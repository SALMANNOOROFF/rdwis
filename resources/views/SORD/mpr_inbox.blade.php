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
                            </td>
                            <td>{{ $doc->updated_at->format('d M, Y') }}</td>
                            <td>
                                {{-- Status Badge Logic --}}
                                @if($doc->status == 'Returned')
                                    <span class="badge badge-danger">{{ $doc->status }}</span>
                                @elseif($doc->status == 'Finalized')
                                    <span class="badge badge-success">{{ $doc->status }}</span>
                                @else
                                    <span class="badge badge-warning">{{ $doc->status }}</span>
                                @endif
                            </td>
                            <td>
                                {{-- Action Button Logic --}}
                                @if($doc->status == 'Returned')
                                    {{-- Locked View --}}
                                    <a href="{{ route('sord.review_mpr', $doc->doc_id) }}" class="btn btn-sm btn-secondary shadow-sm">
                                        <i class="fas fa-eye mr-1"></i> View (Returned)
                                    </a>
                                @elseif($doc->status == 'Finalized')
                                     <a href="{{ route('sord.review_mpr', $doc->doc_id) }}" class="btn btn-sm btn-success shadow-sm">
                                        <i class="fas fa-check mr-1"></i> Completed
                                    </a>
                                @else
                                    {{-- Active Review --}}
                                    <a href="{{ route('sord.review_mpr', $doc->doc_id) }}" class="btn btn-sm btn-primary shadow-sm">
                                        <i class="fas fa-edit mr-1"></i> Review
                                    </a>
                                @endif
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