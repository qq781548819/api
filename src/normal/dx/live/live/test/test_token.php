<?php

include "../src/SignalingToken.php";

$appID = 'b67a8fec08c74a3eac01741836ef8ee0';
$appCertificate = '977d3b89bb454ad69c19287f84287ddd';

$account = time();
$validTimeInSeconds = time();

$res = getToken($appID,$appCertificate,$account, $validTimeInSeconds);

var_dump($res);