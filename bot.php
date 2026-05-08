<?php
// bot.php - Best Possible for ML Pay
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
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
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

        $create_url = "https://mlpay-bot.onrender.com/create_order.php";
        $postData = http_build_query(['action' => 'create_order', 'amount' => $amount]);

        $ch = curl_init($create_url);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $postData);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $response = curl_exec($ch);
        curl_close($ch);

        $res = json_decode($response, true);

        if (isset($res['success']) && $res['success']) {
            $tradeUrl = $res['tradeUrl'];
            $orderCode = $res['orderCode'];

            // Big QR Code
            $qr_url = "https://api.qrserver.com/v1/create-qr-code/?size=450x450&data=" . urlencode($tradeUrl);

            $caption = "💰 <b>Deposit Request</b>\n\n";
            $caption .= "Amount: ₹<b>" . $amount . "</b>\n";
            $caption .= "Order ID: <code>" . $orderCode . "</code>\n\n";
            $caption .= "🔻 QR Scan कर Payment कर\n";
            $caption .= "🔹 किंवा खालील बटण दाबा 👇";

            $keyboard = json_encode([
                'inline_keyboard' => [
                    [['text' => '🌐 Open Payment Page', 'url' => $tradeUrl]]
                ]
            ]);

            sendPhoto($chat_id, $qr_url, $caption);
        } else {
            sendMessage($chat_id, "❌ Error: " . ($res['error'] ?? 'Try again'));
        }
    }

    // UTR Handle
    elseif (strlen($text) > 8) {
        sendMessage($chat_id, "✅ <b>UTR / Transaction ID Received</b>\n\n<code>" . $text . "</code>\n\nPayment Verify करत आहे...");
        // Yeth verify logic add kar
    }
}
?>
