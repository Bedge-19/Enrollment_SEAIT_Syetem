<?php
session_start();
require_once '../config/db.php';

if (!isset($_SESSION['UserType']) || $_SESSION['UserType'] !== 'Student') {
    header("Location: login.php");
    exit;
}

$studentId = $_SESSION['StudentID'] ?? 0;

// Fetch Student Profile
$stmt = $pdo->prepare("
    SELECT s.*, p.ProgramName, st.TypeName
    FROM student s
    LEFT JOIN program p ON s.ProgramID = p.ProgramID
    LEFT JOIN student_type st ON s.StudentTypeID = st.StudentTypeID
    WHERE s.StudentID = ?
");
$stmt->execute([$studentId]);
$student = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$student) {
    header("Location: login.php?error=" . urlencode("Student record not found."));
    exit;
}

// Fetch Active Enrollment & Payment
$enrStmt = $pdo->prepare("
    SELECT e.EnrollmentID, e.SchoolYear, e.Semester, e.Status as EnrollmentStatus,
           pay.PaymentID, pay.Amount as AmountPaid, pay.PaymentDate, pay.PaymentStatus, pay.ReceiptNo,
           ev.EvaluationID, ev.Status as EvalStatus
    FROM enrollment e
    LEFT JOIN payment pay ON e.PaymentID = pay.PaymentID
    LEFT JOIN evaluation ev ON e.EvaluationID = ev.EvaluationID
    WHERE e.StudentID = ?
    ORDER BY e.EnrollmentID DESC
    LIMIT 1
");
$enrStmt->execute([$studentId]);
$enrollment = $enrStmt->fetch(PDO::FETCH_ASSOC);

// Calculate evaluated units
$units = 0;
if (!empty($enrollment['EvaluationID'])) {
    $uStmt = $pdo->prepare("
        SELECT SUM(sub.Units) 
        FROM evaluation_subject es 
        JOIN subject sub ON es.SubjectID = sub.SubjectID 
        WHERE es.EvaluationID = ? AND es.SubjectStatus = 'Approved'
    ");
    $uStmt->execute([$enrollment['EvaluationID']]);
    $units = (int)$uStmt->fetchColumn();
}
if ($units <= 0) $units = 21;

// Fetch All Payment Receipts History
$payStmt = $pdo->prepare("
    SELECT pay.*, pay.Amount as AmountPaid, e.SchoolYear, e.Semester, stf.LastName as CashierLastName, stf.FirstName as CashierFirstName
    FROM payment pay
    JOIN enrollment e ON pay.PaymentID = e.PaymentID
    LEFT JOIN staff stf ON pay.StaffID = stf.StaffID
    WHERE e.StudentID = ?
    ORDER BY pay.PaymentDate DESC, pay.PaymentID DESC
");
$payStmt->execute([$studentId]);
$paymentHistory = $payStmt->fetchAll(PDO::FETCH_ASSOC);

$tuitionPerUnit = 250.00;
$tuitionTotal = $units * $tuitionPerUnit;

$feeItems = [
    ['name' => "Tuition Fee ($units Units @ ₱" . number_format($tuitionPerUnit, 2) . ")", 'amount' => $tuitionTotal],
    ['name' => "Registration & Matriculation Fee", 'amount' => 350.00],
    ['name' => "Library & Information Resources", 'amount' => 400.00],
    ['name' => "Medical & Dental Clinic Fee", 'amount' => 250.00],
    ['name' => "Athletic, Sports & Cultural Fee", 'amount' => 300.00],
    ['name' => "Student Development & Insurance", 'amount' => 200.00],
    ['name' => "Computer Laboratory / IT Resource Fee", 'amount' => 600.00]
];

$totalAssessed = 0.0;
foreach ($feeItems as $item) {
    $totalAssessed += $item['amount'];
}

$amountPaid = (float)($enrollment['AmountPaid'] ?? 0.0);
$isPaid = (($enrollment['PaymentStatus'] ?? '') === 'Paid');
$balance = $isPaid ? 0.0 : max(0.0, $totalAssessed - $amountPaid);
?>
<?php include 'includes/header.php'; ?>
<?php include 'includes/sidebar.php'; ?>
<div class="pl-72 flex flex-col min-h-screen">
<?php include 'includes/topbar.php'; ?>
<main class="w-full pt-16 flex-1 px-gutter py-space-lg">
    <div class="flex flex-col w-full gap-space-lg max-w-5xl mx-auto">
        
        <!-- Header Banner -->
        <div class="bg-surface-container-lowest p-space-lg rounded-xl shadow-[0_1px_8px_rgba(0,0,0,0.04)] border border-surface-container flex flex-col md:flex-row justify-between items-start md:items-center gap-space-md">
            <div>
                <div class="flex items-center gap-2 mb-1">
                    <span class="px-2.5 py-0.5 rounded-full text-xs font-bold uppercase tracking-wider <?= $isPaid ? 'bg-green-100 text-green-800' : 'bg-amber-100 text-amber-800' ?>">
                        <?= $isPaid ? 'Account Settled' : 'Payment Required' ?>
                    </span>
                    <span class="text-xs text-tertiary">Step 5 • Student Financials</span>
                </div>
                <h1 class="font-headline-lg text-headline-lg text-on-surface font-bold">Billing & Payments</h1>
                <p class="font-body-md text-body-md text-tertiary">
                    Official statement of account, tuition fees assessment, payment receipts, and cashier clearance.
                </p>
            </div>
            
            <div class="flex items-center gap-space-sm print:hidden">
                <button onclick="window.print()" class="flex items-center gap-2 px-space-md py-space-sm rounded-lg font-label-md text-label-md font-semibold text-white bg-primary hover:bg-primary-container transition-colors shadow-sm">
                    <i class="fa-solid fa-print text-[18px]"></i>
                    Print SOA
                </button>
            </div>
        </div>

        <!-- Financial KPI Cards -->
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-space-md">
            <div class="bg-surface-container-lowest p-space-md rounded-xl shadow-[0_1px_8px_rgba(0,0,0,0.04)] border border-surface-container">
                <div class="text-xs font-bold uppercase tracking-wider text-tertiary mb-1">Total Assessed</div>
                <div class="font-headline-sm text-headline-sm font-bold text-on-surface font-mono">
                    ₱<?= number_format($totalAssessed, 2) ?>
                </div>
                <div class="text-xs text-tertiary mt-1">
                    Based on <?= $units ?> academic units
                </div>
            </div>

            <div class="bg-surface-container-lowest p-space-md rounded-xl shadow-[0_1px_8px_rgba(0,0,0,0.04)] border border-surface-container">
                <div class="text-xs font-bold uppercase tracking-wider text-tertiary mb-1">Amount Paid</div>
                <div class="font-headline-sm text-headline-sm font-bold text-green-700 font-mono">
                    ₱<?= number_format($amountPaid, 2) ?>
                </div>
                <div class="text-xs text-tertiary mt-1">
                    <?= $isPaid ? 'Full payment received' : 'Partial / Pending' ?>
                </div>
            </div>

            <div class="bg-surface-container-lowest p-space-md rounded-xl shadow-[0_1px_8px_rgba(0,0,0,0.04)] border border-surface-container">
                <div class="text-xs font-bold uppercase tracking-wider text-tertiary mb-1">Current Balance</div>
                <div class="font-headline-sm text-headline-sm font-bold <?= $balance > 0 ? 'text-amber-700' : 'text-on-surface' ?> font-mono">
                    ₱<?= number_format($balance, 2) ?>
                </div>
                <div class="text-xs text-tertiary mt-1">
                    <?= $balance > 0 ? 'Due for cashier settlement' : 'No outstanding balance' ?>
                </div>
            </div>

            <div class="bg-surface-container-lowest p-space-md rounded-xl shadow-[0_1px_8px_rgba(0,0,0,0.04)] border border-surface-container">
                <div class="text-xs font-bold uppercase tracking-wider text-tertiary mb-1">Payment Status</div>
                <div class="font-headline-sm text-headline-sm font-bold <?= $isPaid ? 'text-green-700' : 'text-amber-600' ?>">
                    <?= $isPaid ? 'Paid' : 'Pending' ?>
                </div>
                <div class="text-xs text-tertiary mt-1">
                    OR: <span class="font-mono font-bold"><?= htmlspecialchars($enrollment['ReceiptNo'] ?? 'None') ?></span>
                </div>
            </div>
        </div>

        <!-- Statement of Account (SOA) -->
        <div class="bg-surface-container-lowest rounded-xl shadow-[0_1px_8px_rgba(0,0,0,0.04)] border border-surface-container overflow-hidden">
            <div class="p-space-md bg-surface-container-low border-b border-surface-container flex flex-wrap items-center justify-between gap-space-sm">
                <div class="flex items-center gap-2">
                    <i class="fa-solid fa-receipt text-primary text-[20px]"></i>
                    <h2 class="font-title-md text-title-md font-bold text-on-surface">Official Statement of Account (SOA)</h2>
                </div>
                <div class="text-xs text-tertiary font-mono">
                    A.Y. <?= htmlspecialchars($enrollment['SchoolYear'] ?? '2026-2027') ?> • <?= htmlspecialchars($enrollment['Semester'] ?? '1st Semester') ?>
                </div>
            </div>

            <div class="p-space-lg">
                <!-- Student Summary Bar -->
                <div class="grid grid-cols-2 md:grid-cols-4 gap-space-sm p-space-sm bg-surface-container-low rounded-lg border border-surface-container text-xs mb-space-md">
                    <div>
                        <span class="text-tertiary block font-bold">Student No</span>
                        <span class="font-mono font-bold text-primary"><?= htmlspecialchars($student['StudentNo'] ?? 'N/A') ?></span>
                    </div>
                    <div>
                        <span class="text-tertiary block font-bold">Student Name</span>
                        <span class="font-bold text-on-surface"><?= htmlspecialchars(($student['LastName'] ?? '') . ', ' . ($student['FirstName'] ?? '')) ?></span>
                    </div>
                    <div>
                        <span class="text-tertiary block font-bold">Degree Program</span>
                        <span class="font-medium text-on-surface"><?= htmlspecialchars($student['ProgramName'] ?? 'N/A') ?></span>
                    </div>
                    <div>
                        <span class="text-tertiary block font-bold">Student Classification</span>
                        <span class="font-medium text-on-surface"><?= htmlspecialchars($student['TypeName'] ?? 'Regular') ?></span>
                    </div>
                </div>

                <!-- Fee Schedule Table -->
                <div class="overflow-x-auto border border-surface-container rounded-lg">
                    <table class="w-full text-left border-collapse text-xs">
                        <thead>
                            <tr class="bg-surface-container-low/50 text-tertiary font-label-sm text-label-sm uppercase tracking-wider border-b border-surface-container">
                                <th class="px-space-md py-2.5 font-bold">Fee Description</th>
                                <th class="px-space-md py-2.5 font-bold text-right">Assessed Amount</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y border-surface-container font-body-sm text-body-sm">
                            <?php foreach ($feeItems as $item): ?>
                            <tr>
                                <td class="px-space-md py-2 text-on-surface font-medium text-xs">
                                    <?= htmlspecialchars($item['name']) ?>
                                </td>
                                <td class="px-space-md py-2 text-right font-mono text-xs text-on-surface">
                                    ₱<?= number_format($item['amount'], 2) ?>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                            <tr class="bg-surface-container-low/40 font-bold border-t-2 border-surface-container">
                                <td class="px-space-md py-2.5 uppercase tracking-wider text-xs">Total Institutional Assessment:</td>
                                <td class="px-space-md py-2.5 text-right font-mono text-primary text-sm">₱<?= number_format($totalAssessed, 2) ?></td>
                            </tr>
                            <tr class="bg-green-50/50 font-bold text-green-900">
                                <td class="px-space-md py-2.5 uppercase tracking-wider text-xs">Total Payments Applied:</td>
                                <td class="px-space-md py-2.5 text-right font-mono text-sm">- ₱<?= number_format($amountPaid, 2) ?></td>
                            </tr>
                            <tr class="bg-surface-container-low/70 font-bold">
                                <td class="px-space-md py-2.5 uppercase tracking-wider text-xs text-on-surface">Net Balance Remaining:</td>
                                <td class="px-space-md py-2.5 text-right font-mono text-sm <?= $balance > 0 ? 'text-amber-700' : 'text-green-700' ?>">₱<?= number_format($balance, 2) ?></td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Official Payment Receipts History -->
        <div class="bg-surface-container-lowest rounded-xl shadow-[0_1px_8px_rgba(0,0,0,0.04)] border border-surface-container overflow-hidden">
            <div class="p-space-md bg-surface-container-low border-b border-surface-container flex items-center justify-between">
                <div class="flex items-center gap-2">
                    <i class="fa-solid fa-money-bill-wave text-primary text-[20px]"></i>
                    <h2 class="font-title-md text-title-md font-bold text-on-surface">Official Receipts & Cashier Transactions</h2>
                </div>
                <span class="text-xs font-semibold text-tertiary"><?= count($paymentHistory) ?> Transaction(s)</span>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse text-xs">
                    <thead>
                        <tr class="bg-surface-container-low/50 text-tertiary font-label-sm text-label-sm uppercase tracking-wider border-b border-surface-container">
                            <th class="px-space-md py-2.5 font-bold">Official Receipt #</th>
                            <th class="px-space-md py-2.5 font-bold">Date of Payment</th>
                            <th class="px-space-md py-2.5 font-bold">Academic Term</th>
                            <th class="px-space-md py-2.5 font-bold text-right">Amount Paid</th>
                            <th class="px-space-md py-2.5 font-bold">Cashier Desk</th>
                            <th class="px-space-md py-2.5 font-bold text-center">Status</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y border-surface-container font-body-sm text-body-sm">
                        <?php if (!empty($paymentHistory)): ?>
                            <?php foreach ($paymentHistory as $p): ?>
                            <tr class="hover:bg-surface-container-low/30 transition-colors">
                                <td class="px-space-md py-2.5 font-mono font-bold text-primary">
                                    <?= htmlspecialchars($p['ReceiptNo'] ?: 'OR-PENDING') ?>
                                </td>
                                <td class="px-space-md py-2.5 text-tertiary font-mono">
                                    <?= !empty($p['PaymentDate']) ? date('M d, Y', strtotime($p['PaymentDate'])) : 'N/A' ?>
                                </td>
                                <td class="px-space-md py-2.5 text-on-surface">
                                    <?= htmlspecialchars($p['SchoolYear'] ?? '2026-2027') ?> • <?= htmlspecialchars($p['Semester'] ?? '1st Sem') ?>
                                </td>
                                <td class="px-space-md py-2.5 text-right font-mono font-bold text-on-surface">
                                    ₱<?= number_format((float)$p['AmountPaid'], 2) ?>
                                </td>
                                <td class="px-space-md py-2.5 text-tertiary">
                                    <?= !empty($p['CashierLastName']) ? htmlspecialchars($p['CashierFirstName'] . ' ' . $p['CashierLastName']) : 'University Cashier Windows' ?>
                                </td>
                                <td class="px-space-md py-2.5 text-center">
                                    <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-xs font-bold bg-green-100 text-green-800">
                                        <i class="fa-solid fa-circle-check text-[14px]"></i> Verified
                                    </span>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="6" class="px-space-md py-space-lg text-center text-tertiary">
                                    No completed payment receipts found. Please visit the Cashier / Accounting Office (Window 3) to process your payment.
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Cashier Settlement Guide -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-space-md print:hidden">
            <div class="bg-surface-container-lowest p-space-md rounded-xl shadow-[0_1px_8px_rgba(0,0,0,0.04)] border border-surface-container flex gap-space-sm">
                <i class="fa-solid fa-shop text-primary text-2xl shrink-0"></i>
                <div class="space-y-1">
                    <h3 class="font-title-sm text-title-sm font-bold text-on-surface">In-Person Cashier Windows</h3>
                    <p class="font-body-sm text-body-sm text-tertiary leading-relaxed">
                        Cashier Windows 1 to 4 are open <strong>Monday to Friday, 8:00 AM – 5:00 PM</strong> at the Administration Building Ground Floor. Present your Student ID No. to pay via cash or manager's check.
                    </p>
                </div>
            </div>

            <div class="bg-surface-container-lowest p-space-md rounded-xl shadow-[0_1px_8px_rgba(0,0,0,0.04)] border border-surface-container flex gap-space-sm">
                <i class="fa-solid fa-arrows-rotate text-secondary text-2xl shrink-0"></i>
                <div class="space-y-1">
                    <h3 class="font-title-sm text-title-sm font-bold text-on-surface">Automatic Enrollment Clearance</h3>
                    <p class="font-body-sm text-body-sm text-tertiary leading-relaxed">
                        Once the Cashier records your transaction, your payment status will immediately update to <strong>Paid</strong> in real time, unlocking your official Certificate of Registration (COR) in the Registrar portal.
                    </p>
                </div>
            </div>
        </div>

    </div>
</main>
<?php include 'includes/footer.php'; ?>
</div>