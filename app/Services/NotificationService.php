<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class NotificationService
{
    protected string $projectId;
    protected string $fcmUrl;

    public function __construct(protected FirebaseService $firebase)
    {
        $this->projectId = env('FIREBASE_PROJECT_ID', 'mirror-app-ef781');
        $this->fcmUrl    = "https://fcm.googleapis.com/v1/projects/{$this->projectId}/messages:send";
    }

    /**
     * Envoie un Push FCM + sauvegarde dans Firestore /notifications
     */
    public function sendToUser(string $uid, string $title, string $body, array $data = []): bool
    {
        try {
            // Sauvegarder dans Firestore
            $this->firebase->sendNotificationToUser($uid, $title, $body, $data);

            // Récupérer FCM token et envoyer push
            $user = $this->firebase->getUserById($uid);
            $token = $user['fcmToken'] ?? $user['token'] ?? null;
            if ($token) {
                $this->sendFcmPush($token, $title, $body, $data);
            }

            return true;
        } catch (\Exception $e) {
            Log::error('NotificationService::sendToUser — ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Envoie à tous les utilisateurs Android
     */
    public function sendToAll(string $title, string $body, array $data = []): bool
    {
        try {
            $users = $this->firebase->getAllUsers();
            $sent  = 0;

            foreach ($users as $user) {
                $uid   = $user['id'] ?? null;
                if (!$uid) continue;

                $this->firebase->sendNotificationToUser($uid, $title, $body, $data);

                $token = $user['fcmToken'] ?? $user['token'] ?? null;
                if ($token) {
                    $this->sendFcmPush($token, $title, $body, $data);
                    $sent++;
                }
            }

            Log::info("NotificationService::sendToAll — {$sent} push envoyés");
            return true;
        } catch (\Exception $e) {
            Log::error('NotificationService::sendToAll — ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Notification automatique quand statut commande change
     */
    public function sendOrderStatusNotification(string $uid, string $orderId, string $statut): bool
    {
        $titles = [
            'en_attente' => 'Commande reçue !',
            'confirmée'  => 'Votre commande est confirmée !',
            'expédiée'   => 'Votre colis est en route !',
            'livrée'     => 'Votre colis est arrivé !',
            'annulée'    => 'Votre commande a été annulée',
        ];

        $title = $titles[$statut] ?? 'Mise à jour de commande';
        $body  = "Commande #{$orderId} — Statut : {$statut}";

        return $this->sendToUser($uid, $title, $body, [
            'type'    => 'order_status',
            'orderId' => $orderId,
            'statut'  => $statut,
        ]);
    }

    /**
     * Envoi FCM v1 HTTP API
     */
    protected function sendFcmPush(string $token, string $title, string $body, array $data = []): void
    {
        try {
            $accessToken = $this->getFcmAccessToken();
            if (!$accessToken) return;

            Http::withToken($accessToken)
                ->timeout(10)
                ->post($this->fcmUrl, [
                    'message' => [
                        'token'        => $token,
                        'notification' => ['title' => $title, 'body' => $body],
                        'data'         => array_map('strval', $data),
                        'android'      => [
                            'notification' => [
                                'sound'        => 'default',
                                'click_action' => 'FLUTTER_NOTIFICATION_CLICK',
                            ],
                        ],
                    ],
                ]);
        } catch (\Exception $e) {
            Log::warning('NotificationService::sendFcmPush — ' . $e->getMessage());
        }
    }

    /**
     * Access token OAuth2 pour FCM v1 (même méthode JWT que FirebaseService)
     */
    protected function getFcmAccessToken(): ?string
    {
        try {
            $credPath = base_path(env('FIREBASE_CREDENTIALS',
                'storage/app/mirror-app-ef781-firebase-adminsdk-fbsvc-1727f4848f.json'));
            $creds    = json_decode(file_get_contents($credPath), true);

            $now    = time();
            $claim  = [
                'iss'   => $creds['client_email'],
                'scope' => 'https://www.googleapis.com/auth/firebase.messaging',
                'aud'   => 'https://oauth2.googleapis.com/token',
                'iat'   => $now,
                'exp'   => $now + 3600,
            ];

            $header = rtrim(strtr(base64_encode(json_encode(['alg' => 'RS256', 'typ' => 'JWT'])), '+/', '-_'), '=');
            $body   = rtrim(strtr(base64_encode(json_encode($claim)), '+/', '-_'), '=');
            $data2  = "{$header}.{$body}";
            openssl_sign($data2, $signature, $creds['private_key'], OPENSSL_ALGO_SHA256);
            $sig    = rtrim(strtr(base64_encode($signature), '+/', '-_'), '=');
            $jwt    = "{$data2}.{$sig}";

            $resp = Http::asForm()->post('https://oauth2.googleapis.com/token', [
                'grant_type' => 'urn:ietf:params:oauth:grant-type:jwt-bearer',
                'assertion'  => $jwt,
            ]);

            return $resp->json('access_token');
        } catch (\Exception $e) {
            Log::error('NotificationService::getFcmAccessToken — ' . $e->getMessage());
            return null;
        }
    }
}
