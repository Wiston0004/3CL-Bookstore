<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PointsApiController extends Controller
{
    /**
     * Redeem (deduct) user points
     * POST /api/v1/users/{user}/points/redeem
     */
    public function redeem(Request $request, User $user)
    {
        $data = $request->validate([
            'points' => ['required', 'integer', 'min:1'],
        ]);

        $result = DB::transaction(function () use ($user, $data) {
            $u = User::whereKey($user->id)->lockForUpdate()->firstOrFail();

            if ($u->points < $data['points']) {
                abort(422, 'Insufficient points');
            }

            $u->points -= (int) $data['points'];
            $u->save();

            return [
                'user_id'   => $u->id,
                'redeemed'  => (int) $data['points'],
                'remaining' => (int) $u->points,
            ];
        });

        return response()->json([
            'message' => 'Points redeemed successfully.',
            'data'    => $result,
        ], 200);
    }

    /**
     * Add user points
     * POST /api/v1/users/{user}/points/add
     */
    public function add(Request $request, User $user)
    {
        $data = $request->validate([
            'points' => ['required', 'integer', 'min:1'],
        ]);

        $result = DB::transaction(function () use ($user, $data) {
            $u = User::whereKey($user->id)->lockForUpdate()->firstOrFail();

            $u->points += (int) $data['points'];
            $u->save();

            return [
                'user_id'  => $u->id,
                'added'    => (int) $data['points'],
                'balance'  => (int) $u->points,
            ];
        });

        return response()->json([
            'message' => 'Points added successfully.',
            'data'    => $result,
        ], 200);
    }
}
