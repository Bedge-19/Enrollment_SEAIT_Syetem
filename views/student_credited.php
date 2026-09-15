<?php
session_start();
require_once '../config/db.php';

if (!isset($_SESSION['UserType']) || $_SESSION['UserType'] !== 'Student') {
    header("Location: login.php");
    exit;
}

$studentId = $_SESSION['StudentID'] ?? 0;

// Fetch student info
$stmt = $pdo->prepare("
    SELECT s.*, p.ProgramName, st.TypeName
    FROM student s
    LEFT JOIN program p ON s.ProgramID = p.ProgramID
    LEFT JOIN student_type st ON s.StudentTypeID = st.StudentTypeID
    WHERE s.StudentID = ?
");
$stmt->execute([$studentId]);
$student = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$student) {
    header("Location: login.php?error=" . urlencode("Student record not found."));
    exit;
}

// Fetch credited subjects
$creditStmt = $pdo->prepare("
    SELECT cs.*, sub.SubjectCode, sub.SubjectTitle, sub.Units
    FROM credited_subject cs
    JOIN subject sub ON cs.SubjectID = sub.SubjectID
    WHERE cs.StudentID = ?
    ORDER BY cs.CreditID ASC
");
$creditStmt->execute([$studentId]);
$creditedSubjects = $creditStmt->fetchAll(PDO::FETCH_ASSOC);

$totalCreditedUnits = 0;
foreach ($creditedSubjects as $cs) {
    if ($cs['Status'] === 'Approved') {
        $totalCreditedUnits += (int)$cs['Units'];
    }
}
?>
<?php include 'includes/header.php'; ?>
<?php include 'includes/sidebar.php'; ?>
<div class="pl-72 flex flex-col min-h-screen">
<?php include 'includes/topbar.php'; ?>
<main class="w-full pt-16 flex-1 px-gutter py-space-lg">
    <div class="flex flex-col w-full gap-space-lg max-w-6xl mx-auto">
        
        <!-- Header Banner -->
        <section class="bg-surface-container-lowest p-space-lg rounded-xl shadow-[0_1px_8px_rgba(0,0,0,0.04)] border border-surface-container flex flex-col md:flex-row justify-between items-start md:items-center gap-space-md">
            <div>
                <div class="flex items-center gap-2 mb-1">
                    <span class="px-2.5 py-0.5 rounded-full text-xs font-bold uppercase tracking-wider bg-primary/10 text-primary">Academic Records</span>
                    <span class="text-xs text-tertiary">Step 4B • Advanced Standing</span>
                </div>
                <h1 class="font-headline-lg text-headline-lg text-on-surface font-bold">Credited Subjects</h1>
                <p class="font-body-md text-body-md text-tertiary">
                    Official record of courses credited from previous collegiate institutions or cross-enrollment.
                </p>
            </div>
            
            <div class="flex items-center gap-space-sm bg-surface-container-low px-space-md py-space-sm rounded-xl border border-surface-container">
                <i class="fa-solid fa-user-shield text-primary text-3xl"></i>
                <div>
                    <div class="font-headline-sm text-headline-sm font-bold text-on-surface"><?= $totalCreditedUnits ?> <span class="text-sm font-normal text-tertiary">Units</span></div>
                    <div class="text-xs font-semibold uppercase text-tertiary">Total Approved Credits</div>
                </div>
            </div>
        </section>

        <!-- Student Profile Bar -->
        <div class="bg-surface-container-lowest p-space-md rounded-xl shadow-[0_1px_8px_rgba(0,0,0,0.04)] border border-surface-container flex flex-wrap items-center justify-between gap-space-sm">
            <div class="flex items-center gap-space-sm">
                <div class="w-10 h-10 rounded-full bg-primary/10 text-primary flex items-center justify-center font-bold">
                    <?= substr($student['FirstName'] ?? 'S', 0, 1) . substr($student['LastName'] ?? '', 0, 1) ?>
                </div>
                <div>
                    <div class="font-title-md text-title-md font-bold text-on-surface">
                        <?= htmlspecialchars(($student['LastName'] ?? '') . ', ' . ($student['FirstName'] ?? '')) ?>
                    </div>
                    <div class="text-xs text-tertiary">
                        Student No: <span class="font-mono font-bold text-primary"><?= htmlspecialchars($student['StudentNo'] ?? '') ?></span> • Classification: <span class="font-medium text-on-surface"><?= htmlspecialchars($student['TypeName'] ?? 'Regular') ?></span>
                    </div>
                </div>
            </div>
            <div class="text-xs text-tertiary bg-surface-container-low px-space-sm py-1 rounded-lg border border-surface-container">
                Curriculum: <span class="font-semibold text-on-surface"><?= htmlspecialchars($student['ProgramName'] ?? 'General') ?></span>
            </div>
        </div>

        <!-- Credited Subjects Table -->
        <div class="bg-surface-container-lowest rounded-xl shadow-[0_1px_8px_rgba(0,0,0,0.04)] border border-surface-container overflow-hidden">
            <div class="p-space-md bg-surface-container-low border-b border-surface-container flex justify-between items-center">
                <div class="flex items-center gap-2">
                    <i class="fa-solid fa-clipboard-check text-primary text-[20px]"></i>
                    <h2 class="font-title-md text-title-md font-bold text-on-surface">List of Approved Credited Subjects</h2>
                </div>
                <span class="text-xs font-semibold px-2.5 py-1 rounded-full bg-surface-container text-tertiary">
                    <?= count($creditedSubjects) ?> Course(s) Evaluated
                </span>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="bg-surface-container-low/50 text-tertiary font-label-sm text-label-sm uppercase tracking-wider border-b border-surface-container">
                            <th class="px-space-md py-space-sm font-bold">Subject Code</th>
                            <th class="px-space-md py-space-sm font-bold">Descriptive Title</th>
                            <th class="px-space-md py-space-sm font-bold text-center">Units</th>
                            <th class="px-space-md py-space-sm font-bold">Equivalency Details & School Remarks</th>
                            <th class="px-space-md py-space-sm font-bold text-center">Status</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y border-surface-container font-body-sm text-body-sm">
                        <?php if (!empty($creditedSubjects)): ?>
                            <?php foreach ($creditedSubjects as $sub): ?>
                            <tr class="hover:bg-surface-container-low/30 transition-colors">
                                <td class="px-space-md py-space-sm font-mono font-bold text-primary">
                                    <?= htmlspecialchars($sub['SubjectCode']) ?>
                                </td>
                                <td class="px-space-md py-space-sm font-medium text-on-surface">
                                    <?= htmlspecialchars($sub['SubjectTitle']) ?>
                                </td>
                                <td class="px-space-md py-space-sm text-center font-semibold text-on-surface">
                                    <?= htmlspecialchars($sub['Units']) ?>
                                </td>
                                <td class="px-space-md py-space-sm text-tertiary">
                                    <?= htmlspecialchars($sub['Remarks'] ?: 'Equivalency verified by Registrar Evaluation Desk') ?>
                                </td>
                                <td class="px-space-md py-space-sm text-center">
                                    <?php if ($sub['Status'] === 'Approved'): ?>
                                        <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-xs font-bold bg-green-100 text-green-800">
                                            <i class="fa-solid fa-circle-check text-[14px]"></i> Approved
                                        </span>
                                    <?php elseif ($sub['Status'] === 'Pending'): ?>
                                        <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-xs font-bold bg-amber-100 text-amber-800">
                                            <i class="fa-solid fa-clock text-[14px]"></i> Pending Review
                                        </span>
                                    <?php else: ?>
                                        <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-xs font-bold bg-gray-100 text-gray-700">
                                            <?= htmlspecialchars($sub['Status']) ?>
                                        </span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="5" class="px-space-md py-space-xl text-center">
                                    <div class="flex flex-col items-center justify-center gap-space-xs text-tertiary">
                                        <i class="fa-solid fa-book text-4xl text-tertiary/60"></i>
                                        <p class="font-title-md text-title-md font-semibold text-on-surface">No Credited Subjects on Record</p>
                                        <p class="text-sm max-w-md">
                                            If you are a transferee, second courser, or cross-enrollee, your credited courses will appear here once reviewed and endorsed by the Registrar.
                                        </p>
                                    </div>
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Crediting Policy Guide -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-space-md">
            <div class="bg-surface-container-lowest p-space-md rounded-xl shadow-[0_1px_8px_rgba(0,0,0,0.04)] border border-surface-container flex gap-space-sm">
                <i class="fa-solid fa-circle-info text-primary text-2xl shrink-0"></i>
                <div class="space-y-1">
                    <h3 class="font-title-sm text-title-sm font-bold text-on-surface">Transferee Crediting Policy</h3>
                    <p class="font-body-sm text-body-sm text-tertiary leading-relaxed">
                        Courses taken from CHED/DepEd accredited institutions are eligible for crediting provided the course description and syllabi match at least 80% of our curriculum and a minimum passing grade of 2.0 or higher was attained.
                    </p>
                </div>
            </div>

            <div class="bg-surface-container-lowest p-space-md rounded-xl shadow-[0_1px_8px_rgba(0,0,0,0.04)] border border-surface-container flex gap-space-sm">
                <i class="fa-solid fa-circle-question text-secondary text-2xl shrink-0"></i>
                <div class="space-y-1">
                    <h3 class="font-title-sm text-title-sm font-bold text-on-surface">Need Additional Crediting?</h3>
                    <p class="font-body-sm text-body-sm text-tertiary leading-relaxed">
                        Please visit the <strong>Registrar's Office (Subject Crediting Desk)</strong> with your Official Transcript of Records (OTR) and course syllabi for academic evaluation before final enrollment blocking.
                    </p>
                </div>
            </div>
        </div>

    </div>
</main>
<?php include 'includes/footer.php'; ?>
</div>