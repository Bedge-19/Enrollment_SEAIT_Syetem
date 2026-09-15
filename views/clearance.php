<?php
session_start();
require_once '../config/db.php';

if (!isset($_SESSION['UserType']) || $_SESSION['UserType'] !== 'Staff') {
    header("Location: login.php");
    exit;
}
if (!in_array($_SESSION['Role'] ?? '', ['Clinic', 'Admin'])) {
    header("Location: staff_dashboard.php");
    exit;
}

$studentId = $_GET['id'] ?? null;

// Fetch pending queue for clearance (students who have pending clinic OR clearance)
$queueStmt = $pdo->query("
    SELECT DISTINCT s.StudentID, s.StudentNo, s.FirstName, s.LastName
    FROM student s
    LEFT JOIN clinic cl ON s.StudentID = cl.StudentID
    LEFT JOIN clearance c ON s.StudentID = c.StudentID
    WHERE cl.Status = 'Pending' OR c.Status = 'Pending'
    ORDER BY s.StudentID ASC
");
$queue = $queueStmt->fetchAll(PDO::FETCH_ASSOC);

if ($studentId) {
    $stmt = $pdo->prepare("
        SELECT s.StudentID, s.StudentNo, s.FirstName, s.LastName,
               cl.ClinicID, cl.Status as ClinicStatus,
               c.ClearanceID, c.Status as ClearanceStatus,
               e.EnrollmentID, e.Status as EnrollmentStatus,
               p.PaymentStatus
        FROM student s
        LEFT JOIN clinic cl ON s.StudentID = cl.StudentID
        LEFT JOIN clearance c ON s.StudentID = c.StudentID
        LEFT JOIN enrollment e ON s.StudentID = e.StudentID
        LEFT JOIN payment p ON s.StudentID = p.StudentID
        WHERE s.StudentID = ?
    ");
    $stmt->execute([$studentId]);
} else {
    $stmt = $pdo->query("
        SELECT s.StudentID, s.StudentNo, s.FirstName, s.LastName,
               cl.ClinicID, cl.Status as ClinicStatus,
               c.ClearanceID, c.Status as ClearanceStatus,
               e.EnrollmentID, e.Status as EnrollmentStatus,
               p.PaymentStatus
        FROM student s
        LEFT JOIN clinic cl ON s.StudentID = cl.StudentID
        LEFT JOIN clearance c ON s.StudentID = c.StudentID
        LEFT JOIN enrollment e ON s.StudentID = e.StudentID
        LEFT JOIN payment p ON s.StudentID = p.StudentID
        WHERE cl.Status = 'Pending' OR c.Status = 'Pending'
        LIMIT 1
    ");
}
$clear = $stmt->fetch(PDO::FETCH_ASSOC);

?>
<?php include 'includes/header.php'; ?>
<?php include 'includes/sidebar.php'; ?>
<div class="pl-72 flex flex-col min-h-screen">
<?php include 'includes/topbar.php'; ?>
    <main class="w-full pt-16 flex-1 px-gutter py-space-lg flex gap-space-lg">
        
        <!-- Queue Sidebar -->
        <aside class="w-80 bg-surface-container-lowest rounded-xl shadow-[0_1px_8px_rgba(0,0,0,0.04)] hidden lg:flex flex-col overflow-hidden h-[calc(100vh-140px)]">
            <div class="p-space-md bg-surface-container-low font-label-md text-label-md font-semibold text-tertiary uppercase tracking-wider border-b border-surface-container">
                Pending Clearances (<?= count($queue) ?>)
            </div>
            <div class="overflow-y-auto divide-y border-surface-container flex-1">
                <?php foreach($queue as $q): ?>
                <a href="clearance.php?id=<?= $q['StudentID'] ?>" class="block p-space-md hover:bg-surface-container-low transition-colors <?= ($clear && $clear['StudentID'] == $q['StudentID']) ? 'bg-primary-fixed/20 border-l-4 border-primary' : 'border-l-4 border-transparent' ?>">
                    <p class="font-title-md text-title-md text-on-surface"><?= htmlspecialchars($q['LastName'] . ', ' . $q['FirstName']) ?></p>
                    <p class="font-body-sm text-body-sm text-tertiary mt-1"><?= htmlspecialchars($q['StudentNo']) ?></p>
                </a>
                <?php endforeach; ?>
                <?php if(empty($queue)): ?>
                <div class="p-space-md text-body-sm font-body-sm text-tertiary text-center">No pending clearances.</div>
                <?php endif; ?>
            </div>
        </aside>

        <!-- Main Content -->
        <div class="flex-1 max-w-4xl">
            <?php if ($clear): ?>
            <form action="../controllers/clearance_action_controller.php" method="POST" class="flex flex-col gap-space-md" onsubmit="setTimeout(() => { const btns = this.querySelectorAll('button[type=submit]'); btns.forEach(b => b.disabled = true); }, 10); return true;">
                <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token'] ?? ''); ?>">
                <input type="hidden" name="StudentID" value="<?= $clear['StudentID'] ?>">
                <input type="hidden" name="ClinicID" value="<?= $clear['ClinicID'] ?>">
                <input type="hidden" name="ClearanceID" value="<?= $clear['ClearanceID'] ?>">
                <input type="hidden" name="EnrollmentID" value="<?= $clear['EnrollmentID'] ?>">
                
                <div class="bg-surface-container-lowest p-space-lg rounded-xl shadow-[0_1px_8px_rgba(0,0,0,0.04)] flex flex-col md:flex-row justify-between md:items-center gap-space-sm border border-surface-container">
                    <div>
                        <h2 class="font-headline-md text-headline-md text-on-surface">Process Clearances</h2>
                        <p class="font-body-sm text-body-sm text-tertiary">Student No: <span class="font-bold text-primary"><?= htmlspecialchars($clear['StudentNo']) ?></span></p>
                    </div>
                    <div class="flex gap-space-sm">
                        <button type="submit" class="px-space-md py-space-sm rounded-lg font-label-lg text-label-lg font-semibold text-on-primary bg-primary hover:bg-primary-container transition-colors shadow-sm">
                            Update Clearance Status
                        </button>
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-space-md">
                    <!-- Medical Exam / Clinic -->
                    <div class="bg-surface-container-lowest p-space-lg rounded-xl shadow-[0_1px_8px_rgba(0,0,0,0.04)] border border-surface-container">
                        <h3 class="font-title-lg text-title-lg font-semibold mb-space-md flex items-center gap-2"><i class="fa-solid fa-hospital text-primary text-[24px]"></i> Medical Clearance</h3>
                        <?php if ($clear['ClinicStatus'] === 'Completed'): ?>
                            <div class="p-space-sm bg-[#e8f5e9] text-[#1b5e20] font-label-lg text-label-lg font-bold rounded-lg border border-[#c8e6c9]">Completed</div>
                            <input type="hidden" name="ClinicStatus" value="Completed">
                        <?php else: ?>
                            <select name="ClinicStatus" class="w-full bg-surface-container-low border border-surface-container rounded-lg p-space-sm font-body-sm text-body-sm outline-none focus:border-primary focus:ring-1 focus:ring-primary transition-all text-on-surface">
                                <option value="Pending" <?= $clear['ClinicStatus'] == 'Pending' ? 'selected' : '' ?>>Pending</option>
                                <option value="Completed">Completed (Passed)</option>
                                <option value="Failed">Failed</option>
                            </select>
                        <?php endif; ?>
                    </div>

                    <!-- General Clearance -->
                    <div class="bg-surface-container-lowest p-space-lg rounded-xl shadow-[0_1px_8px_rgba(0,0,0,0.04)] border border-surface-container">
                        <h3 class="font-title-lg text-title-lg font-semibold mb-space-md flex items-center gap-2"><i class="fa-solid fa-list-check text-primary text-[24px]"></i> Institutional Clearance</h3>
                        <?php if ($clear['ClearanceStatus'] === 'Cleared'): ?>
                            <div class="p-space-sm bg-[#e8f5e9] text-[#1b5e20] font-label-lg text-label-lg font-bold rounded-lg border border-[#c8e6c9]">Cleared</div>
                            <input type="hidden" name="ClearanceStatus" value="Cleared">
                        <?php else: ?>
                            <select name="ClearanceStatus" class="w-full bg-surface-container-low border border-surface-container rounded-lg p-space-sm font-body-sm text-body-sm outline-none focus:border-primary focus:ring-1 focus:ring-primary transition-all text-on-surface">
                                <option value="Pending" <?= $clear['ClearanceStatus'] == 'Pending' ? 'selected' : '' ?>>Pending</option>
                                <option value="Cleared">Cleared</option>
                                <option value="Not Cleared">Not Cleared</option>
                            </select>
                        <?php endif; ?>
                    </div>
                </div>

                <div class="bg-surface-container-low p-space-md rounded-xl border border-surface-container font-body-sm text-body-sm text-tertiary">
                    <p><strong class="text-on-surface">Note on Final Enrollment:</strong> The student will only be marked as officially 'Enrolled' if they are cleared medically, institutionally, AND their payment is marked as 'Paid'. Currently, their Payment status is: <strong class="text-on-surface"><?= htmlspecialchars($clear['PaymentStatus'] ?? 'Not Created') ?></strong></p>
                </div>
            </form>
            <?php else: ?>
                <div class="bg-surface-container-lowest p-space-xl rounded-xl shadow-[0_1px_8px_rgba(0,0,0,0.04)] border border-surface-container text-center flex flex-col items-center justify-center min-h-[400px]">
                    <div class="w-16 h-16 bg-surface-container rounded-full flex items-center justify-center text-tertiary mb-space-sm">
                        <i class="fa-solid fa-circle-check text-[32px]"></i>
                    </div>
                    <h2 class="font-headline-md text-headline-md text-on-surface mb-1">No Pending Clearances</h2>
                    <p class="font-body-md text-body-md text-tertiary mb-space-lg">All student clearances have been updated.</p>
                    <a href="staff_dashboard.php" class="px-space-md py-space-sm bg-primary text-on-primary rounded-lg font-label-lg text-label-lg font-semibold hover:bg-primary-container transition-colors shadow-sm">Return to Dashboard</a>
                </div>
            <?php endif; ?>
        </div>
    </main>
</div>
<?php include 'includes/footer.php'; ?>
