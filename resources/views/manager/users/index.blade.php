{{-- resources/views/manager/users/index.blade.php --}}
@extends('layouts.app')

@section('header')
  <h2 class="font-semibold text-xl text-gray-800 leading-tight">Manage Users</h2>
@endsection

@section('content')
<div class="py-6">
  <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-4">

    {{-- Flash messages --}}
    @if(session('ok'))
      <div class="bg-green-50 border border-green-200 text-green-800 px-4 py-3 rounded">{{ session('ok') }}</div>
    @endif
    @if(session('err'))
      <div class="bg-red-50 border border-red-200 text-red-800 px-4 py-3 rounded">{{ session('err') }}</div>
    @endif

    {{-- Filters / Search / Sort --}}
    <form method="get" class="bg-white p-4 rounded shadow grid grid-cols-1 md:grid-cols-5 gap-3" autocomplete="off">
      {{-- Search --}}
      <input
        class="border rounded px-3 py-2"
        name="search"
        value="{{ request('search') }}"
        placeholder="Search name or email"
      >

      {{-- Role filter --}}
      <select name="role" class="border rounded px-3 py-2">
        <option value="">All roles</option>
        <option value="manager"  @selected(request('role')==='manager')>Manager</option>
        <option value="staff"    @selected(request('role')==='staff')>Staff</option>
        <option value="customer" @selected(request('role')==='customer')>Customer</option>
      </select>

      {{-- Sort field --}}
      <select name="sort" class="border rounded px-3 py-2">
        @php $sort = request('sort', 'id'); @endphp
        <option value="id"   @selected($sort==='id')>Sort: ID</option>
        <option value="name" @selected($sort==='name')>Name</option>
      </select>

      {{-- Sort direction --}}
      <select name="dir" class="border rounded px-3 py-2">
        @php $dir = request('dir', 'desc'); @endphp
        <option value="asc"  @selected($dir==='asc')>Asc</option>
        <option value="desc" @selected($dir==='desc')>Desc</option>
      </select>

      {{-- Actions --}}
      <div class="flex gap-2">
        <button class="px-4 py-2 bg-indigo-600 text-white rounded">Apply</button>
        @if(request()->query())
          <a href="{{ route('manager.users.index') }}" class="px-4 py-2 border rounded">Clear</a>
        @endif
      </div>
    </form>

    {{-- Results table --}}
    <div class="bg-white rounded shadow overflow-hidden">
      @php
        $q = request()->except(['page']);
        $toggleDir = request('dir','desc')==='desc' ? 'asc' : 'desc';
      @endphp

      @if($users->count() === 0)
        <div class="p-8 text-center text-gray-500">No users found.</div>
      @else
        <div class="overflow-x-auto">
          <table class="min-w-full text-sm">
            <thead class="bg-gray-50">
              <tr>
                <th class="px-4 py-3 text-left">
                  <a href="{{ route('manager.users.index', array_merge($q,['sort'=>'id','dir'=>$toggleDir])) }}" class="hover:underline">
                    ID
                  </a>
                </th>
                <th class="px-4 py-3 text-left">
                  <a href="{{ route('manager.users.index', array_merge($q,['sort'=>'name','dir'=>$toggleDir])) }}" class="hover:underline">
                    Name
                  </a>
                </th>
                <th class="px-4 py-3 text-left">Email</th>
                <th class="px-4 py-3 text-left">Role</th>
                <th class="px-4 py-3 text-left">Joined</th>
              </tr>
            </thead>
            <tbody class="divide-y">
              @foreach($users as $u)
                <tr>
                  <td class="px-4 py-3">{{ $u->id }}</td>
                  <td class="px-4 py-3">{{ $u->name }}</td>
                  <td class="px-4 py-3">{{ $u->email }}</td>
                  <td class="px-4 py-3 capitalize">{{ $u->role }}</td>
                  <td class="px-4 py-3">{{ $u->created_at?->format('Y-m-d H:i') }}</td>
                </tr>
              @endforeach
            </tbody>
          </table>
        </div>

        <div class="p-4">
          {{ $users->onEachSide(1)->links() }}
        </div>
      @endif
    </div>

  </div>
</div>
@endsection
