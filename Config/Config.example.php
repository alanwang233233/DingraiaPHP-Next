<?php
return function () {
    return [
        'cache' => [
            'type' => 'redis',
            'redis' => [
                'driver' => 'redis',
                'host' => 'localhost',
                'port' => 6379,
                'password' => '123456',
                'dbname' => 'test',
                'timeout' => 0
            ]
        ],
        'database' => [
            'type' => 'mysql',
            'mysql' => [
                'driver' => 'MySQL',
                'host' => 'localhost',
                'port' => 3306,
                'username' => 'root',
                'password' => '123456',
                'dbname' => 'test',
            ]
        ]
    ];
};
