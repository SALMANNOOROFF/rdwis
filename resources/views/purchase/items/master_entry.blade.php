@extends('welcome')

@section('content')
<div class="content-wrapper bg-light">
    <style>
        .master-card { border-top: 5px solid #007bff; border-radius: 8px; box-shadow: 0 4px 10px rgba(0,0,0,0.1); }
        label { font-family: Arial; font-size: 12pt; font-weight: 700; color: #444; }
        .form-control-custom { border-radius: 6px; border: 1px solid #ced4da; padding: 10px; font-size: 12pt; width: 100%; }
        .form-control-custom:focus { border-color: #007bff; outline: none; box-shadow: 0 0 0 3px rgba(0,123,255,0.1); }
    </style>

    <div class="container-fluid pt-4">
        <div class="row justify-content-center">
            <div class="col-md-6">
                
                @if(session('success'))
                    <div class="alert alert-success border-0 shadow-sm">{{ session('success') }}</div>
                @endif

                <div class="card master-card">
                    <div class="card-header bg-white py-3">
                        <h3 class="card-title font-weight-bold text-primary">Add Master Item</h3>
                    </div>

                    <div class="card-body p-4">
                        <form action="{{ route('items.master.store') }}" method="POST">
                            @csrf
                            
                            <div class="form-group mb-4 position-relative">
    <label>Item Name</label>
    <input type="text" name="name" id="item_name" class="form-control-custom" placeholder="e.g. Laptop, Printer, Stationary" required autocomplete="off">
    
    <div id="suggestions" class="bg-white border position-absolute w-100" style="z-index: 1000; display: none;"></div>
</div>


                            <div class="form-group mb-4 d-flex align-items-center">
                                <input type="checkbox" name="is_active" id="is_active" checked style="width: 20px; height: 20px;">
                                <label for="is_active" class="ml-2 mb-0">Is Active?</label>
                            </div>

                            <div class="text-right">
                                <button type="submit" class="btn btn-primary px-5 shadow font-weight-bold">
                                    <i class="fas fa-save mr-1"></i> SAVE MASTER ITEM
                                </button>
                            </div>
                        </form>
                    </div>
                </div>

                <!-- List of Master Items -->
                <div class="mt-5">
                    <h5 class="font-weight-bold text-muted">Master Items List</h5>
                    <div class="table-responsive bg-white rounded shadow-sm border">
                        <table class="table table-hover mb-0" style="font-family: Arial;">
                            <thead class="bg-light">
                                <tr>
                                    <th>ID</th>
                                    <th>Item Name</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($masterItems as $mItem)
                                <tr>
                                    <td>{{ $mItem->id }}</td>
                                    <td class="font-weight-bold">{{ $mItem->name }}</td>
                                    <td>
                                        <span class="badge {{ $mItem->is_active ? 'badge-success' : 'badge-danger' }}">
                                            {{ $mItem->is_active ? 'Active' : 'Inactive' }}
                                        </span>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>

            </div>
        </div>
    </div>
</div>
@endsection

<script>
    document.addEventListener('DOMContentLoaded', function() {
    const input = document.getElementById('item_name');
    const suggestionsBox = document.getElementById('suggestions');

    input.addEventListener('input', function() {
        const query = this.value.trim();
        if(query.length < 2) {
            suggestionsBox.style.display = 'none';
            return;
        }

        fetch(`/items/check-name?q=${query}`)
            .then(res => res.json())
            .then(data => {
                if(data.length === 0) {
                    suggestionsBox.style.display = 'none';
                    return;
                }

                let html = '<ul class="list-unstyled mb-0">';
                data.forEach(item => {
                    html += `<li class="p-2 border-bottom suggestion-item" style="cursor:pointer;" data-name="${item.name}">Do you mean: <strong>${item.name}</strong>?</li>`;
                });
                html += '</ul>';

                suggestionsBox.innerHTML = html;
                suggestionsBox.style.display = 'block';

                document.querySelectorAll('.suggestion-item').forEach(el => {
                    el.addEventListener('click', function() {
                        input.value = this.dataset.name;
                        suggestionsBox.style.display = 'none';
                    });
                });
            });
    });

    // Hide suggestions when clicking outside
    document.addEventListener('click', function(e) {
        if(!input.contains(e.target) && !suggestionsBox.contains(e.target)) {
            suggestionsBox.style.display = 'none';
        }
    });
});

</script>