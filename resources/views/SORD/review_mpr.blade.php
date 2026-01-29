@extends('welcome')

@section('content')
<div class="content-wrapper pt-3">
    <section class="content">
        <div class="container-fluid">
            
            <div class="row mb-2">
                <div class="col-6">
                    <a href="{{ route('sord.inbox') }}" class="btn btn-secondary btn-sm"><i class="fas fa-arrow-left"></i> Back to Inbox</a>
                </div>
                <div class="col-6 text-right">
                    {{-- Status Badge --}}
                    @php
                        $badge = 'badge-secondary';
                        if($document->status == 'Returned') $badge = 'badge-danger';
                        if($document->status == 'Pending Review') $badge = 'badge-warning';
                        if($document->status == 'Forwarded to MD') $badge = 'badge-success';
                    @endphp
                    <span class="badge {{ $badge }} p-2">Status: {{ $document->status }}</span>
                </div>
            </div>

            {{-- === SUCCESS / LOCKED ALERT === --}}
            @if(!$isEditable)
                <div class="alert alert-info shadow-sm border-left-info">
                    <h5><i class="icon fas fa-check-circle"></i> Action Completed!</h5>
                    You have processed this document.
                    <br><strong>New Status:</strong> {{ $document->status }}
                    <br><strong>Currently With:</strong> {{ $document->currentOwner->role->rol_desig ?? ($document->currentOwner->acc_name ?? 'Division / MD') }}
                    <br><small>You cannot make further changes unless it is sent back to you.</small>
                </div>
            @endif

            <form action="{{ route('mpr.store', $project->prj_id) }}" method="POST">
                @csrf
                <div class="row">
                    
                    {{-- SORD EDITABLE BOX --}}
                    <div class="col-md-6">
                        <div class="card card-outline {{ $isEditable ? 'card-success' : 'card-secondary' }}">
                            <div class="card-header">
                                <h3 class="card-title font-weight-bold">SORD Review</h3>
                                @if($isEditable)
                                <button type="button" class="btn btn-xs btn-outline-primary float-right" onclick="copyData()">
                                    <i class="fas fa-copy"></i> Copy Division Data
                                </button>
                                @endif
                            </div>
                            
                            <div class="card-body">
                                <fieldset {{ !$isEditable ? 'disabled' : '' }}>
                                    <div class="form-group">
                                        <label>Physical (%)</label>
                                        <input type="number" id="sord_phy" name="physical_progress" class="form-control font-weight-bold" 
                                               value="{{ $latestVersion->content_data['physical_progress'] ?? '' }}" step="0.1" min="0" max="100">
                                    </div>
                                    <div class="form-group">
                                        <label>Financial (Rs)</label>
                                        <input type="number" id="sord_fin" name="financial_progress" class="form-control font-weight-bold" 
                                               value="{{ $latestVersion->content_data['financial_progress'] ?? '' }}">
                                    </div>
                                    <div class="form-group">
                                        <label>Remarks / Instructions <span class="text-danger">*</span></label>
                                        <textarea name="remarks" class="form-control" rows="5" required placeholder="Enter remarks...">{{ $latestVersion->remarks }}</textarea>
                                    </div>
                                </fieldset>
                            </div>
                            
                            @if($isEditable)
                            <div class="card-footer bg-white">
                                <div class="row">
                                    <div class="col-4">
                                        <button type="submit" name="action" value="save" class="btn btn-default btn-block shadow-sm">Save Draft</button>
                                    </div>
                                    <div class="col-4">
                                        <button type="submit" name="action" value="return" class="btn btn-warning text-white btn-block shadow-sm" onclick="return confirm('Return to Division?');">
                                            <i class="fas fa-undo"></i> Return
                                        </button>
                                    </div>
                                    <div class="col-4">
                                        <button type="submit" name="action" value="finalize" class="btn btn-success btn-block shadow-sm" onclick="return confirm('Forward to MD?');">
                                            Forward MD <i class="fas fa-arrow-right"></i>
                                        </button>
                                    </div>
                                </div>
                            </div>
                            @endif
                        </div>
                    </div>

                    {{-- DIVISION READ-ONLY BOX --}}
                    <div class="col-md-6">
                        <div class="card bg-light">
                            <div class="card-header">
                                <h3 class="card-title text-muted">Submission from Division</h3>
                            </div>
                            <div class="card-body">
                                <div class="form-group">
                                    <label class="small text-muted">Physical (%)</label>
                                    <input type="text" id="div_phy" class="form-control" readonly 
                                           value="{{ $divisionVersion->content_data['physical_progress'] ?? 0 }}">
                                </div>
                                <div class="form-group">
                                    <label class="small text-muted">Financial (Rs)</label>
                                    <input type="text" id="div_fin" class="form-control" readonly 
                                           value="{{ $divisionVersion->content_data['financial_progress'] ?? 0 }}">
                                </div>
                                <div class="form-group">
                                    <label class="small text-muted">Remarks</label>
                                    <div class="p-2 border bg-white rounded" style="min-height: 120px; font-style: italic;">
                                        {{ $divisionVersion->remarks }}
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                </div>
            </form>

        </div>
    </section>
</div>
<script>
    function copyData() {
        document.getElementById('sord_phy').value = document.getElementById('div_phy').value;
        document.getElementById('sord_fin').value = document.getElementById('div_fin').value;
    }
</script>
<style>
    .border-left-info { border-left: 5px solid #17a2b8; }
</style>
@endsection