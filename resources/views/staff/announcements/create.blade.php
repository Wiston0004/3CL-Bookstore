@extends('layouts.app')
@section('title','Create Announcement • Staff')
@section('content')
<div class="wrap">
  <div class="card">
    <h2>Create Announcement</h2>
    <form method="POST" action="{{ route('staff.ann.store') }}">
      @csrf
      <div><label>Title</label><input name="title" class="input" required></div>
      <div><label>Body</label><textarea name="body" class="input" rows="5" required></textarea></div>
      <div><label>Channels (optional)</label><input name="channels[]" class="input" placeholder="e.g. mail,sms"></div>
      <div><button class="btn primary">Save & Publish</button></div>
    </form>
  </div>
</div>
@endsection
