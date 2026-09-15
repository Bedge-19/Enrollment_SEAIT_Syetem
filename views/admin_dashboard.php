<?php
session_start();
require_once '../config/db.php';

if (!isset($_SESSION['UserType']) || $_SESSION['UserType'] !== 'Staff' || ($_SESSION['Role'] ?? '') !== 'Admin') {
    header("Location: login.php?error=" . urlencode("Access denied. Administrator privileges required."));
    exit;
}

// 1. Core KPIs
$totalStudents = (int)$pdo->query("SELECT COUNT(*) FROM student")->fetchColumn();
$totalStaff = (int)$pdo->query("SELECT COUNT(*) FROM staff")->fetchColumn();
$totalPrograms = (int)$pdo->query("SELECT COUNT(*) FROM program")->fetchColumn();
$totalUsers = (int)$pdo->query("SELECT COUNT(*) FROM login")->fetchColumn();
$activeLogins = (int)$pdo->query("SELECT COUNT(*) FROM login WHERE Status = 'Active'")->fetchColumn();
$totalRevenue = (float)$pdo->query("SELECT SUM(Amount) FROM payment WHERE PaymentStatus = 'Paid'")->fetchColumn();

// 2. Intake Funnel Stages
$stageAdmissions = (int)$pdo->query("SELECT COUNT(*) FROM admission WHERE Status = 'Approved'")->fetchColumn();
$stageIDValidated = (int)$pdo->query("SELECT COUNT(*) FROM id_validation WHERE Status = 'Completed'")->fetchColumn();
$stageEvaluated = (int)$pdo->query("SELECT COUNT(*) FROM evaluation WHERE Status = 'Approved'")->fetchColumn();
$stageBlocked = (int)$pdo->query("SELECT COUNT(*) FROM blocking")->fetchColumn();
$stageMedical = (int)$pdo->query("SELECT COUNT(*) FROM clearance WHERE Status = 'Cleared'")->fetchColumn();
$stageEnrolled = (int)$pdo->query("SELECT COUNT(*) FROM enrollment WHERE Status = 'Enrolled'")->fetchColumn();

// 3. Recent System Audit Logs
$stmtLogs = $pdo->query("SELECT * FROM system_log ORDER BY Timestamp DESC LIMIT 8");
$recentLogs = $stmtLogs->fetchAll();

// 4. Department Distribution
$stmtDepts = $pdo->query("
    SELECT d.DepartmentName, COUNT(p.ProgramID) as ProgramCount 
    FROM department d 
    LEFT JOIN program p ON d.departmentID = p.DepartmentID 
    GROUP BY d.departmentID 
    LIMIT 6
");
$deptStats = $stmtDepts->fetchAll();
?>
<?php include 'includes/header.php'; ?>
<?php include 'includes/sidebar.php'; ?>
<div class="pl-72 flex flex-col min-h-screen">
<?php include 'includes/topbar.php'; ?>
<main class="w-full pt-16 flex-1 px-gutter py-space-lg">
    <div class="flex flex-col w-full gap-space-lg">
        
        <!-- Header Banner -->
        <section class="flex flex-col gap-space-md">
            <div class="flex flex-col md:flex-row md:items-end justify-between gap-space-sm bg-surface-container-lowest p-space-lg rounded-xl shadow-[0_1px_8px_rgba(0,0,0,0.04)] border border-outline-variant/30 relative overflow-hidden">
                <div class="flex flex-col gap-space-xs z-10">
                    <div class="flex items-center gap-space-xs text-primary font-label-md text-label-md uppercase tracking-wider font-semibold">
                        <i class="fa-solid fa-shield-halved text-[18px]"></i>
                        <span>System Administration Command</span>
                    </div>
                    <h1 class="font-headline-lg text-headline-lg text-on-surface tracking-tight">System Overview & Analytics</h1>
                    <p class="font-body-md text-body-md text-tertiary">
                        Comprehensive administrative monitoring, campus intake status, and audit log activity.
                    </p>
                </div>
                <div class="flex items-center gap-space-sm z-10">
                    <a href="admin_logs.php" class="flex items-center gap-space-xs px-4 py-2 bg-surface-container-low hover:bg-surface-container text-on-surface rounded-lg font-label-md text-label-md border border-outline-variant/40 transition-colors">
                        <i class="fa-solid fa-receipt text-[18px]"></i>
                        <span>View All Audit Logs</span>
                    </a>
                    <a href="admin_users.php" class="flex items-center gap-space-xs px-4 py-2 bg-primary hover:bg-primary-container text-on-primary rounded-lg font-label-md text-label-md font-semibold transition-colors shadow-sm">
                        <i class="fa-solid fa-users-gear text-[18px]"></i>
                        <span>Manage Accounts</span>
                    </a>
                </div>
            </div>

            <!-- Top Stat Badges -->
            <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-6 gap-space-md">
                <div class="bg-surface-container-lowest p-space-md rounded-xl shadow-[0_1px_8px_rgba(0,0,0,0.04)] border-l-4 border-l-primary flex flex-col justify-between">
                    <div class="flex items-center justify-between text-tertiary">
                        <span class="font-label-sm text-label-sm uppercase tracking-wide">Total Students</span>
                        <i class="fa-solid fa-users text-primary text-[20px]"></i>
                    </div>
                    <div class="mt-space-sm">
                        <span class="font-headline-md text-headline-md text-on-surface"><?= number_format($totalStudents) ?></span>
                    </div>
                </div>
                <div class="bg-surface-container-lowest p-space-md rounded-xl shadow-[0_1px_8px_rgba(0,0,0,0.04)] border-l-4 border-l-secondary flex flex-col justify-between">
                    <div class="flex items-center justify-between text-tertiary">
                        <span class="font-label-sm text-label-sm uppercase tracking-wide">Staff Members</span>
                        <i class="fa-solid fa-user-shield text-secondary text-[20px]"></i>
                    </div>
                    <div class="mt-space-sm">
                        <span class="font-headline-md text-headline-md text-on-surface"><?= number_format($totalStaff) ?></span>
                    </div>
                </div>
                <div class="bg-surface-container-lowest p-space-md rounded-xl shadow-[0_1px_8px_rgba(0,0,0,0.04)] border-l-4 border-l-emerald-500 flex flex-col justify-between">
                    <div class="flex items-center justify-between text-tertiary">
                        <span class="font-label-sm text-label-sm uppercase tracking-wide">Active Users</span>
                        <i class="fa-solid fa-user-shield text-emerald-500 text-[20px]"></i>
                    </div>
                    <div class="mt-space-sm">
                        <span class="font-headline-md text-headline-md text-on-surface"><?= number_format($activeLogins) ?></span>
                    </div>
                </div>
                <div class="bg-surface-container-lowest p-space-md rounded-xl shadow-[0_1px_8px_rgba(0,0,0,0.04)] border-l-4 border-l-blue-500 flex flex-col justify-between">
                    <div class="flex items-center justify-between text-tertiary">
                        <span class="font-label-sm text-label-sm uppercase tracking-wide">Academic Programs</span>
                        <i class="fa-solid fa-graduation-cap text-blue-500 text-[20px]"></i>
                    </div>
                    <div class="mt-space-sm">
                        <span class="font-headline-md text-headline-md text-on-surface"><?= number_format($totalPrograms) ?></span>
                    </div>
                </div>
                <div class="bg-surface-container-lowest p-space-md rounded-xl shadow-[0_1px_8px_rgba(0,0,0,0.04)] border-l-4 border-l-amber-500 flex flex-col justify-between">
                    <div class="flex items-center justify-between text-tertiary">
                        <span class="font-label-sm text-label-sm uppercase tracking-wide">Fully Enrolled</span>
                        <i class="fa-solid fa-user-check text-amber-500 text-[20px]"></i>
                    </div>
                    <div class="mt-space-sm">
                        <span class="font-headline-md text-headline-md text-on-surface"><?= number_format($stageEnrolled) ?></span>
                    </div>
                </div>
                <div class="bg-surface-container-lowest p-space-md rounded-xl shadow-[0_1px_8px_rgba(0,0,0,0.04)] border-l-4 border-l-purple-500 flex flex-col justify-between">
                    <div class="flex items-center justify-between text-tertiary">
                        <span class="font-label-sm text-label-sm uppercase tracking-wide">Revenue (Paid)</span>
                        <i class="fa-solid fa-money-bill-wave text-purple-500 text-[20px]"></i>
                    </div>
                    <div class="mt-space-sm">
                        <span class="font-headline-md text-headline-md text-on-surface">₱<?= number_format($totalRevenue, 2) ?></span>
                    </div>
                </div>
            </div>
        </section>

        <!-- Pipeline Intake Funnel -->
        <section class="flex flex-col gap-space-md">
            <div class="bg-surface-container-lowest rounded-xl shadow-[0_1px_8px_rgba(0,0,0,0.04)] p-space-lg border border-outline-variant/30">
                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-space-xs mb-space-md pb-space-sm border-b border-surface-container">
                    <div>
                        <h2 class="font-headline-sm text-headline-sm text-on-surface">Cross-Department Matriculation Pipeline</h2>
                        <p class="font-body-sm text-body-sm text-tertiary">Real-time candidate progression through Registrar, Clinic, Academic Evaluation, and Cashier</p>
                    </div>
                    <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-emerald-50 text-emerald-700 text-xs font-semibold">
                        <span class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></span>
                        Pipeline Live
                    </span>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-6 gap-space-sm">
                    <div class="p-space-md rounded-xl bg-surface-container-low border border-surface-container flex flex-col gap-2">
                        <div class="flex items-center justify-between text-tertiary">
                            <span class="text-xs font-semibold uppercase">1. Admissions</span>
                            <i class="fa-solid fa-user-check text-[18px]"></i>
                        </div>
                        <div class="text-xl font-bold text-on-surface"><?= $stageAdmissions ?> <span class="text-xs font-normal text-tertiary">approved</span></div>
                        <div class="w-full bg-surface-container-high h-1.5 rounded-full overflow-hidden">
                            <div class="bg-primary h-full" style="width: <?= $totalStudents > 0 ? min(100, round(($stageAdmissions / $totalStudents) * 100)) : 0 ?>%"></div>
                        </div>
                    </div>

                    <div class="p-space-md rounded-xl bg-surface-container-low border border-surface-container flex flex-col gap-2">
                        <div class="flex items-center justify-between text-tertiary">
                            <span class="text-xs font-semibold uppercase">2. ID Validated</span>
                            <i class="fa-solid fa-id-badge text-[18px]"></i>
                        </div>
                        <div class="text-xl font-bold text-on-surface"><?= $stageIDValidated ?> <span class="text-xs font-normal text-tertiary">verified</span></div>
                        <div class="w-full bg-surface-container-high h-1.5 rounded-full overflow-hidden">
                            <div class="bg-secondary h-full" style="width: <?= $totalStudents > 0 ? min(100, round(($stageIDValidated / $totalStudents) * 100)) : 0 ?>%"></div>
                        </div>
                    </div>

                    <div class="p-space-md rounded-xl bg-surface-container-low border border-surface-container flex flex-col gap-2">
                        <div class="flex items-center justify-between text-tertiary">
                            <span class="text-xs font-semibold uppercase">3. Evaluated</span>
                            <i class="fa-solid fa-clipboard-check text-[18px]"></i>
                        </div>
                        <div class="text-xl font-bold text-on-surface"><?= $stageEvaluated ?> <span class="text-xs font-normal text-tertiary">evaluated</span></div>
                        <div class="w-full bg-surface-container-high h-1.5 rounded-full overflow-hidden">
                            <div class="bg-blue-500 h-full" style="width: <?= $totalStudents > 0 ? min(100, round(($stageEvaluated / $totalStudents) * 100)) : 0 ?>%"></div>
                        </div>
                    </div>

                    <div class="p-space-md rounded-xl bg-surface-container-low border border-surface-container flex flex-col gap-2">
                        <div class="flex items-center justify-between text-tertiary">
                            <span class="text-xs font-semibold uppercase">4. Sectioned</span>
                            <i class="fa-solid fa-book-bookmark text-[18px]"></i>
                        </div>
                        <div class="text-xl font-bold text-on-surface"><?= $stageBlocked ?> <span class="text-xs font-normal text-tertiary">blocked</span></div>
                        <div class="w-full bg-surface-container-high h-1.5 rounded-full overflow-hidden">
                            <div class="bg-purple-500 h-full" style="width: <?= $totalStudents > 0 ? min(100, round(($stageBlocked / $totalStudents) * 100)) : 0 ?>%"></div>
                        </div>
                    </div>

                    <div class="p-space-md rounded-xl bg-surface-container-low border border-surface-container flex flex-col gap-2">
                        <div class="flex items-center justify-between text-tertiary">
                            <span class="text-xs font-semibold uppercase">5. Medical</span>
                            <i class="fa-solid fa-shield-heart text-[18px]"></i>
                        </div>
                        <div class="text-xl font-bold text-on-surface"><?= $stageMedical ?> <span class="text-xs font-normal text-tertiary">cleared</span></div>
                        <div class="w-full bg-surface-container-high h-1.5 rounded-full overflow-hidden">
                            <div class="bg-teal-500 h-full" style="width: <?= $totalStudents > 0 ? min(100, round(($stageMedical / $totalStudents) * 100)) : 0 ?>%"></div>
                        </div>
                    </div>

                    <div class="p-space-md rounded-xl bg-surface-container-low border border-surface-container flex flex-col gap-2">
                        <div class="flex items-center justify-between text-tertiary">
                            <span class="text-xs font-semibold uppercase">6. Enrolled</span>
                            <i class="fa-solid fa-circle-check text-[18px]"></i>
                        </div>
                        <div class="text-xl font-bold text-emerald-600"><?= $stageEnrolled ?> <span class="text-xs font-normal text-tertiary">official</span></div>
                        <div class="w-full bg-surface-container-high h-1.5 rounded-full overflow-hidden">
                            <div class="bg-emerald-500 h-full" style="width: <?= $totalStudents > 0 ? min(100, round(($stageEnrolled / $totalStudents) * 100)) : 0 ?>%"></div>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- Two Column Section: Audit Trail & Master Data Distribution -->
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-space-lg">
            
            <!-- Audit Trail (2 Cols) -->
            <div class="lg:col-span-2 bg-surface-container-lowest rounded-xl shadow-[0_1px_8px_rgba(0,0,0,0.04)] border border-outline-variant/30 overflow-hidden">
                <div class="p-space-md border-b border-surface-container flex items-center justify-between">
                    <div class="flex items-center gap-space-xs font-title-md text-title-md text-on-surface">
                        <i class="fa-solid fa-clock-rotate-left text-primary text-[20px]"></i>
                        <span>Recent System Logs & Audit Trail</span>
                    </div>
                    <a href="admin_logs.php" class="text-primary text-xs font-semibold hover:underline">View All</a>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse text-sm">
                        <thead>
                            <tr class="bg-surface-container-low text-tertiary text-xs uppercase font-label-sm border-b border-surface-container">
                                <th class="px-space-md py- space-xs">Timestamp</th>
                                <th class="px-space-md py-space-xs">User / Role</th>
                                <th class="px-space-md py-space-xs">Action</th>
                                <th class="px-space-md py-space-xs">Details</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-surface-container">
                            <?php foreach ($recentLogs as $log): ?>
                            <tr class="hover:bg-surface-container-low/40 transition-colors">
                                <td class="px-space-md py-2.5 text-xs text-tertiary whitespace-nowrap">
                                    <?= date('M d, H:i', strtotime($log['Timestamp'])) ?>
                                </td>
                                <td class="px-space-md py-2.5">
                                    <span class="font-medium text-on-surface"><?= htmlspecialchars($log['User']) ?></span>
                                    <span class="text-xs px-2 py-0.5 rounded bg-surface-container font-mono text-tertiary ml-1"><?= htmlspecialchars($log['Role']) ?></span>
                                </td>
                                <td class="px-space-md py-2.5">
                                    <span class="font-semibold text-primary"><?= htmlspecialchars($log['Action']) ?></span>
                                </td>
                                <td class="px-space-md py-2.5 text-tertiary text-xs max-w-xs truncate" title="<?= htmlspecialchars($log['Details'] ?? '') ?>">
                                    <?= htmlspecialchars($log['Details'] ?? 'N/A') ?>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                            <?php if (empty($recentLogs)): ?>
                            <tr>
                                <td colspan="4" class="px-space-md py-6 text-center text-tertiary text-sm">No activity recorded yet.</td>
                            </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Department Breakdown (1 Col) -->
            <div class="bg-surface-container-lowest rounded-xl shadow-[0_1px_8px_rgba(0,0,0,0.04)] border border-outline-variant/30 overflow-hidden flex flex-col justify-between">
                <div>
                    <div class="p-space-md border-b border-surface-container flex items-center justify-between">
                        <div class="flex items-center gap-space-xs font-title-md text-title-md text-on-surface">
                            <i class="fa-solid fa-building text-secondary text-[20px]"></i>
                            <span>Departments Overview</span>
                        </div>
                        <a href="admin_departments.php" class="text-primary text-xs font-semibold hover:underline">Manage</a>
                    </div>
                    <div class="p-space-md flex flex-col gap-3">
                        <?php foreach ($deptStats as $dept): ?>
                        <div class="flex items-center justify-between p-2 rounded-lg bg-surface-container-low border border-surface-container">
                            <span class="font-medium text-xs text-on-surface line-clamp-1" title="<?= htmlspecialchars($dept['DepartmentName']) ?>">
                                <?= htmlspecialchars($dept['DepartmentName']) ?>
                            </span>
                            <span class="text-xs font-bold px-2 py-0.5 rounded-full bg-primary/10 text-primary whitespace-nowrap">
                                <?= $dept['ProgramCount'] ?> Programs
                            </span>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </div>

                <div class="p-space-md border-t border-surface-container bg-surface-container-low/40">
                    <div class="flex items-center justify-between text-xs text-tertiary">
                        <span>Database Status:</span>
                        <span class="font-semibold text-emerald-600 flex items-center gap-1">
                            <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span> Connected (MySQL)
                        </span>
                    </div>
                </div>
            </div>

        </div>

    </div>
</main>
<?php include 'includes/footer.php'; ?>