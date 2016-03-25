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
			$rankLabel = "";
			$desc = "";
			
			if ($type == "Passive")
			{
				$desc = FormatRemoveEsoItemDescriptionText($lastAbility['description']);
				$rank = $lastAbility['rank'];
				if ($rank < 0) $rank = 1;
				$rankLabel = " ($rank)";
			}
			else
			{
				$desc = FormatRemoveEsoItemDescriptionText($baseAbility['description']);
			}
						
			$output .= "<div class='esovsAbilityBlock'>";
			$output .= "<div class='esovsAbilityBlockTitle'><div class='$iconClass'><img src='$icon' /></div> $name $rankLabel</div>";
			$output .= "<div class='esovsAbilityBlockDesc'>$desc</div>";
			$output .= "</div>";
		}
		
		if ($output != "")
		{
			$output = "<div class='esovsSkillBlockTypeTitle'>$typeLabel</div>" . $output;
		}
		
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


$g_EsoViewSkills = new CEsoViewSkills();
$g_EsoViewSkills->Render();