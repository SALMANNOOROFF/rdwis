  <!DOCTYPE html>
  <html lang="en">
  <head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0">
    <title>RDWIS</title>

    <!-- PWA Setup -->
    <link rel="manifest" href="{{ asset('manifest.json') }}">
    <meta name="theme-color" content="#4f8cff">
    <link rel="apple-touch-icon" href="{{ asset('images/icons/icon-192.png') }}">

    <link rel="stylesheet" href="{{ asset('css/fonts.css') }}">
    
    <link rel="stylesheet" href="{{ asset('plugins/fontawesome-free/css/all.min.css') }}">
    <link rel="stylesheet" href="{{ asset('plugins/ionicons/ionicons.min.css') }}">
    <link rel="stylesheet" href="{{ asset('plugins/tempusdominus-bootstrap-4/css/tempusdominus-bootstrap-4.min.css') }}">
    <link rel="stylesheet" href="{{ asset('plugins/icheck-bootstrap/icheck-bootstrap.min.css') }}">
    <link rel="stylesheet" href="{{ asset('plugins/jqvmap/jqvmap.min.css') }}">
    <link rel="stylesheet" href="{{ asset('dist/css/adminlte.min.css') }}">
    <link rel="stylesheet" href="{{ asset('css/rdwis-dark.css') }}">
    <link rel="stylesheet" href="{{ asset('plugins/overlayScrollbars/css/OverlayScrollbars.min.css') }}">
    <link rel="stylesheet" href="{{ asset('plugins/daterangepicker/daterangepicker.css') }}">
    <link rel="stylesheet" href="{{ asset('plugins/summernote/summernote-bs4.min.css') }}">
    <link rel="stylesheet" href="{{ asset('css/zoom-scale.css') }}">
    <!-- SweetAlert2 -->
    <script src="{{ asset('plugins/sweetalert2/sweetalert2.all.min.js') }}"></script>

    <style>
        /* Global Responsiveness & Sidebar Toggle Fix */
        html, body {
            overflow-x: hidden;
            width: 100%;
            height: 100%;
        }

        .wrapper {
            overflow-x: hidden;
        }

        .content-wrapper {
            overflow-x: hidden;
            transition: margin-left .3s ease-in-out, width .3s ease-in-out;
        }

        @media (max-width: 991.98px) {
            .main-sidebar {
                box-shadow: 0 0 15px rgba(0,0,0,0.5) !important;
            }
        }

        /* Standardized Responsive Table Container */
        .rd-table-responsive {
            width: 100%;
            overflow-x: auto;
            -webkit-overflow-scrolling: touch;
            margin-bottom: 1rem;
            border-radius: 8px;
        }

        .rd-table-responsive::-webkit-scrollbar {
            height: 6px;
        }

        .rd-table-responsive::-webkit-scrollbar-thumb {
            background: var(--rd-border);
            border-radius: 10px;
        }

        /* ---- Original Sidebar Overrides ---- */
        .nav-header {
            color: var(--rd-text3) !important;
        }

        .user-panel .info a,
        .user-panel .info small {
            white-space: normal !important;
            overflow-wrap: anywhere;
            word-break: break-word;
        }

        .user-panel .info small {
            display: block;
        }
    </style>
  </head>

  <body class="hold-transition sidebar-mini layout-fixed {{ str_replace(['.', '/'], '-', Route::currentRouteName() ?? 'home') }}">
  <div class="wrapper">

    <div class="preloader flex-column justify-content-center align-items-center">
      <img class="animation__shake" src="{{ asset('dist/img/withoutbgrdwlogohalf.png') }}" alt="RDWIS Logo" height="200" width="150">
    </div>

    <nav class="main-header navbar navbar-expand navbar-white navbar-light">
      <ul class="navbar-nav">
        <li class="nav-item">
          <a class="nav-link" data-widget="pushmenu" href="#" role="button"><i class="fas fa-bars"></i></a>
        </li>
        {{-- Custom Back & Forward Buttons for PWA Navigation --}}
        <li class="nav-item">
          <a class="nav-link" href="javascript:void(0)" onclick="window.history.back();" title="Go Back">
            <i class="fas fa-chevron-left" style="font-size: 16px;"></i>
          </a>
        </li>
        <li class="nav-item mr-2">
          <a class="nav-link" href="javascript:void(0)" onclick="window.history.forward();" title="Go Forward">
            <i class="fas fa-chevron-right" style="font-size: 16px;"></i>
          </a>
        </li>
        <li class="nav-item d-none d-sm-inline-block">
          @if(Auth::check() && Auth::user()->isSORD())
              <a href="{{ route('sord.dashboard') }}" class="nav-link">Home (SORD)</a>
          @elseif(Auth::check() && strtolower(trim((string) (Auth::user()->acc_untarea ?? ''))) === 'it')
              <a href="{{ route('admin.dashboard') }}" class="nav-link">Home (Admin)</a>
          @else
              <a href="{{ route('dashboard') }}" class="nav-link">Home (Division)</a>
          @endif
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

        {{-- Dynamic Purchase Notifications --}}
        <li class="nav-item dropdown" id="notif-bell-container">
          <a class="nav-link" data-toggle="dropdown" href="#" id="pnt-bell">
            <i class="far fa-bell"></i>
            <span class="badge badge-warning navbar-badge d-none" id="pnt-count">0</span>
          </a>
          <div class="dropdown-menu dropdown-menu-lg dropdown-menu-right" id="pnt-dropdown">
            <span class="dropdown-item dropdown-header" id="pnt-header">Notifications</span>
            <div class="dropdown-divider"></div>
            <div id="pnt-list">
              <!-- Dynamically populated -->
              <div class="dropdown-item text-center text-muted">No new notifications</div>
            </div>
            <div class="dropdown-divider"></div>
            <a href="javascript:void(0)" class="dropdown-item dropdown-footer" id="pnt-mark-all">Mark all as read</a>
          </div>
        </li>
        
        <li class="nav-item">
          <a class="nav-link" data-widget="fullscreen" href="#" role="button">
            <i class="fas fa-expand-arrows-alt"></i>
          </a>
        </li>

        <li class="nav-item">
          <form action="{{ route('logout') }}" method="POST" class="d-inline">
              @csrf
              <button type="submit" class="nav-link btn btn-link text-danger">
                  <i class="fas fa-sign-out-alt"></i>
              </button>
          </form>
        </li>

      </ul>
    </nav>
    <aside class="main-sidebar sidebar-dark-primary elevation-4">
      <a href="#" class="brand-link">
        <img src="{{ asset('dist/img/newonelogo.png') }}" alt="RDWIS Logo" class="brand-image img-circle elevation-3" >
        <span class="brand-text font-weight-light">RDWIS</span>
      </a>

      <div class="sidebar">
        <div class="user-panel mt-3 pb-3 mb-3 d-flex">
          <div class="image">
            </div>
          <div class="info">
            @if(Auth::check())
                <a href="#" class="d-block">
                    {{ Auth::user()->acc_rank }} {{ Auth::user()->acc_name }}<br>
                    <small>{{ Auth::user()->acc_desig }} —<wbr> {{ Auth::user()->acc_untname }}</small>
                </a>
            @else
                <a href="#" class="d-block">Guest</a>
            @endif
          </div>
        </div>

        <nav class="mt-2">
          <ul class="nav nav-pills nav-sidebar flex-column" data-widget="treeview" role="menu" data-accordion="false">
          
      {{-- ========================================================= --}}
      {{-- CASE 1: SO R&D (Area: 'prjrdw') --}}
      {{-- ========================================================= --}}
      @if(Auth::user()->isSORD())

      <li class="nav-header">SOR & D MODULE</li>

      <li class="nav-item">
          <a href="{{ route('sord.dashboard') }}" class="nav-link {{ Request::routeIs('sord.dashboard') ? 'active' : '' }}">
              <i class="nav-icon fas fa-tachometer-alt"></i>
              <p>Dashboard</p>
          </a>
      </li>

      {{-- NEW: INBOX LINK --}}

      
      <li class="nav-item">
          <a href="{{ route('sord.all_projects') }}" class="nav-link {{ Request::routeIs('sord.all_projects') ? 'active' : '' }}">
              <i class="fas fa-layer-group nav-icon"></i>
              <p>All Projects</p>
          </a>
      </li>

      <li class="nav-item">
          <a href="#" class="nav-link">
              <i class="nav-icon fas fa-list-ol"></i>
              <p>Schedule of Rates</p>
          </a>
      </li>

      {{-- ========================================================= --}}
      {{-- CASE 2: AGAR USER 'DIVISION' KA HAI (Old Sidebar) --}}
      {{-- ========================================================= --}}
      @elseif(Auth::user()->isDivision())

          <li class="nav-item">
              <a href="{{ route('dashboard') }}" class="nav-link {{ Request::routeIs('dashboard') ? 'active' : '' }}">
                  <i class="nav-icon fas fa-tachometer-alt"></i>
                  <p>Dashboard</p>
              </a>
          </li>

          

          <li class="nav-item">
              <a href="{{ route('view-projects') }}" class="nav-link {{ Request::routeIs('view-projects*') ? 'active' : '' }}">
                  <i class="fas fa-folder-open nav-icon"></i>
                  <p>PROJECTS</p>
              </a>
          </li>

{{-- Hiding Old Purchase System as requested --}}
          {{-- 
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
                      <a href="{{ route('purchase.reports.index') }}" class="nav-link">
                          <i class="fas fa-file-alt nav-icon"></i>
                          <p>IT LETTER / CS</p>
                      </a>
                  </li>  
              </ul>
          </li>
          --}}

          <li class="nav-item {{ Request::routeIs('purchase.initiation.*') ? 'menu-open' : '' }}">
              <a href="#" class="nav-link {{ Request::routeIs('purchase.initiation.*') ? 'active' : '' }}">
                  <i class="nav-icon fas fa-shopping-cart"></i>
                  <p>PURCHASE CASES <i class="right fas fa-angle-left"></i></p>
              </a>
              <ul class="nav nav-treeview">
                  <li class="nav-item">
                      <a href="{{ route('purchase.initiation.index') }}" class="nav-link {{ Request::routeIs('purchase.initiation.*') ? 'active' : '' }}">
                          <i class="fas fa-list nav-icon"></i>
                          <p>VIEW ALL</p>
                      </a>
                  </li>
              </ul>
          </li>

          <li class="nav-item">
              <a href="{{ route('training.index') }}" class="nav-link {{ Request::routeIs('training.index') || Request::routeIs('training.create') ? 'active' : '' }}">
                  <i class="fas fa-chalkboard-teacher nav-icon"></i>
                  <p>TRAINING</p>
              </a>
          </li>

          <li class="nav-item">
              <a href="{{ route('training.books.index') }}" class="nav-link {{ Request::routeIs('training.books.*') ? 'active' : '' }}">
                  <i class="fas fa-book nav-icon"></i>
                  <p>BOOKS PROCUREMENT</p>
              </a>
          </li>

          <li class="nav-item">
              <a href="{{ route('training.license.index') }}" class="nav-link {{ Request::routeIs('training.license.*') ? 'active' : '' }}">
                  <i class="fas fa-file-signature nav-icon"></i>
                  <p>LICENCE / FEES</p>
              </a>
          </li>

          <li class="nav-item">
              <a href="#" class="nav-link">
                  <i class="nav-icon fas fa-users"></i>
                  <p>HUMAN RESOURCES <i class="right fas fa-angle-left"></i></p>
              </a>
              <ul class="nav nav-treeview">
                  <li class="nav-item">
                      <a href="{{ route('divhr.employelist') }}" class="nav-link {{ Request::routeIs('divhr.employelist') ? 'active' : '' }}">
                          <i class="fas fa-user-check nav-icon"></i><p>CURRENT</p>
                      </a>
                  </li>
                  <li class="nav-item">
                      <a href="{{ route('divhr.attendance') }}" class="nav-link {{ Request::routeIs('divhr.attendance') ? 'active' : '' }}">
                          <i class="fas fa-calendar-check nav-icon"></i><p>ATTENDANCE</p>
                      </a>
                  </li>
              </ul>
          </li>

          <li class="nav-item">
              <a href="#" class="nav-link">
                  <i class="fas fa-money-check-alt nav-icon"></i>
                  <p>SALARY REQUISITIONS <i class="right fas fa-angle-left"></i></p>
              </a>
              <ul class="nav nav-treeview">
                  <li class="nav-item"><a href="#" class="nav-link"><i class="fas fa-edit nav-icon"></i><p>DRAFT</p></a></li>
                  <li class="nav-item"><a href="#" class="nav-link"><i class="fas fa-lock nav-icon"></i><p>CLOSED</p></a></li>
              </ul>
          </li>
          
          <li class="nav-item">
              <a href="#" class="nav-link">
                  <i class="nav-icon fas fa-coins"></i>
                  <p>FINANCE <i class="right fas fa-angle-left"></i></p>
              </a>
              <ul class="nav nav-treeview">
                  <li class="nav-item"><a href="#" class="nav-link"><i class="fas fa-wallet nav-icon"></i><p>ACCOUNTS</p></a></li>
                  <li class="nav-item"><a href="#" class="nav-link"><i class="fas fa-hand-holding-usd nav-icon"></i><p>LOANS</p></a></li>
              </ul>
          </li>

      @elseif(in_array(strtolower(trim((string) (Auth::user()->acc_untarea ?? ''))), ['nrdi', 'proc', 'fin', 'rdw', 'hqs']))
          <li class="nav-header">COMMAND VIEW</li>

          <li class="nav-item">
              <a href="{{ route('nrdi.dashboard') }}" class="nav-link {{ Request::routeIs('nrdi.dashboard') ? 'active' : '' }}">
                  <i class="nav-icon fas fa-tachometer-alt"></i>
                  <p>Dashboard</p>
              </a>
          </li>

          <li class="nav-item">
              <a href="{{ route('nrdi.projects.index') }}" class="nav-link {{ Request::routeIs('nrdi.projects.*') ? 'active' : '' }}">
                  <i class="nav-icon fas fa-folder-open"></i>
                  <p>Projects</p>
              </a>
          </li> 

          <li class="nav-item">
              @php
                  $area = strtolower(trim((string) (Auth::user()->acc_untarea ?? '')));
                  $purchaseRoute = 'nrdi.purchase_cases_new.index';
                  if($area === 'proc') $purchaseRoute = 'nrdi.purchase_cases_new.procurement.index';
                  if($area === 'fin') $purchaseRoute = 'nrdi.purchase_cases_new.finance.index';
              @endphp
              <a href="{{ route($purchaseRoute) }}" class="nav-link {{ Request::routeIs('nrdi.purchase_cases_new.*') ? 'active' : '' }}">
                  <i class="nav-icon fas fa-shopping-cart"></i>
                  <p>Purchase Case</p>
              </a>
          </li>

          <li class="nav-item">
              <a href="{{ route('nrdi.contract_cases_new.index') }}" class="nav-link {{ Request::routeIs('nrdi.contract_cases_new.*') ? 'active' : '' }}">
                  <i class="nav-icon fas fa-file-signature"></i>
                  <p>Contract Case</p>
              </a>
          </li>

          <li class="nav-item {{ Request::routeIs('nrdi.purchase_cases.*') || Request::routeIs('nrdi.contract_cases.*') ? 'menu-open' : '' }}">
              <a href="#" class="nav-link {{ Request::routeIs('nrdi.purchase_cases.*') || Request::routeIs('nrdi.contract_cases.*') ? 'active' : '' }}">
                  <i class="nav-icon fas fa-briefcase"></i>
                  <p>VIEW CASES <i class="right fas fa-angle-left"></i></p>
              </a>
              <ul class="nav nav-treeview">
                  <li class="nav-item">
                      @php
                          $area = strtolower(trim((string) (Auth::user()->acc_untarea ?? '')));
                          $route = 'nrdi.purchase_cases.index';
                          if($area === 'proc') $route = 'nrdi.procurement.purchase_cases.index';
                          if($area === 'fin') $route = 'nrdi.finance.purchase_cases.index';
                      @endphp
                      <a href="{{ route($route) }}" class="nav-link {{ Request::routeIs('nrdi.purchase_cases.*') || Request::routeIs('nrdi.procurement.*') || Request::routeIs('nrdi.finance.*') ? 'active' : '' }}">
                          <i class="fas fa-shopping-cart nav-icon"></i><p>Purchase Cases</p>
                      </a>
                  </li>
                  <li class="nav-item">
                      <a href="{{ route('nrdi.contract_cases.index') }}" class="nav-link {{ Request::routeIs('nrdi.contract_cases.*') ? 'active' : '' }}">
                          <i class="fas fa-file-signature nav-icon"></i><p>Contract Cases</p>
                      </a>
                  </li>
          </ul>
          </li>

      @elseif(strtolower(trim((string) (Auth::user()->acc_untarea ?? ''))) === 'it')
          <li class="nav-header">SYSTEM ADMIN</li>

          <li class="nav-item">
              <a href="{{ route('admin.dashboard') }}" class="nav-link {{ Request::routeIs('admin.dashboard') ? 'active' : '' }}">
                  <i class="nav-icon fas fa-tachometer-alt"></i>
                  <p>Dashboard</p>
              </a>
          </li>

          <li class="nav-item">
              <a href="{{ route('admin.accounts.index') }}" class="nav-link {{ Request::routeIs('admin.accounts.*') ? 'active' : '' }}">
                  <i class="nav-icon fas fa-users-cog"></i>
                  <p>Accounts</p>
              </a>
          </li>

          <li class="nav-item">
              <a href="{{ route('admin.reversals.index') }}" class="nav-link {{ Request::routeIs('admin.reversals.*') ? 'active' : '' }}">
                  <i class="nav-icon fas fa-undo-alt"></i>
                  <p>Data Reversals</p>
              </a>
          </li>

          {{-- ========================================================= --}}
          {{-- CASE 3: UNKNOWN / NO ACCESS --}}
          {{-- ========================================================= --}}
          @else
          <li class="nav-item">
              <a href="#" class="nav-link text-danger">
                  <i class="nav-icon fas fa-exclamation-circle"></i>
                  <p>No Access Assigned</p>
              </a>
          </li>
      @endif

          </ul>
        </nav>
        </div>
      </aside>

    @yield('content')
    <footer class="main-footer">
      <strong>Copyright &copy; 2025 <a href="#">RDWIS</a>.</strong>
      All rights reserved.
      <div class="float-right d-none d-sm-inline-block">
        <b>Version</b> 2.0.0
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
  <script src="{{ asset('dist/js/adminlte.js') }}"></script>
  <script src="{{ asset('dist/js/demo.js') }}"></script>
  <script src="{{ asset('dist/js/pages/dashboard.js') }}"></script>



    @stack('scripts')
    @yield('scripts')

    @auth
        <script src="{{ asset('js/rdwis-notifications.js') }}"></script>
    @endauth

    {{-- ========================================================= --}}
    {{-- RDWIS OFFLINE DEBUG CONSOLE (FOR AIR-GAPPED TROUBLESHOOTING) --}}
    {{-- ========================================================= --}}
    <div id="rdwisDebugConsole" style="position: fixed; bottom: 10px; right: 10px; z-index: 9999; font-family: monospace;">
        <button onclick="toggleDebug()" style="background: #ff3e3e; color: #fff; border: none; padding: 5px 12px; border-radius: 20px; font-weight: bold; cursor: pointer; box-shadow: 0 4px 10px rgba(0,0,0,0.5); font-size: 11px;">
            <i class="fas fa-bug mr-1"></i> DEBUG CONSOLE <span id="debugBadge" class="badge badge-light ml-1" style="display:none;">0</span>
        </button>
        <div id="debugContent" style="display: none; width: 450px; height: 350px; background: #1a1a1a; color: #00ff00; border: 2px solid #ff3e3e; border-radius: 8px; margin-top: 10px; overflow: hidden; font-size: 11px; box-shadow: 0 10px 30px rgba(0,0,0,0.8); flex-direction: column;">
            <div class="d-flex justify-content-between p-2 border-bottom border-secondary bg-dark">
                <span class="font-weight-bold text-danger">RDWIS SYSTEM LOG</span>
                <span class="text-muted cursor-pointer" onclick="clearDebug()">CLEAR</span>
            </div>
            <div id="debugLog" style="flex: 1; overflow-y: auto; padding: 10px;">
                <div class="text-muted italic">Monitoring JS & AJAX errors...</div>
            </div>
        </div>
    </div>

    <script>
        function toggleDebug() {
            const content = document.getElementById('debugContent');
            content.style.display = content.style.display === 'none' ? 'flex' : 'none';
        }

        function clearDebug() {
            document.getElementById('debugLog').innerHTML = '';
            document.getElementById('debugBadge').style.display = 'none';
            document.getElementById('debugBadge').innerText = '0';
        }

        function logToDebug(msg, type = 'ERROR') {
            const log = document.getElementById('debugLog');
            const badge = document.getElementById('debugBadge');
            if (log.innerText.includes('Monitoring')) log.innerHTML = '';
            const entry = document.createElement('div');
            entry.style.marginBottom = '5px';
            entry.style.padding = '4px';
            entry.style.borderLeft = type === 'ERROR' ? '3px solid #ff3e3e' : '3px solid #28a745';
            const time = new Date().toLocaleTimeString();
            entry.innerHTML = `<span style="color:#666;">[${time}]</span> <span style="color:${type==='ERROR'?'#ff3e3e':'#28a745'}; font-weight:bold;">${type}:</span> ${msg}`;
            log.prepend(entry);
            badge.style.display = 'inline-block';
            badge.innerText = parseInt(badge.innerText) + 1;
            document.querySelector('#rdwisDebugConsole button').style.background = '#dc3545';
        }

        window.onerror = function(m, s, l, c, e) {
            logToDebug(`${m} (at ${s.split('/').pop()}:${l})`);
            return false;
        };
        window.onunhandledrejection = (e) => logToDebug(`Async Error: ${e.reason}`);
        
        $(document).ajaxError((e, x, s, t) => {
            let msg = `AJAX Fail: ${s.url} -> ${x.status} (${t})`;
            if (x.responseJSON && x.responseJSON.message) msg += `<br>Msg: ${x.responseJSON.message}`;
            logToDebug(msg);
        });

        $(document).ready(function() {
            if (typeof Swal === 'undefined') logToDebug('SweetAlert2 (Swal) missing.');
            if (typeof $.fn.select2 === 'undefined') logToDebug('Select2 missing.');
            // Initialize Select2 globally for any modal that opens
            $(document).on('shown.bs.modal', function() {
                $('.select2').select2({ width: '100%', theme: 'bootstrap4' });
            });
        });
    </script>

    {{-- Firefox zoom fallback --}}
    <script>
    (function() {
        var isFirefox = typeof InstallTrigger !== 'undefined';
        if (!isFirefox) return;
        var w = window.innerWidth;
        var scale = 1;
        if (w <= 1100) scale = 0.70;
        else if (w <= 1280) scale = 0.75;
        else if (w <= 1400) scale = 0.85;
        if (scale < 1) {
            document.body.style.transform = 'scale(' + scale + ')';
            document.body.style.transformOrigin = '0 0';
            document.body.style.width = (100 / scale) + '%';
        }
    })();
    </script>
    
    <!-- PWA Service Worker & Install Banner -->
    <script>
        if ('serviceWorker' in navigator) {
            window.addEventListener('load', function() {
                navigator.serviceWorker.register('/service-worker.js').catch(function(error) {
                });
            });
        }
    </script>
    @include('pwa.install-banner')
  </body>
</html>
