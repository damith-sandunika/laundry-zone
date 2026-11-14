        <style>
            .ord-btn-wrap {
                position: relative;
                display: inline-flex;
                align-items: center;
            }

            .ord-btn {
                display: inline-flex;
                align-items: center;
                justify-content: center;
                width: 36px;
                height: 36px;
                border-radius: 10px;
                border: 1.5px solid;
                background: white;
                cursor: pointer;
                font-size: 16px;
                line-height: 1;
                transition: all 0.15s;
                box-shadow: 0 1px 3px rgba(0, 0, 0, 0.06);
                text-decoration: none;
                flex-shrink: 0;
            }

            .ord-btn:hover {
                transform: translateY(-2px);
                box-shadow: 0 4px 12px rgba(0, 0, 0, 0.12);
            }

            .ord-btn-label {
                position: absolute;
                bottom: calc(100% + 7px);
                left: 50%;
                transform: translateX(-50%);
                background: #0f172a;
                color: white;
                font-size: 11px;
                font-weight: 700;
                padding: 4px 8px;
                border-radius: 6px;
                white-space: nowrap;
                pointer-events: none;
                opacity: 0;
                transition: opacity 0.15s;
                z-index: 50;
            }

            .ord-btn-label::after {
                content: '';
                position: absolute;
                top: 100%;
                left: 50%;
                transform: translateX(-50%);
                border: 4px solid transparent;
                border-top-color: #0f172a;
            }

            .ord-btn-wrap:hover .ord-btn-label {
                opacity: 1;
            }

            /* Pay Now - green gradient button (larger, pill) */
            .ord-btn--pay-full {
                display: inline-flex;
                align-items: center;
                justify-content: center;
                width: 36px;
                height: 36px;
                border-radius: 10px;
                border: none;
                background: linear-gradient(135deg, #10b981, #059669);
                cursor: pointer;
                font-size: 16px;
                transition: all 0.15s;
                box-shadow: 0 4px 10px rgba(16, 185, 129, 0.35);
            }

            .ord-btn--pay-full:hover {
                transform: translateY(-2px);
                box-shadow: 0 6px 16px rgba(16, 185, 129, 0.45);
            }

            /* Individual color accents */
            .ord-btn--edit {
                color: #1d4ed8;
                border-color: #bfdbfe;
            }

            .ord-btn--edit:hover {
                background: #eff6ff;
                border-color: #93c5fd;
            }

            .ord-btn--pdf {
                color: #dc2626;
                border-color: #fecaca;
            }

            .ord-btn--pdf:hover {
                background: #fef2f2;
                border-color: #fca5a5;
            }

            .ord-btn--print {
                color: #334155;
                border-color: #e2e8f0;
            }

            .ord-btn--print:hover {
                background: #f8fafc;
                border-color: #cbd5e1;
            }

            .ord-btn--chit {
                color: #7c3aed;
                border-color: #ddd6fe;
            }

            .ord-btn--chit:hover {
                background: #f5f3ff;
                border-color: #c4b5fd;
            }

            .ord-btn--label {
                color: #0f766e;
                border-color: #99f6e4;
            }

            .ord-btn--label:hover {
                background: #f0fdfa;
                border-color: #5eead4;
            }
        </style>
