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
    SELECT cl.*, st.LastName as StaffLastName
    FROM clinic cl
    LEFT JOIN staff st ON cl.StaffID = st.StaffID
    WHERE cl.StudentID = ?
    ORDER BY cl.ClinicID DESC
");
$stmt->execute([$studentId]);
$checkups = $stmt->fetchAll();

$latest = $checkups[0] ?? null;
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
                        <i class="fa-solid fa-notes-medical text-[18px]"></i>
                        <span>Campus Health Service</span>
                    </div>
                    <h1 class="font-headline-lg text-headline-lg text-on-surface tracking-tight">Clinic / Medical Status</h1>
                    <p class="font-body-md text-body-md text-tertiary">
                        Your university health records, recorded vital statistics, and annual physical clearance certificates.
                    </p>
                </div>
            </div>
        </section>

        <!-- Current Health Card -->
        <section class="bg-surface-container-lowest p-space-lg rounded-xl shadow-[0_1px_8px_rgba(0,0,0,0.04)] border border-outline-variant/30 flex flex-col md:flex-row md:items-center justify-between gap-4">
            <div class="flex items-center gap-4">
                <div class="w-14 h-14 rounded-2xl <?= ($latest && $latest['Status'] === 'Completed') ? 'bg-emerald-100 text-emerald-700' : 'bg-amber-100 text-amber-700' ?> flex items-center justify-center font-bold">
                    <i class="fa-solid <?= ($latest && $latest['Status'] === 'Completed') ? 'fa-heart-pulse' : 'fa-clipboard-user' ?> text-[28px]"></i>
                </div>
                <div>
                    <span class="text-xs font-semibold text-tertiary uppercase">Medical Fitness Rating</span>
                    <h2 class="text-2xl font-black text-on-surface">
                        <?= ($latest && $latest['Status'] === 'Completed') ? 'Fit for Academic Enrollment' : ($latest ? htmlspecialchars($latest['Status']) : 'Pending Checkup') ?>
                    </h2>
                    <p class="text-xs text-tertiary mt-0.5">
                        Examined by: <?= !empty($latest['StaffLastName']) ? 'Dr./Nurse ' . htmlspecialchars($latest['StaffLastName']) : 'Campus Health Physician' ?>
                    </p>
                </div>
            </div>

            <div class="text-xs text-tertiary md:text-right">
                <div>Exam Date: <span class="font-semibold text-on-surface"><?= !empty($latest['CompletionDate']) ? date('F d, Y', strtotime($latest['CompletionDate'])) : 'Awaiting Examination' ?></span></div>
                <div>Certificate Ref: <span class="font-mono text-primary font-bold">MED-<?= str_pad($latest['ClinicID'] ?? 0, 5, '0', STR_PAD_LEFT) ?></span></div>
            </div>
        </section>

        <!-- Recorded Vitals & Checkup History -->
        <section class="bg-surface-container-lowest rounded-xl shadow-[0_1px_8px_rgba(0,0,0,0.04)] border border-outline-variant/30 overflow-hidden">
            <div class="p-space-md border-b border-surface-container">
                <h3 class="font-title-md text-title-md text-on-surface">Clinical Examination Records</h3>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse text-sm">
                    <thead>
                        <tr class="bg-surface-container-low text-tertiary text-xs uppercase font-label-sm border-b border-surface-container">
                            <th class="px-space-md py-space-sm">Examination Date</th>
                            <th class="px-space-md py-space-sm">Vital Statistics & Findings</th>
                            <th class="px-space-md py-space-sm">Result Status</th>
                            <th class="px-space-md py-space-sm">Examiner</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-surface-container">
                        <?php foreach($checkups as $c): ?>
                        <tr class="hover:bg-surface-container-low/50 transition-colors">
                            <td class="px-space-md py-space-sm font-semibold text-on-surface whitespace-nowrap">
                                <?= date('M d, Y', strtotime($c['CompletionDate'])) ?>
                            </td>
                            <td class="px-space-md py-space-sm text-xs text-on-surface">
                                <?= htmlspecialchars($c['Remarks'] ?? 'Physical examination completed') ?>
                            </td>
                            <td class="px-space-md py-space-sm">
                                <?php if ($c['Status'] === 'Completed'): ?>
                                    <span class="px-2.5 py-0.5 rounded-full text-xs font-semibold bg-emerald-100 text-emerald-800">Passed (Fit)</span>
                                <?php elseif ($c['Status'] === 'Pending'): ?>
                                    <span class="px-2.5 py-0.5 rounded-full text-xs font-semibold bg-amber-100 text-amber-800">Pending</span>
                                <?php else: ?>
                                    <span class="px-2.5 py-0.5 rounded-full text-xs font-semibold bg-error-container text-error">Failed</span>
                                <?php endif; ?>
                            </td>
                            <td class="px-space-md py-space-sm text-xs text-tertiary">
                                <?= htmlspecialchars($c['StaffLastName'] ? 'Dr. ' . $c['StaffLastName'] : 'University Health Physician') ?>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                        <?php if (empty($checkups)): ?>
                        <tr>
                            <td colspan="4" class="px-space-md py-8 text-center text-tertiary">No medical examination records found on file. Please visit the campus health clinic.</td>
                        </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </section>

    </div>
</main>
<?php include 'includes/footer.php'; ?>