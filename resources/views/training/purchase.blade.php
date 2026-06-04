@extends('welcome')
@section('content')

<style>
  @import url('https://fonts.googleapis.com/css2?family=DM+Sans:wght@400;500;600;700;800&display=swap');

  :root {
    --rd-surface: #1e1e2d;
    --rd-surface2: #2b2b40;
    --rd-border: #323248;
    --rd-border2: #3f4254;
    --rd-accent: #0095e8;
    --rd-accent-soft: rgba(0, 149, 232, 0.1);
    --rd-text1: #ffffff;
    --rd-text2: #a2a3b7;
    --rd-text3: #7e8299;
    --rd-bg: #151521;
    --rd-success: #50cd89;
    --rd-success-soft: rgba(80, 205, 137, 0.1);
    --rd-danger: #f1416c;
    --rd-danger-soft: rgba(241, 65, 108, 0.1);
  }

  .sinc-page {
    display: flex;
    flex-direction: column;
    height: calc(100vh - 57px);
    background: var(--rd-bg);
    font-family: 'DM Sans', sans-serif;
    overflow: hidden;
  }

  .sinc-navbar {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 0 24px;
    height: 52px;
    background: var(--rd-surface);
    border-bottom: 1px solid var(--rd-border);
    flex-shrink: 0;
  }

  .sinc-nav-title {
    font-size: .95rem;
    font-weight: 800;
    color: var(--rd-text1);
    display: flex;
    align-items: center;
    gap: 8px;
  }

  .nt-icon {
    width: 30px; height: 30px;
    border-radius: 8px;
    background: var(--rd-accent-soft);
    color: var(--rd-accent);
    display: flex; align-items: center; justify-content: center;
  }

  .sinc-panels {
    display: grid;
    grid-template-columns: 400px 1fr 400px;
    flex: 1;
    overflow: hidden;
  }

  .panel {
    display: flex;
    flex-direction: column;
    overflow: hidden;
    background: var(--rd-bg);
    border-right: 1px solid var(--rd-border);
  }

  .panel-head {
    padding: 14px 18px;
    background: var(--rd-surface);
    border-bottom: 1px solid var(--rd-border);
    display: flex;
    align-items: center;
    justify-content: space-between;
  }

  .panel-head-title {
    font-size: .85rem;
    font-weight: 700;
    color: var(--rd-text1);
    display: flex;
    align-items: center;
    gap: 8px;
  }

  .panel-body {
    flex: 1;
    overflow-y: auto;
    padding: 15px;
  }

  /* Catalog Items */
  .catalog-item {
    padding: 12px;
    background: var(--rd-surface);
    border: 1px solid var(--rd-border);
    border-radius: 10px;
    margin-bottom: 10px;
    cursor: pointer;
    transition: all 0.2s;
  }

  .catalog-item:hover { border-color: var(--rd-accent); background: var(--rd-surface2); }
  .catalog-item.active { border-color: var(--rd-accent); background: var(--rd-accent-soft); }

  .item-meta { font-size: 0.7rem; color: var(--rd-text3); text-uppercase: uppercase; font-weight: 700; }
  .item-name { font-size: 0.85rem; font-weight: 700; color: var(--rd-text1); margin: 2px 0; }
  .item-price { font-size: 0.75rem; color: var(--rd-success); font-weight: 600; }

  /* Config Card */
  .config-card {
    background: var(--rd-surface);
    border-radius: 15px;
    padding: 25px;
    border: 1px solid var(--rd-border);
  }

  .form-group-custom label {
    display: block;
    font-size: 0.7rem;
    font-weight: 800;
    color: var(--rd-text3);
    text-transform: uppercase;
    margin-bottom: 8px;
  }

  .input-custom {
    width: 100%;
    background: var(--rd-surface2);
    border: 1px solid var(--rd-border);
    border-radius: 8px;
    padding: 10px 12px;
    color: var(--rd-text1);
    font-weight: 600;
    outline: none;
  }

  .input-custom:focus { border-color: var(--rd-accent); }

  /* Queue */
  .queue-item {
    background: var(--rd-surface);
    border-bottom: 1px solid var(--rd-border);
    padding: 12px 15px;
    display: flex;
    justify-content: space-between;
    align-items: center;
  }

  .qi-info .qi-name { font-size: 0.8rem; font-weight: 700; color: var(--rd-text1); }
  .qi-info .qi-sub { font-size: 0.7rem; color: var(--rd-text3); }
  .qi-price { font-size: 0.85rem; font-weight: 700; color: var(--rd-accent); }

  .total-bar {
    background: var(--rd-accent);
    padding: 20px;
    color: #fff;
  }

  .btn-confirm {
    background: #fff;
    color: var(--rd-accent);
    border: none;
    font-weight: 800;
    width: 100%;
    padding: 12px;
    border-radius: 10px;
    margin-top: 15px;
  }

  .btn-add {
    background: var(--rd-accent);
    color: #fff;
    border: none;
    font-weight: 700;
    width: 100%;
    padding: 12px;
    border-radius: 10px;
    margin-top: 20px;
  }
</style>

<div class="content-wrapper p-0">
  <div class="sinc-page">
    
    <div class="sinc-navbar">
      <div class="sinc-nav-title">
        <span class="nt-icon"><i class="fas fa-graduation-cap"></i></span>
        Training Purchase Case
      </div>
      <div>
        <a href="{{ route('training.create') }}" class="btn btn-outline-light btn-sm"><i class="fas fa-arrow-left mr-1"></i> Back</a>
      </div>
    </div>

    <div class="sinc-panels">
      
      <!-- Left: Catalog -->
      <div class="panel">
        <div class="panel-head">
          <div class="panel-head-title"><i class="fas fa-th-large"></i> Item Catalog</div>
        </div>
        <div class="panel-body">
          <div class="form-group mb-3">
            <input type="text" class="input-custom" placeholder="Search training items...">
          </div>
          @foreach($items as $item)
          <div class="catalog-item" data-id="{{ $item->id }}" data-title="{{ $item->title }}" data-price="{{ $item->last_price }}">
            <div class="item-meta">{{ $item->category }}</div>
            <div class="item-name">{{ $item->title }}</div>
            <div class="item-price">Est. PKR {{ number_format($item->last_price) }}</div>
          </div>
          @endforeach
        </div>
      </div>

      <!-- Center: Config -->
      <div class="panel">
        <div class="panel-head" style="background:#1b1b28">
          <div class="panel-head-title"><i class="fas fa-cog"></i> Configuration</div>
        </div>
        <div class="panel-body d-flex align-items-center justify-content-center">
          <div class="config-card w-100" style="max-width: 500px;">
            <div id="no-item-selected" class="text-center py-5">
              <i class="fas fa-hand-pointer fa-3x text-muted mb-3"></i>
              <p class="text-muted">Select an item from the catalog to configure</p>
            </div>

            <div id="config-form" class="d-none">
              <h5 id="selected-item-name" class="font-weight-bold text-white mb-4"></h5>
              
              <div class="row">
                <div class="col-md-6 mb-3">
                  <div class="form-group-custom">
                    <label>Quantity</label>
                    <input type="number" id="inp-qty" class="input-custom" value="1" min="1">
                  </div>
                </div>
                <div class="col-md-6 mb-3">
                  <div class="form-group-custom">
                    <label>Unit Price (PKR)</label>
                    <input type="number" id="inp-price" class="input-custom">
                  </div>
                </div>
                <div class="col-md-12 mb-3">
                  <div class="form-group-custom">
                    <label>Justification / Purpose</label>
                    <textarea id="inp-just" class="input-custom" rows="4" placeholder="Why is this required for training?"></textarea>
                  </div>
                </div>
              </div>

              <button class="btn-add" id="btn-add-to-queue">
                <i class="fas fa-plus-circle mr-2"></i> ADD TO PROCUREMENT QUEUE
              </button>
            </div>
          </div>
        </div>
      </div>

      <!-- Right: Queue -->
      <div class="panel" style="border-right:none">
        <div class="panel-head">
          <div class="panel-head-title"><i class="fas fa-list-ul"></i> Purchase Queue</div>
          <span class="badge badge-info" id="queue-count">0 Items</span>
        </div>
        <div class="panel-body p-0" id="queue-container">
          <div class="text-center py-5 text-muted" id="empty-queue-msg">
            <i class="fas fa-shopping-cart fa-2x mb-2"></i>
            <p>Queue is empty</p>
          </div>
        </div>
        
        <div class="total-bar">
          <div class="d-flex justify-content-between align-items-center mb-1">
            <span style="font-size:0.75rem; font-weight:700; text-transform:uppercase; opacity:0.8">Grand Total</span>
            <span style="font-size:0.75rem; opacity:0.8">PKR</span>
          </div>
          <div class="h3 font-weight-bold mb-0" id="grand-total">0.00</div>
          
          <button class="btn-confirm" id="btn-submit-case">
            <i class="fas fa-check-double mr-2"></i> SUBMIT PURCHASE CASE
          </button>
        </div>
      </div>

    </div>
  </div>
</div>

@endsection

@section('scripts')
<script>
$(document).ready(function() {
  let queue = [];
  let selectedItem = null;

  $('.catalog-item').on('click', function() {
    $('.catalog-item').removeClass('active');
    $(this).addClass('active');
    
    selectedItem = {
      id: $(this).data('id'),
      title: $(this).data('title'),
      price: $(this).data('price')
    };

    $('#no-item-selected').addClass('d-none');
    $('#config-form').removeClass('d-none');
    $('#selected-item-name').text(selectedItem.title);
    $('#inp-price').val(selectedItem.price);
  });

  $('#btn-add-to-queue').on('click', function() {
    if(!selectedItem) return;

    let qty = parseFloat($('#inp-qty').val());
    let price = parseFloat($('#inp-price').val());
    let justification = $('#inp-just').val();

    if(qty <= 0 || price <= 0) {
      alert("Please enter valid quantity and price");
      return;
    }

    queue.push({
      ...selectedItem,
      qty,
      price,
      justification
    });

    renderQueue();
    resetForm();
  });

  function renderQueue() {
    let html = '';
    let total = 0;
    
    if(queue.length === 0) {
      $('#empty-queue-msg').removeClass('d-none');
      $('#queue-container').html('');
    } else {
      $('#empty-queue-msg').addClass('d-none');
      queue.forEach((item, index) => {
        let lineTotal = item.qty * item.price;
        total += lineTotal;
        html += `
          <div class="queue-item">
            <div class="qi-info">
              <div class="qi-name">${item.title}</div>
              <div class="qi-sub">Qty: ${item.qty} x ${item.price.toLocaleString()}</div>
            </div>
            <div class="qi-price">PKR ${lineTotal.toLocaleString()}</div>
            <button class="btn btn-link text-danger btn-sm p-0 ml-2 remove-item" data-index="${index}">
              <i class="fas fa-times"></i>
            </button>
          </div>
        `;
      });
      $('#queue-container').html(html);
    }

    $('#grand-total').text(total.toLocaleString(undefined, {minimumFractionDigits: 2}));
    $('#queue-count').text(queue.length + " Items");

    $('.remove-item').on('click', function() {
      let index = $(this).data('index');
      queue.splice(index, 1);
      renderQueue();
    });
  }

  function resetForm() {
    $('#config-form').addClass('d-none');
    $('#no-item-selected').removeClass('d-none');
    $('.catalog-item').removeClass('active');
    $('#inp-qty').val(1);
    $('#inp-just').val('');
    selectedItem = null;
  }

  $('#btn-submit-case').on('click', function() {
    if(queue.length === 0) {
      alert("Please add items to queue first");
      return;
    }

    if(!confirm("Are you sure you want to submit this purchase case?")) return;

    $.ajax({
      url: "{{ route('training.purchase.store') }}",
      method: "POST",
      data: {
        _token: "{{ csrf_token() }}",
        items: queue
      },
      success: function(res) {
        if(res.success) {
          alert(res.message);
          window.location.href = "{{ route('training.index') }}";
        }
      }
    });
  });
});
</script>
@endsection
