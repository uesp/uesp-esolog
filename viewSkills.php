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
	const ESOVS_ICON_URL = "http://esoicons.uesp.net/";
	
	public $version = "";
	
	public $skills = array();
	public $skillTree = array();
	
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
	
	
	private function LoadSkills()
	{
		
		$query = "SELECT * from skillTree".$this->GetTableSuffix().";";
		$result = $this->db->query($query);
		if (!$result) return $this->ReportError("Failed to load skill data!");
		
		$result->data_seek(0);
		
		while (($row = $result->fetch_assoc()))
		{
			//$row['name'] = preg_replace("#(.*) [IV]+#", "$1", $row['name']);
			//$row['baseName'] = preg_replace("#(.*) [IV]+#", "$1", $row['baseName']);
			$this->skills[] = $row;
		}
		
		$this->CreateSkillTree();
		return true;		
	}
	
	
	private function CreateSkillTree()
	{
		$this->skillTree = array();
		
		foreach($this->skills as &$skill)
		{
			$this->ParseSkill($skill);		
		}
		
		foreach($this->skillTree as &$skillType)
		{
			ksort($skillType);
			
			foreach($skillType as &$skillLine)
			{
				usort($skillLine, 'CompareEsoSkillLine');
			}
		}
		
		/*
		foreach($this->skillTree as &$skillType)
		{
			foreach($skillType as &$skillLine)
			{
				foreach($skillLine as &$ability)
				{
					ksort($ability);
				}
			}
		} // */
		
	}
	
	
	private function ParseSkill(&$skill)
	{
		$skillTypeName = $skill['skillTypeName'];
		$names = explode("::", $skillTypeName);
		if (count($names) != 2) return false;
		
		$skillType = $names[0];
		$skillLine = $names[1];
		$abilityName = $skill['name'];
		$baseName = $skill['baseName'];
		$rank = $skill['rank'];
		
		if (!array_key_exists($skillType, $this->skillTree)) $this->skillTree[$skillType] = array(); 
		if (!array_key_exists($skillLine, $this->skillTree[$skillType])) $this->skillTree[$skillType][$skillLine] = array();
		if (!array_key_exists($baseName, $this->skillTree[$skillType][$skillLine])) $this->skillTree[$skillType][$skillLine][$baseName] = array();
		
		$this->skillTree[$skillType][$skillLine][$baseName][$rank] = &$skill;
		$this->skillTree[$skillType][$skillLine][$baseName]['type'] = $skill['type'];
		$this->skillTree[$skillType][$skillLine][$baseName]['name'] = $skill['baseName'];
		
		return true;
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
	
	
	public function GetSkillTreeHtml()
	{
		$output = "";
		
		$output .= $this->GetSkillTreeTypeHtml("Dragonknight");
		$output .= $this->GetSkillTreeTypeHtml("Nightblade");
		$output .= $this->GetSkillTreeTypeHtml("Sorcerer");
		$output .= $this->GetSkillTreeTypeHtml("Templar");
		$output .= $this->GetSkillTreeTypeHtml("Weapon");
		$output .= $this->GetSkillTreeTypeHtml("Armor");
		$output .= $this->GetSkillTreeTypeHtml("World");
		$output .= $this->GetSkillTreeTypeHtml("Guild");
		$output .= $this->GetSkillTreeTypeHtml("Alliance War");
		$output .= $this->GetSkillTreeTypeHtml("Racial");
		$output .= $this->GetSkillTreeTypeHtml("Craft");
				
		return $output;	
	}
	
	
	public function GetSkillTreeTypeHtml($skillType)
	{
		$skillTypeUpper = strtoupper($skillType);
		
		$output  = "";
		$output .= "<div class='esovsSkillTypeTitle'>$skillTypeUpper</div>\n";
		$output .= "<div class='esovsSkillType' style=\"display: none;\">\n";
		
		foreach ($this->skillTree[$skillType] as $skillLine => $skillLineData)
		{
			$output .= $this->GetSkillTreeLineHtml($skillLine, $skillLineData);		
		}
		
		$output .= "</div>\n";
		return $output;	
	}
	
	
	public function GetSkillTreeLineHtml($skillLine, $skillLineData)
	{
		$output  = "<div class='esovsSkillLineTitle'>$skillLine</div>";
		
		return $output;
	}
	
	
	public function GetSkillContentHtml()
	{
		$output = "";
		
		foreach($this->skillTree as $skillType => $skillTypeData)
		{
			foreach($skillTypeData as $skillLine => $skillLineData)
			{
				$output .= $this->GetSkillContentHtml_SkillLine($skillLine, $skillLineData);
			}
		}
		
		return $output;
	}
	
	
	public function GetSkillId($skill)
	{
		$id = $skill['baseName'] . $skill['rank'];
		$id = preg_replace("#[ '\"]#", "_", $id);
		return $id;
	}
	
	
	public function MakeHtmlId($string)
	{
		return preg_replace("#[ '\"]#", "_", $string);
	}
	
	
	public function GetSkillContentHtml_SkillLine($skillLine, $skillLineData)
	{
		$id = $this->MakeHtmlId($skillLine);
		$output = "<div class='esovsSkillContentBlock' id='$id' style='display: none;'>\n";
		
		$output .= "<div class='esovsSkillContentTitle'>".$skillLine."</div>";
		
		$output .= $this->GetSkillContentHtml_SkillLineType("Ultimate", "ULTIMATES", $skillLine, $skillLineData);
		$output .= $this->GetSkillContentHtml_SkillLineType("Active",   "SKILLS",    $skillLine, $skillLineData);
		$output .= $this->GetSkillContentHtml_SkillLineType("Passive",  "PASSIVES",  $skillLine, $skillLineData);
		
		$output .= "</div>\n";
		return $output;
	}
	
	
	public function FindFirstAbility($abilityData)
	{
		for ($i = -1; $i <= 12; ++$i)
		{
			if (array_key_exists($i, $abilityData)) return $abilityData[$i];
		}
		
		return null;
	}
	
	
	public function FindLastAbility($abilityData)
	{
		if ($abilityData['type'] != "Passive" && array_key_exists(4, $abilityData)) return $abilityData[4];
		
		for ($i = 12; $i >= -1; --$i)
		{
			if (array_key_exists($i, $abilityData)) return $abilityData[$i];
		}
	
		return null;
	}
	
	
	public function GetIconURL($icon)
	{
		$icon = preg_replace('/dds$/', 'png', $icon);
		$icon = preg_replace('/^\//', '', $icon);
		
		$iconLink = self::ESOVS_ICON_URL . $icon;
		return $iconLink;
	}
	
	
	public function GetRomanNumeral($value)
	{
		static $NUMERALS = array(
				0  => '0',
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
				
		if (array_key_exists($value, $NUMERALS)) return $NUMERALS[$value];
		return $value;
	}
	
	
	public function GetSkillContentHtml_SkillLineType($type, $typeLabel, $skillLine, $skillLineData)
	{
		$output = "";
		$iconClass = "esovsAbilityBlockIcon";
		if ($type == "Passive") $iconClass = "esovsAbilityBlockPassiveIcon";
		
		foreach ($skillLineData as $abilityName => $abilityData)
		{
			if ($abilityData['type'] != $type) continue;
			
			$baseAbility = $this->FindFirstAbility($abilityData);
			if ($baseAbility == null) continue;
			$lastAbility = $this->FindLastAbility($abilityData);
			
			$name = $baseAbility['name'];
			$icon = $this->GetIconURL($baseAbility['icon']);
			$learnedLevel = $baseAbility['learnedLevel'];
			$rankLabel = "";
			$levelDesc = "";
			$desc = "";
			
			if ($type == "Passive")
			{
				$desc = FormatRemoveEsoItemDescriptionText($lastAbility['description']);
				$rank = $lastAbility['rank'];
				$learnedLevel = $lastAbility['learnedLevel'];
				if ($rank < 0) $rank = 1;
				//$rankLabel = " ($rank)";
				$rankLabel = " " . $this->GetRomanNumeral($rank);
			}
			else
			{
				$desc = FormatRemoveEsoItemDescriptionText($lastAbility['description']);
				$learnedLevel = $lastAbility['learnedLevel'];
				$rank = $lastAbility['rank'];
				$rankLabel = " " . $this->GetRomanNumeral($rank);
			}

			if ($learnedLevel > 0) $levelDesc = "Unlocked at rank $learnedLevel";
						
			$output .= "<div class='esovsAbilityBlock'>";
			$output .= "<div class='esovsAbilityBlockTitle'><div class='$iconClass'><img src='$icon' /></div>";
			$output .= "<div class='esovsAbilityBlockTitleLabel'>$name $rankLabel<br /><div class='esovsAbilityBlockLevelDesc'>$levelDesc</div></div></div>";
			$output .= "<div class='esovsAbilityBlockDesc'>$desc</div>";
			$output .= "</div>";
			$output .= $this->GetSkillContentHtml_AbilityList($abilityName, $abilityData);
		}
		
		if ($output != "")
		{
			$output = "<div class='esovsSkillBlockTypeTitle'>$typeLabel</div>" . $output;
		}
		
		return $output;
	}
	
	
	public function GetSkillContentHtml_AbilityList($abilityName, $abilityData)
	{
		$output = "<div class='esovsAbilityBlockList' style='display: none;'>\n";
		
		foreach ($abilityData as $rank => $ability)
		{
			if (!is_numeric($rank)) continue;
			$output .= $this->GetSkillContentHtml_Ability($abilityName, $rank, $ability);
		}
		
		$output .= "</div>\n";
		return $output;
	}
	
	
	public function GetSkillContentHtml_Ability($abilityName, $abilityRank, $abilityData)
	{
		$type = $abilityData['type'];
		$rank = $abilityData['rank'];
		
		if ($type != "Passive" && !($rank == 8 || $rank == 12)) return "";
		
		$name = $abilityData['name'];
		$icon = $this->GetIconURL($abilityData['icon']);
		$learnedLevel = $abilityData['learnedLevel'];
		$desc = FormatRemoveEsoItemDescriptionText($abilityData['description']);
		
		$iconClass = "esovsAbilityBlockIcon";
		if ($type == "Passive") $iconClass = "esovsAbilityBlockPassiveIcon";
		
		$levelDesc = "";
		if ($learnedLevel > 0) $levelDesc = "<br /><div class='esovsAbilityBlockLevelDesc'>Unlocked at rank $learnedLevel</div>";
		
		if ($type == "Passive")
		{
			if ($rank < 0) $rank = 1;
		}
		else if ($rank > 8)
		{
			$rank -= 8;
			$levelDesc = "";
		}
		else if ($rank > 4)
		{
			$rank -= 4;
			$levelDesc = "";
		}
		
		$rankLabel = " " . $this->GetRomanNumeral($rank);
		
		$output = "<div class='esovsAbilityBlockListItem'>";
		$output .= "<div class='esovsAbilityBlock'>";
		$output .= "<div class='esovsAbilityBlockTitle'><div class='$iconClass'><img src='$icon' /></div>";
		$output .= "<div class='esovsAbilityBlockTitleLabel'>$name $rankLabel$levelDesc</div></div>";
		$output .= "<div class='esovsAbilityBlockDesc'>$desc</div>";
		
		$output .= "</div></div>\n";
		return $output;
	}
	
	
	public function OutputHtml()
	{
		$replacePairs = array(
				
				'{skillTree}' => $this->GetSkillTreeHtml(),
				'{skillContent}'  => $this->GetSkillContentHtml(),
				'{version}' => $this->version,
				'{versionTitle}' => $this->GetVersionTitle(),
		);
	
		$output = strtr($this->htmlTemplate, $replacePairs);
	
		print ($output);
	}
	
	
	public function Render()
	{
		$this->LoadSkills();
		$this->OutputHtml();
	}
	
};


function CompareEsoSkillLine($a, $b)
{
	$a1 = null;
	$b1 = null;
	
	if (array_key_exists(1, $a)) 
		$a1 = $a[1];
	else if (array_key_exists(-1, $a))
		$a1 = $a[-1];
	else
		return 1;
	
	if (array_key_exists(1, $b))
		$b1 = $b[1];
	else if (array_key_exists(-1, $b))
		$b1 = $b[-1];
	else
		return -1;
	
	return $a1['skillIndex'] - $b1['skillIndex'];
}


$g_EsoViewSkills = new CEsoViewSkills();
$g_EsoViewSkills->Render();