<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
<script>
    const CARTO_TILE = 'https://{s}.basemaps.cartocdn.com/rastertiles/voyager/{z}/{x}/{y}{r}.png';
    const CARTO_ATTR = '&copy; <a href="https://www.openstreetmap.org/copyright">OSM</a> &copy; <a href="https://carto.com/">CARTO</a>';
    const DEFAULT_CENTER = [6.9271, 79.8612]; // Colombo
    let addMap = null;
    let addMarker = null;

    function makeBranchIcon() {
        return L.divIcon({
            html: `<div style="transform:translate(-50%,-100%)">
                <div style="width:30px;height:30px;background:#2563eb;border-radius:50%;display:flex;align-items:center;justify-content:center;border:3px solid white;box-shadow:0 2px 10px rgba(37,99,235,0.4);">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="white"><path d="M3 9l9-7 9 7v11a2 2 0 01-2 2H5a2 2 0 01-2-2z"/></svg>
                </div>
                <div style="width:6px;height:6px;background:#2563eb;border-radius:50%;margin:2px auto 0;box-shadow:0 1px 4px rgba(37,99,235,0.3);"></div>
            </div>`,
            className: 'bg-transparent',
            iconSize: [1, 1],
            iconAnchor: [0, 0]
        });
    }

    document.addEventListener('DOMContentLoaded', () => {
        addMap = L.map('addMap', { zoomControl: false }).setView(DEFAULT_CENTER, 13);
        L.tileLayer(CARTO_TILE, { attribution: CARTO_ATTR, subdomains: 'abcd', maxZoom: 20 }).addTo(addMap);
        L.control.zoom({ position: 'bottomright' }).addTo(addMap);

        const oldLat = document.getElementById('add_lat').value;
        const oldLng = document.getElementById('add_lng').value;
        if (oldLat && oldLng) {
            const latlng = [parseFloat(oldLat), parseFloat(oldLng)];
            addMap.setView(latlng, 15);
            addMarker = L.marker(latlng, { icon: makeBranchIcon() }).addTo(addMap);
            document.getElementById('add_latlng_badge').classList.remove('hidden');
            document.getElementById('add_latlng_text').textContent = parseFloat(oldLat).toFixed(5) + ', ' + parseFloat(oldLng).toFixed(5);
        }

        addMap.on('click', (e) => {
            if (addMarker) addMap.removeLayer(addMarker);
            addMarker = L.marker(e.latlng, { icon: makeBranchIcon() }).addTo(addMap);
            document.getElementById('add_lat').value = e.latlng.lat.toFixed(6);
            document.getElementById('add_lng').value = e.latlng.lng.toFixed(6);
            document.getElementById('add_latlng_badge').classList.remove('hidden');
            document.getElementById('add_latlng_text').textContent = e.latlng.lat.toFixed(5) + ', ' + e.latlng.lng.toFixed(5);
        });
    });
</script>
