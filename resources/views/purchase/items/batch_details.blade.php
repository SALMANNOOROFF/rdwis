@extends('welcome')

@section('content')
<div class="content-wrapper bg-white p-4">
    <style>
   
    </style>

    <div class="container-fluid pt-4">
        <div class="mb-3">
            <a href="{{ route('items.batch.list') }}" class="btn btn-link text-primary p-0"><i class="fas fa-arrow-left"></i> Back to Batches</a>
        </div>

        <div class="card detail-card shadow-sm mb-4">
            <div class="card-body">
                <div class="row">
                    <div class="col-md-8">
                        <span class="info-label">Batch Title</span>
                        <h4 class="font-weight-bold text-dark">{{ $batch->prq_desc }}</h4>
                    </div>
                    <div class="col-md-4 text-right">
                        <span class="info-label">Submission Date</span>
                        <p class="font-weight-bold">{{ \Carbon\Carbon::parse($batch->prq_date)->format('d M, Y') }}</p>
                    </div>
                </div>
            </div>
        </div>

        <h5 class="font-weight-bold mb-3"><i class="fas fa-list mr-2"></i> Items in this Batch</h5>
        <div class="card border-0 shadow-sm">
            <div class="card-body p-0">
                <table class="table table-bordered mb-0">
                    <thead class="bg-light">
                        <tr>
                            <th style="width: 70px;" class="text-center">Ser</th>
                            <th>Item Description</th>
                            <th class="text-center" style="width: 150px;">Quantity</th>
                            <th class="text-center" style="width: 150px;">Unit</th>
                        </tr>
                    </thead>
                    <tbody>
    @foreach($items as $item)
    <tr>
        <td class="text-center">{{ $item->pri_serial }}</td>
        <td class="font-weight-bold">{{ $item->pri_desc }}</td>
        <td class="text-center">{{ $item->pri_qty }}</td>
        <td class="text-center">
            <span class="badge badge-light border">{{ $item->pri_qtyunit }}</span>
        </td>
    </tr>
    @endforeach
</tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection