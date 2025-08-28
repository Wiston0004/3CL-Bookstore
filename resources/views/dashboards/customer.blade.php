@extends('layouts.app')
@section('title','Customer Dashboard')
@section('content')
<div class="card">
  <h3 style="margin-top:0">Welcome, {{ auth()->user()->name }}</h3>
  <p class="muted">Member Points: <b>{{ auth()->user()->points }}</b></p>
</div>
@endsection
