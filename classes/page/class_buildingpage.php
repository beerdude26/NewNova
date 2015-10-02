<?php

class BuildingPage
{
    private $_user = NULL;
    private $_text = NULL;
    private $_queue = NULL;
    private $_time = 0;

    public function BuildingPage( User $u )
    {
        $this->_user = $u;
        $this->_queue = ResourceBuilder::GetBuildingListOfColony( $u->CurrentColony() );
        $this->_time = $this->_queue->BuildList()->CommissionedTime();
        
        // Include language files for buildings page, useful to put error messages there.
        $this->_text =& array_merge( $u->Language()->GetFilesByPage( "buildings" ), $u->Language()->GetFilesByPage( "trader" ), $u->Language()->GetFilesByPage( "research" ) );
    }
    
    public function Queue( $value = "empty" )
    {
        if( $value === "empty" )
            return $this->_queue;
        else
            $this->_queue =& $value;
    }
    
    public function RenderQueue()
    {
        $rows = "";
        $timers = "";
        $position = 1;
        foreach( $this->_queue->BuildList()->Members() as $item )
        {
            $result = $this->RenderQueueRow( $item, $position );
            $rows .= $result['row'];
            $timers .= $result['timer'];
            $position++;
        }
        
        return array( 'building_timers' => $timers, 'building_queue' => $rows );
    }
    
    private function RenderQueueRow( BuildingBuildItem $item, $position )
    {
        $vars['build_position_visual'] = $position;
        $vars['build_position_actual'] = $item->PositionInList();
        $vars['building_id'] = $item->ID();
        $vars['building_name'] = $this->_text[$item->Name()];
        $vars['building_level'] = $item->Level();
        $vars['build_item_timer_id'] = $vars['build_position_visual']."_".$item->Name()."_".$vars['building_level'];
        $vars['cancel_item'] = $this->_text['cancel_item'];
        
        if( $position == 1 ) // Only the first item gets a timer.
        {
            // The first item is also unmoveable.
            $vars['class_queue_row'] = ' class="nodrop nodrag"';
            
            $vars['build_time'] = ($item->ScheduledTime() * 1000) + $this->_time * 1000;
            $timeparts = explode(" ",microtime());
            $currenttime = bcadd(($timeparts[0]*1000),bcmul($timeparts[1],1000));
            $vars['current_time'] = $currenttime;
            $row = Page::StaticRender( "buildings/building_queue_row", $vars, $this->_user->AuthorisationLevelName() );
            $timer = Page::StaticRender( "buildings/building_queue_timer", $vars, $this->_user->AuthorisationLevelName() );
        }
        else
        {
            $row = Page::StaticRender( "buildings/building_queue_row", $vars, $this->_user->AuthorisationLevelName() );
            $timer = "";
        }

        return array( "row" => $row, "timer" => $timer );
    }
    
    public function RenderRows()
    {
        $allowedBuildings = $this->_user->CurrentColony()->PlanetType()->AllowedBuildings()->Members();

        $allBuildings = $this->_user->CurrentColony()->Buildings()->Members();

        $rows = "";
        foreach( $allBuildings as $buildingName => $building )
        {
            if( !isset( $allowedBuildings[$buildingName] ) )
                continue; // This building is not allowed on this planet class
            if( !$building->FullfillsPrerequisites( $this->_user->CurrentColony() ) )
                continue; // The required prerequisites for this building have not yet been fullfilled
                
            $rows .= $this->RenderRow( $building );       
        }
        return $rows;
    }
    
    private function RenderRow( IDResource $building )
    {
        $vars['id'] = $building->ID();
        $vars['image'] = $building->Image( "..", $this->_user->Skin() );
        $vars['building_name'] = $this->_text[$building->Name()];
        $vars['description'] = $this->_text[$building->Name()."_description"];
        
        // Calculate build cost
        // TODO: probably wrong, correct this
        $highestLevel = $this->_queue->BuildList()->GetHighestLevelForItem( $building->Name() );
        $buildCost = $building->BuildCost( $this->_user->CurrentColony(), $highestLevel + 1 );
        $cost = "";
        if( $building->Cost()->Metal() > 0 )
            $cost .= $this->_text['metal'].": ".$buildCost->Metal();
        if( $building->Cost()->Crystal() > 0)
            $cost .= " ".$this->_text['crystal'].": ".$buildCost->Crystal();
        if( $building->Cost()->Deuterium() > 0)
            $cost .= " ".$this->_text['deut'].": ".$buildCost->Deuterium();
        if( $building->Cost()->Energy() > 0)
            $cost .= " ".$this->_text['energy'].": ".$buildCost->Energy();
        $vars['cost'] = $cost;
        
        // Calculate build time
        $time = Helper::ConvertToString( $building->BuildTime( $this->_user->CurrentColony(), $buildCost ), $this->_user->Language() );
        $vars['time'] = $this->_text['build_time'].": ".$time;
        
        // Determine build button text and behaviour
        if( $building->Name() === "research_lab" )
        {
            // See if we're allowed to upgrade
            //global $NN_config;
            
            // TODO: implement research
            //$id = $this->_user->ID();
            //$query = "SELECT researchID FROM research WHERE userID = $id LIMIT 1;";
            //$result = Database::Instance()->ExecuteQuery( $query, "SELECT" );
            //if( isset( $result['researchID'] ) && $NN_config['research_lab_upgrade_during_research'] === false )
            //{
            //    $vars['build_button'] = $this->_text['research_lab_researching'];
            //    return Page::StaticRender( "buildings/building_row", $vars, $this->_user->AuthorisationLevelName() );
            //}
        }
        
        $fieldsLeft = ($this->_user->CurrentColony()->FieldsRemaining() > 0);
        
        if( !$fieldsLeft ) // Is there enough space left?
            $vars['build_button'] = $this->_text['no_fields_available'];
        else
        {
            $nextBuildLevel = $building->Amount() + 1;
            if( $fieldsLeft )
            {
                if( $this->_queue->BuildList()->Count() == 0 ) // Is there anything in the build queue?
                {
                    if( $nextBuildLevel == 1 ) // First time we build it, just put "Build" as the text
                    {
                        if( $building->CanBePaidFor( $this->_user->CurrentColony(), $nextBuildLevel ) )
                        {
                            $coloredText = $this->ColorText( $this->_text['build_first_level'], "green" );
                            $vars['build_button'] = $this->MakeBuildButton( $building, $coloredText );
                        }
                        else
                            $vars['build_button'] = $this->ColorText( $this->_text['build_first_level'], "red" );
                    }
                    else // Not the first time, put "Upgrade to level X" as the text
                    {
                        $text = $this->_text['build_next_level']." ".$nextBuildLevel;
                        if( $building->CanBePaidFor( $this->_user->CurrentColony(), $nextBuildLevel ) )
                        {
                            $coloredText = $this->ColorText( $text, "green" );
                            $vars['build_button'] = $this->MakeBuildButton( $building, $coloredText );
                        }
                        else
                            $vars['build_button'] = $this->ColorText( $text, "red" );
                    }
                }
                else // Something is already being produced, user can always build
                {
                    $coloredText = $this->ColorText( $this->_text['put_in_build_queue'], "green" );
                    $vars['build_button'] = $this->MakeBuildButton( $building, $coloredText );
                }
            }
            else // No more space left!
            {
                if( $nextBuildLevel == 1 )
                    $vars['build_button'] = $this->ColorText( $this->_text['build_first_level'], "red" );
                else
                {
                    $text = $this->_text['build_next_level']." ".$nextBuildLevel;
                    $vars['build_button'] = $this->ColorText( $text, "red" );
                }
            }
        }

        return Page::StaticRender( "buildings/building_row", $vars, $this->_user->AuthorisationLevelName() );
    }
    
    private function MakeBuildButton( Building $b, $text )
    {
        return "<a href=\"?command=insert&building=". $b->ID() ."\">". $text ."</a>";
    }
    
    private function ColorText( $text, $color )
    {
        return '<font color="'.$color.'">'.$text.'</font>';
    }
}

?>