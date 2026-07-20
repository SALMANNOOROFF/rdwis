@extends('welcome')

@section('content')
@php
    // Mocking data for demonstration
    $type = request('type') ?? 'inland'; 
    $t = (object)[
        'id' => $id ?? '1024',
        'employee_name' => 'Salman Noor',
        'employee_id' => 'EMP-7643',
        'department' => 'Software Engineering',
        'designation' => 'Senior Developer',
        'type' => $type,
        'title' => 'Advanced Laravel & Microservices Architecture',
        'institute' => 'PIDE / Tech Academy',
        'start_date' => '2026-04-15',
        'end_date' => '2026-04-20',
        'duration' => '5 Days',
        'purpose' => 'System scalability and modernization',
        'status' => 'Pending',
        'remarks' => 'Requested for upcoming ERP overhaul phase.',
        // Inland
        'city' => 'Islamabad',
        'venue' => 'Serena Business Complex',
        'travel_mode' => 'Air',
        'departure_date' => '2026-04-14',
        'return_date' => '2026-04-21',
        'da_rate' => 4500,
        'num_days' => 6,
        'total_da' => 27000,
        'travel_cost' => 35000,
        'hotel_cost' => 48000,
        'total_claim' => 110000,
        // In-house
        'trainer' => 'Dr. Arshad Ali',
        'hall' => 'Conference Room A',
        'num_participants' => 15,
        // Online
        'platform' => 'Udemy / Coursera',
        'course_link' => 'https://udemy.com/laravel-pro',
        'course_fee' => '$199',
        'payment_status' => 'Pending Approval',
        'completion_status' => 'Enrolled'
    ];
@endphp

<div class="content-wrapper">
    <section class="content-header">
        <div class="container-fluid">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <div>
                    <h1 class="font-weight-bold" style="color:var(--rd-text1);">Training Case #{{ $t->id }}</h1>
                    <div class="d-flex align-items-center pt-1">
                        @if($t->status == 'Approved')
                            <span class="badge badge-success px-3 py-1 mr-2"><i class="fas fa-check-circle mr-1"></i> Approved</span>
                        @else
                            <span class="badge badge-warning px-3 py-1 mr-2" style="background:rgba(243, 156, 18, 0.1); color:#f39c12; border:1px solid #f39c12;"><i class="fas fa-clock mr-1"></i> Pending Approval</span>
                        @endif
                        <span class="text-muted small">Submitted on 25 Mar 2026</span>
                    </div>
                </div>
                <div class="d-flex gap-2">
                    <button class="btn btn-sm btn-outline-light mr-2"><i class="fas fa-print mr-1"></i> Print Case</button>
                    <a href="{{ route('training.index') }}" class="btn btn-sm btn-outline-light"><i class="fas fa-arrow-left mr-1"></i> Back</a>
                </div>
            </div>
        </div>
    </section>

    <section class="content">
        <div class="container-fluid">
            <div class="row">
                {{-- Left Column: Main Details --}}
                <div class="col-md-8">
                    {{-- 1. Employee Info --}}
                    <div class="card bg-dark border-0 mb-4" style="border-radius:12px; border-top: 4px solid var(--rd-accent) !important;">
                        <div class="card-body">
                            <h6 class="text-uppercase small font-weight-bold mb-3" style="color:var(--rd-accent);">Personnel Information</h6>
                            <div class="row">
                                <div class="col-md-6 mb-2">
                                    <div class="text-muted small">Employee Name</div>
                                    <div class="font-weight-bold text-white h5">{{ $t->employee_name }}</div>
                                </div>
                                <div class="col-md-6 mb-2">
                                    <div class="text-muted small">Department / Designation</div>
                                    <div class="text-white">{{ $t->department }} · <span class="text-info">{{ $t->designation }}</span></div>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- 2. Training Logistics --}}
                    <div class="card shadow-sm border-0 mb-4" style="background:var(--rd-surface); border-radius:12px;">
                        <div class="card-body">
                            <h6 class="text-uppercase small font-weight-bold mb-3" style="color:var(--rd-text3);">Training & Core Logistics</h6>
                            <div class="row">
                                <div class="col-md-12 mb-3">
                                    <div class="text-muted small text-uppercase font-weight-bold">Training Title</div>
                                    <div class="h4 text-white font-weight-bold">{{ $t->title }}</div>
                                </div>
                                <div class="col-md-4">
                                    <div class="text-muted small">Training Type</div>
                                    <div class="text-white bg-dark d-inline-block px-2 py-1 rounded small border border-secondary mt-1">
                                        <i class="fas fa-{{ $t->type == 'inland' ? 'plane' : ($t->type == 'inhouse' ? 'building' : 'globe') }} mr-1 text-info"></i>
                                        {{ ucfirst($t->type) }} Training
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="text-muted small">Institute / Platform</div>
                                    <div class="text-white">{{ $t->institute }}</div>
                                </div>
                                <div class="col-md-4">
                                    <div class="text-muted small">Duration</div>
                                    <div class="text-white">{{ $t->start_date }} — {{ $t->end_date }} ({{ $t->duration }})</div>
                                </div>
                            </div>

                            @if($t->type == 'inland')
                            <hr class="border-secondary">
                            <div class="row mt-2">
                                <div class="col-md-4 mb-2">
                                    <div class="text-muted small">City / Location</div>
                                    <div class="text-white">{{ $t->city }}</div>
                                </div>
                                <div class="col-md-8 mb-2">
                                    <div class="text-muted small">Venue</div>
                                    <div class="text-white">{{ $t->venue }}</div>
                                </div>
                                <div class="col-md-4">
                                    <div class="text-muted small">Travel Mode</div>
                                    <div class="text-white">{{ $t->travel_mode }}</div>
                                </div>
                                <div class="col-md-4">
                                    <div class="text-muted small">Distance (One Way)</div>
                                    <div class="text-white">{{ $t->distance }} KM</div>
                                </div>
                            </div>
                            @elseif($t->type == 'inhouse')
                            <hr class="border-secondary">
                            <div class="row mt-2">
                                <div class="col-md-4 mb-2">
                                    <div class="text-muted small">Trainer</div>
                                    <div class="text-white">{{ $t->trainer }}</div>
                                </div>
                                <div class="col-md-4 mb-2">
                                    <div class="text-muted small">Location / Hall</div>
                                    <div class="text-white">{{ $t->hall }}</div>
                                </div>
                                <div class="col-md-4">
                                    <div class="text-muted small">Expected Participants</div>
                                    <div class="text-white">{{ $t->num_participants }}</div>
                                </div>
                            </div>
                            @else
                            <hr class="border-secondary">
                            <div class="row mt-2">
                                <div class="col-md-4 mb-2">
                                    <div class="text-muted small">Platform</div>
                                    <div class="text-white">{{ $t->platform }}</div>
                                </div>
                                <div class="col-md-8 mb-2">
                                    <div class="text-muted small">Course Link</div>
                                    <a href="{{ $t->course_link }}" target="_blank" class="text-info small text-truncate d-block">{{ $t->course_link }}</a>
                                </div>
                                <div class="col-md-4">
                                    <div class="text-muted small">Payment Status</div>
                                    <div class="text-warning">{{ $t->payment_status }}</div>
                                </div>
                            </div>
                            @endif
                        </div>
                    </div>

                    {{-- TA/DA Financial Summary --}}
                    @if($t->type == 'inland')
                    <div class="card shadow-sm border-0 mb-4" style="background:var(--rd-surface); border-radius:12px; overflow:hidden;">
                        <div class="card-header bg-dark border-0">
                            <h6 class="mb-0 font-weight-bold text-uppercase small text-info"><i class="fas fa-coins mr-2"></i>Financial Claim (TA/DA) Summary</h6>
                        </div>
                        <div class="card-body p-0">
                            <table class="table table-borderless m-0" style="color:var(--rd-text2);">
                                <tr style="border-bottom: 1px solid rgba(255,255,255,0.05);">
                                    <td class="pl-4 py-3">Daily Allowance ({{ $t->num_days }} Days @ PKR {{ number_format($t->da_rate) }})</td>
                                    <td class="text-right pr-4 text-white font-weight-bold">PKR {{ number_format($t->total_da) }}</td>
                                </tr>
                                <tr style="border-bottom: 1px solid rgba(255,255,255,0.05);">
                                    <td class="pl-4 py-3">Travel Cost ({{ $t->travel_mode }})</td>
                                    <td class="text-right pr-4 text-white font-weight-bold">PKR {{ number_format($t->travel_cost) }}</td>
                                </tr>
                                <tr style="border-bottom: 1px solid rgba(255,255,255,0.05);">
                                    <td class="pl-4 py-3">Hotel / Accommodation Cost</td>
                                    <td class="text-right pr-4 text-white font-weight-bold">PKR {{ number_format($t->hotel_cost) }}</td>
                                </tr>
                                <tr class="bg-dark" style="border-top: 1px solid var(--rd-accent) !important;">
                                    <td class="pl-4 py-4 h5 font-weight-bold text-white mb-0">Total Estimated Claim</td>
                                    <td class="text-right pr-4 py-4 h4 font-weight-bold" style="color:var(--rd-accent);">PKR {{ number_format($t->total_claim) }}</td>
                                </tr>
                            </table>
                        </div>
                    </div>
                    @endif
                </div>

                {{-- Right Column: Side Info --}}
                <div class="col-md-4">
                    {{-- Purpose & Remarks --}}
                    <div class="card shadow-sm border-0 mb-4" style="background:var(--rd-surface); border-radius:12px;">
                        <div class="card-body">
                            <h6 class="text-uppercase small font-weight-bold mb-3" style="color:var(--rd-text3);">Case Summary</h6>
                            <div class="mb-3">
                                <div class="text-muted small">Purpose / Objective</div>
                                <div class="text-white">{{ $t->purpose }}</div>
                            </div>
                            <div class="mb-0">
                                <div class="text-muted small">Remarks</div>
                                <div class="text-white p-2 rounded mt-1" style="background:rgba(255,255,255,0.03); border-left:3px solid var(--rd-text3);">
                                    {{ $t->remarks }}
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Evidence / Attachment --}}
                    <div class="card shadow-sm border-0 mb-4" style="background:var(--rd-surface); border-radius:12px;">
                        <div class="card-body">
                            <h6 class="text-uppercase small font-weight-bold mb-3" style="color:var(--rd-text3);">Attachments (1)</h6>
                            <div class="d-flex align-items-center p-3 rounded" style="background:var(--rd-background); border:1px dashed var(--rd-border);">
                                <i class="fas fa-file-pdf fa-2x text-danger mr-3"></i>
                                <div class="flex-grow-1 overflow-hidden">
                                    <div class="text-white text-truncate font-weight-bold small">training_invite_v2.pdf</div>
                                    <div class="text-muted extra-small">2.4 MB · Application Form</div>
                                </div>
                                <a href="#" class="btn btn-xs btn-outline-info ml-2"><i class="fas fa-download"></i></a>
                            </div>
                        </div>
                    </div>

                    {{-- Admin Actions --}}
                    <div class="card shadow-sm border-0" style="background:var(--rd-surface); border-radius:12px;">
                        <div class="card-body">
                            <h6 class="text-uppercase small font-weight-bold mb-3" style="color:var(--rd-accent);">Approval Actions</h6>
                            <button class="btn btn-success btn-block font-weight-bold py-2 mb-2 shadow-sm"><i class="fas fa-check-circle mr-2"></i> Approve Case</button>
                            <button class="btn btn-outline-danger btn-block font-weight-bold py-2 mb-3"><i class="fas fa-times-circle mr-2"></i> Reject / Return</button>
                            <textarea class="form-control bg-dark border-secondary text-white small" rows="2" placeholder="Approval remarks..."></textarea>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
</div>
@endsection

@section('scripts')
<style>
.extra-small { font-size: 0.65rem; }
.bg-soft-info { background: rgba(23, 162, 184, 0.1) !important; color: #117a8b !important; }
</style>
@endsection
