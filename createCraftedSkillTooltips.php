<?php

if (php_sapi_name() != "cli") die("Can only be run from command line!");

require("/home/uesp/secrets/esolog.secrets");
require_once("esoCommon.php");

$TABLE_SUFFIX = "46pts";

$db = new mysqli($uespEsoLogWriteDBHost, $uespEsoLogWriteUser, $uespEsoLogWritePW, $uespEsoLogDatabase);
if ($db->connect_error) return $this->ReportError("ERROR: Could not connect to esolog database for writing!");

$query = "SELECT * FROM minedSkills$TABLE_SUFFIX WHERE id>50000000";
$result = $db->query($query);
if (!$result) die("Error: Failed to load crafted skill coefficient data!");

$skillCoef = [];

while ($row = $result->fetch_assoc())
{
	$id = $row['id'];
	$skillCoef[$id] = $row;
}

$insertCount = 0;
$count = count($skillCoef);
print("Loaded $count skill coefficient records from minedSkills$TABLE_SUFFIX!\n");

$query = "DELETE FROM skillTooltips$TABLE_SUFFIX WHERE abilityId>50000000;";
$result = $db->query($query);
if (!$result) die("Error: Failed to delete skill tooltips for ability $id!");

foreach ($skillCoef as $id => $skill)
{
	$baseId = intval($id) % 50000000;
	$craftedId = $baseId % 1000;
	$scriptId = intval($baseId / 1000) % 1000;
	$classId = intval($baseId / 1000000);
	
	print("Parsing Skill $craftedId:$scriptId:$classId ($id)...\n");
	
	$numVars = intval($skill['numCoefVars']);
	$rawBaseDesc = FormatRemoveEsoItemDescriptionText($skill['description']);
	$rawDesc = FormatRemoveEsoItemDescriptionText($skill['coefDescription']);
	$skillRawDesc = $skill['coefDescription'];
	
	if ($numVars <= 0) continue;
	if ($rawDesc == "") continue;
	
	//print("\t$craftedId - $scriptId : $rawDesc\n");
	
	$origScriptDesc = "";
	$origScriptName = "";
	$origScriptId = -1;
	$query = "SELECT * FROM craftedScriptDescriptions$TABLE_SUFFIX WHERE craftedAbilityId=$craftedId AND scriptId=$scriptId AND classId=$classId;";
	$result = $db->query($query);
	
	if ($result && $result->num_rows > 0)
	{
		$row = $result->fetch_assoc();
		$origScriptDesc = $row["description"];
		$origScriptName = $row["name"];
		$origScriptId = $row["abilityId"];
		print("\tFound ability $origScriptId\n");
		
		$skill['description'] = $origScriptDesc;
		$rawBaseDesc = FormatRemoveEsoItemDescriptionText($origScriptDesc);
	}
	else
	{
		print("No crafted script description found for $baseId = $craftedId:$scriptId:$classId!\n" . $db->error . "\n");
		die();
	}
	
	$rawScriptDesc = FormatRemoveEsoItemDescriptionText($origScriptDesc);
	
	
	for ($i = 1; $i <= $numVars; ++$i)
	{
		$coefName = '\$' . $i;
		
		$skillRawDesc = preg_replace("/\|cffffff$coefName\|r/", "<<$i>>", $skillRawDesc);
		$skillRawDesc = preg_replace("/$coefName/", "<<$i>>", $skillRawDesc);
		
		$regex = preg_replace("/$coefName/", "(.+)", $rawDesc);
		$regex = preg_replace("/\\$[0-9]+/", ".+", $regex);
		//$regex = preg_replace("/\./", "\.", $regex);
		$tooltipValue = ""; 
		
		if (preg_match("/$regex/", $rawBaseDesc, $matches))
		{
			$tooltipValue = $matches[1];
			//print("\t$i: Value {$matches[1]} in $rawBaseDesc\n");
		}
		else
		{
			print("\t$i: ERROR: No value match!\n\tBase Desc: $rawBaseDesc\n\tRegex: $regex\n");
			//die();
		}
		
		$isHeal = 0;
		$isDamage = 0;
		$isShield = 0;
		$isHealAbsorp = 0;
		$damageType = "";
		$isAoe = 0;
		$isDot = 0;
		$coefType = $skill["type$i"];
		$a = $skill["a$i"];
		$b = $skill["b$i"];
		$c = $skill["c$i"];
		$r = $skill["R$i"];
		$rawType = 0;
		$duration = "";
		$tickCount = "";
		$rawType = 0;
		
		if (preg_match("/ $coefName (\w+) Damage to an enemy/i", $rawDesc, $matches))
		{
			$damageType = $matches[1];
			$isDamage = 1;
			$rawType = 18;
		}
		else if (preg_match("/ $coefName (\w+) Damage to enemies/i", $rawDesc, $matches))
		{
			$damageType = $matches[1];
			$isDamage = 1;
			$isAoe = 1;
			$rawType = 18;
		}
		else if (preg_match("/ $coefName (\w+) Damage to all enemies/i", $rawDesc, $matches))
		{
			$damageType = $matches[1];
			$isDamage = 1;
			$isAoe = 1;
			$rawType = 18;
		}
		else if (preg_match("/ $coefName (\w+) Damage over (\d+) seconds to the enemy/i", $rawDesc, $matches))
		{
			$damageType = $matches[1];
			$duration = $matches[2];
			$isDamage = 1;
			$isDot = 1;
			$rawType = 18;
		}
		else if (preg_match("/ $coefName (\w+) Damage over (\d+) seconds to enemies/i", $rawDesc, $matches))
		{
			$damageType = $matches[1];
			$duration = $matches[2];
			$tickCount = "1";
			$isDamage = 1; //?
			$isDot = 1;
			$isAoe = 1;
			$rawType = 49;
			$rawType = 18;
		}
		else if (preg_match("/ $coefName (\w+) Damage every second/i", $rawDesc, $matches))
		{
			$tickCount = "1";
			$damageType = $matches[1];
			$isDamage = 1;
			$isDot = 1;
			$rawType = 49;
			$rawType = 18;
		}
		else if (preg_match("/ $coefName (\w+) Damage to attackers/i", $rawDesc, $matches))
		{
			$damageType = $matches[1];
			$isDamage = 1;
			$rawType = 18;
		}
		else if (preg_match("/ $coefName (\w+) Damage and/i", $rawDesc, $matches))
		{
			$damageType = $matches[1];
			$isDamage = 1;
			$rawType = 18;
		}
		else if (preg_match("/ $coefName (\w+) Damage\./i", $rawDesc, $matches))
		{
			$damageType = $matches[1];
			$isDamage = 1;
			$rawType = 18;
		}
		else if (preg_match("/ $coefName Healing Absorption/i", $rawDesc, $matches))
		{
			$isHealAbsorp = 1;
			$rawType = -1;
			$rawType = 16;
		}
		else if (preg_match("/Heals you and your allies for $coefName Health/i", $rawDesc, $matches))
		{
			$isHeal = 1;
			$isAoe = 1;
			$rawType = 16;
		}
		else if (preg_match("/Heals you or an ally for $coefName Health/i", $rawDesc, $matches))
		{
			$isHeal = 1;
			$rawType = 16;
		}
		else if (preg_match("/Heals the ally and other allies in the link for $coefName Health/i", $rawDesc, $matches))
		{
			$isHeal = 1;
			$isAoe = 1;
			$rawType = 16;
		}
		else if (preg_match("/Heals the ally and other allies when they leave the link for $coefName Health/i", $rawDesc, $matches))
		{
			$isHeal = 1;
			$isAoe = 1;
			$rawType = 16;
		}
		else if (preg_match("/ heal a nearby ally for $coefName Health/i", $rawDesc, $matches))
		{
			$isHeal = 1;
			$rawType = 16;
		}
		else if (preg_match("/Heals for $coefName Health/i", $rawDesc, $matches))
		{
			$isHeal = 1;
			$rawType = 16;
		}
		else if (preg_match("/Heals you for $coefName Health/i", $rawDesc, $matches))
		{
			$isHeal = 1;
			$rawType = 16;
		}
		else if (preg_match("/absorbs $coefName damage/i", $rawDesc, $matches))
		{
			$isShield = 1;
			$rawType = 16;
		}
		else if (preg_match("/Grants a damage shield every (\d+) seconds for (\d+) seconds that absorbs up to $coefName damage/i", $rawDesc, $matches))
		{
			$isShield = 1;
			$tickCount = $matches[1];
			$duration = $matches[2];
			$rawType = 16;
		}
		else if (preg_match("/Create a Crux/i", $rawDesc, $matches))
		{
			continue;
		}
		else
		{
			print("\tWARNING: No match for description: $rawDesc!\n");
			continue;
		}
		
		if ($isDamage)
		{
			$dmgType = GetEsoDamageTypeFromText($damageType);
			if ($dmgType < 0) $dmgType = 2;	//Physical
		}
		
		$setValues = [
				"abilityId" => $id,
				"idx" => $i,
				"origAbilityId" => $id,
				"coefType" => $coefType,
				"rawType" => $rawType,
				"value" => $db->real_escape_string($tooltipValue),
				"rawValue1" => -1,
				"rawValue2" => -1,
				"duration" => $duration > 0 ? $duration * 1000 : -1,
				"startTime" => -1,
				"tickTime" => $tickCount > 0 ? $tickCount * 1000 : -1,
				"cooldown" => -1,
				"a" => $a,
				"b" => $b,
				"c" => $c,
				"R" => $r,
				"dmgType" => $dmgType,
				"isDmg" => $isDamage,
				"isHeal" => $isHeal,
				"isDmgShield" => $isShield,
				"isAOE" => $isAoe,
				"isDOT" => $isDot,
				"isFlameAOE" => ($isAoe && $dmgType == 3) ? 1 : 0,
				"isElfBane" => 0,
				"isPlayer" => 1, //?
				"isMelee" => 0, //?
				"hasRankMod" => 0,
				"usesManualCoef" => 1,
		];
		
		$cols = [];
		$values = [];
		
		foreach ($setValues as $col => $value)
		{
			$cols[] = $col;
			$values[] = "'$value'";
		}
		
		$cols = implode(",", $cols);
		$values = implode(",", $values);
		
		$query = "INSERT INTO skillTooltips$TABLE_SUFFIX($cols) VALUES($values);";
		$result = $db->query($query);
		//print($query . "\n" . $db->error);
		
		if (!$result) 
		{
			print("Error: Failed to add new skill tooltips for ability $id!\n $query\n".$db->error);
			die();
		}
		
		$insertCount++;
	}
	
	$safeRawDesc = $db->real_escape_string($skillRawDesc);
	$safeDesc = $db->real_escape_string($origScriptDesc);
	$safeName = $db->real_escape_string($origScriptName);
	$indexName = strtolower($safeName);
	
	$query = "UPDATE minedSkills$TABLE_SUFFIX SET name='$safeName', indexName='$indexName', displayId='$origScriptId', description='$safeDesc', rawDescription='$safeRawDesc', isCrafted='1', craftedId='$craftedId' WHERE id=$id;";
	//print($query . "\n");
	$result = $db->query($query);
	
	if (!$result) 
	{
		print("Error: Failed to add update skill raw description for ability $id!\n $query\n".$db->error);
		die();
	}
	
	//print("\t:$skillRawDesc\n");
	
	//Deals $1 Physical Damage to an enemy and bounces up to 2 times to random nearby enemies, dealing $2 Physical Damage and $3 Physical Damage.
	//Enchant your closest pet for 5 seconds to heal a nearby ally for $1 Health each time it deals damage, up to once a second. If you do not have a pet, you deal $2 Shock Damage to enemies within 8 meters of you.
	//Deals $1 Physical Damage over 10 seconds to the enemy. This effect can trigger your weapon enchantment
}

print("Finished! Inserted $insertCount skillTooltip records\n");