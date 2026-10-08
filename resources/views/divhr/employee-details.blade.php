@extends('welcome')

@section('content')
<div class="content-wrapper pt-2 dark text-text1">

  <title>Employee Profile - {{ $emp->emp_name ?? 'Employee' }}</title>
  <script src="{{ asset('plugins/tailwind/tailwind-cdn.js') }}"></script>
  <link rel="stylesheet" href="{{ asset('css/fonts.css') }}">
  <script>
    tailwind.config = {
      darkMode: "class",
      theme: {
        extend: {
          colors: {
            primary: "var(--rd-accent)",
            surface: "var(--rd-surface)",
            surface2: "var(--rd-surface2)",
            surface3: "var(--rd-surface3)",
            surface4: "var(--rd-surface4)",
            border1: "var(--rd-border)",
            border2: "var(--rd-border2)",
            text1: "var(--rd-text1)",
            text2: "var(--rd-text2)",
            text3: "var(--rd-text3)",
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
      scrollbar-width: thin;
      scrollbar-color: #cbd5e1 transparent;
    }

    .contracts-scroll::-webkit-scrollbar {
      width: 6px;
    }

    .contracts-scroll::-webkit-scrollbar-track {
      background: transparent;
    }

    .contracts-scroll::-webkit-scrollbar-thumb {
      background-color: #cbd5e1;
      border-radius: 4px;
    }

    .contracts-scroll::-webkit-scrollbar-thumb:hover {
      background-color: #94a3b8;
    }

    /* leave utilization donut chart */
    .leave-chart {
      width: 120px;
      height: 120px;
      border-radius: 50%;
      background: conic-gradient(
        var(--rd-primary-600) 0 50%,      /* annual */
        var(--rd-primary-400) 50% 57.5%,  /* sick */
        var(--rd-primary-200) 57.5% 62.5%,/* casual */
        var(--rd-neutral-300) 62.5% 100%  /* remaining */
      );
      position: relative;
    }

    @media print {
      @page {
        size: A4 portrait;
        margin: 8mm 12mm;
      }
      html, body {
        background: #ffffff !important;
        color: #000000 !important;
        margin: 0 !important;
        padding: 0 !important;
        width: 100% !important;
        height: auto !important;
      }
      body * {
        visibility: hidden !important;
      }
      #employmentRecordModal,
      #employmentRecordModal *,
      #employmentRecordPrintArea,
      #employmentRecordPrintArea * {
        visibility: visible !important;
      }
      #employmentRecordModal {
        position: static !important;
        left: 0 !important;
        top: 0 !important;
        width: 100% !important;
        height: auto !important;
        max-height: none !important;
        background: #ffffff !important;
        padding: 0 !important;
        margin: 0 !important;
        display: block !important;
        overflow: visible !important;
        inset: auto !important;
        border: none !important;
        box-shadow: none !important;
      }
      #employmentRecordModal > div {
        border: none !important;
        box-shadow: none !important;
        max-width: 100% !important;
        max-height: none !important;
        margin: 0 !important;
        padding: 0 !important;
        border-radius: 0 !important;
      }
      #employmentRecordModal .overflow-y-auto {
        overflow: visible !important;
        padding: 0 !important;
        background: #ffffff !important;
      }
      #employmentRecordPrintArea {
        position: static !important;
        width: 100% !important;
        max-width: 100% !important;
        margin: 0 !important;
        padding: 0 !important;
        background: #ffffff !important;
        color: #000000 !important;
        box-shadow: none !important;
        border: none !important;
        display: block !important;
        page-break-after: avoid !important;
        page-break-inside: avoid !important;
        break-inside: avoid !important;
      }
      .contract-item-row {
        page-break-inside: avoid !important;
        break-inside: avoid !important;
      }
      .no-print, .modal-backdrop, header, nav, aside {
        display: none !important;
      }
    }
  </style>

  <div class="max-w-[1600px] mx-auto p-3 sm:p-6 pb-20 sm:pb-6">
    <header class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 mb-6">
      <div class="flex items-center gap-2">
        <span class="text-text3 font-medium text-sm">Employees /</span>
        <h1 class="text-xl font-bold text-text1 tracking-tight" style="font-family: 'Rajdhani', sans-serif;">{{ $emp->emp_name ?? 'Jonathan Pierce' }}</h1>
      </div>
      <div class="flex flex-wrap items-center gap-2 w-full sm:w-auto">
        <!-- 1. Employee Documents Dropdown (sb sy pehly) -->
        <div class="relative inline-block text-left" id="employeeDocsDropdownWrap">
          <button type="button" id="employeeDocsToggleBtn"
            class="flex-1 sm:flex-initial px-3.5 py-2 text-xs font-bold text-sky-800 bg-sky-100 hover:bg-sky-200 border border-sky-300 rounded-lg shadow-sm transition-all inline-flex items-center justify-center gap-1.5 cursor-pointer">
            <i class="fas fa-folder-open text-sky-600"></i> Employee Documents
            <i class="fas fa-chevron-down text-[10px] ml-1 transition-transform duration-200" id="employeeDocsChevron"></i>
          </button>
          <div id="employeeDocsDropdownMenu" class="origin-top-left absolute left-0 mt-1.5 w-72 rounded-xl shadow-2xl bg-white border border-slate-200 z-50 p-2.5" style="display: none;">
            <div class="text-[10px] font-bold text-slate-500 uppercase tracking-wider px-2 py-1 border-b border-slate-100 mb-1 flex justify-between items-center">
              <span>Attached Documents</span>
              <span class="text-[9px] px-1.5 py-0.5 bg-sky-50 text-sky-600 rounded font-bold">{{ count($attachments ?? []) }} Files</span>
            </div>
            <div class="max-h-56 overflow-y-auto space-y-1 mb-2">
              @php
                $attSlots = ['Appointment Letter', 'Form', 'CV', 'Minute'];
                $attMap = collect($attachments ?? [])->keyBy('eat_type');
              @endphp
              @foreach($attSlots as $slot)
                @php $existing = $attMap->get($slot); @endphp
                @if($existing)
                  <a href="{{ route('universal.attachment.view', ['module' => 'emp', 'id' => $existing->eat_id]) }}" target="_blank"
                    class="flex items-center justify-between p-2 rounded text-xs hover:bg-sky-50 text-slate-700 transition">
                    <span class="flex items-center gap-1.5 truncate">
                      <i class="fas fa-file-pdf text-rose-500"></i>
                      <span class="truncate font-medium">{{ $slot }}</span>
                    </span>
                    <span class="text-[10px] text-sky-600 font-bold flex-shrink-0">View <i class="fas fa-external-link-alt ml-0.5 text-[8px]"></i></span>
                  </a>
                @else
                  <div class="flex items-center justify-between p-2 rounded text-xs text-slate-400">
                    <span class="flex items-center gap-1.5">
                      <i class="far fa-file text-slate-300"></i>
                      <span>{{ $slot }}</span>
                    </span>
                    <span class="text-[9px] italic">Not uploaded</span>
                  </div>
                @endif
              @endforeach
            </div>
            <div class="pt-2 border-t border-slate-100 flex flex-col gap-1">
              <button type="button" onclick="document.getElementById('docsUploadModal').classList.remove('hidden'); document.getElementById('employeeDocsDropdownMenu').style.display='none';"
                class="w-full text-center py-1.5 px-2 bg-sky-600 hover:bg-sky-700 text-white rounded text-[11px] font-bold transition flex items-center justify-center gap-1 cursor-pointer">
                <i class="fas fa-upload text-[10px]"></i> Manage & Upload Documents
              </button>
              <button type="button" onclick="document.getElementById('officialDemographicsModal').classList.remove('hidden'); document.getElementById('employeeDocsDropdownMenu').style.display='none';"
                class="w-full text-center py-1.5 px-2 bg-slate-100 hover:bg-slate-200 text-slate-700 rounded text-[11px] font-semibold transition flex items-center justify-center gap-1 cursor-pointer">
                <i class="fas fa-shield-alt text-[10px] text-primary"></i> View Security & Demographics
              </button>
            </div>
          </div>
        </div>

        @if($canEdit ?? false)
          {{-- 2. Edit Profile button (usky bad) --}}
          <a href="{{ route('divhr.employee.edit', $emp->emp_id ?? $id) }}"
            class="flex-1 sm:flex-initial px-4 py-2 text-xs font-bold text-white bg-primary rounded-lg shadow-lg shadow-blue-500/20 hover:opacity-90 transition-all inline-flex items-center justify-center gap-1.5 text-decoration-none">
            <i class="fas fa-edit mr-1"></i> Edit Profile
          </a>

          @if(in_array(strtolower($emp->emp_status ?? 'active'), ['active', 'current']))
            {{-- Contract Renewal (Cr) button --}}
            <a href="{{ route('division.contract-cases.create', ['type' => 'Cr', 'emp_id' => $emp->emp_id ?? $id]) }}"
              class="flex-1 sm:flex-initial px-3.5 py-2 text-xs font-bold text-amber-900 bg-amber-100 hover:bg-amber-200 border border-amber-300 rounded-lg shadow-sm transition-all inline-flex items-center justify-center gap-1.5 text-decoration-none"
              title="Initiate Contract Renewal (Cr) Case for this employee">
              <i class="fas fa-sync-alt text-amber-700"></i> Renew Contract (Cr)
            </a>

            {{-- Contract Extension (Ce) button --}}
            <a href="{{ route('division.contract-cases.create', ['type' => 'Ce', 'emp_id' => $emp->emp_id ?? $id]) }}"
              class="flex-1 sm:flex-initial px-3.5 py-2 text-xs font-bold text-emerald-900 bg-emerald-100 hover:bg-emerald-200 border border-emerald-300 rounded-lg shadow-sm transition-all inline-flex items-center justify-center gap-1.5 text-decoration-none"
              title="Initiate Contract Extension (Ce) Case for this employee">
              <i class="fas fa-calendar-plus text-emerald-700"></i> Extend Contract (Ce)
            </a>
          @else
            {{-- Rehiring (Rh) button --}}
            <a href="{{ route('division.contract-cases.create', ['type' => 'Rh', 'emp_id' => $emp->emp_id ?? $id]) }}"
              class="flex-1 sm:flex-initial px-3.5 py-2 text-xs font-bold text-cyan-900 bg-cyan-100 hover:bg-cyan-200 border border-cyan-300 rounded-lg shadow-sm transition-all inline-flex items-center justify-center gap-1.5 text-decoration-none"
              title="Initiate Rehiring (Rh) Case for this separated employee">
              <i class="fas fa-user-plus text-cyan-700"></i> Rehire Employee (Rh)
            </a>
          @endif
        @endif

        @can('initiate', \App\Models\AudRev::class)
          <button type="button" onclick="document.getElementById('reverseEmployeeModal').classList.remove('hidden')"
            class="flex-1 sm:flex-initial px-3.5 py-2 text-xs font-bold text-rose-300 bg-rose-950/40 hover:bg-rose-900/60 border border-rose-700/50 rounded-lg shadow-sm transition-all inline-flex items-center justify-center gap-1.5"
            title="Initiate Data Revision for Employee">
            <i class="fas fa-sync-alt text-rose-400"></i> Reverse Employee
          </button>
        @endcan

        <button onclick="history.back()"
          class="flex-1 sm:flex-initial px-3.5 py-2 text-xs font-semibold text-text1 bg-surface3 border border-border2 rounded-lg shadow-refined hover:bg-surface4 transition-all">
          <i class="fas fa-arrow-left mr-1"></i> Back
        </button>
      </div>
    </header>
    <div class="grid grid-cols-12 gap-6">
      <div class="col-span-12 lg:col-span-3 space-y-5">
        <!-- ===== EMPLOYEE PROFILE CARD ===== -->
        <div
          class="bg-surface border border-border1 rounded-xl p-6 overflow-hidden relative text-center">
          <div class="absolute top-0 left-0 w-full h-1 bg-primary"></div>
          <div class="relative mb-4 inline-block group">
            <div class="relative w-36 h-36 mx-auto rounded-2xl overflow-hidden border-4 border-surface2 shadow-lg" style="background: #e2e8f0;">
              <img alt="{{ $emp->emp_name ?? 'Employee' }}"
                class="w-full h-full object-cover"
                src="{{ \App\Facades\FileStorage::url($emp->emp_photodest) ?: asset('dist/img/avatar.png') }}"
                onerror="this.onerror=null; this.src='{{ asset('dist/img/avatar.png') }}';" />
              
              {{-- Hover Overlay to Upload / Change Photo --}}
              <label for="emp_photo_input" class="absolute inset-0 bg-black bg-opacity-40 text-white flex flex-col items-center justify-center cursor-pointer opacity-0 group-hover:opacity-100 transition duration-200" title="Click to Upload / Change Photo">
                <i class="fas fa-camera text-xl mb-1"></i>
                <span class="text-[10px] font-bold uppercase tracking-wider">Upload</span>
              </label>
            </div>

            {{-- Small Camera Badge for Immediate Visual Cue --}}
            <label for="emp_photo_input" class="absolute bottom-[-4px] right-[-4px] bg-primary text-white rounded-full cursor-pointer shadow-md hover:bg-primary-600 transition flex items-center justify-center" title="Click to Upload Photo" style="width: 32px; height: 32px; font-size: 13px; border: 2px solid #ffffff;">
              <i class="fas fa-camera"></i>
            </label>

            <form action="{{ route('divhr.employee.upload_photo', $emp->emp_id ?? $id) }}" method="POST" enctype="multipart/form-data" class="hidden" id="emp_photo_form">
              @csrf
              <input type="file" id="emp_photo_input" name="photo" accept="image/jpeg,image/png,image/jpg,image/webp" onchange="document.getElementById('emp_photo_form').submit()">
            </form>
          </div>
          <h2 class="text-lg font-bold text-text1 leading-tight">{{ $emp->emp_name ?? $id }}</h2>
          <p class="text-sm text-text2 mt-1">{{ $emp->emp_title ?? ($emp->emp_rank ?? '') }}</p>
          <div class="mt-4 space-y-4 pt-4 border-t border-border1">
            <div class="flex justify-between text-xs py-0.5">
              <span class="text-text2 font-medium text-left">Employee ID</span>
              <span class="font-bold text-text1">{{ $emp->emp_id ?? $id }}</span>
            </div>
            <div class="flex justify-between text-xs py-0.5">
              <span class="text-text2 font-medium text-left">Joined</span>
              <span class="font-bold text-text1">
                {{ !empty($emp->emp_joindt) ? \Carbon\Carbon::parse($emp->emp_joindt)->format('d-M-Y') : '—' }}
              </span>
            </div>
            <div class="flex justify-between text-xs py-0.5">
              <span class="text-text2 font-medium text-left">CNIC</span>
              <span class="font-bold text-text1">{{ $emp->emp_cnic ?? '—' }}</span>
            </div>
            <div class="flex justify-between text-xs py-0.5">
              <span class="text-text2 font-medium text-left">Personal Contact</span>
              <span class="font-bold text-text1">{{ $empA?->emp_mobile ?? $empA?->emp_mobile2 ?? '—' }}</span>
            </div>
          </div>
        </div>

        <div
          class="bg-surface border border-border1 rounded-xl p-4">
          <div class="flex items-center gap-2 mb-3">
            <i class="fas fa-phone-alt text-primary text-lg mr-1.5"></i>
            <h3 class="font-bold text-sm text-text1">SOS</h3>
          </div>
          @if(!empty($kin) || !empty($emer))
            @if($kinSame && !empty($kin))
              <div class="space-y-3">
                <div class="flex justify-between text-xs py-0.5">
                  <span class="text-text2 font-medium">Relation</span>
                  <span class="font-bold text-text1">{{ $kin['relation'] ?? '—' }}</span>
                </div>
                <div class="flex justify-between text-xs py-0.5">
                  <span class="text-text2 font-medium">Phone</span>
                  <span class="font-bold text-text1">{{ $emer['mobile'] ?? '—' }}</span>
                </div>
              </div>
            @else
              <div class="space-y-3">
                <div>
                  <div class="text-[10px] font-black uppercase text-text3 mb-1">Next of Kin</div>
                  <div class="flex justify-between text-xs py-0.5">
                    <span class="text-text2 font-medium">Name</span>
                    <span class="font-bold text-text1">{{ $kin['name'] ?? '—' }}</span>
                  </div>
                  <div class="flex justify-between text-xs py-0.5">
                    <span class="text-text2 font-medium">Relation</span>
                    <span class="font-bold text-text1">{{ $kin['relation'] ?? '—' }}</span>
                  </div>
                  <div class="flex justify-between text-xs py-0.5">
                    <span class="text-text2 font-medium">CNIC</span>
                    <span class="font-bold text-text1">{{ $kin['cnic'] ?? '—' }}</span>
                  </div>
                </div>
                <div>
                  <div class="text-[10px] font-black uppercase text-text3 mb-1">Emergency Contact</div>
                  <div class="flex justify-between text-xs py-0.5">
                    <span class="text-text2 font-medium">Name</span>
                    <span class="font-bold text-text1">{{ $emer['name'] ?? '—' }}</span>
                  </div>
                  <div class="flex justify-between text-xs py-0.5">
                    <span class="text-text2 font-medium">Relation</span>
                    <span class="font-bold text-text1">{{ $emer['relation'] ?? '—' }}</span>
                  </div>
                  <div class="flex justify-between text-xs py-0.5">
                    <span class="text-text2 font-medium">Phone</span>
                    <span class="font-bold text-text1">{{ $emer['mobile'] ?? '—' }}</span>
                  </div>
                </div>
              </div>
            @endif
          @else
            <div class="text-xs text-text3">No contact info</div>
          @endif
        </div>

        <div
          class="bg-surface border border-border1 rounded-xl p-4">
          <div class="flex items-center gap-2 mb-2">
            <i class="fas fa-chart-pie text-primary text-lg mr-1.5"></i>
            <h3 class="font-bold text-text1 text-sm">Leave Utilization</h3>
          </div>
          <div class="flex flex-col items-center">
            <div style="width:70px;height:70px;border-radius:50%;background:conic-gradient(var(--rd-primary-600) 0 50%,var(--rd-primary-400) 50% 57.5%,var(--rd-primary-200) 57.5% 62.5%,var(--rd-neutral-300) 62.5% 100%);position:relative;" class="relative mb-3">
              <span class="absolute inset-0 flex items-center justify-center text-[10px] font-bold text-text1">25/40d</span>
            </div>
            <div class="flex flex-wrap justify-center gap-x-2 gap-y-1 text-[9px] text-text2">
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
            class="xl:col-span-8 bg-surface border border-border1 rounded-xl p-4 min-h-[385px] flex flex-col">
            <div class="flex items-center justify-between gap-2 mb-4">
              <div class="flex items-center gap-2">
                <i class="fas fa-file-contract text-primary text-lg mr-1.5"></i>
                <h3 class="font-bold text-text1">Contract Details</h3>
              </div>
              <div class="flex items-center gap-2 ml-auto">
                @if(($contractsHistory ?? collect())->count() <= 1)
                  <a href="{{ url('/divhr/employee/' . ($emp->emp_id ?? $id) . '/service-contract') }}" target="_blank"
                    title="View & Print Official Service Contract"
                    class="px-3 py-1.5 text-xs font-bold text-sky-700 bg-sky-50 hover:bg-sky-100 border border-sky-300 rounded-lg shadow-sm transition-all inline-flex items-center gap-1.5 cursor-pointer text-decoration-none">
                    <i class="fas fa-file-signature text-sky-600"></i> Service Contract
                  </a>
                @else
                  <button type="button" onclick="openServiceContractSelector()"
                    title="View & Print Official Service Contract"
                    class="px-3 py-1.5 text-xs font-bold text-sky-700 bg-sky-50 hover:bg-sky-100 border border-sky-300 rounded-lg shadow-sm transition-all inline-flex items-center gap-1.5 cursor-pointer">
                    <i class="fas fa-file-signature text-sky-600"></i> Service Contract
                  </button>
                @endif
                <button type="button" onclick="openEmploymentRecordModal()"
                  title="Print Contracts"
                  class="px-3 py-1.5 text-xs font-bold text-slate-700 bg-slate-100 hover:bg-slate-200 border border-slate-300 rounded-lg shadow-sm transition-all inline-flex items-center gap-1.5 cursor-pointer">
                  <i class="fas fa-print text-slate-600"></i> Employment History
                </button>
              </div>
            </div>
            <div
              class="bg-transparent p-3 rounded-xl border border-border1 mb-3">
              <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-4">
                <div>
                  <p class="text-[9px] text-text3 uppercase tracking-widest mb-1 font-bold">Hired Project Head</p>
                  @php
                    $detHeadCode = $currentContract?->ctr_hed_code ?: ($currentContract?->ctr_prj_code ?: ($emp?->hed_code ?: ($emp?->prj_code ?? null)));
                    $detPrjTitle = $currentContract?->ctr_prj_title ?: ($emp?->prj_title ?: ($currentContract?->ctr_hed_name ?: ($emp?->hed_name ?? null)));
                    $dPlans = $currentContractPlans ?? collect();
                    $dCount = (int)($distinctPlanCount ?? 0);
                  @endphp
                  <div class="flex items-center flex-wrap gap-1">
                    <p class="font-semibold text-text1 text-xs truncate max-w-[220px]" title="{{ $detPrjTitle }}">
                      @if($detHeadCode)
                        <span class="px-2 py-0.5 font-bold mr-1 rounded text-[10px] text-white shadow-xs" style="background-color: #0284c7;">{{ $detHeadCode }}</span>
                      @endif
                      {{ $detPrjTitle ?: ($detHeadCode ?: '—') }}
                    </p>
                    @if($dCount > 1 && $dPlans->isNotEmpty())
                      <div class="relative inline-block text-left" id="projectAllocationsDropdownWrap">
                        <button type="button" id="projectAllocationsToggleBtn" class="inline-flex items-center px-1.5 py-0.5 rounded text-[9px] font-bold bg-sky-500/10 text-sky-600 border border-sky-500/30 hover:bg-sky-500/20 cursor-pointer shadow-xs transition-colors">
                          <i class="fas fa-layer-group mr-1 text-[8px]"></i>{{ $dCount }} Projects
                          <i class="fas fa-chevron-down ml-1 text-[7px] transition-transform duration-200" id="projectAllocationsChevron"></i>
                        </button>
                        <div id="projectAllocationsDropdownMenu" class="origin-top-left absolute left-0 mt-1.5 w-80 rounded-xl shadow-2xl bg-white border border-slate-200 z-50 p-2.5 max-h-60 overflow-y-auto" style="display: none;">
                          <div class="text-[10px] font-bold text-slate-500 uppercase tracking-wider px-2 py-1 border-b border-slate-100 mb-1.5 flex justify-between items-center">
                            <span>Project Allocations</span>
                            <span class="text-[9px] px-1.5 py-0.5 bg-sky-50 text-sky-600 rounded font-bold">{{ $dPlans->count() }} {{ $dPlans->count() === 1 ? 'Period' : 'Periods' }}</span>
                          </div>
                          @foreach($dPlans as $dp)
                            @php
                              $isCur = is_array($dp) ? ($dp['is_current'] ?? false) : ($dp->is_current ?? false);
                              $dispCode = is_array($dp) ? ($dp['display_code'] ?? ($dp['code'] ?? '—')) : ($dp->display_code ?? ($dp->code ?? '—'));
                              $dispTitle = is_array($dp) ? ($dp['display_title'] ?? ($dp['title'] ?? '')) : ($dp->display_title ?? ($dp->title ?? ''));
                              $pStart = is_array($dp) ? ($dp['start_label'] ?? ($dp['month_label'] ?? '')) : ($dp->start_label ?? ($dp->month_label ?? ''));
                              $pEnd = is_array($dp) ? ($dp['end_label'] ?? ($dp['month_label'] ?? '')) : ($dp->end_label ?? ($dp->month_label ?? ''));
                              $mCount = is_array($dp) ? ($dp['months_count'] ?? 1) : ($dp->months_count ?? 1);
                              $periodStr = ($pStart === $pEnd) ? $pStart . ' (1 Mo)' : "From {$pStart} To {$pEnd} ({$mCount} Mos)";
                            @endphp
                            <div class="p-2 rounded text-[11px] mb-1 {{ $isCur ? 'bg-sky-50 border-l-2 border-sky-500' : 'hover:bg-slate-50 border-b border-slate-100' }}">
                              <div class="flex items-center justify-between gap-1.5 mb-1">
                                <div class="flex items-center gap-1.5 truncate">
                                  <span class="px-1.5 py-0.5 {{ $isCur ? 'bg-sky-600 text-white' : 'bg-slate-100 text-slate-700' }} rounded text-[9px] font-bold">{{ $dispCode }}</span>
                                  <span class="text-slate-800 font-medium truncate text-[10.5px]" title="{{ $dispTitle }}">{{ $dispTitle }}</span>
                                </div>
                                @if($isCur)
                                  <span class="text-[7.5px] font-bold text-emerald-600 uppercase flex-shrink-0 bg-emerald-50 px-1 rounded">Current</span>
                                @endif
                              </div>
                              <div class="text-[9.5px] text-slate-500 font-medium pl-1">
                                <i class="far fa-calendar-alt mr-1 text-slate-400"></i>{{ $periodStr }}
                              </div>
                            </div>
                          @endforeach
                        </div>
                      </div>
                    @endif
                  </div>
                </div>
                <div>
                  <p class="text-[9px] text-text3 uppercase tracking-widest mb-1 font-bold">Designation</p>
                  <p class="font-semibold text-text1 text-xs truncate" title="{{ $currentContract?->ctr_jobtitle ?? ($emp?->emp_title ?? '—') }}">
                    {{ $currentContract?->ctr_jobtitle ?? ($emp?->emp_title ?? '—') }}
                  </p>
                </div>
                <div>
                  <p class="text-[9px] text-text3 uppercase tracking-widest mb-1 font-bold">Start Date</p>
                  <p class="font-semibold text-text1 text-xs">
                    {{ !empty($currentContract?->ctr_startdt) ? \Carbon\Carbon::parse($currentContract?->ctr_startdt)->format('d-M-Y') : '—' }}
                  </p>
                </div>
                <div>
                  <p class="text-[9px] text-text3 uppercase tracking-widest mb-1 font-bold">End Date</p>
                  <div class="flex items-center gap-2">
                    <p class="font-semibold text-text1 text-xs">
                      {{ !empty($currentContract?->ctr_enddt) ? \Carbon\Carbon::parse($currentContract?->ctr_enddt)->format('d-M-Y') : '—' }}
                    </p>
                    <!-- Blinking Green Indicator for Active Contract -->
                    <span class="relative flex h-2.5 w-2.5 ml-1" title="Active Contract">
                      <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-emerald-400 opacity-75"></span>
                      <span class="relative inline-flex rounded-full h-2.5 w-2.5 bg-emerald-500 shadow-sm"></span>
                    </span>
                  </div>
                </div>
              </div>
              <div
                class="grid grid-cols-1 md:grid-cols-2 gap-4 pt-3 border-t border-border1">
                <div>
                  <p class="text-[10px] text-text2 uppercase tracking-widest mb-1 font-bold">Current Annual Salary</p>
                  @php
                    $monthlySalary = (float)($currentContract?->ctr_salary ?: ($lastContract?->ctr_salary ?: ($base?->srq_netsalary ?: ($base?->srq_salary ?: 0))));
                    $annualNet = $monthlySalary > 0 ? ($monthlySalary * 12) : null;
                  @endphp
                  <p class="text-xl font-black text-text1 leading-none">
                    {{ $annualNet ? number_format($annualNet) : '—' }}
                  </p>
                  @if($monthlySalary > 0)
                    <span class="text-[10px] text-text3 font-semibold block mt-1">({{ number_format($monthlySalary) }} / mo)</span>
                  @endif
                </div>
                <div>
                  <p class="text-[10px] text-text2 uppercase tracking-widest mb-1.5 font-bold">Probation Period</p>
                  @php
                    $probRaw = $currentContract?->ctr_prob ?? null;
                    $probPct = null;
                    if (is_numeric($probRaw) && $probRaw >= 0 && $probRaw <= 100) {
                      $probPct = (int)$probRaw;
                    } elseif (!empty($probRaw) && !empty($currentContract?->ctr_startdt)) {
                      $months = (int)$probRaw;
                      $daysTotal = max(1, $months * 30);
                      $daysPassed = \Carbon\Carbon::parse($currentContract?->ctr_startdt)->diffInDays(now());
                      $probPct = max(0, min(100, (int)round(($daysPassed / $daysTotal) * 100)));
                    }
                  @endphp
                  <div class="w-full bg-surface2 h-2 rounded-full overflow-hidden mt-1.5">
                    <div class="bg-emerald-500 h-full flex items-center justify-end pr-1.5" style="width: {{ $probPct ?? 0 }}%">
                      <span class="text-[7px] text-white font-bold">{{ $probPct !== null ? ($probPct.'%') : '—' }}</span>
                    </div>
                  </div>
                </div>
              </div>
            </div>
            <div class="flex-1 min-h-0">
              <h4 class="text-[10px] text-text3 uppercase tracking-widest mb-3 font-bold">Previous Contracts History</h4>
              <div id="contractsWrapper"
                class="border border-border1 rounded-xl relative contracts-scroll overflow-hidden" style="height: 185px; max-height: 220px; overflow-y: auto;">
                <table class="w-full text-left text-[11px] table-fixed">
                  <colgroup>
                    <col style="width: 25%;">
                    <col style="width: 35%;">
                    <col style="width: 16%;">
                    <col style="width: 9%;">
                    <col style="width: 9%;">
                    <col style="width: 6%;">
                  </colgroup>
                  <thead
                    class="sticky top-0 z-10 bg-surface2 border-b border-border1">
                    <tr>
                      <th class="px-2.5 py-2 font-bold text-[9px] text-text3 uppercase">Role / Grade</th>
                      <th class="px-2.5 py-2 font-bold text-[9px] text-text3 uppercase">Project Head</th>
                      <th class="px-2.5 py-2 font-bold text-[9px] text-text3 uppercase">Salary</th>
                      <th class="px-2 py-2 font-bold text-[9px] text-text3 uppercase">Start</th>
                      <th class="px-2 py-2 font-bold text-[9px] text-text3 uppercase">End</th>
                      <th class="px-2 py-2 font-bold text-[9px] text-text3 uppercase text-center relative" title="Contract Status">
                        <span class="inline-block w-2 h-2 rounded-full bg-slate-300" title="Indicator"></span>
                        <span class="absolute right-1 top-1 flex gap-1">
                          <button id="contractScrollUp"
                            class="w-5 h-5 rounded-full bg-surface3 border border-border2 text-text2 flex items-center justify-center shadow-sm hover:bg-surface4 hover:text-text1">
                            <i class="fas fa-arrow-up text-xs"></i>
                          </button>
                          <button id="contractScrollDown"
                            class="w-5 h-5 rounded-full bg-surface3 border border-border2 text-text2 flex items-center justify-center shadow-sm hover:bg-surface4 hover:text-text1">
                            <i class="fas fa-arrow-down text-xs"></i>
                          </button>
                        </span>
                      </th>
                    </tr>
                  </thead>
                  <tbody class="divide-y divide-border1">
                    @php
                      $prevContracts = ($contractsHistory ?? collect())->filter(function($c) use ($currentContract) {
                        return !$currentContract || $c->ctr_id != $currentContract->ctr_id;
                      });
                    @endphp
                    @forelse($prevContracts as $c)
                      @php
                        $cHead = $c->ctr_hed_code ?: ($c->ctr_prj_code ?? null);
                        $cPrj = $c->ctr_prj_title ?: ($c->ctr_hed_name ?: ($c->ctr_hed_code ?: ($c->ctr_prj_code ?? null)));
                      @endphp
                      <tr class="hover:bg-surface2/50 transition-colors">
                        <td class="px-2.5 py-2 font-medium text-text1">
                          <span class="font-bold block text-[11px] leading-tight" title="{{ $c->ctr_jobtitle ?? '—' }}">{{ $c->ctr_jobtitle ?? '—' }}</span>
                          @if(!empty($c->ctr_grade))
                            <span class="text-[9px] text-text3 block font-semibold">{{ $c->ctr_grade }}</span>
                          @endif
                        </td>
                        <td class="px-2.5 py-2 font-medium text-text1">
                          <div class="flex items-center gap-1.5 flex-wrap">
                            @if($cHead)
                              <span class="px-1.5 py-0.5 font-bold flex-shrink-0 rounded text-[9px] text-white shadow-xs" style="background-color: #0284c7;">{{ $cHead }}</span>
                            @endif
                            <span class="text-[10.5px] font-semibold text-text1 leading-snug break-words" title="{{ $cPrj }}">{{ $cPrj ?: ($cHead ?: '—') }}</span>
                          </div>
                        </td>
                        <td class="px-2.5 py-2 font-bold text-text1 whitespace-nowrap">{{ $c->ctr_salary ? number_format($c->ctr_salary) : '—' }}</td>
                        <td class="px-2 py-2 text-text2 whitespace-nowrap">{{ !empty($c->ctr_startdt) ? \Carbon\Carbon::parse($c->ctr_startdt)->format('M Y') : '—' }}</td>
                        <td class="px-2 py-2 text-text2 whitespace-nowrap">{{ !empty($c->ctr_enddt) ? \Carbon\Carbon::parse($c->ctr_enddt)->format('M Y') : '—' }}</td>
                        <td class="px-2 py-2 text-center whitespace-nowrap">
                          <!-- Stopped Red Circle Indicator -->
                          <span class="inline-block w-2.5 h-2.5 rounded-full bg-rose-500 shadow-sm" title="Completed / Past Contract"></span>
                        </td>
                      </tr>
                    @empty
                      <tr><td colspan="6" class="px-3 py-4 text-center text-text3">No previous contracts recorded</td></tr>
                    @endforelse
                  </tbody>
                </table>
              </div>
            </div>
          </div>
          <div class="xl:col-span-4 min-h-[385px] flex flex-col space-y-3">
            @php
              $pts = $salaryTimeline ?? [];
              $ptCount = count($pts);
              $firstSal = $ptCount > 0 ? (float)$pts[0]['salary'] : (float)($firstContract?->ctr_salary ?? 0);
              $lastSal = $ptCount > 0 ? (float)$pts[$ptCount - 1]['salary'] : (float)($lastContract?->ctr_salary ?? 0);
              
              $gPct = ($firstSal > 0 && $lastSal > 0) ? round((($lastSal - $firstSal) / $firstSal) * 100, 1) : null;

              $svgWidth = 360;
              $svgHeight = 130;
              $padX = 30;
              $padTop = 20;
              $padBtm = 20;
              $availW = $svgWidth - (2 * $padX);
              $availH = $svgHeight - $padTop - $padBtm;

              $salValues = array_map(fn($p) => (float)$p['salary'], $pts);
              $minS = !empty($salValues) ? min($salValues) : 0;
              $maxS = !empty($salValues) ? max($salValues) : 0;
              $rangeS = max(1, $maxS - $minS);

              $coords = [];
              if ($ptCount === 1) {
                $coords[] = ['x' => $padX + ($availW / 2), 'y' => $padTop + ($availH / 2), 'pt' => $pts[0]];
                $pathD = "M {$padX}," . ($padTop + ($availH / 2)) . " L " . ($svgWidth - $padX) . "," . ($padTop + ($availH / 2));
                $areaD = "M {$padX}," . ($padTop + ($availH / 2)) . " L " . ($svgWidth - $padX) . "," . ($padTop + ($availH / 2)) . " V {$svgHeight} H {$padX} Z";
              } elseif ($ptCount > 1) {
                foreach ($pts as $idx => $p) {
                  $cx = $padX + ($idx / ($ptCount - 1)) * $availW;
                  if ($maxS == $minS) {
                    $cy = $padTop + ($availH / 2);
                  } else {
                    $cy = ($padTop + $availH) - ((($p['salary'] - $minS) / $rangeS) * $availH);
                  }
                  $coords[] = ['x' => round($cx, 1), 'y' => round($cy, 1), 'pt' => $p];
                }
                $pathParts = [];
                foreach ($coords as $i => $c) {
                  $pathParts[] = ($i === 0 ? 'M ' : 'L ') . $c['x'] . ',' . $c['y'];
                }
                $pathD = implode(' ', $pathParts);
                $areaD = $pathD . " V {$svgHeight} H {$coords[0]['x']} Z";
              } else {
                $pathD = "";
                $areaD = "";
              }
            @endphp
            <div style="height:240px;"
              class="bg-surface border border-border1 rounded-xl p-3 flex flex-col">
              <div class="flex items-center justify-between mb-2">
                <div class="flex items-center gap-1.5">
                  <i class="fas fa-chart-line text-primary text-lg mr-1.5"></i>
                  <h3 class="font-bold text-sm text-text1">Salary Progression</h3>
                </div>
                <span
                  class="text-[8px] font-black text-emerald-400 bg-emerald-500/10 px-1.5 py-0.5 rounded-md border border-emerald-500/20 uppercase tracking-tighter">Growth
                  {{ $gPct !== null ? ($gPct >= 0 ? '+'.$gPct.'%' : $gPct.'%') : '0%' }}</span>
              </div>
              <div class="flex-grow flex items-end justify-center relative min-h-[90px] px-1 mb-2">
                @if(!empty($coords))
                  <svg class="w-full h-full overflow-visible" preserveAspectRatio="none" viewBox="0 0 {{ $svgWidth }} {{ $svgHeight }}">
                    <defs>
                      <linearGradient id="chartGradientDynamic" x1="0%" x2="0%" y1="0%" y2="100%">
                        <stop offset="0%" style="stop-color:var(--rd-accent);stop-opacity:0.28"></stop>
                        <stop offset="100%" style="stop-color:var(--rd-accent);stop-opacity:0.02"></stop>
                      </linearGradient>
                    </defs>
                    <path d="{{ $areaD }}" fill="url(#chartGradientDynamic)"></path>
                    <path d="{{ $pathD }}" fill="none" stroke="var(--rd-accent)"
                      stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5"></path>
                    @foreach($coords as $c)
                      <circle cx="{{ $c['x'] }}" cy="{{ $c['y'] }}" fill="var(--rd-surface)" r="5" stroke="var(--rd-accent)" stroke-width="2.5">
                        <title>{{ $c['pt']['year'] }}: PKR {{ number_format($c['pt']['salary']) }} ({{ $c['pt']['jobtitle'] }})</title>
                      </circle>
                    @endforeach
                  </svg>
                @else
                  <div class="text-xs text-text3 flex items-center justify-center h-full">No progression data</div>
                @endif
              </div>
              <div
                class="flex justify-between text-[8px] text-text3 font-black uppercase tracking-wider border-t border-border1 pt-1.5 mb-1 px-1">
                @forelse($pts as $p)
                  <span class="hover:text-primary transition" title="{{ $p['year'] }}: PKR {{ number_format($p['salary']) }}">{{ $p['year'] }}</span>
                @empty
                  <span>{{ date('Y') }}</span>
                @endforelse
              </div>
              <div class="grid grid-cols-2 gap-2">
                <div style="display: flex; align-items: center; flex-direction: row-reverse; justify-content: space-between; height: 30px;"
                  class="p-1.5 bg-transparent rounded-lg border border-border1">
                  <p class="text-[7px] text-text3 uppercase font-black tracking-widest mb-0.5">Base Start</p>
                  <p class="text-xs font-black text-text1">{{ $firstSal > 0 ? number_format($firstSal) : '—' }}</p>
                </div>
                <div style="display: flex; align-items: center; flex-direction: row-reverse; justify-content: space-between; height: 30px;"
                  class="p-1.5 bg-primary/10 rounded-lg border border-primary/20">
                  <p class="text-[7px] text-primary uppercase font-black tracking-widest mb-0.5">Current</p>
                  <p class="text-xs font-black text-text1">{{ $lastSal > 0 ? number_format($lastSal) : '—' }}</p>
                </div>
              </div>
            </div>
            <div
              class="bg-surface border border-border1 rounded-xl p-3 flex-1 min-h-0">
              <div class="flex items-center gap-2 mb-2">
                <h3 class="font-bold text-sm text-text1">Previous Projects</h3>
              </div>
              <div id="projectsWrapper" class="border border-border1 rounded-xl relative contracts-scroll" style="height: 210px; max-height: 250px; overflow-y: auto;">
                <table class="w-full text-left text-[11px]">
                  <thead class="sticky top-0 z-10 bg-surface2 border-b border-border1">
                    <tr>
                      <th class="px-3 py-1.5 font-bold text-[9px] text-text3 uppercase">Project</th>
                      <th class="px-3 py-1.5 font-bold text-[9px] text-text3 uppercase">Start</th>
                      <th class="px-3 py-1.5 font-bold text-[9px] text-text3 uppercase relative">End
                        <span class="absolute right-2 top-1 flex gap-0.5">
                          <button id="projectScrollUp" class="w-4 h-4 rounded-full bg-surface3 border border-border2 text-text2 flex items-center justify-center shadow-sm hover:bg-surface4 hover:text-text1">
                            <i class="fas fa-arrow-up text-xs"></i>
                          </button>
                          <button id="projectScrollDown" class="w-4 h-4 rounded-full bg-surface3 border border-border2 text-text2 flex items-center justify-center shadow-sm hover:bg-surface4 hover:text-text1">
                            <i class="fas fa-arrow-down text-xs"></i>
                          </button>
                        </span>
                      </th>
                    </tr>
                  </thead>
                  <tbody class="divide-y divide-border1">
                    @forelse($previousProjects ?? [] as $pp)
                      <tr class="hover:bg-surface2/50 transition-colors">
                        <td class="px-3 py-1.5 font-medium text-text1" style="max-width: 160px;">{{ $pp->prj_title ?? '—' }}</td>
                        <td class="px-3 py-1.5 text-text2">{{ !empty($pp->ctr_startdt) ? \Carbon\Carbon::parse($pp->ctr_startdt)->format('M Y') : '—' }}</td>
                        <td class="px-3 py-1.5 text-text2">{{ !empty($pp->ctr_enddt) ? \Carbon\Carbon::parse($pp->ctr_enddt)->format('M Y') : '—' }}</td>
                      </tr>
                    @empty
                      <tr><td colspan="3" class="px-3 py-2 text-center text-text3">No projects</td></tr>
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
            <div class="bg-surface border border-border1 rounded-xl p-4 sm:p-6">
              <div class="flex flex-col md:flex-row justify-center items-start gap-8 relative w-full">
                <div class="w-full md:w-1/2">
                  <!-- education card content -->
                  <div>
                    <div class="flex items-center gap-2 mb-4">
                      <i class="fas fa-graduation-cap text-primary text-lg mr-1.5"></i>
                      <h3 class="font-bold text-text1 text-sm">Education Details</h3>
                    </div>
                    <div class="space-y-4">
                      @php
                        $deg = ($degrees ?? collect())->first();
                      @endphp
                      <div class="grid grid-cols-2 lg:grid-cols-1 gap-2">
                        <div>
                          <p class="text-[9px] text-text3 font-bold uppercase tracking-widest mb-1">Degree</p>
                          <p class="text-xs font-bold text-text1">{{ $deg?->qlf_name ?? '—' }}</p>
                        </div>
                        <div>
                          <p class="text-[9px] text-text3 font-bold uppercase tracking-widest mb-1">Institution</p>
                          <p class="text-xs font-bold text-text1">{{ $deg?->qlf_inst ?? '—' }}</p>
                        </div>
                      </div>
                      <div class="flex justify-between items-center pt-2 border-t border-border1 lg:border-t-0 lg:pt-0">
                        <div>
                          <p class="text-[9px] text-text3 font-bold uppercase tracking-widest mb-1">Duration</p>
                          <p class="text-xs font-bold text-text1">
                            {{ !empty($deg?->qlf_duration) ? ($deg?->qlf_duration.' '.($deg?->qlf_unit ?? '')) : (!empty($deg?->qlf_enddt) ? \Carbon\Carbon::parse($deg?->qlf_enddt)->format('Y') : '—') }}
                          </p>
                        </div>
                        <i class="fas fa-check-circle text-emerald-400 text-xl mr-1.5"></i>
                      </div>
                    </div>
                  </div>
                </div>
                <div class="hidden md:block absolute inset-y-0 left-1/2 w-px bg-border1"></div>
                <div class="w-full md:w-1/2 pt-6 md:pt-0 border-t md:border-t-0 border-border1">
                  <!-- certifications content -->
                  <div>
                    <div class="flex items-center gap-2 mb-4">
                      <i class="fas fa-award text-primary text-lg mr-1.5"></i>
                      <h3 class="font-bold text-text1 text-sm">Certifications</h3>
                    </div>
                    <ul class="space-y-4">
                      @forelse(($certs ?? collect())->take(5) as $ct)
                        <li class="flex justify-between items-center">
                          <span class="text-xs font-bold text-text1 truncate pr-2">{{ $ct->qlf_name ?? '—' }}</span>
                          <span class="px-2 py-0.5 bg-primary/10 text-primary rounded-md text-[8px] font-black uppercase shrink-0 border border-primary/20">
                            {{ !empty($ct->qlf_enddt) ? \Carbon\Carbon::parse($ct->qlf_enddt)->format('Y') : ($ct->qlf_level ?? '—') }}
                          </span>
                        </li>
                      @empty
                        <li class="text-xs text-text3">No certifications</li>
                      @endforelse
                    </ul>
                  </div>
                </div>
              </div>
            </div>
            <!-- specialized skills card full width -->
            <div class="bg-surface border border-border1 rounded-xl p-6">
              <div class="flex items-center gap-1.5 mb-2">
                <i class="fas fa-brain text-primary text-lg mr-1.5"></i>
                <h3 class="font-bold text-text1">Specialized Skills</h3>
              </div>
              <div class="flex flex-wrap gap-1">
                <span class="px-2.5 py-1 bg-primary/10 text-primary text-[10px] font-semibold rounded-md border border-primary/20">Wireframing</span>
                <span class="px-2.5 py-1 bg-primary/10 text-primary text-[10px] font-semibold rounded-md border border-primary/20">User Research</span>
                <span class="px-2.5 py-1 bg-primary/10 text-primary text-[10px] font-semibold rounded-md border border-primary/20">Prototyping</span>
                <span class="px-2.5 py-1 bg-primary/10 text-primary text-[10px] font-semibold rounded-md border border-primary/20">Design Systems</span>
                <span class="px-2.5 py-1 bg-primary/10 text-primary text-[10px] font-semibold rounded-md border border-primary/20">Accessibility</span>
              </div>
            </div>
          </div>
          <!-- career details card remains -->
          <!-- Container holding both sections -->
          <div class="flex flex-col gap-6">
            <!-- Career Details -->
            <div class="bg-surface border border-border1 rounded-xl p-6">
              <div class="flex items-center gap-2 mb-6">
                <i class="fas fa-briefcase text-primary text-lg mr-1.5"></i>
                <h3 class="font-bold text-text1">Career Details</h3>
              </div>
              <div class="space-y-4">
                <div class="flex justify-between items-center">
                  <span class="text-[10px] text-text3 font-bold uppercase tracking-tight">Years in Service</span>
                  <span class="text-xs font-bold text-text1">{{ $yearsInService !== null ? $yearsInService.' years' : '—' }}</span>
                </div>
                <div class="flex justify-between items-center">
                  <span class="text-[10px] text-text3 font-bold uppercase tracking-tight">Last Promotion</span>
                  @php
                    $lastProm = $lastContract?->ctr_date ?? ($lastContract?->ctr_startdt ?? ($lastContract?->ctr_enddt ?? null));
                  @endphp
                  <span class="text-xs font-bold text-text1">{{ $lastProm ? \Carbon\Carbon::parse($lastProm)->format('M Y') : '—' }}</span>
                </div>
                
                <div class="pt-4 mt-2 border-t border-border1">
                  <div class="text-[10px] text-text3 font-black uppercase tracking-widest mb-2">Previous Jobs</div>
                  <ul class="space-y-1.5">
                    @forelse(($jobs ?? collect())->take(5) as $jb)
                      <li class="flex items-center justify-between text-xs">
                        <span class="font-semibold text-text1" style="max-width: 55%; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;">
                          {{ $jb->job_jobtitle ?? '—' }} @ {{ $jb->job_company ?? '—' }}
                        </span>
                        <span class="text-text2">
                          {{ !empty($jb->job_from) ? \Carbon\Carbon::parse($jb->job_from)->format('M Y') : '—' }}
                          —
                          {{ !empty($jb->job_to) ? \Carbon\Carbon::parse($jb->job_to)->format('M Y') : 'Present' }}
                        </span>
                      </li>
                    @empty
                      <li class="text-xs text-text3">No previous jobs</li>
                    @endforelse
                  </ul>
                </div>
              </div>
            </div>

            <!-- Kin Details -->
            <div class="bg-surface border border-border1 rounded-xl p-6">
              <div class="flex items-center gap-2 mb-4">
                <i class="fas fa-user-friends text-primary text-lg mr-1.5"></i>
                <h3 class="font-bold text-text1">Kin Details</h3>
              </div>
              <div class="flex items-center gap-2 mb-3">
                <button id="kinTabNk" type="button" class="px-3 py-1.5 text-xs font-bold rounded-md border border-border2 bg-surface2 text-text2 hover:bg-surface3 hover:text-text1 transition-colors">Next of Kin</button>
      <button id="kinTabEc" type="button" class="px-3 py-1.5 text-xs font-bold rounded-md border border-border2 bg-surface2 text-text2 hover:bg-surface3 hover:text-text1 transition-colors">Emergency Contact</button>
    </div>
    <div id="kinPanelNk" class="space-y-1.5 hidden">
      <div class="text-[10px] text-text3 font-bold uppercase tracking-widest">Next of Kin</div>
      <div class="flex justify-between text-xs"><span class="text-text2">Name</span><span class="font-bold text-text1">{{ $kin['name'] ?? '—' }}</span></div>
      <div class="flex justify-between text-xs"><span class="text-text2">Relation</span><span class="font-bold text-text1">{{ $kin['relation'] ?? '—' }}</span></div>
      <div class="flex justify-between text-xs"><span class="text-text2">CNIC</span><span class="font-bold text-text1">{{ $kin['cnic'] ?? '—' }}</span></div>
    </div>
    <div id="kinPanelEc" class="space-y-1.5 hidden">
      <div class="text-[10px] text-text3 font-bold uppercase tracking-widest">Emergency Contact</div>
      <div class="flex justify-between text-xs"><span class="text-text2">Name</span><span class="font-bold text-text1">{{ $emer['name'] ?? '—' }}</span></div>
      <div class="flex justify-between text-xs"><span class="text-text2">Relation</span><span class="font-bold text-text1">{{ $emer['relation'] ?? '—' }}</span></div>
      <div class="flex justify-between text-xs"><span class="text-text2">Phone</span><span class="font-bold text-text1">{{ $emer['mobile'] ?? '—' }}</span></div>
    </div>
    </div>
  </div>
        </div>
      </div>
    </div>

  <!-- Document Management Modal -->
  <div id="docsUploadModal" class="fixed inset-0 z-50 overflow-y-auto bg-black bg-opacity-60 flex items-center justify-center p-4 hidden">
    <div class="bg-surface border border-border1 rounded-2xl max-w-2xl w-full p-6 shadow-2xl relative">
      <div class="flex justify-between items-center pb-3 border-b border-border1 mb-4">
        <h3 class="text-base font-bold text-text1 flex items-center gap-2">
          <i class="fas fa-folder-open text-primary"></i> Employee Documents Management
        </h3>
        <button type="button" onclick="document.getElementById('docsUploadModal').classList.add('hidden')" class="text-text2 hover:text-text1 cursor-pointer">
          <i class="fas fa-times text-lg"></i>
        </button>
      </div>
      @include('partials.attachments_widget', [
          'module' => 'emp',
          'objectId' => $emp->emp_id ?? $id,
          'title' => 'Employee Documents',
          'defaultSlots' => ['Appointment Letter', 'Form', 'CV', 'Minute'],
          'attachments' => $attachments ?? [],
          'canEdit' => Auth::check() && (Auth::user()->isApprover() || Auth::user()->acc_level >= 2),
      ])
    </div>
  </div>

  <!-- Official Security & Demographics Modal -->
  <div id="officialDemographicsModal" class="fixed inset-0 z-50 overflow-y-auto bg-black bg-opacity-60 flex items-center justify-center p-4 hidden">
    <div class="bg-surface border border-border1 rounded-2xl max-w-3xl w-full p-6 shadow-2xl relative">
      <div class="flex justify-between items-center pb-3 border-b border-border1 mb-4">
        <h3 class="text-base font-bold text-text1 flex items-center gap-2">
          <i class="fas fa-shield-alt text-primary"></i> Official Security & Demographics
        </h3>
        <button type="button" onclick="document.getElementById('officialDemographicsModal').classList.add('hidden')" class="text-text2 hover:text-text1 cursor-pointer">
          <i class="fas fa-times text-lg"></i>
        </button>
      </div>
      <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        <!-- Official & Security Clearance -->
        <div class="bg-surface2/50 border border-border1 rounded-xl p-4">
          <div class="flex items-center gap-2 mb-3">
            <i class="fas fa-shield-alt text-primary text-base"></i>
            <h4 class="font-bold text-text1 text-sm">Security Clearance</h4>
          </div>
          <div class="space-y-2.5 text-xs">
            <div class="flex justify-between py-1 border-b border-border1">
              <span class="text-text2">Clearance Status</span>
              <span class="font-bold text-text1">
                <span class="px-2 py-0.5 rounded-full text-[9px] font-black uppercase tracking-wider {{ ($empC->emp_secclear ?? '') === 'Cleared' ? 'bg-emerald-500/10 text-emerald-400 border border-emerald-500/20' : 'bg-surface3 text-text2' }}">
                  {{ $empC->emp_secclear ?? 'Not Cleared' }}
                </span>
              </span>
            </div>
            <div class="flex justify-between py-1 border-b border-border1">
              <span class="text-text2">Clearance Number</span>
              <span class="font-bold text-text1">{{ $empC->emp_cnum ?? '—' }}</span>
            </div>
            <div class="flex justify-between py-1 border-b border-border1">
              <span class="text-text2">Issue Date</span>
              <span class="font-bold text-text1">{{ !empty($empC->emp_cissuedt) ? \Carbon\Carbon::parse($empC->emp_cissuedt)->format('d-M-Y') : '—' }}</span>
            </div>
            <div class="flex justify-between py-1">
              <span class="text-text2">Expiry Date</span>
              <span class="font-bold text-text1">{{ !empty($empC->emp_cexpdt) ? \Carbon\Carbon::parse($empC->emp_cexpdt)->format('d-M-Y') : '—' }}</span>
            </div>
          </div>
        </div>

        <!-- Personal & Demographics -->
        <div class="bg-surface2/50 border border-border1 rounded-xl p-4">
          <div class="flex items-center gap-2 mb-3">
            <i class="fas fa-id-card text-primary text-base"></i>
            <h4 class="font-bold text-text1 text-sm">Demographics & Address</h4>
          </div>
          <div class="space-y-2.5 text-xs">
            <div class="flex justify-between py-1 border-b border-border1">
              <span class="text-text2">DOB / Gender</span>
              <span class="font-bold text-text1">
                {{ !empty($empA?->emp_dob) ? \Carbon\Carbon::parse($empA->emp_dob)->format('d-M-Y') : '—' }}
                ({{ $empA?->emp_gender ?? '—' }})
              </span>
            </div>
            <div class="flex justify-between py-1 border-b border-border1">
              <span class="text-text2">Marital / Nationality</span>
              <span class="font-bold text-text1">{{ $empA?->emp_marital ?? '—' }} / {{ $empA?->emp_ntnlty ?? 'Pakistani' }}</span>
            </div>
            <div class="flex justify-between py-1 border-b border-border1">
              <span class="text-text2">Religion / Caste</span>
              <span class="font-bold text-text1">{{ $empB?->emp_religion ?? '—' }} ({{ $empB?->emp_caste ?? '—' }})</span>
            </div>
            <div class="flex justify-between py-1">
              <span class="text-text2">Permanent Address</span>
              <span class="font-bold text-text1 text-right truncate max-w-[60%]" title="{{ $empA?->emp_paddress ?? '' }}">{{ $empA?->emp_paddress ?? '—' }}</span>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>

  <!-- Employment Record Print Modal -->
  <div id="employmentRecordModal" class="fixed inset-0 z-50 overflow-y-auto bg-slate-900/60 backdrop-blur-xs flex items-start sm:items-center justify-center p-2 sm:p-4 hidden">
    <div class="bg-white rounded-xl shadow-2xl max-w-4xl w-full my-auto sm:my-3 overflow-hidden flex flex-col border border-slate-300" style="max-height: 94vh;">
      <!-- Modal Toolbar (Not printed) -->
      <div class="no-print flex-shrink-0 bg-slate-100 border-b border-slate-200 px-5 py-2.5 flex justify-between items-center">
        <div class="flex items-center gap-2">
          <div class="w-7 h-7 rounded-lg bg-emerald-100 border border-emerald-300 flex items-center justify-center text-emerald-700">
            <i class="fas fa-file-invoice text-xs"></i>
          </div>
          <div>
            <span class="font-bold text-xs sm:text-sm text-slate-800 tracking-wide block">Employment Record Preview</span>
          </div>
        </div>
        <div class="flex items-center gap-2">
          <button type="button" onclick="printEmploymentRecord()" class="px-3.5 py-1.5 bg-emerald-600 hover:bg-emerald-700 text-white rounded-lg text-xs font-bold shadow-sm transition flex items-center gap-1.5 cursor-pointer">
            <i class="fas fa-print"></i> Print Document (A4)
          </button>
          <button type="button" onclick="document.getElementById('employmentRecordModal').classList.add('hidden')" class="px-3 py-1.5 bg-white hover:bg-slate-100 text-slate-700 border border-slate-300 rounded-lg text-xs font-bold transition cursor-pointer">
            <i class="fas fa-times mr-1"></i> Close
          </button>
        </div>
      </div>

      <!-- Printable Paper Area Container -->
      <div class="flex-1 overflow-y-auto p-2 sm:p-5 bg-slate-100 flex justify-center" style="font-family: Arial, Helvetica, sans-serif;">
        <div id="employmentRecordPrintArea" style="background: #ffffff; color: #000000; width: 100%; max-width: 820px; margin: 0 auto; padding: 24px 32px; box-shadow: 0 4px 15px rgba(0,0,0,0.06); border: 1px solid #e2e8f0; border-radius: 4px;">
          <!-- Document Title -->
          <h1 style="font-size: 20px; font-weight: 600; margin: 0 0 12px 0; color: #000000; letter-spacing: -0.5px;">Employment Record</h1>

          <!-- Top Details Grid -->
          <div style="display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 6px 30px; font-size: 11.5px; line-height: 1.4; margin-bottom: 10px;">
            <!-- Left Col -->
            <div>
              <table style="width: 100%; border-collapse: collapse;">
                <tr>
                  <td style="width: 38%; font-weight: bold; color: #000; padding: 1.5px 0;">Employee ID</td>
                  <td style="color: #000; padding: 1.5px 0;">{{ $emp->emp_num ?: ($emp->emp_id ?: $id) }}</td>
                </tr>
                <tr>
                  <td style="font-weight: bold; color: #000; padding: 1.5px 0;">Joining Date</td>
                  <td style="color: #000; padding: 1.5px 0;">{{ !empty($emp->emp_joindt) ? \Carbon\Carbon::parse($emp->emp_joindt)->format('d M y') : (!empty($firstContract?->ctr_startdt) ? \Carbon\Carbon::parse($firstContract->ctr_startdt)->format('d M y') : '—') }}</td>
                </tr>
                <tr>
                  <td style="font-weight: bold; color: #000; padding: 1.5px 0;">Division</td>
                  <td style="color: #000; padding: 1.5px 0; font-weight: 500;">{{ $emp->unt_name ?: ($authUnit?->unt_name ?: ($base?->eff_unit_name ?: 'Communication Division')) }}</td>
                </tr>
              </table>
            </div>

            <!-- Right Col -->
            <div>
              <table style="width: 100%; border-collapse: collapse;">
                <tr>
                  <td style="width: 32%; font-weight: bold; color: #000; padding: 1.5px 0;">Name</td>
                  <td style="color: #000; padding: 1.5px 0;">{{ $emp->emp_name ?: '—' }}</td>
                </tr>
                <tr>
                  <td style="font-weight: bold; color: #000; padding: 1.5px 0;">Last Date</td>
                  <td style="color: #000; padding: 1.5px 0;">{{ !empty($emp->emp_enddt) ? \Carbon\Carbon::parse($emp->emp_enddt)->format('d M y') : '' }}</td>
                </tr>
                <tr>
                  <td style="font-weight: bold; color: #000; padding: 1.5px 0;">Project</td>
                  <td style="color: #000; padding: 1.5px 0;">{{ $currentContract?->ctr_hed_code ?: ($currentContract?->ctr_prj_code ?: ($emp->hed_code ?: ($emp->prj_code ?? '—'))) }}</td>
                </tr>
              </table>
            </div>
          </div>

          <!-- Divider line -->
          <hr style="border: none; border-top: 1px solid #000; margin: 8px 0 6px 0;">

          <!-- Section Heading: Contracts -->
          <div style="font-size: 12.5px; font-weight: bold; color: #000; margin-bottom: 6px;">Contracts</div>

          <!-- Contracts List (Reverse Chronological) -->
          @forelse(($contractsHistory ?? collect()) as $c)
            <div class="contract-item-row" style="margin-bottom: 8px; font-size: 11px; line-height: 1.35; page-break-inside: avoid; break-inside: avoid;">
              <!-- Dates Header -->
              <div style="margin-bottom: 3px; font-size: 11.5px;">
                <span style="font-weight: bold; color: #000;">Contract dates</span>
                <span style="margin-left: 18px; color: #000;">
                  {{ !empty($c->ctr_startdt) ? \Carbon\Carbon::parse($c->ctr_startdt)->format('d M y') : '—' }} to {{ !empty($c->ctr_enddt) ? \Carbon\Carbon::parse($c->ctr_enddt)->format('d M y') : '—' }}
                  @if(!empty($c->ctr_termindt) && $c->ctr_termindt > $c->ctr_enddt)
                    <span style="color: #222;">(extended to {{ \Carbon\Carbon::parse($c->ctr_termindt)->format('d M y') }})</span>
                  @endif
                </span>
              </div>

              <!-- Details Table with fixed layout and proper column widths -->
              <table style="width: 100%; border-collapse: collapse; font-size: 11px; table-layout: fixed;">
                <colgroup>
                  <col style="width: 10%;">
                  <col style="width: 25%;">
                  <col style="width: 10%;">
                  <col style="width: 24%;">
                  <col style="width: 11%;">
                  <col style="width: 20%;">
                </colgroup>
                <tr>
                  <td style="font-weight: bold; color: #000; padding: 1.5px 0; vertical-align: top;">Division</td>
                  <td style="color: #000; padding: 1.5px 0; vertical-align: top; font-weight: 500;">{{ $c->ctr_unt_name ?: ($emp->unt_name ?: ($authUnit?->unt_name ?: 'Communication Division')) }}</td>
                  <td style="font-weight: bold; color: #000; padding: 1.5px 0; vertical-align: top;">Grade</td>
                  <td style="color: #000; padding: 1.5px 0; vertical-align: top;">{{ $c->ctr_grade ?: '—' }}</td>
                  <td style="font-weight: bold; color: #000; padding: 1.5px 0; vertical-align: top;">Type</td>
                  <td style="color: #000; padding: 1.5px 0 1.5px 6px; vertical-align: top;">{{ ($c->ctr_type == 1 || empty($c->ctr_type)) ? 'Full Time' : ($c->ctr_type == 2 ? 'Part Time' : 'Full Time') }}</td>
                </tr>
                <tr>
                  <td style="font-weight: bold; color: #000; padding: 1.5px 0; vertical-align: top;">Project</td>
                  <td style="color: #000; padding: 1.5px 0; vertical-align: top;">{{ $c->ctr_hed_code ?: ($c->ctr_prj_code ?: '') }}</td>
                  <td style="font-weight: bold; color: #000; padding: 1.5px 0; vertical-align: top;">Job Title</td>
                  <td style="color: #000; padding: 1.5px 0; vertical-align: top;">{{ $c->ctr_jobtitle ?: '—' }}</td>
                  <td style="font-weight: bold; color: #000; padding: 1.5px 0; vertical-align: top;">Salary</td>
                  <td style="color: #000; padding: 1.5px 0 1.5px 6px; vertical-align: top;">{{ $c->ctr_salary ? number_format($c->ctr_salary) : '—' }}</td>
                </tr>
                <tr>
                  <td style="font-weight: bold; color: #000; padding: 1.5px 0; vertical-align: top;">Remarks</td>
                  <td colspan="3" style="color: #000; padding: 1.5px 12px 1.5px 0; vertical-align: top; line-height: 1.35;">{{ $c->ctr_remarks ?: '' }}</td>
                  <td style="font-weight: bold; color: #000; padding: 1.5px 0; vertical-align: top;">Probation</td>
                  <td style="color: #000; padding: 1.5px 0 1.5px 6px; vertical-align: top;">{{ !empty($c->ctr_prob) ? ($c->ctr_prob . (is_numeric($c->ctr_prob) ? ' Months' : '')) : 'Nil' }}</td>
                </tr>
              </table>

              <!-- Divider Line between contracts -->
              <hr style="border: none; border-top: 1px solid #777; margin: 6px 0 6px 0;">
            </div>
          @empty
            <div style="font-size: 11px; color: #666; padding: 8px 0;">No contract history found.</div>
          @endforelse

          <!-- Footer -->
          <div style="display: flex; justify-content: space-between; align-items: center; margin-top: 14px; font-size: 10px; color: #333;">
            <div style="flex: 1;"></div>
            <div style="flex: 1; text-align: center; color: #444;">1 of 1</div>
            <div style="flex: 1; text-align: right; color: #444;">Printed on {{ now()->format('d M y  H:i') }}</div>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>

  <script>
    document.addEventListener('DOMContentLoaded', function () {
      var wrapper = document.getElementById('contractsWrapper');
      if (!wrapper) return;
      var tbody = wrapper.querySelector('tbody');
      var firstRow = tbody ? tbody.querySelector('tr') : null;
      var rowHeight = firstRow ? firstRow.offsetHeight : 36;
      var up = document.getElementById('contractScrollUp');
      var down = document.getElementById('contractScrollDown');
      var rowsCount = tbody ? tbody.querySelectorAll('tr').length : 0;
      
      wrapper.style.height = '155px';
      wrapper.style.maxHeight = '155px';
      wrapper.style.overflowY = 'auto';

      if (rowsCount <= 3) {
        if (up) up.style.display = 'none';
        if (down) down.style.display = 'none';
      } else {
        if (up) {
          up.style.display = '';
          up.onclick = function (e) {
            e.preventDefault();
            wrapper.scrollBy({ top: -rowHeight * 2, behavior: 'smooth' });
          };
        }
        if (down) {
          down.style.display = '';
          down.onclick = function (e) {
            e.preventDefault();
            wrapper.scrollBy({ top: rowHeight * 2, behavior: 'smooth' });
          };
        }
      }
    });
    document.addEventListener('DOMContentLoaded', function () {
      var pWrapper = document.getElementById('projectsWrapper');
      if (!pWrapper) return;
      var pTbody = pWrapper.querySelector('tbody');
      var pThead = pWrapper.querySelector('thead');
      var pFirstRow = pTbody ? pTbody.querySelector('tr') : null;
      var pRowHeight = pFirstRow ? pFirstRow.offsetHeight : 40;
      var pHeaderHeight = pThead ? pThead.offsetHeight : 24;
      pWrapper.style.height = '210px';
      var pUp = document.getElementById('projectScrollUp');
      var pDown = document.getElementById('projectScrollDown');
      if (pUp) pUp.addEventListener('click', function () { pWrapper.scrollBy({ top: -pRowHeight, behavior: 'smooth' }); });
      if (pDown) pDown.addEventListener('click', function () { pWrapper.scrollBy({ top: pRowHeight, behavior: 'smooth' }); });
      window.addEventListener('resize', function () {
        var pFr = pTbody ? pTbody.querySelector('tr') : null;
        var pRh = pFr ? pFr.offsetHeight : pRowHeight;
        var pHh = pThead ? pThead.offsetHeight : pHeaderHeight;
        pWrapper.style.height = '210px';
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
        
        // Reset classes
        nkBtn.className = "px-3 py-1.5 text-xs font-bold rounded-md border border-border2 bg-surface2 text-text2 hover:bg-surface3 hover:text-text1 transition-colors";
        ecBtn.className = "px-3 py-1.5 text-xs font-bold rounded-md border border-border2 bg-surface2 text-text2 hover:bg-surface3 hover:text-text1 transition-colors";
        
        if (target === 'nk') {
          nkPanel.classList.remove('hidden');
          nkBtn.className = "px-3 py-1.5 text-xs font-bold rounded-md border border-primary bg-primary/10 text-primary transition-colors";
        }
        if (target === 'ec') {
          ecPanel.classList.remove('hidden');
          ecBtn.className = "px-3 py-1.5 text-xs font-bold rounded-md border border-primary bg-primary/10 text-primary transition-colors";
        }
      }
      if (nkBtn) nkBtn.addEventListener('click', function(){ activate('nk'); });
      if (ecBtn) ecBtn.addEventListener('click', function(){ activate('ec'); });
      
      // Default to Next of Kin on load
      activate('nk');
    });

    // Project Allocations Dropdown Toggle (Closed by default, toggles on click, closes on click away)
    document.addEventListener('DOMContentLoaded', function () {
      var allocBtn = document.getElementById('projectAllocationsToggleBtn');
      var allocMenu = document.getElementById('projectAllocationsDropdownMenu');
      var allocChevron = document.getElementById('projectAllocationsChevron');
      var allocWrap = document.getElementById('projectAllocationsDropdownWrap');

      if (allocBtn && allocMenu) {
        allocBtn.addEventListener('click', function (e) {
          e.stopPropagation();
          var isHidden = allocMenu.style.display === 'none' || allocMenu.style.display === '';
          if (isHidden) {
            allocMenu.style.display = 'block';
            if (allocChevron) allocChevron.style.transform = 'rotate(180deg)';
          } else {
            allocMenu.style.display = 'none';
            if (allocChevron) allocChevron.style.transform = 'rotate(0deg)';
          }
        });

        document.addEventListener('click', function (e) {
          if (allocWrap && !allocWrap.contains(e.target)) {
            allocMenu.style.display = 'none';
            if (allocChevron) allocChevron.style.transform = 'rotate(0deg)';
          }
        });
      }
    });

    // Employee Documents Dropdown Toggle
    document.addEventListener('DOMContentLoaded', function () {
      var docBtn = document.getElementById('employeeDocsToggleBtn');
      var docMenu = document.getElementById('employeeDocsDropdownMenu');
      var docChevron = document.getElementById('employeeDocsChevron');
      var docWrap = document.getElementById('employeeDocsDropdownWrap');

      if (docBtn && docMenu) {
        docBtn.addEventListener('click', function (e) {
          e.stopPropagation();
          var isHidden = docMenu.style.display === 'none' || docMenu.style.display === '';
          if (isHidden) {
            docMenu.style.display = 'block';
            if (docChevron) docChevron.style.transform = 'rotate(180deg)';
          } else {
            docMenu.style.display = 'none';
            if (docChevron) docChevron.style.transform = 'rotate(0deg)';
          }
        });

        document.addEventListener('click', function (e) {
          if (docWrap && !docWrap.contains(e.target)) {
            docMenu.style.display = 'none';
            if (docChevron) docChevron.style.transform = 'rotate(0deg)';
          }
        });
      }
    });

    function openEmploymentRecordModal() {
      var modal = document.getElementById('employmentRecordModal');
      if (modal) modal.classList.remove('hidden');
    }

    function printEmploymentRecord() {
      var printArea = document.getElementById('employmentRecordPrintArea');
      if (!printArea) {
        window.print();
        return;
      }
      var content = printArea.innerHTML;
      var empName = @json($emp->emp_name ?? 'Employee');
      var printWin = window.open('', '_blank', 'width=980,height=900,menubar=no,toolbar=no,location=no,status=no');
      if (!printWin) {
        window.print();
        return;
      }
      printWin.document.write('<!DOCTYPE html><html><head><title>Employment Record - ' + empName + '</title>');
      printWin.document.write('<style>');
      printWin.document.write('@page { size: A4 portrait; margin: 8mm 12mm; }');
      printWin.document.write('* { box-sizing: border-box; }');
      printWin.document.write('body { font-family: Arial, Helvetica, sans-serif; background: #ffffff !important; color: #000000 !important; margin: 0; padding: 0; font-size: 11px; line-height: 1.35; -webkit-print-color-adjust: exact; print-color-adjust: exact; }');
      printWin.document.write('table { width: 100%; border-collapse: collapse; table-layout: fixed; }');
      printWin.document.write('hr { border: none; border-top: 1px solid #000; margin: 8px 0 6px 0; }');
      printWin.document.write('.contract-item-row { page-break-inside: avoid; break-inside: avoid; }');
      printWin.document.write('@media print { body { padding: 0 !important; } }');
      printWin.document.write('</style></head><body>');
      printWin.document.write(content);
      printWin.document.write('</body></html>');
      printWin.document.close();
      printWin.focus();
      setTimeout(function() {
        printWin.print();
      }, 400);
    }
  </script>

  @can('initiate', \App\Models\AudRev::class)
  {{-- Employee Reversal Modal --}}
  <div id="reverseEmployeeModal" class="hidden fixed inset-0 z-50 flex items-center justify-center bg-black/60 backdrop-blur-sm p-4 overflow-y-auto">
    <div class="bg-surface border border-border1 rounded-2xl shadow-2xl w-full max-w-lg overflow-hidden my-8">
      {{-- Modal Header --}}
      <div class="px-6 py-4 border-b border-border1 flex items-center justify-between bg-surface2">
        <div class="flex items-center gap-2">
          <div class="w-8 h-8 rounded-lg bg-rose-500/10 border border-rose-500/20 flex items-center justify-center text-rose-400 font-bold">
            <i class="fas fa-sync-alt text-sm"></i>
          </div>
          <div>
            <h3 class="text-base font-bold text-text1">Initiate Reversal for Employee</h3>
            <p class="text-xs text-text3">{{ $emp->emp_name ?? 'Employee' }} (ID: {{ $emp->emp_id ?? $id }})</p>
          </div>
        </div>
        <button type="button" onclick="document.getElementById('reverseEmployeeModal').classList.add('hidden')"
          class="text-text3 hover:text-text1 p-1 rounded-lg hover:bg-surface3 transition-colors">
          <i class="fas fa-times text-base"></i>
        </button>
      </div>

      {{-- Tab Buttons --}}
      <div class="px-6 pt-4 pb-2 border-b border-border1 flex gap-2">
        <button type="button" id="empRevTabFieldBtn" onclick="switchEmpRevTab('field')"
          class="flex-1 py-2 px-3 text-xs font-bold rounded-lg border border-primary bg-primary/10 text-primary transition-all flex items-center justify-center gap-1.5">
          <i class="fas fa-edit"></i> Field-Level (Recommended)
        </button>
        <button type="button" id="empRevTabFullBtn" onclick="switchEmpRevTab('full')"
          class="flex-1 py-2 px-3 text-xs font-bold rounded-lg border border-border2 bg-surface2 text-text2 hover:bg-surface3 hover:text-text1 transition-all flex items-center justify-center gap-1.5">
          <i class="fas fa-layer-group"></i> Full Record
        </button>
      </div>

      {{-- Tab 1: Field-Level Correction (RevType 2) --}}
      <div id="empRevTabField" class="p-6">
        <form action="{{ route('divhr.employees.reverse-field', $emp->emp_id ?? $id) }}" method="POST" class="space-y-4">
          @csrf
          <div>
            <label class="block text-xs font-bold uppercase tracking-wider text-text2 mb-1.5">Field to Revise</label>
            <select name="field_name" class="w-full text-xs bg-surface2 border border-border2 rounded-lg px-3 py-2 text-text1 focus:ring-1 focus:ring-primary focus:outline-none">
              <option value="emp_title">Designation / Title (Current: {{ $emp->emp_title ?? '—' }})</option>
              <option value="emp_rank">Rank (Current: {{ $emp->emp_rank ?? '—' }})</option>
              <option value="emp_status">Status (Current: {{ $emp->emp_status ?? '—' }})</option>
              <option value="emp_cnic">CNIC (Current: {{ $emp->emp_cnic ?? '—' }})</option>
              <option value="emp_salary">Basic Pay / Salary</option>
              <option value="emp_joindt">Joining Date (Current: {{ $emp->emp_joindt ?? '—' }})</option>
            </select>
          </div>

          <div class="grid grid-cols-2 gap-3">
            <div>
              <label class="block text-xs font-bold uppercase tracking-wider text-text2 mb-1.5">Old Value</label>
              <input type="text" name="old_value" placeholder="Previous value"
                class="w-full text-xs bg-surface2 border border-border2 rounded-lg px-3 py-2 text-text1 focus:ring-1 focus:ring-primary focus:outline-none" />
            </div>
            <div>
              <label class="block text-xs font-bold uppercase tracking-wider text-text2 mb-1.5">New Value</label>
              <input type="text" name="new_value" placeholder="Corrected value" required
                class="w-full text-xs bg-surface2 border border-border2 rounded-lg px-3 py-2 text-text1 focus:ring-1 focus:ring-primary focus:outline-none" />
            </div>
          </div>

          <div>
            <label class="block text-xs font-bold uppercase tracking-wider text-text2 mb-1.5">Revision Reason <span class="text-rose-400">*</span></label>
            <textarea name="rev_reason" rows="3" required placeholder="Explain why this employee field correction is required..."
              class="w-full text-xs bg-surface2 border border-border2 rounded-lg p-3 text-text1 focus:ring-1 focus:ring-primary focus:outline-none resize-none"></textarea>
          </div>

          <div class="pt-2 flex justify-end gap-2">
            <button type="button" onclick="document.getElementById('reverseEmployeeModal').classList.add('hidden')"
              class="px-4 py-2 text-xs font-semibold text-text2 hover:text-text1 bg-surface2 hover:bg-surface3 border border-border2 rounded-lg transition-colors">
              Cancel
            </button>
            <button type="submit"
              class="px-4 py-2 text-xs font-bold text-white bg-primary hover:opacity-90 rounded-lg shadow transition-opacity flex items-center gap-1.5">
              <i class="fas fa-check"></i> Submit Field Correction Draft
            </button>
          </div>
        </form>
      </div>

      {{-- Tab 2: Full Record Reversal (RevType 1) --}}
      <div id="empRevTabFull" class="p-6 hidden">
        <form action="{{ route('divhr.employees.reverse-full', $emp->emp_id ?? $id) }}" method="POST" class="space-y-4">
          @csrf
          {{-- Required Amber Warning Box --}}
          <div class="p-3.5 bg-amber-500/15 border border-amber-500/30 rounded-xl text-amber-300 text-xs flex items-start gap-2.5">
            <i class="fas fa-exclamation-triangle mt-0.5 text-amber-400 flex-shrink-0"></i>
            <span class="leading-relaxed">Full employee reversal has no working execution path — historical Employee reversals used field-level correction instead</span>
          </div>

          <div>
            <label class="block text-xs font-bold uppercase tracking-wider text-text2 mb-1.5">Revision Reason <span class="text-rose-400">*</span></label>
            <textarea name="rev_reason" rows="3" required placeholder="Explain why full employee revision is being initiated..."
              class="w-full text-xs bg-surface2 border border-border2 rounded-lg p-3 text-text1 focus:ring-1 focus:ring-primary focus:outline-none resize-none"></textarea>
          </div>

          <div class="pt-2 flex justify-end gap-2">
            <button type="button" onclick="document.getElementById('reverseEmployeeModal').classList.add('hidden')"
              class="px-4 py-2 text-xs font-semibold text-text2 hover:text-text1 bg-surface2 hover:bg-surface3 border border-border2 rounded-lg transition-colors">
              Cancel
            </button>
            <button type="submit"
              class="px-4 py-2 text-xs font-bold text-amber-300 bg-amber-950/60 hover:bg-amber-900/80 border border-amber-700/50 rounded-lg shadow transition-colors flex items-center gap-1.5">
              <i class="fas fa-paper-plane"></i> Initiate Full Reversal Draft
            </button>
          </div>
        </form>
      </div>
    </div>
  </div>

  <script>
    function switchEmpRevTab(tab) {
      var fieldTab = document.getElementById('empRevTabField');
      var fullTab = document.getElementById('empRevTabFull');
      var fieldBtn = document.getElementById('empRevTabFieldBtn');
      var fullBtn = document.getElementById('empRevTabFullBtn');

      if (tab === 'field') {
        fieldTab.classList.remove('hidden');
        fullTab.classList.add('hidden');
        fieldBtn.className = "flex-1 py-2 px-3 text-xs font-bold rounded-lg border border-primary bg-primary/10 text-primary transition-all flex items-center justify-center gap-1.5";
        fullBtn.className = "flex-1 py-2 px-3 text-xs font-bold rounded-lg border border-border2 bg-surface2 text-text2 hover:bg-surface3 hover:text-text1 transition-all flex items-center justify-center gap-1.5";
      } else {
        fieldTab.classList.add('hidden');
        fullTab.classList.remove('hidden');
        fullBtn.className = "flex-1 py-2 px-3 text-xs font-bold rounded-lg border border-amber-500 bg-amber-500/10 text-amber-300 transition-all flex items-center justify-center gap-1.5";
        fieldBtn.className = "flex-1 py-2 px-3 text-xs font-bold rounded-lg border border-border2 bg-surface2 text-text2 hover:bg-surface3 hover:text-text1 transition-all flex items-center justify-center gap-1.5";
      }
    }
  </script>
  @endcan
  
  {{-- Service Contract Modal & Document View --}}
  @include('divhr.partials.service-contract-modal')

</div>
@endsection
