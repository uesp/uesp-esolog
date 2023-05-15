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
	
		/* Use CP skill ID to adjust a single star position */
	public $ESPVCP_POSITION_ADJUST = array(
			63 => array(0, 50),
		);
	
		/* Use parent CP skill ID to adjust an entire constellation position */
	public $ESPVCP_PARENT_POSITION_ADJUST = array(
			44 => array(-400, 200),		// Walking Fortress
			53 => array(0, 200),		// Survivor's Spite
			42 => array(-200, 400),		// Wind Chaser
			108 => array(0, 200),		// Mastered Curation
			10 => array(-200, 0),		// Extended Might
			20 => array(-100, 200),		// Staving Death
		);
	
	public $POSITION_FACTORX_V2 = 0.5;
	public $POSITION_FACTORY_V2 = -0.5;
	public $GLOBAL_SCALE = 1.0;
	public $POSITION_OFFSETX_V2 = 870.0;
	public $POSITION_OFFSETY_V2 = -750.0;
	public $POSITION_LINE_OFFSETX = 35;
	public $POSITION_LINE_OFFSETY = 12;
	public $SVG_WIDTH = 1000;
	public $SVG_HEIGHT = 600;
	
	public $baseUrl = "";
	public $basePath = "";
	public $baseResourceUrl = "";
	
	public $showFlatV2 = false;
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
	public $shortDiscWidth = "210px";
	public $isDataLoaded = false;
	
	public $version = "";
	
	public $cpData = array();
	public $cpIndexes = array();
	public $cpAbilityIds = array();
	public $cpSkills = array();
	public $cpSkillDesc = array();
	public $cpTotalPoints = array(0, 0, 0, 0);
	public $cpClusters = array();
	public $cpSkillsIdMap = array();
	public $cpLinks = array();
	public $cpLinksData = array();
	public $cpReverseLinksData = array();
	public $cpSkillIsPurchaseable = array();
	
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
	
	
	private function EscapeHtml($string)
	{
		return htmlspecialchars ($string);
	}
	
	
	public function LoadCpData()
	{
		if ($this->useVersion2) return $this->LoadCp2Data();
		if ($this->isDataLoaded) return true;
		
		$result = true;
		
		$result &= $this->LoadCpDisciplines();
		$result &= $this->LoadCpSkills();
		$result &= $this->LoadCpSkillDescriptions();
		
		$this->isDataLoaded = true;
		
		return $result;
	}
	
	
	private function LoadCp2Data()
	{
		if ($this->isDataLoaded) return true;
		
		$result = true;
		
		$result &= $this->LoadCp2Disciplines();
		$result &= $this->LoadCp2Clusters();
		$result &= $this->LoadCp2Links();
		$result &= $this->LoadCp2Skills();
		$result &= $this->LoadCp2SkillDescriptions();
		
		$this->CreateCp2LinksData();
		$this->CreateCp2UnlockData();
		
		$this->isDataLoaded = true;
		
		return $result;
	}
	
	
	private function CreateCp2LinksData()
	{
		$this->cpLinksData = array();
		$this->cpReverseLinksData = array();
		
		foreach ($this->cpLinks as $link)
		{
			$parentId = $link['parentSkillId'];
			$skillId  = $link['skillId'];
			
			$abilityId1 = $this->cpSkillsIdMap[$parentId];
			$abilityId2 = $this->cpSkillsIdMap[$skillId];
			if ($abilityId1 == null || $abilityId2 == null) { error_log("CreateCp2LinksData: Null ability ID!"); continue; }
			
			$skill1 = $this->cpSkills[$abilityId1];
			$skill2 = $this->cpSkills[$abilityId2];
			if ($skill1 == null || $skill2 == null) { error_log("CreateCp2LinksData: Null skill data!"); continue; }
			
			if ($this->cpLinksData[$abilityId1] == null) $this->cpLinksData[$abilityId1] = array();
			$this->cpLinksData[$abilityId1][] = $abilityId2;
			
			if ($this->cpReverseLinksData[$abilityId2] == null) $this->cpReverseLinksData[$abilityId2] = array();
			$this->cpReverseLinksData[$abilityId2][] = $abilityId1;
		}
		
	}
	
	
	private function UpdateCP2SkillPurchaseableChildren($abilityId)
	{
		if ($this->skillVisited[$abilityId]) return;
		
		$this->skillVisited[$abilityId] = true;
		$this->skillPurchaseTemp[$abilityId] = 1;
		
		$links = $this->cpLinksData[$abilityId];
		if ($links == null) return;
		
		$skillData = $this->cpSkills[$abilityId];
		$skillValue = $this->GetInitialSkillValue($skillData);
		$jumpPointDelta = $skillData['jumpPointDelta'];
		
		if ($skillValue < $jumpPointDelta) return;
		
		foreach ($links as $linkId)
		{
			$this->UpdateCP2SkillPurchaseableChildren($linkId);
		}
	}
	
	
	private function CreateCp2UnlockData()
	{
		$this->skillVisited = array();
		$this->skillPurchaseTemp = array();
		
		foreach ($this->cpSkills as $abilityId => $skillData) 
		{
			$this->cpSkillIsPurchaseable[$abilityId] = false;
			
			if ($skillData['isRoot'] > 0)
			{
				$this->skillVisited = array();
				$this->UpdateCP2SkillPurchaseableChildren($abilityId);
				continue;
			}
		}
		
		foreach ($this->skillPurchaseTemp as $abilityId => $tempPurchase)
		{
			$this->cpSkillIsPurchaseable[$abilityId] = true;
		}
		
		//$data = print_r($this->cpSkillIsPurchaseable, true);
		//error_log("cpSkillIsPurchaseable $data");
	}
	
	
	private function CreateCp2UnlockData_Old()
	{
		$this->cpSkillIsPurchaseable = array();
		$cpSkillVisited = array();
		$cpSkillsToVisit = array();
		$loopCount = 0;
		
		foreach ($this->cpSkills as $abilityId => $skillData)
		{
			$skillData = $this->cpSkills[$abilityId];
			
			if ($skillData['isRoot'] > 0) 
			{
				$cpSkillVisited[$abilityId] = true;
				$this->cpSkillIsPurchaseable[$abilityId] = true;
				continue;
			}
			
			$cpSkillsToVisit[] = $abilityId;
		}
		
		while (count($cpSkillsToVisit) > 0 && $loopCount < 1000)
		{
			$abilityId = array_shift($cpSkillsToVisit);
			$skillData = $this->cpSkills[$abilityId];
			++$loopCount;
			
			$jumpPointDelta = $skillData['jumpPointDelta'];
			$childSkills = $this->cpLinksData[$abilityId];
			$parentSkills = $this->cpReverseLinksData[$abilityId];
			
			if ($parentSkills == null)
			{
				$this->cpSkillIsPurchaseable[$abilityId] = true;
				$cpSkillVisited[$abilityId] = true;
				continue;
			}
			
			$hasParentVisited = false;
			$hasAllParentVisited = true;
			$hasParentUnlocked = false;
			
			foreach ($parentSkills as $parentSkill)
			{
				if (!$cpSkillVisited[$parentSkill]) 
				{
					$hasAllParentVisited = false;
					continue;
				}
				
				$hasParentVisited = true;
				
				if ($this->cpSkillIsPurchaseable[$parentSkill]) 
				{
					$parentSkillData = $this->cpSkills[$parentSkill];
					$parentValue = $this->GetInitialSkillValue($parentSkillData);
					if ($parentValue >= $parentSkillData['jumpPointDelta'])	$hasParentUnlocked = true;
				}
			}
			
			if (!$hasParentUnlocked && !$hasAllParentVisited)
			{
				$cpSkillsToVisit[] = $abilityId;
				continue;
			}
			
			$this->cpSkillIsPurchaseable[$abilityId] = $hasParentUnlocked;
			$cpSkillVisited[$abilityId] = true;
		}
		
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
		$this->cpSkillsIdMap = array();
		
		$basePosAdjustX = 0;
		$basePosAdjustY = 0;
		
		$posAdjust = $this->ESPVCP_POSITION_ADJUST[$skillId];
		
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
			
			//$this->cpSkills[$skillId] = $row;
			$this->cpSkillsIdMap[$skillId] = $abilityId;
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
		
		if (array_key_exists('forceflat', $this->inputParams))
		{
			$showFlat = intval($this->inputParams['forceflat']);
			if ($showFlat != 0) $this->showFlatV2 = true;
		}
		
		if (array_key_exists('forcestar', $this->inputParams))
		{
			$showStar = intval($this->inputParams['forcestar']);
			if ($showStar != 0) $this->showFlatV2 = false;
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
		$description = $this->EscapeHtml($description);
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
			$name = $this->EscapeHtml($discipline['name']);
			$index = $discipline['disciplineIndex'];
			$attr = $this->EscapeHtml($discipline['attribute']);
			$id = str_replace(" ", "_", strtolower($name));
			$id = str_replace("'", "_", $id);
			
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
			//$output .= "<button class='esotvcpPurchaseAllDisc' $showEdit>Purchase All</button>";
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
				$abilityId = $this->cpSkillsIdMap[$skillId];
				if ($abilityId == null) continue;
				$skillsData[$skillId] = $this->cpSkills[$abilityId];
			}
			
			uasort($skillsData, ['CEsoViewCP', 'sortSkillsName']);
			$output .= $this->GetCp2SkillBlockHtml($cluster, $skillsData, $showEdit, "", true);
		}
		
		return $output;
	}
	
	
	public function GetCp2SvgBlock($discipline, $skillsData, $isCluster)
	{
		$name = $this->EscapeHtml($discipline['name']);
		$index = $discipline['disciplineIndex'];
		$discId = str_replace(" ", "_", strtolower($name));
		$discId = str_replace("'", "_", $discId);
		
		$output = "";
		
		$width  = $this->SVG_WIDTH * $this->GLOBAL_SCALE;
		$height = $this->SVG_HEIGHT * $this->GLOBAL_SCALE;
		
		$output .= "<svg id='esovcp2StarSvg_$discId' class='esovcp2StarSvg' viewBox='0 0 $width $height' xmlns='http://www.w3.org/2000/svg'>";
		
		foreach ($skillsData as $skill)
		{
			if (!$isCluster && $skill['parentSkillId'] > 0) continue;
			
			$id = $skill['abilityId'];
			$links = $this->cpLinksData[$id];
			if ($links == null) continue;
			
			foreach ($links as $linkId)
			{
				$linkSkill = $this->cpSkills[$linkId];
				if ($linkSkill == null) continue;
				
				if ($isCluster && $linkSkill['parentSkillId'] <= 0) continue;
				$offsetX = 0;
				$offsetY = 0;
				
				if ($isCluster)
				{
					$parentSkillId = $skill['parentSkillId'];
					$posAdjust = $this->ESPVCP_PARENT_POSITION_ADJUST[$parentSkillId];
					
					if ($posAdjust != null)
					{
						$offsetX = $posAdjust[0];
						$offsetY = $posAdjust[1];
					}
				}
				
				$x1 = intval($offsetX + $skill['x']     + $this->POSITION_OFFSETX_V2) * $this->POSITION_FACTORX_V2 * $this->GLOBAL_SCALE + $this->POSITION_LINE_OFFSETX;
				$y1 = intval($offsetY + $skill['y']     + $this->POSITION_OFFSETY_V2) * $this->POSITION_FACTORY_V2 * $this->GLOBAL_SCALE + $this->POSITION_LINE_OFFSETY;
				$x2 = intval($offsetX + $linkSkill['x'] + $this->POSITION_OFFSETX_V2) * $this->POSITION_FACTORX_V2 * $this->GLOBAL_SCALE + $this->POSITION_LINE_OFFSETX;
				$y2 = intval($offsetY + $linkSkill['y'] + $this->POSITION_OFFSETY_V2) * $this->POSITION_FACTORY_V2 * $this->GLOBAL_SCALE + $this->POSITION_LINE_OFFSETY;
				//$x1 = intval($skill['x']     + $this->POSITION_OFFSETX_V2) * $this->POSITION_FACTORX_V2 + $this->POSITION_LINE_OFFSETX;
				//$y1 = intval($skill['y']     + $this->POSITION_OFFSETY_V2) * $this->POSITION_FACTORY_V2 + $this->POSITION_LINE_OFFSETY;
				//$x2 = intval($linkSkill['x'] + $this->POSITION_OFFSETX_V2) * $this->POSITION_FACTORX_V2 + $this->POSITION_LINE_OFFSETX;
				//$y2 = intval($linkSkill['y'] + $this->POSITION_OFFSETY_V2) * $this->POSITION_FACTORY_V2 + $this->POSITION_LINE_OFFSETY;
				
				$output .= "<line x1='$x1' y1='$y1' x2='$x2' y2='$y2' stroke='#999999' />";
			}
		}
		
		$output .= "</svg>";
		return $output;
	}
	
	
	public function GetCp2SkillBlockHtml($discipline, $skillsData, $showEdit, $extraClass, $isCluster)
	{
		$output = "";
		
		$name = $this->EscapeHtml($discipline['name']);
		$index = $discipline['disciplineIndex'];
		$id = str_replace(" ", "_", strtolower($name));
		$id = str_replace("'", "_", $id);
		
		$display = "none";
		if ($id == $this->selectedDiscId) $display = "block";
		
		$totalPoints  = $this->GetInitialDisciplinePointsV2($index, true);
		
		if (!$this->showFlatV2) 
		{
			$extraClass .= " esovcp2SkillsStar";
			//$extraClass .= " esovcp2SkillsStar_" . $id;
		}
		
		if ($isCluster) $extraClass .= " esovcp2SkillsCluster";
		
		$output .= "<div id='skills_$id' disciplineid='$id' disciplineindex='$index' class='esovcpDiscSkills $extraClass' initialpoints='$totalPoints' style='display: $display;'>";
		
		if (!$this->showFlatV2)
		{
			//$output .= "<canvas class='esovcp2StarCanvas' id='canvas_$id'></canvas>";
			$output .= $this->GetCp2SvgBlock($discipline, $skillsData, $isCluster);
		}
		
		$output .= "<div class='esovcpDiscSkillTitle  esovpcDiscTitle'>$name</div>";
		$output .= "<div class='esovcpDiscTitlePoints  esovpcDiscTitle'>$totalPoints</div><br/>";
		$output .= "<button class='esotvcpResetDisc esotvcpResetDisc2' $showEdit>Reset Discipline</button>";
		$output .= "<button class='esotvcpPurchaseAllDisc esotvcpPurchaseAllDisc2' $showEdit>Purchase All</button>";
		
		foreach ($skillsData as $skill)
		{
			if (!$isCluster && $skill['parentSkillId'] > 0) 
			{
				if ($skill['isClusterRoot'] == 0) continue;
			}
			
			if ($this->showFlatV2) {
				$output .= $this->GetCpSkillSectionHtml($skill, "");
			}
			else {
				$output .= $this->GetCp2SkillSectionHtml($skill, "", $isCluster);
			}
		}
		
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
			
			//$isPurchaseable = $this->cpSkillIsPurchaseable[$abilityId];
			//error_log("$skillName:$clusterName:$abilityId = $isPurchaseable");
			//if (!$isPurchaseable) continue;
			
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
		$name = $this->EscapeHtml($skill['name']);
		$id = $skill['abilityId'];
		$unlockLevel = $skill['unlockLevel'];
		$disciplineIndex = $skill['disciplineIndex'];
		$skillIndex = $skill['skillIndex'];
		$desc = $this->FormatDescriptionHtml($skill['minDescription']);
		$isUnlocked = 0;
		$isPurchaseable = 1;
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
			if ($skillType >= 1 && $isUnlocked > 0 && !$this->GetInitialEquippedValue($skill)) $isUnlocked = 0; 
			
			$isPurchaseable = $this->cpSkillIsPurchaseable[$id];
			if (!$isPurchaseable) $extraClass .= " esovcpNotPurchaseable"; 
		}
		
		if ($skillType <= 0)
			$extraClass .= " esovcpPassive";
		else
			$extraClass .= " esovcpEquippable";
		
		$isRoot = "0";
		if ($skill['isRoot'] > 0) $isRoot = "1";
		
		$output = "<div id='skill_$id' skillid='$id' unlocklevel='$unlockLevel' unlocked='$isUnlocked' isroot='$isRoot' skilltype='$skillType' class='esovcpSkill $extraClass'>";
		
		if ($unlockLevel > 0 && !$this->useVersion2)
		{
			$output .= "<div class='esovcpSkillLevel'>Unlocked<br/>at $unlockLevel</div>";
		}
		else
		{
			if ($initialValue < 0) $initialValue = 0;
			if ($initialValue > $maxPoints) $initialValue = $maxPoints;
				
			//$rawDesc = $skill['descriptions'][$initialValue]['description'];
			$desc = $this->cpSkillDesc[$id][$initialValue];
			
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
			$output .= "<input id='cpinput_$id' skillid='$id' class='esovcpPointInput' disciplineindex='$disciplineIndex' skillindex='$skillIndex' type='text' value='$initialValue' size='3' maxlength='3' jumpdelta='$jumpPointDelta' maxpoints='$maxPoints' $inputReadOnly>";
			$output .= "<button skillid='$id' class='esovcpPlusButton' $showEdit>+</button>";
			$output .= "</div>";
		}
		
		$output .= "<div class='esovcpSkillName'>$name</div> ";
		$output .= "<div class='esovcpSkillDesc' id='descskill_$id'>$desc</div>";
		
		if ($this->useVersion2) 
		{
			$output .= "<div class='esovcpSkillSuffix'>";
			
			$equippable = "";
			if ($skillType > 0) $equippable = ", Equip to Activate";
			
			if ($numJumpPoints > 0)
				$output .= "<div class='esovcpSkillMaxPoints'>$maxPoints pts, $numJumpPoints stages, $jumpPointDelta pts/stage$equippable</div> ";
			else
				$output .= "<div class='esovcpSkillMaxPoints'>$maxPoints pts$equippable</div> ";
			
			$output .= $this->GetCp2SkillChildLinks($skill);
			$output .= "</div>";
		}
		
		$output .= "</div>";
		
		return $output;
	}
	
	
	public function GetCp2SkillChildLinks($skill)
	{
		$id = $skill['abilityId'];
		$childSkills = $this->cpLinksData[$id];
		if ($childSkills == null) return "";
			
		$output = "<div class='esovcpSkillChildren'>Links To: ";
		$childCount = 0;
		
		foreach ($childSkills as $childSkillId)
		{
			$childSkill = $this->cpSkills[$childSkillId];
			if ($childSkill == null) continue;
			
			if ($childCount > 0) $output .= ", ";
			$name = $this->EscapeHtml($childSkill['name']);
			$output .= "<div class='esovcpSkillChildLink' skillid='$childSkillId'>$name</div>";
			
			++$childCount;
		}
		
		$output .= "</div>";
		return $output;
	}
	
	
	public function GetCp2SkillSectionHtml($skill, $extraClass = "", $isCluster = false) 
	{
		$name = $this->EscapeHtml($skill['name']);
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
		
		$isUnlocked = 0;
		$initialValue = $this->GetInitialSkillValue($skill);
		if ($initialValue < 0) $initialValue = 0;
		if ($initialValue > $maxPoints) $initialValue = $maxPoints;
		if ($initialValue >= $unlockLevel) $isUnlocked = 1;
		
		//$rawDesc = $skill['descriptions'][$initialValue]['description'];
		$desc = $this->cpSkillDesc[$id][$initialValue];
		if ($desc == null || $desc == "") $desc = $this->FormatDescriptionHtml($skill['minDescription']);
		
		if (!$this->showEdit) 
		{
			$inputReadOnly = " readonly = 'readonly' ";
			$showEdit = " style='display: none;' ";
		}
		
		if ($parentSkillId > 0)
		{
			//if ($skill['isClusterRoot'] == 0) return "";
			if ($this->showFlatV2) return "";
			
			$clusterRoot = $this->cpClusters[$skill['skillId']];
			
			if ($clusterRoot != null && !$isCluster)
			{
				$name = $clusterRoot['name'];
				$skillType = -10;
				$showEdit = " style='display: none;' ";
				$inputReadOnly = " readonly='readonly' ";
				$showPoints = " iscluster='1' ";
			}
		}
		
		$isPurchaseable = $this->cpSkillIsPurchaseable[$id];
		if (!$isPurchaseable) $extraClass .= " esovcpNotPurchaseable";
		
		$offsetX = 0;
		$offsetY = 0;
		
		if ($isCluster)
		{
			$parentSkillId = $skill['parentSkillId'];
			$posAdjust = $this->ESPVCP_PARENT_POSITION_ADJUST[$parentSkillId];
			
			if ($posAdjust != null)
			{
				$offsetX = $posAdjust[0];
				$offsetY = $posAdjust[1];
			}
		}
		
		$left = intval($offsetX + $skill['x'] + $this->POSITION_OFFSETX_V2) * $this->POSITION_FACTORX_V2 * $this->GLOBAL_SCALE;
		$top  = intval($offsetY + $skill['y'] + $this->POSITION_OFFSETY_V2) * $this->POSITION_FACTORY_V2 * $this->GLOBAL_SCALE;
		
		$imageSrc = '//esolog.uesp.net/resources/cpstar_yellow.png';
		if ($skillType >= 1) $imageSrc = '//esolog.uesp.net/resources/cpstar_white.png';
		if ($skillType < 0) $imageSrc = '//esolog.uesp.net/resources/cpstar_pink.png';
		
		$extraImgClass = "";
		$isDraggable = "false";
		
		if ($skillType >= 1) 
		{
			$isDraggable = "true";
			$extraImgClass .= " esovcp2SkillEquippable";
			if ($isUnlocked > 0 && !$this->GetInitialEquippedValue($skill)) $isUnlocked = 0; 
		}
		
		$clusterAttr = "";
		$extraInputClass = "";
		
		if (!$isCluster && $skillType < 0)
		{
			$initialValue = $this->GetInitialClusterValueV2($skill['skillId']);
			$id .= "_cluster";
			$extraInputClass = " esovcpPointInputCluster";
			$extraClass .= " esovcpSkillCluster";
			$clusterData = $this->cpClusters[$skill['skillId']];
			
			if ($clusterData != null)
			{
				$clusterName = $this->EscapeHtml($clusterData['name']);
				$clusterId = str_replace(" ", "_", strtolower($clusterName));
				$clusterId = str_replace("'", "_", $clusterId);
				$clusterAttr = "clusterid='$clusterId'";
			}
		}
		
		$isRoot = "0";
		if ($skill['isRoot'] > 0) $isRoot = "1";
		
		$output = "<div id='skill_$id' skillid='$id' class='esovcp2Skill $extraClass' maxpoints='$maxPoints' unlocklevel='$unlockLevel' disciplineindex='$disciplineIndex' isroot='$isRoot' skilltype='$skillType' unlocked='$isUnlocked' $clusterAttr style='left: {$left}px; top: {$top}px;'>";
		$output .= "<div class='esovcp2SkillStar esovcpShowTooltip $extraImgClass' draggable='$isDraggable'><img src='$imageSrc' draggable='$isDraggable' /></div>";
		
		$output .= "<div class='esovcpSkillName' style='display: none;'>$name</div>";
		$output .= "<div class='esovcpSkillDesc' id='descskill_$id' style='display: none;'>$desc</div>";
		
		if ($this->showEdit)
			$output .= "<div class='esovcpSkillControls esovcp2StarControls esovcpShowTooltip'>";
		else
			$output .= "<div class='esovcpSkillControls esovcp2StarControls'>";
		
		$output .= "<button skillid='$id' class='esovcpMinusButton' $showEdit>-</button>";
		
		if ($this->showEdit)
			$output .= "<input id='cpinput_$id' skillid='$id' class='esovcpPointInput esovcp2PointInput $extraInputClass' disciplineindex='$disciplineIndex' skillindex='$skillIndex' type='text' value='$initialValue' size='3' maxlength='3' maxpoints='$maxPoints' jumpdelta='$jumpPointDelta' $inputReadOnly $showPoints>";
		else
			$output .= "<input id='cpinput_$id' skillid='$id' class='esovcpPointInput esovcp2PointInput esovcpShowTooltip $extraInputClass' disciplineindex='$disciplineIndex' skillindex='$skillIndex' type='text' value='$initialValue' size='3' maxlength='3' maxpoints='$maxPoints' jumpdelta='$jumpPointDelta' $inputReadOnly $showPoints>";
		
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
			$name = $this->EscapeHtml($cluster['name']);
			
			if ($disciplineIndex != $index) continue;
			
			$totalPoints = $this->GetInitialClusterValueV2($skillId);
			$id = str_replace(" ", "_", strtolower($name));
			$id = str_replace("'", "_", $id);
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
		
		$name = $this->EscapeHtml($discipline['name']);
		$desc = $this->EscapeHtml($discipline['description']);
		$attr = $this->EscapeHtml($discipline['attribute']);
		$index = $discipline['disciplineIndex'];
		$id = str_replace(" ", "_", strtolower($name));
		$id = str_replace("'", "_", $id);
		
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
		
		$name = $this->EscapeHtml($discipline['name']);
		$index = $discipline['disciplineIndex'];
		$id = str_replace(" ", "_", strtolower($name));
		$id = str_replace("'", "_", $id);
		
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
	
	
	public function GetCp2EquipBarHtml()
	{
		if ($this->showFlatV2) return "";
		
		$output = "<div id='esovcpSkillEquipBar'>";
		
		$output .= $this->GetCp2EquipBarDisciplineHtml(1, 1);
		$output .= $this->GetCp2EquipBarDisciplineHtml(2, 5);
		$output .= $this->GetCp2EquipBarDisciplineHtml(3, 9);
		
		if ($this->showEdit) $output .= "<br/>Drag or double-click a white star from below to equip it.";
		
		$output .= "</div>";
		return $output;
	}
	
	
	public function GetCp2EquipBarDisciplineHtml($discIndex, $startSlotIndex)
	{
		$discName = $this->EscapeHtml($this->cpData[$discIndex]['name']);
		$discId = str_replace(" ", "_", strtolower($discName));
		$discId = str_replace("'", "_", $discId);
		
		$output = "<div class='esovcpSkillEquipBarDisc esovcpSkillEquipBarDisc$discIndex' disciplineindex='$discIndex' discid='$discId'>";
		
		for ($i = 0; $i < 4; ++$i)
		{
			$slotIndex = $i + $startSlotIndex;
			
			$slottedAbilityId = $this->initialData['slots'][$slotIndex];
			$imgSrc = "//esolog.uesp.net/resources/cpstar_white.png";
			$imgDisplay = "inline";
			
			if ($slottedAbilityId == null) 
			{
				$imgDisplay = "none";
				$imgSrc = "";
				$slottedAbilityId = -1;
			}
			
			$output .= "<div class='esovcpSkillEquipBarSlot' disciplineindex='$discIndex' skillid='$slottedAbilityId' slotindex='$slotIndex'><img src='$imgSrc' style='display:$imgDisplay;' /></div>";
		}
		
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
		if ($this->shortDiscDisplay) return $this->shortDiscWidth;
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
		
		$query = "SHOW TABLES LIKE 'cp%Skills%';";
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
			$matchResult = preg_match('/cp.*Skills(.*)/', $table, $matches);
			if (!$matchResult) continue;
			
			//$version = substr($table, 8);
			$version = $matches[1];
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
				'{cpLinksJson}' => "{}",
				'{cpReverseLinksJson}' => "{}",
				'{cpUnlockJson}' => "{}",
				'{cpDataJson}' => json_encode($this->cpDataArray),
				'{topBarDisplay}' => $this->GetTopBarDisplay(),
				'{discWidth}' => $this->GetDiscWidth(),
				'{displayResetAllButton}' => $this->GetDisplayResetAllButton(),
				'{footerDisplay}' => $this->GetDisplayFooter(),
				'{isEdit}' => $this->showEdit ? "true" : "false",
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
				'{cpLinksJson}' => json_encode($this->cpLinksData),
				'{cpReverseLinksJson}' => json_encode($this->cpReverseLinksData),
				'{cpUnlockJson}' => json_encode($this->cpSkillIsPurchaseable),
				'{cpDataJson}' => json_encode($this->cpDataArray),
				'{topBarDisplay}' => $this->GetTopBarDisplay(),
				'{discWidth}' => $this->GetDiscWidth(),
				'{displayResetAllButton}' => $this->GetDisplayResetAllButton(),
				'{footerDisplay}' => $this->GetDisplayFooter(),
				'{starEquipBar}' => $this->GetCp2EquipBarHtml(),
				'{isEdit}' => $this->showEdit ? "true" : "false",
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
				'{cpUnlockJson}' => "{}",
				'{cpLinksJson}' => "{}",
				'{cpReverseLinksJson}' => "{}",
				'{topBarDisplay}' => $this->GetTopBarDisplay(),
				'{discWidth}' => $this->GetDiscWidth(),
				'{displayResetAllButton}' => "block",
				'{footerDisplay}' => "block",
				'{starEquipBar}' => "",
				'{isEdit}' => "false",
		);
	
		$output = strtr($this->htmlTemplate, $replacePairs);
		return $output;
	}
	
	
	public function GetOutputHtml()
	{
		$this->LoadCpData();
		
		if ($this->useVersion2)
		{
			$output = $this->CreateOutput2Html();
		}
		else
		{
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


