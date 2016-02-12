<?php 

if (php_sapi_name() != "cli") die("Can only be run from command line!");

require("/home/uesp/secrets/esolog.secrets");

$db = new mysqli($uespEsoLogWriteDBHost, $uespEsoLogWriteUser, $uespEsoLogWritePW, $uespEsoLogDatabase);
if ($db->connect_error) exit("Could not connect to mysql database!");

$query = "SELECT * FROM minedSkillLines;";
$result = $db->query($query);
if (!$result) exit("ERROR: Database query error loading skills!\n" . $db->error);
$skills = array();

while (($skill = $result->fetch_assoc()))
{
	$skills[] = $skill;
}

print ("Loaded " . count($skills) . " skills.\n");

foreach ($skills as $skill)
{
	$xpString = $skill['xp'];
	$totalXp = 0;
	$lastXp = 0;
	$newXp = array();

	$xp = explode(",", $xpString);
	
	foreach ($xp as $value)
	{
		$intValue = intval($value);
		$totalXp = $intValue;
		$newXp[] = $intValue - $lastXp;
		
		$lastXp = $intValue;
	}
	
	$newXpString = implode(",", $newXp);
	
	print("Skill " . $skill['name'] . "\n");
	print("\tOld XP: $xpString\n");
	print("\tNew XP: $newXpString\n");
	print("\tTotal XP: $totalXp\n");
	
		/* Make sure not to update rows more than once */
	if ($skill['totalXp'] == 0 || $skill['totalXp'] == $lastXp)
	{
		$id = $skill['id'];
		$query = "UPDATE minedSkillLines SET totalXp=$totalXp, xp='$newXpString' WHERE id=$id;";
		$result = $db->query($query);
		if (!$result) exit("ERROR: Database query error loading skills!\n" . $db->error);
	}
}


