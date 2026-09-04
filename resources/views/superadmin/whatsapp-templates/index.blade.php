<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-wrap justify-between items-center gap-3">
            <div>
                <h2 class="ph-title"><i class="fa-brands fa-whatsapp text-[#25D366]"></i> WhatsApp Templates</h2>
                <p style="font-size:13px;color:#94a3b8;font-weight:500;margin:0;">Manage Meta Cloud API WhatsApp automated notifications</p>
            </div>
        </div>
    </x-slot>

    @if(session('success'))
        <div style="display:flex;align-items:center;gap:10px;background:#f0fdf4;border:1.5px solid #86efac;border-radius:12px;padding:14px 18px;margin-bottom:20px;">
            <svg width="16" height="16" fill="none" stroke="#16a34a" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
            <span style="font-size:13px;font-weight:600;color:#15803d;">{{ session('success') }}</span>
        </div>
    @endif

    <div style="display:flex;flex-direction:column;gap:20px;">

        {{-- Global Toggle & API Config --}}
        <div style="background:#fff;border:1px solid #e5e7eb;border-radius:16px;padding:20px 24px;box-shadow:0 1px 4px rgba(0,0,0,0.05);">
            <div style="display:flex;flex-wrap:wrap;justify-content:space-between;align-items:flex-start;gap:16px;margin-bottom:20px;">
                <div>
                    <p style="font-weight:800;font-size:16px;color:#1e293b;margin:0 0 4px;">Meta API Configuration</p>
                    <p style="font-size:13px;color:#64748b;margin:0;">Enable WhatsApp notifications and set your Meta Cloud API credentials.</p>
                </div>
                <div style="display:flex;align-items:center;gap:12px;">
                    <form action="{{ route('whatsapp-templates.seedDefault') }}" method="POST">
                        @csrf
                        <button type="submit" style="padding:8px 16px;background:#f0fdf4;border:1px solid #86efac;border-radius:8px;font-size:12px;font-weight:700;color:#15803d;cursor:pointer;">↺ Reset Templates</button>
                    </form>
                </div>
            </div>

            <form action="{{ route('whatsapp-templates.toggleGlobal') }}" method="POST" class="grid grid-cols-1 md:grid-cols-3 gap-6">
                @csrf
                <div class="col-span-1 md:col-span-3 flex items-center gap-4 p-4 bg-slate-50 rounded-xl border border-slate-200">
                    <div>
                        <p style="font-weight:700;font-size:14px;color:#1e293b;margin:0;">Global WhatsApp Switch</p>
                        <p style="font-size:12px;color:#64748b;margin:0;">Turn on/off all WhatsApp messages.</p>
                    </div>
                    <label style="display:inline-flex;align-items:center;cursor:pointer;gap:8px;margin-left:auto;">
                        <span style="font-size:13px;font-weight:700;color:{{ $isWhatsappEnabled ? '#16a34a' : '#dc2626' }};">{{ $isWhatsappEnabled ? 'ON' : 'OFF' }}</span>
                        <input type="hidden" name="is_whatsapp_enabled" value="false">
                        <input type="checkbox" name="is_whatsapp_enabled" value="true" class="sr-only" {{ $isWhatsappEnabled ? 'checked' : '' }}>
                        <div style="width:52px;height:28px;border-radius:99px;background:{{ $isWhatsappEnabled ? '#25D366' : '#d1d5db' }};position:relative;cursor:pointer;transition:0.3s;">
                            <div style="position:absolute;top:3px;left:{{ $isWhatsappEnabled ? '27px' : '3px' }};width:22px;height:22px;border-radius:50%;background:#fff;box-shadow:0 2px 4px rgba(0,0,0,0.15);transition:0.3s;"></div>
                        </div>
                    </label>
                </div>

                <div>
                    <label class="block text-sm font-bold text-slate-700 mb-1">Phone Number ID</label>
                    <input type="text" name="whatsapp_phone_id" value="{{ $whatsappPhoneId }}" class="w-full px-4 py-2 rounded-lg border-slate-300 focus:border-green-500 focus:ring-green-500" placeholder="e.g. 11223344556677">
                </div>
                <div class="md:col-span-2">
                    <label class="block text-sm font-bold text-slate-700 mb-1">Permanent Access Token</label>
                    <input type="password" name="whatsapp_access_token" value="{{ $whatsappAccessToken }}" class="w-full px-4 py-2 rounded-lg border-slate-300 focus:border-green-500 focus:ring-green-500" placeholder="EAAI...">
                </div>

                <div class="col-span-1 md:col-span-3 text-right">
                    <button type="submit" class="bg-[#25D366] hover:bg-[#128C7E] text-white px-6 py-2 rounded-xl font-bold shadow-sm transition-all">
                        Save Configuration
                    </button>
                </div>
            </form>
        </div>

        @if($templates->isEmpty())
        <div style="background:#eff6ff;border:1.5px solid #bfdbfe;border-radius:16px;padding:32px;text-align:center;">
            <p style="font-weight:700;color:#1d4ed8;margin:0 0 8px;">No WhatsApp templates found</p>
            <p style="font-size:13px;color:#3b82f6;margin:0;">Click Reset Templates above to generate them.</p>
        </div>
        @endif

        <div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(380px,1fr));gap:16px;">
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
                    'order_completed'              => ['label'=>'Order Completed',         'emoji'=>'✅'],
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
                    <form action="{{ route('whatsapp-templates.update', $template->id) }}" method="POST" id="tog_{{ $template->id }}">
                        @csrf @method('PUT')
                        <input type="hidden" name="meta_template_name" value="{{ $template->meta_template_name }}">
                        <input type="hidden" name="language_code" value="{{ $template->language_code }}">
                        <input type="hidden" name="content" value="{{ $template->content }}">
                        <input type="hidden" name="is_active" value="{{ $template->is_active ? '0' : '1' }}">
                        <label style="display:inline-flex;align-items:center;gap:6px;cursor:pointer;">
                            <span style="font-size:11px;font-weight:700;color:{{ $template->is_active ? '#16a34a' : '#94a3b8' }};">{{ $template->is_active ? 'ON' : 'OFF' }}</span>
                            <div onclick="document.getElementById('tog_{{ $template->id }}').submit()"
                                 style="width:40px;height:22px;border-radius:99px;background:{{ $template->is_active ? '#25D366' : '#d1d5db' }};position:relative;cursor:pointer;transition:0.3s;">
                                <div style="position:absolute;top:2px;left:{{ $template->is_active ? '20px' : '2px' }};width:18px;height:18px;border-radius:50%;background:#fff;box-shadow:0 1px 4px rgba(0,0,0,0.2);transition:0.3s;"></div>
                            </div>
                        </label>
                    </form>
                </div>

                {{-- Message edit form --}}
                <form action="{{ route('whatsapp-templates.update', $template->id) }}" method="POST">
                    @csrf @method('PUT')
                    <input type="hidden" name="is_active" value="{{ $template->is_active ? '1' : '0' }}">
                    <div style="padding:14px 18px;" class="flex flex-col gap-3">
                        <div class="grid grid-cols-2 gap-3">
                            <div>
                                <label class="block text-[10px] font-bold text-slate-500 uppercase tracking-wider mb-1">Meta Template Name</label>
                                <input type="text" name="meta_template_name" value="{{ $template->meta_template_name }}" placeholder="e.g. order_confirm_v1"
                                       class="w-full text-xs padding-2 border border-slate-300 rounded-lg focus:ring-green-500 focus:border-green-500">
                            </div>
                            <div>
                                <label class="block text-[10px] font-bold text-slate-500 uppercase tracking-wider mb-1">Language Code</label>
                                <input type="text" name="language_code" value="{{ $template->language_code }}" placeholder="e.g. en_US"
                                       class="w-full text-xs padding-2 border border-slate-300 rounded-lg focus:ring-green-500 focus:border-green-500">
                            </div>
                        </div>

                        <div>
                            <label class="block text-[10px] font-bold text-slate-500 uppercase tracking-wider mb-1">Preview Content (For Reference)</label>
                            <textarea name="content" rows="3"
                                style="width:100%;padding:10px 12px;border:1.5px solid #e2e8f0;border-radius:8px;font-size:12px;color:#374151;resize:vertical;outline:none;background:#f8fafc;box-sizing:border-box;font-family:sans-serif;"
                                placeholder="Write what the template looks like here...">{{ $template->content }}</textarea>
                            <p class="text-[10px] text-slate-400 mt-1">Note: This content is for your reference only. Actual messages are managed in Meta Business Manager using the Template Name above. If your template has variables like ((1)), ((2)), ensure they match the order of: {customer_name}, {order_id}, {total}, {amount_due}, {payment_status}.</p>
                        </div>
                    </div>
                    <div style="padding:0 18px 14px;">
                        <button type="submit" style="width:100%;padding:9px;background:#25D366;color:#fff;border:none;border-radius:8px;font-size:12px;font-weight:700;cursor:pointer;" class="hover:bg-[#128C7E] transition-colors">
                            💾 Save Template Settings
                        </button>
                    </div>
                </form>
            </div>
            @endforeach
        </div>
    </div>
</x-app-layout>

