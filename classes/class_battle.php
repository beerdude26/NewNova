<?php

require_once "class_helper.php";
require_once "class_shipfleet.php";
require_once "class_mission.php";

// A battle between two Fleets (= group of CombatUnits).
class Battle extends Mission
{
    private $_defender = NULL;      // Defender

    private function Battle() {}
    
    public static function NewBattle( Colony $attacker, Colony $defender, $scheduledTime )
    {
        $battle = new Battle();
        $battle->Attacker( $attacker );
        $battle->Defender( $defender );
        $battle->Target( $defender->OriginalColony()->Coordinates() );
        $battle->ScheduledTime( $scheduledTime );
        return $battle;
    }
    
    public static function FromDatabase( $row )
    {
        
    }
    
    public function SimulateBattle()
    {
        
    }
    
    public function Attacker( $value = "" )
	{
		if( empty( $value ) )
			return $this->Fleet();
		else
			$this->Fleet( $value );
	}
    
    public function Defender( $value = "" )
	{
		if( empty( $value ) )
			return $this->_defender;
		else
        {
            Helper::checkType( $this, $value, "Fleet" );
			$this->_defender =& $value;
        }
	}
}
  
?>