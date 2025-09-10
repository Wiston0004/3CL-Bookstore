@extends('layouts.app')
@section('title','Edit Announcement • Staff')
@section('content')
<div class="wrap">
  <div class="card">
    <h2>Edit Announcement</h2>
    <form method="POST" action="{{ route('staff.ann.update',$announcement) }}">
      @csrf @method('PUT')
      <div><label>Title</label><input name="title" class="input" value="{{ $announcement->title }}" required></div>
      <div><label>Body</label><textarea name="body" class="input" rows="5">{{ $announcement->body }}</textarea></div>
      <div><button class="btn primary">Update</button></div>
    </form>
  </div>
</div>
@endsection
