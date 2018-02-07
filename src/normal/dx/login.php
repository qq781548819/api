<?php


$mobile=$this->get('mobile');
$user_pass=$this->get('password');


$password = cmf_password($user_pass);
$user = $this->mdb->get("user", "*", [
    "mobile" => $mobile
]);

if (empty($user)) {
    $this->ec('用户不存在');
}

if ($user['user_pass']!=$password) {
    $this->ec('密码错误');
}

$jwt=$this->createJwt($user['id'], $user['user_nickname']);


$res=array(
    'access_token'  =>$jwt,
    'user_id'       =>$user['id'],
    'account'       =>$user['user_login'],
    'password'      =>$user['password']
);
$this->ec($res);
