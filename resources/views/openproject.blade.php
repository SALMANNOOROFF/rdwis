@extends('welcome')

@section('content')

<div class="content-wrapper">

<div class="content-header">
        <h1>Open Projects</h1>
    </div>

    <div class="card">
        <div class="card-body">
            <div class="project-header">
                <div class="project-title">
                    <h3>CDS</h3>
                    <div class="project-desc">Demo CDS Project for PNS Vessel</div>
                    <span class="badge-custom">Demo Comm</span>
                </div>
                <a href="{{route('viewproject')}}" class="btn-view">View Project</a>
            </div>
            <div class="timeline-wrapper">
                <div class="timeline-item"><div class="progress-segment bg-primary"></div><div class="meta-text">Approval</div><div class="meta-date">(04 Oct 21)</div></div> 
                <div class="timeline-item"><div class="progress-segment bg-primary"></div><div class="meta-text">Start</div><div class="meta-date">(20 Oct 21)</div></div>
                <div class="timeline-item"><div class="progress-segment bg-success"></div><div class="meta-text">Work in Progress</div></div>
                <div class="timeline-item"><div class="progress-segment bg-secondary"></div><div class="meta-text">Warranty</div></div>
                <div class="timeline-item"><div class="progress-segment bg-secondary"></div><div class="meta-text">EDC</div><div class="meta-date">(20 Jul 22)</div></div>
            </div>
        </div>
    </div>

    <div class="card">
        <div class="card-body">
            <div class="project-header">
                <div class="project-title">
                    <h3>DADSS</h3>
                    <div class="project-desc">Development of Data System for Demo PMSA</div>
                    <span class="badge-custom">Demo Comm</span>
                </div>
                <a href="{{route('viewproject')}}" class="btn-view">View Project</a>
            </div>
            <div class="timeline-wrapper">
                <div class="timeline-item"><div class="progress-segment bg-primary"></div><div class="meta-text">Approval</div><div class="meta-date">(22 Dec 22)</div></div>
                <div class="timeline-item"><div class="progress-segment bg-warning"></div><div class="meta-text">Funds awaited</div></div>
                <div class="timeline-item"><div class="progress-segment bg-secondary"></div><div class="meta-text">Start</div><div class="meta-date">(02 Jan 23)</div></div>
                <div class="timeline-item"><div class="progress-segment bg-secondary"></div><div class="meta-text">Warranty</div></div>
                <div class="timeline-item"><div class="progress-segment bg-secondary"></div><div class="meta-text">EDC</div><div class="meta-date">(19 May 23)</div></div>
            </div>
        </div>
    </div>

    <div class="card">
        <div class="card-body">
            <div class="project-header">
                <div class="project-title">
                    <h3>NOC-V2</h3>
                    <div class="project-desc">Network Operations Center Upgrade Phase 2</div>
                    <span class="badge-custom" style="background-color: #28a745;">Deployed</span>
                </div>
                <a href="{{route('viewproject')}}" class="btn-view">View Project</a>
            </div>
            <div class="timeline-wrapper">
                <div class="timeline-item"><div class="progress-segment bg-success"></div><div class="meta-text">Approval</div><div class="meta-date">(10 Jan 23)</div></div>
                <div class="timeline-item"><div class="progress-segment bg-success"></div><div class="meta-text">Development</div><div class="meta-date">(15 Feb 23)</div></div>
                <div class="timeline-item"><div class="progress-segment bg-success"></div><div class="meta-text">Testing</div><div class="meta-date">(01 Mar 23)</div></div>
                <div class="timeline-item"><div class="progress-segment bg-success"></div><div class="meta-text">Warranty</div><div class="meta-date">Active</div></div>
                <div class="timeline-item"><div class="progress-segment bg-success"></div><div class="meta-text">Handover</div><div class="meta-date">(10 Mar 23)</div></div>
            </div>
        </div>
    </div>

    <div class="card">
        <div class="card-body">
            <div class="project-header">
                <div class="project-title">
                    <h3>ERP-FMS</h3>
                    <div class="project-desc">Enterprise Resource Planning - Fleet Module</div>
                    <span class="badge-custom" style="background-color: #dc3545;">Halted</span>
                </div>
                <a href="{{route('viewproject')}}" class="btn-view">View Project</a>
            </div>
            <div class="timeline-wrapper">
                <div class="timeline-item"><div class="progress-segment bg-primary"></div><div class="meta-text">Initiation</div><div class="meta-date">(01 Nov 22)</div></div>
                <div class="timeline-item"><div class="progress-segment bg-primary"></div><div class="meta-text">Design</div><div class="meta-date">(15 Nov 22)</div></div>
                <div class="timeline-item"><div class="progress-segment bg-danger"></div><div class="meta-text">Vendor Issue</div><div class="meta-date">Stopped</div></div>
                <div class="timeline-item"><div class="progress-segment bg-secondary"></div><div class="meta-text">Integration</div></div>
                <div class="timeline-item"><div class="progress-segment bg-secondary"></div><div class="meta-text">Live</div><div class="meta-date">(TBD)</div></div>
            </div>
        </div>
    </div>

    <div class="card">
        <div class="card-body">
            <div class="project-header">
                <div class="project-title">
                    <h3>CYBER-X</h3>
                    <div class="project-desc">Cyber Security Infrastructure Audit</div>
                    <span class="badge-custom" style="background-color: #17a2b8;">New</span>
                </div>
                <a href="{{route('viewproject')}}" class="btn-view">View Project</a>
            </div>
            <div class="timeline-wrapper">
                <div class="timeline-item"><div class="progress-segment bg-primary"></div><div class="meta-text">Kick-off</div><div class="meta-date">(Today)</div></div>
                <div class="timeline-item"><div class="progress-segment bg-secondary"></div><div class="meta-text">Audit Phase</div></div>
                <div class="timeline-item"><div class="progress-segment bg-secondary"></div><div class="meta-text">Report</div></div>
                <div class="timeline-item"><div class="progress-segment bg-secondary"></div><div class="meta-text">Patching</div></div>
                <div class="timeline-item"><div class="progress-segment bg-secondary"></div><div class="meta-text">Closure</div><div class="meta-date">(15 Apr 23)</div></div>
            </div>
        </div>
    </div>


</div>
@endsection