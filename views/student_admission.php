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
    SELECT a.*, s.StudentNo, s.FirstName, s.LastName, p.ProgramName, st.LastName as StaffLastName
    FROM admission a
    JOIN student s ON a.StudentID = s.StudentID
    LEFT JOIN program p ON s.ProgramID = p.ProgramID
    LEFT JOIN staff st ON a.StaffID = st.StaffID
    WHERE a.StudentID = ?
    ORDER BY a.AdmissionID DESC
    LIMIT 1
");
$stmt->execute([$studentId]);
$adm = $stmt->fetch();
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
                        <i class="fa-solid fa-clipboard-list text-[18px]"></i>
                        <span>Stage 1 of Matriculation</span>
                    </div>
                    <h1 class="font-headline-lg text-headline-lg text-on-surface tracking-tight">Admissions Application Status</h1>
                    <p class="font-body-md text-body-md text-tertiary">
                        View your formal admission docket, registrar review verdict, and submitted documentary requirements.
                    </p>
                </div>
            </div>
        </section>

        <!-- Status Card -->
        <section class="bg-surface-container-lowest p-space-lg rounded-xl shadow-[0_1px_8px_rgba(0,0,0,0.04)] border border-outline-variant/30 flex flex-col md:flex-row md:items-center justify-between gap-4">
            <div class="flex items-center gap-4">
                <div class="w-14 h-14 rounded-2xl <?= ($adm && $adm['Status'] === 'Approved') ? 'bg-emerald-100 text-emerald-700' : 'bg-amber-100 text-amber-700' ?> flex items-center justify-center font-bold">
                    <i class="fa-solid <?= ($adm && $adm['Status'] === 'Approved') ? 'fa-circle-check' : 'fa-clock' ?> text-[28px]"></i>
                </div>
                <div>
                    <span class="text-xs font-semibold text-tertiary uppercase">Application Status</span>
                    <h2 class="text-2xl font-black text-on-surface">
                        <?= htmlspecialchars($adm['Status'] ?? 'Not Submitted') ?>
                    </h2>
                    <p class="text-xs text-tertiary mt-0.5">
                        Reviewed by: <?= !empty($adm['StaffLastName']) ? 'Registrar ' . htmlspecialchars($adm['StaffLastName']) : 'University Admissions Board' ?>
                    </p>
                </div>
            </div>

            <div class="flex flex-col md:items-end text-xs text-tertiary gap-1">
                <div>Application Reference: <span class="font-mono font-bold text-primary"><?= htmlspecialchars($adm['StudentNo'] ?? 'N/A') ?></span></div>
                <div>Submission Date: <span class="font-semibold text-on-surface"><?= !empty($adm['SubmissionDate']) ? date('F d, Y', strtotime($adm['SubmissionDate'])) : 'N/A' ?></span></div>
                <div>Approval Date: <span class="font-semibold text-on-surface"><?= !empty($adm['ApprovalDate']) && $adm['ApprovalDate'] !== '1000-01-01' ? date('F d, Y', strtotime($adm['ApprovalDate'])) : 'Pending Review' ?></span></div>
            </div>
        </section>

        <!-- Documentary Requirements Checklist -->
        <section class="bg-surface-container-lowest p-space-lg rounded-xl shadow-[0_1px_8px_rgba(0,0,0,0.04)] border border-outline-variant/30">
            <div class="pb-3 border-b border-surface-container mb-4">
                <h3 class="font-title-md text-title-md text-on-surface">Documentary Requirements Checklist</h3>
                <p class="text-xs text-tertiary">Official institutional documents required for full matriculation</p>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-3 text-sm">
                <div class="p-3 rounded-lg bg-surface-container-low border border-surface-container flex items-center justify-between">
                    <div class="flex items-center gap-3">
                        <i class="fa-solid fa-circle-check text-emerald-600 text-[20px]"></i>
                        <span>High School Report Card (Form 138 / TOR)</span>
                    </div>
                    <span class="text-xs font-semibold text-emerald-700 bg-emerald-50 px-2.5 py-0.5 rounded-full">Verified</span>
                </div>

                <div class="p-3 rounded-lg bg-surface-container-low border border-surface-container flex items-center justify-between">
                    <div class="flex items-center gap-3">
                        <i class="fa-solid fa-circle-check text-emerald-600 text-[20px]"></i>
                        <span>PSA Authenticated Birth Certificate</span>
                    </div>
                    <span class="text-xs font-semibold text-emerald-700 bg-emerald-50 px-2.5 py-0.5 rounded-full">Verified</span>
                </div>

                <div class="p-3 rounded-lg bg-surface-container-low border border-surface-container flex items-center justify-between">
                    <div class="flex items-center gap-3">
                        <i class="fa-solid fa-circle-check text-emerald-600 text-[20px]"></i>
                        <span>Certificate of Good Moral Character</span>
                    </div>
                    <span class="text-xs font-semibold text-emerald-700 bg-emerald-50 px-2.5 py-0.5 rounded-full">Verified</span>
                </div>

                <div class="p-3 rounded-lg bg-surface-container-low border border-surface-container flex items-center justify-between">
                    <div class="flex items-center gap-3">
                        <i class="fa-solid fa-circle-check text-emerald-600 text-[20px]"></i>
                        <span>2x2 ID Photo White Background</span>
                    </div>
                    <span class="text-xs font-semibold text-emerald-700 bg-emerald-50 px-2.5 py-0.5 rounded-full">Verified</span>
                </div>
            </div>

            <div class="mt-4 p-3 bg-surface-container-low rounded-lg border border-surface-container text-xs text-tertiary flex items-start gap-2">
                <i class="fa-solid fa-circle-info text-primary text-[18px]"></i>
                <span>Registrar Remarks: <?= htmlspecialchars($adm['Remarks'] ?? 'No special conditions attached to application.') ?></span>
            </div>
        </section>

    </div>
</main>
<?php include 'includes/footer.php'; ?>