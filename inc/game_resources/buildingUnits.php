<?php

    $GR_buildingUnits = array(
        "robotics_factory" => array(
            "name" => "robotics_factory",
            "cost" => array(
                            "metal"		=> 400,
							"crystal"	=> 120,
							"deuterium"	=> 200 ),
            "nextcostmodifier" => 2,
            "prerequisite" => array(),
            "database_id" => 6
            ),
        
        "nano_factory" => array(
            "name" => "nano_factory",
            "cost" => array(
                            "metal"		=> 1000000,
							"crystal"	=> 500000,
							"deuterium"	=> 100000 ),
            "nextcostmodifier" => 2,
            "prerequisite" => array(
                                "robotics_factory" => 10,
                                "computer_technology" => 10
                                ),
            "database_id" => 7
            ),
            
        "shipyard" => array(
            "name" => "shipyard",
            "cost" => array(
                            "metal"		=> 400,
							"crystal"	=> 200,
							"deuterium"	=> 100 ),
            "nextcostmodifier" => 2,
            "prerequisite" => array(
                                "robotics_factory" => 2
                                ),
            "database_id" => 8
            ),
            
        "metal_storage" => array(
            "name" => "metal_storage",
            "cost" => array(
                            "metal"		=> 2000 ),
            "nextcostmodifier" => 2,
            "prerequisite" => array(),
            "database_id" => 9
            ),
            
        "crystal_storage" => array(
            "name" => "crystal_storage",
            "cost" => array(
                            "metal"		=> 2000,
							"crystal"	=> 1000 ),
            "nextcostmodifier" => 2,
            "prerequisite" => array(),
            "database_id" => 10
            ),
            
        "deuterium_storage" => array(
            "name" => "deuterium_storage",
            "cost" => array(
                            "metal"		=> 2000,
							"crystal"	=> 2000 ),
            "nextcostmodifier" => 2,
            "prerequisite" => array(),
            "database_id" => 11
            ),
            
        "research_lab" => array(
            "name" => "research_lab",
            "cost" => array(
                            "metal"		=> 200,
							"crystal"	=> 400,
							"deuterium"	=> 200 ),
            "nextcostmodifier" => 2,
            "prerequisite" => array(),
            "database_id" => 12
            ),
        
        "terraformer" => array(
            "name" => "terraformer",
            "cost" => array(
							"crystal"	=> 50000,
							"deuterium"	=> 100000,
							"energy"	=> 1000 ),
            "nextcostmodifier" => 2,
            "prerequisite" => array(
                                "nano_factory" => 1,
                                "energy_technology" => 12
                                ),
            "database_id" => 13
            ),
            
        "alliance_depot" => array(
            "name" => "alliance_depot",
            "cost" => array(
                            "metal"		=> 20000,
							"crystal"	=> 40000 ),
            "nextcostmodifier" => 2,
            "prerequisite" => array(),
            "database_id" => 14
            ),
            
        "missile_silo" => array(
            "name" => "missile_silo",
            "cost" => array(
                            "metal"		=> 20000,
							"crystal"	=> 20000,
							"deuterium"	=> 1000 ),
            "nextcostmodifier" => 2,
            "prerequisite" => array(),
            "database_id" => 15
            ),
            
        "lunar_base" => array(
            "name" => "lunar_base",
            "cost" => array(
                            "metal"		=> 20000,
							"crystal"	=> 40000,
							"deuterium"	=> 20000 ),
            "nextcostmodifier" => 2,
            "prerequisite" => array(),
            "database_id" => 16
            ),
            
        "sensor_phalanx" => array(
            "name" => "sensor_phalanx",
            "cost" => array(
                            "metal"		=> 20000,
							"crystal"	=> 40000,
							"deuterium"	=> 20000 ),
            "nextcostmodifier" => 2,
			"prerequisite" => array(
                                "lunar_base" => 1
                                ),
            "database_id" => 17
            ),
            
        "jump_gate" => array(
            "name" => "jump_gate",
            "cost" => array(
                            "metal"		=> 2000000,
							"crystal"	=> 4000000,
							"deuterium"	=> 2000000 ),
            "nextcostmodifier" => 2,
			"prerequisite" => array(
                                "lunar_base" => 1,
                                "hyperspace_technology" => 7
                                ),
            "database_id" => 18
            )
        )
?>