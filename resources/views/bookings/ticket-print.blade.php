<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Tiket {{ $ticket['ticket_code'] ?? '' }}</title>
    <style>
        :root {
            --ink: #0f172a;
            --muted: #475569;
            --line: #cbd5e1;
            --soft: #f8fafc;
            --brand: #075985;
        }
        * { box-sizing: border-box; }
        body {
            margin: 0;
            background: #eef4f8;
            color: var(--ink);
            font-family: Arial, Helvetica, sans-serif;
        }
        .page {
            min-height: 100vh;
            display: grid;
            place-items: center;
            padding: 26px;
        }
        .toolbar {
            position: fixed;
            top: 18px;
            right: 18px;
        }
        .print-btn {
            border: 1px solid var(--line);
            background: #ffffff;
            color: var(--ink);
            padding: 10px 14px;
            border-radius: 999px;
            font-size: 13px;
            cursor: pointer;
        }
        .ticket {
            width: min(760px, 100%);
            background: #ffffff;
            border: 1px solid var(--line);
            padding: 22px 24px;
            box-shadow: 0 20px 50px rgba(15, 23, 42, 0.08);
        }
        .doc-head {
            display: table;
            width: 100%;
            margin-bottom: 14px;
            padding-bottom: 14px;
            border-bottom: 2px solid var(--ink);
        }
        .brand-cell,
        .title-cell {
            display: table-cell;
            vertical-align: top;
        }
        .brand-logo {
            width: 150px;
            height: auto;
            object-fit: contain;
        }
        .title-cell {
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
        .doc-code {
            color: var(--muted);
            font-size: 12px;
            line-height: 1.5;
        }
        .barcode-row {
            margin: 8px 0 14px;
            text-align: right;
        }
        .barcode-row img {
            width: 260px;
            max-width: 100%;
            height: auto;
        }
        table {
            width: 100%;
            border-collapse: collapse;
        }
        .info-table {
            margin-top: 12px;
            font-size: 12px;
        }
        .info-table th,
        .info-table td {
            border: 1px solid var(--line);
            padding: 9px 10px;
            vertical-align: top;
        }
        .info-table th {
            width: 24%;
            background: var(--soft);
            color: var(--muted);
            font-size: 10px;
            letter-spacing: .08em;
            text-align: left;
            text-transform: uppercase;
        }
        .info-table td {
            font-weight: 700;
        }
        .sub-value {
            display: block;
            margin-top: 3px;
            color: var(--muted);
            font-size: 11px;
            font-weight: 400;
            line-height: 1.45;
        }
        .section-title {
            margin: 14px 0 7px;
            color: var(--ink);
            font-size: 13px;
            font-weight: 700;
            letter-spacing: .08em;
            text-transform: uppercase;
        }
        .amount {
            font-size: 20px;
            font-weight: 800;
        }
        .note {
            margin-top: 12px;
            padding-top: 10px;
            border-top: 1px dashed var(--line);
            color: var(--muted);
            font-size: 11px;
            line-height: 1.55;
        }
        .signature-table {
            margin-top: 16px;
            font-size: 12px;
            text-align: center;
        }
        .signature-table th,
        .signature-table td {
            width: 50%;
            border: 1px solid var(--line);
            padding: 9px 12px;
        }
        .signature-table th {
            background: var(--soft);
            color: var(--muted);
            font-size: 10px;
            letter-spacing: .1em;
            text-transform: uppercase;
        }
        .signature-space {
            height: 52px;
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
            .page { min-height: auto; display: block; padding: 0; }
            .toolbar { display: none; }
            .ticket { width: 100%; border: 0; box-shadow: none; padding: 0; }
        }
    </style>
</head>
<body>
    @if (($exportMode ?? 'screen') !== 'pdf')
        <div class="toolbar">
            <button class="print-btn" onclick="window.print()">Print / Simpan PDF</button>
        </div>
    @endif

    <div class="page">
        <section class="ticket">
            <header class="doc-head">
                <div class="brand-cell">
                    @if (!empty($ticket['logo_data_uri']))
                        <img src="{{ $ticket['logo_data_uri'] }}" alt="OptiBus" class="brand-logo">
                    @endif
                </div>
                <div class="title-cell">
                    <div class="eyebrow">OptiBus Ticket</div>
                    <h1>Tiket Keberangkatan</h1>
                    <div class="doc-code">
                        Kode Tiket: <strong>{{ $ticket['ticket_code'] }}</strong><br>
                        Keberangkatan: <strong>{{ $ticket['departure_code'] }}</strong>
                    </div>
                </div>
            </header>

            @if (!empty($ticket['barcode_svg']))
                <div class="barcode-row">
                    <img src="{{ $ticket['barcode_svg'] }}" alt="Barcode {{ $ticket['ticket_code'] }}">
                </div>
            @endif

            <div class="section-title">Data Penumpang dan Perjalanan</div>
            <table class="info-table">
                <tbody>
                    <tr>
                        <th>Penumpang</th>
                        <td>
                            {{ $ticket['name'] ?: '-' }}
                            <span class="sub-value">{{ $ticket['phone'] ?: '-' }}</span>
                        </td>
                        <th>Seat</th>
                        <td>
                            {{ $ticket['seat'] ?: '-' }}
                            <span class="sub-value">{{ $ticket['status'] ?: '-' }}</span>
                        </td>
                    </tr>
                    <tr>
                        <th>Rute</th>
                        <td>
                            {{ $ticket['rute'] ?: '-' }}
                            <span class="sub-value">{{ $ticket['segment_name'] ?: '-' }}</span>
                        </td>
                        <th>Tanggal / Jam</th>
                        <td>
                            {{ $ticket['tanggal'] ?: '-' }} &bull; {{ $ticket['jam'] ?: '-' }}
                            <span class="sub-value">Unit {{ $ticket['unit'] ?? 1 }}</span>
                        </td>
                    </tr>
                    <tr>
                        <th>Driver</th>
                        <td>
                            {{ $ticket['driver_name'] ?: '-' }}
                            <span class="sub-value">Nopol {{ $ticket['armada_nopol'] ?: '-' }}</span>
                        </td>
                        <th>Jemput</th>
                        <td>{{ $ticket['pickup_point'] ?: '-' }}</td>
                    </tr>
                </tbody>
            </table>

            <div class="section-title">Pembayaran</div>
            <table class="info-table">
                <tbody>
                    <tr>
                        <th>Metode / Status</th>
                        <td>{{ $ticket['pembayaran'] ?: '-' }}</td>
                        <th>Total</th>
                        <td class="amount">Rp {{ number_format(max(((float) ($ticket['price'] ?? 0)) - ((float) ($ticket['discount'] ?? 0)), 0), 0, ',', '.') }}</td>
                    </tr>
                </tbody>
            </table>

            <div class="note">
                Tunjukkan tiket ini saat keberangkatan. Simpan PDF jika diperlukan untuk arsip.
            </div>

            <table class="signature-table">
                <thead>
                    <tr>
                        <th>Petugas OptiBus</th>
                        <th>Penumpang</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td><div class="signature-space"></div>______________________</td>
                        <td><div class="signature-space"></div>{{ $ticket['name'] ?: '______________________' }}</td>
                    </tr>
                </tbody>
            </table>

            <div class="doc-footer">
                <div>OptiBus Booking &amp; Operations Workspace</div>
                <div>Generated {{ now()->format('d/m/Y H:i') }}</div>
            </div>
        </section>
    </div>
    @if (($exportMode ?? 'screen') !== 'pdf' && request()->boolean('auto_print'))
        <script>
            window.addEventListener('load', () => {
                window.setTimeout(() => window.print(), 250);
            });
        </script>
    @endif
</body>
</html>
