<?php
session_start();
require_once '../config/db.php';

if (!isset($_SESSION['UserType']) || $_SESSION['UserType'] !== 'Staff' || !in_array($_SESSION['Role'] ?? '', ['Accounting', 'Admin'])) {
    header("Location: login.php?error=" . urlencode("Access denied. Cashier / Accounting privileges required."));
    exit;
}

$search = trim($_GET['search'] ?? '');

$sql = "
    SELECT p.PaymentID, p.Amount, p.PaymentStatus, p.PaymentDate,
           s.StudentID, s.StudentNo, s.LastName, s.FirstName,
           prog.ProgramName,
           e.EnrollmentID
    FROM payment p
    JOIN student s ON p.StudentID = s.StudentID
    LEFT JOIN program prog ON s.ProgramID = prog.ProgramID
    LEFT JOIN enrollment e ON p.PaymentID = e.PaymentID
    WHERE p.PaymentStatus = 'Pending'
";
$params = [];

if (!empty($search)) {
    $sql .= " AND (s.StudentNo LIKE ? OR s.LastName LIKE ? OR s.FirstName LIKE ?)";
    $term = "%$search%";
    $params = [$term, $term, $term];
}

$sql .= " ORDER BY p.PaymentID DESC";
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$queue = $stmt->fetchAll();
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
                        <span>Accounting Module</span>
                    </div>
                    <h1 class="font-headline-lg text-headline-lg text-on-surface tracking-tight">Enrollment Billing Queue</h1>
                    <p class="font-body-md text-body-md text-tertiary">
                        Active student queue with pending tuition assessments awaiting cashier settlement.
                    </p>
                </div>
            </div>
        </section>

        <!-- Search -->
        <section class="bg-surface-container-lowest p-space-md rounded-xl shadow-[0_1px_8px_rgba(0,0,0,0.04)] border border-outline-variant/30">
            <form method="GET" class="flex items-center justify-between gap-space-sm">
                <div class="relative max-w-md w-full">
                    <i class="fa-solid fa-magnifying-glass absolute left-3 top-1/2 -translate-y-1/2 text-tertiary text-[18px]"></i>
                    <input type="text" name="search" value="<?= htmlspecialchars($search) ?>" placeholder="Search student number or candidate name..." 
                           class="w-full h-10 pl-9 pr-3 rounded-lg bg-surface-container-low border border-surface-container text-sm focus:outline-none focus:border-primary"/>
                </div>
                <div class="flex items-center gap-2">
                    <button type="submit" class="px-4 py-2 bg-primary text-on-primary rounded-lg text-sm font-semibold hover:bg-primary-container transition-colors">
                        Filter Queue
                    </button>
                    <a href="accounting_billing_queue.php" class="px-3 py-2 bg-surface-container hover:bg-surface-container-high rounded-lg text-sm text-tertiary">
                        Reset
                    </a>
                </div>
            </form>
        </section>

        <!-- Queue Table -->
        <section class="bg-surface-container-lowest rounded-xl shadow-[0_1px_8px_rgba(0,0,0,0.04)] border border-outline-variant/30 overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse text-sm">
                    <thead>
                        <tr class="bg-surface-container-low text-tertiary text-xs uppercase font-label-sm border-b border-surface-container">
                            <th class="px-space-md py-space-sm">Student No</th>
                            <th class="px-space-md py-space-sm">Candidate Name</th>
                            <th class="px-space-md py-space-sm">Degree Program</th>
                            <th class="px-space-md py-space-sm">Assessment Amount</th>
                            <th class="px-space-md py-space-sm">Status</th>
                            <th class="px-space-md py-space-sm text-right">Action</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-surface-container">
                        <?php foreach($queue as $q): ?>
                        <tr class="hover:bg-surface-container-low/50 transition-colors">
                            <td class="px-space-md py-space-sm font-mono font-semibold text-primary">
                                <?= htmlspecialchars($q['StudentNo']) ?>
                            </td>
                            <td class="px-space-md py-space-sm font-medium text-on-surface">
                                <?= htmlspecialchars($q['LastName'] . ', ' . $q['FirstName']) ?>
                            </td>
                            <td class="px-space-md py-space-sm text-xs text-tertiary">
                                <?= htmlspecialchars($q['ProgramName'] ?? 'Unassigned') ?>
                            </td>
                            <td class="px-space-md py-space-sm font-bold text-on-surface">
                                ₱<?= number_format($q['Amount'] > 0 ? $q['Amount'] : 5000.00, 2) ?>
                            </td>
                            <td class="px-space-md py-space-sm">
                                <span class="px-2.5 py-0.5 rounded-full text-xs font-semibold bg-amber-100 text-amber-800">
                                    Pending Payment
                                </span>
                            </td>
                            <td class="px-space-md py-space-sm text-right">
                                <a href="accounting_pos.php?student_id=<?= $q['StudentID'] ?>" class="px-3.5 py-1.5 bg-primary text-on-primary font-semibold text-xs rounded-lg hover:bg-primary-container transition-colors shadow-sm inline-flex items-center gap-1">
                                    <i class="fa-solid fa-cash-register text-[16px]"></i>
                                    <span>Collect Payment</span>
                                </a>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                        <?php if(empty($queue)): ?>
                        <tr>
                            <td colspan="6" class="px-space-md py-8 text-center text-tertiary">No students waiting in the billing queue.</td>
                        </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </section>

    </div>
</main>
<?php include 'includes/footer.php'; ?>