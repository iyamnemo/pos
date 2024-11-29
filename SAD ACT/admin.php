<?php
$host = 'localhost';
$db = 'order_system';
$user = 'root';
$pass = '';
$conn = new PDO("mysql:host=$host;dbname=$db", $user, $pass);

// Handle Add, Edit, and Delete Product actions
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['action'])) {
        $action = $_POST['action'];
        $product_id = $_POST['product_id'];

        
        $stmt = $conn->prepare("SELECT COUNT(*) FROM products WHERE product_id = ?");
        $stmt->execute([$product_id]);
        $existingProductCount = $stmt->fetchColumn();

        if ($action == 'add') {
            if ($existingProductCount > 0) {
                $error = "Error: Product ID '$product_id' already exists!";
            } else {
                $imagePath = '';
                if ($_FILES['image']['name']) {
                    $targetDir = "uploads/";
                    $imagePath = $targetDir . basename($_FILES["image"]["name"]);
                    move_uploaded_file($_FILES["image"]["tmp_name"], $imagePath);
                }
                $stmt = $conn->prepare("INSERT INTO products (product_id, name, stock, price, image_path) VALUES (?, ?, ?, ?, ?)");
                $stmt->execute([$_POST['product_id'], $_POST['name'], $_POST['stock'], $_POST['price'], $imagePath]);
            }
        } elseif ($action == 'edit') {
            if ($existingProductCount > 1) { 
                $error = "Error: Product ID '$product_id' already exists for another product!";
            } else {
                $stmt = $conn->prepare("UPDATE products SET name=?, stock=?, price=? WHERE product_id=?");
                $stmt->execute([$_POST['name'], $_POST['stock'], $_POST['price'], $product_id]);
            }
        } elseif ($action == 'delete') {
            $stmt = $conn->prepare("DELETE FROM products WHERE product_id=?");
            $stmt->execute([$product_id]);
        }
    }
}


$products = $conn->query("SELECT * FROM products")->fetchAll();
?>

<!DOCTYPE html>
<html>
<head>
    <title>Admin Panel</title>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <link rel="stylesheet" href="admin.css">
</head>
<body>


    
    <a href="index.php" class="admin-link">Switch to User Side?</a>
    <h1>Admin Panel - Manage Products</h1>

    <?php if (isset($error)): ?>
        <p style="color: red;"><?php echo $error; ?></p>
    <?php endif; ?>

  
    <form method="POST" enctype="multipart/form-data">
        <input type="hidden" name="action" value="add">
        <input type="text" name="product_id" placeholder="Product ID" required>
        <input type="text" name="name" placeholder="Product Name" required>
        <input type="number" name="stock" placeholder="Stock" required>
        <input type="text" name="price" placeholder="Price" required>
        <input type="file" name="image" accept="image/*">
        <button type="submit">Add Product</button>
    </form>

    <h2>Product List</h2>
    <ul id="product-list">
        <?php foreach ($products as $product): ?>
            <li>
                <img src="<?php echo $product['image_path']; ?>" alt="Image" width="50">
                <strong><?php echo "{$product['name']} ({$product['product_id']})"; ?></strong> - 
                <span>Price: ₱<?php echo number_format($product['price'], 2); ?></span> - 
                <span>Stock Left: <span id="stock_<?php echo $product['product_id']; ?>"><?php echo $product['stock']; ?></span></span>
                
               
                <button onclick="editProduct('<?php echo $product['product_id']; ?>', '<?php echo $product['name']; ?>', <?php echo $product['stock']; ?>, '<?php echo $product['price']; ?>')">Edit</button>
                
              
                <form method="POST" style="display:inline;">
                    <input type="hidden" name="action" value="delete">
                    <input type="hidden" name="product_id" value="<?php echo $product['product_id']; ?>">
                    <button type="submit">Delete</button>
                </form>
            </li>
        <?php endforeach; ?>
    </ul>

   
    <div id="edit-form" style="display:none;">
        <h2>Edit Product</h2>
        <form method="POST">
            <input type="hidden" name="action" value="edit">
            <input type="hidden" name="product_id" id="edit_product_id">
            <input type="text" name="name" id="edit_name" placeholder="Product Name">
            <input type="number" name="stock" id="edit_stock" placeholder="Stock">
            <input type="text" name="price" id="edit_price" placeholder="Price">
            <button type="submit">Save Changes</button>
        </form>
    </div>

    <script>
        function editProduct(id, name, stock, price) {
            
            document.getElementById("edit_product_id").value = id;
            document.getElementById("edit_name").value = name;
            document.getElementById("edit_stock").value = stock;
            document.getElementById("edit_price").value = price;
            document.getElementById("edit-form").style.display = "block";
        }

        // AJAX call to fetch live stock count from the database
        function fetchLiveStock() {
            $.ajax({
                url: 'fetch_stock.php',
                method: 'GET',
                success: function(response) {
                    let stocks = JSON.parse(response);
                    for (let product_id in stocks) {
                        document.getElementById(`stock_${product_id}`).innerText = stocks[product_id];
                    }
                }
            });
        }

       
        setInterval(fetchLiveStock, 5000);
    </script>
</body>
</html>
