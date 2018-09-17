<?php
    $redis = new Redis();
    $redis ->connect('127.0.0.1','6379');
    $redis ->auth('123456');

    #步骤3：选择数据库
    $redis->select(0);
    #步骤4：操作
    $rs = $redis->set('xx', '你好Redis');
    var_dump($rs);
    echo '<hr />';
    $rs = $redis->get('xx');
    var_dump($rs);