@extends('layouts.app')
@section('title','Manager Dashboard')
@section('content')
<div class="card center" style="max-width:520px;margin:0 auto">
  <h3 style="margin-top:0">Manage Users</h3>
  <p class="muted">Create, update, and delete Staff & Customers</p>
  <a class="btn primary" href="{{ route('manager.users.index') }}">Open User Manager</a>
</div>
@endsection
