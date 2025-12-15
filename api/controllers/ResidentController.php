<?php

require_once __DIR__ . '/../models/Resident.php';
require_once __DIR__ . '/../helpers/response.php';
require_once __DIR__ . '/../helpers/auth.php';

class ResidentController {

    // GET ok
    public function index() {
        Auth::guard(['admin', 'sindico']);
        $resident = Resident::all();
        Response::json($resident);
    }

    // GET /units/{id}  ok
    public function show($id) {
        Auth::guard(['admin', 'sindico']);
        $r = Resident::find($id);
        if (!$r) Response::json(['error' => 'Residente no encontrado'], 404);
        Response::json($r);
    }

    // POST ok
    public function store() {
        Auth::guard(['admin', 'sindico']);

        $data = json_decode(file_get_contents("php://input"), true);

        if (empty($data['user_id'])) {
            Response::json(['error' => 'El campo user_id es obligatorio'], 400);
        }
        if (empty($data['unit_id'])) {
            Response::json(['error' => 'El campo unit_id es obligatorio'], 400);
        }
        if (empty($data['type'])) {
            Response::json(['error' => 'El campo type es obligatorio'], 400);
        }

        $resident = Resident::create($data);
        Response::json($resident, 201);
    }

    // PUT /residents/{id}
    public function update($id) {
        Auth::guard(['admin', 'sindico']);

        $data = json_decode(file_get_contents("php://input"), true);
        $resident = Resident::update($id, $data);

        Response::json($resident);
    }

    // DELETE /residents/{id}
    public function delete($id) {
        Auth::guard(['admin']);
        $res = Resident::delete($id);
        Response::json($res);
    }
}
