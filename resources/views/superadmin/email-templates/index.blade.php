<x-app-layout>
    <x-slot name="header">
        <h2 class="ph-title">📧 Email Templates</h2>
        <p class="ph-sub">Manage and customize automated Email notifications</p>
    </x-slot>

    <div class="space-y-6">
        
        <!-- Top Bar: Global Toggle & Actions -->
        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 bg-white p-5 rounded-2xl shadow-sm border border-gray-100">
            <div>
                <h3 class="text-lg font-extrabold text-gray-800">Global Email Gateway</h3>
                <p class="text-sm text-gray-500 mt-1">Enable or disable all outgoing Email messages across the system.</p>
            </div>
            
            <div class="flex items-center gap-3 bg-gray-50 py-2 px-4 rounded-xl border border-gray-200">
                <span class="text-sm font-bold text-gray-700">Master Switch</span>
                <form action="{{ route('email-templates.toggleGlobal') }}" method="POST" id="globalToggleForm" class="flex items-center h-full m-0">
                    @csrf
                    <label class="relative inline-flex items-center cursor-pointer">
                        <input type="checkbox" name="is_email_enabled" value="true" class="sr-only peer" {{ \App\Models\Setting::get('is_email_enabled', 'false') === 'true' ? 'checked' : '' }} onchange="document.getElementById('globalToggleForm').submit()">
                        <div class="w-11 h-6 bg-gray-300 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-green-500 shadow-inner"></div>
                    </label>
                </form>
            </div>
        </div>

        <!-- Success Message -->
        @if(session('success'))
            <div class="bg-green-50 border border-green-200 text-green-700 px-4 py-3 rounded-xl shadow-sm flex items-center gap-3">
                <svg class="w-5 h-5 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"></path></svg>
                <span class="text-sm font-bold">{{ session('success') }}</span>
            </div>
        @endif
        
        <!-- Seed Templates Button (Always Visible) -->
        <div class="bg-blue-50 border border-blue-200 p-6 rounded-2xl flex flex-col sm:flex-row items-center justify-between gap-4 shadow-sm">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 bg-blue-100 rounded-full flex items-center justify-center text-blue-600 shrink-0">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                </div>
                <div>
                    <h4 class="text-blue-800 font-bold text-sm">Missing Templates?</h4>
                    <p class="text-blue-600 text-xs mt-0.5">Click here to generate any missing system default templates.</p>
                </div>
            </div>
            <form action="{{ route('email-templates.seedDefault') }}" method="POST">
                @csrf
                <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-2.5 px-6 rounded-xl shadow-sm transition-colors text-sm whitespace-nowrap">
                    Generate Default Templates
                </button>
            </form>
        </div>

        <!-- Templates Grid -->
        <div class="grid grid-cols-1 xl:grid-cols-2 gap-6">
            @foreach($templates as $template)
                <div class="bg-white rounded-2xl shadow-sm border border-gray-100 hover:shadow-md transition-all duration-200 overflow-hidden flex flex-col">
                    <form action="{{ route('email-templates.update', $template->id) }}" method="POST" class="h-full flex flex-col m-0">
                        @csrf
                        @method('PUT')
                        
                        <!-- Card Header -->
                        <div class="p-5 border-b border-gray-50 flex justify-between items-center bg-slate-50/50">
                            <h5 class="font-extrabold text-slate-700 flex items-center gap-2 text-[15px] capitalize">
                                <span class="w-8 h-8 rounded-full bg-blue-100 text-blue-600 flex items-center justify-center shadow-inner">
                                    <i class="fa-solid fa-envelope-open-text text-sm"></i>
                                </span>
                                {{ str_replace('_', ' ', $template->type) }}
                            </h5>
                            
                            <!-- Individual Toggle -->
                            <div class="flex items-center" title="Enable or Disable this specific template">
                                <input type="hidden" name="is_active" value="0">
                                <label class="relative inline-flex items-center cursor-pointer">
                                    <input type="checkbox" name="is_active" value="1" class="sr-only peer" {{ $template->is_active ? 'checked' : '' }} onchange="this.form.submit()">
                                    <div class="w-9 h-5 bg-gray-300 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-4 after:w-4 after:transition-all peer-checked:bg-green-500 shadow-inner"></div>
                                </label>
                            </div>
                        </div>

                        <!-- Card Body -->
                        <div class="p-5 flex-grow flex flex-col gap-4">
                            <div>
                                <label class="block text-xs font-bold text-gray-500 uppercase tracking-wider mb-2">Email Subject</label>
                                <input type="text" name="subject" value="{{ $template->subject }}" class="w-full bg-gray-50 border border-gray-200 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-blue-500 p-3 text-sm text-gray-700 font-medium shadow-inner" required>
                            </div>

                            <div>
                                <label class="block text-xs font-bold text-gray-500 uppercase tracking-wider mb-2">HTML Body Content</label>
                                <textarea name="body" class="w-full bg-gray-50 border border-gray-200 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-blue-500 p-3 text-sm text-gray-700 font-mono resize-none shadow-inner" rows="6" required>{{ $template->body }}</textarea>
                            </div>
                            
                            <!-- Variables -->
                            <div class="bg-slate-50 p-3.5 rounded-xl border border-slate-100 mt-auto">
                                <p class="text-[11px] font-bold text-slate-500 uppercase tracking-widest mb-2 flex items-center gap-1.5">
                                    <i class="fa-solid fa-wand-magic-sparkles text-blue-500"></i> Available Variables
                                </p>
                                <div class="flex flex-wrap gap-1.5">
                                    <span class="bg-white border border-slate-200 text-slate-600 px-2 py-1 rounded text-[11px] font-mono font-bold shadow-sm select-all">{customer_name}</span>
                                    <span class="bg-white border border-slate-200 text-slate-600 px-2 py-1 rounded text-[11px] font-mono font-bold shadow-sm select-all">{order_id}</span>
                                    <span class="bg-white border border-slate-200 text-slate-600 px-2 py-1 rounded text-[11px] font-mono font-bold shadow-sm select-all">{total}</span>
                                    <span class="bg-white border border-slate-200 text-slate-600 px-2 py-1 rounded text-[11px] font-mono font-bold shadow-sm select-all">{amount_due}</span>
                                </div>
                            </div>
                        </div>

                        <!-- Card Footer -->
                        <div class="p-4 border-t border-gray-50 bg-gray-50/30">
                            <button type="submit" class="w-full bg-slate-800 hover:bg-slate-900 text-white font-bold py-2.5 px-4 rounded-xl shadow-sm transition-colors text-sm flex justify-center items-center gap-2">
                                <i class="fa-solid fa-floppy-disk"></i> Save Template
                            </button>
                        </div>
                    </form>
                </div>
            @endforeach
        </div>
    </div>
</x-app-layout>
