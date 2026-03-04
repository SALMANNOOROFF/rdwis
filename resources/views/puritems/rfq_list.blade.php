@php
    $asset = fn($p) => asset($p);
@endphp
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>My RFQs</title>
    <link rel="stylesheet" href="{{ $asset('dist/css/adminlte.min.css') }}">
    <script src="{{ $asset('plugins/jquery/jquery.min.js') }}"></script>
    <script src="{{ $asset('plugins/bootstrap/js/bootstrap.bundle.min.js') }}"></script>
    <style>.table td,.table th{vertical-align:middle}</style>
    </head>
<body class="p-3">
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-2">
        <h5 class="mb-0">My Division RFQs</h5>
        <a href="{{ route('puritems.index') }}" class="btn btn-default btn-sm">Back to Items</a>
    </div>
    <form method="get" action="{{ route('puritems.rfq.list') }}" class="form-inline mb-2">
        <input type="text" name="term" class="form-control mr-2" placeholder="Search title">
        <button class="btn btn-primary btn-sm">Search</button>
    </form>
    <table class="table table-sm table-bordered">
        <thead>
            <tr>
                <th style="width:80px">RFQ #</th>
                <th>Title</th>
                <th style="width:120px">Status</th>
                <th style="width:140px">Total</th>
            </tr>
        </thead>
        <tbody>
            @forelse($rfqs as $r)
                <tr>
                    <td>{{ $r->rfq_id }}</td>
                    <td>{{ $r->rfq_title }}</td>
                    <td>{{ $r->rfq_status }}</td>
                    <td>{{ number_format($r->rfq_total,2) }}</td>
                </tr>
            @empty
                <tr><td colspan="4" class="text-center text-muted">No RFQs</td></tr>
            @endforelse
        </tbody>
    </table>
</div>
</body>
</html>

