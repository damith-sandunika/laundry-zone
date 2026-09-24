                    workshopMarkers: {}, // { workshopId: { marker, mapType } }

                    addWorkshopMarkersToMap(map, mapKey) {
                        const self = this;
                        this.workshopsArray.forEach(ws => {
                            if (!ws.latitude || !ws.longitude) return;
                            const isNearest = self.nearestWorkshop && self.nearestWorkshop.id === ws.id;
                            const icon = self.makeWorkshopIcon(isNearest);
                            const marker = L.marker([parseFloat(ws.latitude), parseFloat(ws.longitude)], {icon, interactive: false}).addTo(map);
                            const key = mapKey + '_' + ws.id;
                            self.workshopMarkers[key] = { marker, wsId: ws.id, mapKey };
                        });
                    },

                    makeWorkshopIcon(isNearest) {
                        const color = isNearest ? '#2563eb' : '#94a3b8';
                        const opacity = isNearest ? '1' : '0.4';
                        return L.divIcon({
                            html: `<div style="transform:translate(-50%,-100%);opacity:${opacity};transition:all 0.3s;">
                                    <div style="width:32px;height:32px;background:${color};border-radius:50%;display:flex;align-items:center;justify-content:center;border:3px solid white;box-shadow:0 2px 8px rgba(0,0,0,0.25);">
                                        <svg width="16" height="16" viewBox="0 0 24 24" fill="white"><path d="M3 9l9-7 9 7v11a2 2 0 01-2 2H5a2 2 0 01-2-2z"/><polyline points="9 22 9 12 15 12 15 22" fill="white" stroke="white"/></svg>
                                    </div>
                                    ${isNearest ? '<div style="width:8px;height:8px;background:'+color+';border-radius:50%;margin:2px auto 0;box-shadow:0 1px 4px rgba(0,0,0,0.2);"></div>' : '<div style="width:5px;height:5px;background:'+color+';border-radius:50%;margin:2px auto 0;opacity:0.4;"></div>'}
                                   </div>`,
                            className: 'bg-transparent',
                            iconSize: [1, 1],
                            iconAnchor: [0, 0]
                        });
                    },

                    updateWorkshopMarkerStyles() {
                        const self = this;
                        Object.values(this.workshopMarkers).forEach(({ marker, wsId }) => {
                            const isNearest = self.nearestWorkshop && self.nearestWorkshop.id == wsId;
                            marker.setIcon(self.makeWorkshopIcon(isNearest));
                        });
                    },

                    findNearestWorkshop(lat, lng) {
                        if (!this.workshopsArray || this.workshopsArray.length === 0) return null;
                        let nearest = null;
                        let minDist = Infinity;
                        this.workshopsArray.forEach(ws => {
                            if (ws.latitude && ws.longitude) {
                                const d = this.calculateDistance(parseFloat(ws.latitude), parseFloat(ws.longitude), lat, lng);
                                if (d < minDist) {
                                    minDist = d;
                                    nearest = ws;
                                }
                            }
                        });
                        return nearest;
                    },

                    initPickupMap() {
                        if (this.pickupMap) {
                            this.pickupMap.invalidateSize();
                            return;
                        }
                        const defaultCenter = [this.baseLat, this.baseLng];
                        this.pickupMap = L.map('pickup_map', { zoomControl: false }).setView(defaultCenter, 15);

                        // Beautiful tile - CartoDB Positron (clean & light)
                        L.tileLayer('https://{s}.basemaps.cartocdn.com/rastertiles/voyager/{z}/{x}/{y}{r}.png', {
                            attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OSM</a> &copy; <a href="https://carto.com/">CARTO</a>',
                            subdomains: 'abcd',
                            maxZoom: 20
                        }).addTo(this.pickupMap);

                        L.control.zoom({ position: 'bottomright' }).addTo(this.pickupMap);

                        // Add all workshop markers
                        this.addWorkshopMarkersToMap(this.pickupMap, 'pickup');

                        const updatePickupCoords = () => {
                            const center = this.pickupMap.getCenter();
                            document.getElementById('pickup_lat').value = center.lat.toFixed(6);
                            document.getElementById('pickup_lng').value = center.lng.toFixed(6);
                            this.pickupLat = center.lat;
                            this.pickupLng = center.lng;
                            // Update nearest workshop dynamically
                            const nearest = this.findNearestWorkshop(center.lat, center.lng);
                            if (nearest && (!this.nearestWorkshop || this.nearestWorkshop.id != nearest.id)) {
                                this.nearestWorkshop = nearest;
                                this.baseLat = parseFloat(nearest.latitude);
                                this.baseLng = parseFloat(nearest.longitude);
                                this.updateWorkshopMarkerStyles();
                            }
                            this.calculateDeliveryFee();
                        };

                        this.pickupMap.on('move', updatePickupCoords);
                        this.pickupMap.on('moveend', updatePickupCoords);
                        this.pickupMap.on('moveend', () => { if (this.baseLat && this.baseLng) this.drawRoute(this.pickupMap, this.baseLat, this.baseLng, this.pickupLat, this.pickupLng); });
                        updatePickupCoords();
                    },

                    initDeliveryMap() {
                        if (this.deliveryMapInitialized) {
                            this.deliveryMap.invalidateSize();
                            return;
                        }
                        const center = this.pickupMap ? this.pickupMap.getCenter() : [this.baseLat, this.baseLng];
                        this.deliveryMap = L.map('delivery_map', { zoomControl: false }).setView(center, 15);

                        // Same beautiful tile
                        L.tileLayer('https://{s}.basemaps.cartocdn.com/rastertiles/voyager/{z}/{x}/{y}{r}.png', {
                            attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OSM</a> &copy; <a href="https://carto.com/">CARTO</a>',
                            subdomains: 'abcd',
                            maxZoom: 20
                        }).addTo(this.deliveryMap);

                        L.control.zoom({ position: 'bottomright' }).addTo(this.deliveryMap);

                        // Add all workshop markers
                        this.addWorkshopMarkersToMap(this.deliveryMap, 'delivery');

                        const updateDeliveryCoords = () => {
                            const center = this.deliveryMap.getCenter();
                            document.getElementById('delivery_lat').value = center.lat.toFixed(6);
                            document.getElementById('delivery_lng').value = center.lng.toFixed(6);
                            this.deliveryLat = center.lat;
                            this.deliveryLng = center.lng;
                            this.calculateDeliveryFee();
                            if (this.baseLat && this.baseLng) { this.drawRoute(this.deliveryMap, this.baseLat, this.baseLng, this.deliveryLat, this.deliveryLng); }
                        };

                        this.deliveryMap.on('move', updateDeliveryCoords);
                        this.deliveryMap.on('moveend', () => {
                            updateDeliveryCoords();
                            if (this.deliveryLat && this.deliveryLng) {
                                this.drawRoute(this.deliveryMap, this.baseLat, this.baseLng, this.deliveryLat, this.deliveryLng, 'delivery');
                            }
                        });
                        updateDeliveryCoords();
                        this.deliveryMapInitialized = true;
                    },

                    drawRoute(map, startLat, startLng, endLat, endLng) {
                        if (!map.routeLayer) {
                            map.routeLayer = L.layerGroup().addTo(map);
                        }
                        map.routeLayer.clearLayers();
                        if(!startLat || !startLng || !endLat || !endLng) return;
                        
                        window.fetch(`https://router.project-osrm.org/route/v1/driving/${startLng},${startLat};${endLng},${endLat}?overview=full&geometries=geojson`)
                            .then(res => res.json())
                            .then(data => {
                                if(data && data.routes && data.routes.length > 0) {
                                    const route = data.routes[0].geometry;
                                    L.geoJSON(route, {
                                        style: { color: '#2563eb', weight: 4, opacity: 0.8, dashArray: '5, 10' }
                                    }).addTo(map.routeLayer);
                                    
                                    // Fit bounds (Removed to prevent infinite map dragging loop)
                                    // const bounds = L.latLngBounds([
                                    //     [startLat, startLng],
                                    //     [endLat, endLng]
                                    // ]);
                                    // map.fitBounds(bounds, { padding: [50, 50] });
                                }
                            }).catch(err => console.log('OSRM Error', err));
                    },


                    findMe(type) {
                        if ("geolocation" in navigator) {
                            navigator.geolocation.getCurrentPosition(position => {
                                if (type === 'pickup' && this.pickupMap) {
                                    this.pickupMap.setView([position.coords.latitude, position.coords.longitude], 16);
                                }
                            }, error => {
                                alert("Could not get your location. Please check browser permissions.");
                            });
                        }
                    },
                    
