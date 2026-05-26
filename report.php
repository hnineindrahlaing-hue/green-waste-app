<?php
session_start();
require_once dirname(__DIR__) . '/includes/db.php';
require_once dirname(__DIR__) . '/includes/auth.php';

header('Content-Type: application/json');
if (!isLoggedIn()) { echo json_encode(['error'=>'Unauthorized']); exit; }

$db     = getDB();
$action = $_GET['action'] ?? 'hotspots';

if ($action === 'hotspots') {
    $stmt = $db->query("SELECT id,lat,lng,severity,description,status,reported_at FROM reports WHERE lat IS NOT NULL ORDER BY reported_at DESC LIMIT 100");
    echo json_encode($stmt->fetchAll());
    exit;
}

if ($action === 'my' && isLoggedIn()) {
    $uid = $_SESSION['user_id'];
    $stmt = $db->prepare("SELECT * FROM reports WHERE user_id=? ORDER BY reported_at DESC");
    $stmt->execute([$uid]);
    echo json_encode($stmt->fetchAll());
    exit;
}

echo json_encode(['error' => 'Unknown action']);
