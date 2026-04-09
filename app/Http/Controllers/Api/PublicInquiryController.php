<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\CRM\Lead;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class PublicInquiryController extends Controller
{
    /**
     * Store a new inquiry as a Lead.
     */
    public function store(Request $request): JsonResponse
    {
        $tenant = app('current_tenant');

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255',
            'phone' => 'nullable|string|max:50',
            'subject' => 'required|string|max:255',
            'message' => 'required|string|max:5000',
        ]);

        try {
            // Transform inquiry to a CRM Lead
            $lead = Lead::create([
                'tenant_id' => $tenant->id,
                'first_name' => $validated['name'],
                'last_name' => '', // Generic for now
                'email' => $validated['email'],
                'phone' => $validated['phone'],
                'source' => 'web_inquiry',
                'status' => 'new',
                'description' => "Subject: {$validated['subject']}\n\nMessage: {$validated['message']}",
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Inquiry received successfully',
                'lead_id' => $lead->id
            ]);
        } catch (\Exception $e) {
            Log::error('Error storing public inquiry', [
                'error' => $e->getMessage(),
                'data' => $validated
            ]);
            return response()->json(['message' => 'Error processing your inquiry'], 500);
        }
    }
}
