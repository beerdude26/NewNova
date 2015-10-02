<?php

//Dependencies
require_once "class_cost.php";
require_once "class_resource.php";

// A generic group of resources.
class ResourceGroup
{
    private $_members = NULL;   // The members of the group.
    
    public function ResourceGroup( array $group )
    {
        $this->Members( $group );
    }
   
    // Given a list of Resource string names, returns a ResourceGroup
    public static function FromList( array $list )
    {
        $members = array();
        foreach( $list as $itemName )
            $members[] = clone ResourceParser::Instance()->GetItemByName( $itemName );
            
        return new ResourceGroup( $members );
    }
	
	public function Members( $value = "empty" )
	{
		if( $value === "empty" ) // TODO: do this for every array so you can assign empty arrays. foreach doesn't mind and sometimes it's needed.
			return $this->_members;
		else
        {
            Helper::checkTypeList( $this, $value, "Resource" );
            $this->_members =& $value;
        }
	}
    
    public function GetUniqueMembers()
    {
        $uniqueMembers = array();
        foreach( $this->_members as $item)
        {
            $itemName = $item->Name();
            if( !isset( $uniqueMembers[$itemName] ) )
                $uniqueMembers[$itemName] = $item;
        }
        
        return $uniqueMembers;
    }
    
    public function DeleteItem( Resource $item )
    {
        $index = array_search( $item, $this->_members, true );
        unset( $this->_members[$index] );
    }
    
    public function GetMemberByName( $itemName, $suppressError = false )
    {
        if( isset( $this->_members[$itemName] ) )
			return $this->_members[$itemName];
            
        if( !$suppressError )
            throw new Exception("$itemName was not found in the member list!");
        else
            return NULL;
    }
    
    // This one's only used when dealing with BuildItems
    public function GetMemberByInteger( $item )
    {
        $members =& $this->Members();
        return $members[$item];
        // TODO: big error here if not found
    }
    
    public function TotalCost()
    {
        $totalCost = new Cost( 0, 0, 0, 0 );
		foreach( $this->Members() as $resource )
			$totalCost->AddCost( $resource->Cost() );
		
		return $totalCost;
    }
    
    public function Count()
    {
        if( $this->Members() == NULL )
            return 0;
        else
            return count( $this->Members() );
    }
    
    public function TotalAmountOfItem( $itemName )
    {
        $amount = 0;
        foreach( $this->Members() as $unit )
            if( $unit->Name() === $itemName )
                $amount += $unit->Amount();
    }
	
	public function __toString()
	{
		$header = "ResourceGroup\n";
		$members = "Members:\n";
		foreach( $this->Members() as $unitKey => $unitValue )
		{
			$members .= "[KEY: $unitKey] ".$unitValue->__toString();
		}
		return $header.$members;
	}
}

?>