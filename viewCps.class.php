<?php

// Database users, passwords and other secrets
require_once("/home/uesp/secrets/esolog.secrets");
require_once(__DIR__."/esoCommon.php");


class CEsoViewCP
{
	public $ESOVCP_HTML_SIMPLE_TEMPLATE = "";
	public $ESOVCP_HTML_SIMPLE_TEMPLATE_EMBED = "";	
	public $ESOVCP_HTML_SIMPLEV2_TEMPLATE = "";
	public $ESOVCP_HTML_SIMPLEV2_TEMPLATE_EMBED = "";
	
	public $ESPVCP_POSITION_ADJUST = array(
			63 => array(0, 50),
		);
	
	public $POSITION_FACTORX_V2 = 0.5;
	public $POSITION_FACTORY_V2 = -0.5;
	public $POSITION_OFFSETX_V2 = 850.0;
	public $POSITION_OFFSETY_V2 = -750.0;
	
	public $baseUrl = "";
	public $basePath = "";
	public $baseResourceUrl = "";
	
	public $showFlatV2 = true;
	public $useVersion2 = false;
	public $autoUseVersion2 = true;
	public $htmlTemplate = "";
	public $isEmbedded = false;
	public $showEdit = true;
	public $showFooter = true;
	public $showTitleonLeft = false;
	public $rawCpData = "";
	public $decodedCpData = "";
	public $cpDataArray = array();
	public $hasPackedCpData = false;
	public $selectedDiscId = "the_lord";
	
	public $version = "";
	
	public $cpData = array();
	public $cpIndexes = array();
	public $cpAbilityIds = array();
	public $cpSkills = array();
	public $cpSkillDesc = array();
	public $cpTotalPoints = array(0, 0, 0, 0);
	public $cpClusters = array();
	public $cpLinks = array();
	
	public $shortDiscDisplay = false;
	public $hideTopBar = false;
	
	public $initialData = null;
	
	
	public function __construct ($isEmbedded = false, $parseParams = true)
	{
		$this->ESOVCP_HTML_SIMPLE_TEMPLATE = __DIR__."/templates/esocp_simple_template.txt";
		$this->ESOVCP_HTML_SIMPLE_TEMPLATE_EMBED = __DIR__."/templates/esocp_simple_embed_template.txt";
		$this->ESOVCP_HTML_SIMPLEV2_TEMPLATE = __DIR__."/templates/esocp_simplev2_template.txt";
		$this->ESOVCP_HTML_SIMPLEV2_TEMPLATE_EMBED = __DIR__."/templates/esocp_simplev2_embed_template.txt";
		
		$this->isEmbedded = $isEmbedded;
	
		if ($parseParams)
		{
			$this->SetInputParams();
			$this->ParseInputParams();
		}
		
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
		
		UpdateEsoPageViews("cpViews");
	
		return true;
	}
	
	
	private function LoadCpData()
	{
		$result = true;
		
		$result &= $this->LoadCpDisciplines();
		$result &= $this->LoadCpSkills();
		$result &= $this->LoadCpSkillDescriptions();
		
		return $result;
	}
	
	
	private function LoadCp2Data()
	{
		$result = true;
		
		$result &= $this->LoadCp2Disciplines();
		$result &= $this->LoadCp2Clusters();
		$result &= $this->LoadCp2Links();
		$result &= $this->LoadCp2Skills();
		$result &= $this->LoadCp2SkillDescriptions();
		
		return $result;
	}
	
	
	private function LoadCpDisciplines()
	{
		$suffix = $this->GetTableSuffix();
		$query = "SELECT * FROM cpDisciplines$suffix;";
		$result = $this->db->query($query);
		if (!$result) return $this->ReportError("Failed to load cpDisciplines records!");
		
		$this->cpData = array();
		$this->cpIndexes = array();
		$this->cpAbilityIds = array();
		$this->cpSkillDesc = array();
		
		while (($row = $result->fetch_assoc()))
		{
			$name = $row['name'];
			$index = $row['disciplineIndex'];
			
			$this->cpIndexes[$name] = $index;
			$this->cpData[$index] = $row;
			$this->cpData[$index]['totalPoints'] = 0;
			$this->cpData[$index]['skills'] = array();
		}
		
		return true;
	}
	
	
	private function LoadCp2Disciplines()
	{
		$suffix = $this->GetTableSuffix();
		$query = "SELECT * FROM cp2Disciplines$suffix;";
		$result = $this->db->query($query);
		if (!$result) return $this->ReportError("Failed to load cp2Disciplines records!");
		
		$this->cpData = array();
		$this->cpIndexes = array();
		$this->cpAbilityIds = array();
		$this->cpSkillDesc = array();
		
		while (($row = $result->fetch_assoc()))
		{
			$name = $row['name'];
			$index = $row['disciplineIndex'];
			
			$this->cpIndexes[$name] = $index;
			$this->cpData[$index] = $row;
			$this->cpData[$index]['totalPoints'] = 0;
			$this->cpData[$index]['skills'] = array();
		}
		
		return true;
	}
	
	
	private function LoadCpSkills()
	{
		$suffix = $this->GetTableSuffix();
		$query = "SELECT * FROM cpSkills$suffix;";
		$result = $this->db->query($query);
		if (!$result) return $this->ReportError("Failed to load cpSkills records!");
		
		$this->cpAbilityIds = array();
	
		while (($row = $result->fetch_assoc()))
		{
			$abilityId = $row['abilityId'];
			$index = $row['disciplineIndex'];
			
			$this->cpAbilityIds[$abilityId] = $index;
			$this->cpData[$index]['skills'][$abilityId] = $row;
			$this->cpData[$index]['skills'][$abilityId]['descriptions'] = array();
		}
	
		return true;
	}
	
	
	private function LoadCp2Skills()
	{
		$suffix = $this->GetTableSuffix();
		$query = "SELECT * FROM cp2Skills$suffix;";
		$result = $this->db->query($query);
		if (!$result) return $this->ReportError("Failed to load cp2Skills records!");
		
		$this->cpAbilityIds = array();
		$this->cpSkills = array();
		
		while (($row = $result->fetch_assoc()))
		{
			$abilityId = $row['abilityId'];
			$skillId = intval($row['skillId']);
			$index = $row['disciplineIndex'];
			$parentSkillId = intval($row['parentSkillId']);
			$clusterName = "";
			if ($parentSkillId > 0 && $this->cpClusters[$parentSkillId] != null) $clusterName = $this->cpClusters[$parentSkillId]['name'];
			$row['clusterName'] = $clusterName;
			
			$posAdjust = $this->ESPVCP_POSITION_ADJUST[$skillId];
			
			if ($posAdjust != null) 
			{
				$row['x'] = floatVal($row['x']) + $posAdjust[0];
				$row['y'] = floatVal($row['y']) + $posAdjust[1];
			}
			
			$this->cpAbilityIds[$abilityId] = $index;
			$this->cpData[$index]['skills'][$abilityId] = $row;
			$this->cpData[$index]['skills'][$abilityId]['descriptions'] = array();
			$this->cpSkills[$abilityId] = $row;
			$this->cpSkills[$skillId] = $row;
		}
		
		foreach ($this->cpData as $discIndex => &$cpData)
		{
			uasort($this->cpData[$discIndex]['skills'], ['CEsoViewCP', 'sortSkillsName']);
		}
		
		return true;
	}
	
	
	public static function sortSkillsName($a, $b)
	{
		return strcasecmp($a['name'], $b['name']);
	}
	
	
	private function LoadCpSkillDescriptions()
	{
		$suffix = $this->GetTableSuffix();
		$query = "SELECT * FROM cpSkillDescriptions$suffix;";
		$result = $this->db->query($query);
		if (!$result) return $this->ReportError("Failed to load cpSkillDescriptions records!");
		
		$this->cpSkillDesc = array();

		while (($row = $result->fetch_assoc()))
		{
			$abilityId = $row['abilityId'];
			$index = $this->cpAbilityIds[$abilityId];
			$points = $row['points'];
				
			$this->cpAbilityIds[$abilityId] = $index;
			$this->cpData[$index]['skills'][$abilityId]['descriptions'][$points] = $row;
			
			if (!array_key_exists($abilityId, $this->cpSkillDesc) || $this->cpSkillDesc[$abilityId] == null) $this->cpSkillDesc[$abilityId] = array();
			$this->cpSkillDesc[$abilityId][$points] = $this->FormatDescriptionHtml($row['description']);
		}
	
		return true;
	}
	
	
	private function LoadCp2SkillDescriptions()
	{
		$suffix = $this->GetTableSuffix();
		$query = "SELECT * FROM cp2SkillDescriptions$suffix;";
		$result = $this->db->query($query);
		if (!$result) return $this->ReportError("Failed to load cp2SkillDescriptions records!");
		
		$this->cpSkillDesc = array();

		while (($row = $result->fetch_assoc()))
		{
			$abilityId = $row['abilityId'];
			$index = $this->cpAbilityIds[$abilityId];
			$points = $row['points'];
				
			$this->cpAbilityIds[$abilityId] = $index;
			$this->cpData[$index]['skills'][$abilityId]['descriptions'][$points] = $row;
			
			if (!array_key_exists($abilityId, $this->cpSkillDesc) || $this->cpSkillDesc[$abilityId] == null) $this->cpSkillDesc[$abilityId] = array();
			$this->cpSkillDesc[$abilityId][$points] = $this->FormatDescriptionHtml($row['description']);
		}
	
		return true;
	}
	
	private function LoadCp2Clusters()
	{
		$suffix = $this->GetTableSuffix();
		$query = "SELECT * FROM cp2ClusterRoots$suffix;";
		$result = $this->db->query($query);
		if (!$result) return $this->ReportError("Failed to load cp2ClusterRoots records!");
		
		$this->cpClusters = array();
		
		while (($row = $result->fetch_assoc()))
		{
			$skillId = intval($row['skillId']);
			$this->cpClusters[$skillId] = $row;
		}
		
		return true;
	}
	
	
	private function LoadCp2Links()
	{
		$suffix = $this->GetTableSuffix();
		$query = "SELECT * FROM cp2SkillLinks$suffix;";
		$result = $this->db->query($query);
		if (!$result) return $this->ReportError("Failed to load cp2SkillLinks records!");
		
		$this->cpLinks = array();
		
		while (($row = $result->fetch_assoc()))
		{
			$this->cpLinks[] = $row;
		}
		
		return true;
	}
	
	
	private function OutputHtmlHeader()
	{
		ob_start("ob_gzhandler");
		
		header("Expires: 0");
		header("Pragma: no-cache");
		header("Cache-Control: no-cache, no-store, must-revalidate");
		header("content-type: text/html");
		
		$origin = $_SERVER['HTTP_ORIGIN'];
		
		if (substr($origin, -8) == "uesp.net")
		{
			header("Access-Control-Allow-Origin: $origin");
		}
	}
	
	
	public function LoadTemplate()
	{
		$templateFile = "";
		
		if ($this->isEmbedded)
		{
			if ($this->useVersion2)
				$templateFile .= $this->ESOVCP_HTML_SIMPLEV2_TEMPLATE_EMBED;
			else
				$templateFile .= $this->ESOVCP_HTML_SIMPLE_TEMPLATE_EMBED;
		}
		else
		{
			if ($this->useVersion2)
				$templateFile .= $this->ESOVCP_HTML_SIMPLEV2_TEMPLATE;
			else
				$templateFile .= $this->ESOVCP_HTML_SIMPLE_TEMPLATE;
		}
		
		$this->htmlTemplate = file_get_contents($templateFile);
	}
	

	private function ParseInputParams ()
	{
		if (array_key_exists('version', $this->inputParams)) 
		{
			$this->version = urldecode($this->inputParams['version']);
			if ($this->autoUseVersion2 && intval($this->version) >= 29) $this->useVersion2 = true;
		}
		elseif ($this->autoUseVersion2 && GetEsoUpdateVersion() >= 29)
		{
			$this->useVersion2 = true;
		}
		
		if (array_key_exists('forcev2', $this->inputParams))
		{
			$forcev2 = intval($this->inputParams['forcev2']);
			if ($forcev2 != 0) $this->useVersion2 = true;
		}
		
		if (array_key_exists('forcev2', $this->inputParams))
		{
			$showFlat = intval($this->inputParams['forceflat']);
			if ($showFlat != 0) $this->showFlatV2 = true;
		}
		
		if ($this->useVersion2) $this->selectedDiscId = "fitness";
		
		if (array_key_exists('cp', $this->inputParams))
		{
			$this->rawCpData = urldecode($this->inputParams['cp']);
			$this->decodedCpData = base64_decode($this->rawCpData);
			$this->cpDataArray = unpack('C*', $this->decodedCpData);
			$this->hasPackedCpData = true;
		}
		
		if (array_key_exists('disc', $this->inputParams)) $this->selectedDiscId = urldecode($this->inputParams['disc']);
	
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
	
	
	public function GetUpdateDate()
	{
		$query = "SELECT * FROM logInfo WHERE id='lastCPUpdate';";
		$result = $this->db->query($query);
		if (!$result) return "";
	
		$row = $result->fetch_assoc();
		$updateDate = $row['value'];
	
		return $updateDate;
	}
	
	
	public function GetVersionTitle()
	{
		if ($this->GetTableSuffix() == "") return "";
		return " v" . $this->version . "";
	}
	
	
	public function FormatDescriptionHtml($description)
	{
		$output = preg_replace("#\|c([0-9a-fA-F]{6})([a-zA-Z\$ \-0-9\.%]+)\|r#s", "<div class='esovcpDescWhite'>$2</div>", $description);
		return $output;
	}
	
	
	public function GetCpSkillsHtml()
	{
		$output = "";
		
		$showEdit = "";
		if (!$this->showEdit) $showEdit = " style='display: none;' ";
		
		foreach ($this->cpData as &$discipline)
		{
			$name = $discipline['name'];
			$index = $discipline['disciplineIndex'];
			$attr = $discipline['attribute'];
			$id = str_replace(" ", "_", strtolower($name));
			
			$display = "none";
			if ($id == $this->selectedDiscId) $display = "block";
			
			$totalPoints = $this->GetInitialDisciplinePoints($name);
			
			$output .= "<div id='skills_$id' disciplineid='$id' disciplineindex='$index' class='esovcpDiscSkills' style='display: $display;'>";
			$output .= "<div class='esovcpDiscSkillTitle  esovpcDiscTitle$attr'>$name</div>";
			$output .= "<div class='esovcpDiscTitlePoints  esovpcDiscTitle$attr'>$totalPoints</div>";
			//$output .= "<button class='esotvcpResetDisc' $showEdit>Reset Discipline</button>";
			
			foreach ($discipline['skills'] as $skill)
			{
				$output .= $this->GetCpSkillSectionHtml($skill, "");
			}
			
			$output .= "<button class='esotvcpResetDisc' $showEdit>Reset Discipline</button>";
			$output .= "</div>";
		}
		
		return $output;
	}
	
	
	public function GetCp2SkillsHtml()
	{
		$output = "";
		$showEdit = "";
		if (!$this->showEdit) $showEdit = " style='display: none;' ";
		
		foreach ($this->cpData as &$discipline)
		{
			$output .= $this->GetCp2SkillBlockHtml($discipline, $discipline['skills'], $showEdit, "", false);
		}
		
		foreach ($this->cpClusters as $cluster) 
		{
			$skillIds = explode(",", $cluster['skills']);
			$skillsData = array();
			
			foreach ($skillIds as $skillId) 
			{
				$skillsData[$skillId] = $this->cpSkills[$skillId];
			}
			
			uasort($skillsData, ['CEsoViewCP', 'sortSkillsName']);
			$output .= $this->GetCp2SkillBlockHtml($cluster, $skillsData, $showEdit, "", true);
		}
		
		return $output;
	}
	
	
	public function GetCp2SkillBlockHtml($discipline, $skillsData, $showEdit, $extraClass, $isCluster)
	{
		$output = "";
		
		$name = $discipline['name'];
		$index = $discipline['disciplineIndex'];
		$id = str_replace(" ", "_", strtolower($name));
		
		$display = "none";
		if ($id == $this->selectedDiscId) $display = "block";
		
		$totalPoints  = $this->GetInitialDisciplinePointsV2($index, true);
		
		if (!$this->showFlatV2) $extraClass .= " esovcp2SkillsStar";
		
		$output .= "<div id='skills_$id' disciplineid='$id' disciplineindex='$index' class='esovcpDiscSkills $extraClass' initialpoints='$totalPoints' style='display: $display;'>";
		$output .= "<div class='esovcpDiscSkillTitle  esovpcDiscTitle'>$name</div>";
		$output .= "<div class='esovcpDiscTitlePoints  esovpcDiscTitle'>$totalPoints</div>";
		$output .= "<button class='esotvcpResetDisc' $showEdit>Reset Discipline</button>";
		
		foreach ($skillsData as $skill)
		{
			if (!$isCluster && $skill['parentSkillId'] > 0) continue;
			
			if ($this->showFlatV2) {
				$output .= $this->GetCpSkillSectionHtml($skill, "");
			}
			else {
				$output .= $this->GetCp2SkillSectionHtml($skill, "");
			}
		}
		
		//$output .= "<button class='esotvcpResetDisc' $showEdit>Reset Discipline</button>";
		$output .= "</div>";
		
		return $output;
	}
	
	
	public function GetInitialTotalPoints()
	{
		return 	$this->GetInitialAttributePoints(1) + 
				$this->GetInitialAttributePoints(2) + 
				$this->GetInitialAttributePoints(3);
	}
	
	
	public function GetInitialTotalPointsV2()
	{
		return 	$this->GetInitialAttributePointsV2(1) + 
				$this->GetInitialAttributePointsV2(2) + 
				$this->GetInitialAttributePointsV2(3);
	}
	
	
	public function GetInitialAttributePoints($attribute)
	{
		if ($attribute == 1) //Stamina
		{
			return 	$this->GetInitialDisciplinePoints("The Lord") +
					$this->GetInitialDisciplinePoints("The Lady") +
					$this->GetInitialDisciplinePoints("The Steed");
		}
		else if ($attribute == 2) //Magicka
		{
			return 	$this->GetInitialDisciplinePoints("The Ritual") +
					$this->GetInitialDisciplinePoints("The Atronach") +
					$this->GetInitialDisciplinePoints("The Apprentice");
		}
		else if ($attribute == 3) //Health
		{
			return 	$this->GetInitialDisciplinePoints("The Tower") +
					$this->GetInitialDisciplinePoints("The Shadow") +
					$this->GetInitialDisciplinePoints("The Lover");
		}
		
		return 0;
	}
	
	
	public function GetInitialAttributePointsV2($disciplineIndex)
	{
		return $this->GetInitialDisciplinePointsV2($disciplineIndex, true);
	}
	
	
	public function GetInitialClusterValueV2($clusterId)
	{
		if ($this->initialData == null) return 0;
		
		$cluster = $this->cpClusters[$clusterId];
		if ($cluster == null) return 0;
		
		$discIndex = $cluster['disciplineIndex'];
		if ($this->cpData[$discIndex] == null) return 0;
		$discName = $this->cpData[$discIndex]['name'];
		if ($discName == null) return 0;
		
		$totalPoints = 0;
		
		foreach ($this->cpData[$discIndex]['skills'] as $abilityId => $skill)
		{
			$skillName = $skill['name'];
			$clusterName = $skill['clusterName'];
			
			if ($clusterName!= "" && $skill['parentSkillId'] == $clusterId && $this->initialData[$clusterName][$skillName] != null) 
			{
				$totalPoints += $this->initialData[$clusterName][$skillName];
			}
			else if ($skill['parentSkillId'] == $clusterId && $this->initialData[$discName][$skillName] != null) 
			{
				$totalPoints += $this->initialData[$discName][$skillName];
			}
		}
		
		return $totalPoints;
	}
	
	
	public function GetInitialDisciplinePointsV2($discIndex, $includeClusters)
	{
		if ($this->initialData == null) return 0;
		if ($this->cpData[$discIndex] == null) return 0;
		
		$discName = $this->cpData[$discIndex]['name'];
		if ($discName == null) return 0;
		
		$totalPoints = 0;
		
		foreach ($this->cpData[$discIndex]['skills'] as $abilityId => $skill)
		{
			if (!$includeClusters && $skill['parentSkillId'] > 0) continue;
			$skillName = $skill['name'];
			$clusterName = $skill['clusterName'];
			
			if ($clusterName != "" && $this->initialData[$clusterName][$skillName] != null) 
			{
				$totalPoints += $this->initialData[$clusterName][$skillName];
			}
			elseif ($this->initialData[$discName][$skillName] != null) 
			{
				$totalPoints += $this->initialData[$discName][$skillName];
			}
		}
		
		//error_log("GetInitialDisciplinePointsV2: $discIndex-$discName-$includeClusters = $totalPoints");
		return $totalPoints;
	}
	
	
	public function GetInitialDisciplinePoints($discipline)
	{
		if ($this->initialData == null) return 0;
		
		if (!array_key_exists($discipline, $this->initialData) || $this->initialData[$discipline] == null) return 0;
		if (!array_key_exists('points', $this->initialData[$discipline]) || $this->initialData[$discipline]['points'] == null) return 0;
		
		return $this->initialData[$discipline]['points'];
	}
	
	
	public function GetInitialEquippedValue($skill)
	{
		$slotIndex = $this->GetInitialSlotIndexValue($skill);
		if ($slotIndex > 0) return true;
		return false;
	}
	
	
	public function GetInitialSlotIndexValue($skill)
	{
		if ($this->initialData == null) return 0;
		
		$skillName = $skill['name'];
		$clusterName = $skill['clusterName'];
		$disciplineIndex = $skill['disciplineIndex'];
		$discName = $this->cpData[$disciplineIndex]['name'];
		$abilityId = $skill['abilityId'];
		if ($clusterName != "") $discName = $clusterName;
		
		if ($discName == null) return 0;
		
		if (!array_key_exists('slots', $this->initialData) || $this->initialData['slots'] == null) return 0;
		
		foreach ($this->initialData['slots'] as $slotIndex => $slotAbilityId)
		{
			if ($abilityId == $slotAbilityId) return $slotIndex;
		}
		
		return 0;;
	}
	
	
	public function GetInitialSkillValue($skill)
	{
		if ($this->hasPackedCpData) return $this->GetInitialSkillValueFromPackedData($skill);
		if ($this->initialData == null) return 0;
		
		$skillName = $skill['name'];
		$clusterName = $skill['clusterName'];
		$disciplineIndex = $skill['disciplineIndex'];
		$discName = $this->cpData[$disciplineIndex]['name'];
		if ($clusterName != "") $discName = $clusterName;
		
		if ($discName == null) return 0;
		
		if (!array_key_exists($discName, $this->initialData) || $this->initialData[$discName] == null) return 0;
		if (!array_key_exists($skillName, $this->initialData[$discName]) || $this->initialData[$discName][$skillName] == null) return 0;
		
		return $this->initialData[$discName][$skillName];
	}
	
	
	public function GetInitialSkillValueFromPackedData($skill)
	{
		$disciplineIndex = $skill['disciplineIndex'];
		$skillIndex = $skill['skillIndex'];
		$index = ($disciplineIndex - 1) * 4 + $skillIndex;
		
		if ($this->cpDataArray[$index] == null) return "0";
		
		$maxPoints = $skill['maxPoints'];
		if ($maxPoints == null) $maxPoints = 100;
		
		$value = $this->cpDataArray[$index];
		if ($value < 0) $value = 0;
		if ($value > $maxPoints) $value = $maxPoints;
	
		return $value;
	}
	
	
	public function GetCpSkillSectionHtml($skill, $extraClass = "")
	{
		$name = $skill['name'];
		$id = $skill['abilityId'];
		$unlockLevel = $skill['unlockLevel'];
		$disciplineIndex = $skill['disciplineIndex'];
		$skillIndex = $skill['skillIndex'];
		$desc = $this->FormatDescriptionHtml($skill['minDescription']);
		$isUnlocked = 0;
		$maxPoints = $skill['maxPoints'];
		$skillType = $skill['skillType'];
		$numJumpPoints = $skill['numJumpPoints'];
		$jumpPointDelta = $skill['jumpPointDelta'];
		if ($maxPoints == null) $maxPoints = 100;
		
		$showEdit = "";
		$inputReadOnly = "";
		
		if (!$this->showEdit) 
		{
			$inputReadOnly = " readonly = 'readonly' ";
			$showEdit = " style='display: none;' ";
		}
		
		$initialValue = $this->GetInitialSkillValue($skill);
		if ($initialValue < 0) $isUnlocked = 1;
		
		if ($this->useVersion2) 
		{
			$unlockLevel = $jumpPointDelta;
			$isUnlocked = 0;
			if ($initialValue > $unlockLevel) $isUnlocked = 1; 
		}
		
		$output = "<div id='skill_$id' skillid='$id' unlocklevel='$unlockLevel' unlocked='$isUnlocked' skilltype='$skillType' class='esovcpSkill $extraClass'>";
		
		if ($unlockLevel > 0 && !$this->useVersion2)
		{
			$output .= "<div class='esovcpSkillLevel'>Unlocked<br/>at $unlockLevel</div>";
		}
		else
		{
			if ($initialValue < 0) $initialValue = 0;
			if ($initialValue > $maxPoints) $initialValue = $maxPoints;
				
			$rawDesc = $skill['descriptions'][$initialValue]['description'];
			if ($rawDesc != null && $rawDesc != "") $desc = $this->FormatDescriptionHtml($rawDesc);
			
			if ($this->useVersion2) 
			{
				$output .= "<div class='esovcpSkillControls esovcp2SkillControls'>";
				$checked = $this->GetInitialEquippedValue($skill) ? "checked" : "";
				
				if ($skillType <= 0)
					$output .= "<div class='esovcpEquipCheckDiv'></div>";
				else
					$output .= "<div class='esovcpEquipCheckDiv'><input skillid='$id' type='checkbox' class='esovcpEquipCheck' disciplineindex='$disciplineIndex' skillindex='$skillIndex' $checked/></div>";
			}
			else 
			{
				$output .= "<div class='esovcpSkillControls'>";
			}
			
			$output .= "<button skillid='$id' class='esovcpMinusButton' $showEdit>-</button>";
			$output .= "<input skillid='$id' class='esovcpPointInput' disciplineindex='$disciplineIndex' skillindex='$skillIndex' type='text' value='$initialValue' size='3' maxlength='3' maxpoints='$maxPoints' $inputReadOnly>";
			$output .= "<button skillid='$id' class='esovcpPlusButton' $showEdit>+</button>";
			$output .= "</div>";
		}
		
		$output .= "<div class='esovcpSkillName'>$name</div> ";
		$output .= "<div class='esovcpSkillDesc' id='descskill_$id'>$desc</div>";
		
		if ($this->useVersion2) 
		{
			$equippable = "";
			if ($skillType > 0) $equippable = ", Equip to Activate";
			
			if ($numJumpPoints > 0)
				$output .= "<div class='esovcpSkillMaxPoints'>$maxPoints pts, $numJumpPoints stages, $jumpPointDelta pts/stage$equippable</div> ";
			else
				$output .= "<div class='esovcpSkillMaxPoints'>$maxPoints pts$equippable</div> ";
		}
		
		$output .= "</div>";
		
		return $output;
	}
	
	
	public function GetCp2SkillSectionHtml($skill, $extraClass = "") 
	{
		$name = $skill['name'];
		$id = $skill['abilityId'];
		$disciplineIndex = $skill['disciplineIndex'];
		$skillIndex = $skill['skillIndex'];
		$skillType = $skill['skillType'];
		$desc = $this->FormatDescriptionHtml($skill['minDescription']);
		$maxPoints = $skill['maxPoints'];
		$numJumpPoints = $skill['numJumpPoints'];
		$parentSkillId = $skill['parentSkillId'];
		$jumpPointDelta = $skill['jumpPointDelta'];
		if ($maxPoints == null) $maxPoints = 100;
		$unlockLevel = $skill['jumpPointDelta'];
		
		$showEdit = "";
		$inputReadOnly = "";
		$showPoints = "";
		
		if (!$this->showEdit) 
		{
			$inputReadOnly = " readonly = 'readonly' ";
			$showEdit = " style='display: none;' ";
		}
		
		if ($parentSkillId > 0) 
		{
			if ($skill['isClusterRoot'] == 0) return "";
			if ($this->showFlatV2) return "";
			
			$clusterRoot = $this->cpClusters[$skill['skillId']];
			
			if ($clusterRoot != null) 
			{
				$name = $clusterRoot['name'];
				$skillType = -10;
				$showEdit = " style='display: none;' ";
				$inputReadOnly = " readonly = 'readonly' ";
				$showPoints = " iscluster='1' ";
			}
		}
		
		$left = intval($skill['x'] + $this->POSITION_OFFSETX_V2) * $this->POSITION_FACTORX_V2;
		$top = intval($skill['y'] + $this->POSITION_OFFSETY_V2) * $this->POSITION_FACTORY_V2;
		
		$imageSrc = 'resources/YellowStar.png';
		if ($skillType >= 1) $imageSrc = 'resources/BlueStar.png';
		if ($skillType < 0) $imageSrc = 'resources/PurpleStar.png';
		
		$isUnlocked = 0;
		$initialValue = $this->GetInitialSkillValue($skill);
		if ($initialValue > $unlockLevel) $isUnlocked = 1;
		
		$output = "<div id='skill_$id' skillid='$id' class='esovcp2Skill $extraClass' unlocklevel='$unlockLevel' unlocked='$isUnlocked' style='left: {$left}px; top: {$top}px;'>";
		$output .= "<div class='esovcp2SkillStar esovcpShowTooltip'><img src='$imageSrc' /></div>";
		//$output .= "<div class='esovcpSkillName'>$name</div><br/>";
		
		$output .= "<div class='esovcpSkillControls esovcpShowTooltip'>";
		$output .= "<button skillid='$id' class='esovcpMinusButton' $showEdit>-</button>";
		$output .= "<input skillid='$id' class='esovcpPointInput esovcp2PointInput' disciplineindex='$disciplineIndex' skillindex='$skillIndex' type='text' value='$initialValue' size='3' maxlength='3' maxpoints='$maxPoints' $inputReadOnly $showPoints>";
		$output .= "<button skillid='$id' class='esovcpPlusButton' $showEdit>+</button>";
		$output .= "</div>";
		
		$output .= "</div>";
		return $output;
	}
	
	
	public function GetCpDisciplinesHtml()
	{
		$output = "";
		
		if ($this->showTitleonLeft) 
		{
			$output .= "<div class='esovcpDiscSkillTitle'>CHAMPION POINTS</div>";
		}
		
		$totalPoints = $this->GetInitialTotalPoints();
		$totalPoints1 = $this->GetInitialAttributePoints(1);
		$totalPoints2 = $this->GetInitialAttributePoints(2);
		$totalPoints3 = $this->GetInitialAttributePoints(3);
		
		$output .= "<div class='esovcpTotalPoints' id='esovcpTotalPoints'>$totalPoints CP</div>";
		$output .= "<div class='esovcpDiscAttrPoints esovcpDiscHea' id='esovcpDiscHea' attributeindex='1'>$totalPoints1</div>";
		$output .= "<div class='esovcpDiscAttrGroup' id='disc_hea' attributeindex='1'>";
		$output .= $this->GetCpDisciplineTitleHtml($this->cpData[2], "esovcpDiscHea");
		$output .= $this->GetCpDisciplineTitleHtml($this->cpData[3], "esovcpDiscHea");
		$output .= $this->GetCpDisciplineTitleHtml($this->cpData[4], "esovcpDiscHea");
		$output .= "</div>";
	
		$output .= "<div class='esovcpDiscAttrPoints esovcpDiscMag' id='esovcpDiscMag' attributeindex='2'>$totalPoints2</div>";
		$output .= "<div class='esovcpDiscAttrGroup' id='disc_mag' attributeindex='2'>";
		$output .= $this->GetCpDisciplineTitleHtml($this->cpData[5], "esovcpDiscMag");
		$output .= $this->GetCpDisciplineTitleHtml($this->cpData[6], "esovcpDiscMag");
		$output .= $this->GetCpDisciplineTitleHtml($this->cpData[7], "esovcpDiscMag");
		$output .= "</div>";
		
		$output .= "<div class='esovcpDiscAttrPoints esovcpDiscSta' id='esovcpDiscSta' attributeindex='3'>$totalPoints3</div>";
		$output .= "<div class='esovcpDiscAttrGroup' id='disc_sta' attributeindex='3'>";
		$output .= $this->GetCpDisciplineTitleHtml($this->cpData[8], "esovcpDiscSta");
		$output .= $this->GetCpDisciplineTitleHtml($this->cpData[9], "esovcpDiscSta");
		$output .= $this->GetCpDisciplineTitleHtml($this->cpData[1], "esovcpDiscSta");
		$output .= "</div>";
		
		return $output;
	}
	
	
	public function GetCp2DisciplinesHtml()
	{
		$output = "";
		
		if ($this->showTitleonLeft) 
		{
			$output .= "<div class='esovcpDiscSkillTitle'>CHAMPION POINTS</div>";
		}
		
		$totalPoints = $this->GetInitialTotalPointsV2();
		
		$output .= "<div class='esovcpTotalPoints' id='esovcpTotalPoints'>$totalPoints CP</div>";
		$output .= "<div class='esovcpDiscAttrGroup' id='disc_fitness' disciplineindex='3'>";
		$output .= $this->GetCp2DisciplineTitleHtml($this->cpData[3], "esovcpDiscHea");
		$output .= $this->GetCp2DisciplineClusterHtml(3, "esovcpDiscHea");
		$output .= "</div>";
		
		$output .= "<div class='esovcpDiscAttrGroup' id='disc_warfare' disciplineindex='2'>";
		$output .= $this->GetCp2DisciplineTitleHtml($this->cpData[2], "esovcpDiscMag");
		$output .= $this->GetCp2DisciplineClusterHtml(2, "esovcpDiscMag");
		$output .= "</div>";
		
		$output .= "<div class='esovcpDiscAttrGroup' id='disc_craft' disciplineindex='1'>";
		$output .= $this->GetCp2DisciplineTitleHtml($this->cpData[1], "esovcpDiscSta");
		$output .= $this->GetCp2DisciplineClusterHtml(1, "esovcpDiscSta");
		$output .= "</div>";
		
		return $output;
	}
	
	
	public function GetCp2DisciplineClusterHtml($disciplineIndex, $extraClass) 
	{
		$output = "";
		
		foreach ($this->cpClusters as $cluster)
		{
			$index = $cluster['disciplineIndex'];
			$skillId = $cluster['skillId'];
			$name = $cluster['name'];
			
			if ($disciplineIndex != $index) continue;
			
			$totalPoints = $this->GetInitialClusterValueV2($skillId);
			$id = str_replace(" ", "_", strtolower($name));
			if ($id == $this->selectedDiscId) $extraClass .= " esovcpDiscHighlight";
			
			$output .= "<div id='$id' clusterindex='$index' clusterid='$skillId' class='esovcp2Discipline esovcp2DiscCluster $extraClass'>";
			$output .= "$name <div class='esovcpDiscPoints'>$totalPoints</div>";
			$output .= "</div>";
		}
			
		return $output;
	}
	
	
	public function GetCpDisciplineTitleHtml($discipline, $extraClass = "")
	{
		$output = "";
		
		if ($discipline == null) return "";
		
		$name = $discipline['name'];
		$desc = $discipline['description'];
		$attr = $discipline['attribute'];
		$index = $discipline['disciplineIndex'];
		$id = str_replace(" ", "_", strtolower($name));
		
		if ($id == $this->selectedDiscId) $extraClass .= " esovcpDiscHighlight";
		
		$output .= "<div id='$id' disciplineindex='$index' class='esovcpDiscipline $extraClass'>";
		$output .= "$name <div class='esovcpDiscPoints'>0</div>";
		
		if (!$this->shortDiscDisplay)
		{
			$output .= "<div class='esovcpDiscDesc'>$desc</div>";
		}
		
		$output .= "</div>";
	
		return $output;
	}
	
	
	public function GetCp2DisciplineTitleHtml($discipline, $extraClass = "")
	{
		$output = "";
		
		if ($discipline == null) return "";
		
		$name = $discipline['name'];
		$index = $discipline['disciplineIndex'];
		$id = str_replace(" ", "_", strtolower($name));
		
		$totalPoints  = $this->GetInitialDisciplinePointsV2($index, true);
		$totalPoints1 = $this->GetInitialDisciplinePointsV2($index, false);
		
		if ($id == $this->selectedDiscId) $extraClass .= " esovcpDiscHighlight";
		
		$output .= "<div id='$id' disciplineindex='$index' class='esovcp2Discipline $extraClass'>";
		$output .= "$name <div class='esovcpDiscPoints' initialpoints='$totalPoints'>$totalPoints</div>";
		$output .= "</div>";
		
		$output .= "<div id='{$id}_base' clusterindex='$index' class='esovcp2Discipline esovcp2DiscCluster $extraClass' style='display: none;'>";
		$output .= "<div class='esovcpDiscPoints' initialpoints='$totalPoints1'>$totalPoints1</div>";
		$output .= "</div>";
		
		return $output;
	}
	
	
	public function GetCpSkillDescJson()
	{
		return json_encode($this->cpSkillDesc);
	}
	
	
	public function GetCpSkillsJson()
	{
		return json_encode($this->cpSkills);
	}
	
	
	public function GetTopBarDisplay()
	{
		if ($this->hideTopBar) return "none";
		return "block";
	}
	
	
	public function GetDiscWidth()
	{
		if ($this->shortDiscDisplay) return "210px";
		return "";
	}
	
	
	public function GetDisplayResetAllButton()
	{
		if ($this->showEdit) return "block";
		return "none";
	}
	
	
	public function GetDisplayFooter()
	{
		if ($this->showFooter) return "block";
		return "none";
	}
	
	
	public function GetCurrentVersion() 
	{
		return GetEsoDisplayVersion($this->version);
	}
	
	
	public function GetVersionList($currentVersion) 
	{
		$output = "";
		
		$query = "SHOW TABLES LIKE 'cpSkills%';";
		$result = $this->db->query($query);
		if ($result === false) return $this->ReportError("Failed to list all cpSkills table versions!");
		
		$tables = array();
		$output .= "<form action='?' method='get'>";
		
		if ($this->rawCpData != "") 
		{
			$output .= "<input type='hidden' name='cp' value='{$this->rawCpData}'>";
			if ($this->selectedDiscId != "") $output .= "<input type='hidden' name='disc' value='{$this->selectedDiscId}'>";
		}
		
		$output .= "<select name='version'>";
		
		$tables = array();
		
		while (($row = $result->fetch_row())) 
		{
			$table = $row[0];
			$version = substr($table, 8);
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
	
	
	public function CreateOutputHtml()
	{
		$this->LoadTemplate();
		
		$replacePairs = array(
				'{version}' => $this->version,
				'{niceVersion}' => $this->GetCurrentVersion(),
				'{versionList}' => $this->GetVersionList($this->GetCurrentVersion()),
				'{versionTitle}' => $this->GetVersionTitle(),
				'{updateDate}' => $this->GetUpdateDate(),
				'{cpSkills}' => $this->GetCpSkillsHtml(),
				'{cpDisciplines}' => $this->GetCpDisciplinesHtml(),
				'{skillDescJson}' => $this->GetCpSkillDescJson(),
				'{skillsJson}' => $this->GetCpSkillsJson(),
				'{cpDataJson}' => json_encode($this->cpDataArray),
				'{topBarDisplay}' => $this->GetTopBarDisplay(),
				'{discWidth}' => $this->GetDiscWidth(),
				'{displayResetAllButton}' => $this->GetDisplayResetAllButton(),
				'{footerDisplay}' => $this->GetDisplayFooter(),
		);
		
		if (!CanViewEsoLogVersion($this->version))
		{
			return $this->CreateErrorOutputHtml();
		}
		
		$output = strtr($this->htmlTemplate, $replacePairs);
		return $output;
	}
	
	
	public function CreateOutput2Html()
	{
		$this->LoadTemplate();
		
		$replacePairs = array(
				'{version}' => $this->version,
				'{niceVersion}' => $this->GetCurrentVersion(),
				'{versionList}' => $this->GetVersionList($this->GetCurrentVersion()),
				'{versionTitle}' => $this->GetVersionTitle(),
				'{updateDate}' => $this->GetUpdateDate(),
				'{cpSkills}' => $this->GetCp2SkillsHtml(),
				'{cpDisciplines}' => $this->GetCp2DisciplinesHtml(),
				'{skillDescJson}' => $this->GetCpSkillDescJson(),
				'{skillsJson}' => $this->GetCpSkillsJson(),
				'{cpDataJson}' => json_encode($this->cpDataArray),
				'{topBarDisplay}' => $this->GetTopBarDisplay(),
				'{discWidth}' => $this->GetDiscWidth(),
				'{displayResetAllButton}' => $this->GetDisplayResetAllButton(),
				'{footerDisplay}' => $this->GetDisplayFooter(),
		);
		
		if (!CanViewEsoLogVersion($this->version))
		{
			return $this->CreateErrorOutputHtml();
		}
		
		$output = strtr($this->htmlTemplate, $replacePairs);
		return $output;
	}
	
	
	public function CreateErrorOutputHtml()
	{
		$this->LoadTemplate();
	
		$replacePairs = array(
				'{version}' => $this->version,
				'{versionTitle}' => $this->GetVersionTitle(),
				'{updateDate}' => $this->GetUpdateDate(),
				'{cpSkills}' => "",
				'{cpDisciplines}' => "Permission Denied!",
				'{skillDescJson}' => "{}",
				'{cpDataJson}' => "{}",
				'{topBarDisplay}' => $this->GetTopBarDisplay(),
				'{discWidth}' => $this->GetDiscWidth(),
				'{displayResetAllButton}' => "block",
				'{footerDisplay}' => "block",
		);
	
		$output = strtr($this->htmlTemplate, $replacePairs);
		return $output;
	}
	
	
	public function GetOutputHtml()
	{
		if ($this->useVersion2) 
		{
			//error_log("ViewCP: Version 2");
			$this->LoadCp2Data();
			$output = $this->CreateOutput2Html();
		}
		else
		{
			//error_log("ViewCP: Version 1");
			$this->LoadCpData();
			$output = $this->CreateOutputHtml();
		}
		
		return $output;
	}	
	
	
	public function Render()
	{
		$this->OutputHtmlHeader();
		
		$output = $this->GetOutputHtml();
		print ($output);
	}
	
};


