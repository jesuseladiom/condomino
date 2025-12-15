<?php
// api/models/Unit.php
require_once __DIR__ . '/BaseModel.php';

class Unit extends BaseModel {
    protected static $table = "units";

    public static function create($data) {
        global $conn;

        $number = $conn->real_escape_string($data['number']);
        $tower  = $conn->real_escape_string($data['tower'] ?? '');
        $status = $conn->real_escape_string($data['status'] ?? 'ACTIVO');
        $floor = $conn->real_escape_string($data['floor'] ?? 1);
        $description = $conn->real_escape_string($data['description'] ?? '');

        if (self::exists($tower, $number)) {
            return ['error' => 'El departamento ya existe'];
        }

        $sql = "INSERT INTO units (number, tower, status, floor, description)
                VALUES ('$number', '$tower', '$status','$floor','$description')";

        if ($conn->query($sql)) {
            return self::find($conn->insert_id);
        }

        // respaldo por si ocurre carrera (race condition)
        if ($conn->errno === 1062) {
            return ['error' => 'El departamento ya existe'];
        }

        return ['error' => $conn->error];
    }

    public static function all() {
        global $conn;

        $sql = "SELECT * FROM units ORDER BY id DESC";
        $res = $conn->query($sql);

        $rows = [];
        if ($res) {
            while ($row = $res->fetch_assoc()) {
                $rows[] = $row;
            }
        }
        return $rows;
    }

    public static function updateUnit($id, $data) {
        global $conn;

        $id = intval($id);
        $updates = [];

        foreach ($data as $key => $value) {
            $v = $conn->real_escape_string($value);
            $updates[] = "$key = '$v'";
        }

        if (empty($updates)) return self::find($id);

        $sql = "UPDATE units SET " . implode(", ", $updates) . " WHERE id = $id";

        if ($conn->query($sql)) {
            return self::find($id);
        }

        return ['error' => $conn->error];
    }

    public static function deleteUnit($id) {
        global $conn;
        $id = intval($id);
        $sql = "DELETE FROM units WHERE id = $id";

        return $conn->query($sql)
            ? ['ok' => true]
            : ['error' => $conn->error];
    }

    public static function find($id) {
        global $conn;

        $id = intval($id);

        $sql = "SELECT * FROM units WHERE id = $id LIMIT 1";
        $res = $conn->query($sql);

        if ($res && $res->num_rows > 0) {
            return $res->fetch_assoc();
        }

        return null;
    }

    // api/models/Unit.php
    public static function exists($tower, $number) {
        global $conn;

        $tower  = $conn->real_escape_string($tower);
        $number = $conn->real_escape_string($number);

        $sql = "SELECT id FROM units WHERE tower = '$tower' AND number = '$number' LIMIT 1";
        $res = $conn->query($sql);

        return $res && $res->num_rows > 0;
    }


}
