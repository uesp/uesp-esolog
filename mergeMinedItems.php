<?php

$SOURCE_SUFFIX = "31";
$DEST_SUFFIX = "";

$TABLE_FIELDS = array(
		// "id",		// Do not include
		"itemId",
		"internalLevel",
		"internalSubtype",
		"potionData",
		"name",
		"icon",
		"level",
		"quality",
		"value",
		"armorRating",
		"weaponPower",
		"traitDesc",
		"enchantName",
		"enchantDesc",
		"glyphMinLevel",
		"maxCharges",
		"abilityDesc",
		"traitAbilityDesc",
		"setBonusDesc1",
		"setBonusDesc2",
		"setBonusDesc3",
		"setBonusDesc4",
		"setBonusDesc5",
		"setBonusDesc6",
		"setBonusDesc7",
	);

if (php_sapi_name() != "cli") die("Can only be run from command line!");

require("/home/uesp/secrets/esolog.secrets");
require("esoCommon.php");

if ($SOURCE_SUFFIX == $DEST_SUFFIX) exit("Error: Can't copy minedItem$SOURCE_SUFFIX table to itself!\n");
print("Merging mined items from minedItem$SOURCE_SUFFIX to minedItem$DEST_SUFFIX...\n");

$db = new mysqli($uespEsoLogWriteDBHost, $uespEsoLogWriteUser, $uespEsoLogWritePW, $uespEsoLogDatabase);
if ($db->connect_error) exit("Could not connect to mysql database!");

$MIN_ID = 64000;
$MAX_ID = 220000;

$cols = implode(",", $TABLE_FIELDS);

for ($id = $MIN_ID; $id <= $MAX_ID; $id++)
{
	if ($id % 1000 == 0) print("\t$id: Merging...\n");
	
	$result = $db->query("SELECT * FROM minedItem$SOURCE_SUFFIX WHERE itemId='$id';");
	if ($result === false) exit("Error: Failed to load minedItem$SOURCE_SUFFIX for $id!\n");
	
	$srcItems = array();
	
	while ($item = $result->fetch_assoc())
	{
		$intLevel = $item['internalLevel'];
		$intSubtype = $item['internalSubtype'];
		
		$srcItems["$id:$intLevel:$intSubtype"] = $item;
	}
	
	$result1 = $db->query("SELECT * FROM minedItem$DEST_SUFFIX WHERE itemId='$id';");
	if ($result1 === false) exit("Error: Failed to load minedItem$DEST_SUFFIX for $id!\n");
	
	$destItems = array();
	
	while ($item = $result1->fetch_assoc())
	{
		$intLevel = $item['internalLevel'];
		$intSubtype = $item['internalSubtype'];
		
		$destItems["$id:$intLevel:$intSubtype"] = $item;
	}
	
	foreach ($srcItems as $fullId => $srcItem)
	{
		$destItem = $destItems[$fullId];
		
		$intLevel = $srcItem['internalLevel'];
		$intSubtype = $srcItem['internalSubtype'];
		
		$vals = array();
		$setVals = array();
		
		foreach ($TABLE_FIELDS as $field)
		{
			$safeValue = $db->real_escape_string($srcItem[$field]);
			$vals[] = "'$safeValue'";
			$setVals[] = "$field='$safeValue'";
		}
		
		$vals = implode(",", $vals);
		$setVals = implode(",", $setVals);
		
		if ($destItem == null)
		{
			$query = "INSERT INTO minedItem$DEST_SUFFIX($cols) VALUES($vals);";
			$result = $db->query($query);
			if ($result === false) print("\tError: Failed to save new $fullId item to minedItem$DEST_SUFFIX! {$db->error}\n");
		}
		else
		{
			$destId = $destItem['id'];
			
			$query = "UPDATE minedItem$DEST_SUFFIX SET $setVals WHERE id='$destId';";
			$result = $db->query($query);
			if ($result === false) print("\tError: Failed to updating existing $fullId item in minedItem$DEST_SUFFIX! {$db->error}\n");
		}
	}
	
}

print("Done!\n");