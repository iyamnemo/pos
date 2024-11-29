<?php
$host = 'localhost';
$db = 'order_system';
$user = 'root';
$pass = '';
$conn = new PDO("mysql:host=$host;dbname=$db", $user, $pass);

// Fetch live stock counts
$products = $conn->query("SELECT product_id, stock FROM products")->fetchAll(PDO::FETCH_ASSOC);

$stockData = [];
foreach ($products as $product) {
    $stockData[$product['product_id']] = $product['stock'];
}

// Return data as JSON
echo json_encode($stockData);
?>
