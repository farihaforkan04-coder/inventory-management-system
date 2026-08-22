<?php

require_once "config.php";
require_once "functions.php";

$id = filter_var(
    $_GET["id"] ?? "",
    FILTER_VALIDATE_INT
);

if (!$id) {
    die("Invalid product ID.");
}

$stmt = $pdo->prepare(
    "SELECT * FROM products WHERE id = :id"
);

$stmt->execute([
    "id" => $id
]);

$product = $stmt->fetch();

if (!$product) {
    die("Product not found.");
}

$errors = [];

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    if (!check_csrf_token($_POST["csrf_token"] ?? "")) {
        $errors[] = "Invalid request.";
    }

    $name = trim($_POST["name"] ?? "");
    $category = trim($_POST["category"] ?? "");
    $quantity = trim($_POST["quantity"] ?? "");
    $price = trim($_POST["price"] ?? "");
    $description = trim($_POST["description"] ?? "");

    if ($name == "") {
        $errors[] = "Product name is required.";
    }

    if ($category == "") {
        $errors[] = "Category is required.";
    }

    if (!filter_var(
        $quantity,
        FILTER_VALIDATE_INT,
        ["options" => ["min_range" => 0]]
    )) {
        $errors[] = "Quantity must be 0 or greater.";
    }

    if (!is_numeric($price) || $price < 0) {
        $errors[] = "Price must be 0 or greater.";
    }

    if (!$errors) {

        $sql = "UPDATE products SET
                name = :name,
                category = :category,
                quantity = :quantity,
                price = :price,
                description = :description
                WHERE id = :id";

        $stmt = $pdo->prepare($sql);

        $stmt->execute([
            "name" => $name,
            "category" => $category,
            "quantity" => $quantity,
            "price" => $price,
            "description" => $description,
            "id" => $id
        ]);

        header("Location: index.php");
        exit;
    }

} else {

    $name = $product["name"];
    $category = $product["category"];
    $quantity = $product["quantity"];
    $price = $product["price"];
    $description = $product["description"];
}

?>
<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">

    <title>Edit Product</title>

    <link rel="stylesheet" href="assets/style.css">

</head>

<body>

<header class="header">

    <div>
        <h1>Edit Product</h1>
        <p>Update product information.</p>
    </div>

    <a href="index.php" class="button">
        Back
    </a>

</header>

<main class="container">

<section class="inventory">

    <h2>Update Product</h2>

    <?php if ($errors): ?>

        <?php foreach ($errors as $error): ?>

            <p><?= e($error) ?></p>

        <?php endforeach; ?>

    <?php endif; ?>

    <form method="POST">

        <input
            type="hidden"
            name="csrf_token"
            value="<?= e(csrf_token()) ?>"
        >

        <p>
            <label>Product Name</label><br>

            <input
                type="text"
                name="name"
                required
                value="<?= e($name) ?>"
            >
        </p>

        <p>
            <label>Category</label><br>

            <input
                type="text"
                name="category"
                required
                value="<?= e($category) ?>"
            >
        </p>

        <p>
            <label>Quantity</label><br>

            <input
                type="number"
                name="quantity"
                min="0"
                required
                value="<?= e($quantity) ?>"
            >
        </p>

        <p>
            <label>Price</label><br>

            <input
                type="number"
                name="price"
                min="0"
                step="0.01"
                required
                value="<?= e($price) ?>"
            >
        </p>

        <p>
            <label>Description</label><br>

            <textarea
                name="description"
                rows="5"
            ><?= e($description) ?></textarea>
        </p>

        <button type="submit">
            Update Product
        </button>

    </form>

</section>

</main>

</body>
</html>