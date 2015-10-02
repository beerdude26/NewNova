<?php

// Dependencies
require_once "class_combatunit.php";

class ShipUnit extends CombatUnit
{
    private $_engineDetails = NULL;     // Array of ShipEngines, used to upgrade some ships.
    private $_cargoCapacity = 0;        // Ship cargo capacity.

	public function ShipUnit( $name, Cost $cost, $nextcostmodifier, array $prerequisite, $amount, $dbID, $attack, $shield, array $rapidfire, array $enginedetails, $capacity )
	{
        parent::CombatUnit( $name, $cost, $nextcostmodifier, $prerequisite, $amount, $dbID, $attack, $shield, $rapidfire );
		$this->_engineDetails = $enginedetails;
		$this->_cargoCapacity = $capacity;
	}
    
    public static function FromCombatUnit( CombatUnit $r, array $enginedetails, $capacity )
    {
        return new ShipUnit( $r->Name(), $r->Cost(), $r->NextCostModifier(), $r->Prerequisite(), $r->Amount(), $r->ID(), $r->AttackStrength(), $r->ShieldStrength(), $r->RapidfireBonuses(), $enginedetails, $capacity );
    }
    
    public function GetEngine( Colony $colony )
    {
        // TODO: finish this
        return $this->_engineDetails[0];
    }
	
	public function EngineDetails( $value = "" )
	{
		if( empty( $value ) )
			return $this->_engineDetails;
		else
			$this->_engineDetails = $value;
	}

	public function CargoCapacity( $value = "" )
	{
		if( empty( $value ) )
			return $this->_cargoCapacity;
		else
			$this->_cargoCapacity = $value;
	}
    
    public function GetBuildLevelOnColony( Colony $c )
    {
        return $c->Ships()->GetMemberByName( $this->Name() )->Amount();
    }
    
    public function BuildTime( Colony $colony )
    {
        // Calculate time required
        global $NN_config;
        $metalCost = $this->Cost()->Metal();
        $crystalCost = $this->Cost()->Crystal();
        $gameSpeed =& $NN_config["game_speed"];
        $shipyards = $colony->Buildings()->GetMemberByName("shipyard")->Amount();
        $nanoFactories = $colony->Buildings()->GetMemberByName("nano_factory")->Amount();
        $technocrats = $colony->Owner()->Officers()->GetMemberByName("technocrat")->Amount();
        $generals = $colony->Owner()->Officers()->GetMemberByName("general")->Amount();
        
        $timeRequired = ( ($metalCost + $crystalCost) / $gameSpeed ) * ( 1 / ($shipyards + 1) ) * pow(0.5, $nanoFactories);
        $timeRequired = floor( $timeRequired * 3600 * (1 - ($technocrats * 0.05) ) * (1 - ($generals * 0.25) ) );
        
        return $timeRequired;
    }
    
    public function Image( $rootLevel, $skin )
    {
        return "$rootLevel/inc/game_skins/$skin/ships/".$this->Name().".gif";
    }
    
    public function __toString()
    {
        return parent::__toString();
    }
}
?>