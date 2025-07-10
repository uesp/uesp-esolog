<?php
/*
 * Generates static skill icons with the skill name.
 */

$TABLE_SUFFIX = "47pts";
$OUTPUT_PATH = "/mnt/uesp/esogameicons/uespskills";
$ICON_PATH = "/mnt/uesp/esogameicons";
$ONLY_OUTPUT_PLAYER = true;

if (php_sapi_name() != "cli") die("Can only be run from command line!");

require("/home/uesp/secrets/esolog.secrets");
require("esoCommon.php");

print("Generating static skill icons...\n");

$db = new mysqli($uespEsoLogReadDBHost, $uespEsoLogReadUser, $uespEsoLogReadPW, $uespEsoLogDatabase);
if ($db->connect_error) exit("Could not connect to mysql database!");

$query = "SELECT id, displayId, name, texture, isPlayer, setName, rank, isPassive, morph, skillLine, skillType, classType, raceType, prevSkill FROM minedSkills$TABLE_SUFFIX;";
$result = $db->query($query);
if ($result === false) die("Failed to load skill records!\n");

$skills = [];

while ($row = $result->fetch_assoc())
{
	$skills[] = $row;
}

$count = count($skills);
print("Loaded $count skills!\n");

$outputCount = 0;
$uniqueCount = 0;
$outputSkills = [];

if ($TABLE_SUFFIX == "") 
	$versionPath = '/' . GetEsoUpdateVersion();
else
	$versionPath = '/' . $TABLE_SUFFIX;

$path = $OUTPUT_PATH . $versionPath;
print("\tOutputting icons to $path...\n"); 

if (!is_dir($path))
{
	if (!mkdir($path, 0775, false)) die("Failed to create path '$path'!\n");
}

foreach ($skills as $skill)
{
	$texture = trim($skill['texture']);
	$name = trim($skill['name']);
	$rank = intval($skill['rank']);
	$morph = $skill['morph'];
	$skillLine = $skill['skillLine'];
	$id = intval($skill['id']);
	$isPlayer = intval($skill['isPlayer']);
	$setName = intval($skill['setName']);
	$prevSkillId = intval($skill['prevSkill']);
	
	$skillType = intval($skill['skillType']);
	$skillTypeName = GetEsoSkillTypeText($skillType);
	if ($skillType == 1) $skillTypeName = $skill['classType'];
	
	if ($name == null || $name == '') continue;
	if ($texture == null || $texture == '' || $texture == '/esoui/art/icons/icon_missing.dds') continue;
	
	if ($rank > 1) 
	{
		if ($prevSkillId > 0) continue;
	}
	
	if ($rank <= 0 && $isPlayer) continue;
	
	if ($ONLY_OUTPUT_PLAYER)
	{
		if ($isPlayer <= 0 && $setName == "") continue;
	}
	
	$texture = preg_replace('/\.dds$/', '.png', $texture); 
	$iconFilename = $ICON_PATH . $texture;
	
	if (!file_exists($iconFilename)) 
	{
		print("\tError: Failed to find skill icon file '$iconFilename'!\n");
		continue;
	}
	
	$name = strtolower($name);
	$name = str_replace("'", '', $name);
	$name = preg_replace('#[ /:"<>&]#', '-', $name);
	
	$skillTypeName = strtolower($skillTypeName);
	$skillTypeName = str_replace("'", '', $skillTypeName);
	$skillTypeName = preg_replace('#[ /:"&<>]#', '-', $skillTypeName);
	
	$skillLine = strtolower($skillLine);
	$skillLine = str_replace("'", '', $skillLine);
	$skillLine = preg_replace('#[ /:"&<>]#', '-', $skillLine);
	
	$outputPath = $OUTPUT_PATH . $versionPath . "/" . $skillTypeName . "/" . $skillLine;
	$outputFilename = $outputPath . "/" . $name . '.png';
	
	if (!is_dir($outputPath))
	{
		if (!mkdir($outputPath, 0775, true)) print("\tFailed to create path '$outputPath'!\n");
	}
	
	if (!copy($iconFilename, $outputFilename))
	{
		print("\tError: Failed to copy file '$iconFilename' to '$outputFilename'!\n");
	}
	
	$prevSkill = $outputSkills[$name];
	
	if ($prevSkill)
	{
		if ($prevSkill['isPlayer'] || $prevSkill['setName'])
		{
			if ($skill['isPlayer'] || $skill['setName']) print("\tWarning: Duplicate skill name '$name' found!\n");
			continue;
		}
	}
	else
	{
		$uniqueCount++;
	}
	
	$outputFilename = $OUTPUT_PATH . $versionPath . "/" . $name . '.png';
	
	if (!copy($iconFilename, $outputFilename))
	{
		print("\tError: Failed to copy file '$iconFilename' to '$outputFilename'!\n");
		continue;
	}
	
	$outputSkills[$name] = $skill;
	$outputCount++;
	
	if ($outputCount % 1000 == 0) print("\t$outputCount files copied...\n");
}

print("Created $outputCount skill icons ($uniqueCount unique)!\n");