<?php
// create_order.php - Debug Version
error_reporting(E_ALL);
ini_set('display_errors', 1);

if (isset($_POST['action']) && $_POST['action'] === 'create_order') {
    
    $amount = number_format((float)$_POST['amount'], 2, '.', '');

    $params = [
        "amount"      => $amount,
        "appNo"       => "KARMAXRISHI",
        "callbackUrl" => "https://mlpay-bot.onrender.com/callback.php",
        "name"        => "User",
        "remark"      => "Telegram Deposit",
        "orderCode"   => "TG_" . time() . rand(10000,99999)
    ];

    // === Debug Log ===
    file_put_contents('sign_debug.log', date('H:i:s') . " - Params: " . json_encode($params) . "\n", FILE_APPEND);

    ksort($params);
    $query_string = http_build_query($params);
    $secret_key = "dtdLggJaWaltMXrtJVVs";
    $final_string = $query_string . "&key=" . $secret_key;
    
    $signature = strtolower(md5($final_string));

    file_put_contents('sign_debug.log', date('H:i:s') . " - Final String: " . $final_string . "\nSignature: " . $signature . "\n\n", FILE_APPEND);

    $params["sign"] = $signature;

    $json_data = json_encode($params, JSON_UNESCAPED_SLASHES);

    $ch = curl_init("https://api.mlpayment.cc/open-api/trade/collection");
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $json_data);
    curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    $response = curl_exec($ch);
    curl_close($ch);

    file_put_contents('sign_debug.log', date('H:i:s') . " - ML Pay Response: " . $response . "\n\n", FILE_APPEND);

    if ($response) {
        $result = json_decode($response, true);
        if (isset($result['code']) && $result['code'] == 200) {
            echo json_encode([
                "success" => true,
                "tradeUrl" => $result['data']['tradeUrl'],
                "orderCode" => $result['data']['orderCode']
            ]);
        } else {
            echo json_encode(["error" => $result['msg'] ?? "Signature Error"]);
        }
    } else {
        echo json_encode(["error" => "No response"]);
    }
    exit;
}

echo "create_order.php loaded";
?>
