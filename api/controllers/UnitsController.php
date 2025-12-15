<?php
// api/controllers/UnitsController.php

require_once __DIR__ . '/../models/Units.php';
require_once __DIR__ . '/../helpers/response.php';
require_once __DIR__ . '/../helpers/auth.php';

class UnitsController {

    // GET /units
    public function index() {
        Auth::guard(['admin', 'sindico']);
        $units = Unit::all();
        Response::json($units);
    }

    // GET /units/{id}
    public function show($id) {
        Auth::guard(['admin', 'sindico']);
        $u = Unit::find($id);
        if (!$u) Response::json(['error' => 'Unidad no encontrada'], 404);
        Response::json($u);
    }

    // POST /units
    public function store() {
        Auth::guard(['admin', 'sindico']);

        $data = json_decode(file_get_contents("php://input"), true);

        if (empty($data['number'])) {
            Response::json(['error' => 'El campo number es obligatorio'], 400);
        }
        if (empty($data['tower'])) {
            Response::json(['error' => 'El campo tower es obligatorio'], 400);
        }

        $unit = Unit::create($data);
        Response::json($unit, 201);
    }

    // PUT /units/{id}
    public function update($id) {
        Auth::guard(['admin', 'sindico']);

        $data = json_decode(file_get_contents("php://input"), true);
        $unit = Unit::updateUnit($id, $data);

        Response::json($unit);
    }

    // DELETE /units/{id}
    public function delete($id) {
        Auth::guard(['admin']);
        $res = Unit::deleteUnit($id);
        Response::json($res);
    }
}
