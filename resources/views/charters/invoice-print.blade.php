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
            --muted: #64748b;
            --line: #cbd5e1;
            --soft: #f8fafc;
            --brand: #0369a1;
            --brand-soft: #e0f2fe;
            --danger: #b91c1c;
        }
        * { box-sizing: border-box; }
        body {
            margin: 0;
            font-family: Arial, Helvetica, sans-serif;
            color: var(--ink);
            background: #eef4f8;
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
            background: white;
            color: var(--ink);
            padding: 9px 13px;
            border-radius: 999px;
            font-size: 12px;
            cursor: pointer;
        }
        .sheet {
            background: white;
            border: 1px solid #dbe4ea;
            border-radius: 22px;
            padding: 18px;
            box-shadow: 0 18px 50px rgba(15, 23, 42, 0.08);
        }
        .hero {
            display: flex;
            justify-content: space-between;
            gap: 14px;
            margin-bottom: 14px;
            padding-bottom: 14px;
            border-bottom: 1px solid #e7eef3;
        }
        .brand-block {
            display: flex;
            align-items: flex-start;
            gap: 12px;
        }
        .brand-logo {
            width: 138px;
            height: auto;
            object-fit: contain;
        }
        .brand-copy {
            padding-top: 3px;
        }
        .eyebrow {
            font-size: 11px;
            text-transform: uppercase;
            letter-spacing: .12em;
            color: var(--brand);
            font-weight: 700;
            margin-bottom: 6px;
        }
        h1 {
            margin: 0 0 4px;
            font-size: 24px;
            line-height: 1.15;
        }
        .muted {
            color: var(--muted);
            font-size: 12px;
            line-height: 1.45;
        }
        .hero-side {
            min-width: 230px;
            display: grid;
            gap: 8px;
            justify-items: end;
        }
        .pill {
            display: inline-flex;
            align-items: center;
            padding: 7px 11px;
            border-radius: 999px;
            background: var(--brand-soft);
            color: var(--brand);
            font-weight: 700;
            font-size: 11px;
        }
        .barcode-card {
            border: 1px solid var(--line);
            background: #fcfeff;
            border-radius: 14px;
            padding: 8px 10px;
            text-align: center;
            width: 100%;
        }
        .barcode-card img {
            width: 100%;
            max-width: 220px;
            height: auto;
        }
        .grid-4,
        .grid-3,
        .grid-2 {
            display: grid;
            gap: 10px;
            margin-bottom: 12px;
        }
        .grid-4 { grid-template-columns: repeat(4, minmax(0, 1fr)); }
        .grid-3 { grid-template-columns: repeat(3, minmax(0, 1fr)); }
        .grid-2 { grid-template-columns: repeat(2, minmax(0, 1fr)); }
        .card {
            border: 1px solid var(--line);
            border-radius: 14px;
            padding: 10px 12px;
            background: var(--soft);
        }
        .label {
            color: var(--muted);
            font-size: 10px;
            text-transform: uppercase;
            letter-spacing: .08em;
            margin-bottom: 5px;
        }
        .value {
            font-size: 13px;
            font-weight: 700;
            line-height: 1.35;
        }
        .sub-value {
            color: var(--muted);
            font-size: 11px;
            margin-top: 3px;
            line-height: 1.45;
        }
        .section-title {
            margin: 14px 0 8px;
            font-size: 14px;
            font-weight: 700;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            font-size: 11px;
        }
        th {
            text-align: left;
            padding: 8px 10px;
            color: var(--muted);
            font-size: 10px;
            text-transform: uppercase;
            letter-spacing: .08em;
            border-bottom: 1px solid var(--line);
            background: #f8fbfd;
        }
        td {
            padding: 8px 10px;
            border-bottom: 1px solid #e5edf3;
            vertical-align: top;
        }
        .total-box {
            border: 1px solid var(--line);
            border-radius: 16px;
            overflow: hidden;
            margin-top: 10px;
        }
        .total-row {
            display: flex;
            justify-content: space-between;
            gap: 12px;
            padding: 8px 12px;
            font-size: 12px;
            border-bottom: 1px solid #e5edf3;
            background: white;
        }
        .total-row:last-child {
            border-bottom: 0;
        }
        .total-row.grand {
            background: #0f172a;
            color: white;
            font-weight: 700;
        }
        .notes-box {
            margin-top: 12px;
            border: 1px dashed var(--line);
            border-radius: 14px;
            padding: 10px 12px;
            background: #fcfeff;
            color: var(--muted);
            font-size: 11px;
            line-height: 1.5;
        }
        .doc-footer {
            margin-top: 12px;
            display: flex;
            justify-content: space-between;
            gap: 12px;
            font-size: 10px;
            color: var(--muted);
        }
        .danger-box {
            margin-top: 12px;
            border: 1px solid #fecaca;
            background: #fff7f7;
            color: var(--danger);
        }
        @media print {
            body { background: white; }
            .page { padding: 0; }
            .toolbar { display: none; }
            .sheet {
                border: 0;
                box-shadow: none;
                border-radius: 0;
                padding: 8px 10px 0;
            }
        }
        @media (max-width: 780px) {
            .hero { display: grid; }
            .grid-4,
            .grid-3,
            .grid-2 { grid-template-columns: 1fr; }
            .hero-side { justify-items: start; min-width: 0; }
            .doc-footer { display: grid; }
        }
    </style>
</head>
<body>
@php
    $autoPrint = request()->boolean('auto_print');
    $status = strtolower((string) ($invoice['status'] ?? 'active'));
@endphp
<div class="page">
    <div class="toolbar">
        <button class="print-btn" type="button" onclick="window.print()">Cetak Invoice</button>
    </div>

    <div class="sheet">
        <section class="hero">
            <div class="brand-block">
                @if(!empty($invoice['logo_data_uri']))
                    <img class="brand-logo" src="{{ $invoice['logo_data_uri'] }}" alt="CabooQ">
                @endif
                <div class="brand-copy">
                    <div class="eyebrow">Invoice Carter</div>
                    <h1>{{ $invoice['invoice_code'] ?? '-' }}</h1>
                    <div class="muted">
                        {{ $invoice['name'] ?? '-' }}<br>
                        {{ $invoice['company_name'] ?? 'Customer charter' }}{{ !empty($invoice['phone']) ? ' • '.$invoice['phone'] : '' }}
                    </div>
                </div>
            </div>

            <div class="hero-side">
                <div class="pill">{{ $invoice['payment_status'] ?? '-' }}</div>
                @if(!empty($invoice['barcode_svg']))
                    <div class="barcode-card">
                        <img src="{{ $invoice['barcode_svg'] }}" alt="Barcode invoice">
                        <div class="muted" style="margin-top: 6px;">{{ $invoice['invoice_code'] ?? '-' }}</div>
                    </div>
                @endif
            </div>
        </section>

        <section class="grid-4">
            <div class="card">
                <div class="label">Tanggal</div>
                <div class="value">{{ $invoice['start_date'] ?? '-' }}</div>
                <div class="sub-value">Selesai {{ $invoice['end_date'] ?? '-' }}</div>
            </div>
            <div class="card">
                <div class="label">Jam & Layanan</div>
                <div class="value">{{ $invoice['departure_time'] ?? '--:--' }}</div>
                <div class="sub-value">{{ $invoice['layanan'] ?? '-' }}</div>
            </div>
            <div class="card">
                <div class="label">Driver</div>
                <div class="value">{{ $invoice['driver_name'] ?? '-' }}</div>
                <div class="sub-value">{{ $invoice['unit_category'] ?? 'Kategori armada belum dipilih' }}</div>
            </div>
            <div class="card">
                <div class="label">Nopol</div>
                <div class="value">{{ $invoice['armada_nopol'] ?? $invoice['unit_nopol'] ?? '-' }}</div>
                <div class="sub-value">Status {{ strtoupper((string) ($invoice['status'] ?? 'active')) }}</div>
            </div>
        </section>

        <p class="section-title">Rute dan Tagihan</p>
        <section class="grid-2">
            <div class="card">
                <table>
                    <tbody>
                        <tr>
                            <th style="width: 130px;">Titik Jemput</th>
                            <td>{{ $invoice['pickup_point'] ?? '-' }}</td>
                        </tr>
                        <tr>
                            <th>Titik Antar</th>
                            <td>{{ $invoice['drop_point'] ?? '-' }}</td>
                        </tr>
                        <tr>
                            <th>Status Bayar</th>
                            <td>{{ $invoice['payment_status'] ?? '-' }}</td>
                        </tr>
                        <tr>
                            <th>Status BOP</th>
                            <td>{{ $invoice['bop_status'] ?? '-' }}</td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <div>
                <div class="total-box">
                    <div class="total-row">
                        <span>Total Charter</span>
                        <strong>Rp {{ number_format((float) ($invoice['price'] ?? 0), 0, ',', '.') }}</strong>
                    </div>
                    <div class="total-row">
                        <span>Down Payment</span>
                        <strong>Rp {{ number_format((float) ($invoice['down_payment'] ?? 0), 0, ',', '.') }}</strong>
                    </div>
                    <div class="total-row">
                        <span>Biaya Operasional</span>
                        <strong>Rp {{ number_format((float) ($invoice['bop_price'] ?? 0), 0, ',', '.') }}</strong>
                    </div>
                    <div class="total-row grand">
                        <span>Sisa Pembayaran</span>
                        <strong>Rp {{ number_format((float) ($invoice['remaining_payment'] ?? 0), 0, ',', '.') }}</strong>
                    </div>
                </div>
            </div>
        </section>

        <div class="notes-box">
            Invoice ini adalah ringkasan operasional dan pembayaran charter. Mohon cek ulang detail rute, armada, jadwal, dan nominal sebelum keberangkatan.
        </div>

        @if($status === 'canceled')
            <div class="card danger-box">
                <div class="label" style="color: #b91c1c;">Status Charter</div>
                <div class="value" style="color: #b91c1c;">CHARTER DIBATALKAN</div>
                <div class="sub-value" style="color: #b91c1c;">Dokumen ini hanya dipakai sebagai arsip pembatalan dan rekonsiliasi pembayaran.</div>
            </div>
        @endif

        <div class="doc-footer">
            <div>Dicetak dari sistem CabooQ</div>
            <div>{{ $invoice['invoice_code'] ?? '-' }}</div>
        </div>
    </div>
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
