<?php
require "config.php";
require "jwt.php";

header("Content-Type: application/json");

try {
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

    $method = $_SERVER["REQUEST_METHOD"];

    if ($method === "GET") {
        $stmt = $pdo->prepare("SELECT * FROM orders WHERE user_id = ?");
        $stmt->execute([$userData["id"]]);
        $orders = $stmt->fetchAll(PDO::FETCH_ASSOC);
        echo json_encode(["success" => true, "orders" => $orders]);

    } elseif ($method === "POST") {
        $data = json_decode(file_get_contents("php://input"), true);
        if (!isset($data["product"], $data["quantity"]) || empty($data["product"]) || empty($data["quantity"])) {
            echo json_encode(["error" => "Données incomplètes"]);
            exit;
        }

        $product = trim($data["product"]);
        $quantity = intval($data["quantity"]);

        if ($quantity <= 0) {
            echo json_encode(["error" => "Quantité invalide"]);
            exit;
        }

        $stmt = $pdo->prepare("INSERT INTO orders (user_id, product, quantity) VALUES (?, ?, ?)");
        $stmt->execute([$userData["id"], $product, $quantity]);

        if ($stmt->rowCount() > 0) {
            echo json_encode(["success" => "Commande ajoutée"]);
        } else {
            echo json_encode(["error" => "Échec de l'ajout"]);
        }

    } elseif ($method === "PUT") {
        $data = json_decode(file_get_contents("php://input"), true);
        if (!isset($data["order_id"]) || (!isset($data["product"]) && !isset($data["quantity"]))) {
            echo json_encode(["error" => "Données incomplètes"]);
            exit;
        }

        $orderId = intval($data["order_id"]);
        $updates = [];
        $params = [];

        if (isset($data["product"]) && !empty($data["product"])) {
            $updates[] = "product = ?";
            $params[] = trim($data["product"]);
        }
        if (isset($data["quantity"]) && intval($data["quantity"]) > 0) {
            $updates[] = "quantity = ?";
            $params[] = intval($data["quantity"]);
        }

        if (!empty($updates)) {
            $params[] = $orderId;
            $params[] = $userData["id"];
            $sql = "UPDATE orders SET " . implode(", ", $updates) . " WHERE id = ? AND user_id = ?";
            $stmt = $pdo->prepare($sql);
            $stmt->execute($params);

            if ($stmt->rowCount() > 0) {
                echo json_encode(["success" => "Commande mise à jour"]);
            } else {
                echo json_encode(["error" => "Commande introuvable ou aucune modification"]);
            }
        } else {
            echo json_encode(["error" => "Aucune modification spécifiée"]);
        }

    } elseif ($method === "DELETE") {
        $data = json_decode(file_get_contents("php://input"), true);
        if (!isset($data["order_id"])) {
            echo json_encode(["error" => "ID commande manquant"]);
            exit;
        }

        $stmt = $pdo->prepare("DELETE FROM orders WHERE id = ? AND user_id = ?");
        $stmt->execute([$data["order_id"], $userData["id"]]);

        if ($stmt->rowCount() > 0) {
            echo json_encode(["success" => "Commande supprimée"]);
        } else {
            echo json_encode(["error" => "Commande introuvable"]);
        }

    } else {
        echo json_encode(["error" => "Méthode non autorisée"]);
    }

} catch (Exception $e) {
    echo json_encode(["error" => "Erreur serveur : " . $e->getMessage()]);
}
?>
