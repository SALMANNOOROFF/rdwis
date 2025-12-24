@extends('welcome')

@section('content')
<div class="content-wrapper">
<style>
/* Card Structure */
        .card {
            background-color: #fff;
            box-shadow: 0 0 1px rgba(0,0,0,.125), 0 1px 3px rgba(0,0,0,.2);
            border-radius: 0.25rem;
            border-top: 3px solid #007bff; /* AdminLTE Blue Top Border */
            margin-bottom: 20px;
        }

        .card-header {
            background-color: transparent;
            border-bottom: 1px solid rgba(0,0,0,.125);
            padding: 1rem 1.25rem;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .card-title {
            font-size: 1.5rem;
            font-weight: 500;
            margin: 0;
        }

        .card-body {
            padding: 1.25rem;
        }

        /* --- Form Elements --- */
        label {
            font-weight: 700;
            font-size: 0.85rem;
            color: #555;
            margin-bottom: 5px;
            display: block;
        }

        .form-control {
            display: block;
            width: 100%;
            padding: 0.375rem 0.75rem;
            font-size: 0.9rem;
            line-height: 1.5;
            color: #495057;
            background-color: #fff;
            border: 1px solid #ced4da;
            border-radius: 0.25rem;
            margin-bottom: 15px;
            transition: border-color .15s ease-in-out,box-shadow .15s ease-in-out;
        }

        .form-control:focus {
            border-color: #80bdff;
            outline: 0;
        }

        .form-control-plaintext {
            display: block;
            width: 100%;
            padding: 0.375rem 0;
            margin-bottom: 0;
            font-size: 1rem;
            line-height: 1.5;
            color: #212529;
            background-color: transparent;
            border: solid transparent;
            border-width: 1px 0;
        }

        /* Custom Grid for Top Section */
        .top-info-grid {
            display: grid;
            grid-template-columns: 2fr 3fr 1.5fr; /* 3 Columns: Info, Dates, Attachments */
            gap: 20px;
            margin-bottom: 20px;
        }

        /* Date Grid inside middle column */
        .date-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 10px;
            background: #f8f9fa;
            padding: 15px;
            border-radius: 5px;
            border: 1px solid #e9ecef;
        }

        /* Attachments Box */
        .attachment-box {
            border: 1px solid #ced4da;
            border-radius: 0.25rem;
            height: 180px;
            overflow-y: auto;
            background: #fff;
        }
        .attachment-item {
            padding: 8px 10px;
            border-bottom: 1px solid #f0f0f0;
            font-size: 0.85rem;
            display: flex;
            align-items: center;
        }
        .attachment-item input { margin-right: 10px; }

        /* --- Buttons --- */
        .btn {
            display: inline-block;
            font-weight: 400;
            text-align: center;
            vertical-align: middle;
            border: 1px solid transparent;
            padding: .375rem .75rem;
            font-size: 0.9rem;
            border-radius: .25rem;
            cursor: pointer;
            transition: color .15s, background-color .15s;
        }
        .btn-outline-primary { color: #007bff; border-color: #007bff; background: transparent; }
        .btn-outline-primary:hover { color: #fff; background-color: #007bff; }
        
        .btn-secondary { background-color: #6c757d; color: white; }
        .btn-sm { padding: .25rem .5rem; font-size: .875rem; }

        /* --- Table Styling --- */
        .table-responsive {
            width: 100%;
            margin-bottom: 1rem;
            overflow-x: auto;
        }
        
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 1rem;
            background-color: transparent;
        }

        th, td {
            padding: 0.75rem;
            vertical-align: top;
            border-top: 1px solid #dee2e6;
            font-size: 0.9rem;
        }

        thead th {
            vertical-align: bottom;
            border-bottom: 2px solid #dee2e6;
            background-color: #f4f6f9;
            color: #495057;
            text-align: left;
        }

        /* Status Badges in Table */
        .badge {
            padding: 0.4em 0.6em;
            font-size: 75%;
            font-weight: 700;
            border-radius: 0.25rem;
            color: #fff;
        }
        .bg-success { background-color: #28a745; }
        .bg-warning { background-color: #ffc107; color: #1f2d3d; }
        .bg-secondary { background-color: #6c757d; }

        /* --- Footer Section --- */
        .footer-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
            margin-top: 20px;
            padding-top: 20px;
            border-top: 1px solid #dee2e6;
        }

        textarea { resize: vertical; }

        /* Section Headers */
        .section-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 10px;
            border-bottom: 2px solid #007bff;
            padding-bottom: 5px;
        }
        .section-header h4 { margin: 0; color: #007bff; font-size: 1.1rem; }

        @media (max-width: 992px) {
            .top-info-grid { grid-template-columns: 1fr; }
            .footer-grid { grid-template-columns: 1fr; }
        }
    </style>

    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Project CDS</h3>
            <div class="card-tools">
                <button class="btn btn-secondary btn-sm"><i class="fas fa-history"></i> History</button>
                <button class="btn btn-secondary btn-sm"><i class="fas fa-chart-bar"></i> Gantt Chart</button>
                <button class="btn btn-outline-primary btn-sm"><i class="fas fa-file-alt"></i></button>
            </div>
        </div>

        <div class="card-body">
            
            <div class="form-group">
                <label>Project Title</label>
                <input type="text" class="form-control" value="Functional replacement of CDS display of PNS ALAMGIR" style="font-size: 1.1rem; font-weight: 600;">
            </div>

            <div class="top-info-grid">
                <div>
                    <label>Scope / Description</label>
                    <textarea class="form-control" rows="4">Functional replacement of CRTs of CDS with LED displays</textarea>
                    
                    <div style="display: flex; gap: 10px;">
                        <div style="flex: 1;">
                            <label>Sponsor</label>
                            <input type="text" class="form-control" value="DSMR WE">
                        </div>
                        <div style="flex: 1;">
                            <label>Receipt Date</label>
                            <input type="date" class="form-control" value="2021-05-31">
                        </div>
                    </div>
                </div>

                <div class="date-grid">
                    <div>
                        <label>Assign Date</label>
                        <input type="date" class="form-control" value="2021-10-04">
                    </div>
                    <div>
                        <label>Approval Date</label>
                        <input type="date" class="form-control" value="2021-10-04">
                    </div>
                    <div>
                        <label>Proposal Date</label>
                        <input type="date" class="form-control" value="2021-08-09">
                    </div>
                    <div>
                        <label>Start Date</label>
                        <input type="date" class="form-control" value="2021-10-20">
                    </div>
                    <div>
                        <label>Proposed Cost</label>
                        <input type="text" class="form-control" value="10,000,000">
                    </div>
                    <div>
                        <label>EDC</label>
                        <input type="date" class="form-control" value="2022-07-20">
                    </div>
                </div>

                <div>
                    <label>Attachments</label>
                    <div class="attachment-box">
                        <div class="attachment-item"><input type="checkbox" checked> PPF</div>
                        <div class="attachment-item"><input type="checkbox"> Project Approval</div>
                        <div class="attachment-item"><input type="checkbox" checked> Project Proposal</div>
                        <div class="attachment-item"><input type="checkbox" checked> Work Order</div>
                        <div class="attachment-item"><input type="checkbox"> Tech Specs</div>
                        <div class="attachment-item"><input type="checkbox"> Budget Approval</div>
                    </div>
                </div>
            </div>

            <div class="section-header">
                <h4>Activities & Milestones</h4>
                <button class="btn btn-outline-primary btn-sm">Add Milestone</button>
            </div>

            <div class="table-responsive">
                <table>
                    <thead>
                        <tr>
                            <th style="width: 30px;">#</th>
                            <th>Type</th>
                            <th>Description</th>
                            <th>Target Date</th>
                            <th>Achieved Dt</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td>1</td>
                            <td>Milestone</td>
                            <td>Submission of Global Plan</td>
                            <td>20 Nov 21</td>
                            <td>18 Oct 21</td>
                            <td><span class="badge bg-success">Completed</span></td>
                        </tr>
                        <tr>
                            <td>2</td>
                            <td>Milestone</td>
                            <td>Submission of CDR</td>
                            <td>20 Jan 22</td>
                            <td>15 May 22</td>
                            <td><span class="badge bg-success">Completed</span></td>
                        </tr>
                        <tr>
                            <td>3</td>
                            <td>Milestone</td>
                            <td>Lab trials of Decoder and LEDs</td>
                            <td>21 Mar 22</td>
                            <td>01 Dec 22</td>
                            <td><span class="badge bg-success">Completed</span></td>
                        </tr>
                        <tr>
                            <td>4</td>
                            <td>Milestone</td>
                            <td>Installation and SATs</td>
                            <td>20 Jun 22</td>
                            <td>-</td>
                            <td><span class="badge bg-warning">In Progress</span></td>
                        </tr>
                        <tr>
                            <td>5</td>
                            <td>Milestone</td>
                            <td>Warranty</td>
                            <td>21 Jun 23</td>
                            <td>-</td>
                            <td><span class="badge bg-secondary">Not Started</span></td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <div class="footer-grid">
                <div>
                    <div style="display: flex; gap: 15px; margin-bottom: 15px;">
                        <div style="flex: 1;">
                            <label>Current Status</label>
                            <select class="form-control">
                                <option>Work in progress</option>
                                <option>Funds Awaited</option>
                                <option>Completed</option>
                                <option>Halted</option>
                            </select>
                        </div>
                        <div style="flex: 1;">
                            <label>Completion Date</label>
                            <input type="date" class="form-control">
                        </div>
                    </div>

                    <label>Remarks</label>
                    <textarea class="form-control" rows="5" style="background-color: #f8f9fa;">
1. Milestone 1 has been completed and accepted.

2. One of the Radars was made available onboard in April 22 and data tapping activity has been completed.

3. Milestone 2 has been completed and submitted on 15 May.
                    </textarea>
                </div>

                <div>
                    <label>Internal Notes</label>
                    <textarea class="form-control" rows="8" placeholder="Add private notes here..."></textarea>
                </div>
            </div>

        </div> </div>

    </div>
    @endsection