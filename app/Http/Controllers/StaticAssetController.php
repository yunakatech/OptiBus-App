<?php

namespace App\Http\Controllers;

use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\Response;

class StaticAssetController extends Controller
{
    public function style(): BinaryFileResponse|Response
    {
        $manifestPath = public_path('build/manifest.json');

        if (! file_exists($manifestPath)) {
            return $this->emptyCssResponse();
        }

        $manifest = json_decode((string) file_get_contents($manifestPath), true);
        $cssFile = is_array($manifest) ? ($manifest['resources/css/app.css']['file'] ?? null) : null;
        $cssPath = is_string($cssFile) ? public_path('build/'.$cssFile) : null;

        if (! $cssPath || ! file_exists($cssPath)) {
            return $this->emptyCssResponse();
        }

        return response()->file($cssPath, [
            'Content-Type' => 'text/css; charset=UTF-8',
            'Cache-Control' => 'public, max-age=31536000, immutable',
        ]);
    }

    private function emptyCssResponse(): Response
    {
        return response('', 200, [
            'Content-Type' => 'text/css; charset=UTF-8',
            'Cache-Control' => 'no-cache, no-store, must-revalidate',
        ]);
    }
}
