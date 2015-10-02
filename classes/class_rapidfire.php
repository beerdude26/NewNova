<?php

// Dependencies
require_once "class_helper.php";
require_once "class_resourceparser.php";


class RapidFire
{
    private $_unit = "NOT_SET"; // The unit that is vulnerable to rapidfire.
    private $_amount = 1;       // The rapidfire bonus. 1 by default.

	public function RapidFire( $unit, $amount )
	{
		$this->Unit( $unit );
		$this->Amount( $amount );
	}
    
    public function GetActualUnit()
    {
        return ResourceParser::Instance()->GetCombatUnit( $this->_unit );
    }
	
	public function Unit( $value = "" )
	{
		if( empty( $value ) )
			return $this->_unit;
		else
			$this->_unit = $value;
	}
	
	public function Amount( $value = "" )
	{
		if( empty( $value ) )
			return $this->_amount;
		else
			$this->_amount = (int) $value;
	}
}
?>