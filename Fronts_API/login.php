<?php
require "config.php";
require "jwt.php";

header("Content-Type: application/json");

$data = json_decode(file_get_contents("php://input"), true);
if (!$data || empty($data['email']) || empty($data['password'])) {
    echo json_encode(["error" => "Données incomplètes"]);
    exit;
}

$stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
$stmt->execute([$data['email']]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

if ($user && password_verify($data['password'], $user['password'])) {
    $token = generate_jwt(["id" => $user['id'], "username" => $user['username'], "email" => $user['email']]);
    echo json_encode(["success" => "Connexion réussie", "token" => $token, "user" => $user]);
} else {
    echo json_encode(["error" => "Identifiants incorrects"]);
}
?>
