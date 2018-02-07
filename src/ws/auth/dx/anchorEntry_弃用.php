<?php
//主播进场，当前用户扣费开始


use \GatewayWorker\Lib\Gateway;
use \Workerman\Lib\Timer;
use \Workerman\Protocols\Http;

global $orderSn,$user_id;
$user_id = self::get('user_id');//用户id

// var_dump($userinfo);
// $lookers = Gateway::getClientSessionsByGroup($group);

// if (!isset($lookers[self::$client_id])) {
//     self::ec('请先进入匹配直播状态');
//     return;
// }

global $userInfo,$arr ;
$arr = Gateway::getClientIdByUid($user_id);

if (empty($arr)) {
    self::ec('当前没有该用户');
    return;
}
$userInfo = Gateway::getSession($arr[0]);
// $currentInfo MDB()->get('user','*',['id'=>$_SESSION['uid']]);
if ($userinfo['user_type'] != 3) {
    self::ec('您当前不是主播，请在官方渠道申请，感谢您的配合！');
    return;
}

global $rule,$balance;
$rule = MDB()->get('user', "fee", ['id'=>$_SESSION['uid']]);
$balance = MDB()->get('user', 'balance', ['id'=>$user_id]);

$TMins = $balance/$rule;


var_dump('总分钟数：'.$TMins);
var_dump('总秒数：'.($TMins*60));
var_dump($balance);
if ($balance<$rule) {
    self::ec_user(self::$client_id, '当前用户余额不足');
    self::ec_user($arr[0], '您的余额不足');
    return;
}

//扣除开始直播的这一分钟费用
//主播进账，用户扣费

MDB()->action(function ($database) {
    global $user_id,$rule,$balance,$arr;
    $income_rate = $database->get('user', "income_rate", ['id'=>$_SESSION['uid']]);
    // $rule = $database->get('user', "fee", ['id'=>$_SESSION['uid']]);
    // $balance = $database->get('user', ['balance'], ['id'=>$user_id]);
    // if($balance<$rule){
    //     self::ec_user(self::$client_id,'当前用户余额不足');
    //     self::ec_user($arr[0],'您的余额不足');
    //     return false;
    // }
    $anchor = $rule*$income_rate;
    $platform = $rule - $anchor;
    $data = $database->update('user', [
            "income[+]" => floatval($anchor),
            "platform_income[+]" =>  floatval($platform)
        ], [
            'id'=>$_SESSION['uid']
        ]);
    $userData = $database->update('user', ['balance[-]' => $rule], ['id'=>$user_id]);
    if ($data->rowCount()!=1 || $userData->rowCount()!=1) {
        return false;
    }
});

// //开启定时计费定时器计算下一分钟的扣费
// $_SESSION['user_chat_valuation_timer'] = Timer::add(60, 'timed_task', array($user_id));
$userInfo['user_chat_valuation_timer'] = Timer::add(60, 'timed_task', array($user_id));
$balance = MDB()->get('user', 'balance', ['id'=>$user_id]);

$TMins = $balance/$rule;
$map = [
    'remain_seconds' => strval(round($TMins*60)),
    'balance' => $balance,
];
ec_user($_SESSION['client_id'], $map);
ec_user($arr[0], $map);
return;