<?php
require "config.php";
require "jwt.php";

header("Content-Type: application/json");

// Vérification du token
$headers = apache_request_headers();
if (!isset($headers["Authorization"])) {
    echo json_encode(["error" => "Token manquant"]);
    exit;
}

$token = str_replace("Bearer ", "", $headers["Authorization"]);
$userData = verify_jwt($token);

if (!$userData) {
    echo json_encode(["error" => "Token invalide"]);
    exit;
}

// Gestion des requêtes API
$method = $_SERVER["REQUEST_METHOD"];

if ($method === "GET") {
    // Récupérer les commandes
    $stmt = $pdo->prepare("SELECT * FROM orders WHERE user_id = ?");
    $stmt->execute([$userData["id"]]);
    $orders = $stmt->fetchAll(PDO::FETCH_ASSOC);
    echo json_encode(["success" => true, "orders" => $orders]);
} elseif ($method === "POST") {
    // Ajouter une commande
    $data = json_decode(file_get_contents("php://input"), true);
    if (!isset($data["product"], $data["quantity"])) {
        echo json_encode(["error" => "Données incomplètes"]);
        exit;
    }

    $stmt = $pdo->prepare("INSERT INTO orders (user_id, product, quantity) VALUES (?, ?, ?)");
    $stmt->execute([$userData["id"], $data["product"], $data["quantity"]]);

    echo json_encode(["success" => "Commande ajoutée"]);
} elseif ($method === "DELETE") {
    // Supprimer une commande
    $data = json_decode(file_get_contents("php://input"), true);
    if (!isset($data["order_id"])) {
        echo json_encode(["error" => "ID commande manquant"]);
        exit;
    }

    $stmt = $pdo->prepare("DELETE FROM orders WHERE id = ? AND user_id = ?");
    $stmt->execute([$data["order_id"], $userData["id"]]);

    echo json_encode(["success" => "Commande supprimée"]);
} else {
    echo json_encode(["error" => "Méthode non autorisée"]);
}
?>
