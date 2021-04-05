<?php

if (php_sapi_name() != "cli") die("Can only be run from command line!");

require("skillTooltips.class.php");

$skillTooltips = new CEsoSkillTooltips();
$skillTooltips->UpdateAllSkillRawData();

exit();


$TABLE_SUFFIX = "";
$ONLY_DO_SKILLID = -1;
$ONLY_DO_PLAYER = false;
$DO_COEF_UPDATE = false;
$DO_ALL_RANKS = true;
$MAX_COEF_ERROR = 2;	//Minmum coef error in % that generates an error message

$FIXED_COEF_FACTORS = array(
	16212 => array( 1 => 2.0 ),
	50325 => array( 2 => 1.0 ),
);

$RANK_FACTORS = array(
		1 => 1.00,
		2 => 1.01,
		3 => 1.02,
		4 => 1.03,
);

require("/home/uesp/secrets/esolog.secrets");
require("esoCommon.php");
require("esoRawSkillData.php");
require("esoSkillRankData.php");

$db = new mysqli($uespEsoLogWriteDBHost, $uespEsoLogWriteUser, $uespEsoLogWritePW, $uespEsoLogDatabase);
if ($db->connect_error) exit("Could not connect to mysql database!");

print("Parsing ESO raw skill data and merging into database for version '$TABLE_SUFFIX'...\n");
if ($ONLY_DO_PLAYER) print("Only parsing Player Skills!\n");
if ($DO_ALL_RANKS) print("Parsing all ranks of active skills!\n");
if ($DO_COEF_UPDATE) print("Writing valid coefficients to database\n");

$totalCount = 0;
$errorCount = 0;
$coefErrorCount = 0;
$tooltipMismatchCount = 0;
$errorSum = 0;
$errorSumCount = 0;
$maxError = 0;
$minBadR2 = 100;
$maxBadR2 = 0;
$unknownCoefTypes = array();


function GetCoefType($type1, $type2)
{
	if ($type1 == 25 && $type2 == 4)	return 0;		// Magicka
	if ($type1 == 4  && $type2 == 25)	return 0;
	if ($type1 == 25 && $type2 == 35)	return -56;		// Spell + Weapon Damage
	if ($type1 == 35 && $type2 == 4)	return -72;		//  Weapon Damage + Magicka?
	if ($type1 == 25 && $type2 == 0)	return 0;
	if ($type1 == 4 && $type2 == 0)		return 0;
	if ($type1 == 1 && $type2 == 0)		return -74;		// Attack Power
	if ($type1 == 2 && $type2 == 0)		return -74; 	// Weapon Power
	if ($type1 == 7 && $type2 == 0)		return -2;		// Health
	if ($type1 == 35 && $type2 == 29)	return 6;		// Stamina
	if ($type1 == 29 && $type2 == 35)	return 6;
	if ($type1 == 35 && $type2 == 0)	return 6;
	//if ($type1 == 29)	return 6;
	if ($type1 == 0 && $type2 == 0) 	return -1000;
	
	return -1;
}


function CreateTooltipsTable()
{
	global $db;
	global $TABLE_SUFFIX;
	
	$query = "CREATE TABLE IF NOT EXISTS skillTooltips$TABLE_SUFFIX (
					abilityId INTEGER NOT NULL,
					idx TINYINT NOT NULL,
					coefType TINYINT NOT NULL,
					rawType TINYINT NOT NULL,
					a float NOT NULL,
					b float NOT NULL,
					c float NOT NULL,
					r float NOT NULL,
					dmgType TINYINT NOT NULL,
					isDamage TINYINT(1) NOT NULL,
					isHeal TINYINT(1) NOT NULL,
					isDamageShield TINYINT(1) NOT NULL,
					isAOE TINYINT(1) NOT NULL,
					isDOT TINYINT(1) NOT NULL,
					PRIMARY KEY id (abilityId, idx)
			) ENGINE=MYISAM;";
	
	$result = $db->query($query);
	if ($result === false) exit("Error: Failed to create the skillTooltips$TABLE_SUFFIX table!\n$query\n");
	
	return true;
}


function LoadMinedSkill($abilityId)
{
	global $db;
	global $TABLE_SUFFIX;
	
	$query = "SELECT * FROM minedSkills$TABLE_SUFFIX WHERE id='$abilityId';";
	$result = $db->query($query);
	if ($result === false) exit("Error: Failed to load skill data from database!\n$query\n");
	
	$skillData = $result->fetch_assoc();
	return $skillData;
}



function UpdateSkill($abilityId, $rawSkillData, $minedAbilityId, $skillData, $rank)
{
	global $TABLE_SUFFIX;
	global $ONLY_DO_SKILLID;
	global $ONLY_DO_PLAYER;
	global $DO_COEF_UPDATE;
	global $DO_ALL_RANKS;
	global $MAX_COEF_ERROR;
	global $FIXED_COEF_FACTORS;
	global $RANK_FACTORS;
	global $ESO_RAWSKILL_DATA;
	
	global $totalCount;
	global $errorCount;
	global $coefErrorCount;
	global $tooltipMismatchCount;
	global $errorSum;
	global $errorSumCount;
	global $maxError;
	global $minBadR2;
	global $maxBadR2;
	global $unknownCoefTypes;
	global $db;
	
	++$totalCount;
	if ($totalCount % 100 == 0) print("\tUpdated $totalCount skills!\n");
	
	$rawTooltip = json_encode($rawSkillData['coef']);
	$rawCoef = "";
	
	$duration = $rawSkillData['duration']/1000;
	$tickLength = $rawSkillData['tick']/1000;
	$startTime = $rawSkillData['start']/1000;
	
	$safeDesc = $db->real_escape_string($rawSkillData['desc']);
	$safeTooltip = $db->real_escape_string($rawTooltip);
	$safeCoef = $db->real_escape_string($rawCoef);
	
	$query = "UPDATE minedSkills$TABLE_SUFFIX SET rawDescription='$safeDesc', rawTooltip='$safeTooltip', rawCoef='$safeCoef' WHERE id='$minedAbilityId';";
	$result = $db->query($query);
	if ($result === false) exit("Error: Failed to save skill data to database!\n$query\n");
	
	if ($rawSkillData['coef'] == null) return false;
	
	$skillDesc = $rawSkillData['desc'];
	$skillDesc = str_replace("<<AB_DURATION:17566))>>", "AB_DURATION", $skillDesc);
	$skillDesc = str_replace(")>>", ">>", $skillDesc);
	$skillDesc = str_replace("|cffffff", "", $skillDesc);
	$skillDesc = preg_replace("/\|c[a-f0-9]{6}/", "", $skillDesc);
	$skillDesc = str_replace("|r", "", $skillDesc);
	$skillDesc = str_replace("  ", " ", $skillDesc);
	$skillDesc = str_replace("  ", " ", $skillDesc);
	$skillDesc = str_replace("\x3F", "", $skillDesc);
	$skillDesc = str_replace("\xC2\xB7", "", $skillDesc);
	$skillDesc = str_replace("\xB7", "", $skillDesc);
	$skillDesc = preg_replace("/>>%,$/i", ">>%", $skillDesc);
	$skillDesc = preg_replace("/>>%$/i", ">>", $skillDesc);
	
	$minedDesc = $skillData['coefDescription'];
	$mechanic = $skillData['mechanic'];
	
	if ($minedDesc == "") 
	{
		//print("\t$abilityId: No coefficients in mined data!\n");
		return false;
	}
	
	$minedDesc = str_replace("|cffffff", "", $minedDesc);
	$minedDesc = preg_replace("/\|c[a-f0-9]{6}/", "", $minedDesc);
	$minedDesc = str_replace("|r", "", $minedDesc);
	$minedDesc = str_replace("  ", " ", $minedDesc);
	$minedDesc = str_replace("  ", " ", $minedDesc);
	$minedDesc = str_replace("\x3F", "", $minedDesc);
	$minedDesc = str_replace("\xC2\xB7", "", $minedDesc);
	$minedDesc = str_replace("\xB7", "", $minedDesc);
	$minedDesc = preg_replace("/WITH .* EQUIPPED\n/i", "", $minedDesc);
	
	$matchDesc = $skillDesc;
	$matchDesc = str_replace("(", "\(", $matchDesc);
	$matchDesc = str_replace(")", "\)", $matchDesc);
	$matchDesc = str_replace("+", "\+", $matchDesc);
	
	for ($i = 0; $i <= 10; ++$i)
	{
		$matchDesc = preg_replace("/<<$i>>/", "(.*)", $matchDesc);
	}
	
	$numberRegex = "/[0-9]+(?:\.[0-9]+)?/";
	$matchDesc = preg_replace($numberRegex, ".*", $matchDesc);
	
	$isMatched = preg_match("#$matchDesc#i", $minedDesc, $matches); 
	
	if (!isMatched || count($matches) <= 0)
	{
		print("----------------------------------------------------------------------------------------------------\n");
		print("\t\t$abilityId: Error: No match!\n");
		print("SkillDesc: $skillDesc\n");
		print("MinedDesc: $minedDesc\n");
		print("MatchDesc: $matchDesc\n");
		++$errorCount;
		return false;
	}
	else if ($ONLY_DO_SKILLID > 0)
	{
		print("----------------------------------------------------------------------------------------------------\n");
		print("SkillDesc: $skillDesc\n");
		print("MinedDesc: $minedDesc\n");
		print("MatchDesc: $matchDesc\n");
	}
	
	$indexMap = array();
	$indexReverseMap = array();
	$foundTooltips = 0;
	
	if ($ONLY_DO_SKILLID > 0) print("\t$abilityId: " . count($matches) . " matches!\n");
	
	$indexIsMatched = preg_match_all("/<<([0-9]+)>>/", $skillDesc, $tooltipIndexMatches);
	
	if (!$indexIsMatched)
	{
		print("\t$abilityId: Failed to match tooltip index in skilldesc!\n");
		return false;
	}
	
	for ($i = 1; $i < count($matches); ++$i)
	{
		$subMatch = $matches[$i];
		$isMatched = preg_match("/[$]([0-9]+)/", $subMatch);
		
		if ($isMatched)
		{
			if ($tooltipIndexMatches[1][$i-1] == null)
			{
				print("\t$abilityId: $rank: $i: No tooltip index found!\n");
				continue;
			}
			
			$tooltipIndex = $tooltipIndexMatches[1][$i-1];
			
			$ch = $subMatch[1];
			$coefIndex = ord($ch) - ord('1') + 1;
			
			$indexMap[$coefIndex] = $tooltipIndex;
			$indexReverseMap[$tooltipIndex] = $coefIndex;
			
			if ($ONLY_DO_SKILLID > 0) print("\t\t$abilityId: $rank: $i: $coefIndex => $tooltipIndex\n");
			++$foundTooltips;
		}
		else
		{
			if ($ONLY_DO_SKILLID > 0) print("\t\t$abilityId: $rank: $i: $subMatch No Index Match\n");
		}
	}
	
	if ($foundTooltips < $skillData['numCoefVars'])
	{
		print("\t\t$abilityId: $rank: Warning: Found fewer tooltips than in the skill data ($foundTooltips : {$skillData['numCoefVars']})\n");
		++$tooltipMismatchCount;
		print("SkillDesc: $skillDesc\n");
		print("MinedDesc: $minedDesc\n");
		print("MatchDesc: $matchDesc\n");
	}
	else if ($foundTooltips > $skillData['numCoefVars'])
	{
		print("\t\t$abilityId: $rank: Warning: Found more tooltips than in the skill data ($foundTooltips : {$skillData['numCoefVars']})\n");
		++$tooltipMismatchCount;
		print("SkillDesc: $skillDesc\n");
		print("MinedDesc: $minedDesc\n");
		print("MatchDesc: $matchDesc\n");
	}
	
	$rawCoefs = array();
	
	for ($i = 1; $i <= $skillData['numCoefVars']; ++$i)
	{
		$rawCoefs[$i - 1] = array();
		
		$t1 = $skillData["type$i"];
		$a1 = $skillData["a$i"];
		$b1 = $skillData["b$i"];
		$c1 = $skillData["c$i"];
		$r =  $skillData["R$i"];
		$a2 = -1;
		$b2 = -1;
		$c2 = 0;
		$origa2 = -1;
		$origb2 = -1;
		$origc2 = 0;
		
		$tooltipIndex = $indexMap[$i];
		
		if ($tooltipIndex == null) 
		{
			print("\t$abilityId $rank:: $i: No match for tooltip index found!\n");
			continue;
		};
		
		if ($ONLY_DO_SKILLID > 0) print("\t$abilityId: $rank: $i: Found tooltip index: $i => $tooltipIndex\n");
		
		$tooltipData = $rawSkillData['coef'][$tooltipIndex - 1];
		if ($tooltipData == null) continue;
		
		$tooltipType = $tooltipData['type'];
		$tooltipId = $tooltipData['id'];
		
		$tooltipSkillData = $ESO_RAWSKILL_DATA[$tooltipId];
		if ($tooltipSkillData == null) $tooltipSkillData = array();
		
		$rawCoefs[$i - 1]['type'] = $tooltipType;
		$rawCoefs[$i - 1]['id'] = $tooltipId;
		
		$duration = $tooltipSkillData['duration']/1000;
		$tickLength = $tooltipSkillData['tick']/1000;
		$startTime = $tooltipSkillData['start']/1000;
		$value1 = $tooltipSkillData['value1'];
		$value2 = $tooltipSkillData['value2'];
		$coefFactor = 1;
		
		if (($tooltipType == 49 || $tooltipType == 53) && $duration > 0)
		{
			if ($tickLength > 0)
			{
				$coefFactor = ($duration + $tickLength)/$tickLength;
			}
			else
			{
				$coefFactor = $duration;
			}
		}
		
		$fixedFactor = $FIXED_COEF_FACTORS[$abilityId];
		
		if ($fixedFactor) 
		{
			$fixedFactor = $fixedFactor[$i];
			if ($fixedFactor) 
			{
				$coefFactor = $fixedFactor;
				 print("\t$abilityId: $rank: $i: Using fixed factor of $fixedFactor!\n");
			}
		}
		
		$coefType1 = GetCoefType($tooltipData['type1'], $tooltipData['type2']);
		$coefType2 = GetCoefType($tooltipData['type3'], $tooltipData['type4']);
		if ($coefType1 == -1) { print("\t$abilityId: $rank: $i: Unknown coef type found: {$tooltipData['type1']}:{$tooltipData['type2']} \n"); $unknownCoefTypes[] = $abilityId; }
		if ($coefType2 == -1) { print("\t$abilityId: $rank: $i: Unknown coef type found: {$tooltipData['type3']}:{$tooltipData['type4']} \n"); $unknownCoefTypes[] = $abilityId; }
		
		$coefType = -1;
		
		if (($coefType1 == 0 && $coefType2 == 6) || ($coefType1 == 6 && $coefType2 == 0)) 
		{
			$coefType = 10;
		}
		else 
		{
			$coefType = $coefType1;
		}
		
		$isRankMod = $tooltipData['rankMod'];
		
		//if ($rank > 1 /* && !(($coefType == 10 && $mechanic != 10) || ($coefType == 10 && $mechanic != 10 && $i == 1))*/)
		if ($rank > 1 && $isRankMod > 0)
		{
			$coefFactor *= $RANK_FACTORS[$rank];
			if ($ONLY_DO_SKILLID > 0) print("\t$abilityId: $rank: $i: Using $rank factor {$RANK_FACTORS[$rank]} ($mechanic: $coefType : $coefType1 : $coefType2)!\n");
		}
		
		if ($tooltipData['type1'] > 0)
		{
			$rawCoefs[$i - 1]['type1'] = $tooltipData['type1'];
			$rawCoefs[$i - 1]['coef1'] = $tooltipData['coef1'] * $coefFactor;
			$origb2 = $tooltipData['coef1'];
			$b2 = $tooltipData['coef1'] * $coefFactor;
			
			if ($tooltipData['type1'] == 1)
			{
				$c2 = ceil(($value1 + $value2)/2);
				$a2 = $b2;
				$b2 = 0;
			}
			else if ($tooltipData['type1'] == 2)
			{
			 	$a2 = $b2;
			 	if ($value1) $a2 += $value1/100;
			 	$b2 = 0;
			}
		}
		
		if ($tooltipData['type2'] > 0)
		{
			$rawCoefs[$i - 1]['type2'] = $tooltipData['type2'];
			$rawCoefs[$i - 1]['coef2'] = $tooltipData['coef2'] * $coefFactor;
			$origa2 = $tooltipData['coef2'];
			$a2 = $tooltipData['coef2'] * $coefFactor;
		}
		
		if ($tooltipData['type3'] > 0)
		{
			$rawCoefs[$i - 1]['type3'] = $tooltipData['type3'];
			$rawCoefs[$i - 1]['coef3'] = $tooltipData['coef3'] * $coefFactor;
		}
		
		if ($tooltipData['type4'] > 0)
		{
			$rawCoefs[$i - 1]['type4'] = $tooltipData['type4'];
			$rawCoefs[$i - 1]['coef4'] = $tooltipData['coef4'] * $coefFactor;
		}
		
		if ($tooltipType == 92)
		{
			$a2 *= 0.1;
			$b2 *= 0.1;
		}
		
		if ($coefType != $t1 && (!$ONLY_DO_PLAYER || $skillData['isPlayer']))
		{
			//print("\t$abilityId: $i: Coefficient type mismatch ($t1 != $coefType)!\n");
		}
		
		if ($a2 == -1 || $b2 == -1)
		{
			if ($ONLY_DO_SKILLID > 0) print("\t$abilityId: $rank: $i: Missing tooltip coefficient! ({$tooltipData['type1']}: $a2 : $b2 : $c2)\n");
		}
		else
		{
			
			if ($a1 == 0 && $a2 == 0)
				$errora = abs(($a1 - $a2) * 100.0);
			else if ($a1 == 0)
 				$errora = abs(($a1 - $a2)/$a2 * 100.0);
			else
				$errora = abs(($a1 - $a2)/$a1 * 100.0);
			
			if ($b1 == 0 && $b2 == 0)
				$errorb = abs(($b1 - $b2) * 100.0);
			else if ($b1 == 0)
				$errorb = abs(($b1 - $b2)/$b2 * 100.0);
			else
				$errorb = abs(($b1 - $b2)/$b1 * 100.0);
			
			$errorc = 0;
			if ($c2 != 0) $errorc = abs(($c1 - $c2)/$c2 * 100.0);
			
			if (($errora >= $MAX_COEF_ERROR || $errorb >= $MAX_COEF_ERROR || $errorc >= $MAX_COEF_ERROR) && (!$ONLY_DO_PLAYER || $skillData['isPlayer']))
			{
				if ($r > 0.95)
				{
					print("\t$abilityId: $rank: $i: Large difference in coefficients found ($errora%, $errorb%) TooltipType = $tooltipType!\n");
					
					if ($c2) 
					{
						print("\t\t  Mined: $a1 A + $b1 B + $c1 C  (R=$r)\n");
						print("\t\tTooltip: $a2 A + $b2 B + $c2 C \n");
						print("\t\t OrigTT: $origa2 A + $origb2 B + $origc2 C\n");
					}
					else
					{
						print("\t\t  Mined: $a1 A + $b1 B    (R=$r)\n");
						print("\t\tTooltip: $a2 A + $b2 B\n");
						print("\t\t OrigTT: $origa2 A + $origb2 B\n");
					}
					
					print("\t\tDuration: $duration,  Start: $startTime,  Tick: $tickLength\n");
					++$coefErrorCount;
				}
				
				if ($r > 0 && $r < $minBadR2) $minBadR2 = $r;
				if ($r > 0 && $r > $maxBadR2) $maxBadR2 = $r;
			}
			else
			{
				if (!$ONLY_DO_PLAYER || $skillData['isPlayer'])
				{
					if ($errora > $maxError) $maxError = $errora;
					if ($errorb > $maxError) $maxError = $errorb;
					
					$errorSum += $errora;
					$errorSum += $errorb;
					$errorSumCount += 2;
					
					if ($errorc) 
					{
						if ($errorc > $maxError) $maxError = $errorc;
						$errorSum += $errorc;
						$errorSumCount += 1;
					}
				}
				
				if ($ONLY_DO_SKILLID > 0)
				{
					if ($c2) 
					{
						print("\t\t  Mined: $a1 A + $b1 B + $c1 C  (R=$r)\n");
						print("\t\tTooltip: $a2 A + $b2 B + $c2 C \n");
					}
					else
					{
						print("\t\t  Mined: $a1 A + $b1 B    (R=$r)\n");
						print("\t\tTooltip: $a2 A + $b2 B\n");
					}
				}
			}
		}
	}
	
	$rawCoef = json_encode($rawCoefs);
	$safeCoef = $db->real_escape_string($rawCoef);
	$query = "UPDATE minedSkills$TABLE_SUFFIX SET rawCoef='$safeCoef' WHERE id='$minedAbilityId';";
	$result = $db->query($query);
	if ($result === false) exit("Error: Failed to save skill data to database!\n$query\n");
	
	return true;
}


function UpdateSkills()
{
	global $ESO_RAWSKILL_DATA;
	global $DO_ALL_RANKS;
	global $ESO_BASESKILL_RANKDATA;
	global $ONLY_DO_SKILLID;
	
	global $errorSumCount;
	global $errorSum;
	global $totalCount;
	global $minBadR2;
	global $maxBadR2;
	global $avgError;
	global $maxError;
	global $errorCount;
	global $coefErrorCount;
	global $tooltipMismatchCount;
	
	foreach ($ESO_RAWSKILL_DATA as $abilityId => $rawSkillData)
	{
		if ($ONLY_DO_SKILLID > 0 && $abilityId != $ONLY_DO_SKILLID) continue;
		
		$skillData = LoadMinedSkill($abilityId);
		
		UpdateSkill($abilityId, $rawSkillData, $abilityId, $skillData, 1);
		
		if ($DO_ALL_RANKS && $skillData['isPlayer'] > 0 && $skillData['isPassive'] == 0) 
		{
			$rankData = $ESO_BASESKILL_RANKDATA[$abilityId];
			if ($rankData == null) continue;
			
			for ($rank = 2; $rank <= 4; ++$rank)
			{
				$rankAbilityId = $rankData[$rank];
				if ($rankAbilityId == null) continue;
				
				$skillData = LoadMinedSkill($rankAbilityId);
				//print("\t$rankAbilityId: $rank: Updating ranked ability for base $abilityId!\n");
				UpdateSkill($abilityId, $rawSkillData, $rankAbilityId, $skillData, $rank);
			}
		}
	}
	
	if ($errorSumCount > 0)
	{
		$avgError = round($errorSum / $errorSumCount, 2);
		print("Average Error ($errorSumCount coefs, bad R2 $minBadR2 - $maxBadR2): $avgError%    Max: $maxError%\n");
	}
	
	print("Updated a total of $totalCount skills with $errorCount errors, $coefErrorCount coef errors, and $tooltipMismatchCount tooltip count mismatches!\n");
}


UpdateSkills();






