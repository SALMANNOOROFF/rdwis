@extends('welcome')

@section('content')
<div class="content-wrapper">
    <style>
        /* Main Container - AdminLTE Blue Theme */
        .gantt-wrapper {
            background-color: #fff;
            margin: 20px;
            border-top: 4px solid #007bff; /* Standard AdminLTE Blue */
            box-shadow: 0 0 1px rgba(0,0,0,.125), 0 1px 3px rgba(0,0,0,.2);
            border-radius: 0.25rem;
        }

        /* Header - Professional Admin Blue */
        .gantt-header-top {
            background-color: #007bff; 
            background-image: linear-gradient(180deg, #007bff 0%, #0069d9 100%);
            color: white;
            padding: 20px;
            text-align: center;
        }
        .gantt-header-top h2 { margin: 0; font-size: 1.5rem; font-weight: 700; letter-spacing: 0.5px; }
        .gantt-subtitle { font-size: 0.9rem; margin-top: 8px; opacity: 0.9; }

        /* Gantt Grid Logic */
        .gantt-grid {
            display: grid;
            grid-template-columns: 300px 1fr;
            border: 1px solid #dee2e6;
        }

        /* Left Side Labels */
        .gantt-labels {
            background: #f8f9fa;
            border-right: 2px solid #007bff33;
        }
        .gantt-label-header {
            height: 50px;
            background: #e9ecef;
            border-bottom: 2px solid #dee2e6;
        }
        .label-item {
            height: 70px;
            padding: 10px 20px;
            border-bottom: 1px solid #dee2e6;
            display: flex;
            align-items: center;
            font-size: 0.9rem;
            color: #495057;
            font-weight: 600;
        }

        /* Timeline / Months Header */
        .timeline-scroll { overflow-x: auto; position: relative; background: #fff; }
        .timeline-months {
            display: flex;
            background: #343a40; /* AdminLTE Dark Sidebar Color */
            color: #fff;
            height: 50px;
        }
        .month-cell {
            min-width: 140px;
            flex: 1;
            text-align: center;
            line-height: 50px;
            font-size: 0.85rem;
            font-weight: 600;
            border-right: 1px solid #4b545c;
            text-transform: uppercase;
        }

        /* Rows with Blue Tint Hover */
        .timeline-row {
            height: 70px;
            position: relative;
            border-bottom: 1px solid #eee;
            background-image: linear-gradient(to right, #f1f1f1 1px, transparent 1px);
            background-size: 140px 100%;
        }
        .timeline-row:hover { background-color: #f0f7ff; }

        /* Shapes - Blue Theme */
        .milestone-diamond {
            width: 18px;
            height: 18px;
            transform: rotate(45deg);
            position: absolute;
            top: 26px;
            z-index: 5;
            transition: transform 0.2s;
        }
        .milestone-diamond:hover { transform: rotate(45deg) scale(1.2); cursor: pointer; }
        
        .diamond-completed { background-color: #007bff; border: 2px solid #0056b3; }
        .diamond-pending { background-color: #fff; border: 2px solid #007bff; }

        .activity-bar {
            height: 14px;
            position: absolute;
            top: 28px;
            border-radius: 10px;
            box-shadow: 0 1px 3px rgba(0,0,0,0.1);
        }
        .bar-completed { background-color: #007bff; }
        .bar-remaining { border: 2px solid #007bff; background: #fff; }

        /* Footer Legend - Clean Admin Style */
        .gantt-footer-legend {
            background-color: #f4f6f9;
            padding: 20px;
            display: flex;
            justify-content: center;
            gap: 30px;
            border-top: 1px solid #dee2e6;
        }
        .legend-box {
            display: flex;
            align-items: center;
            background: #fff;
            padding: 8px 15px;
            border: 1px solid #dee2e6;
            border-radius: 4px;
            font-size: 0.85rem;
            color: #333;
            box-shadow: 0 1px 2px rgba(0,0,0,0.05);
        }
        .color-sample { width: 30px; height: 12px; margin-right: 10px; border-radius: 2px; }
        .diamond-sample { width: 12px; height: 12px; transform: rotate(45deg); margin-right: 15px; }
    </style>

    <!-- <div class="content-header">
        <div class="container-fluid">
            <button class="btn btn-primary btn-sm" onclick="window.history.back();">
                <i class="fas fa-arrow-left mr-1"></i> Back to Dashboard
            </button>
        </div>
    </div> -->

    <div class="gantt-wrapper">
        <div class="gantt-header-top">
            <h2><i class="fas fa-project-diagram mr-2"></i> Indigenous Development of NIU</h2>
            <div class="gantt-subtitle">
                <strong>PNS/M HASHMAT</strong> | 19 Jan 2022 — 19 Jul 2022
            </div>
        </div>

        <div class="gantt-grid">
            <div class="gantt-labels">
                <div class="gantt-label-header"></div>
                <div class="label-item">GPP & Deliverables Submission</div>
                <div class="label-item">CDR Submission</div>
                <div class="label-item">Prototype Testing (Non-Rugged)</div>
                <div class="label-item">Ruggedized Solution Install</div>
                <div class="label-item">Warranty Maintenance</div>
            </div>

            <div class="timeline-scroll">
                <div class="timeline-months">
                    <div class="month-cell">Jan 22</div>
                    <div class="month-cell">Feb 22</div>
                    <div class="month-cell">Mar 22</div>
                    <div class="month-cell">Apr 22</div>
                    <div class="month-cell)'>May 22</div>
                    <div class="month-cell">Jun 22</div>
                    <div class="month-cell">Jul 22</div>
                    <div class="month-cell">Aug 22</div>
                </div>

                <div class="timeline-row">
                    <div class="milestone-diamond diamond-completed" style="left: 120px;" title="Completed Jan 31"></div>
                </div>

                <div class="timeline-row">
                    <div class="milestone-diamond diamond-completed" style="left: 260px;" title="Completed Feb 25"></div>
                </div>

                <div class="timeline-row">
                    <div class="activity-bar bar-completed" style="left: 120px; width: 400px;"></div>
                    <div class="milestone-diamond diamond-completed" style="left: 520px;"></div>
                </div>

                <div class="timeline-row">
                    <div class="activity-bar bar-remaining" style="left: 520px; width: 300px;"></div>
                    <div class="milestone-diamond diamond-pending" style="left: 820px;"></div>
                </div>

                <div class="timeline-row">
                    <div class="activity-bar bar-remaining" style="left: 820px; width: 120px;"></div>
                </div>
            </div>
        </div>

        <div class="gantt-footer-legend">
            <div class="legend-box">
                <div class="color-sample bar-completed"></div> Completed Activity
            </div>
            <div class="legend-box">
                <div class="diamond-sample diamond-completed"></div> Completed Milestone
            </div>
            <div class="legend-box">
                <div class="color-sample bar-remaining" style="border:1px solid #007bff"></div> Remaining Activity
            </div>
            <div class="legend-box">
                <div class="diamond-sample diamond-pending"></div> Remaining Milestone
            </div>
        </div>
    </div>
</div>
@endsection