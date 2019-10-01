<?php

require("/home/uesp/secrets/esolog.secrets");
require("esoCommon.php");

print("Fixing all npc gender from name codes...\n");

$db = new mysqli($uespEsoLogWriteDBHost, $uespEsoLogWriteUser, $uespEsoLogWritePW, $uespEsoLogDatabase);
if ($db->connect_error) die("Could not connect to mysql database!");

$result = $db->query("SELECT * FROM npc WHERE name LIKE '%^%';");
if (!$result) die("Failed to load npc records!");

$count = 0;

while ($npc = $result->fetch_assoc())
{
	$name = $npc['name'];
	$splitName = explode('^', $name);
	$name = $splitName[0];
	
	$gender = $npc['gender'];
	
	if ($splitName[1] == 'm' || $splitName[1] == 'M')
	{
		$gender = 2;
	}
	else if ($splitName[1] == 'f' || $splitName[1] == 'F')
	{
		$gender = 1;
	}
	else if ($splitName[1] == 'n' || $splitName[1] == 'N')
	{
		$gender = 0;
	}
	
	$id = $npc['id'];
	$safeName = $db->real_escape_string($name);
	$query = "UPDATE npc SET name='$safeName', gender='$gender' WHERE id='$id';";
	$writeResult = $db->query($query);
	if (!$writeResult) print("\t$id: Failed to update record!\n" . $query . "\n" . $db->error . "\n");
	++$count;
}

print("Updated $count NPC records!\n");

