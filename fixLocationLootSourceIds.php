<?php

if (php_sapi_name() != "cli") die("Can only be run from command line!");

print("Fixing all non-NPC location npcId to lootSourceId...\n");

require("/home/uesp/secrets/esolog.secrets");
require("esoCommon.php");

$db = new mysqli($uespEsoLogWriteDBHost, $uespEsoLogWriteUser, $uespEsoLogWritePW, $uespEsoLogDatabase);
if ($db->connect_error) exit("Could not connect to mysql database!");

$query = "SELECT * FROM location WHERE npcId > 0;";
$result = $db->query($query);
if (!$result) exit("Failed to load location data!");

while (($location = $result->fetch_assoc()))
{
	$id = $location['id'];
	$npcId = $location['npcId'];
	
	$npcResult = $db->query("SELECT * FROM npc WHERE id='$npcId';");
	if (!$npcResult) continue;
	if ($npcResult->num_rows >= 1) continue;
	
	$lootSourceResult = $db->query("SELECT * FROM lootSources WHERE id='$npcId';");
	if (!$lootSourceResult) continue;
	
	if ($npcResult->num_rows >= 1) 
	{
		print("\tMissing lootSource row #$npcId!\n");
		continue;
	}
		
	print("\tDeleting location of NPC #$npcId!\n");
		
	$query = "DELETE FROM locations WHERE id='$id';";
	//$writeResult = $db->query($query);
	//if (!$writeResult) print("\tError updating location table!\n");
}

print("Done!\n");
	
