<?php

    $GR_shipUnits = array(
        "small_cargo_ship" => array(
            "name" => "small_cargo_ship",
            "cost" => array(
                            "metal"		=> 2000,
							"crystal"	=> 2000 ),
            "nextcostmodifier" => 1,
            "weapon_details" => array(
                "engine_details" => array(
                    "combustion_engine" => array(
                        "fuel_usage" => 20,
                        "speed" => 5000 ),
                    "impulse_engine" => array(
                        "fuel_usage" => 40,
                        "speed" => 10000 )
                    ),
                "cargo_capacity" => 5000,
                "combat_details" => array(
                    "shield_strength" => 10,
                    "attack_strength" => 5,
                    "rapidfire_capabilities" => array(
                        "spy_probe" => 5,
                        "solar_satellite" => 5
                        )
                    ),
                ),
            "prerequisite" => array(
                                "shipyard" => 1,
                                "combustion_technology" => 2
                                ),
            "database_id" => 101
            ),
        "large_cargo_ship" => array(
            "name" => "large_cargo_ship",
            "cost" => array(
                            "metal"		=> 6000,
							"crystal"	=> 6000 ),
            "nextcostmodifier" => 1,
            "weapon_details" => array(
                "engine_details" => array(
                    "combustion_engine" => array(
                        "fuel_usage" => 20,
                        "speed" => 7500 ),
                    "impulse_engine" => array(
                        "fuel_usage" => 20,
                        "speed" => 7500 )
                    ),
                "cargo_capacity" => 25000,
                "combat_details" => array(
                    "shield_strength" => 25,
                    "attack_strength" => 5,
                    "rapidfire_capabilities" => array(
                        "spy_probe" => 5,
                        "solar_satellite" => 5
                        )
                    )
                ),
            "prerequisite" => array(
                                "shipyard" => 4,
                                "combustion_technology" => 6
                                ),
            "database_id" => 102
            ),
        "light_fighter" => array(
            "name" => "light_fighter",
            "cost" => array(
                            "metal"		=> 3000,
							"crystal"	=> 1000 ),
            "nextcostmodifier" => 1,
            "weapon_details" => array(
                "engine_details" => array(
                    "combustion_engine" => array(
                        "fuel_usage" => 20,
                        "speed" => 12500 ),
                    "impulse_engine" => array(
                        "fuel_usage" => 20,
                        "speed" => 12500 )
                    ),
                "cargo_capacity" => 50,
                "combat_details" => array(
                    "shield_strength" => 10,
                    "attack_strength" => 50,
                    "rapidfire_capabilities" => array(
                        "small_cargo_ship" => 2,
                        "spy_probe" => 5,
                        "solar_satellite" => 5
                        )
                    )
                ),
            "prerequisite" => array(
                                "shipyard" => 1,
                                "combustion_technology" => 1
                                ),
            "database_id" => 103
            ),
        "heavy_fighter" => array(
            "name" => "heavy_fighter",
            "cost" => array(
                            "metal"		=> 6000,
							"crystal"	=> 4000 ),
            "nextcostmodifier" => 1,
            "weapon_details" => array(
                "engine_details" => array(
                    "combustion_engine" => array(
                        "fuel_usage" => 75,
                        "speed" => 10000 ),
                    "impulse_engine" => array(
                        "fuel_usage" => 75,
                        "speed" => 15000 )
                    ),
                "cargo_capacity" => 100,
                "combat_details" => array(
                    "shield_strength" => 25,
                    "attack_strength" => 150,
                    "rapidfire_capabilities" => array(
                        "small_cargo_ship" => 3,
                        "spy_probe" => 5,
                        "solar_satellite" => 5
                        )
                    )
                ),
            "prerequisite" => array(
                                "shipyard" => 3,
                                "armor_technology" => 2,
                                "impulse_drive_technology" => 2
                                ),
            "database_id" => 104
            ),
        "cruiser" => array(
            "name" => "cruiser",
            "cost" => array(
                            "metal"		=> 20000,
							"crystal"	=> 7000,
							"deuterium"	=> 2000 ),
            "nextcostmodifier" => 1,
            "weapon_details" => array(
                "engine_details" => array(
                    "combustion_engine" => array(
                        "fuel_usage" => 300,
                        "speed" => 15000 ),
                    "impulse_engine" => array(
                        "fuel_usage" => 300,
                        "speed" => 15000 )
                    ),
                "cargo_capacity" => 800,
                "combat_details" => array(
                    "shield_strength" => 50,
                    "attack_strength" => 400,
                    "rapidfire_capabilities" => array(
                        "light_fighter" => 6,
                        "spy_probe" => 5,
                        "solar_satellite" => 5,
                        "rocket_launcher" => 10
                        )
                    )
                ),
            "prerequisite" => array(
                                "shipyard" => 5,
                                "ion_technology" => 2,
                                "impulse_drive_technology" => 4
                                ),
            "database_id" => 105
            ),
        "battleship" => array(
            "name" => "battleship",
            "cost" => array(
                            "metal"		=> 45000,
							"crystal"	=> 15000 ),
            "nextcostmodifier" => 1,
            "weapon_details" => array(
                "engine_details" => array(
                    "combustion_engine" => array(
                        "fuel_usage" => 500,
                        "speed" => 10000 ),
                    "impulse_engine" => array(
                        "fuel_usage" => 500,
                        "speed" => 10000 )
                    ),
                "cargo_capacity" => 1500,
                "combat_details" => array(
                    "shield_strength" => 200,
                    "attack_strength" => 1000,
                    "rapidfire_capabilities" => array(
                        "spy_probe" => 5,
                        "solar_satellite" => 5,
                        "rocket_launcher" => 8
                        )
                    )
                ),
            "prerequisite" => array(
                                "shipyard" => 7,
                                "hyperspace_drive_technology" => 4
                                ),
            "database_id" => 106
            ),
        "colony_ship" => array(
            "name" => "colony_ship",
            "cost" => array(
                            "metal"		=> 10000,
							"crystal"	=> 20000,
							"deuterium"	=> 10000 ),
            "nextcostmodifier" => 1,
            "weapon_details" => array(
                "engine_details" => array(
                    "combustion_engine" => array(
                        "fuel_usage" => 1000,
                        "speed" => 2500 ),
                    "impulse_engine" => array(
                        "fuel_usage" => 1000,
                        "speed" => 2500 )
                    ),
                "cargo_capacity" => 7500,
                "combat_details" => array(
                    "shield_strength" => 100,
                    "attack_strength" => 50,
                    "rapidfire_capabilities" => array(
                        "spy_probe" => 5,
                        "solar_satellite" => 5
                        )
                    )
                ),
            "prerequisite" => array(
                                "shipyard" => 4,
                                "impulse_drive_technology" => 3
                                ),
            "database_id" => 107
            ),
        "recycler" => array(
            "name" => "recycler",
            "cost" => array(
                            "metal"		=> 10000,
							"crystal"	=> 6000,
							"deuterium"	=> 2000 ),
            "nextcostmodifier" => 1,
            "weapon_details" => array(
                "engine_details" => array(
                    "combustion_engine" => array(
                        "fuel_usage" => 300,
                        "speed" => 2000 ),
                    "impulse_engine" => array(
                        "fuel_usage" => 300,
                        "speed" => 2000 )
                    ),
                "cargo_capacity" => 20000,
                "combat_details" => array(
                    "shield_strength" => 10,
                    "attack_strength" => 1,
                    "rapidfire_capabilities" => array(
                        "spy_probe" => 5,
                        "solar_satellite" => 5
                        )
                    )
                ),
            "prerequisite" => array(
                                "shipyard" => 4,
                                "combustion_technology" => 6,
                                "shielding_technology" => 2
                                ),
            "database_id" => 108
            ),
        "espionage_probe" => array(
            "name" => "espionage_probe",
            "cost" => array(
							"crystal"	=> 1000 ),
            "nextcostmodifier" => 1,
            "weapon_details" => array(
                "engine_details" => array(
                    "combustion_engine" => array(
                        "fuel_usage" => 1,
                        "speed" => 100000000 ),
                    "impulse_engine" => array(
                        "fuel_usage" => 1,
                        "speed" => 100000000 )
                    ),
                "cargo_capacity" => 5,
                "combat_details" => array(
                    "shield_strength" => 0,
                    "attack_strength" => 0,
                    "rapidfire_capabilities" => array(
                        "spy_probe" => 5,
                        "solar_satellite" => 5,
                        "rocket_launcher" => 0,
                        "light_laser" => 0,
                        "heavy_laser" => 0,
                        "gauss_cannon" => 0,
                        "ion_cannon" => 0,
                        "plasma_cannon" => 0,
                        "small_shield" => 0,
                        "large_shield" => 0
                        )
                    )
                ),
            "prerequisite" => array(
                                "shipyard" => 3,
                                "combustion_technology" => 3,
                                "espionage_technology" => 2
                                ),
            "database_id" => 109
            ),
        "bomber" => array(
            "name" => "bomber",
            "cost" => array(
                            "metal"		=> 50000,
							"crystal"	=> 25000,
							"deuterium"	=> 15000 ),
            "nextcostmodifier" => 1,
            "weapon_details" => array(
                "engine_details" => array(
                    "combustion_engine" => array(
                        "fuel_usage" => 1000,
                        "speed" => 4000 ),
                    "impulse_engine" => array(
                        "fuel_usage" => 1000,
                        "speed" => 5000 )
                    ),
                "cargo_capacity" => 500,
                "combat_details" => array(
                    "shield_strength" => 500,
                    "attack_strength" => 1000,
                    "rapidfire_capabilities" => array(
                        "spy_probe" => 5,
                        "solar_satellite" => 5,
                        "rocket_launcher" => 20,
                        "light_laser" => 20,
                        "heavy_laser" => 10,
                        "ion_cannon" => 10
                        )
                    )
                ),
            "prerequisite" => array(
                                "shipyard" => 8,
                                "impulse_drive_technology" => 6,
                                "plasma_technology" => 5
                                ),
            "database_id" => 110
            ),
        "solar_satellite" => array(
            "name" => "solar_satellite",
            "cost" => array(
							"crystal"	=> 2000,
							"deuterium"	=> 500 ),
            "nextcostmodifier" => 1,
            "weapon_details" => array(
                "engine_details" => array(
                    "combustion_engine" => array(
                        "fuel_usage" => 0,
                        "speed" => 0 ),
                    "impulse_engine" => array(
                        "fuel_usage" => 0,
                        "speed" => 0 )
                    ),
                "cargo_capacity" => 0,
                "combat_details" => array(
                    "shield_strength" => 10,
                    "attack_strength" => 1,
                    "rapidfire_capabilities" => array(
                        "solar_satellite" => 0
                        )
                    )
                ),
            "prerequisite" => array(
                                "shipyard" => 1
                                ),
            "database_id" => 111
            ),
        "destroyer" => array(
            "name" => "destroyer",
            "cost" => array(
                            "metal"		=> 60000,
							"crystal"	=> 50000,
							"deuterium"	=> 15000 ),
            "nextcostmodifier" => 1,
            "weapon_details" => array(
                "engine_details" => array(
                    "combustion_engine" => array(
                        "fuel_usage" => 1000,
                        "speed" => 5000 ),
                    "impulse_engine" => array(
                        "fuel_usage" => 1000,
                        "speed" => 5000 )
                    ),
                "cargo_capacity" => 2000,
                "combat_details" => array(
                    "shield_strength" => 500,
                    "attack_strength" => 2000,
                    "rapidfire_capabilities" => array(
                        "spy_probe" => 5,
                        "solar_satellite" => 5,
                        "battlecruiser" => 2,
                        "light_laser" => 10
                        )
                    )
                ),
            "prerequisite" => array(
                                "shipyard" => 9,
                                "hyperspace_technology" => 5,
                                "hyperspace_drive_technology" => 6
                                ),
            "database_id" => 112
            ),
        "battlecruiser" => array(
            "name" => "battlecruiser",
            "cost" => array(
                            "metal"		=> 30000,
							"crystal"	=> 40000,
							"deuterium"	=> 15000 ),
            "nextcostmodifier" => 1,
            "weapon_details" => array(
                "engine_details" => array(
                    "combustion_engine" => array(
                        "fuel_usage" => 250,
                        "speed" => 10000 ),
                    "impulse_engine" => array(
                        "fuel_usage" => 250,
                        "speed" => 10000 )
                    ),
                "cargo_capacity" => 750,
                "combat_details" => array(
                    "shield_strength" => 400,
                    "attack_strength" => 700,
                    "rapidfire_capabilities" => array(
                        "small_cargo_ship" => 3,
                        "large_cargo_ship" => 3,
                        "heavy_fighter" => 4,
                        "cruiser" => 4,
                        "battleship" => 7,
                        "spy_probe" => 5,
                        "solar_satellite" => 5
                        )
                    )
                ),
            "prerequisite" => array(
                                "shipyard" => 8,
                                "hyperspace_technology" => 5,
                                "hyperspace_drive_technology" => 5,
                                "laser_technology" => 12
                                ),
            "database_id" => 113
            ),
        "death_star" => array(
            "name" => "death_star",
            "cost" => array(
                            "metal"		=> 5000000,
							"crystal"	=> 4000000,
							"deuterium"	=> 1000000 ),
            "nextcostmodifier" => 1,
            "weapon_details" => array(
                "engine_details" => array(
                    "combustion_engine" => array(
                        "fuel_usage" => 1,
                        "speed" => 100 ),
                    "impulse_engine" => array(
                        "fuel_usage" => 1,
                        "speed" => 100 )
                    ),
                "cargo_capacity" => 1000000,
                "combat_details" => array(
                    "shield_strength" => 50000,
                    "attack_strength" => 200000,
                    "rapidfire_capabilities" => array(
                        "small_cargo_ship" => 250,
                        "large_cargo_ship" => 250,
                        "light_fighter" => 200,
                        "heavy_fighter" => 100,
                        "cruiser" => 33,
                        "battleship" => 30,
                        "colony_ship" => 250,
                        "recycler" => 250,
                        "spy_probe" => 1250,
                        "bomber" => 25,
                        "solar_satellite" => 1250,
                        "destroyer" => 5,
                        "death_star" => 1,
                        "battlecruiser" => 15,
                        "rocket_launcher" => 200,
                        "light_laser" => 200,
                        "heavy_laser" => 100,
                        "gauss_cannon" => 50,
                        "ion_cannon" => 100
                        )
                    )
                ),
            "prerequisite" => array(
                                "shipyard" => 12,
                                "hyperspace_technology" => 6,
                                "hyperspace_drive_technology" => 7,
                                "graviton_technology" => 1
                                ),
            "database_id" => 114
            )
        );
?>