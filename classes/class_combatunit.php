<?php

// Dependencies
require_once "class_idresource.php";

class CombatUnit extends IDResource
{
	private $_attackstrength = 1;		// The default attack strength this unit has.
	private $_shieldstrength = 1;		// The default shield strength this unit has.
	private $_rapidfirebonuses = NULL;	// A RapidFire object array that defines which Units this Unit has the rapidfire ability against.
	
	public function CombatUnit( $name, $cost, $nextcostmodifier, $prerequisite, $amount, $dbID, $attack, $shield, $rapidfire )
	{
        parent::IDResource( $name, $cost, $nextcostmodifier, $prerequisite, $amount, $dbID );
		$this->_attackstrength = $attack;
		$this->_shieldstrength = $shield;
		$this->_rapidfirebonuses = $rapidfire;
	}
    
    public static function FromIDResource( IDResource $r, $attack, $shield, $rapidfire )
    {
        return new CombatUnit( $r->Name(), $r->Cost(), $r->NextCostModifier(), $r->Prerequisite(), $r->Amount(), $r->ID(), $attack, $shield, $rapidfire );
    }
	
	public function AttackStrength( $value = "" )
	{
		return $this->_attackstrength = ( ( empty( $value ) ) ? $this->_attackstrength : $value );
	}
	
	public function ShieldStrength( $value = "" )
	{
		return $this->_shieldstrength = ( ( empty( $value ) ) ? $this->_shieldstrength : $value );
	}

	public function RapidfireBonuses( $value = "" )
	{
		return $this->_rapidfirebonuses = ( ( empty( $value ) ) ? $this->_rapidfirebonuses : $value );
	}
    
    public function GetBuildLevelOnColony( Colony $c )
    {
        return $c->Defenses()->GetMemberByName( $this->Name() )->Amount();
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
        $defenders = $colony->Owner()->Officers()->GetMemberByName("defender")->Amount();
        
        $timeRequired = ( ($metalCost + $crystalCost) / $gameSpeed ) * ( 1 / ($shipyards + 1) ) * pow(0.5, $nanoFactories);
        $timeRequired = floor( $timeRequired * 3600 * (1 - ($defenders * 0.375) ) );

        return $timeRequired;
    }
    
    public function Image( $rootLevel, $skin )
    {
        return "$rootLevel/inc/game_skins/$skin/defenses/".$this->Name().".gif";
    }
    
    public function __toString()
    {
        return parent::__toString();
    }
}
?>