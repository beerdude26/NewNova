<?php

class Trader
{
    private $_colony = NULL;
    private $_text = NULL;

    public function Trader( Colony $c ) 
    {
        $this->_colony = $c;

        // Include language files for trader page, useful to put error messages there.
        $this->_text =& $c->Owner()->Language()->GetFilesByPage( "trader" );
    }

    public function GetExchangeRatesFor( $type )
    {
        global $NN_config;
        $vars['resource_being_bought_rate'] = $NN_config["exchange"][$type][$type];
        $vars['exchange_rate_1'] = $NN_config["exchange"][$type][0]; // Cheapest resource
        $vars['exchange_rate_2'] = $NN_config["exchange"][$type][1]; // Most expensive resource
        return $vars;
    }
    
    public function SetUpTradeFor( $type, $additionalVars = NULL )
    {
        $vars =& $this->GetExchangeRatesFor( $type );
        
        if( $type == "metal" )
        {
            $vars['resource_being_bought_name'] = $this->_text["metal"];
            $vars['sell_item'] = $this->_text["sell_metal"];
            $vars['resource1'] = $this->_text['crystal'];
            $vars['resource2'] = $this->_text['deut'];
            $vars['SELLING_ACTION'] = "SELL_METAL";
            $title = $this->_text["sell_metal_title"];
        }
        elseif( $type == "crystal" )
        {
            $vars['resource_being_bought_name'] = $this->_text["crystal"];
            $vars['sell_item'] = $this->_text["sell_crystal"];
            $vars['resource1'] = $this->_text['metal'];
            $vars['resource2'] = $this->_text['deut'];
            $vars['SELLING_ACTION'] = "SELL_CRYSTAL";
            $title = $this->_text["sell_crystal_title"];
        }
        else
        {
            $vars['resource_being_bought_name'] = $this->_text["deut"];
            $vars['sell_item'] = $this->_text["sell_deuterium"];
            $vars['resource1'] = $this->_text['metal'];
            $vars['resource2'] = $this->_text['crystal'];
            $vars['SELLING_ACTION'] = "SELL_DEUTERIUM";
            $title = $this->_text["sell_deuterium_title"];
        }
        
        if( $additionalVars != NULL )
            $vars = array_merge( $vars, $additionalVars );
        
        $page = new Page( "trader_sell", $vars, $title, "trader" );
        echo $page->Display();
    }
    
    private function Buy( $type, $amount1, $amount2 )
    {
        if( $type == "metal" )
            return new Cost( 0, $amount1, $amount2 );
        elseif( $type == "crystal" )
            return new Cost( $amount1, 0, $amount2 );
        else
            return new Cost( $amount1, $amount2, 0 );
    }
    
    private function Sell( $type, $value )
    {
        if( $type == "metal" )
            return new Cost( $value );
        elseif( $type == "crystal" )
            return new Cost( 0, $value );
        else
            return new Cost( 0, 0, $value );
    }
    
    public function ConductTrade( $type, $amount1, $amount2 )
    {
        if( !is_numeric( $amount1 ) || !is_numeric( $amount2 ) )
        {
            $vars['error_display_type'] = "block";
            $vars['error_message'] = "Error!"; // TODO: localize
            $this->SetUpTradeFor( $type, $vars );
            return;
        }
        
        // Get exchange rates
        $rates =& $this->GetExchangeRatesFor( $type );
        
        $value = $amount1 / $rates['exchange_rate_1'];
        $value += $amount2 / $rates['exchange_rate_2'];
        
        if( $this->_colony->CurrentResources()->CostIsDeductible( $this->Sell( $type, $value ) ) )
        {
            $this->_colony->CurrentResources()->AddCost( $this->Buy( $type, $amount1, $amount2 ) );
            $this->_colony->CurrentResources()->DeductCost( $this->Sell( $type, $value ) );
            $this->_colony->UpdateResources();
        }
        else
        {
            $vars['error_display_type'] = "block";
            $vars['error_message'] = $this->_text['insufficient_resources'];
            $this->SetUpTradeFor( $type, $vars );
        }
    }
}

?>