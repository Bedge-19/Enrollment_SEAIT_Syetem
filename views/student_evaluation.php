<?php
session_start();
require_once '../config/db.php';

if (!isset($_SESSION['UserType']) || $_SESSION['UserType'] !== 'Student') {
    header("Location: login.php?error=" . urlencode("Access denied. Student portal privileges required."));
    exit;
}

$studentId = $_SESSION['StudentID'] ?? null;
if (!$studentId) die("Session expired. Please re-login.");

$stmt = $pdo->prepare("
    SELECT ev.*, st.LastName as EvaluatorName
    FROM evaluation ev
    LEFT JOIN staff st ON ev.StaffID = st.StaffID
    WHERE ev.StudentID = ?
    ORDER BY ev.EvaluationID DESC
    LIMIT 1
");
$stmt->execute([$studentId]);
$eval = $stmt->fetch();

$evaluatedSubjects = [];
if ($eval) {
    $stmtSubs = $pdo->prepare("
        SELECT es.*, sub.SubjectCode, sub.SubjectTitle, sub.Units
        FROM evaluation_subject es
        JOIN subject sub ON es.SubjectID = sub.SubjectID
        WHERE es.EvaluationID = ?
        ORDER BY sub.SubjectCode ASC
    ");
    $stmtSubs->execute([$eval['EvaluationID']]);
    $evaluatedSubjects = $stmtSubs->fetchAll();
}
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
                        <span>Academic Assessment</span>
                    </div>
                    <h1 class="font-headline-lg text-headline-lg text-on-surface tracking-tight">Curriculum Evaluation</h1>
                    <p class="font-body-md text-body-md text-tertiary">
                        Official academic advising evaluation sheet, approved semester subject load, and prerequisite clearances.
                    </p>
                </div>
            </div>
        </section>

        <!-- Evaluation Header Card -->
        <section class="bg-surface-container-lowest p-space-lg rounded-xl shadow-[0_1px_8px_rgba(0,0,0,0.04)] border border-outline-variant/30 flex flex-col md:flex-row md:items-center justify-between gap-4">
            <div class="flex items-center gap-4">
                <div class="w-14 h-14 rounded-2xl <?= ($eval && $eval['Status'] === 'Approved') ? 'bg-emerald-100 text-emerald-700' : 'bg-amber-100 text-amber-700' ?> flex items-center justify-center font-bold">
                    <i class="fa-solid <?= ($eval && $eval['Status'] === 'Approved') ? 'fa-circle-check' : 'fa-hourglass-half' ?> text-[28px]"></i>
                </div>
                <div>
                    <span class="text-xs font-semibold text-tertiary uppercase">Academic Evaluation Verdict</span>
                    <h2 class="text-2xl font-black text-on-surface">
                        <?= $eval ? htmlspecialchars($eval['Status']) : 'Not Yet Evaluated' ?>
                    </h2>
                    <p class="text-xs text-tertiary mt-0.5">
                        Evaluated by: <?= !empty($eval['EvaluatorName']) ? 'Registrar Evaluator ' . htmlspecialchars($eval['EvaluatorName']) : 'Academic Evaluation Board' ?>
                    </p>
                </div>
            </div>

            <div class="text-xs text-tertiary md:text-right">
                <div>Date: <span class="font-semibold text-on-surface"><?= !empty($eval['EvaluationDate']) ? date('F d, Y', strtotime($eval['EvaluationDate'])) : 'Pending' ?></span></div>
                <div>Evaluation Ref: <span class="font-mono text-primary font-bold">EVAL-<?= str_pad($eval['EvaluationID'] ?? 0, 5, '0', STR_PAD_LEFT) ?></span></div>
            </div>
        </section>

        <!-- Evaluated Subjects Table -->
        <section class="bg-surface-container-lowest rounded-xl shadow-[0_1px_8px_rgba(0,0,0,0.04)] border border-outline-variant/30 overflow-hidden">
            <div class="p-space-md border-b border-surface-container flex items-center justify-between">
                <h3 class="font-title-md text-title-md text-on-surface">Approved Curriculum Courses</h3>
                <span class="text-xs text-primary font-bold">
                    Total Units: <?= array_sum(array_column($evaluatedSubjects, 'Units')) ?>
                </span>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse text-sm">
                    <thead>
                        <tr class="bg-surface-container-low text-tertiary text-xs uppercase font-label-sm border-b border-surface-container">
                            <th class="px-space-md py-space-sm">Course Code</th>
                            <th class="px-space-md py-space-sm">Course Description</th>
                            <th class="px-space-md py-space-sm">Units</th>
                            <th class="px-space-md py-space-sm">Evaluation Status</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-surface-container">
                        <?php foreach($evaluatedSubjects as $es): ?>
                        <tr class="hover:bg-surface-container-low/50 transition-colors">
                            <td class="px-space-md py-space-sm font-mono font-bold text-primary">
                                <?= htmlspecialchars($es['SubjectCode']) ?>
                            </td>
                            <td class="px-space-md py-space-sm font-medium text-on-surface">
                                <?= htmlspecialchars($es['SubjectTitle']) ?>
                            </td>
                            <td class="px-space-md py-space-sm font-semibold text-on-surface">
                                <?= number_format($es['Units'], 1) ?>
                            </td>
                            <td class="px-space-md py-space-sm">
                                <?php if ($es['SubjectStatus'] === 'Approved'): ?>
                                    <span class="px-2.5 py-0.5 rounded-full text-xs font-semibold bg-emerald-100 text-emerald-800">Approved for Enrollment</span>
                                <?php elseif ($es['SubjectStatus'] === 'Credited'): ?>
                                    <span class="px-2.5 py-0.5 rounded-full text-xs font-semibold bg-blue-100 text-blue-800">Credited (Transferee)</span>
                                <?php else: ?>
                                    <span class="px-2.5 py-0.5 rounded-full text-xs font-semibold bg-amber-100 text-amber-800">Pending Evaluation</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                        <?php if(empty($evaluatedSubjects)): ?>
                        <tr>
                            <td colspan="4" class="px-space-md py-8 text-center text-tertiary">No curriculum courses currently evaluated. Waiting for Registrar academic advising.</td>
                        </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </section>

    </div>
</main>
<?php include 'includes/footer.php'; ?>