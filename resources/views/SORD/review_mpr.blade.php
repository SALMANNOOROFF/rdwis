@extends('welcome')

@section('content')
<div class="content-wrapper pt-3">
    <div class="container-fluid">
        
        <div class="row mb-3">
            <div class="col-md-6">
                <h3>Review MPR: <span class="text-primary">{{ $document->project->prj_title }}</span></h3>
            </div>
            <div class="col-md-6 text-right">
                <span class="badge badge-{{ $document->status == 'Returned' ? 'danger' : 'info' }} p-2">
                    Status: {{ $document->status }}
                </span>
            </div>
        </div>

        <form action="{{ route('sord.action') }}" method="POST">
            @csrf
            <input type="hidden" name="doc_id" value="{{ $document->doc_id }}">

            <div class="row">
                {{-- LEFT: DIVISION DATA --}}
                <div class="col-md-6">
                    <div class="card card-secondary">
                        <div class="card-header"><h3 class="card-title">Division Input</h3></div>
                        <div class="card-body bg-light">
                             <div class="form-group">
                                <label>Physical Progress (%)</label>
                                <input type="text" id="div_phy" class="form-control" readonly 
                                       value="{{ $divisionVersion->content_data['physical_progress'] ?? '' }}">
                            </div>
                            <div class="form-group">
                                <label>Financial Progress</label>
                                <input type="text" id="div_fin" class="form-control" readonly 
                                       value="{{ $divisionVersion->content_data['financial_progress'] ?? '' }}">
                            </div>
                            <div class="form-group">
                                <label>Remarks</label>
                                <textarea class="form-control" rows="3" readonly>{{ $divisionVersion->remarks ?? '' }}</textarea>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- CENTER COPY BUTTON (Only if Editable) --}}
                <div class="col-md-1 d-flex align-items-center justify-content-center">
                    @if($isEditable)
                    <button type="button" class="btn btn-outline-primary" onclick="copyData()" title="Copy Data">
                        <i class="fas fa-arrow-right"></i>
                    </button>
                    @endif
                </div>

                {{-- RIGHT: SORD INPUT --}}
                <div class="col-md-5">
                    <div class="card card-primary">
                        <div class="card-header"><h3 class="card-title">SORD Review</h3></div>
                        <div class="card-body">
                            
                            {{-- Field 1 --}}
                            <div class="form-group">
                                <label>Physical Progress (%)</label>
                                <input type="text" name="physical_progress" id="sord_phy" class="form-control" 
                                       value="{{ $sordVersion->content_data['physical_progress'] ?? '' }}"
                                       {{ !$isEditable ? 'disabled' : '' }}>
                            </div>

                            {{-- Field 2 --}}
                            <div class="form-group">
                                <label>Financial Progress</label>
                                <input type="text" name="financial_progress" id="sord_fin" class="form-control" 
                                       value="{{ $sordVersion->content_data['financial_progress'] ?? '' }}"
                                       {{ !$isEditable ? 'disabled' : '' }}>
                            </div>

                            {{-- Remarks --}}
                            <div class="form-group">
                                <label>Reviewer Remarks</label>
                                <textarea name="remarks" class="form-control" rows="3" 
                                          {{ !$isEditable ? 'disabled' : '' }}>{{ $sordVersion->remarks ?? '' }}</textarea>
                            </div>

                        </div>
                        
                        {{-- FOOTER BUTTONS (Only if Editable) --}}
                        @if($isEditable)
                        <div class="card-footer d-flex justify-content-between">
                            <button type="submit" name="action" value="save" class="btn btn-info">Save Draft</button>
                            <button type="submit" name="action" value="return" class="btn btn-danger">Return</button>
                            <button type="submit" name="action" value="forward" class="btn btn-success">Finalize</button>
                        </div>
                        @else
                        <div class="card-footer text-center">
                            <span class="text-danger font-weight-bold"><i class="fas fa-lock"></i> Use 'Fix & Resubmit' from Division Dashboard</span>
                        </div>
                        @endif

                    </div>
                </div>
            </div>
        </form>
    </div>
</div>

<script>
    function copyData() {
        document.getElementById('sord_phy').value = document.getElementById('div_phy').value;
        document.getElementById('sord_fin').value = document.getElementById('div_fin').value;
    }
</script>
@endsection