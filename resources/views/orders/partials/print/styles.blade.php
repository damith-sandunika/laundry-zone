<style>
    * { margin: 0; padding: 0; box-sizing: border-box; }
    @page { margin: 0; size: 80mm auto; }
    body {
        font-family: {!! $fontFamily !!};
        font-size: {{ $fontSize }};
        width: {{ $paperWidth }};
        max-width: 100%;
        margin: 0 auto;
        padding: 4mm 3mm;
        color: #000;
        background: #fff;
    }
    .center { text-align: center; }
    .bold { font-weight: 600; }
    .uppercase { text-transform: uppercase; letter-spacing: 0.03em; font-size: 11px; }
    .divider { border-top: 1px dashed #64748b; margin: 8px 0; }
    .row { display: flex; justify-content: space-between; padding: 3px 0; gap: 4px; }
    .row span:first-child { flex: 1; text-align: left; word-break: break-word; }
    .row span:last-child { text-align: right; white-space: nowrap; }
    .total-row { font-weight: 700; font-size: 14px; border-top: 1.5px solid #0f172a; padding-top: 6px; margin-top: 6px; }
    .badge { display: inline-block; border: 1px solid #0f172a; padding: 4px 10px; margin-top: 6px; font-size: 11px; font-weight: 600; border-radius: 6px; }
    .item-name { font-weight: 600; font-size: 12px; display: block; word-break: break-word; }
    .item-details { font-size: 11px; color: #475569; padding-left: 2px; }
    @media print {
        body { width: {{ $paperWidth }}; padding: 2mm 3mm; margin: 0 auto; }
        .no-print { display: none; }
    }
</style>

