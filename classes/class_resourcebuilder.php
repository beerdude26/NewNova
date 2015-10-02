<?php

//Dependencies
require_once "class_resource.php";

// A class that handles the building of Resources.
class ResourceBuilder
{
    private $_resourcesToBeCreated = NULL;  // BuildGroup.
    
    public function ResourceBuilder( BuildGroup $list ) 
    {
        $this->BuildList( $list );
    }
    
    public static function GetMissileListOfColony( Colony $colony, $readyToBeBuilt = false )
    {
        global $NN_config;
        $IDrangeFrom = $NN_config["missile_id_range"]["from"];
        $IDrangeTo = $NN_config["missile_id_range"]["to"];
        
        return ResourceBuilder::GetBuildListBy( $colony, $IDrangeFrom, $IDrangeTo, $readyToBeBuilt );
    }
    
    public static function GetDefenseListOfColony( Colony $colony, $readyToBeBuilt = false )
    {
        global $NN_config;
        $IDrangeFrom = $NN_config["defense_id_range"]["from"];
        $IDrangeTo = $NN_config["defense_id_range"]["to"];
        
        return ResourceBuilder::GetBuildListBy( $colony, $IDrangeFrom, $IDrangeTo, $readyToBeBuilt );
    }

    public static function GetShipListOfColony( Colony $colony, $readyToBeBuilt = false )
    {
        global $NN_config;
        $IDrangeFrom = $NN_config["ship_id_range"]["from"];
        $IDrangeTo = $NN_config["ship_id_range"]["to"];
        
        return ResourceBuilder::GetBuildListBy( $colony, $IDrangeFrom, $IDrangeTo, $readyToBeBuilt );
    }
    
    public static function GetBuildingListOfColony( Colony $colony, $readyToBeBuilt = false )
    {
        global $NN_config;
        $IDrangeFrom = $NN_config["building_id_range"]["from"];
        $IDrangeTo = $NN_config["building_id_range"]["to"];
        
        return ResourceBuilder::GetBuildListBy( $colony, $IDrangeFrom, $IDrangeTo, $readyToBeBuilt, "production_building" );
    }
    
    private static function GetBuildListBy( $colony, $IDrangeFrom, $IDrangeTo, $readyToBeBuilt = false, $table = "production" )
    {
        // Get building list
        $colonyID = $colony->ID();
        $query = "SELECT * FROM $table WHERE colonyID = $colonyID AND resource_type_being_built BETWEEN $IDrangeFrom AND $IDrangeTo";
        
        if( $readyToBeBuilt )
        {
            $time = time();
            $query .= " AND $time > scheduled_time";
        }
        
        $query .= " ORDER BY build_list_position ASC;";
        
        $buildData = Database::Instance()->ExecuteQuery( $query, "SELECT" );

        // BuildGroup always expects an array of arrays, so if we get NULL or a single array, we need to make an array of arrays 
        if( $buildData === NULL )
            $buildData = array();
            
        if( isset( $buildData["colonyID"] ) )
            $buildData = array( $buildData );
            
        if( $table === "production_building" )
            $buildGroup = BuildingBuildGroup::FromDatabase( $buildData, $colony );
        else
            $buildGroup = BuildGroup::FromDatabase( $buildData, $colony );
        
        return new ResourceBuilder( $buildGroup );
    }
    
    public function BuildList( $value = "" )
	{
		if( empty( $value ) )
			return $this->_resourcesToBeCreated;
		else
        {
            Helper::checkType( $this, $value, "BuildGroup" );
            $this->_resourcesToBeCreated =& $value;
        }
	}
    
    // Only call this after having updated all items in the BuildGroup.
    public function AddToDatabase()
    {
        // Get Colony ID
        $colonyID = $this->BuildList()->Colony()->ID();
        if( $colonyID === NULL )
            throw new Exception("The colony from this BuildList has not yet been inserted into the database!");
        
        // Get current time
        $currentTime = time();
        
        // We'll want to keep track of how many fields we'll use up
        $usedFields = 0;
        
        // Build up query
        $query = "INSERT INTO production VALUES ";
        foreach( $this->BuildList()->Members() as $item)
        {
            // Check if we're getting a correct object
            Helper::checkType( $this, $item, "BuildItem" );
            
            $itemName = $item->Name();
            $amountRequested = $item->Amount();
            $positionInList = $item->PositionInList();
            $itemID = $item->ID();
            $scheduledTime = $item->FirstBuildTime() + $currentTime;
            
            $currentTime += $scheduledTime;
            
            $query .= "( $colonyID, $itemID, $amountRequested, $positionInList, $scheduledTime ), ";
            
            $usedFields += $amountRequested;
        }

        if( $query != "" )
        {
            // Lop off the last comma, replace with semicolon
            $query = substr( $query, 0, strlen($query) - 2 );
            $query .= ";";
            Database::Instance()->ExecuteQuery( $query, "INSERT" );
            
            // Update UsedFields
            $colonyUsedFields = $this->BuildList()->Colony()->UsedFields();
            $this->BuildList()->Colony()->UsedFields( $colonyUsedFields + $usedFields );
            $itemsToBeUpdated = array( "used_build_fields" );
            $this->BuildList()->Colony()->UpdateDatabaseProperties( $itemsToBeUpdated );
        }
    }
    
    // Only call this after having updated all items in the BuildGroup.
    public function UpdateDatabase()
    {
        $colonyID = $this->BuildList()->Colony()->ID();
        $query = "";
        
        // We'll want to keep track of how many fields we'll use up
        $usedFields = 0;
        
        // Get current time
        $currentTime = time();
        
        // Iterate over the build list and only update the items that were changed
        foreach( $this->BuildList()->Members() as $item )
        {
            // Check if we're getting a correct object
            Helper::checkType( $this, $item, "BuildItem" );
        
            $itemName = $item->Name();
            $amountRequested = $item->Amount();
                
            // TODO: uncommented for testing purposes
            if( $amountRequested === 0 ) // Well the row can just be removed, really
            {
                $this->BuildList()->DeleteItemFromDatabase( $item );
                continue;
            }
                
            $usedFields += $amountRequested;
                
            $newPositionInList = $item->PositionInList();
            $oldPositionInList = $item->OldPositionInList();
            $itemID = $item->ID();
            $scheduledTime = $item->FirstBuildTime() + $currentTime;
                
            $query .= "UPDATE production SET amount_requested = $amountRequested, build_list_position = $newPositionInList, scheduled_time = $scheduledTime ";
            $query .= "WHERE colonyID = $colonyID AND resource_type_being_built = $itemID AND build_list_position = $oldPositionInList;";
        }
        
        if( $query != "" )
        {
            // Lop off the last semicolon because otherwise you get an SQL error
            $fixedQuery = substr( $query, 0, strlen($query) - 1 );
            Database::Instance()->ExecuteQuery( $fixedQuery, "MULTI" );
            
            // Update UsedFields
            $colonyUsedFields = $this->BuildList()->Colony()->UsedFields();
            $this->BuildList()->Colony()->UsedFields( $colonyUsedFields + $usedFields );
            $itemsToBeUpdated = array( "used_build_fields" );
            $this->BuildList()->Colony()->UpdateDatabaseProperties( $itemsToBeUpdated );
        }
    }
    
    public function DeleteItemFromDatabase( BuildItem $item )
    {
        $colonyID = $this->BuildList()->Colony()->ID();
        $type = $item->ID();
        $pos = $item->OldPositionInList();
        $query = "DELETE FROM production WHERE colonyID = $colonyID AND resource_type_being_built = $type AND build_list_position = $pos;";
		$result = Database::Instance()->ExecuteQuery( $query, "DElETE" );
    }
    
    public function DeleteAllFromDatabase()
    {
        foreach( $this->BuildList()->Members() as $unit )
            DeleteItemFromDatabase( $unit );
    }
    
    public function __toString()
    {
        $header = "ResourceBuilder\n";
        $colony = "BuildGroup:\n".$this->BuildList()->__toString();
        return "<pre>".$header.$colony."</pre>";
    }
    
}

?>