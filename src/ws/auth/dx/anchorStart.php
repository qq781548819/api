<?php


use \GatewayWorker\Lib\Gateway;
use \Workerman\Lib\Timer;

$group = 'looker';
$anchorGroup = 'anchor';

$userInfo = MDB()->get('user',"*",['id'=>$_SESSION['uid']]);
var_dump($userInfo);
// self::ec_user(self::$client_id, $userInfo);

if ($userInfo['user_type'] == 3) {
    Gateway::joinGroup(self::$client_id, $anchorGroup);
    
    $lookers = Gateway::getClientSessionsByGroup($group);
    $anchors = Gateway::getClientSessionsByGroup($anchorGroup);
    $map = [
        'lookers' => count($lookers),
        'anchors' => count($anchors)
    ];
    
    self::ec($map);
} else {
    self::ec('您当前不是主播，请去用户中心认证资料
    ，感谢您的配合！');
}
