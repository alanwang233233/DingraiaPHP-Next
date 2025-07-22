<?php
require_once __DIR__ . '/../vendor/autoload.php';
/*
use App\Database\MySQLConnector;

$test = new MySQLConnector('192.168.0.105', 'DingaiaPHP-Next', 'k8TSkJp4czcDYPz2', 'DingaiaPHP-Next');
$test->newUser('55', false, '55');
var_dump($test->findUserByUsername('2'));


use App\Cache\RedisCache;

$test = new RedisCache('192.168.0.105', '6379', 'mBfe8X2C8aiE2e7w', '1');
$test->set('test', '123');
var_dump($test->get('test'));*/
// 应用目录为上一目录
const APP_PATH = '../';

// 开启调试模式
const APP_DEBUG = true;

// 加载框架文件

// 加载配置文件

// 实例化框架类

use App\Models\Database\SQLiteConnector;

$test = new SQLiteConnector('test.db');
var_dump($test->checkUserIdExists('1'));
