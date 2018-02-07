<?php


use GatewayClient\Gateway;

$client_id = $this->get('client_id');


Gateway::bindUid($client_id, "赖坤奇大哥哥1");

Gateway::sendToClient($client_id,"哈哈哈哈哈哈哈哈");