<?php
class PlanetData
{
	private $_usedInPosition;	// This planet is only used in these slots of a system
	private $_groundtype;		// The planet ground type (ice, rock, water, ...)
	private $_class;			// The planet class (moon, colony, ...)
	private $_images;			// The available images for that combination of ground type and planet.
	
	private $_generatedimage;	// The generated image.
	private $_selectedgroundtype; // The selected ground type.
	
	private $_minimum_temperature_min;	// Minimal option for minimal planet temperature
	private $_minimum_temperature_max;	// Maximal option for minimal planet temperature
	private $_minimum_temperature;		// Actual minimal temperature
	private $_maximum_temperature;		// Maximal temperature is the minimal temperature plus this
    
    private $_forGeneration = false;    // Is this data being used for generation?
	
	private function PlanetData()
	{
	}
	
	public static function ForGeneration( $positions, $type, $class, $images, $mintempmin, $mintempmax, $maxtemp )
	{
		$pData = new PlanetData();
		$pData->_usedInPosition = $positions;
		$pData->_groundtype = $type;
		$pData->_class = $class;
		$pData->_images = $images;
		$pData->_minimum_temperature_min = $mintempmin;
		$pData->_minimum_temperature_max = $mintempmax;
		$pData->_maximum_temperature = $maxtemp;
        $pData->_forGeneration = true;
		
		return $pData;
	}
	
	public static function FromDatabase( array $row )
	{
		$pData = new PlanetData();
		$pData->Image( $row['image'] );
		$pData->GroundType( $row['type'] );
		$pData->MinTemperature( $row['minimal_temperature'] );
		$pData->MaxTemperature( $row['maximal_temperature'] - $row['minimal_temperature']  );
		
		return $pData;
	}
	
	public function GenerateMinTemperature()
	{
        if( !$this->_forGeneration )
            throw new Exception("This PlanetData cannot be used for generation!");
        
		// The actual minimal temperature is randomly selected between two values
		$this->_minimum_temperature = mt_rand( $this->_minimum_temperature_min, $this->_minimum_temperature_max);
		return $this->_minimum_temperature;
	}
	
	public function GenerateMaxTemperature()
	{
        if( !$this->_forGeneration )
            throw new Exception("This PlanetData cannot be used for generation!");
    
		if( !isset( $this->_minimum_temperature ) )
            throw new Exception("No minimal temperature has been set! Call GenerateMinTemperature first!");
            
		return $this->_minimum_temperature + $this->_maximum_temperature;
	}
	
	public function MaxTemperature( $value = "" )
	{
		if( empty( $value ) )
			return $this->_maximum_temperature;
		else
			$this->_maximum_temperature = (string) $value;
	}
	
	public function MinTemperature( $value = "" )
	{
		if( empty( $value ) )
			return $this->_minimum_temperature;
		else
			$this->_minimum_temperature = (string) $value;
	}
	
	public function GroundType( $value = "" )
	{
		if( empty( $value ) )
			return $this->_selectedgroundtype;
		else
			$this->_selectedgroundtype = (string) $value;
	}
	
	// Returns a string in the form of [planet_class]/[planet_type]/[planet_image_number].jpg
	public function GenerateImage()
	{
        if( !$this->_forGeneration )
            throw new Exception("This PlanetData cannot be used for generation!");
    
		$selectedImage = $this->_class."/";
		$this->_selectedgroundtype = $this->_groundtype[ mt_rand( 0, count( $this->_groundtype ) -1 ) ];
		$selectedImage .= $this->_selectedgroundtype."/";
		$selectedImage .= $this->_images[ mt_rand( 0, count( $this->_images ) -1 ) ].".jpg";
		$this->_generatedimage = $selectedImage;
		return $selectedImage;
	}
	
	public function Image( $value = "" )
	{
		if( empty( $value ) )
			return $this->_generatedimage;
		else
			$this->_generatedimage = (string) $value;
	}
	
	public function UsedInPosition( $position )
	{
        if( !$this->_forGeneration )
            throw new Exception("This PlanetData cannot be used for generation!");
    
		return in_array( $position, $this->_usedInPosition ) ? true : false;
	}
}
?>