<?php
$uid    =   intval($_GET['uid']);
$channelid  =   intval($_GET['channelid']);
passthru('python ./python/sample/sample.py '.$uid.' '.$channelid.' '.$var_3);

python代码（从官方给的实例修改而来）
import sys
import os
import time
from random import randint

sys.path.append(os.path.join(os.path.dirname(__file__), '../src'))
from DynamicKey4 import generateRecordingKey
from DynamicKey4 import generateMediaChannelKey

#statickey   = "970ca35de60c44645bbae8a215061b33"
#signkey     = "5cfd2fd1755d40ecb72977518be15d3b"
#channelname = "7d72365eb983485397e3e3f9d460bdda"

statickey   = "cc9f2cc9b46f457597e08e91d7c39f79"
signkey     = "a486170cf01546ecab0a3e5cb53c5026"
channelname = int(sys.argv[1])
uid = int(sys.argv[2])

unixts = int(time.time());

randomint = -2147483647
expiredts = 0

#print "%.8x" % (randomint & 0xFFFFFFFF)

if __name__ == "__main__":
    print generateRecordingKey(statickey, signkey, channelname, unixts, randomint, uid, expiredts)
    #print generateMediaChannelKey(statickey, signkey, channelname, unixts, randomint, uid, expiredts)

?>