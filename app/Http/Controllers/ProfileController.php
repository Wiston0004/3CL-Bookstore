<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Storage;

class ProfileController extends Controller
{
    /**
     * Show the profile form.
     * Managers don't need profile features: redirect them away.
     */
    public function edit(Request $request)
    {
        $user = $request->user();

        if ($user->role === 'manager') {
            // Manager: no profile page as per requirement
            return redirect()
                ->route('manager.dashboard')
                ->with('err', 'Managers do not have profile settings. Use Manage Users instead.');
        }

        return view('profile.edit', ['user' => $user]);
    }

    /**
     * Update the user profile.
     * - Staff: cannot change name/email.
     * - Customer: can change everything.
     */
    public function update(Request $request)
    {
        $user = $request->user();

        if ($user->role === 'manager') {
            // Manager: keep consistent with edit()
            return redirect()
                ->route('manager.dashboard')
                ->with('err', 'Managers do not have profile settings. Use Manage Users instead.');
        }

        // Base validation rules (we'll trim them for staff)
        $rules = [
            'name'   => ['sometimes','string','max:255'],
            'email'  => [
                'sometimes','email','max:255',
                Rule::unique('users','email')->ignore($user->id),
            ],
            'phone'  => ['nullable','string','max:50'],
            'address'=> ['nullable','string','max:255'],
            'password' => ['nullable','confirmed','min:8'],
            'avatar'   => ['nullable','image','mimes:jpg,jpeg,png,webp','max:2048'],
        ];

        // Staff cannot change name/email — remove those rules to ignore updates even if posted
        if ($user->role === 'staff') {
            unset($rules['name'], $rules['email']);
        }

        $data = $request->validate($rules);

        // Ensure staff's name/email never change server-side
        if ($user->role === 'staff') {
            unset($data['name'], $data['email']);
        }

        // Handle avatar upload
        if ($request->hasFile('avatar')) {
            // Delete old avatar if any
            if ($user->avatar_path && Storage::disk('public')->exists($user->avatar_path)) {
                Storage::disk('public')->delete($user->avatar_path);
            }

            $path = $request->file('avatar')->store('avatars', 'public');
            $data['avatar_path'] = $path;
        }

        // If password empty, do not overwrite
        if (empty($data['password'])) {
            unset($data['password']);
        }
        // NOTE: Your User model already auto-hashes password via setPasswordAttribute()

        // Update and save
        $user->fill($data)->save();

        return back()->with('ok', 'Profile updated successfully.');
    }
}
