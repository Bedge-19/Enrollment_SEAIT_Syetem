<?php
$ref = $_GET['ref'] ?? 'UNKNOWN';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8"/>
    <meta content="width=device-width, initial-scale=1.0" name="viewport"/>
    <title>Application Submitted - SEAIT Enrolment Platform</title>
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@20..48,100..700,0..1,-50..200" rel="stylesheet"/>
    <script src="../assets/tailwindcss.js"></script>
    <script>
        tailwind.config={darkMode:"class",theme:{extend:{"colors":{"surface-container-lowest":"#ffffff","on-surface":"#0f172a","tertiary":"#64748b","primary":"#f97316","secondary":"#fb923c","background":"#ffffff","surface-container-low":"#f8fafc","surface-container":"#f1f5f9","primary-container":"#ffedd5","on-primary-container":"#c2410c","on-primary":"#ffffff","error":"#ef4444","on-error":"#ffffff"},"borderRadius":{"DEFAULT":"0.25rem","lg":"0.5rem","xl":"0.75rem","full":"9999px"}}}}
    </script>
    <style>
        body { background-image: radial-gradient(circle at top right, #eff4ff, #f8f9ff); background-attachment: fixed; }
        .glass-panel { background: rgba(255, 255, 255, 0.85); backdrop-filter: blur(12px); border: 1px solid rgba(255, 255, 255, 0.3); }
    </style>
</head>
<body class="font-sans antialiased text-on-surface min-h-screen flex flex-col items-center justify-center py-10 px-6">

<div class="max-w-xl w-full glass-panel p-10 rounded-3xl shadow-2xl text-center space-y-6 border-t-8 border-t-primary">
    <div class="inline-flex items-center justify-center w-24 h-24 rounded-full bg-green-100 text-green-600 mb-2">
        <i class="fa-solid fa-circle-check text-6xl"></i>
    </div>
    
    <h1 class="text-3xl font-bold tracking-tight text-on-surface">Application Submitted!</h1>
    
    <p class="text-tertiary text-lg">
        Thank you for applying to South East Asian Institute of Technology. Your application has been successfully submitted and is now pending review by the Registrar.
    </p>

    <div class="bg-surface-container p-6 rounded-2xl my-6 inline-block w-full">
        <p class="text-sm font-semibold text-tertiary uppercase tracking-wider mb-1">Your Reference Number</p>
        <p class="text-3xl font-bold text-primary tracking-widest"><?= htmlspecialchars($ref) ?></p>
    </div>
    
    <p class="text-sm text-tertiary">
        Please save this reference number. You will receive an email once your application has been processed, along with instructions to log in to your Student Portal.
    </p>

    <div class="pt-6">
        <a href="../index.php" class="inline-flex items-center gap-2 bg-primary hover:bg-primary-container text-white px-8 py-3 rounded-xl font-bold shadow-lg hover:shadow-xl transition-all">
            <i class="fa-solid fa-house"></i> Return to Home
        </a>
    </div>
</div>

</body>
</html>
