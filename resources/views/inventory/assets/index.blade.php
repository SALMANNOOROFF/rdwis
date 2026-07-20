@extends('welcome')

@section('content')
<style>
    @import url('https://fonts.googleapis.com/css2?family=Rajdhani:wght@600;700&family=Inter:wght@400;500;600;700&display=swap');

    .asset-hub {
        font-family: 'Inter', sans-serif;
        background: #080b0f !important;
        color: #cbd5e0;
        padding-top: 15px;
        padding-bottom: 40px;
    }

    .rajdhani {
        font-family: 'Rajdhani', sans-serif;
        letter-spacing: 0.5px;
    }

    .card-cyber {
        background: rgba(18, 26, 34, 0.85);
        backdrop-filter: blur(12px);
        border: 1px solid rgba(255, 255, 255, 0.05);
        border-radius: 14px;
    }

    .stat-card {
        border-left: 4px solid #38bdf8;
    }
    .stat-card.assets-on {
        border-left-color: #10b981;
    }
    .stat-card.assets-off {
        border-left-color: #f59e0b;
    }
    .stat-card.inv-on {
        border-left-color: #06b6d4;
    }
    .stat-card.inv-off {
        border-left-color: #8b5cf6;
    }

    .table-cyber {
        background: transparent;
        color: #cbd5e0;
    }
    .table-cyber th {
        background: rgba(18, 26, 34, 0.95) !important;
        border-bottom: 2px solid rgba(255, 255, 255, 0.08) !important;
        color: #67e8f9 !important;
        font-family: 'Rajdhani', sans-serif;
        text-transform: uppercase;
        letter-spacing: 1px;
        font-size: 12px;
        font-weight: bold;
        padding: 12px 14px !important;
    }
    .table-cyber td {
        border-bottom: 1px solid rgba(255, 255, 255, 0.04) !important;
        padding: 12px 14px !important;
        vertical-align: middle;
        font-size: 13px;
    }
    .table-cyber tr:hover {
        background: rgba(255, 255, 255, 0.02) !important;
    }

    .form-control-cyber {
        background: rgba(10, 15, 22, 0.9);
        border: 1px solid rgba(255, 255, 255, 0.1);
        color: #fff;
        border-radius: 8px;
        font-size: 13px;
    }
    .form-control-cyber:focus {
        background: rgba(10, 15, 22, 1);
        border-color: #38bdf8;
        color: #fff;
    }

    .pill-link {
        font-size: 12px;
        font-weight: 600;
        border-radius: 20px;
        padding: 5px 15px;
    }

    .pagination .page-item .page-link {
        background: rgba(18, 26, 34, 0.9);
        border-color: rgba(255, 255, 255, 0.1);
        color: #cbd5e0;
    }
    .pagination .page-item.active .page-link {
        background: #0284c7;
        border-color: #0284c7;
        color: #fff;
    }
</style>

<div class="content-wrapper asset-hub px-4">
    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="text-white rajdhani font-weight-bold mb-1">Inventory & Assets Master</h2>
            <p class="text-muted small mb-0">
                @if($isDivision)
                    Showing inventory & assets strictly for <strong>{{ Auth::user()->acc_untname ?? 'Your Division' }}</strong>.
                @else
                    Showing cross-division inventory & assets for Command, Finance & Procurement.
                @endif
            </p>
        </div>
        <div>
            <a href="{{ route('purchase.receipts.index') }}" class="btn btn-sm btn-outline-info font-weight-bold">
                <i class="fas fa-boxes mr-1"></i> Receive Goods
            </a>
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success bg-success-subtle text-success border-success-subtle mb-4">
            {{ session('success') }}
        </div>
    @endif

    <!-- 4 Metric Cards -->
    <div class="row mb-4">
        <div class="col-md-3">
            <div class="card card-cyber stat-card assets-on p-3 mb-2">
                <span class="text-success small text-uppercase rajdhani font-weight-bold"><i class="fas fa-microchip mr-1"></i> Assets (On Charge)</span>
                <div class="d-flex justify-content-between align-items-baseline mt-2">
                    <h3 class="text-white rajdhani font-weight-bold mb-0">{{ number_format($assetsOnCharge->total_count ?? 0) }}</h3>
                    <span class="text-success font-weight-bold rajdhani" style="font-size: 14px;">PKR {{ number_format($assetsOnCharge->total_value ?? 0) }}</span>
                </div>
                <small class="text-muted mt-1">Durable Equipment in Store</small>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card card-cyber stat-card assets-off p-3 mb-2">
                <span class="text-warning small text-uppercase rajdhani font-weight-bold"><i class="fas fa-user-check mr-1"></i> Assets (Off Charge)</span>
                <div class="d-flex justify-content-between align-items-baseline mt-2">
                    <h3 class="text-white rajdhani font-weight-bold mb-0">{{ number_format($assetsOffCharge->total_count ?? 0) }}</h3>
                    <span class="text-warning font-weight-bold rajdhani" style="font-size: 14px;">PKR {{ number_format($assetsOffCharge->total_value ?? 0) }}</span>
                </div>
                <small class="text-muted mt-1">Issued / Installed Equipment</small>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card card-cyber stat-card inv-on p-3 mb-2">
                <span class="text-info small text-uppercase rajdhani font-weight-bold"><i class="fas fa-warehouse mr-1"></i> Inventory (On Charge)</span>
                <div class="d-flex justify-content-between align-items-baseline mt-2">
                    <h3 class="text-white rajdhani font-weight-bold mb-0">{{ number_format($invOnCharge->total_count ?? 0) }}</h3>
                    <span class="text-info font-weight-bold rajdhani" style="font-size: 14px;">PKR {{ number_format($invOnCharge->total_value ?? 0) }}</span>
                </div>
                <small class="text-muted mt-1">Consumables / Stock in Hand</small>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card card-cyber stat-card inv-off p-3 mb-2">
                <span class="text-purple small text-uppercase rajdhani font-weight-bold" style="color: #c084fc;"><i class="fas fa-dolly mr-1"></i> Inventory (Off Charge)</span>
                <div class="d-flex justify-content-between align-items-baseline mt-2">
                    <h3 class="text-white rajdhani font-weight-bold mb-0">{{ number_format($invOffCharge->total_count ?? 0) }}</h3>
                    <span class="font-weight-bold rajdhani" style="font-size: 14px; color: #c084fc;">PKR {{ number_format($invOffCharge->total_value ?? 0) }}</span>
                </div>
                <small class="text-muted mt-1">Consumed Materials</small>
            </div>
        </div>
    </div>

    <!-- Quick Filter Pills -->
    <div class="mb-3 d-flex flex-wrap gap-2">
        <a href="{{ route('inventory.assets.index', array_merge(request()->except(['category', 'group', 'page']), ['category' => 'All', 'group' => 'All'])) }}" 
           class="btn btn-sm pill-link {{ $category === 'All' && $statusGroup === 'All' ? 'btn-light' : 'btn-outline-light' }} mr-2">
            All Items
        </a>
        <a href="{{ route('inventory.assets.index', array_merge(request()->except(['category', 'group', 'page']), ['category' => 'Assets', 'group' => 'OnCharge'])) }}" 
           class="btn btn-sm pill-link {{ $category === 'Assets' && $statusGroup === 'OnCharge' ? 'btn-success' : 'btn-outline-success' }} mr-2">
            <i class="fas fa-microchip mr-1"></i> Assets (On Charge)
        </a>
        <a href="{{ route('inventory.assets.index', array_merge(request()->except(['category', 'group', 'page']), ['category' => 'Assets', 'group' => 'OffCharge'])) }}" 
           class="btn btn-sm pill-link {{ $category === 'Assets' && $statusGroup === 'OffCharge' ? 'btn-warning' : 'btn-outline-warning' }} mr-2">
            <i class="fas fa-user-check mr-1"></i> Assets (Off Charge)
        </a>
        <a href="{{ route('inventory.assets.index', array_merge(request()->except(['category', 'group', 'page']), ['category' => 'Inventory', 'group' => 'OnCharge'])) }}" 
           class="btn btn-sm pill-link {{ $category === 'Inventory' && $statusGroup === 'OnCharge' ? 'btn-info' : 'btn-outline-info' }} mr-2">
            <i class="fas fa-warehouse mr-1"></i> Inventory (On Charge)
        </a>
        <a href="{{ route('inventory.assets.index', array_merge(request()->except(['category', 'group', 'page']), ['category' => 'Inventory', 'group' => 'OffCharge'])) }}" 
           class="btn btn-sm pill-link {{ $category === 'Inventory' && $statusGroup === 'OffCharge' ? 'btn-secondary text-white' : 'btn-outline-secondary' }} mr-2">
            <i class="fas fa-dolly mr-1"></i> Inventory (Off Charge)
        </a>
    </div>

    <!-- Multi-Filter Card -->
    <div class="card card-cyber p-3 mb-4">
        <form method="GET" action="{{ route('inventory.assets.index') }}">
            <input type="hidden" name="category" value="{{ $category }}">
            <input type="hidden" name="group" value="{{ $statusGroup }}">

            <div class="row align-items-end">
                <!-- Division Filter -->
                <div class="col-md-4 mb-2">
                    <label class="text-muted small font-weight-bold mb-1">Division / Unit</label>
                    @if($isDivision)
                        <div class="form-control form-control-cyber d-flex align-items-center bg-dark text-info font-weight-bold">
                            <i class="fas fa-building mr-2 text-muted"></i> {{ Auth::user()->acc_untname ?? 'Your Division' }}
                        </div>
                    @else
                        <select name="unit_id" class="form-control form-control-cyber" onchange="this.form.submit()">
                            <option value="All" {{ $unitId === 'All' ? 'selected' : '' }}>-- All Divisions / Units --</option>
                            @foreach($units as $u)
                                <option value="{{ $u->unt_id }}" {{ (string)$unitId === (string)$u->unt_id ? 'selected' : '' }}>
                                    {{ $u->unt_namesh }} - {{ $u->unt_name }}
                                </option>
                            @endforeach
                        </select>
                    @endif
                </div>

                <!-- Project / Budget Head Filter -->
                <div class="col-md-4 mb-2">
                    <label class="text-muted small font-weight-bold mb-1">Project / Budget Head</label>
                    <select name="head_id" class="form-control form-control-cyber" onchange="this.form.submit()">
                        <option value="All" {{ $headId === 'All' ? 'selected' : '' }}>-- All Projects --</option>
                        @foreach($heads as $h)
                            <option value="{{ $h->hed_id }}" {{ (string)$headId === (string)$h->hed_id ? 'selected' : '' }}>
                                {{ $h->hed_code }} - {{ $h->hed_name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <!-- Status Filter -->
                <div class="col-md-3 mb-2">
                    <label class="text-muted small font-weight-bold mb-1">Detailed Status</label>
                    <select name="status" class="form-control form-control-cyber" onchange="this.form.submit()">
                        <option value="All" {{ $status === 'All' ? 'selected' : '' }}>-- All Statuses --</option>
                        <optgroup label="On Charge (Store)">
                            <option value="Held" {{ $status === 'Held' ? 'selected' : '' }}>Held in Store</option>
                            <option value="Tagged" {{ $status === 'Tagged' ? 'selected' : '' }}>Tagged</option>
                            <option value="Untagged" {{ $status === 'Untagged' ? 'selected' : '' }}>Untagged</option>
                        </optgroup>
                        <optgroup label="Off Charge (Issued)">
                            <option value="Issued to User" {{ $status === 'Issued to User' ? 'selected' : '' }}>Issued to User</option>
                            <option value="Installed" {{ $status === 'Installed' ? 'selected' : '' }}>Installed</option>
                            <option value="Consumed" {{ $status === 'Consumed' ? 'selected' : '' }}>Consumed</option>
                            <option value="Written Off" {{ $status === 'Written Off' ? 'selected' : '' }}>Written Off</option>
                        </optgroup>
                    </select>
                </div>

                <!-- Filter Action -->
                <div class="col-md-1 mb-2">
                    <button type="submit" class="btn btn-sm btn-info btn-block rajdhani font-weight-bold">
                        APPLY
                    </button>
                </div>
            </div>

            <div class="row mt-2">
                <div class="col-md-12">
                    <input type="text" name="search" class="form-control form-control-cyber" value="{{ $search }}" placeholder="Search description, custodian name, location, case title, or asset ID...">
                </div>
            </div>
        </form>
    </div>

    <!-- Data Table -->
    <div class="card card-cyber p-4 mb-4">
        <div class="table-responsive">
            <table class="table table-cyber mb-0">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Type</th>
                        <th>Item Description & Case</th>
                        <th>Project Head</th>
                        <th>Division</th>
                        <th>Qty & Unit Price</th>
                        <th>Total Value</th>
                        <th>Status</th>
                        <th>Custodian / Location</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($assets as $a)
                        @php
                            $totalVal = ((float)($a->iac_qty ?? 0)) * ((float)($a->ias_price ?? 0));
                            $isAsset = (string)$a->ias_type === '7';
                        @endphp
                        <tr>
                            <td class="rajdhani text-info font-weight-bold">#{{ $a->iac_id }}</td>
                            <td>
                                @if($isAsset)
                                    <span class="badge badge-success font-weight-bold">Asset</span>
                                @else
                                    <span class="badge badge-info font-weight-bold">Inventory</span>
                                @endif
                                <div class="small text-muted">{{ $a->ias_subtype ?? 'General' }}</div>
                            </td>
                            <td>
                                <div class="font-weight-bold text-white mb-1">{{ $a->ias_desc }}</div>
                                <div class="small text-muted">
                                    @if(!empty($a->ias_pcs_id))
                                        Case <a href="{{ route('purchase.receipts.create', $a->ias_pcs_id) }}" class="text-info font-weight-bold">#{{ $a->ias_pcs_id }}</a>: {{ \Illuminate\Support\Str::limit($a->pcs_title ?? 'N/A', 30) }}
                                    @else
                                        Direct Entry
                                    @endif
                                </div>
                            </td>
                            <td>
                                <span class="badge badge-secondary p-1">{{ $a->hed_code ?? 'N/A' }}</span>
                                <div class="small text-muted">{{ \Illuminate\Support\Str::limit($a->hed_name ?? '', 20) }}</div>
                            </td>
                            <td>
                                <strong class="text-white">{{ $a->unt_namesh ?? 'N/A' }}</strong>
                            </td>
                            <td>
                                <div>{{ $a->iac_qty }} {{ $a->iac_qtyunit }}</div>
                                <div class="small text-muted rajdhani">PKR {{ number_format($a->ias_price) }} / unit</div>
                            </td>
                            <td class="rajdhani text-success font-weight-bold">
                                PKR {{ number_format($totalVal) }}
                            </td>
                            <td>
                                @if(in_array($a->iac_status, ['Untagged', 'Tagged', 'Held']))
                                    <span class="badge badge-success px-2 py-1">{{ $a->iac_status }} (On Charge)</span>
                                @else
                                    <span class="badge badge-warning px-2 py-1">{{ $a->iac_status }} (Off Charge)</span>
                                @endif
                            </td>
                            <td>
                                <div class="small text-white font-weight-bold">{{ $a->iac_person ?? 'Store Custody' }}</div>
                                <div class="small text-muted"><i class="fas fa-map-marker-alt text-danger mr-1"></i>{{ $a->iac_location ?? 'Main Warehouse' }}</div>
                            </td>
                            <td>
                                <button type="button" class="btn btn-sm btn-outline-info" data-toggle="modal" data-target="#updateModal{{ $a->iac_id }}">
                                    Transition
                                </button>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="10" class="text-center py-4 text-muted">
                                No inventory items or assets found matching the selected filters.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="mt-3 d-flex justify-content-center">
            {{ $assets->appends(request()->all())->links('pagination::bootstrap-4') }}
        </div>
    </div>

    <!-- Modals Section -->
    @foreach($assets as $a)
        <div class="modal fade" id="updateModal{{ $a->iac_id }}" tabindex="-1" role="dialog" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered" role="document">
                <div class="modal-content bg-dark border border-secondary text-white">
                    <div class="modal-header border-secondary">
                        <h5 class="modal-title rajdhani font-weight-bold">Item #{{ $a->iac_id }} Status Transition</h5>
                        <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <form action="{{ route('inventory.assets.update_status', $a->iac_id) }}" method="POST">
                        @csrf
                        <div class="modal-body">
                            <div class="mb-3 text-muted small">
                                Item: <strong class="text-white">{{ $a->ias_desc }}</strong> | Qty: <strong class="text-white">{{ $a->iac_qty }} {{ $a->iac_qtyunit }}</strong>
                            </div>

                            <div class="form-group mb-3">
                                <label class="text-muted small">Target Status <span class="text-danger">*</span></label>
                                <select name="iac_status" class="form-control form-control-cyber" required>
                                    <optgroup label="On Charge (Store Custody)">
                                        <option value="Held" {{ $a->iac_status === 'Held' ? 'selected' : '' }}>Held in Store</option>
                                        <option value="Tagged" {{ $a->iac_status === 'Tagged' ? 'selected' : '' }}>Tagged</option>
                                        <option value="Untagged" {{ $a->iac_status === 'Untagged' ? 'selected' : '' }}>Untagged</option>
                                    </optgroup>
                                    <optgroup label="Off Charge (Dispensed Custody)">
                                        <option value="Issued to User" {{ $a->iac_status === 'Issued to User' ? 'selected' : '' }}>Issued to User</option>
                                        <option value="Installed" {{ $a->iac_status === 'Installed' ? 'selected' : '' }}>Installed</option>
                                        <option value="Consumed" {{ $a->iac_status === 'Consumed' ? 'selected' : '' }}>Consumed</option>
                                        <option value="Written Off" {{ $a->iac_status === 'Written Off' ? 'selected' : '' }}>Written Off</option>
                                    </optgroup>
                                </select>
                            </div>

                            <div class="form-group mb-3">
                                <label class="text-muted small">Custodian / Issued To Person</label>
                                <input type="text" name="iac_person" class="form-control form-control-cyber" value="{{ $a->iac_person }}" placeholder="e.g. John Doe / Engr. Ali">
                            </div>

                            <div class="form-group mb-3">
                                <label class="text-muted small">Location</label>
                                <input type="text" name="iac_location" class="form-control form-control-cyber" value="{{ $a->iac_location }}" placeholder="e.g. Lab 102 / Office 5">
                            </div>

                            <div class="form-group mb-3">
                                <label class="text-muted small">Remarks</label>
                                <textarea name="iac_remarks" class="form-control form-control-cyber" rows="2" placeholder="Notes or issue reference">{{ $a->iac_remarks }}</textarea>
                            </div>
                        </div>
                        <div class="modal-footer border-secondary">
                            <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                            <button type="submit" class="btn btn-success rajdhani font-weight-bold">SAVE TRANSITION</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    @endforeach
</div>
@endsection
