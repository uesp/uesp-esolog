<?php 

if (php_sapi_name() != "cli") die("Can only be run from command line!");

require("/home/uesp/secrets/esolog.secrets");
require("sparseMinedItem.php");


$db = new mysqli($uespEsoLogReadDBHost, $uespEsoLogReadUser, $uespEsoLogReadPW, $uespEsoLogDatabase);
if ($db === null || $db->connect_error) return die("ERROR: Could not connect to mysql database!");



function LoadMinedItem ($db, $itemId, $intLevel, $intSubtype, $tableSuffix = "")
{
	$query = "SELECT * FROM minedItem{$tableSuffix} WHERE itemId='$itemId' AND internalLevel='$intLevel' AND internalSubtype='$intSubtype';";
	$result = $db->query($query);
	if ($result === false) return null;
	if ($result->num_rows === 0) return null;
	
	return $result->fetch_assoc();
}


function LoadMinedItems ($db, $itemId, $tableSuffix = "")
{
	$query = "SELECT * FROM minedItem{$tableSuffix} WHERE itemId='$itemId';";
	$result = $db->query($query);
	if ($result === false) return null;
	if ($result->num_rows === 0) return null;
	
	$result->data_seek(0);
	$rows = array();
	
	while (($row = $result->fetch_assoc()))
	{
		$rows[] = $row;
	}
	
	return $rows;
}


function CompareSparseMinedItem($item)
{
	global $db;
		
	if ($item['level'] < 0) $item['level'] = 0;
	
	$itemId = $item['itemId'];
	$intLevel = $item['internalLevel'];
	$intSubtype = $item['internalSubtype'];
	$level = $item['level'];
	$quality = $item['quality'];
	
	$sparseItem = LoadEsoSparseMinedItem ($db, $itemId, $intLevel, $intSubtype);
	if ($sparseItem == null) return false;
	
	//print_r($sparseItem);
	
	print("Comparing Item $itemId:$intLevel:$intSubtype ($level/$quality)...\n");
	$result = true;
		
	foreach ($item as $key => $value)
	{
		if ($key == 'id') continue;
		if ($key == 'comment') continue;
		
		$sparseValue = $sparseItem[$key];
		
		if ($sparseValue === null)
		{
			print("\t$key: Missing value in sparse data!\n");
			$result = false;
		}
		else if (RemoveFormats($sparseValue) != RemoveFormats($value))
		{
			print("\t$key: Value mismatch! $value / $sparseValue\n");
			$result = false;
		}
	}
	
	//print ("EnchantName1: {$item['enchantName']}\n");
	//print ("EnchantName2: {$sparseItem['enchantName']}\n");
	
	//print ("EnchantDesc1: {$item['enchantDesc']}\n");
	//print ("EnchantDesc2: {$sparseItem['enchantDesc']}\n");
		
	return $result;
}


function RemoveFormats($text)
{
	return preg_replace("#\|c[a-fA-F0-9]{6}(.*)\|r#", "$1", $text);
}


function CompareSingleSparseMinedItem($itemId, $intLevel, $intSubtype)
{
	global $db;
	
	$item = LoadMinedItem($db, $itemId, $intLevel, $intSubtype, "");
	
	if ($item === null) 
	{
		print("Failed to load item $itemId:$intLevel:$intSubtype!\n");
		return false;	
	}
	
	print("Comparing item $itemId:$intLevel:$intSubtype...\n");
	CompareSparseMinedItem($item);
}


function CompareAllSparseMinedItems($itemId)
{
	global $db;
	
	$items = LoadMinedItems($db, $itemId, "");
	$totalOk = 0;
	
	printf("Comparing %d items with ID %d...\n", count($items), $itemId);
	
	foreach ($items as $item)
	{
		if (CompareSparseMinedItem($item)) ++$totalOk;
	}
	
	printf("Found %d of %d items that matched!\n", $totalOk, count($items));
}



//CompareAllSparseMinedItems(68118);
//CompareSingleSparseMinedItem(68118, 50, 177);


CompareAllSparseMinedItems(113000);
//CompareSingleSparseMinedItem(113000, 4, 8);




