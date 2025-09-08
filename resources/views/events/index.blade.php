@extends('layouts.app')
@section('title','Events • Admin')
@section('content')
<div class="wrap">
  @if(session('ok'))<div class="card" style="border-left:4px solid var(--ok)">{{ session('ok') }}</div>@endif
  <div class="row" style="display:flex;justify-content:space-between;align-items:center;margin-bottom:12px">
    <h2 style="margin:0">Events</h2>
    <a class="btn primary" href="{{ route('events.create') }}">➕ Create Event</a>
  </div>

  <div class="card">
    <table style="width:100%">
      <thead><tr>
        <th style="text-align:left">Title</th>
        <th style="text-align:left">Starts</th>
        <th style="text-align:left">Ends</th>
        <th>Status</th>
        <th>Points</th>
        <th>Action</th>
      </tr></thead>
      <tbody>
      @forelse($events as $e)
        <tr>
          <td><a href="{{ route('events.show',$e) }}">{{ $e->title }}</a></td>
          <td>{{ $e->starts_at?->format('Y-m-d H:i') }}</td>
          <td>{{ $e->ends_at?->format('Y-m-d H:i') }}</td>
          <td><span class="pill">{{ $e->status }}</span></td>
          <td>{{ $e->points_reward }}</td>
          <td>
            <form method="POST" action="{{ route('events.cancel',$e) }}" onsubmit="return confirm('Cancel this event?')">
              @csrf
              <input type="hidden" name="reason" value="Admin cancelled">
              <button class="btn danger" {{ in_array($e->status,['completed','cancelled'])?'disabled':'' }}>Cancel</button>
            </form>
          </td>
        </tr>
      @empty
        <tr><td colspan="6" class="muted">No events yet.</td></tr>
      @endforelse
      </tbody>
    </table>
  </div>
  <div class="mt">{{ $events->links() }}</div>
</div>
@endsection
