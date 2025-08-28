@extends('layouts.app')
@section('title','Staff Dashboard')
@section('content')
<div class="card">
  <h3 style="margin-top:0">Welcome, {{ auth()->user()->name }}</h3>
  <p class="muted">You’re signed in as <b>staff</b>.</p>
</div>
@endsection
