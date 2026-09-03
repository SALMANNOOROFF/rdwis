@extends('welcome')

@section('content')
<div class="content-wrapper pt-3 px-2 sm:px-4" style="background-color: var(--rd-bg); min-height: 100vh;">

  <title>Edit Employee Profile - {{ $emp->emp_name ?? 'Employee' }}</title>
  <link rel="stylesheet" href="{{ asset('css/fonts.css') }}">

  <style>
    :root {
      --rd-primary-50: #EEF4ED;
      --rd-primary-100: #DCE6DA;
      --rd-primary-500: #5F7858;
      --rd-primary-600: #4D6446;
      --rd-primary-700: #3C4E36;
      --rd-neutral-50: #F8FAFC;
      --rd-neutral-100: #F1F5F9;
      --rd-neutral-200: #E2E8F0;
      --rd-neutral-300: #CBD5E1;
      --rd-neutral-600: #475569;
      --rd-neutral-700: #334155;
      --rd-neutral-800: #1E293B;
      --rd-neutral-900: #0F172A;
    }

    .edit-page-container {
      max-width: 1440px;
      margin: 0 auto;
      padding-bottom: 4rem;
    }

    .clean-card {
      background: #FFFFFF;
      border: 1px solid var(--rd-neutral-200);
      border-radius: 12px;
      box-shadow: 0 2px 8px rgba(0, 0, 0, 0.04);
      margin-bottom: 1.5rem;
    }

    .rd-form-label {
      font-size: 0.8rem;
      font-weight: 700;
      color: var(--rd-neutral-700);
      text-transform: uppercase;
      letter-spacing: 0.5px;
      margin-bottom: 0.35rem;
      display: block;
    }

    .rd-input {
      width: 100%;
      background: #FFFFFF;
      border: 1.5px solid var(--rd-neutral-200);
      border-radius: 8px;
      padding: 0.55rem 0.85rem;
      font-size: 0.9rem;
      color: var(--rd-neutral-800);
      transition: all 0.2s ease;
    }
    .rd-input:focus {
      outline: none;
      border-color: var(--rd-primary-500);
      box-shadow: 0 0 0 3px rgba(95, 120, 88, 0.15);
    }
    .rd-input[readonly], .rd-input[disabled] {
      background-color: var(--rd-neutral-100);
      color: var(--rd-neutral-600);
      cursor: not-allowed;
    }

    /* Custom Modern Tab Pills */
    .tab-nav-container {
      display: flex;
      flex-wrap: wrap;
      gap: 0.5rem;
      background: #FFFFFF;
      border: 1px solid var(--rd-neutral-200);
      border-radius: 12px;
      padding: 0.6rem;
      margin-bottom: 1.5rem;
      box-shadow: 0 2px 8px rgba(0, 0, 0, 0.04);
    }

    .tab-pill-btn {
      display: flex;
      align-items: center;
      gap: 0.5rem;
      padding: 0.6rem 1.1rem;
      font-size: 0.85rem;
      font-weight: 700;
      color: var(--rd-neutral-600);
      background: transparent;
      border: none;
      border-radius: 8px;
      cursor: pointer;
      transition: all 0.2s ease;
      text-decoration: none;
    }
    .tab-pill-btn:hover {
      background: var(--rd-neutral-100);
      color: var(--rd-neutral-900);
    }
    .tab-pill-btn.active {
      background: var(--rd-primary-500);
      color: #FFFFFF;
      box-shadow: 0 2px 6px rgba(95, 120, 88, 0.25);
    }

    /* Dynamic Grid Tables */
    .grid-table {
      width: 100%;
      border-collapse: separate;
      border-spacing: 0;
    }
    .grid-table th {
      background: var(--rd-neutral-50);
      color: var(--rd-neutral-700);
      font-size: 0.75rem;
      font-weight: 700;
      text-transform: uppercase;
      letter-spacing: 0.5px;
      padding: 0.75rem 0.6rem;
      border-bottom: 1.5px solid var(--rd-neutral-200);
      border-top: 1px solid var(--rd-neutral-200);
    }
    .grid-table td {
      padding: 0.5rem 0.4rem;
      border-bottom: 1px solid var(--rd-neutral-200);
      vertical-align: middle;
    }
    .grid-table tr:hover td {
      background-color: var(--rd-neutral-50);
    }

    .btn-add-row {
      background: var(--rd-primary-50);
      color: var(--rd-primary-700);
      border: 1.5px solid var(--rd-primary-100);
      padding: 0.45rem 1rem;
      border-radius: 8px;
      font-size: 0.85rem;
      font-weight: 700;
      cursor: pointer;
      transition: all 0.2s ease;
    }
    .btn-add-row:hover {
      background: var(--rd-primary-100);
    }

    .btn-remove-row {
      background: #FEE2E2;
      color: #DC2626;
      border: 1px solid #FECACA;
      width: 32px;
      height: 32px;
      border-radius: 6px;
      display: flex;
      align-items: center;
      justify-content: center;
      cursor: pointer;
      transition: all 0.15s ease;
    }
    .btn-remove-row:hover {
      background: #FCA5A5;
      color: #991B1B;
    }
  </style>

  <div class="edit-page-container">
    
    <!-- Top Header Bar -->
    <div class="d-flex justify-content-between align-items-center flex-wrap gap-3 mb-3">
      <div class="d-flex align-items-center gap-3">
        <a href="{{ route('divhr.employeedetail', $emp->emp_id) }}" class="btn btn-light border px-3 py-2" style="border-radius: 8px;">
          <i class="fas fa-arrow-left mr-1"></i> Back to Profile
        </a>
        <div>
          <h2 class="font-weight-bold text-dark mb-0" style="font-size: 1.4rem;">
            Edit Employee Profile — <span class="text-primary">{{ $emp->emp_name }}</span>
          </h2>
          <div class="d-flex align-items-center gap-2 mt-1">
            <span class="badge badge-dark px-2.5 py-1" style="font-family: monospace; font-size: 0.85rem;">{{ $emp->emp_id }}</span>
            <span class="badge badge-success px-2.5 py-1">{{ $emp->emp_status }}</span>
            <span class="text-muted small ml-1">CNIC: <strong>{{ $emp->emp_cnic }}</strong></span>
          </div>
        </div>
      </div>
      
      <div class="d-flex gap-2">
        <a href="{{ route('divhr.employeedetail', $emp->emp_id) }}" class="btn btn-secondary px-4 py-2 font-weight-bold" style="border-radius: 8px;">
          Cancel
        </a>
        <button type="button" class="btn btn-primary px-4 py-2 font-weight-bold shadow-sm" id="btn-save-profile" style="background: var(--rd-primary-500); border-color: var(--rd-primary-500); border-radius: 8px; font-size: 0.95rem;">
          <i class="fas fa-save mr-1"></i> Save All Changes
        </button>
      </div>
    </div>

    <!-- Tab Navigation Pills -->
    <div class="tab-nav-container">
      <button type="button" class="tab-pill-btn active" data-tab="tab-core-personal1">
        <i class="fas fa-user"></i> Personal 1 & Core
      </button>
      <button type="button" class="tab-pill-btn" data-tab="tab-personal2">
        <i class="fas fa-user-friends"></i> Personal 2 (Kin & Emergency)
      </button>
      <button type="button" class="tab-pill-btn" data-tab="tab-official">
        <i class="fas fa-shield-alt"></i> Official & Clearance
      </button>
      <button type="button" class="tab-pill-btn" data-tab="tab-education">
        <i class="fas fa-graduation-cap"></i> Education ({{ count($degrees) }})
      </button>
      <button type="button" class="tab-pill-btn" data-tab="tab-courses">
        <i class="fas fa-award"></i> Courses & Certs ({{ count($certs) }})
      </button>
      <button type="button" class="tab-pill-btn" data-tab="tab-career">
        <i class="fas fa-briefcase"></i> Career ({{ count($jobs) }})
      </button>
      <button type="button" class="tab-pill-btn" data-tab="tab-vehicles">
        <i class="fas fa-car"></i> Vehicles ({{ count($vehicles) }})
      </button>
      <button type="button" class="tab-pill-btn" data-tab="tab-devices">
        <i class="fas fa-mobile-alt"></i> Devices ({{ count($devices) }})
      </button>
      <button type="button" class="tab-pill-btn" data-tab="tab-bank">
        <i class="fas fa-university"></i> Bank Accounts ({{ count($bankAccounts) }})
      </button>
    </div>

    <!-- Main Edit Form -->
    <form id="employee-edit-form">
      @csrf

      <!-- ═══════════════════════════════════════════════════════════ -->
      <!-- TAB 1: CORE & PERSONAL 1                                   -->
      <!-- ═══════════════════════════════════════════════════════════ -->
      <div class="tab-content-panel" id="tab-core-personal1">
        <div class="clean-card p-4">
          <h5 class="font-weight-bold text-dark mb-4 border-bottom pb-2">
            <i class="fas fa-id-card text-primary mr-2"></i> Core & Identity Details
          </h5>
          <div class="row">
            <div class="col-md-4 mb-3">
              <label class="rd-form-label">Employee ID</label>
              <input type="text" class="rd-input" value="{{ $emp->emp_id }}" readonly>
            </div>
            <div class="col-md-4 mb-3">
              <label class="rd-form-label">Full Name <span class="text-danger">*</span></label>
              <input type="text" name="emp_name" class="rd-input" value="{{ $emp->emp_name }}" required>
            </div>
            <div class="col-md-4 mb-3">
              <label class="rd-form-label">CNIC <span class="text-danger">*</span></label>
              <input type="text" name="emp_cnic" class="rd-input" value="{{ $emp->emp_cnic }}" required placeholder="XXXXX-XXXXXXX-X">
            </div>
          </div>

          <div class="row">
            <div class="col-md-6 mb-3">
              <label class="rd-form-label">Department / Unit <span class="text-danger">*</span></label>
              <select name="emp_unt_id" class="rd-input" required>
                @foreach($departments as $d)
                  <option value="{{ $d->unt_id }}" {{ $emp->emp_unt_id == $d->unt_id ? 'selected' : '' }}>
                    {{ $d->unt_name }} ({{ $d->unt_namesh }})
                  </option>
                @endforeach
              </select>
            </div>
            <div class="col-md-6 mb-3">
              <label class="rd-form-label">
                Assigned Project Head
                <span class="badge badge-light border text-muted ml-1" style="font-size: 10px; font-weight: 600;">
                  <i class="fas fa-lock text-secondary mr-1"></i>Bound to Contract Case
                </span>
              </label>
              <div class="input-group">
                <input type="text" class="rd-input bg-light font-weight-bold text-dark" readonly 
                       value="{{ $currentHead ? '[' . ($currentHead->prj_code ?: $currentHead->hed_code) . '] ' . ($currentHead->prj_title ?: $currentHead->hed_name) : '— None / General —' }}" 
                       style="cursor: not-allowed; border-color: #cbd5e1; background-color: #f8fafc !important;">
              </div>
              <small class="text-muted" style="font-size: 11px;">
                <i class="fas fa-shield-alt text-info mr-1"></i>Project assignment is controlled by the approved contract case and cannot be manually altered.
              </small>
            </div>
          </div>

          <div class="row">
            <div class="col-md-6 mb-3">
              <label class="rd-form-label">Job Title / Designation</label>
              <input type="text" name="emp_title" class="rd-input" value="{{ $emp->emp_title }}">
            </div>
            <div class="col-md-6 mb-3">
              <label class="rd-form-label">Rank / Scale</label>
              <input type="text" name="emp_rank" class="rd-input" value="{{ $emp->emp_rank }}">
            </div>
          </div>

          <div class="row">
            <div class="col-md-4 mb-3">
              <label class="rd-form-label">Joining Date <span class="text-danger">*</span></label>
              <input type="date" name="emp_joindt" class="rd-input" value="{{ $emp->emp_joindt ? \Carbon\Carbon::parse($emp->emp_joindt)->format('Y-m-d') : '' }}" required>
            </div>
            <div class="col-md-4 mb-3">
              <label class="rd-form-label">Status <span class="text-danger">*</span></label>
              <select name="emp_status" class="rd-input" required>
                <option value="Active" {{ $emp->emp_status === 'Active' ? 'selected' : '' }}>Active</option>
                <option value="Released" {{ $emp->emp_status === 'Released' ? 'selected' : '' }}>Released</option>
                <option value="Terminated" {{ $emp->emp_status === 'Terminated' ? 'selected' : '' }}>Terminated</option>
              </select>
            </div>
            <div class="col-md-4 mb-3">
              <label class="rd-form-label">Last Date (if separated)</label>
              <input type="date" name="emp_lastdt" class="rd-input" value="{{ $emp->emp_lastdt ? \Carbon\Carbon::parse($emp->emp_lastdt)->format('Y-m-d') : '' }}">
            </div>
          </div>

          <div class="row">
            <div class="col-md-12 mb-3">
              <label class="rd-form-label">Remarks / Status Notes</label>
              <textarea name="emp_remarks" class="rd-input" rows="2">{{ $emp->emp_remarks }}</textarea>
            </div>
          </div>
        </div>

        <div class="clean-card p-4">
          <h5 class="font-weight-bold text-dark mb-4 border-bottom pb-2">
            <i class="fas fa-user-tag text-primary mr-2"></i> Personal Information (Personal 1)
          </h5>
          <div class="row">
            <div class="col-md-4 mb-3">
              <label class="rd-form-label">Father Name</label>
              <input type="text" name="emp_father" class="rd-input" value="{{ $empA->emp_father ?? '' }}">
            </div>
            <div class="col-md-4 mb-3">
              <label class="rd-form-label">Father CNIC</label>
              <input type="text" name="emp_father_cnic" class="rd-input" value="{{ $empA->emp_father_cnic ?? '' }}" placeholder="XXXXX-XXXXXXX-X">
            </div>
            <div class="col-md-4 mb-3">
              <label class="rd-form-label">Date of Birth</label>
              <input type="date" name="emp_dob" class="rd-input" value="{{ !empty($empA->emp_dob) ? \Carbon\Carbon::parse($empA->emp_dob)->format('Y-m-d') : '' }}">
            </div>
          </div>

          <div class="row">
            <div class="col-md-3 mb-3">
              <label class="rd-form-label">Gender</label>
              <select name="emp_gender" class="rd-input">
                <option value="Male" {{ ($empA->emp_gender ?? '') === 'Male' ? 'selected' : '' }}>Male</option>
                <option value="Female" {{ ($empA->emp_gender ?? '') === 'Female' ? 'selected' : '' }}>Female</option>
                <option value="Other" {{ ($empA->emp_gender ?? '') === 'Other' ? 'selected' : '' }}>Other</option>
              </select>
            </div>
            <div class="col-md-3 mb-3">
              <label class="rd-form-label">Marital Status</label>
              <select name="emp_marital" class="rd-input">
                <option value="Single" {{ ($empA->emp_marital ?? '') === 'Single' ? 'selected' : '' }}>Single</option>
                <option value="Married" {{ ($empA->emp_marital ?? '') === 'Married' ? 'selected' : '' }}>Married</option>
                <option value="Divorced" {{ ($empA->emp_marital ?? '') === 'Divorced' ? 'selected' : '' }}>Divorced</option>
                <option value="Widowed" {{ ($empA->emp_marital ?? '') === 'Widowed' ? 'selected' : '' }}>Widowed</option>
              </select>
            </div>
            <div class="col-md-3 mb-3">
              <label class="rd-form-label">Nationality</label>
              <input type="text" name="emp_ntnlty" class="rd-input" value="{{ $empA->emp_ntnlty ?? 'Pakistani' }}">
            </div>
            <div class="col-md-3 mb-3">
              <label class="rd-form-label">Place of Birth</label>
              <input type="text" name="emp_pob" class="rd-input" value="{{ $empA->emp_pob ?? '' }}">
            </div>
          </div>

          <div class="row">
            <div class="col-md-4 mb-3">
              <label class="rd-form-label">Mobile (Primary)</label>
              <input type="text" name="emp_mobile" class="rd-input" value="{{ $empA->emp_mobile ?? '' }}">
            </div>
            <div class="col-md-4 mb-3">
              <label class="rd-form-label">Mobile (Secondary)</label>
              <input type="text" name="emp_mobile2" class="rd-input" value="{{ $empA->emp_mobile2 ?? '' }}">
            </div>
            <div class="col-md-4 mb-3">
              <label class="rd-form-label">Landline</label>
              <input type="text" name="emp_landline" class="rd-input" value="{{ $empA->emp_landline ?? '' }}">
            </div>
          </div>

          <div class="row">
            <div class="col-md-4 mb-3">
              <label class="rd-form-label">Email Address</label>
              <input type="email" name="emp_email" class="rd-input" value="{{ $empA->emp_email ?? '' }}">
            </div>
            <div class="col-md-4 mb-3">
              <label class="rd-form-label">Discipline</label>
              <input type="text" name="emp_discip" class="rd-input" value="{{ $empA->emp_discip ?? '' }}">
            </div>
            <div class="col-md-4 mb-3">
              <label class="rd-form-label">Speciality</label>
              <input type="text" name="emp_spec" class="rd-input" value="{{ $empA->emp_spec ?? '' }}">
            </div>
          </div>

          <div class="row">
            <div class="col-md-6 mb-3">
              <label class="rd-form-label">Permanent Address</label>
              <textarea name="emp_paddress" class="rd-input" rows="2">{{ $empA->emp_paddress ?? '' }}</textarea>
            </div>
            <div class="col-md-6 mb-3">
              <label class="rd-form-label">Temporary / Present Address</label>
              <textarea name="emp_taddress" class="rd-input" rows="2">{{ $empA->emp_taddress ?? '' }}</textarea>
            </div>
          </div>
        </div>
      </div>

      <!-- ═══════════════════════════════════════════════════════════ -->
      <!-- TAB 2: PERSONAL 2 (Kin & Emergency)                        -->
      <!-- ═══════════════════════════════════════════════════════════ -->
      <div class="tab-content-panel d-none" id="tab-personal2">
        <div class="clean-card p-4">
          <h5 class="font-weight-bold text-dark mb-4 border-bottom pb-2">
            <i class="fas fa-heart text-danger mr-2"></i> Next of Kin & Emergency Contacts
          </h5>
          <div class="row">
            <div class="col-md-4 mb-3">
              <label class="rd-form-label">Next of Kin Name</label>
              <input type="text" name="emp_nokname" class="rd-input" value="{{ $empB->emp_nokname ?? '' }}">
            </div>
            <div class="col-md-4 mb-3">
              <label class="rd-form-label">Next of Kin Relation</label>
              <input type="text" name="emp_nokrelation" class="rd-input" value="{{ $empB->emp_nokrelation ?? '' }}">
            </div>
            <div class="col-md-4 mb-3">
              <label class="rd-form-label">Next of Kin CNIC</label>
              <input type="text" name="emp_nokcnic" class="rd-input" value="{{ $empB->emp_nokcnic ?? '' }}">
            </div>
          </div>

          <div class="row">
            <div class="col-md-4 mb-3">
              <label class="rd-form-label">Emergency Contact Name</label>
              <input type="text" name="emp_emername" class="rd-input" value="{{ $empB->emp_emername ?? '' }}">
            </div>
            <div class="col-md-4 mb-3">
              <label class="rd-form-label">Emergency Contact Relation</label>
              <input type="text" name="emp_emerrelation" class="rd-input" value="{{ $empB->emp_emerrelation ?? '' }}">
            </div>
            <div class="col-md-4 mb-3">
              <label class="rd-form-label">Emergency Contact Mobile</label>
              <input type="text" name="emp_emermobile" class="rd-input" value="{{ $empB->emp_emermobile ?? '' }}">
            </div>
          </div>

          <h5 class="font-weight-bold text-dark mt-4 mb-4 border-bottom pb-2">
            <i class="fas fa-fingerprint text-primary mr-2"></i> Identification & Demographics
          </h5>
          <div class="row">
            <div class="col-md-4 mb-3">
              <label class="rd-form-label">Identification Mark</label>
              <input type="text" name="emp_idmark" class="rd-input" value="{{ $empB->emp_idmark ?? '' }}">
            </div>
            <div class="col-md-4 mb-3">
              <label class="rd-form-label">Height (inches / cm)</label>
              <input type="number" step="0.1" name="emp_height" class="rd-input" value="{{ $empB->emp_height ?? '' }}">
            </div>
            <div class="col-md-4 mb-3">
              <label class="rd-form-label">Caste / Clan</label>
              <input type="text" name="emp_caste" class="rd-input" value="{{ $empB->emp_caste ?? '' }}">
            </div>
          </div>

          <div class="row">
            <div class="col-md-3 mb-3">
              <label class="rd-form-label">Religion</label>
              <input type="text" name="emp_religion" class="rd-input" value="{{ $empB->emp_religion ?? 'Islam' }}">
            </div>
            <div class="col-md-3 mb-3">
              <label class="rd-form-label">Sect</label>
              <input type="text" name="emp_sect" class="rd-input" value="{{ $empB->emp_sect ?? '' }}">
            </div>
            <div class="col-md-3 mb-3">
              <label class="rd-form-label">Police Station Jurisdiction</label>
              <input type="text" name="emp_police" class="rd-input" value="{{ $empB->emp_police ?? '' }}">
            </div>
            <div class="col-md-3 mb-3">
              <label class="rd-form-label">Political Affiliation</label>
              <input type="text" name="emp_political" class="rd-input" value="{{ $empB->emp_political ?? 'None' }}">
            </div>
          </div>
        </div>
      </div>

      <!-- ═══════════════════════════════════════════════════════════ -->
      <!-- TAB 3: OFFICIAL & SECURITY CLEARANCE                       -->
      <!-- ═══════════════════════════════════════════════════════════ -->
      <div class="tab-content-panel d-none" id="tab-official">
        <div class="clean-card p-4">
          <h5 class="font-weight-bold text-dark mb-4 border-bottom pb-2">
            <i class="fas fa-shield-alt text-primary mr-2"></i> Security Clearance & Official Record
          </h5>
          <div class="row">
            <div class="col-md-3 mb-3">
              <label class="rd-form-label">Clearance Number</label>
              <input type="text" name="emp_cnum" class="rd-input" value="{{ $empC->emp_cnum ?? '' }}">
            </div>
            <div class="col-md-3 mb-3">
              <label class="rd-form-label">Clearance Issue Date</label>
              <input type="date" name="emp_cissuedt" class="rd-input" value="{{ !empty($empC->emp_cissuedt) ? \Carbon\Carbon::parse($empC->emp_cissuedt)->format('Y-m-d') : '' }}">
            </div>
            <div class="col-md-3 mb-3">
              <label class="rd-form-label">Clearance Expiry Date</label>
              <input type="date" name="emp_cexpdt" class="rd-input" value="{{ !empty($empC->emp_cexpdt) ? \Carbon\Carbon::parse($empC->emp_cexpdt)->format('Y-m-d') : '' }}">
            </div>
            <div class="col-md-3 mb-3">
              <label class="rd-form-label">Security Clearance Status</label>
              <select name="emp_secclear" class="rd-input">
                <option value="">-- Select Status --</option>
                <option value="Cleared" {{ ($empC->emp_secclear ?? '') === 'Cleared' ? 'selected' : '' }}>Cleared</option>
                <option value="In Process" {{ ($empC->emp_secclear ?? '') === 'In Process' ? 'selected' : '' }}>In Process</option>
                <option value="Not Cleared" {{ ($empC->emp_secclear ?? '') === 'Not Cleared' ? 'selected' : '' }}>Not Cleared</option>
              </select>
            </div>
          </div>
        </div>
      </div>

      <!-- ═══════════════════════════════════════════════════════════ -->
      <!-- TAB 4: EDUCATION (DEGREES)                                 -->
      <!-- ═══════════════════════════════════════════════════════════ -->
      <div class="tab-content-panel d-none" id="tab-education">
        <div class="clean-card p-4">
          <div class="d-flex justify-content-between align-items-center mb-3">
            <h5 class="font-weight-bold text-dark mb-0">
              <i class="fas fa-graduation-cap text-primary mr-2"></i> Academic Qualifications (Degrees)
            </h5>
            <button type="button" class="btn-add-row" id="btn-add-degree">
              <i class="fas fa-plus mr-1"></i> Add Degree
            </button>
          </div>

          <div class="table-responsive">
            <table class="grid-table" id="table-degrees">
              <thead>
                <tr>
                  <th style="width: 22%;">Degree / Program</th>
                  <th style="width: 22%;">Institute / University</th>
                  <th style="width: 15%;">Major / Spec</th>
                  <th style="width: 10%;">Duration</th>
                  <th style="width: 10%;">Unit</th>
                  <th style="width: 10%;">Year / End Date</th>
                  <th style="width: 7%;">Grade / GPA</th>
                  <th style="width: 4%;"></th>
                </tr>
              </thead>
              <tbody>
                @forelse($degrees as $idx => $d)
                  <tr>
                    <td><input type="text" name="degrees[{{ $idx }}][qlf_name]" class="rd-input" value="{{ $d->qlf_name }}" placeholder="e.g. BS Computer Science"></td>
                    <td><input type="text" name="degrees[{{ $idx }}][qlf_inst]" class="rd-input" value="{{ $d->qlf_inst }}" placeholder="e.g. NUST"></td>
                    <td><input type="text" name="degrees[{{ $idx }}][qlf_spec]" class="rd-input" value="{{ $d->qlf_spec }}" placeholder="Software Engineering"></td>
                    <td><input type="number" step="0.5" name="degrees[{{ $idx }}][qlf_duration]" class="rd-input" value="{{ $d->qlf_duration }}"></td>
                    <td>
                      <select name="degrees[{{ $idx }}][qlf_unit]" class="rd-input">
                        <option value="Years" {{ ($d->qlf_unit ?? '') === 'Years' ? 'selected' : '' }}>Years</option>
                        <option value="Months" {{ ($d->qlf_unit ?? '') === 'Months' ? 'selected' : '' }}>Months</option>
                      </select>
                    </td>
                    <td><input type="date" name="degrees[{{ $idx }}][qlf_enddt]" class="rd-input" value="{{ $d->qlf_enddt ? \Carbon\Carbon::parse($d->qlf_enddt)->format('Y-m-d') : '' }}"></td>
                    <td><input type="text" name="degrees[{{ $idx }}][qlf_grade]" class="rd-input" value="{{ $d->qlf_grade }}" placeholder="3.8 / A"></td>
                    <td class="text-center">
                      <button type="button" class="btn-remove-row"><i class="fas fa-trash-alt"></i></button>
                    </td>
                  </tr>
                @empty
                  <tr class="no-records-row">
                    <td colspan="8" class="text-center text-muted py-3">No degrees recorded. Click "Add Degree" to create.</td>
                  </tr>
                @endforelse
              </tbody>
            </table>
          </div>
        </div>
      </div>

      <!-- ═══════════════════════════════════════════════════════════ -->
      <!-- TAB 5: COURSES & CERTIFICATIONS                            -->
      <!-- ═══════════════════════════════════════════════════════════ -->
      <div class="tab-content-panel d-none" id="tab-courses">
        <div class="clean-card p-4">
          <div class="d-flex justify-content-between align-items-center mb-3">
            <h5 class="font-weight-bold text-dark mb-0">
              <i class="fas fa-award text-primary mr-2"></i> Professional Courses & Certifications
            </h5>
            <button type="button" class="btn-add-row" id="btn-add-cert">
              <i class="fas fa-plus mr-1"></i> Add Course / Cert
            </button>
          </div>

          <div class="table-responsive">
            <table class="grid-table" id="table-certs">
              <thead>
                <tr>
                  <th style="width: 25%;">Course / Certification Title</th>
                  <th style="width: 25%;">Issuing Organization / Institute</th>
                  <th style="width: 15%;">License / Cert ID</th>
                  <th style="width: 10%;">Duration</th>
                  <th style="width: 10%;">Unit</th>
                  <th style="width: 11%;">Completion Date</th>
                  <th style="width: 4%;"></th>
                </tr>
              </thead>
              <tbody>
                @forelse($certs as $idx => $c)
                  <tr>
                    <td><input type="text" name="certs[{{ $idx }}][qlf_name]" class="rd-input" value="{{ $c->qlf_name }}" placeholder="e.g. AWS Certified Solutions Architect"></td>
                    <td><input type="text" name="certs[{{ $idx }}][qlf_inst]" class="rd-input" value="{{ $c->qlf_inst }}" placeholder="e.g. Amazon Web Services"></td>
                    <td><input type="text" name="certs[{{ $idx }}][qlf_license]" class="rd-input" value="{{ $c->qlf_license }}"></td>
                    <td><input type="number" step="0.5" name="certs[{{ $idx }}][qlf_duration]" class="rd-input" value="{{ $c->qlf_duration }}"></td>
                    <td>
                      <select name="certs[{{ $idx }}][qlf_unit]" class="rd-input">
                        <option value="Months" {{ ($c->qlf_unit ?? '') === 'Months' ? 'selected' : '' }}>Months</option>
                        <option value="Weeks" {{ ($c->qlf_unit ?? '') === 'Weeks' ? 'selected' : '' }}>Weeks</option>
                        <option value="Days" {{ ($c->qlf_unit ?? '') === 'Days' ? 'selected' : '' }}>Days</option>
                        <option value="Years" {{ ($c->qlf_unit ?? '') === 'Years' ? 'selected' : '' }}>Years</option>
                      </select>
                    </td>
                    <td><input type="date" name="certs[{{ $idx }}][qlf_enddt]" class="rd-input" value="{{ $c->qlf_enddt ? \Carbon\Carbon::parse($c->qlf_enddt)->format('Y-m-d') : '' }}"></td>
                    <td class="text-center">
                      <button type="button" class="btn-remove-row"><i class="fas fa-trash-alt"></i></button>
                    </td>
                  </tr>
                @empty
                  <tr class="no-records-row">
                    <td colspan="7" class="text-center text-muted py-3">No certifications recorded. Click "Add Course / Cert" to create.</td>
                  </tr>
                @endforelse
              </tbody>
            </table>
          </div>
        </div>
      </div>

      <!-- ═══════════════════════════════════════════════════════════ -->
      <!-- TAB 6: CAREER EXPERIENCE                                   -->
      <!-- ═══════════════════════════════════════════════════════════ -->
      <div class="tab-content-panel d-none" id="tab-career">
        <div class="clean-card p-4">
          <div class="d-flex justify-content-between align-items-center mb-3">
            <h5 class="font-weight-bold text-dark mb-0">
              <i class="fas fa-briefcase text-primary mr-2"></i> Previous Employment & Career History
            </h5>
            <button type="button" class="btn-add-row" id="btn-add-job">
              <i class="fas fa-plus mr-1"></i> Add Career Entry
            </button>
          </div>

          <div class="table-responsive">
            <table class="grid-table" id="table-jobs">
              <thead>
                <tr>
                  <th style="width: 18%;">Company / Organization</th>
                  <th style="width: 16%;">Designation / Job Title</th>
                  <th style="width: 12%;">Reported To</th>
                  <th style="width: 8%;">Team Size</th>
                  <th style="width: 11%;">From Date</th>
                  <th style="width: 11%;">To Date</th>
                  <th style="width: 10%;">City</th>
                  <th style="width: 10%;">Key Responsibilities</th>
                  <th style="width: 4%;"></th>
                </tr>
              </thead>
              <tbody>
                @forelse($jobs as $idx => $j)
                  <tr>
                    <td><input type="text" name="jobs[{{ $idx }}][job_company]" class="rd-input" value="{{ $j->job_company }}" placeholder="Company name"></td>
                    <td><input type="text" name="jobs[{{ $idx }}][job_jobtitle]" class="rd-input" value="{{ $j->job_jobtitle }}" placeholder="Job title"></td>
                    <td><input type="text" name="jobs[{{ $idx }}][job_repto]" class="rd-input" value="{{ $j->job_repto }}" placeholder="Manager / Lead"></td>
                    <td><input type="number" name="jobs[{{ $idx }}][job_team]" class="rd-input" value="{{ $j->job_team }}" placeholder="0"></td>
                    <td><input type="date" name="jobs[{{ $idx }}][job_from]" class="rd-input" value="{{ $j->job_from ? \Carbon\Carbon::parse($j->job_from)->format('Y-m-d') : '' }}"></td>
                    <td><input type="date" name="jobs[{{ $idx }}][job_to]" class="rd-input" value="{{ $j->job_to ? \Carbon\Carbon::parse($j->job_to)->format('Y-m-d') : '' }}"></td>
                    <td><input type="text" name="jobs[{{ $idx }}][job_city]" class="rd-input" value="{{ $j->job_city ?? 'Karachi' }}"></td>
                    <td><input type="text" name="jobs[{{ $idx }}][job_resp]" class="rd-input" value="{{ $j->job_resp }}" placeholder="Summary"></td>
                    <td class="text-center">
                      <button type="button" class="btn-remove-row"><i class="fas fa-trash-alt"></i></button>
                    </td>
                  </tr>
                @empty
                  <tr class="no-records-row">
                    <td colspan="9" class="text-center text-muted py-3">No career records found. Click "Add Career Entry" to create.</td>
                  </tr>
                @endforelse
              </tbody>
            </table>
          </div>
        </div>
      </div>

      <!-- ═══════════════════════════════════════════════════════════ -->
      <!-- TAB 7: VEHICLES                                            -->
      <!-- ═══════════════════════════════════════════════════════════ -->
      <div class="tab-content-panel d-none" id="tab-vehicles">
        <div class="clean-card p-4">
          <div class="d-flex justify-content-between align-items-center mb-3">
            <h5 class="font-weight-bold text-dark mb-0">
              <i class="fas fa-car text-primary mr-2"></i> Registered Vehicles
            </h5>
            <button type="button" class="btn-add-row" id="btn-add-vehicle">
              <i class="fas fa-plus mr-1"></i> Add Vehicle
            </button>
          </div>

          <div class="table-responsive">
            <table class="grid-table" id="table-vehicles">
              <thead>
                <tr>
                  <th style="width: 15%;">Vehicle Type</th>
                  <th style="width: 20%;">Maker / Brand</th>
                  <th style="width: 20%;">Model / Variant</th>
                  <th style="width: 12%;">Model Year</th>
                  <th style="width: 18%;">Registration Number</th>
                  <th style="width: 11%;">Color</th>
                  <th style="width: 4%;"></th>
                </tr>
              </thead>
              <tbody>
                @forelse($vehicles as $idx => $v)
                  <tr>
                    <td>
                      <select name="vehicles[{{ $idx }}][vcl_type]" class="rd-input">
                        <option value="Car" {{ ($v->vcl_type ?? '') === 'Car' ? 'selected' : '' }}>Car</option>
                        <option value="Motorcycle" {{ ($v->vcl_type ?? '') === 'Motorcycle' ? 'selected' : '' }}>Motorcycle</option>
                        <option value="Jeep/SUV" {{ ($v->vcl_type ?? '') === 'Jeep/SUV' ? 'selected' : '' }}>Jeep/SUV</option>
                        <option value="Van" {{ ($v->vcl_type ?? '') === 'Van' ? 'selected' : '' }}>Van</option>
                      </select>
                    </td>
                    <td><input type="text" name="vehicles[{{ $idx }}][vcl_maker]" class="rd-input" value="{{ $v->vcl_maker }}" placeholder="e.g. Toyota"></td>
                    <td><input type="text" name="vehicles[{{ $idx }}][vcl_variant]" class="rd-input" value="{{ $v->vcl_variant }}" placeholder="e.g. Corolla Altis"></td>
                    <td><input type="number" name="vehicles[{{ $idx }}][vcl_year]" class="rd-input" value="{{ $v->vcl_year }}" placeholder="2022"></td>
                    <td><input type="text" name="vehicles[{{ $idx }}][vcl_regis]" class="rd-input" value="{{ $v->vcl_regis }}" placeholder="ABC-123"></td>
                    <td><input type="text" name="vehicles[{{ $idx }}][vcl_color]" class="rd-input" value="{{ $v->vcl_color }}" placeholder="White"></td>
                    <td class="text-center">
                      <button type="button" class="btn-remove-row"><i class="fas fa-trash-alt"></i></button>
                    </td>
                  </tr>
                @empty
                  <tr class="no-records-row">
                    <td colspan="7" class="text-center text-muted py-3">No vehicles registered. Click "Add Vehicle" to register.</td>
                  </tr>
                @endforelse
              </tbody>
            </table>
          </div>
        </div>
      </div>

      <!-- ═══════════════════════════════════════════════════════════ -->
      <!-- TAB 8: DEVICES                                             -->
      <!-- ═══════════════════════════════════════════════════════════ -->
      <div class="tab-content-panel d-none" id="tab-devices">
        <div class="clean-card p-4">
          <div class="d-flex justify-content-between align-items-center mb-3">
            <h5 class="font-weight-bold text-dark mb-0">
              <i class="fas fa-mobile-alt text-primary mr-2"></i> Official & Registered Devices
            </h5>
            <button type="button" class="btn-add-row" id="btn-add-device">
              <i class="fas fa-plus mr-1"></i> Add Device
            </button>
          </div>

          <div class="table-responsive">
            <table class="grid-table" id="table-devices">
              <thead>
                <tr>
                  <th style="width: 18%;">Device Type</th>
                  <th style="width: 20%;">Brand / Manufacturer</th>
                  <th style="width: 20%;">Model</th>
                  <th style="width: 19%;">IMEI 1 / Serial</th>
                  <th style="width: 19%;">IMEI 2 (Optional)</th>
                  <th style="width: 4%;"></th>
                </tr>
              </thead>
              <tbody>
                @forelse($devices as $idx => $d)
                  <tr>
                    <td>
                      <select name="devices[{{ $idx }}][dvc_type]" class="rd-input">
                        <option value="Mobile Phone" {{ ($d->dvc_type ?? '') === 'Mobile Phone' ? 'selected' : '' }}>Mobile Phone</option>
                        <option value="Laptop" {{ ($d->dvc_type ?? '') === 'Laptop' ? 'selected' : '' }}>Laptop</option>
                        <option value="Tablet" {{ ($d->dvc_type ?? '') === 'Tablet' ? 'selected' : '' }}>Tablet</option>
                        <option value="Modem/Dongle" {{ ($d->dvc_type ?? '') === 'Modem/Dongle' ? 'selected' : '' }}>Modem/Dongle</option>
                      </select>
                    </td>
                    <td><input type="text" name="devices[{{ $idx }}][dvc_brand]" class="rd-input" value="{{ $d->dvc_brand }}" placeholder="e.g. Samsung"></td>
                    <td><input type="text" name="devices[{{ $idx }}][dvc_model]" class="rd-input" value="{{ $d->dvc_model }}" placeholder="e.g. Galaxy S21"></td>
                    <td><input type="text" name="devices[{{ $idx }}][dvc_imei1]" class="rd-input" value="{{ $d->dvc_imei1 }}" placeholder="15-digit IMEI"></td>
                    <td><input type="text" name="devices[{{ $idx }}][dvc_imei2]" class="rd-input" value="{{ $d->dvc_imei2 }}" placeholder="Optional"></td>
                    <td class="text-center">
                      <button type="button" class="btn-remove-row"><i class="fas fa-trash-alt"></i></button>
                    </td>
                  </tr>
                @empty
                  <tr class="no-records-row">
                    <td colspan="6" class="text-center text-muted py-3">No devices registered. Click "Add Device" to register.</td>
                  </tr>
                @endforelse
              </tbody>
            </table>
          </div>
        </div>
      </div>

      <!-- ═══════════════════════════════════════════════════════════ -->
      <!-- TAB 9: BANK ACCOUNTS                                       -->
      <!-- ═══════════════════════════════════════════════════════════ -->
      <div class="tab-content-panel d-none" id="tab-bank">
        <div class="clean-card p-4">
          <div class="d-flex justify-content-between align-items-center mb-3">
            <h5 class="font-weight-bold text-dark mb-0">
              <i class="fas fa-university text-primary mr-2"></i> Bank Accounts & Salary Disbursement
            </h5>
            <button type="button" class="btn-add-row" id="btn-add-bank">
              <i class="fas fa-plus mr-1"></i> Add Bank Account
            </button>
          </div>

          <div class="table-responsive">
            <table class="grid-table" id="table-bank">
              <thead>
                <tr>
                  <th style="width: 20%;">Bank Name</th>
                  <th style="width: 18%;">Account Title</th>
                  <th style="width: 20%;">Account Number / IBAN</th>
                  <th style="width: 16%;">Branch Name</th>
                  <th style="width: 12%;">Branch City</th>
                  <th style="width: 10%;">Primary for Salary</th>
                  <th style="width: 4%;"></th>
                </tr>
              </thead>
              <tbody>
                @forelse($bankAccounts as $idx => $ba)
                  <tr>
                    <td><input type="text" name="bank_accounts[{{ $idx }}][bac_bnkname]" class="rd-input" value="{{ $ba->bac_bnkname }}" placeholder="e.g. Habib Bank Limited"></td>
                    <td><input type="text" name="bank_accounts[{{ $idx }}][bac_acctitle]" class="rd-input" value="{{ $ba->bac_acctitle }}" placeholder="Account Title"></td>
                    <td><input type="text" name="bank_accounts[{{ $idx }}][bac_accnum]" class="rd-input" value="{{ $ba->bac_accnum }}" placeholder="PK00HABB0000000000000000"></td>
                    <td><input type="text" name="bank_accounts[{{ $idx }}][bac_bchname]" class="rd-input" value="{{ $ba->bac_bchname }}" placeholder="Branch Name"></td>
                    <td><input type="text" name="bank_accounts[{{ $idx }}][bac_bchcity]" class="rd-input" value="{{ $ba->bac_bchcity ?? 'Karachi' }}"></td>
                    <td class="text-center">
                      <input type="checkbox" name="bank_accounts[{{ $idx }}][bac_selforpay]" value="1" {{ !empty($ba->bac_selforpay) ? 'checked' : '' }} style="transform: scale(1.3);">
                    </td>
                    <td class="text-center">
                      <button type="button" class="btn-remove-row"><i class="fas fa-trash-alt"></i></button>
                    </td>
                  </tr>
                @empty
                  <tr class="no-records-row">
                    <td colspan="7" class="text-center text-muted py-3">No bank accounts recorded. Click "Add Bank Account" to create.</td>
                  </tr>
                @endforelse
              </tbody>
            </table>
          </div>
        </div>
      </div>

    </form>
  </div>
</div>

@endsection

@push('scripts')
<script>
$(document).ready(function() {

  // ── Tab Switching Logic ────────────────────────────────────
  $('.tab-pill-btn').click(function(e) {
    e.preventDefault();
    const targetTab = $(this).data('tab');

    $('.tab-pill-btn').removeClass('active');
    $(this).addClass('active');

    $('.tab-content-panel').addClass('d-none');
    $('#' + targetTab).removeClass('d-none');
  });

  // ── Dynamic Row Removers ──────────────────────────────────
  $(document).on('click', '.btn-remove-row', function() {
    const table = $(this).closest('table');
    $(this).closest('tr').remove();
    if (table.find('tbody tr').length === 0) {
      table.find('tbody').append('<tr class="no-records-row"><td colspan="10" class="text-center text-muted py-3">No records found. Click add button above.</td></tr>');
    }
  });

  function clearEmptyRow(tableSelector) {
    $(tableSelector).find('tbody tr.no-records-row').remove();
  }

  // ── Add Degree Row ─────────────────────────────────────────
  $('#btn-add-degree').click(function() {
    clearEmptyRow('#table-degrees');
    const idx = Date.now();
    const html = `
      <tr>
        <td><input type="text" name="degrees[${idx}][qlf_name]" class="rd-input" placeholder="e.g. BS Computer Science"></td>
        <td><input type="text" name="degrees[${idx}][qlf_inst]" class="rd-input" placeholder="e.g. NUST"></td>
        <td><input type="text" name="degrees[${idx}][qlf_spec]" class="rd-input" placeholder="Major / Spec"></td>
        <td><input type="number" step="0.5" name="degrees[${idx}][qlf_duration]" class="rd-input" value="4"></td>
        <td>
          <select name="degrees[${idx}][qlf_unit]" class="rd-input">
            <option value="Years" selected>Years</option>
            <option value="Months">Months</option>
          </select>
        </td>
        <td><input type="date" name="degrees[${idx}][qlf_enddt]" class="rd-input" value="{{ date('Y-m-d') }}"></td>
        <td><input type="text" name="degrees[${idx}][qlf_grade]" class="rd-input" placeholder="GPA / Div"></td>
        <td class="text-center">
          <button type="button" class="btn-remove-row"><i class="fas fa-trash-alt"></i></button>
        </td>
      </tr>
    `;
    $('#table-degrees tbody').append(html);
  });

  // ── Add Cert Row ───────────────────────────────────────────
  $('#btn-add-cert').click(function() {
    clearEmptyRow('#table-certs');
    const idx = Date.now();
    const html = `
      <tr>
        <td><input type="text" name="certs[${idx}][qlf_name]" class="rd-input" placeholder="Course / Certification"></td>
        <td><input type="text" name="certs[${idx}][qlf_inst]" class="rd-input" placeholder="Institute / Issuer"></td>
        <td><input type="text" name="certs[${idx}][qlf_license]" class="rd-input" placeholder="License / ID"></td>
        <td><input type="number" step="0.5" name="certs[${idx}][qlf_duration]" class="rd-input" value="3"></td>
        <td>
          <select name="certs[${idx}][qlf_unit]" class="rd-input">
            <option value="Months" selected>Months</option>
            <option value="Weeks">Weeks</option>
            <option value="Days">Days</option>
            <option value="Years">Years</option>
          </select>
        </td>
        <td><input type="date" name="certs[${idx}][qlf_enddt]" class="rd-input" value="{{ date('Y-m-d') }}"></td>
        <td class="text-center">
          <button type="button" class="btn-remove-row"><i class="fas fa-trash-alt"></i></button>
        </td>
      </tr>
    `;
    $('#table-certs tbody').append(html);
  });

  // ── Add Career Entry ───────────────────────────────────────
  $('#btn-add-job').click(function() {
    clearEmptyRow('#table-jobs');
    const idx = Date.now();
    const html = `
      <tr>
        <td><input type="text" name="jobs[${idx}][job_company]" class="rd-input" placeholder="Company name"></td>
        <td><input type="text" name="jobs[${idx}][job_jobtitle]" class="rd-input" placeholder="Job title"></td>
        <td><input type="text" name="jobs[${idx}][job_repto]" class="rd-input" placeholder="Manager / Lead"></td>
        <td><input type="number" name="jobs[${idx}][job_team]" class="rd-input" placeholder="0"></td>
        <td><input type="date" name="jobs[${idx}][job_from]" class="rd-input" value="{{ date('Y-m-d') }}"></td>
        <td><input type="date" name="jobs[${idx}][job_to]" class="rd-input"></td>
        <td><input type="text" name="jobs[${idx}][job_city]" class="rd-input" value="Karachi"></td>
        <td><input type="text" name="jobs[${idx}][job_resp]" class="rd-input" placeholder="Summary"></td>
        <td class="text-center">
          <button type="button" class="btn-remove-row"><i class="fas fa-trash-alt"></i></button>
        </td>
      </tr>
    `;
    $('#table-jobs tbody').append(html);
  });

  // ── Add Vehicle ────────────────────────────────────────────
  $('#btn-add-vehicle').click(function() {
    clearEmptyRow('#table-vehicles');
    const idx = Date.now();
    const html = `
      <tr>
        <td>
          <select name="vehicles[${idx}][vcl_type]" class="rd-input">
            <option value="Car" selected>Car</option>
            <option value="Motorcycle">Motorcycle</option>
            <option value="Jeep/SUV">Jeep/SUV</option>
            <option value="Van">Van</option>
          </select>
        </td>
        <td><input type="text" name="vehicles[${idx}][vcl_maker]" class="rd-input" placeholder="e.g. Honda"></td>
        <td><input type="text" name="vehicles[${idx}][vcl_variant]" class="rd-input" placeholder="e.g. Civic"></td>
        <td><input type="number" name="vehicles[${idx}][vcl_year]" class="rd-input" value="{{ date('Y') }}"></td>
        <td><input type="text" name="vehicles[${idx}][vcl_regis]" class="rd-input" placeholder="ABC-123"></td>
        <td><input type="text" name="vehicles[${idx}][vcl_color]" class="rd-input" placeholder="Silver"></td>
        <td class="text-center">
          <button type="button" class="btn-remove-row"><i class="fas fa-trash-alt"></i></button>
        </td>
      </tr>
    `;
    $('#table-vehicles tbody').append(html);
  });

  // ── Add Device ─────────────────────────────────────────────
  $('#btn-add-device').click(function() {
    clearEmptyRow('#table-devices');
    const idx = Date.now();
    const html = `
      <tr>
        <td>
          <select name="devices[${idx}][dvc_type]" class="rd-input">
            <option value="Mobile Phone" selected>Mobile Phone</option>
            <option value="Laptop">Laptop</option>
            <option value="Tablet">Tablet</option>
            <option value="Modem/Dongle">Modem/Dongle</option>
          </select>
        </td>
        <td><input type="text" name="devices[${idx}][dvc_brand]" class="rd-input" placeholder="e.g. Dell"></td>
        <td><input type="text" name="devices[${idx}][dvc_model]" class="rd-input" placeholder="e.g. Latitude"></td>
        <td><input type="text" name="devices[${idx}][dvc_imei1]" class="rd-input" placeholder="Serial / IMEI 1"></td>
        <td><input type="text" name="devices[${idx}][dvc_imei2]" class="rd-input" placeholder="Optional IMEI 2"></td>
        <td class="text-center">
          <button type="button" class="btn-remove-row"><i class="fas fa-trash-alt"></i></button>
        </td>
      </tr>
    `;
    $('#table-devices tbody').append(html);
  });

  // ── Add Bank Account ───────────────────────────────────────
  $('#btn-add-bank').click(function() {
    clearEmptyRow('#table-bank');
    const idx = Date.now();
    const html = `
      <tr>
        <td><input type="text" name="bank_accounts[${idx}][bac_bnkname]" class="rd-input" placeholder="Bank Name"></td>
        <td><input type="text" name="bank_accounts[${idx}][bac_acctitle]" class="rd-input" placeholder="Account Title"></td>
        <td><input type="text" name="bank_accounts[${idx}][bac_accnum]" class="rd-input" placeholder="Account Number / IBAN"></td>
        <td><input type="text" name="bank_accounts[${idx}][bac_bchname]" class="rd-input" placeholder="Branch Name"></td>
        <td><input type="text" name="bank_accounts[${idx}][bac_bchcity]" class="rd-input" value="Karachi"></td>
        <td class="text-center">
          <input type="checkbox" name="bank_accounts[${idx}][bac_selforpay]" value="1" checked style="transform: scale(1.3);">
        </td>
        <td class="text-center">
          <button type="button" class="btn-remove-row"><i class="fas fa-trash-alt"></i></button>
        </td>
      </tr>
    `;
    $('#table-bank tbody').append(html);
  });

  // ── Auto-format CNIC Inputs (XXXXX-XXXXXXX-X) ──────────────
  $(document).on('input', 'input[name="emp_cnic"], input[name="emp_father_cnic"], input[name="emp_nokcnic"]', function() {
    let val = $(this).val().replace(/\D/g, '');
    if (val.length > 13) val = val.substring(0, 13);
    let formatted = '';
    if (val.length > 0) {
      formatted += val.substring(0, Math.min(5, val.length));
    }
    if (val.length > 5) {
      formatted += '-' + val.substring(5, Math.min(12, val.length));
    }
    if (val.length > 12) {
      formatted += '-' + val.substring(12, 13);
    }
    $(this).val(formatted);
  });

  // ── Save Form via AJAX ─────────────────────────────────────
  $('#btn-save-profile').click(function(e) {
    e.preventDefault();

    const form = $('#employee-edit-form')[0];
    if (!form.checkValidity()) {
      form.reportValidity();
      return;
    }

    const btn = $(this);
    btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin mr-1"></i> Saving...');

    $.ajax({
      url: "{{ route('divhr.employee.update', $emp->emp_id) }}",
      method: 'POST',
      data: $('#employee-edit-form').serialize(),
      success: function(res) {
        Swal.fire({
          title: 'Saved Successfully',
          text: res.message || 'Employee profile updated.',
          icon: 'success',
          confirmButtonColor: '#5F7858',
        }).then(() => {
          window.location.href = res.redirect_url || "{{ route('divhr.employeedetail', $emp->emp_id) }}";
        });
      },
      error: function(err) {
        btn.prop('disabled', false).html('<i class="fas fa-save mr-1"></i> Save All Changes');
        const msg = err.responseJSON && err.responseJSON.message ? err.responseJSON.message : 'Failed to save employee profile.';
        Swal.fire('Error', msg, 'error');
      }
    });
  });

});
</script>
@endpush
