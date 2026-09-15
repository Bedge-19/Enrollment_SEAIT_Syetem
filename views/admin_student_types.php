<?php
session_start();
require_once '../config/db.php';

if (!isset($_SESSION['UserType']) || $_SESSION['UserType'] !== 'Staff' || ($_SESSION['Role'] ?? '') !== 'Admin') {
    header("Location: login.php?error=" . urlencode("Access denied. Administrator privileges required."));
    exit;
}

$stmt = $pdo->query("
    SELECT st.*, COUNT(s.StudentID) AS StudentCount 
    FROM student_type st 
    LEFT JOIN student s ON st.StudentTypeID = s.StudentTypeID 
    GROUP BY st.StudentTypeID 
    ORDER BY st.StudentTypeID ASC
");
$studentTypes = $stmt->fetchAll();
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
                        <i class="fa-solid fa-shapes text-[18px]"></i>
                        <span>Admin Module</span>
                    </div>
                    <h1 class="font-headline-lg text-headline-lg text-on-surface tracking-tight">Student Types Master Data</h1>
                    <p class="font-body-md text-body-md text-tertiary">
                        Manage admission student classifications (New, Transferee, Returnee, Cross-Enrollee).
                    </p>
                </div>
                <button onclick="document.getElementById('addTypeModal').classList.remove('hidden')" class="flex items-center gap-space-xs px-4 py-2 bg-primary hover:bg-primary-container text-on-primary rounded-lg font-label-md text-label-md font-semibold transition-colors shadow-sm">
                    <i class="fa-solid fa-plus text-[18px]"></i>
                    <span>Add Student Type</span>
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

        <!-- Types Grid -->
        <section class="grid grid-cols-1 md:grid-cols-3 gap-space-md">
            <?php foreach ($studentTypes as $type): ?>
            <div class="bg-surface-container-lowest p-space-lg rounded-xl shadow-[0_1px_8px_rgba(0,0,0,0.04)] border border-outline-variant/30 flex flex-col justify-between">
                <div class="flex items-start justify-between">
                    <div class="w-10 h-10 rounded-lg bg-secondary/15 text-secondary flex items-center justify-center font-bold">
                        <i class="fa-solid fa-id-badge text-[22px]"></i>
                    </div>
                    <form method="POST" action="../controllers/admin_action_controller.php" onsubmit="return confirm('Delete this student type?');">
                        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token'] ?? '') ?>">
                        <input type="hidden" name="action" value="delete_student_type">
                        <input type="hidden" name="student_type_id" value="<?= $type['StudentTypeID'] ?>">
                        <button type="submit" class="p-1.5 text-tertiary hover:text-error rounded hover:bg-surface-container" title="Delete Type">
                            <i class="fa-solid fa-trash-can text-[18px]"></i>
                        </button>
                    </form>
                </div>
                <div class="mt-4">
                    <h3 class="font-headline-sm text-headline-sm text-on-surface"><?= htmlspecialchars($type['TypeName']) ?></h3>
                    <p class="text-xs text-tertiary mt-1">Classification Code #<?= $type['StudentTypeID'] ?></p>
                </div>
                <div class="mt-4 pt-3 border-t border-surface-container flex items-center justify-between text-xs">
                    <span class="text-tertiary">Active Enrollees:</span>
                    <span class="font-bold text-primary"><?= number_format($type['StudentCount']) ?> Students</span>
                </div>
            </div>
            <?php endforeach; ?>
        </section>

    </div>
</main>

<!-- Add Modal -->
<div id="addTypeModal" class="fixed inset-0 z-50 bg-black/40 backdrop-blur-sm hidden flex items-center justify-center p-4">
    <div class="bg-surface-container-lowest rounded-2xl shadow-xl max-w-md w-full p-6 border border-outline-variant/40">
        <div class="flex items-center justify-between pb-3 border-b border-surface-container">
            <h3 class="font-headline-sm text-headline-sm text-on-surface">Add Student Classification</h3>
            <button onclick="document.getElementById('addTypeModal').classList.add('hidden')" class="text-tertiary hover:text-on-surface">
                <i class="fa-solid fa-xmark"></i>
            </button>
        </div>
        <form method="POST" action="../controllers/admin_action_controller.php" class="flex flex-col gap-4 mt-4 text-sm">
            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token'] ?? '') ?>">
            <input type="hidden" name="action" value="add_student_type">

            <div>
                <label class="block text-xs font-semibold text-tertiary uppercase mb-1">Classification Name</label>
                <input type="text" name="type_name" required placeholder="e.g. Cross-Enrollee" class="w-full h-10 px-3 rounded-lg bg-surface-container-low border border-surface-container text-sm focus:outline-none focus:border-primary"/>
            </div>

            <div class="flex items-center justify-end gap-2 mt-2 pt-3 border-t border-surface-container">
                <button type="button" onclick="document.getElementById('addTypeModal').classList.add('hidden')" class="px-4 py-2 rounded-lg bg-surface-container text-sm font-medium text-tertiary hover:bg-surface-container-high">Cancel</button>
                <button type="submit" class="px-4 py-2 rounded-lg bg-primary text-on-primary text-sm font-semibold hover:bg-primary-container">Save Classification</button>
            </div>
        </form>
    </div>
</div>

<?php include 'includes/footer.php'; ?>