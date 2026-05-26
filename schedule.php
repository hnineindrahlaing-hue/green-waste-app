<?php
session_start();
require_once dirname(__DIR__) . '/includes/db.php';
require_once dirname(__DIR__) . '/includes/auth.php';

if (!isLoggedIn()) { header('Location: ../login.php'); exit; }

$db     = getDB();
$action = $_POST['action'] ?? $_GET['action'] ?? '';

if ($action === 'add' && isAdmin()) {
    $zone    = trim($_POST['zone']            ?? '');
    $date    = $_POST['collection_date']      ?? '';
    $time    = $_POST['collection_time']      ?? '';
    $type    = $_POST['waste_type']           ?? 'general';
    $notes   = trim($_POST['notes']           ?? '');
    $status  = 'scheduled';

    if ($zone && $date && $time) {
        $db->prepare("INSERT INTO schedules (zone,collection_date,collection_time,waste_type,status,notes) VALUES (?,?,?,?,?,?)")
           ->execute([$zone, $date, $time, $type, $status, $notes]);
    }
    header('Location: ../schedule.php?added=1');
    exit;
}

if ($action === 'list') {
    header('Content-Type: application/json');
    $month = $_GET['month'] ?? date('Y-m');
    [$y, $m] = explode('-', $month);
    $stmt = $db->prepare("SELECT * FROM schedules WHERE YEAR(collection_date)=? AND MONTH(collection_date)=? ORDER BY collection_date,collection_time");
    $stmt->execute([(int)$y, (int)$m]);
    echo json_encode($stmt->fetchAll());
    exit;
}

header('Location: ../schedule.php');
