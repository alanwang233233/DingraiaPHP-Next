<?php /** @noinspection PhpUnused */

/** @noinspection PhpUnusedPrivateFieldInspection */

require_once BASE_DIR . 'vendor/autoload.php';

abstract class DatabaseConnector
{
    /**
     * @var string
     */
    private string $url;
    /**
     * @var string
     */
    private string $username;
    /**
     * @var string
     */
    private string $password;

    /**
     * @return bool
     */
    abstract protected function checkConnection(): bool;

    /**
     * @param string $username
     * @param string $dingtalkId
     * @param bool $isAdmin
     * @param string $userId
     * @return User
     */
    abstract protected function newUser(string $username, string $dingtalkId, bool $isAdmin, string $userId): User;

    /**
     * @param string $username
     * @return array
     */
    abstract protected function findUserByUsername(string $username): array;

    /**
     * @param User $user
     * @return bool
     */
    abstract protected function updateUserData(User $user): bool;
}

/**
 *
 */
abstract class Plugin
{
    /**
     * @return string
     */
    public function getUpdateUrl(): string
    {
        return 'https://update.example.com/' . $this->getName() . '.json';
    }

    /**
     * @return string
     */
    abstract public function getName(): string;

    /**
     * @return bool
     */
    public function check(): bool
    {
        if ($this->getAuthor() && $this->getVersion() && $this->getDescription() && $this->getName() && $this->getEventList()) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * @return string
     */
    abstract public function getAuthor(): string;

    /**
     * @return string
     */
    abstract public function getVersion(): string;

    /**
     * @return string
     */
    abstract public function getDescription(): string;

    /**
     * @return array
     */
    abstract public function getEventList(): array;
}

class User
{
    public string $userId;
    public string $username;
    public string $dingtalkId;
    public bool $isAdmin;
    public string $userid;

    /**
     * @param string $key
     * @param string $value
     * @return bool
     */
    public function addCustomData(string $key, string $value): bool
    {
        try {
            $this->$key = $value;
            return true;
        } catch (Exception $e) {
            return false;
        }
    }

    public array $customData;
}

# 测试代码

class MySQLConnector extends DatabaseConnector
{
    private string $url;
    private string $username;
    private string $password;
    private string $dbname;
    /** @noinspection PhpMissingFieldTypeInspection */
    private $conn;

    /**
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
     * @return void
     */
    private function connect(): void
    {
        $this->conn = new PDO("mysql:host=$this->url;dbname=$this->dbname", $this->username, $this->password);
        $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $this->createTableIfNotExists();
    }

    /**
     * @return void
     */
    private function createTableIfNotExists(): void
    {
        $sql = "CREATE TABLE IF NOT EXISTS users (
            userId VARCHAR(255) PRIMARY KEY,
            username VARCHAR(255) NOT NULL,
            dingtalkId VARCHAR(255) NOT NULL,
            isAdmin TINYINT(1) NOT NULL,
            customData TEXT
        )ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;";
        $this->conn->exec($sql);
    }

    /**
     * @return bool
     */
    public function checkConnection(): bool
    {
        return $this->conn !== null;
    }

    /**
     * @param string $username
     * @param string $dingtalkId
     * @param bool $isAdmin
     * @param string $userId
     * @return User
     */
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

    /**
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
            $user = new User();
            $user->userId = $row['userId'];
            $user->username = $row['username'];
            $user->dingtalkId = $row['dingtalkId'];
            $user->isAdmin = (bool)$row['isAdmin'];
            $user->customData = $row['customData'];
            $users[] = $user;
        }

        return $users;
    }

    /**
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
/**
 * $test = new MySQLConnector('192.168.0.105', 'DingaiaPHP-Next', 'k8TSkJp4czcDYPz2', 'DingaiaPHP-Next');
* echo $test->checkConnection();
* $test->newUser('2', '2', false, '2');
 **/

