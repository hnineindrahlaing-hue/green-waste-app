<?php
session_start();
require_once dirname(__DIR__) . '/includes/db.php';
require_once dirname(__DIR__) . '/includes/auth.php';

if (!isLoggedIn()) { header('Location: ../login.php'); exit; }

$db     = getDB();
$action = $_POST['action'] ?? $_GET['action'] ?? '';
$uid    = $_SESSION['user_id'];

if ($action === 'list') {
    $title   = trim($_POST['title']        ?? '');
    $desc    = trim($_POST['description']  ?? '');
    $mat     = $_POST['material_type']     ?? 'other';
    $qty     = floatval($_POST['quantity'] ?? 0);
    $unit    = $_POST['unit']              ?? 'kg';
    $price   = floatval($_POST['price']    ?? 0);
    $location = trim($_POST['location']   ?? '');
    $imgPath  = null;

    if ($title && $qty > 0 && $price > 0) {
        if (!empty($_FILES['image']['name'])) {
            $ext = strtolower(pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION));
            if (in_array($ext, ['jpg','jpeg','png','gif','webp'])) {
                $fname = 'market_' . time() . '_' . uniqid() . '.' . $ext;
                $dest  = dirname(__DIR__) . '/uploads/reports/' . $fname;
                if (move_uploaded_file($_FILES['image']['tmp_name'], $dest)) {
                    $imgPath = 'uploads/reports/' . $fname;
                }
            }
        }
        $db->prepare("INSERT INTO market_listings (seller_id,title,description,material_type,quantity,unit,price,image_path,location) VALUES (?,?,?,?,?,?,?,?,?)")
           ->execute([$uid, $title, $desc, $mat, $qty, $unit, $price, $imgPath, $location]);
    }
    header('Location: ../market.php?listed=1');
    exit;
}

if ($action === 'delete' && isset($_POST['id'])) {
    $id = intval($_POST['id']);
    $db->prepare("DELETE FROM market_listings WHERE id=? AND seller_id=?")->execute([$id, $uid]);
    header('Location: ../market.php');
    exit;
}

if ($action === 'listings') {
    header('Content-Type: application/json');
    $stmt = $db->query("SELECT ml.*,u.name as seller FROM market_listings ml LEFT JOIN users u ON ml.seller_id=u.id WHERE ml.status='available' ORDER BY ml.created_at DESC");
    echo json_encode($stmt->fetchAll());
    exit;
}

header('Location: ../market.php');
