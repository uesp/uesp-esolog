<?php

if (php_sapi_name() != "cli") die("Can only be run from command line!");

$FIX_NAMES = true;

require("/home/uesp/secrets/esolog.secrets");
require("esoCommon.php");

print("Merging all NPCs with duplicate names...\n");

$db = new mysqli($uespEsoLogWriteDBHost, $uespEsoLogWriteUser, $uespEsoLogWritePW, $uespEsoLogDatabase);
if ($db->connect_error) exit("Could not connect to mysql database!");

$result = $db->query("SELECT * FROM npc;");
if ($result === false) exit("Failed to load NPCs!");

$ncs = [];

while ($row = $result->fetch_assoc())
{
	$id = $row['id'];
	$npcs[$id] = $row;
}

$count = count($npcs);
print("\tLoaded $count NPCs!\n");

$uniqueNpcs = [];

foreach ($npcs as $id => $npc)
{
	$name = $npc['name'];
	$name = preg_replace('/\^.*$/', '', $name);
	$name = FormatRemoveEsoItemDescriptionText($name);
	
	if ($name != $npc['name']) 
	{
		if ($FIX_NAMES)
		{
			print("\t$id) Fixing name '{$npc['name']}'!\n");
			$safeName = $db->real_escape_string($name);
			$writeResult = $db->query("UPDATE npc SET name='$safeName' WHERE id=$id;");
			$npc['name'] = $name;
		}
		else
		{
			print("\t$id) '{$npc['name']}' needs fixing!\n");
		}
	}
	
	if ($uniqueNpcs[$name] == null) $uniqueNpcs[$name] = [];
	$uniqueNpcs[$name][] = $id;
}

$count = count($uniqueNpcs);
print("\tFound $count unique npcs!\n");
$mergeCount = 0;

foreach ($uniqueNpcs as $name => $npcList)
{
	++$mergeCount;
	$firstNpcId = min($npcList);
	$count = count($npcList);
	if ($count == 1) continue;
	
	print("$mergeCount) Merging $count names match '$name' into NPC $firstNpcId...\n");
	
	$deletedNpcs = [];
	
	foreach ($npcList as $npcId)
	{
		if ($npcId == $firstNpcId) continue;
		
		$deletedNpcs[] = $npcId;
		
		$moveResult = $db->query("UPDATE location SET npcId='$firstNpcId' WHERE npcId='$npcId';");
		if ($moveResult === false) die("\tERROR: Failed to move locations for NPC $firstNpcId to $npcId!\n");
		
		$result1 = $db->query("SELECT * FROM npcLocations WHERE npcId='$npcId';");
		if ($result1 === false) die("\tERROR: Failed to load npcLocations for NPC $npcId!\n");
		
		while ($row = $result1->fetch_assoc())
		{
			$zone = $row['zone'];
			$locCount = $row['locCount'];
			
			$safeZone = $db->real_escape_string($zone);
			$result2 = $db->query("INSERT INTO npcLocations(npcId, zone, locCount) VALUES('$firstNpcId', '$safeZone', $locCount) ON DUPLICATE KEY UPDATE locCount=locCount+$locCount;");
			if ($result2 === false) die("\tERROR: Failed to merge npcLocations for NPC $npcId into $firstNpcId!\n");
			
			$result2 = $db->query("DELETE FROM npcLocations WHERE npcId='$npcId' and zone='$safeZone';");
			if ($result2 === false) die("\tERROR: Failed to delete npcLocations for NPC $firstNpcId!\n");
		}
		
		$result2 = $db->query("DELETE FROM npc WHERE id='$npcId';");
		if ($result2 === false) die("\tERROR: Failed to delete NPC $npcId!\n");
	}
	
	
}
