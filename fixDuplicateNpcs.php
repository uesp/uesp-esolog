<?php

require("/home/uesp/secrets/esolog.secrets");
require("esoCommon.php");

print("Fixing all duplicate NPCs...\n");

$db = new mysqli($uespEsoLogWriteDBHost, $uespEsoLogWriteUser, $uespEsoLogWritePW, $uespEsoLogDatabase);
if ($db->connect_error) die("Could not connect to mysql database!");

$result = $db->query("SELECT * FROM npc;");
if (!$result) die("Failed to load npc records!");

$npcs = array();
$npcNames = array();

while ($npc = $result->fetch_assoc())
{
	$id = $npc['id'];
	$name = trim($npc['name']);
	$npcs[$id] = $npc;
	
	if ($npcNames[$name] == null) $npcNames[$name] = array();
	$npcNames[$name][] = $id;
}

$count = count($npcs);
print("Loaded $count NPC records!\n");

$duplicateNpcs = array();

foreach ($npcNames as $name => $ids)
{
	$count = count($ids);
	if ($count > 1) $duplicateNpcs[] = $name;
}

$count = 0;
$totalCount = count($duplicateNpcs);
print("Found $totalCount duplicate NPCs!\n");

foreach ($duplicateNpcs as $name)
{
	$rootId = -1;
	$minLogId = 10000000000;
	++$count;
	$level = -1;
	$gender = -1;
	$difficulty = -1;
	$ppClass = "";
	$ppDifficulty = -1;
	$reaction = -1;
	$newCount = 0;
	$npcLocs = array();
	
	print("\t$count/$totalCount) $name...\n");
	
	foreach ($npcNames[$name] as $id)
	{
		$npc = $npcs[$id];
		$logId = $npc['logId'];
		
		if ($npc['level'] >= 0 && $level < 0) $level = $npc['level'];
		if ($npc['gender'] >= 0 && $gender < 0) $gender = $npc['gender'];
		if ($npc['difficulty'] >= 0 && $difficulty < 0) $difficulty = $npc['difficulty'];
		if ($npc['ppDifficulty'] >= 0 && $ppDifficulty < 0) $ppDifficulty = $npc['ppDifficulty'];
		if ($npc['reaction'] >= 0 && $reaction < 0) $reaction = $npc['reaction'];
		if ($npc['ppClass'] != "" && $ppClass == "") $ppClass = $npc['ppClass'];
		$newCount += $npc['count'];
		
		if ($logId < $minLogId) 
		{
			$minLogId = $logId;
			$rootId = $id;
		}
		
		$query = "SELECT * FROM npcLocations WHERE npcId='$id';";
		$result = $db->query($query);
		
		if (!$result) 
		{
			print("\tError loading npcLocation records!\n" . $db->error . "\n");
			continue;
		}
		
		while ($npcLoc = $result->fetch_assoc())
		{
			$zone = $npcLoc['zone'];
			$locCount = $npcLoc['locCount'];
			
			if ($npcLocs[$zone] == null) $npcLocs[$zone] = 0;
			$npcLocs[$zone] += $locCount;
		}
	}
	
	foreach ($npcNames[$name] as $id)
	{
		if ($id == $rootId)
		{
			$safePPClass = $db->real_escape_string($ppClass);
			$query = "UPDATE npc SET level='$level', gender='$gender', difficulty='$difficulty', ppClass='$safePPClass', ppDifficulty='$ppDifficulty', reaction='$reaction', count='$newCount' WHERE id='$rootId';";
			//print($query . "\n");
			$result = $db->query($query);
			if (!$result) print("Failed to update root NPC record $rootId!\n" . $db->error . "\n");
			
			$safeName = $db->real_escape_string($name);
			
			foreach ($npcLocs as $zone => $locCount)
			{
				$safeZone = $db->real_escape_string($zone);
				$query = "INSERT INTO npcLocations(npcId, zone, locCount) VALUES('$safeName', '$safeZone', $locCount) ON DUPLICATE KEY UPDATE locCount=$locCount;";
				//print($query . "\n");
				$result = $db->query($query);
				if (!$result) print("Failed to update root NPC location record for $rootId!\n" . $db->error . "\n");
			}
		}
		else
		{
			$query = "DELETE FROM npc WHERE id='$id';";
			//print($query . "\n");
			$result = $db->query($query);
			if (!$result) print("Failed to delete NPC record $id!\n" . $db->error . "\n");
			
			$query = "DELETE FROM npcLocations WHERE npcId='$id';";
			//print($query . "\n");
			$result = $db->query($query);
			if (!$result) print("Failed to delete NPC location records for $id!\n" . $db->error . "\n");
			
			$query = "UPDATE location SET npcId='$rootId' WHERE npcId='$id';";
			//print($query . "\n");
			$result = $db->query($query);
			if (!$result) print("Failed to update NPC location records!\n" . $db->error . "\n");
		}
	}
	
}