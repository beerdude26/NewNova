<?php

require_once "class_helper.php";
require_once "class_user.php";
require_once "class_officer.php";

// A class that contains actions a user can do.
class UserAction
{
    private $_user = NULL;  // User
    
    public function UserAction( User $user )
    {
        $this->User( $user );
    }
    
    public function User( $value = "" )
	{
		if( empty( $value ) )
			return $this->_user;
		else
		{
            Helper::checkType( $this, $value, "User" );
			$this->_user =& $value;
		}
	}
    
    public function PurchaseOfficer( Officer $officer )
    {
        // TODO: finish later. With dark matter?
    }

    public function PurchaseUnits( array $resources, array $amounts, $type )
    {   
        switch( $type )
        {
            case "SHIP":
                $rb = ResourceBuilder::GetShipListOfColony( $c );
                break;
            case "DEFENSE":
                $rb = ResourceBuilder::GetDefenseListOfColony( $c );
                break;
            case "MISSILE":
                $rb = ResourceBuilder::GetMissileListOfColony( $c );
                break;
            case "BUILDING":
                $this->PurchaseBuildings( $resources );
                break;
        }
        // TODO: check if player has enough resources
        
        
    }
    
    public function ConstructBuilding( $resource )
    {
        $colony = $this->User()->CurrentColony();
        $buildGroup = BuildingBuildGroup::FromList( array( $resource ), $colony );
        $rb = new ResourceBuilder( $buildGroup );
    }
    
    public function PurchaseBuildings( array $resources, $firstItem )
    {
        $colony = $this->User()->CurrentColony();
        $buildGroup = BuildingBuildGroup::FromList( $resources, $colony );
        $rb = new ResourceBuilder( $buildGroup );
        $rb->BuildList()->AddToDatabase();
        
        if( $firstItem )
            $rb->BuildList()->UpdateCommissionedTime();
    }
    
    public function CancelBuildings( array $resources, array $positions, $firstItem )
    {
        $c = $this->User()->CurrentColony();
        $bg = BuildingBuildGroup::FromList( $resources, $c, $positions, true );
        $rb = ResourceBuilder::GetBuildingListOfColony( $c );
        
        foreach( $bg->Members() as $item )
            if( !$rb->BuildList()->ContainsBuildItem( $item ) )
                throw new Exception("This item has already been deleted!");
        
        // Reimburse costs
        $reimbursedCosts = $bg->TotalCost();
        $c->CurrentResources()->AddCost( $reimbursedCosts );
        $c->UpdateResources();
        
        // Deduct the units
        $rb->BuildList()->DeductUnits( $bg );
        
        // Update Build times
        $rb->BuildList()->UpdateLevels();
        $rb->BuildList()->UpdateBuildTimes();
        
        if( $firstItem ) // Update Commissioned time
            $rb->BuildList()->UpdateCommissionedTime();
            
        // Update the database
        $rb->BuildList()->UpdateDatabase();
    }
    
    public function CancelUnits( array $resources, array $amounts, array $positions, $type )
    {
        
    
        $c = $this->User()->CurrentColony();
        $buildGroup = BuildGroup::FromList( $resources, $amounts, $c, $positions );
        
        switch( $type )
        {
            case "SHIP":
                $rb = ResourceBuilder::GetShipListOfColony( $c );
                break;
            case "DEFENSE":
                $rb = ResourceBuilder::GetDefenseListOfColony( $c );
                break;
            case "MISSILE":
                $rb = ResourceBuilder::GetMissileListOfColony( $c );
                break;
            case "BUILDING":
                return $this->CancelBuildings( $resources, $positions );
                break;
        }
        
        foreach( $buildGroup->Members() as $item )
            if( !$rb->BuildList()->ContainsBuildItem( $item ) )
                throw new Exception("This item has already been deleted!");
                
        
        // Reimburse costs
        $reimbursedCosts = $buildGroup->ReimbursedCosts();
        $c->CurrentResources()->AddCost( $reimbursedCosts );
        $c->UpdateResources();
        
        // Deduct the units
        $rb->BuildList()->DeductUnits( $buildGroup );
        
        // Update Build times
        $rb->BuildList()->UpdateBuildTimes();
        
        // Update the database
        $rb->UpdateDatabase();
    }
    
    public function DeconstructProductionUnit( IDResource $building, $amount )
    {
        $list = $this->User()->CurrentColony()->ProductionUnits();
        $foundBuilding = array_search( $building, $list );
        
        if( $foundBuilding != NULL )
            if( $foundBuilding->Amount() >= $amount )
            {
                $foundBuilding->Amount( $foundBuilding->Amount() - $amount );
                $this->User()->CurrentColony()->ProductionUnits()->UpdateDatabase();
            }
        // TODO: finish later, does this cost res or reimburse you?
    }
}

?>