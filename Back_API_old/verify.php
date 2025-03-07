<?php
require "config.php";
require "jwt.php";

header("Content-Type: application/json");

// Récupération du token
$headers = apache_request_headers();
if (!isset($headers["Authorization"])) {
    echo json_encode(["success" => false, "message" => "Token manquant"]);
    exit;
}

$token = str_replace("Bearer ", "", $headers["Authorization"]);
$userData = verify_jwt($token);

if (!$userData) {
    echo json_encode(["success" => false, "message" => "Token invalide"]);
    exit;
}

// Récupération des infos de l'utilisateur
$stmt = $pdo->prepare("SELECT id, username FROM users WHERE id = ?");
$stmt->execute([$userData["id"]]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$user) {
    echo json_encode(["success" => false, "message" => "Utilisateur non trouvé"]);
    exit;
}

echo json_encode(["success" => true, "user" => $user]);
?>
