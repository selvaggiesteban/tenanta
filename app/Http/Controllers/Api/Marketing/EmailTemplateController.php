<?php

namespace App\Http\Controllers\Api\Marketing;

use App\Actions\Marketing\CreateEmailTemplateAction;
use App\Actions\Marketing\UpdateEmailTemplateAction;
use App\Http\Controllers\Controller;
use App\Http\Requests\Marketing\StoreEmailTemplateRequest;
use App\Http\Requests\Marketing\UpdateEmailTemplateRequest;
use App\Http\Resources\Marketing\EmailTemplateCollection;
use App\Http\Resources\Marketing\EmailTemplateResource;
use App\Models\Marketing\EmailTemplate;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class EmailTemplateController extends Controller
{
    public function index(Request $request): EmailTemplateCollection
    {
        $query = EmailTemplate::query()
            ->with('creator');

        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }

        if ($request->filled('category')) {
            $query->where('category', $request->category);
        }

        if ($request->filled('search')) {
            $query->where(function ($q) use ($request) {
                $q->where('name', 'like', "%{$request->search}%")
                    ->orWhere('subject', 'like', "%{$request->search}%");
            });
        }

        if ($request->boolean('active_only', false)) {
            $query->where('is_active', true);
        }

        $templates = $query->orderBy('created_at', 'desc')
            ->paginate($request->integer('per_page', 15));

        return new EmailTemplateCollection($templates);
    }

    public function store(
        StoreEmailTemplateRequest $request,
        CreateEmailTemplateAction $action
    ): EmailTemplateResource {
        $template = $action->execute(
            auth()->user()->tenant_id,
            auth()->id(),
            $request->validated()
        );

        return new EmailTemplateResource($template->load('creator'));
    }

    public function show(EmailTemplate $emailTemplate): EmailTemplateResource
    {
        return new EmailTemplateResource($emailTemplate->load('creator'));
    }

    public function update(
        UpdateEmailTemplateRequest $request,
        EmailTemplate $emailTemplate,
        UpdateEmailTemplateAction $action
    ): EmailTemplateResource {
        $template = $action->execute($emailTemplate, $request->validated());

        return new EmailTemplateResource($template->load('creator'));
    }

    public function destroy(EmailTemplate $emailTemplate): JsonResponse
    {
        $emailTemplate->delete();

        return response()->json(['message' => 'Plantilla eliminada']);
    }

    public function duplicate(EmailTemplate $emailTemplate): EmailTemplateResource
    {
        $duplicate = $emailTemplate->replicate();
        $duplicate->name = $emailTemplate->name . ' (copia)';
        $duplicate->created_by = auth()->id();
        $duplicate->save();

        return new EmailTemplateResource($duplicate->load('creator'));
    }

    public function preview(Request $request, EmailTemplate $emailTemplate): JsonResponse
    {
        $mergeFields = $request->input('merge_fields', []);

        $html = $emailTemplate->render($mergeFields);

        return response()->json([
            'subject' => $emailTemplate->subject,
            'html' => $html,
        ]);
    }

    public function categories(): JsonResponse
    {
        $categories = EmailTemplate::whereNotNull('category')
            ->distinct()
            ->pluck('category');

        return response()->json(['data' => $categories]);
    }
}
