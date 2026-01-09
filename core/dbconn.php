<?php

require_once __DIR__ . "/../config/database.php";

class DbConn
{
    private static $pdo = null;

    public static function getConnection()
    {
        if (self::$pdo === null) {
            global $DB_CONFIG;
            $mysqlcfg = $DB_CONFIG['mysql'];
            $dsn = "mysql:host={$mysqlcfg['host']};dbname={$mysqlcfg['dbname']};charset={$mysqlcfg['charset']}";

            try {
                self::$pdo = new PDO($dsn, $mysqlcfg['user'], $mysqlcfg['pass'], [
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                ]);
            } catch (PDOException $e) {
                die("Database connection failed: " . $e->getMessage());
            }
        }
        return self::$pdo;
    }
}

class MongoConn
{
    private static $client = null;
    private static $database = null;

    /**
     * 获取 MongoDB 客户端实例 (单例)
     */
    public static function getClient()
    {

        if (self::$client === null) {

            global $DB_CONFIG;
            $mongoConfig = $DB_CONFIG['mongodb'];

            // 构造 DSN 字符串
            // 格式: mongodb://[user:pass@]host:port
            $auth = "";
            if (!empty($mongoConfig['user']) && !empty($mongoConfig['pass'])) {
                $auth = "{$mongoConfig['user']}:{$mongoConfig['pass']}@";
            }

            $dsn = "mongodb://{$auth}{$mongoConfig['host']}:{$mongoConfig['port']}";

            try {
                // 实例化驱动客户端
                self::$client = new MongoDB\Client($dsn);
            } catch (Exception $e) {
                die("MongoDB Connection failed: " . $e->getMessage());
            }
        }
        return self::$client;
    }

    /**
     * 直接获取数据库对象
     * 这样你可以直接调用 $db->selectCollection('xxx')
     */
    public static function getDB()
    {
        if (self::$database === null) {
            global $DB_CONFIG;
            $config = $DB_CONFIG['mongodb'];
            $client = self::getClient();
            $dbName = $config['dbname'];
            self::$database = $client->selectDatabase($dbName);
        }
        return self::$database;
    }
}
