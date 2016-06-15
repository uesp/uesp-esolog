<?php

/*
 * viewSkillCoef.php -- by Dave Humphrey (dave@uesp.net), February 2016
 * 
 * Very basic viewer for computed ESO skill coefficients.
 * 
 */


	// Database users, passwords and other secrets
require("/home/uesp/secrets/esolog.secrets");
require("esoCommon.php");


class CEsoViewSkillCoef
{
	const ESOVSC_HTML_TEMPLATE = "templates/esovsc_template.txt";
		
	public $db = null;
	
	public $coefData = array();
	
	public $htmlTemplate = "";
	
	public $outputType = "HTML";
	public $version = "";
	public $tableSuffix = "";
	public $extraQueryString = "";
	public $minR2 = 0;
	public $maxR2 = 1;
	public $minRatio = -100;
	public $maxRatio = 100;
	
	
	public function __construct()
	{
		$this->SetInputParams();
		$this->ParseInputParams();
		$this->InitDatabase();
	
		$this->htmlTemplate = file_get_contents(self::ESOVSC_HTML_TEMPLATE);
	}
	
	
	public function ReportError($errorMsg)
	{
		print($errorMsg);
		error_log($errorMsg);
		return false;
	}
	
	
	public function ParseInputParams ()
	{
		if (array_key_exists('output', $this->inputParams)) $this->rawOutput = strtoupper($this->inputParams['output']);
		if (array_key_exists('format', $this->inputParams)) $this->rawOutput = strtoupper($this->inputParams['format']);
			
		if ($this->rawOutput == "CSV")
			$this->outputType = "CSV";
		else if ($this->rawOutput == "HTML")
			$this->outputType = "HTML";
		
		if (array_key_exists('version', $this->inputParams)) $this->version = $this->inputParams['version'];
		if (array_key_exists('v',       $this->inputParams)) $this->version = $this->inputParams['v'];
		
		if (array_key_exists('minr2', $this->inputParams)) $this->minR2 = floatval($this->inputParams['minr2']);
		if (array_key_exists('maxr2', $this->inputParams)) $this->maxR2 = floatval($this->inputParams['maxr2']);
		
		if ($this->minR2 > 1) $this->minR2 = 1;
		if ($this->minR2 < 0) $this->minR2 = 0;
		if ($this->maxR2 > 1) $this->maxR2 = 1;
		if ($this->maxR2 < 0) $this->maxR2 = 0;
		if ($this->minR2 > $this->maxR2) swap($this->minR2, $this->maxR2);
		
		if (array_key_exists('minratio', $this->inputParams)) $this->minRatio = floatval($this->inputParams['minratio']);
		if (array_key_exists('maxratio', $this->inputParams)) $this->maxRatio = floatval($this->inputParams['maxratio']);
		if ($this->minRatio > $this->maxRatio) swap($this->minRatio, $this->maxRatio);
	
		$this->tableSuffix = GetEsoItemTableSuffix($this->version);
	
		if ($this->version != '')
		{
			$this->extraQueryString = '&version='.$this->version;
		}
	}	
	
	
	public function SetInputParams ()
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
	
	
	public function InitDatabase()
	{
		global $uespEsoLogReadDBHost, $uespEsoLogReadUser, $uespEsoLogReadPW, $uespEsoLogDatabase;
	
		$this->db = new mysqli($uespEsoLogReadDBHost, $uespEsoLogReadUser, $uespEsoLogReadPW, $uespEsoLogDatabase);
		if ($this->db->connect_error) return $this->ReportError("ERROR: Could not connect to mysql database!");
	
		return true;
	}
	
	
	public function LoadSkillCoef()
	{
		$query = "SELECT * FROM minedSkills".$this->tableSuffix." WHERE R1 > 0.1;";
		$result = $this->db->query($query);
		if (!$result) return $this->reportErrror("Failed to load skill coefficient data!");
		
		$this->coefData = array();
		
		while (($row = $result->fetch_assoc()))
		{
			$this->coefData[] = $row;
		}
		
		usort($this->coefData, function($a, $b) {
			$c = strcmp($a['name'], $b['name']);
			if ($c == 0) $c = $a['rank'] - $b['rank']; 
			return $c;
		});
		
		return true;
	}
	
	
	public function OutputHeader()
	{
		ob_start("ob_gzhandler");
		header("Expires: 0");
		header("Pragma: no-cache");
		header("Cache-Control: no-cache, no-store, must-revalidate");
		header("Pragma: no-cache");
		
		if ($this->outputType == "CSV")
			header("content-type: text/plain");
		else
			header("content-type: text/html");
	}
	
	
	public function MakePageHeaderHtml()
	{
		$output = "";

		if ($this->minR2 > 0 || $this->maxR2 < 1)
		{
			$output .= "Showing skill coefficients with an R2 value between ".$this->minR2." - ".$this->maxR2.". ";
		}
		
		if ($this->minR2 > 0 || $this->maxR2 < 1)
		{
			$output .= "Showing skill coefficients with a ratio value between ".$this->minRatio." - ".$this->maxRatio.". ";
		}
		
		return $output;
	}
	
	
	public function OutputHtml()
	{
		$replacePairs = array(
				'{pageHeader}' => $this->MakePageHeaderHtml(),
				'{content}' => $this->MakeContentHtml(),
				'{count}' => count($this->coefData),
		);
		
		$output = strtr($this->htmlTemplate, $replacePairs);
		print($output);
		
		return true;
	}
	
	
	public function ShouldOutputCoef($a, $b, $c, $R2)
	{
		$ratio = $b / $a;
		
		if ($ratio < $this->minRatio) return false;
		if ($ratio > $this->maxRatio) return false;
		
		if ($R2 < $this->minR2) return false;
		if ($R2 > $this->maxR2) return false;
		
		
		return true;
	}
	
	
	public function GetStatNames($powerType)
	{
		static $STATNAMES = array(
				UESP_POWERTYPE_SOULTETHER => array("Stat", "Power"),
				UESP_POWERTYPE_LIGHTARMOR => array("LightArmor", ""),
				UESP_POWERTYPE_MEDIUMARMOR => array("MediumArmor", ""),
				UESP_POWERTYPE_HEAVYARMOR => array("HeavyArmor", ""),
				UESP_POWERTYPE_WEAPONDAGGER => array("Daggers", ""),
				UESP_POWERTYPE_ARMORTYPE => array("ArmorTypes", ""),
				UESP_POWERTYPE_DAMAGE => array("SD", "WD"),
				UESP_POWERTYPE_ASSASSINATION => array("AssassinSkills", ""),
				-2 => array("Health", ""),
				0 => array("Magicka", "SD"),
				6 => array("Stamina", "WD"),
				10 => array("Stat", "Power"),
		);
		
		if ($STATNAMES[$powerType] == null) return array("Stat", "Power");
		return $STATNAMES[$powerType];
	}
	
	
	public function MakeEquationDataHtml($skill)
	{
		$output = "";
		$numVars = $skill['numCoefVars'];
		
		for ($i = 1; $i <= $numVars; ++$i)
		{
			$a = $skill['a'.$i];
			$b = $skill['b'.$i];
			$c = $skill['c'.$i];
			$R = $skill['R'.$i];
			$avg = $skill['avg'.$i];
			$type = $skill['type'.$i];
			if ($type == -1) $type = $skill['mechanic'];
			$typeName = GetEsoCustomMechanicTypeText($type);
			$ratio = sprintf("%0.2f", $b / $a);
			
			$statNames = $this->GetStatNames($type);
			$name1 = $statNames[0];
			$name2 = $statNames[1];
			
			if (!$this->ShouldOutputCoef($a, $b, $c, $R)) continue;
			
			$bop = "+";
			$cop = "+";
			if ($b < 0) { $b = -$b; $bop = "-"; }
			if ($c < 0) { $c = -$c; $cop = "-"; }
			
			if ($a == null || $b == null || $c == null || $R == null) continue;
			if ($R < 0) continue;
			
			if ($b == 0 && $c == 0)
				$output .= "\$$i = {$a} $name1";
			else if ($c == 0)
				$output .= "\$$i = {$a} $name1 $bop $b";
			else if ($b == 0)
				$output .= "\$$i = {$a} $name1 $cop $c";
			else
				$output .= "\$$i = {$a} $name1 $bop {$b} $name2 $cop $c";
			
			$output .= " ($typeName, R2 = $R";
			//if ($avg != -1) $output .= ", average = $avg";
			$output .= ", ratio = $ratio";
			$output .= ")<br />";
		}
		
		return $output;
	}
	
	
	public function MakeEquationDataCsv($skill)
	{
		$output = "";
		$numVars = $skill['numCoefVars'];
	
		for ($i = 1; $i <= $numVars; ++$i)
		{
			$a = $skill['a'.$i];
			$b = $skill['b'.$i];
			$c = $skill['c'.$i];
			$R = $skill['R'.$i];
			$type = $skill['type'.$i];
			if ($type == -1) $type = $skill['mechanic'];
			$typeName = GetEsoCustomMechanicTypeText($type);
			$ratio = sprintf("%0.2f", $b / $a);
			
			if (!$this->ShouldOutputCoef($a, $b, $c, $R)) continue;
				
			$bop = "+";
			$cop = "+";
			if ($b < 0) { $b = -$b; $bop = "-"; }
			if ($c < 0) { $c = -$c; $cop = "-"; }
				
			if ($a == null || $b == null || $c == null || $R == null) continue;
			if ($R < 0) continue;
				
			$output .= "\$$i = {$a} Stat $bop {$b} Power $cop $c ($typeName, R2 = $R, ratio = $ratio)   ";
		}
	
		return $output;
	}
	
	
	public function MakeContentHtml()
	{
		$output = "";
		
		$output .= "<tr>";
		$output .= "<th>Skill Name</th>";
		$output .= "<th>ID</th>";
		$output .= "<th>Mechanic</th>";
		$output .= "<th>Class</th>";
		$output .= "<th>Skill Line</th>";
		$output .= "<th>#</th>";
		$output .= "<th>Description</th>";
		$output .= "<th>Equations</th>";
		$output .= "</tr>\n";
		
		foreach ($this->coefData as $skill)
		{
			$desc = FormatRemoveEsoItemDescriptionText($skill['coefDescription']);
			
			$equationData = $this->MakeEquationDataHtml($skill);
			if ($equationData == "") continue;
			
			$skillLine = $skill['skillLine'];
			$skillType = $skill['classType'];
			$rank = $skill['rank'];
			if ($rank <= 0) $rank = '';
			if ($skillType == "") $skillType = $skill['raceType'];
			if ($skillType == "") $skillType = GetEsoSkillTypeText($skill['skillType']);
			
			$mechanic = GetEsoMechanicTypeText($skill['mechanic']);
			
			$output .= "<tr>";
			$output .= "<td><nobr>{$skill['name']} $rank</nobr></td>";
			$output .= "<td>{$skill['id']}</td>";
			$output .= "<td>$mechanic</td>";
			$output .= "<td>$skillType</td>";
			$output .= "<td>$skillLine</td>";
			$output .= "<td>{$skill['numCoefVars']}</td>";
			$output .= "<td>$desc</td>";
			$output .= "<td class='esovsc_nobreak'>$equationData</td>";
			$output .= "</tr>\n";
		}
				
		return $output;		
	}
	
	
	public function OutputCsv()
	{
		$output = "";
		
		$output .= "Skill Name, ";
		$output .= "ID, ";
		$output .= "Mechanic, ";
		$output .= "Class, ";
		$output .= "Skill Line, ";
		$output .= "#, ";
		$output .= "Description, ";
		$output .= "Equations";
		$output .= "\n";
				
		foreach ($this->coefData as $skill)
		{
			$desc = FormatRemoveEsoItemDescriptionText($skill['coefDescription']);
			
			$equationData = $this->MakeEquationDataCsv($skill);
			if ($equationData == "") continue;
			
			$skillLine = $skill['skillLine'];
			$skillType = $skill['classType'];
			$rank = $skill['rank'];
			if ($rank <= 0) $rank = '';
			if ($skillType == "") $skillType = $skill['raceType'];
			if ($skillType == "") $skillType = GetEsoSkillTypeText($skill['skillType']);
			
			$mechanic = GetEsoMechanicTypeText($skill['mechanic']);
			
			$output .= "{$skill['name']} $rank, ";
			$output .= "{$skill['id']}, ";
			$output .= "$mechanic, ";
			$output .= "$skillType, ";
			$output .= "$skillLine, ";
			$output .= "{$skill['numCoefVars']}, ";
			$output .= "\"$desc\", ";
			$output .= "\"$equationData\"";
			$output .= "\n";
		}
				
		print($output);		
		
		return true;
	}
	
	
	public function ViewData()
	{
		$this->OutputHeader();
		
		$this->LoadSkillCoef();
	
		if ($this->outputType == "HTML") return $this->OutputHtml();
		if ($this->outputType == "CSV")  return $this->OutputCsv();
		
		$this->reportError("Unknown output type!");
		return false;
		
	}
	
};


function swap(&$x,&$y) 
{
	$tmp=$x;
	$x=$y;
	$y=$tmp;
}


$g_EsoViewSkillCoef = new CEsoViewSkillCoef();
$g_EsoViewSkillCoef->ViewData();
