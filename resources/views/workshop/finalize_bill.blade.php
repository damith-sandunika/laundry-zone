<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <div>
                <a href="{{ route('workshop.dashboard') }}" class="text-sm font-bold text-slate-400 hover:text-blue-600 mb-1 inline-block">← Back to Dashboard</a>
                <h2 class="ph-title">Finalize Bill - Order #{{ $dispatch->order->order_number }}</h2>
            </div>
        </div>
    </x-slot>

    <div x-data="finalizeBill()" class="flex flex-col lg:flex-row gap-4 h-[calc(100vh-140px)]">
        
        <!-- Services List -->
        <div class="flex-1 bg-white rounded-2xl border border-slate-200 shadow-sm flex flex-col overflow-hidden">
            <div class="p-4 border-b border-slate-100 bg-slate-50">
                <input type="text" x-model="search" placeholder="Search services..." class="w-full px-4 py-2 bg-white border border-slate-200 rounded-lg text-sm font-bold outline-none focus:border-blue-500">
            </div>
            <div class="flex-1 overflow-y-auto p-4 grid grid-cols-2 md:grid-cols-3 gap-3 content-start custom-scrollbar">
                <template x-for="service in filteredServices" :key="service.id">
                    <button @click="addToCart(service)" class="p-3 border border-slate-200 rounded-xl hover:border-blue-500 hover:shadow-md transition-all text-left group bg-white">
                        <div class="text-xs font-black text-slate-800 mb-1" x-text="service.name"></div>
                        <div class="flex justify-between items-end">
                            <span class="text-sm font-black text-blue-600" x-text="'Rs.' + parseFloat(service.base_price).toFixed(0)"></span>
                            <span class="text-[10px] font-bold text-slate-400" x-text="service.price_type === 'per_kg' ? '/KG' : '/ITEM'"></span>
                        </div>
                    </button>
                </template>
            </div>
        </div>

        <!-- Order Cart -->
        <div class="w-full lg:w-[400px] bg-white rounded-2xl border border-slate-200 shadow-sm flex flex-col overflow-hidden">
            <div class="p-4 border-b border-slate-100 bg-slate-50">
                <h3 class="font-black text-slate-800">Order Items</h3>
            </div>
            
            <div class="flex-1 overflow-y-auto p-4 flex flex-col gap-3 custom-scrollbar">
                <template x-for="(item, index) in cart" :key="index">
                    <div class="p-3 border border-slate-200 rounded-xl flex items-center justify-between gap-2 bg-slate-50">
                        <div class="flex-1 min-w-0">
                            <div class="text-xs font-black text-slate-800 truncate" x-text="item.name"></div>
                            <div class="text-[10px] font-bold text-slate-500" x-text="'Rs.' + item.base_price"></div>
                        </div>
                        <div class="flex items-center gap-1">
                            <button @click="if(item.qty > 1) item.qty--" class="w-6 h-6 rounded bg-slate-200 text-slate-600 font-bold flex items-center justify-center">-</button>
                            <input type="number" x-model.number="item.qty" class="w-12 text-center text-xs font-black bg-transparent border-none outline-none py-1" min="1">
                            <button @click="item.qty++" class="w-6 h-6 rounded bg-slate-200 text-slate-600 font-bold flex items-center justify-center">+</button>
                        </div>
                        <button @click="cart.splice(index, 1)" class="p-1 text-red-400 hover:text-red-600">✕</button>
                    </div>
                </template>
                <div x-show="cart.length === 0" class="text-center py-8 text-sm font-bold text-slate-400">
                    No items added yet.
                </div>
            </div>

            <div class="p-4 border-t border-slate-100 bg-white shadow-[0_-4px_10px_rgba(0,0,0,0.02)]">
                <div class="flex justify-between items-center mb-2">
                    <span class="text-xs font-bold text-slate-500">Subtotal</span>
                    <span class="text-sm font-black text-slate-800" x-text="'Rs.' + subtotal.toFixed(2)"></span>
                </div>
                <div class="flex justify-between items-center mb-4">
                    <span class="text-xs font-bold text-slate-500">Total (Min Rs.3000)</span>
                    <span class="text-lg font-black text-blue-700" x-text="'Rs.' + Math.max(3000, subtotal).toFixed(2)"></span>
                </div>
                
                <button @click="saveOrder()" :disabled="isSaving"
                    class="w-full py-3 bg-blue-600 hover:bg-blue-700 text-white font-black text-sm rounded-xl transition-colors shadow-lg shadow-blue-500/30">
                    <span x-text="isSaving ? 'Saving...' : 'Finalize Bill'"></span>
                </button>
            </div>
        </div>
    </div>

    <script>
        function finalizeBill() {
            return {
                services: @json($services),
                search: '',
                cart: @json($cartItems),
                isSaving: false,
                orderId: {{ $dispatch->order_id }},
                
                get filteredServices() {
                    if (!this.search) return this.services;
                    return this.services.filter(s => s.name.toLowerCase().includes(this.search.toLowerCase()));
                },
                
                get subtotal() {
                    return this.cart.reduce((sum, item) => sum + (item.base_price * item.qty), 0);
                },
                
                addToCart(service) {
                    let existing = this.cart.find(i => i.service_type_id === service.id);
                    if (existing) {
                        existing.qty++;
                    } else {
                        this.cart.push({
                            service_type_id: service.id,
                            name: service.name,
                            base_price: service.base_price,
                            qty: 1
                        });
                    }
                },
                
                async saveOrder() {
                    if(this.cart.length === 0) return alert('Cart is empty!');
                    this.isSaving = true;
                    
                    try {
                        const res = await fetch(`/orders/${this.orderId}/update`, {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content
                            },
                            body: JSON.stringify({
                                cart: this.cart,
                                discount_percentage: 0
                            })
                        });
                        const data = await res.json();
                        if (data.success) {
                            window.location.href = '{{ route('workshop.dashboard') }}';
                        } else {
                            alert(data.message || 'Failed to update');
                        }
                    } catch (e) {
                        alert('Error saving order');
                    }
                    
                    this.isSaving = false;
                }
            }
        }
    </script>
</x-app-layout>
