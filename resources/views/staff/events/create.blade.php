@extends('layouts.app')
@section('title','Create Event • Staff')
@section('content')
<div class="wrap">
  <div class="card">
    <a href="{{ route('dashboard.staff') }}" class="pill">← Back to Staff Home</a>
    <h2>Create Event</h2>
    <form method="POST" action="{{ route('staff.events.store') }}" class="grid grid-2" style="gap:14px">
      @csrf
      <div><label>Title</label><input name="title" class="input" required></div>
      <div><label>Type</label><input name="type" class="input" required></div>
      <div><label>Delivery Mode</label><input name="delivery_mode" class="input" required></div>
      <div><label>Starts At</label><input type="datetime-local" name="starts_at" class="input" required></div>
      <div><label>Ends At</label><input type="datetime-local" name="ends_at" class="input"></div>
      <div><label>Visibility</label><input name="visibility" class="input" required></div>
      <div>
        <label>Status</label>
        <select name="status" class="input" required>
          <option value="draft"     {{ old('status', $announcement->status ?? '') == 'draft' ? 'selected' : '' }}>Draft</option>
          <option value="scheduled" {{ old('status', $announcement->status ?? '') == 'scheduled' ? 'selected' : '' }}>Scheduled</option>
          <option value="sent"      {{ old('status', $announcement->status ?? '') == 'sent' ? 'selected' : '' }}>Sent</option>
          <option value="failed"    {{ old('status', $announcement->status ?? '') == 'failed' ? 'selected' : '' }}>Failed</option>
        </select>
      </div>
      <div><label>Points Reward</label><input type="number" name="points_reward" class="input" value="0"></div>
      <div style="grid-column:1/-1"><label>Description</label><textarea name="description" class="input"></textarea></div>
      <div>
        <label>Event Image</label>
        <input type="file" name="image" class="input">
      </div>
      <div style="grid-column:1/-1"><button class="btn primary">Save & Schedule</button></div>
    </form>
  </div>
</div>
@endsection

<!-- raymondleong1226@gmail.com -->
