<?php
session_start();
require_once '../config/db.php';

if (!isset($_SESSION['UserType']) || $_SESSION['UserType'] !== 'Staff' || ($_SESSION['Role'] ?? '') !== 'Admin') {
    header("Location: login.php?error=" . urlencode("Access denied. Administrator privileges required."));
    exit;
}

// Fetch departments with counts
$stmt = $pdo->query("
    SELECT d.*, COUNT(p.ProgramID) AS ProgramCount, COUNT(DISTINCT s.StaffID) AS StaffCount
    FROM department d
    LEFT JOIN program p ON d.departmentID = p.DepartmentID
    LEFT JOIN staff s ON d.departmentID = s.DepartmentID
    GROUP BY d.departmentID
    ORDER BY d.DepartmentName ASC
");
$departments = $stmt->fetchAll();
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
                        <i class="fa-solid fa-building text-[18px]"></i>
                        <span>Admin Module</span>
                    </div>
                    <h1 class="font-headline-lg text-headline-lg text-on-surface tracking-tight">Department Management</h1>
                    <p class="font-body-md text-body-md text-tertiary">
                        Manage university collegiate divisions, academic departments, and vocational centers.
                    </p>
                </div>
                <button onclick="document.getElementById('addDeptModal').classList.remove('hidden')" class="flex items-center gap-space-xs px-4 py-2 bg-primary hover:bg-primary-container text-on-primary rounded-lg font-label-md text-label-md font-semibold transition-colors shadow-sm">
                    <i class="fa-solid fa-plus text-[18px]"></i>
                    <span>Add Department</span>
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

        <!-- Departments Grid -->
        <section class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-space-md">
            <?php foreach ($departments as $dept): ?>
            <div class="bg-surface-container-lowest p-space-lg rounded-xl shadow-[0_1px_8px_rgba(0,0,0,0.04)] border border-outline-variant/30 flex flex-col justify-between hover:border-primary/40 transition-colors">
                <div>
                    <div class="flex items-start justify-between gap-2">
                        <div class="w-10 h-10 rounded-lg bg-primary/10 text-primary flex items-center justify-center font-bold">
                            <i class="fa-solid fa-building-columns text-[22px]"></i>
                        </div>
                        <form method="POST" action="../controllers/admin_action_controller.php" onsubmit="return confirm('Are you sure you want to delete this department?');">
                            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token'] ?? '') ?>">
                            <input type="hidden" name="action" value="delete_department">
                            <input type="hidden" name="department_id" value="<?= $dept['departmentID'] ?>">
                            <button type="submit" class="p-1.5 text-tertiary hover:text-error transition-colors rounded hover:bg-surface-container" title="Delete Department">
                                <i class="fa-solid fa-trash-can text-[18px]"></i>
                            </button>
                        </form>
                    </div>
                    <h3 class="font-headline-sm text-headline-sm text-on-surface mt-3 leading-snug">
                        <?= htmlspecialchars($dept['DepartmentName']) ?>
                    </h3>
                </div>

                <div class="mt-space-md pt-space-sm border-t border-surface-container flex items-center justify-between text-xs text-tertiary">
                    <span class="font-semibold text-primary"><?= $dept['ProgramCount'] ?> Degree Programs</span>
                    <span><?= $dept['StaffCount'] ?> Assigned Staff</span>
                </div>
            </div>
            <?php endforeach; ?>
        </section>

    </div>
</main>

<!-- Add Dept Modal -->
<div id="addDeptModal" class="fixed inset-0 z-50 bg-black/40 backdrop-blur-sm hidden flex items-center justify-center p-4">
    <div class="bg-surface-container-lowest rounded-2xl shadow-xl max-w-md w-full p-6 border border-outline-variant/40">
        <div class="flex items-center justify-between pb-3 border-b border-surface-container">
            <h3 class="font-headline-sm text-headline-sm text-on-surface">Add New Department</h3>
            <button onclick="document.getElementById('addDeptModal').classList.add('hidden')" class="text-tertiary hover:text-on-surface">
                <i class="fa-solid fa-xmark"></i>
            </button>
        </div>
        <form method="POST" action="../controllers/admin_action_controller.php" class="flex flex-col gap-4 mt-4 text-sm">
            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token'] ?? '') ?>">
            <input type="hidden" name="action" value="add_department">

            <div>
                <label class="block text-xs font-semibold text-tertiary uppercase mb-1">Department / College Name</label>
                <input type="text" name="department_name" required placeholder="e.g. College of Nursing" class="w-full h-10 px-3 rounded-lg bg-surface-container-low border border-surface-container text-sm focus:outline-none focus:border-primary"/>
            </div>

            <div class="flex items-center justify-end gap-2 mt-2 pt-3 border-t border-surface-container">
                <button type="button" onclick="document.getElementById('addDeptModal').classList.add('hidden')" class="px-4 py-2 rounded-lg bg-surface-container text-sm font-medium text-tertiary hover:bg-surface-container-high">Cancel</button>
                <button type="submit" class="px-4 py-2 rounded-lg bg-primary text-on-primary text-sm font-semibold hover:bg-primary-container">Save Department</button>
            </div>
        </form>
    </div>
</div>

<?php include 'includes/footer.php'; ?>