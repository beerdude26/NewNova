<?php
class PlanetType
{
    private $_class;            // The planet class (moon, colony, ...)
    private $_id;               // Database ID
    private $_allowedBuildings = NULL; // ResourceGroup of buildings that may be built on this class of planet.
    private $_planetVariations = NULL; // An array of PlanetDatas for this class of planet
	
	public function PlanetType( $class, $id, ResourceGroup $allowedBuildings, array $planetVariations )
	{
        $this->PlanetClass( $class );
        $this->ID( $id );
		$this->AllowedBuildings( $allowedBuildings );
		$this->Variations( $planetVariations );
	}
    
    public static function GetColony()
    {
        global $GR_planetTypes;
        $id = $GR_planetTypes["colony"]["database_id"];
        
        return ResourceParser::Instance()->GetPlanetTypeByID( $id );
    }
    
    public static function GetMoon()
    {
        global $GR_planetTypes;
        $id = $GR_planetTypes["moon"]["database_id"];
        
        return ResourceParser::Instance()->GetPlanetTypeByID( $id );
    }
    
    public static function FromDatabase( $row )
	{
        $id = $row['class'];
        $planetType = ResourceParser::Instance()->GetPlanetTypeByID( $id );
        $variation = array();
        $variation[] = PlanetData::FromDatabase( $row );
		
        return new PlanetType( $planetType->PlanetClass(), $id, $planetType->AllowedBuildings(), $variation );
	}
    
    public function PlanetClass( $value = "" )
	{
		if( empty( $value ) )
			return $this->_class;
		else
			$this->_class = (string) $value;
    }
    
    public function ID( $value = "empty" )
	{
		if( $value === "empty" )
			return $this->_id;
		else
			$this->_id = (int) $value;
    }
    
    public function Variations( $value = "" )
	{
		if( empty( $value ) )
			return $this->_planetVariations;
		else
			$this->_planetVariations =& $value;
    }
    
    public function AllowedBuildings( $value = "" )
	{
		if( empty( $value ) )
			return $this->_allowedBuildings;
		else
            Helper::CheckType( $this, $value, "ResourceGroup" );
			$this->_allowedBuildings =& $value;
    }

    public function GetPlanetDataBySlot( $slot )
	{
		foreach( $this->Variations() as $planetData )
			if( $planetData->UsedInPosition( $slot ) )
				return $planetData;
                
		throw new Exception("Couldn't find any suitable planet variation for slot $slot and class $class!");
	}
	
}
?>