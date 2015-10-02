<?php

require_once "class_helper.php";
require_once "class_productionunit.php";
require_once "class_combatunit.php";
require_once "class_shipunit.php";
require_once "class_technology.php";
require_once "class_planettype.php";
require_once "class_cost.php";
require_once "class_rapidfire.php";
require_once "class_officer.php";
require_once "class_shipengine.php";

class ResourceParser
{
	private static $_instance = NULL;	// Singleton
	private $_productionUnits = NULL;	// Array of ProductionUnits
    private $_buildingUnits = NULL;     // Array of Resources
    private $_technologies = NULL;      // Array of Resources
    private $_shipUnits = NULL;         // Array of ShipUnits
    private $_defenseUnits = NULL;      // Array of CombatUnits
    private $_missileUnits = NULL;      // Array of CombatUnits
    private $_officers = NULL;          // Array of Officers
    private $_itemIDs = NULL;           // Mapping from ID to item name
	private $_planetTypes = NULL;		// Array of planet types
	private $_languages = NULL;			// Array of languages

	private function ResourceParser()
	{
		$this->_productionUnits = array();
        $this->_buildingUnits = array();
        $this->_technologies = array();
        $this->_shipUnits = array();
        $this->_defenseUnits = array();
        $this->_missileUnits = array();
        $this->_officers = array();
        $this->_itemIDs = array();
        
		$this->ParseProductionUnits();
        $this->ParseBuildingUnits();
        $this->ParseTechnologies();
        $this->ParseShipUnits();
        $this->ParseDefenseUnits();
        $this->ParseMissileUnits();
        $this->ParseOfficers();
		$this->ParsePlanetTypes();
		$this->ParseLanguages();
	}
	
	public static function Instance()
	{
		if( !self::$_instance )
		{
			self::$_instance = new ResourceParser();
		}
		
		return self::$_instance;
	}
    
    private function ParseCostOf( $item )
    {
        if( isset( $item["cost"]["metal"] ) )
            $metal = $item["cost"]["metal"];
        else
            $metal = 0;
            
        if( isset( $item["cost"]["crystal"] ) )
            $crystal = $item["cost"]["crystal"];
        else
            $crystal = 0;
        
        if( isset( $item["cost"]["deuterium"] ) )
            $deuterium = $item["cost"]["deuterium"];
        else
            $deuterium = 0;
            
        if( isset( $item["cost"]["energy"] ) )
            $energy = $item["cost"]["energy"];
        else
            $energy = 0;  
            
        return new Cost( $metal, $crystal, $deuterium, $energy );
    }
    
    // A generic Resource parser.
    private function ParseResource( $item )
    {
        $unitname = $item["name"];
        $unitcost = self::ParseCostOf( $item );
		$modifier = $item["nextcostmodifier"];
		$prereq = $item["prerequisite"];
        
        // Parse the prerequisites
        $prerequisites = self::ParsePrerequisites( $prereq );

        return new Resource( $unitname, $unitcost, $modifier, $prerequisites, 1 );
    }
    
    private function ParseIDResource( $item )
    {
        $id = $item["database_id"];
        $unitname = $item["name"];
        $this->_itemIDs[$id] = $unitname;
        
        return IDResource::FromResource( self::ParseResource( $item ), $id );
    }

    private function ParseBuildingUnit( $item )
    {
        return Building::FromIDResource( self::ParseIDResource( $item ) );
    }
    
    private function ParseBuildingUnits()
    {
        global $GR_buildingUnits;
        foreach( $GR_buildingUnits as $unit )
        {
            $unitName = $unit["name"];
            if (self::GetBuildingUnitByName( $unitName ) === NULL)
                $this->_buildingUnits[$unitName] = self::ParseBuildingUnit( $unit );
        }
    }
    
    private function ParseTechnology( $item )
    {
        $res = self::ParseResource( $item );
        $res->Amount( 0 );
        return $res;
    }
    
    private function ParseTechnologies()
    {
        global $GR_technologies;
        foreach( $GR_technologies as $technology )
        {
            $unitName = $technology["name"];
            if (self::GetTechnologyByName( $unitName ) == NULL)
                    $this->_technologies[$unitName] = self::ParseTechnology( $technology );
        }
    }
    
    private function ParseProductionUnit( $item )
    {
        $type = $item["type"];
        return ProductionUnit::FromIDResource( self::ParseIDResource( $item ), $type );
    }
    
	private function ParseProductionUnits()
	{
		global $GR_productionUnits;
		foreach( $GR_productionUnits as $unit )
		{
            $unitName = $unit["name"];
            if ( self::GetProductionUnitByName( $unitName ) === NULL )
                $this->_productionUnits[$unitName] = self::ParseProductionUnit( $unit );
		}
	}
    
    private function ParseShipUnit( $item )
    {
        // Parse ship-specific data
		$enginedetails = $item["weapon_details"]["engine_details"];
        $cargocapacity = $item["weapon_details"]["cargo_capacity"];
        
        // Parse engine details
        $engineList = array();
        while ( list($name, $engine) = each( $enginedetails ) ) 
        {
            $fuelUsage = $engine["fuel_usage"];
            $speed = $engine["speed"];
            $engineList[] = new ShipEngine( $name, $fuelUsage, $speed );
        }
        
        return ShipUnit::FromCombatUnit( self::ParseCombatUnit( $item ), $engineList, $cargocapacity );
    }
    
    private function ParseShipUnits()
    {
        global $GR_shipUnits;
		foreach( $GR_shipUnits as $unit )
		{
            $unitName = $unit["name"];
            if ( self::GetShipUnitByName( $unitName ) === NULL )
                $this->_shipUnits[$unitName] = self::ParseShipUnit( $unit );
		}
    }
    
    private function ParseCombatUnit( $item )
    {
        // Parse combat-specific data
        $shieldstrength = $item["weapon_details"]["combat_details"]["shield_strength"];
        $attackstrength = $item["weapon_details"]["combat_details"]["attack_strength"];
        $rf = $item["weapon_details"]["combat_details"]["rapidfire_capabilities"];
        
        // Parse the rapidfire capabilities
        $rapidfire = self::ParseRapidFireCapabilities( $rf );
        
        return CombatUnit::FromIDResource( self::ParseIDResource( $item ), $attackstrength, $shieldstrength, $rapidfire );
    }
    
    private function ParseDefenseUnit( $item )
    {
        return self::ParseCombatUnit( $item );
    }
    
    private function ParseDefenseUnits()
    {
        global $GR_defenseUnits;
		foreach( $GR_defenseUnits as $unit )
		{
            $unitName = $unit["name"];
            if ( self::GetDefenseUnitByName( $unitName ) === NULL )
                $this->_defenseUnits[$unitName] = self::ParseDefenseUnit( $unit );
		}
    }
    
    private function ParseMissileUnit( $item )
    {
        return self::ParseCombatUnit( $item );
    }
    
    private function ParseMissileUnits()
    {
        global $GR_missileUnits;
		foreach( $GR_missileUnits as $unit )
		{
            $unitName = $unit["name"];
            if ( self::GetMissileUnitByName( $unitName ) === NULL )
                $this->_missileUnits[$unitName] = self::ParseMissileUnit( $unit );
		}
    }
    
    private function ParseRapidFireCapabilities( $list )
    {
        $capabilities = array();
        
        // Get both the key and the value
        while ( list($key, $value) = each( $list ) ) 
        {
            $rapidfireObject = new RapidFire( $key, $value ); // Construct the RapidFire object
            $capabilities[$key] = $rapidfireObject; // Put it in the array
        }

        return $capabilities;
    }
    
    private function ParseOfficer( $item )
    {
        $unitname = $item["name"];
        $cost = self::ParseCostOf( $item );
		$maxamount = $item["max_amount"];
		$prereq = $item["prerequisite"];
        
        // Parse the prerequisites
        $prerequisites = self::ParsePrerequisites( $prereq );
        
        return new Officer( $unitname, $maxamount, $prerequisites, $cost, 0 );
    }
    
    private function ParseOfficers()
    {
        global $GR_officers;
		foreach( $GR_officers as $unit )
		{
            $unitName = $unit["name"];
            if ( self::GetOfficerByName( $unitName ) === NULL )
                $this->_officers[$unitName] = self::ParseOfficer( $unit );
		}
    }
    
    private function ParsePrerequisites( $list )
    {
        $prerequisites = array();
    
        // Get both the key and the value
        while ( list($key, $value) = each( $list ) ) 
        {
            $item = self::GetItemByName( $key ); // Get the Resource that corresponds to the key
            $prerequisite = new Prerequisite( $item, $value ); // Construct the Prerequisite object
            $prerequisites[$key] = $prerequisite; // Put it in the array
        }
        
        return $prerequisites;
    }
	
	private function ParsePlanetTypes()
	{
		global $GR_planetTypes;
        foreach( $GR_planetTypes as $planetType => $planetContents )
        {
            // Database ID
            $id = $planetContents['database_id'];
            
            // Allowed buildings
            $allowedBuildings = array();
            foreach( $planetContents['allowed_buildings'] as $buildingName )
                $allowedBuildings[$buildingName] = $this->GetItemByName( $buildingName );
            $rg = new ResourceGroup( $allowedBuildings );
            
            // Planet data
            $planetVariations = array();
            foreach( $planetContents['planet_data'] as $planetData )
            {
                $usedInPosition = $planetData["used_in_position"];
                $planetGroundType = $planetData["planet_type"];
                $planetImages = $planetData["planet_images"];
                $MinTemperatureMin = $planetData["minimal_temperature_min"];
                $MinTemperatureMax = $planetData["minimal_temperature_max"];
                $MaxTemperature = $planetData["maximal_temperature"];
                $planetVariations[] = PlanetData::ForGeneration( $usedInPosition, $planetGroundType, $planetType, $planetImages, $MinTemperatureMin, $MinTemperatureMax, $MaxTemperature );
            }
            
            $this->_planetTypes[$planetType] = new PlanetType( $planetType, $id, $rg, $planetVariations );
        }
	}
	
	private function ParseLanguages()
	{
		global $GR_language;
		foreach( $GR_language as $language )
		{
			$name = $language["name"];
			$encoding = $language["encoding"];
			$translator = $language["translator"];
			$this->_languages[] = new Language( $name, $encoding, $translator );
		}
	}
	
	public function PlanetTypes()
	{
		return $this->_planetTypes;
	}
	
	public function ProductionUnits()
	{
		return $this->_productionUnits;
	}
    
    public function BuildingUnits()
	{
		return $this->_buildingUnits;
	}
    
    public function Technologies()
	{
		return $this->_technologies;
	}
    
    public function ShipUnits()
	{
		return $this->_shipUnits;
	}
    
    public function DefenseUnits()
	{
		return $this->_defenseUnits;
	}
    
    public function MissileUnits()
	{
		return $this->_missileUnits;
	}
    
    public function Officers()
	{
		return $this->_officers;
	}
	
	public function Languages()
	{
		return $this->_languages;
	}
    
    public function GetItemNameByID( $id )
    {
        if ( isset ( $this->_itemIDs[$id] ) )
            return $this->_itemIDs[$id];
        
        throw new Exception("The item with ID $id is not defined!");
    }
    
    public function GetItemByID( $id )
    {
        if ( isset ( $this->_itemIDs[$id] ) )
            return $this->GetUnitByName( $this->_itemIDs[$id] );
        
        throw new Exception("The item with ID $id is not defined!");
    }
	
	public function GetProductionUnitByName( $name )
	{
        if ( isset ( $this->_productionUnits[$name] ) )
            return $this->_productionUnits[$name];
        else // Attempt to parse the item
        {
            global $GR_productionUnits;
            if( isset( $GR_productionUnits[$name] ) )
            {
                $item = $GR_productionUnits[$name]; // Look up the item in the game resources
                $this->_productionUnits[$name] = self::ParseProductionUnit( $item );
                return $this->_productionUnits[$name];
            }
            
            return null;
        }
    }
    
    public function GetBuildingUnitByName( $name )
	{
        if ( isset ( $this->_buildingUnits[$name] ) )
            return $this->_buildingUnits[$name];
        else // Attempt to parse the item
        {
            global $GR_productionUnits;
            if( isset( $GR_buildingUnits[$name] ) )
            {
                $item = $GR_buildingUnits[$name]; // Look up the item in the game resources
                $this->_buildingUnits[$name] = self::ParseResource( $item );
                return $this->_buildingUnits[$name];
            }
            
            return null;
        }
	}
    
    public function GetTechnologyByName( $name )
	{
        if ( isset ( $this->_technologies[$name] ) )
            return $this->_technologies[$name];
        else // Attempt to parse the item
        {
            global $GR_technologies;
            if( isset( $GR_technologies[$name] ) )
            {
                $item = $GR_technologies[$name]; // Look up the item in the game resources
                $this->_technologies[$name] = self::ParseTechnology( $item );
                return $this->_technologies[$name];
            }
            
            return null;
        }
	}
    
    public function GetShipUnitByName( $name )
    {
        if ( isset ( $this->_shipUnits[$name] ) )
            return $this->_shipUnits[$name];
        else // Attempt to parse the item
        {
            global $GR_shipUnits;
            if( isset( $GR_shipUnits[$name] ) )
            {
                $item = $GR_shipUnits[$name]; // Look up the item in the game resources
                $this->_shipUnits[$name] = self::ParseShipUnit( $item );
                return $this->_shipUnits[$name];
            }
            
            return null;
        }
    }
    
    public function GetDefenseUnitByName( $name )
    {
        if ( isset ( $this->_defenseUnits[$name] ) )
            return $this->_defenseUnits[$name];
        else // Attempt to parse the item
        {
            global $GR_defenseUnits;
            if( isset( $GR_defenseUnits[$name] ) )
            {
                $item = $GR_defenseUnits[$name]; // Look up the item in the game resources
                $this->_defenseUnits[$name] = self::ParseDefenseUnit( $item );
                return $this->_defenseUnits[$name];
            }
            
            return null;
        }
    }
    
    public function GetMissileUnitByName( $name )
    {
        if ( isset ( $this->_missileUnits[$name] ) )
            return $this->_missileUnits[$name];
        else // Attempt to parse the item
        {
            global $GR_missileUnits;
            if( isset( $GR_missileUnits[$name] ) )
            {
                $item = $GR_missileUnits[$name]; // Look up the item in the game resources
                $this->_missileUnits[$name] = self::ParseMissileUnit( $item );
                return $this->_missileUnits[$name];
            }
            
            return null;
        }
    }
    
    public function GetOfficerByName( $name )
    {
        if ( isset ( $this->_officers[$name] ) )
            return $this->_officers[$name];
        else // Attempt to parse the item
        {
            global $GR_officers;
            if( isset( $GR_officers[$name] ) )
            {
                $item = $GR_officers[$name]; // Look up the item in the game resources
                $this->_officers[$name] = self::ParseOfficer( $item );
                return $this->_officers[$name];
            }
            
            return null;
        }
    }
    
    public function GetCombatUnit( $combatunit )
    {
        // Try and look up the combatUnit in the other arrays
        
        $item = self::GetShipUnitByName( $combatunit );
        if ($item != NULL)
            return $item;
            
        $item = self::GetDefenseUnitByName( $combatunit );
        if ($item != NULL)
            return $item;
        
        $item = self::GetMissileUnitByName( $combatunit );
        if ($item != NULL)
            return $item;
        
        return NULL;
    }
    
    public function GetItemByName( $name )
    {
        // Try and look up the resource in the other arrays
    
        $item = self::GetProductionUnitByName( $name );
        if ($item != NULL)
            return $item;
        
        $item = self::GetBuildingUnitByName( $name );
        if ($item != NULL)
            return $item;
        
        $item = self::GetTechnologyByName( $name );
        if ($item != NULL)
            return $item;
            
        $item = self::GetCombatUnit( $name );
        if ($item != NULL)
            return $item;
            
        $item = self::GetOfficerByName( $name );
        if ($item != NULL)
            return $item;
         
        // The item isn't defined at all! Throw an error.
        return NULL;
        //throw new Exception("The Resource $name is not defined!");
    }
    
	public function GetLanguageByName( $name )
	{
		foreach( $this->_languages as $language )
			if( $language->Name() == $name )
				return $language;

		throw new Exception("Couldn't find the language $name!");
	}
    
    public function GetPlanetTypeByID( $id )
    {
        foreach( $this->_planetTypes as $planetType )
            if( $planetType->ID() == $id )
                return $planetType;
                
        throw new Exception("Couldn't find a planet type corresponding to ID $id!");
    }
}
?>