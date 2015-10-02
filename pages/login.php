<?php

include "root.inc";
require_once "$ROOT/common.php";

$user = User::GetCurrentUser();

if( $user != NULL )
    return; // TODO: put error message saying they're already logged in

if(!$_POST)
{
	// Display the page.
	$conditionalVariables['login_display_error'] = 'none';
}
else
{
	// Log in and set the current user as this user.
	$username = $_POST['username'];
	$password = $_POST['password'];
	
	// Create a User object
	$user = User::FromCredentials( $username, $password );
	
	if( $user )
	{
        session_start();
		// Set this user as the current user.
		$user->SetAsCurrentUser();
        return;
	}
	else
	{
		// Display the page, with an error message
		$conditionalVariables['login_display_error'] = 'block';
	}
}

$page = new Page( "login_body", $conditionalVariables, "Log In", "account_login" );
echo $page->Display();

?>
