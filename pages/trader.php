<?php
include "root.inc";
require_once "$ROOT/common.php";

$_SESSION['NewNovaID'] = 1;
$user = User::GetCurrentUser();

if( $user == NULL )
    return;

if(!$_GET)
{
	// Display the page.
	$page = new Page( "trader", NULL, "Trader", "trader" );
	echo $page->Display();
}
else
{
    global $NN_config;
    $trader = new Trader( $user->CurrentColony() );
    
    switch( $_GET['action'] )
    {
        case "SELECT_TYPE":
            if( isset( $_GET['metal'] ) )
                $trader->SetUpTradeFor("metal");
            elseif( isset( $_GET['crystal'] ) )
                $trader->SetUpTradeFor("crystal");
            else
                $trader->SetUpTradeFor("deuterium");
            break;
        case "SELL_METAL":
            $crystal = $_GET['resource1'];
            $deut = $_GET['resource2'];
            $trader->ConductTrade("metal", $crystal, $deut);
            break;
        case "SELL_CRYSTAL":
            $metal = $_GET['resource1'];
            $deut = $_GET['resource2'];
            $trader->ConductTrade("crystal", $metal, $deut);
            break;
        case "SELL_DEUTERIUM":
            $metal = $_GET['resource1'];
            $crystal = $_GET['resource2'];
            $trader->ConductTrade("deut", $metal, $crystal);
            break;
    }

}
?>
