<?php

if (php_sapi_name() != "cli") die("Can only be run from command line!");
print("Fixing skill rank ID data...\n");

require("/home/uesp/secrets/esolog.secrets");

$db = new mysqli($uespEsoLogWriteDBHost, $uespEsoLogWriteUser, $uespEsoLogWritePW, $uespEsoLogDatabase);
if ($db->connect_error) exit("Could not connect to mysql database!");

$FIX_SKILLS = array(
		38932 => array(38932, 41924, 41925, 41926), //Clouding Swarm
		38931 => array(38931, 41933, 41936, 41937),	//Devouring Swarm
		38949 => array(38949, 41900, 41901, 41902),	//Invigorating Drain
		38956 => array(38956, 41879, 41880, 41881),	//Accelerating Drain
		38963 => array(38963, 41813, 41814, 41815),	//Elusive Mist
		38965 => array(38965, 41822 ,41823, 41824),	//Baleful Mist
		58864 => array(58864, 58870, 58873, 58876),	//Claws of Anguish
		58879 => array(58879, 58901, 58904, 58907),	//Claws of Life
		39105 => array(39105, 42117, 42118, 42119),	//Brutal Pounce
		39104 => array(39104, 42126, 42127, 42128),	//Feral Pounce
		39113 => array(39113, 42155, 42156, 42157),	//Ferocious Roar
		39114 => array(39114, 42177, 42178, 42179),	//Rousing Roar
		58317 => array(58317, 58319, 58321, 58323),	//Hircine's Rage
		58325 => array(58325, 58329, 58332, 58334), //Hircine's Fortitude
		58742 => array(58742, 58786, 58790, 58794),	//Howl of Despair
		58798 => array(58798, 58802, 58805, 58808),	//Howl of Agony
);

foreach ($FIX_SKILLS as $id => $skillData)
{
	$rank1 = $skillData[0];
	$rank2 = $skillData[1];
	$rank3 = $skillData[2];
	$rank4 = $skillData[3];
	
	$oldRank2 = $rank1 + 20000000;
	$oldRank3 = $rank1 + 30000000;
	$oldRank4 = $rank1 + 40000000;
	
	$query = "UPDATE minedSkills set nextSkill='$rank2' WHERE id='$rank1';";
	$result = $db->query($query);
	if (!$result) print("\tError: {$db->error}\n");
	
	$query = "UPDATE minedSkills set id='$rank2', prevSkill='$rank1', nextSkill='$rank3' WHERE id='$oldRank2';";
	$result = $db->query($query);
	if (!$result) print("\tError: {$db->error}\n");
	
	$query = "UPDATE minedSkills set id='$rank3', prevSkill='$rank2', nextSkill='$rank4' WHERE id='$oldRank3';";
	$result = $db->query($query);
	if (!$result) print("\tError: {$db->error}\n");
	
	$query = "UPDATE minedSkills set id='$rank4', prevSkill='$rank3' WHERE id='$oldRank4';";
	$result = $db->query($query);
	if (!$result) print("\tError: {$db->error}\n");
	
	
	$query = "UPDATE skillTree set abilityId='$rank2' WHERE abilityId='$oldRank2';";
	$result = $db->query($query);
	if (!$result) print("\tError: {$db->error}\n");
	print ("$query\n");
	
	$query = "UPDATE skillTree set abilityId='$rank3' WHERE abilityId='$oldRank3';";
	$result = $db->query($query);
	if (!$result) print("\tError: {$db->error}\n");
	
	$query = "UPDATE skillTree set abilityId='$rank4' WHERE abilityId='$oldRank4';";
	$result = $db->query($query);
	if (!$result) print("\tError: {$db->error}\n");
}
