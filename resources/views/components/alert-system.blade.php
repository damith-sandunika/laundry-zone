@props(['playAudio' => true])

<div x-data="alertSystem()" x-init="init()" class="relative mr-2 sm:mr-4 flex items-center">
    
    <!-- Individual Alert Icons -->
    <template x-for="alert in alerts" :key="alert.id">
        <div class="relative">
            <button @click="toggleAlert(alert.id)" @click.away="closeAlert(alert.id)" class="relative p-2 rounded-full hover:bg-slate-100 transition-colors focus:outline-none group">
                <div class="w-8 h-8 rounded-full flex items-center justify-center flex-shrink-0 transition-colors"
                     :class="'bg-' + alert.color + '-100 text-' + alert.color + '-600 group-hover:bg-' + alert.color + '-200'">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" :d="alert.icon" />
                    </svg>
                </div>
                <!-- Badge -->
                <div x-show="alert.count > 0" x-cloak class="absolute top-1.5 right-1.5 w-2.5 h-2.5 rounded-full ring-2 ring-white animate-pulse shadow-sm" :class="'bg-' + alert.color + '-500'"></div>
            </button>

            <!-- Dropdown for this specific alert -->
            <div x-show="alert.open" x-cloak
                 x-transition:enter="transition ease-out duration-200"
                 x-transition:enter-start="opacity-0 scale-95 translate-y-2"
                 x-transition:enter-end="opacity-100 scale-100 translate-y-0"
                 x-transition:leave="transition ease-in duration-150"
                 x-transition:leave-start="opacity-100 scale-100 translate-y-0"
                 x-transition:leave-end="opacity-0 scale-95 translate-y-2"
                 class="absolute right-0 top-[110%] w-[280px] sm:w-[320px] bg-white border border-slate-200 rounded-2xl shadow-xl overflow-hidden z-[60] origin-top-right">
                
                <div class="px-4 py-3 border-b border-slate-100 bg-slate-50 flex justify-between items-center">
                    <span class="text-xs font-bold text-slate-800 tracking-tight leading-tight" x-text="alert.label"></span>
                    <span class="text-[10px] font-bold px-2 py-0.5 rounded-full shrink-0" :class="'bg-' + alert.color + '-100 text-' + alert.color + '-700'" x-text="alert.count + ' New'"></span>
                </div>

                <div class="max-h-[360px] overflow-y-auto custom-scrollbar p-2 bg-slate-50/50">
                    <div class="space-y-1" x-html="renderOrderNumbers(alert)"></div>
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
            seenAlertKeys: JSON.parse(localStorage.getItem('laundry_seen_alerts') || '[]'),
            init() {
                this.check();
                setInterval(() => {
                    this.check();
                }, 120000);
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
                    // Map new alerts but keep open state
                    let newAlerts = data.alerts || [];
                    let newlyArrived = [];
                    
                    newAlerts = newAlerts.map(na => {
                        let existing = this.alerts.find(a => a.id === na.id);
                        na.open = existing ? existing.open : false;
                        
                        if (na.order_numbers && na.order_numbers.length > 0) {
                            na.order_numbers.forEach(n => {
                                let text = typeof n === 'object' ? n.number : n;
                                let url = typeof n === 'object' && n.url ? n.url : '#';
                                let itemKey = typeof n === 'object' && n.id ? n.id : text;
                                let key = na.id + '_' + itemKey;
                                if (!this.seenAlertKeys.includes(key)) {
                                    this.seenAlertKeys.push(key);
                                    newlyArrived.push({ label: na.label, text: text, url: url });
                                }
                            });
                        }
                        return na;
                    });
                    
                    if (newlyArrived.length > 0) {
                        localStorage.setItem('laundry_seen_alerts', JSON.stringify(this.seenAlertKeys));
                        
                        if (window.Swal) {
                            let htmlContent = newlyArrived.map(arr => `
                                <div style="margin-bottom:10px;padding:12px;background:#f8fafc;border:1px solid #e2e8f0;border-radius:10px;text-align:left;">
                                    <span style="display:block;font-size:11px;font-weight:800;color:#64748b;text-transform:uppercase;letter-spacing:0.05em;margin-bottom:4px;">${arr.label}</span>
                                    <a href="${arr.url}" style="font-size:15px;font-weight:900;color:#3b82f6;text-decoration:none;">${arr.text}</a>
                                </div>
                            `).join('');
                            
                            Swal.fire({
                                title: '<span style="font-size:20px;font-weight:900;color:#0f172a;">🔔 New Alert Received!</span>',
                                html: htmlContent,
                                showConfirmButton: true,
                                confirmButtonText: 'Dismiss',
                                confirmButtonColor: '#334155',
                                backdrop: `rgba(15,23,42,0.6)`,
                                customClass: {
                                    popup: 'rounded-2xl shadow-2xl'
                                }
                            });
                        }
                    }

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
            },
            toggleAlert(id) {
                let alert = this.alerts.find(a => a.id === id);
                if (alert) {
                    // Close others
                    this.alerts.forEach(a => { if(a.id !== id) a.open = false; });
                    alert.open = !alert.open;
                }
            },
            closeAlert(id) {
                let alert = this.alerts.find(a => a.id === id);
                if (alert) alert.open = false;
            }
        }
    }
</script>


