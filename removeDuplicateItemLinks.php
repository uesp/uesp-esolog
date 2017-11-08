<?php

if (php_sapi_name() != "cli") die("Can only be run from command line!");

require("/home/uesp/secrets/esolog.secrets");

$db = new mysqli($uespEsoLogWriteDBHost, $uespEsoLogWriteUser, $uespEsoLogWritePW, $uespEsoLogDatabase);
if ($db->connect_error) exit("Could not connect to mysql database!");

$TABLE_SUFFIX = "";
$itemId = 10;
$totalDeleted = 0;
$totalFound = 0;

while ($itemId < 150000)
{
	if ($itemId % 1000 == 0) print ("$itemId...\n");
	
	$query = "SELECT id, internalLevel, internalSubtype, link FROM minedItem$TABLE_SUFFIX WHERE itemId=$itemId;";
	$result = $db->query($query);
	if ($result === false) die("Failed to load item #$itemId from minedItem!");
	
	$itemCounts = array();
	$removeIds = array();
	
	while (($row = $result->fetch_assoc()))
	{
		$level = $row['internalLevel'];
		$intType = $row['internalSubtype'];
		$id = "$level:$intType";
		
		if ($itemCounts[$id] == null) $itemCounts[$id] = array();
		$itemCounts[$id][] = $row;
	}
	
	foreach ($itemCounts as $id => $items)
	{
		$count = count($items);
		if ($count <= 1) continue;
		
		print("\t$itemId:$id = $count items!\n");
		
		foreach ($items as $i => $item)
		{
			$link = $item['link'];
			$match = preg_match("#.*:1:0:0:[0-9]+:[0-9]+\|h.*\|h#", $link);
			
			if ($match) 
			{
				$removeIds[] = $item['id'];
				$totalFound++;
			}
		}
	}
	
	$count = count($removeIds);
	if ($count > 0) print("\tDeleting $count duplicate items...\n");
	
	foreach ($removeIds as $id)
	{
		$query = "DELETE FROM minedItem$TABLE_SUFFIX WHERE id=$id;";
		$delResult = $db->query($query);
		
		if ($delResult === false) 
			print("\tError deleting item $id!\n");
		else
			$totalDeleted++;
		
		//print("\tDeleting record $id!\n");
	}

	$itemId++;
}

print("Found a total of $totalFound duplicate items!\n");
print("Deleting a total of $totalDeleted duplicate items!\n");