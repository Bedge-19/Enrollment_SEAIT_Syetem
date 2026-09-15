<?php
// views/change_password.php
require_once '../config/db.php';

// Check if user is logged in
if (!isset($_SESSION['LoginID'])) {
    header("Location: login.php");
    exit;
}

// Optional: check if they actually need to change password
$stmt = $pdo->prepare("SELECT MustChangePassword FROM login WHERE LoginID = :loginId");
$stmt->execute([':loginId' => $_SESSION['LoginID']]);
$user = $stmt->fetch();
if (!$user || $user['MustChangePassword'] != 1) {
    if ($_SESSION['UserType'] === 'Student') {
        header("Location: student_dashboard.php");
    } else {
        header("Location: staff_dashboard.php");
    }
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Change Password - Introllment</title>
    <!-- Use local Tailwind if available, otherwise CDN for now -->
    <script src="../assets/tailwindcss.js"></script>
    <script>
        function disableButton() {
            const btn = document.getElementById('submitBtn');
            btn.innerHTML = 'Updating...';
            btn.disabled = true;
            btn.classList.add('opacity-50', 'cursor-not-allowed');
            return true; // allow form submission to proceed
        }
    </script>
</head>
<body class="bg-gray-100 flex items-center justify-center min-h-screen">

<div class="bg-white p-8 rounded shadow-md w-full max-w-md">
    <h2 class="text-2xl font-bold mb-4 text-center">Change Default Password</h2>
    <p class="text-sm text-gray-600 mb-6 text-center">For security reasons, you must change your password before proceeding.</p>

    <?php if (isset($_GET['error'])): ?>
        <div class="bg-red-100 text-red-700 p-2 rounded mb-4 text-sm">
            <?php echo htmlspecialchars($_GET['error']); ?>
        </div>
    <?php endif; ?>

    <form action="../controllers/change_password_controller.php" method="POST" onsubmit="return disableButton()">
        <!-- CSRF Token -->
        <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token'] ?? ''); ?>">

        <div class="mb-4">
            <label class="block text-gray-700 font-bold mb-2">New Password</label>
            <input type="password" name="new_password" required minlength="8"
                   class="w-full border rounded px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500">
        </div>
        <div class="mb-6">
            <label class="block text-gray-700 font-bold mb-2">Confirm New Password</label>
            <input type="password" name="confirm_password" required minlength="8"
                   class="w-full border rounded px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500">
        </div>

        <button type="submit" id="submitBtn" class="w-full bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 rounded focus:outline-none">
            Update Password
        </button>
    </form>
</div>

</body>
</html>
