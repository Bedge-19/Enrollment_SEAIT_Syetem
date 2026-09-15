<?php
session_start();
require_once '../config/db.php';

if (!isset($_SESSION['UserType']) || $_SESSION['UserType'] !== 'Staff' || !in_array($_SESSION['Role'] ?? '', ['Accounting', 'Admin'])) {
    header("Location: login.php?error=" . urlencode("Access denied. Cashier / Accounting privileges required."));
    exit;
}

$selectedStudentId = (int)($_GET['student_id'] ?? 0);

// Fetch candidates who have a pending payment or enrollment
$stmtStudents = $pdo->query("
    SELECT DISTINCT s.StudentID, s.StudentNo, s.LastName, s.FirstName, p.ProgramName, pay.PaymentID, pay.Amount, pay.PaymentStatus, e.EnrollmentID
    FROM student s
    LEFT JOIN program p ON s.ProgramID = p.ProgramID
    LEFT JOIN payment pay ON s.StudentID = pay.StudentID AND pay.PaymentStatus = 'Pending'
    LEFT JOIN enrollment e ON s.StudentID = e.StudentID
    ORDER BY s.LastName ASC, s.FirstName ASC
");
$allCandidates = $stmtStudents->fetchAll();

// If a specific student is selected, fetch their curriculum units / details
$selectedStudent = null;
$enrolledSubjects = [];
$totalUnits = 18; // default standard semester load
$tuitionPerUnit = 350.00;
$registrationFee = 500.00;
$miscFee = 1500.00;

if ($selectedStudentId) {
    $stmtSel = $pdo->prepare("
        SELECT s.*, p.ProgramName, pay.PaymentID, pay.Amount as AssessedAmount, e.EnrollmentID
        FROM student s
        LEFT JOIN program p ON s.ProgramID = p.ProgramID
        LEFT JOIN payment pay ON s.StudentID = pay.StudentID AND pay.PaymentStatus = 'Pending'
        LEFT JOIN enrollment e ON s.StudentID = e.StudentID
        WHERE s.StudentID = ?
        LIMIT 1
    ");
    $stmtSel->execute([$selectedStudentId]);
    $selectedStudent = $stmtSel->fetch();

    // Fetch subjects from evaluation or enrollment_subject
    $stmtSubs = $pdo->prepare("
        SELECT sub.SubjectCode, sub.SubjectTitle, sub.Units
        FROM enrollment_subject es
        JOIN subject sub ON es.SubjectID = sub.SubjectID
        WHERE es.EnrollmentID = ?
    ");
    if (!empty($selectedStudent['EnrollmentID'])) {
        $stmtSubs->execute([$selectedStudent['EnrollmentID']]);
        $enrolledSubjects = $stmtSubs->fetchAll();
        if (!empty($enrolledSubjects)) {
            $totalUnits = array_sum(array_column($enrolledSubjects, 'Units'));
        }
    }
}

$tuitionFee = $totalUnits * $tuitionPerUnit;
$totalDue = $tuitionFee + $registrationFee + $miscFee;

// Default Receipt No for next transaction
$nextReceiptNo = 'OR-' . date('Y') . '-' . str_pad(mt_rand(10000, 99999), 5, '0', STR_PAD_LEFT);
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
                        <i class="fa-solid fa-cash-register text-[18px]"></i>
                        <span>Accounting POS Terminal</span>
                    </div>
                    <h1 class="font-headline-lg text-headline-lg text-on-surface tracking-tight">Payment Processing (POS)</h1>
                    <p class="font-body-md text-body-md text-tertiary">
                        Calculate tuition assessments, receive tendered cash, and print official university receipts.
                    </p>
                </div>
            </div>

            <?php if (!empty($_GET['msg'])): ?>
            <div class="p-4 bg-emerald-50 border border-emerald-200 text-emerald-900 rounded-xl text-sm flex items-center justify-between shadow-sm">
                <div class="flex items-center gap-2">
                    <i class="fa-solid fa-circle-check text-emerald-600 text-[24px]"></i>
                    <div>
                        <span class="font-bold"><?= htmlspecialchars($_GET['msg']) ?></span>
                        <span class="block text-xs text-emerald-700">Official Receipt has been registered in the general ledger.</span>
                    </div>
                </div>
                <?php if (!empty($_GET['or'])): ?>
                <button onclick="window.print()" class="px-3 py-1.5 bg-emerald-600 text-white rounded-lg text-xs font-semibold hover:bg-emerald-700 transition-colors flex items-center gap-1 shadow-sm">
                    <i class="fa-solid fa-print text-[16px]"></i>
                    <span>Print Receipt</span>
                </button>
                <?php endif; ?>
            </div>
            <?php endif; ?>

            <?php if (!empty($_GET['error'])): ?>
            <div class="p-3 bg-error-container border border-error/30 text-on-error-container rounded-lg text-sm flex items-center gap-2">
                <i class="fa-solid fa-circle-exclamation text-error text-[20px]"></i>
                <span><?= htmlspecialchars($_GET['error']) ?></span>
            </div>
            <?php endif; ?>
        </section>

        <!-- POS Terminal Layout -->
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-space-lg">

            <!-- Left: Student Selector & Assessment Breakdown (2 Cols) -->
            <div class="lg:col-span-2 flex flex-col gap-space-lg">
                
                <!-- Student Selector Card -->
                <div class="bg-surface-container-lowest p-space-lg rounded-xl shadow-[0_1px_8px_rgba(0,0,0,0.04)] border border-outline-variant/30">
                    <label class="block text-xs font-semibold text-tertiary uppercase mb-2">Select Student Candidate</label>
                    <select onchange="window.location.href='accounting_pos.php?student_id=' + this.value" class="w-full h-11 px-3 rounded-lg bg-surface-container-low border border-surface-container text-sm font-medium focus:outline-none focus:border-primary">
                        <option value="">-- Choose Student to Settle Fees --</option>
                        <?php foreach ($allCandidates as $cand): ?>
                            <option value="<?= $cand['StudentID'] ?>" <?= $selectedStudentId === (int)$cand['StudentID'] ? 'selected' : '' ?>>
                                <?= htmlspecialchars($cand['StudentNo'] . ' - ' . $cand['LastName'] . ', ' . $cand['FirstName'] . ' (' . ($cand['ProgramName'] ?? 'No Program') . ')') ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <?php if ($selectedStudent): ?>
                <!-- Student Billing Summary Card -->
                <div class="bg-surface-container-lowest p-space-lg rounded-xl shadow-[0_1px_8px_rgba(0,0,0,0.04)] border border-outline-variant/30 flex flex-col gap-4">
                    <div class="flex items-center justify-between pb-3 border-b border-surface-container">
                        <div>
                            <h2 class="font-headline-sm text-headline-sm text-on-surface"><?= htmlspecialchars($selectedStudent['FirstName'] . ' ' . $selectedStudent['LastName']) ?></h2>
                            <p class="text-xs font-mono text-primary font-semibold">Student No: <?= htmlspecialchars($selectedStudent['StudentNo']) ?> • <?= htmlspecialchars($selectedStudent['ProgramName']) ?></p>
                        </div>
                        <span class="px-3 py-1 rounded-full text-xs font-semibold bg-amber-50 text-amber-700 border border-amber-200">
                            Assessment Pending
                        </span>
                    </div>

                    <!-- Breakdown Table -->
                    <div>
                        <div class="text-xs font-semibold text-tertiary uppercase mb-2">Tuition & Fee Breakdown (A.Y. 2026-2027 1st Sem)</div>
                        <div class="border border-surface-container rounded-lg overflow-hidden text-sm">
                            <div class="flex items-center justify-between p-3 bg-surface-container-low border-b border-surface-container">
                                <span>Tuition Fee (<?= $totalUnits ?> Units @ ₱<?= number_format($tuitionPerUnit, 2) ?>/unit)</span>
                                <span class="font-semibold text-on-surface">₱<?= number_format($tuitionFee, 2) ?></span>
                            </div>
                            <div class="flex items-center justify-between p-3 bg-surface-container-lowest border-b border-surface-container">
                                <span>Registration & Matriculation Fee</span>
                                <span class="font-semibold text-on-surface">₱<?= number_format($registrationFee, 2) ?></span>
                            </div>
                            <div class="flex items-center justify-between p-3 bg-surface-container-low border-b border-surface-container">
                                <span>Campus Facilities & Medical/Library Fee</span>
                                <span class="font-semibold text-on-surface">₱<?= number_format($miscFee, 2) ?></span>
                            </div>
                            <div class="flex items-center justify-between p-3 bg-primary/5 text-primary font-bold text-base">
                                <span>TOTAL ASSESSMENT DUE</span>
                                <span class="text-lg">₱<?= number_format($totalDue, 2) ?></span>
                            </div>
                        </div>
                    </div>
                </div>
                <?php else: ?>
                <div class="bg-surface-container-lowest p-space-xl rounded-xl shadow-[0_1px_8px_rgba(0,0,0,0.04)] border border-outline-variant/30 text-center py-16 text-tertiary">
                    <i class="fa-solid fa-wallet text-[48px] text-tertiary/40 mb-2"></i>
                    <p class="font-medium text-on-surface">No Student Selected</p>
                    <p class="text-xs mt-1">Please select a student candidate from the dropdown above to load their assessment.</p>
                </div>
                <?php endif; ?>

            </div>

            <!-- Right: POS Checkout Terminal (1 Col) -->
            <div class="bg-surface-container-lowest p-space-lg rounded-xl shadow-[0_1px_8px_rgba(0,0,0,0.04)] border border-outline-variant/30 flex flex-col justify-between h-fit">
                <form method="POST" action="../controllers/payment_action_controller.php" class="flex flex-col gap-4 text-sm" onsubmit="return validatePayment();">
                    <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token'] ?? '') ?>">
                    <input type="hidden" name="StudentID" value="<?= $selectedStudent['StudentID'] ?? '' ?>">
                    <input type="hidden" name="PaymentID" value="<?= $selectedStudent['PaymentID'] ?? '' ?>">
                    <input type="hidden" name="EnrollmentID" value="<?= $selectedStudent['EnrollmentID'] ?? '' ?>">
                    <input type="hidden" id="amount_due" name="Amount" value="<?= $totalDue ?>">

                    <div class="border-b border-surface-container pb-3">
                        <div class="flex items-center gap-2 text-primary font-semibold">
                            <i class="fa-solid fa-receipt text-[20px]"></i>
                            <span class="text-base">Cashier Settlement</span>
                        </div>
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-tertiary uppercase mb-1">Official Receipt (OR) Number</label>
                        <input type="text" name="ReceiptNo" value="<?= $nextReceiptNo ?>" required class="w-full h-10 px-3 rounded-lg bg-surface-container-low border border-surface-container text-sm font-mono font-bold text-primary focus:outline-none focus:border-primary"/>
                    </div>

                    <div class="p-3 bg-surface-container-low rounded-xl border border-surface-container">
                        <span class="text-xs text-tertiary uppercase font-semibold">Total Amount Due</span>
                        <div class="text-2xl font-black text-on-surface mt-1">
                            ₱<?= number_format($totalDue, 2) ?>
                        </div>
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-tertiary uppercase mb-1">Cash Tendered (₱)</label>
                        <input type="number" step="any" id="cash_tendered" placeholder="Enter cash received" required oninput="computeChange()" class="w-full h-11 px-3 rounded-lg bg-surface-container-low border border-surface-container text-base font-bold text-on-surface focus:outline-none focus:border-primary"/>
                    </div>

                    <div class="p-3 bg-emerald-50 rounded-xl border border-emerald-200">
                        <span class="text-xs text-emerald-800 uppercase font-semibold">Change Due</span>
                        <div id="change_due" class="text-xl font-black text-emerald-700 mt-1">
                            ₱0.00
                        </div>
                    </div>

                    <button type="submit" <?= !$selectedStudent ? 'disabled' : '' ?> class="w-full py-3 bg-primary hover:bg-primary-container disabled:opacity-40 disabled:cursor-not-allowed text-on-primary font-bold text-sm rounded-xl transition-all shadow-md flex items-center justify-center gap-2">
                        <i class="fa-solid fa-circle-check text-[20px]"></i>
                        <span>Process & Issue Receipt</span>
                    </button>
                </form>
            </div>

        </div>

    </div>
</main>

<script>
function computeChange() {
    const due = parseFloat(document.getElementById('amount_due').value) || 0;
    const tendered = parseFloat(document.getElementById('cash_tendered').value) || 0;
    const change = Math.max(0, tendered - due);
    document.getElementById('change_due').innerText = '₱' + change.toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
}

function validatePayment() {
    const due = parseFloat(document.getElementById('amount_due').value) || 0;
    const tendered = parseFloat(document.getElementById('cash_tendered').value) || 0;
    if (tendered < due) {
        alert("Amount tendered cannot be less than the total assessment due (₱" + due.toFixed(2) + ").");
        return false;
    }
    return true;
}
</script>

<?php include 'includes/footer.php'; ?>