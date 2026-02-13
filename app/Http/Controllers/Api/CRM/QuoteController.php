<?php

namespace App\Http\Controllers\Api\CRM;

use App\Http\Controllers\Controller;
use App\Http\Resources\QuoteResource;
use App\Models\Quote;
use App\Models\QuoteItem;
use App\Services\PDF\QuotePdfService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Facades\DB;

class QuoteController extends Controller
{
    public function index(Request $request): AnonymousResourceCollection
    {
        $query = Quote::query()
            ->with('client:id,name')
            ->withCount('items');

        // Filter by client
        if ($clientId = $request->get('client_id')) {
            $query->where('client_id', $clientId);
        }

        // Filter by status
        if ($status = $request->get('status')) {
            $query->where('status', $status);
        }

        // Search
        if ($search = $request->get('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('quote_number', 'like', "%{$search}%")
                  ->orWhere('title', 'like', "%{$search}%");
            });
        }

        // Sorting
        $sortBy = $request->get('sort_by', 'created_at');
        $sortDir = $request->get('sort_dir', 'desc');
        $query->orderBy($sortBy, $sortDir);

        // Pagination
        $perPage = min($request->get('per_page', 15), 100);
        $quotes = $query->paginate($perPage);

        return QuoteResource::collection($quotes);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'client_id' => 'required|exists:clients,id',
            'title' => 'required|string|max:255',
            'description' => 'nullable|string|max:2000',
            'tax_rate' => 'nullable|numeric|min:0|max:100',
            'discount_amount' => 'nullable|numeric|min:0',
            'currency' => 'nullable|string|size:3',
            'valid_until' => 'nullable|date|after:today',
            'terms' => 'nullable|string|max:5000',
            'notes' => 'nullable|string|max:2000',
            'items' => 'nullable|array',
            'items.*.description' => 'required|string|max:255',
            'items.*.quantity' => 'required|numeric|min:0.01',
            'items.*.unit' => 'nullable|string|max:50',
            'items.*.unit_price' => 'required|numeric|min:0',
            'items.*.discount_percent' => 'nullable|numeric|min:0|max:100',
        ]);

        return DB::transaction(function () use ($validated) {
            $validated['created_by'] = auth('api')->id();

            $quote = Quote::create($validated);

            // Create items if provided
            if (!empty($validated['items'])) {
                foreach ($validated['items'] as $index => $itemData) {
                    $itemData['sort_order'] = $index;
                    $quote->items()->create($itemData);
                }
            }

            $quote->calculateTotals();
            $quote->load(['client:id,name', 'items']);

            return response()->json([
                'success' => true,
                'message' => 'Presupuesto creado exitosamente',
                'data' => new QuoteResource($quote),
            ], 201);
        });
    }

    public function show(Quote $quote): JsonResponse
    {
        $quote->load(['client:id,name,email,phone', 'items', 'createdBy:id,name']);

        return response()->json([
            'success' => true,
            'data' => new QuoteResource($quote),
        ]);
    }

    public function update(Request $request, Quote $quote): JsonResponse
    {
        if (!$quote->isEditable()) {
            return response()->json([
                'success' => false,
                'message' => 'Este presupuesto no puede ser editado',
            ], 422);
        }

        $validated = $request->validate([
            'title' => 'sometimes|string|max:255',
            'description' => 'nullable|string|max:2000',
            'tax_rate' => 'nullable|numeric|min:0|max:100',
            'discount_amount' => 'nullable|numeric|min:0',
            'currency' => 'nullable|string|size:3',
            'valid_until' => 'nullable|date|after:today',
            'terms' => 'nullable|string|max:5000',
            'notes' => 'nullable|string|max:2000',
            'items' => 'nullable|array',
            'items.*.id' => 'nullable|exists:quote_items,id',
            'items.*.description' => 'required|string|max:255',
            'items.*.quantity' => 'required|numeric|min:0.01',
            'items.*.unit' => 'nullable|string|max:50',
            'items.*.unit_price' => 'required|numeric|min:0',
            'items.*.discount_percent' => 'nullable|numeric|min:0|max:100',
        ]);

        return DB::transaction(function () use ($quote, $validated) {
            $quote->update($validated);

            // Update items if provided
            if (isset($validated['items'])) {
                $existingIds = [];

                foreach ($validated['items'] as $index => $itemData) {
                    $itemData['sort_order'] = $index;

                    if (isset($itemData['id'])) {
                        $item = $quote->items()->find($itemData['id']);
                        if ($item) {
                            $item->update($itemData);
                            $existingIds[] = $item->id;
                        }
                    } else {
                        $newItem = $quote->items()->create($itemData);
                        $existingIds[] = $newItem->id;
                    }
                }

                // Delete items not in the update
                $quote->items()->whereNotIn('id', $existingIds)->delete();
            }

            $quote->calculateTotals();
            $quote->load(['client:id,name', 'items']);

            return response()->json([
                'success' => true,
                'message' => 'Presupuesto actualizado exitosamente',
                'data' => new QuoteResource($quote),
            ]);
        });
    }

    public function destroy(Quote $quote): JsonResponse
    {
        $quote->delete();

        return response()->json([
            'success' => true,
            'message' => 'Presupuesto eliminado exitosamente',
        ]);
    }

    public function send(Quote $quote): JsonResponse
    {
        if ($quote->status !== 'draft') {
            return response()->json([
                'success' => false,
                'message' => 'Solo se pueden enviar presupuestos en estado borrador',
            ], 422);
        }

        $quote->markAsSent();

        // TODO: Send email notification to client

        return response()->json([
            'success' => true,
            'message' => 'Presupuesto enviado exitosamente',
            'data' => new QuoteResource($quote),
        ]);
    }

    public function accept(Quote $quote): JsonResponse
    {
        if (!in_array($quote->status, ['sent', 'viewed'])) {
            return response()->json([
                'success' => false,
                'message' => 'Este presupuesto no puede ser aceptado',
            ], 422);
        }

        $quote->accept();

        return response()->json([
            'success' => true,
            'message' => 'Presupuesto aceptado exitosamente',
            'data' => new QuoteResource($quote),
        ]);
    }

    public function reject(Quote $quote): JsonResponse
    {
        if (!in_array($quote->status, ['sent', 'viewed'])) {
            return response()->json([
                'success' => false,
                'message' => 'Este presupuesto no puede ser rechazado',
            ], 422);
        }

        $quote->reject();

        return response()->json([
            'success' => true,
            'message' => 'Presupuesto rechazado',
            'data' => new QuoteResource($quote),
        ]);
    }

    public function duplicate(Quote $quote): JsonResponse
    {
        return DB::transaction(function () use ($quote) {
            $newQuote = $quote->replicate(['quote_number', 'status', 'sent_at', 'viewed_at', 'accepted_at', 'rejected_at']);
            $newQuote->status = 'draft';
            $newQuote->created_by = auth('api')->id();
            $newQuote->save();

            foreach ($quote->items as $item) {
                $newItem = $item->replicate();
                $newItem->quote_id = $newQuote->id;
                $newItem->save();
            }

            $newQuote->calculateTotals();
            $newQuote->load(['client:id,name', 'items']);

            return response()->json([
                'success' => true,
                'message' => 'Presupuesto duplicado exitosamente',
                'data' => new QuoteResource($newQuote),
            ], 201);
        });
    }

    public function pdf(Quote $quote, QuotePdfService $pdfService)
    {
        return $pdfService->stream($quote);
    }

    public function download(Quote $quote, QuotePdfService $pdfService)
    {
        return $pdfService->download($quote);
    }
}
