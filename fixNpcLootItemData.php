<?php

if (php_sapi_name() != "cli") die("Can only be run from command line!");

require("/home/uesp/secrets/esolog.secrets");
require("esoCommon.php");

print("Updating all npcLoot item data...\n");

$db = new mysqli($uespEsoLogWriteDBHost, $uespEsoLogWriteUser, $uespEsoLogWritePW, $uespEsoLogDatabase);
if ($db->connect_error) die("Could not connect to mysql database!");

$result = $db->query("SELECT * FROM npcLoot;");
if ($result === false) die("Failed to load npcLoot data");

$MINEITEM_TABLESUFFIX = "48pts";	//Set to PTS mined item table to search for items if not found in current live item table
$onlyUpdateBadItemNames = true;		//Set to false to update all items, true to only update items that don't have a valid name
$totalCount = 0;
$count = 0;

while (($row = $result->fetch_assoc()))
{
	$count++;
	if ($count % 100000 == 0) print("\t$count: Updating record...\n");
	
	$npcLootId = $row['id'];
	$itemLink = $row['itemLink'];
	$itemName = $row['itemName'];
	
	if ($onlyUpdateBadItemNames)
	{
		$shouldUpdate = false;
		
		if ($itemName == "") $shouldUpdate = true;
		if (preg_match('/^\|H/i', $itemName)) $shouldUpdate = true;
		if (!preg_match('/^\|H/i', $itemLink) && $itemName == "") $shouldUpdate = true;
		
		if (!$shouldUpdate) continue;
	}
	
	print("$npcLootId) Updating item...\n");
	$matches = ParseEsoItemLink($itemLink);
	
	if ($itemLink == "__gold")
	{
		$row['itemName'] = "Gold";
		$row['itemId'] = "-101";
		$row['icon'] = "/esoui/art/currency/currency_gold_32.dds";
		$row['itemType'] = "-1";
		$row['trait'] = "-1";
		$row['quality'] = "1";
		$row['value'] = "1";
	}
	elseif ($itemLink == "__telvar")
	{
		$row['itemName'] = "Telvar";
		$row['itemId'] = "-201";
		$row['icon'] = "/esoui/art/currency/currency_telvar_32.dds";
		$row['itemType'] = "-1";
		$row['trait'] = "-1";
		$row['quality'] = "1";
		$row['value'] = "-1";
	}
	else if ($matches === false)
	{
		$itemName = $row['itemName'];
		if ($itemName != "") continue;
		$itemName = $itemLink;
		
		$row['itemName'] = MakeEsoTitleCaseName($itemLink);
		$row['icon'] = "";
		$row['itemType'] = -1;
		$row['quality'] = -1;
		$row['trait'] = -1;
		$row['value'] = -1;
	}
	else
	{
		$itemId = (int) $matches['itemId'];
		$itemLevel = (int) $matches['level'];
		$itemSubtype = (int) $matches['subtype'];
		
		$query  = "SELECT * FROM minedItem WHERE itemId='$itemId' AND internalLevel='$itemLevel' AND internalSubtype='$itemSubtype';";
		$query1 = "SELECT * FROM minedItemSummary WHERE itemId='$itemId';";
		$mineResult = $db->query($query);
		$summaryResult = $db->query($query1);
		if (!$mineResult || !$summaryResult) continue;
		
		if ($mineResult->num_rows == 0)
		{
			$query = "SELECT * FROM minedItem WHERE itemId='$itemId' AND internalLevel='1' AND internalSubtype='1';";
			$mineResult = $db->query($query);
			if (!$mineResult) continue;
			
			if ($mineResult->num_rows == 0)
			{
				$query = "SELECT * FROM minedItem".$MINEITEM_TABLESUFFIX." WHERE itemId='$itemId' AND internalLevel='$itemLevel' AND internalSubtype='$itemSubtype';";
				$mineResult = $db->query($query);
				if (!$mineResult) continue;
				
				if ($mineResult->num_rows == 0)
				{
					$query = "SELECT * FROM minedItem".$MINEITEM_TABLESUFFIX." WHERE itemId='$itemId' AND internalLevel='1' AND internalSubtype='1';";
					$mineResult = $db->query($query);
					if (!$mineResult) continue;
					
					if ($mineResult->num_rows == 0) continue;
				}
			}
		}
		
		if ($summaryResult->num_rows == 0)
		{
			$query1 = "SELECT * FROM minedItemSummary".$MINEITEM_TABLESUFFIX." WHERE itemId='$itemId';";
			$summaryResult = $db->query($query1);
			if (!$summaryResult) continue;
			
			if ($summaryResult->num_rows == 0) continue;
		}
		
		$itemData = $mineResult->fetch_assoc();
		$summaryData = $summaryResult->fetch_assoc();
		
		//print("\tFound match for $itemId: " . $row['id'] . "\n");
		//print_r($itemData, false);
		//print_r($summaryData, false);
		
		$row['itemName'] = MakeEsoTitleCaseName($itemData['name']);
		$row['icon'] = $itemData['icon'];
		$row['itemType'] = $summaryData['type'];
		$row['quality'] = $itemData['quality'];
		$row['trait'] = $summaryData['trait'];
		$row['value'] = $itemData['value'];
	}
	
	$totalCount++;
	
	$id = $row['id'];
	$itemName = $db->real_escape_string($row['itemName']);
	$icon = $db->real_escape_string($row['icon']);
	$quality = $row['quality'];
	$itemType = $row['itemType'];
	$trait = $row['trait'];
	$value = $row['value'];
	
	$query = "UPDATE npcLoot SET itemName='$itemName', icon='$icon', quality='$quality', itemType='$itemType', trait='$trait', value='$value' WHERE id='$id';";
	$saveResult = $db->query($query);
	if (!$saveResult) print("\tError: Failed to save npcLoot $id!{$db->error}\n");
}

print ("Done, updated and saved $totalCount records!\n");