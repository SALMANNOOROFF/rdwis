@extends('welcome')

@section('content')
<div class="content-wrapper bg-white p-4">


    <div class="container-fluid pt-4">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h3 class="font-weight-bold text-primary"><i class="fas fa-layer-group mr-2"></i> Submitted Batches</h3>
            <a href="{{ route('items.bulk.create') }}" class="btn btn-primary shadow-sm">
                <i class="fas fa-plus-circle mr-1"></i> Create New Batch
            </a>
        </div>

        <div class="card batch-card">
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead>
                            <tr>
                                <th style="width: 100px;">Batch ID</th>
                                <th>Group Title / Demand Description</th>
                                <th>Entry Date</th>
                                <th class="text-center">Status</th>
                                <th class="text-center">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($batches as $batch)
                            <tr>
                                <td class="font-weight-bold text-primary">#{{ $batch->prq_id }}</td>
                                <td>{{ $batch->prq_desc }}</td>
                                <td>{{ \Carbon\Carbon::parse($batch->prq_date)->format('d M, Y') }}</td>
                                <td class="text-center">
                                    <span class="badge badge-info px-3">{{ $batch->prq_status }}</span>
                                </td>
                                <td class="text-center">
    <div class="d-flex justify-content-center" style="gap: 5px;">
        <!-- View Button -->
        <a href="{{ route('items.batch.view', $batch->prq_id) }}" class="btn btn-sm btn-outline-primary rounded-pill px-3">
            View
        </a>

        <!-- Delete Button (Form ke sath taake secure rahe) -->
        <form action="{{ route('items.batch.delete', $batch->prq_id) }}" method="POST" onsubmit="return confirm('Are you sure? This will delete all items in this batch permanently.')">
            @csrf
            @method('DELETE')
            <button type="submit" class="btn btn-sm btn-outline-danger rounded-pill px-3">
                <i class="fas fa-trash-alt"></i>
            </button>
        </form>
    </div>
</td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="5" class="text-center py-5 text-muted">No batches found in database.</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection