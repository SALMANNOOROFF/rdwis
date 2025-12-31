@extends('welcome')

@section('content')
<div class="content-wrapper">
    <style>
        /* Main Theme Colors */
        :root {
            --admin-blue: #007bff;
            --admin-dark: #343a40;
            --light-bg: #f4f6f9;
        }

        .mpr-detail-card {
            background: #fff;
            margin: 15px;
            border-top: 4px solid var(--admin-blue);
            box-shadow: 0 0 1px rgba(0,0,0,.125), 0 1px 3px rgba(0,0,0,.2);
        }

        /* Top Header Section */
        .mpr-main-header {
            background: #f8f9fa;
            padding: 15px;
            border-bottom: 2px solid #dee2e6;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .mpr-title-text { color: var(--admin-dark); font-weight: 700; margin: 0; }

        /* Summary Boxes Layout */
        .summary-grid {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 10px;
            padding: 15px;
            background: #fff;
        }

        .summary-box {
            border: 1px solid #ddd;
            padding: 10px;
            border-radius: 4px;
            font-size: 0.85rem;
            min-height: 100px;
        }

        .summary-box strong { color: var(--admin-blue); display: block; margin-bottom: 5px; border-bottom: 1px solid #eee; }
        .data-row { display: flex; justify-content: space-between; margin-bottom: 3px; }
        .data-label { color: #666; font-weight: 500; }

        /* Milestones Table Section */
        .milestone-section { padding: 0 15px; }
        .section-label { 
            background: var(--admin-dark); 
            color: #fff; 
            padding: 5px 15px; 
            font-size: 0.9rem; 
            font-weight: 600;
            display: inline-block;
            border-radius: 4px 4px 0 0;
        }

        .milestone-table { width: 100%; border-collapse: collapse; border: 1px solid #ddd; font-size: 0.85rem; }
        .milestone-table th { background: #e9ecef; padding: 8px; border: 1px solid #ddd; text-align: left; }
        .milestone-table td { padding: 8px; border: 1px solid #ddd; }
        .milestone-table tr:nth-child(even) { background: #f9f9f9; }

        /* Lower Grid (Progress & Notes) */
        .lower-grid {
            display: grid;
            grid-template-columns: 1fr 1fr 250px;
            gap: 15px;
            padding: 15px;
        }

        .content-box {
            border: 1px solid #ddd;
            border-radius: 4px;
            height: 300px;
            display: flex;
            flex-direction: column;
        }

        .box-header {
            background: #f4f6f9;
            padding: 8px 12px;
            border-bottom: 1px solid #ddd;
            font-weight: 700;
            font-size: 0.85rem;
            display: flex;
            justify-content: space-between;
        }

        .box-body { padding: 10px; overflow-y: auto; font-size: 0.88rem; line-height: 1.5; color: #444; flex-grow: 1; }

        /* Buttons Styling */
        .btn-blue-sm { background: var(--admin-blue); color: #fff; border: none; padding: 4px 12px; border-radius: 3px; font-size: 0.8rem; }
        .btn-blue-sm:hover { background: #0056b3; color: #fff; }

        .btn-group-top .btn { margin-left: 5px; font-weight: 600; font-size: 0.8rem; }
    </style>

    <div class="mpr-detail-card">
        <div class="mpr-main-header">
            <div>
                <h4 class="mpr-title-text">NIU-HS - Indigenous development of NIU for PNS/M HASHMAT</h4>
            </div>
            <div class="btn-group-top">
                <a href="#" class="btn btn-outline-secondary btn-sm shadow-sm">History</a>
                <a href="#" class="btn btn-outline-secondary btn-sm shadow-sm">Print</a>
                <a href="#" class="btn btn-primary btn-sm shadow-sm">Gantt Chart</a>
            </div>
        </div>

        <div class="summary-grid">
            <div class="summary-box">
                <strong>Scope</strong>
                <p>Indigenous development of NIU for PNS/M HASHMAT</p>
            </div>
            <div class="summary-box">
                <strong>Details</strong>
                <div class="data-row"><span class="data-label">Sponsor:</span> <span>DSMM</span></div>
                <div class="data-row"><span class="data-label">Status:</span> <span class="badge badge-warning">Work in progress</span></div>
            </div>
            <div class="summary-box">
                <strong>Timeline</strong>
                <div class="data-row"><span class="data-label">Receipt Date:</span> <span>19 Jan 22</span></div>
                <div class="data-row"><span class="data-label">Approval Date:</span> <span>19 Jan 22</span></div>
                <div class="data-row"><span class="data-label">EDC:</span> <span>19 Jul 22</span></div>
            </div>
            <div class="summary-box">
                <strong>Cost (PKR)</strong>
                <div class="data-row"><span class="data-label">Proposed:</span> <span>16,000,000</span></div>
                <div class="data-row"><span class="data-label">Approved:</span> <span>16,000,000</span></div>
                <div class="data-row"><span class="data-label">Funds:</span> <span>16,000,000</span></div>
            </div>
        </div>

        <div class="milestone-section">
            <div class="section-label">Milestones</div>
            <table class="milestone-table">
                <thead>
                    <tr>
                        <th>S</th>
                        <th>Type</th>
                        <th>Description</th>
                        <th>Target Date</th>
                        <th>%</th>
                        <th>Achieved Date</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td>1</td>
                        <td>Milestone</td>
                        <td>Submission of GPP and list of deliverables</td>
                        <td>04 Feb 22</td>
                        <td>100</td>
                        <td>25 Jan 22</td>
                        <td><span class="text-success font-weight-bold">Completed</span></td>
                    </tr>
                    <tr>
                        <td>2</td>
                        <td>Milestone</td>
                        <td>Submission of CDR</td>
                        <td>21 Feb 22</td>
                        <td>100</td>
                        <td>25 Jan 22</td>
                        <td><span class="text-success font-weight-bold">Completed</span></td>
                    </tr>
                </tbody>
            </table>
        </div>

        <div class="lower-grid">
            <div class="content-box">
                <div class="box-header">
                    <span>Last Finalized Progress</span>
                    <span class="text-muted" style="font-weight: 400;">28-Apr-23 08:38</span>
                </div>
                <div class="box-body">
                    1. Milestones 1 and 2 have been completed and accepted.<br><br>
                    2. Milestone 3 (installation of non-rugged prototype) is also complete, and trials have been completed.<br><br>
                    3. Final installations of rugged system has been completed on 28 Nov 22.
                </div>
            </div>

            <div class="content-box">
                <div class="box-header">
                    <span>Current Progress</span>
                    <button class="btn-blue-sm">Create</button>
                </div>
                <div class="box-body text-muted italic">
                    Click "Create" to add current month progress updates...
                </div>
            </div>

            <div class="content-box">
                <div class="box-header">
                    <span>Sticky Notes</span>
                    <i class="fas fa-sticky-note text-warning"></i>
                </div>
                <div class="box-body" style="background: #fff9c4;">
                    <textarea class="form-control" style="background: transparent; border: none; height: 100%; resize: none;" placeholder="Type notes here..."></textarea>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection