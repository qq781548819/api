<?php

use \GatewayWorker\Lib\Gateway;

$_SESSION['uid'];




$order = MDB()->get('consume', '*', ['order_sn'=> $_SESSION['chat_order_sn']]);


