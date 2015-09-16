<?php

$TABLE_SUFFIX = "";

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



$db = new mysqli($uespEsoLogWriteDBHost, $uespEsoLogWriteUser, $uespEsoLogWritePW, $uespEsoLogDatabase);
if ($db->connect_error) exit("Could not connect to mysql database!");

$query = "CREATE TABLE IF NOT EXISTS skillTree".$TABLE_SUFFIX."(
			id BIGINT NOT NULL AUTO_INCREMENT PRIMARY KEY,
			abilityId BIGINT NOT NULL,
			skillTypeName TINYTEXT NOT NULL,
			rank INTEGER NOT NULL DEFAULT -1,
			baseName TINYTEXT NOT NULL,
			name TINYTEXT NOT NULL,
			description TEXT NOT NULL,
			type TINYTEXT NOT NULL,
			INDEX index_abilityId(abilityId),
			INDEX index_skillTypeName(skillTypeName(20)),
			INDEX index_type(type(8))
		);";

$result = $db->query($query);
if (!$result) exit("ERROR: Database query error creating table!\n" . $db->error);

$query = "DELETE FROM skillTree".$TABLE_SUFFIX.";";
$result = $db->query($query);
if (!$result) exit("ERROR: Database query error (clearing skill tree)!\n" . $db->error);

$query = "SELECT * from minedSkills".$TABLE_SUFFIX." WHERE nextSkill >= 0;";
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
		if ($skill['skillType'] >= 0) $skillRootData[$id]['skillType'] = $skill['skillType'];
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
		$skill = &$skills[$skillLineId];
		$skill['skillType'] = $skillRootData[$id]['skillType'];
		$skill['skillLine'] = $skillRootData[$id]['skillLine'];
		$skill['classType'] = $skillRootData[$id]['classType'];
		$skill['raceType']  = $skillRootData[$id]['raceType'];
	}
}

	/* Print the basic skill tree */
foreach($skillTree as $id => $skillTreeLine)
{
	$skillId1 = $skillTreeLine[1];
	$skillId2 = $skillTreeLine[5];
	$skillId3 = $skillTreeLine[9];
	$line = $skills[$skillId1]['skillLine'];
	$type = $skills[$skillId1]['skillType'];
	$class = $skills[$skillId1]['classType'];
	$race = $skills[$skillId1]['raceType'];
	$name1 = $skills[$skillId1]['name'];
	$name2 = $skills[$skillId2]['name'];
	$name3 = $skills[$skillId3]['name'];
	
	$desc1 = $skills[$skillTreeLine[1]]['description'];
	$desc2 = $skills[$skillTreeLine[2]]['description'];
	$desc3 = $skills[$skillTreeLine[3]]['description'];
	$desc4 = $skills[$skillTreeLine[4]]['description'];
	
	print("\t$name1: $type $line $class $race\n");
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

	/* Update the skills */
foreach($skills as $id => $skill)
{
	$classType = $db->real_escape_string($skill['classType']);
	$raceType = $db->real_escape_string($skill['raceType']);
	$skillType = $db->real_escape_string($skill['skillType']);
	$skillLine = $db->real_escape_string($skill['skillLine']);
	
	$query = "UPDATE minedSkills SET skillType=\"$skillType\",raceType=\"$raceType\",classType=\"$classType\",skillLine=\"$skillLine\" WHERE id=$id;";
	$result = $db->query($query);
	if (!$result) exit("ERROR: Database query error updating skills table!\n" . $db->error . "\n" . $query);
}

	/* Save the skill tree */
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
		$name = $db->real_escape_string($skills[$skillLineId]['name']);
		$desc = $db->real_escape_string($skills[$skillLineId]['description']);
		
		$query = "INSERT INTO skillTree(abilityId,skillTypeName,rank,baseName,name,description,type) VALUES('$skillLineId','$skillTypeName','$index',\"$baseName\",\"$name\",\"$desc\",'$type')";
		$result = $db->query($query);
		if (!$result) exit("ERROR: Database query error inserting into skillTree database!\n" . $db->error . "\n" . $query);
	}
}

	/* Create skill passives */
$query = "SELECT * FROM minedSkills WHERE isPassive=1 AND isPlayer=1 GROUP BY name;";
$passiveResult = $db->query($query);
if (!$passiveResult) exit("ERROR: Database query error finding passive skills!\n" . $db->error . "\n" . $query);

$passiveResult->data_seek(0);
$passiveSkills = array();
$count = 0;
$type = "Passive";
$skillTypeName = "";
$index = 0;

/* Load all skills with a progression */
while (($passive = $passiveResult->fetch_assoc()))
{
	$passiveSkills[] = $passive;
	$count++;
	
	$id = $passive['id'];
	$name = $db->real_escape_string($passive['name']);
	$baseName = $name;
	$desc = $db->real_escape_string($passive['description']);
	
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
	
	$skillTypeName = $db->real_escape_string($skillTypeName);
	
	$query = "INSERT INTO skillTree(abilityId,skillTypeName,rank,baseName,name,description,type) VALUES('$id','$skillTypeName','$index',\"$baseName\",\"$name\",\"$desc\",'$type')";
	$result = $db->query($query);
	if (!$result) exit("ERROR: Database query error inserting into skillTree database!\n" . $db->error . "\n" . $query);
}

print("\tFound $count passive player skills.\n");


?>