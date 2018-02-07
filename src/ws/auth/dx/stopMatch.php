<?php

use \GatewayWorker\Lib\Gateway;
use \Workerman\Lib\Timer;


$lookerGroup = 'looker';
$anchorGroup = 'anchor';

//关闭用户匹配定时组
if (isset($_SESSION['looker_search_timer_id'])) {
    Timer::del($_SESSION['looker_search_timer_id']);
    unset($_SESSION['looker_search_timer_id']);
}
//退出组
Gateway::leaveGroup(self::$client_id, $lookerGroup);

self::ec();