<?php

namespace App\Http\Controllers\Manager;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;

/**
 * @method static \Illuminate\Routing\Controller middleware($middleware, array $options = [])
 */

class UserAdminController extends Controller
{
    public function __construct()
    {
        // Routes already have auth + role:manager middleware; this is just defense-in-depth.
        $this->middleware('auth');
    }

    /**
     * List users with:
     * - filter by role (manager|staff|customer)
     * - sort by id or name (asc|desc)
     * - simple search (name/email)
     */
    public function index(Request $request)
    {
        $q = User::query();

        // --- filter by role ---
        if ($role = $request->input('role')) {
            $q->where('role', $role);
        }

        // --- search (name/email) ---
        if ($search = trim((string)$request->input('search', ''))) {
            $q->where(function ($x) use ($search) {
                $x->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            });
        }

        // --- sort (id|name) ---
        $allowedSort = ['id', 'name'];
        $sort = in_array($request->input('sort'), $allowedSort, true)
            ? $request->input('sort')
            : 'id';

        $dir = $request->input('dir') === 'asc' ? 'asc' : 'desc';

        $users = $q->orderBy($sort, $dir)
                   ->paginate(20)
                   ->withQueryString();

        return view('manager.users.index', compact('users', 'sort', 'dir'));
    }

    // Resource placeholders (not used; route resource() was provided)
    public function create() { abort(404); }
    public function store(Request $request) { abort(404); }
    public function show(User $user) { abort(404); }
    public function edit(User $user) { abort(404); }
    public function update(Request $request, User $user) { abort(404); }
    public function destroy(User $user) { abort(404); }
}
