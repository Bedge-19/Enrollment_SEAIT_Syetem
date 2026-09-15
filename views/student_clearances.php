<?php
session_start();
require_once '../config/db.php';

if (!isset($_SESSION['UserType']) || $_SESSION['UserType'] !== 'Student') {
    header("Location: login.php?error=" . urlencode("Access denied. Student portal privileges required."));
    exit;
}

$studentId = $_SESSION['StudentID'] ?? null;
if (!$studentId) die("Session expired. Please re-login.");

$stmt = $pdo->prepare("SELECT * FROM clearance WHERE StudentID = ? ORDER BY ClearanceID DESC LIMIT 1");
$stmt->execute([$studentId]);
$clearance = $stmt->fetch();

$stmtClinic = $pdo->prepare("SELECT Status, Remarks FROM clinic WHERE StudentID = ? ORDER BY ClinicID DESC LIMIT 1");
$stmtClinic->execute([$studentId]);
$clinic = $stmtClinic->fetch();

$isGeneralCleared = ($clearance && $clearance['Status'] === 'Cleared');
$isClinicCleared = ($clinic && $clinic['Status'] === 'Completed');
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
                        <i class="fa-solid fa-list-check text-[18px]"></i>
                        <span>Institutional Accountability</span>
                    </div>
                    <h1 class="font-headline-lg text-headline-lg text-on-surface tracking-tight">Clearances Overview</h1>
                    <p class="font-body-md text-body-md text-tertiary">
                        Track institutional and department clearance gates required for matriculation validation.
                    </p>
                </div>
            </div>
        </section>

        <!-- Main Clearance Status -->
        <section class="bg-surface-container-lowest p-space-lg rounded-xl shadow-[0_1px_8px_rgba(0,0,0,0.04)] border border-outline-variant/30 flex flex-col md:flex-row md:items-center justify-between gap-4">
            <div class="flex items-center gap-4">
                <div class="w-14 h-14 rounded-2xl <?= $isGeneralCleared ? 'bg-emerald-100 text-emerald-700' : 'bg-amber-100 text-amber-700' ?> flex items-center justify-center font-bold">
                    <i class="fa-solid <?= $isGeneralCleared ? 'fa-circle-check' : 'fa-hourglass-half' ?> text-[28px]"></i>
                </div>
                <div>
                    <span class="text-xs font-semibold text-tertiary uppercase">Overall Clearance Status</span>
                    <h2 class="text-2xl font-black text-on-surface">
                        <?= $isGeneralCleared ? 'Cleared for Enrollment' : 'Action Required' ?>
                    </h2>
                    <p class="text-xs text-tertiary mt-0.5">
                        Clearance Remarks: <?= htmlspecialchars($clearance['Remarks'] ?? 'Awaiting clearance sign-offs.') ?>
                    </p>
                </div>
            </div>
        </section>

        <!-- Department Clearance Gates Grid -->
        <section class="grid grid-cols-1 md:grid-cols-3 gap-space-md">
            
            <!-- Gate 1: Campus Health Clinic -->
            <div class="bg-surface-container-lowest p-space-lg rounded-xl shadow-[0_1px_8px_rgba(0,0,0,0.04)] border border-outline-variant/30 flex flex-col justify-between">
                <div>
                    <div class="flex items-center justify-between">
                        <i class="fa-solid fa-heart-pulse text-primary text-[24px]"></i>
                        <span class="px-2.5 py-0.5 rounded-full text-xs font-semibold <?= $isClinicCleared ? 'bg-emerald-100 text-emerald-800' : 'bg-amber-100 text-amber-800' ?>">
                            <?= $isClinicCleared ? 'Cleared' : 'Pending' ?>
                        </span>
                    </div>
                    <h3 class="font-title-md text-title-md text-on-surface mt-3">Campus Health Clinic</h3>
                    <p class="text-xs text-tertiary mt-1">Annual physical examination, chest x-ray verification, and dental checkup.</p>
                </div>
                <div class="mt-4 pt-3 border-t border-surface-container text-xs text-tertiary">
                    <?= htmlspecialchars($clinic['Remarks'] ?? 'Visit clinic terminal for examination') ?>
                </div>
            </div>

            <!-- Gate 2: University Library -->
            <div class="bg-surface-container-lowest p-space-lg rounded-xl shadow-[0_1px_8px_rgba(0,0,0,0.04)] border border-outline-variant/30 flex flex-col justify-between">
                <div>
                    <div class="flex items-center justify-between">
                        <i class="fa-solid fa-book-atlas text-secondary text-[24px]"></i>
                        <span class="px-2.5 py-0.5 rounded-full text-xs font-semibold bg-emerald-100 text-emerald-800">
                            Cleared
                        </span>
                    </div>
                    <h3 class="font-title-md text-title-md text-on-surface mt-3">University Library</h3>
                    <p class="text-xs text-tertiary mt-1">Book returns, circulation fines, and electronic resource access card.</p>
                </div>
                <div class="mt-4 pt-3 border-t border-surface-container text-xs text-tertiary">
                    No outstanding library books or fines on record.
                </div>
            </div>

            <!-- Gate 3: Collegiate Department Clearance -->
            <div class="bg-surface-container-lowest p-space-lg rounded-xl shadow-[0_1px_8px_rgba(0,0,0,0.04)] border border-outline-variant/30 flex flex-col justify-between">
                <div>
                    <div class="flex items-center justify-between">
                        <i class="fa-solid fa-graduation-cap text-blue-500 text-[24px]"></i>
                        <span class="px-2.5 py-0.5 rounded-full text-xs font-semibold <?= $isGeneralCleared ? 'bg-emerald-100 text-emerald-800' : 'bg-amber-100 text-amber-800' ?>">
                            <?= $isGeneralCleared ? 'Cleared' : 'Pending' ?>
                        </span>
                    </div>
                    <h3 class="font-title-md text-title-md text-on-surface mt-3">Collegiate Department</h3>
                    <p class="text-xs text-tertiary mt-1">Academic advising, program prerequisite validation, and laboratory clearance.</p>
                </div>
                <div class="mt-4 pt-3 border-t border-surface-container text-xs text-tertiary">
                    Verified by Collegiate Department Head / Program Chair.
                </div>
            </div>

        </section>

    </div>
</main>
<?php include 'includes/footer.php'; ?>