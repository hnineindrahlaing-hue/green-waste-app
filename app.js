/* Green Waste App — main JS */

// ── Tracking map ──────────────────────────────────────────────
function initTrackingMap(trucks, userLat, userLng) {
    const map = L.map('track-map').setView([userLat, userLng], 13);

    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        attribution: '© <a href="https://openstreetmap.org">OpenStreetMap</a>'
    }).addTo(map);

    // User marker
    const userIcon = L.divIcon({
        html: '<div style="background:#1a6b3c;width:16px;height:16px;border-radius:50%;border:3px solid #fff;box-shadow:0 0 6px rgba(0,0,0,.4)"></div>',
        iconSize: [16, 16], iconAnchor: [8, 8]
    });
    L.marker([userLat, userLng], {icon: userIcon})
     .addTo(map)
     .bindPopup('<strong>Your Location</strong>');

    // Truck markers
    trucks.forEach(truck => {
        const color = truck.status === 'collecting' ? '#e67e22' :
                      truck.status === 'returning'  ? '#6c757d' : '#17a085';
        const icon = L.divIcon({
            html: `<div style="background:${color};color:#fff;padding:4px 8px;border-radius:8px;
                   font-size:11px;font-weight:700;white-space:nowrap;box-shadow:0 2px 8px rgba(0,0,0,.3)">
                   🚛 ${truck.truck_id}</div>`,
            iconSize: [80, 28], iconAnchor: [40, 14]
        });
        const eta = truck.eta_minutes > 0 ? `ETA: ~${truck.eta_minutes} min` : 'Not in your area';
        L.marker([truck.lat, truck.lng], {icon})
         .addTo(map)
         .bindPopup(`<strong>${truck.truck_id}</strong><br>
                     Driver: ${truck.driver_name}<br>
                     Status: ${truck.status}<br>
                     Zone: ${truck.zone}<br>
                     <em>${eta}</em>`);
    });

    return map;
}

// ── Report map ────────────────────────────────────────────────
function initReportMap(lat, lng) {
    const map = L.map('report-map').setView([lat, lng], 14);
    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        attribution: '© OpenStreetMap'
    }).addTo(map);

    let marker = L.marker([lat, lng], {draggable: true}).addTo(map);
    document.getElementById('report-lat').value = lat;
    document.getElementById('report-lng').value = lng;

    marker.on('dragend', function (e) {
        const pos = marker.getLatLng();
        document.getElementById('report-lat').value = pos.lat.toFixed(6);
        document.getElementById('report-lng').value = pos.lng.toFixed(6);
    });

    map.on('click', function (e) {
        marker.setLatLng(e.latlng);
        document.getElementById('report-lat').value = e.latlng.lat.toFixed(6);
        document.getElementById('report-lng').value = e.latlng.lng.toFixed(6);
    });
    return map;
}

// ── Hotspot / schedule map ────────────────────────────────────
function initHotspotMap(reports) {
    const map = L.map('map').setView([16.8661, 96.1951], 12);
    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        attribution: '© OpenStreetMap'
    }).addTo(map);

    reports.forEach(r => {
        if (!r.lat || !r.lng) return;
        const color = r.severity === 'high' ? '#dc3545' :
                      r.severity === 'medium' ? '#ffc107' : '#28a745';
        L.circleMarker([r.lat, r.lng], {
            radius: r.severity === 'high' ? 14 : 10,
            fillColor: color, color: '#fff',
            weight: 2, opacity: 1, fillOpacity: .75
        }).addTo(map)
          .bindPopup(`<strong>${r.severity.toUpperCase()} severity</strong><br>${r.description}<br>
                      <em>Status: ${r.status}</em>`);
    });
    return map;
}

// ── Photo upload preview ──────────────────────────────────────
function setupPhotoUpload() {
    const input = document.getElementById('photo-input');
    const zone  = document.getElementById('upload-zone');
    const preview = document.getElementById('photo-preview');
    if (!input || !zone) return;

    zone.addEventListener('click', () => input.click());
    zone.addEventListener('dragover', e => { e.preventDefault(); zone.classList.add('dragover'); });
    zone.addEventListener('dragleave', () => zone.classList.remove('dragover'));
    zone.addEventListener('drop', e => {
        e.preventDefault();
        zone.classList.remove('dragover');
        input.files = e.dataTransfer.files;
        showPreview(input.files[0]);
    });
    input.addEventListener('change', () => showPreview(input.files[0]));

    function showPreview(file) {
        if (!file) return;
        const reader = new FileReader();
        reader.onload = e => {
            preview.src = e.target.result;
            preview.classList.remove('d-none');
            zone.querySelector('.upload-hint').classList.add('d-none');
        };
        reader.readAsDataURL(file);
    }
}

// ── Toast helper ─────────────────────────────────────────────
function showToast(msg, type = 'success') {
    const el = document.createElement('div');
    el.className = `toast align-items-center text-bg-${type} border-0 show position-fixed bottom-0 end-0 m-3`;
    el.style.zIndex = 9999;
    el.innerHTML = `<div class="d-flex"><div class="toast-body">${msg}</div>
        <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button></div>`;
    document.body.appendChild(el);
    setTimeout(() => el.remove(), 4000);
}

// ── Geolocation helper ────────────────────────────────────────
function getLocation(callback) {
    if (!navigator.geolocation) {
        callback(16.8661, 96.1951); // Default: Yangon
        return;
    }
    navigator.geolocation.getCurrentPosition(
        pos => callback(pos.coords.latitude, pos.coords.longitude),
        ()  => callback(16.8661, 96.1951)
    );
}

// ── Auto-refresh truck positions ──────────────────────────────
function startTruckRefresh(callback, interval = 30000) {
    setInterval(callback, interval);
}

document.addEventListener('DOMContentLoaded', function () {
    setupPhotoUpload();

    // Confirm deletes
    document.querySelectorAll('[data-confirm]').forEach(el => {
        el.addEventListener('click', e => {
            if (!confirm(el.dataset.confirm)) e.preventDefault();
        });
    });
});
