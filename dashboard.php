<?php
$pageTitle = 'Dashboard — GreenWaste';
require_once 'includes/db.php';
require_once 'includes/header.php';

$db = getDB();
$uid = $user['id'];

// Stats
$totalSchedules  = $db->query("SELECT COUNT(*) FROM schedules WHERE collection_date >= CURDATE()")->fetchColumn();
$activeTrucks    = $db->query("SELECT COUNT(*) FROM truck_locations WHERE status='collecting'")->fetchColumn();
$openReports     = $db->query("SELECT COUNT(*) FROM reports WHERE status='pending'")->fetchColumn();
$marketListings  = $db->query("SELECT COUNT(*) FROM market_listings WHERE status='available'")->fetchColumn();

// Next collection
$nextSchedule = $db->query("SELECT * FROM schedules WHERE collection_date >= CURDATE() AND status='scheduled' ORDER BY collection_date,collection_time LIMIT 1")->fetch();

// Recent reports
$recentReports = $db->query("SELECT r.*, u.name as reporter FROM reports r LEFT JOIN users u ON r.user_id=u.id ORDER BY r.reported_at DESC LIMIT 5")->fetchAll();

// Upcoming schedules
$upcoming = $db->query("SELECT * FROM schedules WHERE collection_date >= CURDATE() ORDER BY collection_date,collection_time LIMIT 5")->fetchAll();

// Market highlights
$marketHighlights = $db->query("SELECT ml.*, u.name as seller FROM market_listings ml LEFT JOIN users u ON ml.seller_id=u.id WHERE ml.status='available' ORDER BY ml.created_at DESC LIMIT 4")->fetchAll();

$wasteColors = ['general'=>'secondary','recyclable'=>'info','organic'=>'success','hazardous'=>'danger'];
$matIcons = ['plastic'=>'🧴','metal'=>'🔩','paper'=>'📄','glass'=>'🫙','electronics'=>'💻','organic'=>'🍃','other'=>'♻️'];
?>

<div class="container-fluid py-4 px-3 px-md-4 fade-in">

    <!-- Welcome banner -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card" style="background:linear-gradient(135deg,var(--green-dark),var(--teal));color:#fff;border-radius:18px;">
                <div class="card-body d-flex align-items-center justify-content-between flex-wrap gap-3 py-4 px-4">
                    <div>
                        <h4 class="fw-bold mb-1">Welcome back, <?= htmlspecialchars($user['name']) ?> 👋</h4>
                        <p class="mb-0 opacity-75">Here's today's waste management overview — <?= date('l, d M Y') ?></p>
                    </div>
                    <?php if ($nextSchedule): ?>
                    <div class="text-end">
                        <div class="badge bg-white text-success fs-6 px-3 py-2">
                            <i class="fa-solid fa-calendar-check me-1"></i>
                            Next: <?= htmlspecialchars($nextSchedule['zone']) ?> on <?= date('d M', strtotime($nextSchedule['collection_date'])) ?>
                        </div>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <!-- Stat cards -->
    <div class="row g-3 mb-4">
        <div class="col-6 col-lg-3">
            <div class="stat-card bg-green-dark">
                <div class="stat-icon"><i class="fa-solid fa-calendar-check"></i></div>
                <div>
                    <div class="stat-val"><?= $totalSchedules ?></div>
                    <div class="stat-lbl">Upcoming Schedules</div>
                </div>
            </div>
        </div>
        <div class="col-6 col-lg-3">
            <div class="stat-card bg-amber">
                <div class="stat-icon"><i class="fa-solid fa-truck-fast"></i></div>
                <div>
                    <div class="stat-val"><?= $activeTrucks ?></div>
                    <div class="stat-lbl">Active Trucks</div>
                </div>
            </div>
        </div>
        <div class="col-6 col-lg-3">
            <div class="stat-card" style="background:#dc3545;">
                <div class="stat-icon"><i class="fa-solid fa-triangle-exclamation"></i></div>
                <div>
                    <div class="stat-val"><?= $openReports ?></div>
                    <div class="stat-lbl">Open Reports</div>
                </div>
            </div>
        </div>
        <div class="col-6 col-lg-3">
            <div class="stat-card bg-teal">
                <div class="stat-icon"><i class="fa-solid fa-recycle"></i></div>
                <div>
                    <div class="stat-val"><?= $marketListings ?></div>
                    <div class="stat-lbl">Market Listings</div>
                </div>
            </div>
        </div>
    </div>

    <!-- Quick actions -->
    <div class="row g-3 mb-4">
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    <div class="section-header">
                        <div class="sh-icon"><i class="fa-solid fa-bolt"></i></div>
                        <h5>Quick Actions</h5>
                    </div>
                    <div class="d-flex gap-2 flex-wrap">
                        <a href="schedule.php" class="btn btn-green">
                            <i class="fa-solid fa-calendar me-1"></i>View Schedule
                        </a>
                        <a href="tracking.php" class="btn btn-warning text-dark">
                            <i class="fa-solid fa-truck-fast me-1"></i>Track Truck
                        </a>
                        <a href="report.php" class="btn btn-danger">
                            <i class="fa-solid fa-triangle-exclamation me-1"></i>Report Bin
                        </a>
                        <a href="market.php" class="btn btn-outline-green">
                            <i class="fa-solid fa-recycle me-1"></i>Sell Recyclables
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-4">
        <!-- Upcoming schedules -->
        <div class="col-lg-6">
            <div class="card h-100">
                <div class="card-body">
                    <div class="section-header">
                        <div class="sh-icon"><i class="fa-solid fa-calendar-days"></i></div>
                        <h5>Upcoming Collections</h5>
                    </div>
                    <?php foreach ($upcoming as $s): ?>
                    <div class="d-flex align-items-center gap-3 mb-3">
                        <div class="text-center" style="min-width:52px;">
                            <div style="font-size:.7rem;text-transform:uppercase;color:#888;"><?= date('M', strtotime($s['collection_date'])) ?></div>
                            <div style="font-size:1.4rem;font-weight:700;color:var(--green-dark);line-height:1"><?= date('d', strtotime($s['collection_date'])) ?></div>
                        </div>
                        <div class="flex-grow-1">
                            <div class="fw-semibold small"><?= htmlspecialchars($s['zone']) ?></div>
                            <div class="d-flex gap-1 align-items-center">
                                <span class="badge bg-<?= $wasteColors[$s['waste_type']] ?> badge-waste-<?= $s['waste_type'] ?>"><?= ucfirst($s['waste_type']) ?></span>
                                <span class="text-muted" style="font-size:.78rem"><i class="fa-regular fa-clock me-1"></i><?= date('H:i', strtotime($s['collection_time'])) ?></span>
                            </div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                    <a href="schedule.php" class="btn btn-outline-green btn-sm w-100 mt-2">View Full Schedule</a>
                </div>
            </div>
        </div>

        <!-- Recent reports -->
        <div class="col-lg-6">
            <div class="card h-100">
                <div class="card-body">
                    <div class="section-header">
                        <div class="sh-icon"><i class="fa-solid fa-map-location-dot"></i></div>
                        <h5>Recent Reports</h5>
                    </div>
                    <?php foreach ($recentReports as $r): ?>
                    <div class="mb-3 ps-3 severity-<?= $r['severity'] ?>">
                        <div class="small fw-semibold"><?= htmlspecialchars(mb_strimwidth($r['description'], 0, 60, '...')) ?></div>
                        <div class="d-flex gap-2 align-items-center">
                            <span class="badge bg-<?= $r['status']==='pending'?'warning text-dark':($r['status']==='resolved'?'success':'info') ?>"><?= ucfirst($r['status']) ?></span>
                            <span class="text-muted" style="font-size:.75rem"><?= date('d M H:i', strtotime($r['reported_at'])) ?></span>
                        </div>
                    </div>
                    <?php endforeach; ?>
                    <a href="report.php" class="btn btn-outline-danger btn-sm w-100 mt-2">Report an Issue</a>
                </div>
            </div>
        </div>

        <!-- Market highlights -->
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    <div class="section-header">
                        <div class="sh-icon"><i class="fa-solid fa-store"></i></div>
                        <h5>Recycling Market — Latest Listings</h5>
                    </div>
                    <div class="row g-3">
                        <?php foreach ($marketHighlights as $m): ?>
                        <div class="col-6 col-lg-3">
                            <div class="card border market-card">
                                <div class="card-body text-center">
                                    <div class="material-icon mx-auto mb-2" style="background:var(--green-light);">
                                        <?= $matIcons[$m['material_type']] ?? '♻️' ?>
                                    </div>
                                    <div class="fw-semibold small"><?= htmlspecialchars($m['title']) ?></div>
                                    <div class="price-tag"><?= number_format($m['price']) ?> Ks/<?= $m['unit'] ?></div>
                                    <div class="text-muted" style="font-size:.75rem"><?= $m['quantity'] ?> <?= $m['unit'] ?> available</div>
                                </div>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                    <a href="market.php" class="btn btn-outline-green btn-sm mt-3">Browse Full Market</a>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>
