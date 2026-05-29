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
            --muted: #64748b;
            --line: #cbd5e1;
            --soft: #f8fafc;
            --brand: #0369a1;
            --brand-soft: #e0f2fe;
            --danger: #b91c1c;
            --danger-soft: #fee2e2;
        }
        * { box-sizing: border-box; }
        body {
            margin: 0;
            font-family: Arial, Helvetica, sans-serif;
            color: var(--ink);
            background: #eef4f8;
        }
        .page {
            max-width: 1024px;
            margin: 0 auto;
            padding: 24px;
        }
        .toolbar {
            display: flex;
            justify-content: flex-end;
            margin-bottom: 16px;
        }
        .print-btn {
            border: 1px solid var(--line);
            background: white;
            color: var(--ink);
            padding: 10px 14px;
            border-radius: 999px;
            font-size: 13px;
            cursor: pointer;
        }
        .sheet {
            background: white;
            border: 1px solid #dbe4ea;
            border-radius: 24px;
            padding: 24px;
            box-shadow: 0 18px 50px rgba(15, 23, 42, 0.08);
        }
        .hero {
            display: flex;
            justify-content: space-between;
            gap: 16px;
            margin-bottom: 18px;
            padding-bottom: 18px;
            border-bottom: 1px solid #e7eef3;
        }
        .brand-block {
            display: flex;
            align-items: flex-start;
            gap: 14px;
        }
        .brand-logo {
            width: 142px;
            height: auto;
            object-fit: contain;
        }
        .brand-copy {
            padding-top: 4px;
        }
        .eyebrow {
            font-size: 12px;
            text-transform: uppercase;
            letter-spacing: .12em;
            color: var(--brand);
            font-weight: 700;
            margin-bottom: 8px;
        }
        h1 {
            margin: 0 0 6px;
            font-size: 30px;
        }
        .muted {
            color: var(--muted);
            font-size: 13px;
        }
        .pill {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 8px 12px;
            border-radius: 999px;
            background: var(--brand-soft);
            color: var(--brand);
            font-weight: 700;
            font-size: 12px;
        }
        .hero-side {
            min-width: 260px;
            display: grid;
            gap: 10px;
            justify-items: end;
        }
        .barcode-card {
            border: 1px solid var(--line);
            background: #fcfeff;
            border-radius: 18px;
            padding: 10px 12px;
            text-align: center;
            width: 100%;
        }
        .barcode-card img {
            width: 100%;
            max-width: 250px;
            height: auto;
        }
        .stats, .meta {
            display: grid;
            gap: 12px;
        }
        .meta {
            grid-template-columns: repeat(4, minmax(0, 1fr));
            margin-bottom: 18px;
        }
        .stats {
            grid-template-columns: repeat(5, minmax(0, 1fr));
            margin-bottom: 18px;
        }
        .card {
            border: 1px solid var(--line);
            border-radius: 18px;
            padding: 14px 16px;
            background: var(--soft);
        }
        .label {
            color: var(--muted);
            font-size: 11px;
            text-transform: uppercase;
            letter-spacing: .08em;
            margin-bottom: 6px;
        }
        .value {
            font-size: 15px;
            font-weight: 700;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            font-size: 12px;
        }
        th {
            text-align: left;
            padding: 10px 12px;
            color: var(--muted);
            font-size: 11px;
            text-transform: uppercase;
            letter-spacing: .08em;
            border-bottom: 1px solid var(--line);
            background: #f8fbfd;
        }
        td {
            padding: 10px 12px;
            border-bottom: 1px solid #e5edf3;
            vertical-align: top;
        }
        .section-title {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin: 18px 0 10px;
        }
        .section-title h2 {
            margin: 0;
            font-size: 16px;
        }
        .danger-wrap {
            margin-top: 18px;
            border: 1px solid #fecaca;
            background: #fff7f7;
            border-radius: 18px;
            padding: 14px;
        }
        .info-wrap {
            margin-top: 18px;
            border: 1px solid #bae6fd;
            background: #f0f9ff;
            border-radius: 18px;
            padding: 14px;
        }
        .danger-wrap h2 {
            color: var(--danger);
        }
        .info-wrap h2 {
            color: var(--brand);
        }
        .money {
            font-weight: 700;
        }
        .note-stack {
            display: grid;
            gap: 4px;
        }
        .empty-row {
            text-align: center;
            color: var(--muted);
            padding: 16px 12px;
        }
        .signatures {
            display: grid;
            grid-template-columns: repeat(3, minmax(0, 1fr));
            gap: 18px;
            margin-top: 24px;
        }
        .signature-card {
            border: 1px dashed var(--line);
            border-radius: 18px;
            padding: 16px 14px;
            text-align: center;
            min-height: 132px;
            background: #fcfeff;
        }
        .signature-role {
            font-size: 11px;
            text-transform: uppercase;
            letter-spacing: .1em;
            color: var(--muted);
        }
        .signature-space {
            height: 54px;
        }
        .signature-name {
            border-top: 1px solid var(--line);
            padding-top: 8px;
            font-weight: 700;
            font-size: 13px;
        }
        .doc-footer {
            margin-top: 18px;
            padding-top: 14px;
            border-top: 1px dashed var(--line);
            display: flex;
            justify-content: space-between;
            gap: 12px;
            color: var(--muted);
            font-size: 11px;
        }
        @media print {
            body { background: white; }
            .page { max-width: none; padding: 0; }
            .toolbar { display: none; }
            .sheet {
                box-shadow: none;
                border: none;
                border-radius: 0;
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
            <div class="hero">
                <div class="brand-block">
                    @if (!empty($manifest['logo_data_uri']))
                        <img src="{{ $manifest['logo_data_uri'] }}" alt="Qbus" class="brand-logo">
                    @endif
                    <div class="brand-copy">
                        <div class="eyebrow">Qbus Manifest</div>
                        <h1>{{ $manifest['title'] }}</h1>
                        <div class="muted">Dokumen resmi daftar penumpang aktif, bagasi terpasang, dan history cancel untuk satu keberangkatan.</div>
                    </div>
                </div>
                <div class="hero-side">
                    <div class="pill">{{ $manifest['departure_code'] }}</div>
                    @if (!empty($manifest['barcode_svg']))
                        <div class="barcode-card">
                            <img src="{{ $manifest['barcode_svg'] }}" alt="Barcode {{ $manifest['departure_code'] }}">
                        </div>
                    @endif
                </div>
            </div>

            <div class="meta">
                <div class="card">
                    <div class="label">Rute</div>
                    <div class="value">{{ $manifest['rute'] }}</div>
                </div>
                <div class="card">
                    <div class="label">Tanggal</div>
                    <div class="value">{{ $manifest['tanggal'] }}</div>
                </div>
                <div class="card">
                    <div class="label">Jam</div>
                    <div class="value">{{ $manifest['jam'] }}</div>
                </div>
                <div class="card">
                    <div class="label">Unit</div>
                    <div class="value">Unit {{ $manifest['unit'] }}</div>
                </div>
            </div>

            <div class="meta" style="grid-template-columns: repeat(2, minmax(0, 1fr));">
                <div class="card">
                    <div class="label">Driver</div>
                    <div class="value">{{ $manifest['driver_name'] ?: '-' }}</div>
                </div>
                <div class="card">
                    <div class="label">Nomor Polisi</div>
                    <div class="value">{{ $manifest['armada_nopol'] ?: '-' }}</div>
                </div>
            </div>

            <div class="stats">
                <div class="card"><div class="label">Total</div><div class="value">{{ $manifest['total'] }}</div></div>
                <div class="card"><div class="label">Aktif</div><div class="value">{{ $manifest['active'] }}</div></div>
                <div class="card"><div class="label">Cancel</div><div class="value">{{ $manifest['canceled'] }}</div></div>
                <div class="card"><div class="label">Lunas</div><div class="value">{{ $manifest['lunas'] }}</div></div>
                <div class="card"><div class="label">Belum Lunas</div><div class="value">{{ $manifest['belum_lunas'] }}</div></div>
            </div>

            <div class="section-title">
                <h2>Penumpang Aktif</h2>
                <div class="muted">{{ count($manifest['passengers']) }} data</div>
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
                            <td colspan="6" class="muted">Belum ada penumpang aktif.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>

            <div class="info-wrap">
                <div class="section-title" style="margin-top:0;">
                    <h2>Bagasi Terpasang</h2>
                    <div class="muted">{{ count($manifest['luggages'] ?? []) }} data &middot; Revenue Rp {{ number_format((float) ($manifest['luggage_revenue'] ?? 0), 0, ',', '.') }}</div>
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
                                <td>
                                    <strong>{{ $row['kode_resi'] ?: '-' }}</strong>
                                </td>
                                <td>
                                    <div class="note-stack">
                                        <strong>{{ $row['sender_name'] ?: '-' }} -> {{ $row['receiver_name'] ?: '-' }}</strong>
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
            </div>

            <div class="danger-wrap">
                <div class="section-title" style="margin-top:0;">
                    <h2>History Cancel</h2>
                    <div class="muted">{{ count($manifest['history_passengers'] ?? []) }} data</div>
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
            </div>

            <div class="signatures">
                <div class="signature-card">
                    <div class="signature-role">Dispatcher / Admin</div>
                    <div class="signature-space"></div>
                    <div class="signature-name">______________________</div>
                </div>
                <div class="signature-card">
                    <div class="signature-role">Driver</div>
                    <div class="signature-space"></div>
                    <div class="signature-name">{{ $manifest['driver_name'] ?: '______________________' }}</div>
                </div>
                <div class="signature-card">
                    <div class="signature-role">Checker / Verifikator</div>
                    <div class="signature-space"></div>
                    <div class="signature-name">______________________</div>
                </div>
            </div>

            <div class="doc-footer">
                <div>Qbus Booking &amp; Operations Workspace</div>
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
