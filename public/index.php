<?php
require_once __DIR__ . '/../vendor/autoload.php';

use App\Database\MySQLConnector;

$test = new MySQLConnector('192.168.0.105', 'DingaiaPHP-Next', 'k8TSkJp4czcDYPz2', 'DingaiaPHP-Next');
$test->newUser('55', false, '55');
var_dump($test->findUserByUsername('2'));


