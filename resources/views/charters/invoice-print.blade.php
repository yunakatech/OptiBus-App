<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $invoice['title'] ?? 'Invoice Carter' }} {{ $invoice['invoice_code'] ?? '' }}</title>
    <style>
        :root {
            color-scheme: light;
            --ink: #0f172a;
            --muted: #475569;
            --line: #cbd5e1;
            --soft: #f8fafc;
            --brand: #075985;
            --danger: #991b1b;
        }
        * { box-sizing: border-box; }
        body {
            margin: 0;
            background: #eef4f8;
            color: var(--ink);
            font-family: Arial, Helvetica, sans-serif;
        }
        .page {
            max-width: 850px;
            margin: 0 auto;
            padding: 18px;
        }
        .toolbar {
            display: flex;
            justify-content: flex-end;
            margin-bottom: 12px;
        }
        .print-btn {
            border: 1px solid var(--line);
            background: #ffffff;
            color: var(--ink);
            padding: 9px 13px;
            border-radius: 999px;
            font-size: 12px;
            cursor: pointer;
        }
        .sheet {
            background: #ffffff;
            border: 1px solid var(--line);
            padding: 20px 22px;
            box-shadow: 0 18px 44px rgba(15, 23, 42, 0.08);
        }
        .doc-head {
            width: 100%;
            margin-bottom: 14px;
            padding-bottom: 12px;
            border-bottom: 2px solid var(--ink);
        }
        .doc-head-main {
            display: table;
            width: 100%;
            margin-bottom: 8px;
        }
        .brand-cell,
        .status-cell {
            display: table-cell;
            vertical-align: top;
        }
        .brand-cell {
            width: 180px;
        }
        .brand-logo {
            width: 148px;
            height: auto;
            object-fit: contain;
        }
        .title-cell {
            padding: 2px 0 0;
        }
        .status-cell {
            width: 260px;
            text-align: right;
        }
        .eyebrow {
            margin-bottom: 5px;
            color: var(--brand);
            font-size: 11px;
            font-weight: 700;
            letter-spacing: .12em;
            text-transform: uppercase;
        }
        h1 {
            margin: 0 0 5px;
            font-size: 26px;
            line-height: 1.15;
        }
        .muted {
            color: var(--muted);
            font-size: 11px;
            line-height: 1.45;
        }
        .status {
            display: inline-block;
            margin: 0 0 6px;
            border: 1px solid var(--line);
            padding: 5px 9px;
            color: var(--brand);
            font-size: 11px;
            font-weight: 700;
            letter-spacing: .04em;
            text-transform: uppercase;
        }
        .barcode {
            text-align: right;
        }
        .barcode img {
            display: block;
            width: 210px;
            max-width: 100%;
            height: auto;
            margin-left: auto;
        }
        table {
            width: 100%;
            border-collapse: collapse;
        }
        th,
        td {
            border: 1px solid var(--line);
            padding: 8px 9px;
            vertical-align: top;
            font-size: 11px;
            line-height: 1.45;
        }
        th {
            background: var(--soft);
            color: var(--muted);
            font-size: 10px;
            letter-spacing: .08em;
            text-align: left;
            text-transform: uppercase;
        }
        td {
            font-weight: 700;
        }
        .section-title {
            margin: 14px 0 7px;
            font-size: 13px;
            font-weight: 700;
            letter-spacing: .08em;
            text-transform: uppercase;
        }
        .normal {
            font-weight: 400;
        }
        .money {
            font-weight: 800;
            text-align: right;
            white-space: nowrap;
        }
        .grand th,
        .grand td {
            background: #0f172a;
            color: #ffffff;
            font-size: 13px;
        }
        .danger th,
        .danger td {
            background: #fff7f7;
            color: var(--danger);
        }
        .doc-footer {
            margin-top: 12px;
            display: flex;
            justify-content: space-between;
            gap: 12px;
            color: var(--muted);
            font-size: 10px;
        }
        @media print {
            body { background: #ffffff; }
            .page { padding: 0; }
            .toolbar { display: none; }
            .sheet {
                border: 0;
                box-shadow: none;
                padding: 8px 10px 0;
            }
        }
        @media (max-width: 780px) {
            .doc-head-main,
            .brand-cell,
            .status-cell {
                display: block;
                width: 100%;
                text-align: left;
            }
            .title-cell {
                padding: 12px 0;
            }
            .barcode img {
                margin-left: 0;
            }
        }
    </style>
</head>
<body>
@php
    $autoPrint = request()->boolean('auto_print');
    $status = strtolower((string) ($invoice['status'] ?? 'active'));
@endphp
<div class="page">
    @if (($exportMode ?? 'screen') !== 'pdf')
        <div class="toolbar">
            <button class="print-btn" type="button" onclick="window.print()">Cetak Invoice</button>
        </div>
    @endif

    <section class="sheet">
        <header class="doc-head">
            <div class="doc-head-main">
                <div class="brand-cell">
                    @if(!empty($invoice['logo_data_uri']))
                        <img class="brand-logo" src="{{ $invoice['logo_data_uri'] }}" alt="OptiBus">
                    @endif
                </div>
                <div class="status-cell">
                    <div class="status">{{ $invoice['payment_status'] ?? '-' }}</div>
                    @if(!empty($invoice['barcode_svg']))
                        <div class="barcode">
                            <img src="{{ $invoice['barcode_svg'] }}" alt="Barcode invoice">
                        </div>
                    @endif
                </div>
            </div>
            <div class="title-cell">
                <div class="eyebrow">Invoice Carter</div>
                <h1>{{ $invoice['invoice_code'] ?? '-' }}</h1>
                <div class="muted">
                    {{ $invoice['name'] ?? '-' }}<br>
                    {{ $invoice['company_name'] ?? 'Customer charter' }}
                    @if(!empty($invoice['phone']))
                        &bull; {{ $invoice['phone'] }}
                    @endif
                </div>
            </div>
        </header>

        <div class="section-title">Detail Customer dan Perjalanan</div>
        <table>
            <tbody>
                <tr>
                    <th>Customer</th>
                    <td>{{ $invoice['name'] ?? '-' }}</td>
                    <th>Perusahaan</th>
                    <td>{{ $invoice['company_name'] ?? '-' }}</td>
                </tr>
                <tr>
                    <th>No. HP</th>
                    <td>{{ $invoice['phone'] ?? '-' }}</td>
                    <th>Layanan</th>
                    <td>{{ $invoice['layanan'] ?? '-' }}</td>
                </tr>
                <tr>
                    <th>Tanggal Mulai</th>
                    <td>{{ $invoice['start_date'] ?? '-' }}</td>
                    <th>Tanggal Selesai</th>
                    <td>{{ $invoice['end_date'] ?? '-' }}</td>
                </tr>
                <tr>
                    <th>Jam</th>
                    <td>{{ $invoice['departure_time'] ?? '--:--' }}</td>
                    <th>Status Charter</th>
                    <td>{{ strtoupper((string) ($invoice['status'] ?? 'active')) }}</td>
                </tr>
                <tr>
                    <th>Driver</th>
                    <td>{{ $invoice['driver_name'] ?? '-' }}</td>
                    <th>Armada</th>
                    <td>{{ $invoice['unit_category'] ?? $invoice['unit_nama_kategori'] ?? '-' }}</td>
                </tr>
                <tr>
                    <th>Nopol</th>
                    <td>{{ $invoice['armada_nopol'] ?? '-' }}</td>
                    <th>Kode Invoice</th>
                    <td>{{ $invoice['invoice_code'] ?? '-' }}</td>
                </tr>
                <tr>
                    <th>Titik Jemput</th>
                    <td colspan="3">{{ $invoice['pickup_point'] ?? '-' }}</td>
                </tr>
                <tr>
                    <th>Titik Antar</th>
                    <td colspan="3">{{ $invoice['drop_point'] ?? '-' }}</td>
                </tr>
            </tbody>
        </table>

        <div class="section-title">Rincian Tagihan</div>
        <table>
            <thead>
                <tr>
                    <th>Deskripsi</th>
                    <th style="width: 180px; text-align: right;">Nominal</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td>Total Charter</td>
                    <td class="money">Rp {{ number_format((float) ($invoice['price'] ?? 0), 0, ',', '.') }}</td>
                </tr>
                <tr>
                    <td>Down Payment</td>
                    <td class="money">Rp {{ number_format((float) ($invoice['down_payment'] ?? 0), 0, ',', '.') }}</td>
                </tr>
                <tr class="grand">
                    <th>Sisa Pembayaran</th>
                    <td class="money">Rp {{ number_format((float) ($invoice['remaining_payment'] ?? 0), 0, ',', '.') }}</td>
                </tr>
            </tbody>
        </table>

        <div class="section-title">Catatan</div>
        <table>
            <tbody>
                <tr>
                    <th style="width: 140px;">Keterangan</th>
                    <td class="normal">
                        Invoice ini adalah ringkasan operasional dan pembayaran charter. Mohon cek ulang detail rute, armada, jadwal, dan nominal sebelum keberangkatan.
                    </td>
                </tr>
            </tbody>
        </table>

        @if($status === 'canceled')
            <div class="section-title">Status Pembatalan</div>
            <table>
                <tbody>
                    <tr class="danger">
                        <th style="width: 140px;">Status Charter</th>
                        <td>CHARTER DIBATALKAN. Dokumen ini hanya dipakai sebagai arsip pembatalan dan rekonsiliasi pembayaran.</td>
                    </tr>
                </tbody>
            </table>
        @endif

        <div class="doc-footer">
            <div>Dicetak dari sistem OptiBus</div>
            <div>{{ $invoice['invoice_code'] ?? '-' }}</div>
        </div>
    </section>
</div>

@if($autoPrint)
    <script>
        window.addEventListener('load', () => {
            setTimeout(() => window.print(), 180);
        });
    </script>
@endif
</body>
</html>
