<?php 


require("esoCommon.php");
require("sparseMinedItemTables.php");



function LoadEsoMinedItem ($db, $itemId, $intLevel, $intSubtype, $tableSuffix = "")
{
	$query = "SELECT * FROM minedItem{$tableSuffix} WHERE itemId='$itemId' AND internalLevel='$intLevel' AND internalSubtype='$intSubtype';";
	$result = $db->query($query);
	if ($result === false) return null;
	if ($result->num_rows === 0) return null;

	return $result->fetch_assoc();
}


function LoadEsoMinedItems ($db, $itemId, $tableSuffix = "")
{
	$query = "SELECT * FROM minedItem{$tableSuffix} WHERE itemId='$itemId';";
	$result = $db->query($query);
	if ($result === false) return null;
	if ($result->num_rows === 0) return null;

	$result->data_seek(0);
	$rows = array();

	while (($row = $result->fetch_assoc()))
	{
		$rows[] = $row;
	}

	return $rows;
}


function FindEsoSparseMinedInternalValues ($intLevel, $intSubtype)
{
	global $ESO_SPARSEMINEDITEM_LEVELMAP;
	
	$key = "$intLevel:$intSubtype";
	
	return $ESO_SPARSEMINEDITEM_LEVELMAP[$key];
}


function FindEsoSparseMinedMaxCharges ($intLevel, $intSubtype)
{
	global $ESO_SPARSEMINEDITEM_MAXCHARGES;

	$key = "$intLevel:$intSubtype";
	
	$value = $ESO_SPARSEMINEDITEM_MAXCHARGES[$key];
	if ($value === null) $value = 0;
	
	return $value;
}


function AdjustEsoSparseMinedItemTraitDesc ($trait, $traitDesc, $level, $quality, $weaponType)
{
	global $ESO_SPARSEMINEDITEM_TRAITLONG;
	global $ESO_SPARSEMINEDITEM_TRAITSHORT;
	
	$trait = intval($trait);
	if ($trait == 0 || $traitDesc == "") return $traitDesc;
	if ($trait == 12 || $trait == 5 || $trait == 7) return $traitDesc;
	
	$level = intval($level);
	$quality = intval($quality);
	
	$longTraitData = $ESO_SPARSEMINEDITEM_TRAITLONG[$trait];
	$shortTraitData = $ESO_SPARSEMINEDITEM_TRAITSHORT[$trait];
		
	$newTraitValue = null;
	
	if ($longTraitData != null)
	{
		$newTraitValue = $longTraitData["$level:$quality"];
		if ($trait == 21 && $newTraitValue !== null) $newTraitValue = floor($newTraitValue*1.1);		// Healthy
	}
	else if ($shortTraitData != null)
	{
		$newTraitValue = $shortTraitData[$quality];
		
			// 2H trait double value
		if ($newTraitValue !== null && ($trait == 8 || $trait == 5 || $trait == 1 || $trait == 3 || $trait == 7))
		{
			$weaponType = intval($weaponType);

			if (($weaponType >= 4 && $weaponType <= 9) || $weaponType == 12 || $weaponType == 13 || $weaponType == 15)
			{
				$newTraitValue = $newTraitValue * 2;
			}
		}
		
		//print("\t\tTrait Change: $traitDesc, $newTraitValue\n");
	}
	
	if ($newTraitValue !== null)
	{
		if (intval($newTraitValue) != $newTraitValue) $newTraitValue = sprintf('%0.1f', $newTraitValue);
		$traitDesc = preg_replace("#(by (?:\|c[A-Faf0-9]{6})|)([0-9\.]+)(\|r|)#", '${1}'.$newTraitValue.'${3}', $traitDesc, 1);
		
		if ($newTraitValue == 11)
			$traitDesc = preg_replace("# a ((?:\|c[A-Faf0-9]{6})|)11(\|r|)\% #", " an ${1}11${2}% ", $traitDesc);
		else
			$traitDesc = preg_replace("# an ((?:\|c[A-Faf0-9]{6})|)([0-9\.]+)(\|r|)\% #", " a $1$2$3% ", $traitDesc);
	}
		
	return $traitDesc;
}


function AdjustEsoSparseMinedItemEnchantment($db, $tableSuffix, $enchantName, $enchantDesc, $intLevel, $intSubtype, $trait, $traitDesc, $equipType)
{
	global $ESO_SPARSEMINEDITEM_ENCHANTMENTS;
	
	if ($enchantName == "" || $enchantDesc == "") return array("", "");
	
	$enchantId = $ESO_SPARSEMINEDITEM_ENCHANTMENTS[$enchantName];
	if ($enchantId == null) return array($enchantName, $enchantDesc);
	
	$query = "SELECT enchantName, enchantDesc FROM minedItem{$tableSuffix} WHERE itemId='$enchantId' AND internalLevel='$intLevel' AND internalSubtype='$intSubtype';";
	$result = $db->query($query);
	if ($result === false) return array($enchantName, $enchantDesc);;
	if ($result->num_rows === 0) return array($enchantName, $enchantDesc);;
	
	$row = $result->fetch_assoc();
	
	if ($equipType == 4 || $equipType == 8 || $equipType == 10 || $equipType == 13)
	{
		$row['enchantDesc'] = preg_replace_callback("#(\|c[A-Fa-f0-9]{6})([0-9\.]+)(\|r)#", function($matches) use ($traitValue) {
			$value = floor(floatval($matches[2]) * 0.405);
			return $matches[1] . $value . $matches[3];
		}, $row['enchantDesc']);
	}
	
	$trait = intval($trait);
	
		// Infused
	if ($trait == 16 || $trait == 4)
	{
		$traitMatch = preg_match("#[0-9]+#", $traitDesc, $matches);
		$traitValue = 0;
		if ($traitMatch) $traitValue = floatval($matches[0]);

		if ($traitValue > 0)
		{
			//print("\t\tInfused Enchantment $traitValue\n");
			//print("\t\t{$row['enchantDesc']}\n");
			
			$row['enchantDesc'] = preg_replace_callback("#(\|c[A-Fa-f0-9]{6})([0-9\.]+)(\|r)#", function($matches) use ($traitValue) {
					$factor = (1 + $traitValue/100);
					$value = floor(floatval($matches[2]) * (1 + $traitValue/100));
					return $matches[1] . $value . $matches[3];
				}, $row['enchantDesc'], 1);
			
			//print("\t\t${row['enchantDesc']}\n");
		}
	}
	
	return array($row['enchantName'], $row['enchantDesc']);
}


function GetEsoSparseMinedItemNameChange ($itemId, $itemLevel, $name)
{
	global $ESO_NAMEDATA_ITEMS;
	
	$nameTable = $ESO_NAMEDATA_ITEMS[$itemId];
	if ($nameTable === null) return $name;
	
	$itemLevel = intval($itemLevel);
	
	$lastName = $nameTable[1];
	
	foreach ($nameTable as $level => $newName)
	{
		if ($itemLevel < $level) return $lastName;
		$lastName = $newName;
	}

	return $lastName;
}


function GetEsoSparseMinedItemIcon($origLevel, $newLevel, $icon)
{
	if ($icon === "") return $icon;
	
	$origLevel = intval($origLevel);
	$newLevel = intval($newLevel);
	
	if ($origLevel <= 15)
		$origSuffix = "a";
	else if ($origLevel <= 25)
		$origSuffix = "b";
	else if ($origLevel <= 35)
		$origSuffix = "c";
	else
		$origSuffix = "d";
	
	if ($newLevel <= 15)
		$newSuffix = "a";
	else if ($newLevel <= 25)
		$newSuffix = "b";
	else if ($newLevel <= 35)
		$newSuffix = "c";
	else
		$newSuffix = "d";

	if ($origSuffix == $newSuffix) return $icon;
	
	$icon = preg_replace("#_{$origSuffix}\.dds$#", "_" . $newSuffix . ".dds" , $icon);	
		
	return $icon;
}


function GetEsoSparseMinedItemValue($trait, $quality, $origQuality, $value, $intLevel, $intSubtype)
{
	global $ESO_SPARSEMINEDITEM_ORNATE;
	global $ESO_SPARSEMINEDITEM_ORNATEVALUEFIXUP;
	
	//print("\t\tChange Value: $trait, $quality, $origQuality, $value\n");
	
	if ($trait != 24 && $trait != 19 && $trait != 10) return $value;
	if ($quality == $origQuality) return $value;
	
	$ornate1 = $ESO_SPARSEMINEDITEM_ORNATE[$origQuality];
	$ornate2 = $ESO_SPARSEMINEDITEM_ORNATE[$quality];
	if ($ornate1 == nul || $ornate2 == null) return $value;
		
	//print("\t\tChange Value: $ornate1, $ornate2\n");
	
	$value = floor(intval($value) / (100 + $ornate1) * (100 + $ornate2));
	
	$fixupValue = $ESO_SPARSEMINEDITEM_ORNATEVALUEFIXUP["$intLevel:$intSubtype"];
	if ($fixupValue != null) $value += $fixupValue;
		
	return $value;
}


function GetEsoSparseMinedItemArmor($trait, $quality, $origQuality, $armorRating)
{
	global $ESO_SPARSEMINEDITEM_REINFORCED;
	
	if ($armorRating == 0) return $armorRating;
	if ($trait != 13) return $armorRating;
	if ($quality == $origQuality) return $armorRating;
	
	$rein1 = $ESO_SPARSEMINEDITEM_REINFORCED[$origQuality];
	$rein2 = $ESO_SPARSEMINEDITEM_REINFORCED[$quality];
	if ($rein1 == nul || $rein2 == null) return $armorRating;
	
	$armorRating = floor(intval($armorRating) / (100 + $rein1) * (100 + $rein2)); 
	
	return $armorRating;	
}


function LoadEsoSparseMinedItem ($db, $itemId, $intLevel, $intSubtype, $tableSuffix = "")
{
	$itemId = intval($itemId);
	$intLevel = intval($intLevel);
	$intSubtype = intval($intSubtype);
		
	$loadLevel = $intLevel;
	$loadSubtype = $intSubtype;
	
	$result = FindEsoSparseMinedInternalValues($loadLevel, $loadSubtype);
	
	if ($result != null)
	{
		$loadLevel = $result[0];
		$loadSubtype = $result[1];
	}
	
	$row = LoadEsoMinedItem($db, $itemId, $loadLevel ,$loadSubtype, $tableSuffix);
	if ($row == null) return null;
	
	$row['internalLevel'] = $intLevel;
	$row['internalSubtype'] = $intSubtype;
	
	$origLevel = $row['level'];
	$newLevel = GetEsoLevelFromIntType($intSubtype);
	if ($newLevel === null) $newLevel = $row['level'];
	if ($newLevel == 1) $newLevel = $intLevel;
	$row['level'] = $newLevel;
	
	$origQuality = $row['quality'];
	$newQuality = GetEsoQualityFromIntType($intSubtype);
	if ($newQuality === null) $newQuality = $row['quality'];
	$row['quality'] = $newQuality;
	
	$row['link'] = preg_replace("#(\|H[A-Za-z0-9]+\:item\:[0-9]+)\:([0-9]+)\:([0-9]+)\:#", "$1:$intSubtype:$intLevel:", $row['link']);
	
	if ($row['maxCharges'] > 0) $row['maxCharges'] = FindEsoSparseMinedMaxCharges($intLevel, $intSubtype);
	
	$row['traitDesc'] = AdjustEsoSparseMinedItemTraitDesc($row['trait'], $row['traitDesc'], $newLevel, $newQuality, $row['weaponType']);

	$enchantResult = AdjustEsoSparseMinedItemEnchantment($db, $tableSuffix, $row['enchantName'], $row['enchantDesc'], $intLevel, $intSubtype, $row['trait'], $row['traitDesc'], $row['equipType']);
	$row['enchantName'] = $enchantResult[0];
	$row['enchantDesc'] = $enchantResult[1];
	
	$row['name'] = GetEsoSparseMinedItemNameChange($itemId, $newLevel, $row['name']);
	
	$row['icon'] = GetEsoSparseMinedItemIcon($origLevel, $newLevel, $row['icon']);
	
	$row['value'] = GetEsoSparseMinedItemValue($row['trait'], $row['quality'], $origQuality, $row['value'], $intLevel, $intSubtype);
	
	$row['armorRating'] = GetEsoSparseMinedItemArmor($row['trait'], $row['quality'], $origQuality, $row['armorRating']);
			
	return $row;
}


function LoadEsoSparseMinedItemByQuality ($db, $itemId, $level, $quality, $tableSuffix = "")
{
	return null;
}


