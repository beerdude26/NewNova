<?php

error_reporting(30719);

require_once "inc/game_resources/game_resources.php";

require_once "classes\class_cost.php";
require_once "classes\class_helper.php";
require_once "classes\class_resource.php";
require_once "classes\class_resourceparser.php";

// $time1 = microtime();
// $dapsap = array();
// for( $i = 0; $i < 1000; $i++)
// {
    // $kbytes = memory_get_usage() / 1024;
    // echo "current memory usage: $kbytes KB<br/>";
    // $dapsap[$i] = new ResourceParser();
// }

// $time2 = microtime();

// $result = $time2 - $time1;
// echo "time used: ".$result."<br/>";
// $peak = memory_get_peak_usage() / 1024;
// echo "peak memory usage: ".$peak." KB";



Helper::var_dump_pre( ResourceParser::Instance()->ProductionUnits() );
Helper::var_dump_pre( ResourceParser::Instance()->BuildingUnits() );
Helper::var_dump_pre( ResourceParser::Instance()->Technologies() );
Helper::var_dump_pre( ResourceParser::Instance()->DefenseUnits() );
Helper::var_dump_pre( ResourceParser::Instance()->ShipUnits() );
Helper::var_dump_pre( ResourceParser::Instance()->MissileUnits() );
Helper::var_dump_pre( ResourceParser::Instance()->Officers() );

$cost = new Cost( 100,100,100,100);
$cost1 = new Cost( 50,50,50,50);
$res = new Resource( "Test", $cost, NULL, NULL );



$cost->AddCost($res);
$cost1->AddCost($cost);

Helper::var_dump_pre( $cost );
Helper::var_dump_pre( $cost1 );

?>

