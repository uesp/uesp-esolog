<?php 

if (php_sapi_name() != "cli") die("Can only be run from command line!");

require("/home/uesp/secrets/esolog.secrets");

$FIELDS = array(
		"itemId",
		"name",
		"description",
		"style",
		"trait",
		"traitDesc",
		"type",
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
		"setBonusDesc1",
		"setBonusDesc2",
		"setBonusDesc3",
		"setBonusDesc4",
		"setBonusDesc5",
		"enchantName",
		"enchantDesc",
		"abilityName",
		"abilityDesc",
);

$RANGE_FIELDS = array(
		"value",
		"weaponPower",
		"armorRating",
		"abilityDesc",
		"enchantDesc",
		"traitDesc",
		"traitAbilityDesc",
		"setBonusDesc1",
		"setBonusDesc2",
		"setBonusDesc3",
		"setBonusDesc4",
		"setBonusDesc5",
);

$db = new mysqli($uespEsoLogWriteDBHost, $uespEsoLogWriteUser, $uespEsoLogWritePW, $uespEsoLogDatabase);
if ($db->connect_error) exit("Could not connect to mysql database!");

$query = "CREATE TABLE IF NOT EXISTS minedItemSummary(
			itemId INTEGER NOT NULL,
			name TINYTEXT NOT NULL,
			description TEXT NOT NULL,
			style TINYINT NOT NULL DEFAULT -1,
			trait TINYINT NOT NULL DEFAULT -1,
			traitDesc TINYTEXT NOT NULL,
			type TINYINT NOT NULL DEFAULT -1,
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
			setBonusDesc1 TINYTEXT NOT NULL,
			setBonusDesc2 TINYTEXT NOT NULL,
			setBonusDesc3 TINYTEXT NOT NULL,
			setBonusDesc4 TINYTEXT NOT NULL,
			setBonusDesc5 TINYTEXT NOT NULL,
			enchantName TINYTEXT NOT NULL,
			enchantDesc TINYTEXT NOT NULL,
			abilityName TINYTEXT NOT NULL,
			abilityDesc TINYTEXT NOT NULL,
			valueRange TINYTEXT NOT NULL,
			weaponPowerRange TINYTEXT NOT NULL,
			armorRatingRange TINYTEXT NOT NULL,
			abilityDescRange TINYTEXT NOT NULL,
			enchantDescRange TINYTEXT NOT NULL,
			traitDescRange TINYTEXT NOT NULL,
			traitAbilityDescRange TINYTEXT NOT NULL,
			setBonusDesc1Range TINYTEXT NOT NULL,
			setBonusDesc2Range TINYTEXT NOT NULL,
			setBonusDesc3Range TINYTEXT NOT NULL,
			setBonusDesc4Range TINYTEXT NOT NULL,
			setBonusDesc5Range TINYTEXT NOT NULL,
			PRIMARY KEY (itemId),
			INDEX index_style (style),
			INDEX index_trait (trait),
			INDEX index_type (type),
			INDEX index_weapontype (weaponType),
			INDEX index_armortype (armorType),
			INDEX index_equiptype (equipType),
			INDEX index_crafttype (craftType),
			FULLTEXT(name, description, abilityName, abilityDesc, enchantName, enchantDesc, traitDesc, setName, setBonusDesc1, setBonusDesc2, setBonusDesc3, setBonusDesc4, setBonusDesc5)
		);";

$result = $db->query($query);
if (!$result) exit("ERROR: Database query error creating table! " . $db->error);

$FIRSTID = 1;
$LASTID = 80000;

for ($id = $FIRSTID; $id <= $LASTID; $id++)
{
	if ($id % 100 == 0) print("Writing Item $id...\n");
	
	$query = "SELECT * FROM minedItem WHERE itemId=$id AND internalLevel=1 AND internalSubtype=2 LIMIT 1;";
	$result = $db->query($query);
	if (!$result) exit("ERROR: Database query error! " . $db->error);
	$minItemData = $result->fetch_assoc();
	
	if (!$minItemData)
	{
		$query = "SELECT * FROM minedItem WHERE itemId=$id LIMIT 1;";
		$result = $db->query($query);
		if (!$result) exit("ERROR: Database query error! " . $db->error);
		$minItemData = $result->fetch_assoc();
		if (!$minItemData) continue;
	}
	
	$query = "SELECT * FROM minedItem WHERE itemId=$id AND internalLevel=50 AND internalSubtype=312 LIMIT 1;";
	$result = $db->query($query);
	if (!$result) exit("ERROR: Database query error! " . $db->error);
	$maxItemData = $result->fetch_assoc();
	
	if (!$maxItemData)
	{
		$query = "SELECT * FROM minedItem where itemId=$id ORDER BY value DESC LIMIT 1;";
		$result = $db->query($query);
		if (!$result) exit("ERROR: Database query error! " . $db->error);
		$maxItemData = $result->fetch_assoc();
	}
	
	$columns = array();
	$values = array();
	
	foreach ($FIELDS as $field)
	{
		$value = "";
		if (array_key_exists($field, $minItemData)) $value = $db->escape_string($minItemData[$field]);
		
		$columns[] = $field;
		$values[] = "'$value'";
	}
	
	foreach ($RANGE_FIELDS as $field)
	{
		$minValue = $minItemData[$field];
		$maxValue = $maxItemData[$field];
		
		if (is_numeric($minValue))
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
			
			$minNumbers = preg_split("/([0-9]+\.?[0-9]?)/s", $minValue, -1, PREG_SPLIT_DELIM_CAPTURE);
			$maxNumbers = preg_split("/([0-9]+\.?[0-9]?)/s", $maxValue, -1, PREG_SPLIT_DELIM_CAPTURE);
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
			$values[] = "'" . $db->escape_string($value) . "'";
		}
		
		$columns[] = "{$field}Range";
	}
	
	$query  = "INSERT INTO minedItemSummary(" . implode(",", $columns) . ") VALUES(" . implode(",", $values) . ");";
	$result = $db->query($query);
	if (!$result) exit("ERROR: Database query error! " . $db->error);
}

?>