<?php

interface DatabaseConnector
{
    /**
     * @var string
     */
    private(set) string $url {
        set;
    }
    /**
     * @var string
     */
    private(set) string $username {
        set;
    }
    /**
     * @var string
     */
    private(set) string $password {
        set;
    }

    function checkConnection(): bool;

    public function newUser(string $username, string $dingtalkId, bool $isAdmin, string $userId): User;

    public function findUserByUsername(string $username): array;

    public function updateUserData(User $user): bool;
}

/**
 *
 */
abstract class Plugin
{
    public function getUpdateUrl(): string
    {
        return 'https://update.example.com/' . $this->getName() . '.json';
    }

    abstract public function getName(): string;

    public function check()
    {
        if ($this->getAuthor() && $this->getVersion() && $this->getDescription() && $this->getName() && $this->getEventList()) {
            return true;
        } else {
            return false;
        }
    }

    abstract public function getAuthor(): string;

    abstract public function getVersion(): string;

    abstract public function getDescription(): string;

    abstract public function getEventList(): array;
}

class User
{
    protected var string $userId;
    protected var string $username;
    protected var string $dingtalkId;
    protected var bool $isAdmin;
    protected var string $userid;

    protected function addCustomData(string $key, string $value): bool
    {
        try {
            $this->$key = $value;
            return true;
        } catch (Exception $e) {
            return false;
        }
    }
}

# 测试代码

class MySQLConnector implements DatabaseConnector
{
    private string $url;
    private string $username;
    private string $password;
    private string $dbname;
    private string $conn;

    public function __construct(string $url, string $username, string $password, string $dbname)
    {
        $this->url = $url;
        $this->username = $username;
        $this->password = $password;
        $this->dbname = $dbname;
        $this->connect();
    }

    private function connect()
    {
        try {
            $this->conn = new PDO("mysql:host=$this->url;dbname=$this->dbname", $this->username, $this->password);
            $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $this->createTableIfNotExists();
        } catch (PDOException $e) {
            throw new Exception("数据库连接失败: " . $e->getMessage());
        }
    }

    private function createTableIfNotExists()
    {
        $sql = "CREATE TABLE IF NOT EXISTS users (
            userId VARCHAR(255) PRIMARY KEY,
            username VARCHAR(255) NOT NULL,
            dingtalkId VARCHAR(255) NOT NULL,
            isAdmin TINYINT(1) NOT NULL,
            customData TEXT
        )";
        $this->conn->exec($sql);
    }

    public function getUrl(): string
    {
        return $this->url;
    }

    public function setUrl(string $url): void
    {
        $this->url = $url;
    }

    public function getUsername(): string
    {
        return $this->username;
    }

    public function setUsername(string $username): void
    {
        $this->username = $username;
    }

    public function getPassword(): string
    {
        return $this->password;
    }

    public function setPassword(string $password): void
    {
        $this->password = $password;
    }

    public function checkConnection()
    {
        return $this->conn !== null;
    }

    public function newUser(string $username, string $dingtalkId, bool $isAdmin, string $userId): User
    {
        $user = new User();
        $user->userId = $userId;
        $user->username = $username;
        $user->dingtalkId = $dingtalkId;
        $user->isAdmin = $isAdmin;

        $customData = json_encode([]);

        $stmt = $this->conn->prepare("INSERT INTO users (userId, username, dingtalkId, isAdmin, customData) 
                                      VALUES (:userId, :username, :dingtalkId, :isAdmin, :customData)");
        $stmt->bindParam(':userId', $userId);
        $stmt->bindParam(':username', $username);
        $stmt->bindParam(':dingtalkId', $dingtalkId);
        $stmt->bindParam(':isAdmin', $isAdmin, PDO::PARAM_BOOL);
        $stmt->bindParam(':customData', $customData);
        $stmt->execute();

        return $user;
    }

    public function findUserByUsername(string $username): array
    {
        $stmt = $this->conn->prepare("SELECT * FROM users WHERE username = :username");
        $stmt->bindParam(':username', $username);
        $stmt->execute();

        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$result) {
            return [];
        }

        $user = new User();
        $user->userId = $result['userId'];
        $user->username = $result['username'];
        $user->dingtalkId = $result['dingtalkId'];
        $user->isAdmin = (bool)$result['isAdmin'];

        $customData = json_decode($result['customData'], true);
        if (is_array($customData)) {
            foreach ($customData as $key => $value) {
                $user->addCustomData($key, $value);
            }
        }

        return $user;
    }

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
                                          dingtalkId = :dingtalkId, 
                                          isAdmin = :isAdmin, 
                                          customData = :customData 
                                      WHERE userId = :userId");
        $stmt->bindParam(':userId', $user->userId);
        $stmt->bindParam(':username', $user->username);
        $stmt->bindParam(':dingtalkId', $user->dingtalkId);
        $stmt->bindParam(':isAdmin', $user->isAdmin, PDO::PARAM_BOOL);
        $stmt->bindParam(':customData', $customDataJson);

        return $stmt->execute();
    }
}