<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Http\Resources\AnnouncementResource;
use App\Models\Announcement;
use App\Jobs\AnnouncementPublishJob;
use Illuminate\Http\Request;

class AnnouncementApiController extends Controller
{
    // Staff + Customer: list announcements
    public function index()
    {
        $announcements = Announcement::latest()->paginate(10);
        return AnnouncementResource::collection($announcements);
    }

    // Staff: create & publish
    public function store(Request $request)
    {
        $this->authorizeRole('staff');

        $data = $request->validate([
            'title' => 'required|string|max:255',
            'body' => 'required|string',
            'channels' => 'nullable|array',
        ]);

        $announcement = Announcement::create($data);

        AnnouncementPublishJob::dispatch($announcement->id, [
            'title' => $announcement->title,
            'message' => $announcement->body,
            'channels' => $data['channels'] ?? ['mail'],
            'recipients' => [], // add later
            'meta' => ['announcement_id' => $announcement->id],
        ]);

        return new AnnouncementResource($announcement);
    }

    // Staff + Customer: view announcement
    public function show(Announcement $announcement)
    {
        return new AnnouncementResource($announcement);
    }

    // Staff: update announcement
    public function update(Request $request, Announcement $announcement)
    {
        $this->authorizeRole('staff');

        $data = $request->validate([
            'title' => 'required|string|max:255',
            'body' => 'required|string',
        ]);

        $announcement->update($data);
        return new AnnouncementResource($announcement);
    }

    // Staff: delete
    public function destroy(Announcement $announcement)
    {
        $this->authorizeRole('staff');

        $announcement->delete();
        return response()->json(['ok' => true, 'message' => 'Announcement deleted.']);
    }

    private function authorizeRole(string $required)
    {
        if (auth()->user()->role !== $required) {
            abort(403, "Only {$required} can perform this action.");
        }
    }
}
