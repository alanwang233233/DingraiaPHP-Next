<?php

namespace App\Database;

use App\User;
use PDO;
use PDOException;

class SQLiteConnector extends DatabaseConnector
{
    private PDO $pdo;

    public function __construct(string $dbPath)
    {
        // SQLite 连接无需用户名、密码等复杂信息，这里按父类构造函数形式传参，实际可按需调整
        parent::__construct('sqlite:' . $dbPath, '', '', '');
        try {
            $this->pdo = new PDO('sqlite:' . $dbPath);
            $this->pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        } catch (PDOException $e) {
            die("数据库连接失败: " . $e->getMessage());
        }
    }

    protected function checkConnection(): bool
    {
        try {
            $this->pdo->query('SELECT 1');
            return true;
        } catch (PDOException) {
            return false;
        }
    }

    protected function newUser(string $username, string $dingtalkId, bool $isAdmin, string $userId): User
    {
        $sql = "INSERT INTO users (userId, username, dingtalkId, isAdmin) VALUES (:userId, :username, :dingtalkId, :isAdmin)";
        $stmt = $this->pdo->prepare($sql);
        $stmt->bindParam(':userId', $userId);
        $stmt->bindParam(':username', $username);
        $stmt->bindParam(':dingtalkId', $dingtalkId);
        $stmt->bindParam(':isAdmin', $isAdmin, PDO::PARAM_BOOL);
        $stmt->execute();

        // 获取自增的 uid 并设置到 User 对象
        $uid = $this->pdo->lastInsertId();
        return new User($uid, $userId, $username, $dingtalkId, $isAdmin, []);
    }

    protected function findUserByUsername(string $username): array
    {
        $sql = "SELECT * FROM users WHERE username = :username";
        $stmt = $this->pdo->prepare($sql);
        $stmt->bindParam(':username', $username);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    protected function updateUserData(User $user): bool
    {
        $sql = "UPDATE users SET userId = :userId, username = :username, dingtalkId = :dingtalkId, isAdmin = :isAdmin WHERE uid = :uid";
        $stmt = $this->pdo->prepare($sql);
        $stmt->bindParam(':userId', $user->userId);
        $stmt->bindParam(':username', $user->username);
        $stmt->bindParam(':dingtalkId', $user->dingtalkId);
        $stmt->bindParam(':isAdmin', $user->isAdmin, PDO::PARAM_BOOL);
        $stmt->bindParam(':uid', $user->uid, PDO::PARAM_INT);
        return $stmt->execute();
    }
}