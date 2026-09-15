<?php
session_start();
require_once '../config/db.php';

if (!isset($_SESSION['UserType']) || $_SESSION['UserType'] !== 'Staff' || ($_SESSION['Role'] ?? '') !== 'Admin') {
    header("Location: login.php?error=" . urlencode("Access denied. Administrator privileges required."));
    exit;
}

$moduleFilter = $_GET['module'] ?? '';
$roleFilter = $_GET['role'] ?? '';
$search = trim($_GET['search'] ?? '');

$sql = "SELECT * FROM system_log WHERE 1=1";
$params = [];

if (!empty($moduleFilter)) {
    $sql .= " AND Module = ?";
    $params[] = $moduleFilter;
}

if (!empty($roleFilter)) {
    $sql .= " AND Role = ?";
    $params[] = $roleFilter;
}

if (!empty($search)) {
    $sql .= " AND (User LIKE ? OR Action LIKE ? OR Details LIKE ?)";
    $term = "%$search%";
    $params[] = $term;
    $params[] = $term;
    $params[] = $term;
}

$sql .= " ORDER BY Timestamp DESC LIMIT 100";
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$logs = $stmt->fetchAll();

// Distinct modules for filter
$modules = $pdo->query("SELECT DISTINCT Module FROM system_log ORDER BY Module ASC")->fetchAll(PDO::FETCH_COLUMN);
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
                        <i class="fa-solid fa-receipt text-[18px]"></i>
                        <span>Security & Compliance</span>
                    </div>
                    <h1 class="font-headline-lg text-headline-lg text-on-surface tracking-tight">System Logs & Auditing</h1>
                    <p class="font-body-md text-body-md text-tertiary">
                        Comprehensive transactional audit trail, authentication records, and cross-department activities.
                    </p>
                </div>
            </div>
        </section>

        <!-- Filters -->
        <section class="bg-surface-container-lowest p-space-md rounded-xl shadow-[0_1px_8px_rgba(0,0,0,0.04)] border border-outline-variant/30">
            <form method="GET" class="flex flex-wrap items-center justify-between gap-space-sm">
                <div class="flex flex-wrap items-center gap-space-sm flex-1">
                    <div class="relative min-w-[240px] flex-1">
                        <i class="fa-solid fa-magnifying-glass absolute left-3 top-1/2 -translate-y-1/2 text-tertiary text-[18px]"></i>
                        <input type="text" name="search" value="<?= htmlspecialchars($search) ?>" placeholder="Search user, action, details..." 
                               class="w-full h-10 pl-9 pr-3 rounded-lg bg-surface-container-low border border-surface-container text-sm"/>
                    </div>
                    <select name="module" class="h-10 px-3 rounded-lg bg-surface-container-low border border-surface-container text-sm">
                        <option value="">All Modules</option>
                        <?php foreach($modules as $m): ?>
                            <option value="<?= htmlspecialchars($m) ?>" <?= $moduleFilter === $m ? 'selected' : '' ?>><?= htmlspecialchars($m) ?></option>
                        <?php endforeach; ?>
                    </select>
                    <select name="role" class="h-10 px-3 rounded-lg bg-surface-container-low border border-surface-container text-sm">
                        <option value="">All Roles</option>
                        <option value="Admin" <?= $roleFilter === 'Admin' ? 'selected' : '' ?>>Admin</option>
                        <option value="Registrar" <?= $roleFilter === 'Registrar' ? 'selected' : '' ?>>Registrar</option>
                        <option value="Clinic" <?= $roleFilter === 'Clinic' ? 'selected' : '' ?>>Clinic</option>
                        <option value="Accounting" <?= $roleFilter === 'Accounting' ? 'selected' : '' ?>>Accounting</option>
                        <option value="Student" <?= $roleFilter === 'Student' ? 'selected' : '' ?>>Student</option>
                    </select>
                </div>
                <div class="flex items-center gap-space-xs">
                    <button type="submit" class="px-4 py-2 bg-primary text-on-primary rounded-lg text-sm font-semibold hover:bg-primary-container transition-colors">
                        Apply Filter
                    </button>
                    <a href="admin_logs.php" class="px-3 py-2 bg-surface-container hover:bg-surface-container-high rounded-lg text-sm text-tertiary">
                        Reset
                    </a>
                </div>
            </form>
        </section>

        <!-- Log Table -->
        <section class="bg-surface-container-lowest rounded-xl shadow-[0_1px_8px_rgba(0,0,0,0.04)] border border-outline-variant/30 overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse text-sm">
                    <thead>
                        <tr class="bg-surface-container-low text-tertiary text-xs uppercase font-label-sm border-b border-surface-container">
                            <th class="px-space-md py-space-sm">Log ID</th>
                            <th class="px-space-md py-space-sm">Date & Time</th>
                            <th class="px-space-md py-space-sm">Actor / Role</th>
                            <th class="px-space-md py-space-sm">Target Module</th>
                            <th class="px-space-md py-space-sm">Action Performed</th>
                            <th class="px-space-md py-space-sm">Transaction Details</th>
                            <th class="px-space-md py-space-sm">IP Address</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-surface-container">
                        <?php foreach ($logs as $log): ?>
                        <tr class="hover:bg-surface-container-low/50 transition-colors">
                            <td class="px-space-md py-space-sm font-mono text-xs text-tertiary">
                                #<?= $log['LogID'] ?>
                            </td>
                            <td class="px-space-md py-space-sm text-xs text-on-surface whitespace-nowrap">
                                <?= date('Y-m-d H:i:s', strtotime($log['Timestamp'])) ?>
                            </td>
                            <td class="px-space-md py-space-sm">
                                <span class="font-semibold text-on-surface"><?= htmlspecialchars($log['User']) ?></span>
                                <span class="ml-1 text-[11px] px-2 py-0.5 rounded bg-surface-container font-medium text-tertiary"><?= htmlspecialchars($log['Role']) ?></span>
                            </td>
                            <td class="px-space-md py-space-sm text-xs font-semibold text-secondary">
                                <?= htmlspecialchars($log['Module']) ?>
                            </td>
                            <td class="px-space-md py-space-sm text-xs font-bold text-primary">
                                <?= htmlspecialchars($log['Action']) ?>
                            </td>
                            <td class="px-space-md py-space-sm text-xs text-tertiary max-w-sm break-words">
                                <?= htmlspecialchars($log['Details'] ?? 'N/A') ?>
                            </td>
                            <td class="px-space-md py-space-sm text-xs font-mono text-tertiary">
                                <?= htmlspecialchars($log['IPAddress'] ?? '127.0.0.1') ?>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                        <?php if(empty($logs)): ?>
                        <tr>
                            <td colspan="7" class="px-space-md py-8 text-center text-tertiary">No log events match the query criteria.</td>
                        </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </section>

    </div>
</main>
<?php include 'includes/footer.php'; ?>