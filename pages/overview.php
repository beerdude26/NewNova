<?php

include "root.inc";
require_once "$ROOT/common.php";

if(!$_POST)
{
	// Display the page.
	$page = new Page( "login_body", NULL, "Empire Overview", "account_login" );
	echo $page->Display();
}

?>
