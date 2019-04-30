<?php

if (php_sapi_name() != "cli") die("Can only be run from command line!");

print("Fixing all lootSource counts...\n");

require("/home/uesp/secrets/esolog.secrets");
require("esoCommon.php");

$db = new mysqli($uespEsoLogWriteDBHost, $uespEsoLogWriteUser, $uespEsoLogWritePW, $uespEsoLogDatabase);
if ($db->connect_error) exit("Could not connect to mysql database!");

$query = "SELECT * FROM lootSources;";
$result = $db->query($query);
if (!$result) exit("Failed to load lootSource data!");

while (($lootSource = $result->fetch_assoc()))
{
	$id = $lootSource['id'];
	$safeName = $db->real_escape_string($lootSource['name']);
	print("\tGetting count data for $safeName...\n");
		
	$query = "SELECT count FROM npcLoot WHERE lootSourceId='$id' AND zone='' AND itemName='__totalCount';";
	$countResult = $db->query($query);
	
	if (!$countResult)
	{
		print("\tFailed to get count data for $safeName!\n");
		continue;
	}
	
	$countRow = $countResult->fetch_assoc();
	$count = $countRow['count'];
	
	print("\t\tFound count = $count...\n");
	
	if ($count == 0)
	{
		$query = "DELETE FROM lootSources WHERE id=$id;";
	}
	else
	{
		$query = "UPDATE lootSources SET count=$count WHERE id=$id;";
	}
	
	$writeResult = $db->query($query);
	if (!$writeResult) print("\tError updating lootSources!\n" . $db->error . "\n");

}

print("Done!\n");
	
