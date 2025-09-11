<?php

namespace App\Http\Controllers;

use App\Models\Announcement;

class AnnouncementCustomerController extends Controller
{
    public function index()
    {
        $announcements = Announcement::latest()->paginate(10);
        return view('customer.announcements.index', compact('announcements'));
    }

    public function show(Announcement $announcement)
    {
        return view('customer.announcements.show', compact('announcement'));
    }
}
