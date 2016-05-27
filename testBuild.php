<?php

require_once("/home/uesp/secrets/esolog.secrets");
require_once("esoCommon.php");


class CEsoTestBuild 
{
	const TEMPLATE_FILE = "templates/esoTestBuild_template.txt";
	
	public $db = null;
	public $htmlTemplate = "";
	public $version = "";
	
	
	public static $INPUT_STATS_LIST = array(
			"Level",
			"AttributeHealth",
			"AttributeMagicka",
			"AttributeStamina",
			"GearHealth",
			"GearMagicka",
			"GearStamina",
			"CPHealth",
			"CPMagicka",
			"CPStamina",
			"FoodHealth",
			"FoodMagicka",
			"FoodStamina",
	);
	
	
	public static $COMPUTED_STATS_LIST = array(
			
			"Health" => array(
					"title" => "Health",
					"compute" => array(
							"+156 * Level + 944",
							"+122 * AttributeHealth",
							"+GearHealth",
							"(1 + pow(CPHealth, 0.56432)/100)",
					),
			),
			
			"Magicka" => array(
					"title" => "Magicka",
					"compute" => array(
							"+142 * Level + 858",
							"+111 * AttributeMagicka",
							"+GearMagicka",
							"(1 + pow(CPMagicka, 0.56432)/100)",
					),
			),
			
			"Stamina" => array(
					"title" => "Stamina",
					"compute" => array(
							"+142 * Level + 858",
							"+111 * AttributeStamina",
							"+GearStamina",
							"(1 + pow(CPStamina, 0.56432)/100)",
					),
			),
			
	); 
	
	
	public function __construct()
	{
		$this->SetInputParams();
		$this->ParseInputParams();
		$this->InitDatabase();
		$this->LoadTemplate();
	}
	
	
	public function ReportError($errorMsg)
	{
		print($errorMsg);
		error_log($errorMsg);
		return false;
	}
	
	
	public function ParseInputParams ()
	{
		//if (array_key_exists('output', $this->inputParams)) $this->rawOutput = strtoupper($this->inputParams['output']);
	}
	
	
	public function SetInputParams ()
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
	
	
	public function InitDatabase()
	{
		global $uespEsoLogReadDBHost, $uespEsoLogReadUser, $uespEsoLogReadPW, $uespEsoLogDatabase;
	
		$this->db = new mysqli($uespEsoLogReadDBHost, $uespEsoLogReadUser, $uespEsoLogReadPW, $uespEsoLogDatabase);
		if ($this->db->connect_error) return $this->ReportError("ERROR: Could not connect to mysql database!");
	
		return true;
	}
	
	
	
	public function LoadTemplate()
	{
		$this->htmlTemplate = file_get_contents(self::TEMPLATE_FILE);
		
		if (!$this->htmlTemplate)
		{
			$this->htmlTemplate = "Error: Failed to load HTML template file '".self::TEMPLATE_FILE."'!";
			return false;
		}
		
		return true;
	}
	
	
	public function OutputHtmlHeader()
	{
		ob_start("ob_gzhandler");
		
		header("Expires: 0");
		header("Pragma: no-cache");
		header("Cache-Control: no-cache, no-store, must-revalidate");
		header("Pragma: no-cache");
		header("content-type: text/html");
	}
	
	
	public function GetComputedStatsJson()
	{
		return json_encode(self::$COMPUTED_STATS_LIST);
	}
	
	
	public function GetInputStatsJson()
	{
		return json_encode(self::$INPUT_STATS_LIST);
	}
	
	
	public function GetOutputHtml()
	{
		$replacePairs = array(
				'{version}' => $this->version,
				'{esoComputedStatsJson}' => $this->GetComputedStatsJson(),
				'{esoInputStatsJson}' => $this->GetInputStatsJson(),
		);
		
		$output = strtr($this->htmlTemplate, $replacePairs);
		return $output;
	}
	
	
	public function Render()
	{
		$this->OutputHtmlHeader();
		print($this->GetOutputHtml());
	}
	
};



$g_EsoTestBuild = new CEsoTestBuild();
$g_EsoTestBuild->Render();
