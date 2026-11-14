    <style>
        /* ── Control Panel (hidden on print) ── */
        #ctrl-panel {
            position: fixed;
            top: 0; left: 0; right: 0;
            background: #1e293b;
            color: #f1f5f9;
            padding: 8px 12px;
            display: flex;
            align-items: center;
            gap: 10px;
            flex-wrap: wrap;
            font-family: system-ui, sans-serif;
            font-size: 12px;
            z-index: 9999;
            box-shadow: 0 2px 8px rgba(0,0,0,0.3);
        }
        #ctrl-panel label { font-weight: 700; color: #94a3b8; white-space: nowrap; }
        #ctrl-panel select, #ctrl-panel input[type=range] {
            background: #334155; color: #f1f5f9; border: 1px solid #475569;
            border-radius: 6px; padding: 3px 8px; font-size: 12px; cursor: pointer;
        }
        #ctrl-panel input[type=range] { width: 80px; padding: 4px 0; }
        #btn-print {
            margin-left: auto;
            background: #3b82f6; color: white; border: none;
            padding: 6px 18px; border-radius: 8px; font-size: 13px;
            font-weight: 800; cursor: pointer; letter-spacing: 0.03em;
            transition: background 0.15s;
        }
        #btn-print:hover { background: #2563eb; }
        #scale-val { font-weight: 800; color: #60a5fa; min-width: 36px; }

        /* ── Label Body ── */
        body {
            font-family: 'Inter', system-ui, -apple-system, sans-serif;
            font-weight: 700;
            margin: 0;
            padding: 10px;
            color: #000;
            background: #f1f5f9;
        }

        #label-wrapper {
            background: #fff;
            margin: 0 auto;
            padding: 8px 10px;
            box-sizing: border-box;
            border: 2px solid #000;
        }

        .center  { text-align: center; }
        .bold    { font-weight: 800; }
        .right   { text-align: right; }

        .divider {
            border: none;
            border-top: 1.5px dashed #000;
            margin: 6px 0;
        }
        .divider-solid {
            border: none;
            border-top: 2px solid #000;
            margin: 6px 0;
        }

        /* ── Sections ── */
        .brand-name {
            font-size: 1.5em;
            font-weight: 800;
            letter-spacing: -0.02em;
        }
        .brand-sub {
            font-size: 0.85em;
            font-weight: 700;
            margin-top: 1px;
        }

        .inv-label {
            font-size: 0.72em;
            text-transform: uppercase;
            letter-spacing: 2px;
            font-weight: 800;
            margin-bottom: 2px;
        }
        .inv-number {
            font-size: 2.4em;
            font-weight: 900;
            letter-spacing: 1px;
            line-height: 1.1;
        }
        .inv-date {
            font-size: 0.78em;
            font-weight: 700;
            margin-top: 2px;
        }

        .row {
            display: flex;
            justify-content: space-between;
            padding: 4px 0;
            gap: 4px;
            font-size: 0.92em;
            font-weight: 700;
        }
        .row span:first-child {
            flex: 1;
            text-align: left;
            word-break: break-word;
        }
        .row span:last-child {
            text-align: right;
            white-space: nowrap;
        }

        .section-label {
            font-size: 0.72em;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 1.5px;
            margin-bottom: 3px;
        }

        .balance-row {
            display: flex;
            justify-content: space-between;
            font-size: 1.15em;
            font-weight: 900;
            border-top: 2px solid #000;
            border-bottom: 2px solid #000;
            padding: 3px 0;
            margin-top: 3px;
        }

        .instruction {
            border: 2px solid #000;
            text-align: center;
            padding: 6px 4px;
            margin-top: 6px;
        }
        .instruction-main {
            font-size: 1.1em;
            font-weight: 900;
            text-transform: uppercase;
            letter-spacing: 2px;
        }
        .instruction-sub {
            font-size: 0.78em;
            margin-top: 2px;
            font-weight: 600;
        }

        .address-text {
            font-size: 0.92em;
            font-weight: 700;
            line-height: 1.4;
        }

        .footer-text {
            text-align: center;
            font-size: 0.78em;
            font-weight: 700;
        }

        @media print {
            #ctrl-panel { display: none !important; }
            body { margin: 0; padding: 0; background: #fff; }
            #label-wrapper { border: 2px solid #000; box-shadow: none; }
        }
    </style>
