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
require_once("parseSalesData.php");
require_once("esoCommon.php");
require_once("esoPotionData.php");
require_once("esoSkillRankData.php");


class EsoLogParser
{
	const SHOW_PARSE_LINENUMBERS = true;
	
	const ELP_INPUT_LOG_PATH = "";
	const ELP_OUTPUTLOG_FILENAME = "parser.log";
	
	const ELP_PARSE_INDEXFILE = "esolog.parse.index";
	
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
	//const START_MINEITEM_TIMESTAMP = 4743975994677000000;	//v12    1475650800
	//const START_MINEITEM_TIMESTAMP = 4744009321690431488;	//v13pts 1483541100
	//const START_MINEITEM_TIMESTAMP = 4744021174005006336; //v13 1486388686
														  //v14pts ? 
	//const START_MINEITEM_TIMESTAMP = 4744059227864039424; //v14 1495461433
														  //v15pts?
	//const START_MINEITEM_TIMESTAMP = 4744089672613888000; //v15 1502720027
	//const START_MINEITEM_TIMESTAMP = 4744115047104512000; //v16 1508769777
	//const START_MINEITEM_TIMESTAMP = ?; //v17pts ?
	//const START_MINEITEM_TIMESTAMP = 4744155630808000000; //v17 1518445680
	//const START_MINEITEM_TIMESTAMP = 4744191147415437312; //v18 1526913505
															//v19pts?
	//const START_MINEITEM_TIMESTAMP = 4744221569218248704; //v19 1534166628
	//const START_MINEITEM_TIMESTAMP = ; //v20pts ?
	//const START_MINEITEM_TIMESTAMP = 4744246935181852672; //v20 1540214345
	//const START_MINEITEM_TIMESTAMP = ;	//v21pts 1548072000
	//const START_MINEITEM_TIMESTAMP = ;	//v21
	//const START_MINEITEM_TIMESTAMP = 4744307872848936960;	//v22pts 1554743017
	//const START_MINEITEM_TIMESTAMP = 4744323044049158144;	//v22 1558360113
	const START_MINEITEM_TIMESTAMP = 4744353489713364992;	//v23 1565618406
			  	
		/* Ignore any guild sales earlier than this timestamp */
	const START_GUILDSALESDATA_TIMESTAMP = 0;
	
	const MINEITEM_TABLESUFFIX = "";
	const SKILLS_TABLESUFFIX   = "";
	
		/* Parse or skip certain types of log entries. */
	const ONLY_PARSE_SALES = false;
	const ONLY_PARSE_MINEDITEMS = false;
	const ONLY_PARSE_NPCLOOT = false;
	const ONLY_PARSE_NPCLOOT_CHESTS = false;
	const ONLY_PARSE_MAILITEM = false;
	const ONLY_PARSE_SAFEBOXES_FOUND = false;
	const ONLY_PARSE_SHOWBOOK = false;
	
		// Start of log09100.log: 1487204716 / 4744024596682899456
	//public $IGNORE_LOGENTRY_BEFORE_TIMESTAMP1 = 1487204716;
	
		// 4744159327491719168 = 1519327044
	//public $IGNORE_LOGENTRY_BEFORE_TIMESTAMP1 = 1519327044;
	//public $IGNORE_LOGENTRY_BEFORE_TIMESTAMP1 = 1526912000;
	//public $IGNORE_LOGENTRY_BEFORE_TIMESTAMP1 = 1551540983;
	public $IGNORE_LOGENTRY_BEFORE_TIMESTAMP1 = 1558361897;
	
	public $db = null;
	public $dbSlave = null;
	private $dbSlaveInitialized  = false;
	private $dbReadInitialized  = false;
	private $dbWriteInitialized = false;
	public $lastQuery = "";
	public $skipCreateTables = false;
	
	public $salesData = null;
	
	public $currentLanguage = 'en';
	
	public $logFilePath = "";
	public $currentParseLine = 0;
	public $currentParseFile = "";
	public $startFileIndex = -1;
	public $startFileLine = 0;
	public $usingManualStartIndex = false;
	public $lastFileIndexParsed = 0;
	public $lastFileLineParsed = 0;
	
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
	
	public $startMicroTime = 0;
	
	public $limitDbReadsWrites = false;
	public $waitForSlave = true;
	public $dbWriteCount = 0;
	public $dbReadCount = 0;
	public $dbReadCountPeriod = 2000;
	public $dbReadNextSleepCount = 2000;
	public $dbWriteCountPeriod = 400;
	public $dbWriteNextSleepCount = 400;
	public $dbReadCountSleep = 5;		// Period in seconds for sleep()
	public $dbWriteCountSleep = 5;		// Period in seconds for sleep()
	public $maxAllowedSlaveLag = 5;		// Maximum database slave lag in seconds before write delays are enforced
	public $maxSlaveLagChecks = 10;
	
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
	const RESOURCE_JEWELRY = 8;
	
	
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
			"minedSkills14pts",
			"minedSkills14",
			"minedSkills15pts",
			"minedSkills15",
			"minedSkills16pts",
			"minedSkills16",
			"minedSkills17pts",
			"minedSkills17",
			"minedSkills18pts",
			"minedSkills18",
			"minedSkills19pts",
			"minedSkills19",
			"minedSkills20pts",
			"minedSkills20",
			"minedSkills21pts",
			"minedSkills21",
			"minedSkills22pts",
			"minedSkills22",
			"minedSkills23pts",
			"minedSkills23",
			"minedSkills24pts",
			"minedSkills24",
			"minedSkills25pts",
			"minedSkills25",
			"minedSkills26pts",
			"minedSkills26",
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
			
		//'Barrel'				=> self::RESOURCE_JEWELRY,
			
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
			'bookId' => self::FIELD_INT,
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
	
	public static $OLDQUEST_FIELDS = array(
			'id' => self::FIELD_INT,
			'logId' => self::FIELD_INT,
			'locationId' => self::FIELD_INT,
			'name' => self::FIELD_STRING,
			'objective' => self::FIELD_STRING,
	);
	
	public static $OLDQUESTSTAGE_FIELDS = array(
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
	
	public static $QUEST_FIELDS = array(
			'id' => self::FIELD_INT,
			'internalId' => self::FIELD_INT,
			'logId' => self::FIELD_INT,
			'locationId' => self::FIELD_INT,
			'name' => self::FIELD_STRING,
			'level' => self::FIELD_INT,
			'type' => self::FIELD_INT,
			'repeatType' => self::FIELD_INT,
			'displayType' => self::FIELD_STRING,
			'backgroundText' => self::FIELD_STRING,
			'objective' => self::FIELD_STRING,
			'poiIndex' => self::FIELD_INT,
			'goalText' => self::FIELD_STRING,
			'confirmText' => self::FIELD_STRING,
			'declineText' => self::FIELD_STRING,
			'endDialogText' => self::FIELD_STRING,
			'endJournalText' => self::FIELD_STRING,
			'endBackgroundText' => self::FIELD_STRING,
			'isShareable' => self::FIELD_INT,
			'numTools' => self::FIELD_INT,
			'hasTimer' => self::FIELD_INT,
			'timerCaption' => self::FIELD_STRING,
			'timerDuration' => self::FIELD_FLOAT,
			'numSteps' => self::FIELD_INT,
			'numRewards' => self::FIELD_INT,
			'count' => self::FIELD_INT,
			'zone' => self::FIELD_STRING,
	);
	
	public static $QUESTSTEP_FIELDS = array(
			'id' => self::FIELD_INT,
			'logId' => self::FIELD_INT,
			'locationId' => self::FIELD_INT,
			'questId' => self::FIELD_INT,
			'uniqueId' => self::FIELD_INT,
			'stageIndex' => self::FIELD_INT,
			'stepIndex' => self::FIELD_INT,
			'text' => self::FIELD_STRING,
			'type' => self::FIELD_INT,
			'overrideText' => self::FIELD_STRING,
			'visibility' => self::FIELD_INT,
			'numConditions' => self::FIELD_INT,
			'count' => self::FIELD_INT,
	);
	
	public static $QUESTCONDITION_FIELDS = array(
			'id' => self::FIELD_INT,
			'logId' => self::FIELD_INT,
			'questId' => self::FIELD_INT,
			'uniqueId' => self::FIELD_INT,
			'questStepId' => self::FIELD_INT,
			'stageIndex' => self::FIELD_INT,
			'stepIndex' => self::FIELD_INT,
			'conditionIndex' => self::FIELD_INT,
			'type1' => self::FIELD_INT,
			'type2' => self::FIELD_INT,
			'text' => self::FIELD_STRING,
			'maxValue' => self::FIELD_INT,
			'isFail' => self::FIELD_INT,
			'isVisible' => self::FIELD_INT,
			'isComplete' => self::FIELD_INT,
			'isShared' => self::FIELD_INT,
			'count' => self::FIELD_INT,
	);
	
	public static $QUESTREWARD_FIELDS = array(
			'id' => self::FIELD_INT,
			'logId' => self::FIELD_INT,
			'questId' => self::FIELD_INT,
			'uniqueId' => self::FIELD_INT,
			'type' => self::FIELD_INT,
			'name' => self::FIELD_STRING,
			'quantity' => self::FIELD_INT,
			'icon' => self::FIELD_STRING,
			'quality' => self::FIELD_INT,
			'itemType' => self::FIELD_INT,
			'itemId' => self::FIELD_INT,
			'collectId' => self::FIELD_INT,
			'count' => self::FIELD_INT,
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
			'duration' => self::FIELD_FLOAT,
			'count' => self::FIELD_INT,
	);
	
	public static $NPC_FIELDS = array(
			'id' => self::FIELD_INT,
			'logId' => self::FIELD_INT,
			'name' => self::FIELD_STRING,
			'level' => self::FIELD_INT,
			'gender' => self::FIELD_INT,
			'difficulty' => self::FIELD_INT,
			'ppClass' => self::FIELD_STRING,
			'ppDifficulty' => self::FIELD_INT,
			'count' => self::FIELD_INT,
			'reaction' => self::FIELD_INT,
	);
	
	public static $NPC_LOCATION_FIELDS = array(
			'npcId' => self::FIELD_INT,
			'zone' => self::FIELD_STRING,
	);
	
	public static $LOOTSOURCE_FIELDS = array(
			'id' => self::FIELD_INT,
			'name' => self::FIELD_STRING,
			'count' => self::FIELD_INT,
	);
	
	public static $NPCLOOT_FIELDS = array(
			'id' => self::FIELD_INT,
			'logId' => self::FIELD_INT,
			'lootSourceId' => self::FIELD_INT,
			'zone' => self::FIELD_STRING,
			'itemLink' => self::FIELD_STRING,
			'itemName' => self::FIELD_STRING,
			'itemId' => self::FIELD_INT,
			'qnt' => self::FIELD_INT,
			'count' => self::FIELD_INT,
			'icon' => self::FIELD_STRING,
			'quality' => self::FIELD_INT,
			'itemType' => self::FIELD_INT,			
			'trait' => self::FIELD_INT,
			'value' => self::FIELD_INT,
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
			'quality' => self::FIELD_INT,
			'name' => self::FIELD_STRING,
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
			'specialType' => self::FIELD_INT,
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
			'specialType' => 'specialType',
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
			'displayId' => self::FIELD_INT,
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
			'isPermanent' => self::FIELD_INT,
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
			'baseAbilityId'  => self::FIELD_INT,
			'prevSkill'  => self::FIELD_INT,
			'nextSkill'  => self::FIELD_INT,
			'nextSkill2'  => self::FIELD_INT,			
			'rank'  => self::FIELD_INT,
			'morph'  => self::FIELD_INT,
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
			'c' => self::FIELD_FLOAT,
			'd' => self::FIELD_FLOAT,
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
			'furnCategory' => self::FIELD_STRING,
			'furnSubcategory' => self::FIELD_STRING,
	);
	
	public static $ACHIEVEMENTCATEGORY_FIELDS = array(
			'id' => self::FIELD_INT,
			'name' => self::FIELD_STRING,
			'categoryName' => self::FIELD_STRING,
			'subCategoryName' => self::FIELD_STRING,
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
			'nextId' => self::FIELD_INT,
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
		ini_set('mysql.connect_timeout', 1000);
		ini_set('mysql.wait_timeout', 1000);
		ini_set('default_socket_timeout', 1000);
		
		$this->startMicroTime = microtime(true);
				
		if (intval(self::MINEITEM_TABLESUFFIX) <= 8)
		{
			unset(self::$MINEDITEM_FIELDS['tags']);
		}
				
		if (intval(self::MINEITEM_TABLESUFFIX) < 13)
		{
			unset(self::$MINEDITEM_FIELDS['specialType']);
		}
		
		$this->salesData = new EsoSalesDataParser();
		$this->salesData->startMicroTime = $this->startMicroTime;
		
		$this->initDatabaseWrite();
		if ($this->waitForSlave) $this->initSlaveDatabase();
		
		$this->setInputParams();
		$this->parseInputParams();
		$this->readParseIndexFile();
	}
	
	
	public function PrintLine($text)
	{
		$currentMicroTime = microtime(true);
		$diffTime = floor(($currentMicroTime - $this->startMicroTime)*1000)/1000;
		$diffTime = number_format($diffTime, 3);
		
		print("\t$diffTime: $text\n");
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
		
		++$this->dbReadCount;
		
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
		
		++$this->dbReadCount;
	
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
		
		++$this->dbReadCount;
		
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
			
			$this->dbWriteCount++;
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
				$this->reportError("Missing value for $key field in $table table update!");
				continue;
			}
			
			if (!$isFirst) $query .= ', ';
			
			if ($value == self::FIELD_INT || $value == self::FIELD_FLOAT)
			{
				if ($record[$key] === null || $record[$key] === '' )
					$query .= "`{$key}`=-1";
				else
					$query .= "`{$key}`={$record[$key]}";
			}
			elseif ($value == self::FIELD_STRING)
				$query .= "`{$key}`='". $this->db->real_escape_string($record[$key]) ."'";
			else
				$this->reportError("Unknown ID type $value found for $key field in $table table!");
			
			$isFirst = false;
		}
		
		if ($idType == self::FIELD_INT)
			$query .= " WHERE `$idField`=$id;";
		elseif ($idType == self::FIELD_FLOAT)
			$query .= " WHERE `$idField`=$id;";
		elseif ($idType == self::FIELD_STRING)
			$query .= " WHERE `$idField`='". $this->db->real_escape_string($id) ."';";
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
				$this->reportError("Missing value for $key field in $table table insert!");
				continue;
			}
				
			if (!$isFirst)
			{
				$columns .= ', ';
				$values  .= ', ';
			}
			
			$columns .= "`" . $key . "`";
				
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
		
		$this->dbWriteCount++;
		
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
	
	
	public function LoadBookId ($bookId)
	{
		$book = $this->loadRecord('book', 'bookId', $bookId, self::$BOOK_FIELDS);
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
		
		$this->dbWriteCount++;
	
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
	
	
	public function SaveOldQuest (&$record)
	{
		return $this->saveRecord('quest', $record, 'id', self::$OLDQUEST_FIELDS);
	}
	
	
	public function SaveItem (&$record)
	{
		return $this->saveRecord('item', $record, 'id', self::$ITEM_FIELDS);
	}
	
	
	public function SaveOldQuestStage (&$record)
	{
		return $this->saveRecord('oldQuestStage', $record, 'id', self::$OLDQUESTSTAGE_FIELDS);
	}
	
	
	public function SaveQuest (&$record)
	{
		return $this->saveRecord('quest', $record, 'id', self::$QUEST_FIELDS);
	}
	
	
	public function SaveQuestStep (&$record)
	{
		return $this->saveRecord('questStep', $record, 'id', self::$QUESTSTEP_FIELDS);
	}
	
	
	public function SaveQuestCondition (&$record)
	{
		return $this->saveRecord('questCondition', $record, 'id', self::$QUESTCONDITION_FIELDS);
	}
	
	
	public function SaveQuestItem (&$record)
	{
		return $this->saveRecord('questItem', $record, 'id', self::$QUESTITEM_FIELDS);
	}
	
	
	public function SaveQuestReward (&$record)
	{
		return $this->saveRecord('questReward', $record, 'id', self::$QUESTREWARD_FIELDS);
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
	
	
	public function SaveLootSource (&$record)
	{
		return $this->saveRecord('lootSources', $record, 'id', self::$LOOTSOURCE_FIELDS);
	}	
	
	
	public function SaveNPCLoot (&$record)
	{
		return $this->saveRecord('npcLoot', $record, 'id', self::$NPCLOOT_FIELDS);
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
						safeBoxesFound INTEGER NOT NULL,
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
						bookId INTEGER NOT NULL,
						PRIMARY KEY (id),
						FULLTEXT(title),
						FULLTEXT(body),
						INDEX index_bookId(bookId)
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
						name TINYTEXT NOT NULL,
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
		
		$query = "CREATE TABLE IF NOT EXISTS oldQuest (
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
		if ($result === FALSE) return $this->reportError("Failed to create oldQuest table!");
		
		$query = "CREATE TABLE IF NOT EXISTS oldQuestStage (
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
		if ($result === FALSE) return $this->reportError("Failed to create oldQuestStage table!");
		
		$query = "CREATE TABLE IF NOT EXISTS quest (
						id BIGINT NOT NULL AUTO_INCREMENT,
						internalId INTEGER NOT NULL,
						logId BIGINT NOT NULL,
						locationId BIGINT NOT NULL,
						name TINYTEXT NOT NULL,
						zone TINYTEXT NOT NULL,
						level TINYINT NOT NULL,
						type SMALLINT NOT NULL,
						repeatType SMALLINT NOT NULL,
						displayType SMALLINT NOT NULL,
						backgroundText TEXT NOT NULL,
						objective TEXT NOT NULL,
						poiIndex INTEGER NOT NULL,
						goalText TEXT NOT NULL,
						confirmText TEXT NOT NULL,
						declineText TEXT NOT NULL,
						endDialogText TEXT NOT NULL,
						endBackgroundText TEXT NOT NULL,
						endJournalText TEXT NOT NULL,
						isShareable TINYINT NOT NULL,
						numTools TINYINT NOT NULL,
						hasTimer TINYINT NOT NULL,
						timerCaption TINYTEXT NOT NULL,
						timerDuration FLOAT NOT NULL,
						numSteps SMALLINT NOT NULL,				
						numRewards TINYINT NOT NULL,
						PRIMARY KEY (id),
						INDEX index_name(name(32)),
						FULLTEXT(backgroundText, objective, goalText, confirmText, declineText, endDialogText, endBackgroundText, endJournalText)
					);";
		
		$this->lastQuery = $query;
		$result = $this->db->query($query);
		if ($result === FALSE) return $this->reportError("Failed to create quest table!");
		
		$query = "CREATE TABLE IF NOT EXISTS questStep (
						id BIGINT NOT NULL AUTO_INCREMENT,
						logId BIGINT NOT NULL,
						locationId BIGINT NOT NULL,
						questId BIGINT NOT NULL,
						stageIndex SMALLINT NOT NULL,
						stepIndex SMALLINT NOT NULL,
						text TEXT NOT NULL,
						type SMALLINT NOT NULL,
						overrideText TEXT NOT NULL,
						visibility TINYINT NOT NULL,
						numConditions TINYINT NOT NULL,
						count INTEGER NOT NULL,
						uniqueId INTEGER NOT NULL,
						PRIMARY KEY (id),
						INDEX index_quest(questId),
						FULLTEXT(text, overrideText)
					);";
		
		$this->lastQuery = $query;
		$result = $this->db->query($query);
		if ($result === FALSE) return $this->reportError("Failed to create questStep table!");
		
		$query = "CREATE TABLE IF NOT EXISTS questCondition (
						id BIGINT NOT NULL AUTO_INCREMENT,
						logId BIGINT NOT NULL,
						questId BIGINT NOT NULL,
						questStepId BIGINT NOT NULL,
						stageIndex SMALLINT NOT NULL,
						stepIndex SMALLINT NOT NULL,
						conditionIndex TINYINT NOT NULL,
						type1 SMALLINT NOT NULL,
						type2 SMALLINT NOT NULL,
						text TEXT NOT NULL,
						`maxValue` INTEGER NOT NULL,
						isFail TINYINT NOT NULL,
						isComplete TINYINT NOT NULL,
						isShared TINYINT NOT NULL,
						isVisible TINYINT NOT NULL,
						count INTEGER NOT NULL,
						uniqueId INTEGER NOT NULL,
						PRIMARY KEY (id),
						INDEX index_quest(questId, stageIndex, stepIndex, conditionIndex),
						FULLTEXT(text)
					);";
		
		$this->lastQuery = $query;
		$result = $this->db->query($query);
		if ($result === FALSE) return $this->reportError("Failed to create questCondition table!");
		
		$query = "CREATE TABLE IF NOT EXISTS questReward (
						id BIGINT NOT NULL AUTO_INCREMENT,
						logId BIGINT NOT NULL,
						questId BIGINT NOT NULL,
						name TINYTEXT NOT NULL,
						type SMALLINT NOT NULL,
						itemId INTEGER NOT NULL,
						collectId INTEGER NOT NULL,
						icon TINYTEXT NOT NULL,
						quantity INTEGER NOT NULL,
						quality TINYINT NOT NULL,
						itemType SMALLINT NOT NULL,
						count INTEGER NOT NULL,
						uniqueId INTEGER NOT NULL,
						PRIMARY KEY (id),
						FULLTEXT(name),
						INDEX index_questId(questId)
					);";
		
		$this->lastQuery = $query;
		$result = $this->db->query($query);
		if ($result === FALSE) return $this->reportError("Failed to create questReward table!");
		
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
						duration FLOAT NOT NULL,
						count INTEGER NOT NULL,
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
						ppClass TINYTEXT NOT NULL,
						ppDifficulty TINYINT NOT NULL,
						count INTEGER NOT NULL,
						reaction TINYINT NOT NULL,
						PRIMARY KEY (id),
						FULLTEXT(name, ppClass)
					);";
		
		$this->lastQuery = $query;
		$result = $this->db->query($query);
		if ($result === FALSE) return $this->reportError("Failed to create npc table!");
		
		$query = "CREATE TABLE IF NOT EXISTS npcLocations (
						npcId BIGINT NOT NULL,
						zone TINYTEXT NOT NULL,
						PRIMARY KEY (npcId, zone(64))
					);";
		
		$this->lastQuery = $query;
		$result = $this->db->query($query);
		if ($result === FALSE) return $this->reportError("Failed to create npcLocations table!");
		
		$query = "CREATE TABLE IF NOT EXISTS lootSources (
						id BIGINT NOT NULL AUTO_INCREMENT,
						name TINYTEXT NOT NULL,
						count INTEGER NOT NULL,
						PRIMARY KEY (id),
						FULLTEXT(name)
					);";
		
		$this->lastQuery = $query;
		$result = $this->db->query($query);
		if ($result === FALSE) return $this->reportError("Failed to create lootSources table!");

		$query = "CREATE TABLE IF NOT EXISTS npcLoot (
						id BIGINT NOT NULL AUTO_INCREMENT,
						logId BIGINT NOT NULL,
						lootSourceId BIGINT NOT NULL,
						zone TINYTEXT NOT NULL,
						itemLink TINYTEXT NOT NULL,
						itemName TINYTEXT NOT NULL,
						itemId INTEGER NOT NULL,
						qnt INTEGER NOT NULL,
						count INTEGER NOT NULL,
						icon TINYTEXT NOT NULL,
						itemType SMALLINT NOT NULL,
						trait TINYINT NOT NULL,
						quality TINYINT NOT NULL,
						value SMALLINT NOT NULL,
						PRIMARY KEY (id),
						INDEX index_itemLink(itemLink(64)),
						INDEX index_itemId(itemId),
						INDEX index_lootSourceId(lootSourceId),
						INDEX index_zone(zone(24)),
						INDEX index_itemName(itemName(24))
					);";
		
		$this->lastQuery = $query;
		$result = $this->db->query($query);
		if ($result === FALSE) return $this->reportError("Failed to create npcLoot table!");
		
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
			specialType SMALLINT NOT NULL DEFAULT -1,
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
			INDEX index_itemId (itemId, internalLevel, internalSubtype),
			INDEX index_enchantId (enchantId)
		);";
		
		$this->lastQuery = $query;
		$result = $this->db->query($query);
		if ($result === FALSE) return $this->reportError("Failed to create minedItem table!");
		
		$query = "CREATE TABLE IF NOT EXISTS itemIdCheck(
			id BIGINT NOT NULL AUTO_INCREMENT PRIMARY KEY,
			itemId INTEGER NOT NULL,
			`version` TINYTEXT NOT NULL,
			INDEX index_itemId (itemId),
			INDEX index_version (`version`(8))
		);";
		
		$this->lastQuery = $query;
		$result = $this->db->query($query);
		if ($result === FALSE) return $this->reportError("Failed to create itemIdCheck table!");
		
		$query = "CREATE TABLE IF NOT EXISTS minedSkills".self::SKILLS_TABLESUFFIX."(
			id INTEGER NOT NULL PRIMARY KEY,
			displayId INTEGER NOT NULL DEFAULT -1,
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
			isPermanent TINYINT NOT NULL DEFAULT 0,
			castTime INTEGER NOT NULL DEFAULT -1,
			channelTime INTEGER NOT NULL DEFAULT -1,
			angleDistance INTEGER NOT NULL DEFAULT -1,
			mechanic INTEGER NOT NULL DEFAULT -1,
			texture TEXT NOT NULL,
			isPlayer TINYINT NOT NULL DEFAULT 0,
			raceType TINYTEXT NOT NULL,
			classType TINYTEXT NOT NULL,
			skillLine TINYTEXT NOT NULL,
			prevSkill INTEGER NOT NULL DEFAULT 0,
			nextSkill INTEGER NOT NULL DEFAULT 0,
			nextSkill2 INTEGER NOT NULL DEFAULT 0,
			baseAbilityId INTEGER NOT NULL DEFAULT 0,
			learnedLevel INTEGER NOT NULL DEFAULT -1,
			rank TINYINT NOT NULL DEFAULT 0,		
			morph TINYINT NOT NULL DEFAULT -1,
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
			`maxValue` FLOAT NOT NULL,
			x FLOAT NOT NULL,
			y FLOAT NOT NULL,
			a FLOAT NOT NULL DEFAULT -1,
			b FLOAT NOT NULL DEFAULT -1,
			c FLOAT NOT NULL DEFAULT -1,
			d FLOAT NOT NULL DEFAULT -1,
			r2 FLOAT NOT NULL DEFAULT -1,
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
			furnCategory TINYTEXT NOT NULL,
			furnSubcategory TINYTEXT NOT NULL,
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
			FULLTEXT(subCategoryName)
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
			nextId INTEGER NOT NULL,
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
		$this->log("Adding new user $userName...");
		
		$safeName = $this->db->real_escape_string($userName);
		
		$query = "INSERT INTO user(name) VALUES('{$safeName}');";
		$this->lastQuery = $query;
		$result = $this->db->query($query);
		
		$this->dbWriteCount++;
		
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
		$this->users[$userName]['safeBoxesFound'] = 0;
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
		$this->users[$userName]['mineItemStartGameTime'] = 0;
		$this->users[$userName]['mineItemStartTimeStamp'] = 0;
		$this->users[$userName]['__lastChestFoundGameTime'] = 0;
		$this->users[$userName]['__lastSackFoundGameTime'] = 0;
		$this->users[$userName]['__lastTroveFoundGameTime'] = 0;
		$this->users[$userName]['__lastBookGameTime'] = 0;
		$this->users[$userName]['__lastLootGainedTarget'] = '';
		$this->users[$userName]['__lastLootGainedGameTime'] = 0;
		$this->users[$userName]['__lastFootlockerOpenedName'] = $footLockerName;
		$this->users[$userName]['__lastFootlockerOpenedGameTime'] = $gameTime;
		$this->users[$userName]['lastQuestOffered'] = null;
		
		return $this->users[$userName];
	}
	
	
	public function addNewIPAddressRecord ($ipAddress)
	{
		$safeIP = $this->db->real_escape_string($ipAddress);
	
		$query = "INSERT INTO ipAddress(ipAddress) VALUES('{$safeIP}');";
		$this->lastQuery = $query;
		$result = $this->db->query($query);
				
		$this->dbWriteCount++;
	
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
		//$this->log("Getting data for user $userName...");
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
		
		++$this->dbReadCount;
		
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
		settype($row['safeBoxesFound'], "integer");
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
		$this->users[$userName]['safeBoxesFound'] = $row['safeBoxesFound'];
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
		$this->users[$userName]['mineItemStartGameTime'] = 0;
		$this->users[$userName]['lastMinedItemIdCheckNote'] = null;
		$this->users[$userName]['mineItemStartTimeStamp'] = 0;
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
		
		++$this->dbReadCount;
	
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
		$result &= $this->salesData->SaveUpdatedGuilds();
		$result &= $this->salesData->SaveUpdatedItems();		
				
		return $result;
	}
	
	
	public function saveUsers ()
	{
		$result = true;
		$this->log("Saving users...");
		
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
		$this->log("\tSaving user {$user['name']}...");
		
		$safeName = $this->db->real_escape_string($user['name']);
		
		$query = "UPDATE user SET entryCount={$user['entryCount']}, newCount={$user['newCount']}, errorCount={$user['errorCount']}, duplicateCount={$user['duplicateCount']}";
		$query .= ", itemsLooted={$user['itemsLooted']}";
		$query .= ", itemsStolen={$user['itemsStolen']}";
		$query .= ", chestsFound={$user['chestsFound']}";
		$query .= ", trovesFound={$user['trovesFound']}";
		$query .= ", sacksFound={$user['sacksFound']}";
		$query .= ", safeBoxesFound={$user['safeBoxesFound']}";
		$query .= ", booksRead={$user['booksRead']}";
		$query .= ", nodesHarvested={$user['nodesHarvested']}";
		$query .= ", mobsKilled={$user['mobsKilled']}";
		$query .= ", language='{$user['language']}'";
		$query .= " WHERE name='{$safeName}';";
		$this->lastQuery = $query;
		
		$result = $this->db->query($query);
		if ($result === FALSE) return $this->reportError("Failed to save user '{$safeName}'!");
		
		$this->dbWriteCount++;
		
		return true;
	}
	
	
	public function saveIPAddress ($ipAddress)
	{
		$safeName = $this->db->real_escape_string($ipAddress['ipAddress']);
		
		$enabled = $ipAddress['enabled'] ? 1 : 0;
		
		$query = "UPDATE ipAddress SET enabled={$enabled} WHERE ipAddress='{$safeName}';";
		$result = $this->db->query($query);
		if ($result === FALSE) return $this->reportError("Failed to save IP address '{$safeName}'!");
		
		$this->dbWriteCount++;
		
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
		
		++$this->dbReadCount;
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
		
		if ($logEntry['event'] == 'mi' || $logEntry['event'] == 'mineitem') 
		{
			return !($logEntry['ipAddress'] == '' || $logEntry['userName'] == '');
		}
		
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
		
		$this->dbWriteCount++;
	
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
		if ($this->db->connect_error) return $this->reportError("Could not connect to mysql database!");
	
		$this->dbReadInitialized = true;
		$this->dbWriteInitialized = false;
	
		return true;
	}
	
	
	private function initSlaveDatabase ()
	{
		global $uespEsoLogReadDBHost, $uespEsoLogReadUser, $uespEsoLogReadPW, $uespEsoLogDatabase;
	
		if ($this->dbSlaveInitialized) return true;
	
		$this->dbSlave = new mysqli($uespEsoLogReadDBHost, $uespEsoLogReadUser, $uespEsoLogReadPW, $uespEsoLogDatabase);
		if ($this->dbSlave->connect_error) return $this->reportError("Could not connect to mysql slave database!");
	
		$this->dbSlaveInitialized = true;
		
		$this->salesData->dbSlave = $this->dbSlave;
	
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
		if ($this->db->connect_error) return $this->reportError("Could not connect to mysql database!");
	
		$this->dbReadInitialized = true;
		$this->dbWriteInitialized = true;
	
		if ($this->skipCreateTables) return true;
		return $this->createTables();
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
	
	
	public function ParseLootGainedEntry ($logEntry)
	{
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
			
		$npcId = 0;
		$updateNpcZone = false;
			
		if ($logEntry['ppClassString'] != null && $logEntry['ppDifficulty'] != null)
		{
			$name = $logEntry['lastTarget'];
		
			if ($name != "")
			{
				$logEntry['name'] = $name;
				$npcRecord = $this->FindNPC($name);
					
				if ($npcRecord == null) {
					$npcRecord = $this->CreateNPC($logEntry);
				}
				else {
		
					if ($npcRecord['ppClass'] == "" && $logEntry['ppClassString'] != "")
					{
						$npcRecord['ppClass'] = $logEntry['ppClassString'];
						$npcRecord['ppDifficulty'] = $logEntry['ppDifficulty'];
						$this->SaveNPC($npcRecord);
					}
				}
					
				if ($npcRecord != null) $npcId = $npcRecord['id'];
			}
			
			$updateNpcZone = true;			
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
		
		$isNewLocation = false;
		$this->CheckLocation("item", $itemRecord['name'], $logEntry, array('itemId' => $itemRecord['id']), $isNewLocation);
		
		if ($updateNpcZone) $this->UpdateNpcZone($npcRecord['id'], $logEntry['zone'], $isNewLocation);
		
		return true;
	}
		
	
	public function OnLootGainedEntry ($logEntry)
	{
		//event{LootGained}  itemLink{|H2DC50E:item:30159:1:16:0:0:0:0:0:0:0:0:0:0:0:0:0:0:0:0:0|hwormwood|h}  lootType{1}  qnt{1}
		//lastTarget{Wormwood}  zone{Wayrest}  x{0.50276911258698}  y{0.073295257985592}  gameTime{65831937}  timeStamp{4743645111026450432}  userName{Reorx}  end{}
		//ppBonus, ppIsHostile, ppChance, ppDifficulty, ppEmpty, ppResult, ppClassString, ppClass
		
		if ($logEntry['itemName']) $logEntry['itemName'] = MakeEsoTitleCaseName($logEntry['itemName']);
		
		if (!self::ONLY_PARSE_NPCLOOT && !self::ONLY_PARSE_MAILITEM && !self::ONLY_PARSE_NPCLOOT_CHESTS)
		{
			$this->ParseLootGainedEntry($logEntry);
		}
		
		return $this->UpdateNpcLoot($logEntry);
	}
	
	
	public function FindItemName ($itemLink)
	{
		$matches = ParseEsoItemLink($itemLink);
		if ($matches === false) return "";
		
		$itemId = (int) $matches['itemId'];
		$itemLevel = (int) $matches['level'];
		$itemSubtype = (int) $matches['subtype'];
		
		$query = "SELECT name FROM minedItem WHERE itemId='$itemId' AND internalLevel='$itemLevel' AND internalSubtype='$itemSubtype';";
		$result = $this->db->query($query);
		if (!$result) return "";
		
		if ($result->num_rows == 0)
		{
			$query = "SELECT name FROM minedItem WHERE itemId='$itemId' AND internalLevel='1' AND internalSubtype='1';";
			$result = $this->db->query($query);
			if (!$result) return "";
			
			if ($result->num_rows == 0) return "";
		}
		
		++$this->dbReadCount;
		
		$row = $result->fetch_assoc();
		$name =  $row['name'];
		
		return $name;
	}
	
	
	public function FindMinedItemData ($itemLink)
	{
		$matches = ParseEsoItemLink($itemLink);
		if ($matches === false) return false;
	
		$itemId = (int) $matches['itemId'];
		$itemLevel = (int) $matches['level'];
		$itemSubtype = (int) $matches['subtype'];
	
		$query = "SELECT * FROM minedItem WHERE itemId='$itemId' AND internalLevel='$itemLevel' AND internalSubtype='$itemSubtype';";
		$result = $this->db->query($query);
		if (!$result) return false;
	
		if ($result->num_rows == 0)
		{
			$query = "SELECT * FROM minedItem WHERE itemId='$itemId' AND internalLevel='1' AND internalSubtype='1';";
			$result = $this->db->query($query);
			if (!$result) return false;
				
			if ($result->num_rows == 0) return false;
		}
		
		++$this->dbReadCount;
	
		$row = $result->fetch_assoc();
		return $row;
	}
	
	
	public function UpdateNpcLoot ($logEntry)
	{
		$logEntry['zone'] = preg_replace("#\^.*#", "", $logEntry['zone']);
		
		$zone = $logEntry['zone'];
		$gameTime = (int) $logEntry['gameTime'];
		$npcName = $logEntry['lastTarget'];
		$qnt = intval($logEntry['qnt']);
				
		if ($npcName == null || $npcName == "") return false;
		
		if (($npcName == "Chest" || $npcName == "Safebox") && $this->currentUser['__lastLockPickQuality'] != null)
		{
			$deltaTime = $logEntry['gameTime'] - $this->currentUser['__lastLockPickGameTime'];
			//$this->log("\tChest: $deltaTime, {$this->currentUser['__lastLockPickQuality']}");
			
			if ($deltaTime < 15000)
			{
				$chestType = GetEsoChestTypeText($this->currentUser['__lastLockPickQuality']);
				if ($chestType != "") $npcName = "$npcName ($chestType)";
			}
		}
		
		if ($logEntry['event'] == 'MoneyGained')
		{
			$logEntry['itemLink'] = "__gold";
			$logEntry['itemName'] = "Gold";
			$logEntry['itemId'] = "-101";
			$logEntry['icon'] = "/esoui/art/currency/currency_gold_32.dds";
			$logEntry['itemType'] = "-1";
			$logEntry['trait'] = "-1";
			$logEntry['quality'] = "1";
			$logEntry['value'] = "1";
		}
		else if ($logEntry['event'] == 'TelvarUpdate')
		{
			$logEntry['itemLink'] = "__telvar";
			$logEntry['itemName'] = "Telvar";
			$logEntry['itemId'] = "-201";
			$logEntry['icon'] = "/esoui/art/currency/currency_telvar_32.dds";
			$logEntry['itemType'] = "-1";
			$logEntry['trait'] = "-1";
			$logEntry['quality'] = "1";
			$logEntry['value'] = "-1";
		}
		
		if ($npcName == "footlocker" && $this->currentUser['__lastFootlockerOpenedName'] != null)
		{	
			$npcName = $this->currentUser['__lastFootlockerOpenedName'];
		}
		
		$lootSourceRecord = $this->FindLootSource($npcName);
		
		if ($lootSourceRecord == null)
		{
			$logEntry['name'] = $npcName;
			$lootSourceRecord = $this->CreateLootSource($logEntry);
			if ($lootSourceRecord == null) return false;
			
			//$this->CheckLocation("lootSource", $npcName, $logEntry, array('lootSourceId' => $lootSourceRecord['id']));
		}
				
		$lootRecord = $this->FindNPCLoot($lootSourceRecord['id'], $zone, $logEntry['itemLink']);
		
		if ($lootRecord == null)
		{
			$itemData = $this->FindMinedItemData($logEntry['itemLink']);
			
			if ($itemData)
			{
				$logEntry['itemName'] = MakeEsoTitleCaseName($itemData['name']);
				$logEntry['icon'] = $itemData['icon'];
				$logEntry['quality'] = $itemData['quality'];
				$logEntry['itemType'] = $itemData['type'];
				$logEntry['trait'] = $itemData['trait'];
				$logEntry['value'] = $itemData['value'];
			}
			else if ($itemData['itemName'] = "")
			{
				$logEntry['itemName'] = $logEntry['itemLink'];
			}
			
			$lootRecord = $this->CreateNPCLoot($lootSourceRecord, $zone, $logEntry);
			if ($lootRecord == null) return false;
		}
		else
		{
			if ($qnt > 0) $lootRecord['qnt'] += $qnt;
			$lootRecord['count'] += 1;
			
			$this->SaveNPCLoot($lootRecord);
		}
		
		$diffTime = $gameTime - $this->currentUser['__lastLootGainedGameTime'];
		
		if ($this->currentUser['__lastLootGainedTarget'] != $npcName || $diffTime > 1000 || $this->currentUser['__lastLootGainedGameTime'] <= 0)
		{
			$lootSourceRecord['count']++;
			$this->SaveLootSource($lootSourceRecord);
			//$this->log("Updating NPC Totals: $npcName, $diffTime, $gameTime, {$this->currentUser['__lastLootGainedTarget']}, {$this->currentUser['__lastLootGainedGameTime']}");
			
			$lootRecord = $this->FindNPCLoot($lootSourceRecord['id'], $zone, "__totalCount");
			
			if ($lootRecord == null)
			{
				$lootRecord = $this->CreateNPCLoot($lootSourceRecord, $zone, $logEntry);
				if ($lootRecord == null) return false;
				
				$lootRecord['itemLink'] = "__totalCount";
				$lootRecord['itemName'] = "__totalCount";
				$lootRecord['itemId'] = -1;
				$lootRecord['qnt'] = 0;
				$lootRecord['__dirty'] = true;
			}
			else
			{
				$lootRecord['count'] += 1;
				$lootRecord['__dirty'] = true;
			}
			
			$this->SaveNPCLoot($lootRecord);
			
			$lootRecord = $this->FindNPCLoot($lootSourceRecord['id'], "", "__totalCount");
				
			if ($lootRecord == null)
			{
				$lootRecord = $this->CreateNPCLoot($lootSourceRecord, "", $logEntry);
				if ($lootRecord == null) return false;
			
				$lootRecord['itemLink'] = "__totalCount";
				$lootRecord['itemName'] = "__totalCount";
				$lootRecord['itemId'] = -1;
				$lootRecord['qnt'] = 0;
				$lootRecord['__dirty'] = true;
			}
			else
			{
				$lootRecord['count'] += 1;
				$lootRecord['__dirty'] = true;
			}
				
			$this->SaveNPCLoot($lootRecord);
		}
		
		$this->currentUser['__lastLootGainedTarget'] = $npcName;
		$this->currentUser['__lastLootGainedGameTime'] = $gameTime;
				
		return true;
	}
	
	
	public function OnMoneyGained($logEntry)
	{
		return $this->UpdateNpcLoot($logEntry);
	}
	
	
	public function OnOpenFootlocker($logEntry)
	{
		$gameTime = (int) $logEntry['gameTime'];
		$footLockerName = MakeEsoTitleCaseName($logEntry['itemName']);
		
		$this->currentUser['__lastLootGainedGameTime'] = 0;
		$this->currentUser['__lastLootGainedTarget'] = "";
		
		$this->currentUser['__lastFootlockerOpenedName'] = $footLockerName;
		$this->currentUser['__lastFootlockerOpenedGameTime'] = $gameTime;
		
		return true;
	}
	
	
	public function OnTelvarUpdate($logEntry)
	{
		if ($logEntry['reason'] == 0 && $logEntry['qnt'] > 0)
		{
			return $this->UpdateNpcLoot($logEntry);
		}
		
		return true;
	}
	
	
	public function OnMailItem($logEntry)
	{
		static $ROMAN_NUMBERS = array(
				0 => "",		
				1 => " I",
				2 => " II",
				3 => " III",
				4 => " IV",
				5 => " V",
				6 => " VI",
				7 => " VII",
				8 => " VIII",
				9 => " IX",
				10 => " X",
		);
		
		if ($logEntry['tradeType'] == null) return true;
		if ($logEntry['tradeType'] <= 0) return true;
		if ($logEntry['subject'] == null) return true;
		if ($logEntry['subject'] == "") return true;
		
		$logEntry['zone'] = "";
		$suffix = $ROMAN_NUMBERS[intval($logEntry['craftLevel'])];
		
		if ($suffix != null)
			$logEntry['lastTarget'] = $logEntry['subject'] . $suffix;
		else
			$logEntry['lastTarget'] = $logEntry['subject'];
		
	 	return $this->UpdateNpcLoot($logEntry);	
	}
	
	
	public function ParseItemLink ($itemLink)
	{
			/* Quick check for quest items */
		if ($itemLink[0] != '|' || $itemLink[1] != 'H')
		{
			$matches['name'] = $itemLink;
			$matches['error'] = true;
			return $matches;
		}
		
			//|H0:item:ID:SUBTYPE:LEVEL:ENCHANTID:ENCHANTSUBTYPE:ENCHANTLEVEL:0:0:0:0:0:0:0:0:0:STYLE:CRAFTED:BOUND:CHARGES:POTIONEFFECT|hNAME|h
			//(?:\:(?P<extradata>[0-9]*))?
		//$result = preg_match('/\|H(?P<color>[A-Za-z0-9]*)\:item\:(?P<itemId>[0-9]*)\:(?P<subtype>[0-9]*)\:(?P<level>[0-9]*)\:(?P<enchantId>[0-9]*)\:(?P<enchantSubtype>[0-9]*)\:(?P<enchantLevel>[0-9]*)\:(.*?)\:(?P<style>[0-9]*)\:(?P<crafted>[0-9]*)\:(?P<bound>[0-9]*)\:(?P<stolen>[0-9]*)\:(?P<charges>[0-9]*)\:(?P<potionData>[0-9]*)\|h(?P<name>[^|\^]*)(?P<nameCode>.*?)\|h/', $itemLink, $matches);
		$matches = ParseEsoItemLink($itemLink);
		
		if (!$matches) 
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
		
		$npcRecord['name'] = $logEntry['name'];
		$npcRecord['gender'] = $logEntry['gender'];
		$npcRecord['level'] = $logEntry['level'];
		$npcRecord['difficulty'] = $logEntry['difficulty'];
		$npcRecord['count'] = 0;
		$npcRecord['__isNew'] = true;
		
		if ($logEntry['ppClassString'] != "") $npcRecord['ppClass']      = $logEntry['ppClassString'];
		if ($logEntry['ppDifficulty']  != "") $npcRecord['ppDifficulty'] = $logEntry['ppDifficulty'];
		if ($logEntry['reaction']      != "") $npcRecord['reaction']     = $logEntry['reaction'];
		
		++$this->currentUser['newCount'];
		$this->currentUser['__dirty'] = true;
		
		$result = $this->SaveNPC($npcRecord);
		if (!$result) return null;
		
		return $npcRecord;
	}
	
	
	public function UpdateNpcZone ($npcId, $zone, $isNewLocation)
	{
		if ($npcId === null || $npcId <= 0) return false;
		if ($zone === null || $zone == "") return false;
		
		$safeZone = $this->db->real_escape_string($zone);
		
		if ($isNewLocation)
		{
			$query = "INSERT INTO npcLocations(npcId, zone, locCount) VALUES($npcId, '$safeZone', 1) ON DUPLICATE KEY UPDATE locCount = locCount + 1;";
		}
		else
		{
			$query = "INSERT IGNORE INTO npcLocations(npcId, zone, locCount) VALUES($npcId, '$safeZone', 1);";
		}		
		
		$this->lastQuery = $query;
			
		$result = $this->db->query($query);
		if ($result === false) return $this->reportError("Failed to insert/update npcLocations record!");
		
		return true;
	}
	
	
	public function CreateLootSource ($logEntry)
	{
		$lootSourceRecord = $this->createNewRecord(self::$LOOTSOURCE_FIELDS);
		
		$lootSourceRecord['name'] = $logEntry['name'];
		$lootSourceRecord['count'] = 0;
		$lootSourceRecord['__isNew'] = true;
		
		++$this->currentUser['newCount'];
		$this->currentUser['__dirty'] = true;
		
		$result = $this->SaveLootSource($lootSourceRecord);
		if (!$result) return null;
		
		return $lootSourceRecord;
	}
	
	
	public function CreateNPCLoot ($lootSource, $zone, $logEntry)
	{
		$lootRecord = $this->createNewRecord(self::$NPCLOOT_FIELDS);
	
		$lootRecord['zone'] = $zone;
		$lootRecord['lootSourceId'] = $lootSource['id'];
		$lootRecord['itemName'] = $logEntry['itemName'];
		$lootRecord['itemLink'] = $logEntry['itemLink'];
		$lootRecord['quality'] = -1;
		$lootRecord['itemType'] = -1;
		$lootRecord['trait'] = -1;
		$lootRecord['value'] = -1;
		$lootRecord['icon'] = "";
		
		if ($logEntry['quality']) $lootRecord['quality'] = $logEntry['quality'];
		if ($logEntry['itemType']) $lootRecord['itemType'] = $logEntry['itemType'];
		if ($logEntry['trait']) $lootRecord['trait'] = $logEntry['trait'];
		if ($logEntry['icon']) $lootRecord['icon'] = $logEntry['icon'];
		
		$lootRecord['itemId'] = 0;
		if ($logEntry['itemId'] != null) $lootRecord['itemId'] = $logEntry['itemId'];
		$itemLinkMatches = ParseEsoItemLink($logEntry['itemLink']);
		if ($itemLinkMatches && $itemLinkMatches['itemId']) $lootRecord['itemId'] = $itemLinkMatches['itemId'];
		 
		$lootRecord['count'] = 1;
		$lootRecord['qnt'] = 0;
		$qnt = intval($logEntry['qnt']);
		if ($qnt > 0) $lootRecord['qnt'] = $qnt;
		
		$lootRecord['__isNew'] = true;
	
		++$this->currentUser['newCount'];
		$this->currentUser['__dirty'] = true;
	
		$result = $this->SaveNPCLoot($lootRecord);
		if (!$result) return null;
	
		return $lootRecord;
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
		//event{Recipe::Ingredient}  icon{/esoui/art/icons/crafting_cloth_pollen.dds}  qnt{1}  name{shornhelm grains^p}  value{0}  quality{1}  
		//itemLink{|HFFFFFF:item:33767:0:0:0:0:0:0:0:0:0:0:0:0:0:0:0:0:0:0:0|hshornhelm grains^p|h}
		
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
	
	
	public function CreateOldQuest ($name, $objective, $logEntry)
	{
		$questRecord = $this->createNewRecord(self::$OLDQUEST_FIELDS);
		
		$questRecord['name'] = $name;
		$questRecord['objective'] = $objective;
		$questRecord['locationId'] = -1;
		$questRecord['__isNew'] = true;
		
		++$this->currentUser['newCount'];
		$this->currentUser['__dirty'] = true;
		
		$result = $this->SaveOldQuest($questRecord);
		if (!$result) return null;
		
		$questLocation = $this->CreateLocation("oldquest", $name, $logEntry, array('questId' => $questRecord['id']));
		$result = $this->SaveLocation($questLocation);
		if (!$result) return null;
		
		$questRecord['locationId'] = $questLocation['id'];
		$result = $this->SaveOldQuest($questRecord);
		if (!$result) return null;
		
		return $questRecord;
	}
	
	
	public function CreateOldQuestStage ($questRecord, $logEntry)
	{
		$questStageRecord = $this->createNewRecord(self::$OLDQUESTSTAGE_FIELDS);
		
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
	
		$result = $this->SaveOldQuestStage($questStageRecord);
		if (!$result) return null;
	
		$questLocation = $this->CreateLocation("oldquest", $questRecord['name'], $logEntry, array('questId' => $questRecord['id'], 'questStageId' => $questStageRecord['id']));
		$result = $this->SaveLocation($questLocation);
		if (!$result) return null;
	
		$questStageRecord['locationId'] = $questLocation['id'];
		$result = $this->SaveOldQuestStage($questStageRecord);
		if (!$result) return null;
	
		return $questStageRecord;
	}
	
	
	public function CreateQuest ($name, $logEntry)
	{
		$questRecord = $this->createNewRecord(self::$QUEST_FIELDS);
	
		$questRecord['name'] = $name;
		$questRecord['locationId'] = -1;
		$questRecord['level'] = $logEntry['level'];
		$questRecord['type'] = $logEntry['type'];
		$questRecord['repeatType'] = $logEntry['repeatType'];
		$questRecord['displayType'] = $logEntry['displayType'];
		$questRecord['backgroundText'] = $logEntry['bgText'];
		$questRecord['poiIndex'] = $logEntry['poiIndex'];
		$questRecord['goalText'] = $logEntry['goal'];
		$questRecord['objective'] = $logEntry['objective'];
		$questRecord['confirmText'] = $logEntry['confirm'];
		$questRecord['declineText'] = $logEntry['decline'];
		$questRecord['endDialogText'] = $logEntry['endDialog'];
		$questRecord['endBackgroundText'] = $logEntry['endBgText'];
		$questRecord['endJournalText'] = $logEntry['endJournalText'];
		$questRecord['isShareable'] = $logEntry['shareable'];
		$questRecord['numTools'] = $logEntry['numTools'];
		$questRecord['hasTimer'] = $logEntry['timerVisible'];
		$questRecord['timerCaption'] = $logEntry['timerCaption'];
		$questRecord['timerDuration'] = floatval($logEntry['timerEnd']) - floatval($logEntry['timerStart']);
		$questRecord['numSteps'] = $logEntry['numSteps'];
		$questRecord['numRewards'] = $logEntry['numRewards'];
		$questRecord['zone'] = $logEntry['zone'];
		if ($logEntry['questZone'] !== null) $questRecord['zone'] = $logEntry['questZone']; 
		$questRecord['count'] = 0;
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
	
	
	public function CreateQuestStep ($questRecord, $logEntry)
	{
		$questStageRecord = $this->createNewRecord(self::$QUESTSTEP_FIELDS);
	
		$questStageRecord['locationId'] = -1;
		$questStageRecord['questId'] = $questRecord['id'];
		$questStageRecord['stageIndex'] = $logEntry['stageIndex'];
		$questStageRecord['stepIndex'] = $logEntry['step'];
		$questStageRecord['text'] = $logEntry['text'];
		$questStageRecord['type'] = $logEntry['stepType'];
		$questStageRecord['overrideText'] = $logEntry['overrideText'];
		$questStageRecord['count'] = 0;
		$questStageRecord['uniqueId'] = $logEntry['uniqueId'];
		
		if ($logEntry['visible'] == null)
			$questStageRecord['visibility'] = -1;
		else 
			$questStageRecord['visibility'] = $logEntry['visible'];
		
		$questStageRecord['numConditions'] = $logEntry['numCond'];
		$questStageRecord['__isNew'] = true;
	
		++$this->currentUser['newCount'];
		$this->currentUser['__dirty'] = true;
	
		$result = $this->SaveQuestStep($questStageRecord);
		if (!$result) return null;
		
		$questLocation = $this->CreateLocation("quest", $questRecord['name'], $logEntry, array('questId' => $questRecord['id'], 'questStageId' => $questStageRecord['id']));
		$result = $this->SaveLocation($questLocation);
		if (!$result) return null;
		
		$questStageRecord['locationId'] = $questLocation['id'];
		$result = $this->SaveQuestStep($questStageRecord);
		if (!$result) return null;
	
		return $questStageRecord;
	}
	
	
	public function CreateQuestCondition ($questRecord, $stepRecord, $logEntry)
	{
		$questCondRecord = $this->createNewRecord(self::$QUESTCONDITION_FIELDS);
	
		$questCondRecord['questId'] = $questRecord['id'];
		$questCondRecord['questStepId'] = $stepRecord['id'];
				
		$questCondRecord['stageIndex'] = $logEntry['stageIndex'];
		$questCondRecord['stepIndex'] = $logEntry['step'];
		$questCondRecord['conditionIndex'] = $logEntry['condition'];
		$questCondRecord['type1'] = $logEntry['condType'];
		$questCondRecord['type2'] = $logEntry['condType'];
		$questCondRecord['text'] = preg_replace("#:[\xC2\xA0\s]*[0-9]+[\xC2\xA0\s]*/[\xC2\xA0\s]*[0-9]+[\xC2\xA0\s]*#", ":", $logEntry['text']);
		$questCondRecord['maxValue'] = $logEntry['maxValue'];
		$questCondRecord['isShared'] = $logEntry['isShared'];
		$questCondRecord['isFail'] = $logEntry['isFail'];
		$questCondRecord['isComplete'] = $logEntry['isComplete'];
		$questCondRecord['isShared'] = $logEntry['isShared'];
		$questCondRecord['isVisible'] = $logEntry['isVisible'];
		$questCondRecord['count'] = 0;
		$questCondRecord['uniqueId'] = $logEntry['uniqueId'];
		$questCondRecord['__isNew'] = true;
		
		++$this->currentUser['newCount'];
		$this->currentUser['__dirty'] = true;
		
		$result = $this->SaveQuestCondition($questCondRecord);
		if (!$result) return null;
	
		return $questCondRecord;
	}
	
	
	public function CreateQuestReward ($questRecord, $logEntry)
	{
		$rewardRecord = $this->createNewRecord(self::$QUESTREWARD_FIELDS);
	
		$rewardRecord['questId'] = $questRecord['id'];
		$rewardRecord['type'] = $logEntry['type'];
		$rewardRecord['name'] = $logEntry['name'];
		$rewardRecord['quantity'] = $logEntry['count'];
		$rewardRecord['icon'] = $logEntry['icon'];
		$rewardRecord['quality'] = $logEntry['quality'];
		$rewardRecord['itemType'] = $logEntry['itemType'];
		$rewardRecord['itemId'] = $logEntry['itemId'];
		$rewardRecord['collectId'] = $logEntry['collectId'];
		$rewardRecord['uniqueId'] = $logEntry['uniqueId'];
		$rewardRecord['count'] = 0;
		$rewardRecord['__isNew'] = true;
	
		++$this->currentUser['newCount'];
		$this->currentUser['__dirty'] = true;
	
		$result = $this->SaveQuestReward($rewardRecord);
		if (!$result) return null;
	
		return $rewardRecord;
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
		$questItemRecord['duration'] = -1;
		$questItemRecord['count'] = -1;
		
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
	
	
	public function CreateOldQuestOfferStage ($questRecord, $logEntry)
	{
		$questStageRecord = $this->createNewRecord(self::$OLDQUESTSTAGE_FIELDS);
	
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
	
		$result = $this->SaveOldQuestStage($questStageRecord);
		if (!$result) return null;
	
		$questLocation = $this->CreateLocation("oldquest", $questRecord['name'], $logEntry, array('questId' => $questRecord['id'], 'questStageId' => $questStageRecord['id']));
		$result = $this->SaveLocation($questLocation);
		if (!$result) return null;
	
		$questStageRecord['locationId'] = $questLocation['id'];
		$result = $this->SaveOldQuestStage($questStageRecord);
		if (!$result) return null;
	
		return $questStageRecord;
	}
	
	
	public function OnQuestComplete ($logEntry)
	{
		$questRecord = $this->FindQuest($logEntry['quest']);
		if ($questRecord == null) return $this->reportError("No quest found matching '{$logEntry['quest']}' in quest complete event!");
		
		if ($logEntry['xp'] == null) return true;
		
		$rewardRecord = $this->FindQuestReward($questRecord['id'], -1, "Experience");
		
		if ($rewardRecord == null)
		{
			$rewardRecord = $this->CreateQuestReward($questRecord, $logEntry);
			if ($rewardRecord == null) return false;
		}
		
		$rewardRecord['name'] = "Experience";
		$rewardRecord['type'] = -1;
		$rewardRecord['quantity'] = $logEntry['xp'];
		$rewardRecord['icon'] = '';
		$rewardRecord['quality'] = 0;
		$rewardRecord['itemType'] = -1;
		$rewardRecord['itemId'] = 0;
		$rewardRecord['collectId'] = 0;
		$rewardRecord['uniqueId'] = $logEntry['uniqueId'];
		$rewardRecord['count'] += 1;
		$rewardRecord['__dirty'] = true;
		
		$result = $this->SaveQuestReward($rewardRecord);
		if (!$result) return false;
		
		return true;
	}
	
	
	public function OnQuestAdded ($logEntry)
	{
		//event{QuestAdded}  quest{A Brush With Death}  objective{}  
		//y{0.64464473724365}  zone{Mines of Khuras}  x{0.25603923201561}
		//gameTime{2450267}  timeStamp{4743643875908780032}  userName{...}  ipAddress{...}  logTime{1396487058}  end{}
		
		$questRecord = $this->FindOldQuest($logEntry['quest']);
		
		if ($questRecord == null)
		{
			$questRecord = $this->CreateOldQuest($logEntry['quest'], $logEntry['objective'], $logEntry);
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
				//$this->log("\tSaving QuestOffer Data (" . $questRecord['name'] . ")");
				
				$questStageRecord = $this->FindOldQuestStageByType($questId, -123);
				if ($questStageRecord != null) return true;
				
				$questStageRecord = $this->CreateOldQuestOfferStage($questRecord, $questOfferData);
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
				
		$logEntry['itemLink'] = preg_replace("#\|h([^|]+)\|h#", "|h|h", $logEntry['itemLink']);
		
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
		$questItemRecord['count'] += 1;
		$questItemRecord['__dirty'] = true;
		
		if ($logEntry['duration']       != null) $questItemRecord['duration'] = $logEntry['duration'];
		if ($logEntry['stepIndex']      != null) $questItemRecord['stepIndex'] = $logEntry['stepIndex'];
		if ($logEntry['conditionIndex'] != null) $questItemRecord['conditionIndex'] = $logEntry['conditionIndex'];
		if ($logEntry['toolIndex']      != null) $questItemRecord['stepIndex'] = $logEntry['toolIndex'];
		
		$questRecord = $this->FindQuest($logEntry['questName']);
		if ($questRecord != null) $questItemRecord['questId'] = $questRecord['id'];
		
		$result = $this->SaveQuestItem($questItemRecord);
		if (!$result) return false;
		
		return true;
	}
	
	
	public function OnQuestStart ($logEntry)
	{
		$questRecord = $this->FindQuest($logEntry['quest']);
		
		if ($questRecord == null)
		{
			$questRecord = $this->CreateQuest($logEntry['quest'], $logEntry);
			if ($questRecord == null) return false;
			return true;
		}
		
		$questRecord['level'] = $logEntry['level'];
		$questRecord['type'] = $logEntry['type'];
		$questRecord['repeatType'] = $logEntry['repeatType'];
		$questRecord['displayType'] = $logEntry['displayType'];
		$questRecord['backgroundText'] = $logEntry['bgText'];
		$questRecord['poiIndex'] = $logEntry['poiIndex'];
		$questRecord['objective'] = $logEntry['objective'];
				
		if ($logEntry['goal']) $questRecord['goalText'] = $logEntry['goal'];
		if ($logEntry['confirm']) $questRecord['confirmText'] = $logEntry['confirm'];
		if ($logEntry['decline']) $questRecord['declineText'] = $logEntry['decline'];
		if ($logEntry['endDialog']) $questRecord['endDialogText'] = $logEntry['endDialog'];
		if ($logEntry['endBgText']) $questRecord['endBackgroundText'] = $logEntry['endBgText'];
		if ($logEntry['endJournalText']) $questRecord['endJournalText'] = $logEntry['endJournalText'];
		if ($logEntry['questZone'] !== null) $questRecord['zone'] = $logEntry['questZone'];
		
		$questRecord['isShareable'] = $logEntry['shareable'];
		$questRecord['numTools'] = $logEntry['numTools'];
		$questRecord['hasTimer'] = $logEntry['timerVisible'];
		$questRecord['timerCaption'] = $logEntry['timerCaption'];
		$questRecord['timerDuration'] = floatval($logEntry['timerEnd']) - floatval($logEntry['timerStart']);
		$questRecord['numSteps'] = $logEntry['numSteps'];
		$questRecord['numRewards'] = $logEntry['numRewards'];
		$questRecord['count'] += 1;
		$questRecord['__dirty'] = true;
		
		$locationId = $this->CheckLocationId("quest", $questRecord['name'], $logEntry, array('questId' => $questRecord['id']));
		
		if ($locationId > 0)
		{
			$questRecord['locationId'] = $locationId;
			$questRecord['__dirty'] = true;
		}
		
		if ($questRecord['__dirty'])
		{
			$result = $this->SaveQuest($questRecord);
			if (!$result) return false;
		}
		
		return true;
	}
	
	
	public function OnQuestStep ($logEntry)
	{
		if ($logEntry['stageIndex'] == null || $logEntry['stageIndex'] <= 0) return true;
		
		$questRecord = $this->FindQuest($logEntry['quest']);
		if ($questRecord == null) return false;
		
		$questStageRecord = $this->FindQuestStep($questRecord['id'], $logEntry['stageIndex'], $logEntry['step']);
		
		if ($questStageRecord == null)
		{
			$questStageRecord = $this->CreateQuestStep($questRecord, $logEntry);
			if ($questStageRecord == null) return false;
			return true;
		}
		
		$questStageRecord['stepIndex'] = $logEntry['step'];
		$questStageRecord['type'] = $logEntry['stepType'];
		$questStageRecord['overrideText'] = $logEntry['overrideText'];
		$questStageRecord['count'] += 1;
		$questStageRecord['uniqueId'] = $logEntry['uniqueId'];
		
		if ($logEntry['visible'] == null)
			$questStageRecord['visibility'] = -1;
		else 
			$questStageRecord['visibility'] = $logEntry['visible'];
		
		$questStageRecord['numConditions'] = $logEntry['numCond'];
		$questStageRecord['__dirty'] = true;
				
		$locationId = $this->CheckLocationId("quest", $questRecord['name'], $logEntry, array('questId' => $questRecord['id'], 'questStageId' => $questStageRecord['id']));
		
		if ($locationId > 0)
		{
			$questStageRecord['locationId'] = $locationId;
			$questStageRecord['__dirty'] = true;
		}
		
		if ($questStageRecord['__dirty'])
		{
			$result = $this->SaveQuestStep($questStageRecord);
			if (!$result) return false;
		}
		
		return true;
	}
	
	
	public function OnQuestCondition ($logEntry)
	{
		if ($logEntry['stageIndex'] == null || $logEntry['stageIndex'] <= 0) return true;
		
		$questRecord = $this->FindQuest($logEntry['quest']);
		if ($questRecord == null) return $this->reportError("Failed to find matching quest for {$logEntry['quest']} in condition!");
		
		$questStageRecord = $this->FindQuestStep($questRecord['id'], $logEntry['stageIndex'], $logEntry['step']);
		if ($questStageRecord == null) return $this->reportError("Failed to find matching quest step for {$logEntry['quest']}:{$logEntry['stageIndex']}:{$logEntry['step']}!");
		
		$questCondRecord = $this->FindQuestCondition($questRecord['id'], $logEntry['stageIndex'], $logEntry['step'], $logEntry['condition'], $logEntry['text']);
				
		if ($questCondRecord == null)
		{
			$questCondRecord = $this->CreateQuestCondition($questRecord, $questStageRecord, $logEntry);
			if ($questCondRecord == null) return false;
			return true;
		}
		
		$questCondRecord['type1'] = $logEntry['condType'];
		$questCondRecord['type2'] = $logEntry['condType'];
		$questCondRecord['maxValue'] = $logEntry['maxValue'];
		$questCondRecord['isShared'] = $logEntry['isShared'];
		$questCondRecord['isFail'] = $logEntry['isFail'];
		$questCondRecord['isComplete'] = $logEntry['isComplete'];
		$questCondRecord['isShared'] = $logEntry['isShared'];
		$questCondRecord['isVisible'] = $logEntry['isVisible'];
		$questCondRecord['count'] += 1;
		$questCondRecord['uniqueId'] = $logEntry['uniqueId'];
		$questCondRecord['__dirty'] = true;
		
		$result = $this->SaveQuestCondition($questCondRecord);
		if (!$result) return false;
		
		return true;
	}
	
	
	public function OnQuestReward ($logEntry)
	{
		$questRecord = $this->FindQuest($logEntry['quest']);
		if ($questRecord == null) return $this->reportError("Failed to find matching quest for {$logEntry['quest']} in reward!");
		
		if ($logEntry['name'] == "" && $logEntry['type'] == 1) $logEntry['name'] = "Gold";
		
		$rewardRecord = $this->FindQuestReward($questRecord['id'], $logEntry['type'], $logEntry['name']);
		
		if ($rewardRecord == null)
		{
			$rewardRecord = $this->CreateQuestReward($questRecord, $logEntry);
			if ($rewardRecord == null) return false;
			return true;
		}
		
		$rewardRecord['name'] = $logEntry['name'];
		$rewardRecord['quantity'] = $logEntry['count'];
		$rewardRecord['icon'] = $logEntry['icon'];
		$rewardRecord['quality'] = $logEntry['quality'];
		$rewardRecord['itemType'] = $logEntry['itemType'];
		$rewardRecord['itemId'] = $logEntry['itemId'];
		$rewardRecord['collectId'] = $logEntry['collectId'];
		$rewardRecord['count'] += 1;
		$rewardRecord['uniqueId'] = $logEntry['uniqueId'];
		$rewardRecord['__dirty'] = true;
		
		$result = $this->SaveQuestReward($rewardRecord);
		if (!$result) return false;
		
		return true;
	}
	
	
	public function OnQuestChanged ($logEntry)
	{
		//event{QuestChanged}  overrideText{Talk to Grahla}  quest{The Nameless Soldier}  isHidden{false}  isFail{false}  isPushed{false}
		//isCondComplete{true}  isComplete{true} condition{Find Alana}  condType{9}  condVal{1}  condMaxVal{1}
		//y{0.48894619941711}  zone{Glenumbra}  x{0.51565104722977}  
		//timeStamp{4743643893159952384}  gameTime{456809}  userName{...}  ipAddress{...}  logTime{1396487061}  end{}
		
		$questRecord = $this->FindOldQuest($logEntry['quest']);
		
		if ($questRecord == null)
		{
			$questRecord = $this->CreateOldQuest($logEntry['quest'], $logEntry['objective'], $logEntry);
			if ($questRecord == null) return false;
		}
		
		$questId = $questRecord['id'];
		
		$questStageRecord = $this->FindOldQuestStage($questId, $logEntry['condition']);
		if ($questStageRecord != null) return true;
		
		$questStageRecord = $this->CreateOldQuestStage($questRecord, $logEntry);
		if ($questStageRecord == null) return false;
		
		return true;
	}
	
	
	public function OnQuestAdvanced ($logEntry)
	{
		//event{QuestAdvanced}  isPushed{false}  quest{The Nameless Soldier}  isComplete{true}  mainStepChanged{true}  
		//y{0.48894619941711}  zone{Glenumbra}  x{0.51565104722977}  timeStamp{4743643893159952384}  gameTime{456839} 
		//userName{...}  ipAddress{...}  logTime{1396487061}  end{}
		
		$questRecord = $this->FindQuest($logEntry['quest']);
		if ($questRecord == null) return $this->reportError("Failed to find matching quest for '{$logEntry['quest']}' in advanced quest step!");
		
		if ($logEntry['goal']) 
		{
			$questRecord['goalText'] = $logEntry['goal'];
			$questRecord["__dirty"] = true;
		}
		
		if ($logEntry['confirm']) 
		{
			$questRecord['confirmText'] = $logEntry['confirm'];
			$questRecord["__dirty"] = true;
		}
		
		if ($logEntry['decline']) 
		{
			$questRecord['declineText'] = $logEntry['decline'];
			$questRecord["__dirty"] = true;
		}
		
		if ($logEntry['endDialog']) 
		{
			$questRecord['endDialogText'] = $logEntry['endDialog'];
			$questRecord["__dirty"] = true;
		}
		
		if ($logEntry['endBgText']) 
		{
			$questRecord['endBackgroundText'] = $logEntry['endBgText'];
			$questRecord["__dirty"] = true;
		}
		
		if ($logEntry['endJournalText']) 
		{
			$questRecord['endJournalText'] = $logEntry['endJournalText'];
			$questRecord["__dirty"] = true;
		}
		
		if ($questRecord["__dirty"]) $this->SaveQuest($questRecord);
		
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
		
		++$this->dbReadCount;
		
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
		
		++$this->dbReadCount;
		
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
		
		++$this->dbReadCount;
		
		if ($result->num_rows == 0) return null;
		
		$row = $result->fetch_assoc();
		return $this->createRecordFromRow($row, self::$ITEM_FIELDS);
	}
	
	
	public function FindOldQuest ($name)
	{
		$safeName = $this->db->real_escape_string($name);
		$query = "SELECT * FROM oldQuest WHERE name='$safeName';";
		$this->lastQuery = $query;
		
		$result = $this->db->query($query);
		
		if ($result === false)
		{
			$this->reportError("Failed to retrieve old quest!");
			return null;
		}
		
		++$this->dbReadCount;
		
		if ($result->num_rows == 0) return null;
		
		$row = $result->fetch_assoc();
		return $this->createRecordFromRow($row, self::$OLDQUEST_FIELDS);
	}
	
	
	public function FindOldQuestStage ($questId, $objective)
	{
		$safeObj = $this->db->real_escape_string($objective);
		$safeId = (int) $questId;
		$query = "SELECT * FROM oldQuestStage WHERE questId=$safeId AND objective='$safeObj';";
		$this->lastQuery = $query;
		
		$result = $this->db->query($query);
		
		if ($result === false)
		{
			$this->reportError("Failed to retrieve old quest stage!");
			return null;
		}

		++$this->dbReadCount;
		
		if ($result->num_rows == 0) return null;
		
		$row = $result->fetch_assoc();
		return $this->createRecordFromRow($row, self::$OLDQUESTSTAGE_FIELDS);
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
		
		++$this->dbReadCount;
	
		if ($result->num_rows == 0) 
		{
			return null;
		}
	
		$row = $result->fetch_assoc();
		return $this->createRecordFromRow($row, self::$QUEST_FIELDS);
	}
	
	
	public function FindQuestStep ($questId, $stageIndex, $stepIndex)
	{
		$safeIndex = (int) $stepIndex;
		$safeStage = (int) $stageIndex;
		$safeId = (int) $questId;
		$query = "SELECT * FROM questStep WHERE questId=$safeId AND stageIndex=$safeStage and stepIndex=$safeIndex;";
		$this->lastQuery = $query;
	
		$result = $this->db->query($query);
	
		if ($result === false)
		{
			$this->reportError("Failed to retrieve quest step!");
			return null;
		}
		
		++$this->dbReadCount;
	
		if ($result->num_rows == 0) return null;
	
		$row = $result->fetch_assoc();
		return $this->createRecordFromRow($row, self::$QUESTSTEP_FIELDS);
	}
	
	
	public function FindQuestReward ($questId, $type, $rewardName)
	{
		$safeId = (int) $questId;
		$safeType = (int) $type;
		$safeName = $this->db->real_escape_string($rewardName);
		$query = "SELECT * FROM questReward WHERE questId=$safeId AND type=$safeType AND name='$safeName';";
		$this->lastQuery = $query;
		
		$result = $this->db->query($query);
		
		if ($result === false)
		{
			$this->reportError("Failed to retrieve quest reward!");
			return null;
		}
		
		++$this->dbReadCount;
		
		if ($result->num_rows == 0) return null;
		
		$row = $result->fetch_assoc();
		return $this->createRecordFromRow($row, self::$QUESTREWARD_FIELDS);
	}
	
	
	public function FindQuestCondition ($questId, $stageIndex, $stepIndex, $conditionIndex, $text)
	{
		$safeIndex = (int) $stepIndex;
		$safeCond = (int) $conditionIndex;
		$safeStage = (int) $stageIndex;
		$safeId = (int) $questId;
		
		$text = preg_replace("#:[\xC2\xA0\s]*[0-9]+[\xC2\xA0\s]*/[\xC2\xA0\s]*[0-9]+[\xC2\xA0\s]*#", ":", $text);
		$safeText = $this->db->real_escape_string($text);
		
		$query = "SELECT * FROM questCondition WHERE questId=$safeId AND stageIndex=$safeStage AND stepIndex=$safeIndex AND conditionIndex=$safeCond AND text='$safeText';";
		$this->lastQuery = $query;
	
		$result = $this->db->query($query);
	
		if ($result === false)
		{
			$this->reportError("Failed to retrieve quest condition!");
			return null;
		}
		
		++$this->dbReadCount;
	
		if ($result->num_rows == 0) return null;
	
		$row = $result->fetch_assoc();
		return $this->createRecordFromRow($row, self::$QUESTCONDITION_FIELDS);
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
		
		++$this->dbReadCount;
	
		if ($result->num_rows == 0) return null;
	
		$row = $result->fetch_assoc();
		return $this->createRecordFromRow($row, self::$QUESTITEM_FIELDS);
	}
	
	
	public function FindOldQuestStageByType ($questId, $type)
	{
		$safeType = $this->db->real_escape_string($type);
		$safeId = (int) $questId;
		$query = "SELECT * FROM oldQuestStage WHERE questId=$safeId AND type=$safeType LIMIT 1;";
		$this->lastQuery = $query;
	
		$result = $this->db->query($query);
	
		if ($result === false)
		{
			$this->reportError("Failed to retrieve old quest stage by type!");
			return null;
		}
		
		++$this->dbReadCount;
	
		if ($result->num_rows == 0) return null;
	
		$row = $result->fetch_assoc();
		return $this->createRecordFromRow($row, self::$OLDQUESTSTAGE_FIELDS);
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
		
		++$this->dbReadCount;
		
		if ($result->num_rows == 0) return null;
		
		$row = $result->fetch_assoc();
		return $this->createRecordFromRow($row, self::$NPC_FIELDS);
	}
	
	
	public function FindLootSource ($name)
	{
		$safeName = $this->db->real_escape_string($name);
		$query = "SELECT * FROM lootSources WHERE name='$safeName';";
		$this->lastQuery = $query;
		
		$result = $this->db->query($query);
		
		if ($result === false)
		{
			$this->reportError("Failed to retrieve lootSource!");
			return null;
		}
		
		++$this->dbReadCount;
		
		if ($result->num_rows == 0) return null;
		
		$row = $result->fetch_assoc();
		return $this->createRecordFromRow($row, self::$LOOTSOURCE_FIELDS);
	}
	
	
	public function FindNPCLoot ($lootSourceId, $zone, $itemLink)
	{
		$safeLink = $this->db->real_escape_string($itemLink);
		$safeZone = $this->db->real_escape_string($zone);
		$safeId = (int) $lootSourceId;
		
		$query = "SELECT * FROM npcLoot WHERE lootSourceId=$safeId AND zone='$safeZone' AND itemLink='$safeLink';";
		$this->lastQuery = $query;
	
		$result = $this->db->query($query);
	
		if ($result === false)
		{
			$this->reportError("Failed to retrieve NPC loot!");
			return null;
		}
		
		++$this->dbReadCount;
	
		if ($result->num_rows == 0) return null;
	
		$row = $result->fetch_assoc();
		return $this->createRecordFromRow($row, self::$NPCLOOT_FIELDS);
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
		
		++$this->dbReadCount;
	
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
		
		++$this->dbReadCount;
	
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
		
		++$this->dbReadCount;
		
		//$this->reportError("query({$result->num_rows}): $query");
		if ($result->num_rows == 0) return null;
		
		$row = $result->fetch_assoc();
		$this->currentUser['lastLocationRecordId'] = $row['id'];
		
		return $this->createRecordFromRow($row, self::$LOCATION_FIELDS);
	}
	
	
	public function CheckLocation ($type, $name, $logEntry, $extraIds = null, &$isNew = null)
	{
		if ($this->IncrementLocationCounter($type, $name, $logEntry, $extraIds)) 
		{
			$isNew = true;
			return true;
		}
		
		$isNew = false;
		return $this->CreateLocation($type, $name, $logEntry, $extraIds) != null;
	}

	
	public function CheckLocationId ($type, $name, $logEntry, $extraIds = null)
	{
		$id = $this->IncrementLocationCounterId($type, $name, $logEntry, $extraIds);
		if ($id > 0) return $id;
		
		$location =  $this->CreateLocation($type, $name, $logEntry, $extraIds);
		if ($location == null) return 0;
		
		return $location['id'];
	}
	
	
	public function IncrementLocationCounter ($type, $name, $logEntry, $extraIds = null)
	{
		$locationRecord = $this->FindLocation($type, $logEntry['x'], $logEntry['y'], $logEntry['zone'], $extraIds);
		if ($locationRecord == null) return false;
		
		++$locationRecord['count'];
		$this->saveLocation($locationRecord);
		
		return true;
	}
	
	
	public function IncrementLocationCounterId ($type, $name, $logEntry, $extraIds = null)
	{
		$locationRecord = $this->FindLocation($type, $logEntry['x'], $logEntry['y'], $logEntry['zone'], $extraIds);
		if ($locationRecord == null) return 0;
	
		++$locationRecord['count'];
		$this->saveLocation($locationRecord);
	
		return $locationRecord['id'];
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
			if (array_key_exists('itemId',  $extraIds)) $locationRecord['itemId']  = $extraIds['itemId'];
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
		
		++$this->dbReadCount;
		
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
		$bookId = $logEntry['bookId'];
		$body = $logEntry['body'];
		$medium = (int) $logEntry['medium'];
		$bookRecord = null;
		
		if ($bookId != null) 
			$bookRecord = $this->LoadBookId($bookId);
		else if ($bookTitle != null)
			$bookRecord = $this->LoadBook($bookTitle);
		
		if ($bookRecord == null || $bookRecord === false) return $this->reportLogParseError("Missing book title or ID!");
		
		if ($bookRecord['__isNew'] === true)
		{
			$bookRecord['title'] = $bookTitle;
			$bookRecord['body'] = $body;
			$bookRecord['mediumIndex'] = $medium;
			$bookRecord['isLore'] = 0;
			$bookRecord['__dirty'] = true;
			
			if ($logEntry['categoryIndex'] != null) $bookRecord['categoryIndex'] = (int) $logEntry['categoryIndex'];
			if ($logEntry['collectionIndex'] != null) $bookRecord['collectionIndex'] = (int) $logEntry['collectionIndex'];
			if ($logEntry['bookIndex'] != null) $bookRecord['bookIndex'] = (int) $logEntry['bookIndex'];
			
			++$this->currentUser['newCount'];
			$this->currentUser['__dirty'] = true;
		}
		elseif ($bookRecord['mediumIndex'] <= 0 || $bookRecord['body'] == '' || $bookRecord['icon'] == '')
		{
			if ($bookRecord['body'] == '') $bookRecord['body'] = $body;
			$bookRecord['mediumIndex'] = $medium;
			$bookRecord['__dirty'] = true;
			
			if ($logEntry['categoryIndex'] != null) $bookRecord['categoryIndex'] = (int) $logEntry['categoryIndex'];
			if ($logEntry['collectionIndex'] != null) $bookRecord['collectionIndex'] = (int) $logEntry['collectionIndex'];
			if ($logEntry['bookIndex'] != null) $bookRecord['bookIndex'] = (int) $logEntry['bookIndex'];
			
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
		//$this->log("\tLoreBook: $bookTitle");
		
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
		else if ($bookRecord['guildIndex'] < 0 || $bookRecord['icon'] == '' || $bookRecord['icon'] == '/esoui/art/icons/icon_missing.dds' ||
				 $bookRecord['collectionIndex'] <= 0 || $bookRecord['bookIndex'] <= 0 || $bookRecord['categoryIndex'] <= 0 || $bookRecord['guildIndex'] <= 0 )
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
	
	
	public function OnMineBookStart ($logEntry)
	{
		return true;
	}
	
	
	public function OnMineBookEnd ($logEntry)
	{
		return true;
	}
	
	
	public function OnMineBookCategory ($logEntry)
	{
		return true;
	}
	
	
	public function OnMineBookCollection ($logEntry)
	{
		return true;
	}
	
	
	public function OnMineBook ($logEntry)
	{
		$bookId = $logEntry['bookId'];
		if ($bookId == null) return $this->reportLogParseError("Missing book ID!");
		
		$bookRecord = $this->LoadBookId($bookId);
		if ($bookRecord === false) return false;
		
		$bookRecord['title'] = $logEntry['title'];;
		$bookRecord['icon'] = $logEntry['icon'];
			
		if ($logEntry['medium'] > 0) $bookRecord['medium'] = $logEntry['medium'];
			
		$bookRecord['categoryIndex'] = $logEntry['categoryIndex'];
		$bookRecord['collectionIndex'] = $logEntry['collectionIndex'];
		$bookRecord['bookIndex'] = $logEntry['bookIndex'];

		$bookRecord['__dirty'] = true;
		
		++$this->currentUser['newCount'];
		$this->currentUser['__dirty'] = true;
		
		$result &= $this->SaveBook($bookRecord);
		
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
			//$this->log("\t\tFound {$logEntry['name']} skill update for book {$lastBookRecord['title']}...");
			$lastBookRecord['skill'] = $logEntry['name'];
			$this->SaveBook($lastBookRecord);
		}
		
		return true;
	}
	
	
	public function OnSkyshard ($logEntry)
	{
		//event{Skyshard}  y{0.43859297037125}  zone{Portdun Watch}  x{0.68326634168625}  lastTarget{Skyshard} 
		//timeStamp{4743645430720495616}  gameTime{56651357}  userName{Reorx}  end{}
		
		//$this->log("\t\tFound Skyshard...");
		
		return $this->CheckLocation("skyshard", "Skyshard", $logEntry, null);
	}
	
	
	public function OnFoundTreasure ($logEntry)
	{
		//event{FoundTreasure}  name{Chest}  x{0.25863909721375}  y{0.76831662654877}  lastTarget{Chest}  zone{Wayrest}  
		//gameTime{336361}  timeStamp{4743645698686189568}  userName{Reorx}  end{}  
		
		//$this->log("\t\tFound Treasure...");
		
		$result = $this->CheckLocation("treasure", $logEntry['name'], $logEntry, null);
		
		$this->currentUser['__lastTreasureFoundName'] = $logEntry['name'];
		$this->currentUser['__lastTreasureFoundGameTime'] = $logEntry['gameTime'];
				
		if ($logEntry['name'] == "Chest")
		{
			$chestRecord = $this->createNewRecord(self::$CHEST_FIELDS);
			
			$locationId = $this->currentUser['lastLocationRecordId'];
			if ($locationId == null) $locationId = 0;
			
			$chestRecord['locationId'] = (int) $locationId;
			$chestRecord['quality'] = -1;
			$chestRecord['name'] = "Chest";
			if ($logEntry['lockQuality'] > 0) $chestRecord['quality'] = $logEntry['lockQuality'];
			
			$result &= $this->saveChest($chestRecord);
			$this->currentUser['lastChestRecord'] = $chestRecord;
			
			$diff = $logEntry['gameTime'] - $this->currentUser['__lastChestFoundGameTime'];
			//$this->log("Chest Diff = $diff");
			
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
		else if ($logEntry['name'] == "Safebox")
		{
			$chestRecord = $this->createNewRecord(self::$CHEST_FIELDS);
				
			$locationId = $this->currentUser['lastLocationRecordId'];
			if ($locationId == null) $locationId = 0;
				
			$chestRecord['locationId'] = (int) $locationId;
			$chestRecord['quality'] = -1;
			$chestRecord['name'] = "Safebox";
			if ($logEntry['lockQuality'] > 0) $chestRecord['quality'] = $logEntry['lockQuality'];
				
			$result &= $this->saveChest($chestRecord);
			
			$diff = $logEntry['gameTime'] - $this->currentUser['__lastSafeboxFoundGameTime'];
				
			if ($diff >= self::TREASURE_DELTA_TIME || $diff < 0)
			{
				++$this->currentUser['safeBoxesFound'];
				$this->currentUser['__dirty'] = true;
				$this->currentUser['__lastSafeboxFoundGameTime'] = $logEntry['gameTime'];
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
		
		$this->currentUser['__lastLockPickQuality'] = $logEntry['quality'];
		$this->currentUser['__lastLockPickGameTime'] = $logEntry['gameTime'];
		
		//$this->log("\tLockPick: {$logEntry['quality']}");
		
		if (self::ONLY_PARSE_NPCLOOT_CHESTS || self::ONLY_PARSE_NPCLOOT) return true;
		
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
		
		$npcRecord['count'] += 1;
		$this->SaveNPC($npcRecord);
		
		$isNewLocation = false;
		$this->CheckLocation("npc", $name, $logEntry, array('npcId' => $npcRecord['id']), $isNewLocation);
		
		$this->UpdateNpcZone($npcRecord['id'], $logEntry['zone'], $isNewLocation);
		
		
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
			$query = "INSERT INTO itemIdCheck(itemId, `version`) VALUES($id, '$version')";
			$this->lastQuery = $query;
			
			$result = $this->db->query($query);
			if ($result === false) return $this->reportError("Failed to create itemIdCheck record!");
			
			$this->dbWriteCount++;
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
		
		if ($logEntry['type'] >= 35 && $logEntry['type'] <= 40)
		{
			$logEntry['useAbilityDesc'] = "";
		}
		
			/* Don't update set data for invalid types */
		if (array_key_exists('type', $logEntry) && $logEntry['type'] != 1 && $logEntry['type'] != 2 && $logEntry['type'] != 18)
		{
			$logEntry['setBonus1'] = "";
			$logEntry['setBonus2'] = "";
			$logEntry['setBonus3'] = "";
			$logEntry['setBonus4'] = "";
			$logEntry['setBonus5'] = "";
			$logEntry['setBonus6'] = "";
			
			$logEntry['setDesc1'] = "";
			$logEntry['setDesc2'] = "";
			$logEntry['setDesc3'] = "";
			$logEntry['setDesc4'] = "";
			$logEntry['setDesc5'] = "";
			
			$logEntry['setName'] = "";
			$logEntry['setBonusCount'] = "";
			$logEntry['setMaxCount'] = "";
		}
		
		if ($logEntry['setMaxCount'] == 4)
		{
			$logEntry['setMaxCount'] = 5;
		}
		
			/* Don't update enchantment for invalid types */
		if (array_key_exists('type', $logEntry) && $logEntry['type'] != 1 && $logEntry['type'] != 2 && $logEntry['type'] != 14 && $logEntry['type'] != 20  && $logEntry['type'] != 21 && $logEntry['type'] != 26)
		{
			$logEntry['enchantName'] = "";
			$logEntry['enchantDesc'] = "";
		}
		
		if ($logEntry['materialLevelDesc'] != null)
		{
			$logEntry['matLevelDesc'] = $logEntry['materialLevelDesc'];
		}
		
		if ($logEntry['refinedMat'] != "")
		{
			$mat = ucwords(preg_replace("#\^.*#", "", $logEntry['refinedMat']));
			$logEntry['useAbilityDesc'] = "Can be refined into |cffffff7|r to |cffffff10|r |cffffff$mat|r.";
		}
		
		if ($logEntry["reagentTrait1"] != "")
		{
			$logEntry['useAbilityDesc'] = "|cffffffTRAITS|r\n{$logEntry["reagentTrait1"]}\n{$logEntry["reagentTrait2"]}\n{$logEntry["reagentTrait3"]}\n{$logEntry["reagentTrait4"]}";
		}
		
		if (array_key_exists('type', $logEntry) && $logEntry["runeName"] != "" && $logEntry["type"] >= 51 && $logEntry["type"] <= 53)
		{
			$skillName = "Aspect Improvement";
			if ($logEntry["type"] == 51) $skillName = "Potency Improvement";
			
			$logEntry['useAbilityDesc'] = "|cffffffTRANSLATION|r\n{$logEntry["runeName"]}";
			if ($logEntry["runeRank"] > 0) $logEntry['useAbilityDesc'] .= "\n\n|c00ff00Requires $skillName {$logEntry["runeRank"]}|r";
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
			$reqTrades = $logEntry['reqTrades'];
			
			if ($resultAbility == null) $resultAbility = "";
			if ($resultCooldown == null) $resultCooldown = "0";
			if ($resultMinLevel == null) $resultMinLevel = "";
			if ($resultMaxLevel == null) $resultMaxLevel = "";
			if ($recipeRank== null) $recipeRank = "";
			if ($recipeQuality == null) $recipeQuality = "";
			if ($recipeIngredients == null) $recipeIngredients = "";
			if ($reqTrades == null) $reqTrades = "";
			
			$reqTrades = str_replace("Alchemy", "Solvent Proficiency", $reqTrades);
			$reqTrades = str_replace("Blacksmithing", "Metalworking", $reqTrades);
			$reqTrades = str_replace("Clothing", "Tailoring", $reqTrades);
			$reqTrades = str_replace("Enchanting", "Potency Improvement", $reqTrades);
			$reqTrades = str_replace("Provisioning", "Recipe Improvement", $reqTrades);
			
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
				
				if ($abilityDesc != "") $abilityDesc .= "\n";
				$abilityDesc .= "Scales from $minImage|cffffff".$resultMinLevel."|r to $maxImage|cffffff".$resultMaxLevel."|r.";
			}
			else if ($resultMinLevel == 0 && $resultMaxLevel == 0)
			{
				//$abilityDesc .= "\nThese effects are scaled based on your level.";
			}
			
			$recipeIngredients = preg_replace("#(\^[a-zA-Z]+)#", "", $recipeIngredients);
			
			if ($abilityDesc != "") $abilityDesc .= "\n\n";
			
			if ($recipeIngredients != "") 
			{
				$ingr = ucwords($recipeIngredients);
				$ingr = preg_replace("# X([0-9]+)#", " ($1)", $ingr);
				$ingr = preg_replace("# \(1\)#", "", $ingr);
				$abilityDesc .= "|cffffffINGREDIENTS|r\n" . $ingr;
			}
			
			if ($reqTrades != "")
			{
				if ($abilityDesc != "") $abilityDesc .= "\n\n";
				$abilityDesc .= "|cffffffTO CREATE|r";
				$trades = explode(",", $reqTrades);
				
				foreach ($trades as $trade) 
				{
					$abilityDesc .= "\n|c00ff00Requires $trade|r";
				}
				
				if ($recipeQuality > 0) $abilityDesc .= "\n|c00ff00Requires Recipe Quality $recipeQuality|r";
			}
			else if ($recipeRank > 0 && $recipeQuality > 0)
			{
				$abilityDesc .= "\n\n|cffffffTO CREATE|r\n|c00ff00Requires Recipe Improvement $recipeRank|r\n|c00ff00Requires Recipe Quality $recipeQuality|r";
			}
			
			$logEntry['useAbilityDesc'] = $abilityDesc;
		}
		
		if (array_key_exists('furnDataID', $logEntry))
		{
			$logEntry['setDesc1'] = $logEntry['furnDataID'];
			$logEntry['setDesc2'] = $logEntry['furnCate'];
			$logEntry['setDesc3'] = $logEntry['furnSubCate'];
			$logEntry['setDesc4'] = $logEntry['furnCateName'];
			$logEntry['setDesc5'] = $logEntry['furnSubCateName'];
		}
		
	}
	
	
	public function FixItemLink ($itemLink)
	{
		$itemLink = preg_replace("#(.*):(0|1):(0|1):(0|1):([0-9]+:[0-9]+\|h.*\|h)#", '$1:0:0:0:$5', $itemLink);
		
		return $itemLink;
	}
	
	
	public function OnMineItem ($logEntry)
	{
		if ($logEntry['timeStamp'] > 0 && $logEntry['timeStamp'] < self::START_MINEITEM_TIMESTAMP) return $this->reportLogParseError("\tWarning: Skipping mineitem due to old timestamp!" .  $logEntry['timeStamp']);
						
		$itemLink = $logEntry['itemLink'];
		if ($itemLink == null) return $this->reportLogParseError("Missing item link!");
		
		$itemLink = $this->FixItemLink($itemLink);
		
		$minedItem = $this->LoadMinedItemLink($itemLink);
		if ($minedItem === false) return $this->reportLogParseError("\tWarning: Failed to load or initialize item data!");
		
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
				$this->log("\tWarning: item #$itemId, set $setName has $setCount set bonus elements!");
				$this->lastSetCount6WarningItemId = $minedItem['itemId'];
			}
			
			if ($minedItem['setName'] == "Amberplasm" || $logEntry['setName'] == "Amberplasm")
			{
				$minedItem['setBonusDesc4'] = $logEntry['setDesc4'] . "\n" . $logEntry['setDesc5'];
				$minedItem['setBonusDesc5'] = $logEntry['setDesc6'];
			}
			else if ($minedItem['setName'] == "Shacklebreaker" || $logEntry['setName'] == "Shacklebreaker")
			{
				//$minedItem['setBonusDesc3'] = $logEntry['setDesc3'] . "\n" . $logEntry['setDesc4'];
				//$minedItem['setBonusDesc4'] = $logEntry['setDesc5'] . "\n" . $logEntry['setDesc6'];
				$minedItem['setBonusDesc5'] = $logEntry['setDesc5'] . "\n" . $logEntry['setDesc6'];
				//$logEntry['setDesc5'] = "";
			}
			else if ($minedItem['setName'] == "Vastarie's Tutelage" || $logEntry['setName'] == "Vastarie's Tutelage")
			{
				//$minedItem['setBonusDesc3'] = $logEntry['setDesc3'] . "\n" . $logEntry['setDesc4'];
				$minedItem['setBonusDesc4'] = $logEntry['setDesc4'] . "\n" . $logEntry['setDesc5'];
				$minedItem['setBonusDesc5'] = $logEntry['setDesc6'];
				//$logEntry['setDesc5'] = "";
			}
			else
			{
				$minedItem['setBonusDesc5'] = $logEntry['setDesc5'] . "\n" . $logEntry['setDesc6'];
			}
			
			$minedItem['__dirty'] = true;
		}
		
		if (array_key_exists('setDesc7', $logEntry))
		{
			if ($minedItem['itemId'] != $this->lastSetCount7WarningItemId)
			{
				$setName = $logEntry['setName'];
				$setCount = $logEntry['setBonusCount'];
				$itemId = $minedItem['itemId'];
				$this->log("\tWarning: item #$itemId, set $setName has $setCount set bonus elements!");
				$this->lastSetCount7WarningItemId = $minedItem['itemId'];
			}
		}
		
		$result = true;
		if ($minedItem['__dirty']) $result &= $this->SaveMinedItem($minedItem);
		
		if (!$result) $this->reportLogParseError("\tError: Failed to save item data!");
		
		$this->currentUser['lastMinedItemLogEntry'] = $logEntry;
		//$this->log("Found mined item $itemLink");
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
		if ($logEntry['timeStamp'] > 0 && $logEntry['timeStamp'] < self::START_MINEITEM_TIMESTAMP) return $this->reportLogParseError("\tWarning: Skipping mineitem due to old timestamp! ". $logEntry['timeStamp']);
		
		$itemLink = $logEntry['itemLink'];
		if ($itemLink == null) return $this->reportLogParseError("Missing item link!");
		
		$itemLink = $this->FixItemLink($itemLink);
		
		$minedItem = $this->LoadMinedItemLink($itemLink);
		if ($minedItem === false) return $this->reportLogParseError("\tError: Failed to load or initialize item data!");
		
		if ($minedItem['__isNew'] === true)
		{
			++$this->currentUser['newCount'];
			$this->currentUser['__dirty'] = true;
		}
		
		$mergedLogEntry = $this->MergeMineItemLogs($logEntry, $this->currentUser['lastMinedItemLogEntry']);
		$this->ParseMinedItemLog($mergedLogEntry);
		
		$this->MergeMineItemLogToDb($minedItem, $mergedLogEntry);
		
		if (array_key_exists('setDesc6', $mergedLogEntry))
		{
			if ($minedItem['itemId'] != $this->lastSetCount6WarningItemId)
			{
				$setName = $mergedLogEntry['setName'];
				$setCount = $mergedLogEntry['setBonusCount'];
				$itemId = $minedItem['itemId'];
				$this->log("\tWarning: item #$itemId, set $setName has $setCount set bonus elements!");
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
		
		if (!$result) $this->reportLogParseError("\tError: Failed to save item data!");
		
		$this->currentUser['lastMinedItemLogEntry'] = $mergedLogEntry;
		//$this->log("Found mined item $itemLink");
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
	
	
	public function OnSkill18 ($logEntry)
	{
		if (!$this->IsValidUser($logEntry)) return false;
				
		$version = $this->currentUser['lastSkillDumpNote'];
		$abilityId = $logEntry['id'];
		if ($abilityId == null || $abilityId == "") return $this->reportLogParseError("Missing abilityId in skill!");
		
		$skill = $this->LoadSkillDump($abilityId);
		if ($skill === false) return $this->reportLogParseError("Failed to load skill $abilityId!");
  	
		$skill['displayId'] = $logEntry['id'];
		$skill['name'] = $logEntry['name'];
		
			/* Rank dependent parameters */
		$skill['description'] = $logEntry['desc'];
		$skill['duration'] = $logEntry['duration'];
		$skill['cost'] = $logEntry['cost'];
		$skill['target'] = $logEntry['target'];
		$skill['minRange'] = $logEntry['minRange'];
		$skill['maxRange'] = $logEntry['maxRange'];
		$skill['radius'] = $logEntry['radius'];
		$skill['castTime'] = $logEntry['castTime'];
		$skill['channelTime'] = $logEntry['channelTime'];
		
		$skill['isPassive'] = $logEntry['passive'] == "true" ? 1 : 0;
		$skill['isPermanent'] = $logEntry['perm'] == "true" ? 1 : 0;
		$skill['isChanneled'] = $logEntry['channel'];
		
		$skill['angleDistance'] = $logEntry['angleDistance'];
		$skill['mechanic'] = $logEntry['mechanic'];
		$skill['upgradeLines'] = $logEntry['upgradeLines'];
		$skill['effectLines'] = $logEntry['effectLines'];
		$skill['texture'] = $logEntry['icon'];
		$skill['isPlayer'] = 0;
		
		if ($logEntry['skillType'] > 0 || $logEntry['desc1'] != null)
		{
			$skill['isUltimate'] = $logEntry['ultimate'];
			$skill['skillType'] = $logEntry['skillType'];
			$skill['skillLine'] = $logEntry['skillLineName'];
			$skill['skillIndex'] = $logEntry['abilityIndex'];
			$skill['isPlayer'] = 1;
			$skill['morph'] = $logEntry['morph'];
			$skill['learnedLevel'] = $logEntry['earnedLevel'];
			
			$skillLineId = $logEntry['skillLineId'];
			
			if ($this->CLASS_SKILLLINE_IDS[$skillLineId])
				$skill['classType'] = $this->CLASS_SKILLLINE_IDS[$skillLineId];
			elseif ($this->RACE_SKILLLINE_IDS[$skillLineId])
				$skill['raceType'] = $this->RACE_SKILLLINE_IDS[$skillLineId];
			
			$id1 = $logEntry['id1'];
			$id2 = $logEntry['id2'];
			$id3 = $logEntry['id3'];
			
			if ($id1 > 0 && ($id1 == $abilityId || $id2 == $abilityId || $id3 == $abilityId))
			{
				$skill['isPassive'] = 0;
				$morph = $logEntry['morph'];
				$rank = $logEntry['rank'];
				$skill['rank'] = 4;
				$skill['baseAbilityId'] = $logEntry['id1'];
				
				if ($morph == 0)
				{
					$skill['prevSkill'] = 0;
					$skill['nextSkill'] = $logEntry['id2'];
					$skill['nextSkill2'] = $logEntry['id3'];
				}
				elseif ($morph == 1)
				{
					$skill['prevSkill'] = $logEntry['id1'];
					$skill['nextSkill'] = 0;
					$skill['nextSkill2'] = 0;
				}
				elseif ($morph == 2)
				{
					$skill['prevSkill'] = $logEntry['id1'];
					$skill['nextSkill'] = 0;
					$skill['nextSkill2'] = 0;
				}
			}
			else if ($logEntry['passive1'] > 0 || $logEntry['passive'] == "true")
			{
				$skill['isPassive'] = 1;
				$maxLevel = intval($logEntry['maxLevel']);
				$currentRank = 0;
				
				for ($i = 1; $i <= $maxLevel; ++$i)
				{
					if ($abilityId == $logEntry["passive$i"]) $currentRank = $i;
				}
				
				$skill['rank'] = $currentRank;
				$nextRank = $currentRank + 1;
				$prevRank = $currentRank - 1;				
				
				if ($logEntry['passive1'] == 0 || $currentRank == 0)
				{
					$skill['learnedLevel'] = $logEntry['earnedLevel'];
					$skill['baseAbilityId'] = $abilityId;
					$skill['rank'] = 1;
					$skill['prevSkill'] = -1;
					$skill['nextSkill'] = -1;
					$skill['nextSkill2'] = -1;
				}
				else
				{
					$skill['baseAbilityId'] = $logEntry['passive1'];					
					$skill['prevSkill'] = $logEntry['passive' . $prevRank];
					$skill['nextSkill'] = $logEntry['passive' . $nextRank];
					$skill['nextSkill2'] = -1;
					if ($skill['prevSkill'] == null) $skill['prevSkill'] = -1;
					if ($skill['nextSkill'] == null) $skill['nextSkill'] = -1;
					$skill['learnedLevel'] = $logEntry['rank' . $currentRank];
				}				
				
			}
		}
		
		if (($logEntry['skillType'] <= 0 && $logEntry['desc2'] == null) || $skill['isPassive'] || $logEntry['desc1'] == null || ($skill['isPlayer'] == 0 && $logEntry['desc2'] == null))
		{
			$this->SaveSkillDump($skill);
			return true;
		}
		
		$id1 = $abilityId;
		$id2 = $this->GetCustomAbilityId($abilityId, 2);
		$id3 = $this->GetCustomAbilityId($abilityId, 3);
		$id4 = $this->GetCustomAbilityId($abilityId, 4);
		
		//print("SkillDump18: $id1, $id2, $id3, $id4\n");
		
		$skill2 = $this->LoadSkillDump($id2);
		$skill3 = $this->LoadSkillDump($id3);
		$skill4 = $this->LoadSkillDump($id4);
		
		$this->MergeSkillData($skill2, $skill);
		$this->MergeSkillData($skill3, $skill);
		$this->MergeSkillData($skill4, $skill);
		
		$origPrevSkill  = $skill['prevSkill'];
		$origNextSkill  = $skill['nextSkill'];
		$origNextSkill2 = $skill['nextSkill2'];
		
		$skill['displayId'] = $abilityId;
		$skill['description'] = $logEntry['desc1'];
		$skill['duration'] = $logEntry['duration1'];
		$skill['cost'] = $logEntry['cost1'];
		$skill['target'] = $logEntry['target1'];
		$skill['minRange'] = $logEntry['minRange1'];
		$skill['maxRange'] = $logEntry['maxRange1'];
		$skill['radius'] = $logEntry['radius1'];
		$skill['castTime'] = $logEntry['castTime1'];
		$skill['channelTime'] = $logEntry['channelTime1'];
		$skill['rank'] = 1;
		$skill['nextSkill'] = $id2;
		$skill['nextSkill2'] = 0;
		$skill['prevSkill'] = $origPrevSkill;
						
		$this->SaveSkillDump($skill);
		
		$skill2['displayId'] = $abilityId;
		$skill2['description'] = $logEntry['desc2'];
		$skill2['duration'] = $logEntry['duration2'];
		$skill2['cost'] = $logEntry['cost2'];
		$skill2['target'] = $logEntry['target2'];
		$skill2['minRange'] = $logEntry['minRange2'];
		$skill2['maxRange'] = $logEntry['maxRange2'];
		$skill2['radius'] = $logEntry['radius2'];
		$skill2['castTime'] = $logEntry['castTime2'];
		$skill2['channelTime'] = $logEntry['channelTime2'];
		$skill2['rank'] = 2;
		$skill2['nextSkill'] = $id3;
		$skill2['nextSkill2'] = 0;
		$skill2['prevSkill'] = $id1;
		
		$this->SaveSkillDump($skill2);
		
		$skill3['displayId'] = $abilityId;
		$skill3['description'] = $logEntry['desc3'];
		$skill3['duration'] = $logEntry['duration3'];
		$skill3['cost'] = $logEntry['cost3'];
		$skill3['target'] = $logEntry['target3'];
		$skill3['minRange'] = $logEntry['minRange3'];
		$skill3['maxRange'] = $logEntry['maxRange3'];
		$skill3['radius'] = $logEntry['radius3'];
		$skill3['castTime'] = $logEntry['castTime3'];
		$skill3['channelTime'] = $logEntry['channelTime3'];
		$skill3['rank'] = 3;
		$skill3['nextSkill'] = $id4;
		$skill3['nextSkill2'] = 0;
		$skill3['prevSkill'] = $id2;
			
		$this->SaveSkillDump($skill3);
			
		$skill4['displayId'] = $abilityId;
		$skill4['description'] = $logEntry['desc4'];
		$skill4['duration'] = $logEntry['duration4'];
		$skill4['cost'] = $logEntry['cost4'];
		$skill4['target'] = $logEntry['target4'];
		$skill4['minRange'] = $logEntry['minRange4'];
		$skill4['maxRange'] = $logEntry['maxRange4'];
		$skill4['radius'] = $logEntry['radius4'];
		$skill4['castTime'] = $logEntry['castTime4'];
		$skill4['channelTime'] = $logEntry['channelTime4'];
		$skill4['rank'] = 4;
		$skill4['nextSkill'] = $origNextSkill;
		$skill4['nextSkill2'] = $origNextSkill2;
		$skill4['prevSkill'] = $id3;
			
		$this->SaveSkillDump($skill4);
		
		return true;
	}
	
	
	public function GetCustomAbilityId($abilityId, $rank)
	{
		global $ESO_BASESKILL_RANKDATA;
		
		if ($rank <= 1) return $abilityId;
		
		$skillRankData = $ESO_BASESKILL_RANKDATA[$abilityId];
		
		if ($skillRankData == null || $skillRankData[$rank] == null) return $abilityId + 10000000*$rank; 
		return $skillRankData[$rank];		
	}
	
	
	public function MergeSkillData (&$skill1, $skill2)
	{
		static $FIELDS = array(
				'name',
				'isPassive',
				'isPermanent',
				'isChanneled',
				'angleDistance',
				'mechanic',
				'upgradeLines',
				'effectLines',
				'texture',
				'isUltimate',
				'skillType',
				'skillLine',
				'skillIndex',
				'morph',
				'learnedLevel',
				'isPlayer',
				'baseAbilityId',
		);
		
		foreach ($FIELDS as $field)
		{
			$skill1[$field] = $skill2[$field];
		}
	}
	
	
	public function OnSkill ($logEntry)
	{
		if (IsEsoVersionAtLeast(self::SKILLS_TABLESUFFIX, 18)) return $this->OnSkill18($logEntry);
		
		if (!$this->IsValidUser($logEntry)) return false;
		
		$version = $this->currentUser['lastSkillDumpNote'];
		$abilityId = $logEntry['id'];
		if ($abilityId == null || $abilityId == "") return $this->reportLogParseError("Missing abilityId in skill!");
		
		$skill = $this->LoadSkillDump($abilityId);
		if ($skill === false) return false;
  	
		$skill['name'] = $logEntry['name'];
		$skill['displayId'] = $logEntry['id'];
		$skill['description'] = $logEntry['desc'];
		$skill['duration'] = $logEntry['duration'];
		$skill['cost'] = $logEntry['cost'];
		$skill['target'] = $logEntry['target'];
		$skill['minRange'] = $logEntry['minRange'];
		$skill['maxRange'] = $logEntry['maxRange'];
		$skill['radius'] = $logEntry['radius'];
		$skill['isPassive'] = $logEntry['passive'];
		$skill['isPermanent'] = $logEntry['perm'] == "true" ? 1 : 0;
		if ($skill['isPermanent'] == null) $skill['isPermanent'] = 0;
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
		
		if (array_key_exists('skillLine', $logEntry) || $logEntry['skillLineId'] > 0) 
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
			// No longer need to parse this
		return true;
		
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
	
	
	public $CLASS_SKILLLINE_IDS = array(
			133 => "Necromancer",
			132 => "Necromancer",
			131 => "Necromancer",
			129 => "Warden",
			128 => "Warden",
			127 => "Warden",			
			38 => "Nightblade",
			39 => "Nightblade",
			40 => "Nightblade",			
			43 => "Sorcerer",
			42 => "Sorcerer",
			41 => "Sorcerer",			
			37 => "Dragonknight",
			36 => "Dragonknight",
			35 => "Dragonknight",			
			28 => "Templar",
			27 => "Templar",
			22 => "Templar",
	);
	
	
	public $RACE_SKILLLINE_IDS = array(
			63 => "Argonian",
			60 => "Breton",
			64 => "Dark Elf",
			56 => "High Elf",
			59 => "Imperial",
			58 => "Khajiit",
			65 => "Nord",
			52 => "Orc",
			62 => "Redguard",
			57 => "Wood Elf",
	);
	
	
	public function OnSkillLine ($logEntry)
	{
		if (!$this->IsValidUser($logEntry)) return false;
		$version = $this->currentUser['lastSkillDumpNote'];
		
		$this->currentUser['lastSkillLineName'] = $logEntry['name'];
		
		$skillLine = $this->LoadSkillLine($logEntry['name']);
		if ($skillLine === false) return false;
		
		$skillLineId = $logEntry['skillLineId'];
		$skillLine['xp'] = $logEntry['xpString'];
		$skillLine['totalXp'] = $logEntry['totalXp'];
		
			// Doesn't work from update 17
		//if (array_key_exists('race', $logEntry)) $skillLine['raceType'] = $logEntry['race'];
		//if (array_key_exists('class', $logEntry)) $skillLine['classType'] = $logEntry['class'];
		
		if ($this->CLASS_SKILLLINE_IDS[$skillLineId])
			$skillLine['classType'] = $this->CLASS_SKILLLINE_IDS[$skillLineId];
		elseif ($this->RACE_SKILLLINE_IDS[$skillLineId])
			$skillLine['raceType'] = $this->RACE_SKILLLINE_IDS[$skillLineId];			
		
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
			$skillLine['fullName'] = GetEsoSkillTypeText($skillLine['skillType']) . '::' . $logEntry['name'];
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
		
		$this->dbWriteCount += 3;
		
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
		$cpDisc['c'] = -1;
		$cpDisc['d'] = -1;
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
		
		$collectible['name'] = preg_replace("#\^.*#", "", $logEntry['name']);
		$collectible['description'] = FormatRemoveEsoItemDescriptionText($logEntry['description']);
		$collectible['nickname'] = preg_replace("#\^.*#", "", $logEntry['nickname']);
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
		$collectible['furnCategory'] = $logEntry['furnCateName'];
		$collectible['furnSubcategory'] = $logEntry['furnSubcateName'];
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
		
		$this->log("\tFound Achievement::Start...");
				
		$this->lastQuery = "DELETE FROM achievementCategories;";
		$result = $this->db->query($this->lastQuery);
		if (!$result) return $this->reportLogParseError("Failed to clear achievementCategories table!");
		
		$this->lastQuery = "DELETE FROM achievements;";
		$result = $this->db->query($this->lastQuery);
		if (!$result) return $this->reportLogParseError("Failed to clear achievements table!");
		
		$this->lastQuery = "DELETE FROM achievementCriteria;";
		$result = $this->db->query($this->lastQuery);
		if (!$result) return $this->reportLogParseError("Failed to clear achievementCriteria table!");
		
		$this->dbWriteCount += 3;
		
		$this->logInfos['lastAchievementUpdate'] = date("Y-M-d H:i:s");
				
		return true;
	}
	
	
	public function OnAchievementCategory ($logEntry)
	{
		if (!$this->IsValidUser($logEntry)) return false;
		
		$achievementCategory = array();
		
		$achievementCategory['categoryName'] = $logEntry['name'];
		$achievementCategory['name'] = $logEntry['name'] . "::General";
		$achievementCategory['subCategoryName'] = "General";
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
		$achievementCategory['subCategoryName'] = $logEntry['name'];
		
		if ($logEntry['name'] == "")
			$achievementCategory['name'] =  $logEntry['categoryName'] . "::General";
		else
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
		$achievement['nextId'] = $logEntry['nextId'];
		$achievement['points'] = $logEntry['points'];
		$achievement['itemName'] = $logEntry['itemName'];
		$achievement['itemIcon'] = $logEntry['itemIcon'];
		$achievement['itemQuality'] = $logEntry['itemQuality'];
		$achievement['title'] = $logEntry['title'];
		
		if ($logEntry['subCategoryName'] == null || $logEntry['subCategoryName'] == "")
			$achievement['categoryName'] = $logEntry['categoryName'] . "::General";
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
	
	
	public function OnGuildSummary ($logEntry)
	{
		/*		
			logData.guildIndex = guildIndex
			logData.guildId = GetGuildId(guildIndex)
			logData.name = GetGuildName(logData.guildId)
			logData.founded = GetGuildFoundedDate(logData.guildId)
			logData.numMembers, logData.numOnline, logData.leader = GetGuildInfo(logData.guildId)
			--logData.description = GetGuildDescription(logData.guildId)
			--logData.motd = GetGuildMotD(logData.guildId)
			logData.kiosk = GetGuildOwnedKioskInfo(guildId)
			logData.server = GetWorldName()
		*/
		
		if (intval($logEntry['timeStamp1']) < self::START_GUILDSALESDATA_TIMESTAMP) return true;
		if ($logEntry['name'] == "") return false;
		
		$server = $this->GetGuildSaleServer($logEntry['server']);
		$this->salesData->server = $server;
		
		$guildData = &$this->salesData->GetGuildData($server, $logEntry['name']);
		
		//$this->log("OnGuildSummary");
		//print_r($logEntry);
		
		if ($guildData['__new'] === true || true)
		{
			$a = strptime($logEntry['founded'], '%m/%d/%Y');
						
			if ($a !== false)
			{
				$guildData['foundedDate'] = mktime(0, 0, 0, $a['tm_mon']+1, $a['tm_mday'], $a['tm_year']+1900);
			}
			
			$guildData['storeLocation'] = $logEntry['kiosk'];
			$guildData['lastStoreLocTime'] = $logEntry['timeStamp1'];
			$guildData['numMembers'] = $logEntry['numMembers'];
			$guildData['leader'] = $logEntry['leader'];			
			
			$guildData['__dirty'] = true;
			$guildData['__new'] = false;
		}
		else if ($guildData['lastStoreLocTime'] <= 0 || intval($guildData['lastStoreLocTime']) < intval($logEntry['timeStamp1']))
		{
			//$this->log("Updating Guild Data...");
			$guildData['storeLocation'] = $logEntry['kiosk'];
			$guildData['lastStoreLocTime'] = $logEntry['timeStamp1'];
			$guildData['numMembers'] = $logEntry['numMembers'];
			$guildData['leader'] = $logEntry['leader'];
			
			$guildData['__dirty'] = true;
		}
		
		return true;
	}
	
	
	public function OnGuildSaleSearchInfo ($logEntry)
	{
		/*
		 logData.guildId = guildId
		 logData.name = GetGuildName(guildId)
		 logData.server = GetWorldName()
		 logData.zone = uespLog.lastTargetData.zone
		 logData.lastTarget = uespLog.lastTargetData.name
		 logData.kiosk = GetGuildOwnedKioskInfo(guildId)
		 */
		
		//$this->("OnGuildSaleSearchInfo");
		
		if (intval($logEntry['timeStamp1']) < self::START_GUILDSALESDATA_TIMESTAMP) return true;
		
		if ($logEntry['name'] == "") 
		{
			if ($logEntry['guild'] == "") return false;
			$logEntry['name'] = $logEntry['guild'];
		}
		
		$server = $this->GetGuildSaleServer($logEntry['server']);
		$this->salesData->server = $server;
				
		$guildData = &$this->salesData->GetGuildData($server, $logEntry['name']);
		
		if ($guildData['__new'] === true)
		{
			if ($logEntry['kiosk'] != null && $logEntry['kiosk'] != "")
			{
				$guildData['storeLocation'] = $logEntry['kiosk'];
			}
			else
			{
				$guildData['storeLocation'] = $logEntry['lastTarget'] . " in " . $logEntry['zone'];
			}
			
			$guildData['lastStoreLocTime'] = $logEntry['timeStamp1'];
			$guildData['numMembers'] = 0;
			$guildData['leader'] = "";
				
			$guildData['__dirty'] = true;
			$guildData['__new'] = false;
		}
		else if ($guildData['lastStoreLocTime'] <= 0 || intval($guildData['lastStoreLocTime']) < intval($logEntry['timeStamp1']) || $guildData['storeLocation'] == "")
		{
			if ($logEntry['kiosk'] != null && $logEntry['kiosk'] != "")
			{
				$guildData['storeLocation'] = $logEntry['kiosk'];
			}
			else
			{
				$guildData['storeLocation'] = $logEntry['lastTarget'] . " in " . $logEntry['zone'];
			}
			
			$guildData['lastStoreLocTime'] = $logEntry['timeStamp1'];
			
			$guildData['__dirty'] = true;
		}
		
		return true;
	}
	
	
	public function GetGuildSaleServer($server)
	{
		return $this->salesData->GetGuildSaleServer($server);
	}
	
	
	public function OnGuildSale ($logEntry)
	{
		/*
		 	logData.type = eventType
			logData.saleTimestamp = tostring(currentTimestamp - seconds)
			logData.eventId = tostring(eventId)
			logData.seller = seller
			logData.buyer = buyer
			logData.qnt = qnt
			logData.gold = gold
			logData.taxes = taxes
			logData.server = GetWorldName()
			logData.guild = GetGuildName(guildId)
			logData.itemLink = itemLink
		 */
				
		if (intval($logEntry['timeStamp1']) < self::START_GUILDSALESDATA_TIMESTAMP) return true;
		if ($logEntry["itemLink"] == null) return false;
		if ($logEntry['guild'] == "") return false;
		if (floatval($logEntry['eventId']) < 1) return false;
		
		$server = $this->GetGuildSaleServer($logEntry['server']);
		$this->salesData->server = $server;
		
		$logEntry["seller"] = preg_replace("#(;.*)#", "", $logEntry["seller"]);
		$logEntry["buyer"] = preg_replace("#(;.*)#", "", $logEntry["buyer"]);
		
		$guildData = &$this->salesData->GetGuildData($server, $logEntry['guild']);
		$itemData = &$this->salesData->GetItemDataByKey($server, $logEntry["itemLink"], $logEntry);
	
		$salesData = $this->salesData->LoadSale($itemData['id'], $guildData['id'], $logEntry['eventId']);
		
		if ($salesData === false)
		{
			$salesData = $this->salesData->CreateNewSale($itemData, $guildData, $logEntry);
		}
		else
		{
			//$this->log("Found duplicate sale: {$itemData['id']}:{$guildData['id']}:{$logEntry['eventId']}");
		}
		
		return true;
	}
	
	
	public function OnGuildSaleSearchEntry ($logEntry)
	{
		/*
			logData.event = "GuildSaleSearchEntry"
			logData.guildId = guildId
			logData.guild = GetGuildName(guildId)
			logData.server = GetWorldName()
			logData.icon, logData.item, logData.quality, logData.qnt, logData.seller, logData.timeRemaining, logData.price, logData.currency = GetTradingHouseSearchResultItemInfo(itemIndex)
			logData.itemLink = GetTradingHouseSearchResultItemLink(itemIndex)
			logData.listTimestamp = tostring(currentTimestamp + logData.timeRemaining - uespLog.SALES_MAX_LISTING_TIME) 
		 */
		
		if (intval($logEntry['timeStamp1']) < self::START_GUILDSALESDATA_TIMESTAMP) return true;
		if ($logEntry["itemLink"] == null) return false;
		if ($logEntry['guild'] == "") return false;
		
		$server = $this->GetGuildSaleServer($logEntry['server']);
		$this->salesData->server = $server;
		
		$logEntry['seller'] = preg_replace("#(\|.*)#", "", $logEntry['seller']);
		$logEntry["seller"] = preg_replace("#(;.*)#", "", $logEntry["seller"]);
		$logEntry["buyer"] = preg_replace("#(;.*)#", "", $logEntry["buyer"]);
		
		$guildData = &$this->salesData->GetGuildData($server, $logEntry['guild']);
		$itemData = &$this->salesData->GetItemDataByKey($server, $logEntry["itemLink"], $logEntry);
		$hasUniqueId = false;
		$salesData = false;
		
		if ($logEntry['uniqueId'])
		{
			//print("\t\t{$logEntry['uniqueId']}: UniqueId\n");
			$salesData = $this->salesData->LoadSaleSearchEntryById($itemData['id'], $guildData['id'], $logEntry['uniqueId']);
			
			if ($salesData)
			{
				$hasUniqueId = true;
				
				if ($salesData['server'] != $server || $salesData['itemId'] != $itemData['id'] || $salesData['guildId'] != $guildData['id'] || $salesData['sellerName'] != $logEntry['seller'])
				{
					print("\t\tSales Entry UniqueId mismatch: {$logEntry['uniqueId']}, {$salesData['server']}:$server, {$salesData['itemId']}:{$itemData['id']}, {$salesData['guildId']}:{$guildData['id']}, {$salesData['sellerName']}:{$logEntry['seller']}  \n");
					$hasUniqueId = false;
					$salesData = false;
				}
			}
		}		
		
		if (!$salesData)
		{
			$salesData = $this->salesData->LoadSaleSearchEntry($itemData['id'], $guildData['id'], $logEntry['listTimestamp'], $logEntry['seller']);
		}
		
		if ($salesData === false)
		{
			$salesData = $this->salesData->CreateNewSaleSearchEntry($itemData, $guildData, $logEntry);
		}
		else if ($logEntry['uniqueId'] && !$hasUniqueId)
		{
			//print("\t\tUpdating Unique ID\n");
			$salesData['uniqueId'] = $logEntry['uniqueId'];
			$this->salesData->UpdateSearchEntryId($salesData);
		}
		
		return true;
	}
	
	
	public function OnGuildSaleListingEntryCancel ($logEntry)
	{
		/*
			logData.event = eventName
			logData.guildId, logData.guild = GetCurrentTradingHouseGuildDetails()
			logData.server = GetWorldName()
			logData.qnt = listingData.qnt
			logData.seller = listingData.seller
			logData.item = listingData.name
			logData.quality = listingData.quality
			logData.price = listingData.price
			logData.itemLink = listingData.itemLink
			logData.listTimestamp = tostring(listingData.listTimestamp) 
		 */
		
		if (intval($logEntry['timeStamp1']) < self::START_GUILDSALESDATA_TIMESTAMP) return true;
		
		return true;
	}
	
	
	public function OnGuildSaleListingInfo ($logEntry)
	{
		/*
	 		logData.event = "GuildSaleListingInfo"
			logData.guildId = guildId
			logData.guild = guildName
			logData.server = GetWorldName()
			logData.zone = uespLog.lastTargetData.zone
			logData.lastTarget = uespLog.lastTargetData.name
			logData.kiosk = GetGuildOwnedKioskInfo(guildId)
		 */
		
		return $this->OnGuildSaleSearchInfo($logEntry);
	}
	
	
	public function OnGuildSaleListingEntry ($logEntry)
	{
		/*
			logData.event = "GuildSaleListingEntry"
			logData.guildId = guildId
			logData.guild = guildName
			logData.server = GetWorldName()
			logData.icon, logData.item, logData.quality, logData.qnt, logData.seller, logData.timeRemaining, logData.price = GetTradingHouseListingItemInfo(itemIndex)
			logData.itemLink = GetTradingHouseListingItemLink(itemIndex)
			logData.listTimestamp = tostring(currentTimestamp + logData.timeRemaining - uespLog.SALES_MAX_LISTING_TIME) 
		 */
		
		return $this->OnGuildSaleSearchEntry($logEntry);
	}
	
	
	public function OnPickPocketFailed ($logEntry)
	{
		//logData.ppBonus, logData.ppIsHostile, logData.ppChance, logData.ppDifficulty, logData.ppEmpty, 
		//logData.ppResult, logData.ppClassString, logData.ppClass = GetGameCameraPickpocketingBonusInfo()
		//x, y, zone ,target
		
		$name = $logEntry['target'];
		if ($name == "") return false;
		
		$npcRecord = $this->FindNPC($name);
		
		if ($npcRecord == null)
		{
			$npcRecord = $this->CreateNPC($logEntry);
			if ($npcRecord == null) return false;
			
			if ($npcRecord['ppClass'] == "" && $logEntry['ppClassString'] != "")
			{
				$npcRecord['ppClass'] = $logEntry['ppClassString'];
				$npcRecord['ppDifficulty'] = $logEntry['ppDifficulty'];
				$this->SaveNPC($npcRecord);
			}
		}
		
		$isNewLocation = false;		
		$npcLocation = $this->FindLocation("npc", $logEntry['x'], $logEntry['y'], $logEntry['zone'], array('npcId' => $npcRecord['id']));
		
		if ($npcLocation == null)
		{
			$isNewLocation = true;
			$npcLocation = $this->CreateLocation("npc", $name, $logEntry, array('npcId' => $npcRecord['id']));
			if ($npcLocation == null) return false;
		}
		else
		{
			++$npcLocation['counter'];
				
			$result = $this->SaveLocation($npcLocation);
			if (!$result) return false;
		}
		
		$this->UpdateNpcZone($npcRecord['id'], $logEntry['zone'], $isNewLocation);
		
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
		if ($this->currentUser['name'] != "Reorx" && $this->currentUser['name'] != "Reorx2") return $this->reportLogParseError("Ignoring {$logEntry['event']} from user ".$this->currentUser['name']."!");
		return true;
	}
	
	
	public function WaitForSlaveDatabase()
	{
		if (!$this->limitDbReadsWrites) return;
		
		$origWriteCount = $this->dbWriteNextSleepCount;
		$origReadCount = $this->dbReadNextSleepCount;
		
		if ($this->dbWriteCount >= $this->dbWriteNextSleepCount) $this->dbWriteNextSleepCount = $this->dbWriteCount + $this->dbWriteCountPeriod;
		if ($this->dbReadCount >= $this->dbReadNextSleepCount) $this->dbReadNextSleepCount = $this->dbReadCount + $this->dbReadCountPeriod;

		if (!$this->waitForSlave)
		{
			$this->log("Exceeded $origWriteCount DB writes or $origReadCount DB reads...sleeping for {$this->dbWriteCountSleep} sec...");
			sleep($this->dbWriteCountSleep);
			return;
		}
		
		$checkCount = 0;
		$this->log("Exceeded $origWriteCount DB writes or $origReadCount DB reads...checking slave lag...");
		sleep($this->dbWriteCountSleep);
		
		do {
			$query = "SHOW SLAVE STATUS;";
			$result = $this->dbSlave->query($query);
			if ($result === false) return $this->reportLogParseError("Failed to query database slave for status!");
			
			$slaveData = $result->fetch_assoc();
			
			$query = "SHOW MASTER STATUS;";
			$result = $this->db->query($query);
			if ($result === false) return $this->reportLogParseError("Failed to query database master for status!");
			
			$masterData = $result->fetch_assoc();
			
			$masterPos = $masterData['Position'];
			$slavePos = $slaveData['Exec_Master_Log_Pos'];
			$slaveLag = $slaveData['Seconds_Behind_Master'];

			
			if ($slaveLag < $this->maxAllowedSlaveLag) 
			{
				$this->log("Slave database lag is $slaveLag sec...resuming writes!");
				return true;
			}
			
			$this->log("Slave lag is $slaveLag. Master position is $masterPos. Slave position is $slavePos.");
			$this->log("Waiting for slave database lag to be under {$this->maxAllowedSlaveLag} sec!");
			sleep($this->dbWriteCountSleep);
		} while ($checkCount < $this->maxSlaveLagChecks);
		
		$this->log("Exceeded {$this->maxSlaveLagChecks} slave database lag checks...resuming writes!");
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
			case "GuildSummary":
			case "GuildSale":
			case "GuildSaleSearchInfo":
			case "GuildSaleSearchEntry":
			case "GuildSaleListingEntry::Cancel":
			case "GuildSaleListingInfo":
			case "GuildSaleListingEntry":
			case 'mineBook':
			case 'mineBook:Start':
			case 'mineBook:End':
			case 'mineBook:Category':
			case 'mineBook:Collection':
			//case 'SkillCoef':
			//case 'SkillCoef::Start':
			//case 'SkillCoef::End':
				return false;
		}
		
		return true;
	}
	
	
	public function handleLogEntry ($logEntry)
	{
		$skipLogEntryCreate = false;
		
		if ($this->IGNORE_LOGENTRY_BEFORE_TIMESTAMP1 > 0 && $logEntry['timeStamp1'] > 0)
		{
			if ($logEntry['timeStamp1'] < $this->IGNORE_LOGENTRY_BEFORE_TIMESTAMP1) return false;
		}
				
		if (self::ONLY_PARSE_MINEDITEMS)
		{
			switch ($logEntry['event'])
			{
				case "mineItem::AutoStart":
				case "mineitem::Start":
				case "mineItem::Start":
				case "mineItems::idCheck::start":
				case "mineItem::idCheck::start":
				case "mineItems::idCheck::end":
				case "mineItem::idCheck::end":
				case "mineItems::idCheck":
				case "mineItem::idCheck":
				case "mineItem::AutoEnd":
				case "mineitem::End":
				case "mineItem::End":
				case "mineitem":
				case "mineItem":
				case "mi":
				case "MineCollect::Start":
				case "MineCollect::Category":
				case "MineCollect::Subcategory":
				case "MineCollect::Index":
				case "MineCollect::End":
				case "MineCollectID::Start":
				case "MineCollectID":
				case "MineCollectID::End":
					break;
				default:
					return true;
			}
		}
		else if (self::ONLY_PARSE_SALES)
		{
			switch ($logEntry['event'])
			{
				case "GuildSummary":
				case "GuildSale":
				case "GuildSaleSearchInfo":
				case "GuildSaleSearchEntry":
				case "GuildSaleListingEntry::Cancel":
				case "GuildSaleListingInfo":
				case "GuildSaleListingEntry":
					break;
				default:
					return true;
			}
		}
		else if (self::ONLY_PARSE_NPCLOOT)
		{
			$skipLogEntryCreate = true;
			
			switch ($logEntry['event'])
			{
				case "LootGained":
				case "OpenFootLocker":
				case "MoneyGained":
				case "TelvarUpdate":
				case "LockPick":
					break;
				default:
					return true;
			}
		}
		else if (self::ONLY_PARSE_NPCLOOT_CHESTS)
		{
			$skipLogEntryCreate = true;
				
			switch ($logEntry['event'])
			{
				case "LootGained":
				case "MoneyGained":
					if ($logEntry['lastTarget'] != "Chest" && $logEntry['lastTarget'] != "Safebox") return true;
				case "LockPick":
					break;
				case 'FoundTreasure':
					if ($logEntry['lastTarget'] != "Safebox") return true;
					break;
				default:
					return true;
			}
		}
		else if (self::ONLY_PARSE_MAILITEM)
		{
			switch ($logEntry['event'])
			{
				case "MailItem":
					break;
				default:
					return true;
			}
		}
		else if (self::ONLY_PARSE_SAFEBOXES_FOUND)
		{
			$skipLogEntryCreate = true;
			
			switch ($logEntry['event'])
			{
				case "FoundTreasure":
					break;
				default:
					return true;
			}
			
			if ($logEntry['name'] != "Safebox") return true;
		}
		else if (self::ONLY_PARSE_SHOWBOOK)
		{
			switch ($logEntry['event'])
			{
				case "ShowBook":
					break;
				default:
					return true;
			}
			
			$skipLogEntryCreate = true;
		}
		
		if (!$this->isValidLogEntry($logEntry)) return false;
		
		if ($this->dbWriteCount >= $this->dbWriteNextSleepCount || $this->dbReadCount >= $this->dbReadNextSleepCount)
		{
			$this->WaitForSlaveDatabase();
		}
		
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
		
		if ($skipLogEntryCreate) $createLogEntry = false;
		
		if ($createLogEntry && !$skipLogEntryCreate)
		{
			$isDuplicate = $this->isDuplicateLogEntry($logEntry);
			//$this->log("\tLogEntry: {$logEntry['event']} = $isDuplicate");
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
		
		//$this->log($logEntry['event'] . "");
		
		switch($logEntry['event'])
		{
			case "OpenFootLocker":				$result = $this->OnOpenFootlocker($logEntry); break;
			case "LootGained":					$result = $this->OnLootGainedEntry($logEntry); break;
			case "SlotUpdate":					$result = $this->OnSlotUpdateEntry($logEntry); break;
			case "InvDump":						$result = $this->OnInvDump($logEntry); break;
			case "InvDump::Start":
			case "InvDumpStart":				$result = $this->OnInvDumpStart($logEntry); break;
			case "InvDump::End":
			case "InvDumpEnd":					$result = $this->OnInvDumpEnd($logEntry); break;
			case "MoneyGained":					$result = $this->OnMoneyGained($logEntry); break;
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
			case "QuestComplete":				$result = $this->OnQuestComplete($logEntry); break;
			case "QuestObjComplete":			$result = $this->OnNullEntry($logEntry); break;
			case "QuestOptionalStep":			$result = $this->OnNullEntry($logEntry); break;
			case "QuestCompleteExperience":		$result = $this->OnNullEntry($logEntry); break;
			case "QuestMoney":					$result = $this->OnNullEntry($logEntry); break;
			case "QuestItem":					$result = $this->OnQuestItem($logEntry); break;
			
			case "Quest::Start":				$result = $this->OnQuestStart($logEntry); break;
			case "Quest::Step":					$result = $this->OnQuestStep($logEntry); break;
			case "Quest::Condition":			$result = $this->OnQuestCondition($logEntry); break;
			case "Quest::Reward":				$result = $this->OnQuestReward($logEntry); break;
			
			case "CraftComplete":				$result = $this->OnNullEntry($logEntry); break;
			case "CraftComplete::Result":		$result = $this->OnNullEntry($logEntry); break;
			case "SkillRankUpdate":				$result = $this->OnSkillRankUpdate($logEntry); break;
			case "SkillPointsChanged":			$result = $this->OnNullEntry($logEntry); break;
			case "Location":					$result = $this->OnNullEntry($logEntry); break;
			case "LoreBook":					$result = $this->OnLoreBook($logEntry); break;
			case "ShowBook":					$result = $this->OnShowBook($logEntry); break;
			case "mineBook:Start":				$result = $this->OnMineBookStart($logEntry); break;
			case "mineBook:End":				$result = $this->OnMineBookEnd($logEntry); break;
			case "mineBook:Collection":			$result = $this->OnMineBookCollection($logEntry); break;
			case "mineBook:Category":			$result = $this->OnMineBookCategory($logEntry); break;
			case "mineBook":					$result = $this->OnMineBook($logEntry); break;
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
			case "MailItem":					$result = $this->OnMailItem($logEntry); break;
			case "VeteranXPUpdate":				$result = $this->OnNullEntry($logEntry); break;		//TODO
			case "AllianceXPUpdate":			$result = $this->OnNullEntry($logEntry); break;		//TODO
			case "TelvarUpdate":				$result = $this->OnTelvarUpdate($logEntry); break;
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
			
			case "GuildSummary":				$result = $this->OnGuildSummary($logEntry); break;
			case "GuildSale":					$result = $this->OnGuildSale($logEntry); break;
			case "GuildSaleSearchInfo":			$result = $this->OnGuildSaleSearchInfo($logEntry); break;
			case "GuildSaleSearchEntry":		$result = $this->OnGuildSaleSearchEntry($logEntry); break;
			case "GuildSaleListingEntry::Cancel":$result = $this->OnGuildSaleListingEntryCancel($logEntry); break;
			case "GuildSaleListingInfo":		$result = $this->OnGuildSaleListingInfo($logEntry); break;
			case "GuildSaleListingEntry":		$result = $this->OnGuildSaleListingEntry($logEntry); break;
			
			case "PickpocketFailed":			$result = $this->OnPickPocketFailed($logEntry); break;
			
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
		
		if ($logEntry['event'] == "mi" || $logEntry['event'] == "mineitem" || $logEntry['event'] == "mineItem")
		{
			if (!array_key_exists('gameTime', $logEntry))
			{
				$logEntry['gameTime'] = $this->currentUser['mineItemStartGameTime'];
			}
		
			if (!array_key_exists('timeStamp', $logEntry))
			{
				$logEntry['timeStamp'] = $this->currentUser['mineItemStartTimeStamp'];
			}
		}
		
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
		
		$this->currentParseFile = $logFilename;
		$this->currentParseLine = 0;
		$this->fileDuplicateCount = 0;
		
		$fileIndex = 0;
		$result = preg_match("|eso([0-9]*)\.log|", $logFilename, $matches);
		if ($result) $fileIndex = (int) $matches[1];
		
		if ($this->startFileIndex > $fileIndex)
		{
			//$this->log("\t\tSkipping file $logFilename...");
			return true;
		}
		
		$this->log("Parsing entire log file $logFilename...");
		
		$this->lastFileIndexParsed = $fileIndex;
		$this->lastFileLineParsed = 0;
				
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
		if ($this->startFileIndex == $fileIndex) $nextLineUpdate = intval($this->startFileLine/1000)*1000 + 1000;
		
		foreach ($logEntries[1] as $key => $value)
		{
			$lineCount = substr_count($value, "\n") + 1;
			$totalLineCount += $lineCount;
			$this->currentParseLine = $totalLineCount;
			
			if ($this->startFileIndex == $fileIndex && $totalLineCount < $this->startFileLine)
			{
				continue;
			}
		
			$entryLog = $this->parseLogEntry($value);
			
			if (!$this->handleLogEntry($entryLog))
			{
				$this->log("\t$totalLineCount: Failed to handle log entry!");
				++$errorCount;
			}
			
			++$entryCount;
			
			if ($totalLineCount >= $nextLineUpdate && self::SHOW_PARSE_LINENUMBERS)
			{
				$this->log("\tParsing line $totalLineCount...");
				$nextLineUpdate += 1000;
			}
		}
		
		$this->lastFileLineParsed = $totalLineCount;
		
		$this->log("\tParsed {$entryCount} log entries in {$totalLineCount} lines from file.");
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
		
		if ($this->lastFileIndexParsed > 0 && !$this->usingManualStartIndex)
		{
			$this->writeParseIndexFile($this->lastFileIndexParsed, $this->lastFileLineParsed);
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
		$currentMicroTime = microtime(true);
		$diffTime = floor(($currentMicroTime - $this->startMicroTime)*1000)/1000;
		$diffTime = number_format($diffTime, 3);
		
		print("$diffTime: $msg\n");
		
		$result = file_put_contents($this->logFilePath . self::ELP_OUTPUTLOG_FILENAME, "$diffTime: $msg\n", FILE_APPEND | LOCK_EX);
		return TRUE;
	}
	
	
	private function readParseIndexFile ()
	{
		$indexData = file_get_contents($this->logFilePath . self::ELP_PARSE_INDEXFILE);
		
		if ($indexData !== false && !$this->usingManualStartIndex)
		{
			$splitData = explode(",", $indexData);
			$this->startFileIndex = intval($splitData[0]);
			$this->startFileLine = intval($splitData[1]);
			$this->log("Starting log parsing at automatic file index {$this->startFileIndex} line {$this->startFileLine}.");
		}
		
	}
	
	
	private function writeParseIndexFile ($index, $line)
	{
		$output = "$index, $line";
		file_put_contents($this->logFilePath . self::ELP_PARSE_INDEXFILE, $output);
	}
	
	
	private function parseInputParams ()
	{
		if (array_key_exists('start', $this->inputParams))
		{
			$this->startFileIndex = (int) $this->inputParams['start'];
			$this->startFileLine = 0;
			$this->usingManualStartIndex = true;
			$this->log("Starting log parsing at manual file index {$this->startFileIndex} line 1.");
		}
		
		if (array_key_exists('startline', $this->inputParams))
		{
			$this->startFileLine = (int) $this->inputParams['startline'];
			$this->usingManualStartIndex = true;
			$this->log("Starting log parsing at manual file index {$this->startFileIndex} line {$this->startFileLine}.");
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


$output = shell_exec("ps -ef | grep -v grep | grep parseLog.php | wc -l");

if ($output >= 2)
{
	echo "ParseLog is already running...aborting!\n";
	exit;
}


$g_EsoLogParser = new EsoLogParser();
$g_EsoLogParser->ParseAllLogs();
$g_EsoLogParser->saveData();

$g_EsoLogParser->salesData->ShowParseSummary();





