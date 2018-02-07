<?php



use \GatewayWorker\Lib\Gateway;

if (isset($_SESSION['user_chat_valuation_timer'])) {
    unset($_SESSION['user_chat_valuation_timer']);
}


$userInfo = MDB()->get('user', '*', ['id'=>$_SESSION['uid']]);
self::ec($userInfo);
