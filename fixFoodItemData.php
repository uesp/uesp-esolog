<?php

if (php_sapi_name() != "cli") die("Can only be run from command line!");

require("/home/uesp/secrets/esolog.secrets");

$TABLE_SUFFIX = "";

$db = new mysqli($uespEsoLogWriteDBHost, $uespEsoLogWriteUser, $uespEsoLogWritePW, $uespEsoLogDatabase);
if ($db->connect_error) exit("Could not connect to mysql database!");

$query = "SELECT itemId from minedItemSummary$TABLE_SUFFIX where type=4 or type=12;";
$result = $db->query($query);
if ($result === false) exit("Failed to load item IDs!");

$foodIds = array();

while ($row = $result->fetch_assoc())
{
	$foodIds[] = $row['itemId'];
}

print("Found " . count($foodIds) . " food/drink items!\n");

foreach ($foodIds as $i => $id)
{
	print("Updating item $id...\n");
	$query = "UPDATE minedItem$TABLE_SUFFIX SET enchantDesc='', enchantName='', traitDesc='' WHERE itemId=$id;";
	$result = $db->query($query);
	if ($result === false) exit("Failed to update minedItem!");
}


