<?php

namespace App\Http\Controllers;

use App\Models\Event;
use App\Models\EventRegistration;
use App\Jobs\AwardPointsForRegistration;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class EventCustomerController extends Controller
{
    public function index()
    {
        $events = Event::whereNotIn('status', ['cancelled'])->orderBy('starts_at')->paginate(10);
        return view('customer.events.index', compact('events'));
    }

    public function show(Event $event)
    {
        return view('customer.events.show', compact('event'));
    }

    public function register(Request $request, Event $event)
    {
        $registration = EventRegistration::firstOrCreate([
            'event_id' => $event->id,
            'user_id'  => auth()->id(),
        ]);

        // 🔹 Command Pattern: award points asynchronously
        AwardPointsForRegistration::dispatch($registration->id);

        // ✅ Try external API to get points first
        $userId = auth()->id();
        $points = 0;

        try {
            $base = rtrim(config('services.users_api.base'), '/');
            $timeout = (float) config('services.users_api.timeout', 5);

            $res = Http::timeout($timeout)
                ->acceptJson()
                ->get("$base/users/{$userId}/points");

            if ($res->ok() && isset($res['data']['points'])) {
                $points = (int) $res['data']['points'];
            } else {
                // fallback internal
                $points = auth()->user()->points ?? 0;
            }
        } catch (\Throwable $e) {
            // fallback internal
            $points = auth()->user()->points ?? 0;
        }

        return redirect()
            ->route('cust.events.show', $event->slug)
            ->with('ok', "🎉 You have successfully registered for this event! Your total points: {$points}");
    }
}
