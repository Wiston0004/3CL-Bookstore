<?php

namespace App\Http\Controllers;

use App\Models\Event;
use App\Models\User;
use App\Jobs\ScheduleEventJob;
use App\Jobs\MakeEventLiveJob;
use App\Jobs\CancelEventJob;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class EventController extends Controller
{
    public function index(){
        $events = Event::latest('starts_at')->paginate(12);
        return view('events.index', compact('events'));
    }

    public function create(){
        return view('events.create');
    }

    public function store(Request $r){
        $data = $r->validate([
            'title'=>'required|string|max:255',
            'description'=>'nullable|string',
            'type'=>'required|in:book_fair,flash_sale,author_lecture,webinar,other',
            'delivery_mode'=>'required|in:onsite,online,hybrid',
            'starts_at'=>'required|date',
            'ends_at'=>'nullable|date|after_or_equal:starts_at',
            'visibility'=>'required|in:public,private,targeted',
            'target_role'=>'nullable|string',
            'join_url'=>'nullable|string',
            'venue_name'=>'nullable|string',
            'address'=>'nullable|string',
            'points_reward'=>'nullable|integer|min:0',
        ]);

        $base = Str::slug($data['title']); $slug=$base; $i=1;
        while (Event::where('slug',$slug)->exists()) $slug = $base.'-'.(++$i);

        $event = Event::create([
            'title'=>$data['title'],
            'slug'=>$slug,
            'description'=>$data['description'] ?? null,
            'type'=>$data['type'],
            'delivery_mode'=>$data['delivery_mode'],
            'starts_at'=>$data['starts_at'],
            'ends_at'=>$data['ends_at'] ?? null,
            'visibility'=>$data['visibility'],
            'status'=>Event::DRAFT,
            'join_url'=>$data['join_url'] ?? null,
            'venue_name'=>$data['venue_name'] ?? null,
            'address'=>$data['address'] ?? null,
            'organizer_id'=>auth()->id(),
            'points_reward'=>$data['points_reward'] ?? 0,
        ]);

        // Command: schedule now
        ScheduleEventJob::dispatch($event->id);

        //Optional recipients by role (for go-live announcement)
        $recipients = User::when($r->filled('target_role'), fn($q)=>$q->where('role',$r->input('target_role')))
            ->get(['id','name','email','phone']);

        MakeEventLiveJob::dispatch($event->id, [
            'title'=>"Event Live: {$event->title}",
            'message'=>"Happening now! {$event->description}",
            'channels'=>['mail','push'],
            'recipients'=>$recipients,
            'meta'=>['event_id'=>$event->id],
        ])->delay($event->starts_at);

        return redirect()->route('events.index')->with('ok','Event created & scheduled.');
    }

    public function cancel(Event $event, Request $r){
        CancelEventJob::dispatch($event->id, $r->input('reason'));
        return back()->with('ok', 'Event cancellation queued.');
    }
}
