<?php

namespace App\Models\Database;

use Exception;
use PDO;
use PDOException;

class MySQL
{
    private PDO $pdo;
    private array $config;

    /**
     * 初始化数据库连接
     * @param array $config 数据库配置[host,dbname,user,pwd,charset]
     * @throws Exception
     */
    public function __construct(array $config)
    {
        $this->config = $config;
        if (!isset($config['host']) or !isset($config['dbname']) or !isset($config['username']) or !isset($config['password'])) {
            throw new Exception('Missing database configuration');
        }
        try {
            $this->init();
        } catch (Exception $e) {
            throw new Exception('MySQL Error: '.$e->getMessage());
        }
    }

    /**
     * 获取PDO实例(单例模式)
     * @return void
     */
    private function init(): void
    {
        $dsn = "mysql:host=" . $this->config['host'] . ";dbname=" . $this->config['dbname'] . ";charset=" . ($this->config['charset'] ?? 'utf8mb4');
        $options = [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
        ];

        try {
            $this->pdo = new PDO(
                $dsn,
                $this->config['user'],
                $this->config['pwd'],
                $options
            );
        } catch (PDOException $e) {
            throw new PDOException("数据库连接失败: " . $e->getMessage());
        }
    }



    /**
     * 查询单条记录
     * @param string $sql SQL语句
     * @param array $params 绑定参数
     * @return array|null 结果数组或null
     */
    public function getOne(string $sql, array $params = []): ?array
    {
        $stmt = $this->pdo()->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetch() ?: null;
    }

    /**
     * 查询多条记录
     * @param string $sql SQL语句
     * @param array $params 绑定参数
     * @return array 结果集数组
     */
    public function getAll(string $sql, array $params = []): array
    {
        $stmt = $this->pdo()->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    /**
     * 执行增删改操作
     * @param string $sql SQL语句
     * @param array $params 绑定参数
     * @return int 影响行数
     */
    public function exec(string $sql, array $params = []): int
    {
        $stmt = $this->pdo()->prepare($sql);
        $stmt->execute($params);
        return $stmt->rowCount();
    }

    /**
     * 获取最后插入的ID
     * @return string
     */
    public function lastInsertId(): string
    {
        return $this->pdo()->lastInsertId();
    }

    /**
     * 开启事务
     */
    public function beginTransaction(): void
    {
        $this->pdo()->beginTransaction();
    }

    /**
     * 提交事务
     */
    public function commit(): void
    {
        $this->pdo()->commit();
    }

    /**
     * 回滚事务
     */
    public function rollback(): void
    {
        $this->pdo()->rollback();
    }

    /**
     * 直接获取PDO实例
     * @return PDO
     */
    public function pdo(): PDO
    {
        return $this->pdo;
    }
}
