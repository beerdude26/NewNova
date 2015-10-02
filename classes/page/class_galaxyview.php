<?php

class GalaxyView
{
    private $_user = NULL;
    private $_text = NULL;
    private $_coords = NULL;
    
    public function GalaxyView( User $u, Coordinates $c = NULL )
    {
        $this->_user = $u;
        
        if( $c == NULL )
            $this->_coords = $u->CurrentColony()->Coordinates();
        else
            $this->_coords = $c;

        // Include language files for galaxy page, useful to put error messages there.
        $this->_text =& $u->Language()->GetFilesByPage( "galaxy_view" );
    }
    
    public function Render()
    {
        return $this->RenderTitle().$this->RenderRows().$this->RenderFooter();
    }
    
    private function RenderTitle()
    {
        $vars['galaxy'] = $this->_coords->Galaxy();
        $vars['system'] = $this->_coords->System();
        
        
        
        return Page::StaticRender( "galaxy/galaxy_title", array_merge( $this->_text, $vars ), $this->_user->AuthorisationLevelName() );
    }
    
    private function RenderRows()
    {
        $galaxy = $this->_coords->Galaxy();
        $system = $this->_coords->System();
        $query = "SELECT * FROM colony WHERE galaxy_position = $galaxy AND system_position = $system;";
        
        $results = Database::Instance()->ExecuteQuery( $query, "SELECT" );
        
        // Fill up the rows array with actual colonies
        $rows = array();
        if( isset( $results['ID'] ) ) // Single result
            $rows[$results['planet_position']] = $this->RenderRowOfColony( Colony::FromDatabaseByID( $results['ID'] ) );
        elseif( $results != NULL ) // Not null, not single --> list of arrays
            foreach( $results as $row )
                $rows[$row['planet_position']] = $this->RenderRowOfColony( Colony::FromDatabaseByID( $row['ID'] ) );
                
        // Fill up the gaps with empty rows
        global $NN_config;
        for( $i = 1; $i <= $NN_config['max_planets']; $i++ )
            if( !isset( $rows[$i] ) )
               $rows[$i] = $this->RenderEmptyRow( $i );
        
        // Concatenate everything together
        $text = "";
        for( $i = 1; $i <= $NN_config['max_planets']; $i++ )
            $text .= $rows[$i];

        return $text;
    }
    
    private function RenderEmptyRow( $pos )
    {
        $positionData = array( "tabindex" => ($pos + 1), "planet_position" => $pos );
        $planetImage['planet_image'] = "";
        $nameData = "";
        $nameData['inactivity'] = "";
        $nameData['planet_name'] = "";
        $nameData['color_name'] = "";
        $moonImage['moon_image'] = "";
        $debrisData['debris'] = "";
        $ownerData['owner'] = "";
        $allianceData['alliance'] = "";
        $actionData['actions'] = "";
        
        $vars = array_merge( $positionData, $planetImage, $nameData, $moonImage, $debrisData, $ownerData, $allianceData, $actionData );
        return Page::StaticRender( "galaxy/galaxy_row", $vars, $this->_user->AuthorisationLevelName() );
    }
    
    private function RenderRowOfColony( Colony $c )
    {
        $positionData = array( "tabindex" => ($c->Coordinates()->Planet() + 1), "planet_position" => $c->Coordinates()->Planet() );
        $planetImage['planet_image'] = $c->PlanetData()->Image();
        $nameData = $this->GetPlanetNameDataOfColony( $c );
        $moonImage['moon_image'] = $c->PlanetData()->Image(); // TODO: PLACEHOLDER, put actual moon image here
        $debrisData['debris'] = "Debris"; // TODO: PLACEHOLDER
        $ownerData['owner'] = $c->Owner()->Username(); // TODO: PLACEHOLDER
        $allianceData['alliance'] = "Alliance"; // TODO: PLACEHOLDER
        $actionData['actions'] = "Actions"; // TODO: PLACEHOLDER, implement PMs and alliances and buddies before doing this
        
        $vars = array_merge( $positionData, $planetImage, $nameData, $moonImage, $debrisData, $ownerData, $allianceData, $actionData );
        return Page::StaticRender( "galaxy/galaxy_row", $vars, $this->_user->AuthorisationLevelName() );
    }
    
    private function GetPlanetNameDataOfColony( $c )
    {
        // TODO: colorize name for alliances
        
        $user = $c->Owner();
        $lastOnline = $user->LastOnline();
        
        // Calculate inactivity
        if( $lastOnline > time() - (59*60) && !$user->Equals( $this->_user ) )
            if( $lastOnline > time() - (10*60) )
                $inactivity = "(*)";
            else
                $inactivity = "(" . Helper::ConvertToMinutes( time() - $lastOnline, $user->Language() ) . ")";
        else
            $inactivity = "";

        // TODO: phalanx thingies
        
        $planetName = $c->Name();
        
        $vars['inactivity'] = $inactivity;
        $vars['planet_name'] = $planetName;
        $vars['color_name'] = "";
                
        return $vars;
    }
    
    private function RenderFooter()
    {
        // "X plants inhabited" text
        $galaxy = $this->_coords->Galaxy();
        $system = $this->_coords->System();
        $query = "SELECT COUNT(ID) AS count FROM colony WHERE galaxy_position = $galaxy AND system_position = $system;";
        $result = Database::Instance()->ExecuteQuery( $query, "SELECT" );
        if( $result['count'] == 1 )
            $planetsInhabited = $result['count']." ".$this->_text['single_planet_inhabited'];
        else
            $planetsInhabited = $result['count']." ".$this->_text['several_planets_inhabited'];
        $footerData['planets_inhabited'] = $planetsInhabited;
        
        // Calculate position of outer space
        global $NN_config;
        $footerData['outer_space_pos'] = $NN_config["max_planets"] + 1;
        
        $footerData['legend'] = "Legend"; // TODO: Placeholder
        
        $footerData['missiles_available'] = 0; // TODO: implement missiles // $c->Missiles()->Amount();
        $footerData['fleet_slots_available'] = 0; // TODO: implement fleet slots
        $footerData['fleet_slots_total'] = 0; // TODO: implement fleet slots
        
        $c = $this->_user->CurrentColony();
        $footerData['recyclers_available'] = $c->Fleet()->GetMemberByName("recycler")->Amount();
        $footerData['espionage_probes_available'] = $c->Fleet()->GetMemberByName("espionage_probe")->Amount();
        
        $vars = array_merge( $footerData, $this->_text );

        return Page::StaticRender( "galaxy/galaxy_footer", $vars, $this->_user->AuthorisationLevelName() );
    }
    

}

?>