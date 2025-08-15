<?php

namespace App\Models\User;

use App\Tools;
use PDOException;
use Random\RandomException;
use App\Models\Database\MySQL;

class MySQLUserRepository extends AbstractUserRepository
{
    use Tools;

    /**
     * MySQLConnector
     * @param string $host
     * @param string $username
     * @param string $password
     * @param string $dbname
     */
    public function __construct(string $host, string $username, string $password, string $dbname)
    {
        // 初始化数据库连接
        MySQL::init([
            'host' => $host,
            'dbname' => $dbname,
            'user' => $username,
            'pwd' => $password,
            'charset' => 'utf8mb4'
        ]);

        $this->createTableIfNotExists();
    }

    /**
     * 创建用户表
     * @return void
     */
    private function createTableIfNotExists(): void
    {
        $sql = "CREATE TABLE IF NOT EXISTS users (
                                     uid INT PRIMARY KEY UNIQUE NOT NULL AUTO_INCREMENT ,
                                     userId VARCHAR(255) NOT NULL UNIQUE,
                                     username VARCHAR(255) NOT NULL,
                                     isAdmin BOOLEAN NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";

        MySQL::exec($sql);
    }

    /**
     * 检查数据库连接
     * @return bool
     */
    public function checkConnection(): bool
    {
        try {
            MySQL::pdo();
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
        $uid = $this->generateRandomInt();
        $uid = preg_replace('/[^a-zA-Z0-9_]/', '', $uid);
        $result = true;

        while ($result) {
            $uid = $this->generateRandomInt();
            $result = $this->checkUidExists($uid);
        }

        $user = new User($uid, $userId, $username, $isAdmin, []);

        // 插入用户数据
        MySQL::exec(
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
        $result = MySQL::getOne(
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

        $results = MySQL::getAll(
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
        $rowCount = MySQL::exec(
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
