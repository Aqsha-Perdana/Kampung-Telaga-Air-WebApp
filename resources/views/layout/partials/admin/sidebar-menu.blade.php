@php
  $isAdmin = $adminUser->isAdmin();
  $canMasterData = $adminUser->canAccessMasterData();
  $canTransaction = $adminUser->canAccessTransaction();
  $canFinancial = $adminUser->canAccessFinancial();
@endphp

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
    
    <div class="px-3 mt-2 mb-3">
      <div class="text-center">
        <span class="role-badge {{ $isAdmin ? 'role-admin' : 'role-pengelola' }}">
          {{ $isAdmin ? 'Admin' : 'Manager' }}
        </span>
      </div>
    </div>
    
    <nav class="sidebar-nav scroll-sidebar" data-simplebar="">
      <ul id="sidebarnav">
        <li class="nav-small-cap">
          <i class="ti ti-dots nav-small-cap-icon fs-4"></i>
          <span class="hide-menu">Home</span>
        </li>
        <li class="sidebar-item {{ request()->is('admin/dashboard*') ? 'active' : '' }}">
          <a class="sidebar-link" href="{{ url('/admin/dashboard') }}" aria-expanded="false">
            <span>
              <i class="ti ti-layout-dashboard"></i>
            </span>
            <span class="hide-menu">Dashboard</span>
          </a>
        </li>
        <li class="sidebar-item {{ request()->routeIs('sales.*') ? 'active' : '' }}">
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
        <li class="sidebar-item {{ request()->routeIs('admin.notifications.*') ? 'active' : '' }}">
          <a class="sidebar-link" href="{{ route('admin.notifications.index') }}" aria-expanded="false">
            <span>
              <i class="ti ti-bell"></i>
            </span>
            <span class="hide-menu">Notifications</span>
          </a>
        </li>
        <li class="sidebar-item {{ request()->routeIs('admin.ai-center.*') ? 'active' : '' }}">
          <a class="sidebar-link" href="{{ route('admin.ai-center.index') }}" aria-expanded="false">
            <span>
              <i class="ti ti-sparkles"></i>
            </span>
            <span class="hide-menu">AI Center</span>
          </a>
        </li>
        
        @if($canMasterData)
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
        
        @if($canTransaction)
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
        
        @if($canFinancial)
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

        <li class="nav-small-cap">
          <i class="ti ti-dots nav-small-cap-icon fs-4"></i>
          <span class="hide-menu">Account</span>
        </li>
        <li class="sidebar-item {{ request()->routeIs('admin.profile*') ? 'active' : '' }}">
          <a class="sidebar-link" href="{{ route('admin.profile') }}" aria-expanded="false">
            <span>
              <i class="ti ti-user-circle"></i>
            </span>
            <span class="hide-menu">Profile</span>
          </a>
        </li>

        @if($canMasterData)
        <li class="nav-small-cap">
          <i class="ti ti-dots nav-small-cap-icon fs-4"></i>
          <span class="hide-menu">User Management</span>
        </li>
        <li class="sidebar-item {{ request()->routeIs('admin.users.*') ? 'active' : '' }}">
          <a class="sidebar-link" href="{{ route('admin.users.index') }}" aria-expanded="false">
            <span>
              <i class="ti ti-users"></i>
            </span>
            <span class="hide-menu">Users</span>
          </a>
        </li>
        @endif
      </ul>
    </nav>
  </div>
</aside>
<!-- Sidebar End -->

