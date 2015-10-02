<?php

// Dependencies
require_once "class_resource.php";
require_once "class_cost.php";
require_once "class_resourceparser.php";

class Technology extends Resource
{
	public function Technology( $name, Cost $cost, $nextcostmodifier, $prerequisite, $amount )
	{
		parent::Resource( $name, $cost, $nextcostmodifier, $prerequisite, $amount );
	}
    
    public function GetBuildLevelOnColony( Colony $c )
    {
        return $c->Ships()->GetMemberByName( $this->Name() )->Amount();
    }
    
    // Outdated, compare with IDResource
    public function BuildTime( BuildItem $item )
    {
        // Calculate next build level
        $nextBuildLevel = $this->GetNextBuildLevel( $group );
        
        // Calculate costs
        $buildCosts = $this->BuildCost( $group, $nextBuildLevel );
        
        // Calculate time required
        $colony =& $item->BuildGroup()->Colony();
        $interGalacticLabLevel = $colony->Technologies()->GetMemberByName("intergalactic_research_network_technology")->Amount();
        
        $researchLabs = 0;
        if( $interGalacticLabLevel > 0 )
        {
            // Get all the research labs from this user
            $userID = $colony->Owner()->ID();
            $query = "SELECT cs.research_lab FROM colony_structures AS cs, colony AS c WHERE cs.colonyID = c.ID AND c.userID = $userID;"; 
            $results = Database::Instance()->ExecuteQuery( $query, "SELECT" );
        
            $numberOfLabs = 0;
            $labList = array();
            if( isset( $results['research_lab'] ) ) // Single result
                $labList[$numberOfLabs] = $results['research_lab'];
            else // List of results
                foreach( $results as $result )
                {
                    $labList[$numberOfLabs] = $result['research_lab'];
                    $numberOfLabs++;
                }
            
            asort( $labList ); // Sort from lowest to highest
            for ($i = 0; $i <= $interGalacticLabLevel; $i++)
                $researchLabs += $labList[$lab];
        }
        else
            $researchLabs = $colony->Buildings()->GetMemberByName("research_lab")->Amount();

        global $NN_config;
        $metalCost =& $buildCosts->Metal();
        $crystalCost =& $buildCosts->Crystal();
        $gameSpeed =& $NN_config["game_speed"];
        $scientists = $colony->Owner()->Officers()->GetMemberByName("scientist")->Amount();
        
        $timeRequired = ( ($metalCost + $crystalCost) / $gameSpeed ) * ( 1 / ($researchLabs + 1) ) * 2;
        $timeRequired = floor( $timeRequired * 3600 * (1 - ($scientists * 0.1) ) );
        
        return $timeRequired;
    }
    
    public function Image( $rootLevel, $skin )
    {
        return "$rootLevel/inc/game_skins/$skin/technologies/".$this->Name().".gif";
    }
    
}
?>