<?php

require_once("esoCommon.php");
require_once("/home/uesp/secrets/esolog.secrets");

if (php_sapi_name() != "cli") die("Can only be run from command line!");

print("Updating all skill tables with indexName field...\n"); 

$db = new mysqli($uespEsoLogWriteDBHost, $uespEsoLogWriteUser, $uespEsoLogWritePW, $uespEsoLogDatabase);
if ($db->connect_error) exit("Could not connect to mysql database!");

$query = "SHOW TABLES LIKE 'minedSkills%';";
$result = $db->query($query);
if ($result === false) exit("Error: Failed to list all skill tables!\n");

$skillTables = [];

while ($row = $result->fetch_array(MYSQLI_NUM))
{
	$skillTables[] = $row[0];
}

$count = count($skillTables);
print("\tFound $count skill tables...\n");

$columnAddCount = 0;
$indexAddCount = 0;
$nameUpdateCount = 0;

foreach ($skillTables as $skillTable)
{
	print("\tUpdating table $skillTable...\n");
	
	$query = "ALTER TABLE $skillTable ADD COLUMN indexName TINYTEXT NOT NULL;";
	$result = $db->query($query);
	if ($result !== false) $columnAddCount++;
	
	$query = "CREATE INDEX indexIndexName ON $skillTable(indexName(32));";
	$result = $db->query($query);
	
	if ($result !== false) 
		$indexAddCount++;
	else
		print($db->error . "\n");
	
	$query = "UPDATE $skillTable SET indexName=LOWER(REPLACE(name, '\'', ''));";
	$result = $db->query($query);
	if ($result === false) print("\tFailed to update index names in $skillTable\n");
	
	$nameUpdateCount += $result->affected_rows;
}

print("Added $columnAddCount indexName columns, added $indexAddCount indexes, and updated $nameUpdateCount rows!\n");