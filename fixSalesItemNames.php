<?php 

if (php_sapi_name() != "cli") die("Can only be run from command line!");

require_once("/home/uesp/secrets/esosalesdata.secrets");
require_once("esoCommon.php");

$db = new mysqli($uespEsoSalesDataWriteDBHost, $uespEsoSalesDataWriteUser, $uespEsoSalesDataWritePW, $uespEsoSalesDataDatabase);
if ($db->connect_error) die("Could not connect to mysql database!");

$query = "SELECT * FROM items;";
$itemResult = $db->query($query);
if (!$itemResult) die("Failed to load items!");

print ("Found ".$itemResult->num_rows." items for name updates...\n");

while (($item = $itemResult->fetch_assoc()))
{
	++$count;
	if (($count % 1000) == 0) print("$count: Updating item...\n");
	//if ($count < 45000) continue;
	
	$id = $item['id'];
	$itemId = $item['itemId'];
	$intLevel = $item['internalLevel'];
	$intType = $item['internalSubType'];
	
	//print("\t $itemId : $intLevel : $intType\n");
	
	$query = "SELECT name FROM uesp_esolog.minedItem WHERE itemId=$itemId AND internalLevel=$intLevel AND internalSubtype=$intType LIMIT 1;";
	$result = $db->query($query);
	
	if ($result === false) 
	{
		print("\t$count: Error loading item $itemId:$intType:$intLevel data! " . $result->error . "\n");
		print("\t\tQuery: $query\n");
		print("\t\tError: {$db->error}\n");
		continue;	
	}
	
	if ($result->num_rows == 0)
	{
		//print("\tNo matching minedItem found!\n");
		
		$query = "SELECT name FROM uesp_esolog.minedItem WHERE itemId=$itemId AND internalLevel=1 AND internalSubtype=1 LIMIT 1;";
		$result = $db->query($query);
		
		if ($result === false || $result->num_rows == 0) 
		{
			print("\t$count: Item $itemId:$intType:$intLevel was not found!\n");
			continue;
		}
	}
	
	$minedItem = $result->fetch_assoc();
	
	$name1 = MakeEsoTitleCaseName($item['name']);
	$name2 = MakeEsoTitleCaseName($minedItem['name']);

	if ($name1 != $name2) 
	{
		print("\t$itemId: Name mismatch: $name1 / $name2\n");
		$safeName = $db->real_escape_string($name2);
		$query = "UPDATE items SET name='$safeName' WHERE id=$id;";
		$result = $db->query($query);
		if (!$result) print("\tError saving item name!\n");
	}
}


