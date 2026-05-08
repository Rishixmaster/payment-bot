<?php
// bot.php
error_reporting(E_ALL);
ini_set('display_errors', 1);

$botToken = '8287734551:AAHZdBs4Fo1GReozFWtcc-alz6mJR5XNI7E';

function sendMessage($chat_id, $text, $reply_markup = null) {
    global $botToken;
    $url = "https://api.telegram.org/bot$botToken/sendMessage";
    $data = ['chat_id' => $chat_id, 'text' => $text, 'parse_mode' => 'HTML'];
    if ($reply_markup) $data['reply_markup'] = $reply_markup;
    
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_exec($ch);
    curl_close($ch);
}

function sendPhoto($chat_id, $photo_url, $caption) {
    global $botToken;
    $url = "https://api.telegram.org/bot$botToken/sendPhoto";
    $data = ['chat_id' => $chat_id, 'photo' => $photo_url, 'caption' => $caption, 'parse_mode' => 'HTML'];
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

        $response = file_get_contents("https://mlpay-bot.onrender.com/create_order.php?action=create_order&amount=".$amount);
        $res = json_decode($response, true);

        if (isset($res['success'])) {
            $qr_url = "https://api.qrserver.com/v1/create-qr-code/?size=450x450&data=" . urlencode($res['tradeUrl']);

            $caption = "💳 <b>Deposit Request</b>\n\n";
            $caption .= "Amount: ₹<b>" . $amount . "</b>\n";
            $caption .= "Order ID: <code>" . $res['orderCode'] . "</code>\n\n";
            $caption .= "QR Scan कर Payment कर\n";
            $caption .= "Payment केल्यानंतर <b>UTR / Transaction ID</b> इथे पाठवा";

            sendPhoto($chat_id, $qr_url, $caption);
        } else {
            sendMessage($chat_id, "❌ " . ($res['error'] ?? 'Error'));
        }
    }

    // UTR Handle
    elseif (strlen($text) > 8) {
        sendMessage($chat_id, "✅ UTR Received: <code>$text</code>\n\nVerify करत आहे...");
        // Yeth verify logic add karu shakto
    }
}
?>
