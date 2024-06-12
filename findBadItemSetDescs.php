<?php 

if (php_sapi_name() != "cli") die("Can only be run from command line!");

require("/home/uesp/secrets/esolog.secrets");
require("esoCommon.php");

$db = new mysqli($uespEsoLogReadDBHost, $uespEsoLogReadUser, $uespEsoLogReadPW, $uespEsoLogDatabase);
if ($db->connect_error) exit("Could not connect to mysql database!");

$count = count($ESO_SET_INDEXES);
print("Finding all bad set descriptions in $count sets...\n");

$TABLE_SUFFIX = "42";
$VERBOSE = true;
$REMOVE_NUMBERS = true;
$OUTPUT_FIXITEMS = true;

$fixIds = [];

foreach ($ESO_SET_INDEXES as $setIndex => $setName)
{
	$safeName = $db->real_escape_string($setName);
	$query = "SELECT * FROM minedItemSummary$TABLE_SUFFIX WHERE setName='$safeName';";
	
	$result = $db->query($query);
	if ($result === false) exit("Error: Failed to load results for set '$safeName'!\n$query\n");
	
	if ($result->num_rows == 0)
	{
		print("$setName:\n");
		print("\tNo items found!");
		continue;
	}
	
	$items = array();
	
	while ($row = $result->fetch_assoc())
	{
		$itemType = intval($row['type']);
		if ($itemType == 18) continue;
		
		$itemId = $row['itemId'];
		$items[$itemId] = $row;
	}
	
	$setDescCounts = array( 1 => [], 2 => [], 3 => [], 4 => [], 5 => [], 6 => [], 7 => [], 8 => [], 9 => [], 10 => [], 11 => [], 12 => []);
	$setDescIds = array( 1 => [], 2 => [], 3 => [], 4 => [], 5 => [], 6 => [], 7 => [], 8 => [], 9 => [], 10 => [], 11 => [], 12 => []);
	
	foreach ($items as $itemId => $item)
	{
		for ($i = 1; $i <= 12; ++$i)
		{
			$desc = strtolower($item["setBonusDesc$i"]);
			$desc = str_replace("|cffffff", "", $desc);
			$desc = str_replace("|r", "", $desc);
			
			if ($REMOVE_NUMBERS)
			{
				$desc = preg_replace('#[0-9]#', '', $desc);
			}
			
			$setDescCounts[$i][$desc] += 1;
			$setDescIds[$i][$desc][] = $itemId;
		}
	}
	
	$printedName = false;
	
	for ($i = 1; $i <= 12; ++$i)
	{
		$count = count($setDescCounts[$i]);
		
		if ($count > 1)
		{
			$minCount = 10000;
			$minItemIds = [];
			
			foreach ($setDescCounts[$i] as $setDesc => $setCount)
			{
				$count1 = count($setDescIds[$i][$setDesc]);
				if ($VERBOSE) print("$i : $setCount / $count1 = $setDesc\n");
				
				if ($count1 > 0 && $count1 < $minCount)
				{
					$minCount = $count1;
					$minItemIds = $setDescIds[$i][$setDesc];
				}
			}
			
			$ids = implode(",", $minItemIds);
			//$fixIds = array_merge($fixIds, $minItemIds);
			
			foreach ($minItemIds as $itemId)
			{
				$fixIds[$itemId] += 1;
			}
			
			if (!$printedName) 
			{
				$printedName = true;
				print("$setName:\n");
			}
			
			print("\tsetDesc$i has $count different descriptions ($ids)!\n");
		}
	}
	
}

ksort($fixIds);
$count = count($fixIds);
print("Found $count items that may need to be fixed:\n");
$luaFunctionCount = 1;
$output = "function uespminetest1()\n";

foreach ($fixIds as $itemId => $count)
{
	print("\t$itemId\n");
	
	$output .= "\tuespLog.MineSingleItemSafe($itemId)\n";
	++$luaFunctionCount;
	$output .= "end\nfunction uespminetest$luaFunctionCount()\n";
}

$output .= "end\n";

if ($OUTPUT_FIXITEMS)
{
	file_put_contents("fixitems.lua", $output);
}


