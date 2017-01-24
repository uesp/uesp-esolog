<?php 

if (php_sapi_name() != "cli") die("Can only be run from command line!");

require("/home/uesp/secrets/esolog.secrets");
require("sparseMinedItem.php");


$db = new mysqli($uespEsoLogReadDBHost, $uespEsoLogReadUser, $uespEsoLogReadPW, $uespEsoLogDatabase);
if ($db === null || $db->connect_error) return die("ERROR: Could not connect to mysql database!");


function CompareSparseMinedItem($item)
{
	global $db;
		
	if ($item['level'] < 0) $item['level'] = 0;
	
	$itemId = $item['itemId'];
	$intLevel = $item['internalLevel'];
	$intSubtype = $item['internalSubtype'];
	$level = $item['level'];
	$quality = $item['quality'];
	
	if ($intLevel == 0 || $intSubtype == 0 || $intSubtype == 378) return true;
	
	$sparseItem = LoadEsoSparseMinedItem ($db, $itemId, $intLevel, $intSubtype);
	if ($sparseItem == null) return false;
	
	//print_r($sparseItem);

	$outputHeader = false;
	$result = true;
		
	foreach ($item as $key => $value)
	{
		if ($key == 'id') continue;
		if ($key == 'comment') continue;
		if ($key == 'icon') continue;
		$outputError = false;
		
		$sparseValue = $sparseItem[$key];
		
		if ($key == 'enchantDesc' || $key == 'enchantName') 
		{
			if ($sparseValue == "" && $value != "") continue;
		}		
		
		if ($sparseValue === null)
		{
			$outputErrorMsg = "\t$key: Missing value in sparse data!\n";
			$outputError = true;
			$result = false;
		}
		else if (RemoveFormats($sparseValue) != RemoveFormats($value))
		{
			if ($key == 'value')
				$outputErrorMsg = "\t$key: ". ($value - $sparseValue) ."\n";
			else
				$outputErrorMsg = "\t$key: Value mismatch! $value / $sparseValue\n";
			
			$outputError = true;
			$result = false;
		}
		
		if ($outputError)
		{
			if (!$outputHeader)
			{
				print("Comparing Item $itemId:$intLevel:$intSubtype ($level/$quality)...\n");
				$outputHeader = true;
			}
			print ($outputErrorMsg);
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
	return strtolower(preg_replace("#\|c[a-fA-F0-9]{6}(.*)\|r#", "$1", $text));
}


function CompareSingleSparseMinedItem($itemId, $intLevel, $intSubtype)
{
	global $db;
	
	$item = LoadEsoMinedItem($db, $itemId, $intLevel, $intSubtype, "");
	
	if ($item === null) 
	{
		print("Failed to load item $itemId:$intLevel:$intSubtype!\n");
		return false;	
	}
	
	print("Comparing item $itemId:$intLevel:$intSubtype...\n");
	
	return CompareSparseMinedItem($item);
}


function CompareAllSparseMinedItems($itemId)
{
	global $db;
	
	$items = LoadEsoMinedItems($db, $itemId, "");
	if ($items == null) return false;
	
	$totalOk = 0;
	
	//printf("Comparing %d items with ID %d...\n", count($items), $itemId);
	
	foreach ($items as $item)
	{
		if (CompareSparseMinedItem($item)) ++$totalOk;
	}
	
	printf("%d: Found %d of %d items that matched!\n", $itemId, $totalOk, count($items));
}


function CompareRandomSparseMinedItems($count)
{
	for ($i = 0; $i < $count; ++$i)
	{
		$itemId = mt_rand(1, 120000);
		CompareAllSparseMinedItems($itemId);
	}
}


//CompareSingleSparseMinedItem(68118, 50, 177);
//CompareSingleSparseMinedItem(113000, 4, 8);
//CompareSingleSparseMinedItem(113000, 50, 370);
//CompareAllSparseMinedItems(64655);
//CompareSingleSparseMinedItem(64655, 50, 368);
//CompareSingleSparseMinedItem(64655, 0, 0);

//CompareAllSparseMinedItems(113000);
//CompareAllSparseMinedItems(64655);
//CompareAllSparseMinedItems(68118);
//CompareAllSparseMinedItems(75741);
//CompareAllSparseMinedItems(107884);
//CompareAllSparseMinedItems(94648);
//CompareAllSparseMinedItems(51329);
//CompareAllSparseMinedItems(69101);
//CompareAllSparseMinedItems(107779);
//CompareAllSparseMinedItems(50919);
CompareAllSparseMinedItems(68944);

//CompareRandomSparseMinedItems(100);






