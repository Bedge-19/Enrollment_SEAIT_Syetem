<?php
session_start();
require_once '../config/db.php';

if (!isset($_SESSION['UserType']) || $_SESSION['UserType'] !== 'Student') {
    header("Location: login.php");
    exit;
}

$studentId = $_SESSION['StudentID'] ?? null;
if (!$studentId) {
    die("Student ID not found in session.");
}

// Fetch student details
$stmt = $pdo->prepare("SELECT * FROM student WHERE StudentID = ?");
$stmt->execute([$studentId]);
$student = $stmt->fetch();

// Fetch statuses for the tracker
$admission = $pdo->prepare("SELECT Status FROM admission WHERE StudentID = ? ORDER BY AdmissionID DESC LIMIT 1");
$admission->execute([$studentId]);
$admStatus = $admission->fetchColumn() ?: 'Not Started';

$clinic = $pdo->prepare("SELECT Status FROM clinic WHERE StudentID = ? ORDER BY ClinicID DESC LIMIT 1");
$clinic->execute([$studentId]);
$clinicStatus = $clinic->fetchColumn() ?: 'Not Started';

$clearance = $pdo->prepare("SELECT Status FROM clearance WHERE StudentID = ? ORDER BY ClearanceID DESC LIMIT 1");
$clearance->execute([$studentId]);
$clearanceStatus = $clearance->fetchColumn() ?: 'Not Started';

$eval = $pdo->prepare("SELECT Status FROM evaluation WHERE StudentID = ? ORDER BY EvaluationID DESC LIMIT 1");
$eval->execute([$studentId]);
$evalStatus = $eval->fetchColumn() ?: 'Not Started';

$enrollment = $pdo->prepare("SELECT Status FROM enrollment WHERE StudentID = ? ORDER BY EnrollmentID DESC LIMIT 1");
$enrollment->execute([$studentId]);
$enrollStatus = $enrollment->fetchColumn() ?: 'Not Started';

$payment = $pdo->prepare("SELECT PaymentStatus FROM payment WHERE StudentID = ? ORDER BY PaymentID DESC LIMIT 1");
$payment->execute([$studentId]);
$payStatus = $payment->fetchColumn() ?: 'Not Started';

function getStatusBadge($status) {
    $map = [
        'Not Started' => ['bg-surface-container-high text-tertiary', 'fa-clock', 'Pending Action'],
        'Pending' => ['bg-secondary-container/50 text-secondary', 'fa-hourglass-half', 'In Review'],
        'Approved' => ['bg-green-100 text-green-700', 'fa-circle-check', 'Completed'],
        'Cleared' => ['bg-green-100 text-green-700', 'fa-circle-check', 'Completed'],
        'Completed' => ['bg-green-100 text-green-700', 'fa-circle-check', 'Completed'],
        'Enrolled' => ['bg-primary-container text-primary', 'fa-certificate', 'Enrolled'],
        'Paid' => ['bg-green-100 text-green-700', 'fa-money-bill-wave', 'Paid'],
        'Rejected' => ['bg-error-container/50 text-error', 'fa-circle-xmark', 'Rejected'],
        'Failed' => ['bg-error-container/50 text-error', 'fa-circle-exclamation', 'Failed'],
        'Not Cleared' => ['bg-error-container/50 text-error', 'fa-triangle-exclamation', 'Action Required']
    ];
    $data = $map[$status] ?? $map['Not Started'];
    return "<span class='inline-flex items-center gap-1.5 px-2.5 py-1 rounded-md text-xs font-medium {$data[0]}'><i class='fa-solid {$data[1]} text-[12px]'></i> {$data[2]}</span>";
}

$steps = [
    [
        'title' => '1. Admissions Application',
        'desc' => 'Submit your basic profile and admission requirements to the Registrar.',
        'status' => $admStatus,
        'link' => 'student_admission.php',
        'icon' => 'fa-clipboard-list'
    ],
    [
        'title' => '2. Clinic / Medical Status',
        'desc' => 'Visit the campus clinic for medical checkup and clearance.',
        'status' => $clinicStatus === 'Completed' ? 'Completed' : ($clinicStatus === 'Failed' ? 'Failed' : ($clinicStatus === 'Pending' ? 'Pending' : 'Not Started')),
        'link' => 'student_clinic.php',
        'icon' => 'fa-notes-medical'
    ],
    [
        'title' => '3. Health & General Clearances',
        'desc' => 'Ensure all previous accountabilities and health clearances are cleared.',
        'status' => $clearanceStatus,
        'link' => 'student_clearances.php',
        'icon' => 'fa-list-check'
    ],
    [
        'title' => '4. Curriculum Evaluation',
        'desc' => 'Registrar evaluates your subjects and previous credits.',
        'status' => $evalStatus,
        'link' => 'student_evaluation.php',
        'icon' => 'fa-clipboard-check'
    ],
    [
        'title' => '5. Section & Class Schedule',
        'desc' => 'Registrar plots and assigns your block section and schedule.',
        'status' => $enrollStatus === 'Enrolled' ? 'Completed' : ($enrollStatus === 'Pending' ? 'Pending' : 'Not Started'),
        'link' => 'student_schedule.php',
        'icon' => 'fa-calendar-days'
    ],
    [
        'title' => '6. Billing & Payments',
        'desc' => 'Proceed to Accounting to settle your enrollment fees.',
        'status' => $payStatus,
        'link' => 'student_billing.php',
        'icon' => 'fa-receipt'
    ],
    [
        'title' => '7. ID Validation Card',
        'desc' => 'Claim or validate your official student ID card.',
        'status' => 'Not Started', // Simplified
        'link' => 'student_id.php',
        'icon' => 'fa-id-card'
    ]
];
?>
<?php include 'includes/header.php'; ?>
<?php include 'includes/sidebar.php'; ?>
<div class="pl-72 flex flex-col min-h-screen">
<?php include 'includes/topbar.php'; ?>
<main class="w-full pt-16 flex-1 px-gutter py-space-lg">
    <div class="flex flex-col w-full gap-space-lg">
        
        <section class="flex flex-col gap-space-md">
            <div class="flex flex-col md:flex-row md:items-end justify-between gap-space-sm bg-surface-container-lowest p-space-lg rounded-xl shadow-[0_1px_8px_rgba(0,0,0,0.04)] relative overflow-hidden">
                <div class="flex flex-col gap-space-xs z-10">
                    <div class="flex items-center gap-space-xs text-primary font-label-md text-label-md uppercase tracking-wider font-semibold">
                        <i class="fa-solid fa-graduation-cap text-[16px]"></i>
                        <span>Student Portal</span>
                    </div>
                    <h1 class="font-headline-lg text-headline-lg text-on-surface tracking-tight">Welcome, <?= htmlspecialchars($student['FirstName'] ?? 'Student') ?>!</h1>
                    <p class="font-body-md text-body-md text-tertiary">
                        Track your real-time enrollment progress and complete the required steps below.
                    </p>
                </div>
            </div>
        </section>

        <section class="flex flex-col gap-space-md">
            <div class="bg-surface-container-lowest rounded-xl shadow-[0_1px_8px_rgba(0,0,0,0.04)] overflow-hidden">
                <div class="p-space-lg border-b border-surface-container flex justify-between items-center">
                    <div>
                        <h2 class="font-headline-sm text-headline-sm text-on-surface">Step-by-Step Enrollment Guide</h2>
                        <p class="font-body-sm text-body-sm text-tertiary">Complete all stages sequentially to finalize your matriculation.</p>
                    </div>
                </div>
                <div class="p-space-lg">
                    <div class="relative flex flex-col gap-8">
                        <div class="absolute left-6 top-6 bottom-6 w-0.5 bg-surface-container z-0"></div>
                        
                        <?php foreach($steps as $i => $step): 
                            $isComplete = in_array($step['status'], ['Approved', 'Cleared', 'Completed', 'Enrolled', 'Paid']);
                            $isActive = $step['status'] === 'Pending';
                            
                            $iconColor = $isComplete ? 'bg-primary text-on-primary' : ($isActive ? 'bg-secondary text-on-secondary' : 'bg-surface-container-high text-tertiary');
                            $borderColor = $isActive ? 'border-secondary bg-secondary/5' : 'border-surface-container bg-surface-container-lowest';
                        ?>
                        <div class="relative z-10 flex gap-space-md items-start group">
                            <div class="w-12 h-12 rounded-full <?= $iconColor ?> shadow-sm flex items-center justify-center shrink-0 border-4 border-surface-container-lowest transition-transform group-hover:scale-105">
                                <i class="fa-solid <?= $step['icon'] ?> text-lg"></i>
                            </div>
                            <div class="flex-1 border <?= $borderColor ?> rounded-xl p-space-md shadow-sm flex flex-col md:flex-row md:items-center justify-between gap-space-sm transition-colors hover:border-primary/30">
                                <div>
                                    <h3 class="font-title-md text-title-md text-on-surface"><?= $step['title'] ?></h3>
                                    <p class="font-body-sm text-body-sm text-tertiary mt-1"><?= $step['desc'] ?></p>
                                </div>
                                <div class="flex items-center gap-space-sm">
                                    <?= getStatusBadge($step['status']) ?>
                                    <a href="<?= $step['link'] ?>" class="px-3 py-1.5 text-xs font-semibold rounded-lg bg-primary/10 text-primary hover:bg-primary hover:text-white transition-all flex items-center gap-1 shadow-xs">Open Module</a>
                                </div>
                            </div>
                        </div>
                        <?php endforeach; ?>
                        
                    </div>
                </div>
            </div>
        </section>

    </div>
</main>
<?php include 'includes/footer.php'; ?>
