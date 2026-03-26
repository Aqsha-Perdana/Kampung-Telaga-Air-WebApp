@php
  $adminUser = Auth::guard('admin')->user();
@endphp
<!doctype html>
<html lang="en">

@include('layout.partials.admin.head')

<body>
  <div class="page-wrapper" id="main-wrapper" data-layout="vertical" data-navbarbg="skin6" data-sidebartype="full"
    data-sidebar-position="fixed" data-header-position="fixed">

    @include('layout.partials.admin.sidebar-menu')

    <div class="body-wrapper" style="height: 100vh; overflow-y: auto;">
      @include('layout.partials.admin.header')

      <div class="container-fluid" style="padding-bottom: 2rem;">
        @yield('content')
      </div>
    </div>
  </div>

  @include('layout.partials.admin.scripts')
</body>

</html>
