<?php 

// Database users, passwords and other secrets
require_once("/home/uesp/secrets/esolog.secrets");
require_once(__DIR__."/esoCommon.php");


class CEsoViewSkills
{

	const ESOVS_ENABLE_PROFILE = false;

	public $ESOVS_HTML_TEMPLATE = "";
	public $ESOVS_HTML_TEMPLATE_EMBED = "";
	
	const ESOVS_ICON_URL = UESP_ESO_ICON_URL;

	public $version = "";
	public $showAll = false;
	public $highlightSkillId = 33963; // Dragonknight Standard
	public $highlightSkillType = "";
	public $highlightSkillLine = "";

	public $isFirstSkill = true;
	public $useUpdate10Costs = true;

	public $skills = array();
	public $skillIds = array();
	public $skillTree = array();
	public $skillSearchIds = array();
	public $skillHealth = 20000;
	public $skillMagicka = 20000;
	public $skillStamina = 20000;
	public $skillLevel = 66;
	public $skillSpellDamage = 2000;
	public $skillWeaponDamage = 2000;

	public $htmlTemplate = "";
	public $isEmbedded = false;
	public $displayType = "summary";
	public $showLeftDetails = true;
	public $displayClass = "all";
	public $displayRace = "all";
	public $displayMenuBar = true;
	public $displaySkillBar = false;
	public $baseUrl = "";
	public $basePath = "";
	public $baseResource = "";
	
	public $initialData = array();
	public $activeData = array();
	public $passiveData = array();
	public $enableWerewolf = false;
	public $enableOverload = false;
	public $activeWeaponBar3 = "-1";
	public $activeWeaponBar4 = "-1";
	
	public $initialSkillBarData = array(
			0 => array( 
					0 => array(), 
					1 => array(), 
					2 => array(), 
					3 => array(),
					4 => array(), 
					5 => array() ),
			1 => array(
					0 => array(),
					1 => array(),
					2 => array(),
					3 => array(),
					4 => array(),
					5 => array() ),
			2 => array(
					0 => array(),
					1 => array(),
					2 => array(),
					3 => array(),
					4 => array(),
					5 => array() ),
			3 => array(
					0 => array(),
					1 => array(),
					2 => array(),
					3 => array(),
					4 => array(),
					5 => array() ),
		);
	
	public $dataLoaded = false;	
	public $activeSkillBar = 1;
	
	
	public $IGNORE_SKILLS = array(
			"Wall of Storms" => 1,
			"Wall of Fire" => 1,
			"Wall of Frost" => 1,
			"Flame Impulse" => 1,
			"Frost Impulse" => 1,
			"Shock Impulse" => 1,
			"Flame Touch" => 1,
			"Frost Touch" => 1,
			"Shock Touch" => 1,
			"Fire Impulse" => 1,
			"Frost Impulse" => 1,
			"Shock Impulse" => 1,
			"Fire Storm" => 1,
			"Thunder Storm" => 1,
			"Ice Storm" => 1,
			"Fiery Rage" => 1,
			"Thunderous Rage" => 1,
			"Icy Rage" => 1,
			"Eye of Flame" => 1,
			"Eye of Lightning" => 1,
			"Eye of Frost" => 1,
	);
	


	public function __construct ($isEmbedded = false, $displayType = "summary", $parseParams = true)
	{
		//SetupUespSession();
		
		$this->ESOVS_HTML_TEMPLATE = __DIR__."/templates/esoskills_template.txt";
		$this->ESOVS_HTML_TEMPLATE_EMBED = __DIR__."/templates/esoskills_embed_template.txt";
		
		$this->isEmbedded = $isEmbedded;
		$this->displayType = $displayType;
		
		if ($parseParams)
		{
			$this->SetInputParams();
			$this->ParseInputParams();
		}
		
		$this->InitDatabase();
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
		
		UpdateEsoPageViews("skillViews");

		return true;
	}


	private function OutputHtmlHeader()
	{
		ob_start("ob_gzhandler");
		header("Expires: 0");
		header("Pragma: no-cache");
		header("Cache-Control: no-cache, no-store, must-revalidate");
		header("Pragma: no-cache");
		header("Access-Control-Allow-Origin: *");
	}


	public function LoadTemplate()
	{
		//$templateFile = $this->basePath;
		$templateFile = "";
		
		if ($this->isEmbedded)
			$templateFile .= $this->ESOVS_HTML_TEMPLATE_EMBED;
		else
			$templateFile .= $this->ESOVS_HTML_TEMPLATE;
			
		$this->htmlTemplate = file_get_contents($templateFile);
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
		
			/* Destruction skills */

		$this->LogProfile("LoadSkills()", $startTime);
		
		$this->CreateSkillTree();
		$this->CreateSkillSearchIds();
		//$this->FindBaseAbilityForInitialActiveData();
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
					if (!is_array($baseAbility)) continue;
					
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
		
		//$this->skillTree[$skillType][$skillLine]['skillType'] = $skillType;

		$this->skillTree[$skillType][$skillLine][$baseName][$rank] = &$skill;
		$this->skillTree[$skillType][$skillLine][$baseName]['type'] = $skill['type'];
		$this->skillTree[$skillType][$skillLine][$baseName]['name'] = $skill['baseName'];

		return true;
	}
	
	
	public function ParseLevel($level)
	{
		$value = 66;	
	
		if (is_numeric($level))
		{
			$value = intval($level);
		}
		else if (preg_match("#^[vV]([0-9]+)#", trim($level), $matches))
		{
			$value = intval($matches[1]) + 50;
		}
		else if (preg_match("#^CP([0-9]+)#i", trim($level), $matches))
		{
			$value = floor(intval($matches[1])/10) + 50;
		}
		
		if ($value < 1) $value = 1;
		if ($value > 66) $value = 66;
		
		return $value;
	}
	
	
	public function FormatLevel($level)
	{
		if ($level <= 50) return $level;
		if (UESP_SHOWCPLEVEL) return "CP" . (($level - 50)*10);
		return "v" . ($level - 50);
	}


	private function ParseInputParams ()
	{
		if (array_key_exists('version', $this->inputParams)) $this->version = urldecode($this->inputParams['version']);
		
		if (array_key_exists('showall', $this->inputParams)) 
		{
			if ($this->inputParams['showall'] == '')
				$this->showAll = true;
			else
				$this->showAll = (intval($this->inputParams['showall']) != 0) ? true : false;
		}
		
		if (array_key_exists('skillid', $this->inputParams)) $this->highlightSkillId = intval($this->inputParams['skillid']);
		if (array_key_exists('abilityid', $this->inputParams)) $this->highlightSkillId = intval($this->inputParams['abilityid']);
		if (array_key_exists('id', $this->inputParams)) $this->highlightSkillId = intval($this->inputParams['id']);
		
		if (array_key_exists('level', $this->inputParams)) $this->skillLevel = $this->ParseLevel($this->inputParams['level']);
		
		if (array_key_exists('health', $this->inputParams)) $this->skillHealth = intval($this->inputParams['health']);
		if (array_key_exists('magicka', $this->inputParams)) $this->skillMagicka = intval($this->inputParams['magicka']);
		if (array_key_exists('stamina', $this->inputParams)) $this->skillStamina = intval($this->inputParams['stamina']);
		if (array_key_exists('spelldamage', $this->inputParams)) $this->skillSpellDamage = intval($this->inputParams['spelldamage']);
		if (array_key_exists('weapondamage', $this->inputParams)) $this->skillWeaponDamage = intval($this->inputParams['weapondamage']);
		
		if (array_key_exists('display', $this->inputParams)) 
		{
			$displayType = urldecode($this->inputParams['display']);
			
			if ($displayType == "summary")
				$this->displayType = "summary";
			else if ($displayType == "select")
				$this->displayType = "select";
		}
		
		if (IsEsoVersionAtLeast($this->version, 10)) $this->useUpdate10Costs = true;
		
		if ($this->displayType == "summary")
		{
			$this->showLeftDetails = true;
			$this->displayClass = "all";
			$this->displayRace = "all";
			$this->displayMenuBar = true;
			$this->displaySkillBar = false;
		}
		else if ($this->displayType == "select")
		{
			$this->showLeftDetails = false;
			$this->displayClass = "Dragonknight";
			$this->displayRace = "Argonian";
			$this->displayMenuBar = false;
			$this->displaySkillBar = true;
		}

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

		$output .= $this->GetSkillTreeTypeHtml("Dragonknight", true);
		if (IsEsoVersionAtLeast($this->version, "22") && CanViewEsoLogVersion("22pts")) $output .= $this->GetSkillTreeTypeHtml("Necromancer", true);
		$output .= $this->GetSkillTreeTypeHtml("Nightblade", true);
		$output .= $this->GetSkillTreeTypeHtml("Sorcerer", true);
		$output .= $this->GetSkillTreeTypeHtml("Templar", true);
		if (IsEsoVersionAtLeast($this->version, "14") && CanViewEsoLogVersion("14pts")) $output .= $this->GetSkillTreeTypeHtml("Warden", true);		
		$output .= $this->GetSkillTreeTypeHtml("Weapon", false);
		$output .= $this->GetSkillTreeTypeHtml("Armor", false);
		$output .= $this->GetSkillTreeTypeHtml("World", false);
		$output .= $this->GetSkillTreeTypeHtml("Guild", false);
		$output .= $this->GetSkillTreeTypeHtml("Alliance War", false);
		$output .= $this->GetSkillTreeTypeHtml("Racial", false);
		$output .= $this->GetSkillTreeTypeHtml("Craft", false);
		
		if ($this->displayType == "select") $output .= $this->GetSkillPointsHtml();

		return $output;
	}
	
	
	public function GetUsedSkillPoints()
	{
		if ($this->initialData == null) return 0;
		
		$usedPoints = $this->initialData['UsedPoints'];
		if ($usedPoints == null || $usedPoints < 0) $usedPoints = 0;
		
		return $usedPoints;
	}

	
	public function GetSkillPointsHtml()
	{
		$usedPoints = $this->GetUsedSkillPoints();
		
		$output  = "<div id='esovsSkillPointsContent'>";
		$output .= "	<div id='esovsSkillPointsTitle'>Used Skill Points:</div> <div id='esovsSkillPoints'>$usedPoints</div>";
		$output .= "	<button id='esovsSkillReset'>Reset All Skills</button>";
		$output .= "</div>";
		return $output;
	}
	

	public function GetSkillTreeTypeHtml($skillType, $isClass)
	{
		$isClassVisible = true;
		$displayType = "none";
		$extraClass = "";
		$skillTypeUpper = strtoupper($skillType);
		$titleDisplayType = "block";
		
		if ($isClass && $this->displayClass != "all" && strcasecmp($this->displayClass, $skillType) != 0)
		{
			$isClassVisible = false;
			$displayType = "none";
			$titleDisplayType = "none";
		}
			
		if ($isClassVisible && (($this->isFirstSkill && $this->highlightSkillType == "") || $this->highlightSkillType == $skillType))
		{
			$extraClass = "esovsSkillLineTitleHighlight";
			$displayType = "block";
			$this->isFirstSkill = false;
		}
		
		$output  = "";
		$output .= "<div class='esovsSkillTypeTitle' style=\"display: $titleDisplayType;\">$skillTypeUpper</div>\n";
		$output .= "<div class='esovsSkillType' skilltypeid=\"$skillType\" style=\"display: $displayType;\">\n";
		$isFirstSkillLine = true;

		foreach ($this->skillTree[$skillType] as $skillLine => $skillLineData)
		{
			$isRaceVisible = true;
			
			if ($skillType == "Racial")
			{
				$isRaceVisible = false;
				if ($this->displayRace == "all" || startsWithNoCase($skillLine, $this->displayRace)) $isRaceVisible = true;				
			}
			
			if ($displayType != "none" && ($this->highlightSkillLine == $skillLine || ($this->highlightSkillLine == "" && $isFirstSkillLine)))
				$output .= $this->GetSkillTreeLineHtml($skillLine, $skillLineData, "esovsSkillLineTitleHighlight", $isRaceVisible);
			else
				$output .= $this->GetSkillTreeLineHtml($skillLine, $skillLineData, "", $isRaceVisible);
						
			$isFirstSkillLine = false;
		}

		$output .= "</div>\n";
		return $output;
	}


	public function GetSkillTreeLineHtml($skillLine, $skillLineData, $extraClass = "", $isVisible = true)
	{
		$displayType = "block";
		
		if (!$isVisible) 
		{
			$displayType = "none";
			$extraClass .= " esovsSkillLineDisabled";
		}
		
		$output  = "<div class='esovsSkillLineTitle $extraClass' skilllineid=\"$skillLine\" style=\"display: $displayType;\">$skillLine</div>";

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
				$output .= $this->GetSkillContentHtml_SkillLine($skillLine, $skillLineData, $skillType);
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


	public function GetSkillContentHtml_SkillLine($skillLine, $skillLineData, $skillType)
	{
		$displayType = "none";

		if (($this->isFirstSkill && $this->highlightSkillLine == "") || $this->highlightSkillLine == $skillLine)
		{
			$displayType = "block";
			$this->isFirstSkill = false;
		}

		$id = $this->MakeHtmlId($skillLine);
		$output = "<div class='esovsSkillContentBlock' id='$id' style='display: $displayType;' skilltype='$skillType'>\n";
		
		if ($this->displayType == "select")
		{
			$output .= "<button class='esovsSkillLineResetAll'>Reset Line</button>";
			$output .= "<button class='esovsSkillLinePurchaseAll'>Purchase Line</button> ";
		}

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
	
	
	public function FindPurchasedAbility($abilityData)
	{
		if ($this->initialData == null) return null;
		
		for ($i = -1; $i <= 12; ++$i)
		{
			if (!array_key_exists($i, $abilityData) || $abilityData[$i] == null) continue;
			$abilityId = $abilityData[$i]['abilityId'];
			if (!array_key_exists($abilityId, $this->initialData) || $this->initialData[$abilityId] == null) continue;
			
			return $abilityData[$i];
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
		$iconLink = self::ESOVS_ICON_URL . "/" . $icon;
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
			if (array_key_exists('skillIndex', $abilityData) && $abilityData['skillIndex'] < 0) continue;
			
			$baseAbility = $this->FindFirstAbility($abilityData);
			if ($baseAbility == null) continue;
			
			$lastAbility = $this->FindLastAbility($abilityData);
			$isPurchased = false;
			
			if ($baseAbility['type'] == "Passive")
				$baseAbilityId = $baseAbility['abilityId'];
			else
				$baseAbilityId = $lastAbility['abilityId'];
			
			if ($this->displayType == "select")
			{
				$purchasedAbility = $this->FindPurchasedAbility($abilityData);
				
				if ($purchasedAbility != null) 
				{
					$lastAbility = $purchasedAbility;
					$baseAbility = $lastAbility;
					$abilityName = $lastAbility['name'];
					$isPurchased = true;
				}
			}
			
			$output .= $this->GetSkillContentHtml_AbilityBlock($abilityName, $lastAbility, $baseAbility, true, $isPurchased, $baseAbilityId);
				
			if ($lastAbility['maxRank'] > 1 || $this->displayType == "select")
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


	public function GetSkillContentHtml_AbilityBlock($abilityName, $abilityData, $baseAbility, $topLevel, $isPurchased, $baseAbilityId)
	{
		$output = "";
					
		if ($baseAbilityId == null) 
			$baseId = $baseAbility['abilityId'];
		else
			$baseId = $baseAbilityId;
		
		$id = $abilityData['abilityId'];
		$index = $abilityData['__index'];
		$name = $baseAbility['name'];
		$baseName = $abilityData['baseName'];
		
		if ($baseName != "" && array_key_exists($baseName, $this->IGNORE_SKILLS) && $this->IGNORE_SKILLS[$baseName] != null) return "";
		
		$type = $baseAbility['type'];
		$icon = $this->GetIconURL($baseAbility['icon']);
		$effectLines = $abilityData['effectLines'];
		$mechanic = $baseAbility['mechanic'];
		$skillType = GetEsoSkillTypeText($baseAbility['skillType']);
		$skillLine = $baseAbility['skillLine'];
		$classType = $baseAbility['classType'];
		$raceType = $baseAbility['raceType'];

		$cost = $abilityData['cost'];
		$learnedLevel = $abilityData['learnedLevel'];
		if ($learnedLevel > 50) $learnedLevel = 50;
		$costDesc = "";
		$rank = $abilityData['rank'];
		$origRank = $rank;
		$maxRank = $abilityData['maxRank'];
		$rankLabel = "";
		$morph = 0;
		
		if ($topLevel && $type == "Passive" && $this->displayType == "select")
		{
			$cost = $baseAbility['cost'];
			$learnedLevel = $baseAbility['learnedLevel'];
			$rank = $baseAbility['rank'];
			$effectLines = $baseAbility['effectLines'];
			$id = $baseAbility['abilityId'];
			$index = $baseAbility['__index'];
		}
		
		$this->skills[$index]['__isOutput'] = true;
			
		$desc = FormatRemoveEsoItemDescriptionText($abilityData['description']);
		$extraIconAttr = "";
		$iconClass = "esovsAbilityBlockIcon";
		if ($type == "Passive") $iconClass = "esovsAbilityBlockPassiveIcon";

		if ($type == "Passive")
		{
			if ($rank < 0) $rank = 1;
			$morph = -1;
			$origRank = 1;
		}
		else
		{
			$costDesc = $cost;
				
			if ($rank > 8)
			{
				$morph = 2;
				$rank -= 8;
			}
			else if ($rank > 4)
			{
				$morph = 1;
				$rank -= 4;
			}
			
			if ($this->displayType == "select" && $topLevel) 
			{
				if ($isPurchased)
					$extraIconAttr = "draggable='true'";
				else
					$extraIconAttr = "draggable='false'";
			}
		}

		if ($rank > 0 && $maxRank > 1) $rankLabel = " " . $this->GetRomanNumeral($rank);

		$extraClass = "esovsAbilityBlockHover";
		
		if ($this->displayType == "select")
		{
			if ($topLevel && !$isPurchased) 
				$extraClass .= " esovsAbilityBlockNotPurchase";
			else if ($topLevel)
				$extraClass .= "";
			else
				$extraClass .= " esovsAbilityBlockSelect";
		}
		
		if ($id == $this->highlightSkillId && $this->displayType == "summary") $extraClass .= " esovsSearchHighlight";
			
		$output .= "<div class='esovsAbilityBlock $extraClass' morph='$morph' skillid='$id' origskillid='$baseId' rank='$rank' origrank='$origRank' maxrank='$maxRank' abilitytype='$type' skilltype='$skillType' skilline='$skillLine' classtype='$classType' racetype='$raceType'>" ;

		if ($topLevel)
		{
			if ($this->displayType == "select")
			{
				$output .= "<img class='esovsAbilityBlockPlusSelect' src='//esolog.uesp.net/resources/pointsplus_up.png' />";
			}
			else if ($maxRank > 1)
			{
				$output .= "<img class='esovsAbilityBlockPlus' src='//esolog.uesp.net/resources/pointsplus_up.png' />";
			}
			else
			{
				$output .= "<div class='esovsAbilityBlockPlus'></div>";
			}
		}
		
		$output .= "<div class='$iconClass' $extraIconAttr><img alt='' src='$icon' />";
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
	
	
	public function FindBaseAbilityForInitialActiveData()
	{
		foreach ($this->activeData as $abilityId => $activeData)
		{
			$this->activeData[$abilityId]['baseAbilityId'] = $this->FindBaseAbilityForActiveData($abilityId);
		}
	}
	
	
	public function FindBaseAbilityForActiveData($abilityId)
	{
		$skillData = $this->skillIds[$abilityId];
		if ($skillData == null) { return $abilityId; }
				
		while ($skillData['prevSkill'] > 0)
		{
			$prevId = $skillData['prevSkill'];
			$skillData = $this->skillIds[$prevId];
			if ($skillData == null) { return $abilityId; }
		}
		
		$baseAbilityId = $skillData['abilityId'];
		if ($skillData['isPassive'] != 0) { return $baseAbilityId; }
				
		while ($skillData['nextSkill'] > 0)
		{
			$nextId = $skillData['nextSkill'];
			$skillData = $this->skillIds[$nextId];
			if ($skillData == null) return $baseAbilityId;
			if ($skillData['rank'] == 4) return $skillData['abilityId'];
		}
		
		return $baseAbilityId;
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
		$extraClass = "";
		$baseId = "";
		if ($this->displayType == "summary" && $this->DoesAbilityListHaveHighlightSkill($abilityData)) $displayType = "block";
			
		$output = "<div class='esovsAbilityBlockList $extraClass' style='display: $displayType;'>\n";
		
		if ($this->displayType == "select")
		{
			$output .= "<div class='esovsAbilityBlock esovsAbilityBlockHover esovsAbilityBlockSelect esovsAbilityNone' skillid='-1'>";
			$output .= "<img src='//esolog.uesp.net/resources/edit_cancel_up.png'> Refund Ability";
			$output .= "</div>";
		}

		foreach ($abilityData as $rank => $ability)
		{
			if (!is_numeric($rank)) continue;
			
			if (!$this->showAll && $ability['type'] != "Passive")
			{																																		// TODO: Volendrung skills
				if (!($rank == 8 || $rank == 12 || ($rank == 4 && $this->displayType == "select")) ) continue;
			}
			
			if ($baseId == "") $baseId = $ability['abilityId'];
				
			$output .= $this->GetSkillContentHtml_AbilityBlock($abilityName, $ability, $ability, false, false, $baseId);
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
		$output = $this->CreateOutputHtml();

		$startTime = microtime(true);

		print ($output);

		$this->LogProfile("OutputHtml():Print", $startTime);
	}
	
	
	public function GetUpdateDate()
	{
		$query = "SELECT * FROM logInfo WHERE id='lastSkillUpdate';";
		$result = $this->db->query($query);
		if (!$result) return "";
		
		$row = $result->fetch_assoc();
		$updateDate = $row['value'];
		
		return $updateDate;
	}
	
	
	public function GetLeftBlockDisplay()
	{
		if ($this->showLeftDetails) return "block";
		return "none";
	}
	
	
	public function GetMenuBarDisplay()
	{
		if ($this->displayMenuBar) return "block";
		return "none";
	}
	
	
	public function GetRightBlockMargin()
	{
		if ($this->showLeftDetails)	return "";
		return "margin-left: 0;";
	}
	
	
	public function GetSkillBarHtml()
	{
		if (!$this->displaySkillBar) return "";
		$output = "<div id='esovsSkillBar'>";
		
		$extraClass1 = "";
		$extraClass2 = "";
		$extraClass3 = "";
		$extraClass4 = "";
		
		$activeBar3 = $this->activeWeaponBar3;
		$activeBar4 = $this->activeWeaponBar4;
		$activeBar3 = -1;
		$activeBar4 = -1;
		
		$barTitle3 = "Overload";
		$barTitle4 = "Werewolf";
		
		$barDisplay3 = "display: none;";
		$barDisplay4 = "display: none;";
		
		if ($this->enableOverload) $barDisplay3 = "";
		if ($this->enableWerewolf) $barDisplay4 = "";
		
		if ($this->activeSkillBar == 1) $extraClass1 = "esovsSkillBarHighlight";
		if ($this->activeSkillBar == 2) $extraClass2 = "esovsSkillBarHighlight";
		if ($this->activeSkillBar == 3) $extraClass3 = "esovsSkillBarHighlight";
		if ($this->activeSkillBar == 4) $extraClass4 = "esovsSkillBarHighlight";
		
		$output .= "<div id='esovsSkillBar1' class='esovsSkillBar $extraClass1' skillbar='1' activeweaponbar='1'>";
		$output .= "	<div class='esovsSkillBarTitle'>Bar 1</div>";
		$output .= $this->GetSkillBarSlotHtml(0, 0);
		$output .= $this->GetSkillBarSlotHtml(0, 1);
		$output .= $this->GetSkillBarSlotHtml(0, 2);
		$output .= $this->GetSkillBarSlotHtml(0, 3);
		$output .= $this->GetSkillBarSlotHtml(0, 4);
		$output .= "	&nbsp; &nbsp; &nbsp; &nbsp; ";
		$output .= $this->GetSkillBarSlotHtml(0, 5);
		$output .= "</div>";
		
		$output .= "<div id='esovsSkillBar2' class='esovsSkillBar $extraClass2' skillbar='2' activeweaponbar='2'>";
		$output .= "	<div class='esovsSkillBarTitle'>Bar 2</div>";
		$output .= $this->GetSkillBarSlotHtml(1, 0);
		$output .= $this->GetSkillBarSlotHtml(1, 1);
		$output .= $this->GetSkillBarSlotHtml(1, 2);
		$output .= $this->GetSkillBarSlotHtml(1, 3);
		$output .= $this->GetSkillBarSlotHtml(1, 4);
		$output .= "	&nbsp; &nbsp; &nbsp; &nbsp; ";
		$output .= $this->GetSkillBarSlotHtml(1, 5);
		$output .= "</div>";
		
		$output .= "<div id='esovsSkillBar3' class='esovsSkillBar $extraClass3' skillbar='3' style='$barDisplay3' activeweaponbar='$activeBar3'>";
		$output .= "	<div class='esovsSkillBarTitle'>$barTitle3</div>";
		$output .= $this->GetSkillBarSlotHtml(2, 0);
		$output .= $this->GetSkillBarSlotHtml(2, 1);
		$output .= $this->GetSkillBarSlotHtml(2, 2);
		$output .= $this->GetSkillBarSlotHtml(2, 3);
		$output .= $this->GetSkillBarSlotHtml(2, 4);
		$output .= "	&nbsp; &nbsp; &nbsp; &nbsp; ";
		$output .= $this->GetSkillBarSlotHtml(2, 5);
		$output .= "</div>";
		
		$output .= "<div id='esovsSkillBar4' class='esovsSkillBar $extraClass4' skillbar='4' style='$barDisplay4' activeweaponbar='$activeBar4'>";
		$output .= "	<div class='esovsSkillBarTitle'>$barTitle4</div>";
		$output .= $this->GetSkillBarSlotHtml(3, 0);
		$output .= $this->GetSkillBarSlotHtml(3, 1);
		$output .= $this->GetSkillBarSlotHtml(3, 2);
		$output .= $this->GetSkillBarSlotHtml(3, 3);
		$output .= $this->GetSkillBarSlotHtml(3, 4);
		$output .= "	&nbsp; &nbsp; &nbsp; &nbsp; ";
		$output .= $this->GetSkillBarSlotHtml(3, 5);
		$output .= "</div>";
		
		$output .= "</div>";
		return $output;
	}
	
	
	public function GetSkillBarSlotHtml($barIndex, $slotIndex)
	{
		$outBarIndex = $barIndex + 1;
		$outSlotIndex = $slotIndex + 1;
		$classSuffix = "" . $outBarIndex . $outSlotIndex. "";
		
		$draggable = "false";
		$skillId = "0";
		$origSkillId = "0";
		$imageSrc = "";
		
		if ($this->initialSkillBarData[$barIndex] != null && $this->initialSkillBarData[$barIndex][$slotIndex] != null)
		{
			$skillData = $this->initialSkillBarData[$barIndex][$slotIndex];
			
			if ($skillData['skillId'] > 0)
			{
				$abilityData = $this->skillIds[$skillData['skillId']];
				
				$draggable = "true";
				$skillId = $skillData['skillId'];
				$origSkillId = $skillData['origSkillId'];
				
				if ($abilityData != null) $imageSrc = $this->GetIconURL($abilityData['texture']);
			}
		}
						
		$output = "<div class='esovsSkillBarItem'>";
		$output .= "	<img class='esovsSkillBarIcon' alt='' draggable='$draggable' id='esovsSkillIcon$classSuffix' skillindex='$outSlotIndex' skillbar='$outBarIndex' skillid='$skillId' origskillid='$origSkillId' src='$imageSrc'>";
		$output .= "</div>";
		
		return $output;
	}
	
	
	public function GetCurrentVersion() 
	{
		return GetEsoDisplayVersion($this->version);
	}
	
	
	public function GetVersionList($currentVersion) 
	{
		$output = "";
		
		$query = "SHOW TABLES LIKE 'minedSkills%';";
		$result = $this->db->query($query);
		if ($result === false) return $this->ReportError("Failed to list all minedSkills table versions!");
		
		$tables = array();
		$output .= "<form action='?' method='get'>";
		if ($this->showall) $output .= "<input type='hidden' name='showall' value='1'>";
		if ($this->highlightSkillId) $output .= "<input type='hidden' name='id' value='{$this->highlightSkillId}'>";
		if ($this->skillLevel) $output .= "<input type='hidden' name='level' value='{$this->skillLevel}'>";
		if ($this->skillHealth) $output .= "<input type='hidden' name='health' value='{$this->skillHealth}'>";
		if ($this->skillMagicka) $output .= "<input type='hidden' name='magicka' value='{$this->skillMagicka}'>";
		if ($this->skillStamina) $output .= "<input type='hidden' name='stamina' value='{$this->skillStamina}'>";
		if ($this->skillSpellDamage) $output .= "<input type='hidden' name='spelldamage' value='{$this->skillSpellDamage}'>";
		if ($this->skillWeaponDamage) $output .= "<input type='hidden' name='weapondamage' value='{$this->skillWeaponDamage}'>";
		if ($this->displayType) $output .= "<input type='hidden' name='display' value='{$this->displayType}'>";
		$output .= "<select name='version'>";
		
		$tables = array();
		
		while (($row = $result->fetch_row())) 
		{
			$table = $row[0];
			$version = substr($table, 11);
			if ($version == "") $version = GetEsoUpdateVersion();
						
			$tables[$version] = $version;
		}
		
		natsort($tables);
		
		foreach ($tables as $version) 
		{
			$select = "";
			if (strcasecmp($version, $currentVersion) == 0) $select = "selected";
			$output .= "<option $select>$version</option>";
		}
		
		$output .= "</select>";
		$output .= "<input type='submit' value='Go'>";
		$output .= "</form>";
		
		return $output;
	}
	
	
	public function GetSkillHistoryLink()
	{
		if ($this->highlightSkillId <= 0) return "";
		return "//esolog.uesp.net/viewSkillCoef.php?abilityid={$this->highlightSkillId}";
	}
	
	
	public function CreateOutputHtml()
	{
		global $ESO_DESTRUCTION_SKILLS;
		global $ESO_POISON_SKILLS;
		global $ESO_FLAMEAOE_SKILLS;
		global $ESO_ELFBANE_SKILLS;
		
		$startTime = microtime(true);
	
		$replacePairs = array(
				'{skillTree}' => $this->GetSkillTreeHtml(),
				'{skillContent}'  => $this->GetSkillContentHtml(),
				'{version}' => $this->version,
				'{niceVersion}' => $this->GetCurrentVersion(),
				'{versionList}' => $this->GetVersionList($this->GetCurrentVersion()),
				'{versionTitle}' => $this->GetVersionTitle(),
				'{rawSkillData}' => "",
				'{coefSkillData}' => "",
				'{skillsJson}' => $this->GetSkillsJson(),
				'{skillSearchIdJson}' => $this->GetSkillSearchIdsJson(),
				'{skillHighlightId}' => $this->highlightSkillId,
				'{skillHighlightType}' => $this->highlightSkillType,
				'{skillHighlightLine}' => $this->highlightSkillLine,
				'{level}' => $this->skillLevel,
				'{fmtLevel}' => $this->FormatLevel($this->skillLevel),
				'{health}' => $this->skillHealth,
				'{magicka}' => $this->skillMagicka,
				'{stamina}' => $this->skillStamina,
				'{spellDamage}' => $this->skillSpellDamage,
				'{weaponDamage}' => $this->skillWeaponDamage,
				'{skillShowAll}' => $this->showAll ? "true" : "false",
				'{updateDate}' => $this->GetUpdateDate(),
				'{useUpdate10Costs}' => $this->useUpdate10Costs ? 1 : 0,
				'{leftBlockDisplay}' => $this->GetLeftBlockDisplay(),
				'{rightBlockMargin}' => $this->GetRightBlockMargin(),
				'{menuBarDisplay}' => $this->GetMenuBarDisplay(),
				'{displayType}' => $this->displayType,
				'{skillBar}' => $this->GetSkillBarHtml(),
				'{usedPoints}' => $this->GetUsedSkillPoints(),
				'{activeDataJson}' => json_encode($this->activeData),
				'{passiveDataJson}' => json_encode($this->passiveData),
				'{skillBarJson}'  => json_encode($this->initialSkillBarData),
				'{destructionDataJson}'  => json_encode($ESO_DESTRUCTION_SKILLS),
				'{poisonSkillsJson}' => json_encode($ESO_POISON_SKILLS),
				'{flameAOESkillsJson}' => json_encode($ESO_FLAMEAOE_SKILLS),
				'{elfBaneSkillsJson}' => json_encode($ESO_ELFBANE_SKILLS),
				'{skillHistoryLink}' => $this->GetSkillHistoryLink(),
		);
		
		if (!CanViewEsoLogVersion($this->version))
		{
			return $this->CreateErrorOutputHtml();
		}
	
		$output = strtr($this->htmlTemplate, $replacePairs);
	
		$this->LogProfile("OutputHtml():Transform", $startTime);
		return $output;
	}
	
	
	public function CreateErrorOutputHtml()
	{
		$startTime = microtime(true);
	
		$replacePairs = array(
				'{skillTree}' => "",
				'{skillContent}'  => "Permission Denied!",
				'{version}' => $this->version,
				'{versionTitle}' => $this->GetVersionTitle(),
				'{rawSkillData}' => "",
				'{coefSkillData}' => "",
				'{skillsJson}' => "{}",
				'{skillSearchIdJson}' => "{}",
				'{skillHighlightId}' => $this->highlightSkillId,
				'{skillHighlightType}' => $this->highlightSkillType,
				'{skillHighlightLine}' => $this->highlightSkillLine,
				'{level}' => $this->skillLevel,
				'{fmtLevel}' => $this->FormatLevel($this->skillLevel),
				'{health}' => $this->skillHealth,
				'{magicka}' => $this->skillMagicka,
				'{stamina}' => $this->skillStamina,
				'{spellDamage}' => $this->skillSpellDamage,
				'{weaponDamage}' => $this->skillWeaponDamage,
				'{skillShowAll}' => $this->showAll ? "true" : "false",
				'{updateDate}' => $this->GetUpdateDate(),
				'{useUpdate10Costs}' => $this->useUpdate10Costs ? 1 : 0,
				'{leftBlockDisplay}' => $this->GetLeftBlockDisplay(),
				'{rightBlockMargin}' => $this->GetRightBlockMargin(),
				'{menuBarDisplay}' => $this->GetMenuBarDisplay(),
				'{displayType}' => $this->displayType,
				'{skillBar}' => "",
				'{usedPoints}' => $this->GetUsedSkillPoints(),
				'{activeDataJson}' => "{}",
				'{passiveDataJson}' => "{}",
				'{skillBarJson}'  => "{}",
				'{skillHistoryLink}' => "",
		);
	
		$output = strtr($this->htmlTemplate, $replacePairs);
	
		$this->LogProfile("OutputHtml():Error:Transform", $startTime);
		return $output;
	}


	public function Render()
	{
		$this->OutputHtmlHeader();
		
		if (!$this->dataLoaded)	$this->LoadData();
		
		$this->SetupHighlightSkill();
		$this->OutputHtml();
	}
	
	
	public function LoadData()
	{
		$this->LoadTemplate();
		$this->LoadSkills();
		$this->dataLoaded = true;
	}
	
	
	public function GetOutputHtml()
	{
		if (!$this->dataLoaded)	$this->LoadData();
		
		$this->SetupHighlightSkill();
		return $this->CreateOutputHtml();
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




