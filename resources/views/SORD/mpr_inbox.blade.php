@extends('welcome')

@section('content')
<div class="content-wrapper pt-3">
    <div class="container-fluid">
        <div class="card card-primary card-outline">
            <div class="card-header">
                <h3 class="card-title">Incoming MPRs (Approval Queue)</h3>
            </div>
            <div class="card-body p-0">
                <table class="table table-hover table-striped">
                    <thead>
                        <tr>
                            <th>Ref No</th>
                            <th>Project Title</th>
                            <th>From (Division)</th>
                            <th>Received Date</th>
                            <th>Status</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($documents as $doc)
                        <tr>
                            <td>{{ $doc->doc_ref_no ?? $doc->doc_id }}</td>
                            <td>{{ $doc->project->prj_title }}</td>
                            <td>
                                <span class="font-weight-bold text-dark">
                                    {{ $doc->creator->unit->unt_name ?? 'Division' }}
                                </span>
                                <br>
                                {{-- Show Role if available, else name --}}
                                <small class="text-muted">
                                    <i class="fas fa-user-tag mr-1"></i>
                                    {{ $doc->creator->role->rol_desig ?? $doc->creator->acc_name }}
                                </small>
                            </td>
                            <td>{{ $doc->updated_at->format('d M, Y') }}</td>
                            <td><span class="badge badge-warning">{{ $doc->status }}</span></td>
                            <td>
                                <a href="{{ route('sord.review_mpr', $doc->prj_id) }}" class="btn btn-sm btn-primary shadow-sm">
                                    <i class="fas fa-search-plus mr-1"></i> Review
                                </a>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="6" class="text-center py-5 text-muted">No Pending MPRs found.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection