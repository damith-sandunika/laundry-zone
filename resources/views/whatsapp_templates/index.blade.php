<x-app-layout>
    <x-slot name="header">
        <h2 class="ph-title">WhatsApp Messages</h2>
        <p class="ph-sub">Manage automated WhatsApp messages sent to customers</p>
    </x-slot>

    @if(session('success'))
        <div class="bg-green-50 text-green-700 px-6 py-4 mb-6 text-sm font-semibold border border-green-200 rounded-xl shadow-sm flex items-center animate-fade-in">
            <svg class="w-5 h-5 mr-3 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"></path></svg>
            {{ session('success') }}
        </div>
    @endif

    <div x-data='whatsappTemplates(@json($templates))' class="space-y-6">
        
        <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-6">
            <template x-for="template in templates" :key="template.id">
                <div class="bg-white rounded-[24px] border border-slate-200/60 shadow-sm flex flex-col overflow-hidden relative group hover:shadow-[0_12px_24px_rgba(0,0,0,0.06)] hover:-translate-y-1 transition-all duration-300">
                    
                    <div class="absolute top-0 right-0 w-32 h-32 bg-gradient-to-br from-blue-50 to-indigo-50 rounded-full blur-2xl -mr-16 -mt-16 pointer-events-none"></div>

                    <div class="p-6 pb-4 border-b border-slate-100/80 bg-white/50 backdrop-blur-sm z-10 flex justify-between items-start gap-4">
                        <div class="flex-1">
                            <h3 class="text-[15px] font-black text-slate-900 leading-tight tracking-tight" x-text="template.name"></h3>
                        </div>
                        <span class="text-[9px] font-extrabold bg-blue-50 text-blue-600 px-2.5 py-1 rounded-md uppercase tracking-widest shrink-0 shadow-sm border border-blue-100" x-text="template.type.replace('_', ' ')"></span>
                    </div>

                    <div class="p-6 pt-4 flex-1 flex flex-col gap-5 z-10">
                        <div class="bg-slate-50 border border-slate-100 rounded-xl p-4 flex-1">
                            <p class="text-[13px] font-semibold text-slate-600 leading-relaxed whitespace-pre-wrap font-mono" x-text="template.content"></p>
                        </div>
                        
                        <div class="mt-auto pt-1">
                            <button @click="editTemplate(template)" class="w-full py-3.5 bg-white hover:bg-slate-50 text-blue-600 border-2 border-blue-100 hover:border-blue-200 rounded-xl text-[13px] font-black shadow-sm transition-all flex items-center justify-center gap-2 group-hover:bg-blue-50">
                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"/></svg>
                                Customize Message
                            </button>
                        </div>
                    </div>
                </div>
            </template>
        </div>

        <!-- Edit Modal -->
        <div x-show="showEditModal" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4">
            <div class="absolute inset-0 bg-slate-900/60 backdrop-blur-sm transition-opacity" x-transition.opacity @click="showEditModal = false"></div>
            
            <div class="relative bg-white rounded-[24px] shadow-2xl w-full max-w-3xl z-10 flex flex-col overflow-hidden animate-slide-up" @click.stop>
                <div class="px-6 py-5 border-b border-slate-100 bg-slate-50/50 flex justify-between items-center">
                    <div>
                        <h3 class="font-black text-lg text-slate-900 flex items-center gap-2">
                            <svg class="w-5 h-5 text-blue-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                            Edit <span x-text="editingTemplate ? editingTemplate.name : ''"></span>
                        </h3>
                        <p class="text-[11px] text-slate-500 font-bold mt-1 uppercase tracking-widest">Customize the message using dynamic tags</p>
                    </div>
                    <button @click="showEditModal = false" class="p-2 -mr-2 text-slate-400 hover:text-slate-600 hover:bg-slate-100 rounded-lg transition-colors">
                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
                    </button>
                </div>
                
                <form :action="editingTemplate ? '{{ url('/whatsapp-templates') }}/' + editingTemplate.id : ''" method="POST" class="flex flex-col md:flex-row m-0 h-[450px]">
                    @csrf
                    @method('PUT')
                    
                    <div class="p-6 flex-1 flex flex-col bg-white">
                        <label class="block text-[11px] font-extrabold text-slate-500 mb-2 uppercase tracking-widest">Message Content</label>
                        <textarea name="content" x-model="editContent" rows="12" required
                            class="w-full flex-1 bg-slate-50 border-2 border-slate-200 focus:border-blue-500 focus:ring-4 focus:ring-blue-500/10 rounded-xl px-4 py-3 text-[14px] font-semibold text-slate-800 outline-none transition-all resize-none font-mono"></textarea>
                    </div>
                    
                    <div class="p-6 w-full md:w-72 bg-slate-50 border-l border-slate-100 flex flex-col">
                        <div>
                            <h4 class="text-[11px] font-extrabold text-slate-500 uppercase tracking-widest mb-3">Available Tags</h4>
                            <p class="text-[11.5px] font-semibold text-slate-400 mb-4 leading-relaxed">Click a tag below to insert it into the message at your cursor position.</p>
                            <div class="flex flex-wrap gap-2">
                                <template x-if="editingTemplate && editingTemplate.available_tags">
                                    <template x-for="tag in JSON.parse(editingTemplate.available_tags)" :key="tag">
                                        <button type="button" @click="insertTag(tag)" class="px-2.5 py-1.5 bg-blue-100/50 hover:bg-blue-100 text-blue-700 border border-blue-200 rounded-lg text-[11px] font-black transition-colors cursor-pointer flex items-center gap-1">
                                            <span class="text-blue-400 font-mono text-[10px]">{ }</span>
                                            <span x-text="tag"></span>
                                        </button>
                                    </template>
                                </template>
                            </div>
                        </div>

                        <div class="mt-auto pt-6 flex flex-col gap-3">
                            <button type="submit" class="w-full py-3.5 bg-gradient-to-r from-blue-600 to-indigo-600 hover:from-blue-700 hover:to-indigo-700 text-white font-black rounded-xl shadow-lg shadow-blue-500/30 hover:shadow-blue-500/50 hover:-translate-y-0.5 transition-all text-[13px] flex items-center justify-center gap-2">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"></path></svg>
                                Save Changes
                            </button>
                            <button type="button" @click="showEditModal = false" class="w-full py-3.5 bg-white border border-slate-200 hover:bg-slate-50 text-slate-600 font-bold rounded-xl transition-all text-[13px]">
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
                    if (!textarea) return;
                    
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


