<?php

require_once "class_helper.php";
require_once "class_shipfleet.php";

// Fleet Mission
class Mission
{
	private $_fleet = NULL;             // ShipFleet
    private $_target = NULL;            // Coordinates of target
    private $_databaseID = -1;          // Database ID
    private $_scheduledTime = 0;        // When is the mission completed?
    private $_isValid = false;          // Has this mission been validated?
    
    private function Mission() {}
    
    public static function NewMission( ShipFleet $fleet, Coordinates $target, $scheduledTime )
    {
        $mission = new Mission();
        $mission->Fleet( $fleet );
        $mission->Target( $target );
        $mission->ScheduledTime( $scheduledTime );
        return $mission;
    }
    
    public function Fleet( $value = "" )
	{
		if( empty( $value ) )
			return $this->_fleet;
		else
        {
            Helper::checkType( $this, $value, "ShipFleet" );
            foreach( $value->Members() as $unit )
            {
                // This is hardcoded, perhaps put it in a config value?
                if( $unit->Name() === "solar_satellite" && $unit->Amount() > 0 )
                    if( $value->MissionType() != 0 )
                        throw new Exception("You can't have a solar satellite in a mission!");
            }
            
			$this->_fleet =& $value;
        }
	}
    
    public function Target( $value = "" )
	{
		if( empty( $value ) )
			return $this->_target;
		else
        {
            Helper::checkType( $this, $value, "Coordinates" );
			$this->_target =& $value;
        }
	}
    
    public function ID( $value = "" )
	{
		if( empty( $value ) )
			return $this->_databaseID;
		else
			$this->_databaseID = $value;
	}
    
    public function ScheduledTime( $value = "" )
	{
		if( empty( $value ) )
			return $this->_scheduledTime;
		else
			$this->_scheduledTime = $value;
	}
    
    protected function IsValid( $value = "empty" )
	{
		if( $value === "empty" )
			return $this->_isValid;
		else
			$this->_isValid = (bool) $value;
	}
    
    // Validates the Mission, returning an error code if not valid
    public function Validate()
    {
        if( $this->IsValid() )
            return "mission_ok"; // Don't need to do all that work if it's valid
            
        if( $this->EnoughFuelForMission() )
        {
            $this->PutFuelInFleetCargo();
            if( $this->Fleet()->RemainingCargoCapacity() >= 0 )
                if( $this->Fleet()->ID() != 0 )
                    return "mission_ok";
                else
                    return "fleet_not_added_to_database";
            else
                return "mission_insufficient_cargo_capacity";
        }
        else
            return "mission_insufficient_fuel";
    }
    
    public function PutFuelInFleetCargo()
    {
        $originalDeuterium = $this->Fleet()->Cargo()->Deuterium();
        $this->Fleet()->Cargo()->Deuterium( $originalDeuterium + $this->GetTotalFuelUsage() );
    }
    
    public function EnoughFuelForMission()
    {
        if( $this->IsValid() )
            throw new Exception("You can't check the fuel for a mission that's already underway!");
            
        $deuteriumOnColony = $this->Fleet()->OriginalColony()->CurrentResources()->Deuterium();
        $totalFuelUsage = $this->GetTotalFuelUsage();
        
        if( $deuteriumOnColony - $totalFuelUsage < 0 )
            return false;
        return true;
    }
    
    public function HasOccurred()
    {
        if( time() > $this->ScheduledTime() )
            return true;
        return false;
    }
    
    public function Distance()
    {
         return $this->Fleet()->OriginalColony()->Coordinates()->DistanceFrom( $this->Target() );
    }
     
    public function Duration()
    {
        global $NN_config;
        $topSpeedOfFleet = $this->Fleet()->GetTopSpeed();
        $distance = $this->Distance();
        $speedFactor = $this->SpeedFactor();
        $gameSpeed = $NN_config["game_speed"];
        
        // TODO: get rid of these magic numbers?
        $duration = ( ( 35000 / $gameSpeed * sqrt( $distance * 10 / $topSpeedOfFleet ) + 10) / $speedFactor );
        
        return round( $duration );
    }
    
    // TODO: This is not *exactly* as the original game, problematic?
    public function GetTotalFuelUsage()
    {
        $totalFuelUsage = 0;
        $topSpeedOfFleet = $this->Fleet()->GetTopSpeed();
        $missionDuration = $this->Duration();
        $distance = $this->Distance();
        $speedFactor = $this->SpeedFactor();

        foreach( $this->Fleet()->Members() as $unit )
        {
            if( $unit->Amount() < 0 )
                continue;

            $shipSpeed = $unit->GetEngine( $this->Fleet()->OriginalColony() )->Speed();
            $fuelUsage = $unit->GetEngine( $this->Fleet()->OriginalColony() )->FuelUsage();
            $shipCount = $unit->Amount();

            $speed = 35000 / ( $missionDuration * $speedFactor - 10 ) * sqrt( $distance * 10 / $shipSpeed );
            $basicConsumption = $fuelUsage * $shipCount;

            $totalFuelUsage += $basicConsumption * ( ($distance / 35000) * pow( ($speed / 10), 2 ) );
        }
        
        return round($totalFuelUsage) + 1;
    }
    
    // TODO: if I find any more "config" functions, put them in config class?
    public function SpeedFactor()
    {
        global $NN_config;
        return $NN_config["fleet_speed"] / $NN_config["fleet_speed_reduction"];
    }
}
  
?>