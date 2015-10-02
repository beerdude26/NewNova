<?php

//Dependencies
require_once "class_builditem.php";

// A Resource that is to be built.
class BuildingBuildItem extends BuildItem
{
    private $_hasChanged = false;    // Has the Resource in question changed?
    private $_buildGroup = NULL;     // The BuildGroup this item is a part of.
    private $_positionInList = 0;    // Position of the requested Resource.
    private $_oldPositionInList = 0; // Old position of the requested Resource.
    private $_scheduledTime = 0;     // First build time of the item.
    
    private $_level = 0;

    public function BuildingBuildItem( $name, Cost $cost, $nextcostmodifier, $prerequisite, $amount, $dbID, $position, $oldPosition, $time, $level, BuildGroup $bg = NULL ) 
    {
        parent::IDResource( $name, $cost, $nextcostmodifier, $prerequisite, $amount, $dbID  );
        $this->PositionInList( $position );
        $this->OldPositionInList( $oldPosition, false);
        $this->ScheduledTime( $time );
        $this->Level( $level );
        $this->HasChanged( false );
        $this->BuildGroup( $bg );
    }
    
    public static function FromIDResource( IDResource $resource, $position, $oldPosition, $scheduledTime, $level, BuildGroup $bg = NULL )
    {
        return new BuildingBuildItem( $resource->Name(), $resource->Cost(), $resource->NextCostModifier(), $resource->Prerequisite(), $resource->Amount(),
                                      $resource->ID(), $position, $oldPosition, $scheduledTime, $level, $bg );
    }
    
    public function Level( $value = "empty" )
	{
		if( $value === "empty" )
			return $this->_level;
		else
        {
            $this->_level = $value;
            $this->_hasChanged = true;
        }
	}
    
    public function ScheduledTime( $value = "empty" )
	{
		if( $value === "empty" )
			return $this->_scheduledTime;
		else
        {
            $this->_scheduledTime = $value;
            $this->_hasChanged = true;
        }
	}

    // TODO: Don't forget to actually put this somewhere!
    public function CanBeConstructed()
    {
        if( !parent::CanBeConstructed() )
            return false;
        
        $fieldsLeft = true;
        
        // In case of a building, can't build if there aren't enough fields
        if( $this->IsBuilding() )
            if( $colony->FieldsLeft( $this ) == 0 )
                return false;
                
        // Can't have more than 1 shield dome
        if( ($this->Name() === "small_shield_dome" || $this->Name() === "large_shield_dome") )
        {
            switch( $this->Name() )
            {
                case "small_shield_dome":
                    $amountOfAlreadyBuiltDomes = $this->BuildGroup()->Colony()->Buildings()->GetMemberByName("small_shield_dome")->Amount();
                    break;
                case "large_shield_dome":
                    $amountOfAlreadyBuiltDomes = $this->BuildGroup()->Colony()->Buildings()->GetMemberByName("large_shield_dome")->Amount();
                    break;
            }
            $thisAmount = $this->Amount();
            if( $amountOfAlreadyBuiltDomes > 0 || $thisAmount > 0 )
                    return false;
        }
        
        // TODO: check for more requirements
        return true;
    }
    
    // Searches the database and BuildGroup for this item and sums up all scheduled levels.
    public function UpdateLevel()
    {
        // Search the BuildGroup itself
        $levels = $this->GetLevelsBeforeThisIn( $this->BuildGroup()->Members() );
        
        // Search the database list
        if( $this->BuildGroup()->ItemsInDatabase() != NULL ) // If NULL, the BuildGroup IS the database list, so we've already searched it.
            $levels += $this->GetLevelsBeforeThisIn( $this->BuildGroup()->ItemsInDatabase()->Members() );

        $this->Level( $levels );
        return $levels;
    }
    
    // Given an array of BuildItems, returns the sum of levels before (= earlier position) this instance.
    public function GetLevelsBeforeThisIn( array $list )
    {
        $levels = 0;
        // Beginning from the tail, go towards the head of the array in search of this instance
        end( $list );
        while( current( $list ) != false && $this->PositionInList() != current( $list )->PositionInList() )
            prev( $list );
            
        if( current( $list ) === false ) // We aren't in the list yet, just start from the tail
            end( $list );
        
        // The internal pointer is now set at this instance, let's continue towards the head and count the levels
        while( current( $list ) != false )
        {
            $item = current( $list );
            
            if( $this->Name() === $item->Name() && $item->Amount() > 0 )
                $levels++;
            
            prev( $list );
        }
        
        return $levels;
    }
    
    // Returns a Cost object.
    public function BuildCost()
    {
        return $this->CastToIDResource()->BuildCost( $this->BuildGroup()->Colony(), $this->Level() );
    }
    
    public function BuildTime( Cost $costs )
    {
        return $this->CastToIDResource()->BuildTime( $this->BuildGroup()->Colony(), $costs );
    }
    
    public function CalculateScheduledTime( $timeOffset = 0 )
    {
        $buildCosts = $this->BuildCost();
        $buildTime = $this->BuildTime( $buildCosts );
        
        $this->ScheduledTime( $buildTime + $timeOffset );
        return ($buildTime + $timeOffset);
    }
    
    public function ReadyToBuild()
    {
        $commissionedTime = $this->BuildGroup()->CommissionedTime();
        $buildCosts = $this->BuildCost();
        $buildTime = $this->BuildTime( $buildCosts );
        
        $totalTime = $commissionedTime + $buildTime;
        
        if( time() >= $totalTime && $this->Amount() > 0 )
            return true;
        return false;
    }
    
    // Call this when the item is ready to be built
    public function Construct()
    {
        if( !$this->ReadyToBuild() )
        {
            // TODO: log an error, hide from user
            "<br/>This unit is not ready to be built!";
            echo $this;
            //throw new Exception("This unit is not ready to be built!");
            return NULL;
        }

        // Add the item to the colony
        $this->BuildGroup()->Colony()->AddUnit( $this );
        
        // Reduce amount by 1
        $this->Amount( $this->Amount() - 1 );
        
        // Update database
        $this->BuildGroup()->UpdateDatabase();
        
        // The next item is scheduled immediately after this one,
        // so update the commissioned time
        $this->BuildGroup()->UpdateCommissionedTime();
        
        return $this->Name();
    }
    
    public function __toString()
    {
        $itemName = ResourceParser::Instance()->GetItemNameByID( $this->ID() );
        $id = $this->ID();
        $amount = $this->Amount();
        $pos = $this->PositionInList();
        $oldpos = $this->OldPositionInList();
        $level = $this->Level();
        return "BuildingBuildItem for the IDResource $itemName ($id), amount = $amount, level = $level\nNew position: $pos, old position: $oldpos";
    }
}