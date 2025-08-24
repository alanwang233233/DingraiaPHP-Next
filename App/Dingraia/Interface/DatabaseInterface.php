<?php

namespace App\Dingraia\Interface;

use PDO;
use Exception;

interface DatabaseInterface
{
    /**
     * 初始化数据库连接
     * @param array $config 数据库配置[host,dbname,user,pwd,charset]
     * @throws Exception
     */
    public function __construct(array $config);

    /**
     * 查询单条记录
     * @param string $sql SQL语句
     * @param array $params 绑定参数
     * @return array|null 结果数组或null
     */
    public function getOne(string $sql, array $params = []): ?array;

    /**
     * 查询多条记录
     * @param string $sql SQL语句
     * @param array $params 绑定参数
     * @return array 结果集数组
     */
    public function getAll(string $sql, array $params = []): array;

    /**
     * 执行增删改操作
     * @param string $sql SQL语句
     * @param array $params 绑定参数
     * @return int 影响行数
     */
    public function exec(string $sql, array $params = []): int;

    /**
     * 获取最后插入的ID
     * @return string
     */
    public function lastInsertId(): string;

    /**
     * 开启事务
     */
    public function beginTransaction(): void;

    /**
     * 提交事务
     */
    public function commit(): void;

    /**
     * 回滚事务
     */
    public function rollback(): void;

    /**
     * 直接获取PDO实例
     * @return PDO
     */
    public function pdo(): PDO;
}