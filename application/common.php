<?php
// +----------------------------------------------------------------------
// | ThinkPHP [ WE CAN DO IT JUST THINK ]
// +----------------------------------------------------------------------
// | Copyright (c) 2006-2016 http://thinkphp.cn All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: 流年 <liu21st@gmail.com>
// +----------------------------------------------------------------------

// 应用公共文件
use Medoo\Medoo;
use \GatewayWorker\Lib\Gateway;
use \Workerman\Lib\Timer;
use \Workerman\Protocols\Http;

include EXTEND_PATH."AgoraDynamicKey/DynamicKey5.php";
include EXTEND_PATH."AgoraDynamicKey/SignalingToken.php";
function write_log($content, $type="request")
{
    $rs=array('status'=>-1,'msg'=>"action is error");
    $log_dir=!empty(C('log_dir'))?C('log_dir'):'./log/';
    if (!is_dir($log_dir)) {
        if (!mkdir(iconv("UTF-8", "GBK", $log_dir), 0777, true)) {
            $rs['msg']='create dir error';
            return $rs;
        }
    }
    $path=$log_dir.$type.'_'.date("Y-m-d").'.log';
    $bool=file_put_contents($path, date("Y-m-d H:i:s"). " " . $content. "\r\n", FILE_APPEND | LOCK_EX);
    if (!empty($bool)) {
        $rs=array('status'=>1,'msg'=>"success");
    }
    return $rs;
}
//自定义数据库操作函数
function MDB($dbname='')
{
    $db = new Medoo(config('Meedo_DB'));
    return $db;
}

  //校验jwt权限API
 function verifyJwt($jwt)
 {
     $key = md5(C('JwtKey'));
     // JWT::$leeway = 3;
     $authInfo ;
     try {
         $authInfo = JWT::decode($jwt, $key, array('HS256'));
         return $authInfo;
     } catch (\Firebase\JWT\SignatureInvalidException $e) {
         // var_dump($e);
         $this->ec('token无效');
     } catch (\Firebase\JWT\ExpiredException $e) {
         // var_dump($e);
         $this->ec('token过期');
     } catch (Exception $e) {
         $this->ec($e);
     }
 }

 //获取声网token
 function getAgoraToken($uid)
 {
     $validTimeInSeconds = 86400;

     $res = getToken(config('Agora_appid'), config('Agora_appCert'), $uid, $validTimeInSeconds);

     return $res;
 }

 //获取信令通道key
 function getAgoraChannelKey($channelName, $uid)
 {
     //        $randomInt = 58964981;
     $randomInt = rand(100000, 999999);
     $ts= time();
     $expiredTs= 0;
     $res = generateMediaChannelKey(config('Agora_appid'), config('Agora_appCert'), $channelName, $ts, $randomInt, $uid, $expiredTs);
    
     return $res;
 }



 
function ec_user($client_id, $text="", $route='')
{
    $re=array('error' =>'0','msg'=>'success','route'=>''  );
    //数组成功输出
    if (is_array($text)) {
        $re['result']=$text;
        $re['msg']= 'success';
        $re['route']= 'auth.dx.anchorEntry';
    } elseif ($text!='') {
        $re['error']='1';
        $re['route']= 'auth.dx.anchorEntry';
        $re['msg']=$text;
    }
    if ($route != '') {
        $re['route'] = $route;
    }
    $result = json_encode($re);//JSON_UNESCAPED_UNICODE
    Gateway::sendToClient($client_id, $result);
    try {
        Http::end();
    } catch (Exception $e) {
        // var_dump($e->getMessage());
    }
}

function timed_task($user_id)
{
    global $rule,$balance,$user_id,$arr;
    $rule = MDB()->get('user', "fee", ['id'=>$_SESSION['uid']]);
    $balance = MDB()->get('user', 'balance', ['id'=>$user_id]);

    $TMins = $balance/$rule;

    var_dump('总分钟数：'.$TMins);
    var_dump('总秒数：'.($TMins*60));
    var_dump($balance);
    if ($balance<$rule) {
        if (isset($_SESSION['user_chat_valuation_timer'])) {
            Timer::del($_SESSION['user_chat_valuation_timer']);
        }
        $map = [
            'status_msg' => '当前用户余额不足',
        ];
        ec_user($_SESSION['client_id'], $map, 'checkout_room');
        ec_user($arr[0], $map, 'checkout_room');
        return false;
    }
    //var_dump('定时一次'.$count);

    MDB()->action(function ($database) use ($rule,$balance,$user_id,$arr) {
        // global $rule,$balance,$user_id,$arr;
        $income_rate = $database->get('user', "income_rate", ['id'=>$_SESSION['uid']]);
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
   
    $balance = MDB()->get('user', 'balance', ['id'=>$user_id]);

    $TMins = $balance/$rule;
    $map = [
       'remain_seconds' => strval(round($TMins*60)),
       'balance' => $balance,
   ];
    ec_user($_SESSION['client_id'], $map);
    ec_user($arr[0], $map);
    return;
}
