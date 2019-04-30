<?php

if (php_sapi_name() != "cli") die("Can only be run from command line!");

print("Converting all records in the npc table to lootSources...\n");

require("/home/uesp/secrets/esolog.secrets");
require("esoCommon.php");

$db = new mysqli($uespEsoLogWriteDBHost, $uespEsoLogWriteUser, $uespEsoLogWritePW, $uespEsoLogDatabase);
if ($db->connect_error) exit("Could not connect to mysql database!");

$query = "SELECT * FROM npc;";
$result = $db->query($query);
if (!$result) exit("Failed to load npc data!");

$npcsToDelete = array();

while (($npc = $result->fetch_assoc()))
{
	$isNpc = false;
	
	if ($npc['level'] >= 0) $isNpc = true;
	if ($npc['gender'] >= 0) $isNpc = true;
	if ($npc['difficulty'] >= 0) $isNpc = true;
	if ($npc['ppClass'] != "") $isNpc = true;
	if ($npc['ppDifficulty'] >= 0) $isNpc = true;
	
	$id = $npc['id'];
	$count = $npc['count'];
	$name = $db->real_escape_string($npc['name']);
	
	$query = "INSERT INTO lootSources(id, name, count) VALUES($id, '$name', '$count');";
	$writeResult = $db->query($query);
	if (!$writeResult) print("\tFailed to update lootSources table!\n" . $db->error . "\n$query\n");
	
	if (!$isNpc) $npcsToDelete[] = $npc['id'];
}

$count = count($npcsToDelete);
print("\tFound $count npcs to delete...\n");

foreach ($npcsToDelete as $npcId)
{
	$query = "DELETE FROM npc WHERE id=$npcId;";
	$result = $db->query($query);
	if (!$result) print("\tFailed to delete npc record #$npcId!\n");
}

print("Done!\n");
	
