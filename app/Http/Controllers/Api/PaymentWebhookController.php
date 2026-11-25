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
     * POST /api/webhooks/mayar/{token}
     */
    public function mayar(Request $request, MayarGateway $mayar, string $token): JsonResponse
    {
        $secret = trim((string) config('mayar.webhook_secret'));
        if (strlen($secret) < 32) {
            Log::critical('Mayar webhook secret is missing or too short.');

            return response()->json(['status' => 'unavailable'], 503);
        }

        if (! hash_equals($secret, $token)) {
            return response()->json(['status' => 'not_found'], 404);
        }

        $maxPayloadBytes = max(1024, (int) config('mayar.webhook_max_payload_bytes', 32768));
        $contentLength = (int) $request->server('CONTENT_LENGTH', 0);
        if ($contentLength > $maxPayloadBytes || strlen($request->getContent()) > $maxPayloadBytes) {
            return response()->json(['status' => 'payload_too_large'], 413);
        }

        $payload = $request->all();
        if ($payload === []) {
            return response()->json(['status' => 'invalid_payload'], 422);
        }

        $result = $mayar->handleWebhook($payload);

        Log::info('Mayar webhook handled', [
            'status' => $result['status'] ?? 'unknown',
            'invoice_id' => $result['invoice_id'] ?? null,
        ]);

        return response()->json($result, 200);
    }
}
