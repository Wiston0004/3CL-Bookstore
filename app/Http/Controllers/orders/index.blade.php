@extends('layouts.app')

@section('header')
  <h2 class="font-semibold text-xl text-gray-800 leading-tight">My Orders</h2>
@endsection

@section('content')
<div class="p-6 space-y-4">
  @foreach($orders as $o)
    <div class="border rounded p-4 flex justify-between items-center">
      <div>
        <div class="font-semibold">Order #{{ $o->id }} — {{ $o->status }}</div>
        <div class="text-sm text-gray-600">{{ $o->order_date->format('Y-m-d H:i') }} • RM {{ number_format($o->total_amount,2) }}</div>
      </div>
      <a href="{{ route('orders.show',$o) }}" class="px-3 py-1 bg-blue-600 text-white rounded">View</a>
    </div>
  @endforeach
  {{ $orders->links() }}
</div>
@endsection
