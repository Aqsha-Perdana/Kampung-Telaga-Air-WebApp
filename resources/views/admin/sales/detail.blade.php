@extends('layout.sidebar')

@section('title', 'Order Detail - ' . $order->id_order)

@section('content')
@include('admin.sales.detail.components.content')
@include('admin.sales.detail.components.styles')
@include('admin.sales.detail.components.scripts')
@endsection
