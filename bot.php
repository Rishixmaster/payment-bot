<?php
// bot.php - Full Final Version for Render
error_reporting(E_ALL);
ini_set('display_errors', 1);

$botToken = '8287734551:AAHZdBs4Fo1GReozFWtcc-alz6mJR5XNI7E';

// Function to send message
function sendMessage($chat_id, $text, $reply_markup = null) {
    global $botToken;
    $url = "https://api.telegram.org/bot$botToken/sendMessage";
    
    $data = [
        'chat_id' => $chat_id,
        'text' => $text,
        'parse_mode' => 'HTML'
    ];
    
    if ($reply_markup) {
        $data['reply_markup'] = $reply_markup;
    }

    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_exec($ch);
    curl_close($ch);
}

// Main Webhook Logic
$update = json_decode(file_get_contents('php://input'), true);

if (isset($update['message'])) {
    $chat_id = $update['message']['chat']['id'];
    $text = trim($update['message']['text'] ?? '');

    // Start Command
    if (strpos($text, '/start') === 0) {
        sendMessage($chat_id, "✅ <b>ML Pay Bot Ready Ahe!</b>\n\n/deposit 100 command use kar.");
    }
    
    // Deposit Command
    elseif (strpos($text, '/deposit') === 0) {
        $parts = explode(' ', $text);
        $amount = isset($parts[1]) ? floatval($parts[1]) : 100.00;

        if ($amount < 10) {
            sendMessage($chat_id, "❌ Minimum deposit ₹10 ahe.");
            exit;
        }

        // Call create_order.php
        $create_url = "https://mlpay-bot.onrender.com/create_order.php";
        
        $postData = [
            'action' => 'create_order',
            'amount' => $amount
        ];

        $ch = curl_init($create_url);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $postData);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $response = curl_exec($ch);
        curl_close($ch);

        $result = json_decode($response, true);

        if (isset($result['success']) && $result['success'] === true) {
            $tradeUrl = $result['tradeUrl'];
            $orderCode = $result['orderCode'];

            $message = "💰 <b>Deposit Request Created</b>\n\n";
            $message .= "Amount: ₹<b>" . $amount . "</b>\n";
            $message .= "Order ID: <code>" . $orderCode . "</code>\n\n";
            $message .= "Payment करण्यासाठी खालील बटण दाबा 👇";

            $keyboard = json_encode([
                'inline_keyboard' => [
                    [
                        ['text' => '💸 Pay Now', 'url' => $tradeUrl]
                    ]
                ]
            ]);

            sendMessage($chat_id, $message, $keyboard);
        } else {
            sendMessage($chat_id, "❌ Error: " . ($result['error'] ?? 'Unknown error while creating order'));
        }
    }
    
    // Unknown command
    else {
        sendMessage($chat_id, "Command samajli nahi.\n\nUse kar:\n/start\n/deposit 100");
    }
}

// Browser madhe open kele tar message
else {
    echo "ML Pay Telegram Bot Running Successfully on Render!";
}
?>
