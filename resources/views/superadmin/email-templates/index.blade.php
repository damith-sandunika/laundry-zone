<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-wrap justify-between items-center gap-3">
            <div>
                <h2 class="ph-title">📧 Email Templates</h2>
                <p style="font-size:13px;color:#94a3b8;font-weight:500;margin:0;">Customize automated email notifications sent to customers</p>
            </div>
        </div>
    </x-slot>

    @if(session('success'))
        <div style="display:flex;align-items:center;gap:10px;background:#f0fdf4;border:1.5px solid #86efac;border-radius:12px;padding:14px 18px;margin-bottom:20px;">
            <svg width="16" height="16" fill="none" stroke="#16a34a" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
            <span style="font-size:13px;font-weight:600;color:#15803d;">{{ session('success') }}</span>
        </div>
    @endif

    <div x-data="emailTemplateManager()" style="display:flex;flex-direction:column;gap:20px;">

        {{-- Global Toggle --}}
        <div style="display:flex;flex-wrap:wrap;justify-content:space-between;align-items:center;gap:16px;background:#fff;border:1px solid #e5e7eb;border-radius:16px;padding:20px 24px;box-shadow:0 1px 4px rgba(0,0,0,0.05);">
            <div>
                <p style="font-weight:800;font-size:16px;color:#1e293b;margin:0 0 4px;">Global Email Switch</p>
                <p style="font-size:13px;color:#64748b;margin:0;">Enable or disable ALL outgoing emails system-wide.</p>
            </div>
            <div style="display:flex;align-items:center;gap:12px;">
                <span style="font-size:13px;font-weight:700;color:#374151;">{{ $isEmailEnabled ? 'Enabled' : 'Disabled' }}</span>
                <form action="{{ route('email-templates.toggleGlobal') }}" method="POST" id="globalToggleForm">
                    @csrf
                    <label style="position:relative;display:inline-flex;align-items:center;cursor:pointer;">
                        <input type="checkbox" name="is_email_enabled" value="true" class="sr-only peer"
                            {{ $isEmailEnabled ? 'checked' : '' }}
                            onchange="document.getElementById('globalToggleForm').submit()">
                        <div style="width:52px;height:28px;border-radius:99px;background:{{ $isEmailEnabled ? '#22c55e' : '#d1d5db' }};position:relative;transition:background 0.2s;">
                            <div style="position:absolute;top:3px;left:{{ $isEmailEnabled ? '27px' : '3px' }};width:22px;height:22px;border-radius:50%;background:#fff;box-shadow:0 2px 4px rgba(0,0,0,0.15);transition:left 0.2s;"></div>
                        </div>
                    </label>
                </form>
                <form action="{{ route('email-templates.seedDefault') }}" method="POST">
                    @csrf
                    <button type="submit" style="padding:8px 16px;background:#eff6ff;border:1px solid #bfdbfe;border-radius:8px;font-size:12px;font-weight:700;color:#1d4ed8;cursor:pointer;">
                        ↺ Reset Defaults
                    </button>
                </form>
            </div>
        </div>

        {{-- Template Cards Grid --}}
        <div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(320px,1fr));gap:16px;">
            @foreach($templates as $template)
            @php
                $colors = [
                    'order_confirmation'           => ['bg'=>'#eff6ff','border'=>'#bfdbfe','dot'=>'#3b82f6','label'=>'Order Confirmation'],
                    'bill_finalize'                => ['bg'=>'#fffbeb','border'=>'#fde68a','dot'=>'#f59e0b','label'=>'Bill Finalized'],
                    'order_ready'                  => ['bg'=>'#f0fdf4','border'=>'#86efac','dot'=>'#22c55e','label'=>'Order Ready'],
                    'order_delivered'              => ['bg'=>'#faf5ff','border'=>'#d8b4fe','dot'=>'#8b5cf6','label'=>'Order Delivered'],
                    'delivery_confirmation'        => ['bg'=>'#ecfeff','border'=>'#a5f3fc','dot'=>'#06b6d4','label'=>'Delivery On Way'],
                    'delivery_thank_you'           => ['bg'=>'#fdf4ff','border'=>'#f0abfc','dot'=>'#ec4899','label'=>'Delivery Thank You'],
                    'order_dispatched_to_workshop' => ['bg'=>'#fff7ed','border'=>'#fed7aa','dot'=>'#f97316','label'=>'Sent to Workshop'],
                    'order_processing'             => ['bg'=>'#eff6ff','border'=>'#bfdbfe','dot'=>'#3b82f6','label'=>'Processing at Workshop'],
                    'order_returned_from_workshop' => ['bg'=>'#fffbeb','border'=>'#fde68a','dot'=>'#f59e0b','label'=>'Back from Workshop'],
                    'order_cancelled'              => ['bg'=>'#fef2f2','border'=>'#fca5a5','dot'=>'#ef4444','label'=>'Order Cancelled'],
                ];
                $c = $colors[$template->type] ?? ['bg'=>'#f8fafc','border'=>'#e2e8f0','dot'=>'#64748b','label'=>$template->name];
            @endphp
            <div style="background:#fff;border:1.5px solid {{ $c['border'] }};border-radius:14px;padding:20px;display:flex;flex-direction:column;gap:14px;box-shadow:0 2px 8px rgba(0,0,0,0.04);">
                <div style="display:flex;justify-content:space-between;align-items:center;">
                    <div style="display:flex;align-items:center;gap:10px;">
                        <div style="width:10px;height:10px;border-radius:50%;background:{{ $c['dot'] }};flex-shrink:0;"></div>
                        <p style="font-weight:800;font-size:14px;color:#1e293b;margin:0;">{{ $c['label'] }}</p>
                    </div>
                    <span style="font-size:10px;font-weight:700;padding:3px 9px;border-radius:20px;background:{{ $template->is_active ? '#dcfce7' : '#fee2e2' }};color:{{ $template->is_active ? '#16a34a' : '#dc2626' }};">
                        {{ $template->is_active ? 'ACTIVE' : 'INACTIVE' }}
                    </span>
                </div>

                <div style="background:{{ $c['bg'] }};border-radius:8px;padding:10px 14px;">
                    <p style="margin:0;font-size:12px;font-weight:600;color:#374151;">Subject:</p>
                    <p style="margin:4px 0 0;font-size:12px;color:#64748b;">{{ $template->subject }}</p>
                </div>

                <div style="display:flex;gap:8px;">
                    <button
                        @click="openEditor({{ json_encode($template) }})"
                        style="flex:1;padding:8px 12px;background:#1e40af;color:#fff;border:none;border-radius:8px;font-size:12px;font-weight:700;cursor:pointer;display:flex;align-items:center;justify-content:center;gap:6px;">
                        <svg width="13" height="13" fill="none" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" d="M11 4.5L9.5 3L3 9.5V11h1.5L11 4.5z"/></svg>
                        Edit & Preview
                    </button>
                    <form action="{{ route('email-templates.update', $template) }}" method="POST">
                        @csrf @method('PUT')
                        <input type="hidden" name="subject" value="{{ $template->subject }}">
                        <input type="hidden" name="body" value="{{ $template->body }}">
                        <input type="hidden" name="is_active" value="{{ $template->is_active ? '0' : '1' }}">
                        <button type="submit"
                            style="padding:8px 12px;background:{{ $template->is_active ? '#fef2f2' : '#f0fdf4' }};border:1px solid {{ $template->is_active ? '#fca5a5' : '#86efac' }};border-radius:8px;font-size:12px;font-weight:700;cursor:pointer;color:{{ $template->is_active ? '#dc2626' : '#16a34a' }};">
                            {{ $template->is_active ? 'Disable' : 'Enable' }}
                        </button>
                    </form>
                </div>
            </div>
            @endforeach
        </div>

        {{-- Edit Modal with Split Preview --}}
        <div x-show="editorOpen" class="fixed inset-0 z-50" style="display:none;">
            <div class="fixed inset-0 bg-gray-900/50 backdrop-blur-sm" @click="editorOpen=false"></div>
            <div style="position:fixed;inset:0;display:flex;align-items:center;justify-content:center;padding:16px;z-index:51;">
                <div style="background:#fff;border-radius:20px;width:100%;max-width:1100px;max-height:90vh;display:flex;flex-direction:column;box-shadow:0 25px 60px rgba(0,0,0,0.25);overflow:hidden;">

                    {{-- Modal Header --}}
                    <div style="padding:18px 24px;border-bottom:1px solid #e5e7eb;display:flex;justify-content:space-between;align-items:center;background:linear-gradient(135deg,#1e40af,#3b82f6);">
                        <div>
                            <p style="font-weight:800;font-size:16px;color:#fff;margin:0;" x-text="'Edit: ' + (selectedTemplate?.name || '')"></p>
                            <p style="font-size:12px;color:#bfdbfe;margin:4px 0 0;">Changes will be saved to the database.</p>
                        </div>
                        <button @click="editorOpen=false" style="background:rgba(255,255,255,0.15);border:none;color:#fff;border-radius:8px;padding:6px 10px;cursor:pointer;font-size:18px;line-height:1;">✕</button>
                    </div>

                    {{-- Subject Bar --}}
                    <div style="padding:12px 24px;border-bottom:1px solid #f1f5f9;background:#f8fafc;">
                        <label style="font-size:11px;font-weight:700;color:#64748b;text-transform:uppercase;letter-spacing:0.05em;">Subject Line</label>
                        <input type="text" x-model="editSubject" style="width:100%;margin-top:4px;padding:8px 12px;border:1.5px solid #e2e8f0;border-radius:8px;font-size:13px;outline:none;background:#fff;box-sizing:border-box;">
                    </div>

                    {{-- Split Editor / Preview --}}
                    <div style="display:grid;grid-template-columns:1fr 1fr;flex:1;overflow:hidden;min-height:0;">
                        {{-- HTML Editor --}}
                        <div style="display:flex;flex-direction:column;border-right:1px solid #e5e7eb;">
                            <div style="padding:10px 16px;background:#f8fafc;border-bottom:1px solid #f1f5f9;">
                                <p style="margin:0;font-size:11px;font-weight:700;color:#64748b;text-transform:uppercase;letter-spacing:0.05em;">✏️ HTML Editor</p>
                                <p style="margin:2px 0 0;font-size:10px;color:#94a3b8;">Use: {customer_name} {order_id} {total} {amount_due} {payment_status}</p>
                            </div>
                            <textarea
                                x-model="editBody"
                                @input="updatePreview()"
                                style="flex:1;padding:16px;font-family:monospace;font-size:12px;line-height:1.6;border:none;outline:none;resize:none;overflow-y:auto;background:#1e293b;color:#e2e8f0;min-height:300px;"
                                placeholder="Enter HTML email body here..."></textarea>
                        </div>

                        {{-- Live Preview --}}
                        <div style="display:flex;flex-direction:column;overflow:hidden;">
                            <div style="padding:10px 16px;background:#f8fafc;border-bottom:1px solid #f1f5f9;display:flex;justify-content:space-between;align-items:center;">
                                <p style="margin:0;font-size:11px;font-weight:700;color:#64748b;text-transform:uppercase;letter-spacing:0.05em;">👁 Live Preview</p>
                                <span style="font-size:10px;color:#94a3b8;">Sample data used</span>
                            </div>
                            <div style="flex:1;overflow-y:auto;background:#f1f5f9;padding:16px;">
                                <iframe
                                    id="emailPreviewFrame"
                                    style="width:100%;height:100%;min-height:300px;border:none;background:#fff;border-radius:8px;"
                                    sandbox="allow-same-origin"></iframe>
                            </div>
                        </div>
                    </div>

                    {{-- Footer Actions --}}
                    <div style="padding:14px 24px;border-top:1px solid #e5e7eb;background:#f8fafc;display:flex;justify-content:flex-end;gap:10px;">
                        <button @click="editorOpen=false" style="padding:10px 20px;background:#fff;border:1.5px solid #e2e8f0;border-radius:10px;font-size:13px;font-weight:700;color:#374151;cursor:pointer;">
                            Cancel
                        </button>
                        <button @click="saveTemplate()" style="padding:10px 24px;background:linear-gradient(135deg,#1e40af,#3b82f6);color:#fff;border:none;border-radius:10px;font-size:13px;font-weight:700;cursor:pointer;box-shadow:0 4px 12px rgba(59,130,246,0.3);">
                            💾 Save Template
                        </button>
                    </div>
                </div>
            </div>
        </div>

        {{-- Hidden save form --}}
        <form id="saveTemplateForm" method="POST" style="display:none;">
            @csrf @method('PUT')
            <input type="hidden" name="subject" id="saveSubject">
            <input type="hidden" name="body" id="saveBody">
            <input type="hidden" name="is_active" id="saveIsActive" value="1">
        </form>
    </div>

    <x-slot name="scripts">
    <script>
        document.addEventListener('alpine:init', () => {
            Alpine.data('emailTemplateManager', () => ({
                editorOpen: false,
                selectedTemplate: null,
                editSubject: '',
                editBody: '',

                openEditor(template) {
                    this.selectedTemplate = template;
                    this.editSubject = template.subject;
                    this.editBody = template.body;
                    this.editorOpen = true;
                    this.$nextTick(() => {
                        this.updatePreview();
                    });
                },

                updatePreview() {
                    const frame = document.getElementById('emailPreviewFrame');
                    if (!frame) return;
                    // Replace placeholders with sample data
                    let html = this.editBody
                        .replace(/\{customer_name\}/g, 'Damith Sandunika')
                        .replace(/\{order_id\}/g, '1042')
                        .replace(/\{total\}/g, 'Rs.1,250.00')
                        .replace(/\{amount_due\}/g, 'Rs.750.00')
                        .replace(/\{payment_status\}/g, 'Partially Paid');

                    const doc = frame.contentDocument || frame.contentWindow.document;
                    doc.open();
                    doc.write(html);
                    doc.close();
                },

                saveTemplate() {
                    if (!this.selectedTemplate) return;
                    const form = document.getElementById('saveTemplateForm');
                    form.action = '/email-templates/' + this.selectedTemplate.id;
                    document.getElementById('saveSubject').value = this.editSubject;
                    document.getElementById('saveBody').value = this.editBody;
                    document.getElementById('saveIsActive').value = this.selectedTemplate.is_active ? '1' : '0';
                    form.submit();
                }
            }));
        });
    </script>
    </x-slot>
</x-app-layout>


