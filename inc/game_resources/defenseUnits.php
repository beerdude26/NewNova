<?php

    $GR_defenseUnits = array(
        "rocket_launcher" => array(
            "name" => "rocket_launcher",
            "cost" => array(
                            "metal"		=> 2000 ),
            "nextcostmodifier" => 1,
            "weapon_details" => array(
                "combat_details" => array(
                    "shield_strength" => 20,
                    "attack_strength" => 80,
                    "rapidfire_capabilities" => array(
                        "spy_probe" => 5,
                        "solar_satellite" => 0
                        )
                    )
                ),
            "prerequisite" => array(
                                "shipyard" => 1
                                ),
            "database_id" => 201
            ),
        "light_laser" => array(
            "name" => "light_laser",
            "cost" => array(
                            "metal"		=> 1500,
							"crystal"	=> 500 ),
            "nextcostmodifier" => 1,
            "weapon_details" => array(
                "combat_details" => array(
                    "shield_strength" => 25,
                    "attack_strength" => 100,
                    "rapidfire_capabilities" => array(
                        "spy_probe" => 5,
                        "solar_satellite" => 0
                        )
                    )
                ),
            "prerequisite" => array(
                                "shipyard" => 2,
                                "energy_technology" => 1,
                                "laster_technology" => 3
                                ),
            "database_id" => 202
            ),
        "heavy_laser" => array(
            "name" => "heavy_laser",
            "cost" => array(
                            "metal"		=> 6000,
							"crystal"	=> 2000 ),
            "nextcostmodifier" => 1,
            "weapon_details" => array(
                "combat_details" => array(
                    "shield_strength" => 100,
                    "attack_strength" => 250,
                    "rapidfire_capabilities" => array(
                        "spy_probe" => 5,
                        "solar_satellite" => 0
                        )
                    )
                ),
            "prerequisite" => array(
                                "shipyard" => 4,
                                "energy_technology" => 3,
                                "laster_technology" => 6
                                ),
            "database_id" => 203
            ),
        "gauss_cannon" => array(
            "name" => "gauss_cannon",
            "cost" => array(
                            "metal"		=> 20000,
							"crystal"	=> 15000,
							"deuterium"	=> 2000 ),
            "nextcostmodifier" => 1,
            "weapon_details" => array(
                "combat_details" => array(
                    "shield_strength" => 200,
                    "attack_strength" => 1100,
                    "rapidfire_capabilities" => array(
                        "spy_probe" => 5,
                        "solar_satellite" => 0
                        )
                    )
                ),
            "prerequisite" => array(
                                "shipyard" => 6,
                                "energy_technology" => 6,
                                "weapons_technology" => 3,
                                "shielding_technology" => 1
                                ),
            "database_id" => 204
            ),
        "ion_cannon" => array(
            "name" => "ion_cannon",
            "cost" => array(
                            "metal"		=> 2000,
							"crystal"	=> 6000 ),
            "nextcostmodifier" => 1,
            "weapon_details" => array(
                "combat_details" => array(
                    "shield_strength" => 500,
                    "attack_strength" => 150,
                    "rapidfire_capabilities" => array(
                        "spy_probe" => 5,
                        "solar_satellite" => 0
                        )
                    )
                ),
            "prerequisite" => array(
                                "shipyard" => 4,
                                "ion_technology" => 4
                                ),
            "database_id" => 205
            ),
        "plasma_turret" => array(
            "name" => "plasma_turret",
            "cost" => array(
                            "metal"		=> 50000,
							"crystal"	=> 50000,
							"deuterium"	=> 30000 ),
            "nextcostmodifier" => 1,
            "weapon_details" => array(
                "combat_details" => array(
                    "shield_strength" => 300,
                    "attack_strength" => 3000,
                    "rapidfire_capabilities" => array(
                        "spy_probe" => 5,
                        "solar_satellite" => 0
                        )
                    )
                ),
            "prerequisite" => array(
                                "shipyard" => 8,
                                "plasma_technology" => 7
                                ),
            "database_id" => 206
            ),
        "small_shield_dome" => array(
            "name" => "small_shield_dome",
            "cost" => array(
                            "metal"		=> 10000,
							"crystal"	=> 10000 ),
            "nextcostmodifier" => 1,
            "weapon_details" => array(
                "combat_details" => array(
                    "shield_strength" => 2000,
                    "attack_strength" => 1,
                    "rapidfire_capabilities" => array(
                        "spy_probe" => 5,
                        "solar_satellite" => 0
                        )
                    )
                ),
            "prerequisite" => array(
                                "shipyard" => 1,
                                "shielding_technology" => 2
                                ),
            "database_id" => 207
            ),
        "large_shield_dome" => array(
            "name" => "large_shield_dome",
            "cost" => array(
                            "metal"		=> 50000,
							"crystal"	=> 50000 ),
            "nextcostmodifier" => 1,
            "weapon_details" => array(
                "combat_details" => array(
                    "shield_strength" => 10000,
                    "attack_strength" => 1,
                    "rapidfire_capabilities" => array(
                        "spy_probe" => 5,
                        "solar_satellite" => 0
                        )
                    )
                ),
            "prerequisite" => array(
                                "shipyard" => 6,
                                "shielding_technology" => 6
                                ),
            "database_id" => 208
            )
        );
        
    $GR_missileUnits = array(
        "anti_ballistic_missile" => array(
            "name" => "anti_ballistic_missile",
            "cost" => array(
                            "metal"		=> 8000,
							"crystal"	=> 2000 ),
            "nextcostmodifier" => 1,
            "weapon_details" => array(
                "combat_details" => array(
                    "shield_strength" => 1,
                    "attack_strength" => 1,
                    "rapidfire_capabilities" => array()
                    )
                ),
            "prerequisite" => array(
                                "missile_silo" => 2,
                                "shipyard" => 1
                                ),
            "database_id" => 301
            ),
        "interplanetary_missile" => array(
            "name" => "interplanetary_missile",
            "cost" => array(
                            "metal"		=> 12500,
							"crystal"	=> 2500,
							"deuterium"	=> 10000 ),
            "nextcostmodifier" => 1,
            "weapon_details" => array(
                "combat_details" => array(
                    "shield_strength" => 1,
                    "attack_strength" => 12000,
                    "rapidfire_capabilities" => array()
                    )
                ),
            "prerequisite" => array(
                                "missile_silo" => 4,
                                "shipyard" => 1,
                                "impulse_drive_technology" => 1
                                ),
            "database_id" => 302
            )
        );
            
?>