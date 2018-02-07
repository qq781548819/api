<?php


//'wx'表微信端，'qq'表qq端
$thirdparty = $this->get('thirdparty');
$openid=$this->get('openid');
$profile_image_url = $this->get('profile_image_url');
$name = $this->get('name');

$phone = $this->get('phone');
$code = $this->get('code');


$key = $thirdparty.$openid;
$map = array('openid'=>$key);
$mapUser = array('mobile'=>$phone);
$query = MDB()->get('third_party_user', '*', $map);//查询第三方表
$queryUser =  MDB()->get('user', '*', $mapUser);//查询user表
// var_dump($query);
// var_dump($queryUser);
$time = time();
if ($thirdparty == 'wx' || $thirdparty == 'qq') {
    if (!$query && !$queryUser) {
        //查询为空，新增第三方数据库数据，新增用户表，返回token
        
        MDB()->pdo->beginTransaction();
        try {
            $pwd=cmf_password($phone);
            $user=array(
                'user_pass'=>$pwd,
                'last_login_time'=>$time,
                'create_time'=>$time,
                'mobile'    =>$phone,
                'user_login'=>$phone,
                'user_nickname' => $name,
                'avatar' => $profile_image_url,
                'password'=>$phone,
                'avatar'=>$this->getUrl().C('default_avatar'),
                'user_nickname'=>$phone
            );
            $user['id'] = MDB()->insert('user', $user);
            
            $hxResult = $this->hx->createUser($user['mobile'], $user['password']);
            $third_party = array(
                'user_id'=>$user['id'],
                'last_login_time' => $time,
                'create_time' => $time,
                'status' => 1,
                'nickname' =>$name,
                'third_party' =>$thirdparty,
                'app_id'=>'wx_app_id',
                'openid'=>$key
            );
            $third_party['id'] = MDB()->insert('third_party_user', $third_party);
        
            $jwt=$this->createJwt($user['id'], $user['user_nickname']);
            $res=array(
                'access_token'  =>$jwt,
                'user_id'       =>$user['id'],
                'account'       =>$user['user_login'],
                'password'      =>$user['password']
                // 'easemob' =>$hxResult
            );
            
            /* Commit the changes */
            MDB()->pdo->commit();
            // $this->ec($res);
        } catch (\Exception $e) {
            // var_dump($e->getMessage());
            MDB()->pdo->rollBack();
            // $this->ec('操作异常，数据库事务回滚');
        }
       
    
        // var_dump($user);
        // var_dump($third_party);
    } elseif (!$query && $queryUser) {
        MDB()->pdo->beginTransaction();//事务操作
        try {
            //var_dump('测试用户已经注册,sfafasfsafa');
            $third_party = array(
                'user_id'=> $queryUser['id'],
                'last_login_time' => $time,
                'create_time' => $time,
                'status' => 1,
                'nickname' =>$name,
                'third_party' =>$thirdparty,
                'app_id'=>'wx_app_id',
                'openid'=>$key
            );
            $third_party['id'] = MDB()->insert('third_party_user', $third_party);

            $data = array(
                'user_nickname' => $name,
                'avatar' => $profile_image_url
            );
            MDB()->update('user', $data, ['id'=>$queryUser['id']]);
            $jwt=$this->createJwt($queryUser['id'], $queryUser['user_nickname']);
            $res=array(
                'access_token'  =>$jwt,
                'user_id'       =>$queryUser['id'],
                'account'       =>$queryUser['user_login'],
                'password'      =>$queryUser['password']
                // 'easemob' =>$hxResult
            );
            /* Commit the changes */
            MDB()->pdo->commit();
            $this->ec($res);
        } catch (\Exception $e) {
            var_dump($e->getMessage());
            MDB()->pdo->rollBack();
            $this->ec('操作异常，数据库事务回滚');
            throw $e;
        }
    } else {
        $this->ec('当前第三方账号已经绑定');
    }
} else {
    $this->ec('请输入正确的平台号');
}
