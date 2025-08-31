{{-- resources/views/profile/edit.blade.php --}}
@extends('layouts.app')

@section('header')
  <h2 class="font-semibold text-xl text-gray-800 leading-tight">My Profile</h2>
@endsection

@section('content')
<div class="py-6">
  <div class="max-w-3xl mx-auto sm:px-6 lg:px-8 space-y-4">

    {{-- Flash messages --}}
    @if(session('ok'))
      <div class="bg-green-50 border border-green-200 text-green-800 px-4 py-3 rounded">{{ session('ok') }}</div>
    @endif
    @if(session('err'))
      <div class="bg-red-50 border border-red-200 text-red-800 px-4 py-3 rounded">{{ session('err') }}</div>
    @endif

    @php $u = $user ?? auth()->user(); @endphp

    {{-- Staff notice --}}
    @if($u->role === 'staff')
      <div class="bg-amber-50 border border-amber-200 text-amber-800 px-4 py-3 rounded">
        Staff can update profile details except <strong>name</strong> and <strong>email</strong> (set by Manager).
      </div>
    @endif

    {{-- Customer notice --}}
    @if($u->role === 'customer')
      <div class="bg-blue-50 border border-blue-200 text-blue-800 px-4 py-3 rounded">
        Customers can update all profile fields below.
      </div>
    @endif

    <div class="bg-white shadow rounded p-6">
      <form action="{{ route('profile.update') }}" method="POST" enctype="multipart/form-data" class="space-y-5">
        @csrf

        {{-- Avatar --}}
        <div class="flex items-center gap-4">
          <div class="w-16 h-16 rounded-full overflow-hidden bg-gray-100">
            @if($u->avatar_path)
              <img src="{{ asset('storage/'.$u->avatar_path) }}" alt="avatar" class="w-16 h-16 object-cover">
            @else
              <div class="w-full h-full flex items-center justify-center text-gray-400">No Avatar</div>
            @endif
          </div>
          <div>
            <label class="block text-sm font-medium mb-1">Avatar</label>
            <input type="file" name="avatar" accept="image/*" class="block w-full text-sm">
            @error('avatar') <div class="text-sm text-red-600 mt-1">{{ $message }}</div> @enderror
          </div>
        </div>

        {{-- Name --}}
        <div>
          <label class="block text-sm font-medium mb-1">Name</label>
          <input
            type="text"
            name="name"
            value="{{ old('name', $u->name) }}"
            @if($u->role !== 'customer') readonly @endif
            class="w-full border rounded px-3 py-2 @if($u->role !== 'customer') bg-gray-100 cursor-not-allowed @endif"
          >
          @error('name') <div class="text-sm text-red-600 mt-1">{{ $message }}</div> @enderror
          @if($u->role === 'staff')
            <div class="text-xs text-gray-500 mt-1">Staff name is maintained by Manager.</div>
          @endif
        </div>

        {{-- Email --}}
        <div>
          <label class="block text-sm font-medium mb-1">Email</label>
          <input
            type="email"
            name="email"
            value="{{ old('email', $u->email) }}"
            @if($u->role !== 'customer') readonly @endif
            class="w-full border rounded px-3 py-2 @if($u->role !== 'customer') bg-gray-100 cursor-not-allowed @endif"
          >
          @error('email') <div class="text-sm text-red-600 mt-1">{{ $message }}</div> @enderror
          @if($u->role === 'staff')
            <div class="text-xs text-gray-500 mt-1">Staff email is maintained by Manager.</div>
          @endif
        </div>

        {{-- Phone --}}
        <div>
          <label class="block text-sm font-medium mb-1">Phone</label>
          <input
            type="text"
            name="phone"
            value="{{ old('phone', $u->phone) }}"
            class="w-full border rounded px-3 py-2"
            placeholder="+60-xxx-xxx"
          >
          @error('phone') <div class="text-sm text-red-600 mt-1">{{ $message }}</div> @enderror
        </div>

        {{-- Address --}}
        <div>
          <label class="block text-sm font-medium mb-1">Address</label>
          <input
            type="text"
            name="address"
            value="{{ old('address', $u->address) }}"
            class="w-full border rounded px-3 py-2"
            placeholder="Street, City, State"
          >
          @error('address') <div class="text-sm text-red-600 mt-1">{{ $message }}</div> @enderror
        </div>

        {{-- Password --}}
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
          <div>
            <label class="block text-sm font-medium mb-1">New Password</label>
            <input type="password" name="password" class="w-full border rounded px-3 py-2" placeholder="Leave blank to keep current">
            @error('password') <div class="text-sm text-red-600 mt-1">{{ $message }}</div> @enderror
          </div>
          <div>
            <label class="block text-sm font-medium mb-1">Confirm New Password</label>
            <input type="password" name="password_confirmation" class="w-full border rounded px-3 py-2">
          </div>
        </div>

        <div class="flex items-center justify-end gap-3 pt-2">
          <a href="{{ url()->previous() }}" class="px-4 py-2 border rounded">Cancel</a>
          <button class="px-4 py-2 bg-indigo-600 text-white rounded">Save Changes</button>
        </div>
      </form>
    </div>

  </div>
</div>
@endsection
