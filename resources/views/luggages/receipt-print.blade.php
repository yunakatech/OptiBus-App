<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $document['title'] ?? 'Resi Bagasi' }} {{ $document['kode_resi'] ?? '' }}</title>
    <style>
        :root {
            color-scheme: light;
            --ink: #000000;
            --muted: #3f3f46;
            --line: #1f2937;
            --paper: #ffffff;
        }
        * { box-sizing: border-box; }
        body {
            margin: 0;
            background: #f3f4f6;
            color: var(--ink);
            font-family: "Courier New", "IBM Plex Mono", monospace;
        }
        .page {
            width: 100%;
            max-width: 340px;
            margin: 0 auto;
            padding: 10px 8px 24px;
        }
        .toolbar {
            display: flex;
            justify-content: center;
            margin-bottom: 10px;
        }
        .print-btn {
            border: 1px solid #111827;
            background: #ffffff;
            color: #111827;
            padding: 8px 14px;
            border-radius: 999px;
            font-size: 11px;
            font-weight: 700;
            letter-spacing: .04em;
            cursor: pointer;
        }
        .sheet {
            background: var(--paper);
            border: 1px solid #111827;
            padding: 12px 10px 14px;
        }
        .receipt-head {
            text-align: center;
            padding-bottom: 12px;
            border-bottom: 1px dashed #111827;
        }
        .logo-shell {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            min-height: 48px;
            padding: 4px 8px 6px;
            margin-bottom: 8px;
        }
        .logo {
            width: 118px;
            height: auto;
            display: block;
            object-fit: contain;
        }
        .eyebrow {
            margin: 0;
            font-size: 10px;
            font-weight: 700;
            letter-spacing: .18em;
            text-transform: uppercase;
        }
        h1 {
            margin: 6px 0 0;
            font-size: 18px;
            line-height: 1.2;
            letter-spacing: .02em;
        }
        .subtitle {
            margin-top: 4px;
            color: var(--muted);
            font-size: 10px;
            line-height: 1.45;
        }
        .receipt-code {
            margin: 10px auto 0;
            display: inline-block;
            padding: 5px 8px;
            border: 1px solid #111827;
            font-size: 11px;
            font-weight: 700;
            letter-spacing: .05em;
            word-break: break-all;
        }
        .barcode-card {
            margin-top: 10px;
            padding-top: 8px;
            border-top: 1px dashed #111827;
        }
        .barcode-card img {
            width: 100%;
            max-width: 220px;
            height: auto;
            display: block;
            margin: 0 auto;
        }
        .barcode-note {
            margin-top: 2px;
            font-size: 9px;
            letter-spacing: .12em;
            text-transform: uppercase;
            color: var(--muted);
        }
        .section {
            padding-top: 10px;
            margin-top: 10px;
            border-top: 1px dashed #111827;
        }
        .section-title {
            margin: 0 0 6px;
            font-size: 10px;
            font-weight: 700;
            letter-spacing: .14em;
            text-transform: uppercase;
        }
        .kv {
            display: grid;
            gap: 6px;
        }
        .kv-row {
            display: grid;
            grid-template-columns: 84px minmax(0, 1fr);
            gap: 8px;
            align-items: start;
        }
        .kv-label {
            font-size: 10px;
            color: var(--muted);
        }
        .kv-value {
            font-size: 11px;
            font-weight: 700;
            line-height: 1.45;
            word-break: break-word;
        }
        .party-name {
            font-size: 12px;
            font-weight: 700;
            line-height: 1.4;
        }
        .party-meta {
            margin-top: 3px;
            font-size: 10px;
            line-height: 1.5;
            color: var(--muted);
            white-space: pre-line;
        }
        .summary {
            display: grid;
            gap: 8px;
        }
        .summary-card {
            border: 1px solid #111827;
            padding: 8px 9px;
        }
        .summary-label {
            font-size: 9px;
            letter-spacing: .1em;
            text-transform: uppercase;
            color: var(--muted);
        }
        .summary-value {
            margin-top: 3px;
            font-size: 11px;
            font-weight: 700;
            line-height: 1.45;
        }
        .summary-money {
            margin-top: 4px;
            font-size: 18px;
            font-weight: 700;
            line-height: 1.2;
        }
        .notes {
            padding: 8px 0 0;
            font-size: 10px;
            line-height: 1.6;
            color: var(--muted);
        }
        .cutline {
            margin-top: 12px;
            padding-top: 8px;
            border-top: 1px dashed #111827;
            text-align: center;
            font-size: 9px;
            letter-spacing: .16em;
            text-transform: uppercase;
            color: var(--muted);
        }
        .footer {
            padding-top: 10px;
            text-align: center;
            font-size: 9px;
            line-height: 1.55;
            color: var(--muted);
        }
        .footer strong {
            color: var(--ink);
            font-size: 10px;
            letter-spacing: .04em;
        }
        @page {
            size: 72mm auto;
            margin: 3mm;
        }
        @media print {
            body {
                background: #ffffff;
            }
            .page {
                max-width: none;
                padding: 0;
            }
            .toolbar {
                display: none;
            }
            .sheet {
                border: none;
                padding: 0;
            }
            .logo {
                filter: grayscale(1) contrast(1.15);
            }
        }
    </style>
</head>
<body>
    <div class="page">
        @if (($exportMode ?? 'screen') !== 'pdf')
            <div class="toolbar">
                <button class="print-btn" onclick="window.print()">Print Resi</button>
            </div>
        @endif

        <section class="sheet">
            <header class="receipt-head">
                @if (!empty($document['logo_data_uri']))
                    <div class="logo-shell">
                        <img src="{{ $document['logo_data_uri'] }}" alt="Qbus" class="logo">
                    </div>
                @endif
                <p class="eyebrow">Qbus Baggage</p>
                <h1>{{ $document['title'] }}</h1>
                <div class="subtitle">Resi thermal operasional pickup dan serah terima bagasi.</div>
                <div class="receipt-code">{{ $document['kode_resi'] ?: '-' }}</div>
                @if (!empty($document['barcode_svg']))
                    <div class="barcode-card">
                        <img src="{{ $document['barcode_svg'] }}" alt="Barcode {{ $document['kode_resi'] }}">
                        <div class="barcode-note">Scan Resi</div>
                    </div>
                @endif
            </header>

            <section class="section">
                <p class="section-title">Detail Pengiriman</p>
                <div class="kv">
                    <div class="kv-row">
                        <div class="kv-label">Layanan</div>
                        <div class="kv-value">{{ $document['service_name'] ?: '-' }}</div>
                    </div>
                    <div class="kv-row">
                        <div class="kv-label">Rute</div>
                        <div class="kv-value">{{ $document['rute'] ?: '-' }}</div>
                    </div>
                    <div class="kv-row">
                        <div class="kv-label">Tanggal</div>
                        <div class="kv-value">{{ $document['tanggal'] ?: '-' }}</div>
                    </div>
                    <div class="kv-row">
                        <div class="kv-label">Armada</div>
                        <div class="kv-value">{{ $document['unit_nopol'] ?: '-' }}</div>
                    </div>
                </div>
            </section>

            <section class="section">
                <p class="section-title">Pengirim</p>
                <div class="party-name">{{ $document['sender_name'] ?: '-' }}</div>
                <div class="party-meta">{{ $document['sender_phone'] ?: '-' }}{{ !empty($document['sender_address']) ? "\n".$document['sender_address'] : '' }}</div>
            </section>

            <section class="section">
                <p class="section-title">Penerima</p>
                <div class="party-name">{{ $document['receiver_name'] ?: '-' }}</div>
                <div class="party-meta">{{ $document['receiver_phone'] ?: '-' }}{{ !empty($document['receiver_address']) ? "\n".$document['receiver_address'] : '' }}</div>
            </section>

            <section class="section">
                <p class="section-title">Ringkasan</p>
                <div class="summary">
                    <div class="summary-card">
                        <div class="summary-label">Jumlah Barang</div>
                        <div class="summary-value">{{ $document['quantity'] ?? 0 }} barang</div>
                    </div>
                    <div class="summary-card">
                        <div class="summary-label">Pembayaran</div>
                        <div class="summary-value">{{ $document['payment_status'] ?: '-' }}</div>
                    </div>
                    <div class="summary-card">
                        <div class="summary-label">Biaya Bagasi</div>
                        <div class="summary-money">Rp {{ number_format((float) ($document['price'] ?? 0), 0, ',', '.') }}</div>
                    </div>
                </div>
            </section>

            @if (!empty($document['notes']))
                <section class="section">
                    <p class="section-title">Catatan</p>
                    <div class="notes">{{ $document['notes'] }}</div>
                </section>
            @endif

            <div class="cutline">Simpan Resi Ini</div>

            <footer class="footer">
                Arsip operasional bagasi.<br>
                Resi: <strong>{{ $document['kode_resi'] ?: '-' }}</strong>
            </footer>
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
