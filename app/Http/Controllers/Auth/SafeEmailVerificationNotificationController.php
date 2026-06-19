<?php

namespace App\Http\Controllers\Auth;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Log;
use Laravel\Fortify\Contracts\EmailVerificationNotificationSentResponse;
use Laravel\Fortify\Http\Responses\RedirectAsIntended;

class SafeEmailVerificationNotificationController extends Controller
{
    /**
     * Send a new email verification notification.
     */
    public function store(Request $request)
    {
        $user = $request->user();

        if ($user->hasVerifiedEmail()) {
            return $request->wantsJson()
                ? new JsonResponse('', 204)
                : app(RedirectAsIntended::class, ['name' => 'email-verification']);
        }

        try {
            $user->sendEmailVerificationNotification();
        } catch (\Throwable $e) {
            Log::warning('email.verification.send_failed', [
                'user_id' => (int) ($user?->id ?? 0),
                'email' => (string) ($user?->email ?? ''),
                'exception' => $e::class,
                'error' => $e->getMessage(),
            ]);

            return $this->failedResponse($request);
        }

        return app(EmailVerificationNotificationSentResponse::class);
    }

    private function failedResponse(Request $request)
    {
        if ($request->wantsJson()) {
            return response()->json([
                'message' => 'Unable to send the verification email right now. Please try again later.',
            ], 503);
        }

        return back()->with('status', 'verification-link-failed');
    }
}
