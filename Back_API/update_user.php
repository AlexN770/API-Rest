<?php
require "config.php";
require "jwt.php";

header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json");
header("Access-Control-Allow-Methods: POST");
header("Access-Control-Allow-Headers: Authorization, Content-Type");

// Vérification du token
$headers = apache_request_headers();
if (!isset($headers['Authorization'])) {
    echo json_encode(["error" => "Token manquant"]);
    exit;
}

$token = str_replace("Bearer ", "", $headers['Authorization']);
$userData = verify_jwt($token);

if (!$userData) {
    echo json_encode(["error" => "Token invalide"]);
    exit;
}

$user_id = $userData['id'];

// Récupération des données JSON
$data = json_decode(file_get_contents("php://input"), true);

if (!$data || (empty($data['username']) && empty($data['email']) && empty($data['password']))) {
    echo json_encode(["error" => "Aucune donnée fournie pour la mise à jour"]);
    exit;
}

// Préparation des valeurs
$username = !empty($data['username']) ? trim($data['username']) : null;
$email = !empty($data['email']) ? trim($data['email']) : null;
$password = !empty($data['password']) ? password_hash(trim($data['password']), PASSWORD_BCRYPT) : null;

// Construction dynamique de la requête UPDATE
$fields = [];
$values = [];

if ($username !== null) {
    $fields[] = "username = ?";
    $values[] = $username;
}
if ($email !== null) {
    $fields[] = "email = ?";
    $values[] = $email;
}
if ($password !== null) {
    $fields[] = "password = ?";
    $values[] = $password;
}

$values[] = $user_id;

$sql = "UPDATE users SET " . implode(", ", $fields) . " WHERE id = ?";

try {
    $stmt = $pdo->prepare($sql);
    $stmt->execute($values);
    echo json_encode(["success" => "Profil mis à jour avec succès"]);
} catch (PDOException $e) {
    echo json_encode(["error" => "Erreur lors de la mise à jour : " . $e->getMessage()]);
}
?>
