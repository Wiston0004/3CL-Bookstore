@extends('layouts.app')

@section('title', $book->title.' — Details')

@section('content')
  <div class="card">
    <div class="row">
      <h2 style="margin:0">📖 {{ $book->title }}</h2>
      <a href="{{ route('customer.index') }}" class="btn right">Back to List</a>
    </div>

    <div class="grid grid-2 mt">
      {{-- Left: Cover --}}
      <div class="center" style="background:#0f1533;border:1px solid #1c2346;border-radius:12px;padding:16px;min-height:360px;display:flex;align-items:center;justify-content:center;">
        @if($book->cover_image_url)
          <img src="{{ $book->cover_image_url }}" alt="Cover" style="max-height:340px;max-width:100%;object-fit:contain;">
        @else
          <span class="muted">No Cover</span>
        @endif
      </div>

      {{-- Right: Info --}}
      <div>
        <div class="row">
          <span class="pill" style="background:{{ $book->stock>0 ? '#0f2a1a' : '#2a1212' }};border-color:{{ $book->stock>0 ? '#184a31' : '#4a1c1c' }}">
            {{ $book->stock>0 ? 'In stock ('.$book->stock.')' : 'Out of stock' }}
          </span>
          <span class="pill">ISBN: {{ $book->isbn }}</span>
          <span class="pill">RM {{ number_format($book->price,2) }}</span>
        </div>

        <div class="grid grid-2 mt">
          <div>
            <label>Author</label>
            <div>{{ $book->author }}</div>
          </div>
          <div>
            <label>Category</label>
            <div>{{ $book->categories->first()->name ?? '-' }}</div>
          </div>
          <div>
            <label>Genre</label>
            <div>{{ $book->genre ?? '-' }}</div>
          </div>
        </div>

        @if(!empty($book->description))
          <div class="mt">
            <label>Description</label>
            <p style="margin:0;white-space:pre-line">{{ $book->description }}</p>
          </div>
        @endif

        <div class="row mt">
          <button type="button" class="btn primary"
                  @if($book->stock<=0) disabled @endif
                  onclick="this.innerText='Added!'; this.classList.remove('primary'); this.classList.add('success'); alert('“{{ addslashes($book->title) }}” added to cart (demo only).');">
            Add to Cart
          </button>
          <a href="{{ route('customer.index') }}" class="btn">Back</a>
        </div>
      </div>
    </div>
  </div>

  {{-- Optional read-only reviews --}}
  @if($book->relationLoaded('reviews') && $book->reviews->count())
    <div class="card mt">
      <h3 style="margin-top:0">Reviews</h3>
      <div class="grid">
        @foreach($book->reviews as $r)
          <div class="card" style="padding:14px">
            <div class="row">
              <strong>{{ $r->rating }}/5</strong>
              <span class="muted">{{ $r->created_at->format('d M Y') }}</span>
            </div>
            <div class="mt">{{ $r->content }}</div>
          </div>
        @endforeach
      </div>
    </div>
  @endif
@endsection
