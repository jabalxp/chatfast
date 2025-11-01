<?php
require_once __DIR__ . '/../config/constants.php';

/**
 * Generate JWT token
 * @param array $payload Data to encode in token
 * @return string JWT token
 */
function generateJWT($payload) {
    $header = json_encode(['typ' => 'JWT', 'alg' => 'HS256']);
    $payload['exp'] = time() + JWT_EXPIRATION;
    $payload = json_encode($payload);
    
    $base64UrlHeader = str_replace(['+', '/', '='], ['-', '_', ''], base64_encode($header));
    $base64UrlPayload = str_replace(['+', '/', '='], ['-', '_', ''], base64_encode($payload));
    
    $signature = hash_hmac('sha256', $base64UrlHeader . "." . $base64UrlPayload, JWT_SECRET, true);
    $base64UrlSignature = str_replace(['+', '/', '='], ['-', '_', ''], base64_encode($signature));
    
    return $base64UrlHeader . "." . $base64UrlPayload . "." . $base64UrlSignature;
}

/**
 * Verify and decode JWT token
 * @param string $jwt JWT token
 * @return array|false Decoded payload or false if invalid
 */
function verifyJWT($jwt) {
    $tokenParts = explode('.', $jwt);
    
    if (count($tokenParts) !== 3) {
        return false;
    }
    
    $header = base64_decode($tokenParts[0]);
    $payload = base64_decode($tokenParts[1]);
    $signatureProvided = $tokenParts[2];
    
    $base64UrlHeader = str_replace(['+', '/', '='], ['-', '_', ''], base64_encode($header));
    $base64UrlPayload = str_replace(['+', '/', '='], ['-', '_', ''], base64_encode($payload));
    
    $signature = hash_hmac('sha256', $base64UrlHeader . "." . $base64UrlPayload, JWT_SECRET, true);
    $base64UrlSignature = str_replace(['+', '/', '='], ['-', '_', ''], base64_encode($signature));
    
    if ($base64UrlSignature !== $signatureProvided) {
        return false;
    }
    
    $payloadArray = json_decode($payload, true);
    
    if (!isset($payloadArray['exp']) || $payloadArray['exp'] < time()) {
        return false;
    }
    
    return $payloadArray;
}

/**
 * Get user ID from authorization header
 * @return int|false User ID or false if not authenticated
 */
function getUserIdFromAuth() {
    $headers = getallheaders();
    
    if (!isset($headers['Authorization'])) {
        return false;
    }
    
    $authHeader = $headers['Authorization'];
    $token = str_replace('Bearer ', '', $authHeader);
    
    $payload = verifyJWT($token);
    
    if (!$payload || !isset($payload['user_id'])) {
        return false;
    }
    
    return $payload['user_id'];
}

/**
 * Require authentication - returns user ID or sends 401 error
 * @return int User ID
 */
function requireAuth() {
    $userId = getUserIdFromAuth();
    
    if (!$userId) {
        http_response_code(401);
        echo json_encode(['error' => 'Unauthorized']);
        exit;
    }
    
    return $userId;
}
?>
