<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>RDWIS</title>

  <!-- Google Font: Source Sans Pro -->
  <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Source+Sans+Pro:300,400,400i,700&display=fallback">
  <!-- Font Awesome -->
  <link rel="stylesheet" href="plugins/fontawesome-free/css/all.min.css">
  <!-- Ionicons -->
  <link rel="stylesheet" href="https://code.ionicframework.com/ionicons/2.0.1/css/ionicons.min.css">
  <!-- Tempusdominus Bootstrap 4 -->
  <link rel="stylesheet" href="plugins/tempusdominus-bootstrap-4/css/tempusdominus-bootstrap-4.min.css">
  <!-- iCheck -->
  <link rel="stylesheet" href="plugins/icheck-bootstrap/icheck-bootstrap.min.css">
  <!-- JQVMap -->
  <link rel="stylesheet" href="plugins/jqvmap/jqvmap.min.css">
  <!-- Theme style -->
  <link rel="stylesheet" href="dist/css/adminlte.min.css">
  <!-- overlayScrollbars -->
  <link rel="stylesheet" href="plugins/overlayScrollbars/css/OverlayScrollbars.min.css">
  <!-- Daterange picker -->
  <link rel="stylesheet" href="plugins/daterangepicker/daterangepicker.css">
  <!-- summernote -->
  <link rel="stylesheet" href="plugins/summernote/summernote-bs4.min.css">
</head>
<body class="hold-transition sidebar-mini layout-fixed">
<div class="wrapper">

  <!-- Preloader -->
  <div class="preloader flex-column justify-content-center align-items-center">
    <img class="animation__shake" src="dist/img/withoutbgrdwlogofull.png" alt="Logoo" height="400px" width="300px">
  </div>

  <!-- Navbar -->
  <nav class="main-header navbar navbar-expand navbar-white navbar-light">
    <!-- Left navbar links -->
    <ul class="navbar-nav">
      <li class="nav-item">
        <a class="nav-link" data-widget="pushmenu" href="#" role="button"><i class="fas fa-bars"></i></a>
      </li>
      <li class="nav-item d-none d-sm-inline-block">
        <a href="{{route('index')}}" class="nav-link">Dashboard</a>
      </li>
      <!-- <li class="nav-item d-none d-sm-inline-block">
        <a href="#" class="nav-link">Contact</a>
      </li> -->
    </ul>

    <!-- Right navbar links -->
    <ul class="navbar-nav ml-auto">
      <!-- Navbar Search -->
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

      <!-- Messages Dropdown Menu -->
      <li class="nav-item dropdown">
        <a class="nav-link" data-toggle="dropdown" href="#">
          <i class="far fa-comments"></i>
          <span class="badge badge-danger navbar-badge">3</span>
        </a>
        <div class="dropdown-menu dropdown-menu-lg dropdown-menu-right">
          <a href="#" class="dropdown-item">
            <!-- Message Start -->
            <div class="media">
              <img src="dist/img/user1-128x128.jpg" alt="User Avatar" class="img-size-50 mr-3 img-circle">
              <div class="media-body">
                <h3 class="dropdown-item-title">
                  Brad Diesel
                  <span class="float-right text-sm text-danger"><i class="fas fa-star"></i></span>
                </h3>
                <p class="text-sm">Call me whenever you can...</p>
                <p class="text-sm text-muted"><i class="far fa-clock mr-1"></i> 4 Hours Ago</p>
              </div>
            </div>
            <!-- Message End -->
          </a>
          <div class="dropdown-divider"></div>
          <a href="#" class="dropdown-item">
            <!-- Message Start -->
            <div class="media">
              <img src="dist/img/user8-128x128.jpg" alt="User Avatar" class="img-size-50 img-circle mr-3">
              <div class="media-body">
                <h3 class="dropdown-item-title">
                  John Pierce
                  <span class="float-right text-sm text-muted"><i class="fas fa-star"></i></span>
                </h3>
                <p class="text-sm">I got your message bro</p>
                <p class="text-sm text-muted"><i class="far fa-clock mr-1"></i> 4 Hours Ago</p>
              </div>
            </div>
            <!-- Message End -->
          </a>
          <div class="dropdown-divider"></div>
          <a href="#" class="dropdown-item">
            <!-- Message Start -->
            <div class="media">
              <img src="dist/img/user3-128x128.jpg" alt="User Avatar" class="img-size-50 img-circle mr-3">
              <div class="media-body">
                <h3 class="dropdown-item-title">
                  Nora Silvester
                  <span class="float-right text-sm text-warning"><i class="fas fa-star"></i></span>
                </h3>
                <p class="text-sm">The subject goes here</p>
                <p class="text-sm text-muted"><i class="far fa-clock mr-1"></i> 4 Hours Ago</p>
              </div>
            </div>
            <!-- Message End -->
          </a>
          <div class="dropdown-divider"></div>
          <a href="#" class="dropdown-item dropdown-footer">See All Messages</a>
        </div>
      </li>
      <!-- Notifications Dropdown Menu -->
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
          <a href="#" class="dropdown-item">
            <i class="fas fa-users mr-2"></i> 8 friend requests
            <span class="float-right text-muted text-sm">12 hours</span>
          </a>
          <div class="dropdown-divider"></div>
          <a href="#" class="dropdown-item">
            <i class="fas fa-file mr-2"></i> 3 new reports
            <span class="float-right text-muted text-sm">2 days</span>
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
  <!-- /.navbar -->

    @yield('content')

  <!-- Main Sidebar Container -->
  <aside class="main-sidebar sidebar-dark-primary elevation-4">
    <!-- Brand Logo -->
    <a href="{{route('index')}}" class="brand-link">
      <img src="dist/img/logordwhalf.png" alt="AdminLTE Logo" class="brand-image img-circle elevation-3" style="opacity: .8">
      <span class="brand-text font-weight-light">RDWIS</span>
    </a>

    <!-- Sidebar -->
    <div class="sidebar">
      <!-- Sidebar user panel (optional) -->
      <div class="user-panel mt-3 pb-3 mb-3 d-flex">
        <div class="image">
          <!-- <img src="dist/img/user2-160x160.jpg" class="img-circle elevation-2" alt="User Image"> -->
        </div>
        <div class="info">
          <a href="#" class="d-block">DIRECTOR SINC</a>
        </div>
      </div>

      <!-- SidebarSearch Form -->
      <!-- <div class="form-inline">
        <div class="input-group" data-widget="sidebar-search">
          <input class="form-control form-control-sidebar" type="search" placeholder="Search" aria-label="Search">
          <div class="input-group-append">
            <button class="btn btn-sidebar">
              <i class="fas fa-search fa-fw"></i>
            </button>
          </div>
        </div>
      </div> -->

     <!-- Sidebar Menu -->
<nav class="mt-2">
<ul class="nav nav-pills nav-sidebar flex-column" data-widget="treeview" role="menu" data-accordion="false">

<!-- ================= PROJECTS ================= -->
<li class="nav-item">
  <a href="#" class="nav-link">
    <i class="nav-icon fas fa-project-diagram"></i>
    <p>PROJECTS <i class="right fas fa-angle-left"></i></p>
  </a>

      <ul class="nav nav-treeview">
        <li class="nav-item">
          <a href="{{route('viewprojects')}}" class="nav-link">
            <i class="fas fa-folder-open nav-icon"></i>
            <p>PROJECTS</p>
          </a>
        </li>
        
        <li class="nav-item">
          <a href="#" class="nav-link">
            <i class="fas fa-file-alt nav-icon"></i>
            <p>PREPARE MPR</p>
          </a>
        </li>
      </ul>
</li>

<!-- ================= PURCHASE CASES ================= -->
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
        <p>RECEIPTS</i></p>
      </a>

      <ul class="nav nav-treeview">
        <!-- Nested items removed -->
      </ul>
    </li>

    <!-- SIMPLE PAGE (NO DROPDOWN) -->
    <li class="nav-item">
      <a href="cases-without-items.php" class="nav-link">
        <i class="fas fa-exclamation-triangle nav-icon"></i>
        <p>CASES WITHOUT ITEMS</p>
      </a>
    </li>
  </ul>
</li>


<!-- ================= HUMAN RESOURCES ================= -->
<li class="nav-item">
  <a href="#" class="nav-link">
    <i class="nav-icon fas fa-users"></i>
    <p>HUMAN RESOURCES <i class="right fas fa-angle-left"></i></p>
  </a>

  <ul class="nav nav-treeview">

    <li class="nav-item">
      <a href="#" class="nav-link">
        <i class="fas fa-id-badge nav-icon"></i>
        <p>EMPLOYEES <i class="right fas fa-angle-left"></i></p>
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

  </ul>
</li>

<!-- ================= FINANCE ================= -->
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

<!-- ================= OTHER MODULES ================= -->
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
<!-- /.sidebar-menu -->

    </div>
    <!-- /.sidebar -->
  </aside>



  <footer class="main-footer">
    <strong>Copyright &copy; 2025 <a href="">RDWIS</a>.</strong>
    All rights reserved.
    <div class="float-right d-none d-sm-inline-block">
      <b>Version</b> 1.0.1
    </div>
  </footer>

  <!-- Control Sidebar -->
  <aside class="control-sidebar control-sidebar-dark">
    <!-- Control sidebar content goes here -->
  </aside>
  <!-- /.control-sidebar -->
</div>
<!-- ./wrapper -->

<!-- jQuery -->
<script src="plugins/jquery/jquery.min.js"></script>
<!-- jQuery UI 1.11.4 -->
<script src="plugins/jquery-ui/jquery-ui.min.js"></script>
<!-- Resolve conflict in jQuery UI tooltip with Bootstrap tooltip -->
<script>
  $.widget.bridge('uibutton', $.ui.button)
</script>
<!-- Bootstrap 4 -->
<script src="plugins/bootstrap/js/bootstrap.bundle.min.js"></script>
<!-- ChartJS -->
<script src="plugins/chart.js/Chart.min.js"></script>
<!-- Sparkline -->
<script src="plugins/sparklines/sparkline.js"></script>
<!-- JQVMap -->
<script src="plugins/jqvmap/jquery.vmap.min.js"></script>
<script src="plugins/jqvmap/maps/jquery.vmap.usa.js"></script>
<!-- jQuery Knob Chart -->
<script src="plugins/jquery-knob/jquery.knob.min.js"></script>
<!-- daterangepicker -->
<script src="plugins/moment/moment.min.js"></script>
<script src="plugins/daterangepicker/daterangepicker.js"></script>
<!-- Tempusdominus Bootstrap 4 -->
<script src="plugins/tempusdominus-bootstrap-4/js/tempusdominus-bootstrap-4.min.js"></script>
<!-- Summernote -->
<script src="plugins/summernote/summernote-bs4.min.js"></script>
<!-- overlayScrollbars -->
<script src="plugins/overlayScrollbars/js/jquery.overlayScrollbars.min.js"></script>
<!-- AdminLTE App -->
<script src="dist/js/adminlte.js"></script>
<!-- AdminLTE for demo purposes -->
<script src="dist/js/demo.js"></script>
<!-- AdminLTE dashboard demo (This is only for demo purposes) -->
<script src="dist/js/pages/dashboard.js"></script>
</body>
</html>
