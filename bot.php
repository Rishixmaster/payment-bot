<?php
// bot.php - Debug Version
error_reporting(E_ALL);
ini_set('display_errors', 1);

$botToken = '8287734551:AAHZdBs4Fo1GReozFWtcc-alz6mJR5XNI7E';

function sendMessage($chat_id, $text) {
    global $botToken;
    $url = "https://api.telegram.org/bot$botToken/sendMessage";
    $data = ['chat_id' => $chat_id, 'text' => $text, 'parse_mode' => 'HTML'];
    
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_exec($ch);
    curl_close($ch);
}

$update = json_decode(file_get_contents('php://input'), true);

if (isset($update['message'])) {
    $chat_id = $update['message']['chat']['id'];
    $text = trim($update['message']['text']);

    if (strpos($text, '/deposit') === 0) {
        $amount = explode(' ', $text)[1] ?? 100;

        sendMessage($chat_id, "🔄 Order creating... Amount: ₹".$amount);

        // Call create_order
        $response = file_get_contents("https://mlpay-bot.onrender.com/create_order.php?action=create_order&amount=".$amount);
        
        sendMessage($chat_id, "Raw Response:\n".$response);   // ← Debug

        $res = json_decode($response, true);

        if ($res && isset($res['success']) && $res['success']) {
            sendMessage($chat_id, "✅ Order Created! QR yeta ahe...");
        } else {
            sendMessage($chat_id, "❌ Create Order Failed\nError: " . ($res['error'] ?? $response));
        }
    }
}
?>
