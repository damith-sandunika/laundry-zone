<?php
$file = 'resources/views/delivery/index.blade.php';
$content = file_get_contents($file);

// Replace the entire x-data div and its inline object, up to the closing `}">`
// We will replace everything from `<div x-data="{` down to `}">`
$pattern = '/<div x-data="\{\s*currentTab: \'pickups\',.*?\}">/s';
$content = preg_replace($pattern, '<div x-data="deliveryOrders()">', $content);

// Remove the existing view buttons if they exist
$pattern1 = '/<button type="button" @click="openPreview[^>]+><i class="fa-solid fa-eye"><\/i><\/button>\s*/';
$content = preg_replace($pattern1, '', $content);

// Inject View buttons exactly where we want them
$search = '<div style="display:inline-flex;gap:6px;align-items:center;">';
$replace = '<div style="display:inline-flex;gap:6px;align-items:center;">
                                        <button type="button" @click="openPreview($event, {{ $appt->id }}, {{ json_encode([\'customer\' => $appt->customer, \'order\' => $appt->order, \'branch\' => $appt->branch, \'pickup_lat\' => $appt->latitude, \'pickup_lng\' => $appt->longitude, \'delivery_lat\' => $appt->delivery_lat, \'delivery_lng\' => $appt->delivery_lng, \'pickup_address\' => $appt->pickup_address, \'delivery_address\' => $appt->delivery_address, \'notes\' => $appt->special_notes ?? \'\']) }})" style="padding:6px;background:#f8fafc;border:1px solid #e2e8f0;border-radius:8px;color:#3b82f6;cursor:pointer;" title="Preview Order"><i class="fa-solid fa-eye"></i></button>';

$content = str_replace($search, $replace, $content);

$modalHtml = <<<HTML
        <!-- "?"? Preview Modal "?"? -->
        <div x-cloak x-show="showPreviewModal" class="fixed inset-0 z-[70] flex items-center justify-center bg-slate-900/60 backdrop-blur-[6px] p-4">
            <div style="background:white;border-radius:24px;box-shadow:0 25px 50px rgba(0,0,0,0.25);width:100%;max-width:800px;overflow:hidden;display:flex;flex-direction:column;max-height:90vh;" @click.away="showPreviewModal = false">
                <div style="padding:20px 24px;border-bottom:1px solid #f1f5f9;display:flex;justify-content:space-between;align-items:center;background:#f8fafc;">
                    <div>
                        <h3 style="font-size:18px;font-weight:900;color:#0f172a;margin:0;">Order Preview</h3>
                        <p style="font-size:12px;color:#64748b;margin:2px 0 0;font-weight:600;">Customer and Route Details</p>
                    </div>
                    <button @click="showPreviewModal = false" style="background:none;border:none;cursor:pointer;color:#64748b;font-size:20px;"><i class="fa-solid fa-xmark"></i></button>
                </div>
                
                <div style="padding:24px;overflow-y:auto;display:grid;grid-template-columns:1fr 1fr;gap:24px;" class="md:grid-cols-2 grid-cols-1">
                    
                    <div style="display:flex;flex-direction:column;gap:16px;">
                        <!-- Customer Details -->
                        <div style="background:#f8fafc;padding:16px;border-radius:12px;border:1px solid #e2e8f0;">
                            <h4 style="font-size:12px;font-weight:800;color:#334155;text-transform:uppercase;margin:0 0 12px 0;letter-spacing:0.05em;"><i class="fa-solid fa-user text-blue-500 mr-2"></i>Customer Info</h4>
                            <div style="font-size:14px;font-weight:700;color:#0f172a;" x-text="previewData?.customer?.name || 'Unknown'"></div>
                            <div style="font-size:12px;color:#475569;margin-top:2px;" x-text="previewData?.customer?.contact_phone || previewData?.customer?.phone || ''"></div>
                            
                            <div style="margin-top:12px;padding-top:12px;border-top:1px dashed #cbd5e1;">
                                <div style="font-size:11px;font-weight:800;color:#64748b;margin-bottom:4px;">Pickup Address</div>
                                <div style="font-size:12px;font-weight:600;color:#334155;" x-text="previewData?.pickup_address || 'Not Provided'"></div>
                            </div>
                            
                            <div style="margin-top:12px;padding-top:12px;border-top:1px dashed #cbd5e1;" x-show="previewData?.delivery_address && previewData?.delivery_address !== previewData?.pickup_address">
                                <div style="font-size:11px;font-weight:800;color:#64748b;margin-bottom:4px;">Delivery Address</div>
                                <div style="font-size:12px;font-weight:600;color:#334155;" x-text="previewData?.delivery_address || 'Same as Pickup'"></div>
                            </div>
                        </div>

                        <!-- Order Details -->
                        <div style="background:#f8fafc;padding:16px;border-radius:12px;border:1px solid #e2e8f0;">
                            <h4 style="font-size:12px;font-weight:800;color:#334155;text-transform:uppercase;margin:0 0 12px 0;letter-spacing:0.05em;"><i class="fa-solid fa-shirt text-blue-500 mr-2"></i>Order Summary</h4>
                            
                            <template x-if="!previewData?.order?.items?.length">
                                <div style="font-size:12px;color:#64748b;font-weight:500;">No items found or order not yet created.</div>
                            </template>
                            
                            <template x-if="previewData?.order?.items?.length">
                                <div style="display:flex;flex-direction:column;gap:8px;">
                                    <template x-for="item in previewData.order.items">
                                        <div style="display:flex;justify-content:space-between;font-size:12px;font-weight:600;color:#334155;">
                                            <span x-text="item.service_type_name + ' (x' + item.qty + ')'"></span>
                                            <span x-text="'LKR ' + item.total_price"></span>
                                        </div>
                                    </template>
                                    <div style="display:flex;justify-content:space-between;font-size:13px;font-weight:800;color:#0f172a;margin-top:8px;padding-top:8px;border-top:1px solid #cbd5e1;">
                                        <span>Total</span>
                                        <span x-text="'LKR ' + previewData.order.total_amount"></span>
                                    </div>
                                </div>
                            </template>
                        </div>
                        
                        <template x-if="previewData?.notes">
                            <div style="background:#fef2f2;padding:12px;border-radius:12px;border:1px solid #fca5a5;">
                                <div style="font-size:11px;font-weight:800;color:#b91c1c;margin-bottom:4px;text-transform:uppercase;"><i class="fa-solid fa-triangle-exclamation mr-1"></i> Special Notes</div>
                                <div style="font-size:13px;font-weight:700;color:#991b1b;" x-text="previewData.notes"></div>
                            </div>
                        </template>

                    </div>

                    <!-- Map Preview -->
                    <div style="display:flex;flex-direction:column;gap:12px;height:100%;">
                        <div style="font-size:12px;font-weight:800;color:#334155;text-transform:uppercase;letter-spacing:0.05em;"><i class="fa-solid fa-map-location-dot text-blue-500 mr-2"></i>Route Map</div>
                        <div id="admin_preview_map" style="width:100%;height:350px;border-radius:12px;border:2px solid #e2e8f0;background:#f1f5f9;z-index:1;"></div>
                    </div>
                </div>
            </div>
        </div>

    </div>

    <!-- Alpine Data for this Page -->
    <script>
        document.addEventListener('alpine:init', () => {
            Alpine.data('deliveryOrders', () => ({
                currentTab: 'pickups',
                showCashModal: false,
                cashData: null,
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
                },
                
                showPreviewModal: false,
                previewData: null,
                previewMap: null,
                previewRouteLayer: null,
                openPreview(e, apptId, data) {
                    this.previewData = data;
                    this.showPreviewModal = true;
                    
                    setTimeout(() => {
                        this.initPreviewMap();
                    }, 300);
                },
                initPreviewMap() {
                    if (this.previewMap) {
                        this.previewMap.invalidateSize();
                        this.previewMap.remove();
                    }
                    
                    if (!this.previewData.branch || !this.previewData.branch.lat || !this.previewData.branch.lng) {
                        return; // Branch has no coords
                    }
                    
                    const centerLat = parseFloat(this.previewData.branch.lat);
                    const centerLng = parseFloat(this.previewData.branch.lng);
                    
                    this.previewMap = L.map('admin_preview_map', { zoomControl: false }).setView([centerLat, centerLng], 12);
                    L.tileLayer('https://{s}.basemaps.cartocdn.com/light_all/{z}/{x}/{y}{r}.png', {
                        attribution: '365 Laundry'
                    }).addTo(this.previewMap);
                    
                    L.control.zoom({ position: 'bottomright' }).addTo(this.previewMap);
                    this.previewRouteLayer = L.layerGroup().addTo(this.previewMap);
                    
                    // Draw Branch
                    const branchIcon = L.divIcon({
                        className: 'custom-div-icon',
                        html: `<div style="background:#1d4ed8;color:white;width:32px;height:32px;display:flex;align-items:center;justify-content:center;border-radius:50%;box-shadow:0 4px 6px -1px rgba(0,0,0,0.1);border:2px solid white;font-size:14px;"><i class="fa-solid fa-store"></i></div>`,
                        iconSize: [32, 32],
                        iconAnchor: [16, 16]
                    });
                    L.marker([centerLat, centerLng], {icon: branchIcon}).addTo(this.previewMap).bindTooltip("Workshop");
                    
                    let pLat = this.previewData.pickup_lat ? parseFloat(this.previewData.pickup_lat) : null;
                    let pLng = this.previewData.pickup_lng ? parseFloat(this.previewData.pickup_lng) : null;
                    let dLat = this.previewData.delivery_lat ? parseFloat(this.previewData.delivery_lat) : pLat;
                    let dLng = this.previewData.delivery_lng ? parseFloat(this.previewData.delivery_lng) : pLng;
                    
                    const locIcon = L.divIcon({
                        className: 'custom-div-icon',
                        html: `<div style="background:#f59e0b;color:white;width:24px;height:24px;display:flex;align-items:center;justify-content:center;border-radius:50%;box-shadow:0 4px 6px -1px rgba(0,0,0,0.1);border:2px solid white;font-size:12px;"><i class="fa-solid fa-user"></i></div>`,
                        iconSize: [24, 24],
                        iconAnchor: [12, 12]
                    });
                    
                    if (pLat && pLng) {
                        L.marker([pLat, pLng], {icon: locIcon}).addTo(this.previewMap).bindTooltip("Pickup Location");
                        
                        // Draw route Branch -> Pickup
                        axios.get(`https://router.project-osrm.org/route/v1/driving/\${centerLng},\${centerLat};\${pLng},\${pLat}?overview=full&geometries=geojson`)
                            .then(res => {
                                if(res.data && res.data.routes && res.data.routes.length > 0) {
                                    L.geoJSON(res.data.routes[0].geometry, {
                                        style: { color: '#f59e0b', weight: 4, opacity: 0.8, dashArray: '5, 10' }
                                    }).addTo(this.previewRouteLayer);
                                }
                            });
                    }
                    
                    if (dLat && dLng && (dLat !== pLat || dLng !== pLng)) {
                        L.marker([dLat, dLng], {icon: locIcon}).addTo(this.previewMap).bindTooltip("Delivery Location");
                        
                        // Draw route Branch -> Delivery
                        axios.get(`https://router.project-osrm.org/route/v1/driving/\${centerLng},\${centerLat};\${dLng},\${dLat}?overview=full&geometries=geojson`)
                            .then(res => {
                                if(res.data && res.data.routes && res.data.routes.length > 0) {
                                    L.geoJSON(res.data.routes[0].geometry, {
                                        style: { color: '#3b82f6', weight: 4, opacity: 0.8, dashArray: '5, 10' }
                                    }).addTo(this.previewRouteLayer);
                                }
                            });
                    }
                }
            }));
        });
    </script>
</x-app-layout>
HTML;

$content = str_replace('    </div>
</x-app-layout>', $modalHtml, $content);

file_put_contents($file, $content);
echo "Done";
?>
