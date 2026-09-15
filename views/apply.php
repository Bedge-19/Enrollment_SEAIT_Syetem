<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once '../config/db.php';
// Fetch programs
$stmt = $pdo->query("SELECT ProgramID, ProgramName FROM program");
$programs = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Fetch student types
$stmt = $pdo->query("SELECT StudentTypeID, TypeName FROM student_type");
$studentTypes = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8"/>
    <meta content="width=device-width, initial-scale=1.0" name="viewport"/>
    <title>SEAIT Enrolment Platform - Apply for Admission</title>
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@20..48,100..700,0..1,-50..200" rel="stylesheet"/>
    <script src="../assets/tailwindcss.js"></script>
    <script>
        tailwind.config={darkMode:"class",theme:{extend:{"colors":{"surface-container-lowest":"#ffffff","on-surface":"#0f172a","tertiary":"#64748b","primary":"#f97316","secondary":"#fb923c","background":"#ffffff","surface-container-low":"#f8fafc","surface-container":"#f1f5f9","primary-container":"#ffedd5","on-primary-container":"#c2410c","on-primary":"#ffffff","error":"#ef4444","on-error":"#ffffff"},"borderRadius":{"DEFAULT":"0.25rem","lg":"0.5rem","xl":"0.75rem","full":"9999px"}}}}
    </script>
    <style>
        body { background-image: radial-gradient(circle at top right, #eff4ff, #f8f9ff); background-attachment: fixed; }
        .glass-panel { background: rgba(255, 255, 255, 0.85); backdrop-filter: blur(12px); border: 1px solid rgba(255, 255, 255, 0.3); }
        .input-field { width: 100%; height: 2.75rem; padding: 0 1rem; background-color: #eff4ff; color: #0b1c30; border-radius: 0.5rem; outline: none; transition: all 0.2s; border: 1px solid transparent; }
        .input-field:focus { background-color: #ffffff; border-color: #a33900; box-shadow: 0 0 0 4px rgba(163, 57, 0, 0.1); }
        .label { display: block; font-size: 0.875rem; font-weight: 600; color: #0b1c30; margin-bottom: 0.25rem; }
    </style>
    <script>
        function disableButton(form) {
            const btn = form.querySelector('button[type="submit"]');
            btn.innerHTML = 'Submitting... <i class="fa-solid fa-hourglass-half"></i>';
            btn.disabled = true;
            btn.classList.add('opacity-50', 'cursor-not-allowed');
            return true;
        }
    </script>
</head>
<body class="font-sans antialiased text-on-surface min-h-screen flex flex-col items-center justify-center py-10">

<div class="max-w-4xl w-full px-6">
    <div class="text-center mb-8">
        <div class="inline-flex items-center justify-center w-16 h-16 rounded-full bg-primary/10 text-primary mb-4">
            <i class="fa-solid fa-graduation-cap text-4xl"></i>
        </div>
        <h1 class="text-4xl font-bold tracking-tight text-on-surface mb-2">Application for Admission</h1>
        <p class="text-tertiary">Begin your journey with SEAIT. Please fill out the details accurately.</p>
    </div>

    <form action="../controllers/admission_controller.php" method="POST" class="glass-panel p-8 rounded-2xl shadow-xl space-y-8" onsubmit="return disableButton(this)">
        <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token'] ?? ''); ?>">

        <?php if (isset($_GET['error'])): ?>
            <div class="p-4 bg-red-100 text-red-700 border border-red-300 rounded-lg font-semibold">
                <?= htmlspecialchars($_GET['error']) ?>
            </div>
        <?php endif; ?>

        
        <!-- Section: Personal Info -->
        <div>
            <div class="flex items-center gap-2 mb-4 pb-2 border-b border-surface-container">
                <i class="fa-solid fa-user text-primary"></i>
                <h2 class="text-lg font-semibold">Personal Information</h2>
            </div>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div>
                    <label class="label">First Name *</label>
                    <input type="text" name="FirstName" class="input-field" required>
                </div>
                <div>
                    <label class="label">Middle Name</label>
                    <input type="text" name="MiddleName" class="input-field">
                </div>
                <div>
                    <label class="label">Last Name *</label>
                    <input type="text" name="LastName" class="input-field" required>
                </div>
                <div>
                    <label class="label">Date of Birth *</label>
                    <input type="date" name="BirthDate" class="input-field" required>
                </div>
                <div>
                    <label class="label">Sex *</label>
                    <select name="Sex" class="input-field" required>
                        <option value="">Select...</option>
                        <option value="Male">Male</option>
                        <option value="Female">Female</option>
                    </select>
                </div>
                <div class="md:col-span-2">
                    <label class="label">Complete Address *</label>
                    <input type="text" name="Address" class="input-field" required>
                </div>
                <div>
                    <label class="label">Contact No. *</label>
                    <input type="text" name="ContactNo" class="input-field" required>
                </div>
                <div>
                    <label class="label">Email Address *</label>
                    <input type="email" name="Email" class="input-field" required>
                </div>
            </div>
        </div>

        <!-- Section: Academic Profile -->
        <div>
            <div class="flex items-center gap-2 mb-4 pb-2 border-b border-surface-container">
                <i class="fa-solid fa-scroll text-primary"></i>
                <h2 class="text-lg font-semibold">Academic Profile</h2>
            </div>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="label">Previous School Name *</label>
                    <input type="text" name="PreviousSchoolName" class="input-field" required>
                </div>
                <div>
                    <label class="label">Previous Program / Strand *</label>
                    <input type="text" name="PreviousProgram" class="input-field" required>
                </div>
                <div>
                    <label class="label">Last Year Level Completed *</label>
                    <input type="text" name="LastYearLevelCompleted" class="input-field" required>
                </div>
                <div>
                    <label class="label">General Weighted Average (GWA) *</label>
                    <input type="number" step="0.01" name="GWA" class="input-field" required>
                </div>
            </div>
        </div>

        <!-- Section: Program Application -->
        <div>
            <div class="flex items-center gap-2 mb-4 pb-2 border-b border-surface-container">
                <i class="fa-solid fa-bookmark text-primary"></i>
                <h2 class="text-lg font-semibold">Program Selection</h2>
            </div>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="label">Student Type *</label>
                    <select name="StudentTypeID" class="input-field" required>
                        <option value="">Select...</option>
                        <?php foreach($studentTypes as $type): ?>
                            <option value="<?= $type['StudentTypeID'] ?>"><?= htmlspecialchars($type['TypeName']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div>
                    <label class="label">Program Applied For *</label>
                    <select name="ProgramID" class="input-field" required>
                        <option value="">Select...</option>
                        <?php foreach($programs as $prog): ?>
                            <option value="<?= $prog['ProgramID'] ?>"><?= htmlspecialchars($prog['ProgramName']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>
        </div>

        <!-- Section: Guardian Info -->
        <div>
            <div class="flex items-center gap-2 mb-4 pb-2 border-b border-surface-container">
                <i class="fa-solid fa-people-roof text-primary"></i>
                <h2 class="text-lg font-semibold">Emergency Contact</h2>
            </div>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="label">Guardian Name *</label>
                    <input type="text" name="GuardianName" class="input-field" required>
                </div>
                <div>
                    <label class="label">Guardian Contact No. *</label>
                    <input type="text" name="GuardianContactNo" class="input-field" required>
                </div>
            </div>
        </div>

        <div class="pt-4 flex justify-between items-center border-t border-surface-container">
            <a href="../index.php" class="text-tertiary font-semibold hover:text-primary transition-colors flex items-center gap-1">
                <i class="fa-solid fa-arrow-left"></i> Back to Home
            </a>
            <button type="submit" class="bg-primary hover:bg-primary-container text-white px-8 py-3 rounded-lg font-bold shadow-lg hover:shadow-xl transition-all transform hover:-translate-y-0.5 flex items-center gap-2">
                Submit Application <i class="fa-solid fa-paper-plane"></i>
            </button>
        </div>

    </form>
</div>

</body>
</html>
