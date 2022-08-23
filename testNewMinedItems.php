<?php 
if (php_sapi_name() != "cli") die("Can only be run from command line!");

require("/home/uesp/secrets/esolog.secrets");
require("esoCommon.php");

$NEW_TABLE_SUFFIX = "30";
$OLD_TABLE_SUFFIX = "";
$MAX_ITEM_ID = 220000;

$db = new mysqli($uespEsoLogReadDBHost, $uespEsoLogReadUser, $uespEsoLogReadPW, $uespEsoLogDatabase);
if ($db->connect_error) exit("Could not connect to mysql database!");


for ($itemId = 3; $itemId <= $MAX_ITEM_ID; ++$itemId)
{
	if ($itemId % 1000 == 0) print("Loading $itemId...\n");
	$oldResult = $db->query("SELECT * FROM minedItem$OLD_TABLE_SUFFIX WHERE itemId='$itemId';");
	if ($oldResult === false) die("Failed to load item from minedItem$OLD_TABLE_SUFFIX!");
	
	$newResult = $db->query("SELECT minedItemSummary$NEW_TABLE_SUFFIX.*, minedItem$NEW_TABLE_SUFFIX.* FROM minedItem$NEW_TABLE_SUFFIX left join minedItemSummary$NEW_TABLE_SUFFIX on minedItemSummary$NEW_TABLE_SUFFIX.itemId=minedItem$NEW_TABLE_SUFFIX.itemId WHERE minedItem$NEW_TABLE_SUFFIX.itemId='$itemId';");
	if ($newResult === false) die("Failed to load item from minedItem$NEW_TABLE_SUFFIX!");
	
	$oldCount = $oldResult->num_rows;
	$newCount = $newResult->num_rows;
	if ($oldCount == 0 && $newCount == 0) continue;
	
	if ($oldCount != $newCount) print("\t\t$itemId) Item count mismatch $oldCount <> $newCount !\n");
	
	$oldItems = [];
	$newItems = [];
	
	while ($oldItem = $oldResult->fetch_assoc())
	{
		$id = $oldItem['internalLevel'] . ":" . $oldItem['internalSubtype'];
		$oldItems[$id] = $oldItem;
	}
	
	while ($newItem = $newResult->fetch_assoc())
	{
		$id = $newItem['internalLevel'] . ":" . $newItem['internalSubtype'];
		$newItems[$id] = $newItem;
	}
	
	foreach ($oldItems as $id => $oldItem)
	{
		$newItem = $newItems[$id];
		
		if ($newItem == null)
		{
			print("\t\t$itemId) Missing $id in new items!\n");
			continue;
		}
		
		foreach ($oldItem as $field => $value)
		{
			if ($field == 'link') continue;
			if ($field == 'comment') continue;
			if ($field == 'cond') continue;
			if ($field == 'logId') continue;
			if ($field == 'enchantId') continue;
			if ($field == 'enchantLevel') continue;
			if ($field == 'enchantSubtype') continue;
			if ($field == 'traitCooldown') continue;
			if ($field == 'enchantSubtype') continue;
			if ($field == 'abilityName') continue;
			if ($field == 'glyphMaxLevel') continue;
			if ($field == 'runeRank') continue;
			
			$newValue = $newItem[$field];
			
			if ($newValue === null)
			{
				print("\t\t$itemId:$id) Missing new value for '$field': $value <> $newValue\n");
				continue;
			}
		}
	}
	
	//exit;
}

