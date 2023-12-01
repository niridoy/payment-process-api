<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Validator;
use App\Models\Payment;
use Illuminate\Support\Facades\Log;

class MakePaymentController extends Controller
{
    public function makePayment(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'amount' => 'required|numeric|min:0', 
            'user_id' => 'required|integer', 
        ]);
    
        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'errors' => $validator->errors()
            ]); 
        }
                 
        $amount = $request['amount'];
        $userId = $request['user_id'];
    
        $mockApiResponse = Http::withHeaders([
            'X-Mock-Status' => 'accepted', 
        ])->get(env('APP_URL') . '/api/v1/mock-payment');

        if ($mockApiResponse->successful()) {
    
            $response = Http::withHeaders([
                'X-Mock-Status' => 'accepted',
            ])->post(env('APP_URL') . '/api/v1/process-payment', [
                'amount' => $amount,
                'user_id' => $userId,
            ]);
    
            if ($response->successful()) {
                $randomBytes = random_bytes(16); 
                $transactionId = bin2hex($randomBytes);

                Payment::create([
                    'user_id' => $userId,
                    'amount' => $amount,
                    'transaction_id' => $transactionId,
                ]);
    
                $updatePaymentResponse = Http::post(env('APP_URL') . '/api/v1/update-payment/', [
                    'transaction_id' => $transactionId,
                    'status' => 'accepted', 
                ]);
    
                if ($updatePaymentResponse->successful()) {
                    return response()->json(['message' => 'Payment processed and updated successfully']);
                } else {
                    return response()->json(['message' => 'Failed to update payment status'], $updatePaymentResponse->status());
                }
            } else {
                return response()->json(['message' => 'Process payment API call failed'], $response->status());
            }
        } else {
            return response()->json(['message' => 'Mock API call failed'], $mockApiResponse->status());
        }
    }

    public function updatePayment(Request $request)
    {
        $transactionId = $request->input('transaction_id');
        $status = $request->input('status');

        $payment = Payment::where('transaction_id', $transactionId)->first();

        if ($payment) {
            
            $payment->status = $status;
            $payment->save();

            return response()->json(['message' => 'Payment status updated successfully']);
        }

        return response()->json(['message' => 'Payment not found'], 404);
    }
}
