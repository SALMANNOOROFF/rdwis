@extends('welcome')

@section('content')
<section class="content" style="margin-left:250px; margin-top:10px;">
<div class="container-fluid">

<style>
  .profile-title { font-weight:600; }
  .nav-pills .nav-link {
    color:#fff;
    font-size:14px;
    border-radius:20px;
    padding:7px 14px;
  }
  .nav-pills .nav-link.active {
    background:#fff;
    color:#007bff;
    font-weight:600;
  }
  .card-body p {
    font-size:14px;
    margin-bottom:6px;
  }
  .info-label {
    font-size:13px;
    color:#6c757d;
  }
 .nav-pills .nav-link:not(.active):hover {
    color: white;
    }
</style>

<!-- HEADER -->
<div class="row mb-2 align-items-center">
  <div class="col">
    <h3 class="profile-title mb-0">Employee Profile</h3>
  </div>
  <div class="col text-right">
    <a href="{{ route('divhr.employelist') }}" class="btn btn-outline-secondary btn-sm mr-1">
      ← Back
    </a>
    <button class="btn btn-primary btn-sm">
      Edit Profile
    </button>
  </div>
</div>

<div class="row">

<!-- LEFT PROFILE -->
<div class="col-md-3">
  <div class="card card-outline card-primary">
    <div class="card-body text-center">

      <img src="{{ asset('dist/img/user2-160x160.jpg') }}"
           class="img-circle mb-2 shadow" width="105">

      <h6 class="mb-0 font-weight-bold">Ali Khan</h6>
      <small class="text-muted">Senior Software Engineer</small><br>
      <span class="badge badge-primary mt-1">Active</span>

      <hr>

      <p><span class="info-label">Employee ID</span><br><b>EMP-001</b></p>
      <p><span class="info-label">Rank</span><br><b>BPS-17</b></p>
      <p><span class="info-label">Joining Date</span><br><b>01-01-2024</b></p>

    </div>
  </div>
</div>

<!-- RIGHT CONTENT -->
<div class="col-md-9">

<div class="card">
  <div class="card-header bg-primary p-2">
    <ul class="nav nav-pills nav-fill">
      <li class="nav-item"><a class="nav-link active" data-toggle="tab" href="#personal">Personal</a></li>
      <li class="nav-item"><a class="nav-link" data-toggle="tab" href="#education">Education</a></li>
      <li class="nav-item"><a class="nav-link" data-toggle="tab" href="#career">Career</a></li>
      <li class="nav-item"><a class="nav-link" data-toggle="tab" href="#bank">Bank</a></li>
      <li class="nav-item"><a class="nav-link" data-toggle="tab" href="#official">Official</a></li>
      <li class="nav-item"><a class="nav-link" data-toggle="tab" href="#devices">Devices</a></li>
    </ul>
  </div>

<div class="card-body tab-content">

<!-- PERSONAL -->
<div class="tab-pane fade show active" id="personal">
  <div class="row">
    <div class="col-md-4"><b>CNIC</b><br>35202-1234567-1</div>
    <div class="col-md-4"><b>Gender</b><br>Male</div>
    <div class="col-md-4"><b>Marital</b><br>Married</div>
  </div>

  <div class="row mt-2">
    <div class="col-md-4"><b>Nationality</b><br>Pakistani</div>
    <div class="col-md-4"><b>Religion</b><br>Islam</div>
    <div class="col-md-4"><b>Caste</b><br>Khan</div>
  </div>

  <hr>
  <div class="row">
    <div class="col-md-6"><b>Father Name</b><br>Ahmed Khan</div>
    <div class="col-md-6"><b>Father CNIC</b><br>35202-9876543-1</div>
  </div>

  <hr>
  <div class="row">
    <div class="col-md-6"><b>Temporary Address</b><br>Lahore</div>
    <div class="col-md-6"><b>Permanent Address</b><br>Sheikhupura</div>
  </div>

  <hr>
  <div class="row">
    <div class="col-md-6"><b>Next of Kin</b><br>Ayesha Khan (Wife)</div>
    <div class="col-md-6"><b>Emergency Contact</b><br>0301-9999999</div>
  </div>
</div>

<!-- EDUCATION -->
<div class="tab-pane fade" id="education">

  <div class="row">
    <div class="col-md-4"><b>Degree</b><br>BS Computer Science</div>
    <div class="col-md-4"><b>Institution</b><br>University of Lahore</div>
    <div class="col-md-4"><b>Duration</b><br>2018 – 2022</div>
  </div>

  <div class="row mt-2">
    <div class="col-md-4"><b>GPA</b><br><span class="badge badge-primary">3.5</span></div>
    <div class="col-md-3"><b>Grade</b><br><span class="badge badge-info">A</span></div>
    <div class="col-md-3"><b>Major</b><br>Software Engineering</div>
  </div>

  <hr>
  <b>Certifications</b><br>
  <span class="badge badge-secondary mr-1">Laravel</span>
  <span class="badge badge-secondary mr-1">AWS Cloud</span>
  <span class="badge badge-secondary">REST APIs</span>

</div>

<!-- CAREER -->
<div class="tab-pane fade" id="career">
  <div class="row">
    <div class="col-md-4"><b>Company</b><br>ABC Tech</div>
    <div class="col-md-4"><b>Role</b><br>Software Engineer</div>
    <div class="col-md-4"><b>Period</b><br>2020 – 2022</div>
  </div>
  <hr>
  <div class="row">
    <div class="col-md-4"><b>Company</b><br>XYZ Pvt Ltd</div>
    <div class="col-md-4"><b>Role</b><br>Senior Engineer</div>
    <div class="col-md-4"><b>Period</b><br>2022 – Present</div>
  </div>
</div>

<!-- BANK -->
<div class="tab-pane fade" id="bank">
  <div class="row">
    <div class="col-md-4"><b>Bank</b><br>HBL</div>
    <div class="col-md-4"><b>Account Title</b><br>Ali Khan</div>
    <div class="col-md-4"><b>Account No</b><br>123456789</div>
  </div>
  <hr>
  <div class="row">
    <div class="col-md-4"><b>Branch</b><br>Gulberg</div>
    <div class="col-md-4"><b>Branch Code</b><br>0123</div>
    <div class="col-md-4"><b>City</b><br>Lahore</div>
  </div>
</div>

<!-- OFFICIAL -->
<div class="tab-pane fade" id="official">
  <div class="row">
    <div class="col-md-4"><b>Gate Card</b><br>GC-0098</div>
    <div class="col-md-4"><b>Issued</b><br>01-01-2024</div>
    <div class="col-md-4"><b>Expiry</b><br>31-12-2026</div>
  </div>
</div>

<!-- DEVICES -->
<div class="tab-pane fade" id="devices">
  <div class="row">
    <div class="col-md-4"><b>Device</b><br>Samsung Galaxy S21</div>
    <div class="col-md-4"><b>Model</b><br>S21</div>
    <div class="col-md-4"><b>IMEI</b><br>356789012345678</div>
  </div>
</div>

</div>
</div>

</div>
</div>

</div>
</section>
@endsection
