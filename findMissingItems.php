<?php 

if (php_sapi_name() != "cli") die("Can only be run from command line!");

require("/home/uesp/secrets/esolog.secrets");

$db = new mysqli($uespEsoLogReadDBHost, $uespEsoLogReadUser, $uespEsoLogReadPW, $uespEsoLogDatabase);
if ($db->connect_error) exit("Could not connect to mysql database!");

$TABLEPREFIX = "20";
$VERSION = "20";
$FIRSTID = 3;
$LASTID = 160000;
$MAGICCOUNT = 1483;
$MAGICCOUNT = 1533;

$luaFunctionCount = 1;
$MAX_ITEMS_PER_FUNCTION = 1;

$checkData = array();

$query = "SELECT * FROM itemIdCheck WHERE version='$VERSION';";
$result = $db->query($query);
if (!$result) exit("ERROR: Database query error (finding item check data)!\n" . $db->error);

while ($row = $result->fetch_assoc())
{
	$checkData[$row['itemId']] = $row;	
}

print("Found ".count($checkData)." check item rows!\n");

$output = "function uespminetest$luaFunctionCount()\n";

for ($id = $FIRSTID; $id <= $LASTID; $id++)
{
	if ($id % 1000 == 0) print("Checking Item $id...\n");
	
	$idCheck = $checkData[$id];
	
	$query = "SELECT count(*) as count FROM minedItem$TABLEPREFIX WHERE itemId=$id;";
	$result = $db->query($query);
	if (!$result) exit("ERROR: Database query error (finding item count)!\n" . $db->error);
	$itemData = $result->fetch_assoc();
	$itemCount = $itemData['count'];
	$fixItem = false;
	
	if ($idCheck != null)
	{
		if ($itemCount == 0)
		{
			print("\t$id: Missing complete item!\n");
			$fixItem = true;
		}
		else if ($itemCount != 1 && $itemCount < $MAGICCOUNT)
		{
			print("\t$id: Missing partial item data ($itemCount records)!\n");
			$fixItem = true;
		}
	}
	else if ($idCheck == null && $itemCount > 0)
	{
		print("\t$id: Item data where it should be missing ($itemCount)!\n");
	}
		
	if ($fixItem)
	{
		$output .= "\tuespLog.MineItems($id, $id) \n";
		++$linesOutput;
				
		if (($linesOutput % $MAX_ITEMS_PER_FUNCTION) == 0)
		{
			$output .= "\tuespLog.Msg('Done uespminetest$luaFunctionCount...')\n";
			++$luaFunctionCount;			
			$output .= "\tzo_callLater(uespminetest$luaFunctionCount, 1000)\n";
			$output .= "end\n";
			$output .= "function uespminetest$luaFunctionCount()\n";					
		}
		
	}
	
}

$output .= "\tuespLog.Msg('Done fixing mined items...')\n";
$output .= "end\n";
file_put_contents("fixitems.lua", $output, 0);

