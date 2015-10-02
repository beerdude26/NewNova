<?php

require_once "class_helper.php";
require_once "class_shipfleet.php";

// Transportation of a fleet from one colony to another
class Transportation extends Mission
{
    private $_otherColony = NULL;       // Receiving colony

    private function Transportation() {}
    
    public static function NewTransportation( ShipFleet $fleet, Colony $colony )
    {
        global $GR_missionDatabaseIDs;
        $fleet->MissionType( $GR_missionDatabaseIDs["transportation"] );
        $transport = new Transportation();
        $transport->Fleet( $fleet );
        $transport->ReceivingColony( $colony );
        $transport->ScheduledTime( $transport->Duration() + time() );
        return $transport;
    }
    
    public static function FromDatabaseByID( $id, Colony $c = NULL )
    {
       $query = "SELECT * FROM scheduled_transports WHERE transportID = $id";
       $row = Database::Instance()->ExecuteQuery( $query, "SELECT" );
       return Transportation::FromDatabase( $row );
    }
    
    public static function FromDatabase( array $row, Colony $colony = NULL )
    {
        $transport = new Transportation();
        $fleet = ShipFleet::FromDatabaseByID( $row['fleetID'] );
        $transport->Fleet( $fleet );
        $transport->ID( $row['transportID'] );
        if( $colony === NULL )
            $colony = Colony::FromDatabaseByID( $row['colonyID'], User::GetCurrentUser() );
        $transport->ReceivingColony( $colony );
        $transport->ScheduledTime( $row['scheduled_time'] );
        $transport->IsValid( true ); // Anything that gets into the database is valid.
        return $transport;
    }
    
    public static function FromOtherMission( Mission $otherMission )
    {
        return Transportation::NewTransportation( $otherMission->Fleet(), $otherMission->Fleet()->OriginalColony() );
    }
    
    public function ReceivingColony( $value = "" )
	{
		if( empty( $value ) )
			return $this->_otherColony;
		else
        {
            Helper::checkType( $this, $value, "Colony" );
			$this->_otherColony =& $value;
        }
	}
    
    public function Target()
    {
        return $this->_otherColony->Coordinates();
    }
    
    public function Validate()
    {
        $basicValidation = parent::Validate();
        if( !($basicValidation === "mission_ok") )
            // TODO: Show a message to the user and return, for now just error out
            throw new Exception($basicValidation);

        // Everything is good to go, set isValid to true
        $this->IsValid( true );
    }
    
    public function AddToDatabase()
    {
        if( !$this->IsValid() )
            throw new Exception("Mission has not been validated!");
            
        $query = "INSERT INTO scheduled_transports(fleetID, colonyID, scheduled_time) ";
        $query .= "VALUES(".$this->Fleet()->ID().", ".$this->ReceivingColony()->ID();
        $query .= ", ".$this->ScheduledTime().");";
        $result = Database::Instance()->ExecuteQuery( $query, "INSERT" );
        $this->ID( $result );
        
        if( $result )
        {
            global $GR_missionDatabaseIDs;
            $this->Fleet()->MissionType( $GR_missionDatabaseIDs['transportation'] );
            $this->Fleet()->UpdateDatabase(true);
        }
        return $result;
    }
    
    public function DeleteFromDatabase()
    {
        $query = "DELETE FROM scheduled_transports WHERE transportID = ".$this->ID().";";
        $res1 = Database::Instance()->ExecuteQuery( $query, "DELETE" );
        $query = "DELETE FROM fleet WHERE fleetID = ".$this->Fleet()->ID().";";
        $res2 = Database::Instance()->ExecuteQuery( $query, "DELETE" );

        return ($res1 && $res2);
    }
    
    // Execute this when transport arrives
    public function Arrive()
    {
        global $GR_missionDatabaseIDs;
        
        if( !$this->HasOccurred() || !$this->IsValid() )
            return; // TODO: throw error?
            
        // Put transport's cargo in receiving colony
        $this->ReceivingColony()->AddResources( $this->Fleet()->Cargo() );
        // Transfer transport fleet to colony fleet
        $this->Fleet()->OriginalColony( $this->ReceivingColony() ); // This fleet now belongs to that colony
        $this->Fleet()->MissionType( $GR_missionDatabaseIDs['stationary'] ); // This fleet's mission is now 'stationary'
        $this->ReceivingColony()->Fleet()->AddToFleet( $this->Fleet() );
        
        //Update database
        $resultRes = $this->ReceivingColony()->UpdateResources();
        $resultFleet = $this->ReceivingColony()->Fleet()->UpdateDatabase();
        
        // TODO: find a way to verify if no errors have occurred
        if( $this->DeleteFromDatabase() > 0 )
            return true;
                
        return false;
        // TODO: error here?
    }
}
  
?>