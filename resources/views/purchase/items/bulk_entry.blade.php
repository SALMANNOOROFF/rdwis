@extends('welcome')

@section('content')
<!-- OFFLINE CSS (Paths from standard AdminLTE structure) -->
<link rel="stylesheet" href="{{ asset('plugins/select2/css/select2.min.css') }}">
<link rel="stylesheet" href="{{ asset('plugins/select2-bootstrap4-theme/select2-bootstrap4.min.css') }}">

<div class="content-wrapper bg-white px-3">
    <div class="container-fluid pt-4">
        
        <!-- Alerts -->
        @if(session('success'))
            <div class="alert alert-success border-0 shadow-sm">{{ session('success') }}</div>
        @endif

        <form action="{{ route('items.bulk.store') }}" method="POST" id="bulkForm">
            @csrf
            
            <!-- HEADER SECTION -->
            <div class="card shadow-sm mb-4" style="border-top: 5px solid #007bff; border-radius: 8px; font-family: Arial;">
                <div class="card-header bg-white">
                    <h3 class="card-title font-weight-bold text-primary">Batch Entry Form</h3>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-8 mb-3">
                            <label style="font-weight: bold; font-size: 12pt;">Group Title / Demand Name</label>
                            <input type="text" name="group_title" class="form-control" placeholder="Enter Batch Name" required style="font-size: 12pt; height: 40px;">
                        </div>
                        <div class="col-md-4 mb-3">
                            <label style="font-weight: bold; font-size: 12pt;">Entry Date</label>
                            <input type="date" name="group_date" class="form-control" value="{{ date('Y-m-d') }}" style="font-size: 12pt; height: 40px;">
                        </div>
                    </div>
                </div>
            </div>

            <!-- ITEMS TABLE -->
            <div class="card border-0 shadow-sm" style="font-family: Arial;">
                <div class="card-header bg-light d-flex justify-content-between align-items-center">
                    <h5 class="mb-0 font-weight-bold text-secondary">Items Collection</h5>
                    <button type="button" class="btn btn-primary btn-sm shadow" onclick="addRow()">
                        <i class="fas fa-plus"></i> Add Another Item
                    </button>
                </div>
                <div class="card-body p-0">
                    <table class="table table-bordered mb-0" id="itemsTable">
                        <thead class="bg-light">
                            <tr>
                                <th style="width: 60px;" class="text-center">#</th>
                                <th>Select Item (Searchable Dropdown)</th>
                                <th style="width: 150px;">Quantity</th>
                                <th style="width: 50px;"></th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr class="item-row">
                                <td class="text-center align-middle font-weight-bold">1</td>
                                <td>
                                    <select name="items[0][item_id]" class="form-control item-select" required>
                                        <option value="">-- Click to search items --</option>
                                        @foreach($masterItems as $mi)
                                            <option value="{{ $mi->id }}">{{ $mi->name }}</option>
                                        @endforeach
                                    </select>
                                    <!-- Hidden field to pass description name to backend -->
                                    <input type="hidden" name="items[0][desc]" class="item-name-hidden">
                                </td>
                                <td><input type="number" name="items[0][qty]" class="form-control text-center" placeholder="Qty" required min="1" style="font-size: 12pt;"></td>
                                <td class="text-center"></td>
                            </tr>
                        </tbody>
                    </table>
                </div>
                <div class="card-footer bg-white text-right py-3">
                    <button type="submit" class="btn btn-success px-5 font-weight-bold shadow-lg" style="font-size: 12pt;">
                        <i class="fas fa-check-circle mr-1"></i> SAVE BATCH
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>

<!-- OFFLINE SCRIPTS -->
<script src="{{ asset('plugins/jquery/jquery.min.js') }}"></script>
<script src="{{ asset('plugins/select2/js/select2.full.min.js') }}"></script>

<script>
    let rowIdx = 1;

    $(document).ready(function() {
        initSelect2($('.item-select'));
    });

  function initSelect2(element) {
    element.select2({
        theme: 'bootstrap4',
        placeholder: "Search items...",
        allowClear: true,
        width: '100%',
        minimumResultsForSearch: 0
    }).on('select2:select', function (e) {
        // Correct way to find the hidden input in the same table cell
        let selectedText = e.params.data.text;
        $(this).closest('td').find('.item-name-hidden').val(selectedText);
        
        refreshOptions(); // Duplicate check logic
    });
}

// Add New Row (can specify after which row)
function addRow(afterRow = null) {
    let table = $('#itemsTable tbody');
    let uniqueIdx = Date.now(); // unique index for form names

    let newRow = `
        <tr class="item-row">
            <td class="text-center align-middle font-weight-bold">#</td>
            <td>
                <select name="items[${uniqueIdx}][item_id]" class="form-control item-select" required>
                    <option value="">-- Click or Type to Search --</option>
                    @foreach($masterItems as $mi)
                        <option value="{{ $mi->id }}">{{ $mi->name }}</option>
                    @endforeach
                </select>
                <input type="hidden" name="items[${uniqueIdx}][desc]" class="item-name-hidden">
            </td>
            <td>
                <input type="number" name="items[${uniqueIdx}][qty]" class="form-control text-center" placeholder="Qty" required min="1">
            </td>
            <td class="text-center align-middle">
                <button type="button" class="btn btn-link text-success p-0 mr-2" onclick="addRow($(this).closest('tr'))">
                    <i class="fas fa-plus"></i>
                </button>
                <button type="button" class="btn btn-link text-danger p-0" onclick="removeRow(this)">
                    <i class="fas fa-trash"></i>
                </button>
            </td>
        </tr>
    `;

    if (afterRow) {
        $(newRow).insertAfter(afterRow); // insert after the current row
    } else {
        table.append(newRow); // append at the end if no row specified
    }

    // Initialize select2 for the newly added select
    initSelect2(table.find('.item-select').last());
}

// Remove row function
function removeRow(btn) {
    $(btn).closest('tr').remove();
}


    function removeRow(btn) {
        $(btn).closest('tr').remove();
        refreshOptions();
    }

    // 3. NO-DUPLICATE LOGIC: Disable already selected items
    function refreshOptions() {
        let selectedIDs = [];

        // Collect all selected IDs
        $('.item-select').each(function() {
            let val = $(this).val();
            if (val) selectedIDs.push(val);
        });

        // Loop through every dropdown and disable options that are selected elsewhere
        $('.item-select').each(function() {
            let currentDropdown = $(this);
            let currentVal = currentDropdown.val();

            currentDropdown.find('option').each(function() {
                let optionVal = $(this).val();
                if (optionVal !== "" && selectedIDs.includes(optionVal) && optionVal !== currentVal) {
                    $(this).prop('disabled', true);
                } else {
                    $(this).prop('disabled', false);
                }
            });

            // Refresh Select2 UI to show items as disabled in search results
            currentDropdown.select2({
                theme: 'bootstrap4',
                width: '100%',
                minimumResultsForSearch: 0
            });
        });
    }
</script>
@endsection