<?php

// This class does a lot of the time-based game logic.
class Think
{
    private static $_instance = NULL;	//Singleton
    private $_user = NULL;
    private $_overridden = false;
    
    private function Think() {}
	
	public static function Instance()
	{
		if( !self::$_instance )
		{
			self::$_instance = new Think();
		}
		
		return self::$_instance;
	}
    
    public function OverrideUser( User $u )
    {
        $this->_user = $u;
        $this->_overridden = true;
    }
    
    public function ReturnControl()
    {
        $this->_user = NULL;
        $this->_overridden = false;
    }
    
    /* TRANSPORTS */
    
    private function ExecuteTransport( array $row, Colony $c = NULL )
    {
        $transport = Transportation::FromDatabase( $row, $c );
        $transport->Arrive();
    }
    
    
    public function CheckTransportsOf( Colony $c )
    {
        $currentTime = time();
        $query = "SELECT * FROM scheduled_transports WHERE $currentTime > scheduled_time";
        
        $rows = Database::Instance()->ExecuteQuery( $query, "SELECT" );
        
        if( $rows === NULL )
            return; // Nothing to do here.
            
        if( !isset( $rows['transportID'] ) )
            foreach( $rows as $row )
                $this->ExecuteTransport( $row, $c );
        else
            $this->ExecuteTransport( $rows, $c );
    }
    
    /* UNIT CONSTRUCTION */
    
    private function ConstructUnits( ResourceBuilder $rb )
    {
        if( count($rb->BuildList()->Members()) == 0 ) 
            return true; // Nothing to do here
            
        foreach( $rb->BuildList()->Members() as $unit )
            $unit->Construct();
        
        // Update rest of build list
        $rb->BuildList()->UpdateBuildTimes();
		$rb->UpdateDatabase();
        return true;
    }
    
    // Constructs the head of the list
    private function ConstructBuilding( ResourceBuilder $rb )
    {
    
        if( count($rb->BuildList()->Members()) == 0 ) 
            return true; // Nothing to do here
            
        // Get the first item on the list
        $unit = reset( $rb->BuildList()->Members() );
        
        echo "<br/>Unit about to be built: ";
        echo $unit;
            
        // Construct it (database is updated within)
        return $unit->Construct();
    }
    
    public function ConstructBuildingsOf( Colony $c )
    {
        $rb = ResourceBuilder::GetBuildingListOfColony( $c );
        $constructedBuilding = $this->ConstructBuilding( $rb );
        
        $c->UpdateBuildingsInDatabase( array( $constructedBuilding ) );
    }
    
    public function ConstructShipsOf( Colony $c )
    {
        $rb = ResourceBuilder::GetShipListOfColony( $c, true );
        if( $this->ConstructUnits( $rb ) )
            $rb->BuildList()->Colony()->Fleet()->UpdateDatabase();
    }
    
    public function ConstructDefensesOf( Colony $c )
    {
        $rb = ResourceBuilder::GetDefenseListOfColony( $c, true );
        $this->ConstructUnits( $rb );
    }
    
    public function ConstructMissilesOf( Colony $c )
    {
        $rb = ResourceBuilder::GetMissileListOfColony( $c, true );
        $this->ConstructUnits( $rb );
    }
    
    public function ConstructUnitsOf( Colony $c )
    {
        if( $this->_overridden )
            $user = $this->_user;
        else
            $user = User::GetOwnerOfColonyID( $c->ID(), $c );
            
        $c->Owner( $user );
        
        $this->ConstructBuildingsOf( $c );
        $this->ConstructShipsOf( $c );
        $this->ConstructDefensesOf( $c );
        $this->ConstructMissilesOf( $c );
    }
    
    // Returns a list of all colonyIDs that have unfinished actions, no doubles, and defenders up top.
    public function GetColoniesWithUnfinishedActions()
    {
        $currentTime = time();
        $production = "SELECT DISTINCT colonyID AS id, 0 AS is_defender FROM production WHERE $currentTime > scheduled_time;";
        $transports = "SELECT DISTINCT colonyID AS id, 0 AS is_defender FROM scheduled_transports WHERE $currentTime > scheduled_time;";
        $expeditions = "SELECT DISTINCT f.colonyID AS id, 0 AS is_defender FROM scheduled_expeditions AS e, fleet AS f WHERE e.fleetID = f.fleetID AND $currentTime > scheduled_time;";
        $attackers = "SELECT DISTINCT attackerID, 0 AS is_defender FROM scheduled_battles WHERE $currentTime > scheduled_time;";
        $defenders = "SELECT DISTINCT defenderID, 1 AS is_defender FROM scheduled_battles WHERE $currentTime > scheduled_time;";
        
        $results = array();
        $queries = array( $production, $transports, $expeditions, $attackers, $defenders );
        foreach( $queries as $query )
            $results[] = Database::Instance()->ExecuteQuery( $query, "SELECT" );

        $allColonies = array();
        foreach( $results as $rows )
        {
            if( is_array( $rows ) && isset( $rows['id'] ) ) // Just one row
                    $allColonies[ $rows['id'] ] = $rows['is_defender'];
            elseif( is_array( $rows ) )
                foreach( $rows as $row )
                    $allColonies[ $row['id'] ] = $row['is_defender']; 
        }
        
        arsort( $allColonies ); // Defenders have to be updated first, so let's sort it like that.
        
        return array_keys($allColonies);
    }
    
    public function ThinkForColony( $colonyID )
    {
        $colony = Colony::FromDatabaseByID( $colonyID );
        $this->ConstructUnitsOf( $colony );
        
    }
    
    public function ThinkOnce()
    {
        if( $this->_overridden )
            throw new Exception("It is not allowed to call Think::ThinkOnce while overriding the user!");
    
        foreach( $this->GetColoniesWithUnfinishedActions() as $colonyID )
            $this->ThinkForColony( $colonyID );
    }
    
}

?>