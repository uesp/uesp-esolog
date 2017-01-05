<?php

if (php_sapi_name() != "cli") die("Can only be run from command line!");

/*
 * Input Parameters:
 * 		start=[number]
 * 				Start parsing at the given LOG index.	
 * 
 * TODO:
 * 		- Add first and last timeStamp for each user
 * 		- Parse ItemLink
 * 		- Parse MailItem
 * 		- Parse VeteranXPUpdate
 * 		- Proper handling of quest items?
 * 		- Log locations/sources of items
 * 		- Base item and enchantments
 * 		- Log resource locations and types 
 * 		- Parse out item data into database fields
 * 		- Option to delete worms/crawlers/plump worms
 * 		- Display icon image
 * 		- Use esoCommon.php
 * 		- Properly parse/save trait abilities (traitAbility1...3, traitCooldown1...3)
 * 		- Rename minedItem "cond" to "condition"
 * 		- Review indexes on minedItem
 * 			- No FULLTEXT?
 * 			- Index on internalLevel/internalSubtype 
 *
 *
 */

	// Database users, passwords and other secrets
require("/home/uesp/secrets/esolog.secrets");


class EsoLogParser
{
	const SHOW_PARSE_LINENUMBERS = true;
	
	const ELP_INPUT_LOG_PATH = "";
	const ELP_INDEX_FILENAME = "esolog.index";
	const ELP_OUTPUTLOG_FILENAME = "parser.log";
	
	const TREASURE_DELTA_TIME = 4000;
	const BOOK_DELTA_TIME = 4000;
	const QUESTOFFERED_DELTA_TIME = 30000;
	
	const ELP_POSITION_FACTOR = 1000;	// Converts floating point position in log to integer value for db
	
	const ELP_SKILLCOEF_MININUM_R2 = -1;       //Log all coefficients for now
	const ELP_SKILLCOEF_MININUM_NUMPOINTS = 5;
	const ELP_SKILLCOEF_MAXCOEFVARS = 6;
	
	const ELP_THIEVESTROVE_LASTFIXTIMESTAMP = 4743900596690485248;
	
	//const START_MINEITEM_TIMESTAMP = 4743729922978086912; //v5
	//const START_MINEITEM_TIMESTAMP = 4743796906663084032; //v6
	//const START_MINEITEM_TIMESTAMP = 4743831656832434176; //v7
	//const START_MINEITEM_TIMESTAMP = 4743836443376300000; //v8pts
	//const START_MINEITEM_TIMESTAMP = 4743888214748560000; //v9pts	
	//const START_MINEITEM_TIMESTAMP = 4743853750546857984; //v8
	//const START_MINEITEM_TIMESTAMP = 4743899415482204160; //v9	 1457359600
	//const START_MINEITEM_TIMESTAMP = 4743917341752950784;	//v10pts 1461632912
	//const START_MINEITEM_TIMESTAMP = 4743923056748003328;	//v10	 1464706800
	//const START_MINEITEM_TIMESTAMP = 4743940391898710016;	//v11pts 1467127601
	//const START_MINEITEM_TIMESTAMP = 4743947678092620000;	//v11    1470075600
	//const START_MINEITEM_TIMESTAMP = 4743947678092620000;	//v12pts
	const START_MINEITEM_TIMESTAMP = 4743975994677000000;	//v12    1475650800
	//const START_MINEITEM_TIMESTAMP = 4744009321690431488;	//v13pts 1483541100
	
	const MINEITEM_TABLESUFFIX = "13pts";
	const SKILLS_TABLESUFFIX   = "13pts";
	
	public $db = null;
	private $dbReadInitialized  = false;
	private $dbWriteInitialized = false;
	public $lastQuery = "";
	public $skipCreateTables = false;
	
	public $currentLanguage = 'en';
	
	public $logFilePath = "";
	public $currentLogFilename = "tmp.log";
	public $currentLogIndex = 1;
	public $rawLogData = array();
	
	public $currentParseLine = 0;
	public $currentParseFile = "";
	public $startFileIndex = 0;
	
	public $duplicateCount = 0;
	public $fileDuplicateCount = 0;
	public $skipDuplicates = true;
	public $suppressDuplicateMsg = true;
	public $suppressMissingLocationMsg = true;
	
	public $lastValidTime = array();
	public $lastValidUserName = "Anonymous";
	public $lastSetCount6WarningItemId = -1;
	
	public $users = array();
	public $ipAddresses = array();
	
	public $currentLogEntryId = -1;
	public $currentUser = null;
	public $currentIpAddress = null;
	
	public $skillInfo = array();
	
	public $logInfos = array();
	
	const FIELD_INT = 1;
	const FIELD_STRING = 2;
	const FIELD_FLOAT = 3;
	
	const RESOURCE_UNKNOWN = -1;
	const RESOURCE_NONE = 0;
	const RESOURCE_ORE = 1;
	const RESOURCE_WOOD = 2;
	const RESOURCE_CLOTH = 3;
	const RESOURCE_LEATHER = 4;
	const RESOURCE_RUNESTONE = 5;
	const RESOURCE_REAGENT = 6;
	const RESOURCE_INGREDIENT = 7;
	
	
	public static $TABLES_WITH_IDFIELD = array(
			"minedSkills",
			"minedSkills8",
			"minedSkills8pts",
			"minedSkills9",
			"minedSkills9pts",
			"minedSkills10pts",
			"minedSkills10",
			"minedSkills11",
			"minedSkills11pts",
			"minedSkills12",
			"minedSkills12pts",
			"minedSkills13",
			"minedSkills13pts",
			"collectibles",
			"achievements",
	);
	
	
	public static $RESOURCE_TARGETS = array(
		'Iron Ore'				=> self::RESOURCE_ORE,
		'High Iron Ore'			=> self::RESOURCE_ORE,
		'Orichalcum Ore'		=> self::RESOURCE_ORE,
		'Dwarven Ore'			=> self::RESOURCE_ORE,
		'Ebony Ore'				=> self::RESOURCE_ORE,
		'Calcinium Ore'			=> self::RESOURCE_ORE,
		'Galatite Ore'			=> self::RESOURCE_ORE,
		'Quicksilver Ore'		=> self::RESOURCE_ORE,
		'Voidstone Ore'			=> self::RESOURCE_ORE,
			
		'Rough Maple'			=> self::RESOURCE_WOOD,
		'Rough Oak'				=> self::RESOURCE_WOOD,
		'Rough Beech'			=> self::RESOURCE_WOOD,
		'Rough Hickory'			=> self::RESOURCE_WOOD,
		'Rough Yew'				=> self::RESOURCE_WOOD,
		'Rough Birch'			=> self::RESOURCE_WOOD,
		'Rough Ash'				=> self::RESOURCE_WOOD,
		'Rough Mahogany'		=> self::RESOURCE_WOOD,
		'Rough Nightwood'		=> self::RESOURCE_WOOD,
			
		'Raw Jute'				=> self::RESOURCE_CLOTH,
		'Raw Flax'				=> self::RESOURCE_CLOTH,
		'Raw Cotton'			=> self::RESOURCE_CLOTH,
		'Raw Spidersilk'		=> self::RESOURCE_CLOTH,
		'Raw Ebonthread'		=> self::RESOURCE_CLOTH,
		'Kreshweed'				=> self::RESOURCE_CLOTH,
		'Ironweed'				=> self::RESOURCE_CLOTH,
		'Saint\'s Hair'			=> self::RESOURCE_CLOTH,
		'Void Bloom'			=> self::RESOURCE_CLOTH,
			
		'Rawhide Scraps'		=> self::RESOURCE_LEATHER,
		'Leather Scraps'		=> self::RESOURCE_LEATHER,
		'Thick Leather Scraps'	=> self::RESOURCE_LEATHER,
		'Hide Scraps'			=> self::RESOURCE_LEATHER,
		'Fellhide Scraps'		=> self::RESOURCE_LEATHER,
		'Topgrain Hide Scraps'	=> self::RESOURCE_LEATHER,
		'Iron Hide Scraps'		=> self::RESOURCE_LEATHER,
		'Superb Scraps'			=> self::RESOURCE_LEATHER,
		'Shadowhide Scraps'		=> self::RESOURCE_LEATHER,
			
		'Aspect Rune'			=> self::RESOURCE_RUNESTONE,
		'Potency Rune'			=> self::RESOURCE_RUNESTONE,
		'Essence Rune'			=> self::RESOURCE_RUNESTONE,
		
		'Pure Water'			=> self::RESOURCE_REAGENT,
		'Water Skin'			=> self::RESOURCE_REAGENT,
		'Water Skin'			=> self::RESOURCE_REAGENT,
		'Blessed Thistle'		=> self::RESOURCE_REAGENT,
		'Blue Entoloma'			=> self::RESOURCE_REAGENT,
		'Bugloss'				=> self::RESOURCE_REAGENT,
		'Columbine'				=> self::RESOURCE_REAGENT,
		'Corn Flower'			=> self::RESOURCE_REAGENT,
		'Dragonthorn'			=> self::RESOURCE_REAGENT,
		'Emetic Russula'		=> self::RESOURCE_REAGENT,
		'Imp Stool'				=> self::RESOURCE_REAGENT,
		'Lady\'s Smock'			=> self::RESOURCE_REAGENT,
		'Luminous Russula'		=> self::RESOURCE_REAGENT,
		'Mountain Flower'		=> self::RESOURCE_REAGENT,
		'Namira\'s Rot'			=> self::RESOURCE_REAGENT,
		'Nirnroot'				=> self::RESOURCE_REAGENT,
		'Stinkhorn'				=> self::RESOURCE_REAGENT,
		'Voilet Coprinus'		=> self::RESOURCE_REAGENT,
		'Water Hyacinth'		=> self::RESOURCE_REAGENT,
		'White Cap'				=> self::RESOURCE_REAGENT,
		'Wormwood'				=> self::RESOURCE_REAGENT,
			
		'Barrel'				=> self::RESOURCE_INGREDIENT,
		'Crate'					=> self::RESOURCE_INGREDIENT,
		'Barrels'				=> self::RESOURCE_INGREDIENT,
		'Crates'				=> self::RESOURCE_INGREDIENT,
		'Backpack'				=> self::RESOURCE_INGREDIENT,
		'Sack'					=> self::RESOURCE_INGREDIENT,
		'Bag'					=> self::RESOURCE_INGREDIENT,
	
	);
	
	public static $FIELD_NAMES = array(
			self::FIELD_INT => "integer",
			self::FIELD_STRING => "string",
			self::FIELD_FLOAT => "string",
	);
	
	public static $LOGINFO_FIELDS = array(
			'key' => self::FIELD_STRING,
			'value' => self::FIELD_STRING,
	);
	
	public static $BOOK_FIELDS = array(
			'id' => self::FIELD_INT,
			'logId' => self::FIELD_INT,
			'title' => self::FIELD_STRING,
			'body' => self::FIELD_STRING,
			'icon' => self::FIELD_STRING,
			'isLore' => self::FIELD_INT,
			'skill' => self::FIELD_STRING,
			'mediumIndex' => self::FIELD_INT,
			'categoryIndex' => self::FIELD_INT,
			'collectionIndex' => self::FIELD_INT,
			'bookIndex' => self::FIELD_INT,
			'guildIndex' => self::FIELD_INT,
	);
	
	public static $ITEM_FIELDS = array(
			'id' => self::FIELD_INT,
			'logId' => self::FIELD_INT,
			'link' => self::FIELD_STRING,
			'name' => self::FIELD_STRING,
			'icon' => self::FIELD_STRING,
			'color' => self::FIELD_STRING,
			'style' => self::FIELD_INT,
			'trait' => self::FIELD_INT,
			'quality' => self::FIELD_INT,
			'locked' => self::FIELD_INT,
			'type' => self::FIELD_INT,
			'equipType' => self::FIELD_INT,
			'craftType' => self::FIELD_INT,
			'value' => self::FIELD_INT,
			'level' => self::FIELD_INT,
	);
	
	public static $LOCATION_FIELDS = array(
			'id' => self::FIELD_INT,
			'logId' => self::FIELD_INT,
			'bookId' => self::FIELD_INT,
			'npcId' => self::FIELD_INT,
			'questId' => self::FIELD_INT,
			'questStageId' => self::FIELD_INT,
			'itemId' => self::FIELD_INT,
			'type' => self::FIELD_STRING,
			'name' => self::FIELD_STRING,
			'count' => self::FIELD_INT,
			'x' => self::FIELD_INT,
			'y' => self::FIELD_INT,
			'rawX' => self::FIELD_FLOAT,
			'rawY' => self::FIELD_FLOAT,
			'zone' => self::FIELD_STRING,
	);
	
	public static $QUEST_FIELDS = array(
			'id' => self::FIELD_INT,
			'logId' => self::FIELD_INT,
			'locationId' => self::FIELD_INT,
			'name' => self::FIELD_STRING,
			'objective' => self::FIELD_STRING,
	);
	
	public static $QUESTSTAGE_FIELDS = array(
			'id' => self::FIELD_INT,
			'logId' => self::FIELD_INT,
			'questId' => self::FIELD_INT,
			'locationId' => self::FIELD_INT,
			'objective' => self::FIELD_STRING,
			'overrideText' => self::FIELD_STRING,
			'orderIndex' => self::FIELD_INT,
			'type' => self::FIELD_INT,
			'counter' => self::FIELD_INT,
			'isFail' => self::FIELD_INT,
			'isPushed' => self::FIELD_INT,
			'isHidden' => self::FIELD_INT,
			'isComplete' => self::FIELD_INT,
	);
		
	public static $QUESTITEM_FIELDS = array(
			'id' => self::FIELD_INT,
			'logId' => self::FIELD_INT,
			'questId' => self::FIELD_INT,
			'itemLink' => self::FIELD_STRING,
			'questName' => self::FIELD_STRING,
			'name' => self::FIELD_STRING,
			'itemId' => self::FIELD_INT,
			'header' => self::FIELD_STRING,
			'icon' => self::FIELD_STRING,
			'description' => self::FIELD_STRING,
			'stepIndex' => self::FIELD_INT,
			'conditionIndex' => self::FIELD_INT,
	);
	
	public static $NPC_FIELDS = array(
			'id' => self::FIELD_INT,
			'logId' => self::FIELD_INT,
			'name' => self::FIELD_STRING,
			'level' => self::FIELD_INT,
			'gender' => self::FIELD_INT,
			'difficulty' => self::FIELD_INT,
	);
	
	public static $RECIPE_FIELDS = array(
			'id' => self::FIELD_INT,
			'logId' => self::FIELD_INT,
			'resultItemId' => self::FIELD_INT,
			'name' => self::FIELD_STRING,
			'level' => self::FIELD_INT,
			'type' => self::FIELD_INT,
			'quality' => self::FIELD_INT,
	);
	
	public static $INGREDIENT_FIELDS = array(
			'id' => self::FIELD_INT,
			'logId' => self::FIELD_INT,
			'recipeId' => self::FIELD_INT,
			'itemId' => self::FIELD_INT,
			'name' => self::FIELD_STRING,
			'quantity' => self::FIELD_INT,
	);
	
	public static $CHEST_FIELDS = array(
			'id' => self::FIELD_INT,
			'locationId' => self::FIELD_INT,
			'quality' => self::FIELD_INT
	);
	
	public static $MINEDITEM_FIELDS = array(
			'id' => self::FIELD_INT,
			'logId' => self::FIELD_INT,
			'link' => self::FIELD_STRING,
			'itemId' => self::FIELD_INT,
			'internalLevel' => self::FIELD_INT,
			'internalSubtype' => self::FIELD_INT,
			'potionData' => self::FIELD_INT,
			'name' => self::FIELD_STRING,
			'description' => self::FIELD_STRING,
			'icon' => self::FIELD_STRING,
			'style' => self::FIELD_INT,
			'trait' => self::FIELD_INT,
			'quality' => self::FIELD_INT,
			'type' => self::FIELD_INT,
			'equipType' => self::FIELD_INT,
			'weaponType' => self::FIELD_INT,
			'armorType' => self::FIELD_INT,
			'craftType' => self::FIELD_INT,
			'armorRating' => self::FIELD_INT,
			'weaponPower' => self::FIELD_INT,
			'value' => self::FIELD_INT,
			'level' => self::FIELD_INT,
			'cond' => self::FIELD_INT,
			'enchantId' => self::FIELD_INT,
			'enchantLevel' => self::FIELD_INT,
			'enchantSubtype' => self::FIELD_INT,
			'enchantName' => self::FIELD_STRING,
			'enchantDesc' => self::FIELD_STRING,
			'maxCharges' => self::FIELD_INT,
			'abilityName' => self::FIELD_STRING,
			'abilityDesc' => self::FIELD_STRING,
			'abilityCooldown' => self::FIELD_INT,
			'setName' => self::FIELD_STRING,
			'setBonusCount' => self::FIELD_INT,
			'setMaxEquipCount' => self::FIELD_INT,
			'setBonusCount1' => self::FIELD_INT,
			'setBonusCount2' => self::FIELD_INT,
			'setBonusCount3' => self::FIELD_INT,
			'setBonusCount4' => self::FIELD_INT,
			'setBonusCount5' => self::FIELD_INT,
			'setBonusDesc1' => self::FIELD_STRING,
			'setBonusDesc2' => self::FIELD_STRING,
			'setBonusDesc3' => self::FIELD_STRING,
			'setBonusDesc4' => self::FIELD_STRING,
			'setBonusDesc5' => self::FIELD_STRING,
			'glyphMinLevel' => self::FIELD_INT,
			'glyphMaxLevel' => self::FIELD_INT,
			'runeType' => self::FIELD_INT,
			'runeRank' => self::FIELD_INT,
			'bindType' => self::FIELD_INT,
			'siegeHP' => self::FIELD_INT,
			'bookTitle' => self::FIELD_STRING,
			'craftSkillRank' => self::FIELD_INT,
			'recipeRank' => self::FIELD_INT,
			'recipeQuality' => self::FIELD_INT,
			'refinedItemLink' => self::FIELD_STRING,
			'resultItemLink' => self::FIELD_STRING,
			'traitDesc' => self::FIELD_STRING,
			'traitAbilityDesc' => self::FIELD_STRING,
			'traitCooldown' => self::FIELD_INT,
			'materialLevelDesc' => self::FIELD_STRING,
			'isUnique' => self::FIELD_INT,
			'isUniqueEquipped' => self::FIELD_INT,
			'isVendorTrash' => self::FIELD_INT,
			'isArmorDecay' => self::FIELD_INT,
			'isConsumable' => self::FIELD_INT,
			'comment' => self::FIELD_STRING,
			'tags' => self::FIELD_STRING,
			'dyeData' => self::FIELD_STRING,
	);
	
	public static $MINED_ITEMKEY_TO_DBKEY = array(
			'itemLink' => 'link',
			'itemId' => 'itemId',
			'internalLevel' => 'internalLevel',
			'internalSubtype' => 'internalSubtype',
			'potionData' => 'potionData',
			'name' => 'name',
			'flavourText' => 'description',
			'icon' => 'icon',
			'itemStyle' => 'style',
			'trait' => 'trait',
			'quality' => 'quality',
			'type' => 'type',
			'equipType' => 'equipType',
			'weaponType' => 'weaponType',
			'armorType' => 'armorType',
			'craftSkill' => 'craftType',
			'armorRating' => 'armorRating',
			'weaponPower' => 'weaponPower',
			'value' => 'value',
			'level' => 'level',
			'condition' => 'cond',
			'minGlyphLevel' => 'glyphMinLevel',
			'maxGlyphLevel' => 'glyphMaxLevel',
			'enchantId' => 'enchantId',
			'enchantLevel' => 'enchantLevel',
			'enchantSubtype' => 'enchantSubtype',
			'enchantName' => 'enchantName',
			'enchantDesc' => 'enchantDesc',
			'maxCharges' => 'maxCharges',
			'useAbilityName' => 'abilityName',
			'useAbilityDesc' => 'abilityDesc',
			'useCooldown' => 'abilityCooldown',
			'setName' => 'setName',
			'setBonusCount' => 'setBonusCount',
			'setMaxCount' => 'setMaxEquipCount',
			'setBonus1' => 'setBonusCount1',
			'setBonus2' => 'setBonusCount2',
			'setBonus3' => 'setBonusCount3',
			'setBonus4' => 'setBonusCount4',
			'setBonus5' => 'setBonusCount5',
			'setDesc1' => 'setBonusDesc1',
			'setDesc2' => 'setBonusDesc2',
			'setDesc3' => 'setBonusDesc3',
			'setDesc4' => 'setBonusDesc4',
			'setDesc5' => 'setBonusDesc5',
			'runeType' => 'runeType',
			'runeRank' => 'runeRank',
			'bindType' => 'bindType',
			'siegeHP' => 'siegeHP',
			'bookTitle' => 'bookTitle',
			'craftSkillRank' => 'craftSkillRank',
			'recipeRank' => 'recipeRank',
			'recipeQuality' => 'recipeQuality',
			'refinedItemLink' => 'refinedItemLink',
			'recipeLink' => 'resultItemLink',
			'traitDesc' => 'traitDesc',
			'traitAbility1' => 'traitAbilityDesc',
			'matLevelDesc' => 'materialLevelDesc',
			'traitCooldown1' => 'traitCooldown',
			'isUnique' => 'isUnique',
			'isUniqueEquipped' => 'isUniqueEquipped',
			'isVendorTrash' => 'isVendorTrash',
			'isArmorDecay' => 'isArmorDecay',
			'isConsumable' => 'isConsumable',
			'comment' => 'comment',
			'tags' => 'tags',
			'dyeData' => 'dyeData',
			//runeName
			//ingrName1-N
	);
	
	
	public static $SKILLDUMP_FIELDS = array(
			'id' => self::FIELD_INT,
			'name' => self::FIELD_STRING,
			'description' => self::FIELD_STRING,
			'duration' => self::FIELD_INT,
			'cost' => self::FIELD_INT,
			'target' => self::FIELD_STRING,
			'minRange' => self::FIELD_INT,
			'maxRange' => self::FIELD_INT,
			'radius' => self::FIELD_INT,
			'isPassive' => self::FIELD_INT,
			'isChanneled' => self::FIELD_INT,
			'castTime' => self::FIELD_INT,
			'channelTime' => self::FIELD_INT,
			'angleDistance' => self::FIELD_INT,
			'mechanic' => self::FIELD_INT,
			'upgradeLines' => self::FIELD_STRING,
			'effectLines' => self::FIELD_STRING,
			'texture'  => self::FIELD_STRING,
			'skillType'  => self::FIELD_INT,
			'isPlayer'  => self::FIELD_INT,
			'raceType'  => self::FIELD_STRING,
			'classType'  => self::FIELD_STRING,
			'prevSkill'  => self::FIELD_INT,
			'nextSkill'  => self::FIELD_INT,
			'nextSkill2'  => self::FIELD_INT,
			'rank'  => self::FIELD_INT,
			'learnedLevel'  => self::FIELD_INT,
			'skillLine' => self::FIELD_STRING,
			'skillIndex' => self::FIELD_INT,
			'numCoefVars' => self::FIELD_INT,
			'coefDescription' =>  self::FIELD_STRING,
			'type1' => self::FIELD_INT,
			'a1' => self::FIELD_FLOAT,
			'b1' => self::FIELD_FLOAT,
			'c1' => self::FIELD_FLOAT,
			'R1' => self::FIELD_FLOAT,
			'avg1' => self::FIELD_FLOAT,
			'type2' => self::FIELD_INT,
			'a2' => self::FIELD_FLOAT,
			'b2' => self::FIELD_FLOAT,
			'c2' => self::FIELD_FLOAT,
			'R2' => self::FIELD_FLOAT,
			'avg2' => self::FIELD_FLOAT,
			'type3' => self::FIELD_INT,
			'a3' => self::FIELD_FLOAT,
			'b3' => self::FIELD_FLOAT,
			'c3' => self::FIELD_FLOAT,
			'R3' => self::FIELD_FLOAT,
			'avg3' => self::FIELD_FLOAT,
			'type4' => self::FIELD_INT,
			'a4' => self::FIELD_FLOAT,
			'b4' => self::FIELD_FLOAT,
			'c4' => self::FIELD_FLOAT,
			'R4' => self::FIELD_FLOAT,
			'avg4' => self::FIELD_FLOAT,
			'type5' => self::FIELD_INT,
			'a5' => self::FIELD_FLOAT,
			'b5' => self::FIELD_FLOAT,
			'c5' => self::FIELD_FLOAT,
			'R5' => self::FIELD_FLOAT,
			'avg5' => self::FIELD_FLOAT,
			'type6' => self::FIELD_INT,
			'a6' => self::FIELD_FLOAT,
			'b6' => self::FIELD_FLOAT,
			'c6' => self::FIELD_FLOAT,
			'R6' => self::FIELD_FLOAT,
			'avg6' => self::FIELD_FLOAT,
	);
	
	
	public static $SKILLLINE_FIELDS = array(
			'id' => self::FIELD_INT,
			'name' => self::FIELD_STRING,
			'fullName' => self::FIELD_STRING,
			'skillType' => self::FIELD_INT,
			'raceType' =>  self::FIELD_STRING,
			'classType' =>  self::FIELD_STRING,
			'numRanks' => self::FIELD_INT,
			'xp' => self::FIELD_STRING,
			'totalXp' => self::FIELD_INT,
	);
	
	
	public static $CPDISCIPLINE_FIELDS = array(
			'id' => self::FIELD_INT,
			'disciplineIndex' => self::FIELD_INT,
			'name' => self::FIELD_STRING,
			'description' => self::FIELD_STRING,
			'attribute' => self::FIELD_INT,
	);
	
	public static $CPSKILL_FIELDS = array(
			'id' => self::FIELD_INT,
			'abilityId' => self::FIELD_INT,
			'disciplineIndex' => self::FIELD_INT,
			'skillIndex' => self::FIELD_INT,
			'unlockLevel' => self::FIELD_INT,
			'name' => self::FIELD_STRING,
			'minDescription' => self::FIELD_STRING,
			'maxDescription' => self::FIELD_STRING,
			'maxValue' => self::FIELD_FLOAT,
			'x' => self::FIELD_FLOAT,
			'y' => self::FIELD_FLOAT,
			'a' => self::FIELD_FLOAT,
			'b' => self::FIELD_FLOAT,
			'r2' => self::FIELD_FLOAT,
			'fitDescription' => self::FIELD_STRING,
	);
	
	public static $CPSKILLDESCRIPTION_FIELDS = array(
			'id' => self::FIELD_INT,
			'abilityId' => self::FIELD_INT,
			'description' => self::FIELD_STRING,
			'points' => self::FIELD_INT,
	);
	
	
	public static $COLLECTIBLE_FIELDS = array(
			'id' => self::FIELD_INT,
			'name' => self::FIELD_STRING,
			'nickname' => self::FIELD_STRING,
			'description' => self::FIELD_STRING,
			'itemLink' => self::FIELD_STRING,
			'hint' => self::FIELD_STRING,
			'icon' => self::FIELD_STRING,
			'backgroundIcon' => self::FIELD_STRING,
			'lockedIcon' => self::FIELD_STRING,
			'categoryType' => self::FIELD_INT,
			'zoneIndex' => self::FIELD_INT,
			'categoryIndex' => self::FIELD_INT,
			'subCategoryIndex' => self::FIELD_INT,
			'collectibleIndex' => self::FIELD_INT,
			'achievementIndex' => self::FIELD_INT,
			'categoryName' => self::FIELD_STRING,
			'subCategoryName' => self::FIELD_STRING,
			'isUnlocked' => self::FIELD_INT,
			'isActive' => self::FIELD_INT,
			'isSlottable' => self::FIELD_INT,
			'isUsable' => self::FIELD_INT,
			'isRenameable' => self::FIELD_INT,
			'isPlaceholder' => self::FIELD_INT,
			'isHidden' => self::FIELD_INT,
			'hasAppearance' => self::FIELD_INT,
			'visualPriority' => self::FIELD_INT,
			'helpCategoryIndex' => self::FIELD_INT,
			'helpIndex' => self::FIELD_INT,
			'questName' => self::FIELD_STRING,
			'backgroundText' => self::FIELD_STRING,
			'cooldown' => self::FIELD_INT,
	);
	
	public static $ACHIEVEMENTCATEGORY_FIELDS = array(
			'id' => self::FIELD_INT,
			'name' => self::FIELD_STRING,
			'categoryName' => self::FIELD_STRING,
			'subcategoryName' => self::FIELD_STRING,
			'categoryIndex' => self::FIELD_INT,
			'subCategoryIndex' => self::FIELD_INT,
			'numAchievements' => self::FIELD_INT,
			'points' => self::FIELD_INT,
			'hidesPoints' => self::FIELD_INT,
			'icon' => self::FIELD_STRING,
			'pressedIcon' => self::FIELD_STRING,
			'mouseoverIcon' => self::FIELD_STRING,
			'gamepadIcon' => self::FIELD_STRING,
	);
		
	
	public static $ACHIEVEMENT_FIELDS = array(
			'id' => self::FIELD_INT,
			'name' => self::FIELD_STRING,
			'description' => self::FIELD_STRING,
			'categoryIndex' => self::FIELD_INT,
			'subCategoryIndex' => self::FIELD_INT,
			'achievementIndex' => self::FIELD_INT,
			'categoryName' =>  self::FIELD_STRING,
			'points' => self::FIELD_INT,
			'icon' => self::FIELD_STRING,
			'numRewards' => self::FIELD_INT,
			'itemLink' => self::FIELD_STRING,
			'link' => self::FIELD_STRING,
			'firstId' => self::FIELD_INT,
			'prevId' => self::FIELD_INT,
			'points' => self::FIELD_INT,
			'itemName' => self::FIELD_STRING,
			'itemIcon' => self::FIELD_STRING,
			'itemQuality' => self::FIELD_INT,
			'title' => self::FIELD_STRING,
			'collectibleId' => self::FIELD_INT,
			'dyeId' => self::FIELD_INT,
			'dyeName' => self::FIELD_STRING,
			'dyeRarity' => self::FIELD_INT,
			'dyeHue' => self::FIELD_INT,
			'dyeColor' => self::FIELD_STRING,
	);
	
	
	public static $ACHIEVEMENTCRITERIA_FIELDS = array(
			'id' => self::FIELD_INT,
			'achievementId' => self::FIELD_INT,
			'description' => self::FIELD_STRING,
			'numRequired' => self::FIELD_INT,
			'criteriaIndex' => self::FIELD_INT,
	);
	
	
	public function __construct ()
	{
		
		if (intval(self::MINEITEM_TABLESUFFIX) <= 8)
		{
			unset(self::$MINEDITEM_FIELDS['tags']);
		}
		
		$this->initDatabaseWrite();
		$this->readIndexFile();
		$this->currentLogFilename = $this->getCurrentLogFilename();
		$this->setInputParams();
		$this->parseInputParams();
	}
	
	
	public function GetSkillTypeText ($value)
	{
		static $VALUES = array(
				-1 => "",
				0 => "",
				1 => "Class",
				2 => "Weapon",
				3 => "Armor",
				4 => "World",
				5 => "Guild",
				6 => "Alliance War",
				7 => "Racial",
				8 => "Craft",
				9 => "Champion",
		);
		
		$key = (int) $value;
		
		if (array_key_exists($key, $VALUES)) return $VALUES[$key];
		return "Unknown ($key)";
	}
	
	
	public function createNewRecord ($fieldDef)
	{
		$newRecord = array();
		
		foreach ($fieldDef as $key => $value)
		{
			if ($value == self::FIELD_INT)
			{
				$newRecord[$key] = -1;
			}
			elseif ($value == self::FIELD_FLOAT)
			{
				$newRecord[$key] = -1;
			}
			elseif ($value == self::FIELD_STRING)
			{
				$newRecord[$key] = '';
			}
		}
		
		$newRecord['logId'] = $this->currentLogEntryId;
		$newRecord['__isNew'] = true;
		$newRecord['__dirty'] = true;
		
		return $newRecord;
	}
	
	
	public function createNewRecordID ($idField, $id, $fieldDef)
	{
		$record = $this->createNewRecord($fieldDef);
		$record[$idField] = $id;
		
		return $record;
	}
	
	
	public function createNewRecordID2 ($idField, $id, $idField2, $id2, $fieldDef)
	{
		$record = $this->createNewRecord($fieldDef);
		$record[$idField] = $id;
		$record[$idField2] = $id2;
		
		return $record;
	}
	
	
	public function createSelectQuery ($table, $idField, $id, $fieldDef)
	{
		$idType = $fieldDef[$idField];
		if ($idType == null) return $this->reportError("Unknown ID field $idField in $table table!");
		
		if ($idType == self::FIELD_INT)
			$query = "SELECT * FROM $table WHERE $idField=$id LIMIT 1;";
		elseif ($idType == self::FIELD_STRING)
			$query = "SELECT * FROM $table WHERE $idField='". $this->db->real_escape_string($id) ."' LIMIT 1;";
		else
			return $this->reportError("Unknown ID type $idType in $table table!");
		
		$this->lastQuery = $query;
		return $query;
	}
	
	
	public function createSelectQuery2 ($table, $idField, $id, $idField2, $id2, $fieldDef)
	{
		$idType = $fieldDef[$idField];
		if ($idType == null) return $this->reportError("Unknown ID field $idField in $table table!");
		
		$idType2 = $fieldDef[$idField2];
		if ($idType2 == null) return $this->reportError("Unknown ID field $idField2 in $table table!");
		
		if ($idType == self::FIELD_INT)
			$query1 = "$idField=$id";
		elseif ($idType == self::FIELD_STRING)
			$query1 = "$idField='". $this->db->real_escape_string($id) ."'";
		else
			return $this->reportError("Unknown ID type $idType in $table table!");
		
		if ($idType2 == self::FIELD_INT)
			$query2 = "$idField2=$id2";
		elseif ($idType2 == self::FIELD_STRING)
			$query2 = "$idField2='". $this->db->real_escape_string($id2) ."'";
		else
			return $this->reportError("Unknown ID type $idType2 in $table table!");
		
		$this->lastQuery = "SELECT * FROM $table WHERE $query1 AND $query2 LIMIT 1";
		return $this->lastQuery;
	}
	
	
	public function createRecordFromRow ($row, $fieldDef)
	{
		$record = array();
		
		foreach ($fieldDef as $key => $value)
		{
			if (array_key_exists($key, $row))
			{
				$record[$key] = $row[$key];
		
				settype($record[$key], self::$FIELD_NAMES[$value]);
			}
			elseif ($value == self::FIELD_INT)
			{
				$record[$key] = -1;
			}
			elseif ($value == self::FIELD_FLOAT)
			{
				$record[$key] = -1;
			}
			elseif ($value == self::FIELD_STRING)
			{
				$record[$key] = '';
			}
		}
		
		$record['__isNew'] = false;
		$record['__dirty'] = false;
		return $record;
	}
	
	
	public function loadRecord ($table, $idField, $id, $fieldDef)
	{
		if ($id == null) return $this->createNewRecordID($idField, $id, $fieldDef);
		
		$query = $this->createSelectQuery($table, $idField, $id, $fieldDef);
		if ($query === false) return false;
		
		$result = $this->db->query($query);
		if ($result === FALSE) return $this->reportError("Failed to load record $id from $table table!");
		
		if ($result->num_rows === 0) return $this->createNewRecordID($idField, $id, $fieldDef);
		
		$result->data_seek(0);
		$row = $result->fetch_assoc();
		
		return $this->createRecordFromRow($row, $fieldDef);
	}
	
	
	public function loadRecord2 ($table, $idField, $id, $idField2, $id2, $fieldDef)
	{
		if ($id == null || $id2 == null) return $this->createNewRecordID2($idField, $id, $idField2, $id2, $fieldDef);
		
		$query = $this->createSelectQuery2($table, $idField, $id, $idField2, $id2, $fieldDef);
		if ($query === false) return false;
	
		$result = $this->db->query($query);
		if ($result === FALSE) return $this->reportError("Failed to load record $id from $table table!");
	
		if ($result->num_rows === 0) return $this->createNewRecordID2($idField, $id, $idField2, $id2, $fieldDef);
	
		$result->data_seek(0);
		$row = $result->fetch_assoc();
	
		return $this->createRecordFromRow($row, $fieldDef);
	}
	
	
	public function LoadLogInfo ()
	{
		$query = "SELECT * FROM logInfo;";
		$this->lastQuery = $query;
		
		$result = $this->db->query($query);
		if ($result === false) return $this->reportError("Failed to load records from logInfo table!");
		
		$records = array();
		if ($result->num_rows === 0) return true;
		
		$result->data_seek(0);
		
		while (($row = $result->fetch_assoc())) 
		{
			$key = $row['id'];
			$value = $row['value'];
			$records[$key] = $value;
		}
		
		$this->logInfos = $records;
		return true;
	}
	
	
	public function SaveLogInfo ()
	{
		foreach ($this->logInfos as $key => $value)
		{
			$safeKey = $this->db->real_escape_string($key);
			$safeValue = $this->db->real_escape_string($value);
			$query = "INSERT INTO logInfo(id, value) VALUES('$safeKey', '$safeValue') ON DUPLICATE KEY UPDATE id='$safeKey', value='$safeValue';";
			$this->lastQuery = $query;
			
			$result = $this->db->query($query);
			if ($result === false) return $this->reportError("Failed to save record info logInfo table!");
		}
		
		return true;
	}
	
	
	public function createUpdateQuery ($table, $record, $idField, $fieldDef)
	{
		$idType = $fieldDef[$idField];
		if ($idType == null) return $this->reportError("Unknown ID field $idField in $table table!");
		
		$id = $record[$idField];
		if ($id == null) return $this->reportError("$table record missing ID field $idField value!");
		
		$query = "UPDATE $table SET ";
		$isFirst = true;
		
		foreach ($fieldDef as $key => $value)
		{
			if ($key === $idField) continue;
			if ($key === 'id') continue;
			
			if (!array_key_exists($key, $record))
			{
				$this->reportError("Missing value for $key field in $table table!");
				continue;
			}
			
			if (!$isFirst) $query .= ', ';
			
			if ($value == self::FIELD_INT || $value == self::FIELD_FLOAT)
			{
				if ($record[$key] === null || $record[$key] === '' )
					$query .= "{$key}=-1";
				else
					$query .= "{$key}={$record[$key]}";
			}
			elseif ($value == self::FIELD_STRING)
				$query .= "{$key}='". $this->db->real_escape_string($record[$key]) ."'";
			else
				$this->reportError("Unknown ID type $value found for $key field in $table table!");
			
			$isFirst = false;
		}
		
		if ($idType == self::FIELD_INT)
			$query .= " WHERE $idField=$id;";
		elseif ($idType == self::FIELD_FLOAT)
			$query .= " WHERE $idField=$id;";
		elseif ($idType == self::FIELD_STRING)
			$query .= " WHERE $idField='". $this->db->real_escape_string($id) ."';";
		else
			return $this->reportError("Unknown ID type $idType in $table table!");
	
		$this->lastQuery = $query;
		return $query;
	}
	
	
	public function AddSkillInfo ($index, $name, $type)
	{
		if (!array_key_exists($type, $this->skillInfo)) $this->skillInfo[$type] = array();
		if (array_key_exists($index, $this->skillInfo[$type])) return true;
		
		$this->skillInfo[$type][$index] = $name;
		
		return true;
	}
	
	
	public function DumpSkillInfo ()
	{
		foreach ($this->skillInfo as $type => $value)
		{
			foreach ($value as $index => $name)
			{
				print("$type , $index, $name\n");
			}
		}
	}
	
	
	public function createInsertQuery ($table, $record, $fieldDef)
	{
		$columns = "";
		$values = "";
		$isFirst = true;
		
		foreach ($fieldDef as $key => $value)
		{
			if ($key === 'id' && !in_array($table, self::$TABLES_WITH_IDFIELD)) continue;
			
			if (!array_key_exists($key, $record))
			{
				$this->reportError("Missing value for $key field in $table table!");
				continue;
			}
				
			if (!$isFirst)
			{
				$columns .= ', ';
				$values  .= ', ';
			}
			
			$columns .= $key;
				
			if ($value == self::FIELD_INT || $value == self::FIELD_FLOAT)
			{
				if ($record[$key] === null || $record[$key] === '' )
					$values .= '-1';
				else
					$values .= $record[$key];
			}
			elseif ($value == self::FIELD_STRING)
				$values .= "'". $this->db->real_escape_string($record[$key]) ."'";
			else
				$this->reportError("Unknown ID type $value found for $key field in $table table!");
			
			$isFirst = false;
		}
		
		$query = "INSERT INTO $table($columns) VALUES($values);";
		$this->lastQuery = $query;
		return $query;
	}
	
	
	public function saveRecord ($table, &$record, $idField, $fieldDef)
	{
		if ($record['__isNew'])
			$query = $this->createInsertQuery($table, $record, $fieldDef);
		else
			$query = $this->createUpdateQuery($table, $record, $idField, $fieldDef);
		
		if ($query === false) return false;
		
		$result = $this->db->query($query);
		if ($result === false) return $this->reportError("Failed to save record {$record[$idField]} to {$table} table!");
		
		if ($record['__isNew']) $record['id'] = $this->db->insert_id;
		$record['__isNew'] = false;
		$record['__dirty'] = false;
		
		return true;
	}
	
	
	public function LoadBook ($bookTitle)
	{
		$book = $this->loadRecord('book', 'title', $bookTitle, self::$BOOK_FIELDS);
		if ($book === false) return false;
		
		return $book;
	}
	
	
	public function LoadCollectible ($id)
	{
		$record = $this->loadRecord('collectibles', 'id', $id, self::$COLLECTIBLE_FIELDS);
		if ($record === false) return false;
	
		return $record;
	}
	
	
	public function LoadAchievementCategory ($id)
	{
		$record = $this->loadRecord('achievementCategories', 'id', $id, self::$ACHIEVEMENTCATEGORY_FIELDS);
		if ($record === false) return false;
	
		return $record;
	}
	
	
	public function LoadAchievement ($id)
	{
		$record = $this->loadRecord('achievements', 'id', $id, self::$ACHIEVEMENT_FIELDS);
		if ($record === false) return false;
	
		return $record;
	}
	
	
	public function LoadAchievementCriteria ($id)
	{
		$record = $this->loadRecord('achievementCriteria', 'id', $id, self::$ACHIEVEMENTCRITERIA_FIELDS);
		if ($record === false) return false;
	
		return $record;
	}
	
	
	public function LoadMinedItemID ($id)
	{
		$minedItem = $this->loadRecord('minedItem'.self::MINEITEM_TABLESUFFIX, 'id', $id, self::$MINEDITEM_FIELDS);
		if ($minedItem === false) return false;
	
		return $minedItem;
	}
	
	
	public function LoadMinedItemLink ($itemLink)
	{
		$minedItem = $this->loadRecord('minedItem'.self::MINEITEM_TABLESUFFIX, 'link', $itemLink, self::$MINEDITEM_FIELDS);
		if ($minedItem === false) return false;
		
		return $minedItem;
	}
	
	
	public function LoadSkillDump ($abilityId)
	{
		if ($abilityId <= 0) return false;
		
		$skill = $this->loadRecord('minedSkills'.self::SKILLS_TABLESUFFIX, 'id', $abilityId, self::$SKILLDUMP_FIELDS);
		if ($skill === false) return false;
	
		return $skill;
	}
	
	
	public function LoadSkillLine ($name)
	{
		if ($name == "") return false;
		
		$skill = $this->loadRecord('minedSkillLines'.self::SKILLS_TABLESUFFIX, 'name', $name, self::$SKILLLINE_FIELDS);
		if ($skill === false) return false;
	
		return $skill;
	}
	
	
	public function SaveSkillCoef(&$coefData)
	{
		$abilityId = intval($coefData['id']);
		$setQuery = array();
		
		foreach ($coefData as $key => $value)
		{
			if ($key == "id") continue;
			$safeValue = $this->db->real_escape_string($value);
			$setQuery[] = "$key=\"$safeValue\"";
		}
		
		$query = "UPDATE minedSkills".self::SKILLS_TABLESUFFIX." SET " . implode(", ", $setQuery) . " WHERE id=$abilityId";
		$this->lastQuery = $query;
		$result = $this->db->query($query);
		if (!$result) return $this->reportError("Failed to save skill coefficient data!");
	
		return true;
	}
	
	
	public function SaveCPSkill (&$record)
	{
		return $this->saveRecord('cpSkills'.self::SKILLS_TABLESUFFIX, $record, 'id', self::$CPSKILL_FIELDS);
	}
	
	
	public function SaveCPDiscipline (&$record)
	{
		return $this->saveRecord('cpDisciplines'.self::SKILLS_TABLESUFFIX, $record, 'id', self::$CPDISCIPLINE_FIELDS);
	}
	
	
	public function SaveCPSkillDescription (&$record)
	{
		return $this->saveRecord('cpSkillDescriptions'.self::SKILLS_TABLESUFFIX, $record, 'id', self::$CPSKILLDESCRIPTION_FIELDS);
	}
	
	
	public function SaveSkillDump (&$record)
	{
		return $this->saveRecord('minedSkills'.self::SKILLS_TABLESUFFIX, $record, 'id', self::$SKILLDUMP_FIELDS);
	}
	
	
	public function SaveSkillLine (&$record)
	{
		return $this->saveRecord('minedSkillLines'.self::SKILLS_TABLESUFFIX, $record, 'name', self::$SKILLLINE_FIELDS);
	}
	
	
	public function SaveMinedItem (&$record)
	{
		return $this->saveRecord('minedItem'.self::MINEITEM_TABLESUFFIX, $record, 'id', self::$MINEDITEM_FIELDS);
	}
	
	
	public function SaveBook (&$record)
	{
		return $this->saveRecord('book', $record, 'id', self::$BOOK_FIELDS);
	}
	
	
	public function SaveQuest (&$record)
	{
		return $this->saveRecord('quest', $record, 'id', self::$QUEST_FIELDS);
	}
	
	
	public function SaveItem (&$record)
	{
		return $this->saveRecord('item', $record, 'id', self::$ITEM_FIELDS);
	}
	
	
	public function SaveQuestStage (&$record)
	{
		return $this->saveRecord('questStage', $record, 'id', self::$QUESTSTAGE_FIELDS);
	}
	
	
	public function SaveQuestItem (&$record)
	{
		return $this->saveRecord('questItem', $record, 'id', self::$QUESTITEM_FIELDS);
	}
	
	
	public function SaveLocation (&$record)
	{
		return $this->saveRecord('location', $record, 'id', self::$LOCATION_FIELDS);
	}
	
	
	public function SaveChest (&$record)
	{
		return $this->saveRecord('chest', $record, 'id', self::$CHEST_FIELDS);
	}
	
	
	public function SaveNPC (&$record)
	{
		return $this->saveRecord('npc', $record, 'id', self::$NPC_FIELDS);
	}
	
	
	public function SaveRecipe (&$record)
	{
		return $this->saveRecord('recipe', $record, 'id', self::$RECIPE_FIELDS);
	}
	
	
	public function SaveIngredient (&$record)
	{
		return $this->saveRecord('ingredient', $record, 'id', self::$INGREDIENT_FIELDS);
	}
	
	
	public function SaveCollectible (&$record)
	{
		return $this->saveRecord('collectibles', $record, 'id', self::$COLLECTIBLE_FIELDS);
	}
	
	
	public function SaveAchievementCategory (&$record)
	{
		return $this->saveRecord('achievementCategories', $record, 'id', self::$ACHIEVEMENTCATEGORY_FIELDS);
	}
	
	
	public function SaveAchievement (&$record)
	{
		return $this->saveRecord('achievements', $record, 'id', self::$ACHIEVEMENT_FIELDS);
	}
	
	
	public function SaveAchievementCriteria (&$record)
	{
		return $this->saveRecord('achievementCriteria', $record, 'id', self::$ACHIEVEMENTCRITERIA_FIELDS);
	}
	
	
	public function createTables()
	{
		$result = $this->initDatabaseWrite();
		if (!$result) return false;
		
		$query = "CREATE TABLE IF NOT EXISTS logInfo (
						id TINYTEXT NOT NULL,
						value TINYTEXT NOT NULL,
						PRIMARY KEY (id(64))
					);";
		
		$this->lastQuery = $query;
		$result = $this->db->query($query);
		if ($result === FALSE) return $this->reportError("Failed to create logInfo table!");
		
		$query = "CREATE TABLE IF NOT EXISTS logEntry (
						id BIGINT NOT NULL AUTO_INCREMENT,
						gameTime INTEGER NOT NULL,
						timeStamp BIGINT NOT NULL,
						entryHash BIGINT NOT NULL,
						userName TINYTEXT NOT NULL,
						ipAddress TINYTEXT NOT NULL,
						PRIMARY KEY (id),
						INDEX unique_entry (gameTime, timeStamp, entryHash)
					);";
		
		$this->lastQuery = $query;
		$result = $this->db->query($query);
		if ($result === FALSE) return $this->reportError("Failed to create logEntry table!");
		
		$query = "CREATE TABLE IF NOT EXISTS user (
						name TINYTEXT NOT NULL,
						entryCount INTEGER NOT NULL,
						errorCount INTEGER NOT NULL,
						duplicateCount INTEGER NOT NULL,
						newCount INTEGER NOT NULL,
						chestsFound INTEGER NOT NULL,
						sacksFound INTEGER NOT NULL,
						trovesFound INTEGER NOT NULL,
						booksRead INTEGER NOT NULL,
						nodesHarvested INTEGER NOT NULL,
						itemsLooted INTEGER NOT NULL,
						itemsStolen INTEGER NOT NULL,
						mobsKilled INTEGER NOT NULL,
						enabled TINYINT NOT NULL DEFAULT 1,
						language TINYTEXT NOT NULL,
						PRIMARY KEY (name(64))
					);";
		
		$this->lastQuery = $query;
		$result = $this->db->query($query);
		if ($result === FALSE) return $this->reportError("Failed to create user table!");
		
		$query = "CREATE TABLE IF NOT EXISTS ipAddress (
						ipaddress TINYTEXT NOT NULL,
						enabled TINYINT NOT NULL DEFAULT 1,
						PRIMARY KEY (ipaddress(64))
					);";
		
		$this->lastQuery = $query;
		$result = $this->db->query($query);
		if ($result === FALSE) return $this->reportError("Failed to create ipAddress table!");
		
		$query = "CREATE TABLE IF NOT EXISTS book (
						id BIGINT NOT NULL AUTO_INCREMENT,
						logId BIGINT NOT NULL,
						title TINYTEXT NOT NULL,
						body TEXT NOT NULL,
						skill TINYTEXT NOT NULL,
						mediumIndex INTEGER NOT NULL,
						isLore INTEGER NOT NULL,
						icon TEXT NOT NULL,
						categoryIndex INTEGER NOT NULL,
						collectionIndex INTEGER NOT NULL,
						bookIndex INTEGER NOT NULL,
						guildIndex INTEGER NOT NULL,
						PRIMARY KEY (id),
						FULLTEXT(title),
						FULLTEXT(body)
					);";
		
		$this->lastQuery = $query;
		$result = $this->db->query($query);
		if ($result === FALSE) return $this->reportError("Failed to create book table!");
		
		$query = "CREATE TABLE IF NOT EXISTS location (
						id BIGINT NOT NULL AUTO_INCREMENT,
						logId BIGINT NOT NULL,
						npcId BIGINT NOT NULL,
						questId BIGINT NOT NULL,
						questStageId BIGINT NOT NULL,
						itemId BIGINT NOT NULL,
						bookId BIGINT NOT NULL,
						type TINYTEXT NOT NULL,
						name TINYTEXT NOT NULL,
						count INTEGER NOT NULL,
						zone TINYTEXT NOT NULL,
						x INTEGER NOT NULL,
						y INTEGER NOT NULL,
						rawX FLOAT NOT NULL,
						rawY FLOAT NOT NULL,
						PRIMARY KEY (id),
						INDEX index_zone(zone(32)),
						INDEX find_loc (zone(64), x, y),
						INDEX find_loctype (type(32), zone(64), x, y),
						INDEX find_bookloc (bookId, zone(64), x, y),
						INDEX find_npcloc (npcId, zone(64), x, y),
						INDEX find_itemloc (itemId, zone(64), x, y),
						INDEX find_questloc (questId, zone(64), x, y),
						INDEX find_queststageloc (questStageId, zone(64), x, y),
						FULLTEXT(name)
					);";
		
		$this->lastQuery = $query;
		$result = $this->db->query($query);
		if ($result === FALSE) return $this->reportError("Failed to create location table!");
		
		$query = "CREATE TABLE IF NOT EXISTS chest (
						id BIGINT NOT NULL AUTO_INCREMENT,
						locationId BIGINT NOT NULL,
						logId BIGINT NOT NULL,
						quality TINYINT NOT NULL,
						PRIMARY KEY (id)
					);";
		
		$this->lastQuery = $query;
		$result = $this->db->query($query);
		if ($result === FALSE) return $this->reportError("Failed to create chest table!");
		
		$query = "CREATE TABLE IF NOT EXISTS item (
						id BIGINT NOT NULL AUTO_INCREMENT,
						logId BIGINT NOT NULL,
						link TINYTEXT NOT NULL,
						name TINYTEXT NOT NULL,
						icon TINYTEXT NOT NULL,
						color TINYTEXT NOT NULL,
						style TINYINT NOT NULL,
						trait TINYINT NOT NULL,
						quality TINYINT NOT NULL,
						locked TINYINT NOT NULL,
						type TINYINT NOT NULL,
						equipType TINYINT NOT NULL,
						craftType TINYINT NOT NULL,
						value INTEGER NOT NULL,
						level TINYINT NOT NULL,
						PRIMARY KEY (id),
						INDEX index_link (link(64)),
						FULLTEXT(name)
					);";
		
		$this->lastQuery = $query;
		$result = $this->db->query($query);
		if ($result === FALSE) return $this->reportError("Failed to create item table!");
		
		$query = "CREATE TABLE IF NOT EXISTS quest (
						id BIGINT NOT NULL AUTO_INCREMENT,
						logId BIGINT NOT NULL,
						locationId BIGINT NOT NULL,
						name TINYTEXT NOT NULL,
						objective TINYTEXT NOT NULL,
						PRIMARY KEY (id),
						FULLTEXT(name),
						FULLTEXT(objective)
					);";
		
		$this->lastQuery = $query;
		$result = $this->db->query($query);
		if ($result === FALSE) return $this->reportError("Failed to create quest table!");
		
		$query = "CREATE TABLE IF NOT EXISTS questStage (
						id BIGINT NOT NULL AUTO_INCREMENT,
						logId BIGINT NOT NULL,
						questId BIGINT NOT NULL,
						locationId BIGINT NOT NULL,
						objective TINYTEXT NOT NULL,
						overrideText TINYTEXT NOT NULL,
						orderIndex INTEGER NOT NULL,
						type INTEGER NOT NULL,
						counter INTEGER NOT NULL,
						isHidden TINYINT NOT NULL,
						isPushed TINYINT NOT NULL,
						isFail TINYINT NOT NULL,
						isComplete TINYINT NOT NULL,
						PRIMARY KEY (id),
						INDEX index_quest(questId),
						FULLTEXT(objective),
						FULLTEXT(overrideText)
					);";
		
		$this->lastQuery = $query;
		$result = $this->db->query($query);
		if ($result === FALSE) return $this->reportError("Failed to create questStage table!");
		
		$query = "CREATE TABLE IF NOT EXISTS questItem (
						id BIGINT NOT NULL AUTO_INCREMENT,
						logId BIGINT NOT NULL,
						questId BIGINT NOT NULL,
						questName TINYTEXT NOT NULL,
						itemLink TINYTEXT NOT NULL,
						name TINYTEXT NOT NULL,
						header TINYTEXT NOT NULL,
						itemId INTEGER NOT NULL,
						description TEXT NOT NULL,
						icon TINYTEXT NOT NULL,
						stepIndex INTEGER NOT NULL,
						conditionIndex INTEGER NOT NULL,
						PRIMARY KEY (id),
						FULLTEXT(name),
						FULLTEXT(description),
						INDEX index_questId(questId),
						INDEX index_link (itemLink(64))
					);";
		
		$this->lastQuery = $query;
		$result = $this->db->query($query);
		if ($result === FALSE) return $this->reportError("Failed to create questItem table!");
		
		$query = "CREATE TABLE IF NOT EXISTS npc (
						id BIGINT NOT NULL AUTO_INCREMENT,
						logId BIGINT NOT NULL,
						name TINYTEXT NOT NULL,
						level INTEGER NOT NULL,
						gender TINYINT NOT NULL,
						difficulty TINYINT NOT NULL,
						PRIMARY KEY (id),
						FULLTEXT(name)
					);";
		
		$this->lastQuery = $query;
		$result = $this->db->query($query);
		if ($result === FALSE) return $this->reportError("Failed to create npc table!");
		
		$query = "CREATE TABLE IF NOT EXISTS recipe (
						id BIGINT NOT NULL AUTO_INCREMENT,
						logId BIGINT NOT NULL,
						resultItemId BIGINT NOT NULL,
						name TINYTEXT NOT NULL,
						level INTEGER NOT NULL,
						type TINYINT NOT NULL,
						quality TINYINT NOT NULL,
						PRIMARY KEY (id),
						FULLTEXT(name)
					);";
		
		$this->lastQuery = $query;
		$result = $this->db->query($query);
		if ($result === FALSE) return $this->reportError("Failed to create recipe table!");
		
		$query = "CREATE TABLE IF NOT EXISTS ingredient (
						id BIGINT NOT NULL AUTO_INCREMENT,
						logId BIGINT NOT NULL,
						recipeId BIGINT NOT NULL,
						itemId BIGINT NOT NULL,
						name TINYTEXT NOT NULL,
						quantity INTEGER NOT NULL,
						PRIMARY KEY (id),
						FULLTEXT(name)
					);";
		
		$this->lastQuery = $query;
		$result = $this->db->query($query);
		if ($result === FALSE) return $this->reportError("Failed to create ingredient table!");
		
		$query = "CREATE TABLE IF NOT EXISTS minedItem".self::MINEITEM_TABLESUFFIX." (
			id BIGINT NOT NULL AUTO_INCREMENT,
			logId BIGINT NOT NULL,
			link TINYTEXT NOT NULL,
			itemId INTEGER NOT NULL DEFAULT 0,
			internalLevel SMALLINT NOT NULL DEFAULT 0,
			internalSubtype INTEGER NOT NULL DEFAULT 0,
			potionData INTEGER NOT NULL DEFAULT 0,
			name TINYTEXT NOT NULL,
			description TEXT NOT NULL,
			style TINYINT NOT NULL,
			trait TINYINT NOT NULL,
			quality TINYINT NOT NULL,
			value INTEGER NOT NULL DEFAULT -1,
			level TINYINT NOT NULL,
			type TINYINT NOT NULL,
			equipType TINYINT NOT NULL DEFAULT -1,
			weaponType TINYINT NOT NULL DEFAULT -1,
			armorType TINYINT NOT NULL DEFAULT -1,
			craftType TINYINT NOT NULL DEFAULT -1,
			armorRating INTEGER NOT NULL DEFAULT -1,
			weaponPower INTEGER NOT NULL DEFAULT -1,
			cond INTEGER NOT NULL DEFAULT -1,
			enchantId INTEGER NOT NULL DEFAULT -1,
			enchantLevel SMALLINT NOT NULL DEFAULT -1,
			enchantSubtype INTEGER NOT NULL DEFAULT -1,
			enchantName TINYTEXT NOT NULL,
			enchantDesc TEXT NOT NULL,
			maxCharges INTEGER NOT NULL DEFAULT -1,
			abilityName TINYTEXT NOT NULL,
			abilityDesc TEXT NOT NULL,
			abilityCooldown INTEGER NOT NULL DEFAULT -1,
			setName TINYTEXT NOT NULL,
			setBonusCount TINYINT NOT NULL DEFAULT -1,
			setMaxEquipCount TINYINT NOT NULL DEFAULT -1,
			setBonusCount1 TINYINT NOT NULL DEFAULT -1,
			setBonusCount2 TINYINT NOT NULL DEFAULT -1,
			setBonusCount3 TINYINT NOT NULL DEFAULT -1,
			setBonusCount4 TINYINT NOT NULL DEFAULT -1,
			setBonusCount5 TINYINT NOT NULL DEFAULT -1,
			setBonusDesc1 TEXT NOT NULL,
			setBonusDesc2 TEXT NOT NULL,
			setBonusDesc3 TEXT NOT NULL,
			setBonusDesc4 TEXT NOT NULL,
			setBonusDesc5 TEXT NOT NULL,
			glyphMinLevel SMALLINT NOT NULL DEFAULT -1,
			glyphMaxLevel SMALLINT NOT NULL DEFAULT -1,
			runeType TINYINT NOT NULL DEFAULT -1,
			runeRank TINYINT NOT NULL DEFAULT -1,
			bindType TINYINT NOT NULL DEFAULT -1,
			siegeHP INTEGER NOT NULL DEFAULT -1,
			bookTitle TINYTEXT NOT NULL,
			craftSkillRank TINYINT NOT NULL DEFAULT -1,
			recipeRank TINYINT NOT NULL DEFAULT -1,
			recipeQuality TINYINT NOT NULL DEFAULT -1,
			refinedItemLink TINYTEXT NOT NULL,
			resultItemLink TINYTEXT NOT NULL,
			materialLevelDesc TINYTEXT NOT NULL,
			traitDesc TINYTEXT NOT NULL,
			traitAbilityDesc TINYTEXT NOT NULL,
			traitCooldown INTEGER NOT NULL DEFAULT -1,
			isUnique BIT NOT NULL DEFAULT 0,
			isUniqueEquipped BIT NOT NULL DEFAULT 0,
			isVendorTrash BIT NOT NULL DEFAULT 0,
			isArmorDecay BIT NOT NULL DEFAULT 0,
			isConsumable BIT NOT NULL DEFAULT 0,
			icon TINYTEXT NOT NULL,
			comment TINYTEXT NOT NULL,
			tags TINYTEXT NOT NULL,
			dyeData TEXT NOT NULL,
			PRIMARY KEY (id),
			INDEX index_link (link(64)),
			INDEX index_itemId (itemId),
			INDEX index_enchantId (enchantId)
		);";
		
		$this->lastQuery = $query;
		$result = $this->db->query($query);
		if ($result === FALSE) return $this->reportError("Failed to create minedItem table!");
		
		$query = "CREATE TABLE IF NOT EXISTS itemIdCheck(
			id BIGINT NOT NULL AUTO_INCREMENT PRIMARY KEY,
			itemId INTEGER NOT NULL,
			version TINYTEXT NOT NULL,
			INDEX index_itemId (itemId),
			INDEX index_version (version(8))
		);";
		
		$this->lastQuery = $query;
		$result = $this->db->query($query);
		if ($result === FALSE) return $this->reportError("Failed to create itemIdCheck table!");
		
		$query = "CREATE TABLE IF NOT EXISTS minedSkills".self::SKILLS_TABLESUFFIX."(
			id INTEGER NOT NULL PRIMARY KEY,
			name TINYTEXT NOT NULL,
			description TEXT NOT NULL,
			target TINYTEXT NOT NULL,
			skillType INTEGER NOT NULL DEFAULT -1,
			upgradeLines TEXT NOT NULL,
			effectLines TEXT NOT NULL,
			duration INTEGER NOT NULL DEFAULT -1,
			cost INTEGER NOT NULL DEFAULT -1,
			minRange INTEGER NOT NULL DEFAULT -1,
			maxRange INTEGER NOT NULL DEFAULT -1,
			radius INTEGER NOT NULL DEFAULT -1,
			isPassive TINYINT NOT NULL DEFAULT 0,
			isChanneled TINYINT NOT NULL DEFAULT 0,
			castTime INTEGER NOT NULL DEFAULT -1,
			channelTime INTEGER NOT NULL DEFAULT -1,
			angleDistance INTEGER NOT NULL DEFAULT -1,
			mechanic INTEGER NOT NULL DEFAULT -1,
			texture TEXT NOT NULL,
			isPlayer TINYINT NOT NULL DEFAULT 0,
			raceType TINYTEXT NOT NULL,
			classType TINYTEXT NOT NULL,
			skillLine TINYTEXT NOT NULL,
			prevSkill BIGINT NOT NULL DEFAULT 0,
			nextSkill BIGINT NOT NULL DEFAULT 0,
			nextSkill2 BIGINT NOT NULL DEFAULT 0,
			learnedLevel INTEGER NOT NULL DEFAULT -1,
			rank TINYINT NOT NULL DEFAULT 0,		
			skillIndex TINYINT NOT NULL DEFAULT -1,
			numCoefVars TINYINT NOT NULL DEFAULT -1,
			coefDescription TEXT NOT NULL,
			type1 TINYINT NOT NULL DEFAULT -1,
			a1 FLOAT NOT NULL DEFAULT -1,
			b1 FLOAT NOT NULL DEFAULT -1,
			c1 FLOAT NOT NULL DEFAULT -1,
			R1 FLOAT NOT NULL DEFAULT -1,
			avg1 FLOAT NOT NULL DEFAULT -1,
			type2 TINYINT NOT NULL DEFAULT -1,
			a2 FLOAT NOT NULL DEFAULT -1,
			b2 FLOAT NOT NULL DEFAULT -1,
			c2 FLOAT NOT NULL DEFAULT -1,
			R2 FLOAT NOT NULL DEFAULT -1,
			avg2 FLOAT NOT NULL DEFAULT -1,
			type3 TINYINT NOT NULL DEFAULT -1,
			a3 FLOAT NOT NULL DEFAULT -1,
			b3 FLOAT NOT NULL DEFAULT -1,
			c3 FLOAT NOT NULL DEFAULT -1,
			R3 FLOAT NOT NULL DEFAULT -1,
			avg3 FLOAT NOT NULL DEFAULT -1,
			type4 TINYINT NOT NULL DEFAULT -1,
			a4 FLOAT NOT NULL DEFAULT -1,
			b4 FLOAT NOT NULL DEFAULT -1,
			c4 FLOAT NOT NULL DEFAULT -1,
			R4 FLOAT NOT NULL DEFAULT -1,
			avg4 FLOAT NOT NULL DEFAULT -1,
			type5 TINYINT NOT NULL DEFAULT -1,
			a5 FLOAT NOT NULL DEFAULT -1,
			b5 FLOAT NOT NULL DEFAULT -1,
			c5 FLOAT NOT NULL DEFAULT -1,
			R5 FLOAT NOT NULL DEFAULT -1,
			avg5 FLOAT NOT NULL DEFAULT -1,
			type6 TINYINT NOT NULL DEFAULT -1,
			a6 FLOAT NOT NULL DEFAULT -1,
			b6 FLOAT NOT NULL DEFAULT -1,
			c6 FLOAT NOT NULL DEFAULT -1,
			R6 FLOAT NOT NULL DEFAULT -1,
			avg6 FLOAT NOT NULL DEFAULT -1,
			FULLTEXT(name),
			FULLTEXT(description),
			FULLTEXT(upgradeLines),
			FULLTEXT(effectLines)
		);";
		
		$this->lastQuery = $query;
		$result = $this->db->query($query);
		if ($result === FALSE) return $this->reportError("Failed to create minedSkills table!");
		
		$query = "CREATE TABLE IF NOT EXISTS minedSkillLines".self::SKILLS_TABLESUFFIX."(
			id BIGINT NOT NULL AUTO_INCREMENT PRIMARY KEY,
			name TINYTEXT NOT NULL,
			fullName TINYTEXT NOT NULL,
			skillType TINYTEXT NOT NULL,
			raceType TINYTEXT NOT NULL,
			classType TINYTEXT NOT NULL,
			numRanks INTEGER NOT NULL DEFAULT 0,
			xp TEXT NOT NULL,
			totalXp INTEGER NOT NULL DEFAULT 0,
			INDEX index_name (name(16)),
			INDEX index_fullName (fullName(32))
		);";
		
		$this->lastQuery = $query;
		$result = $this->db->query($query);
		if ($result === FALSE) return $this->reportError("Failed to create minedSkillLines table!");
		
		$query = "CREATE TABLE IF NOT EXISTS cpDisciplines".self::SKILLS_TABLESUFFIX."(
			id BIGINT NOT NULL AUTO_INCREMENT PRIMARY KEY,
			disciplineIndex INTEGER NOT NULL,
			name TINYTEXT NOT NULL,
			description TEXT NOT NULL,
			attribute TINYINT NOT NULL
		);";
		
		$this->lastQuery = $query;
		$result = $this->db->query($query);
		if ($result === FALSE) return $this->reportError("Failed to create cpDisciplines table!");
		
		$query = "CREATE TABLE IF NOT EXISTS cpSkills".self::SKILLS_TABLESUFFIX."(
			id BIGINT NOT NULL AUTO_INCREMENT PRIMARY KEY,
			abilityId INTEGER NOT NULL,
			disciplineIndex INTEGER NOT NULL,
			skillIndex INTEGER NOT NULL,
			unlockLevel INTEGER NOT NULL,
			name TINYTEXT NOT NULL,
			minDescription TEXT NOT NULL,
			maxDescription TEXT NOT NULL,
			maxValue FLOAT NOT NULL,
			x FLOAT NOT NULL,
			y FLOAT NOT NULL,
			a FLOAT NOT NULL,
			b FLOAT NOT NULL,
			r2 FLOAT NOT NULL,
			fitDescription TEXT NOT NULL,
			INDEX index_abilityId(abilityId)
		);";
		
		$this->lastQuery = $query;
		$result = $this->db->query($query);
		if ($result === FALSE) return $this->reportError("Failed to create cpSkills table!");
		
		$query = "CREATE TABLE IF NOT EXISTS cpSkillDescriptions".self::SKILLS_TABLESUFFIX."(
			id BIGINT NOT NULL AUTO_INCREMENT PRIMARY KEY,
			abilityId INTEGER NOT NULL,
			points INTEGER NOT NULL,
			description TEXT NOT NULL,
			INDEX index_abilityId(abilityId)
		);";
		
		$this->lastQuery = $query;
		$result = $this->db->query($query);
		if ($result === FALSE) return $this->reportError("Failed to create cpSkillDescriptions table!");
		
		$this->lastQuery = $query;
		$result = $this->db->query($query);
		if ($result === FALSE) return $this->reportError("Failed to create cpSkills table!");
		
		$query = "CREATE TABLE IF NOT EXISTS collectibles(
			id BIGINT NOT NULL PRIMARY KEY,
			name TINYTEXT NOT NULL,
			nickname TINYTEXT NOT NULL,
			description MEDIUMTEXT NOT NULL,
			itemLink TINYTEXT NOT NULL,
			hint MEDIUMTEXT NOT NULL,
			icon TINYTEXT NOT NULL,
			backgroundIcon TINYTEXT NOT NULL,
			lockedIcon TINYTEXT NOT NULL,
			categoryType TINYINT NOT NULL,
			zoneIndex INTEGER NOT NULL,
			categoryIndex TINYINT NOT NULL,
			subCategoryIndex TINYINT NOT NULL,
			collectibleIndex TINYINT NOT NULL,
			achievementIndex INTEGER NOT NULL,
			categoryName TINYTEXT NOT NULL,
			subCategoryName TINYTEXT NOT NULL,
			isUnlocked TINYINT NOT NULL,
			isActive TINYINT NOT NULL,
			isSlottable TINYINT NOT NULL,
			isUsable TINYINT NOT NULL,
			isRenameable TINYINT NOT NULL,
			isPlaceholder TINYINT NOT NULL,
			isHidden TINYINT NOT NULL,
			hasAppearance TINYINT NOT NULL,
			visualPriority TINYINT NOT NULL,
			helpCategoryIndex INTEGER NOT NULL,
			helpIndex INTEGER NOT NULL,
			questName TINYTEXT NOT NULL,
			backgroundText MEDIUMTEXT NOT NULL,
			cooldown INTEGER NOT NULL,
			FULLTEXT(name),
			FULLTEXT(nickname),
			FULLTEXT(description),
			FULLTEXT(hint)
		);";
		
		$this->lastQuery = $query;
		$result = $this->db->query($query);
		if ($result === FALSE) return $this->reportError("Failed to create collectibles table!");
		
		$query = "CREATE TABLE IF NOT EXISTS achievementCategories(
			id BIGINT NOT NULL AUTO_INCREMENT PRIMARY KEY,
			name TINYTEXT NOT NULL,
			categoryName TINYTEXT NOT NULL,
			subcategoryName TINYTEXT NOT NULL,
			categoryIndex INTEGER NOT NULL,
			subCategoryIndex INTEGER NOT NULL,
			numAchievements INTEGER NOT NULL,
			points INTEGER NOT NULL,
			hidesPoints TINYINT NOT NULL,
			icon TINYTEXT NOT NULL,
			pressedIcon TINYTEXT NOT NULL,
			mouseoverIcon TINYTEXT NOT NULL,
			gamepadIcon TINYTEXT NOT NULL,
			INDEX index_categoryIndex(categoryIndex),
			INDEX index_subCategoryIndex(subCategoryIndex),
			FULLTEXT(categoryName),
			FULLTEXT(subcategoryName)
		);";
		
		$this->lastQuery = $query;
		$result = $this->db->query($query);
		if ($result === FALSE) return $this->reportError("Failed to create achievementCategories table!");
		
		$query = "CREATE TABLE IF NOT EXISTS achievements(
			id INTEGER NOT NULL PRIMARY KEY,
			name TINYTEXT NOT NULL,
			description TEXT NOT NULL,
			categoryIndex INTEGER NOT NULL,
			subCategoryIndex INTEGER NOT NULL,
			achievementIndex INTEGER NOT NULL,
			categoryName TINYTEXT NOT NULL,
			points INTEGER NOT NULL,
			icon TINYTEXT NOT NULL,
			numRewards TINYINT NOT NULL,
			itemLink TINYTEXT NOT NULL,
			link TINYTEXT NOT NULL,
			firstId INTEGER NOT NULL,
			prevId INTEGER NOT NULL,
			itemName TINYTEXT NOT NULL,
			itemIcon TINYTEXT NOT NULL,
			itemQuality TINYINT NOT NULL,
			title TINYTEXT NOT NULL,
			collectibleId INTEGER NOT NULL,
			dyeId INTEGER NOT NULL,
			dyeName TINYTEXT NOT NULL,
			dyeRarity TINYINT NOT NULL,
			dyeHue TINYINT NOT NULL,
			dyeColor TINYTEXT NOT NULL,
			INDEX index_categoryName(categoryName(32)),
			FULLTEXT(name),
			FULLTEXT(description),
			FULLTEXT(title)
		);";
		
		$this->lastQuery = $query;
		$result = $this->db->query($query);
		if ($result === FALSE) return $this->reportError("Failed to create achievements table!");
		
		$query = "CREATE TABLE IF NOT EXISTS achievementCriteria(
			id BIGINT NOT NULL AUTO_INCREMENT PRIMARY KEY,
			achievementId INTEGER NOT NULL,
			description MEDIUMTEXT NOT NULL,
			numRequired INTEGER NOT NULL,
			criteriaIndex INTEGER NOT NULL,
			INDEX index_achievmentId(achievementId),
			FULLTEXT(description)
		);";
		
		$this->lastQuery = $query;
		$result = $this->db->query($query);
		if ($result === FALSE) return $this->reportError("Failed to create achievementCriteria table!");
		
		return true;
	}
	
	
	public function &addNewUserRecord ($userName)
	{
		print("Adding new user $userName...\n");
		
		$safeName = $this->db->real_escape_string($userName);
		
		$query = "INSERT INTO user(name) VALUES('{$safeName}');";
		$this->lastQuery = $query;
		$result = $this->db->query($query);
		
		if ($result === FALSE)
		{
			$this->reportError("Failed to add user '{$userName}'!");
			return null;
		}
		
		$this->users[$userName] = array();
		$this->users[$userName]['name'] = $userName;
		$this->users[$userName]['entryCount'] = 0;
		$this->users[$userName]['errorCount'] = 0;
		$this->users[$userName]['duplicateCount'] = 0;
		$this->users[$userName]['newCount'] = 0;
		$this->users[$userName]['chestsFound'] = 0;
		$this->users[$userName]['sacksFound'] = 0;
		$this->users[$userName]['trovesFound'] = 0;
		$this->users[$userName]['booksRead'] = 0;
		$this->users[$userName]['itemsLooted'] = 0;
		$this->users[$userName]['itemsStolen'] = 0;
		$this->users[$userName]['nodesHarvested'] = 0;
		$this->users[$userName]['mobsKilled'] = 0;
		$this->users[$userName]['enabled'] = true;
		$this->users[$userName]['language'] = 'en';
		$this->users[$userName]['__dirty'] = false;
		
			/* Set default language of known users */
		if ($userName == "klarix") $this->users[$userName]['language'] = 'de';
		
		$this->users[$userName]['lastBookRecord'] = null;
		$this->users[$userName]['lastBookLogEntry'] = null;
		$this->users[$userName]['lastMinedItemLogEntry'] = null;
		$this->users[$userName]['lastSkillDumpNote'] = null;
		$this->users[$userName]['lastMinedItemIdCheckNote'] = null;
		$this->users[$userName]['mineItemStartGameTime'] = 1;
		$this->users[$userName]['mineItemStartTimeStamp'] = 1;
		$this->users[$userName]['__lastChestFoundGameTime'] = 0;
		$this->users[$userName]['__lastSackFoundGameTime'] = 0;
		$this->users[$userName]['__lastTroveFoundGameTime'] = 0;
		$this->users[$userName]['__lastBookGameTime'] = 0;
		$this->users[$userName]['lastQuestOffered'] = null;
		
		return $this->users[$userName];
	}
	
	
	public function addNewIPAddressRecord ($ipAddress)
	{
		$safeIP = $this->db->real_escape_string($ipAddress);
	
		$query = "INSERT INTO ipAddress(ipAddress) VALUES('{$safeIP}');";
		$this->lastQuery = $query;
		$result = $this->db->query($query);
	
		if ($result === FALSE)
		{
			$this->reportError("Failed to add IP Address '{$ipAddress}'!");
			return null;
		}
		
		$this->ipAddresses[$ipAddress] = array();
		$this->ipAddresses[$ipAddress]['ipAddress'] = $ipAddress;
		$this->ipAddresses[$ipAddress]['enabled'] = true;
		$this->ipAddresses[$ipAddress]['__dirty'] = false;
	
		return $this->ipAddresses[$ipAddress];
	}
	
	
	public function &getUserRecord ($userName)
	{
		//print ("Getting data for user $userName...\n");
		if (array_key_exists($userName, $this->users)) return $this->users[$userName];
		
		$safeName = $this->db->real_escape_string($userName);
		
		$query = "SELECT * FROM user WHERE name='{$safeName}' LIMIT 1;";
		$this->lastQuery = $query;
		$result = $this->db->query($query);
		
		if ($result === FALSE)
		{
			$this->reportError("Failed to get data for user '{$userName}'!");
			return null;
		}
		
		if ($result->num_rows === 0) return $this->addNewUserRecord($userName);
		
		$result->data_seek(0);
		$row = $result->fetch_assoc();
		settype($row['enabled'], "integer");
		settype($row['entryCount'], "integer");
		settype($row['errorCount'], "integer");
		settype($row['duplicateCount'], "integer");
		settype($row['newCount'], "integer");
		settype($row['chestsFound'], "integer");
		settype($row['trovesFound'], "integer");
		settype($row['sacksFound'], "integer");
		settype($row['booksRead'], "integer");
		settype($row['itemsLooted'], "integer");
		settype($row['itemsStolen'], "integer");
		settype($row['nodesHarvested'], "integer");
		settype($row['mobsKilled'], "integer");
		
		$this->users[$userName] = array();
		$this->users[$userName]['name'] = $userName;
		$this->users[$userName]['entryCount'] = $row['entryCount'];
		$this->users[$userName]['errorCount'] = $row['errorCount'];
		$this->users[$userName]['duplicateCount'] = $row['duplicateCount'];
		$this->users[$userName]['newCount'] = $row['newCount'];
		$this->users[$userName]['chestsFound'] = $row['chestsFound'];
		$this->users[$userName]['trovesFound'] = $row['trovesFound'];
		$this->users[$userName]['sacksFound'] = $row['sacksFound'];
		$this->users[$userName]['booksRead'] = $row['booksRead'];
		$this->users[$userName]['itemsLooted'] = $row['itemsLooted'];
		$this->users[$userName]['itemsStolen'] = $row['itemsStolen'];
		$this->users[$userName]['nodesHarvested'] = $row['nodesHarvested'];
		$this->users[$userName]['mobsKilled'] = $row['mobsKilled'];
		$this->users[$userName]['enabled'] = ($row['enabled'] != 0);
		$this->users[$userName]['language'] = $row['language'];
		$this->users[$userName]['__dirty'] = false;
		
		$this->users[$userName]['lastBookRecord'] = null;
		$this->users[$userName]['lastBookLogEntry'] = null;
		$this->users[$userName]['lastMinedItemLogEntry'] = null;
		$this->users[$userName]['lastSkillDumpNote'] = null;
		$this->users[$userName]['mineItemStartGameTime'] = 1;
		$this->users[$userName]['lastMinedItemIdCheckNote'] = null;
		$this->users[$userName]['mineItemStartTimeStamp'] = 1;
		$this->users[$userName]['lastQuestOffered'] = null;
		
		return $this->users[$userName];
	}
	
	
	public function &getIPAddressRecord ($ipAddress)
	{
		if (array_key_exists($ipAddress, $this->ipAddresses)) return $this->ipAddresses[$ipAddress];
	
		$safeIP = $this->db->real_escape_string($ipAddress);
	
		$query = "SELECT * FROM ipAddress WHERE ipAddress='{$safeIP}' LIMIT 1;";
		$this->lastQuery = $query;
		$result = $this->db->query($query);
	
		if ($result === FALSE)
		{
			$this->reportError("Failed to get data for ipAddress '{$ipAddress}'!");
			return null;
		}
	
		if ($result->num_rows === 0) return $this->addNewIPAddressRecord($ipAddress);
		
		$result->data_seek(0);
		$row = $result->fetch_assoc();
		settype($row['enabled'], "integer");
		
		$this->ipAddresses[$ipAddress] = array();
		$this->ipAddresses[$ipAddress]['ipAddress'] = $ipAddress;
		$this->ipAddresses[$ipAddress]['enabled'] = ($row['enabled'] != 0);
		$this->ipAddresses[$ipAddress]['__dirty'] = false;
			
		return $this->ipAddresses[$ipAddress];
	}
	
	
	public function saveData ()
	{
		$result = true;
		
		$result &= $this->saveUsers();
		$result &= $this->saveIPAddresses();
		$result &= $this->SaveLogInfo();
		
		return $result;
	}
	
	
	public function saveUsers ()
	{
		$result = true;
		print("Saving users...\n");
		
		foreach ($this->users as $key => $value)
		{
			
			if ($value['__dirty'] === true)
			{
				$result &= $this->saveUser($value);
			}
		}
		
		return $result;
	}
	
	
	public function saveIPAddresses ()
	{
		$result = true;
		
		foreach ($this->ipAddresses as $key => $value)
		{
			if ($value['__dirty'] === true)
			{
				$result &= $this->saveIPAddress($value);
			}
		}
		
		return $result;
	}
	
	
	public function saveUser ($user)
	{
		print("\tSaving user {$user['name']}...\n");
		
		$safeName = $this->db->real_escape_string($user['name']);
		
		$query = "UPDATE user SET entryCount={$user['entryCount']}, newCount={$user['newCount']}, errorCount={$user['errorCount']}, duplicateCount={$user['duplicateCount']}";
		$query .= ", itemsLooted={$user['itemsLooted']}";
		$query .= ", itemsStolen={$user['itemsStolen']}";
		$query .= ", chestsFound={$user['chestsFound']}";
		$query .= ", trovesFound={$user['trovesFound']}";
		$query .= ", sacksFound={$user['sacksFound']}";
		$query .= ", booksRead={$user['booksRead']}";
		$query .= ", nodesHarvested={$user['nodesHarvested']}";
		$query .= ", mobsKilled={$user['mobsKilled']}";
		$query .= ", language='{$user['language']}'";
		$query .= " WHERE name='{$safeName}';";
		$result = $this->db->query($query);
		if ($result === FALSE) return $this->reportError("Failed to save user '{$safeName}'!");
		
		return true;
	}
	
	
	public function saveIPAddress ($ipAddress)
	{
		$safeName = $this->db->real_escape_string($ipAddress['ipAddress']);
		
		$enabled = $ipAddress['enabled'] ? 1 : 0;
		
		$query = "UPDATE ipAddress SET enabled={$enabled} WHERE ipAddress='{$safeName}';";
		$result = $this->db->query($query);
		if ($result === FALSE) return $this->reportError("Failed to save IP address '{$safeName}'!");
		
		return true;
	}
	
	
	public function IsTargetResource ($targetName)
	{
		return array_key_exists($targetName, self::$RESOURCE_TARGETS);
	}
	
	
	public function GetResourceType ($targetName)
	{
		if (!array_key_exists($targetName, self::$RESOURCE_TARGETS)) return self::RESOURCE_UNKNOWN;
		return self::$RESOURCE_TARGETS[$targetName];
	}
	
	
	public function IsDuplicateEntry ($gameTime, $timeStamp, $entryHash)
	{
		$query = "SELECT * FROM logEntry WHERE gameTime={$gameTime} AND timeStamp={$timeStamp} AND entryHash={$entryHash};";
		$this->lastQuery = $query;
		$result = $this->db->query($query);
		
		if ($result === false) return $this->reportLogParseError("Failed to check logEntry table!");
		return ($result->num_rows > 0);
	}
	
	
	public function isDuplicateLogEntry ($logEntry)
	{
		return $this->IsDuplicateEntry($logEntry['gameTime'], $logEntry['timeStamp'], $logEntry['__crc']);
	}
	
	
	public function isValidLogEntry ($logEntry)
	{
		static $VALID_FIELDS = array(
				"event", "gameTime", "timeStamp", "userName", "ipAddress"
		);
		
		if ($logEntry === null) return $this->reportLogParseError("NULL log entry received!");
		
		foreach ($VALID_FIELDS as $key => $field)
		{
			if (!array_key_exists($field, $logEntry)) return $this->reportLogParseError("Missing $field in log entry!");
			if ($logEntry[$field] == '') return false; 
				//return $this->reportLogParseError("\tFound empty $field in log entry!");
		}
		
		return true;
	}
	
	
	public function addLogEntryRecord ($gameTime, $timeStamp, $entryHash, $userName, $ipAddress)
	{
		$safeName = $this->db->real_escape_string($userName);
		$safeIp = $this->db->real_escape_string($ipAddress);
	
		$query = "INSERT INTO logEntry(gameTime, timeStamp, entryHash, userName, ipAddress) VALUES($gameTime, $timeStamp, $entryHash, '$safeName', '$safeIp');";
		$this->lastQuery = $query;
		$result = $this->db->query($query);
		if ($result === FALSE) return $this->reportError("Failed to create logEntry record!");
	
		return $this->db->insert_id;
	}
	
	
	public function addLogEntryRecordFromLog ($logEntry)
	{
		return $this->addLogEntryRecord($logEntry['gameTime'], $logEntry['timeStamp'], $logEntry['__crc'], $logEntry['userName'], $logEntry['ipAddress']);
	}
	
	
	private function initDatabase ()
	{
		global $uespEsoLogReadDBHost, $uespEsoLogReadUser, $uespEsoLogReadPW, $uespEsoLogDatabase;
	
		if ($this->dbReadInitialized || $this->dbWriteInitialized) return true;
	
		$this->db = new mysqli($uespEsoLogReadDBHost, $uespEsoLogReadUser, $uespEsoLogReadPW, $uespEsoLogDatabase);
		if ($db->connect_error) return $this->reportError("Could not connect to mysql database!");
	
		$this->dbReadInitialized = true;
		$this->dbWriteInitialized = false;
	
		return true;
	}
	
	
	private function initDatabaseWrite ()
	{
		global $uespEsoLogWriteDBHost, $uespEsoLogWriteUser, $uespEsoLogWritePW, $uespEsoLogDatabase;
	
		if ($this->dbWriteInitialized) return true;
	
		if ($this->dbReadInitialized)
		{
			$this->db->close();
			unset($this->db);
			$this->db = null;
			$this->dbReadInitialized = false;
		}
	
		$this->db = new mysqli($uespEsoLogWriteDBHost, $uespEsoLogWriteUser, $uespEsoLogWritePW, $uespEsoLogDatabase);
		if ($db->connect_error) return $this->reportError("Could not connect to mysql database!");
	
		$this->dbReadInitialized = true;
		$this->dbWriteInitialized = true;
	
		if ($this->skipCreateTables) return true;
		return $this->createTables();
	}
	
	
	public function getCurrentLogFilename()
	{
		return $this->generateLogFilename($this->currentLogIndex);
	}
	
	
	public function generateLogFilename($index)
	{
		$logFilename = self::ELP_INPUT_LOG_PATH . sprintf( 'eso%05d.log', $index);
		return $logFilename;
	}
	
	
	public function parseFormInput()
	{
		return true;
	}
	
	
	public function OnStolen ($logEntry)
	{
		//event{LootGained}  itemLink{|H2DC50E:item:30159:1:16:0:0:0:0:0:0:0:0:0:0:0:0:0:0:0:0:0|hwormwood|h}  lootType{1}  qnt{1}
		//lastTarget{Wormwood}  zone{Wayrest}  x{0.50276911258698}  y{0.073295257985592}  gameTime{65831937}  timeStamp{4743645111026450432}  userName{Reorx}  end{}
	
		++$this->currentUser['itemsStolen'];
		$this->currentUser['__dirty'] = true;
	
		if ($logEntry['lastTarget'] == "Thieves Trove" && $logEntry['timeStamp'] < self::ELP_THIEVESTROVE_LASTFIXTIMESTAMP)
		{
			$diff = $logEntry['gameTime'] - $this->currentUser['__lastTroveFoundGameTime'];
	
			if ($diff >= self::TREASURE_DELTA_TIME || $diff < 0)
			{
				++$this->currentUser['trovesFound'];
				$this->currentUser['__dirty'] = true;
				$this->currentUser['__lastTroveFoundGameTime'] = $logEntry['gameTime'];
			}
		}
		
		return true;
	}
		
	
	public function OnLootGainedEntry ($logEntry)
	{
		//event{LootGained}  itemLink{|H2DC50E:item:30159:1:16:0:0:0:0:0:0:0:0:0:0:0:0:0:0:0:0:0|hwormwood|h}  lootType{1}  qnt{1}
		//lastTarget{Wormwood}  zone{Wayrest}  x{0.50276911258698}  y{0.073295257985592}  gameTime{65831937}  timeStamp{4743645111026450432}  userName{Reorx}  end{}
		
		++$this->currentUser['itemsLooted'];
		$this->currentUser['__dirty'] = true;
		
		if ($logEntry['lastTarget'] == "Thieves Trove" && $logEntry['timeStamp'] < self::ELP_THIEVESTROVE_LASTFIXTIMESTAMP)
		{
			$diff = $logEntry['gameTime'] - $this->currentUser['__lastTroveFoundGameTime'];
				
			if ($diff >= self::TREASURE_DELTA_TIME || $diff < 0)
			{
				++$this->currentUser['trovesFound'];
				$this->currentUser['__dirty'] = true;
				$this->currentUser['__lastTroveFoundGameTime'] = $logEntry['gameTime'];
			}
		}
		
		if ($logEntry['rvcType'] == "stole" || $logEntry['rcvType'] == "stole")
		{
			++$this->currentUser['itemsStolen'];
			$this->currentUser['__dirty'] = true;
		}
		
		if ($this->IsTargetResource($logEntry['lastTarget']))
		{
			//$this->log("\tFound user node harvest...");
			++$this->currentUser['nodesHarvested'];
			$this->currentUser['__dirty'] = true;
		}
		
		$itemRecord = $this->FindItemLink($logEntry['itemLink']);
		
		if ($itemRecord == null)
		{
			$itemRecord = $this->CreateItem($logEntry);
			if ($itemRecord == null) return false;
		}
		
		$itemLocation = $this->FindLocation("item", $logEntry['x'], $logEntry['y'], $logEntry['zone'], array('itemId' => $itemRecord['id']));
		
		if ($itemLocation == null)
		{
			$itemLocation = $this->CreateLocation("item", $itemRecord['name'], $logEntry, array('itemId' => $itemRecord['id']));
			if ($itemLocation == null) return false;
		}
		else
		{
			++$itemLocation['counter'];
		
			$result = $this->SaveLocation($itemLocation);
			if (!$result) return false;
		}
		
		return true;
	}
	
	
	public function ParseItemLink ($itemLink)
	{
		$matches = array();
		
			/* Quick check for quest items */
		if ($itemLink[0] != '|' || $itemLink[1] != 'H')
		{
			$matches['name'] = $itemLink;
			$matches['error'] = true;
			return $matches;
		}
		
			//|H0:item:ID:SUBTYPE:LEVEL:ENCHANTID:ENCHANTSUBTYPE:ENCHANTLEVEL:0:0:0:0:0:0:0:0:0:STYLE:CRAFTED:BOUND:CHARGES:POTIONEFFECT|hNAME|h
			//(?:\:(?P<extradata>[0-9]*))?
		$result = preg_match('/\|H(?P<color>[A-Za-z0-9]*)\:item\:(?P<itemId>[0-9]*)\:(?P<subtype>[0-9]*)\:(?P<level>[0-9]*)\:(?P<enchantId>[0-9]*)\:(?P<enchantSubtype>[0-9]*)\:(?P<enchantLevel>[0-9]*)\:(.*?)\:(?P<style>[0-9]*)\:(?P<crafted>[0-9]*)\:(?P<bound>[0-9]*)\:(?P<stolen>[0-9]*)\:(?P<charges>[0-9]*)\:(?P<potionData>[0-9]*)\|h(?P<name>[^|\^]*)(?P<nameCode>.*?)\|h/', $itemLink, $matches);
		
		if ($result == 0) 
		{
			$this->ReportLogParseError("Error parsing item link '$itemLink'!");
			$matches['name'] = $itemLink;
			$matches['error'] = true;
			return $matches;
		}
		
		$matches['error'] = false;
		return $matches;
	}
	
	
	public function CreateItem ($logEntry)
	{
		$itemRecord = $this->createNewRecord(self::$ITEM_FIELDS);
		
		$itemData = $this->ParseItemLink($logEntry['itemLink']);
		
		if ($itemData['error'] === true)
		{
			$itemRecord['link'] = '';
		}
		else
		{
			$itemRecord['link'] = $logEntry['itemLink'];
		}
		
		$itemRecord['icon'] = $logEntry['icon'];
		$itemRecord['name'] = $itemData['name'];
		$itemRecord['level'] = $itemData['level'];
		$itemRecord['color'] = $itemData['color'];
		$itemRecord['craftType'] = $logEntry['craftType'];
		$itemRecord['type'] = $logEntry['type'];
		$itemRecord['equipType'] = $logEntry['equipType'];
		$itemRecord['trait'] = $logEntry['trait'];
		$itemRecord['style'] = $logEntry['itemStyle'];
		$itemRecord['value'] = $logEntry['value'];
		$itemRecord['quality'] = $logEntry['quality'];
		$itemRecord['locked'] = $logEntry['locked'];
		
		++$this->currentUser['newCount'];
		$this->currentUser['__dirty'] = true;
		
		$result = $this->SaveItem($itemRecord);
		if (!$result) return null;
	
		return $itemRecord;
	}
	
	
	public function CreateNPC ($logEntry)
	{
		$npcRecord = $this->createNewRecord(self::$NPC_FIELDS);
		
		$npcRecord['name'] = $logEntry['name'];;
		$npcRecord['gender'] = $logEntry['gender'];
		$npcRecord['level'] = $logEntry['level'];
		$npcRecord['difficulty'] = $logEntry['difficulty'];
		$npcRecord['__isNew'] = true;
		
		++$this->currentUser['newCount'];
		$this->currentUser['__dirty'] = true;
		
		$result = $this->SaveNPC($npcRecord);
		if (!$result) return null;
		
		return $npcRecord;
	}
	
	
	public function CreateRecipe ($logEntry)
	{
		//event{Recipe}  numIngredients{2}  provLevel{1}  name{shornhelm ale}  specialType{2}  quality{1} 
		$recipeRecord = $this->createNewRecord(self::$RECIPE_FIELDS);
		
		$recipeRecord['name'] = $logEntry['name'];
		$recipeRecord['type'] = $logEntry['specialType'];
		$recipeRecord['level'] = $logEntry['provLevel'];
		$recipeRecord['quality'] = $logEntry['quality'];
		$recipeRecord['resultItemId'] = -1;
		$recipeRecord['__isNew'] = true;
		
		++$this->currentUser['newCount'];
		$this->currentUser['__dirty'] = true;
		
		$result = $this->SaveRecipe($recipeRecord);
		if (!$result) return null;
		
		return $recipeRecord;
	}
	
	
	public function CreateIngredient ($recipeId, $itemId, $logEntry)
	{
		//event{Recipe::Ingredient}  icon{/esoui/art/icons/crafting_cloth_pollen.dds}  qnt{1}  name{shornhelm grains^p}  value{0}  quality{1}  itemLink{|HFFFFFF:item:33767:0:0:0:0:0:0:0:0:0:0:0:0:0:0:0:0:0:0:0|hshornhelm grains^p|h}
		
		$ingredientRecord = $this->createNewRecord(self::$INGREDIENT_FIELDS);
		
		$name = preg_replace("|\^.*|", '', $logEntry['name']);
		
		$ingredientRecord['name'] = $name;
		$ingredientRecord['quantity'] = $logEntry['qnt'];
		$ingredientRecord['itemId'] = $itemId;
		$ingredientRecord['recipeId'] = $recipeId;
		$ingredientRecord['__isNew'] = true;
		
		++$this->currentUser['newCount'];
		$this->currentUser['__dirty'] = true;
		
		$result = $this->SaveIngredient($ingredientRecord);
		if (!$result) return null;
		
		return $ingredientRecord;
	}
	
	
	public function CreateQuest ($name, $objective, $logEntry)
	{
		$questRecord = $this->createNewRecord(self::$QUEST_FIELDS);
		
		$questRecord['name'] = $name;
		$questRecord['objective'] = $objective;
		$questRecord['locationId'] = -1;
		$questRecord['__isNew'] = true;
		
		++$this->currentUser['newCount'];
		$this->currentUser['__dirty'] = true;
		
		$result = $this->SaveQuest($questRecord);
		if (!$result) return null;
		
		$questLocation = $this->CreateLocation("quest", $name, $logEntry, array('questId' => $questRecord['id']));
		$result = $this->SaveLocation($questLocation);
		if (!$result) return null;
		
		$questRecord['locationId'] = $questLocation['id'];
		$result = $this->SaveQuest($questRecord);
		if (!$result) return null;
		
		return $questRecord;
	}
	
	
	public function CreateQuestStage ($questRecord, $logEntry)
	{
		$questStageRecord = $this->createNewRecord(self::$QUESTSTAGE_FIELDS);
		
		$questStageRecord['questId'] = $questRecord['id'];
		$questStageRecord['type'] = $logEntry['condType'];
		$questStageRecord['counter'] = $logEntry['condMaxVal'];
		$questStageRecord['orderIndex'] = -1;
		$questStageRecord['objective'] = $logEntry['condition'];
		$questStageRecord['overrideText'] = $logEntry['overrideText'];
		$questStageRecord['isHidden'] = ($logEntry['isHidden'] === 'true') ? 1 : 0;
		$questStageRecord['isFail'] = ($logEntry['isFail'] === 'true') ? 1 : 0;
		$questStageRecord['isPushed'] = ($logEntry['isPushed'] === 'true') ? 1 : 0;
		$questStageRecord['isComplete'] = ($logEntry['isComplete'] === 'true') ? 1 : 0;
		$questStageRecord['locationId'] = -1;
		$questStageRecord['__isNew'] = true;
	
		++$this->currentUser['newCount'];
		$this->currentUser['__dirty'] = true;
	
		$result = $this->SaveQuestStage($questStageRecord);
		if (!$result) return null;
	
		$questLocation = $this->CreateLocation("quest", $questRecord['name'], $logEntry, array('questId' => $questRecord['id'], 'questStageId' => $questStageRecord['id']));
		$result = $this->SaveLocation($questLocation);
		if (!$result) return null;
	
		$questStageRecord['locationId'] = $questLocation['id'];
		$result = $this->SaveQuestStage($questStageRecord);
		if (!$result) return null;
	
		return $questStageRecord;
	}
	
	
	public function CreateQuestItem ($logEntry)
	{
		$questItemRecord = $this->createNewRecord(self::$QUESTITEM_FIELDS);
	
		$questItemRecord['questId'] = -1;
		$questItemRecord['itemLink'] = $logEntry['itemLink'];
		$questItemRecord['name'] = $logEntry['name'];
		$questItemRecord['questName'] = $logEntry['questName'];
		$questItemRecord['itemId'] = $logEntry['questId'];
		$questItemRecord['description'] = $logEntry['desc'];
		$questItemRecord['header'] = $logEntry['header'];
		$questItemRecord['icon'] = $logEntry['texture'];
		$questItemRecord['stepIndex'] = -1;
		$questItemRecord['conditionIndex'] = -1;
		
		if ($logEntry['stepIndex']      != null) $questItemRecord['stepIndex'] = $logEntry['stepIndex'];
		if ($logEntry['conditionIndex'] != null) $questItemRecord['conditionIndex'] = $logEntry['conditionIndex'];
		if ($logEntry['toolIndex']      != null) $questItemRecord['stepIndex'] = $logEntry['toolIndex'];
		
		$questItemRecord['__isNew'] = true;
		
		$questRecord = $this->FindQuest($logEntry['questName']);
		if ($questRecord != null) $questItemRecord['questId'] = $questRecord['id'];
	
		++$this->currentUser['newCount'];
		$this->currentUser['__dirty'] = true;
	
		$result = $this->SaveQuestItem($questItemRecord);
		if (!$result) return null;
		
		return $questItemRecord;
	}
	
	
	public function CreateQuestOfferStage ($questRecord, $logEntry)
	{
		$questStageRecord = $this->createNewRecord(self::$QUESTSTAGE_FIELDS);
	
		$questStageRecord['questId'] = $questRecord['id'];
		$questStageRecord['type'] = -123;
		$questStageRecord['counter'] = -1;
		$questStageRecord['orderIndex'] = -123;
		$questStageRecord['objective'] = $logEntry['dialog'];
		$questStageRecord['overrideText'] = "";
		$questStageRecord['isHidden'] = 0;
		$questStageRecord['isFail'] = 0;
		$questStageRecord['isPushed'] = 0;
		$questStageRecord['isComplete'] = 0;
		$questStageRecord['locationId'] = -1;
		$questStageRecord['__isNew'] = true;
	
		++$this->currentUser['newCount'];
		$this->currentUser['__dirty'] = true;
	
		$result = $this->SaveQuestStage($questStageRecord);
		if (!$result) return null;
	
		$questLocation = $this->CreateLocation("quest", $questRecord['name'], $logEntry, array('questId' => $questRecord['id'], 'questStageId' => $questStageRecord['id']));
		$result = $this->SaveLocation($questLocation);
		if (!$result) return null;
	
		$questStageRecord['locationId'] = $questLocation['id'];
		$result = $this->SaveQuestStage($questStageRecord);
		if (!$result) return null;
	
		return $questStageRecord;
	}
	
	
	public function OnQuestAdded ($logEntry)
	{
		//event{QuestAdded}  quest{A Brush With Death}  objective{}  
		//y{0.64464473724365}  zone{Mines of Khuras}  x{0.25603923201561}
		//gameTime{2450267}  timeStamp{4743643875908780032}  userName{...}  ipAddress{...}  logTime{1396487058}  end{}
		
		$questRecord = $this->FindQuest($logEntry['quest']);
		
		if ($questRecord == null)
		{
			$questRecord = $this->CreateQuest($logEntry['quest'], $logEntry['objective'], $logEntry);
			if ($questRecord == null) return false;
		}
		
		$questId = $questRecord['id'];
		$questOfferData = $this->currentUser['lastQuestOffered'];
		$this->currentUser['lastQuestOffered'] = null;
		
		if ($questOfferData != null)
		{
			$deltaQuestOfferTime = $logEntry['gameTime'] - $questOfferData['gameTime'];
			
			if ($deltaQuestOfferTime < self::QUESTOFFERED_DELTA_TIME && $deltaQuestOfferTime >= 0)
			{
				//print("\tSaving QuestOffer Data (" . $questRecord['name'] . ")\n");
				
				$questStageRecord = $this->FindQuestStageByType($questId, -123);
				if ($questStageRecord != null) return true;
				
				$questStageRecord = $this->CreateQuestOfferStage($questRecord, $questOfferData);
				if ($questStageRecord == null) return false;
			}
		}
		
		return true;
	}
	
	
	public function OnQuestItem ($logEntry)
	{
		//event{QuestItem}  stepIndex{1}  itemLink{|H0:quest_item:5625|hDurzog Feed|h}  conditionIndex{1}  
		//header{Quest Item}  questId{5625}  name{Durzog Feed}  questName{Getting a Bellyful}  journalIndex{7}  
		//desc{This meat is surprisingly fresh and carries a robust, heady odor.}  texture{/esoui/art/icons/quest_food_003.dds}  
		//gameTime{845859763}  timeStamp{4743895467916525568}  lang{en}  userName{...}  ipAddress{...}  logTime{1396487061}  end{}
		
		$questItemRecord = $this->FindQuestItem($logEntry['itemLink']);
		
		if ($questItemRecord == null)
		{
			$questItemRecord = $this->CreateQuestItem($logEntry);
			if ($questItemRecord == null) return false;
			
			return true;
		}
		
		$questItemRecord['name'] = $logEntry['name'];
		$questItemRecord['questName'] = $logEntry['questName'];
		$questItemRecord['itemId'] = $logEntry['questId'];
		$questItemRecord['description'] = $logEntry['desc'];
		$questItemRecord['header'] = $logEntry['header'];
		$questItemRecord['icon'] = $logEntry['texture'];
		
		if ($logEntry['stepIndex']      != null) $questItemRecord['stepIndex'] = $logEntry['stepIndex'];
		if ($logEntry['conditionIndex'] != null) $questItemRecord['conditionIndex'] = $logEntry['conditionIndex'];
		if ($logEntry['toolIndex']      != null) $questItemRecord['stepIndex'] = $logEntry['toolIndex'];
		
		$questRecord = $this->FindQuest($logEntry['questName']);
		if ($questRecord != null) $questItemRecord['questId'] = $questRecord['id'];
		
		$this->currentUser['__dirty'] = true;
		
		$result = $this->SaveQuestItem($questItemRecord);
		if (!$result) return false;
		
		return true;
	}
	
	
	public function OnQuestChanged ($logEntry)
	{
		//event{QuestChanged}  overrideText{Talk to Grahla}  quest{The Nameless Soldier}  isHidden{false}  isFail{false}  isPushed{false}
		//isCondComplete{true}  isComplete{true} condition{Find Alana}  condType{9}  condVal{1}  condMaxVal{1}
		//y{0.48894619941711}  zone{Glenumbra}  x{0.51565104722977}  
		//timeStamp{4743643893159952384}  gameTime{456809}  userName{...}  ipAddress{...}  logTime{1396487061}  end{}
		
		$questRecord = $this->FindQuest($logEntry['quest']);
		
		if ($questRecord == null)
		{
			$questRecord = $this->CreateQuest($logEntry['quest'], $logEntry['objective'], $logEntry);
			if ($questRecord == null) return false;
		}
		
		$questId = $questRecord['id'];
		
		$questStageRecord = $this->FindQuestStage($questId, $logEntry['condition']);
		if ($questStageRecord != null) return true;
		
		$questStageRecord = $this->CreateQuestStage($questRecord, $logEntry);
		if ($questStageRecord == null) return false;
		
		return true;
	}
	
	
	public function OnQuestAdvanced ($logEntry)
	{
		//event{QuestAdvanced}  isPushed{false}  quest{The Nameless Soldier}  isComplete{true}  mainStepChanged{true}  
		//y{0.48894619941711}  zone{Glenumbra}  x{0.51565104722977}  timeStamp{4743643893159952384}  gameTime{456839} 
		//userName{...}  ipAddress{...}  logTime{1396487061}  end{}
		
		return true;
	}
	
	
	public function OnQuestOffered ($logEntry)
	{
		//event{QuestOffered}  optionGold{}  response{<Accept Mission>}  optionIndex{}  optionImp{}  dialog{...}  
		//farewell{Goodbye.}  optionText{}  optionType{}  npcLevel{0}  npcName{Bounty Mission Board}  
		//x{0.55621987581253}  zone{Southern High Rock Gate}  y{0.53252720832825}  timeStamp{4743797388274040832}  
		//gameTime{3757083}  lang{en}  userName{...}  ipAddress{...}  logTime{1433063158}  end{}
		
		$this->currentUser['lastQuestOffered'] = $logEntry;
		
		return true;
	}
	
	public function OnQuestRemoved ($logEntry)
	{
		//event{QuestRemoved}  completed{true}  poiIndex{12}  quest{The White Mask of Merien}  zoneIndex{2}
		//y{0.31489595770836}  zone{Glenumbra}  x{0.43005546927452}  timeStamp{4743643932582215680}
		//gameTime{7603682}  userName{...}  ipAddress{...}  logTime{1396487065}  end{}
		
		return true;
	}
	
	
	public function OnInvDumpStart ($logEntry)
	{
		//event{InvDumpStart}  timeStamp{4743646532660625408}  gameTime{75447364}  userName{Reorx}  end{}
		return true;
	}
	
	
	public function OnInvDumpEnd($logEntry)
	{
		//event{InvDumpEnd}  userName{Reorx}  end{}
		return true;
	}
	
	
	public function OnInvDump ($logEntry)
	{
		//event{InvDump}  icon{/esoui/art/icons/gear_breton_neck_a.dds}  itemLink{|H3A92FF:item:29072:50:14:0:0:0:0:0:0:0:0:0:0:0:0:3:0:1:0:0|hSilky Threads|h} 
		// itemStyle{3}  locked{false}  trait{22}  qnt{1}  craftType{0}  slot{1}  bag{0}  value{22}  equipType{2}  type{2}  quality{3}  userName{Reorx}  end{}
		
		return $this->OnSlotUpdateEntry($logEntry);
	}
	
	
	public function startsWith($haystack, $needle)
	{
		return $needle === "" || strpos($haystack, $needle) === 0;
	}
	
	
	public function OnSlotUpdateEntry ($logEntry)
	{
		//event{SlotUpdate}  icon{/esoui/art/icons/crafting_flower_wormwood_r1.dds}  slot{50}  bag{1}  qnt{13}  craftType{31}  quality{2}
		//locked{false}  trait{0}  equipType{0}  itemStyle{0}  itemLink{|H2DC50E:item:30159:1:1:0:0:0:0:0:0:0:0:0:0:0:0:0:0:0:0:0|hwormwood|h}
		//type{31}  value{2}  userName{Reorx}  end{}
		
		return true;
		
		$itemLink = $logEntry['itemLink'];
		if (strpos($itemLink, "|H") !== 0) return $this->ReportLogParseError("Skipping SlotUpdate with no full item link!");
		
		$itemRecord = $this->FindItemLink($itemLink);
		
		if ($itemRecord == null)
		{
			$itemRecord = $this->CreateItem($logEntry);
			if ($itemRecord == null) return false;
		}
		
		return true;
	}
	
	
	public function FindItemNameWithNoLink ($itemName)
	{
		$safeName = $this->db->real_escape_string($itemName);
		$query = "SELECT * FROM item WHERE name='$safeName' AND link='';";
		$this->lastQuery = $query;
		
		$result = $this->db->query($query);
		
		if ($result === false)
		{
			$this->reportError("Failed to retrieve item!");
			return null;
		}
		
		if ($result->num_rows == 0) return null;
		
		$row = $result->fetch_assoc();
		return $this->createRecordFromRow($row, self::$ITEM_FIELDS);
	}
	
	
	public function FindItemLink ($itemLink)
	{
		if (!$this->startsWith($itemLink, "|H")) return $this->FindItemNameWithNoLink($itemName);
		
		$safeLink = $this->db->real_escape_string($itemLink);
		$query = "SELECT * FROM item WHERE link='$safeLink';";
		$this->lastQuery = $query;
		
		$result = $this->db->query($query);
		
		if ($result === false)
		{
			$this->reportError("Failed to retrieve item!");
			return null;
		}
		
		if ($result->num_rows == 0) return null;
		
		$row = $result->fetch_assoc();
		return $this->createRecordFromRow($row, self::$ITEM_FIELDS);
	}
	
	
	public function FindItemID ($id)
	{
		$safeID = $this->db->real_escape_string($id);
		$query = "SELECT * FROM item WHERE id=$safeID;";
		$this->lastQuery = $query;
		
		$result = $this->db->query($query);
		
		if ($result === false)
		{
			$this->reportError("Failed to retrieve item!");
			return null;
		}
		
		if ($result->num_rows == 0) return null;
		
		$row = $result->fetch_assoc();
		return $this->createRecordFromRow($row, self::$ITEM_FIELDS);
	}
	
	
	public function FindQuest ($name)
	{
		$safeName = $this->db->real_escape_string($name);
		$query = "SELECT * FROM quest WHERE name='$safeName';";
		$this->lastQuery = $query;
		
		$result = $this->db->query($query);
		
		if ($result === false)
		{
			$this->reportError("Failed to retrieve quest!");
			return null;
		}
		
		if ($result->num_rows == 0) return null;
		
		$row = $result->fetch_assoc();
		return $this->createRecordFromRow($row, self::$QUEST_FIELDS);
	}
	
	
	public function FindQuestStage ($questId, $objective)
	{
		$safeObj = $this->db->real_escape_string($objective);
		$safeId = (int) $questId;
		$query = "SELECT * FROM questStage WHERE questId=$safeId AND objective='$safeObj';";
		$this->lastQuery = $query;
		
		$result = $this->db->query($query);
		
		if ($result === false)
		{
			$this->reportError("Failed to retrieve quest stage!");
			return null;
		}
		
		if ($result->num_rows == 0) return null;
		
		$row = $result->fetch_assoc();
		return $this->createRecordFromRow($row, self::$QUESTSTAGE_FIELDS);
	}
	
	
	public function FindQuestItem ($itemLink)
	{
		$safeLink = $this->db->real_escape_string($itemLink);
		$query = "SELECT * FROM questItem WHERE itemLink=\"$safeLink\";";
		$this->lastQuery = $query;
		$result = $this->db->query($query);
	
		if ($result === false)
		{
			$this->reportError("Failed to retrieve quest item!");
			return null;
		}
	
		if ($result->num_rows == 0) return null;
	
		$row = $result->fetch_assoc();
		return $this->createRecordFromRow($row, self::$QUESTITEM_FIELDS);
	}
	
	
	public function FindQuestStageByType ($questId, $type)
	{
		$safeType = $this->db->real_escape_string($type);
		$safeId = (int) $questId;
		$query = "SELECT * FROM questStage WHERE questId=$safeId AND type=$safeType LIMIT 1;";
		$this->lastQuery = $query;
	
		$result = $this->db->query($query);
	
		if ($result === false)
		{
			$this->reportError("Failed to retrieve quest stage by type!");
			return null;
		}
	
		if ($result->num_rows == 0) return null;
	
		$row = $result->fetch_assoc();
		return $this->createRecordFromRow($row, self::$QUESTSTAGE_FIELDS);
	}
	
	
	public function FindNPC ($name)
	{
		$safeName = $this->db->real_escape_string($name);
		$query = "SELECT * FROM npc WHERE name='$safeName';";
		$this->lastQuery = $query;
		
		$result = $this->db->query($query);
		
		if ($result === false)
		{
			$this->reportError("Failed to retrieve NPC!");
			return null;
		}
		
		if ($result->num_rows == 0) return null;
		
		$row = $result->fetch_assoc();
		return $this->createRecordFromRow($row, self::$NPC_FIELDS);
	}
	
	
	public function FindRecipe ($name)
	{
		$safeName = $this->db->real_escape_string($name);
		$query = "SELECT * FROM recipe WHERE name='$safeName';";
		$this->lastQuery = $query;
	
		$result = $this->db->query($query);
	
		if ($result === false)
		{
			$this->reportError("Failed to retrieve recipe!");
			return null;
		}
	
		if ($result->num_rows == 0) return null;
	
		$row = $result->fetch_assoc();
		return $this->createRecordFromRow($row, self::$RECIPE_FIELDS);
	}
	
	
	public function FindIngredient ($recipeId, $itemId, $name)
	{
		$safeId1 = $this->db->real_escape_string($recipeId);
		$safeId2 = $this->db->real_escape_string($itemId);
		$safeName = $this->db->real_escape_string(preg_replace("|\^.*|", '', $name));
		$query = "SELECT * FROM ingredient WHERE recipeId=$safeId1 AND itemId=$safeId2 AND name='$safeName';";
		$this->lastQuery = $query;
	
		$result = $this->db->query($query);
	
		if ($result === false)
		{
			$this->reportError("Failed to retrieve ingredient!");
			return null;
		}
	
		if ($result->num_rows == 0) return null;
	
		$row = $result->fetch_assoc();
		return $this->createRecordFromRow($row, self::$INGREDIENT_FIELDS);
	}
	
	
	public function ConvertPos ($rawPos)
	{
		if ($rawPos == null || $rawPos == '') return 0;
		return (int) ($rawPos * self::ELP_POSITION_FACTOR);
	}
	
	
	public function FindLocation ($type, $rawX, $rawY, $zone, $extraIds = null)
	{
		if ($rawX == '' || $rawY == '' || $zone == '') return null;
		
		$safeZone = $this->db->real_escape_string($zone);
		$safeType = $this->db->real_escape_string($type);
		$x = $this->ConvertPos($rawX);
		$y = $this->ConvertPos($rawY);
		$extraWhere = "";
		
		if ($extraIds != null)
		{
			$extras = array();
			if (array_key_exists('bookId',  $extraIds)) $extras[] = "bookId=" . $extraIds['bookId'];
			if (array_key_exists('npcId',   $extraIds))  $extras[] = "npcId=" . $extraIds['npcId'];
			if (array_key_exists('questId', $extraIds))  $extras[] = "questId=" . $extraIds['questId'];
			if (array_key_exists('questStageId', $extraIds))  $extras[] = "questStageId=" . $extraIds['questStageId'];
			if (array_key_exists('itemId',  $extraIds))  $extras[] = "itemId=" . $extraIds['itemId'];
			
			$extraWhere = implode(" AND ", $extras);
			if ($extraWhere != "") $extraWhere .= " AND ";
		}
		
		$query = "SELECT * FROM location WHERE $extraWhere type='$safeType' AND zone='$safeZone' AND x=$x AND y=$y;";
		$this->lastQuery = $query;
		
		$result = $this->db->query($query);
		
		if ($result === false)
		{
			$this->reportError("Failed to retrieve location!");
			return null;
		}
		
		//$this->reportError("query({$result->num_rows}): $query");
		if ($result->num_rows == 0) return null;
		
		$row = $result->fetch_assoc();
		$this->currentUser['lastLocationRecordId'] = $row['id'];
		
		return $this->createRecordFromRow($row, self::$LOCATION_FIELDS);
	}
	
	
	public function CheckLocation ($type, $name, $logEntry, $extraIds = null)
	{
		if ($this->IncrementLocationCounter($type, $name, $logEntry, $extraIds)) return true;
		return $this->CreateLocation($type, $name, $logEntry, $extraIds) != null;
	}
	
	
	public function IncrementLocationCounter ($type, $name, $logEntry, $extraIds = null)
	{
		$locationRecord = $this->FindLocation($type, $logEntry['x'], $logEntry['y'], $logEntry['zone'], $extraIds);
		if ($locationRecord == null) return false;
		
		++$locationRecord['count'];
		$this->saveLocation($locationRecord);
		
		return true;
	}
	
	
	public function CreateLocation ($type, $name, $logEntry, $extraIds = null)
	{
		if ($logEntry['x'] == '' || $logEntry['y'] == '' || $logEntry['zone'] == '')
		{
			if (!$this->suppressMissingLocationMsg) $this->ReportLogParseError("Skipping location with missing x/y/zone fields!");
			return null;
		}
		
		$locationRecord = $this->createNewRecord(self::$LOCATION_FIELDS);
		
		$x = $this->ConvertPos($logEntry['x']);
		$y = $this->ConvertPos($logEntry['y']);
		
		$locationRecord['x'] = $x;
		$locationRecord['y'] = $y;
		$locationRecord['rawX'] = $logEntry['x'];
		$locationRecord['rawY'] = $logEntry['y'];
		$locationRecord['zone'] = $logEntry['zone'];
		$locationRecord['count'] = 1;
		$locationRecord['type'] = $type;
		$locationRecord['name'] = $name;
		$locationRecord['__isNew'] = true;
		
		if ($extraIds != null)
		{
			if (array_key_exists('bookId',  $extraIds)) $locationRecord['bookId']  = $extraIds['bookId'];
			if (array_key_exists('npcId',   $extraIds)) $locationRecord['npcId']   = $extraIds['npcId'];
			if (array_key_exists('questId', $extraIds)) $locationRecord['questId'] = $extraIds['questId'];
			if (array_key_exists('questStageId', $extraIds)) $locationRecord['questStageId'] = $extraIds['questStageId'];
			if (array_key_exists('itemId',  $extraIds)) $locationRecord['itemId']  = $$extraIds['itemId'];
		}
		
		++$this->currentUser['newCount'];
		$this->currentUser['__dirty'] = true;
		
		$result = $this->saveLocation($locationRecord);
		$this->currentUser['lastLocationRecordId'] = $locationRecord['id'];
		
		return $locationRecord;
	}
	
	
	public function FindBookLocation ($x, $y, $zone, $bookId)
	{
		$safeZone = $this->db->real_escape_string($zone);
		$query = "SELECT * FROM location WHERE bookId=$bookId AND zone='$safeZone' AND x=$x AND y=$y;";
		$this->lastQuery = $query;
		
		$result = $this->db->query($query);
		if ($result === false) return $this->reportError("Failed to retrieve book locations!");
		
		return ($result->num_rows > 0);
	}
	
	
	public function CheckBookLocation ($logEntry, $bookRecord)
	{
		$extraIds = array('bookId' => $bookRecord['id']);
		return $this->CheckLocation("book", $bookRecord['title'], $logEntry, $extraIds);
	}
	
	
	public function OnShowBook ($logEntry)
	{
		//event{ShowBook}  medium{3}  body{...} bookTitle{Jornibret's Last Dance}  y{0.60519206523895}  x{0.689866065979}  zone{Daggerfall}
		//gameTime{3748234}  timeStamp{4743642811914518528}  userName{...}  ipAddress{...}  logTime{1396192529}  end{}
		
		$diff = $logEntry['gameTime'] - $this->currentUser['__lastBookGameTime'];
		
		if ($diff >= self::BOOK_DELTA_TIME || $diff < 0)
		{
			++$this->currentUser['booksRead'];
			$this->currentUser['__dirty'] = true;
			$this->currentUser['__lastBookGameTime'] = $logEntry['gameTime'];
		}
		
		$bookTitle = $logEntry['bookTitle'];
		//print("\tShowBook: $bookTitle\n");
		
		$body = $logEntry['body'];
		$medium = (int) $logEntry['medium'];
		
		if ($bookTitle == null) return $this->reportLogParseError("Missing book title!");
		
		$bookRecord = $this->LoadBook($bookTitle);
		if ($bookRecord === false) return false;
		
		if ($bookRecord['__isNew'] === true)
		{
			$bookRecord['title'] = $bookTitle;
			$bookRecord['body'] = $body;
			$bookRecord['mediumIndex'] = $medium;
			$bookRecord['isLore'] = 0;
			$bookRecord['__dirty'] = true;
			
			++$this->currentUser['newCount'];
			$this->currentUser['__dirty'] = true;
		}
		elseif ($bookRecord['mediumIndex'] < 0 || $bookRecord['body'] == '')
		{
			$bookRecord['body'] = $body;
			$bookRecord['mediumIndex'] = $medium;
			$bookRecord['__dirty'] = true;
			
			++$this->currentUser['newCount'];
			$this->currentUser['__dirty'] = true;
		}
		
		if ($bookRecord['__dirty']) $result &= $this->SaveBook($bookRecord);
		$result = $this->CheckBookLocation($logEntry, $bookRecord);
		
		$this->currentUser['lastBookRecord'] = $bookRecord;
		$this->currentUser['lastBookLogEntry'] = $logEntry;
		return $result;
	}
	
	
	public function OnLoreBook ($logEntry)
	{
		//event{LoreBook}  icon{/esoui/art/icons/icon_missing.dds}  guild{0}  collection{1}  known{true}  index{18}  category{2}
		//bookTitle{A Clothier's Primer}  y{0.52298730611801}  x{0.5053853392601}  zone{Port Hunding}
		//gameTime{11874643}  timeStamp{4743642846001627136}  userName{...}  ipAddress{...}  logTime{1396193303}  end{}
		
		$bookTitle = $logEntry['bookTitle'];
		//print("\tLoreBook: $bookTitle\n");
		
		if ($bookTitle == null) return $this->reportLogParseError("Missing book title!");
	
		$bookRecord = $this->LoadBook($bookTitle);
		if ($bookRecord === false) return false;
		
		if ($bookRecord['__isNew'] === true)
		{
			$bookRecord['title'] = $bookTitle;
			$bookRecord['icon'] = $logEntry['icon'];
			$bookRecord['collectionIndex'] = $logEntry['collection'];
			$bookRecord['bookIndex'] = $logEntry['index'];
			$bookRecord['categoryIndex'] = $logEntry['category'];
			$bookRecord['guildIndex'] = $logEntry['guild'];
			$bookRecord['isLore'] = 1;
			$bookRecord['__dirty'] = true;
				
			++$this->currentUser['newCount'];
			$this->currentUser['__dirty'] = true;
		}
		else if ($bookRecord['guildIndex'] < 0)
		{
			$bookRecord['icon'] = $logEntry['icon'];
			$bookRecord['collectionIndex'] = $logEntry['collection'];
			$bookRecord['bookIndex'] = $logEntry['index'];
			$bookRecord['categoryIndex'] = $logEntry['category'];
			$bookRecord['guildIndex'] = $logEntry['guild'];
			$bookRecord['isLore'] = 1;
			$bookRecord['__dirty'] = true;
				
			++$this->currentUser['newCount'];
			$this->currentUser['__dirty'] = true;
		}
		
		if ($bookRecord['__dirty']) $result &= $this->SaveBook($bookRecord);
		$result = $this->CheckBookLocation($logEntry, $bookRecord);
		
		$this->currentUser['lastBookRecord'] = $bookRecord;
		$this->currentUser['lastBookLogEntry'] = $logEntry;
		return $result;
	}
	
	
	public function OnSkillRankUpdate ($logEntry)
	{
		//event{SkillRankUpdate}  rank{2}  skillType{8}  name{Alchemy}  skillIndex{1}  
		//x{0.21715186536312}  y{0.46380305290222}  zone{Vulkhel Guard}  
		//gameTime{2610340}  timeStamp{4743643324668182528}  userName{...}  ipAddress{...}  logTime{1396487021}  end{}
		
		$this->AddSkillInfo($logEntry['skillIndex'], $logEntry['name'], $logEntry['skillType']);
		
		$lastBookRecord = &$this->currentUser['lastBookRecord'];
		$lastBookLogEntry = &$this->currentUser['lastBookLogEntry'];
		
		if ($lastBookRecord == null || $lastBookLogEntry == null) return true;
		
		$skillGameTime = (int) $logEntry['gameTime'];
		$bookGameTime  = $lastBookLogEntry['gameTime'];
		$diffTime = $skillGameTime - $bookGameTime;
		if ($diffTime < 0 || $diffTime > 1000) return true;
		
		if ($lastBookRecord['skill'] == '')
		{
			//print("\t\tFound {$logEntry['name']} skill update for book {$lastBookRecord['title']}...\n");
			$lastBookRecord['skill'] = $logEntry['name'];
			$this->SaveBook($lastBookRecord);
		}
		
		return true;
	}
	
	
	public function OnSkyshard ($logEntry)
	{
		//event{Skyshard}  y{0.43859297037125}  zone{Portdun Watch}  x{0.68326634168625}  lastTarget{Skyshard} 
		//timeStamp{4743645430720495616}  gameTime{56651357}  userName{Reorx}  end{}
		
		//print("\t\tFound Skyshard...\n");
		
		return $this->CheckLocation("skyshard", "Skyshard", $logEntry, null);
	}
	
	
	public function OnFoundTreasure ($logEntry)
	{
		//event{FoundTreasure}  name{Chest}  x{0.25863909721375}  y{0.76831662654877}  lastTarget{Chest}  zone{Wayrest}  
		//gameTime{336361}  timeStamp{4743645698686189568}  userName{Reorx}  end{}  
		
		//print("\t\tFound Treasure...\n");
		
		$result = $this->CheckLocation("treasure", $logEntry['name'], $logEntry, null);
		
		if ($logEntry['name'] == "Chest")
		{
			$chestRecord = $this->createNewRecord(self::$CHEST_FIELDS);
			
			$locationId = $this->currentUser['lastLocationRecordId'];
			if ($locationId == null) $locationId = 0;
			
			$chestRecord['locationId'] = (int) $locationId;
			$chestRecord['quality'] = -1;
			
			$result &= $this->saveChest($chestRecord);
			$this->currentUser['lastChestRecord'] = $chestRecord;
			
			$diff = $logEntry['gameTime'] - $this->currentUser['__lastChestFoundGameTime'];
			//print("Chest Diff = $diff\n");
			
			if ($diff >= self::TREASURE_DELTA_TIME || $diff < 0)
			{
				//$this->log("\tFound user chest...");
				++$this->currentUser['chestsFound'];
				$this->currentUser['__dirty'] = true;
				$this->currentUser['__lastChestFoundGameTime'] = $logEntry['gameTime'];
			}
		}
		else if ($logEntry['name'] == "Heavy Sack")
		{
			$diff = $logEntry['gameTime'] - $this->currentUser['__lastSackFoundGameTime'];
			
			if ($diff >= self::TREASURE_DELTA_TIME || $diff < 0)
			{
				//$this->log("\tFound user sack...");
				++$this->currentUser['sacksFound'];
				$this->currentUser['__dirty'] = true;
				$this->currentUser['__lastSackFoundGameTime'] = $logEntry['gameTime'];
			}
		}
		else if ($logEntry['name'] == "Heavy Crate")
		{
			$diff = $logEntry['gameTime'] - $this->currentUser['__lastSackFoundGameTime'];
			
			if ($diff >= self::TREASURE_DELTA_TIME || $diff < 0)
			{
				//$this->log("\tFound user crate...");
				++$this->currentUser['sacksFound'];
				$this->currentUser['__dirty'] = true;
				$this->currentUser['__lastSackFoundGameTime'] = $logEntry['gameTime'];
			}
		}
		else if ($logEntry['name'] == "Thieves Trove")
		{
			$diff = $logEntry['gameTime'] - $this->currentUser['__lastTroveFoundGameTime'];
			
			if ($diff >= self::TREASURE_DELTA_TIME || $diff < 0)
			{
				++$this->currentUser['trovesFound'];
				$this->currentUser['__dirty'] = true;
				$this->currentUser['__lastTroveFoundGameTime'] = $logEntry['gameTime'];
			}
		}
		
		return $result;
	}
	
	
	public function OnExperienceUpdate ($logEntry)
	{
		//event{ExperienceUpdate}  reason{10}  xpGained{915}  unit{player}  maxXP{0}
		//x{0.56155747175217}  zone{Auridon}  y{0.69421499967575}
		//timeStamp{4743653895434141696}  gameTime{32224169}  userName{Reorx}
		
		if ($logEntry['reason'] == 0)
		{
			++$this->currentUser['mobsKilled'];
			$this->currentUser['__dirty'] = true;
		}
		
		return true;
	}
	
	
	public function OnLockPick ($logEntry)
	{
		//event{LockPick}  quality{1}  x{0.25767278671265}  y{0.77135974168777}  zone{Wayrest}  gameTime{336988}  timeStamp{4743645698686189568}  userName{Reorx}  end{}
		
		$locationId = $this->currentUser['lastLocationRecordId'];
		if ($locationId == null) $locationId = 0;
		
		if ($this->currentUser['lastChestRecord'] == null)
			$chestRecord = $this->createNewRecord(self::$CHEST_FIELDS);
		else 
			$chestRecord = &$this->currentUser['lastChestRecord'];
		
		$chestRecord['locationId'] = (int) $locationId;
		$chestRecord['quality'] = (int) $logEntry['quality'];
		
		$result = $this->saveChest($chestRecord);
		return $result;
	}
	
	
	//event{Recipe}  numIngredients{2}  provLevel{1}  name{shornhelm ale}  specialType{2}  quality{1}  gameTime{4580338}  timeStamp{4743642815408373760}  userName{}  ipAddress{}  logTime{1396192828}  end{}
	//event{Recipe::Result}  icon{/esoui/art/icons/crafting_dom_beer_001.dds}  qnt{1}  name{shornhelm ale}  value{2}  quality{2}  itemLink{|H2DC50E:item:33933:0:0:0:0:0:0:0:0:0:0:0:0:0:0:0:0:0:0:0|hshornhelm ale|h}  gameTime{4580338}  timeStamp{4743642815408373760}  userName{}  ipAddress{}  logTime{1396192828}  end{}
	//event{Recipe::Ingredient}  icon{/esoui/art/icons/crafting_cloth_pollen.dds}  qnt{1}  name{shornhelm grains^p}  value{0}  quality{1}  itemLink{|HFFFFFF:item:33767:0:0:0:0:0:0:0:0:0:0:0:0:0:0:0:0:0:0:0|hshornhelm grains^p|h}  gameTime{4580338}  timeStamp{4743642815408373760}  userName{}  ipAddress{}  logTime{1396192828}  end{}
	//event{Recipe::Ingredient}  icon{/esoui/art/icons/crafting_cloth_pollen.dds}  qnt{1}  name{brown malt}  value{0}  quality{1}  itemLink{|HFFFFFF:item:40260:0:0:0:0:0:0:0:0:0:0:0:0:0:0:0:0:0:0:0|hbrown malt|h}  gameTime{4580338}  timeStamp{4743642815408373760}  userName{}  ipAddress{}  logTime{1396192828}  end{}
	
	
	public function OnRecipe ($logEntry)
	{
		$name = $logEntry['name'];
		$recipeRecord = $this->FindRecipe($name);
		
		if ($recipeRecord == null)
		{
			$recipeRecord = $this->CreateRecipe($logEntry);
			if ($recipeRecord == null) return false;
		}
		
		$this->currentUser['lastRecipeRecord'] = $recipeRecord;
		return true;
	}
	
	
	public function OnRecipeResult ($logEntry)
	{
		$recipeRecord = &$this->currentUser['lastRecipeRecord'];
		if ($recipeRecord == null) return $this->ReportLogParseError("Missing recipe for recipe result!");
		
		$itemRecord = $this->FindItemLink($logEntry['itemLink']);
		
		if ($itemRecord == null)
		{
			$itemRecord = $this->CreateItem($logEntry);
			if ($itemRecord == null) return false;
		}
		
		$recipeRecord['resultItemId'] = $itemRecord['id'];
		$result = $this->SaveRecipe($recipeRecord);
		
		return $result;
	}
	
	
	public function OnRecipeIngredient ($logEntry)
	{
		$recipeRecord = &$this->currentUser['lastRecipeRecord'];
		if ($recipeRecord == null) return $this->ReportLogParseError("Missing recipe for recipe ingredient!");
		$recipeId = $recipeRecord['id'];
		
		$itemRecord = $this->FindItemLink($logEntry['itemLink']);
		
		if ($itemRecord == null)
		{
			$itemRecord = $this->CreateItem($logEntry);
			if ($itemRecord == null) return false;
		}
		
		$itemId = $itemRecord['id'];
		$ingredientRecord = $this->FindIngredient($recipeId, $itemId, $logEntry['name']);
		
		if ($ingredientRecord == null)
		{
			$ingredientRecord = $this->CreateIngredient($recipeId, $itemId, $logEntry);
			if ($ingredientRecord == null) return false;
		}
		
		return true;
	}
	
	
	public function OnTargetChange ($logEntry)
	{
		//event{TargetChange}  level{19}  gender{2}  difficulty{1}  name{Stonechewer Skirmisher}  lastTarget{Aspect Rune}  
		//x{0.45511141419411}  zone{Stormhaven}  y{0.47166284918785}  timeStamp{4743643569678450688}  gameTime{2655510}  
		//userName{...}  ipAddress{...}  logTime{1396487115}  end{}
		
		$name = $logEntry['name'];
		$npcRecord = $this->FindNPC($name);
		
		if ($npcRecord == null)
		{
			$npcRecord = $this->CreateNPC($logEntry);
			if ($npcRecord == null) return false;
		}
		
		$npcLocation = $this->FindLocation("npc", $logEntry['x'], $logEntry['y'], $logEntry['zone'], array('npcId' => $npcRecord['id']));
		
		if ($npcLocation == null)
		{
			$npcLocation = $this->CreateLocation("npc", $name, $logEntry, array('npcId' => $npcRecord['id']));
			if ($npcLocation == null) return false;
		}
		else
		{
			++$npcLocation['counter'];
			
			$result = $this->SaveLocation($npcLocation);
			if (!$result) return false;
		}
		
		return true;
	}
	
	
	public function OnFish ($logEntry)
	{
		return $this->CheckLocation("fish", "Fishing Hole", $logEntry, null);
	}
	
	
	public function OnMineItemIdCheckStart ($logEntry)
	{
		$this->currentUser['lastMinedItemIdCheckNote'] = $logEntry['note'];
	}
	
	
	public function OnMineItemIdCheckEnd ($logEntry)
	{
		$this->currentUser['lastMinedItemIdCheckNote'] = null;
	}
	
	
	public function OnMineItemIdCheck ($logEntry)
	{
		$version = $this->currentUser['lastMinedItemIdCheckNote'];
		if ($version == null || $version == "") return false;
		
		$startId = (int) $logEntry['startId'];
		$endId = (int) $logEntry['endId'];
		
		if ($startId <= 0 || $endId <= 0) return false;
		if ($startId > $endId) return false;
		
		for ($id = $startId; $id <= $endId; ++$id)
		{
			$query = "INSERT INTO itemIdCheck(itemId, version) VALUES($id, '$version')";
			$this->lastQuery = $query;
			
			$result = $this->db->query($query);
			if ($result === false) return $this->reportError("Failed to create itemIdCheck record!");
		}
		
		return true;
	}
	
	
	public function OnMineItemStart ($logEntry)
	{
		if ($logEntry['timeStamp'] < self::START_MINEITEM_TIMESTAMP) return false;
		$this->currentUser['lastMinedItemLogEntry'] = null;
		$this->currentUser['mineItemStartGameTime'] = $logEntry['gameTime'];
		$this->currentUser['mineItemStartTimeStamp'] = $logEntry['timeStamp'];
	}
	
	
	public function OnMineItemEnd ($logEntry)
	{
		if ($logEntry['timeStamp'] < self::START_MINEITEM_TIMESTAMP) return false;
		$this->currentUser['lastMinedItemLogEntry'] = null;
	}
	
	
	public function ParseMinedItemLog (&$logEntry)
	{
		$itemLink = $logEntry['itemLink'];
		
		$parsedLink = $this->ParseItemLink($itemLink);
		
		if ($parsedLink)
		{
			$logEntry['itemId'] = $parsedLink['itemId'];
			$logEntry['internalSubtype'] = $parsedLink['subtype'];
			$logEntry['internalLevel'] = $parsedLink['level'];
			$logEntry['enchantId'] = $parsedLink['enchantId'];
			$logEntry['enchantSubtype'] = $parsedLink['enchantSubtype'];
			$logEntry['enchantLevel'] = $parsedLink['enchantLevel'];
			$logEntry['potionData'] = $parsedLink['potionData'];
		}
		
			// Strip trailing control code from name if any
		if (array_key_exists('name', $logEntry))
		{
			$matchData = array();
			$result = preg_match("|(.*)(\^[a-zA-Z0-9]*)|s", $logEntry['name'], $matchData);
			if ($result) $logEntry['name'] = $matchData[1];
		}
		
		if (array_key_exists('reqVetLevel', $logEntry) && $logEntry['reqVetLevel'] > 0)
		{
			$logEntry['level'] = strval(intval($logEntry['reqVetLevel']) + 50);
		}
		elseif (array_key_exists('reqCP', $logEntry) && $logEntry['reqCP'] > 0)
		{
			$logEntry['level'] = strval(intval($logEntry['reqCP'])/10 + 50);
		}
		elseif (array_key_exists('reqLevel', $logEntry) && $logEntry['reqLevel'] > 0)
		{
			$logEntry['level'] = $logEntry['reqLevel'];
		}
		
		if (!array_key_exists('setMaxCount', $logEntry))
		{
			$logEntry['setMaxCount'] = 0;
			$highestSetDesc = "";
			
			if (array_key_exists('setDesc1', $logEntry) && $logEntry['setDesc1'] != "") $highestSetDesc = $logEntry['setDesc1'];
			if (array_key_exists('setDesc2', $logEntry) && $logEntry['setDesc2'] != "") $highestSetDesc = $logEntry['setDesc2'];
			if (array_key_exists('setDesc3', $logEntry) && $logEntry['setDesc3'] != "") $highestSetDesc = $logEntry['setDesc3'];
			if (array_key_exists('setDesc4', $logEntry) && $logEntry['setDesc4'] != "") $highestSetDesc = $logEntry['setDesc4'];
			if (array_key_exists('setDesc5', $logEntry) && $logEntry['setDesc5'] != "") $highestSetDesc = $logEntry['setDesc5'];
			if (array_key_exists('setDesc6', $logEntry) && $logEntry['setDesc6'] != "") $highestSetDesc = $logEntry['setDesc6'];
			
			if ($highestSetDesc != "")
			{
				$matches = array();
				$result = preg_match("/\(([0-9]+) items\)/", $highestSetDesc, $matches);
				if ($result) $logEntry['setMaxCount'] = (int) $matches[1];
			}
		}
		
		if (array_key_exists('flag', $logEntry))
		{
			$flags = explode(' ', $logEntry['flag']);
			
			foreach ($flags as $key => $flag)
			{
				if ($flag == "Unique")
					$logEntry['isUnique'] = true;
				else if ($flag == "UniqueEquipped")
					$logEntry['isUniqueEquipped'] = true;
				else if ($flag == "Vendor")
					$logEntry['isVendor'] = true;
				else if ($flag == "ArmorDecay")
					$logEntry['isArmorDecay'] = true;
				else if ($flag == "Consumable")
					$logEntry['isConsumable'] = true;
			}
		}
		
		if (array_key_exists('dyeStampId', $logEntry) && $logEntry['dyeStampId'] > 0)
		{
			$dyeId = $logEntry['dyeStampId'];
			$pId = $logEntry['primaryDyeId'];
			$sId = $logEntry['secondaryDyeId'];
			$aId = $logEntry['accentDyeId'];
			$pColor = $logEntry['primaryDyeColor'];
			$sColor = $logEntry['secondaryDyeColor'];
			$aColor = $logEntry['accentDyeColor'];
			$pName = $logEntry['primaryDyeName'];
			$sName = $logEntry['secondaryDyeName'];
			$aName = $logEntry['accentDyeName'];
			
			$a = '{';
			$b = '}';
			
			$logEntry['dyeData'] = "$dyeId, $pId$a$pName$b$a$pColor$b, $sId$a$sName$b$a$sColor$b, $aId$a$aName$b$a$aColor$b";
		}
		
		if (array_key_exists('recipeLink', $logEntry) && $logEntry['recipeLink'] != "")
		{
			$resultAbility = $logEntry['resultUseAbility'];
			$resultCooldown = $logEntry['resultCooldown'];
			$recipeIngredients = $logEntry['recipeIngredients'];
			$resultMinLevel = $logEntry['resultMinLevel'];
			$resultMaxLevel = $logEntry['resultMaxLevel'];
			$recipeRank = $logEntry['recipeRank'];
			$recipeQuality = $logEntry['recipeQuality'];
			
			if ($resultAbility == null) $resultAbility = "";
			if ($resultCooldown == null) $resultCooldown = "0";
			if ($resultMinLevel == null) $resultMinLevel = "";
			if ($resultMaxLevel == null) $resultMaxLevel = "";
			if ($recipeRank == null) $recipeRank = "";
			if ($recipeQuality == null) $recipeQuality = "";
			if ($recipeIngredients == null) $recipeIngredients = "";
			
			if ($resultAbility != "")
			{
				$abilityDesc = $resultAbility;
				if ($resultCooldown != "" && $resultCooldown > 0) $abilityDesc .= " (" . intval($resultCooldown/1000) . " second cooldown)";
				
				if ($resultMinLevel > 0 && $resultMaxLevel > 0)
				{
					$minImage = "level ";
					$maxImage = "level ";
					
					if ($resultMinLevel > 50)
					{
						$resultMinLevel = ($resultMinLevel - 50) * 10; 
						$minImage = "|t24:24:champion_icon_24.dds|t";
					}
					
					if ($resultMaxLevel > 50)
					{
						$resultMaxLevel = ($resultMaxLevel - 50) * 10;
						$maxImage = "|t24:24:champion_icon_24.dds|t";
					}
					
					$abilityDesc .= "\nScales from $minImage|cffffff".$resultMinLevel."|r to $maxImage|cffffff".$resultMaxLevel."|r.";
				}
				else if ($resultMinLevel == 0 && $resultMaxLevel == 0)
				{
					//$abilityDesc .= "\nThese effects are scaled based on your level.";
				}
				
				$recipeIngredients = preg_replace("#(\^[a-zA-Z]+)#", "", $recipeIngredients);
				
				if ($recipeIngredients != "") $abilityDesc .= "\n\n|cffffffINGREDIENTS|r\n" . ucwords($recipeIngredients);
				if ($recipeRank > 0 && $recipeQuality > 0) $abilityDesc .= "\n\n|cffffffTO CREATE|r\n|c00ff00Requires Recipe Improvement $recipeRank|r\n|c00ff00Requires Recipe Quality $recipeQuality|r";
				
				$logEntry['useAbilityDesc'] = $abilityDesc;
				
				//print("\tCreated Recipe Description: $abilityDesc\n");
			}			
		}		
	}
	
	
	public function OnMineItem ($logEntry)
	{
		if ($logEntry['timeStamp'] < self::START_MINEITEM_TIMESTAMP) return false;
		
		$itemLink = $logEntry['itemLink'];
		if ($itemLink == null) return $this->reportLogParseError("Missing item link!");
		
		$minedItem = $this->LoadMinedItemLink($itemLink);
		if ($minedItem === false) return false;
		
		if ($minedItem['__isNew'] === true)
		{
			++$this->currentUser['newCount'];
			$this->currentUser['__dirty'] = true;
		}
		
		$this->ParseMinedItemLog($logEntry);
		$this->MergeMineItemLogToDb($minedItem, $logEntry);
		
		if (array_key_exists('setDesc6', $logEntry))
		{
			if ($minedItem['itemId'] != $this->lastSetCount6WarningItemId)
			{
				$setName = $logEntry['setName'];
				$setCount = $logEntry['setBonusCount'];
				$itemId = $minedItem['itemId'];
				print("\tWarning: item #$itemId, set $setName has $setCount set bonus elements!\n");
				$this->lastSetCount6WarningItemId = $minedItem['itemId'];
			}
			
			if ($minedItem['setName'] == "Amberplasm")
			{
				$minedItem['setBonusDesc4'] = $logEntry['setDesc4'] . "\n" . $logEntry['setDesc5'];
				$minedItem['setBonusDesc5'] = $logEntry['setDesc6'];
			}
			else
			{
				$minedItem['setBonusDesc5'] = $logEntry['setDesc5'] . "\n" . $logEntry['setDesc6'];
			}
			
			$minedItem['__dirty'] = true;
		}
		
		$result = true;
		if ($minedItem['__dirty']) $result &= $this->SaveMinedItem($minedItem);
		
		$this->currentUser['lastMinedItemLogEntry'] = $logEntry;
		//print("Found mined item $itemLink\n");
		return $result;
	}
	
	
	public function MergeMineItemLogToDb (&$minedItem, $logEntry)
	{
		
		foreach ($logEntry as $key => $value)
		{
			if (!array_key_exists($key, self::$MINED_ITEMKEY_TO_DBKEY)) continue;
			$dbKey = self::$MINED_ITEMKEY_TO_DBKEY[$key];
			
			//if ($dbKey != null && (!array_key_exists($dbKey, $minedItem) || $minedItem[$dbKey] != $value))
			{
				$minedItem[$dbKey] = $value;
				$minedItem['__dirty'] = true;
			}
		}
		
	}
	
	
	public function MergeMineItemLogs ($logEntry, $lastEntry)
	{
		$mergedLogEntry = $logEntry;
		if ($lastEntry == null) return $mergedLogEntry;
		
		foreach ($lastEntry as $key => $value)
		{
			if (!array_key_exists($key, $mergedLogEntry))
			{
				$mergedLogEntry[$key] = $value;
			}
		}
		
		return $mergedLogEntry;
	}
	
	
	public function OnMineItemShort ($logEntry)
	{
		if ($logEntry['timeStamp'] < self::START_MINEITEM_TIMESTAMP) return false;
		
		$itemLink = $logEntry['itemLink'];
		if ($itemLink == null) return $this->reportLogParseError("Missing item link!");
		
		$minedItem = $this->LoadMinedItemLink($itemLink);
		if ($minedItem === false) return false;
		
		if ($minedItem['__isNew'] === true)
		{
			++$this->currentUser['newCount'];
			$this->currentUser['__dirty'] = true;
		}
		
		$this->ParseMinedItemLog($logEntry);
		$mergedLogEntry = $this->MergeMineItemLogs($logEntry, $this->currentUser['lastMinedItemLogEntry']);
		$this->MergeMineItemLogToDb($minedItem, $mergedLogEntry);
		
		if (array_key_exists('setDesc6', $mergedLogEntry))
		{
			if ($minedItem['itemId'] != $this->lastSetCount6WarningItemId)
			{
				$setName = $mergedLogEntry['setName'];
				$setCount = $mergedLogEntry['setBonusCount'];
				$itemId = $minedItem['itemId'];
				print("\tWarning: item #$itemId, set $setName has $setCount set bonus elements!\n");
				$this->lastSetCount6WarningItemId = $minedItem['itemId'];
			}
				
			if ($minedItem['setName'] == "Amberplasm")
			{
				$minedItem['setBonusDesc4'] = $mergedLogEntry['setDesc4'] . "\n" . $mergedLogEntry['setDesc5'];
				$minedItem['setBonusDesc5'] = $mergedLogEntry['setDesc6'];
			}
			else
			{
				$minedItem['setBonusDesc5'] = $mergedLogEntry['setDesc5'] . "\n" . $mergedLogEntry['setDesc6'];
			}
				
			$minedItem['__dirty'] = true;
		}
		
		$result = true;
		if ($minedItem['__dirty']) $result &= $this->SaveMinedItem($minedItem);
		
		$this->currentUser['lastMinedItemLogEntry'] = $mergedLogEntry;
		//print("Found mined item $itemLink\n");
		return $result;
	}
	
	
	public function OnSkillCoefStart ($logEntry)
	{
		//numSkills{169}  numPoints{5}
		
		if (!$this->IsValidUser($logEntry)) return false;
		
		$this->currentUser['lastSkillCoefIgnore'] = true;
		$numPoints = $logEntry['numPoints'];
		
		if ($numPoints >= self::ELP_SKILLCOEF_MININUM_NUMPOINTS) 
		{
			$this->currentUser['lastSkillCoefIgnore'] = false;
		}
		
		return true;
	}
	
	
	public function OnSkillCoef ($logEntry)
	{
		//R1{0.99999}  desc{Conjure...can absorb |cffffff$1|r damage.}  a1{0.30219}  c1{-3.29720}  
		//name{Conjured Ward}  b1{-0.00406}  abilityId{28418}  numVars{1}  lang{en}
		
		if (!$this->IsValidUser($logEntry)) return false;
		if ($this->currentUser['lastSkillCoefIgnore']) return true;
		
		$numVars = $logEntry['numVars'];
		
		$coefData = array();
		$coefData['numCoefVars'] = $numVars;
		$coefData['coefDescription'] = $logEntry['desc'];
		$coefData['id'] = $logEntry['abilityId'];
		
		if ($numVars > self::ELP_SKILLCOEF_MAXCOEFVARS) $numVars = self::ELP_SKILLCOEF_MAXCOEFVARS; 
		
		for ($i = 1; $i <= $numVars; ++$i)
		{
			$a = $logEntry["a".$i];
			$b = $logEntry["b".$i];
			$c = $logEntry["c".$i];
			$R = $logEntry["R".$i];
			$type = $logEntry["type".$i];
			
			if ($a == null) continue;
			if ($b == null) continue;
			if ($c == null) continue;
			if ($R == null) continue;
			
			if ($R < self::ELP_SKILLCOEF_MININUM_R2) continue;
			
			$coefData["a".$i] = $a;
			$coefData["b".$i] = $b;
			$coefData["c".$i] = $c;
			$coefData["R".$i] = $R;
			$coefData["avg".$i] = $logEntry["avg".$i];
			$coefData["type".$i] = $type;
		}
		
		$this->SaveSkillCoef($coefData);
		return true;
	}
	
	
	public function OnSkillCoefEnd ($logEntry)
	{
		$this->currentUser['lastSkillCoefIgnore'] = false;
		return true;
	}
	
	
	public function OnSkillDumpStart ($logEntry)
	{
		if (!$this->IsValidUser($logEntry)) return false; 
		
		if ($logEntry['note'] != null)
			$this->currentUser['lastSkillDumpNote'] = $logEntry['note'];
		else
			$this->currentUser['lastSkillDumpNote'] = '';
		
		$this->currentUser['lastSkillLineName'] = null;
		$this->log("\tFound SkillDumpStart(".$this->currentUser['lastSkillDumpNote'].")...");
		
		$this->logInfos['lastSkillUpdate'] = date("Y-M-d H:i:s");
		return true;
	}
	
	
	public function OnSkillDumpEnd ($logEntry)
	{
		if (!$this->IsValidUser($logEntry)) return false;
		$this->currentUser['lastSkillDumpNote'] = null;
		$this->currentUser['lastSkillLineName'] = null;
		return true;
	}
	
	
	public function OnSkill ($logEntry)
	{
		if (!$this->IsValidUser($logEntry)) return false;
		
		$version = $this->currentUser['lastSkillDumpNote'];
		$abilityId = $logEntry['id'];
		if ($abilityId == null || $abilityId == "") return $this->reportLogParseError("Missing abilityId in skill!");
		
		$skill = $this->LoadSkillDump($abilityId);
		if ($skill === false) return false;
  	
		$skill['name'] = $logEntry['name'];
		$skill['description'] = $logEntry['desc'];
		$skill['duration'] = $logEntry['duration'];
		$skill['cost'] = $logEntry['cost'];
		$skill['target'] = $logEntry['target'];
		$skill['minRange'] = $logEntry['minRange'];
		$skill['maxRange'] = $logEntry['maxRange'];
		$skill['radius'] = $logEntry['radius'];
		$skill['isPassive'] = $logEntry['passive'];
		$skill['isChanneled'] = $logEntry['channel'];
		$skill['castTime'] = $logEntry['castTime'];
		$skill['channelTime'] = $logEntry['channelTime'];
		$skill['angleDistance'] = $logEntry['angleDistance'];
		$skill['mechanic'] = $logEntry['mechanic'];
		$skill['upgradeLines'] = $logEntry['upgradeLines'];
		$skill['effectLines'] = $logEntry['effectLines'];
		$skill['texture'] = $logEntry['icon'];
		
		if (array_key_exists('rank', $logEntry)) $skill['rank'] = $logEntry['rank'];
		if (array_key_exists('learnedLevel', $logEntry)) $skill['learnedLevel'] = $logEntry['learnedLevel'];
		if (array_key_exists('abilityIndex', $logEntry)) $skill['skillIndex'] = $logEntry['abilityIndex'];
		
		if (array_key_exists('skillLine', $logEntry)) 
		{
			static $SKILL_TYPES = array(
					"Legerdemain" => 4,
					"Werewolf" => 4,
					"Vampire" => 4,
					"Thieves Guild" => 5,
					"Emperor" => 6,
					"Provisioning" => 8,
					"Dark Brotherhood" => 5,
				);
			
			$skill['isPlayer'] = 1;
			$skill['skillLine'] = $logEntry['skillLine'];
			$skill['skillType'] = $SKILL_TYPES[$logEntry['skillLine']];
			if ($skill['skillType'] == null) $skill['skillType'] = 0;
		}
		
		if (array_key_exists('nextSkill', $logEntry))
		{
			$skill['nextSkill'] = $logEntry['nextSkill'];
			$skill['nextSkill2'] = $logEntry['nextSkill2'];
			$skill['prevSkill'] = $logEntry['prevSkill'];
			
			if ($skill['nextSkill'] < 0) $skill['nextSkill'] = 0;
			if ($skill['nextSkill2'] < 0) $skill['nextSkill2'] = 0;
			if ($skill['prevSkill'] < 0) $skill['prevSkill'] = 0;
		}
				
		$this->SaveSkillDump($skill);
		
		return true;
	}
	
	
	public function OnSkillLearned ($logEntry)
	{
		if (!$this->IsValidUser($logEntry)) return false;
		$version = $this->currentUser['lastSkillDumpNote'];
		
		$abilityId = $logEntry['id'];
		if ($abilityId == null || $abilityId == "") return $this->reportLogParseError("Missing abilityId in learned skill!");
		
		$skill = $this->LoadSkillDump($abilityId);
		if ($skill === false) return false;
		
		if (array_key_exists('texture', $logEntry)) $skill['texture'] = $logEntry['texture'];
		if (array_key_exists('level',   $logEntry)) $skill['learnedLevel'] = $logEntry['level'];
		$skill['isPlayer'] = 1;
		
		$this->SaveSkillDump($skill);
		
		return true;
	}
	
	
	public function OnSkillType ($logEntry)
	{
		if (!$this->IsValidUser($logEntry)) return false;
		$version = $this->currentUser['lastSkillDumpNote'];
		
		return true;
	}
	
	
	public function OnSkillLine ($logEntry)
	{
		if (!$this->IsValidUser($logEntry)) return false;
		$version = $this->currentUser['lastSkillDumpNote'];
		
		$this->currentUser['lastSkillLineName'] = $logEntry['name'];
		
		$skillLine = $this->LoadSkillLine($logEntry['name']);
		if ($skillLine === false) return false;
		
		$skillLine['xp'] = $logEntry['xpString'];
		$skillLine['totalXp'] = $logEntry['totalXp'];
		if (array_key_exists('race', $logEntry)) $skillLine['raceType'] = $logEntry['race'];
		if (array_key_exists('class', $logEntry)) $skillLine['classType'] = $logEntry['class'];
		$skillLine['skillType'] = $logEntry['skillType'];
		
		if (array_key_exists('numRanks', $logEntry))
		{
			$skillLine['numRanks'] = $logEntry['numRanks'];
		}
		else
		{
			$skillLine['numRanks'] = substr_count($logEntry['xpString'], ',') + 1;
		}
		
		if ($skillLine['skillType'] == 7)
		{
			$skillLine['fullName'] = 'Racial::' . $logEntry['name'];
		}
		elseif ($skillLine['skillType'] == 1)
		{
			$skillLine['fullName'] = $skillLine['classType'] . '::' . $logEntry['name'];
		}
		else
		{
			$skillLine['fullName'] = $this->GetSkillTypeText($skillLine['skillType']) . '::' . $logEntry['name'];
		}
		
		$this->SaveSkillLine($skillLine);
		
		return true;
	}
	
	
	public function OnSkillAbilityId ($logEntry, $abilityId, $prevAbilityId, $nextAbilityId, $rankMod)
	{
		if ($abilityId == null || $abilityId == "") return false;
		if (!$this->IsValidUser($logEntry)) return false;
		
		$skill = $this->LoadSkillDump($abilityId);
		if ($skill === false) return false;
		
		if (array_key_exists('texture', $logEntry)) $skill['texture'] = $logEntry['texture'];
		if (array_key_exists('skillType', $logEntry)) $skill['skillType'] = $logEntry['skillType'];
		if (array_key_exists('class', $logEntry)) $skill['classType'] = $logEntry['class'];
		if (array_key_exists('race', $logEntry)) $skill['raceType'] = $logEntry['race'];
		
		if (array_key_exists('level', $logEntry)) 
		{
			$skill['rank'] = $logEntry['level'] + $rankMod;
			if ($skill['rank'] == -1) $skill['rank'] = 1;
		}		
		
		if ($logEntry['passive'] == "true" && $skill['rank'] == 0) $skill['rank'] = 1;
		
		if (array_key_exists('rank', $logEntry) && $rankMod == 0) 
		{
			if ($logEntry['passive'] == "false")
				$skill['learnedLevel'] = $logEntry['rank'];
			else if ($logEntry['level'] <= 1)
				$skill['learnedLevel'] = $logEntry['rank'];
		}
		
		if (array_key_exists('nextEarnedRank', $logEntry))
		{
			if ($rankMod == 1)
			{
				$skill['learnedLevel'] = $logEntry['nextEarnedRank'];
			}
		}
		
		$skill['skillLine'] = $this->currentUser['lastSkillLineName'];
		$skill['isPlayer'] = 1;
		$skill['nextSkill'] = $nextAbilityId;
		$skill['prevSkill'] = $prevAbilityId;
		$skill['skillIndex'] = $logEntry['abilityIndex'];
		
		$this->SaveSkillDump($skill);
		
		return true;
	}
	
	
	public function OnSkillAbility ($logEntry)
	{
		if (!$this->IsValidUser($logEntry)) return false;
		$version = $this->currentUser['lastSkillDumpNote'];
		
		$this->OnSkillAbilityId($logEntry, $logEntry['abilityId1'], -1, $logEntry['abilityId2'], 0);
		$this->OnSkillAbilityId($logEntry, $logEntry['abilityId2'], $logEntry['abilityId1'], -1, 1);
		
		return true;
	}
	
	
	public function OnSkillProgression ($logEntry)
	{
		if (!$this->IsValidUser($logEntry)) return false;
		$version = $this->currentUser['lastSkillDumpNote'];
		
		$prevSkill = 0;
		$nextSkill = 0;
		$nextSkill2 = 0;
		
		for ($morph = 0; $morph < 3; ++$morph)
		{
			$name = $logEntry['name' . $morph];
			$texture = $logEntry['texture' . $morph];
			$prevSkill = 0;
			
			if ($morph > 0)
			{
				$prevSkill = $logEntry['id04'];
				if ($prevSkill == null) $prevSkill = 0;
			}
			
			for ($level = 1; $level < 5; ++$level)
			{
				$id = $logEntry['id' . $morph . $level];
				if ($id == null || $id == "" || $id <= 0) continue;
				
				$skill = $this->LoadSkillDump($id);
				if ($skill === false) continue;
				
				if ($morph == 0 && $level == 4)
				{
					$nextSkill = $logEntry['id11'];
					if ($nextSkill == null) $nextSkill = 0;
					$nextSkill2 = $logEntry['id21'];
					if ($nextSkill2 == null) $nextSkill2 = 0;
				}
				else
				{
					$nextSkill2 = 0;
					$nextSkill = $logEntry['id' . $morph . ($level + 1)];
					if ($nextSkill == null) $nextSkill = 0;
				}
				
				if ($texture != "") $skill['texture'] = $texture;
				$skill['prevSkill'] = $prevSkill;
				$skill['nextSkill'] = $nextSkill;
				$skill['nextSkill2'] = $nextSkill2;
				$skill['isPlayer'] = 1;
				$skill['rank'] = $level;
				
				$this->SaveSkillDump($skill);
				
				$prevSkill = $id;
			}
		}
		
		return true;
	}
	
		
	public function OnCPStart ($logEntry)
	{
		if (!$this->IsValidUser($logEntry)) return false;
	
		if ($logEntry['note'] != null)
			$this->logInfos['lastCPNote'] = $logEntry['note'];
		else
			$this->logInfos['lastCPNote'] = '';

		$this->log("\tFound CPStart(".$logEntry['note'].")...");
		$this->logInfos['lastCPUpdate'] = date("Y-M-d H:i:s");
		
		$this->lastQuery = "DELETE FROM cpDisciplines".self::SKILLS_TABLESUFFIX.";";
		$result = $this->db->query($this->lastQuery);
		if (!$result) return $this->reportLogParseError("Failed to clear cpDisciplines table!");
		
		$this->lastQuery = "DELETE FROM cpSkills".self::SKILLS_TABLESUFFIX.";";
		$result = $this->db->query($this->lastQuery);
		if (!$result) return $this->reportLogParseError("Failed to clear cpSkills table!");
		
		$this->lastQuery = "DELETE FROM cpSkillDescriptions".self::SKILLS_TABLESUFFIX.";";
		$result = $this->db->query($this->lastQuery);
		if (!$result) return $this->reportLogParseError("Failed to clear cpSkillDescriptions table!");
		
		return true;
	}
	
	
	public function OnCPDiscipline ($logEntry)
	{
		if (!$this->IsValidUser($logEntry)) return false;
		
		$cp = array();
		$cp['disciplineIndex'] = $logEntry['discIndex'];
		$cp['description'] = $logEntry['desc'];
		$cp['name'] = $logEntry['name'];
		$cp['attribute'] = $logEntry['attr'];
		
		$cp['__isNew'] = true;
		$cp['__dirty'] = true;
		
		return $this->SaveCPDiscipline($cp);
	}
	
	
	public function OnCPSkill ($logEntry)
	{
		if (!$this->IsValidUser($logEntry)) return false;
		
		$cpDisc = array();
		$cpDisc['abilityId'] = $logEntry['abilityId'];
		$cpDisc['disciplineIndex'] = $logEntry['discIndex'];
		$cpDisc['skillIndex'] = $logEntry['skillIndex'];
		$cpDisc['minDescription'] = $logEntry['desc'];
		$cpDisc['maxDescription'] = $logEntry['maxDesc'];
		$cpDisc['name'] = $logEntry['name'];
		$cpDisc['x'] = $logEntry['x'];
		$cpDisc['y'] = $logEntry['y'];
		$cpDisc['maxValue'] = 0;
		$cpDisc['a'] = -1;
		$cpDisc['b'] = -1;
		$cpDisc['r2'] = -1;
		$cpDisc['fitDescription'] = "";
		
		if ($logEntry['unlockLevel'] == null)
		{
			$cpDisc['unlockLevel'] = 0;
			$matches = array();
			$result = preg_match("#([0-9\.]+)#", $logEntry['maxDesc'], $matches);
			if ($result) $cpDisc['maxValue'] = $matches[1];
		}
		else
		{
			$cpDisc['unlockLevel'] = $logEntry['unlockLevel'];
		}
		
		$cpDisc['__isNew'] = true;
		$cpDisc['__dirty'] = true;
		
		return $this->SaveCPSkill($cpDisc);
	}
	
	
	public function OnCPDescription ($logEntry)
	{
		if (!$this->IsValidUser($logEntry)) return false;
		
		$cpDesc = array();
		$cpDesc['abilityId'] = $logEntry['abilityId'];
		$cpDesc['description'] = $logEntry['desc'];
		$cpDesc['points'] = $logEntry['points'];
		$cpDesc['__isNew'] = true;
		$cpDesc['__dirty'] = true;
		
		return $this->SaveCPSkillDescription($cpDesc);
	}
	
	
	public function OnCPEnd ($logEntry)
	{
		if (!$this->IsValidUser($logEntry)) return false;
		return true;
	}
	
	
	public function OnMineCollectIDStart ($logEntry)
	{
		if (!$this->IsValidUser($logEntry)) return false;
		
		return true;
	}
	
	
	public function OnMineCollectID ($logEntry)
	{
		if (!$this->IsValidUser($logEntry)) return false;
		
		$id = $logEntry['id'];
		if ($id == null || $id == "") return false;
		
		$collectible = $this->LoadCollectible($id);
		
		$collectible['name'] = $logEntry['name'];
		$collectible['nickname'] = $logEntry['nickname'];
		$collectible['description'] = $logEntry['description'];
		$collectible['itemLink'] = $logEntry['itemLink'];
		$collectible['hint'] = $logEntry['hint'];
		$collectible['icon'] = $logEntry['icon'];
		$collectible['backgroundIcon'] = $logEntry['bgImage'];
		$collectible['lockedIcon'] = $logEntry['lockedIcon'];
		$collectible['categoryType'] = $logEntry['categoryType'];
		$collectible['zoneIndex'] = $logEntry['zoneIndex'];
		$collectible['categoryIndex'] = $logEntry['category'];
		$collectible['subCategoryIndex'] = $logEntry['subCategory'];
		$collectible['collectibleIndex'] = $logEntry['index'];
		$collectible['achievementIndex'] = $logEntry['achieveIndex'];
		$collectible['categoryName'] = $logEntry['categoryName'];
		$collectible['subCategoryName'] = $logEntry['subCategoryName'];
		$collectible['isUnlocked'] = $logEntry['unlocked'];
		$collectible['isActive'] = $logEntry['isActive'];
		$collectible['isPlaceholder'] = $logEntry['isPlaceholder'];
		$collectible['isSlottable'] = $logEntry['isSlottable'];
		$collectible['isUsable'] = $logEntry['isUsable'];
		$collectible['isRenameable'] = $logEntry['isRenameable'];
		$collectible['isHidden'] = $logEntry['isHidden'];
		$collectible['hasAppearance'] = $logEntry['hasAppearance'];
		$collectible['visualPriority'] = $logEntry['visualPriority'];
		$collectible['helpCategoryIndex'] = $logEntry['helpCategoryIndex'];
		$collectible['helpIndex'] = $logEntry['helpIndex'];
		$collectible['questName'] = $logEntry['questName'];
		$collectible['backgroundText'] = $logEntry['backgroundText'];
		$collectible['cooldown'] = $logEntry['cooldown'];
		$collectible['__dirty'] = true;
		
		return $this->SaveCollectible($collectible);
	}
	
	
	public function OnMineCollectIDEnd ($logEntry)
	{
		return true;
	}
	
	
	public function OnAchievementStart ($logEntry)
	{		
		if (!$this->IsValidUser($logEntry)) return false;
		
		print("\tFound Achievement::Start...\n");
				
		$this->lastQuery = "DELETE FROM achievementCategories;";
		$result = $this->db->query($this->lastQuery);
		if (!$result) return $this->reportLogParseError("Failed to clear achievementCategories table!");
		
		$this->lastQuery = "DELETE FROM achievements;";
		$result = $this->db->query($this->lastQuery);
		if (!$result) return $this->reportLogParseError("Failed to clear achievements table!");
		
		$this->lastQuery = "DELETE FROM achievementCriteria;";
		$result = $this->db->query($this->lastQuery);
		if (!$result) return $this->reportLogParseError("Failed to clear achievementCriteria table!");
				
		return true;
	}
	
	
	public function OnAchievementCategory ($logEntry)
	{
		if (!$this->IsValidUser($logEntry)) return false;
		
		$achievementCategory = array();
		
		$achievementCategory['categoryName'] = $logEntry['name'];
		$achievementCategory['name'] = $logEntry['name'];
		$achievementCategory['subcategoryName'] = "";
		$achievementCategory['categoryIndex'] = $logEntry['categoryIndex'];
		$achievementCategory['subCategoryIndex'] = -1;
		$achievementCategory['numAchievements'] = $logEntry['numAchievements'];
		$achievementCategory['points'] = $logEntry['points'];
		$achievementCategory['hidesPoints'] = $logEntry['hidesPoints'];
		$achievementCategory['icon'] = $logEntry['icon'];
		$achievementCategory['pressedIcon'] = $logEntry['pressedIcon'];
		$achievementCategory['mouseoverIcon'] = $logEntry['mouseoverIcon'];
		$achievementCategory['gamepadIcon'] = $logEntry['gamepadIcon'];
		
		$achievementCategory['__isNew'] = true;
		$achievementCategory['__dirty'] = true;
		
		return $this->SaveAchievementCategory($achievementCategory);
	}
	
	
	public function OnAchievementSubcategory ($logEntry)
	{
		if (!$this->IsValidUser($logEntry)) return false;
		
		$achievementCategory = array();
		
		$achievementCategory['categoryName'] = $logEntry['categoryName'];
		$achievementCategory['subcategoryName'] = $logEntry['name'];
		$achievementCategory['name'] =  $logEntry['categoryName'] . "::" . $logEntry['name'];
		$achievementCategory['categoryIndex'] = $logEntry['categoryIndex'];
		$achievementCategory['subCategoryIndex'] = $logEntry['subCategoryIndex'];
		$achievementCategory['numAchievements'] = $logEntry['numAchievements'];
		$achievementCategory['points'] = $logEntry['points'];
		$achievementCategory['hidesPoints'] = $logEntry['hidesPoints'];
		$achievementCategory['icon'] = "";
		$achievementCategory['pressedIcon'] = "";
		$achievementCategory['mouseoverIcon'] = "";
		$achievementCategory['gamepadIcon'] = "";
		
		$achievementCategory['__isNew'] = true;
		$achievementCategory['__dirty'] = true;
		
		return $this->SaveAchievementCategory($achievementCategory);
	}
	
	
	public function OnAchievement ($logEntry)
	{
		if (!$this->IsValidUser($logEntry)) return false;
		
		$id = $logEntry['id'];
		if ($id == null || $id == "") return false;
		
		$achievement = $this->LoadAchievement($id);
		
		$achievement['name'] = $logEntry['name'];
		$achievement['categoryIndex'] = $logEntry['categoryIndex'];
		$achievement['subCategoryIndex'] = $logEntry['subCategoryIndex'];
		$achievement['achievementIndex'] = $logEntry['achievementIndex'];
		$achievement['description'] = $logEntry['description'];
		$achievement['points'] = $logEntry['points'];
		$achievement['icon'] = $logEntry['icon'];
		$achievement['numRewards'] = $logEntry['numRewards'];
		$achievement['itemLink'] = $logEntry['itemLink'];
		$achievement['link'] = $logEntry['link'];
		$achievement['firstId'] = $logEntry['firstId'];
		$achievement['prevId'] = $logEntry['prevId'];
		$achievement['points'] = $logEntry['points'];
		$achievement['itemName'] = $logEntry['itemName'];
		$achievement['itemIcon'] = $logEntry['itemIcon'];
		$achievement['itemQuality'] = $logEntry['itemQuality'];
		$achievement['title'] = $logEntry['title'];
		
		if ($logEntry['subCategoryName'] == null || $logEntry['subCategoryName'] == "")
			$achievement['categoryName'] = $logEntry['categoryName'];
		else
			$achievement['categoryName'] = $logEntry['categoryName'] . "::" . $logEntry['subCategoryName'];
		
		if ($logEntry['hasDyeReward'])
		{
			if ($logEntry['dyeId'] != null)
				$achievement['dyeId'] = $logEntry['dyeId'];
			else if ($logEntry['dyeIndex'] != null)
				$achievement['dyeId'] = $logEntry['dyeIndex'];
				
			$achievement['dyeName'] = $logEntry['dyeName'];
			$achievement['dyeRarity'] = $logEntry['dyeRarity'];
			$achievement['dyeHue'] = $logEntry['dyeHue'];
			$achievement['dyeColor'] = dechex(floor($logEntry['dyeR'] * 255)) . dechex(floor($logEntry['dyeG'] * 255)) . dechex(floor($logEntry['dyeB'] * 255));
		}
		else
		{
			$achievement['dyeId'] = -1;
			$achievement['dyeName'] = "";
			$achievement['dyeRarity'] = -1;
			$achievement['dyeHue'] = -1;
			$achievement['dyeColor'] = "";
		}
		
		$achievement['collectibleId'] = $logEntry['collectibleId'];
		$achievement['__dirty'] = true;
		
		return $this->SaveAchievement($achievement);
	}
	
	
	public function OnAchievementCriteria ($logEntry)
	{
		if (!$this->IsValidUser($logEntry)) return false;
		
		$achievementCriteria = array();
		
		$achievementCriteria['achievementId'] = $logEntry['id'];
		$achievementCriteria['description'] = $logEntry['description'];
		$achievementCriteria['numRequired'] = $logEntry['numRequired'];
		$achievementCriteria['criteriaIndex'] = $logEntry['index'];
		$achievementCriteria['__isNew'] = true;
		$achievementCriteria['__dirty'] = true;
		
		return $this->SaveAchievementCriteria($achievementCriteria);
	}
	
	
	public function OnAchievementEnd ($logEntry)
	{
		if (!$this->IsValidUser($logEntry)) return false;
		
		return true;
	}	
	
	
	public function OnNullEntry ($logEntry)
	{
		// Do Nothing
		return true;
	}
	
	
	public function OnUnknownEntry ($logEntry)
	{
		$this->reportLogParseError("Unknown event '{$logEntry['event']}' found in log entry!");
		return true;
	}
	
	
	public function IsValidUser ($logEntry)
	{
		if ($this->currentUser['name'] != "Reorx") return $this->reportLogParseError("Ignoring {$logEntry['event']} from user ".$this->currentUser['name']."!");
		return true;
	}
	
	
	public function checkLanguage ($logEntry)
	{
		$language = $logEntry['lang'];
		
		if ($language == null)
		{
			$language = $this->currentUser['language'];
		}
		else if ($language != $this->currentUser['language'])
		{
			$this->currentUser['language'] = $language;
			$this->currentUser['__dirty'] = true;
		}
		
		if ($language != $this->currentLanguage) return false;
		return true;
	}
	
	
	public function checkLogEntryRecordCreate ($logEntry)
	{
		
		switch($logEntry['event'])
		{
			case 'mi':
			case 'mineitem':
			case 'mineItem':
			case "mineItem::AutoStart":
			case "mineItem::Start":
			case "mineItem::AutoEnd":
			case "mineItem::End":
			case "skill":
			case "skillDump::Start":
			case "skillDump::StartProgression":
			case "skillDump::StartType":
			case "skillDump::StartLearned":
			case "skillDump::StartMissing":
			case "skillDump::End":
			case "skillDump::EndProgression":
			case "skillDump::EndType":
			case "skillDump::EndLearned":
			case "skillDump::EndMissing":
			case "skillDump::start":
			case "skillDump::end":
			case "skillType":
			case "skillLine":
			case "skillAbility":
			case "skillLearned":
			case "skillProgression":
			case "CP":
			case "CP::start":
			case "CP::end":
			case "CP::disc":
			case "CP::desc":
			case "MineCollect::Start":
			case "MineCollect::Category":
			case "MineCollect::Subcategory":
			case "MineCollect::Index":
			case "MineCollect::End":
			case "MineCollectID::Start":
			case "MineCollectID":
			case "MineCollectID::End":
			case "Achievement::Start":
			case "Achievement::Subcategory":
			case "Achievement::Category":
			case "Achievement::Criteria":
			case "Achievement":
			case "Achievement::End":
				return false;
		}
		
		return true;
	}
	
	
	public function handleLogEntry ($logEntry)
	{
		if (!$this->isValidLogEntry($logEntry)) return false;
		
		$user = &$this->getUserRecord($logEntry['userName']);
		$ipAddress = &$this->getIPAddressRecord($logEntry['ipAddress']);
		$this->currentUser = &$user;
		$this->currentIpAddress = &$ipAddress;
		
		if ($user == null) return $this->reportLogParseError("Invalid user found!");
		if ($ipAddress == null) return $this->reportLogParseError("Invalid ipAddress found!");
		if ($user['enabled'] === false) return $this->reportLogParseError("User is disabled...skipping entry!");
		if ($ipAddress['enabled'] === false) return $this->reportLogParseError("IP address is disabled...skipping entry!");
		
		if (!$this->checkLanguage($logEntry)) return true;
		$createLogEntry = $this->checkLogEntryRecordCreate($logEntry);
		$isDuplicate = false;
		
		if ($createLogEntry)
		{
			$isDuplicate = $this->isDuplicateLogEntry($logEntry);
		}
		
		if ($this->skipDuplicates && $isDuplicate)
		{
			if (!$this->suppressDuplicateMsg) $this->log("{$this->currentParseLine}: Skipping duplicate log entry ({$logEntry['gameTime']}, {$logEntry['timeStamp']}, {$logEntry['__crc']})...");
			++$this->fileDuplicateCount;
			++$this->duplicateCount;
			++$user['duplicateCount'];
			$user['__dirty'] = true;
			return true;
		}
		else if (!$isDuplicate)
		{
		}
		
		if ($createLogEntry)
		{
			$logId = $this->addLogEntryRecordFromLog($logEntry);
			if ($logId === false) return false;
			$this->currentLogEntryId = $logId;
		}
		else
		{
			$this->currentLogEntryId = -1;
		}
		
		++$user['entryCount'];
		$user['__dirty'] = true;
		
		switch($logEntry['event'])
		{
			case "OpenFootLocker":				$result = $this->OnNullEntry($logEntry); break;
			case "LootGained":					$result = $this->OnLootGainedEntry($logEntry); break;
			case "SlotUpdate":					$result = $this->OnSlotUpdateEntry($logEntry); break;
			case "InvDump":						$result = $this->OnInvDump($logEntry); break;
			case "InvDump::Start":
			case "InvDumpStart":				$result = $this->OnInvDumpStart($logEntry); break;
			case "InvDump::End":
			case "InvDumpEnd":					$result = $this->OnInvDumpEnd($logEntry); break;
			case "MoneyGained":					$result = $this->OnNullEntry($logEntry); break;
			case "TargetChange":				$result = $this->OnTargetChange($logEntry); break;
			case "ChatterBegin":				$result = $this->OnNullEntry($logEntry); break;
			case "ChatterBegin::Option":		$result = $this->OnNullEntry($logEntry); break;
			case "ConversationUpdated":			$result = $this->OnNullEntry($logEntry); break;
			case "ConversationUpdated::Option":	$result = $this->OnNullEntry($logEntry); break;
			case "QuestAdded":					$result = $this->OnQuestAdded($logEntry); break;
			case "QuestChanged":				$result = $this->OnQuestChanged($logEntry); break;
			case "QuestAdvanced":				$result = $this->OnQuestAdvanced($logEntry); break;
			case "QuestOffered":				$result = $this->OnQuestOffered($logEntry); break;
			case "QuestRemoved":				$result = $this->OnQuestRemoved($logEntry); break;
			case "QuestObjComplete":			$result = $this->OnNullEntry($logEntry); break;
			case "QuestOptionalStep":			$result = $this->OnNullEntry($logEntry); break;
			case "QuestCompleteExperience":		$result = $this->OnNullEntry($logEntry); break;
			case "QuestMoney":					$result = $this->OnNullEntry($logEntry); break;
			case "QuestItem":					$result = $this->OnQuestItem($logEntry); break;
			case "CraftComplete":				$result = $this->OnNullEntry($logEntry); break;
			case "CraftComplete::Result":		$result = $this->OnNullEntry($logEntry); break;
			case "SkillRankUpdate":				$result = $this->OnSkillRankUpdate($logEntry); break;
			case "SkillPointsChanged":			$result = $this->OnNullEntry($logEntry); break;
			case "Location":					$result = $this->OnNullEntry($logEntry); break;
			case "LoreBook":					$result = $this->OnLoreBook($logEntry); break;
			case "ShowBook":					$result = $this->OnShowBook($logEntry); break;
			case "Sell":						$result = $this->OnNullEntry($logEntry); break;
			case "Buy":							$result = $this->OnNullEntry($logEntry); break;
			case "Fish":						$result = $this->OnFish($logEntry); break;
			case "Skyshard":					$result = $this->OnSkyshard($logEntry); break;
			case "FoundTreasure":				$result = $this->OnFoundTreasure($logEntry); break;
			case "LockPick":					$result = $this->OnLockPick($logEntry); break;
			case "Recipe":						$result = $this->OnRecipe($logEntry); break;
			case "Recipe::Result":				$result = $this->OnRecipeResult($logEntry); break;
			case "Recipe::Ingredient":			$result = $this->OnRecipeIngredient($logEntry); break;
			case "Recipe::List":				$result = $this->OnNullEntry($logEntry); break;
			case "Recipe::End":					$result = $this->OnNullEntry($logEntry); break;
			case "Global":						$result = $this->OnNullEntry($logEntry); break;
			case "Global::End":					$result = $this->OnNullEntry($logEntry); break;
			case "Achievement::Start":			$result = $this->OnAchievementStart($logEntry); break;
			case "Achievement":					$result = $this->OnAchievement($logEntry); break;
			case "Category":					$result = $this->OnAchievementCategory($logEntry); break;
			case "Subcategory":					$result = $this->OnAchievementSubcategory($logEntry); break;
			case "Achievement::Category":		$result = $this->OnAchievementCategory($logEntry); break;
			case "Achievement::Subcategory":	$result = $this->OnAchievementSubcategory($logEntry); break;
			case "Achievement::Criteria":		$result = $this->OnAchievementCriteria($logEntry); break;
			case "Achievement::End":			$result = $this->OnAchievementEnd($logEntry); break;
			case "ExperienceUpdate":			$result = $this->OnExperienceUpdate($logEntry); break;
			case "mineItem::AutoStart":			$result = $this->OnMineItemStart($logEntry); break;
			case "mineitem::Start":				$result = $this->OnMineItemStart($logEntry); break;
			case "mineItem::Start":				$result = $this->OnMineItemStart($logEntry); break;
			case "mineItems::idCheck::start":
			case "mineItem::idCheck::start":	$result = $this->OnMineItemIdCheckStart($logEntry); break;
			case "mineItems::idCheck::end":
			case "mineItem::idCheck::end":		$result = $this->OnMineItemIdCheckEnd($logEntry); break;
			case "mineItems::idCheck":
			case "mineItem::idCheck":			$result = $this->OnMineItemIdCheck($logEntry); break;
			case "mineItem::AutoEnd":			$result = $this->OnMineItemEnd($logEntry); break;
			case "mineitem::End":				$result = $this->OnMineItemEnd($logEntry); break;
			case "mineItem::End":				$result = $this->OnMineItemEnd($logEntry); break;
			case "mineitem":					$result = $this->OnMineItem($logEntry); break;
			case "mineItem":					$result = $this->OnMineItem($logEntry); break;
			case "mi":							$result = $this->OnMineItemShort($logEntry); break;
			case "ItemLink":					$result = $this->OnNullEntry($logEntry); break;		//TODO
			case "MailItem":					$result = $this->OnNullEntry($logEntry); break;		//TODO
			case "VeteranXPUpdate":				$result = $this->OnNullEntry($logEntry); break;		//TODO
			case "AllianceXPUpdate":			$result = $this->OnNullEntry($logEntry); break;		//TODO
			case "TelvarUpdate":				$result = $this->OnNullEntry($logEntry); break;		//TODO
			case "Stolen":						$result = $this->OnStolen($logEntry); break;
			case "SkillCoef::Start":			$result = $this->OnSkillCoefStart($logEntry); break;
			case "SkillCoef":					$result = $this->OnSkillCoef($logEntry); break;
			case "SkillCoef::End":				$result = $this->OnSkillCoefEnd($logEntry); break;
			case "skillDump::start":
			case "skillDump::Start":			$result = $this->OnSkillDumpStart($logEntry); break;
			case "skillDump::end":
			case "skillDump::End":				$result = $this->OnSkillDumpEnd($logEntry); break;
			case "skillDump::StartMissing":		$result = $this->OnSkillDumpStart($logEntry); break;
			case "skillDump::EndMissing":		$result = $this->OnSkillDumpEnd($logEntry); break;
			case "skill":						$result = $this->OnSkill($logEntry); break;
			case "skillDump::StartType":		$result = $this->OnSkillDumpStart($logEntry); break;
			case "skillType":					$result = $this->OnSkillType($logEntry); break;
			case "skillLine":					$result = $this->OnSkillLine($logEntry); break;
			case "skillAbility":				$result = $this->OnSkillAbility($logEntry); break;
			case "skillDump::EndType":			$result = $this->OnSkillDumpEnd($logEntry); break;
			case "skillDump::StartProgression":	$result = $this->OnSkillDumpStart($logEntry); break;
			case "skillProgression":			$result = $this->OnSkillProgression($logEntry); break;
			case "skillDump::EndProgression":	$result = $this->OnSkillDumpEnd($logEntry); break;
			case "skillDump::StartLearned":		$result = $this->OnSkillDumpStart($logEntry); break;
			case "skillLearned":				$result = $this->OnSkillLearned($logEntry); break;
			case "skillDump::EndLearned":		$result = $this->OnSkillDumpEnd($logEntry); break;
			case "CP::start":					$result = $this->OnCPStart($logEntry); break;
			case "CP::disc":					$result = $this->OnCPDiscipline($logEntry); break;
			case "CP":							$result = $this->OnCPSkill($logEntry); break;
			case "CP::desc":					$result = $this->OnCPDescription($logEntry); break;
			case "CP::end":						$result = $this->OnCPEnd($logEntry); break;
			case "MineCollect::Start":
			case "MineCollect::Category":
			case "MineCollect::Subcategory":
			case "MineCollect::Index":
			case "MineCollect::End":			$result = $this->OnNullEntry($logEntry); break;
			case "MineCollectID::Start":		$result = $this->OnMineCollectIDStart($logEntry); break;
			case "MineCollectID":				$result = $this->OnMineCollectID($logEntry); break;
			case "MineCollectID::End":			$result = $this->OnMineCollectIDEnd($logEntry); break;
			break;
			case "Test":
			case "TEST":
			case "test":						$result = $this->OnNullEntry($logEntry); break;
			default:							$result = $this->OnUnknownEntry($logEntry); break;
		}
		
		if ($result === false)
		{
			++$user['errorCount'];
			$user['__dirty'] = true;
		}
		
		return true;
	}
	
	
	public function parseLogEntry ($logString)
	{
		$matchData = array();
		$resultData = array();
		
		$result = preg_match_all("|([a-zA-Z0-9_]+){(.*?)}  |s", $logString, $matchData);
		
		if ($result === 0) 
		{
			$this->reportLogParseError("Failed to find any matches for log entry: " . $logString);
			return null;
		}
		
		foreach ($matchData[1] as $key => $value)
		{
			$resultData[$value] = $matchData[2][$key];
		}
		
		if (!array_key_exists('ipAddress', $resultData))
		{
			$event = $resultData['event'];
			if ($event == null) $event = 'NULL';
			$this->reportLogParseError("Missing IP address for event '$event'! Ignoring possibly corrupt data!");
			return null;
		}
		
		$this->prepareLogEntry($resultData, $logString);
		return $resultData; 
	}
	
	
	public function prepareLogEntry(&$logEntry, $logString)
	{
		$logEntry['__crc'] = crc32($logString);
		
		if (!array_key_exists('userName',  $logEntry) || $logEntry['userName'] == '')
		{
			$logEntry['userName'] = $this->lastValidUserName;
		}
		else
		{
			$this->lastValidUserName = $logEntry['userName'];
		}
		
/*		if ($logEntry['event'] == "mi" || $logEntry['event'] == "mineitem" || $logEntry['event'] == "mineItem")
		{
			if (!array_key_exists('gameTime', $logEntry))
			{
				$logEntry['gameTime'] = $this->currentUser['mineItemStartGameTime'];
			}
		
			if (!array_key_exists('timeStamp', $logEntry))
			{
				$logEntry['timeStamp'] = $this->currentUser['mineItemStartTimeStamp'];
			}
		} */
		
		$ipAddress = $logEntry['ipAddress'];
		
		if (!array_key_exists('gameTime', $logEntry))
		{
			$logEntry['gameTime'] = $this->lastValidTime[$ipAddress]['gameTime'];
		}
		else
		{
			$this->lastValidTime[$ipAddress]['gameTime'] = $logEntry['gameTime'];
		}
		
		if (!array_key_exists('timeStamp', $logEntry))
		{
			$logEntry['timeStamp'] = $this->lastValidTime[$ipAddress]['timeStamp'];
		}
		else
		{
			$this->lastValidTime[$ipAddress]['timeStamp'] = $logEntry['timeStamp'];
		}
		
	}
	
	
	public function parseEntireLog ($logFilename)
	{
		if (!$this->initDatabaseWrite()) return false;
		
		$this->log("Parsing entire log file $logFilename...");
		
		$this->currentParseFile = $logFilename;
		$this->currentParseLine = 0;
		$this->fileDuplicateCount = 0;
		
		$fileIndex = 0;
		$result = preg_match("|eso([0-9]*)\.log|", $logFilename, $matches);
		if ($result) $fileIndex = (int) $matches[1];
		
		//print("FileIndex = $fileIndex\n");
		
		if ($this->startFileIndex > $fileIndex)
		{
			$this->log("\t\tSkipping file $logFilename...");
			return true;
		}
		
		$logEntries = array();
		$entryCount = 0;
		$errorCount = 0;
		
		$fileData = file_get_contents($logFilename);
		if ($fileData == null) return $this->reportError("Failed to load the log file '{$logFilename}'!");
		
		if (strlen($fileData) === 0) return TRUE;
		
		$result = preg_match_all('|(event{.*?end{}  )|s', $fileData, $logEntries);
		if ($result === 0) return $this->reportError("Failed to find any log entries in file '{$logFilename}'!");
		
		$totalLineCount = 0;
		$nextLineUpdate = 1000;
		
		foreach ($logEntries[1] as $key => $value)
		{
			$lineCount = substr_count($value, "\n") + 1;
			$totalLineCount += $lineCount;
			$this->currentParseLine = $totalLineCount;
			
			$entryLog = $this->parseLogEntry($value);
			
			if (!$this->handleLogEntry($entryLog))
			{
				++$errorCount;
			}
			
			++$entryCount;
			
			if ($totalLineCount >= $nextLineUpdate && self::SHOW_PARSE_LINENUMBERS)
			{
				print("\tParsing line $totalLineCount...\n");
				$nextLineUpdate += 1000;
			}
		}
		
		$this->log("\tParsed {$entryCount} log entries from file.");
		$this->log("\tFound {$errorCount} entries with errors.");
		$this->log("\tSkipped {$this->fileDuplicateCount} duplicate log entries.");
		return TRUE;
	}
	
	
	public function testParseEntry($logEntry)
	{
		$data = array();
		
		$result = preg_match_all("|([a-zA-Z]+){(.*?)}  |s", $logEntry, $data);
		print("Preg Result = " . $result . "\n");
		print("Result Count = " . count($data) . "\n");
		print("Result[0] Count = " . count($data[0]) . "\n");
		print("Result[1] Count = " . count($data[1]) . "\n");
		print("Result[2] Count = " . count($data[2]) . "\n");
		
		$varData = array();
		
		foreach ($data[1] as $key => $value)
		{
			$varData[$value] = $data[2][$key];
			//print($value . " = " . $data[2][$key] . "\n");
		}
		
		foreach ($varData as $key => $value)
		{
			print($key . " = " . $value . "\n");
		}
		
		return TRUE;
	}
	
	
	public function ParseAllLogs()
	{	
		$files = glob($this->logFilePath . "eso*.log");
		$this->createTables();
		$this->LoadLogInfo();
		
		foreach ($files as $key => $value)
		{
			$this->parseEntireLog($value);
		}
		
		$this->logInfos['lastUpdate'] = date("Y-M-d H:i:s");
		return true;
	}
	
	
	public function testParse()
	{
		$this->createTables();
		$this->parseEntireLog("/home/uesp/www/esolog/log/eso00004.log");
		return TRUE;
		
		$fileData = file_get_contents("/home/uesp/www/esolog/log/eso00001.log");
		if ($fileData == null) return $this->reportError();
		
		$logEntries = array();
		
		$result = preg_match_all("|(event{.*end{}  \n)|s", $fileData, $logEntries);
		print("Preg Result = " . $result . "\n");
		print("Result Count = " . count($logEntries) . "\n");
		print("Result[0] Count = " . count($logEntries[0]) . "\n");
		print("Result[1] Count = " . count($logEntries[1]) . "\n");
		
		foreach ($logEntries[1] as $key => $value)
		{
			//print($value);
			//$this->testParseEntry($value);
			$result = $this->parseLogEntry($value);
		}
		
		return true;
	}
	
	public function testItemLink()
	{
		$item1 = "|H2DC50E:item:30159:1:16:0:0:0:0:0:0:0:0:0:0:0:0:0:0:0:0:0|hwormwood|h";
		$item2 = "|H2DC50E:item:30159:1:16:0:0:0:0:0:0:0:0:0:0:0:0:0:0:0:0:0|hwormwood^p|h";
		$item3 = "|HFFFFFF:item:33767:1:0:0:0:0:0:0:0:0:0:0:0:0:0:0:0:0:0:0|hshornhelm grains^p|h";
		//$this->ParseItemLink($item1);
		//$this->ParseItemLink($item2);
		$this->ParseItemLink($item3);
		return true;
	}
	
	
	public function readIndexFile()
	{
		$filename = $this->logFilePath . self::ELP_INDEX_FILENAME;
		
		if (!file_exists($filename))
		{
			$this->currentLogIndex = 1;
			return false;
		}
		
		$index = file_get_contents($filename);
	
		if ($index === false)
		{
			$this->currentLogIndex = 1;
			return false;
		}
	
		$this->currentLogIndex = (int) $index;
		if ($this->currentLogIndex < 0) $this->currentLogIndex = 1;
	
		return true;
	}
	
	
	public function reportError ($errorMsg)
	{
		$this->log($errorMsg);
		
		if ($this->db != null && $this->db->error)
		{
			$this->log("\tDB Error:" . $this->db->error);
			$this->log("\tLast Query:" . $this->lastQuery);
		}
		return false;
	}
	
	
	public function reportLogParseError ($errorMsg)
	{
		//$this->reportError("{$this->currentParseFile}:{$this->currentParseLine}: {$errorMsg}");
		$this->reportError("\t{$this->currentParseLine}: {$errorMsg}");
		return false;
	}
	
	
	public function log ($msg)
	{
		print($msg . "\n");
		$result = file_put_contents($this->logFilePath . self::ELP_OUTPUTLOG_FILENAME, $msg . "\n", FILE_APPEND | LOCK_EX);
		return TRUE;
	}
	
	
	private function parseInputParams ()
	{
		if (array_key_exists('start', $this->inputParams))
		{
			$this->startFileIndex = (int) $this->inputParams['start'];
			$this->log("Starting log parsing at file index {$this->startFileIndex}.");
		}
		
		return true;
	}
	
	
	private function setInputParams ()
	{
		global $argv;
		$this->inputParams = $_REQUEST;
		
			// Add command line arguments to input parameters for testing
		if ($argv !== null)
		{
			$foundPath = false;
			$argIndex = 0;
			
			foreach ($argv as $arg)
			{
				$argIndex += 1;
				if ($argIndex <= 1) continue;
				
				$e = explode("=", $arg);
				
				if(count($e) == 2)
				{
					$this->inputParams[$e[0]] = $e[1];
				}
				elseif (!$foundPath)
				{
					$this->logFilePath = rtrim($e[0], '/') . '/';
					$foundPath = true;
				}
				else
				{
					$this->inputParams[$e[0]] = 1;
				}
			}
		}
	}
	
	
	public function writeHeaders ()
	{
		header("Expires: 0");
		header("Pragma: no-cache");
		header("Cache-Control: no-cache, no-store, must-revalidate");
		header("Pragma: no-cache");
		header("content-type: text/html");
	}
	
};


$g_EsoLogParser = new EsoLogParser();
$g_EsoLogParser->ParseAllLogs();
$g_EsoLogParser->saveData();


