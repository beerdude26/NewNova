<?php
include "root.inc";
require_once "$ROOT/common.php";

if(!$_POST)
{
	// Display the page.
	$conditionalVariables['login_display_error'] = 'none';
}
else
{
    // Register a new user with form data
	$username = Database::Instance()->EscapeString( $_POST['username'] );
	$password = Database::Instance()->EscapeString( $_POST['password'] );
	$email = Database::Instance()->EscapeString( $_POST['email'] );
	$secondaryEmail = Database::Instance()->EscapeString( $_POST['secondary_email'] );
    if( $secondaryEmail == "" )
        $secondaryEmail = $email;

	$planetName = Database::Instance()->EscapeString( $_POST['planet_name'] );
    if( $planetName == "" )
        $planetName = "Colony"; // TODO: localize this?
    
    // Create new user, is automatically added to database
    $user = User::NewUser( $username, $password, 0, $email, $secondaryEmail, $planetName );

	if( $user )
	{
		// Set this user as the current user.
		$user->SetAsCurrentUser();
        $page = new Page( "registration_complete", NULL, "Registration Complete", "account_activation" );
		echo $page->Display();
        return;
	}
	else
	{
		// Display the page, with an error message
		$conditionalVariables['login_display_error'] = 'block';
	}
}

$extraScripts = Helper::InsertValidation( "../" );
$page = new Page( "registration", $conditionalVariables, "Register new account", "account_activation", $extraScripts );
echo $page->Display();

?>
