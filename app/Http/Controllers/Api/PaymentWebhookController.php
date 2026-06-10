<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\PaymentGateway;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class PaymentWebhookController extends Controller
{
    /**
     * Handle Midtrans payment notification webhook.
     * POST /api/webhooks/midtrans
     */
    public function midtrans(Request $request): JsonResponse
    {
        $payload = $request->all();

        Log::info('Midtrans webhook received', ['order_id' => $payload['order_id'] ?? 'unknown']);

        $success = PaymentGateway::handleWebhook($payload);

        if ($success) {
            return response()->json(['status' => 'ok'], 200);
        }

        return response()->json(['status' => 'ignored'], 200);
    }
}
