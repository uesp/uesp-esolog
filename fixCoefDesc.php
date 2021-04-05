<?php

$TABLE_SUFFIX = "";

if (php_sapi_name() != "cli") die("Can only be run from command line!");

require("/home/uesp/secrets/esolog.secrets");
require("esoCommon.php");

print("Fixing all skill coef descriptions for version '$TABLE_SUFFIX'...\n");

$db = new mysqli($uespEsoLogWriteDBHost, $uespEsoLogWriteUser, $uespEsoLogWritePW, $uespEsoLogDatabase);
if ($db->connect_error) exit("Error: Could not connect to mysql database!\n");

$query = "SELECT * FROM minedSkills$TABLE_SUFFIX WHERE descHeader!='' AND coefDescription!='';";
$result = $db->query($query);
if ($result === false) exit("Error: Failed to load skills!\n");

print("Loaded {$result->num_rows} skills to be fixed!\n");

while (($skill = $result->fetch_assoc()))
{
	$abilityId = $skill['id'];
	$descHeader = $skill['descHeader'];
	$desc = $skill['description'];
	$coefDesc = $skill['coefDescription'];
	
	$newCoefDesc = preg_replace('/^\|cffffff' . preg_quote($descHeader) . "\|r\n/", "", $coefDesc);
	$newCoefDesc = preg_replace('/^' . preg_quote($descHeader) . "\n/", "", $newCoefDesc);
	
	//print("$abilityId: $descHeader : $newCoefDesc\n");
	
	$safeDesc = $db->real_escape_string($newCoefDesc);
	$query = "UPDATE minedSkills$TABLE_SUFFIX SET coefDescription='$safeDesc' WHERE id='$abilityId';";
	$writeResult = $db->query($query);
	if ($writeResult === false) print("ERROR: Failed to save coef description to skill $abilityId!\n");
	++$writeCount;
	
	$safeDesc = $db->real_escape_string($desc);
	$query = "UPDATE skillTree$TABLE_SUFFIX SET description='$safeDesc' WHERE abilityId='$abilityId';";
	$writeResult = $db->query($query);
	if ($writeResult === false) print("ERROR: Failed to save skill tree description to skill $abilityId!\n");
}

print("Fixed $writeCount skills!\n");




