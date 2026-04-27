<?php

namespace App\Http\Controllers\Api\CRM;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class EmailMarketingController extends Controller
{
    public function index()
    {
        return response()->json(['data' => []]);
    }

    public function store(Request $request)
    {
        return response()->json(['message' => 'Campaign created']);
    }

    public function show($id)
    {
        return response()->json(['data' => []]);
    }

    public function update(Request $request, $id)
    {
        return response()->json(['message' => 'Campaign updated']);
    }

    public function destroy($id)
    {
        return response()->json(['message' => 'Campaign deleted']);
    }
}
