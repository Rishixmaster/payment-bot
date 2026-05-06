<?php
// bot.php
error_reporting(E_ALL);
ini_set('display_errors', 1);

$botToken = '8287734551:AAHZdBs4Fo1GReozFWtcc-alz6mJR5XNI7E';

function sendMessage($chat_id, $text, $reply_markup = null) {
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

// Webhook logic
$update = json_decode(file_get_contents('php://input'), true);

if (isset($update['message'])) {
    $chat_id = $update['message']['chat']['id'];
    $text = trim($update['message']['text'] ?? '');

    if (strpos($text, '/deposit') === 0) {
        $amount = explode(' ', $text)[1] ?? 100;
        // Yethun create_order.php la call kar
        $result = file_get_contents("https://".$_SERVER['HTTP_HOST']."/create_order.php?amount=".$amount);
        $res = json_decode($result, true);

        if (isset($res['success'])) {
            $keyboard = json_encode([
                'inline_keyboard' => [[['text' => '💸 Pay Now', 'url' => $res['tradeUrl']]]]
            ]);
            sendMessage($chat_id, "💰 Deposit ₹".$amount."\nOrder: ".$res['orderCode'], $keyboard);
        } else {
            sendMessage($chat_id, "Error: ".$res['error'] ?? 'Unknown');
        }
    }
}
?>
