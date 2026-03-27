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

  .admin-ai-widget {
    position: fixed;
    right: 24px;
    bottom: 24px;
    z-index: 1300;
  }

  .admin-ai-fab {
    display: inline-flex;
    align-items: center;
    gap: 12px;
    border: 0;
    border-radius: 18px;
    padding: 12px 16px;
    background: linear-gradient(135deg, #1d4ed8 0%, #2563eb 45%, #60a5fa 100%);
    color: #fff;
    box-shadow: 0 20px 40px rgba(37, 99, 235, 0.28);
    transition: transform 0.2s ease, box-shadow 0.2s ease, opacity 0.2s ease;
  }

  .admin-ai-fab:hover {
    transform: translateY(-2px);
    box-shadow: 0 24px 44px rgba(37, 99, 235, 0.32);
  }

  .admin-ai-fab.is-hidden {
    opacity: 0;
    pointer-events: none;
  }

  .admin-ai-fab-icon {
    width: 42px;
    height: 42px;
    border-radius: 14px;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    background: rgba(255, 255, 255, 0.16);
    font-size: 1.2rem;
  }

  .admin-ai-fab-text {
    display: flex;
    flex-direction: column;
    align-items: flex-start;
    line-height: 1.15;
  }

  .admin-ai-fab-text small {
    opacity: 0.82;
    font-size: 0.72rem;
  }

  .admin-ai-panel {
    width: min(420px, calc(100vw - 28px));
    max-height: min(78vh, 720px);
    display: none;
    flex-direction: column;
    overflow: hidden;
    border: 1px solid #dbe7ff;
    border-radius: 26px;
    background: rgba(255, 255, 255, 0.98);
    box-shadow: 0 24px 60px rgba(15, 23, 42, 0.18);
    backdrop-filter: blur(10px);
  }

  .admin-ai-panel.is-open {
    display: flex;
  }

  .admin-ai-panel-header {
    display: flex;
    align-items: flex-start;
    justify-content: space-between;
    gap: 12px;
    padding: 18px 18px 14px;
    background: linear-gradient(135deg, #eff6ff 0%, #ffffff 100%);
    border-bottom: 1px solid #e6eefc;
  }

  .admin-ai-panel-header h6 {
    margin: 0;
    font-weight: 700;
    color: #0f172a;
  }

  .admin-ai-panel-header p {
    font-size: 0.79rem;
    color: #5b6476;
  }

  .admin-ai-close {
    width: 36px;
    height: 36px;
    border-radius: 12px;
    border: 0;
    background: #fff;
    color: #0f172a;
    box-shadow: inset 0 0 0 1px #e2e8f0;
  }

  .admin-ai-panel-body {
    padding: 16px 18px 12px;
    display: flex;
    flex-direction: column;
    gap: 14px;
    overflow: hidden;
  }

  .admin-ai-prompts {
    display: flex;
    flex-wrap: wrap;
    gap: 8px;
  }

  .admin-ai-prompt {
    border: 1px solid #dbe7ff;
    border-radius: 999px;
    background: #f8fbff;
    color: #1d4ed8;
    padding: 7px 12px;
    font-size: 0.78rem;
    font-weight: 600;
  }

  .admin-ai-chat-board {
    min-height: 280px;
    max-height: calc(78vh - 235px);
    overflow-y: auto;
    padding-right: 4px;
    display: flex;
    flex-direction: column;
    gap: 10px;
  }

  .admin-ai-chat-empty {
    padding: 18px;
    border-radius: 18px;
    background: #f8fafc;
    border: 1px dashed #cbd5e1;
    color: #475569;
    display: flex;
    flex-direction: column;
    gap: 6px;
    font-size: 0.84rem;
  }

  .admin-ai-bubble {
    max-width: 92%;
    padding: 12px 14px;
    border-radius: 18px;
    font-size: 0.85rem;
    line-height: 1.55;
  }

  .admin-ai-bubble.assistant {
    align-self: flex-start;
    background: #f8fafc;
    border: 1px solid #e2e8f0;
    color: #0f172a;
  }

  .admin-ai-bubble.admin {
    align-self: flex-end;
    background: linear-gradient(135deg, #1d4ed8 0%, #2563eb 100%);
    color: #fff;
  }

  .admin-ai-bubble-title {
    font-size: 0.72rem;
    font-weight: 700;
    margin-bottom: 6px;
    opacity: 0.82;
  }

  .admin-ai-bubble-meta {
    margin-top: 8px;
    font-size: 0.72rem;
    opacity: 0.76;
  }

  .admin-ai-panel-footer {
    padding: 14px 18px 18px;
    border-top: 1px solid #edf2f7;
    background: #fff;
  }

  .admin-ai-form {
    display: grid;
    grid-template-columns: 1fr auto;
    gap: 10px;
    margin-bottom: 10px;
  }

  .admin-ai-panel-actions {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 10px;
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

  @media (max-width: 767px) {
    .admin-ai-widget {
      right: 14px;
      bottom: 14px;
      left: 14px;
    }

    .admin-ai-fab {
      width: 100%;
      justify-content: center;
    }

    .admin-ai-panel {
      width: 100%;
      max-height: 82vh;
    }

    .admin-ai-panel-actions {
      flex-direction: column;
      align-items: stretch;
    }
  }

  .admin-notif-trigger {
    position: relative;
  }

  .admin-notif-dropdown {
    width: min(420px, 92vw);
    max-height: 560px;
    overflow: hidden;
    padding: 0;
    border: 1px solid #e9eef5;
    border-radius: 18px;
    box-shadow: 0 20px 50px rgba(15, 23, 42, 0.12);
  }

  .admin-notif-header {
    display: flex;
    align-items: flex-start;
    justify-content: space-between;
    gap: 12px;
    padding: 14px 16px 12px;
    border-bottom: 1px solid #eef2f6;
    background: #fff;
  }

  .admin-notif-header h6 {
    font-size: 0.95rem;
    font-weight: 700;
    color: #101828;
  }

  .admin-notif-header small {
    display: block;
    margin-top: 2px;
    font-size: 0.75rem;
  }

  .admin-notif-list {
    max-height: 420px;
    overflow-y: auto;
    background: #fff;
  }

  .admin-notif-item {
    display: flex;
    gap: 12px;
    padding: 12px 16px;
    border-bottom: 1px solid #f1f5f9;
    text-decoration: none;
    color: inherit;
    transition: background-color 0.2s ease, transform 0.2s ease;
  }

  .admin-notif-item:hover {
    background: #f8fbff;
    transform: translateY(-1px);
  }

  .admin-notif-item.is-unread {
    background: #f8fbff;
  }

  .admin-notif-item.is-read {
    background: #fff;
  }

  .admin-notif-item:last-child {
    border-bottom: 0;
  }

  .admin-notif-icon {
    width: 34px;
    height: 34px;
    border-radius: 11px;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
    font-size: 0.95rem;
    color: #fff;
  }

  .admin-notif-icon.bg-warning,
  .admin-notif-icon.bg-info {
    color: #111827;
  }

  .admin-notif-content {
    min-width: 0;
    flex: 1;
  }

  .admin-notif-item-head {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 8px;
    margin-bottom: 4px;
  }

  .admin-notif-title {
    font-size: 0.85rem;
    font-weight: 700;
    color: #111827;
    margin: 0;
  }

  .admin-notif-time {
    font-size: 0.72rem;
    color: #6b7280;
    white-space: nowrap;
  }

  .admin-notif-text {
    font-size: 0.76rem;
    line-height: 1.45;
    color: #4b5563;
    margin: 0 0 8px;
  }

  .admin-notif-facts {
    display: grid;
    grid-template-columns: repeat(2, minmax(0, 1fr));
    gap: 6px 10px;
    font-size: 0.72rem;
    color: #667085;
  }

  .admin-notif-facts span {
    min-width: 0;
  }

  .admin-notif-facts strong {
    color: #344054;
    font-weight: 600;
  }

  .admin-notif-status {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    padding: 2px 8px;
    border-radius: 999px;
    font-size: 0.68rem;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.02em;
    background: #fff7d6;
    color: #9a6700;
  }

  .admin-notif-item.is-read .admin-notif-status {
    background: #eaf7ef;
    color: #027a48;
  }

  .admin-notif-empty {
    padding: 20px 16px;
    display: flex;
    flex-direction: column;
    gap: 4px;
    font-size: 0.78rem;
    color: #6b7280;
  }

  .admin-notif-empty strong {
    color: #111827;
    font-size: 0.84rem;
  }

  .admin-notif-footer {
    background: #fff;
  }

  @media (max-width: 576px) {
    .admin-notif-dropdown {
      width: min(96vw, 420px);
    }

    .admin-notif-facts {
      grid-template-columns: 1fr;
    }

    .admin-notif-item {
      padding: 12px 14px;
    }
  }
</style>
