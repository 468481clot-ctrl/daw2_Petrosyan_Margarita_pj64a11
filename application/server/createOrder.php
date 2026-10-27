
<?php

function calculateTotal($products, $quantities) {
    $prices = array(
        1 => 100,
        2 => 80,
        3 => 30,
        4 => 10
    );

    $total = 0;
    $selectedProducts = array();

    foreach ($products as $id => $productName) {
        $quantity = intval($quantities[$id]);

        if ($quantity > 0) {
            $price = $prices[$id];
            $subtotal = $price * $quantity;
            $total = $total + $subtotal;

            $selectedProducts[] = array(
                "name" => $productName,
                "quantity" => $quantity,
                "unitPrice" => $price,
                "subtotal" => $subtotal
            );
        }
    }

    $vat = $total * 0.21;
    $totalWithVat = $total + $vat;

    return array(
        "products" => $selectedProducts,
        "subtotal" => $total,
        "vat" => $vat,
        "totalWithVat" => $totalWithVat
    );
}

function showError() {
    http_response_code(404);
    header("Content-Type: text/html; charset=utf-8");

    echo "<h1>Error 404</h1>";
    echo "<p>There was a problem opening or saving the orders file.</p>";
    echo "<button onclick=\"window.location.href='../client/viewOrders.html'\">Back to View Orders</button>";
    echo "<button onclick=\"window.location.href='../client/operations.html'\">Back to Operations</button>";
    echo "<button onclick=\"window.location.href='../client/index.html'\">Back to Home</button>";
    exit;
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $orderId = $_POST["orderId"];
    $customerName = $_POST["customerName"];
    $address = $_POST["address"];
    $email = $_POST["email"];
    $phone = $_POST["phone"];

    $products = isset($_POST["products"]) ? $_POST["products"] : array();
    $quantities = isset($_POST["quantities"]) ? $_POST["quantities"] : array();

    $result = calculateTotal($products, $quantities);

    $order = array(
        "orderId" => $orderId,
        "customerName" => $customerName,
        "address" => $address,
        "email" => $email,
        "phone" => $phone,
        "products" => $result["products"],
        "subtotal" => $result["subtotal"],
        "vat" => $result["vat"],
        "totalWithVat" => $result["totalWithVat"]
    );

    $file = __DIR__ . "/../../orders/orders.json";

    if (!file_exists($file)) {
        if (file_put_contents($file, json_encode(array())) === false) {
            showError();
        }
    }

    $content = file_get_contents($file);

    if ($content === false) {
        showError();
    }

    $orders = json_decode($content, true);

    if (!is_array($orders)) {
        showError();
    }

    $orders[] = $order;

    $saved = file_put_contents(
        $file,
        json_encode($orders, JSON_PRETTY_PRINT),
        LOCK_EX
    );

    if ($saved === false) {
        showError();
    }

    header("Content-Type: text/plain; charset=utf-8");
    http_response_code(200);

    echo number_format($result["totalWithVat"], 2, ".", "") . " EUR";
}

?>
