<?php
class Database {
    private static $instance = null;

    public static function connect() {
        if (self::$instance === null) {
            $host    = 'localhost';
            $db      = 'reverse_market';
            $user    = 'root';
            $pass    = '';
            $charset = 'utf8mb4';
            $dsn = "mysql:host=$host;dbname=$db;charset=$charset";
            self::$instance = new PDO($dsn, $user, $pass, array(
                PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES   => false,
            ));
        }
        return self::$instance;
    }
}
