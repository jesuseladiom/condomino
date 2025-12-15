<?php
$DB_HOST = "localhost";
$DB_USER = "root";
$DB_PASS = "";
$DB_NAME = "condomino";

$conn = new mysqli($DB_HOST, $DB_USER, $DB_PASS, $DB_NAME);

if ($conn->connect_error) {
    http_response_code(500);
    die(json_encode(["error" => "Error de conexión: " . $conn->connect_error]));
}

$conn->set_charset("utf8");
?>
