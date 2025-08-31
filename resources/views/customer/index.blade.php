@extends('layouts.app')

@section('title','Browse Books')

@section('content')
  <div class="card mb">
    <form method="get" class="row" autocomplete="off">
      <input class="input" name="search" value="{{ request('search') }}" placeholder="Search title, author, ISBN">
      <button class="btn right">Search</button>
      @if(request('search'))
        <a href="{{ route('customer.index') }}" class="btn">Clear</a>
      @endif
    </form>
  </div>

  @if($books->count())
    <div class="grid grid-3">
      @foreach($books as $book)
        <div class="card" style="display:flex;flex-direction:column;gap:12px">
          <div class="center" style="background:#0f1533;border:1px solid #1c2346;border-radius:12px;padding:12px;height:230px;display:flex;align-items:center;justify-content:center;">
            @if($book->cover_image_url)
              <img src="{{ $book->cover_image_url }}" alt="Cover" style="max-height:100%;max-width:100%;object-fit:contain;">
            @else
              <span class="muted">No Cover</span>
            @endif
          </div>

          <div>
            <div class="row">
              <h3 style="margin:0">{{ $book->title }}</h3>
              <span class="pill right" style="background:{{ $book->stock>0 ? '#0f2a1a' : '#2a1212' }};border-color:{{ $book->stock>0 ? '#184a31' : '#4a1c1c' }}">
                {{ $book->stock>0 ? 'In stock ('.$book->stock.')' : 'Out of stock' }}
              </span>
            </div>
            <div class="muted">by {{ $book->author }}</div>
            <div class="muted">Category: {{ $book->categories->first()->name ?? '-' }}</div>

            @if(!empty($book->description))
              <p class="muted" style="margin-top:8px;display:-webkit-box;-webkit-line-clamp:3;-webkit-box-orient:vertical;overflow:hidden;">
                {{ $book->description }}
              </p>
            @endif
          </div>

          <div class="row">
            <div class="pill">RM {{ number_format($book->price,2) }}</div>
            <div class="right row" style="gap:8px">
              <a href="{{ route('customer.show',$book) }}" class="btn">View Details</a>
              <button type="button" class="btn primary"
                      @if($book->stock<=0) disabled @endif
                      onclick="this.innerText='Added!'; this.classList.remove('primary'); this.classList.add('success'); alert('“{{ addslashes($book->title) }}” added to cart (demo only).');">
                Add to Cart
              </button>
            </div>
          </div>
        </div>
      @endforeach
    </div>

    <div class="mt">
      {{ $books->withQueryString()->links() }}
    </div>
  @else
    <div class="card center muted">No books available right now.</div>
  @endif
@endsection
