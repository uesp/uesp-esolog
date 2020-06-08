<?php

if (php_sapi_name() != "cli") die("Can only be run from command line!");

require("/home/uesp/secrets/esolog.secrets");
require("esoCommon.php");

$db = new mysqli($uespEsoLogReadDBHost, $uespEsoLogReadUser, $uespEsoLogReadPW, $uespEsoLogDatabase);
if ($db->connect_error) exit("Could not connect to mysql database!");

$result = $db->query("select itemId, name, description, enchantDesc, type, weaponType, armorType, setName, style from minedItemSummary where description!='' and equipType>0 group by description having count(*)=1;");
if (!$result) die("Failed to run query!");

while ($item = $result->fetch_assoc()) {
	$itemType = GetEsoItemTypeText($item['type']);
	$armorType = GetEsoItemArmorTypeText($item['armorType']);
	$weaponType = GetEsoItemWeaponTypeText($item['weaponType']);
	$itemId = $item['itemId'];
	$name = $item['name'];
	$desc = $item['description'];
	$enchantDesc = $item['enchantDesc'];
	$setName = $item['setName'];
	$style = $item['style'];
	$styleName = GetEsoItemStyleText($style);
	
	$desc = str_replace('"', '`', $desc);
	$desc = str_replace('|cffffff', '', $desc);
	$desc = str_replace('|r', '', $desc);
	$desc = str_replace("\n", '\n', $desc);
	$desc = str_replace("\r", '', $desc);
	$enchantDesc = str_replace('|cffffff', '', $enchantDesc);
	$enchantDesc = str_replace('|r', '', $enchantDesc);
	$enchantDesc = str_replace("\n", '\n', $enchantDesc);
	$enchantDesc = str_replace("\r", '', $enchantDesc);
	$name = str_replace('"', '`', $name);
	
	print("\"$itemId\",\"$name\",\"$desc\",\"$enchantDesc\",\"$itemType\",\"$armorType\",\"$weaponType\",\"$setName\",\"$styleName\"\n");
}
