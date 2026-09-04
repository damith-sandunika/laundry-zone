<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-wrap justify-between items-center gap-3">
            <div>
                <h2 class="ph-title">📱 SMS Templates</h2>
                <p style="font-size:13px;color:#94a3b8;font-weight:500;margin:0;">Manage automated SMS notifications sent to customers</p>
            </div>
        </div>
    </x-slot>

    @if(session('success'))
        <div style="display:flex;align-items:center;gap:10px;background:#f0fdf4;border:1.5px solid #86efac;border-radius:12px;padding:14px 18px;margin-bottom:20px;">
            <svg width="16" height="16" fill="none" stroke="#16a34a" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
            <span style="font-size:13px;font-weight:600;color:#15803d;">{{ session('success') }}</span>
        </div>
    @endif

    @php $isSmsEnabled = \App\Models\Setting::where('key','is_sms_enabled')->value('value') === 'true'; @endphp

    <div style="display:flex;flex-direction:column;gap:20px;">

        {{-- Global Toggle --}}
        <div style="display:flex;flex-wrap:wrap;justify-content:space-between;align-items:center;gap:16px;background:#fff;border:1px solid #e5e7eb;border-radius:16px;padding:20px 24px;box-shadow:0 1px 4px rgba(0,0,0,0.05);">
            <div>
                <p style="font-weight:800;font-size:16px;color:#1e293b;margin:0 0 4px;">Global SMS Switch</p>
                <p style="font-size:13px;color:#64748b;margin:0;">Enable or disable ALL outgoing SMS system-wide.</p>
            </div>
            <div style="display:flex;align-items:center;gap:12px;">
                <form action="{{ route('sms-templates.toggleGlobal') }}" method="POST" id="globalSmsForm">
                    @csrf
                    <label style="display:inline-flex;align-items:center;cursor:pointer;gap:8px;">
                        <span style="font-size:13px;font-weight:700;color:{{ $isSmsEnabled ? '#16a34a' : '#dc2626' }};">{{ $isSmsEnabled ? 'ON' : 'OFF' }}</span>
                        <input type="checkbox" name="is_sms_enabled" value="true" class="sr-only" {{ $isSmsEnabled ? 'checked' : '' }} onchange="document.getElementById('globalSmsForm').submit()">
                        <div style="width:52px;height:28px;border-radius:99px;background:{{ $isSmsEnabled ? '#22c55e' : '#d1d5db' }};position:relative;cursor:pointer;">
                            <div style="position:absolute;top:3px;left:{{ $isSmsEnabled ? '27px' : '3px' }};width:22px;height:22px;border-radius:50%;background:#fff;box-shadow:0 2px 4px rgba(0,0,0,0.15);"></div>
                        </div>
                    </label>
                </form>
                <form action="{{ route('sms-templates.seedDefault') }}" method="POST">
                    @csrf
                    <button type="submit" style="padding:8px 16px;background:#f0fdf4;border:1px solid #86efac;border-radius:8px;font-size:12px;font-weight:700;color:#15803d;cursor:pointer;">↺ Reset Defaults</button>
                </form>
            </div>
        </div>

        @if($templates->isEmpty())
        <div style="background:#eff6ff;border:1.5px solid #bfdbfe;border-radius:16px;padding:32px;text-align:center;">
            <p style="font-weight:700;color:#1d4ed8;margin:0 0 8px;">No SMS templates found</p>
            <p style="font-size:13px;color:#3b82f6;margin:0;">Click Reset Defaults above to generate them.</p>
        </div>
        @endif

        <div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(320px,1fr));gap:16px;">
            @foreach($templates as $template)
            @php
                $labels = [
                    'order_confirmation'           => ['label'=>'Order Confirmation',      'emoji'=>'🧾'],
                    'order_ready'                  => ['label'=>'Order Ready',             'emoji'=>'✅'],
                    'order_delivered'              => ['label'=>'Order Delivered',          'emoji'=>'🎉'],
                    'bill_finalize'                => ['label'=>'Bill Finalized',           'emoji'=>'💰'],
                    'delivery_confirmation'        => ['label'=>'Rider On the Way',        'emoji'=>'🚴'],
                    'delivery_thank_you'           => ['label'=>'Delivery Thank You',      'emoji'=>'💌'],
                    'order_dispatched_to_workshop' => ['label'=>'Sent to Workshop',        'emoji'=>'🏭'],
                    'order_processing'             => ['label'=>'Processing at Workshop',  'emoji'=>'⚙️'],
                    'order_returned_from_workshop' => ['label'=>'Back from Workshop',      'emoji'=>'📦'],
                    'order_cancelled'              => ['label'=>'Order Cancelled',         'emoji'=>'❌'],
                ];
                $info = $labels[$template->type] ?? ['label'=>ucwords(str_replace('_',' ',$template->type)),'emoji'=>'📱'];
            @endphp

            <div style="background:#fff;border:1.5px solid {{ $template->is_active ? '#86efac' : '#e5e7eb' }};border-radius:14px;overflow:hidden;box-shadow:0 2px 6px rgba(0,0,0,0.04);">

                {{-- Header with inline toggle --}}
                <div style="padding:14px 18px;background:{{ $template->is_active ? '#f0fdf4' : '#f8fafc' }};border-bottom:1px solid #f1f5f9;display:flex;justify-content:space-between;align-items:center;">
                    <div style="display:flex;align-items:center;gap:10px;">
                        <span style="font-size:18px;">{{ $info['emoji'] }}</span>
                        <p style="font-weight:800;font-size:13px;color:#1e293b;margin:0;">{{ $info['label'] }}</p>
                    </div>

                    {{-- Toggle form --}}
                    <form action="{{ route('sms-templates.update', $template->id) }}" method="POST" id="tog_{{ $template->id }}">
                        @csrf @method('PUT')
                        <input type="hidden" name="message" value="{{ $template->message }}">
                        <input type="hidden" name="is_active" value="{{ $template->is_active ? '0' : '1' }}">
                        <label style="display:inline-flex;align-items:center;gap:6px;cursor:pointer;">
                            <span style="font-size:11px;font-weight:700;color:{{ $template->is_active ? '#16a34a' : '#94a3b8' }};">{{ $template->is_active ? 'ON' : 'OFF' }}</span>
                            <div onclick="document.getElementById('tog_{{ $template->id }}').submit()"
                                 style="width:40px;height:22px;border-radius:99px;background:{{ $template->is_active ? '#22c55e' : '#d1d5db' }};position:relative;cursor:pointer;">
                                <div style="position:absolute;top:2px;left:{{ $template->is_active ? '20px' : '2px' }};width:18px;height:18px;border-radius:50%;background:#fff;box-shadow:0 1px 4px rgba(0,0,0,0.2);"></div>
                            </div>
                        </label>
                    </form>
                </div>

                {{-- Message edit form --}}
                <form action="{{ route('sms-templates.update', $template->id) }}" method="POST">
                    @csrf @method('PUT')
                    <input type="hidden" name="is_active" value="{{ $template->is_active ? '1' : '0' }}">
                    <div style="padding:14px 18px;">
                        <textarea name="message" rows="4" required
                            style="width:100%;padding:10px 12px;border:1.5px solid #e2e8f0;border-radius:8px;font-size:12px;color:#374151;resize:vertical;outline:none;background:#f8fafc;box-sizing:border-box;font-family:sans-serif;">{{ $template->message }}</textarea>
                        <div style="margin-top:7px;display:flex;flex-wrap:wrap;gap:4px;">
                            @foreach(['{customer_name}','{order_id}','{total}','{amount_due}','{payment_status}'] as $var)
                            <span style="font-family:monospace;font-size:10px;background:#eff6ff;color:#1d4ed8;border:1px solid #bfdbfe;border-radius:4px;padding:2px 7px;cursor:pointer;"
                                onclick="insertSmsVar(this,'{{ $var }}')">{{ $var }}</span>
                            @endforeach
                        </div>
                    </div>
                    <div style="padding:0 18px 14px;">
                        <button type="submit" style="width:100%;padding:9px;background:linear-gradient(135deg,#1e40af,#3b82f6);color:#fff;border:none;border-radius:8px;font-size:12px;font-weight:700;cursor:pointer;">
                            💾 Save Message
                        </button>
                    </div>
                </form>
            </div>
            @endforeach
        </div>
    </div>

    <x-slot name="scripts">
    <script>
    function insertSmsVar(el, varText) {
        const textarea = el.closest('form').querySelector('textarea');
        if (!textarea) return;
        const s = textarea.selectionStart, e = textarea.selectionEnd;
        textarea.value = textarea.value.substring(0,s) + varText + textarea.value.substring(e);
        textarea.selectionStart = textarea.selectionEnd = s + varText.length;
        textarea.focus();
    }
    </script>
    </x-slot>
</x-app-layout>

