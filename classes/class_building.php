<?php

// Dependencies
require_once "class_idresource.php";

class Building extends IDResource
{
    public function Building( $name, Cost $cost, $nextcostmodifier, $prerequisite, $amount, $dbID )
	{
        parent::IDResource( $name, $cost, $nextcostmodifier, $prerequisite, $amount, $dbID );
	}
    
    public static function FromIDResource( IDResource $r )
    {
        return new Building( $r->Name(), $r->Cost(), $r->NextCostModifier(), $r->Prerequisite(), $r->Amount(), $r->ID() );
    }
    
    public function GetBuildLevelOnColony( Colony $c )
    {
        return $c->Buildings()->GetMemberByName( $this->Name() )->Amount();
    }
    
    // Given a Colony and a Cost, calculates total Build Time.
    public function BuildTime( Colony $colony, Cost $buildCosts )
    {
        // Calculate time required
        global $NN_config;
        $metalCost =& $buildCosts->Metal();
        $crystalCost =& $buildCosts->Crystal();
        $gameSpeed =& $NN_config["game_speed"];
        $robotFactories = $colony->Buildings()->GetMemberByName("robotics_factory")->Amount();
        $nanoFactories = $colony->Buildings()->GetMemberByName("nano_factory")->Amount();
        $manufacturers = $colony->Owner()->Officers()->GetMemberByName("manufacturer")->Amount();
        
        $timeRequired = ( ($metalCost + $crystalCost) / $gameSpeed ) * ( 1 / ($robotFactories + 1) ) * pow(0.5, $nanoFactories);
        $timeRequired = floor( ($timeRequired * 3600) * (1 - ($manufacturers * 0.1) ) );

        return $timeRequired;
    }
    
    // Searches the database production table and sums up all scheduled levels.
    public function GetScheduledLevelsInDatabase()
    {
        $type = $this->ID();
        $query = "SELECT SUM(amount_requested) AS total_amount FROM production_building WHERE resource_type_being_built = $type;";
        $result = Database::Instance()->ExecuteQuery( $query, "SELECT" );
        
        if( $result['total_amount'] === NULL )
            return 0;
        else
            return (int) $result['total_amount'];
    }
    
    public function Image( $rootLevel, $skin )
    {
        return "$rootLevel/inc/game_skins/$skin/buildings/".$this->Name().".gif";
    }
    
    public function __toString()
    {
        return parent::__toString();
    }
}

?>