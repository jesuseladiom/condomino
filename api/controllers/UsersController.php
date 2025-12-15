<?php
// api/controllers/UsersController.php
require_once __DIR__ . '/../models/User.php';
require_once __DIR__ . '/../helpers/Response.php';
require_once __DIR__ . '/../helpers/Auth.php';

class UsersController {

    // crear usuario
    public function store() {
        Auth::guard(['admin','sindico']);

        $data = json_decode(file_get_contents('php://input'), true);

        if (
            empty($data['name']) ||
            empty($data['email']) ||
            empty($data['password'])
        ) {
            Response::json(['error' => 'name, email y password son obligatorios'], 400);
        }

        if (User::findByEmail($data['email'])) {
            Response::json(['error' => 'Email ya registrado'], 409);
        }

        $user = User::create($data);

        if (isset($user['error'])) {
            Response::json($user, 500);
        }

        unset($user['password']);

        Response::json($user, 201);
    }


    // GET /users
    public function index() {
        Auth::guard(['admin','sindico']); // only these roles by default can list users
        $users = User::all();
        Response::json($users);
    }

    // GET /users/{id}
    public function show($id) {
        Auth::guard(['admin','sindico']);
        $user = User::find($id);
        if (!$user) Response::json(['error' => 'Usuario no encontrado'], 404);
        Response::json($user);
    }

    // PUT /users/{id}
    public function update($id) {
        Auth::guard(['admin','sindico']);
        $data = json_decode(file_get_contents('php://input'), true);

        var_dump($data);
        exit;

        $res = User::updateUser($id, $data);
        if (isset($res['error'])) Response::json($res, 500);
        Response::json($res);
    }

    // DELETE /users/{id}
    public function delete($id) {
        Auth::guard(['admin']);
        $res = User::deleteUser($id);
        if (isset($res['error'])) Response::json($res, 500);
        Response::json(['ok' => true]);
    }
}
