<?php
require_once __DIR__ . '/../vendor/autoload.php';

// use App\Database\MySQLConnector;

/*$test = new MySQLConnector('192.168.0.105', 'DingaiaPHP-Next', 'k8TSkJp4czcDYPz2', 'DingaiaPHP-Next');
$test->newUser('55', false, '55');
var_dump($test->findUserByUsername('2'));*/


use App\Cache\RedisCache;

$test = new RedisCache('192.168.0.105', '6379', 'mBfe8X2C8aiE2e7w', '1');
// $test->set('test', '123');
var_dump($test->get('test'));
