@extends('layouts.app')

@section('header')
  <h2 class="font-semibold text-xl text-gray-800 leading-tight">Order #{{ $order->id }}</h2>
@endsection

@section('content')
<div class="p-6 space-y-4">
  <div>Status: <span class="font-semibold">{{ $order->status }}</span></div>
  <div>Total: <span class="font-semibold">RM {{ number_format($order->total_amount,2) }}</span></div>

  <h3 class="font-semibold mt-4">Items</h3>
  <table class="w-full text-sm">
    <thead><tr><th class="text-left">Title</th><th>Qty</th><th>Unit</th><th>Total</th></tr></thead>
    <tbody>
      @foreach($order->items as $it)
        <tr class="border-b">
          <td>{{ $it->book->title ?? 'Book #'.$it->book_id }}</td>
          <td>{{ $it->quantity }}</td>
          <td>RM {{ number_format($it->unit_price,2) }}</td>
          <td>RM {{ number_format($it->quantity * $it->unit_price,2) }}</td>
        </tr>
      @endforeach
    </tbody>
  </table>

  <h3 class="font-semibold mt-4">Shipment</h3>
  <div>
    Address: {{ $order->shipment->shipping_address ?? '-' }}<br>
    Shipped: {{ optional($order->shipment->shipped_date)->format('Y-m-d H:i') ?? '-' }}<br>
    Delivered: {{ optional($order->shipment->delivery_date)->format('Y-m-d H:i') ?? '-' }}
  </div>

  <h3 class="font-semibold mt-4">Transactions</h3>
  <ul class="list-disc ml-6">
    @foreach($order->transactions as $tx)
      <li>{{ $tx->transaction_date->format('Y-m-d H:i') }} — {{ $tx->transaction_type }} — RM {{ number_format($tx->amount,2) }}</li>
    @endforeach
  </ul>

  {{-- Example admin controls (show only if admin) --}}
  @if(auth()->user()->role === 'admin')
    <div class="mt-4 flex gap-2">
      <form method="POST" action="{{ route('orders.ship',$order) }}">@csrf @method('PATCH')
        <button class="px-3 py-1 bg-slate-700 text-white rounded">Ship</button>
      </form>
      <form method="POST" action="{{ route('orders.arrive',$order) }}">@csrf @method('PATCH')
        <button class="px-3 py-1 bg-slate-700 text-white rounded">Arrived</button>
      </form>
      <form method="POST" action="{{ route('orders.complete',$order) }}">@csrf @method('PATCH')
        <button class="px-3 py-1 bg-emerald-600 text-white rounded">Complete</button>
      </form>
      <form method="POST" action="{{ route('orders.cancel',$order) }}" onsubmit="return confirm('Cancel this order?')">
        @csrf @method('PATCH')
        <button class="px-3 py-1 bg-red-600 text-white rounded">Cancel</button>
      </form>
    </div>
  @endif
</div>
@endsection
