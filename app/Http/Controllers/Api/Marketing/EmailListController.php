<?php

namespace App\Http\Controllers\Api\Marketing;

use App\Actions\Marketing\ManageEmailListAction;
use App\Http\Controllers\Controller;
use App\Http\Requests\Marketing\AddListSubscribersRequest;
use App\Http\Requests\Marketing\ImportListSubscribersRequest;
use App\Http\Requests\Marketing\StoreEmailListRequest;
use App\Http\Requests\Marketing\UpdateEmailListRequest;
use App\Http\Resources\Marketing\EmailListCollection;
use App\Http\Resources\Marketing\EmailListResource;
use App\Http\Resources\Marketing\EmailListSubscriberCollection;
use App\Models\Marketing\EmailList;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class EmailListController extends Controller
{
    public function index(Request $request): EmailListCollection
    {
        $query = EmailList::query()
            ->with('creator');

        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }

        if ($request->filled('search')) {
            $query->where('name', 'like', "%{$request->search}%");
        }

        if ($request->boolean('active_only', false)) {
            $query->where('is_active', true);
        }

        $lists = $query->orderBy('created_at', 'desc')
            ->paginate($request->integer('per_page', 15));

        return new EmailListCollection($lists);
    }

    public function store(
        StoreEmailListRequest $request,
        ManageEmailListAction $action
    ): EmailListResource {
        $list = $action->create(
            auth()->user()->tenant_id,
            auth()->id(),
            $request->validated()
        );

        return new EmailListResource($list->load('creator'));
    }

    public function show(EmailList $emailList): EmailListResource
    {
        return new EmailListResource($emailList->load('creator'));
    }

    public function update(
        UpdateEmailListRequest $request,
        EmailList $emailList,
        ManageEmailListAction $action
    ): EmailListResource {
        $list = $action->update($emailList, $request->validated());

        return new EmailListResource($list->load('creator'));
    }

    public function destroy(EmailList $emailList): JsonResponse
    {
        $emailList->delete();

        return response()->json(['message' => 'Lista eliminada']);
    }

    public function subscribers(Request $request, EmailList $emailList): EmailListSubscriberCollection
    {
        $query = $emailList->subscribers();

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('search')) {
            $query->where(function ($q) use ($request) {
                $q->where('email', 'like', "%{$request->search}%")
                    ->orWhere('name', 'like', "%{$request->search}%");
            });
        }

        $subscribers = $query->orderBy('subscribed_at', 'desc')
            ->paginate($request->integer('per_page', 50));

        return new EmailListSubscriberCollection($subscribers);
    }

    public function addSubscribers(
        AddListSubscribersRequest $request,
        EmailList $emailList,
        ManageEmailListAction $action
    ): JsonResponse {
        $added = $action->addSubscribers($emailList, $request->subscribers);

        return response()->json([
            'message' => "Se agregaron $added suscriptores",
            'added' => $added,
            'total' => $emailList->fresh()->subscriber_count,
        ]);
    }

    public function importSubscribers(
        ImportListSubscribersRequest $request,
        EmailList $emailList,
        ManageEmailListAction $action
    ): JsonResponse {
        $file = $request->file('file');
        $csvContent = file_get_contents($file->getRealPath());

        $result = $action->importFromCsv($emailList, $csvContent);

        return response()->json([
            'message' => "Importación completada: {$result['added']} de {$result['total']} suscriptores agregados",
            'added' => $result['added'],
            'total' => $result['total'],
            'errors' => $result['errors'],
        ]);
    }

    public function removeSubscriber(EmailList $emailList, int $subscriberId): JsonResponse
    {
        $subscriber = $emailList->subscribers()->find($subscriberId);

        if (!$subscriber) {
            return response()->json(['message' => 'Suscriptor no encontrado'], 404);
        }

        $subscriber->delete();
        $emailList->refreshCounts();

        return response()->json(['message' => 'Suscriptor eliminado']);
    }

    public function syncDynamic(
        EmailList $emailList,
        ManageEmailListAction $action
    ): JsonResponse {
        if ($emailList->type !== EmailList::TYPE_DYNAMIC) {
            return response()->json([
                'message' => 'Esta operación solo aplica a listas dinámicas',
            ], 422);
        }

        $added = $action->syncDynamicList($emailList);

        return response()->json([
            'message' => "Sincronización completada: $added suscriptores agregados",
            'added' => $added,
            'total' => $emailList->fresh()->subscriber_count,
        ]);
    }

    public function export(EmailList $emailList): JsonResponse
    {
        $subscribers = $emailList->subscribers()
            ->select('email', 'name', 'status', 'subscribed_at', 'source')
            ->get();

        $csv = "email,name,status,subscribed_at,source\n";
        foreach ($subscribers as $subscriber) {
            $csv .= "\"{$subscriber->email}\",\"{$subscriber->name}\",\"{$subscriber->status}\",\"{$subscriber->subscribed_at}\",\"{$subscriber->source}\"\n";
        }

        return response()->json([
            'filename' => "lista_{$emailList->id}_suscriptores.csv",
            'content' => $csv,
        ]);
    }
}
