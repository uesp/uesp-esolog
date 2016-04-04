<?php 

// Database users, passwords and other secrets
require_once("/home/uesp/secrets/esolog.secrets");
require_once("esoCommon.php");


class CEsoViewSkills
{

	const ESOVS_ENABLE_PROFILE = false;

	const ESOVS_HTML_TEMPLATE = "templates/esoskills_template.txt";
	const ESOVS_HTML_TEMPLATE_EMBED = "templates/esoskills_embed_template.txt";
	
	const ESOVS_ICON_URL = "http://esoicons.uesp.net/";

	public $version = "";
	public $showAll = false;
	public $highlightSkillId = 33963; // Dragonknight Standard
	public $highlightSkillType = "";
	public $highlightSkillLine = "";

	public $isFirstSkill = true;

	public $skills = array();
	public $skillIds = array();
	public $skillTree = array();
	public $skillSearchIds = array();

	public $htmlTemplate = "";
	public $isEmbedded = false;
	public $baseUrl = "";


	public function __construct ($isEmbedded = false)
	{
		$this->isEmbedded = $isEmbedded;
		
		$this->SetInputParams();
		$this->ParseInputParams();
		$this->InitDatabase();
		$this->LoadTemplate();
	}


	public function LogProfile($name, $startTime)
	{
		if (!self::ESOVS_ENABLE_PROFILE) return;

		$deltaTime = (microtime(true) - $startTime) * 1000.0;
		error_log("Profile $name = $deltaTime ms");
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


	private function OutputHtmlHeader()
	{
		ob_start("ob_gzhandler");
		header("Expires: 0");
		header("Pragma: no-cache");
		header("Cache-Control: no-cache, no-store, must-revalidate");
		header("Pragma: no-cache");
		header("Access-Control-Allow-Origin: " . $_SERVER['HTTP_ORIGIN'] . "");
	}


	private function LoadTemplate()
	{
		if ($this->isEmbedded)
			$this->htmlTemplate = file_get_contents(self::ESOVS_HTML_TEMPLATE_EMBED);
		else
			$this->htmlTemplate = file_get_contents(self::ESOVS_HTML_TEMPLATE);
	}


	private function LoadSkills()
	{
		$startTime = microtime(true);

		$minedSkillTable = "minedSkills" . $this->GetTableSuffix();
		$skillTreeTable  = "skillTree" . $this->GetTableSuffix();
		$query = "SELECT $minedSkillTable.*, $skillTreeTable.* FROM $skillTreeTable LEFT JOIN $minedSkillTable ON abilityId=$minedSkillTable.id;";
		$result = $this->db->query($query);
		if (!$result) return $this->ReportError("Failed to load skill data!");

		$result->data_seek(0);

		while (($row = $result->fetch_assoc()))
		{
			$id = $row['abilityId'];
			$index = count($this->skills);
				
			$row['__isOutput'] = false;
			$row['__index'] = $index;
				
			$this->skills[] = $row;
			$this->skillIds[$id] = $row;
		}

		$this->LogProfile("LoadSkills()", $startTime);

		$this->CreateSkillTree();
		$this->CreateSkillSearchIds();
		return true;
	}


	private function CreateSkillTree()
	{
		$startTime = microtime(true);
		$this->skillTree = array();

		foreach($this->skills as &$skill)
		{
			$this->ParseSkill($skill);
		}

		// Sort tree and fill in missing effectLines
		foreach($this->skillTree as &$skillType)
		{
			uksort($skillType, 'CompareEsoSkillLineName_Priv');
				
			foreach($skillType as &$skillLine)
			{
				usort($skillLine, 'CompareEsoSkillLine_Priv');

				foreach ($skillLine as $baseName => &$baseAbility)
				{
					foreach ($baseAbility as $rank => &$ability)
					{
						if (!is_numeric($rank)) continue;

						if ($ability['effectLines'] == "")
						{
							if ($rank > 5 && $rank < 9 && array_key_exists(5, $baseAbility))
							{
								$ability["effectLines"] = $baseAbility[5]["effectLines"];
							}
							else if ($rank > 9 && $rank < 13 && array_key_exists(9, $baseAbility))
							{
								$ability["effectLines"] = $baseAbility[9]["effectLines"];
							}
						}
					}
				}
			}
		}

		uksort($this->skillTree, 'CompareEsoSkillTypeName_Priv');

		$this->LogProfile("CreateSkillTree()", $startTime);
	}


	private function CreateSkillSearchIds()
	{
		$startTime = microtime(true);

		$this->skillSearchIds = array();

		foreach($this->skillTree as &$skillType)
		{
			foreach($skillType as &$skillLine)
			{
				foreach ($skillLine as $baseName => &$baseAbility)
				{
					foreach ($baseAbility as $rank => &$ability)
					{
						if (!is_numeric($rank)) continue;

						$this->skillSearchIds[] = $ability['abilityId'];
					}
				}
			}
		}

		$this->LogProfile("CreateSkillSearchIds()", $startTime);
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
		if (array_key_exists('showall', $this->inputParams)) $this->showAll = true;
		if (array_key_exists('skillid', $this->inputParams)) $this->highlightSkillId = intval($this->inputParams['skillid']);
		if (array_key_exists('abilityid', $this->inputParams)) $this->highlightSkillId = intval($this->inputParams['abilityid']);
		if (array_key_exists('id', $this->inputParams)) $this->highlightSkillId = intval($this->inputParams['id']);

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
		$this->isFirstSkill = true;

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

		$displayType = "none";
		$extraClass = "";

		if (($this->isFirstSkill && $this->highlightSkillType == "") || $this->highlightSkillType == $skillType)
		{
			$extraClass = "esovsSkillLineTitleHighlight";
			$displayType = "block";
			$this->isFirstSkill = false;
		}

		$output  = "";
		$output .= "<div class='esovsSkillTypeTitle'>$skillTypeUpper</div>\n";
		$output .= "<div class='esovsSkillType' style=\"display: $displayType;\">\n";
		$isFirstSkillLine = true;

		foreach ($this->skillTree[$skillType] as $skillLine => $skillLineData)
		{
			if ($displayType != "none" && ($this->highlightSkillLine == $skillLine || ($this->highlightSkillLine == "" && $isFirstSkillLine)))
				$output .= $this->GetSkillTreeLineHtml($skillLine, $skillLineData, "esovsSkillLineTitleHighlight");
				else
					$output .= $this->GetSkillTreeLineHtml($skillLine, $skillLineData, "");
						
					$isFirstSkillLine = false;
		}

		$output .= "</div>\n";
		return $output;
	}


	public function GetSkillTreeLineHtml($skillLine, $skillLineData, $extraClass = "")
	{
		$output  = "<div class='esovsSkillLineTitle $extraClass'>$skillLine</div>";

		return $output;
	}


	public function GetSkillContentHtml()
	{
		$output = "";
		$this->isFirstSkill = true;

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
		$displayType = "none";

		if (($this->isFirstSkill && $this->highlightSkillLine  == "" )|| $this->highlightSkillLine == $skillLine)
		{
			$displayType = "block";
			$this->isFirstSkill = false;
		}

		$id = $this->MakeHtmlId($skillLine);
		$output = "<div class='esovsSkillContentBlock' id='$id' style='display: $displayType;'>\n";

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


	public function SetupHighlightSkill()
	{
		$skillData = $this->skillIds[$this->highlightSkillId];
		if ($skillData == null) return false;

		$skillTypeName = $skillData['skillTypeName'];
		$names = explode("::", $skillTypeName);

		$skillType = $names[0];
		$skillLine = $names[1];
		if ($skillType == null || $skillLine == null) return false;

		$this->highlightSkillType = $skillType;
		$this->highlightSkillLine = $skillLine;

		return true;
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


	public function GetSkillContentHtml_SkillLineType($type, $typeLabel, $skillLine, $skillLineData)
	{
		$output = "";

		foreach ($skillLineData as $abilityName => $abilityData)
		{
			if ($abilityData['type'] != $type) continue;
				
			$baseAbility = $this->FindFirstAbility($abilityData);
			if ($baseAbility == null) continue;
				
			$lastAbility = $this->FindLastAbility($abilityData);
				
			$output .= $this->GetSkillContentHtml_AbilityBlock($abilityName, $lastAbility, $baseAbility, true);
				
			if ($lastAbility['maxRank'] > 1)
			{
				$output .= $this->GetSkillContentHtml_AbilityList($abilityName, $abilityData);
			}
		}

		if ($output != "")
		{
			$output = "<div class='esovsSkillBlockTypeTitle'>$typeLabel</div>" . $output;
		}

		return $output;
	}


	public function GetSkillContentHtml_AbilityBlock($abilityName, $abilityData, $baseAbility, $topLevel)
	{
		$output = "";
			
		$id = $abilityData['abilityId'];
		$index = $abilityData['__index'];
		$this->skills[$index]['__isOutput'] = true;

		$name = $baseAbility['name'];
		$type = $baseAbility['type'];
		$icon = $this->GetIconURL($baseAbility['icon']);
		$effectLines = $abilityData['effectLines'];

		$cost = $abilityData['cost'];
		$learnedLevel = $abilityData['learnedLevel'];
		$levelDesc = "";
		$costDesc = "";
		if ($learnedLevel > 0) $levelDesc = "Unlocked at rank $learnedLevel";
		$rank = $abilityData['rank'];
		$maxRank = $abilityData['maxRank'];
		$rankLabel = "";
			
		$desc = FormatRemoveEsoItemDescriptionText($abilityData['description']);

		$iconClass = "esovsAbilityBlockIcon";
		if ($type == "Passive") $iconClass = "esovsAbilityBlockPassiveIcon";

		if ($type == "Passive")
		{
			if ($rank < 0) $rank = 1;
		}
		else
		{
			$costDesc = $cost;
				
			if ($rank > 8)
			{
				$rank -= 8;
			}
			else if ($rank > 4)
			{
				$rank -= 4;
			}
		}

		if ($rank > 0 && $maxRank > 1) $rankLabel = " " . $this->GetRomanNumeral($rank);

		$extraClass = "";
		if ($id == $this->highlightSkillId) $extraClass = "esovsSearchHighlight";
			
		$output .= "<div class='esovsAbilityBlock $extraClass' skillid='$id'>" ;

		if ($topLevel)
		{
			if ($maxRank > 1)
				$output .= "<img class='esovsAbilityBlockPlus' src='resources/pointsplus_up.png' />";
				else
					$output .= "<div class='esovsAbilityBlockPlus'></div>";
		}

		$output .= "<div class='$iconClass'><img src='$icon' />";
		if ($learnedLevel > 0) $output .= "<div class='esovsAbilityBlockIconLevel'>$learnedLevel</div>";
		$output .= "</div>";
		$output .= "<div class='esovsAbilityBlockTitle'>";
		$output .= "<div class='esovsAbilityBlockTitleLabel'>";
		$output .= "<div class='esovsAbilityBlockName'>$name $rankLabel</div>";
		$output .= "<div class='esovsAbilityBlockCost' skillid='$id'>$costDesc</div>";
		$output .= "</div>";
		$output .= "<div class='esovsAbilityBlockDesc' skillid='$id'>$desc";
		if ($effectLines != "") $output .= " <div class='esovsAbilityBlockEffectLines'>$effectLines</div>";
		$output .= "</div>";
		$output .= "</div>";
		$output .= "</div>";

		return $output;
	}


	public function DoesAbilityListHaveHighlightSkill($abilityData)
	{
		foreach ($abilityData as $rank => $ability)
		{
			if (!is_numeric($rank)) continue;
			if ($ability['abilityId'] == $this->highlightSkillId) return true;
		}

		return false;
	}


	public function GetSkillContentHtml_AbilityList($abilityName, $abilityData)
	{
		$displayType = "none";
		if ($this->DoesAbilityListHaveHighlightSkill($abilityData)) $displayType = "block";
			
		$output = "<div class='esovsAbilityBlockList' style='display: $displayType;'>\n";

		foreach ($abilityData as $rank => $ability)
		{
			if (!is_numeric($rank)) continue;
			if (!$this->showAll && $ability['type'] != "Passive" && !($rank == 8 || $rank == 12)) continue;
				
			$output .= $this->GetSkillContentHtml_AbilityBlock($abilityName, $ability, $ability, false);
		}

		$output .= "</div>\n";
		return $output;
	}


	public function GetSkillsJson()
	{
		$startTime = microtime(true);
		$skillIds = array();

		foreach ($this->skills as $skill)
		{
			$skillIds[$skill['abilityId']] = $skill;
		}

		$output = json_encode($skillIds);

		$this->LogProfile("GetSkillsJson()", $startTime);
		return $output;
	}


	public function GetSkillSearchIdsJson()
	{
		$startTime = microtime(true);

		$output = json_encode($this->skillSearchIds);

		$this->LogProfile("GetSkillSearchIdsJson()", $startTime);

		return $output;

	}


	public function OutputHtml()
	{
		$startTime = microtime(true);

		$replacePairs = array(
				'{skillTree}' => $this->GetSkillTreeHtml(),
				'{skillContent}'  => $this->GetSkillContentHtml(),
				'{version}' => $this->version,
				'{versionTitle}' => $this->GetVersionTitle(),
				'{rawSkillData}' => "",
				'{coefSkillData}' => "",
				'{skillsJson}' => $this->GetSkillsJson(),
				'{skillSearchIdJson}' => $this->GetSkillSearchIdsJson(),
				'{skillHighlightId}' => $this->highlightSkillId,
				'{skillHighlightType}' => $this->highlightSkillType,
				'{skillHighlightLine}' => $this->highlightSkillLine,
		);

		$output = strtr($this->htmlTemplate, $replacePairs);

		$this->LogProfile("OutputHtml():Transform", $startTime);

		$startTime = microtime(true);

		print ($output);

		$this->LogProfile("OutputHtml():Print", $startTime);
	}


	public function Render()
	{
		$this->OutputHtmlHeader();
		$this->LoadSkills();
		$this->SetupHighlightSkill();
		$this->OutputHtml();
	}

};


function CompareEsoSkillLineName_Priv($a, $b)
{
	static $SKILLLINES = array(
			"Light Armor" => 1,
			"Medium Armor" => 2,
			"Heavy Armor" => 3,
			"Two Handed" => 1,
			"One Hand and Shield" => 2,
			"Dual Wield" => 3,
			"Bow" => 4,
			"Destruction Staff" => 5,
			"Restoration Staff" => 6,
	);

	if (!array_key_exists($a, $SKILLLINES) || !array_key_exists($b, $SKILLLINES))
	{
		return strcmp($a, $b);
	}

	return $SKILLLINES[$a] - $SKILLLINES[$b];
}


function CompareEsoSkillTypeName_Priv($a, $b)
{
	static $SKILLTYPES = array(
			"Class" => 0,
			"Dragonknight" => 1,
			"Nightblade" => 2,
			"Sorcerer" => 3,
			"Templar" => 4,
			"Weapon" => 5,
			"Armor" => 6,
			"World" => 7,
			"Guild" => 8,
			"Alliance War" => 9,
			"Racial" => 10,
			"Craft" => 11,
	);

	if (!array_key_exists($a, $SKILLTYPES) || !array_key_exists($b, $SKILLTYPES))
	{
		return strcmp($a, $b);
	}

	return $SKILLTYPES[$a] - $SKILLTYPES[$b];
}


function CompareEsoSkillLine_Priv($a, $b)
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


