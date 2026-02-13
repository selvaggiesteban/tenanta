<?php

namespace App\Http\Controllers\Api\Support;

use App\Http\Controllers\Controller;
use App\Models\KbArticle;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class KbArticleController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = KbArticle::with(['category', 'author']);

        if ($request->has('category_id')) {
            $query->where('category_id', $request->category_id);
        }

        if ($request->has('status')) {
            $query->where('status', $request->status);
        } else {
            $query->published();
        }

        if ($request->has('search')) {
            $query->search($request->search);
        }

        if ($request->boolean('featured', false)) {
            $query->featured();
        }

        $articles = $query->orderByDesc('published_at')
            ->paginate($request->get('per_page', 20));

        return response()->json([
            'data' => $articles->map(fn($article) => [
                'id' => $article->id,
                'title' => $article->title,
                'slug' => $article->slug,
                'excerpt' => $article->excerpt,
                'category' => $article->category ? [
                    'id' => $article->category->id,
                    'name' => $article->category->name,
                ] : null,
                'author' => [
                    'id' => $article->author->id,
                    'name' => $article->author->name,
                ],
                'status' => $article->status,
                'is_featured' => $article->is_featured,
                'views' => $article->views,
                'reading_time' => $article->reading_time,
                'published_at' => $article->published_at?->toISOString(),
            ]),
            'meta' => [
                'current_page' => $articles->currentPage(),
                'last_page' => $articles->lastPage(),
                'per_page' => $articles->perPage(),
                'total' => $articles->total(),
            ],
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'slug' => 'nullable|string|max:255',
            'category_id' => 'required|exists:kb_categories,id',
            'excerpt' => 'nullable|string|max:500',
            'content' => 'required|string',
            'status' => 'nullable|in:draft,published,archived',
            'is_featured' => 'nullable|boolean',
            'is_public' => 'nullable|boolean',
            'tags' => 'nullable|array',
        ]);

        $article = KbArticle::create([
            ...$validated,
            'tenant_id' => Auth::user()->tenant_id,
            'author_id' => Auth::id(),
            'status' => $validated['status'] ?? 'draft',
            'published_at' => ($validated['status'] ?? 'draft') === 'published' ? now() : null,
        ]);

        $article->load(['category', 'author']);

        return response()->json([
            'data' => [
                'id' => $article->id,
                'title' => $article->title,
                'slug' => $article->slug,
                'status' => $article->status,
            ],
            'message' => 'Artículo creado exitosamente',
        ], 201);
    }

    public function show(KbArticle $kbArticle): JsonResponse
    {
        $kbArticle->load(['category', 'author']);

        // Track view only for published articles
        if ($kbArticle->status === 'published') {
            $kbArticle->incrementViews();
        }

        return response()->json([
            'data' => [
                'id' => $kbArticle->id,
                'title' => $kbArticle->title,
                'slug' => $kbArticle->slug,
                'excerpt' => $kbArticle->excerpt,
                'content' => $kbArticle->content,
                'category' => $kbArticle->category ? [
                    'id' => $kbArticle->category->id,
                    'name' => $kbArticle->category->name,
                    'slug' => $kbArticle->category->slug,
                ] : null,
                'author' => [
                    'id' => $kbArticle->author->id,
                    'name' => $kbArticle->author->name,
                ],
                'status' => $kbArticle->status,
                'is_featured' => $kbArticle->is_featured,
                'is_public' => $kbArticle->is_public,
                'tags' => $kbArticle->tags,
                'views' => $kbArticle->views,
                'helpful_yes' => $kbArticle->helpful_yes,
                'helpful_no' => $kbArticle->helpful_no,
                'helpful_percentage' => $kbArticle->helpful_percentage,
                'reading_time' => $kbArticle->reading_time,
                'published_at' => $kbArticle->published_at?->toISOString(),
                'created_at' => $kbArticle->created_at->toISOString(),
                'updated_at' => $kbArticle->updated_at->toISOString(),
            ],
        ]);
    }

    public function update(Request $request, KbArticle $kbArticle): JsonResponse
    {
        $validated = $request->validate([
            'title' => 'sometimes|string|max:255',
            'slug' => 'nullable|string|max:255',
            'category_id' => 'sometimes|exists:kb_categories,id',
            'excerpt' => 'nullable|string|max:500',
            'content' => 'sometimes|string',
            'status' => 'nullable|in:draft,published,archived',
            'is_featured' => 'nullable|boolean',
            'is_public' => 'nullable|boolean',
            'tags' => 'nullable|array',
        ]);

        // Set published_at when publishing
        if (isset($validated['status']) && $validated['status'] === 'published' && !$kbArticle->published_at) {
            $validated['published_at'] = now();
        }

        $kbArticle->update($validated);

        return response()->json([
            'data' => [
                'id' => $kbArticle->id,
                'title' => $kbArticle->title,
                'slug' => $kbArticle->slug,
                'status' => $kbArticle->status,
            ],
            'message' => 'Artículo actualizado exitosamente',
        ]);
    }

    public function destroy(KbArticle $kbArticle): JsonResponse
    {
        $kbArticle->delete();

        return response()->json(null, 204);
    }

    public function publish(KbArticle $kbArticle): JsonResponse
    {
        $kbArticle->publish();

        return response()->json([
            'message' => 'Artículo publicado exitosamente',
        ]);
    }

    public function archive(KbArticle $kbArticle): JsonResponse
    {
        $kbArticle->archive();

        return response()->json([
            'message' => 'Artículo archivado exitosamente',
        ]);
    }

    public function feedback(Request $request, KbArticle $kbArticle): JsonResponse
    {
        $validated = $request->validate([
            'helpful' => 'required|boolean',
        ]);

        $kbArticle->markHelpful($validated['helpful']);

        return response()->json([
            'message' => 'Gracias por tu feedback',
            'helpful_percentage' => $kbArticle->fresh()->helpful_percentage,
        ]);
    }
}
