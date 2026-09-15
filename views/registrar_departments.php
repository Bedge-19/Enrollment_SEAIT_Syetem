<?php
session_start();
require_once '../config/db.php';

if (!isset($_SESSION['UserType']) || $_SESSION['UserType'] !== 'Staff' || !in_array($_SESSION['Role'] ?? '', ['Registrar', 'Admin'])) {
    header("Location: login.php?error=" . urlencode("Access denied. Registrar privileges required."));
    exit;
}

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
                        <i class="fa-solid fa-diagram-project text-[18px]"></i>
                        <span>Registrar Module</span>
                    </div>
                    <h1 class="font-headline-lg text-headline-lg text-on-surface tracking-tight">Programs & Departments</h1>
                    <p class="font-body-md text-body-md text-tertiary">
                        Collegiate organizational tree, degree program hierarchy, and curriculum offerings.
                    </p>
                </div>
            </div>
        </section>

        <!-- Department & Program Accordion/Cards -->
        <div class="flex flex-col gap-space-md">
            <?php foreach($departments as $d): 
                $stmtProg = $pdo->prepare("
                    SELECT p.*, COUNT(s.StudentID) as StudentCount, COUNT(DISTINCT sub.SubjectID) as SubjectCount
                    FROM program p
                    LEFT JOIN student s ON p.ProgramID = s.ProgramID
                    LEFT JOIN subject sub ON p.ProgramID = sub.ProgramID
                    WHERE p.DepartmentID = ?
                    GROUP BY p.ProgramID
                    ORDER BY p.ProgramName ASC
                ");
                $stmtProg->execute([$d['departmentID']]);
                $deptPrograms = $stmtProg->fetchAll();
            ?>
            <div class="bg-surface-container-lowest rounded-xl shadow-[0_1px_8px_rgba(0,0,0,0.04)] border border-outline-variant/30 overflow-hidden">
                <div class="p-space-md bg-surface-container-low/60 border-b border-surface-container flex items-center justify-between">
                    <div class="flex items-center gap-2">
                        <i class="fa-solid fa-building-columns text-primary text-[22px]"></i>
                        <h2 class="font-title-lg text-title-lg font-bold text-on-surface"><?= htmlspecialchars($d['DepartmentName']) ?></h2>
                    </div>
                    <span class="text-xs font-semibold px-2.5 py-0.5 rounded-full bg-surface-container text-tertiary">
                        <?= count($deptPrograms) ?> Program Offerings
                    </span>
                </div>
                <div class="p-space-md grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-3">
                    <?php foreach($deptPrograms as $prog): ?>
                    <div class="p-3 rounded-lg bg-surface-container-lowest border border-surface-container hover:border-primary/40 transition-colors flex flex-col justify-between">
                        <div>
                            <span class="font-semibold text-xs text-on-surface block leading-tight"><?= htmlspecialchars($prog['ProgramName']) ?></span>
                        </div>
                        <div class="mt-3 pt-2 border-t border-surface-container flex items-center justify-between text-[11px] text-tertiary">
                            <span><?= $prog['SubjectCount'] ?> Courses</span>
                            <span class="font-bold text-primary"><?= $prog['StudentCount'] ?> Enrollees</span>
                        </div>
                    </div>
                    <?php endforeach; ?>
                    <?php if (empty($deptPrograms)): ?>
                    <div class="col-span-3 text-xs text-tertiary py-3 text-center">No degree programs assigned to this department yet.</div>
                    <?php endif; ?>
                </div>
            </div>
            <?php endforeach; ?>
        </div>

    </div>
</main>
<?php include 'includes/footer.php'; ?>