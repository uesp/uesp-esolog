<?php

/*
 * skillTooltip.php -- by Dave Humphrey (dave@uesp.net), April 2016
 * 
 * Returns an HTML fragment for a skill popup tooltip.
 * 
 * TODO:
 *
 */

// Database users, passwords and other secrets
require("/home/uesp/secrets/esolog.secrets");
require("esoCommon.php");


class CEsoSkillTooltip
{
	
	const TOOLTIP_DIVIDER = "<img src='http://esolog.uesp.net/resources/skill_divider.png' class='esoSkillPopupTooltipDivider'>";
	const MAX_SKILL_COEF = 6;
	
	public $inputParams = array();
	public $db = null;
	
	public $skillId = 0;
	public $skillLevel = 66;
	public $skillMagicka = 20000;
	public $skillHealth = 20000;
	public $skillStamina = 20000;
	public $skillSpellDamage = 2000;
	public $skillWeaponDamage = 2000;
	public $skillMaxStat = 20000;
	public $skillMaxDamage = 2000;
	public $version = "";
	
	public $skillData = array();
	
	public function __construct ()
	{
		ob_start("ob_gzhandler");
		
		$this->SetInputParams();
		$this->ParseInputParams();
		$this->InitDatabase();
	}
	
	
	public function ReportError($errorMsg)
	{
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
	
	
	private function ParseLevel($level)
	{
		if (is_numeric($level))
		{
			$value = intval($level);
			if ($value <  1) $value = 1;
			if ($value > 66) $value = 66;
			return $value;
		}
		
		if (preg_match("#^[vV]([0-9]+)#", trim($level), $matches))
		{
			$value = intval($matches[1]) + 50;
			if ($value <  1) $value = 1;
			if ($value > 66) $value = 66;
			return $value;
		}
		
		return 66;
	}


	private function ParseInputParams ()
	{
		if (array_key_exists('version', $this->inputParams)) $this->version = urldecode($this->inputParams['version']);
		if (array_key_exists('showall', $this->inputParams)) $this->showAll = true;
		
		if (array_key_exists('id', $this->inputParams)) $this->skillId = intval($this->inputParams['id']);
		if (array_key_exists('skillid', $this->inputParams)) $this->skillId = intval($this->inputParams['id']);
		if (array_key_exists('abilityid', $this->inputParams)) $this->skillId = intval($this->inputParams['id']);
		
		if (array_key_exists('level', $this->inputParams)) $this->skillLevel = $this->ParseLevel($this->inputParams['level']);
		
		if (array_key_exists('health', $this->inputParams)) $this->skillHealth = intval($this->inputParams['health']);
		if (array_key_exists('magicka', $this->inputParams)) $this->skillMagicka = intval($this->inputParams['magicka']);
		if (array_key_exists('stamina', $this->inputParams)) $this->skillStamina = intval($this->inputParams['stamina']);
		if (array_key_exists('spelldamage', $this->inputParams)) $this->skillSpellDamage = intval($this->inputParams['spelldamage']);
		if (array_key_exists('weapondamage', $this->inputParams)) $this->skillWeaponDamage = intval($this->inputParams['weapondamage']);
		
		$this->skillMaxStat = max($this->skillMagicka, $this->skillStamina);
		$this->skillMaxDamage = max($this->skillSpellDamage, $this->skillWeaponDamage);
	
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
	

	public function ComputeEsoSkillValue($type, $a, $b, $c)
	{
		$value = 0;
	
		$a = floatval($a);
		$b = floatval($b);
		$c = floatval($c);
	
		if ($type == -2) // Health
		{
			$value = $a * $this->skillHealth + $c;
		}
		else if ($type == 0) // Magicka
		{
			$value = $a * $this->skillMagicka + $b * $this->skillSpellDamage + $c;
		}
		else if ($type == 6) // Stamina
		{
			$value = $a * $this->skillStamina + $b * $this->skillWeaponDamage + $c;
		}
		else if ($type == 10) // Ultimate
		{
			$value = $a * $this->skillMaxStat + $b * $this->skillMaxDamage + $c;
		}
		else if ($type == -50) // Ultimate Soul Tether
		{
			$value = $a * $this->skillMaxStat + $b * $this->skillSpellDamage + $c;
		}
		else
		{
			return '?';
		}
	
		return round($value);
	}
	
	
	public function GetSkillDescription()
	{
		$coefDesc = $this->skillData['coefDescription'];
	
		if ($coefDesc == null || $coefDesc == "")
		{
			return $this->ConvertDescriptionToHtml($this->skillData['description']);
		}
	
		for ($i = 1; $i <= self::MAX_SKILL_COEF; ++$i)
		{
			$type = $this->skillData["type$i"];
			if ($type == -1) continue;
	
			$a = $this->skillData['a' . $i];
			$b = $this->skillData['b' . $i];
			$c = $this->skillData['c' . $i];
			$srcString = '$' . $i;
			
			$value = $this->ComputeEsoSkillValue($type, $a, $b, $c);
			$coefDesc = str_replace($srcString, $value, $coefDesc);
		}
	
		return $this->ConvertDescriptionToHtml($coefDesc);
	}
	
	
	public function ComputeEsoSkillCost($baseCost, $level)
	{
		if ($level < 1) $level = 1;
		if ($level >= 66) return $baseCost;
	
		if ($level >= 1 && $level <= 50) return round($baseCost * $level / 65.5367 + $baseCost / 10.7466);
		return round($baseCost * $level / 110.942 + $baseCost / 2.46882);
	}
	
	
	public function LoadSkill()
	{
		$minedSkillTable = "minedSkills" . $this->GetTableSuffix();
		$skillTreeTable  = "skillTree" . $this->GetTableSuffix();
		$abilityId = $this->skillId;
		
		$query = "SELECT $minedSkillTable.*, $skillTreeTable.* FROM $skillTreeTable LEFT JOIN $minedSkillTable ON abilityId=$minedSkillTable.id ";
		$query .= " WHERE abilityId=$abilityId;";
				
		$result = $this->db->query($query);
		if (!$result) return $this->ReportError("Failed to load skill data!");
		
		$result->data_seek(0);
		$this->skillData = $result->fetch_assoc();
		
		return true;
	}
	
	
	public function GetRomanNumeral($value)
	{
		static $NUMERALS = array(
				1  => 'I',
				2  => 'II',
				3  => 'III',
				4  => 'IV',
				5  => 'V',
				6  => 'VI',
				7  => 'VII',
				8  => 'VIII',
				9  => 'IX',
				10 => 'X',
				11 => 'XI',
				12 => 'XII',
		);
	
		if ($value <= 0) return '';
		if (array_key_exists($value, $NUMERALS)) return $NUMERALS[$value];
		return $value;
	}
	
	
	private function GetTableSuffix()
	{
		return GetEsoItemTableSuffix($this->version);
	}
	
	
	public function escape($string)
	{
		return htmlspecialchars($string, ENT_COMPAT, 'UTF-8');
	}
	
	
	public function ConvertDescriptionToHtml($description)
	{
		$newDesc = preg_replace('/\|c[a-fA-F0-9]{6}([a-zA-Z _0-9\.\+\-\:\;\n\r\t$]*)\|r/', '<div class="esoSkillToolWhite">$1</div>', $description);
		$newDesc = preg_replace('/\n/', '<br />', $newDesc);
		return $newDesc;
	}
	
	
	public function OutputHtml()
	{
		$output = "";
		
		$name = $this->escape($this->skillData['name']);
		$rank = $this->skillData['rank'];
		$learnedLevel = $this->skillData['learnedLevel'];
		$desc = $this->ConvertDescriptionToHtml($this->skillData['description']);
		$coefDesc = $this->skillData['coefDescription'];
		$channelTime = intval($this->skillData['channelTime']) / 1000;
		$castTime = intval($this->skillData['castTime']) / 1000;
		$radius = intval($this->skillData['radius']) / 100;
		$duration = intval($this->skillData['duration']) / 1000;
		$target = $this->escape($this->skillData['target']);
		$area = $this->escape($this->skillData['area']);
		$range = $this->escape($this->skillData['range']);
		$cost = $this->ComputeEsoSkillCost(intval($this->skillData['cost']), $this->skillLevel);
		$castTimeStr = $castTime . " seconds";
		$skillType = $this->skillData['type'];
		$newDesc = $this->GetSkillDescription();
		$mechanic = $this->skillData['mechanic'];
		
		$realRank = $rank;
		if ($realRank >= 9) $realRank -= 8;
		if ($realRank >= 5) $realRank -= 4;
		
		$fullName = $name;
		if ($realRank > 0) $fullName .= " " . $this->GetRomanNumeral($realRank);
		
		$output .= "<div class='esoSkillPopupTooltipTitle'>$fullName</div>";
		$output .= self::TOOLTIP_DIVIDER;
		
		if ($skillType != 'passive')
		{
			$costStr = "$cost ";
			
			if ($mechanic == 0)
				$costStr .= "Magicka";
			else if ($mechanic == 6)
				$costStr .= "Stamina";
				
			if ($channelTime > 0)
			{
				$output .= "<div class='esoSkillPopupTooltipValue'>$channelTime seconds</div>";
				$output .= "<div class='esoSkillPopupTooltipName'>Channel Time</div>";
			}
			else if ($castTime <= 0)
			{
				$castTimeStr = "Instant";
			}
				
			if ($castTimeStr != '')
			{
				$output .= "<div class='esoSkillPopupTooltipValue'>$castTimeStr</div>";
				$output .= "<div class='esoSkillPopupTooltipName'>Cast Time</div>";
			}
				
			if ($target != '')
			{
				$output .= "<div class='esoSkillPopupTooltipValue'>$target</div>";
				$output .= "<div class='esoSkillPopupTooltipName'>Target</div>";
			}
				
			if ($area != '')
			{
				$output .= "<div class='esoSkillPopupTooltipValue'>$area</div>";
				$output .= "<div class='esoSkillPopupTooltipName'>Area</div>";
			}
				
			if ($radius > 0)
			{
				$output .= "<div class='esoSkillPopupTooltipValue'>$radius meters</div>";
				$output .= "<div class='esoSkillPopupTooltipName'>Radius</div>";
			}
				
			if ($range > 0)
			{
				$output .= "<div class='esoSkillPopupTooltipValue'>$range</div>";
				$output .= "<div class='esoSkillPopupTooltipName'>Range</div>";
			}
				
			if ($duration > 0)
			{
				$output .= "<div class='esoSkillPopupTooltipValue'>$duration seconds</div>";
				$output .= "<div class='esoSkillPopupTooltipName'>Duration</div>";
			}
				
			if ($cost != '')
			{
				$output .= "<div class='esoSkillPopupTooltipValue'>$costStr</div>";
				$output .= "<div class='esoSkillPopupTooltipName'>Cost</div>";
			}
				
			$output .= self::TOOLTIP_DIVIDER;
		}
		
		$output .= "<div class='esoSkillPopupTooltipDesc'>$newDesc</div>";
		
		if ($learnedLevel > 0)
		{
			$output .= "<div class='esoSkillPopupTooltipLevel'>Unlocked at Rank $learnedLevel</div>";
		}
		
		print($output);
		
		return true;
	}
	
	
	private function OutputHtmlHeader()
	{
		ob_start("ob_gzhandler");
		
		header("Expires: 0");
		header("Pragma: no-cache");
		header("Cache-Control: no-cache, no-store, must-revalidate");
		header("Pragma: no-cache");
		header("Access-Control-Allow-Origin: " . $_SERVER['HTTP_ORIGIN'] . "");
		header("content-type: text/html");
	}
	
	
	public function Render()
	{
		$this->OutputHtmlHeader();
		
		if (!$this->LoadSkill()) return "Unknown skill {$this->skillId}!";

		$this->OutputHtml();
		
		return true;
	}
	
};


$g_EsoSkillTooltip = new CEsoSkillTooltip();
$g_EsoSkillTooltip->Render();
