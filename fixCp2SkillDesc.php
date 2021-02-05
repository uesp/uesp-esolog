<?php

$TABLE_SUFFIX = "29pts";

if (php_sapi_name() != "cli") die("Can only be run from command line!");

require("/home/uesp/secrets/esolog.secrets");
require("esoCommon.php");

print("Fixing all CP2 skill descriptions...\n");

$db = new mysqli($uespEsoLogWriteDBHost, $uespEsoLogWriteUser, $uespEsoLogWritePW, $uespEsoLogDatabase);
if ($db->connect_error) exit("Could not connect to mysql database!");

$result = $db->query("SELECT * FROM cp2Skills$TABLE_SUFFIX;");
if ($result === false) die("Failed to load cpSkills data!");

$cpSkills = array();

while (($row = $result->fetch_assoc())) {
	$id = intval($row['skillId']);
	$cpSkills[$id] = $row;
}

$count = count($cpSkills);
print("\tLoaded $count CP skills!\n");

/*
$result = $db->query("SELECT * FROM cp2SkillDescriptions$TABLE_SUFFIX;");
if ($result === false) die("Failed to load cpSkillDescs data!");

$cpSkillDescs = array();

while ($row = $result->fetch_assoc()) {
	$id = intval($row['id']);
	$cpSkillDescs[$id] = $row;
}

$count = count($cpSkillDescs);
print("\tLoaded $count CP skill descriptions!\n"); //*/


//Increases the cost which merchants buy goods from you by |cffffff2|r% per stage. Current bonus: |cffffff0|r%
$regexes = array(
		"/Grants ([0-9\.]+) /",
		"/by ([0-9\.]+)%/",
		"/by ([0-9\.]+) meter/",
		"/by ([0-9\.]+) per/",
		"/by ([0-9\.]+)\./",
		"/by ([0-9\.]+) Stamina/",
		"/decay ([0-9\.]+)%/",
		"/have a ([0-9\.]+)%/",
		"/wipes ([0-9\.]+)%/",
		"/Adds ([0-9\.]+) minutes/",
		"/Removes ([0-9\.]+) gold/",
		"/for ([0-9\.]+)%/",
		"/absorbs ([0-9\.]+) damage/",
		"/deal ([0-9\.]+) Physical/",
		"/deal ([0-9\.]+) Magic/",
		"/for ([0-9\.]+) Oblivion/",
		"/with ([0-9\.]+)%/",
		"/cost ([0-9\.]+)%/",
		"/costs ([0-9\.]+) less/",
		"/Restore ([0-9\.]+) Stamina/",
		"/Restore ([0-9\.]+) Magicka/",
		"/take ([0-9\.]+)%/",
);

foreach ($cpSkills as $skillId => $skill) {
	$numJumps = intval($skill['numJumpPoints']);
	if ($numJumps == 1) continue;
	
	$rawDesc = $skill['minDescription'];
	$desc = str_replace("|cffffff", "", $rawDesc);
	$desc = str_replace("|r", "", $desc);
	
	if (!preg_match("/Current (?:bonus|value)(?::|) /i", $desc)) continue;
	$matches = array();
	
	foreach ($regexes as $regex) {
		$match = preg_match($regex, $desc, $matches);
		if ($match) break;
	}
	
	if (!$match) {
		print("Failed to match description for skill $skillId: $desc\n");
		continue;
	}
	
	print("\tFixing {$skill['name']}($skillId) descriptions...\n");
	//print("\\tt$desc\n");
	
	$matchValue = floatval($matches[1]);
	$jumpDelta = intval($skill['jumpPointDelta']);
	$maxPoints = intval($skill['maxPoints']);
	
	$newBonus = 0;
	$lastNewBonus = 0;
	$newDesc = "";
	$lastPoint = 0;
	
	for ($i = 0; $i <= $maxPoints; $i += $jumpDelta) {
		$lastPoint = $i + $jumpDelta - 1;	
		$intBonus = intval($newBonus);
		$newDesc = preg_replace("/Current (bonus|value)(?::|) \|cffffff([0-9]+)\|r/i", "Current \$1: |cffffff$intBonus|r", $rawDesc);
		
		$safeDesc = $db->real_escape_string($newDesc);
		
		if ($lastPoint == $i)
			$query = "UPDATE cp2SkillDescriptions$TABLE_SUFFIX SET description='$safeDesc' WHERE skillId='$skillId' AND points='$i';";
		else
			$query = "UPDATE cp2SkillDescriptions$TABLE_SUFFIX SET description='$safeDesc' WHERE skillId='$skillId' AND points>='$i' AND points<='$lastPoint';";
		
		$result = $db->query($query);
		if ($result === false) print("\tError: Failed to update cp2SkillDescriptions value $skillId:$i!\n");
		
		$lastNewBonus = $newBonus;
		$newBonus += $matchValue;
		//print("\t$i: Bonus = $intBonus: $newDesc\n$query\n");
	}
	
	if ($newDesc != "") {
		$safeDesc = $db->real_escape_string($newDesc);
		$result = $db->query("UPDATE cp2Skills$TABLE_SUFFIX SET maxDescription='$safeDesc', `maxValue`='$lastNewBonus' WHERE skillId='$skillId';");
		if ($result === false) print("\tError: Failed to update cp2SkillDescriptions value $skillId:$i!\n" . $db->error);
	}
	
}