<?php 

if (php_sapi_name() != "cli") die("Can only be run from command line!");

require("/home/uesp/secrets/esolog.secrets");
require("esoCommon.php");

$db = new mysqli($uespEsoLogReadDBHost, $uespEsoLogReadUser, $uespEsoLogReadPW, $uespEsoLogDatabase);
if ($db->connect_error) exit("Could not connect to mysql database!");

$VERSION = "";

$items = [];

$result = $db->query("SELECT * FROM minedItemSummary$VERSION WHERE type=1 or type=2;");
if ($result === false) exit("Failed to query minedItemSummary$VERSION table!");

while ($item = $result->fetch_assoc())
{
	$id = intval($item['itemId']);
	$items[$id] = $item;
}

$count = count($items);
print("Loaded $count armor and weapon records!\n");


function getItemTypeNames($item)
{
	$ITEMWEAPONTYPE_TEXTS = array(
		1 => "Axe",
		2 => "Hammer",
		3 => "Sword",
		4 => "Greatsword",
		5 => "Battle Axe",
		6 => "Maul",
		8 => "Bow",
		9 => "Restoration Staff",
		11 => "Dagger",
		12 => "Inferno Staff",
		13 => "Frost Staff",
		14 => "Shield",
		15 => "Lightning Staff",
	);
	
	$ITEMARMORTYPE_TEXTS = array(
			1 => array(
				1 => "Hat",
				2 => "Necklace",
				3 => "Shirt",		//Robe
				4 => "Epaulets",
				8 => "Sash",
				9 => "Breeches",
				10 => "Shoes",
				12 => "Ring",
				13 => "Gloves",
			),
			2 => array(
				1 => "Helmet",
				2 => "Neckace",
				3 => "Jack",
				4 => "Arm Cops",
				8 => "Belt",
				9 => "Guards",
				10 => "Boots",
				12 => "Ring",
				13 => "Bracers",
			),
			3 => array(
				1 => "Helm",
				2 => "Necklace",
				3 => "Cuirass",
				4 => "Pauldron",
				8 => "Girdle",
				9 => "Greaves",
				10 => "Sabatons",
				12 => "Ring",
				13 => "Gauntlets",
			),
	);
	
	$type = intval($item['type']);
	$weaponType = intval($item['weaponType']);
	$armorType = intval($item['armorType']);
	$equipType = intval($item['equipType']);
	
	if ($type == 1)
	{
		$typeName = $ITEMWEAPONTYPE_TEXTS[$weaponType];
		if ($typeName == null) return "";
		
		return $typeName;
	}
	elseif ($type == 2)
	{
		$typeNames = $ITEMARMORTYPE_TEXTS[$armorType];
		if ($typeNames == null) return "";
		
		$typeName = $typeNames[$equipType];
		if ($typeName == null) return "";
		
		return $typeName;
	}
	
	return "";
}

print("TODO...\n");

foreach ($items as $itemId => $item)
{
	$name = $item['name'];
	$set = $item['setName'];
	$weaponType = $item['weaponType'];
	$armorType = $item['armorType'];
	$equipType = $item['equipType'];
	
	$typeName = getItemTypeNames($item);
	
	if (preg_match('', $name)) continue;
	
}