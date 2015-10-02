<?php

// Dependencies
require_once "class_helper.php";

class ShipEngine
{
    private $_name = "NAME_NOT_SET";    // Engine name
    private $_fuelUsage = 0;            // Fuel usage
    private $_speed = 0;                // Speed

	public function ShipEngine( $name, $fuelUsage, $speed )
	{
        $this->_name = $name;
        $this->_fuelUsage = $fuelUsage;
		$this->_speed = $speed;
	}
    
    public function Name( $value = "" )
	{
		if( empty( $value ) )
			return $this->_name;
		else
			$this->_name = $value;
	}
	
	public function FuelUsage( $value = "" )
	{
		if( empty( $value ) )
			return $this->_fuelUsage;
		else
			$this->_fuelUsage = $value;
	}
	
	public function Speed( $value = "" )
	{
		if( empty( $value ) )
			return $this->_speed;
		else
			$this->_speed = $value;
	}
}
?>