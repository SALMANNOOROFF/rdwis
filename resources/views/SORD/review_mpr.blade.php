@extends('welcome')

@section('content')
<div class="content-wrapper pt-3">
    <section class="content">
        <div class="container-fluid">

            {{-- HEADER / BACK BUTTON --}}
            <div class="row mb-3">
                <div class="col-md-6">
                    <a href="{{ route('sord.inbox') }}" class="btn btn-secondary shadow-sm">
                        <i class="fas fa-arrow-left mr-1"></i> Back to Inbox
                    </a>
                </div>
                <div class="col-md-6 text-right">
                    <span class="badge badge-info p-2" style="font-size: 1rem;">
                        Project Ref: {{ $document->doc_ref_no ?? $project->prj_code }}
                    </span>
                </div>
            </div>

            <div class="row">
                
                {{-- LEFT: PROJECT INFO (Read Only) --}}
                <div class="col-md-4">
                    <div class="card card-primary card-outline h-100">
                        <div class="card-body box-profile">
                            <h3 class="profile-username text-center">{{ $project->prj_title }}</h3>
                            <p class="text-muted text-center">{{ $project->unit->unt_name ?? 'Unknown Division' }}</p>

                            <ul class="list-group list-group-unbordered mb-3">
                                <li class="list-group-item">
                                    <b>Sponsor</b> <a class="float-right">{{ $project->prj_sponsor }}</a>
                                </li>
                                <li class="list-group-item">
                                    <b>Cost</b> <a class="float-right">{{ number_format($project->prj_cost) }}</a>
                                </li>
                                <li class="list-group-item">
                                    <b>Initiated By</b> <a class="float-right">{{ $document->creator->acc_name ?? 'N/A' }}</a>
                                </li>
                                <li class="list-group-item">
                                    <b>Current Status</b> <span class="float-right badge badge-warning">{{ $document->status }}</span>
                                </li>
                            </ul>

                            {{-- Last Version Info --}}
                            @if($document->versions->count() > 0)
                                <div class="alert alert-light border">
                                    <small><strong>Last Update:</strong> {{ $document->latestVersion->remarks }}</small>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>

                {{-- RIGHT: REVIEW FORM (Editable) --}}
                <div class="col-md-8">
                    <div class="card card-success shadow">
                        <div class="card-header">
                            <h3 class="card-title text-bold">
                                <i class="fas fa-search-plus mr-1"></i> Review & Action
                            </h3>
                        </div>
                        
                        {{-- Form Submit wahi purane route par jayega (store logic same hai) --}}
                        <form action="{{ route('mpr.store', $project->prj_id) }}" method="POST">
                            @csrf
                            <div class="card-body">
                                
                                @php
                                    $latestData = $document->latestVersion ? $document->latestVersion->content_data : [];
                                    $latestRemarks = $document->latestVersion ? $document->latestVersion->remarks : '';
                                @endphp

                                <div class="alert alert-info">
                                    <i class="fas fa-info-circle"></i> You can edit the values below if correction is needed before forwarding/returning.
                                </div>

                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label>Verified Physical Progress (%)</label>
                                            <div class="input-group">
                                                <input type="number" name="physical_progress" class="form-control font-weight-bold" 
                                                       value="{{ $latestData['physical_progress'] ?? '' }}" 
                                                       step="0.1" min="0" max="100"
                                                       {{ !$isEditable ? 'disabled' : '' }}>
                                                <div class="input-group-append"><span class="input-group-text">%</span></div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label>Verified Financial Progress (Rs)</label>
                                            <div class="input-group">
                                                <div class="input-group-prepend"><span class="input-group-text">Rs</span></div>
                                                <input type="number" name="financial_progress" class="form-control font-weight-bold" 
                                                       value="{{ $latestData['financial_progress'] ?? '' }}"
                                                       {{ !$isEditable ? 'disabled' : '' }}>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="form-group">
                                    <label>Reviewer Remarks / Instructions</label>
                                    <textarea name="remarks" class="form-control" rows="5" 
                                              placeholder="Enter your observations or instructions for the division here..."
                                              {{ !$isEditable ? 'disabled' : '' }}>{{ $latestRemarks }}</textarea>
                                </div>

                            </div>
                            
                            {{-- ACTION BUTTONS --}}
                            @if($isEditable)
                            <div class="card-footer bg-white border-top">
                                <div class="row">
                                    <div class="col-md-4">
                                        <button type="submit" name="action" value="save" class="btn btn-default btn-block shadow-sm">
                                            <i class="fas fa-save mr-1"></i> Save Draft
                                        </button>
                                    </div>
                                    <div class="col-md-4">
                                        <button type="submit" name="action" value="return" class="btn btn-warning text-white btn-block shadow-sm"
                                            onclick="return confirm('Return to Division for Correction?');">
                                            <i class="fas fa-undo mr-1"></i> Return to Division
                                        </button>
                                    </div>
                                    <div class="col-md-4">
                                        {{-- Future: Add Forward Logic --}}
                                        <button type="button" class="btn btn-dark btn-block shadow-sm" disabled>
                                            Forward to MD <i class="fas fa-arrow-right ml-1"></i>
                                        </button>
                                    </div>
                                </div>
                            </div>
                            @else
                                <div class="card-footer text-center text-muted">
                                    <em>View Only - Action already taken.</em>
                                </div>
                            @endif

                        </form>
                    </div>
                </div>

            </div>
        </div>
    </section>
</div>
@endsection