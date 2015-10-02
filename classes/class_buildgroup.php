<?php

//Dependencies
require_once "class_cost.php";
require_once "class_resource.php";
require_once "class_buildingbuilditem.php";
require_once "class_buildingbuildgroup.php";
require_once "class_resourcegroup.php";

// A group of resources that are to built.
// IMPORTANT: These resources must be of the same type (same range of database-IDs).
class BuildGroup extends ResourceGroup
{
    private $_colony = NULL;            // The colony that commissioned this build list.
    private $_itemsInDatabase = NULL;    // Items in the database of the same type. Used to calculate build levels and such.

    public function BuildGroup( array $group, Colony $colony ) 
    {
        $this->Members( $group );
        $this->Colony( $colony );
    }

    // Given a list of IDResource string names and a list of amounts, returns a BuildGroup
    // Other functions just take a list because all members are supposed to be unique there,
    // but here, you can have multiple elements of the same type.
    // IMPORTANT: These resources must be of the same type (same range of database-IDs).
    public static function FromList( array $resources, array $amounts, Colony $colony, array $listPositions = NULL, $forDeletion = false )
    {    
        $members = array();
        $positionInList = 0;
        $amountCounter = 0;

        // Determine the list positions.
        if( $listPositions == NULL ) // We didn't receive an overriding array.
        {
            // Get the offset of build list positions
            $firstItem = ResourceParser::Instance()->GetItemByName( $resources[0] );
            if( $firstItem->IsBuilding() )
            {
                $scheduledUnitData = BuildGroup::GetLastBuildListPosition( $colony );
                $positionInList += $scheduledUnitData;
            }
            else
            {
                $scheduledUnitData = BuildGroup::ShipyardIsBusyUntil();
                $positionInList += $scheduledUnitData["build_list_position"];
            }
        
            $positionInList++; // If there are other constructions preceding this, offset by 1.
            
        }
        else // We DID receive an overriding array, let's use those numbers instead!
        {
            $positionCounter = 0;
            $positionInList = $listPositions[$positionCounter];
        }

        // Construct list of members
        foreach( $resources as $itemName )
        {
            $itemObject = clone ResourceParser::Instance()->GetItemByName( $itemName );
            
            // the 'false' boolean means "Don't inform the BuildGroup of a change" because we'll manually calculate build times in a second
            $itemObject->Amount( $amounts[$amountCounter], false ); 
            $amountCounter++;
            
            if( $listPositions != NULL )
            {
                $positionInList = $listPositions[$positionCounter];
                $positionCounter++;
            }
            
            if( $itemObject->IsBuilding() )
                $members[$positionInList] =& BuildingBuildItem::FromIDResource( $itemObject, $positionInList, $positionInList, 0, 1, NULL );
            else
                $members[$positionInList] =& BuildItem::FromIDResource( $itemObject, $positionInList, $positionInList, 0, NULL );

            $positionInList++; 
        }
        
        if( $itemObject->IsBuilding() )
            $rg = new BuildingBuildGroup( $members, $colony );
        else
            $rg = new BuildGroup( $members, $colony );
            
        foreach( $rg->Members() as $unit )
            $unit->BuildGroup( $rg );
        
        if( !$forDeletion )
        {
            $rg->FillItemsInDatabase(); // Pretty damn crucial for correct calculation of buildLevels!
            $rg->UpdateBuildTimes();
        }
        return $rg;
    }
    
    public static function FromDatabase( array $buildData, Colony $colony )
    {
        $members = array();

        // Build a list of BuildItems
        foreach( $buildData as $row )
        {
            $itemName = ResourceParser::Instance()->GetItemNameByID( $row['resource_type_being_built'] );
            $itemObject = clone ResourceParser::Instance()->GetItemByName( $itemName );
            $itemObject->Amount( $row['amount_requested'], false );
            $positionInList = $row['build_list_position'];
            
            $members[$positionInList] =& BuildItem::FromIDResource( $itemObject, $positionInList, $positionInList, 0, NULL );
        }

        $rg = new BuildGroup( $members, $colony );
        $rg->UpdateBuildTimes();
        return $rg;
    }

    public function Type()
    {
        // We infer the type of the entire BuildGroup from the first item in the list.
        $firstItem = reset( $this->Members() );
        
        if( $this->Members() == NULL ) // If it's empty, we can't check much.
            return "EMPTY";

        Helper::checkType( $this, $firstItem, "BuildItem" ); 
        
        global $NN_config;
        $types = array( "building_id_range" => "BUILDING", "ship_id_range" => "SHIP", "defense_id_range" => "DEFENSE", "missile_id_range" => "MISSILE" );
        
        foreach( $types as $rangeName => $type )
            if( $firstItem->ID() >= $NN_config[$rangeName]["from"] && $firstItem->ID() <= $NN_config[$rangeName]["to"] )
                return $type;
    }
    
    // Don't call this in FromDatabase!
    public function FillItemsInDatabase()
    {
        $c = $this->Colony();
        
        if( $this->Type() === "BUILDING" )
            $rb = ResourceBuilder::GetBuildingListOfColony( $c );
        elseif( $this->Type() === "SHIP" )
            $rb = ResourceBuilder::GetShipListOfColony( $c );
        elseif( $this->Type() === "MISSILE" )
            $rb = ResourceBuilder::GetMissileListOfColony( $c );
        else
            $rb = ResourceBuilder::GetDefenseListOfColony( $c );
            
        $this->ItemsInDatabase( $rb->BuildList() );
    }
    
    public function ItemsInDatabase( $value = "empty" )
	{
		if( $value === "empty" )
            return $this->_itemsInDatabase;
		else
            $this->_itemsInDatabase = $value;
	}

    public function Colony( $value = "" )
	{
		if( empty( $value ) )
			return $this->_colony;
		else
        {
            Helper::checkType( $this, $value, "Colony" );
            $this->_colony =& $value;
        }
	}
    
    // Calculates the total reimbursed costs if you were to deduct this BuildGroup from the database.
    public function ReimbursedCosts()
    {
        $totalCost = new Cost();
        
        foreach( $this->Members() as $item )
        {
            if( $this->Type() === "BUILDING" ) // Buildings need a build cost, which is based on levels.
            {
                $itemObject =& $item->CastToIDResource();
                $buildLevelsOnColony = $itemObject->GetBuildLevelOnColony( $this->Colony() );
                $buildLevelsInDatabase = $item->ScheduledLevelsBeforeThis();
            
                // Sum up all reimbursed costs
                for( $reimbursedLevels = $item->Amount(); $reimbursedLevels > 0; $reimbursedLevels-- )
                {
                    $totalBuildLevel = $buildLevelsOnColony + $reimbursedLevels + $buildLevelsInDatabase;
                    $buildCosts = $item->BuildCost( $itemObject, $totalBuildLevel );
                    $totalCost->AddCost( $buildCosts );
                }
            }
            else
                $totalCost->AddCost( $item->BuildCost( $itemObject, $item->Amount() ) );
        }
        
        return $totalCost;
    }
    
    public function TotalCost()
    {
        $totalCost = new Cost();
        foreach( $this->Members() as $unit )
            $totalCost->AddCost( $unit->BuildCost() );
    }

    public static function ShipyardIsBusyUntil()
    {
        global $NN_config;
        $sFrom = $NN_config["ship_id_range"]["from"];
        $sTo = $NN_config["ship_id_range"]["to"];
        $dFrom = $NN_config["defense_id_range"]["from"];
        $dTo = $NN_config["defense_id_range"]["to"];
        $mFrom = $NN_config["missile_id_range"]["from"];
        $mTo = $NN_config["missile_id_range"]["to"];
        
        $fromClause = "FROM production WHERE (resource_type_being_built BETWEEN $sFrom AND $sTo OR 
                                          resource_type_being_built BETWEEN $dFrom AND $dTo OR 
                                          resource_type_being_built BETWEEN $mFrom AND $mTo)";
                                          
        $query = "SELECT MAX(build_list_position) AS pos ".$fromClause.";";
        $maxPos = Database::Instance()->ExecuteQuery( $query, "SELECT" );
        if( isset( $maxPos['pos'] ) )
            $maxPos = $maxPos['pos'];
        else
            return array("build_list_position" => 0, "scheduled_time" => 0);
        
        // TODO: simplify this with an efficient subquery                      
        $query = "SELECT build_list_position, scheduled_time ".$fromClause." AND $maxPos = build_list_position LIMIT 1;";
        $result = Database::Instance()->ExecuteQuery( $query, "SELECT" );
        if( isset( $result['build_list_position'] ) )
            return $result;
        return array("build_list_position" => 0, "scheduled_time" => 0);
    }
    
    // Expects a BuildGroup whose members are indexed IN THE SAME WAY as this BuildGroup.
    public function DeductUnits( BuildGroup $bg )
    {
        Helper::containsNegative( $bg->Members() ); // You can't deduct negative units, of course
        
        if( !( ($this->Type() === $bg->Type()) || $bg->Type() === "EMPTY" || $this->Type() === "EMPTY" ) )
            throw new Exception("You can't combine different types of BuildGroups!");
    
        $otherList =& $bg->Members();
        foreach( $this->Members() as $index => $buildItem )
            if( isset( $otherList[$index] ) )
                $buildItem->Amount( $buildItem->Amount() - $otherList[$index]->Amount() );
                
        Helper::containsNegative( $this->Members() ); // You can't deduct more than is present, of course
    }
    
    // Returns boolean
    public function ContainsBuildItem( BuildItem $needle )
    {
        foreach( $this->Members() as $item )
            if( $item->ID() === $needle->ID() && $item->PositionInList() === $needle->PositionInList() 
                && $item->BuildGroup()->Colony()->Equals( $needle->BuildGroup()->Colony() ) )
                return true;
        return false;
    }
    
    public function UpdateBuildTimes()
    {
        $offset = 0;
        
        // Update build times of each item.
        foreach( $this->Members() as $unit )
        {
            $unit->UpdateLevel();
            $offset = $unit->CalculateScheduledTime( $offset );
        }
    }
    
    // TODO: see if this returns correct value
    public function BuildItemBefore( BuildItem $item )
    {
        $result = array_search( $item, $this->Members(), true );
        if( $result === false )
            return NULL;
        
        prev( $this->Members() );
        $result = current( $this->Members() );
        if( $result === false )
            return NULL;
            
        return $result;
    }
    
    public static function LinkMembersToGroup( BuildGroup $bg )
    {
        foreach( $bg->Members() as $unit )
            $unit->BuildGroup( $bg );
    }

    public function __toString()
    {
        $header = "BuildGroup\n";
        $colony = "Colony: ".$this->Colony()->Name()." (".$this->Colony()->ID().")";
        $members = "\nMembers:\n";
        
        foreach( $this->Members() as $key => $member )
            $members .= "[KEY ".$key."] ".$member->__toString()."\n";
        return "<pre>".$header.$colony.$members."</pre>";
    }
}

?>