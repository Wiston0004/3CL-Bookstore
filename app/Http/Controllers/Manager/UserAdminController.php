<?php

namespace App\Http\Controllers\Manager;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Validation\Rules\Password;

class UserAdminController extends Controller
{
    /** List staff & customers with search + pagination */
    public function index(Request $request)
    {
        $users = User::query()
            ->whereIn('role', ['staff', 'customer'])
            ->when(
                $request->search,
                fn($q, $s) => $q->where(fn($w) => $w
                    ->where('username', 'like', "%{$s}%")
                    ->orWhere('email', 'like', "%{$s}%")
                    ->orWhere('name', 'like', "%{$s}%"))
            )
            ->latest()
            ->paginate(12);

        return view('manager.users.index', compact('users'));
    }

    /** Show create form */
    public function create()
    {
        return view('manager.users.create');
    }

    /** Create staff/customer */
    public function store(Request $request)
    {
        $data = $request->validate([
            'role'                  => ['required', 'in:staff,customer'],
            'name'                  => ['required', 'string', 'max:100'],
            'username'              => ['required', 'alpha_num', 'min:4', 'max:30', 'unique:users,username'],
            'email'                 => ['required', 'email', 'max:255', 'unique:users,email'],
            'password'              => ['required', 'confirmed', Password::min(8)->letters()->numbers()->mixedCase()],
            'phone'                 => ['nullable', 'string', 'max:20'],
            'address'               => ['nullable', 'string', 'max:255'],
            'points'                => ['nullable', 'integer', 'min:0'],
        ]);

        User::create($data); // password hashed by mutator/cast

        return redirect()
            ->route('manager.users.index')
            ->with('flash.success', 'User created.');
    }

    /** Show edit form */
    public function edit(User $user)
    {
        abort_if($user->role === 'manager', 403);
        return view('manager.users.edit', compact('user'));
    }

    /** Update staff/customer */
    public function update(Request $request, User $user)
    {
        abort_if($user->role === 'manager', 403);

        $data = $request->validate([
            'role'                  => ['required', 'in:staff,customer'],
            'name'                  => ['required', 'string', 'max:100'],
            'username'              => ['required', 'alpha_num', 'min:4', 'max:30', "unique:users,username,{$user->id}"],
            'email'                 => ['required', 'email', 'max:255', "unique:users,email,{$user->id}"],
            'password'              => ['nullable', 'confirmed', 'min:8'],
            'phone'                 => ['nullable', 'string', 'max:20'],
            'address'               => ['nullable', 'string', 'max:255'],
            'points'                => ['nullable', 'integer', 'min:0'],
        ]);

        // Only set password if provided
        if (empty($data['password'])) {
            unset($data['password']);
        }

        $user->update($data);

        return back()->with('flash.success', 'User updated.');
    }

    /** Soft-delete staff/customer */
    public function destroy(User $user)
    {
        abort_if($user->role === 'manager', 403);

        $user->delete();

        return back()->with('flash.success', 'User deleted.');
    }

    /** (Optional) Show user details page */
    public function show(User $user)
    {
        abort_if($user->role === 'manager', 403);
        return view('manager.users.show', compact('user'));
    }
}
