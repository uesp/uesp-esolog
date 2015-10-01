<?php

$OLD_ITEM_TABLE = "item16";		// Place to move items with only 20 data fields
$TABLE_SUFFIX = "";

if (php_sapi_name() != "cli") die("Can only be run from command line!");
print("Fixing item data...\n");

require("/home/uesp/secrets/esolog.secrets");

$db = new mysqli($uespEsoLogWriteDBHost, $uespEsoLogWriteUser, $uespEsoLogWritePW, $uespEsoLogDatabase);
if ($db->connect_error) exit("Could not connect to mysql database!");

if ($OLD_ITEM_TABLE != "")
{
	$query = "CREATE TABLE IF NOT EXISTS $OLD_ITEM_TABLE LIKE item$TABLE_SUFFIX;";
	$result = $db->query($query);
	if (!$result) exit("ERROR: Database query error creating  $OLD_ITEM_TABLE table!\n" . $db->error);
}

$query = "SELECT * FROM item$TABLE_SUFFIX WHERE name LIKE '|%';";
$itemResult = $db->query($query);
if (!$itemResult) exit("ERROR: Database query error finding links in item names!\n" . $db->error);
$itemResult->data_seek(0);
$itemCount = 0;

while (($item = $itemResult->fetch_assoc()))
{
	$itemLink = $item['link'];
	$result = preg_match('/\|H[A-Za-z0-9]*\:item(\:[0-9]*)*\|h(?P<name>[^|\^]*)(?P<nameCode>.*?)\|h/', $itemLink, $matches);
	
	if ($result == 0) 
	{
		print("\tError: Failed to parse link '$itemLink'!\n");
		continue;
	}
	
	$itemName = $matches['name'];
	$safeName = $db->real_escape_string($itemName);
	$id = $item['id'];
	print("\tFound item name '$itemName' from $itemLink\n");
	
	$query = "UPDATE item$TABLE_SUFFIX SET name='$safeName' WHERE id=$id;";
	$writeResult = $db->query($query);
	if (!$writeResult) exit("ERROR: Database query error saving item!\n" . $db->error);
	$itemCount++;
}

print("Fixed $itemCount items with names as links.\n");

$query = "SELECT id,name,link FROM item$TABLE_SUFFIX WHERE link!='';";
$itemResult = $db->query($query);
if (!$itemResult) exit("ERROR: Database query error finding links in item names!\n" . $db->error);
$itemResult->data_seek(0);
$itemCount = 0;
$moveOldCount = 0;

while (($item = $itemResult->fetch_assoc()))
{
	$itemLink = $item['link'];
	$result = preg_match('/\|H[A-Za-z0-9]*\:item(?P<data>(\:[0-9]*)*)\|h(?P<name>[^|\^]*)(?P<nameCode>.*?)\|h/', $itemLink, $matches);
	
	if ($result == 0)
	{
		print("\tError: Failed to parse link '$itemLink'!\n");
		continue;
	}
	
	$colonCount = substr_count($matches['data'], ':');
	if ($colonCount < 20 || $colonCount > 21) print("\t$colonCount : {$matches['data']}\n");
	
	$itemName = $matches['name'];
	$oldName = $item['name'];
	if ($matches['nameCode'] != '' && $matches['nameCode'][0] != '^') print("\tNamecode '{$matches['nameCode']}' for '$oldName'!\n");
	$id = $item['id'];
	
	if ($itemName != $oldName)
	{
		$safeName = $db->real_escape_string($itemName);
		print("\tFixing item name '$oldName' to '$itemName\n");
		
		$query = "UPDATE item$TABLE_SUFFIX SET name='$safeName' WHERE id=$id;";
		$writeResult = $db->query($query);
		if (!$writeResult) exit("ERROR: Database query error saving item!\n" . $db->error);
		$itemCount++;
	}
	
	if($OLD_ITEM_TABLE != '' && $colonCount == 20)
	{
		$query = "INSERT IGNORE $OLD_ITEM_TABLE SELECT * FROM item$TABLE_SUFFIX WHERE id=$id;";
		$writeResult = $db->query($query);
		if (!$writeResult) exit("ERROR: Database query error copying item to old table!\n" . $db->error);
		
		$query = "DELETE FROM item$TABLE_SUFFIX WHERE id=$id;";
		$writeResult = $db->query($query);
		if (!$writeResult) exit("ERROR: Database query error deleting item!\n" . $db->error);
		$moveOldCount++;
	}
}

print("Fixed $itemCount items names.\n");
print("Moved $moveOldCount old items.\n");

$query = "SELECT name,count(*) as c,link FROM item WHERE link != '' GROUP BY link HAVING c>1;";
$itemResult = $db->query($query);
if (!$itemResult) exit("ERROR: Database query error finding duplicating items!\n" . $db->error);
print ("Found {$itemResult->num_rows} duplicate item links.\n");