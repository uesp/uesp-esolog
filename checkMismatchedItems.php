<?php

$TABLE_SUFFIX = "16";

$subType1 = 307;
$subType2 = 364;

if (php_sapi_name() != "cli") die("Can only be run from command line!");
print("Checking for mismatched item data...\n");

require("/home/uesp/secrets/esolog.secrets");

$db = new mysqli($uespEsoLogReadDBHost, $uespEsoLogReadUser, $uespEsoLogReadPW, $uespEsoLogDatabase);
if ($db->connect_error) exit("Could not connect to mysql database!");


print("\tLoading items with subtype $subType1...\n");

$query = "SELECT * from minedItem$TABLE_SUFFIX WHERE internalLevel=50 and internalSubtype=$subType1;";
$result = $db->query($query);
if (!$result) exit("Failed to query for item data!");

$itemData1 = array();

while (($item = $result->fetch_assoc()))
{
	$itemData1[] = $item;
}

$count = count($itemData1);
print("\tFound $count items with subtype $subType1...\n");

print("\tLoading items with subtype $subType2...\n");
$query = "SELECT * from minedItem$TABLE_SUFFIX WHERE internalLevel=50 and internalSubtype=$subType2;";
$result = $db->query($query);
if (!$result) exit("Failed to query for item data!");

$itemData2 = array();

while (($item = $result->fetch_assoc()))
{
	$itemData2[] = $item;
}

$count = count($itemData2);
print("\tFound $count items with subtype $subType2...\n");

foreach ($itemData1 as $i => $item1)
{
	$item2 = $itemData2[$i];
	
	if ($item2 == null) 
	{
		print("\t$i) Null item2 data\n");
		continue;
	}
	
	if ($item1['itemId'] != $item2['itemId'])
	{
		print("\t$i) Mismatched itemIds\n");
		continue;
	}
			
}