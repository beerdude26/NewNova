<?php

// Dependencies
require_once "class_resource.php";

// A Resource that uses a Database ID.
class IDResource extends Resource
{
    private $_databaseID = NULL;        // A number for use in the database.
	
	public function IDResource( $name, Cost $cost, $nextcostmodifier, $prerequisite, $amount, $dbID )
	{
        parent::Resource( $name, $cost, $nextcostmodifier, $prerequisite, $amount );
		$this->_databaseID = $dbID;
	}
    
    public static function FromResource( Resource $r, $dbID )
    {
        return new IDResource( $r->Name(), $r->Cost(), $r->NextCostModifier(), $r->Prerequisite(), $r->Amount(), $dbID );
    }
	
	public function ID( $value = "" )
	{
		return $this->_databaseID = ( ( empty( $value ) ) ? $this->_databaseID : $value );
	}
    
    private function InRange( $range )
    {
        if( $this->ID() >= $range["from"] && $this->ID() <= $range["to"] )
            return true;
        return false;
    }
    
    public function IsShip()
    {
        global $NN_config;
        $range = $NN_config["ship_id_range"];
        return $this->InRange( $range );
    }
    
    public function IsBuilding()
    {
        global $NN_config;
        $range = $NN_config["building_id_range"];
        return $this->InRange( $range );
    }
    
    public function IsDefense()
    {
        global $NN_config;
        $range = $NN_config["defense_id_range"];
        return $this->InRange( $range );
    }
    
    public function IsMissile()
    {
        global $NN_config;
        $range = $NN_config["missile_id_range"];
        return $this->InRange( $range );
    }
    
    public function __toString()
    {
        return parent::__toString();
    }
    
    // Automatically fetches build level from database to produce correct build cost
    public function BuildCost( Colony $c, $buildLevel = NULL )
    {
        if( $buildLevel == NULL )
            $buildLevel = $this->GetScheduledLevelsInDatabase() + $c->Buildings()->GetMemberByName( $this->Name() )->Amount();
        
        $metalCost = floor( $this->Cost()->Metal() * pow( $this->NextcostModifier(), $buildLevel ) );
        $crystalCost = floor( $this->Cost()->Crystal() * pow( $this->NextcostModifier(), $buildLevel ) );
        $deuteriumCost = floor( $this->Cost()->Deuterium() * pow( $this->NextcostModifier(), $buildLevel ) );
        $energyCost = floor( $this->Cost()->Energy() * pow( $this->NextcostModifier(), $buildLevel ) );
        return new Cost( $metalCost, $crystalCost, $deuteriumCost, $energyCost );
    }
    
    // Searches the database production table and sums up all scheduled levels.
    public function GetScheduledLevelsInDatabase()
    {
        $type = $this->ID();
        $query = "SELECT SUM(amount_requested) AS total_amount FROM production WHERE resource_type_being_built = $type;";
        $result = Database::Instance()->ExecuteQuery( $query, "SELECT" );
        
        if( $result['total_amount'] === NULL )
            return 0;
        else
            return (int) $result['total_amount'];
    }
}
?>