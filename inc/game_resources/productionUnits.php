<?php

	$GR_productionUnits = array(
		"metal_mine" => array(
			"name" => "metal_mine",
			"cost" => array(
							"metal"		=> 60,
							"crystal"	=> 15 ),
			"nextcostmodifier" => 3/2,
			"prerequisite" => array(),
            "database_id" => 1,
			"type" => "METAL"),
			
		"crystal_mine" => array(
			"name" => "crystal_mine",
			"cost" => array(
							"metal"		=> 48,
							"crystal"	=> 24 ),
			"nextcostmodifier" => 1.6,
			"prerequisite" => array(),
            "database_id" => 2,
			"type" => "CRYSTAL"),
			
		"deuterium_synthesizer" => array(
			"name" => "deuterium_synthesizer",
			"cost" => array(
							"metal"		=> 225,
							"crystal"	=> 75 ),
			"nextcostmodifier" => 3/2,
			"prerequisite" => array(),
            "database_id" => 3,
			"type" => "DEUTERIUM"),
			
		"solar_plant" => array(
			"name" => "solar_plant",
			"cost" => array(
							"metal"		=> 75,
							"crystal"	=> 30 ),
			"nextcostmodifier" => 3/2,
			"prerequisite" => array(),
            "database_id" => 4,
			"type" => "ENERGY"),
        
        "fusion_plant" => array(
			"name" => "fusion_plant",
			"cost" => array(
							"metal"		=> 900,
							"crystal"	=> 360,
							"deuterium"	=> 180 ),
			"nextcostmodifier" => 1.8,
			"prerequisite" => array(
                            "deuterium_synthesizer" => 5,
                            "energy_technology" => 3
                            ),
            "database_id" => 5,
			"type" => "ENERGY"),
        
        "[SHIP]solar_satellite" => array(
			"name" => "[SHIP]solar_satellite",
			"cost" => array(
							"metal"		=> 0,
							"crystal"	=> 0,
							"deuterium"	=> 0,
							"energy"	=> 0 ),
			"nextcostmodifier" => 3/2,
			"prerequisite" => array(),
            "database_id" => 0,
			"type" => "ENERGY")
		);
	
?>