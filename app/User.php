<?php

namespace App;
class User
{
    public int $uid;
    public string $userId;
    public string $username;
    public string $dingtalkId;
    public bool $isAdmin;
    public array $customData;

    public function __construct($uid, $userId, $username, $dingtalkId, $isAdmin, $customData)
    {
        $this->uid = $uid;
        $this->userId = $userId;
        $this->username = $username;
        $this->dingtalkId = $dingtalkId;
        $this->isAdmin = $isAdmin;
        $this->customData = $customData;
    }

    final static public function new(): User
    {

    }
}