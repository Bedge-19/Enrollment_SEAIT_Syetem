<?php
session_start();
require_once '../config/db.php';

if (!isset($_SESSION['UserType']) || $_SESSION['UserType'] !== 'Student') {
    header("Location: login.php");
    exit;
}

$studentId = $_SESSION['StudentID'] ?? 0;

// Fetch Student Profile
$stmt = $pdo->prepare("
    SELECT s.*, p.ProgramName, d.DepartmentName, st.TypeName
    FROM student s
    LEFT JOIN program p ON s.ProgramID = p.ProgramID
    LEFT JOIN department d ON p.DepartmentID = d.DepartmentID
    LEFT JOIN student_type st ON s.StudentTypeID = st.StudentTypeID
    WHERE s.StudentID = ?
");
$stmt->execute([$studentId]);
$student = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$student) {
    header("Location: login.php?error=" . urlencode("Student record not found."));
    exit;
}

// Fetch Latest Enrollment
$enrStmt = $pdo->prepare("
    SELECT e.*, sec.SectionID, sec.SectionCode, sec.YearLevel as SecYearLevel,
           pay.PaymentID, pay.PaymentStatus, pay.ReceiptNo, pay.Amount as AmountPaid, pay.PaymentDate,
           ev.EvaluationID, ev.Status as EvalStatus,
           adm.Status as AdmStatus,
           cln.Status as ClinicStatus,
           clr.Status as ClearanceStatus
    FROM enrollment e
    LEFT JOIN blocking blk ON e.EnrollmentID = blk.EnrollmentID
    LEFT JOIN section sec ON blk.SectionID = sec.SectionID
    LEFT JOIN payment pay ON e.PaymentID = pay.PaymentID
    LEFT JOIN evaluation ev ON e.EvaluationID = ev.EvaluationID
    LEFT JOIN admission adm ON e.StudentID = adm.StudentID
    LEFT JOIN clinic cln ON e.StudentID = cln.StudentID
    LEFT JOIN clearance clr ON e.StudentID = clr.StudentID
    WHERE e.StudentID = ?
    ORDER BY e.EnrollmentID DESC
    LIMIT 1
");
$enrStmt->execute([$studentId]);
$enrollment = $enrStmt->fetch(PDO::FETCH_ASSOC);

// Fetch Enrolled Subjects
$enrolledSubjects = [];
if ($enrollment) {
    $subStmt = $pdo->prepare("
        SELECT es.*, sub.SubjectCode, sub.SubjectTitle, sub.Units, sec.SectionCode
        FROM enrollment_subject es
        JOIN subject sub ON es.SubjectID = sub.SubjectID
        LEFT JOIN section sec ON es.SectionID = sec.SectionID
        WHERE es.EnrollmentID = ?
        ORDER BY sub.SubjectCode ASC
    ");
    $subStmt->execute([$enrollment['EnrollmentID']]);
    $enrolledSubjects = $subStmt->fetchAll(PDO::FETCH_ASSOC);

    // If empty in enrollment_subject, fallback to evaluation_subject
    if (empty($enrolledSubjects) && !empty($enrollment['EvaluationID'])) {
        $evalSubStmt = $pdo->prepare("
            SELECT es.*, sub.SubjectCode, sub.SubjectTitle, sub.Units, ? as SectionCode
            FROM evaluation_subject es
            JOIN subject sub ON es.SubjectID = sub.SubjectID
            WHERE es.EvaluationID = ? AND es.SubjectStatus = 'Approved'
            ORDER BY sub.SubjectCode ASC
        ");
        $evalSubStmt->execute([$enrollment['SectionCode'] ?? 'TBA', $enrollment['EvaluationID']]);
        $enrolledSubjects = $evalSubStmt->fetchAll(PDO::FETCH_ASSOC);
    }
}

$totalUnits = 0;
foreach ($enrolledSubjects as $sub) {
    $totalUnits += (int)$sub['Units'];
}

// Fee calculations
$tuitionPerUnit = 250.00;
$tuitionTotal = $totalUnits * $tuitionPerUnit;
$miscReg = 350.00;
$miscLib = 400.00;
$miscClinic = 250.00;
$miscAthletic = 300.00;
$miscLab = 600.00;
$miscTotal = $miscReg + $miscLib + $miscClinic + $miscAthletic + $miscLab;
$totalAssessed = $tuitionTotal + $miscTotal;
$isOfficiallyEnrolled = ($enrollment && $enrollment['Status'] === 'Enrolled');
?>
<?php include 'includes/header.php'; ?>
<?php include 'includes/sidebar.php'; ?>
<div class="pl-72 flex flex-col min-h-screen">
<?php include 'includes/topbar.php'; ?>
<main class="w-full pt-16 flex-1 px-gutter py-space-lg">
    <div class="flex flex-col w-full gap-space-lg max-w-5xl mx-auto">
        
        <!-- Header Banner (hidden on print) -->
        <div class="print:hidden bg-surface-container-lowest p-space-lg rounded-xl shadow-[0_1px_8px_rgba(0,0,0,0.04)] border border-surface-container flex flex-col md:flex-row justify-between items-start md:items-center gap-space-md">
            <div>
                <div class="flex items-center gap-2 mb-1">
                    <span class="px-2.5 py-0.5 rounded-full text-xs font-bold uppercase tracking-wider <?= $isOfficiallyEnrolled ? 'bg-green-100 text-green-800' : 'bg-amber-100 text-amber-800' ?>">
                        <?= $isOfficiallyEnrolled ? 'Officially Enrolled' : 'Enrollment Status: ' . htmlspecialchars($enrollment['Status'] ?? 'Pending') ?>
                    </span>
                    <span class="text-xs text-tertiary">A.Y. <?= htmlspecialchars($enrollment['SchoolYear'] ?? '2026-2027') ?> • <?= htmlspecialchars($enrollment['Semester'] ?? '1st Semester') ?></span>
                </div>
                <h1 class="font-headline-lg text-headline-lg text-on-surface font-bold">Enrollment Registration & COR</h1>
                <p class="font-body-md text-body-md text-tertiary">
                    Official Certificate of Registration (COR), matriculation assessment, and academic load verification.
                </p>
            </div>
            
            <div class="flex items-center gap-space-sm">
                <?php if ($isOfficiallyEnrolled): ?>
                <button onclick="window.print()" class="flex items-center gap-2 px-space-md py-space-sm rounded-lg font-label-md text-label-md font-semibold text-white bg-primary hover:bg-primary-container transition-colors shadow-sm">
                    <i class="fa-solid fa-print text-[18px]"></i>
                    Print Official COR
                </button>
                <?php endif; ?>
                <a href="student_schedule.php" class="flex items-center gap-2 px-space-md py-space-sm rounded-lg font-label-md text-label-md font-semibold bg-surface-container-low hover:bg-surface-container text-on-surface border border-surface-container transition-colors">
                    <i class="fa-solid fa-calendar-days text-[18px]"></i>
                    Class Schedule
                </a>
            </div>
        </div>

        <?php if (!$isOfficiallyEnrolled): ?>
        <!-- Enrollment Process Pipeline Guide -->
        <div class="print:hidden bg-surface-container-lowest p-space-lg rounded-xl shadow-[0_1px_8px_rgba(0,0,0,0.04)] border border-surface-container">
            <h2 class="font-title-lg text-title-lg font-bold text-on-surface mb-1 flex items-center gap-2">
                <i class="fa-solid fa-route text-primary"></i>
                Enrollment Completion Progress Guide
            </h2>
            <p class="text-body-sm text-tertiary mb-space-md">Complete all institutional gates below to unlock your official Certificate of Registration.</p>
            
            <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-5 gap-space-sm">
                <!-- Gate 1: Admission -->
                <div class="p-space-sm rounded-lg border <?= ($enrollment['AdmStatus'] ?? '') === 'Approved' ? 'bg-green-50/50 border-green-200' : 'bg-surface-container-low border-surface-container' ?>">
                    <div class="flex items-center justify-between mb-1">
                        <span class="text-xs font-bold uppercase text-tertiary">Gate 1</span>
                        <i class="fa-solid <?= ($enrollment['AdmStatus'] ?? '') === 'Approved' ? 'fa-circle-check text-green-600' : 'fa-clock text-amber-500' ?> text-sm"></i>
                    </div>
                    <div class="font-title-sm text-title-sm font-bold text-on-surface">Admissions</div>
                    <div class="text-xs text-tertiary"><?= htmlspecialchars($enrollment['AdmStatus'] ?? 'Under Review') ?></div>
                </div>

                <!-- Gate 2: Evaluation & Section -->
                <div class="p-space-sm rounded-lg border <?= !empty($enrollment['SectionCode']) ? 'bg-green-50/50 border-green-200' : 'bg-surface-container-low border-surface-container' ?>">
                    <div class="flex items-center justify-between mb-1">
                        <span class="text-xs font-bold uppercase text-tertiary">Gate 2</span>
                        <i class="fa-solid <?= !empty($enrollment['SectionCode']) ? 'fa-circle-check text-green-600' : 'fa-clock text-amber-500' ?> text-sm"></i>
                    </div>
                    <div class="font-title-sm text-title-sm font-bold text-on-surface">Section Block</div>
                    <div class="text-xs text-tertiary"><?= htmlspecialchars($enrollment['SectionCode'] ?? 'Pending Assign') ?></div>
                </div>

                <!-- Gate 3: Clinic -->
                <div class="p-space-sm rounded-lg border <?= ($enrollment['ClinicStatus'] ?? '') === 'Cleared' ? 'bg-green-50/50 border-green-200' : 'bg-surface-container-low border-surface-container' ?>">
                    <div class="flex items-center justify-between mb-1">
                        <span class="text-xs font-bold uppercase text-tertiary">Gate 3</span>
                        <i class="fa-solid <?= ($enrollment['ClinicStatus'] ?? '') === 'Cleared' ? 'fa-circle-check text-green-600' : 'fa-clock text-amber-500' ?> text-sm"></i>
                    </div>
                    <div class="font-title-sm text-title-sm font-bold text-on-surface">Health Check</div>
                    <div class="text-xs text-tertiary"><?= htmlspecialchars($enrollment['ClinicStatus'] ?? 'Pending Exam') ?></div>
                </div>

                <!-- Gate 4: Accounting Payment -->
                <div class="p-space-sm rounded-lg border <?= ($enrollment['PaymentStatus'] ?? '') === 'Paid' ? 'bg-green-50/50 border-green-200' : 'bg-surface-container-low border-surface-container' ?>">
                    <div class="flex items-center justify-between mb-1">
                        <span class="text-xs font-bold uppercase text-tertiary">Gate 4</span>
                        <i class="fa-solid <?= ($enrollment['PaymentStatus'] ?? '') === 'Paid' ? 'fa-circle-check text-green-600' : 'fa-clock text-amber-500' ?> text-sm"></i>
                    </div>
                    <div class="font-title-sm text-title-sm font-bold text-on-surface">Accounting / POS</div>
                    <div class="text-xs text-tertiary"><?= htmlspecialchars($enrollment['PaymentStatus'] ?? 'Unpaid') ?></div>
                </div>

                <!-- Gate 5: Registrar Approval -->
                <div class="p-space-sm rounded-lg border <?= $isOfficiallyEnrolled ? 'bg-green-50/50 border-green-200' : 'bg-surface-container-low border-surface-container' ?>">
                    <div class="flex items-center justify-between mb-1">
                        <span class="text-xs font-bold uppercase text-tertiary">Gate 5</span>
                        <i class="fa-solid <?= $isOfficiallyEnrolled ? 'fa-circle-check text-green-600' : 'fa-clock text-amber-500' ?> text-sm"></i>
                    </div>
                    <div class="font-title-sm text-title-sm font-bold text-on-surface">COR Release</div>
                    <div class="text-xs text-tertiary"><?= $isOfficiallyEnrolled ? 'Released' : 'Pending' ?></div>
                </div>
            </div>
        </div>
        <?php endif; ?>

        <!-- OFFICIAL CERTIFICATE OF REGISTRATION (COR) CONTAINER -->
        <div class="bg-surface-container-lowest rounded-xl shadow-[0_1px_8px_rgba(0,0,0,0.04)] border border-surface-container p-space-xl print:border-none print:shadow-none print:p-0">
            
            <!-- COR School Header -->
            <div class="text-center border-b-2 border-on-surface/10 pb-space-md mb-space-md">
                <div class="text-xs font-serif uppercase tracking-widest text-tertiary mb-1">Republic of the Philippines</div>
                <div class="font-headline-sm text-headline-sm font-bold uppercase tracking-wider text-on-surface">State University • Introllment System</div>
                <div class="text-xs text-tertiary uppercase tracking-wide">Office of the University Registrar • Student Records Division</div>
                <div class="mt-2 inline-block px-4 py-1 bg-surface-container-low border border-surface-container rounded font-bold uppercase tracking-wider text-primary text-sm">
                    Official Certificate of Matriculation & Registration (COR)
                </div>
            </div>

            <!-- Student Matriculation Details Grid -->
            <div class="grid grid-cols-2 md:grid-cols-4 gap-space-sm bg-surface-container-low/50 p-space-md rounded-lg border border-surface-container text-xs mb-space-md">
                <div>
                    <span class="text-tertiary block uppercase font-bold text-[10px]">Student Number</span>
                    <span class="font-mono font-bold text-primary text-sm"><?= htmlspecialchars($student['StudentNo'] ?? 'N/A') ?></span>
                </div>
                <div>
                    <span class="text-tertiary block uppercase font-bold text-[10px]">Student Name</span>
                    <span class="font-bold text-on-surface text-sm"><?= htmlspecialchars(($student['LastName'] ?? '') . ', ' . ($student['FirstName'] ?? '') . ' ' . ($student['MiddleName'] ?? '')) ?></span>
                </div>
                <div>
                    <span class="text-tertiary block uppercase font-bold text-[10px]">Academic Degree Program</span>
                    <span class="font-semibold text-on-surface"><?= htmlspecialchars($student['ProgramName'] ?? 'N/A') ?></span>
                </div>
                <div>
                    <span class="text-tertiary block uppercase font-bold text-[10px]">Collegiate Department</span>
                    <span class="font-semibold text-on-surface"><?= htmlspecialchars($student['DepartmentName'] ?? 'Academic Affairs') ?></span>
                </div>
                <div>
                    <span class="text-tertiary block uppercase font-bold text-[10px]">Academic Year / Term</span>
                    <span class="font-bold text-on-surface"><?= htmlspecialchars($enrollment['SchoolYear'] ?? '2026-2027') ?> • <?= htmlspecialchars($enrollment['Semester'] ?? '1st Sem') ?></span>
                </div>
                <div>
                    <span class="text-tertiary block uppercase font-bold text-[10px]">Section / Block</span>
                    <span class="font-bold text-primary"><?= htmlspecialchars($enrollment['SectionCode'] ?? 'Pending Block') ?></span>
                </div>
                <div>
                    <span class="text-tertiary block uppercase font-bold text-[10px]">Student Classification</span>
                    <span class="font-semibold text-on-surface"><?= htmlspecialchars($student['TypeName'] ?? 'Regular') ?></span>
                </div>
                <div>
                    <span class="text-tertiary block uppercase font-bold text-[10px]">Date Enrolled</span>
                    <span class="font-semibold text-on-surface"><?= !empty($enrollment['EnrollmentDate']) ? date('M d, Y', strtotime($enrollment['EnrollmentDate'])) : date('M d, Y') ?></span>
                </div>
            </div>

            <!-- Subject Schedule Matrix -->
            <div class="mb-space-md overflow-hidden border border-surface-container rounded-lg">
                <table class="w-full text-left border-collapse text-xs">
                    <thead>
                        <tr class="bg-surface-container-low text-tertiary font-label-sm text-label-sm uppercase tracking-wider border-b border-surface-container">
                            <th class="px-space-md py-2 font-bold">Course Code</th>
                            <th class="px-space-md py-2 font-bold">Course Descriptive Title</th>
                            <th class="px-space-md py-2 font-bold text-center">Units</th>
                            <th class="px-space-md py-2 font-bold text-center">Section</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y border-surface-container font-body-sm text-body-sm">
                        <?php if (!empty($enrolledSubjects)): ?>
                            <?php foreach ($enrolledSubjects as $sub): ?>
                            <tr>
                                <td class="px-space-md py-2 font-mono font-bold text-primary text-xs">
                                    <?= htmlspecialchars($sub['SubjectCode']) ?>
                                </td>
                                <td class="px-space-md py-2 font-medium text-on-surface text-xs">
                                    <?= htmlspecialchars($sub['SubjectTitle']) ?>
                                </td>
                                <td class="px-space-md py-2 text-center font-bold text-xs">
                                    <?= htmlspecialchars($sub['Units']) ?>
                                </td>
                                <td class="px-space-md py-2 text-center font-mono text-tertiary text-xs">
                                    <?= htmlspecialchars($sub['SectionCode'] ?? $enrollment['SectionCode'] ?? 'SEC-101') ?>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                            <tr class="bg-surface-container-low/60 font-bold">
                                <td colspan="2" class="px-space-md py-2 text-right uppercase tracking-wider text-xs">Total Registered Academic Load:</td>
                                <td class="px-space-md py-2 text-center text-primary text-sm"><?= $totalUnits ?> Units</td>
                                <td></td>
                            </tr>
                        <?php else: ?>
                            <tr>
                                <td colspan="4" class="px-space-md py-space-lg text-center text-tertiary">
                                    No subject load has been plotted or blocked yet. Please check back after Subject Evaluation and Sectioning.
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>

            <!-- Financial Assessment Summary -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-space-md mb-space-lg">
                <div class="p-space-md border border-surface-container rounded-lg bg-surface-container-low/30 text-xs">
                    <div class="font-bold uppercase tracking-wider text-on-surface mb-2 border-b border-surface-container pb-1 flex justify-between">
                        <span>Assessment of Fees</span>
                        <span>Amount (PHP)</span>
                    </div>
                    <div class="space-y-1 text-tertiary">
                        <div class="flex justify-between">
                            <span>Tuition Fee (<?= $totalUnits ?> units @ ₱<?= number_format($tuitionPerUnit, 2) ?>):</span>
                            <span class="font-mono text-on-surface">₱<?= number_format($tuitionTotal, 2) ?></span>
                        </div>
                        <div class="flex justify-between">
                            <span>Registration & Matriculation:</span>
                            <span class="font-mono text-on-surface">₱<?= number_format($miscReg, 2) ?></span>
                        </div>
                        <div class="flex justify-between">
                            <span>Library & Information Tech Fee:</span>
                            <span class="font-mono text-on-surface">₱<?= number_format($miscLib, 2) ?></span>
                        </div>
                        <div class="flex justify-between">
                            <span>Medical & Dental Clinic Fee:</span>
                            <span class="font-mono text-on-surface">₱<?= number_format($miscClinic, 2) ?></span>
                        </div>
                        <div class="flex justify-between">
                            <span>Athletic & Cultural Fee:</span>
                            <span class="font-mono text-on-surface">₱<?= number_format($miscAthletic, 2) ?></span>
                        </div>
                        <div class="flex justify-between">
                            <span>Computer / Laboratory Fee:</span>
                            <span class="font-mono text-on-surface">₱<?= number_format($miscLab, 2) ?></span>
                        </div>
                        <div class="flex justify-between font-bold text-on-surface pt-2 border-t border-surface-container text-sm">
                            <span>Total Institutional Assessment:</span>
                            <span class="font-mono text-primary">₱<?= number_format($totalAssessed, 2) ?></span>
                        </div>
                    </div>
                </div>

                <div class="p-space-md border border-surface-container rounded-lg bg-surface-container-low/30 text-xs flex flex-col justify-between">
                    <div>
                        <div class="font-bold uppercase tracking-wider text-on-surface mb-2 border-b border-surface-container pb-1">
                            Payment Details & Receipt Status
                        </div>
                        <div class="space-y-1.5 text-tertiary">
                            <div class="flex justify-between">
                                <span>Official Receipt (OR) Number:</span>
                                <span class="font-mono font-bold text-on-surface"><?= htmlspecialchars($enrollment['ReceiptNo'] ?? 'PENDING CASHIER') ?></span>
                            </div>
                            <div class="flex justify-between">
                                <span>Payment Status:</span>
                                <span class="font-bold <?= ($enrollment['PaymentStatus'] ?? '') === 'Paid' ? 'text-green-700' : 'text-amber-700' ?>">
                                    <?= htmlspecialchars($enrollment['PaymentStatus'] ?? 'Unpaid / Pending Assessment') ?>
                                </span>
                            </div>
                            <div class="flex justify-between">
                                <span>Total Amount Paid:</span>
                                <span class="font-mono font-bold text-on-surface">₱<?= number_format((float)($enrollment['AmountPaid'] ?? 0), 2) ?></span>
                            </div>
                            <div class="flex justify-between">
                                <span>Transaction Date:</span>
                                <span><?= !empty($enrollment['PaymentDate']) ? date('M d, Y', strtotime($enrollment['PaymentDate'])) : 'N/A' ?></span>
                            </div>
                        </div>
                    </div>

                    <!-- Registrar Stamp Box -->
                    <div class="mt-4 p-2 bg-white rounded border-2 border-dashed <?= $isOfficiallyEnrolled ? 'border-green-600 text-green-800' : 'border-amber-500 text-amber-800' ?> text-center">
                        <div class="text-[11px] font-bold uppercase tracking-widest">
                            <?= $isOfficiallyEnrolled ? 'OFFICIALLY VALIDATED & ENROLLED' : 'REGISTRAR PROVISIONAL REGISTRATION' ?>
                        </div>
                        <div class="text-[9px] text-tertiary">Validated by Office of the University Registrar</div>
                    </div>
                </div>
            </div>

            <!-- Signatures Footer -->
            <div class="grid grid-cols-2 gap-space-xl pt-space-lg border-t border-surface-container text-center text-xs">
                <div>
                    <div class="font-bold text-on-surface"><?= htmlspecialchars(($student['LastName'] ?? '') . ', ' . ($student['FirstName'] ?? '')) ?></div>
                    <div class="border-t border-on-surface/30 mt-1 pt-1 text-tertiary">Student Signature & Acceptance of Academic Policies</div>
                </div>
                <div>
                    <div class="font-bold text-on-surface">DR. ELEANOR VANCE, Ed.D.</div>
                    <div class="border-t border-on-surface/30 mt-1 pt-1 text-tertiary">University Registrar • Official Seal</div>
                </div>
            </div>

        </div>

    </div>
</main>
<?php include 'includes/footer.php'; ?>
</div>