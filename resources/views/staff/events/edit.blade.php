@extends('layouts.app')
@section('title','Edit Event • Staff')
@section('content')
<div class="wrap">
  <div class="card">
    <h2>Edit Event</h2>
    <form method="POST" action="{{ route('staff.events.update',$event) }}" class="grid grid-2" style="gap:14px">
      @csrf @method('PUT')
      <div><label>Title</label><input name="title" class="input" value="{{ $event->title }}" required></div>
      <div><label>Type</label><input name="type" class="input" value="{{ $event->type }}"></div>
      <div><label>Delivery Mode</label><input name="delivery_mode" class="input" value="{{ $event->delivery_mode }}"></div>
      <div><label>Starts At</label><input type="datetime-local" name="starts_at" class="input" value="{{ $event->starts_at->format('Y-m-d\TH:i') }}"></div>
      <div><label>Ends At</label><input type="datetime-local" name="ends_at" class="input" value="{{ $event->ends_at?->format('Y-m-d\TH:i') }}"></div>
      <div><label>Visibility</label><input name="visibility" class="input" value="{{ $event->visibility }}"></div>
      <div><label>Points Reward</label><input type="number" name="points_reward" class="input" value="{{ $event->points_reward }}"></div>
      <div style="grid-column:1/-1"><label>Description</label><textarea name="description" class="input">{{ $event->description }}</textarea></div>
      <div style="grid-column:1/-1"><button class="btn primary">Update</button></div>
    </form>
  </div>
</div>
@endsection
