<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ProfileController extends Controller
{
    /** Show profile form (manager has no profile features) */
    public function edit()
    {
        $user = Auth::user();

        // Manager → no profile features
        if ($user->role === 'manager') {
            return redirect()
                ->route('manager.dashboard')
                ->with('flash.info', 'Manager accounts do not have profile settings.');
        }

        // Build a simple list of editable fields for the view
        $editable = $user->role === 'customer'
            ? ['name','username','email','phone','address','avatar','password']
            : ['username','phone','address','avatar','password']; // staff

        return view('profile.edit', [
            'user'     => $user,
            'editable' => $editable,
        ]);
    }

    /** Update profile with role-specific rules */
    public function update(Request $request)
    {
        $user = $request->user();

        // Manager → block
        if ($user->role === 'manager') {
            return redirect()
                ->route('manager.dashboard')
                ->with('flash.info', 'Manager accounts do not have profile settings.');
        }

        if ($user->role === 'customer') {
            // Customer can edit everything
            $rules = [
                'name'                  => ['required','string','max:100'],
                'username'              => ['required','alpha_num','min:4','max:30',"unique:users,username,{$user->id}"],
                'email'                 => ['required','email','max:255',"unique:users,email,{$user->id}"],
                'phone'                 => ['nullable','string','max:20'],
                'address'               => ['nullable','string','max:255'],
                'avatar'                => ['nullable','image','max:2048'],
                'password'              => ['nullable','confirmed','min:8'],
            ];
            $allowed = ['name','username','email','phone','address']; // avatar/password handled below

        } else { // staff
            // Staff cannot edit name or email
            $rules = [
                'username'              => ['required','alpha_num','min:4','max:30',"unique:users,username,{$user->id}"],
                'phone'                 => ['nullable','string','max:20'],
                'address'               => ['nullable','string','max:255'],
                'avatar'                => ['nullable','image','max:2048'],
                'password'              => ['nullable','confirmed','min:8'],
            ];
            $allowed = ['username','phone','address']; // avatar/password handled below
        }

        $data = $request->validate($rules);

        // Only keep fields allowed for this role to prevent tampering
        $data = array_intersect_key($data, array_flip($allowed));

        // Handle password if provided
        if ($request->filled('password')) {
            $user->password = $request->input('password'); // hashed via mutator/cast
        }

        // Handle avatar upload
        if ($request->hasFile('avatar')) {
            $path = $request->file('avatar')->store('avatars','public');
            $data['avatar_path'] = $path;
        }

        $user->update($data);

        return back()->with('flash.success', 'Profile updated.');
    }
}
