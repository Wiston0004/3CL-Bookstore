@extends('layouts.app')
@section('title',$event->title)
@section('content')
<div class="wrap">
  <div class="card">
    <h2>{{ $event->title }}</h2>
    <p><strong>When:</strong> {{ $event->starts_at->format('Y-m-d H:i') }} 
      @if($event->ends_at) - {{ $event->ends_at->format('Y-m-d H:i') }} @endif</p>
    <p><strong>Mode:</strong> {{ ucfirst($event->delivery_mode) }}</p>
    <p><strong>Description:</strong> {{ $event->description }}</p>
    <p><strong>Points Reward:</strong> {{ $event->points_reward }}</p>

    <form method="POST" action="{{ route('cust.events.register',$event) }}">
      @csrf
      <button class="btn primary">Join Event</button>
    </form>
  </div>
</div>
@endsection
