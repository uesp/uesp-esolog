<?php
/*
 * skillReference.php -- by Dave Humphrey (dave@uesp.net), September 2022
 * 
 * TODO:
 *
 */

	// Database users, passwords and other secrets
require("/home/uesp/secrets/esolog.secrets");
require("esoCommon.php");


class CUespEsoSkillReference
{
	
	const ESOVS_ICON_URL = UESP_ESO_ICON_URL;
	const ESOVS_BLANK_ICON = "blank.png";
	
	public $version = "";
	public $showImage = true;
	
	public $db = null;
	public $lastQuery = "";
	public $skills = [];
	
	
	public function __construct()
	{
		$this->ParseInputParams();
		$this->InitDatabase();
	}
	
	
	public function ReportError($errorMsg)
	{
		error_log($errorMsg);
		$this->errorMessages[] = $errorMsg;
		return false;
	}
	
	
	private function ParseInputParams ()
	{
		$this->inputParams = $_REQUEST;
		
		if (array_key_exists('version', $this->inputParams)) 
		{
			$this->version = trim($this->inputParams['version']);
			if ($this->version === strval(GetEsoUpdateVersion())) $this->version = "";
		}
		
		if (array_key_exists('showimage', $this->inputParams)) 
		{
			$this->showImage = intval($this->inputParams['showImage']);
		}
	}
	
	
	private function InitDatabase()
	{
		global $uespEsoLogReadDBHost, $uespEsoLogReadUser, $uespEsoLogReadPW, $uespEsoLogDatabase;
		
		$this->db = new mysqli($uespEsoLogReadDBHost, $uespEsoLogReadUser, $uespEsoLogReadPW, $uespEsoLogDatabase);
		if ($this->db->connect_error) return $this->ReportError("ERROR: Could not connect to mysql database!");
		
		return true;
	}
	
	
	private function LoadSkills()
	{
		$tableSuffix = GetEsoItemTableSuffix($this->version);
		$minedSkillTable = "minedSkills$tableSuffix";
		$skillTreeTable = "skillTree$tableSuffix";
		
		//$this->lastQuery = "SELECT * FROM skillTree$tableSuffix ORDER BY name;";
		$this->lastQuery = "SELECT $minedSkillTable.*, $skillTreeTable.* FROM $skillTreeTable LEFT JOIN $minedSkillTable ON abilityId=$minedSkillTable.id ORDER BY $skillTreeTable.name;";
		
		$result = $this->db->query($this->lastQuery);
		if ($result === false) return $this->ReportError("Error: Failed to load skill data!");
		
		while ($row = $result->fetch_assoc())
		{
			$this->skills[] = $row;
		}
		
		return true;
	}
	
	
	public function GetVersionList($currentVersion) 
	{
		$output = "";
		if ($currentVersion == "") $currentVersion = GetEsoUpdateVersion();
		
		$query = "SHOW TABLES LIKE 'skillTree%';";
		$result = $this->db->query($query);
		if ($result === false) return $this->ReportError("Failed to list all skillTree table versions!");
		
		$tables = array();
		$output .= "<form action='?' method='get' style='display: inline-block;'>";
		if (!$this->showImage) $output .= "<input type='hidden' name='showimage' value='0'>";
		$output .= "<select name='version'>";
		
		$tables = array();
		
		while (($row = $result->fetch_row())) 
		{
			$table = $row[0];
			$version = substr($table, 9);
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
	
	
	private function EscapeHtml($string)
	{
		return htmlspecialchars($string);
	}
	
	
	public function WritePageHeader()
	{
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
<html>
<head>
	<title>UESP:ESO Log Data -- Skill Reference</title>
	<meta http-equiv="Content-Type" content="text/html;charset=utf-8" />
	<meta charset="utf-8" />
	<link rel="stylesheet" href="//esolog-static.uesp.net/viewlog.css" />
	<script type="text/javascript" src="//esolog-static.uesp.net/resources/jquery-1.10.2.js"></script>
	<script type="text/javascript" src="//esolog-static.uesp.net/viewlog.js"></script>
</head>
<body>
<body>
<a href='viewlog.php'>Back to Home</a><br />
<h1>ESO: Skill Reference</h1>
<?php
		return true;
	}
	
	
	public function WritePageFooter()
	{
?>
<hr>
<div class='elvLicense'>Most content here is available under the same Attribute-ShareAlike 2.5 License as the UESP wiki. See <a href='https://en.uesp.net/wiki/UESPWiki:Copyright_and_Ownership'>Copyright and Ownership</a>
for more information. Some data is extracted directly from the ESO game data files and copyright is owned by Zenimax Online Studios.</div>
</body>
</html>
<?php
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
	
	
	public function GetIconURL($icon)
	{
		if ($icon == null || $icon == "") return self::ESOVS_ICON_URL . "/" . self::ESOVS_BLANK_ICON;
		
		$icon = preg_replace('/dds$/', 'png', $icon);
		$iconLink = self::ESOVS_ICON_URL . "/" . $icon;
		return $iconLink;
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
	
	
	private function OutputHtml()
	{
		$versionList = $this->GetVersionList($this->version);
		$count = count($this->skills);
		
		$output = "Showing $count skills for game update: $versionList";
		
		$output .= "<table border='1' cellspacing='0' cellpadding='2' class='uespEsoSkillRefTable'>\n";
		$output .= "<tr>\n";
		$output .= "<th>Icon</th>";
		$output .= "<th>Skill Name</th>";
		$output .= "<th>Skill Line</th>";
		$output .= "<th>Type</th>";
		$output .= "<th>Cost</th>";
		$output .= "<th>Description</th>";
		$output .= "<th>Image</th>";
		$output .= "</tr>\n";
		
		foreach ($this->skills as $skill)
		{
			$name = $this->EscapeHtml($skill['name']);
			$skillLine = $this->EscapeHtml($skill['skillTypeName']);
			$cost = $this->EscapeHtml($skill['cost']);
			
			$rank = $skill['rank'];
			if ($skill['isPassive'] == 0) $rank = $rank % 4;
			
			if ($skill['maxRank'] > 1) $name .= $this->EscapeHtml(' ' . $this->GetRomanNumeral($rank));
			$desc = $this->EscapeHtml(FormatRemoveEsoItemDescriptionText($skill['description']));
			
			$icon = $this->EscapeHtml($skill['icon']);
			$iconUrl = $this->GetIconURL($icon);
			
			$type = "Active";
			
			if ($skill['isPassive'] != 0) 
				$type = "Passive";
			elseif (stripos($cost, 'Ultimate') > 0)
				$type = "Ultimate";
			
			$output .= "<tr>\n";
			$output .= "<td><img src=\"$iconUrl\" title=\"$icon\"></td>\n";
			$output .= "<td><b>$name</b></td>\n";
			$output .= "<td>$skillLine</td>\n";
			$output .= "<td>$type</td>\n";
			$output .= "<td>$cost</td>\n";
			$output .= "<td>$desc</td>\n";
			
			if ($this->showImage)
				$output .= "<td></td>\n";
			else
				$output .= "<td></td>\n";
			
			$output .= "</tr>\n";
		}
		
		$output .= "</table>";
		print($output);
		
		return true;
	}
	
	
	public function Render()
	{
		$this->OutputHtmlHeader();
		$this->WritePageHeader();
		$this->LoadSkills();
		$this->OutputHtml();
		$this->WritePageFooter();
		
		return true;
	}
	
};


$ref = new CUespEsoSkillReference();
$ref->Render();