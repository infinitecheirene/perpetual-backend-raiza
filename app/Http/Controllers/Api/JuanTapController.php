<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\JuanTapProfile;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;

class JuanTapController extends Controller
{
    /**
     * Get the authenticated user's JuanTap profile
     */
    public function show(Request $request)
    {
        $profile = $request->user()->juantapProfile;
        return response()->json(['data' => $profile], 200);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'profile_url' => 'nullable|url',
            'qr_code' => 'nullable|string',
            'status' => 'required|in:active,inactive',
            'subscription' => 'required|in:silver,gold,black',
        ]);

        $profile = $request->user()->juantapProfile()->create($data);
        return response()->json(['data' => $profile], 201);
    }

    public function update(Request $request, $id)
    {
        $profile = $request->user()->juantapProfile;
        $profile->update($request->only(['profile_url', 'qr_code', 'status', 'subscription']));
        return response()->json(['data' => $profile], 200);
    }

    public function destroy(Request $request, $id)
    {
        $profile = $request->user()->juantapProfile;
        $profile->delete();
        return response()->json(['message' => 'Deleted'], 200);
    }
}