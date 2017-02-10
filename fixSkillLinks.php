<?php

$TABLE_SUFFIX = "";
$PRINT_TABLE = false;

if (php_sapi_name() != "cli") die("Can only be run from command line!");

require("/home/uesp/secrets/esolog.secrets");


print("Fixing skill table next/prev ID links...\n");

$db = new mysqli($uespEsoLogWriteDBHost, $uespEsoLogWriteUser, $uespEsoLogWritePW, $uespEsoLogDatabase);
if ($db->connect_error) exit("Could not connect to mysql database!");

$query = "SELECT * FROM minedSkills$TABLE_SUFFIX WHERE rank>0 OR isPlayer>0;";
$result = $db->query($query);
if ($result === false) die("Failed to load skill records!\n");

$skills = array();
$baseSkillIds = array();
$endSkillIds = array();

while (($row = $result->fetch_assoc()))
{
	$id = $row['id'];	
	$skills[$id] = $row;
	
	if ($row['rank'] == 1 && $row['prevSkill'] <= 0) $baseSkillIds[] = $id;
	if ($row['nextSkill'] <= 0 && $row['prevSkill'] > 0) $endSkillIds[] = $id;
}

$count1 = count($skills);
$count2 = count($baseSkillIds);
$count3 = count($endSkillIds);
print("Loaded $count1 skills with $count2 base skills and $count3 end skills!\n");

print("Fixing nextSkill links...\n");

foreach ($endSkillIds as $id)
{
	$skill = $skills[$id];
	if ($skills[$id] == null) continue;
	
	$currentId = $id;
	$prevSkillId = $skill['prevSkill'];
	
	//print("EndStart: $id\n");
	
	while ($prevSkillId > 0 && $skills[$prevSkillId] != null)
	{
		//print("\t$prevSkillId\n");
		
		if ($skills[$prevSkillId]['nextSkill'] <= 0 && $skills[$prevSkillId]['nextSkill2'] != $currentId)
		{
			$skills[$prevSkillId]['nextSkill'] = $currentId;
			$skills[$prevSkillId]['__dirty'] = true;
		}
		elseif ($skills[$prevSkillId]['nextSkill2'] <= 0 && $skills[$prevSkillId]['nextSkill'] != $currentId)
		{
			$skills[$prevSkillId]['nextSkill2'] = $currentId;
			$skills[$prevSkillId]['__dirty'] = true;
		}
		
		$currentId = $prevSkillId;
		$skill = $skills[$prevSkillId];
		$prevSkillId = $skill['prevSkill'];
		
		if ($currentId == $prevSkillId)
		{
			print("Found Loop at $currentId!\n");
			break;
		}
	}
}

print("Fixing prevSkill links...\n");

foreach ($baseSkillIds as $id)
{
	if ($skills[$id] == null) continue;
	
	$currentId = $id;
	$nextSkillId = $skills[$id]['nextSkill'];
	$prevSkillId = 0;
	$baseSkillId = $id;
	$nextSkillId2 = 0;
	$prevSkillId2 = 0;
	
	while ($currentId > 0 && $skills[$currentId] != null)
	{
		$skills[$currentId]['prevSkill'] = $prevSkillId;
		$skills[$currentId]['baseAbilityId'] = $baseSkillId;
		$skills[$currentId]['__dirty'] = true;
		
		if ($skills[$currentId]['nextSkill2'] > 0 && $skills[$currentId]['rank'] == 4) {
			$nextSkillId2 = $skills[$currentId]['nextSkill2'];
			$prevSkillId2 = $currentId;
		}
		
		$prevSkillId = $currentId;
		$nextSkillId = $skills[$currentId]['nextSkill'];
				
		if ($currentId == $nextSkillId)
		{
			print("Found Loop at $currentId!\n");
			break;
		}
		
		$currentId = $nextSkillId;
	}	
	
	$currentId = $nextSkillId2;
	
	while ($currentId > 0 && $skills[$currentId] != null)
	{
		$skills[$currentId]['prevSkill'] = $prevSkillId2;
		$skills[$currentId]['baseAbilityId'] = $baseSkillId;
		$skills[$currentId]['__dirty'] = true;
				
		$prevSkillId2 = $currentId;
		$nextSkillId2 = $skills[$currentId]['nextSkill'];
				
		if ($currentId == $nextSkillId2)
		{
			print("Found Loop at $currentId!\n");
			break;
		}
		
		$currentId = $nextSkillId2;
	}

}

$count = 0;
print("Saving updating skill records...\n");

foreach ($skills as $id => $skill)
{
	if ($skill['__dirty'] !== true) continue;
	
	++$count;
	$next = $skill['nextSkill'];
	$next2 = $skill['nextSkill2'];
	$prev = $skill['prevSkill'];
	$base = $skill['baseAbilityId'];
	
	$query = "UPDATE minedSkills$TABLE_SUFFIX SET nextSkill=$next, nextSkill2=$next2, prevSkill=$prev, baseAbilityId=$base WHERE id=$id;";
	$result = $db->query($query);	
	if ($result === false) print("Error updating skill record $id!\n");
}


print("Saved $count skill records!\n");

