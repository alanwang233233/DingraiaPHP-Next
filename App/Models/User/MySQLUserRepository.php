<?php

namespace App\Models\User;

use App\Tools;
use PDOException;
use Random\RandomException;
use App\Models\Database\MySQL;
use Exception;

class MySQLUserRepository extends AbstractUserRepository
{
    use Tools;

    /**
     * @var MySQL 数据库连接实例
     */
    private MySQL $mysql;

    /**
     * 构造函数：初始化数据库连接
     * @param string $host
     * @param string $username
     * @param string $password
     * @param string $dbname
     * @throws Exception
     */
    public function __construct(string $host, string $username, string $password, string $dbname)
    {
        // 初始化数据库连接实例
        $this->mysql = new MySQL([
            'host' => $host,
            'dbname' => $dbname,
            'username' => $username,
            'password' => $password,
            'charset' => 'utf8mb4'
        ]);

        $this->createTableIfNotExists();
    }

    /**
     * 创建用户表（如果不存在）
     * @return void
     */
    private function createTableIfNotExists(): void
    {
        $sql = "CREATE TABLE IF NOT EXISTS users (
                  uid INT PRIMARY KEY UNIQUE NOT NULL AUTO_INCREMENT,
                  userId VARCHAR(255) NOT NULL UNIQUE,
                  username VARCHAR(255) NOT NULL,
                  isAdmin BOOLEAN NOT NULL
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";

        $this->mysql->exec($sql);
    }

    /**
     * 检查数据库连接是否有效
     * @return bool
     */
    public function checkConnection(): bool
    {
        try {
            /** @noinspection PhpExpressionResultUnusedInspection */
            $this->mysql->pdo();
            return true;
        } catch (PDOException) {
            return false;
        }
    }

    /**
     * 创建新用户
     * @param string $username
     * @param bool $isAdmin
     * @param string $userId
     * @return User
     * @throws RandomException
     */
    public function newUser(string $username, bool $isAdmin, string $userId): User
    {
        // 生成唯一的uid
        do {
            $uid = $this->generateRandomInt();
            // 过滤非字母数字下划线字符
            $uid = preg_replace('/[^a-zA-Z0-9_]/', '', $uid);
        } while ($this->checkUidExists((int)$uid));

        // 创建用户对象
        $user = new User((int)$uid, $userId, $username, $isAdmin, []);

        // 插入用户数据到数据库
        $this->mysql->exec(
            "INSERT INTO users (uid, userId, username, isAdmin) 
             VALUES (:uid, :userId, :username, :isAdmin)",
            [
                ':uid' => $uid,
                ':userId' => $userId,
                ':username' => $username,
                ':isAdmin' => $isAdmin ? 1 : 0
            ]
        );

        return $user;
    }

    /**
     * 检查指定的 uid 是否存在于用户表中
     * @param int $uid 要检查的用户唯一标识
     * @return bool 如果 uid 存在返回 true，否则返回 false
     */
    public function checkUidExists(int $uid): bool
    {
        $result = $this->mysql->getOne(
            "SELECT 1 FROM users WHERE uid = :uid",
            [':uid' => $uid]
        );

        return $result !== null;
    }

    /**
     * 根据用户名查找用户
     * @param string $username
     * @return array
     */
    public function findUserByUsername(string $username): array
    {
        $users = [];
        $pattern = "%$username%"; // 模糊匹配模式

        $results = $this->mysql->getAll(
            "SELECT * FROM users WHERE username LIKE :pattern",
            [':pattern' => $pattern]
        );

        foreach ($results as $row) {
            $user = new User(
                $row['uid'],
                $row['userId'],
                $row['username'],
                (bool)$row['isAdmin'],
                []
            );
            $users[] = $user;
        }

        return $users;
    }

    /**
     * 更新用户数据
     * @param User $user
     * @return bool
     */
    public function updateUserData(User $user): bool
    {
        $rowCount = $this->mysql->exec(
            "UPDATE users 
             SET username = :username, 
                 isAdmin = :isAdmin
             WHERE userId = :userId",
            [
                ':userId' => $user->userId,
                ':username' => $user->username,
                ':isAdmin' => $user->isAdmin ? 1 : 0
            ]
        );

        return $rowCount > 0;
    }
}
