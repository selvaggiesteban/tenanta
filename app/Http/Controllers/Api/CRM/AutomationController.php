<?php

namespace App\Http\Controllers\Api\CRM;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class AutomationController extends Controller
{
    public function index()
    {
        return response()->json(['data' => []]);
    }

    public function store(Request $request)
    {
        return response()->json(['message' => 'Automation created']);
    }

    public function show($id)
    {
        return response()->json(['data' => []]);
    }

    public function update(Request $request, $id)
    {
        return response()->json(['message' => 'Automation updated']);
    }

    public function destroy($id)
    {
        return response()->json(['message' => 'Automation deleted']);
    }
}
