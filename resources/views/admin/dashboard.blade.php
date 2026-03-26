@extends('layout.sidebar')

@section('content')
@include('admin.dashboard.components.hero-recent-row')
@include('admin.dashboard.components.kpi-cards-row')
@include('admin.dashboard.components.enhanced-features-row')
@include('admin.dashboard.components.detailed-resource-performance-row')
@include('admin.dashboard.components.charts-status-row')
@include('admin.dashboard.components.unsold-resources-row')
@endsection

@section('scripts')
@include('admin.dashboard.components.scripts')
@endsection

@section('styles')
@include('admin.dashboard.components.styles')
@endsection
