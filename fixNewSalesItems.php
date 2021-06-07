<?php 

if (php_sapi_name() != "cli") die("Can only be run from command line!");

require_once("/home/uesp/secrets/esosalesdata.secrets");

$db = new mysqli($uespEsoSalesDataWriteDBHost, $uespEsoSalesDataWriteUser, $uespEsoSalesDataWritePW, $uespEsoSalesDataDatabase);
if ($db->connect_error) die("Could not connect to mysql database!");

$query = "SELECT * FROM items WHERE itemType=0 or name='';";
$result = $db->query($query);
$items = array();

while (($row = $result->fetch_assoc()))
{
	$items[] = $row;
}

$totalCount = count($items);
print ("Loaded $totalCount new items that need updating!\n");
$count = 0;

foreach ($items as $item)
{
	++$count;
	if (($count % 1000) == 0) print("$count/$totalCount: Updating item...\n");
	
	$itemId = $item['itemId'];
	$intLevel = $item['internalLevel'];
	$intType = $item['internalSubType'];
	
	$query = "SELECT * FROM uesp_esolog.minedItem WHERE itemId=$itemId AND internalLevel=$intLevel AND internalSubtype=$intType;";
	$result = $db->query($query);
	
	if ($result === false)
	{
		print("\t$count: Error loading item $itemId:$intType:$intLevel data! " . $result->error . "\n");
		continue;
	}
	
	if ($result->num_rows == 0)
	{
		$query = "SELECT * FROM uesp_esolog.minedItem WHERE itemId=$itemId AND internalLevel=1 AND internalSubtype=1;";
		$result = $db->query($query);
		
		if ($result === false || $result->num_rows == 0)
		{
			print("\t$count: Item $itemId:$intType:$intLevel was not found!\n");
			continue;
		}
	}
	
	$minedItem = $result->fetch_assoc();
	
	$level = $item['level'];
	if ($level <= 0) $level = $minedItem['level'];
	
	$quality = $item['quality'];
	if ($quality <= 0) $quality = $minedItem['quality'];
	
	$trait = $minedItem['trait'];
	$itemType = $minedItem['type'];
	$equipType = $minedItem['equipType'];
	$weaponType = $minedItem['weaponType'];
	$armorType = $minedItem['armorType'];
	$icon = $db->real_escape_string($minedItem['icon']);
	$setName = $db->real_escape_string($minedItem['setName']);
	$name = $db->real_escape_string($minedItem['name']);
	$id = $item['id'];
	
	$query  = "UPDATE items SET trait='$trait', itemType='$itemType', level='$level', quality='$quality', equipType='$equipType', ";
	$query .= "weaponType='$weaponType', armorType='$armorType', icon='$icon', setName='$setName', name='$name' WHERE id=$id;";
	
	$result = $db->query($query);
	if ($result === false) print("\tError updating item $id!\n");
}

