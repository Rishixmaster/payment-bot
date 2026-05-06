<?php
// public_html/create_order.php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

if (isset($_POST['action']) && $_POST['action'] === 'create_order') {
    $amount = isset($_POST['amount']) ? floatval($_POST['amount']) : 100.00;
    $userId = "user123"; // baadme dynamic karu

    $params = [
        "amount" => number_format($amount, 2, '.', ''),
        "appNo" => "KARMAXRISHI",
        "callbackUrl" => "https://darkwepaymentnet.xo.je/callback.php",   // ← Changed
        "name" => "User",
        "orderCode" => "ORDER_" . time() . rand(1000,9999)
    ];

    ksort($params);
    $query_string = http_build_query($params);
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
            $data = $result['data'];
            $orderData = json_encode([
                "userId" => $userId,
                "orderCode" => $data['orderCode'],
                "merchantOrderCode" => $data['merchantOrderCode'],
                "amount" => $amount,
                "status" => "pending"
            ]);

            file_put_contents('orders.txt', $orderData . "\n", FILE_APPEND);

            echo json_encode([
                "success" => true,
                "tradeUrl" => $data['tradeUrl'],
                "orderCode" => $data['orderCode']
            ]);
        } else {
            echo json_encode(["error" => $result['msg'] ?? "Payment creation failed"]);
        }
    } else {
        echo json_encode(["error" => "No response from ML Pay"]);
    }
    exit;
}
?>
