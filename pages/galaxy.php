<?php
include "root.inc";
require_once "$ROOT/common.php";

$_SESSION['NewNovaID'] = 1;
$user = User::GetCurrentUser();

if( $user == NULL )
    return;

// Fill galaxy and system selection menu
if( $_GET )
{
    $system = $_GET['system'];
    if( isset( $_GET['systemRight'] ) )
        $system++;
    elseif( isset( $_GET['systemLeft'] ) )
        $system--;
        
    $galaxy = $_GET['galaxy'];
    if( isset( $_GET['galaxyRight'] ) )
        $galaxy++;
    elseif( isset( $_GET['galaxyLeft'] ) )
        $galaxy--;
}
else
{
    $galaxy = $user->CurrentColony()->Coordinates()->Galaxy();
    $system = $user->CurrentColony()->Coordinates()->System();
}

$coordinates = New Coordinates( $galaxy, $system, 1 );

// The Coordinates class automatically clamps values, so they're always within range
$vars['current_galaxy'] = $coordinates->Galaxy();
$vars['current_system'] = $coordinates->System();

// Render new galaxy view
$view = new GalaxyView( $user, $coordinates );
$vars['galaxy_view'] = $view->Render();

// Render galaxy page
$page = new Page( "galaxy/galaxy", $vars, "Galaxy View", "galaxy_view" );
echo $page->Display();
?>