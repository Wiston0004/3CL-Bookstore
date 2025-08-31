{{-- resources/views/books/show.blade.php --}}
@extends('layouts.app')

@section('title', $book->title)

@section('content')
@php
  $stock = (int) ($book->stock ?? 0);
  $lowThreshold = (int) request('low', 5);
  $isLow  = $stock > 0 && $stock < $lowThreshold;
  $isOut  = $stock <= 0;
  $avg    = (float) ($book->avg_rating ?? 0);
@endphp

<div class="grid" style="gap:16px">
  {{-- Book header card --}}
  <div class="card">
    <div class="row" style="justify-content:space-between;align-items:center;margin-bottom:12px">
      <div class="row" style="gap:10px;align-items:center">
        <h2 style="margin:0">📖 {{ $book->title }}</h2>
        @if($book->categories && $book->categories->count())
          <div class="row" style="gap:6px;flex-wrap:wrap">
            @foreach($book->categories->take(3) as $cat)
              <span class="pill">{{ $cat->name }}</span>
            @endforeach
            @if($book->categories->count() > 3)
              <span class="pill muted">+{{ $book->categories->count()-3 }}</span>
            @endif
          </div>
        @endif
      </div>

      <div class="row" style="gap:8px">
        <a href="{{ route('books.index') }}" class="pill">← Back</a>
        <a href="{{ route('books.edit',$book) }}" class="btn">Edit</a>
        <form action="{{ route('books.destroy',$book) }}" method="POST">
          @csrf @method('DELETE')
          <button class="btn danger" data-confirm="Delete this book?">Delete</button>
        </form>
      </div>
    </div>

    <div class="grid grid-2">
      {{-- Cover --}}
      <div class="card" style="padding:16px; display:flex; align-items:center; justify-content:center; min-height:240px">
        @if($book->cover_image_url)
          <img src="{{ $book->cover_image_url }}" alt="Cover"
               style="max-height:300px; max-width:100%; border-radius:12px; border:1px solid #1c2346">
        @else
          <div class="muted">No cover image</div>
        @endif
      </div>

      {{-- Facts --}}
      <div class="grid" style="gap:10px">
        <div class="row" style="gap:8px;align-items:center">
          <div class="pill muted">Author</div>
          <div>{{ $book->author }}</div>
        </div>
        <div class="row" style="gap:8px;align-items:center">
          <div class="pill muted">ISBN</div>
          <div>{{ $book->isbn }}</div>
        </div>
        <div class="row" style="gap:8px;align-items:center">
          <div class="pill muted">Genre</div>
          <div>{{ $book->genre ?? '-' }}</div>
        </div>
        <div class="row" style="gap:8px;align-items:center">
          <div class="pill muted">Price</div>
          <div><strong>RM {{ number_format($book->price,2) }}</strong></div>
        </div>

        {{-- Stock status --}}
        <div class="row" style="gap:8px;align-items:center">
          <div class="pill muted">Stock</div>
          <div>{{ $stock }}</div>
          @if($isOut)
            <span class="pill" style="border-color:#3e1d1d;background:linear-gradient(180deg,#1a0e0e,#241012);color:#fca5a5">Out of stock</span>
          @elseif($isLow)
            <span class="pill" style="border-color:#3e2c1d;background:linear-gradient(180deg,#2a1a0e,#2f1e12);color:#fcd39b">Low</span>
          @else
            <span class="pill" style="border-color:#1d3f2a;background:linear-gradient(180deg,#0d1d13,#11271a);color:#bdf7c4">In stock</span>
          @endif
        </div>

        {{-- Rating --}}
        <div class="row" style="gap:8px;align-items:center">
          <div class="pill muted">Rating</div>
          <div class="row" style="gap:6px;align-items:center">
            <div aria-label="Average rating" title="{{ number_format($avg,1) }}/5" style="letter-spacing:2px">
              @for($i=1;$i<=5;$i++)
                @php $filled = $avg >= $i - 0.5; @endphp
                <span style="color:#f59e0b">{{ $filled ? '★' : '☆' }}</span>
              @endfor
            </div>
            <span class="muted">{{ number_format($avg,1) }}/5</span>
          </div>
        </div>
      </div>
    </div>

    {{-- Description --}}
    <div class="mt" style="margin-top:18px">
      <h3 style="margin:0 0 6px">Description</h3>
      <p class="muted" style="white-space:pre-line;margin:0">
        {{ $book->description ?: 'No description provided.' }}
      </p>
    </div>
  </div>

  {{-- Reviews --}}
  <div class="card">
    <div class="row" style="justify-content:space-between;align-items:center;margin-bottom:10px">
      <h3 style="margin:0">Reviews</h3>
      <span class="pill muted">Total: {{ $book->reviews->count() }}</span>
    </div>

    @forelse($book->reviews as $r)
      <div style="padding:12px 0;border-bottom:1px solid #202750">
        <div class="row" style="gap:8px;align-items:center;margin-bottom:6px">
          <strong>{{ $r->rating }}/5</strong>
          <div style="letter-spacing:2px;color:#f59e0b">
            @for($i=1;$i<=5;$i++)
              <span>{{ $r->rating >= $i ? '★' : '☆' }}</span>
            @endfor
          </div>
          @if(property_exists($r,'created_at') && $r->created_at)
            <span class="muted">• {{ $r->created_at->format('d M Y') }}</span>
          @endif
        </div>
        <div class="muted">{{ $r->content }}</div>
      </div>
    @empty
      <div class="muted">No reviews yet.</div>
    @endforelse
  </div>
</div>
@endsection
