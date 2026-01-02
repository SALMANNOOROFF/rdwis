@extends('welcome')

@section('content')
<div class="content-wrapper">

    <!-- HEADER -->
    <div class="content-header">
        <div class="container-fluid">
            <div class="row mb-4 align-items-center">

                <!-- Page Title -->
                <div class="col-md-4 col-sm-12">
                    <h1 id="page-heading" class="m-0 font-weight-bold">
                        All Purchase Cases
                    </h1>
                </div>

                <!-- Create Button -->
                <div class="col-md-4 col-sm-12 text-center my-2">
                    <a href="{{ route('createnewcase') }}"
                       class="btn btn-success shadow-sm px-4"
                       style="border-radius:20px">
                        <i class="fas fa-plus-circle mr-1"></i>
                        Create New Case
                    </a>
                </div>

                <!-- Filters -->
                <div class="col-md-4 col-sm-12 text-right">
                    <div class="btn-group shadow-sm">
                        <button class="btn btn-outline-primary active"
                                onclick="filterCases('all','All Purchase Cases',this)">
                            All
                        </button>
                        <button class="btn btn-outline-primary"
                                onclick="filterCases('open','Open Cases',this)">
                            Open
                        </button>
                        <button class="btn btn-outline-primary"
                                onclick="filterCases('closed','Closed Cases',this)">
                            Closed
                        </button>
                    </div>
                </div>

            </div>
        </div>
    </div>

    <!-- CONTENT -->
    <div class="content">
        <div class="container-fluid">

            <!-- ================= OPEN / DRAFT CASE ================= -->
            <div class="card project-card shadow-sm mb-4" data-status="open">
                <div class="card-body">

                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h3>PC-2004</h3>
                            <div class="text-muted">
                                Procurement of pressure gauges and sensors
                            </div>
                            <span class="badge badge-warning px-3 py-2">
                                Draft
                            </span>
                        </div>

                        <a href="{{ route('purchasecasedetails') }}"
                           class="btn btn-primary btn-sm px-4">
                            View Case
                        </a>
                    </div>

                    <!-- TIMELINE -->
                    <div class="timeline-wrapper mt-4">
                        <div class="timeline-item">
                            <div class="progress-segment bg-primary"></div>
                            <div class="meta-text">Initiated</div>
                        </div>
                        <div class="timeline-item">
                            <div class="progress-segment bg-warning"></div>
                            <div class="meta-text">D Finance</div>
                        </div>
                        <div class="timeline-item">
                            <div class="progress-segment bg-secondary"></div>
                            <div class="meta-text">Approved</div>
                        </div>
                        <div class="timeline-item">
                            <div class="progress-segment bg-secondary"></div>
                            <div class="meta-text">Purchase Instruction</div>
                        </div>
                        <div class="timeline-item">
                            <div class="progress-segment bg-secondary"></div>
                            <div class="meta-text">Purchase Order</div>
                        </div>
                        <div class="timeline-item">
                            <div class="progress-segment bg-secondary"></div>
                            <div class="meta-text">Item Received</div>
                        </div>
                        <div class="timeline-item">
                            <div class="progress-segment bg-secondary"></div>
                            <div class="meta-text">Payment</div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- ================= COMPLETED CASE ================= -->
            <div class="card project-card shadow-sm mb-4" data-status="closed">
                <div class="card-body">

                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h3>PC-1988</h3>
                            <div class="text-muted">
                                Supply of IT Equipment (Laptops / Servers)
                            </div>
                            <span class="badge badge-success px-3 py-2">
                                Completed
                            </span>
                        </div>

                        <a href="#" class="btn btn-primary btn-sm px-4">
                            View Case
                        </a>
                    </div>

                    <!-- TIMELINE (ALL GREEN) -->
                    <div class="timeline-wrapper mt-4">
                        @foreach([
                            'Initiated',
                            'D Finance',
                            'Approved',
                            'Purchase Instruction',
                            'Purchase Order',
                            'Item Received',
                            'Payment'
                        ] as $step)
                        <div class="timeline-item">
                            <div class="progress-segment bg-success"></div>
                            <div class="meta-text">{{ $step }}</div>
                        </div>
                        @endforeach
                    </div>

                </div>
            </div>

            <!-- ================= IN PROGRESS CASE ================= -->
            <div class="card project-card shadow-sm mb-4" data-status="open">
                <div class="card-body">

                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h3>TA-5501</h3>
                            <div class="text-muted">
                                TA/DA Claim – Officer Visit to Karachi
                            </div>
                            <span class="badge badge-info px-3 py-2">
                                In Progress
                            </span>
                        </div>

                        <a href="#" class="btn btn-primary btn-sm px-4">
                            View Case
                        </a>
                    </div>

                    <div class="timeline-wrapper mt-4">
                        <div class="timeline-item"><div class="progress-segment bg-primary"></div><div class="meta-text">Initiated</div></div>
                        <div class="timeline-item"><div class="progress-segment bg-primary"></div><div class="meta-text">D Finance</div></div>
                        <div class="timeline-item"><div class="progress-segment bg-warning"></div><div class="meta-text">Approved</div></div>
                        <div class="timeline-item"><div class="progress-segment bg-secondary"></div><div class="meta-text">Purchase Instruction</div></div>
                        <div class="timeline-item"><div class="progress-segment bg-secondary"></div><div class="meta-text">Purchase Order</div></div>
                        <div class="timeline-item"><div class="progress-segment bg-secondary"></div><div class="meta-text">Item Received</div></div>
                        <div class="timeline-item"><div class="progress-segment bg-secondary"></div><div class="meta-text">Payment</div></div>
                    </div>

                </div>
            </div>

        </div>
    </div>
</div>

<!-- JS -->
<script>
function filterCases(status, heading, btn){
    document.getElementById('page-heading').innerText = heading;

    document.querySelectorAll('.btn-group .btn')
        .forEach(b => b.classList.remove('active'));

    btn.classList.add('active');

    document.querySelectorAll('.project-card')
        .forEach(card => {
            card.style.display =
                (status === 'all' || card.dataset.status === status)
                ? 'block' : 'none';
        });
}
</script>

<!-- CSS -->
<style>
.project-card{
    border:none;
    border-radius:10px;
    transition:.2s;
}
.project-card:hover{
    transform:translateY(-4px);
    box-shadow:0 6px 20px rgba(0,0,0,.1);
}
.timeline-wrapper{
    display:flex;
    justify-content:space-between;
}
.timeline-item{
    flex:1;
    text-align:center;
    padding:0 4px;
}
.progress-segment{
    height:6px;
    border-radius:4px;
    margin-bottom:6px;
}
.meta-text{
    font-size:12px;
    font-weight:600;
    color:#555;
}
.btn-group .btn.active{
    background:#007bff;
    color:#fff;
}
</style>
@endsection
