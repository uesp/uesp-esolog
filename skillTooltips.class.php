<?php
/*
 * Class for handling of ESO skill tooltip and coefficients.
 */


require("/home/uesp/secrets/esolog.secrets");
require_once("esoCommon.php");
require_once("esoSkillRankData.php");


class CEsoSkillTooltips
{
	protected $TABLE_SUFFIX = "";			// Don't set here, set in constructor
	protected $ONLY_DO_ABILITYID = -1;
	protected $DONT_SAVE_TOOLTIPS = false;
	protected $MIN_ALLOWED_ERRORPERCENT = 3;
	protected $MIN_ALLOWED_R2 = 0.99;
	protected $ONLY_UPDATE_DESCRIPTIONS = false;
	
	protected static $POWERTYPE_INVALID = POWERTYPE_INVALID;
	protected static $POWERTYPE_MAGICKA = POWERTYPE_MAGICKA;
	protected static $POWERTYPE_STAMINA = POWERTYPE_STAMINA;
	protected static $POWERTYPE_ULTIMATE = POWERTYPE_ULTIMATE;
	protected static $POWERTYPE_HEALTH = POWERTYPE_HEALTH;
	
	protected $FIXED_COEF_FACTORS = array(
		16212 => array( 1 => 2.0 ),
		50325 => array( 2 => 1.0 ),
	);
	
	protected $RANK_FACTORS = array(
		1 => 1.00,
		2 => 1.01,
		3 => 1.02,
		4 => 1.03,
	);
	
	protected static $NUMBER_REGEX = '/[0-9]+(?:\.[0-9]+)?/';
	
	protected $db = null;
	protected $lastQuery = "";
	protected $isDbReadInitialized = false;
	protected $isDbWriteInitialized = false;
	protected $isDbConnected = false;
	
	protected $updateDescCount = 0;
	protected $updateNameCount = 0;
	protected $updateTooltipCount = 0;
	protected $coefErrorCount = 0;
	
	protected $minedSkills = array();
	
	
	public function __construct($tableSuffix = "")
	{
		$this->TABLE_SUFFIX = $tableSuffix;
		
		$updateVersion = intval($this->TABLE_SUFFIX);
		if ($this->TABLE_SUFFIX == "") $updateVersion = intval(GetEsoUpdateVersion());
		
			// Support for older power type constants
		if ($updateVersion < 34)
		{
			self::$POWERTYPE_INVALID = POWERTYPE_OLD_INVALID;
			self::$POWERTYPE_MAGICKA = POWERTYPE_OLD_MAGICKA;
			self::$POWERTYPE_STAMINA = POWERTYPE_OLD_STAMINA;
			self::$POWERTYPE_ULTIMATE = POWERTYPE_OLD_ULTIMATE;
			self::$POWERTYPE_HEALTH = POWERTYPE_OLD_HEALTH;
		}
	}
	
	
	protected function ReportError($msg)
	{
		if ($this->db && $this->db->error) $msg .= "\n" . $this->db->error . "\n" . $this->lastQuery;
		print("$msg\n");
		return false;
	}
	
	
	private function InitDatabase()
	{
		global $uespEsoLogReadDBHost, $uespEsoLogReadUser, $uespEsoLogReadPW, $uespEsoLogDatabase;
		
		if ($this->isDbWriteInitialized || $this->isDbReadInitialized) return true;
		
		$this->db = new mysqli($uespEsoLogReadDBHost, $uespEsoLogReadUser, $uespEsoLogReadPW, $uespEsoLogDatabase);
		if ($this->db->connect_error) return $this->ReportError("ERROR: Could not connect to esolog database!");
		
		$this->isDbReadInitialized = true;
		$this->isDbWriteInitialized = false;
		$this->isDbConnected = false;
		return true;
	}
	
	
	private function InitDatabaseWrite()
	{
		global $uespEsoLogWriteDBHost, $uespEsoLogWriteUser, $uespEsoLogWritePW, $uespEsoLogDatabase;
		
		if ($this->isDbWriteInitialized) return true;
		
		$this->db = new mysqli($uespEsoLogWriteDBHost, $uespEsoLogWriteUser, $uespEsoLogWritePW, $uespEsoLogDatabase);
		if ($this->db->connect_error) return $this->ReportError("ERROR: Could not connect to esolog database for writing!");
		
		$this->isDbWriteInitialized = true;
		$this->isDbReadInitialized = false;
		$this->isDbConnected = false;
		return true;
	}
	
	
	public function ConnectDB($db, $isWrite = false, $tableSuffix = null)
	{
		$this->db = $db;
		
		$this->isDbConnected = true;
		$this->isDbWriteInitialized = true;
		$this->isDbReadInitialized = $isWrite;
		
		if ($tableSuffix) $this->TABLE_SUFFIX = $tableSuffix;
	}
	
	
	public function DisconnectDB($db, $isWrite = false)
	{
		if ($this->isDbConnected)
		{
			$this->db = null;
			$this->isDbConnected = false;
			$this->isDbWriteInitialized = false;
			$this->isDbReadInitialized = false;
		}
	}
	
	
	public function CreateTable()
	{
		if (!$this->InitDatabaseWrite()) return false;
		
		$this->lastQuery = "CREATE TABLE IF NOT EXISTS skillTooltips{$this->TABLE_SUFFIX} (
						abilityId INTEGER NOT NULL,
						idx TINYINT NOT NULL DEFAULT -1,
						origAbilityId INTEGER NOT NULL,
						coefType TINYINT NOT NULL DEFAULT -1,
						rawType TINYINT NOT NULL DEFAULT -1,
						value TINYTEXT NOT NULL DEFAULT '',
						rawValue1 INTEGER NOT NULL DEFAULT -1,
						rawValue2 INTEGER NOT NULL DEFAULT -1,
						duration INTEGER NOT NULL DEFAULT -1,
						startTime INTEGER NOT NULL DEFAULT -1,
						tickTime INTEGER NOT NULL DEFAULT -1,
						cooldown INTEGER NOT NULL DEFAULT -1,
						a float NOT NULL DEFAULT -1,
						b float NOT NULL DEFAULT -1,
						c float NOT NULL DEFAULT -1,
						r float NOT NULL DEFAULT -1,
						dmgType MEDIUMINT NOT NULL DEFAULT -1,
						isDmg TINYINT(1) NOT NULL DEFAULT 0,
						isHeal TINYINT(1) NOT NULL DEFAULT 0,
						isDmgShield TINYINT(1) NOT NULL DEFAULT 0,
						isAOE TINYINT(1) NOT NULL DEFAULT 0,
						isDOT TINYINT(1) NOT NULL DEFAULT 0,
						isFlameAOE TINYINT(1) NOT NULL DEFAULT 0,
						isElfBane TINYINT(1) NOT NULL DEFAULT 0,
						isPlayer TINYINT(1) NOT NULL DEFAULT 0,
						isMelee TINYINT(1) NOT NULL DEFAULT 0,
						hasRankMod TINYINT(1) NOT NULL DEFAULT 0,
						usesManualCoef TINYINT(1) NOT NULL DEFAULT 0,
						PRIMARY KEY id (abilityId, idx)
				) ENGINE=MYISAM;";
		
		$result = $this->db->query($this->lastQuery);
		if ($result === false) exit("Error: Failed to create the skillTooltips{$this->TABLE_SUFFIX} table!");
		
		return true;
	}
	
	
	public static function ConvertStatTypeToPowerType($type1, $type2, $coef1, $coef2)
	{
		if ($type1 == null) $type1 = 0;
		if ($type2 == null) $type2 = 0;
		
			/* Special cases? */
		if ($type1 != 0 && $type2 ==  0 && $coef1 == 0) return UESP_POWERTYPE_CONSTANTVALUE;
		if ($type2 != 0 && $type1 ==  0 && $coef2 == 0) return UESP_POWERTYPE_CONSTANTVALUE;
		
		if ($type1 == 25 && $type2 ==  4)	return self::$POWERTYPE_MAGICKA;
		if ($type1 ==  4 && $type2 == 25)	return self::$POWERTYPE_MAGICKA;
		if ($type1 == 25 && $type2 ==  0)	return self::$POWERTYPE_MAGICKA;
		if ($type1 ==  4 && $type2 ==  0)	return self::$POWERTYPE_MAGICKA;
		if ($type1 == 25 && $type2 == 35)	return UESP_POWERTYPE_DAMAGE;
		if ($type1 == 35 && $type2 ==  4)	return UESP_POWERTYPE_MAGICKAWITHWD;
		if ($type1 ==  1 && $type2 ==  0)	return UESP_POWERTYPE_WEAPONPOWER;
		if ($type1 ==  2 && $type2 ==  0)	return UESP_POWERTYPE_WEAPONPOWER;
		if ($type1 ==  7 && $type2 ==  0)	return self::$POWERTYPE_HEALTH;
		if ($type1 ==  7 && $type2 ==  7)	return self::$POWERTYPE_HEALTH;	//New in update 33
		if ($type1 == 35 && $type2 == 29)	return self::$POWERTYPE_STAMINA;
		if ($type1 == 29 && $type2 == 35)	return self::$POWERTYPE_STAMINA;
		if ($type1 == 35 && $type2 ==  0)	return self::$POWERTYPE_STAMINA;
		if ($type1 == 29 && $type2 ==  0)	return self::$POWERTYPE_STAMINA;
		if ($type1 ==  0 && $type2 ==  0) 	return UESP_POWERTYPE_CONSTANTVALUE;
		if ($type1 == 22 && $type2 == 13) 	return UESP_POWERTYPE_RESISTANCE;
		if ($type1 == 13 && $type2 == 22) 	return UESP_POWERTYPE_RESISTANCE;
		if ($type1 == 13 && $type2 ==  0) 	return UESP_POWERTYPE_RESISTANCE;
		if ($type1 == 22 && $type2 ==  0) 	return UESP_POWERTYPE_RESISTANCE;
		
		return self::$POWERTYPE_INVALID;
	}
	
	
	protected function LoadAllMinedSkills()
	{
		$this->lastQuery = "SELECT * FROM minedSkills{$this->TABLE_SUFFIX};";
		$result = $this->db->query($this->lastQuery);
		if ($result == false) return $this->ReportError("Error: Failed to load all mined skills!");
		
		$this->minedSkills = array();
		
		while (($skill = $result->fetch_assoc()))
		{
			$id = intval($skill['id']);
			$this->minedSkills[$id] = $skill;
		}
		
		$this->ReportError("Loaded " . count($this->minedSkills) . " mined skills!");
		return true;
	}
	
	
	protected function LoadAllPlayerTooltips()
	{
		$this->skillTooltips = array();
		
		$this->lastQuery = "SELECT * FROM skillTooltips{$this->TABLE_SUFFIX} WHERE isPlayer=1;";
		$result = $this->db->query($this->lastQuery);
		if ($result == false) return false;
		
		while (($tooltip = $result->fetch_assoc()))
		{
			$abilityId = intval($tooltip['abilityId']);
			$tooltipIndex = intval($tooltip['idx']);
			
			if ($this->skillTooltips[$abilityId] == null) $this->skillTooltips[$abilityId] = array();
			$this->skillTooltips[$abilityId][$tooltipIndex] = $tooltip;
		}
		
		foreach ($this->skillTooltips as $abilityId => $tooltips)
		{
			if ($this->minedSkills[$abilityId] == null) $this->minedSkills[$abilityId] = array();
			$this->minedSkills[$abilityId]['tooltips'] = $tooltips;
		}
		
		return true;
	}
	
	
	public function LoadAllPlayerSkills()
	{
		if (!$this->InitDatabase()) return false;
		
		$this->lastQuery = "SELECT * FROM minedSkills{$this->TABLE_SUFFIX} WHERE isPlayer=1;";
		$result = $this->db->query($this->lastQuery);
		if ($result == false) return false;
		
		$this->minedSkills = array();
		
		while (($skill = $result->fetch_assoc()))
		{
			$this->minedSkills[$skill['id']] = $skill;
		}
		
		$this->LoadAllPlayerTooltips();
		
		return $this->minedSkills;
	}
	
	
	public function LoadSkill($abilityId)
	{
		if (!$this->InitDatabase()) return false;
		
		$safeId = $this->db->real_escape_string($abilityId);
		$this->lastQuery = "SELECT * FROM minedSkills{$this->TABLE_SUFFIX} WHERE id='$safeId';";
		$result = $this->db->query($this->lastQuery);
		if ($result == false) return false;
		
		$skillData = $result->fetch_assoc();
		$skillTooltips = array();
		
		$this->lastQuery = "SELECT * FROM skillTooltips{$this->TABLE_SUFFIX} WHERE abilityId='$safeId';";
		$result = $this->db->query($this->lastQuery);
		if ($result == false) return $skillData;
		
		while (($tooltip = $result->fetch_assoc()))
		{
			$tooltipIndex = intval($tooltip['idx']);
			$skillTooltips[$tooltipIndex] = $tooltip;
		}
		
		$skillData['tooltips'] = $skillTooltips;
		return $skillData;
	}
	
	
	public function SaveTooltipFlags($abilityId, $tooltipIndex, $flags)
	{
		$values = array();
		
		foreach ($flags as $flag => $value)
		{
			$value = intval($value);
			$values[] = "$flag='$value'";
		}
		
		$values = implode(",", $values);
		$this->lastQuery = "UPDATE skillTooltips{$this->TABLE_SUFFIX} SET $values WHERE abilityId='$abilityId' AND idx='$tooltipIndex';";
		
		if ($this->DONT_SAVE_TOOLTIPS)
		{
			if ($abilityId == $this->ONLY_DO_ABILITYID) print($this->lastQuery . "\n");
			return true;
		}
		
		$result = $this->db->query($this->lastQuery);
		if (!$result) return false;
		
		return true;
	}
	
	
	public function ResetAllTooltipFlags()
	{
		$this->lastQuery = "UPDATE skillTooltips{$this->TABLE_SUFFIX} SET isDmg=0, isHeal=0, isDmgShield=0, isAOE=0, isDOT=0, isFlameAOE=0, isElfBane=0, isMelee=0;";
		
		if ($this->DONT_SAVE_TOOLTIPS) return true;
		
		if (!$this->InitDatabaseWrite()) return false;
		
		$result = $this->db->query($this->lastQuery);
		if ($result == false) return $this->ReportError("Error: Failed to reset all tooltip flags!");
		
		return true;
	}
	
	
	protected function ResetAllTooltipData()
	{
		if ($this->DONT_SAVE_TOOLTIPS) return true;
		
		$this->lastQuery = "TRUNCATE TABLE skillTooltips{$this->TABLE_SUFFIX};";
		$result = $this->db->query($this->lastQuery);
		if ($result === false) return $this->ReportError("Failed to empty table skillTooltips{$this->TABLE_SUFFIX}!"); 
		return true;
	}
	
	
	public static function MakeNiceDescription($skillDesc)
	{
		$skillDesc = str_replace("<<(AB_PHYSICAL_DAMAGE:104695>>", "AB_PHYSICAL_DAMAGE", $skillDesc);
		$skillDesc = str_replace("<<AB_DURATION:17566))>>", "AB_DURATION", $skillDesc);
		$skillDesc = str_replace(")>>", ">>", $skillDesc);
		$skillDesc = str_replace(":>>", ">>", $skillDesc);
		$skillDesc = preg_replace("/\|c[a-fA-F0-9]{6}/", "", $skillDesc);
		$skillDesc = str_replace("|r", "", $skillDesc);
		$skillDesc = str_replace("    ", " ", $skillDesc);
		$skillDesc = str_replace("   ", " ", $skillDesc);
		$skillDesc = str_replace("  ", " ", $skillDesc);
		$skillDesc = str_replace("  ", " ", $skillDesc);
		$skillDesc = str_replace("\r\n", "\n", $skillDesc);
		$skillDesc = str_replace("\x3F\xB7", "", $skillDesc);
		$skillDesc = str_replace("\xC2\xB7", "", $skillDesc);
		$skillDesc = str_replace("\xB7", "", $skillDesc);
		$skillDesc = str_replace("\x3F", "", $skillDesc);
		$skillDesc = preg_replace('/>>%,$/i', ">>%", $skillDesc);
		$skillDesc = preg_replace('/>>,$/i', ">>", $skillDesc);
		
		return $skillDesc;
	}
	
	
	public static function MakeMatchFromRawDescription($desc)
	{
		$matchDesc = CEsoSkillTooltips::MakeNiceDescription($desc);
		
		$matchDesc = str_replace("(", "\(", $matchDesc);
		$matchDesc = str_replace(")", "\)", $matchDesc);
		$matchDesc = str_replace("+", "\+", $matchDesc);
		$matchDesc = str_replace("an <<", "a[n]? <<", $matchDesc);
		
		$matchDesc = preg_replace("/<<[0-9]+>>/", "(.*)", $matchDesc);
		$matchDesc = preg_replace(CEsoSkillTooltips::$NUMBER_REGEX, ".*", $matchDesc);
		
		return "#$matchDesc#i";
	}
	
	
	public static function CountTooltipsInRawDescription($desc)
	{
		$matchCount = preg_match_all("/<<[0-9]>>/", $desc);
		if ($matchCount === false) return 0;
		return $matchCount;
	}
	
	
	protected function UpdateSkillRawTimes($abilityId, $startTime, $tickTime, $cooldown)
	{
		if ($this->DONT_SAVE_TOOLTIPS) return true;
		
		if ($startTime == null) $startTime = 0;
		if ($tickTime == null) $tickTime = 0;
		if ($cooldown == null) $cooldown = 0;
		
		$safeTime = intval($startTime);
		$safeTick = intval($tickTime);
		$safeCooldown = intval($cooldown);
		
		$this->lastQuery = "UPDATE minedSkills{$this->TABLE_SUFFIX} SET startTime='$safeTime', tickTime='$safeTick', cooldown='$safeCooldown' where id='$abilityId';";
		
		$result = $this->db->query($this->lastQuery);
		if ($result === false) return $this->ReportError("Failed to update raw time data for skill $abilityId!");
		
		return true;
	}
	
	
	protected function UpdateSkillRawName($abilityId, $rawName)
	{
		if ($this->DONT_SAVE_TOOLTIPS) return true;
		
		$rawName = str_replace("  ", " ", $rawName);
		$rawName = str_replace("  ", " ", $rawName);
		
		$safeName = $this->db->real_escape_string($rawName);
		$this->lastQuery = "UPDATE minedSkills{$this->TABLE_SUFFIX} SET rawName='$safeName' where id='$abilityId';";
		
		//print($this->lastQuery . "\n");
		
		$result = $this->db->query($this->lastQuery);
		if ($result === false) return $this->ReportError("Failed to update raw name for skill $abilityId!");
		
		++$this->updateNameCount;
		return true;
	}
	
	
	protected function UpdateSkillRawDescription($abilityId, $rawDesc)
	{
		if ($this->DONT_SAVE_TOOLTIPS) return true;
		
		$rawDesc = str_replace("  ", " ", $rawDesc);
		$rawDesc = str_replace("  ", " ", $rawDesc);
		
		$safeDesc = $this->db->real_escape_string($rawDesc);
		$this->lastQuery = "UPDATE minedSkills{$this->TABLE_SUFFIX} SET rawDescription='$safeDesc' where id='$abilityId';";
		
		//print($this->lastQuery . "\n");
		
		$result = $this->db->query($this->lastQuery);
		if ($result === false) return $this->ReportError("Failed to update raw description for skill $abilityId!");
		
		++$this->updateDescCount;
		return true;
	}
	
	
	public static function ImplodeSql($values, $quoteChar)
	{
		$result = "";
		$isFirst = true;
		
		foreach ($values as $value)
		{
			if (!$isFirst) $result .= ",";
			
			if ($quoteChar)
				$result .= $quoteChar . $value . $quoteChar;
			else
				$result .= $value;
			
			$isFirst = false;
		}
		
		return $result;
	}
	
	
	protected function SaveTooltipInfo($abilityId, $tooltipIndex, $tooltipInfo)
	{
		$cols = array(
				'abilityId',
				'idx',
				'origAbilityId',
				'coefType',
				'rawType',
				'value',
				'a',
				'b',
				'c',
				'r',
				'usesManualCoef',
				'isPlayer',
				'dmgType',
				'hasRankMod',
				'duration',
				'startTime',
				'tickTime',
				'cooldown',
				'rawValue1',
				'rawValue2',
		);
		
		if ($tooltipInfo['duration'] == '') $tooltipInfo['duration'] = '-1';
		if ($tooltipInfo['startTime'] == '') $tooltipInfo['startTime'] = '-1';
		if ($tooltipInfo['tickTime'] == '') $tooltipInfo['tickTime'] = '-1';
		if ($tooltipInfo['cooldown'] == '') $tooltipInfo['cooldown'] = '-1';
		if ($tooltipInfo['rawValue1'] == '') $tooltipInfo['rawValue1'] = '-1';
		if ($tooltipInfo['rawValue2'] == '') $tooltipInfo['rawValue2'] = '-1';
		
		if ($tooltipInfo['isManual'] == '') $tooltipInfo['isManual'] = '0';
		if ($tooltipInfo['isPlayer'] == '') $tooltipInfo['isPlayer'] = '0';
		
		$values = array(
				$abilityId,
				$tooltipInfo['tooltipIndex'],
				$tooltipInfo['origAbilityId'],
				$tooltipInfo['coefType'],
				$tooltipInfo['rawType'],
				$this->db->real_escape_string($tooltipInfo['value']),
				$tooltipInfo['a'],
				$tooltipInfo['b'],
				$tooltipInfo['c'],
				$tooltipInfo['r'],
				$tooltipInfo['isManual'],
				$tooltipInfo['isPlayer'],
				$tooltipInfo['dmgType'],
				$tooltipInfo['hasRankMod'],
				$tooltipInfo['duration'],
				$tooltipInfo['startTime'],
				$tooltipInfo['tickTime'],
				$tooltipInfo['cooldown'],
				$tooltipInfo['rawValue1'],
				$tooltipInfo['rawValue2'],
		);
		
		$cols = $this->ImplodeSql($cols, "`");
		$values = $this->ImplodeSql($values, "'");
		
		$this->lastQuery = "INSERT INTO skillTooltips{$this->TABLE_SUFFIX}($cols) VALUES($values);";
		
		if ($this->DONT_SAVE_TOOLTIPS) 
		{
			if ($abilityId == $this->ONLY_DO_ABILITYID) print($this->lastQuery . "\n");
			return true;
		}
		
		$result = $this->db->query($this->lastQuery);
		if ($result === false) return $this->ReportError("Error: Failed to save tooltip info for ability $abilityId:{$tooltipInfo['tooltipIndex']}!");
		
		++$this->updateTooltipCount;
		return true;
	}
	
	
	protected function SaveTooltipInfos($abilityId, $tooltipInfos)
	{
		foreach ($tooltipInfos as $i => $tooltipInfo)
		{
			if (!$this->SaveTooltipInfo($abilityId, $i, $tooltipInfo)) return false;
		}
		
		return true;
	}
	
	
	protected static function ComputePercentError($a1, $a2, $absValue)
	{
		if ($a1 == 0 && $a2 == 0)
			$errora = 0;
		else if ($a1 == 0 || $a2 == 0)
 			$errora = abs(($a1 - $a2)/$absValue * 100.0);
		else
			$errora = abs(($a1 - $a2)/$a1 * 100.0);
		
		return $errora;
	}
	
	
	protected function CompareRawCoefValues($abilityId, $rank, $coefIndex, $newInfo)
	{
		$minedSkill = $this->minedSkills[$abilityId];
		if ($coefIndex > $minedSkill['numCoefVars']) return true;
		
		$newInfo['coefIndex'] = $coefIndex;
		
		$a1 = $minedSkill['a' . $coefIndex];
		$b1 = $minedSkill['b' . $coefIndex];
		$c1 = $minedSkill['c' . $coefIndex];
		$r1 = $minedSkill['R' . $coefIndex];
		$coefType1 = $minedSkill['type' . $coefIndex];
		
		$coefFactor = $newInfo['coefFactor'];
		$a2 = $newInfo['a'] * $coefFactor;
		$b2 = $newInfo['b'] * $coefFactor;
		$c2 = $newInfo['c'];
		$r2 = $newInfo['r'] * $coefFactor;
		$coefType2 = $newInfo['coefType'];
		
		$errora = $this->ComputePercentError($a1, $a2, 1.0);
		$errorb = $this->ComputePercentError($b1, $b2, 0.1);
		$errorc = 0;
		if ($c2 != 0) $errorc = $this->ComputePercentError($c1, $c2, 0.1);
		
		if ($coefType1 == UESP_POWERTYPE_MAGICHEALTHCAP) 
		{
			$errorb = 0;
			$b2 = $b1;
		}
		
		if ($r1 < $this->MIN_ALLOWED_R2)
		{
			if ($this->ONLY_DO_ABILITYID > 0) print("\t$abilityId : $rank : {$newInfo['tooltipIndex']}: Not using mined coefficient values due to small R2 ($r1)!\n");
			return false;
		}
		
		if ($errora >= $this->MIN_ALLOWED_ERRORPERCENT || $errorb >= $this->MIN_ALLOWED_ERRORPERCENT || $errorc >= $this->MIN_ALLOWED_ERRORPERCENT)
		{
			print("\t$abilityId: $rank: {$newInfo['tooltipIndex']}: Large difference in coefficients found ($errora%, $errorb%) TooltipType = {$newInfo['rawType']}, CoefType = {$newInfo['coefType']}!\n");
			
			$origa2 = $newInfo['rawa'];
			$origb2 = $newInfo['rawb'];
			$origc2 = $newInfo['rawc'];
			$hasRankMod = $this->DoesAbilityCoefChangeWithRank($abilityId, $coefIndex);
			
			if ($c2)
			{
				print("\t\t  Mined: $a1 A + $b1 B + $c1 C  (R=$r1)\n");
				print("\t\tTooltip: $a2 A + $b2 B + $c2 C \n");
				print("\t\t OrigTT: $origa2 A + $origb2 B + $origc2 C\n");
				print("\t\t HasRankMod($coefIndex): $hasRankMod\n");
			}
			else
			{
				print("\t\t  Mined: $a1 A + $b1 B + $c1 C  (R=$r1)\n");
				print("\t\tTooltip: $a2 A + $b2 B\n");
				print("\t\t OrigTT: $origa2 A + $origb2 B\n");
				print("\t\t HasRankMod($coefIndex): $hasRankMod\n");
			}
			
			//print("\t\tDuration: $duration,  Start: $startTime,  Tick: $tickLength\n");
			++$this->coefErrorCount;
			return true;
		}
		
			/* Use raw cofficient by default if the error is small */
		return false;
	}
	
	
	protected function SetTooltipInfoCoefValues($abilityId, $rank, $tooltipIndex, $coefIndex, &$newInfo)
	{
		$minedSkill = $this->minedSkills[$abilityId];
		if ($coefIndex > $minedSkill['numCoefVars']) return;
		
		$newInfo['coefIndex'] = $coefIndex;
		$newInfo['isPlayer'] = $minedSkill['isPlayer'];
		
		$a = $minedSkill['a' . $coefIndex];
		$b = $minedSkill['b' . $coefIndex];
		$c = $minedSkill['c' . $coefIndex];
		$r = $minedSkill['R' . $coefIndex];
		$coefType = $minedSkill['type' . $coefIndex];
		
		$useValues = true;
		if ($newInfo['usesRawData']) $useValues = $this->CompareRawCoefValues($abilityId, $rank, $coefIndex, $newInfo);
		
		if ($useValues)
		{
			global $ESO_RAWSKILL_DATA;
			
			$baseAbilityId = $this->GetBaseAbilityRankId($abilityId, $rank);
			//print("\t\t$abilityId:$rank: Using manual coefficients (base $baseAbilityId)...\n");
			
			$rawSkillData = $ESO_RAWSKILL_DATA[$baseAbilityId];
			if ($rawSkillData == null) $rawSkillData = array();
			
			$rawCoef = $rawSkillData['coef'][$tooltipIndex - 1];
			if ($rawCoef == null) $rawCoef = array();
			
			$tooltipType = $rawCoef['type'];
			$tooltipId = $rawCoef['id'];
			
			$rawTooltipData = $ESO_RAWSKILL_DATA[$tooltipId];
			if ($rawTooltipData == null) $rawTooltipData = array();
			
			$duration = $rawTooltipData['duration']/1000;
			$tickLength = $rawTooltipData['tick']/1000;
			$startTime = $rawTooltipData['start']/1000;
			
			//print("\t\t$abilityId:$rank: Type/ID $tooltipType:$tooltipId\n");
			
				/* Scale back manual coefficients to ticks */
			if (($tooltipType == 49 || $tooltipType == 53) && $duration > 0)
			{
				if ($tickLength > 0)
					$coefFactor = ($duration + $tickLength) / $tickLength;
				else
					$coefFactor = $duration;
				
				//print("\t\t$abilityId:$rank: Using manual coefficient factor of $coefFactor\n");
				
				if ($coefFactor != 0)
				{
					$a = $a / $coefFactor;
					$b = $b / $coefFactor;
					$c = $c / $coefFactor;
				}
			}
			
			$newInfo['a'] = $a;
			$newInfo['b'] = $b;
			$newInfo['c'] = $c;
			$newInfo['r'] = $r;
			$newInfo['coefType'] = $coefType;
			$newInfo['isManual'] = 1;
		}
	}
	
	
	protected function GetTooltipCoefFactor($abilityId, $rank, $tooltipIndex, $coefIndex)
	{
		global $ESO_RAWSKILL_DATA;
		
		$coefFactor = 1;
		$baseAbilityId = $this->GetBaseAbilityRankId($abilityId, $rank);
		
		$rawSkillData = $ESO_RAWSKILL_DATA[$baseAbilityId];
		if ($rawSkillData == null) return $coefFactor;
		
		$rawCoef = $rawSkillData['coef'][$tooltipIndex - 1];
		if ($rawCoef == null) return $coefFactor;
		
		$tooltipType = $rawCoef['type'];
		$tooltipId = $rawCoef['id'];
		
		$rawTooltipData = $ESO_RAWSKILL_DATA[$tooltipId];
		
		if ($rawTooltipData == null) 
		{
			//print("\t\tNo tooltip data for $tooltipId\n");
			$rawTooltipData = array();
		}
		
		$duration = $rawTooltipData['duration']/1000;
		$tickLength = $rawTooltipData['tick']/1000;
		$startTime = $rawTooltipData['start']/1000;
		
		//print("\t\tTick Coef Factor for $tooltipId: $duration x $tickLength\n");
		
		if (($tooltipType == 49 || $tooltipType == 53) && $duration > 0)
		{
			//print("\t\tHas Tick Coef Factor: $duration x $tickLength\n");
			
			if ($tickLength > 0)
				$coefFactor = ($duration + $tickLength) / $tickLength;
			else
				$coefFactor = $duration;
		}
		
		$fixedFactor = $this->FIXED_COEF_FACTORS[$abilityId];
		
		if ($fixedFactor)
		{
			$fixedFactor = $fixedFactor[$tooltipIndex];
			
			if ($fixedFactor)
			{
				$coefFactor = $fixedFactor;
				print("\t$abilityId: $rank: $tooltipIndex: Using a fixed coefficient factor of $fixedFactor!\n");
			}
		}
		
		if ($tooltipType == 92)
		{
			$coefFactor *= 0.1;
		}
		
		if ($rank > 1)
		{
			$hasRankMod = $this->DoesAbilityCoefChangeWithRank($abilityId, $coefIndex);
			
			if ($hasRankMod)
			{
				$coefFactor *= $this->RANK_FACTORS[$rank];
				if ($this->ONLY_DO_SKILLID > 0) print("\t$abilityId: $rank: $tooltipIndex: Using $rank factor {$this->RANK_FACTORS[$rank]}!\n");
			}
		}
		
		//print("\t\tCoefFactor for $abilityId:$rank = $coefFactor\n");
		return $coefFactor;
	}
	
	
	protected function SetTooltipInfoRawCoefValues($abilityId, $rank, $tooltipIndex, $coefIndex, &$newInfo)
	{
		global $ESO_RAWSKILL_DATA;
		
		$baseAbilityId = $this->GetBaseAbilityRankId($abilityId, $rank);
		
		$rawSkillData = $ESO_RAWSKILL_DATA[$baseAbilityId];
		if ($rawSkillData == null) return false;
		
		$rawCoef = $rawSkillData['coef'][$tooltipIndex - 1];
		if ($rawCoef == null) return $this->ReportError("$abilityId : $rank : $tooltipIndex : Missing raw skill coefficient data!");
		
		$tooltipType = $rawCoef['type'];
		$tooltipId = $rawCoef['id'];
		
		//if ($rawSkillData['dmgtype']) $newInfo['dmgType'] = $rawSkillData['dmgtype'];
		if ($rawCoef['dmgtype']) $newInfo['dmgType'] = $rawCoef['dmgtype'];
		if ($tooltipType) $newInfo['rawType'] = $tooltipType;
		if ($tooltipId) $newInfo['origAbilityId'] = $tooltipId;
		
		$rawTooltipData = $ESO_RAWSKILL_DATA[$tooltipId];
		if ($rawTooltipData == null) $rawTooltipData = array();
		
		if ($rawTooltipData['duration']) $newInfo['duration'] = $rawTooltipData['duration'];
		if ($rawTooltipData['tick']) $newInfo['tick'] = $rawTooltipData['tick'];
		if ($rawTooltipData['start']) $newInfo['start'] = $rawTooltipData['start'];
		if ($rawTooltipData['dmgtype']) $newInfo['dmgType'] = $rawTooltipData['dmgtype'];
		
		$value1 = $rawTooltipData['value1'];
		$value2 = $rawTooltipData['value2'];
		$newInfo['rawValue1'] = $value1;
		$newInfo['rawValue2'] = $value2;
		$newInfo['duration'] = $rawTooltipData['duration'];
		$newInfo['startTime'] = $rawTooltipData['start'];
		$newInfo['tickTime'] = $rawTooltipData['tick'];
		$newInfo['cooldown'] = $rawTooltipData['cooldown'];
		
		$coefType1 = $this->ConvertStatTypeToPowerType($rawCoef['type1'], $rawCoef['type2'], $rawCoef['coef1'], $rawCoef['coef2']);
		$coefType2 = $this->ConvertStatTypeToPowerType($rawCoef['type3'], $rawCoef['type4'], $rawCoef['coef3'], $rawCoef['coef4']);
		
		if ($coefType1 == self::$POWERTYPE_INVALID) print("\t\t$abilityId:$rank:$tooltipIndex: Invalid coefficient type found: {$rawCoef['type1']}:{$rawCoef['type2']}\n");
		if ($coefType2 == self::$POWERTYPE_INVALID) print("\t\t$abilityId:$rank:$tooltipIndex: Invalid coefficient type found: {$rawCoef['type3']}:{$rawCoef['type4']}\n");
		
		if (($coefType1 == UESP_POWERTYPE_CONSTANTVALUE && $coefType2 == UESP_POWERTYPE_CONSTANTVALUE) || ($coefType1 == self::$POWERTYPE_INVALID && $coefType2 == self::$POWERTYPE_INVALID))
		{
			if ($this->ONLY_DO_ABILITYID > 0) print("\t$abilityId : $rank : $tooltipIndex: No raw cofficient values to use!\n");
			return true;
		}
		
		if (($coefType1 == self::$POWERTYPE_MAGICKA && $coefType2 == self::$POWERTYPE_STAMINA) || ($coefType1 == self::$POWERTYPE_STAMINA && $coefType2 == self::$POWERTYPE_MAGICKA))
			$coefType = self::$POWERTYPE_ULTIMATE;
		else if ($coefType1 == self::$POWERTYPE_MAGICKA && $coefType2 == self::$POWERTYPE_HEALTH)
			$coefType = UESP_POWERTYPE_HEALTHORSPELLDAMAGE;
		else if ($coefType1 == UESP_POWERTYPE_DAMAGE && $coefType2 == self::$POWERTYPE_HEALTH)
			$coefType = UESP_POWERTYPE_HEALTHORDAMAGE;
		else 
			$coefType = $coefType1;
		
		$newInfo['coefFactor'] = $this->GetTooltipCoefFactor($abilityId, $rank, $tooltipIndex, $coefIndex);
		$coefFactor = 1;
		$hasRankMod = $this->DoesAbilityCoefChangeWithRank($abilityId, $coefIndex);
		$newInfo['hasRankMod'] = $hasRankMod ? 1 : 0;
		
		$a = 0;
		$b = 0;
		$c = 0;
		$rawa = 0;
		$rawb = 0;
		$rawc = 0;
		
		$rawType1 = $rawCoef['type1'];
		$rawType2 = $rawCoef['type2'];
		$rawCoef1 = $rawCoef['coef1'];
		$rawCoef2 = $rawCoef['coef2'];
		
		if ($rawType1 == 0)
		{
			$rawType1 = $rawCoef['type3'];
			$rawCoef1 = $rawCoef['coef3'];
		}
		
		if ($rawType2 == 0)
		{
			$rawType2 = $rawCoef['type4'];
			$rawCoef2 = $rawCoef['coef4'];
		}
		
		if ($rawType2 > 0)
		{
			$a = $rawCoef2 * $coefFactor;
			$rawa = $rawCoef2;
		}
		
		if ($rawType1 > 0)
		{
			$b = $rawCoef1 * $coefFactor;
			$rawb = $rawCoef1;
		}
		
		if ($rawType1 == 1)
		{
			$c = ceil(($value1 + $value2)/2);
			$a = $b;
			$b = 0;		}
		else if ($rawType1 == 4 || $rawType1 == 29)
		{
			$tmp = $a;
			$a = $b;
			$b = $tmp;
		}
		else if ($rawType1 == 2)
		{
		 	$a = $b;
		 	if ($value1) $a += $value1/100;
		 	$b = 0;
		}
		else if ($rawType1 == 7 || $rawType1 == 13 || $rawType1 == 22)
		{
			$a = $b;
			$b = 0;
		}
		
		if ($coefType == UESP_POWERTYPE_HEALTHORSPELLDAMAGE)
		{
			$a = $rawCoef['coef1'];
			$b = $rawCoef['coef3'];
			$c = 0;
			$rawa = $a;
			$rawb = $b;
			$rawc = $c;
		}
		else if ($coefType == UESP_POWERTYPE_HEALTHORDAMAGE)
		{
			$a = $rawCoef['coef1'];
			$b = $rawCoef['coef2'];
			$c = $rawCoef['coef3'];
			$rawa = $a;
			$rawb = $b;
			$rawc = $c;
		}
		
			/* Check for health capped damage shield coefficients */
		if ($tooltipType == 16 && $coefType == self::$POWERTYPE_MAGICKA && $b == 0)
		{
			if ($this->ONLY_DO_ABILITYID > 0) print("\t\tChecking for health capped damage shield...\n");
			
			$desc = FormatEsoItemDescriptionText($rawSkillData['desc']);
			$hasMatch = preg_match('/Damage shield strength capped at ([0-9]+)%/i', $desc, $matches); 
			
			if ($hasMatch)
			{
				$capValue = intval($matches[1]);
				
				if ($capValue > 0 and $capValue < 100)
				{
					$b = $capValue / 100;
					$rawb = $b;
					$coefType = UESP_POWERTYPE_MAGICHEALTHCAP;
				}
			}
		}
		
		if ($this->ONLY_DO_ABILITYID > 0)
		{
			print("\tCoefType: $coefType,  RawTypes: {$rawCoef['type1']}:{$rawCoef['type2']} / {$rawCoef['type3']}:{$rawCoef['type4']}\n");
			print("\t\t Values: $a, $b, $c\n");
			print("\t\t    Raw: $rawa, $rawb, $rawc\n");
		}
		
		$newInfo['a'] = $a;
		$newInfo['b'] = $b;
		$newInfo['c'] = $c;
		$newInfo['rawa'] = $rawa;
		$newInfo['rawb'] = $rawb;
		$newInfo['rawc'] = $rawc;
		$newInfo['r'] = "1";
		$newInfo['coefType'] = $coefType;
		$newInfo['isManual'] = 0;
		$newInfo['usesRawData'] = 1;
		
		return true;
	}
	
	
	protected function CreateCoefTooltipInfo($abilityId, $rank, $coefMatches, $tooltipIndexMatches, $minedDescMatches)
	{
		$tooltipInfo = array();
		$tooltipIndexes = array();
		
		$baseAbilityId = $this->GetBaseAbilityRankId($abilityId, $rank);
		
		for ($i = 1; $i < count($coefMatches) && $i <= count($tooltipIndexMatches); ++$i)
		{
			$tooltipIndex = intval($tooltipIndexMatches[$i - 1]);
			if ($tooltipIndexes[$tooltipIndex]) continue;
			$tooltipIndexes[$tooltipIndex] = 1;
			
			$newInfo = array();
			$newInfo['baseAbilityId'] = $baseAbilityId;
			$newInfo['abilityId'] = $abilityId;
			$newInfo['index'] = $i;
			$newInfo['coefIndex'] = -1;
			$newInfo['origAbilityId'] = 0;
			$newInfo['coefType'] = -1;
			$newInfo['rawType'] = 0;
			$newInfo['value'] = "";
			$newInfo['a'] = 0;
			$newInfo['b'] = 0;
			$newInfo['c'] = 0;
			$newInfo['r'] = 0;
			$newInfo['rank'] = $rank;
			$newInfo['isManual'] = 0;
			$newInfo['usesRawData'] = 0;
			$newInfo['tooltipIndex'] = $tooltipIndex;
			$newInfo['isPlayer'] = 0;
			$newInfo['dmgType'] = 0;
			$newInfo['rawValue1'] = 0;
			$newInfo['rawValue2'] = 0;
			$newInfo['hasRankMod'] = 0;
			$newInfo['duration'] = 0;
			$newInfo['startTime'] = 0;
			$newInfo['tickTime'] = 0;
			$newInfo['cooldown'] = 0;
			
			$coefMatch = $coefMatches[$i];
			$newInfo['value'] = $coefMatch;
			$hasCoefMatch = preg_match("/[$]([0-9]+)/", $coefMatch);
			
			$coefIndex = -1;
			if ($hasCoefMatch) $coefIndex = intval($coefMatch[1]);
			
			$this->SetTooltipInfoRawCoefValues($abilityId, $rank, $tooltipIndex, $coefIndex, $newInfo);
			
			if ($hasCoefMatch)
			{
				$newInfo['coefIndex'] = $coefIndex;
				$this->SetTooltipInfoCoefValues($abilityId, $rank, $tooltipIndex, $coefIndex, $newInfo);
				if ($minedDescMatches[$i] != null) $newInfo['value'] = $minedDescMatches[$i];
			}
			else
			{
				$newInfo['coefType'] = UESP_POWERTYPE_CONSTANTVALUE;
				//print("\t$abilityId:$tooltipIndex: No coefIndex match!\n");
			}
			
			$tooltipInfo[$i] = $newInfo;
		}
		
		return $tooltipInfo;
	}
	
	
	public function GetSkillRankData($skillData)
	{
		global $ESO_BASESKILL_RANKDATA;
		
		if ($skillData == null) return null;
		
		$rankData = $ESO_BASESKILL_RANKDATA[$skillData['id']];
		if ($rankData != null) return $rankData;
		
		if ($skillData['isPlayer'] == 1 && $skillData['isPassive'] == 0)
		{
			$rankData = array(
					1 => intval($skillData['id']),
					2 => intval($skillData['id']) + 20000000,
					3 => intval($skillData['id']) + 30000000,
					4 => intval($skillData['id']) + 40000000,
			);
			return $rankData;
		}
		
		return null;
	}
	
	
	protected function GetAbilityRankId($abilityId, $rank)
	{
		global $ESO_BASESKILL_RANKDATA;
		
		if ($rank == 1) return $abilityId;
		
		$rankData = $ESO_BASESKILL_RANKDATA[$abilityId];
		
		if ($rankData != null)
		{
			if ($rankData[$rank] == null) return $abilityId;
			return $rankData[$rank];
		}
		
		return $abilityId + 10000000 * rank;
	}
	
	
	protected function GetRank4AbilityRankId($abilityId)
	{
		global $ESO_BASESKILL_RANKDATA;
		
		$rankData = $ESO_BASESKILL_RANKDATA[$abilityId];
		
		if ($rankData != null)
		{
			if ($rankData[4] == null) return $abilityId;
			return $rankData[4];
		}
		
		return $abilityId + 40000000;
	}
	
	
	protected function GetBaseAbilityRankId($abilityId, $rank = null)
	{
		global $ESO_SKILL_RANKDATA;
		
		if ($rank == 1) return $abilityId;
		
		$rankData = $ESO_SKILL_RANKDATA[$abilityId];
		
		if ($rankData != null)
		{
			if ($rankData[0] == null) return $abilityId;
			return $rankData[0];
		}
		
		if ($abilityId >= 40000000) return $abilityId - 40000000;
		if ($abilityId >= 30000000) return $abilityId - 30000000;
		if ($abilityId >= 20000000) return $abilityId - 20000000;
		
		return $abilityId;
	}
	
	
	protected function DoesAbilityCoefChangeWithRank($abilityId, $coefIndex)
	{
		//print("\t\t\tRankMod($abilityId, $coefIndex)\n");
		
		$minedSkill = $this->minedSkills[$abilityId];
		if ($minedSkill == null) return false;
		if ($minedSkill['isPlayer'] == 0) return false;
		if ($minedSkill['isPassive'] == 1) return false;
		
		$baseAbilityId = $this->GetBaseAbilityRankId($abilityId);
		$rankAbilityId = $this->GetRank4AbilityRankId($baseAbilityId);
		
		$baseSkill = $this->minedSkills[$baseAbilityId];
		$rankSkill = $this->minedSkills[$rankAbilityId];
		if ($baseSkill == null || $rankSkill == null) return false; 
		
		if ($baseSkill['numCoefVars'] < $coefIndex) return false;
		
		$a1 = $baseSkill['a' . $coefIndex];
		$a2 = $rankSkill['a' . $coefIndex];
		
		$b1 = $baseSkill['b' . $coefIndex];
		$b2 = $rankSkill['b' . $coefIndex];
		
		$diffa = abs($a1 - $a2);
		$diffb = abs($b1 - $b2);
		$diffAPercent = 0;
		$diffBPercent = 0;
		if ($a1 != 0) $diffAPercent = $diffa / $a1 * 100;
		if ($b1 != 0) $diffBPercent = $diffb / $b1 * 100;
		
		//print("\t\t\tRankMod($abilityId, $coefIndex)($baseAbilityId, $rankAbilityId): $a1 : $a2 : $diffa : $diffAPercent : " . ($diffa < 0.00025) . "\n");
		//print("\t\t\t\t\t\t: $b1 : $b2 : $diffb : $diffBPercent : " . ($diffb < 0.00025) . "\n");
		
		if ($diffAPercent > 2) return true;
		if ($diffa >= 0.00025) return true;
		if ($diffBPercent > 2) return true;
		if ($diffb >= 0.00025) return true;
		
		return false;
	}
	
	
	protected function UpdateSkillRawData($abilityId)
	{
		global $ESO_RAWSKILL_DATA;
		
		if ($this->ONLY_DO_ABILITYID > 0 && $this->ONLY_DO_ABILITYID != $abilityId) return true;
		
		$minedSkill = $this->minedSkills[$abilityId];
		if ($minedSkill['description'] == "") return true;
		
		if ($minedSkill['isPlayer'] == 1 && $minedSkill['isPassive'] == 0) $rank = intval($minedSkill['rank']);
		$baseAbilityId = $this->GetBaseAbilityRankId($abilityId, $rank);
		
		$rawSkillData = $ESO_RAWSKILL_DATA[$baseAbilityId];
		if ($rawSkillData == null) return true;
		
		$coefDesc = $this->MakeNiceDescription($minedSkill['coefDescription']);
		$minedDesc = $this->MakeNiceDescription($minedSkill['description']);
		if ($coefDesc == "") $coefDesc = $minedDesc;
		
		$hasNumber = preg_match(CEsoSkillTooltips::$NUMBER_REGEX, $minedDesc);
		if (!$hasNumber) return true;
		
		//$rawDesc = iconv('WINDOWS-1256', 'UTF-8', $rawSkillData['desc']);		// Doesn't work for some binary character sequences
		$rawDesc = $rawSkillData['desc'];
		$rawName = $rawSkillData['name'];
		if ($rawDesc == null || $rawDesc == "") return true;
		
		$rawDesc = str_replace(")>>", ">>", $rawDesc);
		
		if ($this->ONLY_UPDATE_DESCRIPTIONS)
		{
			if (!$this->UpdateSkillRawDescription($abilityId, $rawDesc)) return false;
			return true;
		}
		
		if (!$this->UpdateSkillRawName($abilityId, $rawName)) return false;
		if (!$this->UpdateSkillRawDescription($abilityId, $rawDesc)) return false;
		if (!$this->UpdateSkillRawTimes($abilityId, $rawSkillData['start'], $rawSkillData['tick'],  $rawSkillData['cooldown'])) return false;
		
		$numTooltips = preg_match_all("/<<([0-9]+)>>/", $rawDesc, $tooltipIndexMatches);
		if ($numTooltips == 0) return true;
		
		$matchDesc = $this->MakeMatchFromRawDescription($rawDesc);
		$isMatched = preg_match($matchDesc, $coefDesc, $coefMatches);
		$isMinedMatched = preg_match($matchDesc, $minedDesc, $minedDescMatches);
		
		if (!$isMatched || count($coefMatches) <= 1)
		{
			$this->ReportError("$abilityId: $rank: Failed to match raw description to coefficient description!");
			$this->ReportError("SkillDesc($isMinedMatched): $minedDesc");
			$this->ReportError(" CoefDesc($isMatched): $coefDesc");
			$this->ReportError("MatchDesc: $matchDesc");
			$this->ReportError("  RawDesc: $rawDesc");
			return false;
		}
		else if ($this->ONLY_DO_ABILITYID > 0)
		{
			$this->ReportError("$abilityId: $rank:");
			$this->ReportError("SkillDesc($isMinedMatched): $minedDesc");
			$this->ReportError(" CoefDesc($isMatched): $coefDesc");
			$this->ReportError("MatchDesc: $matchDesc");
			$this->ReportError("  RawDesc: $rawDesc");
		}
		
		$tooltipInfos = $this->CreateCoefTooltipInfo($abilityId, $rank, $coefMatches, $tooltipIndexMatches[1], $minedDescMatches);
		if (!$this->SaveTooltipInfos($abilityId, $tooltipInfos)) return false;
		
		return true;
	}
	
	
	public function UpdateAllSkillRawData()
	{
		global $ESO_RAWSKILL_DATA;
		require_once("esoRawSkillData{$this->TABLE_SUFFIX}.php");
		
		if (!$this->InitDatabaseWrite()) return false;
		if (!$this->LoadAllMinedSkills()) return false;
		
		if (!$this->CreateTable()) return false;
		if (!$this->ResetAllTooltipData()) return false;
		
		$successCount = 0;
		$errorCount = 0;
		
		$this->ReportError("Updating all skill tooltip data...");
		
		foreach ($this->minedSkills as $abilityId => $minedSkill)
		{
			if ($this->UpdateSkillRawData($abilityId))
				++$successCount;
			else
				++$errorCount;
		}
		
		$this->ReportError("Updated $successCount skills ({$this->updateDescCount} descriptions, {$this->updateNameCount} names, {$this->updateTooltipCount} tooltips) with $errorCount errors ({$this->coefErrorCount} coefficient errors).");
		
		return true;
	}
	
};

	// For testing only
//$test = new CEsoSkillTooltips("31");
//$test->UpdateAllSkills();