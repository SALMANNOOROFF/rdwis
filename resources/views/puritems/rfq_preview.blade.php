@php
    $asset = fn($p) => asset($p);
@endphp
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>RFQ Draft</title>
    <link rel="stylesheet" href="{{ $asset('dist/css/adminlte.min.css') }}">
</head>
<body class="p-4">
    <div class="container">
        <h4>Request for Quotation</h4>
        <form method="post" action="{{ route('puritems.rfq.submit') }}" id="rfq-submit-form">
            @csrf
            <div class="form-group mb-2">
                <label>RFQ Title</label>
                <input type="text" class="form-control" name="title" placeholder="Enter RFQ title" value="Draft RFQ">
            </div>
        <table class="table table-sm table-bordered mt-3">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Item</th>
                    <th>Qty</th>
                    <th>Unit</th>
                    <th>Unit Price</th>
                    <th>Total</th>
                </tr>
            </thead>
            <tbody>
            @foreach($rows as $i=>$r)
                <tr>
                    <td>{{ $i+1 }}</td>
                    <td>{{ $r['item']? $r['item']->itm_title : '' }}</td>
                    <td>{{ $r['qty'] }}</td>
                    <td>{{ $r['item']? $r['item']->itm_qtyunit : '' }}</td>
                    <td>{{ isset($r['unit']) ? number_format($r['unit'],2) : ($r['price']? $r['price']->prc_gross : '0') }}</td>
                    <td>{{ number_format($r['line'],2) }}</td>
                </tr>
                <input type="hidden" name="items[{{ $i }}][itm_id]" value="{{ $r['item']? $r['item']->itm_id : '' }}">
                <input type="hidden" name="items[{{ $i }}][qty]" value="{{ $r['qty'] }}">
                @if(!empty($r['price']) && empty($r['priceOverride']))
                    <input type="hidden" name="items[{{ $i }}][prc_id]" value="{{ $r['price']->prc_id }}">
                @endif
                @if(!empty($r['priceOverride']))
                    <input type="hidden" name="items[{{ $i }}][price]" value="{{ $r['priceOverride'] }}">
                @endif
            @endforeach
            </tbody>
            <tfoot>
                <tr>
                    <th colspan="5" class="text-right">Grand Total</th>
                    <th>{{ number_format($total,2) }}</th>
                </tr>
            </tfoot>
        </table>
        <div class="text-right">
            <a href="{{ url()->previous() }}" class="btn btn-default">Back</a>
            <button class="btn btn-primary">Create RFQ</button>
        </div>
        </form>
    </div>
</body>
</html>
