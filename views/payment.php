<?php
session_start();
require_once '../config/db.php';

if (!isset($_SESSION['UserType']) || $_SESSION['UserType'] !== 'Staff') {
    header("Location: login.php");
    exit;
}
if (!in_array($_SESSION['Role'] ?? '', ['Accounting', 'Admin'])) {
    header("Location: staff_dashboard.php");
    exit;
}

$studentId = $_GET['id'] ?? null;

// Fetch pending queue for payments
$queueStmt = $pdo->query("
    SELECT p.PaymentID, p.PaymentStatus, s.StudentID, s.StudentNo, s.FirstName, s.LastName
    FROM payment p
    JOIN student s ON p.StudentID = s.StudentID
    WHERE p.PaymentStatus = 'Pending'
    ORDER BY p.PaymentDate ASC, p.PaymentID ASC
");
$queue = $queueStmt->fetchAll(PDO::FETCH_ASSOC);

if ($studentId) {
    $stmt = $pdo->prepare("
        SELECT p.PaymentID, p.PaymentDate, p.PaymentStatus, p.Amount, p.ReceiptNo,
               s.StudentID, s.StudentNo, s.FirstName, s.LastName,
               e.EnrollmentID, e.Status as EnrollmentStatus
        FROM payment p
        JOIN student s ON p.StudentID = s.StudentID
        LEFT JOIN enrollment e ON s.StudentID = e.StudentID
        WHERE s.StudentID = ? AND p.PaymentStatus = 'Pending'
    ");
    $stmt->execute([$studentId]);
} else {
    $stmt = $pdo->query("
        SELECT p.PaymentID, p.PaymentDate, p.PaymentStatus, p.Amount, p.ReceiptNo,
               s.StudentID, s.StudentNo, s.FirstName, s.LastName,
               e.EnrollmentID, e.Status as EnrollmentStatus
        FROM payment p
        JOIN student s ON p.StudentID = s.StudentID
        LEFT JOIN enrollment e ON s.StudentID = e.StudentID
        WHERE p.PaymentStatus = 'Pending'
        ORDER BY p.PaymentDate ASC, p.PaymentID ASC
        LIMIT 1
    ");
}
$pay = $stmt->fetch(PDO::FETCH_ASSOC);

?>
<?php include 'includes/header.php'; ?>
<?php include 'includes/sidebar.php'; ?>
<div class="pl-72 flex flex-col min-h-screen">
<?php include 'includes/topbar.php'; ?>
    <main class="w-full pt-16 flex-1 px-gutter py-space-lg flex gap-space-lg">
        
        <!-- Queue Sidebar -->
        <aside class="w-80 bg-surface-container-lowest rounded-xl shadow-[0_1px_8px_rgba(0,0,0,0.04)] hidden lg:flex flex-col overflow-hidden h-[calc(100vh-140px)]">
            <div class="p-space-md bg-surface-container-low font-label-md text-label-md font-semibold text-tertiary uppercase tracking-wider border-b border-surface-container">
                Pending Payments (<?= count($queue) ?>)
            </div>
            <div class="overflow-y-auto divide-y border-surface-container flex-1">
                <?php foreach($queue as $q): ?>
                <a href="payment.php?id=<?= $q['StudentID'] ?>" class="block p-space-md hover:bg-surface-container-low transition-colors <?= ($pay && $pay['StudentID'] == $q['StudentID']) ? 'bg-primary-fixed/20 border-l-4 border-primary' : 'border-l-4 border-transparent' ?>">
                    <p class="font-title-md text-title-md text-on-surface"><?= htmlspecialchars($q['LastName'] . ', ' . $q['FirstName']) ?></p>
                    <p class="font-body-sm text-body-sm text-tertiary mt-1"><?= htmlspecialchars($q['StudentNo']) ?></p>
                </a>
                <?php endforeach; ?>
                <?php if(empty($queue)): ?>
                <div class="p-space-md text-body-sm font-body-sm text-tertiary text-center">No pending payments.</div>
                <?php endif; ?>
            </div>
        </aside>

        <!-- Main Content -->
        <div class="flex-1 max-w-4xl">
            <?php if ($pay): ?>
            <form action="../controllers/payment_action_controller.php" method="POST" class="flex flex-col gap-space-md" onsubmit="setTimeout(() => { const btns = this.querySelectorAll('button[type=submit]'); btns.forEach(b => b.disabled = true); }, 10); return true;">
                <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token'] ?? ''); ?>">
                <input type="hidden" name="PaymentID" value="<?= $pay['PaymentID'] ?>">
                <input type="hidden" name="StudentID" value="<?= $pay['StudentID'] ?>">
                <input type="hidden" name="EnrollmentID" value="<?= $pay['EnrollmentID'] ?>">
                
                <div class="bg-surface-container-lowest p-space-lg rounded-xl shadow-[0_1px_8px_rgba(0,0,0,0.04)] flex flex-col md:flex-row justify-between md:items-center gap-space-sm border border-surface-container">
                    <div>
                        <h2 class="font-headline-md text-headline-md text-on-surface">Process Enrollment Payment</h2>
                        <p class="font-body-sm text-body-sm text-tertiary">Student No: <span class="font-bold text-primary"><?= htmlspecialchars($pay['StudentNo']) ?></span></p>
                    </div>
                    <div class="flex gap-space-sm">
                        <button type="submit" class="px-space-md py-space-sm rounded-lg font-label-lg text-label-lg font-semibold text-on-primary bg-primary hover:bg-primary-container transition-colors shadow-sm flex items-center gap-2">
                            <i class="fa-solid fa-money-bill-wave text-[20px]"></i> Process Payment & Complete Enrollment
                        </button>
                    </div>
                </div>

                <div class="bg-surface-container-lowest p-space-md rounded-xl shadow-[0_1px_8px_rgba(0,0,0,0.04)] border border-surface-container">
                    <h3 class="font-title-lg text-title-lg font-semibold mb-space-md border-b border-surface-container pb-space-xs">Payment Details</h3>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-space-md">
                        <div>
                            <label class="block font-label-sm text-label-sm font-bold text-tertiary uppercase tracking-wider mb-2">Amount Paid</label>
                            <div class="relative">
                                <span class="absolute left-3 top-1/2 -translate-y-1/2 text-tertiary font-bold">₱</span>
                                <input type="number" step="0.01" name="Amount" class="w-full pl-8 bg-surface-container-low border border-surface-container rounded-lg p-space-sm font-body-sm text-body-sm outline-none focus:border-primary focus:ring-1 focus:ring-primary transition-all text-on-surface" required placeholder="0.00">
                            </div>
                        </div>
                        <div>
                            <label class="block font-label-sm text-label-sm font-bold text-tertiary uppercase tracking-wider mb-2">Receipt Number</label>
                            <input type="text" name="ReceiptNo" class="w-full bg-surface-container-low border border-surface-container rounded-lg p-space-sm font-body-sm text-body-sm outline-none focus:border-primary focus:ring-1 focus:ring-primary transition-all text-on-surface" required placeholder="OR-123456">
                        </div>
                    </div>
                </div>

                <div class="bg-surface-container-low p-space-md rounded-xl border border-surface-container font-body-sm text-body-sm text-tertiary flex gap-3">
                    <i class="fa-solid fa-circle-info text-primary mt-1"></i>
                    <p>Completing this payment will officially change the student's status to <strong class="text-on-surface">Enrolled</strong>, provided that medical and institutional clearances are also marked as Completed.</p>
                </div>
            </form>
            <?php else: ?>
                <div class="bg-surface-container-lowest p-space-xl rounded-xl shadow-[0_1px_8px_rgba(0,0,0,0.04)] border border-surface-container text-center flex flex-col items-center justify-center min-h-[400px]">
                    <div class="w-16 h-16 bg-surface-container rounded-full flex items-center justify-center text-tertiary mb-space-sm">
                        <i class="fa-solid fa-money-bill-wave text-[32px]"></i>
                    </div>
                    <h2 class="font-headline-md text-headline-md text-on-surface mb-1">No Pending Payments</h2>
                    <p class="font-body-md text-body-md text-tertiary mb-space-lg">All accounts are settled.</p>
                    <a href="staff_dashboard.php" class="px-space-md py-space-sm bg-primary text-on-primary rounded-lg font-label-lg text-label-lg font-semibold hover:bg-primary-container transition-colors shadow-sm">Return to Dashboard</a>
                </div>
            <?php endif; ?>
        </div>
    </main>
</div>
<?php include 'includes/footer.php'; ?>
