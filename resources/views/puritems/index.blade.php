@php
    $asset = fn($p) => asset($p);
@endphp
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Items</title>
    <link rel="stylesheet" href="{{ $asset('dist/css/adminlte.min.css') }}">
    <script src="{{ $asset('plugins/jquery/jquery.min.js') }}"></script>
</head>
<body class="hold-transition sidebar-mini">
<div class="wrapper">
    <section class="content p-3">
        <div class="container-fluid">
            <div class="row">
                <div class="col-md-3">
                    <form method="get" action="{{ route('puritems.index') }}" class="mb-2">
                        <input type="text" name="term" value="{{ $filters['term'] ?? '' }}" class="form-control mb-2" placeholder="Search item title">
                        <select name="cat" class="form-control mb-2">
                            <option value="">Category</option>
                            @foreach($cats as $c)
                                <option value="{{ $c->cat_id }}" @if(($filters['cat'] ?? null)==$c->cat_id) selected @endif>{{ $c->cat_name }}</option>
                            @endforeach
                        </select>
                        <select name="sub" class="form-control mb-2">
                            <option value="">Subcategory</option>
                            @foreach($subs as $s)
                                <option value="{{ $s->sub_id }}" @if(($filters['sub'] ?? null)==$s->sub_id) selected @endif>{{ $s->sub_name }}</option>
                            @endforeach
                        </select>
                        <div class="form-check mb-2">
                            <input class="form-check-input" type="checkbox" id="mine" name="mine" value="1" @if(!empty($filters['mine'])) checked @endif>
                            <label class="form-check-label" for="mine">Only my items</label>
                        </div>
                        <div class="d-flex">
                            <button class="btn btn-primary mr-2">Filter</button>
                            <a href="{{ route('puritems.index') }}" class="btn btn-default">Reset</a>
                        </div>
                    </form>
                    <div class="card">
                        <div class="card-header d-flex justify-content-between align-items-center">
                            <span>Items</span>
                            <a class="btn btn-xs btn-outline-secondary" href="{{ route('puritems.rfq.list') }}">My RFQs</a>
                        </div>
                        <ul class="list-group list-group-flush" id="items-list" style="max-height:60vh;overflow:auto">
                            @foreach($items as $it)
                                @php $lp = $it->latestPrice; @endphp
                                <li class="list-group-item d-flex justify-content-between align-items-center">
                                    <span>{{ $it->itm_title }}</span>
                                    <button
                                      class="btn btn-xs btn-success add-to-group"
                                      data-id="{{ $it->itm_id }}"
                                      data-unit="{{ $it->itm_qtyunit }}"
                                      data-price="{{ $lp ? $lp->prc_gross : 0 }}"
                                      data-prc-id="{{ $lp ? $lp->prc_id : '' }}"
                                    >Add</button>
                                </li>
                            @endforeach
                        </ul>
                    </div>
                    <div class="mt-2 d-flex">
                        <form method="post" action="{{ route('puritems.setup') }}" class="mr-1">
                            @csrf
                            <button class="btn btn-secondary btn-sm">Setup Schema</button>
                        </form>
                        <form method="post" action="{{ route('puritems.populate') }}">
                            @csrf
                            <button class="btn btn-secondary btn-sm">Populate</button>
                        </form>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="card">
                        <div class="card-header d-flex justify-content-between align-items-center">
                            <span>Group</span>
                            <button class="btn btn-sm btn-primary" id="btn-preview">Proceed for quote</button>
                        </div>
                        <div class="card-body">
                            <table class="table table-sm" id="group-table">
                                <thead>
                                <tr>
                                    <th>Item</th>
                                    <th>Qty</th>
                                    <th>Unit</th>
                                    <th>Price</th>
                                    <th>Total</th>
                                    <th></th>
                                </tr>
                                </thead>
                                <tbody></tbody>
                                <tfoot>
                                <tr>
                                    <th colspan="4" class="text-right">Grand Total</th>
                                    <th id="grand-total">0</th>
                                    <th></th>
                                </tr>
                                </tfoot>
                            </table>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card">
                        <div class="card-header d-flex justify-content-between align-items-center">
                            <span>Add Item</span>
                            <button class="btn btn-xs btn-success" data-toggle="modal" data-target="#modal-add-item">Add</button>
                        </div>
                        <div class="card-body">
                            <p class="text-muted mb-0">Use the Add button to create a new item with price.</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
</div>
<form id="rfq-form" method="post" action="{{ route('puritems.rfq.preview') }}" target="_blank" style="display:none">
    @csrf
    <input type="hidden" name="items_json" id="items_json">
</form>
<script>
    const group = [];
    function renderGroup() {
        const tbody = $('#group-table tbody');
        tbody.empty();
        let total = 0;
        group.forEach((g,idx)=>{
            const line = (g.price||0) * (g.qty||1);
            total += line;
            const tr = $('<tr>');
            tr.append('<td>'+g.title+'</td>');
            tr.append('<td><input type="number" step="0.01" class="form-control form-control-sm qty" data-idx="'+idx+'" value="'+g.qty+'"></td>');
            tr.append('<td>'+g.unit+'</td>');
            tr.append('<td><input type="number" step="0.01" class="form-control form-control-sm price" data-idx="'+idx+'" value="'+(g.price||0)+'"></td>');
            tr.append('<td>'+line.toFixed(2)+'</td>');
            tr.append('<td><button class="btn btn-xs btn-danger rm" data-idx="'+idx+'">X</button></td>');
            tbody.append(tr);
        });
        $('#grand-total').text(total.toFixed(2));
    }
    $(document).on('click','.add-to-group',function(){
        const id = $(this).data('id');
        const price = parseFloat($(this).data('price'))||0;
        const unit = $(this).data('unit')||'unit';
        const title = $(this).closest('li').find('span').contents().filter(function(){ return this.nodeType===3; }).text().trim();
        group.push({itm_id:id,title:title,qty:1,price:price,unit:unit});
        renderGroup();
    });
    $(document).on('input','.qty',function(){
        const idx = $(this).data('idx');
        group[idx].qty = parseFloat($(this).val())||1;
        renderGroup();
    });
    $(document).on('input','.price',function(){
        const idx = $(this).data('idx');
        group[idx].price = parseFloat($(this).val())||0;
        renderGroup();
    });
    $(document).on('click','.rm',function(e){
        e.preventDefault();
        const idx = $(this).data('idx');
        group.splice(idx,1);
        renderGroup();
    });
    $('#btn-preview').on('click',function(){
        const items = group.map(g=>({itm_id:g.itm_id,qty:g.qty,price:g.price}));
        const f = $('<form method="post" action="{{ route('puritems.rfq.preview') }}" target="_blank"></form>');
        f.append('@csrf'.replace('@','<input type="hidden" name="_token" value="{{ csrf_token() }}">'));
        items.forEach((it,i)=>{
            f.append('<input type="hidden" name="items['+i+'][itm_id]" value="'+it.itm_id+'">');
            f.append('<input type="hidden" name="items['+i+'][qty]" value="'+it.qty+'">');
            if (it.price && it.price>0) {
                f.append('<input type="hidden" name="items['+i+'][price]" value="'+it.price+'">');
            }
        });
        $('body').append(f);
        f.submit();
        f.remove();
    });
</script>

<div class="modal fade" id="modal-add-item" tabindex="-1" role="dialog" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">Add Item</h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <form method="post" action="{{ route('puritems.item.create') }}" id="form-add-item">
      @csrf
      <div class="modal-body">
        <input class="form-control mb-2" name="title" placeholder="Title" required>
        <input class="form-control mb-2" name="desc" placeholder="Description">
        <div class="form-row">
            <div class="col">
                <input class="form-control mb-2" name="qtyunit" placeholder="Qty Unit" required>
            </div>
            <div class="col">
                <input class="form-control mb-2" name="base" type="number" step="0.01" placeholder="Base" required id="price-base">
            </div>
        </div>
        <div class="form-row">
            <div class="col">
                <input class="form-control mb-2" name="gst" type="number" step="0.01" placeholder="GST" id="price-gst">
            </div>
            <div class="col">
                <input class="form-control mb-2" name="sst" type="number" step="0.01" placeholder="SST" id="price-sst">
            </div>
        </div>
        <div class="alert alert-secondary py-1" id="price-gross" style="display:none"></div>
        <select class="form-control mb-2" name="cat_id">
            <option value="">Category</option>
            @foreach($cats as $c)
                <option value="{{ $c->cat_id }}">{{ $c->cat_name }}</option>
            @endforeach
        </select>
        <select class="form-control mb-2" name="sub_id">
            <option value="">Subcategory</option>
            @foreach($subs as $s)
                <option value="{{ $s->sub_id }}">{{ $s->sub_name }}</option>
            @endforeach
        </select>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
        <button class="btn btn-success">Save</button>
      </div>
      </form>
    </div>
  </div>
</div>

<script src="{{ $asset('plugins/bootstrap/js/bootstrap.bundle.min.js') }}"></script>
<script>
function updateGross(){
    const base = parseFloat($('#price-base').val()||0);
    const gst  = parseFloat($('#price-gst').val()||0);
    const sst  = parseFloat($('#price-sst').val()||0);
    const gross = (base + gst + sst).toFixed(2);
    $('#price-gross').text('Gross: '+gross).show();
}
$('#price-base,#price-gst,#price-sst').on('input', updateGross);

$('#form-add-item').on('submit', function(e){
    e.preventDefault();
    const $f = $(this);
    $.ajax({
        url: $f.attr('action'),
        method: 'POST',
        data: $f.serialize(),
        headers: {'X-Requested-With': 'XMLHttpRequest'},
        success: function(res){
            if(res && res.ok){
                const it = res.item;
                // push to group
                group.push({itm_id: it.itm_id, title: it.title, qty: 1, price: it.price, unit: it.qtyunit});
                renderGroup();
                // add to left list quickly
                const li = $('<li class="list-group-item d-flex justify-content-between align-items-center">\
                    <span>'+it.title+'</span>\
                    <button class="btn btn-xs btn-success add-to-group" data-id="'+it.itm_id+'" data-unit="'+it.qtyunit+'" data-price="'+it.price+'">Add</button>\
                </li>');
                $('#items-list').prepend(li);
                // reset + close
                $f[0].reset();
                $('#modal-add-item').modal('hide');
            }
        },
        error: function(xhr){
            alert('Failed to save item. Please check fields.');
        }
    });
});
</script>
</body>
</html>
