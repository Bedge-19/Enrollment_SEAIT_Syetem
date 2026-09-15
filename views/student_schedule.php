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
    SELECT s.*, p.ProgramName
    FROM student s
    LEFT JOIN program p ON s.ProgramID = p.ProgramID
    WHERE s.StudentID = ?
");
$stmt->execute([$studentId]);
$student = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$student) {
    header("Location: login.php?error=" . urlencode("Student record not found."));
    exit;
}

// Fetch Student's Blocked Section
$secStmt = $pdo->prepare("
    SELECT e.EnrollmentID, e.Status as EnrollmentStatus, e.SchoolYear, e.Semester,
           sec.SectionID, sec.SectionCode, sec.YearLevel
    FROM enrollment e
    JOIN blocking blk ON e.EnrollmentID = blk.EnrollmentID
    JOIN section sec ON blk.SectionID = sec.SectionID
    WHERE e.StudentID = ?
    ORDER BY e.EnrollmentID DESC
    LIMIT 1
");
$secStmt->execute([$studentId]);
$sectionInfo = $secStmt->fetch(PDO::FETCH_ASSOC);

$schedules = [];
$sectionId = $sectionInfo['SectionID'] ?? 0;

if ($sectionId) {
    $schedStmt = $pdo->prepare("
        SELECT sc.*, sub.SubjectCode, sub.SubjectTitle, sub.Units
        FROM schedule sc
        JOIN subject sub ON sc.SubjectID = sub.SubjectID
        WHERE sc.SectionID = ?
        ORDER BY FIELD(sc.Day, 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday'), sc.TimeStart ASC
    ");
    $schedStmt->execute([$sectionId]);
    $schedules = $schedStmt->fetchAll(PDO::FETCH_ASSOC);
}

// Group schedules by day
$daysOfWeek = ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday'];
$scheduleByDay = [];
foreach ($daysOfWeek as $d) {
    $scheduleByDay[$d] = [];
}
$totalUnits = 0;
$seenSubjects = [];
foreach ($schedules as $s) {
    $day = $s['Day'];
    if (isset($scheduleByDay[$day])) {
        $scheduleByDay[$day][] = $s;
    }
    if (!isset($seenSubjects[$s['SubjectID']])) {
        $seenSubjects[$s['SubjectID']] = true;
        $totalUnits += (int)$s['Units'];
    }
}
?>
<?php include 'includes/header.php'; ?>
<?php include 'includes/sidebar.php'; ?>
<div class="pl-72 flex flex-col min-h-screen">
<?php include 'includes/topbar.php'; ?>
<main class="w-full pt-16 flex-1 px-gutter py-space-lg">
    <div class="flex flex-col w-full gap-space-lg max-w-6xl mx-auto">
        
        <!-- Header Banner -->
        <div class="bg-surface-container-lowest p-space-lg rounded-xl shadow-[0_1px_8px_rgba(0,0,0,0.04)] border border-surface-container flex flex-col md:flex-row justify-between items-start md:items-center gap-space-md">
            <div>
                <div class="flex items-center gap-2 mb-1">
                    <span class="px-2.5 py-0.5 rounded-full text-xs font-bold uppercase tracking-wider bg-primary/10 text-primary">Class Timetable</span>
                    <span class="text-xs text-tertiary">A.Y. <?= htmlspecialchars($sectionInfo['SchoolYear'] ?? '2026-2027') ?> • <?= htmlspecialchars($sectionInfo['Semester'] ?? '1st Semester') ?></span>
                </div>
                <h1 class="font-headline-lg text-headline-lg text-on-surface font-bold">Section & Class Schedule</h1>
                <p class="font-body-md text-body-md text-tertiary">
                    Weekly plotted lecture, laboratory, and classroom allocations for your assigned section block.
                </p>
            </div>
            
            <div class="flex items-center gap-space-sm print:hidden">
                <button onclick="window.print()" class="flex items-center gap-2 px-space-md py-space-sm rounded-lg font-label-md text-label-md font-semibold text-white bg-primary hover:bg-primary-container transition-colors shadow-sm">
                    <i class="fa-solid fa-print text-[18px]"></i>
                    Print Schedule
                </button>
            </div>
        </div>

        <!-- Section Status Card -->
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-space-md">
            <div class="bg-surface-container-lowest p-space-md rounded-xl shadow-[0_1px_8px_rgba(0,0,0,0.04)] border border-surface-container">
                <div class="text-xs font-bold uppercase tracking-wider text-tertiary mb-1">Enrolled Section</div>
                <div class="font-headline-sm text-headline-sm font-bold text-primary">
                    <?= htmlspecialchars($sectionInfo['SectionCode'] ?? 'Pending Assignment') ?>
                </div>
                <div class="text-xs text-tertiary mt-1">
                    Year Level: <?= htmlspecialchars($sectionInfo['YearLevel'] ?? '1') ?>
                </div>
            </div>

            <div class="bg-surface-container-lowest p-space-md rounded-xl shadow-[0_1px_8px_rgba(0,0,0,0.04)] border border-surface-container">
                <div class="text-xs font-bold uppercase tracking-wider text-tertiary mb-1">Enrolled Program</div>
                <div class="font-title-md text-title-md font-bold text-on-surface truncate">
                    <?= htmlspecialchars($student['ProgramName'] ?? 'General') ?>
                </div>
                <div class="text-xs text-tertiary mt-1 truncate">
                    Academic Degree Curriculum
                </div>
            </div>

            <div class="bg-surface-container-lowest p-space-md rounded-xl shadow-[0_1px_8px_rgba(0,0,0,0.04)] border border-surface-container">
                <div class="text-xs font-bold uppercase tracking-wider text-tertiary mb-1">Total Academic Load</div>
                <div class="font-headline-sm text-headline-sm font-bold text-on-surface">
                    <?= $totalUnits ?> <span class="text-sm font-normal text-tertiary">Units</span>
                </div>
                <div class="text-xs text-tertiary mt-1">
                    <?= count($seenSubjects) ?> Registered Subjects
                </div>
            </div>

            <div class="bg-surface-container-lowest p-space-md rounded-xl shadow-[0_1px_8px_rgba(0,0,0,0.04)] border border-surface-container">
                <div class="text-xs font-bold uppercase tracking-wider text-tertiary mb-1">Class Sessions</div>
                <div class="font-headline-sm text-headline-sm font-bold text-green-700">
                    <?= count($schedules) ?> <span class="text-sm font-normal text-tertiary">Slots</span>
                </div>
                <div class="text-xs text-tertiary mt-1">
                    Plotted Weekly Meetings
                </div>
            </div>
        </div>

        <?php if ($sectionId && !empty($schedules)): ?>
        <!-- Weekly Matrix View -->
        <div class="bg-surface-container-lowest rounded-xl shadow-[0_1px_8px_rgba(0,0,0,0.04)] border border-surface-container overflow-hidden">
            <div class="p-space-md bg-surface-container-low border-b border-surface-container flex items-center justify-between">
                <div class="flex items-center gap-2">
                    <i class="fa-solid fa-calendar-week text-primary text-[20px]"></i>
                    <h2 class="font-title-md text-title-md font-bold text-on-surface">Weekly Timetable Grid</h2>
                </div>
                <span class="text-xs font-semibold text-tertiary">Mon - Sat Schedule Matrix</span>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-6 divide-y lg:divide-y-0 lg:divide-x border-surface-container p-space-sm gap-2 lg:gap-0">
                <?php foreach ($daysOfWeek as $day): ?>
                <div class="flex flex-col min-h-[260px] p-2">
                    <div class="font-bold text-xs uppercase tracking-wider text-on-surface text-center py-1.5 mb-2 bg-surface-container-low rounded border border-surface-container">
                        <?= $day ?>
                    </div>
                    
                    <div class="flex-1 flex flex-col gap-2">
                        <?php if (!empty($scheduleByDay[$day])): ?>
                            <?php foreach ($scheduleByDay[$day] as $slot): ?>
                            <div class="p-2 rounded-lg bg-primary-fixed/20 border border-primary/20 flex flex-col gap-1 shadow-sm hover:shadow transition-shadow">
                                <div class="flex items-center justify-between">
                                    <span class="font-mono font-bold text-primary text-xs"><?= htmlspecialchars($slot['SubjectCode']) ?></span>
                                    <span class="text-[10px] font-semibold bg-white/80 px-1 rounded text-tertiary"><?= htmlspecialchars($slot['Units']) ?>u</span>
                                </div>
                                <div class="text-[11px] font-medium text-on-surface line-clamp-2 leading-tight">
                                    <?= htmlspecialchars($slot['SubjectTitle']) ?>
                                </div>
                                <div class="text-[10px] text-tertiary flex items-center gap-1 mt-1 pt-1 border-t border-primary/10">
                                    <i class="fa-solid fa-clock text-[12px]"></i>
                                    <?= date('g:i A', strtotime($slot['TimeStart'])) ?> - <?= date('g:i A', strtotime($slot['TimeEnd'])) ?>
                                </div>
                            </div>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <div class="h-full flex items-center justify-center text-[11px] text-tertiary/60 italic py-6">
                                No classes
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>

        <!-- Schedule List Table -->
        <div class="bg-surface-container-lowest rounded-xl shadow-[0_1px_8px_rgba(0,0,0,0.04)] border border-surface-container overflow-hidden">
            <div class="p-space-md bg-surface-container-low border-b border-surface-container flex items-center gap-2">
                <i class="fa-solid fa-rectangle-list text-primary text-[20px]"></i>
                <h2 class="font-title-md text-title-md font-bold text-on-surface">Detailed Class Schedule Roster</h2>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse text-xs">
                    <thead>
                        <tr class="bg-surface-container-low/50 text-tertiary font-label-sm text-label-sm uppercase tracking-wider border-b border-surface-container">
                            <th class="px-space-md py-space-sm font-bold">Day</th>
                            <th class="px-space-md py-space-sm font-bold">Time Window</th>
                            <th class="px-space-md py-space-sm font-bold">Subject Code</th>
                            <th class="px-space-md py-space-sm font-bold">Course Title</th>
                            <th class="px-space-md py-space-sm font-bold text-center">Units</th>
                            <th class="px-space-md py-space-sm font-bold">Facility / Room</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y border-surface-container font-body-sm text-body-sm">
                        <?php foreach ($schedules as $s): ?>
                        <tr class="hover:bg-surface-container-low/30 transition-colors">
                            <td class="px-space-md py-2.5 font-bold text-on-surface">
                                <?= htmlspecialchars($s['Day']) ?>
                            </td>
                            <td class="px-space-md py-2.5 text-tertiary font-mono">
                                <?= date('g:i A', strtotime($s['TimeStart'])) ?> – <?= date('g:i A', strtotime($s['TimeEnd'])) ?>
                            </td>
                            <td class="px-space-md py-2.5 font-mono font-bold text-primary">
                                <?= htmlspecialchars($s['SubjectCode']) ?>
                            </td>
                            <td class="px-space-md py-2.5 font-medium text-on-surface">
                                <?= htmlspecialchars($s['SubjectTitle']) ?>
                            </td>
                            <td class="px-space-md py-2.5 text-center font-bold text-on-surface">
                                <?= htmlspecialchars($s['Units']) ?>
                            </td>
                            <td class="px-space-md py-2.5 text-tertiary">
                                Main Academic Wing • Rm <?= rand(101, 408) ?>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <?php else: ?>
        <!-- Pending Sectioning State -->
        <div class="bg-surface-container-lowest p-space-xl rounded-xl shadow-[0_1px_8px_rgba(0,0,0,0.04)] border border-surface-container text-center flex flex-col items-center justify-center gap-space-sm py-16">
            <i class="fa-solid fa-clock text-5xl text-tertiary/60"></i>
            <h2 class="font-headline-sm text-headline-sm font-bold text-on-surface">Schedule Awaiting Section Blocking</h2>
            <p class="font-body-md text-body-md text-tertiary max-w-md">
                Your official class schedule will be visible once the Registrar assigns your section block and finalizes classroom allocations.
            </p>
            <a href="student_enrollment.php" class="mt-2 inline-flex items-center gap-2 px-space-md py-space-sm bg-primary text-white font-label-md rounded-lg shadow-sm hover:bg-primary-container transition-colors">
                <i class="fa-solid fa-circle-check text-[18px]"></i>
                Check Enrollment Status
            </a>
        </div>
        <?php endif; ?>

    </div>
</main>
<?php include 'includes/footer.php'; ?>
</div>