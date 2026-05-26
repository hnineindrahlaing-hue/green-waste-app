<?php
$pageTitle = 'Admin Panel — GreenWaste';
define('ROOT', dirname(__DIR__));
require_once ROOT . '/includes/db.php';
require_once ROOT . '/includes/auth.php';
require_once ROOT . '/includes/header.php';

if (!isAdmin()) { header('Location: ../dashboard.php'); exit; }

$db = getDB();

$totalUsers    = $db->query("SELECT COUNT(*) FROM users WHERE role='resident'")->fetchColumn();
$pendingReports = $db->query("SELECT COUNT(*) FROM reports WHERE status='pending'")->fetchColumn();
$totalSchedules = $db->query("SELECT COUNT(*) FROM schedules")->fetchColumn();
$activeTrucks   = $db->query("SELECT COUNT(*) FROM truck_locations WHERE status='collecting'")->fetchColumn();

$allReports = $db->query("SELECT r.*, u.name as reporter FROM reports r LEFT JOIN users u ON r.user_id=u.id ORDER BY r.reported_at DESC LIMIT 20")->fetchAll();
$allUsers   = $db->query("SELECT * FROM users ORDER BY created_at DESC")->fetchAll();
$trucks     = $db->query("SELECT * FROM truck_locations ORDER BY truck_id")->fetchAll();

// Handle truck update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'update_truck') {
    $db->prepare("UPDATE truck_locations SET status=?,zone=?,eta_minutes=? WHERE id=?")
       ->execute([$_POST['status'], $_POST['zone'], (int)$_POST['eta'], (int)$_POST['truck_id']]);
    header('Location: index.php?updated=1');
    exit;
}

// Handle report status update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'update_report') {
    $db->prepare("UPDATE reports SET status=?,admin_notes=? WHERE id=?")
       ->execute([$_POST['status'], $_POST['notes'], (int)$_POST['report_id']]);
    header('Location: index.php?updated=1');
    exit;
}
?>

<div class="container-fluid py-4 px-3 px-md-4 fade-in">
    <div class="section-header mb-4">
        <div class="sh-icon"><i class="fa-solid fa-shield-halved"></i></div>
        <h5>Admin Dashboard</h5>
    </div>

    <?php if (isset($_GET['updated'])): ?>
    <div class="alert alert-success alert-dismissible fade show"><i class="fa-solid fa-check me-1"></i>Updated successfully.<button class="btn-close" data-bs-dismiss="alert"></button></div>
    <?php endif; ?>

    <!-- Stat cards -->
    <div class="row g-3 mb-4">
        <div class="col-6 col-lg-3">
            <div class="stat-card bg-green-dark">
                <div class="stat-icon"><i class="fa-solid fa-users"></i></div>
                <div><div class="stat-val"><?= $totalUsers ?></div><div class="stat-lbl">Residents</div></div>
            </div>
        </div>
        <div class="col-6 col-lg-3">
            <div class="stat-card" style="background:#dc3545;">
                <div class="stat-icon"><i class="fa-solid fa-circle-exclamation"></i></div>
                <div><div class="stat-val"><?= $pendingReports ?></div><div class="stat-lbl">Pending Reports</div></div>
            </div>
        </div>
        <div class="col-6 col-lg-3">
            <div class="stat-card bg-teal">
                <div class="stat-icon"><i class="fa-solid fa-calendar-check"></i></div>
                <div><div class="stat-val"><?= $totalSchedules ?></div><div class="stat-lbl">Total Schedules</div></div>
            </div>
        </div>
        <div class="col-6 col-lg-3">
            <div class="stat-card bg-amber">
                <div class="stat-icon"><i class="fa-solid fa-truck-fast"></i></div>
                <div><div class="stat-val"><?= $activeTrucks ?></div><div class="stat-lbl">Active Trucks</div></div>
            </div>
        </div>
    </div>

    <div class="row g-4">
        <!-- Reports management -->
        <div class="col-lg-7">
            <div class="card">
                <div class="card-body">
                    <div class="section-header">
                        <div class="sh-icon"><i class="fa-solid fa-map-location-dot"></i></div>
                        <h5>Hotspot Reports</h5>
                    </div>
                    <div style="max-height:400px;overflow-y:auto;">
                        <?php foreach ($allReports as $r): ?>
                        <div class="mb-3 p-3 rounded border severity-<?= $r['severity'] ?>">
                            <div class="d-flex justify-content-between align-items-start">
                                <div class="fw-semibold small"><?= htmlspecialchars(mb_strimwidth($r['description'],0,70,'...')) ?></div>
                                <span class="badge bg-<?= $r['status']==='pending'?'warning text-dark':($r['status']==='resolved'?'success':'info') ?>"><?= ucfirst($r['status']) ?></span>
                            </div>
                            <div class="text-muted small">by <?= htmlspecialchars($r['reporter'] ?? 'Unknown') ?> · <?= date('d M H:i', strtotime($r['reported_at'])) ?></div>
                            <div class="mt-2 d-flex gap-1">
                                <button class="btn btn-sm btn-outline-green" data-bs-toggle="modal" data-bs-target="#reportModal"
                                        data-id="<?= $r['id'] ?>" data-desc="<?= htmlspecialchars($r['description']) ?>" data-status="<?= $r['status'] ?>" data-notes="<?= htmlspecialchars($r['admin_notes'] ?? '') ?>">
                                    <i class="fa-solid fa-pen-to-square me-1"></i>Update
                                </button>
                                <?php if ($r['lat'] && $r['lng']): ?>
                                <a href="https://maps.google.com?q=<?= $r['lat'] ?>,<?= $r['lng'] ?>" target="_blank" class="btn btn-sm btn-outline-secondary">
                                    <i class="fa-solid fa-map-pin me-1"></i>Map
                                </a>
                                <?php endif; ?>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
        </div>

        <!-- Truck management -->
        <div class="col-lg-5">
            <div class="card mb-4">
                <div class="card-body">
                    <div class="section-header">
                        <div class="sh-icon"><i class="fa-solid fa-truck-fast"></i></div>
                        <h5>Fleet Control</h5>
                    </div>
                    <?php foreach ($trucks as $t): ?>
                    <div class="border rounded p-2 mb-2">
                        <div class="d-flex justify-content-between align-items-center">
                            <span class="fw-bold small"><?= htmlspecialchars($t['truck_id']) ?> — <?= htmlspecialchars($t['driver_name']) ?></span>
                            <span class="badge bg-<?= $t['status']==='collecting'?'warning text-dark':($t['status']==='returning'?'secondary':'success') ?>"><?= ucfirst($t['status']) ?></span>
                        </div>
                        <div class="text-muted small mb-2">Zone: <?= htmlspecialchars($t['zone']) ?></div>
                        <form method="POST" class="row g-1">
                            <input type="hidden" name="action" value="update_truck">
                            <input type="hidden" name="truck_id" value="<?= $t['id'] ?>">
                            <div class="col-5">
                                <select name="status" class="form-select form-select-sm">
                                    <?php foreach (['idle','collecting','returning'] as $s): ?>
                                    <option value="<?= $s ?>" <?= $t['status']===$s?'selected':'' ?>><?= ucfirst($s) ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-4">
                                <input type="number" name="eta" class="form-control form-control-sm" value="<?= $t['eta_minutes'] ?>" placeholder="ETA min">
                            </div>
                            <div class="col-3">
                                <button type="submit" class="btn btn-green btn-sm w-100">Save</button>
                            </div>
                            <div class="col-12">
                                <input type="text" name="zone" class="form-control form-control-sm" value="<?= htmlspecialchars($t['zone']) ?>" placeholder="Zone">
                            </div>
                        </form>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>

            <!-- Users -->
            <div class="card">
                <div class="card-body">
                    <div class="section-header">
                        <div class="sh-icon"><i class="fa-solid fa-users"></i></div>
                        <h5>Users</h5>
                    </div>
                    <div style="max-height:250px;overflow-y:auto;">
                        <?php foreach ($allUsers as $u): ?>
                        <div class="d-flex align-items-center gap-2 mb-2">
                            <div style="width:34px;height:34px;border-radius:50%;background:var(--green-light);display:flex;align-items:center;justify-content:center;font-weight:700;color:var(--green-dark)">
                                <?= strtoupper(substr($u['name'],0,1)) ?>
                            </div>
                            <div class="flex-grow-1">
                                <div class="small fw-semibold"><?= htmlspecialchars($u['name']) ?></div>
                                <div class="text-muted" style="font-size:.72rem"><?= htmlspecialchars($u['email']) ?></div>
                            </div>
                            <span class="badge bg-<?= $u['role']==='admin'?'danger':'light text-dark' ?>"><?= $u['role'] ?></span>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Report modal -->
<div class="modal fade" id="reportModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="POST">
                <input type="hidden" name="action" value="update_report">
                <input type="hidden" name="report_id" id="modal-report-id">
                <div class="modal-header" style="background:var(--green-dark);color:#fff;">
                    <h5 class="modal-title">Update Report</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <p class="small text-muted" id="modal-report-desc"></p>
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Status</label>
                        <select name="status" id="modal-report-status" class="form-select">
                            <option value="pending">Pending</option>
                            <option value="reviewed">Reviewed</option>
                            <option value="resolved">Resolved</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Admin Notes</label>
                        <textarea name="notes" id="modal-report-notes" class="form-control" rows="3" placeholder="Internal notes..."></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-green">Update</button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php
$extraScripts = '<script>
document.getElementById("reportModal")?.addEventListener("show.bs.modal", function(e) {
    const btn = e.relatedTarget;
    document.getElementById("modal-report-id").value = btn.dataset.id;
    document.getElementById("modal-report-desc").textContent = btn.dataset.desc;
    document.getElementById("modal-report-status").value = btn.dataset.status;
    document.getElementById("modal-report-notes").value = btn.dataset.notes;
});
</script>';
require_once ROOT . '/includes/footer.php';
?>
