<?php

$TABLE_SUFFIX = "14pts";
$OUTPUT_FILE = "playerSkills14pts.php";

if (php_sapi_name() != "cli") die("Can only be run from command line!");

require("/home/uesp/secrets/esolog.secrets");

print("Saving player skills data...\n");

$db = new mysqli($uespEsoLogReadDBHost, $uespEsoLogReadUser, $uespEsoLogReadPW, $uespEsoLogDatabase);
if ($db->connect_error) exit("Could not connect to mysql database!");

$query = "SELECT * FROM minedSkills$TABLE_SUFFIX WHERE isPlayer=1;";
$result = $db->query($query);
if (!$result) exit("Failed to load skill data!");

$skills = array();

while ($row = $result->fetch_assoc())
{
	//if ($row['classType'] != "Warden") continue;
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
	
	$newSkill = array();
	//$newSkill['id'] = $row['id'];
	$newSkill['name'] = $row['name'];
	$newSkill['rank'] = $row['rank'];
	$newSkill['learnedLevel'] = $row['learnedLevel'];
	$newSkill['skillIndex'] = $row['skillIndex'];
	$newSkill['skillType'] = $row['skillType'];
	$newSkill['skillLine'] = $row['skillLine'];
	$newSkill['raceType'] = $row['raceType'];
	$newSkill['classType'] = $row['classType'];
	$newSkill['prevSkill'] = $row['prevSkill'];
	$newSkill['nextSkill'] = $row['nextSkill'];
	$newSkill['nextSkill2'] = $row['nextSkill2'];
	$newSkill['baseAbilityId'] = $row['baseAbilityId'];
	
	$skills[$row['id']] = $newSkill;
}

$output = '<?php' . "\n" . '$ESO_PLAYER_SKILLS = ' . var_export($skills, true) . ';';

file_put_contents($OUTPUT_FILE, $output);
