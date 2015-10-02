<?php

require_once("class_helper.php");

class Cost
{
	private $_metal = 0;		// Metal cost
	private $_crystal = 0;		// Crystal cost
	private $_deuterium = 0;	// Deuterium cost
	private $_energy = 0;		// Energy cost
	
	public function Cost( $metal = 0, $crystal = 0, $deuterium = 0, $energy = 0 )
	{
		$this->Metal( $metal );
		$this->Crystal( $crystal );
		$this->Deuterium( $deuterium );
		$this->Energy( $energy );
	}
	
	public function Metal( $value = "" )
	{			
		return $this->_metal = ( ( empty( $value ) ) ? $this->_metal : max( 0, $value ) );
	}
	
	public function Crystal( $value = "" )
	{			
		return $this->_crystal = ( ( empty( $value ) ) ? $this->_crystal : max( 0, $value ) );
	}
	
	public function Deuterium( $value = "" )
	{			
		return $this->_deuterium = ( ( empty( $value ) ) ? $this->_deuterium : max( 0, $value ) );
	}
	
	public function Energy( $value = "" )
	{			
		return $this->_energy = ( ( empty( $value ) ) ? $this->_energy : max( 0, $value ) );
	}
    
    public function AddCost( Cost $value )
    {
        $this->Metal( $this->Metal() + $value->Metal() );
        $this->Crystal( $this->Crystal() + $value->Crystal() );
        $this->Deuterium( $this->Deuterium() + $value->Deuterium() );
        $this->Energy( $this->Energy() + $value->Energy() );
    }
    
    public function DeductCost( Cost $value )
    {
        $this->Metal( $this->Metal() - $value->Metal() );
        $this->Crystal( $this->Crystal() - $value->Crystal() );
        $this->Deuterium( $this->Deuterium() - $value->Deuterium() );
        $this->Energy( $this->Energy() - $value->Energy() );
    }
    
    public function CostIsDeductible ( Cost $value )
    {
        $enoughMetal = ($this->Metal() - $value->Metal() >= 0);
        $enoughCrystal = ($this->Crystal() - $value->Crystal() >= 0);
        $enoughDeuterium = ($this->Deuterium() - $value->Deuterium() >= 0);
        $enoughEnergy = ($this->Energy() - $value->Energy() >= 0);
        
        if( $enoughMetal && $enoughCrystal && $enoughDeuterium && $enoughEnergy )
            return true;
        return false;
    }
    
    public function __toString()
    {
        $metal = $this->Metal();
        $crystal = $this->Crystal();
        $deut = $this->Deuterium();
        $energy = $this->Energy();
        return "<pre>Cost = ($metal,$crystal,$deut,$energy)</pre>";
    }
}
?>