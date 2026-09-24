    <style>
        /* Mobile warning */
        .mobile-pos-warning {
            display: none;
        }

        @media (max-width: 768px) {
            .pos-wrapper {
                display: none !important;
            }

            .mobile-pos-warning {
                display: flex;
                flex-direction: column;
                align-items: center;
                justify-content: center;
                height: 60vh;
                text-align: center;
                padding: 20px;
            }
        }

        .custom-scrollbar::-webkit-scrollbar {
            width: 4px;
            height: 4px;
        }

        .custom-scrollbar::-webkit-scrollbar-track {
            background: transparent;
        }

        .custom-scrollbar::-webkit-scrollbar-thumb {
            background: #cbd5e1;
            border-radius: 10px;
        }

        .custom-scrollbar::-webkit-scrollbar-thumb:hover {
            background: #94a3b8;
        }

        .custom-scrollbar-hide::-webkit-scrollbar {
            display: none;
        }

        .custom-scrollbar-hide {
            -ms-overflow-style: none;
            scrollbar-width: none;
        }

        .hide-arrows::-webkit-outer-spin-button,
        .hide-arrows::-webkit-inner-spin-button {
            -webkit-appearance: none;
            margin: 0;
        }

        .hide-arrows {
            -moz-appearance: textfield;
        }
    </style>

    <div class="mobile-pos-warning">
        <svg class="w-16 h-16 text-slate-400 mb-4" fill="none" stroke="currentColor" stroke-width="1.5"
            viewBox="0 0 24 24">
            <rect x="2" y="4" width="20" height="16" rx="2" />
            <path stroke-linecap="round" d="M10 20v-2m4 2v-2" />
        </svg>
        <h2 class="text-xl font-black text-slate-800 mb-2 tracking-tight">Desktop View Required</h2>
        <p class="text-sm font-semibold text-slate-500">The POS System is designed for wider screens. Please use a
            tablet or desktop computer to process orders.</p>
    </div>
