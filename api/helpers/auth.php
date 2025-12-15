<?php
use Firebase\JWT\JWT;
use Firebase\JWT\Key;

require_once __DIR__ . '/Response.php';

class Auth {

    private static $secret = "clave_secreta_123";

    // ==========================================
    // MÉTODO PRINCIPAL QUE USA TU CONTROLADOR
    // ==========================================
    public static function guard(array $allowedRoles = []) {

        $headers = getallheaders();

        if (!isset($headers["Authorization"])) {
            Response::json(["error" => "Token no enviado"], 401);
            exit;
        }

        $token = str_replace("Bearer ", "", $headers["Authorization"]);

        try {
            $decoded = JWT::decode($token, new Key(self::$secret, 'HS256'));
            $decoded = (array)$decoded;
        } catch (\Exception $e) {
            Response::json(["error" => "Token inválido"], 401);
            exit;
        }

        // Si NO tiene roles permitidos → permitir cualquiera
        if (empty($allowedRoles)) {
            return $decoded;
        }

        if (!isset($decoded["role"])) {
            Response::json(["error" => "Token sin rol"], 403);
            exit;
        }

        if (!in_array($decoded["role"], $allowedRoles)) {
            Response::json(["error" => "Acceso denegado"], 403);
            exit;
        }

        // Todo OK
        return $decoded;
    }

    // ==========================================
    // Generar un token (para tu login)
    // ==========================================
    public static function generateToken($user) {

        $payload = [
            "id" => $user["id"],
            "name" => $user["name"],
            "role" => $user["role"],   // admin, sindico, residente, etc.
            "iat" => time(),
            "exp" => time() + (60 * 60 * 24) // 24 horas
        ];

        return JWT::encode($payload, self::$secret, 'HS256');
    }
}


