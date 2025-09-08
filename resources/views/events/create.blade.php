@extends('layouts.app')
@section('title','Create Event • Admin')
@section('content')
<div class="wrap">
  <div class="card">
    <h2 style="margin-top:0">Create Event</h2>
    @if($errors->any())
      <div class="muted">Please fix the errors below.</div>
    @endif
    <form method="POST" action="{{ route('events.store') }}" class="grid grid-2" style="gap:14px">
      @csrf
      <div>
        <label>Title</label>
        <input name="title" class="input" required>
      </div>
      <div>
        <label>Type</label>
        <select name="type" class="input">
          <option>other</option><option>book_fair</option><option>flash_sale</option>
          <option>author_lecture</option><option>webinar</option>
        </select>
      </div>
      <div>
        <label>Delivery Mode</label>
        <select name="delivery_mode" class="input">
          <option>online</option><option>onsite</option><option>hybrid</option>
        </select>
      </div>
      <div>
        <label>Starts At</label>
        <input type="datetime-local" name="starts_at" class="input" required>
      </div>
      <div>
        <label>Ends At</label>
        <input type="datetime-local" name="ends_at" class="input">
      </div>
      <div>
        <label>Visibility</label>
        <select name="visibility" class="input">
          <option>public</option><option>private</option><option>targeted</option>
        </select>
      </div>
      <div>
        <label>Target Role (optional)</label>
        <input name="target_role" class="input" placeholder="staff / customer">
      </div>
      <div style="grid-column:1/-1">
        <label>Description</label>
        <textarea name="description" rows="3" class="input"></textarea>
      </div>
      <div>
        <label>Join URL (online)</label>
        <input name="join_url" class="input">
      </div>
      <div>
        <label>Venue Name (onsite)</label>
        <input name="venue_name" class="input">
      </div>
      <div style="grid-column:1/-1">
        <label>Address (onsite)</label>
        <input name="address" class="input">
      </div>
      <div>
        <label>Points Reward</label>
        <input type="number" min="0" name="points_reward" value="0" class="input">
      </div>

      <div style="grid-column:1/-1">
        <button class="btn primary">Save & Schedule</button>
      </div>
    </form>
  </div>
</div>
@endsection
