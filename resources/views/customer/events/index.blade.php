@extends('layouts.app')
@section('title','Events')
@section('content')
<div class="wrap">
  <a href="{{ route('customer.dashboard') }}" class="pill">Back</a>
  <h2>Upcoming Events</h2>
  <div class="grid grid-3">
    @forelse($events as $e)
      <div class="card">
        <div>
          <label>Event Image</label>
          <input type="file" name="image" class="input">
        </div>

        <h3>{{ $e->title }}</h3>
        <p class="muted">{{ $e->starts_at?->format('M d, Y H:i') }}</p>
        <p>{{ Str::limit($e->description,100) }}</p>
        <a class="btn" href="{{ route('cust.events.show',$e) }}">View Details</a>
      </div>
    @empty
      <p class="muted">No events available.</p>
    @endforelse
  </div>
  <div class="mt">{{ $events->links() }}</div>
</div>
@endsection
