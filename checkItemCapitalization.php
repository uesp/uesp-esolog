<?php

$TABLE_SUFFIX = "";

if (php_sapi_name() != "cli") die("Can only be run from command line!");
print("Checking for mismatched item name capitalization...\n");

require("/home/uesp/secrets/esolog.secrets");

$db = new mysqli($uespEsoLogReadDBHost, $uespEsoLogReadUser, $uespEsoLogReadPW, $uespEsoLogDatabase);
if ($db->connect_error) exit("Could not connect to mysql database!");

$summaryResult = $db->query("SELECT * FROM minedItemSummary$TABLE_SUFFIX;");
if ($summaryResult === false) exit("Failed to query records from minedItemSummary$TABLE_SUFFIX!");

$count = 0;

while ($summaryRow = $summaryResult->fetch_assoc())
{
	++$count;
	#$if ($count % 1000 == 0) print("Checking item $count...\n");
	
	$summaryName = $summaryRow['name'];
	$itemId = intval($summaryRow['itemId']);
	
	$result = $db->query("SELECT DISTINCT name FROM minedItem$TABLE_SUFFIX WHERE itemId='$itemId';");
	if ($result === false) exit("Failed to query records from minedItemSummary$TABLE_SUFFIX! " . $db->error);
	
	//print("$itemId = " . $result->num_rows . "\n");
	
	while ($row = $result->fetch_assoc())
	{
		$name = $row['name'];
		if ($name != $summaryName) print("$summaryName, $name, $itemId\n");
		//print("$summaryName, $name, $itemId\n");
	}
}

