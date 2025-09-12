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

            $u->points -= $data['points'];
            $u->save();

            return [
                'user_id'   => $u->id,
                'redeemed'  => $data['points'],
                'remaining' => $u->points,
            ];
        });

        return response()->json([
            'message' => 'Points redeemed successfully.',
            'data'    => $result,
        ]);
    }
}
