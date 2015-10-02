<?php

// Database settings

$NN_config["servername"] = "localhost";
$NN_config["username"] = "newnova";
$NN_config["password"] = "CBA2W-DIS";
$NN_config["database"] = "newnova";
$NN_config["prefix"] = "";

// Template settings
$NN_config["template_directory"] = "/inc/game_templates";
// Default skin
$NN_config["default_skin"] = "xnova";
// Default language
$NN_config["default_language"] = "english";

// Admin settings
$NN_config["administrator_level"] = 100;

// Game world definitions
$NN_config["max_galaxies"] = 9;
$NN_config["max_systems"] = 499;
$NN_config["max_planets"] = 15;

// Game speed
$NN_config["game_speed"] = 7500;
$NN_config["fleet_speed"] = 12500;
$NN_config["fleet_speed_reduction"] = 2500;

// Planet Generation

// Field generation
$NN_config["initial_colony_fields"] = 163;
$NN_config["minimum_field_sizes"] = array (  40,  50,  55, 100,  95,  80, 115, 120, 125,  75,  80,  85,  60,  40,  50);
$NN_config["maximum_field_sizes"] = array (  90,  95,  95, 240, 240, 230, 180, 180, 190, 125, 120, 130, 160, 300, 150);

// Starting resources
$NN_config["starting_amount_metal"] = 500;
$NN_config["starting_amount_crystal"] = 500;
$NN_config["starting_amount_deuterium"] = 500;

// Starting storage
$NN_config["starting_storage"] = 1000000;

// Range of database IDs
$NN_config["building_id_range"]["from"] = 0;
$NN_config["building_id_range"]["to"] = 100;
$NN_config["ship_id_range"]["from"] = 101;
$NN_config["ship_id_range"]["to"] = 200;
$NN_config["defense_id_range"]["from"] = 201;
$NN_config["defense_id_range"]["to"] = 300;
$NN_config["missile_id_range"]["from"] = 301;
$NN_config["missile_id_range"]["to"] = 400;

// World distances
$NN_config["galaxy_distance"] = 20000;
$NN_config["system_distance"] = 5 * 19 + 2700;
$NN_config["planet_distance"] = 5 + 1000;

// Trader exchange rates
$NN_config["exchange"]["metal"]["metal"] = 1;
$NN_config["exchange"]["metal"][0] = 0.5;
$NN_config["exchange"]["metal"][1] = 0.25;
$NN_config["exchange"]["crystal"][0] = 2;
$NN_config["exchange"]["crystal"]["crystal"] = 1;
$NN_config["exchange"]["crystal"][1] = 0.5;
$NN_config["exchange"]["deuterium"][0] = 4;
$NN_config["exchange"]["deuterium"][1] = 2;
$NN_config["exchange"]["deuterium"]["deuterium"] = 1;

// How many extra fields you get per terraformer
$NN_config['terraformer_field_bonus'] = 5;

// Are you allowed to upgrade your research lab during research?
$NN_config['research_lab_upgrade_during_research'] = false;

// Copyright
$NN_config["copyright"] = "Creative Commons License TODO: ADD TO THIS";


?>