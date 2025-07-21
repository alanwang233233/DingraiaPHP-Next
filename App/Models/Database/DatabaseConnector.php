<?php

namespace App\Models\Database;

use App\Models\User;

abstract class DatabaseConnector
{
    private string $url;
    private string $dbname;
    private string $username;
    private string $password;

    public function __construct(string $url, string $username, string $password, string $dbname)
    {
        $this->url = $url;
        $this->username = $username;
        $this->password = $password;
        $this->dbname = $dbname;
    }

    abstract protected function checkConnection(): bool;

    /**
     * 新建用户
     * @param string $username
     * @param bool $isAdmin
     * @param string $userId
     * @return User
     */
    abstract protected function newUser(string $username, bool $isAdmin, string $userId): User;

    /**
     * 根据用户名查找用户
     * @param string $username
     * @return array
     */
    abstract protected function findUserByUsername(string $username): array;

    /**
     * 更新用户数据
     * @param User $user
     * @return bool
     */
    abstract protected function updateUserData(User $user): bool;
}