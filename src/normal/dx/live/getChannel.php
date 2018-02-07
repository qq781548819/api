<?php
include __DIR__.'/live/src/DynamicKey5.php';

$channelName = $this->get('channelName');

$appID = 'b67a8fec08c74a3eac01741836ef8ee0';
$appCertificate = '977d3b89bb454ad69c19287f84287ddd';

//AAAoADQ3NkQzMkNFOEU0Rjg2MzAxOTk1QkFFQzQ2NDE0M0VFQ0Y3QkY0RkIQALZ6j+wIx0o+rAF0GDbvjuAAAAAA9buDAwAAAAAAAA=="
//AAAoADg1RDQ3QTU4MDkxN0JDMzAyMkUwQTg4MTk5N0VEMjgwNzI3MDhDMjYQALZ6j+wIx0o+rAF0GDbvjuBgEVta9buDAwAAAAAAAA==
//AAAoADg1RDQ3QTU4MDkxN0JDMzAyMkUwQTg4MTk5N0VEMjgwNzI3MDhDMjYQALZ6j+wIx0o+rAF0GDbvjuBgEVta9buDAwAAAAAAAA==

//$channelName = "7d72365eb983485397e3e3f9d460bdda";
$ts = time();
$ts = 1515917664;

$randomInt = 58964981;
$uid = 0;
$expiredTs = 0;

$actual = testMediaChannelKey($appID, $appCertificate, $channelName, $ts, $randomInt, $uid, $expiredTs);

$this->ec(['channelKey'=>$actual]);


function testMediaChannelKey($appID, $appCertificate, $channelName, $ts, $randomInt, $uid, $expiredTs)
{
    $actual = generateMediaChannelKey($appID, $appCertificate, $channelName, $ts, $randomInt, $uid, $expiredTs);

    return $actual;
}

