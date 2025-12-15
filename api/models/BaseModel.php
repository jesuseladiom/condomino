<?php

class BaseModel {

    protected static $db;

    public static function db() {

        if (!self::$db) {
            $host = "localhost";
            $user = "root";
            $pass = "";
            $dbname = "condomino";

            try {
                self::$db = new PDO(
                    "mysql:host=$host;dbname=$dbname;charset=utf8",
                    $user,
                    $pass,
                    [
                        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
                    ]
                );
            } catch (PDOException $e) {
                die("Error DB: " . $e->getMessage());
            }
        }

        return self::$db;
    }
}

