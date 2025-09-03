@extends('layouts.app')
@section('title', $event->title.' • Event')
@section('content')
<div class="wrap">
  @if(session('ok'))<div class="card" style="border-left:4px solid var(--ok)">{{ session('ok') }}</div>@endif
  <div class="card">
    <h2 style="margin-top:0">{{ $event->title }}</h2>
    <div class="muted">{{ $event->starts_at?->format('Y-m-d H:i') }} → {{ $event->ends_at?->format('Y-m-d H:i') }} • {{ $event->status }}</div>
    <p style="margin-top:10px">{{ $event->description }}</p>
    @if($event->points_reward>0)
      <div class="pill" style="background:#122b24;border:1px solid #1d4ed8;margin:8px 0">Earn {{ $event->points_reward }} pts when you join</div>
    @endif

    <form method="POST" action="{{ route('events.register.store',$event) }}" class="mt">
      @csrf
      <button class="btn success">Join Event</button>
    </form>
  </div>
</div>
@endsection
