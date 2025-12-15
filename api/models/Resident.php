<?php
// api/models/Unit.php
require_once __DIR__ . '/BaseModel.php';

class Resident extends BaseModel {
    protected static $table = "residents";

    //ok
    public static function create($data) {
        global $conn;

        $user_id = $conn->real_escape_string($data['user_id']);
        $unit_id  = $conn->real_escape_string($data['unit_id'] ?? '');
        $activo = $conn->real_escape_string($data['activo'] ?? 1);
        $type = $conn->real_escape_string($data['type'] ?? "residente");

        if (self::exists($user_id)) {
            return ['error' => 'El residente ya existe'];
        }

        $sql = "INSERT INTO residents (user_id, unit_id, type, active)
                VALUES ('$user_id', '$unit_id', '$type','$activo')";

        if ($conn->query($sql)) {
            return self::find($conn->insert_id);
        }

        // respaldo por si ocurre carrera (race condition)
        if ($conn->errno === 1062) {
            return ['error' => 'El residente ya existe'];
        }

        return ['error' => $conn->error];
    }

    //ok
    public static function all() {
        global $conn;

        $sql = "SELECT * FROM residents ORDER BY id DESC";
        $res = $conn->query($sql);

        $rows = [];
        if ($res) {
            while ($row = $res->fetch_assoc()) {
                $rows[] = $row;
            }
        }
        return $rows;
    }

    public static function update($id, $data) {
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

    public static function delete($id) {
        global $conn;
        $id = intval($id);
        $sql = "DELETE FROM units WHERE id = $id";

        return $conn->query($sql)
            ? ['ok' => true]
            : ['error' => $conn->error];
    }

    // ok
    public static function find($id) {
        global $conn;

        $id = intval($id);

        $sql = "SELECT * FROM residents WHERE id = $id LIMIT 1";
        $res = $conn->query($sql);

        if ($res && $res->num_rows > 0) {
            return $res->fetch_assoc();
        }

        return null;
    }

    // ok, un residente solo puede vivir en una unidad
    public static function exists($user_id) {
        global $conn;

        $user_id  = $conn->real_escape_string($user_id);

        $sql = "SELECT id FROM residents WHERE user_id = '$user_id'  LIMIT 1";
        $res = $conn->query($sql);

        return $res && $res->num_rows > 0;
    }


}