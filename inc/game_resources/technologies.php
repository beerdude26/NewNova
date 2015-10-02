<?php

    $GR_technologies = array(
        "espionage_technology" => array(
            "name" => "espionage_technology",
            "cost" => array(
                            "metal"		=> 200,
							"crystal"	=> 1000,
							"deuterium"	=> 200 ),
            "nextcostmodifier" => 2,
            "prerequisite" => array(
                                "research_lab" => 3
                                )
            ),
            
        "computer_technology" => array(
            "name" => "computer_technology",
            "cost" => array(
                            "metal"		=> 0,
							"crystal"	=> 400,
							"deuterium"	=> 600 ),
            "nextcostmodifier" => 2,
            "prerequisite" => array(
                                "research_lab" => 1
                                )
            ),
            
        "weapons_technology" => array(
            "name" => "weapons_technology",
            "cost" => array(
                            "metal"		=> 800,
							"crystal"	=> 200 ),
            "nextcostmodifier" => 2,
            "prerequisite" => array(
                                "research_lab" => 4
                                )
            ),
            
        "shielding_technology" => array(
            "name" => "shielding_technology",
            "cost" => array(
                            "metal"		=> 200,
							"crystal"	=> 600 ),
            "nextcostmodifier" => 2,
            "prerequisite" => array(
                                "research_lab" => 6,
                                "energy_technology" => 3
                                )
            ),
            
        "armor_technology" => array(
            "name" => "armor_technology",
            "cost" => array(
                            "metal"		=> 1000 ),
            "nextcostmodifier" => 2,
            "prerequisite" => array(
                                "research_lab" => 2
                                )
            ),
            
        "energy_technology" => array(
            "name" => "energy_technology",
            "cost" => array(
                            "metal"		=> 800,
							"crystal"	=> 400 ),
            "nextcostmodifier" => 2,
            "prerequisite" => array(
                                "research_lab" => 1
                                )
            ),
            
        "hyperspace_technology" => array(
            "name" => "hyperspace_technology",
            "cost" => array(
                            "metal"		=> 4000,
							"crystal"	=> 2000 ),
            "nextcostmodifier" => 2,
            "prerequisite" => array(
                                "energy_technology" => 5,
                                "shielding_technology" => 5,
                                "research_lab" => 7
                                )
            ),
            
        "combustion_drive" => array(
            "name" => "combustion_drive",
            "cost" => array(
                            "metal"		=> 400,
							"deuterium"	=> 600 ),
            "nextcostmodifier" => 2,
            "prerequisite" => array(
                                "energy_technology" => 1,
                                "research_lab" => 1
                                )
            ),
            
        "impulse_drive" => array(
            "name" => "impulse_drive",
            "cost" => array(
                            "metal"		=> 2000,
							"crystal"	=> 4000,
							"deuterium"	=> 600 ),
            "nextcostmodifier" => 2,
            "prerequisite" => array(
                                "energy_technology" => 1,
                                "research_lab" => 2
                                )
            ),
            
        "hyperspace_drive" => array(
            "name" => "hyperspace_drive",
            "cost" => array(
                            "metal"		=> 10000,
							"crystal"	=> 20000,
							"deuterium"	=> 6000 ),
            "nextcostmodifier" => 2,
            "prerequisite" => array(
                                "hyperspace_technology" => 3,
                                "research_lab" => 7
                                )
            ),
            
        "laser_technology" => array(
            "name" => "laser_technology",
            "cost" => array(
                            "metal"		=> 200,
							"crystal"	=> 100 ),
            "nextcostmodifier" => 2,
            "prerequisite" => array(
                                "energy_technology" => 2,
                                "research_lab" => 1
                                )
            ),
            
        "ion_technology" => array(
            "name" => "ion_technology",
            "cost" => array(
                            "metal"		=> 1000,
							"crystal"	=> 300,
							"deuterium"	=> 100 ),
            "nextcostmodifier" => 2,
            "prerequisite" => array(
                                "laser_technology" => 5,
                                "energy_technology" => 4,
                                "research_lab" => 4
                                )
            ),
            
        "plasma_technology" => array(
            "name" => "plasma_technology",
            "cost" => array(
                            "metal"		=> 2000,
							"crystal"	=> 4000,
							"deuterium"	=> 1000 ),
            "nextcostmodifier" => 2,
            "prerequisite" => array(
                                "laser_technology" => 10,
                                "ion_technology" => 5,
                                "energy_technology" => 8,
                                "research_lab" => 5
                                )
            ),
            
        "intergalactic_research_network_technology" => array(
            "name" => "intergalactic_research_network_technology",
            "cost" => array(
                            "metal"		=> 240000,
							"crystal"	=> 400000,
							"deuterium"	=> 160000 ),
            "nextcostmodifier" => 2,
            "prerequisite" => array(
                                "computer_technology" => 8,
                                "hyperspace_technology" => 8,
                                "research_lab" => 10
                                )
            ),
            
        "expedition_technology" => array(
            "name" => "expedition_technology",
            "cost" => array(
                            "metal"		=> 4000,
							"crystal"	=> 8000,
							"deuterium"	=> 4000 ),
            "nextcostmodifier" => 2,
            "prerequisite" => array(
                                "computer_technology" => 4,
                                "impulse_drive_technology" => 3,
                                "research_lab" => 3
                                )
            ),
            
        "graviton_technology" => array(
            "name" => "graviton_technology",
            "cost" => array(
							"energy"	=> 300000 ),
            "nextcostmodifier" => 3,
            "prerequisite" => array(
                                "research_lab" => 12
                                )
            )
        )
?>