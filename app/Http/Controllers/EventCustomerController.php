<?php

namespace App\Http\Controllers;

use App\Models\Event;
use App\Models\EventRegistration;
use App\Jobs\AwardPointsForRegistration;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

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

        // 🔹 Award points asynchronously
        AwardPointsForRegistration::dispatch($registration->id);

        $userId = auth()->id();
        $points = 0;
        $success = false;

        // 🔹 Try EXTERNAL API first
        try {
            $base = rtrim(config('services.users_api.base'), '/');
            $timeout = (float) config('services.users_api.timeout', 5);

            $res = Http::timeout($timeout)
                ->acceptJson()
                ->get("$base/users/{$userId}/points");

            if ($res->ok() && isset($res['data']['points'])) {
                $points = (int) $res['data']['points'];
                $success = true;
            }
        } catch (\Throwable $e) {
            Log::warning("External API points fetch failed", [
                'user_id' => $userId,
                'error'   => $e->getMessage(),
            ]);
        }

        // 🔹 If external fails → INTERNAL API
        if (!$success) {
            try {
                $internalBase = url('/api/v1');
                $res = Http::acceptJson()
                    ->get("$internalBase/users/{$userId}/points");

                if ($res->ok() && isset($res['data']['points'])) {
                    $points = (int) $res['data']['points'];
                }
            } catch (\Throwable $e) {
                Log::error("Internal API points fetch failed", [
                    'user_id' => $userId,
                    'error'   => $e->getMessage(),
                ]);
                $points = 0; // safe default
            }
        }

        return redirect()
            ->route('cust.events.show', $event->slug)
            ->with('ok', "🎉 You have successfully registered for this event! Your total points: {$points}");
    }
}
