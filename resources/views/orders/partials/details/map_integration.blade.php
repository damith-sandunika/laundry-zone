        @if($order->is_delivery_order && $order->deliveryAppointment)
            <!-- Maps Section -->
            <div x-data="orderMaps()" style="margin-top:24px;">
                <h2 class="ph-title" style="margin-bottom:12px; font-size:16px;">Logistics Overview</h2>
                <div style="display:flex;gap:24px;flex-wrap:wrap;">

                    @if($order->deliveryAppointment->latitude && $order->deliveryAppointment->longitude)
                        <!-- Pickup Map -->
                        <div
                            style="flex:1;min-width:300px;background:white;border-radius:12px;border:1px solid #e2e8f0;overflow:hidden;box-shadow:0 1px 4px rgba(0,0,0,0.02);">
                            <div
                                style="padding:12px 16px;background:#fff7ed;border-bottom:1px solid #ffedd5;display:flex;align-items:center;gap:10px;">
                                <div
                                    style="width:28px;height:28px;background:#ffedd5;color:#ea580c;border-radius:50%;display:flex;align-items:center;justify-content:center;font-size:12px;">
                                    <i class="fa-solid fa-arrow-up"></i></div>
                                <div>
                                    <div
                                        style="font-size:10px;font-weight:900;color:#ea580c;text-transform:uppercase;letter-spacing:0.05em;">
                                        Pickup Route</div>
                                    <div style="font-size:12px;font-weight:800;color:#431407;margin-top:2px;">Customer to Branch
                                    </div>
                                </div>
                            </div>
                            <div id="pickup-route-badge"
                                style="display:none;padding:8px 14px;background:linear-gradient(135deg,#ea580c,#f97316);font-size:12px;font-weight:800;color:white;text-align:center;letter-spacing:0.03em;">
                                <i class="fa-solid fa-route" style="margin-right:6px;"></i>
                                <span id="pickup-route-text">Calculating route...</span>
                            </div>
                            <div id="pickup-route-map" style="width:100%;height:300px;background:#f8fafc;z-index:1;"></div>
                        </div>
                    @endif

                    @if($order->deliveryAppointment->delivery_lat && $order->deliveryAppointment->delivery_lng)
                        <!-- Delivery Map -->
                        <div
                            style="flex:1;min-width:300px;background:white;border-radius:12px;border:1px solid #e2e8f0;overflow:hidden;box-shadow:0 1px 4px rgba(0,0,0,0.02);">
                            <div
                                style="padding:12px 16px;background:#f0fdf4;border-bottom:1px solid #dcfce7;display:flex;align-items:center;gap:10px;">
                                <div
                                    style="width:28px;height:28px;background:#dcfce7;color:#16a34a;border-radius:50%;display:flex;align-items:center;justify-content:center;font-size:12px;">
                                    <i class="fa-solid fa-arrow-down"></i></div>
                                <div>
                                    <div
                                        style="font-size:10px;font-weight:900;color:#16a34a;text-transform:uppercase;letter-spacing:0.05em;">
                                        Delivery Route</div>
                                    <div style="font-size:12px;font-weight:800;color:#166534;margin-top:2px;">Branch to Customer
                                    </div>
                                </div>
                            </div>
                            <div id="route-distance-badge"
                                style="display:none;padding:8px 14px;background:linear-gradient(135deg,#1d4ed8,#4f46e5);font-size:12px;font-weight:800;color:white;text-align:center;letter-spacing:0.03em;">
                                <i class="fa-solid fa-route" style="margin-right:6px;"></i>
                                <span id="route-distance-text">Calculating route...</span>
                            </div>
                            <div id="delivery-route-map" style="width:100%;height:300px;background:#f8fafc;z-index:1;"></div>
                        </div>
                    @endif

                </div>
            </div>

            <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
            <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
            <script>
                document.addEventListener('alpine:init', () => {
                    Alpine.data('orderMaps', () => ({
                        pickupMap: null,
                        deliveryMap: null,
                        pLat: {{ $order->deliveryAppointment->latitude ?? 'null' }},
                        pLng: {{ $order->deliveryAppointment->longitude ?? 'null' }},
                        dLat: {{ $order->deliveryAppointment->delivery_lat ?? 'null' }},
                        dLng: {{ $order->deliveryAppointment->delivery_lng ?? 'null' }},
                        bLat: {{ $order->branch->latitude ?? 'null' }},
                        bLng: {{ $order->branch->longitude ?? 'null' }},
                        bName: '{{ addslashes($order->branch->name ?? "Branch") }}',
                        init() {
                            setTimeout(() => this.initMaps(), 300);
                        },
                        initMaps() {
                            let branchIcon = L.divIcon({
                                html: '<div style="background:#0f172a;color:white;width:32px;height:32px;border-radius:50%;display:flex;align-items:center;justify-content:center;border:3px solid white;box-shadow:0 4px 6px rgba(0,0,0,0.2);"><i class="fa-solid fa-store" style="font-size:12px;"></i></div>',
                                className: '', iconSize: [32, 32], iconAnchor: [16, 16], popupAnchor: [0, -16]
                            });
                            let pickupIcon = L.divIcon({
                                html: '<div style="background:#ea580c;color:white;width:32px;height:32px;border-radius:50%;display:flex;align-items:center;justify-content:center;border:3px solid white;box-shadow:0 4px 6px rgba(0,0,0,0.2);"><i class="fa-solid fa-arrow-up" style="font-size:12px;"></i></div>',
                                className: '', iconSize: [32, 32], iconAnchor: [16, 32], popupAnchor: [0, -32]
                            });
                            let customerIcon = L.divIcon({
                                html: '<div style="background:#16a34a;color:white;width:32px;height:32px;border-radius:50%;display:flex;align-items:center;justify-content:center;border:3px solid white;box-shadow:0 4px 6px rgba(0,0,0,0.2);"><i class="fa-solid fa-house" style="font-size:12px;"></i></div>',
                                className: '', iconSize: [32, 32], iconAnchor: [16, 32], popupAnchor: [0, -32]
                            });

                            if (this.pLat && this.pLng && document.getElementById('pickup-route-map')) {
                                this.pickupMap = L.map('pickup-route-map').setView([this.pLat, this.pLng], 13);
                                L.tileLayer('https://{s}.basemaps.cartocdn.com/rastertiles/voyager/{z}/{x}/{y}{r}.png', {
                                    attribution: '&copy; OpenStreetMap'
                                }).addTo(this.pickupMap);

                                L.marker([this.pLat, this.pLng], { icon: pickupIcon }).bindPopup('<b>Customer Pickup</b>').addTo(this.pickupMap);

                                if (this.bLat && this.bLng) {
                                    L.marker([this.bLat, this.bLng], { icon: branchIcon }).bindPopup('<b>' + this.bName + '</b>').addTo(this.pickupMap);

                                    document.getElementById('pickup-route-badge').style.display = 'block';
                                    fetch(`https://router.project-osrm.org/route/v1/driving/${this.bLng},${this.bLat};${this.pLng},${this.pLat}?overview=full&geometries=geojson`)
                                        .then(r => r.json())
                                        .then(data => {
                                            if (data.routes && data.routes.length > 0) {
                                                let route = data.routes[0];
                                                let distKm = (route.distance / 1000).toFixed(1);
                                                let durationMin = Math.ceil(route.duration / 60);
                                                let routeCoords = route.geometry.coordinates.map(c => [c[1], c[0]]);
                                                L.polyline(routeCoords, { color: '#ea580c', weight: 4, opacity: 0.8 }).addTo(this.pickupMap);
                                                document.getElementById('pickup-route-text').innerHTML = `${this.bName} &rarr; Pickup &nbsp;|&nbsp; ${distKm} km &nbsp;|&nbsp; ~${durationMin} min`;
                                                this.pickupMap.fitBounds(L.latLngBounds(routeCoords), { padding: [25, 25] });
                                            }
                                        }).catch(() => document.getElementById('pickup-route-text').textContent = 'Route unavailable');
                                }
                            }

                            if (this.dLat && this.dLng && document.getElementById('delivery-route-map')) {
                                this.deliveryMap = L.map('delivery-route-map').setView([this.dLat, this.dLng], 13);
                                L.tileLayer('https://{s}.basemaps.cartocdn.com/rastertiles/voyager/{z}/{x}/{y}{r}.png', {
                                    attribution: '&copy; OpenStreetMap'
                                }).addTo(this.deliveryMap);

                                L.marker([this.dLat, this.dLng], { icon: customerIcon }).bindPopup('<b>Customer Delivery</b>').addTo(this.deliveryMap);

                                if (this.bLat && this.bLng) {
                                    L.marker([this.bLat, this.bLng], { icon: branchIcon }).bindPopup('<b>' + this.bName + '</b>').addTo(this.deliveryMap);

                                    document.getElementById('route-distance-badge').style.display = 'block';
                                    fetch(`https://router.project-osrm.org/route/v1/driving/${this.bLng},${this.bLat};${this.dLng},${this.dLat}?overview=full&geometries=geojson`)
                                        .then(r => r.json())
                                        .then(data => {
                                            if (data.routes && data.routes.length > 0) {
                                                let route = data.routes[0];
                                                let distKm = (route.distance / 1000).toFixed(1);
                                                let durationMin = Math.ceil(route.duration / 60);
                                                let routeCoords = route.geometry.coordinates.map(c => [c[1], c[0]]);
                                                L.polyline(routeCoords, { color: '#4f46e5', weight: 4, opacity: 0.8 }).addTo(this.deliveryMap);
                                                document.getElementById('route-distance-text').innerHTML = `${this.bName} &rarr; Customer &nbsp;|&nbsp; ${distKm} km &nbsp;|&nbsp; ~${durationMin} min`;
                                                this.deliveryMap.fitBounds(L.latLngBounds(routeCoords), { padding: [25, 25] });
                                            }
                                        }).catch(() => document.getElementById('route-distance-text').textContent = 'Route unavailable');
                                }
                            }
                        }
                    }));
                });
            </script>
        @endif
