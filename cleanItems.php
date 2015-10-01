<?php

$TABLE_SUFFIX = "";

if (php_sapi_name() != "cli") die("Can only be run from command line!");
print("Fixing item data...\n");

require("/home/uesp/secrets/esolog.secrets");

$db = new mysqli($uespEsoLogWriteDBHost, $uespEsoLogWriteUser, $uespEsoLogWritePW, $uespEsoLogDatabase);
if ($db->connect_error) exit("Could not connect to mysql database!");

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
	$oldName = $item['name'];
	if ($matches['nameCode'] != '' && $matches['nameCode'][0] != '^') print("\tNamecode '{$matches['nameCode']}' for '$oldName'!\n");
	
	if ($itemName == $oldName) continue;
	$safeName = $db->real_escape_string($itemName);
	$id = $item['id'];
	
	print("\tFixing item name '$oldName' to '$itemName\n");
	
	$query = "UPDATE item$TABLE_SUFFIX SET name='$safeName' WHERE id=$id;";
	$writeResult = $db->query($query);
	if (!$writeResult) exit("ERROR: Database query error saving item!\n" . $db->error);
	$itemCount++;
}

print("Fixed $itemCount items names.\n");

$query = "SELECT name,count(*) as c,link FROM item GROUP BY link HAVING c>1;";
$itemResult = $db->query($query);
if (!$itemResult) exit("ERROR: Database query error finding duplicating items!\n" . $db->error);
print ("Found {$itemResult->num_rows} duplicate item links.\n");