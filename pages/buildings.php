<?php
include "root.inc";
require_once "$ROOT/common.php";

$_SESSION['NewNovaID'] = 1;
$user = User::GetCurrentUser();

if( $user == NULL )
    return;
      
if( $_GET )
{
    if( isset( $_GET['command'] ) )
        switch( $_GET['command'] )
        {
            case "insert":
                $id = $_GET['building'];
                $itemName = ResourceParser::Instance()->GetItemNameByID( $id );
                $action = new UserAction( $user );
                
                // Check if this is the first item to be inserted
                $count = BuildingBuildGroup::GetListCount( $user->CurrentColony() );
                
                $firstItem = false;
                if( $count == 0 )
                    $firstItem = true; // Only calculate commissioned time on first insertion
                
                $action->PurchaseBuildings( array( $itemName ), $firstItem );
                break;
            case "cancel":
                $id = $_GET['building'];
                $itemName = ResourceParser::Instance()->GetItemNameByID( $id );
                $pos = $_GET['build_position'];
                $action = new UserAction( $user );
                
                // Check if we want to delete the first item
                $firstItem = false;
                $visualPos = $_GET['viewposition'];
                if( $visualPos == 1 )
                    $firstItem = true; // Only recalculate commissioned time when first item is deleted
                
                $action->CancelBuildings( array( $itemName ), array( $pos ), $firstItem );
                break;
            case "update":
                $id = $_GET['building'];
                $itemName = ResourceParser::Instance()->GetItemNameByID( $id );
                $pos = $_GET['position'];
                
                Database::Instance()->SetDebugging( true );
                
                // Tap into the Think function and override it for a second
                Think::Instance()->OverrideUser( $user );
                Think::Instance()->ConstructBuildingsOf( $user->CurrentColony() );
                Think::Instance()->ReturnControl();
                break;
            case "reorder":
                $rows = explode( "_", $_GET['rows'] );
                $rb = ResourceBuilder::GetBuildingListOfColony( $user->CurrentColony(), false );
                $rb->BuildList()->Reorder( $rows );
                $rb->BuildList()->UpdateDatabase();
                break;
        }
}

// Render new buildings page
$view = new BuildingPage( $user );
$vars['build_list_overiew'] = $view->RenderRows();
$queue = $view->RenderQueue();
$vars['timer_layout'] = "{d<}{dn}:{d>}{hnn}:{mnn}:{snn}";
$vars['building_queue'] =& $queue['building_queue'];
$vars['building_timers'] =& $queue['building_timers'];

$vars['fields_used'] = $user->CurrentColony()->UsedFields();
$vars['fields_total'] = $user->CurrentColony()->MaxFields();
$vars['fields_left'] = $user->CurrentColony()->FieldsRemaining();

$extraScripts = Helper::InsertCountDown( "../" );
$extraScripts .= Helper::InsertReorder( "../" );

// Render buildings page
$page = new Page( "buildings/buildings", $vars, "Buildings", "buildings", $extraScripts );
echo $page->Display();
?>