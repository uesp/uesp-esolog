<?php 

if (php_sapi_name() != "cli") die("Can only be run from command line!");

require("/home/uesp/secrets/esolog.secrets");

$FIELDS = array(
		"itemId",
		"name",
		"description",
		"style",
		"trait",
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
);

$db = new mysqli($uespEsoLogWriteDBHost, $uespEsoLogWriteUser, $uespEsoLogWritePW, $uespEsoLogDatabase);
if ($db->connect_error) exit("Could not connect to mysql database!");

$query = "CREATE TABLE IF NOT EXISTS minedItemSummary(
			itemId INTEGER NOT NULL,
			name TINYTEXT NOT NULL,
			description TEXT NOT NULL,
			style TINYINT NOT NULL DEFAULT -1,
			trait TINYINT NOT NULL DEFAULT -1,
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
			PRIMARY KEY (itemId),
			INDEX index_style (style),
			INDEX index_trait (trait),
			INDEX index_type (type),
			INDEX index_weapontype (weaponType),
			INDEX index_armortype (armorType),
			INDEX index_equiptype (equipType),
			INDEX index_crafttype (craftType),
			FULLTEXT(name),
			FULLTEXT(description)
		);";

$result = $db->query($query);
if (!$result) exit("ERROR: Database query error! " . $db->error);

$FIRSTID = 1;
$LASTID = 80000;

for ($id = $FIRSTID; $id <= $LASTID; $id++)
{
	if ($id % 100 == 0) print("Writing Item $id...\n");
	
	$query = "SELECT * FROM minedItem WHERE itemId=$id LIMIT 1;";
	$result = $db->query($query);
	if (!$result) exit("ERROR: Database query error! " . $db->error);
	
	$result->data_seek(0);
	$row = $result->fetch_assoc();
	if (!$row) continue;
	
	$columns = "";
	$values = "";
	
	foreach ($FIELDS as $field)
	{
		if ($columns != "") $columns .= ",";
		if ($values != "") $values .= ",";
		
		$value = "";
		if (array_key_exists($field, $row)) $value = $db->escape_string($row[$field]);
		
		$columns .= $field;
		$values .= "'$value'";
	}
	
	$query  = "INSERT INTO minedItemSummary($columns) VALUES($values);";
	$result = $db->query($query);
	if (!$result) exit("ERROR: Database query error! " . $db->error);
}

?>