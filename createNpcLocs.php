<?php

if (php_sapi_name() != "cli") die("Can only be run from command line!");

print("Creating the npcLocations table...\n");

require("/home/uesp/secrets/esolog.secrets");
require("esoCommon.php");

$db = new mysqli($uespEsoLogReadDBHost, $uespEsoLogReadUser, $uespEsoLogReadPW, $uespEsoLogDatabase);
if ($db->connect_error) exit("Could not connect to mysql database!");

$query = "SELECT id FROM npc;";
$result = $db->query($query);
if (!$result) exit("Failed to load npc data!");

$npcIds = array();

while ($npc = $result->fetch_assoc())
{
	$npcIds[] = $npc['id'];
}

$count = count($npcIds);
print("\tFound $count npcs.\n");

$dbWrite = new mysqli($uespEsoLogWriteDBHost, $uespEsoLogWriteUser, $uespEsoLogWritePW, $uespEsoLogDatabase);
if ($dbWrite->connect_error) exit("Could not connect to mysql database!");

$query = "CREATE TABLE IF NOT EXISTS npcLocations (
						npcId BIGINT NOT NULL,
						zone TINYTEXT NOT NULL,
						locCount INTEGER NOT NULL,
						PRIMARY KEY (npcId, zone(64))
					) ENGINE=MYISAM;";
$result = $dbWrite->query($query);
if (!$result) exit("Failed to create npcLocations table!");

foreach ($npcIds as $i => $npcId)
{
	print("\tNPC $npcId...");
	
	$query = "SELECT npcId, zone, count(*) as locCount FROM location WHERE npcId=$npcId GROUP BY zone;";
	$result = $db->query($query);
	
	if (!$result) 
	{
		print("Error!\n");
		continue;
	}
	
	$numRows = $result->num_rows;
	
	print("Loaded $numRows rows.\n");
	
	while ($row = $result->fetch_assoc())
	{
		$safeZone = $dbWrite->real_escape_string($row['zone']);
		$locCount = $row['locCount'];
		$query = "INSERT IGNORE INTO npcLocations(npcId, zone, locCount) VALUES($npcId, '$safeZone', $locCount);";
		$writeResult = $dbWrite->query($query);
		if (!$writeResult) print("\tError: " . $dbWrite->error . "\n");
	}
}



		