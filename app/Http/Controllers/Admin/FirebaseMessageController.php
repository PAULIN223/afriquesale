<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\FirebaseService;

class FirebaseMessageController extends Controller
{
    public function __construct(protected FirebaseService $firebase) {}

    public function index()
    {
        $messages = $this->firebase->getAllMessages();

        // Conversations identifiées par "chatId" ("{uid}_admin"), pas par un champ userId.
        // Triées DESC par createdAt : le premier message rencontré par chatId est le plus récent.
        $conversations = [];
        foreach ($messages as $msg) {
            $chatId = $msg['chatId'] ?? 'unknown_admin';
            $uid    = str_ends_with($chatId, '_admin') ? substr($chatId, 0, -strlen('_admin')) : $chatId;

            if (!isset($conversations[$chatId])) {
                $conversations[$chatId] = [
                    'uid'      => $uid,
                    'name'     => ($msg['isFromUser'] ?? true) ? ($msg['senderName'] ?? 'Client') : 'Client',
                    'last_msg' => $msg['text'] ?? $msg['body'] ?? '',
                    'count'    => 0,
                    'unread'   => 0,
                    'date'     => $msg['createdAt'] ?? null,
                ];
            } elseif (($msg['isFromUser'] ?? true) && $conversations[$chatId]['name'] === 'Client') {
                // Complète le nom du client dès qu'on croise un de ses messages (le plus récent
                // peut être une réponse admin dont on ne connaît pas le nom du client).
                $conversations[$chatId]['name'] = $msg['senderName'] ?? 'Client';
            }
            $conversations[$chatId]['count']++;
            if (($msg['isFromUser'] ?? false) && !($msg['read'] ?? false)) {
                $conversations[$chatId]['unread']++;
            }
        }

        return view('admin.firebase.messages.index', [
            'conversations' => array_values($conversations),
            'total'         => count($messages),
        ]);
    }
}
