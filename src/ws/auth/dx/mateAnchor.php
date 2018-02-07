<?php


use \GatewayWorker\Lib\Gateway;
use \Workerman\Lib\Timer;

//status 1 为返回当前人数 2为匹配到的用户信息
$group = 'looker';
$anchorGroup = 'anchor';

Gateway::joinGroup(self::$client_id, $group);

$lookers = Gateway::getClientSessionsByGroup($group);
$anchors = Gateway::getClientSessionsByGroup($anchorGroup);
$count = 0;

//用户匹配主播
$_SESSION['looker_search_timer_id'] = Timer::add(2, function ($anchorGroup, $count) {
    $anchors = Gateway::getClientSessionsByGroup($anchorGroup);
    // var_dump($anchors);
    if ($count >5) {//关闭匹配
        // if(isset($_SESSION['looker_search_timer_id'])){
        //     Timer::del($_SESSION['looker_search_timer_id']);
        //     unset($_SESSION['looker_search_timer_id']);
        // }
        self::ec_user(self::$client_id, '匹配时间过长，客官没有满意的么？开通vip，更多高颜值美女主播等你撩');
    }
    
    if (count($anchors)==0) {
        if (isset($_SESSION['looker_search_timer_id'])) {
            Timer::del($_SESSION['looker_search_timer_id']);
            unset($_SESSION['looker_search_timer_id']);
        }
       
        self::ec_user(self::$client_id, '暂无主播在线');
    } else {
        $anchor = array_rand($anchors, 1); //随机获取一个主播
        // $zhubo_id = $clients_list[$res]['uid'];
        if (isset($_SESSION['looker_search_timer_id'])) {
            Timer::del($_SESSION['looker_search_timer_id']);
            unset($_SESSION['looker_search_timer_id']);
        }
        $anchorInfo = Gateway::getSession($anchor);
       // $_SESSION['anchorInfo'] = $anchorInfo;
    //    $m_self = M('follow')->where(array('uid'=>$user_id,'is_unfollow'=>1,'to_user_phone'=>$phone ))->find();
       $is_follow = MDB()->has('follow',[
            'uid'=>$_SESSION['uid'],'is_unfollow'=>1,'to_uid'=>$anchorInfo['uid'] 
        ]);
        if($is_follow){
            $anchorInfo['is_follow'] ="1";
        }else{
            $anchorInfo['is_follow'] ="0";
        }
        $anchorInfo['status'] = '2';
        // var_dump($_SESSION);
        $userinfo = $_SESSION;
        $userinfo['status'] = '2';
       
        self::ec_user(self::$client_id, $anchorInfo);
        self::ec_user($anchor, $userinfo,"auth.dx.anchorStart");
    }
    $count++;
    var_dump($_SESSION);
}, array($anchorGroup,$count));
$map = [
    'status' => '1',
    'lookers' => strval(count($lookers)),
    'anchors' => strval(count($anchors)),
    
];

self::ec($map);









// var_dump($_SESSION);

// array(5) {
//     ["uid"]=>
//     string(6) "100000"
//     ["nickname"]=>
//     string(9) "大哥哥"
//     ["avatar"]=>
//     string(84) "https://imgsrc.baidu.com/baike/pic/item/14ce36d3d539b6006501cb61e250352ac75cb71d.jpg"
//     ["mobile"]=>
//     string(11) "15119120669"
//     ["is_vip"]=>
//     string(1) "0"
//   }
  

// var_dump($uid);//string(6) "100000"


// var_dump($userinfo);
