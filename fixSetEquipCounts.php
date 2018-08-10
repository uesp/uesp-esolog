<?php 


if (php_sapi_name() != "cli") die("Can only be run from command line!");

require("/home/uesp/secrets/esolog.secrets");

$db = new mysqli($uespEsoLogWriteDBHost, $uespEsoLogWriteUser, $uespEsoLogWritePW, $uespEsoLogDatabase);
if ($db->connect_error) exit("Could not connect to mysql database!");

$TABLE_SUFFIX = "";
$itemId = 1;
$totalFixed = 0;
$totalFound = 0;

while ($itemId < 150000)
{
	if ($itemId % 1000 == 0) print ("$itemId...\n");

	$query = "SELECT id, internalLevel, internalSubtype, setMaxEquipCount FROM minedItem$TABLE_SUFFIX WHERE itemId=$itemId and internalLevel=10 and internalSubtype=1 ;";
	$result = $db->query($query);
	if ($result === false) die("Failed to load item #$itemId from minedItem!");
	
	while (($row = $result->fetch_assoc()))
	{
		$setMaxCount = $row['setMaxEquipCount'];
		if ($setMaxCount != 4) continue;
		$totalFound++;
		
		$id = $row['id'];
		$level = $row['internalLevel'];
		$intType = $row['internalSubtype'];
		
		print("\t$itemId:$level:$intType has a setMaxCount == 4...fixing all...\n");
		
		//$query = "UPDATE minedItem$TABLE_SUFFIX SET setMaxEquipCount=5 WHERE id=$id;";
		$query = "UPDATE minedItem$TABLE_SUFFIX SET setMaxEquipCount=5 WHERE itemId=$itemId;";
		$updateResult = $db->query($query);
		
		if ($updateResult === false)
			print("\tError updating item $id!\n");
		else
			$totalFixed++;

		//sleep(50);
		break;
	}
	
	$itemId++;
}


print("Found a total of $totalFound items with setEquipCount of 4!\n");
print("Fixed a total of $totalFixed items!\n");