@extends('layouts.app')
@section('title','User Manager')
@section('content')
<div class="card">
  <div class="row">
    <h3 style="margin:0">Users</h3>
    <a class="btn primary right" href="{{ route('manager.users.create') }}">+ New</a>
  </div>

  {{-- Filter + Sort --}}
  <form method="GET" class="row mt" style="gap:10px">
    <input class="input" name="search" value="{{ $search ?? '' }}" placeholder="Search username, email, or name" style="min-width:260px">

    <select class="input" name="role" style="min-width:160px">
      <option value="" {{ ($role ?? '')==='' ? 'selected':'' }}>All roles</option>
      <option value="staff" {{ ($role ?? '')==='staff' ? 'selected':'' }}>Staff</option>
      <option value="customer" {{ ($role ?? '')==='customer' ? 'selected':'' }}>Customer</option>
    </select>

    <select class="input" name="sort" style="min-width:160px">
      <option value="id"   {{ ($sort ?? 'id')==='id' ? 'selected':'' }}>Sort by ID</option>
      <option value="name" {{ ($sort ?? 'id')==='name' ? 'selected':'' }}>Sort by Name</option>
    </select>

    <select class="input" name="dir" style="min-width:140px">
      <option value="asc"  {{ ($dir ?? 'desc')==='asc'  ? 'selected':'' }}>Ascending</option>
      <option value="desc" {{ ($dir ?? 'desc')==='desc' ? 'selected':'' }}>Descending</option>
    </select>

    <button class="btn" type="submit">Apply</button>
    @if(($search ?? '')!=='' || ($role ?? '')!=='' || ($sort ?? 'id')!=='id' || ($dir ?? 'desc')!=='desc')
      <a class="pill" href="{{ route('manager.users.index') }}">Reset</a>
    @endif
  </form>

  <div class="mt">
    <table class="table">
      <thead>
        <tr>
          {{-- Clickable headers to toggle sort --}}
          @php
            $toggleDir = ($dir ?? 'desc') === 'asc' ? 'desc' : 'asc';
          @endphp
          <th>
            <a href="{{ route('manager.users.index', array_merge(request()->query(), ['sort'=>'id','dir'=> ($sort==='id' ? $toggleDir : 'asc')])) }}">
              ID {!! $sort==='id' ? ($dir==='asc'?'↑':'↓') : '' !!}
            </a>
          </th>
          <th>Role</th>
          <th>
            <a href="{{ route('manager.users.index', array_merge(request()->query(), ['sort'=>'name','dir'=> ($sort==='name' ? $toggleDir : 'asc')])) }}">
              Name {!! $sort==='name' ? ($dir==='asc'?'↑':'↓') : '' !!}
            </a>
          </th>
          <th>Username</th>
          <th>Email</th>
          <th>Points</th>
          <th></th>
        </tr>
      </thead>
      <tbody>
      @forelse($users as $u)
        <tr>
          <td>{{ $u->id }}</td>
          <td><span class="pill">{{ $u->role }}</span></td>
          <td>{{ $u->name }}</td>
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
      @empty
        <tr><td colspan="7" class="muted">No users found…</td></tr>
      @endforelse
      </tbody>
    </table>

    <div class="mt">{{ $users->links() }}</div>
  </div>
</div>
@endsection
