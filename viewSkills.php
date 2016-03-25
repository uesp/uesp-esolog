<?php

/*
 * viewSkills.php -- by Dave Humphrey (dave@uesp.net), March 2016
 * 
 * Outputs a HTML page containing an ESO skill tree similar to the game UI.
 * 
 * TODO:
 *
 */

// Database users, passwords and other secrets
require("/home/uesp/secrets/esolog.secrets");
require("esoCommon.php");



class CEsoViewSkills
{
	
	const ESOVS_HTML_TEMPLATE = "templates/esoskills_template.txt";
	
	public $version = "";
	
	public $htmlTemplate = "";
	
	
	public function __construct ()
	{
		$this->SetInputParams();
		$this->ParseInputParams();
		$this->InitDatabase();
		$this->LoadTemplate();
	}
	
	
	public function ReportError($errorMsg)
	{
		//print($errorMsg);
		error_log($errorMsg);
		return false;
	}
	
	
	private function InitDatabase()
	{
		global $uespEsoLogReadDBHost, $uespEsoLogReadUser, $uespEsoLogReadPW, $uespEsoLogDatabase;
	
		$this->db = new mysqli($uespEsoLogReadDBHost, $uespEsoLogReadUser, $uespEsoLogReadPW, $uespEsoLogDatabase);
		if ($this->db->connect_error) return $this->ReportError("ERROR: Could not connect to mysql database!");
	
		return true;
	}
	
	
	private function LoadTemplate()
	{
		$this->htmlTemplate = file_get_contents(self::ESOVS_HTML_TEMPLATE);
	}
	
	
	private function ParseInputParams ()
	{
		if (array_key_exists('version', $this->inputParams)) $this->version = urldecode($this->inputParams['version']);
		
		return true;
	}
	
	
	private function SetInputParams ()
	{
		global $argv;
		$this->inputParams = $_REQUEST;
	
			// Add command line arguments to input parameters for testing
		if ($argv !== null)
		{
			$argIndex = 0;
				
			foreach ($argv as $arg)
			{
				$argIndex += 1;
				if ($argIndex <= 1) continue;
				$e = explode("=", $arg);
	
				if(count($e) == 2)
				{
					$this->inputParams[$e[0]] = $e[1];
				}
				else
				{
					$this->inputParams[$e[0]] = 1;
				}
			}
		}
	}
	
	
	private function GetTableSuffix()
	{
		return GetEsoItemTableSuffix($this->version);
	}
	
	
	public function GetVersionTitle()
	{
		if ($this->GetTableSuffix() == "") return "";
		return " v" . $this->version . "";
	}
	
	
	public function OutputHtml()
	{
		$replacePairs = array(
				
				'{version}' => $this->version,
				'{versionTitle}' => $this->GetVersionTitle(),
		);
	
		$output = strtr($this->htmlTemplate, $replacePairs);
	
		print ($output);
	}
	
	
	public function Render()
	{
		$this->OutputHtml();
	}
	
};


$g_EsoViewSkills = new CEsoViewSkills();
$g_EsoViewSkills->Render();