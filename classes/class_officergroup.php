<?php

//Dependencies
require_once "class_officer.php";

class OfficerGroup
{
    private $_members = NULL;   // Array of Officers
    private $_owner = NULL;     // User object
    private $_changes = NULL;   // Array of booleans that defines what officers have changed

    private function OfficerGroup() {}
    
    public function Members( $value = "" )
	{
		if( empty( $value ) )
			return $this->_members;
		else
        {
            Helper::checkTypeList( $this, $value, "Officer" );
            $this->_members =& $value;
        }
	}
    
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
    
    public static function Generateofficers( User $user )
    {
        $list = ResourceParser::Instance()->Officers();
        $newOfficers = new OfficerGroup();
        $newOfficers->Members( $list );
        
        $changes = array();
        foreach( $list as $officer )
            $changes[$officer->Name()] = false;
        
        $newOfficers->Changes( $changes );
        $newOfficers->Owner( $user );
        return $newOfficers;
    }
    
    public function ChangeOfficer( $name, $amount )
    {
        $list =& $this->Members();
        $list[$name] = $amount;
        $this->SetChanged( $name, true );
    }
    
    private function SetChanged( $name, $value )
    {
        $this->_changes[$name] = (bool) $value;
    }
    
    public static function FromDatabase( array $row, User $user )
    {
        $members = array();
        $changes = array();
        $row = array_slice( $row, 4 ); // Slice off first four folumns
        foreach( $row as $rowName => $rowValue )
        {
            $splitName = explode( "_", $rowName ); // Name is of the type "X_level"
            $rowName = $splitName[0];
            
            $officerObject = clone ResourceParser::Instance()->GetOfficerByName( $rowName );
            $officerObject->Amount( $rowValue );
            $members[$rowName] = $officerObject;
            $changes[$rowName] = false;
        }
        
        $officerGroup = new OfficerGroup();
        $officerGroup->Members( $members );
        $officerGroup->Owner( $user );
        $officerGroup->Changes( $changes );
        return $officerGroup;
    }
    
    public function AddToDatabase()
    {
        $list = $this->Members();
        $userID = $this->Owner()->ID();
    
        $query = "INSERT INTO user_officers ";
        $query .= "(userID, points_available, mining_experience, raiding_experience, geologist_level, ";
        $query .= "admiral_level, engineer_level, technocrat_level, manufacturer_level, scientist_level, ";
        $query .= "defender_level, juggernaut_level, spy_level, commander_level, storekeeper_level, general_level, ";
        $query .= "raider_level, emperor_level) ";
        $query .= "VALUES( ".$userID.", 0, 0, 0, ";
        
        foreach( $list as $officer )
            $query .= $officer->Amount().", ";
            
        // Lop off the last comma
        $query = Helper::lop_off( $query, 2, ");" );
        
        Database::Instance()->ExecuteQuery( $query, "INSERT" );
    }

    public function UpdateDatabase()
    {
        $changeCounter = 0;
        $list = $this->Members();
        $changes = $this->Changes();
        $userID = $this->Owner()->ID();
        $changesNeeded = false;
        
        $query = "UPDATE user_officers SET ";
        foreach( $list as $officer )
        {
            if( $changes[$changeCounter] == true )
            {
                $query .= $officer->Name()."_level = ".$officer->Amount().", ";
                 $changesNeeded = true;
            }
            $changeCounter++;
        }
        
        if(  $changesNeeded )
        {
            // Lop off last comma
            $query = Helper::lop_off( $query, 2 );
            $query .= " WHERE userID = $userID";
        
            Database::Instance()->ExecuteQuery( $query, "UPDATE" );
        }
    }
   
    public function GetUniqueMembers()
    {
        $uniqueMembers = array();
        foreach( $this->_members as $item)
        {
            $itemName = $item->Name();
            if( !isset( $uniqueMembers[$itemName] ) )
                $uniqueMembers[$itemName] = $item;
        }
        
        return $uniqueMembers;
    }
    
    public function GetMemberByName( $itemName, $suppressError = false )
    {
        if( isset( $this->_members[$itemName] ) )
			return $this->_members[$itemName];
            
        if( !$suppressError )
            throw new Exception("$itemName was not found in the member list!");
        else
            return NULL;
    }
    
    public function TotalCost()
    {
        $totalCost = new Cost( 0, 0, 0, 0 );
		foreach( $this->Members() as $resource )
			$totalCost->AddCost( $resource->Cost() );
		
		return $totalCost;
    }
}

?>