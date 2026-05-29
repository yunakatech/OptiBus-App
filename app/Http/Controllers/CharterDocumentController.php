<?php

namespace App\Http\Controllers;

use App\Support\Code39;
use App\Support\HeadlessPdf;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\View as ViewFactory;

class CharterDocumentController extends Controller
{
    public function print(int $id): View
    {
        return view('charters.invoice-print', [
            'invoice' => $this->charterPayload($id),
        ]);
    }

    public function pdf(Request $request, int $id)
    {
        $payload = $this->charterPayload($id);

        $pdfPath = $this->generatePdfFromView(
            'charters.invoice-print',
            [
                'invoice' => $payload,
                'exportMode' => 'pdf',
            ],
            sprintf('Invoice Carter %s', $payload['invoice_code']),
        );

        $filename = sprintf('invoice-carter-%s.pdf', strtolower((string) $payload['invoice_code']));

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
    private function charterPayload(int $id): array
    {
        abort_unless(Schema::hasTable('charters'), 404);

        $hasArmadaIdColumn = Schema::hasColumn('charters', 'armada_id');
        $hasArmadaNopolColumn = Schema::hasColumn('charters', 'armada_nopol');
        $canJoinArmadas = $hasArmadaIdColumn && Schema::hasTable('armadas');
        $hasStatusColumn = Schema::hasColumn('charters', 'status');

        $query = DB::table('charters as c')
            ->leftJoin('units as u', 'c.unit_id', '=', 'u.id')
            ->where('c.id', $id);

        if ($canJoinArmadas) {
            $query->leftJoin('armadas as a', 'c.armada_id', '=', 'a.id');
        }

        $select = [
            'c.id',
            'c.name',
            'c.company_name',
            'c.phone',
            'c.start_date',
            'c.end_date',
            'c.departure_time',
            'c.pickup_point',
            'c.drop_point',
            'c.driver_name',
            'c.price',
            'c.layanan',
            'c.bop_price',
            'c.bop_status',
            'c.down_payment',
            'c.payment_status',
            'c.created_at',
            DB::raw($hasStatusColumn ? 'c.status as status' : "CASE WHEN c.payment_status = 'Canceled' THEN 'canceled' WHEN c.bop_status = 'done' THEN 'done' ELSE 'active' END as status"),
            DB::raw('u.nopol as unit_nopol'),
            DB::raw('u.category as unit_category'),
        ];

        if ($hasArmadaNopolColumn && $canJoinArmadas) {
            $select[] = DB::raw('COALESCE(c.armada_nopol, a.nopol) as armada_nopol');
        } elseif ($hasArmadaNopolColumn) {
            $select[] = 'c.armada_nopol';
        } elseif ($canJoinArmadas) {
            $select[] = DB::raw('a.nopol as armada_nopol');
        } else {
            $select[] = DB::raw('NULL as armada_nopol');
        }

        $row = $query->select($select)->first();

        abort_unless($row !== null, 404);

        $createdDate = preg_replace('/[^0-9]/', '', substr((string) ($row->created_at ?? $row->start_date ?? ''), 0, 10));
        $invoiceCode = sprintf('CHT-%s-%04d', $createdDate !== '' ? $createdDate : date('Ymd'), (int) $row->id);
        $price = (float) ($row->price ?? 0);
        $downPayment = (float) ($row->down_payment ?? 0);

        return [
            'title' => 'Invoice Carter',
            'invoice_code' => $invoiceCode,
            'barcode_svg' => Code39::svgDataUri($invoiceCode),
            'id' => (int) $row->id,
            'name' => (string) ($row->name ?? ''),
            'company_name' => $row->company_name ? (string) $row->company_name : null,
            'phone' => $row->phone ? (string) $row->phone : null,
            'start_date' => (string) ($row->start_date ?? ''),
            'end_date' => (string) ($row->end_date ?? ''),
            'departure_time' => $row->departure_time ? substr((string) $row->departure_time, 0, 5) : null,
            'pickup_point' => (string) ($row->pickup_point ?? ''),
            'drop_point' => (string) ($row->drop_point ?? ''),
            'driver_name' => $row->driver_name ? (string) $row->driver_name : null,
            'layanan' => $row->layanan ? (string) $row->layanan : null,
            'price' => $price,
            'bop_price' => (float) ($row->bop_price ?? 0),
            'bop_status' => $row->bop_status ? (string) $row->bop_status : null,
            'down_payment' => $downPayment,
            'remaining_payment' => max($price - $downPayment, 0),
            'payment_status' => $row->payment_status ? (string) $row->payment_status : null,
            'status' => $row->status ? (string) $row->status : 'active',
            'unit_category' => $row->unit_category ? (string) $row->unit_category : null,
            'unit_nopol' => $row->unit_nopol ? (string) $row->unit_nopol : null,
            'armada_nopol' => $row->armada_nopol ? (string) $row->armada_nopol : null,
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
