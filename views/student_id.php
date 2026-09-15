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

// Fetch Latest Enrollment & Section
$enrStmt = $pdo->prepare("
    SELECT e.EnrollmentID, e.SchoolYear, e.Semester, e.Status as EnrollmentStatus,
           sec.SectionCode, sec.YearLevel
    FROM enrollment e
    LEFT JOIN blocking blk ON e.EnrollmentID = blk.EnrollmentID
    LEFT JOIN section sec ON blk.SectionID = sec.SectionID
    WHERE e.StudentID = ?
    ORDER BY e.EnrollmentID DESC
    LIMIT 1
");
$enrStmt->execute([$studentId]);
$enrollment = $enrStmt->fetch(PDO::FETCH_ASSOC);

// Fetch ID Validation Record
$idvStmt = $pdo->prepare("
    SELECT idv.*, stf.LastName as StaffLastName, stf.FirstName as StaffFirstName
    FROM id_validation idv
    LEFT JOIN staff stf ON idv.StaffID = stf.StaffID
    WHERE idv.StudentID = ?
    ORDER BY idv.ValidationID DESC
    LIMIT 1
");
$idvStmt->execute([$studentId]);
$idValidation = $idvStmt->fetch(PDO::FETCH_ASSOC);

$isValidated = ($idValidation && ($idValidation['Status'] ?? '') === 'Validated');
$schoolYear = $enrollment['SchoolYear'] ?? '2026-2027';
$semester = $enrollment['Semester'] ?? '1st Semester';
?>
<?php include 'includes/header.php'; ?>
<?php include 'includes/sidebar.php'; ?>
<div class="pl-72 flex flex-col min-h-screen">
<?php include 'includes/topbar.php'; ?>
<main class="w-full pt-16 flex-1 px-gutter py-space-lg">
    <div class="flex flex-col w-full gap-space-lg max-w-5xl mx-auto">
        
        <!-- Header Banner -->
        <div class="bg-surface-container-lowest p-space-lg rounded-xl shadow-[0_1px_8px_rgba(0,0,0,0.04)] border border-surface-container flex flex-col md:flex-row justify-between items-start md:items-center gap-space-md print:hidden">
            <div>
                <div class="flex items-center gap-2 mb-1">
                    <span class="px-2.5 py-0.5 rounded-full text-xs font-bold uppercase tracking-wider <?= $isValidated ? 'bg-green-100 text-green-800' : 'bg-amber-100 text-amber-800' ?>">
                        <?= $isValidated ? 'Validated & Active' : 'Validation Status: ' . htmlspecialchars($idValidation['Status'] ?? 'Pending') ?>
                    </span>
                    <span class="text-xs text-tertiary">Step 7 • Campus Pass & RFID</span>
                </div>
                <h1 class="font-headline-lg text-headline-lg text-on-surface font-bold">ID Validation Card</h1>
                <p class="font-body-md text-body-md text-tertiary">
                    Official digital institutional identification card and semester validation pass.
                </p>
            </div>
            
            <div class="flex items-center gap-space-sm">
                <button onclick="window.print()" class="flex items-center gap-2 px-space-md py-space-sm rounded-lg font-label-md text-label-md font-semibold text-white bg-primary hover:bg-primary-container transition-colors shadow-sm">
                    <i class="fa-solid fa-print text-[18px]"></i>
                    Print Digital ID
                </button>
            </div>
        </div>

        <!-- DIGITAL ID CARD CONTAINER (FRONT & BACK) -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-space-lg">
            
            <!-- FRONT CARD -->
            <div class="relative bg-gradient-to-br from-slate-900 via-slate-800 to-primary-950 text-white p-space-lg rounded-2xl shadow-xl border border-white/10 flex flex-col justify-between min-h-[380px] overflow-hidden">
                <!-- Background university watermark -->
                <div class="absolute -right-8 -bottom-8 opacity-10 pointer-events-none">
                    <i class="fa-solid fa-graduation-cap text-[240px]"></i>
                </div>

                <!-- Card Header -->
                <div class="relative z-10 flex items-center justify-between border-b border-white/15 pb-3">
                    <div class="flex items-center gap-2.5">
                        <div class="w-9 h-9 rounded-full bg-primary flex items-center justify-center font-bold text-white shadow-md">
                            <i class="fa-solid fa-book-atlas text-lg"></i>
                        </div>
                        <div>
                            <div class="text-[11px] font-bold uppercase tracking-widest text-primary-200">State University</div>
                            <div class="text-[9px] text-white/70 uppercase tracking-wider">Official Student Pass</div>
                        </div>
                    </div>
                    
                    <div class="text-right">
                        <span class="inline-flex items-center gap-1.5 px-2 py-0.5 rounded text-[10px] font-mono font-bold <?= $isValidated ? 'bg-green-500/20 text-green-300 border border-green-500/40' : 'bg-amber-500/20 text-amber-300 border border-amber-500/40' ?>">
                            <i class="fa-solid <?= $isValidated ? 'fa-circle-check' : 'fa-hourglass-half' ?> text-[11px]"></i>
                            <?= $isValidated ? 'VALIDATED' : 'PENDING' ?>
                        </span>
                    </div>
                </div>

                <!-- Card Body: Avatar & Details -->
                <div class="relative z-10 flex items-center gap-space-md my-4">
                    <!-- Photo Avatar -->
                    <div class="relative shrink-0">
                        <div class="w-24 h-28 rounded-xl bg-gradient-to-t from-primary/30 to-white/10 border-2 border-white/30 flex flex-col items-center justify-center overflow-hidden shadow-inner text-white/80">
                            <i class="fa-solid fa-user text-5xl"></i>
                            <span class="text-[9px] font-bold tracking-wider uppercase mt-1 text-white/60">Photo</span>
                        </div>
                        <div class="absolute -bottom-1 -right-1 w-6 h-6 rounded-full bg-primary text-white flex items-center justify-center shadow">
                            <i class="fa-solid fa-id-card-clip text-[14px]"></i>
                        </div>
                    </div>

                    <!-- Student Info -->
                    <div class="flex-1 space-y-1.5">
                        <div>
                            <div class="text-[10px] font-semibold text-white/60 uppercase tracking-wider">Student Name</div>
                            <div class="font-bold text-base leading-tight text-white tracking-wide">
                                <?= htmlspecialchars(($student['FirstName'] ?? '') . ' ' . ($student['LastName'] ?? '')) ?>
                            </div>
                        </div>

                        <div>
                            <div class="text-[10px] font-semibold text-white/60 uppercase tracking-wider">Student Number</div>
                            <div class="font-mono font-bold text-primary-200 text-sm tracking-wider">
                                <?= htmlspecialchars($student['StudentNo'] ?? '2026-00000') ?>
                            </div>
                        </div>

                        <div>
                            <div class="text-[10px] font-semibold text-white/60 uppercase tracking-wider">Degree Program</div>
                            <div class="text-xs font-medium text-white/90 truncate">
                                <?= htmlspecialchars($student['ProgramName'] ?? 'General') ?>
                            </div>
                        </div>

                        <div class="text-[11px] text-white/80">
                            Section: <span class="font-bold text-white font-mono"><?= htmlspecialchars($enrollment['SectionCode'] ?? 'TBA') ?></span>
                        </div>
                    </div>
                </div>

                <!-- Card Footer / Barcode -->
                <div class="relative z-10 pt-3 border-t border-white/15 flex items-center justify-between">
                    <div>
                        <div class="text-[9px] text-white/60 uppercase tracking-widest">Academic Year Valid</div>
                        <div class="text-xs font-bold font-mono text-white"><?= htmlspecialchars($schoolYear) ?> • <?= htmlspecialchars($semester) ?></div>
                    </div>

                    <!-- Barcode Display -->
                    <div class="text-right">
                        <div class="bg-white/95 px-2 py-1 rounded inline-block shadow">
                            <div class="font-mono text-[10px] font-black text-black tracking-widest">
                                ||||| | |||| ||| |||||
                            </div>
                        </div>
                        <div class="text-[8px] font-mono text-white/60 tracking-wider mt-0.5"><?= htmlspecialchars($student['StudentNo'] ?? '') ?></div>
                    </div>
                </div>
            </div>

            <!-- BACK CARD -->
            <div class="relative bg-gradient-to-br from-slate-900 via-slate-800 to-slate-950 text-white p-space-lg rounded-2xl shadow-xl border border-white/10 flex flex-col justify-between min-h-[380px]">
                
                <!-- Back Header -->
                <div class="border-b border-white/15 pb-2">
                    <div class="text-[10px] font-bold uppercase tracking-widest text-white/80">State University Institutional Pass</div>
                    <div class="text-[8px] text-white/50">This card is non-transferable and remains property of the University.</div>
                </div>

                <!-- Back Info Grid -->
                <div class="space-y-3 my-3 text-xs">
                    <div class="bg-white/5 p-2.5 rounded-lg border border-white/10">
                        <div class="text-[10px] font-bold uppercase text-primary-200 mb-1">In Case of Emergency (ICE)</div>
                        <div class="text-xs font-bold text-white"><?= htmlspecialchars($student['GuardianName'] ?: 'Parent / Legal Guardian') ?></div>
                        <div class="text-xs text-white/80 font-mono"><?= htmlspecialchars($student['GuardianPhone'] ?: '+63 (02) 8123-4567') ?></div>
                        <div class="text-[10px] text-white/60 mt-0.5"><?= htmlspecialchars($student['Address'] ?: 'Metro Manila, Philippines') ?></div>
                    </div>

                    <div class="text-[10px] text-white/70 space-y-1 leading-relaxed">
                        <p>• Must be presented upon entry to campus turnstiles, library, and examination halls.</p>
                        <p>• If found, please return to the Office of the University Registrar or call (02) 8900-1122.</p>
                    </div>
                </div>

                <!-- Back Signatures & QR -->
                <div class="pt-3 border-t border-white/15 flex items-end justify-between">
                    <div>
                        <div class="font-serif text-xs font-bold text-white">DR. ELEANOR VANCE</div>
                        <div class="text-[8px] text-white/60 uppercase tracking-wider">University Registrar</div>
                    </div>

                    <div class="w-14 h-14 bg-white p-1 rounded-lg shadow flex flex-col items-center justify-center">
                        <i class="fa-solid fa-qrcode text-black text-3xl"></i>
                    </div>
                </div>

            </div>

        </div>

        <!-- ID Validation Details & RFID Pass Info -->
        <div class="bg-surface-container-lowest rounded-xl shadow-[0_1px_8px_rgba(0,0,0,0.04)] border border-surface-container overflow-hidden">
            <div class="p-space-md bg-surface-container-low border-b border-surface-container flex items-center justify-between">
                <div class="flex items-center gap-2">
                    <i class="fa-solid fa-id-badge text-primary text-[20px]"></i>
                    <h2 class="font-title-md text-title-md font-bold text-on-surface">ID Validation Status & Campus Access Privileges</h2>
                </div>
                <span class="text-xs font-semibold text-tertiary">RFID System Integration</span>
            </div>

            <div class="p-space-lg grid grid-cols-1 md:grid-cols-3 gap-space-md">
                <div class="p-space-md rounded-lg border border-surface-container bg-surface-container-low/40">
                    <div class="text-xs font-bold uppercase tracking-wider text-tertiary mb-1">Validation Record</div>
                    <div class="font-title-md text-title-md font-bold <?= $isValidated ? 'text-green-700' : 'text-amber-700' ?>">
                        <?= htmlspecialchars($idValidation['Status'] ?? 'Pending Validation') ?>
                    </div>
                    <div class="text-xs text-tertiary mt-1">
                        Validated By: <?= !empty($idValidation['StaffLastName']) ? htmlspecialchars($idValidation['StaffFirstName'] . ' ' . $idValidation['StaffLastName']) : 'Registrar ID Processor' ?>
                    </div>
                </div>

                <div class="p-space-md rounded-lg border border-surface-container bg-surface-container-low/40">
                    <div class="text-xs font-bold uppercase tracking-wider text-tertiary mb-1">Validity Period</div>
                    <div class="font-title-md text-title-md font-bold text-on-surface">
                        <?= !empty($idValidation['ValidUntil']) ? date('M d, Y', strtotime($idValidation['ValidUntil'])) : 'End of A.Y. 2026-2027' ?>
                    </div>
                    <div class="text-xs text-tertiary mt-1">
                        Issued: <?= !empty($idValidation['ValidationDate']) ? date('M d, Y', strtotime($idValidation['ValidationDate'])) : date('M d, Y') ?>
                    </div>
                </div>

                <div class="p-space-md rounded-lg border border-surface-container bg-surface-container-low/40">
                    <div class="text-xs font-bold uppercase tracking-wider text-tertiary mb-1">Physical Smart Card</div>
                    <div class="font-title-md text-title-md font-bold text-primary">
                        Window 5 • Registrar
                    </div>
                    <div class="text-xs text-tertiary mt-1">
                        Claim printed RFID smart card with COR
                    </div>
                </div>
            </div>
        </div>

        <!-- How to get physical card sticker -->
        <div class="bg-surface-container-lowest p-space-md rounded-xl shadow-[0_1px_8px_rgba(0,0,0,0.04)] border border-surface-container flex gap-space-sm print:hidden">
            <i class="fa-solid fa-circle-question text-primary text-2xl shrink-0"></i>
            <div class="space-y-1">
                <h3 class="font-title-sm text-title-sm font-bold text-on-surface">Physical ID Card Validation Process</h3>
                <p class="font-body-sm text-body-sm text-tertiary leading-relaxed">
                    Once your enrollment and payment are complete, proceed to the <strong>Office of the University Registrar (Window 5: ID Validation & Photo Capture)</strong> to receive your physical semester hologram sticker and activate your campus turnstile RFID tap pass.
                </p>
            </div>
        </div>

    </div>
</main>
<?php include 'includes/footer.php'; ?>
</div>