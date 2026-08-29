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
                <span style="font-size:13px;font-weight:700;color:{{ $isSmsEnabled ? '#16a34a' : '#dc2626' }};">
                    {{ $isSmsEnabled ? '✅ Enabled' : '❌ Disabled' }}
                </span>
                <form action="{{ route('sms-templates.toggleGlobal') }}" method="POST" id="globalSmsForm">
                    @csrf
                    <label style="position:relative;display:inline-flex;align-items:center;cursor:pointer;" title="Click to toggle">
                        <input type="checkbox" name="is_sms_enabled" value="true" class="sr-only peer"
                            {{ $isSmsEnabled ? 'checked' : '' }}
                            onchange="document.getElementById('globalSmsForm').submit()">
                        <div style="width:52px;height:28px;border-radius:99px;background:{{ $isSmsEnabled ? '#22c55e' : '#d1d5db' }};position:relative;transition:background 0.2s;cursor:pointer;">
                            <div style="position:absolute;top:3px;left:{{ $isSmsEnabled ? '27px' : '3px' }};width:22px;height:22px;border-radius:50%;background:#fff;box-shadow:0 2px 4px rgba(0,0,0,0.15);transition:left 0.2s;"></div>
                        </div>
                    </label>
                </form>
                <form action="{{ route('sms-templates.seedDefault') }}" method="POST">
                    @csrf
                    <button type="submit" style="padding:8px 16px;background:#f0fdf4;border:1px solid #86efac;border-radius:8px;font-size:12px;font-weight:700;color:#15803d;cursor:pointer;">
                        ↺ Reset Defaults
                    </button>
                </form>
            </div>
        </div>

        @if($templates->isEmpty())
        <div style="background:#eff6ff;border:1.5px solid #bfdbfe;border-radius:16px;padding:32px;text-align:center;">
            <p style="font-weight:700;color:#1d4ed8;margin:0 0 8px;">No SMS templates found</p>
            <p style="font-size:13px;color:#3b82f6;margin:0 0 20px;">Click Reset Defaults above to generate them.</p>
        </div>
        @endif

        {{-- Templates Grid --}}
        <div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(320px,1fr));gap:16px;">
            @foreach($templates as $template)
            @php
                $labels = [
                    'order_confirmation'           => ['label'=>'Order Confirmation',        'emoji'=>'🧾','color'=>'#3b82f6'],
                    'order_ready'                  => ['label'=>'Order Ready',               'emoji'=>'✅','color'=>'#22c55e'],
                    'order_delivered'              => ['label'=>'Order Delivered',            'emoji'=>'🎉','color'=>'#8b5cf6'],
                    'bill_finalize'                => ['label'=>'Bill Finalized',             'emoji'=>'💰','color'=>'#f59e0b'],
                    'delivery_confirmation'        => ['label'=>'Rider On the Way',          'emoji'=>'🚴','color'=>'#06b6d4'],
                    'delivery_thank_you'           => ['label'=>'Delivery Thank You',        'emoji'=>'💌','color'=>'#ec4899'],
                    'order_dispatched_to_workshop' => ['label'=>'Sent to Workshop',          'emoji'=>'🏭','color'=>'#f97316'],
                    'order_processing'             => ['label'=>'Processing at Workshop',    'emoji'=>'⚙️','color'=>'#3b82f6'],
                    'order_returned_from_workshop' => ['label'=>'Back from Workshop',        'emoji'=>'📦','color'=>'#f59e0b'],
                    'order_cancelled'              => ['label'=>'Order Cancelled',           'emoji'=>'❌','color'=>'#ef4444'],
                ];
                $info = $labels[$template->type] ?? ['label'=>ucwords(str_replace('_',' ',$template->type)),'emoji'=>'📱','color'=>'#64748b'];
            @endphp
            <div style="background:#fff;border:1.5px solid #e5e7eb;border-radius:14px;overflow:hidden;box-shadow:0 2px 6px rgba(0,0,0,0.04);display:flex;flex-direction:column;">
                {{-- Card header --}}
                <div style="padding:14px 18px;background:#f8fafc;border-bottom:1px solid #f1f5f9;display:flex;justify-content:space-between;align-items:center;">
                    <div style="display:flex;align-items:center;gap:10px;">
                        <span style="font-size:20px;">{{ $info['emoji'] }}</span>
                        <div>
                            <p style="font-weight:800;font-size:13px;color:#1e293b;margin:0;">{{ $info['label'] }}</p>
                            <p style="font-size:10px;color:#94a3b8;margin:2px 0 0;font-family:monospace;">{{ $template->type }}</p>
                        </div>
                    </div>
                    <span style="font-size:10px;font-weight:700;padding:4px 10px;border-radius:20px;background:{{ $template->is_active ? '#dcfce7' : '#fee2e2' }};color:{{ $template->is_active ? '#15803d' : '#dc2626' }};">
                        {{ $template->is_active ? 'ACTIVE' : 'INACTIVE' }}
                    </span>
                </div>

                {{-- Edit Form --}}
                <form action="{{ route('sms-templates.update', $template->id) }}" method="POST" style="flex:1;display:flex;flex-direction:column;">
                    @csrf @method('PUT')

                    <div style="padding:16px 18px;flex:1;">
                        <label style="font-size:10px;font-weight:700;color:#64748b;text-transform:uppercase;letter-spacing:0.05em;display:block;margin-bottom:6px;">Message Content</label>
                        <textarea name="message" rows="4" required style="width:100%;padding:10px 12px;border:1.5px solid #e2e8f0;border-radius:8px;font-size:12px;font-family:sans-serif;color:#374151;resize:vertical;outline:none;background:#f8fafc;box-sizing:border-box;">{{ $template->message }}</textarea>

                        <div style="margin-top:10px;display:flex;flex-wrap:wrap;gap:5px;">
                            @foreach(['{customer_name}','{order_id}','{total}','{amount_due}','{payment_status}'] as $var)
                            <span style="font-family:monospace;font-size:10px;background:#eff6ff;color:#1d4ed8;border:1px solid #bfdbfe;border-radius:4px;padding:2px 7px;cursor:pointer;" onclick="insertVar(this,'{{ $var }}')">{{ $var }}</span>
                            @endforeach
                        </div>
                    </div>

                    {{-- Footer with Enable/Disable + Save --}}
                    <div style="padding:12px 18px;border-top:1px solid #f1f5f9;display:flex;gap:8px;align-items:center;">
                        {{-- Enable toggle as separate hidden input --}}
                        <input type="hidden" name="is_active" value="{{ $template->is_active ? '1' : '0' }}">
                        <button type="submit"
                            style="flex:1;padding:9px 12px;background:linear-gradient(135deg,#1e40af,#3b82f6);color:#fff;border:none;border-radius:8px;font-size:12px;font-weight:700;cursor:pointer;">
                            💾 Save
                        </button>
                    </div>
                </form>

                {{-- Separate Enable/Disable form --}}
                <form action="{{ route('sms-templates.update', $template->id) }}" method="POST" style="padding:0 18px 14px;">
                    @csrf @method('PUT')
                    <input type="hidden" name="message" value="{{ $template->message }}">
                    <input type="hidden" name="is_active" value="{{ $template->is_active ? '0' : '1' }}">
                    <button type="submit"
                        style="width:100%;padding:7px 12px;background:{{ $template->is_active ? '#fef2f2' : '#f0fdf4' }};border:1.5px solid {{ $template->is_active ? '#fca5a5' : '#86efac' }};border-radius:8px;font-size:11px;font-weight:700;color:{{ $template->is_active ? '#dc2626' : '#16a34a' }};cursor:pointer;">
                        {{ $template->is_active ? '🔴 Disable This Template' : '🟢 Enable This Template' }}
                    </button>
                </form>
            </div>
            @endforeach
        </div>
    </div>

    <x-slot name="scripts">
    <script>
    function insertVar(el, varText) {
        const textarea = el.closest('form').querySelector('textarea[name="message"]');
        if (!textarea) return;
        const start = textarea.selectionStart;
        const end = textarea.selectionEnd;
        const val = textarea.value;
        textarea.value = val.substring(0, start) + varText + val.substring(end);
        textarea.selectionStart = textarea.selectionEnd = start + varText.length;
        textarea.focus();
    }
    </script>
    </x-slot>
</x-app-layout>
