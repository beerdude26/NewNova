<?php

//Dependencies
require_once "class_resourcegroup.php";

// A group of buildings.
class BuildingGroup extends ResourceGroup
{
    private $_colony = NULL;     // Colony object

    private function BuildingGroup() {}
    
    public function Colony( $value = "" )
	{
		if( empty( $value ) )
			return $this->_colony;
		else
        {
            Helper::checkType( $this, $value, "Colony" );
			$this->_colony =& $value;
        }
	}
    
    public static function NewBuildingGroup( array $list, Colony $colony )
    {
        $gr = new BuildingGroup();
        $gr->Members( $list );
        $gr->Colony( $colony );
        return $gr;
    }
    
    // Given a list of IDResource string names, returns a BuildingGroup
    public static function FromList( array $list, Colony $colony )
    {
        return BuildingGroup::FromResourceGroup( IDResource::CreateListFrom( $list ), $colony );
    }
    
    public static function FromResourceGroup( array $group, Colony $colony )
    {
        return new BuildingGroup( $group->Members(), $colony );
    }
    
    // Generates a BuildingGroup with all production units with their amounts set to 0
    public static function GenerateProductionUnits( Colony $colony )
    {
        $list = ResourceParser::Instance()->ProductionUnits();
        return BuildingGroup::GenerateGroup( $list, $colony );
    }
    
    // Generates a BuildingGroup with all other units with their amounts set to 0
    public static function GenerateBuildingUnits( Colony $colony )
    {
        $list = ResourceParser::Instance()->BuildingUnits();
        return BuildingGroup::GenerateGroup( $list, $colony );
    }
    
    private static function GenerateGroup( array $list, Colony $colony )
    {
        foreach( $list as $unit )
            $unit->Amount( 0 );
        $newGroup = new BuildingGroup();
        $newGroup->Members( $list );
        $newGroup->Colony( $colony );
        return $newGroup;
    }
    
    public static function Merge( BuildingGroup $b1, BuildingGroup $b2 )
    {
        if( $b1->Colony()->Equals( $b2->Colony() ) )
            return BuildingGroup::NewBuildingGroup( array_merge( $b1->Members(), $b2->Members() ), $b1->Colony() );
    }
    
    public static function FromDatabaseByColony( Colony $colony )
    {
        $id = $colony->ID();
        $query = "SELECT * FROM colony_structures WHERE colonyID = $id;";
        $row = Database::Instance()->ExecuteQuery( $query, "SELECT" );
        $row = array_slice( $row, 1 ); // Dump the colonyID
        $productionUnitsRow = array_slice( $row, 0, 5 ); // TODO: put solar sats here?
        $buildingsRow = array_slice( $row, 5 );
        
        $productionUnits = BuildingGroup::FromDatabase( $productionUnitsRow, $colony );
        $buildings = BuildingGroup::FromDatabase( $buildingsRow, $colony );
        
        return array( $productionUnits, $buildings );
    }

    public static function FromDatabase( array $row, Colony $colony )
    {
        $members = array();
        foreach( $row as $rowName => $rowValue )
        {
            $buildObject = clone ResourceParser::Instance()->GetItemByName( $rowName );
            $buildObject->Amount( $rowValue );
            $members[$rowName] = $buildObject;
        }
        
        $newGroup = new BuildingGroup();
        $newGroup->Members( $members );
        $newGroup->Colony( $colony );
        return $newGroup;
    }
    
    public function AddToDatabase()
    {
        $list = $this->Members();
        $colonyID = $this->Colony()->ID();
        
        $query = "INSERT INTO colony (colonyID, ";
        foreach( $list as $building )
            $query .= $building->Name().", ";
            
        // Lop off the last comma, replace with )
        $query = Helper::lop_off( $query, 2, ")" );
        
        $query .= "VALUES( $colonyID, ";
        foreach( $list as $building )
            $query .= $building->Amount().", ";
            
        $query = Helper::lop_off( $query, 2, ");" );
        
        Database::Instance()->ExecuteQuery( $query, "INSERT" );
    }
    
    // Expects a user and a list of booleans the size of all buildings
    public function UpdateDatabase()
    {
        $list = $this->Members();
        $colonyID = $this->Colony()->ID();

        $changesNeeded = false;
        
        $query = "UPDATE colony SET ";
        foreach( $list as $building )
        {   
            $query .= $building->Name()." = ".$building->Amount().", ";
            $changesNeeded = true;
        }
            
        if( $changesNeeded )
        {
            // Lop off last comma
            $query = Helper::lop_off( $query, 2 );
            $query .= " WHERE userID = $userID";
        
            Database::Instance()->ExecuteQuery( $query, "UPDATE" );
        }
    }
}

?>