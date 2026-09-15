<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once '../config/db.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Portal Access & Central Authentication - SEAIT Enrolment Platform</title>
  <!-- Instant Dark Mode Initialization -->
  <script>
    (function() {
      const theme = localStorage.getItem('theme');
      if (theme === 'dark' || (!theme && window.matchMedia('(prefers-color-scheme: dark)').matches)) {
        document.documentElement.classList.add('dark');
      } else {
        document.documentElement.classList.remove('dark');
      }
    })();
  </script>
  <!-- Font Awesome 6 Icons (Local & CDN Fallback) -->
  <link rel="stylesheet" href="../assets/fontawesome/css/all.min.css"/>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css"/>
  <script src="../assets/tailwindcss.js"></script>
  <style>
    * {
      font-family: Arial, Helvetica, sans-serif !important;
    }
    html.dark {
      color-scheme: dark;
    }
    html.dark body {
      background-color: #0b0f19 !important;
      color: #f8fafc !important;
    }
    html.dark header,
    html.dark footer {
      background-color: #111827 !important;
      border-color: #1f293d !important;
    }
    html.dark .bg-white {
      background-color: #111827 !important;
    }
    html.dark .bg-slate-50 {
      background-color: #172033 !important;
    }
    html.dark .bg-slate-100 {
      background-color: #1e293b !important;
    }
    html.dark .text-slate-900,
    html.dark .text-slate-800 {
      color: #f8fafc !important;
    }
    html.dark .text-slate-700,
    html.dark .text-slate-600,
    html.dark label {
      color: #e2e8f0 !important;
    }
    html.dark .text-slate-500,
    html.dark .text-slate-400 {
      color: #cbd5e1 !important;
    }
    html.dark .border-slate-100,
    html.dark .border-slate-200 {
      border-color: #1f293d !important;
    }
    html.dark input {
      background-color: #172033 !important;
      color: #f8fafc !important;
      border-color: #334155 !important;
    }
    html.dark input::placeholder {
      color: #94a3b8 !important;
    }

    /* High contrast indicators & badges */
    html.dark .bg-orange-50,
    html.dark .bg-orange-50\/70,
    html.dark .bg-orange-100 {
      background-color: rgba(249, 115, 22, 0.2) !important;
      border-color: rgba(249, 115, 22, 0.4) !important;
    }
    html.dark .text-orange-700,
    html.dark .text-orange-950 {
      color: #fdba74 !important;
    }
    html.dark .bg-sky-50,
    html.dark .bg-sky-50\/70 {
      background-color: rgba(14, 165, 233, 0.2) !important;
      border-color: rgba(14, 165, 233, 0.4) !important;
    }
    html.dark .text-sky-700 {
      color: #7dd3fc !important;
    }
    html.dark .bg-red-50 {
      background-color: rgba(239, 68, 68, 0.2) !important;
      border-color: rgba(239, 68, 68, 0.4) !important;
    }
    html.dark .text-red-700 {
      color: #fca5a5 !important;
    }
  </style>
  <script>
    function disableButton(form) {
      const btn = form.querySelector('button[type="submit"]');
      const span = btn.querySelector('span');
      if (span) span.innerText = 'Authenticating...';
      btn.disabled = true;
      btn.classList.add('opacity-50', 'cursor-not-allowed');
      return true;
    }
  </script>
</head>
<body class="bg-[#f8fafc] text-slate-800 antialiased min-h-screen flex flex-col justify-between transition-colors duration-200">

  <!-- TOP BRANDING BAR -->
  <header class="h-16 bg-white border-b border-slate-200 px-8 flex items-center justify-between sticky top-0 z-20">
    <div class="flex items-center gap-3">
      <img src="../assets/seait.png" alt="SEAIT Official Seal" class="w-10 h-10 object-contain shrink-0">
      <div>
        <div class="flex items-center gap-1.5">
          <span class="font-bold text-base tracking-tight text-slate-900">SEAIT</span>
          <span class="text-xs font-bold px-1.5 py-0.5 rounded bg-orange-100 text-orange-700 uppercase tracking-wider">EduTech</span>
        </div>
        <div class="text-[10px] text-slate-500 font-medium tracking-tight">CENTRAL IDENTITY & ACCESS GATEWAY</div>
      </div>
    </div>

    <div class="flex items-center gap-4 text-xs font-medium text-slate-600">
      <span class="hidden sm:inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full bg-slate-100 text-slate-700">
        <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
        Auth Node: Online (Port 443)
      </span>
      <a href="javascript:void(0)" onclick="alert('Feature coming soon.')" class="hover:text-orange-600 transition">Admissions Helpdesk</a>
      <a href="javascript:void(0)" onclick="alert('Feature coming soon.')" class="hover:text-orange-600 transition">Academic Calendar</a>
      <a href="../index.php" class="px-3 py-1.5 rounded-lg border border-slate-200 dark:border-slate-700 hover:bg-slate-50 dark:hover:bg-slate-800 text-slate-700 dark:text-slate-300 font-semibold transition">Back to Main Portal</a>
      <button id="themeToggleBtn" onclick="toggleDarkMode()" class="p-1.5 rounded-lg text-slate-500 hover:text-slate-800 dark:hover:text-slate-200 hover:bg-slate-100 dark:hover:bg-slate-800 transition-colors flex items-center justify-center" type="button" title="Toggle Dark / Light Mode">
        <i id="themeToggleIcon" class="fa-solid fa-moon text-[20px]"></i>
      </button>
    </div>
  </header>

  <!-- MAIN AUTHENTICATION CONTAINER -->
  <main class="flex-1 flex items-center justify-center p-6 sm:p-10 my-4">
    <div class="max-w-4xl w-full grid grid-cols-1 md:grid-cols-12 bg-white border border-slate-200 rounded-3xl shadow-sm overflow-hidden">
      
      <!-- LEFT BRAND HERO PANEL (5 cols) -->
      <div class="md:col-span-5 bg-gradient-to-br from-[#ea580c] to-orange-700 p-8 sm:p-10 text-white flex flex-col justify-between relative overflow-hidden">
        <!-- Abstract subtle circles -->
        <div class="absolute -top-12 -left-12 w-48 h-48 bg-white/10 rounded-full blur-xl pointer-events-none"></div>
        <div class="absolute -bottom-12 -right-12 w-48 h-48 bg-black/10 rounded-full blur-xl pointer-events-none"></div>

        <div class="relative z-10">
          <div class="inline-flex items-center gap-2 px-2.5 py-1 bg-white/20 rounded-full text-[11px] font-semibold tracking-wide uppercase mb-6 backdrop-blur-xs">
            <span class="w-1.5 h-1.5 rounded-full bg-emerald-300"></span>
            A.Y. 2024–2025 Active
          </div>

          <h2 class="text-2xl sm:text-3xl font-bold leading-tight">
            South East Asian Institute of Technology
          </h2>
          <p class="text-xs text-orange-100 mt-3 leading-relaxed">
            Welcome to the unified digital gateway for prospective students, continuing learners, faculty evaluators, and university registrars.
          </p>
        </div>

        <div class="relative z-10 my-8 space-y-3">
          <div class="flex items-center gap-3 p-3 rounded-xl bg-white/10 border border-white/15 backdrop-blur-xs">
            <div class="w-8 h-8 rounded-lg bg-white/20 flex items-center justify-center text-white shrink-0">
              <i class="fa-solid fa-database text-xs"></i>
            </div>
            <div>
              <div class="text-xs font-bold">Relational Schema Secure</div>
              <div class="text-[10px] text-orange-100">Bcrypt password hashes mapped to `login`</div>
            </div>
          </div>

          <div class="flex items-center gap-3 p-3 rounded-xl bg-white/10 border border-white/15 backdrop-blur-xs">
            <div class="w-8 h-8 rounded-lg bg-white/20 flex items-center justify-center text-white shrink-0">
              <i class="fa-solid fa-users-gear text-xs"></i>
            </div>
            <div>
              <div class="text-xs font-bold">Role-Based Access Control</div>
              <div class="text-[10px] text-orange-100">Registrar, Accounting, Clinic, Admin &amp; Students</div>
            </div>
          </div>
        </div>

        <div class="relative z-10 text-[11px] text-orange-200">
          Need technical assistance? Contact Registrar IT Support at <span class="underline text-white">support@seait.edu.ph</span>
        </div>
      </div>

      <!-- RIGHT SIGN-IN FORM (7 cols) -->
      <div class="md:col-span-7 p-8 sm:p-10 flex flex-col justify-between">
        <div>
          <!-- Role Switcher Tabs (Mapped to DB enum UserType: 'Student' vs 'Staff') -->
          <div class="flex items-center justify-between pb-4 border-b border-slate-100">
            <div>
              <h3 class="text-xl font-bold text-slate-900">Sign In to Platform</h3>
              <p class="text-xs text-slate-500 mt-0.5">Choose your account role to continue</p>
            </div>
            <span class="text-[11px] font-mono text-slate-400 font-semibold">`login` table</span>
          </div>

          <div class="grid grid-cols-2 gap-2 p-1 bg-slate-100 rounded-xl my-6">
            <button type="button" id="btn_staff" onclick="setRole('Staff')" class="py-2.5 px-3 rounded-lg text-xs font-bold flex items-center justify-center gap-2 bg-[#ea580c] text-white shadow-xs transition">
              <i class="fa-solid fa-user-shield text-[14px]"></i>
              <span>Registrar / Staff</span>
            </button>
            <button type="button" id="btn_student" onclick="setRole('Student')" class="py-2.5 px-3 rounded-lg text-xs font-semibold flex items-center justify-center gap-2 text-slate-600 hover:text-slate-900 hover:bg-slate-200/60 transition">
              <i class="fa-solid fa-graduation-cap text-[14px]"></i>
              <span>Student Enrollee</span>
            </button>
          </div>

          <script>
            function setRole(role) {
              document.getElementById('user_type_input').value = role;
              const btnStaff = document.getElementById('btn_staff');
              const btnStudent = document.getElementById('btn_student');
              const scopeText = document.getElementById('scope_text');
              const roleIdText = document.getElementById('role_id_text');
              const indicator = document.getElementById('role_scope_indicator');
              
              if (role === 'Staff') {
                btnStaff.className = 'py-2.5 px-3 rounded-lg text-xs font-bold flex items-center justify-center gap-2 bg-[#ea580c] text-white shadow-xs transition';
                btnStudent.className = 'py-2.5 px-3 rounded-lg text-xs font-semibold flex items-center justify-center gap-2 text-slate-600 hover:text-slate-900 hover:bg-slate-200/60 transition';
                scopeText.innerText = 'Staff Role (Registrar Operations)';
                roleIdText.innerText = 'RoleID: Registrar';
                indicator.className = 'p-3 bg-orange-50/70 border border-orange-200/70 rounded-xl flex items-center justify-between text-xs';
                roleIdText.className = 'text-[10px] font-mono text-orange-700 bg-white px-2 py-0.5 rounded border border-orange-200';
                indicator.querySelector('span.w-2').className = 'w-2 h-2 rounded-full bg-orange-500';
              } else {
                btnStudent.className = 'py-2.5 px-3 rounded-lg text-xs font-bold flex items-center justify-center gap-2 bg-[#0284c7] text-white shadow-xs transition';
                btnStaff.className = 'py-2.5 px-3 rounded-lg text-xs font-semibold flex items-center justify-center gap-2 text-slate-600 hover:text-slate-900 hover:bg-slate-200/60 transition';
                scopeText.innerText = 'Student Role (Enrollee)';
                roleIdText.innerText = 'RoleID: Student';
                indicator.className = 'p-3 bg-sky-50/70 border border-sky-200/70 rounded-xl flex items-center justify-between text-xs';
                roleIdText.className = 'text-[10px] font-mono text-sky-700 bg-white px-2 py-0.5 rounded border border-sky-200';
                indicator.querySelector('span.w-2').className = 'w-2 h-2 rounded-full bg-sky-500';
              }
            }

            function quickFill(user, pass, role) {
              setRole(role);
              const uInput = document.querySelector('input[name="username"]');
              const pInput = document.getElementById('password_input');
              if (uInput) {
                uInput.value = user;
                uInput.classList.add('ring-2', 'ring-orange-400');
                setTimeout(() => uInput.classList.remove('ring-2', 'ring-orange-400'), 800);
              }
              if (pInput) {
                pInput.value = pass;
                pInput.classList.add('ring-2', 'ring-orange-400');
                setTimeout(() => pInput.classList.remove('ring-2', 'ring-orange-400'), 800);
              }
            }
          </script>

          <!-- Quick Fill Demo Accounts -->
          <div class="mb-5 p-3 rounded-2xl bg-slate-50 border border-slate-200/80">
            <div class="flex items-center justify-between mb-2">
              <span class="text-[11px] font-bold text-slate-700 flex items-center gap-1.5">
                <i class="fa-solid fa-key text-orange-500 text-xs"></i>
                <span>Quick Fill Test Accounts</span>
              </span>
              <span class="text-[10px] text-slate-400 font-medium">Click any to auto-fill</span>
            </div>
            <div class="flex flex-wrap gap-1.5">
              <button type="button" onclick="quickFill('admin', 'admin123', 'Staff')" class="px-2.5 py-1 rounded-lg text-[11px] font-bold bg-purple-50 text-purple-700 border border-purple-200 hover:bg-purple-100 transition flex items-center gap-1" title="System Administrator">
                <i class="fa-solid fa-crown text-[10px]"></i> Admin
              </button>
              <button type="button" onclick="quickFill('staff1', 'password123', 'Staff')" class="px-2.5 py-1 rounded-lg text-[11px] font-bold bg-orange-50 text-orange-700 border border-orange-200 hover:bg-orange-100 transition flex items-center gap-1" title="Registrar Staff">
                <i class="fa-solid fa-id-card-clip text-[10px]"></i> Registrar
              </button>
              <button type="button" onclick="quickFill('accounting', 'password123', 'Staff')" class="px-2.5 py-1 rounded-lg text-[11px] font-bold bg-emerald-50 text-emerald-700 border border-emerald-200 hover:bg-emerald-100 transition flex items-center gap-1" title="Accounting Cashier">
                <i class="fa-solid fa-receipt text-[10px]"></i> Accounting
              </button>
              <button type="button" onclick="quickFill('clinic', 'password123', 'Staff')" class="px-2.5 py-1 rounded-lg text-[11px] font-bold bg-rose-50 text-rose-700 border border-rose-200 hover:bg-rose-100 transition flex items-center gap-1" title="Campus Clinic Staff">
                <i class="fa-solid fa-heart-pulse text-[10px]"></i> Clinic
              </button>
              <button type="button" onclick="quickFill('student', 'student123', 'Student')" class="px-2.5 py-1 rounded-lg text-[11px] font-bold bg-sky-50 text-sky-700 border border-sky-200 hover:bg-sky-100 transition flex items-center gap-1" title="Student Enrollee">
                <i class="fa-solid fa-graduation-cap text-[10px]"></i> Student
              </button>
            </div>
          </div>

          <!-- LOGIN FORM -->
          <form action="../controllers/auth_controller.php" method="POST" class="space-y-4" onsubmit="return disableButton(this)">
            <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token'] ?? ''); ?>">
            <input type="hidden" name="user_type" id="user_type_input" value="Staff">
            <div>
              <label class="block text-xs font-bold text-slate-700 mb-1.5 flex justify-between">
                <span>Username or Institutional ID</span>
                <span class="text-slate-400 font-normal text-[10px] font-mono">Mapped: Username</span>
              </label>
              <div class="relative flex items-center">
                <span class="absolute left-3 text-slate-400">
                  <i class="fa-solid fa-user text-xs"></i>
                </span>
                <input type="text" name="username" required class="w-full pl-9 pr-3 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs font-medium text-slate-800 placeholder:text-slate-400 focus:outline-none focus:border-orange-500 focus:bg-white transition" placeholder="e.g. admin, staff1, or student">
              </div>
            </div>

            <div>
              <div class="flex justify-between items-center mb-1.5">
                <label class="text-xs font-bold text-slate-700 flex items-center gap-1.5">
                  <span>Password</span>
                  <span class="text-slate-400 font-normal text-[10px] font-mono">Mapped: PasswordHash</span>
                </label>
                <a href="javascript:void(0)" onclick="alert('Feature coming soon.')" class="text-[11px] text-[#ea580c] hover:underline font-semibold">Forgot Password?</a>
              </div>
              <div class="relative flex items-center">
                <span class="absolute left-3 text-slate-400">
                  <i class="fa-solid fa-lock text-xs"></i>
                </span>
                <input type="password" id="password_input" name="password" required class="w-full pl-9 pr-10 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs font-medium text-slate-800 focus:outline-none focus:border-orange-500 focus:bg-white transition">
                <button type="button" onclick="const p = document.getElementById('password_input'); p.type = p.type === 'password' ? 'text' : 'password';" class="absolute right-3 text-slate-400 hover:text-slate-600">
                  <i class="fa-solid fa-eye text-xs"></i>
                </button>
              </div>
            </div>

            <!-- Role Scope Indicator -->
            <div id="role_scope_indicator" class="p-3 bg-orange-50/70 border border-orange-200/70 rounded-xl flex items-center justify-between text-xs">
              <div class="flex items-center gap-2 text-slate-700">
                <span class="w-2 h-2 rounded-full bg-orange-500"></span>
                <span>Active Scope: <strong class="text-orange-950" id="scope_text">Staff Role (Registrar Operations)</strong></span>
              </div>
              <span class="text-[10px] font-mono text-orange-700 bg-white px-2 py-0.5 rounded border border-orange-200" id="role_id_text">RoleID: Registrar</span>
            </div>
            
            <?php if (isset($_GET['error'])): ?>
              <div class="p-3 bg-red-50 text-red-700 text-xs rounded-xl border border-red-200 flex items-center gap-2">
                <i class="fa-solid fa-circle-exclamation text-red-500"></i>
                <span><?= htmlspecialchars($_GET['error']) ?></span>
              </div>
            <?php endif; ?>

            <div class="flex items-center justify-between pt-1">
              <label class="flex items-center gap-2 cursor-pointer text-xs text-slate-600 select-none">
                <input type="checkbox" checked class="rounded border-slate-300 text-orange-600 focus:ring-orange-500 w-4 h-4">
                <span>Remember this terminal (12 hours)</span>
              </label>
            </div>

            <button type="submit" class="w-full py-3 bg-[#ea580c] hover:bg-orange-700 text-white rounded-xl text-xs font-bold flex items-center justify-center gap-2 shadow-sm shadow-orange-500/20 transition mt-2">
              <span>Authenticate & Access Command Center</span>
              <i class="fa-solid fa-arrow-right-to-bracket text-[13px]"></i>
            </button>
          </form>
        </div>

        <!-- Quick Switch for New Enrollees -->
        <div class="pt-6 border-t border-slate-100 mt-6 text-center text-xs text-slate-500">
          Incoming Freshman or Transferee without login credentials?
          <a href="admission.php" class="font-bold text-[#ea580c] hover:underline block sm:inline sm:ml-1">Start New Admission Application</a>
        </div>
      </div>
    </div>
  </main>

  <!-- PERSISTENT SYSTEM FOOTER -->
  <footer class="h-10 bg-white border-t border-slate-200 px-8 flex items-center justify-between text-[11px] text-slate-500 shrink-0">
    <div class="flex items-center gap-6">
      <div class="flex items-center gap-2">
        <span class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></span>
        <span class="font-medium text-slate-700">All Systems Operational</span>
      </div>
      <span>•</span>
      <span>DB Connected (22 Tables)</span>
      <span>•</span>
      <span>Central Login Gateway</span>
    </div>
    <div class="flex items-center gap-2">
      <i class="fa-solid fa-shield-halved text-slate-400 text-xs"></i>
      <span>256-Bit SSL Encrypted Campus Node</span>
    </div>
  </footer>

  <script>
    function updateThemeIcon() {
      const icon = document.getElementById('themeToggleIcon');
      if (!icon) return;
      const isDark = document.documentElement.classList.contains('dark');
      icon.className = isDark ? 'fa-solid fa-sun text-[18px] text-amber-400' : 'fa-solid fa-moon text-[18px]';
      icon.title = isDark ? 'Switch to Light Mode' : 'Switch to Dark Mode';
    }

    function toggleDarkMode() {
      const isDark = document.documentElement.classList.toggle('dark');
      localStorage.setItem('theme', isDark ? 'dark' : 'light');
      updateThemeIcon();
    }

    document.addEventListener('DOMContentLoaded', updateThemeIcon);
    updateThemeIcon();
  </script>
</body>
</html>