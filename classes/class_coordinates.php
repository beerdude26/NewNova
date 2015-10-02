<?php
class Coordinates
{
	private $_galaxy = 0;		// Galaxy of the location
	private $_system = 0;		// System of the location
	private $_planet = 0;		// Planet of the location
	
	public function Coordinates( $galaxy, $system, $planet )
	{
		$this->Galaxy( $galaxy );
		$this->System( $system );
		$this->Planet( $planet );
	}
	
	public function Galaxy( $value = "" )
	{
		if( empty( $value ) )
			return $this->_galaxy;
		else
		{
			// Import the configuration file.
			global $NN_config;
            $this->_galaxy = Helper::clamp( 1, $NN_config["max_galaxies"], (int) $value );
		}
	}
	
	public function System( $value = "" )
	{
		if( empty( $value ) )
			return $this->_system;
		else
		{
			// Import the configuration file.
			global $NN_config;
            $this->_system = Helper::clamp( 1, $NN_config["max_systems"], (int) $value );
		}
	}
	
	public function Planet( $value = "" )
	{
		if( empty( $value ) )
			return $this->_planet;
		else
		{
			// Import the configuration file.
			global $NN_config;
			$this->_planet = Helper::clamp( 1, $NN_config["max_planets"], (int) $value );
		}
	}
    
    public function DistanceFrom( Coordinates $c )
    {        
        global $NN_config;
        $distance = 0;
        
        if( ( $this->Galaxy() - $c->Galaxy() ) != 0 )
            $distance = abs( $this->Galaxy() - $c->Galaxy() ) * $NN_config["galaxy_distance"];
        elseif(($this->System() - $c->System()) != 0)
            $distance = abs( $this->System() - $c->System() ) * $NN_config["system_distance"];
        elseif(($this->Planet() - $c->Planet()) != 0)
            $distance = abs( $this->Planet() - $c->Planet() ) * $NN_config["planet_distance"];
        else
            $distance = 5;

        return $distance;
    }
    
    
}
?>