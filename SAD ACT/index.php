<?php
$host = 'localhost';
$db = 'order_system';
$user = 'root';
$pass = '';
$conn = new PDO("mysql:host=$host;dbname=$db", $user, $pass);

// Fetch sa products
$products = $conn->query("SELECT * FROM products")->fetchAll();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Product List</title>
    <link rel="stylesheet" href="index.css">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script>
        let cart = {};

        // Filtering sestem
        function filterProducts() {
            let filterValue = document.getElementById("filter").value.toLowerCase();
            document.querySelectorAll(".product").forEach(product => {
                let id = product.getAttribute("data-id").toLowerCase();
                let name = product.getAttribute("data-name").toLowerCase();
                product.style.display = (id.includes(filterValue) || name.includes(filterValue)) ? "block" : "none";
            });
        }

        // Add item to cart
        function addToCart(productId, name, price, stock) {
            if (cart[productId]) {
                if (cart[productId].quantity < stock) cart[productId].quantity++;
            } else {
                cart[productId] = { name, price, quantity: 1, stock };
            }
            updateStockDisplay(productId, stock);
            updateCartDisplay();
            updateTotal();
        }

        // Remove item from cart
        function removeFromCart(productId, stock) {
            if (cart[productId]) {
                cart[productId].quantity--;
                if (cart[productId].quantity <= 0) delete cart[productId];
            }
            updateStockDisplay(productId, stock);
            updateCartDisplay();
            updateTotal();
        }

        // Update the stock display
        function updateStockDisplay(productId, stock) {
            let displayStock = stock - (cart[productId] ? cart[productId].quantity : 0);
            document.getElementById(`stock_${productId}`).innerText = displayStock;
        }

        // Update the cart display
        function updateCartDisplay() {
            let cartContainer = document.getElementById("cart-container");
            cartContainer.innerHTML = "<h3>Cart</h3>";
            for (let id in cart) {
                cartContainer.innerHTML += `
                    <p>${cart[id].name} (${id}) - Quantity: ${cart[id].quantity}
                    <button class="boton" onclick="removeFromCart('${id}', ${cart[id].stock})">Remove from Cart</button></p>
                `;
            }
        }

        // Update the total price display
        function updateTotal() {
            let totalContainer = document.getElementById("product-details");
            totalContainer.innerHTML = "<h3>Product Details</h3>";
            let total = 0;
            for (let id in cart) {
                let itemTotal = cart[id].price * cart[id].quantity;
                total += itemTotal;
                totalContainer.innerHTML += `<p>${cart[id].name} - Quantity: ${cart[id].quantity} - Price: ₱${cart[id].price.toFixed(2)} - Total: ₱${itemTotal.toFixed(2)}</p>`;
            }
            totalContainer.innerHTML += `<p><strong>Total Amount: ₱${total.toFixed(2)}</strong></p>`;
            totalContainer.innerHTML += `<button class="boton" onclick="checkout()">Checkout</button>`;
        }

        // Checkout function
        function checkout() {
            let totalAmount = 0;
            for (let id in cart) {
                totalAmount += cart[id].price * cart[id].quantity;
            }

            // sa tax
            let vatTax = totalAmount * 0.12;
            let totalAmountWithTax = totalAmount + vatTax;

            
            let userName = prompt("Enter your name:");
            let paymentAmount = parseFloat(prompt("Enter the amount you will pay:"));

           
            if (paymentAmount < totalAmountWithTax) {
                alert("Insufficient payment. Please enter a larger amount.");
                return;
            }

            
            let change = paymentAmount - totalAmountWithTax;
            generateReceipt(userName, vatTax, totalAmount, totalAmountWithTax, change);
        }

        // Generate the receipt
        function generateReceipt(userName, vatTax, subtotal, totalAmountWithTax, change) {
            let receiptPanel = document.getElementById("receipt-panel");
            let receiptHTML = `
                <h3>Receipt</h3>
                <div class="receipt-content">
                    <p>Date: ${new Date().toLocaleString()}</p>
                    <p>Name: ${userName}</p>
                    <p>----------------------------------------</p>
            `;
            for (let id in cart) {
                let itemTotal = cart[id].price * cart[id].quantity;
                receiptHTML += `
                    <p>${cart[id].name} x ${cart[id].quantity} @ ₱${cart[id].price.toFixed(2)} each = ₱${itemTotal.toFixed(2)}</p>
                `;
            }

            receiptHTML += `
                <p>----------------------------------------</p>
                <p>Subtotal: ₱${subtotal.toFixed(2)}</p>
                <p>VAT Tax (12%): ₱${vatTax.toFixed(2)}</p>
                <p>Total: ₱${totalAmountWithTax.toFixed(2)}</p>
                <p>Amount Paid: ₱${(subtotal + vatTax).toFixed(2)}</p>
                <p>Change: ₱${change.toFixed(2)}</p>
                <button onclick="closeReceipt()">Close</button>
            </div>`;
            receiptPanel.innerHTML = receiptHTML;
            receiptPanel.classList.add("show");
        }

        // Close the receipt
        function closeReceipt() {
            document.getElementById("receipt-panel").classList.remove("show");
            cart = {}; 
            updateCartDisplay();
            updateTotal();
        }
    </script>
</head>
<body>

<!-- Background display yun gomagalaw -->
<video autoplay loop muted playsinline id="background-video">
        <source src="uploads/bgpink.mp4" type="video/mp4">
        Your browser does not support the video tag.
    </video>
  
    <a href="admin.php" class="admin-link">Switch to Admin Side?</a>

    <!-- Main Content -->
    <h1> checkout the best lubong in market</h1>
    <input type="text" id="filter" oninput="filterProducts()" placeholder="Filter by ID or name">

    <div id="product-list">
        <?php foreach ($products as $product): ?>
            <div class="product" data-id="<?php echo $product['product_id']; ?>" data-name="<?php echo $product['name']; ?>">
                <img src="<?php echo $product['image_path']; ?>" alt="<?php echo $product['name']; ?>">
                <h3><?php echo $product['name']; ?> (<?php echo $product['product_id']; ?>)</h3>
                <p>Price: ₱<?php echo number_format($product['price'], 2); ?></p>
                <p>Stock Left: <span id="stock_<?php echo $product['product_id']; ?>"><?php echo $product['stock']; ?></span></p>
                <button onclick="addToCart('<?php echo $product['product_id']; ?>', '<?php echo $product['name']; ?>', <?php echo $product['price']; ?>, <?php echo $product['stock']; ?>)">Add to Cart</button>
            </div>
        <?php endforeach; ?>
    </div>

    <div class="container">

    <!-- Cart KOntener -->
    <div id="cart-container">
        <h3>Cart</h3>
    </div>

    <!-- Product dets kontener -->
    <div id="product-details">
        <h3>Product Details</h3>
    </div>
    </div>

    <!-- Resivo -->
    <div id="receipt-panel"></div>
</body>
</html>
