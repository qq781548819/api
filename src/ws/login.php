<?php

use \GatewayWorker\Lib\Gateway;
use \Workerman\Lib\Timer;

$account = self::get('account');
$psw = self::get('psw');


$exist = MDB()->get("user",'*', [
    "AND" => [
        "user_login" => $account,
        'password' => $psw
	]
       
]);
// var_dump($_SESSION);
if($exist){
    if(isset($_SESSION['auth_timer_id'])){
        Timer::del($_SESSION['auth_timer_id']);
        unset($_SESSION['auth_timer_id']);
    }
    $_SESSION['uid'] = $exist['id'];
    $_SESSION['nickname'] = $exist['user_nickname'];
    $_SESSION['avatar'] =$exist['avatar'];
    $_SESSION['mobile'] = $exist['mobile'];
    $_SESSION['is_vip'] = $exist['is_vip'];
    $_SESSION['user_type'] = $exist['user_type'];
    $_SESSION['client_id'] = self::$client_id;
    if ($exist['user_type'] == '3') {
        $_SESSION['rule'] = $exist['fee'];
    }
    // $_SESSION['']
    Gateway::bindUid(self::$client_id, $exist['id']);
    $map = [
        'user_id' =>$exist['id'],
        'nickname' =>$exist['user_nickname'],
        'mobile' => $exist['mobile'],
        'client_id' => self::$client_id
    ];
    // json_encode($userInfo);
    self::ec($map);
}else{
    self::ec('登录失败');
}
// var_dump($exist_id);
