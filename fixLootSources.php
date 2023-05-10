<?php
if (php_sapi_name() != "cli") die("Can only be run from command line!");

print("Fixing all lootSource data from test database...\n");

require("/home/uesp/secrets/esolog.secrets");
require("esoCommon.php");

$db = new mysqli($uespEsoLogWriteDBHost, $uespEsoLogWriteUser, $uespEsoLogWritePW, $uespEsoLogDatabase);
if ($db->connect_error) exit("Could not connect to mysql database!");

$testdb = new mysqli($uespEsoLogWriteDBHost, $uespEsoLogReadUser, $uespEsoLogReadPW, 'test');
if ($testdb->connect_error) exit("Could not connect to mysql test database!");

$query = "SELECT * FROM lootSources;";
$result = $testdb->query($query);
if (!$result) exit("Failed to load test.lootSource data!");

$testLootSources = [];

while (($row = $result->fetch_assoc()))
{
	$id = intval($row['id']);
	$testLootSources[$id] = $row;
}

$count = count($testLootSources);
print ("Loaded $count test.lootSources!\n");

$query = "SELECT * FROM lootSources;";
$result = $db->query($query);
if (!$result) exit("Failed to load uesp_esolog.lootSource data!");

$lootSources = [];

while (($row = $result->fetch_assoc()))
{
	$id = intval($row['id']);
	$lootSources[$id] = $row;
}

$count = count($lootSources);
print ("Loaded $count uesp_esolog.lootSources!\n");


print("Fixing names from test.lootSources...\n");
$fixedCount = 0;

foreach ($testLootSources as $id => $testLootSource)
{
	$lootSource = $lootSources[$id];
	if ($lootSource == null) continue;
	if ($lootSource['name'] == $testLootSource['name']) continue;
	
	$safeName = $db->real_escape_string($testLootSource['name']);
	$query = "UPDATE lootSources SET name='$safeName' WHERE id='$id';";
	
	$result = $db->query($query);
	
	if (!result)
	{
		print("\tError: Failed to set name '$name' in record $id!\n");
		continue;
	}
	
	++$fixedCount;
}

print ("Fixed $fixedCount names in uesp_esolog.lootSources!\n");

print("Merging duplicate records in uesp.lootSources...\n");
$fixedCount = 0;
$nameIndex = [];

foreach ($lootSources as $id => $lootSource)
{
	$name = $lootSource['name'];
	if ($name == "Unknown") continue;
	
	$nameIndex[$name][] = $id;
	
	if (count($nameIndex[$name]) > 1) ++$fixedCount;
}

print("Found $fixedCount duplicate records!\n");

foreach ($nameIndex as $name => $ids)
{
	if (count($ids) <= 1) continue;
	
	$firstId = $ids[0];
	
	for ($i = 1; $i < count($ids); ++$i)
	{
		$id = $ids[$i];
		$lootSource = $lootSources[$id];
		$lootCount = intval($lootSource['count']);
		
		print("\t$name: Merging $id to $firstId (count $lootCount)...\n");
		
		$query = "UPDATE lootSources SET count=count+$lootCount WHERE id='$firstId';";
		$result = $db->query($query);
		if (!$result) print("\tError: Failed to update uesp_esolog.lootSource count for $firstId!\n");
		
		$query = "UPDATE npcLoot SET lootSourceId='$firstId' WHERE lootSourceId='$id';";
		$result = $db->query($query);
		if (!$result) print("\tError: Failed to update uesp_esolog.npcLoot count for $id to $firstId!\n");
		
		$query = "DELETE FROM lootSources WHERE id='$id';";
		$result = $db->query($query);
		if (!$result) print("\tError: Failed to delete from uesp_esolog.lootSource count for $id!\n");
	}
}

print("Merging duplicate name records with ^* suffix in uesp.lootSources...\n");
$fixedCount = 0;
$nameIndex = [];

foreach ($lootSources as $id => $lootSource)
{
	$name = $lootSource['name'];
	if ($name == "Unknown") continue;
	if (!preg_match("|\^.*|", $name)) continue;
	
	$name = preg_replace("|\^.*|", '', $name);
	
	$nameIndex[$name][] = $id;
	
	if (count($nameIndex[$name]) > 1) ++$fixedCount;
}

print("Found $fixedCount duplicate records!\n");

foreach ($nameIndex as $name => $ids)
{
	if (count($ids) <= 1) continue;
	
	$firstId = $ids[0];
	
	for ($i = 1; $i < count($ids); ++$i)
	{
		$id = $ids[$i];
		$lootSource = $lootSources[$id];
		$lootCount = intval($lootSource['count']);
		
		print("\t$name: Merging $id to $firstId (count $lootCount)...\n");
		
		continue;
		
		$query = "UPDATE lootSources SET count=count+$lootCount WHERE id='$firstId';";
		$result = $db->query($query);
		if (!$result) print("\tError: Failed to update uesp_esolog.lootSource count for $firstId!\n");
		
		$query = "UPDATE npcLoot SET lootSourceId='$firstId' WHERE lootSourceId='$id';";
		$result = $db->query($query);
		if (!$result) print("\tError: Failed to update uesp_esolog.npcLoot count for $id to $firstId!\n");
		
		$query = "DELETE FROM lootSources WHERE id='$id';";
		$result = $db->query($query);
		if (!$result) print("\tError: Failed to delete from uesp_esolog.lootSource count for $id!\n");
	}
}