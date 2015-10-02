<?php

class Helper
{

	public function Helper()
	{
	
	}
	
	public function clamp( $minval, $maxval, $val )
	{
		return max( $minval, min( $maxval, $val ) );
	}
    
    public function lop_off( $string, $amount, $replaceBy = "" )
    {
        $ret = substr( $string, 0, strlen($string) - $amount );
        if( !empty( $replaceBy ) )
            $ret .= $replaceBy;
            
        return $ret;
    }
	
	public function var_dump_pre( $mixed = NULL )
	{
		echo '<pre>';
		var_dump( $mixed );
		echo '</pre>';
		return NULL;
	}
    
    public function checkType( $calledObject, $givenObject, $expectedType )
    {
        if( !(is_a($givenObject, $expectedType)) )
        {
            $backTrace = debug_backtrace();
            $methodName = $backTrace[count($backTrace) - 1]["function"];
            $location = get_class( $calledObject )."::".$methodName;
            $exception = $location." expects an object of the type of ".$expectedType.".";
            throw new Exception($exception);
        }
    }
    
    public function checkTypeList( $calledObject, $givenList, $expectedType )
    {
        $backTrace = debug_backtrace();
        $methodName = $backTrace[count($backTrace) - 1]["function"];
        $location = get_class( $calledObject )."::".$methodName;
    
        foreach( $givenList as $givenObject )
        {
            if( !(is_a($givenObject, $expectedType)) )
            {
                $exception = $location." expects an object of the type of ".$expectedType.".";
                throw new Exception($exception);
            }
        }
    }
    
    // Expects two keyhashed arrays with Resource objects
    public function sumUnits( array $a1, array $a2 )
    {
        Helper::checkTypeList( $this, $a1, "Resource" );
        Helper::checkTypeList( $this, $a2, "Resource" );
        $newArray = array();
        
        foreach( $a1 as $resource )
        {
            $name = $resource->Name();
            if( empty( $newArray[$name] ) )
                $newArray[$name] = clone $resource; // We need a fresh copy
            
            // Iterate through other list     
            foreach( $a2 as $otherResource )
                if( $otherResource->Name() === $name )
                    $newArray[$name]->Amount( $newArray[$name]->Amount() + $otherResource->Amount() );
        }
        
        // Any units the first array did not have are added as well.
        foreach( $a2 as $resource )
            if( empty( $newArray[$name] ) )
                $newArray[$name] = clone $resource;
        
        return $newArray;
    }
    
    // Expects two keyhashed arrays with Resource objects,
    // the first one needs to have all the Resource objects the second one has
    
    // TODO: Introduce type hinting everywhere. Only works with objects and arrays.
    public function deductUnits( array $a1, array $a2 )
    {
        Helper::checkTypeList( $this, $a1, "Resource" );
        Helper::checkTypeList( $this, $a2, "Resource" );
        $newArray = array();
        
        foreach( $a1 as $resource )
        {
            $name = $resource->Name();
            if( empty( $newArray[$name] ) )
                $newArray[$name] = clone $resource; // We need a fresh copy
            
            // Iterate through other list     
            foreach( $a2 as $otherResource )
                if( $otherResource->Name() === $name )
                    $newArray[$name]->Amount( $newArray[$name]->Amount() - $otherResource->Amount() );
            
        }
        
        foreach( $a2 as $resource )
        if( empty( $newArray[$name] ) )
                throw new Exception("Second array in deductUnits contained units that were not in first array!");
        
        return $newArray;
    }
    
    // Expects an array of Resource objects
    public function containsNegative( array $array )
    {
        Helper::checkTypeList( $this, $array, "Resource" );
        foreach( $array as $element )
            if( $element->Amount() < 0 )
                return true;
                
        return false;
    }
    
    // Checks if all Resource objects in the given array are all in a certain range of database-IDs.
    public function sameType( array $array )
    {
        global $NN_config;
        $types = array( "building_id_range", "ship_id_range", "defense_id_range", "missile_id_range" );
        
        $rangeFrom = "";
        $rangeTo = "";
        foreach( $array as $element )
        {
            // Determine the type of the first element. Ignored afterwards.
            if( $rangeFrom === "" )
                foreach( $types as $rangeName )
                    if( $element->ID() >= $NN_config[$rangeName]["from"] && $element->ID() <= $NN_config[$rangeName]["to"] )
                    {
                        $rangeFrom = $NN_config[$rangeName]["from"];
                        $rangeTo = $NN_config[$rangeName]["to"];
                        break 1;
                    }

            // Determine the type of the element.
            if( !($element->ID() >= $rangeFrom && $element->ID() <= $rangeTo) )
                return false;
        }
        return true;
    }
    
    // Takes seconds, converts them to format of "XXX min"
    public function ConvertToMinutes( $seconds, Language $language )
    {
        $min = floor($seconds / 60 % 60);

        $time = '';
        if ($min != 0)
        {
            $text = $language->GetFilesByPage("time");
            $time .= $min . $text['minute_abbreviation_long'] . ' ';
        }   

        return $time;
    }
    
    public function ConvertToString( $seconds, Language $language )
    {
        $day = floor( $seconds / (24 * 3600));
        $hs = floor( $seconds / 3600 % 24);
        $ms = floor( $seconds / 60 % 60);
        $sr = floor( $seconds / 1 % 60);

        if ($day < 0) { $day = 0; }
        if ($hs  < 0) { $hs = 0; }
        if ($ms  < 0) { $ms = 0; }
        if ($sr  < 0) { $sr = 0; }
        
        if ($hs < 10) { $hh = "0" . $hs; } else { $hh = $hs; }
        if ($ms < 10) { $mm = "0" . $ms; } else { $mm = $ms; }
        if ($sr < 10) { $ss = "0" . $sr; } else { $ss = $sr; }

        $time = '';
        $text = $language->GetFilesByPage("time");
        $dayAbbreviation = $text['day_abbreviation'];
        $hourAbbreviation = $text['hour_abbreviation'];
        $minuteAbbreviation = $text['minute_abbreviation'];
        
        if ($day != 0) { $time .= $day . $dayAbbreviation . ' '; }
        if ($hs  != 0) { $time .= $hh . $hourAbbreviation . ' ';  } else { $time .= '00' . $hourAbbreviation . ' '; }
        if ($ms  != 0) { $time .= $mm . $minuteAbbreviation . ' ';  } else { $time .= '00' . $minuteAbbreviation . ' '; }
        $time .= $ss . $text['second_abbreviation'];

        return $time;
    }
    
    // TODO: put this functions and the ones above it (for converting time and such) into a separate class?
    public function InsertValidation( $rootLevel )
    {
        return '<script type="text/javascript" src="'.$rootLevel.'javascript/jquery/validation/jquery.validate.js"></script>';
    }
    
    public function InsertCountDown( $rootLevel )
    {
        $return = '<link rel="stylesheet" type="text/css" href="'.$rootLevel.'javascript/jquery/countdown/jquery.countdown.css" />'."\n";
        $return .=  '<script type="text/javascript" src="'.$rootLevel.'javascript/jquery/countdown/jquery.countdown.js"></script>'."\n";
        return $return;
    }
    
    public function InsertReorder( $rootLevel )
    {
        $return =  '<script type="text/javascript" src="'.$rootLevel.'javascript/jquery/reorder/jquery.tablednd_0_5.js"></script>'."\n";
        return $return;
    }

}

?>