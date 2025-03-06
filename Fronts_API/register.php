<?php
require "config.php";

header("Content-Type: application/json");

$data = json_decode(file_get_contents("php://input"), true);
if (!$data || empty($data['username']) || empty($data['email']) || empty($data['password'])) {
    echo json_encode(["error" => "Données incomplètes"]);
    exit;
}

$password_hash = password_hash($data['password'], PASSWORD_BCRYPT);
$stmt = $pdo->prepare("INSERT INTO users (username, email, password) VALUES (?, ?, ?)");
try {
    $stmt->execute([$data['username'], $data['email'], $password_hash]);
    echo json_encode(["success" => "Utilisateur créé avec succès"]);
} catch (PDOException $e) {
    echo json_encode(["error" => "Erreur lors de l'inscription : " . $e->getMessage()]);
}
?>
