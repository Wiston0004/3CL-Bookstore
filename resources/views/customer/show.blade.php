@extends('layouts.app')

@section('header')
  <h2 class="font-semibold text-xl text-gray-800 leading-tight">{{ $book->title }}</h2>
@endsection

@section('content')
<div class="py-6">
  <div class="max-w-5xl mx-auto sm:px-6 lg:px-8 space-y-6">

    <div class="bg-white rounded shadow p-6">
      <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        <div>
          @if($book->cover_path)
            <img src="{{ asset('storage/'.$book->cover_path) }}" class="w-full h-auto rounded">
          @endif
        </div>
        <div class="md:col-span-2 space-y-2">
          <div class="text-2xl font-semibold">{{ $book->title }}</div>
          <div class="text-gray-600">By {{ $book->author ?? 'Unknown' }}</div>
          <div class="text-lg">RM {{ number_format($book->price,2) }}</div>
          <div class="text-sm text-gray-600">Stock: {{ $book->stock }}</div>
          <p class="mt-3">{{ $book->description }}</p>
        </div>
      </div>
    </div>

    <div class="bg-white rounded shadow p-6">
      <h3 class="font-semibold mb-3">Reviews</h3>

      @auth
        <form action="{{ route('reviews.store',$book) }}" method="POST" class="mb-4 flex flex-col md:flex-row gap-3">
          @csrf
          <select name="rating" class="border rounded px-3 py-2">
            @for($i=5;$i>=1;$i--) <option value="{{ $i }}">{{ $i }} ★</option> @endfor
          </select>
          <input name="content" class="border rounded px-3 py-2 flex-1" placeholder="Say something...">
          <button class="px-4 py-2 bg-indigo-600 text-white rounded">Post</button>
        </form>
      @endauth

      @forelse($book->reviews as $r)
        <div class="border-t py-3">
          <div class="flex items-center justify-between">
            <div class="font-medium">{{ $r->user?->name ?? 'User' }}</div>
            <div class="text-amber-600">{{ str_repeat('★', $r->rating) }}</div>
          </div>
          <div class="text-sm text-gray-700">{{ $r->content }}</div>
          <div class="text-xs text-gray-500">{{ $r->created_at->diffForHumans() }}</div>
        </div>
      @empty
        <div class="text-gray-500">No reviews yet.</div>
      @endforelse
    </div>

  </div>
</div>
@endsection
