<?php

if (php_sapi_name() != "cli") die("Can only be run from command line!");

require("esoSkillRankData.php");
require("/home/uesp/secrets/esolog.secrets");

$db = new mysqli($uespEsoLogReadDBHost, $uespEsoLogReadUser, $uespEsoLogReadPW, $uespEsoLogDatabase);
if ($db->connect_error) exit("Could not connect to mysql database!");

$skillData = array();
$result = $db->query("SELECT * FROM minedSkills26pts WHERE isPlayer=1;");
if (!$result) die("Failed to run load skills query!");

while ($row = $result->fetch_assoc())
{
	$id = $row['id'];
	$skillData[$id] = $row;
}

$count = count($skillData);
print("Loaded $count player skills!\n");
$errorCount = 0;

foreach ($ESO_BASESKILL_RANKDATA as $abilityId => $rankData)
{
	$skillBase = $skillData[$abilityId];
	$skill1 = $skillData[$rankData[1]];
	$skill2 = $skillData[$rankData[2]];
	$skill3 = $skillData[$rankData[3]];
	$skill4 = $skillData[$rankData[4]];
	
	if ($skillBase == null) print("\t$abilityId: Base skill data not found!\n");
	if ($skill1 == null) print("\t{$rankData[1]}: Skill rank 1 data not found!\n");
	if ($skill2 == null) print("\t{$rankData[2]}: Skill rank 2 data not found!\n");
	if ($skill3 == null) print("\t{$rankData[3]}: Skill rank 3 data not found!\n");
	if ($skill4 == null) print("\t{$rankData[4]}: Skill rank 4 data not found!\n");
	
	//if ($skill1['rank'] != 1) { print("\t$abilityId:{$rankData[1]}: Skill rank 1 data mismatch ({$skill1['rank']} found)!\n"); ++$errorCount; }
	//if ($skill2['rank'] != 2) { print("\t$abilityId:{$rankData[2]}: Skill rank 2 data mismatch ({$skill2['rank']} found)!\n"); ++$errorCount; }
	//if ($skill3['rank'] != 3) { print("\t$abilityId:{$rankData[3]}: Skill rank 3 data mismatch ({$skill3['rank']} found)!\n"); ++$errorCount; }
	//if ($skill4['rank'] != 4) { print("\t$abilityId:{$rankData[4]}: Skill rank 4 data mismatch ({$skill4['rank']} found)!\n"); ++$errorCount; }
	
	if ($skill1['rank'] != 1 || $skill2['rank'] != 2 || $skill3['rank'] != 3 || $skill4['rank'] != 4)
	{
		$realRanks = array();
		$realRanks[$skill1['rank']] = $skill1['id'];
		$realRanks[$skill2['rank']] = $skill2['id'];
		$realRanks[$skill3['rank']] = $skill3['id'];
		$realRanks[$skill4['rank']] = $skill4['id'];
		
		print("\t$abilityId =>\n\t\tarray(\n");
		print("\t\t\t1 => {$realRanks[1]},\n");
		print("\t\t\t2 => {$realRanks[2]},\n");
		print("\t\t\t3 => {$realRanks[3]},\n");
		print("\t\t\t4 => {$realRanks[4]},\n");
		print("\t\t),\n");
		
		++$errorCount;
	}
}

print("\tFound $errorCount skills with rank data errors!\n");