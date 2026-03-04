@extends('welcome')

@section('content')
<div class="content-wrapper pt-2">

<title>Employee Profile - Jonathan Pierce</title>
<script src="https://cdn.tailwindcss.com?plugins=forms,typography,container-queries"></script>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800;900&family=Material+Icons+Outlined&display=swap" rel="stylesheet"/>
<script>
  tailwind.config = {
    darkMode: "class",
    theme: {
      extend: {
        colors: {
          primary: "#2563eb",
          "background-light": "#f8fafc",
          "background-dark": "#0f172a",
        },
        fontFamily: {
          display: ["Inter", "sans-serif"],
        },
        borderRadius: {
          DEFAULT: "0.5rem",
        },
        boxShadow: {
          'refined': '0 2px 4px -1px rgb(0 0 0 / 0.05), 0 1px 2px -1px rgb(0 0 0 / 0.05)',
          'card-hover': '0 6px 10px -2px rgb(0 0 0 / 0.08), 0 2px 4px -2px rgb(0 0 0 / 0.08)',
        }
      },
    },
  };
</script>
<style>
  body { font-family: 'Inter', sans-serif; -webkit-font-smoothing: antialiased; }
  .glass-card {
    background: rgba(255, 255, 255, 0.8);
    backdrop-filter: blur(8px);
  }
  .dark .glass-card {
    background: rgba(30, 41, 59, 0.7);
  }
</style>

<body class="bg-background-light dark:bg-background-dark text-slate-800 dark:text-slate-200 min-h-screen transition-colors duration-200">
<div class="max-w-[1600px] mx-auto p-3">

  <!-- Header -->
  <header class="flex justify-between items-center mb-4">
    <div class="flex items-center gap-2">
      <span class="text-slate-400 dark:text-slate-500 font-medium text-sm">Employees /</span>
      <h1 class="text-base font-bold text-slate-900 dark:text-white tracking-tight">{{ $emp->emp_name ?? $id }}</h1>
    </div>
    <div class="flex gap-2">
      <a href="{{ route('divhr.employelist') }}" class="px-2.5 py-1 text-xs font-semibold text-slate-600 bg-white border border-slate-200 rounded-sm shadow-refined hover:bg-slate-50 transition-all dark:bg-slate-800 dark:border-slate-700 dark:text-slate-300">
        <span class="material-icons-outlined text-xs align-middle mr-0.5">arrow_back</span> Back
      </a>
      <button class="px-2.5 py-1 text-xs font-semibold text-white bg-primary rounded-sm shadow-sm hover:bg-blue-700 transition-all">
        Edit Profile
      </button>
    </div>
  </header>

  <div class="grid grid-cols-12 gap-4">

    <!-- Left Sidebar -->
    <div class="col-span-12 lg:col-span-3 space-y-3">

      <!-- Profile Card -->
      <div class="bg-white dark:bg-slate-800 rounded-sm shadow-refined border border-slate-200/60 dark:border-slate-700 p-4 overflow-hidden relative text-center">
        <div class="absolute top-0 left-0 w-full h-0.5 bg-primary"></div>
        <div class="relative mb-2 inline-block">
          <img alt="Jonathan Pierce" class="w-14 h-14 rounded-full border-4 border-slate-50 dark:border-slate-900 shadow-md object-cover" src="https://lh3.googleusercontent.com/aida-public/AB6AXuD1bJoSvLWaUEvY8bQ1GelzXFgA4xPz2N463E0PpFjsXKU1WaAO9JvYAJCsRbWMU3myOXm1gO6gBr2F3eZCjkqNmVvHSHzYN5KhYGa_3vezLxlHcxfXOJxvcF-5bSqeehClPpRIeEIxyQmKoD7WbFF8HYb4-4YkqhE17sWDyABTd9S-90EaLzBIFWpfGxG2I7TmWiiWJPRQEDeKDwDPn90Uk8Q3inr2hcwAkSvwmgn9Yuumpjg5iCVEXUL0LW-eDEAxYKMR9ontSg"/>
        </div>
        <h2 class="text-base font-bold text-slate-900 dark:text-white leading-tight">{{ $emp->emp_name ?? '—' }}</h2>
        <p class="text-sm text-slate-500 dark:text-slate-400 mt-0.5">{{ $emp->emp_title ?? ($emp->emp_rank ?? '—') }}</p>
        <div class="mt-3 space-y-2 pt-3 border-t border-slate-100 dark:border-slate-700">
          <div class="flex justify-between text-xs">
            <span class="text-slate-400 font-medium">Employee ID</span>
            <span class="font-bold text-slate-900 dark:text-white">{{ $emp->emp_id ?? $id }}</span>
          </div>
          <div class="flex justify-between text-xs">
            <span class="text-slate-400 font-medium">Joined</span>
            <span class="font-bold text-slate-900 dark:text-white">{{ !empty($emp->emp_joindt) ? \Carbon\Carbon::parse($emp->emp_joindt)->format('d-M-Y') : '—' }}</span>
          </div>
          <div class="flex justify-between text-xs">
            <span class="text-slate-400 font-medium">Department</span>
            <span class="font-bold text-slate-900 dark:text-white">{{ $base->eff_unit_name ?? ($emp->unt_name ?? '—') }}</span>
          </div>
        </div>
      </div>

      <!-- Leave Utilization -->
      <div class="bg-white dark:bg-slate-800 rounded-sm shadow-refined border border-slate-200/60 dark:border-slate-700 p-3">
        <div class="flex items-center gap-1.5 mb-3">
          <span class="material-icons-outlined text-primary text-base">assessment</span>
          <h3 class="font-bold text-sm text-slate-900 dark:text-white">Leave Utilization</h3>
        </div>
        <div class="space-y-2.5">
          <div>
            <div class="flex justify-between text-xs mb-1">
              <span class="text-slate-500 font-medium">Annual</span>
              <span class="font-bold text-slate-700 dark:text-slate-300">20 / 25 d</span>
            </div>
            <div class="w-full bg-slate-100 dark:bg-slate-700 h-1.5 rounded-full overflow-hidden">
              <div class="bg-primary h-full" style="width: 80%"></div>
            </div>
          </div>
          <div>
            <div class="flex justify-between text-xs mb-1">
              <span class="text-slate-500 font-medium">Sick</span>
              <span class="font-bold text-slate-700 dark:text-slate-300">3 / 10 d</span>
            </div>
            <div class="w-full bg-slate-100 dark:bg-slate-700 h-1.5 rounded-full overflow-hidden">
              <div class="bg-blue-400 h-full" style="width: 30%"></div>
            </div>
          </div>
          <div>
            <div class="flex justify-between text-xs mb-1">
              <span class="text-slate-500 font-medium">Casual</span>
              <span class="font-bold text-slate-700 dark:text-slate-300">2 / 5 d</span>
            </div>
            <div class="w-full bg-slate-100 dark:bg-slate-700 h-1.5 rounded-full overflow-hidden">
              <div class="bg-blue-300 h-full" style="width: 40%"></div>
            </div>
          </div>
        </div>
      </div>

      <!-- Specialized Skills -->
      <div class="bg-white dark:bg-slate-800 rounded-sm shadow-refined border border-slate-200/60 dark:border-slate-700 p-3">
        <div class="flex items-center gap-1.5 mb-3">
          <span class="material-icons-outlined text-primary text-base">psychology</span>
          <h3 class="font-bold text-sm text-slate-900 dark:text-white">Specialized Skills</h3>
        </div>
        <div class="flex flex-wrap gap-1.5">
          <span class="px-2 py-0.5 bg-blue-50 dark:bg-blue-900/20 text-blue-600 dark:text-blue-400 text-xs font-semibold rounded-sm border border-blue-100/50 dark:border-blue-900/50">Wireframing</span>
          <span class="px-2 py-0.5 bg-blue-50 dark:bg-blue-900/20 text-blue-600 dark:text-blue-400 text-xs font-semibold rounded-sm border border-blue-100/50 dark:border-blue-900/50">User Research</span>
          <span class="px-2 py-0.5 bg-blue-50 dark:bg-blue-900/20 text-blue-600 dark:text-blue-400 text-xs font-semibold rounded-sm border border-blue-100/50 dark:border-blue-900/50">Prototyping</span>
          <span class="px-2 py-0.5 bg-blue-50 dark:bg-blue-900/20 text-blue-600 dark:text-blue-400 text-xs font-semibold rounded-sm border border-blue-100/50 dark:border-blue-900/50">Design Systems</span>
          <span class="px-2 py-0.5 bg-blue-50 dark:bg-blue-900/20 text-blue-600 dark:text-blue-400 text-xs font-semibold rounded-sm border border-blue-100/50 dark:border-blue-900/50">Accessibility</span>
        </div>
      </div>

      <!-- Quick Actions -->
      <div class="bg-white dark:bg-slate-800 rounded-sm shadow-refined border border-slate-200/60 dark:border-slate-700 p-3">
        <div class="flex items-center gap-1.5 mb-3">
          <span class="material-icons-outlined text-primary text-base">bolt</span>
          <h3 class="font-bold text-sm text-slate-900 dark:text-white">Quick Actions</h3>
        </div>
        <div class="grid grid-cols-2 gap-2">
          <button class="flex items-center justify-center gap-1 px-2 py-1.5 bg-primary text-white text-xs font-bold rounded-sm hover:bg-blue-700 transition-all shadow-sm">
            <span class="material-icons-outlined text-xs">email</span> Email
          </button>
          <button class="flex items-center justify-center gap-1 px-2 py-1.5 border border-slate-200 dark:border-slate-700 text-slate-600 dark:text-slate-300 text-xs font-bold rounded-sm hover:bg-slate-50 dark:hover:bg-slate-700 transition-all">
            <span class="material-icons-outlined text-xs">file_download</span> CV
          </button>
        </div>
      </div>
    </div>

    <!-- Main Content -->
    <div class="col-span-12 lg:col-span-9 space-y-4">

      <!-- Contract + Salary Row -->
      <div class="grid grid-cols-1 xl:grid-cols-12 gap-4">

        <!-- Contract Details -->
        <div class="xl:col-span-8 bg-white dark:bg-slate-800 rounded-sm shadow-refined border border-slate-200/60 dark:border-slate-700 p-4">
          <div class="flex items-center gap-2 mb-3">
            <span class="material-icons-outlined text-primary text-lg">description</span>
            <h3 class="font-bold text-sm text-slate-900 dark:text-white">Contract Details</h3>
          </div>
          <div class="bg-slate-50/50 dark:bg-slate-900/40 p-3 rounded-sm border border-slate-100 dark:border-slate-700/50 mb-4">
            <div class="grid grid-cols-3 gap-3 mb-3">
              <div>
                <p class="text-[10px] text-slate-400 uppercase tracking-widest mb-0.5 font-bold">Department</p>
                <p class="font-semibold text-slate-900 dark:text-white text-sm">{{ $base->eff_unit_name ?? ($emp->unt_name ?? '—') }}</p>
              </div>
              <div>
                <p class="text-[10px] text-slate-400 uppercase tracking-widest mb-0.5 font-bold">Designation</p>
                <p class="font-semibold text-slate-900 dark:text-white text-sm">{{ $currentContract->ctr_jobtitle ?? ($emp->emp_title ?? '—') }}</p>
              </div>
              <div>
                <p class="text-[10px] text-slate-400 uppercase tracking-widest mb-0.5 font-bold">Next Review</p>
                <p class="font-semibold text-slate-900 dark:text-white text-sm">
                  {{ !empty($currentContract->ctr_enddt) ? \Carbon\Carbon::parse($currentContract->ctr_enddt)->format('d-M-Y') : '—' }}
                </p>
              </div>
            </div>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 pt-3 border-t border-slate-100 dark:border-slate-700/50">
              <div>
                <p class="text-[10px] text-slate-400 uppercase tracking-widest mb-0.5 font-bold">Current Annual Salary</p>
                @php
                  $monthlyNet = $base->srq_netsalary ?? $base->srq_salary ?? null;
                  $annualNet = $monthlyNet ? $monthlyNet * 12 : null;
                @endphp
                <p class="text-lg font-black text-slate-900 dark:text-white">
                  {{ $annualNet ? number_format($annualNet) : '—' }}
                </p>
              </div>
              <div>
                <p class="text-[10px] text-slate-400 uppercase tracking-widest mb-1 font-bold">Probation Period</p>
                <div class="w-full bg-slate-200 dark:bg-slate-700 h-2 rounded-full overflow-hidden mt-1">
                  <div class="bg-emerald-500 h-full flex items-center justify-end pr-1" style="width: 45%">
                    <span class="text-[7px] text-white font-bold">45%</span>
                  </div>
                </div>
              </div>
            </div>
          </div>
          <div>
            <h4 class="text-[10px] text-slate-400 uppercase tracking-widest mb-2 font-bold">Previous Contracts History</h4>
            <div class="overflow-hidden border border-slate-100 dark:border-slate-700/50 rounded-sm">
              <table class="w-full text-left text-xs">
                <thead class="bg-slate-50/80 dark:bg-slate-700/50 border-b border-slate-100 dark:border-slate-700">
                  <tr>
                    <th class="px-3 py-2 font-bold text-[9px] text-slate-500 uppercase">Role</th>
                    <th class="px-3 py-2 font-bold text-[9px] text-slate-500 uppercase">Salary</th>
                    <th class="px-3 py-2 font-bold text-[9px] text-slate-500 uppercase">Start</th>
                    <th class="px-3 py-2 font-bold text-[9px] text-slate-500 uppercase">End</th>
                    <th class="px-3 py-2 font-bold text-[9px] text-slate-500 uppercase text-center">Status</th>
                  </tr>
                </thead>
                <tbody class="divide-y divide-slate-50 dark:divide-slate-700/50">
                  @forelse($contractsHistory ?? [] as $c)
                    <tr class="hover:bg-slate-50/50 dark:hover:bg-slate-700/20 transition-colors">
                      <td class="px-3 py-2 font-medium text-slate-900 dark:text-white text-xs">{{ $c->ctr_jobtitle ?? '—' }}</td>
                      <td class="px-3 py-2 font-bold text-slate-700 dark:text-slate-300 text-xs">{{ $c->ctr_salary ? number_format($c->ctr_salary) : '—' }}</td>
                      <td class="px-3 py-2 text-slate-500 text-xs">{{ !empty($c->ctr_startdt) ? \Carbon\Carbon::parse($c->ctr_startdt)->format('d-M-Y') : '—' }}</td>
                      <td class="px-3 py-2 text-slate-500 text-xs">{{ !empty($c->ctr_enddt) ? \Carbon\Carbon::parse($c->ctr_enddt)->format('d-M-Y') : '—' }}</td>
                      <td class="px-3 py-2 text-center">
                        @php
                          $cls = $c->status_label === 'Active' ? 'bg-emerald-50 dark:bg-emerald-900/20 text-emerald-600 dark:text-emerald-400' :
                                 ($c->status_label === 'Future' ? 'bg-blue-50 dark:bg-blue-900/20 text-blue-600 dark:text-blue-400' :
                                  'bg-slate-50 dark:bg-slate-700 text-slate-600 dark:text-slate-300');
                        @endphp
                        <span class="px-2 py-0.5 {{ $cls }} rounded-full text-[9px] font-black uppercase">{{ $c->status_label }}</span>
                      </td>
                    </tr>
                  @empty
                    <tr><td class="px-3 py-2 text-center text-slate-500 text-xs" colspan="5">No contracts</td></tr>
                  @endforelse
                </tbody>
              </table>
            </div>
          </div>
        </div>

        <!-- Salary Progression -->
        <div class="xl:col-span-4 bg-white dark:bg-slate-800 rounded-sm shadow-refined border border-slate-200/60 dark:border-slate-700 p-3 flex flex-col">
          <div class="flex items-center justify-between mb-3">
            <div class="flex items-center gap-1.5">
              <span class="material-icons-outlined text-primary text-base">auto_graph</span>
              <h3 class="font-bold text-sm text-slate-900 dark:text-white">Salary Progression</h3>
            </div>
            @php
              $first = ($salaryProgression ?? collect())->first();
              $last = ($salaryProgression ?? collect())->last();
              $growth = ($first && $last && $first->total) ? round((($last->total - $first->total)/$first->total)*100, 1) : null;
            @endphp
            <span class="text-[8px] font-black text-emerald-500 bg-emerald-50 dark:bg-emerald-900/20 px-1.5 py-0.5 rounded-sm border border-emerald-100/50 dark:border-emerald-800/50 uppercase">{{ $growth !== null ? ($growth > 0 ? '+' : '').$growth.'%' : '—' }}</span>
          </div>
          <div class="flex-grow flex items-end justify-center relative min-h-[110px] px-1 mb-2">
            <svg class="w-full h-full overflow-visible" preserveAspectRatio="none" viewBox="0 0 400 200">
              <defs>
                <linearGradient id="chartGradient" x1="0%" x2="0%" y1="0%" y2="100%">
                  <stop offset="0%" style="stop-color:#2563eb;stop-opacity:0.2"></stop>
                  <stop offset="100%" style="stop-color:#2563eb;stop-opacity:0"></stop>
                </linearGradient>
              </defs>
              <path d="M 0,180 L 100,165 L 200,125 L 300,90 L 400,30" fill="none" stroke="#2563eb" stroke-linecap="round" stroke-linejoin="round" stroke-width="3"></path>
              <path d="M 0,180 L 100,165 L 200,125 L 300,90 L 400,30 V 200 H 0 Z" fill="url(#chartGradient)"></path>
              <circle cx="0" cy="180" fill="white" r="3.5" stroke="#2563eb" stroke-width="1.5"></circle>
              <circle cx="100" cy="165" fill="white" r="3.5" stroke="#2563eb" stroke-width="1.5"></circle>
              <circle cx="200" cy="125" fill="white" r="3.5" stroke="#2563eb" stroke-width="1.5"></circle>
              <circle cx="300" cy="90" fill="white" r="3.5" stroke="#2563eb" stroke-width="1.5"></circle>
              <circle cx="400" cy="30" fill="white" r="3.5" stroke="#2563eb" stroke-width="1.5"></circle>
            </svg>
          </div>
          <div class="flex justify-between text-[8px] text-slate-400 font-black uppercase tracking-widest border-t border-slate-50 dark:border-slate-700/50 pt-1.5 mb-3">
            <span>2020</span><span>2021</span><span>2022</span><span>2023</span><span>2024</span>
          </div>
          <div class="grid grid-cols-2 gap-2">
            <div class="p-2 bg-slate-50/80 dark:bg-slate-900/40 rounded-sm border border-slate-100 dark:border-slate-700/50">
              <p class="text-[8px] text-slate-400 uppercase font-black tracking-widest mb-0.5">Base Start</p>
              @php
                $minYear = ($salaryProgression ?? collect())->min('yr');
                $minTotal = ($salaryProgression ?? collect())->firstWhere('yr', $minYear)->total ?? null;
              @endphp
              <p class="text-sm font-black text-slate-900 dark:text-white">{{ $minTotal ? number_format($minTotal) : '—' }}</p>
            </div>
            <div class="p-2 bg-blue-50/50 dark:bg-blue-900/20 rounded-sm border border-blue-100/30 dark:border-blue-800/30">
              <p class="text-[8px] text-primary uppercase font-black tracking-widest mb-0.5">Current</p>
              @php
                $maxYear = ($salaryProgression ?? collect())->max('yr');
                $maxTotal = ($salaryProgression ?? collect())->firstWhere('yr', $maxYear)->total ?? null;
              @endphp
              <p class="text-sm font-black text-slate-900 dark:text-white">{{ $maxTotal ? number_format($maxTotal) : '—' }}</p>
            </div>
          </div>
        </div>
      </div>

      <!-- Career / Education / Certifications -->
      <div class="grid grid-cols-1 md:grid-cols-3 gap-4">

        <!-- Career Details -->
        <div class="bg-white dark:bg-slate-800 rounded-sm shadow-refined border border-slate-200/60 dark:border-slate-700 p-4">
          <div class="flex items-center gap-2 mb-3">
            <span class="material-icons-outlined text-primary text-lg">work_outline</span>
            <h3 class="font-bold text-sm text-slate-900 dark:text-white">Career Details</h3>
          </div>
          <div class="space-y-2.5">
            <div class="flex justify-between items-center">
              <span class="text-xs text-slate-400 font-bold uppercase">Years in Service</span>
              <span class="text-sm font-bold text-slate-900 dark:text-white">{{ $yearsInService !== null ? $yearsInService.' years' : '—' }}</span>
            </div>
            <div class="flex justify-between items-center">
              <span class="text-xs text-slate-400 font-bold uppercase">Last Promotion</span>
              @php
                $lastJob = ($jobs ?? collect())->first();
                $lastProm = $lastJob->job_to ?? $lastJob->job_from ?? null;
              @endphp
              <span class="text-sm font-bold text-slate-900 dark:text-white">{{ $lastProm ? \Carbon\Carbon::parse($lastProm)->format('M Y') : '—' }}</span>
            </div>
            <div class="flex justify-between items-center">
              <span class="text-xs text-slate-400 font-bold uppercase">Current Band</span>
              <span class="text-sm font-bold text-slate-900 dark:text-white">—</span>
            </div>
            <div class="flex justify-between items-center">
              <span class="text-xs text-slate-400 font-bold uppercase">Rating</span>
              <span class="text-sm font-bold flex items-center gap-1 text-slate-900 dark:text-white">—</span>
            </div>
          </div>
        </div>

        <!-- Education Details -->
        <div class="bg-white dark:bg-slate-800 rounded-sm shadow-refined border border-slate-200/60 dark:border-slate-700 p-4">
          <div class="flex items-center gap-2 mb-3">
            <span class="material-icons-outlined text-primary text-lg">school</span>
            <h3 class="font-bold text-sm text-slate-900 dark:text-white">Education Details</h3>
          </div>
          <div class="space-y-2.5">
            <div>
              <p class="text-[10px] text-slate-400 font-bold uppercase tracking-widest mb-0.5">Degree</p>
              @php $deg = ($degrees ?? collect())->first(); @endphp
              <p class="text-sm font-bold text-slate-900 dark:text-white">{{ $deg ? $deg->qlf_name : '—' }}</p>
            </div>
            <div>
              <p class="text-[10px] text-slate-400 font-bold uppercase tracking-widest mb-0.5">Institution</p>
              <p class="text-sm font-bold text-slate-900 dark:text-white">{{ $deg ? $deg->qlf_inst : '—' }}</p>
            </div>
            <div class="flex justify-between items-end">
              <div>
                <p class="text-[10px] text-slate-400 font-bold uppercase tracking-widest mb-0.5">Duration</p>
                <p class="text-sm font-bold text-slate-900 dark:text-white">
                  @if($deg)
                    {{ $deg->qlf_duration }} {{ $deg->qlf_unit }} @if(!empty($deg->qlf_enddt)) • {{ \Carbon\Carbon::parse($deg->qlf_enddt)->format('Y') }} @endif
                  @else
                    —
                  @endif
                </p>
              </div>
              <span class="material-icons-outlined text-slate-200 dark:text-slate-700 text-xl">verified</span>
            </div>
          </div>
        </div>

        <!-- Certifications -->
        <div class="bg-white dark:bg-slate-800 rounded-sm shadow-refined border border-slate-200/60 dark:border-slate-700 p-4">
          <div class="flex items-center gap-2 mb-3">
            <span class="material-icons-outlined text-primary text-lg">military_tech</span>
            <h3 class="font-bold text-sm text-slate-900 dark:text-white">Certifications</h3>
          </div>
          <ul class="space-y-2.5">
            @forelse(($certs ?? collect())->take(3) as $c)
              <li class="flex justify-between items-center">
                <span class="text-sm font-bold text-slate-800 dark:text-slate-300">{{ $c->qlf_name ?? $c->qlf_type }}</span>
                <span class="px-2 py-0.5 bg-emerald-50 dark:bg-emerald-900/20 text-emerald-600 dark:text-emerald-400 rounded-sm text-[9px] font-black uppercase">
                  {{ !empty($c->qlf_enddt) ? \Carbon\Carbon::parse($c->qlf_enddt)->format('Y') : '—' }}
                </span>
              </li>
            @empty
              <li class="flex justify-between items-center">
                <span class="text-sm font-bold text-slate-800 dark:text-slate-300">—</span>
                <span class="px-2 py-0.5 bg-slate-50 dark:bg-slate-700 text-slate-600 dark:text-slate-300 rounded-sm text-[9px] font-black uppercase">N/A</span>
              </li>
            @endforelse
          </ul>
        </div>
      </div>

      <!-- Assigned Assets -->
      <div class="bg-white dark:bg-slate-800 rounded-sm shadow-refined border border-slate-200/60 dark:border-slate-700 p-4 overflow-hidden">
        <div class="flex items-center gap-2 mb-3">
          <span class="material-icons-outlined text-primary text-lg">devices</span>
          <h3 class="font-bold text-sm text-slate-900 dark:text-white">Assigned Assets</h3>
        </div>
        <div class="overflow-x-auto -mx-4">
          <table class="w-full text-left text-xs">
            <thead class="bg-slate-50 dark:bg-slate-700/50 border-b border-slate-100 dark:border-slate-700">
              <tr>
                <th class="px-4 py-2 font-bold text-[9px] uppercase tracking-widest text-slate-400">Device</th>
                <th class="px-4 py-2 font-bold text-[9px] uppercase tracking-widest text-slate-400">Serial</th>
                <th class="px-4 py-2 font-bold text-[9px] uppercase tracking-widest text-slate-400">Issued On</th>
                <th class="px-4 py-2 font-bold text-[9px] uppercase tracking-widest text-slate-400">Condition</th>
                <th class="px-4 py-2 font-bold text-[9px] uppercase tracking-widest text-slate-400 text-center">Status</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-slate-50 dark:divide-slate-700/50">
              @forelse(($vehicles ?? collect()) as $v)
                <tr class="hover:bg-slate-50/50 dark:hover:bg-slate-700/20 transition-colors">
                  <td class="px-4 py-2.5 font-bold text-slate-900 dark:text-white text-xs">{{ trim(($v->vcl_maker ?? '').' '.($v->vcl_variant ?? '')) ?: '—' }}</td>
                  <td class="px-4 py-2.5 text-slate-500 dark:text-slate-400 font-mono text-xs">{{ $v->vcl_regis ?? '—' }}</td>
                  <td class="px-4 py-2.5 text-slate-500 dark:text-slate-400 text-xs">{{ !empty($v->vcl_year) ? $v->vcl_year : '—' }}</td>
                  <td class="px-4 py-2.5"><span class="px-2 py-0.5 bg-emerald-50 dark:bg-emerald-900/20 text-emerald-600 dark:text-emerald-400 rounded-sm text-[9px] font-black uppercase">Healthy</span></td>
                  <td class="px-4 py-2.5 text-center"><span class="px-2 py-0.5 bg-blue-50 dark:bg-blue-900/20 text-blue-600 dark:text-blue-400 rounded-sm text-[9px] font-black uppercase">Assigned</span></td>
                </tr>
              @empty
                <tr><td class="px-4 py-2.5 text-center text-slate-500 text-xs" colspan="5">No assets</td></tr>
              @endforelse
            </tbody>
          </table>
        </div>
      </div>

    </div>
  </div>
</div>
</body>
</div>
@endsection
