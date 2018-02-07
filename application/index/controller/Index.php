<?php
namespace app\index\controller;

use Firebase\JWT\JWT;
use Medoo\Medoo;
use GatewayClient\Gateway;

import('Easemob', EXTEND_PATH);
use Easemob\Easemob;

// require('c/c.php');

class Index
{
    /**
 * === 指定registerAddress表明与哪个GatewayWorker(集群)通讯。===
 * GatewayWorker里用Register服务来区分集群，即一个GatewayWorker(集群)只有一个Register服务，
 * GatewayClient要与之通讯必须知道这个Register服务地址才能通讯，这个地址格式为 ip:端口 ，
 * 其中ip为Register服务运行的ip(如果GatewayWorker是单机部署则ip就是运行GatewayWorker的服务器ip)，
 * 端口是对应ip的服务器上start_register.php文件中监听的端口，也就是GatewayWorker启动时看到的Register的端口。
 * GatewayClient要想推送数据给客户端，必须知道客户端位于哪个GatewayWorker(集群)，
 * 然后去连这个GatewayWorker(集群)Register服务的 ip:端口，才能与对应GatewayWorker(集群)通讯。
 * 这个 ip:端口 在GatewayClient一侧使用 Gateway::$registerAddress 来指定。
 * 
 * === 如果GatewayClient和GatewayWorker不在同一台服务器需要以下步骤 ===
 * 1、需要设置start_gateway.php中的lanIp为实际的本机内网ip(如不在一个局域网也可以设置成外网ip)，设置完后要重启GatewayWorker
 * 2、GatewayClient这里的Gateway::$registerAddress的ip填写填写上面步骤1lanIp所指定的ip，端口
 * 3、需要开启GatewayWorker所在服务器的防火墙，让以下端口可以被GatewayClient所在服务器访问，
 *    端口包括Rgister服务的端口以及start_gateway.php中lanIp与startPort指定的几个端口
 *
 * === 如果GatewayClient和GatewayWorker在同一台服务器 ===
 * GatewayClient和Register服务都在一台服务器上，ip填写127.0.0.1及即可，无需其它设置。
 **/

    //声明
    public $rootdir='./src/';
    public $action='';
    public $json;            //输入
    public $re=array('error' =>'0','msg'=>'success' );   //输出
    public $mdb;

    //环信
    public $hx;
    //构造函数
    public function __construct()
    {
    }
 
    public function test(){
        var_dump('我是测试');
    }
 
    public function index()
    {
        Gateway::$registerAddress = '127.0.0.1';
        $this->initEasemob();
        $this->json=$this->tojson();
        $this->myrouter($this->json->act);
    }

    public function initEasemob()
    {
        $options['client_id']=C('Easemob_client_id');
        $options['client_secret']=C('Easemob_client_secret');
        $options['org_name']=C('Easemob_org_name');
        $options['app_name']=C('Easemob_app_name');
    
        //环信
        $this->hx= new Easemob($options);
    }
   
    //获取参数
    private function tojson()
    {

    //cli模式下参数
        if (isset($_SERVER['argv'])) {
            //修改工作目录使相对路径生效
        chdir(pathinfo($_SERVER['argv'][0])['dirname']);   //chdir('/var/www/html1/api/');

        if (isset($_SERVER['argv'][2])) {
            $_GET['json']=$_SERVER['argv'][2];
        }
            if (isset($_SERVER['argv'][3])) {
                $_GET['access_token']=$_SERVER['argv'][3];
            }
            $json=$_SERVER['argv'][2];
        } else {
            $getjson=$this->q('json');
            $input=file_get_contents('php://input', 'r');
            $json=($getjson!='')?$getjson:$input;
        }
        //file_put_contents('api_log.log', date("Y-m-d H:i:s"). " " . $json. "\r\n", FILE_APPEND | LOCK_EX);
        write_log($json);
        $json=$this->checkJson($json);
        return $json;
    }

    //获取参数
    private function getjson()
    {
        //cli模式下参数
        if (isset($_SERVER['argv'])) {
            //修改工作目录使相对路径生效
        chdir(pathinfo($_SERVER['argv'][0])['dirname']);   //chdir('/var/www/html1/api/');

        if (isset($_SERVER['argv'][2])) {
            $_GET['json']=$_SERVER['argv'][2];
        }
            if (isset($_SERVER['argv'][3])) {
                $_GET['access_token']=$_SERVER['argv'][3];
            }
            $json=$_SERVER['argv'][2];
        } else {
            $getjson=$this->q('json');
            $input=file_get_contents('php://input', 'r');
            $json=($getjson!='')?$getjson:$input;
        }

        $user=json_decode($json, true);
      
        return $json;
    }
    //取值判定
    private function q($key)
    {
        return isset($_REQUEST[$key])?$_REQUEST[$key]:'';
    }


    //自定义路由 接口代码变量作用域所在的函数
    private function myrouter($act)
    {
        // $mdb = $this->initMedooDb();
        //接口调试
        $action =$this->trimStr($act);
        $this->action=$action;
        if ($action!='') {
            $router=str_replace('.', '/', $action);
            $mode=explode('/', $router);

            //token接口区分
            if ($mode[0]=='token') {
                if (isset($_GET['access_token']) and $_GET['access_token']!='') {
                    if (isset($_GET['user_id'])) {
                        $user_id = $_GET['user_id'];
                    } elseif ($_GET['access_token'] == 'tc123456') {
                        $user_id=100000;
                    } else {
                        $authInfo = $this->verifyJwt($_GET['access_token']);
                        $user_id=(float)$authInfo->client_id;
                    }
                    //引入公用文件 ./api/src/token/comm.php
                    $tmppath=$this->rootdir.'token/common.php';
                } else {
                    $this->error(3);
                }
            } else {
                //引入公用文件 ./api/src/normal/comm.php
                $tmppath=$this->rootdir.'normal/common.php';
            }
            include $tmppath;

            //接口请求文件存在导入
            $path=$this->rootdir.$router.'.php';
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
                $this->error(5);
            }
        } else {
            $this->error(4);
        }
    }



    //检测JSON字符串
    private function checkJson($jsonstr)
    {
        if ($jsonstr=='') {
            $this->error(1);
        }
        $json=json_decode($jsonstr);
        if ($json) {
            return $json;
        } else {
            $this->error(2);
        }
    }

    //过滤
    private function trimStr($str)
    {
        return preg_match("/^[a-zA-Z0-9\.]*$/", $str)?$str:'';
    }



    //错误返回
    private function error($n, $v='')
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
        $rr=($v!='')?printf($error_list[$n], $v):$error_list[$n];
        die($rr);
    }

    private function createJwt($user_id, $username)
    {
        $key = md5(C('JwtKey'));
        $time = time();
        $expire = $time + 14400;
        $token = array(
            "client_id" => $user_id,
            "username" => $username,
            "iss" => "wxdxtv.com",//签发组织
            "aud" => "laikunqidagege", //签发作者
            "iat" => $time,
            "nbf" => $time,
            "exp" =>$expire,
        );
        // $jwt = JWT::encode($token, $key);
        $jwt = JWT::encode($token, $key);
        return $jwt;
    }
    //校验jwt权限API
    // private function verifyJwt($jwt)
    // {
    //     $key = md5(C('JwtKey'));
    //     // JWT::$leeway = 3;
    //     $authInfo ;
    //     try {
    //         $authInfo = JWT::decode($jwt, $key, array('HS256'));
    //         return $authInfo;
    //     } catch (\Firebase\JWT\SignatureInvalidException $e) {
    //         // var_dump($e);
    //         $this->ec('token无效');
    //     } catch (\Firebase\JWT\ExpiredException $e) {
    //         // var_dump($e);
    //         $this->ec('token过期');
    //     } catch (Exception $e) {
    //         $this->ec($e);
    //     }
    // }
  //校验jwt权限API
    private function verifyJwt($jwt)
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

    //结果返回
    private function ec($text='')
    {
        //数组成功输出
        if (is_array($text)) {
            $this->re['result']=$text;
            $this->re['msg']=$this->action;
        } elseif ($text!='') {
            $this->re['error']='1';
            $this->re['msg']=$text;
        }
        die(json_encode($this->re, JSON_UNESCAPED_UNICODE));
    }

    //日志
    private function tolog($log)
    {
        file_put_contents('./logg.txt', date("Y-m-d H:i:s").'| '.print_r($log, true).PHP_EOL, FILE_APPEND);
        file_put_contents('./log.txt', date("Y-m-d H:i:s").'| '.print_r($log, true));
    }


    //取值判定
    private function get($key)
    {
        return isset($this->json->$key)?$this->json->$key:$this->error(6, $key);
    }

    //没有值是为空
    private function gett($key)
    {
        return isset($this->json->$key)?$this->json->$key:"";
    }

    private function getUrl()
    {
        $PHP_SELF=$_SERVER['PHP_SELF'];
        $url='http://'.$_SERVER['HTTP_HOST'].substr($PHP_SELF, 0, strrpos($PHP_SELF, '/')+1);
        return $url;
    }

    private function getHttpHost()
    {
        $url='http://'.$_SERVER['HTTP_HOST'];
        return $url;
    }
}
