@extends('welcome')

@section('content')
<div class="content-wrapper">
    <div class="content-header pb-0">
        <div class="container-fluid">
            <h1 class="m-0 font-weight-bold text-dark" style="font-size: 1.8rem;"><i class="fas fa-inbox text-primary mr-2"></i> DG Purchase Dashboard</h1>
        </div>
    </div>

    <section class="content mt-3">
        <div class="container-fluid">
            {{-- Top Notification/Metric Cards (Smaller) --}}
            <div class="row mb-3">
                @php
                    $pendingPurchases = $purchases->whereIn('pcs_status', ['Pending', 'Under Scrutiny', 'Forwarded', 'Draft', 'Submitted']);
                    $processedPurchases = $purchases->whereIn('pcs_status', ['Approved', 'Returned', 'Rejected']);
                    
                    $pendingCount = $pendingPurchases->count();
                    $processedCount = $processedPurchases->count();
                    $totalValue = $purchases->sum('pcs_price');
                @endphp
                <div class="col-md-4">
                    <div class="card shadow-sm border-0 border-left-warning rounded-lg">
                        <div class="card-body py-2 px-3 d-flex align-items-center justify-content-between">
                            <div>
                                <h6 class="text-uppercase text-muted font-weight-bold mb-1" style="font-size: 0.7rem;">Pending Cases</h6>
                                <h4 class="mb-0 font-weight-bold text-dark">{{ $pendingCount }}</h4>
                            </div>
                            <div class="rounded-circle bg-warning d-flex align-items-center justify-content-center shadow-sm" style="width: 36px; height: 36px;">
                                <i class="fas fa-clock text-white"></i>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card shadow-sm border-0 border-left-success rounded-lg">
                        <div class="card-body py-2 px-3 d-flex align-items-center justify-content-between">
                            <div>
                                <h6 class="text-uppercase text-muted font-weight-bold mb-1" style="font-size: 0.7rem;">Processed</h6>
                                <h4 class="mb-0 font-weight-bold text-dark">{{ $processedCount }}</h4>
                            </div>
                            <div class="rounded-circle bg-success d-flex align-items-center justify-content-center shadow-sm" style="width: 36px; height: 36px;">
                                <i class="fas fa-check-double text-white"></i>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card shadow-sm border-0 border-left-info rounded-lg">
                        <div class="card-body py-2 px-3 d-flex align-items-center justify-content-between">
                            <div>
                                <h6 class="text-uppercase text-muted font-weight-bold mb-1" style="font-size: 0.7rem;">Estimated Value</h6>
                                <h4 class="mb-0 font-weight-bold text-dark">Rs. {{ number_format((float) $totalValue) }}</h4>
                            </div>
                            <div class="rounded-circle bg-info d-flex align-items-center justify-content-center shadow-sm" style="width: 36px; height: 36px;">
                                <i class="fas fa-money-bill-wave text-white"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Search & Global Filters --}}
            <div class="row mb-3">
                <div class="col-md-12">
                    <div class="card shadow-sm border-0 rounded-lg">
                        <div class="card-body py-3 d-flex flex-wrap align-items-center gap-3" style="row-gap: 12px !important;">
                            <div class="input-group" style="max-width: 400px; min-width: 250px; flex-grow: 1;">
                                <div class="input-group-prepend">
                                    <span class="input-group-text bg-white border-right-0 text-muted"><i class="fas fa-search"></i></span>
                                </div>
                                <input type="text" id="dgSearchInput" class="form-control border-left-0 shadow-none" placeholder="Search by case title, ref, or amount..." onkeyup="filterCases()">
                            </div>
                            <div class="ml-sm-auto text-muted font-weight-bold" style="font-size: 0.85rem; width: 100%; max-width: fit-content;">
                                Showing <span id="dgCaseCount">{{ $purchases->count() }}</span> total cases across <span id="dgDivCount">{{ $groupedPurchases->count() }}</span> divisions
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- DIVISION TABS --}}
            <div class="mb-3">
                <ul class="nav nav-pills" id="divisionTabs" role="tablist" style="gap: 10px;">
                    <li class="nav-item">
                        <a class="nav-link active shadow-sm font-weight-bold rounded-pill px-4" 
                           id="div-all-tab" data-toggle="pill" href="#div-all" role="tab">
                            <i class="fas fa-th-large mr-1 text-muted"></i> All Divisions
                        </a>
                    </li>
                    @foreach($groupedPurchases as $unitId => $unitCases)
                        @php 
                            $divName = $unitNameMap[$unitId] ?? 'Division ' . $unitId; 
                            $divPendingCount = $unitCases->whereIn('pcs_status', ['Pending', 'Under Scrutiny', 'Forwarded', 'Draft', 'Submitted'])->count();
                        @endphp
                        <li class="nav-item">
                            <a class="nav-link bg-white shadow-sm text-dark font-weight-bold rounded-pill px-4" 
                               id="div-{{ $unitId }}-tab" data-toggle="pill" href="#div-{{ $unitId }}" role="tab">
                                <i class="fas fa-building mr-1 text-muted"></i> {{ $divName }}
                                @if($divPendingCount > 0)
                                    <span class="badge badge-danger badge-pill ml-2">{{ $divPendingCount }}</span>
                                @endif
                            </a>
                        </li>
                    @endforeach
                </ul>
            </div>

            {{-- DIVISION CONTENT --}}
            <div class="tab-content" id="divisionTabsContent">
                {{-- Global ALL DIVISIONS Tab --}}
                <div class="tab-pane fade show active" id="div-all" role="tabpanel">
                    @include('nrdi.purchase_cases.partials.case_table', ['cases' => $purchases, 'unitId' => 'all'])
                </div>

                @foreach($groupedPurchases as $unitId => $unitCases)
                    <div class="tab-pane fade" id="div-{{ $unitId }}" role="tabpanel">
                        @include('nrdi.purchase_cases.partials.case_table', ['cases' => $unitCases, 'unitId' => $unitId])
                    </div>
                @endforeach
            </div>

        </div>
    </section>
</div>

<style>
    .nav-pills .nav-link.active {
        background-color: var(--rd-primary) !important;
        color: white !important;
    }
    .nav-pills .nav-link.active .div-icon {
        color: white !important;
    }
    .nav-pills .nav-link.active .div-badge {
        background-color: white !important;
        color: #dc3545 !important;
    }
    .nav-tabs .nav-link.active {
        border-bottom: 3px solid var(--rd-primary) !important;
    }
    .border-left-warning { border-left: 4px solid #ffc107 !important; }
    .border-left-success { border-left: 4px solid #28a745 !important; }
    .border-left-info { border-left: 4px solid #17a2b8 !important; }
</style>

<script>
    function filterCases() {
        const input = document.getElementById('dgSearchInput');
        const filter = input.value.toLowerCase();
        const activePane = document.querySelector('.tab-pane.active');
        const rows = activePane.querySelectorAll('.case-row');
        let visibleCount = 0;

        rows.forEach(row => {
            const title = row.querySelector('.case-title').textContent.toLowerCase();
            const ref = row.querySelector('.case-ref').textContent.toLowerCase();
            const amount = row.querySelector('.case-amount').textContent.toLowerCase();
            
            if (title.indexOf(filter) > -1 || ref.indexOf(filter) > -1 || amount.indexOf(filter) > -1) {
                row.style.display = "";
                visibleCount++;
            } else {
                row.style.display = "none";
            }
        });

        document.getElementById('dgCaseCount').textContent = visibleCount;
    }

    // Reset search when switching tabs
    $(document).on('shown.bs.tab', 'a[data-toggle="pill"]', function (e) {
        document.getElementById('dgSearchInput').value = '';
        const rows = document.querySelectorAll('.case-row');
        rows.forEach(r => r.style.display = '');
        
        // Update visual active state for pills
        $('#divisionTabs .nav-link').removeClass('active active shadow-sm').addClass('bg-white text-dark');
        $(e.target).removeClass('bg-white text-dark shadow-sm').addClass('active shadow-sm');

        // Reset counts
        const activePane = document.querySelector('.tab-pane.active');
        const totalRows = activePane.querySelectorAll('.case-row').length;
        document.getElementById('dgCaseCount').textContent = totalRows;
    });

    document.addEventListener('DOMContentLoaded', function() {
        // Inner tabs coloring
        $('.inner-tab-btn').on('shown.bs.tab', function (e) {
            var $parentUl = $(e.target).closest('ul');
            $parentUl.find('.inner-tab-btn').removeClass('text-primary').addClass('text-muted');
            $(e.target).removeClass('text-muted').css('color', 'var(--rd-primary)');
        });
    });
</script>
@endsection
