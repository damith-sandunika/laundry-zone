@props(['playAudio' => true])

<div x-data="alertSystem()" x-init="init()" class="relative mr-2 sm:mr-4 flex items-center">
    
    <!-- Notification Bell -->
    <button @click="notifOpen = !notifOpen; profileOpen = false" @click.away="notifOpen = false" class="relative p-2 rounded-full hover:bg-slate-100 transition-colors focus:outline-none">
        <svg class="w-5 h-5 sm:w-6 sm:h-6 text-slate-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
            <path stroke-linecap="round" stroke-linejoin="round" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" />
        </svg>
        <!-- Badge -->
        <div x-show="count > 0" x-cloak class="absolute top-1.5 right-1.5 w-2 h-2 sm:w-2.5 sm:h-2.5 bg-red-500 rounded-full ring-2 ring-white animate-pulse"></div>
    </button>

    <!-- Notification Dropdown -->
    <div x-show="notifOpen" x-cloak
         x-transition:enter="transition ease-out duration-200"
         x-transition:enter-start="opacity-0 scale-95 translate-y-2"
         x-transition:enter-end="opacity-100 scale-100 translate-y-0"
         x-transition:leave="transition ease-in duration-150"
         x-transition:leave-start="opacity-100 scale-100 translate-y-0"
         x-transition:leave-end="opacity-0 scale-95 translate-y-2"
         class="absolute right-0 top-[110%] w-[280px] sm:w-[320px] bg-white border border-slate-200 rounded-2xl shadow-xl overflow-hidden z-[60] origin-top-right">
        
        <div class="px-4 py-3 border-b border-slate-100 bg-slate-50 flex justify-between items-center">
            <span class="text-xs font-bold text-slate-800 uppercase tracking-wider">Notifications</span>
            <span class="text-[10px] font-bold bg-blue-100 text-blue-700 px-2 py-0.5 rounded-full" x-text="count + ' New'"></span>
        </div>

        <div class="max-h-[360px] overflow-y-auto custom-scrollbar">
            <template x-if="alerts.length === 0">
                <div class="px-4 py-8 text-center text-slate-500">
                    <svg class="w-10 h-10 mx-auto text-slate-300 mb-2" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" />
                    </svg>
                    <p class="text-[13px] font-medium">All caught up!</p>
                </div>
            </template>
            <template x-for="alert in alerts" :key="alert.id">
                <div class="border-b border-slate-50 last:border-0 hover:bg-slate-50 transition-colors">
                    <div class="px-4 py-3 flex gap-3 items-start cursor-pointer" @click="alert.expanded = !alert.expanded">
                        <div class="w-8 h-8 rounded-full flex items-center justify-center flex-shrink-0 mt-0.5"
                             :class="'bg-' + alert.color + '-100 text-' + alert.color + '-600'">
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" :d="alert.icon"/>
                            </svg>
                        </div>
                        <div class="flex-1 min-w-0">
                            <h4 class="text-[12px] font-bold text-slate-800 leading-tight mb-1" x-text="alert.label"></h4>
                            <p class="text-[11px] text-slate-500 font-medium">
                                <span class="font-bold" :class="'text-' + alert.color + '-600'" x-text="alert.count"></span> 
                                <span x-text="alert.subtitle || 'Items'"></span>
                            </p>
                        </div>
                        <svg class="w-4 h-4 text-slate-400 transform transition-transform" :class="alert.expanded ? 'rotate-180' : ''" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                        </svg>
                    </div>
                    
                    <div x-show="alert.expanded" x-collapse>
                        <div class="bg-slate-50/50 px-4 py-2 border-t border-slate-50">
                            <div class="space-y-1" x-html="renderOrderNumbers(alert)"></div>
                        </div>
                    </div>
                </div>
            </template>
        </div>
    </div>

    <!-- Audio Player (Base64 Beep) -->
    <audio id="notification-sound" src="data:audio/mp3;base64,//NExAAAAANIAAAAAExBTUUzLjEwMKqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqq//NExAAAAANIAAAAAExBTUUzLjEwMKqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqq" preload="auto" loop></audio>
</div>

<script>
    function alertSystem() {
        return {
            count: 0,
            alerts: [],
            lastCount: 0,
            init() {
                this.check();
                setInterval(() => {
                    this.check();
                }, 45000);
            },
            renderOrderNumbers(alert) {
                if (!alert || !alert.order_numbers || alert.order_numbers.length === 0) return '';
                return alert.order_numbers.map((n, i) => {
                    let text = typeof n === 'object' ? n.number : n;
                    let url = typeof n === 'object' && n.url ? n.url : '#';
                    let clickable = url !== '#' ? 'cursor:pointer; hover:bg-slate-200' : '';
                    let onClick = url !== '#' ? `onclick="window.location.href='${url}'"` : '';
                    
                    return `<div ${onClick} class="flex items-center gap-2 px-2 py-1.5 rounded-lg transition-colors ${clickable ? 'hover:bg-slate-200' : ''}">
                                <span class="text-[10px] text-slate-400 w-4 text-right">${i+1}.</span>
                                <span class="text-[11px] font-bold text-blue-700">${text}</span>
                            </div>`;
                }).join('');
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
                    // Retain expanded state
                    let newAlerts = data.alerts || [];
                    newAlerts = newAlerts.map(na => {
                        let existing = this.alerts.find(a => a.id === na.id);
                        na.expanded = existing ? existing.expanded : false;
                        return na;
                    });
                    this.alerts = newAlerts;
                    this.count = data.count || 0;

                    if (this.count > 0 && this.count > this.lastCount) {
                        this.playSound();
                    } else if (this.count === 0) {
                        this.stopSound();
                    }
                    this.lastCount = this.count;
                })
                .catch(error => console.error('Error fetching alerts:', error));
            },
            playSound() {
                const audio = document.getElementById('notification-sound');
                if (audio) {
                    audio.play().catch(e => console.log('Audio play failed:', e));
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
