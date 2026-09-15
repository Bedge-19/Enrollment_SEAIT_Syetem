<?php
session_start();
require_once '../config/db.php';

if (!isset($_SESSION['UserType']) || $_SESSION['UserType'] !== 'Staff' || !in_array($_SESSION['Role'] ?? '', ['Registrar', 'Admin'])) {
    header("Location: login.php?error=" . urlencode("Access denied. Registrar privileges required."));
    exit;
}

// Handle Final Approval POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $csrf = $_POST['csrf_token'] ?? '';
    if (!isset($_SESSION['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $csrf)) {
        header("Location: registrar_enrollment_approvals.php?error=" . urlencode("Invalid security token."));
        exit;
    }

    $enrollmentId = (int)($_POST['enrollment_id'] ?? 0);
    $action = $_POST['action'] ?? '';
    $staffId = $_SESSION['StaffID'] ?? 1;

    if ($action === 'approve_enrollment' && $enrollmentId) {
        $stmt = $pdo->prepare("UPDATE enrollment SET Status = 'Enrolled', StaffID = ? WHERE EnrollmentID = ?");
        $stmt->execute([$staffId, $enrollmentId]);

        require_once '../config/logger.php';
        log_activity($pdo, 'Final Enrollment Approval', 'Enrollment Approvals', "Approved official enrollment for EnrollmentID: $enrollmentId");

        header("Location: registrar_enrollment_approvals.php?msg=" . urlencode("Student enrollment officially approved! Certificate of Registration (COR) released."));
        exit;
    } elseif ($action === 'cancel_enrollment' && $enrollmentId) {
        $stmt = $pdo->prepare("UPDATE enrollment SET Status = 'Cancelled', StaffID = ? WHERE EnrollmentID = ?");
        $stmt->execute([$staffId, $enrollmentId]);
        header("Location: registrar_enrollment_approvals.php?msg=" . urlencode("Enrollment marked as cancelled."));
        exit;
    }
}

// Fetch candidates with all approval gates
$stmt = $pdo->query("
    SELECT e.EnrollmentID, e.EnrollmentDate, e.Status as EnrollmentStatus, e.SchoolYear, e.Semester,
           s.StudentID, s.StudentNo, s.LastName, s.FirstName,
           p.ProgramName,
           adm.Status as AdmStatus,
           idv.Status as IDValStatus,
           ev.Status as EvalStatus,
           blk.BlockingID, sec.SectionCode,
           cln.Status as ClinicStatus,
           clr.Status as ClearanceStatus,
           pay.PaymentStatus, pay.ReceiptNo
    FROM enrollment e
    JOIN student s ON e.StudentID = s.StudentID
    LEFT JOIN program p ON s.ProgramID = p.ProgramID
    LEFT JOIN admission adm ON s.StudentID = adm.StudentID
    LEFT JOIN id_validation idv ON s.StudentID = idv.StudentID
    LEFT JOIN evaluation ev ON e.EvaluationID = ev.EvaluationID
    LEFT JOIN blocking blk ON e.EnrollmentID = blk.EnrollmentID
    LEFT JOIN section sec ON blk.SectionID = sec.SectionID
    LEFT JOIN clinic cln ON s.StudentID = cln.StudentID
    LEFT JOIN clearance clr ON s.StudentID = clr.StudentID
    LEFT JOIN payment pay ON e.PaymentID = pay.PaymentID
    ORDER BY e.EnrollmentID DESC
    LIMIT 50
");
$candidates = $stmt->fetchAll();
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
                        <i class="fa-solid fa-check-double text-[18px]"></i>
                        <span>Registrar Module</span>
                    </div>
                    <h1 class="font-headline-lg text-headline-lg text-on-surface tracking-tight">Enrollment Approvals Gate</h1>
                    <p class="font-body-md text-body-md text-tertiary">
                        Final authorization board verifying Admissions, ID Validation, Subject Evaluations, Section Blocking, Medicals, and Cashier settlements.
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

        <!-- Approval Board Table -->
        <section class="bg-surface-container-lowest rounded-xl shadow-[0_1px_8px_rgba(0,0,0,0.04)] border border-outline-variant/30 overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse text-sm">
                    <thead>
                        <tr class="bg-surface-container-low text-tertiary text-xs uppercase font-label-sm border-b border-surface-container">
                            <th class="px-space-md py-space-sm">Student</th>
                            <th class="px-space-md py-space-sm">Program & Section</th>
                            <th class="px-space-md py-space-sm text-center">Admission</th>
                            <th class="px-space-md py-space-sm text-center">ID Val</th>
                            <th class="px-space-md py-space-sm text-center">Eval</th>
                            <th class="px-space-md py-space-sm text-center">Section</th>
                            <th class="px-space-md py-space-sm text-center">Medical</th>
                            <th class="px-space-md py-space-sm text-center">Payment</th>
                            <th class="px-space-md py-space-sm text-right">Final Decision</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-surface-container">
                        <?php foreach($candidates as $c): 
                            $admOk = $c['AdmStatus'] === 'Approved';
                            $idOk = $c['IDValStatus'] === 'Completed';
                            $evalOk = $c['EvalStatus'] === 'Approved';
                            $blkOk = !empty($c['BlockingID']);
                            $medOk = $c['ClinicStatus'] === 'Completed' || $c['ClearanceStatus'] === 'Cleared';
                            $payOk = $c['PaymentStatus'] === 'Paid';
                            $allPassed = $admOk && $idOk && $evalOk && $blkOk && $medOk && $payOk;
                        ?>
                        <tr class="hover:bg-surface-container-low/50 transition-colors">
                            <td class="px-space-md py-space-sm">
                                <span class="font-semibold text-on-surface"><?= htmlspecialchars($c['LastName'] . ', ' . $c['FirstName']) ?></span>
                                <span class="block text-xs font-mono text-primary"><?= htmlspecialchars($c['StudentNo']) ?></span>
                            </td>
                            <td class="px-space-md py-space-sm text-xs text-tertiary">
                                <div><?= htmlspecialchars($c['ProgramName'] ?? 'Unassigned') ?></div>
                                <span class="font-bold text-on-surface"><?= htmlspecialchars($c['SectionCode'] ?? 'Unblocked') ?></span>
                            </td>
                            
                            <!-- Gate 1: Admission -->
                            <td class="px-space-md py-space-sm text-center">
                                <i class="fa-solid <?= $admOk ? 'fa-circle-check text-emerald-600' : 'fa-clock text-amber-500' ?> text-[16px]"></i>
                            </td>

                            <!-- Gate 2: ID Validation -->
                            <td class="px-space-md py-space-sm text-center">
                                <i class="fa-solid <?= $idOk ? 'fa-circle-check text-emerald-600' : 'fa-clock text-amber-500' ?> text-[16px]"></i>
                            </td>

                            <!-- Gate 3: Subject Evaluation -->
                            <td class="px-space-md py-space-sm text-center">
                                <i class="fa-solid <?= $evalOk ? 'fa-circle-check text-emerald-600' : 'fa-clock text-amber-500' ?> text-[16px]"></i>
                            </td>

                            <!-- Gate 4: Section Blocking -->
                            <td class="px-space-md py-space-sm text-center">
                                <i class="fa-solid <?= $blkOk ? 'fa-circle-check text-emerald-600' : 'fa-clock text-amber-500' ?> text-[16px]"></i>
                            </td>

                            <!-- Gate 5: Clinic / Clearance -->
                            <td class="px-space-md py-space-sm text-center">
                                <i class="fa-solid <?= $medOk ? 'fa-circle-check text-emerald-600' : 'fa-clock text-amber-500' ?> text-[16px]"></i>
                            </td>

                            <!-- Gate 6: Payment -->
                            <td class="px-space-md py-space-sm text-center">
                                <i class="fa-solid <?= $payOk ? 'fa-circle-check text-emerald-600' : 'fa-clock text-amber-500' ?> text-[16px]"></i>
                            </td>

                            <!-- Decision Action -->
                            <td class="px-space-md py-space-sm text-right">
                                <?php if ($c['EnrollmentStatus'] === 'Enrolled'): ?>
                                    <span class="inline-flex items-center gap-1 text-xs font-bold text-emerald-700 bg-emerald-50 px-2.5 py-1 rounded-full border border-emerald-200">
                                        <i class="fa-solid fa-circle-check text-[16px]"></i> Officially Enrolled
                                    </span>
                                <?php else: ?>
                                    <form method="POST" action="registrar_enrollment_approvals.php" class="inline">
                                        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token'] ?? '') ?>">
                                        <input type="hidden" name="action" value="approve_enrollment">
                                        <input type="hidden" name="enrollment_id" value="<?= $c['EnrollmentID'] ?>">
                                        <button type="submit" class="px-3.5 py-1.5 <?= $allPassed ? 'bg-primary hover:bg-primary-container text-on-primary' : 'bg-surface-container text-on-surface hover:bg-surface-container-high' ?> text-xs font-bold rounded-lg transition-colors shadow-sm inline-flex items-center gap-1">
                                            <i class="fa-solid fa-user-check text-[16px]"></i>
                                            <span><?= $allPassed ? 'Finalize Enrollment' : 'Force Approve' ?></span>
                                        </button>
                                    </form>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                        <?php if (empty($candidates)): ?>
                        <tr>
                            <td colspan="9" class="px-space-md py-8 text-center text-tertiary">No enrollment applications pending review.</td>
                        </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </section>

    </div>
</main>
<?php include 'includes/footer.php'; ?>