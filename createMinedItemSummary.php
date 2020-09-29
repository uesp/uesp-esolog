<?php 
if (php_sapi_name() != "cli") die("Can only be run from command line!");

require("/home/uesp/secrets/esolog.secrets");
require("esoCommon.php");

$TABLE_SUFFIX = "28pts";

$FIELDS = array(
		"itemId",
		"name",
		"description",
		"materialLevelDesc",
		"style",
		"trait",
		"type",
		"specialType",	//Update13
		"equipType",
		"weaponType",
		"armorType",
		"craftType",
		"bindType",
		"isUnique",
		"isUniqueEquipped",
		"isVendorTrash",
		"isArmorDecay",
		"isConsumable",
		"icon",
		"setName",
		"enchantName",
		"abilityName",
		"tags",
		"dyeData",
);

$RANGE_FIELDS = array(
		"level",
		"value",
		"weaponPower",
		"armorRating",
		"abilityDesc",
		"enchantDesc",
		"traitDesc",
		"quality",
		"traitAbilityDesc",
		"setBonusDesc1",
		"setBonusDesc2",
		"setBonusDesc3",
		"setBonusDesc4",
		"setBonusDesc5",
);

if (intval($TABLE_SUFFIX) <= 8)
{
	unset($FIELDS['tags']);
}

$db = new mysqli($uespEsoLogWriteDBHost, $uespEsoLogWriteUser, $uespEsoLogWritePW, $uespEsoLogDatabase);
if ($db->connect_error) exit("Could not connect to mysql database!");

$query = "DROP TABLE minedItemSummaryTmp IF EXISTS;";
$result = $db->query($query);

$query = "CREATE TABLE IF NOT EXISTS minedItemSummaryTmp(
			id BIGINT NOT NULL AUTO_INCREMENT,
			itemId INTEGER NOT NULL,
			name TINYTEXT NOT NULL,
			allNames TEXT NOT NULL,
			description TEXT NOT NULL,
			materialLevelDesc TEXT NOT NULL,
			style TINYINT NOT NULL DEFAULT -1,
			trait TINYINT NOT NULL DEFAULT -1,
			type TINYINT NOT NULL DEFAULT -1,
			specialType SMALLINT NOT NULL DEFAULT -1,
			equipType TINYINT NOT NULL DEFAULT -1,
			weaponType TINYINT NOT NULL DEFAULT -1,
			armorType TINYINT NOT NULL DEFAULT -1,
			craftType TINYINT NOT NULL DEFAULT -1,
			bindType TINYINT NOT NULL DEFAULT -1,
			isUnique BIT NOT NULL DEFAULT 0,
			isUniqueEquipped BIT NOT NULL DEFAULT 0,
			isVendorTrash BIT NOT NULL DEFAULT 0,
			isArmorDecay BIT NOT NULL DEFAULT 0,
			isConsumable BIT NOT NULL DEFAULT 0,
			icon TINYTEXT NOT NULL,
			setName TINYTEXT NOT NULL,
			enchantName TINYTEXT NOT NULL,
			abilityName TINYTEXT NOT NULL,
			level TINYTEXT NOT NULL,
			value TINYTEXT NOT NULL,
			quality TINYTEXT NOT NULL,
			weaponPower TINYTEXT NOT NULL,
			armorRating TINYTEXT NOT NULL,
			abilityDesc TEXT NOT NULL,
			enchantDesc TEXT NOT NULL,
			traitDesc TINYTEXT NOT NULL,
			traitAbilityDesc TINYTEXT NOT NULL,
			setBonusDesc1 TEXT NOT NULL,
			setBonusDesc2 TEXT NOT NULL,
			setBonusDesc3 TEXT NOT NULL,
			setBonusDesc4 TEXT NOT NULL,
			setBonusDesc5 TEXT NOT NULL,
			tags TINYTEXT NOT NULL,
			dyeData TEXT NOT NULL,
			PRIMARY KEY (id),
			INDEX index_style (style),
			INDEX index_trait (trait),
			INDEX index_type (type),
			INDEX index_weapontype (weaponType),
			INDEX index_armortype (armorType),
			INDEX index_equiptype (equipType),
			INDEX index_crafttype (craftType),
			FULLTEXT(name, description, abilityName, abilityDesc, enchantName, enchantDesc, traitDesc, setName, setBonusDesc1, setBonusDesc2, setBonusDesc3, setBonusDesc4, setBonusDesc5, tags, allNames)
		) ENGINE=MYISAM;";

$result = $db->query($query);
if (!$result) exit("ERROR: Database query error creating table!\n" . $db->error);

$FIRSTID = 3;		// 1/2 are potion/poison data
$LASTID = 180000;
$MINSUBTYPE = 0;		// Has problems with item enchantments missing
$MINSUBTYPE = 2;
$MAXSUBTYPE = 370;

for ($id = $FIRSTID; $id <= $LASTID; $id++)
{
	if ($id % 100 == 0) print("Parsing Item $id...\n");
	
	$query = "SELECT * FROM minedItem".$TABLE_SUFFIX." WHERE itemId=$id AND internalLevel=1 AND internalSubtype=$MINSUBTYPE LIMIT 1;";
	$result = $db->query($query);
	if (!$result) exit("ERROR: Database query error (finding min item)!\n" . $db->error);
	$minItemData = $result->fetch_assoc();
	
	if (!$minItemData)
	{
		$query = "SELECT * FROM minedItem".$TABLE_SUFFIX." WHERE itemId=$id AND internalLevel=1 AND internalSubtype=1 LIMIT 1;";
		$result = $db->query($query);
		if (!$result) exit("ERROR: Database query error (finding min item)!\n" . $db->error);
		$minItemData = $result->fetch_assoc();
		
		if (!$minItemData) 
		{
			$query = "SELECT * FROM minedItem".$TABLE_SUFFIX." WHERE itemId=$id LIMIT 1;";
			$result = $db->query($query);
			if (!$result) exit("ERROR: Database query error (finding min item v2)!\n" . $db->error);
			$minItemData = $result->fetch_assoc();
		}
		
		if (!$minItemData) continue;
	}
	
	$query = "SELECT * FROM minedItem".$TABLE_SUFFIX." WHERE itemId=$id AND internalLevel=50 AND internalSubtype=$MAXSUBTYPE LIMIT 1;";
	$result = $db->query($query);
	if (!$result) exit("ERROR: Database query error (finding max item)!\n" . $db->error);
	$maxItemData = $result->fetch_assoc();
	
	if (!$maxItemData)
	{
		$query = "SELECT * FROM minedItem".$TABLE_SUFFIX." where itemId=$id ORDER BY value DESC LIMIT 1;";
		$result = $db->query($query);
		if (!$result) exit("ERROR: Database query error (finding max item v2)!\n" . $db->error);
		$maxItemData = $result->fetch_assoc();
	}
	
	$allNames = array();
	$query = "SELECT name from minedItem".$TABLE_SUFFIX." where itemId=$id;";
	$result = $db->query($query);
	if (!$result) exit("ERROR: Database query error (finding all item names)!\n" . $db->error);
	
	while (($row = $result->fetch_assoc()))
	{
		$name = $row['name'];
		$allNames[$name] = 1;
	}
	
	$allNameValue = "";
	
	foreach ($allNames as $name => $value)
	{
		$allNameValue .= $name . "\n";
	}
	
	$columns = array();
	$values = array();
	
	foreach ($FIELDS as $field)
	{
		$value = "";
		
		if (array_key_exists($field, $minItemData)) 
		{
			//$value = $db->real_escape_string($minItemData[$field]);
			$value = $minItemData[$field];
		}
		
		if ($field == "icon" && array_key_exists($field, $maxItemData))
		{
			//$value = $db->real_escape_string($maxItemData[$field]);
			$value = $maxItemData[$field];
		}
		
		if ($value != "" && ($field == 'name' || $field == 'setName'))
		{
			$value = preg_replace("#Trifling #i", "", $value);
			$value = MakeEsoTitleCaseName($value);
		}
		
		$value = $db->real_escape_string($value);
		$columns[] = $field;
		$values[] = "'$value'";
	}
	
	foreach ($RANGE_FIELDS as $field)
	{
		$minValue = $minItemData[$field];
		$maxValue = $maxItemData[$field];
		
		if ($field == "level")
		{
			$minLevel = GetEsoItemLevelText($minValue);
			$maxLevel = GetEsoItemLevelText($maxValue);
				
			if ($maxLevel == null || $minLevel == $maxLevel)
				$values[] = "'$maxLevel'";
			else
				$values[] = "'$minLevel-$maxLevel'";
		}
		elseif (is_numeric($minValue))
		{
			if ($minValue == null || $minValue == $maxValue)
				$values[] = "'$minValue'";
			else
				$values[] = "'$minValue-$maxValue'";
		}
		else
		{
			//Grants a 3.0 point Damage Shield for 5.0 seconds
			//Life Drain Deals 4.0 Magic Damage and heals you for 2.0.
			//Increase weapon enchantment effect by 8.0%
			
			$minNumbers = preg_split("/([0-9]+(?:\.[0-9])?)/s", $minValue, -1, PREG_SPLIT_DELIM_CAPTURE);
			$maxNumbers = preg_split("/([0-9]+(?:\.[0-9])?)/s", $maxValue, -1, PREG_SPLIT_DELIM_CAPTURE);
			$value = "";
			
			for ($i = 0; $i < count($minNumbers); $i++)
			{
				$minBlock = $minNumbers[$i];
				$maxBlock = $maxNumbers[$i];
				if ($maxBlock == null) $maxBlock = $minBlock;
				
				if (is_numeric($minBlock[0]))
				{
					if ($minBlock == $maxBlock)
						$range = strval($minBlock);
					else
						$range = "$minBlock-$maxBlock";
						
					$value .= $range;
				}
				else
				{
					$value .= $minBlock;
				}
			}
			
			//print("\tMin: $minValue\n");
			//print("\tMax: $maxValue\n");
			//print("\tRange: $value\n");
			$values[] = "'" . $db->real_escape_string($value) . "'";
		}
		
		$columns[] = $field;
	}
	
	$columns[] = "allNames";
	$values[] = "'" . $db->real_escape_string($allNameValue) . "'";;
	
	$query  = "INSERT INTO minedItemSummaryTmp(" . implode(",", $columns) . ") VALUES(" . implode(",", $values) . ");";
	$result = $db->query($query);
	if (!$result) print("ERROR: Database query error (writing item summary)!\n" . $db->error . "\nQuery=".$query . "\n");
}

$query = "DROP TABLE IF EXISTS minedItemSummary$TABLE_SUFFIX;";
$result = $db->query($query);
if (!$result) print("Error: Failed to delete table to minedItemSummary$TABLE_SUFFIX!\n{$db->error}");

$query = "RENAME TABLE minedItemSummaryTmp to minedItemSummary$TABLE_SUFFIX;";
$result = $db->query($query);
if (!$result) print("Error: Failed to rename temp table to minedItemSummary$TABLE_SUFFIX!\n{$db->error}");
