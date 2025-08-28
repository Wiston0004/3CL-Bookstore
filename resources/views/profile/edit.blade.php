@extends('layouts.app')
@section('title','My Profile')
@section('content')
<div class="card" style="max-width:720px;margin:0 auto">
  <h3 style="margin-top:0">Edit Profile</h3>
  @if (session('status')) <div class="muted">{{ session('status') }}</div> @endif
  @if ($errors->any()) <div class="muted" style="color:#fca5a5">{{ implode(', ', $errors->all()) }}</div> @endif

  <form method="POST" action="{{ route('profile.update') }}" enctype="multipart/form-data">
    @csrf
    <div class="grid grid-2">
      <div>
        <label>Name</label>
        <input class="input" name="name" value="{{ old('name',auth()->user()->name) }}" required>
      </div>
      <div>
        <label>Username</label>
        <input class="input" name="username" value="{{ old('username',auth()->user()->username) }}" required>
      </div>
      <div>
        <label>Email</label>
        <input class="input" type="email" name="email" value="{{ old('email',auth()->user()->email) }}" required>
      </div>
      <div>
        <label>Phone</label>
        <input class="input" name="phone" value="{{ old('phone',auth()->user()->phone) }}">
      </div>
      <div style="grid-column:1/-1">
        <label>Address</label>
        <input class="input" name="address" value="{{ old('address',auth()->user()->address) }}">
      </div>
      <div>
        <label>New Password</label>
        <input class="input" type="password" name="password" autocomplete="new-password">
      </div>
      <div>
        <label>Confirm Password</label>
        <input class="input" type="password" name="password_confirmation" autocomplete="new-password">
      </div>
      <div style="grid-column:1/-1">
        <label>Avatar</label>
        <input class="input" type="file" name="avatar" accept="image/*">
      </div>
    </div>

    @if(auth()->user()->avatar_path)
      <div class="mt">
        <img src="{{ asset('storage/'.auth()->user()->avatar_path) }}" alt="avatar"
             style="height:64px;border-radius:12px;border:1px solid #2a3263">
      </div>
    @endif

    <div class="row mt">
      <button class="btn success">Save Changes</button>
      <a class="pill right" href="{{ route(auth()->user()->role.'.dashboard') }}">Back</a>
    </div>
  </form>
</div>
@endsection
