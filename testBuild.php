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
			"Defending",
			"Sturdy",
			"Armor.Light",
			"Armor.Medium",
			"Armor.Heavy",
			"Armor.Types",
			"Level",
			"CPLevel",
			"EffectiveLevel",
			"EffectiveLevel",
			"CP.TotalPoints",
			"Attribute.TotalPoints",
			"Mundus.Name",
			"Race",
			"Class",
	);
	
	
	public $STATS_TYPE_LIST = array(
			"Attribute",
			"Item",
			"Set",
			"Skill",
			"Skill2",
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
			"HealingDone",
			"HealingTaken",
			"HealingReceived",
			"HealingTotal",
			"BashCost",
			"BlockCost",
			"BlockMitigation",
			"RollDodgeCost",
			"SprintCost",
			"SneakCost",
			"BreakFreeCost",
			"HARestore",
			"Constitution",
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
	
	
	public $RACE_TYPES = array(
			"Argonian" => "",
			"Breton" => "",
			"Dark Elf" => "Dunmer",
			"High Elf" => "Altmer",
			"Imperial" => "",
			"Khajiit" => "",
			"Nord" => "",
			"Orc" => "Orsimer",
			"Redguard" => "",
			"Wood Elf" => "Bosmer",
	);
	
	
	public $CLASS_TYPES = array(
			"Dragonknight",
			"Nightblade",
			"Sorcerer",
			"Templar",
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
							"Set.Magicka",
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
							"Set.Stamina",
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
			
			"HealthRegen" => array(
					"title" => "Health Regen",
					"compute" => array(
							"round(5.592 * Level + 29.4)",
							"Item.HealthRegen",
							"+",
							"Set.HealthRegen",
							"+",
							"Mundus.HealthRegen * (1 + Divines)",
							"+",
							"1 + CP.HealthRegen",
							"*",
							"1 + Skill2.HealthRegen",
							"*",
							"Food.HealthRegen",
							"+",
							"1 + Skill.HealthRegen",
							"*",
					),
			),
			
			"MagickaRegen" => array(
					"title" => "Magicka Regen",
					"compute" => array(
							"round(9.30612 * Level + 48.7)",
							"Item.MagickaRegen",
							"+",
							"Set.MagickaRegen",
							"+",
							"Mundus.MagickaRegen * (1 + Divines)",
							"+",
							"1 + CP.MagickaRegen",
							"*",
							"1 + Skill2.MagickaRegen",
							"*",
							"Food.MagickaRegen",
							"+",
							"1 + Skill.MagickaRegen",
							"*",
					),
			),
			
			"StaminaRegen" => array(
					"title" => "Stamina Regen",
					"compute" => array(
							"round(9.30612 * Level + 48.7)",
							"Item.StaminaRegen",
							"+",
							"Set.StaminaRegen",
							"+",
							"Mundus.StaminaRegen * (1 + Divines)",
							"+",
							"1 + CP.StaminaRegen",
							"*",
							"1 + Skill2.StaminaRegen",
							"*",
							"Food.StaminaRegen",
							"+",
							"1 + Skill.StaminaRegen",
							"*",
					),
			),
			
			"SpellDamage" => array(
					"title" => "Spell Damage",
					"compute" => array(
							"Item.SpellDamage",
							"Mundus.SpellDamage * (1 + Divines)",
							"+",
							"1 + Skill.SpellDamage",
							"*",							
					),
			),
			
			"WeaponDamage" => array(
					"title" => "Weapon Damage",
					"compute" => array(
							"Item.WeaponDamage",
							"Set.WeaponDamage",
							"+",
							"Mundus.WeaponDamage * (1 + Divines)",
							"+",
							"1 + Skill.WeaponDamage",
							"*",
					),
			),
			
			
			"SpellCrit" => array(
					"title" => "Spell Critical",
					"compute" => array(
							"Item.SpellCrit",
							"Mundus.SpellCrit * (1 + Divines)",
							"+",
							"Skill.SpellCrit",
							"+",
							"Buff.SpellCrit",
							"+",
					),
			),
			
			"WeaponCrit" => array(
					"title" => "Weapon Critical",
					"compute" => array(
							"Item.WeaponCrit",
							"Set.WeaponCrit",
							"+",
							"Mundus.WeaponCrit * (1 + Divines)",
							"+",
							"Skill.WeaponCrit",
							"+",
							"Buff.WeaponCrit",
							"+",
					),
			),
			
			"CritDamage" => array(
					"title" => "Critical Damage",
					"display" => "percent",
					"compute" => array(
							"CP.CritDamage",
							"Skill.CritDamage",
							"+",
							"Mundus.CritDamage * (1 + Divines)",
							"+",
							"Buff.CritDamage",
							"+",
							"0.5",
							"+",
					),
			),
			
			"SpellResist" => array(
					"title" => "Spell Resistance",
					"compute" => array(
							"Item.SpellResist",
							"Skill2.SpellResist",
							"+",
							"1 + Defending",
							"*",
							"Set.SpellResist",
							"+",
							"Skill.SpellResist",
							"+",
							"CP.SpellResist",
							"+",
					),
			),
			
			"PhysicalResist" => array(
					"title" => "Physical Resistance",
					"compute" => array(
							"Item.PhysicalResist",
							"Skill2.PhysicalResist",
							"+",
							"1 + Defending",
							"*",
							"Set.PhysicalResist",
							"+",
							"Skill.PhysicalResist",
							"+",
							"CP.PhysicalResist",
							"+",
					),
			),
			
			"CritResist" => array(
					"title" => "Critical Resistance",
					"compute" => array(
							"Item.CritResist",
							"Set.CritResist",
							"+",
							"Skill.CritResist",
							"+",
							"CP.CritResist",
							"+",
					),
			),
			
			"ColdResist" => array(
					"title" => "Cold Resistance",
					"compute" => array(
							"Item.ColdResist",
							"Set.ColdResist",
							"+",
							"Skill.ColdResist",
							"+",
							"CP.ColdResist",
							"+",
							"Buff.ColdResist",
							"+",
					),
			),
			
			"FireResist" => array(
					"title" => "Fire Resistance",
					"compute" => array(
							"Item.FireResist",
							"Set.FireResist",
							"+",
							"Skill.FireResist",
							"+",
							"CP.FireResist",
							"+",
							"Buff.FireResist",
							"+",
					),
			),
			
			"ShockResist" => array(
					"title" => "Shock Resistance",
					"compute" => array(
							"Item.ShockResist",
							"Set.ShockResist",
							"+",
							"Skill.ShockResist",
							"+",
							"CP.ShockResist",
							"+",
							"Buff.ShockResist",
							"+",
					),
			),
			
			"PoisonResist" => array(
					"title" => "Poison Resistance",
					"compute" => array(
							"Item.PoisonResist",
							"Set.PoisonResist",
							"+",
							"Skill.PoisonResist",
							"+",
							"CP.PoisonResist",
							"+",
							"Buff.PoisonResist",
							"+",
					),
			),
			
			"HealingDone" => array(
					"title" => "Healing Done",
					"display" => "percent",
					"compute" => array(
							"Item.HealingDone",
							"Set.HealingDone",
							"+",
							"Skill.HealingDone",
							"+",
							"CP.HealingDone",
							"+",
							"Buff.HealingDone",
							"+",
							"Mundus.HealingDone",
							"+",
					),
			),
			
			"HealingTaken" => array(
					"title" => "Healing Taken",
					"display" => "percent",
					"compute" => array(
							"Item.HealingTaken",
							"Set.HealingTaken",
							"+",
							"Skill.HealingTaken",
							"+",
							"CP.HealingTaken",
							"+",
							"Buff.HealingTaken",
							"+",
					),
			),
			
			"HealingReceived" => array(
					"title" => "Healing Received",
					"display" => "percent",
					"compute" => array(
							"Item.HealingReceived",
							"Set.HealingReceived",
							"+",
							"Skill.HealingReceived",
							"+",
							"CP.HealingReceived",
							"+",
							"Buff.HealingReceived",
							"+",
					),
			),
			
			"Healing" => array(
					"title" => "Healing Total",
					"display" => "percent",
					"compute" => array(
							"1 + HealingDone",
							"1 + HealingTaken",
							"*",
							"1 + HealingReceived",
							"*"
					),
			),
			
			"SneakCost" => array(
					"title" => "Sneak Cost",
					"compute" => array(			// TODO: Include item
							"1 + 2 * EffectiveLevel",
							"1 - CP.SneakCost",
							"*",
							"1 - Skill.SneakCost",
							"*",
					),
			),
			
			"SprintCost" => array(
					"title" => "Sprint Cost",
					"compute" => array(		// TODO: Include items/skills
							"floor(38.46 + 7.69*EffectiveLevel)",
							"1 - CP.SprintCost",
							"*",
					),
			),
			
			"BashCost" => array(
					"title" => "Bash Cost",
					"compute" => array(
							"floor(157 + 26.25*EffectiveLevel)",
							"Item.BashCost * 1.1625",  // TODO: Check?
							"-",
					),
			),
			
			"BlockCost" => array(
					"title" => "Block Cost",
					"compute" => array(
							"180 + 30*EffectiveLevel",
							"1 - Sturdy/1.16",
							"*",
							"1 - CP.BlockCost",
							"*",
							"Item.BlockCost",
							"-",
							"Set.BlockCost",
							"-",
							"1 - Skill.BlockCost",
							"*",
					),
			),
			
			"RollDodgeCost" => array(
					"title" => "Roll Dodge Cost",
					"compute" => array(
							"floor(34 + 5.62*EffectiveLevel)*10",
							"1 - CP.RollDodgeCost",
							"*",
							"1 - Skill.RollDodgeCost",
							"*",
							"1 - Item.RollDodgeCost",  	// TODO: Check?
							"*",
							"1 - Set.RollDodgeCost",  	// TODO: Check?
							"*",
					),
			),
			
			"BreakFreeCost" => array(					// TODO: Check?
					"title" => "Break Free Cost",
					"compute" => array(
							"450 + 75*EffectiveLevel",
							"1 - CP.BreakFreeCost",
							"*",
							"1 - Skill.BreakFreeCost",
							"*",
							"1 - Item.BreakFreeCost",
							"*",
							"1 - Set.BreakFreeCost",
							"*",
					),
			),
				
			"BlockMitigation" => array(
					"title" => "Block Mitigation",
					"compute" => array(
							"0.5",
							"1 - Skill.BlockMitigation",
							"*",
					),
			),
			
			//Mitigation?

			"HARestore" => array(
					"title" => "Heavy Attack Restore",
					"compute" => array(
							"floor(1 + Level * 28.25)",
							"floor(CPLevel * 30.625)",
							"+",
							"1 + CP.HARestore",
							"*",
							"1 + Skill.HARestore",
							"*",
					),
			),
			
			"Constitution" => array(				// TODO: Check?
					"title" => "Constitution",
					"compute" => array(
							"floor(2.82 * EffectiveLevel)",
							"Armor.Heavy",
							"*",
							"1 + Set.Constitution",
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
		
		foreach ($this->STATS_BASE_LIST as $stat)
		{
			$this->INPUT_STATS_LIST[$stat] = 0;
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
		$output .= "<option value='none'>(none)</option>";
		
		foreach ($this->MUNDUS_TYPES as $name => $type)
		{
			$output .= "<option value='$name'>$name <small>($type)</small></option>";
		}
		
		return $output;
	}
	
	
	public function GetClassListHtml()
	{
		$output = "";
	
		foreach ($this->CLASS_TYPES as $class)
		{
			$output .= "<option value='$class'>$class</option>";
		}
	
		return $output;
	}
	
	
	public function GetRaceListHtml()
	{
		$output = "";
	
		foreach ($this->RACE_TYPES as $name => $extra)
		{
			$extraDesc = "";
			if ($extra != "") $extraDesc = " ($extra)";
			$output .= "<option value='$name'>$name$extraDesc</option>";
		}
	
		return $output;
	}
	
	
	public function GetOutputHtml()
	{
		$replacePairs = array(
				'{version}' => $this->version,
				'{esoComputedStatsJson}' => $this->GetComputedStatsJson(),
				'{esoInputStatsJson}' => $this->GetInputStatsJson(),
				'{raceList}' => $this->GetRaceListHtml(),
				'{classList}' => $this->GetClassListHtml(),
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
