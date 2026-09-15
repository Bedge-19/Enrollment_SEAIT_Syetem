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

$evaluationId = $_GET['id'] ?? null;

// Fetch pending queue
$queueStmt = $pdo->query("
    SELECT e.EvaluationID, s.StudentNo, s.FirstName, s.LastName
    FROM evaluation e
    JOIN student s ON e.StudentID = s.StudentID
    WHERE e.Status = 'Pending'
    ORDER BY e.EvaluationDate ASC, e.EvaluationID ASC
");
$queue = $queueStmt->fetchAll(PDO::FETCH_ASSOC);

if ($evaluationId) {
    $stmt = $pdo->prepare("
        SELECT e.EvaluationID, e.EvaluationDate, e.Status, e.Remarks,
               s.StudentID, s.StudentNo, s.FirstName, s.LastName, s.ProgramID,
               p.ProgramName, t.TypeName as StudentType,
               sp.PreviousSchoolName, sp.GWA
        FROM evaluation e
        JOIN student s ON e.StudentID = s.StudentID
        LEFT JOIN program p ON s.ProgramID = p.ProgramID
        LEFT JOIN student_type t ON s.StudentTypeID = t.StudentTypeID
        LEFT JOIN student_profile sp ON s.StudentID = sp.StudentID
        WHERE e.EvaluationID = ?
    ");
    $stmt->execute([$evaluationId]);
} else {
    $stmt = $pdo->query("
        SELECT e.EvaluationID, e.EvaluationDate, e.Status, e.Remarks,
               s.StudentID, s.StudentNo, s.FirstName, s.LastName, s.ProgramID,
               p.ProgramName, t.TypeName as StudentType,
               sp.PreviousSchoolName, sp.GWA
        FROM evaluation e
        JOIN student s ON e.StudentID = s.StudentID
        LEFT JOIN program p ON s.ProgramID = p.ProgramID
        LEFT JOIN student_type t ON s.StudentTypeID = t.StudentTypeID
        LEFT JOIN student_profile sp ON s.StudentID = sp.StudentID
        WHERE e.Status = 'Pending'
        ORDER BY e.EvaluationDate ASC, e.EvaluationID ASC
        LIMIT 1
    ");
}
$eval = $stmt->fetch(PDO::FETCH_ASSOC);

$subjects = [];
if ($eval) {
    // Fetch subjects for this program
    $subStmt = $pdo->prepare("SELECT SubjectID, SubjectCode, SubjectTitle, Units FROM subject WHERE ProgramID = ?");
    $subStmt->execute([$eval['ProgramID']]);
    $subjects = $subStmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Fetch already evaluated subjects if not pending
    $evalSubStmt = $pdo->prepare("SELECT SubjectID, SubjectStatus FROM evaluation_subject WHERE EvaluationID = ?");
    $evalSubStmt->execute([$eval['EvaluationID']]);
    $evaluated = [];
    foreach($evalSubStmt->fetchAll(PDO::FETCH_ASSOC) as $row) {
        $evaluated[$row['SubjectID']] = $row['SubjectStatus'];
    }
}
?>
<?php include 'includes/header.php'; ?>
<?php include 'includes/sidebar.php'; ?>
<div class="pl-72 flex flex-col min-h-screen">
<?php include 'includes/topbar.php'; ?>
    <main class="w-full pt-16 flex-1 px-gutter py-space-lg flex gap-space-lg">
        
        <!-- Queue Sidebar -->
        <aside class="w-80 bg-surface-container-lowest rounded-xl shadow-[0_1px_8px_rgba(0,0,0,0.04)] hidden lg:flex flex-col overflow-hidden h-[calc(100vh-140px)]">
            <div class="p-space-md bg-surface-container-low font-label-md text-label-md font-semibold text-tertiary uppercase tracking-wider border-b border-surface-container">
                Pending Evaluations (<?= count($queue) ?>)
            </div>
            <div class="overflow-y-auto divide-y border-surface-container flex-1">
                <?php foreach($queue as $q): ?>
                <a href="evaluation.php?id=<?= $q['EvaluationID'] ?>" class="block p-space-md hover:bg-surface-container-low transition-colors <?= ($eval && $eval['EvaluationID'] == $q['EvaluationID']) ? 'bg-primary-fixed/20 border-l-4 border-primary' : 'border-l-4 border-transparent' ?>">
                    <p class="font-title-md text-title-md text-on-surface"><?= htmlspecialchars($q['LastName'] . ', ' . $q['FirstName']) ?></p>
                    <p class="font-body-sm text-body-sm text-tertiary mt-1"><?= htmlspecialchars($q['StudentNo']) ?></p>
                </a>
                <?php endforeach; ?>
                <?php if(empty($queue)): ?>
                <div class="p-space-md text-body-sm font-body-sm text-tertiary text-center">No pending evaluations.</div>
                <?php endif; ?>
            </div>
        </aside>

        <!-- Main Content -->
        <div class="flex-1 max-w-5xl">
            <?php if ($eval): ?>
            <form action="../controllers/evaluation_action_controller.php" method="POST" class="flex flex-col gap-space-md" onsubmit="setTimeout(() => { const btns = this.querySelectorAll('button[type=submit]'); btns.forEach(b => b.disabled = true); }, 10); return true;">
                <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token'] ?? ''); ?>">
                <input type="hidden" name="EvaluationID" value="<?= $eval['EvaluationID'] ?>">
                <input type="hidden" name="StudentID" value="<?= $eval['StudentID'] ?>">
                
                <div class="bg-surface-container-lowest p-space-lg rounded-xl shadow-[0_1px_8px_rgba(0,0,0,0.04)] flex flex-col md:flex-row justify-between md:items-center gap-space-sm border border-surface-container">
                    <div>
                        <h2 class="font-headline-md text-headline-md text-on-surface">Academic Evaluation</h2>
                        <p class="font-body-sm text-body-sm text-tertiary">Student No: <span class="font-bold text-primary"><?= htmlspecialchars($eval['StudentNo']) ?></span> • Program: <span class="font-bold"><?= htmlspecialchars($eval['ProgramName']) ?></span></p>
                    </div>
                    <?php if ($eval['Status'] === 'Pending'): ?>
                    <div class="flex gap-space-sm">
                        <button type="submit" name="action" value="Reject" class="px-space-md py-space-sm rounded-lg font-label-lg text-label-lg font-semibold text-error bg-error-container hover:bg-error/20 transition-colors">
                            Reject
                        </button>
                        <button type="submit" name="action" value="Approve" class="px-space-md py-space-sm rounded-lg font-label-lg text-label-lg font-semibold text-on-primary bg-primary hover:bg-primary-container transition-colors shadow-sm">
                            Approve Subjects
                        </button>
                    </div>
                    <?php else: ?>
                    <div class="px-space-md py-space-xs rounded-lg font-label-md text-label-md font-semibold <?= $eval['Status'] == 'Approved' ? 'bg-secondary-container/30 text-secondary' : 'bg-error-container text-error' ?>">
                        Status: <?= htmlspecialchars($eval['Status']) ?>
                    </div>
                    <?php endif; ?>
                </div>

                <div class="bg-surface-container-lowest p-space-md rounded-xl shadow-[0_1px_8px_rgba(0,0,0,0.04)] border border-surface-container">
                    <h3 class="font-title-lg text-title-lg font-semibold mb-space-md border-b border-surface-container pb-space-xs">Academic Profile</h3>
                    <div class="grid grid-cols-2 md:grid-cols-4 gap-space-sm font-body-sm text-body-sm">
                        <div><p class="text-tertiary">Applicant Type</p><p class="font-medium text-on-surface"><?= htmlspecialchars($eval['StudentType']) ?></p></div>
                        <div><p class="text-tertiary">Previous School</p><p class="font-medium text-on-surface"><?= htmlspecialchars($eval['PreviousSchoolName'] ?? 'N/A') ?></p></div>
                        <div><p class="text-tertiary">GWA</p><p class="font-medium text-on-surface"><?= htmlspecialchars($eval['GWA'] ?? 'N/A') ?></p></div>
                    </div>
                </div>

                <div class="bg-surface-container-lowest p-space-md rounded-xl shadow-[0_1px_8px_rgba(0,0,0,0.04)] border border-surface-container overflow-hidden">
                    <h3 class="font-title-lg text-title-lg font-semibold mb-space-md border-b border-surface-container pb-space-xs">Curriculum Subjects</h3>
                    <div class="overflow-x-auto">
                        <table class="w-full text-left border-collapse">
                            <thead>
                                <tr class="bg-surface-container-low text-tertiary font-label-sm text-label-sm uppercase tracking-wider">
                                    <th class="px-space-md py-space-sm font-bold">Subject Code</th>
                                    <th class="px-space-md py-space-sm font-bold">Descriptive Title</th>
                                    <th class="px-space-md py-space-sm font-bold">Units</th>
                                    <th class="px-space-md py-space-sm font-bold text-center">Action</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y border-surface-container font-body-sm text-body-sm">
                                <?php foreach($subjects as $sub): 
                                    $status = $evaluated[$sub['SubjectID']] ?? 'Pending';
                                ?>
                                <tr class="hover:bg-surface-container-low/50 transition-colors">
                                    <td class="px-space-md py-space-sm font-bold text-primary"><?= htmlspecialchars($sub['SubjectCode']) ?></td>
                                    <td class="px-space-md py-space-sm font-medium text-on-surface"><?= htmlspecialchars($sub['SubjectTitle']) ?></td>
                                    <td class="px-space-md py-space-sm text-on-surface"><?= htmlspecialchars($sub['Units']) ?></td>
                                    <td class="px-space-md py-space-sm text-center">
                                        <?php if ($eval['Status'] === 'Pending'): ?>
                                            <select name="subjects[<?= $sub['SubjectID'] ?>]" class="bg-surface-container-low border border-surface-container rounded p-1 text-sm outline-none focus:ring-1 focus:ring-primary text-on-surface">
                                                <option value="Pending">Do Not Take</option>
                                                <option value="Approved" selected>Approve to Take</option>
                                                <?php if ($eval['StudentType'] === 'Transferee'): ?>
                                                <option value="Credited">Credit (Transferee)</option>
                                                <?php endif; ?>
                                            </select>
                                        <?php else: ?>
                                            <span class="font-bold <?= $status == 'Approved' ? 'text-[oklch(0.6_0.15_140)]' : ($status == 'Credited' ? 'text-[oklch(0.55_0.15_260)]' : 'text-tertiary') ?>">
                                                <?= $status ?>
                                            </span>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                                <?php if(empty($subjects)): ?>
                                <tr><td colspan="4" class="px-space-md py-space-xl text-center text-tertiary">No subjects found for this program.</td></tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
                
                <?php if ($eval['Status'] === 'Pending'): ?>
                <div class="bg-surface-container-lowest p-space-md rounded-xl shadow-[0_1px_8px_rgba(0,0,0,0.04)] border border-surface-container">
                    <label class="block font-label-md text-label-md text-on-surface mb-space-xs">Evaluation Remarks</label>
                    <textarea name="Remarks" rows="3" class="w-full bg-surface-container-low border border-surface-container rounded-lg p-space-sm font-body-sm text-body-sm outline-none focus:border-primary focus:ring-1 focus:ring-primary transition-all" placeholder="Optional notes..."></textarea>
                </div>
                <?php endif; ?>

            </form>
            <?php else: ?>
                <div class="bg-surface-container-lowest p-space-xl rounded-xl shadow-[0_1px_8px_rgba(0,0,0,0.04)] border border-surface-container text-center flex flex-col items-center justify-center min-h-[400px]">
                    <div class="w-16 h-16 bg-surface-container rounded-full flex items-center justify-center text-tertiary mb-space-sm">
                        <i class="fa-solid fa-check-to-slot text-[32px]"></i>
                    </div>
                    <h2 class="font-headline-md text-headline-md text-on-surface mb-1">No Pending Evaluations</h2>
                    <p class="font-body-md text-body-md text-tertiary mb-space-lg">All academic evaluations have been processed.</p>
                    <a href="staff_dashboard.php" class="px-space-md py-space-sm bg-primary text-on-primary rounded-lg font-label-lg text-label-lg font-semibold hover:bg-primary-container transition-colors shadow-sm">Return to Dashboard</a>
                </div>
            <?php endif; ?>
        </div>
    </main>
</div>
<?php include 'includes/footer.php'; ?>
