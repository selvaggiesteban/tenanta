<?php

namespace App\Http\Controllers\Api\Marketing;

use App\Actions\Marketing\AddCampaignRecipientsAction;
use App\Actions\Marketing\CreateEmailCampaignAction;
use App\Actions\Marketing\GetCampaignStatsAction;
use App\Actions\Marketing\ScheduleCampaignAction;
use App\Actions\Marketing\SendCampaignAction;
use App\Actions\Marketing\UpdateEmailCampaignAction;
use App\Http\Controllers\Controller;
use App\Http\Requests\Marketing\AddCampaignRecipientsRequest;
use App\Http\Requests\Marketing\ScheduleCampaignRequest;
use App\Http\Requests\Marketing\StoreEmailCampaignRequest;
use App\Http\Requests\Marketing\UpdateEmailCampaignRequest;
use App\Http\Resources\Marketing\CampaignStatsResource;
use App\Http\Resources\Marketing\EmailCampaignCollection;
use App\Http\Resources\Marketing\EmailCampaignResource;
use App\Http\Resources\Marketing\EmailRecipientCollection;
use App\Models\Marketing\EmailCampaign;
use App\Models\Marketing\EmailList;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class EmailCampaignController extends Controller
{
    public function index(Request $request): EmailCampaignCollection
    {
        $query = EmailCampaign::query()
            ->with('creator', 'template');

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }

        if ($request->filled('search')) {
            $query->where(function ($q) use ($request) {
                $q->where('name', 'like', "%{$request->search}%")
                    ->orWhere('subject', 'like', "%{$request->search}%");
            });
        }

        $campaigns = $query->orderBy('created_at', 'desc')
            ->paginate($request->integer('per_page', 15));

        return new EmailCampaignCollection($campaigns);
    }

    public function store(
        StoreEmailCampaignRequest $request,
        CreateEmailCampaignAction $action
    ): EmailCampaignResource {
        $campaign = $action->execute(
            auth()->user()->tenant_id,
            auth()->id(),
            $request->validated()
        );

        return new EmailCampaignResource($campaign->load(['creator', 'template']));
    }

    public function show(EmailCampaign $emailCampaign): EmailCampaignResource
    {
        return new EmailCampaignResource(
            $emailCampaign->load(['creator', 'template'])
        );
    }

    public function update(
        UpdateEmailCampaignRequest $request,
        EmailCampaign $emailCampaign,
        UpdateEmailCampaignAction $action
    ): EmailCampaignResource {
        $campaign = $action->execute($emailCampaign, $request->validated());

        return new EmailCampaignResource($campaign->load(['creator', 'template']));
    }

    public function destroy(EmailCampaign $emailCampaign): JsonResponse
    {
        if (!in_array($emailCampaign->status, [EmailCampaign::STATUS_DRAFT, EmailCampaign::STATUS_SCHEDULED])) {
            return response()->json([
                'message' => 'Solo se pueden eliminar campañas en estado borrador o programadas',
            ], 422);
        }

        $emailCampaign->delete();

        return response()->json(['message' => 'Campaña eliminada']);
    }

    public function duplicate(EmailCampaign $emailCampaign): EmailCampaignResource
    {
        $duplicate = $emailCampaign->replicate([
            'status',
            'sent_count',
            'delivered_count',
            'opened_count',
            'clicked_count',
            'bounced_count',
            'unsubscribed_count',
            'scheduled_at',
            'started_at',
            'completed_at',
        ]);

        $duplicate->name = $emailCampaign->name . ' (copia)';
        $duplicate->status = EmailCampaign::STATUS_DRAFT;
        $duplicate->created_by = auth()->id();
        $duplicate->recipient_count = 0;
        $duplicate->save();

        return new EmailCampaignResource($duplicate->load(['creator', 'template']));
    }

    public function recipients(Request $request, EmailCampaign $emailCampaign): EmailRecipientCollection
    {
        $query = $emailCampaign->recipients();

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('search')) {
            $query->where(function ($q) use ($request) {
                $q->where('email', 'like', "%{$request->search}%")
                    ->orWhere('name', 'like', "%{$request->search}%");
            });
        }

        $recipients = $query->orderBy('created_at', 'desc')
            ->paginate($request->integer('per_page', 50));

        return new EmailRecipientCollection($recipients);
    }

    public function addRecipients(
        AddCampaignRecipientsRequest $request,
        EmailCampaign $emailCampaign,
        AddCampaignRecipientsAction $action
    ): JsonResponse {
        $data = $request->validated();
        $added = 0;

        switch ($data['source']) {
            case 'list':
                $list = EmailList::findOrFail($data['list_id']);
                $added = $action->fromList($emailCampaign, $list);
                break;

            case 'users':
                $users = User::whereIn('id', $data['user_ids'])->get();
                $added = $action->fromUsers($emailCampaign, $users);
                break;

            case 'emails':
                $added = $action->fromEmails($emailCampaign, $data['emails']);
                break;
        }

        return response()->json([
            'message' => "Se agregaron $added destinatarios",
            'added' => $added,
            'total' => $emailCampaign->fresh()->recipient_count,
        ]);
    }

    public function removeRecipient(
        EmailCampaign $emailCampaign,
        string $email,
        AddCampaignRecipientsAction $action
    ): JsonResponse {
        $removed = $action->removeRecipient($emailCampaign, $email);

        if (!$removed) {
            return response()->json(['message' => 'Destinatario no encontrado'], 404);
        }

        return response()->json(['message' => 'Destinatario eliminado']);
    }

    public function schedule(
        ScheduleCampaignRequest $request,
        EmailCampaign $emailCampaign,
        ScheduleCampaignAction $action
    ): EmailCampaignResource {
        $campaign = $action->execute(
            $emailCampaign,
            Carbon::parse($request->scheduled_at)
        );

        return new EmailCampaignResource($campaign->load(['creator', 'template']));
    }

    public function cancelSchedule(
        EmailCampaign $emailCampaign,
        ScheduleCampaignAction $action
    ): EmailCampaignResource {
        $campaign = $action->cancel($emailCampaign);

        return new EmailCampaignResource($campaign->load(['creator', 'template']));
    }

    public function send(
        EmailCampaign $emailCampaign,
        SendCampaignAction $action
    ): JsonResponse {
        try {
            $action->execute($emailCampaign);

            return response()->json([
                'message' => 'Campaña enviada exitosamente',
                'status' => $emailCampaign->fresh()->status,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => $e->getMessage(),
            ], 422);
        }
    }

    public function stats(
        EmailCampaign $emailCampaign,
        GetCampaignStatsAction $action
    ): CampaignStatsResource {
        $stats = $action->execute($emailCampaign);

        return new CampaignStatsResource($stats);
    }

    public function preview(Request $request, EmailCampaign $emailCampaign): JsonResponse
    {
        $mergeFields = $request->input('merge_fields', [
            'name' => 'Usuario Ejemplo',
            'email' => 'ejemplo@email.com',
            'first_name' => 'Usuario',
        ]);

        $html = $emailCampaign->content_html;

        foreach ($mergeFields as $key => $value) {
            $html = str_replace(
                ['{{' . $key . '}}', '{{ ' . $key . ' }}'],
                $value,
                $html
            );
        }

        return response()->json([
            'subject' => $emailCampaign->subject,
            'html' => $html,
        ]);
    }
}
