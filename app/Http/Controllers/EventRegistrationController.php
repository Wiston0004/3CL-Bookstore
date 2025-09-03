<?php

namespace App\Http\Controllers;

use App\Models\Event;
use App\Models\EventRegistration;
use Illuminate\Http\Request;

class EventRegistrationController extends Controller
{
    public function list()
    {
        // public listing for customers (upcoming/live)
        $events = Event::whereIn('status', [Event::SCHEDULED, Event::LIVE])
            ->orderBy('starts_at')->paginate(12);
        return view('events.list', compact('events'));
    }

    public function show(Event $event)
    {
        return view('events.show', compact('event'));
    }

    public function store(Request $r, Event $event)
    {
        $r->validate([
            'name'=>'nullable|string|max:255',
            'email'=>'nullable|email',
            'phone'=>'nullable|string|max:50',
        ]);

        EventRegistration::firstOrCreate(
            ['event_id'=>$event->id,'user_id'=>auth()->id()],
            [
                'name'=>auth()->user()->name ?? $r->string('name'),
                'email'=>auth()->user()->email ?? $r->string('email'),
                'phone'=>$r->string('phone'),
                'status'=>'registered','source'=>'web'
            ]
        );

        return back()->with('ok','Registered. Points (if any) will be awarded automatically.');
    }
}
