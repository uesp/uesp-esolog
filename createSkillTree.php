<?php

$TABLE_SUFFIX = "13pts";
$PRINT_TABLE = false;

if (php_sapi_name() != "cli") die("Can only be run from command line!");
print("Creating skill tree from mined skill data...\n");

require("/home/uesp/secrets/esolog.secrets");


function GetSkillTypeText ($value)
{
	static $VALUES = array(
			-1 => "",
			0 => "",
			1 => "Class",
			2 => "Weapon",
			3 => "Armor",
			4 => "World",
			5 => "Guild",
			6 => "Alliance War",
			7 => "Racial",
			8 => "Craft",
			9 => "Champion",
	);

	$key = (int) $value;

	if (array_key_exists($key, $VALUES)) return $VALUES[$key];
	return "Unknown ($key)";
}


function GetCombatMechanicText ($value)
{
	static $VALUES = array(
			-2 => "Health",
			-1 => "Invalid",
			0 => "Magicka",
			1 => "Werewolf",
			4 => "Power",
			6 => "Stamina",
			7 => "Momentum",
			9 => "Finesse",
			10 => "Ultimate",
			11 => "Mount Stamina",
			12 => "Health Bonus",
	);

	$key = (int) $value;

	if (array_key_exists($key, $VALUES)) return $VALUES[$key];
	return "Unknown ($key)";
}


$db = new mysqli($uespEsoLogWriteDBHost, $uespEsoLogWriteUser, $uespEsoLogWritePW, $uespEsoLogDatabase);
if ($db->connect_error) exit("Could not connect to mysql database!");

$query = "CREATE TABLE IF NOT EXISTS skillTree".$TABLE_SUFFIX."(
			id BIGINT NOT NULL AUTO_INCREMENT PRIMARY KEY,
			abilityId BIGINT NOT NULL,
			skillTypeName TINYTEXT NOT NULL,
			learnedLevel INTEGER NOT NULL DEFAULT -1,
			maxRank TINYINT NOT NULL DEFAULT -1,
			rank INTEGER NOT NULL DEFAULT -1,
			baseName TINYTEXT NOT NULL,
			name TINYTEXT NOT NULL,
			description TEXT NOT NULL,
			type TINYTEXT NOT NULL,
			cost TINYTEXT NOT NULL,
			icon TINYTEXT NOT NULL,
			skillIndex TINYINT NOT NULL DEFAULT -1,
			INDEX index_abilityId(abilityId),
			INDEX index_skillTypeName(skillTypeName(20)),
			INDEX index_type(type(8))
		);";

$result = $db->query($query);
if (!$result) exit("ERROR: Database query error creating table!\n" . $db->error);

$query = "DELETE FROM skillTree".$TABLE_SUFFIX.";";
$result = $db->query($query);
if (!$result) exit("ERROR: Database query error (clearing skill tree)!\n" . $db->error);

$query = "SELECT * from minedSkills".$TABLE_SUFFIX." WHERE nextSkill >= 0 AND isPassive=0 AND isPlayer=1;";
$skillResult = $db->query($query);
if (!$skillResult) exit("ERROR: Database query error (finding skill lines)!\n" . $db->error);
$skillResult->data_seek(0);

$skills = array();
$skillRoots = array();
$count = 0;

	/* Load all skills with a progression */
while (($skill = $skillResult->fetch_assoc()))
{
	$id = $skill['id'];
	$skill['maxLevel'] = 4;
	$skills[$id] = $skill;
	$count++;
}

print("\tFound $count skills with a skill progression.\n");
$skillTree = array();

	/* Find the root skills */
foreach ($skills as $id => $skill)
{
	if ($skill['nextSkill'] > 0 && $skill['prevSkill'] <= 0)
	{
		$skillTree[$id] = array();
		$skillTree[$id][1] = $id;
		$skillTree[$id][2] = $skill['nextSkill'];
	}

}

print("\tFound ".count($skillTree)." root skills\n");

	/* Follow the skill tree to its end one level at a time*/
for ($skillIndex = 2; $skillIndex <= 12; $skillIndex++)
{
	foreach($skillTree as $id => $skillLine)
	{
		$lastSkillId = $skillLine[$skillIndex];
		$nextSkill  = $skills[$lastSkillId]['nextSkill'];
		$nextSkill2 = $skills[$lastSkillId]['nextSkill2'];
		
		if ($nextSkill  > 0) $skillTree[$id][$skillIndex + 1] = $nextSkill;
		if ($nextSkill2 > 0) $skillTree[$id][$skillIndex + 5] = $nextSkill2;
	}
}

$skillRootData = array();

	/* Find the type/line/race/class for each skill line */
foreach($skillTree as $id => $skillTreeLine)
{
	$skillRootData[$id] = array();
	
	foreach ($skillTreeLine as $index => $skillLineId)
	{
		$skill = $skills[$skillLineId];
		
		if ($skill['skillType'] >   0) $skillRootData[$id]['skillType'] = $skill['skillType'];
		if ($skill['learnedLevel'] >0) $skillRootData[$id]['learnedLevel'] = $skill['learnedLevel'];
		if ($skill['skillIndex']  > 0) $skillRootData[$id]['skillIndex'] = $skill['skillIndex'];
		if ($skill['skillLine'] != "") $skillRootData[$id]['skillLine'] = $skill['skillLine'];
		if ($skill['classType'] != "") $skillRootData[$id]['classType'] = $skill['classType'];
		if ($skill['raceType']  != "") $skillRootData[$id]['raceType']  = $skill['raceType'];
	}
}

	/* Propagate the  type/line/race/class throughout each skill line */
foreach($skillTree as $id => $skillTreeLine)
{
	foreach ($skillTreeLine as $index => $skillLineId)
	{
		$skills[$skillLineId]['skillType']    = $skillRootData[$id]['skillType'];
		$skills[$skillLineId]['learnedLevel'] = $skillRootData[$id]['learnedLevel'];
		$skills[$skillLineId]['skillIndex']   = $skillRootData[$id]['skillIndex'];
		$skills[$skillLineId]['skillLine']    = $skillRootData[$id]['skillLine'];
		$skills[$skillLineId]['classType']    = $skillRootData[$id]['classType'];
		$skills[$skillLineId]['raceType']     = $skillRootData[$id]['raceType'];
	}
}

	/* Print the basic skill tree */
if ($PRINT_TABLE) 
{
	foreach($skillTree as $id => $skillTreeLine)
	{
		$skillId1 = $skillTreeLine[1];
		$skillId2 = $skillTreeLine[5];
		$skillId3 = $skillTreeLine[9];
		$line = $skills[$skillId1]['skillLine'];
		$type = $skills[$skillId1]['skillType'];
		$class = $skills[$skillId1]['classType'];
		$race = $skills[$skillId1]['raceType'];
		$skillIndex = $skills[$skillId1]['skillIndex'];
		$name1 = $skills[$skillId1]['name'];
		$name2 = $skills[$skillId2]['name'];
		$name3 = $skills[$skillId3]['name'];
		
		$desc1 = $skills[$skillTreeLine[1]]['description'];
		$desc2 = $skills[$skillTreeLine[2]]['description'];
		$desc3 = $skills[$skillTreeLine[3]]['description'];
		$desc4 = $skills[$skillTreeLine[4]]['description'];
		
		print("\t$name1: $type $line $class $race $skillIndex\n");
		//print("\t\t Rank 1: $desc1\n");
		//print("\t\t Rank 2: $desc2\n");
		//print("\t\t Rank 3: $desc3\n");
		//print("\t\t Rank 4: $desc4\n");
		
		$desc1 = $skills[$skillTreeLine[5]]['description'];
		$desc2 = $skills[$skillTreeLine[6]]['description'];
		$desc3 = $skills[$skillTreeLine[7]]['description'];
		$desc4 = $skills[$skillTreeLine[8]]['description'];
		
		print("\t\t$name2\n");
		//print("\t\t Rank 1: $desc1\n");
		//print("\t\t Rank 2: $desc2\n");
		//print("\t\t Rank 3: $desc3\n");
		//print("\t\t Rank 4: $desc4\n");
		
		$desc1 = $skills[$skillTreeLine[9]]['description'];
		$desc2 = $skills[$skillTreeLine[10]]['description'];
		$desc3 = $skills[$skillTreeLine[11]]['description'];
		$desc4 = $skills[$skillTreeLine[12]]['description'];
		
		print("\t\t$name3\n");
		//print("\t\t Rank 1: $desc1\n");
		//print("\t\t Rank 2: $desc2\n");
		//print("\t\t Rank 3: $desc3\n");
		//print("\t\t Rank 4: $desc4\n");
	}
}

	/* Update the skill effect lines */
foreach($skillTree as $id => $skillTreeLine)
{
	if ($skillTreeLine[5] == null || $skillTreeLine[9] == null) continue;
	
	$skillLineId5 = $skillTreeLine[5];
	$skillLineId9 = $skillTreeLine[9];
	if ($skills[$skillLineId5] == null || $skills[$skillLineId9] == null) continue;
	
	$effectLine1 = $skills[$skillLineId5]['effectLines'];
	$effectLine2 = $skills[$skillLineId9]['effectLines'];
	if ($effectLine1 == "" && $effectLine2 == "") continue;
	
	for ($index = 1; $index <= 12; $index++)
	{
		$skillLineId = $skillTreeLine[$index];
		$skill = $skills[$skillLineId];
		$rank = $index;

		if ($rank > 5 && $rank < 9)
		{
			//print("\tSetting effect line for $id morph 1\n");
			$skills[$skillLineId]['effectLines'] = $effectLine1;
		}
		elseif ($rank > 9 && $rank < 13)
		{
			//print("\tSetting effect line for $id morph 2\n");
			$skills[$skillLineId]['effectLines'] = $effectLine2;
		}
	}
}

	/* Update the skills */
print("Updating skill data...\n");

foreach($skills as $id => $skill)
{
	$classType = $db->real_escape_string($skill['classType']);
	$raceType = $db->real_escape_string($skill['raceType']);
	$skillType = $db->real_escape_string($skill['skillType']);
	$skillLine = $db->real_escape_string($skill['skillLine']);
	$learnedLevel = $db->real_escape_string($skill['learnedLevel']);
	$skillIndex = $db->real_escape_string($skill['skillIndex']);
	$effectLines = $db->real_escape_string($skill['effectLines']);
	
	$query = "UPDATE minedSkills$TABLE_SUFFIX SET skillType=\"$skillType\", raceType=\"$raceType\", classType=\"$classType\", skillLine=\"$skillLine\", learnedLevel=\"$learnedLevel\", skillIndex=\"$skillIndex\", effectLines=\"$effectLines\"  WHERE id=$id;";
	$result = $db->query($query);
	if (!$result) exit("ERROR: Database query error updating skills table!\n" . $db->error . "\n" . $query);
}

	/* Save the skill tree */
print("Creating skill tree...\n");

foreach($skillTree as $id => $skillTreeLine)
{
	$skill = $skills[$id];
	
	if ($skill['skillType'] == 1)
	{
		$skillTypeName = $skill['classType'] . "::" . $skill['skillLine'];
	}
	elseif ($skill['skillType'] == 7)
	{
		$skillTypeName = "Racial::" . $skill['skillLine'];
	}
	else
	{
		$skillTypeName = GetSkillTypeText($skill['skillType']) . "::" . $skill['skillLine'];
	}
	
	$rootSkill = $skills[$skillTreeLine[1]];
	$skillTypeName = $db->real_escape_string($skillTypeName);
	$baseName = $db->real_escape_string($rootSkill['name']);
	 
	$type = "Active";
	
	if ($rootSkill['mechanic']  == 10) $type = "Ultimate";
	if ($rootSkill['isPassive'] ==  1) $type = "Passive";
	
	for($index = 1; $index <= 12; $index++)
	{
		$skillLineId = $skillTreeLine[$index];
		$thisSkill = &$skills[$skillLineId];
		$name = $db->real_escape_string($thisSkill['name']);
		$desc = $db->real_escape_string($thisSkill['description']);
		$cost = "" . $thisSkill['cost'] . " " . GetCombatMechanicText($thisSkill['mechanic']);
		$icon = $db->real_escape_string($thisSkill['texture']);
		$learnedLevel = $thisSkill['learnedLevel'];
		$abilityIndex = $thisSkill['skillIndex'];
		$maxLevel = $thisSkill['maxLevel'];
		
		$query = "INSERT INTO skillTree$TABLE_SUFFIX(abilityId,skillTypeName,rank,baseName,name,description,type,cost,icon,learnedLevel,skillIndex,maxRank) VALUES('$skillLineId','$skillTypeName','$index',\"$baseName\",\"$name\",\"$desc\",'$type','$cost',\"$icon\", \"$learnedLevel\",\"$abilityIndex\", \"$maxLevel\")";
		$result = $db->query($query);
		if (!$result) exit("ERROR: Database query error inserting into skillTree database!\n" . $db->error . "\n" . $query);
		unset($thisSkill);
	}
}

	/* Create skill passives */
$query = "SELECT * FROM minedSkills$TABLE_SUFFIX WHERE isPassive=1 AND isPlayer=1;";
$passiveResult = $db->query($query);
if (!$passiveResult) exit("ERROR: Database query error finding passive skills!\n" . $db->error . "\n" . $query);

$passiveResult->data_seek(0);
$passiveSkills = array();
$passiveIds = array();
$passiveMaxLevel = array();
$count = 0;
$type = "Passive";
$skillTypeName = "";
$index = 0;

print("Loading passives...\n");

	/* Load all passives */
while (($passive = $passiveResult->fetch_assoc()))
{
	$id = $passive['id'];
	$passive['name'] = preg_replace("#(.*) [IV]+$#", "$1", $passive['name']);
	$passive['baseName'] = preg_replace("#(.*) [IV]+$#", "$1", $passive['baseName']);
	$passive['maxLevel'] = 1;
	
	$passiveSkills[] = $passive;
	$passiveIds[$id] = &$passiveSkills[count($passiveSkills) - 1];
	$passiveMaxLevel[$id] = 1;
	$count++;
}

print("Loaded ".count($passiveIds)." passives!\n");
print("Updating next/prev pointers in passive data...\n");

	/* Update next/prev IDs and set skillTypeName */
foreach ($passiveSkills as $index => $passive)
{
	$id = $passive['id'];
	$nextSkill = $passive['nextSkill'];
	$prevSkill = $passive['prevSkill'];
	
	$hasPrevSkill = array_key_exists($prevSkill, $passiveIds);
	$hasNextSkill = array_key_exists($nextSkill, $passiveIds);
		
	if ($hasPrevSkill && $hasNextSkill)
	{
		$passiveIds[$nextSkill]['prevSkill'] = $id;
		$passiveIds[$prevSkill]['nextSkill'] = $id;
	}
	else if ($hasPrevSkill)
	{
		$passiveIds[$prevSkill]['nextSkill'] = $id;
	}
	else if ($hasNextSkill)
	{
		$passiveIds[$nextSkill]['prevSkill'] = $id;
	}
		
	if ($passive['skillType'] == 1)
	{
		$skillTypeName = $passive['classType'] . "::" . $passive['skillLine'];
	}
	elseif ($passive['skillType'] == 7)
	{
		$skillTypeName = "Racial::" . $passive['skillLine'];
	}
	else
	{
		$skillTypeName = GetSkillTypeText($passive['skillType']) . "::" . $passive['skillLine'];
	}

	$passiveSkills[$index]['skillTypeName'] = $skillTypeName;
}

	/* Count maxLevels */
print("Counting passive maxLevels...\n");

foreach ($passiveSkills as $passive)
{
	$id = $passive['id'];
	$nextSkillId = $passive['nextSkill'];
	$prevSkillId = $passive['prevSkill'];
	
	if ($nextSkillId <= 0 || $prevSkillId > 0) continue;
	if (!array_key_exists($nextSkillId, $passiveIds)) continue;
	$maxLevelCount = 1;
	$nextSkill = $passiveIds[$nextSkillId];
	
	while ($nextSkill != null)
	{
		$maxLevelCount = $maxLevelCount + 1;
		$nextSkillId = $nextSkill['nextSkill'];
		if (!array_key_exists($nextSkillId, $passiveIds)) break;
		$nextSkill = $passiveIds[$nextSkillId];
	
		if ($maxLevelCount > 20) break;
	}
	
	$passiveMaxLevel[$id] = $maxLevelCount;
	//print("\tMaxLevel for $id is $maxLevelCount\n");
}

	/* Propagate maxLevels */
print("Propagating passive maxLevels...\n");

foreach ($passiveSkills as $index => $passive)
{
	$id = $passive['id'];
	$nextSkillId = $passive['nextSkill'];
	$prevSkillId = $passive['prevSkill'];
		
	if ($nextSkillId <= 0 || $prevSkillId > 0) continue;
	if (!array_key_exists($nextSkillId, $passiveIds)) continue;
	$nextSkill = $passiveIds[$nextSkillId];
	$maxLevelCount = 1;
	
	$maxLevel = $passiveMaxLevel[$id];
	$passiveIds[$id]['maxLevel'] = $maxLevel;
		
	while ($nextSkill != null)
	{
		$maxLevelCount = $maxLevelCount + 1;
		
		$passiveIds[$nextSkillId]['maxLevel'] = $maxLevel;
		$nextSkillId = $nextSkill['nextSkill'];
		if (!array_key_exists($nextSkillId, $passiveIds)) break;
		$nextSkill = $passiveIds[$nextSkillId];
		
		if ($maxLevelCount > 20) break;
	}
}

	/* Handle abilities in duplicate skill lines */
$DUPLICATE_SKILLS = array(
	/*
		array(		// Robust
				"id" => array(36064, 45297, 45298),
				"race1" => "Nord",
				"race2" => "Orc",
				"index1" => 2,
				"index2" => 3,
		), //*/	
		array(		// Magnus
				"id" => array(35995, 45259, 45260),
				"race1" => "Breton",
				"race2" => "High Elf",
				"index1" => 2,
				"index2" => 3,
		),
		array(		// Steathly
				"id" => array(36022, 45295, 45296),
				"race1" => "Wood Elf",
				"race2" => "Khajiit",
				"index1" => 4,
				"index2" => 3,
		),
		/*
		array(		// Shield Affinity
				"id" => array(36312),
				"race1" => "Imperial",
				"race2" => "Redguard",
				"index1" => 1,
				"index2" => 1,
		), //*/
		array(		// Conditioning
				"id" => array(36153, 45279, 45280),
				"race1" => "Imperial",
				"race2" => "Redguard",
				"index1" => 3,
				"index2" => 3,
		),
);

$newPassives = array();

foreach ($DUPLICATE_SKILLS as $dupSkill)
{
	$ids = $dupSkill['id'];
	$race1 = $dupSkill['race1'];
	$race2 = $dupSkill['race2'];
	$index1 = $dupSkill['index1'];
	$index2 = $dupSkill['index2'];
	
	foreach ($ids as $id)
	{
		$passive = $passiveIds[$id];
		
		if ($passive == null) 
		{
			print("\tSkill $id not found!\n");
			continue;
		}
		
		if ($passive['raceType'] != $race1)
		{
			$newPassive = $passive;
			$newPassive['raceType'] = $race1;
			$newPassive['skillLine'] = $race1 . " Skills";
			$newPassive['skillTypeName'] = "Racial::" . $newPassive['skillLine'];
			$newPassive['skillIndex'] = $index1;
			$newPassives[] = $newPassive;
			print("\tAdded skill $id for $race1\n");
		}
		
		if ($passive['raceType'] != $race2)
		{
			$newPassive = $passive;
			$newPassive['raceType'] = $race2;
			$newPassive['skillLine'] = $race2 . " Skills";
			$newPassive['skillTypeName'] = "Racial::" . $newPassive['skillLine'];
			$newPassive['skillIndex'] = $index2;
			$newPassives[] = $newPassive;
			print("\tAdded skill $id for $race2\n");
		}
	}
}

unset($passive);
$passiveSkills = array_merge($passiveSkills, $newPassives);


function ComparePassives($a, $b)
{
	//$compare = strcmp($a['skillTypeName'], $b['skillTypeName']);
	$compare = 0;
	
	if ($compare == 0)
	{
		$compare = strcmp($a['name'], $b['name']);
		
		if ($compare == 0)
		{
			$compare = $a['rank'] - $b['rank'];
		}
	}
	
	return $compare;
}

	/* Sort passives */
print("Sorting passive data...\n");
usort($passiveSkills, "ComparePassives");

print("Creating passive skill tree and saving data...\n");

	/* Create tree for passives */
foreach ($passiveSkills as $passive)
{	
	$id = $passive['id'];
	$name = $db->real_escape_string($passive['name']);
	$baseName = $name;
	$desc = $db->real_escape_string($passive['description']);
	$icon = $db->real_escape_string($passive['texture']);
	$rank = $passive['rank'];
	$learnedLevel = $passive['learnedLevel'];
	$abilityIndex = $passive['skillIndex'];
	$maxLevel = $passive['maxLevel'];
	
	if ($learnedLevel < 0) $learnedLevel = 1;
	
	$skillTypeName = $db->real_escape_string($passive['skillTypeName']);
	
	$query = "INSERT INTO skillTree$TABLE_SUFFIX(abilityId,skillTypeName,rank,baseName,name,description,type,cost,icon,learnedLevel,skillIndex,maxRank) VALUES('$id','$skillTypeName','$rank',\"$baseName\",\"$name\",\"$desc\",'$type','None',\"$icon\", \"$learnedLevel\", \"$abilityIndex\", \"$maxLevel\")";
	$result = $db->query($query);
	if (!$result) exit("ERROR: Database query error inserting into skillTree table!\n" . $db->error . "\n" . $query);
	
	$nextSkill = $passive['nextSkill'];
	$prevSkill = $passive['prevSkill'];
	
	$query = "UPDATE minedSkills$TABLE_SUFFIX SET nextSkill=$nextSkill, prevSkill=$prevSkill WHERE id=$id;";
	$result = $db->query($query);
	if (!$result) exit("ERROR: Database query error updating minedSkill table!\n" . $db->error . "\n" . $query);
}

print("\tFound $count passive player skills.\n");

