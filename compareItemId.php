<?php 

if (php_sapi_name() != "cli") die("Can only be run from command line!");

require("/home/uesp/secrets/esolog.secrets");

$db = new mysqli($uespEsoLogReadDBHost, $uespEsoLogReadUser, $uespEsoLogReadPW, $uespEsoLogDatabase);
if ($db->connect_error) exit("Could not connect to mysql database!");

$TABLEPREFIX = "41";
$VERSION = "1.9";
$FIRSTID = 1;
$LASTID = 100000;
$missingOkCount = 0;
$missingItemCount = 0;
$missingCheckCount = 0;
$okCount = 0;
$totalCount = 0;
$validCount = 0;

print("Checking for missing items...\n");

for ($id = $FIRSTID; $id <= $LASTID; $id++)
{
	++$totalCount;
	
	if ($id % 1000 == 0) print("Checking Item $id...\n");
	
	$query = "SELECT * FROM itemIdCheck WHERE itemId=$id AND version='$VERSION' LIMIT 1;";
	$result = $db->query($query);
	if (!$result) exit("ERROR: Database query error (finding item)!\n" . $db->error);
	
	$itemCheckData = $result->fetch_assoc();
	
	$query = "SELECT itemId FROM minedItemSummary$TABLEPREFIX WHERE itemId=$id LIMIT 1;";
	$result = $db->query($query);
	if (!$result) exit("ERROR: Database query error (finding item)!\n" . $db->error);
	$itemData = $result->fetch_assoc();
	
	if (!$itemData && !$itemCheckData)
	{
		++$missingOkCount;	
	}
	else if ($itemData && !$itemCheckData)
	{
		++$missingCheckCount;
		++$validCount;
		print("\t$id: Missing check data!\n");
	}
	else if (!$itemData && $itemCheckData)
	{
		++$validCount;
		++$missingItemCount;
		print("\t$id: Missing item data!\n");
	}
	else 
	{
		++$validCount;
		++$okCount;
	}
}


print("Total Items Checked = $totalCount\n");
print("        Valid Items = $validCount\n");
print("      Missing Items = $missingItemCount\n");
print("     Missing Checks = $missingCheckCount\n");
