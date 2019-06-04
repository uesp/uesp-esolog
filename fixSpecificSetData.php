<?php 

if (php_sapi_name() != "cli") die("Can only be run from command line!");

require("/home/uesp/secrets/esolog.secrets");

$db = new mysqli($uespEsoLogWriteDBHost, $uespEsoLogWriteUser, $uespEsoLogWritePW, $uespEsoLogDatabase);
if ($db->connect_error) exit("Error: Could not connect to mysql database!");

$TABLE_SUFFIX = "";
$totalFixed = 0;
$totalFound = 0;

$query = "SELECT * FROM minedItemSummary$TABLE_SUFFIX WHERE setName=\"Vastarie's Tutelage\";";
$result = $db->query($query);
if ($result === false || $result->num_rows <= 0) die("No items found!");

$items = array();

while (($row = $result->fetch_assoc()))
{
	$items[] = $row;
}

$count = count($items);
print("Loaded $count items matching set...\n");

foreach ($items as $i => $item)
{
	$itemId = $item['itemId'];
	print("\tFixing item #$itemId...\n");
		
	$set4 = $item['setBonusDesc4'];
	$set5 = $item['setBonusDesc5'];
	
		//(5 items) Adds 1-129 Stamina Recovery (5 items) When you resurrect an ally, you and your ally gain 3-258 Weapon and Spell Damage and 10% cost reduction to non-Ultimate abilities for 10 seconds.
	$result = preg_match('/(\(5 items\) Adds [0-9\-]+ Stamina Recovery)\s*(.*)/', $set5, $matches);
	
	if (!$result) 
	{
		print("\tError: Failed to parse set description string!\n");
		continue;
	}
	
	$set5Prefix = $matches[1];
	$set5Suffix = $matches[2];
	
	//print("\tPrefix: $set5Prefix\n");
	//print("\tSuffix: $set5Suffix\n");
	
	$newSet4 = $set4 . "\n" . $set5Prefix;
	$newSet5 = $set5Suffix;
	
	$safeBonus4 = $db->real_escape_string($newSet4);
	$safeBonus5 = $db->real_escape_string($newSet5);
	$query = "UPDATE minedItemSummary$TABLE_SUFFIX SET setBonusDesc4='$safeBonus4', setBonusDesc5='$safeBonus5' WHERE itemId='$itemId';";
	$result = $db->query($query);
	if ($result === false) print("\t\tError: Failed to update summary table data!\n" . $db->error);
	
	$query = "SELECT * FROM minedItem$TABLE_SUFFIX WHERE itemId=$itemId;";
	$result = $db->query($query);
	if ($result === false) die("Failed to load item data!");
	
	while (($row = $result->fetch_assoc()))
	{
		$recordId = $row['id'];
		
		$set4 = $row['setBonusDesc4'];
		$set5 = $row['setBonusDesc5'];
		
		$matchResult = preg_match('/(\(5 items\) Adds [0-9\-]+ Stamina Recovery)\s*(.*)/', $set5, $matches);
	
		if (!$matchResult) 
		{
			print("\tError: Failed to parse item set description string!\n");
			continue;
		}
	
		$set5Prefix = $matches[1];
		$set5Suffix = $matches[2];
	
		$newSet4 = $set4 . "\n" . $set5Prefix;
		$newSet5 = $set5Suffix;
		
		//print("\tPrefix: $set5Prefix\n");
		//print("\tSuffix: $set5Suffix\n");
		
		$safeBonus4 = $db->real_escape_string($newSet4);
		$safeBonus5 = $db->real_escape_string($newSet5);
		$query = "UPDATE minedItem$TABLE_SUFFIX SET setBonusDesc4='$safeBonus4', setBonusDesc5='$safeBonus5' WHERE id='$recordId';";
		$writeResult = $db->query($query);
		if ($writeResult === false) print("\t\tError: Failed to update item table data!\n" . $db->error);
	}
	
}

