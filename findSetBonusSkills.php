<?php


$TABLE_SUFFIX = "40";
$SHOW_SET = "";
$MATCH_ALL_SETS = true;

$FIXED_DATA = [
		142012 => "CP:Cutting Defense"
];

$IGNORE_SKILLS = [
		76617 => true,
		76618 => true,
];

$IGNORE_SETS = [
		"The Worm's Raiment",
		"Ebon Armory",
];

$IGNORE_SET_BONUS = [
		"Bahraha's Curse" => [ 4 => true ],
		"Syvarra's Scales" => [ 4 => true ],
		"Vesture of Darloc Brae" => [ 3 => true ],
		"Way of Air" => [ 3 => true ],
];

if (php_sapi_name() != "cli") die("Can only be run from command line!");

require("/home/uesp/secrets/esolog.secrets");
require("esoCommon.php");

print("Finding all set bonuses that link to skills...\n");

$db = new mysqli($uespEsoLogWriteDBHost, $uespEsoLogWriteUser, $uespEsoLogWritePW, $uespEsoLogDatabase);
if ($db->connect_error) exit("Could not connect to mysql database!");

$result = $db->query("SELECT * FROM setSummary$TABLE_SUFFIX ORDER by setName;");
if ($result === false) die("Failed to load set data!");

$sets = array();

while ($row = $result->fetch_assoc())
{
	$sets[] = $row;
}

$count = count($sets);
print("\tLoaded $count sets.\n");

if ($MATCH_ALL_SETS)
{
	print("\tTrying to match all sets...\n");
	$result = $db->query("SELECT * FROM minedSkills$TABLE_SUFFIX WHERE description!='' ORDER BY id;");
}
else
{
	$result = $db->query("SELECT * FROM minedSkills$TABLE_SUFFIX WHERE description LIKE '% scales %' OR description LIKE '% scaling %' ORDER BY id;");
}

//$result = $db->query("SELECT * FROM minedSkills$TABLE_SUFFIX WHERE description LIKE '%scaling off%' ORDER BY id;");
if ($result === false) die("Failed to load skill data!");

$skills = array();

while ($row = $result->fetch_assoc())
{
	$row['matchDesc'] = str_replace("  ", " ", $row['description']);
	$row['matchDesc'] = str_replace("  ", " ", $row['matchDesc']);
	$row['matchDesc'] = str_replace("  ", " ", $row['matchDesc']);
	$row['matchDesc'] = str_replace("  ", " ", $row['matchDesc']);
	$row['matchDesc'] = FormatRemoveEsoItemDescriptionText($row['matchDesc']);
	$row['matchDesc'] = preg_replace("/[0-9]+(?:\.[0-9]+)?(?:-[0-9]+(?:\.[0-9]+)?)?/", "#", $row['matchDesc']);
	$row['matchDesc'] = str_replace("  ", " ", $row['matchDesc']);
	$row['matchDesc'] = str_replace("  ", " ", $row['matchDesc']);
	$skills[] = $row;
}

$count = count($skills);
print("\tLoaded $count skills that might be set descriptions.\n");


function FindMatchingSkill($setBonus, $setName)
{
	global $IGNORE_SKILLS;
	global $skills;
	global $SHOW_SET;
	
	$rawBonus = str_replace("  ", " ", $setBonus);
	$rawBonus = str_replace("  ", " ", $rawBonus);
	$rawBonus = preg_replace("/\([0-9]+ items\) /", "", $rawBonus);
	$rawBonus = preg_replace("/\([0-9]+ item\) /", "", $rawBonus);
	$rawBonus = preg_replace("/[0-9]+(?:\.[0-9]+)?(?:-[0-9]+(?:\.[0-9]+)?)?/", "#", $rawBonus);
	$rawBonus = str_replace("\r\n", " ", $rawBonus);
	$rawBonus = str_replace("\r", " ", $rawBonus);
	$rawBonus = str_replace("\n", " ", $rawBonus);
	$rawBonus = str_replace("  ", " ", $rawBonus);
	$rawBonus = str_replace("  ", " ", $rawBonus);
	$rawBonus = str_replace("  ", " ", $rawBonus);
	//print("\t\t'$rawBonus'\n");
	
	if ($SHOW_SET == $setName) print("\t\t$setName: '$rawBonus'\n");
	
	$foundSkills = array();
	
	foreach ($skills as $skill)
	{
		if ($IGNORE_SKILLS[$skill['id']]) continue;
		
		if ($SHOW_SET == $setName) print("\t\t\t{$skill['id']}:{$skill['matchDesc']}\n");
		if (strcasecmp($rawBonus, $skill['matchDesc']) == 0) $foundSkills[] = $skill; 
	}
	
	$count = count($foundSkills);
	if ($count == 0) return false;
	if ($count == 1) return $foundSkills[0];
	
	print("\t\tFound $count matching skills!\n");
	
	return $foundSkills[0];
}

$foundCount = 0;

foreach ($sets as $set)
{
	$setName = $set['setName'];
	print("\tChecking $setName...\n");
	
	for ($i = 1; $i <= 12; ++$i)
	{
		$setBonus = $set["setBonusDesc$i"];
		if ($setBonus == null || $setBonus == "") continue;
		
		if ($IGNORE_SET_BONUS[$setName])
		{
			$ignore = $IGNORE_SET_BONUS[$setName];
			
			if ($ignore[$i] === true) 
			{
				print("\t\tIgnoring set bonus $i\n");
				continue;
			}
		}
		
		//print("\t\tsetBonusDesc$i : $setBonus\n");
		
		$hasMatch = preg_match("/ scales /", $setBonus);
		if (!$hasMatch) $hasMatch = preg_match("/ scaling /", $setBonus);
		if (!$MATCH_ALL_SETS && !$hasMatch) continue;
		
		$numMatches = preg_match_all("/\([0-9]+ items\) [^(]*/", $setBonus, $matches);
		$setBonuses = array();
		
		if ($numMatches == 0)
		{
			$setBonuses[] = $setBonus;
			print("\t\tFound no set descriptions!\n");
		}
		else
		{
			//print_r($matches);
			print("\t\tFound $numMatches set descriptions!\n");
			
			for ($j = 0; $j < $numMatches; ++$j)
			{
				$bonus = trim($matches[0][$j]);
				$setBonuses[] = $bonus;
				
				//print("\t\t$j: $bonus\n");
			}
		}
		
		foreach ($setBonuses as $setBonus)
		{
			$hasMatch = preg_match("/ scales /", $setBonus);
			if (!$hasMatch) $hasMatch = preg_match("/ scaling /", $setBonus);
			if (!$MATCH_ALL_SETS && !$hasMatch) continue;
		
			$skill = FindMatchingSkill($setBonus, $set['setName']);
			
			if ($skill === false)
			{
				print("\t\tNo matching skill found for: '$setBonus'\n");
				continue;
			}
			
			print("\t\tFound matching skill {$skill['id']}:{$skill['name']}\n");
			++$foundCount;
			
			$setName = $set['setName'];
			$setName = str_replace("Perfected ", "", $setName);
			$safeSetName = $db->real_escape_string($setName);
			
			$writeResult = $db->query("UPDATE minedSkills$TABLE_SUFFIX SET setName='$safeSetName' WHERE id='{$skill['id']}';");
			if ($writeResult === false) print("\t\tFailed to update setName for skill {$skill['id']}!\n"); 
		}
		
	}
}

foreach ($FIXED_DATA as $id => $setName)
{
	$safeId = intval($id);
	$safeSetName = $db->real_escape_string($setName);
	$writeResult = $db->query("UPDATE minedSkills$TABLE_SUFFIX SET setName='$safeSetName' WHERE id='$safeId';");
	if ($writeResult === false) print("\t\tFailed to update setName for skill $safeId!\n");
}

print("Found $foundCount skills matching set descriptions.\n"); 


