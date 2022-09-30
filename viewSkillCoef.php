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
	
	public $htmlTemplate = "";
	
	public $outputType = "HTML";
	public $version = "";
	public $tableSuffix = "";
	public $showOldCoefData = false;
	public $extraQueryString = "";
	public $minR2 = 0;
	public $maxR2 = 1;
	public $minRatio = -1000000;
	public $maxRatio = 1000000;
	public $showSkillId = -1;
	
	public $skillVersions = array();
	public $skillResults = array();
	public $skillTooltipVersions = array();
	public $skillTooltipResults = array();
	public $coefData = array();
	public $skillData = array();
	public $skillTooltips = array();
	
	
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
		if (array_key_exists('id', $this->inputParams)) $this->showSkillId = intval($this->inputParams['id']);
		if (array_key_exists('skillid', $this->inputParams)) $this->showSkillId = intval($this->inputParams['skillid']);
		if (array_key_exists('abilityid', $this->inputParams)) $this->showSkillId = intval($this->inputParams['abilityid']);
		
		if (array_key_exists('output', $this->inputParams)) $this->rawOutput = strtoupper($this->inputParams['output']);
		if (array_key_exists('format', $this->inputParams)) $this->rawOutput = strtoupper($this->inputParams['format']);
			
		if ($this->rawOutput == "CSV")
			$this->outputType = "CSV";
		else if ($this->rawOutput == "HTML")
			$this->outputType = "HTML";
		
		if (array_key_exists('version', $this->inputParams)) $this->version = $this->inputParams['version'];
		if (array_key_exists('v',       $this->inputParams)) $this->version = $this->inputParams['v'];
		
		if (array_key_exists('showold', $this->inputParams)) $this->showOldCoefData = intval($this->inputParams['showold']) != 0;
		
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
	
	
	private function EscapeHtml($string)
	{
		return htmlspecialchars($string);
	}
	
	
	public function InitDatabase()
	{
		global $uespEsoLogReadDBHost, $uespEsoLogReadUser, $uespEsoLogReadPW, $uespEsoLogDatabase;
		
		$this->db = new mysqli($uespEsoLogReadDBHost, $uespEsoLogReadUser, $uespEsoLogReadPW, $uespEsoLogDatabase);
		if ($this->db->connect_error) return $this->ReportError("ERROR: Could not connect to mysql database!");
		
		return true;
	}
	
	
	public function LoadSkillTooltipHistory()
	{
		$query = "SHOW TABLES LIKE 'skillTooltips%';";
		$result = $this->db->query($query);
		if ($result === false) return $this->ReportError("Failed to list all skillTooltip table versions!");
		
		$tables = array();
		
		while (($row = $result->fetch_row()))
		{
			$tables[] = $row[0];
		}
		
		$this->skillTooltipResults = array();
		$this->skillTooltipVersions = array();
		
		foreach ($tables as $table)
		{
			$query = "SELECT * FROM $table WHERE abilityId='{$this->showSkillId}';";
			$result = $this->db->query($query);
			if ($result === false || $result->num_rows == 0) continue;
			
			$version = substr($table, 13);
			if ($version == "") $version = GetEsoUpdateVersion();
			$this->skillTooltipVersions[$version] = $version;
			
			while ($row = $result->fetch_assoc())
			{
				$row['version'] = $version;
				$tooltipIndex = intval($row['idx']);
				$this->skillTooltipResults[$version][$tooltipIndex] = $row;
			}
		}
		
		return true;
	}
	
	
	public function LoadSkillHistory()
	{
		$query = "SHOW TABLES LIKE 'minedSkills%';";
		$result = $this->db->query($query);
		if ($result === false) return $this->ReportError("Failed to list all minedSkills table versions!");
		
		$tables = array();
		
		while (($row = $result->fetch_row()))
		{
			$tables[] = $row[0];
		}
		
		$this->skillResults = array();
		$this->skillVersions = array();
		
		foreach ($tables as $table)
		{
			$query = "SELECT * FROM $table WHERE id='{$this->showSkillId}';";
			$result = $this->db->query($query);
			if ($result === false || $result->num_rows == 0) continue;
			
			$row = $result->fetch_assoc();
			
			$version = substr($table, 11);
			if ($version == "") $version = GetEsoUpdateVersion();
			$this->skillVersions[$version] = $version;
			
			$row['version'] = $version;
			
			$this->skillResults[$version] = $row;
		}
		
		natsort($this->skillVersions);
		
		return true;
	}
	
	
	public function LoadSkillTooltips()
	{
		$tooltipTable = "skillTooltips{$this->tableSuffix}";
		$skillTable = "minedSkills{$this->tableSuffix}";
		$treeTable = "skillTree{$this->tableSuffix}";
		
		//$query = "select $skillTable.*, $tooltipTable.* FROM $tooltipTable LEFT JOIN $skillTable on abilityId=id;";
		$query = "select * FROM $tooltipTable;";
		$result = $this->db->query($query);
		if (!$result) return $this->reportErrror("Failed to load skill tooltip data!");
		
		$this->skillTooltips = array();
		
		while (($row = $result->fetch_assoc()))
		{
			$abilityId = intval($row['abilityId']);
			$tooltipIndex = intval($row['idx']);
			
			$this->skillTooltips[$abilityId][$tooltipIndex] = $row;
		}
		
		$query = "select * FROM $skillTable WHERE id IN (SELECT DISTINCT abilityId FROM $tooltipTable);";
		$result = $this->db->query($query);
		if (!$result) return $this->reportErrror("Failed to load skill tooltip data!");
		
		$this->skillData = array();
		
		while (($row = $result->fetch_assoc()))
		{
			$abilityId = intval($row['id']);
			
			$row['tooltips'] = $this->skillTooltips[$abilityId];
			
			$this->skillData[$abilityId] = $row;
		}
		
		usort($this->skillData, function($a, $b) {
			$c = strcmp($a['name'], $b['name']);
			if ($c == 0) $c = $a['rank'] - $b['rank'];
			return $c;
		});
		
		return true;
	}
	
	
	public function LoadSkillCoef()
	{
		$query = "SELECT * FROM minedSkills".$this->tableSuffix." WHERE R1 > 0.9;";
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
	
	
	public function MakeHistoryPageHeaderHtml()
	{
		$count = count($this->skillResults);
		$count1 = count($this->skillTooltipResults);
		
		if ($count == 0) return "<div>Error: No skill history found for skill ID {$this->showSkillId}!</div>";
		
		$output = "<div>This is a history of coefficients for skill ID {$this->showSkillId}. Found $count instances with computed coefficients and $count1 with parsed coefficients.</div>";
		
		return $output;
	}
	
	
	public function MakePageHeaderHtml()
	{
		$count = count($this->coefData);
		if (!$this->showOldCoefData && count($this->skillTooltips) > 0) $count = count($this->skillData);
		
		$output = "<div>This is a list of skill coefficients currently available. Found $count skills with valid coefficients.</div>";
		$output .= "<div>";
		
		if ($this->minR2 > 0 || $this->maxR2 < 1)
		{
			$output .= "Showing skill coefficients with an R2 value between ".$this->minR2." - ".$this->maxR2.". ";
		}
		
		if ($this->minR2 > 0 || $this->maxR2 < 1)
		{
			$output .= "Showing skill coefficients with a ratio value between ".$this->minRatio." - ".$this->maxRatio.". ";
		}
		
		$output .= "Show data for update " . $this->GetVersionList($this->GetCurrentVersion()) . "";
		$output .= "</div>";
		
		return $output;
	}
	
	
	public function OutputHtml()
	{
		$replacePairs = array(
				'{pageHeader}' => $this->MakePageHeaderHtml(),
				'{content}' => $this->MakeContentHtml(),
				'{count}' => count($this->coefData),
				'{trail}' => "",
		);
		
		$output = strtr($this->htmlTemplate, $replacePairs);
		print($output);
		
		return true;
	}
	
	
	public function OutputSkillHistory() 
	{
		$replacePairs = array(
				'{pageHeader}' => $this->MakeHistoryPageHeaderHtml(),
				'{content}' => $this->MakeHistoryContentHtml(),
				'{count}' => count($this->skillResults),
				'{trail}' => " : <a href='?'>All Skills</a>",
		);
		
		$output = strtr($this->htmlTemplate, $replacePairs);
		print($output);
		
		return true;
	}
	
	
	public function ShouldOutputCoef($a, $b, $c, $R2)
	{
		if ($a != 0)
		{
			$ratio = $b / $a;
			
			if ($ratio < $this->minRatio) return false;
			if ($ratio > $this->maxRatio) return false;
		}
		
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
				UESP_POWERTYPE_ASSASSINATION => array("Assassin", ""),
				
				UESP_POWERTYPE_FIGHTERSGUILD => array("FightersGuild", ""),
				UESP_POWERTYPE_DRACONICPOWER => array("DraconicPower", ""),
				UESP_POWERTYPE_SHADOW => array("Shadow", ""),
				UESP_POWERTYPE_SIPHONING => array("Siphoning", ""),
				UESP_POWERTYPE_SORCERER => array("Sorcerer", ""),
				UESP_POWERTYPE_MAGESGUILD => array("MagesGuild", ""),
				UESP_POWERTYPE_SUPPORT => array("Support", ""),
				UESP_POWERTYPE_ANIMALCOMPANION => array("AnimCompanion", ""),
				UESP_POWERTYPE_GREENBALANCE => array("GreenBalance", ""),
				UESP_POWERTYPE_WINTERSEMBRACE => array("WintersEmbrace", ""),
				UESP_POWERTYPE_MAGICHEALTHCAP => array("Magicka", "HealthCap"),
				UESP_POWERTYPE_BONETYRANT => array("BoneTyrant", ""),
				UESP_POWERTYPE_GRAVELORD => array("GraveLord", ""),
				UESP_POWERTYPE_SPELLDAMAGECAPPED => array("SD", ""),
				UESP_POWERTYPE_MAGICKAWITHWD => array("Magicka", "WD"),
				UESP_POWERTYPE_MAGICKACAPPED => array("Magicka", "SD"),
				UESP_POWERTYPE_WEAPONPOWER => array("WeaponPower", ""),
				UESP_POWERTYPE_CONSTANTVALUE => array("", ""),
				UESP_POWERTYPE_HEALTHORSPELLDAMAGE => array("SD", "Health"),
				UESP_POWERTYPE_RESISTANCE => array("MaxResist", ""),
				UESP_POWERTYPE_MAGICLIGHTARMOR => array("Magicka", "LightArmor"),
				UESP_POWERTYPE_HEALTHORDAMAGE => array("Health", "MaxPower"),
				
				-2 => array("Health", ""),
				0 => array("Magicka", "SD"),
				6 => array("Stamina", "WD"),
				10 => array("MaxStat", "MaxPower"),
				
					/* New in Update 34 */
				1 => array("Magicka", "SD"),
				4 => array("Stamina", "WD"),
				8 => array("MaxStat", "MaxPower"),
				32 => array("Health", ""),
		);
		
		if ($STATNAMES[$powerType] == null) return array("Stat", "Power");
		return $STATNAMES[$powerType];
	}
	
	
	public function MakeTooltipEquationDataHtml($skill)
	{
		$output = "";
		
		foreach ($skill['tooltips'] as $tooltip)
		{
			$idx = $tooltip['idx'];
			
			$a = floatval($tooltip['a']);
			$b = floatval($tooltip['b']);
			$c = floatval($tooltip['c']);
			$R = floatval($tooltip['r']);
			
			if ($R < 0) continue;
			if (!$this->ShouldOutputCoef($a, $b, $c, $R)) continue;
			
			$coefType = intval($tooltip['coefType']);
			$rawType = intval($tooltip['rawType']);
			$dmgType = intval($tooltip['dmgType']);
			$duration = intval($tooltip['duration']);
			$tickTime = intval($tooltip['tickTime']);
			$cooldown = intval($tooltip['cooldown']);
			$isDmg = intval($tooltip['isDmg']);
			$isHeal = intval($tooltip['isHeal']);
			$isDmgShield = intval($tooltip['isDmgShield']);
			$isAOE = intval($tooltip['isAOE']);
			$isDOT = intval($tooltip['isDOT']);
			$value = $this->EscapeHTml($tooltip['value']);
			$isMelee = intval($tooltip['isMelee']);
			$hasRankMod = intval($tooltip['hasRankMod']);
			$usesManualCoef = intval($tooltip['usesManualCoef']);
			$typeName = GetEsoCustomMechanicTypeText($coefType, $this->version);
			
			$output .= "&lt;&lt;$idx&gt;&gt; = ";
			
			if ($coefType == UESP_POWERTYPE_CONSTANTVALUE)
			{
				$output .= "$value (Constant)<br/>";
				continue;
			}
			
			if ($a == 0)
				$ratio = "NAN";
			else
				$ratio = sprintf("%0.2f", $b / $a);
			
			$flags = [];
			$flags[] = $typeName;
			if ($ratio != "NAN" && $ratio != 0) $flags[] = "ratio = $ratio";
			if ($isDmg) $flags[] = "Dmg";
			if ($isDmg && $dmgType) $flags[] = GetEsoDamageTypeText($dmgType);
			if ($isDmgShield) $flags[] = "DmgShield";
			if ($isHeal) $flags[] = "Heal";
			if ($isAOE) $flags[] = "AOE"; else $flags[] = "SingleTarget";
			if ($isDOT) $flags[] = "DOT"; else $flags[] = "Direct";
			if ($isMelee) $flags[] = "Melee";
			if ($isRankMod) $flags[] = "RankMod";
			if ($duration) $flags[] = ($duration/1000)."s duration";
			if ($tickTime) $flags[] = ($tickTime/1000)."s tick";
			if ($cooldown) $flags[] = ($cooldown/1000)."s cooldown";
			$flags[] = "R2 = $R";
			$flags = implode(", ", $flags);
			
			$statNames = $this->GetStatNames($coefType);
			$name1 = $statNames[0];
			$name2 = $statNames[1];
			$name3 = $statNames[3];
			if ($name3 == null) $name3 = '';
			
			$bop = "+";
			$cop = "+";
			if ($b < 0) { $b = -$b; $bop = "-"; }
			if ($c < 0) { $c = -$c; $cop = "-"; }
			
			if ($a != 0) $output .= "{$a} $name1 ";
			if ($b != 0) $output .= "$bop $b $name2 ";
			if ($c != 0) $output .= "$cop $c $name3";
			
			$output .= " ($flags)";
			$output .= "<br />";
		}
		
		return $output;
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
			$typeName = GetEsoCustomMechanicTypeText($type, $this->version);
			
			if ($a == 0)
				$ratio = "NAN";
			else
				$ratio = sprintf("%0.2f", $b / $a);
			
			$statNames = $this->GetStatNames($type);
			$name1 = $statNames[0];
			$name2 = $statNames[1];
			$name3 = $statNames[3];
			if ($name3 == null) $name3 = '';
			
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
				$output .= "\$$i = {$a} $name1 $cop $c $name3";
			else
				$output .= "\$$i = {$a} $name1 $bop {$b} $name2 $cop $c $name3";
			
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
			$typeName = GetEsoCustomMechanicTypeText($type, $this->version);
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
		if ($this->minR2 > 0) $output .= "<input type='hidden' name='minr2' value='{$this->minR2}'>";
		if ($this->maxR2 < 1) $output .= "<input type='hidden' name='maxr2' value='{$this->maxR2}'>";
		if ($this->minRatio != -1000000) $output .= "<input type='hidden' name='minratio' value='{$this->minRatio}'>";
		if ($this->maxRatio != 1000000) $output .= "<input type='hidden' name='maxratio' value='{$this->maxRatio}'>";
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
	
	
	public function MakeHistoryContentHtml()
	{
		$output = "";
		
		$output .= "<tr>";
		$output .= "<th>Update</th>";
		$output .= "<th>Skill Name</th>";
		$output .= "<th>Mechanic</th>";
		$output .= "<th>Class</th>";
		$output .= "<th>Skill Line</th>";
		$output .= "<th>#</th>";
		$output .= "<th>Description</th>";
		$output .= "<th>Equations</th>";
		$output .= "</tr>\n";
		
		foreach ($this->skillVersions as $version)
		{
			$tooltips = $this->skillTooltipResults[$version];
			$skill = $this->skillResults[$version];
			
			$desc = $this->EscapeHtml(FormatRemoveEsoItemDescriptionText($skill['coefDescription']));
			
			$equationData = $this->MakeEquationDataHtml($skill);
			if ($equationData == "") continue;
			
			$skillLine = $this->EscapeHtml($skill['skillLine']);
			$skillType = $skill['classType'];
			$rank = $skill['rank'];
			if ($rank <= 0) $rank = '';
			if ($skillType == "") $skillType = $skill['raceType'];
			if ($skillType == "") $skillType = GetEsoSkillTypeText($skill['skillType']);
			$skillType = $this->EscapeHtml($skillType);
			
			$name = $this->EscapeHtml($skill['name']);
			$mechanic = $this->EscapeHtml(GetEsoMechanicTypeText($skill['mechanic'], $this->version));
			
			$rowspan = "";
			
			if (!$this->showOldCoefData && $tooltips != null)
			{
				$skill['tooltips'] = $tooltips;
				
				$desc2 = $this->EscapeHtml(FormatRemoveEsoItemDescriptionText($skill['rawDescription']));
				$equationData2 = $this->MakeTooltipEquationDataHtml($skill);
				$rowspan = "rowspan='2'";
			}
			
			$output .= "<tr>";
			$output .= "<td $rowspan><b>$version</b></td>";
			$output .= "<td $rowspan><nobr>$name $rank</nobr></td>";
			$output .= "<td $rowspan>$mechanic</td>";
			$output .= "<td $rowspan>$skillType</td>";
			$output .= "<td $rowspan>$skillLine</td>";
			$output .= "<td>{$skill['numCoefVars']}</td>";
			$output .= "<td>$desc</td>";
			$output .= "<td class='esovsc_nobreak'>$equationData</td>";
			$output .= "</tr>\n";
			
			if ($rowspan)
			{
				$count = count($tooltips);
				$output .= "<tr>";
				$output .= "<td>$count</td>";
				$output .= "<td>$desc2</td>";
				$output .= "<td class='esovsc_nobreak'>$equationData2</td>";
				$output .= "</tr>\n";
			}
		}
		
		return $output;
	}
	
	
	public function MakeTooltipContentHtml()
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
		
		foreach ($this->skillData as $skill)
		{
			$desc = $this->EscapeHtml(FormatRemoveEsoItemDescriptionText($skill['rawDescription']));
			
			$equationData = $this->MakeTooltipEquationDataHtml($skill);
			if ($equationData == "") continue;
			
			$numTooltips = count($skill['tooltips']);
			$skillLine = $this->EscapeHtml($skill['skillLine']);
			$skillType = $skill['classType'];
			$rank = $skill['rank'];
			if ($rank <= 0) $rank = '';
			if ($skillType == "") $skillType = $skill['raceType'];
			if ($skillType == "") $skillType = GetEsoSkillTypeText($skill['skillType']);
			$skillType = $this->EscapeHtml($skillType);
			
			$name = $this->EscapeHtml($skill['name']);
			$mechanic = $this->EscapeHtml(GetEsoMechanicTypeText($skill['mechanic'], $this->version));
			$link = "?abilityid={$skill['id']}";
			
			$output .= "<tr>";
			$output .= "<td><nobr><a href='$link'>$name $rank</a></nobr></td>";
			$output .= "<td>{$skill['id']}</td>";
			$output .= "<td>$mechanic</td>";
			$output .= "<td>$skillType</td>";
			$output .= "<td>$skillLine</td>";
			$output .= "<td>$numTooltips</td>";
			$output .= "<td>$desc</td>";
			$output .= "<td class='esovsc_nobreak'>$equationData</td>";
			$output .= "</tr>\n";
		}
		
		return $output;
	}
	
	
	public function MakeContentHtml()
	{
		if (!$this->showOldCoefData && count($this->skillTooltips) > 0) return $this->MakeTooltipContentHtml();
		
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
			$desc = $this->EscapeHtml(FormatRemoveEsoItemDescriptionText($skill['coefDescription']));
			
			$equationData = $this->MakeEquationDataHtml($skill);
			if ($equationData == "") continue;
			
			$skillLine = $this->EscapeHtml($skill['skillLine']);
			$skillType = $skill['classType'];
			$rank = $skill['rank'];
			if ($rank <= 0) $rank = '';
			if ($skillType == "") $skillType = $skill['raceType'];
			if ($skillType == "") $skillType = GetEsoSkillTypeText($skill['skillType']);
			$skillType = $this->EscapeHtml($skillType);
			
			$name = $this->EscapeHtml($skill['name']);
			$mechanic = $this->EscapeHtml(GetEsoMechanicTypeText($skill['mechanic'], $this->version));
			$link = "?abilityid={$skill['id']}";
			
			$output .= "<tr>";
			$output .= "<td><nobr><a href='$link'>$name $rank</a></nobr></td>";
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
			
			$mechanic = GetEsoMechanicTypeText($skill['mechanic'], $this->version);
			
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
		
		if ($this->showSkillId > 0)
		{
			$this->LoadSkillHistory();
			$this->LoadSkillTooltipHistory();
			
			return $this->OutputSkillHistory();
		}
		
		$this->LoadSkillCoef();
		$this->LoadSkillTooltips();
		
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
