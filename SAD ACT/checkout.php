<?php
$host = 'localhost';
$db = 'order_system';
$user = 'root';
$pass = '';
$conn = new PDO("mysql:host=$host;dbname=$db", $user, $pass);

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $cart = json_decode($_POST['cart'], true);

    foreach ($cart as $productId => $item) {
        $stmt = $conn->prepare("SELECT stock FROM products WHERE product_id = ?");
        $stmt->execute([$productId]);
        $stock = $stmt->fetchColumn();

        if ($stock >= $item['quantity']) {
            $newStock = $stock - $item['quantity'];
            $updateStmt = $conn->prepare("UPDATE products SET stock = ? WHERE product_id = ?");
            $updateStmt->execute([$newStock, $productId]);
        } else {
            echo "Insufficient stock for {$item['name']}";
            exit;
        }
    }
    echo "Checkout successful! Thank you for your purchase.";
}
?>
