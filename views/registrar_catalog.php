<?php
session_start();
require_once '../config/db.php';

if (!isset($_SESSION['UserType']) || $_SESSION['UserType'] !== 'Staff' || !in_array($_SESSION['Role'] ?? '', ['Registrar', 'Admin'])) {
    header("Location: login.php?error=" . urlencode("Access denied. Registrar privileges required."));
    exit;
}

// Handle Add / Delete Subject
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $csrf = $_POST['csrf_token'] ?? '';
    if (!isset($_SESSION['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $csrf)) {
        header("Location: registrar_catalog.php?error=" . urlencode("Invalid security token."));
        exit;
    }

    $action = $_POST['action'] ?? '';

    if ($action === 'add_subject') {
        $code = trim($_POST['subject_code'] ?? '');
        $title = trim($_POST['subject_title'] ?? '');
        $units = (float)($_POST['units'] ?? 3.0);
        $progId = (int)($_POST['program_id'] ?? 0);

        if (empty($code) || empty($title) || !$progId) {
            header("Location: registrar_catalog.php?error=" . urlencode("All fields are required."));
            exit;
        }

        try {
            $stmt = $pdo->prepare("INSERT INTO subject (SubjectCode, SubjectTitle, Units, ProgramID) VALUES (?, ?, ?, ?)");
            $stmt->execute([$code, $title, $units, $progId]);

            require_once '../config/logger.php';
            log_activity($pdo, 'Add Course', 'Academic Course Catalog', "Added subject: $code - $title ($units units)");

            header("Location: registrar_catalog.php?msg=" . urlencode("Subject added to academic course catalog!"));
            exit;
        } catch (Exception $e) {
            header("Location: registrar_catalog.php?error=" . urlencode("Failed to add course: " . $e->getMessage()));
            exit;
        }
    } elseif ($action === 'delete_subject') {
        $subId = (int)($_POST['subject_id'] ?? 0);
        $stmt = $pdo->prepare("DELETE FROM subject WHERE SubjectID = ?");
        $stmt->execute([$subId]);
        header("Location: registrar_catalog.php?msg=" . urlencode("Course deleted from catalog."));
        exit;
    }
}

$search = trim($_GET['search'] ?? '');
$programFilter = (int)($_GET['program_id'] ?? 0);

$sql = "
    SELECT s.*, p.ProgramName 
    FROM subject s 
    LEFT JOIN program p ON s.ProgramID = p.ProgramID 
    WHERE 1=1
";
$params = [];

if (!empty($search)) {
    $sql .= " AND (s.SubjectCode LIKE ? OR s.SubjectTitle LIKE ?)";
    $term = "%$search%";
    $params = [$term, $term];
}

if ($programFilter > 0) {
    $sql .= " AND s.ProgramID = ?";
    $params[] = $programFilter;
}

$sql .= " ORDER BY s.SubjectCode ASC LIMIT 100";
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$subjects = $stmt->fetchAll();

$programs = $pdo->query("SELECT * FROM program ORDER BY ProgramName ASC")->fetchAll();
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
                        <i class="fa-solid fa-book-open text-[18px]"></i>
                        <span>Registrar Module</span>
                    </div>
                    <h1 class="font-headline-lg text-headline-lg text-on-surface tracking-tight">Academic Course Catalog</h1>
                    <p class="font-body-md text-body-md text-tertiary">
                        Master curriculum courses, credit units, subject descriptions, and collegiate program associations.
                    </p>
                </div>
                <button onclick="document.getElementById('addCourseModal').classList.remove('hidden')" class="flex items-center gap-space-xs px-4 py-2 bg-primary hover:bg-primary-container text-on-primary rounded-lg font-label-md text-label-md font-semibold transition-colors shadow-sm">
                    <i class="fa-solid fa-circle-plus text-[18px]"></i>
                    <span>Add New Course</span>
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

        <!-- Filters -->
        <section class="bg-surface-container-lowest p-space-md rounded-xl shadow-[0_1px_8px_rgba(0,0,0,0.04)] border border-outline-variant/30">
            <form method="GET" class="flex flex-wrap items-center justify-between gap-space-sm">
                <div class="flex flex-wrap items-center gap-space-sm flex-1">
                    <div class="relative min-w-[280px] flex-1">
                        <i class="fa-solid fa-magnifying-glass absolute left-3 top-1/2 -translate-y-1/2 text-tertiary text-[18px]"></i>
                        <input type="text" name="search" value="<?= htmlspecialchars($search) ?>" placeholder="Search course code or title..." 
                               class="w-full h-10 pl-9 pr-3 rounded-lg bg-surface-container-low border border-surface-container text-sm focus:outline-none focus:border-primary"/>
                    </div>
                    <select name="program_id" class="h-10 px-3 rounded-lg bg-surface-container-low border border-surface-container text-sm max-w-xs">
                        <option value="">All Degree Programs</option>
                        <?php foreach($programs as $p): ?>
                            <option value="<?= $p['ProgramID'] ?>" <?= $programFilter === (int)$p['ProgramID'] ? 'selected' : '' ?>>
                                <?= htmlspecialchars($p['ProgramName']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="flex items-center gap-space-xs">
                    <button type="submit" class="px-4 py-2 bg-primary text-on-primary rounded-lg text-sm font-semibold hover:bg-primary-container transition-colors">
                        Filter Catalog
                    </button>
                    <a href="registrar_catalog.php" class="px-3 py-2 bg-surface-container hover:bg-surface-container-high rounded-lg text-sm text-tertiary">
                        Reset
                    </a>
                </div>
            </form>
        </section>

        <!-- Catalog Table -->
        <section class="bg-surface-container-lowest rounded-xl shadow-[0_1px_8px_rgba(0,0,0,0.04)] border border-outline-variant/30 overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse text-sm">
                    <thead>
                        <tr class="bg-surface-container-low text-tertiary text-xs uppercase font-label-sm border-b border-surface-container">
                            <th class="px-space-md py-space-sm">Course Code</th>
                            <th class="px-space-md py-space-sm">Course Description</th>
                            <th class="px-space-md py-space-sm">Units</th>
                            <th class="px-space-md py-space-sm">Degree Curriculum</th>
                            <th class="px-space-md py-space-sm text-right">Action</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-surface-container">
                        <?php foreach ($subjects as $sub): ?>
                        <tr class="hover:bg-surface-container-low/50 transition-colors">
                            <td class="px-space-md py-space-sm font-mono font-bold text-primary">
                                <?= htmlspecialchars($sub['SubjectCode']) ?>
                            </td>
                            <td class="px-space-md py-space-sm font-medium text-on-surface">
                                <?= htmlspecialchars($sub['SubjectTitle']) ?>
                            </td>
                            <td class="px-space-md py-space-sm font-semibold text-on-surface">
                                <?= number_format($sub['Units'], 1) ?>
                            </td>
                            <td class="px-space-md py-space-sm text-xs text-tertiary">
                                <?= htmlspecialchars($sub['ProgramName'] ?? 'General Education') ?>
                            </td>
                            <td class="px-space-md py-space-sm text-right">
                                <form method="POST" action="registrar_catalog.php" class="inline" onsubmit="return confirm('Delete this course?');">
                                    <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token'] ?? '') ?>">
                                    <input type="hidden" name="action" value="delete_subject">
                                    <input type="hidden" name="subject_id" value="<?= $sub['SubjectID'] ?>">
                                    <button type="submit" class="p-1.5 text-tertiary hover:text-error rounded hover:bg-surface-container" title="Delete Course">
                                        <i class="fa-solid fa-trash-can text-[18px]"></i>
                                    </button>
                                </form>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                        <?php if (empty($subjects)): ?>
                        <tr>
                            <td colspan="5" class="px-space-md py-8 text-center text-tertiary">No courses found matching the search.</td>
                        </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </section>

    </div>
</main>

<!-- Add Course Modal -->
<div id="addCourseModal" class="fixed inset-0 z-50 bg-black/40 backdrop-blur-sm hidden flex items-center justify-center p-4">
    <div class="bg-surface-container-lowest rounded-2xl shadow-xl max-w-md w-full p-6 border border-outline-variant/40">
        <div class="flex items-center justify-between pb-3 border-b border-surface-container">
            <h3 class="font-headline-sm text-headline-sm text-on-surface">Add Curriculum Course</h3>
            <button onclick="document.getElementById('addCourseModal').classList.add('hidden')" class="text-tertiary hover:text-on-surface">
                <i class="fa-solid fa-xmark"></i>
            </button>
        </div>
        <form method="POST" action="registrar_catalog.php" class="flex flex-col gap-3 mt-4 text-sm">
            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token'] ?? '') ?>">
            <input type="hidden" name="action" value="add_subject">

            <div class="grid grid-cols-3 gap-3">
                <div class="col-span-1">
                    <label class="block text-xs font-semibold text-tertiary uppercase mb-1">Code</label>
                    <input type="text" name="subject_code" placeholder="IT101" required class="w-full h-9 px-3 rounded-lg bg-surface-container-low border border-surface-container text-sm font-mono focus:outline-none focus:border-primary"/>
                </div>
                <div class="col-span-2">
                    <label class="block text-xs font-semibold text-tertiary uppercase mb-1">Credit Units</label>
                    <input type="number" step="0.5" name="units" value="3.0" required class="w-full h-9 px-3 rounded-lg bg-surface-container-low border border-surface-container text-sm focus:outline-none focus:border-primary"/>
                </div>
            </div>

            <div>
                <label class="block text-xs font-semibold text-tertiary uppercase mb-1">Course Title / Description</label>
                <input type="text" name="subject_title" placeholder="Introduction to Computing" required class="w-full h-9 px-3 rounded-lg bg-surface-container-low border border-surface-container text-sm focus:outline-none focus:border-primary"/>
            </div>

            <div>
                <label class="block text-xs font-semibold text-tertiary uppercase mb-1">Associated Program</label>
                <select name="program_id" required class="w-full h-9 px-3 rounded-lg bg-surface-container-low border border-surface-container text-sm focus:outline-none focus:border-primary">
                    <option value="">-- Select Degree Program --</option>
                    <?php foreach($programs as $p): ?>
                        <option value="<?= $p['ProgramID'] ?>"><?= htmlspecialchars($p['ProgramName']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="flex items-center justify-end gap-2 mt-4 pt-3 border-t border-surface-container">
                <button type="button" onclick="document.getElementById('addCourseModal').classList.add('hidden')" class="px-4 py-2 rounded-lg bg-surface-container text-sm font-medium text-tertiary hover:bg-surface-container-high">Cancel</button>
                <button type="submit" class="px-5 py-2 rounded-lg bg-primary text-on-primary text-sm font-semibold hover:bg-primary-container shadow-sm">Save Course</button>
            </div>
        </form>
    </div>
</div>

<?php include 'includes/footer.php'; ?>