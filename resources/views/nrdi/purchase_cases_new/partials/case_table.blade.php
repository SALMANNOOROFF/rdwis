@php
    $activeStatuses = ['Pending', 'Under Scrutiny', 'Forwarded', 'Draft', 'Submitted', 'With DFinance', 'With MD', 'With DDG', 'With DG'];
    $processedStatuses = ['Approved', 'Returned', 'Rejected'];
    
    $divPending = $cases->whereIn('pcs_status', $activeStatuses);
    $divProcessed = $cases->whereIn('pcs_status', $processedStatuses);
@endphp

<div class="card shadow-sm border-0 rounded-lg">
    <div class="card-header bg-white border-bottom pt-3 pb-0">
        <ul class="nav nav-tabs border-0" id="innerCaseTabs-{{ $unitId }}" role="tablist">
            <li class="nav-item">
                <a class="nav-link active border-0 font-weight-bold pb-3 inner-tab-btn" data-toggle="tab" href="#pending-{{ $unitId }}" role="tab" style="color: var(--rd-primary);">
                    <i class="fas fa-clock mr-1"></i> Pending Action ({{ $divPending->count() }})
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link border-0 font-weight-bold text-muted pb-3 inner-tab-btn" data-toggle="tab" href="#processed-{{ $unitId }}" role="tab">
                    <i class="fas fa-check-circle mr-1"></i> Action Taken ({{ $divProcessed->count() }})
                </a>
            </li>
        </ul>
    </div>
    <div class="card-body p-0">
        <div class="tab-content">
            
            {{-- Inner PENDING --}}
            <div class="tab-pane fade show active" id="pending-{{ $unitId }}" role="tabpanel">
                @if($divPending->count() > 0)
                    <div class="table-responsive">
                        <table class="table table-hover table-striped mb-0 dg-case-table">
                            <thead class="bg-light">
                                <tr>
                                    <th class="border-0 pl-4">Type</th>
                                    <th class="border-0">Title / Description</th>
                                    <th class="border-0">Date</th>
                                    <th class="border-0 text-right">Est. Amount</th>
                                    <th class="border-0 text-center">Status</th>
                                    <th class="border-0 text-right pr-4">Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($divPending as $p)
                                <tr class="case-row">
                                    <td class="pl-4 align-middle">
                                        <span class="badge badge-primary px-2 py-1">{{ strtoupper($p->pcs_type) }}</span>
                                    </td>
                                    <td class="align-middle">
                                        <div class="font-weight-bold text-dark case-title">{{ $p->pcs_title }}</div>
                                        <small class="text-muted case-ref">Ref: {{ $p->pcs_type }}-{{ $p->pcs_id }}</small>
                                    </td>
                                    <td class="align-middle">{{ \Carbon\Carbon::parse($p->pcs_date)->format('d M, Y') }}</td>
                                    <td class="align-middle text-right font-weight-bold">Rs. <span class="case-amount">{{ number_format((float) ($p->pcs_price ?? 0)) }}</span></td>
                                    <td class="align-middle text-center">
                                        <span class="badge badge-warning text-dark"><i class="fas fa-hourglass-half mr-1"></i> {{ $p->pcs_status }}</span>
                                    </td>
                                    <td class="align-middle text-right pr-4">
                                        <a href="{{ route($detailsRouteName, $p->pcs_id) }}" class="btn btn-sm btn-outline-primary rounded-pill px-3 shadow-sm">View</a>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    <div class="text-center py-5">
                        <div class="display-4 text-muted mb-3"><i class="fas fa-check-circle opacity-25"></i></div>
                        <h6 class="text-muted">No pending cases here.</h6>
                    </div>
                @endif
            </div>

            {{-- Inner PROCESSED --}}
            <div class="tab-pane fade" id="processed-{{ $unitId }}" role="tabpanel">
                @if($divProcessed->count() > 0)
                    <div class="table-responsive">
                        <table class="table table-hover table-striped mb-0 dg-case-table">
                            <thead class="bg-light">
                                <tr>
                                    <th class="border-0 pl-4">Type</th>
                                    <th class="border-0">Title / Description</th>
                                    <th class="border-0">Date</th>
                                    <th class="border-0 text-right">Est. Amount</th>
                                    <th class="border-0 text-center">Status</th>
                                    <th class="border-0 text-right pr-4">Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($divProcessed as $p)
                                    @php
                                        $statusColor = 'success';
                                        if($p->pcs_status == 'Returned') $statusColor = 'warning';
                                        if($p->pcs_status == 'Rejected') $statusColor = 'danger';
                                    @endphp
                                <tr class="case-row">
                                    <td class="pl-4 align-middle">
                                        <span class="badge badge-secondary px-2 py-1">{{ strtoupper($p->pcs_type) }}</span>
                                    </td>
                                    <td class="align-middle">
                                        <div class="font-weight-bold text-dark case-title">{{ $p->pcs_title }}</div>
                                        <small class="text-muted case-ref">Ref: {{ $p->pcs_type }}-{{ $p->pcs_id }}</small>
                                    </td>
                                    <td class="align-middle">{{ \Carbon\Carbon::parse($p->pcs_date)->format('d M, Y') }}</td>
                                    <td class="align-middle text-right font-weight-bold">Rs. <span class="case-amount">{{ number_format((float) ($p->pcs_price ?? 0)) }}</span></td>
                                    <td class="align-middle text-center">
                                        <span class="badge badge-{{ $statusColor }} text-white">{{ $p->pcs_status }}</span>
                                    </td>
                                    <td class="align-middle text-right pr-4">
                                        <a href="{{ route($detailsRouteName, $p->pcs_id) }}" class="btn btn-sm btn-outline-secondary rounded-pill px-3 shadow-sm">View</a>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    <div class="text-center py-5">
                        <h6 class="text-muted">No processed cases here.</h6>
                    </div>
                @endif
            </div>

        </div>
    </div>
</div>
