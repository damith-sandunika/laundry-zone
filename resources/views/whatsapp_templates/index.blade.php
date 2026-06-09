<x-app-layout>
    <x-slot name="header">
        <div style="display:flex;justify-content:space-between;align-items:center;flex-wrap:wrap;gap:12px;">
            <div>
                <h2 class="ph-title">WhatsApp Message Templates</h2>
                <p style="font-size:13px;color:#94a3b8;font-weight:500;margin:0;">Manage automated WhatsApp messages sent to customers</p>
            </div>
        </div>
    </x-slot>

    @if(session('success'))
        <div style="margin-bottom:16px;padding:12px 18px;background:#f0fdf4;border:1.5px solid #86efac;border-radius:10px;font-size:13px;font-weight:700;color:#16a34a;">
            ✓ {{ session('success') }}
        </div>
    @endif

    <div x-data="whatsappTemplates({{ json_encode($templates) }})" style="display:flex;flex-direction:column;gap:16px;">
        
        <div style="display:grid;grid-template-columns:repeat(auto-fill, minmax(320px, 1fr));gap:16px;">
            <template x-for="template in templates" :key="template.id">
                <div style="background:white;border-radius:16px;border:1px solid #e2e8f0;box-shadow:0 2px 10px rgba(0,0,0,0.02);overflow:hidden;display:flex;flex-direction:column;">
                    <div style="padding:16px 20px;background:#f8fafc;border-bottom:1px solid #f1f5f9;display:flex;justify-content:space-between;align-items:center;">
                        <h3 style="font-size:14px;font-weight:800;color:#0f172a;margin:0;" x-text="template.name"></h3>
                        <span style="font-size:10px;font-weight:800;background:#dbeafe;color:#1d4ed8;padding:4px 8px;border-radius:6px;text-transform:uppercase;letter-spacing:0.05em;" x-text="template.type.replace('_', ' ')"></span>
                    </div>
                    <div style="padding:20px;flex:1;display:flex;flex-direction:column;gap:16px;">
                        <div style="background:#f1f5f9;border-radius:12px;padding:12px;font-size:13px;color:#334155;line-height:1.5;white-space:pre-wrap;font-family:monospace;" x-text="template.content"></div>
                        
                        <div style="margin-top:auto;">
                            <button @click="editTemplate(template)" style="width:100%;padding:10px;background:white;color:#1d4ed8;border:2px solid #bfdbfe;border-radius:10px;font-size:13px;font-weight:800;cursor:pointer;transition:all 0.15s;" onmouseover="this.style.background='#eff6ff'" onmouseout="this.style.background='white'">
                                Edit Message
                            </button>
                        </div>
                    </div>
                </div>
            </template>
        </div>

        <!-- Edit Modal -->
        <div x-show="showEditModal" class="fixed inset-0 z-50 flex items-center justify-center p-4" style="display:none;">
            <div class="absolute inset-0 bg-black bg-opacity-50 backdrop-blur-sm" @click="showEditModal = false"></div>
            <div class="relative bg-white rounded-2xl shadow-2xl w-full max-w-2xl z-10 flex flex-col overflow-hidden" @click.stop>
                <div class="p-5 border-b border-gray-100 bg-gray-50 flex justify-between items-center">
                    <div>
                        <h3 class="font-black text-lg text-gray-800" x-text="'Edit ' + (editingTemplate ? editingTemplate.name : '')"></h3>
                        <p class="text-xs text-gray-500 font-bold mt-1">Customize the message template using dynamic tags</p>
                    </div>
                    <button @click="showEditModal = false" class="text-gray-400 hover:text-gray-600">
                        <svg width="24" height="24" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
                    </button>
                </div>
                
                <form :action="editingTemplate ? '{{ url('/whatsapp-templates') }}/' + editingTemplate.id : ''" method="POST" class="flex flex-col md:flex-row m-0">
                    @csrf
                    @method('PUT')
                    
                    <div class="p-6 flex-1 border-r border-gray-100">
                        <label class="block text-xs font-bold text-gray-700 mb-2 uppercase tracking-wide">Message Content</label>
                        <textarea name="content" x-model="editContent" rows="10" required
                            class="w-full text-sm border-2 border-gray-200 rounded-xl p-3 focus:border-blue-500 focus:ring-blue-500 bg-gray-50 font-semibold outline-none resize-none leading-relaxed"></textarea>
                    </div>
                    
                    <div class="p-6 w-full md:w-64 bg-gray-50 flex flex-col gap-4">
                        <div>
                            <h4 class="text-xs font-bold text-gray-800 uppercase tracking-wide mb-3">Available Tags</h4>
                            <p class="text-xs text-gray-500 font-semibold mb-3 leading-relaxed">Click a tag to insert it into the message.</p>
                            <div class="flex flex-wrap gap-2">
                                <template x-if="editingTemplate && editingTemplate.available_tags">
                                    <template x-for="tag in editingTemplate.available_tags" :key="tag">
                                        <button type="button" @click="insertTag(tag)" class="px-2 py-1.5 bg-blue-100 text-blue-700 border border-blue-200 rounded text-xs font-black hover:bg-blue-200 transition-colors cursor-pointer" x-text="tag"></button>
                                    </template>
                                </template>
                            </div>
                        </div>

                        <div class="mt-auto pt-4 border-t border-gray-200">
                            <button type="submit" class="w-full py-3 bg-blue-600 hover:bg-blue-700 text-white font-black rounded-xl shadow-md transition-all text-sm">
                                Save Changes
                            </button>
                            <button type="button" @click="showEditModal = false" class="w-full py-2.5 mt-2 bg-white border-2 border-gray-200 hover:bg-gray-50 text-gray-600 font-black rounded-xl transition-all text-sm">
                                Cancel
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('alpine:init', () => {
            Alpine.data('whatsappTemplates', (templates) => ({
                templates: templates,
                showEditModal: false,
                editingTemplate: null,
                editContent: '',

                editTemplate(template) {
                    this.editingTemplate = template;
                    this.editContent = template.content;
                    this.showEditModal = true;
                },

                insertTag(tag) {
                    const textarea = document.querySelector('textarea[name="content"]');
                    const startPos = textarea.selectionStart;
                    const endPos = textarea.selectionEnd;
                    
                    this.editContent = this.editContent.substring(0, startPos) + tag + this.editContent.substring(endPos, this.editContent.length);
                    
                    // Focus back and move cursor
                    setTimeout(() => {
                        textarea.focus();
                        textarea.selectionStart = startPos + tag.length;
                        textarea.selectionEnd = startPos + tag.length;
                    }, 10);
                }
            }));
        });
    </script>
</x-app-layout>
