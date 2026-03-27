<!-- Header Start -->
<header class="app-header">
  <nav class="navbar navbar-expand-lg navbar-light">
    <ul class="navbar-nav">
      <li class="nav-item d-block d-xl-none">
        <a class="nav-link sidebartoggler nav-icon-hover" id="headerCollapse" href="javascript:void(0)">
          <i class="ti ti-menu-2"></i>
        </a>
      </li>
      <li class="nav-item dropdown">
        <a class="nav-link nav-icon-hover admin-notif-trigger" href="javascript:void(0)" id="adminNotifToggle" data-bs-toggle="dropdown"
          aria-expanded="false" aria-label="Admin notifications">
          <i class="ti ti-bell-ringing"></i>
          <div class="notification bg-primary rounded-circle" id="adminNotifPing"></div>
          <span class="badge bg-danger rounded-pill admin-notif-count" id="adminNotifCount">0</span>
        </a>
        <div class="dropdown-menu admin-notif-dropdown" aria-labelledby="adminNotifToggle">
          <div class="admin-notif-header">
            <div>
              <h6 class="mb-0">Notifications</h6>
              <small class="text-muted">Live activity from bookings, payments, and refunds</small>
            </div>
            <button type="button" class="btn btn-link p-0 small" id="adminNotifMarkRead">Mark all as read</button>
          </div>
          <div class="admin-notif-list" id="adminNotifList">
            <div class="admin-notif-empty">
              <strong>No notifications yet</strong>
              <span>New admin activity will appear here automatically.</span>
            </div>
          </div>
          <div class="admin-notif-footer border-top px-3 py-2 d-flex justify-content-between align-items-center gap-2">
            <small class="text-muted" id="adminNotifFooterText">Latest 10 items</small>
            <a href="{{ route('admin.notifications.index') }}" class="btn btn-sm btn-outline-primary">
              <i class="ti ti-list-details me-1"></i> View all
            </a>
          </div>
        </div>
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
                <h6 class="mb-0">{{ $adminUser->name }}</h6>
                <small class="text-muted">{{ $adminUser->email }}</small>
                <br>
                <span class="badge bg-primary mt-1">
                  {{ $adminUser->isAdmin() ? 'Admin' : 'Manager' }}
                </span>
              </div>
              <a href="{{ route('admin.profile') }}" class="d-block px-3 py-2 border-bottom text-decoration-none">
                <i class="ti ti-user-circle me-1"></i> Profile
              </a>
              <a href="{{ route('admin.logout') }}" class="btn btn-outline-primary mx-3 mt-2 d-block no-prefetch"
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
<!-- Header End -->