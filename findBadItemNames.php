<?php 

if (php_sapi_name() != "cli") die("Can only be run from command line!");

require("/home/uesp/secrets/esolog.secrets");

$db = new mysqli($uespEsoLogReadDBHost, $uespEsoLogReadUser, $uespEsoLogReadPW, $uespEsoLogDatabase);
if ($db->connect_error) exit("Could not connect to mysql database!");

print("Finding all mismatched item names in mined item data...\n");

$TABLE_SUFFIX = "33pts";
$linesOutput = 0;
$luaFunctionCount = 1;
$DO_TYPE_CHECK = false;		// Not needed after changing db tables in update 30

$START_ID = 3;
$END_ID = 200000;

$MAXBADITEMCOUNT = 1450;

$NUMCALLSPERFUNCTION = 300;

$SUFFIXES = array(
		"arm cops" => 1,
		"belt" => 1,
		"boots" => 1,
		"bow" => 1,
		"bracers" => 1,
		"breeches" => 1,
		"chest" => 1,
		"cuirass" => 1,
		"epaulets" => 1,
		"feet" => 1,
		"girdle" => 1,
		"gloves" => 1,
		"greaves" => 1,
		"guantlets" => 1,
		"guards" => 1,
		"hat" => 1,
		"hands" => 1,
		"helmet" => 1,
		"helmt" => 1,
		"jack" => 1,
		"jerkin" => 1,
		"pauldron" => 1,
		"robe" => 1,
		"sabatons" => 1,
		"sash" => 1,
		"shield" => 1,
		"shoulders" => 1,
		"shoes" => 1,
		
		"axe" => 1,
		"battle axe" => 1,
		"dagger" => 1,
		"fire staff" => 1,
		"greatsword" => 1,
		"ice staff" => 1,
		"inferno staff" => 1,
		"lightning staff" => 1,
		"mace" => 1,
		"maul" => 1,
		"restoration staff" => 1,
		"sword" => 1,
		
		"glyph of absorb health" => 1,
		"glyph of absorb stamina" => 1,
		"glyph of absorb magicka" => 1,
		"glyph of decrease health" => 1,
		"glyph of decrease health" => 1,
		"glyph of reduce spell cost" => 1,
		"glyph of reduce feat cost" => 1,
		"glyph of bashing" => 1,
		"glyph of shielding" => 1,
		"glyph of potion boost" => 1,
		"glyph of potion speed" => 1,
		"glyph of increase physical harm" => 1,
		"glyph of increase magical harm" => 1,
		"glyph of decrease physical harm" => 1,
		"glyph of decrease spell harm" => 1,
		"glyph of weapon damage" => 1,
		"glyph of frost resist" => 1,
		"glyph of frost" => 1,
		"glyph of hardening" => 1,
		"glyph of health" => 1,
		"glyph of health recovery" => 1,
		"glyph of magicka" => 1,
		"glyph of magicka recovery" => 1,
		"glyph of stamina" => 1,
		"glyph of stamina recovery" => 1,
		"glyph of poison" => 1,
		"glyph of poison resist" => 1,
		"glyph of weakening" => 1,
		"glyph of foulness" => 1,
		"glyph of shock" => 1,
		"glyph of shock resist" => 1,
		"glyph of crushing" => 1,
		"glyph of disease" => 1,
		"glyph of disease resist" => 1,
		"glyph of flame" => 1,
		"glyph of flame resist" => 1,
		
		"entrapment" => 1,
		"hindering" => 1,
		"uncertainty" => 1,
		"enervation" => 1,
		"health" => 1,
		"magicka" => 1,
		"stamina" => 1,
		"strength" => 1,
		"spell weaving" => 1,
		"rejuvenation" => 1,
		"immovability" => 1,
		"ravage armor" => 1,
		"speed" => 1,
		"armor" => 1,
		"spell critical" => 1,
		"detection" => 1,
		"spell power" => 1,
		"weapon crit" => 1,
		"weapon power" => 1,
		"spell crit" => 1,
		"invisibility" => 1,
		"ravage health" => 1,
		"ravage magicka" => 1,
		"ravage stamina" => 1,
		"maim" => 1,
		"cowardice" => 1,
		"spell protection" => 1,
		"ravage spell protection" => 1,
		
		"heavy armor" => 1,
		"medium armor" => 1,
		"light armor" => 1,
		"wooden weapon" => 1,
		"metal weapon" => 1,
		"small heavy armor" => 1,
		"small medium armor" => 1,
		"small light armor" => 1,
		"large heavy armor" => 1,
		"large medium armor" => 1,
		"large light armor" => 1,
		"1h weapon" => 1,
		"2h weapon" => 1,
		"staff" => 1,
		"accessory" => 1,
		"armor" => 1,
		"weapon" => 1,
		"ring" => 1,
		"necklace" => 1,
		"equipment" => 1,
		"the skillmaxer" => 1,
		"craglorn weapon" => 1,
		"craglorn armor" => 1,
);

//$START_ID = 116747;

for ($itemId = $START_ID; $itemId <= $END_ID; ++$itemId)
{
	if (($itemId % 1000) == 0) print("\t$itemId: Checking for mismatched item names ($linesOutput bad item records found so far)...\n");
	
	//$query = "SELECT name, internalLevel, internalSubtype, equipType, armorType, weaponType from minedItem$TABLE_SUFFIX WHERE itemId=$itemId;";
	$query = "SELECT name, internalLevel, internalSubtype from minedItem$TABLE_SUFFIX WHERE itemId=$itemId;";
	$result = $db->query($query);
	
	if ($result->num_rows <= 1) continue;
	
	$items = array();
	$nameCount = array();
	
	while (($row = $result->fetch_assoc()))
	{
		$name = $row['name'];
		
		if ($items[$name] == null) $items[$name] = array();
		
		$row['isBad'] = false;
		$items[$name][] = $row;
		
		if ($nameCount[$name] == null) $nameCount[$name] = 0;
		$nameCount[$name] += 1;
	}
	
	$numNames = count($nameCount);
	if ($numNames <= 1) continue;
	
	arsort($nameCount);
	
	$maxCount = 0;
	$maxName = "";
	
	foreach ($nameCount as $name => $count)
	{
		if ($maxCount < $count)
		{
			$maxCount = $count;
			$maxName = $name;
		}
	}	
	
	$maxEquipType = 0;
	$maxWeaponType = 0;
	$maxArmorType = 0;
	$maxSuffix = "";
	$lowerName = strtolower($maxName);
	
	foreach ($SUFFIXES as $suffix => $null)
	{
		$length = strlen($suffix) + 1;
		
		if ($suffix == $lowerName || substr($lowerName, -$length) == " ".$suffix)
		{
			$maxSuffix = $suffix;
			break;
		}
	}
	
	$maxEquipType = $items[$maxName][0]['equipType'];
	$maxWeaponType = $items[$maxName][0]['weaponType'];
	$maxArmorType = $items[$maxName][0]['armorType'];
	$hasBad = false;
	$badOutput = "";
	
	foreach ($nameCount as $name => $count)
	{
		if ($name == $maxName) continue;
		$itemData = $items[$name];
		$isBad = false;
		$errMsg = "";
		
		if ($maxSuffix)
		{
			$lowerName = strtolower($name);
			$length = strlen($maxSuffix) + 1;
			
			if (!($suffix == $lowerName || substr($lowerName, -$length) == " ".$suffix))
			{
				$badOutput .= "\t\t$name: Bad Suffix, expecting '$suffix'!\n";
				$isBad = true;
			}
		}
		
		foreach ($itemData as $i => $data)
		{
			$id = $data['internalLevel'] . ":" . $data['internalSubtype'];
			$errMsg = "";
			
			if ($data['equipType'] != $maxEquipType)
			{
				$errMsg = "\t\t$id: Bad equipType {$data['equipType']} expecting '$maxEquipType'!\n";
				$data['isBad'] = true;
			}
			
			if ($data['armorType'] != $maxArmorType)
			{
				$errMsg = "\t\t$id: Bad armorType {$data['armorType']} expecting '$maxArmorType'!\n";
				$data['isBad'] = true;
			}
			
			if ($data['weaponType'] != $maxWeaponType)
			{
				$errMsg = "\t\t$id: Bad weaponType {$data['weaponType']} expecting '$maxWeaponType'!\n";
				$data['isBad'] = true;
			}
			
				/* Only flag items that are composed mostly of one name. This skips items that have a lot of names
				 * on purpose like Gylphs and other crafted items. */
			if ($maxCount > $MAXBADITEMCOUNT)
			{
				$errMsg = "\t\t$id: Low item count $count!\n";
				$data['isBad'] = true;
			}
			
			if ($data['weaponPower'] > 0 && $data['type'] == 2)
			{
				$errMsg = "\t\t$id: Found armor with weaponPower set!\n";
				$data['isBad'] = true;
			}
			
			if ($data['armorRating'] > 0 && $data['type'] == 1 && $data['weaponType'] != 14)
			{
				$errMsg = "\t\t$id: Found weapon with armorRating set!\n";
				$data['isBad'] = true;
			}
			
			if ($isBad)
			{
				//$errMsg = "\t\t$id: Bad Suffix for '$name' expecting '$suffix'!\n";
				$data['isBad'] = true;
			}
			
			if ($data['isBad']) 
			{
				$hasBad = true;
				$badOutput .= $errMsg;
				$items[$name][$i]['isBad'] = true;
			}
		}
	}
	
	if (!$hasBad) continue;
	
	print("\t$itemId: Has $numNames different names\n");
	$maxCount = 0;
	$maxName = "";
	
	foreach ($nameCount as $name => $count)
	{
		$itemData = $items[$name];
		$ids = "";
				
		if (count($itemData) < 20)
		{
			$ids = "(";
			
			foreach ($itemData as $i => $data)
			{
				$ids .= $data['internalLevel'] . ":" . $data['internalSubtype'] . ", ";
			}
			
			$ids .= ")";
		}
		
		print("\t\t$count: $name $ids\n");
	}
	
	print($badOutput);
	$output = "";
	//$output .= "uespLog.MineSingleItemSafe_FinishCallback = uespLog.StartNextMineTest\n";
	
	if ($linesOutput == 0)
	{
		$output .= "function uespminetest$luaFunctionCount()\n";
	}
	
	foreach ($nameCount as $name => $count)
	{
		//if ($count >= $maxCount) continue;
		$itemData = $items[$name];
		
		foreach ($itemData as $i => $data)
		{
			if (!$data['isBad']) continue;
			
			$output .= "\tuespLog.MineItemSingle($itemId, {$data['internalLevel']}, {$data['internalSubtype']}) \n";
			++$linesOutput;
			
			if (($linesOutput % $NUMCALLSPERFUNCTION) == 0)
			{
				++$luaFunctionCount;
				$output .= "end\nfunction uespminetest$luaFunctionCount()\n";
			}
		}
	}
	
	file_put_contents("fixitems.lua", $output, FILE_APPEND);
}

if ($linesOutput > 0) 
{
	$output = "end\n";
	file_put_contents("fixitems.lua", $output, FILE_APPEND);
}
