<?php 

if (php_sapi_name() != "cli") die("Can only be run from command line!");

require("/home/uesp/secrets/esolog.secrets");

$db = new mysqli($uespEsoLogReadDBHost, $uespEsoLogReadUser, $uespEsoLogReadPW, $uespEsoLogDatabase);
if ($db->connect_error) exit("Could not connect to mysql database!");

print("Finding all mismatched item set names in mined item data...\n");

$TABLE_SUFFIX = "";
$linesOutput = 0;
$luaFunctionCount = 1;

$START_ID = 1;
$END_ID = 150000;

$output = "function uespminetest1()\n";
file_put_contents("fixitems.lua", $output, FILE_APPEND);

$query = "SELECT itemId, setName from minedItemSummary$TABLE_SUFFIX;";
$result = $db->query($query);
	
if ($result->num_rows < 1) exit("Error querying mineditem data!\n");
$setNames = array();

while (($row = $result->fetch_assoc()))
{
	$itemId = $row['itemId'];
	$setNames[$itemId] = $row['setName'];
}

for ($itemId = $START_ID; $itemId <= $END_ID; ++$itemId)
{
	if (($itemId % 1000) == 0) print("\t$itemId: Checking for mismatched item set names ($linesOutput bad items found so far)...\n");
	
	$setName = $setNames[$itemId];
	if ($setName == null) continue;
	
	$query = "SELECT setName, internalLevel, internalSubtype from minedItem$TABLE_SUFFIX WHERE itemId=$itemId;";
	$result = $db->query($query);
	
	if ($result->num_rows < 1) continue;
	
	$items = array();
	$nameCount = array();
	$prelinesOutput = $linesOutput;
	
	while (($row = $result->fetch_assoc()))
	{
		$setName2 = $row['setName'];
		if (strcasecmp($setName2, $setName) == 0) continue;
		
		//print ("{$row['internalLevel']}, {$row['internalSubtype']}, $setName2, $setName\n");
		
		$output = "\tuespLog.MineItemSingle($itemId, {$row['internalLevel']}, {$row['internalSubtype']}) \n";
		++$linesOutput;
				
		if (($linesOutput % 1500) == 0)
		{
			++$luaFunctionCount;
			$output .= "end\nfunction uespminetest$luaFunctionCount()\n";					
		}
		
		file_put_contents("fixitems.lua", $output, FILE_APPEND);
	}
	
	$diffLinesOutput = $linesOutput - $prelinesOutput; 
	if ($diffLinesOutput > 0) print("\t$itemId: found $diffLinesOutput bad records!\n");	
}

$output = "end\n";
file_put_contents("fixitems.lua", $output, FILE_APPEND);


				
