<?php 

// Database users, passwords and other secrets
require_once("/home/uesp/secrets/esolog.secrets");
require_once("esoCommon.php");


class CEsoViewCP
{
	const ESOVCP_HTML_TEMPLATE = "templates/esocp_template.txt";
	const ESOVCP_HTML_TEMPLATE_EMBED = "templates/esocp_template.txt";
	const ESOVCP_HTML_SIMPLE_TEMPLATE = "templates/esocp_simple_template.txt";
	const ESOVCP_HTML_SIMPLE_TEMPLATE_EMBED = "templates/esocp_simple_template.txt";
	
	public $viewSimpleOutput = true;
	public $htmlTemplate = "";
	public $isEmbedded = false;
	public $baseUrl = "";
	public $basePath = "";
	public $baseResource = "";
	public $displayDiscIndex = 2;
	
	public $version = "";
	
	public $cpData = array();
	public $cpIndexes = array();
	public $cpAbilityIds = array();
	public $cpSkillDesc = array();
			

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
		header("Pragma: no-cache");
		header("Access-Control-Allow-Origin: " . $_SERVER['HTTP_ORIGIN'] . "");
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
			if ($index == $this->displayDiscIndex) $display = "block";
			
			$output .= "<div id='skills_$id' class='esovcpDiscSkills' style='display: $display;'>";
			$output .= "<div class='esovcpDiscSkillTitle'>$name</div>";
			$output .= "<hr>";
			
			foreach ($discipline['skills'] as $skill)
			{
				$output .= $this->GetCpSkillSectionHtml($skill, "");
			}
			
			$output .= "</div>";			
		}
		
		return $output;
	}
	
	
	public function GetCpSkillSectionHtml($skill, $extraClass = "")
	{
		$name = $skill['name'];
		$id = $skill['abilityId'];
		$unlockLevel = $skill['unlockLevel'];
		$desc = $this->FormatDescriptionHtml($skill['minDescription']);
		
		$output  = "<div id='skill_$id' skillid='$id' class='esovcpSkill $extraClass'>";
		
		if ($unlockLevel > 0)
		{
			$output .= "<div class='esovcpSkillLevel'>Unlocked at <br/>$unlockLevel</div>";
		}
		else
		{
			$output .= "<div class='esovcpSkillControls'>";
			$output .= "<button skillid='$id' class='esovcpMinusButton'>-</button>";
			$output .= "<input skillid='$id' class='esovcpPointInput' type='text' value='0' size='3' maxlength='3'>";
			$output .= "<button skillid='$id' class='esovcpPlusButton'>+</button>";
			$output .= "</div>";	
		}
		
		$output .= "$name <div class='esovcpSkillDesc' id='descskill_$id'>$desc</div>";
		$output .= "</div>";
		
		return $output;
	}
	
	
	public function GetCpDisciplinesHtml()
	{
		$output = "";
		
		$output .= $this->GetCpDisciplineTitleHtml($this->cpData[2], "esovcpDiscSta");
		$output .= $this->GetCpDisciplineTitleHtml($this->cpData[3], "esovcpDiscSta");
		$output .= $this->GetCpDisciplineTitleHtml($this->cpData[4], "esovcpDiscSta");
		$output .= $this->GetCpDisciplineTitleHtml($this->cpData[5], "esovcpDiscMag");
		$output .= $this->GetCpDisciplineTitleHtml($this->cpData[6], "esovcpDiscMag");
		$output .= $this->GetCpDisciplineTitleHtml($this->cpData[7], "esovcpDiscMag");
		$output .= $this->GetCpDisciplineTitleHtml($this->cpData[8], "esovcpDiscHea");
		$output .= $this->GetCpDisciplineTitleHtml($this->cpData[9], "esovcpDiscHea");
		$output .= $this->GetCpDisciplineTitleHtml($this->cpData[1], "esovcpDiscHea");
		
		return $output;
	}
	
	
	public function GetCpDisciplineTitleHtml($discipline, $extraClass = "")
	{
		if ($discipline == null) return "";
		
		$name = $discipline['name'];
		$desc = $discipline['description'];
		$attr = $discipline['attribute'];
		$index = $discipline['disciplineIndex'];
		
		if ($index == $this->displayDiscIndex) $extraClass .= " esovcpDiscHighlight";
		
		$id = str_replace(" ", "_", strtolower($name));
			
		$output .= "<div id='$id' class='esovcpDiscipline $extraClass'>";
		$output .= "$name <div class='esovcpDiscDesc'>$desc</div>";
		$output .= "</div>";	
	
		return $output;
	}
	
	
	public function GetCpSkillDescJson()
	{
		return json_encode($this->cpSkillDesc);	
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
		);
	
		$output = strtr($this->htmlTemplate, $replacePairs);
		return $output;
	}
	
	
	
	public function Render()
	{
		$this->LoadCpData();
		
		$this->OutputHtmlHeader();
		
		$output = $this->CreateOutputHtml();
		print ($output);
	}
	
};


$g_EsoViewCp = new CEsoViewCP(false);
$g_EsoViewCp->Render();