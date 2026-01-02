@extends('welcome')

@section('content')
<div class="content-wrapper">
    <style>
        /* Modern AdminLTE Enhancements */
        .card {
            border-top: 3px solid #007bff;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
            border-radius: 8px;
            margin: 20px;
        }

        .card-header {
            background-color: #f8f9fa;
            border-bottom: 1px solid #eee;
            padding: 15px 20px;
        }

        .card-title {
            font-weight: 600;
            color: #333;
        }

        /* Hover Fix for Buttons */
        .btn-outline-primary {
            color: #007bff !important;
            border-color: #007bff !important;
        }
        .btn-outline-primary:hover {
            color: #fff !important;
            background-color: #007bff !important;
        }
        .btn-outline-primary a {
            color: inherit;
            text-decoration: none;
        }

        /* Top Info Grid */
        .top-info-grid {
            display: grid;
            grid-template-columns: 1.5fr 2fr 1fr;
            gap: 20px;
            margin-bottom: 25px;
        }

        .date-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 12px;
            background: #fdfdfd;
            padding: 15px;
            border: 1px solid #ebedf2;
            border-radius: 8px;
        }

        /* Advanced Attachment Box */
        .attachment-box {
            border: 1px solid #ced4da;
            border-radius: 6px;
            height: 200px;
            overflow-y: auto;
            background: #fff;
        }
        .attachment-item {
            padding: 10px;
            border-bottom: 1px solid #f1f1f1;
            display: flex;
            align-items: center;
            justify-content: space-between;
        }
        .attachment-info { display: flex; align-items: center; }
        .attachment-info i { margin-right: 8px; color: #28a745; display: none; } /* Hidden by default */
        .file-input-wrapper { position: relative; overflow: hidden; display: inline-block; }
        .file-input-wrapper input[type=file] { font-size: 100px; position: absolute; left: 0; top: 0; opacity: 0; cursor: pointer; }

        /* Table Styling */
        .table thead th {
            background-color: #f4f6f9;
            text-transform: uppercase;
            font-size: 0.75rem;
            letter-spacing: 1px;
        }

        /* Section Headers */
        .section-header {
            border-left: 4px solid #007bff;
            padding-left: 10px;
            margin: 30px 0 15px 0;
            background: #f8f9fa;
            padding: 10px;
            border-radius: 0 4px 4px 0;
        }

        /* Bottom Save Bar */
        .form-actions {
            background: #fff;
            padding: 20px;
            border-top: 1px solid #dee2e6;
            text-align: right;
            margin-top: 30px;
            border-radius: 0 0 8px 8px;
        }

        @media (max-width: 1200px) {
            .top-info-grid { grid-template-columns: 1fr 1fr; }
        }
        @media (max-width: 768px) {
            .top-info-grid { grid-template-columns: 1fr; }
        }
    </style>

    <div class="card">
        <div class="card-header">
            <h3 class="card-title"><i class="fas fa-project-diagram mr-2"></i> Project Management System</h3>
            <div class="card-tools">
                <button class="btn btn-secondary btn-sm mr-1"><i class="fas fa-history"></i> History</button>
                <button class="btn btn-secondary btn-sm mr-1"><i class="fas fa-chart-bar"></i> Gantt Chart</button>
                <button class="btn btn-outline-primary btn-sm"><i class="fas fa-print"></i></button>
            </div>
        </div>

        <div class="card-body">
            <div class="form-group mb-4">
                <label><i class="fas fa-tag"></i> Project Title</label>
                <input type="text" class="form-control form-control-lg" value="Functional replacement of CDS display of PNS ALAMGIR" style="border-left: 4px solid #007bff; font-weight: 600;">
            </div>

            <div class="top-info-grid">
                <div>
                    <div class="form-group">
                        <label>Scope / Description</label>
                        <textarea class="form-control" rows="4">Functional replacement of CRTs of CDS with LED displays</textarea>
                    </div>
                    <div class="row">
                        <div class="col-6">
                            <label>Sponsor</label>
                            <input type="text" class="form-control" value="DSMR WE">
                        </div>
                        <div class="col-6">
                            <label>Receipt Date</label>
                            <input type="date" class="form-control" value="2021-05-31">
                        </div>
                    </div>
                </div>

                <div class="date-grid">
                    <div class="form-group">
                        <label>Assign Date</label>
                        <input type="date" class="form-control" value="2021-10-04">
                    </div>
                    <div class="form-group">
                        <label>Approval Date</label>
                        <input type="date" class="form-control" value="2021-10-04">
                    </div>
                    <div class="form-group">
                        <label>Proposal Date</label>
                        <input type="date" class="form-control" value="2021-08-09">
                    </div>
                    <div class="form-group">
                        <label>Start Date</label>
                        <input type="date" class="form-control" value="2021-10-20">
                    </div>
                    <div class="form-group">
                        <label>Proposed Cost (PKR)</label>
                        <input type="text" class="form-control" value="10,000,000">
                    </div>
                    <div class="form-group">
                        <label>EDC</label>
                        <input type="date" class="form-control" value="2022-07-20" style="color: #dc3545; font-weight: bold;">
                    </div>
                </div>

                <div>
                    <label>Documents & Attachments</label>
                    <div class="attachment-box">
                        @php
                            $docs = ['PPF', 'Project Approval', 'Project Proposal', 'Work Order', 'Tech Specs'];
                        @endphp
                        @foreach($docs as $doc)
                        <div class="attachment-item">
                            <div class="attachment-info">
                                <i class="fas fa-check-circle mr-1" id="icon-{{$loop->index}}"></i>
                                <span class="text-muted">{{ $doc }}</span>
                            </div>
                            <div class="file-input-wrapper">
                                <button class="btn btn-xs btn-default border"><i class="fas fa-upload"></i></button>
                                <input type="file" onchange="markUploaded(this, 'icon-{{$loop->index}}')">
                            </div>
                        </div>
                        @endforeach
                    </div>
                </div>
            </div>

            <div class="section-header d-flex justify-content-between align-items-center">
                <h4 class="m-0"><i class="fas fa-tasks mr-2"></i> Activities & Milestones</h4>
                <a href="{{route('addmilestonepr')}}" class="btn btn-outline-primary btn-sm">
                    <i class="fas fa-plus"></i> Add Milestone
                </a>
            </div>

            <div class="table-responsive">
                <table class="table table-hover border">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Type</th>
                            <th>Description</th>
                            <th>Target Date</th>
                            <th>Achieved Date</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td>1</td>
                            <td><span class="text-primary">Milestone</span></td>
                            <td>Submission of Global Plan</td>
                            <td>20 Nov 21</td>
                            <td>18 Oct 21</td>
                            <td><span class="badge bg-success">Completed</span></td>
                        </tr>
                        <tr>
                            <td>2</td>
                            <td><span class="text-primary">Milestone</span></td>
                            <td>Submission of CDR</td>
                            <td>20 Jan 22</td>
                            <td>15 May 22</td>
                            <td><span class="badge bg-success">Completed</span></td>
                        </tr>
                        <tr>
                            <td>3</td>
                            <td><span class="text-primary">Milestone</span></td>
                            <td>Lab trials of Decoder and LEDs</td>
                            <td>21 Mar 22</td>
                            <td>01 Dec 22</td>
                            <td><span class="badge bg-success">Completed</span></td>
                        </tr>
                        <tr>
                            <td>4</td>
                            <td><span class="text-primary">Milestone</span></td>
                            <td>Installation and SATs</td>
                            <td>20 Jun 22</td>
                            <td>-</td>
                            <td><span class="badge bg-warning text-dark">In Progress</span></td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <div class="row mt-4">
                <div class="col-md-6">
                    <div class="row">
                        <div class="col-md-6 form-group">
                            <label>Current Status</label>
                            <select class="form-control border-primary">
                                <option>Work in progress</option>
                                <option>Funds Awaited</option>
                                <option>Completed</option>
                                <option>Halted</option>
                            </select>
                        </div>
                        <div class="col-md-6 form-group">
                            <label>Final Completion Date</label>
                            <input type="date" class="form-control">
                        </div>
                    </div>
                    <div class="form-group">
                        <label>Progress Remarks</label>
                        <textarea class="form-control" rows="5" style="background-color: #fcfcfc; border-left: 3px solid #28a745;">1. Milestone 1 has been completed and accepted.
2. Radars data tapping activity completed.
3. Milestone 2 submitted on 15 May.</textarea>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group">
                        <label>Internal Notes (Private)</label>
                        <textarea class="form-control" rows="8" placeholder="Enter notes for internal team reference..."></textarea>
                    </div>
                </div>
            </div>

            <div class="form-actions">
                <button type="button" class="btn btn-default mr-2">Cancel</button>
                <button type="submit" class="btn btn-primary btn-lg shadow-sm">
                    <i class="fas fa-save mr-1"></i> Save Changes
                </button>
            </div>

        </div> 
    </div>
</div>

<script>
    // Function to show checkmark when file is selected
    function markUploaded(input, iconId) {
        if (input.files && input.files[0]) {
            document.getElementById(iconId).style.display = "inline-block";
            // Change button color to show success
            input.previousElementSibling.classList.remove('btn-default');
            input.previousElementSibling.classList.add('btn-success');
            input.previousElementSibling.innerHTML = '<i class="fas fa-check"></i>';
        }
    }
</script>
@endsection