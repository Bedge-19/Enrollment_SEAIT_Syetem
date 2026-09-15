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

$enrollmentId = $_GET['id'] ?? null;

// Fetch pending queue
$queueStmt = $pdo->query("
    SELECT e.EnrollmentID, s.StudentNo, s.FirstName, s.LastName
    FROM enrollment e
    JOIN student s ON e.StudentID = s.StudentID
    LEFT JOIN blocking b ON e.EnrollmentID = b.EnrollmentID
    WHERE e.Status = 'Pending' AND b.BlockingID IS NULL
    ORDER BY e.EnrollmentDate ASC, e.EnrollmentID ASC
");
$queue = $queueStmt->fetchAll(PDO::FETCH_ASSOC);

if ($enrollmentId) {
    $stmt = $pdo->prepare("
        SELECT e.EnrollmentID, e.EnrollmentDate, e.Status, e.EvaluationID,
               s.StudentID, s.StudentNo, s.FirstName, s.LastName, s.ProgramID,
               p.ProgramName
        FROM enrollment e
        JOIN student s ON e.StudentID = s.StudentID
        LEFT JOIN program p ON s.ProgramID = p.ProgramID
        WHERE e.EnrollmentID = ?
    ");
    $stmt->execute([$enrollmentId]);
} else {
    $stmt = $pdo->query("
        SELECT e.EnrollmentID, e.EnrollmentDate, e.Status, e.EvaluationID,
               s.StudentID, s.StudentNo, s.FirstName, s.LastName, s.ProgramID,
               p.ProgramName
        FROM enrollment e
        JOIN student s ON e.StudentID = s.StudentID
        LEFT JOIN program p ON s.ProgramID = p.ProgramID
        LEFT JOIN blocking b ON e.EnrollmentID = b.EnrollmentID
        WHERE e.Status = 'Pending' AND b.BlockingID IS NULL
        ORDER BY e.EnrollmentDate ASC, e.EnrollmentID ASC
        LIMIT 1
    ");
}
$enroll = $stmt->fetch(PDO::FETCH_ASSOC);

$sections = [];
$approvedSubjects = [];

if ($enroll) {
    // Fetch available sections for this program
    $secStmt = $pdo->prepare("SELECT SectionID, SectionCode, YearLevel FROM section WHERE ProgramID = ?");
    $secStmt->execute([$enroll['ProgramID']]);
    $sections = $secStmt->fetchAll(PDO::FETCH_ASSOC);

    // Fetch approved subjects from evaluation
    $subStmt = $pdo->prepare("
        SELECT es.SubjectID, sub.SubjectCode, sub.SubjectTitle, sub.Units
        FROM evaluation_subject es
        JOIN subject sub ON es.SubjectID = sub.SubjectID
        WHERE es.EvaluationID = ? AND es.SubjectStatus = 'Approved'
    ");
    $subStmt->execute([$enroll['EvaluationID']]);
    $approvedSubjects = $subStmt->fetchAll(PDO::FETCH_ASSOC);
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
                Pending Sectioning (<?= count($queue) ?>)
            </div>
            <div class="overflow-y-auto divide-y border-surface-container flex-1">
                <?php foreach($queue as $q): ?>
                <a href="blocking.php?id=<?= $q['EnrollmentID'] ?>" class="block p-space-md hover:bg-surface-container-low transition-colors <?= ($enroll && $enroll['EnrollmentID'] == $q['EnrollmentID']) ? 'bg-primary-fixed/20 border-l-4 border-primary' : 'border-l-4 border-transparent' ?>">
                    <p class="font-title-md text-title-md text-on-surface"><?= htmlspecialchars($q['LastName'] . ', ' . $q['FirstName']) ?></p>
                    <p class="font-body-sm text-body-sm text-tertiary mt-1"><?= htmlspecialchars($q['StudentNo']) ?></p>
                </a>
                <?php endforeach; ?>
                <?php if(empty($queue)): ?>
                <div class="p-space-md text-body-sm font-body-sm text-tertiary text-center">No pending sectioning.</div>
                <?php endif; ?>
            </div>
        </aside>

        <!-- Main Content -->
        <div class="flex-1 max-w-5xl">
            <?php if ($enroll): ?>
            <form action="../controllers/blocking_action_controller.php" method="POST" class="flex flex-col gap-space-md" onsubmit="setTimeout(() => { const btns = this.querySelectorAll('button[type=submit]'); btns.forEach(b => b.disabled = true); }, 10); return true;">
                <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token'] ?? ''); ?>">
                <input type="hidden" name="EnrollmentID" value="<?= $enroll['EnrollmentID'] ?>">
                
                <div class="bg-surface-container-lowest p-space-lg rounded-xl shadow-[0_1px_8px_rgba(0,0,0,0.04)] flex flex-col md:flex-row justify-between md:items-center gap-space-sm border border-surface-container">
                    <div>
                        <h2 class="font-headline-md text-headline-md text-on-surface">Assign Section Block</h2>
                        <p class="font-body-sm text-body-sm text-tertiary">Student No: <span class="font-bold text-primary"><?= htmlspecialchars($enroll['StudentNo']) ?></span> • Program: <span class="font-bold"><?= htmlspecialchars($enroll['ProgramName']) ?></span></p>
                    </div>
                    <div class="flex gap-space-sm">
                        <button type="submit" class="px-space-md py-space-sm rounded-lg font-label-lg text-label-lg font-semibold text-on-primary bg-primary hover:bg-primary-container transition-colors shadow-sm">
                            Confirm Section & Proceed to Payment
                        </button>
                    </div>
                </div>

                <div class="bg-surface-container-lowest p-space-md rounded-xl shadow-[0_1px_8px_rgba(0,0,0,0.04)] border border-surface-container">
                    <h3 class="font-title-lg text-title-lg font-semibold mb-space-md border-b border-surface-container pb-space-xs">Select Block Section</h3>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-space-sm">
                        <div>
                            <label class="block font-label-sm text-label-sm font-bold text-tertiary uppercase tracking-wider mb-2">Available Sections</label>
                            <select name="SectionID" class="w-full bg-surface-container-low border border-surface-container rounded-lg p-space-sm font-body-sm text-body-sm outline-none focus:border-primary focus:ring-1 focus:ring-primary transition-all text-on-surface" required>
                                <option value="">-- Choose a Section --</option>
                                <?php foreach($sections as $sec): ?>
                                <option value="<?= $sec['SectionID'] ?>">
                                    <?= htmlspecialchars($sec['SectionCode']) ?> (Year <?= $sec['YearLevel'] ?>)
                                </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                </div>

                <div class="bg-surface-container-lowest p-space-md rounded-xl shadow-[0_1px_8px_rgba(0,0,0,0.04)] border border-surface-container overflow-hidden">
                    <h3 class="font-title-lg text-title-lg font-semibold mb-space-md border-b border-surface-container pb-space-xs">Approved Subjects for Blocking</h3>
                    <div class="overflow-x-auto">
                        <table class="w-full text-left border-collapse">
                            <thead>
                                <tr class="bg-surface-container-low text-tertiary font-label-sm text-label-sm uppercase tracking-wider">
                                    <th class="px-space-md py-space-sm font-bold">Subject Code</th>
                                    <th class="px-space-md py-space-sm font-bold">Descriptive Title</th>
                                    <th class="px-space-md py-space-sm font-bold">Units</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y border-surface-container font-body-sm text-body-sm">
                                <?php 
                                $totalUnits = 0;
                                foreach($approvedSubjects as $sub): 
                                    $totalUnits += $sub['Units'];
                                ?>
                                <tr class="hover:bg-surface-container-low/50 transition-colors">
                                    <td class="px-space-md py-space-sm font-bold text-primary"><?= htmlspecialchars($sub['SubjectCode']) ?>
                                        <input type="hidden" name="subjects[]" value="<?= $sub['SubjectID'] ?>">
                                    </td>
                                    <td class="px-space-md py-space-sm font-medium text-on-surface"><?= htmlspecialchars($sub['SubjectTitle']) ?></td>
                                    <td class="px-space-md py-space-sm text-on-surface"><?= htmlspecialchars($sub['Units']) ?></td>
                                </tr>
                                <?php endforeach; ?>
                                <?php if(empty($approvedSubjects)): ?>
                                <tr><td colspan="3" class="px-space-md py-space-xl text-center text-tertiary">No approved subjects found.</td></tr>
                                <?php endif; ?>
                            </tbody>
                            <tfoot>
                                <tr class="bg-surface-container-low text-on-surface font-title-sm text-title-sm font-bold border-t border-surface-container">
                                    <td colspan="2" class="px-space-md py-space-sm text-right">Total Units:</td>
                                    <td class="px-space-md py-space-sm text-primary"><?= number_format($totalUnits, 1) ?></td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>

            </form>
            <?php else: ?>
                <div class="bg-surface-container-lowest p-space-xl rounded-xl shadow-[0_1px_8px_rgba(0,0,0,0.04)] border border-surface-container text-center flex flex-col items-center justify-center min-h-[400px]">
                    <div class="w-16 h-16 bg-surface-container rounded-full flex items-center justify-center text-tertiary mb-space-sm">
                        <i class="fa-solid fa-book-bookmark text-[32px]"></i>
                    </div>
                    <h2 class="font-headline-md text-headline-md text-on-surface mb-1">No Pending Sectioning</h2>
                    <p class="font-body-md text-body-md text-tertiary mb-space-lg">All enrollments have been assigned blocks.</p>
                    <a href="staff_dashboard.php" class="px-space-md py-space-sm bg-primary text-on-primary rounded-lg font-label-lg text-label-lg font-semibold hover:bg-primary-container transition-colors shadow-sm">Return to Dashboard</a>
                </div>
            <?php endif; ?>
        </div>
    </main>
</div>
<?php include 'includes/footer.php'; ?>
