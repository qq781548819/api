<?php
//'wx'表微信端，'qq'表qq端
$thirdparty = $this->get('thirdparty');
$openid=$this->get('openid');
$profile_image_url = $this->get('profile_image_url');
$name = $this->get('name');

if ($thirdparty == 'wx' || $thirdparty == 'qq') {


    // $model = M('third_party_user');

    // $userModel = M('user');
    
    $key = $thirdparty.$openid;
    $map = array(
        'openid' => $key
    );
    // $third_party_user = $model->where($map)->find();
    $third_party_user = $this->mdb->get('third_party_user', ['user_id'], $map);
    
    if (!$third_party_user) {
        //该微信账号未绑定
        $this->ec('请先绑定该第三方账号');
    } else {
        //查询返回token
        $user_id = $third_party_user['user_id'];
        // var_dump(intval($user_id));
        $user_id = intval($user_id);
        $map = array('id'=>$user_id);
        
        $user = $this->mdb->get('user', ['id','user_login','password','user_nickname'], $map);
        
        $jwt=$this->createJwt($user['id'], $user['user_nickname']);
        $res=array(
            'access_token'  => $jwt,
            'user_id'       =>$user['id'],
            'account'       =>$user['user_login'],
            'password'      =>$user['password']
            // 'easemob' =>$hxResult
        );
        $this->ec($res);
    }
} else {
    $this->ec('请输入正确的平台号');
}
