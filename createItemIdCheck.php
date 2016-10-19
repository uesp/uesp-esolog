<?php 

if (php_sapi_name() != "cli") die("Can only be run from command line!");

require("/home/uesp/secrets/esolog.secrets");

$db = new mysqli($uespEsoLogWriteDBHost, $uespEsoLogWriteUser, $uespEsoLogWritePW, $uespEsoLogDatabase);
if ($db->connect_error) exit("Could not connect to mysql database!");

$query = "CREATE TABLE IF NOT EXISTS itemIdCheck(
			id BIGINT NOT NULL AUTO_INCREMENT PRIMARY KEY,
			itemId INTEGER NOT NULL,
			version TINYTEXT NOT NULL,
			INDEX index_itemId (itemId),
			INDEX index_version (version(8))
		);";

$result = $db->query($query);
if (!$result) exit("ERROR: Database query error creating table!\n" . $db->error);

$TABLEPREFIX = "";
$VERSION = "12";
$FIRSTID = 1;
$LASTID = 130000;

for ($id = $FIRSTID; $id <= $LASTID; $id++)
{
	if ($id % 1000 == 0) print("Checking Item $id...\n");
	
	$query = "SELECT itemId FROM minedItemSummary$TABLEPREFIX WHERE itemId=$id LIMIT 1;";
	$result = $db->query($query);
	if (!$result) exit("ERROR: Database query error (finding item)!\n" . $db->error);
	$itemData = $result->fetch_assoc();
	
	if (!$itemData)
	{
		$query = "DELETE FROM itemIdCheck WHERE itemId=" . $id . " AND version='". $VERSION ."';";
		$result = $db->query($query);
	}
	else
	{
		$query  = "INSERT INTO itemIdCheck(itemId, version) VALUES(" . $id . ", '". $VERSION ."');";
		$result = $db->query($query);
		if (!$result) exit("ERROR: Database query error (writing itemIdCheck)!\n" . $db->error);
	}
}

?>