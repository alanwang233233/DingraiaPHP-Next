<?php
require_once __DIR__ . '/../vendor/autoload.php';

use App\Dingraia\Dingraia;

const APP_PATH = '../';
const APP_DEBUG = true;
$dingraia = new Dingraia();
$dingraia->run();
ob_end_flush();

