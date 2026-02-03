@extends('welcome')

@section('content')

<style>
/* PAGE WRAPPER (sidebar offset) */
.divhr-page{
    margin-left: 250px;   /* adjust if needed */
}

/* ALL STYLES SCOPED TO THIS PAGE ONLY */
.divhr-page .section{padding:20px}
.divhr-page .card{background:#fff;border-top:3px solid #007bff;border-radius:4px;box-shadow:0 2px 6px rgba(0,0,0,.08)}
.divhr-page .header{padding:15px 20px;display:flex;justify-content:space-between;align-items:center;border-bottom:1px solid #ddd}
.divhr-page .header h3{margin:0;font-size:18px;color:#333}
.divhr-page .tabs button{border:1px solid #007bff;background:none;color:#007bff;padding:6px 12px;font-size:13px;cursor:pointer;border-radius:3px}
.divhr-page .tabs .active{background:#007bff;color:#fff}
.divhr-page .body{padding:20px}
.divhr-page .tab{display:none}
.divhr-page .tab.active{display:block}
.divhr-page table{width:100%;border-collapse:collapse;font-size:14px}
.divhr-page th,
.divhr-page td{border:1px solid #dee2e6;padding:10px}
.divhr-page th{background:#f8f9fa;text-align:left}
.divhr-page tr:hover{background:#f1f1f1}
.divhr-page .badge{padding:4px 8px;font-size:12px;border-radius:3px;color:#fff}
.divhr-page .success{background:#28a745}
.divhr-page .danger{background:#dc3545}
.divhr-page .muted{color:#6c757d}
.divhr-page .btn{border:none;padding:6px 10px;font-size:13px;border-radius:3px;cursor:pointer;color:#fff}
.divhr-page .info{background:#007bff}
.divhr-page .secondary{background:#6c757d}
</style>

<div class="divhr-page">
  <div class="section">
    <div class="card">

      <div class="header">
        <h3>Employee Overview</h3>
        <div class="tabs">
          <button class="active" onclick="openTab('current',this)">Current</button>
          <button onclick="openTab('left',this)">Left</button>
        </div>
      </div>

      <div class="body">

        <!-- Current Employees -->
        <div id="current" class="tab active">
          <table>
            <tr>
              <th>S No</th><th>Name</th><th>Salary</th>
              <th>Contract Start</th><th>Contract End</th><th>Action</th>
            </tr>
            <tr>
              <td>1</td><td>Ali Khan</td><td>120,000 PKR</td>
              <td>01-01-2024</td>
              <td><span class="badge success">31-12-2024</span></td>
              <td><a href="{{ route('divhr.employeedetail', 1) }}"><button class="btn info">View</button></a></td>
            </tr>
          </table>
        </div>

        <!-- Left Employees -->
        <div id="left" class="tab">
          <table>
            <tr>
              <th>S No</th><th>Name</th><th>Last Salary</th>
              <th>Contract Start</th><th>Contract End</th><th>Action</th>
            </tr>
            <tr class="muted">
              <td>1</td><td>Ahmed Raza</td><td>95,000 PKR</td>
              <td>01-01-2023</td>
              <td><span class="badge danger">31-12-2023</span></td>
              <td><a href="{{ route('divhr.employeedetail', 1) }}"><button class="btn info">View</button></a></td>
            </tr>
          </table>
        </div>

      </div>
    </div>
  </div>
</div>

<script>
function openTab(id,btn){
  document.querySelectorAll('.divhr-page .tab').forEach(t=>t.classList.remove('active'));
  document.querySelectorAll('.divhr-page .tabs button').forEach(b=>b.classList.remove('active'));
  document.getElementById(id).classList.add('active');
  btn.classList.add('active');
}
</script>

@endsection
