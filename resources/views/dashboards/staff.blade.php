@extends('layouts.app')

@section('header')
  <h2 class="font-semibold text-xl text-gray-800 leading-tight">Staff Dashboard</h2>
@endsection

@section('content')
<div class="p-6 max-w-7xl mx-auto">
  <div class="grid sm:grid-cols-2 lg:grid-cols-3 gap-6">

    {{-- Inventory / Books --}}
    <a href="{{ route('books.index') }}"
       class="block rounded-xl border border-gray-200 bg-white p-6 shadow hover:shadow-md transition">
      <div class="text-2xl mb-2">📚</div>
      <div class="font-semibold text-lg">Manage Inventory</div>
      <p class="text-sm text-gray-600 mt-1">
        View, add, edit books and adjust stock.
      </p>
    </a>

  </div>
</div>
@endsection
