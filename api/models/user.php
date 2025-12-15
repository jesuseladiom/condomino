<?php
// api/models/User.php
require_once __DIR__ . '/BaseModel.php';

class User extends BaseModel {

    protected static $table = 'users';

    public static function findByEmail($email) {
        $db = self::db();

        $stmt = $db->prepare("SELECT * FROM users WHERE email = ? LIMIT 1");
        $stmt->execute([$email]);

        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public static function find($id) {
        $db = self::db();

        $stmt = $db->prepare("SELECT id, name, email, phone, role, created_at FROM users WHERE id = ? LIMIT 1");
        $stmt->execute([$id]);

        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public static function all() {
        $db = self::db();

        $stmt = $db->query("SELECT id, name, email, phone, role, created_at FROM users ORDER BY name ASC");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public static function create($data) {
        $db = self::db();

        $password = password_hash($data['password'], PASSWORD_DEFAULT);

        $stmt = $db->prepare("
            INSERT INTO users (name, email, password, phone, role)
            VALUES (?, ?, ?, ?, ?)
        ");

        $ok = $stmt->execute([
            $data['name'],
            $data['email'],
            $password,
            $data['phone'] ?? '',
            $data['role'] ?? 'residente'
        ]);

        return $ok ? self::find($db->lastInsertId()) : null;
    }

    public static function updateUser($id, $data) {
        $db = self::db();

        $fields = [];
        $params = [];

        if (isset($data['name'])) { $fields[] = "name = ?"; $params[] = $data['name']; }
        if (isset($data['email'])) { $fields[] = "email = ?"; $params[] = $data['email']; }
        if (isset($data['phone'])) { $fields[] = "phone = ?"; $params[] = $data['phone']; }
        if (isset($data['role'])) { $fields[] = "role = ?"; $params[] = $data['role']; }

        if (isset($data['password']) && $data['password'] !== '') {
            $fields[] = "password = ?";
            $params[] = password_hash($data['password'], PASSWORD_DEFAULT);
        }

        if (empty($fields)) return self::find($id);

        $params[] = $id;

        $sql = "UPDATE users SET " . implode(", ", $fields) . " WHERE id = ?";

        $stmt = $db->prepare($sql);
        $stmt->execute($params);

        return self::find($id);
    }

    public static function deleteUser($id) {
        $db = self::db();

        $stmt = $db->prepare("DELETE FROM users WHERE id = ?");
        return $stmt->execute([$id]);
    }
}
