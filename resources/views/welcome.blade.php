  <!DOCTYPE html>
  <html lang="en">
  <head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>RDWIS</title>

    <!-- PWA Setup -->
    <link rel="manifest" href="{{ asset('manifest.json') }}">
    <meta name="theme-color" content="#5F7858">
    <link rel="apple-touch-icon" href="{{ asset('images/icons/icon-192.png') }}">

    <link rel="stylesheet" href="{{ asset('css/fonts.css') }}">
    
    <link rel="stylesheet" href="{{ asset('plugins/fontawesome-free/css/all.min.css') }}">
    <link rel="stylesheet" href="{{ asset('plugins/ionicons/ionicons.min.css') }}">
    <link rel="stylesheet" href="{{ asset('plugins/tempusdominus-bootstrap-4/css/tempusdominus-bootstrap-4.min.css') }}">
    <link rel="stylesheet" href="{{ asset('plugins/icheck-bootstrap/icheck-bootstrap.min.css') }}">
    <link rel="stylesheet" href="{{ asset('plugins/jqvmap/jqvmap.min.css') }}">
    <link rel="stylesheet" href="{{ asset('dist/css/adminlte.min.css') }}">
    <link rel="stylesheet" href="{{ asset('css/rdwis-dark.css') }}">
    <link rel="stylesheet" href="{{ asset('css/theme.css') }}">
    <link rel="stylesheet" href="{{ asset('plugins/overlayScrollbars/css/OverlayScrollbars.min.css') }}">
    <link rel="stylesheet" href="{{ asset('plugins/daterangepicker/daterangepicker.css') }}">
    <link rel="stylesheet" href="{{ asset('plugins/summernote/summernote-bs4.min.css') }}">
    <link rel="stylesheet" href="{{ asset('plugins/select2/css/select2.min.css') }}">
    <link rel="stylesheet" href="{{ asset('plugins/select2-bootstrap4-theme/select2-bootstrap4.min.css') }}">
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

        @keyframes pulse-red-glow {
            0% {
                box-shadow: 0 0 0 0 rgba(239, 68, 68, 0.85);
                transform: scale(1);
            }
            50% {
                box-shadow: 0 0 0 7px rgba(239, 68, 68, 0);
                transform: scale(1.08);
            }
            100% {
                box-shadow: 0 0 0 0 rgba(239, 68, 68, 0);
                transform: scale(1);
            }
        }

        .badge-blinking-red {
            background-color: #ef4444 !important;
            color: #ffffff !important;
            animation: pulse-red-glow 1.5s infinite cubic-bezier(0.4, 0, 0.6, 1);
            font-weight: 700 !important;
            border-radius: 12px !important;
            padding: 2px 7px !important;
            font-size: 10px !important;
            letter-spacing: 0.3px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            border: 1px solid rgba(255, 255, 255, 0.6);
            vertical-align: middle;
        }

        /* Standardized Sidebar Treeview Dropdown Arrow & Badge Layout (Expanded Mode) */
        body:not(.sidebar-collapse) .nav-sidebar .nav-link,
        body.sidebar-collapse .main-sidebar:hover .nav-sidebar .nav-link {
            position: relative !important;
            display: flex !important;
            align-items: center !important;
            padding-right: 28px !important;
        }

        body:not(.sidebar-collapse) .nav-sidebar .nav-link p,
        body.sidebar-collapse .main-sidebar:hover .nav-sidebar .nav-link p {
            display: inline-flex !important;
            align-items: center !important;
            margin: 0 !important;
            width: 100% !important;
            white-space: nowrap !important;
            text-transform: capitalize !important;
        }

        .main-sidebar .nav-sidebar .nav-link p,
        .main-sidebar .nav-sidebar .nav-header {
            text-transform: capitalize !important;
        }

        /* Universal Clickable Entity Title Links (Projects, Contract Cases, Purchase Cases) */
        a.rd-entity-title-link,
        .rd-entity-title-link,
        a.rd-entity-title-link:link,
        a.rd-entity-title-link:focus,
        a.rd-entity-title-link:active,
        .rd-entity-title-link.is-clicked {
            color: #111827 !important; /* solid black by default */
            text-decoration: none !important;
            transition: color 0.15s ease-in-out;
            cursor: pointer !important;
            display: inline-block;
        }
        /* Keep visited links as default solid black as well, but DO NOT put !important so :hover can override it seamlessly */
        a.rd-entity-title-link:visited {
            color: #111827;
            text-decoration: none !important;
        }

        /* ONLY when inside truly dark themed tables */
        .table-dark a.rd-entity-title-link,
        .table-dark .rd-entity-title-link {
            color: #f8fafc !important;
        }
        .table-dark a.rd-entity-title-link:visited {
            color: #f8fafc;
        }

        /* Hover States (Strictly vibrant blue #2563eb, NO underline ever across any parent or state) */
        html body a.rd-entity-title-link:hover,
        html body a.rd-entity-title-link:visited:hover,
        html body a.rd-entity-title-link:focus:hover,
        html body a.rd-entity-title-link:active:hover,
        html body .rd-entity-title-link:hover,
        html body .rd-entity-title-link:hover *,
        a.rd-entity-title-link:hover,
        a.rd-entity-title-link:hover *,
        a.rd-entity-title-link:visited:hover,
        .rd-entity-title-link:hover,
        .rd-entity-title-link:hover *,
        .rd-entity-title-link:visited:hover,
        a.rd-entity-title-link:focus:hover,
        .rd-entity-title-link:focus:hover {
            color: #2563eb !important; /* Clear vibrant blue */
            text-decoration: none !important; /* Never underline */
        }
        html body .table-dark a.rd-entity-title-link:hover,
        .table-dark a.rd-entity-title-link:hover,
        .table-dark .rd-entity-title-link:hover {
            color: #38bdf8 !important; /* Lighter bright blue for dark tables */
            text-decoration: none !important;
        }

        body:not(.sidebar-collapse) .nav-sidebar .nav-link p > .badge,
        body:not(.sidebar-collapse) .nav-sidebar .nav-link p > span.badge,
        body.sidebar-collapse .main-sidebar:hover .nav-sidebar .nav-link p > .badge,
        body.sidebar-collapse .main-sidebar:hover .nav-sidebar .nav-link p > span.badge {
            margin-left: 6px !important;
            margin-right: 4px !important;
            flex-shrink: 0 !important;
        }

        body:not(.sidebar-collapse) .nav-sidebar .nav-link p > .right,
        body:not(.sidebar-collapse) .nav-sidebar .nav-link > .right,
        body.sidebar-collapse .main-sidebar:hover .nav-sidebar .nav-link p > .right,
        body.sidebar-collapse .main-sidebar:hover .nav-sidebar .nav-link > .right {
            position: absolute !important;
            right: 10px !important;
            top: 50% !important;
            transform: translateY(-50%) !important;
            margin: 0 !important;
            font-size: 11px !important;
            color: var(--rd-text3, #94a3b8) !important;
            transition: transform 0.25s ease-in-out !important;
        }

        .nav-item.menu-open > .nav-link p > .right,
        .nav-item.menu-open > .nav-link > .right,
        .nav-item.menu-is-opening > .nav-link p > .right,
        .nav-item.menu-is-opening > .nav-link > .right {
            transform: translateY(-50%) rotate(-90deg) !important;
        }

        /* =========================================================================
           SIDEBAR COLLAPSED (MINI) MODE FIXES — ICONS ONLY & CLEAN PROFILE HIDING
           ========================================================================= */
        body.sidebar-collapse .main-sidebar:not(:hover) .user-panel,
        body.sidebar-collapse.sidebar-mini .main-sidebar:not(:hover) .user-panel {
            display: none !important;
            height: 0 !important;
            padding: 0 !important;
            margin: 0 !important;
            border: none !important;
            overflow: hidden !important;
        }

        body.sidebar-collapse .main-sidebar:not(:hover) .brand-link,
        body.sidebar-collapse.sidebar-mini .main-sidebar:not(:hover) .brand-link {
            display: flex !important;
            justify-content: center !important;
            align-items: center !important;
            padding: 0.8125rem 0.5rem !important;
            text-align: center !important;
        }

        body.sidebar-collapse .main-sidebar:not(:hover) .brand-link .brand-image,
        body.sidebar-collapse.sidebar-mini .main-sidebar:not(:hover) .brand-link .brand-image {
            margin: 0 auto !important;
            float: none !important;
            max-height: 33px !important;
        }

        body.sidebar-collapse .main-sidebar:not(:hover) .brand-link .brand-text,
        body.sidebar-collapse.sidebar-mini .main-sidebar:not(:hover) .brand-link .brand-text {
            display: none !important;
            visibility: hidden !important;
            width: 0 !important;
            opacity: 0 !important;
        }

        body.sidebar-collapse .main-sidebar:not(:hover) .nav-sidebar .nav-link p,
        body.sidebar-collapse.sidebar-mini .main-sidebar:not(:hover) .nav-sidebar .nav-link p,
        body.sidebar-collapse .main-sidebar:not(:hover) .nav-sidebar .nav-header,
        body.sidebar-collapse.sidebar-mini .main-sidebar:not(:hover) .nav-sidebar .nav-header,
        body.sidebar-collapse .main-sidebar:not(:hover) .nav-sidebar .nav-link .right,
        body.sidebar-collapse.sidebar-mini .main-sidebar:not(:hover) .nav-sidebar .nav-link .right,
        body.sidebar-collapse .main-sidebar:not(:hover) .nav-treeview {
            display: none !important;
            visibility: hidden !important;
            width: 0 !important;
            opacity: 0 !important;
        }

        body.sidebar-collapse .main-sidebar:not(:hover) .nav-sidebar .nav-link,
        body.sidebar-collapse.sidebar-mini .main-sidebar:not(:hover) .nav-sidebar .nav-link {
            display: flex !important;
            justify-content: center !important;
            align-items: center !important;
            width: 4.6rem !important;
            padding: 0.75rem 0 !important;
            margin: 2px 0 !important;
            text-align: center !important;
        }

        body.sidebar-collapse .main-sidebar:not(:hover) .nav-sidebar .nav-link .nav-icon,
        body.sidebar-collapse.sidebar-mini .main-sidebar:not(:hover) .nav-sidebar .nav-link .nav-icon {
            margin: 0 auto !important;
            font-size: 1.3rem !important;
            display: inline-block !important;
            text-align: center !important;
            float: none !important;
            width: auto !important;
        }

        /* When hovering over collapsed sidebar -> smooth expand */
        body.sidebar-collapse.sidebar-mini .main-sidebar:hover {
            width: 250px !important;
        }

        body.sidebar-collapse.sidebar-mini .main-sidebar:hover .brand-link {
            display: block !important;
            text-align: left !important;
            padding: 0.8125rem 0.5rem !important;
        }

        body.sidebar-collapse.sidebar-mini .main-sidebar:hover .brand-link .brand-text {
            display: inline-block !important;
            visibility: visible !important;
            width: auto !important;
            opacity: 1 !important;
        }

        body.sidebar-collapse.sidebar-mini .main-sidebar:hover .user-panel {
            display: flex !important;
            height: auto !important;
            margin-top: 1rem !important;
            margin-bottom: 1rem !important;
            padding-bottom: 1rem !important;
        }

        body.sidebar-collapse.sidebar-mini .main-sidebar:hover .nav-sidebar .nav-link {
            display: flex !important;
            width: auto !important;
            padding-right: 28px !important;
            padding-left: 1rem !important;
            justify-content: flex-start !important;
        }

        body.sidebar-collapse.sidebar-mini .main-sidebar:hover .nav-sidebar .nav-link p {
            display: inline-flex !important;
            visibility: visible !important;
            width: 100% !important;
            opacity: 1 !important;
        }

        body.sidebar-collapse.sidebar-mini .main-sidebar:hover .nav-sidebar .nav-header {
            display: block !important;
            visibility: visible !important;
            opacity: 1 !important;
        }

        body.sidebar-collapse.sidebar-mini .main-sidebar:hover .nav-sidebar .nav-link .nav-icon {
            margin-right: 0.5rem !important;
            font-size: 1.1rem !important;
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
                box-shadow: 0 0 15px rgba(41,40,36,0.3) !important;
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

        /* Frosted Glass Preloader Overlay */
        .preloader {
            background: rgba(247, 245, 240, 0.45) !important;
            background-color: rgba(247, 245, 240, 0.45) !important;
            backdrop-filter: blur(14px);
            -webkit-backdrop-filter: blur(14px);
            transition: opacity 0.35s ease, visibility 0.35s ease, height 0.3s ease !important;
        }

        .preloader.preloader-hidden,
        .preloader[style*="height: 0"],
        .preloader[style*="height:0"],
        .preloader[style*="display: none"],
        .preloader[style*="display:none"] {
            display: none !important;
            visibility: hidden !important;
            opacity: 0 !important;
            backdrop-filter: none !important;
            -webkit-backdrop-filter: none !important;
            pointer-events: none !important;
            height: 0 !important;
            z-index: -1 !important;
        }

        .preloader img,
        .preloader .animation__shake,
        .preloader .animation__wobble {
            filter: drop-shadow(0 12px 30px rgba(41, 40, 36, 0.18)) !important;
            animation: preloaderPulse 1.8s ease-in-out infinite alternate !important;
            max-width: 180px !important;
            height: auto !important;
        }

        @keyframes preloaderPulse {
            0% { transform: translateY(0px) scale(0.97); opacity: 0.9; }
            100% { transform: translateY(-6px) scale(1.02); opacity: 1; }
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
        @if(session('impersonated_by_god'))
        <li class="nav-item d-none d-sm-inline-block">
            <a href="/godmode/return" class="nav-link btn btn-danger text-white px-3 shadow-sm ml-3" style="border-radius: 20px;"><i class="fas fa-biohazard mr-2"></i>Exit Control ({{ Auth::user()->acc_name }})</a>
        </li>
        @endif
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

      <ul class="navbar-nav ml-auto align-items-center" style="gap: 8px;">
        
        {{-- Dynamic Purchase Notifications --}}
        <li class="nav-item dropdown" id="notif-bell-container">
          <a class="nav-link position-relative d-flex align-items-center justify-content-center pnt-bell-btn" data-toggle="dropdown" href="#" id="pnt-bell" title="Notifications" style="width: 36px; height: 36px; border-radius: 50%; background: var(--rd-neutral-100); border: 1px solid var(--rd-border); color: var(--rd-text1); transition: all 0.2s;">
            <i class="far fa-bell" style="font-size: 15px;"></i>
            <span class="badge badge-danger navbar-badge d-none" id="pnt-count" style="font-size: 10px; font-weight: 700; top: -4px; right: -4px; padding: 2px 6px; border-radius: 10px; box-shadow: 0 2px 6px rgba(220,53,69,0.35); border: 1.5px solid #fff;">0</span>
          </a>
          <div class="dropdown-menu dropdown-menu-lg dropdown-menu-right notif-dropdown-custom shadow-lg p-0" id="pnt-dropdown" style="min-width: 350px; max-width: 400px; border-radius: 12px; border: 1px solid var(--rd-border); overflow: hidden; background: var(--rd-surface);">
            <div class="d-flex align-items-center justify-content-between px-3 py-2 border-bottom" style="background: var(--rd-neutral-50);">
              <div class="d-flex align-items-center">
                <i class="fas fa-bell mr-2" style="color: var(--rd-primary-600); font-size: 13px;"></i>
                <span class="font-weight-bold" style="font-size: 13px; color: var(--rd-text1);">Notifications</span>
              </div>
              <span class="badge badge-pill text-xs px-2 py-1" id="pnt-badge-header" style="background: var(--rd-primary-100); color: var(--rd-primary-800); border: 1px solid var(--rd-primary-300); font-size: 10.5px;">0 New</span>
            </div>
            
            <div id="pnt-list" class="p-1" style="max-height: 320px; overflow-y: auto;">
              <!-- Dynamically populated -->
              <div class="p-4 text-center text-muted" style="font-size: 13px;">
                <i class="far fa-bell-slash fa-2x mb-2 d-block text-muted opacity-50"></i>
                No new notifications
              </div>
            </div>
            
            <div class="border-top p-2 text-center" style="background: var(--rd-neutral-50);">
              <a href="javascript:void(0)" class="btn btn-sm btn-link text-decoration-none font-weight-bold p-0" id="pnt-mark-all" style="color: var(--rd-primary-700); font-size: 12px;">
                <i class="fas fa-check-double mr-1"></i> Mark all as read
              </a>
            </div>
          </div>
        </li>
        
        <li class="nav-item">
          <a class="nav-link d-flex align-items-center justify-content-center" data-widget="fullscreen" href="#" role="button" title="Full Screen" style="width: 36px; height: 36px; border-radius: 50%; background: var(--rd-neutral-100); border: 1px solid var(--rd-border); color: var(--rd-text1); transition: all 0.2s;">
            <i class="fas fa-expand-arrows-alt" style="font-size: 13px;"></i>
          </a>
        </li>

        {{-- Enhanced Red Logout Button with Confirmation --}}
        <li class="nav-item">
          <form id="global-logout-form" action="{{ route('logout') }}" method="POST" class="d-inline m-0">
              @csrf
              <button type="button" class="btn btn-danger btn-sm font-weight-bold d-flex align-items-center shadow-sm px-3 ml-1" onclick="handleLogoutClick(event)" style="border-radius: 20px; height: 34px; gap: 7px; font-size: 12.5px; background: #dc3545 !important; border-color: #dc3545 !important; color: #ffffff !important; box-shadow: 0 3px 8px rgba(220,53,69,0.3) !important; transition: all 0.2s ease;">
                  <i class="fas fa-sign-out-alt" style="font-size: 12px;"></i>
                  <span>Logout</span>
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
                @if(session('impersonated_by_god'))
                <a href="#" class="d-block text-danger">
                    <i class="fas fa-radiation-alt mr-1"></i> God Mode<br>
                    <small class="text-warning">Controlling: {{ Auth::user()->acc_rank }} {{ Auth::user()->acc_name }}</small>
                </a>
                @else
                <a href="#" class="d-block">
                    {{ Auth::user()->acc_rank }} {{ Auth::user()->acc_name }}<br>
                    <small>{{ Auth::user()->acc_desig }} —<wbr> {{ Auth::user()->acc_untname }}</small>
                </a>
                @endif
            @else
                <a href="#" class="d-block">Guest</a>
            @endif
          </div>
        </div>

        <nav class="mt-2">
          <ul class="nav nav-pills nav-sidebar flex-column" data-widget="treeview" role="menu" data-accordion="false">
          @php
              $sbBadges = $sidebarBadges ?? \App\Services\SidebarBadgeService::getBadgesForUser();
              $sbPur = $sbBadges['pur'] ?? 0;
              $sbCtr = $sbBadges['ctr'] ?? 0;
              $sbHr  = $sbBadges['hr'] ?? 0;
          @endphp
          
          @if(Auth::user()->acc_username === 'superadminrdw' || session('impersonated_by_god'))
          <li class="nav-header text-danger font-weight-bold"><i class="fas fa-radiation-alt mr-2"></i> God Mode</li>
          
          {{-- Headquarters Dropdown --}}
          <li class="nav-item has-treeview">
              <a href="#" class="nav-link bg-danger text-white">
                  <i class="nav-icon fas fa-building"></i>
                  <p>Headquarters <i class="right fas fa-angle-left"></i></p>
              </a>
              <ul class="nav nav-treeview" style="background-color: rgba(220, 53, 69, 0.1);">
                  @php
                      $hqUsers = \App\Models\CenAccount::where('acc_status', 'Active')
                          ->whereIn('acc_untnamesh', ['HQs NRD', 'NRDI'])
                          ->whereNotIn('acc_username', ['superadminrdw', 'srehman', 'srrehman'])
                          ->orderBy('acc_name')
                          ->get();
                  @endphp
                  @foreach($hqUsers as $hqU)
                  <li class="nav-item">
                      <a href="/godmode/takeover/{{ $hqU->acc_id }}" class="nav-link text-white" onclick="return confirm('Take control of {{ $hqU->acc_name }}?');">
                          <i class="far fa-user nav-icon"></i>
                          <p>{{ $hqU->acc_rank }} {{ $hqU->acc_name }} ({{ $hqU->acc_username }})</p>
                      </a>
                  </li>
                  @endforeach
              </ul>
          </li>

          {{-- MD/DG Links --}}
          @php
              $mdDgUsers = \App\Models\CenAccount::where('acc_status', 'Active')
                  ->whereIn('acc_username', ['srehman', 'srrehman'])
                  ->orderBy('acc_username', 'desc') // srrehman (DG) then srehman (MD)
                  ->get();
          @endphp
          @foreach($mdDgUsers as $mdDg)
          <li class="nav-item">
              <a href="/godmode/takeover/{{ $mdDg->acc_id }}" class="nav-link bg-danger text-white mb-1" onclick="return confirm('Take control of {{ $mdDg->acc_name }}?');">
                  <i class="nav-icon fas fa-user-tie"></i>
                  <p>{{ $mdDg->acc_rank }} {{ $mdDg->acc_name }} ({{ $mdDg->acc_desig }})</p>
              </a>
          </li>
          @endforeach

          {{-- Divisions Dropdown --}}
          <li class="nav-item has-treeview">
              <a href="#" class="nav-link bg-danger text-white">
                  <i class="nav-icon fas fa-network-wired"></i>
                  <p>Divisions <i class="right fas fa-angle-left"></i></p>
              </a>
              <ul class="nav nav-treeview" style="background-color: rgba(220, 53, 69, 0.1);">
                  @php
                      // Fetch users in divisions (e.g. Sys, NWS, Sensors, Enab, SoS, Comm)
                      $divUsers = \App\Models\CenAccount::where('acc_status', 'Active')
                          ->whereIn('acc_untnamesh', ['Sys', 'NWS', 'Sensors', 'Enab', 'SoS', 'Comm'])
                          ->orderBy('acc_untnamesh')
                          ->orderBy('acc_name')
                          ->get();
                  @endphp
                  @foreach($divUsers as $divU)
                  <li class="nav-item">
                      <a href="/godmode/takeover/{{ $divU->acc_id }}" class="nav-link text-white" onclick="return confirm('Take control of {{ $divU->acc_name }}?');">
                          <i class="far fa-circle nav-icon"></i>
                          <p>[{{ $divU->acc_untnamesh }}] {{ $divU->acc_name }}</p>
                      </a>
                  </li>
                  @endforeach
              </ul>
          </li>

          {{-- Others Dropdown --}}
          <li class="nav-item has-treeview">
              <a href="#" class="nav-link bg-danger text-white">
                  <i class="nav-icon fas fa-users-cog"></i>
                  <p>Others <i class="right fas fa-angle-left"></i></p>
              </a>
              <ul class="nav nav-treeview" style="background-color: rgba(220, 53, 69, 0.1);">
                  @php
                      $otherUsers = \App\Models\CenAccount::where('acc_status', 'Active')
                          ->whereNotIn('acc_username', ['superadminrdw', 'srehman', 'srrehman'])
                          ->whereNotIn('acc_untnamesh', ['HQs NRD', 'NRDI', 'Sys', 'NWS', 'Sensors', 'Enab', 'SoS', 'Comm'])
                          ->orderBy('acc_untnamesh')
                          ->orderBy('acc_name')
                          ->get();
                  @endphp
                  @foreach($otherUsers as $othU)
                  <li class="nav-item">
                      <a href="/godmode/takeover/{{ $othU->acc_id }}" class="nav-link text-white" onclick="return confirm('Take control of {{ $othU->acc_name }}?');">
                          <i class="far fa-dot-circle nav-icon"></i>
                          <p>[{{ $othU->acc_untnamesh }}] {{ $othU->acc_name }}</p>
                      </a>
                  </li>
                  @endforeach
              </ul>
          </li>

          {{-- RDWIS Settings Dropdown for SuperAdmin / God Mode --}}
          <li class="nav-item has-treeview {{ request()->is('admin/settings*') ? 'menu-open' : '' }} mt-1 mb-2">
              <a href="#" class="nav-link bg-warning text-dark font-weight-bold" style="border: 1px solid #d97706; box-shadow: 0 2px 6px rgba(0,0,0,0.1); border-radius: 6px;">
                  <i class="nav-icon fas fa-sliders-h text-dark"></i>
                  <p class="font-weight-bold rajdhani" style="font-size: 13.5px; letter-spacing: 0.5px;">
                      Rdwis Settings <i class="right fas fa-angle-left text-dark"></i>
                  </p>
              </a>
              <ul class="nav nav-treeview p-1" style="background-color: #ffffff; border-radius: 6px; margin-top: 4px; border: 1px solid #e2e8f0; box-shadow: 0 3px 8px rgba(0,0,0,0.05);">
                  <li class="nav-item mb-1">
                      <a href="{{ route('admin.settings.financial') }}" class="nav-link py-2 px-2.5 rounded d-flex align-items-center {{ request()->routeIs('admin.settings.financial') ? 'bg-primary text-white font-weight-bold' : 'text-dark' }}" style="font-size: 12.5px;">
                          <i class="fas fa-coins nav-icon {{ request()->routeIs('admin.settings.financial') ? 'text-white' : 'text-warning' }} mr-2" style="font-size: 14px;"></i>
                          <p class="mb-0 text-truncate font-weight-bold rajdhani">Financial & Hr Limits</p>
                      </a>
                  </li>
                  <li class="nav-item mb-1">
                      <a href="{{ route('admin.settings.workflows') }}" class="nav-link py-2 px-2.5 rounded d-flex align-items-center {{ request()->routeIs('admin.settings.workflows') ? 'bg-primary text-white font-weight-bold' : 'text-dark' }}" style="font-size: 12.5px;">
                          <i class="fas fa-shopping-cart nav-icon {{ request()->routeIs('admin.settings.workflows') ? 'text-white' : 'text-success' }} mr-2" style="font-size: 14px;"></i>
                          <p class="mb-0 text-truncate font-weight-bold rajdhani">Workflow: Purchase Cases</p>
                      </a>
                  </li>
                  <li class="nav-item mb-1">
                      <a href="{{ route('admin.settings.workflows_mpr') }}" class="nav-link py-2 px-2.5 rounded d-flex align-items-center {{ request()->routeIs('admin.settings.workflows_mpr') ? 'bg-primary text-white font-weight-bold' : 'text-dark' }}" style="font-size: 12.5px;">
                          <i class="fas fa-file-alt nav-icon {{ request()->routeIs('admin.settings.workflows_mpr') ? 'text-white' : 'text-info' }} mr-2" style="font-size: 14px;"></i>
                          <p class="mb-0 text-truncate font-weight-bold rajdhani">Workflow: Mpr Reports</p>
                      </a>
                  </li>
                  <li class="nav-item">
                      <a href="{{ route('admin.settings.workflows_hr') }}" class="nav-link py-2 px-2.5 rounded d-flex align-items-center {{ request()->routeIs('admin.settings.workflows_hr') ? 'bg-primary text-white font-weight-bold' : 'text-dark' }}" style="font-size: 12.5px;">
                          <i class="fas fa-user-check nav-icon {{ request()->routeIs('admin.settings.workflows_hr') ? 'text-white' : 'text-danger' }} mr-2" style="font-size: 14px;"></i>
                          <p class="mb-0 text-truncate font-weight-bold rajdhani">Workflow: Hiring & Contracts</p>
                      </a>
                  </li>
                  <li class="nav-item">
                      <a href="{{ route('nrdi.purchase_cases_new.it_template') }}" class="nav-link py-2 px-2.5 rounded d-flex align-items-center {{ Request::is('*/it-template*') ? 'bg-primary text-white font-weight-bold' : 'text-dark' }}" style="font-size: 12.5px;">
                          <i class="fas fa-file-alt nav-icon {{ Request::is('*/it-template*') ? 'text-white' : 'text-warning' }} mr-2" style="font-size: 14px;"></i>
                          <p class="mb-0 text-truncate font-weight-bold rajdhani">IT Letter Template</p>
                      </a>
                  </li>
              </ul>
          </li>
          @endif

      {{-- ========================================================= --}}
      {{-- CASE 1: SO R&D (Area: 'prjrdw') --}}
      {{-- ========================================================= --}}
      @if(Auth::user()->isSORD())

      <li class="nav-header">Sor & D Module</li>

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
          <a href="{{ route('division.finance-of-project.index') }}" class="nav-link {{ Request::routeIs('division.finance-of-project.*') ? 'active' : '' }}">
              <i class="fas fa-chart-pie nav-icon text-warning"></i>
              <p>Project Financing</p>
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
                  <p>Projects</p>
              </a>
          </li>

          <li class="nav-item">
              <a href="{{ route('division.finance-of-project.index') }}" class="nav-link {{ Request::routeIs('division.finance-of-project.*') ? 'active' : '' }}">
                  <i class="fas fa-chart-pie nav-icon text-warning"></i>
                  <p>Project Financing</p>
              </a>
          </li>



            <li class="nav-item">
                <a href="{{ route('purchase.initiation.index') }}" class="nav-link {{ Request::routeIs('purchase.initiation.*') || Request::routeIs('purchase.select') || Request::is('pc-initiation*') ? 'active' : '' }}">
                    <i class="nav-icon fas fa-shopping-cart"></i>
                    <p>Purchase Cases <span class="badge badge-blinking-red badge-pur-parent badge-pur-child {{ $sbPur > 0 ? '' : 'd-none' }} ml-1">{{ $sbPur }}</span></p>
                </a>
            </li>

            <li class="nav-item">
                <a href="{{ route('division.contract-cases.index') }}" class="nav-link {{ Request::routeIs('division.contract-cases.*') ? 'active' : '' }}">
                    <i class="fas fa-file-signature nav-icon text-warning"></i>
                    <p>Contract Cases <span class="badge badge-blinking-red badge-ctr-child {{ $sbCtr > 0 ? '' : 'd-none' }} ml-1">{{ $sbCtr }}</span></p>
                </a>
            </li>

            <li class="nav-item">
                <a href="{{ route('inventory.assets.index') }}" class="nav-link {{ Request::routeIs('inventory.assets.*') || Request::routeIs('purchase.receipts.*') ? 'active' : '' }}">
                    <i class="nav-icon fas fa-boxes text-info"></i>
                    <p>Inventory & Assets</p>
                </a>
            </li>

            <li class="nav-item">
                <a href="{{ route('nrdi.firms.list') }}" class="nav-link {{ Request::routeIs('nrdi.firms.*') ? 'active' : '' }}">
                    <i class="nav-icon fas fa-building text-cyan"></i>
                    <p>Suppliers & Firms</p>
                </a>
            </li>

            <li class="nav-item {{ Request::routeIs('divhr.employelist*') || Request::routeIs('divhr.employeedetail*') || Request::routeIs('hr.navy_civilians') || Request::routeIs('hr.pn_officers') || Request::routeIs('hr.pn_sailors') || Request::routeIs('divhr.attendance*') || Request::routeIs('divhr.salary.requisitions*') ? 'menu-open' : '' }}">
                <a href="#" class="nav-link {{ Request::routeIs('divhr.employelist*') || Request::routeIs('divhr.employeedetail*') || Request::routeIs('hr.navy_civilians') || Request::routeIs('hr.pn_officers') || Request::routeIs('hr.pn_sailors') || Request::routeIs('divhr.attendance*') || Request::routeIs('divhr.salary.requisitions*') ? 'active' : '' }}">
                    <i class="nav-icon fas fa-users text-primary"></i>
                    <p>Human Resources <span class="badge badge-blinking-red badge-hr-parent {{ $sbHr > 0 ? '' : 'd-none' }} ml-1">{{ $sbHr }}</span> <i class="right fas fa-angle-left"></i></p>
                </a>
                <ul class="nav nav-treeview">
                    <li class="nav-item">
                        <a href="{{ route('divhr.employelist') }}" class="nav-link {{ Request::routeIs('divhr.employelist*') || Request::routeIs('divhr.employeedetail*') ? 'active' : '' }}">
                            <i class="fas fa-user-tie nav-icon text-info"></i><p>Hired Employees <span class="badge badge-blinking-red badge-hr-child {{ $sbHr > 0 ? '' : 'd-none' }} ml-1">{{ $sbHr }}</span></p>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="{{ route('hr.navy_civilians') }}" class="nav-link {{ Request::routeIs('hr.navy_civilians') ? 'active' : '' }}">
                            <i class="fas fa-user-shield nav-icon text-success"></i><p>Navy Civilians</p>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="{{ route('hr.pn_officers') }}" class="nav-link {{ Request::routeIs('hr.pn_officers') ? 'active' : '' }}">
                            <i class="fas fa-user-astronaut nav-icon text-warning"></i><p>PN Officers</p>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="{{ route('hr.pn_sailors') }}" class="nav-link {{ Request::routeIs('hr.pn_sailors') ? 'active' : '' }}">
                            <i class="fas fa-anchor nav-icon text-cyan"></i><p>PN CPO / Sailors</p>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="{{ route('division.attendance') }}" class="nav-link {{ Request::routeIs('division.attendance*') || Request::routeIs('divhr.attendance*') ? 'active' : '' }}">
                            <i class="fas fa-calendar-check nav-icon text-success"></i><p>Attendance</p>
                        </a>
                    </li>
                    @if(!Auth::user()->isMdDdgDg())
                    <li class="nav-item">
                        <a href="{{ route('divhr.salary.requisitions.index') }}" class="nav-link {{ Request::routeIs('divhr.salary.requisitions*') ? 'active' : '' }}">
                            <i class="fas fa-file-invoice-dollar nav-icon text-warning"></i><p>Salary Requisitions</p>
                        </a>
                    </li>
                    @endif
                </ul>
            </li>

            <!-- Dedicated REPORTS Dropdown for Division -->
            <li class="nav-item {{ Request::routeIs('hr.reports.*') || Request::routeIs('fin.reports.*') ? 'menu-open' : '' }}">
                <a href="#" class="nav-link {{ Request::routeIs('hr.reports.*') || Request::routeIs('fin.reports.*') ? 'active' : '' }}">
                    <i class="nav-icon fas fa-chart-line text-cyan"></i>
                    <p>Reports <i class="right fas fa-angle-left"></i></p>
                </a>
                <ul class="nav nav-treeview">
                    <li class="nav-item">
                        <a href="{{ route('hr.reports.index') }}" class="nav-link {{ Request::routeIs('hr.reports.*') ? 'active' : '' }}">
                            <i class="fas fa-users-cog nav-icon" style="color: #67e8f9;"></i><p>Hr Reports</p>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="{{ route('fin.reports.index') }}" class="nav-link {{ Request::routeIs('fin.reports.*') ? 'active' : '' }}">
                            <i class="fas fa-coins nav-icon text-warning"></i><p>Finance Reports</p>
                        </a>
                    </li>
                </ul>
            </li>

      {{-- ========================================================= --}}
      {{-- CASE 3: HR USER (Area: 'hr') --}}
      {{-- ========================================================= --}}
      @elseif(strtolower(trim((string) (Auth::user()->acc_untarea ?? ''))) === 'hr')
          <li class="nav-header text-info rajdhani font-weight-bold" style="letter-spacing: 1px;">Hr Directorate</li>

          <li class="nav-item">
              <a href="{{ route('hr.dashboard') }}" class="nav-link {{ Request::routeIs('hr.dashboard') ? 'active' : '' }}">
                  <i class="nav-icon fas fa-tachometer-alt"></i>
                  <p>Dashboard</p>
              </a>
          </li>

           <li class="nav-item">
               <a href="{{ route('division.finance-of-project.index') }}" class="nav-link {{ Request::routeIs('division.finance-of-project.*') ? 'active' : '' }}">
                   <i class="fas fa-chart-pie nav-icon text-warning"></i>
                   <p>Project Financing</p>
               </a>
           </li>

           <li class="nav-item">
               <a href="{{ route('nrdi.firms.list') }}" class="nav-link {{ Request::routeIs('nrdi.firms.*') ? 'active' : '' }}">
                   <i class="nav-icon fas fa-building text-cyan"></i>
                   <p>Suppliers & Firms</p>
               </a>
           </li>

           <li class="nav-item {{ Request::routeIs('divhr.employelist*') || Request::routeIs('divhr.employeedetail*') || Request::routeIs('hr.navy_civilians') || Request::routeIs('hr.pn_officers') || Request::routeIs('hr.pn_sailors') || Request::routeIs('divhr.attendance*') || Request::routeIs('divhr.salary.requisitions*') ? 'menu-open' : '' }}">
               <a href="#" class="nav-link {{ Request::routeIs('divhr.employelist*') || Request::routeIs('divhr.employeedetail*') || Request::routeIs('hr.navy_civilians') || Request::routeIs('hr.pn_officers') || Request::routeIs('hr.pn_sailors') || Request::routeIs('divhr.attendance*') || Request::routeIs('divhr.salary.requisitions*') ? 'active' : '' }}">
                   <i class="nav-icon fas fa-users text-primary"></i>
                   <p>Human Resources <span class="badge badge-blinking-red badge-hr-parent {{ $sbHr > 0 ? '' : 'd-none' }} ml-1">{{ $sbHr }}</span> <i class="right fas fa-angle-left"></i></p>
               </a>
               <ul class="nav nav-treeview">
                   <li class="nav-item">
                       <a href="{{ route('divhr.employelist') }}" class="nav-link {{ Request::routeIs('divhr.employelist*') || Request::routeIs('divhr.employeedetail*') ? 'active' : '' }}">
                           <i class="fas fa-user-tie nav-icon text-info"></i><p>Hired Employees <span class="badge badge-blinking-red badge-hr-child {{ $sbHr > 0 ? '' : 'd-none' }} ml-1">{{ $sbHr }}</span></p>
                       </a>
                   </li>
                   <li class="nav-item">
                       <a href="{{ route('hr.navy_civilians') }}" class="nav-link {{ Request::routeIs('hr.navy_civilians') ? 'active' : '' }}">
                           <i class="fas fa-user-shield nav-icon text-success"></i><p>Navy Civilians</p>
                       </a>
                   </li>
                   <li class="nav-item">
                       <a href="{{ route('hr.pn_officers') }}" class="nav-link {{ Request::routeIs('hr.pn_officers') ? 'active' : '' }}">
                           <i class="fas fa-user-astronaut nav-icon text-warning"></i><p>PN Officers</p>
                       </a>
                   </li>
                   <li class="nav-item">
                       <a href="{{ route('hr.pn_sailors') }}" class="nav-link {{ Request::routeIs('hr.pn_sailors') ? 'active' : '' }}">
                           <i class="fas fa-anchor nav-icon text-cyan"></i><p>PN CPO / Sailors</p>
                       </a>
                   </li>
                   <li class="nav-item">
                        <a href="{{ route('hr.attendance') }}" class="nav-link {{ Request::routeIs('hr.attendance*') || Request::routeIs('divhr.attendance*') ? 'active' : '' }}">
                            <i class="nav-icon fas fa-calendar-check text-success"></i>
                            <p>Attendance</p>
                        </a>
                    </li>
                   @if(!Auth::user()->isMdDdgDg())
                   <li class="nav-item">
                       <a href="{{ route('divhr.salary.requisitions.index') }}" class="nav-link {{ Request::routeIs('divhr.salary.requisitions*') ? 'active' : '' }}">
                           <i class="nav-icon fas fa-file-invoice-dollar text-warning"></i>
                           <p>Salary Requisitions</p>
                       </a>
                   </li>
                   @endif
               </ul>
           </li>

           <li class="nav-item">
               <a href="{{ route('hr.contract-cases.index') }}" class="nav-link {{ Request::routeIs('hr.contract-cases.*') || Request::routeIs('divhr.contract.*') ? 'active' : '' }}">
                   <i class="nav-icon fas fa-file-signature text-warning"></i>
                   <p>Contract Cases <span class="badge badge-blinking-red badge-ctr-child {{ $sbCtr > 0 ? '' : 'd-none' }} ml-1">{{ $sbCtr }}</span></p>
               </a>
           </li>

           <li class="nav-item">
               <a href="{{ route('hr.reports.index') }}" class="nav-link {{ Request::routeIs('hr.reports.*') ? 'active' : '' }}">
                   <i class="nav-icon fas fa-chart-pie text-cyan"></i>
                   <p>Hr Reports</p>
               </a>
           </li>

      @elseif(in_array(strtolower(trim((string) (Auth::user()->acc_untarea ?? ''))), ['nrdi', 'proc', 'prc', 'fin', 'rdw', 'hqs']))
          @php
              $area = strtolower(trim((string) (Auth::user()->acc_untarea ?? '')));
              $isProc = in_array($area, ['proc', 'prc'], true);
          @endphp
          <li class="nav-header">{{ $isProc ? 'Procurement Directorate' : 'Command View' }}</li>

          <li class="nav-item">
              @php
                  $dashRoute = 'nrdi.dashboard';
                  if ($area === 'fin') {
                      $dashRoute = 'fin.dashboard';
                  } elseif ($isProc) {
                      $dashRoute = 'nrdi.procurement.purchase_cases.index';
                  }
              @endphp
              <a href="{{ route($dashRoute) }}" class="nav-link {{ Request::routeIs($dashRoute) ? 'active' : '' }}">
                  <i class="nav-icon fas fa-tachometer-alt text-primary"></i>
                  <p>Dashboard</p>
              </a>
          </li>

          @if(!$isProc)
          <li class="nav-item">
              <a href="{{ route('nrdi.projects.index') }}" class="nav-link {{ Request::routeIs('nrdi.projects.*') ? 'active' : '' }}">
                  <i class="nav-icon fas fa-folder-open"></i>
                  <p>Projects</p>
              </a>
          </li> 
          @endif

          <li class="nav-item">
              <a href="{{ route('division.finance-of-project.index') }}" class="nav-link {{ Request::routeIs('division.finance-of-project.*') ? 'active' : '' }}">
                  <i class="fas fa-chart-pie nav-icon text-warning"></i>
                  <p>Project Financing</p>
              </a>
          </li>

          <li class="nav-item">
              @php
                  $purchaseRoute = 'nrdi.purchase_cases_new.index';
                  if($isProc) $purchaseRoute = 'nrdi.purchase_cases_new.procurement.index';
                  if($area === 'fin') $purchaseRoute = 'nrdi.purchase_cases_new.finance.index';
              @endphp
              <a href="{{ route($purchaseRoute) }}" class="nav-link {{ Request::routeIs($purchaseRoute) || Request::routeIs('nrdi.purchase_cases_new.*') ? 'active' : '' }}">
                  <i class="nav-icon fas fa-shopping-cart text-info"></i>
                  <p>Purchase Cases <span class="badge badge-blinking-red badge-pur-parent badge-pur-child {{ $sbPur > 0 ? '' : 'd-none' }} ml-1">{{ $sbPur }}</span></p>
              </a>
          </li>

          @if(!$isProc)
          <li class="nav-item">
              <a href="{{ route('nrdi.contract_cases_new.index') }}" class="nav-link {{ Request::routeIs('nrdi.contract_cases_new.*') ? 'active' : '' }}">
                  <i class="nav-icon fas fa-file-signature text-warning"></i>
                  <p>Contract Cases <span class="badge badge-blinking-red badge-ctr-parent badge-ctr-child {{ $sbCtr > 0 ? '' : 'd-none' }} ml-1">{{ $sbCtr }}</span></p>
              </a>
          </li>


          @endif

          <li class="nav-item">
              <a href="{{ route('inventory.assets.index') }}" class="nav-link {{ Request::routeIs('inventory.assets.*') || Request::routeIs('purchase.receipts.*') ? 'active' : '' }}">
                  <i class="nav-icon fas fa-boxes text-success"></i>
                  <p>Inventory & Assets</p>
              </a>
          </li>

          <li class="nav-item">
              <a href="{{ route('nrdi.firms.list') }}" class="nav-link {{ Request::routeIs('nrdi.firms.*') ? 'active' : '' }}">
                  <i class="nav-icon fas fa-building text-cyan"></i>
                  <p>Suppliers & Firms</p>
              </a>
          </li>

           @if(!$isProc)
           {{-- HUMAN RESOURCES (For MD, DDG, DG, Finance) --}}
           <li class="nav-item {{ Request::routeIs('divhr.employelist*') || Request::routeIs('divhr.employeedetail*') || Request::routeIs('hr.navy_civilians') || Request::routeIs('hr.pn_officers') || Request::routeIs('hr.pn_sailors') ? 'menu-open' : '' }}">
               <a href="#" class="nav-link {{ Request::routeIs('divhr.employelist*') || Request::routeIs('divhr.employeedetail*') || Request::routeIs('hr.navy_civilians') || Request::routeIs('hr.pn_officers') || Request::routeIs('hr.pn_sailors') ? 'active' : '' }}">
                   <i class="nav-icon fas fa-users text-primary"></i>
                   <p>Human Resources <span class="badge badge-blinking-red badge-hr-parent {{ $sbHr > 0 ? '' : 'd-none' }} ml-1">{{ $sbHr }}</span> <i class="right fas fa-angle-left"></i></p>
               </a>
               <ul class="nav nav-treeview">
                   <li class="nav-item">
                       <a href="{{ route('divhr.employelist') }}" class="nav-link {{ Request::routeIs('divhr.employelist*') || Request::routeIs('divhr.employeedetail*') ? 'active' : '' }}">
                           <i class="fas fa-user-tie nav-icon text-info"></i><p>Hired Employees <span class="badge badge-blinking-red badge-hr-child {{ $sbHr > 0 ? '' : 'd-none' }} ml-1">{{ $sbHr }}</span></p>
                       </a>
                   </li>
                   <li class="nav-item">
                       <a href="{{ route('hr.navy_civilians') }}" class="nav-link {{ Request::routeIs('hr.navy_civilians') ? 'active' : '' }}">
                           <i class="fas fa-user-shield nav-icon text-success"></i><p>Navy Civilians</p>
                       </a>
                   </li>
                   <li class="nav-item">
                       <a href="{{ route('hr.pn_officers') }}" class="nav-link {{ Request::routeIs('hr.pn_officers') ? 'active' : '' }}">
                           <i class="fas fa-user-astronaut nav-icon text-warning"></i><p>PN Officers</p>
                       </a>
                   </li>
                   <li class="nav-item">
                       <a href="{{ route('hr.pn_sailors') }}" class="nav-link {{ Request::routeIs('hr.pn_sailors') ? 'active' : '' }}">
                           <i class="fas fa-anchor nav-icon text-cyan"></i><p>PN CPO / Sailors</p>
                       </a>
                   </li>
                    @if(!Auth::user()->isMdDdgDg())
                   <li class="nav-item">
                       <a href="{{ route('divhr.salary.requisitions.index') }}" class="nav-link {{ Request::routeIs('divhr.salary.requisitions*') ? 'active' : '' }}">
                           <i class="fas fa-file-invoice-dollar nav-icon text-warning"></i><p>Salary Requisitions</p>
                       </a>
                   </li>
                    @endif
               </ul>
           </li>
           @endif

          @if(strtolower(trim((string) (Auth::user()->acc_untarea ?? ''))) === 'fin')
          <li class="nav-item">
              <a href="{{ route('finance.accounts.index') }}" class="nav-link {{ Request::routeIs('finance.accounts.*') ? 'active' : '' }}">
                  <i class="nav-icon fas fa-wallet text-cyan"></i>
                  <p>Accounts</p>
              </a>
          </li>
          <li class="nav-item {{ Request::routeIs('fin.payments.*') || Request::routeIs('fin.commitments.*') || Request::routeIs('divhr.salary.*') ? 'menu-open' : '' }}">
              <a href="#" class="nav-link">
                  <i class="nav-icon fas fa-file-invoice-dollar text-warning"></i>
                  <p>
                      Commitments
                      <i class="right fas fa-angle-left"></i>
                  </p>
              </a>
              <ul class="nav nav-treeview">
                  <li class="nav-item">
                      <a href="{{ route('fin.payments.index') }}" class="nav-link {{ Request::routeIs('fin.payments.*') ? 'active' : '' }}">
                          <i class="fas fa-shopping-cart nav-icon text-info"></i>
                          <p>Purchase Case</p>
                      </a>
                  </li>
                  <li class="nav-item">
                      <a href="{{ route('divhr.salary.requisitions.index') }}" class="nav-link {{ Request::routeIs('divhr.salary.requisitions.*') ? 'active' : '' }}">
                          <i class="fas fa-file-invoice-dollar nav-icon text-warning"></i>
                          <p>Salary Requisitions</p>
                      </a>
                  </li>
                  <li class="nav-item">
                      <a href="{{ route('divhr.salary.orders.index') }}" class="nav-link {{ Request::routeIs('divhr.salary.orders.*') ? 'active' : '' }}">
                          <i class="fas fa-money-check-alt nav-icon text-success"></i>
                          <p>Salary Orders</p>
                      </a>
                  </li>
              </ul>
          </li>
          @endif

          @if($isProc)
          <!-- Dedicated REPORTS Dropdown for Procurement -->
          <li class="nav-item {{ Request::routeIs('nrdi.procurement.reports.*') || Request::is('*/it-template*') ? 'menu-open' : '' }}">
              <a href="#" class="nav-link {{ Request::routeIs('nrdi.procurement.reports.*') || Request::is('*/it-template*') ? 'active' : '' }}">
                  <i class="nav-icon fas fa-chart-line text-cyan"></i>
                  <p>Reports <i class="right fas fa-angle-left"></i></p>
              </a>
              <ul class="nav nav-treeview">
                  <li class="nav-item">
                      <a href="{{ route('nrdi.procurement.reports.index') }}" class="nav-link {{ Request::routeIs('nrdi.procurement.reports.*') ? 'active' : '' }}">
                          <i class="fas fa-boxes nav-icon text-info"></i><p>Inventory & Asset Reports</p>
                      </a>
                  </li>
                  <li class="nav-item">
                      <a href="{{ route('nrdi.purchase_cases_new.it_template') }}" class="nav-link {{ Request::is('*/it-template*') ? 'active' : '' }}">
                          <i class="fas fa-file-alt nav-icon text-warning"></i><p>IT Letter Template</p>
                      </a>
                  </li>
              </ul>
          </li>
          @else
          <!-- Dedicated REPORTS Dropdown for HQ / Finance -->
          <li class="nav-item {{ Request::routeIs('hr.reports.*') || Request::routeIs('fin.reports.*') ? 'menu-open' : '' }}">
              <a href="#" class="nav-link {{ Request::routeIs('hr.reports.*') || Request::routeIs('fin.reports.*') ? 'active' : '' }}">
                  <i class="nav-icon fas fa-chart-line text-cyan"></i>
                  <p>Reports <i class="right fas fa-angle-left"></i></p>
              </a>
              <ul class="nav nav-treeview">
                  @if(strtolower(trim((string) (Auth::user()->acc_untarea ?? ''))) !== 'fin')
                  <li class="nav-item">
                      <a href="{{ route('hr.reports.index') }}" class="nav-link {{ Request::routeIs('hr.reports.*') ? 'active' : '' }}">
                          <i class="fas fa-users-cog nav-icon" style="color: #67e8f9;"></i><p>Hr Reports</p>
                      </a>
                  </li>
                  @endif
                  <li class="nav-item">
                      <a href="{{ route('fin.reports.index') }}" class="nav-link {{ Request::routeIs('fin.reports.*') ? 'active' : '' }}">
                          <i class="fas fa-coins nav-icon text-warning"></i><p>Finance Reports</p>
                      </a>
                  </li>
              </ul>
          </li>
          @endif


      @elseif(strtolower(trim((string) (Auth::user()->acc_untarea ?? ''))) === 'it')
          <li class="nav-header">System Admin</li>

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

          <li class="nav-item">
              <a href="{{ route('division.finance-of-project.index') }}" class="nav-link {{ Request::routeIs('division.finance-of-project.*') ? 'active' : '' }}">
                  <i class="fas fa-chart-pie nav-icon text-warning"></i>
                  <p>Project Financing</p>
              </a>
          </li>

          <!-- Dedicated REPORTS Dropdown for IT -->
          <li class="nav-item {{ Request::routeIs('hr.reports.*') || Request::routeIs('fin.reports.*') ? 'menu-open' : '' }}">
              <a href="#" class="nav-link {{ Request::routeIs('hr.reports.*') || Request::routeIs('fin.reports.*') ? 'active' : '' }}">
                  <i class="nav-icon fas fa-chart-line text-cyan"></i>
                  <p>Reports <i class="right fas fa-angle-left"></i></p>
              </a>
              <ul class="nav nav-treeview">
                  <li class="nav-item">
                      <a href="{{ route('hr.reports.index') }}" class="nav-link {{ Request::routeIs('hr.reports.*') ? 'active' : '' }}">
                          <i class="fas fa-users-cog nav-icon" style="color: #67e8f9;"></i><p>Hr Reports</p>
                      </a>
                  </li>
                  <li class="nav-item">
                      <a href="{{ route('fin.reports.index') }}" class="nav-link {{ Request::routeIs('fin.reports.*') ? 'active' : '' }}">
                          <i class="fas fa-coins nav-icon text-warning"></i><p>Finance Reports</p>
                      </a>
                  </li>
              </ul>
          </li>

          @elseif(strtolower(trim((string) (Auth::user()->acc_untarea ?? ''))) === 'hr')
          <li class="nav-header">Human Resources</li>

          <li class="nav-item">
              <a href="{{ route('nrdi.dashboard') }}" class="nav-link {{ Request::routeIs('nrdi.dashboard') ? 'active' : '' }}">
                  <i class="nav-icon fas fa-tachometer-alt"></i>
                  <p>Dashboard</p>
              </a>
          </li>

          <li class="nav-item">
              <a href="{{ route('divhr.employelist') }}" class="nav-link {{ Request::routeIs('divhr.employelist') || Request::routeIs('divhr.employeedetail') ? 'active' : '' }}">
                  <i class="fas fa-user-check nav-icon"></i>
                  <p>Employees</p>
              </a>
          </li>

          <li class="nav-item">
              <a href="{{ route('nrdi.contract_cases_new.index') }}" class="nav-link {{ Request::routeIs('nrdi.contract_cases_new.*') ? 'active' : '' }}">
                  <i class="nav-icon fas fa-file-signature"></i>
                  <p>Contract Cases</p>
              </a>
          </li>

          <!-- Dedicated REPORTS Dropdown for HR Role -->
          <li class="nav-item {{ Request::routeIs('hr.reports.*') ? 'menu-open' : '' }}">
              <a href="#" class="nav-link {{ Request::routeIs('hr.reports.*') ? 'active' : '' }}">
                  <i class="nav-icon fas fa-chart-line text-cyan"></i>
                  <p>Reports <i class="right fas fa-angle-left"></i></p>
              </a>
              <ul class="nav nav-treeview">
                  <li class="nav-item">
                      <a href="{{ route('hr.reports.index') }}" class="nav-link {{ Request::routeIs('hr.reports.*') ? 'active' : '' }}">
                          <i class="fas fa-users-cog nav-icon" style="color: #67e8f9;"></i><p>Hr Reports</p>
                      </a>
                  </li>
              </ul>
          </li>


          {{-- ========================================================= --}}
          {{-- CASE 3: GENERAL / OTHER DEPARTMENTS (Admin, IS, MTSS) --}}
          {{-- ========================================================= --}}
          @else
          <li class="nav-header">{{ Auth::user()->acc_untname ?? 'General Access' }}</li>
          <li class="nav-item">
              <a href="{{ route('support.tickets.index') }}" class="nav-link">
                  <i class="nav-icon fas fa-headset text-info"></i>
                  <p>Support & Requests</p>
              </a>
          </li>
      @endif

          {{-- GLOBAL SUPPORT / COMPLAINTS & SUGGESTIONS FOR EVERY USER --}}
          @php
              $curUser = Auth::user();
              $sbPendingTickets = 0;
              if ($curUser) {
                  try {
                      $uArea = strtolower(trim((string)($curUser->acc_untarea ?? '')));
                      $isResolv = in_array($uArea, ['it', 'gomoe', 'godmode']) || session('impersonated_by_god');
                      if ($isResolv) {
                          $sbPendingTickets = \Illuminate\Support\Facades\DB::table('sup.tickets')
                              ->whereIn('tkt_status', ['Open', 'In Progress', 'Returned'])
                              ->count();
                      } else {
                          $sbPendingTickets = \Illuminate\Support\Facades\DB::table('sup.tickets')
                              ->where('tkt_user_id', $curUser->acc_id)
                              ->where('tkt_status', 'Returned')
                              ->count();
                      }
                  } catch (\Exception $e) {
                      $sbPendingTickets = 0;
                  }
              }
          @endphp
          <li class="nav-header" style="color: #94a3b8; font-size: 9.5px; letter-spacing: 0.8px; font-weight: 700; margin-top: 10px; border-top: 1px solid rgba(255,255,255,0.08); padding-top: 8px; padding-bottom: 2px;">Helpdesk</li>
          <li class="nav-item">
              <a href="{{ route('support.tickets.index') }}" class="nav-link {{ Request::routeIs('support.tickets.*') ? 'active' : '' }}" style="border-radius: 6px; margin: 1px 6px; padding: 6px 10px;">
                  <i class="nav-icon fas fa-headset text-info" style="font-size: 13px; margin-right: 6px;"></i>
                  <p style="font-size: 11.5px; font-weight: 600; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">
                      Complaints / Suggestions
                      @if($sbPendingTickets > 0)
                          <span class="badge badge-warning badge-pill ml-1 font-weight-bold" style="font-size: 9px; padding: 2px 5px;">{{ $sbPendingTickets }}</span>
                      @endif
                  </p>
              </a>
          </li>

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
  <script src="{{ asset('plugins/select2/js/select2.full.min.js') }}"></script>



    @stack('scripts')
    @yield('scripts')

    @auth
        <script src="{{ asset('js/rdwis-notifications.js') }}"></script>
    @endauth

    {{-- ========================================================= --}}
    {{-- RDWIS OFFLINE DEBUG CONSOLE (FOR AIR-GAPPED TROUBLESHOOTING) --}}
    {{-- ========================================================= --}}
    <div id="rdwisDebugConsole" style="position: fixed; bottom: 10px; right: 10px; z-index: 9999; font-family: monospace;">
        <button onclick="toggleDebug()" style="background: #ff3e3e; color: #fff; border: none; padding: 5px 12px; border-radius: 20px; font-weight: bold; cursor: pointer; box-shadow: var(--rd-shadow-md); font-size: 11px;">
            <i class="fas fa-bug mr-1"></i> DEBUG CONSOLE <span id="debugBadge" class="badge badge-light ml-1" style="display:none;">0</span>
        </button>
        <div id="debugContent" style="display: none; width: 450px; height: 350px; background: var(--rd-surface); color: #00ff00; border: 2px solid #ff3e3e; border-radius: 8px; margin-top: 10px; overflow: hidden; font-size: 11px; box-shadow: 0 10px 30px rgba(0,0,0,0.8); flex-direction: column;">
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
            const log = document.getElementById('debugLog');
            const badge = document.getElementById('debugBadge');
            if (log) log.innerHTML = '';
            if (badge) {
                badge.style.display = 'none';
                badge.innerText = '0';
            }
        }

        function logToDebug(msg, type = 'ERROR') {
            const log = document.getElementById('debugLog');
            const badge = document.getElementById('debugBadge');
            if (!log) return;
            if (log.innerText && log.innerText.includes('Monitoring')) log.innerHTML = '';
            const entry = document.createElement('div');
            entry.style.marginBottom = '5px';
            entry.style.padding = '4px';
            entry.style.borderLeft = type === 'ERROR' ? '3px solid #ff3e3e' : '3px solid #28a745';
            const time = new Date().toLocaleTimeString();
            entry.innerHTML = `<span style="color:#666;">[${time}]</span> <span style="color:${type==='ERROR'?'#ff3e3e':'#28a745'}; font-weight:bold;">${type}:</span> ${msg}`;
            log.prepend(entry);
            
            if (badge) {
                badge.style.display = 'inline-block';
                badge.innerText = parseInt(badge.innerText || '0') + 1;
            }
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
                if (typeof $.fn.select2 !== 'undefined') {
                    $('.select2').select2({ width: '100%', theme: 'bootstrap4' });
                }
            });
        });

        // Enhanced Logout Confirmation Handler
        function handleLogoutClick(e) {
            e.preventDefault();
            if (typeof Swal !== 'undefined') {
                Swal.fire({
                    title: 'Confirm Sign Out',
                    text: 'Are you sure you want to sign out from RDWIS?',
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#dc3545',
                    cancelButtonColor: '#77736B',
                    confirmButtonText: '<i class="fas fa-sign-out-alt mr-1"></i> Yes, Logout',
                    cancelButtonText: 'Cancel',
                    reverseButtons: true,
                    focusCancel: true,
                    background: 'var(--rd-surface)',
                    color: 'var(--rd-text1)',
                    customClass: {
                        popup: 'shadow-lg border'
                    }
                }).then((result) => {
                    if (result.isConfirmed) {
                        Swal.fire({
                            title: 'Signing Out...',
                            text: 'Please wait while your session is terminated.',
                            icon: 'info',
                            showConfirmButton: false,
                            timer: 900,
                            background: 'var(--rd-surface)',
                            color: 'var(--rd-text1)'
                        });
                        setTimeout(function() {
                            document.getElementById('global-logout-form').submit();
                        }, 700);
                    }
                });
            } else {
                if (confirm('Are you sure you want to log out of RDWIS?')) {
                    document.getElementById('global-logout-form').submit();
                }
            }
        }
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
    <!-- Preloader Auto-dismiss and complete cleanup -->
    <script>
        (function() {
            function removePreloader() {
                var preloader = document.querySelector('.preloader');
                if (preloader) {
                    preloader.style.opacity = '0';
                    preloader.style.backdropFilter = 'none';
                    preloader.style.webkitBackdropFilter = 'none';
                    preloader.style.pointerEvents = 'none';
                    setTimeout(function() {
                        if (preloader && preloader.parentNode) {
                            preloader.parentNode.removeChild(preloader);
                        }
                    }, 350);
                }
            }
            if (document.readyState === 'complete') {
                setTimeout(removePreloader, 100);
            } else {
                window.addEventListener('load', function() {
                    setTimeout(removePreloader, 150);
                });
            }
            // Absolute fallback after 1s
            setTimeout(removePreloader, 1000);
        })();
    </script>
    <!-- Universal Sidebar Title Case Normalizer -->
    <script>
        (function() {
            function toTitleCase(str) {
                return str.replace(/\b([a-zA-Z])([a-zA-Z]*)/g, function(match, first, rest) {
                    return first.toUpperCase() + rest.toLowerCase();
                });
            }

            function formatSidebarLabels() {
                var items = document.querySelectorAll('.main-sidebar .nav-sidebar .nav-link p, .main-sidebar .nav-sidebar .nav-header');
                items.forEach(function(el) {
                    el.childNodes.forEach(function(node) {
                        if (node.nodeType === Node.TEXT_NODE && node.nodeValue && node.nodeValue.trim().length > 0) {
                            node.nodeValue = toTitleCase(node.nodeValue);
                        }
                    });
                });
            }

            if (document.readyState === 'loading') {
                document.addEventListener('DOMContentLoaded', formatSidebarLabels);
            } else {
                formatSidebarLabels();
            }
            if (window.jQuery) {
                $(document).on('expanded.lte.treeview shown.bs.collapse', formatSidebarLabels);
            }
            var sidebar = document.querySelector('.main-sidebar .nav-sidebar');
            if (sidebar && window.MutationObserver) {
                var observer = new MutationObserver(function() {
                    observer.disconnect();
                    formatSidebarLabels();
                    observer.observe(sidebar, { childList: true, subtree: true });
                });
                observer.observe(sidebar, { childList: true, subtree: true });
            }

            try {
                sessionStorage.removeItem('rd_visited_links');
            } catch(e) {}

            // Foolproof runtime hover listener for entity title links
            document.addEventListener('mouseover', function(e) {
                var link = e.target.closest('.rd-entity-title-link');
                if (link) {
                    var isDark = link.closest('.table-dark');
                    link.style.setProperty('color', isDark ? '#38bdf8' : '#2563eb', 'important');
                    link.style.setProperty('text-decoration', 'none', 'important');
                }
            }, true);

            document.addEventListener('mouseout', function(e) {
                var link = e.target.closest('.rd-entity-title-link');
                if (link) {
                    var isDark = link.closest('.table-dark');
                    link.style.setProperty('color', isDark ? '#f8fafc' : '#111827', 'important');
                    link.style.setProperty('text-decoration', 'none', 'important');
                }
            }, true);
        })();
    </script>
    @include('partials.live_document_modal')
    @include('pwa.install-banner')
  </body>
</html>
