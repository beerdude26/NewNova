<?php

// Dependencies
require_once "class_helper.php";

class Officer
{
    private $_name = "NOT_SET";     // The name of the officer.
    private $_maxamount = 1;        // How many officers you can have of this type.
    private $_cost = NULL;          // Cost of the Officer (if any)
    private $_prerequisites = NULL; // Array of Prerequisite objects.
    private $_amount = 0;           // Amount of Officers you have.

	public function Officer( $name, $maxamount, $prereqs, Cost $cost, $amount )
	{
		$this->Name( $name );
		$this->Cost( $cost );
		$this->MaxAmount( $maxamount );
		$this->Amount( $amount );
		$this->Prerequisites( $prereqs );
	}
    
	public function Name( $value = "" )
	{
		if( empty( $value ) )
			return $this->_name;
		else
			$this->_name = $value;
	}
    
    public function Cost( $value = "" )
	{
		if( empty( $value ) )
			return $this->_cost;
		else
			$this->_cost =& $value;
	}
	
	public function MaxAmount( $value = "" )
	{
		if( empty( $value ) )
			return $this->_maxamount;
		else
			$this->_maxamount = (int) $value;
	}
    
    public function Amount( $value = "empty" )
	{
		if( $value === "empty" )
			return $this->_amount;
		else
			$this->_amount = (int) $value;
	}
    
    public function Prerequisites( $value = "" )
	{
		if( empty( $value ) )
			return $this->_prerequisites;
		else
			$this->_prerequisites = $value;
	}
	
}
?>