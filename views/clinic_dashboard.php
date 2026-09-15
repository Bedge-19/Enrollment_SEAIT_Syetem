<?php
session_start();
require_once '../config/db.php';

if (!isset($_SESSION['UserType']) || $_SESSION['UserType'] !== 'Staff' || !in_array($_SESSION['Role'] ?? '', ['Clinic', 'Admin'])) {
    header("Location: login.php?error=" . urlencode("Access denied. Clinic staff privileges required."));
    exit;
}

// 1. Core KPIs
$pendingClearances = (int)$pdo->query("SELECT COUNT(*) FROM clearance WHERE Status = 'Pending'")->fetchColumn();
$clearedTotal = (int)$pdo->query("SELECT COUNT(*) FROM clearance WHERE Status = 'Cleared'")->fetchColumn();
$completedConsultations = (int)$pdo->query("SELECT COUNT(*) FROM clinic WHERE Status = 'Completed'")->fetchColumn();
$totalPatients = (int)$pdo->query("SELECT COUNT(DISTINCT StudentID) FROM student")->fetchColumn();

// 2. Priority Health Clearance Queue
$stmt = $pdo->query("
    SELECT c.ClearanceID, s.StudentID, s.StudentNo, s.LastName, s.FirstName, p.ProgramName, c.Status as ClearanceStatus,
           cl.Status as ClinicStatus, cl.Remarks as MedicalRemarks
    FROM clearance c 
    JOIN student s ON c.StudentID = s.StudentID 
    LEFT JOIN program p ON s.ProgramID = p.ProgramID
    LEFT JOIN clinic cl ON s.StudentID = cl.StudentID
    WHERE c.Status = 'Pending' 
    ORDER BY c.ClearanceDate ASC, c.ClearanceID ASC 
    LIMIT 10
");
$queue = $stmt->fetchAll();

// 3. Recent Consultations
$stmtRecent = $pdo->query("
    SELECT cl.*, s.StudentNo, s.LastName, s.FirstName, p.ProgramName
    FROM clinic cl
    JOIN student s ON cl.StudentID = s.StudentID
    LEFT JOIN program p ON s.ProgramID = p.ProgramID
    ORDER BY cl.ClinicID DESC
    LIMIT 5
");
$recentConsultations = $stmtRecent->fetchAll();
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
                        <i class="fa-solid fa-heart-pulse text-[18px]"></i>
                        <span>Campus Health Operations</span>
                    </div>
                    <h1 class="font-headline-lg text-headline-lg text-on-surface tracking-tight">Clinic Command Dashboard</h1>
                    <p class="font-body-md text-body-md text-tertiary">
                        Real-time student physical examination intake, vital statistics records, and health clearances.
                    </p>
                </div>
                <div class="flex items-center gap-space-sm">
                    <a href="clinic_consultations.php" class="flex items-center gap-space-xs px-4 py-2 bg-primary hover:bg-primary-container text-on-primary rounded-lg font-label-md text-label-md font-semibold transition-colors shadow-sm">
                        <i class="fa-solid fa-briefcase-medical text-[18px]"></i>
                        <span>New Medical Checkup</span>
                    </a>
                </div>
            </div>

            <?php if (!empty($_GET['msg'])): ?>
            <div class="p-3 bg-emerald-50 border border-emerald-200 text-emerald-800 rounded-lg text-sm flex items-center gap-2">
                <i class="fa-solid fa-circle-check text-emerald-600 text-[20px]"></i>
                <span><?= htmlspecialchars($_GET['msg']) ?></span>
            </div>
            <?php endif; ?>

            <!-- Metric Badges -->
            <div class="grid grid-cols-2 md:grid-cols-4 gap-space-md">
                <div class="bg-surface-container-lowest p-space-md rounded-xl shadow-[0_1px_8px_rgba(0,0,0,0.04)] border-l-4 border-l-amber-500 flex flex-col justify-between">
                    <div class="flex items-center justify-between text-tertiary">
                        <span class="font-label-sm text-label-sm uppercase tracking-wide">Pending Clearances</span>
                        <i class="fa-solid fa-clock text-amber-500 text-[20px]"></i>
                    </div>
                    <div class="mt-space-sm">
                        <span class="font-headline-md text-headline-md text-on-surface"><?= number_format($pendingClearances) ?></span>
                    </div>
                </div>

                <div class="bg-surface-container-lowest p-space-md rounded-xl shadow-[0_1px_8px_rgba(0,0,0,0.04)] border-l-4 border-l-emerald-500 flex flex-col justify-between">
                    <div class="flex items-center justify-between text-tertiary">
                        <span class="font-label-sm text-label-sm uppercase tracking-wide">Total Cleared</span>
                        <i class="fa-solid fa-circle-check text-emerald-500 text-[20px]"></i>
                    </div>
                    <div class="mt-space-sm">
                        <span class="font-headline-md text-headline-md text-on-surface"><?= number_format($clearedTotal) ?></span>
                    </div>
                </div>

                <div class="bg-surface-container-lowest p-space-md rounded-xl shadow-[0_1px_8px_rgba(0,0,0,0.04)] border-l-4 border-l-primary flex flex-col justify-between">
                    <div class="flex items-center justify-between text-tertiary">
                        <span class="font-label-sm text-label-sm uppercase tracking-wide">Completed Checkups</span>
                        <i class="fa-solid fa-stethoscope text-primary text-[20px]"></i>
                    </div>
                    <div class="mt-space-sm">
                        <span class="font-headline-md text-headline-md text-on-surface"><?= number_format($completedConsultations) ?></span>
                    </div>
                </div>

                <div class="bg-surface-container-lowest p-space-md rounded-xl shadow-[0_1px_8px_rgba(0,0,0,0.04)] border-l-4 border-l-blue-500 flex flex-col justify-between">
                    <div class="flex items-center justify-between text-tertiary">
                        <span class="font-label-sm text-label-sm uppercase tracking-wide">Total Patient Roster</span>
                        <i class="fa-solid fa-bed-pulse text-blue-500 text-[20px]"></i>
                    </div>
                    <div class="mt-space-sm">
                        <span class="font-headline-md text-headline-md text-on-surface"><?= number_format($totalPatients) ?></span>
                    </div>
                </div>
            </div>
        </section>

        <!-- Queue Section -->
        <section class="bg-surface-container-lowest rounded-xl shadow-[0_1px_8px_rgba(0,0,0,0.04)] border border-outline-variant/30 overflow-hidden">
            <div class="p-space-lg flex flex-col sm:flex-row sm:items-center justify-between gap-space-sm border-b border-surface-container">
                <div>
                    <h2 class="font-headline-sm text-headline-sm text-on-surface">Immediate Health Clearance Queue</h2>
                    <p class="font-body-sm text-body-sm text-tertiary">Students awaiting medical verification before matriculation finalization</p>
                </div>
                <a href="clearance.php" class="px-3.5 py-1.5 bg-primary/10 text-primary font-semibold text-xs rounded-full hover:bg-primary/20 transition-colors">
                    Open Full Queue &rarr;
                </a>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse text-sm">
                    <thead>
                        <tr class="bg-surface-container-low text-tertiary text-xs uppercase font-label-sm border-b border-surface-container">
                            <th class="px-space-md py-space-sm font-semibold">Student No</th>
                            <th class="px-space-md py-space-sm font-semibold">Candidate Name</th>
                            <th class="px-space-md py-space-sm font-semibold">Program</th>
                            <th class="px-space-md py-space-sm font-semibold">Medical Notes</th>
                            <th class="px-space-md py-space-sm font-semibold text-right">Quick Action</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-surface-container">
                        <?php foreach($queue as $row): ?>
                        <tr class="hover:bg-surface-container-low/50 transition-colors">
                            <td class="px-space-md py-space-sm font-semibold text-primary">
                                <?= htmlspecialchars($row['StudentNo']) ?>
                            </td>
                            <td class="px-space-md py-space-sm font-medium text-on-surface">
                                <?= htmlspecialchars($row['LastName'] . ', ' . $row['FirstName']) ?>
                            </td>
                            <td class="px-space-md py-space-sm text-xs text-tertiary">
                                <?= htmlspecialchars($row['ProgramName'] ?? 'Unassigned') ?>
                            </td>
                            <td class="px-space-md py-space-sm text-xs text-tertiary max-w-xs truncate">
                                <?= htmlspecialchars($row['MedicalRemarks'] ?? 'No vitals logged yet') ?>
                            </td>
                            <td class="px-space-md py-space-sm text-right">
                                <form method="POST" action="../controllers/clinic_action_controller.php" class="inline flex items-center justify-end gap-1.5">
                                    <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token'] ?? '') ?>">
                                    <input type="hidden" name="action" value="process_clearance">
                                    <input type="hidden" name="student_id" value="<?= $row['StudentID'] ?>">
                                    <input type="hidden" name="clearance_status" value="Cleared">
                                    <input type="hidden" name="remarks" value="Cleared by Clinic Staff">
                                    <button type="submit" class="px-3 py-1 bg-emerald-600 hover:bg-emerald-700 text-white font-semibold text-xs rounded shadow-sm">
                                        Clear Student
                                    </button>
                                    <a href="clinic_consultations.php?student_id=<?= $row['StudentID'] ?>" class="px-2.5 py-1 bg-surface-container hover:bg-surface-container-high text-xs rounded text-on-surface">
                                        Examine
                                    </a>
                                </form>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                        <?php if(empty($queue)): ?>
                        <tr>
                            <td colspan="5" class="px-space-md py-8 text-center text-tertiary">No pending clearances at this time. All students cleared!</td>
                        </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </section>

    </div>
</main>
<?php include 'includes/footer.php'; ?>