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
	
	const TOOLTIP_DIVIDER = "<img src='//esolog.uesp.net/resources/skill_divider.png' class='esoSkillPopupTooltipDivider'>";
	const ICON_URL = "//esoicons.uesp.net";
	const MAX_SKILL_COEF = 6;
	
	public $inputParams = array();
	public $db = null;
	
	public $skillId = 0;
	public $skillName = "";
	public $skillLine = "";
	public $skillLevel = 66;
	public $skillMagicka = 20000;
	public $skillHealth = 20000;
	public $skillStamina = 20000;
	public $skillSpellDamage = 2000;
	public $skillWeaponDamage = 2000;
	public $skillMaxStat = 20000;
	public $skillMaxDamage = 2000;
	public $includeLink = false;
	public $useDefaultDesc = true;
	public $skillShowThumb = false;
	public $version = "";
	public $useUpdate10Costs = false;
	
	public $skillData = array();
	
	
	public function __construct ()
	{
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
	
	
	public function EscapeHtml($html)
	{
		return htmlspecialchars($html);
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
		if (array_key_exists('version', $this->inputParams)) $this->version = $this->inputParams['version'];
		
		if (array_key_exists('id', $this->inputParams)) $this->skillId = intval($this->inputParams['id']);
		if (array_key_exists('skillid', $this->inputParams)) $this->skillId = intval($this->inputParams['id']);
		if (array_key_exists('abilityid', $this->inputParams)) $this->skillId = intval($this->inputParams['id']);
		
		if (array_key_exists('name', $this->inputParams)) $this->skillName = $this->inputParams['name'];
		if (array_key_exists('skillname', $this->inputParams)) $this->skillName = $this->inputParams['skillname'];
		if (array_key_exists('skillline', $this->inputParams)) $this->skillLine = $this->inputParams['skillline'];
		
		if (array_key_exists('includelink', $this->inputParams)) $this->includeLink = intval($this->inputParams['includelink']) != 0;
		
		if (array_key_exists('level', $this->inputParams))
		{
			$this->skillLevel = $this->ParseLevel($this->inputParams['level']);
			$this->useDefaultDesc = false;
		}
		
		if (array_key_exists('health', $this->inputParams))
		{
			$this->skillHealth = intval($this->inputParams['health']);
			$this->useDefaultDesc = false;
		}
		
		if (array_key_exists('magicka', $this->inputParams))
		{
			$this->skillMagicka = intval($this->inputParams['magicka']);
			$this->useDefaultDesc = false;
		}
		
		if (array_key_exists('stamina', $this->inputParams))
		{
			$this->skillStamina = intval($this->inputParams['stamina']);
			$this->useDefaultDesc = false;
		}
		
		if (array_key_exists('spelldamage', $this->inputParams))
		{
			$this->skillSpellDamage = intval($this->inputParams['spelldamage']);
			$this->useDefaultDesc = false;
		}
		
		if (array_key_exists('weapondamage', $this->inputParams))
		{
			$this->skillWeaponDamage = intval($this->inputParams['weapondamage']);
			$this->useDefaultDesc = false;
		}
		
		if (array_key_exists('thumb', $this->inputParams)) $this->skillShowThumb = true;
		
		$this->skillMaxStat = max($this->skillMagicka, $this->skillStamina);
		$this->skillMaxDamage = max($this->skillSpellDamage, $this->skillWeaponDamage);
		
		if (IsEsoVersionAtLeast($this->version, 10)) $this->useUpdate10Costs = true;
		
		return true;
	}
	
	
	public $fixupSkills = array(
				35995 => array(
						"altmer" => 10,
						"high elf" => 10,
						"breton" => 5,
				),
				45259 => array(
						"altmer" => 20,
						"high elf" => 20,
						"breton" => 15,
				),
				45260 => array(
						"altmer" => 40,
						"high elf" => 40,
						"breton" => 30,
				),
			
				36022 => array(
						"khajiit" => 10,
						"bosmer" => 25,
						"wood elf" => 25,
				),
				45295 => array(
						"khajiit" => 20,
						"bosmer" => 35,
						"wood elf" => 35,
				),
				45296 => array(
						"khajiit" => 40,
						"bosmer" => 50,
						"wood elf" => 50,
				),
			
				36153 => array(
						"imperial" => 10,
						"redguard" => 10,
				),
				45279 => array(
						"imperial" => 20,
						"redguard" => 20,
				),
				45280 => array(
						"imperial" => 40,
						"redguard" => 40,
				),
			
			
			);
	
	
	private function FixupSkills()
	{
		$skillId = $this->skillId;
		if (!array_key_exists($skillId, $this->fixupSkills)) return false;
		
		$fixupData = $this->fixupSkills[$skillId];
		if ($fixupData == null) return false;
		if ($this->skillLine == "") return false;
		
		$this->skillData['skillLine'] = $this->skillLine;
		
		$skillLine = strtolower($this->skillLine);
		$learnedLevel = $fixupData[$skillLine];
		
		if ($learnedLevel != null) $this->skillData['learnedLevel'] = $learnedLevel;
		
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
		$descHeader = $this->skillData['descHeader'];
		$coefDesc = $this->skillData['coefDescription'];
		//if ($descHeader) $coefDesc = "|cffffff$descHeader|r\n" . $coefDesc;
		
		if ($this->useDefaultDesc)
		{
			if ($descHeader) return $this->ConvertDescriptionToHtml("|cffffff$descHeader|r\n" . $this->skillData['description']);
			return $this->ConvertDescriptionToHtml($this->skillData['description']);
		}
		elseif ($coefDesc == null || $coefDesc == "")
		{
			if ($descHeader) return $this->ConvertDescriptionToHtml("|cffffff$descHeader|r\n" . $this->skillData['description']);
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
	
	
	public function ComputeEsoSkillCost($maxCost, $level)
	{
		if (!$this->useUpdate10Costs) return $this->ComputeEsoSkillCostOld($maxCost, $level);
		
		if ($level < 1) $level = 1;
		if ($level >= 66) return $maxCost;
		
		return round($maxCost * $level / 72.0 + $maxCost / 12.0);
	}
	
	
	public function ComputeEsoSkillCostOld($maxCost, $level)
	{
		if ($level < 1) $level = 1;
		if ($level >= 66) return $maxCost;
		
		if ($level >= 1 && $level <= 50) return round($maxCost * $level * 25.0 / 1624.0 + $maxCost * 75.0 / 812.0);
		return round($maxCost * $level / 116.0 + $maxCost / 2.32);
	}
	
	
	public function LoadSkillByName()
	{
		$minedSkillTable = "minedSkills" . $this->GetTableSuffix();
		$skillTreeTable  = "skillTree" . $this->GetTableSuffix();
		$abilityId = $this->skillId;
		
		$skillName = $this->skillName;
		$skillTypeName = "";
		$skillLine = "";
		
		$result = preg_match('#(.*)/(.*)/(.*)#', $this->skillName, $matches);
		
		if ($result)
		{
			$skillTypeName = preg_replace('#-#', ' ' , $matches[1]);
			$skillLine = preg_replace('#-#', ' ' , $matches[2]);
			$skillName = preg_replace('#-#', ' ' , $matches[3]);
		}
		
		$safeSkillName = $this->db->real_escape_string($skillName);
		$safeSkillLine = $this->db->real_escape_string($skillLine);
		
		$query = "SELECT $minedSkillTable.*, $skillTreeTable.* FROM $skillTreeTable LEFT JOIN $minedSkillTable ON abilityId=$minedSkillTable.id WHERE $minedSkillTable.indexName='$safeSkillName' and skillLine='$safeSkillLine' and isPlayer=1 and (($minedSkillTable.rank=4 and isPassive=0) or (isPassive=1 and $minedSkillTable.rank=1));";
		
		$result = $this->db->query($query);
		if (!$result) return $this->ReportError("Failed to load skill data ($skillTypeName, $skillLine, $skillName!\n$query");
		
		if ($result->num_rows == 0)
		{
			$query = "SELECT $minedSkillTable.*, $skillTreeTable.* FROM $skillTreeTable LEFT JOIN $minedSkillTable ON abilityId=$minedSkillTable.id WHERE $minedSkillTable.indexName='$safeSkillName' and isPlayer=1 and (($minedSkillTable.rank=4 and isPassive=0) or (isPassive=1 and $minedSkillTable.rank=1));";
			$result = $this->db->query($query);
			if (!$result) return $this->ReportError("Failed to load skill data ($skillTypeName, $skillLine, $skillName!\n$query");
		}
		
		$this->skillData = $result->fetch_assoc();
		
		if ($this->skillData == null) return $this->ReportError("Failed to load skill data ($skillTypeName, $skillLine, $skillName!\n$query");
		return true;
	}
	
	
	public function LoadSkill()
	{
		if ($this->skillId <= 0 && $this->skillName != "") return $this->LoadSkillByName();
		
		$minedSkillTable = "minedSkills" . $this->GetTableSuffix();
		$skillTreeTable  = "skillTree" . $this->GetTableSuffix();
		$abilityId = $this->skillId;
		
		$query = "SELECT $minedSkillTable.*, $skillTreeTable.* FROM $skillTreeTable LEFT JOIN $minedSkillTable ON abilityId=$minedSkillTable.id WHERE abilityId=$abilityId;";
		
		$result = $this->db->query($query);
		if (!$result) return $this->ReportError("Failed to load skill data!");
		
		$this->skillData = $result->fetch_assoc();
		if ($this->skillData == null) return $this->ReportError("Failed to load skill data ($skillTypeName, $skillLine, $skillName!\n$query");
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
		return htmlspecialchars($string);
	}
	
	
	public function ConvertDescriptionToHtml($description)
	{
			/* Fix for double formatted text */
		//$newDesc = preg_replace('/\|c[a-fA-F0-9]{6}\|c[a-fA-F0-9]{6}([a-zA-Z _0-9\.\+\-\:\;\n\r\t$]*)\|r\|r/', '<div class="esoSkillToolWhite">$1</div>', $description);
		$newDesc = preg_replace('/\|c[a-fA-F0-9]{6}\|c([a-fA-F0-9]{6})([a-zA-Z _0-9\.\+\-\:\;\n\r\t$]*)\|r\|r/', '<div style="color:#$1;display:inline;">$2</div>', $description);
		
		//$newDesc = preg_replace('/\|c[a-fA-F0-9]{6}([a-zA-Z _0-9\.\+\-\:\;\n\r\t$]*)\|r/', '<div class="esoSkillToolWhite">$1</div>', $newDesc);
		$newDesc = preg_replace('/\|c([a-fA-F0-9]{6})([a-zA-Z _0-9\.\+\-\:\;\n\r\t$]*)\|r/', '<div style="color:#$1;display:inline;">$2</div>', $newDesc);
		
		$newDesc = preg_replace('/\n/', '<br />', $newDesc);
		return $newDesc;
	}
	
	
	public function GetSkillData($key, $default = '')
	{
		if ($this->skillData == null) return $default;
		if (!array_key_exists($key, $this->skillData)) return $default;
		return $this->skillData[$key];
	}
	
	
	public function EscapeSkill($key, $default = '')
	{
		if (!array_key_exists($key, $this->skillData)) return $default;
		return $this->escape($this->skillData[$key]);
	}
	
	
	public function GetCostClass($mechanic)
	{
		$costClass = "";
		
		if (intval($this->version >= 34))
		{
			if ($mechanic == 1)
			{
				$costClass = "esovsMagicka";
			}
			else if ($mechanic == 4)
			{
				$costClass = "esovsStamina";
			}
		}
		else
		{
			if ($mechanic == 0)
			{
				$costClass = "esovsMagicka";
			}
			else if ($mechanic == 6)
			{
				$costClass = "esovsStamina";
			}
		}
		
		return $costClass;
	}
	
	
	public function GetCostSuffix($mechanic)
	{
		if (intval($this->version >= 34))
		{
			return GetEsoCombatMechanicText34($mechanic);
		}
		
		return GetEsoCombatMechanicText($mechanic);
	}
	
	
	public function GetCostHtml()
	{
		$output = "";
		$costs = explode(",", $this->GetSkillData('cost'));
		$mechanics = explode(",", $this->GetSkillData('mechanic'));
		
		foreach ($costs as $i => $cost)
		{
			$mechanic = $mechanics[$i];
			
			//$cost = $this->ComputeEsoSkillCost($cost, $this->skillLevel) . " " . $this->GetCostSuffix($mechanic);
			$safeCost = $this->EscapeHtml($cost);
			$costClass = $this->GetCostClass($mechanic);
			
			$output .= "<div class='esovsSkillTooltipValue $costClass'>$safeCost</div>";
			$output .= "<div class='esovsSkillTooltipName'>Cost</div>";
		}
		
		return $output;
	}
	
	
	public function OutputHtml()
	{
		$output = "<div class='esovsSkillTooltip'>";
		
		$name = $this->EscapeSkill('name');
		$rank = $this->GetSkillData('rank');
		$learnedLevel = $this->GetSkillData('learnedLevel');
		$skillLine = $this->GetSkillData('skillLine');
		$channelTime = intval($this->GetSkillData('channelTime')) / 1000;
		$castTime = intval($this->GetSkillData('castTime')) / 1000;
		$radius = intval($this->GetSkillData('radius')) / 100;
		$duration = intval($this->GetSkillData('duration')) / 1000;
		$target = $this->escape($this->GetSkillData('target'));
		$area = $this->escape($this->GetSkillData('area'));
		$range = $this->escape($this->GetSkillData('range'));
		$castTimeStr = $castTime . " seconds";
		$skillType = $this->GetSkillData('type');
		$newDesc = $this->GetSkillDescription();
		$mechanic = $this->GetSkillData('mechanic');
		$effectLines = $this->GetSkillData('effectLines');
		$nextSkill = $this->GetSkillData('nextSkill');
		$icon = $this->GetSkillData('icon');
		$cost = $this->GetSkillData('cost');
		
		$realRank = $rank;
		$fullName = $name;
		
		if ($this->skillShowThumb)
		{
			$realIcon = str_replace(".dds", ".png", self::ICON_URL . $icon);
			$output .=  "<img src='$realIcon' class='esoSkillPopupIcon' />";
			//$output .= "<div style='height: 32px;'></div>";
		}
		
		if ($skillType == 'passive' || $skillType == 'Passive')
		{
			if ($realRank > 0 && !($realRank == 1 && $nextSkill <= 0)) $fullName .= " " . $this->GetRomanNumeral($realRank);
			
			$output .= "<div class='esovsSkillTooltipTitle'>$fullName</div>";
			$output .= self::TOOLTIP_DIVIDER;
		}
		else
		{
			if ($realRank >= 9) $realRank -= 8;
			if ($realRank >= 5) $realRank -= 4;
			if ($realRank > 0) $fullName .= " " . $this->GetRomanNumeral($realRank);
			
			$output .= "<div class='esovsSkillTooltipTitle'>$fullName</div>";
			$output .= self::TOOLTIP_DIVIDER;
			
			if ($channelTime > 0)
			{
				$output .= "<div class='esovsSkillTooltipValue'>$channelTime seconds</div>";
				$output .= "<div class='esovsSkillTooltipName'>Channel Time</div>";
				$castTimeStr = "";
			}
			else if ($castTime <= 0)
			{
				$castTimeStr = "Instant";
			}
			
			if ($castTimeStr != '')
			{
				$output .= "<div class='esovsSkillTooltipValue'>$castTimeStr</div>";
				$output .= "<div class='esovsSkillTooltipName'>Cast Time</div>";
			}
			
			if ($target != '')
			{
				$output .= "<div class='esovsSkillTooltipValue'>$target</div>";
				$output .= "<div class='esovsSkillTooltipName'>Target</div>";
			}
			
			if ($area != '')
			{
				$output .= "<div class='esovsSkillTooltipValue'>$area</div>";
				$output .= "<div class='esovsSkillTooltipName'>Area</div>";
			}
			
			if ($radius > 0)
			{
				$output .= "<div class='esovsSkillTooltipValue'>$radius meters</div>";
				$output .= "<div class='esovsSkillTooltipName'>Radius</div>";
			}
			
			if ($range > 0)
			{
				$output .= "<div class='esovsSkillTooltipValue'>$range</div>";
				$output .= "<div class='esovsSkillTooltipName'>Range</div>";
			}
			
			if ($duration > 0)
			{
				$output .= "<div class='esovsSkillTooltipValue'>$duration seconds</div>";
				$output .= "<div class='esovsSkillTooltipName'>Duration</div>";
			}
			
			if ($cost != '')
			{
				$output .= $this->GetCostHtml();
				//$output .= "<div class='esovsSkillTooltipValue $costClass'>$costStr</div>";
				//$output .= "<div class='esovsSkillTooltipName'>Cost</div>";
			}
			
			$output .= self::TOOLTIP_DIVIDER;
		}
		
		$output .= "<div class='esovsSkillTooltipDesc'>$newDesc</div>";
		if ($effectLines != "") $output .= " <div class='esovsSkillTooltipEffectLines'><b>NEW EFFECT</b><br/>$effectLines</div>";
		
		if ($learnedLevel > 0)
		{
			if ($skillLine != "")
				$output .= "<div class='esovsSkillTooltipLevel'>Unlocked at $skillLine Rank $learnedLevel</div>";
			else
				$output .= "<div class='esovsSkillTooltipLevel'>Unlocked at Rank $learnedLevel</div>";
		}
		
		if ($this->includeLink)
		{
			$linkUrl = "https://en.uesp.net/";
			
			if ($this->skillName != "")
			{
				$result = preg_match('#(.*)/(.*)/(.*)#', $this->skillName, $matches);
				
				if ($result)
				{
					$articleName = preg_replace('#-#', ' ', $matches[3]);
					$articleName = ucwords($articleName);
				}
				else
				{
					$articleName = preg_replace('#-#', ' ', $skillName);
					$articleName = ucwords($articleName);
				}
				
				$linkUrl .= "wiki/Online:" . $articleName;
			}
			
			$output .= "<div class='esovsSkillTooltipLink'><a href=\"$linkUrl\">Tooltips from the UESP.net</a></div>";
		}
		else
		{
			$output .= "<div class='esovsSkillTooltipLink'>Tooltips from the UESP.net</div>";
		}
		
		$output .= "</div>";
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
		header("content-type: text/html");
		
		header("Access-Control-Allow-Origin: *");
	}
	
	
	public function Render()
	{
		$this->OutputHtmlHeader();
		
		if (!$this->LoadSkill()) return "Unknown skill {$this->skillId}!";
		
		//$this->FixupSkills();
		
		$this->OutputHtml();
		
		return true;
	}
	
};


$g_EsoSkillTooltip = new CEsoSkillTooltip();
$g_EsoSkillTooltip->Render();
