@extends('layouts.app')
@section('title','User Manager')
@section('content')
<div class="card">
  <div class="row">
    <h3 style="margin:0">Users</h3>
    <a class="btn primary right" href="{{ route('manager.users.create') }}">+ New</a>
  </div>
  <form method="GET" class="row mt">
    <input class="input" name="search" value="{{ request('search') }}" placeholder="Search username, email, or name">
    <button class="btn">Search</button>
  </form>

  <div class="mt">
    <table class="table">
      <thead><tr><th>ID</th><th>Role</th><th>Username</th><th>Email</th><th>Points</th><th></th></tr></thead>
      <tbody>
      @foreach($users as $u)
        <tr>
          <td>{{ $u->id }}</td>
          <td><span class="pill">{{ $u->role }}</span></td>
          <td>{{ $u->username }}</td>
          <td>{{ $u->email }}</td>
          <td>{{ $u->points }}</td>
          <td class="right">
            <a class="pill" href="{{ route('manager.users.edit',$u) }}">Edit</a>
            <form method="POST" action="{{ route('manager.users.destroy',$u) }}" style="display:inline">
              @csrf @method('DELETE')
              <button class="pill" data-confirm="Delete this user?">Delete</button>
            </form>
          </td>
        </tr>
      @endforeach
      </tbody>
    </table>
    <div class="mt">{{ $users->links() }}</div>
  </div>
</div>
@endsection
