<?php 

if (php_sapi_name() != "cli") die("Can only be run from command line!");

require("/home/uesp/secrets/esolog.secrets");
require("esoCommon.php");

$TABLE_SUFFIX = "";
$NUM_TESTS = 10000;
$QUIET = true;

$db = new mysqli($uespEsoLogReadDBHost, $uespEsoLogReadUser, $uespEsoLogReadPW, $uespEsoLogDatabase);
if ($db->connect_error) exit("Could not connect to mysql database!");

$rangedFields = [];
$validItemCount = 0;
$dynamicItemCount = 0;

$result = $db->query("SELECT MIN(itemId) as m1, MAX(itemId) as m2 FROM minedItemSummary$TABLE_SUFFIX;");
if ($result === false) exit("Failed to query maximum item id!");
$row = $result->fetch_assoc();
$minItemId = intval($row['m1']);
$maxItemId = intval($row['m2']);

print("Running $NUM_TESTS to determine which mined item fields have ranged/dynamic values...\n");
print("Using item IDs from $minItemId to $maxItemId\n");

for ($i = 0; $i < $NUM_TESTS; ++$i)
{
	$itemId = rand($minItemId, $maxItemId);
	
	$result1 = $db->query("SELECT * FROM minedItem$TABLE_SUFFIX WHERE itemId=$itemId AND internalLevel=1 and internalSubtype=1;");
	$result2 = $db->query("SELECT * FROM minedItem$TABLE_SUFFIX WHERE itemId=$itemId AND internalLevel=50 and internalSubtype=370;");
	
	if ($result1->num_rows <= 0) 
	{
		if (!$QUIET) print("\t$i) $itemId - No item\n");
		continue;
	}
	
	if ($result2->num_rows <= 0) 
	{
		if (!$QUIET) print("\t$i) $itemId - Not a dynamic item\n");
		continue;
	}
	
	++$validItemCount;
	if (!$QUIET) print("\t$i) $itemId ($validItemCount)\n");
	
	$rows1 = $result1->fetch_assoc();
	$rows2 = $result2->fetch_assoc();
	$dynamicColCount = 0;
	
	foreach ($rows1 as $col => $value1)
	{
		if ($col == "id") continue;
		if ($col == "itemId") continue;
		if ($col == "link") continue;
		if ($col == "internalLevel") continue;
		if ($col == "internalSubtype") continue;
		if ($col == "comment") continue;
		
		$value2 = $rows2[$col];
		
		if ($value1 != $value2) 
		{
			$rangedFields[$col] += 1;
			++$dynamicColCount;
			
			if ($col == "description")
			{
				print("\t\t$i) $itemId -- $col: $value1 : $value2\n");
			}
		}
	}
	
	if ($dynamicColCount > 0) ++$dynamicItemCount;
}

ksort($rangedFields);
$count = count($rangedFields);
print("Found $dynamicItemCount items out of $validItemCount valid items in $NUM_TESTS tests with $count dynamic fields:\n");

foreach ($rangedFields as $col => $count)
{
	print("\t$col = $count\n");
}

/* 10k Test
 *  	abilityDesc = 3		useAbilityDesc (potions and food)
        armorRating = 2326
        enchantDesc = 3388
        enchantName = 1565
        glyphMinLevel ?
        icon = 2472
        level = 4751
        maxCharges = 1818
        name = 82
        quality = 4743
        setBonusDesc1 = 4450
        setBonusDesc2 = 3955
        setBonusDesc3 = 4048
        setBonusDesc4 = 2381
        setBonusDesc5 = 225
        traitDesc = 4550
        value = 4740
        weaponPower = 2267
        
        
MinedItem Fields:
	itemId
	internalLevel
	internalSubtype
	itemLink
	potionData
	abilityDesc
	armorRating
	enchantName
	enchantDesc
	glyphMinLevel
	maxCharges
	icon
	level
	maxCharges
	name
	quality
	setBonusDesc1
	setBonusDesc2
	setBonusDesc3
	setBonusDesc4
	setBonusDesc5
	traitDesc
	value
	weaponPower
	comment
	
	
	X description
	X style
	X trait
	X type
	X specialType
	X equipType
	X weaponType
	X armorType
	X craftType
	X bindType
	x runeType
	X materialLevelDesc
	X cond
	X enchantId
	X enchantLevel
	X enchantSubtype
	x abilityName
	x abilityCooldown
	x setName
	x setBonusCount
	x setMaxEquipCount
	x setBonusCount1
	x setBonusCount2
	x setBonusCount3
	x setBonusCount4
	x setBonusCount5
	x glyphMaxLevel
	x siegeHP
	x bookTitle
	x craftSkillRank
	x recipeRank
	x recipeQuality
	x refinedItemLink
	x resultItemLink
	x traitCooldown
	x tags
	x dyeData
	x actorCategory
	x traitAbilityDesc

SummaryFields:

	Vary:
		allNames
		level
		value
		quality
		armorRating
		weaponPower
		enchantDesc
		maxCharges
		abilityDesc
		traitAbilityDesc ?
		setBonusDesc1
		setBonusDesc2
		setBonusDesc3
		setBonusDesc4
		setBonusDesc5
		
		icon
		enchantName
		potionData
		
	name
	description
	style
	trait
	type
	specialType
	equipType
	weaponType
	armorType
	craftType
	bindType
	runeType
	filterTypes
	isUnique
	isUniqueEquipped
	isVendorTrash
	isArmorDecay
	isConsumable
	abilityName
	abilityCooldown
	materialLevelDesc
	setName
	setId
	setBonusCount
	setMaxEquipCount
	setBonusCount1
	setBonusCount2
	setBonusCount3
	setBonusCount4
	setBonusCount5
	tags
	dyeData
	siegeHP
	bookTitle
	craftSkillRank
	recipeRank
	recipeQuality
	refinedItemLink
	resultItemLink
	traitCooldown
	
	actorCategory
	useType
	sellInfo
	traitTypeCategory
	combinationDesc
	combinationId
	defaultEnchantId
	furnLimitType
	furnDataId
	recipeListIndex
	recipeIndex
	containerCollectId
	containerSetName
	containerSetId
	refinedMaterialLink




Removed Fields:
	runeRank (same as craftSkillRank)
	glyphMaxLevel
	cond (not needed?)
	enchantId
	enchantLevel
	enchantSubtype
	refinedItemLink (duplicate of refinedMaterialLink)
Changed:"
	$logEntry['setDesc1'] = $logEntry['furnDataID'];
	$logEntry['setDesc2'] = $logEntry['furnCate'];
	$logEntry['setDesc3'] = $logEntry['furnSubCate'];
	$logEntry['setDesc4'] = $logEntry['furnCateName'];
	$logEntry['setDesc5'] = $logEntry['furnSubCateName'];
		to furnDataId and furnCategory fields
	
 */
