<?php 

$INPUT_TABLE_SUFFIX  = "";
$OUTPUT_TABLE_SUFFIX = "21pts";

$START_ITEM_ID = 1;
$END_ITEM_ID = 160000;

$MINITEM_LEVEL = 1;
$MINITEM_SUBTYPE = 1;

$MAXITEM_LEVEL = 50;
$MAXITEM_SUBTYPE = 370;

if (php_sapi_name() != "cli") die("Can only be run from command line!");

require("/home/uesp/secrets/esolog.secrets");
require("esoCommon.php");

$db = new mysqli($uespEsoLogWriteDBHost, $uespEsoLogWriteUser, $uespEsoLogWritePW, $uespEsoLogDatabase);
if ($db->connect_error) exit("Could not connect to mysql database!");

print("Copying PTS item data from minedItem$INPUT_TABLE_SUFFIX to minedItem$OUTPUT_TABLE_SUFFIX...\n");

$result = $db->query("CREATE TABLE IF NOT EXISTS minedItem$OUTPUT_TABLE_SUFFIX LIKE minedItem$INPUT_TABLE_SUFFIX;");
if (!$result) die("Failed to create minedItem$OUTPUT_TABLE_SUFFIX table! ".$db->error);

$result = $db->query("DESCRIBE minedItem$INPUT_TABLE_SUFFIX;");
if (!$result) die("Failed to describe minedItem$INPUT_TABLE_SUFFIX table! ".$db->error);

$columns = array();

while ($row = $result->fetch_assoc())
{
	if ($row['Field'] != "id") $columns[] = $row['Field'];
}

$columns = implode($columns, ",");
print("Columns to Copy: $columns\n");

for ($itemId = $START_ITEM_ID; $itemId <= $END_ITEM_ID; $itemId++)
{
	if ($itemId % 1000 == 0) print("\t$itemId: Copying items...\n");
	
	$minResult = $db->query("INSERT INTO minedItem$OUTPUT_TABLE_SUFFIX($columns) SELECT $columns FROM minedItem$INPUT_TABLE_SUFFIX where itemId='$itemId' AND internalLevel='$MINITEM_LEVEL' AND internalSubtype='$MINITEM_SUBTYPE';");
	$maxResult = $db->query("INSERT INTO minedItem$OUTPUT_TABLE_SUFFIX($columns)  SELECT $columns FROM minedItem$INPUT_TABLE_SUFFIX where itemId='$itemId' AND internalLevel='$MAXITEM_LEVEL' AND internalSubtype='$MAXITEM_SUBTYPE';");

}

print("Copying potion data...\n");
$result = $db->query("INSERT INTO minedItem$OUTPUT_TABLE_SUFFIX($columns) SELECT $columns FROM minedItem$INPUT_TABLE_SUFFIX where itemId < 0");

print("Done!\n");