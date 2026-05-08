<?php
// create_order.php - Fixed Signature Version
error_reporting(E_ALL);
ini_set('display_errors', 1);

if (isset($_POST['action']) && $_POST['action'] === 'create_order') {
    
    $amount = number_format((float)$_POST['amount'], 2, '.', '');

    $params = [
        "amount"      => $amount,
        "appNo"       => "KARMAXRISHI",
        "callbackUrl" => "https://mlpay-bot.onrender.com/callback.php",
        "name"        => "User",
        "orderCode"   => "ORDER_" . time() . rand(1000, 9999)
    ];

    // === Original Working Signature Method ===
    ksort($params);
    
    $query_string = "";
    foreach ($params as $key => $value) {
        $query_string .= $key . "=" . $value . "&";
    }
    $query_string = rtrim($query_string, "&");

    $secret_key = "dtdLggJaWaltMXrtJVVs";
    $final_string = $query_string . "&key=" . $secret_key;
    
    $signature = strtolower(md5($final_string));

    $params["sign"] = $signature;

    $json_data = json_encode($params, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);

    $api_url = "https://api.mlpayment.cc/open-api/trade/collection";

    $ch = curl_init($api_url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $json_data);
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
            echo json_encode([
                "error" => $result['msg'] ?? 'Signature / API Error'
            ]);
        }
    } else {
        echo json_encode(["error" => "No response from ML Pay"]);
    }
    exit;
}

echo "create_order.php is loaded";
?>
