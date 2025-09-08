@extends('layouts.app')
@section('title','Events')
@section('content')
<div class="wrap">
  <h2 style="margin:0 0 12px">Upcoming & Live Events</h2>
  <div class="grid grid-2">
    @forelse($events as $e)
      <div class="card">
        <h3 style="margin:0 0 6px"><a href="{{ route('events.show',$e) }}">{{ $e->title }}</a></h3>
        <div class="muted">{{ $e->starts_at?->format('Y-m-d H:i') }} • {{ $e->status }}</div>
         <p style="margin-top:8px">{{ \Illuminate\Support\Str::limit($e->description, 140) }}</p>
        <a class="btn" href="{{ route('events.show',$e) }}">View</a>
      </div>
    @empty
      <div class="muted">No events available.</div>
    @endforelse
  </div>
  <div class="mt">{{ $events->links() }}</div>
</div>
@endsection
