<?php

//Dependencies
require_once "class_resourcegroup.php";

// A group of technologies.
class TechnologyGroup extends ResourceGroup
{
    private $_owner = NULL;     // User object
    private $_changes = NULL;   // Array of booleans that defines what technologies have changed

    private function TechnologyGroup() {}
    
    public function Owner( $value = "" )
	{
		if( empty( $value ) )
			return $this->_owner;
		else
        {
            Helper::checkType( $this, $value, "User" );
			$this->_owner =& $value;
        }
	}
    
    public function Changes( $value = "" )
	{
		if( empty( $value ) )
			return $this->_changes;
		else
			$this->_changes = $value;
	}
    
    public static function GenerateTechs( User $user )
    {
        $list = ResourceParser::Instance()->Technologies();
        $newTechnologies = new TechnologyGroup();
        $newTechnologies->Members( $list );
        
        $changes = array();
        foreach( $list as $technology )
            $changes[$technology->Name()] = false;
        
        $newTechnologies->Changes( $changes );
        $newTechnologies->Owner( $user );
        return $newTechnologies;
    }
    
    public static function FromDatabase( array $row, User $user )
    {
        $members = array();
        $changes = array();
        $row = array_slice( $row, 1 ); // Slice off userID column
        foreach( $row as $rowName => $rowValue )
        {
            $techObject = clone ResourceParser::Instance()->GetTechnologyByName( $rowName );
            $techObject->Amount( $rowValue );
            $members[$rowName] = $techObject;
            $changes[$rowName] = false;
        }
        
        $technologyGroup = new TechnologyGroup();
        $technologyGroup->Members( $members );
        $technologyGroup->Owner( $user );
        $technologyGroup->Changes( $changes );
        return $technologyGroup;
    }
    
    public function ChangeTechnology( $name, $amount )
    {
        $list =& $this->Members();
        $list[$name]->Amount( $amount );
        $this->SetChanged( $name, true );
    }
    
    private function SetChanged( $name, $value )
    {
        $this->_changes[$name] = (bool) $value;
    }
    
    public function AddToDatabase()
    {
        $list = $this->Members();
        $userID = $this->Owner()->ID();
        
        $query = "INSERT INTO user_technology (userID, ";
        foreach( $list as $technology )
            $query .= $technology->Name().", ";
            
        // Lop off the last comma, replace with )
        $query = Helper::lop_off( $query, 2, ")" );
        
        $query .= "VALUES( $userID, ";
        foreach( $list as $technology )
            $query .= $technology->Amount().", ";
            
        $query = Helper::lop_off( $query, 2, ");" );
        
        Database::Instance()->ExecuteQuery( $query, "INSERT" );
    }
    
    // Expects a user and a list of booleans the size of all technologies
    public function UpdateDatabase()
    {
        $list = $this->Members();
        $userID = $this->Owner()->ID();
        $changes = $this->Changes();
        
        $changesNeeded = false;
        
        $query = "UPDATE user_technology SET ";
        foreach( $list as $technology )
            if( $changes[$technology->Name()] == true )
            {
                $query .= $technology->Name()." = ".$technology->Amount().", ";
                $changesNeeded = true;
            }
            
        if( $changesNeeded )
        {
            // Lop off last comma
            $query = Helper::lop_off( $query, 2 );
            $query .= " WHERE userID = $userID";
        
            Database::Instance()->ExecuteQuery( $query, "UPDATE" );
        }
    }
}

?>