<?php

namespace App\Models\User;

use App\Tools;
use PDO;
use Random\RandomException;
use ReflectionObject;
use ReflectionProperty;

class MySQLUserRepository extends AbstractUserRepository
{
    use Tools;

    private string $url;
    private string $username;
    private string $password;
    private string $dbname;
    /** @noinspection PhpMissingFieldTypeInspection */
    private $conn;

    /**
     * MySQLConnector
     * @param string $url
     * @param string $username
     * @param string $password
     * @param string $dbname
     */
    public function __construct(string $url, string $username, string $password, string $dbname)
    {
        $this->url = $url;
        $this->username = $username;
        $this->password = $password;
        $this->dbname = $dbname;
        $this->connect();
    }

    /**
     * 连接到数据库
     * @return void
     */
    private function connect(): void
    {
        $this->conn = new PDO("mysql:host=$this->url;dbname=$this->dbname", $this->username, $this->password);
        $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
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

        $this->conn->exec($sql);
    }

    /**
     * 检查数据库连接
     * @return bool
     */
    public function checkConnection(): bool
    {
        return $this->conn !== null;
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
        $stmt = $this->conn->prepare("INSERT INTO users (uid,userId, username, isAdmin) 
                                      VALUES (:uid,:userId, :username, :isAdmin)");
        $stmt->bindParam(':uid', $uid);
        $stmt->bindParam(':userId', $userId);
        $stmt->bindParam(':username', $username);
        $stmt->bindParam(':isAdmin', $isAdmin, PDO::PARAM_BOOL);
        $stmt->execute();
        $stmt2 = $this->conn->prepare("SELECT * FROM users WHERE userId = " . $user->userId);
        $stmt2->execute();
        // 测试
        /*        $sql = $this->conn->prepare("CREATE TABLE IF NOT EXISTS `$uid` (
                            uid INT PRIMARY KEY NOT NULL UNIQUE ,
                            FOREIGN KEY (uid) REFERENCES users(uid)
                        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
                $sql->execute();*/

        return $user;
    }

    /**
     * 检查指定的 uid 是否存在于用户表中
     * @param int $uid 要检查的用户唯一标识
     * @return bool 如果 uid 存在返回 true，否则返回 false
     */
    public function checkUidExists(int $uid): bool
    {
        $stmt = $this->conn->prepare("SELECT 1 FROM users WHERE uid = :uid");
        $stmt->bindParam(':uid', $uid, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchColumn() !== false;
    }


    /**
     * 根据用户名查找用户
     * @param string $username
     * @return array
     */
    public function findUserByUsername(string $username): array
    {
        $users = [];
        $pattern = "%$username%"; //模糊匹配模式

        $stmt = $this->conn->prepare("SELECT * FROM users WHERE username LIKE :pattern");
        $stmt->bindParam(':pattern', $pattern, PDO::PARAM_STR);
        $stmt->execute();

        $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
        foreach ($results as $row) {
            $user = new User($row['uid'], $row['userId'], $row['username'], (bool)$row['isAdmin'], []);
            /*            $uid = $row['uid'];
                        $uid = preg_replace('/[^a-zA-Z0-9_]/', '', $uid);
                        $stmt = $this->conn->prepare("SELECT * FROM `$uid` WHERE uid = :uid");
                        $stmt->bindParam(':uid', $uid, PDO::PARAM_INT);
                        $stmt->execute();
                        $customData = $stmt->fetchAll(PDO::FETCH_ASSOC);
                        $user->customData = $customData;*/
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
        $reflection = new ReflectionObject($user);
        $properties = $reflection->getProperties(ReflectionProperty::IS_PUBLIC);

        $customData = [];
        foreach ($properties as $property) {
            $name = $property->getName();
            if (!in_array($name, ['userId', 'username', 'dingtalkId', 'isAdmin'])) {
                $customData[$name] = $property->getValue($user);
            }
        }

        $customDataJson = json_encode($customData);

        $stmt = $this->conn->prepare("UPDATE users 
                                      SET username = :username, 
                                          isAdmin = :isAdmin
                                      WHERE userId = :userId");
        $stmt->bindParam(':userId', $user->userId);
        $stmt->bindParam(':username', $user->username);
        $stmt->bindParam(':isAdmin', $user->isAdmin, PDO::PARAM_BOOL);
        $stmt->bindParam(':customData', $customDataJson);

        return $stmt->execute();
    }
}

