<?php

include __DIR__."/live/src/SignalingToken.php";

$appID = 'b67a8fec08c74a3eac01741836ef8ee0';
$appCertificate = '977d3b89bb454ad69c19287f84287ddd';

$account = time();
$validTimeInSeconds = time()+86400;

$res = getToken($appID,$appCertificate,$account, $validTimeInSeconds);

$this->ec(['token'=>$res]);