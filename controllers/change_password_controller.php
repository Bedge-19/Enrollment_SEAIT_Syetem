<?php
// controllers/change_password_controller.php
session_start();
require_once '../config/db.php';

// Ensure POST request
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: ../views/change_password.php");
    exit;
}

// CSRF check
$csrf = $_POST['csrf_token'] ?? '';
if (!isset($_SESSION['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $csrf)) {
    header("Location: ../views/change_password.php?error=" . urlencode("Invalid security token."));
    exit;
}

// Ensure user is logged in
if (!isset($_SESSION['LoginID'])) {
    header("Location: ../views/login.php");
    exit;
}

$newPassword = $_POST['new_password'] ?? '';
$confirmPassword = $_POST['confirm_password'] ?? '';

if (empty($newPassword) || empty($confirmPassword)) {
    header("Location: ../views/change_password.php?error=" . urlencode("Both fields are required."));
    exit;
}

if (strlen($newPassword) < 8) {
    header("Location: ../views/change_password.php?error=" . urlencode("Password must be at least 8 characters long."));
    exit;
}

if ($newPassword !== $confirmPassword) {
    header("Location: ../views/change_password.php?error=" . urlencode("Passwords do not match."));
    exit;
}

// Hash and update
$hashed = password_hash($newPassword, PASSWORD_DEFAULT);
$stmt = $pdo->prepare("UPDATE login SET PasswordHash = :pass, MustChangePassword = 0 WHERE LoginID = :loginId");
$stmt->execute([
    ':pass' => $hashed,
    ':loginId' => $_SESSION['LoginID']
]);

// Redirect based on UserType
if ($_SESSION['UserType'] === 'Student') {
    header("Location: ../views/student_dashboard.php");
} else {
    header("Location: ../views/staff_dashboard.php");
}
exit;
?>
