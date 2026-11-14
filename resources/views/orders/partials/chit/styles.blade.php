<style>
    #ctrl-panel { position: fixed; top: 0; left: 0; right: 0; background: #1e293b; color: #f1f5f9; padding: 8px 12px; display: flex; align-items: center; gap: 10px; flex-wrap: wrap; font-family: system-ui, sans-serif; font-size: 12px; z-index: 9999; box-shadow: 0 2px 8px rgba(0, 0, 0, 0.3); }
    #ctrl-panel label { font-weight: 700; color: #94a3b8; white-space: nowrap; }
    #ctrl-panel select, #ctrl-panel input[type=range] { background: #334155; color: #f1f5f9; border: 1px solid #475569; border-radius: 6px; padding: 3px 8px; font-size: 12px; cursor: pointer; }
    #ctrl-panel input[type=range] { width: 80px; padding: 4px 0; }
    #btn-print { margin-left: auto; background: #3b82f6; color: #fff; border: none; border-radius: 6px; padding: 5px 14px; font-size: 12px; font-weight: 700; cursor: pointer; }
    #btn-print:hover { background: #2563eb; }
    * { margin: 0; padding: 0; box-sizing: border-box; }
    body { font-family: {!! $fontFamily !!}; font-size: {{ $fontSize }}; color: #000; background: #f8fafc; display: flex; justify-content: center; }
    #chit-wrapper { background: #fff; padding: 10px 10px 20px 10px; width: {{ $paperWidth }}; box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1); }
    .header { text-align: center; border-bottom: 1px solid #000; padding-bottom: 5px; margin-bottom: 10px; }
    .text-bold { font-weight: 900; font-size: {{ $orderNoSize }}; margin: 3px 0; }
    .text-small { font-weight: 900; font-size: 0.9em; margin: 3px 0; text-align: center; }
    .text-center { text-align: center; }
    .date { font-weight: 900; font-size: 0.9em; margin: 5px 0 10px; text-align: center; text-transform: uppercase; }
    .items { margin-top: 10px; margin-bottom: 15px; }
    .item-row { display: flex; justify-content: space-between; font-weight: bold; font-size: 0.72em; margin-bottom: 5px; padding-bottom: 2px; }
    .total-row { display: flex; justify-content: space-between; font-weight: bold; font-size: 0.78em; margin-top: 5px; border-bottom: 1px solid #000; padding: 5px 0; margin-bottom: 15px; }
    .process-list { margin-top: 15px; font-size: 0.78em; font-weight: bold; }
    .process-list div { margin-bottom: 15px; }
    .special-note { margin-top: 15px; font-size: 0.78em; font-weight: bold; margin-bottom: 15px; }
    .note-line { border-bottom: 1px dotted #000; height: 18px; margin-top: 4px; }
    @media print {
        #ctrl-panel { display: none !important; }
        body { background: #fff; }
        #chit-wrapper { box-shadow: none; padding: 0; margin-top: 0 !important; }
        @page { margin: 0; }
    }
</style>

