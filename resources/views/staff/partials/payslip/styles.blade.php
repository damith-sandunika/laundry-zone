    <style>
        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }
        body {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif;
            color: #1e293b;
            background-color: #f8fafc;
            padding: 24px;
            font-size: 13px;
            line-height: 1.5;
        }
        .payslip-card {
            max-width: 850px;
            margin: 0 auto;
            background: #ffffff;
            border: 1px solid #e2e8f0;
            border-radius: 16px;
            box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.05);
            padding: 36px;
            position: relative;
        }
        .no-print {
            max-width: 850px;
            margin: 0 auto 16px;
            display: flex;
            justify-content: flex-end;
            gap: 10px;
        }
        .btn-print {
            padding: 10px 20px;
            background: #2563eb;
            color: #ffffff;
            border: none;
            border-radius: 8px;
            font-weight: 700;
            font-size: 13px;
            cursor: pointer;
            box-shadow: 0 4px 12px rgba(37, 99, 235, 0.2);
            transition: all 0.2s;
        }
        .btn-print:hover {
            background: #1d4ed8;
        }
        .btn-close {
            padding: 10px 20px;
            background: #f1f5f9;
            color: #475569;
            border: 1px solid #cbd5e1;
            border-radius: 8px;
            font-weight: 700;
            font-size: 13px;
            cursor: pointer;
        }
        .header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            border-bottom: 2px solid #e2e8f0;
            padding-bottom: 20px;
            margin-bottom: 24px;
        }
        .company-title {
            font-size: 24px;
            font-weight: 900;
            color: #0f172a;
            letter-spacing: -0.5px;
        }
        .company-sub {
            font-size: 12px;
            color: #64748b;
            font-weight: 600;
            margin-top: 2px;
        }
        .payslip-badge {
            text-align: right;
        }
        .badge-title {
            font-size: 20px;
            font-weight: 800;
            color: #2563eb;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        .badge-period {
            font-size: 12px;
            font-weight: 700;
            color: #475569;
            margin-top: 2px;
        }
        .badge-ref {
            font-size: 11px;
            color: #94a3b8;
            font-family: monospace;
            margin-top: 4px;
        }
        
        .grid-2 {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
            margin-bottom: 24px;
        }
        .info-box {
            background: #f8fafc;
            border: 1px solid #f1f5f9;
            border-radius: 12px;
            padding: 16px;
        }
        .info-box-title {
            font-size: 11px;
            font-weight: 800;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            color: #64748b;
            margin-bottom: 10px;
            border-bottom: 1px dashed #cbd5e1;
            padding-bottom: 6px;
        }
        .info-row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 6px;
            font-size: 12px;
        }
        .info-label {
            color: #64748b;
            font-weight: 600;
        }
        .info-value {
            color: #0f172a;
            font-weight: 700;
        }

        /* Payment Audit Status Box */
        .payment-status-box {
            border-radius: 12px;
            padding: 16px 20px;
            margin-bottom: 24px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .payment-status-box.paid {
            background-color: #f0fdf4;
            border: 1px solid #bbf7d0;
            color: #166534;
        }
        .payment-status-box.pending {
            background-color: #fffbeb;
            border: 1px solid #fef3c7;
            color: #92400e;
        }
        .status-badge {
            display: inline-block;
            padding: 4px 10px;
            border-radius: 6px;
            font-weight: 800;
            font-size: 11px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        .status-badge.paid {
            background-color: #166534;
            color: #ffffff;
        }
        .status-badge.pending {
            background-color: #d97706;
            color: #ffffff;
        }

        .statement-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 24px;
            border-radius: 12px;
            overflow: hidden;
            border: 1px solid #e2e8f0;
        }
        .statement-table th {
            background-color: #f1f5f9;
            color: #334155;
            font-size: 11px;
            font-weight: 800;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            padding: 12px 16px;
            text-align: left;
            border-bottom: 1px solid #e2e8f0;
        }
        .statement-table td {
            padding: 12px 16px;
            border-bottom: 1px solid #f1f5f9;
            font-size: 13px;
        }
        .text-right {
            text-align: right;
        }
        .font-mono {
            font-family: 'SFMono-Regular', Consolas, 'Liberation Mono', Menlo, monospace;
            font-weight: 700;
        }
        .text-green { color: #16a34a; }
        .text-red { color: #dc2626; }
        .text-amber { color: #d97706; }
        .text-indigo { color: #4f46e5; }
        .text-purple { color: #9333ea; }
        
        .net-salary-box {
            background: linear-gradient(135deg, #1e3a8a 0%, #1e40af 100%);
            color: #ffffff;
            border-radius: 12px;
            padding: 20px 24px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 30px;
            box-shadow: 0 10px 20px -5px rgba(30, 58, 138, 0.3);
        }
        .net-label {
            font-size: 14px;
            font-weight: 800;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            color: #93c5fd;
        }
        .net-sub {
            font-size: 11px;
            color: #cbd5e1;
            margin-top: 2px;
        }
        .net-amount {
            font-size: 28px;
            font-weight: 900;
            font-family: 'SFMono-Regular', Consolas, monospace;
            letter-spacing: -0.5px;
        }

        /* Worked Days Section */
        .worked-days-box {
            margin-bottom: 30px;
        }
        .worked-days-title {
            font-size: 12px;
            font-weight: 800;
            color: #334155;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin-bottom: 10px;
        }
        .days-table {
            width: 100%;
            border-collapse: collapse;
            border: 1px solid #e2e8f0;
            border-radius: 8px;
            overflow: hidden;
            font-size: 11px;
        }
        .days-table th {
            background: #f8fafc;
            padding: 8px 12px;
            text-align: left;
            color: #64748b;
            font-weight: 700;
            border-bottom: 1px solid #e2e8f0;
        }
        .days-table td {
            padding: 8px 12px;
            border-bottom: 1px solid #f1f5f9;
        }

        .signatures {
            margin-top: 40px;
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 40px;
            padding-top: 20px;
            border-top: 1px solid #f1f5f9;
        }
        .sig-block {
            text-align: center;
        }
        .sig-line {
            border-top: 1px solid #cbd5e1;
            margin: 40px auto 8px;
            width: 70%;
        }
        .sig-title {
            font-size: 11px;
            font-weight: 700;
            color: #64748b;
            text-transform: uppercase;
        }
        .footer-watermark {
            margin-top: 30px;
            text-align: center;
            font-size: 10px;
            color: #94a3b8;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        @media print {
            body {
                background: #ffffff;
                padding: 0;
            }
            .payslip-card {
                border: none;
                box-shadow: none;
                padding: 0;
            }
            .no-print {
                display: none !important;
            }
        }
    </style>
