<?php
session_start();
require_once '../config/db.php';

if (!isset($_SESSION['UserType']) || $_SESSION['UserType'] !== 'Staff' || !in_array($_SESSION['Role'] ?? '', ['Accounting', 'Admin'])) {
    header("Location: login.php?error=" . urlencode("Access denied. Cashier / Accounting privileges required."));
    exit;
}

// 1. Financial Metrics
$pendingPayments = (int)$pdo->query("SELECT COUNT(*) FROM payment WHERE PaymentStatus = 'Pending'")->fetchColumn();
$paidEnrollees = (int)$pdo->query("SELECT COUNT(*) FROM payment WHERE PaymentStatus = 'Paid'")->fetchColumn();
$totalCollected = (float)$pdo->query("SELECT SUM(Amount) FROM payment WHERE PaymentStatus = 'Paid'")->fetchColumn();
$todayCollected = (float)$pdo->query("SELECT SUM(Amount) FROM payment WHERE PaymentStatus = 'Paid' AND PaymentDate = CURDATE()")->fetchColumn();

// 2. Immediate Cashier Queue
$stmtQueue = $pdo->query("
    SELECT p.PaymentID, p.Amount, p.PaymentStatus,
           s.StudentID, s.StudentNo, s.LastName, s.FirstName,
           prog.ProgramName,
           e.EnrollmentID
    FROM payment p
    JOIN student s ON p.StudentID = s.StudentID
    LEFT JOIN program prog ON s.ProgramID = prog.ProgramID
    LEFT JOIN enrollment e ON p.PaymentID = e.PaymentID
    WHERE p.PaymentStatus = 'Pending'
    ORDER BY p.PaymentID DESC
    LIMIT 10
");
$queue = $stmtQueue->fetchAll();

// 3. Recent Official Receipts Issued
$stmtRecent = $pdo->query("
    SELECT p.*, s.StudentNo, s.LastName, s.FirstName, prog.ProgramName
    FROM payment p
    JOIN student s ON p.StudentID = s.StudentID
    LEFT JOIN program prog ON s.ProgramID = prog.ProgramID
    WHERE p.PaymentStatus = 'Paid'
    ORDER BY p.PaymentID DESC
    LIMIT 8
");
$recentPaid = $stmtRecent->fetchAll();
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
                        <i class="fa-solid fa-landmark text-[18px]"></i>
                        <span>Cashier & Finance Operations</span>
                    </div>
                    <h1 class="font-headline-lg text-headline-lg text-on-surface tracking-tight">Cashier Command Dashboard</h1>
                    <p class="font-body-md text-body-md text-tertiary">
                        Point-of-sale matriculation settlement, tuition billings, and revenue collections.
                    </p>
                </div>
                <div class="flex items-center gap-space-sm">
                    <a href="accounting_pos.php" class="flex items-center gap-space-xs px-4 py-2 bg-primary hover:bg-primary-container text-on-primary rounded-lg font-label-md text-label-md font-semibold transition-colors shadow-sm">
                        <i class="fa-solid fa-cash-register text-[18px]"></i>
                        <span>Open POS Cashier</span>
                    </a>
                </div>
            </div>

            <?php if (!empty($_GET['msg'])): ?>
            <div class="p-3 bg-emerald-50 border border-emerald-200 text-emerald-800 rounded-lg text-sm flex items-center gap-2">
                <i class="fa-solid fa-circle-check text-emerald-600 text-[20px]"></i>
                <span><?= htmlspecialchars($_GET['msg']) ?></span>
            </div>
            <?php endif; ?>

            <!-- Metrics -->
            <div class="grid grid-cols-2 md:grid-cols-4 gap-space-md">
                <div class="bg-surface-container-lowest p-space-md rounded-xl shadow-[0_1px_8px_rgba(0,0,0,0.04)] border-l-4 border-l-emerald-500 flex flex-col justify-between">
                    <div class="flex items-center justify-between text-tertiary">
                        <span class="font-label-sm text-label-sm uppercase tracking-wide">Today's Collections</span>
                        <i class="fa-solid fa-calendar-day text-emerald-500 text-[20px]"></i>
                    </div>
                    <div class="mt-space-sm">
                        <span class="font-headline-md text-headline-md text-on-surface">₱<?= number_format($todayCollected, 2) ?></span>
                    </div>
                </div>

                <div class="bg-surface-container-lowest p-space-md rounded-xl shadow-[0_1px_8px_rgba(0,0,0,0.04)] border-l-4 border-l-primary flex flex-col justify-between">
                    <div class="flex items-center justify-between text-tertiary">
                        <span class="font-label-sm text-label-sm uppercase tracking-wide">Total Semester Revenue</span>
                        <i class="fa-solid fa-money-bill-wave text-primary text-[20px]"></i>
                    </div>
                    <div class="mt-space-sm">
                        <span class="font-headline-md text-headline-md text-on-surface">₱<?= number_format($totalCollected, 2) ?></span>
                    </div>
                </div>

                <div class="bg-surface-container-lowest p-space-md rounded-xl shadow-[0_1px_8px_rgba(0,0,0,0.04)] border-l-4 border-l-amber-500 flex flex-col justify-between">
                    <div class="flex items-center justify-between text-tertiary">
                        <span class="font-label-sm text-label-sm uppercase tracking-wide">Pending Invoices</span>
                        <i class="fa-solid fa-receipt text-amber-500 text-[20px]"></i>
                    </div>
                    <div class="mt-space-sm">
                        <span class="font-headline-md text-headline-md text-on-surface"><?= number_format($pendingPayments) ?></span>
                    </div>
                </div>

                <div class="bg-surface-container-lowest p-space-md rounded-xl shadow-[0_1px_8px_rgba(0,0,0,0.04)] border-l-4 border-l-blue-500 flex flex-col justify-between">
                    <div class="flex items-center justify-between text-tertiary">
                        <span class="font-label-sm text-label-sm uppercase tracking-wide">Paid Enrollees</span>
                        <i class="fa-solid fa-circle-check text-blue-500 text-[20px]"></i>
                    </div>
                    <div class="mt-space-sm">
                        <span class="font-headline-md text-headline-md text-on-surface"><?= number_format($paidEnrollees) ?></span>
                    </div>
                </div>
            </div>
        </section>

        <!-- Billing Queue & Recent Transactions -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-space-lg">

            <!-- Pending Billings -->
            <section class="bg-surface-container-lowest rounded-xl shadow-[0_1px_8px_rgba(0,0,0,0.04)] border border-outline-variant/30 overflow-hidden">
                <div class="p-space-md border-b border-surface-container flex items-center justify-between">
                    <div>
                        <h2 class="font-title-md text-title-md text-on-surface">Enrollment Billing Queue</h2>
                        <p class="text-xs text-tertiary">Students ready for assessment settlement</p>
                    </div>
                    <a href="accounting_billing_queue.php" class="text-xs font-semibold text-primary hover:underline">View All</a>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse text-sm">
                        <thead>
                            <tr class="bg-surface-container-low text-tertiary text-xs uppercase font-label-sm border-b border-surface-container">
                                <th class="px-space-md py-space-xs">Student</th>
                                <th class="px-space-md py-space-xs">Program</th>
                                <th class="px-space-md py-space-xs">Amount Due</th>
                                <th class="px-space-md py-space-xs text-right">Action</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-surface-container">
                            <?php foreach ($queue as $q): ?>
                            <tr class="hover:bg-surface-container-low/50 transition-colors">
                                <td class="px-space-md py-space-sm">
                                    <span class="font-medium text-on-surface"><?= htmlspecialchars($q['LastName'] . ', ' . $q['FirstName']) ?></span>
                                    <span class="block text-xs font-mono text-primary"><?= htmlspecialchars($q['StudentNo']) ?></span>
                                </td>
                                <td class="px-space-md py-space-sm text-xs text-tertiary">
                                    <?= htmlspecialchars($q['ProgramName'] ?? 'Unassigned') ?>
                                </td>
                                <td class="px-space-md py-space-sm font-semibold text-on-surface">
                                    ₱<?= number_format($q['Amount'] > 0 ? $q['Amount'] : 5000.00, 2) ?>
                                </td>
                                <td class="px-space-md py-space-sm text-right">
                                    <a href="accounting_pos.php?student_id=<?= $q['StudentID'] ?>" class="px-3 py-1 bg-primary text-on-primary text-xs font-semibold rounded hover:bg-primary-container transition-colors shadow-sm inline-flex items-center gap-1">
                                        <i class="fa-solid fa-cash-register text-[15px]"></i>
                                        <span>Collect</span>
                                    </a>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                            <?php if (empty($queue)): ?>
                            <tr>
                                <td colspan="4" class="px-space-md py-8 text-center text-tertiary text-sm">No students in billing queue.</td>
                            </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </section>

            <!-- Recent Official Receipts -->
            <section class="bg-surface-container-lowest rounded-xl shadow-[0_1px_8px_rgba(0,0,0,0.04)] border border-outline-variant/30 overflow-hidden">
                <div class="p-space-md border-b border-surface-container flex items-center justify-between">
                    <div>
                        <h2 class="font-title-md text-title-md text-on-surface">Recent Official Receipts (OR)</h2>
                        <p class="text-xs text-tertiary">Recently posted tuition transactions</p>
                    </div>
                    <a href="payment.php" class="text-xs font-semibold text-primary hover:underline">Ledger</a>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse text-sm">
                        <thead>
                            <tr class="bg-surface-container-low text-tertiary text-xs uppercase font-label-sm border-b border-surface-container">
                                <th class="px-space-md py-space-xs">OR Number</th>
                                <th class="px-space-md py-space-xs">Student</th>
                                <th class="px-space-md py-space-xs">Date</th>
                                <th class="px-space-md py-space-xs text-right">Paid Amount</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-surface-container">
                            <?php foreach ($recentPaid as $r): ?>
                            <tr class="hover:bg-surface-container-low/50 transition-colors">
                                <td class="px-space-md py-space-sm font-mono text-xs font-semibold text-emerald-700">
                                    <?= htmlspecialchars($r['ReceiptNo'] ?: 'OR-'.date('Y').'-'.$r['PaymentID']) ?>
                                </td>
                                <td class="px-space-md py-space-sm">
                                    <span class="font-medium text-on-surface"><?= htmlspecialchars($r['LastName'] . ', ' . $r['FirstName']) ?></span>
                                    <span class="block text-xs text-tertiary"><?= htmlspecialchars($r['StudentNo']) ?></span>
                                </td>
                                <td class="px-space-md py-space-sm text-xs text-tertiary">
                                    <?= date('M d, Y', strtotime($r['PaymentDate'])) ?>
                                </td>
                                <td class="px-space-md py-space-sm font-bold text-on-surface text-right">
                                    ₱<?= number_format($r['Amount'], 2) ?>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                            <?php if (empty($recentPaid)): ?>
                            <tr>
                                <td colspan="4" class="px-space-md py-8 text-center text-tertiary text-sm">No settled payments recorded yet.</td>
                            </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </section>

        </div>

    </div>
</main>
<?php include 'includes/footer.php'; ?>