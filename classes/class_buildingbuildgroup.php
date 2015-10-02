<?php

//Dependencies
require_once "class_buildgroup.php";

// A group of resources that are to built.
// IMPORTANT: These resources must be of the same type (same range of database-IDs).
class BuildingBuildGroup extends BuildGroup
{
    private $_commissionedTime = 0;

    public function BuildingBuildGroup( array $group, Colony $colony ) 
    {
        parent::BuildGroup( $group, $colony );
        $this->CommissionedTime( BuildingBuildGroup::GetCommissionedTime() );
    }
    
    public function CommissionedTime( $value = "empty" )
	{
		if( $value === "empty" )
			return $this->_commissionedTime;
		else
        {
            $this->_commissionedTime = $value;
            $this->_hasChanged = true;
        }
	}
    
    public static function FromList( array $resources, Colony $colony, array $listPositions = NULL, $forDeletion = false )
    {    
        $members = array();
        $positionCounter = 0;
        $positionInList = 0;

        // Determine the list positions.
        if( $listPositions == NULL ) // We didn't receive an overriding array.
        {
            $positionInList += BuildingBuildGroup::GetLastBuildListPosition( $colony );
            $positionInList++; // If there are other constructions preceding this, offset by 1.
        }
        else // We DID receive an overriding array, let's use those numbers instead!
            $positionInList = $listPositions[0];

        // Construct list of members
        foreach( $resources as $itemName )
        {
            $itemObject = clone ResourceParser::Instance()->GetItemByName( $itemName );
            $itemObject->Amount( 1 );
            
            if( $listPositions != NULL )
            {
                // Override the list position with the given array.
                $positionInList = $listPositions[$positionCounter];
                $positionCounter++;
            }
            
            $members[$positionInList] =& BuildingBuildItem::FromIDResource( $itemObject, $positionInList, $positionInList, 0, 1, NULL );
            $positionInList++; 
        }
        
        $bg = new BuildingBuildGroup( $members, $colony );
        BuildGroup::LinkMembersToGroup( $bg );
        
        // If this BuildGroup is being built up so we can deduct it from another BuildGroup,
        // we don't calculate all the build times, because they won't be used.
        if( !$forDeletion ) 
        {
            $bg->FillItemsInDatabase(); // Pretty damn crucial for correct calculation of buildLevels!
            $bg->UpdateLevels();
            $bg->UpdateBuildTimes();
        }
        
        return $bg;
    }

    public static function FromDatabase( array $buildData, Colony $colony, $updateBuildTimes = true )
    {
        $members = array();

        // Build a list of BuildItems
        foreach( $buildData as $row )
        {
            $itemName = ResourceParser::Instance()->GetItemNameByID( $row['resource_type_being_built'] );
            
            $itemObject = clone ResourceParser::Instance()->GetItemByName( $itemName );
            $itemObject->Amount( 1 );
            $positionInList = $row['build_list_position'];
            $level = $row['level'];
            
            $members[$positionInList] =& BuildingBuildItem::FromIDResource( $itemObject, $positionInList, $positionInList, 0, $level, NULL );
        }

        $bg = new BuildingBuildGroup( $members, $colony );
        if( $updateBuildTimes )
            $bg->UpdateBuildTimes();
        return $bg;
    }

    public function Type()
    {
        return "BUILDING";
    }
    
    public function FillItemsInDatabase()
    {
        $c = $this->Colony();
        $this->ItemsInDatabase( ResourceBuilder::GetBuildingListOfColony( $c )->BuildList() );
    }
    
    public function TotalCost()
    {
        $totalCost = new Cost();
        foreach( $this->Members() as $unit )
            $totalCost->AddCost( $unit->BuildCost() );
            
        return $totalCost;
    }
    
    public function UpdateLevels()
    {
        foreach( $this->Members() as $unit )
            $unit->UpdateLevel();
    }
    
    public function UpdateBuildTimes()
    {
        $offset = 0;
        
        // Update build times of each item.
        foreach( $this->Members() as $unit )
        {
            $unit->BuildGroup( $this );
            $offset = $unit->CalculateScheduledTime( $offset );
        }
    }
    
    public function GetHighestLevelForItem( $itemName )
    {
        $level = 0;
        foreach( $this->Members() as $item )
            if( $item->Name() === $itemName && $item->Level() > $level )
                $level = $item->Level();
                
        return $level;
    }
    
    // Only call this after having updated all items in the BuildGroup.
    public function AddToDatabase()
    {
        // Get Colony ID
        $colonyID = $this->Colony()->ID();
        if( $colonyID === NULL )
            throw new Exception("The colony from this BuildList has not yet been inserted into the database!");

        // We'll want to keep track of how many fields we'll use up
        $usedFields = 0;
        
        // Build up query
        $query = "INSERT INTO production_building VALUES ";
        foreach( $this->Members() as $item)
        {
            // Check if we're getting a correct object
            Helper::checkType( $this, $item, "BuildingBuildItem" );
            
            $positionInList = $item->PositionInList();
            $itemID = $item->ID();
            $level = $item->Level();

            $query .= "( $colonyID, $itemID, $level, $positionInList ), ";

            $usedFields ++;
        }

        if( $query != "" )
        {
            // Lop off the last comma, replace with semicolon
            $query = substr( $query, 0, strlen($query) - 2 );
            $query .= ";";
            Database::Instance()->ExecuteQuery( $query, "INSERT" );
            
            // Update UsedFields
            $colonyUsedFields = $this->Colony()->UsedFields();
            $this->Colony()->UsedFields( $colonyUsedFields + $usedFields );
            $itemsToBeUpdated = array( "used_build_fields" );
            $this->Colony()->UpdateDatabaseProperties( $itemsToBeUpdated );
        }
    }
    
    // Only call this after having updated all items in the BuildGroup.
    public function UpdateDatabase()
    {
        $colonyID = $this->Colony()->ID();
        $query = "";

        // Iterate over the build list
        foreach( $this->Members() as $item )
        {
            // Check if we're getting a correct object
            Helper::checkType( $this, $item, "BuildItem" );

            if( $item->Amount() < 1 ) // Well the row can just be removed, really
            {
                $this->DeleteItemFromDatabase( $item );
                continue;
            }
                
            $newPositionInList = $item->PositionInList();
            $oldPositionInList = $item->OldPositionInList();
            $itemID = $item->ID();
            $level = $item->Level();
                
            $query .= "UPDATE production_building SET build_list_position = $newPositionInList, level = $level ";
            $query .= "WHERE colonyID = $colonyID AND resource_type_being_built = $itemID AND build_list_position = $oldPositionInList;";
        }
        
        if( $query != "" )
        {
            // Lop off the last semicolon because otherwise you get an SQL error
            $fixedQuery = substr( $query, 0, strlen($query) - 1 );
            Database::Instance()->ExecuteQuery( $fixedQuery, "MULTI" );
        }
    }
    
    public function DeleteItemFromDatabase( BuildingBuildItem $item )
    {
        $colonyID = $this->Colony()->ID();
        $type = $item->ID();
        $pos = $item->OldPositionInList();
        $query = "DELETE FROM production_building WHERE colonyID = $colonyID AND resource_type_being_built = $type AND build_list_position = $pos;";
        Database::Instance()->ExecuteQuery( $query, "DElETE" );
        
        // Update UsedFields
        $colonyUsedFields = $this->Colony()->UsedFields();
        $this->Colony()->UsedFields( $colonyUsedFields - 1 );
        $itemsToBeUpdated = array( "used_build_fields" );
        $this->Colony()->UpdateDatabaseProperties( $itemsToBeUpdated );
    }
    
    public function GetCommissionedTime()
    {
        $colonyID = $this->Colony()->ID();
        $query = "SELECT current_building_scheduled_at FROM colony_properties WHERE colonyID = $colonyID;";
        $result = Database::Instance()->ExecuteQuery( $query, "SELECT" );
        $this->CommissionedTime( $result['current_building_scheduled_at'] );
        return $result['current_building_scheduled_at'];
    }
    
    // Updates the 'current_building_scheduled_at' row in the colony_properties table
    public function UpdateCommissionedTime()
    {
        $colonyID = $this->Colony()->ID();
        $seconds = time();
        $query = "UPDATE colony_properties SET current_building_scheduled_at = $seconds WHERE colonyID = $colonyID;";
        $this->CommissionedTime( $seconds );
        return Database::Instance()->ExecuteQuery( $query, "UPDATE" );
    }
    
    // Given an array of the same size as the instance's members, reorders the BuildingBuildGroup
    public function Reorder( array $newPositions )
    {
        $lengthInput = count( $newPositions );
        $lengthMembers = count( $this->Members() );
        
        if( $lengthInput === $lengthMembers )
        {
            $members =& $this->Members();
            foreach( $newPositions as $couple )
            {
                $position = explode( "-", $couple );
                $newPosition = $position[0];
                $oldPosition = $position[1];
                
                $members[$oldPosition]->PositionInList( $newPosition );
            }
        }
    }
    
    public static function GetLastBuildListPosition( Colony $c )
    {
        $id = $c->ID();
        $query = "SELECT MAX(build_list_position) AS pos FROM production_building WHERE colonyID = $id";
        $maxPos = Database::Instance()->ExecuteQuery( $query, "SELECT" );
        
        if( $maxPos == NULL )
            return 0;
        else
            return $maxPos['pos'];
    }
    
    public static function GetListCount( Colony $c )
    {
        $id = $c->ID();
        $query = "SELECT COUNT(colonyID) as count FROM production_building WHERE colonyID = $id;";
        $result = Database::Instance()->ExecuteQuery( $query, "SELECT" );
                
        if( $result == NULL )
            $count = 0;
        else
            $count = $result['count'];
    }

    public function __toString()
    {
        $header = "BuildingBuildGroup\n";
        $colony = "Colony: ".$this->Colony()->Name()." (".$this->Colony()->ID().")";
        $members = "\nMembers:\n";
        
        foreach( $this->Members() as $key => $member )
            $members .= "[KEY ".$key."] ".$member->__toString()."\n";
        return "<pre>".$header.$colony.$members."</pre>";
    }
}

?>