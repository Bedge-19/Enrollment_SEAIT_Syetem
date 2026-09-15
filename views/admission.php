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

$admissionId = $_GET['id'] ?? null;

if ($admissionId) {
    $stmt = $pdo->prepare("
        SELECT a.AdmissionID, a.SubmissionDate, a.Status, a.Remarks, 
               s.StudentID, s.StudentNo, s.FirstName, s.LastName, s.MiddleName, s.Sex, s.BirthDate, s.Address as BasicAddress, s.ContactNo as BasicContact, s.Email,
               p.ProgramName, t.TypeName as StudentType,
               sp.Address as ProfileAddress, sp.ContactNo as ProfileContact, sp.GuardianName, sp.GuardianContactNo, 
               sp.PreviousSchoolName, sp.PreviousProgram, sp.LastYearLevelCompleted, sp.GWA
        FROM admission a
        JOIN student s ON a.StudentID = s.StudentID
        LEFT JOIN student_profile sp ON s.StudentID = sp.StudentID
        LEFT JOIN program p ON s.ProgramID = p.ProgramID
        LEFT JOIN student_type t ON s.StudentTypeID = t.StudentTypeID
        WHERE a.AdmissionID = ?
    ");
    $stmt->execute([$admissionId]);
} else {
    $stmt = $pdo->query("
        SELECT a.AdmissionID, a.SubmissionDate, a.Status, a.Remarks, 
               s.StudentID, s.StudentNo, s.FirstName, s.LastName, s.MiddleName, s.Sex, s.BirthDate, s.Address as BasicAddress, s.ContactNo as BasicContact, s.Email,
               p.ProgramName, t.TypeName as StudentType,
               sp.Address as ProfileAddress, sp.ContactNo as ProfileContact, sp.GuardianName, sp.GuardianContactNo, 
               sp.PreviousSchoolName, sp.PreviousProgram, sp.LastYearLevelCompleted, sp.GWA
        FROM admission a
        JOIN student s ON a.StudentID = s.StudentID
        LEFT JOIN student_profile sp ON s.StudentID = sp.StudentID
        LEFT JOIN program p ON s.ProgramID = p.ProgramID
        LEFT JOIN student_type t ON s.StudentTypeID = t.StudentTypeID
        WHERE a.Status = 'Pending'
        ORDER BY a.SubmissionDate ASC, a.AdmissionID ASC
        LIMIT 1
    ");
}
$app = $stmt->fetch(PDO::FETCH_ASSOC);

// Also fetch the queue list for the sidebar
$queueStmt = $pdo->query("
    SELECT a.AdmissionID, s.LastName, s.FirstName, p.ProgramName, s.StudentNo
    FROM admission a
    JOIN student s ON a.StudentID = s.StudentID
    LEFT JOIN program p ON s.ProgramID = p.ProgramID
    WHERE a.Status = 'Pending'
    ORDER BY a.SubmissionDate ASC, a.AdmissionID ASC
");
$queue = $queueStmt->fetchAll(PDO::FETCH_ASSOC);
?>
<?php include 'includes/header.php'; ?>
<?php include 'includes/sidebar.php'; ?>
<div class="pl-72 flex flex-col min-h-screen">
<?php include 'includes/topbar.php'; ?>
    <main class="w-full pt-16 flex-1 px-gutter py-space-lg flex gap-space-lg">
        
        <!-- Queue Sidebar -->
        <aside class="w-80 bg-surface-container-lowest rounded-xl shadow-[0_1px_8px_rgba(0,0,0,0.04)] hidden lg:flex flex-col overflow-hidden h-[calc(100vh-140px)]">
            <div class="p-space-md bg-surface-container-low font-label-md text-label-md font-semibold text-tertiary uppercase tracking-wider border-b border-surface-container">
                Pending Queue (<?= count($queue) ?>)
            </div>
            <div class="overflow-y-auto divide-y border-surface-container flex-1">
                <?php foreach($queue as $q): ?>
                <a href="admission.php?id=<?= $q['AdmissionID'] ?>" class="block p-space-md hover:bg-surface-container-low transition-colors <?= ($app && $app['AdmissionID'] == $q['AdmissionID']) ? 'bg-primary-fixed/20 border-l-4 border-primary' : 'border-l-4 border-transparent' ?>">
                    <p class="font-title-md text-title-md text-on-surface"><?= htmlspecialchars($q['LastName'] . ', ' . $q['FirstName']) ?></p>
                    <p class="font-body-sm text-body-sm text-tertiary mt-1"><?= htmlspecialchars($q['StudentNo']) ?> • <?= htmlspecialchars($q['ProgramName']) ?></p>
                </a>
                <?php endforeach; ?>
                <?php if(empty($queue)): ?>
                <div class="p-space-md text-body-sm font-body-sm text-tertiary text-center">No pending applications.</div>
                <?php endif; ?>
            </div>
        </aside>

        <!-- Main Content -->
        <div class="flex-1 max-w-5xl">
            <?php if ($app): ?>
            <form action="../controllers/admission_action_controller.php" method="POST" class="flex flex-col gap-space-md" onsubmit="setTimeout(() => { const btns = this.querySelectorAll('button[type=submit]'); btns.forEach(b => b.disabled = true); }, 10); return true;">
                <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token'] ?? ''); ?>">
                <input type="hidden" name="AdmissionID" value="<?= $app['AdmissionID'] ?>">
                
                <!-- Top Actions -->
                <div class="bg-surface-container-lowest p-space-lg rounded-xl shadow-[0_1px_8px_rgba(0,0,0,0.04)] flex flex-col md:flex-row justify-between md:items-center gap-space-sm">
                    <div>
                        <h2 class="font-headline-md text-headline-md text-on-surface">Review Application</h2>
                        <p class="font-body-sm text-body-sm text-tertiary">Ref: <?= htmlspecialchars($app['StudentNo']) ?> • Submitted on <?= htmlspecialchars($app['SubmissionDate']) ?></p>
                    </div>
                    <?php if ($app['Status'] === 'Pending'): ?>
                    <div class="flex gap-space-sm">
                        <button type="submit" name="action" value="Reject" class="px-space-md py-space-sm rounded-lg font-label-lg text-label-lg font-semibold text-error bg-error-container hover:bg-error/20 transition-colors">
                            Reject
                        </button>
                        <button type="submit" name="action" value="Approve" class="px-space-md py-space-sm rounded-lg font-label-lg text-label-lg font-semibold text-on-primary bg-primary hover:bg-primary-container transition-colors shadow-sm">
                            Approve Admission
                        </button>
                    </div>
                    <?php else: ?>
                    <div class="px-space-md py-space-xs rounded-lg font-label-md text-label-md font-semibold <?= $app['Status'] == 'Approved' ? 'bg-secondary-container/30 text-secondary' : 'bg-error-container text-error' ?>">
                        Status: <?= htmlspecialchars($app['Status']) ?>
                    </div>
                    <?php endif; ?>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-space-md">
                    <!-- Personal Info -->
                    <div class="bg-surface-container-lowest p-space-md rounded-xl shadow-[0_1px_8px_rgba(0,0,0,0.04)] border border-surface-container">
                        <div class="flex items-center gap-space-xs mb-space-sm text-primary border-b border-surface-container pb-space-xs">
                            <i class="fa-solid fa-user text-[20px]"></i>
                            <h3 class="font-title-md text-title-md font-semibold">Personal Details</h3>
                        </div>
                        <dl class="flex flex-col gap-space-xs font-body-sm text-body-sm">
                            <div class="grid grid-cols-3"><dt class="text-tertiary">Full Name</dt><dd class="col-span-2 font-medium text-on-surface"><?= htmlspecialchars($app['FirstName'] . ' ' . $app['MiddleName'] . ' ' . $app['LastName']) ?></dd></div>
                            <div class="grid grid-cols-3"><dt class="text-tertiary">Sex</dt><dd class="col-span-2 text-on-surface"><?= htmlspecialchars($app['Sex']) ?></dd></div>
                            <div class="grid grid-cols-3"><dt class="text-tertiary">Birth Date</dt><dd class="col-span-2 text-on-surface"><?= htmlspecialchars($app['BirthDate']) ?></dd></div>
                            <div class="grid grid-cols-3"><dt class="text-tertiary">Email</dt><dd class="col-span-2 text-on-surface"><?= htmlspecialchars($app['Email']) ?></dd></div>
                            <div class="grid grid-cols-3"><dt class="text-tertiary">Contact</dt><dd class="col-span-2 text-on-surface"><?= htmlspecialchars($app['BasicContact']) ?></dd></div>
                            <div class="grid grid-cols-3"><dt class="text-tertiary">Address</dt><dd class="col-span-2 text-on-surface"><?= htmlspecialchars($app['BasicAddress']) ?></dd></div>
                        </dl>
                    </div>

                    <!-- Academic Info -->
                    <div class="bg-surface-container-lowest p-space-md rounded-xl shadow-[0_1px_8px_rgba(0,0,0,0.04)] border border-surface-container">
                        <div class="flex items-center gap-space-xs mb-space-sm text-primary border-b border-surface-container pb-space-xs">
                            <i class="fa-solid fa-graduation-cap text-[20px]"></i>
                            <h3 class="font-title-md text-title-md font-semibold">Academic Profile</h3>
                        </div>
                        <dl class="flex flex-col gap-space-xs font-body-sm text-body-sm">
                            <div class="grid grid-cols-3"><dt class="text-tertiary">Program</dt><dd class="col-span-2 font-bold text-primary"><?= htmlspecialchars($app['ProgramName']) ?></dd></div>
                            <div class="grid grid-cols-3"><dt class="text-tertiary">Applicant Type</dt><dd class="col-span-2 text-on-surface"><?= htmlspecialchars($app['StudentType']) ?></dd></div>
                            <div class="grid grid-cols-3"><dt class="text-tertiary">Previous School</dt><dd class="col-span-2 text-on-surface"><?= htmlspecialchars($app['PreviousSchoolName']) ?></dd></div>
                            <div class="grid grid-cols-3"><dt class="text-tertiary">Prev Program</dt><dd class="col-span-2 text-on-surface"><?= htmlspecialchars($app['PreviousProgram']) ?></dd></div>
                            <div class="grid grid-cols-3"><dt class="text-tertiary">Last Lvl/Yr</dt><dd class="col-span-2 text-on-surface"><?= htmlspecialchars($app['LastYearLevelCompleted']) ?></dd></div>
                            <div class="grid grid-cols-3"><dt class="text-tertiary">GWA</dt><dd class="col-span-2 font-semibold text-on-surface"><?= htmlspecialchars($app['GWA']) ?></dd></div>
                        </dl>
                    </div>
                    
                    <!-- Guardian Info -->
                    <div class="bg-surface-container-lowest p-space-md rounded-xl shadow-[0_1px_8px_rgba(0,0,0,0.04)] border border-surface-container md:col-span-2">
                        <div class="flex items-center gap-space-xs mb-space-sm text-primary border-b border-surface-container pb-space-xs">
                            <i class="fa-solid fa-people-roof text-[20px]"></i>
                            <h3 class="font-title-md text-title-md font-semibold">Emergency Contact & Guardian</h3>
                        </div>
                        <dl class="grid grid-cols-1 md:grid-cols-2 gap-space-sm font-body-sm text-body-sm">
                            <div class="grid grid-cols-3"><dt class="text-tertiary">Name</dt><dd class="col-span-2 font-medium text-on-surface"><?= htmlspecialchars($app['GuardianName'] ?? 'N/A') ?></dd></div>
                            <div class="grid grid-cols-3"><dt class="text-tertiary">Contact No</dt><dd class="col-span-2 text-on-surface"><?= htmlspecialchars($app['GuardianContactNo'] ?? 'N/A') ?></dd></div>
                        </dl>
                    </div>

                    <?php if ($app['Status'] === 'Pending'): ?>
                    <div class="bg-surface-container-lowest p-space-md rounded-xl shadow-[0_1px_8px_rgba(0,0,0,0.04)] border border-surface-container md:col-span-2">
                        <label class="block font-label-md text-label-md text-on-surface mb-space-xs">Registrar Remarks / Internal Notes</label>
                        <textarea name="Remarks" rows="3" class="w-full bg-surface-container-low border border-surface-container rounded-lg p-space-sm font-body-sm text-body-sm outline-none focus:border-primary focus:ring-1 focus:ring-primary transition-all" placeholder="Optional notes about this approval or rejection..."></textarea>
                    </div>
                    <?php endif; ?>
                </div>
            </form>
            <?php else: ?>
                <div class="bg-surface-container-lowest p-space-xl rounded-xl shadow-[0_1px_8px_rgba(0,0,0,0.04)] border border-surface-container text-center flex flex-col items-center justify-center min-h-[400px]">
                    <div class="w-16 h-16 bg-surface-container rounded-full flex items-center justify-center text-tertiary mb-space-sm">
                        <i class="fa-solid fa-inbox text-[32px]"></i>
                    </div>
                    <h2 class="font-headline-md text-headline-md text-on-surface mb-1">Queue is Empty</h2>
                    <p class="font-body-md text-body-md text-tertiary mb-space-lg">There are no pending admission applications to review right now.</p>
                    <a href="staff_dashboard.php" class="px-space-md py-space-sm bg-primary text-on-primary rounded-lg font-label-lg text-label-lg font-semibold hover:bg-primary-container transition-colors shadow-sm">Return to Dashboard</a>
                </div>
            <?php endif; ?>
        </div>
    </main>
</div>
<?php include 'includes/footer.php'; ?>