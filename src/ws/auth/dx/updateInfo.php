<?php



use \GatewayWorker\Lib\Gateway;

$user_id = self::get('user_id');


$exist = MDB()->get("user",'*', [
    'id' =>$user_id       
]);
// var_dump($_SESSION);
if($exist){
    $newInfo = [];
    $newInfo['uid'] = $exist['id'];
    $newInfo['nickname'] = $exist['user_nickname'];
    $newInfo['avatar'] =$exist['avatar'];
    $newInfo['mobile'] = $exist['mobile'];
    $newInfo['is_vip'] = $exist['is_vip'];
    $newInfo['client_id'] = self::$client_id;
    if ($exist['user_type'] == '3') {
        $newInfo['rule'] = $exist['fee'];
    }
    // $_SESSION['']
    // Gateway::bindUid(self::$client_id, $exist['id']);
    Gateway::setSession(self::$client_id, $newInfo);
    $map = [
        'user_id' =>$exist['id'],
        'nickname' =>$exist['user_nickname'],
        'mobile' => $exist['mobile'],
        'client_id' => self::$client_id
    ];
    // json_encode($userInfo);
    self::ec($_SESSION);
}else{
    self::ec('更新个人信息失败');
}