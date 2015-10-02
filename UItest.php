<?php




$time1 = microtime(true);
require_once 'common.php';
$page = new Page( "registration", NULL, "Testing123", "account_activation" );
echo $page->Display();
$time2 = microtime(true);

echo ($time2-$time1);


?>