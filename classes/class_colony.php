<?php

require_once "class_coordinates.php";
require_once "class_database.php";
require_once "class_buildinggroup.php";
require_once "interface_comparable.php";

class Colony implements Comparable
{
	private $_name = "NO_NAME_SET";		// Name of the colony.
	private $_coordinates = NULL;		// Coordinates of the colony as a Coordinates object.
	private $_ishomecolony = FALSE;		// Is this the home colony of the user?
	private $_ismoon = FALSE;			// Is this colony a moon?
	private $_id = NULL;				// Database ID of the colony
	private $_owner = NULL;				// Owner of the colony
	private $_currentresources = NULL;	// Current resources of the colony as a Cost object.
	
	private $_usedfields = 0;			// Amount of fields already used on the colony.
	private $_maxfields = 163;			// Amount of max available fields on the colony.
	private $_diameter = 0;				// Diameter of the colony.
	
	private $_planetType = NULL;		// Type of the colony as a PlanetType object.
	private $_planetData = NULL;		// Data of the colony as a PlanetData object.
	private $_lastupdated;				// The last time this colony was updated.
	
	private $_metalstorage = 0;			// Metal storage on this colony.
	private $_crystalstorage = 0;		// Crystal storage on this colony.
	private $_deuteriumstorage = 0;		// Deuterium storage on this colony.
    
    private $_productionUnits = NULL;   // BuildingGroup
    private $_buildingUnits = NULL;     // BuildingGroup
    private $_fleet = NULL;             // ShipFleet
    private $_defenses = NULL;          // CombatGroup
    
	
	// TODO: build queues for ships and structures
	
	public static function NewColony( $name, Coordinates $coordinates, User $owner, $isHomecolony, $ismoon = FALSE )
	{
		// Import the configuration file.
		global $NN_config;
		
		// Create a new colony.
		$colony = new Colony();
		
		// Colony details
		$colony->Name( $name );
		$colony->Coordinates( $coordinates );
		$colony->Owner( $owner );
		$colony->HomeColony( $isHomecolony );
		
		// Resources
		$colony->CurrentResources( new Cost( $NN_config["starting_amount_metal"], $NN_config["starting_amount_crystal"], $NN_config["starting_amount_deuterium"], 0 ) );
		
		// Storage
		$colony->MetalStorage( $NN_config["starting_storage"] );
		$colony->CrystalStorage( $NN_config["starting_storage"] );
		$colony->DeuteriumStorage( $NN_config["starting_storage"] );
		
		// Generate available fields
		$colony->GenerateFields();
		$colony->UsedFields( 0 );
		
		// Generate the planet's properties (temperature, image)
		$colony->GeneratePlanetProperties();
		
		// Set the "Last updated" variable
		$colony->LastUpdated( time() );
		
        // TODO: dump this and make a separate class for moons perhaps, we can get planet class with PlanetType->ID()
		// Is this colony a moon?
		$colony->IsMoon( $ismoon );
        
        // Generate building units
        $colony->BuildingUnits( BuildingGroup::GenerateBuildingUnits( $colony ) );
        
        // Generate production units
        $colony->ProductionUnits( BuildingGroup::GenerateProductionUnits( $colony ) );
        
        // Generate defenses
        $colony->Defenses( CombatGroup::GenerateDefenses( $colony ) );
        
        // Generate home fleet
        $colony->Fleet( CombatGroup::GenerateShips( $colony ) );
		
		return $colony;
	}
    
    public static function FromDatabaseByID( $id, User $user = NULL )
    {
       $query = "SELECT * FROM colony WHERE ID = $id";
       $row = Database::Instance()->ExecuteQuery( $query, "SELECT" );
       return Colony::FromDatabase( $row, $user );
    }
	
	
	// TODO: Update to read from all tables
	public static function FromDatabase( array $row, User $user = NULL )
	{
		$colony = new Colony();
		
		// Colony details
		$colony->Name( $row["name"] );
		$colony->Coordinates( new Coordinates($row["galaxy_position"],$row["system_position"],$row["planet_position"]) );
		$colony->ID( $row["ID"] );
        
        if( $user == NULL )
            $colony->Owner( User::GetOwnerOfColonyID( $row["ID"], $colony ) );
        else
            $colony->Owner( $user );
            
		$colony->HomeColony( $row["is_home_colony"] );
		
		// Resources
		$rowResources = Database::Instance()->ExecuteQuery( "SELECT * FROM colony_resources WHERE colonyID = ".$row['ID'].";", "SELECT" ); 
		$colony->CurrentResources( new Cost($rowResources["metal_available"],$rowResources["crystal_available"],$rowResources["deuterium_available"],0) ); // TODO: Add energy later
		
		// Storage
		$colony->MetalStorage( $rowResources['metal_storage_limit'] );
		$colony->CrystalStorage( $rowResources['crystal_storage_limit'] );
		$colony->DeuteriumStorage( $rowResources['deuterium_storage_limit'] );
		
		// Get planet surface properties
		$rowProperties = Database::Instance()->ExecuteQuery( "SELECT * FROM colony_properties WHERE colonyID = ".$row['ID'].";", "SELECT" ); 
				
		$colony->MaxFields( $rowProperties['max_build_fields'] );
		$colony->UsedFields( $rowProperties['used_build_fields'] );
		$colony->Diameter( $rowProperties['diameter'] );
		
		$colony->PlanetType( PlanetType::FromDatabase( $rowProperties ) );
        $planetData = $colony->PlanetType()->Variations();
        $colony->PlanetData( $planetData[0] );
		
		// "Last updated" variable
		$colony->LastUpdated( $row['last_updated'] );
		
        // Is this colony a moon?
		$colony->IsMoon( $row['is_moon'] );
        
        // Get buildings
        $buildings = BuildingGroup::FromDatabaseByColony( $colony );
        
        $colony->ProductionUnits( $buildings[0] );
        $colony->BuildingUnits( $buildings[1] );

        // Get combat units
        $colony->Defenses( CombatGroup::GetDefensesOfColony( $colony ) );
        $colony->Fleet( ShipFleet::GetShipsOfColony( $colony ) );

		return $colony;
	}
	
	public function ID( $value = "" )
	{
		if( empty( $value ) )
			return $this->_id;
		else
			$this->_id = (int) $value;
	}

	public function HomeColony( $value = "empty" )
	{
		if( $value === "empty" )
			return $this->_ishomecolony ? 1 : 0;
		else
			$this->_ishomecolony = (boolean) $value;
	}
	
	public function IsMoon( $value = "empty" )
	{
		if( $value === "empty" )
			return $this->_ismoon ? 1 : 0;
		else
			$this->_ismoon = (boolean) $value;
	}
	
	public function Name( $value = "" )
	{
		if( empty( $value ) )
			return $this->_name;
		else
			$this->_name = (string) $value;
	}
	
	public function Coordinates( $value = "" )
	{
		if( empty( $value ) )
			return $this->_coordinates;
		else
		{
            Helper::checkType( $this, $value, "Coordinates" );
			$this->_coordinates =& $value;
		}
	}
	
	public function MaxFields( $value = "" )
	{
		if( empty( $value ) )
        {
            // Calculate how many fields we get extra by terraformers
            global $NN_config;
            $terraformedFields = $this->BuildingUnits()->GetMemberByName("terraformer")->Amount() * $NN_config['terraformer_field_bonus'];
			return ($this->_maxfields + $terraformedFields);
        }
		else
			$this->_maxfields = (int) $value;
	}
	
	public function UsedFields( $value = "" )
	{
		if( empty( $value ) )
			return $this->_usedfields;
		else
			$this->_usedfields = (int) $value;
	}
    
    public function FieldsRemaining()
    {
        return $this->MaxFields() - $this->UsedFields();
    }
    
    public function FieldsLeft( BuildItem $item )
    {
        if( !$item->IsBuilding() )
            throw new Exception("You can't call Colony::FieldsLeft on a non-building BuildItem!");
    
        // First, calculate how many fields are used up by the items before this item in the build list
        $fieldsLeft = $this->MaxFields() - $this->UsedFields();
        $list = $item->BuildGroup()->Members();
        $myPosition = $item->PositionInList();
        for( $i = 0; $i < $myPosition; $i++ )
            if( $list[$i]->IsBuilding() )
                $fieldsLeft -= $list[$i]->Amount();
       
        return $fieldsLeft;
    }
	
	public function Diameter( $value = "" )
	{
		if( empty( $value ) )
			return $this->_diameter;
		else
			$this->_diameter = (int) $value;
	}
	
	public function CurrentResources( $value = "" )
	{
		if( empty( $value ) )
			return $this->_currentresources;
		else
        {
            Helper::checkType( $this, $value, "Cost" );
			$this->_currentresources =& $value;
        }
	}
	
	public function PlanetType( $value = "" )
	{
		if( empty( $value ) )
			return $this->_planetType;
		else
        {
            Helper::checkType( $this, $value, "PlanetType");
			$this->_planetType =& $value;
        }
	}
    
    public function PlanetData( $value = "" )
	{
		if( empty( $value ) )
			return $this->_planetData;
		else
        {
            Helper::checkType( $this, $value, "PlanetData");
			$this->_planetData =& $value;
        }
	}
	
	public function MinTemperature( $value = "" )
	{
		if( empty( $value ) )
			return $this->_usedfields;
		else
			$this->_usedfields = (int) $value;
	}

	
	public function LastUpdated( $value = "" )
	{
		if( empty( $value ) )
			return $this->_lastupdated;
		else
			$this->_lastupdated = $value;
	}
	
	public function MetalStorage( $value = "" )
	{
		if( empty( $value ) )
			return $this->_metalstorage;
		else
			$this->_metalstorage = (int) $value;
	}
	
	public function CrystalStorage( $value = "" )
	{
		if( empty( $value ) )
			return $this->_crystalstorage;
		else
			$this->_crystalstorage = (int) $value;
	}
	
	public function DeuteriumStorage( $value = "" )
	{
		if( empty( $value ) )
			return $this->_deuteriumstorage;
		else
			$this->_deuteriumstorage = (int) $value;
	}
	
	public function Owner( $value = "" )
	{
		if( empty( $value ) )
			return $this->_owner;
		else
        {
            Helper::checkType( $this, $value, "User" );
			$this->_owner =& $value;
        }
	}
    
    public function Buildings()
    {
        return BuildingGroup::Merge( $this->ProductionUnits(), $this->BuildingUnits() );
    }
    
    public function ProductionUnits( $value = "" )
	{
		if( empty( $value ) )
			return $this->_productionUnits;
		else
        {
            Helper::checkType( $this, $value, "BuildingGroup" );
			$this->_productionUnits =& $value;
        }
	}
    
    public function BuildingUnits( $value = "" )
	{
		if( empty( $value ) )
			return $this->_buildingUnits;
		else
        {
            Helper::checkType( $this, $value, "BuildingGroup" );
			$this->_buildingUnits =& $value;
        }
	}
    
    public function Fleet( $value = "" )
	{
		if( empty( $value ) )
			return $this->_fleet;
		else
        {
            Helper::checkType( $this, $value, "ShipFleet" );
			$this->_fleet =& $value;
        }
	}
    
    public function Defenses( $value = "" )
	{
		if( empty( $value ) )
			return $this->_defenses;
		else
        {
            Helper::checkType( $this, $value, "CombatGroup" );
			$this->_defenses =& $value;
        }
	}
    
    public function SearchForPrerequisite( $itemName )
    {
        $officerResult = $this->Owner()->Officers()->GetMemberByName($itemName, true);
        $techResult = $this->Owner()->Technologies()->GetMemberByName($itemName, true);
        $buildingResult = $this->Buildings()->GetMemberByName($itemName, true);
        
        if( $officerResult != NULL )
            return $officerResult;
        if( $techResult != NULL )
            return $techResult;
        if( $buildingResult != NULL )
            return $buildingResult;
            
        return NULL;
        // Throw big-ass error
    }
    
    // Given a BuildItem, adds the item to the Colony
    public function AddUnit( BuildItem $unit )
    {
        $amount = 1;
        $name = $unit->Name();
		
        if( $unit->IsBuilding() )
        {
            $origAmount = $this->Buildings()->GetMemberByName($name)->Amount();
            $this->Buildings()->GetMemberByName($name)->Amount( $origAmount + $amount );
        }
        if( $unit->IsShip() )
        {
            $origAmount = $this->Fleet()->GetMemberByName($name)->Amount();
            $this->Fleet()->GetMemberByName($name)->Amount( $origAmount + $amount );
        }
        if( $unit->IsDefense() )
        {
            $origAmount = $this->Defenses()->GetMemberByName($name)->Amount();
            $this->Defenses()->GetMemberByName($name)->Amount( $origAmount + $amount );
        }
            // TODO: add missiles here
    }
	
	private function GenerateFields()
	{
	
		// Import the configuration file.
		global $NN_config;
		
		// If this is the user's home planet, just give him the size of the current setting.
		if( $this->_ishomecolony )
			$PlanetFields = $NN_config["initial_colony_fields"];
	
		//Generate the amount of fields on the planet.
		$ClassicFieldSetting = 163;		// The field count of the original OGame.
		$CurrentFieldSetting = $NN_config["initial_colony_fields"]; // The current field count setting.
		
		// Calculate the field ratio.
		$FieldRatio = floor ( ($ClassicFieldSetting / $CurrentFieldSetting) * 10000 ) / 100;
		
		$MinFieldsArray = $NN_config["minimum_field_sizes"]; // An array containing the minimum field sizes for every planet slot.
		$MaxFieldsArray = $NN_config["maximum_field_sizes"]; // An array containing the maximum field sizes for every planet slot.
		
		//Calculate the minimum and maximum fields of the new planet.
		$MinFields = floor ( $MinFieldsArray[$this->_coordinates->Planet() - 1] + ( $MinFieldsArray[$this->_coordinates->Planet() - 1] * $FieldRatio ) / 100 );
		$MaxFields = floor ( $MaxFieldsArray[$this->_coordinates->Planet() - 1] + ( $MaxFieldsArray[$this->_coordinates->Planet() - 1] * $FieldRatio ) / 100 );
		
		// Randomly determine the fields this planet will receive.
		$this->_maxfields = mt_rand($MinFields, $MaxFields);
		
		// Determine the diameter of the planet.
		$this->_diameter = ($this->_maxfields ^ (14 / 1.5)) * 75; 
	}
	
	private function GeneratePlanetProperties()
	{
		$planetPosition = $this->_coordinates->Planet();
        $planetType = PlanetType::GetColony();
        $planetData = $planetType->GetPlanetDataBySlot( $planetPosition );
        
        $planetData->GenerateMinTemperature();
        $planetData->GenerateImage();
        $this->PlanetType( $planetType );
		$this->PlanetData( $planetData );
	}
	
	public static function CreateHomeBase( User $owner, $name )
	{
		// Import the configuration file.
		global $NN_config;
	
		// Get the location of the last added home colony in the universe
		$result = Database::Instance()->ExecuteQuery("SELECT last_galaxy_position, last_system_position, last_planet_position FROM game_information;", "SELECT");
		
		$last_galaxy_position = $result["last_galaxy_position"];
		$last_system_position = $result["last_system_position"];
		$last_planet_position = $result["last_planet_position"];
		
		// Search for a nice spot in the galaxy
		$suitablePositionFound = FALSE;
		while ( $suitablePositionFound != TRUE )
		{
			for( $currentGalaxy = $last_galaxy_position; $currentGalaxy <= $NN_config["max_galaxies"]; $currentGalaxy++ )
			{
				for( $currentSystem = $last_system_position; $currentSystem <= $NN_config["max_systems"]; $currentSystem++ )
				{
					// Try to find a suitable planet in a system
					for( $currentPosition = $last_planet_position; $currentPosition <= 15; $currentPosition++ )
					{
						// Check if the current position is not too crowded
						$query = "SELECT COUNT(ID) FROM colony WHERE galaxy_position = $currentGalaxy AND system_position = $currentSystem;";
						$result = Database::Instance()->ExecuteQuery( $query, "SELECT" );
						
						if( $result["COUNT(ID)"] > 4 ) // Kinda crowded.
						{
							if( $currentSystem == $NN_config["max_systems"] )
								{
									// We've filled up this galaxy, go to the next
									$currentGalaxy++;
									// Start from system 1 and position 1 again
									$currentSystem = 1;
									$currentPosition = 1;
								}
								else
								{
									// There's still space in this galaxy but the current system is full
									$currentSystem++;
									$currentPosition = 1;
								}
							continue;
						}
						
						// All right, we got up till here so this system is not too crowded
						
						// Randomly choose a slot that's not too hot and not too cold
						$selectedPlanet = round( mt_rand( 4, 12 ) );
						
						// See if it's inhabited
						$query = "SELECT * FROM colony WHERE galaxy_position = $currentGalaxy AND system_position = $currentSystem AND planet_position = $selectedPlanet;";
						$result = Database::Instance()->ExecuteQuery( $query, "SELECT" );
						
						if( $result === NULL ) // There is no colony on this position, let's make one
						{
							$newColony =& Colony::NewColony( $name, new Coordinates( $currentGalaxy, $currentSystem, $selectedPlanet ), $owner, TRUE);
							Colony::AddToDatabase( $newColony );
							$suitablePositionFound = TRUE;
						}
						else // There is a colony on this position, prepare the next search position
						{
							if( $currentPosition > 2 )
							{
								if( $currentSystem == $NN_config["max_systems"] )
								{
									// We've filled up this galaxy, go to the next
									$currentGalaxy++;
									// Start from system 1 and position 1 again
									$currentSystem = 1;
									$currentPosition = 1;
								}
								else
								{
									// There's still space in this galaxy but the current system is full
									$currentSystem++;
									$currentPosition = 1;
								}
							}
							else
							{
								// There's still space in this system
								$currentPosition++;
							}
						}
						
					if( $suitablePositionFound )
					{
						$query = "UPDATE game_information SET last_galaxy_position = $currentGalaxy, last_system_position = $currentSystem, last_planet_position = $selectedPlanet;";
						Database::Instance()->ExecuteQuery( $query, "UPDATE" );
						break 3; // Exit out of the galaxy search loop
					}
					
					}
				}
			}
		}
	
	if( isset( $newColony ) )
		return $newColony;
	
	return NULL;
	}

	public static function AddToDatabase( Colony $c )
	{
        // TODO: (not a real todo) VERY IMPORTANT: get_called_class ONLY WORKS ABOVE 5.3.0!!
        Helper::checkType(  get_called_class(), $c, "Colony" );
        
        // If this is a home colony, check if one already exists.
        if( $c->HomeColony() )
        {
            $ownerID = $c->Owner()->ID();
            $query = "SELECT ID FROM colony WHERE userID = $ownerID AND is_home_colony = 1;";
            $result = Database::Instance()->ExecuteQuery( $query, "SELECT" );
            if( $result != NULL )
                throw new Exception("The user $ownerID already has a home colony!");
        }

		// Insert a row in the colony table
		$query = "INSERT INTO colony ";
		$query .= "(name, userID, is_home_colony, is_moon, galaxy_position, system_position, planet_position, last_updated) ";
		$query .= "VALUES ('".$c->Name()."', ".$c->Owner()->ID().", ".$c->HomeColony().", ".$c->IsMoon().", ".$c->Coordinates()->Galaxy().", ".$c->Coordinates()->System().", ".$c->Coordinates()->Planet().", ".$c->LastUpdated().");";
        
		$colonyID = Database::Instance()->ExecuteQuery( $query, "INSERT" );
        $c->ID( $colonyID );
		
		// Prepare rows in the other colony tables
		
		// colony_defences
		$query = "INSERT INTO colony_defences VALUES( $colonyID, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0 );";
		Database::Instance()->ExecuteQuery( $query, "INSERT" );
		
		// colony_properties
		$query = "INSERT INTO colony_properties VALUES( $colonyID, 0, ".$c->MaxFields().", ".$c->UsedFields().", ".$c->PlanetData()->MinTemperature().", ";
		$query .= $c->PlanetData()->GenerateMaxTemperature().", '".$c->PlanetData()->GroundType()."', '".$c->PlanetData()->Image()."', ";
		$query .= $c->Diameter().", 0, ".$c->PlanetType()->ID().")";
		Database::Instance()->ExecuteQuery( $query, "INSERT" );
		
		// colony_resources
		$query = "INSERT INTO colony_resources VALUES( $colonyID, ".$c->CurrentResources()->Metal().", 0, ".$c->MetalStorage();
		$query .= ", ".$c->CurrentResources()->Crystal().", 0, ".$c->CrystalStorage();
		$query .= ", ".$c->CurrentResources()->Deuterium().", 0, ".$c->DeuteriumStorage();
		$query .= ", ".$c->CurrentResources()->Energy().", ".$c->CurrentResources()->Energy();
		$query .= ", 0, 0, 0, 0, 0, 0 );";
		Database::Instance()->ExecuteQuery( $query, "INSERT" );

		// fleet table
        $c->Fleet()->AddToDatabase();
		
		// colony_structures
		$query = "INSERT INTO colony_structures VALUES( $colonyID, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0 );";
		Database::Instance()->ExecuteQuery( $query, "INSERT" );
        
        // Update Colony object
        $c->ID( $colonyID );
		
		return $colonyID;
	}
    
    public function UpdateDatabaseProperties( array $itemsToUpdate )
    {
        $query = "UPDATE colony_properties SET ";
        foreach( $itemsToUpdate as $itemName )
        {
            switch( $itemName )
            {
                CASE "points":
                    //$value = $this->Points(); // TODO: implement points
                    break;
                CASE "max_build_fields":
                    $value = $this->MaxFields();
                    break;
                CASE "used_build_fields":
                    $value = $this->UsedFields();
                    break;
                CASE "minimal_temperature":
                    $value = $this->MinimalTemperature();
                    break;
                CASE "maximal_temperature":
                    $value = $this->MaximalTemperature();
                    break;
                CASE "type":
                    $value = $this->PlanetData()->GroundType();
                    break;
                CASE "diameter":
                    $value = $this->Diameter();
                    break;
                CASE "is_destroyed":
                    //$value = $this->IsDestroyed(); // TODO: implement this
                    break;
                CASE "class":
                    $value = $this->PlanetType()->PlanetClass();
                    break;
            }
            $query .= "$itemName = $value, ";
        }
            
        // Lop off last comma
        $query = Helper::lop_off( $query, 2 );
        $query .= " WHERE colonyID = ".$this->ID().";";
        return Database::Instance()->ExecuteQuery( $query, "UPDATE" );
    }
    
    public function UpdateBuildingsInDatabase( array $names )
    {
        $query = "UPDATE colony_structures ";
        foreach( $names as $itemName )
        {
            $amount = $this->Buildings()->GetMemberByName( $itemName )->Amount();
            $query .= "SET $itemName = $amount, ";
        }
        
        if( $query != "" )
        {
            // Lop off the last semicolon because otherwise you get an SQL error
            $query = Helper::lop_off( $query, 2, " " );
            $id = $this->ID();
            $query .= "WHERE colonyID = $id;";
            Database::Instance()->ExecuteQuery( $query, "UPDATE" );
        }
    }
	
	public function AddResources( Cost $cost )
	{
        $this->_currentresources->AddCost( $cost );
	}
	
	public function RemoveResources( Cost $cost )
	{
        $this->_currentresources->DeductCost( $cost );
	}
	
	public function UpdateResources()
	{
		//Set up a query
		$query = "UPDATE colony_resources SET metal_available = ".$this->_currentresources->Metal().", crystal_available = ".$this->_currentresources->Crystal().", deuterium_available = ".$this->_currentresources->Deuterium()." WHERE colonyID = ".$this->_id.";";
		
		//Update database
		Database::Instance()->ExecuteQuery($query,"UPDATE");
	}
    
    // Interface functions
    public function Equals( self $other )
    {
        if( $this->ID() == $other->ID() )
            return true;
            
        return false;
    }
	
}
?>