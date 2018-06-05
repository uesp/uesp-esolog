<?php

$TABLE_SUFFIX = "";
$PRINT_TABLE = false;

if (php_sapi_name() != "cli") die("Can only be run from command line!");

require("/home/uesp/secrets/esolog.secrets");
require("esoCommon.php");

print("Adding destruction skill table entries...\n");

$db = new mysqli($uespEsoLogWriteDBHost, $uespEsoLogWriteUser, $uespEsoLogWritePW, $uespEsoLogDatabase);
if ($db->connect_error) exit("Could not connect to mysql database!");


function AddSkillTreeElement($baseAbilityId, $elementAbilityId, $element, $newName)
{
	global $db;
	global $TABLE_SUFFIX;
	
	$query = "DELETE FROM skillTree$TABLE_SUFFIX where abilityId=$elementAbilityId;";
	$result = $db->query($query);
	if ($result === false) print("\tError deleting element ability $elementAbilityId!\n");
	
	$query = "SELECT * FROM minedSkills$TABLE_SUFFIX WHERE id=$baseAbilityId;";
	$result = $db->query($query);
	
	if ($result === false || $result->num_rows <= 0) 
	{
		print("\tError loading base ability ability $baseAbilityId!\n");
		return false;
	}
	
	$baseRow = $result->fetch_assoc();
	
	$query = "SELECT * FROM minedSkills$TABLE_SUFFIX WHERE id=$elementAbilityId;";
	$result = $db->query($query);
	
	if ($result === false || $result->num_rows <= 0)
	{
		print("\tError loading base ability ability $baseAbilityId!\n");
		return false;
	}
	
	$row = $result->fetch_assoc();
	
	$skillType = GetEsoSkillTypeTypeText(5);
	$skillTypeName = $db->real_escape_string($skillType . ":" . $baseRow['skillLine']);
	$learnedLevel = $baseRow['learnedLevel'];
	$maxRank = 4;
	$rank = $baseRow['rank'];
	$baseName = $db->real_escape_string($baseRow['name']);
	$origAbilityId = $baseRow['baseAbilityId'];
	$name = $db->real_escape_string($newName);
	$description = $db->real_escape_string($row['description']);
	$cost = $row['cost'];
	$icon = $db->real_escape_string($row['texture']);
	$prevSkill  = $baseRow['prevSkill'];
	$nextSkill  = $baseRow['nextSkill'];
	$nextSkill2 = $baseRow['nextSkill2'];
	$displayId = $baseRow['displayId'];
	
	$type = "Active";
	if ($row['mechanic']  == 10) $type = "Ultimate";
	if ($row['isPassive'] ==  1) $type = "Passive";
	
	$query = "SELECT * FROM minedSkills$TABLE_SUFFIX WHERE id=$origAbilityId;";
	$result = $db->query($query);
	
	if ($result !== false && $result->num_rows > 0) 
	{
		$origBaseRow = $result->fetch_assoc();
		$baseName = $db->real_escape_string($origBaseRow['name']);
	}
		
	$query  = "INSERT INTO skillTree$TABLE_SUFFIX SET ";
	$query .= "abilityId='$elementAbilityId', ";
	$query .= "skillTypeName='$skillTypeName', ";
	$query .= "learnedLevel='$learnedLevel', ";
	$query .= "maxRank='$maxRank', ";
	$query .= "rank='$rank', ";
	$query .= "baseName='$baseName', ";
	$query .= "name='$name', ";
	$query .= "description='$description', ";
	$query .= "type='$type', ";
	$query .= "cost='$cost', ";
	$query .= "icon='$icon', ";
	$query .= "displayId='$displayId', ";
	$query .= "skillIndex='-1' ";
	$query .= ";";
	
	$result = $db->query($query);
	if ($result === false) print("\tError adding element $elementAbilityId to skill tree!\n");
		
	$query  = "UPDATE minedSkills$TABLE_SUFFIX SET baseAbilityId='$origAbilityId', ";
	$query .= " prevSkill='$prevSkill', nextSkill='$nextSkill', nextSkill2='$nextSkill2', skillType='2', skillLine='Destruction Staff', target=''";
	$query .= " WHERE id='$elementAbilityId';";
	
	$result = $db->query($query);
	if ($result === false) print("\tError updating element $elementAbilityId base ability ID!\n");
	
	print("\tUpdated element $elementAbilityId base ability ID!\n");
		
	return true;
}


foreach ($ESO_DESTRUCTION_SKILLS as $baseAbilityId => $elementIds)
{
	$flameId = $elementIds['flame']; 
	$shockId = $elementIds['shock'];
	$frostId = $elementIds['frost'];
	
	$flameName = $elementIds['flameName'];
	$shockName = $elementIds['shockName'];
	$frostName = $elementIds['frostName'];
	
	AddSkillTreeElement($baseAbilityId, $flameId, "Flame", $flameName);
	AddSkillTreeElement($baseAbilityId, $shockId, "Shock", $shockName);
	AddSkillTreeElement($baseAbilityId, $frostId, "Frost", $frostName);
}