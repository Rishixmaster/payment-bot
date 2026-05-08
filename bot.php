<?php
// bot.php - Fixed & Improved
error_reporting(E_ALL);
ini_set('display_errors', 1);

$botToken = '8287734551:AAHZdBs4Fo1GReozFWtcc-alz6mJR5XNI7E';

function sendMessage($chat_id, $text) {
    global $botToken;
    $url = "https://api.telegram.org/bot$botToken/sendMessage";
    $data = [
        'chat_id' => $chat_id,
        'text' => $text,
        'parse_mode' => 'HTML'
    ];
    
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_exec($ch);
    curl_close($ch);
}

// ================== Main Logic ==================
$update = json_decode(file_get_contents('php://input'), true);

if (isset($update['message'])) {
    $chat_id = $update['message']['chat']['id'];
    $text = trim($update['message']['text']);

    if (strpos($text, '/deposit') === 0) {
        $amount = explode(' ', $text)[1] ?? 100;

        sendMessage($chat_id, "🔄 Order creating... ₹".$amount);

        // Better way to call create_order.php
        $create_url = "https://mlpay-bot.onrender.com/create_order.php";

        $postData = http_build_query([
            'action' => 'create_order',
            'amount' => $amount
        ]);

        $ch = curl_init($create_url);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $postData);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        $response = curl_exec($ch);
        $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        sendMessage($chat_id, "HTTP Code: ".$http_code."\nRaw Response: ".$response);

        $res = json_decode($response, true);

        if ($res && isset($res['success']) && $res['success'] == true) {
            // QR Code Logic pudhe yeto
            sendMessage($chat_id, "✅ Order Created Successfully!");
        } else {
            sendMessage($chat_id, "❌ Failed\nError: " . ($res['error'] ?? 'Empty response'));
        }
    }
}
?>
