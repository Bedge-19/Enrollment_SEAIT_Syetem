<?php
session_start();
require_once '../config/db.php';

if (!isset($_SESSION['UserType']) || $_SESSION['UserType'] !== 'Student') {
    header("Location: login.php?error=" . urlencode("Access denied. Student portal privileges required."));
    exit;
}

$studentId = $_SESSION['StudentID'] ?? null;
if (!$studentId) die("Session expired. Please re-login.");

// Fetch student & profile details
$stmt = $pdo->prepare("
    SELECT s.*, sp.GuardianName, sp.GuardianContactNo, sp.PreviousSchoolName, sp.PreviousProgram, sp.LastYearLevelCompleted, sp.GWA,
           p.ProgramName, d.DepartmentName, st.TypeName
    FROM student s
    LEFT JOIN student_profile sp ON s.StudentID = sp.StudentID
    LEFT JOIN program p ON s.ProgramID = p.ProgramID
    LEFT JOIN department d ON p.DepartmentID = d.departmentID
    LEFT JOIN student_type st ON s.StudentTypeID = st.StudentTypeID
    WHERE s.StudentID = ?
");
$stmt->execute([$studentId]);
$student = $stmt->fetch();
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
                        <i class="fa-solid fa-user text-[18px]"></i>
                        <span>Student Portal</span>
                    </div>
                    <h1 class="font-headline-lg text-headline-lg text-on-surface tracking-tight">Profile & Background</h1>
                    <p class="font-body-md text-body-md text-tertiary">
                        Review your personal demographic data, emergency guardian contact, and academic credentials.
                    </p>
                </div>
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

        <!-- Profile Form & Info Card -->
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-space-lg">

            <!-- Left: Read-Only Academic Identity (1 Col) -->
            <div class="bg-surface-container-lowest p-space-lg rounded-xl shadow-[0_1px_8px_rgba(0,0,0,0.04)] border border-outline-variant/30 flex flex-col items-center text-center">
                <div class="w-24 h-24 rounded-full bg-primary/10 text-primary flex items-center justify-center font-bold text-3xl mb-3 shadow-inner">
                    <?= strtoupper(substr($student['FirstName'] ?? 'S', 0, 1) . substr($student['LastName'] ?? 'T', 0, 1)) ?>
                </div>
                <h2 class="font-headline-sm text-headline-sm text-on-surface">
                    <?= htmlspecialchars(($student['FirstName'] ?? '') . ' ' . ($student['LastName'] ?? '')) ?>
                </h2>
                <p class="font-mono text-sm font-bold text-primary mt-1"><?= htmlspecialchars($student['StudentNo'] ?? 'N/A') ?></p>

                <div class="w-full mt-6 pt-4 border-t border-surface-container flex flex-col gap-2.5 text-left text-xs">
                    <div>
                        <span class="text-tertiary block">Degree Program</span>
                        <span class="font-semibold text-on-surface text-sm"><?= htmlspecialchars($student['ProgramName'] ?? 'Unassigned') ?></span>
                    </div>
                    <div>
                        <span class="text-tertiary block">Department / College</span>
                        <span class="font-semibold text-on-surface"><?= htmlspecialchars($student['DepartmentName'] ?? 'General') ?></span>
                    </div>
                    <div>
                        <span class="text-tertiary block">Student Classification</span>
                        <span class="font-semibold text-on-surface"><?= htmlspecialchars($student['TypeName'] ?? 'Regular') ?></span>
                    </div>
                    <div>
                        <span class="text-tertiary block">Previous School & GWA</span>
                        <span class="font-semibold text-on-surface"><?= htmlspecialchars($student['PreviousSchoolName'] ?? 'High School') ?> (GWA: <?= htmlspecialchars($student['GWA'] ?? 'N/A') ?>)</span>
                    </div>
                </div>
            </div>

            <!-- Right: Editable Contact & Guardian Information (2 Cols) -->
            <div class="lg:col-span-2 bg-surface-container-lowest p-space-lg rounded-xl shadow-[0_1px_8px_rgba(0,0,0,0.04)] border border-outline-variant/30">
                <div class="pb-3 border-b border-surface-container mb-4">
                    <h2 class="font-title-md text-title-md text-on-surface">Editable Contact & Family Background</h2>
                    <p class="text-xs text-tertiary">Keep your contact numbers and emergency notifications up to date</p>
                </div>

                <form method="POST" action="../controllers/student_action_controller.php" class="flex flex-col gap-4 text-sm">
                    <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token'] ?? '') ?>">
                    <input type="hidden" name="action" value="update_profile">

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-semibold text-tertiary uppercase mb-1">Email Address</label>
                            <input type="email" name="Email" value="<?= htmlspecialchars($student['Email'] ?? '') ?>" required class="w-full h-10 px-3 rounded-lg bg-surface-container-low border border-surface-container text-sm focus:outline-none focus:border-primary"/>
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-tertiary uppercase mb-1">Mobile Contact No</label>
                            <input type="text" name="ContactNo" value="<?= htmlspecialchars($student['ContactNo'] ?? '') ?>" required class="w-full h-10 px-3 rounded-lg bg-surface-container-low border border-surface-container text-sm focus:outline-none focus:border-primary"/>
                        </div>
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-tertiary uppercase mb-1">Current Residential Address</label>
                        <input type="text" name="Address" value="<?= htmlspecialchars($student['Address'] ?? '') ?>" required class="w-full h-10 px-3 rounded-lg bg-surface-container-low border border-surface-container text-sm focus:outline-none focus:border-primary"/>
                    </div>

                    <div class="pt-4 border-t border-surface-container">
                        <h3 class="font-semibold text-on-surface text-sm mb-3">Emergency Guardian Details</h3>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-xs font-semibold text-tertiary uppercase mb-1">Parent / Guardian Name</label>
                                <input type="text" name="GuardianName" value="<?= htmlspecialchars($student['GuardianName'] ?? '') ?>" required class="w-full h-10 px-3 rounded-lg bg-surface-container-low border border-surface-container text-sm focus:outline-none focus:border-primary"/>
                            </div>
                            <div>
                                <label class="block text-xs font-semibold text-tertiary uppercase mb-1">Guardian Emergency Contact</label>
                                <input type="text" name="GuardianContactNo" value="<?= htmlspecialchars($student['GuardianContactNo'] ?? '') ?>" required class="w-full h-10 px-3 rounded-lg bg-surface-container-low border border-surface-container text-sm focus:outline-none focus:border-primary"/>
                            </div>
                        </div>
                    </div>

                    <div class="flex items-center justify-end gap-2 pt-3 border-t border-surface-container">
                        <button type="submit" class="px-5 py-2.5 bg-primary hover:bg-primary-container text-on-primary font-semibold text-sm rounded-lg transition-colors shadow-sm flex items-center gap-2">
                            <i class="fa-solid fa-floppy-disk text-[18px]"></i>
                            <span>Save Profile Changes</span>
                        </button>
                    </div>
                </form>
            </div>

        </div>

    </div>
</main>
<?php include 'includes/footer.php'; ?>