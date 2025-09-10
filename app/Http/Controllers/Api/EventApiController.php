<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Http\Resources\EventResource;
use App\Http\Resources\EventRegistrationResource;
use App\Models\Event;
use App\Models\EventRegistration;
use App\Jobs\ScheduleEventJob;
use App\Jobs\CancelEventJob;
use App\Jobs\AwardPointsForRegistration;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class EventApiController extends Controller
{
    // Staff + Customer: list events
    public function index()
    {
        $events = Event::latest('starts_at')->paginate(10);
        return EventResource::collection($events);
    }

    // Staff: create event
    public function store(Request $request)
    {
        $this->authorizeRole('staff');

        $data = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'type' => 'required|string',
            'delivery_mode' => 'required|string',
            'starts_at' => 'required|date',
            'ends_at' => 'nullable|date|after_or_equal:starts_at',
            'visibility' => 'required|string',
            'points_reward' => 'nullable|integer|min:0',
        ]);

        $data['slug'] = Str::slug($data['title']) . '-' . uniqid();
        $data['organizer_id'] = $request->user()->id;
        $data['status'] = Event::DRAFT;

        $event = Event::create($data);
        ScheduleEventJob::dispatch($event->id);

        return new EventResource($event);
    }

    // Staff + Customer: view event
    public function show(Event $event)
    {
        return new EventResource($event);
    }

    // Staff: update event
    public function update(Request $request, Event $event)
    {
        $this->authorizeRole('staff');

        $data = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'type' => 'required|string',
            'delivery_mode' => 'required|string',
            'starts_at' => 'required|date',
            'ends_at' => 'nullable|date|after_or_equal:starts_at',
            'visibility' => 'required|string',
            'points_reward' => 'nullable|integer|min:0',
        ]);

        $event->update($data);
        return new EventResource($event);
    }

    // Staff: delete/cancel event
    public function destroy(Event $event)
    {
        $this->authorizeRole('staff');

        CancelEventJob::dispatch($event->id, 'Deleted via API');
        return response()->json(['ok' => true, 'message' => 'Event cancelled.']);
    }

    // Customer: register for event
    public function register(Request $request, Event $event)
    {
        $this->authorizeRole('customer');

        $registration = EventRegistration::firstOrCreate([
            'event_id' => $event->id,
            'user_id' => $request->user()->id,
        ]);

        AwardPointsForRegistration::dispatch($registration->id);

        return new EventRegistrationResource($registration);
    }

    private function authorizeRole(string $required)
    {
        if (auth()->user()->role !== $required) {
            abort(403, "Only {$required} can perform this action.");
        }
    }
}
