<?php

// Dependencies
require_once "class_building.php";
require_once "class_cost.php";

class ProductionUnit extends Building
{
	private $_type = "METAL";			// What type of mine resource the unit produces.
	private $_count = 0;				// The number of times this production unit has been constructed on the planet.
	private $_powerlevel = 100;			// The power level this production unit is running at. Example: 10 = produces 10% of what it would produce normally, up to 100.

	public function ProductionUnit( $name, $cost, $nextcostmodifier, $prerequisite, $amount, $id, $type )
	{
		parent::Building( $name, $cost, $nextcostmodifier, $prerequisite, $amount, $id );
		$this->_type = (string) $type;
	}
    
    public static function FromIDResource( IDResource $r, $type )
    {
        return new ProductionUnit( $r->Name(), $r->Cost(), $r->NextCostModifier(), $r->Prerequisite(), $r->Amount(), $r->ID(), $type );
    }
	
	public function Type( $value = "" )
	{
		if( empty( $value ) )
			return $this->_type;
		else
			$this->_type = (string) $value;
	}
	
	public function Count( $value = "" )
	{
		if( empty( $value ) )
			return $this->_unitcount;
		else
			$this->_unitcount = (int) $value;
	}
	
	public function PowerLevel( $value = "" )
	{
		if( empty( $value ) )
			return $this->_unitpowerlevel;
		else
			$this->_unitpowerlevel = Helper::clamp( 0, 100, (int) $value );
	}
	
	public function EnergyRequired()
	{
		switch( $this->_type )
		{
			case "METAL":
				return ( 10 * $this->_count * pow( (1.1), $this->_count ) ) * ( 0.1 * $this->_powerlevel );
				break;
			case "CRYSTAL":
				return ( 10 * $this->_count * pow( (1.1), $this->_count ) ) * ( 0.1 * $this->_powerlevel );
				break;
			case "DEUTERIUM":
				return ( 20 * $this->_count * pow( (1.1), $this->_count ) ) * ( 0.1 * $this->_powerlevel );
				break;
			case "ENERGY":
				return 0;
		}
	}
	
	public function Produce( $user, $planet )
	{
		//Determine the type of resource
		switch( $this->_type )
		{
			case "METAL":
				return ProduceMetal();
				break;
			case "CRYSTAL":
				return ProduceCrystal();
				break;
			case "DEUTERIUM":
				return ProduceDeuterium($planet);
				break;
			case "ENERGY":
				return ProduceEnergy($user, $planet);
				break;
		}
	}
	
	private function ProduceMetal()
	{
		$metal = ( 30 * $this->_count * pow( (1.1), $this->_count ) ) * ( 0.1 * $this->_powerlevel );
		return new Cost( $metal, 0, 0, 0);
	}
	
	private function ProduceCrystal()
	{
		$crystal = ( 20 * $this->_count * pow( (1.1), $this->_count ) ) * ( 0.1 * $this->_powerlevel );
		return new Cost( 0, $crystal, 0, 0);
	}
	
	private function ProduceDeuterium( $planet )
	{
		$deuterium = ( ( 10 * $this->_count * pow( (1.1), $this->_count) ) * (-0.002 * $planet->GenerateMaxTemperature() + 1.28)) * ( 0.1 * $this->_powerlevel );
		return new Cost( 0, 0, $deuterium, 0);
	}
	
	private function ProduceEnergy( $user, $planet )
	{
		//Energy can be produced by three different entities: Solar Panels, Fusion Plants and Solar Satellites
		switch( $this->_name )
		{
			case "solar_panel":
				$energy = ( 20 * $this->_count * pow( (1.1), $this->_count) ) * (0.1 * $this->_powerlevel );
				return new Cost( 0, 0, 0, $energy);
				break;
			case "fusion_plant":
				$energyTechLevel = $user->Technology()->EnergyTech();
				$energy = ( 30 * $this->_count * pow( (1.05 + $energyTechLevel * 0.01), $this->_count) ) * ( 0.1 * $this->_powerlevel );
				$usedDeuterium = - ( 10 * $this->_count * pow( (1.1),$this->_count ) ) * ( 0.1 * $this->_powerlevel );
				return new Cost( 0, 0, $usedDeuterium, $energy);
				break;
			case "[SHIP]solar_satellite":
				$energy = ( ( $planet->GenerateMaxTemperature() / 4 ) + 20 ) * $this->_count * ( 0.1 * $this->_powerlevel );
				return new Cost( 0, 0, 0, $energy);
				break;
		}		
	}
}
?>