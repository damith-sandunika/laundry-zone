<!-- Item Note Modal -->
<div x-show="showNoteModal" class="fixed inset-0 z-[100] flex items-center justify-center" style="display: none;">
    <!-- Backdrop -->
    <div class="fixed inset-0 bg-slate-900/50 backdrop-blur-sm" @click="showNoteModal = false" x-transition.opacity></div>

    <!-- Modal Content -->
    <div class="bg-white rounded-3xl w-full max-w-sm shadow-2xl overflow-hidden relative z-10 mx-4"
         x-transition:enter="transition ease-out duration-300"
         x-transition:enter-start="opacity-0 scale-95 translate-y-4"
         x-transition:enter-end="opacity-100 scale-100 translate-y-0"
         x-transition:leave="transition ease-in duration-200"
         x-transition:leave-start="opacity-100 scale-100 translate-y-0"
         x-transition:leave-end="opacity-0 scale-95 translate-y-4">
        
        <div class="p-6">
            <h3 class="text-lg font-black text-slate-800 mb-1">Add Note</h3>
            <p class="text-xs font-bold text-slate-500 mb-4">Add special instructions for this item.</p>

            <textarea id="note-modal-input" x-model="currentNoteText" rows="3" placeholder="e.g. Wash separately, tough stain on collar..." 
                class="w-full bg-slate-50 border border-slate-200 rounded-xl p-3 text-sm font-bold text-slate-800 focus:border-blue-500 focus:ring-4 focus:ring-blue-500/10 outline-none resize-none transition-all"></textarea>
            
            <div class="flex items-center justify-end gap-3 mt-5">
                <button @click="showNoteModal = false" class="px-5 py-2.5 rounded-xl text-sm font-bold text-slate-600 bg-slate-100 hover:bg-slate-200 transition-colors">Cancel</button>
                <button @click="saveNote()" class="px-5 py-2.5 rounded-xl text-sm font-black text-white bg-blue-600 hover:bg-blue-700 shadow-md shadow-blue-500/25 transition-colors">Save Note</button>
            </div>
        </div>
    </div>
</div>
