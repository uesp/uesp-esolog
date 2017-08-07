<?php

$TABLE_SUFFIX = "15pts";
$SOURCE_DATA1 = "playerSkills14.php";
$SOURCE_DATA2 = "";

if (php_sapi_name() != "cli") die("Can only be run from command line!");

require("/home/uesp/secrets/esolog.secrets");
require($SOURCE_DATA1);

if ($SOURCE_DATA2) 
{
	require($SOURCE_DATA2);
}

print("Restoring player skill data...\n");

$db = new mysqli($uespEsoLogWriteDBHost, $uespEsoLogWriteUser, $uespEsoLogWritePW, $uespEsoLogDatabase);
if ($db->connect_error) exit("Could not connect to mysql database!");

$count = 0;

foreach ($ESO_PLAYER_SKILLS as $skillId => $skill)
{
	// isPlayer
	// rank
	// learnedLevel
	// skillIndex
	// skillType
	// skillLine
	// raceType
	// classType
	// prevSkill
	// nextSkill
	// nextSkill2
	// baseAbilityId
	
	$skill['skillLine'] = $db->real_escape_string($skill['skillLine']);
	$skill['skillType'] = $db->real_escape_string($skill['skillType']);
	
	$query = "UPDATE minedSkills$TABLE_SUFFIX SET ";
	$query .= "isPlayer=1, rank='{$skill['rank']}', learnedLevel='{$skill['learnedLevel']}', skillIndex='{$skill['skillIndex']}', ";
	$query .= "skillType='{$skill['skillType']}', skillLine='{$skill['skillLine']}', raceType='{$skill['raceType']}', classType='{$skill['classType']}', ";
	$query .= "prevSkill='{$skill['prevSkill']}', nextSkill='{$skill['nextSkill']}', nextSkill2='{$skill['nextSkill2']}', baseAbilityId='{$skill['baseAbilityId']}' ";
	$query .= "WHERE id=$skillId;";
	
	$result = $db->query($query);
	
	if (!$result) 
		print("\t$skillId: Failed to save skill!\n$db->error\n");
	else
		$count++;
}


if ($ESO_PLAYER_SKILLS14)
{
	foreach ($ESO_PLAYER_SKILLS14 as $skillId => $skill)
	{
		// isPlayer
		// rank
		// learnedLevel
		// skillIndex
		// skillType
		// skillLine
		// raceType
		// classType
		// prevSkill
		// nextSkill
		// nextSkill2
		// baseAbilityId
		
		$skill['skillLine'] = $db->real_escape_string($skill['skillLine']);
		$skill['skillType'] = $db->real_escape_string($skill['skillType']);
	
		$query = "UPDATE minedSkills$TABLE_SUFFIX SET ";
		$query .= "isPlayer=1, rank='{$skill['rank']}', learnedLevel='{$skill['learnedLevel']}', skillIndex='{$skill['skillIndex']}', ";
		$query .= "skillType='{$skill['skillType']}', skillLine='{$skill['skillLine']}', raceType='{$skill['raceType']}', classType='{$skill['classType']}', ";
		$query .= "prevSkill='{$skill['prevSkill']}', nextSkill='{$skill['nextSkill']}', nextSkill2='{$skill['nextSkill2']}', baseAbilityId='{$skill['baseAbilityId']}' ";
		$query .= "WHERE id=$skillId;";
	
		$result = $db->query($query);
	
		if (!$result)
			print("\t$skillId: Failed to save skill!\n$db->error\n");
		else
			$count++;
	}
}

print("\tUpdated $count skills!\n");

