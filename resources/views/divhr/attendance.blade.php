@extends('welcome')
@section('content')
<div class="content-wrapper p-3">
  <div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-3">
      <h4 class="mb-0 text-primary"><i class="fas fa-user-check mr-2"></i>Attendance (Division)</h4>
      <form method="get" action="{{ route('divhr.attendance') }}" class="form-inline">
        <input type="month" name="month" value="{{ $month }}" class="form-control form-control-sm mr-2">
        <button class="btn btn-sm btn-outline-primary mr-2">Go</button>
        <button type="button" class="btn btn-sm btn-success" id="btn-save">Save</button>
      </form>
    </div>
    <div class="card">
      <div class="card-header d-flex justify-content-between align-items-center">
        <div>From {{ $first }} to {{ $last }}</div>
        <div class="text-muted">Days {{ $days }}</div>
      </div>
      <div class="card-body p-0 table-responsive">
        <table class="table table-sm table-bordered mb-0">
          <thead class="thead-light">
            <tr>
              <th style="width:60px">#</th>
              <th style="min-width:200px">Employee</th>
@for($d=1;$d<=$days;$d++)
              <th class="text-center" style="width:32px">{{ $d }}</th>
@endfor
              <th style="width:90px" class="text-center">%</th>
            </tr>
            <tr>
              <th></th>
              <th></th>
@for($d=1;$d<=$days;$d++)
              <th class="text-center text-muted" style="font-weight:normal">{{ $weekdays[$d] }}</th>
@endfor
              <th></th>
            </tr>
          </thead>
          <tbody id="att-body">
@forelse($list as $i=>$row)
            <tr>
              <td>{{ $i+1 }}</td>
              <td data-emp="{{ $row['emp_id'] }}">{{ $row['name'] }}</td>
@for($d=1;$d<=$days;$d++)
@php $v = $row['vals'][$d] ?? null; @endphp
              <td class="text-center">
                <select class="form-control form-control-sm att" data-day="{{ $d }}">
                  <option value="" {{ empty($v)?'selected':'' }}></option>
                  <option value="P" {{ $v==='P'?'selected':'' }}>P</option>
                  <option value="A" {{ $v==='A'?'selected':'' }}>A</option>
                  <option value="L" {{ $v==='L'?'selected':'' }}>L</option>
                  <option value="CL" {{ $v==='C'?'selected':'' }}>CL</option>
                </select>
              </td>
@endfor
              <td class="text-center">{{ $row['percent'] }}%</td>
            </tr>
@empty
            <tr><td colspan="{{ 2+$days+1 }}" class="text-center text-muted">No attendance</td></tr>
@endforelse
          </tbody>
        </table>
      </div>
    </div>
  </div>
  </div>
@endsection
@section('scripts')
<script>
  (function(){
    const changes = [];
    $('#att-body').on('change','select.att',function(){
      const td = $(this).closest('td');
      const tr = $(this).closest('tr');
      const emp = tr.find('td[data-emp]').data('emp');
      const day = parseInt($(this).data('day'));
      const val = $(this).val();
      changes.push({emp_id: String(emp), day: day, val: val});
    });
    $('#btn-save').on('click', function(){
      if (changes.length===0) { location.reload(); return; }
      const f = $('<form method="post" action="{{ route('divhr.attendance.save') }}"></form>');
      f.append('<input type="hidden" name="_token" value="{{ csrf_token() }}">');
      f.append('<input type="hidden" name="month" value="{{ $month }}">');
      f.append('<input type="hidden" name="payload_json" value=\''+JSON.stringify(changes)+'\'>');
      $('body').append(f);
      f.submit();
    });
  })();
  </script>
@endsection
