<?php
session_start();
require_once '../config/db.php';

if (!isset($_SESSION['UserType']) || $_SESSION['UserType'] !== 'Staff' || ($_SESSION['Role'] ?? '') !== 'Admin') {
    header("Location: login.php?error=" . urlencode("Access denied. Administrator privileges required."));
    exit;
}

$search = trim($_GET['search'] ?? '');
$userTypeFilter = $_GET['user_type'] ?? '';
$statusFilter = $_GET['status'] ?? '';

$sql = "
    SELECT l.*, 
           CONCAT(COALESCE(st.FirstName, s.FirstName, ''), ' ', COALESCE(st.LastName, s.LastName, '')) AS FullName,
           s.RoleID,
           p.ProgramName
    FROM login l
    LEFT JOIN staff s ON l.StaffID = s.StaffID
    LEFT JOIN student st ON l.StudentID = st.StudentID
    LEFT JOIN program p ON st.ProgramID = p.ProgramID
    WHERE 1=1
";
$params = [];

if (!empty($search)) {
    $sql .= " AND (l.Username LIKE ? OR s.FirstName LIKE ? OR s.LastName LIKE ? OR st.FirstName LIKE ? OR st.LastName LIKE ?)";
    $term = "%$search%";
    $params = array_merge($params, [$term, $term, $term, $term, $term]);
}

if (!empty($userTypeFilter)) {
    $sql .= " AND l.UserType = ?";
    $params[] = $userTypeFilter;
}

if (!empty($statusFilter)) {
    $sql .= " AND l.Status = ?";
    $params[] = $statusFilter;
}

$sql .= " ORDER BY l.LoginID DESC LIMIT 50";
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$users = $stmt->fetchAll();
?>
<?php include 'includes/header.php'; ?>
<?php include 'includes/sidebar.php'; ?>
<div class="pl-72 flex flex-col min-h-screen">
<?php include 'includes/topbar.php'; ?>
<main class="w-full pt-16 flex-1 px-gutter py-space-lg">
    <div class="flex flex-col w-full gap-space-lg">

        <!-- Header -->
        <section class="flex flex-col gap-space-md">
            <div class="flex flex-col md:flex-row md:items-end justify-between gap-space-sm bg-surface-container-lowest p-space-lg rounded-xl shadow-[0_1px_8px_rgba(0,0,0,0.04)] border border-outline-variant/30">
                <div class="flex flex-col gap-space-xs">
                    <div class="flex items-center gap-space-xs text-primary font-label-md text-label-md uppercase tracking-wider font-semibold">
                        <i class="fa-solid fa-users-gear text-[18px]"></i>
                        <span>Admin Module</span>
                    </div>
                    <h1 class="font-headline-lg text-headline-lg text-on-surface tracking-tight">User Login Management</h1>
                    <p class="font-body-md text-body-md text-tertiary">
                        Manage credentials, account active status, and password resets for Staff and Students.
                    </p>
                </div>
            </div>

            <?php if (!empty($_GET['msg'])): ?>
            <div class="p-3 bg-emerald-50 border border-emerald-200 text-emerald-800 rounded-lg text-sm flex items-center gap-2">
                <i class="fa-solid fa-circle-check text-emerald-600 text-[20px]"></i>
                <span><?= htmlspecialchars($_GET['msg']) ?></span>
            </div>
            <?php endif; ?>
            <?php if (!empty($_GET['error'])): ?>
            <div class="p-3 bg-error-container border border-error/30 text-on-error-container rounded-lg text-sm flex items-center gap-2">
                <i class="fa-solid fa-circle-exclamation text-error text-[20px]"></i>
                <span><?= htmlspecialchars($_GET['error']) ?></span>
            </div>
            <?php endif; ?>
        </section>

        <!-- Filters & Search Bar -->
        <section class="bg-surface-container-lowest p-space-md rounded-xl shadow-[0_1px_8px_rgba(0,0,0,0.04)] border border-outline-variant/30">
            <form method="GET" class="flex flex-wrap items-center justify-between gap-space-sm">
                <div class="flex flex-wrap items-center gap-space-sm flex-1">
                    <div class="relative min-w-[240px] flex-1">
                        <i class="fa-solid fa-magnifying-glass absolute left-3 top-1/2 -translate-y-1/2 text-tertiary text-[18px]"></i>
                        <input type="text" name="search" value="<?= htmlspecialchars($search) ?>" placeholder="Search by username or name..." 
                               class="w-full h-10 pl-9 pr-3 rounded-lg bg-surface-container-low border border-surface-container focus:outline-none focus:border-primary text-sm"/>
                    </div>
                    <select name="user_type" class="h-10 px-3 rounded-lg bg-surface-container-low border border-surface-container text-sm text-on-surface">
                        <option value="">All User Types</option>
                        <option value="Staff" <?= $userTypeFilter === 'Staff' ? 'selected' : '' ?>>Staff</option>
                        <option value="Student" <?= $userTypeFilter === 'Student' ? 'selected' : '' ?>>Student</option>
                    </select>
                    <select name="status" class="h-10 px-3 rounded-lg bg-surface-container-low border border-surface-container text-sm text-on-surface">
                        <option value="">All Statuses</option>
                        <option value="Active" <?= $statusFilter === 'Active' ? 'selected' : '' ?>>Active</option>
                        <option value="Inactive" <?= $statusFilter === 'Inactive' ? 'selected' : '' ?>>Inactive</option>
                    </select>
                </div>
                <div class="flex items-center gap-space-xs">
                    <button type="submit" class="px-4 py-2 bg-primary text-on-primary rounded-lg text-sm font-semibold hover:bg-primary-container transition-colors">
                        Filter
                    </button>
                    <a href="admin_users.php" class="px-3 py-2 bg-surface-container hover:bg-surface-container-high rounded-lg text-sm text-tertiary">
                        Reset
                    </a>
                </div>
            </form>
        </section>

        <!-- User Accounts Table -->
        <section class="bg-surface-container-lowest rounded-xl shadow-[0_1px_8px_rgba(0,0,0,0.04)] border border-outline-variant/30 overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse text-sm">
                    <thead>
                        <tr class="bg-surface-container-low text-tertiary text-xs uppercase font-label-sm border-b border-surface-container">
                            <th class="px-space-md py-space-sm">Username</th>
                            <th class="px-space-md py-space-sm">Linked Name</th>
                            <th class="px-space-md py-space-sm">User Type / Role</th>
                            <th class="px-space-md py-space-sm">Status</th>
                            <th class="px-space-md py-space-sm">Last Login</th>
                            <th class="px-space-md py-space-sm text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-surface-container">
                        <?php foreach ($users as $u): ?>
                        <tr class="hover:bg-surface-container-low/50 transition-colors">
                            <td class="px-space-md py-space-sm font-semibold text-primary">
                                <?= htmlspecialchars($u['Username']) ?>
                            </td>
                            <td class="px-space-md py-space-sm text-on-surface font-medium">
                                <?= htmlspecialchars(trim($u['FullName']) ?: 'System Account') ?>
                                <?php if (!empty($u['ProgramName'])): ?>
                                    <span class="block text-xs text-tertiary font-normal"><?= htmlspecialchars($u['ProgramName']) ?></span>
                                <?php endif; ?>
                            </td>
                            <td class="px-space-md py-space-sm">
                                <span class="px-2.5 py-0.5 rounded-full text-xs font-semibold <?= $u['UserType'] === 'Staff' ? 'bg-secondary/15 text-secondary' : 'bg-primary/10 text-primary' ?>">
                                    <?= htmlspecialchars($u['UserType']) ?> <?= !empty($u['RoleID']) ? '('.$u['RoleID'].')' : '' ?>
                                </span>
                            </td>
                            <td class="px-space-md py-space-sm">
                                <?php if ($u['Status'] === 'Active'): ?>
                                    <span class="inline-flex items-center gap-1 text-xs text-emerald-700 bg-emerald-50 px-2.5 py-0.5 rounded-full font-medium">
                                        <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span> Active
                                    </span>
                                <?php else: ?>
                                    <span class="inline-flex items-center gap-1 text-xs text-tertiary bg-surface-container px-2.5 py-0.5 rounded-full font-medium">
                                        <span class="w-1.5 h-1.5 rounded-full bg-tertiary"></span> Inactive
                                    </span>
                                <?php endif; ?>
                            </td>
                            <td class="px-space-md py-space-sm text-xs text-tertiary">
                                <?= !empty($u['LastLogin']) ? date('M d, Y H:i', strtotime($u['LastLogin'])) : 'Never' ?>
                            </td>
                            <td class="px-space-md py-space-sm text-right">
                                <div class="flex items-center justify-end gap-2">
                                    <!-- Toggle Active/Inactive Status -->
                                    <form method="POST" action="../controllers/admin_action_controller.php" class="inline">
                                        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token'] ?? '') ?>">
                                        <input type="hidden" name="action" value="toggle_user_status">
                                        <input type="hidden" name="login_id" value="<?= $u['LoginID'] ?>">
                                        <input type="hidden" name="status" value="<?= $u['Status'] === 'Active' ? 'Inactive' : 'Active' ?>">
                                        <button type="submit" class="px-2.5 py-1 text-xs font-medium rounded border <?= $u['Status'] === 'Active' ? 'border-tertiary/30 text-tertiary hover:bg-surface-container' : 'border-emerald-300 text-emerald-700 hover:bg-emerald-50' ?>">
                                            <?= $u['Status'] === 'Active' ? 'Deactivate' : 'Activate' ?>
                                        </button>
                                    </form>

                                    <!-- Reset Password Quick Action -->
                                    <button onclick="openResetModal(<?= $u['LoginID'] ?>, '<?= htmlspecialchars($u['Username']) ?>')" class="px-2.5 py-1 text-xs font-medium bg-surface-container hover:bg-surface-container-high rounded text-on-surface transition-colors">
                                        Reset Password
                                    </button>
                                </div>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                        <?php if (empty($users)): ?>
                        <tr>
                            <td colspan="6" class="px-space-md py-8 text-center text-tertiary">No matching user accounts found.</td>
                        </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </section>

    </div>
</main>

<!-- Reset Password Modal -->
<div id="resetModal" class="fixed inset-0 z-50 bg-black/40 backdrop-blur-sm hidden flex items-center justify-center p-4">
    <div class="bg-surface-container-lowest rounded-2xl shadow-xl max-w-md w-full p-6 border border-outline-variant/40">
        <div class="flex items-center justify-between pb-3 border-b border-surface-container">
            <h3 class="font-headline-sm text-headline-sm text-on-surface">Reset Password</h3>
            <button onclick="closeResetModal()" class="text-tertiary hover:text-on-surface">
                <i class="fa-solid fa-xmark"></i>
            </button>
        </div>
        <form method="POST" action="../controllers/admin_action_controller.php" class="flex flex-col gap-4 mt-4">
            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token'] ?? '') ?>">
            <input type="hidden" name="action" value="reset_password">
            <input type="hidden" id="reset_login_id" name="login_id" value="">

            <div>
                <label class="block text-xs font-semibold text-tertiary uppercase mb-1">Target Account</label>
                <input type="text" id="reset_username" disabled class="w-full h-10 px-3 rounded-lg bg-surface-container text-sm font-mono text-on-surface"/>
            </div>

            <div>
                <label class="block text-xs font-semibold text-tertiary uppercase mb-1">New Temporary Password</label>
                <input type="text" name="new_password" value="password123" required class="w-full h-10 px-3 rounded-lg bg-surface-container-low border border-surface-container text-sm font-mono focus:outline-none focus:border-primary"/>
            </div>

            <div class="flex items-center gap-2">
                <input type="checkbox" id="must_change" name="must_change" value="1" checked class="w-4 h-4 rounded text-primary">
                <label for="must_change" class="text-xs text-on-surface font-medium">Require user to change password on next login</label>
            </div>

            <div class="flex items-center justify-end gap-2 mt-4 pt-3 border-t border-surface-container">
                <button type="button" onclick="closeResetModal()" class="px-4 py-2 rounded-lg bg-surface-container text-sm font-medium text-tertiary hover:bg-surface-container-high">Cancel</button>
                <button type="submit" class="px-4 py-2 rounded-lg bg-primary text-on-primary text-sm font-semibold hover:bg-primary-container">Confirm Reset</button>
            </div>
        </form>
    </div>
</div>

<script>
function openResetModal(loginId, username) {
    document.getElementById('reset_login_id').value = loginId;
    document.getElementById('reset_username').value = username;
    document.getElementById('resetModal').classList.remove('hidden');
}
function closeResetModal() {
    document.getElementById('resetModal').classList.add('hidden');
}
</script>

<?php include 'includes/footer.php'; ?>