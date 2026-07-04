<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;

/**
 * Service Firebase via REST API Firestore v1
 * Utilise l'API REST pour éviter les dépendances gRPC/ext-sodium.
 */
class FirebaseService
{
    protected string $projectId;
    protected string $baseUrl;
    protected ?string $cachedToken = null;
    protected int $tokenExpiry    = 0;

    public function __construct()
    {
        $this->projectId = env('FIREBASE_PROJECT_ID', 'mirror-app-ef781');
        $this->baseUrl   = "https://firestore.googleapis.com/v1/projects/{$this->projectId}/databases/(default)/documents";
    }

    // ─── Access Token OAuth2 ───────────────────────────────────────────────────

    protected function getAccessToken(): string
    {
        return Cache::remember('firebase_access_token', 3500, function () {
            $credPath = base_path(env('FIREBASE_CREDENTIALS',
                'storage/app/mirror-app-ef781-firebase-adminsdk-fbsvc-1727f4848f.json'));
            $creds    = json_decode(file_get_contents($credPath), true);

            $now    = time();
            $claim  = [
                'iss'   => $creds['client_email'],
                'scope' => 'https://www.googleapis.com/auth/datastore',
                'aud'   => 'https://oauth2.googleapis.com/token',
                'iat'   => $now,
                'exp'   => $now + 3600,
            ];

            $jwt    = $this->buildJwt($claim, $creds['private_key']);
            $resp   = Http::asForm()->post('https://oauth2.googleapis.com/token', [
                'grant_type' => 'urn:ietf:params:oauth:grant-type:jwt-bearer',
                'assertion'  => $jwt,
            ]);

            return $resp->json('access_token', '');
        });
    }

    private function buildJwt(array $payload, string $privateKey): string
    {
        $header  = base64_encode(json_encode(['alg' => 'RS256', 'typ' => 'JWT']));
        $body    = base64_encode(json_encode($payload));
        $header  = rtrim(strtr($header, '+/', '-_'), '=');
        $body    = rtrim(strtr($body, '+/', '-_'), '=');
        $data    = "{$header}.{$body}";
        openssl_sign($data, $signature, $privateKey, OPENSSL_ALGO_SHA256);
        $sig     = rtrim(strtr(base64_encode($signature), '+/', '-_'), '=');
        return "{$data}.{$sig}";
    }

    // ─── Requête Firestore REST ────────────────────────────────────────────────

    protected function firestoreGet(string $collection, array $query = []): array
    {
        try {
            $token = $this->getAccessToken();
            $url   = "{$this->baseUrl}/{$collection}";
            $resp  = Http::withToken($token)->timeout(15)->get($url, $query);
            if (!$resp->successful()) return [];
            $docs  = $resp->json('documents', []);
            return array_map(fn($d) => $this->parseDoc($d), $docs);
        } catch (\Exception $e) {
            Log::error("FirebaseService::firestoreGet({$collection}) — " . $e->getMessage());
            return [];
        }
    }

    protected function firestoreGetDoc(string $collection, string $docId): ?array
    {
        try {
            $token = $this->getAccessToken();
            $url   = "{$this->baseUrl}/{$collection}/{$docId}";
            $resp  = Http::withToken($token)->timeout(15)->get($url);
            if (!$resp->successful()) return null;
            return $this->parseDoc($resp->json());
        } catch (\Exception $e) {
            Log::error("FirebaseService::firestoreGetDoc — " . $e->getMessage());
            return null;
        }
    }

    protected function firestorePatch(string $collection, string $docId, array $fields): bool
    {
        try {
            $token  = $this->getAccessToken();
            $url    = "{$this->baseUrl}/{$collection}/{$docId}";
            $body   = ['fields' => $this->encodeFields($fields)];
            $masks  = implode('&', array_map(fn($k) => "updateMask.fieldPaths={$k}", array_keys($fields)));
            $resp   = Http::withToken($token)->timeout(15)->patch("{$url}?{$masks}", $body);
            return $resp->successful();
        } catch (\Exception $e) {
            Log::error("FirebaseService::firestorePatch — " . $e->getMessage());
            return false;
        }
    }

    protected function firestorePost(string $collection, array $fields): bool
    {
        try {
            $token = $this->getAccessToken();
            $url   = "{$this->baseUrl}/{$collection}";
            $body  = ['fields' => $this->encodeFields($fields)];
            $resp  = Http::withToken($token)->timeout(15)->post($url, $body);
            return $resp->successful();
        } catch (\Exception $e) {
            Log::error("FirebaseService::firestorePost — " . $e->getMessage());
            return false;
        }
    }

    protected function firestoreDelete(string $collection, string $docId): bool
    {
        try {
            $token = $this->getAccessToken();
            $url   = "{$this->baseUrl}/{$collection}/{$docId}";
            $resp  = Http::withToken($token)->timeout(15)->delete($url);
            return $resp->successful();
        } catch (\Exception $e) {
            Log::error("FirebaseService::firestoreDelete — " . $e->getMessage());
            return false;
        }
    }

    // Requête structurée Firestore (pour filtres WHERE)
    protected function firestoreQuery(string $collection, array $filters = [], string $orderBy = '', string $orderDir = 'DESCENDING', int $limit = 500): array
    {
        try {
            $token = $this->getAccessToken();
            $url   = "https://firestore.googleapis.com/v1/projects/{$this->projectId}/databases/(default)/documents:runQuery";

            $query = [
                'from'  => [['collectionId' => $collection]],
                'limit' => $limit,
            ];

            if (!empty($filters)) {
                $conditions = array_map(fn($f) => [
                    'fieldFilter' => [
                        'field' => ['fieldPath' => $f['field']],
                        'op'    => $f['op'] ?? 'EQUAL',
                        'value' => $this->encodeValue($f['value']),
                    ],
                ], $filters);

                $query['where'] = count($conditions) === 1
                    ? $conditions[0]
                    : ['compositeFilter' => ['op' => 'AND', 'filters' => $conditions]];
            }

            if ($orderBy) {
                $query['orderBy'] = [['field' => ['fieldPath' => $orderBy], 'direction' => $orderDir]];
            }

            $resp = Http::withToken($token)->timeout(15)->post($url, ['structuredQuery' => $query]);
            if (!$resp->successful()) return [];

            return array_values(array_filter(
                array_map(fn($r) => isset($r['document']) ? $this->parseDoc($r['document']) : null, $resp->json()),
                fn($d) => $d !== null
            ));
        } catch (\Exception $e) {
            Log::error("FirebaseService::firestoreQuery({$collection}) — " . $e->getMessage());
            return [];
        }
    }

    // ─── Parseur documents Firestore ──────────────────────────────────────────

    protected function parseDoc(array $doc): array
    {
        $parts = explode('/', $doc['name'] ?? '');
        $id    = end($parts);
        $data  = ['id' => $id];

        foreach ($doc['fields'] ?? [] as $key => $valueWrapper) {
            $data[$key] = $this->decodeValue($valueWrapper);
        }

        return $data;
    }

    protected function decodeValue(array $v): mixed
    {
        if (isset($v['stringValue']))    return $v['stringValue'];
        if (isset($v['integerValue']))   return (int) $v['integerValue'];
        if (isset($v['doubleValue']))    return (float) $v['doubleValue'];
        if (isset($v['booleanValue']))   return (bool) $v['booleanValue'];
        if (isset($v['nullValue']))      return null;
        if (isset($v['timestampValue'])) return $v['timestampValue'];
        if (isset($v['mapValue']))       return array_map(fn($f) => $this->decodeValue($f), $v['mapValue']['fields'] ?? []);
        if (isset($v['arrayValue']))     return array_map(fn($f) => $this->decodeValue($f), $v['arrayValue']['values'] ?? []);
        if (isset($v['referenceValue'])) return $v['referenceValue'];
        return null;
    }

    protected function encodeValue(mixed $v): array
    {
        if (is_null($v))    return ['nullValue' => null];
        if (is_bool($v))    return ['booleanValue' => $v];
        if (is_int($v))     return ['integerValue' => (string) $v];
        if (is_float($v))   return ['doubleValue' => $v];
        if (is_array($v))   return ['arrayValue' => ['values' => array_map([$this, 'encodeValue'], $v)]];
        return ['stringValue' => (string) $v];
    }

    protected function encodeFields(array $fields): array
    {
        $result = [];
        foreach ($fields as $k => $v) {
            $result[$k] = $this->encodeValue($v);
        }
        return $result;
    }

    // ═══════════════════════════════════════════════════════════
    // UTILISATEURS — collection: users
    // ═══════════════════════════════════════════════════════════

    public function getAllUsers(): array
    {
        return $this->firestoreGet('users');
    }

    public function getUserById(string $uid): ?array
    {
        return $this->firestoreGetDoc('users', $uid);
    }

    public function blockUser(string $uid): bool
    {
        return $this->firestorePatch('users', $uid, ['statut' => 'bloqué']);
    }

    public function unblockUser(string $uid): bool
    {
        return $this->firestorePatch('users', $uid, ['statut' => 'actif']);
    }

    public function getUsersStats(string $period = 'month'): array
    {
        $users = $this->getAllUsers();
        $stats = [];
        foreach ($users as $user) {
            $dt = $this->parseDate($user['createdAt'] ?? null);
            if (!$dt) continue;
            $key = $this->periodKey($dt, $period);
            $stats[$key] = ($stats[$key] ?? 0) + 1;
        }
        ksort($stats);
        return $stats;
    }

    // ═══════════════════════════════════════════════════════════
    // COMMANDES — collection: orders
    // ═══════════════════════════════════════════════════════════

    public function getAllOrders(array $filters = []): array
    {
        $firestoreFilters = [];
        if (!empty($filters['userId'])) {
            $firestoreFilters[] = ['field' => 'userId', 'value' => $filters['userId']];
        }

        $orders = $firestoreFilters
            ? $this->firestoreQuery('orders', $firestoreFilters, 'createdAt', 'DESCENDING')
            : $this->firestoreGet('orders');

        // Filtre statut côté PHP : l'app Android écrit le champ "status" à la création,
        // "statut" n'est ajouté que lorsque l'admin change le statut — un filtre Firestore
        // sur un seul de ces deux champs raterait donc la moitié des commandes.
        if (!empty($filters['statut'])) {
            $orders = array_filter($orders, fn($o) => ($o['statut'] ?? $o['status'] ?? 'en_attente') === $filters['statut']);
        }

        // Filtre date côté PHP
        if (!empty($filters['dateDebut'])) {
            $debut  = strtotime($filters['dateDebut']);
            $orders = array_filter($orders, fn($o) => $this->parseTimestamp($o['createdAt'] ?? null) >= $debut);
        }
        if (!empty($filters['dateFin'])) {
            $fin    = strtotime($filters['dateFin']) + 86399;
            $orders = array_filter($orders, fn($o) => $this->parseTimestamp($o['createdAt'] ?? null) <= $fin);
        }

        usort($orders, fn($a, $b) =>
            ($this->parseTimestamp($b['createdAt'] ?? null) ?? 0) - ($this->parseTimestamp($a['createdAt'] ?? null) ?? 0)
        );

        return array_values($orders);
    }

    public function getOrderById(string $orderId): ?array
    {
        return $this->firestoreGetDoc('orders', $orderId);
    }

    public function updateOrderStatus(string $orderId, string $statut): bool
    {
        // Écrit à la fois "statut" (utilisé par l'admin) et "status" (champ d'origine
        // écrit par l'app Android) pour que les deux restent synchronisés.
        return $this->firestorePatch('orders', $orderId, [
            'statut'    => $statut,
            'status'    => $statut,
            'updatedAt' => now()->toIso8601String(),
        ]);
    }

    public function getOrdersByUser(string $uid): array
    {
        return $this->getAllOrders(['userId' => $uid]);
    }

    public function getOrdersStats(string $period = 'month'): array
    {
        $orders = $this->getAllOrders();
        $stats  = [];
        foreach ($orders as $order) {
            $dt = $this->parseDate($order['createdAt'] ?? null);
            if (!$dt) continue;
            $key = $this->periodKey($dt, $period);
            $stats[$key] = ($stats[$key] ?? 0) + 1;
        }
        ksort($stats);
        return $stats;
    }

    public function getRevenueStats(string $period = 'month'): array
    {
        $orders = $this->getAllOrders();
        $stats  = [];
        foreach ($orders as $order) {
            if (($order['statut'] ?? $order['status'] ?? '') === 'annulée') continue;
            $dt = $this->parseDate($order['createdAt'] ?? null);
            if (!$dt) continue;
            $key = $this->periodKey($dt, $period);
            $stats[$key] = ($stats[$key] ?? 0) + (float) ($order['finalTotal'] ?? $order['total'] ?? $order['baseTotal'] ?? 0);
        }
        ksort($stats);
        return $stats;
    }

    public function getTopProducts(int $limit = 5): array
    {
        $orders  = $this->getAllOrders();
        $counter = [];
        foreach ($orders as $order) {
            foreach ($order['items'] ?? [] as $item) {
                $id = $item['productId'] ?? ($item['id'] ?? 'unknown');
                if (!isset($counter[$id])) {
                    $counter[$id] = ['name' => $item['title'] ?? $item['name'] ?? $id, 'qty' => 0, 'revenue' => 0];
                }
                $counter[$id]['qty']     += (int) ($item['quantity'] ?? 1);
                $counter[$id]['revenue'] += (float) ($item['price'] ?? 0) * (int) ($item['quantity'] ?? 1);
            }
        }
        uasort($counter, fn($a, $b) => $b['qty'] - $a['qty']);
        return array_slice(array_values($counter), 0, $limit);
    }

    // ═══════════════════════════════════════════════════════════
    // PRODUITS — collection: products
    // ═══════════════════════════════════════════════════════════

    public function getAllProducts(): array
    {
        return $this->firestoreGet('products');
    }

    public function getProductBySlug(string $slug): ?array
    {
        $results = $this->firestoreQuery('products', [['field' => 'slug', 'value' => $slug]]);
        return $results[0] ?? null;
    }

    public function createOrUpdateProduct(array $data): bool
    {
        $id = $data['id'] ?? null;
        unset($data['id']);
        if ($id) {
            return $this->firestorePatch('products', $id, $data);
        }
        return $this->firestorePost('products', $data);
    }

    public function toggleProductStatus(string $productId): bool
    {
        $product = $this->firestoreGetDoc('products', $productId);
        $current = $product['actif'] ?? true;
        return $this->firestorePatch('products', $productId, ['actif' => !$current]);
    }

    // ═══════════════════════════════════════════════════════════
    // NOTIFICATIONS
    // ═══════════════════════════════════════════════════════════

    public function getAdminNotifications(): array
    {
        return $this->firestoreQuery('notifications_admin', [], 'createdAt', 'DESCENDING', 50);
    }

    public function markNotificationAsRead(string $id): bool
    {
        return $this->firestorePatch('notifications_admin', $id, ['lu' => true]);
    }

    public function sendNotificationToUser(string $uid, string $title, string $body, array $data = []): bool
    {
        return $this->firestorePost('notifications', [
            'userId'    => $uid,
            'title'     => $title,
            'body'      => $body,
            'lu'        => false,
            'createdAt' => now()->toIso8601String(),
        ]);
    }

    // ═══════════════════════════════════════════════════════════
    // OTP
    // ═══════════════════════════════════════════════════════════

    public function getOtpByEmail(string $email): ?array
    {
        $results = $this->firestoreQuery('email_otp_codes', [['field' => 'email', 'value' => $email]]);
        return $results[0] ?? null;
    }

    public function deleteOtp(string $email): bool
    {
        $otp = $this->getOtpByEmail($email);
        if ($otp && !empty($otp['id'])) {
            return $this->firestoreDelete('email_otp_codes', $otp['id']);
        }
        return false;
    }

    public function isOtpValid(string $email, string $code): bool
    {
        $otp = $this->getOtpByEmail($email);
        if (!$otp) return false;
        if ((string) ($otp['code'] ?? '') !== (string) $code) return false;
        $expires = $this->parseTimestamp($otp['expiresAt'] ?? null);
        return $expires && $expires > time();
    }

    // ═══════════════════════════════════════════════════════════
    // MESSAGES
    // ═══════════════════════════════════════════════════════════

    public function getAllMessages(): array
    {
        return $this->firestoreQuery('messages', [], 'createdAt', 'DESCENDING', 200);
    }

    public function getMessagesByUser(string $uid): array
    {
        // La collection "messages" de l'app Android identifie une conversation par
        // "chatId" (format "{uid}_admin"), pas par un champ "userId".
        return $this->firestoreQuery('messages', [['field' => 'chatId', 'value' => "{$uid}_admin"]], 'createdAt', 'ASCENDING');
    }

    public function sendMessage(string $uid, string $text, string $senderName = 'Support AfriSale'): bool
    {
        return $this->firestorePost('messages', [
            'chatId'     => "{$uid}_admin",
            'senderId'   => 'admin',
            'senderName' => $senderName,
            'text'       => $text,
            'isFromUser' => false,
            'read'       => false,
            'createdAt'  => now()->toIso8601String(),
        ]);
    }

    public function markMessagesAsRead(string $uid): void
    {
        foreach ($this->getMessagesByUser($uid) as $msg) {
            if (($msg['isFromUser'] ?? false) && !($msg['read'] ?? false)) {
                $this->firestorePatch('messages', $msg['id'], ['read' => true]);
            }
        }
    }

    // ═══════════════════════════════════════════════════════════
    // HELPERS
    // ═══════════════════════════════════════════════════════════

    protected function parseDate(mixed $value): ?\DateTime
    {
        if (!$value) return null;
        try {
            if (is_string($value)) return new \DateTime($value);
            if (is_int($value) || is_float($value)) {
                return (new \DateTime())->setTimestamp((int) $value);
            }
        } catch (\Exception) {}
        return null;
    }

    protected function parseTimestamp(mixed $value): ?int
    {
        $dt = $this->parseDate($value);
        return $dt ? $dt->getTimestamp() : null;
    }

    protected function periodKey(\DateTime $dt, string $period): string
    {
        return match ($period) {
            'week'  => $dt->format('Y-W'),
            'month' => $dt->format('Y-m'),
            default => $dt->format('Y-m-d'),
        };
    }
}
