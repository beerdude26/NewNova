<?php

require_once 'root.inc';
require_once "class_officergroup.php";
require_once "class_technologygroup.php";

class User implements Comparable
{
	private $_ID;					// Database ID
	private $_username;				// Username
	private $_authorisationLevel;	// Authorisation level
	private $_authorisationLevelName;	// Authorisation level as string
	private $_primaryEmail;			// Primary e-mail
	private $_secondaryEmail;		// Secondary e-mail
	private $_registrationTime;		// Datestamp when the user registered
	private $_lastOnline;			// Datestamp when the user was last seen online
	private $_isBanned;				// Boolean to check if the user is banned
	private $_bannedUntil;			// Timestamp that defines until how long the user is banned
	
	private $_currentColony;		// Currently selected colony of the user
    private $_technologies;         // TechnologyGroup
    private $_officers;             // OfficerGroup
	
	private function User()
	{
		// Do nothing. This is defined because creation of a new user object should occur
		// via the static functions
	}
	
	public static function NewUser( $username, $plainPassword, $authlevel, $primaryEmail, $secondaryEmail = "alternate_email", $colony = "Colony" )
	{
		// Create the user and add him/her to the database
	
		$user = new User();
		
		$user->Username( $username );
		$user->AuthorisationLevel( $authlevel );
		$user->PrimaryEmail( $primaryEmail );
		$user->SecondaryEmail( $secondaryEmail );
		
		$currentTime = time();
		$user->RegistrationTime( $currentTime );
		$user->LastOnline( $currentTime );
		
		$user->IsBanned( FALSE );
		$user->BannedUntil( 0 );
		
		$userID = User::AddToDatabase( $user, $plainPassword );
		$user->ID( $userID );
		
		// Now that the user has been created, let's create a home colony for him or her
		$user->CurrentColony( Colony::CreateHomeBase( $user, $colony ) );
        
        // Instantiate user's technologies and officers, and add them to database
        $user->Officers( OfficerGroup::Generateofficers( $user ) );
        $user->Officers()->AddToDatabase();
        
        $user->Technologies( TechnologyGroup::GenerateTechs( $user ) );
        $user->Technologies()->AddToDatabase();
		
		return $user;
	}
	
	public static function FromCredentials( $userName, $plainPassword )
	{
		// Escape username
		$userName = Database::Instance()->EscapeString( $userName );

		// Load user data
		$userData = Database::Instance()->ExecuteQuery( "SELECT * FROM user WHERE username = '$userName'", "SELECT" );
		
		// Get the random salt value
		$random_salt_value = $userData['randomsalt'];
		$hashedPassword = sha1( $random_salt_value.$plainPassword );
		
		if( $hashedPassword == $userData['password'] )
			return User::FromDatabase( $userData );
		
		return NULL;
	}
    
    public static function GetOwnerOfColonyID( $id, Colony $c = NULL )
    {
        $query = "SELECT u.* FROM user AS u, colony AS c WHERE u.ID = c.userID AND c.ID = $id;";
        $result = Database::Instance()->ExecuteQuery( $query, "SELECT" );
        return User::FromDatabase( $result, $c );
    }
	
	public static function FromDatabase( $row, Colony $c = NULL )
	{
		$user = new User();
		
		$user->ID( $row['ID'] );
		$user->Username( $row['username'] );
		$user->AuthorisationLevel( Database::Instance()->ExecuteQuery("SELECT * FROM authorisation WHERE ID = ".(int)$row['authorisationID'].";","SELECT") );
		$user->PrimaryEmail( $row['primary_email'] );
		$user->SecondaryEmail( $row['secondary_email'] );
		$user->RegistrationTime( $row['registration_time'] );
		$user->LastOnline( $row['last_online'] );
		$user->IsBanned( $row['is_banned'] );
		$user->BannedUntil( $row['banned_until'] );
        
        // Load authorisation level name
        $levelNameRow = Database::Instance()->ExecuteQuery( "SELECT name FROM authorisation WHERE level = ".$user->AuthorisationLevel().";", "SELECT" );
        $user->AuthorisationLevelName( $levelNameRow['name'] );
		
		// Load home colony
        if( $c == NULL )
        {
            $colonyDatabaseRow = Database::Instance()->ExecuteQuery("SELECT * FROM colony WHERE userID = ".$user->ID()." AND is_home_colony = 1", "SELECT");
            $user->CurrentColony( Colony::FromDatabase( $colonyDatabaseRow, $user ) );
        }
        else
            $user->CurrentColony( $c );
		
        // Load user's technologies
        $technologyDatabaseRow = Database::Instance()->ExecuteQuery("SELECT * FROM user_technology WHERE userID = ".$user->ID(), "SELECT");
		$user->Technologies( TechnologyGroup::FromDatabase( $technologyDatabaseRow, $user ) );
        
        // Load user's officers
        $officerDatabaseRow = Database::Instance()->ExecuteQuery("SELECT * FROM user_officers WHERE userID = ".$user->ID(), "SELECT");
		$user->Officers( OfficerGroup::FromDatabase( $officerDatabaseRow, $user ) );
        
		// Return user
		return $user;
	}
	
	public static function AddToDatabase( $u, $plainPassword )
	{
		Helper::checkType( get_called_class(), $u, "User" );
		
		// Generate a 50-character random salt and salt the plain password with it
		$random_salt_value = substr(md5(uniqid(mt_rand(), true)), 0, 50);
		
		$hashedPassword = sha1( $random_salt_value.$plainPassword );

		$query = "INSERT INTO user ";
		$query .= "(username, password, randomsalt, authorisationID, primary_email, secondary_email, ";
		$query .= "registration_time, last_online, is_banned, banned_until) ";
		$query .= "VALUES('".$u->Username()."', '".$hashedPassword."', '".$random_salt_value."', ".$u->AuthorisationLevel();
		$query .= ", '".$u->PrimaryEmail()."', '".$u->SecondaryEmail()."', ".$u->RegistrationTime().", ".$u->LastOnline();
		$query .= ", ".$u->IsBanned().", ".$u->BannedUntil().");";
		
		return Database::Instance()->ExecuteQuery( $query, "INSERT" );
	}
	
	public function ID( $value = "" )
	{
		if( empty( $value ) )
			return $this->_ID;
		else
		{
			$this->_ID = $value;
		}
	}
	
	public function Username( $value = "" )
	{
		if( empty( $value ) )
			return $this->_username;
		else
		{
			$this->_username = $value;
		}
	}
	
	public function AuthorisationLevel( $value = "empty" )
	{
		if( $value === "empty" )
			return $this->_authorisationLevel;
		else
		{
			$this->_authorisationLevel = (int) $value;
		}
	}
    
    public function AuthorisationLevelName( $value = "__empty__" )
	{
		if( $value === "__empty__" )
			return $this->_authorisationLevelName;
		else
		{
			$this->_authorisationLevelName = (string) $value;
		}
	}
	
	public function PrimaryEmail( $value = "" )
	{
		if( empty( $value ) )
			return $this->_primaryEmail;
		else
		{
			$this->_primaryEmail = $value;
		}
	}
	
	public function SecondaryEmail( $value = "" )
	{
		if( empty( $value ) )
			return $this->_secondaryEmail;
		else
		{
			$this->_secondaryEmail = $value;
		}
	}
	
	public function RegistrationTime( $value = "" )
	{
		if( empty( $value ) )
			return $this->_registrationTime;
		else
		{
			$this->_registrationTime = $value;
		}
	}
	
	public function LastOnline( $value = "" )
	{
		if( empty( $value ) )
			return $this->_lastOnline;
		else
		{
			$this->_lastOnline = $value;
		}
	}
	
	public function IsBanned( $value = "empty" )
	{
		if( $value === "empty" )
			return $this->_isBanned ? 1 : 0;
		else
		{
			$this->_isBanned = (boolean)$value;
		}
	}
	
	public function BannedUntil( $value = "empty" )
	{
		if( $value === "empty" )
			return $this->_bannedUntil;
		else
		{
			$this->_bannedUntil = (int)$value;
		}
	}
    
    public function Technologies( $value = "" )
	{
		if( empty( $value ) )
			return $this->_technologies;
		else
		{
            Helper::checkTypeList( $this, $value, "TechnologyGroup" );
			$this->_technologies = $value;
		}
	}
    
    public function Officers( $value = "" )
	{
		if( empty( $value ) )
			return $this->_officers;
		else
		{
            Helper::checkTypeList( $this, $value, "OfficerGroup" );
			$this->_officers = $value;
		}
	}
	
	public function Language()
	{
		global $NN_config;
		
		// TODO: read preferred language from database
		return ResourceParser::Instance()->GetLanguageByName( $NN_config["default_language"] );
	}
	
	public function Skin()
	{
		global $NN_config;
		
		// TODO: read preferred skin from database
		return $NN_config["default_skin"];
	}
	
	public function CurrentColony( $value = "" )
	{
		if( empty( $value ) )
			return $this->_currentColony;
		else
		{
			$this->_currentColony =& $value;
		}
	}
	
	public function SetAsCurrentUser()
	{
		$_SESSION['NewNovaID'] = $this->_ID;
	}
	
	public static function GetCurrentUser()
	{
		if( isset( $_SESSION['NewNovaID'] ) )
		{
			$query = Database::Instance()->ExecuteQuery("SELECT * FROM user WHERE ID = ".$_SESSION['NewNovaID'],"SELECT");
			return User::FromDatabase( $query );
		}
		else
		{
			throw new Exception("NewNovaID wasn't set in the session variables, can't call User::GetCurrentUser!");
		}
	}
    
    // Interface methods
     public function Equals(self $other)
     {
        if( $this->ID() === $other->ID() )
            return true;
        return false;
     }
}
?>