<?php

namespace App\Http\Controllers;

use App\Support\Code39;
use App\Support\HeadlessPdf;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\View as ViewFactory;

class LuggageDocumentController extends Controller
{
    public function print(int $id): View
    {
        return view('luggages.receipt-print', [
            'document' => $this->luggagePayload($id),
        ]);
    }

    public function pdf(Request $request, int $id)
    {
        $payload = $this->luggagePayload($id);

        $pdfPath = $this->generatePdfFromView(
            'luggages.receipt-print',
            [
                'document' => $payload,
                'exportMode' => 'pdf',
            ],
            sprintf('Resi Bagasi %s', $payload['kode_resi']),
        );

        $filename = sprintf('resi-bagasi-%s.pdf', strtolower((string) $payload['kode_resi']));

        if ($request->boolean('inline')) {
            $contents = (string) file_get_contents($pdfPath);
            @unlink($pdfPath);

            return response($contents, 200, [
                'Content-Type' => 'application/pdf',
                'Content-Disposition' => 'inline; filename="'.$filename.'"',
            ]);
        }

        return response()->download($pdfPath, $filename)->deleteFileAfterSend(true);
    }

    /**
     * @return array<string, mixed>
     */
    private function luggagePayload(int $id): array
    {
        abort_unless(Schema::hasTable('luggages'), 404);

        $row = DB::table('luggages as l')
            ->leftJoin('luggage_services as s', 'l.service_id', '=', 's.id')
            ->leftJoin('units as u', 'l.unit_id', '=', 'u.id')
            ->leftJoin('routes as r', 'l.rute_id', '=', 'r.id')
            ->where('l.id', $id)
            ->select([
                'l.id',
                'l.sender_name',
                'l.sender_phone',
                'l.sender_address',
                'l.receiver_name',
                'l.receiver_phone',
                'l.receiver_address',
                'l.quantity',
                'l.notes',
                'l.price',
                'l.status',
                'l.payment_status',
                'l.rute',
                'l.tanggal',
                'l.kode_resi',
                DB::raw('s.name as service_name'),
                DB::raw('u.nopol as unit_nopol'),
                DB::raw('r.name as route_name'),
            ])
            ->first();

        abort_unless($row !== null, 404);

        $kodeResi = trim((string) ($row->kode_resi ?? ''));

        return [
            'title' => 'Resi Bagasi',
            'id' => (int) $row->id,
            'kode_resi' => $kodeResi,
            'barcode_svg' => $kodeResi !== '' ? Code39::svgDataUri($kodeResi) : null,
            'sender_name' => (string) ($row->sender_name ?? ''),
            'sender_phone' => (string) ($row->sender_phone ?? ''),
            'sender_address' => (string) ($row->sender_address ?? ''),
            'receiver_name' => (string) ($row->receiver_name ?? ''),
            'receiver_phone' => (string) ($row->receiver_phone ?? ''),
            'receiver_address' => (string) ($row->receiver_address ?? ''),
            'service_name' => (string) ($row->service_name ?? ''),
            'rute' => (string) ($row->route_name ?? $row->rute ?? ''),
            'tanggal' => (string) ($row->tanggal ?? ''),
            'unit_nopol' => (string) ($row->unit_nopol ?? ''),
            'quantity' => (int) ($row->quantity ?? 0),
            'notes' => (string) ($row->notes ?? ''),
            'price' => (float) ($row->price ?? 0),
            'status' => (string) ($row->status ?? ''),
            'payment_status' => (string) ($row->payment_status ?? ''),
            'logo_data_uri' => $this->brandingLogoDataUri(),
        ];
    }

    private function brandingLogoDataUri(): ?string
    {
        $logoPath = public_path('images/qbus-logo-full.png');

        if (! is_file($logoPath)) {
            return null;
        }

        $contents = @file_get_contents($logoPath);

        if ($contents === false) {
            return null;
        }

        return 'data:image/png;base64,'.base64_encode($contents);
    }

    /**
     * @param  array<string, mixed>  $data
     */
    private function generatePdfFromView(string $view, array $data, string $title): string
    {
        $html = ViewFactory::make($view, $data)->render();

        return HeadlessPdf::renderHtmlToPdf($html, $title);
    }
}
