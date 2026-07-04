<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\FirebaseService;
use App\Services\NotificationService;
use Illuminate\Http\Request;

class FirebaseNotificationController extends Controller
{
    public function __construct(
        protected FirebaseService     $firebase,
        protected NotificationService $notification
    ) {}

    public function index()
    {
        $notifications = $this->firebase->getAdminNotifications();
        $unread        = count(array_filter($notifications, fn($n) => !($n['lu'] ?? false)));

        return view('admin.firebase.notifications.index', compact('notifications', 'unread'));
    }

    public function sendAll(Request $request)
    {
        $request->validate([
            'title' => 'required|string|max:100',
            'body'  => 'required|string|max:300',
        ]);

        $this->notification->sendToAll($request->title, $request->body);
        return back()->with('success', 'Notification envoyée à tous les utilisateurs Android.');
    }

    public function sendUser(Request $request)
    {
        $request->validate([
            'uid'   => 'required|string',
            'title' => 'required|string|max:100',
            'body'  => 'required|string|max:300',
        ]);

        $this->notification->sendToUser($request->uid, $request->title, $request->body);
        return back()->with('success', 'Notification envoyée au client.');
    }

    public function markRead(string $id)
    {
        $this->firebase->markNotificationAsRead($id);
        return back()->with('success', 'Notification marquée comme lue.');
    }
}
