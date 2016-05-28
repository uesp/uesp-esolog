<?php

require_once("/home/uesp/secrets/esolog.secrets");
require_once("esoCommon.php");


class CEsoTestBuild 
{
	const TEMPLATE_FILE = "templates/esoTestBuild_template.txt";
	
	public $db = null;
	public $htmlTemplate = "";
	public $version = "";
	
	
	public $STATS_UNIQUE_LIST = array(
			"Divines",
			"Armor.Light",
			"Armor.Medium",
			"Armor.Heavy",
			"Armor.Types",
			"Level",
			"EffectiveLevel",
			"CP.TotalPoints",
			"Attribute.TotalPoints",
			"Mundus.Name",
	);
	
	
	public $STATS_TYPE_LIST = array(
			"Attribute",
			"Item",
			"Set",
			"Skill",
			"Buff",
			"Food",
			"CP",
			"Mundus",
	);
	
	
	public $STATS_BASE_LIST = array(
			"Health",
			"Magicka",
			"Stamina",
			"HealthRegen",
			"MagickaRegen",
			"StaminaRegen",
			"WeaponDamage",
			"SpellDamage",
			"WeaponCrit",
			"SpellCrit",
			"CritDamage",
			"SpellResist",
			"PhysicalResist",
			"FireResist",
			"ColdResist",
			"PoisonResist",
			"ShockResist",
			"CritResist",
			"SpellPenetration",
			"PhysicalPenetration",
			"RunSpeed",
			"HealingGiven",
			"HealingReceived",
	);
	
	
	public $MUNDUS_TYPES = array(
			"The Apprentice" 	=> "Spell Damage",
			"The Atronach" 		=> "Magicka Regen",
			"The Lady" 			=> "Physical Resist",
			"The Lover" 		=> "Spell Resist",
			"The Lord" 			=> "Health",
			"The Mage" 			=> "Magicka",
			"The Ritual" 		=> "Healing",
			"The Serpent" 		=> "Stamina Regen",
			"The Shadow" 		=> "Crit Damage",
			"The Steed" 		=> "Run/Health Regen",
			"The Thief" 		=> "Crit Chance",
			"The Tower" 		=> "Stamina",
			"The Warrior" 		=> "Weapon Damage",
	);
	
	
	public $INPUT_STATS_LIST = array();
	
	
	public $COMPUTED_STATS_LIST = array(
			
			"Health" => array(
					"title" => "Health",
					"compute" => array(
							"156 * Level + 944",
							"122 * Attribute.Health",
							"+",
							"Item.Health",
							"+",
							"Set.Health",
							"+",
							"1 + pow(CP.Health, 0.56432)/100",
							"*",
							"Food.Health",
							"+",
							"Mundus.Health * (1 + Divines)",
							"+",
							"1 + Skill.Health",
							"*",
					),
			),
			
			"Magicka" => array(
					"title" => "Magicka",
					"compute" => array(
							"142 * Level + 858",
							"111 * Attribute.Magicka",
							"+",
							"Item.Magicka",
							"+",
							"1 + pow(CP.Magicka, 0.56432)/100",
							"*",
							"Food.Magicka",
							"+",
							"Mundus.Magicka * (1 + Divines)",
							"+",
							"1 + Skill.Magicka",
							"*",
					),
			),
			
			"Stamina" => array(
					"title" => "Stamina",
					"compute" => array(
							"142 * Level + 858",
							"111 * Attribute.Stamina",
							"+",						
							"Item.Stamina",
							"+",
							"1 + pow(CP.Stamina, 0.56432)/100",
							"*",
							"Food.Stamina",
							"+",
							"Mundus.Stamina * (1 + Divines)",
							"+",
							"1 + Skill.Stamina",
							"*",
					),
			),
			
	); 
	
	
	public function __construct()
	{
		$this->MakeInputStatsList();
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
	
	
	public function MakeInputStatsList()
	{
		$this->INPUT_STATS_LIST = array();
		
		foreach ($this->STATS_UNIQUE_LIST as $statItem)
		{
			$statList = explode(".", $statItem);
			$count = count($statList);
			
			if ($count == 1)
			{
				$this->INPUT_STATS_LIST[$statList[0]] = 0;
			}
			else if ($count == 2)
			{
				$statBase = $statList[0];
				if ($this->INPUT_STATS_LIST[$statBase] == null) $this->INPUT_STATS_LIST[$statBase] = array();
				$this->INPUT_STATS_LIST[$statBase][$statList[1]] = 0;
			}			
		}
		
		foreach ($this->STATS_TYPE_LIST as $statBase)
		{
			if ($this->INPUT_STATS_LIST[$statBase] == null) $this->INPUT_STATS_LIST[$statBase] = array();
			
			foreach ($this->STATS_BASE_LIST as $stat)
			{
				$this->INPUT_STATS_LIST[$statBase][$stat] = 0;
			}
		}
		
		
	}
	
	
	public function GetComputedStatsJson()
	{
		return json_encode($this->COMPUTED_STATS_LIST);
	}
	
	
	public function GetInputStatsJson()
	{
		return json_encode($this->INPUT_STATS_LIST);
	}
	
	
	public function GetMundusListHtml()
	{
		$output = "";
		
		foreach ($this->MUNDUS_TYPES as $name => $type)
		{
			$output .= "<option value='$name'>$name <small>($type)</small></option>";
		}
		
		return $output;
	}
	
	
	public function GetOutputHtml()
	{
		$replacePairs = array(
				'{version}' => $this->version,
				'{esoComputedStatsJson}' => $this->GetComputedStatsJson(),
				'{esoInputStatsJson}' => $this->GetInputStatsJson(),
				'{mundusList}' => $this->GetMundusListHtml(),
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
