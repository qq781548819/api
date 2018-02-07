<?php
/**
 * Created by PhpStorm.
 * User: zhouwenping
 * Date: 2017/11/9
 * Time: 下午4:20
 */

namespace app\push\controller;

use \GatewayWorker\Lib\Gateway;
use \Workerman\Lib\Timer;
// 使用 Medoo 的命名空间
use Medoo\Medoo;
use \Workerman\Protocols\Http;

class Event
{
    public static $rootdir='./src/ws/';
    public static $client_id;
    //声明
    public static $re;   //输出
    public static $action;   //输出
    /**
     * 进程启动后初始化数据库连接
     */
    public static function onWorkerStart($worker)
    {
        global $db;
      
        $db = new Medoo(config('Meedo_DB'));
        // global $live;
        // $live = new \live();
    }

    // 定时关闭未认证的连接
    public static function onConnect($client_id)
    {
        // 连接到来后，定时30秒关闭这个链接，需要30秒内发认证并删除定时器阻止关闭连接的执行
        $_SESSION['auth_timer_id'] = Timer::add(30, function ($client_id) {
            Gateway::closeClient($client_id);
        }, array($client_id), false);

        Gateway::sendToClient($client_id, json_encode(array(
            'type'      => 'init',
            'client_id' => $client_id
        )));
            
    }
    
    /**
     * 有消息时
     *
     * @param int   $client_id
     * @param mixed $message
     *
     * @throws Exception
     */
    public static function onMessage($client_id, $message)
    {
        global $db,$json;
        self::$re=array('error' =>'0','msg'=>'success','route'=>'' );
        self::$client_id = $client_id;
        // echo "client:{$_SERVER['REMOTE_ADDR']}:{$_SERVER['REMOTE_PORT']} gateway:{$_SERVER['GATEWAY_ADDR']}:{$_SERVER['GATEWAY_PORT']}  client_id:$client_id session:" . json_encode($_SESSION) . " onMessage:" . $message . "\n";
        // 使用数据库实例
        // 客户端传递的是json数据
        $json = json_decode($message, true);
        $exist = isset($json['route']);
        if ($exist) {
            self::$action = $json['route'];
            self::myrouter($json['route']);
        } else {
            self::error(2);
        }
    }
   
   
    /**
     * 当客户端断开连接时
     *
     * @param integer $client_id 客户端id
     */
    public static function onClose($client_id)
    {
        // debug
        echo "client:{$_SERVER['REMOTE_ADDR']}:{$_SERVER['REMOTE_PORT']} gateway:{$_SERVER['GATEWAY_ADDR']}:{$_SERVER['GATEWAY_PORT']}  client_id:$client_id onClose:''\n";
        //如果当前用户存在聊天订单，退出后设置退组



    }

    //{"route":"dx.login","account":"","psw":""}
    public static function myrouter($route)
    {
        $uid;
        $userinfo;
        //接口调试
        $action =self::trimStr($route);
        // $this->action=$action;
        if ($action!='') {
            $router=str_replace('.', '/', $action);
            $mode=explode('/', $router);

            //需要权限接口区分
            if ($mode[0]=='auth') {
                // var_dump($_SESSION);
                if (isset($_SESSION['uid']) and $_SESSION['uid']!='') {
                    $uid = $_SESSION['uid'];
                    $userinfo = MDB()->get('user', '*', ['id'=>$uid]);
                } else {
                    self::error(3);
                }
            }
            //接口请求文件存在导入
            $path=self::$rootdir.$router.'.php';
            if (file_exists($path)) {
                //引入分组公用文件
                $tmppath1=dirname($path).'/common.php';
                if (file_exists($tmppath1) and $tmppath!=$tmppath1) {
                    include $tmppath1;
                }
                //引入路由目标文件
                include $path;
            } else {
                //$this->error(5,$path);
                self::error(5);
            }
        } else {
            error(4);
        }
    }
    //过滤
    public static function trimStr($str)
    {
        return preg_match("/^[a-zA-Z0-9\.]*$/", $str)?$str:'';
    }
    //错误返回
    public static function error($n, $v='')
    {
        $error_list=array(
      '{"error":"0","msg":"success"}',
      '{"error":"1","msg":"nothing"}',
      '{"error":"2","msg":"json_format_error"}',
      '{"error":"3","msg":"token_error"}',
      '{"error":"4","msg":"action_empty"}',
      '{"error":"5","msg":"file_not_exists=>%s"}',
      '{"error":"6","msg":"%s_value_empty"}',
      '{"error":"7","msg":"%s_gvalue_empty"}',
      '{"error":"8","msg":"class_notfind"}',
      '{"error":"9","msg":"xxxxxxxxxxx"}',
      '{"error":"10","msg":"xxxxxxxxxxx"}',
      '{"error":"11","msg":"xxxxxxxxxxx"}',
  );
        // var_dump($v);
        $errorInfo;
        if ($v != '') {
            $errorInfo = sprintf($error_list[6], $v);
        } else {
            $errorInfo =$error_list[$n];
        }
        // echo '错误'.$rr;
        Gateway::sendToCurrentClient($errorInfo);
        // return;
        die();
        // try{
        //     Http::end();
        // }catch(Exception $e){
        //     // var_dump($e->getMessage());
        // }
    }
    //取值判定
    public static function get($key)
    {
        global $json;
        if (isset($json[$key])) {
            if (empty($json[$key])) {
                self::error(6, $key);
            } else {
                return $json[$key];
            }
        } else {
            self::error(6, $key);
        }
    }

    //结果返回
    public static function ec($text='',$route='')
    {
        self::$re=array('error' =>'0','msg'=>'success','route'=>'' );
        //数组成功输出
        if (is_array($text)) {
            self::$re['result']=$text;
            self::$re['msg']= 'success';
            self::$re['route']= self::$action;
        } elseif ($text!='') {
            self::$re['error']='1';
            self::$re['route']= self::$action;
            self::$re['msg']=$text;
        }
        if($route != ''){
            self::$re['route'] = $route;
        }
        $result = json_encode(self::$re);//JSON_UNESCAPED_UNICODE
        Gateway::sendToCurrentClient($result);
        try{
            Http::end();
        }catch(Exception $e){
            // var_dump($e->getMessage());
        }
    }

    public static function log(){
        var_dump('测试调用');
    }
    public static function ec_user($client_id,$text="",$route=''){
        self::$re=array('error' =>'0','msg'=>'success','route'=>''  );
        //数组成功输出
        if (is_array($text)) {
            self::$re['result']=$text;
            self::$re['msg']= 'success';
            self::$re['route']= self::$action;
        } elseif ($text!='') {
            self::$re['error']='1';
            self::$re['route']= self::$action;
            self::$re['msg']=$text;
        }
        if($route != ''){
            self::$re['route'] = $route;
        }
        $result = json_encode(self::$re);//JSON_UNESCAPED_UNICODE
        Gateway::sendToClient($client_id,$result);
        try{
            Http::end();
        }catch(Exception $e){
            // var_dump($e->getMessage());
        }
    }
}
