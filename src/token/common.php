<?php
function cmf_password($client_secret)
{
    $result = "###" . md5(md5("XLOnzcjb2bMQfxu1Kg".$client_secret));        //thinkcmf对应编码
    return $result;
}
