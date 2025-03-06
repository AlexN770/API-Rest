<?php
function generate_jwt($payload, $secret = "supersecret", $expire = 3600) {
    $header = base64_encode(json_encode(["alg" => "HS256", "typ" => "JWT"]));
    $payload["exp"] = time() + $expire;
    $payload = base64_encode(json_encode($payload));
    $signature = base64_encode(hash_hmac("sha256", "$header.$payload", $secret, true));
    return "$header.$payload.$signature";
}

function verify_jwt($token, $secret = "supersecret") {
    $parts = explode(".", $token);
    if (count($parts) !== 3) return false;
    [$header, $payload, $signature] = $parts;
    $valid_signature = base64_encode(hash_hmac("sha256", "$header.$payload", $secret, true));
    if ($signature !== $valid_signature) return false;
    $payload = json_decode(base64_decode($payload), true);
    return ($payload["exp"] >= time()) ? $payload : false;
}
?>
