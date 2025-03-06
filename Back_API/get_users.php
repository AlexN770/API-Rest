<?php
require "config.php";
require "jwt.php";

header("Content-Type: application/json");

// Récupérer le token envoyé par le front
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

// Récupérer les infos de l'utilisateur
$stmt = $pdo->prepare("SELECT id, username, email FROM users WHERE id = ?");
$stmt->execute([$userData["id"]]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

if ($user) {
    echo json_encode(["success" => "Utilisateur trouvé", "user" => $user]);
} else {
    echo json_encode(["error" => "Utilisateur non trouvé"]);
}
?>
