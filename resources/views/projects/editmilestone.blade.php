@extends('welcome')

@section('content')
<div class="content-wrapper pt-3">
    <section class="content">
        <div class="container-fluid">
            <div class="card card-warning card-outline shadow">
                <div class="card-header">
                    <h3 class="card-title text-bold">
                        <i class="fas fa-edit mr-2"></i> Edit Milestone
                    </h3>
                </div>

                {{-- Update Form --}}
                <form action="{{ route('milestone.update', $milestone->msn_id) }}" method="POST">
                    @csrf
                    <div class="card-body">
                        
                        {{-- Description --}}
                        <div class="form-group">
                            <label>Milestone Description <span class="text-danger">*</span></label>
                            <textarea name="msn_desc" class="form-control" rows="3" required>{{ $milestone->msn_desc }}</textarea>
                        </div>

                        <div class="row">
                            {{-- Target Date --}}
                            <div class="col-md-4 form-group">
                                <label>Target Date <span class="text-danger">*</span></label>
                                <input type="date" name="msn_targetdt" class="form-control" value="{{ $milestone->msn_targetdt }}" required>
                            </div>

                            {{-- Type --}}
                            <div class="col-md-4 form-group">
                                <label>Type</label>
                                <select name="msn_type" class="form-control">
                                    <option value="Physical" {{ $milestone->msn_type == 'Physical' ? 'selected' : '' }}>Physical</option>
                                    <option value="Financial" {{ $milestone->msn_type == 'Financial' ? 'selected' : '' }}>Financial</option>
                                    <option value="General" {{ $milestone->msn_type == 'General' ? 'selected' : '' }}>General</option>
                                </select>
                            </div>

                            {{-- Status --}}
                            <div class="col-md-4 form-group">
                                <label>Status</label>
                                <select name="msn_status" class="form-control">
                                    <option value="Pending" {{ $milestone->msn_status == 'Pending' ? 'selected' : '' }}>Pending</option>
                                    <option value="In Progress" {{ $milestone->msn_status == 'In Progress' ? 'selected' : '' }}>In Progress</option>
                                    <option value="Completed" {{ $milestone->msn_status == 'Completed' ? 'selected' : '' }}>Completed</option>
                                </select>
                            </div>
                        </div>
                    </div>

                    <div class="card-footer text-right">
                        <a href="{{ route('projects.show', $project->prj_id) }}" class="btn btn-default mr-2">Cancel</a>
                        <button type="submit" class="btn btn-warning px-4"><i class="fas fa-save mr-1"></i> Update Changes</button>
                    </div>
                </form>
            </div>
        </div>
    </section>
</div>
@endsection