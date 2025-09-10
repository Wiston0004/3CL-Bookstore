<?php

namespace App\Http\Controllers;

use App\Models\Event;
use App\Jobs\ScheduleEventJob;
use App\Jobs\CancelEventJob;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class EventController extends Controller
{
    public function index()
    {
        $events = Event::orderByDesc('starts_at')->paginate(10);
        return view('staff.events.index', compact('events'));
    }

    public function create()
    {
        return view('staff.events.create');
    }

    public function store(Request $request)
    {
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
        $data['organizer_id'] = auth()->id();
        $data['status'] = 'draft';

        // 🔹 Command Pattern: delegate scheduling to a Job
        $event = Event::create($data);
        ScheduleEventJob::dispatch($event->id);

        return redirect()->route('staff.events.index')->with('ok', 'Event created and scheduled.');
    }

    public function edit(Event $event)
    {
        return view('staff.events.edit', compact('event'));
    }

    public function update(Request $request, Event $event)
    {
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
        return redirect()->route('staff.events.index')->with('ok', 'Event updated.');
    }

    public function destroy(Event $event)
    {
        $event->delete(); // deletes the row from DB
        return redirect()->route('staff.events.index')
                        ->with('ok', 'Event deleted.');
    }
}
