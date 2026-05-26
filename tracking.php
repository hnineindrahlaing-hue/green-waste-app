<?php
session_start();
require_once dirname(__DIR__) . '/includes/db.php';
require_once dirname(__DIR__) . '/includes/auth.php';

header('Content-Type: application/json');
if (!isLoggedIn()) { echo json_encode(['error'=>'Unauthorized']); exit; }

$db     = getDB();
$action = $_GET['action'] ?? $_POST['action'] ?? 'positions';

if ($action === 'positions') {
    $trucks = $db->query("SELECT * FROM truck_locations ORDER BY truck_id")->fetchAll();
    echo json_encode(['trucks' => $trucks, 'updated_at' => date('Y-m-d H:i:s')]);
    exit;
}

if ($action === 'update' && isAdmin()) {
    $id     = intval($_POST['id']     ?? 0);
    $lat    = floatval($_POST['lat']  ?? 0);
    $lng    = floatval($_POST['lng']  ?? 0);
    $status = $_POST['status']        ?? 'idle';
    $eta    = intval($_POST['eta']    ?? 0);
    $zone   = trim($_POST['zone']     ?? '');
    $db->prepare("UPDATE truck_locations SET lat=?,lng=?,status=?,eta_minutes=?,zone=?,updated_at=NOW() WHERE id=?")
       ->execute([$lat, $lng, $status, $eta, $zone, $id]);
    echo json_encode(['success' => true]);
    exit;
}

echo json_encode(['error' => 'Unknown action']);
