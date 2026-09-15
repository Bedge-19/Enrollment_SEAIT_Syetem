<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8"/>
  <meta content="width=device-width, initial-scale=1.0" name="viewport"/>
  <title>SEAIT Introllment | Centralized Enrolment & Campus Management Platform</title>
  <link rel="icon" type="image/png" href="assets/seait.png"/>

  <!-- Instant Dark Mode Initialization (Prevents FOUC) -->
  <script>
    (function() {
      const savedTheme = localStorage.getItem('theme');
      if (savedTheme === 'dark' || (!savedTheme && window.matchMedia('(prefers-color-scheme: dark)').matches)) {
        document.documentElement.classList.add('dark');
      } else {
        document.documentElement.classList.remove('dark');
      }
    })();
  </script>

  <!-- Font Awesome 6 Icons (Local & CDN Fallback) -->
  <link rel="stylesheet" href="assets/fontawesome/css/all.min.css"/>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css"/>
  <link href="https://fonts.googleapis.com/css2?family=Hanken+Grotesk:wght@300;400;500;600;700;800;900&family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet"/>

  <!-- Tailwind CSS Engine -->
  <script src="assets/tailwindcss_with_plugins.js"></script>
  <script>
    tailwind.config = {
      darkMode: 'class',
      theme: {
        extend: {
          colors: {
            primary: {
              50: '#fff7ed',
              100: '#ffedd5',
              200: '#fed7aa',
              300: '#fdba74',
              400: '#fb923c',
              500: '#f97316',
              600: '#ea580c',
              700: '#c2410c',
              800: '#9a3412',
              900: '#7c2d12',
              DEFAULT: '#ea580c'
            },
            brand: {
              peach: '#FFF5ED',
              cream: '#FFF0E5'
            }
          },
          fontFamily: {
            sans: ['Inter', 'system-ui', '-apple-system', 'sans-serif'],
            heading: ['Hanken Grotesk', 'Inter', 'sans-serif']
          }
        }
      }
    }
  </script>

  <style>
    html {
      scroll-behavior: smooth;
    }
    body {
      font-family: 'Inter', system-ui, -apple-system, sans-serif;
    }
    h1, h2, h3, h4, h5, h6, .font-heading {
      font-family: 'Hanken Grotesk', 'Inter', sans-serif;
    }

    /* Ambient card elevation & transitions */
    .system-card {
      transition: all 0.25s cubic-bezier(0.16, 1, 0.3, 1);
    }
    .system-card:hover {
      transform: translateY(-4px);
      box-shadow: 0 20px 30px -10px rgba(234, 88, 12, 0.12), 0 8px 15px -6px rgba(0, 0, 0, 0.05);
    }

    /* Dark mode global overrides */
    html.dark {
      color-scheme: dark;
    }
    html.dark body {
      background-color: #0b0f19 !important;
      color: #f8fafc !important;
    }
    html.dark header {
      background-color: rgba(17, 24, 39, 0.95) !important;
      border-color: #1f293d !important;
    }
    html.dark .bg-white {
      background-color: #111827 !important;
    }
    html.dark .bg-slate-50,
    html.dark .bg-slate-100 {
      background-color: #172033 !important;
    }
    html.dark .bg-brand-peach,
    html.dark .bg-brand-cream,
    html.dark .bg-orange-50 {
      background-color: #131d2e !important;
    }
    html.dark .border-slate-100,
    html.dark .border-slate-200,
    html.dark .border-orange-100,
    html.dark .border-orange-200 {
      border-color: #1f293d !important;
    }
    html.dark .text-slate-900,
    html.dark .text-slate-800 {
      color: #f8fafc !important;
    }
    html.dark .text-slate-700,
    html.dark .text-slate-600 {
      color: #e2e8f0 !important;
    }
    html.dark .text-slate-500,
    html.dark .text-slate-400 {
      color: #cbd5e1 !important;
    }
    html.dark .system-card:hover {
      box-shadow: 0 20px 35px -10px rgba(0, 0, 0, 0.65) !important;
    }
  </style>
</head>
<body class="bg-white text-slate-800 antialiased selection:bg-orange-500 selection:text-white transition-colors duration-200">

  <!-- ========================================================================= -->
  <!-- NAVIGATION HEADER -->
  <!-- ========================================================================= -->
  <header class="w-full bg-white/95 backdrop-blur-md sticky top-0 z-50 border-b border-slate-200/80 transition-colors">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 h-20 flex items-center justify-between">
      
      <!-- Brand Logo Container with Official Seal -->
      <a class="flex items-center gap-3.5 group" href="#home" title="SEAIT Enrolment Platform">
        <img src="assets/seait.png" alt="SEAIT Official University Seal" class="w-11 h-11 object-contain shrink-0 drop-shadow-sm group-hover:scale-105 transition-transform"/>
        <div class="flex flex-col">
          <div class="flex items-center gap-1.5">
            <span class="text-xl font-black tracking-tight text-slate-900 leading-none">SEAIT</span>
            <span class="text-[10px] font-bold px-1.5 py-0.5 rounded bg-orange-100 text-orange-700 uppercase tracking-wider font-mono">Introllment</span>
          </div>
          <span class="text-[10px] uppercase font-bold text-slate-500 tracking-wider mt-1">Integrated Enrolment Platform</span>
        </div>
      </a>

      <!-- Desktop Navigation Menu Links -->
      <nav aria-label="Main Navigation" class="hidden lg:flex items-center gap-8 text-sm font-semibold text-slate-600">
        <a class="hover:text-orange-600 transition-colors" href="#overview">Overview</a>
        <a class="hover:text-orange-600 transition-colors" href="#modules">5 Core Modules</a>
        <a class="hover:text-orange-600 transition-colors" href="#lifecycle">Enrollment Pipeline</a>
        <a class="hover:text-orange-600 transition-colors" href="#architecture">Architecture</a>
        <a class="hover:text-orange-600 transition-colors" href="#portals">Role Portals</a>
      </nav>

      <!-- Right Action CTA & Theme Toggle -->
      <div class="flex items-center gap-3">
        <!-- Theme Toggle Button -->
        <button id="themeToggleBtn" onclick="toggleDarkMode()" class="p-2 rounded-xl text-slate-500 hover:text-slate-900 dark:hover:text-white hover:bg-slate-100 dark:hover:bg-slate-800 transition-colors flex items-center justify-center border border-slate-200/70 dark:border-slate-700" type="button" title="Toggle Dark / Light Mode">
          <i id="themeToggleIcon" class="fa-solid fa-moon text-[20px]"></i>
        </button>

        <!-- Apply CTA -->
        <a class="hidden sm:inline-flex items-center gap-1.5 px-4 py-2.5 rounded-xl text-xs font-bold border border-orange-200 text-orange-700 bg-orange-50/70 hover:bg-orange-100 transition-colors" href="views/admission.php">
          <i class="fa-solid fa-file-pen text-[16px]"></i>
          <span>Online Admission</span>
        </a>

        <!-- Portal Sign In CTA -->
        <a class="inline-flex items-center gap-2 px-5 py-2.5 rounded-xl text-xs sm:text-sm font-bold bg-orange-600 hover:bg-orange-700 text-white shadow-md shadow-orange-500/25 hover:shadow-lg hover:shadow-orange-500/35 transition-all" href="views/login.php">
          <i class="fa-solid fa-right-to-bracket text-[18px]"></i>
          <span>Sign In to Portal</span>
        </a>
      </div>
    </div>
  </header>

  <main>
    <!-- ========================================================================= -->
    <!-- HERO BANNER SECTION (SYSTEM SHOWCASE INSTEAD OF PLACEHOLDER GRAPHIC) -->
    <!-- ========================================================================= -->
    <section class="relative pt-12 pb-16 lg:pt-16 lg:pb-24 overflow-hidden border-b border-slate-200/60" id="home">
      <!-- Ambient Gradient Backdrop -->
      <div class="absolute inset-0 bg-gradient-to-b from-orange-50/40 via-white to-white dark:from-[#131d2e] dark:via-[#0b0f19] dark:to-[#0b0f19] -z-10"></div>
      
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-12 items-center">
          
          <!-- Hero Left Column: Headings & System Mission -->
          <div class="lg:col-span-6 flex flex-col items-start text-left z-10">
            
            <!-- Academic Term Badge -->
            <div class="inline-flex items-center gap-2 px-3.5 py-1.5 rounded-full bg-orange-100 text-orange-800 dark:bg-orange-950/60 dark:text-orange-300 text-xs font-bold uppercase tracking-wider mb-5 border border-orange-200/80 dark:border-orange-800/40">
              <span class="w-2 h-2 rounded-full bg-orange-600 animate-ping"></span>
              <span>A.Y. 2026–2027 • 1st Semester Enrolment Active</span>
            </div>

            <h1 class="text-4xl sm:text-5xl lg:text-5xl font-extrabold text-slate-900 tracking-tight leading-[1.15] mb-5 font-heading">
              Centralized Academic Enrolment &amp; <br class="hidden sm:inline"/>
              <span class="text-orange-600">Multi-Role Campus</span> Management
            </h1>

            <p class="text-base sm:text-lg text-slate-600 dark:text-slate-300 max-w-xl mb-8 leading-relaxed">
              An interconnected digital university operations ecosystem unifying <strong>Registrar</strong> admissions &amp; evaluation, <strong>Clinic</strong> medical clearance, <strong>Accounting</strong> POS cashiering, and <strong>Student</strong> self-service into one live 7-gate pipeline.
            </p>

            <!-- Action Buttons -->
            <div class="flex flex-wrap items-center gap-3.5 mb-10">
              <a class="px-6 py-3.5 rounded-xl bg-orange-600 hover:bg-orange-700 text-white font-bold text-sm shadow-lg shadow-orange-500/25 hover:shadow-orange-500/40 transition-all flex items-center gap-2 transform hover:-translate-y-0.5" href="views/login.php">
                <i class="fa-solid fa-gauge-high text-[20px]"></i>
                <span>Open Operations Portal</span>
              </a>
              <a class="px-6 py-3.5 rounded-xl border border-slate-300 dark:border-slate-700 hover:border-orange-500 bg-white dark:bg-slate-800 text-slate-700 dark:text-slate-200 hover:text-orange-600 font-semibold text-sm transition-all shadow-sm flex items-center gap-2" href="views/admission.php">
                <i class="fa-solid fa-id-card text-[20px]"></i>
                <span>Freshmen Admission</span>
              </a>
              <a class="px-5 py-3.5 rounded-xl text-slate-600 dark:text-slate-400 hover:text-orange-600 font-semibold text-sm transition-colors flex items-center gap-1" href="#modules">
                <span>Explore Functions</span>
                <i class="fa-solid fa-arrow-down text-[16px]"></i>
              </a>
            </div>

            <!-- Stats Bar -->
            <div class="pt-6 border-t border-slate-200/80 dark:border-slate-800 grid grid-cols-3 gap-6 w-full max-w-lg">
              <div>
                <div class="text-2xl font-black text-slate-900 font-heading">5 Offices</div>
                <div class="text-xs text-slate-500 font-medium mt-0.5">Admin, Registrar, Clinic, POS, Student</div>
              </div>
              <div>
                <div class="text-2xl font-black text-slate-900 font-heading">7 Gates</div>
                <div class="text-xs text-slate-500 font-medium mt-0.5">End-to-End Verification Pipeline</div>
              </div>
              <div>
                <div class="text-2xl font-black text-slate-900 font-heading">100% Digital</div>
                <div class="text-xs text-slate-500 font-medium mt-0.5">Instant COR &amp; Validation Passes</div>
              </div>
            </div>

          </div>

          <!-- Hero Right Column: REAL SYSTEM UI PREVIEW (REPLACING CARTOON GRAPHIC) -->
          <div class="lg:col-span-6 relative flex justify-center items-center" data-purpose="system-live-preview">
            
            <!-- Futuristic Multi-Card Command Dashboard Preview -->
            <div class="w-full max-w-lg bg-white dark:bg-[#111827] border border-slate-200 dark:border-slate-800 rounded-3xl p-6 shadow-2xl shadow-orange-500/10 relative overflow-hidden">
              
              <!-- Window Top Action Bar -->
              <div class="flex items-center justify-between pb-4 border-b border-slate-100 dark:border-slate-800">
                <div class="flex items-center gap-2">
                  <span class="w-3 h-3 rounded-full bg-red-500/80"></span>
                  <span class="w-3 h-3 rounded-full bg-amber-500/80"></span>
                  <span class="w-3 h-3 rounded-full bg-emerald-500/80"></span>
                  <span class="text-xs font-mono font-bold text-slate-400 dark:text-slate-500 ml-2">introllment://live-gateway</span>
                </div>
                <div class="flex items-center gap-1.5 px-2.5 py-1 rounded-full bg-emerald-50 dark:bg-emerald-950/50 border border-emerald-200 dark:border-emerald-800/50 text-[11px] font-bold text-emerald-700 dark:text-emerald-300 font-mono">
                  <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 animate-pulse"></span>
                  <span>ONLINE (22 Tables)</span>
                </div>
              </div>

              <!-- Student Profile Header Strip inside Mockup -->
              <div class="mt-4 p-4 rounded-2xl bg-slate-50 dark:bg-[#172033] border border-slate-200/70 dark:border-slate-800 flex items-center justify-between">
                <div class="flex items-center gap-3">
                  <div class="w-11 h-11 rounded-xl bg-orange-600 text-white flex items-center justify-center font-bold text-base shadow-sm">
                    2026
                  </div>
                  <div>
                    <div class="text-sm font-bold text-slate-900 leading-snug">Maria Elena Santos</div>
                    <div class="text-xs text-slate-500 font-mono">StudentNo: 2026-00004 • BSIT Year 1</div>
                  </div>
                </div>
                <span class="px-2.5 py-1 rounded-full text-[11px] font-bold bg-emerald-100 text-emerald-800 border border-emerald-300">
                  Officially Enrolled
                </span>
              </div>

              <!-- Live 7-Stage Pipeline Mockup inside Card -->
              <div class="mt-4 space-y-2.5">
                <div class="text-xs font-bold uppercase tracking-wider text-slate-400 font-heading">
                  Active Enrollment Verification Gates
                </div>

                <!-- Gate 1: Admission -->
                <div class="p-2.5 rounded-xl bg-white dark:bg-slate-800/80 border border-slate-100 dark:border-slate-800 flex items-center justify-between text-xs">
                  <div class="flex items-center gap-2.5">
                    <span class="w-6 h-6 rounded-lg bg-emerald-100 text-emerald-700 flex items-center justify-center font-bold text-xs">✓</span>
                    <span class="font-semibold text-slate-700 dark:text-slate-200">1. Admissions Application &amp; Requirements</span>
                  </div>
                  <span class="text-[11px] font-mono font-bold text-emerald-600">Approved</span>
                </div>

                <!-- Gate 2: Clinic -->
                <div class="p-2.5 rounded-xl bg-white dark:bg-slate-800/80 border border-slate-100 dark:border-slate-800 flex items-center justify-between text-xs">
                  <div class="flex items-center gap-2.5">
                    <span class="w-6 h-6 rounded-lg bg-emerald-100 text-emerald-700 flex items-center justify-center font-bold text-xs">✓</span>
                    <span class="font-semibold text-slate-700 dark:text-slate-200">2. Campus Clinic Vitals &amp; Medical Clearance</span>
                  </div>
                  <span class="text-[11px] font-mono font-bold text-emerald-600">Cleared (Fit)</span>
                </div>

                <!-- Gate 3: Subject Evaluation -->
                <div class="p-2.5 rounded-xl bg-white dark:bg-slate-800/80 border border-slate-100 dark:border-slate-800 flex items-center justify-between text-xs">
                  <div class="flex items-center gap-2.5">
                    <span class="w-6 h-6 rounded-lg bg-emerald-100 text-emerald-700 flex items-center justify-center font-bold text-xs">✓</span>
                    <span class="font-semibold text-slate-700 dark:text-slate-200">3. Curriculum Evaluation &amp; Subject Advising</span>
                  </div>
                  <span class="text-[11px] font-mono font-bold text-emerald-600">24.0 Units</span>
                </div>

                <!-- Gate 4: Accounting POS -->
                <div class="p-2.5 rounded-xl bg-white dark:bg-slate-800/80 border border-slate-100 dark:border-slate-800 flex items-center justify-between text-xs">
                  <div class="flex items-center gap-2.5">
                    <span class="w-6 h-6 rounded-lg bg-emerald-100 text-emerald-700 flex items-center justify-center font-bold text-xs">✓</span>
                    <span class="font-semibold text-slate-700 dark:text-slate-200">4. Accounting POS Cashier &amp; Official Receipt</span>
                  </div>
                  <span class="text-[11px] font-mono font-bold text-emerald-600">OR-2026-84910</span>
                </div>

                <!-- Gate 5: Final Certificate of Registration -->
                <div class="p-2.5 rounded-xl bg-orange-50 dark:bg-orange-950/40 border border-orange-200 dark:border-orange-800/50 flex items-center justify-between text-xs">
                  <div class="flex items-center gap-2.5">
                    <span class="w-6 h-6 rounded-lg bg-orange-600 text-white flex items-center justify-center font-bold text-xs">★</span>
                    <span class="font-bold text-orange-950 dark:text-orange-300">5. Official COR &amp; Class Schedule Released</span>
                  </div>
                  <a href="views/login.php" class="px-2 py-0.5 bg-orange-600 text-white font-bold rounded text-[10px] hover:bg-orange-700 transition-colors">
                    Download
                  </a>
                </div>
              </div>

              <!-- Quick Mini Action Strip inside Mockup -->
              <div class="mt-4 pt-4 border-t border-slate-100 dark:border-slate-800 grid grid-cols-2 gap-2 text-center">
                <a href="views/login.php" class="py-2 px-3 rounded-xl bg-slate-100 dark:bg-slate-800 hover:bg-slate-200 text-slate-700 dark:text-slate-200 text-xs font-bold transition">
                  Registrar Queue (0 Pending)
                </a>
                <a href="views/login.php" class="py-2 px-3 rounded-xl bg-orange-600 hover:bg-orange-700 text-white text-xs font-bold transition">
                  Cashier POS Terminal
                </a>
              </div>

            </div>

          </div>

        </div>
      </div>
    </section>

    <!-- ========================================================================= -->
    <!-- 5 CORE SYSTEM MODULES SHOWCASE -->
    <!-- ========================================================================= -->
    <section class="py-20 bg-slate-50 dark:bg-[#0f1523] border-b border-slate-200/70 dark:border-slate-800 relative transition-colors" id="modules">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        
        <!-- Section Header -->
        <div class="text-center max-w-3xl mx-auto mb-16">
          <span class="text-xs font-bold text-orange-600 uppercase tracking-widest font-heading">Multi-Office Architecture</span>
          <h2 class="text-3xl sm:text-4xl font-extrabold text-slate-900 tracking-tight mt-2 mb-4 font-heading">
            Five Purpose-Built <span class="text-orange-600">Operational Tiers</span>
          </h2>
          <p class="text-slate-600 dark:text-slate-300 text-sm sm:text-base leading-relaxed">
            Eliminating departmental silos through automated state transitions. Every administrative office operates on real-time live data.
          </p>
        </div>

        <!-- 5 Cards Grid -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
          
          <!-- Module 1: Registrar Operations -->
          <div class="bg-white dark:bg-[#111827] rounded-3xl p-7 border border-slate-200/80 dark:border-slate-800 system-card flex flex-col justify-between">
            <div>
              <div class="w-13 h-13 rounded-2xl bg-orange-100 dark:bg-orange-950/60 text-orange-600 dark:text-orange-400 flex items-center justify-center mb-6 shadow-xs">
                <i class="fa-solid fa-clipboard-check text-[28px]"></i>
              </div>
              <div class="text-xs font-mono font-bold text-orange-600 uppercase tracking-wider mb-1">11 Functional Modules</div>
              <h3 class="text-xl font-bold text-slate-900 mb-3 font-heading">Registrar Operations</h3>
              <p class="text-xs text-slate-600 dark:text-slate-300 leading-relaxed mb-4">
                Automates student admissions review, unique Student No allocation (`2026-XXXXX`), course evaluations, transferee credit crediting, sectioning, weekly class schedule plotting, and issuance of certified Matriculation Forms (COR).
              </p>
              <ul class="text-xs text-slate-500 dark:text-slate-400 space-y-1.5 border-t border-slate-100 dark:border-slate-800 pt-3">
                <li class="flex items-center gap-1.5"><span class="text-emerald-500 font-bold">✓</span> Admissions Queue &amp; Auto-Account Provisioning</li>
                <li class="flex items-center gap-1.5"><span class="text-emerald-500 font-bold">✓</span> Curriculum Prerequisites &amp; Credit Processing</li>
                <li class="flex items-center gap-1.5"><span class="text-emerald-500 font-bold">✓</span> Class Schedule Plotter &amp; ID Card Validator</li>
              </ul>
            </div>
            <a class="mt-6 inline-flex items-center text-xs font-bold text-orange-600 hover:text-orange-700" href="views/login.php">
              Open Registrar Desk <span class="ml-1">→</span>
            </a>
          </div>

          <!-- Module 2: Accounting POS & Cashier -->
          <div class="bg-white dark:bg-[#111827] rounded-3xl p-7 border border-slate-200/80 dark:border-slate-800 system-card flex flex-col justify-between">
            <div>
              <div class="w-13 h-13 rounded-2xl bg-emerald-100 dark:bg-emerald-950/60 text-emerald-600 dark:text-emerald-400 flex items-center justify-center mb-6 shadow-xs">
                <i class="fa-solid fa-cash-register text-[28px]"></i>
              </div>
              <div class="text-xs font-mono font-bold text-emerald-600 uppercase tracking-wider mb-1">4 Cashier Modules</div>
              <h3 class="text-xl font-bold text-slate-900 mb-3 font-heading">Accounting POS &amp; Cashier</h3>
              <p class="text-xs text-slate-600 dark:text-slate-300 leading-relaxed mb-4">
                Calculates tuition and laboratory assessments dynamically according to evaluated unit loads. Features a full Cashier POS terminal with tendered cash change calculations, official receipt generator (`OR-YYYY-XXXXX`), and general ledger logging.
              </p>
              <ul class="text-xs text-slate-500 dark:text-slate-400 space-y-1.5 border-t border-slate-100 dark:border-slate-800 pt-3">
                <li class="flex items-center gap-1.5"><span class="text-emerald-500 font-bold">✓</span> Real-Time Tuition Assessment Per Unit</li>
                <li class="flex items-center gap-1.5"><span class="text-emerald-500 font-bold">✓</span> Point of Sale (POS) Cash Checkout Terminal</li>
                <li class="flex items-center gap-1.5"><span class="text-emerald-500 font-bold">✓</span> Official Receipt (OR) Number Generation</li>
              </ul>
            </div>
            <a class="mt-6 inline-flex items-center text-xs font-bold text-emerald-600 hover:text-emerald-700" href="views/login.php">
              Open Cashier Terminal <span class="ml-1">→</span>
            </a>
          </div>

          <!-- Module 3: Campus Clinic & Health -->
          <div class="bg-white dark:bg-[#111827] rounded-3xl p-7 border border-slate-200/80 dark:border-slate-800 system-card flex flex-col justify-between">
            <div>
              <div class="w-13 h-13 rounded-2xl bg-blue-100 dark:bg-blue-950/60 text-blue-600 dark:text-blue-400 flex items-center justify-center mb-6 shadow-xs">
                <i class="fa-solid fa-briefcase-medical text-[28px]"></i>
              </div>
              <div class="text-xs font-mono font-bold text-blue-600 uppercase tracking-wider mb-1">4 Clinical Modules</div>
              <h3 class="text-xl font-bold text-slate-900 mb-3 font-heading">Campus Health Clinic</h3>
              <p class="text-xs text-slate-600 dark:text-slate-300 leading-relaxed mb-4">
                Maintains a comprehensive electronic patient directory for all enrolled students. Doctors and nurses record physical examinations, vital signs (Blood Pressure, Heart Rate, Height, Weight, BMI), and issue digital health clearance certificates.
              </p>
              <ul class="text-xs text-slate-500 dark:text-slate-400 space-y-1.5 border-t border-slate-100 dark:border-slate-800 pt-3">
                <li class="flex items-center gap-1.5"><span class="text-emerald-500 font-bold">✓</span> Complete Physical Checkup &amp; Vitals Logging</li>
                <li class="flex items-center gap-1.5"><span class="text-emerald-500 font-bold">✓</span> Automatic Body Mass Index (BMI) Computing</li>
                <li class="flex items-center gap-1.5"><span class="text-emerald-500 font-bold">✓</span> Instant Health Clearance Gate Approval</li>
              </ul>
            </div>
            <a class="mt-6 inline-flex items-center text-xs font-bold text-blue-600 hover:text-blue-700" href="views/login.php">
              Open Clinic Desk <span class="ml-1">→</span>
            </a>
          </div>

          <!-- Module 4: Student Self-Service Portal -->
          <div class="bg-white dark:bg-[#111827] rounded-3xl p-7 border border-slate-200/80 dark:border-slate-800 system-card flex flex-col justify-between">
            <div>
              <div class="w-13 h-13 rounded-2xl bg-amber-100 dark:bg-amber-950/60 text-amber-600 dark:text-amber-400 flex items-center justify-center mb-6 shadow-xs">
                <i class="fa-solid fa-graduation-cap text-[28px]"></i>
              </div>
              <div class="text-xs font-mono font-bold text-amber-600 uppercase tracking-wider mb-1">11 Student Modules</div>
              <h3 class="text-xl font-bold text-slate-900 mb-3 font-heading">Student Self-Service Portal</h3>
              <p class="text-xs text-slate-600 dark:text-slate-300 leading-relaxed mb-4">
                Empowers learners with full visibility over their academic standing: an interactive 7-stage progress tracker, document requirements checklist, weekly plotted timetable schedule, itemized Statement of Account (SOA), and validated digital ID pass.
              </p>
              <ul class="text-xs text-slate-500 dark:text-slate-400 space-y-1.5 border-t border-slate-100 dark:border-slate-800 pt-3">
                <li class="flex items-center gap-1.5"><span class="text-emerald-500 font-bold">✓</span> Live 7-Stage Enrolment Lifecycle Tracker</li>
                <li class="flex items-center gap-1.5"><span class="text-emerald-500 font-bold">✓</span> Printable Certificate of Registration (COR)</li>
                <li class="flex items-center gap-1.5"><span class="text-emerald-500 font-bold">✓</span> Digital Student ID with Holographic Validation</li>
              </ul>
            </div>
            <a class="mt-6 inline-flex items-center text-xs font-bold text-amber-600 hover:text-amber-700" href="views/login.php">
              Sign In as Student <span class="ml-1">→</span>
            </a>
          </div>

          <!-- Module 5: System Administration & Auditing -->
          <div class="bg-white dark:bg-[#111827] rounded-3xl p-7 border border-slate-200/80 dark:border-slate-800 system-card flex flex-col justify-between md:col-span-2 lg:col-span-2">
            <div>
              <div class="w-13 h-13 rounded-2xl bg-purple-100 dark:bg-purple-950/60 text-purple-600 dark:text-purple-400 flex items-center justify-center mb-6 shadow-xs">
                <i class="fa-solid fa-user-shield text-[28px]"></i>
              </div>
              <div class="text-xs font-mono font-bold text-purple-600 uppercase tracking-wider mb-1">7 Governance Modules</div>
              <h3 class="text-xl font-bold text-slate-900 mb-3 font-heading">Administration, Security &amp; Master Data</h3>
              <p class="text-xs text-slate-600 dark:text-slate-300 leading-relaxed mb-4">
                Provides campus ICT leadership with fine-grained control: user login credential management with bcrypt hashing, staff role delegation, academic departments &amp; program master data catalogs, and immutable security audit logs tracking every change by IP and timestamp.
              </p>
              <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 text-xs text-slate-500 dark:text-slate-400 border-t border-slate-100 dark:border-slate-800 pt-3">
                <div class="flex items-center gap-1.5"><span class="text-emerald-500 font-bold">✓</span> User Login &amp; Role-Based Access Control (RBAC)</div>
                <div class="flex items-center gap-1.5"><span class="text-emerald-500 font-bold">✓</span> Collegiate Departments &amp; Curricula Master Data</div>
                <div class="flex items-center gap-1.5"><span class="text-emerald-500 font-bold">✓</span> Automatic Credential Issuance &amp; Password Resets</div>
                <div class="flex items-center gap-1.5"><span class="text-emerald-500 font-bold">✓</span> Real-Time Security Audit Logging &amp; Trail Feeds</div>
              </div>
            </div>
            <a class="mt-6 inline-flex items-center text-xs font-bold text-purple-600 hover:text-purple-700" href="views/login.php">
              Open Admin Command Center <span class="ml-1">→</span>
            </a>
          </div>

        </div>
      </div>
    </section>

    <!-- ========================================================================= -->
    <!-- 7-GATE ENROLLMENT LIFECYCLE (WORKFLOW PIPELINE) -->
    <!-- ========================================================================= -->
    <section class="py-20 bg-white dark:bg-[#0b0f19] relative transition-colors" id="lifecycle">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        
        <div class="text-center max-w-3xl mx-auto mb-16">
          <span class="text-xs font-bold text-orange-600 uppercase tracking-widest font-heading">Automated End-to-End Workflow</span>
          <h2 class="text-3xl sm:text-4xl font-extrabold text-slate-900 tracking-tight mt-2 mb-4 font-heading">
            The 7 Verification <span class="text-orange-600">Gates</span>
          </h2>
          <p class="text-slate-600 dark:text-slate-300 text-sm sm:text-base leading-relaxed">
            Every enrollee progresses through seven strict verification gates before their official Certificate of Registration (COR) and student ID card are validated.
          </p>
        </div>

        <!-- 7 Steps Visual Stepper Grid -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
          
          <!-- Gate 1 -->
          <div class="p-6 rounded-2xl bg-slate-50 dark:bg-[#111827] border border-slate-200/80 dark:border-slate-800 flex flex-col justify-between">
            <div>
              <div class="flex items-center justify-between mb-4">
                <span class="w-8 h-8 rounded-full bg-orange-600 text-white font-bold text-xs flex items-center justify-center font-mono">01</span>
                <span class="text-[10px] font-bold text-slate-400 uppercase font-mono">Applicant</span>
              </div>
              <h4 class="text-base font-bold text-slate-900 mb-2 font-heading">Online Admission</h4>
              <p class="text-xs text-slate-500 leading-relaxed">
                Applicant submits personal demographics, educational background, guardian contacts, and documentary requirements via the public portal.
              </p>
            </div>
            <div class="mt-4 pt-3 border-t border-slate-200/60 dark:border-slate-800 text-[11px] font-mono text-orange-600 font-semibold">
              Trigger: Form Submission
            </div>
          </div>

          <!-- Gate 2 -->
          <div class="p-6 rounded-2xl bg-slate-50 dark:bg-[#111827] border border-slate-200/80 dark:border-slate-800 flex flex-col justify-between">
            <div>
              <div class="flex items-center justify-between mb-4">
                <span class="w-8 h-8 rounded-full bg-orange-600 text-white font-bold text-xs flex items-center justify-center font-mono">02</span>
                <span class="text-[10px] font-bold text-slate-400 uppercase font-mono">Registrar</span>
              </div>
              <h4 class="text-base font-bold text-slate-900 mb-2 font-heading">Document Approval</h4>
              <p class="text-xs text-slate-500 leading-relaxed">
                Registrar verifies Form 137/138 or Transcript. Upon 1-click approval, the system generates an official Student Number (`2026-XXXXX`) and provisions login credentials.
              </p>
            </div>
            <div class="mt-4 pt-3 border-t border-slate-200/60 dark:border-slate-800 text-[11px] font-mono text-orange-600 font-semibold">
              Output: Account Provisioned
            </div>
          </div>

          <!-- Gate 3 -->
          <div class="p-6 rounded-2xl bg-slate-50 dark:bg-[#111827] border border-slate-200/80 dark:border-slate-800 flex flex-col justify-between">
            <div>
              <div class="flex items-center justify-between mb-4">
                <span class="w-8 h-8 rounded-full bg-orange-600 text-white font-bold text-xs flex items-center justify-center font-mono">03</span>
                <span class="text-[10px] font-bold text-slate-400 uppercase font-mono">Academic Head</span>
              </div>
              <h4 class="text-base font-bold text-slate-900 mb-2 font-heading">Subject Advising</h4>
              <p class="text-xs text-slate-500 leading-relaxed">
                Academic evaluators verify prerequisites against curriculum master data, evaluate transferee course equivalencies, and approve the semester unit load.
              </p>
            </div>
            <div class="mt-4 pt-3 border-t border-slate-200/60 dark:border-slate-800 text-[11px] font-mono text-orange-600 font-semibold">
              Status: Evaluated Load
            </div>
          </div>

          <!-- Gate 4 -->
          <div class="p-6 rounded-2xl bg-slate-50 dark:bg-[#111827] border border-slate-200/80 dark:border-slate-800 flex flex-col justify-between">
            <div>
              <div class="flex items-center justify-between mb-4">
                <span class="w-8 h-8 rounded-full bg-orange-600 text-white font-bold text-xs flex items-center justify-center font-mono">04</span>
                <span class="text-[10px] font-bold text-slate-400 uppercase font-mono">Registrar</span>
              </div>
              <h4 class="text-base font-bold text-slate-900 mb-2 font-heading">Section &amp; Schedule</h4>
              <p class="text-xs text-slate-500 leading-relaxed">
                Student is allocated into an official cohort block section (e.g. `BSIT-1A`) with plotted timetable days, lecture hours, laboratories, and room assignments.
              </p>
            </div>
            <div class="mt-4 pt-3 border-t border-slate-200/60 dark:border-slate-800 text-[11px] font-mono text-orange-600 font-semibold">
              Matrix: Conflict-Free
            </div>
          </div>

          <!-- Gate 5 -->
          <div class="p-6 rounded-2xl bg-slate-50 dark:bg-[#111827] border border-slate-200/80 dark:border-slate-800 flex flex-col justify-between">
            <div>
              <div class="flex items-center justify-between mb-4">
                <span class="w-8 h-8 rounded-full bg-orange-600 text-white font-bold text-xs flex items-center justify-center font-mono">05</span>
                <span class="text-[10px] font-bold text-slate-400 uppercase font-mono">Campus Clinic</span>
              </div>
              <h4 class="text-base font-bold text-slate-900 mb-2 font-heading">Medical Clearance</h4>
              <p class="text-xs text-slate-500 leading-relaxed">
                Medical officer conducts physical examination, logs vital signs and BMI, verifies immunizations, and grants health clearance in the patient registry.
              </p>
            </div>
            <div class="mt-4 pt-3 border-t border-slate-200/60 dark:border-slate-800 text-[11px] font-mono text-orange-600 font-semibold">
              Status: Cleared (Fit)
            </div>
          </div>

          <!-- Gate 6 -->
          <div class="p-6 rounded-2xl bg-slate-50 dark:bg-[#111827] border border-slate-200/80 dark:border-slate-800 flex flex-col justify-between">
            <div>
              <div class="flex items-center justify-between mb-4">
                <span class="w-8 h-8 rounded-full bg-orange-600 text-white font-bold text-xs flex items-center justify-center font-mono">06</span>
                <span class="text-[10px] font-bold text-slate-400 uppercase font-mono">Accounting</span>
              </div>
              <h4 class="text-base font-bold text-slate-900 mb-2 font-heading">POS Billing &amp; OR</h4>
              <p class="text-xs text-slate-500 leading-relaxed">
                Tuition assessment is loaded into the Cashier POS terminal. Cash tendered is processed, change calculated, and official receipt (`OR-YYYY-XXXXX`) generated.
              </p>
            </div>
            <div class="mt-4 pt-3 border-t border-slate-200/60 dark:border-slate-800 text-[11px] font-mono text-orange-600 font-semibold">
              Status: Paid / Settled
            </div>
          </div>

          <!-- Gate 7 -->
          <div class="p-6 rounded-2xl bg-orange-600 text-white shadow-xl shadow-orange-500/20 flex flex-col justify-between md:col-span-2 lg:col-span-2">
            <div>
              <div class="flex items-center justify-between mb-4">
                <span class="w-8 h-8 rounded-full bg-white text-orange-600 font-black text-xs flex items-center justify-center font-mono">07</span>
                <span class="text-[10px] font-bold text-orange-100 uppercase font-mono">Final Board</span>
              </div>
              <h4 class="text-lg font-bold text-white mb-2 font-heading">Enrollment Confirmation &amp; Official COR Release</h4>
              <p class="text-xs text-orange-100 leading-relaxed">
                The 7-gate approval board confirms that Admission, Advising, Sectioning, Clinic, Clearances, and Payment are 100% satisfied. The official Certificate of Registration (COR) is released and the student RFID card is validated for campus turnstiles.
              </p>
            </div>
            <div class="mt-4 pt-3 border-t border-white/20 flex items-center justify-between text-xs">
              <span class="font-bold text-white">Full University Enrollment Confirmed</span>
              <a href="views/login.php" class="px-3 py-1 rounded bg-white text-orange-600 font-bold hover:bg-orange-50 transition-colors">
                Sign In to View COR
              </a>
            </div>
          </div>

        </div>
      </div>
    </section>

    <!-- ========================================================================= -->
    <!-- DIRECT ROLE PORTALS HUB -->
    <!-- ========================================================================= -->
    <section class="py-20 bg-slate-50 dark:bg-[#0f1523] border-t border-slate-200/70 dark:border-slate-800 relative transition-colors" id="portals">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        
        <div class="text-center max-w-2xl mx-auto mb-14">
          <span class="text-xs font-bold text-orange-600 uppercase tracking-widest font-heading">Access Directory</span>
          <h2 class="text-3xl sm:text-4xl font-extrabold text-slate-900 tracking-tight mt-2 mb-3 font-heading">
            Direct Office <span class="text-orange-600">Access Portals</span>
          </h2>
          <p class="text-slate-600 dark:text-slate-300 text-sm">
            Select your assigned university office to authenticate and access your command workspace.
          </p>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-4">
          
          <!-- Portal 1: Student -->
          <a href="views/login.php" class="bg-white dark:bg-[#111827] p-5 rounded-2xl border border-slate-200/80 dark:border-slate-800 hover:border-orange-500 system-card flex flex-col items-center text-center group">
            <div class="w-12 h-12 rounded-xl bg-amber-100 dark:bg-amber-950/60 text-amber-600 flex items-center justify-center mb-3 group-hover:scale-110 transition-transform">
              <i class="fa-solid fa-graduation-cap text-[24px]"></i>
            </div>
            <h4 class="text-sm font-bold text-slate-900 mb-1 font-heading">Student Enrollee</h4>
            <p class="text-[11px] text-slate-500">COR, Class Schedule, Clearance &amp; SOA</p>
            <span class="mt-3 text-[11px] font-bold text-orange-600 flex items-center gap-0.5">Log In →</span>
          </a>

          <!-- Portal 2: Registrar -->
          <a href="views/login.php" class="bg-white dark:bg-[#111827] p-5 rounded-2xl border border-slate-200/80 dark:border-slate-800 hover:border-orange-500 system-card flex flex-col items-center text-center group">
            <div class="w-12 h-12 rounded-xl bg-orange-100 dark:bg-orange-950/60 text-orange-600 flex items-center justify-center mb-3 group-hover:scale-110 transition-transform">
              <i class="fa-solid fa-clipboard-user text-[24px]"></i>
            </div>
            <h4 class="text-sm font-bold text-slate-900 mb-1 font-heading">Registrar Officer</h4>
            <p class="text-[11px] text-slate-500">Admissions, Evaluation, Schedule &amp; Approvals</p>
            <span class="mt-3 text-[11px] font-bold text-orange-600 flex items-center gap-0.5">Log In →</span>
          </a>

          <!-- Portal 3: Cashier / POS -->
          <a href="views/login.php" class="bg-white dark:bg-[#111827] p-5 rounded-2xl border border-slate-200/80 dark:border-slate-800 hover:border-orange-500 system-card flex flex-col items-center text-center group">
            <div class="w-12 h-12 rounded-xl bg-emerald-100 dark:bg-emerald-950/60 text-emerald-600 flex items-center justify-center mb-3 group-hover:scale-110 transition-transform">
              <i class="fa-solid fa-cash-register text-[24px]"></i>
            </div>
            <h4 class="text-sm font-bold text-slate-900 mb-1 font-heading">Accounting Cashier</h4>
            <p class="text-[11px] text-slate-500">Tuition Assessment, POS Terminal &amp; Receipts</p>
            <span class="mt-3 text-[11px] font-bold text-emerald-600 flex items-center gap-0.5">Log In →</span>
          </a>

          <!-- Portal 4: Clinic -->
          <a href="views/login.php" class="bg-white dark:bg-[#111827] p-5 rounded-2xl border border-slate-200/80 dark:border-slate-800 hover:border-orange-500 system-card flex flex-col items-center text-center group">
            <div class="w-12 h-12 rounded-xl bg-blue-100 dark:bg-blue-950/60 text-blue-600 flex items-center justify-center mb-3 group-hover:scale-110 transition-transform">
              <i class="fa-solid fa-briefcase-medical text-[24px]"></i>
            </div>
            <h4 class="text-sm font-bold text-slate-900 mb-1 font-heading">Health Clinic</h4>
            <p class="text-[11px] text-slate-500">Vitals, Consultations &amp; Medical Clearance</p>
            <span class="mt-3 text-[11px] font-bold text-blue-600 flex items-center gap-0.5">Log In →</span>
          </a>

          <!-- Portal 5: Administrator -->
          <a href="views/login.php" class="bg-white dark:bg-[#111827] p-5 rounded-2xl border border-slate-200/80 dark:border-slate-800 hover:border-orange-500 system-card flex flex-col items-center text-center group">
            <div class="w-12 h-12 rounded-xl bg-purple-100 dark:bg-purple-950/60 text-purple-600 flex items-center justify-center mb-3 group-hover:scale-110 transition-transform">
              <i class="fa-solid fa-shield-halved text-[24px]"></i>
            </div>
            <h4 class="text-sm font-bold text-slate-900 mb-1 font-heading">System Admin</h4>
            <p class="text-[11px] text-slate-500">User Accounts, Security Logs &amp; Master Data</p>
            <span class="mt-3 text-[11px] font-bold text-purple-600 flex items-center gap-0.5">Log In →</span>
          </a>

        </div>
      </div>
    </section>

    <!-- ========================================================================= -->
    <!-- PRE-FOOTER CALL TO ACTION -->
    <!-- ========================================================================= -->
    <section class="py-14 bg-white dark:bg-[#0b0f19] border-t border-slate-200/70 dark:border-slate-800">
      <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="bg-gradient-to-r from-orange-600 via-orange-500 to-amber-500 rounded-3xl p-8 sm:p-12 shadow-xl text-white flex flex-col md:flex-row items-center justify-between gap-8">
          <div class="text-center md:text-left">
            <h3 class="text-2xl sm:text-3xl font-extrabold tracking-tight font-heading">Start Academic Year 2026–2027 Enrolment</h3>
            <p class="text-orange-100 text-xs sm:text-sm mt-2 max-w-lg">
              Submit your admissions credentials online or sign in to track your subject clearance checkpoints in real-time.
            </p>
          </div>
          <div class="flex flex-wrap items-center gap-3">
            <a class="px-6 py-3.5 rounded-xl bg-white text-orange-600 font-bold text-xs sm:text-sm shadow-md hover:bg-orange-50 transition-all flex items-center gap-1.5" href="views/admission.php">
              <i class="fa-solid fa-pen-to-square text-[18px]"></i>
              <span>New Admission Application</span>
            </a>
            <a class="px-6 py-3.5 rounded-xl bg-slate-950 hover:bg-black text-white font-bold text-xs sm:text-sm transition-all shadow-md flex items-center gap-1.5" href="views/login.php">
              <i class="fa-solid fa-lock-open text-[18px]"></i>
              <span>Sign In to System</span>
            </a>
          </div>
        </div>
      </div>
    </section>
  </main>

  <!-- ========================================================================= -->
  <!-- PERSISTENT FOOTER WITH OFFICIAL SEAL -->
  <!-- ========================================================================= -->
  <footer class="bg-slate-100 dark:bg-[#090d16] border-t border-slate-200 dark:border-slate-800 pt-16 pb-12 transition-colors" id="architecture">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
      <div class="grid grid-cols-1 md:grid-cols-12 gap-10 pb-12 border-b border-slate-200 dark:border-slate-800">
        
        <!-- Brand Col with Official Seal -->
        <div class="md:col-span-5 flex flex-col items-start">
          <div class="flex items-center gap-3 mb-4">
            <img src="assets/seait.png" alt="SEAIT Official Logo" class="w-10 h-10 object-contain shrink-0"/>
            <div>
              <div class="text-base font-black text-slate-900 leading-tight">South East Asian Institute of Technology</div>
              <div class="text-[10px] uppercase font-bold text-orange-600 tracking-wider">Introllment Academic System</div>
            </div>
          </div>
          <p class="text-xs text-slate-500 leading-relaxed max-w-sm mb-6">
            A state-of-the-art integrated campus automation system managing Admissions, Health Clearances, Advising, Sectioning, POS Cashiering, and Student Records.
          </p>
          <div class="text-xs text-slate-500 space-y-1">
            <div class="flex items-center gap-2">
              <i class="fa-solid fa-location-dot text-[16px] text-orange-600"></i>
              <span>National Highway, Crossing Rubber, Tupi, South Cotabato</span>
            </div>
            <div class="flex items-center gap-2">
              <i class="fa-solid fa-envelope text-[16px] text-orange-600"></i>
              <span>registrar@seait.edu.ph • it.support@seait.edu.ph</span>
            </div>
          </div>
        </div>

        <!-- Links Col 1: System Modules -->
        <div class="md:col-span-3">
          <h4 class="text-xs font-black uppercase tracking-wider text-slate-900 mb-4 font-heading">System Modules</h4>
          <ul class="space-y-2 text-xs text-slate-600 dark:text-slate-400">
            <li><a class="hover:text-orange-600 transition-colors" href="views/login.php">Registrar Operations Queue</a></li>
            <li><a class="hover:text-orange-600 transition-colors" href="views/login.php">Accounting Cashier (POS)</a></li>
            <li><a class="hover:text-orange-600 transition-colors" href="views/login.php">Campus Health Clearance</a></li>
            <li><a class="hover:text-orange-600 transition-colors" href="views/login.php">Student Self-Service Hub</a></li>
            <li><a class="hover:text-orange-600 transition-colors" href="views/login.php">Admin Security &amp; Audit Logs</a></li>
          </ul>
        </div>

        <!-- Links Col 2: Direct Functions -->
        <div class="md:col-span-2">
          <h4 class="text-xs font-black uppercase tracking-wider text-slate-900 mb-4 font-heading">Enrollee Tools</h4>
          <ul class="space-y-2 text-xs text-slate-600 dark:text-slate-400">
            <li><a class="hover:text-orange-600 transition-colors" href="views/admission.php">Freshmen Admission</a></li>
            <li><a class="hover:text-orange-600 transition-colors" href="views/login.php">Download Official COR</a></li>
            <li><a class="hover:text-orange-600 transition-colors" href="views/login.php">Class Schedule Plotter</a></li>
            <li><a class="hover:text-orange-600 transition-colors" href="views/login.php">Fee Assessment &amp; SOA</a></li>
            <li><a class="hover:text-orange-600 transition-colors" href="views/login.php">RFID ID Pass Validation</a></li>
          </ul>
        </div>

        <!-- Links Col 3: Architecture & Security -->
        <div class="md:col-span-2">
          <h4 class="text-xs font-black uppercase tracking-wider text-slate-900 mb-4 font-heading">Architecture</h4>
          <ul class="space-y-2 text-xs text-slate-600 dark:text-slate-400">
            <li><span class="text-slate-400">MySQL Relational (22 Tables)</span></li>
            <li><span class="text-slate-400">Bcrypt RBAC Security</span></li>
            <li><span class="text-slate-400">FOUC-Free Dark Theme</span></li>
            <li><span class="text-slate-400">CHED Standard Curricula</span></li>
          </ul>
        </div>

      </div>

      <!-- Footer Bottom Strip -->
      <div class="pt-8 flex flex-col sm:flex-row items-center justify-between text-[11px] text-slate-500 gap-4">
        <p>© 2026 South East Asian Institute of Technology, Inc. (SEAIT). All rights reserved.</p>
        <div class="flex items-center gap-4">
          <span class="inline-flex items-center gap-1.5 font-mono">
            <span class="w-2 h-2 rounded-full bg-emerald-500"></span>
            <span>256-Bit SSL Encrypted Campus Node</span>
          </span>
        </div>
      </div>
    </div>
  </footer>

  <!-- Theme Toggle Script -->
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