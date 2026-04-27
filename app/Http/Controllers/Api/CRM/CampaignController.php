<?php

namespace App\Http\Controllers\Api\CRM;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class CampaignController extends Controller
{
    public function getStats()
    {
        return response()->json([
            'total_contacts' => \App\Models\Contact::count(),
            'active_campaigns' => 0
        ]);
    }

    public function getConversions($channel)
    {
        $validChannels = ['email', 'messenger', 'whatsapp'];
        
        if (!in_array($channel, $validChannels)) {
            return response()->json(['error' => 'Invalid channel'], 400);
        }

        return response()->json([
            'channel' => $channel,
            'conversions' => [],
            'stats' => []
        ]);
    }
}
