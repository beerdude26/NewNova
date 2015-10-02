<?php

require_once "class_helper.php";
require_once "class_shipfleet.php";

// Expedition into the unknown
class Expedition extends Mission
{
    private function Expedition() {}
    
    public static function NewExpedition( ShipFleet $fleet, Coordinates $target, $scheduledTime )
    {
        $expedition = new Expedition();
        $expedition->Fleet( $fleet );
        $expedition->Target( $target );
        $expedition->ScheduledTime( $scheduledTime );
        return $expedition;
    }
    
    public static function FromDatabaseByID( $id )
    {
       $query = "SELECT * FROM scheduled_expeditions WHERE fleetID = $id";
       $row = Database::Instance()->ExecuteQuery( $query, "SELECT" );
       return Expedition::FromDatabase( $row );
    }
    
    public static function FromDatabase( array $row )
    {
        $expedition = new Expedition();
        $fleet = ShipFleet::FromDatabaseByID( $row['fleetID'] );
        $expedition->Fleet( $fleet );
        $coordinates = new Coordinates( $row['galaxy_position'], $row['system_position'], $row['planet_position'] );
        $expedition->Target( $coordinates );
        $expedition->ScheduledTime( $row['scheduled_time'] );
        return $expedition;
    }
    
    public function AddToDatabase()
    {
        $id = $this->Fleet()->ID();
        $gpos = $this->Target()->Galaxy();
        $spos = $this->Target()->System();
        $ppos = $this->Target()->Position();
        $time = $this->Duration() + time();
        $query = "INSERT INTO scheduled_expeditions(fleetID, galaxy_position, system_position, ";
        $query .= "planet_position, schedule_time) VALUES($id,$gpos,$spos,$ppos,$time);";
        
        $result = Database::Instance()->ExecuteQuery( $query, "INSERT" );
        $this->ID( $result );
        return $result;
    }
    
    public function DeleteFromDatabase()
    {
        $query = "DELETE FROM scheduled_expeditions WHERE expeditionID = ".$this->ID().";";
        return Database::Instance()->ExecuteQuery( $query, "DELETE" );
    }
    
    public function ReturnToBase()
    {
        if( $this->DeleteFromDatabase() > 0 )
        {
            $homeMission = Transportation::FromOtherMission( $this );
            if ( $homeMission->AddToDatabase() > 0 )
                return true;
        }
        // TODO: error here?
        return false;
    }
    
    // Execute this when expedition is done
    public function DoExpeditionStuff()
    {
        if( !$this->HasOccurred() )
            return; // TODO: throw error?
             
        $randomAction = mrand( 0, 10 );
        
        switch( $randomAction )
        {
            case 0:
            case 1: // Anything below 3: tough luck, a black hole is encountered.
            case 2:
                $randomAction++;
				$percentageLost = (($randomAction * 33) + 1) / 100;
                if( $percentageLost == 100 ) // We can just delete the entire fleet.
                    $this->Fleet()->DeleteFromDatabase();
                else // The fleet is partially destroyed
                {
                    foreach( $this->Fleet() as $ship )
                    {
                        $amountLost = intval( $ship->Amount() * $percentageLost );
                        $ship->Amount( $ship->Amount() - $amountLost );
                    }
                    $this->Fleet()->UpdateDatabase();
                    $this->ReturnToBase();
                }
                break;
            case 3: // 3 or 7 means the fleet didn't find anything at all.
            case 7:
                $this->ReturnToBase();
                break;
            case 4:
            case 5: // The fleet has found resources!
            case 6: 
                $maxCapacity = $this->Fleet()->RemainingCargoCapacity();
                $minCapacity = 0; // TODO: Note: A very big change from the original game!
                $foundGoods = mrand( $minCapacity, $maxCapacity );
                $foundMetal = intval( $foundGoods / 2 );
                $foundCrystal = intval( $foundGoods / 4 );
                $foundDeuterium = intval( $foundGoods / 6 );
                
                // Add the resources to the fleet's cargo
                $this->Fleet()->Cargo()->AddCost( new Cost( $foundMetal, $foundCrystal, $foundDeuterium, 0 ) );
                $this->Fleet()->UpdateDatabase();
                $this->ReturnToBase();
                break;
            case 8:
            case 9: // The fleet has found disbanded ships!
            case 10:
                global $GR_missionExpeditionData;
                foreach( $this->Fleet()->Members() as $ship )
                {
                    $amountDiscovered = round( $ship->Amount() * $GR_missionExpeditionData[$ship->Name()]['ratio_to_be_gained'] );
                    if( $amountDiscovered > 0 )
                        $ship->Amount( $ship->Amount() + $amountDiscovered );
                }
                $this->ReturnToBase();
                break;
        }
    }
}
  
?>