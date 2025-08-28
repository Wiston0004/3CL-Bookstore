@extends('layouts.app')
@section('title','My Profile')
@section('content')
@php
  // $user and $editable are passed from controller
@endphp

<div class="card" style="max-width:720px;margin:0 auto">
  <h3 style="margin-top:0">Edit Profile</h3>

  {{-- Show a quick banner if staff (to explain restrictions) --}}
  @if($user->role === 'staff')
    <p class="muted">Note: Staff cannot edit <b>name</b> or <b>email</b>. Please contact the manager for changes.</p>
  @endif

  @if ($errors->any())
    <div class="muted" style="color:#fca5a5">{{ implode(', ', $errors->all()) }}</div>
  @endif

  <form method="POST" action="{{ route('profile.update') }}" enctype="multipart/form-data">
    @csrf

    <div class="grid grid-2">
      {{-- NAME --}}
      <div>
        <label>Name</label>
        @if(in_array('name',$editable))
          <input class="input" name="name" value="{{ old('name',$user->name) }}" required>
        @else
          <input class="input" value="{{ $user->name }}" disabled>
        @endif
      </div>

      {{-- USERNAME --}}
      <div>
        <label>Username</label>
        @if(in_array('username',$editable))
          <input class="input" name="username" value="{{ old('username',$user->username) }}" required>
        @else
          <input class="input" value="{{ $user->username }}" disabled>
        @endif
      </div>

      {{-- EMAIL --}}
      <div>
        <label>Email</label>
        @if(in_array('email',$editable))
          <input class="input" type="email" name="email" value="{{ old('email',$user->email) }}" required>
        @else
          <input class="input" type="email" value="{{ $user->email }}" disabled>
        @endif
      </div>

      {{-- PHONE --}}
      <div>
        <label>Phone</label>
        @if(in_array('phone',$editable))
          <input class="input" name="phone" value="{{ old('phone',$user->phone) }}">
        @else
          <input class="input" value="{{ $user->phone }}" disabled>
        @endif
      </div>

      {{-- ADDRESS --}}
      <div style="grid-column:1/-1">
        <label>Address</label>
        @if(in_array('address',$editable))
          <input class="input" name="address" value="{{ old('address',$user->address) }}">
        @else
          <input class="input" value="{{ $user->address }}" disabled>
        @endif
      </div>

      {{-- PASSWORD (always allowed for staff & customer) --}}
      <div>
        <label>New Password</label>
        @if(in_array('password',$editable))
          <input class="input" type="password" name="password" autocomplete="new-password">
        @else
          <input class="input" type="password" value="********" disabled>
        @endif
      </div>
      <div>
        <label>Confirm Password</label>
        @if(in_array('password',$editable))
          <input class="input" type="password" name="password_confirmation" autocomplete="new-password">
        @else
          <input class="input" type="password" value="********" disabled>
        @endif
      </div>

      {{-- AVATAR --}}
      <div style="grid-column:1/-1">
        <label>Avatar</label>
        @if(in_array('avatar',$editable))
          <input class="input" type="file" name="avatar" accept="image/*">
        @else
          <input class="input" type="text" value="No changes allowed" disabled>
        @endif
      </div>
    </div>

    @if($user->avatar_path)
      <div class="mt">
        <img src="{{ asset('storage/'.$user->avatar_path) }}" alt="avatar"
             style="height:64px;border-radius:12px;border:1px solid #2a3263">
      </div>
    @endif

    <div class="row mt">
      <button class="btn success" @if($user->role==='manager') disabled @endif>
        Save Changes
      </button>
      <a class="pill right" href="{{ route($user->role.'.dashboard') }}">Back</a>
    </div>
  </form>
</div>
@endsection
