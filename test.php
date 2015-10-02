<?php

error_reporting(30719);

require_once "common.php";

Database::Instance()->ExecuteQuery("TRUNCATE TABLE colony", NULL);
Database::Instance()->ExecuteQuery("TRUNCATE TABLE colony_defences", NULL);
Database::Instance()->ExecuteQuery("TRUNCATE TABLE colony_properties", NULL);
Database::Instance()->ExecuteQuery("TRUNCATE TABLE colony_resources", NULL);
Database::Instance()->ExecuteQuery("TRUNCATE TABLE colony_structures", NULL);
Database::Instance()->ExecuteQuery("TRUNCATE TABLE fleet", NULL);
Database::Instance()->ExecuteQuery("TRUNCATE TABLE user", NULL);
Database::Instance()->ExecuteQuery("TRUNCATE TABLE production", NULL);
Database::Instance()->ExecuteQuery("TRUNCATE TABLE user_officers", NULL);
Database::Instance()->ExecuteQuery("TRUNCATE TABLE user_technology", NULL);
Database::Instance()->ExecuteQuery("TRUNCATE TABLE scheduled_transports", NULL);
Database::Instance()->SetDebugging( true );

$user = User::NewUser( "Beerdude26", "aSimplePassword", 100, "Beerdude26@gmail.com", "alternate_email" );
$colony = Colony::NewColony( "Nigron", new Coordinates(100,50,7), $user, FALSE );
Colony::AddToDatabase( $colony );
echo "<br/>";
echo "<br/>";

echo "Current colony:";
Helper::var_dump_pre( $user->CurrentColony()->ID() );

$units = array( "light_fighter" => 10, "small_cargo_ship" => 20, "cruiser" => 10 );
$extrafleet = ShipFleet::FromList( $units, $user->CurrentColony(), 0 );

//Helper::var_dump_pre( $user->CurrentColony()->Fleet() );

echo "Adding some extra units to original fleet: ";
$user->CurrentColony()->Fleet()->AddToFleet( $extrafleet );
$user->CurrentColony()->Fleet()->UpdateDatabase();

$t_units = array( "light_fighter" => 5, "small_cargo_ship" => 5, "cruiser" => 3 );
$t_fleet = ShipFleet::FromList( $t_units, $user->CurrentColony(), 0 );

echo "Splitting up some units from the original fleet: ";
$fleets = $user->CurrentColony()->Fleet()->SplitFleet( $t_fleet );

// Let's send a part of the fleet on a transport mission
$transport = Transportation::NewTransportation( $fleets["new_fleet"], $colony );
//$transport->AddToDatabase();
$transport->Validate();
$transport->AddToDatabase();

//$user->CurrentColony()->Fleet()->DeductFromFleet( $extrafleet );

//Helper::var_dump_pre( $user->CurrentColony()->Fleet() );


//Helper::var_dump_pre( $user->Officers() );

//Helper::var_dump_pre( $user->CurrentColony()->BuildingUnits() );

echo memory_get_peak_usage() / 1024;

/*$user->Technologies()->ChangeTechnology( "espionage_technology", 5 );

$changes = $user->Technologies()->Changes();
$techs = $user->Technologies()->Members();


Helper::var_dump_pre( "espionage_technology" );
Helper::var_dump_pre( $techs["espionage_technology"] );
Helper::var_dump_pre( $changes["espionage_technology"] );*/

//$user->Technologies()->UpdateDatabase();

/*$_SESSION['NewNovaID'] = 19;

$user = User::GetCurrentUser();
$colony = $user->CurrentColony();

$shiplist = ResourceBuilder::GetShipListOfColony( $colony );

foreach( $shiplist->Members() as $item )
{
    Helper::var_dump_pre( $item );
}*/

$actions = new UserAction( $user );

$bldgs = array( "robotics_factory", "robotics_factory", "shipyard", "metal_mine" );
$b_amounts = array( 1, 1, 1, 1 );
$actions->PurchaseUnits( $bldgs, $b_amounts );

$defs = array( "plasma_turret", "light_laser", "rocket_launcher", "plasma_turret", "small_shield_dome", "light_laser", "plasma_turret" );
$d_amounts = array( 5, 10, 12, 50, 2, 5, 20 );
$actions->PurchaseUnits( $defs, $d_amounts );


$resources = array( "light_fighter", "cruiser", "battleship", "light_fighter" );
$amounts = array(10,5,7,10);
$actions->PurchaseUnits( $resources, $amounts );

$bldgs = array( "robotics_factory", "robotics_factory", "shipyard", "metal_mine" );
$b_amounts = array( 2, 1, 3, 1 );
$actions->PurchaseUnits( $bldgs, $b_amounts );

$defs = array( "plasma_turret", "light_laser", "rocket_launcher", "plasma_turret", "small_shield_dome", "light_laser", "plasma_turret" );
$d_amounts = array( 5, 10, 12, 50, 2, 5, 20 );
$actions->PurchaseUnits( $defs, $d_amounts );


$resources = array( "light_fighter", "cruiser", "battleship", "light_fighter" );
$amounts = array(10,5,7,10);
$actions->PurchaseUnits( $resources, $amounts );

$bldgs = array( "robotics_factory", "robotics_factory", "shipyard", "metal_mine" );
$b_amounts = array( 2, 1, 3, 1 );
$actions->PurchaseUnits( $bldgs, $b_amounts );

$defs = array( "plasma_turret", "light_laser", "rocket_launcher", "plasma_turret", "small_shield_dome", "light_laser", "plasma_turret" );
$d_amounts = array( 5, 10, 12, 50, 2, 5, 20 );
$actions->PurchaseUnits( $defs, $d_amounts );

$resources = array( "light_fighter", "cruiser", "battleship", "light_fighter" );
$amounts = array(10,5,7,10);
$actions->PurchaseUnits( $resources, $amounts );

$bldgs = array( "robotics_factory", "nano_factory", "shipyard", "metal_mine" );
$b_amounts = array( 5, 10, 20, 30 );
$actions->PurchaseUnits( $bldgs, $b_amounts );

// Right, that all works, let's cancel some stuff 

$stuffToBeCanceled = array( "robotics_factory", "nano_factory" );
$amountsCanceled = array( 5, 10 );
$positions = array( 12, 13 );

$actions->CancelUnits( $stuffToBeCanceled, $amountsCanceled, $positions );


/*$_SESSION['NewNovaID'] = 43;

$user = User::GetCurrentUser();

Helper::var_dump_pre( $user );

echo $user;

$colony = $user->CurrentColony();*/


/*$resources = array("light_fighter", "cruiser", "battleship", "light_fighter");
$amounts = array( 10,20,30,40 );
$fleet1 = new ShipFleet( ShipUnit::MakeListFrom( $resources, $amounts ), $colony, 0 );

$resources2 = array("light_fighter", "cruiser", "battleship", "light_fighter", "death_star");
$amounts2 = array( 10,20,30,40,50 );
$fleet2 = new ShipFleet( ShipUnit::MakeListFrom( $resources2, $amounts2 ), $colony, 0 );

$battle = Battle::NewBattle( $fleet1, $fleet2, 0, 5000);

Helper::var_dump_pre( $battle );*/

?>