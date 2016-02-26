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
		$query = "SELECT * FROM minedSkills".$this->tableSuffix." WHERE R1 >= 0.98;";
		$result = $this->db->query($query);
		if (!$result) return $this->reportErrror("Failed to load skill coefficient data!");
		
		$this->coefData = array();
		
		while (($row = $result->fetch_assoc()))
		{
			$this->coefData[] = $row;
		}
		
		usort($this->coefData, function($a, $b) {
			return strcmp($a['name'], $b['name']);
		});
		
		return true;
	}
	
	
	public function OutputHeader()
	{
		header("Expires: 0");
		header("Pragma: no-cache");
		header("Cache-Control: no-cache, no-store, must-revalidate");
		header("Pragma: no-cache");
		
		if ($this->outputType == "CSV")
			header("content-type: text/plain");
		else
			header("content-type: text/html");
	}
	
	
	public function OutputHtml()
	{
		$replacePairs = array(
				'{content}' => $this->MakeContentHtml(),
		);
		
		$output = strtr($this->htmlTemplate, $replacePairs);
		print($output);
		
		return true;
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
			
			$bop = "+";
			$cop = "+";
			if ($b < 0) { $b = -$b; $bop = "-"; }
			if ($c < 0) { $c = -$c; $cop = "-"; }
			
			if ($a == null || $b == null || $c == null || $R == null) continue;
			if ($R < 0) continue;
			
			$output .= "\$$i = {$a} Stat $bop {$b} Power $cop $c (R2 = $R)<br />";
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
				
			$bop = "+";
			$cop = "+";
			if ($b < 0) { $b = -$b; $bop = "-"; }
			if ($c < 0) { $c = -$c; $cop = "-"; }
				
			if ($a == null || $b == null || $c == null || $R == null) continue;
			if ($R < 0) continue;
				
			$output .= "\$$i = {$a} Stat $bop {$b} Power $cop $c (R2 = $R)   ";
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
			
			$skillLine = $skill['skillLine'];
			$skillType = $skill['classType'];
			if ($skillType == "") $skillType = $skill['raceType'];
			if ($skillType == "") $skillType = GetEsoSkillTypeText($skill['skillType']);
			
			$mechanic = GetEsoMechanicTypeText($skill['mechanic']);
			
			$output .= "<tr>";
			$output .= "<td>{$skill['name']}</td>";
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
			
			$skillLine = $skill['skillLine'];
			$skillType = $skill['classType'];
			if ($skillType == "") $skillType = $skill['raceType'];
			if ($skillType == "") $skillType = GetEsoSkillTypeText($skill['skillType']);
			
			$mechanic = GetEsoMechanicTypeText($skill['mechanic']);
			
			$output .= "{$skill['name']}, ";
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


$g_EsoViewSkillCoef = new CEsoViewSkillCoef();
$g_EsoViewSkillCoef->ViewData();
