<?php

namespace App\Http\Controllers\API;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Log;

class MockController extends Controller
{
    public function handleMockRequest(Request $request)
    {
        $statusCode = $request->header('X-Mock-Status');

        if ($statusCode === 'accepted') {
            return response()->json(['message' => 'Mock successful response'], 200);
        } elseif ($statusCode === 'failed') {
            return response()->json(['message' => 'Mock failed response'], 500);
        } else {
            return response()->json(['message' => 'Invalid mock status'], 400);
        }
    }
}
