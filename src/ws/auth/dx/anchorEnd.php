<?php


use \GatewayWorker\Lib\Gateway;
use \Workerman\Lib\Timer;

$lookerGroup = 'looker';
$anchorGroup = 'anchor';


$userInfo = MDB()->get("user", "*", [
    "id" => $_SESSION['uid']
]);

if ($userInfo['user_type'] == '3') {
    //退出组
    Gateway::leaveGroup(self::$client_id, $anchorGroup);
    self::ec();
} else {
    self::ec('您当前不是主播，请在官方渠道申请，感谢您的配合！');
}
