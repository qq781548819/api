<?php

// echo('哈哈哈哈');
// $user =model('user')->where('mobile', '15119120669')
// ->find()->getData();

// var_dump($user);
// MDB()->pdo->beginTransaction();
 
$info = MDB()->select("user",'*', [
    "id" => "100000"
]);
 
var_dump($info);

/* Commit the changes */
// MDB()->pdo->commit();
 
// /* Recognize mistake and roll back changes */
// MDB()->pdo->rollBack();
