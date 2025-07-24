<?php
require_once('db_credentials.php');

function db_connect()
{
    try {
        $pdo = new PDO('sqlite:' . DB_PATH);

        // SQL to create a table if it doesn't exist
        $sql = "CREATE TABLE IF NOT EXISTS admins (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        first_name TEXT,
        last_name TEXT,
        username TEXT,
        email TEXT,
        hashed_password TEXT
        );";

        $pdo->exec($sql);

    } catch (PDOException $e) {
        exit($e->getMessage());
    }

    return $pdo;
}
