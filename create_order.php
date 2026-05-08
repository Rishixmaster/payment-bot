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
        "orderCode"   => "ORDER_" . time() . rand(1000,9999)
    ];

    ksort($params);
    $query_string = http_build_query($params);
    $secret_key = "dtdLggJaWaltMXrtJVVs";
    $final_string = $query_string . "&key=" . $secret_key;
    $signature = strtolower(md5($final_string));

    $params["sign"] = $signature;

    $json_data = json_encode($params);

    $ch = curl_init("https://api.mlpayment.cc/open-api/trade/collection");
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $json_data);
    curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    $response = curl_exec($ch);
    curl_close($ch);

    $result = json_decode($response, true);

    if (isset($result['code']) && $result['code'] == 200) {
        echo json_encode([
            "success" => true,
            "tradeUrl" => $result['data']['tradeUrl'],
            "orderCode" => $result['data']['orderCode'],
            "amount" => $amount
        ]);
    } else {
        echo json_encode(["error" => $result['msg'] ?? "API Error"]);
    }
}
?>
