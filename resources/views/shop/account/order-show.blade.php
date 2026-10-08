@extends('layouts.shop')

@section('title', 'Pedido ' . $order->order_number . ' | Mr Bulls')

@section('content')

    <x-shop.order-detail :order="$order" />

@endsection