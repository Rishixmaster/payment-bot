<?php
// create_order.php
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

    ksort($params);
    $query_string = http_build_query($params);
    $secret_key = "dtdLggJaWaltMXrtJVVs";
    $final_string = $query_string . "&key=" . $secret_key;
    $signature = strtolower(md5($final_string));

    $params["sign"] = $signature;

    $json_data = json_encode($params, JSON_UNESCAPED_SLASHES);

    // Debug Output
    $debug = [
        "status" => "debug",
        "amount" => $amount,
        "orderCode" => $params["orderCode"],
        "final_string" => $final_string,
        "signature" => $signature,
        "json_sent" => $json_data
    ];

    $ch = curl_init("https://api.mlpayment.cc/open-api/trade/collection");
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $json_data);
    curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    $response = curl_exec($ch);
    curl_close($ch);

    if ($response) {
        $result = json_decode($response, true);
        if (isset($result['code']) && $result['code'] == 200) {
            echo json_encode([
                "success" => true,
                "tradeUrl" => $result['data']['tradeUrl'],
                "orderCode" => $result['data']['orderCode']
            ]);
        } else {
            $debug['mlpay_response'] = $result;
            echo json_encode(["error" => $result['msg'] ?? "Signature Error", "debug" => $debug]);
        }
    } else {
        echo json_encode(["error" => "No response from ML Pay"]);
    }
    exit;
}

echo "create_order.php loaded";
?>
