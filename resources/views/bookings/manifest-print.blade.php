<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $manifest['title'] ?? 'Manifest Keberangkatan' }}</title>
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
            max-width: 1120px;
            margin: 0 auto;
            padding: 18px;
        }
        .toolbar {
            display: flex;
            justify-content: flex-end;
            margin-bottom: 10px;
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
            padding: 16px 18px;
            box-shadow: 0 18px 44px rgba(15, 23, 42, 0.08);
        }
        .doc-head {
            display: table;
            width: 100%;
            margin-bottom: 10px;
            padding-bottom: 10px;
            border-bottom: 2px solid var(--ink);
        }
        .brand-cell,
        .title-cell,
        .code-cell {
            display: table-cell;
            vertical-align: top;
        }
        .brand-cell { width: 190px; }
        .brand-logo {
            width: 150px;
            height: auto;
            object-fit: contain;
        }
        .title-cell {
            padding: 0 14px;
        }
        .code-cell {
            width: 240px;
            text-align: right;
        }
        .eyebrow {
            margin-bottom: 4px;
            color: var(--brand);
            font-size: 10px;
            font-weight: 700;
            letter-spacing: .12em;
            text-transform: uppercase;
        }
        h1 {
            margin: 0 0 4px;
            font-size: 22px;
            line-height: 1.15;
        }
        .muted {
            color: var(--muted);
            font-size: 10px;
            line-height: 1.35;
        }
        .code {
            font-size: 12px;
            font-weight: 700;
        }
        .barcode {
            margin-top: 6px;
        }
        .barcode img {
            width: 210px;
            max-width: 100%;
            height: auto;
        }
        table {
            width: 100%;
            border-collapse: collapse;
        }
        th,
        td {
            border: 1px solid var(--line);
            padding: 5px 6px;
            vertical-align: top;
            font-size: 10px;
            line-height: 1.35;
        }
        th {
            background: var(--soft);
            color: var(--muted);
            font-size: 9px;
            font-weight: 700;
            letter-spacing: .08em;
            text-align: left;
            text-transform: uppercase;
        }
        .summary-table {
            margin-bottom: 10px;
        }
        .summary-table td {
            font-size: 11px;
            font-weight: 700;
        }
        .section-title {
            display: flex;
            justify-content: space-between;
            align-items: baseline;
            gap: 10px;
            margin: 11px 0 5px;
            font-size: 12px;
            font-weight: 700;
            letter-spacing: .08em;
            text-transform: uppercase;
        }
        .section-title .meta {
            color: var(--muted);
            font-size: 10px;
            font-weight: 400;
            letter-spacing: 0;
            text-transform: none;
        }
        .money {
            font-weight: 700;
            white-space: nowrap;
        }
        .note-stack {
            display: grid;
            gap: 2px;
        }
        .empty-row {
            text-align: center;
            color: var(--muted);
            padding: 10px 6px;
        }
        .danger-title {
            color: var(--danger);
        }
        .signature-table {
            margin-top: 12px;
            text-align: center;
        }
        .signature-table th,
        .signature-table td {
            width: 33.333%;
        }
        .signature-space {
            height: 42px;
        }
        .doc-footer {
            margin-top: 9px;
            display: flex;
            justify-content: space-between;
            gap: 12px;
            color: var(--muted);
            font-size: 9px;
        }
        @page {
            size: A4 landscape;
            margin: 8mm;
        }
        @media print {
            body { background: #ffffff; }
            .page { max-width: none; padding: 0; }
            .toolbar { display: none; }
            .sheet {
                border: 0;
                box-shadow: none;
                padding: 0;
            }
        }
    </style>
</head>
<body>
    <div class="page">
        @if (($exportMode ?? 'screen') !== 'pdf')
            <div class="toolbar">
                <button class="print-btn" onclick="window.print()">Print / Simpan PDF</button>
            </div>
        @endif

        <section class="sheet">
            <header class="doc-head">
                <div class="brand-cell">
                    @if (!empty($manifest['logo_data_uri']))
                        <img src="{{ $manifest['logo_data_uri'] }}" alt="OptiBus" class="brand-logo">
                    @endif
                </div>
                <div class="title-cell">
                    <div class="eyebrow">OptiBus Manifest</div>
                    <h1>{{ $manifest['title'] }}</h1>
                    <div class="muted">Daftar penumpang aktif, bagasi terpasang, dan history cancel untuk satu keberangkatan.</div>
                </div>
                <div class="code-cell">
                    <div class="code">{{ $manifest['departure_code'] }}</div>
                    @if (!empty($manifest['barcode_svg']))
                        <div class="barcode">
                            <img src="{{ $manifest['barcode_svg'] }}" alt="Barcode {{ $manifest['departure_code'] }}">
                        </div>
                    @endif
                </div>
            </header>

            <table class="summary-table">
                <tbody>
                    <tr>
                        <th>Rute</th>
                        <td>{{ $manifest['rute'] }}</td>
                        <th>Tanggal</th>
                        <td>{{ $manifest['tanggal'] }}</td>
                        <th>Jam</th>
                        <td>{{ $manifest['jam'] }}</td>
                        <th>Unit</th>
                        <td>Unit {{ $manifest['unit'] }}</td>
                    </tr>
                    <tr>
                        <th>Driver</th>
                        <td>{{ $manifest['driver_name'] ?: '-' }}</td>
                        <th>Nopol</th>
                        <td>{{ $manifest['armada_nopol'] ?: '-' }}</td>
                        <th>Total</th>
                        <td>{{ $manifest['total'] }}</td>
                        <th>Aktif</th>
                        <td>{{ $manifest['active'] }}</td>
                    </tr>
                    <tr>
                        <th>Cancel</th>
                        <td>{{ $manifest['canceled'] }}</td>
                        <th>Lunas</th>
                        <td>{{ $manifest['lunas'] }}</td>
                        <th>Belum Lunas</th>
                        <td>{{ $manifest['belum_lunas'] }}</td>
                        <th>Bagasi</th>
                        <td>{{ $manifest['luggage_total'] ?? count($manifest['luggages'] ?? []) }}</td>
                    </tr>
                </tbody>
            </table>

            <div class="section-title">
                <span>Penumpang Aktif</span>
                <span class="meta">{{ count($manifest['passengers']) }} data</span>
            </div>
            <table>
                <thead>
                    <tr>
                        <th>Kode Tiket</th>
                        <th>Seat</th>
                        <th>Penumpang</th>
                        <th>Jemput</th>
                        <th>Pembayaran</th>
                        <th>Total</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($manifest['passengers'] as $row)
                        <tr>
                            <td>{{ $row['ticket_code'] ?? '-' }}</td>
                            <td>{{ $row['seat'] ?? '-' }}</td>
                            <td>
                                <strong>{{ $row['name'] ?? '-' }}</strong><br>
                                <span class="muted">{{ $row['phone'] ?? '-' }}</span>
                            </td>
                            <td>
                                {{ $row['pickup_point'] ?? '-' }}<br>
                                <span class="muted">{{ $row['segment_name'] ?? '-' }}</span>
                            </td>
                            <td>{{ $row['pembayaran'] ?? '-' }}</td>
                            <td class="money">Rp {{ number_format(max(((float) ($row['price'] ?? 0)) - ((float) ($row['discount'] ?? 0)), 0), 0, ',', '.') }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="empty-row">Belum ada penumpang aktif.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>

            <div class="section-title">
                <span>Bagasi Terpasang</span>
                <span class="meta">{{ count($manifest['luggages'] ?? []) }} data &middot; Revenue Rp {{ number_format((float) ($manifest['luggage_revenue'] ?? 0), 0, ',', '.') }}</span>
            </div>
            <table>
                <thead>
                    <tr>
                        <th>Kode Resi</th>
                        <th>Pengirim / Penerima</th>
                        <th>Rute / Tanggal</th>
                        <th>Qty</th>
                        <th>Status</th>
                        <th>Pembayaran</th>
                        <th>Tarif</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse (($manifest['luggages'] ?? []) as $row)
                        <tr>
                            <td><strong>{{ $row['kode_resi'] ?: '-' }}</strong></td>
                            <td>
                                <div class="note-stack">
                                    <strong>{{ $row['sender_name'] ?: '-' }} -&gt; {{ $row['receiver_name'] ?: '-' }}</strong>
                                    @if (!empty($row['notes']))
                                        <span class="muted">{{ $row['notes'] }}</span>
                                    @endif
                                </div>
                            </td>
                            <td>
                                {{ $row['rute'] ?: '-' }}<br>
                                <span class="muted">{{ $row['tanggal'] ?: '-' }}</span>
                            </td>
                            <td>{{ (int) ($row['quantity'] ?? 0) }}</td>
                            <td>{{ $row['status'] ?: '-' }}</td>
                            <td>{{ $row['payment_status'] ?: '-' }}</td>
                            <td class="money">Rp {{ number_format((float) ($row['price'] ?? 0), 0, ',', '.') }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="empty-row">Belum ada bagasi yang terpasang pada keberangkatan ini.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>

            <div class="section-title danger-title">
                <span>History Cancel</span>
                <span class="meta">{{ count($manifest['history_passengers'] ?? []) }} data</span>
            </div>
            <table>
                <thead>
                    <tr>
                        <th>Kode Tiket</th>
                        <th>Seat</th>
                        <th>Penumpang</th>
                        <th>Jemput</th>
                        <th>Status</th>
                        <th>Pembayaran</th>
                        <th>Log Cancel</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse (($manifest['history_passengers'] ?? []) as $row)
                        <tr>
                            <td>{{ $row['ticket_code'] ?? '-' }}</td>
                            <td>{{ $row['seat'] ?? '-' }}</td>
                            <td>
                                <strong>{{ $row['name'] ?? '-' }}</strong><br>
                                <span class="muted">{{ $row['phone'] ?? '-' }}</span>
                            </td>
                            <td>
                                {{ $row['pickup_point'] ?? '-' }}<br>
                                <span class="muted">{{ $row['segment_name'] ?? '-' }}</span>
                            </td>
                            <td>{{ $row['status'] ?? '-' }}</td>
                            <td>{{ $row['pembayaran'] ?? '-' }}</td>
                            <td>
                                <div class="note-stack">
                                    <span>{{ !empty($row['cancel_reason']) ? $row['cancel_reason'] : 'Tanpa alasan' }}</span>
                                    @if (!empty($row['canceled_by']))
                                        <span class="muted">Oleh {{ $row['canceled_by'] }}</span>
                                    @endif
                                    @if (!empty($row['canceled_at']))
                                        <span class="muted">{{ $row['canceled_at'] }}</span>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="empty-row">Belum ada penumpang cancel pada keberangkatan ini.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>

            <table class="signature-table">
                <thead>
                    <tr>
                        <th>Dispatcher / Admin</th>
                        <th>Driver</th>
                        <th>Checker / Verifikator</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td><div class="signature-space"></div>______________________</td>
                        <td><div class="signature-space"></div>{{ $manifest['driver_name'] ?: '______________________' }}</td>
                        <td><div class="signature-space"></div>______________________</td>
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
