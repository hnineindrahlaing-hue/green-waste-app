<?php
session_start();
if (isset($_SESSION['user_id'])) { header('Location: dashboard.php'); exit; }
require_once 'includes/db.php';

$error = ''; $success = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name    = trim($_POST['name']    ?? '');
    $email   = trim($_POST['email']   ?? '');
    $phone   = trim($_POST['phone']   ?? '');
    $address = trim($_POST['address'] ?? '');
    $pass    = $_POST['password'] ?? '';
    $pass2   = $_POST['password2'] ?? '';

    if ($pass !== $pass2) { $error = 'Passwords do not match.'; }
    elseif (strlen($pass) < 6) { $error = 'Password must be at least 6 characters.'; }
    else {
        $db = getDB();
        $stmt = $db->prepare("SELECT id FROM users WHERE email = ?");
        $stmt->execute([$email]);
        if ($stmt->fetch()) { $error = 'Email already registered.'; }
        else {
            $hash = password_hash($pass, PASSWORD_DEFAULT);
            $db->prepare("INSERT INTO users (name,email,phone,password,address) VALUES (?,?,?,?,?)")
               ->execute([$name, $email, $phone, $hash, $address]);
            $success = 'Account created! You can now log in.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register — GreenWaste</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
<div class="login-wrapper">
    <div class="login-card fade-in" style="max-width:480px">
        <div class="login-logo"><i class="fa-solid fa-leaf"></i></div>
        <h4 class="text-center fw-bold mb-1" style="color:var(--green-dark)">Create Account</h4>
        <p class="text-center text-muted small mb-4">Join the Green Waste community</p>

        <?php if ($error): ?>
            <div class="alert alert-danger py-2 small"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>
        <?php if ($success): ?>
            <div class="alert alert-success py-2 small"><?= htmlspecialchars($success) ?></div>
            <a href="login.php" class="btn btn-green w-100 mb-3">Go to Login</a>
        <?php else: ?>
        <form method="POST">
            <div class="mb-3">
                <label class="form-label fw-semibold">Full Name</label>
                <input type="text" name="name" class="form-control" placeholder="Ko Aung" required>
            </div>
            <div class="mb-3">
                <label class="form-label fw-semibold">Email</label>
                <input type="email" name="email" class="form-control" placeholder="you@example.com" required>
            </div>
            <div class="mb-3">
                <label class="form-label fw-semibold">Phone</label>
                <input type="text" name="phone" class="form-control" placeholder="+95 9XXXXXXXXX">
            </div>
            <div class="mb-3">
                <label class="form-label fw-semibold">Address</label>
                <input type="text" name="address" class="form-control" placeholder="Township, City">
            </div>
            <div class="mb-3">
                <label class="form-label fw-semibold">Password</label>
                <input type="password" name="password" class="form-control" placeholder="Min. 6 characters" required>
            </div>
            <div class="mb-4">
                <label class="form-label fw-semibold">Confirm Password</label>
                <input type="password" name="password2" class="form-control" placeholder="Re-enter password" required>
            </div>
            <button type="submit" class="btn btn-green w-100 py-2 fw-semibold">
                <i class="fa-solid fa-user-plus me-2"></i>Register
            </button>
        </form>
        <?php endif; ?>
        <div class="text-center mt-3 small">
            Already have an account? <a href="login.php" style="color:var(--green-dark)">Sign In</a>
        </div>
    </div>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
