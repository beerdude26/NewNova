<?php

//Dependencies
require_once "class_idresource.php";

// A Resource that is to be built.
class BuildItem extends IDResource
{
    private $_hasChanged = false;    // Has the Resource in question changed?
    private $_buildGroup = NULL;     // The BuildGroup this item is a part of.
    private $_positionInList = 0;    // Position of the requested Resource.
    private $_oldPositionInList = 0; // Old position of the requested Resource.
    private $_firstBuildTime = 0;    // First build time of the item.
    private $_lastBuildTime = 0;     // Last build time of the item

    private function BuildItem() {}
    
    public static function FromIDResource( IDResource $resource, $position, $oldPosition, $scheduledTime, BuildGroup $bg = NULL )
    {
        $buildItem = new BuildItem();
        $buildItem->Name( $resource->Name() );
        $buildItem->Cost( $resource->Cost() );
        $buildItem->NextCostModifier( $resource->NextCostModifier() );
        $buildItem->Prerequisite( $resource->Prerequisite() );
        $buildItem->ID( $resource->ID() );
        $buildItem->Amount( $resource->Amount(), false );
        $buildItem->PositionInList( $position, false );
        $buildItem->OldPositionInList( $oldPosition, false);
        $buildItem->FirstBuildTime( $scheduledTime );
        $buildItem->HasChanged( false );
        $buildItem->BuildGroup( $bg );
        
        return $buildItem;
    }
    
    public static function NewBuildItem( $name, $cost, $nextcostmodifier, $prerequisite, $amount, $dbID, $position, $oldPosition, $time, BuildGroup $bg )
    {
        parent::IDResource( $name, $cost, $nextcostmodifier, $prerequisite, $amount, $dbID  );
        $this->PositionInList( $position );
        $buildItem->OldPositionInList( $oldPosition, false);
        $this->FirstBuildTime( $time );
        $this->HasChanged( false );
        $buildItem->BuildGroup( $bg );
    }
    
	public function PositionInList( $value = "empty", $informBuildGroup = true )
	{
		if( $value === "empty" )
			return $this->_positionInList;
		else
        {
            $this->_positionInList = $value;
            $this->_hasChanged = true;
        }
	}
    
    public function OldPositionInList( $value = "empty", $informBuildGroup = true )
	{
		if( $value === "empty" )
			return $this->_oldPositionInList;
		else
        {
            $this->_oldPositionInList = $value;
            $this->_hasChanged = true;
        }
	}
    
    public function Amount( $value = "empty", $informBuildGroup = true )
	{
		if( $value === "empty" )
			return parent::Amount();
		else
        {
            parent::Amount( $value );
            $this->_hasChanged = true;
        }
	}
    
    public function HasChanged( $value = "empty" )
	{
		if( $value === "empty" )
			return $this->_hasChanged ? 1 : 0;
		else
			$this->_hasChanged = (boolean) $value;
	}
    
    public function FirstBuildTime( $value = "empty" )
	{
		if( $value === "empty" )
			return $this->_firstBuildTime;
		else
        {
            $this->_firstBuildTime = $value;
            $this->_hasChanged = true;
        }
	}
    
    public function LastBuildTime( $value = "empty" )
	{
		if( $value === "empty" )
			return $this->_lastBuildTime;
		else
        {
            $this->_lastBuildTime = $value;
            $this->_hasChanged = true;
        }
	}
    
    public function BuildGroup( $value = "" )
	{
		if( empty( $value ) )
			return $this->_buildGroup;
		else
            $this->_buildGroup =& $value;
	}
    
    // TODO: Don't forget to actually put this somewhere!
    public function CanBeConstructed()
    {
        $payable = $this->CastToIDResource()->CanBePaidFor( $this->BuildGroup()->Colony(), $this->GetScheduledLevelsBeforeThis() + 1 );
        $prereqs = $this->CastToIDResource()->FullfillsPrerequisites( $this->BuildGroup()->Colony() );
        
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
        if( $payable && $prereqs )
            return true;
        return false;
    }
    
    public function ScheduledLevelsBeforeThis()
    {
        $levels = 0;
        
        $bg = $this->BuildGroup()->Members();

        // Go towards the start of the array in search of this instance
        end( $bg );
        while( $this->PositionInList() != current( $bg )->PositionInList() && current( $bg ) != false )
            prev( $bg );
        
        // The internal pointer is now set at this instance, let's continue towards the start
        prev( $bg );
        while( current( $bg ) != false )
        {
            $item = current( $bg );
            
            if( $this->Name() === $item->Name() )
                $levels += $item->Amount();
            
            prev( $bg );
        }
        
        return $levels;
    }
    
    // Searches the database and BuildGroup for this item and sums up all scheduled levels.
    public function GetScheduledLevelsBeforeThis()
    {
        $levels = 0;
        
        // Search the BuildGroup itself
        foreach( $this->BuildGroup()->Members() as $item )
            if( $this->Name() === $item->Name() )
                $levels += $item->Amount();
        
        // Search the database list
        if( $this->BuildGroup()->ItemsInDatabase() == NULL )
            return $levels; // The BuildGroup IS the database list, so there aren't any items before this in it.

        foreach( $this->BuildGroup()->ItemsInDatabase() as $item )
            if( $this->Name() === $item->Name() )
                $levels += $item->Amount();
                
        return $levels;
    }
    
    public function LastBuildTimeOfPreviousItem()
    {
        // Get last build time of previous item.
        $previousItem = $this->BuildGroup()->BuildItemBefore( $this );
        if( $previousItem == NULL )
            $lastBuildTime = 0;
        else
            $lastBuildTime = $previousItem->LastBuildTime();
        return $lastBuildTime;
    }
    
    // Given a real IDResource and a build level, returns a Cost object.
    public function BuildCost( IDResource $itemObject, $buildLevel )
    {
        return $itemObject->BuildCost( $this->BuildGroup()->Colony(), $buildLevel );
    }
    
    public function CastToIDResource()
    {
        // Cast this BuildItem to an actual IDResource
        $itemName = ResourceParser::Instance()->GetItemNameByID( $this->ID() );
        $itemObject =& ResourceParser::Instance()->GetItemByName( $itemName );
        return $itemObject;
    }

    // NOTE: does not use time()
    public function CalculateTotalBuildTimeForLastAmount()
    {
        $lastBuildTime = $this->LastBuildTimeOfPreviousItem();
        
        if( $this->Amount() === 0 ) // No build time occurs!
        {
            $this->LastBuildTime( $lastBuildTime );
            return;
        }
        
        $itemObject =& $this->CastToIDResource();
        
        $totalBuildTime = 0;
        
        if( $this->BuildGroup()->Type() === "BUILDING" ) // Buildings need a build cost, which is based on levels.
        {
            $buildLevelsOnColony = $itemObject->GetBuildLevelOnColony( $this->BuildGroup()->Colony() );
            $buildLevelsInDatabase = $this->ScheduledLevelsBeforeThis();
            for( $requestedLevels = 0; $requestedLevels < $this->Amount(); $requestedLevels++ )
            {
                $totalBuildLevel = $buildLevelsOnColony + $requestedLevels + $buildLevelsInDatabase;
                $buildCosts = $this->BuildCost( $itemObject, $totalBuildLevel );
                $totalBuildTime += $itemObject->BuildTime( $this->BuildGroup()->Colony(), $buildCosts );
            }
        }
        else // Defenses, Ships and Missiles are linear.
            $totalBuildTime += $itemObject->BuildTime( $this->BuildGroup()->Colony() ) * $this->Amount();
        
        // Add this plethora of build time
        $lastBuildTime += $totalBuildTime;
        
        $this->LastBuildTime( $lastBuildTime );
    }
    
    // NOTE: Does not use time()
    public function CalculateTotalBuildTimeForFirstAmount()
    {
        // Get last build time of previous item.
        $firstBuildTime = $this->LastBuildTimeOfPreviousItem();
        
        if( $this->Amount() === 0 ) // No build time occurs!
        {
            $this->FirstBuildTime( $firstBuildTime );
            return;
        }
        
        $itemObject =& $this->CastToIDResource();
        
        // Calculate first build time.
        if( $this->BuildGroup()->Type() === "BUILDING" ) // Buildings need a build cost, which is based on levels.
        {
            $buildLevelsOnColony = $itemObject->GetBuildLevelOnColony( $this->BuildGroup()->Colony() );
            $buildLevelsInDatabase = $this->ScheduledLevelsBeforeThis();
            $totalBuildLevel = $buildLevelsOnColony + $buildLevelsInDatabase;
            $buildCosts = $this->BuildCost( $itemObject, $totalBuildLevel );
            $firstBuildTime += $itemObject->BuildTime( $this->BuildGroup()->Colony(), $buildCosts );
        }
        else // Defenses, Ships and Missiles are linear.
            $firstBuildTime += $itemObject->BuildTime( $this->BuildGroup()->Colony() );
        
        $this->FirstBuildTime( $firstBuildTime );
    }
    
    public function ReadyToBuild()
    {
        echo "Current Time and First Build Time<br/>";
        echo time()." and ".$this->FirstBuildTime();
        if( time() > $this->FirstBuildTime() && $this->Amount() > 0 )
            return true;
        return false;
    }

    // Call this when the item is ready to be built
    // Adds as many items as possible.
    public function Construct()
    {
        if( !$this->ReadyToBuild() )
        {
            echo $this;
            //throw new Exception("This unit is not ready to be built!");
        }

        // Add the item to the colony
        $currentTime = time();
        while( $this->ReadyToBuild() && $this->Amount() > 0 )
        {
            // Add a single unit
            $this->BuildGroup()->Colony()->AddUnit( $this );
            // Reduce amount by 1
            $this->Amount( $this->Amount() - 1 );
            // Recalculate first build time
            $this->CalculateTotalBuildTimeForFirstAmount( $currentTime );
        }
    }
    
    public function __toString()
    {
        $itemName = ResourceParser::Instance()->GetItemNameByID( $this->ID() );
        $id = $this->ID();
        $amount = $this->Amount();
        $pos = $this->PositionInList();
        $oldpos = $this->OldPositionInList();
        return "BuildItem for the IDResource $itemName ($id), amount = $amount\nNew position: $pos, old position: $oldpos";
    }
}