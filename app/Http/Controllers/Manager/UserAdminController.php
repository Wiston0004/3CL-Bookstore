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
        // Inputs (with sane defaults)
        $search = trim((string) $request->input('search', ''));
        $role   = $request->input('role');               // staff|customer|null
        $sort   = $request->input('sort', 'id');         // id|name
        $dir    = $request->input('dir', 'desc');        // asc|desc

        // Whitelist to prevent SQL injection
        $sort = in_array($sort, ['id','name'], true) ? $sort : 'id';
        $dir  = $dir === 'asc' ? 'asc' : 'desc';

        $q = \App\Models\User::query()->whereIn('role', ['staff','customer']);

        if (in_array($role, ['staff','customer'], true)) {
            $q->where('role', $role);
        }

        if ($search !== '') {
            $q->where(function ($w) use ($search) {
                $w->where('username','like',"%{$search}%")
                ->orWhere('email','like',"%{$search}%")
                ->orWhere('name','like',"%{$search}%");
            });
        }

        $q->orderBy($sort, $dir)->orderBy('id','desc'); // deterministic secondary sort

        $users = $q->paginate(12)->withQueryString();   // keep filters on pagination links

        return view('manager.users.index', compact('users','search','role','sort','dir'));
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
