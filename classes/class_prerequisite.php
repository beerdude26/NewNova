<?php
class Prerequisite
{
	private $_prerequisite;		// A Resource or Officer object.
	private $_amount;			// The number of times the prerequisite must be fulfilled.
	
	public function Prerequisite( $prerequisite, $amount )
	{
		$this->_prerequisite = $prerequisite;
		$this->_amount = $amount;
	}
	
	public function Item( $value = "" )
	{
		return $this->_prerequisite = ( empty( $value ) ) ? $this->_prerequisite : $value;
	}
	
	public function Amount( $value = "" )
	{
		return $this->_amount = ( empty( $value ) ) ? $this->_amount : $value;
	}
}
?>