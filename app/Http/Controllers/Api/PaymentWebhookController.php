<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\MayarGateway;
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

    /**
     * Handle Mayar payment webhook.
     * POST /api/webhooks/mayar
     */
    public function mayar(Request $request, MayarGateway $mayar): JsonResponse
    {
        $payload = $request->all();
        $result = $mayar->handleWebhook($payload);

        Log::info('Mayar webhook handled', [
            'status' => $result['status'] ?? 'unknown',
            'invoice_id' => $result['invoice_id'] ?? null,
        ]);

        return response()->json($result, 200);
    }
}
