<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class ProfileController extends Controller
{
    /** Show profile form */
    public function edit()
    {
        return view('profile.edit');
    }

    /** Update profile (manager account is locked) */
    public function update(Request $request)
    {
        $user = $request->user();

        if ($user->role === 'manager') {
            return back()->withErrors(['username' => 'Manager account cannot be changed.']);
        }

        $data = $request->validate([
            'name'                  => ['required', 'string', 'max:100'],
            'username'              => ['required', 'alpha_num', 'min:4', 'max:30', "unique:users,username,{$user->id}"],
            'email'                 => ['required', 'email', 'max:255', "unique:users,email,{$user->id}"],
            'phone'                 => ['nullable', 'string', 'max:20'],
            'address'               => ['nullable', 'string', 'max:255'],
            'avatar'                => ['nullable', 'image', 'max:2048'],
            'password'              => ['nullable', 'confirmed', 'min:8'],
        ]);

        // update password only if provided
        if ($request->filled('password')) {
            $user->password = $data['password']; // hashed by mutator/cast
        }
        unset($data['password']);

        // handle avatar upload
        if ($request->hasFile('avatar')) {
            $path = $request->file('avatar')->store('avatars', 'public');
            $data['avatar_path'] = $path;
        }

        $user->update($data);

        return back()->with('flash.success', 'Profile updated.');
    }
}
