<?php

require_once "config.php";
require_once "functions.php";

$search = trim($_GET["search"] ?? "");

if ($search != "") {

    $sql = "SELECT * FROM products
            WHERE name LIKE :search
            OR category LIKE :search
            ORDER BY id DESC";

    $stmt = $pdo->prepare($sql);

    $stmt->execute([
        "search" => "%$search%"
    ]);

} else {

    $stmt = $pdo->query(
        "SELECT * FROM products ORDER BY id DESC"
    );
}

$products = $stmt->fetchAll();

$totalProducts = $pdo
    ->query("SELECT COUNT(*) FROM products")
    ->fetchColumn();

$totalUnits = $pdo
    ->query("SELECT COALESCE(SUM(quantity), 0) FROM products")
    ->fetchColumn();

$lowStock = $pdo
    ->query("SELECT COUNT(*) FROM products WHERE quantity <= 5")
    ->fetchColumn();

?>
<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta name="viewport"
          content="width=device-width, initial-scale=1.0">

    <title>Inventory Management System</title>

    <link rel="stylesheet" href="assets/style.css">

</head>

<body>

<header class="header">

    <div>
        <h1>Inventory Management System</h1>

        <p>
            Manage products and stock easily.
        </p>
    </div>

    <a href="add.php" class="button">
        + Add Product
    </a>

</header>

<main class="container">

    <div class="stats">

        <div class="card">

            <h3>Total Products</h3>

            <p>
                <?= $totalProducts ?>
            </p>

        </div>

        <div class="card">

            <h3>Total Units</h3>

            <p>
                <?= $totalUnits ?>
            </p>

        </div>

        <div class="card">

            <h3>Low Stock</h3>

            <p>
                <?= $lowStock ?>
            </p>

        </div>

    </div>

    <section class="inventory">

        <div class="inventory-header">

            <h2>Product Inventory</h2>

            <form method="GET">

                <input
                    type="text"
                    name="search"
                    placeholder="Search product..."
                    value="<?= e($search) ?>"
                >

                <button type="submit">
                    Search
                </button>

            </form>

        </div>

        <div class="table-container">

            <table>

                <thead>

                    <tr>

                        <th>ID</th>
                        <th>Name</th>
                        <th>Category</th>
                        <th>Quantity</th>
                        <th>Stock Status</th>
                        <th>Price</th>
                        <th>Description</th>
                        <th>Actions</th>

                    </tr>

                </thead>

                <tbody>

                <?php foreach ($products as $product): ?>

                    <tr>

                        <td>
                            <?= $product["id"] ?>
                        </td>

                        <td>
                            <?= e($product["name"]) ?>
                        </td>

                        <td>
                            <?= e($product["category"]) ?>
                        </td>

                        <td>

                            <?php if ($product["quantity"] <= 5): ?>

                                <span class="low-stock">
                                    <?= $product["quantity"] ?>
                                </span>

                            <?php else: ?>

                                <?= $product["quantity"] ?>

                            <?php endif; ?>

                        </td>
                        <td>

    <?php if ($product["quantity"] <= 5): ?>

        <span class="low-stock">
            Low Stock
        </span>

    <?php else: ?>

        In Stock

    <?php endif; ?>

</td>

                        <td>
                            $<?= number_format($product["price"], 2) ?>
                        </td>

                        <td>
                            <?= e($product["description"]) ?>
                        </td>

                        <td>

                            <a
                                class="edit"
                                href="edit.php?id=<?= $product["id"] ?>"
                            >
                                Edit
                            </a>

                            <form
                                action="delete.php"
                                method="POST"
                                class="delete-form"
                                onsubmit="return confirm('Are you sure you want to delete this product?');"
                            >

                                <input
                                    type="hidden"
                                    name="csrf_token"
                                    value="<?= e(csrf_token()) ?>"
                                >

                                <input
                                    type="hidden"
                                    name="id"
                                    value="<?= $product["id"] ?>"
                                >

                                <button
                                    type="submit"
                                    class="delete"
                                >
                                    Delete
                                </button>

                            </form>

                        </td>

                    </tr>

                <?php endforeach; ?>

                </tbody>

            </table>

        </div>

    </section>

</main>

</body>
</html>