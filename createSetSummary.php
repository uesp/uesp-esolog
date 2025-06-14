<?php

$TABLE_SUFFIX = "46";
$SOURCEITEMTABLE = "Summary";
$KEEPONLYNEWSETS = false;
$REMOVEDUPLICATES = true;
$SHOW_ONLY_SET = "";
$QUIET = true;

if (php_sapi_name() != "cli") die("Can only be run from command line!");
print("Updating item set data from mined item summaries for version $TABLE_SUFFIX...\n");

require("/home/uesp/secrets/esolog.secrets");
require("esoCommon.php");

$options = getopt("dv");
if ($options['d'] != null || $options['v'] != null) $QUIET = false;


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
			2 => "Mace",
			3 => "Sword",
			4 => "Greatsword",
			5 => "Battleaxe",
			6 => "Maul",
			7 => "Prop",
			8 => "Bow",
			9 => "Resto",
			10 => "Rune",
			11 => "Dagger",
			12 => "Flame",
			13 => "Frost",
			14 => "Shield",
			15 => "Lightning",
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

$ESO_SETINDEX_MAP = array();

foreach ($ESO_SET_INDEXES as $setIndex => $setName)
{
	$setName = strtolower($setName);
	if ($ESO_SETINDEX_MAP[$setName] != null) print ("\tWarning: Duplicate set index $setIndex for '$setName'!\n");
	$ESO_SETINDEX_MAP[$setName] = $setIndex;
}

$db = new mysqli($uespEsoLogWriteDBHost, $uespEsoLogWriteUser, $uespEsoLogWritePW, $uespEsoLogDatabase);
if ($db->connect_error) exit("Could not connect to mysql database!");

$query = "DROP TABLE IF EXISTS setSummaryTmp;";
$result = $db->query($query);
if (!$result) print("Error: Failed to delete table setSummaryTmp!\n{$db->error}");

$query = "CREATE TABLE IF NOT EXISTS setSummaryTmp(
			id BIGINT NOT NULL AUTO_INCREMENT PRIMARY KEY,
			setName TINYTEXT NOT NULL DEFAULT '',
			indexName TINYTEXT NOT NULL DEFAULT '',
			setMaxEquipCount TINYINT NOT NULL DEFAULT 0,
			setBonusCount TINYINT NOT NULL DEFAULT 0,
			itemCount INTEGER NOT NULL DEFAULT 0,
			setBonusDesc1 TEXT NOT NULL DEFAULT '',
			setBonusDesc2 TEXT NOT NULL DEFAULT '',
			setBonusDesc3 TEXT NOT NULL DEFAULT '',
			setBonusDesc4 TEXT NOT NULL DEFAULT '',
			setBonusDesc5 TEXT NOT NULL DEFAULT '',
			setBonusDesc6 TEXT NOT NULL DEFAULT '',
			setBonusDesc7 TEXT NOT NULL DEFAULT '',
			setBonusDesc8 TEXT NOT NULL DEFAULT '',
			setBonusDesc9 TEXT NOT NULL DEFAULT '',
			setBonusDesc10 TEXT NOT NULL DEFAULT '',
			setBonusDesc11 TEXT NOT NULL DEFAULT '',
			setBonusDesc12 TEXT NOT NULL DEFAULT '',
			setBonusDesc TEXT NOT NULL DEFAULT '',
			itemSlots TEXT NOT NULL DEFAULT '',
			gameId INTEGER NOT NULL DEFAULT 0,
			type TINYTEXT NOT NULL DEFAULT '',
			category TINYTEXT NOT NULL DEFAULT '',
			sources TINYTEXT NOT NULL DEFAULT '',
			FULLTEXT(setName, setBonusDesc1, setBonusDesc2, setBonusDesc3, setBonusDesc4, setBonusDesc5, setBonusDesc6, setBonusDesc7, setBonusDesc8, setBonusDesc9, setBonusDesc10, setBonusDesc11, setBonusDesc12)
		) ENGINE=MYISAM;";

$result = $db->query($query);
if (!$result) exit("ERROR: Database query error creating table!\n" . $db->error);

$ESO_SETINDEX_MAP = array();

foreach ($ESO_SET_INDEXES as $setIndex => $setName)
{
	$setName = strtolower($setName);
	if ($ESO_SETINDEX_MAP[$setName] != null) print ("\tWarning: Duplicate set index $setIndex for '$setName'!\n");
	$ESO_SETINDEX_MAP[$setName] = $setIndex;
}

$count = count($ESO_SETINDEX_MAP);
print("Found $count set index records!\n");

$query = "SELECT * FROM setInfo;";
$rowResult = $db->query($query);
if (!$rowResult) exit("ERROR: Database query error (loading setInfo)!\n" . $db->error);
$rowResult->data_seek(0);

$setInfos = [];

while (($row = $rowResult->fetch_assoc()))
{
	$setName = strtolower($row['setName']);
	$setInfos[$setName] = $row;
}

$count = count($setInfos);
print("Loaded $count setInfo records!\n");

$query = "SELECT * FROM minedItem".$SOURCEITEMTABLE.$TABLE_SUFFIX." WHERE setName!='' ORDER BY itemId DESC;";
$rowResult = $db->query($query);
if (!$rowResult) exit("ERROR: Database query error (finding set items)!\n" . $db->error);
$rowResult->data_seek(0);

$itemCount = 0;
$updateCount = 0;
$newCount = 0;
$setItemSlots = array();

while (($row = $rowResult->fetch_assoc()))
{
	$QUIET_SET = $QUIET;
	
	$itemType = intval($row['type']);
	if ($itemType == 18) continue;	//Ignore containers?
	
	++$itemCount;
	$setName = $row['setName'];
	
	$indexName = strtolower($setName);
	$indexName = str_replace("'", "", $indexName);
	$indexName = str_replace(",", "", $indexName);
	$indexName = str_replace(" ", "-", $indexName);
	
	$setBonusDesc1 = TransformBonusDesc($row['setBonusDesc1']);
	$setBonusDesc2 = TransformBonusDesc($row['setBonusDesc2']);
	$setBonusDesc3 = TransformBonusDesc($row['setBonusDesc3']);
	$setBonusDesc4 = TransformBonusDesc($row['setBonusDesc4']);
	$setBonusDesc5 = TransformBonusDesc($row['setBonusDesc5']);
	$setBonusDesc6 = TransformBonusDesc($row['setBonusDesc6']);
	$setBonusDesc7 = TransformBonusDesc($row['setBonusDesc7']);
	$setBonusDesc8 = TransformBonusDesc($row['setBonusDesc8']);
	$setBonusDesc9 = TransformBonusDesc($row['setBonusDesc9']);
	$setBonusDesc10 = TransformBonusDesc($row['setBonusDesc10']);
	$setBonusDesc11 = TransformBonusDesc($row['setBonusDesc11']);
	$setBonusDesc12 = TransformBonusDesc($row['setBonusDesc12']);
	$setBonusCount = 0;
	$setMaxEquipCount = $row['setMaxEquipCount'];
	if ($setMaxEquipCount == null || $setMaxEquipCount == "") $setMaxEquipCount = 1; 
	
	$lastBonusDesc = $setBonusDesc12;
	if ($lastBonusDesc == "") $lastBonusDesc = $setBonusDesc11;
	if ($lastBonusDesc == "") $lastBonusDesc = $setBonusDesc10;
	if ($lastBonusDesc == "") $lastBonusDesc = $setBonusDesc9;
	if ($lastBonusDesc == "") $lastBonusDesc = $setBonusDesc8;
	if ($lastBonusDesc == "") $lastBonusDesc = $setBonusDesc7;
	if ($lastBonusDesc == "") $lastBonusDesc = $setBonusDesc6;
	if ($lastBonusDesc == "") $lastBonusDesc = $setBonusDesc5;
	if ($lastBonusDesc == "") $lastBonusDesc = $setBonusDesc4;
	if ($lastBonusDesc == "") $lastBonusDesc = $setBonusDesc3;
	if ($lastBonusDesc == "") $lastBonusDesc = $setBonusDesc2;
	if ($lastBonusDesc == "") $lastBonusDesc = $setBonusDesc1;
	
	if ($setBonusDesc1 != "") $setBonusCount = 1;
	if ($setBonusDesc2 != "") $setBonusCount = 2;
	if ($setBonusDesc3 != "") $setBonusCount = 3;
	if ($setBonusDesc4 != "") $setBonusCount = 4;
	if ($setBonusDesc5 != "") $setBonusCount = 5;
	if ($setBonusDesc6 != "") $setBonusCount = 6;
	if ($setBonusDesc7 != "") $setBonusCount = 7;
	if ($setBonusDesc8 != "") $setBonusCount = 8;
	if ($setBonusDesc9 != "") $setBonusCount = 9;
	if ($setBonusDesc10 != "") $setBonusCount = 10;
	if ($setBonusDesc11 != "") $setBonusCount = 11;
	if ($setBonusDesc12 != "") $setBonusCount = 12;
	
	if (!array_key_exists($setName, $setItemSlots)) $setItemSlots[$setName] = array();
	UpdateItemSlotArray($setItemSlots[$setName], $row);
	
	$matches = array();
	$regResult = preg_match('/\(([0-9]+) items\)/', $lastBonusDesc, $matches);
	if ($regResult) $setMaxEquipCount = $matches[1];
	
	if (!$QUIET)
	{
		if ($SHOW_ONLY_SET == "" || $SHOW_ONLY_SET == $setName)
		{
			print("\tUpdating set $setName with $setMaxEquipCount items...\n");
			$QUIET_SET = false;
		}
		else
		{
			$QUIET_SET = true;
		}
	}
	//print("\t\t$setBonusDesc1 == " . $row['setBonusDesc1'] . "\n");
	
	$query = "SELECT * FROM setSummaryTmp WHERE setName=\"$setName\";";
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
		$newBonusDesc6 = preg_replace('/\|c[0-9a-fA-F]{6}([a-zA-Z0-9\.\-\%\s]+)\|r/', '$1', $newRow['setBonusDesc6']);
		$newBonusDesc7 = preg_replace('/\|c[0-9a-fA-F]{6}([a-zA-Z0-9\.\-\%\s]+)\|r/', '$1', $newRow['setBonusDesc7']);
		$newBonusDesc8 = preg_replace('/\|c[0-9a-fA-F]{6}([a-zA-Z0-9\.\-\%\s]+)\|r/', '$1', $newRow['setBonusDesc8']);
		$newBonusDesc9 = preg_replace('/\|c[0-9a-fA-F]{6}([a-zA-Z0-9\.\-\%\s]+)\|r/', '$1', $newRow['setBonusDesc9']);
		$newBonusDesc10 = preg_replace('/\|c[0-9a-fA-F]{6}([a-zA-Z0-9\.\-\%\s]+)\|r/', '$1', $newRow['setBonusDesc10']);
		$newBonusDesc11 = preg_replace('/\|c[0-9a-fA-F]{6}([a-zA-Z0-9\.\-\%\s]+)\|r/', '$1', $newRow['setBonusDesc11']);
		$newBonusDesc12 = preg_replace('/\|c[0-9a-fA-F]{6}([a-zA-Z0-9\.\-\%\s]+)\|r/', '$1', $newRow['setBonusDesc12']);
		
		if ($newBonusDesc1 != $setBonusDesc1) { $matches = true; if (!$QUIET_SET) print("\t\tSet bonus #1 doesn't match!\n"); }
		if ($newBonusDesc2 != $setBonusDesc2) { $matches = true; if (!$QUIET_SET) print("\t\tSet bonus #2 doesn't match!\n"); }
		if ($newBonusDesc3 != $setBonusDesc3) { $matches = true; if (!$QUIET_SET) print("\t\tSet bonus #3 doesn't match!\n"); }
		if ($newBonusDesc4 != $setBonusDesc4) { $matches = true; if (!$QUIET_SET) print("\t\tSet bonus #4 doesn't match!\n"); }
		if ($newBonusDesc5 != $setBonusDesc5) { $matches = true; if (!$QUIET_SET) print("\t\tSet bonus #5 doesn't match!\n"); }
		if ($newBonusDesc6 != $setBonusDesc6) { $matches = true; if (!$QUIET_SET) print("\t\tSet bonus #6 doesn't match!\n"); }
		if ($newBonusDesc7 != $setBonusDesc7) { $matches = true; if (!$QUIET_SET) print("\t\tSet bonus #7 doesn't match!\n"); }
		if ($newBonusDesc8 != $setBonusDesc8) { $matches = true; if (!$QUIET_SET) print("\t\tSet bonus #8 doesn't match!\n"); }
		if ($newBonusDesc9 != $setBonusDesc9) { $matches = true; if (!$QUIET_SET) print("\t\tSet bonus #9 doesn't match!\n"); }
		if ($newBonusDesc10 != $setBonusDesc10) { $matches = true; if (!$QUIET_SET) print("\t\tSet bonus #10 doesn't match!\n"); }
		if ($newBonusDesc11 != $setBonusDesc11) { $matches = true; if (!$QUIET_SET) print("\t\tSet bonus #11 doesn't match!\n"); }
		if ($newBonusDesc12 != $setBonusDesc12) { $matches = true; if (!$QUIET_SET) print("\t\tSet bonus #12 doesn't match!\n"); }
		if ($newRow['setMaxEquipCount'] != $setMaxEquipCount) { $matches = false; if (!$QUIET_SET) print("\t\tSet max equip count doesn't match!\n"); }
		
		if ($matches) 
		{
			$updateId = $newRow['id'];
			$createNewSet = false;
			break;
		}
	}
	
	if ($createNewSet)
	{
		++$newCount;
		
		$setBonusDesc = "";
		if ($setBonusDesc1 != "") $setBonusDesc .= $setBonusDesc1;
		if ($setBonusDesc2 != "") $setBonusDesc .= "\n".$setBonusDesc2;
		if ($setBonusDesc3 != "") $setBonusDesc .= "\n".$setBonusDesc3;
		if ($setBonusDesc4 != "") $setBonusDesc .= "\n".$setBonusDesc4;
		if ($setBonusDesc5 != "") $setBonusDesc .= "\n".$setBonusDesc5;
		if ($setBonusDesc6 != "") $setBonusDesc .= "\n".$setBonusDesc6;
		if ($setBonusDesc7 != "") $setBonusDesc .= "\n".$setBonusDesc7;
		if ($setBonusDesc8 != "") $setBonusDesc .= "\n".$setBonusDesc8;
		if ($setBonusDesc9 != "") $setBonusDesc .= "\n".$setBonusDesc9;
		if ($setBonusDesc10 != "") $setBonusDesc .= "\n".$setBonusDesc10;
		if ($setBonusDesc11 != "") $setBonusDesc .= "\n".$setBonusDesc11;
		if ($setBonusDesc12 != "") $setBonusDesc .= "\n".$setBonusDesc12;
		
		if (!$QUIET_SET) print("\t\tCreating new set $setName\n$setBonusDesc...\n");
		
		$gameIndex = $ESO_SETINDEX_MAP[strtolower($setName)];
		if ($gameIndex == null) $gameIndex = -1;
		
		$setInfo = $setInfos[strtolower($setName)];
		$setType = "";
		$setSources = "";
		
		if ($setInfo)
		{
			$setType = $db->real_escape_string($setInfo['type']);
			$setSources = $db->real_escape_string($setInfo['sources']);
		}
		else
		{
			print("\t\tWARNING: No $setName found in set info!\n");
		}
		
		$setName = $db->real_escape_string($setName);
		$indexName = $db->real_escape_string($indexName);
		$setBonusDesc = $db->real_escape_string($setBonusDesc);
		$setBonusDesc1 = $db->real_escape_string($setBonusDesc1);
		$setBonusDesc2 = $db->real_escape_string($setBonusDesc2);
		$setBonusDesc3 = $db->real_escape_string($setBonusDesc3);
		$setBonusDesc4 = $db->real_escape_string($setBonusDesc4);
		$setBonusDesc5 = $db->real_escape_string($setBonusDesc5);
		$setBonusDesc6 = $db->real_escape_string($setBonusDesc6);
		$setBonusDesc7 = $db->real_escape_string($setBonusDesc7);
		$setBonusDesc8 = $db->real_escape_string($setBonusDesc8);
		$setBonusDesc9 = $db->real_escape_string($setBonusDesc9);
		$setBonusDesc10 = $db->real_escape_string($setBonusDesc10);
		$setBonusDesc11 = $db->real_escape_string($setBonusDesc11);
		$setBonusDesc12 = $db->real_escape_string($setBonusDesc12);
		
		$query  = "INSERT INTO setSummaryTmp(setName, indexName, setMaxEquipCount, setBonusCount, itemCount, setBonusDesc1, setBonusDesc2, setBonusDesc3, setBonusDesc4, setBonusDesc5, setBonusDesc6, setBonusDesc7, setBonusDesc8, setBonusDesc9, setBonusDesc10, setBonusDesc11, setBonusDesc12, setBonusDesc, gameId, type, sources) ";
		$query .= "VALUES('$setName', '$indexName', $setMaxEquipCount, $setBonusCount, 1, '$setBonusDesc1', '$setBonusDesc2', '$setBonusDesc3', '$setBonusDesc4', '$setBonusDesc5', '$setBonusDesc6', '$setBonusDesc7', '$setBonusDesc8', '$setBonusDesc9', '$setBonusDesc10', '$setBonusDesc11', '$setBonusDesc12', '$setBonusDesc', $gameIndex, '$setType', '$setSources');";
		
		$result = $db->query($query);
		if (!$result) exit("ERROR: Database query error inserting into table!\n" . $db->error . "\n" . $query);
	}
	else if ($updateId > 0)
	{
		//if (!$QUIET_SET) print("\t\tUpdating set $updateId...\n");
		++$updateCount;
		$query = "UPDATE setSummaryTmp SET itemCount=itemCount+1 WHERE id=$updateId;";
		$result = $db->query($query);
		if (!$result) exit("ERROR: Database query error updating table!\n" . $db->error . "\n" . $query);
	}
	else
	{
		if (!$QUIET_SET) print("\t\tError: Unknown set record to update!\n");
	}
	
}

print("\tUpdating set item slots...\n");

foreach ($setItemSlots as $setName => $setSlots)
{
	$slotString = CreateItemSlotString($setSlots);
	$query = "UPDATE setSummaryTmp SET itemSlots='".$slotString."' WHERE setName=\"".$setName."\";";
	$result = $db->query($query);
	if (!$result) exit("ERROR: Database query error updating table!\n" . $db->error . "\n" . $query);
	//print("$setName: $slotString\n");
}

print("Found $itemCount item sets, $newCount new, $updateCount duplicate!\n");

if ($KEEPONLYNEWSETS && $TABLE_SUFFIX != "")
{
	print("\tDeleting existing sets in setSummary...\n");
	
	$query = "DELETE setSummaryTmp FROM setSummaryTmp LEFT JOIN setSummary on setSummaryTmp.setName = setSummary.setName WHERE setSummary.setName IS NOT NULL;";
	$result = $db->query($query);
	if (!$result) exit("ERROR: Database query error deleting old sets!\n" . $db->error . "\n" . $query);
	
	print("\tDeleting old sets...OK!\n");
}

if ($REMOVEDUPLICATES)
{
	print("\tRemoving duplicates...\n");
	
	$query = "SELECT *, COUNT(*) c, GROUP_CONCAT(id) ids, GROUP_CONCAT(itemCount) itemCounts FROM setSummaryTmp GROUP BY setName HAVING c > 1;";
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
		
		$maxCount = max($itemCounts);
		
		foreach ($itemCounts as $i => $itemCount)
		{
			$itemId = $ids[$i];
			if ($itemCount >= $maxCount) continue;
			
			print("\t\t\tDeleting record {$itemId} with count {$itemCount}...\n");
			
			$query = "DELETE FROM setSummaryTmp WHERE id=$itemId;";
			$deleteResult =	$db->query($query);
			if (!$deleteResult) exit("ERROR: Database query error deleting duplicate sets!\n" . $db->error . "\n" . $query);
		}
	}
}

$query = "DROP TABLE IF EXISTS setSummary$TABLE_SUFFIX;";
$db->query($query);

$query = "RENAME TABLE setSummaryTmp TO setSummary$TABLE_SUFFIX;";
$result = $db->query($query);
if ($result === false) exit("ERROR: Failed to rename table to setSummary$TABLE_SUFFIX!");


