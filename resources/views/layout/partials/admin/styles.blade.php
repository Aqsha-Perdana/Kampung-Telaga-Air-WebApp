<style>
  /* Prevent content clipping by fixed viewport */
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

  /* Keep main content scrollable */
  .body-wrapper {
    flex: 1;
    height: 100vh;
    overflow-y: auto !important;
    overflow-x: hidden;
    position: relative;
    display: flex;
    flex-direction: column;
    isolation: isolate;
  }
  
  .container-fluid {
    padding-bottom: 2rem;
    position: relative;
    z-index: 1;
  }

  .app-header {
    position: sticky;
    top: 0;
    z-index: 1200;
    background-color: #fff;
  }

  .admin-notif-count {
    display: none;
    position: absolute;
    top: 4px;
    right: 4px;
    font-size: 10px;
    line-height: 1;
    min-width: 18px;
    text-align: center;
  }

  #adminNotifPing {
    display: none;
  }

  #adminNotifPing.is-visible {
    display: block;
  }

  .admin-notif-dropdown {
    width: min(460px, 92vw);
    max-height: 460px;
    overflow: hidden;
    padding: 0;
  }

  .admin-notif-header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 12px;
    padding: 12px 14px;
    border-bottom: 1px solid #e9ecef;
    background: #fff;
  }

  .admin-notif-list {
    max-height: 390px;
    overflow-y: auto;
    background: #fff;
  }

  .admin-notif-item {
    display: block;
    padding: 11px 14px;
    border-bottom: 1px solid #f1f3f5;
    text-decoration: none;
    color: inherit;
    transition: background-color 0.2s ease;
  }

  .admin-notif-item:hover {
    background: #f8fafc;
  }

  .admin-notif-item.is-unread {
    background: #f8fbff;
  }

  .admin-notif-item:last-child {
    border-bottom: 0;
  }

  .admin-notif-title {
    font-size: 13px;
    font-weight: 600;
    color: #111827;
    margin: 0 0 4px;
  }

  .admin-notif-text {
    font-size: 12px;
    color: #4b5563;
    margin: 0;
  }

  .admin-notif-meta {
    margin-top: 6px;
    font-size: 11px;
    color: #6b7280;
    display: flex;
    gap: 8px;
    flex-wrap: wrap;
  }

  .admin-notif-empty {
    padding: 16px 14px;
    font-size: 12px;
    color: #6b7280;
  }
  
  .body-wrapper::before,
  .body-wrapper::after,
  .container-fluid::before,
  .container-fluid::after {
    display: none !important;
  }

  /* Sidebar only scrolls inside nav area */
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

  .sidebar-nav::-webkit-scrollbar {
    width: 5px;
  }

  .sidebar-nav::-webkit-scrollbar-track {
    background: transparent;
  }

  .sidebar-nav::-webkit-scrollbar-thumb {
    background: rgba(0, 0, 0, 0.2);
    border-radius: 10px;
  }

  .sidebar-nav::-webkit-scrollbar-thumb:hover {
    background: rgba(0, 0, 0, 0.3);
  }

  .sidebar-link {
    transition: background-color 0.2s ease, color 0.2s ease, transform 0.2s ease, box-shadow 0.2s ease;
  }

  .sidebar-item.active > .sidebar-link {
    transform: translateX(2px);
  }

  .sidebar-item.is-pending > .sidebar-link {
    background-color: #eef2ff;
    color: #3640c5;
  }

  .body-wrapper,
  .container-fluid {
    transition: opacity 0.2s ease, transform 0.2s ease;
  }

  .page-wrapper.is-leaving .body-wrapper,
  .page-wrapper.is-leaving .container-fluid {
    opacity: 0.65;
    transform: translateY(3px);
    pointer-events: none;
  }

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

  .brand-logo {
    flex-shrink: 0;
  }

  .px-3.mt-2.mb-3 {
    flex-shrink: 0;
  }

  .body-wrapper {
    margin-left: 270px;
  }

  .report-header-card {
    position: relative;
    z-index: 1050;
  }

  .report-header-card .card-body {
    overflow: visible !important;
  }

  .custom-report-dropdown {
    z-index: 9999 !important;
    box-shadow: 0 10px 25px rgba(0, 0, 0, 0.1);
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

