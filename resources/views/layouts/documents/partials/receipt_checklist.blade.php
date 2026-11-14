<!-- 4. Notes & Checklist Customization (Fully Dynamic Add/Remove) -->
<div class="p-4 rounded-xl bg-slate-50 border border-slate-200/70 space-y-4">
    <div class="font-bold text-xs text-slate-800 uppercase tracking-wider flex items-center justify-between">
        <span>4. Special Note & Dynamic Workshop Checklist</span>
        <label class="flex items-center gap-1.5 cursor-pointer text-xs font-semibold text-slate-600">
            <input type="checkbox" name="custom_fields[show_process_list]" value="1" x-model="showProcessList" {{ $showProcessList == '1' ? 'checked' : '' }} class="rounded text-blue-600 focus:ring-blue-500">
            <span>Show Checklist</span>
        </label>
    </div>

    <!-- Special Notes Customizer -->
    <div x-show="showSpecialNotes" class="grid grid-cols-3 gap-3">
        <div class="col-span-2">
            <label class="block text-[11px] font-bold text-slate-600 mb-1">Special Note Label</label>
            <input type="text" name="custom_fields[notes_label]" x-model="notesLabel" value="{{ $notesLabel }}"
                class="w-full text-xs font-medium rounded-xl border-slate-200 bg-white" placeholder="e.g. Special Note :">
        </div>
        <div>
            <label class="block text-[11px] font-bold text-slate-600 mb-1">Blank Lines</label>
            <select name="custom_fields[notes_line_count]" x-model="notesLineCount" class="w-full text-xs font-semibold rounded-xl border-slate-200 bg-white">
                <option value="1" {{ $notesLineCount === 1 ? 'selected' : '' }}>1 Line</option>
                <option value="2" {{ $notesLineCount === 2 ? 'selected' : '' }}>2 Lines</option>
                <option value="3" {{ $notesLineCount === 3 ? 'selected' : '' }}>3 Lines</option>
                <option value="4" {{ $notesLineCount === 4 ? 'selected' : '' }}>4 Lines</option>
            </select>
        </div>
    </div>

    <!-- Dynamic Checklist Steps (Add / Edit / Remove) -->
    <div x-show="showProcessList" class="space-y-3 pt-2 border-t border-slate-200/60">
        <div class="flex items-center justify-between">
            <label class="block text-[11px] font-bold text-slate-700 uppercase tracking-wider">
                Checklist Process Steps (<span x-text="processItems.length"></span> steps)
            </label>
            <button type="button" @click="addProcessItem()"
                class="px-3 py-1 bg-emerald-600 hover:bg-emerald-700 text-white rounded-lg text-xs font-bold inline-flex items-center gap-1 shadow-sm transition">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/></svg>
                <span>Add Step</span>
            </button>
        </div>

        <div class="space-y-2 max-h-60 overflow-y-auto pr-1">
            <template x-for="(item, idx) in processItems" :key="idx">
                <div class="flex items-center gap-2 bg-white p-2 rounded-xl border border-slate-200 shadow-2xs">
                    <span class="text-xs font-bold text-slate-400 w-5 text-center" x-text="idx + 1"></span>
                    <input type="text" x-model="item.name" @input="syncJson()"
                        class="flex-1 text-xs font-bold rounded-lg border-slate-200 bg-slate-50 focus:bg-white focus:ring-blue-500"
                        placeholder="Process Label (e.g. Wash)">
                    
                    <span class="text-xs font-bold text-slate-400">:</span>
                    
                    <input type="text" x-model="item.suffix" @input="syncJson()"
                        class="w-28 text-xs font-mono rounded-lg border-slate-200 bg-slate-50 focus:bg-white focus:ring-blue-500"
                        placeholder="Sign / Line">
                    
                    <button type="button" @click="removeProcessItem(idx)"
                        class="p-1.5 text-slate-400 hover:text-rose-600 rounded-lg hover:bg-rose-50 transition" title="Delete Step">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                    </button>
                </div>
            </template>
        </div>
    </div>
</div>

