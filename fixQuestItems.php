<?php

require("/home/uesp/secrets/esolog.secrets");
require("esoCommon.php");

print("Fixing all old/duplicate quest item data...\n");

$db = new mysqli($uespEsoLogWriteDBHost, $uespEsoLogWriteUser, $uespEsoLogWritePW, $uespEsoLogDatabase);
if ($db->connect_error) die("Could not connect to mysql database!");

$result = $db->query("SELECT * FROM questItem;");
if (!$result) die("Failed to load npc records!");

$count = 0;
$questItems = array();
$questIdMap = array();
$questItemIdMap = array();

while ($item = $result->fetch_assoc())
{
	$id = $item['id'];
	$questId = $item['questId'];
	$itemId = $item['itemId'];
	
	$questItems[$id] = $item;
	
	if ($questIdMap[$questId] == null) $questIdMap[$questId] = array();
	if ($questItemIdMap[$itemId] == null) $questItemIdMap[$itemId] = array();
	
	$questIdMap[$questId][] = $id;
	$questItemIdMap[$itemId][] = $id;
}

$count = count($questItems);
print("\tLoaded $count quest items.\n");

foreach ($questItems as $id => $item)
{
	$result = preg_match("#\|h.+\|h$#", $item['itemLink'], $matches);
	if ($result != 1) continue;

	$itemId = $item['itemId'];
	$itemCount = count($questItemIdMap[$id]);
	
	if ($itemCount == 1)
	{		
		$itemLink = $result = preg_replace("#\|h(.+)\|h$#", "|h|h", $item['itemLink']);
		
		print("\tFound UNIQUE '{$item['itemLink']}...fixing to $itemLink\n");

		$query = "UPDATE questItem SET itemLink='$itemLink' WHERE id=$id;";
	}
	else
	{
		print("\tFound DUP '{$item['itemLink']}...deleting\n");
		$query = "DELETE FROM questItem WHERE id=$id;";
	}
	
	$result = $db->query($query);
	if (!$result) print("\t$id: Failed to update data!\n");
	
}