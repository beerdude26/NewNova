<?php

// Dependencies
require_once "class_cost.php";
require_once "class_prerequisite.php";

class Resource
{
	private $_name = "NAME_NOT_SET";	// The name of the resource.
	private $_cost = NULL;				// The cost of the resource as a Cost object.
	private $_nextcostmodifier = 1;		// A modifier that is multiplied with the build level of the resource to determine how much the next level of this resource will cost.
	private $_prerequisite = NULL;		// The prerequisites for this resource in the form of a Prerequisite object array.
    private $_amount = 0;               // Amount of the resource.
    
	public function Resource( $name, Cost $cost, $nextcostmodifier, $prerequisite, $amount )
	{
		$this->Name( $name );
		$this->Cost( $cost );
		$this->NextcostModifier( $nextcostmodifier );
		$this->Prerequisite( $prerequisite );
		$this->Amount( $amount );
	}
    
    // Note: this can't be overloaded by child classes because it's static.
    public static function MakeListFrom( array $list, $useNamesAsIndices = true )
    {
        $members = array();
        foreach( $list as $itemName => $itemAmount )
        {
            // TODO: MAKE SURE THIS IS A NEW INSTANCE OF THE OBJECT,
            // AS WE CHANGE THE AMOUNT
            $itemObject = clone ResourceParser::Instance()->GetItemByName( $itemName );
            $itemObject->Amount( $itemAmount );
            if( $useNamesAsIndices )
                $members[$itemName] = $itemObject;
            else
                $members[] = $itemObject;
        }

        return $members;
    }
	
	public function Name( $value = "" )
	{
		return $this->_name = ( ( empty( $value ) ) ? $this->_name : $value );
	}
	
	public function Cost( $value = "" )
	{
		if( empty( $value ) )
			return $this->_cost;
		else
        {
            Helper::checkType( $this, $value, "Cost");
			$this->_cost =& $value;
        }
	}
	
	public function NextcostModifier( $value = "" )
	{
		return $this->_nextcostmodifier = ( ( empty( $value ) ) ? $this->_nextcostmodifier : $value );
	}
	
	public function Prerequisite( $value = "" )
	{ 
		if( empty( $value ) )
			return $this->_prerequisite;
		else
        {
			$this->_prerequisite =& $value;
        }
	}

    public function Amount( $value = "empty" )
	{
		if( $value === "empty" )
			return $this->_amount;
		else
        {
			$this->_amount = Helper::clamp( 0, (int) $value, (int) $value );
        }
	}
    
    //Calculate the build level of the resource that is to be constructed.
    protected function CalculateNextBuildLevel( BuildItem $item, $alreadyBuiltLevels )
    {
        $scheduledLevelTotal = 0;
    
        // First, look through the database list.
        
        // If it's empty, it means the current BuildGroup IS the database list! We look through that list below.
        if( $item->BuildGroup()->ItemsInDatabase() != NULL )
            foreach( $item->BuildGroup()->ItemsInDatabase() as $buildItem )
                // We're only interested in items scheduled before us, not in the future!
                if( $buildItem->PositionInList() < $item->PositionInList() )
                    if( $buildItem->Name() === $this->Name() )
                        $scheduledLevelTotal += $buildItem->Amount();
                    
        // Next, look through the current BuildGroup, we may have requested units like these before (but they're not in the database yet)
        foreach( $item->BuildGroup()->Members() as $buildItem )
            // We're only interested in items scheduled before us, not in the future!
            if( $buildItem->PositionInList() < $item->PositionInList() ) 
                if( $buildItem->Name() === $this->Name() )
                    $scheduledLevelTotal += $buildItem->Amount();
        
        echo "<pre>the next buildLevel return of ".$item->Name()." is ".($alreadyBuiltLevels + $scheduledLevelTotal + 1).".</pre>";

        return ($alreadyBuiltLevels + $scheduledLevelTotal + 1);
    }
    
    public function CanBePaidFor( Colony $colony, $buildLevel )
    {
        $cost = $this->BuildCost( $colony, $buildLevel );
        return $colony->CurrentResources()->CostIsDeductible( $cost );
    }
    
    public function DeconstructionCost( BuildItem $item, $buildLevel = -1 )
    {
        $colony =& $item->BuildGroup()->Colony();
        $c = $this->BuildCost( $item, $buildLevel );
        return new Cost( floor($c->Metal() / 4), floor($c->Crystal() / 4), floor($c->Deuterium() / 4), floor($c->Energy() / 4) );
    }
    
    public function FullfillsPrerequisites( Colony $c )
    {
        if( $this->Prerequisite() == NULL )
            return true;
            
        foreach( $this->Prerequisite() as $prerequisite )
        {
            $prereqName = $prerequisite->Item()->Name();
            $prereqAmount = $prerequisite->Amount();
            
            $colonyAmount = $c->SearchForPrerequisite( $prereqName )->Amount();
            if( $prereqAmount > $colonyAmount )
                return false;
        }
        return true;
    }
    
    public function __toString()
    {
        $name = "Name: ". $this->Name();
        $cost = $this->Cost()->__toString();
        $amount = "Amount: ".$this->Amount();
        
        return "<pre>Resource\n".$name."\n".$cost."\n".$amount."\n</pre>";
    }
    

}
?>