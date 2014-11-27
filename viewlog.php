<?php


	// Database users, passwords and other secrets
require("/home/uesp/secrets/esolog.secrets");


class EsoLogViewer
{
	
	const PRINT_DB_ERRORS = true;
	
		// Must be same as matching value in the log parser
	const ELV_POSITION_FACTOR = 1000;
	
	public $db = null;
	public $dbReadInitialized = false;
	public $totalRowCount = 0;
	public $lastQuery = "";
	
	public $logInfos = array();
	
	public $action = "";
	public $recordType = '';
	public $recordID = -1;
	public $recordSort = '';
	public $recordSortOrder = '';
	public $recordFilter = '';
	public $recordFilterId = '';
	public $recordField = '';
	public $outputFormat = 'HTML';
	public $search = '';
	public $searchTotalCount = 0;
	public $searchTerms = array();
	public $searchResults = array();
	public $displayLimit = 100;
	public $displayStart = 0;
	public $displayRawValues = false;
	
		// TODO: Use same definitions as parseLog.php?
	const FIELD_INT = 1;
	const FIELD_STRING = 2;
	const FIELD_FLOAT = 3;
	const FIELD_POSITION = 4;
	const FIELD_INTPOSITIVE = 5;
	const FIELD_INTBOOLEAN = 6;
	const FIELD_LARGESTRING = 7;
	const FIELD_INTTRANSFORM = 8;
	const FIELD_INTID = 9;
	
	public static $FIELD_NAMES = array(
			self::FIELD_INT => "integer",
			self::FIELD_STRING => "string",
	);
	
	public static $BOOK_FIELDS = array(
			'id' => self::FIELD_INTID,
			'title' => self::FIELD_STRING,
			'body' => self::FIELD_LARGESTRING,
			'icon' => self::FIELD_STRING,
			'isLore' => self::FIELD_INTBOOLEAN,
			'skill' => self::FIELD_STRING,
			'mediumIndex' => self::FIELD_INTTRANSFORM,
			'categoryIndex' => self::FIELD_INTPOSITIVE,
			'collectionIndex' => self::FIELD_INTPOSITIVE,
			'bookIndex' => self::FIELD_INTPOSITIVE,
			'guildIndex' => self::FIELD_INTPOSITIVE,
			'logId' => self::FIELD_INTID,
	);
	
	public static $CHEST_FIELDS = array(
			'id' => self::FIELD_INTID,
			'locationId' => self::FIELD_INTID,
			'zone' => self::FIELD_STRING,
			'x' => self::FIELD_POSITION,
			'y' => self::FIELD_POSITION,
			'quality' => self::FIELD_INTTRANSFORM,
			'logId' => self::FIELD_INTID,
	);
	
	public static $ITEM_FIELDS = array(
			'id' => self::FIELD_INT,
			'name' => self::FIELD_STRING,
			'level' => self::FIELD_INT,
			'value' => self::FIELD_INT,
			'style' => self::FIELD_INTTRANSFORM,
			'trait' => self::FIELD_INTTRANSFORM,
			'quality' => self::FIELD_INTTRANSFORM,
			'locked' => self::FIELD_INTBOOLEAN,
			'type' => self::FIELD_INTTRANSFORM,
			'equipType' => self::FIELD_INTTRANSFORM,
			'craftType' => self::FIELD_INTTRANSFORM,
			'color' => self::FIELD_STRING,
			'icon' => self::FIELD_STRING,
			'link' => self::FIELD_STRING,
			'logId' => self::FIELD_INTID,
	);
	
	public static $LOCATION_FIELDS = array(
			'id' => self::FIELD_INTID,
			'type' => self::FIELD_STRING,
			'name' => self::FIELD_STRING,
			'count' => self::FIELD_INT,
			'zone' => self::FIELD_STRING,
			'x' => self::FIELD_POSITION,
			'y' => self::FIELD_POSITION,
			'rawX' => self::FIELD_FLOAT,
			'rawY' => self::FIELD_FLOAT,
			'bookId' => self::FIELD_INTID,
			'npcId' => self::FIELD_INTID,
			'questId' => self::FIELD_INTID,
			'questStageId' => self::FIELD_INTID,
			'itemId' => self::FIELD_INTID,
			'logId' => self::FIELD_INTID,
	);
	
	public static $QUEST_FIELDS = array(
			'id' => self::FIELD_INTID,
			'logId' => self::FIELD_INTID,
			'locationId' => self::FIELD_INTID,
			'zone' => self::FIELD_STRING,
			'name' => self::FIELD_STRING,
			'objective' => self::FIELD_STRING,
	);
	
	public static $QUESTSTAGE_FIELDS = array(
			'id' => self::FIELD_INTID,
			'logId' => self::FIELD_INTID,
			'questId' => self::FIELD_INTID,
			'locationId' => self::FIELD_INTID,
			'zone' => self::FIELD_STRING,
			'x' => self::FIELD_POSITION,
			'y' => self::FIELD_POSITION,
			'objective' => self::FIELD_STRING,
			'overrideText' => self::FIELD_STRING,
			'orderIndex' => self::FIELD_INT,
			'type' => self::FIELD_INT,
			'counter' => self::FIELD_INT,
			'isFail' => self::FIELD_INTBOOLEAN,
			'isPushed' => self::FIELD_INTBOOLEAN,
			'isHidden' => self::FIELD_INTBOOLEAN,
			'isComplete' => self::FIELD_INTBOOLEAN,
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
			'id' => self::FIELD_INTID,
			'logId' => self::FIELD_INTID,
			'resultItemId' => self::FIELD_INTID,
			'name' => self::FIELD_STRING,
			'level' => self::FIELD_INT,
			'type' => self::FIELD_INT,
			'quality' => self::FIELD_INT,
	);
	
	public static $INGREDIENT_FIELDS = array(
			'id' => self::FIELD_INTID,
			'logId' => self::FIELD_INTID,
			'recipeId' => self::FIELD_INTID,
			'recipeName' => self::FIELD_STRING,
			'itemId' => self::FIELD_INTID,
			'name' => self::FIELD_STRING,
			'level' => self::FIELD_INT,
			'itemLink' => self::FIELD_STRING,
			'quantity' => self::FIELD_INT,
	);
	
	public static $USER_FIELDS = array(
			'name' => self::FIELD_STRING,
			'entryCount' => self::FIELD_INT,
			'newCount' => self::FIELD_INT,
			'chestsFound' => self::FIELD_INT,
			'sacksFound' => self::FIELD_INT,
			'booksRead' => self::FIELD_INT,
			'nodesHarvested' => self::FIELD_INT,
			'itemsLooted' => self::FIELD_INT,
			'mobsKilled' => self::FIELD_INT,
			'duplicateCount' => self::FIELD_INT,
			'language' => self::FIELD_STRING,
			'enabled' => self::FIELD_INTBOOLEAN,
	);
	
	public static $MINEDITEM_FIELDS = array(
			'id' => self::FIELD_INTID,
			'logId' => self::FIELD_INTID,
			'link' => self::FIELD_STRING,
			'itemId' => self::FIELD_INT,
			'internalLevel' => self::FIELD_INT,
			'internalSubtype' => self::FIELD_INT,
			'potionData' => self::FIELD_INT,
			'name' => self::FIELD_STRING,
			'description' => self::FIELD_STRING,
			'style' => self::FIELD_INTTRANSFORM,
			'trait' => self::FIELD_INTTRANSFORM,
			'quality' => self::FIELD_INTTRANSFORM,
			'value' => self::FIELD_INT,
			'level' => self::FIELD_INT,
			'type' => self::FIELD_INTTRANSFORM,
			'equipType' => self::FIELD_INTTRANSFORM,
			'weaponType' => self::FIELD_INTTRANSFORM,
			'armorType' => self::FIELD_INTTRANSFORM,
			'craftType' => self::FIELD_INTTRANSFORM,
			'armorRating' => self::FIELD_INT,
			'weaponPower' => self::FIELD_INT,
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
			'runeType' => self::FIELD_INT,				//TODO: Transform
			'runeRank' => self::FIELD_INT,
			'bindType' => self::FIELD_INT,				//TODO: Transform
			'siegeHP' => self::FIELD_INT,
			'bookTitle' => self::FIELD_STRING,
			'craftSkillRank' => self::FIELD_INT,
			'recipeRank' => self::FIELD_INT,			//TODO: Transform?
			'recipeQuality' => self::FIELD_INT,			//TODO: Transform
			'refinedItemLink' => self::FIELD_STRING,
			'resultItemLink' => self::FIELD_STRING,
			'materialLevelDesc' => self::FIELD_STRING,
			'traitDesc' => self::FIELD_STRING,
			'traitAbilityDesc' => self::FIELD_STRING,
			'traitCooldown' => self::FIELD_INT,
			'icon' => self::FIELD_STRING,
			'isUnique' => self::FIELD_INTBOOLEAN,
			'isUniqueEquipped' => self::FIELD_INTBOOLEAN,
			'isVendorTrash' => self::FIELD_INTBOOLEAN,
			'isArmorDecay' => self::FIELD_INTBOOLEAN,
			'isConsumable' => self::FIELD_INTBOOLEAN,
	);
	
	
	public static $RECORD_TYPES = array(
			
			'book' => array(
					'displayName' => 'Books',
					'displayNameSingle' => 'Book',
					'record' => 'book',
					'table' => 'book',
					'method' => 'DoRecordDisplay',
					'sort' => 'title',
					
					'transform' => array(
							'mediumIndex' => 'GetBookMediumText',
					),
					
					'filters' => array(
							array(
								'record' => 'location',
								'field' => 'bookId',
								'thisField' => 'id',
								'displayName' => 'View Locations',
								'type' => 'filter',
							),
					),
			),
			
			'chest' => array(
					'displayName' => 'Chests',
					'displayNameSingle' => 'Chest',
					'record' => 'chest',
					'table' => 'chest',
					'method' => 'DoRecordDisplay',
					'sort' => 'quality',
					
					'transform' => array(
							'quality' => 'GetChestQualityText',
					),
					
					'filters' => array(
							array(
									'record' => 'location',
									'field' => 'id',
									'thisField' => 'locationId',
									'displayName' => 'View Location',
									'type' => 'viewRecord',
							),
					),
					'join' => array(
							'locationId' => array(
									'joinField' => 'id',
									'table' => 'location',
									'fields' => array('x', 'y', 'zone'),
							),
					),
			),
			
			'item' => array(
					'displayName' => 'Items',
					'displayNameSingle' => 'Item',
					'record' => 'item',
					'table' => 'item',
					'method' => 'DoRecordDisplay',
					'sort' => 'name',
						
					'transform' => array(
							'type' => 'GetItemTypeText',
							'style' => 'GetItemStyleText',
							'trait' => 'GetItemTraitText',
							'quality' => 'GetItemQualityText',
							'equipType' => 'GetItemEquipTypeText',
							'craftType' => 'GetItemTypeText',
					),
						
					'filters' => array(
							array(
									'record' => 'ingredient',
									'field' => 'itemId',
									'thisField' => 'id',
									'displayName' => 'View Recipes',
									'type' => 'filter',
							),
					),
			),
			
			'location' => array (
					'displayName' => 'Locations',
					'displayNameSingle' => 'Location',
					'record' => 'location',
					'table' => 'location',
					'method' => 'DoRecordDisplay',
					'sort' => 'zone',
					
					'join' => array(
					),
					
					'filters' => array(
							array(
									'record' => 'book',
									'field' => 'id',
									'thisField' => 'bookId',
									'displayName' => 'View Book',
									'type' => 'viewRecord',
							),
							array(
									'record' => 'quest',
									'field' => 'id',
									'thisField' => 'questId',
									'displayName' => 'View Quest',
									'type' => 'viewRecord',
							),
							array(
									'record' => 'npc',
									'field' => 'id',
									'thisField' => 'npcId',
									'displayName' => 'View NPC',
									'type' => 'viewRecord',
							),
							array(
									'record' => 'queststage',
									'field' => 'id',
									'thisField' => 'questStageId',
									'displayName' => 'View Quest Stage',
									'type' => 'viewRecord',
							),
					),
			),
			
			'quest' => array(
					'displayName' => 'Quests',
					'displayNameSingle' => 'Quest',
					'record' => 'quest',
					'table' => 'quest',
					'method' => 'DoRecordDisplay',
					'sort' => 'name',
						
					'transform' => array(
					),
					
					'join' => array(
							'locationId' => array(
								'joinField' => 'id',
								'table' => 'location',
								'fields' => array('zone'),
							),
					),
					
					'filters' => array(
							array(
									'record' => 'queststage',
									'field' => 'questId',
									'thisField' => 'id',
									'displayName' => 'View Stages',
									'type' => 'filter',
							),
							array(
									'record' => 'location',
									'field' => 'questId',
									'thisField' => 'id',
									'displayName' => 'View Locations',
									'type' => 'filter',
							),
					),
			),
			
			'queststage' => array(
					'message' => 'Note: Quest stages are not necessarily in the correct order yet.',
					'displayName' => 'Quest Stages',
					'displayNameSingle' => 'Quest Stage',
					'record' => 'queststage',
					'table' => 'questStage',
					'method' => 'DoRecordDisplay',
					'sort' => 'questId',
					
					'transform' => array(
					),
					
					'join' => array(
							'locationId' => array(
									'joinField' => 'id',
									'table' => 'location',
									'fields' => array('x', 'y', 'zone'),
							),
					),
					
					'filters' => array(
							array(
									'record' => 'quest',
									'field' => 'id',
									'thisField' => 'questId',
									'displayName' => 'View Quest',
									'type' => 'viewRecord',
							),
							array(
									'record' => 'location',
									'field' => 'id',
									'thisField' => 'locationId',
									'displayName' => 'View Location',
									'type' => 'viewRecord',
							),
					),
			),
			
			'recipe' => array(
					'displayName' => 'Recipes',
					'displayNameSingle' => 'Recipe',
					'record' => 'recipe',
					'table' => 'recipe',
					'method' => 'DoRecordDisplay',
					'sort' => 'name',
					
					'transform' => array(
							'quality' => 'GetItemQualityText',
					),
					
					'filters' => array(
							array(
									'record' => 'ingredient',
									'field' => 'recipeId',
									'thisField' => 'id',
									'displayName' => 'View Ingredients',
									'type' => 'filter',
							),
							array(
									'record' => 'item',
									'field' => 'id',
									'thisField' => 'resultItemId',
									'displayName' => 'View Result Item',
									'type' => 'viewRecord',
							),
					),
			),
			
			'ingredient' => array(
					'displayName' => 'Ingredients',
					'displayNameSingle' => 'Ingredient',
					'record' => 'ingredient',
					'table' => 'ingredient',
					'method' => 'DoRecordDisplay',
					'sort' => 'recipeId',
						
					'transform' => array(
					),
						
					'filters' => array(
							array(
									'record' => 'recipe',
									'field' => 'id',
									'thisField' => 'recipeId',
									'displayName' => 'View Recipe',
									'type' => 'viewRecord',
							),
							array(
									'record' => 'item',
									'field' => 'id',
									'thisField' => 'itemId',
									'displayName' => 'View Item',
									'type' => 'viewRecord',
							),
					),
					
					'join' => array(
							'recipeId' => array(
									'joinField' => 'id',
									'table' => 'recipe',
									'fields' => array('recipeName' => 'name',  'level' => 'level'),
							),
							'itemId' => array(
									'joinField' => 'id',
									'table' => 'item',
									'fields' => array('itemLink' => 'link'),
							),
					),
			),
			
			'npc' => array(
					'displayName' => 'NPCs',
					'displayNameSingle' => 'NPC',
					'record' => 'npc',
					'table' => 'npc',
					'method' => 'DoRecordDisplay',
					'sort' => 'name',
					
					'transform' => array(
					),
					
					'filters' => array(
							array(
									'record' => 'location',
									'field' => 'npcId',
									'thisField' => 'id',
									'displayName' => 'View Locations',
									'type' => 'filter',
							),
					),
					
					'join' => array(
					),
			),
			
			'user' => array(
					'displayName' => 'Users',
					'displayNameSingle' => 'User',
					'record' => 'user',
					'table' => 'user',
					'method' => 'DoRecordDisplay',
					'sort' => 'name',
						
					'transform' => array(
					),
						
					'filters' => array(
					),
			),
			
			'minedItem' => array(
					'displayName' => 'Mined Items',
					'displayNameSingle' => 'Mined Item',
					'record' => 'minedItem',
					'table' => 'minedItem',
					'method' => 'DoRecordDisplay',
					'sort' => 'itemId',
			
					'transform' => array(
							'type' => 'GetItemTypeText',
							'style' => 'GetItemStyleText',
							'trait' => 'GetItemTraitText',
							'quality' => 'GetItemQualityText',
							'equipType' => 'GetItemEquipTypeText',
							'craftType' => 'GetItemTypeText',
							'armorType' => 'GetItemArmorTypeText',
							'weaponType' => 'GetItemWeaponTypeText',
					),
			
					'filters' => array(
					),
			),
	);
	
	
	public static $SEARCH_DATA = array(
			'book' => array(
					'searchFields' => array('title', 'body'),
					'fields' => array(
							'id' => 'id',
							'title' => 'name',
					),
			),
			'item' => array(
					'searchFields' => array('name'),
					'fields' => array(
							'id' => 'id',
							'name' => 'name',
					),
			),
			'quest' => array(
					'searchFields' => array('name', 'objective'),
					'fields' => array(
							'id' => 'id',
							'name' => 'name',
					),
			),
			'questStage' => array(
					'searchFields' => array('objective', 'overrideText'),
					'fields' => array(
							'id' => 'id',
							'questId' => 'questId',
							'objective' => 'name',
					),
			),
			'npc' => array(
					'searchFields' => array('name'),
					'fields' => array(
							'id' => 'id',
							'name' => 'name',
					),
			),
			'recipe' => array(
					'searchFields' => array('name'),
					'fields' => array(
							'id' => 'id',
							'name' => 'name',
					),
			),
			'ingredient' => array(
					'searchFields' => array('name'),
					'fields' => array(
							'id' => 'id',
							'name' => 'name',
					),
			),
	);
	
	
	public static $SEARCH_FIELDS = array(
			'id' => self::FIELD_INTID,
			'type' => self::FIELD_STRING,
			'name' => self::FIELD_STRING,
	);
	
	public function __construct ()
	{
			// TODO: Static initialization?
		self::$RECORD_TYPES['book']['fields'] = self::$BOOK_FIELDS;
		self::$RECORD_TYPES['chest']['fields'] = self::$CHEST_FIELDS;
		self::$RECORD_TYPES['item']['fields'] = self::$ITEM_FIELDS;
		self::$RECORD_TYPES['location']['fields'] = self::$LOCATION_FIELDS;
		self::$RECORD_TYPES['quest']['fields'] = self::$QUEST_FIELDS;
		self::$RECORD_TYPES['queststage']['fields'] = self::$QUESTSTAGE_FIELDS;
		self::$RECORD_TYPES['npc']['fields'] = self::$NPC_FIELDS;
		self::$RECORD_TYPES['recipe']['fields'] = self::$RECIPE_FIELDS;
		self::$RECORD_TYPES['ingredient']['fields'] = self::$INGREDIENT_FIELDS;
		self::$RECORD_TYPES['user']['fields'] = self::$USER_FIELDS;
		self::$RECORD_TYPES['minedItem']['fields'] = self::$MINEDITEM_FIELDS;
		
		$this->InitDatabase();
		$this->SetInputParams();
		$this->ParseInputParams();
		$this->LoadLogInfo();
	}
	
	
	private function InitDatabase ()
	{
		global $uespEsoLogReadDBHost, $uespEsoLogReadUser, $uespEsoLogReadPW, $uespEsoLogDatabase;
		
		if ($this->dbReadInitialized) return true;
		
		$this->db = new mysqli($uespEsoLogReadDBHost, $uespEsoLogReadUser, $uespEsoLogReadPW, $uespEsoLogDatabase);
		if ($db->connect_error) return $this->ReportError("Could not connect to mysql database!");
		
		$this->dbReadInitialized = true;
		return true;
	}
	
	
	public function ReportError ($errorMsg)
	{
		print($errorMsg);
		
		if (self::PRINT_DB_ERRORS && $this->db != null && $this->db->error)
		{
			print("<p />DB Error:" . $this->db->error . "<p />");
			print("<p />Last Query:" . $this->lastQuery . "<p />");
		}
		
		return FALSE;
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
	
	
	public function GetItemTraitText ($value)
	{
		static $VALUES = array(
				-1 => "",
				18 => "Armor Divines",
				17 => "Armor Exploration",
				12 => "Armor Impenetrable",
				16 => "Armor Infused",
				20 => "Armor Intricate",
				19 => "Armor Ornate",
				13 => "Armor Reinforced",
				11 => "Armor Sturdy",
				15 => "Armor Training",
				14 => "Armor Well Fitted",
				22 => "Jewelry Arcmne",
				21 => "Jewelry Health",
				24 => "Jewelry Ornate",
				23 => "Jewelry Robust",
				0 => "None",
				2 => "Weapon Charged",
				5 => "Weapon Defending",
				4 => "Weapon Infused",
				9 => "Weapon Intricate",
				10 => "Weapon Ornate",
				1 => "Weapon Power",
				3 => "Weapon Precise",
				7 => "Weapon Sharpened",
				6 => "Weapon Training",
				8 => "Weapon Weighted",
				25 => "Nirnhoned",
				26 => "Nirnhoned",
		);
		
		$key = (int) $value;
		
		if (array_key_exists($key, $VALUES)) return $VALUES[$key];
		return "Unknown ($key)";
	}
	
	
	public function GetItemStyleText ($value)
	{
		static $VALUES = array(
				-1 => "",
				0 => "None",
				1 => "Breton",
				2 => "Redguard",
				3 => "Orc",
				4 => "Dunmer",
				5 => "Nord",
				6 => "Argonian",
				7 => "Altmer",
				8 => "Bosmer",
				9 => "Khajiit",
				10 => "Unique",
				11 => "Aldmeri Dominion",
				12 => "Ebonheart Pact",
				13 => "Daggerfall Covenant",
				14 => "Dwemer",
				15 => "Ancient Elf",
				16 => "Imperial",
				17 => "Reach",
				18 => "Bandit",
				19 => "Primitive",
				20 => "Daedric",
				21 => "Warrior Class",
				22 => "Mage Class",
				23 => "Rogue Class",
				24 => "Summoner Class",
				25 => "Marauder Class",
				26 => "Healer Class",
				27 => "Battlemage Class",
				28 => "Nightblade Class",
				29 => "Ranger Class",
				30 => "Knight Class",
				31 => "Draugr",
				32 => "Maormer",
				33 => "Akaviri",
				34 => "Imperial",
				35 => "Yokudan",
		);
		
		$key = (int) $value;
		
		if (array_key_exists($key, $VALUES)) return $VALUES[$key];
		return "Unknown ($key)";
	}
	
	
	public function GetItemQualityText ($value)
	{
		static $VALUES = array(
				-1 => "",
				0 => "Trash",
				1 => "Normal",
				2 => "Fine",
				3 => "Superior",
				4 => "Epic",
				5 => "Legendary",
		);
		
		$key = (int) $value;
		
		if (array_key_exists($key, $VALUES)) return $VALUES[$key];
		return "Unknown ($key)";
	}
	
	
	public function GetBookMediumText ($value)
	{
		static $VALUES = array(
				-1 => "",
				0 => "Yellowed Paper",
				1 => "Animal Skin",
				2 => "Rubbing Paper",
				3 => "Letter",
				4 => "Note",
				5 => "Scroll",
				6 => "Tablet",
		);
		
		$key = (int) $value;
		
		if (array_key_exists($key, $VALUES)) return $VALUES[$key];
		return "Unknown ($key)";
	}
	
	
	public function GetItemArmorTypeText ($value)
	{
		static $VALUES = array(
				-1 => "",
				0 => "None",
				1 => "Light",
				2 => "Medium",
				3 => "Heavy",
		);
	
		$key = (int) $value;
	
		if (array_key_exists($key, $VALUES)) return $VALUES[$key];
		return "Unknown ($key)";
	}
	
	
	public function GetItemWeaponTypeText ($value)
	{
		static $VALUES = array(
				-1 => "",
				0 => "None",
				1 => "Axe",
				2 => "Hammer",
				3 => "Sword",
				4 => "Two handed Sword",
				5 => "Two handed Axe",
				6 => "Two handed Hammer",
				7 => "Prop",
				8 => "Bow",
				9 => "Healing Staff",
				10 => "Rune",
				11 => "Dagger",
				12 => "Fire Staff",
				13 => "Frost Staff",
				14 => "Shield",
				15 => "Lightning Staff",
		);
	
		$key = (int) $value;
	
		if (array_key_exists($key, $VALUES)) return $VALUES[$key];
		return "Unknown ($key)";
	}
	
	
	public function GetItemTypeText ($value)
	{
		static $VALUES = array(
				-1 => "",
				11 => "additive",
				33 => "alchemy_base",
				2 => "armor",
				24 => "armor_booster",
				45 => "armor_trait",
				47 => "ava_repair",
				41 => "blacksmithing_booster",
				36 => "blacksmithing_material",
				35 => "blacksmithing_raw_material",
				43 => "clothier_booster",
				40 => "clothier_material",
				39 => "clothier_raw_material",
				34 => "collectible",
				18 => "container",
				13 => "costume",
				14 => "disguise",
				12 => "drink",
				32 => "enchanting_rune",
				25 => "enchantment_booster",
				28 => "flavoring",
				4 => "food",
				21 => "glyph_armor",
				26 => "glyph_jewelry",
				20 => "glyph_weapon",
				10 => "ingredient",
				22 => "lockpick",
				16 => "lure",
				0 => "none",
				3 => "plug",
				30 => "poison",
				7 => "potion",
				17 => "raw_material",
				31 => "reagent",
				29 => "recipe",
				8 => "scroll",
				6 => "siege",
				19 => "soul_gem",
				27 => "spice",
				44 => "style_material",
				15 => "tabard",
				9 => "tool",
				48 => "trash",
				5 => "trophy",
				1 => "weapon",
				23 => "weapon_booster",
				46 => "weapon_trait",
				42 => "woodworking_booster",
				38 => "woodworking_material",
				37 => "woodworking_raw_material",
				49 => "spellcrafting_tablet",
				50 => "mount",
				51 => "potency_rune",
				52 => "aspect_rune",
				53 => "essence_rune",
		);
		
		$key = (int) $value;
		
		if (array_key_exists($key, $VALUES)) return $VALUES[$key];
		return "Unknown ($key)";
	}
	
	public function GetItemEquipTypeText ($value)
	{
		static $VALUES = array(
				-1 => "",
				0 => "none",
				1 => "Head",
				2 => "Neck",
				3 => "Chest",
				4 => "Shoulders",
				5 => "One Hand",
				6 => "Two Hand",
				7 => "Off Hand",
				8 => "Waist",
				9 => "Legs",
				10 => "Feet",
				11 => "Costume",
				12 => "Ring",
				13 => "Hand",
				14 => "Main Hand",
		);
		
		$key = (int) $value;
		
		if (array_key_exists($key, $VALUES)) return $VALUES[$key];
		return "Unknown ($key)";
	}
	
	
	public function GetChestQualityText ($value)
	{
		static $VALUES = array(
				-1 => "",
				0 => "None",
				1 => "Simple",
				2 => "Intermediate",
				3 => "Advanced",
				4 => "Master",
				5 => "Impossible",
		);
		
		$key = (int) $value;
		
		if (array_key_exists($key, $VALUES)) return $VALUES[$key];
		return "Unknown ($key)";
	}
	
	
	public function TransformRecordValue ($recordInfo, $field, $value)
	{
		if ($this->displayRawValues) return $value;
		
		if (!array_key_exists('transform', $recordInfo)) return $value;
		if (!array_key_exists($field, $recordInfo['transform'])) return $value;
		
		$method = $recordInfo['transform'][$field];
		return $this->$method($value);
	}
	
	
	public function WritePageHeader()
	{
		if ($this->outputFormat == 'CSV') return true;
		
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
<html>
<head>
	<title>UESP:ESO Log Data Viewer</title>
	<meta http-equiv="Content-Type" content="text/html;charset=utf-8" />
	<link rel="stylesheet" href="viewlog.css" />
	<script type="text/javascript" src="viewlog.js"></script>
</head>
<body>
<?php
		return true;
	}
	
	
	public function WritePageFooter()
	{
		if ($this->outputFormat == 'CSV') return true;
		
		$lastUpdate = $this->logInfos['lastUpdate'];
		if ($lastUpdate == null) $lastUpdate = '?';
		
		$output = "<hr>\n<div class=\"elvLastUpdate\">Data last updated on $lastUpdate</div>";
		print($output);
		
		?>
</body>
</html>
	<?php
			return true;
	}
	
	
	public function GetRecordCount ($table)
	{
		$query = "SELECT COUNT(*) FROM $table;";
		$this->lastQuery = $query;
		$result = $this->db->query($query);
		
		if ($result === false)
		{
			$this->ReportError("Failed to get record count for $table!");
			return 0;
		}
		
		$row = $result->fetch_row();
		return $row[0];
	}
	
	
	public function DoHomePage ($recordInfo)
	{
?>
	<h1>ESO: Record Types</h1>
The ESO log viewer displays the raw game data for Elder Scrolls Online as collected by the <a href="http://www.uesp.net/wiki/User:Daveh/uespLog_Addon">uespLog add-on</a>. It was created to be a tool for UESP editors and patrollers to
use as part of improving and maintaining <a href="http://www.uesp.net/">UESPWiki</a>. It is not intended to be a user-friendly way to learn about the Elder Scrolls games.
If you do not understand what this information means, or how to use this webpage, then go to <a href="http://www.uesp.net/"><b>UESPWiki</b></a> for user-friendly game information.
	<ul class='elvRecordTypeList'>
<?php
	
		foreach (self::$RECORD_TYPES as $key => $value)
		{
			$query = "record=" . $value['record'];
			$displayName = $value['displayName'];
			
			$output  = "\t\t<li>";
			$output .= "<a href=\"?$query\">$displayName ";
			$output .= "(" . $this->GetRecordCount($value['table']) . " records) </a>";
			$output .= "</li>\n";
			print($output);
		}
?>
	</ul>
	
	<form method='get' action=''>
		<input type='search' name='search' value='' maxlength='64' size='32' />
		<input type='submit' value='Search...' />
	</form>
<?php
		
		return true;
	}
	
	
	public function OutputTopMenu ($recordInfo = null)
	{
		if (!$this->IsOutputHTML()) return true;
		
		$output = "<a href='viewlog.php'>Back to Home</a><br />\n";
		
		print($output);
		return true;
	}
	
	
	public function OutputRecordHeader ($recordInfo)
	{
		$this->OutputTopMenu($recordInfo);
		
		$output = "";
		
		$displayName = $recordInfo['displayName'];
		if ($this->IsOutputHTML()) $output .= "<h1>ESO: Viewing $displayName</h1>\n";
		
		if ($this->IsFiltering())
		{
			if ($this->IsOutputHTML()) $output .= "<h2>Showing for {$this->recordFilter}:{$this->recordFilterId}</h2>";
		}
		
		if ($recordInfo['message'] != null)
		{
			if ($this->IsOutputHTML()) $output .= $recordInfo['message'] ."<p />\n";
		}
		
		print($output);
		return true;
	}
	
	
	public function GetSelectQueryJoins ($recordInfo)
	{
		$query = "";
		if ($recordInfo['join'] == '') return $query;
		
		foreach ($recordInfo['join'] as $key => $value)
		{
			$table1 = $recordInfo['table'];
			$table2 = $value['table'];
			$tableId1 = $key;
			$tableId2 = $value['joinField'];
			$query .= "LEFT JOIN $table2 on $table1.$tableId1 = $table2.$tableId2 ";
		}
		
		return $query;
	}
	
	
	public function GetTablesForSelectQuery($recordInfo)
	{
		$tables = $recordInfo['table'] . ".*";
		
		if ($recordInfo['join'] == '') return $tables;
		
		foreach ($recordInfo['join'] as $key => $value)
		{
			$tables .= ', ';
			
			if ($value['fields'] == '')
			{
				$tables .= $value['table'] . ".*";
			}
			else
			{
				$isFirst = true;
				
				foreach ($value['fields'] as $fieldAlias => $fieldName)
				{
					if (!$isFirst) $tables .= ",";
					
					$tables .= " {$value['table']}.$fieldName";
					if (gettype($fieldAlias) == "string") $tables .= " as $fieldAlias";
					
					$isFirst = false;
				}
			}
		}
		
		return $tables;
	}
	
	
	public function GetSelectQuerySort ($recordInfo)
	{
		$sort = '';
		
		if ($this->recordSort == '' && $recordInfo['sort'] == '') return '';
		
		if ($this->recordSort == '')
			$sort = " ORDER BY {$recordInfo['sort']} ";
		else
			$sort = " ORDER BY {$this->recordSort} ";
		
		if ($this->recordSortOrder != '')
			$sort .= $this->recordSortOrder . ' ';
		elseif ($recordInfo['sortOrder'] != '')
			$sort .= $recordInfo['sortOrder'] . ' ';
		
		return $sort;
	}
	
	
	public function GetSelectQueryFilter ($recordInfo)
	{
		$field = $this->recordFilter;
		$id = $this->recordFilterId;
		$table = $recordInfo['table'];
		
		if ($field == '' || $id == '') return '';
		
		if (!array_key_exists($field, $recordInfo['fields'])) 
		{
			$this->ReportError("Invalid filter field '{$field}' found for table '{$recordInfo['table']}'!");
			return '';
		}
		
		$fieldType = $recordInfo['fields'][$field];
		
		switch ($fieldType)
		{
			case self::FIELD_STRING:
			case self::FIELD_LARGESTRING:
				$filter = " WHERE $table.$field='$id' ";
				break;
			default:
				$filter = " WHERE $table.$field=$id ";
				break;
		}
		
		return $filter;
	}
	
	
	public function CreateFilterLink ($record, $filter, $id, $link)
	{	
		if ($id == '' || $id <= 0) return "";
		
		$output = "<a class='elvFilterLink' href='?record={$record}&filter=$filter&filterid=$id'>$link</a>";
		
		return $output;
	}
	
	
	public function CreateFilterLinks ($recordInfo, $recordData)
	{
		$output = "";
		
		if (!array_key_exists('filters', $recordInfo)) return "";
		
		foreach ($recordInfo['filters'] as $key => $value)
		{
			if ($value['type'] == 'filter')
				$output .= $this->CreateFilterLink($value['record'], $value['field'], $recordData[$value['thisField']], $value['displayName']);
			elseif ($value['type'] == 'viewRecord')
				$output .= $this->GetViewRecordLink($value['record'], $recordData[$value['thisField']], $value['displayName']);
			else
				$output .= $this->CreateFilterLink($value['record'], $value['field'], $recordData[$value['thisField']], $value['displayName']);
		}
		
		return $output;
	}
	
	
	public function CreateSelectQuery ($recordInfo)
	{
		$tables = $this->GetTablesForSelectQuery($recordInfo);
		$table = $recordInfo['table'];
		
		$query = "SELECT SQL_CALC_FOUND_ROWS $tables FROM $table ";
		
		$query .= $this->GetSelectQueryJoins($recordInfo);
		$query .= $this->GetSelectQueryFilter($recordInfo);
		$query .= $this->GetSelectQuerySort($recordInfo);
		
		$query .= " LIMIT $this->displayLimit OFFSET $this->displayStart ";
		$query .= ";";
		
		$this->lastQuery = $query;
		return $query;
	}
	
	
	public function CreateSelectQueryID ($recordInfo, $id)
	{
		$tables = $this->GetTablesForSelectQuery($recordInfo);
		$table = $recordInfo['table'];
		
		$query = "SELECT SQL_CALC_FOUND_ROWS $tables FROM $table ";
		
		$query .= $this->GetSelectQueryJoins($recordInfo);
		$query .= " WHERE $table.id=$id";
		//$query .= " ORDER BY {$recordInfo['sort']} ";
		$query .= " LIMIT 1 ";
		$query .= ";";
		
		$this->lastQuery = $query;
		return $query;
	}
	
	
	public function GetRecordFieldHeader ($recordInfo)
	{
		if ($this->IsOutputHTML())
		{
			$output  = "\t<tr>\n";
			$output .= "\t\t<th></th>\n";
		}
		
		foreach ($recordInfo['fields'] as $key => $value)
		{
			
			if ($this->IsOutputHTML())
			{
				$sortLink = $this->GetSortRecordLink($key, $key);
				$output .= "\t\t<th>$sortLink</th>\n";
			}
			elseif ($this->IsOutputCSV())
			{
				$output .= "\"$key\",";
			}
		}
		
		if ($this->IsOutputHTML())
		{
			$output .= "\t\t<th></th>\n";
			$output .= "\t</tr>\n";
		}
		elseif ($this->IsOutputCSV())
		{
			$output .= "\n";
		}
		
		return $output;
	}
	
	
	public function CreateFieldLink ($recordType, $field, $id, $link)
	{
		$link = "<a href=\"?record=$recordType&field=$field&id=$id&action=view\">$link</a>";
		return $link;
	}
	
	
	public function SimpleFormatField ($value, $type)
	{
		if ($this->IsOutputCSV()) return $this->SimpleFormatFieldCSV($value, $type);
		
		$output = "";
		if ($value == null) return "";
		
		switch ($type)
		{
			case self::FIELD_INT:
			case self::FIELD_INTTRANSFORM:
				$output = $value;
				break;
			default:
			case self::FIELD_STRING:
			case self::FIELD_LARGESTRING:
				$output = $value;
				break;
			case self::FIELD_POSITION:
				if ($this->displayRawValues) return $value;
				$output = $value / self::ELV_POSITION_FACTOR;
				break;
			case self::FIELD_INTPOSITIVE:
				if ($this->displayRawValues) return $value;
				if ((int) $value >= 0) $output = $value;
				break;
			case self::FIELD_INTID:
				if ($this->displayRawValues) return $value;
				if ((int) $value > 0) $output = $value;
				break;
			case self::FIELD_INTBOOLEAN:
				if ($this->displayRawValues) return $value;
				$intValue = (int)$value;
				
				if ($intValue === 0)
					$output = "false";
				elseif ($intValue > 0)
					$output = "true";
				
				break;
		}
		
		return $output;
	}
	
	
	public function SimpleFormatFieldCSV ($value, $type)
	{
		$output = "";
		if ($value == null) return "";
	
		switch ($type)
		{
			case self::FIELD_INT:
			case self::FIELD_INTTRANSFORM:
				$output = $value;
				break;
			default:
			case self::FIELD_STRING:
				$escapeValue = addslashes($value);
				$output = "\"$escapeValue\"";
				break;
			case self::FIELD_LARGESTRING:
				$output = "\"...\"";
				break;
			case self::FIELD_POSITION:
				if ($this->displayRawValues) return $value;
				$output = $value / self::ELV_POSITION_FACTOR;
				break;
			case self::FIELD_INTPOSITIVE:
				if ($this->displayRawValues) return $value;
				if ((int) $value >= 0) $output = $value;
				break;
			case self::FIELD_INTID:
				if ($this->displayRawValues) return $value;
				if ((int) $value > 0) $output = $value;
				break;
			case self::FIELD_INTBOOLEAN:
				if ($this->displayRawValues) return $value;
				$intValue = (int)$value;
				
				if ($intValue === 0)
					$output = "\"false\"";
				elseif ($intValue > 0)
					$output = "\"true\"";
				
				break;
		}
	
		return $output;
	}
	
	
	public function FormatField ($value, $type, $recordType, $field, $id, $recordInfo)
	{
		if ($this->IsOutputCSV()) return $this->FormatFieldCSV($value, $type, $recordType, $field, $id, $recordInfo);
		
		$output = "";
		if ($value == null) return "";
		
		switch ($type)
		{
			case self::FIELD_INT:
				$output = $value;
				break;	
			default:
			case self::FIELD_STRING:
				$output = $value;
				break;
			case self::FIELD_LARGESTRING:
				$link = "View (". strlen($value) ." bytes)";
				$output = $this->CreateFieldLink($recordType, $field, $id, $link);
				break;
			case self::FIELD_POSITION:
				if ($this->displayRawValues) return $value;
				$output = $value / self::ELV_POSITION_FACTOR;
				break;
			case self::FIELD_INTPOSITIVE:
				if ($this->displayRawValues) return $value;
				if ((int) $value >= 0) $output = $value;
				break;
			case self::FIELD_INTID:
				if ($this->displayRawValues) return $value;
				if ((int) $value > 0) $output = $value;
				break;
			case self::FIELD_INTTRANSFORM:
				$output = $this->TransformRecordValue($recordInfo, $field, $value);
				break;
			case self::FIELD_INTBOOLEAN:
				if ($this->displayRawValues) return $value;
				$intValue = (int)$value;
				
				if ($intValue === 0)
					$output = "false";
				elseif ($intValue > 0)
					$output = "true";
				
				break;
		}
		
		return $output;
	}
	
	
	public function FormatFieldCSV ($value, $type, $recordType, $field, $id, $recordInfo)
	{
		$output = "";
		if ($value == null) return "";
		
		switch ($type)
		{
			case self::FIELD_INT:
				$output = $value;
				break;
			default:
			case self::FIELD_STRING:
				$escapeValue = addslashes($value);
				$output = "\"$escapeValue\"";
				break;
			case self::FIELD_LARGESTRING:
				$output = "\"...\"";
				break;
			case self::FIELD_POSITION:
				if ($this->displayRawValues) return $value;
				$output = $value / self::ELV_POSITION_FACTOR;
				break;
			case self::FIELD_INTPOSITIVE:
				if ($this->displayRawValues) return $value;
				if ((int) $value >= 0) $output = $value;
				break;
			case self::FIELD_INTID:
				if ($this->displayRawValues) return $value;
				if ((int) $value > 0) $output = $value;
				break;
			case self::FIELD_INTTRANSFORM:
				$output = "\"" . $this->TransformRecordValue($recordInfo, $field, $value) . "\"";
				break;
			case self::FIELD_INTBOOLEAN:
				if ($this->displayRawValues) return $value;
				$intValue = (int)$value;
				
				if ($intValue === 0)
					$output = "\"false\"";
				elseif ($intValue > 0)
					$output = "\"true\"";
				
				break;
		}
		
		return $output;
	}
	
	
	public function FormatFieldAll ($value, $type, $recordType, $field, $id, $recordInfo)
	{
		if ($this->IsOutputCSV()) return $this->FormatFieldAllCSV($value, $type, $recordType, $field, $id, $recordInfo);
		
		$output = "";
		if ($value == null) return "";
		
		switch ($type)
		{
			case self::FIELD_LARGESTRING:
				$output = "<div class='elvLargeStringView'>$value</div>";
				return $output;
		}
		
		return $this->FormatField($value, $type, $recordType, $field, $id, $recordInfo);
	}
	
	
	public function FormatFieldAllCSV ($value, $type, $recordType, $field, $id, $recordInfo)
	{
		return $this->FormatFieldCSV($value, $type, $recordType, $field, $id, $recordInfo);
	}
	
	
	public function GetPageQueryString ($ignoreFields)
	{
		$query = "";
		$isFirst = true;
		
		foreach($this->inputParams as $key => $value)
		{
			if (in_array($key, $ignoreFields)) continue;
			
			if (!$isFirst) $query .= "&";
			$query .= "$key=$value";
			$isFirst = false;
		}
		
		return $query;
	}
	
	
	public function GetNextPrevLink ($recordInfo)
	{
		$output = "";
		
		$prevStart = $this->displayStart - $this->displayLimit;
		$nextStart = $this->displayStart + $this->displayLimit;
		if ($prevStart < 0) $prevStart = 0;
		if ($nextStart < 0) $nextStart = 0;
		
		$oldQuery = $this->GetPageQueryString(array("start"));
		
		if ($this->displayStart > 0) 
			$output .= "<a href='?start=$prevStart&$oldQuery'>Prev</a> &nbsp; ";
		else
			$output .= "Prev &nbsp; ";
		
		if ($nextStart < $this->totalRowCount) 
			$output .= "<a href='?start=$nextStart&$oldQuery'>Next</a>";
		else
			$output .= "Next";
		
		$output .= "\n";
		
		return $output;
	}
	
	
	public function GetViewRecordLink ($record, $id, $link)
	{
		if ($id == '' || $id <= 0) return "";
		
		$link = "<a class='elvRecordLink' href='?action=view&record=$record&id=$id'>$link</a>";
		
		return $link;
	}
	
	
	public function GetSortRecordLink ($sortField, $link)
	{
		$oldQuery = $this->GetPageQueryString(array("sort", "sortorder"));
		
		if ($this->recordSortOrder == "DESC")
			$sortOrder = "a";
		elseif ($this->recordSortOrder == "ASC")
			$sortOrder = "d";
		else
			$sortOrder = "a";
		
		$link = "<a href='?sort=$sortField&sortorder=$sortOrder&$oldQuery'>$link</a>";
		return $link;
	}
	
	
	public function PrintRecords ($recordInfo)
	{
		if (!$this->InitDatabase()) return false;
		
		$query = $this->CreateSelectQuery($recordInfo);
		if ($query === false) return $this->reportError("Failed to create record query!");
		
		$result = $this->db->query($query);
		if ($result === false) return $this->reportError("Failed to retrieve record data!");
		
		$result2 = $this->db->query("SELECT FOUND_ROWS();");
		$rowData = $result2->fetch_row();
		$this->totalRowCount = $rowData[0];
		
		$displayCount = $result->num_rows;
		$startIndex = $this->displayStart + 1;
		$endIndex = $this->displayStart + $this->displayLimit;
		if ($endIndex > $this->totalRowCount) $endIndex = $this->totalRowCount;
		
		$output = "";
		
		if ($this->IsOutputHTML())
		{
			$output .= "Displaying $displayCount of $this->totalRowCount records from $startIndex to $endIndex.\n";
			$output .= "<br />" . $this->GetNextPrevLink($recordInfo);
			$output .= "<table border='1' cellspacing='0' cellpadding='2'>\n";
		}
		
		$output .= $this->GetRecordFieldHeader($recordInfo);
		
		$result->data_seek(0);
		
		while ( ($row = $result->fetch_assoc()) )
		{
			$id = $row['id'];
			
			if ($this->IsOutputHTML())
			{
				$output .= "\t<tr>\n";
				$output .= "\t\t<td>". $this->GetViewRecordLink($recordInfo['record'], $id, "View") ."</td>\n";
			}
			
			foreach ($recordInfo['fields'] as $key => $value)
			{
				$fmtValue = $this->FormatField($row[$key], $value, $recordInfo['record'], $key, $id, $recordInfo);
				
				if ($this->IsOutputHTML())
					$output .= "\t\t<td>" . $fmtValue . "</td>\n";
				elseif ($this->IsOutputCSV())
					$output .= "$fmtValue,";
			}
			
			if ($this->IsOutputHTML())
			{
				$output .= "\t\t<td>" . $this->CreateFilterLinks($recordInfo, $row) . "</td>\n";
				$output .= "\t</tr>\n";
			}
			elseif ($this->IsOutputCSV())
			{
				$output .= "\n";
			}
			
		}
		
		if ($this->IsOutputHTML()) 
		{
			$output .= "</table>\n";
			$output .= $this->GetNextPrevLink($recordInfo);
		}
		
		print($output);
	}
	
	
	public function DoRecordDisplay ($recordInfo)
	{
		$this->OutputRecordHeader($recordInfo);
		$this->PrintRecords($recordInfo);
		
		return true;
	}
	
	
	public function IsFiltering()
	{
		return $this->recordFilter != "" && $this->recordFilterId != "";
	}
	
	
	public function IsOutputHTML()
	{
		return $this->outputFormat == "HTML";
	}
	
	
	public function IsOutputCSV()
	{
		return $this->outputFormat == "CSV";
	}
	
	
	public function DoViewRecord ($recordInfo)
	{
		if (!$this->IsOutputHTML()) return $this->ReportError("Cannot output record in {$this->outputFormat} format!");
		
		if ($this->recordField != '') return $this->DoViewRecordField($recordInfo);
		
		$this->OutputTopMenu($recordInfo);
		$displayName = $recordInfo['displayNameSingle'];
		$id = $this->recordID;
		
		$output = "";
		$output .= "<h1>ESO: Viewing $displayName: ID#$id</h1>\n";
		
		if (!$this->InitDatabase()) return false;
		if ($this->recordID < 0) return $this->ReportError("Invalid record ID received!");
		
		$table = $recordInfo['table'];
		
		$query = $this->CreateSelectQueryID($recordInfo, $id);
		
		$result = $this->db->query($query);
		if ($result === false) return $this->ReportError("Failed to retrieve record from database!");
		if ($result->num_rows === 0) return $this->ReportError("Failed to retrieve record from database!");
		
		$result->data_seek(0);
		$row = $result->fetch_assoc();
		
		$output .= "<table border='1' cellpadding='2' cellspacing='0'>\n";
		$csvHeader = "";
		$csvData = "";
		
		foreach ($recordInfo['fields'] as $key => $value)
		{
			$rowValue = $this->FormatFieldAll($row[$key], $value, $recordInfo['record'], $key, $row['id'], $recordInfo);
			
			$output .= "\t<tr>\n";
			$output .= "\t\t<th>$key</th>\n";
			$output .= "\t\t<td>$rowValue</td>\n";
			$output .= "\t</tr>\n";
		}
		
		$output .= "</table>\n";
		$output .= $this->CreateFilterLinks($recordInfo, $row) . "<br />";
		
		print($output);
		return true;
	}
	
	
	public function DoViewRecordField ($recordInfo)
	{
		if (!$this->IsOutputHTML()) return $this->ReportError("Cannot output record field '{$this->recordField}' in {$this->outputFormat} format!");
		
		$this->OutputTopMenu($recordInfo);
		if (!$this->InitDatabase()) return false;
		
		if ($this->recordID < 0) return $this->ReportError("Invalid record ID received!");
		if ($this->recordField === '') return $this->ReportError("Invalid record field received!");
		
		$fieldType = $recordInfo['fields'][$this->recordField];
		if ($fieldType == null) return $this->ReportError("Invalid record field '$this->recordField' received!");
		
		$table = $recordInfo['table'];
		$id = $this->recordID;
		
		$query = $this->CreateSelectQueryID($recordInfo, $id);
		
		$result = $this->db->query($query);
		if ($result === false) return $this->ReportError("Failed to retrieve record from database!");
		if ($result->num_rows === 0) return $this->ReportError("Failed to retrieve record from database!");
		
		$result->data_seek(0);
		$row = $result->fetch_assoc();
		
		$displayName = $recordInfo['displayNameSingle'];
		$output  = "<h1>ESO: Viewing $displayName ($id) : {$this->recordField}</h1>\n";
		$output .= "<div class='elvRecordView'>";
		$output .= $row[$this->recordField];
		$output .= "</div>";
		
		print($output);
		
		return true;
	}
	
	
	public function SearchTable ($table, $searchData)
	{
		$searchTerms = implode('* ', $this->searchTerms) . '*';
		$limitCount = $this->displayLimit;
		$searchFields = implode(', ',$searchData['searchFields']);
		
		$query = "SELECT SQL_CALC_FOUND_ROWS * FROM $table WHERE MATCH($searchFields) AGAINST ('$searchTerms' in BOOLEAN MODE) LIMIT $limitCount;";
		$this->lastQuery = $query;
		
		$result = $this->db->query($query);
		if ($result === false) return $this->ReportError("Failed to perform search on $table table!");
		
		$result2 = $this->db->query("SELECT FOUND_ROWS();");
		$rowData = $result2->fetch_row();
		$this->searchTotalCount += $rowData[0];
		
		$result->data_seek(0);
		
		while ( ($row = $result->fetch_assoc()) )
		{
			$results = array();
			
			foreach($searchData['fields'] as $key => $value)
			{
				$results[$value] = $row[$key];
			}
			
			$results['type'] = $table;
			$this->searchResults[] = $results;
		}
		
		return true;
	}
	
	
	public function CreateSearchViewLink($result)
	{
		$output = "";
		
		switch ($result['type'])
		{
			case 'book':
				$output .= $this->GetViewRecordLink('book', $result['id'], 'View Book');
				break;
			case 'quest':
				$output .= $this->GetViewRecordLink('quest', $result['id'], 'View Quest');
				break;
			case 'questStage':
				$output .= $this->GetViewRecordLink('quest', $result['questId'], 'View Quest') . " ";
				$output .= $this->GetViewRecordLink('queststage', $result['id'], 'View Quest Stage');
				break;
			case 'item':
				$output .= $this->GetViewRecordLink('item', $result['id'], 'View Item');
				break;
			case 'npc':
				$output .= $this->GetViewRecordLink('npc', $result['id'], 'View NPC');
				break;
			case 'recipe':
				$output .= $this->GetViewRecordLink('recipe', $result['id'], 'View Recipe');
				break;
			case 'ingredient':
				$output .= $this->GetViewRecordLink('ingredient', $result['id'], 'View Ingredient');
				break;
			default:
				$output .= $this->GetViewRecordLink($result['type'], $result['id'], 'View ' . ucwords($result['type']));
				break;
		};
		
		return $output;
	}
	
	
	public function DisplaySearchResults()
	{
		$searchCount = count($this->searchResults);
		$totalCount = $this->searchTotalCount;
		
		if ($this->IsOutputHTML())
		{
			$output  = "<h1>Search Results</h1>";
			$output .= "Note: Only basic display of search results is currently supported (no paging or sorting).<p />";
			$output .= "Displaying {$searchCount} of {$totalCount} records matching \"{$this->search}\"<p />";
			$output .= "<table border='1' cellpadding='2' cellspacing='0'>\n";
			$output .= "<tr>\n";
			$output .= "\t<th></th>\n";
		}
		
		foreach (self::$SEARCH_FIELDS as $key => $value)
		{
			if ($this->IsOutputHTML())
				$output .= "\t<th>$key</th>\n";
			elseif ($this->IsOutputCSV())
				$output .= "\"$key\",";
		}
		
		if ($this->IsOutputHTML())
		{
			$output .= "\t<th></th>\n";
			$output .= "</tr>\n";
		}
		elseif ($this->IsOutputCSV())
		{
			$output .= "\n";
		}
		
		foreach ($this->searchResults as $key => $result)
		{
			if ($this->IsOutputHTML())
			{
				$output .= "<tr>\n";
				$output .= "\t<td></td>\n";
			}
			
			foreach (self::$SEARCH_FIELDS as $key => $value)
			{
				$fmtValue = $this->SimpleFormatField($result[$key], $value);
				
				if ($this->IsOutputHTML())
					$output .= "\t<td>$fmtValue</td>\n";
				elseif ($this->IsOutputCSV())
					$output .= "$fmtValue,";
			}
			
			if ($this->IsOutputHTML())
			{
				$viewLink = $this->CreateSearchViewLink($result);
				$output .= "\t<td>$viewLink</td>\n";
				$output .= "</tr>\n";
			}
			elseif ($this->IsOutputCSV())
			{
				$output .= "\n";
			}
		}
		
		if ($this->IsOutputHTML()) $output .= "</table>\n";
		
		print($output);
		return true;
	}
	
	
	public function DoSearch()
	{
		$this->OutputTopMenu();
		
		$this->searchTotalCount = 0;
		$this->searchResults = array();
		$this->searchTerms = explode(" ", $this->search);
		
		foreach (self::$SEARCH_DATA as $table => $searchData)
		{
			$this->SearchTable($table, $searchData);
		}
		
		$this->DisplaySearchResults();
		$this->WritePageFooter();
		return true;
	}
	
	
	public function Start()
	{
		$this->WriteHeaders();
		$this->WritePageHeader();
		
		if ($this->search != "") return $this->DoSearch();
		
		foreach (self::$RECORD_TYPES as $key => $value)
		{
			if ($this->recordType == $value['record'])
			{
				
				if ($this->action == "view")
				{
					$this->DoViewRecord($value);
					$this->WritePageFooter();
					return true;
				}
				
				$method = $value['method'];
				$this->$method($value);
				$this->WritePageFooter();
				return true;
			}
		}
		
		$this->DoHomePage(null);
		$this->WritePageFooter();
		
		return true;
	}
	
	
	private function ParseInputParams ()
	{
		if (array_key_exists('record', $this->inputParams)) $this->recordType = $this->db->real_escape_string($this->inputParams['record']);
		if (array_key_exists('search', $this->inputParams)) $this->search = $this->db->real_escape_string($this->inputParams['search']);
		if (array_key_exists('format', $this->inputParams)) $this->outputFormat = strtoupper($this->db->real_escape_string($this->inputParams['format']));
		if (array_key_exists('field', $this->inputParams)) $this->recordField = $this->db->real_escape_string($this->inputParams['field']);
		if (array_key_exists('id', $this->inputParams)) $this->recordID = $this->db->real_escape_string($this->inputParams['id']);
		if (array_key_exists('action', $this->inputParams)) $this->action = $this->db->real_escape_string($this->inputParams['action']);
		if (array_key_exists('start', $this->inputParams)) $this->displayStart = (int) $this->inputParams['start'];
		if (array_key_exists('sort', $this->inputParams)) $this->recordSort = $this->db->real_escape_string($this->inputParams['sort']);
		if (array_key_exists('filter', $this->inputParams)) $this->recordFilter = $this->db->real_escape_string($this->inputParams['filter']);
		if (array_key_exists('filterid', $this->inputParams)) $this->recordFilterId = $this->db->real_escape_string($this->inputParams['filterid']);
		
		if (array_key_exists('raw', $this->inputParams))
		{
			$raw = $this->inputParams['raw'];
			
			if ($raw === "true" || (int)$raw != 0)
				$this->displayRawValues = true;
			else
				$this->displayRawValues = false;
		}
		
		if (array_key_exists('sortorder', $this->inputParams))
		{
			switch ($this->inputParams['sortorder'])
			{
				default:
				case 'a':
				case 'A':
					$this->recordSortOrder = 'ASC';
					break;
				case 'd':
				case 'D':
					$this->recordSortOrder = 'DESC';
					break;
			}
		}
		
		if ($this->outputFormat == 'CSV')
		{
			$this->displayLimit = 10000000;
		}
		
		return true;
	}
	
	
	private function SetInputParams ()
	{
		global $argv;
		$this->inputParams = $_REQUEST;
		
			// Add command line arguments to input parameters for testing
		if ($argv !== null)
		{
			foreach ($argv as $arg)
			{
				$e = explode("=", $arg);
				
				if(count($e) == 2)
					$this->inputParams[$e[0]] = $e[1];
				else
					$this->inputParams[$e[0]] = 0;
			}
		}
	}
	
	
	public function WriteHeaders ()
	{
		header("Expires: 0");
		header("Pragma: no-cache");
		header("Cache-Control: no-cache, no-store, must-revalidate");
		header("Pragma: no-cache");
		
		if ($this->IsOutputCSV())
			header("content-type: text/plain");
		else
			header("content-type: text/html");
	}
	
};


$g_EsoLogViewer = new EsoLogViewer();
$g_EsoLogViewer->Start();


?>