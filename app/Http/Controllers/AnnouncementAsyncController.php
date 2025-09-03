<?php

namespace App\Http\Controllers;

use App\Jobs\AnnouncementPublishJob;
use App\Models\Announcement;
use App\Models\User;
use Illuminate\Http\Request;

class AnnouncementAsyncController extends Controller
{
    public function create(){
        return view('announcements.create_job');
    }

    public function store(Request $r)
    {
        $data = $r->validate([
            'title'=>'required|string|max:255',
            'body'=>'required|string',
            'channels'=>'required|array',
            'role'=>'nullable|string',
        ]);

        $announce = Announcement::create([
            'title'=>$data['title'],
            'body'=>$data['body'],
            'channels'=>$data['channels'],
            'status'=>'scheduled',
            'scheduled_at'=>now(),
        ]);

        $recipients = User::when($r->filled('role'), fn($q)=>$q->where('role',$r->string('role')))
            ->get(['id','name','email','phone']);

        AnnouncementPublishJob::dispatch(
            $announce->id,
            [

                'title'=>$data['title'],

                'message'=>$data['body'],

                'channels'=>$data['channels'],
                'recipients'=>$recipients,
                'meta'=>['announcement_id'=>$announce->id],
            ]
        );

        return back()->with('ok','Announcement queued and will be published via Pipeline.');
    }
}
