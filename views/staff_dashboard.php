<?php
session_start();
require_once '../config/db.php';

if (!isset($_SESSION['UserType']) || $_SESSION['UserType'] !== 'Staff') {
    header("Location: login.php");
    exit;
}

// Fetch KPIs
$totalEnrolled = $pdo->query("SELECT COUNT(*) FROM enrollment WHERE Status = 'Enrolled'")->fetchColumn();
$pendingAdmissions = $pdo->query("SELECT COUNT(*) FROM admission WHERE Status = 'Pending'")->fetchColumn();
$pendingEvaluations = $pdo->query("SELECT COUNT(*) FROM evaluation WHERE Status = 'Pending'")->fetchColumn();
$pendingSectioning = $pdo->query("SELECT COUNT(*) FROM enrollment WHERE Status = 'Pending'")->fetchColumn();
$pendingPayments = $pdo->query("SELECT COUNT(*) FROM payment WHERE PaymentStatus = 'Pending'")->fetchColumn();

// Fetch Recent Queue (Join student and admission)
$stmt = $pdo->query("
    SELECT s.StudentID, s.StudentNo, s.LastName, s.FirstName, p.ProgramName, a.Status as AdmStatus, a.AdmissionID
    FROM student s
    LEFT JOIN program p ON s.ProgramID = p.ProgramID
    JOIN admission a ON s.StudentID = a.StudentID
    ORDER BY a.SubmissionDate DESC, a.AdmissionID DESC
    LIMIT 10
");
$queue = $stmt->fetchAll(PDO::FETCH_ASSOC);

?>
<?php include 'includes/header.php'; ?>
<?php include 'includes/sidebar.php'; ?>
<div class="pl-72 flex flex-col min-h-screen">
<?php include 'includes/topbar.php'; ?>
<main class="w-full pt-16 flex-1 px-gutter py-space-lg">
    <div class="flex flex-col w-full gap-space-lg">
        
        <!-- Overview Header & KPI Strip -->
        <section class="flex flex-col gap-space-md">
            <div class="flex flex-col md:flex-row md:items-end justify-between gap-space-sm bg-surface-container-lowest p-space-lg rounded-xl shadow-[0_1px_8px_rgba(0,0,0,0.04)] relative overflow-hidden">
                <div class="flex flex-col gap-space-xs z-10">
                    <div class="flex items-center gap-space-xs text-primary font-label-md text-label-md uppercase tracking-wider font-semibold">
                        <i class="fa-solid fa-table-cells-large text-[16px]"></i>
                        <?php
                        $roleTitle = [
                            'Registrar' => 'Registrar Operations Command',
                            'Accounting' => 'Accounting & Finance Command',
                            'Clinic' => 'Campus Health Command',
                            'Admin' => 'System Administrator Command'
                        ][$_SESSION['Role'] ?? ''] ?? 'Registrar Operations Command';
                        ?>
                        <span class=""><?= $roleTitle ?></span>
                    </div>
                    <h1 class="font-headline-lg text-headline-lg text-on-surface tracking-tight">Academic Year 2026–2027</h1>
                    <p class="font-body-md text-body-md text-tertiary">
                        1st Semester Real-time Matriculation Intake & Clearance Pipeline Monitor
                    </p>
                </div>
                <div class="flex items-center gap-space-sm z-10 self-start md:self-auto">
                    <span class="inline-flex items-center gap-1.5 px-space-sm py-1 rounded-full bg-surface-container font-label-md text-label-md text-tertiary">
                        <span class="w-2 h-2 rounded-full bg-secondary-container animate-pulse"></span>
                        Live Sync Active
                    </span>
                    <button class="flex items-center gap-space-xs px-space-md py-space-sm bg-surface-container hover:bg-surface-container-high text-on-surface rounded-lg font-label-lg text-label-lg transition-colors" type="button">
                        <i class="fa-solid fa-file-arrow-down text-[18px]"></i>
                        <span class="">Export Summary</span>
                    </button>
                </div>
            </div>

            <!-- 5 Top Metric Stat Badges -->
            <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-5 gap-space-md">
                <!-- Total Enrolled -->
                <div class="bg-surface-container-lowest p-space-md rounded-xl shadow-[0_1px_8px_rgba(0,0,0,0.04)] flex flex-col justify-between border-l-4 border-l-primary">
                    <div class="flex items-center justify-between text-tertiary">
                        <span class="font-label-md text-label-md uppercase tracking-wide">Total Enrolled</span>
                        <i class="fa-solid fa-graduation-cap text-primary text-[20px]"></i>
                    </div>
                    <div class="mt-space-sm">
                        <span class="font-headline-lg text-headline-lg text-on-surface"><?= number_format($totalEnrolled) ?></span>
                    </div>
                </div>
                
                <!-- Pending Admissions -->
                <div class="bg-surface-container-lowest p-space-md rounded-xl shadow-[0_1px_8px_rgba(0,0,0,0.04)] flex flex-col justify-between border-l-4 border-l-secondary">
                    <div class="flex items-center justify-between text-tertiary">
                        <span class="font-label-md text-label-md uppercase tracking-wide">Pending Admissions</span>
                        <i class="fa-solid fa-user-check text-secondary text-[20px]"></i>
                    </div>
                    <div class="mt-space-sm">
                        <span class="font-headline-lg text-headline-lg text-on-surface"><?= number_format($pendingAdmissions) ?></span>
                    </div>
                </div>

                <!-- Pending Evaluations -->
                <div class="bg-surface-container-lowest p-space-md rounded-xl shadow-[0_1px_8px_rgba(0,0,0,0.04)] flex flex-col justify-between border-l-4 border-l-blue-500">
                    <div class="flex items-center justify-between text-tertiary">
                        <span class="font-label-md text-label-md uppercase tracking-wide">Subject Crediting</span>
                        <i class="fa-solid fa-clipboard-check text-tertiary text-[20px]"></i>
                    </div>
                    <div class="mt-space-sm">
                        <span class="font-headline-lg text-headline-lg text-on-surface"><?= number_format($pendingEvaluations) ?></span>
                    </div>
                </div>

                <!-- Pending Blocking -->
                <div class="bg-surface-container-lowest p-space-md rounded-xl shadow-[0_1px_8px_rgba(0,0,0,0.04)] flex flex-col justify-between border-l-4 border-l-purple-500">
                    <div class="flex items-center justify-between text-tertiary">
                        <span class="font-label-md text-label-md uppercase tracking-wide">Pending Blocking</span>
                        <i class="fa-solid fa-calendar-week text-purple-500 text-[20px]"></i>
                    </div>
                    <div class="mt-space-sm">
                        <span class="font-headline-lg text-headline-lg text-on-surface"><?= number_format($pendingSectioning) ?></span>
                    </div>
                </div>

                <!-- Pending Cashier -->
                <div class="bg-surface-container-lowest p-space-md rounded-xl shadow-[0_1px_8px_rgba(0,0,0,0.04)] flex flex-col justify-between border-l-4 border-l-green-500">
                    <div class="flex items-center justify-between text-tertiary">
                        <span class="font-label-md text-label-md uppercase tracking-wide">Pending Cashier</span>
                        <i class="fa-solid fa-wallet text-green-500 text-[20px]"></i>
                    </div>
                    <div class="mt-space-sm">
                        <span class="font-headline-lg text-headline-lg text-on-surface"><?= number_format($pendingPayments) ?></span>
                    </div>
                </div>
            </div>
        </section>

        <!-- Queue Section -->
        <section class="flex flex-col gap-space-md">
            <div class="bg-surface-container-lowest rounded-xl shadow-[0_1px_8px_rgba(0,0,0,0.04)] overflow-hidden">
                <!-- Queue Header -->
                <div class="p-space-lg flex flex-col gap-space-md border-b border-surface-container">
                    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-space-sm">
                        <div>
                            <h2 class="font-headline-sm text-headline-sm text-on-surface">Recent Enrollment Queue</h2>
                            <p class="font-body-sm text-body-sm text-tertiary">Real-time candidate workflow roster awaiting registrar action</p>
                        </div>
                        <div class="flex items-center gap-space-xs">
                            <a href="admission.php" class="font-label-md text-label-md text-primary bg-primary-fixed/40 hover:bg-primary-fixed/60 transition-colors px-3 py-1 rounded-full font-medium">View All</a>
                        </div>
                    </div>
                </div>

                <!-- Data Table -->
                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse">
                        <thead>
                            <tr class="bg-surface-container-low text-tertiary text-xs uppercase tracking-wider font-label-sm">
                                <th class="px-space-md py-space-sm font-semibold">Student No</th>
                                <th class="px-space-md py-space-sm font-semibold">Name</th>
                                <th class="px-space-md py-space-sm font-semibold">Program</th>
                                <th class="px-space-md py-space-sm font-semibold">Status</th>
                                <th class="px-space-md py-space-sm font-semibold text-right">Action</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y border-surface-container">
                            <?php foreach($queue as $row): ?>
                            <tr class="hover:bg-surface-container-low/50 transition-colors group">
                                <td class="px-space-md py-space-sm font-label-md text-label-md text-primary font-semibold">
                                    <?= htmlspecialchars($row['StudentNo']) ?>
                                </td>
                                <td class="px-space-md py-space-sm">
                                    <span class="font-body-md text-body-md font-medium text-on-surface"><?= htmlspecialchars($row['LastName'] . ', ' . $row['FirstName']) ?></span>
                                </td>
                                <td class="px-space-md py-space-sm">
                                    <span class="font-body-sm text-body-sm text-tertiary"><?= htmlspecialchars($row['ProgramName'] ?? 'Unassigned') ?></span>
                                </td>
                                <td class="px-space-md py-space-sm">
                                    <?php if($row['AdmStatus'] === 'Pending'): ?>
                                        <span class="inline-flex items-center gap-1 bg-surface-container text-tertiary px-2 py-0.5 rounded-md font-label-sm text-label-sm">Pending</span>
                                    <?php elseif($row['AdmStatus'] === 'Approved'): ?>
                                        <span class="inline-flex items-center gap-1 bg-surface-container text-secondary px-2 py-0.5 rounded-md font-label-sm text-label-sm">Approved</span>
                                    <?php else: ?>
                                        <span class="inline-flex items-center gap-1 bg-surface-container text-tertiary px-2 py-0.5 rounded-md font-label-sm text-label-sm"><?= htmlspecialchars($row['AdmStatus']) ?></span>
                                    <?php endif; ?>
                                </td>
                                <td class="px-space-md py-space-sm text-right">
                                    <a href="admission.php?id=<?= $row['AdmissionID'] ?>" class="inline-flex items-center justify-center w-8 h-8 rounded-full text-tertiary hover:bg-surface-container hover:text-primary transition-colors">
                                        <i class="fa-solid fa-chevron-right text-[18px]"></i>
                                    </a>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                            <?php if(empty($queue)): ?>
                            <tr>
                                <td colspan="5" class="px-space-md py-space-lg text-center font-body-md text-body-md text-tertiary">No recent applications found.</td>
                            </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </section>

    </div>
<?php include 'includes/footer.php'; ?>
