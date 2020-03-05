<?php

$TABLE_SUFFIX = "25";
$SOURCEITEMTABLE = "Summary";
$KEEPONLYNEWSETS = false;
$REMOVEDUPLICATES = true;

if (php_sapi_name() != "cli") die("Can only be run from command line!");
print("Updating item set data from mined item summaries...\n");

require("/home/uesp/secrets/esolog.secrets");


function TransformBonusDesc($desc)
{
	$newDesc = preg_replace('/\|c[0-9a-fA-F]{6}([^|]+)\|r/', '$1', $desc);
	//$newDesc = preg_replace('/\n/', ' ', $newDesc);
	return $newDesc;
}


function GetItemArmorTypeText ($value)
{
	static $VALUES = array(
			-1 => "",
			0 => "",
			1 => "Light",
			2 => "Medium",
			3 => "Heavy",
	);

	$key = (int) $value;

	if (array_key_exists($key, $VALUES)) return $VALUES[$key];
	return "$key?";
}


function GetItemWeaponTypeText ($value)
{
	static $VALUES = array(
			-1 => "",
			0 => "",
			1 => "Axe",
			2 => "Hammer",
			3 => "Sword",
			4 => "2HSword",
			5 => "2HAxe",
			6 => "2HHammer",
			7 => "Prop",
			8 => "Bow",
			9 => "HealStaff",
			10 => "Rune",
			11 => "Dagger",
			12 => "FireStaff",
			13 => "FrostStaff",
			14 => "Shield",
			15 => "LightStaff",
	);
	
	$key = (int) $value;
	
	if (array_key_exists($key, $VALUES)) return $VALUES[$key];
	return "$key?";
}


function GetItemEquipTypeText ($value)
{
	static $VALUES = array(
			-1 => "",
			0 => "",
			1 => "Head",
			2 => "Neck",
			3 => "Chest",
			4 => "Shoulder",
			5 => "OneHand",
			6 => "TwoHand",
			7 => "OffHand",
			8 => "Waist",
			9 => "Leg",
			10 => "Feet",
			11 => "Costume",
			12 => "Ring",
			13 => "Hand",
			14 => "MainHand",
	);

	$key = (int) $value;

	if (array_key_exists($key, $VALUES)) return $VALUES[$key];
	return "$key?";
}


function GetItemTypeText ($value)
{
	static $VALUES = array(
			-1 => "",
			11 => "additive",
			33 => "alchemy_base",
			2 => "armor",
			24 => "armor_booster",
			45 => "armor_trait",
			47 => "ava_repair",
			41 => "blacksmithing_booster",
			36 => "blacksmithing_material",
			35 => "blacksmithing_raw_material",
			43 => "clothier_booster",
			40 => "clothier_material",
			39 => "clothier_raw_material",
			34 => "collectible",
			18 => "container",
			13 => "costume",
			14 => "disguise",
			12 => "drink",
			32 => "enchanting_rune",
			25 => "enchantment_booster",
			28 => "flavoring",
			4 => "food",
			21 => "glyph_armor",
			26 => "glyph_jewelry",
			20 => "glyph_weapon",
			10 => "ingredient",
			22 => "lockpick",
			16 => "lure",
			0 => "none",
			3 => "plug",
			30 => "poison",
			7 => "potion",
			17 => "raw_material",
			31 => "reagent",
			29 => "recipe",
			8 => "scroll",
			6 => "siege",
			19 => "soul_gem",
			27 => "spice",
			44 => "style_material",
			15 => "tabard",
			9 => "tool",
			48 => "trash",
			5 => "trophy",
			1 => "weapon",
			23 => "weapon_booster",
			46 => "weapon_trait",
			42 => "woodworking_booster",
			38 => "woodworking_material",
			37 => "woodworking_raw_material",
			49 => "spellcrafting_tablet",
			50 => "mount",
			51 => "potency_rune",
			52 => "aspect_rune",
			53 => "essence_rune",
	);

	$key = (int) $value;

	if (array_key_exists($key, $VALUES)) return $VALUES[$key];
	return "$key?";
}


function JoinArrayKeys ($array)
{
	$output = "";
	
	foreach($array as $key => $value)
	{
		if ($output != "") $output .= " ";
		$output = $output . $key;
	}
	
	return $output;
}


function CreateItemSlotString ($setSlots)
{
	$output = "";
	
	foreach($setSlots as $key => $value)
	{
		if ($output != "") $output .= " ";
		
		if ($key == "Heavy" || $key == "Medium" || $key == "Light")
		{
			if (count($value) >= 7)
				$output = $output . $key . "(All)";
			else
				$output = $output . $key . "(" . JoinArrayKeys($value) . ")";
		}
		elseif ($key == "Weapons")
		{
			if (count($value) >= 12)
				$output = $output . $key . "(All)";
			else
				$output = $output . $key . "(" . JoinArrayKeys($value) . ")";
		}
		else
		{
			$output = $output . $key;
		}
	}
	
	return $output;
}


function UpdateItemSlotArray (&$outputArray, $item)
{
	$itemName = $item['name'];
	
	$type = $item['type'];
	$weaponType = $item['weaponType'];
	$armorType = $item['armorType'];
	$equipType = $item['equipType'];
	$typeText = GetItemTypeText($type);
	$armorTypeText = GetItemArmorTypeText($armorType);
	$equipTypeText = GetItemEquipTypeText($equipType);
	$weaponTypeText = GetItemWeaponTypeText($weaponType);
	
	$output = &$outputArray;
	
	if ($armorTypeText != "") 
	{
		if (!array_key_exists($armorTypeText, $outputArray)) $outputArray[$armorTypeText] = array();
		$output = &$outputArray[$armorTypeText];
		
		if ($equipTypeText != "")
		{
			$output[$equipTypeText] = 1;
		}
	}
	else if ($weaponTypeText != "")
	{
		if ($weaponTypeText == "Shield")
		{
			$output["Shield"] = 1;
		}
		else
		{
			if (!array_key_exists("Weapons", $outputArray)) $outputArray["Weapons"] = array();
			$output = &$outputArray["Weapons"];
			$output[$weaponTypeText] = 1;
		}
	}
	elseif ($equipTypeText != "") 
	{
		$output[$equipTypeText] = 1;
	}
	elseif ($typeText != "")
	{
		$output[$typeText] = 1;
	}
	
}


$db = new mysqli($uespEsoLogWriteDBHost, $uespEsoLogWriteUser, $uespEsoLogWritePW, $uespEsoLogDatabase);
if ($db->connect_error) exit("Could not connect to mysql database!");

$query = "CREATE TABLE IF NOT EXISTS setSummary".$TABLE_SUFFIX."(
			id BIGINT NOT NULL AUTO_INCREMENT PRIMARY KEY,
			setName TINYTEXT NOT NULL,
			setMaxEquipCount TINYINT NOT NULL DEFAULT 0,
			setBonusCount TINYINT NOT NULL DEFAULT 0,
			itemCount INTEGER NOT NULL DEFAULT 0,
			setBonusDesc1 TEXT NOT NULL,
			setBonusDesc2 TEXT NOT NULL,
			setBonusDesc3 TEXT NOT NULL,
			setBonusDesc4 TEXT NOT NULL,
			setBonusDesc5 TEXT NOT NULL,
			setBonusDesc TEXT NOT NULL,
			itemSlots TEXT NOT NULL,
			FULLTEXT(setName, setBonusDesc1, setBonusDesc2, setBonusDesc3, setBonusDesc4, setBonusDesc5)
		);";

$result = $db->query($query);
if (!$result) exit("ERROR: Database query error creating table!\n" . $db->error);

$query = "DELETE FROM setSummary".$TABLE_SUFFIX.";";
$result = $db->query($query);
if (!$result) exit("ERROR: Database query error deleting table!\n" . $db->error);

//$query = "UPDATE setSummary".$TABLE_SUFFIX." SET itemCount=0;";
//$result = $db->query($query);
//if (!$result) exit("ERROR: Database query error (clearing item counts)!\n" . $db->error);

$query = "SELECT * FROM minedItem".$SOURCEITEMTABLE.$TABLE_SUFFIX." WHERE setName!='';";
$rowResult = $db->query($query);
if (!$rowResult) exit("ERROR: Database query error (finding set items)!\n" . $db->error);
$rowResult->data_seek(0);

$itemCount = 0;
$updateCount = 0;
$newCount = 0;
$setItemSlots = array();

while (($row = $rowResult->fetch_assoc()))
{
	++$itemCount;
	$setName = $row['setName'];
	$setBonusDesc1 = TransformBonusDesc($row['setBonusDesc1']);
	$setBonusDesc2 = TransformBonusDesc($row['setBonusDesc2']);
	$setBonusDesc3 = TransformBonusDesc($row['setBonusDesc3']);
	$setBonusDesc4 = TransformBonusDesc($row['setBonusDesc4']);
	$setBonusDesc5 = TransformBonusDesc($row['setBonusDesc5']);
	$setBonusCount = 0;
	$setMaxEquipCount = $row['setMaxEquipCount'];
	if ($setMaxEquipCount == null || $setMaxEquipCount == "") $setMaxEquipCount = 1; 
	
	$lastBonusDesc = $setBonusDesc5;
	
	if ($lastBonusDesc == "") $lastBonusDesc = $setBonusDesc4;
	if ($lastBonusDesc == "") $lastBonusDesc = $setBonusDesc3;
	if ($lastBonusDesc == "") $lastBonusDesc = $setBonusDesc2;
	if ($lastBonusDesc == "") $lastBonusDesc = $setBonusDesc1;
	
	if ($setBonusDesc1 != "") $setBonusCount = 1;
	if ($setBonusDesc2 != "") $setBonusCount = 2;
	if ($setBonusDesc3 != "") $setBonusCount = 3;
	if ($setBonusDesc4 != "") $setBonusCount = 4;
	if ($setBonusDesc5 != "") $setBonusCount = 5;
	
	if (!array_key_exists($setName, $setItemSlots)) $setItemSlots[$setName] = array();
	UpdateItemSlotArray($setItemSlots[$setName], $row);
	
	$matches = array();
	$regResult = preg_match('/\(([0-9]+) items\)/', $lastBonusDesc, $matches);
	if ($regResult) $setMaxEquipCount = $matches[1];
	
	print("\tUpdating set $setName with $setMaxEquipCount items...\n");
	//print("\t\t$setBonusDesc1 == " . $row['setBonusDesc1'] . "\n");
	
	$query = "SELECT * FROM setSummary".$TABLE_SUFFIX." WHERE setName=\"$setName\";";
	$result = $db->query($query);
	if (!$result) exit("ERROR: Database query error finding set!\n" . $db->error);
	
	$createNewSet = true;
	$updateId = -1;
	
	while ( ($newRow = $result->fetch_assoc()) )
	{
		$matches = true;
		
		$newBonusDesc1 = preg_replace('/\|c[0-9a-fA-F]{6}([a-zA-Z0-9\.\-\%\s]+)\|r/', '$1', $newRow['setBonusDesc1']);
		$newBonusDesc2 = preg_replace('/\|c[0-9a-fA-F]{6}([a-zA-Z0-9\.\-\%\s]+)\|r/', '$1', $newRow['setBonusDesc2']);
		$newBonusDesc3 = preg_replace('/\|c[0-9a-fA-F]{6}([a-zA-Z0-9\.\-\%\s]+)\|r/', '$1', $newRow['setBonusDesc3']);
		$newBonusDesc4 = preg_replace('/\|c[0-9a-fA-F]{6}([a-zA-Z0-9\.\-\%\s]+)\|r/', '$1', $newRow['setBonusDesc4']);
		$newBonusDesc5 = preg_replace('/\|c[0-9a-fA-F]{6}([a-zA-Z0-9\.\-\%\s]+)\|r/', '$1', $newRow['setBonusDesc5']);
		
		if ($newBonusDesc1 != $setBonusDesc1) { $matches = true; print("\t\tSet bonus #1 doesn't match!\n"); }
		if ($newBonusDesc2 != $setBonusDesc2) { $matches = true; print("\t\tSet bonus #2 doesn't match!\n"); }
		if ($newBonusDesc3 != $setBonusDesc3) { $matches = true; print("\t\tSet bonus #3 doesn't match!\n"); }
		if ($newBonusDesc4 != $setBonusDesc4) { $matches = true; print("\t\tSet bonus #4 doesn't match!\n"); }
		if ($newBonusDesc5 != $setBonusDesc5) { $matches = true; print("\t\tSet bonus #5 doesn't match!\n"); }
		if ($newRow['setMaxEquipCount'] != $setMaxEquipCount) { $matches = false; print("\t\tSet max equip count doesn't match!\n"); }
		
		if ($matches) 
		{
			$updateId = $newRow['id'];
			$createNewSet = false;
			break;
		}
	}
	
	if ($createNewSet)
	{
		print("\t\tCreating new set...\n");
		++$newCount;
		
		$setBonusDesc = "";
		if ($setBonusDesc1 != "") $setBonusDesc .= $setBonusDesc1;
		if ($setBonusDesc2 != "") $setBonusDesc .= "\n".$setBonusDesc2;
		if ($setBonusDesc3 != "") $setBonusDesc .= "\n".$setBonusDesc3;
		if ($setBonusDesc4 != "") $setBonusDesc .= "\n".$setBonusDesc4;
		if ($setBonusDesc5 != "") $setBonusDesc .= "\n".$setBonusDesc5;
		
		$query  = "INSERT INTO setSummary".$TABLE_SUFFIX."(setName, setMaxEquipCount, setBonusCount, itemCount, setBonusDesc1, setBonusDesc2, setBonusDesc3, setBonusDesc4, setBonusDesc5, setBonusDesc) ";
		$query .= "VALUES(\"$setName\", $setMaxEquipCount, $setBonusCount, 1, \"$setBonusDesc1\", \"$setBonusDesc2\", \"$setBonusDesc3\", \"$setBonusDesc4\", \"$setBonusDesc5\", \"$setBonusDesc\");";
		
		$result = $db->query($query);
		if (!$result) exit("ERROR: Database query error inserting into table!\n" . $db->error . "\n" . $query);
	}
	else if ($updateId > 0)
	{
		print("\t\tUpdating set $updateId...\n");
		++$updateCount;
		$query = "UPDATE setSummary".$TABLE_SUFFIX." SET itemCount=itemCount+1 WHERE id=$updateId;";
		$result = $db->query($query);
		if (!$result) exit("ERROR: Database query error updating table!\n" . $db->error . "\n" . $query);
	}
	else
	{
		print("\t\tError: Unknown set record to update!\n");
	}
	
}

print("\tUpdating set item slots...\n");

foreach ($setItemSlots as $setName => $setSlots)
{
	$slotString = CreateItemSlotString($setSlots);
	$query = "UPDATE setSummary".$TABLE_SUFFIX." SET itemSlots='".$slotString."' WHERE setName=\"".$setName."\";";
	$result = $db->query($query);
	if (!$result) exit("ERROR: Database query error updating table!\n" . $db->error . "\n" . $query);
	//print("$setName: $slotString\n");
}

print("Found $itemCount item sets, $newCount new, $updateCount duplicate!\n");

if ($KEEPONLYNEWSETS && $TABLE_SUFFIX != "")
{
	print("\tDeleting existing sets in setSummary...\n");
	
	$query = "DELETE setSummary$TABLE_SUFFIX FROM setSummary$TABLE_SUFFIX LEFT JOIN setSummary on setSummary{$TABLE_SUFFIX}.setName = setSummary.setName WHERE setSummary.setName IS NOT NULL;";
	$result = $db->query($query);
	if (!$result) exit("ERROR: Database query error deleting old sets!\n" . $db->error . "\n" . $query);
	
	print("\tDeleting old sets...OK!\n");
}

if ($REMOVEDUPLICATES)
{
	print("\tRemoving duplicates...\n");
	
	$query = "SELECT *, COUNT(*) c, GROUP_CONCAT(id) ids, GROUP_CONCAT(itemCount) itemCounts FROM setSummary$TABLE_SUFFIX GROUP BY setName HAVING c > 1;";
	$rowResult = $db->query($query);
	if (!$rowResult) exit("ERROR: Database query error finding duplicate sets!\n" . $db->error . "\n" . $query);
	
	while (($row = $rowResult->fetch_assoc()))
	{
		$setName = $row['setName'];
		$count = $row['c'];
		$id = $row['id'];
		$ids = explode(",", $row['ids']);
		$itemCounts = explode(",", $row['itemCounts']);
		
		print("\t\tFound duplicate set $setName ($c records, '{$row['ids']}', '{$row['itemCounts']}') \n");
		
		$minCounts = array_keys($itemCounts, min($itemCounts));
		$minCount = $minCounts[0];
		$minId = $ids[$minCount];
		print("\t\t\tDeleting record {$minId} with count {$itemCounts[$minCount]}...\n");
		
		$query = "DELETE FROM setSummary$TABLE_SUFFIX WHERE id=$minId;";
		$deleteResult =	$db->query($query);
		if (!$deleteResult) exit("ERROR: Database query error deleting duplicate sets!\n" . $db->error . "\n" . $query);
	}
}


