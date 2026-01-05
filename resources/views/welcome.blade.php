<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>RDWIS</title>

  <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Source+Sans+Pro:300,400,400i,700&display=fallback">
  
  <link rel="stylesheet" href="{{ asset('plugins/fontawesome-free/css/all.min.css') }}">
  <link rel="stylesheet" href="https://code.ionicframework.com/ionicons/2.0.1/css/ionicons.min.css">
  <link rel="stylesheet" href="{{ asset('plugins/tempusdominus-bootstrap-4/css/tempusdominus-bootstrap-4.min.css') }}">
  <link rel="stylesheet" href="{{ asset('plugins/icheck-bootstrap/icheck-bootstrap.min.css') }}">
  <link rel="stylesheet" href="{{ asset('plugins/jqvmap/jqvmap.min.css') }}">
  <link rel="stylesheet" href="{{ asset('dist/css/adminlte.min.css') }}">
  <link rel="stylesheet" href="{{ asset('plugins/overlayScrollbars/css/OverlayScrollbars.min.css') }}">
  <link rel="stylesheet" href="{{ asset('plugins/daterangepicker/daterangepicker.css') }}">
  <link rel="stylesheet" href="{{ asset('plugins/summernote/summernote-bs4.min.css') }}">

  {{-- NOTE: Maine yahan se scripts hata diye hain, wo ab file ke end mein load honge --}}
</head>
<body class="hold-transition sidebar-mini layout-fixed">
<div class="wrapper">

  <div class="preloader flex-column justify-content-center align-items-center">
    <img class="animation__shake" src="{{ asset('dist/img/withoutbgrdwlogohalf.png') }}" alt="Logoo" height="400px" width="300px">
  </div>

  <nav class="main-header navbar navbar-expand navbar-white navbar-light">
    <ul class="navbar-nav">
      <li class="nav-item">
        <a class="nav-link" data-widget="pushmenu" href="#" role="button"><i class="fas fa-bars"></i></a>
      </li>
      <li class="nav-item d-none d-sm-inline-block">
        <a href="{{ route('dashboard') }}" class="nav-link">Dashboard</a>
      </li>
    </ul>

    <ul class="navbar-nav ml-auto">
      <li class="nav-item">
        <a class="nav-link" data-widget="navbar-search" href="#" role="button">
          <i class="fas fa-search"></i>
        </a>
        <div class="navbar-search-block">
          <form class="form-inline">
            <div class="input-group input-group-sm">
              <input class="form-control form-control-navbar" type="search" placeholder="Search" aria-label="Search">
              <div class="input-group-append">
                <button class="btn btn-navbar" type="submit">
                  <i class="fas fa-search"></i>
                </button>
                <button class="btn btn-navbar" type="button" data-widget="navbar-search">
                  <i class="fas fa-times"></i>
                </button>
              </div>
            </div>
          </form>
        </div>
      </li>

      <li class="nav-item dropdown">
        <a class="nav-link" data-toggle="dropdown" href="#">
          <i class="far fa-comments"></i>
          <span class="badge badge-danger navbar-badge">3</span>
        </a>
        <div class="dropdown-menu dropdown-menu-lg dropdown-menu-right">
          <a href="#" class="dropdown-item">
            <div class="media">
              <img src="{{ asset('dist/img/user1-128x128.jpg') }}" alt="User Avatar" class="img-size-50 mr-3 img-circle">
              <div class="media-body">
                <h3 class="dropdown-item-title">
                  Brad Diesel
                  <span class="float-right text-sm text-danger"><i class="fas fa-star"></i></span>
                </h3>
                <p class="text-sm">Call me whenever you can...</p>
                <p class="text-sm text-muted"><i class="far fa-clock mr-1"></i> 4 Hours Ago</p>
              </div>
            </div>
            </a>
          <div class="dropdown-divider"></div>
          <a href="#" class="dropdown-item dropdown-footer">See All Messages</a>
        </div>
      </li>
      
      <li class="nav-item dropdown">
        <a class="nav-link" data-toggle="dropdown" href="#">
          <i class="far fa-bell"></i>
          <span class="badge badge-warning navbar-badge">15</span>
        </a>
        <div class="dropdown-menu dropdown-menu-lg dropdown-menu-right">
          <span class="dropdown-item dropdown-header">15 Notifications</span>
          <div class="dropdown-divider"></div>
          <a href="#" class="dropdown-item">
            <i class="fas fa-envelope mr-2"></i> 4 new messages
            <span class="float-right text-muted text-sm">3 mins</span>
          </a>
          <div class="dropdown-divider"></div>
          <a href="#" class="dropdown-item dropdown-footer">See All Notifications</a>
        </div>
      </li>
      
      <li class="nav-item">
        <a class="nav-link" data-widget="fullscreen" href="#" role="button">
          <i class="fas fa-expand-arrows-alt"></i>
        </a>
      </li>
      <li class="nav-item">
        <a class="nav-link" data-widget="control-sidebar" data-controlsidebar-slide="true" href="#" role="button">
          <i class="fas fa-th-large"></i>
        </a>
      </li>
    </ul>
  </nav>
  @yield('content')

  <aside class="main-sidebar sidebar-dark-primary elevation-4">
    <a href="{{ route('view-projects') }}" class="brand-link">
      <img src="{{ asset('dist/img/withoutbgrdwlogohalf.png') }}" alt="AdminLTE Logo" class="brand-image img-circle elevation-3" style="opacity: .8">
      <span class="brand-text font-weight-light">RDWIS</span>
    </a>

    <div class="sidebar">
      <div class="user-panel mt-3 pb-3 mb-3 d-flex">
        <div class="image">
          </div>
        <div class="info">
          <a href="#" class="d-block">{{ Auth::user()->acc_desig ?? 'DIRECTOR SINC' }}</a>
        </div>
      </div>

      <nav class="mt-2">
        <ul class="nav nav-pills nav-sidebar flex-column" data-widget="treeview" role="menu" data-accordion="false">

        <li class="nav-item">
            <a href="{{ route('dashboard') }}" class="nav-link">
              <i class="nav-icon fas fa-tachometer-alt"></i>
              <p>Dashboard</p>
            </a>
        </li>

        <li class="nav-item">
            <a href="#" class="nav-link">
              <i class="nav-icon fas fa-project-diagram"></i>
              <p>PROJECTS <i class="right fas fa-angle-left"></i></p>
            </a>

            <ul class="nav nav-treeview">
              <li class="nav-item">
                <a href="{{ route('view-projects') }}" class="nav-link">
                  <i class="fas fa-folder-open nav-icon"></i>
                  <p>PROJECTS</p>
                </a>
              </li>
              
              <li class="nav-item">
                <a href="{{route('openmprs')}}" class="nav-link">
                  <i class="fas fa-file-alt nav-icon"></i>
                  <p>PREPARE MPR</p>
                </a>
              </li>
            </ul>
        </li>

        <li class="nav-item">
            <a href="#" class="nav-link">
              <i class="nav-icon fas fa-shopping-cart"></i>
              <p>PURCHASE CASES <i class="right fas fa-angle-left"></i></p>
            </a>
            <ul class="nav nav-treeview">
              <li class="nav-item">
                <a href="{{ route('viewpurchasecase') }}" class="nav-link">
                  <i class="fas fa-briefcase nav-icon"></i>
                  <p>PURCHASE CASES (PCs)</p>
                </a>
              </li>
              <li class="nav-item">
                <a href="#" class="nav-link">
                  <i class="fas fa-receipt nav-icon"></i>
                  <p>RECEIPTS</p>
                </a>
              </li>
              <li class="nav-item">
                <a href="cases-without-items.php" class="nav-link">
                  <i class="fas fa-exclamation-triangle nav-icon"></i>
                  <p>CASES WITHOUT ITEMS</p>
                </a>
              </li>
            </ul>
        </li>

        <li class="nav-item">
            <a href="#" class="nav-link">
              <i class="nav-icon fas fa-users"></i>
              <p>HUMAN RESOURCES <i class="right fas fa-angle-left"></i></p>
            </a>
            <ul class="nav nav-treeview">
              <li class="nav-item"><a href="#" class="nav-link"><i class="fas fa-user-check nav-icon"></i><p>CURRENT</p></a></li>
              <li class="nav-item"><a href="#" class="nav-link"><i class="fas fa-user-times nav-icon"></i><p>PREVIOUS</p></a></li>
              <li class="nav-item"><a href="#" class="nav-link"><i class="fas fa-calendar-check nav-icon"></i><p>ATTENDANCE</p></a></li>
            </ul>
        </li>

        <li class="nav-item">
            <a href="#" class="nav-link">
              <i class="fas fa-money-check-alt nav-icon"></i>
              <p>SALARY REQUISITIONS <i class="right fas fa-angle-left"></i></p>
            </a>
            <ul class="nav nav-treeview">
              <li class="nav-item"><a href="#" class="nav-link"><i class="fas fa-edit nav-icon"></i><p>DRAFT</p></a></li>
              <li class="nav-item"><a href="#" class="nav-link"><i class="fas fa-folder-open nav-icon"></i><p>OPEN</p></a></li>
              <li class="nav-item"><a href="#" class="nav-link"><i class="fas fa-lock nav-icon"></i><p>CLOSED</p></a></li>
            </ul>
        </li>

        <li class="nav-item">
            <a href="reports.php" class="nav-link">
              <i class="fas fa-chart-pie nav-icon"></i>
              <p>REPORTS</p>
            </a>
        </li>
        
        <li class="nav-item">
            <a href="#" class="nav-link">
              <i class="nav-icon fas fa-coins"></i>
              <p>FINANCE <i class="right fas fa-angle-left"></i></p>
            </a>
            <ul class="nav nav-treeview">
              <li class="nav-item"><a href="#" class="nav-link"><i class="fas fa-wallet nav-icon"></i><p>ACCOUNTS</p></a></li>
              <li class="nav-item"><a href="#" class="nav-link"><i class="fas fa-hand-holding-usd nav-icon"></i><p>LOANS</p></a></li>
              <li class="nav-item"><a href="#" class="nav-link"><i class="fas fa-chart-bar nav-icon"></i><p>PROJECT SHARES STATUS</p></a></li>
              <li class="nav-item"><a href="#" class="nav-link"><i class="fas fa-balance-scale nav-icon"></i><p>HR SUBHEAD STATUS</p></a></li>
              <li class="nav-item"><a href="#" class="nav-link"><i class="fas fa-university nav-icon"></i><p>CSCRF STATUS</p></a></li>
              <li class="nav-item"><a href="#" class="nav-link"><i class="fas fa-clock nav-icon"></i><p>PCs AWAITING PAYMENT</p></a></li>
            </ul>
        </li>

        <li class="nav-item">
            <a href="#" class="nav-link">
              <i class="nav-icon fas fa-building"></i>
              <p>FIRMS</p>
            </a>
        </li>
        <li class="nav-item">
            <a href="#" class="nav-link">
              <i class="nav-icon fas fa-boxes"></i>
              <p>INVENTORY</p>
            </a>
        </li>
        <li class="nav-item">
            <a href="#" class="nav-link">
              <i class="nav-icon fas fa-warehouse"></i>
              <p>ASSETS</p>
            </a>
        </li>
        <li class="nav-item">
            <a href="#" class="nav-link">
              <i class="nav-icon fas fa-share-alt"></i>
              <p>SHARED ASSETS</p>
            </a>
        </li>

        </ul>
      </nav>
      </div>
    </aside>

  <footer class="main-footer">
    <strong>Copyright &copy; 2025 <a href="">RDWIS</a>.</strong>
    All rights reserved.
    <div class="float-right d-none d-sm-inline-block">
      <b>Version</b> 1.0.1
    </div>
  </footer>

  <aside class="control-sidebar control-sidebar-dark">
    </aside>
  </div>
<script src="{{ asset('plugins/jquery/jquery.min.js') }}"></script>

<script src="{{ asset('plugins/jquery-ui/jquery-ui.min.js') }}"></script>

<script>
  $.widget.bridge('uibutton', $.ui.button)
</script>

<script src="{{ asset('plugins/bootstrap/js/bootstrap.bundle.min.js') }}"></script>

<script src="{{ asset('dist/js/adminlte.js') }}"></script>

<script src="{{ asset('plugins/chart.js/Chart.min.js') }}"></script>
<script src="{{ asset('plugins/sparklines/sparkline.js') }}"></script>
<script src="{{ asset('plugins/jqvmap/jquery.vmap.min.js') }}"></script>
<script src="{{ asset('plugins/jqvmap/maps/jquery.vmap.usa.js') }}"></script>
<script src="{{ asset('plugins/jquery-knob/jquery.knob.min.js') }}"></script>
<script src="{{ asset('plugins/moment/moment.min.js') }}"></script>
<script src="{{ asset('plugins/daterangepicker/daterangepicker.js') }}"></script>
<script src="{{ asset('plugins/tempusdominus-bootstrap-4/js/tempusdominus-bootstrap-4.min.js') }}"></script>
<script src="{{ asset('plugins/summernote/summernote-bs4.min.js') }}"></script>
<script src="{{ asset('plugins/overlayScrollbars/js/jquery.overlayScrollbars.min.js') }}"></script>

<script src="{{ asset('dist/js/demo.js') }}"></script>
<script src="{{ asset('dist/js/pages/dashboard.js') }}"></script>

{{-- Custom Scripts Section for individual pages (Modal logic yahan inject hoga) --}}
@yield('scripts')

</body>
</html>