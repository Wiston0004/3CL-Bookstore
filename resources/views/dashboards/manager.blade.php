@extends('layouts.app')
@section('title','Manager Dashboard')
@section('content')
<div class="grid grid-3">
  <div class="card center">
    <h3 style="margin-top:0">Manage Users</h3>
    <p class="muted">Create and update Staff & Customers</p>
    <a class="btn primary" href="{{ route('manager.users.index') }}">Open User Manager</a>
  </div>

  <div class="card center">
    <h3 style="margin-top:0">Reports</h3>
    <p class="muted">See monthly customer registrations</p>
    <a class="btn" href="{{ route('manager.reports.customers') }}">View Reports</a>
  </div>

  <div class="card center">
    <h3 style="margin-top:0">Transactions</h3>
    <p class="muted">All payments & refunds</p>
    <a class="btn" href="{{ route('manager.transactions.index') }}">View Transactions</a>
  </div>
</div>

  
@endsection
