<?php
session_start();
require_once '../config/db.php';

if (!isset($_SESSION['UserType']) || $_SESSION['UserType'] !== 'Staff') {
    header("Location: login.php");
    exit;
}
if (!in_array($_SESSION['Role'] ?? '', ['Registrar', 'Admin'])) {
    header("Location: staff_dashboard.php");
    exit;
}

$validationId = $_GET['id'] ?? null;

// Fetch pending queue
$queueStmt = $pdo->query("
    SELECT v.ValidationID, v.Status, s.StudentNo, s.FirstName, s.LastName
    FROM id_validation v
    JOIN student s ON v.StudentID = s.StudentID
    WHERE v.Status = 'Pending'
    ORDER BY v.ValidationDate ASC, v.ValidationID ASC
");
$queue = $queueStmt->fetchAll(PDO::FETCH_ASSOC);

if ($validationId) {
    $stmt = $pdo->prepare("
        SELECT v.ValidationID, v.ValidationDate, v.Status,
               s.StudentID, s.StudentNo, s.FirstName, s.LastName, s.MiddleName,
               l.Username, l.Status as LoginStatus
        FROM id_validation v
        JOIN student s ON v.StudentID = s.StudentID
        LEFT JOIN login l ON s.StudentID = l.StudentID
        WHERE v.ValidationID = ?
    ");
    $stmt->execute([$validationId]);
} else {
    $stmt = $pdo->query("
        SELECT v.ValidationID, v.ValidationDate, v.Status,
               s.StudentID, s.StudentNo, s.FirstName, s.LastName, s.MiddleName,
               l.Username, l.Status as LoginStatus
        FROM id_validation v
        JOIN student s ON v.StudentID = s.StudentID
        LEFT JOIN login l ON s.StudentID = l.StudentID
        WHERE v.Status = 'Pending'
        ORDER BY v.ValidationDate ASC, v.ValidationID ASC
        LIMIT 1
    ");
}
$val = $stmt->fetch(PDO::FETCH_ASSOC);

?>
<?php include 'includes/header.php'; ?>
<?php include 'includes/sidebar.php'; ?>
<div class="pl-72 flex flex-col min-h-screen">
<?php include 'includes/topbar.php'; ?>
    <main class="w-full pt-16 flex-1 px-gutter py-space-lg flex gap-space-lg">
        
        <!-- Queue Sidebar -->
        <aside class="w-80 bg-surface-container-lowest rounded-xl shadow-[0_1px_8px_rgba(0,0,0,0.04)] hidden lg:flex flex-col overflow-hidden h-[calc(100vh-140px)]">
            <div class="p-space-md bg-surface-container-low font-label-md text-label-md font-semibold text-tertiary uppercase tracking-wider border-b border-surface-container">
                Pending ID Verifications (<?= count($queue) ?>)
            </div>
            <div class="overflow-y-auto divide-y border-surface-container flex-1">
                <?php foreach($queue as $q): ?>
                <a href="id_validation.php?id=<?= $q['ValidationID'] ?>" class="block p-space-md hover:bg-surface-container-low transition-colors <?= ($val && $val['ValidationID'] == $q['ValidationID']) ? 'bg-primary-fixed/20 border-l-4 border-primary' : 'border-l-4 border-transparent' ?>">
                    <p class="font-title-md text-title-md text-on-surface"><?= htmlspecialchars($q['LastName'] . ', ' . $q['FirstName']) ?></p>
                    <p class="font-body-sm text-body-sm text-tertiary mt-1"><?= htmlspecialchars($q['StudentNo']) ?></p>
                </a>
                <?php endforeach; ?>
                <?php if(empty($queue)): ?>
                <div class="p-space-md text-body-sm font-body-sm text-tertiary text-center">No pending verifications.</div>
                <?php endif; ?>
            </div>
        </aside>

        <!-- Main Content -->
        <div class="flex-1 max-w-4xl">
            <?php if ($val): ?>
            <form action="../controllers/id_validation_action_controller.php" method="POST" class="flex flex-col gap-space-md" onsubmit="setTimeout(() => { const btns = this.querySelectorAll('button[type=submit]'); btns.forEach(b => b.disabled = true); }, 10); return true;">
                <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token'] ?? ''); ?>">
                <input type="hidden" name="ValidationID" value="<?= $val['ValidationID'] ?>">
                <input type="hidden" name="StudentID" value="<?= $val['StudentID'] ?>">
                
                <div class="bg-surface-container-lowest p-space-lg rounded-xl shadow-[0_1px_8px_rgba(0,0,0,0.04)] flex flex-col md:flex-row justify-between md:items-center gap-space-sm border border-surface-container">
                    <div>
                        <h2 class="font-headline-md text-headline-md text-on-surface">Identity & Credentials Verification</h2>
                        <p class="font-body-sm text-body-sm text-tertiary">Student No: <span class="font-bold text-primary"><?= htmlspecialchars($val['StudentNo']) ?></span></p>
                    </div>
                    <?php if ($val['Status'] === 'Pending'): ?>
                    <div class="flex gap-space-sm">
                        <button type="submit" name="action" value="Failed" class="px-space-md py-space-sm rounded-lg font-label-lg text-label-lg font-semibold text-error bg-error-container hover:bg-error/20 transition-colors">
                            Fail Verification
                        </button>
                        <button type="submit" name="action" value="Completed" class="px-space-md py-space-sm rounded-lg font-label-lg text-label-lg font-semibold text-on-primary bg-primary hover:bg-primary-container transition-colors shadow-sm">
                            Mark as Validated
                        </button>
                    </div>
                    <?php else: ?>
                    <div class="px-space-md py-space-xs rounded-lg font-label-md text-label-md font-semibold <?= $val['Status'] == 'Completed' ? 'bg-secondary-container/30 text-secondary' : 'bg-error-container text-error' ?>">
                        Status: <?= htmlspecialchars($val['Status']) ?>
                    </div>
                    <?php endif; ?>
                </div>

                <div class="bg-surface-container-lowest p-space-xl rounded-xl shadow-[0_1px_8px_rgba(0,0,0,0.04)] border border-surface-container">
                    <h3 class="font-title-lg text-title-lg font-semibold mb-space-lg border-b border-surface-container pb-space-xs">Student Information</h3>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-y-space-lg gap-x-space-md">
                        <div>
                            <label class="block font-label-sm text-label-sm font-bold text-tertiary uppercase tracking-wider mb-1">Full Name</label>
                            <p class="font-title-md text-title-md text-on-surface"><?= htmlspecialchars($val['FirstName'] . ' ' . $val['MiddleName'] . ' ' . $val['LastName']) ?></p>
                        </div>
                        <div>
                            <label class="block font-label-sm text-label-sm font-bold text-tertiary uppercase tracking-wider mb-1">Generated Username</label>
                            <p class="font-title-md text-title-md text-on-surface"><?= htmlspecialchars($val['Username'] ?? 'N/A') ?></p>
                        </div>
                        <div>
                            <label class="block font-label-sm text-label-sm font-bold text-tertiary uppercase tracking-wider mb-1">Account Status</label>
                            <p class="font-title-md text-title-md <?= $val['LoginStatus'] === 'Active' ? 'text-[oklch(0.6_0.15_140)]' : 'text-error' ?>"><?= htmlspecialchars($val['LoginStatus'] ?? 'Unknown') ?></p>
                        </div>
                        <div>
                            <label class="block font-label-sm text-label-sm font-bold text-tertiary uppercase tracking-wider mb-1">Validation Date</label>
                            <p class="font-title-md text-title-md text-on-surface"><?= htmlspecialchars($val['ValidationDate']) ?></p>
                        </div>
                    </div>

                    <?php if ($val['Status'] === 'Pending'): ?>
                    <div class="mt-space-xl p-space-md bg-surface-container-low rounded-xl border border-surface-container">
                        <div class="flex gap-space-sm text-primary">
                            <i class="fa-solid fa-circle-info text-[24px]"></i>
                            <div>
                                <p class="font-title-sm text-title-sm font-bold">Instructions for Staff:</p>
                                <p class="font-body-sm text-body-sm text-tertiary mt-1">Please verify the physical documents (Birth Certificate, Form 138/137, Good Moral, ID Pictures) submitted by the student against the encoded data. Once verified, click "Mark as Validated" to activate the student's portal account.</p>
                            </div>
                        </div>
                    </div>
                    <?php endif; ?>
                </div>
            </form>
            <?php else: ?>
                <div class="bg-surface-container-lowest p-space-xl rounded-xl shadow-[0_1px_8px_rgba(0,0,0,0.04)] border border-surface-container text-center flex flex-col items-center justify-center min-h-[400px]">
                    <div class="w-16 h-16 bg-surface-container rounded-full flex items-center justify-center text-tertiary mb-space-sm">
                        <i class="fa-solid fa-clipboard-check text-[32px]"></i>
                    </div>
                    <h2 class="font-headline-md text-headline-md text-on-surface mb-1">No Verifications Pending</h2>
                    <p class="font-body-md text-body-md text-tertiary mb-space-lg">All student identities have been verified.</p>
                    <a href="staff_dashboard.php" class="px-space-md py-space-sm bg-primary text-on-primary rounded-lg font-label-lg text-label-lg font-semibold hover:bg-primary-container transition-colors shadow-sm">Return to Dashboard</a>
                </div>
            <?php endif; ?>
        </div>
    </main>
</div>
<?php include 'includes/footer.php'; ?>
