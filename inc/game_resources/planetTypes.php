<?php

	// This array contains information about colonisable planets,
	// such as minimum and maximum temperature, the images of a planet, etc.
	// Planet image directories are built up as such: [planet_class]/[planet_type]/[planet_image_number].jpg

	$GR_planetTypes = array(
        "colony" => array(
            "database_id" => 1,
            "allowed_buildings" => array(
                "metal_mine",
                "crystal_mine",
                "deuterium_synthesizer",
                "solar_plant",
                "fusion_plant",
                "robotics_factory",
                "nano_factory",
                "shipyard",
                "metal_storage",
                "crystal_storage",
                "deuterium_storage",
                "research_lab",
                "terraformer",
                "alliance_depot",
                "missile_silo" 
            ),
            "planet_data" => array(
                "1-2-3" => array(
                    // This planet is only used in these slots of a system
                    "used_in_position" => array( 1, 2, 3 ),
                    // The planet ground type (ice, rock, water, ...)
                    "planet_type" => array( "rock" ),
                    // The available images for that combination of ground type and planet.
                    "planet_images" => array('01', '02', '03', '04', '05', '06', '07', '08', '09', '10'),
                    // Minimal option for minimal planet temperature
                    "minimal_temperature_min" => 0,
                    // Maximal option for minimal planet temperature
                    "minimal_temperature_max" => 100,
                    // The actual minimal temperature is randomly selected between above temperatures,
                    // and the maximal temperature is then the minimal temperature plus the variable below
                    "maximal_temperature" => 40
                ),
                "4-5-6" => array(
                    "used_in_position" => array( 4, 5, 6 ),
                    "planet_type" => array( "jungle" ),
                    "planet_images" => array('01', '02', '03', '04', '05', '06', '07', '08', '09', '10'),
                    "minimal_temperature_min" => -25,
                    "minimal_temperature_max" => 75,
                    "maximal_temperature" => 40
                ),
                "7-8-9" => array(
                    "used_in_position" => array( 7, 8, 9 ),
                    "planet_type" => array( "earthlike" ),
                    "planet_images" => array('01', '02', '03', '04', '05', '06', '07'),
                    "minimal_temperature_min" => -50,
                    "minimal_temperature_max" => 50,
                    "maximal_temperature" => 40
                ),
                "10-11-12" => array(
                    "used_in_position" => array( 10, 11, 12 ),
                    "planet_type" => array( "water" ),
                    "planet_images" => array('01', '02', '03', '04', '05', '06', '07', '08', '09'),
                    "minimal_temperature_min" => -75,
                    "minimal_temperature_max" => 25,
                    "maximal_temperature" => 40
                ),
                "13-14-15" => array(
                    "used_in_position" => array( 13, 14, 15 ),
                    "planet_type" => array( "ice" ),
                    "planet_images" => array('01', '02', '03', '04', '05', '06', '07', '08', '09', '10'),
                    "minimal_temperature_min" => -100,
                    "minimal_temperature_max" => 10,
                    "maximal_temperature" => 40
                ),
                "default" => array(
                    "used_in_position" => array( 1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11, 12, 13, 14, 15 ),
                    "planet_type" => array( "jungle", "gas", "earthlike", "rock", "water", "desert", "ice" ),
                    "planet_images" => array( '00', '01', '02', '03', '04', '05', '06', '07', '08', '09', '10'),
                    "minimal_temperature_min" => -120,
                    "minimal_temperature_max" => 10,
                    "maximal_temperature" => 40
                )
            )
        ),
        "moon" => array(
            "database_id" => 2,
            "allowed_buildings" => array(
                "fusion_plant",
                "robotics_factory",
                "shipyard",
                "metal_storage",
                "crystal_storage",
                "deuterium_storage",
                "alliance_depot",
                "lunar_base",
                "sensor_phalanx",
                "jump_gate" 
            ),
            "planet_data" => array(
                // TODO: put in actual values for moon in here
                "default" => array(
                    "used_in_position" => array( 1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11, 12, 13, 14, 15 ),
                    "planet_type" => array( "jungle", "gas", "earthlike", "rock", "water", "desert", "ice" ),
                    "planet_images" => array( '00', '01', '02', '03', '04', '05', '06', '07', '08', '09', '10'),
                    "minimal_temperature_min" => -120,
                    "minimal_temperature_max" => 10,
                    "maximal_temperature" => 40
                )
            )
        )
    );