<?php

	/* Database users, passwords and other secrets */
require("/home/uesp/secrets/esolog.secrets");

	/* Common library */
require("esoCommon.php");


class EsoLogViewer
{
	const PRINT_DB_ERRORS = true;
	const SHOW_QUERY = true;
	
		/* Which PTS version to enable. Blank for none */
	const ENABLE_PTS_VERSION = "46";
	
		// Must be same as matching value in the log parser
	const ELV_POSITION_FACTOR = 1000;
	
	const GAME_ICON_URL = UESP_ESO_ICON_URL;
	
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
	public $rawSearch = '';
	public $search = '';
	public $searchType = '';
	public $searchTotalCount = 0;
	public $searchTerms = array();
	public $searchResults = array();
	public $displayLimit = 1000;
	public $displayStart = 0;
	public $displayRawValues = false;
	public $PTS_VERSION_NAME = "Unknown";
	
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
	const FIELD_TEXTTRANSFORM = 10;
	const FIELD_GAMEICON = 11;
	const FIELD_COLORBOX = 12;
	const FIELD_INTYESBLANK = 6;
	
	
	public static $FIELD_NAMES = array(
			self::FIELD_INT => "integer",
			self::FIELD_STRING => "string",
	);
	
	public static $BOOK_FIELDS = array(
			'id' => self::FIELD_INTID,
			'title' => self::FIELD_STRING,
			'body' => self::FIELD_LARGESTRING,
			'icon' => self::FIELD_GAMEICON,
			'isLore' => self::FIELD_INTYESBLANK,
			'skill' => self::FIELD_STRING,
			'mediumIndex' => self::FIELD_INTTRANSFORM,
			'categoryIndex' => self::FIELD_INTPOSITIVE,
			'collectionIndex' => self::FIELD_INTPOSITIVE,
			'bookIndex' => self::FIELD_INTPOSITIVE,
			'guildIndex' => self::FIELD_INTPOSITIVE,
			'bookId' => self::FIELD_INT,
	);
	
	public static $CHEST_FIELDS = array(
			'id' => self::FIELD_INTID,
			'locationId' => self::FIELD_INTID,
			'zone' => self::FIELD_STRING,
			'x' => self::FIELD_POSITION,
			'y' => self::FIELD_POSITION,
			'name' => self::FIELD_STRING,
			'quality' => self::FIELD_INTTRANSFORM,
	);
	
	public static $ITEM_FIELDS = array(
			'id' => self::FIELD_INT,
			'name' => self::FIELD_STRING,
			'level' => self::FIELD_INT,
			'value' => self::FIELD_INT,
			'style' => self::FIELD_INTTRANSFORM,
			'trait' => self::FIELD_INTTRANSFORM,
			'quality' => self::FIELD_INTTRANSFORM,
			'type' => self::FIELD_INTTRANSFORM,
			'equipType' => self::FIELD_INTTRANSFORM,
			'craftType' => self::FIELD_INTTRANSFORM,
			'icon' => self::FIELD_GAMEICON,
			'link' => self::FIELD_STRING,
	);
	
	public static $LOGINFO_FIELDS = array(
			'id' => self::FIELD_STRING,
			'value' => self::FIELD_STRING,
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
			'firstTime' => self::FIELD_INTTRANSFORM,
			'lastTime' => self::FIELD_INTTRANSFORM,
	);
	
	public static $OLDQUEST_FIELDS = array(
			'id' => self::FIELD_INTID,
			'locationId' => self::FIELD_INTID,
			'zone' => self::FIELD_STRING,
			'name' => self::FIELD_STRING,
			'objective' => self::FIELD_STRING,
	);
	
	public static $CROWNSTOREITEM_FIELDS = array(
			'id' => self::FIELD_INTID,
			'name' => self::FIELD_STRING,
			//'description' => self::FIELD_STRING,
			'category' => self::FIELD_STRING,
			'subCategory' => self::FIELD_STRING,
			'price' => self::FIELD_STRING,
			'esoPlusPrice' => self::FIELD_STRING,
			'saleTimestamp' => self::FIELD_INTTRANSFORM,
			'lastUpdated' => self::FIELD_INTTRANSFORM,
			'isNew' => self::FIELD_INT,
			'imageUrl' => self::FIELD_TEXTTRANSFORM,
	);
	
	public static $QUEST_FIELDS = array(
			'id' => self::FIELD_INTID,
			'internalId' => self::FIELD_INTID,
			//'locationId' => self::FIELD_INTID,
			'zone' => self::FIELD_STRING,
			'locationZone' => self::FIELD_STRING,
			'name' => self::FIELD_STRING,
			'level' => self::FIELD_INT,
			'type' => self::FIELD_INTTRANSFORM,
			'repeatType' => self::FIELD_INTTRANSFORM,
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
			'uniqueId' => self::FIELD_INT,
	);
	
	public static $QUESTSTEP_FIELDS = array(
			'id' => self::FIELD_INTID,
			//'locationId' => self::FIELD_INTID,
			'zone' => self::FIELD_STRING,
			'x' => self::FIELD_POSITION,
			'y' => self::FIELD_POSITION,
			'questId' => self::FIELD_INTID,
			'uniqueId' => self::FIELD_INT,
			'stageIndex' => self::FIELD_INT,
			'stepIndex' => self::FIELD_INT,
			'text' => self::FIELD_STRING,
			'journalText' => self::FIELD_STRING,
			'type' => self::FIELD_INTTRANSFORM,
			'overrideText' => self::FIELD_STRING,
			'visibility' => self::FIELD_INTTRANSFORM,
			'numConditions' => self::FIELD_INT,
			'count' => self::FIELD_INT,
	);
	
	public static $QUESTCONDITION_FIELDS = array(
			'id' => self::FIELD_INTID,
			'logId' => self::FIELD_INTID,
			'questId' => self::FIELD_INTID,
			'uniqueId' => self::FIELD_INT,
			'questStepId' => self::FIELD_INTID,
			'stageIndex' => self::FIELD_INT,
			'stepIndex' => self::FIELD_INT,
			'conditionIndex' => self::FIELD_INT,
			'text' => self::FIELD_STRING,
			'maxValue' => self::FIELD_INT,
			'type1' => self::FIELD_INTTRANSFORM,
			'type2' => self::FIELD_INTTRANSFORM,
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
			'type' => self::FIELD_INTTRANSFORM,
			'name' => self::FIELD_STRING,
			'quantity' => self::FIELD_INT,
			'icon' => self::FIELD_STRING,
			'quality' => self::FIELD_INTTRANSFORM,
			'itemType' => self::FIELD_INTTRANSFORM,
			'itemId' => self::FIELD_INT,
			'collectId' => self::FIELD_INT,
			'count' => self::FIELD_INT,
	);
	
	public static $QUESTGOLDREWARD_FIELDS = array(
			'id' => self::FIELD_INT,
			'logId' => self::FIELD_INT,
			'questName' => self::FIELD_STRING,
			'gold' => self::FIELD_INT,
			'playerLevel' => self::FIELD_INT,
			'uniqueId' => self::FIELD_INT,
			'questId' => self::FIELD_INT,
	);
	
	public static $QUESTXPREWARD_FIELDS = array(
			'id' => self::FIELD_INT,
			'logId' => self::FIELD_INT,
			'questName' => self::FIELD_STRING,
			'experience' => self::FIELD_INT,
			'playerLevel' => self::FIELD_INT,
			'uniqueId' => self::FIELD_INT,
			'questId' => self::FIELD_INT,
	);
	
	public static $QUESTITEM_FIELDS = array(
			'id' => self::FIELD_INTID,
			'logId' => self::FIELD_INTID,
			'questId' => self::FIELD_INTID,
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
		
	public static $OLDQUESTSTAGE_FIELDS = array(
			'id' => self::FIELD_INTID,
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
			'isFail' => self::FIELD_INTYESBLANK,
			'isPushed' => self::FIELD_INTYESBLANK,
			'isHidden' => self::FIELD_INTYESBLANK,
			'isComplete' => self::FIELD_INTYESBLANK,
	);
	
	public static $NPC_FIELDS = array(
			'id' => self::FIELD_INT,
			'name' => self::FIELD_STRING,
			'level' => self::FIELD_INT,
			'gender' => self::FIELD_INT,
			'maxHealth' => self::FIELD_INT,
			'difficulty' => self::FIELD_INT,
			'ppClass' => self::FIELD_STRING,
			'ppDifficulty' => self::FIELD_INT,
			'count' => self::FIELD_INT,
			'reaction' => self::FIELD_INTTRANSFORM,
			'unitType' => self::FIELD_INT,
	);
	
	public static $NPC_LOCATION_FIELDS = array(
			'npcId' => self::FIELD_INT,
			'name' => self::FIELD_STRING,
			'zone' => self::FIELD_STRING,
			'locCount' => self::FIELD_INT,
			'maxHealth' => self::FIELD_INT,
			'unitType' => self::FIELD_INT,
	);
	
	public static $LOOTSOURCE_FIELDS = array(
			'id' => self::FIELD_INT,
			'name' => self::FIELD_STRING,
			'count' => self::FIELD_INT,
	);
	
	public static $NPCLOOT_FIELDS = array(
			'id' => self::FIELD_INT,
			'name' => self::FIELD_STRING,
			'zone' => self::FIELD_STRING,
			'icon' => self::FIELD_GAMEICON,
			'itemLink' => self::FIELD_TEXTTRANSFORM,
			'itemName' => self::FIELD_STRING,
			'quality' => self::FIELD_INT,
			'trait' => self::FIELD_INTTRANSFORM,
			'value' => self::FIELD_INT,
			'itemType' => self::FIELD_INTTRANSFORM,
			'itemId' => self::FIELD_INT,
			'qnt' => self::FIELD_INT,
			'count' => self::FIELD_INT,
	);
	
	public static $RECIPE_FIELDS = array(
			'id' => self::FIELD_INTID,
			'resultItemId' => self::FIELD_INTID,
			'name' => self::FIELD_STRING,
			'level' => self::FIELD_INT,
			'type' => self::FIELD_INT,
			'quality' => self::FIELD_INT,
	);
	
	public static $INGREDIENT_FIELDS = array(
			'id' => self::FIELD_INTID,
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
			'trovesFound' => self::FIELD_INT,
			'safeBoxesFound' => self::FIELD_INT,
			'booksRead' => self::FIELD_INT,
			'nodesHarvested' => self::FIELD_INT,
			'itemsLooted' => self::FIELD_INT,
			'itemsStolen' => self::FIELD_INT,
			'mobsKilled' => self::FIELD_INT,
			'duplicateCount' => self::FIELD_INT,
			'language' => self::FIELD_STRING,
			'enabled' => self::FIELD_INTYESBLANK,
	);
	
	public static $MINEDITEM_FIELDS = array(
			//'id' => self::FIELD_INTID,
			'link' => self::FIELD_INTTRANSFORM,
			'itemId' => self::FIELD_INT,
			'internalLevel' => self::FIELD_INT,
			'internalSubtype' => self::FIELD_INT,
			'potionData' => self::FIELD_INT,
			'name' => self::FIELD_INTTRANSFORM,
			'icon' => self::FIELD_GAMEICON,
			'description' => self::FIELD_TEXTTRANSFORM,
			'tags' => self::FIELD_STRING,
			'style' => self::FIELD_INTTRANSFORM,
			'trait' => self::FIELD_INTTRANSFORM,
			'quality' => self::FIELD_INTTRANSFORM,
			'value' => self::FIELD_INT,
			'level' => self::FIELD_INT,
			'type' => self::FIELD_INTTRANSFORM,
			'specialType' => self::FIELD_INTTRANSFORM,
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
			'enchantDesc' => self::FIELD_TEXTTRANSFORM,
			'maxCharges' => self::FIELD_INT,
			'abilityName' => self::FIELD_STRING,
			'abilityDesc' => self::FIELD_TEXTTRANSFORM,
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
			'setBonusDesc6' => self::FIELD_STRING,
			'setBonusDesc7' => self::FIELD_STRING,
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
			'traitAbilityDesc' => self::FIELD_TEXTTRANSFORM,
			'traitCooldown' => self::FIELD_INT,
			'isUnique' => self::FIELD_INTYESBLANK,
			'isUniqueEquipped' => self::FIELD_INTYESBLANK,
			'isVendorTrash' => self::FIELD_INTYESBLANK,
			'isArmorDecay' => self::FIELD_INTYESBLANK,
			'isConsumable' => self::FIELD_INTYESBLANK,
			'dyeData' => self::FIELD_STRING,
			//'actorCategory' => self::FIELD_INT,
	);
	
	
	public static $MINEDITEMSUMMARY_FIELDS = array(
			//'id' => self::FIELD_INTID,		// Removed in update 30
			'itemId' => self::FIELD_INT,
			'name' => self::FIELD_INTTRANSFORM,
			'icon' => self::FIELD_GAMEICON,
			'description' => self::FIELD_TEXTTRANSFORM,
			'tags' => self::FIELD_STRING,
			'dyeData' => self::FIELD_STRING,
			'style' => self::FIELD_INTTRANSFORM,
			'trait' => self::FIELD_INTTRANSFORM,
			'value' => self::FIELD_STRING,
			'level' => self::FIELD_STRING,
			'quality' => self::FIELD_STRING,
			'type' => self::FIELD_INTTRANSFORM,
			'specialType'=> self::FIELD_INTTRANSFORM,
			'equipType' => self::FIELD_INTTRANSFORM,
			'weaponType' => self::FIELD_INTTRANSFORM,
			'armorType' => self::FIELD_INTTRANSFORM,
			'craftType' => self::FIELD_INTTRANSFORM,
			'armorRating' => self::FIELD_STRING,
			'weaponPower' => self::FIELD_STRING,
			'enchantName' => self::FIELD_STRING,
			'enchantDesc' => self::FIELD_TEXTTRANSFORM,
			//'abilityName' => self::FIELD_STRING,
			//'abilityDesc' => self::FIELD_TEXTTRANSFORM,
			'setName' => self::FIELD_STRING,
			'setBonusDesc1' => self::FIELD_STRING,
			'setBonusDesc2' => self::FIELD_STRING,
			'setBonusDesc3' => self::FIELD_STRING,
			'setBonusDesc4' => self::FIELD_STRING,
			'setBonusDesc5' => self::FIELD_STRING,
			'setBonusDesc6' => self::FIELD_STRING,
			'setBonusDesc7' => self::FIELD_STRING,
			'bindType' => self::FIELD_INT,
			'traitDesc' => self::FIELD_STRING,
			'traitAbilityDesc' => self::FIELD_STRING,
			'isUnique' => self::FIELD_INTYESBLANK,
			'isUniqueEquipped' => self::FIELD_INTYESBLANK,
			'isVendorTrash' => self::FIELD_INTYESBLANK,
			'isArmorDecay' => self::FIELD_INTYESBLANK,
			'isConsumable' => self::FIELD_INTYESBLANK,
			'materialLevelDesc' => self::FIELD_TEXTTRANSFORM,
			'resultItemLink' => self::FIELD_TEXTTRANSFORM,
			'recipeListIndex' => self::FIELD_INT,
			'recipeIndex' => self::FIELD_INT,
			//'actorCategory' => self::FIELD_INT,
	);
	
	
	public static $SETSUMMARY_FIELDS = array(
			'gameId' => self::FIELD_INTID,
			'setName' => self::FIELD_STRING,
			'type' => self::FIELD_STRING,
			'sources' => self::FIELD_STRING,
			'setMaxEquipCount' => self::FIELD_INT,
			'setBonusCount' => self::FIELD_INT,
			'setBonusDesc' => self::FIELD_TEXTTRANSFORM,
			'itemSlots' => self::FIELD_STRING,
			'itemCount' => self::FIELD_INT,
			'setBonusDesc1' => self::FIELD_TEXTTRANSFORM,
			'setBonusDesc2' => self::FIELD_TEXTTRANSFORM,
			'setBonusDesc3' => self::FIELD_TEXTTRANSFORM,
			'setBonusDesc4' => self::FIELD_TEXTTRANSFORM,
			'setBonusDesc5' => self::FIELD_TEXTTRANSFORM,
			'setBonusDesc6' => self::FIELD_TEXTTRANSFORM,
			'setBonusDesc7' => self::FIELD_TEXTTRANSFORM,
			'id' => self::FIELD_INTID,
	);
	
	
	public static $SETINFO_FIELDS = array(
			'setName' => self::FIELD_STRING,
			'gameId' => self::FIELD_INT,
			'type' => self::FIELD_STRING,
			'gameType' => self::FIELD_STRING,
			'sources' => self::FIELD_STRING,
			'category' => self::FIELD_STRING,
			'slots' => self::FIELD_STRING,
			'numPieces' => self::FIELD_INT,
			'maxEquipCount' => self::FIELD_INT,
	);
	
	
	public static $SKILLDUMP_FIELDS = array(
			'id' => self::FIELD_INT,
			'displayId' => self::FIELD_INT,
			'version' => self::FIELD_STRING,
			'texture'  => self::FIELD_GAMEICON,
			'name' => self::FIELD_STRING,
			'description' => self::FIELD_TEXTTRANSFORM,
			'descHeader' => self::FIELD_TEXTTRANSFORM,
			'duration' => self::FIELD_INT,
			'startTime' => self::FIELD_INT,
			'tickTime' => self::FIELD_INT,
			'cooldown' => self::FIELD_INT,
			'cost' => self::FIELD_STRING,
			'target' => self::FIELD_STRING,
			'minRange' => self::FIELD_INT,
			'maxRange' => self::FIELD_INT,
			'radius' => self::FIELD_INT,
			'isPassive' => self::FIELD_INT,
			'isChanneled' => self::FIELD_INT,
			'isPermanent' => self::FIELD_INT,
			'isPlayer'  => self::FIELD_INT,
			'isCrafted' => self::FIELD_INT,
			'craftedId' => self::FIELD_INT,
			'rank'  => self::FIELD_INT,
			'morph'  => self::FIELD_INT,
			'learnedLevel'  => self::FIELD_INT,
			'castTime' => self::FIELD_INT,
			'channelTime' => self::FIELD_INT,
			'angleDistance' => self::FIELD_INT,
			'mechanic' =>self::FIELD_INTTRANSFORM,	//TODO: Update 35pts
			'buffType' => self::FIELD_INTTRANSFORM,
			'isToggle' => self::FIELD_INT,
			'chargeFreq' => self::FIELD_INTTRANSFORM,	//TODO: Update 35pts
			'skillIndex' => self::FIELD_INT,
			'skillType'  => self::FIELD_INTTRANSFORM,
			'skillLine' => self::FIELD_STRING,
			'raceType'  => self::FIELD_STRING,
			'classType'  => self::FIELD_STRING,
			'setName'  => self::FIELD_STRING,
			'baseAbilityId'  => self::FIELD_INT,
			'prevSkill'  => self::FIELD_INT,
			'nextSkill'  => self::FIELD_INT,
			'nextSkill2'  => self::FIELD_INT,
			'upgradeLines' => self::FIELD_TEXTTRANSFORM,
			'effectLines' => self::FIELD_TEXTTRANSFORM,
			'numCoefVars' => self::FIELD_INT,
			'coefDescription' =>  self::FIELD_TEXTTRANSFORM,
			'type1' => self::FIELD_INTTRANSFORM,
			'a1' => self::FIELD_FLOAT,
			'b1' => self::FIELD_FLOAT,
			'c1' => self::FIELD_FLOAT,
			'R1' => self::FIELD_FLOAT,
			'avg1' => self::FIELD_FLOAT,
			'type2' => self::FIELD_INTTRANSFORM,
			'a2' => self::FIELD_FLOAT,
			'b2' => self::FIELD_FLOAT,
			'c2' => self::FIELD_FLOAT,
			'R2' => self::FIELD_FLOAT,
			'avg2' => self::FIELD_FLOAT,
			'type3' => self::FIELD_INTTRANSFORM,
			'a3' => self::FIELD_FLOAT,
			'b3' => self::FIELD_FLOAT,
			'c3' => self::FIELD_FLOAT,
			'R3' => self::FIELD_FLOAT,
			'avg3' => self::FIELD_FLOAT,
			'type4' => self::FIELD_INTTRANSFORM,
			'a4' => self::FIELD_FLOAT,
			'b4' => self::FIELD_FLOAT,
			'c4' => self::FIELD_FLOAT,
			'R4' => self::FIELD_FLOAT,
			'avg4' => self::FIELD_FLOAT,
			'type5' => self::FIELD_INTTRANSFORM,
			'a5' => self::FIELD_FLOAT,
			'b5' => self::FIELD_FLOAT,
			'c5' => self::FIELD_FLOAT,
			'R5' => self::FIELD_FLOAT,
			'avg5' => self::FIELD_FLOAT,
			'type6' => self::FIELD_INTTRANSFORM,
			'a6' => self::FIELD_FLOAT,
			'b6' => self::FIELD_FLOAT,
			'c6' => self::FIELD_FLOAT,
			'R6' => self::FIELD_FLOAT,
			'avg6' => self::FIELD_FLOAT,
			'rawDescription' =>  self::FIELD_STRING,
			'rawTooltip' =>  self::FIELD_STRING,
			'rawCoef' =>  self::FIELD_STRING,
			'coefTypes' =>  self::FIELD_STRING,
	);
	
	
	public static $CRAFTEDSKILL_FIELDS = array(
			'id' => self::FIELD_INT,
			'abilityId' => self::FIELD_INT,
			'abilityIds' => self::FIELD_STRING,
			'skillType' => self::FIELD_INT,
			'name' => self::FIELD_STRING,
			'description' => self::FIELD_STRING,
			'hint' => self::FIELD_STRING,
			'icon' => self::FIELD_STRING,
			'slots1' => self::FIELD_STRING,
			'slots2' => self::FIELD_STRING,
			'slots3' => self::FIELD_STRING,
	);
	
	
	public static $CRAFTEDSCRIPT_FIELDS = array(
			'id' => self::FIELD_INT,
			'slot' => self::FIELD_INT,
			'name' => self::FIELD_STRING,
			'description' => self::FIELD_STRING,
			'hint' => self::FIELD_STRING,
			'icon' => self::FIELD_STRING,
	);
	
	
	public static $CRAFTEDSCRIPTDESCRIPTION_FIELDS = array(
			'id' => self::FIELD_INT,
			'craftedAbilityId' => self::FIELD_INT,
			'scriptId' => self::FIELD_INT,
			'classId' => self::FIELD_INT,
			'abilityId' => self::FIELD_INT,
			'name' => self::FIELD_STRING,
			'description' => self::FIELD_STRING,
	);
	
	
	public static $SKILLLINE_FIELDS = array(
			'id' => self::FIELD_INT,
			'name' => self::FIELD_STRING,
			'fullName' => self::FIELD_STRING,
			'skillType' => self::FIELD_INTTRANSFORM,
			'raceType' =>  self::FIELD_STRING,
			'classType' =>  self::FIELD_STRING,
			'numRanks' => self::FIELD_INT,
			'totalXp' => self::FIELD_INT,
			'xp' => self::FIELD_LARGESTRING,
	);
	
	
	public static $SKILLTREE_FIELDS = array(
			'id' => self::FIELD_INT,
			'abilityId' => self::FIELD_INT,
			'icon'  => self::FIELD_GAMEICON,
			'skillTypeName' => self::FIELD_STRING,
			'baseName' => self::FIELD_STRING,
			'name' => self::FIELD_STRING,
			'learnedLevel' => self::FIELD_INT,
			'rank' => self::FIELD_INT,
			'maxRank' => self::FIELD_INT,
			'type' => self::FIELD_STRING,
			'cost' => self::FIELD_STRING,
			'skillIndex' => self::FIELD_INT,
			'description' => self::FIELD_TEXTTRANSFORM,
	);
	
	
	public static $CPDISCIPLINE_FIELDS = array(
			'id' => self::FIELD_INT,
			'disciplineIndex' => self::FIELD_INT,
			'name' => self::FIELD_STRING,
			'description' => self::FIELD_TEXTTRANSFORM,
			'attribute' => self::FIELD_TEXTTRANSFORM,
	);
	
	public static $CPSKILL_FIELDS = array(
			'id' => self::FIELD_INT,
			'abilityId' => self::FIELD_INT,
			'disciplineIndex' => self::FIELD_INT,
			'skillIndex' => self::FIELD_INT,
			'unlockLevel' => self::FIELD_INT,
			'name' => self::FIELD_STRING,
			'maxValue' => self::FIELD_FLOAT,
			'minDescription' => self::FIELD_TEXTTRANSFORM,
			'maxDescription' => self::FIELD_TEXTTRANSFORM,
			'fitDescription' => self::FIELD_STRING,
			'a' => self::FIELD_FLOAT,
			'b' => self::FIELD_FLOAT,
			'c' => self::FIELD_FLOAT,
			'd' => self::FIELD_FLOAT,
			'r2' => self::FIELD_FLOAT,
			'x' => self::FIELD_FLOAT,
			'y' => self::FIELD_FLOAT,
	);
	
	public static $CPSKILLDESCRIPTION_FIELDS = array(
			'id' => self::FIELD_INT,
			'abilityId' => self::FIELD_INT,
			'description' => self::FIELD_TEXTTRANSFORM,
			'points' => self::FIELD_INT,
	);
	
	public static $CP2DISCIPLINE_FIELDS = array(
			'id' => self::FIELD_INT,
			'disciplineIndex' => self::FIELD_INT,
			'disciplineId' => self::FIELD_INT,
			'name' => self::FIELD_STRING,
			'bgTexture' => self::FIELD_STRING,
			'glowTexture' => self::FIELD_STRING,
			'selectTexture' => self::FIELD_STRING,
			'discType' => self::FIELD_INT,
			'numSkills' => self::FIELD_INT,
	);
	
	public static $CP2SKILL_FIELDS = array(
			'id' => self::FIELD_INT,
			'skillId' => self::FIELD_INT,
			'parentSkillId' => self::FIELD_INT,
			'abilityId' => self::FIELD_INT,
			'disciplineIndex' => self::FIELD_INT,
			'disciplineId' => self::FIELD_INT,
			'skillIndex' => self::FIELD_INT,
			'name' => self::FIELD_STRING,
			'skillType' => self::FIELD_INT,
			'numJumpPoints' => self::FIELD_INT,
			'jumpPoints' => self::FIELD_STRING,
			'jumpPointDelta' => self::FIELD_INT,
			'isRoot' => self::FIELD_INT,
			'isClusterRoot' => self::FIELD_INT,
			'maxPoints' => self::FIELD_INT,
			'minDescription' => self::FIELD_TEXTTRANSFORM,
			'maxDescription' => self::FIELD_TEXTTRANSFORM,
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
	
	public static $CP2SKILLDESCRIPTION_FIELDS = array(
			'id' => self::FIELD_INT,
			'abilityId' => self::FIELD_INT,
			'skillId' => self::FIELD_INT,
			'description' => self::FIELD_TEXTTRANSFORM,
			'points' => self::FIELD_INT,
	);
	
	public static $CP2CLUSTERROOT_FIELDS = array(
			'id' => self::FIELD_INT,
			'skillId' => self::FIELD_INT,
			'texture' => self::FIELD_STRING,
			'name' => self::FIELD_STRING,
			'skills' => self::FIELD_STRING,
			'disciplineIndex' => self::FIELD_INT,
			'disciplineId' => self::FIELD_INT,
	);
	
	public static $CP2SKILLLINK_FIELDS = array(
			'id' => self::FIELD_INT,
			'skillId' => self::FIELD_INT,
			'parentSkillId' => self::FIELD_INT,
			'name' => self::FIELD_STRING,
			'parentName' => self::FIELD_STRING,
	);
	
	public static $COLLECTIBLE_FIELDS = array(
			'id' => self::FIELD_INT,
			'name' => self::FIELD_STRING,
			'itemLink' => self::FIELD_STRING,
			'nickname' => self::FIELD_STRING,
			'description' => self::FIELD_STRING,
			'hint' => self::FIELD_STRING,
			'icon' => self::FIELD_GAMEICON,
			'lockedIcon' => self::FIELD_GAMEICON,
			'backgroundIcon' => self::FIELD_GAMEICON,
			'categoryType' => self::FIELD_INTTRANSFORM,
			'zoneIndex' => self::FIELD_INT,
			'categoryIndex' => self::FIELD_INT,
			'subCategoryIndex' => self::FIELD_INT,
			'collectibleIndex' => self::FIELD_INT,
			'achievementIndex' => self::FIELD_INT,
			'categoryName' => self::FIELD_STRING,
			'subCategoryName' => self::FIELD_STRING,
			'furnCategory' => self::FIELD_STRING,
			'furnSubcategory' => self::FIELD_STRING,
			'furnLimitType' => self::FIELD_INTTRANSFORM,
			'tags' => self::FIELD_STRING,
			'isUnlocked' => self::FIELD_INTYESBLANK,
			'isActive' => self::FIELD_INTYESBLANK,
			'isSlottable' => self::FIELD_INTYESBLANK,
			'isUsable' => self::FIELD_INTYESBLANK,
			'isRenameable' => self::FIELD_INTYESBLANK,
			'isPlaceholder' => self::FIELD_INTYESBLANK,
			'isHidden' => self::FIELD_INTYESBLANK,
			'hasAppearance' => self::FIELD_INTYESBLANK,
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
			'subCategoryName' => self::FIELD_STRING,
			'categoryIndex' => self::FIELD_INT,
			'subCategoryIndex' => self::FIELD_INT,
			'numAchievements' => self::FIELD_INT,
			'points' => self::FIELD_INT,
			'hidesPoints' => self::FIELD_INT,
			'icon' => self::FIELD_GAMEICON,
			'pressedIcon' => self::FIELD_GAMEICON,
			'mouseoverIcon' => self::FIELD_GAMEICON,
			'gamepadIcon' => self::FIELD_GAMEICON,
	);
	
	
	public static $ACHIEVEMENT_FIELDS = array(
			'id' => self::FIELD_INT,
			'name' => self::FIELD_STRING,
			'description' => self::FIELD_STRING,
			'categoryIndex' => self::FIELD_INT,
			'subCategoryIndex' => self::FIELD_INT,
			'achievementIndex' => self::FIELD_INT,
			'categoryName' => self::FIELD_STRING,
			'points' => self::FIELD_INT,
			'icon' => self::FIELD_GAMEICON,
			'numRewards' => self::FIELD_INT,
			'itemLink' => self::FIELD_STRING,
			'link' => self::FIELD_STRING,
			'firstId' => self::FIELD_INT,
			'prevId' => self::FIELD_INT,
			'nextId' => self::FIELD_INT,
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
			'dyeColor' => self::FIELD_COLORBOX,
	);
	
	
	public static $ACHIEVEMENTCRITERIA_FIELDS = array(
			'id' => self::FIELD_INT,
			'achievementId' => self::FIELD_INT,
			'name' => self::FIELD_STRING,
			'description' => self::FIELD_STRING,
			'numRequired' => self::FIELD_INT,
			'criteriaIndex' => self::FIELD_INT,
	);
	
	
	public static $ANTIQUITYLEAD_FIELDS = array(
			'id' => self::FIELD_INT,
			'name' => self::FIELD_STRING,
			'icon' => self::FIELD_GAMEICON,
			'quality' => self::FIELD_INT,
			'difficulty' => self::FIELD_INT,
			'requiresLead' => self::FIELD_INT,
			'isRepeatable' => self::FIELD_INT,
			'rewardId' => self::FIELD_INT,
			'zoneId' => self::FIELD_INT,
			'setId' => self::FIELD_INT,
			'setName' => self::FIELD_STRING,
			'setIcon' => self::FIELD_GAMEICON,
			'setQuality' => self::FIELD_INT,
			'setRewardId' => self::FIELD_INT,
			'setCount' => self::FIELD_INT,
			'categoryId' => self::FIELD_INT,
			'categoryOrder' => self::FIELD_INT,
			'categoryName' => self::FIELD_STRING,
			'categoryIcon' => self::FIELD_GAMEICON,
			'categoryCount' => self::FIELD_INT,
			'loreName1' => self::FIELD_STRING,
			'loreDescription1' => self::FIELD_STRING,
			'loreName2' => self::FIELD_STRING,
			'loreDescription2' => self::FIELD_STRING,
			'loreName3' => self::FIELD_STRING,
			'loreDescription3' => self::FIELD_STRING,
			'loreName4' => self::FIELD_STRING,
			'loreDescription4' => self::FIELD_STRING,
			'loreName5' => self::FIELD_STRING,
			'loreDescription5' => self::FIELD_STRING,
	);
	
	
	public static $ZONE_FIELDS = array(
			'id' => self::FIELD_INT,
			'zoneId' => self::FIELD_INT,
			'zoneIndex' => self::FIELD_INT,
			'zoneName' => self::FIELD_STRING,
			'subZoneName' => self::FIELD_STRING,
			'description' => self::FIELD_STRING,
			'mapName' => self::FIELD_STRING,
			'mapType' => self::FIELD_INT,
			'mapContentType' => self::FIELD_INT,
			'mapFilterType' => self::FIELD_INT,
			'numPOIs' => self::FIELD_INT,
			'allowsScaling' => self::FIELD_INT,
			'allowsBattleScaling' => self::FIELD_INT,
			'minLevel' => self::FIELD_INT,
			'maxLevel' => self::FIELD_INT,
			'isAvA1' => self::FIELD_INT,
			'isAvA2' => self::FIELD_INT,
			'isBattleground' => self::FIELD_INT,
			'telvarBehavior' => self::FIELD_INT,
			'isOutlaw' => self::FIELD_INT,
			'isJustice' => self::FIELD_INT,
			'isTutorial' => self::FIELD_INT,
			'isGroupOwnable' => self::FIELD_INT,
			'isDungeon' => self::FIELD_INT,
			'dungeonDifficulty' => self::FIELD_INT,
			'count' => self::FIELD_INT,
	);
	
	public static $ZONEPOI_FIELDS = array(
			'id' => self::FIELD_INT,
			'zoneId' => self::FIELD_INT,
			'zoneName' => self::FIELD_STRING,
			'subZoneName' => self::FIELD_STRING,
			'poiIndex' => self::FIELD_INT,
			'normX' => self::FIELD_FLOAT,
			'normY' => self::FIELD_FLOAT,
			'pinType' => self::FIELD_INT,
			'mapIcon' => self::FIELD_STRING,
			'isShown' => self::FIELD_INT,
			'poiType' => self::FIELD_INT,
			'objName' => self::FIELD_STRING,
			'objLevel' => self::FIELD_INT,
			'objStartDesc' => self::FIELD_STRING,
			'objEndDesc' => self::FIELD_STRING,
			'count' => self::FIELD_INT,
	);
	
	
	public static $TRIBUTEPATRON_FIELDS = array(
			'id' => self::FIELD_INT,
			'name' => self::FIELD_STRING,
			'category' => self::FIELD_STRING,
			'rarity' => self::FIELD_INT,
			'smallIcon' => self::FIELD_GAMEICON,
			'largeIcon' => self::FIELD_GAMEICON,
			'loreDescription' => self::FIELD_STRING,
			'collectibleId' => self::FIELD_INT,
			'isNeutral' => self::FIELD_INT,
			'skipNeutral' => self::FIELD_INT,
			'categoryId' => self::FIELD_INT,
			'playStyleDescription' => self::FIELD_STRING,
			'acquireHint' => self::FIELD_STRING,
			'numStartCards' => self::FIELD_INT,
			'startCards' => self::FIELD_STRING,
			'numDockCards' => self::FIELD_INT,
			'dockCards' => self::FIELD_STRING,
			'actionTexture' => self::FIELD_GAMEICON,
			'largeRingIcon' => self::FIELD_GAMEICON,
			'actionGlow' => self::FIELD_GAMEICON,
			'agentTexture' => self::FIELD_GAMEICON,
			'agentGlow' => self::FIELD_GAMEICON,
			'suitIcon' => self::FIELD_GAMEICON,
	);
	
	
	public static $TRIBUTECARD_FIELDS = array(
			'id' => self::FIELD_INT,
			'name' => self::FIELD_STRING,
			'description' => self::FIELD_STRING,
			'cardType' => self::FIELD_INT,
			'rarity' => self::FIELD_INT,
			'texture' => self::FIELD_GAMEICON,
			'glowTexture' => self::FIELD_GAMEICON,
			'resourceType' => self::FIELD_INT,
			'resourceQnt' => self::FIELD_INT,
			'defeatType' => self::FIELD_INT,
			'defeatQnt' => self::FIELD_INT,
			'doesTaunt' => self::FIELD_INT,
			'isContract' => self::FIELD_INT,
			'oneMechanic' => self::FIELD_INT,
			'numActiveMechanics' => self::FIELD_INT,
			'activeMechanic1' => self::FIELD_STRING,
			'activeMechanic2' => self::FIELD_STRING,
			'activeMechanic3' => self::FIELD_STRING,
			'activeMechanic4' => self::FIELD_STRING,
			'activeMechanic5' => self::FIELD_STRING,
			'numComboMechanics' => self::FIELD_INT,
			'comboMechanic1' => self::FIELD_STRING,
			'comboMechanic2' => self::FIELD_STRING,
			'comboMechanic3' => self::FIELD_STRING,
			'comboMechanic4' => self::FIELD_STRING,
			'comboMechanic5' => self::FIELD_STRING,
	);
	
	
	public static $CAMPAIGNINFO_FIELDS = array(
			'id' => self::FIELD_INT,
			'server' => self::FIELD_STRING,
			'idx' => self::FIELD_INT,
			'name' => self::FIELD_STRING,
			'scoreAldmeri' => self::FIELD_INT,
			'scoreDaggerfall' => self::FIELD_INT,
			'scoreEbonheart' => self::FIELD_INT,
			'underdogAlliance' => self::FIELD_INT,
			'populationAldmeri' => self::FIELD_INT,
			'populationDaggerfall' => self::FIELD_INT,
			'populationEbonheart' => self::FIELD_INT,
			'waitTime' => self::FIELD_INT,
			'startTime' => self::FIELD_INTTRANSFORM,
			'endTime' => self::FIELD_INTTRANSFORM,
			'lastUpdated' => self::FIELD_INTTRANSFORM,
			'entriesUpdated' => self::FIELD_INTTRANSFORM,
	);
	
	
	public static $CAMPAIGNLEADERBOARDS_FIELDS = array(
			'campaignId' => self::FIELD_INT,
			'server' => self::FIELD_STRING,
			'campaignName' => self::FIELD_STRING,
			'rank' => self::FIELD_INT,
			'points' => self::FIELD_INT,
			'name' => self::FIELD_STRING,
			//'displayName' => self::FIELD_STRING,
			'class' => self::FIELD_INTTRANSFORM,
			'alliance' => self::FIELD_INTTRANSFORM,
	);
	
	
	public static $ENDEAVOR_FIELDS = array(
			'startTimestamp' => self::FIELD_INT,
			'endTimestamp' => self::FIELD_INT,
			'name' => self::FIELD_STRING,
			'description' => self::FIELD_STRING,
			'idx' => self::FIELD_INT,
			'type' => self::FIELD_INT,
			'typeLimit' => self::FIELD_INT,
			'numRewards' => self::FIELD_INT,
			'rewards' => self::FIELD_STRING,
			'rawRewards' => self::FIELD_STRING,
	);
	
	
	public static $GOLDENVENDORITEM_FIELDS = array(
			'startTimestamp' => self::FIELD_INT,
			'link' => self::FIELD_STRING,
			'name' => self::FIELD_STRING,
			'trait' => self::FIELD_INT,
			'quality' => self::FIELD_INT,
			'bindType' => self::FIELD_INT,
			'price' => self::FIELD_STRING,
	);
	
	
	public static $LUXURYVENDORITEM_FIELDS = array(
			'startTimestamp' => self::FIELD_INT,
			'link' => self::FIELD_STRING,
			'name' => self::FIELD_STRING,
			'trait' => self::FIELD_INT,
			'quality' => self::FIELD_INT,
			'bindType' => self::FIELD_INT,
			'price' => self::FIELD_STRING,
	);
	
	
	public static $PTS_SEARCH_TYPE_OPTIONS = array(
			'Items Update ## PTS' => 'minedItemSummary##pts',
			'Sets Update ## PTS' => 'setSummary##pts',
			'Skills Update ## PTS' => 'minedSkills##pts',
	);
	
	
	public static $PTS_RECORD_TYPES = array(
			'minedItem##pts' => array(
					'displayName' => 'Update ## PTS: Mined Items',
					'displayNameSingle' => 'Update ## PTS: Mined Item',
					'record' => 'minedItem##pts',
					'table' => 'minedItem##pts',
					'method' => 'DoRecordDisplay',
					'sort' => 'itemId',
					'message' => 'These are items for update ## (__NAME__) as logged from the PTS server. Note that only Level 1 White and CP160 Gold items have been exported.',
					
					'transform' => array(
							'type' => 'GetItemTypeText',
							'specialType' => 'GetItemSpecialTypeText',
							'style' => 'GetItemStyleText',
							'trait' => 'GetItemTraitText',
							'quality' => 'GetItemQualityText',
							'equipType' => 'GetItemEquipTypeText',
							'craftType' => 'GetItemTypeText',
							'armorType' => 'GetItemArmorTypeText',
							'weaponType' => 'GetItemWeaponTypeText',
							'name' => 'MakeMinedItemLinkPts',
							'link' => 'MakeMinedItemLinkPts',
							'description' => 'RemoveTextFormats',
							'abilityDesc' => 'RemoveTextFormats',
							'enchantDesc' => 'RemoveTextFormats',
					),
						
					'filters' => array(
					),
			),
			
			
			'minedItemSummary##pts' => array(
					'displayName' => 'Update ## PTS: Mined Item Summaries',
					'displayNameSingle' => 'Update ## PTS: Mined Item Summary',
					'record' => 'minedItemSummary##pts',
					'table' => 'minedItemSummary##pts',
					'method' => 'DoRecordDisplay',
					'sort' => 'itemId',
					'idField' => 'itemId',
					'message' => 'These are items for update ## (__NAME__) as logged from the PTS server. These are all game items, some of which may not be obtainable. See <a href="/viewlog.php?record=item">Looted Items</a> for items actually looted/seen in the game.',
					
					'transform' => array(
							'type' => 'GetItemTypeText',
							'specialType' => 'GetItemSpecialTypeText',
							'style' => 'GetItemStyleText',
							'trait' => 'GetItemTraitText',
							'quality' => 'GetItemQualityText',
							'equipType' => 'GetItemEquipTypeText',
							'craftType' => 'GetItemTypeText',
							'armorType' => 'GetItemArmorTypeText',
							'weaponType' => 'GetItemWeaponTypeText',
							'name' => 'MakeMinedItemSummaryLinkPts',
							'description' => 'RemoveTextFormats',
							'abilityDesc' => 'RemoveTextFormats',
							'enchantDesc' => 'RemoveTextFormats',
							'materialLevelDesc' => 'RemoveTextFormats',
							'resultItemLink' => 'FormatResultItemLinkPts',
					),
						
					'filters' => array(
					),
			),
			
			'setSummary##pts' => array(
					'displayName' => 'Update ## PTS: Set Summaries',
					'displayNameSingle' => 'Update ## PTS: Set Item Summary',
					'record' => 'setSummary##pts',
					'table' => 'setSummary##pts',
					'method' => 'DoRecordDisplay',
					'sort' => 'setName',
					'message' => "These are sets for update ## (__NAME__) as logged from the PTS server.",
					
					'columnNames' => array(
							'id' => 'Internal ID',
					),
					
					'transform' => array(
							'setBonusDesc' => 'TransformSetBonusDesc',
							'setBonusDesc1' => 'TransformSetBonusDesc',
							'setBonusDesc2' => 'TransformSetBonusDesc',
							'setBonusDesc3' => 'TransformSetBonusDesc',
							'setBonusDesc4' => 'TransformSetBonusDesc',
							'setBonusDesc5' => 'TransformSetBonusDesc',
							'setBonusDesc6' => 'TransformSetBonusDesc',
							'setBonusDesc7' => 'TransformSetBonusDesc',
					),
					
					'filters' => array(
							array(
									'record' => 'minedItemSummary##pts',
									'field' => 'setName',
									'thisField' => 'setName',
									'displayName' => 'View&nbsp;Items',
									'type' => 'filter',
							),
					),
			),
			
			'minedSkills##pts' => array(
					'displayName' => 'Update ## PTS: Mined Skills',
					'displayNameSingle' => 'Update ## PTS: Mined Skill',
					'record' => 'minedSkills##pts',
					'table' => 'minedSkills##pts',
					'method' => 'DoRecordDisplay',
					'sort' => 'name',
					'message' => "These are skills for update ## (__NAME__) as logged from the PTS server.",
						
					'transform' => array(
							'mechanic' => 'GetCombatMechanicText34',
							'type1' => 'GetCustomCombatMechanicText34',
							'type2' => 'GetCustomCombatMechanicText34',
							'type3' => 'GetCustomCombatMechanicText34',
							'type4' => 'GetCustomCombatMechanicText34',
							'type5' => 'GetCustomCombatMechanicText34',
							'type6' => 'GetCustomCombatMechanicText34',
							'skillType' => 'GetSkillTypeText',
							'description' => 'RemoveTextFormats',
							'descHeader' => 'RemoveTextFormats',
							'coefDescription' => 'RemoveTextFormats',
							'effectLines' => 'RemoveTextFormats',
							'upgradeLines' => 'RemoveTextFormats',
							'chargeFreq' => 'ConvertSkillChargeFreq',
							'buffType' => 'GetBuffTypeText',
					),
						
					'filters' => array(
					),
			),
			
			'craftedSkills##pts' => array(
					'displayName' => 'Update ## PTS: Crafted Skills',
					'displayNameSingle' => 'Update ## PTS: Crafted Skill',
					'record' => 'craftedSkills##pts',
					'table' => 'craftedSkills##pts',
					'method' => 'DoRecordDisplay',
					'sort' => 'name',
					'message' => "These are crafted skills for update ## (__NAME__) as logged from the PTS server.",
					
					'transform' => array(
					),
					
					'filters' => array(
					),
			),
			
			'craftedScripts##pts' => array(
					'displayName' => 'Update ## PTS: Crafted Scripts',
					'displayNameSingle' => 'Update ## PTS: Crafted Script',
					'record' => 'craftedScripts##pts',
					'table' => 'craftedScripts##pts',
					'method' => 'DoRecordDisplay',
					'sort' => 'name',
					'message' => "These are crafted skills for update ## (__NAME__) as logged from the PTS server.",
					
					'transform' => array(
					),
					
					'filters' => array(
					),
			),
			
			'craftedScriptDescriptions##pts' => array(
					'displayName' => 'Update ## PTS: Crafted Script Descriptions',
					'displayNameSingle' => 'Update ## PTS: Crafted Script Description',
					'record' => 'craftedScriptDescriptions##pts',
					'table' => 'craftedScriptDescriptions##pts',
					'method' => 'DoRecordDisplay',
					'sort' => 'name',
					'message' => "These are crafted skills for update ## (__NAME__) as logged from the PTS server.",
					
					'transform' => array(
					),
					
					'filters' => array(
					),
			),
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
			
			'crownStoreItems' => array(
					'displayName' => 'Crown Store Items',
					'displayNameSingle' => 'Crown Store Item',
					'record' => 'crownStoreItems',
					'table' => 'crownStoreItems',
					'method' => 'DoRecordDisplay',
					'sort' => [ 'category', 'subCategory', 'name'],
					
					'transform' => array(
							'lastUpdated' => 'GetTimestampDateFormatWithDiff',
							'saleTimestamp' => 'GetTimestampDateFormatWithDiff',
							'imageUrl' => 'MakeShortLink',
					),
			),
			
			'item' => array(
					'displayName' => 'Looted Items',
					'displayNameSingle' => 'Looted Item',
					'record' => 'item',
					'table' => 'item',
					'method' => 'DoRecordDisplay',
					'sort' => 'name',
					'message' => 'These are items that have been seen/looted in the game. See <a href="/viewlog.php?record=minedItemSummary">Item Summaries</a> for all possible items in the game.',
						
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
					'sort' => 'count',
					
					'validsortfields' => array(
							'id',
							'count',
					),
					
					'join' => array(
					),
					
					'transform' => array(
							'firstTime' => 'GetTimestampText',
							'lastTime' => 'GetTimestampText',
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
			
			'logInfo' => array(
					'displayName' => 'Log Statistics',
					'displayNameSingle' => 'Log Statistic',
					'record' => 'logInfo',
					'table' => 'logInfo',
					'method' => 'DoRecordDisplay',
					'sort' => 'id',
						
					'transform' => array(
					),
						
					'filters' => array(
					),
			),
			
			/*
			'oldQuest' => array(
					'displayName' => 'Old Quests',
					'displayNameSingle' => 'Old Quest',
					'record' => 'oldQuest',
					'table' => 'oldQuest',
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
									'record' => 'oldQuestStage',
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
			
			'oldQuestStage' => array(
					'message' => 'Note: Quest stages are not necessarily in the correct order yet.',
					'displayName' => 'Old Quest Stages',
					'displayNameSingle' => 'Old Quest Stage',
					'record' => 'oldQuestStage',
					'table' => 'oldQuestStage',
					'method' => 'DoRecordDisplay',
					'sort' => 'questId, orderIndex',
					
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
									'record' => 'oldQuest',
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
			), //*/
			
			'quest' => array(
					'displayName' => 'Quests',
					'displayNameSingle' => 'Quest',
					'record' => 'quest',
					'table' => 'quest',
					'method' => 'DoRecordDisplay',
					'sort' => 'name',
					
					'sortTranslate' => array(
								'zone' => 'location.zone',
							),
					
					'transform' => array(
							'type' => 'GetEsoQuestTypeText',
							'repeatType' => 'GetEsoQuestRepeatTypeText',
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
									'record' => 'questStep',
									'field' => 'questId',
									'thisField' => 'id',
									'displayName' => 'View Steps',
									'type' => 'filter',
							),
							array(
									'record' => 'questReward',
									'field' => 'questId',
									'thisField' => 'id',
									'displayName' => 'View Rewards',
									'type' => 'filter',
							),
							array(
									'record' => 'questCondition',
									'field' => 'questId',
									'thisField' => 'id',
									'displayName' => 'View Conditions',
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
			
			'uniqueQuest' => array(
					'displayName' => 'Unique Quests',
					'displayNameSingle' => 'Unique Quest',
					'record' => 'uniqueQuest',
					'table' => 'uniqueQuest',
					'method' => 'DoRecordDisplay',
					'sort' => 'name',
					
					'sortTranslate' => array(
								'zone' => 'location.zone',
							),
			
					'transform' => array(
							'type' => 'GetEsoQuestTypeText',
							'repeatType' => 'GetEsoQuestRepeatTypeText',
					),
						
					'join' => array(
							'locationId' => array(
									'joinField' => 'id',
									'table' => 'location',
									'fields' => array('x', 'y', 'locationZone' => 'zone'),
							),
					),
						
					'filters' => array(
							array(
									'record' => 'questStep',
									'field' => 'questId',
									'thisField' => 'id',
									'displayName' => 'View Steps',
									'type' => 'filter',
							),
							array(
									'record' => 'questReward',
									'field' => 'questId',
									'thisField' => 'id',
									'displayName' => 'View Rewards',
									'type' => 'filter',
							),
							array(
									'record' => 'questCondition',
									'field' => 'questId',
									'thisField' => 'id',
									'displayName' => 'View Conditions',
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
			
			'questStep' => array(
					'displayName' => 'Quest Steps',
					'displayNameSingle' => 'Quest Step',
					'record' => 'questStep',
					'table' => 'questStep',
					'method' => 'DoRecordDisplay',
					'sort' => 'questId, stageIndex, stepIndex',
					
					'badSort' => [ 'journalText' => 1, 'text' => 1 ],
					'validsortfields' => array(
							'id',
							'questId',
							'stageIndex',
							'stepIndex',
							'zone',
							'uniqueId',
					),
					
					'transform' => array(
							'type' => 'GetEsoQuestStepTypeText',
							'visibility' => 'GetEsoQuestStepVisibilityTypeText',
					),
						
					'join' => array(
							'locationId' => array(
									'joinField' => 'id',
									'table' => 'location',
									'fields' => array('x', 'y', 'zone'),
							),
							'id' => array(
									'joinField' => 'questStepId',
									'table' => 'questCondition',
									'fields' => array('journalText' => 'text'),
							),
					),
					
					'group' => 'questCondition.questStepId',
					
					'filters' => array(
							array(
									'record' => 'quest',
									'field' => 'id',
									'thisField' => 'questId',
									'displayName' => 'View Quest',
									'type' => 'viewRecord',
							),
							array(
									'record' => 'questCondition',
									'field' => 'questStepId',
									'thisField' => 'id',
									'displayName' => 'View Conditions',
									'type' => 'filter',
							),
							array(
									'record' => 'location',
									'field' => 'questStageId',
									'thisField' => 'id',
									'displayName' => 'View Locations',
									'type' => 'filter',
							),
					),
			),
			
			
			'questCondition' => array(
					'displayName' => 'Quest Conditions',
					'displayNameSingle' => 'Quest Condition',
					'record' => 'questCondition',
					'table' => 'questCondition',
					'method' => 'DoRecordDisplay',
					'sort' => 'questId, stageIndex, stepIndex, conditionIndex',
					
					'columnNames' => array(
							'type1' => 'Map Pin Type 1',
							'type2' => 'Map Pin Type 2',
					),
					
					'transform' => array(
							'type1' => 'GetEsoMapPinTypeText',
							'type2' => 'GetEsoMapPinTypeText',
					),
					
					'join' => array(
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
									'record' => 'questStep',
									'field' => 'id',
									'thisField' => 'questStepId',
									'displayName' => 'View Step',
									'type' => 'viewRecord',
							),
					),
			),
			
			'questReward' => array(
					'message' => '',
					'displayName' => 'Quest Rewards',
					'displayNameSingle' => 'Quest Reward',
					'record' => 'questReward',
					'table' => 'questReward',
					'method' => 'DoRecordDisplay',
					'sort' => 'name',
					
					'transform' => array(
							'quality' => 'GetItemQualityText',
							'type' => 'GetEsoQuestRewardTypeText',
							'itemType' => 'GetEsoQuestRewardItemTypeText',
					),
					
					'join' => array(
					),
					
					'filters' => array(
							array(
									'record' => 'quest',
									'field' => 'id',
									'thisField' => 'questId',
									'displayName' => 'View Quest',
									'type' => 'viewRecord',
							),
					),
			),
			
			'questGoldReward' => array(
					'message' => '',
					'displayName' => 'Quest Gold Rewards',
					'displayNameSingle' => 'Quest Gold Reward',
					'record' => 'questGoldReward',
					'table' => 'questGoldReward',
					'method' => 'DoRecordDisplay',
					'sort' => 'playerLevel, questName, gold',
					
					'transform' => array(
					),
					
					'join' => array(
					),
					
					'filters' => array(
					),
			),
			
			'questXPReward' => array(
					'message' => '',
					'displayName' => 'Quest Experience Rewards',
					'displayNameSingle' => 'Quest Experience Reward',
					'record' => 'questXPReward',
					'table' => 'questXPReward',
					'method' => 'DoRecordDisplay',
					'sort' => 'playerLevel, questName, experience',
					
					'transform' => array(
					),
					
					'join' => array(
					),
					
					'filters' => array(
					),
			),
			
			'questItem' => array(
					'message' => '',
					'displayName' => 'Quest Items',
					'displayNameSingle' => 'Quest Item',
					'record' => 'questItem',
					'table' => 'questItem',
					'method' => 'DoRecordDisplay',
					'sort' => 'name',
					
					'transform' => array(
					),
					
					'join' => array(
					),
					
					'filters' => array(
							array(
									'record' => 'quest',
									'field' => 'id',
									'thisField' => 'questId',
									'displayName' => 'View Quest',
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
							'reaction' => 'GetEsoReactionText',
					),
					
					'filters' => array(
							array(
									'record' => 'location',
									'field' => 'npcId',
									'thisField' => 'id',
									'displayName' => 'View Locations',
									'type' => 'filter',
							),
							array(
									'record' => 'npcLocations',
									'field' => 'npcId',
									'thisField' => 'id',
									'displayName' => 'View Zones',
									'type' => 'filter',
							),
					),
					
					'join' => array(
					),
			),
			
			'npcLocations' => array(
					'displayName' => 'NPC Zones',
					'displayNameSingle' => 'NPC Zones',
					'record' => 'npcLocations',
					'table' => 'npcLocations',
					'method' => 'DoRecordDisplay',
					'sort' => array('npcId', 'zone'),
					
					'transform' => array(
					),
					
					'filters' => array(
							array(
									'record' => 'npc',
									'field' => 'id',
									'thisField' => 'npcId',
									'displayName' => 'View NPC',
									'type' => 'viewRecord',
							),		
					),
					
					'join' => array(
							'npcId' => array(
								'joinField' => 'id',
								'table' => 'npc',
								'fields' => array('name'),
							),
					),
			),
			
			'lootSources' => array(
					'displayName' => 'Loot Sources',
					'displayNameSingle' => 'Loot Source',
					'record' => 'lootSources',
					'table' => 'lootSources',
					'method' => 'DoRecordDisplay',
					'sort' => 'name',
					
					'transform' => array(
					),
					
					'filters' => array(
							array(
									'fields' => array(
											"name" => "source",
									),
									'url' => '/viewNpcLoot.php',
									'displayName' => 'View Loots',
									'type' => 'external',
							),
					),
					
					'join' => array(
					),
			),
			
			'npcLoot' => array(
					'displayName' => 'Loots',
					'displayNameSingle' => 'Loot',
					'record' => 'npcLoot',
					'table' => 'npcLoot',
					'method' => 'DoRecordDisplay',
					'sort' => 'name, zone, itemName',
					
					'transform' => array(
							'itemLink' => 'MakeItemLink',
							'itemType' => 'GetItemTypeText',
							'trait' => 'GetItemTraitText',
					),
					
					'filters' => array(
							array(
									'record' => 'lootSources',
									'field' => 'id',
									'thisField' => 'lootSourceId',
									'displayName' => 'View Source',
									'type' => 'viewRecord',
							),
							array(
									'fields' => array(
											"name" => "source",
									),
									'url' => '/viewNpcLoot.php',
									'displayName' => 'View Loots',
									'type' => 'external',
							),
							array(
									'fields' => array(
											"itemName" => "item",
									),
									'url' => '/viewNpcLoot.php',
									'displayName' => 'View Item Sources',
									'type' => 'external',
							),
					),
					
					'join' => array(
							'lootSourceId' => array(
									'joinField' => 'id',
									'table' => 'lootSources',
									'fields' => array('name'),
							),
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
					'message' => "This table is too large to search/sort quickly. Trying using the <a href='/viewlog.php?record=minedItemSummary'>Item Summary</a> table if you need to instead or <a href='/viewlog.php?record=item'>Looted Items</a>.",
					
					'validsortfields' => array(
							'id',
							'itemId',
							'enchantId',
					),
					
					'transform' => array(
							'type' => 'GetItemTypeText',
							'specialType' => 'GetItemSpecialTypeText',
							'style' => 'GetItemStyleText',
							'trait' => 'GetItemTraitText',
							'quality' => 'GetItemQualityText',
							'equipType' => 'GetItemEquipTypeText',
							'craftType' => 'GetItemTypeText',
							'armorType' => 'GetItemArmorTypeText',
							'weaponType' => 'GetItemWeaponTypeText',
							'name' => 'MakeMinedItemLink',
							'link' => 'MakeMinedItemLink',
							'description' => 'RemoveTextFormats',
							'abilityDesc' => 'RemoveTextFormats',
							'enchantDesc' => 'RemoveTextFormats',
					),
				
					'filters' => array(
					),
			),
			
			'minedItemSummary' => array(
					'displayName' => 'Mined Item Summaries',
					'displayNameSingle' => 'Mined Item Summary',
					'record' => 'minedItemSummary',
					'table' => 'minedItemSummary',
					'method' => 'DoRecordDisplay',
					'sort' => 'itemId',
					'idField' => 'itemId',
					'message' => 'These are all game items, some of which may not be obtainable. See <a href="/viewlog.php?record=item">Looted Items</a> for items actually looted/seen in the game.',
						
					'transform' => array(
							'type' => 'GetItemTypeText',
							'specialType' => 'GetItemSpecialTypeText',
							'style' => 'GetItemStyleText',
							'trait' => 'GetItemTraitText',
							'quality' => 'GetItemQualityText',
							'equipType' => 'GetItemEquipTypeText',
							'craftType' => 'GetItemTypeText',
							'armorType' => 'GetItemArmorTypeText',
							'weaponType' => 'GetItemWeaponTypeText',
							'name' => 'MakeMinedItemSummaryLink',
							'description' => 'RemoveTextFormats',
							'abilityDesc' => 'RemoveTextFormats',
							'enchantDesc' => 'RemoveTextFormats',
							'materialLevelDesc' => 'RemoveTextFormats',
							'resultItemLink' => 'FormatResultItemLink',
					),
			
					'filters' => array(
					),
			),
			
			'setSummary' => array(
					'displayName' => 'Set Summaries',
					'displayNameSingle' => 'Set Item Summary',
					'record' => 'setSummary',
					'table' => 'setSummary',
					'method' => 'DoRecordDisplay',
					'sort' => 'setName',
					
					'columnNames' => array(
							'id' => 'Internal ID',
					),
					
					'transform' => array(
							'setBonusDesc' => 'TransformSetBonusDesc',
							'setBonusDesc1' => 'TransformSetBonusDesc',
							'setBonusDesc2' => 'TransformSetBonusDesc',
							'setBonusDesc3' => 'TransformSetBonusDesc',
							'setBonusDesc4' => 'TransformSetBonusDesc',
							'setBonusDesc5' => 'TransformSetBonusDesc',
							'setBonusDesc6' => 'TransformSetBonusDesc',
							'setBonusDesc7' => 'TransformSetBonusDesc',
					),
					
					'filters' => array(
							array(
									'record' => 'minedItemSummary',
									'field' => 'setName',
									'thisField' => 'setName',
									'displayName' => 'View&nbsp;Items',
									'type' => 'filter',
							),
					),
			),
			
			'setInfo' => array(
					'displayName' => 'Set Infos',
					'displayNameSingle' => 'Set Info',
					'record' => 'setInfo',
					'table' => 'setInfo',
					'method' => 'DoRecordDisplay',
					'sort' => 'setName',
					
					'columnNames' => array(
					),
					
					'transform' => array(
					),
					
					'filters' => array(
					),
			),
			
			'minedSkills' => array(
					'displayName' => 'Mined Skills',
					'displayNameSingle' => 'Mined Skill',
					'record' => 'minedSkills',
					'table' => 'minedSkills',
					'method' => 'DoRecordDisplay',
					'sort' => 'name',
						
					'transform' => array(
							'mechanic' => 'GetCombatMechanicText',
							'type1' => 'GetCustomCombatMechanicText',
							'type2' => 'GetCustomCombatMechanicText',
							'type3' => 'GetCustomCombatMechanicText',
							'type4' => 'GetCustomCombatMechanicText',
							'type5' => 'GetCustomCombatMechanicText',
							'type6' => 'GetCustomCombatMechanicText',
							'skillType' => 'GetSkillTypeText',
							'description' => 'RemoveTextFormats',
							'descHeader' => 'RemoveTextFormats',
							'coefDescription' => 'RemoveTextFormats',
							'effectLines' => 'RemoveTextFormats',
							'upgradeLines' => 'RemoveTextFormats',
							'chargeFreq' => 'ConvertSkillChargeFreq',
							'buffType' => 'GetBuffTypeText',
					),
						
					'filters' => array(
							array(
									'record' => 'minedSkills',
									'field' => 'id',
									'thisField' => 'prevSkill',
									'displayName' => 'Prev',
									'type' => 'viewRecord',
							),
							array(
									'record' => 'minedSkills',
									'field' => 'id',
									'thisField' => 'nextSkill',
									'displayName' => 'Next',
									'type' => 'viewRecord',
							),
							array(
									'record' => 'minedSkills',
									'field' => 'id',
									'thisField' => 'nextSkill2',
									'displayName' => 'Next2',
									'type' => 'viewRecord',
							),
							array(
									'record' => 'minedSkills',
									'field' => 'id',
									'thisField' => 'baseAbilityId',
									'displayName' => 'Base',
									'type' => 'viewRecord',
							),
					),
			),
			
			'craftedSkills' => array(
					'displayName' => 'Crafted Skills',
					'displayNameSingle' => 'Crafted Skill',
					'record' => 'craftedSkills',
					'table' => 'craftedSkills',
					'method' => 'DoRecordDisplay',
					'sort' => 'name',
					
					'transform' => array(
					),
					
					'filters' => array(
					),
			),
			
			'craftedScripts' => array(
					'displayName' => 'Crafted Scripts',
					'displayNameSingle' => 'Crafted Script',
					'record' => 'craftedScripts',
					'table' => 'craftedScripts',
					'method' => 'DoRecordDisplay',
					'sort' => 'name',
					
					'transform' => array(
					),
					
					'filters' => array(
					),
			),
			
			'craftedScriptDescriptions' => array(
					'displayName' => 'Crafted Script Descriptions',
					'displayNameSingle' => 'Crafted Script Description',
					'record' => 'craftedScriptDescriptions',
					'table' => 'craftedScriptDescriptions',
					'method' => 'DoRecordDisplay',
					'sort' => 'name',
					
					'transform' => array(
					),
					
					'filters' => array(
					),
			),
			
			'minedSkillLines' => array(
					'displayName' => 'Mined Skill Lines',
					'displayNameSingle' => 'Mined Skill Line',
					'record' => 'minedSkillLines',
					'table' => 'minedSkillLines',
					'method' => 'DoRecordDisplay',
					'sort' => 'fullName',
					
					'transform' => array(
							'skillType' => 'GetSkillTypeText',
					),
					
					'filters' => array(
							array(
									'record' => 'skillTree',
									'field' => 'skillTypeName',
									'thisField' => 'fullName',
									'displayName' => 'View Skills',
									'type' => 'filter',
							),
					),
			),
			
			'skillTree' => array(
					'displayName' => 'Skill Tree',
					'displayNameSingle' => 'Skill Tree',
					'record' => 'skillTree',
					'table' => 'skillTree',
					'method' => 'DoRecordDisplay',
					'sort' => array('skillTypeName', 'skillIndex', 'rank'),
					//'sort' => array("FIELD(type, 'Ultimate', 'Active', 'Passive')", 'baseName', 'learnedLevel', 'rank'),
					//'sort' => array('type', 'rank'),
						
					'transform' => array(
							'description' => 'RemoveTextFormats',
					),
						
					'filters' => array(
							array(
									'record' => 'minedSkills',
									'field' => 'id',
									'thisField' => 'abilityId',
									'displayName' => 'View Ability',
									'type' => 'viewRecord',
							),
					),
			),
			/* Old Pre Update 29
			 'cpDisciplines' => array(
					'displayName' => 'Champion Point Disciplines',
					'displayNameSingle' => 'Champion Point Discipline',
					'record' => 'cpDisciplines',
					'table' => 'cpDisciplines',
					'method' => 'DoRecordDisplay',
					'sort' => 'disciplineIndex',
			
					'transform' => array(
							'attribute' => 'GetAttributeText',
							'description' => 'RemoveTextFormats',
					),
			
					'filters' => array(
					),
			),
			
			'cpSkills' => array(
					'displayName' => 'Champion Point Skills',
					'displayNameSingle' => 'Champion Point Skill',
					'record' => 'cpSkills',
					'table' => 'cpSkills',
					'method' => 'DoRecordDisplay',
					'sort' => array('disciplineIndex', 'skillIndex'),
						
					'transform' => array(
							'minDescription' => 'RemoveTextFormats',
							'maxDescription' => 'RemoveTextFormats',
					),
						
					'filters' => array(
					),
			),
			
			'cpSkillDescriptions' => array(
					'displayName' => 'Champion Point Skill Descriptions',
					'displayNameSingle' => 'Champion Point Skill Description',
					'record' => 'cpSkillDescriptions',
					'table' => 'cpSkillDescriptions',
					'method' => 'DoRecordDisplay',
					'sort' => array('abilityId', 'points'),
						
					'transform' => array(
							'description' => 'RemoveTextFormats',
					),
						
					'filters' => array(
					),
			), //*/
			
			'cp2Disciplines' => array(
					'displayName' => 'Champion Point v2 Disciplines',
					'displayNameSingle' => 'Champion Point v2 Discipline',
					'record' => 'cp2Disciplines',
					'table' => 'cp2Disciplines',
					'method' => 'DoRecordDisplay',
					'sort' => 'disciplineId',
					
					'transform' => array(
					),
			
					'filters' => array(
					),
			),
			
			'cp2Skills' => array(
					'displayName' => 'Champion Point v2 Skills',
					'displayNameSingle' => 'Champion Point v2 Skill',
					'record' => 'cp2Skills',
					'table' => 'cp2Skills',
					'method' => 'DoRecordDisplay',
					'sort' => array('disciplineIndex', 'skillIndex'),
						
					'transform' => array(
							'minDescription' => 'RemoveTextFormats',
							'maxDescription' => 'RemoveTextFormats',
					),
						
					'filters' => array(
					),
			),
			
			'cp2SkillLinks' => array(
					'displayName' => 'Champion Point v2 Skill Links',
					'displayNameSingle' => 'Champion v2 Point Skill Link',
					'record' => 'cp2SkillLinks',
					'table' => 'cp2SkillLinks',
					'method' => 'DoRecordDisplay',
					'sort' => array('parentSkillId'),
						
					'transform' => array(
					),
						
					'filters' => array(
					),
					
					'join' => array(
							'parentSkillId' => array(
									'table' => 'cp2Skills',
									'joinField' => 'skillId',
									'fields' => array('parentName' => 'name'),
							),
							'skillId' => array(
									'table' => 'cp2Skills',
									'tableAlias' => 'cp2Skills',
									'joinField' => 'skillId',
									'fields' => array('name' => 'name'),
							),
					),
			),
			
			'cp2ClusterRoots' => array(
					'displayName' => 'Champion Point v2 Cluster Roots',
					'displayNameSingle' => 'Champion Point v2 Cluster Root',
					'record' => 'cp2ClusterRoots',
					'table' => 'cp2ClusterRoots',
					'method' => 'DoRecordDisplay',
					'sort' => array('name'),
						
					'transform' => array(
					),
						
					'filters' => array(
					),
			),
			
			'cp2SkillDescriptions' => array(
					'displayName' => 'Champion Point v2 Skill Descriptions',
					'displayNameSingle' => 'Champion Point v2 Skill Description',
					'record' => 'cp2SkillDescriptions',
					'table' => 'cp2SkillDescriptions',
					'method' => 'DoRecordDisplay',
					'sort' => array('skillId', 'points'),
					
					'transform' => array(
							'description' => 'RemoveTextFormats',
					),
					
					'filters' => array(
					),
			),
			
			'collectibles' => array(
					'displayName' => 'Collectibles',
					'displayNameSingle' => 'Collectible',
					'record' => 'collectibles',
					'table' => 'collectibles',
					'method' => 'DoRecordDisplay',
					'sort' => array('id'),
					
					'transform' => array(
							'categoryType' => 'GetCollectibleCategoryTypeText',
							'furnLimitType' => 'GetEsoFurnLimitTypeRawText',
					),
					
					'filters' => array(
					),
			),
			
			'achievementCategories' => array(
					'displayName' => 'Achievement Categories',
					'displayNameSingle' => 'Achievement Category',
					'record' => 'achievementCategories',
					'table' => 'achievementCategories',
					'method' => 'DoRecordDisplay',
					'sort' => array('categoryIndex', 'subCategoryIndex'),
					
					'transform' => array(
					),
					
					'filters' => array(
							array(
									'record' => 'achievements',
									'field' => 'categoryName',
									'thisField' => 'name',
									'displayName' => 'View Achievements',
									'type' => 'filter',
							),
					),
			),
			
			'achievements' => array(
					'displayName' => 'Achievements',
					'displayNameSingle' => 'Achievement',
					'record' => 'achievements',
					'table' => 'achievements',
					'method' => 'DoRecordDisplay',
					'sort' => array('id'),
					
					'transform' => array(
					),
					
					'filters' => array(
							array(
									'record' => 'achievementCriteria',
									'field' => 'achievementId',
									'thisField' => 'id',
									'displayName' => 'View Criteria',
									'type' => 'filter',
							),
					),
			),
			
			'achievementCriteria' => array(
					'displayName' => 'Achievement Criteria',
					'displayNameSingle' => 'Achievement Criteria',
					'record' => 'achievementCriteria',
					'table' => 'achievementCriteria',
					'method' => 'DoRecordDisplay',
					'sort' => array('achievementId', 'criteriaIndex'),
					
					'join' => array(
							'achievementId' => array(
									'joinField' => 'id',
									'table' => 'achievements',
									'fields' => array('name'),
							),
					),
					
					'transform' => array(
					),
					
					'filters' => array(
							array(
									'record' => 'achievements',
									'field' => 'id',
									'thisField' => 'achievementId',
									'displayName' => 'View Achievement',
									'type' => 'viewRecord',
							),
					),
			),
			
			'antiquityLeads' => array(
					'displayName' => 'Antiquity Lead',
					'displayNameSingle' => 'Antiquity Lead',
					'record' => 'antiquityLeads',
					'table' => 'antiquityLeads',
					'method' => 'DoRecordDisplay',
					'sort' => array('name'),
					
					'join' => array(
					),
					
					'transform' => array(
					),
					
					'filters' => array(
					),
			),
			
			'zones' => array(
					'displayName' => 'Zones',
					'displayNameSingle' => 'Zone',
					'record' => 'zones',
					'table' => 'zones',
					'method' => 'DoRecordDisplay',
					'sort' => array('zoneName', 'subZoneName'),
					
					'join' => array(
					),
					
					'transform' => array(
					),
					
					'filters' => array(
					),
			),
			
			'zonePois' => array(
					'displayName' => 'Zone POIs',
					'displayNameSingle' => 'Zone POI',
					'record' => 'zonePois',
					'table' => 'zonePois',
					'method' => 'DoRecordDisplay',
					'sort' => array('zoneName', 'subZoneName', 'poiIndex'),
					
					'join' => array(
					),
					
					'transform' => array(
					),
					
					'filters' => array(
					),
			),
			
			'tributeCards' => array(
					'displayName' => 'Tribute Card',
					'displayNameSingle' => 'Tribute Cards',
					'record' => 'tributeCards',
					'table' => 'tributeCards',
					'method' => 'DoRecordDisplay',
					'sort' => 'name',
					
					'transform' => array(
					),
					
					'filters' => array(
					),
			),
			
			'tributePatrons' => array(
					'displayName' => 'Tribute Patron',
					'displayNameSingle' => 'Tribute Patrons',
					'record' => 'tributePatrons',
					'table' => 'tributePatrons',
					'method' => 'DoRecordDisplay',
					'sort' => 'name',
					
					'transform' => array(
					),
					
					'filters' => array(
					),
			),
			
			'campaignInfo' => array(
					'displayName' => 'Campaigns',
					'displayNameSingle' => 'Campaign',
					'record' => 'campaignInfo',
					'table' => 'campaignInfo',
					'method' => 'DoRecordDisplay',
					'sort' => 'name',
					
					'transform' => array(
							'startTime' => 'GetTimestampDateFormatWithDiff',
							'endTime' => 'GetTimestampDateFormatWithDiff',
							'lastUpdated' => 'GetTimestampDateFormatWithDiff',
							'entriesUpdated' => 'GetTimestampDateFormatWithDiff',
					),
					
					'filters' => array(
					),
			),
			
			'campaignLeaderboards' => array(
					'displayName' => 'Campaign Leaderboards',
					'displayNameSingle' => 'Campaign Leaderboard',
					'record' => 'campaignLeaderboards',
					'table' => 'campaignLeaderboards',
					'method' => 'DoRecordDisplay',
					'sort' => array('server', 'campaignId', 'points DESC'),
					
					'join' => array(
							'campaignId' => array(
									'joinField' => 'id',
									'table' => 'campaignInfo',
									'fields' => array('campaignName' => 'name'),
							),
					),
					
					'transform' => array(
							'class' => 'GetEsoClassIdText',
							'alliance' => 'GetEsoAllianceShortText',
					),
					
					'filters' => array(
					),
			),
			
			'endeavors' => array(
					'displayName' => 'Endeavors',
					'displayNameSingle' => 'Endeavor',
					'record' => 'endeavors',
					'table' => 'endeavors',
					'method' => 'DoRecordDisplay',
					
					'join' => array(
					),
					
					'transform' => array(
					),
					
					'filters' => array(
					),
			),
			
			'goldenVendorItems' => array(
					'displayName' => 'Golden Vendor Items',
					'displayNameSingle' => 'Golden Vendor Item',
					'record' => 'goldenVendorItems',
					'table' => 'goldenVendorItems',
					'method' => 'DoRecordDisplay',
					
					'join' => array(
					),
					
					'transform' => array(
					),
					
					'filters' => array(
					),
			),
			
			'luxuryVendorItems' => array(
					'displayName' => 'Luxury Vendor Items',
					'displayNameSingle' => 'Luxury Vendor Item',
					'record' => 'luxuryVendorItems',
					'table' => 'luxuryVendorItems',
					'method' => 'DoRecordDisplay',
					
					'join' => array(
					),
					
					'transform' => array(
					),
					
					'filters' => array(
					),
			),
			
	);
	
	
	public static $SEARCH_TYPE_OPTIONS = array(
			'All' => '',
			'Achievements' => 'achievements',
			'Antiquity Leads' => 'antiquityLeads',
			'Books' => 'book',
			'Collectibles' => 'collectibles',
			'Crown Store Items' => 'crownStoreItems',
			'Ingredients' => 'ingredient',
			'Items' => 'minedItemSummary',
			'Logged Items' => 'item',
			'Loot' => 'npcLoot',
			'Loot Sources' => 'lootSources',
			'NPCs' => 'npc',			
			//'Old Quests' => 'oldQuest',
			//'Old Quest Stages' => 'oldQuestStage',
			'Quests' => 'quest',
			'Quests (Unique)' => 'uniqueQuest',
			'Quest Steps' => 'questStep',
			'Quest Conditions' => 'questCondition',
			'Quest Item' => 'questItem',
			'Quest Reward' => 'questReward',
			'Recipes' => 'recipe',
			'Sets' => 'setSummary',
			'Skills' => 'minedSkills',
			'Tribute Cards' => 'tributeCards',
			'Tribute Patrons' => 'tributePatrons',
			'Zones' => 'zones',
			'Zone POIs' => 'zonePois',
	);
	
	
	public static $SEARCH_TYPE_EXTRAS = array(
			'achievements' => array('achievementCriteria', 'achievementCategories'),
	);
	
	
	public static $PTS_SEARCH_DATA = array(
			'minedItemSummary##pts' => array(
					'searchFields' => array('name', 'description', 'abilityDesc', 'enchantName', 'enchantDesc', 'traitDesc', 'setName', 'setBonusDesc1', 'setBonusDesc2', 'setBonusDesc3', 'setBonusDesc4', 'setBonusDesc5', 'setBonusDesc6', 'setBonusDesc7'),
					'fields' => array(
							'itemId' => 'id',
							'name' => 'name',
							//'itemId' => 'note',
							'icon' => 'icon',
					),
			),
			'setSummary##pts' => array(
					'searchFields' => array('setName', 'setBonusDesc1', 'setBonusDesc2', 'setBonusDesc3', 'setBonusDesc4', 'setBonusDesc5', 'setBonusDesc6', 'setBonusDesc7'),
					'fields' => array(
							'id' => 'id',
							'setName' => 'name',
							'setBonusDesc' => 'note',
					),
			),
			'minedSkills##pts' => array(
					'searchFields' => array('name', 'description', 'descHeader'),
					'fields' => array(
							'id' => 'id',
							'name' => 'name',
							'description' => 'note',
							'texture' => 'icon',
					),
			),
	);
	
	
	public static $SEARCH_DATA = array(
			'achievementCategories' => array(
					'searchFields' => array('name'),
					'fields' => array(
							'id' => 'id',
							'name' => 'name',
					),
			),
			'achievements' => array(
					'searchFields' => array('name', 'description', 'title'),
					'fields' => array(
							'id' => 'id',
							'name' => 'name',
							'icon' => 'icon',
					),
			),
			'achievementCriteria' => array(
					'searchFields' => array('description'),
					'fields' => array(
							'id' => 'id',
							'description' => 'name',
					),
			),
			'antiquityLeads' => array(
					'searchFields' => array('name', 'loreName1', 'loreDescription1', 'loreName2', 'loreDescription2', 'loreName3', 'loreDescription3', 'loreName4', 'loreDescription4', 'loreName5', 'loreDescription5'),
					'fields' => array(
							'id' => 'id',
							'name' => 'name',
							'icon' => 'icon',
					),
			),
			'book' => array(
					'searchFields' => array('title', 'body'),
					'fields' => array(
							'id' => 'id',
							'title' => 'name',
							'icon' => 'icon',
					),
			),
			'collectibles' => array(
					'searchFields' => array('name', 'description', 'nickname', 'hint'),
					'fields' => array(
							'id' => 'id',
							'name' => 'name',
							'icon' => 'icon',
					),
			),
			'crownStoreItems' => array(
					'searchFields' => array('name', 'description'),
					'fields' => array(
							'id' => 'id',
							'name' => 'name',
					),
			),
			'item' => array(
					'searchFields' => array('name'),
					'fields' => array(
							'id' => 'id',
							'name' => 'name',
							'icon' => 'icon',
					),
			),
			'oldQuest' => array(
					'searchFields' => array('name', 'objective'),
					'fields' => array(
							'id' => 'id',
							'name' => 'name',
					),
			),
			'oldQuestStage' => array(
					'searchFields' => array('objective', 'overrideText'),
					'fields' => array(
							'id' => 'id',
							'questId' => 'questId',
							'objective' => 'name',
					),
			),
			'quest' => array(
					'searchFields' => array('name', 'objective', 'backgroundText'),
					'fields' => array(
							'id' => 'id',
							'name' => 'name',
							'internalId' => 'note',
					),
			),
			'uniqueQuest' => array(
					'searchFields' => array('name', 'objective', 'backgroundText'),
					'fields' => array(
							'id' => 'id',
							'name' => 'name',
							'internalId' => 'note',
					),
			),
			'questStep' => array(
					'searchFields' => array('text', 'overrideText'),
					'fields' => array(
							'id' => 'id',
							'questId' => 'questId',
							'text' => 'name',
					),
			),
			'questCondition' => array(
					'searchFields' => array('text'),
					'fields' => array(
							'id' => 'id',
							'questId' => 'questId',
							'text' => 'name',
					),
			),
			'questItem' => array(
					'searchFields' => array('name', 'description'),
					'fields' => array(
							'id' => 'id',
							'name' => 'name',
							'itemId' => 'note',
					),
			),
			'questReward' => array(
					'searchFields' => array('name'),
					'fields' => array(
							'id' => 'id',
							'name' => 'name',
							'itemId' => 'note',
					),
			),
			'questGoldReward' => array(
					'searchFields' => array('questName'),
					'fields' => array(
							'id' => 'id',
							'questName' => 'name',
					),
			),
			'questXPReward' => array(
					'searchFields' => array('questName'),
					'fields' => array(
							'id' => 'id',
							'questName' => 'name',
					),
			),
			'npc' => array(
					'searchFields' => array('name'),
					'fields' => array(
							'id' => 'id',
							'name' => 'name',
					),
			),
			'lootSources' => array(
					'searchFields' => array('name'),
					'view' => array(
								'url' => '/viewNpcLoot.php',
								'parameter' => 'source',
								'column' => 'name',
								'title' => 'View Loot',
					),
					'fields' => array(
							'id' => 'id',
							'name' => 'name',
							'count' => 'note',
					),
			),
			'npcLoot' => array(
					'searchFields' => array('itemName'),
					'view' => array(
								'url' => '/viewNpcLoot.php',
								'parameter' => 'item',
								'column' => 'name',
								'title' => 'View Loot Sources',
					),
					'fields' => array(
							'id' => 'id',
							'itemName' => 'name',
							'zone' => 'note',
							'icon' => 'icon',
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
			/*						// Far too slow with current search implementation
			'minedItem' => array(
					//'searchFields' => array('name', 'description', 'setName', 'abilityName', 'abilityDesc', 'bookTitle', 'comment', 'setBonusDesc1', 'setBonusDesc2', 'setBonusDesc3', 'setBonusDesc4', 'setBonusDesc5'),
					'searchFields' => array(),
					'fields' => array(
							'id' => 'id',
							'name' => 'name',
					),
			), */
			'minedItemSummary' => array(
					'searchFields' => array('name', 'description', 'abilityDesc', 'enchantName', 'enchantDesc', 'traitDesc', 'setName', 'setBonusDesc1', 'setBonusDesc2', 'setBonusDesc3', 'setBonusDesc4', 'setBonusDesc5', 'setBonusDesc6', 'setBonusDesc7'),
					'fields' => array(
							'itemId' => 'id',
							'name' => 'name',
							//'itemId' => 'note',
							'icon' => 'icon',
					),
			),
			'setSummary' => array(
					'searchFields' => array('setName', 'setBonusDesc1', 'setBonusDesc2', 'setBonusDesc3', 'setBonusDesc4', 'setBonusDesc5', 'setBonusDesc6', 'setBonusDesc7', 'type', 'sources'),
					'fields' => array(
							'id' => 'id',
							'setName' => 'name',
							'setBonusDesc' => 'note',
					),
			),
			'minedSkills' => array(
					'searchFields' => array('name', 'description', 'descHeader'),
					'fields' => array(
							'id' => 'id',
							'name' => 'name',
							'description' => 'note',
							'texture' => 'icon',
					),
			),
			'zones' => array(
					'searchFields' => array('zoneName', 'subZoneName', 'description', 'mapName'),
					'fields' => array(
							'id' => 'id',
							'name' => 'name',
							'description' => 'note',
					),
			),
			'zonePois' => array(
					'searchFields' => array('zoneName', 'subZoneName', 'mapIcon', 'objName', 'objStartDesc', 'objEndDesc'),
					'fields' => array(
							'id' => 'id',
							'zoneName' => 'name',
							'description' => 'note',
							'mapIcon' => 'icon',
					),
			),
			
			'tributeCards' => array(
					'searchFields' => array('name', 'description'),
					'fields' => array(
							'id' => 'id',
							'name' => 'name',
							'description' => 'note',
							'texture' => 'icon',
					),
			),
			
			'tributePatrons' => array(
					'searchFields' => array('name', 'loreDescription', 'playStyleDescription'),
					'fields' => array(
							'id' => 'id',
							'name' => 'name',
							'description' => 'note',
							'smallIcon' => 'icon',
					),
			),
	);
	
	
	public static $SEARCH_FIELDS = array(
			'id' => self::FIELD_INTID,
			'icon' => self::FIELD_GAMEICON,
			'type' => self::FIELD_STRING,
			'name' => self::FIELD_STRING,
			'note' => self::FIELD_STRING,
	);
	
	
	public function __construct ()
	{
		//SetupUespSession();
		
		$this->PTS_VERSION_NAME = GetEsoUpdateName(self::ENABLE_PTS_VERSION);
		
		uasort(self::$RECORD_TYPES, 'CompareRecordTypeByDisplayName');
		
			// TODO: Static initialization?
		self::$RECORD_TYPES['book']['fields'] = self::$BOOK_FIELDS;
		self::$RECORD_TYPES['chest']['fields'] = self::$CHEST_FIELDS;
		self::$RECORD_TYPES['item']['fields'] = self::$ITEM_FIELDS;
		self::$RECORD_TYPES['location']['fields'] = self::$LOCATION_FIELDS;
		self::$RECORD_TYPES['crownStoreItems']['fields'] = self::$CROWNSTOREITEM_FIELDS;
		//self::$RECORD_TYPES['oldQuest']['fields'] = self::$OLDQUEST_FIELDS;
		//self::$RECORD_TYPES['oldQuestStage']['fields'] = self::$OLDQUESTSTAGE_FIELDS;
		self::$RECORD_TYPES['quest']['fields'] = self::$QUEST_FIELDS;
		self::$RECORD_TYPES['uniqueQuest']['fields'] = self::$QUEST_FIELDS;
		self::$RECORD_TYPES['questStep']['fields'] = self::$QUESTSTEP_FIELDS;
		self::$RECORD_TYPES['questCondition']['fields'] = self::$QUESTCONDITION_FIELDS;
		self::$RECORD_TYPES['questReward']['fields'] = self::$QUESTREWARD_FIELDS;
		self::$RECORD_TYPES['questGoldReward']['fields'] = self::$QUESTGOLDREWARD_FIELDS;
		self::$RECORD_TYPES['questXPReward']['fields'] = self::$QUESTXPREWARD_FIELDS;
		self::$RECORD_TYPES['questItem']['fields'] = self::$QUESTITEM_FIELDS;
		self::$RECORD_TYPES['npc']['fields'] = self::$NPC_FIELDS;
		self::$RECORD_TYPES['npcLocations']['fields'] = self::$NPC_LOCATION_FIELDS;
		self::$RECORD_TYPES['lootSources']['fields'] = self::$LOOTSOURCE_FIELDS;
		self::$RECORD_TYPES['npcLoot']['fields'] = self::$NPCLOOT_FIELDS;
		self::$RECORD_TYPES['recipe']['fields'] = self::$RECIPE_FIELDS;
		self::$RECORD_TYPES['ingredient']['fields'] = self::$INGREDIENT_FIELDS;
		self::$RECORD_TYPES['user']['fields'] = self::$USER_FIELDS;
		self::$RECORD_TYPES['minedItem']['fields'] = self::$MINEDITEM_FIELDS;
		self::$RECORD_TYPES['minedItemSummary']['fields'] = self::$MINEDITEMSUMMARY_FIELDS;
		self::$RECORD_TYPES['setSummary']['fields'] = self::$SETSUMMARY_FIELDS;
		self::$RECORD_TYPES['setInfo']['fields'] = self::$SETINFO_FIELDS;
		self::$RECORD_TYPES['minedSkills']['fields'] = self::$SKILLDUMP_FIELDS;
		self::$RECORD_TYPES['craftedSkills']['fields'] = self::$CRAFTEDSKILL_FIELDS;
		self::$RECORD_TYPES['craftedScripts']['fields'] = self::$CRAFTEDSCRIPT_FIELDS;
		self::$RECORD_TYPES['craftedScriptDescriptions']['fields'] = self::$CRAFTEDSCRIPTDESCRIPTION_FIELDS;
		self::$RECORD_TYPES['minedSkillLines']['fields'] = self::$SKILLLINE_FIELDS;
		self::$RECORD_TYPES['skillTree']['fields'] = self::$SKILLTREE_FIELDS;
		//self::$RECORD_TYPES['cpDisciplines']['fields'] = self::$CPDISCIPLINE_FIELDS;
		//self::$RECORD_TYPES['cpSkills']['fields'] = self::$CPSKILL_FIELDS;
		//self::$RECORD_TYPES['cpSkillDescriptions']['fields'] = self::$CPSKILLDESCRIPTION_FIELDS;
		self::$RECORD_TYPES['cp2Disciplines']['fields'] = self::$CP2DISCIPLINE_FIELDS;
		self::$RECORD_TYPES['cp2Skills']['fields'] = self::$CP2SKILL_FIELDS;
		self::$RECORD_TYPES['cp2SkillLinks']['fields'] = self::$CP2SKILLLINK_FIELDS;
		self::$RECORD_TYPES['cp2ClusterRoots']['fields'] = self::$CP2CLUSTERROOT_FIELDS;
		self::$RECORD_TYPES['cp2SkillDescriptions']['fields'] = self::$CP2SKILLDESCRIPTION_FIELDS;
		self::$RECORD_TYPES['collectibles']['fields'] = self::$COLLECTIBLE_FIELDS;
		self::$RECORD_TYPES['achievementCategories']['fields'] = self::$ACHIEVEMENTCATEGORY_FIELDS;
		self::$RECORD_TYPES['achievements']['fields'] = self::$ACHIEVEMENT_FIELDS;
		self::$RECORD_TYPES['achievementCriteria']['fields'] = self::$ACHIEVEMENTCRITERIA_FIELDS;
		self::$RECORD_TYPES['logInfo']['fields'] = self::$LOGINFO_FIELDS;
		self::$RECORD_TYPES['antiquityLeads']['fields'] = self::$ANTIQUITYLEAD_FIELDS;
		self::$RECORD_TYPES['zones']['fields'] = self::$ZONE_FIELDS;
		self::$RECORD_TYPES['zonePois']['fields'] = self::$ZONEPOI_FIELDS;
		self::$RECORD_TYPES['tributeCards']['fields'] = self::$TRIBUTECARD_FIELDS;
		self::$RECORD_TYPES['tributePatrons']['fields'] = self::$TRIBUTEPATRON_FIELDS;
		self::$RECORD_TYPES['campaignInfo']['fields'] = self::$CAMPAIGNINFO_FIELDS;
		self::$RECORD_TYPES['campaignLeaderboards']['fields'] = self::$CAMPAIGNLEADERBOARDS_FIELDS;
		self::$RECORD_TYPES['endeavors']['fields'] = self::$ENDEAVOR_FIELDS;
		self::$RECORD_TYPES['goldenVendorItems']['fields'] = self::$GOLDENVENDORITEM_FIELDS;
		self::$RECORD_TYPES['luxuryVendorItems']['fields'] = self::$LUXURYVENDORITEM_FIELDS;
		
		$this->EnablePtsRecords();
		
		$this->InitDatabase();
		$this->SetInputParams();
		$this->ParseInputParams();
		$this->LoadLogInfo();
	}
	
	
	public function EnablePtsRecords()
	{
		if (self::ENABLE_PTS_VERSION == "") return false;
		
		foreach (self::$PTS_RECORD_TYPES as $key => $records)
		{
			$newKey = $this->TransformPtsRecordString($key);
			
			self::$RECORD_TYPES[$newKey] = $this->TransformPtsRecordTypes($records);
			
			if (startsWith($key, "minedItem##")) self::$RECORD_TYPES[$newKey]['fields'] = self::$MINEDITEM_FIELDS;
			if (startsWith($key, "setSummary##")) self::$RECORD_TYPES[$newKey]['fields'] = self::$SETSUMMARY_FIELDS;
			if (startsWith($key, "minedItemSummary##")) self::$RECORD_TYPES[$newKey]['fields'] = self::$MINEDITEMSUMMARY_FIELDS;
			if (startsWith($key, "minedSkills##")) self::$RECORD_TYPES[$newKey]['fields'] = self::$SKILLDUMP_FIELDS;
			if (startsWith($key, "craftedSkills##")) self::$RECORD_TYPES[$newKey]['fields'] = self::$CRAFTEDSKILL_FIELDS;
			if (startsWith($key, "craftedScripts##")) self::$RECORD_TYPES[$newKey]['fields'] = self::$CRAFTEDSCRIPT_FIELDS;
			if (startsWith($key, "craftedScriptDescriptions##")) self::$RECORD_TYPES[$newKey]['fields'] = self::$CRAFTEDSCRIPTDESCRIPTION_FIELDS;
		}
		
		foreach (self::$PTS_SEARCH_TYPE_OPTIONS as $key => $name)
		{
			$newKey = $this->TransformPtsRecordString($key);
			self::$SEARCH_TYPE_OPTIONS[$newKey] = $this->TransformPtsRecordString($name);
		}
		
		foreach (self::$PTS_SEARCH_DATA as $key => $records)
		{
			$newKey = $this->TransformPtsRecordString($key);
			self::$SEARCH_DATA[$newKey] = $records;
		}
		
		return true;
	}
	
	
	public function TransformPtsRecordTypes($records)
	{
		$newRecords = $records;
		
		$newRecords['displayName'] = $this->TransformPtsRecordString($newRecords['displayName']);
		$newRecords['displayNameSingle'] = $this->TransformPtsRecordString($newRecords['displayNameSingle']);
		$newRecords['record'] = $this->TransformPtsRecordString($newRecords['record']);
		$newRecords['table'] = $this->TransformPtsRecordString($newRecords['table']);
		$newRecords['message'] = $this->TransformPtsRecordString($newRecords['message']);
		
		if ($newRecords['transform']) 
		{
		}
		
		if ($newRecords['filters']) 
		{
			foreach ($newRecords['filters'] as $i => $filter)
			{
				if ($filter['record'] != null)
				{
					$newRecords['filters'][$i]['record'] = $this->TransformPtsRecordString($filter['record']);
				}
			}
		}
		
		return $newRecords;
	}
	
	
	public function TransformPtsRecordString($string)
	{
		if ($string === null) return null;
		
		$newString = str_replace("##", self::ENABLE_PTS_VERSION, $string);
		$newString = str_replace("__NAME__", $this->PTS_VERSION_NAME, $newString);
		
		return $newString;
	}
	
	
	private function InitDatabase ()
	{
		global $uespEsoLogReadDBHost, $uespEsoLogReadUser, $uespEsoLogReadPW, $uespEsoLogDatabase;
		
		if ($this->dbReadInitialized) return true;
		
		$this->db = new mysqli($uespEsoLogReadDBHost, $uespEsoLogReadUser, $uespEsoLogReadPW, $uespEsoLogDatabase);
		if ($this->db->connect_error) return $this->ReportError("Could not connect to mysql database!");
		
		UpdateEsoPageViews("logViews");
		
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
		return GetEsoItemTraitFullText($value);
	}
	
	
	public function GetItemStyleText ($value)
	{
		return GetEsoItemStyleText($value);
	}
	
	
	public function GetItemQualityText ($value)
	{
		return GetEsoItemQualityText($value);
	}
	
	
	public function GetAttributeText($value)
	{
		return GetEsoAttributeText($value);
	}
	
	
	public function GetTimestampText ($value)
	{
		if ($value <= 0) return "";
		return date('Y-m-d H:i:s', $value);
	}
	
	
	public function GetSkillTypeText ($value)
	{
		return GetEsoSkillTypeText($value);
	}
	
	
	public function GetEsoClassIdText($value)
	{
		return GetEsoClassIdText($value);
	}
	
	
	public function GetEsoAllianceText($value)
	{
		return GetEsoAllianceText($value);
	}
	
	
	public function GetEsoAllianceShortText($value)
	{
		return GetEsoAllianceShortText($value);
	}
	
	
	public function GetBuffTypeText ($value) {
		return GetEsoBuffTypeText($value);
	}
	
	
	public function ConvertSkillChargeFreq($value) {
		return intval($value) / 1000;
	}
	
	
	public function GetCombatMechanicText ($value)
	{
		return GetEsoMechanicTypeText($value);
	}
	
	
	public function GetCustomCombatMechanicText ($value)
	{
		return GetEsoCustomMechanicTypeText($value);
	}
	
	
	public function GetCombatMechanicText34 ($value)
	{
		return GetEsoMechanicTypeText34($value);
	}
	
	
	public function GetCustomCombatMechanicText34 ($value)
	{
		return GetEsoCustomMechanicTypeText($value);	//TODO ?
	}
	
	
	public function GetTimestampDateFormat ($value)
	{
		if ($value <= 0) return '-';
		return date('Y-m-d H:i:s', $value);
	}
	
	
	public function FormatTimeDiffSeconds ($diff)
	{
		$output = "";
		$diff = abs($diff);
		
		if ($diff == 0) return "just now";
		
		$seconds = $diff;
		$minutes = $diff / 60;
		$hours = $diff / 3600;
		$days = $diff / 86400;
		$months = $diff / (86400 * 30.5);
		$years = $diff / (86400 * 365.25);
		
		if ($years >= 1)
		{
			$output = number_format($years, 1) . "year";
			if ($years > 1.1) $output .= "s";
		}
		elseif ($months >= 1)
		{
			$output = number_format($months, 1) . " month";
			if ($months > 1.1) $output .= "s";
		}
		elseif ($days >= 1)
		{
			$output = number_format($days, 1) . " day";
			if ($days > 1.1) $output .= "s";
		}
		elseif ($hours >= 1)
		{
			$output = number_format($hours, 1) . " hour";
			if ($hours > 1.1) $output .= "s";
		}
		elseif ($minutes >=  1)
		{
			$output = number_format($minutes, 1) . " minute";
			if ($minutes > 1.1) $output .= "s";
		}
		else
		{
			$output = $seconds . " second";
			if ($seconds > 1) $output .= "s";
		}
		
		return $output;
	}
	
	
	public function GetTimestampDateFormatWithDiff ($value)
	{
		if ($value <= 0) return '-';
		$date = date('Y-m-d H:i:s', $value);
		
		$diffTime = time() - $value;
		
		if ($diffTime > 0)
		{
			$diff = $this->FormatTimeDiffSeconds($diffTime) . " ago";
		}
		elseif ($diffTime < 0)
		{
			$diff = "in " . $this->FormatTimeDiffSeconds($diffTime);
		}
		else
		{
			$diff = "just now";
		}
		
		return $date . " ($diff)";
	}
	
	
	public function GetBookMediumText ($value)
	{
			// TODO: Move to EsoCommon.php
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
		return GetEsoItemArmorTypeText($value);
	}
	
	
	public function GetItemWeaponTypeText ($value)
	{
		return GetEsoItemWeaponTypeText($value);
	}
	
	
	public function GetItemTypeText ($value)
	{
		return GetEsoItemTypeText($value);
	}
	
	
	public function GetEsoReactionText ($value)
	{
		return GetEsoReactionText($value);
	}
	
	
	public function GetItemSpecialTypeText ($value)
	{
		return GetEsoItemSpecialTypeText($value);
	}
	
	
	public function GetItemEquipTypeText ($value)
	{
		return GetEsoItemEquipTypeText($value);
	}
	
	
	public function GetEsoQuestRepeatTypeText($value)
	{
		return GetEsoQuestRepeatTypeText($value);
	}
	
	
	public function GetEsoQuestStepTypeText($value)
	{
		return GetEsoQuestStepTypeText($value);
	}
	
	
	public function GetEsoQuestStepVisibilityTypeText($value)
	{
		return GetEsoQuestStepVisibilityTypeText($value);
	}
	
	
	public function GetEsoQuestTypeText($value)
	{
		return GetEsoQuestTypeText($value);
	}
	
	
	public function GetEsoMapPinTypeText($value)
	{
		return GetEsoMapPinTypeText($value);
	}
		
	
	public function GetChestQualityText($value)
	{
		return GetEsoChestTypeText($value);
	}
	
	
	public function GetEsoQuestRewardTypeText($value)
	{
		return GetEsoQuestRewardTypeText($value);
	}	
	
	
	public function GetEsoQuestRewardItemTypeText($value)
	{
		return GetEsoQuestRewardItemTypeText($value);
	}
	
	
	public function GetCollectibleCategoryTypeText($value) 
	{
		return GetEsoCollectibleCategoryTypeText($value);
	}
	
	public function GetEsoFurnLimitTypeRawText($value)
	{
		return GetEsoFurnLimitTypeRawText($value);
	}
	
	
	public function RemoveTextFormats ($text)
	{
		return FormatRemoveEsoItemDescriptionText($text);
	}
	
	
	public function FormatResultItemLinkPts ($link, $itemData)
	{
		return $this->FormatResultItemLink ($link, $itemData, true);
	}
	
	
	public function FormatResultItemLink ($link, $itemData, $isPts = false)
	{
		if (!$this->IsOutputHTML()) return $value;
		
		$itemId = intval($itemData['itemId']);
		if ($itemId <= 0) return $link;
		
		$ptsVersion = "";
		if ($isPts) $ptsVersion = "&version=" . self::ENABLE_PTS_VERSION . "pts";
		$dataLink = "<a href=\"itemLink.php?itemid=$itemId&summary=1$ptsVersion\">";
		
		$query = "SELECT name FROM minedItemSummary WHERE itemId=$itemId";
		$result = $this->db->query($query);
		if ($result === false) return $dataLink . $link . "</a>";
		
		$resultItem = $result->fetch_assoc();
		if ($resultItem == null) return $dataLink . $link . "</a>";
		
		$resultName = $resultItem['name'];
		if ($resultName == null || $resultName == '') $resultName = $link;
		
		return $dataLink . $resultName . "</a>";
	}
	
	
	public function TransformRecordValue ($recordInfo, $field, $value, $itemData)
	{
		if ($this->displayRawValues) return $value;
		
		if (!array_key_exists('transform', $recordInfo)) return $value;
		if (!array_key_exists($field, $recordInfo['transform'])) return $value;
		
		if ($recordInfo['transform'][$field] == '') return $value;
		
		$method = $recordInfo['transform'][$field];
		return $this->$method($value, $itemData);
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
	<meta charset="utf-8" />
	<link rel="stylesheet" href="//esolog-static.uesp.net/resources/theme.default.min.css?sept2024a" />
	<link rel="stylesheet" href="//esolog-static.uesp.net/viewlog.css?sept2024b" />
	<script type="text/javascript" src="//esolog-static.uesp.net/resources/jquery-1.10.2.js"></script>
	<script type="text/javascript" src="//esolog-static.uesp.net/resources/jquery.tablesorter.min.js?sept2024a"></script>
	<script type="text/javascript" src="//esolog-static.uesp.net/viewlog.js?sept2024a"></script>
</head>
<body>
<?php
		return true;
	}
	
	
	public function WritePageFooter()
	{
		if ($this->outputFormat == 'CSV') return true;
		
		if (self::SHOW_QUERY)
		{
			print("<!-- Last Query: \n");
			print($this->lastQuery);
			print("  -->\n");
		}
		
		$lastUpdate = $this->logInfos['lastUpdate'];
		if ($lastUpdate == null) $lastUpdate = '?';
		
		$output = "<hr>\n<div class=\"elvLastUpdate\">Data last updated on $lastUpdate</div>";
		//$output .= "<br>{$_SESSION['uesp_eso_morrowind']}";
		
		$output .= "<br/><div class='elvLicense'>Most content here is available under the same Attribute-ShareAlike 2.5 License as the UESP wiki. See <a href='https://en.uesp.net/wiki/UESPWiki:Copyright_and_Ownership'>Copyright and Ownership</a> for more information.";
		$output .= " Some data is extracted directly from the ESO game data files and copyright is owned by Zenimax Online Studios.</div>";
		
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
			//$this->ReportError("Failed to get record count for $table!");
			return 0;
		}
		
		$row = $result->fetch_row();
		return $row[0];
	}
	
	
	public function MakeShortLink($url)
	{
		if (!$this->IsOutputHTML()) return $url;
		
		$output = "<a href=\"$url\">link</a>";
		return $output;
	}
	
	
	public function MakeItemLink($itemLink)
	{
		if (!$this->IsOutputHTML()) return $itemLink;
		
		if (substr($itemLink, 0, 2) != "|H") return $itemLink;
		
		$urlLink = urlencode($itemLink);
		$output = "<a href=\"itemLink.php?link=$urlLink\">" . $itemLink . "</a>";
		return $output;
	}	
	
	
	public function MakeMinedItemLink ($value, $itemData)
	{
		if (!$this->IsOutputHTML()) return $value;
		
		$itemId = $itemData['itemId'];
		$itemIntLevel = $itemData['internalLevel'];
		$itemIntType = $itemData['internalSubtype'];
		
		$output = "<a href=\"itemLink.php?itemid=$itemId&intlevel=$itemIntLevel&inttype=$itemIntType\">" . $value . "</a>";
		return $output;
	}

	
	public function MakeMinedItemLinkPts ($value, $itemData)
	{
		if (!$this->IsOutputHTML()) return $value;
		
		$itemId = $itemData['itemId'];
		$itemIntLevel = $itemData['internalLevel'];
		$itemIntType = $itemData['internalSubtype'];
		
		$ptsVersion = self::ENABLE_PTS_VERSION . "pts";
		
		$output = "<a href=\"itemLink.php?itemid=$itemId&intlevel=$itemIntLevel&inttype=$itemIntType&version=$ptsVersion\">" . $value . "</a>";
		return $output;
	}
	
	
	public function MakeMinedItemSummaryLink ($value, $itemData)
	{
		if (!$this->IsOutputHTML()) return $value;
		
		$itemId = $itemData['itemId'];
		
		$output = "<a href=\"itemLink.php?itemid=$itemId&summary\">" . $value . "</a>";
		return $output;
	}
	
	
	public function MakeMinedItemSummaryLinkPts ($value, $itemData)
	{
		if (!$this->IsOutputHTML()) return $value;
		
		$itemId = $itemData['itemId'];
		$ptsVersion = self::ENABLE_PTS_VERSION . "pts";
	
		$output = "<a href=\"itemLink.php?itemid=$itemId&summary&version=$ptsVersion\">" . $value . "</a>";
		return $output;
	}
	
	
	public function TransformSetBonusDesc ($value, $itemData)
	{
		if (!$this->IsOutputHTML()) return $value;
		
		return preg_replace('/\n/', '<br />', $value);
	}
	
	
	public function DoHomePage ($recordInfo)
	{
?>
	<h1>ESO: Record Types</h1>
<div style="background-color:#cfc; padding:5px; border: solid 1px gray;">
The username/password for some data views is <b>esolog</b> / <b>esolog</b> due to issues with Bots DDOSing this service.
</div>
The ESO log viewer displays the raw game data for Elder Scrolls Online as collected by the <a href="//www.uesp.net/wiki/User:Daveh/uespLog_Addon">uespLog add-on</a>. It was created to be a tool for UESP editors and patrollers to
use as part of improving and maintaining <a href="//www.uesp.net/">UESPWiki</a>. It is not intended to be a user-friendly way to learn about the Elder Scrolls games.
If you do not understand what this information means, or how to use this webpage, then go to <a href="//www.uesp.net/"><b>UESPWiki</b></a> for user-friendly game information.
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
	<p />
	<b>Other Things</b>
	<ul class='elvRecordTypeList'>
		<li><a href="/itemSearch.php">Advanced Item Search</a></li>
		<li><a href="/viewMinedItems.php">Mined Items by Category</a></li>
		<li><a href="/viewCollectibles.php">Collectibles by Category</a></li>
		<li><a href="/viewSkills.php">Skill Browser</a></li>
		<li><a href="/viewCps.php">Champion Point Browser</a></li>
		<li><a href="/viewSkillCoef.php">Skill Coefficients</a></li>
		<li><a href="//www.uesp.net/wiki/Special:EsoBuildEditor">Build Editor</a></li>
		<li><a href="/viewPotions.php">Alchemy Calculator</a></li>
		<li><a href="/goldenVendor.php">Golden Vendor Items</a></li>
		<li><a href="/goldenVendor.php?vendor=luxury">Luxury Vendor Items</a></li>
		<li><a href="//esosales.uesp.net/viewSales.php">Sales Data</a></li>
		<li><a href="/viewAchievements.php">Achievement Viewer</a></li>
		<li><a href="/viewFurnishings.php">Furnishing Recipe List (CSV)</a></li>
		<li><a href="/setReference.php">Set Reference</a> &nbsp; <a href="/setReference.php?showimage=0">(without images)</a></li>
		<li><a href="/skillReference.php">Skill Reference</a></li>
		<li><a href="//esoapi.uesp.net">API Information</a></li>
		<li><a href="//esofiles.uesp.net/">ESO Raw File Download</a></li>
		<li><a href="https://github.com/esoui/esoui">ESO UI Source Code on GitHub</a></li>
		<li><a href="/submit.php">Submit Logs</a></li>
	</ul>
<?php	
		$this->OutputSearchForm();
		return true;
	}
	
	
	public function OutputSearchForm()
	{
		$options = '';
		
		foreach(self::$SEARCH_TYPE_OPTIONS as $key => $value)
		{
			$selected = '';
			if ($value == $this->searchType) $selected = 'selected';
			$options .= "\t\t<option value='$value' $selected>$key</option>\n";
		}
		
		$safeSearch = $this->EscapeStringHtml($this->rawSearch);
		
?>
		<div id='elvSearchForm'>
			<form method='get' action=''>
				<input type='search' name='search' value="<?=$safeSearch?>" maxlength='64' size='32' />
				<input type='submit' value='Search...' />
				<br/>
				<div id='elvSearchType'>
					Search
					<select name='searchtype'>
						<?=$options ?>
					</select>
				</div>
				<small class="elvSearchAdvanced"><a href="/itemSearch.php">Advanced Item Search</a></small>
			</form>
		</div>
<?php
	}
	
	
	public function OutputTopMenu ($recordInfo = null)
	{
		if (!$this->IsOutputHTML()) return true;
		
		$output = "<a href='viewlog.php'>Back to Home</a><br />\n";
		
		print($output);
		
		$this->OutputSearchForm();
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
		
		if (array_key_exists('message', $recordInfo))
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
			$alias = $value['tableAlias'];
			
			if ($alias != null)
				$query .= "LEFT JOIN $table2 AS $alias on $table1.$tableId1 = $alias.$tableId2 ";
			else
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
			$tableName = $value['table'];
			$aliasName = $value['tableAlias'];
			if ($aliasName != null) $tableName = $aliasName;
			
			if ($value['fields'] == '')
			{
				$tables .= $tableName . ".*";
			}
			else
			{
				$isFirst = true;
				
				foreach ($value['fields'] as $fieldAlias => $fieldName)
				{
					if (!$isFirst) $tables .= ",";
					
					$tables .= " {$tableName}.$fieldName";
					if (gettype($fieldAlias) == "string") $tables .= " as $fieldAlias";
					
					$isFirst = false;
				}
			}
		}
		
		return $tables;
	}
	
	
	public function IsValidSortField($recordInfo, $field)
	{
		if ($field == '') return true;
		
		$badSort = $recordInfo['badSort'];
		if ($badSort && $badSort[$field]) return false;
		
		if (!array_key_exists('validsortfields', $recordInfo)) return true;
		if (in_array($field, $recordInfo['validsortfields'])) return true;
		return false;
	}
	
	
	public function GetSelectQuerySort ($recordInfo)
	{
		$sort = '';
		$customSort = $this->recordSort;
		$badSort = $recordInfo['badSort'];
		
		if (!$this->IsValidSortField($recordInfo, $customSort)) $customSort = '';
		if ($customSort == '' && $recordInfo['sort'] == '') return '';
		
		$sortFields = $recordInfo['sort'];
		if ($customSort != '') $sortFields = $customSort;
		
		if (array_key_exists('sortTranslate', $recordInfo) && array_key_exists($sortFields, $recordInfo['sortTranslate'])) $sortFields = $recordInfo['sortTranslate'][$sortFields];
		
		if (is_array($sortFields)) $sortFields = implode(",", $sortFields);
		
		$sort = " ORDER BY {$sortFields} ";
		
		if ($this->recordSortOrder != '')
			$sort .= $this->recordSortOrder . ' ';
		elseif (array_key_exists('sortOrder', $recordInfo) && $recordInfo['sortOrder'] != '')
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
				$filter = " WHERE $table.$field='$id' ";
				break;
		}
		
		return $filter;
	}
	
	
	public function CreateFilterLink ($record, $filter, $id, $link)
	{	
		if ($id == '' || (is_int($id) && $id <= 0)) return "";
		
		$id = urlencode($id);
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
				$output .= $this->GetViewRecordLink($value['record'], $value['field'], $recordData[$value['thisField']], $value['displayName']);
			elseif ($value['type'] == 'external')
				$output .= $this->GetViewExternalRecordLink($value['url'], $value['fields'], $recordData, $value['displayName']);
			else
				$output .= $this->CreateFilterLink($value['record'], $value['field'], $recordData[$value['thisField']], $value['displayName']);
		}
		
		return $output;
	}
	
	
	public function GetViewExternalRecordLink($url, $fields, $recordData, $displayName)
	{
		$queries = array();
		
		foreach ($fields as $source => $dest)
		{
			$value = $recordData[$source];
			if ($value == null) continue;
			
			$value = urlencode($value);
			$queries[] = "$dest=$value";
		}
		
		$query = implode("&", $queries);
		
		$output = "<a class='elvExternalLink' href='$url?$query'>$displayName</a>";
		return $output;
	}
	
	
	public function CreateSelectQuery ($recordInfo)
	{
		$tables = $this->GetTablesForSelectQuery($recordInfo);
		$table = $recordInfo['table'];
		
		$query = "SELECT $tables FROM $table ";
		$query .= $this->GetSelectQueryJoins($recordInfo);
		$query .= $this->GetSelectQueryFilter($recordInfo);
		$query .= $this->GetSelectQuerySort($recordInfo);
		
		$query .= " LIMIT $this->displayLimit OFFSET $this->displayStart ";
		$query .= ";";
		
		$this->lastQuery = $query;
		return $query;
	}
	
	
	public function GetSelectQueryRecordCount($recordInfo)
	{
		$query = $this->CreateSelectQueryRecordCount($recordInfo);
		
		$result = $this->db->query($query);
		if ($result === false) return 0;
		
		$rowData = $result->fetch_row();
		if ($rowData[0] == null) return 0;
		return $rowData[0];
	}
	
	
	public function CreateSelectQueryRecordCount ($recordInfo)
	{
		$tables = $this->GetTablesForSelectQuery($recordInfo);
		$table = $recordInfo['table'];
	
		$query = "SELECT COUNT(*) FROM $table ";
		$query .= $this->GetSelectQueryJoins($recordInfo);
		$query .= $this->GetSelectQueryFilter($recordInfo);
		$query .= ";";
	
		$this->lastQuery = $query;
		return $query;
	}
	
	
	public function CreateSelectQueryID ($recordInfo, $id)
	{
		$tables = $this->GetTablesForSelectQuery($recordInfo);
		$table = $recordInfo['table'];
		
		$idField = 'id';
		if ($recordInfo['idField']) $idField = $recordInfo['idField'];
		
		$query = "SELECT $tables FROM $table ";
		$query .= $this->GetSelectQueryJoins($recordInfo);
		$query .= " WHERE $table.$idField='$id'";
		$query .= " LIMIT 1 ";
		$query .= ";";
		
		$this->lastQuery = $query;
		return $query;
	}
	
	
	public function CreateSelectQueryIDRecordCount ($recordInfo, $id)
	{
		$tables = $this->GetTablesForSelectQuery($recordInfo);
		$table = $recordInfo['table'];
		
		$idField = 'id';
		if ($recordInfo['idField']) $idField = $recordInfo['idField'];
		
		$query = "SELECT COUNT(*) FROM $table ";
		$query .= $this->GetSelectQueryJoins($recordInfo);
		$query .= " WHERE $table.$idField='$id'";
		$query .= ";";
		
		$this->lastQuery = $query;
		return $query;
	}
	
	
	public function GetRecordFieldHeader ($recordInfo)
	{
		if ($this->IsOutputHTML())
		{
			$output  = "\t<thead><tr>\n";
			$output .= "\t\t<th class='sorter-false'></th>\n";
		}
		
		foreach ($recordInfo['fields'] as $key => $value)
		{
			$colName = $key;
			
			if (array_key_exists('columnNames', $recordInfo) && array_key_exists($colName, $recordInfo['columnNames'])) $colName = $recordInfo['columnNames'][$colName];
			
			if ($this->IsOutputHTML())
			{
				if (!$this->IsValidSortField($recordInfo, $key))
					$sortLink = $key;
				else
					$sortLink = $this->GetSortRecordLink($key, $colName);
				
				$output .= "\t\t<th>$sortLink</th>\n";
			}
			elseif ($this->IsOutputCSV())
			{
				$output .= "\"$key\",";
			}
		}
		
		if ($this->IsOutputHTML())
		{
			$output .= "\t\t<th class='sorter-false'></th>\n";
			$output .= "\t</tr></thead>\n";
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
			case self::FIELD_TEXTTRANSFORM:
			case self::FIELD_STRING:
			case self::FIELD_COLORBOX:
			case self::FIELD_LARGESTRING:
				$output = $this->EscapeStringHtml($value);
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
				if ((int) $value != 0) $output = $value;
				break;
			case self::FIELD_INTYESBLANK:
				if ($this->displayRawValues) return $value;
				$intValue = (int)$value;
				
				if ($intValue === 0)
					$output = "";
				else
					$output = "Yes";
				
				break;
			case self::FIELD_INTBOOLEAN:
				if ($this->displayRawValues) return $value;
				$intValue = (int)$value;
				
				if ($intValue === 0)
					$output = "false";
				elseif ($intValue > 0)
					$output = "true";
				
				break;
			case self::FIELD_GAMEICON:
				//$output =  $this->EscapeStringHtml($value);
				$url = self::GAME_ICON_URL . preg_replace("/\.dds/", ".png", $value);
				//TODO: Need a url/uri escaping here and elsewhere that gameicon type is output to HTML
				$output = "<a href='$url'><img src='$url' title='$value'/ width='32'></a>";
				break;
		}
		
		return $output;
	}
	
	
	public function EscapeStringHtml($value)
	{
		$value = str_replace("…", "&hellip;", $value);
		$value = str_replace("—", "&mdash;", $value);
		
		$value = htmlentities($value, ENT_COMPAT | ENT_HTML401 | ENT_SUBSTITUTE);
		//$value = htmlspecialchars($value, ENT_COMPAT | ENT_HTML401 | ENT_SUBSTITUTE);
		
		$value = str_replace("&amp;mdash;", "&mdash;", $value);
		$value = str_replace("&amp;hellip;", "&hellip;", $value);
		
		return $value;
	}
	
	
	public function EscapeStringCSV($value)
	{
		$newValue = str_replace("—", "-", $value);
		$newValue = str_replace("\\", "\\\\", $value);
		$newValue = str_replace("\"", "\\\"", $value);
		return $newValue;
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
			case self::FIELD_TEXTTRANSFORM:
			case self::FIELD_STRING:
				$escapeValue = $this->EscapeStringCSV($value);
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
				if ((int) $value != 0) $output = $value;
				break;
			case self::FIELD_INTYESBLANK:
				if ($this->displayRawValues) return $value;
				$intValue = (int)$value;
				
				if ($intValue === 0)
					$output = "\"\"";
				else
					$output = "\"true\"";
				
				break;
			case self::FIELD_INTBOOLEAN:
				if ($this->displayRawValues) return $value;
				$intValue = (int)$value;
				
				if ($intValue === 0)
					$output = "\"false\"";
				elseif ($intValue > 0)
					$output = "\"true\"";
				
				break;
			case self::FIELD_GAMEICON:
				$output = $value;
				break;
			case self::FIELD_COLORBOX:
				$output = $value;
				break;
		}
	
		return $output;
	}
	
	
	public function FormatField ($value, $type, $recordType, $field, $id, $recordInfo, $itemData)
	{
		if ($this->IsOutputCSV()) return $this->FormatFieldCSV($value, $type, $recordType, $field, $id, $recordInfo, $itemData);
		
		$output = "";
		if ($value == null) return "";
		
		switch ($type)
		{
			case self::FIELD_INT:
				$output = $value;
				break;	
			default:
			case self::FIELD_STRING:
				$output = $this->EscapeStringHtml($value);
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
				if ((int) $value != 0) $output = $value;
				break;
			case self::FIELD_INTTRANSFORM:
			case self::FIELD_TEXTTRANSFORM:
				$output = $this->TransformRecordValue($recordInfo, $field, $value, $itemData);
				break;
			case self::FIELD_INTYESBLANK:
				if ($this->displayRawValues) return $value;
				$intValue = (int)$value;
				
				if ($intValue === 0)
					$output = "";
				elseif ($intValue > 0)
					$output = "Yes";
				
				break;
			case self::FIELD_INTBOOLEAN:
				if ($this->displayRawValues) return $value;
				$intValue = (int)$value;
				
				if ($intValue === 0)
					$output = "false";
				elseif ($intValue > 0)
					$output = "true";
				
				break;
			case self::FIELD_GAMEICON:
				$url = self::GAME_ICON_URL . preg_replace("/\.dds/", ".png", $value);
				$output = "<a href='$url'><img src='$url' title='$value'/></a>";
				break;
			case self::FIELD_COLORBOX:
				$displayValue = strtoupper($value);
				$output = "#$displayValue <div style='background-color: #$value;' class='elvColorBox'></div>";
				break;
		}
		
		return $output;
	}
	
	
	public function FormatFieldCSV ($value, $type, $recordType, $field, $id, $recordInfo, $itemData)
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
				$escapeValue = $this->EscapeStringCSV($value);
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
				if ((int) $value != 0) $output = $value;
				break;
			case self::FIELD_INTTRANSFORM:
			case self::FIELD_TEXTTRANSFORM:
				$output = "\"" . $this->TransformRecordValue($recordInfo, $field, $value, $itemData) . "\"";
				break;
			case self::FIELD_INTYESBLANK:
				if ($this->displayRawValues) return $value;
				$intValue = (int)$value;
				
				if ($intValue === 0)
					$output = "\"\"";
				elseif ($intValue > 0)
					$output = "\"Yes\"";
				
				break;
			case self::FIELD_INTBOOLEAN:
				if ($this->displayRawValues) return $value;
				$intValue = (int)$value;
				
				if ($intValue === 0)
					$output = "\"false\"";
				elseif ($intValue > 0)
					$output = "\"true\"";
				
				break;
			case self::FIELD_GAMEICON:
				$output = $value;
				break;
			case self::FIELD_COLORBOX:
				$output = $value;
				break;
		}
		
		return $output;
	}
	
	
	public function FormatFieldAll ($value, $type, $recordType, $field, $id, $recordInfo, $itemData)
	{
		if ($this->IsOutputCSV()) return $this->FormatFieldAllCSV($value, $type, $recordType, $field, $id, $recordInfo, $itemData);
		
		$output = "";
		if ($value == null) return "";
		
		switch ($type)
		{
			case self::FIELD_LARGESTRING:
				$safeValue =  $this->EscapeStringHtml($value);
				$output = "<div class='elvLargeStringView'>$safeValue</div>";
				return $output;
		}
		
		return $this->FormatField($value, $type, $recordType, $field, $id, $recordInfo, $itemData);
	}
	
	
	public function FormatFieldAllCSV ($value, $type, $recordType, $field, $id, $recordInfo, $itemData)
	{
		return $this->FormatFieldCSV($value, $type, $recordType, $field, $id, $recordInfo, $itemData);
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
	
	
	public function GetViewRecordLink ($record, $targetId, $id, $link)
	{
		if ($id == '' || $id == 0) return "";
		
		$link = "<a class='elvRecordLink' href='?action=view&record=$record&$targetId=$id'>$link</a>";
		
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
		
		$output = "<a href='?sort=$sortField&sortorder=$sortOrder&$oldQuery'>$link</a>";
		return $output;
	}
	
	
	public function PrintRecords ($recordInfo)
	{
		if (!$this->InitDatabase()) return false;
		
		$this->totalRowCount = $this->GetSelectQueryRecordCount($recordInfo);
		
		$query = $this->CreateSelectQuery($recordInfo);
		if ($query === false) return $this->reportError("Failed to create record query!");
		
		$result = $this->db->query($query);
		if ($result === false) return $this->reportError("Failed to retrieve record data!");
		
		$displayCount = $result->num_rows;
		$startIndex = $this->displayStart + 1;
		$endIndex = $this->displayStart + $this->displayLimit;
		if ($endIndex > $this->totalRowCount) $endIndex = $this->totalRowCount;
		
		$output = "";
		//$output = "$query</br>";
		
		if ($this->IsOutputHTML())
		{
			$output .= "Displaying $displayCount of $this->totalRowCount records from $startIndex to $endIndex.\n";
			$output .= "<br />" . $this->GetNextPrevLink($recordInfo);
			$output .= "<table id='esologtable' border='1' cellspacing='0' cellpadding='2' class=''>\n";
		}
		
		$output .= $this->GetRecordFieldHeader($recordInfo);
		if ($this->IsOutputHTML()) $output .= "<tbody>\n";
		
		$result->data_seek(0);
		
		while ( ($row = $result->fetch_assoc()) )
		{
			$idField = 'id';
			if ($recordInfo['idField']) $idField = $recordInfo['idField'];
			$id = $row[$idField];
			
			if ($this->IsOutputHTML())
			{
				$output .= "\t<tr>\n";
				$output .= "\t\t<td>". $this->GetViewRecordLink($recordInfo['record'], 'id', $id, "View") ."</td>\n";
			}
			
			foreach ($recordInfo['fields'] as $key => $value)
			{
				$fmtValue = $this->FormatField($row[$key], $value, $recordInfo['record'], $key, $id, $recordInfo, $row);
				
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
			$output .= "</tbody>\n";
			$output .= "</table>\n";
			$output .= $this->GetNextPrevLink($recordInfo);
		}
		
		print($output);
	}
	
	
	public function DoRecordDisplay ($recordInfo)
	{
		if (!CanViewEsoLogTable($recordInfo['table'])) return $this->ReportError("Permission denied!");
		
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
		if (!CanViewEsoLogTable($recordInfo['table'])) return $this->ReportError("Permission denied!");
		
		if ($this->recordField != '') return $this->DoViewRecordField($recordInfo);
		
		$this->OutputTopMenu($recordInfo);
		$displayName = $recordInfo['displayNameSingle'];
		$id = $this->recordID;
		
		$output = "";
		$output .= "<h1>ESO: Viewing $displayName: ID#$id</h1>\n";
		
		if (!$this->InitDatabase()) return false;
		if ($this->recordID == 0) return $this->ReportError("Invalid record ID received!");
		
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
			$idField = 'id';
			if ($recordInfo['idField']) $idField = $recordInfo['idField'];
			$id = $row[$idField];
			
			$rowValue = $this->FormatFieldAll($row[$key], $value, $recordInfo['record'], $key, $id, $recordInfo, $row);
			
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
	
	
	public function SearchTableExact ($table, $searchData)
	{
		$safeSearch = $this->db->real_escape_string(trim($this->rawSearch));
		if ($safeSearch == "") return false;
		
		$limitCount = $this->displayLimit;
		$likeString = " LIKE '%$safeSearch%' ";
		$searchFields = $searchData['searchFields']; 
				
		foreach ($searchFields as &$field)
		{
			$field .= $likeString;
		}
		
		$whereQuery = implode(' OR ', $searchFields);
		$query = "SELECT COUNT(*) FROM $table WHERE $whereQuery LIMIT $limitCount;";
		$this->lastQuery = $query;
	
		$result = $this->db->query($query);
		if ($result === false) return $this->ReportError("Failed to perform exact search on $table table!");
	
		$rowData = $result->fetch_row();
		$this->searchTotalCount += $rowData[0];
	
		$query = "SELECT * FROM $table WHERE $whereQuery LIMIT $limitCount;";
		$this->lastQuery = $query;
	
		$result = $this->db->query($query);
		if ($result === false) return $this->ReportError("Failed to perform search on $table table!");
	
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
	
	
	public function SearchTable ($table, $searchData)
	{
		$matches = array();
		preg_match_all('#([A-Za-z0-9_]+)([[:punct:]]+[A-Za-z0-9_]+)?\s*#', trim($this->rawSearch), $matches);
		$this->searchWords = array();
		
		foreach ($matches[1] as $word)
		{
			if (count($word) > 2) $this->searchWords[] = $word;
		}
		
		if (count($this->searchWords) == 0) return false;
		$this->searchWordWildcard = implode('*', $this->searchWords) . '*';
		$searchTerms = $this->searchWordWildcard;
		
		$limitCount = $this->displayLimit;
		$searchFields = implode(', ',$searchData['searchFields']);
		
		$query = "SELECT COUNT(*) FROM $table WHERE MATCH($searchFields) AGAINST ('$searchTerms' in BOOLEAN MODE) LIMIT $limitCount;";
		$this->lastQuery = $query;
		
		$result = $this->db->query($query);
		if ($result === false) return $this->ReportError("Failed to perform search on $table table!");
		
		$rowData = $result->fetch_row();
		$this->searchTotalCount += $rowData[0];
		
		$query = "SELECT * FROM $table WHERE MATCH($searchFields) AGAINST ('$searchTerms' in BOOLEAN MODE) LIMIT $limitCount;";
		$this->lastQuery = $query;
		
		$result = $this->db->query($query);
		if ($result === false) return $this->ReportError("Failed to perform search on $table table!");
		
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
		
		$searchData = self::$SEARCH_DATA[$result['type']];
		
		if ($searchData && $searchData['view'])
		{
			$url = $searchData['view']['url'];
			$parameter = $searchData['view']['parameter'];
			$column = urlencode($result[$searchData['view']['column']]);
			$title = $searchData['view']['title'];
			if ($title == null) $title = "View";
			
			$output = "<a href='$url?$parameter=$column'>$title</a>";
			return $output;
		}
		
		switch ($result['type'])
		{
			case 'book':
				$output .= $this->GetViewRecordLink('book', 'id', $result['id'], 'View Book');
				break;
			case 'quest':
				$output .= $this->GetViewRecordLink('quest', 'id', $result['id'], 'View Quest');
				break;
			case 'uniqueQuest':
				$output .= $this->GetViewRecordLink('uniqueQuest', 'id', $result['id'], 'View Unique Quest');
				break;
			case 'oldQuest':
				$output .= $this->GetViewRecordLink('oldQuest', 'id', $result['id'], 'View Quest');
				break;
			case 'questStage':
				$output .= $this->GetViewRecordLink('quest', 'id', $result['questId'], 'View Quest') . " ";
				$output .= $this->GetViewRecordLink('questStage','id', $result['id'], 'View Quest Stage');
				break;
			case 'oldQuestStage':
				$output .= $this->GetViewRecordLink('oldQuest', 'id', $result['questId'], 'View Quest') . " ";
				$output .= $this->GetViewRecordLink('oldQuestStage','id', $result['id'], 'View Quest Stage');
				break;
			case 'questItem':
			case 'questitem':
				$output .= $this->GetViewRecordLink('questItem', 'id', $result['id'], 'View Quest Item') . " ";
				break;
			case 'questReward':
				$output .= $this->GetViewRecordLink('questReward', 'id', $result['id'], 'View Quest Reward') . " ";
				break;
			case 'item':
				$output .= $this->GetViewRecordLink('item', 'id', $result['id'], 'View Item');
				break;
			case 'npc':
				$output .= $this->GetViewRecordLink('npc', 'id', $result['id'], 'View NPC');
				break;
			case 'recipe':
				$output .= $this->GetViewRecordLink('recipe', 'id', $result['id'], 'View Recipe');
				break;
			case 'ingredient':
				$output .= $this->GetViewRecordLink('ingredient', 'id', $result['id'], 'View Ingredient');
				break;
			case 'minedItem':
				$output .= $this->GetViewRecordLink('minedItem', 'id', $result['id'], 'View Item');
				break;
			case 'minedItemSummary':
				$output .= $this->GetViewRecordLink('minedItemSummary', 'id', $result['id'], 'View Item');
				break;
			case 'minedSkills':
				$output .= $this->GetViewRecordLink('minedSkills', 'id', $result['id'], 'View Skill');
				break;
			case 'minedSkillLines':
				$output .= $this->GetViewRecordLink('minedSkillLines', 'id', $result['id'], 'View Skill Line');
				break;
			case 'skillTree':
				$output .= $this->GetViewRecordLink('skillTree', 'id', $result['id'], 'View Skill Tree');
				break;
			default:
				//$output .= $this->GetViewRecordLink($result['type'], 'id', $result['id'], 'View ' . ucwords($result['type']));
				$output .= $this->GetViewRecordLink($result['type'], 'id', $result['id'], 'View');
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
			$safeSearch = $this->EscapeStringHtml($this->search);
			
			$output  = "<h1>Search Results</h1>";
			$output .= "Note: Only basic display of search results is currently supported (no paging or sorting).<p />";
			$output .= "Displaying {$searchCount} of {$totalCount} records matching \"$safeSearch\"<p />";
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
			$viewLink = $this->CreateSearchViewLink($result);
			
			if ($this->IsOutputHTML())
			{
				$output .= "<tr class='esologSearchRow'>\n";
				$output .= "\t<td>$viewLink</td>\n";
			}
			
			foreach (self::$SEARCH_FIELDS as $key => $value)
			{
				$fmtValue = "";
				if (array_key_exists($key, $result)) $fmtValue = $this->SimpleFormatField($result[$key], $value);
				
				if ($this->IsOutputHTML())
					$output .= "\t<td>$fmtValue</td>\n";
				elseif ($this->IsOutputCSV())
					$output .= "$fmtValue,";
			}
			
			if ($this->IsOutputHTML())
			{
				$output .= "\t<td></td>\n";
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
	
	
	public function IsTableSearchType($table)
	{
		if ($this->searchType == '') return true;
		if ($this->searchType == $table) return true;
		
		if (!array_key_exists($this->searchType, self::$SEARCH_TYPE_EXTRAS)) return false;
		$extraTypes = self::$SEARCH_TYPE_EXTRAS[$this->searchType];
		if ($extraTypes != null && in_array($table, $extraTypes)) return true;
		
		return false;
	}
	
	
	public function DoSearch()
	{
		$this->OutputTopMenu();
		
		$this->searchTotalCount = 0;
		$this->searchResults = array();
		$this->searchTerms = explode(" ", $this->search);
		
			/* Exact searches */
		foreach (self::$SEARCH_DATA as $table => $searchData)
		{
			if ($this->IsTableSearchType($table))
			{
				$this->SearchTableExact($table, $searchData);
			}
		}
		
			/* Partial searches */
		foreach (self::$SEARCH_DATA as $table => $searchData)
		{
			if ($this->IsTableSearchType($table))
			{
				$this->SearchTable($table, $searchData);
			}
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
		
		if (array_key_exists('search', $this->inputParams)) 
		{
			$this->rawSearch = $this->inputParams['search'];
			$this->search = $this->db->real_escape_string($this->inputParams['search']);
		}
		if (array_key_exists('searchtype', $this->inputParams)) $this->searchType = $this->db->real_escape_string($this->inputParams['searchtype']);
		if (array_key_exists('format', $this->inputParams)) $this->outputFormat = strtoupper($this->db->real_escape_string($this->inputParams['format']));
		if (array_key_exists('output', $this->inputParams)) $this->outputFormat = strtoupper($this->db->real_escape_string($this->inputParams['output']));
		if (array_key_exists('field', $this->inputParams)) $this->recordField = $this->db->real_escape_string($this->inputParams['field']);
		if (array_key_exists('id', $this->inputParams)) $this->recordID = $this->db->real_escape_string($this->inputParams['id']);
		if (array_key_exists('action', $this->inputParams)) $this->action = $this->db->real_escape_string($this->inputParams['action']);
		if (array_key_exists('start', $this->inputParams)) $this->displayStart = (int) $this->inputParams['start'];
		if (array_key_exists('filter', $this->inputParams)) $this->recordFilter = $this->db->real_escape_string($this->inputParams['filter']);
		if (array_key_exists('filterid', $this->inputParams)) $this->recordFilterId = $this->db->real_escape_string($this->inputParams['filterid']);
		
		if (array_key_exists('sort', $this->inputParams)) 
		{
			$result = preg_match("|^([a-zA-Z0-9_]+)|s", trim($this->inputParams['sort']), $matches);
			if ($result) $this->recordSort = $matches[1];
		}
		
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
			$this->inputParams['sortorder'] = strtolower($this->inputParams['sortorder']);
			
			switch ($this->inputParams['sortorder'])
			{
				default:
				case 'a':
				case 'asc':
					$this->recordSortOrder = 'ASC';
					break;
				case 'd':
				case 'desc':
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
		ob_start("ob_gzhandler");
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


function CompareRecordTypeByDisplayName($a, $b)
{
	return strcmp($a['displayName'], $b['displayName']);
}

$g_EsoLogViewer = new EsoLogViewer();
$g_EsoLogViewer->Start();

