<?php
// api/controllers/AuthController.php
require_once __DIR__ . '/../models/User.php';
require_once __DIR__ . '/../helpers/Response.php';
require_once __DIR__ . '/../helpers/Auth.php';

class AuthController {

    // POST /auth/register
    // body: { "name":"", "email":"", "password":"", "phone":"", "role":"" }
    public function register() {
        $data = json_decode(file_get_contents('php://input'), true);
        if (empty($data['name']) || empty($data['email']) || empty($data['password'])) {
            Response::json(['error' => 'Campos obligatorios: name, email, password'], 400);
        }

        if (User::findByEmail($data['email'])) {
            Response::json(['error' => 'Email ya registrado'], 409);
        }

        $user = User::create($data);
        if (isset($user['error'])) Response::json($user, 500);

        // remove sensitive fields if any
        unset($user['password']);

        Response::json($user, 201);
    }

    // POST /auth/login
    // body: { "email":"", "password":"" }
    public function login() {
        $data = json_decode(file_get_contents('php://input'), true);
        if (empty($data['email']) || empty($data['password'])) {
            Response::json(['error' => 'Email y password requeridos'], 400);
        }

        $user = User::findByEmail($data['email']);
        if (!$user) Response::json(['error' => 'Credenciales inválidas'], 401);

        if (!password_verify($data['password'], $user['password'])) {
            Response::json(['error' => 'Credenciales inválidas'], 401);
        }

        $token = Auth::generateToken($user);

        // return token + public user info
        $public = [
            'id' => $user['id'],
            'name' => $user['name'],
            'email' => $user['email'],
            'role' => $user['role']
        ];

        Response::json(['token' => $token, 'user' => $public]);
    }

    // optional: endpoint to get current user from token
    // GET /auth/me
    public function me() {
        $decoded = Auth::verify(); // will Response::json(401) if invalid
        // decoded contains id/email/role
        $user = User::find($decoded->id);
        if (!$user) Response::json(['error' => 'Usuario no encontrado'], 404);
        Response::json($user);
    }
}
