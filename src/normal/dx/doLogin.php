<?php

$mobile=$this->get('mobile');
$password = $this->get('password');
$code = $this->get('code');

$check_code = 1234;

if ($code !=$check_code) {
    $this->ec(['status'=>0,'msg'=>'验证码错误']);
}
// var_dump($this);
$user = $this->mdb->get("user", "*", [
    "mobile" => $mobile
]);

// var_dump($user);

$pwd=cmf_password($password);
$time = time();

if (!$user) {
    //注册用户
    $insertUser=array(
        'user_pass'=>$pwd,
        'last_login_time'=>$time,
        'create_time'=>$time,
        'mobile'    =>$mobile,
        'user_login'=>$mobile,
        'password'=>$password,
        'avatar'=>$this->getUrl().C('default_avatar'),
        'user_nickname'=>$mobile
    );
    $id = $this->mdb->insert('user', $insertUser);
    
    //新增单个环信用户
    $hxResult = $this->hx->createUser($mobile, $password);
    
    $jwt=$this->createJwt($id, $insertUser['user_nickname']);
    // if(array_key_exists("action",$hxResult)){
    $res=array(
            'access_token'  =>$jwt,
            'user_id'       =>$id,
            'account'       =>$insertUser['user_login'],
            'password'      =>$insertUser['password']
            // 'easemob' =>$hxResult
        );
    $this->ec($res);
} else {
    //修改密码
    $data=array(
        'user_pass'=>$pwd,
        'last_login_time'=>$time,
        'password'=>$password
    );
    $this->mdb->update('user', $data, ['id'=>$user['id']]);
    $user = $this->mdb->get("user", "*", [
        "mobile" => $mobile
    ]);
    //修改环信密码
    $hxResult = $this->hx->resetPassword($mobile, $password);

    $jwt=$this->createJwt($user['id'], $user['user_nickname']);
    // if(array_key_exists("action",$hxResult)){
    $res=array(
            'access_token'  => $jwt,
            'user_id'       =>$user['id'],
            'account'       =>$user['user_login'],
            'password'      =>$user['password'],
        );
    $this->ec($res);
}
