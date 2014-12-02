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
 *
 * 
	SKILL_TYPE_NONE = 0
	SKILL_TYPE_CLASS = 1
	SKILL_TYPE_WEAPON = 2
	SKILL_TYPE_ARMOR = 3
	SKILL_TYPE_WORLD = 4
	SKILL_TYPE_GUILD = 5
	SKILL_TYPE_AVA = 6
	SKILL_TYPE_RACIAL = 7
	SKILL_TYPE_TRADESKILL = 8
	
	1 , 1, Aedric Spear
	1 , 2, Dawn's Wrath
	1 , 3, Restoring Light
	2 , 1, Two Handed
	2 , 2, One Hand and Shield
	2 , 3, Dual Wield
	2 , 4, Bow
	2 , 5, Destruction Staff
	2 , 6, Restoration Staff
	3 , 1, Light Armor
	3 , 2, Medium Armor
	3 , 3, Heavy Armor
	4 , 1, Soul Magic
	5 , 1, Fighters  Guild
	5 , 2, Mages Guild
	6 , 1, Assault
	6 , 2, Support
	7 , 1, Breton Skills
	8 , 1, Alchemy
	8 , 2, Blacksmithing
	8 , 3, Clothing
	8 , 4, Enchanting
	8 , 5, Provisioning
	8 , 6, Woodworking
	
	Locations
		Book = Bookid
		Skyshard
		Chest / Heavy Sack
		Resource Node
		NPC = id
		Crafting Station
		Fishing Hole
		Item = id
		Quest = id
		
		Fields
			bookId
			npcId
			itemId
			questId
			type
			name
			
		event{SlotUpdate}  
			icon{/esoui/art/icons/crafting_flower_wormwood_r1.dds}
			slot{50}
			bag{1}
			qnt{13}
			craftType{31}
			quality{2}
			locked{false}
			trait{0}
			equipType{0}
			itemStyle{0}
			itemLink{|H2DC50E:item:30159:1:1:0:0:0:0:0:0:0:0:0:0:0:0:0:0:0:0:0|hwormwood|h}
			type{31}
			value{2}
			
		event{LootGained}
			itemLink{|H2DC50E:item:30159:1:16:0:0:0:0:0:0:0:0:0:0:0:0:0:0:0:0:0|hwormwood|h}
			lootType{1}
			qnt{1}
			lastTarget{Wormwood}

	Items:
		id
		link
		name
		style
		value
		level
		icon
		type
		equipType
		craftType
		trait
		style
		quality
		color
		
		
	Enchantment:
		id
		type
		description
	
	Base Item:
		id
		type
		equipType
		craftType
		trait
		style?
	
	quest
		id
		logId
		locationId
		name
		objective
		
	questStage
		id
		logId
		questId
		locationId
		objective
		overrideText
		index
		type
		counter
		isHidden
		isFail
		isPushed
		isComplete
		
	npc
		logId
		locationId
		name
		level
		gender
		difficulty
		
	 Recipe
	 	id
	 	logId
	 	resultItemId
	 	name
	 	level
	 	type
	 	quality	
	 	
	 Ingredient
	 	id
	 	logId
	 	recipeId
	 	itemId
	 	name
	 	quantity
		
 */

	// Database users, passwords and other secrets
require("/home/uesp/secrets/esolog.secrets");


class EsoLogParser
{
	const ELP_INPUT_LOG_PATH = "";
	const ELP_INDEX_FILENAME = "esolog.index";
	const ELP_OUTPUTLOG_FILENAME = "parser.log";
	
	const TREASURE_DELTA_TIME = 4000;
	const BOOK_DELTA_TIME = 4000;
	
	const ELP_POSITION_FACTOR = 1000;	// Converts floating point position in log to integer value for db
	const START_MINEITEM_TIMESTAMP = 4743729922978086912;
	
	public $db = null;
	private $dbReadInitialized  = false;
	private $dbWriteInitialized = false;
	public $lastQuery = "";
	
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
			'traitAbility' => 'traitAbilityDesc',
			'matLevelDesc' => 'materialLevelDesc',
			'traitCooldown' => 'traitCooldown',
			'isUnique' => 'isUnique',
			'isUniqueEquipped' => 'isUniqueEquipped',
			'isVendorTrash' => 'isVendorTrash',
			'isArmorDecay' => 'isArmorDecay',
			'isConsumable' => 'isConsumable',
			//runeName
			//ingrName1-N
	);
	
	
	public function __construct ()
	{
		$this->initDatabaseWrite();
		$this->readIndexFile();
		$this->currentLogFilename = $this->getCurrentLogFilename();
		$this->setInputParams();
		$this->parseInputParams();
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
	
	
	public function createNewRecordID ($id, $idField, $fieldDef)
	{
		$record = $this->createNewRecord($fieldDef);
		$record[$idField] = $id;
		
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
		$query = $this->createSelectQuery($table, $idField, $id, $fieldDef);
		if ($query === false) return false;
		
		$result = $this->db->query($query);
		if ($result === FALSE) return $this->reportError("Failed to load record $id from $table table!");
		
		if ($result->num_rows === 0) return $this->createNewRecordID($idField, $id, $fieldDef);
		
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
			if ($key === 'id') continue;
			
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
	
	
	public function LoadMinedItemID ($id)
	{
		$minedItem = $this->loadRecord('minedItem', 'id', $id, self::$MINEDITEM_FIELDS);
		if ($minedItem === false) return false;
	
		return $minedItem;
	}
	
	
	public function LoadMinedItemLink ($itemLink)
	{
		$minedItem = $this->loadRecord('minedItem', 'link', $itemLink, self::$MINEDITEM_FIELDS);
		if ($minedItem === false) return false;
		
		return $minedItem;
	}
	
	
	public function SaveMinedItem (&$record)
	{
		return $this->saveRecord('minedItem', $record, 'id', self::$MINEDITEM_FIELDS);
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
						booksRead INTEGER NOT NULL,
						nodesHarvested INTEGER NOT NULL,
						itemsLooted INTEGER NOT NULL,
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
						INDEX index_quest (questId),
						FULLTEXT(objective),
						FULLTEXT(overrideText)
					);";
		
		$this->lastQuery = $query;
		$result = $this->db->query($query);
		if ($result === FALSE) return $this->reportError("Failed to create questStage table!");
		
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
		
		$query = "CREATE TABLE IF NOT EXISTS minedItem (
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
			enchantName TINYTEXT,
			enchantDesc TEXT NOT NULL,
			maxCharges INTEGER NOT NULL DEFAULT -1,
			abilityName TINYTEXT,
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
			setBonusDesc1 TINYTEXT NOT NULL,
			setBonusDesc2 TINYTEXT NOT NULL,
			setBonusDesc3 TINYTEXT NOT NULL,
			setBonusDesc4 TINYTEXT NOT NULL,
			setBonusDesc5 TINYTEXT NOT NULL,
			glyphMinLevel SMALLINT NOT NULL DEFAULT -1,
			glyphMaxLevel SMALLINT NOT NULL DEFAULT -1,
			runeType TINYINT NOT NULL DEFAULT -1,
			runeRank TINYINT NOT NULL DEFAULT -1,
			bindType TINYINT NOT NULL DEFAULT -1,
			siegeHP INTEGER NOT NULL DEFAULT -1,
			bookTitle TINYTEXT,
			craftSkillRank TINYINT NOT NULL DEFAULT -1,
			recipeRank TINYINT NOT NULL DEFAULT -1,
			recipeQuality TINYINT NOT NULL DEFAULT -1,
			refinedItemLink TINYTEXT NOT NULL,
			resultItemLink TINYTEXT NOT NULL,
			materialLevelDesc TINYTEXT NOT NULL,
			traitDesc TINYTEXT,
			traitAbilityDesc TINYTEXT NOT NULL,
			traitCooldown TINYINT NOT NULL DEFAULT -1,
			isUnique BIT NOT NULL DEFAULT 0,
			isUniqueEquipped BIT NOT NULL DEFAULT 0,
			isVendorTrash BIT NOT NULL DEFAULT 0,
			isArmorDecay BIT NOT NULL DEFAULT 0,
			isConsumable BIT NOT NULL DEFAULT 0,
			icon TINYTEXT NOT NULL,
			PRIMARY KEY (id),
			INDEX index_link (link(64)),
			INDEX index_itemId (itemId),
			INDEX index_enchantId (enchantId),
			FULLTEXT(name),
			FULLTEXT(description),
			FULLTEXT(setName),
			FULLTEXT(abilityName),
			FULLTEXT(abilityDesc),
			FULLTEXT(setBonusDesc1, setBonusDesc2, setBonusDesc3, setBonusDesc4, setBonusDesc5),
			FULLTEXT(bookTitle)
		);";
		
		$this->lastQuery = $query;
		$result = $this->db->query($query);
		if ($result === FALSE) return $this->reportError("Failed to create minedItem table!");
		
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
		$this->users[$userName]['booksRead'] = 0;
		$this->users[$userName]['itemsLooted'] = 0;
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
		$this->users[$userName]['mineItemStartGameTime'] = 1;
		$this->users[$userName]['mineItemStartTimeStamp'] = 1;
		$this->users[$userName]['__lastChestFoundGameTime'] = 0;
		$this->users[$userName]['__lastSackFoundGameTime'] = 0;
		$this->users[$userName]['__lastBookGameTime'] = 0;
		
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
		settype($row['sacksFound'], "integer");
		settype($row['booksRead'], "integer");
		settype($row['itemsLooted'], "integer");
		settype($row['nodesHarvested'], "integer");
		settype($row['mobsKilled'], "integer");
		
		$this->users[$userName] = array();
		$this->users[$userName]['name'] = $userName;
		$this->users[$userName]['entryCount'] = $row['entryCount'];
		$this->users[$userName]['errorCount'] = $row['errorCount'];
		$this->users[$userName]['duplicateCount'] = $row['duplicateCount'];
		$this->users[$userName]['newCount'] = $row['newCount'];
		$this->users[$userName]['chestsFound'] = $row['chestsFound'];
		$this->users[$userName]['sacksFound'] = $row['sacksFound'];
		$this->users[$userName]['booksRead'] = $row['booksRead'];
		$this->users[$userName]['itemsLooted'] = $row['itemsLooted'];
		$this->users[$userName]['nodesHarvested'] = $row['nodesHarvested'];
		$this->users[$userName]['mobsKilled'] = $row['mobsKilled'];
		$this->users[$userName]['enabled'] = ($row['enabled'] != 0);
		$this->users[$userName]['language'] = $row['language'];
		$this->users[$userName]['__dirty'] = false;
		
		$this->users[$userName]['lastBookRecord'] = null;
		$this->users[$userName]['lastBookLogEntry'] = null;
		$this->users[$userName]['lastMinedItemLogEntry'] = null;
		$this->users[$userName]['mineItemStartGameTime'] = 1;
		$this->users[$userName]['mineItemStartTimeStamp'] = 1;
		
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
		$query .= ", chestsFound={$user['chestsFound']}";
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
			if ($logEntry[$field] == '') return $this->reportLogParseError("Found empty $field in log entry!");
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
	
		if ($this->skipCheckTables) return true;
		return $this->checkTables();
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
	
		//if ($this->skipCheckTables) return true;
		//return $this->checkTables();
		return true;
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
	
	
	public function OnLootGainedEntry ($logEntry)
	{
		//event{LootGained}  itemLink{|H2DC50E:item:30159:1:16:0:0:0:0:0:0:0:0:0:0:0:0:0:0:0:0:0|hwormwood|h}  lootType{1}  qnt{1}
		//lastTarget{Wormwood}  zone{Wayrest}  x{0.50276911258698}  y{0.073295257985592}  gameTime{65831937}  timeStamp{4743645111026450432}  userName{Reorx}  end{}
		
		++$this->currentUser['itemsLooted'];
		$this->currentUser['__dirty'] = true;
		
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
		//|H0:item:ID:SUBTYPE:LEVEL:ENCHANTID:ENCHANTSUBTYPE:ENCHANTLEVEL:0:0:0:0:0:0:0:0:0:STYLE:CRAFTED:BOUND:CHARGES:POTIONEFFECT|hNAME|h
		$matches = array();
		
		//$result = preg_match('/\|H([A-Za-z0-9]*)\:item\:([0-9]*)\:([0-9]*)\:([0-9]*)\:(.*?)\|h([a-zA-Z0-9 _\(\)\'\-]*)(.*?)\|h/', $itemLink, $matches);
		$result = preg_match('/\|H(?P<color>[A-Za-z0-9]*)\:item\:(?P<itemId>[0-9]*)\:(?P<subtype>[0-9]*)\:(?P<level>[0-9]*)\:(?P<enchantId>[0-9]*)\:(?P<enchantSubtype>[0-9]*)\:(?P<enchantLevel>[0-9]*)\:(.*?)\:(?P<style>[0-9]*)\:(?P<crafted>[0-9]*)\:(?P<bound>[0-9]*)\:(?P<charges>[0-9]*)\:(?P<potionData>[0-9]*)\|h(?P<name>[a-zA-Z0-9 _\(\)\'\-]*)(?P<nameCode>.*?)\|h/', $itemLink, $matches);
		
		if ($result == 0) 
		{
			$this->ReportLogParseError("Error parsing item link '$itemLink'!");
			$matches['name'] = $itemLink;
			$matches['error'] = true;
			return $matches;
		}
		
		/*
		$result = array();
		
		$result['color'] = $matches[1];
		$result['id'] = $matches[2];
		$result['unknown'] = $matches[3];
		$result['level'] = $matches[4];
		$result['data'] = $matches[2] . ':' . $matches[3] . ':' . $matches[5];
		$result['name'] = $matches[6];
		$result['namecode'] = $matches[7] == null ? '' : $matches[7]; */
		
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
		if ($this->startsWith($itemLink, "|H")) return $this->FindItemNameWithNoLink($itemName);
		
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
			$logEntry['level'] = strval(intval($logEntry['reqVetLevel']) + 49);
		}
		elseif (array_key_exists('reqLevel', $logEntry) && $logEntry['reqLevel'] > 0)
		{
			$logEntry['level'] = $logEntry['reqLevel'];
		}
		
		if (!array_key_exists('setMaxEquipCount', $logEntry))
		{
			$logEntry['setMaxEquipCount'] = 0; //(2 items)
			$highestSetDesc = "";
			
			if (array_key_exists('setDesc1', $logEntry)) $highestSetDesc = $logEntry['setDesc1'];
			if (array_key_exists('setDesc2', $logEntry)) $highestSetDesc = $logEntry['setDesc2'];
			if (array_key_exists('setDesc3', $logEntry)) $highestSetDesc = $logEntry['setDesc3'];
			if (array_key_exists('setDesc4', $logEntry)) $highestSetDesc = $logEntry['setDesc4'];
			if (array_key_exists('setDesc5', $logEntry)) $highestSetDesc = $logEntry['setDesc5'];
			
			if ($highestSetDesc != "")
			{
				$matches = array();
				$result = preg_match("/\(([0-9]+) items\)/", $highestSetDesc, $matches);
				if ($result) $logEntry['setMaxEquipCount'] = (int) $matches[1];
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
		
		$result = true;
		if ($minedItem['__dirty']) $result &= $this->SaveMinedItem($minedItem);
		
		$this->currentUser['lastMinedItemLogEntry'] = $mergedLogEntry;
		//print("Found mined item $itemLink\n");
		return $result;
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
			case "QuestRemoved":				$result = $this->OnQuestRemoved($logEntry); break;
			case "QuestObjComplete":			$result = $this->OnNullEntry($logEntry); break;
			case "QuestOptionalStep":			$result = $this->OnNullEntry($logEntry); break;
			case "QuestCompleteExperience":		$result = $this->OnNullEntry($logEntry); break;
			case "QuestMoney":					$result = $this->OnNullEntry($logEntry); break;
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
			case "Achievement":					$result = $this->OnNullEntry($logEntry); break;
			case "Category":					$result = $this->OnNullEntry($logEntry); break;
			case "Subcategory":					$result = $this->OnNullEntry($logEntry); break;
			case "Achievement::Start":			$result = $this->OnNullEntry($logEntry); break;
			case "Achievement::End":			$result = $this->OnNullEntry($logEntry); break;
			case "ExperienceUpdate":			$result = $this->OnExperienceUpdate($logEntry); break;
			case "mineItem::AutoStart":			$result = $this->OnMineItemStart($logEntry); break;
			case "mineItem::Start":				$result = $this->OnMineItemStart($logEntry); break;
			case "mineItem::AutoEnd":			$result = $this->OnMineItemEnd($logEntry); break;
			case "mineItem::End":				$result = $this->OnMineItemEnd($logEntry); break;
			case "mineitem":					$result = $this->OnMineItem($logEntry); break;
			case "mineItem":					$result = $this->OnMineItem($logEntry); break;
			case "mi":							$result = $this->OnMineItemShort($logEntry); break;
			case "ItemLink":					$result = $this->OnNullEntry($logEntry); break;		//TODO
			case "MailItem":					$result = $this->OnNullEntry($logEntry); break;		//TODO
			case "VeteranXPUpdate":				$result = $this->OnNullEntry($logEntry); break;		//TODO
			case "AllianceXPUpdate":			$result = $this->OnNullEntry($logEntry); break;		//TODO
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
		$this->reportError("{$this->currentParseLine}: {$errorMsg}");
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

?>

