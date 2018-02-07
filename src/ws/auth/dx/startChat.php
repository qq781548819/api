<?php

use \GatewayWorker\Lib\Gateway;
use \Workerman\Lib\Timer;

$group = 'looker';
$anchorGroup = 'anchor';

$anchor_id = self::get('anchor_id');


$lookers = Gateway::getClientSessionsByGroup($group);

if (!isset($lookers[self::$client_id])) {
    self::ec('请先进入匹配直播状态');
    return;
}


// MDB()->
$yCode = array('A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I', 'J');
global $orderSn,$time;
$orderSn = 'chat_'.$yCode[intval(date('Y')) - 2018]. strtoupper(dechex(date('m'))) . date('d') . substr(time(), -5) . substr(microtime(), 2, 5) . sprintf('%02d', rand(0, 99));
$time = time();
$arr = Gateway::getClientIdByUid($anchor_id);
if (!empty($arr)) {
    //开始跟该主播聊天，从匹配组退出
    if (isset($_SESSION['looker_search_timer_id'])) {
        Timer::del($_SESSION['looker_search_timer_id']);
        unset($_SESSION['looker_search_timer_id']);
    }
    global $anchorInfo;
    $anchorInfo = Gateway::getSession($arr[0]);

    Gateway::leaveGroup($anchorInfo['client_id'], $anchorGroup);

    $user = MDB()->get('user', ['balance'], ['id'=>$_SESSION['uid']]);
    $anchor = MDB()->get('user', ['fee'], ['id'=>$anchorInfo['uid']]);
    // var_dump($user);

    if ($user['balance']<$anchor['fee']) {
        self::ec('费用不足，请充值');
        return;
    }
    

    MDB()->action(function ($database) {
        // $anchorInfo = Gateway::getSession($arr[0]);
        global $anchorInfo;
        global $orderSn,$time;
        $rule = $database->get('user', "fee", ['id'=>$anchorInfo['uid']]);
        $id = $database->insert('chat_order', [
            // "chat_status" => "0",
            "order_sn" => $orderSn ,
            "start_time" => $time,
            "room_id" => $orderSn,
            "user_id" => $_SESSION['uid'],
            "user_name" => $_SESSION['nickname'],
            "user_phone" => $_SESSION['mobile'],
            "user_avatar" => $_SESSION['avatar'],
            "anchor_id" => $anchorInfo['uid'],
            "anchor_name" => $anchorInfo['nickname'],
            "anchor_phone" => $anchorInfo['mobile'],
            "anchor_avatar" => $anchorInfo['avatar'],
            "rule" =>  $rule
        ]);
        $_SESSION['chat_order_sn'] = $orderSn;
        Gateway::joinGroup(self::$client_id, $orderSn);
        $chatGroup = $database->insert('chat_group', [
            // "chat_status" => "0",
            "group_name" => $orderSn ,
            "user_id" => $_SESSION['uid'],
            "user_type" => $_SESSION['user_type'],
            "user_id" => $_SESSION['uid'],
            "user_name" => $_SESSION['nickname'],
            "user_phone" => $_SESSION['mobile'],
            "user_avatar" => $_SESSION['avatar'],
            "anchor_id" => $anchorInfo['uid'],
            "anchor_name" => $anchorInfo['nickname'],
            "anchor_phone" => $anchorInfo['mobile'],
            "anchor_avatar" => $anchorInfo['avatar'],
            "add_time" =>  $time
        ]);

        var_dump($id->rowCount());
        var_dump($chatGroup->rowCount());

        if ($id->rowCount() == 1 ) {
 
        //发送用户、主播房间号，用户主播进入房间，进入直播间就绪状态
            $userMap=[
            'signaling_key' => getAgoraToken($_SESSION['uid']),
            'channel_key' => getAgoraChannelKey($orderSn, $_SESSION['uid']),
            'channel_name' => $orderSn,
            'order_sn' => $orderSn,
            'peer_id' => $anchorInfo['uid']
        ];
            $anchorMap = [
            'signaling_key' => getAgoraToken($anchorInfo['uid']),
            'channel_key' => getAgoraChannelKey($orderSn, $anchorInfo['uid']),
            'channel_name' => $orderSn,
            'order_sn' => $orderSn,
            'peer_id' => $_SESSION['uid']
        ];
            self::ec_user($_SESSION['client_id'], $userMap);
            self::ec_user($anchorInfo['client_id'], $anchorMap, "auth.dx.anchorStart");
        } else {
            self::ec_user($_SESSION['client_id'], '添加异常，数据回滚');
            self::ec_user($anchorInfo['client_id'], '添加异常，数据回滚');
            return false;
            //异常情况，如果有计费定时器则关闭
        }
    });
} else {
    self::ec('查找不到该主播，请校验');
}
