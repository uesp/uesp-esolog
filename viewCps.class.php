<?php

// Database users, passwords and other secrets
require_once("/home/uesp/secrets/esolog.secrets");
require_once("esoCommon.php");


class CEsoViewCP
{
	const ESOVCP_HTML_TEMPLATE = "templates/esocp_template.txt";
	const ESOVCP_HTML_TEMPLATE_EMBED = "templates/esocp_embed_template.txt";
	const ESOVCP_HTML_SIMPLE_TEMPLATE = "templates/esocp_simple_template.txt";
	const ESOVCP_HTML_SIMPLE_TEMPLATE_EMBED = "templates/esocp_simple_embed_template.txt";
	
	
	public $baseUrl = "";
	public $basePath = "";
	public $baseResourceUrl = "";
	
	public $viewSimpleOutput = true;
	public $htmlTemplate = "";
	public $isEmbedded = false;
	public $rawCpData = "";
	public $decodedCpData = "";
	public $cpDataArray = array();
	public $selectedDiscId = "the_lord";
	
	public $version = "";
	
	public $cpData = array();
	public $cpIndexes = array();
	public $cpAbilityIds = array();
	public $cpSkillDesc = array();
	public $cpTotalPoints = array(0, 0, 0, 0);

	public $shortDiscDisplay = false;
	public $hideTopBar = false;
			

	public function __construct ($isEmbedded = false)
	{
		$this->isEmbedded = $isEmbedded;
	
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
	
	
	private function LoadCpData()
	{
		$result = true;
		
		$result &= $this->LoadCpDisciplines();
		$result &= $this->LoadCpSkills();
		$result &= $this->LoadCpSkillDescriptions();
		
		return $result;
	}
	
	
	private function LoadCpDisciplines()
	{
		$query = "SELECT * FROM cpDisciplines;";
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
	
	
	private function LoadCpSkills()
	{
		$query = "SELECT * FROM cpSkills;";
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
	
	
	private function LoadCpSkillDescriptions()
	{
		$query = "SELECT * FROM cpSkillDescriptions;";
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
			
			if ($this->cpSkillDesc[$abilityId] == null) $this->cpSkillDesc[$abilityId] = array();
			$this->cpSkillDesc[$abilityId][$points] = $this->FormatDescriptionHtml($row['description']);
		}
	
		return true;
	}
	
	
	private function OutputHtmlHeader()
	{
		ob_start("ob_gzhandler");
		
		header("Expires: 0");
		header("Pragma: no-cache");
		header("Cache-Control: no-cache, no-store, must-revalidate");
		header("Access-Control-Allow-Origin: " . $_SERVER['HTTP_ORIGIN'] . "");
		header("content-type: text/html");
	}
	
	
	public function LoadTemplate()
	{
		$templateFile = $this->basePath;
	
		if ($this->isEmbedded)
		{
			if ($this->viewSimpleOutput)
				$templateFile .= self::ESOVCP_HTML_SIMPLE_TEMPLATE_EMBED;
			else
				$templateFile .= self::ESOVCP_HTML_TEMPLATE_EMBED;
		}
		else
		{
			if ($this->viewSimpleOutput)
				$templateFile .= self::ESOVCP_HTML_SIMPLE_TEMPLATE;
			else
				$templateFile .= self::ESOVCP_HTML_TEMPLATE;
		}
					
		$this->htmlTemplate = file_get_contents($templateFile);
	}
	

	private function ParseInputParams ()
	{
		if (array_key_exists('version', $this->inputParams)) $this->version = urldecode($this->inputParams['version']);
		
		if (array_key_exists('cp', $this->inputParams))
		{
			$this->rawCpData = urldecode($this->inputParams['cp']);
			$this->decodedCpData = base64_decode($this->rawCpData);
			$this->cpDataArray = unpack('C*', $this->decodedCpData);
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
		
		foreach ($this->cpData as &$discipline)
		{
			$name = $discipline['name'];
			$index = $discipline['disciplineIndex'];
			$id = str_replace(" ", "_", strtolower($name));
			
			$display = "none";
			if ($id == $this->selectedDiscId) $display = "block";
			
			$output .= "<div id='skills_$id' disciplineid='$id' disciplineindex='$index' class='esovcpDiscSkills' style='display: $display;'>";
			$output .= "<div class='esovcpDiscSkillTitle'>$name</div>";
			$output .= "<div class='esovcpDiscTitlePoints'>0</div>";
			//$output .= "<hr>";
			
			foreach ($discipline['skills'] as $skill)
			{
				$output .= $this->GetCpSkillSectionHtml($skill, "");
			}
		
			$output .= "<button class='esotvcpResetDisc'>Reset Discipline</button>";
			$output .= "</div>";			
		}
		
		return $output;
	}
	
	
	public function GetInitialSkillValue($skill)
	{
		$disciplineIndex = $skill['disciplineIndex'];
		$skillIndex = $skill['skillIndex'];
		$index = ($disciplineIndex - 1) * 4 + $skillIndex;

		if ($this->cpDataArray[$index] == null) return "0";
		
		$value = $this->cpDataArray[$index];
		if ($value < 0) $value = 0;
		if ($value > 100) $value = 100;
		
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
		
		$output = "<div id='skill_$id' skillid='$id' unlocklevel='$unlockLevel' unlocked='$isUnlocked' class='esovcpSkill $extraClass'>";
		
		if ($unlockLevel > 0)
		{
			$output .= "<div class='esovcpSkillLevel'>Unlocked at <br/>$unlockLevel</div>";
		}
		else
		{
			$initialValue = $this->GetInitialSkillValue($skill);
			
			if ($initialValue > 0 && $initialValue <= 100)
			{
				$rawDesc = $skill['descriptions'][$initialValue]['description'];
				if ($rawDesc != null && $rawDesc != "") $desc = $this->FormatDescriptionHtml($rawDesc);
			}
			
			$output .= "<div class='esovcpSkillControls'>";
			$output .= "<button skillid='$id' class='esovcpMinusButton'>-</button>";
			$output .= "<input skillid='$id' class='esovcpPointInput' disciplineindex='$disciplineIndex' skillindex='$skillIndex' type='text' value='$initialValue' size='3' maxlength='3'>";
			$output .= "<button skillid='$id' class='esovcpPlusButton'>+</button>";
			$output .= "</div>";	
		}
		
		$output .= "<div class='esovcpSkillName'>$name</div> ";
		$output .= "<div class='esovcpSkillDesc' id='descskill_$id'>$desc</div>";
		$output .= "</div>";
		
		return $output;
	}
	
	
	public function GetCpDisciplinesHtml()
	{
		$output = "";
		
		$output .= "<div class='esovcpTotalPoints' id='esovcpTotalPoints'>0 CP</div>";
		$output .= "<div class='esovcpDiscAttrPoints esovcpDiscHea' id='esovcpDiscHea' attributeindex='1'>0</div>";
		$output .= "<div class='esovcpDiscAttrGroup' id='disc_hea' attributeindex='1'>";
		$output .= $this->GetCpDisciplineTitleHtml($this->cpData[2], "esovcpDiscHea");
		$output .= $this->GetCpDisciplineTitleHtml($this->cpData[3], "esovcpDiscHea");
		$output .= $this->GetCpDisciplineTitleHtml($this->cpData[4], "esovcpDiscHea");
		$output .= "</div>";
	
		$output .= "<div class='esovcpDiscAttrPoints esovcpDiscMag' id='esovcpDiscMag' attributeindex='2'>0</div>";
		$output .= "<div class='esovcpDiscAttrGroup' id='disc_mag' attributeindex='2'>";
		$output .= $this->GetCpDisciplineTitleHtml($this->cpData[5], "esovcpDiscMag");
		$output .= $this->GetCpDisciplineTitleHtml($this->cpData[6], "esovcpDiscMag");
		$output .= $this->GetCpDisciplineTitleHtml($this->cpData[7], "esovcpDiscMag");
		$output .= "</div>";
		
		$output .= "<div class='esovcpDiscAttrPoints esovcpDiscSta' id='esovcpDiscSta' attributeindex='3'>0</div>";
		$output .= "<div class='esovcpDiscAttrGroup' id='disc_sta' attributeindex='3'>";
		$output .= $this->GetCpDisciplineTitleHtml($this->cpData[8], "esovcpDiscSta");
		$output .= $this->GetCpDisciplineTitleHtml($this->cpData[9], "esovcpDiscSta");
		$output .= $this->GetCpDisciplineTitleHtml($this->cpData[1], "esovcpDiscSta");
		$output .= "</div>";
		
		return $output;
	}
	
	
	public function GetCpDisciplineTitleHtml($discipline, $extraClass = "")
	{
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
	
	
	public function GetCpSkillDescJson()
	{
		return json_encode($this->cpSkillDesc);	
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
	
	
	public function CreateOutputHtml()
	{
		$this->LoadTemplate();
		
		$replacePairs = array(
				'{version}' => $this->version,
				'{versionTitle}' => $this->GetVersionTitle(),
				'{updateDate}' => $this->GetUpdateDate(),
				'{cpSkills}' => $this->GetCpSkillsHtml(),
				'{cpDisciplines}' => $this->GetCpDisciplinesHtml(),
				'{skillDescJson}' => $this->GetCpSkillDescJson(),
				'{cpDataJson}' => json_encode($this->cpDataArray),
				'{topBarDisplay}' => $this->GetTopBarDisplay(),
				'{discWidth}' => $this->GetDiscWidth(),
		);
	
		$output = strtr($this->htmlTemplate, $replacePairs);
		return $output;
	}
	
	
	public function GetOutputHtml()
	{
		$this->LoadCpData();
		
		$output = $this->CreateOutputHtml();
		return $output;
	}	
	
	
	public function Render()
	{
		$this->OutputHtmlHeader();
		
		$output = $this->GetOutputHtml();
		print ($output);
	}
	
};


