<?php

require_once "config.php";
require_once "functions.php";

if ($_SERVER["REQUEST_METHOD"] != "POST") {
    header("Location: index.php");
    exit;
}

if (!check_csrf_token($_POST["csrf_token"] ?? "")) {
    die("Invalid request.");
}

$id = filter_var(
    $_POST["id"] ?? "",
    FILTER_VALIDATE_INT
);

if (!$id) {
    die("Invalid product ID.");
}

$stmt = $pdo->prepare(
    "DELETE FROM products WHERE id = :id"
);

$stmt->execute([
    "id" => $id
]);

header("Location: index.php");
exit;

?>