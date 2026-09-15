<?php
session_start();
require_once '../config/db.php';

if (!isset($_SESSION['UserType']) || $_SESSION['UserType'] !== 'Staff' || !in_array($_SESSION['Role'] ?? '', ['Registrar', 'Admin'])) {
    header("Location: login.php?error=" . urlencode("Access denied. Registrar privileges required."));
    exit;
}

// Handle Schedule Add / Delete
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $csrf = $_POST['csrf_token'] ?? '';
    if (!isset($_SESSION['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $csrf)) {
        header("Location: registrar_schedule.php?error=" . urlencode("Invalid security token."));
        exit;
    }

    $action = $_POST['action'] ?? '';

    if ($action === 'add_schedule') {
        $sectionId = (int)($_POST['section_id'] ?? 0);
        $subjectId = (int)($_POST['subject_id'] ?? 0);
        $day = $_POST['day'] ?? 'Monday';
        $timeStart = $_POST['time_start'] ?? '08:00:00';
        $timeEnd = $_POST['time_end'] ?? '09:30:00';

        if (!$sectionId || !$subjectId) {
            header("Location: registrar_schedule.php?error=" . urlencode("Section and Subject are required."));
            exit;
        }

        try {
            $stmt = $pdo->prepare("INSERT INTO schedule (Day, TimeStart, TimeEnd, SectionID, SubjectID) VALUES (?, ?, ?, ?, ?)");
            $stmt->execute([$day, $timeStart, $timeEnd, $sectionId, $subjectId]);

            require_once '../config/logger.php';
            log_activity($pdo, 'Plot Schedule', 'Class Schedule Plotter', "Plotted $day ($timeStart - $timeEnd) for Section $sectionId, Subject $subjectId");

            header("Location: registrar_schedule.php?msg=" . urlencode("Schedule slot plotted successfully!"));
            exit;
        } catch (Exception $e) {
            header("Location: registrar_schedule.php?error=" . urlencode("Failed to plot schedule: " . $e->getMessage()));
            exit;
        }
    } elseif ($action === 'delete_schedule') {
        $schedId = (int)($_POST['schedule_id'] ?? 0);
        $stmt = $pdo->prepare("DELETE FROM schedule WHERE ScheduleID = ?");
        $stmt->execute([$schedId]);
        header("Location: registrar_schedule.php?msg=" . urlencode("Schedule slot removed."));
        exit;
    }
}

$sectionFilter = (int)($_GET['section_id'] ?? 0);

$sql = "
    SELECT sc.*, s.SectionCode, s.YearLevel, sub.SubjectCode, sub.SubjectTitle, sub.Units, p.ProgramName
    FROM schedule sc
    JOIN section s ON sc.SectionID = s.SectionID
    JOIN subject sub ON sc.SubjectID = sub.SubjectID
    LEFT JOIN program p ON s.ProgramID = p.ProgramID
    WHERE 1=1
";
$params = [];

if ($sectionFilter > 0) {
    $sql .= " AND sc.SectionID = ?";
    $params[] = $sectionFilter;
}

$sql .= " ORDER BY FIELD(sc.Day, 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday'), sc.TimeStart ASC";
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$schedules = $stmt->fetchAll();

$sections = $pdo->query("SELECT s.*, p.ProgramName FROM section s LEFT JOIN program p ON s.ProgramID = p.ProgramID ORDER BY s.SectionCode ASC")->fetchAll();
$subjects = $pdo->query("SELECT * FROM subject ORDER BY SubjectCode ASC")->fetchAll();
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
                        <i class="fa-solid fa-calendar-days text-[18px]"></i>
                        <span>Registrar Module</span>
                    </div>
                    <h1 class="font-headline-lg text-headline-lg text-on-surface tracking-tight">Class Schedule Plotter</h1>
                    <p class="font-body-md text-body-md text-tertiary">
                        Coordinate curriculum subject timetables, classroom sections, day-of-week slots, and lecture hours.
                    </p>
                </div>
                <button onclick="document.getElementById('addSchedModal').classList.remove('hidden')" class="flex items-center gap-space-xs px-4 py-2 bg-primary hover:bg-primary-container text-on-primary rounded-lg font-label-md text-label-md font-semibold transition-colors shadow-sm">
                    <i class="fa-solid fa-calendar-plus text-[18px]"></i>
                    <span>Plot New Schedule</span>
                </button>
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

        <!-- Section Filter Bar -->
        <section class="bg-surface-container-lowest p-space-md rounded-xl shadow-[0_1px_8px_rgba(0,0,0,0.04)] border border-outline-variant/30">
            <form method="GET" class="flex items-center gap-3">
                <label class="text-xs font-semibold text-tertiary uppercase">Filter by Class Section:</label>
                <select name="section_id" onchange="this.form.submit()" class="h-9 px-3 rounded-lg bg-surface-container-low border border-surface-container text-sm">
                    <option value="">All Active Sections</option>
                    <?php foreach($sections as $sec): ?>
                        <option value="<?= $sec['SectionID'] ?>" <?= $sectionFilter === (int)$sec['SectionID'] ? 'selected' : '' ?>>
                            <?= htmlspecialchars($sec['SectionCode'] . ' - ' . ($sec['ProgramName'] ?? 'Gen')) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
                <?php if ($sectionFilter): ?>
                    <a href="registrar_schedule.php" class="text-xs text-primary hover:underline">Clear Filter</a>
                <?php endif; ?>
            </form>
        </section>

        <!-- Schedules Table -->
        <section class="bg-surface-container-lowest rounded-xl shadow-[0_1px_8px_rgba(0,0,0,0.04)] border border-outline-variant/30 overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse text-sm">
                    <thead>
                        <tr class="bg-surface-container-low text-tertiary text-xs uppercase font-label-sm border-b border-surface-container">
                            <th class="px-space-md py-space-sm">Day of Week</th>
                            <th class="px-space-md py-space-sm">Time Window</th>
                            <th class="px-space-md py-space-sm">Subject Code & Description</th>
                            <th class="px-space-md py-space-sm">Units</th>
                            <th class="px-space-md py-space-sm">Assigned Section</th>
                            <th class="px-space-md py-space-sm text-right">Action</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-surface-container">
                        <?php foreach($schedules as $sched): ?>
                        <tr class="hover:bg-surface-container-low/50 transition-colors">
                            <td class="px-space-md py-space-sm font-semibold text-on-surface">
                                <span class="px-2.5 py-1 rounded bg-surface-container font-mono text-xs font-bold text-primary">
                                    <?= htmlspecialchars($sched['Day']) ?>
                                </span>
                            </td>
                            <td class="px-space-md py-space-sm font-mono text-xs text-on-surface">
                                <?= date('h:i A', strtotime($sched['TimeStart'])) ?> – <?= date('h:i A', strtotime($sched['TimeEnd'])) ?>
                            </td>
                            <td class="px-space-md py-space-sm">
                                <span class="font-bold text-primary"><?= htmlspecialchars($sched['SubjectCode']) ?></span>
                                <span class="block text-xs text-tertiary"><?= htmlspecialchars($sched['SubjectTitle']) ?></span>
                            </td>
                            <td class="px-space-md py-space-sm font-medium text-on-surface">
                                <?= $sched['Units'] ?> Units
                            </td>
                            <td class="px-space-md py-space-sm text-xs font-semibold text-on-surface">
                                <?= htmlspecialchars($sched['SectionCode']) ?>
                                <span class="block text-[11px] font-normal text-tertiary"><?= htmlspecialchars($sched['ProgramName'] ?? '') ?></span>
                            </td>
                            <td class="px-space-md py-space-sm text-right">
                                <form method="POST" action="registrar_schedule.php" class="inline" onsubmit="return confirm('Remove this schedule slot?');">
                                    <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token'] ?? '') ?>">
                                    <input type="hidden" name="action" value="delete_schedule">
                                    <input type="hidden" name="schedule_id" value="<?= $sched['ScheduleID'] ?>">
                                    <button type="submit" class="p-1.5 text-tertiary hover:text-error rounded hover:bg-surface-container" title="Delete Schedule Slot">
                                        <i class="fa-solid fa-trash-can text-[18px]"></i>
                                    </button>
                                </form>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                        <?php if(empty($schedules)): ?>
                        <tr>
                            <td colspan="6" class="px-space-md py-8 text-center text-tertiary">No class schedule slots found for this selection. Click "Plot New Schedule" to assign classes.</td>
                        </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </section>

    </div>
</main>

<!-- Add Schedule Modal -->
<div id="addSchedModal" class="fixed inset-0 z-50 bg-black/40 backdrop-blur-sm hidden flex items-center justify-center p-4">
    <div class="bg-surface-container-lowest rounded-2xl shadow-xl max-w-md w-full p-6 border border-outline-variant/40">
        <div class="flex items-center justify-between pb-3 border-b border-surface-container">
            <h3 class="font-headline-sm text-headline-sm text-on-surface">Plot Class Schedule Slot</h3>
            <button onclick="document.getElementById('addSchedModal').classList.add('hidden')" class="text-tertiary hover:text-on-surface">
                <i class="fa-solid fa-xmark"></i>
            </button>
        </div>
        <form method="POST" action="registrar_schedule.php" class="flex flex-col gap-3 mt-4 text-sm">
            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token'] ?? '') ?>">
            <input type="hidden" name="action" value="add_schedule">

            <div>
                <label class="block text-xs font-semibold text-tertiary uppercase mb-1">Target Section</label>
                <select name="section_id" required class="w-full h-9 px-3 rounded-lg bg-surface-container-low border border-surface-container text-sm focus:outline-none focus:border-primary">
                    <option value="">-- Choose Class Section --</option>
                    <?php foreach($sections as $sec): ?>
                        <option value="<?= $sec['SectionID'] ?>"><?= htmlspecialchars($sec['SectionCode'] . ' - ' . ($sec['ProgramName'] ?? '')) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div>
                <label class="block text-xs font-semibold text-tertiary uppercase mb-1">Course / Subject</label>
                <select name="subject_id" required class="w-full h-9 px-3 rounded-lg bg-surface-container-low border border-surface-container text-sm focus:outline-none focus:border-primary">
                    <option value="">-- Choose Subject --</option>
                    <?php foreach($subjects as $sub): ?>
                        <option value="<?= $sub['SubjectID'] ?>"><?= htmlspecialchars($sub['SubjectCode'] . ' - ' . $sub['SubjectTitle']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div>
                <label class="block text-xs font-semibold text-tertiary uppercase mb-1">Day of Week</label>
                <select name="day" required class="w-full h-9 px-3 rounded-lg bg-surface-container-low border border-surface-container text-sm focus:outline-none focus:border-primary">
                    <option value="Monday">Monday</option>
                    <option value="Tuesday">Tuesday</option>
                    <option value="Wednesday">Wednesday</option>
                    <option value="Thursday">Thursday</option>
                    <option value="Friday">Friday</option>
                    <option value="Saturday">Saturday</option>
                </select>
            </div>

            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="block text-xs font-semibold text-tertiary uppercase mb-1">Time Start</label>
                    <input type="time" name="time_start" value="08:00" required class="w-full h-9 px-3 rounded-lg bg-surface-container-low border border-surface-container text-sm focus:outline-none focus:border-primary"/>
                </div>
                <div>
                    <label class="block text-xs font-semibold text-tertiary uppercase mb-1">Time End</label>
                    <input type="time" name="time_end" value="09:30" required class="w-full h-9 px-3 rounded-lg bg-surface-container-low border border-surface-container text-sm focus:outline-none focus:border-primary"/>
                </div>
            </div>

            <div class="flex items-center justify-end gap-2 mt-4 pt-3 border-t border-surface-container">
                <button type="button" onclick="document.getElementById('addSchedModal').classList.add('hidden')" class="px-4 py-2 rounded-lg bg-surface-container text-sm font-medium text-tertiary hover:bg-surface-container-high">Cancel</button>
                <button type="submit" class="px-5 py-2 rounded-lg bg-primary text-on-primary text-sm font-semibold hover:bg-primary-container shadow-sm">Save Schedule Slot</button>
            </div>
        </form>
    </div>
</div>

<?php include 'includes/footer.php'; ?>