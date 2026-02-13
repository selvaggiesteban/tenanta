<?php

namespace App\Http\Controllers\Api\Chat;

use App\Http\Controllers\Controller;
use App\Http\Resources\ConversationResource;
use App\Http\Resources\MessageResource;
use App\Models\Conversation;
use App\Services\AI\AIManager;
use App\Services\AI\Tools\ToolDefinitions;
use App\Services\AI\Tools\ToolExecutor;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ChatController extends Controller
{
    public function __construct(
        protected AIManager $aiManager,
        protected ToolExecutor $toolExecutor,
    ) {}

    /**
     * List user's conversations.
     */
    public function index(Request $request): JsonResponse
    {
        $conversations = Conversation::where('user_id', Auth::id())
            ->orderByDesc('last_message_at')
            ->paginate($request->get('per_page', 20));

        return response()->json([
            'data' => ConversationResource::collection($conversations),
            'meta' => [
                'current_page' => $conversations->currentPage(),
                'last_page' => $conversations->lastPage(),
                'per_page' => $conversations->perPage(),
                'total' => $conversations->total(),
            ],
        ]);
    }

    /**
     * Create a new conversation.
     */
    public function store(Request $request): JsonResponse
    {
        $request->validate([
            'title' => 'nullable|string|max:255',
            'provider' => 'nullable|string|in:claude,openai,gemini',
        ]);

        $conversation = Conversation::create([
            'tenant_id' => Auth::user()->tenant_id,
            'user_id' => Auth::id(),
            'title' => $request->input('title'),
            'provider' => $request->input('provider', config('ai.default')),
        ]);

        return response()->json([
            'data' => new ConversationResource($conversation),
        ], 201);
    }

    /**
     * Get a conversation with messages.
     */
    public function show(Conversation $conversation): JsonResponse
    {
        $this->authorize('view', $conversation);

        $conversation->load('messages');

        return response()->json([
            'data' => new ConversationResource($conversation),
        ]);
    }

    /**
     * Delete a conversation.
     */
    public function destroy(Conversation $conversation): JsonResponse
    {
        $this->authorize('delete', $conversation);

        $conversation->delete();

        return response()->json(null, 204);
    }

    /**
     * Send a message and get AI response.
     */
    public function sendMessage(Request $request, Conversation $conversation): JsonResponse
    {
        $this->authorize('update', $conversation);

        $request->validate([
            'content' => 'required|string|max:10000',
        ]);

        // Add user message
        $userMessage = $conversation->addMessage([
            'role' => 'user',
            'content' => $request->input('content'),
        ]);

        // Generate title if first message
        $conversation->generateTitle();

        // Get AI response
        $response = $this->getAIResponse($conversation);

        return response()->json([
            'user_message' => new MessageResource($userMessage),
            'assistant_message' => new MessageResource($response),
        ]);
    }

    /**
     * Send a message with streaming response.
     */
    public function streamMessage(Request $request, Conversation $conversation): StreamedResponse
    {
        $this->authorize('update', $conversation);

        $request->validate([
            'content' => 'required|string|max:10000',
        ]);

        // Add user message
        $conversation->addMessage([
            'role' => 'user',
            'content' => $request->input('content'),
        ]);

        // Generate title if first message
        $conversation->generateTitle();

        return new StreamedResponse(function () use ($conversation) {
            $this->streamAIResponse($conversation);
        }, 200, [
            'Content-Type' => 'text/event-stream',
            'Cache-Control' => 'no-cache',
            'Connection' => 'keep-alive',
            'X-Accel-Buffering' => 'no',
        ]);
    }

    /**
     * Get AI response for conversation.
     */
    protected function getAIResponse(Conversation $conversation): \App\Models\Message
    {
        $provider = $this->aiManager->provider($conversation->provider);
        $messages = $conversation->getMessagesForAI();
        $tools = ToolDefinitions::enabled();
        $systemPrompt = config('ai.system_prompt');

        $response = $provider->chat($messages, $tools, $systemPrompt);

        // Handle tool calls
        if ($response->hasToolCalls()) {
            return $this->handleToolCalls($conversation, $response, $provider, $systemPrompt);
        }

        // Save assistant message
        return $conversation->addMessage([
            'role' => 'assistant',
            'content' => $response->content,
            'input_tokens' => $response->inputTokens,
            'output_tokens' => $response->outputTokens,
            'model' => $response->model,
        ]);
    }

    /**
     * Handle tool calls from AI response.
     */
    protected function handleToolCalls(
        Conversation $conversation,
        \App\Services\AI\Contracts\AIResponse $response,
        \App\Services\AI\Contracts\AIProviderInterface $provider,
        ?string $systemPrompt
    ): \App\Models\Message {
        // Save assistant message with tool calls
        $conversation->addMessage([
            'role' => 'assistant',
            'content' => $response->content,
            'tool_calls' => $response->toolCalls,
            'input_tokens' => $response->inputTokens,
            'output_tokens' => $response->outputTokens,
            'model' => $response->model,
        ]);

        // Execute tools and collect results
        $toolResults = [];
        foreach ($response->toolCalls as $toolCall) {
            $result = $this->toolExecutor->execute($toolCall['name'], $toolCall['input'] ?? []);
            $toolResults[] = [
                'tool_use_id' => $toolCall['id'],
                'name' => $toolCall['name'],
                'content' => $result,
            ];
        }

        // Save tool results
        $conversation->addMessage([
            'role' => 'user',
            'content' => '',
            'tool_results' => $toolResults,
        ]);

        // Get follow-up response
        $messages = $conversation->getMessagesForAI();
        $tools = ToolDefinitions::enabled();

        $followUp = $provider->chat($messages, $tools, $systemPrompt);

        // Recursively handle if more tool calls
        if ($followUp->hasToolCalls()) {
            return $this->handleToolCalls($conversation, $followUp, $provider, $systemPrompt);
        }

        // Save final response
        return $conversation->addMessage([
            'role' => 'assistant',
            'content' => $followUp->content,
            'input_tokens' => $followUp->inputTokens,
            'output_tokens' => $followUp->outputTokens,
            'model' => $followUp->model,
        ]);
    }

    /**
     * Stream AI response.
     */
    protected function streamAIResponse(Conversation $conversation): void
    {
        $provider = $this->aiManager->provider($conversation->provider);
        $messages = $conversation->getMessagesForAI();
        $tools = ToolDefinitions::enabled();
        $systemPrompt = config('ai.system_prompt');

        $fullContent = '';
        $toolCalls = null;

        try {
            foreach ($provider->streamChat($messages, $tools, $systemPrompt) as $chunk) {
                if ($chunk['type'] === 'text') {
                    $fullContent .= $chunk['content'];
                    echo "data: " . json_encode(['type' => 'text', 'content' => $chunk['content']]) . "\n\n";
                    ob_flush();
                    flush();
                } elseif ($chunk['type'] === 'done') {
                    $toolCalls = $chunk['tool_calls'] ?? null;

                    // Handle tool calls in streaming mode
                    if ($toolCalls) {
                        echo "data: " . json_encode(['type' => 'tool_start']) . "\n\n";
                        ob_flush();
                        flush();

                        // Save assistant message with tool calls
                        $conversation->addMessage([
                            'role' => 'assistant',
                            'content' => $fullContent,
                            'tool_calls' => $toolCalls,
                        ]);

                        // Execute tools
                        foreach ($toolCalls as $toolCall) {
                            $result = $this->toolExecutor->execute($toolCall['name'], $toolCall['input'] ?? []);

                            echo "data: " . json_encode([
                                'type' => 'tool_result',
                                'tool' => $toolCall['name'],
                                'result' => $result,
                            ]) . "\n\n";
                            ob_flush();
                            flush();

                            // Save tool result
                            $conversation->addMessage([
                                'role' => 'user',
                                'content' => '',
                                'tool_results' => [[
                                    'tool_use_id' => $toolCall['id'],
                                    'name' => $toolCall['name'],
                                    'content' => $result,
                                ]],
                            ]);
                        }

                        // Get follow-up response (non-streaming for simplicity)
                        $messages = $conversation->getMessagesForAI();
                        $followUp = $provider->chat($messages, $tools, $systemPrompt);

                        echo "data: " . json_encode([
                            'type' => 'text',
                            'content' => $followUp->content,
                        ]) . "\n\n";
                        ob_flush();
                        flush();

                        $conversation->addMessage([
                            'role' => 'assistant',
                            'content' => $followUp->content,
                            'input_tokens' => $followUp->inputTokens,
                            'output_tokens' => $followUp->outputTokens,
                            'model' => $followUp->model,
                        ]);
                    } else {
                        // No tool calls, just save the message
                        $conversation->addMessage([
                            'role' => 'assistant',
                            'content' => $fullContent,
                        ]);
                    }

                    echo "data: " . json_encode(['type' => 'done']) . "\n\n";
                    ob_flush();
                    flush();
                }
            }
        } catch (\Exception $e) {
            echo "data: " . json_encode([
                'type' => 'error',
                'message' => $e->getMessage(),
            ]) . "\n\n";
            ob_flush();
            flush();
        }
    }
}
