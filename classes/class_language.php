<?php
class Language
{
	private $_name;		// Language name
	private $_encoding;	// Language encoding
	private $_files;	// Language files
	private $_translator; // The person who translated this language.
	
	public function Language( $name, $encoding, $translator )
	{
		$this->_name = $name;
		$this->_encoding = $encoding;
		$this->_translator = $translator;
	}
	
	public function Name( $value = "" )
	{			
		return $this->_name = ( ( empty( $value ) ) ? $this->_name : $value );
	}
	
	public function Encoding( $value = "" )
	{			
		return $this->_encoding = ( ( empty( $value ) ) ? $this->_encoding : $value );
	}
	
	public function Translator( $value = "" )
	{			
		return $this->_translator = ( ( empty( $value ) ) ? $this->_translator : $value );
	}
	
	public function GetFilesByPage( $value )
	{
        $path = "../inc/game_languages/language_".$this->Name()."/".$value.".mo";
		include $path;
        return $lang[$value];
			
		return ""; // TODO: error here
	}
}
?>