@extends('layouts.app')
@section('title','Manager Dashboard')
@section('content')
<div class="grid grid-3">
  <div class="card center">
    <h3 style="margin-top:0">Manage Users</h3>
    <p class="muted">Create, update, and delete Staff & Customers</p>
    <a class="btn primary" href="{{ route('manager.users.index') }}">Open User Manager</a>
  </div>
  <div class="card center">
    <h3 style="margin-top:0">Profile</h3>
    <p class="muted">View your info (locked)</p>
    <a class="btn" href="{{ route('profile.edit') }}">View Profile</a>
  </div>
</div>
@endsection
