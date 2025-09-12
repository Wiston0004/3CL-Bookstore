<?php

namespace App\Http\Controllers;

use App\Models\Announcement;
use App\Jobs\AnnouncementPublishJob;
use Illuminate\Http\Request;

class AnnouncementController extends Controller
{
    public function index()
    {
        $announcements = Announcement::orderByDesc('created_at')->paginate(10);
        return view('staff.announcements.index', compact('announcements'));
    }

    public function create()
    {
        return view('staff.announcements.create');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'title' => 'required|string|max:255',
            'body' => 'required|string',
            'status' => 'required|in:draft,scheduled,sent,failed',
            'channels' => 'nullable|array', // e.g. ['mail','sms']
        ]);

        $announcement = Announcement::create($data);

        // 🔹 Command + ChainOfResponsibility
        AnnouncementPublishJob::dispatch($announcement->id, [
            'title' => $announcement->title,
            'message' => $announcement->body,
            'channels' => $data['channels'] ?? ['mail'],
            'recipients' => [], // You can later filter target audience
            'meta' => ['announcement_id' => $announcement->id],
        ]);

        return redirect()->route('staff.ann.index')->with('ok', 'Announcement created & queued for publishing.');
    }

    public function edit(Announcement $announcement)
    {
        return view('staff.announcements.edit', compact('announcement'));
    }

    public function update(Request $request, Announcement $announcement)
    {
        $data = $request->validate([
            'title' => 'required|string|max:255',
            'body' => 'required|string',
            'status' => 'required|in:draft,scheduled,sent,failed',
        ]);

        $announcement->update($data);
        return redirect()->route('staff.ann.index')->with('ok', 'Announcement updated.');
    }

    public function destroy(Announcement $announcement)
    {
        $announcement->delete();
        return redirect()->route('staff.ann.index')->with('ok', 'Announcement deleted.');
    }
}
