<?php
// bot.php
error_reporting(E_ALL);
ini_set('display_errors', 1);

$botToken = '8287734551:AAHZdBs4Fo1GReozFWtcc-alz6mJR5XNI7E';

function sendMessage($chat_id, $text, $reply_markup = null) {
    global $botToken;
    $url = "https://api.telegram.org/bot$botToken/sendMessage";
    $data = [
        'chat_id' => $chat_id,
        'text' => $text,
        'parse_mode' => 'HTML'
    ];
    if ($reply_markup) $data['reply_markup'] = $reply_markup;
    
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_exec($ch);
    curl_close($ch);
}

function sendPhoto($chat_id, $photo_url, $caption) {
    global $botToken;
    $url = "https://api.telegram.org/bot$botToken/sendPhoto";
    $data = [
        'chat_id' => $chat_id,
        'photo' => $photo_url,
        'caption' => $caption,
        'parse_mode' => 'HTML'
    ];
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
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

        $result = file_get_contents("https://mlpay-bot.onrender.com/create_order.php?action=create_order&amount=$amount");
        $res = json_decode($result, true);

        if (isset($res['success'])) {
            $orderCode = $res['orderCode'];
            
            // QR Code generate (Google Chart API)
            $upiLink = $res['tradeUrl'];   // ML Pay cha link
            $qr_url = "https://api.qrserver.com/v1/create-qr-code/?size=300x300&data=" . urlencode($upiLink);

            $caption = "💰 <b>Deposit Request</b>\n\n";
            $caption .= "Amount: ₹<b>$amount</b>\n";
            $caption .= "Order ID: <code>$orderCode</code>\n\n";
            $caption .= "QR Scan karun payment kar ani\n";
            $caption .= "<b>UTR Number</b> pathav.";

            sendPhoto($chat_id, $qr_url, $caption);
            
            // Order save kar (baadme verify sathi)
            $data = json_encode(["chat_id" => $chat_id, "orderCode" => $orderCode, "amount" => $amount, "status" => "pending"]);
            file_put_contents('orders.txt', $data . "\n", FILE_APPEND);
        } else {
            sendMessage($chat_id, "Error: " . ($res['error'] ?? 'Try again'));
        }
    }

    // UTR Verify (User UTR pathavto)
    elseif (strpos(strtolower($text), 'utr') !== false || is_numeric($text)) {
        sendMessage($chat_id, "✅ UTR Received!\n\nVerify karat ahe, thoda thamba...");
        // Yeth verify logic yeto (manual kinva auto)
    }
}
?>
