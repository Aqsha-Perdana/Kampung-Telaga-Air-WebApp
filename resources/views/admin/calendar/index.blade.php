@extends('layout.sidebar')

@section('title', 'Package Sales Calendar')

@section('styles')
@include('admin.calendar.components.styles')
@endsection

@section('content')
@include('admin.calendar.components.content')
@endsection

@section('scripts')
@include('admin.calendar.components.scripts')
@endsection