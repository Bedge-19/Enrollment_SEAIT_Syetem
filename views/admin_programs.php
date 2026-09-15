<?php
session_start();
require_once '../config/db.php';

if (!isset($_SESSION['UserType']) || $_SESSION['UserType'] !== 'Staff' || ($_SESSION['Role'] ?? '') !== 'Admin') {
    header("Location: login.php?error=" . urlencode("Access denied. Administrator privileges required."));
    exit;
}

$deptFilter = $_GET['department_id'] ?? '';

$sql = "
    SELECT p.*, d.DepartmentName, 
           COUNT(DISTINCT s.SubjectID) AS SubjectCount,
           COUNT(DISTINCT st.StudentID) AS StudentCount
    FROM program p
    LEFT JOIN department d ON p.DepartmentID = d.departmentID
    LEFT JOIN subject s ON p.ProgramID = s.ProgramID
    LEFT JOIN student st ON p.ProgramID = st.ProgramID
    WHERE 1=1
";
$params = [];

if (!empty($deptFilter)) {
    $sql .= " AND p.DepartmentID = ?";
    $params[] = $deptFilter;
}

$sql .= " GROUP BY p.ProgramID ORDER BY d.DepartmentName ASC, p.ProgramName ASC";
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$programs = $stmt->fetchAll();

$departments = $pdo->query("SELECT * FROM department ORDER BY DepartmentName ASC")->fetchAll();
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
                        <i class="fa-solid fa-graduation-cap text-[18px]"></i>
                        <span>Admin Module</span>
                    </div>
                    <h1 class="font-headline-lg text-headline-lg text-on-surface tracking-tight">Program Master Data</h1>
                    <p class="font-body-md text-body-md text-tertiary">
                        Manage degree curricula, vocational programs, and collegiate departmental assignments.
                    </p>
                </div>
                <button onclick="document.getElementById('addProgModal').classList.remove('hidden')" class="flex items-center gap-space-xs px-4 py-2 bg-primary hover:bg-primary-container text-on-primary rounded-lg font-label-md text-label-md font-semibold transition-colors shadow-sm">
                    <i class="fa-solid fa-circle-plus text-[18px]"></i>
                    <span>Add New Program</span>
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

        <!-- Department Filter -->
        <section class="bg-surface-container-lowest p-space-md rounded-xl shadow-[0_1px_8px_rgba(0,0,0,0.04)] border border-outline-variant/30">
            <form method="GET" class="flex items-center gap-3">
                <label class="text-xs font-semibold text-tertiary uppercase">Filter by Department:</label>
                <select name="department_id" onchange="this.form.submit()" class="h-9 px-3 rounded-lg bg-surface-container-low border border-surface-container text-sm">
                    <option value="">All Departments</option>
                    <?php foreach ($departments as $d): ?>
                        <option value="<?= $d['departmentID'] ?>" <?= $deptFilter == $d['departmentID'] ? 'selected' : '' ?>>
                            <?= htmlspecialchars($d['DepartmentName']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
                <?php if (!empty($deptFilter)): ?>
                    <a href="admin_programs.php" class="text-xs text-primary hover:underline">Clear Filter</a>
                <?php endif; ?>
            </form>
        </section>

        <!-- Program Table -->
        <section class="bg-surface-container-lowest rounded-xl shadow-[0_1px_8px_rgba(0,0,0,0.04)] border border-outline-variant/30 overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse text-sm">
                    <thead>
                        <tr class="bg-surface-container-low text-tertiary text-xs uppercase font-label-sm border-b border-surface-container">
                            <th class="px-space-md py-space-sm">Program Name</th>
                            <th class="px-space-md py-space-sm">Collegiate Department</th>
                            <th class="px-space-md py-space-sm">Curriculum Subjects</th>
                            <th class="px-space-md py-space-sm">Enrolled Students</th>
                            <th class="px-space-md py-space-sm text-right">Action</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-surface-container">
                        <?php foreach ($programs as $prog): ?>
                        <tr class="hover:bg-surface-container-low/50 transition-colors">
                            <td class="px-space-md py-space-sm font-semibold text-on-surface">
                                <?= htmlspecialchars($prog['ProgramName']) ?>
                            </td>
                            <td class="px-space-md py-space-sm text-xs text-tertiary">
                                <?= htmlspecialchars($prog['DepartmentName'] ?? 'Unassigned') ?>
                            </td>
                            <td class="px-space-md py-space-sm">
                                <span class="px-2 py-0.5 rounded bg-surface-container text-xs font-semibold text-on-surface">
                                    <?= $prog['SubjectCount'] ?> Subjects
                                </span>
                            </td>
                            <td class="px-space-md py-space-sm">
                                <span class="px-2 py-0.5 rounded bg-primary/10 text-xs font-semibold text-primary">
                                    <?= $prog['StudentCount'] ?> Students
                                </span>
                            </td>
                            <td class="px-space-md py-space-sm text-right">
                                <form method="POST" action="../controllers/admin_action_controller.php" class="inline" onsubmit="return confirm('Are you sure you want to delete this program?');">
                                    <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token'] ?? '') ?>">
                                    <input type="hidden" name="action" value="delete_program">
                                    <input type="hidden" name="program_id" value="<?= $prog['ProgramID'] ?>">
                                    <button type="submit" class="p-1.5 text-tertiary hover:text-error rounded hover:bg-surface-container" title="Delete Program">
                                        <i class="fa-solid fa-trash-can text-[18px]"></i>
                                    </button>
                                </form>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </section>

    </div>
</main>

<!-- Add Program Modal -->
<div id="addProgModal" class="fixed inset-0 z-50 bg-black/40 backdrop-blur-sm hidden flex items-center justify-center p-4">
    <div class="bg-surface-container-lowest rounded-2xl shadow-xl max-w-md w-full p-6 border border-outline-variant/40">
        <div class="flex items-center justify-between pb-3 border-b border-surface-container">
            <h3 class="font-headline-sm text-headline-sm text-on-surface">Add New Degree Program</h3>
            <button onclick="document.getElementById('addProgModal').classList.add('hidden')" class="text-tertiary hover:text-on-surface">
                <i class="fa-solid fa-xmark"></i>
            </button>
        </div>
        <form method="POST" action="../controllers/admin_action_controller.php" class="flex flex-col gap-4 mt-4 text-sm">
            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token'] ?? '') ?>">
            <input type="hidden" name="action" value="add_program">

            <div>
                <label class="block text-xs font-semibold text-tertiary uppercase mb-1">Program Title</label>
                <input type="text" name="program_name" required placeholder="e.g. BS Medical Technology" class="w-full h-10 px-3 rounded-lg bg-surface-container-low border border-surface-container text-sm focus:outline-none focus:border-primary"/>
            </div>

            <div>
                <label class="block text-xs font-semibold text-tertiary uppercase mb-1">Collegiate Department</label>
                <select name="department_id" required class="w-full h-10 px-3 rounded-lg bg-surface-container-low border border-surface-container text-sm focus:outline-none focus:border-primary">
                    <option value="">Select Department...</option>
                    <?php foreach ($departments as $d): ?>
                        <option value="<?= $d['departmentID'] ?>"><?= htmlspecialchars($d['DepartmentName']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="flex items-center justify-end gap-2 mt-2 pt-3 border-t border-surface-container">
                <button type="button" onclick="document.getElementById('addProgModal').classList.add('hidden')" class="px-4 py-2 rounded-lg bg-surface-container text-sm font-medium text-tertiary hover:bg-surface-container-high">Cancel</button>
                <button type="submit" class="px-4 py-2 rounded-lg bg-primary text-on-primary text-sm font-semibold hover:bg-primary-container">Save Program</button>
            </div>
        </form>
    </div>
</div>

<?php include 'includes/footer.php'; ?>