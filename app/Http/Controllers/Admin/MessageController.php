<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Message;

class MessageController extends Controller
{
    public function index()
    {
        $conversations = Message::with('user')
            ->latest()
            ->get()
            ->groupBy('user_id')
            ->map(function ($messages) {
                $last = $messages->first();
                return [
                    'user'     => $last->user,
                    'last_msg' => $last->body,
                    'count'    => $messages->count(),
                    'unread'   => $messages->where('sender', 'client')->where('is_read', false)->count(),
                    'date'     => $last->created_at,
                ];
            })
            ->sortByDesc('date')
            ->values();

        return view('admin.messages.index', [
            'conversations' => $conversations,
            'total'         => Message::count(),
        ]);
    }
}
