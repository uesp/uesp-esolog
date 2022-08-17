<?php

if (php_sapi_name() != "cli") die("Can only be run from command line!");

require("/home/uesp/secrets/esolog.secrets");
require("esoCommon.php");

print("Updating all setSummary tables from the setInfo table...\n");

$db = new mysqli($uespEsoLogWriteDBHost, $uespEsoLogWriteUser, $uespEsoLogWritePW, $uespEsoLogDatabase);
if ($db->connect_error) exit("Could not connect to mysql database!");

$query = "SHOW TABLES LIKE 'setSummary%';";
$rowResult = $db->query($query);
if (!$rowResult) exit("ERROR: Database query error (finding setSummary tables)!\n" . $db->error);
$rowResult->data_seek(0);

$setTables = [];

while (($row = $rowResult->fetch_row()))
{
	$setTables[] = $row[0];
}

$count = count($setTables);
print("Found $count setSummary* tables!\n");

$query = "SELECT * FROM setInfo;";
$rowResult = $db->query($query);
if (!$rowResult) exit("ERROR: Database query error (loading setInfo)!\n" . $db->error);
$rowResult->data_seek(0);

$setInfos = [];

while (($row = $rowResult->fetch_assoc()))
{
	$setName = strtolower($row['setName']);
	$setInfos[$setName] = $row;
}

$count = count($setInfos);
print("Loaded $count setInfo records!\n");

$updateCount = 0;

foreach ($setTables as $setTable)
{
	print("Updating $setTable...\n");
	
	$query = "ALTER TABLE `$setTable` ADD COLUMN type TINYTEXT NOT NULL;";
	$rowResult = $db->query($query); //Might fail if the row already exists
	 
	$query = "ALTER TABLE `$setTable` ADD COLUMN sources TINYTEXT NOT NULL;";
	$rowResult = $db->query($query); //Might fail if the row already exists
	
	$query = "SELECT id, setName FROM `$setTable`;";
	$rowResult = $db->query($query);
	if (!$rowResult) exit("ERROR: Database query error (loading $setTable)!\n" . $db->error);
	$rowResult->data_seek(0);
	
	while (($row = $rowResult->fetch_assoc()))
	{
		$setName = $row['setName'];
		$id = $row['id'];
		
		$setInfo = $setInfos[strtolower($setName)];
		
		if ($setInfo == null)
		{
			$name = strtolower($setName);
			
			if ($name == "icy conjuror") $name = "icy conjurer";
			if ($name == "lefthander's war girdle") $name = "lefthander's aegis belt";
			if ($name == "blood spawn") $name = "bloodspawn";
			if ($name == "amberplasm") $name = "amber plasm";
			$name = preg_replace('/^perfect /', 'perfected ', $name);
			if (preg_match('/ \(perfected\)$/', $name)) $name = "perfected " . preg_replace('/ \(perfected\)$/', '', $name);
			
			$setInfo = $setInfos[strtolower($name)];
		}
		
		if ($setInfo == null)
		{
			print("\tWARNING: $setName setInfo not found!\n");
			continue;
		}
		
		$setType = $db->real_escape_string($setInfo['type']);
		$setSources = $db->real_escape_string($setInfo['sources']);
		
		$query = "UPDATE `$setTable` SET type='$setType', sources='$setSources' WHERE id='$id';";
		$writeResult = $db->query($query);
		if (!$writeResult) exit("ERROR: Database query error (failed to update row $id)!\n" . $db->error);
		
		++$updateCount;
	}
}

print("Finished! Updated $updateCount set rows.\n");
