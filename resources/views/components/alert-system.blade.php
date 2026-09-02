@props(['playAudio' => true])

<div x-data="alertSystem()" x-init="init()" class="relative mr-1 sm:mr-2 lg:mr-4 flex items-center gap-1.5 sm:gap-2 flex-wrap">
    
    <template x-for="alert in alerts" :key="alert.id">
        <span class="relative flex items-center justify-center w-7 h-7 sm:w-10 sm:h-10 rounded-lg sm:rounded-xl border-2 shadow-sm transition-all animate-pulse cursor-pointer"
           :class="'bg-' + alert.color + '-50/50 border-' + alert.color + '-200 text-' + alert.color + '-700 hover:bg-' + alert.color + '-100'"
           @click="toggleCustomAlert(alert)">
            
            <!-- Heavy Flashing background effect -->
            <span class="absolute inset-0 rounded-lg sm:rounded-xl animate-ping opacity-30" :class="'bg-' + alert.color + '-500'"></span>

            <!-- Small notification dot -->
            <div class="absolute -top-1 -right-1 w-2.5 h-2.5 sm:w-3 sm:h-3 rounded-full ring-2 ring-white z-10 flex items-center justify-center text-[7px] sm:text-[8px] font-black text-white" :class="'bg-' + alert.color + '-600'" x-text="alert.count"></div>
            
            <!-- Icon -->
            <svg class="w-3.5 h-3.5 sm:w-5 sm:h-5 relative z-10" :class="'text-' + alert.color + '-600'" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.2">
                <path stroke-linecap="round" stroke-linejoin="round" :d="alert.icon"/>
            </svg>
        </span>
    </template>

    <!-- Custom Centered Alert Box Container -->
    <template x-teleport="body">
        <div x-show="activeAlert" x-cloak class="fixed inset-0 z-[100] flex items-center justify-center pointer-events-none px-4">
            
            <!-- Overlay background -->
            <div class="absolute inset-0 bg-slate-900/40 backdrop-blur-sm pointer-events-auto"
                 x-show="activeAlert"
                 x-transition.opacity
                 @click="activeAlert = null; clickedAlert = null">
            </div>

            <!-- The Modal Box -->
            <div x-show="activeAlert"
                 x-transition:enter="transition ease-out duration-300"
                 x-transition:enter-start="opacity-0 scale-90 translate-y-4"
                 x-transition:enter-end="opacity-100 scale-100 translate-y-0"
                 x-transition:leave="transition ease-in duration-200"
                 x-transition:leave-start="opacity-100 scale-100 translate-y-0"
                 x-transition:leave-end="opacity-0 scale-90 translate-y-4"
                 class="relative pointer-events-auto bg-white/95 backdrop-blur-2xl border shadow-2xl rounded-2xl p-5 flex flex-col items-center text-center w-[92%] max-w-[320px] z-10"
                 @click.stop
                 :class="'border-' + (activeAlert?.color || 'slate') + '-200'">
                
                <!-- Close Button -->
                <button @click="activeAlert = null; clickedAlert = null" 
                        class="absolute top-3 right-3 p-1.5 rounded-full text-slate-400 hover:text-slate-600 hover:bg-slate-100 transition-colors">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>

                <div class="w-12 h-12 rounded-full flex items-center justify-center mb-3 shadow-sm"
                     :class="'bg-' + (activeAlert?.color || 'slate') + '-100 text-' + (activeAlert?.color || 'slate') + '-600'">
                    <svg class="w-6 h-6 animate-pulse" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" :d="activeAlert?.icon"/>
                    </svg>
                </div>
                <h3 class="text-[13px] font-bold text-slate-800 mb-3 leading-tight px-2" x-text="activeAlert?.label"></h3>

                <!-- Count Badge -->
                <div class="px-4 py-1.5 rounded-lg mb-3 w-full" :class="'bg-' + (activeAlert?.color || 'slate') + '-50'">
                    <p class="text-[13px] font-semibold text-slate-600">
                        <span class="text-[20px] font-black" :class="'text-' + (activeAlert?.color || 'slate') + '-600'" x-text="activeAlert?.count"></span>
                        <span x-text="activeAlert?.subtitle || 'Pending'"></span>
                    </p>
                </div>

                <!-- Order Numbers List -->
                <div x-show="activeAlert?.order_numbers?.length > 0"
                     class="w-full mt-2 rounded-xl border overflow-hidden"
                     :class="'border-' + (activeAlert?.color || 'slate') + '-100'">
                    <div style="max-height:160px;overflow-y:auto;text-align:left;"
                         x-html="renderOrderNumbers(activeAlert)">
                    </div>
                </div>
            </div>
        </div>
    </template>

    <!-- Audio Player (Base64 Beep) -->
    <audio id="notification-sound" src="data:audio/mp3;base64,//NExAAAAANIAAAAAExBTUUzLjEwMKqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqq//NExAAAAANIAAAAAExBTUUzLjEwMKqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqq" preload="auto" loop></audio>
</div>

<script>
    function alertSystem() {
        return {
            count: 0,
            alerts: [],
            lastCount: 0,
            activeAlert: null,
            clickedAlert: null,
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
                    let bg = i % 2 === 0 ? '#ffffff' : '#f8fafc';
                    let clickable = url !== '#' ? 'cursor:pointer; text-decoration:underline;' : '';
                    let onClick = url !== '#' ? `onclick="window.location.href='${url}'"` : '';
                    let hoverClass = url !== '#' ? `onmouseover="this.style.background='#e2e8f0'" onmouseout="this.style.background='${bg}'"` : '';
                    
                    return `<div ${onClick} ${hoverClass} style="display:flex;align-items:center;gap:8px;padding:8px 12px;border-bottom:1px solid #f1f5f9;background:${bg}; ${clickable} transition:background 0.15s;">
                                <span style="font-size:11px;color:#94a3b8;min-width:18px;text-align:right;text-decoration:none;">${i+1}.</span>
                                <span style="font-size:12px;font-weight:800;color:#1e40af;letter-spacing:0.03em;">${text}</span>
                            </div>`;
                }).join('');
            },
            showCustomAlert(alert) {
                if (!this.clickedAlert) {
                    this.activeAlert = alert;
                }
            },
            hideCustomAlert() {
                if (!this.clickedAlert) {
                    this.activeAlert = null;
                }
            },
            toggleCustomAlert(alert) {
                if (this.clickedAlert && this.clickedAlert.id === alert.id) {
                    this.clickedAlert = null;
                    this.activeAlert = null;
                } else {
                    this.clickedAlert = alert;
                    this.activeAlert = alert;
                }
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
                    this.alerts = data.alerts || [];
                    this.count = data.count || 0;
                    
                    // Update active alert data if it's currently showing
                    if (this.activeAlert) {
                        const updated = this.alerts.find(a => a.id === this.activeAlert.id);
                        if (updated) {
                            this.activeAlert = updated;
                            if (this.clickedAlert) this.clickedAlert = updated;
                        } else {
                            this.activeAlert = null;
                            this.clickedAlert = null;
                        }
                    }

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
