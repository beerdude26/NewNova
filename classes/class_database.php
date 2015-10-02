<?php

// Dependencies
require_once "root.inc";
require_once "$ROOT/config.php";

class Database
{
	private static $_instance = NULL;	//Singleton

	private $_servername;		// SQL server name
	private $_username;			// SQL login
	private $_password;			// SQL password
	private $_database;			// SQL database
	private $_prefix;			// Table prefix
    private $_link = NULL;      // Database connection
    private $_debuggingActive = false; // Is debugging active?
	
	private function Database()
	{
		global $NN_config;
		
		$this->_servername = (string) $NN_config["servername"];
		$this->_username = (string) $NN_config["username"];
		$this->_password = (string) $NN_config["password"];
		$this->_database = (string) $NN_config["database"];
		$this->_prefix = (string) $NN_config["prefix"];
	}
	
	public static function Instance()
	{
		if( !self::$_instance )
		{
			self::$_instance = new Database();
		}
		
		return self::$_instance;
	}
    
    public function Link()
    {
        if( $this->_link == NULL )
            $this->Connect();

        return $this->_link;
    }
    
    public function SetDebugging( $bool )
    {
        $this->_debuggingActive = $bool;
    }
	
	private function Connect()
	{
		$this->_link = new mysqli($this->_servername, $this->_username, $this->_password, $this->_database);
	}
    
    public function EscapeString( $string )
    {
        return $this->Connection()->real_escape_string( $string );
    }
	
	public function Connection()
	{
		if( $this->Link()->connect_error )
			$this->Connect();
		
		return $this->Link();
	}
	
	public function Disconnect()
	{
		$this->Link()->close();
	}
	
	public function ExecuteQuery( $querytext, $type )
	{
		if ( !isset( $this->_link ) )
			$this->Connect();
        
        if( $type == "MULTI")
            $query_result = $this->Link()->multi_query( $querytext );
        else
            $query_result = $this->Link()->query( $querytext );

        if( $this->Link()->connect_errno > 0)
        {
            echo "SQL error! Query:<br/>$querytext";
            throw new exception ( $this->Link()->connect_error );
        }
        
        if( $this->_debuggingActive )
        {
            echo "<br/>Query text:";
            Helper::var_dump_pre( $querytext );
        }
        
		$returnedresource = FALSE;
			
		switch( $type )
		{
			case "SELECT":
				if( $query_result == false )
                {
                    $returnedresource = NULL;
                    break;
                }
                
                $numberOfRows = $query_result->num_rows;
                if( $this->_debuggingActive )
                {
                    echo "<br/>Number of rows:";
                    Helper::var_dump_pre( $numberOfRows );
                }
                
                if( $numberOfRows > 1 )
                {
                    while($row = $query_result->fetch_array(MYSQL_ASSOC))
                        $rows[] = $row;
                    $returnedresource = $rows;
                }
                else
                    if( $row = $query_result->fetch_array(MYSQL_ASSOC) )
                        $returnedresource = $row;
                    else
                        $returnedresource = NULL;
				break;
			case "UPDATE":
				$returnedresource = $this->Link()->affected_rows;
				break;
			case "INSERT":
				$returnedresource = $this->Link()->insert_id;
				break;
			case "DELETE":
				$returnedresource = $this->Link()->affected_rows;
				break;
            case "MULTI":
                $results = array();
                $index = 0;
                do
                    if ( $result = $this->Link()->use_result() )
                    {
                        while( $row = $result->fetch_row() )
                            $results[$index][] = $row;
                        $result->close();
                        $index++;
                    }
                while ( $this->Link()->next_result() );
                
                $returnedresource = $results;
                break;
			default:
				$returnedresource = $query_result;
				break;
		}
        
        if( $this->_debuggingActive )
        {
            echo "<br/>Returned resource:";
            Helper::var_dump_pre( $returnedresource );
        }

		return $returnedresource;
	}
}
?>