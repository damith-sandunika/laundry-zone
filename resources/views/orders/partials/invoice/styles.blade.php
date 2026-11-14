<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">
<style>
    * { box-sizing: border-box; margin: 0; padding: 0; }
    @page { size: A4; margin: 0; }
    body { 
        font-family: {!! $fontFamily !!}; 
        font-size: {{ $fontSize }};
        background: #e2e8f0; 
        -webkit-print-color-adjust: exact !important; 
        print-color-adjust: exact !important; 
        padding: 40px; 
        display: flex;
        justify-content: center;
    }
    .invoice-box { 
        background: white; 
        width: 210mm; 
        min-height: 297mm; 
        padding: 50px; 
        box-shadow: 0 10px 30px rgba(0,0,0,0.1); 
        border-radius: 8px;
        position: relative;
        overflow: hidden;
    }
    .header-shape {
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        height: 12px;
        background: {{ $primaryColor }};
    }
    .header { display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 40px; margin-top: 20px; }
    .logo-section h1 { font-size: {{ $companyNameSize }}; font-weight: 900; color: {{ $primaryColor }}; margin-bottom: 4px; letter-spacing: -0.03em; }
    .logo-section p { font-size: 13px; color: #64748b; font-weight: 500; line-height: 1.5; }
    .invoice-title { text-align: right; }
    .invoice-title h2 { font-size: {{ $invoiceTitleSize }}; font-weight: 900; color: #cbd5e1; text-transform: uppercase; letter-spacing: 0.1em; margin-bottom: 8px; }
    .invoice-title p { font-size: 14px; font-weight: 700; color: {{ $accentColor }}; }
    .info-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 40px; margin-bottom: 40px; }
    .info-box { background: #f8fafc; padding: 20px; border-radius: 12px; border: 1px solid #f1f5f9; }
    .info-box h3 { font-size: 11px; font-weight: 800; text-transform: uppercase; letter-spacing: 0.1em; color: #94a3b8; margin-bottom: 12px; }
    .info-box p { font-size: 14px; font-weight: 700; color: #0f172a; margin-bottom: 4px; }
    .info-box span { font-size: 13px; color: #64748b; font-weight: 500; display: block; line-height: 1.5; }
    .items-table { width: 100%; border-collapse: collapse; margin-bottom: 30px; }
    .items-table th { background: {{ $primaryColor }}; color: white; font-size: 12px; font-weight: 700; text-transform: uppercase; padding: 14px 16px; text-align: left; letter-spacing: 0.05em; }
    .items-table th.right { text-align: right; }
    .items-table th.center { text-align: center; }
    .items-table th:first-child { border-radius: 8px 0 0 8px; }
    .items-table th:last-child { border-radius: 0 8px 8px 0; }
    .items-table td { padding: 16px; border-bottom: 1px solid #f1f5f9; font-size: 14px; color: #1e293b; font-weight: 600; }
    .items-table td.right { text-align: right; }
    .items-table td.center { text-align: center; }
    .items-table tr:last-child td { border-bottom: none; }
    .totals-section { display: flex; justify-content: flex-end; }
    .totals-box { width: 320px; }
    .total-row { display: flex; justify-content: space-between; padding: 8px 0; font-size: 14px; color: #475569; font-weight: 600; }
    .total-row.grand-total { border-top: 2px solid #e2e8f0; margin-top: 8px; padding-top: 12px; font-size: 18px; color: #0f172a; font-weight: 900; }
    .total-row.paid { color: #10b981; }
    .total-row.balance { background: #eff6ff; padding: 12px; border-radius: 8px; color: #2563eb; font-weight: 800; margin-top: 8px; }
    .footer { position: absolute; bottom: 40px; left: 50px; right: 50px; display: flex; flex-direction: column; align-items: center; border-top: 1.5px solid #f1f5f9; padding-top: 20px; gap: 8px; text-align: center; }
    .footer p { font-size: 13px; color: #64748b; font-weight: 600; }
    .badge { background: #10b981; color: white; padding: 6px 14px; border-radius: 99px; font-size: 12px; font-weight: 800; text-transform: uppercase; letter-spacing: 0.05em; display: inline-block; }
    .badge.pending { background: #f8fafc; color: #94a3b8; border: 1px solid #e2e8f0; font-size: 10px; }
    .pay-btn { display: inline-block; background: {{ $accentColor }}; color: white; text-decoration: none; padding: 14px 32px; border-radius: 12px; font-weight: 900; font-size: 16px; box-shadow: 0 4px 15px rgba(37, 99, 235, 0.3); margin: 5px 0; }
    .print-link { display: none; font-size: 11px; font-weight: 700; color: #0f172a; margin: 5px 0; }
    @media screen {
        .print-only { display: none !important; }
    }
    @media print {
        body { background: white; padding: 0; display: block; }
        .invoice-box { box-shadow: none; width: 100%; min-height: 100vh; padding: 15mm; border-radius: 0; margin: 0; }
        .no-print { display: none !important; }
        .print-link { display: none !important; }
    }
</style>

