<!doctype html>
<html lang="en">

<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Admin Page - Kampung Telaga Air</title>
  <link rel="shortcut icon" type="image/png" href="{{ asset('assets/images/logos/logo.png') }}" />
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@tabler/icons-webfont@latest/tabler-icons.min.css">
  <link rel="stylesheet" href="{{ asset('assets/css/styles.min.css') }}" />
  <style>
    /* PERBAIKAN: Hilangkan overflow: hidden yang memotong konten */
    html, body {
      height: 100vh;
      margin: 0;
      padding: 0;
      overflow: hidden;
    }

    .page-wrapper {
      height: 100vh;
      overflow: hidden;
      display: flex;
    }

    /* PERBAIKAN: body-wrapper harus bisa scroll */
    .body-wrapper {
      flex: 1;
      height: 100vh;
      overflow-y: auto !important; /* ✅ PENTING: Biarkan scroll */
      overflow-x: hidden;
      position: relative;
    }
    
    .container-fluid {
      padding-bottom: 2rem;
      /* Hilangkan semua overflow restrictions */
    }
    
    /* Hilangkan pseudo-elements yang tidak perlu */
    .body-wrapper::before,
    .body-wrapper::after,
    .container-fluid::before,
    .container-fluid::after {
      display: none !important;
    }

    /* Fix sidebar scroll - ONLY sidebar should scroll */
    .left-sidebar {
      width: 270px;
      height: 100vh;
      overflow: hidden;
      display: flex;
      flex-direction: column;
      position: fixed;
      left: 0;
      top: 0;
      z-index: 1040;
    }

    .left-sidebar > div {
      display: flex;
      flex-direction: column;
      height: 100%;
      overflow: hidden;
    }

    .sidebar-nav {
      flex: 1;
      overflow-y: auto;
      overflow-x: hidden;
      padding-bottom: 2rem;
      margin-right: -10px;
      padding-right: 10px;
    }

    /* Custom scrollbar untuk sidebar */
    .sidebar-nav::-webkit-scrollbar {
      width: 5px;
    }

    .sidebar-nav::-webkit-scrollbar-track {
      background: transparent;
    }

    .sidebar-nav::-webkit-scrollbar-thumb {
      background: rgba(0,0,0,0.2);
      border-radius: 10px;
    }

    .sidebar-nav::-webkit-scrollbar-thumb:hover {
      background: rgba(0,0,0,0.3);
    }

    /* Role badge styling */
    .role-badge {
      display: inline-block;
      padding: 4px 12px;
      border-radius: 12px;
      font-size: 11px;
      font-weight: 600;
      text-transform: uppercase;
      letter-spacing: 0.5px;
      margin-top: 8px;
    }
    
    .role-admin {
      background: linear-gradient(135deg, #83b1f1 0%, #3640c5 100%);
      color: white;
    }
    
    .role-pengelola {
      background: linear-gradient(135deg, #abebffff 0%, #47789eff 100%);
      color: white;
    }

    /* Ensure brand logo doesn't scroll */
    .brand-logo {
      flex-shrink: 0;
    }

    .px-3.mt-2.mb-3 {
      flex-shrink: 0;
    }

    /* Main content area adjustment */
    .body-wrapper {
      margin-left: 270px; /* Sesuaikan dengan lebar sidebar */
    }

    /* Memastikan card header memiliki lapisan yang lebih tinggi dari card KPI */
    .report-header-card {
      position: relative;
      z-index: 1050;
    }

    .report-header-card .card-body {
      overflow: visible !important;
    }

    .custom-report-dropdown {
      z-index: 9999 !important;
      box-shadow: 0 10px 25px rgba(0,0,0,0.1);
      border: none;
      padding: 0.5rem;
    }

    .dropdown-item:hover {
      background-color: #f8f9fa;
      border-radius: 4px;
    }

    .report-header-card {
      position: relative;
      z-index: 1100 !important;
    }

    .custom-report-dropdown {
      position: absolute !important;
      inset: auto 0 auto auto !important;
      z-index: 99999 !important;
      transform: translate3d(0px, 40px, 0px) !important;
      display: none;
    }

    .dropdown-menu.show {
      display: block !important;
    }

    .row.mb-4 + .row, .row.mb-4 {
      position: relative;
      z-index: 1;
    }

    /* Responsive adjustments */
    @media (max-width: 1199px) {
      .left-sidebar {
        transform: translateX(-100%);
        transition: transform 0.3s ease;
      }

      .left-sidebar.show-sidebar {
        transform: translateX(0);
      }

      .body-wrapper {
        margin-left: 0 !important;
      }
    }
  </style>
  @yield('styles')
</head>

<body>
  <div class="page-wrapper" id="main-wrapper" data-layout="vertical" data-navbarbg="skin6" data-sidebartype="full"
    data-sidebar-position="fixed" data-header-position="fixed">
    
    <!-- Sidebar Start -->
    <aside class="left-sidebar">
      <div>
        <div class="brand-logo d-flex align-items-center justify-content-between">
          <a href="{{ url('/') }}" class="text-nowrap logo-img"><br>
            <img src="{{ asset('assets/images/logos/primary-logo.png') }}" width="170" alt="" />
          </a>
          <div class="close-btn d-xl-none d-block sidebartoggler cursor-pointer" id="sidebarCollapse">
            <i class="ti ti-x fs-8"></i>
          </div>
        </div>
        
        <!-- User Role Info -->
        <div class="px-3 mt-2 mb-3">
          <div class="text-center">
            <span class="role-badge {{ Auth::guard('admin')->user()->isAdmin() ? 'role-admin' : 'role-pengelola' }}">
              {{ Auth::guard('admin')->user()->isAdmin() ? 'Admin' : 'Manager' }}
            </span>
          </div>
        </div>
        
        <!-- Sidebar navigation-->
        <nav class="sidebar-nav scroll-sidebar" data-simplebar="">
          <ul id="sidebarnav">
            <!-- Dashboard - Available for both roles -->
            <li class="nav-small-cap">
              <i class="ti ti-dots nav-small-cap-icon fs-4"></i>
              <span class="hide-menu">Home</span>
            </li>
            <li class="sidebar-item">
              <a class="sidebar-link" href="{{ url('/admin/dashboard') }}" aria-expanded="false">
                <span>
                  <i class="ti ti-layout-dashboard"></i>
                </span>
                <span class="hide-menu">Dashboard</span>
              </a>
            </li>
            <li class="sidebar-item">
              <a class="sidebar-link" href="{{ route('sales.index') }}" aria-expanded="false">
                <span>
                  <i class="ti ti-shopping-cart"></i>
                </span>
                <span class="hide-menu">Sales Record</span>
              </a>
            </li>
            <li class="sidebar-item {{ request()->routeIs('calendar.index*') ? 'active' : '' }}">
              <a class="sidebar-link" href="{{ route('calendar.index') }}" aria-expanded="false">
                <span>
                  <i class="ti ti-calendar-event"></i>
                </span>
                <span class="hide-menu">Calendar</span>
              </a>
            </li>
            
            <!-- Master Data - Only for Admin -->
            @if(Auth::guard('admin')->user()->canAccessMasterData())
            <li class="nav-small-cap">
              <i class="ti ti-dots nav-small-cap-icon fs-4"></i>
              <span class="hide-menu">Master Data</span>
            </li>
            <li class="sidebar-item {{ request()->routeIs('destinasis.*') ? 'active' : '' }}">
              <a class="sidebar-link" href="{{ route('destinasis.index') }}" aria-expanded="false">
                <span>
                  <i class="ti ti-article"></i>
                </span>
                <span class="hide-menu">Destination</span>
              </a>
            </li>
            <li class="sidebar-item {{ request()->routeIs('homestays.*') ? 'active' : '' }}">
              <a class="sidebar-link" href="{{ route('homestays.index') }}" aria-expanded="false">
                <span>
                  <i class="ti ti-home"></i>
                </span>
                <span class="hide-menu">Homestay</span>
              </a>
            </li>
            <li class="sidebar-item {{ request()->routeIs('boats.*') ? 'active' : '' }}">
              <a class="sidebar-link" href="{{ route('boats.index') }}" aria-expanded="false">
                <span>
                  <i class="ti ti-anchor"></i>
                </span>
                <span class="hide-menu">Boat</span>
              </a>
            </li>
            <li class="sidebar-item {{ request()->routeIs('culinary.*') ? 'active' : '' }}">
              <a class="sidebar-link" href="{{ route('culinaries.index') }}" aria-expanded="false">
                <span>
                  <i class="ti ti-tools-kitchen-2"></i>
                </span>
                <span class="hide-menu">Culinary</span>
              </a>
            </li>
            <li class="sidebar-item {{ request()->routeIs('kiosk.*') ? 'active' : '' }}">
              <a class="sidebar-link" href="{{ route('kiosks.index') }}" aria-expanded="false">
                <span>
                  <i class="ti ti-building-store"></i>
                </span>
                <span class="hide-menu">Kiosk</span>
              </a>
            </li>
            <li class="sidebar-item {{ request()->routeIs('beban-operasional.*') ? 'active' : '' }}">
              <a class="sidebar-link" href="{{ route('beban-operasional.index') }}" aria-expanded="false">
                <span>
                  <i class="ti ti-report-money"></i>
                </span>
                <span class="hide-menu">Operational Expense</span>
              </a>
            </li>

            <li class="nav-small-cap">
              <i class="ti ti-dots nav-small-cap-icon fs-4"></i>
              <span class="hide-menu">360 Degree</span>
            </li>
            <li class="sidebar-item {{ request()->routeIs('admin.footage360.*') ? 'active' : '' }}">
              <a class="sidebar-link" href="{{ route('footage360.index') }}" aria-expanded="false">
                <span>
                  <i class="ti ti-camera"></i>
                </span>
                <span class="hide-menu">360 Content</span>
              </a>
            </li>
            @endif
            
            <!-- Transaction - Only for Admin -->
            @if(Auth::guard('admin')->user()->canAccessTransaction())
            <li class="nav-small-cap">
              <i class="ti ti-dots nav-small-cap-icon fs-4"></i>
              <span class="hide-menu">Manage Package</span>
            </li>
            <li class="sidebar-item {{ request()->routeIs('paketWisatas.*') ? 'active' : '' }}">
              <a class="sidebar-link" href="{{ route('paket-wisata.index') }}" aria-expanded="false">
                <span>
                  <i class="ti ti-package"></i>
                </span>
                <span class="hide-menu">Tour Package</span>
              </a>
            </li>
            @endif
            
            <!-- Financial - Available for both roles -->
            @if(Auth::guard('admin')->user()->canAccessFinancial())
            <li class="nav-small-cap">
              <i class="ti ti-dots nav-small-cap-icon fs-4"></i>
              <span class="hide-menu">Financial</span>
            </li>
            <li class="sidebar-item {{ request()->routeIs('financial-reports.*') ? 'active' : '' }}">
              <a class="sidebar-link" href="{{ route('financial-reports.index') }}" aria-expanded="false">
                <span>
                  <i class="ti ti-report-money"></i>
                </span>
                <span class="hide-menu">Financial Reports</span>
              </a>
            </li>
            @endif
          </ul>
        </nav>
      </div>
    </aside>
    <!--  Sidebar End -->
    
    <!--  Main wrapper -->
    <div class="body-wrapper" style="height: 100vh; overflow-y: auto;">
      <!--  Header Start -->
      <header class="app-header" style="top: 0; z-index: 1000; background-color: #fff;">
        <nav class="navbar navbar-expand-lg navbar-light">
          <ul class="navbar-nav">
            <li class="nav-item d-block d-xl-none">
              <a class="nav-link sidebartoggler nav-icon-hover" id="headerCollapse" href="javascript:void(0)">
                <i class="ti ti-menu-2"></i>
              </a>
            </li>
            <li class="nav-item">
              <a class="nav-link nav-icon-hover" href="javascript:void(0)">
                <i class="ti ti-bell-ringing"></i>
                <div class="notification bg-primary rounded-circle"></div>
              </a>
            </li>
          </ul>
          <div class="navbar-collapse justify-content-end px-0" id="navbarNav">
            <ul class="navbar-nav flex-row ms-auto align-items-center justify-content-end">
              <li class="nav-item dropdown">
                <a class="nav-link nav-icon-hover" href="javascript:void(0)" id="drop2" data-bs-toggle="dropdown"
                  aria-expanded="false">
                  <img src="{{ asset('assets/images/profile/user-1.jpg') }}" alt="" width="35" height="35" class="rounded-circle">
                </a>
                <div class="dropdown-menu dropdown-menu-end dropdown-menu-animate-up" aria-labelledby="drop2">
                  <div class="message-body">
                    <div class="px-3 py-2 border-bottom">
                      <h6 class="mb-0">{{ Auth::guard('admin')->user()->name }}</h6>
                      <small class="text-muted">{{ Auth::guard('admin')->user()->email }}</small>
                      <br>
                      <span class="badge bg-primary mt-1">
                        {{ Auth::guard('admin')->user()->isAdmin() ? 'Admin' : 'Pengelola' }}
                      </span>
                      </div>
                    <a href="{{ route('admin.logout') }}" class="btn btn-outline-primary mx-3 mt-2 d-block"
                       onclick="event.preventDefault(); document.getElementById('logout-form').submit();">
                      Logout
                    </a>
                    <form id="logout-form" action="{{ route('admin.logout') }}" method="POST" class="d-none">
                      @csrf
                    </form>
                  </div>
                </div>
              </li>
            </ul>
          </div>
        </nav>
      </header>
      <!--  Header End -->
      
      <div class="container-fluid" style="padding-bottom: 2rem;">
        @yield('content')
      </div>
    </div>
  </div>
  
  {{-- Base Scripts --}}
  <script src="{{ asset('assets/libs/jquery/dist/jquery.min.js') }}"></script>
  <script src="{{ asset('assets/libs/bootstrap/dist/js/bootstrap.bundle.min.js') }}"></script>
  <script src="{{ asset('assets/js/sidebarmenu.js') }}"></script>
  <script src="{{ asset('assets/js/app.min.js') }}"></script>
  <script src="{{ asset('assets/libs/simplebar/dist/simplebar.js') }}"></script>
  
  {{-- Conditional Scripts --}}
  @if(request()->is('admin/dashboard*'))
    <script src="{{ asset('assets/libs/apexcharts/dist/apexcharts.min.js') }}"></script>
    <script src="{{ asset('assets/js/dashboard.js') }}"></script>
  @endif
  
  @yield('scripts')
  
  @stack('scripts')
  
  <script>
    document.addEventListener('DOMContentLoaded', function() {
      // Clean up unwanted SVG/Canvas elements on dashboard
      if (window.location.pathname.includes('/admin/dashboard')) {
        document.querySelectorAll('.body-wrapper > svg, .body-wrapper > canvas').forEach(function(el) {
          if (!el.id || (el.id !== 'revenueChart' && el.id !== 'distributionChart')) {
            el.remove();
          }
        });
        
        document.querySelectorAll('.container-fluid > svg, .container-fluid > canvas').forEach(function(el) {
          if (!el.id || (el.id !== 'revenueChart' && el.id !== 'distributionChart')) {
            el.remove();
          }
        });
      }
    });
  </script>
</body>

</html>