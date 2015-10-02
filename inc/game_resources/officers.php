<?php

    $GR_officers = array(
        "geologist" => array(
            "name" => "geologist",
            "max_amount" => 20,
            "prerequisite" => array(),
            "cost" => array()
            ),
        "admiral" => array(
            "name" => "admiral",
            "max_amount" => 20,
            "prerequisite" => array(),
            "cost" => array()
            ),
        "engineer" => array(
            "name" => "engineer",
            "max_amount" => 10,
            "prerequisite" => array(
                "geologist" => 5
                ),
            "cost" => array()
            ),
        "technocrat" => array(
            "name" => "technocrat",
            "max_amount" => 10,
            "prerequisite" => array(
                "admiral" => 5
                ),
            "cost" => array()
            ),
        "manufacturer" => array(
            "name" => "manufacturer",
            "max_amount" => 3,
            "prerequisite" => array(
                "geologist" => 10,
                "engineer" => 2
                ),
            "cost" => array()
            ),
        "scientist" => array(
            "name" => "scientist",
            "max_amount" => 6,
            "prerequisite" => array(
                "geologist" => 10,
                "engineer" => 2
                ),
            "cost" => array()
            ),
        "storekeeper" => array(
            "name" => "storekeeper",
            "max_amount" => 2,
            "prerequisite" => array(
                "manufacturer" => 1
                ),
            "cost" => array()
            ),
        "defender" => array(
            "name" => "defender",
            "max_amount" => 2,
            "prerequisite" => array(
                "scientist" => 1
                ),
            "cost" => array()
            ),
        "juggernaut" => array(
            "name" => "juggernaut",
            "max_amount" => 1,
            "prerequisite" => array(
                "geologist" => 20,
                "engineer" => 10,
                "manufacturer" => 3,
                "scientist" => 3,
                "storekeeper" => 2,
                "defender" => 2
                ),
            "cost" => array()
            ),
        "spy" => array(
            "name" => "spy",
            "max_amount" => 2,
            "prerequisite" => array(
                "admiral" => 10,
                "technocrat" => 5
                ),
            "cost" => array()
            ),
        "commander" => array(
            "name" => "commander",
            "max_amount" => 2,
            "prerequisite" => array(
                "admiral" => 10,
                "technocrat" => 5
                ),
            "cost" => array()
            ),
        "general" => array(
            "name" => "general",
            "max_amount" => 3,
            "prerequisite" => array(
                "commander" => 1
                ),
            "cost" => array()
            ),
        "raider" => array(
            "name" => "raider",
            "max_amount" => 1,
            "prerequisite" => array(
                "admiral" => 20,
                "technocrat" => 10,
                "spy" => 2,
                "commander" => 2,
                "general" => 3
                ),
            "cost" => array()
            ),
        "emperor" => array(
            "name" => "emperor",
            "max_amount" => 1,
            "prerequisite" => array(
                "raider" => 1,
                "juggernaut" => 1
                ),
            "cost" => array()
            )
    ); 
?>