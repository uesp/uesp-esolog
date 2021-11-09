<?php 

if (php_sapi_name() != "cli") die("Can only be run from command line!");

require("/home/uesp/secrets/esolog.secrets");

$db = new mysqli($uespEsoLogReadDBHost, $uespEsoLogReadUser, $uespEsoLogReadPW, $uespEsoLogDatabase);
if ($db->connect_error) exit("Could not connect to mysql database!");

print("Finding all mismatched item summaries in mined item data...\n");

$TABLE_SUFFIX = "32pts";

$linesOutput = 0;
$luaFunctionCount = 1;

$MAXBADITEMCOUNT = 1450;
$NUMCALLSPERFUNCTION = 300;

$result = $db->query("SELECT * FROM minedItemSummary$TABLE_SUFFIX ORDER BY itemId;");
if ($result === false) die("Error: Failed to load minedItemSummary$TABLE_SUFFIX!\n");

$badItems = array();

while ($item = $result->fetch_assoc())
{
	$isBad = false;
	$itemId = $item['itemId'];
	
	if (($itemId % 1000) == 0) 
	{
		$count = count($badItems);
		print("\t$itemId: Checking for bad item summaries ($count bad items found so far)...\n");
	}
	
	$equipType = $item['equipType'];
	$weaponType = $item['weaponType'];
	$armorType = $item['armorType'];
	$itemType = $item['type'];
	$setName = $item['setName'];
	$setBonusDesc1 = $item['setBonusDesc1'];
	/*
	 * EquipType
		1 => "Head",
		2 => "Neck",
		3 => "Chest",
		4 => "Shoulders",
		5 => "One Hand",
		6 => "Two Hand",
		7 => "Off Hand",
		8 => "Waist",
		9 => "Legs",
		10 => "Feet",
		11 => "Costume",
		12 => "Ring",
		13 => "Hand",
		14 => "Main Hand",
	 */
	
	if ($itemType == 1) // Weapon
	{
		if ($equipType != 5 && $equipType != 6 && $equipType != 7)
		{
			print("\t$itemId: Non-zero equipType $equipType found for weapon item!\n");
			$isBad = true;
		}
		
		if ($armorType > 0)
		{
			print("\t$itemId: Non-zero armorType $armorType found for weapon item!\n");
			$isBad = true;
		}
	}
	else if ($itemType == 2)	// Armor
	{
		if ($equipType != 1 && $equipType != 2 && $equipType != 3 && $equipType != 4 && $equipType != 8 && $equipType != 9 && $equipType != 10 && $equipType != 12 && $equipType != 13 && $equipType != 11)
		{
			print("\t$itemId: Non-zero equipType $equipType found for weapon item!\n");
			$isBad = true;
		}
		
		if ($weaponType > 0)
		{
			print("\t$itemId: Non-zero weaponType $weaponType found for armor item!\n");
			$isBad = true;
		}
	}
	else if ($itemType == 14 || $itemType == 15)	// Costume, Disguise, Tabard
	{
		if ($equipType != 15 && $equipType != 11)
		{
			print("\t$itemId: Non-zero equipType $equipType found for costume item!\n");
			$isBad = true;
		}
		
		if ($armorType > 0)
		{
			print("\t$itemId: Non-zero armorType $armorType found for costume item!\n");
			$isBad = true;
		}
		
		if ($weaponType > 0)
		{
			print("\t$itemId: Non-zero weaponType $weaponType found for costume item!\n");
			$isBad = true;
		}
	}
	else if ($itemType == 30)	// Poison
	{
		if ($equipType != 15)
		{
			print("\t$itemId: Non-zero equipType $equipType found for poison item!\n");
			$isBad = true;
		}
		
		if ($armorType > 0)
		{
			print("\t$itemId: Non-zero armorType $armorType found for poison item!\n");
			$isBad = true;
		}
		
		if ($weaponType > 0)
		{
			print("\t$itemId: Non-zero weaponType $weaponType found for poison item!\n");
			$isBad = true;
		}
	}
	else
	{
		if ($equipType > 0)
		{
			print("\t$itemId: Non-zero equipType $equipType found for non-weapon/armor item!\n");
			$isBad = true;
		}
		
		if ($armorType > 0)
		{
			print("\t$itemId: Non-zero armorType $armorType found for non-weapon/armor item!\n");
			$isBad = true;
		}
		
		if ($weaponType > 0)
		{
			print("\t$itemId: Non-zero weaponType $weaponType found for non-weapon/armor item!\n");
			$isBad = true;
		}
	}
	
	if ($setName != "" && $setBonusDesc1 == "")
	{
			print("\t$itemId: Set name with empty set bonus desc!\n");
			$isBad = true;
	}
	
	if ($isBad) 
	{
		$badItems[] = $itemId;
	}
}

$count = count($badItems); 
print("\tOutput $count bad items to fixitems.lua...\n");
$output = "";

foreach ($badItems as $itemId)
{
	if ($linesOutput % $NUMCALLSPERFUNCTION == 0)
	{
		if ($linesOutput != 0) $output .= "end\n";
		$output .= "function uespminetest$luaFunctionCount()\n";
		++$luaFunctionCount;
	}
	
	$output .= "\tuespLog.MineItemSingle($itemId, 1, 1) \n";
	
	++$linesOutput;
}

if ($linesOutput != 0) $output .= "end\n";
file_put_contents("fixitems.lua", $output, FILE_APPEND);

