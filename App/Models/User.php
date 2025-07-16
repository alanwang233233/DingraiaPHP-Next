<?php

namespace App\Models;

class User
{
    public int $uid;
    public string $userId;
    public string $username;
    public bool $isAdmin;
    public array $customData;

    public function __construct($uid, $userId, $username, $isAdmin, $customData)
    {
        $this->uid = $uid;
        $this->userId = $userId;
        $this->username = $username;
        $this->isAdmin = $isAdmin;
        $this->customData = $customData;
    }
}