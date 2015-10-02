<?php

//Dependencies
require_once "class_resourcegroup.php";
require_once "class_combatunit.php";

// A group of combat units.
class CombatGroup extends ResourceGroup
{
    private $_originalColony = NULL;    // Originating Colony
    
    public function CombatGroup( array $group, Colony $colony )
    {
        $this->Members( $group );
        $this->OriginalColony( $colony );
    }
    
    // Given a list of IDResource string names, returns a CombatGroup
    public static function FromList( array $list, Colony $colony )
    {
        $resourceGroup = new ResourceGroup( IDResource::MakeListFrom( $list ) );
        return CombatGroup::FromResourceGroup( $resourceGroup, $colony );
    }
    
    public static function FromResourceGroup( ResourceGroup $group, Colony $colony )
    {
        return new CombatGroup( $group->Members(), $colony );
    }
    
    public static function GetDefensesOfColony( Colony $colony )
    {
        $id = $colony->ID();
        $query = "SELECT * FROM colony_defences WHERE colonyID = $id;";
        
        $row = Database::Instance()->ExecuteQuery( $query, "SELECT" );
        $row = array_slice( $row, 1 ); // Get rid of colonyID
        // TODO: put these numbers in the config files or game resources,
        // otherwise when you add a new weapon you need to change all these.
        $row = array_slice( $row, 0, 8); // Get rid of missiles
        
        return CombatGroup::FromList( $row, $colony );
    }
    
    public static function GenerateDefenses( Colony $colony )
    {
        $list = ResourceParser::Instance()->DefenseUnits();
        foreach( $list as $unit )
            $unit->Amount( 0 );
        return new CombatGroup( $list, $colony );
    }
    
    public static function GenerateShips( Colony $colony )
    {
        $list = ResourceParser::Instance()->ShipUnits();
        $list = ResourceParser::Instance()->ShipUnits();
        foreach( $list as $unit )
            $unit->Amount( 0 );
        return new ShipFleet( $list, $colony, 0 );
    }
    
    public function OriginalColony( $value = "" )
	{
		if( empty( $value ) )
			return $this->_originalColony;
		else
        {
            Helper::checkType( $this, $value, "Colony" );
			$this->_originalColony =& $value;
        }
	}
    
    public function TotalUnits()
    {
        $totalUnits = 0;
        foreach( $this->Members() as $unit )
            $totalUnits += $unit->Amount();
            
        return $totalUnits;
    }

    public function TotalShields()
    {
        $totalShields = 0;
        foreach( $this->Members() as $unit )
            $totalShields += $unit->ShieldStrength();
            
        return $totalShields;
    }

    public function TotalFirepower()
    {
        $totalFirepower = 0;
        foreach( $this->Members() as $unit )
            $totalFirepower += $unit->AttackStrength();
            
        return $totalFirepower;
    }
	
	public function __toString()
	{
		$header = "CombatGroup\n";
		$members = "Members:\n";
		foreach( $this->Members() as $unitKey => $unitValue )
		{
			$members .= "[KEY: $unitKey] ".$unitValue->__toString();
		}
		return $header.$members;
	}
}

?>