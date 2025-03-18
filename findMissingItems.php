<?php 

if (php_sapi_name() != "cli") die("Can only be run from command line!");

require("/home/uesp/secrets/esolog.secrets");

$db = new mysqli($uespEsoLogReadDBHost, $uespEsoLogReadUser, $uespEsoLogReadPW, $uespEsoLogDatabase);
if ($db->connect_error) exit("Could not connect to mysql database!");

$TABLEPREFIX = "45";
$VERSION = "45";
$FIRSTID = 3;
$INCLUDEENDFUNC = true;
$LASTID = 220000;
//$MAGICCOUNT = 1483;
//$MAGICCOUNT = 1533;
$MAGICCOUNT = 1532;
//$MAGICCOUNT = 8;	//PTS

$luaFunctionCount = 1;
$MAX_ITEMS_PER_FUNCTION = 1;
$MAX_CALLS_PER_FUNCTION = 500;
$linesOutput = 0;
$itemsOutput = 0;
$totalLinesOutput = 0;

$checkData = array();

$query = "SELECT * FROM itemIdCheck WHERE version='$VERSION';";
$result = $db->query($query);
if (!$result) exit("ERROR: Database query error (finding item check data)!\n" . $db->error);

while ($row = $result->fetch_assoc())
{
	$checkData[$row['itemId']] = $row;
}

print("Found ".count($checkData)." check item rows!\n");

$output = "";
$del_output = "";
$output .= "uespLog.MineSingleItemSafe_FinishCallback = uespLog.StartNextMineTest\n";
$output .= "function uespminetest$luaFunctionCount()\n";

$HAS_ITEM_SUBIDS = false;
$itemSubIds = array();

file_put_contents("fixitems.lua", $output, 0);
$output = "";

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
	$fixItemIds = array();
	
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
			
			if ($HAS_ITEM_SUBIDS)
			{
				$query = "SELECT internalLevel, internalSubtype FROM minedItem$TABLEPREFIX WHERE itemId=$id;";
				$result = $db->query($query);
				if (!$result) exit("ERROR: Database query error (loading item subids)!\n" . $db->error);
				
				$itemSubIdsDiff = array();
				
				while ($row = $result->fetch_assoc())
				{
					$subid = $row['internalLevel'] . ":" . $row['internalSubtype'];
					$itemSubIdsDiff[$subid] = 1;
				}
				
				$fixItemIds = array_diff_key($itemSubIds, $itemSubIdsDiff);
				$fixItem = true;
				
				//$count1 = count($itemSubIds);
				//$count2 = count($itemSubIdsDiff);
				//$count3 = count($fixItemIds);
				//print("\t\tDiff Counts: $count1, $count2, $count3\n");
			}
			else
			{
				$fixItem = true;
			}
		}
		else if (!$HAS_ITEM_SUBIDS)
		{
			$query = "SELECT internalLevel, internalSubtype FROM minedItem$TABLEPREFIX WHERE itemId=$id;";
			$result = $db->query($query);
			if (!$result) exit("ERROR: Database query error (loading item subids)!\n" . $db->error);
			
			//print("\t\tLoaded {$result->num_rows} subids...\n");
			
			while ($row = $result->fetch_assoc())
			{
				$subid = $row['internalLevel'] . ":" . $row['internalSubtype'];
				$itemSubIds[$subid] = 1;
			}
			
			$count = count($itemSubIds);
			
			if ($count < $MAGICCOUNT)
			{
				$itemSubIds = array();
			}
			else
			{
				print("\t$id: Loaded $count subids!\n");
				$HAS_ITEM_SUBIDS = true;
			}
		}
	}
	else if ($idCheck == null && $itemCount > 0)
	{
		print("\t$id: Item data where it should be missing ($itemCount)!\n");
		$del_output .= "$id\n";
	}
	
	if ($fixItem)
	{
		if (count($fixItemIds) > 0)
		{
			foreach ($fixItemIds as $itemSubId => $index)
			{
				$splitIds = explode(":", $itemSubId);
				$internalLevel = $splitIds[0];
				$internalSubtype = $splitIds[1];
				
				$output .= "\tuespLog.MineItemSingle($id, $internalLevel, $internalSubtype)\n";
				++$itemsOutput;
				++$totalLinesOutput;
			}
		}
		else
		{
			$output .= "\tuespLog.MineSingleItemSafe($id) \n";
			++$linesOutput;
			++$totalLinesOutput;
			$itemsOutput += $MAGICCOUNT;
		}
		
		if ($itemsOutput > $MAX_CALLS_PER_FUNCTION)
		{
			//$output .= "\tuespLog.Msg('Done uespminetest$luaFunctionCount...')\n";
			//$output .= "\tzo_callLater(uespminetest$luaFunctionCount, 1000)\n";
			
			++$luaFunctionCount;
			$itemsOutput = 0;
			
			if ($INCLUDEENDFUNC) $output .= "\tuespLog.EndMineTestFunction()\n";
			$output .= "end\n";
			$output .= "function uespminetest$luaFunctionCount()\n";
			
			file_put_contents("fixitems.lua", $output, FILE_APPEND);
			$output = "";
		}
	}
}

$output .= "\tuespLog.Msg('Done fixing mined items...')\n";
$output .= "end\n";

file_put_contents("fixitems.lua", $output, FILE_APPEND);

if ($del_output != "")
{
	file_put_contents("delitems.txt", $del_output, 0);
}


print("Output $totalLinesOutput lines to fixitems.lua!\n");