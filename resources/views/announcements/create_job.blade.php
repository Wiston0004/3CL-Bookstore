@extends('layouts.app')
@section('title','New Announcement')
@section('content')
<div class="wrap">
  @if(session('ok'))<div class="card" style="border-left:4px solid var(--ok)">{{ session('ok') }}</div>@endif
  <div class="card">
    <h2 style="margin-top:0">Queue Announcement</h2>
    <form method="POST" action="{{ route('manager.ann.queue') }}" class="grid grid-2" style="gap:14px">
      @csrf
      <div style="grid-column:1/-1">
        <label>Title</label>
        <input name="title" class="input" required>
      </div>
      <div style="grid-column:1/-1">
        <label>Body</label>
        <textarea name="body" rows="4" class="input" required></textarea>
      </div>
      <div>
        <label>Channels</label>
        <div class="row" style="display:flex;gap:10px">
          <label><input type="checkbox" name="channels[]" value="mail" checked> Email</label>
          <label><input type="checkbox" name="channels[]" value="sms"> SMS</label>
          <label><input type="checkbox" name="channels[]" value="push"> Push</label>
        </div>
      </div>
      <div>
        <label>Target Role (optional)</label>
        <input name="role" class="input" placeholder="staff / customer">
      </div>
      <div style="grid-column:1/-1">
        <button class="btn primary">Queue Announcement</button>
      </div>
    </form>
  </div>
</div>
@endsection
