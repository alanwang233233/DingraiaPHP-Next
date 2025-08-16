<?php
return function () {
    return [
        'redis' => [
            'host' => 'localhost',
            'port' => 6379,
            'password' => '123456',
            'database' => 'test',
            'timeout' => 0
        ],
        'database' => [
            'driver' => 'mysql',
            'mysql' => [
                'host' => 'localhost',
                'port' => 3306,
                'username' => 'root',
                'password' => '123456',
                'database' => 'test'
            ],
            'sqlite' => [
                'database' => 'test.db'
            ],
            'pgsql' => [
                'host' => 'localhost',
                'port' => 5432,
                'username' => 'root',
                'password' => '123456',
                'database' => 'test'
            ]
        ]
    ];
};
