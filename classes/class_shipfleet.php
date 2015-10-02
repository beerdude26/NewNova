<?php

//Dependencies
require_once "class_combatgroup.php";

// A group of shipUnits
class ShipFleet extends CombatGroup
{
    private $_cargo = NULL;     // Cargo of the fleet, Cost object
    private $_missionType = 0; // Type of mission the fleet is on.
    private $_ID = 0;           // Database ID

    public function ShipFleet( array $group, Colony $colony, $missionType, Cost $cargo = NULL, $id = 0 )
    {
        $this->Members( $group );
        $this->OriginalColony( $colony );
        $this->MissionType( $missionType );
        $this->Cargo( new Cost( 0,0,0,0 ) );
        $this->ID( $id );
    }
    
    public static function FromDatabase( array $row, Colony $colony = NULL )
    {    
        // Slice off fleetID, colonyID, mission type and cargo data
        $shipList = array_slice( $row, 6 );
        
        if( $colony === NULL )
            $colony = Colony::FromDatabaseByID( $row['colonyID'], User::GetCurrentUser() );
            
        $members = CombatUnit::MakeListFrom( $shipList );
        $missiontype = $row['mission_type'];
        $id = $row['fleetID'];
        $cargo = new Cost( $row['metal_in_cargo'], $row['crystal_in_cargo'], $row['deuterium_in_cargo'], 0 );
        return new ShipFleet( $members, $colony, $missiontype, $cargo, $id );
    }
    
    public static function FromDatabaseByID( $id )
    {
        $query = "SELECT * FROM fleet WHERE fleetID = $id";
        $row = Database::Instance()->ExecuteQuery( $query, "SELECT" );
        
        return ShipFleet::FromDatabase( $row );
    }
    
    public static function FromCombatGroup( CombatGroup $g, Colony $c, $missionType )
    {
        return new ShipFleet( $g->Members(), $c, $missionType );
    }
    
    public static function FromList( array $array, Colony $colony, $missionType )
    {
        return new ShipFleet( ShipUnit::MakeListFrom( $array ), $colony, $missionType );
    }
    
    public static function GetShipsOfColony( Colony $colony )
    {
        global $GR_missionDatabaseIDs;
        $id = $colony->ID();
        $query = "SELECT * FROM fleet WHERE colonyID = $id AND mission_type = 0 LIMIT 1;";
        
        $row = Database::Instance()->ExecuteQuery( $query, "SELECT" );
        return ShipFleet::FromDatabase( $row, $colony );
    }
    
    public function Cargo( $value = "" )
	{
		if( empty( $value ) )
			return $this->_cargo;
		else
        {
            Helper::checkType( $this, $value, "Cost" );
			$this->_cargo =& $value;
        }
	}

    public function RemainingCargoCapacity()
    {
        $totalCapacity = 0;
        foreach( $this->Members() as $ship )
            $totalCapacity += $ship->CargoCapacity() * $ship->Amount();

        $totalCapacity -= $this->Cargo()->Metal();
        $totalCapacity -= $this->Cargo()->Crystal();
        $totalCapacity -= $this->Cargo()->Deuterium();

        return $totalCapacity;
    }
    
    public function GetTopSpeed()
    {
        $topSpeed = 10000000000000; // If a unit goes faster than this, increase
        foreach( $this->Members() as $unit )
        {
            if( $unit->Amount() < 0 )
                continue;
            $topSpeed = min( $topSpeed, $unit->GetEngine( $this->OriginalColony() )->Speed() );
        }
        
        return $topSpeed;
    }
    
    public function MissionType( $value = "empty" )
	{
		if( $value === "empty" )
			return $this->_missionType;
		else
            $this->_missionType = (int) $value;
	}
    
    public function ID( $value = "" )
	{
		if( empty( $value ) )
			return $this->_ID;
		else
            $this->_ID = (int) $value;
	}
    
    public function VerifySameColonyAndMission( ShipFleet $otherFleet, $throwException = false )
    {
        // Verify if these fleets are on the same colony and not on a mission
        if( $this->OriginalColony()->Equals( $otherFleet->OriginalColony() ) )
            if( $this->MissionType() == 0 && $otherFleet->MissionType() == 0 )
                return true;

        if( $throwException )
            throw new Exception("The given fleet is not on the same colony and/or on a mission!");
        return false;     
    }
    
    public function AddToFleet( ShipFleet $otherFleet )
    {
        Helper::checkType( $this, $otherFleet, "ShipFleet" );
        $this->VerifySameColonyAndMission( $otherFleet, true );
        
        $newFleet = Helper::sumUnits( $this->Members(), $otherFleet->Members() );
        $this->Members( $newFleet );
    }
    
    public function DeductFromFleet( ShipFleet $otherFleet )
    {
        Helper::checkType( $this, $otherFleet, "ShipFleet" );
        $this->VerifySameColonyAndMission( $otherFleet, true );
        
        $newFleet = Helper::deductUnits( $this->Members(), $otherFleet->Members() );
        $this->Members( $newFleet );
        
        if( Helper::containsNegative( $newFleet ) ) // Should never ever happen
            throw new Exception("Fleet contains negative units! (in ShipFleet::DeductFromFleet)");
    }
    
    // Given a shipFleet, splits up this fleet
    public function SplitFleet( ShipFleet $otherFleet )
    {
        $this->DeductFromFleet( $otherFleet );
        $this->UpdateDatabase();
        $otherFleet->AddToDatabase();
        return array( "old_fleet" => $this, "new_fleet" => $otherFleet );
    }
    
    public function AddToDatabase()
    {
        if( $this->ID() != 0 )
            throw new Exception("This fleet has already been added to the database!");
    
        $query = "INSERT INTO fleet(colonyID,mission_type,metal_in_cargo,crystal_in_cargo,deuterium_in_cargo, ";
        foreach( $this->Members() as $ship )
            $query.= $ship->Name().", ";
        // Lop off the last comma, replace with )
        $query = Helper::lop_off( $query, 2, ") " );
        
        $colonyID = $this->OriginalColony()->ID();
        $missionType = (int) $this->MissionType();
        $metal = $this->Cargo()->Metal();
        $metal = $this->Cargo()->Metal();
        $crystal = $this->Cargo()->Crystal();
        $deuterium = $this->Cargo()->Deuterium();
        $query .= "VALUES($colonyID, $missionType, $metal, $crystal, $deuterium, ";
        foreach( $this->Members() as $ship )
            $query .= $ship->Amount().", ";
        // Lop off the last comma, replace with )
        $query = Helper::lop_off( $query, 2, ");" );
        
        $result = Database::Instance()->ExecuteQuery( $query, "INSERT");
        $this->ID( $result );
        
        return $result;
    }
    
    public function DeleteFromDatabase()
    {
        $query = "DELETE FROM fleet WHERE fleetID = ".$this->ID();
        return Database::Instance()->ExecuteQuery( $query, "DELETE" );
    }
    
    public function UpdateDatabase( $updateOnlyMissionType = false )
    {
    
        $query = "UPDATE fleet SET";
        
        // Mission type
        $query .= " mission_type = ".$this->MissionType();
        
        if( !$updateOnlyMissionType )
        {
            // Cargo
            $query .= ", metal_in_cargo = ".$this->Cargo()->Metal();
            $query .= ", crystal_in_cargo = ".$this->Cargo()->Crystal();
            $query .= ", deuterium_in_cargo = ".$this->Cargo()->Deuterium();
        
            // Ships
            foreach( $this->Members() as $ship )
                $query .= ", ".$ship->Name()." = ".$ship->Amount();
        }
            
        $query .= " WHERE fleetID = ".$this->ID().";";
        return Database::Instance()->ExecuteQuery( $query, "UPDATE" );
    }
    
    public function __toString()
    {
        $header = "ShipFleet\n";
        $cargo = "Cargo: ".$this->Cargo()->__toString();
        $missionType = "Mission Type = ".$this->MissionType()."\n";
        $missionType = "Database ID = ".$this->ID()."\n";
        $members = "Members:\n";
        foreach( $this->Members() as $unitKey => $unitValue )
            $members .= "[KEY: $unitKey] ".$unitValue->__toString();
        return "<pre>".$header.$cargo.$missionType.$members."</pre>";
    }
}

?> 