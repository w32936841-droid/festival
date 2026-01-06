<?php
// Telegram Bot API Integration for Festival System v1.0.1
require_once __DIR__ . '/../config.php';

class TelegramBotAPI {
    private $apiUrl;
    private $token;

    public function __construct() {
        $this->token = BOT_API_TOKEN;
        $this->apiUrl = BOT_API_URL;
    }

    /**
     * Check if user exists in the bot system
     */
    public function checkUser($chatId) {
        try {
            $url = $this->apiUrl . '/users';
            $data = [
                'actions' => 'user',
                'chat_id' => $chatId
            ];

            $response = $this->makeRequest($url, $data);

            if ($response && isset($response['status']) && $response['status'] === 'success') {
                return [
                    'valid' => true,
                    'user_data' => $response['user'] ?? null
                ];
            }

            return [
                'valid' => false,
                'error' => $response['message'] ?? 'User not found'
            ];
        } catch (Exception $e) {
            return [
                'valid' => false,
                'error' => 'API Error: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Get available products from bot API
     */
    public function getProducts($limit = 10, $page = 1) {
        try {
            $url = $this->apiUrl . '/product';
            $data = [
                'actions' => 'products',
                'limit' => $limit,
                'page' => $page
            ];

            $response = $this->makeRequest($url, $data);

            if ($response && isset($response['status']) && $response['status'] === 'success') {
                return [
                    'success' => true,
                    'products' => $response['products'] ?? [],
                    'pagination' => $response['pagination'] ?? null
                ];
            }

            return [
                'success' => false,
                'error' => $response['message'] ?? 'Failed to fetch products'
            ];
        } catch (Exception $e) {
            return [
                'success' => false,
                'error' => 'API Error: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Create discount code via bot API
     */
    public function createDiscountCode($discountData) {
        try {
            $url = $this->apiUrl . '/discount';
            $data = [
                'actions' => 'discount_sell_add',
                'code' => $discountData['code'],
                'price' => $discountData['price'] ?? 0,
                'limit_discount' => $discountData['limit_discount'] ?? 1,
                'agent' => $discountData['agent'] ?? 'allusers',
                'product_id' => $discountData['product_id'] ?? 'all',
                'panel_id' => $discountData['panel_id'] ?? '/all',
                'time' => $discountData['time'] ?? 24,
                'type' => $discountData['type'] ?? 'all'
            ];

            $response = $this->makeRequest($url, $data);

            if ($response && isset($response['status']) && $response['status'] === 'success') {
                return [
                    'success' => true,
                    'discount_id' => $response['discount_id'] ?? null,
                    'message' => $response['message'] ?? 'Discount created successfully'
                ];
            }

            return [
                'success' => false,
                'error' => $response['message'] ?? 'Failed to create discount'
            ];
        } catch (Exception $e) {
            return [
                'success' => false,
                'error' => 'API Error: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Send notification to user via bot
     */
    public function sendNotification($chatId, $message) {
        try {
            $url = $this->apiUrl . '/sendMessage';
            $data = [
                'actions' => 'send_message',
                'chat_id' => $chatId,
                'text' => $message,
                'parse_mode' => 'HTML'
            ];

            $response = $this->makeRequest($url, $data);

            return [
                'success' => isset($response['status']) && $response['status'] === 'success',
                'message_id' => $response['message_id'] ?? null
            ];
        } catch (Exception $e) {
            return [
                'success' => false,
                'error' => 'Failed to send notification: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Make HTTP request to bot API
     */
    private function makeRequest($url, $data) {
        $ch = curl_init();

        curl_setopt_array($ch, [
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => json_encode($data),
            CURLOPT_HTTPHEADER => [
                'Content-Type: application/json',
                'Token: ' . $this->token,
                'Accept: application/json'
            ],
            CURLOPT_TIMEOUT => 30,
            CURLOPT_SSL_VERIFYPEER => false, // For development only
            CURLOPT_USERAGENT => 'FestivalSystem/1.0.1'
        ]);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error = curl_error($ch);

        curl_close($ch);

        if ($error) {
            throw new Exception('cURL Error: ' . $error);
        }

        if ($httpCode !== 200) {
            throw new Exception('HTTP Error: ' . $httpCode);
        }

        $decoded = json_decode($response, true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new Exception('Invalid JSON response');
        }

        return $decoded;
    }
}

// Utility functions for easy access
function checkTelegramUser($chatId) {
    $bot = new TelegramBotAPI();
    return $bot->checkUser($chatId);
}

function createDiscountInBot($discountData) {
    $bot = new TelegramBotAPI();
    return $bot->createDiscountCode($discountData);
}

function sendTelegramNotification($chatId, $message) {
    $bot = new TelegramBotAPI();
    return $bot->sendNotification($chatId, $message);
}
?>
