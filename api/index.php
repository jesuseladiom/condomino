<?php
require_once __DIR__ . '/../vendor/autoload.php';

// ✅ CONEXIÓN GLOBAL
require_once __DIR__ . "/config/database.php";

$request = $_SERVER['REQUEST_URI'];
$method  = $_SERVER['REQUEST_METHOD'];

// Limpia la ruta quitando /api, adaptado al VirtualHost
$clean = parse_url($request, PHP_URL_PATH);
$clean = str_replace("/api", "", $clean);

// ---------------- ROUTER -----------------
switch (true) {

    // AUTH
    case $clean === "/auth/register" && $method === "POST":
        require __DIR__ . "/controllers/AuthController.php";
        (new AuthController)->register();
        break;

    case $clean === "/auth/login" && $method === "POST":
       require __DIR__ . "/controllers/AuthController.php";
        (new AuthController)->login();
        break;

    case $clean === "/auth/me" && $method === "GET":
        require __DIR__ . "/controllers/AuthController.php";
        (new AuthController)->me();
        break;

    // USERS
    case $clean === "/users" && $method === "GET":
        require __DIR__ . "/controllers/UsersController.php";
        (new UsersController)->index();
        break;

    case $clean === "/users" && $method === "POST":
        require __DIR__ . "/controllers/UsersController.php";
        (new UsersController)->store();
        break;

    case preg_match('#^/users/(\d+)$#', $clean, $m) && $method === "GET":
        require __DIR__ . "/controllers/UsersController.php";
        (new UsersController)->show($m[1]);
        break;

    case preg_match('#^/users/(\d+)$#', $clean, $m) && $method === "PUT":
        require __DIR__ . "/controllers/UsersController.php";
        (new UsersController)->update($m[1]);
        break;

    case preg_match('#^/users/(\d+)$#', $clean, $m) && $method === "DELETE":
        require __DIR__ . "/controllers/UsersController.php";
        (new UsersController)->delete($m[1]);
        break;

    // UNITS (DEPARTAMENTOS)
    case $clean === "/units" && $method === "GET":
        require __DIR__ . "/controllers/UnitsController.php";
        (new UnitsController)->index();
        break;

    case $clean === "/units" && $method === "POST":
        require __DIR__ . "/controllers/UnitsController.php";
        (new UnitsController)->store();
        break;

    case preg_match('#^/units/(\d+)$#', $clean, $m) && $method === "GET":
        require __DIR__ . "/controllers/UnitsController.php";
        (new UnitsController)->show($m[1]);
        break;

    case preg_match('#^/units/(\d+)$#', $clean, $m) && $method === "PUT":
        require __DIR__ . "/controllers/UnitsController.php";
        (new UnitsController)->update($m[1]);
        break;

    case preg_match('#^/units/(\d+)$#', $clean, $m) && $method === "DELETE":
        require __DIR__ . "/controllers/UnitsController.php";
        (new UnitsController)->delete($m[1]);
        break;

    // RESIDENTES
    case $clean === "/residents" && $method === "GET":
        require __DIR__ . "/controllers/ResidentController.php";
        (new ResidentController)->index();
        break;

    case $clean === "/residents" && $method === "POST":
        require __DIR__ . "/controllers/ResidentController.php";
        (new ResidentController)->store();
        break;

    case preg_match('#^/residents/(\d+)$#', $clean, $m) && $method === "GET":
        require __DIR__ . "/controllers/ResidentController.php";
        (new ResidentController)->show($m[1]);
        break;

    case preg_match('#^/residents/(\d+)$#', $clean, $m) && $method === "PUT":
        require __DIR__ . "/controllers/ResidentController.php";
        (new ResidentController)->update($m[1]);
        break;

    case preg_match('#^/residents/(\d+)$#', $clean, $m) && $method === "DELETE":
        require __DIR__ . "/controllers/ResidentController.php";
        (new ResidentController)->delete($m[1]);
        break;

    // DEFAULT
    default:
        http_response_code(404);
        echo json_encode([
            "error" => "Endpoint no encontrado",
            "path" => $clean
        ]);
}