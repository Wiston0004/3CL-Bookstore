<?php

namespace App\Http\Controllers;

use App\Models\Event;
use App\Models\EventRegistration;
use App\Jobs\AwardPointsForRegistration;
use Illuminate\Http\Request;

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
            'user_id' => auth()->id(),
        ]);

        // 🔹 Command Pattern: award points asynchronously
        AwardPointsForRegistration::dispatch($registration->id);

        return back()->with('ok', 'You registered for this event. Points will be awarded.');
    }
}
