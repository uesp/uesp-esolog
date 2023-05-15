<?php

/*
 * cpTooltip.php -- by Dave Humphrey (dave@uesp.net)
 * 
 * Returns an HTML fragment for a Champion Point popup tooltip.
 * 
 * TODO:
 *
 */

// Database users, passwords and other secrets
require("/home/uesp/secrets/esolog.secrets");
require("esoCommon.php");


class CEsoCPTooltip
{
	public $ESOVCPTOOLTIP_HTML_SIMPLEV2_TEMPLATE = "";
	public $ESOVCPTOOLIP_HTML_SIMPLEV2_TEMPLATE_EMBED = "";
	
	const TOOLTIP_DIVIDER = "<img src='//esolog.uesp.net/resources/skill_divider.png' class='esoSkillPopupTooltipDivider'>";
	const ICON_URL = "//esoicons.uesp.net";
	public $ALTERNATE_TABLE_SUFFIX = "38pts";	// Is automatically set to the highest current PTS version in constructor
	
	public $inputParams = array();
	public $db = null;
	public $htmlTemplate = "";
	
	public $cpData = array();
	
	public $cpId = 0;
	public $cpName = null;
	public $includeLink = true;
	public $outputFullHtml = false;
	public $isEmbedded = true;
	
	
	public function __construct ()
	{
		$this->ESOVCPTOOLTIP_HTML_SIMPLEV2_TEMPLATE = __DIR__."/templates/esocptooltip_simplev2_template.txt";
		$this->ESOVCPTOOLIP_HTML_SIMPLEV2_TEMPLATE_EMBED = __DIR__."/templates/esocptooltip_simplev2_embed_template.txt";
		
		$this->ALTERNATE_TABLE_SUFFIX = (GetEsoUpdateVersion()+1) . "pts";
		
		if (GetEsoItemTableSuffix($this->ALTERNATE_TABLE_SUFFIX) == "")
		{
			$this->ALTERNATE_TABLE_SUFFIX = GetEsoUpdateVersion() . "pts";
		}
		
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
	
	
	private function GetTableSuffix()
	{
		return GetEsoItemTableSuffix($this->version);
	}
	
	
	private function ParseInputParams ()
	{
		if (array_key_exists('version', $this->inputParams)) $this->version = $this->inputParams['version'];
		
		if (array_key_exists('id', $this->inputParams)) $this->cpId = intval($this->inputParams['id']);
		if (array_key_exists('cpid', $this->inputParams)) $this->cpId = intval($this->inputParams['cpid']);
		if (array_key_exists('abilityid', $this->inputParams)) $this->cpId = intval($this->inputParams['abilityid']);
		
		if (array_key_exists('cp', $this->inputParams)) $this->cpName = $this->inputParams['cp'];
		if (array_key_exists('name', $this->inputParams)) $this->cpName = $this->inputParams['name'];
		if (array_key_exists('cpname', $this->inputParams)) $this->cpName = $this->inputParams['cpname'];
		
		if (array_key_exists('includelink', $this->inputParams)) $this->includeLink = intval($this->inputParams['includelink']) != 0;
		
		if (array_key_exists('fullhtml', $this->inputParams)) 
		{
			$this->outputFullHtml = intval($this->inputParams['fullhtml']) != 0;
			$this->isEmbedded = !$this->outputFullHtml;
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
	
	
	public function LoadCPByName()
	{
		if ($this->cpName == null || $this->cpName == "") return false;
		$safeName = $this->db->real_escape_string($this->cpName);
		
		$cpTable = "cp2Skills" . $this->GetTableSuffix();
		$descTable = "cp2SkillDescriptions" . $this->GetTableSuffix();
		
		$query = "SELECT * FROM $cpTable WHERE name='$safeName';";
		
		$result = $this->db->query($query);
		if (!$result) return $this->ReportError("Failed to load CP data by name!");
		
		$this->cpData = $result->fetch_assoc();
		if ($this->cpData == null) return $this->ReportError("Failed to load CP data for $safeName!");
		
		return true;
	}
	
	
	public function LoadCP()
	{
		$cpId = intval($this->cpId);
		if ($cpId <= 0) return $this->LoadCPByName();
		
		$cpTable = "cp2Skills" . $this->GetTableSuffix();
		$descTable = "cp2SkillDescriptions" . $this->GetTableSuffix();
		
		$query = "SELECT * FROM $cpTable WHERE skillId='$cpId';";
		
		$result = $this->db->query($query);
		if (!$result) return $this->ReportError("Failed to load CP data!");
		
		$this->cpData = $result->fetch_assoc();
		if ($this->cpData == null) return $this->ReportError("Failed to load CP data for $cpId!");
		
		/*
		$query = "SELECT * FROM $descTable WHERE skillid='$cpId';";
		
		$result = $this->db->query($query);
		if (!$result) return $this->ReportError("Failed to load CP description data!");
		
		$this->cpData['descriptions'] = $result->fetch_assoc(); */
		
		return true;
	}
	
	
	public function LoadTemplate()
	{
		$templateFile = "";
		
		if ($this->isEmbedded)
		{
			$templateFile .= $this->ESOVCPTOOLIP_HTML_SIMPLEV2_TEMPLATE_EMBED;
		}
		else
		{
			$templateFile .= $this->ESOVCPTOOLTIP_HTML_SIMPLEV2_TEMPLATE;
		}
		
		$this->htmlTemplate = file_get_contents($templateFile);
	}
	
	
	public function GetCurrentVersion()
	{
		return GetEsoDisplayVersion($this->version);
	}
	
	
	public function GetCpNameHtml()
	{
		if ($this->cpData == null) 
		{
			if ($this->cpName != null) return $this->EscapeHtml($this->cpName); 
			return "Unknown";
		}
		
		$name = $this->cpData['name'];
		$name = strtoupper($name);
		$name = $this->EscapeHtml($name);
		return $name;
	}
	
	
	public function GetCpDescHtml()
	{
		if ($this->cpData == null) 
		{
			if ($this->cpName != null) return $this->EscapeHtml("No CP found with a name matching '{$this->cpName}'!");
			return $this->EscapeHtml("No CP found with an ID of '{$this->cpId}'!");
		}
		
		$desc = $this->cpData['maxDescription'];
		//$desc = $this->EscapeHtml($desc);
		$desc = FormatEsoItemDescriptionText($desc);
		return $desc;
	}
	
	
	public function GetCpMaxPointsHtml()
	{
		$maxPoints = intval($this->cpData['maxPoints']);
		return $maxPoints;
	}
	
	
	public function GetCpEquipHtml()
	{
		$skillType = $this->cpData['skillType'];
		if ($skillType == 1) return "Add to Champion Bar to Activate";
		return "";
	}
	
	
	public function GetCpDisplayStage()
	{
		$jumpPoints = $this->cpData['jumpPoints'];
		
		if ($jumpPoints == "") return "display: none;";
		return "display: block;";
	}
	
	
	public function GetCpNextStageHtml()
	{
		$jumpPoints = $this->cpData['jumpPoints'];
		$jumpPoints = preg_replace('/^0,/', '', $jumpPoints);
		$jumpPoints = $this->EscapeHtml($jumpPoints);
		
		return $jumpPoints;
	}
	
	
	public function CreateOutput2Html()
	{
		$this->LoadTemplate();
		
		//Increases your Bash damage by <div class="esovcpDescWhite">60</div> per stage.<p></p>Current bonus: <div class="esovcpDescWhite">0</div> increased damage</div>
		$replacePairs = array(
				'{version}' => $this->GetCurrentVersion(),
				'{cpName}' => $this->GetCpNameHtml(),
				'{cpDesc}' => $this->GetCpDescHtml(),
				'{cpMaxPoints}' => $this->GetCpMaxPointsHtml(),
				'{cpEquip}' => $this->GetCpEquipHtml(),
				'{cpDisplayStage}' => $this->GetCpDisplayStage(),
				'{cpNextStage}' => $this->GetCpNextStageHtml(),
		);
		
		$output = strtr($this->htmlTemplate, $replacePairs);
		return $output;
	}
	
	
	public function CreateErrorOutputHtml()
	{
		$this->LoadTemplate();
		
		//Increases your Bash damage by <div class="esovcpDescWhite">60</div> per stage.<p></p>Current bonus: <div class="esovcpDescWhite">0</div> increased damage</div>
		$replacePairs = array(
				'{version}' => $this->GetCurrentVersion(),
				'{cpName}' => $this->GetCpNameHtml(),
				'{cpDesc}' => $this->GetCpDescHtml(),
				'{cpMaxPoints}' => 0,
				'{cpEquip}' => "",
				'{cpDisplayStage}' => "display:none;",
				'{cpNextStage}' => "",
		);
		
		$output = strtr($this->htmlTemplate, $replacePairs);
		return $output;
	}
	
	
	public function OutputHtml()
	{
		$output = $this->CreateOutput2Html();
		print($output);
		return true;
	}
	
	
	private function OutputFullHtml($cpOutput)
	{
?>
<!DOCTYPE HTML>
<html>
	<head>
		<title>UESP:ESO CP Tooltip</title>
		<meta http-equiv="Content-Type" content="text/html;charset=utf-8" />
		<link rel="stylesheet" href="//esolog-static.uesp.net/resources/esocp_simple_embed.css" />
	</head>
<body style="width: 380px; margin: 0; padding: 0;">
<?php
		print($cpOutput);
?>
</body>
</html>
<?php
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
		
		if (!$this->LoadCP()) 
		{
			$errorHtml = $this->CreateErrorOutputHtml();
			print($errorHtml);
			return false;
		}
		
		$this->OutputHtml();
		
		return true;
	}
	
};


$g_EsoCPTooltip = new CEsoCPTooltip();
$g_EsoCPTooltip->Render();

	

