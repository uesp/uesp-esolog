<?php 

if (php_sapi_name() != "cli") die("Can only be run from command line!");

require("/home/uesp/secrets/esolog.secrets");

$db = new mysqli($uespEsoLogReadDBHost, $uespEsoLogReadUser, $uespEsoLogReadPW, $uespEsoLogDatabase);
if ($db->connect_error) exit("Could not connect to mysql database!");

$TABLEPREFIX = "";
$VERSION = "11";
$FIRSTID = 3;
$LASTID = 100000;
$checkData = array();
$MAGICCOUNT = 1483;


$query = "SELECT * FROM itemIdCheck WHERE version='$VERSION';";
$result = $db->query($query);
if (!$result) exit("ERROR: Database query error (finding item check data)!\n" . $db->error);

while ($row = $result->fetch_assoc())
{
	$checkData[$row['itemId']] = $row;	
}

print("Found ".count($checkData)." check item rows!\n");


for ($id = $FIRSTID; $id <= $LASTID; $id++)
{
	if ($id % 1000 == 0) print("Checking Item $id...\n");
	
	$idCheck = $checkData[$id];
	
	$query = "SELECT count(*) as count FROM minedItem$TABLEPREFIX WHERE itemId=$id;";
	$result = $db->query($query);
	if (!$result) exit("ERROR: Database query error (finding item count)!\n" . $db->error);
	$itemData = $result->fetch_assoc();
	$itemCount = $itemData['count'];
	
	if ($idCheck != null)
	{
		if ($itemCount == 0)
		{
			print("\t$id: Missing complete item!\n");
		}
		else if ($itemCount != 1 && $itemCount != $MAGICCOUNT)
		{
			print("\t$id: Missing partial item data ($itemCount records)!\n");
		}
	}
	else if ($idCheck == null && $itemCount > 0)
	{
		print("\t$id: Item data where it should be missing ($itemCount)!\n");
	}
	
	
}

