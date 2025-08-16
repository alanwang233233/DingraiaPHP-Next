<?php

namespace App\Models\User;

use App\Models\Database\SQLite;
use App\Tools;
use Exception;
use Random\RandomException;

class SQLiteUserRepository extends AbstractUserRepository
{
    use Tools;

    private SQLite $db;

    /**
     * 构造函数，初始化数据库连接并创建表
     * @param string $dbname 数据库文件名
     * @throws Exception
     */
    public function __construct(string $dbname)
    {
        $this->db = new SQLite($dbname);
        $this->createTableIfNotExists();
    }


    /**
     * 创建用户表（如果不存在）
     * @return void
     * @throws Exception
     */
    private function createTableIfNotExists(): void
    {
        $sql = "CREATE TABLE IF NOT EXISTS users (
            uid INTEGER PRIMARY KEY,
            userId TEXT NOT NULL UNIQUE,
            username TEXT NOT NULL,
            isAdmin INTEGER NOT NULL
        )";

        $this->db->exec($sql);
    }

    /**
     * 检查数据库连接是否有效
     * @return bool
     */
    public function checkConnection(): bool
    {
        try {
            // 通过执行简单查询检查连接
            $this->db->getOne("SELECT 1");
            return true;
        } catch (Exception) {
            return false;
        }
    }

    /**
     * 创建新用户
     * @param string $username 用户名
     * @param bool $isAdmin 是否为管理员
     * @param string $userId 用户ID
     * @return User 新创建的用户对象
     * @throws RandomException|Exception
     */
    public function newUser(string $username, bool $isAdmin, string $userId): User
    {
        // 检查userId是否已存在
        if ($this->checkUserIdExists($userId)) {
            throw new Exception("User ID '$userId' already exists");
        }

        // 生成唯一的uid
        $uid = $this->generateUniqueUid();

        try {
            $this->db->beginTransaction();

            // 插入用户记录
            $sql = "INSERT INTO users (uid, userId, username, isAdmin) 
                    VALUES (:uid, :userId, :username, :isAdmin)";

            $params = [
                ':uid' => $uid,
                ':userId' => $userId,
                ':username' => $username,
                ':isAdmin' => $isAdmin ? 1 : 0
            ];

            $this->db->exec($sql, $params);

            // 验证插入结果
            $userData = $this->db->getOne(
                "SELECT * FROM users WHERE userId = :userId",
                [':userId' => $userId]
            );

            if (!$userData) {
                throw new Exception("Failed to create user");
            }

            $this->db->commit();

            return new User(
                $userData['uid'],
                $userData['userId'],
                $userData['username'],
                (bool)$userData['isAdmin'],
                []
            );
        } catch (Exception $e) {
            $this->db->rollback();
            throw $e;
        }
    }

    /**
     * 生成唯一的UID
     * @return int 唯一的UID
     * @throws RandomException
     * @throws Exception
     */
    private function generateUniqueUid(): int
    {
        do {
            $uid = $this->generateRandomInt();
            // 清理UID，确保只包含字母数字和下划线
            $uid = (int)preg_replace('/[^a-zA-Z0-9_]/', '', $uid);
        } while ($this->checkUidExists($uid));

        return $uid;
    }

    /**
     * 检查指定的uid是否存在
     * @param int $uid 要检查的UID
     * @return bool 如果存在返回true，否则返回false
     * @throws Exception
     */
    public function checkUidExists(int $uid): bool
    {
        $result = $this->db->getOne(
            "SELECT 1 FROM users WHERE uid = :uid",
            [':uid' => $uid]
        );

        return $result !== null;
    }

    /**
     * 检查指定的userId是否存在
     * @param string $userId 要检查的用户ID
     * @return bool 如果存在返回true，否则返回false
     * @throws Exception
     */
    public function checkUserIdExists(string $userId): bool
    {
        $result = $this->db->getOne(
            "SELECT 1 FROM users WHERE userId = :userId",
            [':userId' => $userId]
        );

        return $result !== null;
    }

    /**
     * 根据用户名查找用户
     * @param string $username 用户名（支持模糊搜索）
     * @return array 用户对象数组
     * @throws Exception
     */
    public function findUserByUsername(string $username): array
    {
        $users = [];
        $pattern = "%$username%"; // 模糊匹配模式

        $results = $this->db->getAll(
            "SELECT * FROM users WHERE username LIKE :pattern",
            [':pattern' => $pattern]
        );

        foreach ($results as $row) {
            $users[] = new User(
                $row['uid'],
                $row['userId'],
                $row['username'],
                (bool)$row['isAdmin'],
                []
            );
        }

        return $users;
    }

    /**
     * 更新用户数据
     * @param User $user 用户对象
     * @return bool 更新是否成功
     * @throws Exception
     */
    public function updateUserData(User $user): bool
    {
        $sql = "UPDATE users 
                SET username = :username, 
                    isAdmin = :isAdmin
                WHERE userId = :userId";

        $params = [
            ':userId' => $user->userId,
            ':username' => $user->username,
            ':isAdmin' => $user->isAdmin ? 1 : 0
        ];

        $rowCount = $this->db->exec($sql, $params);
        return $rowCount > 0;
    }
}
