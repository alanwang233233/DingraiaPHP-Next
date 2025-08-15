<?php
namespace App\Models\Database;
use PDO;
use PDOException;
class MySQL
{
    private static PDO $pdo; // PDO实例
    private static array $config = []; // 数据库配置

    /**
     * 初始化数据库连接
     * @param array $config 数据库配置[host,dbname,user,pwd,charset]
     */
    public static function init(array $config): void
    {
        self::$config = $config;
    }

    /**
     * 获取PDO实例(单例模式)
     * @return PDO
     * @throws PDOException
     */
    private static function getPdo(): PDO
    {
        if (self::$pdo === null) {
            $dsn = "mysql:host=" . self::$config['host'] . ";dbname=" . self::$config['dbname'] . ";charset=" . (self::$config['charset'] ?? 'utf8mb4');
            $options = [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false,
            ];

            try {
                self::$pdo = new PDO(
                    $dsn,
                    self::$config['user'],
                    self::$config['pwd'],
                    $options
                );
            } catch (PDOException $e) {
                throw new PDOException("数据库连接失败: " . $e->getMessage());
            }
        }
        return self::$pdo;
    }

    /**
     * 查询单条记录
     * @param string $sql SQL语句
     * @param array $params 绑定参数
     * @return array|null 结果数组或null
     */
    public static function getOne(string $sql, array $params = []): ?array
    {
        $stmt = self::getPdo()->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetch() ?: null;
    }

    /**
     * 查询多条记录
     * @param string $sql SQL语句
     * @param array $params 绑定参数
     * @return array 结果集数组
     */
    public static function getAll(string $sql, array $params = []): array
    {
        $stmt = self::getPdo()->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    /**
     * 执行增删改操作
     * @param string $sql SQL语句
     * @param array $params 绑定参数
     * @return int 影响行数
     */
    public static function exec(string $sql, array $params = []): int
    {
        $stmt = self::getPdo()->prepare($sql);
        $stmt->execute($params);
        return $stmt->rowCount();
    }

    /**
     * 获取最后插入的ID
     * @return string
     */
    public static function lastInsertId(): string
    {
        return self::getPdo()->lastInsertId();
    }

    /**
     * 开启事务
     */
    public static function beginTransaction(): void
    {
        self::getPdo()->beginTransaction();
    }

    /**
     * 提交事务
     */
    public static function commit(): void
    {
        self::getPdo()->commit();
    }

    /**
     * 回滚事务
     */
    public static function rollback(): void
    {
        self::getPdo()->rollback();
    }

    /**
     * 直接获取PDO实例
     * @return PDO
     */
    public static function pdo(): PDO
    {
        return self::getPdo();
    }
}