<?php
// bot.php - Final QR Code + UTR Flow
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

// ================== Main Logic ==================
$update = json_decode(file_get_contents('php://input'), true);

if (isset($update['message'])) {
    $chat_id = $update['message']['chat']['id'];
    $text = trim($update['message']['text']);

    // Deposit Command
    if (strpos($text, '/deposit') === 0) {
        $amount = explode(' ', $text)[1] ?? 100;

        if ($amount < 10) {
            sendMessage($chat_id, "❌ Minimum ₹10 allowed.");
            exit;
        }

        sendMessage($chat_id, "🔄 Order creating... ₹".$amount);

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

            // QR Code Generate
            $qr_url = "https://api.qrserver.com/v1/create-qr-code/?size=350x350&data=" . urlencode($tradeUrl);

            $caption = "💰 <b>Deposit Request</b>\n\n";
            $caption .= "Amount: ₹<b>" . $amount . "</b>\n";
            $caption .= "Order ID: <code>" . $orderCode . "</code>\n\n";
            $caption .= "QR Code Scan कर payment कर.\n\n";
            $caption .= "✅ Payment केल्यानंतर <b>UTR Number</b> इथे पाठवा.";

            sendPhoto($chat_id, $qr_url, $caption);

            // Order Save (for future verification)
            $orderData = json_encode([
                'chat_id' => $chat_id,
                'orderCode' => $orderCode,
                'amount' => $amount,
                'status' => 'pending'
            ]);
            file_put_contents('orders.txt', $orderData . "\n", FILE_APPEND);
        } else {
            sendMessage($chat_id, "❌ Order creation failed.");
        }
    }

    // UTR Number Handle
    elseif (strlen($text) > 8 && (strpos(strtolower($text), 'utr') !== false || is_numeric($text))) {
        sendMessage($chat_id, "✅ UTR Received!\n\nPayment verify करत आहे, कृपया थोडा वेळ थांबा...");
        // Yeth manual verify kinva auto callback logic yeto
    }
}
?>
