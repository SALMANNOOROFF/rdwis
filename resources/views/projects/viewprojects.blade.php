@extends('welcome')

@section('content')
<div class="content-wrapper">
    <div class="content-header">
        <div class="container-fluid">
            <div class="row mb-4 align-items-center">
                
                <div class="col-md-4 col-sm-12">
                    <h1 id="page-heading" class="m-0" style="font-weight: 700;">All Projects</h1>
                </div>

                <div class="col-md-4 col-sm-12 text-center my-2">
                    <a href="{{route('addnewproject')}}" class="btn btn-success shadow-sm" style="border-radius: 20px; padding: 8px 25px;">
                        <i class="fas fa-plus-circle mr-1"></i> Create New Project
                    </a>
                </div>

                <div class="col-md-4 col-sm-12 text-right">
                    <div class="btn-group shadow-sm">
                        <button type="button" class="btn btn-outline-primary active" onclick="filterProjects('all', 'All Projects', this)">All</button>
                        <button type="button" class="btn btn-outline-primary" onclick="filterProjects('open', 'Open Projects', this)">Open</button>
                        <button type="button" class="btn btn-outline-primary" onclick="filterProjects('closed', 'Closed Projects', this)">Closed</button>
                    </div>
                </div>

            </div>
        </div>
    </div>

    <div class="content">
        <div class="container-fluid">

            <div class="card project-card shadow-sm mb-4" data-status="open">
                <div class="card-body">
                    <div class="project-header d-flex justify-content-between align-items-center">
                        <div class="project-title">
                            <h3>CDS</h3>
                            <div class="project-desc text-muted">Demo CDS Project for PNS Vessel</div>
                            <span class="badge badge-primary px-3 py-2">Demo Comm</span>
                        </div>
                        <a href="{{route('openprojectdetails')}}" class="btn btn-primary btn-sm px-4">View Project</a>
                    </div>
                    <div class="timeline-wrapper mt-3">
                        <div class="timeline-item"><div class="progress-segment bg-primary"></div><div class="meta-text">Approval</div><div class="meta-date">(04 Oct 21)</div></div> 
                        <div class="timeline-item"><div class="progress-segment bg-primary"></div><div class="meta-text">Start</div><div class="meta-date">(20 Oct 21)</div></div>
                        <div class="timeline-item"><div class="progress-segment bg-success"></div><div class="meta-text">Work in Progress</div></div>
                        <div class="timeline-item"><div class="progress-segment bg-secondary"></div><div class="meta-text">Warranty</div></div>
                        <div class="timeline-item"><div class="progress-segment bg-secondary"></div><div class="meta-text">EDC</div><div class="meta-date">(20 Jul 22)</div></div>
                    </div>
                </div>
            </div>

            <div class="card project-card shadow-sm mb-4" data-status="closed">
                <div class="card-body">
                    <div class="project-header d-flex justify-content-between align-items-center">
                        <div class="project-title">
                            <h3>NOC-V2</h3>
                            <div class="project-desc text-muted">Network Operations Center Upgrade Phase 2</div>
                            <span class="badge badge-success px-3 py-2">Deployed</span>
                        </div>
                        <a href="{{route('openprojectdetails')}}" class="btn btn-primary btn-sm px-4">View Project</a>
                    </div>
                    <div class="timeline-wrapper mt-3">
                        <div class="timeline-item"><div class="progress-segment bg-success"></div><div class="meta-text">Approval</div><div class="meta-date">(10 Jan 23)</div></div>
                        <div class="timeline-item"><div class="progress-segment bg-success"></div><div class="meta-text">Development</div><div class="meta-date">(15 Feb 23)</div></div>
                        <div class="timeline-item"><div class="progress-segment bg-success"></div><div class="meta-text">Testing</div><div class="meta-date">(01 Mar 23)</div></div>
                        <div class="timeline-item"><div class="progress-segment bg-success"></div><div class="meta-text">Warranty</div><div class="meta-date">Active</div></div>
                        <div class="timeline-item"><div class="progress-segment bg-success"></div><div class="meta-text">Handover</div><div class="meta-date">(10 Mar 23)</div></div>
                    </div>
                </div>
            </div>

            <div class="card project-card shadow-sm mb-4" data-status="open">
                <div class="card-body">
                    <div class="project-header d-flex justify-content-between align-items-center">
                        <div class="project-title">
                            <h3>ERP-FMS</h3>
                            <div class="project-desc text-muted">Enterprise Resource Planning - Fleet Module</div>
                            <span class="badge badge-danger px-3 py-2">Halted</span>
                        </div>
                        <a href="{{route('openprojectdetails')}}" class="btn btn-primary btn-sm px-4">View Project</a>
                    </div>
                    <div class="timeline-wrapper mt-3">
                        <div class="timeline-item"><div class="progress-segment bg-primary"></div><div class="meta-text">Initiation</div><div class="meta-date">(01 Nov 22)</div></div>
                        <div class="timeline-item"><div class="progress-segment bg-primary"></div><div class="meta-text">Design</div><div class="meta-date">(15 Nov 22)</div></div>
                        <div class="timeline-item"><div class="progress-segment bg-danger"></div><div class="meta-text">Vendor Issue</div><div class="meta-date">Stopped</div></div>
                        <div class="timeline-item"><div class="progress-segment bg-secondary"></div><div class="meta-text">Integration</div></div>
                        <div class="timeline-item"><div class="progress-segment bg-secondary"></div><div class="meta-text">Live</div><div class="meta-date">(TBD)</div></div>
                    </div>
                </div>
            </div>

        </div>
    </div>
</div>

<script>
function filterProjects(status, headingText, btnElement) {
    // 1. Heading Update
    document.getElementById('page-heading').innerText = headingText;

    // 2. Active Button State (Passing 'this' or btnElement ensures correct event handling)
    let buttons = document.querySelectorAll('.btn-group .btn');
    buttons.forEach(btn => btn.classList.remove('active'));
    
    // Check if event exists (for click) or use btnElement directly
    if(btnElement) {
        btnElement.classList.add('active');
    } else if(window.event) {
        window.event.target.classList.add('active');
    }

    // 3. Filtering Cards
    let cards = document.querySelectorAll('.project-card');
    cards.forEach(card => {
        if (status === 'all') {
            card.style.display = 'block';
        } else {
            card.style.display = (card.getAttribute('data-status') === status) ? 'block' : 'none';
        }
    });
}
</script>

<style>
    /* Spacing and visual polish */
    .project-card { 
        transition: transform 0.2s ease, box-shadow 0.2s ease; 
        border: none;
        border-radius: 10px;
    }
    .project-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 4px 15px rgba(0,0,0,0.1) !important;
    }
    .btn-group .btn.active { 
        background-color: #007bff !important; 
        color: white !important; 
    }
    .badge { font-weight: 500; }
</style>
@endsection