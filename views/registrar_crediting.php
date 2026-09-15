<?php
session_start();
require_once '../config/db.php';

if (!isset($_SESSION['UserType']) || $_SESSION['UserType'] !== 'Staff' || !in_array($_SESSION['Role'] ?? '', ['Registrar', 'Admin'])) {
    header("Location: login.php?error=" . urlencode("Access denied. Registrar privileges required."));
    exit;
}

// Handle Crediting Actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $csrf = $_POST['csrf_token'] ?? '';
    if (!isset($_SESSION['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $csrf)) {
        header("Location: registrar_crediting.php?error=" . urlencode("Invalid security token."));
        exit;
    }

    $action = $_POST['action'] ?? '';

    if ($action === 'credit_subject') {
        $studentId = (int)($_POST['student_id'] ?? 0);
        $subjectId = (int)($_POST['subject_id'] ?? 0);
        $prevCode = trim($_POST['prev_subject_code'] ?? '');
        $prevSchool = trim($_POST['prev_school'] ?? '');
        $grade = trim($_POST['grade'] ?? '1.75');
        $remarks = "Credited from $prevSchool ($prevCode) - Grade: $grade";

        if (!$studentId || !$subjectId) {
            header("Location: registrar_crediting.php?error=" . urlencode("Student and Subject are required."));
            exit;
        }

        try {
            $stmt = $pdo->prepare("INSERT INTO credited_subject (Status, Remarks, StudentID, SubjectID) VALUES ('Approved', ?, ?, ?)");
            $stmt->execute([$remarks, $studentId, $subjectId]);

            require_once '../config/logger.php';
            log_activity($pdo, 'Credit Subject', 'Subject Crediting', "Credited SubjectID $subjectId for StudentID $studentId ($remarks)");

            header("Location: registrar_crediting.php?msg=" . urlencode("Subject credited successfully!"));
            exit;
        } catch (Exception $e) {
            header("Location: registrar_crediting.php?error=" . urlencode("Error crediting subject: " . $e->getMessage()));
            exit;
        }
    } elseif ($action === 'update_status') {
        $creditId = (int)($_POST['credit_id'] ?? 0);
        $newStatus = $_POST['status'] ?? 'Approved';
        $stmt = $pdo->prepare("UPDATE credited_subject SET Status = ? WHERE CreditID = ?");
        $stmt->execute([$newStatus, $creditId]);
        header("Location: registrar_crediting.php?msg=" . urlencode("Status updated to $newStatus."));
        exit;
    }
}

// Fetch credited subjects
$stmt = $pdo->query("
    SELECT cs.*, s.StudentNo, s.LastName, s.FirstName, sub.SubjectCode, sub.SubjectTitle, sub.Units, p.ProgramName
    FROM credited_subject cs
    JOIN student s ON cs.StudentID = s.StudentID
    JOIN subject sub ON cs.SubjectID = sub.SubjectID
    LEFT JOIN program p ON s.ProgramID = p.ProgramID
    ORDER BY cs.CreditID DESC
");
$credited = $stmt->fetchAll();

// Transferees or shifters for the dropdown
$students = $pdo->query("SELECT StudentID, StudentNo, LastName, FirstName FROM student ORDER BY LastName ASC, FirstName ASC")->fetchAll();
$subjects = $pdo->query("SELECT SubjectID, SubjectCode, SubjectTitle, Units FROM subject ORDER BY SubjectCode ASC")->fetchAll();
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
                        <i class="fa-solid fa-clipboard-check text-[18px]"></i>
                        <span>Registrar Module</span>
                    </div>
                    <h1 class="font-headline-lg text-headline-lg text-on-surface tracking-tight">Subject Crediting Processing</h1>
                    <p class="font-body-md text-body-md text-tertiary">
                        Evaluate transferees and shifters, credit equivalent courses, and apply previous collegiate units.
                    </p>
                </div>
                <button onclick="document.getElementById('creditModal').classList.remove('hidden')" class="flex items-center gap-space-xs px-4 py-2 bg-primary hover:bg-primary-container text-on-primary rounded-lg font-label-md text-label-md font-semibold transition-colors shadow-sm">
                    <i class="fa-solid fa-folder-plus text-[18px]"></i>
                    <span>Credit New Subject</span>
                </button>
            </div>

            <?php if (!empty($_GET['msg'])): ?>
            <div class="p-3 bg-emerald-50 border border-emerald-200 text-emerald-800 rounded-lg text-sm flex items-center gap-2">
                <i class="fa-solid fa-circle-check text-emerald-600 text-[20px]"></i>
                <span><?= htmlspecialchars($_GET['msg']) ?></span>
            </div>
            <?php endif; ?>
            <?php if (!empty($_GET['error'])): ?>
            <div class="p-3 bg-error-container border border-error/30 text-on-error-container rounded-lg text-sm flex items-center gap-2">
                <i class="fa-solid fa-circle-exclamation text-error text-[20px]"></i>
                <span><?= htmlspecialchars($_GET['error']) ?></span>
            </div>
            <?php endif; ?>
        </section>

        <!-- Credited Roster Table -->
        <section class="bg-surface-container-lowest rounded-xl shadow-[0_1px_8px_rgba(0,0,0,0.04)] border border-outline-variant/30 overflow-hidden">
            <div class="p-space-md border-b border-surface-container flex items-center justify-between">
                <h3 class="font-title-md text-title-md text-on-surface">Credited Academic Equivalencies</h3>
                <span class="text-xs text-tertiary font-semibold"><?= count($credited) ?> records evaluated</span>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse text-sm">
                    <thead>
                        <tr class="bg-surface-container-low text-tertiary text-xs uppercase font-label-sm border-b border-surface-container">
                            <th class="px-space-md py-space-sm">Student</th>
                            <th class="px-space-md py-space-sm">Credited Course</th>
                            <th class="px-space-md py-space-sm">Units</th>
                            <th class="px-space-md py-space-sm">Equivalency Details</th>
                            <th class="px-space-md py-space-sm">Status</th>
                            <th class="px-space-md py-space-sm text-right">Action</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-surface-container">
                        <?php foreach($credited as $c): ?>
                        <tr class="hover:bg-surface-container-low/50 transition-colors">
                            <td class="px-space-md py-space-sm">
                                <span class="font-semibold text-on-surface"><?= htmlspecialchars($c['LastName'] . ', ' . $c['FirstName']) ?></span>
                                <span class="block text-xs font-mono text-primary"><?= htmlspecialchars($c['StudentNo']) ?></span>
                            </td>
                            <td class="px-space-md py-space-sm">
                                <span class="font-mono font-bold text-on-surface"><?= htmlspecialchars($c['SubjectCode']) ?></span>
                                <span class="block text-xs text-tertiary"><?= htmlspecialchars($c['SubjectTitle']) ?></span>
                            </td>
                            <td class="px-space-md py-space-sm font-semibold text-on-surface">
                                <?= $c['Units'] ?> Units
                            </td>
                            <td class="px-space-md py-space-sm text-xs text-tertiary max-w-sm">
                                <?= htmlspecialchars($c['Remarks'] ?? 'Equivalency approved') ?>
                            </td>
                            <td class="px-space-md py-space-sm">
                                <?php if ($c['Status'] === 'Approved'): ?>
                                    <span class="px-2.5 py-0.5 rounded-full text-xs font-semibold bg-emerald-100 text-emerald-800">Approved</span>
                                <?php elseif ($c['Status'] === 'Pending'): ?>
                                    <span class="px-2.5 py-0.5 rounded-full text-xs font-semibold bg-amber-100 text-amber-800">Pending</span>
                                <?php else: ?>
                                    <span class="px-2.5 py-0.5 rounded-full text-xs font-semibold bg-error-container text-error">Rejected</span>
                                <?php endif; ?>
                            </td>
                            <td class="px-space-md py-space-sm text-right">
                                <form method="POST" action="registrar_crediting.php" class="inline">
                                    <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token'] ?? '') ?>">
                                    <input type="hidden" name="action" value="update_status">
                                    <input type="hidden" name="credit_id" value="<?= $c['CreditID'] ?>">
                                    <input type="hidden" name="status" value="<?= $c['Status'] === 'Approved' ? 'Rejected' : 'Approved' ?>">
                                    <button type="submit" class="text-xs px-2.5 py-1 rounded border border-surface-container hover:bg-surface-container text-tertiary">
                                        <?= $c['Status'] === 'Approved' ? 'Revoke' : 'Approve' ?>
                                    </button>
                                </form>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                        <?php if(empty($credited)): ?>
                        <tr>
                            <td colspan="6" class="px-space-md py-8 text-center text-tertiary">No subjects currently credited. Click "Credit New Subject" to evaluate a transferee.</td>
                        </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </section>

    </div>
</main>

<!-- Credit Modal -->
<div id="creditModal" class="fixed inset-0 z-50 bg-black/40 backdrop-blur-sm hidden flex items-center justify-center p-4">
    <div class="bg-surface-container-lowest rounded-2xl shadow-xl max-w-lg w-full p-6 border border-outline-variant/40">
        <div class="flex items-center justify-between pb-3 border-b border-surface-container">
            <h3 class="font-headline-sm text-headline-sm text-on-surface">Credit Subject Equivalency</h3>
            <button onclick="document.getElementById('creditModal').classList.add('hidden')" class="text-tertiary hover:text-on-surface">
                <i class="fa-solid fa-xmark"></i>
            </button>
        </div>
        <form method="POST" action="registrar_crediting.php" class="flex flex-col gap-3 mt-4 text-sm">
            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token'] ?? '') ?>">
            <input type="hidden" name="action" value="credit_subject">

            <div>
                <label class="block text-xs font-semibold text-tertiary uppercase mb-1">Student Candidate</label>
                <select name="student_id" required class="w-full h-10 px-3 rounded-lg bg-surface-container-low border border-surface-container text-sm focus:outline-none focus:border-primary">
                    <option value="">-- Choose Transferee / Shifter --</option>
                    <?php foreach($students as $s): ?>
                        <option value="<?= $s['StudentID'] ?>"><?= htmlspecialchars($s['StudentNo'] . ' - ' . $s['LastName'] . ', ' . $s['FirstName']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div>
                <label class="block text-xs font-semibold text-tertiary uppercase mb-1">Equivalent Curriculum Course</label>
                <select name="subject_id" required class="w-full h-10 px-3 rounded-lg bg-surface-container-low border border-surface-container text-sm focus:outline-none focus:border-primary">
                    <option value="">-- Select Target Curriculum Subject --</option>
                    <?php foreach($subjects as $sub): ?>
                        <option value="<?= $sub['SubjectID'] ?>"><?= htmlspecialchars($sub['SubjectCode'] . ' - ' . $sub['SubjectTitle'] . ' (' . $sub['Units'] . ' units)') ?></option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="block text-xs font-semibold text-tertiary uppercase mb-1">Previous School Name</label>
                    <input type="text" name="prev_school" required placeholder="e.g. Mindanao State Univ." class="w-full h-9 px-3 rounded-lg bg-surface-container-low border border-surface-container text-sm focus:outline-none focus:border-primary"/>
                </div>
                <div>
                    <label class="block text-xs font-semibold text-tertiary uppercase mb-1">Previous Course Code</label>
                    <input type="text" name="prev_subject_code" required placeholder="e.g. MATH 101" class="w-full h-9 px-3 rounded-lg bg-surface-container-low border border-surface-container text-sm focus:outline-none focus:border-primary"/>
                </div>
            </div>

            <div>
                <label class="block text-xs font-semibold text-tertiary uppercase mb-1">Grade Earned</label>
                <input type="text" name="grade" value="1.75" class="w-full h-9 px-3 rounded-lg bg-surface-container-low border border-surface-container text-sm focus:outline-none focus:border-primary"/>
            </div>

            <div class="flex items-center justify-end gap-2 mt-4 pt-3 border-t border-surface-container">
                <button type="button" onclick="document.getElementById('creditModal').classList.add('hidden')" class="px-4 py-2 rounded-lg bg-surface-container text-sm font-medium text-tertiary hover:bg-surface-container-high">Cancel</button>
                <button type="submit" class="px-5 py-2 rounded-lg bg-primary text-on-primary text-sm font-semibold hover:bg-primary-container shadow-sm">Credit Subject</button>
            </div>
        </form>
    </div>
</div>

<?php include 'includes/footer.php'; ?>