<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Tiket {{ $ticket['ticket_code'] ?? '' }}</title>
    <style>
        :root {
            --ink: #0f172a;
            --muted: #64748b;
            --line: #d7e3ea;
            --soft: #f8fbfd;
            --brand: #075985;
            --brand-soft: #e0f2fe;
        }
        * { box-sizing: border-box; }
        body {
            margin: 0;
            background: #edf5f9;
            color: var(--ink);
            font-family: Arial, Helvetica, sans-serif;
        }
        .page {
            min-height: 100vh;
            display: grid;
            place-items: center;
            padding: 28px;
        }
        .toolbar {
            position: fixed;
            top: 18px;
            right: 18px;
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
        .ticket {
            width: min(760px, 100%);
            background: white;
            border: 1px solid var(--line);
            border-radius: 26px;
            overflow: hidden;
            box-shadow: 0 24px 60px rgba(15, 23, 42, 0.08);
        }
        .hero {
            padding: 22px 24px;
            background:
                radial-gradient(circle at top left, rgba(14, 165, 233, 0.18), transparent 45%),
                linear-gradient(135deg, #f8fcff 0%, #eef8fd 100%);
            border-bottom: 1px solid var(--line);
        }
        .brand-row {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 16px;
            margin-bottom: 12px;
        }
        .brand-logo {
            width: 148px;
            height: auto;
            object-fit: contain;
        }
        .brand-copy {
            flex: 1;
        }
        .eyebrow {
            font-size: 11px;
            text-transform: uppercase;
            letter-spacing: .14em;
            color: var(--brand);
            font-weight: 700;
            margin-bottom: 8px;
        }
        h1 {
            margin: 0 0 6px;
            font-size: 28px;
        }
        .sub {
            color: var(--muted);
            font-size: 13px;
        }
        .codes {
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
            margin-top: 14px;
        }
        .barcode-card {
            margin-top: 14px;
            border: 1px solid var(--line);
            background: rgba(255, 255, 255, 0.8);
            border-radius: 18px;
            padding: 10px 12px;
            max-width: 320px;
        }
        .barcode-card img {
            display: block;
            width: 100%;
            height: auto;
        }
        .pill {
            display: inline-flex;
            align-items: center;
            padding: 8px 12px;
            border-radius: 999px;
            background: var(--brand-soft);
            color: var(--brand);
            font-size: 12px;
            font-weight: 700;
        }
        .body {
            padding: 22px 24px 24px;
        }
        .grid {
            display: grid;
            gap: 14px;
            grid-template-columns: repeat(2, minmax(0, 1fr));
        }
        .card {
            background: var(--soft);
            border: 1px solid var(--line);
            border-radius: 18px;
            padding: 14px 16px;
        }
        .label {
            font-size: 11px;
            text-transform: uppercase;
            letter-spacing: .08em;
            color: var(--muted);
            margin-bottom: 6px;
        }
        .value {
            font-size: 15px;
            font-weight: 700;
        }
        .muted {
            color: var(--muted);
            font-size: 12px;
        }
        .footer {
            margin-top: 16px;
            padding-top: 16px;
            border-top: 1px dashed var(--line);
            display: flex;
            justify-content: space-between;
            gap: 16px;
            align-items: center;
        }
        .amount {
            font-size: 24px;
            font-weight: 800;
        }
        .signatures {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 18px;
            margin-top: 18px;
        }
        .signature-card {
            border: 1px dashed var(--line);
            border-radius: 18px;
            padding: 14px;
            min-height: 118px;
            background: #fcfeff;
            text-align: center;
        }
        .signature-role {
            font-size: 11px;
            text-transform: uppercase;
            letter-spacing: .1em;
            color: var(--muted);
        }
        .signature-space {
            height: 48px;
        }
        .signature-name {
            border-top: 1px solid var(--line);
            padding-top: 8px;
            font-weight: 700;
            font-size: 13px;
        }
        .doc-footer {
            margin-top: 16px;
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
            .page { padding: 0; min-height: auto; display: block; }
            .toolbar { display: none; }
            .ticket { box-shadow: none; border-radius: 0; width: 100%; border: none; }
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
            <div class="hero">
                <div class="brand-row">
                    <div class="brand-copy">
                        <div class="eyebrow">OptiBus Ticket</div>
                        <h1>Tiket Keberangkatan</h1>
                        <div class="sub">Dokumen tiket perjalanan penumpang untuk keberangkatan terjadwal.</div>
                    </div>
                    @if (!empty($ticket['logo_data_uri']))
                        <img src="{{ $ticket['logo_data_uri'] }}" alt="OptiBus" class="brand-logo">
                    @endif
                </div>
                <div class="codes">
                    <div class="pill">{{ $ticket['ticket_code'] }}</div>
                    <div class="pill">{{ $ticket['departure_code'] }}</div>
                </div>
                @if (!empty($ticket['barcode_svg']))
                    <div class="barcode-card">
                        <img src="{{ $ticket['barcode_svg'] }}" alt="Barcode {{ $ticket['ticket_code'] }}">
                    </div>
                @endif
            </div>

            <div class="body">
                <div class="grid">
                    <div class="card">
                        <div class="label">Penumpang</div>
                        <div class="value">{{ $ticket['name'] ?: '-' }}</div>
                        <div class="muted">{{ $ticket['phone'] ?: '-' }}</div>
                    </div>
                    <div class="card">
                        <div class="label">Seat</div>
                        <div class="value">{{ $ticket['seat'] ?: '-' }}</div>
                        <div class="muted">{{ $ticket['status'] ?: '-' }}</div>
                    </div>
                    <div class="card">
                        <div class="label">Rute</div>
                        <div class="value">{{ $ticket['rute'] ?: '-' }}</div>
                        <div class="muted">{{ $ticket['segment_name'] ?: '-' }}</div>
                    </div>
                    <div class="card">
                        <div class="label">Tanggal & Jam</div>
                        <div class="value">{{ $ticket['tanggal'] ?: '-' }} &bull; {{ $ticket['jam'] ?: '-' }}</div>
                        <div class="muted">Unit {{ $ticket['unit'] ?? 1 }}</div>
                    </div>
                    <div class="card">
                        <div class="label">Driver</div>
                        <div class="value">{{ $ticket['driver_name'] ?: '-' }}</div>
                        <div class="muted">Nopol {{ $ticket['armada_nopol'] ?: '-' }}</div>
                    </div>
                    <div class="card">
                        <div class="label">Jemput & Bayar</div>
                        <div class="value">{{ $ticket['pickup_point'] ?: '-' }}</div>
                        <div class="muted">{{ $ticket['pembayaran'] ?: '-' }}</div>
                    </div>
                </div>

                <div class="footer">
                    <div>
                        <div class="label">Catatan</div>
                        <div class="muted">Tunjukkan tiket ini saat keberangkatan. Simpan PDF jika diperlukan untuk arsip.</div>
                    </div>
                    <div style="text-align:right;">
                        <div class="label">Total</div>
                        <div class="amount">Rp {{ number_format(max(((float) ($ticket['price'] ?? 0)) - ((float) ($ticket['discount'] ?? 0)), 0), 0, ',', '.') }}</div>
                    </div>
                </div>

                <div class="signatures">
                    <div class="signature-card">
                        <div class="signature-role">Petugas OptiBus</div>
                        <div class="signature-space"></div>
                        <div class="signature-name">______________________</div>
                    </div>
                    <div class="signature-card">
                        <div class="signature-role">Penumpang</div>
                        <div class="signature-space"></div>
                        <div class="signature-name">{{ $ticket['name'] ?: '______________________' }}</div>
                    </div>
                </div>

                <div class="doc-footer">
                    <div>OptiBus Booking &amp; Operations Workspace</div>
                    <div>Generated {{ now()->format('d/m/Y H:i') }}</div>
                </div>
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
