<?php
// callback.php
ini_set('display_errors', 1);
error_reporting(E_ALL);

$botToken = '8287734551:AAHZdBs4Fo1GReozFWtcc-alz6mJR5XNI7E';

// ML Pay kadun yeto te data ghe
$input = file_get_contents('php://input');
$data = json_decode($input, true);

if (empty($data)) {
    $data = $_POST;   // kahi veles POST madhe yete
}

// Log kar (debug sathi)
file_put_contents('callback_log.txt', date('Y-m-d H:i:s') . " - " . json_encode($data) . "\n", FILE_APPEND);

$orderCode = $data['orderCode'] ?? $data['merchantOrderCode'] ?? '';

// Orders file madhun check kar
$orders = file('orders.txt', FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
$chat_id = null;
$amount = 0;

foreach ($orders as $line) {
    $orderData = json_decode($line, true);
    if (isset($orderData['orderCode']) && $orderData['orderCode'] == $orderCode) {
        $chat_id = $orderData['userId'];   // currently user123 ahe, baadme real chat_id thev
        $amount = $orderData['amount'];
        break;
    }
}

if ($chat_id && isset($data['status']) && $data['status'] == 'SUCCESS') {
    
    $text = "✅ <b>Payment Successful!</b>\n\n";
    $text .= "Amount: ₹<b>" . $amount . "</b>\n";
    $text .= "Order ID: <code>" . $orderCode . "</code>\n\n";
    $text .= "Tujha balance add zala ahe! 🎉";

    // Telegram la message pathav
    $apiUrl = "https://api.telegram.org/bot$botToken/sendMessage";
    $postData = [
        'chat_id' => $chat_id,
        'text' => $text,
        'parse_mode' => 'HTML'
    ];

    $ch = curl_init($apiUrl);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $postData);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_exec($ch);
    curl_close($ch);

    echo "SUCCESS";  // ML Pay la success return kar
} else {
    echo "PENDING or FAILED";
}
?>
