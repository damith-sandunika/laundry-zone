    <script>
        document.addEventListener('alpine:init', () => {
            Alpine.data('deliveryManager', () => ({
                currentTab: 'pickups',
                showCashModal: false,
                cashData: null,
                previewData: null,
                showPreviewModal: false,
                openPreview(data) {
                    this.previewData = typeof data === 'string' ? JSON.parse(data) : data;
                    this.showPreviewModal = true;
                    setTimeout(() => this.initPreviewMap(), 300);
                },
                initPreviewMap() {
                    let pLat = parseFloat(this.previewData.pickup_lat);
                    let pLng = parseFloat(this.previewData.pickup_lng);
                    let dLat = parseFloat(this.previewData.delivery_lat);
                    let dLng = parseFloat(this.previewData.delivery_lng);
                    let bLat = parseFloat(this.previewData.branch?.latitude);
                    let bLng = parseFloat(this.previewData.branch?.longitude);
                    let bName = this.previewData.branch?.name || 'Branch';
                    let isSameLocation = (pLat === dLat && pLng === dLng);
                    let isDifferentDelivery = this.previewData.delivery_preference !== 'pickup_from_branch' &&
                        this.previewData.delivery_preference !== 'pickup_from_store' &&
                        this.previewData.delivery_preference !== 'self_pickup' &&
                        this.previewData.delivery_address &&
                        this.previewData.delivery_address !== this.previewData.pickup_address &&
                        !isSameLocation;

                    // Initialize Pickup Route Map (Branch → Customer Pickup Location)
                    if (pLat && pLng) {
                        setTimeout(() => {
                            let pEl = document.getElementById('pickup-route-map');
                            if (pEl) {
                                // Destroy old map if exists
                                if (this.pickupMap) {
                                    this.pickupMap.remove();
                                    this.pickupMap = null;
                                }
                                this.pickupMap = L.map('pickup-route-map', {zoomControl: true});
                                L.tileLayer('https://{s}.basemaps.cartocdn.com/rastertiles/voyager/{z}/{x}/{y}{r}.png', {maxZoom: 19}).addTo(this.pickupMap);

                                // Customer pickup marker (orange)
                                let pickupIcon = L.divIcon({
                                    html: '<div style="width:32px;height:32px;background:#ea580c;border-radius:50% 50% 50% 0;transform:rotate(-45deg);border:3px solid white;box-shadow:0 2px 8px rgba(234,88,12,0.5);"><div style="width:10px;height:10px;background:white;border-radius:50%;position:absolute;top:50%;left:50%;transform:translate(-50%,-50%);"></div></div>',
                                    className: '', iconSize: [32, 32], iconAnchor: [16, 32]
                                });
                                L.marker([pLat, pLng], {icon: pickupIcon}).bindPopup('<b>📍 Customer Pickup</b><br>' + (this.previewData.pickup_address || '')).addTo(this.pickupMap);

                                if (bLat && bLng) {
                                    // Branch marker (blue)
                                    let branchIcon = L.divIcon({
                                        html: '<div style="width:32px;height:32px;background:#1d4ed8;border-radius:50% 50% 50% 0;transform:rotate(-45deg);border:3px solid white;box-shadow:0 2px 8px rgba(29,78,216,0.5);"><div style="width:10px;height:10px;background:white;border-radius:50%;position:absolute;top:50%;left:50%;transform:translate(-50%,-50%);"></div></div>',
                                        className: '', iconSize: [32, 32], iconAnchor: [16, 32]
                                    });
                                    L.marker([bLat, bLng], {icon: branchIcon}).bindPopup('<b>🏪 ' + bName + '</b>').addTo(this.pickupMap);

                                    // Fit to show both points
                                    let pBounds = L.latLngBounds([[bLat, bLng], [pLat, pLng]]);
                                    this.pickupMap.fitBounds(pBounds, {padding: [30, 30]});

                                    // Show badge
                                    let pBadge = document.getElementById('pickup-route-badge');
                                    if (pBadge) { pBadge.style.display = 'block'; }

                                    // Fetch OSRM route branch → pickup
                                    let osrmPickupUrl = `https://router.project-osrm.org/route/v1/driving/${bLng},${bLat};${pLng},${pLat}?overview=full&geometries=geojson`;
                                    fetch(osrmPickupUrl)
                                        .then(r => r.json())
                                        .then(data => {
                                            if (data.routes && data.routes.length > 0) {
                                                let route = data.routes[0];
                                                let distKm = (route.distance / 1000).toFixed(1);
                                                let durationMin = Math.ceil(route.duration / 60);

                                                // Draw route polyline (orange for pickup)
                                                let routeCoords = route.geometry.coordinates.map(c => [c[1], c[0]]);
                                                L.polyline(routeCoords, {
                                                    color: '#ea580c', weight: 4, opacity: 0.8
                                                }).addTo(this.pickupMap);

                                                // Update badge
                                                let pText = document.getElementById('pickup-route-text');
                                                if (pText) {
                                                    pText.innerHTML = `${bName} → Pickup &nbsp;|&nbsp; 🛣️ ${distKm} km &nbsp;|&nbsp; ⏱️ ~${durationMin} min`;
                                                }

                                                // Re-fit to route
                                                this.pickupMap.fitBounds(L.latLngBounds(routeCoords), {padding: [25, 25]});
                                            }
                                        })
                                        .catch(() => {
                                            let pText = document.getElementById('pickup-route-text');
                                            if (pText) { pText.textContent = 'Route unavailable (offline)'; }
                                        });
                                } else {
                                    // No branch coords — just show pickup point
                                    this.pickupMap.setView([pLat, pLng], 15);
                                }
                                this.pickupMap.invalidateSize();
                            }
                        }, 200);
                    }

                    // Initialize Delivery Route Map (Branch → Customer) if different location
                    if (isDifferentDelivery && dLat && dLng) {
                        setTimeout(() => {
                            let dEl = document.getElementById('delivery-route-map');
                            if (dEl) {
                                // Destroy old map if exists
                                if (this.deliveryMap) {
                                    this.deliveryMap.remove();
                                    this.deliveryMap = null;
                                }
                                this.deliveryMap = L.map('delivery-route-map', {zoomControl: true});
                                L.tileLayer('https://{s}.basemaps.cartocdn.com/rastertiles/voyager/{z}/{x}/{y}{r}.png', {maxZoom: 19}).addTo(this.deliveryMap);

                                // Customer delivery marker (green)
                                let customerIcon = L.divIcon({
                                    html: '<div style="width:32px;height:32px;background:#16a34a;border-radius:50% 50% 50% 0;transform:rotate(-45deg);border:3px solid white;box-shadow:0 2px 8px rgba(22,163,74,0.5);"><div style="width:10px;height:10px;background:white;border-radius:50%;position:absolute;top:50%;left:50%;transform:translate(-50%,-50%);"></div></div>',
                                    className: '', iconSize: [32, 32], iconAnchor: [16, 32]
                                });
                                L.marker([dLat, dLng], {icon: customerIcon}).bindPopup('<b>📍 Customer Location</b><br>' + (this.previewData.delivery_address || '')).addTo(this.deliveryMap);

                                // Branch marker (blue/orange) — if branch coordinates available
                                if (bLat && bLng) {
                                    let branchIcon = L.divIcon({
                                        html: '<div style="width:32px;height:32px;background:#1d4ed8;border-radius:50% 50% 50% 0;transform:rotate(-45deg);border:3px solid white;box-shadow:0 2px 8px rgba(29,78,216,0.5);"><div style="width:10px;height:10px;background:white;border-radius:50%;position:absolute;top:50%;left:50%;transform:translate(-50%,-50%);"></div></div>',
                                        className: '', iconSize: [32, 32], iconAnchor: [16, 32]
                                    });
                                    L.marker([bLat, bLng], {icon: branchIcon}).bindPopup('<b>🏪 ' + bName + '</b>').addTo(this.deliveryMap);

                                    // Fit map to show both points
                                    let bounds = L.latLngBounds([[bLat, bLng], [dLat, dLng]]);
                                    this.deliveryMap.fitBounds(bounds, {padding: [30, 30]});

                                    // Show distance badge
                                    let badge = document.getElementById('route-distance-badge');
                                    if (badge) { badge.style.display = 'block'; }

                                    // Fetch route from OSRM
                                    let osrmUrl = `https://router.project-osrm.org/route/v1/driving/${bLng},${bLat};${dLng},${dLat}?overview=full&geometries=geojson`;
                                    fetch(osrmUrl)
                                        .then(r => r.json())
                                        .then(data => {
                                            if (data.routes && data.routes.length > 0) {
                                                let route = data.routes[0];
                                                let distKm = (route.distance / 1000).toFixed(1);
                                                let durationMin = Math.ceil(route.duration / 60);

                                                // Draw route polyline
                                                let routeCoords = route.geometry.coordinates.map(c => [c[1], c[0]]);
                                                L.polyline(routeCoords, {
                                                    color: '#4f46e5', weight: 4, opacity: 0.8, dashArray: null
                                                }).addTo(this.deliveryMap);

                                                // Update distance badge
                                                let distText = document.getElementById('route-distance-text');
                                                if (distText) {
                                                    distText.innerHTML = `${bName} → Customer &nbsp;|&nbsp; 🛣️ ${distKm} km &nbsp;|&nbsp; ⏱️ ~${durationMin} min`;
                                                }

                                                // Re-fit to route
                                                this.deliveryMap.fitBounds(L.latLngBounds(routeCoords), {padding: [25, 25]});
                                            }
                                        })
                                        .catch(() => {
                                            let distText = document.getElementById('route-distance-text');
                                            if (distText) { distText.textContent = 'Route unavailable (offline)'; }
                                        });
                                } else {
                                    // No branch coords — just show delivery point
                                    this.deliveryMap.setView([dLat, dLng], 15);
                                }
                                this.deliveryMap.invalidateSize();
                            }
                        }, 200);
                    }
                },
                openCashModal(apptId, orderNumber, total, paid, pending) {
                    this.cashData = { id: apptId, orderNumber, total, paid, pending };
                    this.showCashModal = true;
                },
                showRejectModal: false,
                rejectApptId: null,
                rejectReason: '',
                openRejectModal(apptId) {
                    this.rejectApptId = apptId;
                    this.rejectReason = '';
                    this.showRejectModal = true;
                }
            }));
        });
    </script>