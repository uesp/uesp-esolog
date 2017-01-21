<?php 


require("esoCommon.php");
require("sparseMinedItemTables.php");


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


function AdjustEsoSparseMinedItemTraitDesc ($trait, $traitDesc, $level, $quality)
{
	global $ESO_SPARSEMINEDITEM_TRAITLONG;
	global $ESO_SPARSEMINEDITEM_TRAITSHORT;
	
	$trait = intval($trait);
	if ($trait == 0 || $traitDesc == "") return $traitDesc;
	
	$level = intval($level);
	$quality = intval($quality);
	
	$longTraitData = $ESO_SPARSEMINEDITEM_TRAITLONG[$trait];
	$shortTraitData = $ESO_SPARSEMINEDITEM_TRAITSHORT[$trait];
		
	$newTraitValue = null;
	
	if ($longTraitData != null)
	{
		$newTraitValue = $longTraitData["$level:$quality"];
		if ($trait = 21 && $newTraitValue !== null) $newTraitValue = floor($newTraitValue*1.1);		//Healthy
	}
	else if ($shortTraitData != null)
	{
		$newTraitValue = $shortTraitData[$quality];
		//print("\t\tTrait Change: $traitDesc, $newTraitValue\n");
	}
	
	if ($newTraitValue !== null)
	{
		if (is_float($newTraitValue)) $newTraitValue = sprintf('%0.1f', $newTraitValue);
		$traitDesc = preg_replace("#(by (?:\|c[A-Faf0-9]{6})|)([0-9\.]+)(\|r|)#", '${1}'.$newTraitValue.'${3}', $traitDesc, 1);
	}
	
	return $traitDesc;
}


function AdjustEsoSparseMinedItemEnchantment($db, $tableSuffix, $enchantName, $enchantDesc, $intLevel, $intSubtype, $trait)
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
	
		// Infused
	if ($trait == 16 || $trait == 4)
	{
		//?
	}
	
	return array($row['enchantName'], $row['enchantDesc']);
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
	
	$query = "SELECT * FROM minedItem{$tableSuffix} WHERE itemId='$itemId' AND internalLevel='$loadLevel' AND internalSubtype='$loadSubtype';";
	$result = $db->query($query);
	if ($result === false) return null;
	if ($result->num_rows === 0) return null;
	
	$row = $result->fetch_assoc();
	if ($row == null) return null;
	
	$row['internalLevel'] = $intLevel;
	$row['internalSubtype'] = $intSubtype;
	
	$newLevel = GetEsoLevelFromIntType($intSubtype);
	if ($newLevel === null) $newLevel = $row['level'];
	if ($newLevel == 1) $newLevel = $intLevel;
	$row['level'] = $newLevel;
	
	$newQuality = GetEsoQualityFromIntType($intSubtype);
	if ($newQuality === null) $newQuality = $row['quality'];
	$row['quality'] = $newQuality;
	
	$row['link'] = preg_replace("#(\|H[A-Za-z0-9]+\:item\:[0-9]+)\:([0-9]+)\:([0-9]+)\:#", "$1:$intSubtype:$intLevel:", $row['link']);
	
	if ($row['maxCharges'] > 0) $row['maxCharges'] = FindEsoSparseMinedMaxCharges($intLevel, $intSubtype);
	
	$row['traitDesc'] = AdjustEsoSparseMinedItemTraitDesc($row['trait'], $row['traitDesc'], $newLevel, $newQuality);

	$enchantResult = AdjustEsoSparseMinedItemEnchantment($db, $tableSuffix, $row['enchantName'], $row['enchantDesc'], $intLevel, $intSubtype, $row['trait']);
	$row['enchantName'] = $enchantResult[0];
	$row['enchantDesc'] = $enchantResult[1];
	
		//name
			
	return $row;
}


function LoadEsoSparseMinedItemByQuality ($db, $itemId, $level, $quality, $tableSuffix = "")
{
	return null;
}


