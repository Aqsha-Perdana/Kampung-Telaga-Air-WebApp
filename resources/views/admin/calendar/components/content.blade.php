<div class="container-fluid">
    @include('admin.calendar.components.content.header')

    <div class="row g-3">
        <div class="col-lg-9">
            @include('admin.calendar.components.content.calendar-column')
        </div>
        <div class="col-lg-3">
            @include('admin.calendar.components.content.resources-panel')
            @include('admin.calendar.components.content.alerts-panel')
        </div>
    </div>
</div>

@include('admin.calendar.components.content.detail-modal')