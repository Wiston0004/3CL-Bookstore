@extends('layouts.app')
@section('title','Create Event • Staff')
@section('content')
<div class="wrap">
  <div class="card">
    <h2>Create Event</h2>
    <form method="POST" action="{{ route('staff.events.store') }}" class="grid grid-2" style="gap:14px">
      @csrf
      <div><label>Title</label><input name="title" class="input" required></div>
      <div><label>Type</label><input name="type" class="input" required></div>
      <div><label>Delivery Mode</label><input name="delivery_mode" class="input" required></div>
      <div><label>Starts At</label><input type="datetime-local" name="starts_at" class="input" required></div>
      <div><label>Ends At</label><input type="datetime-local" name="ends_at" class="input"></div>
      <div><label>Visibility</label><input name="visibility" class="input" required></div>
      <div><label>Points Reward</label><input type="number" name="points_reward" class="input" value="0"></div>
      <div style="grid-column:1/-1"><label>Description</label><textarea name="description" class="input"></textarea></div>
      <div style="grid-column:1/-1"><button class="btn primary">Save & Schedule</button></div>
    </form>
  </div>
</div>
@endsection
