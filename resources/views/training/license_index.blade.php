@extends('welcome')

@section('content')
<div class="content-wrapper">
    <div class="content-header pb-2">
        <div class="container-fluid">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <div>
                    <h1 class="m-0 font-weight-bold" style="color:var(--rd-text1); letter-spacing: -0.5px;">
                        <i class="fas fa-shield-alt mr-2 text-info"></i> Licence Management
                    </h1>
                    <p class="text-muted mb-0 small">Shared institutional licences and subscription tracking</p>
                </div>
                <div>
                    <a href="{{ route('training.license.create') }}" class="btn btn-primary btn-sm px-3 shadow-sm" style="border-radius:6px;">
                        <i class="fas fa-plus-circle mr-2"></i> Buy New Licence
                    </a>
                </div>
            </div>
        </div>
    </div>

    <section class="content">
        <div class="container-fluid">
            {{-- Search & Filters --}}
            <div class="row mb-4">
                <div class="col-md-12">
                    <div class="d-flex bg-dark p-2 border border-secondary" style="border-radius: 12px; background: rgba(0,0,0,0.2) !important;">
                        <div class="input-group input-group-sm mr-2" style="max-width: 300px;">
                            <div class="input-group-prepend">
                                <span class="input-group-text bg-transparent border-0 text-muted"><i class="fas fa-search"></i></span>
                            </div>
                            <input type="text" class="form-control bg-transparent border-0 text-white" placeholder="Search licences...">
                        </div>
                        <select class="form-control form-control-sm bg-dark border-secondary text-white mr-2" style="width: 150px;">
                            <option>All Departments</option>
                            <option>IT</option>
                            <option>HR</option>
                            <option>Research</option>
                        </select>
                        <button class="btn btn-outline-secondary btn-sm"><i class="fas fa-filter mr-1"></i> Filter</button>
                    </div>
                </div>
            </div>

            {{-- Licence List (Compact) --}}
            <div class="card shadow-sm border-0" style="background:var(--rd-surface); border-radius:12px; border: 1px solid rgba(255,255,255,0.05) !important;">
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover mb-0 text-white" style="border-collapse: separate; border-spacing: 0;">
                            <thead>
                                <tr class="extra-small text-uppercase tracking-wider text-muted" style="background: rgba(0,0,0,0.2);">
                                    <th class="py-3 pl-4 border-0" style="width: 50px;">#</th>
                                    <th class="py-3 border-0">Product / Tool</th>
                                    <th class="py-3 border-0">Department</th>
                                    <th class="py-3 border-0" style="width: 200px;">Seats / Availability</th>
                                    <th class="py-3 border-0">Expiry</th>
                                    <th class="py-3 pr-4 border-0 text-right">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($licenses as $license)
                                    <tr class="licence-row" style="transition: all 0.2s ease;">
                                        <td class="py-3 pl-4 border-top border-secondary align-middle">
                                            <div class="icon-box-sm" style="background: {{ $license['color'] }}15; color: {{ $license['color'] }}; width: 32px; height: 32px; border-radius: 8px; display: flex; align-items: center; justify-content: center; font-size: 0.9rem;">
                                                <i class="{{ $license['icon'] }}"></i>
                                            </div>
                                        </td>
                                        <td class="py-3 border-top border-secondary align-middle text-white">
                                            <div class="font-weight-bold mb-0" style="font-size: 0.95rem;">{{ $license['product'] }}</div>
                                            <div class="extra-small text-muted text-uppercase" style="font-size: 0.6rem;">ID: LIC-00{{ $license['id'] }}</div>
                                        </td>
                                        <td class="py-3 border-top border-secondary align-middle">
                                            <span class="badge badge-dark border border-secondary extra-small px-2 py-1" style="color:var(--rd-text2)">{{ $license['department'] }}</span>
                                        </td>
                                        <td class="py-3 border-top border-secondary align-middle">
                                            <div class="d-flex align-items-center">
                                                <div class="flex-grow-1 mr-3">
                                                    @php $percent = ($license['available'] / $license['total_seats']) * 100; @endphp
                                                    <div class="progress" style="height: 4px; background: rgba(255,255,255,0.05); border-radius: 2px;">
                                                        <div class="progress-bar {{ $percent > 20 ? 'bg-success' : 'bg-danger' }}" role="progressbar" style="width: {{ $percent }}%"></div>
                                                    </div>
                                                </div>
                                                <span class="extra-small font-weight-bold {{ $license['available'] > 0 ? 'text-success' : 'text-danger' }}">
                                                    {{ $license['available'] }} / {{ $license['total_seats'] }}
                                                </span>
                                            </div>
                                        </td>
                                        <td class="py-3 border-top border-secondary align-middle">
                                            <div class="font-weight-bold text-white small">{{ \Carbon\Carbon::parse($license['expiry'])->format('M d, Y') }}</div>
                                            <div class="extra-small text-muted" style="font-size: 0.6rem;">Renewal Due</div>
                                        </td>
                                        <td class="py-3 pr-4 border-top border-secondary align-middle text-right">
                                            <button class="btn btn-info btn-xs px-3 btn-request py-1" data-id="{{ $license['id'] }}" data-name="{{ $license['product'] }}" style="border-radius: 4px; font-size: 0.75rem;">
                                                <i class="fas fa-paper-plane mr-1"></i> Request
                                            </button>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </section>
</div>

{{-- Request Modal --}}
<div class="modal fade" id="requestModal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content border-0 shadow-lg" style="background: #1a1a27; border-radius: 20px;">
            <div class="modal-header border-0 text-center d-block pt-4">
                <div class="icon-circle mb-3 bg-info-soft mx-auto" style="width:60px; height:60px; background:rgba(23,162,184,0.1); border-radius:50%; display:flex; align-items:center; justify-content:center;">
                    <i class="fas fa-key text-info fa-2x"></i>
                </div>
                <h4 class="modal-title font-weight-bold w-100 text-white" id="modalLicName">Adobe Creative Cloud</h4>
                <p class="text-muted small mt-2">Requesting access from holding department</p>
            </div>
            <div class="modal-body px-4 pb-4">
                <div id="requestForm">
                    <div class="form-group">
                        <label class="extra-small text-uppercase text-muted">Reason for Access</label>
                        <textarea class="form-control bg-dark border-secondary text-white" rows="3" placeholder="Explain why you need this licence..."></textarea>
                    </div>
                    <button type="button" id="submitRequest" class="btn btn-info btn-block py-2 font-weight-bold" style="border-radius:10px;">
                        Send Request Now
                    </button>
                </div>
                <div id="requestSuccess" class="d-none animated fadeIn text-center py-3">
                    <div class="alert alert-success bg-success-soft border-0 mb-4" style="background:rgba(40,167,69,0.1); color:#28a745;">
                        <i class="fas fa-check-circle mr-2"></i> Request Approved! Credentials Shared.
                    </div>
                    <div class="card bg-dark border-secondary p-3 mb-3 text-left">
                        <div class="extra-small text-muted mb-2">SHARED CREDENTIALS</div>
                        <div class="mb-2">
                            <label class="extra-small text-muted mb-0">USER ID</label>
                            <div class="text-white font-weight-bold" id="simUser">rdwis_user_782</div>
                        </div>
                        <div>
                            <label class="extra-small text-muted mb-0">PASSWORD</label>
                            <div class="text-info font-weight-bold">
                                ************ <span class="badge badge-info ml-2">Shared via SSO</span>
                            </div>
                        </div>
                    </div>
                    <button type="button" class="btn btn-outline-secondary btn-sm" data-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>
</div>

@endsection

@section('scripts')
<style>
    :root {
        --rd-surface: #1e1e2d;
        --rd-text1: #ffffff;
        --rd-text2: #a2a3b7;
    }
    .extra-small { font-size: 0.65rem; font-weight: 700; letter-spacing: 0.5px; margin-bottom: 4px; }
    .licence-card:hover { 
        transform: translateY(-8px);
        box-shadow: 0 15px 35px rgba(0,0,0,0.4) !important;
        border: 1px solid rgba(var(--rd-info-rgb), 0.3) !important;
    }
    .licence-card { border: 1px solid rgba(255,255,255,0.05) !important; }
    .animated { animation-duration: 0.5s; }
</style>

<script>
$(document).ready(function() {
    $('.btn-request').click(function() {
        const id = $(this).data('id');
        const name = $(this).data('name');
        $('#modalLicName').text(name);
        $('#requestForm').removeClass('d-none');
        $('#requestSuccess').addClass('d-none');
        $('#requestModal').modal('show');
    });

    $('#submitRequest').click(function() {
        $(this).html('<i class="fas fa-spinner fa-spin mr-2"></i> Sending...');
        setTimeout(() => {
            $('#requestForm').addClass('d-none');
            $('#requestSuccess').removeClass('d-none');
            $('#simUser').text('rdwis_user_' + Math.floor(Math.random() * 900 + 100));
        }, 1500);
    });
});
</script>
@endsection
