@extends('welcome')

@section('content')
<div class="content-wrapper pt-2">

  <title>Employee Profile - Jonathan Pierce</title>
  <script src="https://cdn.tailwindcss.com?plugins=forms,typography,container-queries"></script>
  <link
    href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800;900&amp;family=Material+Icons+Outlined&amp;display=swap"
    rel="stylesheet" />
  <script>
    tailwind.config = {
      darkMode: "class",
      theme: {
        extend: {
          colors: {
            primary: "var(--rd-accent)",
            "background-light": "var(--rd-bg-light)",
            "background-dark": "var(--rd-bg)",
          },
          fontFamily: {
            display: ["Inter", "sans-serif"],
          },
          borderRadius: {
            DEFAULT: "0.625rem",
          },
          boxShadow: {
            'refined': '0 4px 6px -1px rgb(0 0 0 / 0.05), 0 2px 4px -2px rgb(0 0 0 / 0.05)',
            'card-hover': '0 10px 15px -3px rgb(0 0 0 / 0.08), 0 4px 6px -4px rgb(0 0 0 / 0.08)',
          }
        },
      },
    };
  </script>
  <style>
    body {
      font-family: 'Inter', sans-serif;
      -webkit-font-smoothing: antialiased;
    }

    .chart-bar-utilized {
      height: 70%;
      transition: height 0.3s ease;
    }

    .chart-bar-total {
      height: 100%;
      transition: height 0.3s ease;
    }

    .glass-card {
      background: rgba(255, 255, 255, 0.1);
      backdrop-filter: blur(8px);
    }

    .dark .glass-card {
      background: rgba(30, 41, 59, 0.4);
    }

    .contract-overflow {
      height: 50px;
      overflow: hidden;
    }

    .contracts-scroll {
      overflow-y: auto;
      -ms-overflow-style: none;
      scrollbar-width: none;
    }

    .contracts-scroll::-webkit-scrollbar {
      display: none;
    }

    /* leave utilization donut chart */
    .leave-chart {
      width: 120px;
      height: 120px;
      border-radius: 50%;
      background: conic-gradient(
        var(--rd-accent) 0 50%,      /* annual */
        #3b82f6 50% 57.5%,  /* sick */
        #60a5fa 57.5% 62.5%,/* casual */
        var(--rd-surface3) 62.5% 100%  /* remaining */
      );
      position: relative;
    }

    
  </style>
</head>

<body
  class="dark bg-background-light dark:bg-background-dark text-white dark:text-white min-h-screen transition-colors duration-200">

  <div class="max-w-[1600px] mx-auto p-3 sm:p-6 pb-20 sm:pb-6">
    <header class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 mb-6">
      <div class="flex items-center gap-2">
        <span class="text-white/60 dark:text-white/60 font-medium text-sm">Employees /</span>
        <h1 class="text-xl font-bold text-white dark:text-white tracking-tight" style="font-family: 'Rajdhani', sans-serif;">{{ $emp->emp_name ?? 'Jonathan Pierce' }}</h1>
      </div>
      <div class="flex gap-2 w-full sm:w-auto">
        <button onclick="history.back()"
          class="flex-1 sm:flex-initial px-4 py-2 text-xs font-semibold text-white bg-slate-800 border border-slate-700 rounded-lg shadow-refined hover:bg-slate-700 transition-all">
          <span class="material-icons-outlined text-base align-middle mr-1">arrow_back</span> Back
        </button>
        <button
          class="flex-1 sm:flex-initial px-5 py-2 text-xs font-semibold text-white bg-primary rounded-lg shadow-lg shadow-blue-500/20 hover:opacity-90 transition-all">
          Edit Profile
        </button>
      </div>
    </header>
    <div class="grid grid-cols-12 gap-6">
      <div class="col-span-12 lg:col-span-3 space-y-7">
        <!-- ===== EMPLOYEE PROFILE CARD ===== -->
        <div
          class="bg-white dark:bg-[var(--rd-surface)]/50 border-slate-700 p-6 overflow-hidden relative text-center">
          <div class="absolute top-0 left-0 w-full h-1 bg-primary"></div>
          <div class="relative mb-4 inline-block">
            <img alt="{{ $emp->emp_name ?? 'Employee' }}"
              class="w-20 h-20 rounded-full border-4 border-slate-50 dark:border-slate-900 shadow-md object-cover"
              src="{{ $emp->emp_photodest ? asset($emp->emp_photodest) : 'https://ui-avatars.com/api/?name='.urlencode($emp->emp_name ?? 'Employee').'&background=2563eb&color=ffffff' }}" />
          </div>
          <h2 class="text-lg font-bold text-white dark:text-white leading-tight">{{ $emp->emp_name ?? $id }}</h2>
          <p class="text-sm text-white dark:text-white mt-1">{{ $emp->emp_title ?? ($emp->emp_rank ?? '') }}</p>
          <div class="mt-4 space-y-6 pt-4 border-t border-slate-100 dark:border-slate-700">
            <div class="flex justify-between text-xs py-0.5">
              <span class="text-white font-medium text-left">Employee ID</span>
              <span class="font-bold text-white dark:text-white">{{ $emp->emp_id ?? $id }}</span>
            </div>
            <div class="flex justify-between text-xs py-0.5">
              <span class="text-white font-medium text-left">Joined</span>
              <span class="font-bold text-white dark:text-white">
                {{ !empty($emp->emp_joindt) ? \Carbon\Carbon::parse($emp->emp_joindt)->format('d-M-Y') : '—' }}
              </span>
            </div>
            <div class="flex justify-between text-xs py-0.5">
              <span class="text-white font-medium text-left">CNIC</span>
              <span class="font-bold text-white dark:text-white">{{ $emp->emp_cnic ?? '—' }}</span>
            </div>
            <div class="flex justify-between text-xs py-0.5">
              <span class="text-white font-medium text-left">Personal Contact</span>
              <span class="font-bold text-white dark:text-white">{{ $empA->emp_mobile ?? $empA->emp_mobile2 ?? '—' }}</span>
            </div>
          </div>
        </div>

        <div
          class="bg-white dark:bg-[var(--rd-surface)]/50 border-slate-700 p-4">
          <div class="flex items-center gap-2 mb-3">
            <span class="material-icons-outlined text-primary text-lg">phone_in_talk</span>
            <h3 class="font-bold text-sm text-white dark:text-white">SOS</h3>
          </div>
          @if(!empty($kin) || !empty($emer))
            @if($kinSame && !empty($kin))
              <div class="space-y-3">
                <div class="flex justify-between text-xs py-0.5">
                  <span class="text-white font-medium">Relation</span>
                  <span class="font-bold text-white dark:text-white">{{ $kin['relation'] ?? '—' }}</span>
                </div>
                <div class="flex justify-between text-xs py-0.5">
                  <span class="text-white font-medium">Phone</span>
                  <span class="font-bold text-white dark:text-white">{{ $emer['mobile'] ?? '—' }}</span>
                </div>
              </div>
            @else
              <div class="space-y-3">
                <div>
                  <div class="text-[10px] font-black uppercase text-white mb-1">Next of Kin</div>
                  <div class="flex justify-between text-xs py-0.5">
                    <span class="text-white font-medium">Name</span>
                    <span class="font-bold text-white dark:text-white">{{ $kin['name'] ?? '—' }}</span>
                  </div>
                  <div class="flex justify-between text-xs py-0.5">
                    <span class="text-white font-medium">Relation</span>
                    <span class="font-bold text-white dark:text-white">{{ $kin['relation'] ?? '—' }}</span>
                  </div>
                  <div class="flex justify-between text-xs py-0.5">
                    <span class="text-white font-medium">CNIC</span>
                    <span class="font-bold text-white dark:text-white">{{ $kin['cnic'] ?? '—' }}</span>
                  </div>
                </div>
                <div>
                  <div class="text-[10px] font-black uppercase text-white mb-1">Emergency Contact</div>
                  <div class="flex justify-between text-xs py-0.5">
                    <span class="text-white font-medium">Name</span>
                    <span class="font-bold text-white dark:text-white">{{ $emer['name'] ?? '—' }}</span>
                  </div>
                  <div class="flex justify-between text-xs py-0.5">
                    <span class="text-white font-medium">Relation</span>
                    <span class="font-bold text-white dark:text-white">{{ $emer['relation'] ?? '—' }}</span>
                  </div>
                  <div class="flex justify-between text-xs py-0.5">
                    <span class="text-white font-medium">Phone</span>
                    <span class="font-bold text-white dark:text-white">{{ $emer['mobile'] ?? '—' }}</span>
                  </div>
                </div>
              </div>
            @endif
          @else
            <div class="text-xs text-white">No contact info</div>
          @endif
        </div>

        <div
          class="bg-white dark:bg-[var(--rd-surface)]/50 border-slate-700 p-4">
          <div class="flex items-center gap-2 mb-2">
            <span class="material-icons-outlined text-primary text-lg">assessment</span>
            <h3 class="font-bold text-white dark:text-white text-sm">Leave Utilization</h3>
          </div>
          <div class="flex flex-col items-center">
            <div style="width:70px;height:70px;border-radius:50%;background:conic-gradient(var(--rd-accent) 0 50%,#3b82f6 50% 57.5%,#60a5fa 57.5% 62.5%,var(--rd-surface3) 62.5% 100%);position:relative;" class="relative mb-3">
              <span class="absolute inset-0 flex items-center justify-center text-[10px] font-bold dark:text-white">25/40d</span>
            </div>
            <div class="flex flex-wrap justify-center gap-x-2 gap-y-1 text-[9px]">
              <div class="flex items-center gap-1">
                <span class="w-2 h-2 bg-primary rounded-full"></span>
                <span>Annual 20/25d</span>
              </div>
              <div class="flex items-center gap-1">
                <span class="w-2 h-2 bg-blue-400 rounded-full"></span>
                <span>Sick 3/10d</span>
              </div>
              <div class="flex items-center gap-1">
                <span class="w-2 h-2 bg-blue-300 rounded-full"></span>
                <span>Casual 2/5d</span>
              </div>
            </div>
          </div>
        </div>
      </div>
      <div class="col-span-12 lg:col-span-9 space-y-6">
        <div class="grid grid-cols-1 xl:grid-cols-12 gap-6">

          <div 
            class="xl:col-span-8 bg-white dark:bg-[var(--rd-surface)]/50 border-slate-700 p-4 h-[385px] flex flex-col overflow-hidden">
            <div class="flex items-center gap-2 mb-4">
              <span class="material-icons-outlined text-primary text-xl">description</span>
              <h3 class="font-bold text-white dark:text-white">Contract Details</h3>
            </div>
            <div
              class="bg-transparent p-3 rounded-xl border border-slate-700/50 mb-3">
              <div class="grid grid-cols-2 md:grid-cols-3 gap-3 mb-4">
                <div>
                  <p class="text-[9px] text-white/50 uppercase tracking-widest mb-1 font-bold">Department</p>
                  <p class="font-semibold text-white dark:text-white text-xs truncate">{{ $authUnit->unt_name ?? ($base->eff_unit_name ?? ($emp->unt_name ?? '—')) }}</p>
                </div>
                <div>
                  <p class="text-[9px] text-white/50 uppercase tracking-widest mb-1 font-bold">Designation</p>
                  <p class="font-semibold text-white dark:text-white text-xs truncate">{{ $currentContract->ctr_jobtitle ?? ($emp->emp_title ?? '—') }}</p>
                </div>
                <div class="col-span-2 md:col-span-1 border-t md:border-t-0 pt-2 md:pt-0 border-slate-700/50">
                  <p class="text-[9px] text-white/50 uppercase tracking-widest mb-1 font-bold">Next Review</p>
                  <p class="font-semibold text-white dark:text-white text-xs">
                    {{ !empty($currentContract->ctr_enddt) ? \Carbon\Carbon::parse($currentContract->ctr_enddt)->format('d-M-Y') : '—' }}
                  </p>
                </div>
              </div>
              <div
                class="grid grid-cols-1 md:grid-cols-2 gap-4 pt-3 border-t border-slate-100 dark:border-slate-700/50">
                <div>
                  <p class="text-[10px] text-white uppercase tracking-widest mb-1 font-bold">Current Annual Salary
                  </p>
                  @php
                    $monthlyNet = $base->srq_netsalary ?? $base->srq_salary ?? null;
                    $annualNet = $monthlyNet ? $monthlyNet * 12 : null;
                  @endphp
                  <p class="text-xl font-black text-white dark:text-white">
                    {{ $annualNet ? number_format($annualNet) : '—' }}
                  </p>
                </div>
                <div>
                  <p class="text-[10px] text-white uppercase tracking-widest mb-1.5 font-bold">Probation Period</p>
                  @php
                    $probRaw = $currentContract->ctr_prob ?? null;
                    $probPct = null;
                    if (is_numeric($probRaw) && $probRaw >= 0 && $probRaw <= 100) {
                      $probPct = (int)$probRaw;
                    } elseif (!empty($probRaw) && !empty($currentContract->ctr_startdt)) {
                      $months = (int)$probRaw;
                      $daysTotal = max(1, $months * 30);
                      $daysPassed = \Carbon\Carbon::parse($currentContract->ctr_startdt)->diffInDays(now());
                      $probPct = max(0, min(100, (int)round(($daysPassed / $daysTotal) * 100)));
                    }
                  @endphp
                  <div class="w-full bg-slate-200 dark:bg-[var(--rd-surface2)] h-2 rounded-full overflow-hidden mt-1.5">
                    <div class="bg-emerald-500 h-full flex items-center justify-end pr-1.5" style="width: {{ $probPct ?? 0 }}%">
                      <span class="text-[7px] text-white font-bold">{{ $probPct !== null ? ($probPct.'%') : '—' }}</span>
                    </div>
                  </div>
                </div>
              </div>
            </div>
            <div class="flex-1 min-h-0">
              <h4 class="text-[10px] text-white uppercase tracking-widest mb-3 font-bold">Previous Contracts History
              </h4>
              <div id="contractsWrapper"
                class="border border-slate-100 dark:border-slate-700/50 rounded-xl relative contracts-scroll" style="height: 140px;">
                <table class="w-full text-left text-[11px]">
                  <thead
                    class="sticky top-0 z-10 bg-slate-50/80 dark:bg-[var(--rd-surface2)]/50 border-b border-slate-100 dark:border-slate-700"
                    style="background-color:var(--rd-surface2)">
                    <tr>
                      <th class="px-4 py-2 font-bold text-[9px] text-white uppercase">Role</th>
                      <th class="px-4 py-2 font-bold text-[9px] text-white uppercase">Salary</th>
                      <th class="px-3 py-2 font-bold text-[9px] text-white uppercase">Start</th>
                      <th class="px-3 py-2 font-bold text-[9px] text-white uppercase">End</th>
                      <th class="px-2 py-2 pr-12 font-bold text-[9px] text-white uppercase text-center relative">
                        Status
                        <span class="absolute right-2 top-1 flex gap-1.5">
                          <button id="contractScrollUp"
                            class="w-5 h-5 rounded-full bg-slate-100 dark:bg-[var(--rd-surface2)] border border-slate-200 dark:border-slate-600 text-white dark:text-white dark:text-white flex items-center justify-center shadow-sm hover:bg-slate-200 dark:hover:bg-slate-600">
                            <span class="material-icons-outlined text-[12px] leading-none">arrow_upward</span>
                          </button>
                          <button id="contractScrollDown"
                            class="w-5 h-5 rounded-full bg-slate-100 dark:bg-[var(--rd-surface2)] border border-slate-200 dark:border-slate-600 text-white dark:text-white dark:text-white flex items-center justify-center shadow-sm hover:bg-slate-200 dark:hover:bg-slate-600">
                            <span class="material-icons-outlined text-[12px] leading-none">arrow_downward</span>
                          </button>
                        </span>
                      </th>
                    </tr>
                  </thead>
                  <tbody class="divide-y divide-slate-50 dark:divide-slate-700/50">
                    @forelse(($contractsHistory ?? collect()) as $c)
                      @php
                        $label = $c->status_label ?? '—';
                        $cls = $label === 'Active' ? 'bg-emerald-50 dark:bg-emerald-900/20 text-emerald-600 dark:text-emerald-400'
                              : ($label === 'Future' ? 'bg-blue-50 dark:bg-blue-900/20 text-blue-600 dark:text-blue-400'
                              : 'bg-slate-50 dark:bg-[var(--rd-surface2)] text-white dark:text-white dark:text-white');
                      @endphp
                      <tr class="hover:bg-slate-50/50 dark:hover:bg-slate-700/20 transition-colors">
                        <td class="px-2 py-2 font-medium text-white dark:text-white" style="max-width: 120px;">{{ $c->ctr_jobtitle ?? '—' }}</td>
                        <td class="px-3 py-2 font-bold text-white dark:text-white">{{ $c->ctr_salary ? number_format($c->ctr_salary) : '—' }}</td>
                        <td class="px-2 py-2 text-white">{{ !empty($c->ctr_startdt) ? \Carbon\Carbon::parse($c->ctr_startdt)->format('M Y') : '—' }}</td>
                        <td class="px-2 py-2 text-white">{{ !empty($c->ctr_enddt) ? \Carbon\Carbon::parse($c->ctr_enddt)->format('M Y') : '—' }}</td>
                        <td class="px-2 py-2 text-center">
                          <span style="margin-left: -30px;" class="px-2 py-0.5 {{ $cls }} rounded-full text-[8px] font-black uppercase tracking-wider">{{ $label }}</span>
                        </td>
                      </tr>
                    @empty
                      <tr><td colspan="5" class="px-3 py-2 text-center text-white">No contracts</td></tr>
                    @endforelse
                  </tbody>
                </table>
              </div>
            </div>
          </div>
          <div class="xl:col-span-4 h-[385px] flex flex-col overflow-hidden space-y-3">
            <div style="height:240px;"
              class="bg-white dark:bg-[var(--rd-surface)]/50 border-slate-700 p-3 flex flex-col">
              <div class="flex items-center justify-between mb-3">
                <div class="flex items-center gap-1.5">
                  <span class="material-icons-outlined text-primary text-lg">auto_graph</span>
                  <h3 class="font-bold text-sm text-white dark:text-white">Salary Progression</h3>
                </div>
                @php
                  $gFirst = $firstContract->ctr_salary ?? null;
                  $gLast = $lastContract->ctr_salary ?? null;
                  $g = ($gFirst && $gLast && $gFirst > 0) ? round((($gLast - $gFirst)/$gFirst)*100, 1) : null;
                @endphp
                <span
                  class="text-[8px] font-black text-emerald-500 bg-emerald-50 dark:bg-emerald-900/20 px-1.5 py-0.5 rounded-md border border-emerald-100/50 dark:border-emerald-800/50 uppercase tracking-tighter">Growth
                  {{ $g !== null ? ($g > 0 ? '+' : '').$g.'%' : '—' }}</span>
              </div>
              <div class="flex-grow flex items-end justify-center relative min-h-[90px] px-1 mb-3">
                <svg class="w-full h-full overflow-visible" preserveAspectRatio="none" viewBox="0 0 400 200">
                  <defs>
                    <linearGradient id="chartGradient" x1="0%" x2="0%" y1="0%" y2="100%">
                      <stop offset="0%" style="stop-color:#2563eb;stop-opacity:0.2"></stop>
                      <stop offset="100%" style="stop-color:#2563eb;stop-opacity:0"></stop>
                    </linearGradient>
                  </defs>
                  <path d="M 0,180 L 100,165 L 200,125 L 300,90 L 400,30" fill="none" stroke="#2563eb"
                    stroke-linecap="round" stroke-linejoin="round" stroke-width="3"></path>
                  <path d="M 0,180 L 100,165 L 200,125 L 300,90 L 400,30 V 200 H 0 Z" fill="url(#chartGradient)"></path>
                  <circle cx="0" cy="180" fill="white" r="6" stroke="#2563eb" stroke-width="2"></circle>
                  <circle cx="100" cy="165" fill="white" r="6" stroke="#2563eb" stroke-width="2"></circle>
                  <circle cx="200" cy="125" fill="white" r="6" stroke="#2563eb" stroke-width="2"></circle>
                  <circle cx="300" cy="90" fill="white" r="6" stroke="#2563eb" stroke-width="2"></circle>
                  <circle cx="400" cy="30" fill="white" r="6" stroke="#2563eb" stroke-width="2"></circle>
                </svg>
              </div>
              <div
                class="flex justify-between text-[7px] text-white font-black uppercase tracking-widest border-t border-slate-50 dark:border-slate-700/50 pt-1.5 mb-1">
                <span>2020</span>
                <span>2021</span>
                <span>2022</span>
                <span>2023</span>
                <span>2024</span>
              </div>
              <div class="grid grid-cols-2 gap-2">
                <div style="display: flex; align-items: center; flex-direction: row-reverse; justify-content: space-between; height: 30px;"
                  class="p-1.5 bg-transparent rounded-lg border border-slate-700/50">
                  <p class="text-[7px] text-white uppercase font-black tracking-widest mb-0.5">Base Start</p>
                  <p class="text-xs font-black text-white dark:text-white">{{ !empty($firstContract?->ctr_salary) ? number_format($firstContract->ctr_salary) : '—' }}</p>
                </div>
                <div style="display: flex; align-items: center; flex-direction: row-reverse; justify-content: space-between; height: 30px;"
                  class="p-1.5 bg-blue-50/50 dark:bg-blue-900/20 rounded-lg border border-blue-100/30 dark:border-blue-800/30">
                  <p class="text-[7px] text-primary uppercase font-black tracking-widest mb-0.5">Current</p>
                  <p class="text-xs font-black text-white dark:text-white">{{ !empty($lastContract?->ctr_salary) ? number_format($lastContract->ctr_salary) : '—' }}</p>
                </div>
              </div>
            </div>
            <div
              class="bg-white dark:bg-[var(--rd-surface)]/50 border-slate-700 p-3 flex-1 min-h-0">
              <div class="flex items-center gap-2 mb-2">
                <h3 class="font-bold text-sm text-white dark:text-white">Previous Projects</h3>
              </div>
              <div id="projectsWrapper" class="border border-slate-100 dark:border-slate-700/50 rounded-xl relative contracts-scroll" style="height: 120px;">
                <table class="w-full text-left text-[11px]">
                  <thead class="sticky top-0 z-10 bg-slate-50 dark:bg-[var(--rd-surface2)]/50 border-b border-slate-100 dark:border-slate-700">
                    <tr>
                      <th class="px-3 py-1.5 font-bold text-[9px] text-white uppercase">Project</th>
                      <th class="px-3 py-1.5 font-bold text-[9px] text-white uppercase">Start</th>
                      <th class="px-3 py-1.5 font-bold text-[9px] text-white uppercase relative">End
                        <span class="absolute right-2 top-1 flex gap-0.5">
                          <button id="projectScrollUp" class="w-4 h-4 rounded-full bg-slate-100 dark:bg-[var(--rd-surface2)] border border-slate-200 dark:border-slate-600 text-white dark:text-white dark:text-white flex items-center justify-center shadow-sm hover:bg-slate-200 dark:hover:bg-slate-600">
                            <span class="material-icons-outlined text-[12px] leading-none">arrow_upward</span>
                          </button>
                          <button id="projectScrollDown" class="w-4 h-4 rounded-full bg-slate-100 dark:bg-[var(--rd-surface2)] border border-slate-200 dark:border-slate-600 text-white dark:text-white dark:text-white flex items-center justify-center shadow-sm hover:bg-slate-200 dark:hover:bg-slate-600">
                            <span class="material-icons-outlined text-[12px] leading-none">arrow_downward</span>
                          </button>
                        </span>
                      </th>
                    </tr>
                  </thead>
                  <tbody class="divide-y divide-slate-50 dark:divide-slate-700/50">
                    @forelse($previousProjects ?? [] as $pp)
                      <tr class="hover:bg-slate-50/50 dark:hover:bg-slate-700/20 transition-colors">
                        <td class="px-3 py-1.5 font-medium text-white dark:text-white" style="max-width: 160px;">{{ $pp->prj_title ?? '—' }}</td>
                        <td class="px-3 py-1.5 text-white">{{ !empty($pp->ctr_startdt) ? \Carbon\Carbon::parse($pp->ctr_startdt)->format('M Y') : '—' }}</td>
                        <td class="px-3 py-1.5 text-white">{{ !empty($pp->ctr_enddt) ? \Carbon\Carbon::parse($pp->ctr_enddt)->format('M Y') : '—' }}</td>
                      </tr>
                    @empty
                      <tr><td colspan="3" class="px-3 py-2 text-center text-white">No projects</td></tr>
                    @endforelse
                  </tbody>
                </table>
              </div>
            </div>
          </div>
        </div>
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
          <div class="lg:col-span-2 space-y-6">
            <!-- combined education + certification card container -->
            <!-- combined education + certification card container -->
            <div class="bg-white dark:bg-[var(--rd-surface)]/50 border-slate-700 p-4 sm:p-6">
              <div class="flex flex-col md:flex-row justify-center items-start gap-8 relative w-full">
                <div class="w-full md:w-1/2">
                  <!-- education card content -->
                  <div>
                    <div class="flex items-center gap-2 mb-4">
                      <span class="material-icons-outlined text-primary text-xl">school</span>
                      <h3 class="font-bold text-white dark:text-white text-sm">Education Details</h3>
                    </div>
                    <div class="space-y-4">
                      @php
                        $deg = ($degrees ?? collect())->first();
                      @endphp
                      <div class="grid grid-cols-2 lg:grid-cols-1 gap-2">
                        <div>
                          <p class="text-[9px] text-white/60 font-bold uppercase tracking-widest mb-1">Degree</p>
                          <p class="text-xs font-bold text-white dark:text-white">{{ $deg->qlf_name ?? '—' }}</p>
                        </div>
                        <div>
                          <p class="text-[9px] text-white/60 font-bold uppercase tracking-widest mb-1">Institution</p>
                          <p class="text-xs font-bold text-white dark:text-white">{{ $deg->qlf_inst ?? '—' }}</p>
                        </div>
                      </div>
                      <div class="flex justify-between items-center pt-2 border-t border-slate-700/50 lg:border-t-0 lg:pt-0">
                        <div>
                          <p class="text-[9px] text-white/60 font-bold uppercase tracking-widest mb-1">Duration</p>
                          <p class="text-xs font-bold text-white dark:text-white">
                            {{ !empty($deg?->qlf_duration) ? ($deg->qlf_duration.' '.($deg->qlf_unit ?? '')) : (!empty($deg?->qlf_enddt) ? \Carbon\Carbon::parse($deg->qlf_enddt)->format('Y') : '—') }}
                          </p>
                        </div>
                        <span class="material-icons-outlined text-emerald-400 text-2xl">verified</span>
                      </div>
                    </div>
                  </div>
                </div>
                <div class="hidden md:block absolute inset-y-0 left-1/2 w-px bg-slate-700/50"></div>
                <div class="w-full md:w-1/2 pt-6 md:pt-0 border-t md:border-t-0 border-slate-700/50">
                  <!-- certifications content -->
                  <div>
                    <div class="flex items-center gap-2 mb-4">
                      <span class="material-icons-outlined text-primary text-xl">military_tech</span>
                      <h3 class="font-bold text-white dark:text-white text-sm">Certifications</h3>
                    </div>
                    <ul class="space-y-4">
                      @forelse(($certs ?? collect())->take(5) as $ct)
                        <li class="flex justify-between items-center">
                          <span class="text-xs font-bold text-white dark:text-white truncate pr-2">{{ $ct->qlf_name ?? '—' }}</span>
                          <span class="px-2 py-0.5 bg-blue-900/20 text-blue-400 rounded-md text-[8px] font-black uppercase shrink-0">
                            {{ !empty($ct->qlf_enddt) ? \Carbon\Carbon::parse($ct->qlf_enddt)->format('Y') : ($ct->qlf_level ?? '—') }}
                          </span>
                        </li>
                      @empty
                        <li class="text-xs text-white/50">No certifications</li>
                      @endforelse
                    </ul>
                  </div>
                </div>
              </div>
            </div>
            <!-- specialized skills card full width -->
            <div class="bg-white dark:bg-[var(--rd-surface)]/50 border-slate-700 p-6">
              <div class="flex items-center gap-1.5 mb-0">
                <span class="material-icons-outlined text-primary text-xl">psychology</span>
                <h3 class="font-bold text-white dark:text-white">Specialized Skills</h3>
              </div>
              <div class="flex flex-wrap gap-1">
                <span class="px-2.5 py-1 bg-blue-50 dark:bg-blue-900/20 text-blue-600 dark:text-blue-400 text-[10px] font-semibold rounded-md border border-blue-100/50 dark:border-blue-900/50">Wireframing</span>
                <span class="px-2.5 py-1 bg-blue-50 dark:bg-blue-900/20 text-blue-600 dark:text-blue-400 text-[10px] font-semibold rounded-md border border-blue-100/50 dark:border-blue-900/50">User Research</span>
                <span class="px-2.5 py-1 bg-blue-50 dark:bg-blue-900/20 text-blue-600 dark:text-blue-400 text-[10px] font-semibold rounded-md border border-blue-100/50 dark:border-blue-900/50">Prototyping</span>
                <span class="px-2.5 py-1 bg-blue-50 dark:bg-blue-900/20 text-blue-600 dark:text-blue-400 text-[10px] font-semibold rounded-md border border-blue-100/50 dark:border-blue-900/50">Design Systems</span>
                <span class="px-2.5 py-1 bg-blue-50 dark:bg-blue-900/20 text-blue-600 dark:text-blue-400 text-[10px] font-semibold rounded-md border border-blue-100/50 dark:border-blue-900/50">Accessibility</span>
              </div>
            </div>
          </div>
          <!-- career details card remains -->
         <!-- Container holding both sections -->
<div class="flex flex-col gap-6">
  <!-- Career Details -->
  <div class="bg-white dark:bg-[var(--rd-surface)]/50 border-slate-700 p-6">
    <div class="flex items-center gap-2 mb-6">
      <span class="material-icons-outlined text-primary text-xl">work_outline</span>
      <h3 class="font-bold text-white dark:text-white">Career Details</h3>
    </div>
    <div class="space-y-4">
      <div class="flex justify-between items-center">
        <span class="text-[10px] text-white font-bold uppercase tracking-tight">Years in Service</span>
        <span class="text-xs font-bold text-white dark:text-white">{{ $yearsInService !== null ? $yearsInService.' years' : '—' }}</span>
      </div>
      <div class="flex justify-between items-center">
        <span class="text-[10px] text-white font-bold uppercase tracking-tight">Last Promotion</span>
        @php
          $lastProm = $lastContract->ctr_date ?? ($lastContract->ctr_startdt ?? ($lastContract->ctr_enddt ?? null));
        @endphp
        <span class="text-xs font-bold text-white dark:text-white">{{ $lastProm ? \Carbon\Carbon::parse($lastProm)->format('M Y') : '—' }}</span>
      </div>
      
      <div class="pt-4 mt-2 border-t border-slate-100 dark:border-slate-700/60">
        <div class="text-[10px] text-white font-black uppercase tracking-widest mb-2">Previous Jobs</div>
        <ul class="space-y-1.5">
          @forelse(($jobs ?? collect())->take(5) as $jb)
            <li class="flex items-center justify-between text-xs">
              <span class="font-semibold text-white dark:text-white" style="max-width: 55%; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;">
                {{ $jb->job_jobtitle ?? '—' }} @ {{ $jb->job_company ?? '—' }}
              </span>
              <span class="text-white">
                {{ !empty($jb->job_from) ? \Carbon\Carbon::parse($jb->job_from)->format('M Y') : '—' }}
                —
                {{ !empty($jb->job_to) ? \Carbon\Carbon::parse($jb->job_to)->format('M Y') : 'Present' }}
              </span>
            </li>
          @empty
            <li class="text-xs text-white">No previous jobs</li>
          @endforelse
        </ul>
      </div>
    </div>
  </div>

  <!-- Kin Details -->
  <div class="bg-white dark:bg-[var(--rd-surface)]/50 border-slate-700 p-6">
    <div class="flex items-center gap-2 mb-4">
      <span class="material-icons-outlined text-primary text-xl">people_outline</span>
      <h3 class="font-bold text-white dark:text-white">Kin Details</h3>
    </div>
    <div class="flex items-center gap-2 mb-3">
      <button id="kinTabNk" type="button" class="px-3 py-1.5 text-xs font-bold rounded-md border border-slate-600 bg-slate-700 text-white hover:bg-slate-600 transition-colors">Next of Kin</button>
      <button id="kinTabEc" type="button" class="px-3 py-1.5 text-xs font-bold rounded-md border border-slate-600 bg-slate-700 text-white hover:bg-slate-600 transition-colors">Emergency Contact</button>
    </div>
    <div id="kinPanelNk" class="space-y-1.5 hidden">
      <div class="text-[10px] text-white font-bold uppercase tracking-widest">Next of Kin</div>
      <div class="flex justify-between text-xs"><span class="text-white">Name</span><span class="font-bold text-white dark:text-white">{{ $kin['name'] ?? '—' }}</span></div>
      <div class="flex justify-between text-xs"><span class="text-white">Relation</span><span class="font-bold text-white dark:text-white">{{ $kin['relation'] ?? '—' }}</span></div>
      <div class="flex justify-between text-xs"><span class="text-white">CNIC</span><span class="font-bold text-white dark:text-white">{{ $kin['cnic'] ?? '—' }}</span></div>
    </div>
    <div id="kinPanelEc" class="space-y-1.5 hidden">
      <div class="text-[10px] text-white font-bold uppercase tracking-widest">Emergency Contact</div>
      <div class="flex justify-between text-xs"><span class="text-white">Name</span><span class="font-bold text-white dark:text-white">{{ $emer['name'] ?? '—' }}</span></div>
      <div class="flex justify-between text-xs"><span class="text-white">Relation</span><span class="font-bold text-white dark:text-white">{{ $emer['relation'] ?? '—' }}</span></div>
      <div class="flex justify-between text-xs"><span class="text-white">Phone</span><span class="font-bold text-white dark:text-white">{{ $emer['mobile'] ?? '—' }}</span></div>
    </div>
  </div>
</div>

<script data-cfasync="false" src="/cdn-cgi/scripts/5c5dd728/cloudflare-static/email-decode.min.js"></script>
        </div>
      </div>
    </div>
  </div>

  <script>
    document.addEventListener('DOMContentLoaded', function () {
      var wrapper = document.getElementById('contractsWrapper');
      if (!wrapper) return;
      var tbody = wrapper.querySelector('tbody');
      var thead = wrapper.querySelector('thead');
      var firstRow = tbody ? tbody.querySelector('tr') : null;
      var rowHeight = firstRow ? firstRow.offsetHeight : 40;
      var headerHeight = thead ? thead.offsetHeight : 24;
      var up = document.getElementById('contractScrollUp');
      var down = document.getElementById('contractScrollDown');
      var rowsCount = tbody ? tbody.querySelectorAll('tr').length : 0;
      if (rowsCount <= 2) {
        wrapper.style.height = (headerHeight + rowHeight * rowsCount) + 'px';
        wrapper.style.overflowY = 'hidden';
        if (up) up.style.display = 'none';
        if (down) down.style.display = 'none';
      } else {
        wrapper.style.height = (headerHeight + rowHeight * 2.5) + 'px';
        wrapper.style.overflowY = 'auto';
        if (up) up.style.display = '';
        if (down) down.style.display = '';
        if (up) up.addEventListener('click', function () { wrapper.scrollBy({ top: -rowHeight, behavior: 'smooth' }); });
        if (down) down.addEventListener('click', function () { wrapper.scrollBy({ top: rowHeight, behavior: 'smooth' }); });
      }
      window.addEventListener('resize', function () {
        var fr = tbody ? tbody.querySelector('tr') : null;
        var rh = fr ? fr.offsetHeight : rowHeight;
        var hh = thead ? thead.offsetHeight : headerHeight;
        var rCount = tbody ? tbody.querySelectorAll('tr').length : rowsCount;
        if (rCount <= 2) {
          wrapper.style.height = (hh + rh * rCount) + 'px';
          wrapper.style.overflowY = 'hidden';
          if (up) up.style.display = 'none';
          if (down) down.style.display = 'none';
        } else {
          wrapper.style.height = (hh + rh * 2.5) + 'px';
          wrapper.style.overflowY = 'auto';
          if (up) up.style.display = '';
          if (down) down.style.display = '';
        }
        rowHeight = rh;
        headerHeight = hh;
      });
    });
    document.addEventListener('DOMContentLoaded', function () {
      var pWrapper = document.getElementById('projectsWrapper');
      if (!pWrapper) return;
      var pTbody = pWrapper.querySelector('tbody');
      var pThead = pWrapper.querySelector('thead');
      var pFirstRow = pTbody ? pTbody.querySelector('tr') : null;
      var pRowHeight = pFirstRow ? pFirstRow.offsetHeight : 40;
      var pHeaderHeight = pThead ? pThead.offsetHeight : 24;
      pWrapper.style.height = '75px';
      var pUp = document.getElementById('projectScrollUp');
      var pDown = document.getElementById('projectScrollDown');
      if (pUp) pUp.addEventListener('click', function () { pWrapper.scrollBy({ top: -pRowHeight, behavior: 'smooth' }); });
      if (pDown) pDown.addEventListener('click', function () { pWrapper.scrollBy({ top: pRowHeight, behavior: 'smooth' }); });
      window.addEventListener('resize', function () {
        var pFr = pTbody ? pTbody.querySelector('tr') : null;
        var pRh = pFr ? pFr.offsetHeight : pRowHeight;
        var pHh = pThead ? pThead.offsetHeight : pHeaderHeight;
        pWrapper.style.height = '75px';
        pRowHeight = pRh;
        pHeaderHeight = pHh;
      });
    });
    document.addEventListener('DOMContentLoaded', function () {
      var nkBtn = document.getElementById('kinTabNk');
      var ecBtn = document.getElementById('kinTabEc');
      var nkPanel = document.getElementById('kinPanelNk');
      var ecPanel = document.getElementById('kinPanelEc');
      function activate(target) {
        if (!nkPanel || !ecPanel) return;
        nkPanel.classList.add('hidden');
        ecPanel.classList.add('hidden');
        if (target === 'nk') nkPanel.classList.remove('hidden');
        if (target === 'ec') ecPanel.classList.remove('hidden');
      }
      if (nkBtn) nkBtn.addEventListener('click', function(){ activate('nk'); });
      if (ecBtn) ecBtn.addEventListener('click', function(){ activate('ec'); });
    });
  </script>
</body>

</div>
@endsection
