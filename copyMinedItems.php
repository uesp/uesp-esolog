<?php

$SOURCE_SUFFIX = "";
$DEST_SUFFIX = "31";

if (php_sapi_name() != "cli") die("Can only be run from command line!");

require("/home/uesp/secrets/esolog.secrets");
require("esoCommon.php");

if ($SOURCE_SUFFIX == $DEST_SUFFIX) exit("Error: Can't copy minedItem$SOURCE_SUFFIX table to itself!\n");
print("Copying mined items from minedItem$SOURCE_SUFFIX to minedItem$DEST_SUFFIX...\n");

$db = new mysqli($uespEsoLogWriteDBHost, $uespEsoLogWriteUser, $uespEsoLogWritePW, $uespEsoLogDatabase);
if ($db->connect_error) exit("Could not connect to mysql database!");

$MIN_ID = 1;
$MAX_ID = 200000;

$result = $db->query("CREATE TABLE IF NOT EXISTS minedItem$DEST_SUFFIX LIKE minedItem$SOURCE_SUFFIX;");
if ($result === false) exit("Error: Failed to create minedItem$DEST_SUFFIX table!\n");

$result = $db->query("TRUNCATE TABLE minedItem$DEST_SUFFIX;");
if ($result === false) exit("Error: Failed to empty minedItem$DEST_SUFFIX table!\n");

for ($id = $MIN_ID; $id <= $MAX_ID; $id++)
{
	if ($id % 1000 == 0) print("\t$id: Copying...\n");
	
	$result = $db->query("INSERT INTO minedItem$DEST_SUFFIX SELECT * FROM minedItem$SOURCE_SUFFIX WHERE itemId='$id';");
	if ($result === false) exit("Error: Failed to copy minedItems for $id!\n");
	
}

print("Copying minedItemSummary$SOURCE_SUFFIX to minedItemSummary$DEST_SUFFIX...\n");

$result = $db->query("CREATE TABLE IF NOT EXISTS minedItemSummary$DEST_SUFFIX LIKE minedItemSummary$SOURCE_SUFFIX;");
if ($result === false) exit("Error: Failed to create minedItemSummary$DEST_SUFFIX table!\n");

$result = $db->query("TRUNCATE TABLE minedItemSummary$DEST_SUFFIX;");
if ($result === false) exit("Error: Failed to empty minedItemSummary$DEST_SUFFIX table!\n");

$result = $db->query("INSERT INTO minedItemSummary$DEST_SUFFIX SELECT * FROM minedItemSummary$SOURCE_SUFFIX;");
if ($result === false) exit("Error: Failed to copy minedItems for $id!\n");

print("Done!\n");