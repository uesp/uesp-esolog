<?php 

if (php_sapi_name() != "cli") die("Can only be run from command line!");

require("/home/uesp/secrets/esolog.secrets");

$db = new mysqli($uespEsoLogReadDBHost, $uespEsoLogReadUser, $uespEsoLogReadPW, $uespEsoLogDatabase);
if ($db->connect_error) exit("Could not connect to mysql database!");

print("Finding all mismatched item names in mined item data...\n");

$linesOutput = 0;
$luaFunctionCount = 1;

$START_ID = 3;
$END_ID = 150000;

//$START_ID = 116747;

for ($itemId = $START_ID; $itemId <= $END_ID; ++$itemId)
{
	if (($itemId % 1000) == 0) print("\t$itemId: Checking for mismatched item names.\n");
	
	$query = "SELECT name, internalLevel, internalSubtype from minedItem WHERE itemId=$itemId;";
	$result = $db->query($query);
	
	if ($result->num_rows <= 1) continue;
	
	$items = array();
	$nameCount = array();
	
	while (($row = $result->fetch_assoc()))
	{
		$name = $row['name'];
		
		if ($items[$name] == null) $items[$name] = array();
		$items[$name][] = $row;
		
		if ($nameCount[$name] == null) $nameCount[$name] = 0;
		$nameCount[$name] += 1;
	}
	
	$numNames = count($nameCount);
	if ($numNames <= 1) continue;
	
	arsort($nameCount);
	print("\t$itemId: Has $numNames different names:\n");
	$maxCount = 0;
	
	foreach ($nameCount as $name => $count)
	{
		$itemData = $items[$name];
		$ids = "";
		if ($maxCount < $count) $maxCount = $count;
				
		if (count($itemData) < 20)
		{
			
			foreach ($itemData as $itemCount => $data)
			{
				$ids = $data['internalLevel'] . ":" . $data['internalSubtype'] . ", "; 
			}
		}
		
		print("\t\t$count: $name ($ids)\n");
	}
	
	if ($maxCount > 1400)
	{
		$output = "";
		
		if ($linesOutput == 0)
		{
			$output .= "function uespminetest$luaFunctionCount()\n";
		}
	
		foreach ($nameCount as $name => $count)
		{
			if ($count >= $maxCount) continue;
			$itemData = $items[$name];
		
			foreach ($itemData as $itemCount => $data)
			{
				$output .= "\tuespLog.MineItemSingle($itemId, {$data['internalLevel']}, {$data['internalSubtype']}) \n";
				++$linesOutput;
				
				if (($linesOutput % 1500) == 0)
				{
					++$luaFunctionCount;
					$output .= "end\nfunction uespminetest$luaFunctionCount()\n";					
				}
			}
		}

		file_put_contents("fixitems.lua", $output, FILE_APPEND);
	}
	
}


				
