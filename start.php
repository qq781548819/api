<?php

/**
 * workerman 命令行启动文件
 */
define('APP_PATH', __DIR__ . '/application/');
define('BIND_MODULE', 'push/Worker');
// 加载框架引导文件
require __DIR__ . '/thinkphp/start.php';
