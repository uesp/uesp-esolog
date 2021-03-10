<?php

$TABLE_SUFFIX = "29";

if (php_sapi_name() != "cli") die("Can only be run from command line!");

require("/home/uesp/secrets/esolog.secrets");
require("esoCommon.php");

print("Adding PVP artifact skill table entries...\n");

$db = new mysqli($uespEsoLogWriteDBHost, $uespEsoLogWriteUser, $uespEsoLogWritePW, $uespEsoLogDatabase);
if ($db->connect_error) exit("Could not connect to mysql database!");

$FIXED_SKILLS = array(
			"Ruinous Cyclone" => array(
					"id" => 116096,
					"type" => "Ultimate",
					"skillTypeName" => "Alliance War::Volendrung",
					"skillType" => 6,
					"skillLine" => "Volendrung",
					"learnedLevel" => 1,
					"maxRank" => 4,
					"rank" => 4,
					"skillIndex" => 1,
					"morph" => 0,
			),
			"Rourken's Rebuke" => array(
					"id" => 116093,
					"type" => "Active",
					"skillTypeName" => "Alliance War::Volendrung",
					"skillType" => 6,
					"skillLine" => "Volendrung",
					"learnedLevel" => 1,
					"maxRank" => 4,
					"rank" => 4,
					"skillIndex" => 2,
					"morph" => 0,
			),
			"Malacath's Venegeance" => array(
					"id" => 116094,
					"type" => "Active",
					"skillTypeName" => "Alliance War::Volendrung",
					"skillType" => 6,
					"skillLine" => "Volendrung",
					"learnedLevel" => 1,
					"maxRank" => 4,
					"rank" => 4,
					"skillIndex" => 3,
					"morph" => 0,
			),
			"Accursed Charge" => array(
					"id" => 117979,
					"type" => "Active",
					"skillTypeName" => "Alliance War::Volendrung",
					"skillType" => 6,
					"skillLine" => "Volendrung",
					"learnedLevel" => 1,
					"maxRank" => 4,
					"rank" => 4,
					"skillIndex" => 4,
					"morph" => 0,
			),
			"Pariah's Resolve" => array(
					"id" => 116095,
					"type" => "Active",
					"skillTypeName" => "Alliance War::Volendrung",
					"skillType" => 6,
					"skillLine" => "Volendrung",
					"learnedLevel" => 1,
					"maxRank" => 4,
					"rank" => 4,
					"skillIndex" => 5,
					"morph" => 0,
					
			),
			"Sundering Swing" => array(
					"id" => 117985,
					"type" => "Active",
					"skillTypeName" => "Alliance War::Volendrung",
					"skillType" => 6,
					"skillLine" => "Volendrung",
					"learnedLevel" => 1,
					"maxRank" => 4,
					"rank" => 4,
					"skillIndex" => 6,
					"morph" => 0,
			),
			
);

$skills = array();

foreach ($FIXED_SKILLS as $skillName => $skillData)
{
	$id = $skillData['id'];
	
	$query = "SELECT * FROM minedSkills$TABLE_SUFFIX WHERE id='$id' LIMIT 1;";
	$result = $db->query($query);
	
	if (!$result) 
	{
		print("\tError: Failed to load skill $id!\n$db->error");
		continue;
	}
	
	$skills[$id] = $result->fetch_assoc();
	$skills[$id]['fixedData'] = $skillData;
}


foreach ($skills as $skillId => $skillData)
{
	$fixedData = $skillData['fixedData'];
	
	$skillIndex = $fixedData['skillIndex'];
	$skillType = $fixedData['skillType'];
	$skillLine = $fixedData['skillLine'];
	$rank = $fixedData['rank'];
	$morph = $fixedData['morph'];
	$learnedLevel = $fixedData['learnedLevel'];
		
	$query = "UPDATE minedSkills$TABLE_SUFFIX SET skillIndex='$skillIndex', skillType='$skillType', skillLine='$skillLine', rank='$rank', morph='$morph', learnedLevel='$learnedLevel', baseAbilityId='$skillId' WHERE id='$skillId';";
	$result = $db->query($query);
	if (!$result) print("\tError: Failed to update skill $skillId!\n" . $db->error);
	
	$query = "DELETE FROM skillTree$TABLE_SUFFIX where abilityId='$skillId';";
	$result = $db->query($query);
	if ($result === false) print("\tError deleting element ability $skillId!\n");
	
	$columns = array();
	$values = array();
	
	$columns[] = "abilityId";
	$values[] = $skillId;
	
	$columns[] = "displayId";
	$values[] = $skillId;
	
	$columns[] = "skillTypeName";
	$values[] = "'" . $fixedData['skillTypeName'] . "'";
	
	$columns[] = "learnedLevel";
	$values[] = $learnedLevel;
	
	$columns[] = "maxRank";
	$values[] = $fixedData['maxRank'];
	
	$columns[] = "rank";
	$values[] = $rank;
	
	$columns[] = "baseName";
	$values[] = "'" . $db->real_escape_string($skillData['name']) . "'";
	
	$columns[] = "name";
	$values[] = "'" . $db->real_escape_string($skillData['name']) . "'";
	
	$columns[] = "description";
	$values[] = "'" . $db->real_escape_string($skillData['description']) . "'";
	
	$columns[] = "type";
	$values[] =  "'" . $fixedData['type'] . "'";
	
	$columns[] = "cost";
	$values[] =  $skillData['cost'];
	
	$columns[] = "icon";
	$values[] =  "'" . $skillData['texture'] . "'";
	
	$columns[] = "skillIndex";
	$values[] = $skillIndex;
	
	$dbCols   = implode(",", $columns);
	$dbValues = implode(",", $values);
	
	$query = "INSERT INTO skillTree$TABLE_SUFFIX($dbCols) VALUES($dbValues);";
	//print($query . "\n");
		
	$result = $db->query($query);
	if (!$result) print("\tError: Failed to create skillTree record for $skillId!\n" . $db->error);
}


//Skill Table
	//skillIndex
	//skillType
	//skillLine
	//baseAbilityId
	//learnedLevel
	//rank
	//morph
