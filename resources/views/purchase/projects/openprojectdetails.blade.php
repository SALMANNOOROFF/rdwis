@extends('welcome')

@section('content')
<div class="content-wrapper pt-3">
    <style>
        /* Modern UI Enhancements */
        .card-primary.card-outline { border-top: 3px solid #007bff; }
        
        /* Header specific styling */
        .header-controls { display: flex; align-items: center; gap: 15px; }
        .header-info-box { display: flex; flex-direction: column; align-items: flex-end; line-height: 1.2; }
        .header-label { font-size: 0.7rem; color: #6c757d; text-transform: uppercase; font-weight: 700; }
        .header-value { font-size: 0.95rem; font-weight: 700; color: #333; }
        .section-header { border-left: 4px solid #007bff; background: #f4f6f9; padding: 8px 15px; margin-bottom: 20px; font-weight: bold; color: #333; display: flex; justify-content: space-between; align-items: center; }

        /* Team Avatar Styling */
        .team-section-container {
            background: #ffffff;
            border: 1px solid #ced4da;
            border-radius: .5rem;
            padding: 10px 15px;
            display: flex;
            align-items: center;
            justify-content: flex-end;
            flex-wrap: wrap;
            gap: 10px;
            min-height: 60px;
        }

        .team-avatar-wrapper {
            position: relative;
            cursor: pointer;
            transition: transform 0.2s;
        }
        .team-avatar-wrapper:hover {
            transform: scale(1.1);
            z-index: 10;
        }
        .team-avatar-wrapper img {
            width: 50px;
            height: 50px;
            border-radius: 50%;
            border: 2px solid #fff;
            box-shadow: 0 3px 6px rgba(0,0,0,0.15);
            object-fit: cover;
        }
        
        /* Count Button */
        .more-staff-btn {
            width: 50px;
            height: 50px;
            border-radius: 50%;
            background-color: #e9ecef;
            color: #495057;
            border: 2px solid #fff;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: bold;
            font-size: 14px;
            box-shadow: 0 3px 6px rgba(0,0,0,0.15);
            cursor: pointer;
            text-decoration: none;
        }
        .more-staff-btn:hover { background-color: #dee2e6; color: #333; }

        /* Document Grid */
        .attachment-grid { display: grid; grid-template-columns: repeat(2, 1fr); gap: 10px; background: #fff; }
        .attachment-item { padding: 8px 12px; border: 1px solid #dee2e6; border-radius: 4px; display: flex; align-items: center; justify-content: space-between; transition: all 0.3s ease; }
        .btn-upload-icon { width: 30px; height: 30px; padding: 0; display: flex; align-items: center; justify-content: center; border-radius: 50%; }
        
        /* Timeline */
        .timeline-steps { display: flex; justify-content: space-around; position: relative; padding: 20px 0; background: #ffffff; border-radius: 8px; border: 1px solid #ebedf2; }
        .timeline-steps::before { content: ''; position: absolute; top: 50%; left: 10%; right: 10%; height: 2px; background: #e9ecef; z-index: 1; }
        .t-step { position: relative; z-index: 2; text-align: center; }
        .t-icon { width: 35px; height: 35px; background: #fff; border: 2px solid #007bff; border-radius: 50%; line-height: 31px; margin: 0 auto 8px; color: #007bff; transition: all 0.3s; }
        .t-step.active .t-icon { background: #007bff; color: #fff; }
        .t-step.edc .t-icon { border-color: #dc3545; color: #dc3545; }
        .t-label { font-size: 11px; font-weight: 700; color: #495057; display: block; }
        .t-date { font-size: 10px; color: #868e96; }
        .milestone-scroll { max-height: 320px; overflow-y: auto; border: 1px solid #dee2e6; }
        .file-input-hidden { position: absolute; left: 0; top: 0; opacity: 0; cursor: pointer; width: 100%; height: 100%; }

        /* Employee Modal Styling */
        .emp-modal-img { width: 120px; height: 120px; border-radius: 50%; object-fit: cover; border: 4px solid #f4f6f9; margin-bottom: 15px; }
        .emp-detail-row { border-bottom: 1px solid #eee; padding: 10px 0; display: flex; justify-content: space-between; }
        .emp-detail-row:last-child { border-bottom: none; }
        .emp-label { font-weight: 600; color: #6c757d; }
        
        
    </style>

    {{-- MOCK DATA --}}
    @php
        $team = [
            ['id'=>1, 'name'=>'Ali Khan', 'role'=>'Project Manager', 'img'=>'https://ui-avatars.com/api/?name=Ali+Khan&background=007bff&color=fff', 'email'=>'ali@rdwis.com', 'phone'=>'0300-1234567'],
            ['id'=>2, 'name'=>'Sara Ahmed', 'role'=>'Senior Architect', 'img'=>'https://ui-avatars.com/api/?name=Sara+Ahmed&background=e83e8c&color=fff', 'email'=>'sara@rdwis.com', 'phone'=>'0300-7654321'],
            ['id'=>3, 'name'=>'Bilal Hameed', 'role'=>'Site Engineer', 'img'=>'https://ui-avatars.com/api/?name=Bilal+Hameed&background=28a745&color=fff', 'email'=>'bilal@rdwis.com', 'phone'=>'0333-1122334'],
            ['id'=>4, 'name'=>'Usman Qureshi', 'role'=>'Surveyor', 'img'=>'https://ui-avatars.com/api/?name=Usman+Q&background=ffc107&color=fff', 'email'=>'usman@rdwis.com', 'phone'=>'0321-9988776'],
            ['id'=>5, 'name'=>'Zainab Bibi', 'role'=>'Technician', 'img'=>'https://ui-avatars.com/api/?name=Zainab&background=17a2b8&color=fff', 'email'=>'zainab@rdwis.com', 'phone'=>'0345-5544332'],
            ['id'=>6, 'name'=>'Ahmed Raza', 'role'=>'Draftsman', 'img'=>'https://ui-avatars.com/api/?name=Ahmed+Raza&background=6c757d&color=fff', 'email'=>'ahmed@rdwis.com', 'phone'=>'0301-2233445'],
            ['id'=>7, 'name'=>'John Doe', 'role'=>'Helper', 'img'=>'https://ui-avatars.com/api/?name=John+Doe&background=343a40&color=fff', 'email'=>'john@rdwis.com', 'phone'=>'0300-0000000'],
            ['id'=>8, 'name'=>'Jane Doe', 'role'=>'Intern', 'img'=>'https://ui-avatars.com/api/?name=Jane+Doe&background=6610f2&color=fff', 'email'=>'jane@rdwis.com', 'phone'=>'0300-1111111'],
        ];
        $displayLimit = 6;
        $remaining = count($team) - $displayLimit;
    @endphp

    <div class="container-fluid">
        <form action="#" method="POST" enctype="multipart/form-data">
        @csrf
        
        <div class="card card-primary card-outline shadow">
            
            {{-- HEADER: ID, Status, Date --}}
            <div class="card-header d-flex align-items-center justify-content-between p-2">
                <h3 class="card-title text-bold m-0 pl-2" style="font-size: 1.2rem;">
                    <i class="fas fa-project-diagram mr-2 text-primary"></i> 
                    Project ID: #{{ $project->prj_id }}
                </h3>

                <div class="header-controls">
                    <div class="header-info-box">
                        <span class="header-label">Current Status</span>
                        <span class="badge {{ ($project->prj_status ?? '') == 'Completed' ? 'badge-success' : 'badge-info' }}" style="font-size: 0.9rem; padding: 5px 10px;">
                            {{ $project->prj_status ?? 'N/A' }}
                        </span>
                    </div>
                    <div class="header-info-box">
                        <span class="header-label">Completion Date</span>
                        <span class="header-value text-danger">
                            <i class="far fa-calendar-alt mr-1"></i> {{ $project->prj_enddt ?? 'Not Set' }}
                        </span>
                    </div>
                    <div class="border-left mx-2" style="height: 30px; border-color: #ccc !important;"></div>
                    <a href="{{route('projecthistory')}}" class="btn btn-secondary btn-sm shadow-sm"><i class="fas fa-history"></i> History</a>
                    <button type="button" class="btn btn-default btn-sm shadow-sm" onclick="window.print()"><i class="fas fa-print"></i></button>
                </div>
            </div>

            <div class="card-body">
                
              
                <div class="row mb-4 align-items-center">
                    <div class="col-md-6">
                        <label class="text-muted mb-1 small font-weight-bold">PROJECT TITLE</label>
                    
                        <input type="text" class="form-control form-control-lg border-primary shadow-sm" 
                               value="{{ $project->prj_title ?? '' }}" 
                            
                               style="font-weight: 700; border-left: 5px solid #007bff;">
                    </div>

                    <div class="col-md-6">
                        <label class="text-muted mb-1 small font-weight-bold d-block text-right">HIRED STAFF / TEAM ({{ count($team) }} Members)</label>
                        <div class="team-section-container">
                            
                            {{-- Loop to show only 6 images --}}
                            @foreach($team as $index => $member)
                                @if($index < $displayLimit)
                                    <div class="team-avatar-wrapper" 
                                         onclick="openEmployeeModal('{{ $member['name'] }}', '{{ $member['role'] }}', '{{ $member['img'] }}', '{{ $member['email'] }}', '{{ $member['phone'] }}')">
                                        <img src="{{ $member['img'] }}" alt="{{ $member['name'] }}" title="{{ $member['name'] }} - {{ $member['role'] }}">
                                    </div>
                                @endif
                            @endforeach

                            {{-- Show +More Button if staff > 6 --}}
                            @if($remaining > 0)
                                <a href="#" class="more-staff-btn" onclick="openAllStaffModal()">
                                    +{{ $remaining }}
                                </a>
                            @endif

                            <button type="button" class="add-team-btn ml-2" title="Add New Member" style="width: 40px; height: 40px; font-size: 16px;">
                                <i class="fas fa-plus"></i>
                            </button>
                        </div>
                    </div>
                </div>

                <div class="row">
              
                    <div class="col-lg-5 col-md-5">
                        <div class="section-header"><span><i class="fas fa-info-circle mr-1"></i> Basic Information</span></div>
                        
                        <div class="form-group">
                            <label><small class="font-weight-bold">Scope of Work</small></label>
                          
                            <textarea class="form-control" rows="2" style="font-size: 0.9rem;">{{ $project->prj_scope ?? '' }}</textarea>
                        </div>

                        <div class="row">
                            <div class="col-4 form-group">
                                <label><small class="font-weight-bold">Sponsor</small></label>
                              
                                <input type="text" class="form-control form-control-sm" value="{{ $project->prj_sponsor ?? '' }}">
                            </div>
                            <div class="col-4 form-group">
                                <label><small class="font-weight-bold">Receipt Date</small></label>
                              
                                <input type="date" class="form-control form-control-sm" value="{{ $project->prj_rcptdt ?? '' }}" >
                            </div>
                            <div class="col-4 form-group">
                                <label><small class="font-weight-bold">Proposed Cost</small></label>
                                <div class="input-group input-group-sm">
                                    <div class="input-group-prepend"><span class="input-group-text">Rs.</span></div>
                                   
                                    <input type="text" class="form-control font-weight-bold" value="{{ number_format($project->prj_propcost ?? 0) }}" >
                                </div>
                            </div>
                        </div>

                        <label class="mt-2"><small class="font-weight-bold text-dark">REQUIRED DOCUMENTS</small></label>
                        <div class="attachment-grid">
                            @php $docs = ['PPF', 'Project Approval', 'Project Proposal', 'Work Order', 'Tech Specs', 'Drawing/Plan']; @endphp
                            @foreach($docs as $index => $doc)
                            <div class="attachment-item shadow-sm">
                                <span style="font-size: 0.75rem; font-weight: 600;"><i class="fas fa-file-alt text-muted mr-1"></i> {{ $doc }}</span>
                                <div style="position: relative;">
                                    <button type="button" class="btn btn-upload-icon btn-outline-primary shadow-sm" id="btn-{{$index}}">
                                        <i class="fas fa-upload" style="font-size: 0.8rem;"></i>
                                    </button>
                                    <input type="file" name="docs[{{ $doc }}]" class="file-input-hidden" onchange="updateUploadUI(this, 'btn-{{$index}}')">
                                </div>
                            </div>
                            @endforeach
                        </div>
                    </div>

                    {{-- Right Column: Timeline & Milestones --}}
                    <div class="col-lg-7 col-md-7">
                        <div class="section-header"><span><i class="fas fa-stream mr-1"></i> Execution Timeline</span></div>
                        
                        <div class="timeline-steps mb-4 shadow-sm">
                            <div class="t-step active">
                                <div class="t-icon"><i class="fas fa-file-invoice"></i></div>
                                <span class="t-label">Proposal</span>
                                <span class="t-date">{{ $project->prj_propdt ?? 'N/A' }}</span>
                            </div>
                            <div class="t-step"><div class="t-icon"><i class="fas fa-user-check"></i></div><span class="t-label">Assigned</span></div>
                            <div class="t-step"><div class="t-icon"><i class="fas fa-check-double"></i></div><span class="t-label">Approved</span></div>
                            <div class="t-step"><div class="t-icon"><i class="fas fa-play"></i></div><span class="t-label">Started</span></div>
                            <div class="t-step edc">
                                <div class="t-icon"><i class="fas fa-flag-checkered"></i></div>
                                <span class="t-label text-danger">EDC Target</span>
                                <span class="t-date text-danger font-weight-bold">{{ $project->prj_estenddt ?? 'N/A' }}</span>
                            </div>
                        </div>

                        <div class="section-header">
                            <span><i class="fas fa-tasks mr-1"></i> Activities & Milestones</span>
                            <a href="{{ route('projects.add-milestone', $project->prj_id) }}" class="btn btn-primary btn-xs">
    <i class="fas fa-plus-circle"></i> New Milestone
</a>
                        </div>

                        <div class="milestone-scroll">
                            <table class="table table-sm table-head-fixed table-hover m-0">
                                <thead class="bg-light">
                                    <tr>
                                        <th>Type</th>
                                        <th>Description</th>
                                        <th>Target</th>
                                        <th>Status</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($project->milestones ?? [] as $milestone)
                                    <tr>
                                        <td><span class="badge badge-info">{{ $milestone->msn_type }}</span></td>
                                        <td>{{ Str::limit($milestone->msn_desc, 50) }}</td>
                                        <td><small>{{ $milestone->msn_targetdt }}</small></td>
                                        <td><span class="badge {{ Str::lower($milestone->msn_status) == 'completed' ? 'badge-success' : 'badge-warning' }}">{{ $milestone->msn_status }}</span></td>
                                    </tr>
                                    @empty
                                    <tr><td colspan="4" class="text-center py-4 text-muted">No activities recorded yet.</td></tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                {{-- REMOVED: Progress Remarks and Internal Notes Section --}}

            </div>

          
        </div>
        </form>
    </div>
</div>

{{-- MODAL 1: Individual Employee Detail --}}
<div class="modal fade" id="employeeDetailModal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-body text-center p-4">
                <img src="" id="empModalImg" class="emp-modal-img shadow-sm">
                <h4 id="empModalName" class="font-weight-bold mb-1"></h4>
                <p id="empModalRole" class="text-primary mb-4"></p>

                <div class="text-left mt-3">
                    <div class="emp-detail-row">
                        <span class="emp-label"><i class="fas fa-id-card mr-2"></i>Employee ID</span>
                        <span class="text-dark font-weight-bold">EMP-00X</span>
                    </div>
                    <div class="emp-detail-row">
                        <span class="emp-label"><i class="fas fa-envelope mr-2"></i>Email</span>
                        <span id="empModalEmail" class="text-dark"></span>
                    </div>
                    <div class="emp-detail-row">
                        <span class="emp-label"><i class="fas fa-phone mr-2"></i>Phone</span>
                        <span id="empModalPhone" class="text-dark"></span>
                    </div>
                </div>
                
                <button type="button" class="btn btn-secondary btn-block mt-4" data-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

{{-- MODAL 2: All Hired Staff List --}}
<div class="modal fade" id="allStaffModal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-header bg-light">
                <h5 class="modal-title font-weight-bold"><i class="fas fa-users text-primary mr-2"></i> Hired Personnel List</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body p-0">
                <table class="table table-striped table-hover m-0">
                    <thead class="bg-primary text-white">
                        <tr>
                            <th>Image</th>
                            <th>Name</th>
                            <th>Designation</th>
                            <th>Contact</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($team as $member)
                        <tr>
                            <td><img src="{{ $member['img'] }}" class="rounded-circle" width="35" height="35"></td>
                            <td class="font-weight-bold">{{ $member['name'] }}</td>
                            <td>{{ $member['role'] }}</td>
                            <td>{{ $member['phone'] }}</td>
                            <td><span class="badge badge-success">Active</span></td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<script>
    // Show Single Employee Detail
    function openEmployeeModal(name, role, img, email, phone) {
        document.getElementById('empModalName').innerText = name;
        document.getElementById('empModalRole').innerText = role;
        document.getElementById('empModalImg').src = img;
        document.getElementById('empModalEmail').innerText = email;
        document.getElementById('empModalPhone').innerText = phone;
        $('#employeeDetailModal').modal('show');
    }

    // Show All Staff List
    function openAllStaffModal() {
        $('#allStaffModal').modal('show');
    }

    function updateUploadUI(input, btnId) {
        if (input.files && input.files[0]) {
            const btn = document.getElementById(btnId);
            btn.classList.remove('btn-outline-primary');
            btn.classList.add('btn-success');
            btn.innerHTML = '<i class="fas fa-check" style="font-size: 0.8rem;"></i>';
            btn.closest('.attachment-item').style.borderColor = '#28a745';
            btn.closest('.attachment-item').style.backgroundColor = '#f0fff4';
        }
    }
</script>
@endsection