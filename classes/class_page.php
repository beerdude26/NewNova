<?php

require_once "root.inc";
require_once "$ROOT/config.php";

class Page
{
	private $_content;			// The actual content (template + PHP variables).
	private $_languageFiles;	// An array that determines which language files need to be loaded.
	private $_pageTitle = "";	// Page title.
	private $_showTopNavigation = true;	// Show the top navigation bar or not.
	private $_extraMetaTags = "";	// Extra meta tags that should be included.
	private $_userLevel = 0;		// The minimum authorisation level this user should have in order to view this page.
	
	public function Page( $template, $variables, $title, $languageFiles, $extraScripts = '', $showTopNavigation = true, $isInGame = false, $extraMetaTags = '' )
	{
		global $NN_config;
		
		$this->_languageFiles = $languageFiles;
		
		$currentUser = User::GetCurrentUser();
        $userLevelName = $currentUser->AuthorisationLevelName();
		
		// Get name of userlevel
		// TODO: have this use $currentUser
		//$userLevelName = Database::Instance()->ExecuteQuery("SELECT name FROM authorisation WHERE level = ".$currentUser->AuthorisationLevel().";","SELECT");
		$resultSet = Database::Instance()->ExecuteQuery("SELECT name FROM authorisation WHERE level = 0;","SELECT");
		$userLevelName = $resultSet['name'];
		
		if( $userLevelName == NULL ) return null /* TODO: Throw gigantic error, too */;
		
		// Load common variables
		$CommonVariables = self::CommonFiles();
		
		// Render the header, add it to the content
		$this->_content = $this->RenderHeader( $CommonVariables, $title, $userLevelName, $extraMetaTags, $extraScripts );
		
		// If we are in the game, render the side bar
		if( $isInGame )
		{
			$this->_content .= $this->RenderSidebar( $CommonVariables, $userLevelName );
		}
		
		// If required, render the top navigation bar
		if( $showTopNavigation )
		{
			// Load navigation variables
			$variables['TODO'] = "add some variables";
		}
		
		// Render the actual content
		$this->_content .= $this->RenderContent( $CommonVariables, $template, $variables, $userLevelName );

		// Render the footer
		$this->_content .= $this->RenderFooter( $CommonVariables, $userLevelName );
	}
	
	public function Display()
	{
		return $this->_content;
	}
	
	private function RenderHeader( $variables, $title, $userLevelName, $extraMetaTags, $extraScripts )
	{			
		// Load header variables
		
		// Load title
		$variables['title'] = $title;
		// Load meta tags
		$variables['-meta-'] = $extraMetaTags;
        // Load extra scripts
        $variables['-head-'] = $extraScripts;
		
		// Render the template and return the content
		return $this->RenderTemplate( "simple_header", $variables, $userLevelName );
	}
	
	private function RenderSidebar( $variables, $userLevelName )
	{
		// TODO: finish this
		
		// Load sidebar variables
		
		// Render the template and return the content
		return $this->RenderTemplate( "sidebar", $variables, $userLevelName );
	}
	
	private function RenderTopNavigation( $variables, $currentUser, $currentPlanet )
	{
		// TODO: make this
	}
	
	private function RenderContent( $CommonVariables, $template, $variables, $userLevelName )
	{
		// Load common variables
		$variables = array_merge( $variables, $CommonVariables );
		
		// Render the template and return the content
		return $this->RenderTemplate( $template, $variables, $userLevelName );
	}
	
	private function RenderFooter( $variables, $userLevelName )
	{
		global $NN_config;
		
		// Load footer variables
		
		// Load copyright
		$variables['footer_copyright'] = $NN_config["copyright"];
		
		// Load translator of the language
		// TODO: Fix this to use $currentUser
		//$variables['translator'] = $currentUser->Language()->Translator();
		$variables['footer_translator'] = ResourceParser::Instance()->GetLanguageByName( "english" )->Translator();
		
		// Render the template and return the content
		return $this->RenderTemplate( "overall_footer", $variables, $userLevelName );	
	}
	
	private function RenderTemplate( $template, $variables, $userLevelName )
	{
		global $NN_config;
		require 'root.inc';

		// Page templates are looked up in the following manner:
		// [root location]/[template directory]/[name of user level in database]/[template name].tpl
		$filename = $ROOT.$NN_config["template_directory"].'/'.$userLevelName.'/'.$template.".tpl";
		
		// Get file, don't show an error for now
		// TODO: Do show an error
		$templateContent = file_get_contents ($filename);
		
		// Replace template placeholders with PHP variables and return finished content
		return preg_replace('#\{([a-z0-9\-_]*?)\}#Ssie', '( ( isset($variables[\'\1\']) ) ? $variables[\'\1\'] : \'\' );', $templateContent);
	}
	
	private function CommonFiles()
	{
		global $NN_config;
		require 'root.inc';
		
		// Load language files
		//$currentUser = User::GetCurrentUser();
		//$variables = $currentUser->Language()->Files();
		//$variables['ENCODING'] = $currentUser->Language()->Encoding();
		
		// TODO: Modify this code to use the $currentUser variable
		
		if( !is_array( $this->_languageFiles ) ) // We just need one language file
		{
			$variables = ResourceParser::Instance()->GetLanguageByName( "english" )->GetFilesByPage( $this->_languageFiles );
		}
		else // We need more than one language file
		{
		$variables = array();
		foreach( $this->_languageFiles as $languageFile )
			$variables = array_merge( $variables, ResourceParser::Instance()->GetLanguageByName( "english" )->GetFilesByPage( $languageFile ) );
		}
		
		$variables['ENCODING'] = "UTF-8";
		
		// Load skin path
		$variables['skinpath'] = $ROOT."/inc/game_skins/".$NN_config["default_skin"]."/";
		
		return $variables;
	}
    
    public static function StaticRender( $template, $variables, $userLevelName )
	{
		global $NN_config;
		require 'root.inc';

		// Page templates are looked up in the following manner:
		// [root location]/[template directory]/[name of user level in database]/[template name].tpl
		$filename = $ROOT.$NN_config["template_directory"].'/'.$userLevelName.'/'.$template.".tpl";
		
		// Get file, don't show an error for now
		// TODO: Do show an error
		$templateContent = file_get_contents ($filename);
		
		// Replace template placeholders with PHP variables and return finished content
		return preg_replace('#\{([a-z0-9\-_]*?)\}#Ssie', '( ( isset($variables[\'\1\']) ) ? $variables[\'\1\'] : \'\' );', $templateContent);
	}
}
?>