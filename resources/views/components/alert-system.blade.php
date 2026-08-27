@props(['playAudio' => true])

@php
    $alertTypes = [
        'new_order' => ['label' => 'New Bookings', 'icon' => 'M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z', 'color' => 'emerald'],
        'branch_dispatch' => ['label' => 'Dispatched to Us', 'icon' => 'M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4', 'color' => 'amber'],
        'workshop_return' => ['label' => 'Returned Items', 'icon' => 'M3 10h10a8 8 0 018 8v2M3 10l6 6m-6-6l6-6', 'color' => 'purple'],
        'payment' => ['label' => 'Payments', 'icon' => 'M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z', 'color' => 'blue'],
        'delivery_arrived' => ['label' => 'Deliveries', 'icon' => 'M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4', 'color' => 'indigo'],
        'rider_task' => ['label' => 'Tasks', 'icon' => 'M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.242-4.243a8 8 0 1111.314 0z', 'color' => 'orange'],
        'general' => ['label' => 'Alerts', 'icon' => 'M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9', 'color' => 'red']
    ];
@endphp

<div x-data="alertSystem()" x-init="init()" class="relative mr-2 lg:mr-4 flex items-center gap-2">
    
    @foreach($alertTypes as $typeKey => $t)
    <div x-show="grouped['{{ $typeKey }}'] && grouped['{{ $typeKey }}'].length > 0" class="relative" x-cloak>
        <button @click="openType = (openType === '{{ $typeKey }}' ? null : '{{ $typeKey }}')" @click.away="if(openType === '{{ $typeKey }}') openType = null" class="relative flex items-center justify-center w-10 h-10 rounded-xl bg-slate-50 border-2 border-slate-100 hover:bg-slate-100 hover:border-slate-200 transition-colors text-slate-500 hover:text-slate-700" title="{{ $t['label'] }}">
            <!-- Flashing dot -->
            <div class="absolute top-[6px] right-[6px] w-2.5 h-2.5 rounded-full bg-{{ $t['color'] }}-500 ring-2 ring-white animate-pulse"></div>
            
            <svg class="w-[20px] h-[20px] animate-pulse text-{{ $t['color'] }}-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.2"><path stroke-linecap="round" stroke-linejoin="round" d="{{ $t['icon'] }}"/></svg>
        </button>

        <!-- Dropdown -->
        <div x-show="openType === '{{ $typeKey }}'" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0 translate-y-2" x-transition:enter-end="opacity-100 translate-y-0" x-transition:leave="transition ease-in duration-150" x-transition:leave-start="opacity-100 translate-y-0" x-transition:leave-end="opacity-0 translate-y-2" class="absolute right-0 top-[52px] w-[300px] bg-white/95 backdrop-blur-xl border border-slate-200 rounded-2xl shadow-2xl overflow-hidden z-50 origin-top-right" x-cloak>
            <div class="flex items-center justify-between px-4 py-3 border-b border-slate-100 bg-slate-50/50">
                <span class="text-xs font-black text-slate-800">{{ $t['label'] }}</span>
                <div class="flex items-center gap-2">
                    <span class="text-[10px] font-bold text-{{ $t['color'] }}-700 bg-{{ $t['color'] }}-100 px-2 py-0.5 rounded-md" x-text="(grouped['{{ $typeKey }}'] ? grouped['{{ $typeKey }}'].length : 0) + ' New'"></span>
                    <button @click="markAsRead('{{ $typeKey }}')" class="text-[10px] font-bold text-blue-600 hover:text-blue-800 underline">Mark as read</button>
                </div>
            </div>
            <div class="max-h-[280px] overflow-y-auto">
                <template x-for="notif in (grouped['{{ $typeKey }}'] || [])" :key="notif.id">
                    <a :href="notif.url" @click="markAsRead('{{ $typeKey }}')" class="flex gap-3 px-4 py-3 border-b border-slate-50 hover:bg-slate-50/50 transition-colors">
                        <div class="mt-0.5 flex-shrink-0">
                            <svg class="w-4 h-4 text-{{ $t['color'] }}-500" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        </div>
                        <div>
                            <p class="text-[11.5px] font-semibold text-slate-700 leading-snug mb-0.5" x-text="notif.message"></p>
                            <span class="text-[9.5px] font-medium text-slate-400" x-text="notif.time"></span>
                        </div>
                    </a>
                </template>
            </div>
        </div>
    </div>
    @endforeach

    <!-- Audio Player (Base64 Beep) -->
    <audio id="notification-sound" src="data:audio/mp3;base64,//NExAAAAANIAAAAAExBTUUzLjEwMKqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqq//NExAAAAANIAAAAAExBTUUzLjEwMKqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqq" preload="auto" loop></audio>
</div>

<script>
    function alertSystem() {
        return {
            openType: null,
            count: 0,
            grouped: {},
            lastCount: 0,
            init() {
                this.check();
                setInterval(() => {
                    this.check();
                }, 10000); // Poll every 10 seconds
            },
            check() {
                fetch('/notifications/check', {
                    headers: {
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                })
                .then(response => response.json())
                .then(data => {
                    this.grouped = data.grouped || {};
                    this.count = data.count;
                    
                    if (this.count > 0 && this.count > this.lastCount) {
                        this.playSound();
                    } else if (this.count === 0) {
                        this.stopSound();
                    }
                    this.lastCount = this.count;
                })
                .catch(error => console.error('Error fetching notifications:', error));
            },
            markAsRead(type) {
                const url = type ? '/notifications/read?type=' + encodeURIComponent(type) : '/notifications/read';
                fetch(url, {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        if (type) {
                            // Optimistically remove from grouped
                            this.grouped[type] = [];
                            // Recalculate count
                            let newCount = 0;
                            for (let t in this.grouped) {
                                newCount += this.grouped[t].length;
                            }
                            this.count = newCount;
                            this.lastCount = newCount;
                        } else {
                            this.count = 0;
                            this.lastCount = 0;
                            this.grouped = {};
                        }
                        
                        this.openType = null;
                        
                        if (this.count === 0) {
                            this.stopSound();
                        }
                    }
                });
            },
            playSound() {
                const audio = document.getElementById('notification-sound');
                if (audio) {
                    audio.play().catch(e => console.log('Audio play failed (browser policy):', e));
                }
            },
            stopSound() {
                const audio = document.getElementById('notification-sound');
                if (audio) {
                    audio.pause();
                    audio.currentTime = 0;
                }
            }
        }
    }
</script>
