<?php

/*
 * itemLink.php -- by Dave Humphrey (dave@uesp.net), December 2014
 * 
 * Outputs a HTML page containing an ESO item and its data in the same/similar format
 * as the in-game item tooltips.
 * 
 * TODO:
 *		- Items with no level/quality data
 *		- Items with same name but different itemIds
 *		- Output extra data:
 *			- style
 *			- craft related
 *			- glyph related
 *			- flags
 *		- Search
 *		- Browse items
 *		- IE fix
 *		- Better error page
 *		- Page Types:
 *			- Normal
 *			- Short/plain link
 *			- Error
 *		- Better web font
 *		- Armor/weapon models/textures/pictures?
 *		- Handle "missing" levels/qualities
 *			- Items with no level data (fixed level like item #5216)?
 *		- Combine identical enchantments
 *		- Dynamic updates of enchantment data?
 *		- Controls for enchantment level/quality
 *		- Check "comment" data mismatches
 *		- Move icons to files1
 *		- Long item names (26721, 26658, 57584=46 chars)
 *		- Remove temporary web fonts (wait for new fonts to be used for a while to prevent caching issues)
 * 
 */

// Database users, passwords and other secrets
require("/home/uesp/secrets/esolog.secrets");
require("esoCommon.php");


function compareRawDataSortedIndex($a, $b)
{
	$a0 = (int) $a[0];
	$b0 = (int) $b[0];
	
	if ($a0 == $b0)
	{
		$a1 = (int) $a[1];
		$b1 = (int) $b[1];
		if ($a1 == $b1) return 0;
		return ($a1 < $b1) ? -1 : 1;
	}

	return ($a0 < $b0) ? -1 : 1;
}


class CEsoItemLink
{
	const ESOIL_HTML_TEMPLATE = "templates/esoitemlink_template.txt";
	const ESOIL_RAWDATA_HTML_TEMPLATE = "templates/esoitemlink_rawdata_template.txt";
	const ESOIL_HTML_EMBED_TEMPLATE = "templates/esoitemlink_embed_template.txt";
	const ESOIL_ICON_PATH = "/home/uesp/www/eso/gameicons/";
	const ESOIL_ICON_URL = "http://esoicons.uesp.net/";
	const ESOIL_ICON_UNKNOWN = "unknown.png";
	
	const ESOIL_POTION_MAGICITEMID = 1234567;
	const ESOIL_ENCHANT_ITEMID = 23662;	
	
		// Weird results based on level when enabled (depends on level of character not item)
	const ESOIL_USEPRECENT_CRITICALVALUE = false;
	
	static public $ESOIL_ERROR_ITEM_DATA = array(
			"name" => "Unknown",
			"itemId" => 0,
			"internalSubtype" => 0,
			"internalLevel" => 0,
			"quality" => 0,
			"level" => "?",
			"value" => "?",
			"type" => 0,
			"bind" => 0,
			"description" => "Unknown item!",
			"icon" => "/esoui/art/icons/icon_missing.dds",
			"style" => -1,
	);
	
	static public $ESOIL_ERROR_QUESTITEM_DATA = array(
			"name" => "Unknown",
			"questId" => "",
			"itemLink" => "",
			"questName" => "",
			"itemId" => "",
			"header" => "",
			"icon" => "/esoui/art/icons/icon_missing.dds",
			"description" => "Unknown quest item!",
			"stepIndex" => "",
			"conditionIndex" => "",
	);
	
	static public $ESOIL_ERROR_COLLECTIBLEITEM_DATA = array(
			"name" => "Unknown",
			"itemLink" => "",
			"nickname" => "",
			"description" => "Unknown collectible item!",
			"hint" => "",
			"icon" => "/esoui/art/icons/icon_missing.dds",
			"lockedIcon" => "",
			"backgroundIcon" => "",
			"categoryType" => "",
			"zoneIndex" => "",
			"categoryIndex" => "",
			"subCategoryIndex" => "",
			"collectibleIndex" => "",
			"achievementIndex" => "",
			"categoryName" => "",
			"subCategoryName" => "",
			"isUnlocked" => "",
			"isActive" => "",
			"isSlottable" => "",
			"isUsable" => "",
			"isPlaceholder" => "",
			"isHidden" => "",
			"hasAppeareance" => "",
			"visualPriority" => "",
			"helpCategoryIndex" => "",
			"helpIndex" => "",
			"questName" => "",
			"backgroundText" => "",
			"cooldown" => "",
	);
	
	static public $ESOIL_ITEM_SUMMARY_FIELDS = array(
			"level",
			"value",
			"weaponPower",
			"armorRating",
			"traitDesc",
			"enchantDesc",
			"abilityDesc",
			"traitAbilityDesc",
			"setBonusDesc1",
			"setBonusDesc2",
			"setBonusDesc3",
			"setBonusDesc4",
			"setBonusDesc5",
	);
	
	public $inputParams = array();
	public $itemId = 0;
	public $itemLink = "";
	public $itemLevel = -1;		// 1-66
	public $itemQuality = -1;	// 0-5
	public $itemIntLevel = 1;	// 1-50
	public $itemIntType = 1;	// 1-400
	public $itemBound = -1;
	public $itemStyle = -1;
	public $itemCrafted = -1;
	public $itemCharges = -1;
	public $itemPotionData = -1;
	public $itemStolen = -1;
	public $itemSetCount = -1;
	public $showStyle = true;
	public $enchantId1 = 0;
	public $enchantIntLevel1 = 0;
	public $enchantIntType1 = 0;
	public $enchantId2 = 0;
	public $enchantIntLevel2 = 0;
	public $enchantIntType2 = 0;
	public $itemRecord = array();
	public $enchantRecord1 = null;
	public $enchantRecord2 = null;
	public $itemAllData = array();
	public $version = "";
	public $useUpdate10Display = false;
	public $itemSimilarRecords = array();
	public $itemSummary = array();
	public $outputType = "html";
	public $outputRaw = false;
	public $rawDataKeys = array();
	public $rawDataSortedIndexes = array();
	public $showAll = false;
	public $itemErrorDesc = "";
	public $db = null;
	public $htmlTemplate = "";
	public $embedLink = false;
	public $showSummary = false;
	
	public $questItemId = -1;
	public $questItemData = array();
	
	public $collectibleItemId = -1;
	public $collectibleItemData = array();
	
	
	public function __construct ()
	{
		$this->SetInputParams();
		$this->ParseInputParams();
		$this->InitDatabase();
	}
	
	
	public function ReportError($errorMsg)
	{
		//print($errorMsg);
		error_log($errorMsg);
		return false;
	}
	
	
	public function ParseItemLink($itemLink)
	{
		$result = preg_match('/\|H(?P<color>[A-Za-z0-9]*)\:item\:(?P<itemId>[0-9]*)\:(?P<subtype>[0-9]*)\:(?P<level>[0-9]*)\:(?P<enchantId1>[0-9]*)\:(?P<enchantSubtype1>[0-9]*)\:(?P<enchantLevel1>[0-9]*)\:(?P<enchantId2>[0-9]*)\:(?P<enchantSubtype2>[0-9]*)\:(?P<enchantLevel2>[0-9]*)\:(.*?)\:(?P<style>[0-9]*)\:(?P<crafted>[0-9]*)\:(?P<bound>[0-9]*)\:(?P<stolen>[0-9]*)\\:(?P<charges>[0-9]*)\:(?P<potionData>[0-9]*)\|h\[?(?P<name>[a-zA-Z0-9 %_\(\)\'\-]*)(?P<nameCode>.*?)\]?\|h/', $itemLink, $matches);
		if (!$result) return $this->ReportError("Failed to parse item link: $itemLink");
		
		$this->itemId = $matches['itemId'];
		$this->itemIntLevel = $matches['level'];
		$this->itemIntType = $matches['subtype'];
		
		$this->itemStyle = $matches['style'];
		$this->itemBound = $matches['bound'];
		$this->itemCrafted = $matches['crafted'];
		$this->itemCharges = $matches['charges'];
		$this->itemPotionData = $matches['potionData'];
		$this->itemStolen = $matches['stolen'];
		
		$this->enchantId1 = $matches['enchantId1'];
		$this->enchantIntLevel1 = $matches['enchantLevel1'];
		$this->enchantIntType1 = $matches['enchantSubtype1'];
		
		$this->enchantId2 = $matches['enchantId2'];
		$this->enchantIntLevel2 = $matches['enchantLevel2'];
		$this->enchantIntType2 = $matches['enchantSubtype2'];
		
		return true;
	}
	
	
	private function ParseInputParams ()
	{
		if (array_key_exists('itemlink', $this->inputParams)) 
		{ 
			$this->itemLink = urldecode($this->inputParams['itemlink']);
			$this->ParseItemLink($this->itemLink);
		}
		elseif (array_key_exists('link', $this->inputParams))
		{
			$this->itemLink = urldecode($this->inputParams['link']);
			$this->ParseItemLink($this->itemLink);
		}
		
		if (array_key_exists('itemid', $this->inputParams)) $this->itemId = (int) $this->inputParams['itemid'];
		
		if (array_key_exists('level', $this->inputParams)) 
		{
			$level = strtolower($this->inputParams['level']);
			
			if ($level[0] == 'v')
			{
				$this->itemLevel = (int) ltrim($level, 'v') + 50;
			}
			else if ($level[0] == 'c' && $level[1] == 'p')
			{
				$this->itemLevel = floor(((int) substr($level, 2))/10) + 50;
			}			
			else
			{
				$this->itemLevel = (int) $level;
			}
			
			$this->itemQuality = 1;
		}
		
		if (array_key_exists('quality', $this->inputParams))
		{
			$this->itemQuality = (int) $this->inputParams['quality'];
			if ($this->itemLevel < 0) $this->itemLevel = 1;
		}
		
		if (array_key_exists('show', $this->inputParams) || array_key_exists('showall', $this->inputParams)) $this->showAll = true;
		if (array_key_exists('rawdata', $this->inputParams)) $this->outputRaw = true;
		if (array_key_exists('summary', $this->inputParams)) $this->showSummary = true;
		if (array_key_exists('intlevel', $this->inputParams)) $this->itemIntLevel = (int) $this->inputParams['intlevel'];
		if (array_key_exists('inttype', $this->inputParams)) $this->itemIntType = (int) $this->inputParams['inttype'];
		if (array_key_exists('setcount', $this->inputParams)) $this->itemSetCount = (int) $this->inputParams['setcount'];
		if (array_key_exists('potiondata', $this->inputParams)) $this->itemPotionData = (int) $this->inputParams['potiondata'];
		if (array_key_exists('stolen', $this->inputParams)) $this->itemStolen = (int) $this->inputParams['stolen'];
		if (array_key_exists('style', $this->inputParams)) $this->itemStyle = (int) $this->inputParams['style'];
				
		if (array_key_exists('version', $this->inputParams)) $this->version = urldecode($this->inputParams['version']);
		if (array_key_exists('v', $this->inputParams)) $this->version = urldecode($this->inputParams['v']);
		
		if (array_key_exists('embed', $this->inputParams))
		{
			$this->embedLink = true;
			$this->htmlTemplate = file_get_contents(self::ESOIL_HTML_EMBED_TEMPLATE);
		}
		else
		{
			$this->htmlTemplate = file_get_contents(self::ESOIL_HTML_TEMPLATE);
		}
		
		if (array_key_exists('enchantid', $this->inputParams))
		{
			$this->enchantId1 = (int) $this->inputParams['enchantid'];
			$this->enchantIntType1 = 1;
			$this->enchantIntLevel1 = 1;
		}
		
		if (array_key_exists('enchantintlevel', $this->inputParams))
		{
			$this->enchantIntLevel1 = (int) $this->inputParams['enchantintlevel'];
		}
		
		if (array_key_exists('enchantinttype', $this->inputParams))
		{
			$this->enchantIntType1 = (int) $this->inputParams['enchantinttype'];
		}
		
		if (array_key_exists('output', $this->inputParams)) 
		{
			$this->inputParams['output'] = strtolower($this->inputParams['output']);
			
			switch ($this->inputParams['output'])
			{
				case "text":
				case "html":
				case "csv":
					$this->outputType = $this->inputParams['output'];
					break;
			}
		}
		
		if (array_key_exists('qid', $this->inputParams))
		{
			$this->questItemId = (int) $this->inputParams['qid'];
		}
		
		if (array_key_exists('cid', $this->inputParams))
		{
			$this->collectibleItemId = (int) $this->inputParams['cid']; 
		}
				
		$this->useUpdate10Display = IsEsoVersionAtLeast($this->version, 10);
		
		return true;
	}
	
	
	private function SetInputParams ()
	{
		global $argv;
		$this->inputParams = $_REQUEST;
		
			// Add command line arguments to input parameters for testing
		if ($argv !== null)
		{
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
				else
				{
					$this->inputParams[$e[0]] = 1;
				}
			}
		}
	}
	
	
	private function GetTableSuffix()
	{
		return GetEsoItemTableSuffix($this->version);
	}
	
	
	private function InitDatabase()
	{
		global $uespEsoLogReadDBHost, $uespEsoLogReadUser, $uespEsoLogReadPW, $uespEsoLogDatabase;
		
		$this->db = new mysqli($uespEsoLogReadDBHost, $uespEsoLogReadUser, $uespEsoLogReadPW, $uespEsoLogDatabase);
		if ($this->db->connect_error) return $this->ReportError("ERROR: Could not connect to mysql database!");
		
		return true;
	}
	
	
	private function ReduceAllItemData()
	{
		if (count($this->itemAllData) == 0) return;
		
		$firstItem = $this->itemAllData[0];
		
		foreach ($this->itemAllData as $index => &$item)
		{
			if ($firstItem == $item) continue;
			$delItems = array("link");
			
			foreach ($item as $key => $value)
			{
				if (array_key_exists($key, $firstItem) && $firstItem[$key] == $value)
				{
					$delItems[] = $key;
				}
			}
			
			foreach ($delItems as $key => $value)
			{
				unset($item[$value]);
			}
		}
	}
	
	
	private function LoadAllItemData()
	{
		$this->itemAllData = array();
		if ($this->itemId <= 0) return false;
		
		$query = "SELECT * FROM minedItem". $this->GetTableSuffix() ." WHERE itemId={$this->itemId} ORDER BY level, quality;";
		
		$result = $this->db->query($query);
		if (!$result) return false;
		if ($result->num_rows === 0) return false;
		
		$result->data_seek(0);
		
		while (($row = $result->fetch_assoc()))
		{
					// TODO: Temporary fix for setMaxEquipCount
			if (array_key_exists('setMaxEquipCount', $row) && $row['setMaxEquipCount'] == -1)
			{
				$highestSetDesc = "";
					
				if (array_key_exists('setBonusDesc1', $row) && $row['setBonusDesc1'] != "") $highestSetDesc = $row['setBonusDesc1'];
				if (array_key_exists('setBonusDesc2', $row) && $row['setBonusDesc2'] != "") $highestSetDesc = $row['setBonusDesc2'];
				if (array_key_exists('setBonusDesc3', $row) && $row['setBonusDesc3'] != "") $highestSetDesc = $row['setBonusDesc3'];
				if (array_key_exists('setBonusDesc4', $row) && $row['setBonusDesc4'] != "") $highestSetDesc = $row['setBonusDesc4'];
				if (array_key_exists('setBonusDesc5', $row) && $row['setBonusDesc5'] != "") $highestSetDesc = $row['setBonusDesc5'];
					
				if ($highestSetDesc != "")
				{
					$matches = array();
					$matchResult = preg_match("/\(([0-9]+) items\)/", $highestSetDesc, $matches);
					if ($matchResult) $row['setMaxEquipCount'] = (int) $matches[1];
				}
			}
			
			$row['isBound'] =  $this->itemBound < 0 ? 0 : $this->itemBound;
			$row['charges'] =  $this->itemCharges < 0 ? 0 : $this->itemCharges;
			$row['isCrafted'] = $this->itemCrafted < 0 ? 0 : $this->itemCrafted;
			$row['enchantId1'] = $this->enchantId1;
			$row['enchantId2'] = $this->enchantId2;
			$row['enchantIntLevel1'] = $this->enchantIntLevel1;
			$row['enchantIntType1'] = $this->enchantIntType1;
			$row['enchantIntLevel2'] = $this->enchantIntLevel2;
			$row['enchantIntType2'] = $this->enchantIntType2;
			
			if ($this->enchantRecord1 != null)
			{
				$row['enchantName1'] = $this->enchantRecord1['enchantName'];
				$row['enchantDesc1'] = $this->enchantRecord1['enchantDesc'];
			}
			
			if ($this->enchantRecord2 != null)
			{
				$row['enchantName2'] = $this->enchantRecord2['enchantName'];
				$row['enchantDesc2'] = $this->enchantRecord2['enchantDesc'];
			}
			
			if ($this->itemStyle > 0 && $this->itemStyle != $row['style'])
			{
				$row['origStyle'] = $row['style'];
				$row['style'] = $this->itemStyle;
			}
			
			$row['version'] = $this->version;
			
			$this->itemAllData[] = $row;
		}
		
		$this->ReduceAllItemData();
		return true;
	}
	
	
	private function LoadItemErrorData()
	{
		$this->itemRecord = self::$ESOIL_ERROR_ITEM_DATA;
		$this->itemRecord['name'] = "Unknown Item #" . $this->itemId;
		$this->itemRecord['itemId'] = $this->itemId;
		$this->itemRecord['quality'] = $this->itemQuality;
		$this->itemRecord['level'] = $this->itemLevel;
		$this->itemRecord['internalSubtype'] = $this->itemIntType;
		$this->itemRecord['internalLevel'] = $this->itemIntLevel;
		
		if ($this->itemLevel > 0 && $this->itemQuality >= 0)
			$this->itemRecord['description'] = "No item found matching itemId # {$this->itemId}, level {$this->itemLevel}, and quality {$this->itemQuality}!";
		else if ($this->itemIntType >= 0 && $this->itemIntLevel > 0)
			$this->itemRecord['description'] = "No item found matching itemId # {$this->itemId}, internalLevel {$this->itemIntLevel}, and internalSubtype {$this->itemIntType}!";
		else
			$this->itemRecord['description'] = "No item found matching itemId # {$this->itemId}!";
		
	}
	
	
	private function LoadQuestItemErrorData()
	{
		$this->questItemData = self::$ESOIL_ERROR_QUESTITEM_DATA;
		
		$this->questItemData['name'] = "Unknown Quest Item";
		$this->questItemData['itemId'] = $this->questItemId;
		$this->questItemData['description'] = "No quest item found matching ID# {$this->questItemId}!";
	}
	
	
	private function LoadCollectibleItemErrorData()
	{
		$this->collectibleItemData = self::$ESOIL_ERROR_COLLECTIBLEITEM_DATA;
	
		$this->collectibleItemData['name'] = "Unknown Collectible";
		$this->collectibleItemData['id'] = $this->collectibleItemId;
		$this->collectibleItemData['description'] = "No collectible item found matching ID# {$this->collectibleItemId}!";
	}
	
	
	public function MergeItemSummary()
	{
		if ($this->itemSummary == null || count($this->itemSummary) == 0) return false;
		
		foreach (self::$ESOIL_ITEM_SUMMARY_FIELDS as $field)
		{
			$value = $this->itemSummary[$field];
			
			if ($field == "level" && $value == "")
				$this->itemRecord[$field] = '1-V16';
			else
				$this->itemRecord[$field] = $value;
		}
		
		return true;
	}
	
	
	private function LoadItemSummaryData()
	{
		if ($this->itemId <= 0) return $this->ReportError("ERROR: Missing or invalid item ID specified (1-85000)!");
		$query = "SELECT * FROM minedItemSummary". $this->GetTableSuffix() ." WHERE itemId={$this->itemId};";
		
		$result = $this->db->query($query);
		if (!$result) return $this->ReportError("ERROR: Database query error! " . $this->db->error);
		
		$this->itemSummary = $result->fetch_assoc();
		if (!$this->itemSummary) $this->ReportError("ERROR: No item summary found matching ID {$this->itemId}!");
		
		return true;
	}
	
	
	private function LoadItemRecord()
	{
		if ($this->itemId <= 0) return $this->ReportError("ERROR: Missing or invalid item ID specified (1-85000)!");
		$query = "";
		
		if ($this->itemLevel >= 1)
		{
			if ($this->itemLevel <= 0) return $this->ReportError("ERROR: Missing or invalid item Level specified (1-64)!");
			if ($this->itemQuality < 0) return $this->ReportError("ERROR: Missing or invalid item Quality specified (1-5)!");
			$query = "SELECT * FROM minedItem". $this->GetTableSuffix() ." WHERE itemId={$this->itemId} AND level={$this->itemLevel} AND quality={$this->itemQuality} LIMIT 1;";
			$this->itemErrorDesc = "id={$this->itemId}, Level={$this->itemLevel}, Quality={$this->itemQuality}";
		}
		else
		{
			if ($this->itemIntType < 0) return $this->ReportError("ERROR: Missing or invalid item internal type specified (1-400)!");
			$query = "SELECT * FROM minedItem". $this->GetTableSuffix() ." WHERE itemId={$this->itemId} AND internalLevel={$this->itemIntLevel} AND internalSubtype={$this->itemIntType} LIMIT 1;";
			$this->itemErrorDesc = "id={$this->itemId}, Internal Level={$this->itemIntLevel}, Internal Type={$this->itemIntType}";
		}
		
		$result = $this->db->query($query);
		if (!$result) return $this->ReportError("ERROR: Database query error! " . $this->db->error);
		
		if ($result->num_rows === 0)
		{
			if ($this->itemLevel <= 0 && $this->itemIntType == 1)
			{
				$this->itemIntType = 2;
				$query = "SELECT * FROM minedItem". $this->GetTableSuffix() ." WHERE itemId={$this->itemId} AND internalLevel={$this->itemIntLevel} AND internalSubtype={$this->itemIntType} LIMIT 1;";
				$this->itemErrorDesc = "id={$this->itemId}, Internal Level={$this->itemIntLevel}, Internal Type={$this->itemIntType}";
				
				$result = $this->db->query($query);
				if (!$result) return $this->ReportError("ERROR: Database query error! " . $this->db->error);
				
				if ($result->num_rows === 0)
				{
					$this->itemLevel = 50;
					$this->itemIntLevel = 50;
					$this->itemIntType = 370;
					$query = "SELECT * FROM minedItem". $this->GetTableSuffix() ." WHERE itemId={$this->itemId} AND internalLevel={$this->itemIntLevel} AND internalSubtype={$this->itemIntType} LIMIT 1;";
					$this->itemErrorDesc = "id={$this->itemId}, Internal Level={$this->itemIntLevel}, Internal Type={$this->itemIntType}";
					
					$result = $this->db->query($query);
					if (!$result) return $this->ReportError("ERROR: Database query error! " . $this->db->error);
				}
			}
			else if ($this->embedLink)
			{
				$this->itemIntType = 1;
				$this->itemIntLevel = 1;
				
				$query = "SELECT * FROM minedItem". $this->GetTableSuffix() ." WHERE itemId={$this->itemId} AND internalLevel={$this->itemIntLevel} AND internalSubtype={$this->itemIntType} LIMIT 1;";
				$this->itemErrorDesc = "id={$this->itemId}, Internal Level={$this->itemIntLevel}, Internal Type={$this->itemIntType}";
				
				$result = $this->db->query($query);
				if (!$result) return $this->ReportError("ERROR: Database query error! " . $this->db->error);
			}
			
			if ($result->num_rows === 0) return $this->ReportError("ERROR: No item found matching {$this->itemErrorDesc}!");
		}
		
		$result->data_seek(0);
		$row = $result->fetch_assoc();
		if (!$row) $this->ReportError("ERROR: No item found matching {$this->itemErrorDesc}!");
		
		$this->itemLevel = (int) $row['level'];
		$this->itemQuality = (int) $row['quality'];
		
			// TODO: Temporary fix for setMaxEquipCount
		if (array_key_exists('setMaxEquipCount', $row) && $row['setMaxEquipCount'] == -1)
		{
			$highestSetDesc = "";
			$row['setMaxEquipCount'] = 0;
			
			if (array_key_exists('setBonusDesc1', $row) && $row['setBonusDesc1'] != "") $highestSetDesc = $row['setBonusDesc1'];
			if (array_key_exists('setBonusDesc2', $row) && $row['setBonusDesc2'] != "") $highestSetDesc = $row['setBonusDesc2'];
			if (array_key_exists('setBonusDesc3', $row) && $row['setBonusDesc3'] != "") $highestSetDesc = $row['setBonusDesc3'];
			if (array_key_exists('setBonusDesc4', $row) && $row['setBonusDesc4'] != "") $highestSetDesc = $row['setBonusDesc4'];
			if (array_key_exists('setBonusDesc5', $row) && $row['setBonusDesc5'] != "") $highestSetDesc = $row['setBonusDesc5'];
				
			if ($highestSetDesc != "")
			{
				$row['setMaxEquipCount'] = 1;
				$matches = array();
				$matchResult = preg_match("/\(([0-9]+) items\)/", $highestSetDesc, $matches);
				if ($matchResult) $row['setMaxEquipCount'] = (int) $matches[1];
			}
		}
		
		$row['enchantId1'] = $this->enchantId1;
		$row['enchantId2'] = $this->enchantId2;
		$row['enchantIntLevel1'] = $this->enchantIntLevel1;
		$row['enchantIntType1'] = $this->enchantIntType1;
		$row['enchantIntLevel2'] = $this->enchantIntLevel2;
		$row['enchantIntType2'] = $this->enchantIntType2;
		
		$row['traitAbilityDescs'] = array();
		$row['traitCooldowns'] = array();
		
		if ($row['traitAbilityDesc'] != "")
		{
			$row['traitAbilityDescArray'][] = $row['traitAbilityDesc'];
			$row['traitCooldownArray'][] = $row['traitCooldown'];
		}
		
		$this->itemRecord = $row;
		
		$this->LoadItemPotionData();
		$this->LoadEnchantMaxCharges();
		return true;
	}
	
	
	private function LoadQuestItemRecord()
	{
		if ($this->questItemId <= 0) return $this->ReportError("ERROR: Missing or invalid quest item ID specified (1-85000)!");
		$query = "";
	
		$query = "SELECT * FROM questItem". $this->GetTableSuffix() ." WHERE itemId={$this->questItemId} LIMIT 1;";
		$this->itemErrorDesc = "qid={$this->questItemId}";
		$result = $this->db->query($query);
		if (!$result) return $this->ReportError("ERROR: Database query error! " . $this->db->error);
		
		$result->data_seek(0);
		$this->questItemData = $result->fetch_assoc();
		
		if (!$this->questItemData) 
		{
			$this->ReportError("ERROR: No quest item found matching {$this->itemErrorDesc}!");
			$this->questItemData = array();
			return false;
		}
		
		return true;
	}
	
	
	private function LoadCollectibleItemRecord()
	{
		if ($this->collectibleItemId <= 0) return $this->ReportError("ERROR: Missing or invalid collectible item ID specified (1-85000)!");
		$query = "";
	
		$query = "SELECT * FROM collectibles". $this->GetTableSuffix() ." WHERE id={$this->collectibleItemId} LIMIT 1;";
		$this->itemErrorDesc = "cid={$this->collectibleItemId}";
		$result = $this->db->query($query);
		if (!$result) return $this->ReportError("ERROR: Database query error! " . $this->db->error);
	
		$result->data_seek(0);
		$this->collectibleItemData = $result->fetch_assoc();
		
		if (!$this->collectibleItemData) 
		{
			$this->ReportError("ERROR: No collectible item found matching {$this->itemErrorDesc}!");
			$this->collectibleItemData = array();
			return false;
		}
	
		return true;
	}
	
	
	private function LoadItemPotionData()
	{
		if ($this->itemPotionData <= 0) return true;
		
		$potionData = intval($this->itemPotionData);
		$potionEffect1 = $potionData & 255;
		$potionEffect2 = ($potionData >> 8) & 255;
		$potionEffect3 = ($potionData >> 16) & 127;
		
		$this->LoadItemPotionDataEffect($potionEffect1);
		$this->LoadItemPotionDataEffect($potionEffect2);
		$this->LoadItemPotionDataEffect($potionEffect3);
		
		ksort($this->itemRecord['traitAbilityDescArray']);
		ksort($this->itemRecord['traitCooldownArray']);		
		return true;
	}
	
	
	private function LoadItemPotionDataEffect($effectIndex)
	{
		$effectIndex = intval($effectIndex);
		if ($effectIndex <= 0 || $effectIndex > 127) return true;
		
		if ($this->itemLevel >= 1)
		{
			$level = $this->itemLevel;
			$quality = $this->itemQuality;
			$query = "SELECT traitAbilityDesc, traitCooldown FROM minedItem{$this->GetTableSuffix()} WHERE itemId=".self::ESOIL_POTION_MAGICITEMID." AND level=$level AND quality=$quality AND potionData=$effectIndex LIMIT 1;";
		}
		else
		{
			$intlevel = $this->itemIntLevel;
			$subtype = $this->itemIntType;
			$query = "SELECT traitAbilityDesc, traitCooldown FROM minedItem{$this->GetTableSuffix()} WHERE itemId=".self::ESOIL_POTION_MAGICITEMID." AND internalLevel=$intlevel AND internalSubtype=$type AND potionData=$effectIndex LIMIT 1;";
		}
		
		$this->lastQuery = $query;
		$result = $this->db->query($query);
		if (!$result) return $this->ReportError("ERROR: Database query error! " . $this->db->error);
		if ($result->num_rows == 0) return true;
		
		$result->data_seek(0);
		$row = $result->fetch_assoc();
		
		if ($row['traitAbilityDesc'] == "") return true;
		
		$this->itemRecord['traitAbilityDescArray'][$effectIndex] = $row['traitAbilityDesc'];
		$this->itemRecord['traitCooldownArray'][$effectIndex] = $row['traitCooldown'];
		return true;
	}
	
	
	private function LoadEnchantMaxCharges()
	{
		if ($this->itemRecord['maxCharges'] > 0) return true;
		
		if ($this->itemLevel >= 1)
		{
			$level = $this->itemLevel;
			$quality = $this->itemQuality;
			$query = "SELECT maxCharges FROM minedItem{$this->GetTableSuffix()} WHERE itemId=".self::ESOIL_ENCHANT_ITEMID." AND level=$level AND quality=$quality LIMIT 1;";
		}
		else
		{
			$intlevel = $this->itemIntLevel;
			$subtype = $this->itemIntType;
			$query = "SELECT maxCharges FROM minedItem{$this->GetTableSuffix()} WHERE itemId=".self::ESOIL_ENCHANT_ITEMID." AND internalLevel=$intlevel AND internalSubtype=$type LIMIT 1;";
		}
		
		$this->lastQuery = $query;
		$result = $this->db->query($query);
		if (!$result) return $this->ReportError("ERROR: Database query error! " . $this->db->error);
		if ($result->num_rows == 0) return true;
		
		$result->data_seek(0);
		$row = $result->fetch_assoc();
		if ($row['maxCharges'] == "") return true;
		
		$maxCharges = $row['maxCharges'];
		if ($this->itemRecord['trait'] == 2) $maxCharges *= $this->itemRecord['quality']*0.25 + 2;
		
		$this->itemRecord['maxCharges'] = $maxCharges; 
		return true;
	}
	
	
	private function LoadEnchantRecords()
	{
		if ($this->enchantId1 > 0 && $this->enchantIntLevel1 > 0 && $this->enchantIntType1 > 0)
		{
			$query = "SELECT * FROM minedItem". $this->GetTableSuffix() ." WHERE itemId={$this->enchantId1} AND internalLevel={$this->enchantIntLevel1} AND internalSubtype={$this->enchantIntType1} LIMIT 1;";
			$result = $this->db->query($query);
			if (!$result) return $this->ReportError("ERROR: Database query error! " . $this->db->error);
			
			$result->data_seek(0);
			$row = $result->fetch_assoc();
			if ($row) $this->enchantRecord1 = $row;
		}
		
		if ($this->enchantId2 > 0 && $this->enchantIntLevel2 > 0 && $this->enchantIntType2 > 0)
		{
			$query = "SELECT * FROM minedItem". $this->GetTableSuffix() ." WHERE itemId={$this->enchantId2} AND internalLevel={$this->enchantIntLevel2} AND internalSubtype={$this->enchantIntType2} LIMIT 1;";
			$result = $this->db->query($query);
			if (!$result) return $this->ReportError("ERROR: Database query error! " . $this->db->error);
				
			$result->data_seek(0);
			$row = $result->fetch_assoc();
			if ($row) $this->enchantRecord2 = $row;
		}
		
		if ($this->enchantRecord1 != null)
		{
			$this->itemRecord['enchantName1'] = $this->enchantRecord1['enchantName'];
			$this->itemRecord['enchantDesc1'] = $this->enchantRecord1['enchantDesc'];
		}
			
		if ($this->enchantRecord2 != null)
		{
			$this->itemRecord['enchantName2'] = $this->enchantRecord2['enchantName'];
			$this->itemRecord['enchantDesc2'] = $this->enchantRecord2['enchantDesc'];
		}
		
		return true;
	}
	
	
	private function MakeSimilarItemBlock()
	{
		$output = "";
		
		foreach ($this->itemSimilarRecords as $key => $item)
		{
			if ($item['id'] == $this->itemRecord['id']) continue;
			
			$intType = $item['internalSubtype'];
			$intLevel = $item['internalLevel'];
			$itemId = $this->itemId;

			if ($this->enchantId1 > 0)
			{
				$enchantId = $this->enchantId1;
				$enchantLevel = $this->enchantIntLevel1;
				$enchantType = $this->enchantIntType1;
				$output .= "<li><a href='itemLink.php?itemid=$itemId&intlevel=$intLevel&inttype=$intType&enchantid=$enchantId&enchantintlevel=$enchantLevel&enchantinttype=$enchantType'>Internal Type $intLevel:$intType</a></li>";
			}
			else
				$output .= "<li><a href='itemLink.php?itemid=$itemId&intlevel=$intLevel&inttype=$intType'>Internal Type $intLevel:$intType</a></li>";
		}
		
		if ($output == "") $output = "<li>No similar items found.</li>";
		
		return $output;
	}
	
	
	private function LoadSimilarItemRecords()
	{
		$query = "SELECT id, internalLevel, internalSubtype FROM minedItem". $this->GetTableSuffix() ." WHERE itemId={$this->itemId} AND level={$this->itemLevel} AND quality={$this->itemQuality};";
		$result = $this->db->query($query);
		if (!$result) return $this->ReportError("ERROR: Database query error! " . $this->db->error);
		if ($result->num_rows === 0) return true;
		$result->data_seek(0);
		
		while (($row = $result->fetch_assoc()))
		{
			$this->itemSimilarRecords[] = $row;
		}
		
		return false;
	}
	
	
	private function TestAddSetData ($row)
	{ 
		$row['setMaxEquipCount'] = 5;
		$row['setBonusCount'] = 4;
		$row['setBonusCount1'] = 2;
		$row['setBonusCount2'] = 3;
		$row['setBonusCount3'] = 4;
		$row['setBonusCount4'] = 5;
		$row['setBonusDesc1'] = "Adds 139 Armor";
		$row['setBonusDesc2'] = "Adds 104 Max Health";
		$row['setBonusDesc3'] = "Adds 104 Max Health";
		$row['setBonusDesc4'] = "Death's Wind If struck by a melee attack while below 35% health, nearby enemies are knocked back and stunned for 4.0 seconds. This effect can only happen once every 30.0 seconds";
		return $row;
	}
	
	
	private function OutputHtmlHeader()
	{
		ob_start("ob_gzhandler");
		header("Expires: 0");
		header("Pragma: no-cache");
		header("Cache-Control: no-cache, no-store, must-revalidate");
		header("Pragma: no-cache");
		header("Access-Control-Allow-Origin: " . $_SERVER['HTTP_ORIGIN'] . "");
		
		if ($this->outputType == "html")
			header("content-type: text/html");
		elseif ($this->outputType == "csv")
			header("content-type: text/plain");
		else
			header("content-type: text/plain");
	}
	
	
	private function MakeItemRawDataList()
	{	
		$output = "";
		$this->itemRecord['origItemLink'] = $this->itemRecord['link'];
		$this->itemRecord['isBound'] = $this->itemBound < 0 ? 0 : $this->itemBound;
		$this->itemRecord['isCrafted'] = $this->itemCrafted < 0 ? 0 : $this->itemCrafted;
		$this->itemRecord['charges'] = $this->itemCharges < 0 ? 0 : $this->itemCharges;
		$this->itemRecord['version'] = $this->version;
		
		if ($this->itemStyle > 0 && $this->itemStyle != $this->itemRecord['style'])
		{
			$this->itemRecord['origStyle'] = $this->itemRecord['style'];
			$this->itemRecord['style'] = $this->itemStyle;
		}
		
		foreach ($this->itemRecord as $key => $value)
		{
			if (!$this->showAll && ($key == 'id' || $key == 'logId' || $value == "" || $value == '-1')) continue;
			$id = "esoil_rawdata_" . $key;
			
			if ($key == "link") $value = $this->MakeItemLink();
			
			if ($key == "icon")
				$output .= "\t<tr><td>$key</td><td id='$id'><img id='esoil_rawdata_iconimage' src='{$this->MakeItemIconImageLink()}' /> $value</td></tr>\n";
			else
				$output .= "\t<tr><td>$key</td><td id='$id'>$value</td></tr>\n";
			
		}
		
		return $output;
	}
	
	
	private function MakeQuestItemRawDataList()
	{
		$output = "";
		$this->questItemData['version'] = $this->version;
	
		foreach ($this->questItemData as $key => $value)
		{
			if (!$this->showAll && ($key == 'id' || $key == 'logId' || $value == "" || $value == '-1')) continue;
			$id = "esoil_rawdata_" . $key;
			
			if ($key == "icon")
				$output .= "\t<tr><td>$key</td><td id='$id'><img id='esoil_rawdata_iconimage' src='{$this->MakeQuestItemIconImageLink()}' /> $value</td></tr>\n";
			else
				$output .= "\t<tr><td>$key</td><td id='$id'>$value</td></tr>\n";
						
		}
	
		return $output;
	}
	
	
	private function MakeCollectibleItemRawDataList()
	{
		$output = "";
		$this->collectibleItemData['version'] = $this->version;
	
		foreach ($this->collectibleItemData as $key => $value)
		{
			if (!$this->showAll && ($key == 'id' || $key == 'logId' || $value == "" || $value == '-1')) continue;
			$id = "esoil_rawdata_" . $key;
				
			if ($key == "icon")
				$output .= "\t<tr><td>$key</td><td id='$id'><img id='esoil_rawdata_iconimage' src='{$this->MakeCollectibleItemIconImageLink()}' /> $value</td></tr>\n";
			else
				$output .= "\t<tr><td>$key</td><td id='$id'>$value</td></tr>\n";
	
		}
	
		return $output;
	}
	
	
	private function MakeIconImageLink($icon)
	{
		if ($icon == null || $icon == "") $icon = self::ESOIL_ICON_UNKNOWN;
		
		$icon = preg_replace('/dds$/', 'png', $icon);
		$icon = preg_replace('/^\//', '', $icon);
		
		$iconLink = self::ESOIL_ICON_URL . $icon;
		return $iconLink;
	}
	
	
	private function MakeItemIconImageLink()
	{
		$icon = $this->itemRecord['icon'];
		return $this->MakeIconImageLink($icon);
	}
	
	
	private function MakeQuestItemIconImageLink()
	{
		$icon = $this->questItemData['icon'];
		return $this->MakeIconImageLink($icon);
	}
	
	
	private function MakeCollectibleItemIconImageLink()
	{
		$icon = $this->collectibleItemData['icon'];
		return $this->MakeIconImageLink($icon);
	}
	
	
	private function MakeItemLevelSimpleString()
	{		
		$level = $this->itemRecord['level'];
		if ($level <= 0) return "Level ?";
		
		if ($level > 50)
		{
			$level -= 50;
			$cp = $level * 10;
			
			if ($this->useUpdate10Display)
				return "Level 50 CP$cp";
			else
				return "Rank V$level";
		}
		
		return "Level $level";
	}
	
	
	private function ShouldShowLevel()
	{
		$itemType = $this->itemRecord['type'];
		
		if ($itemType == 1) return true;
		if ($itemType == 2) return true;
		if ($itemType == 4) return true;
		if ($itemType == 7) return true;
		if ($itemType == 12) return true;
		if ($itemType == 20) return true;
		if ($itemType == 21) return true;
		if ($itemType == 26) return true;
		if ($itemType == 32) return true;
		
		return false;
	}
	
	
	private function MakeItemLevelString()
	{
		//if (!$this->ShouldShowLevel()) return "";
		
		$level = $this->itemRecord['level'];
		if ($level <= 0) return "";
		
		if ($level > 50) 
		{
			$level -= 50;
			
			if ($this->useUpdate10Display)
				return "LEVEL <div id='esoil_itemlevel'>50</div>";
			else
				return "<img src='http://esoitem.uesp.net/resources/eso_item_veteranicon.png' /> RANK <div id='esoil_itemlevel'>$level</div>";
		}
		
		return "LEVEL <div id='esoil_itemlevel'>$level</div>";
	}
	
	
	private function MakeItemLeftBlock()
	{
		$type = $this->itemRecord['type'];
		$equipType = $this->itemRecord['equipType'];
		
		if ($type == 2) //armor 
		{
			if ($equipType == 2 || $equipType == 12) // ring/neck
				return "";
			else
				return "ARMOR <div id='esoil_itemleft'>{$this->itemRecord['armorRating']}</div>";
		}
		elseif ($type == 1) //weapon / shield 
		{
			if ($equipType == 7) // shield
				return "ARMOR <div id='esoil_itemleft'>{$this->itemRecord['armorRating']}</div>";
			else //weapon
				return "DAMAGE <div id='esoil_itemleft'>{$this->itemRecord['weaponPower']}</div>";
		}
		
		return "";
	}
	
	
	private function MakeItemRightBlock()
	{
		if (!$this->useUpdate10Display) return $this->MakeItemOldValueBlock();

		$level = $this->itemRecord['level'];
		
		if (!is_numeric($level)) 
		{
			if ($level == "CP160") return "<img src='http://esoitem.uesp.net/resources/champion_icon.png' class='esoil_cpimg'>CP<div id='esoil_itemlevel'>160</div>";
			if ($level == "1-CP160") return "<div id='esoil_itemlevel'>1 -</div> <img src='http://esoitem.uesp.net/resources/champion_icon.png' class='esoil_cpimg'>CP<div id='esoil_itemlevel'>160</div>";
			return "<div id='esoil_itemlevel'>$level</div>";
		}
		
		if ($level <= 50) return "";
		
		$cp = ($level - 50) * 10;
		$output = "<img src='http://esoitem.uesp.net/resources/champion_icon.png' class='esoil_cpimg'>CP<div id='esoil_itemlevel'>$cp</div>";
		
		return $output;
	}
	
	
	private function MakeItemOldValueBlock()
	{
		$value = $this->itemRecord['value'];
		$output = "VALUE <div id='esoil_itemoldvalue'>$value</div>";
		return $output;
	}
	
	
	private function MakeItemNewValueBlock()
	{
		$value = $this->itemRecord['value'];
		$output = "<div id='esoil_itemnewvalue'>$value</div> <img src='http://esoitem.uesp.net/resources/currency_gold_32.png' class='esoil_goldimg'>";
		return $output;
	}
	
	
	private function MakeItemStolenText()
	{
		if ($this->itemStolen <= 0) return "";
		
		$output = "<img src='http://esoitem.uesp.net/resources/stolenitem.png' class='esoil_stolenicon' />";
		
		return $output;
	}
	
	
	private function MakeItemBindTypeText()
	{
		if ($this->itemBound > 0) return "Bound";
		$bindType = $this->itemRecord['bindType'];
		
		if ($bindType <= 0) return "";
		return GetEsoItemBindTypeText($bindType);
	}
	
	
	private function MakeItemTypeText()
	{
		switch ($this->itemRecord['type'])
		{
			case 1:
			case 2:
				return GetEsoItemEquipTypeText($this->itemRecord['equipType']);
			case 4:
				return "Food";
			default:
				return GetEsoItemTypeText($this->itemRecord['type']);
		}
	}
	
	
	private function MakeItemSubTypeText()
	{
		$type = $this->itemRecord['type'];
		if ($type <= 0) return "";
		
		if ($type == 2) //armor
		{
			if ($this->itemRecord['armorType'] > 0) return "(" . GetEsoItemArmorTypeText($this->itemRecord['armorType']) . ")";
			return "";
		}
		elseif ($type == 1) //weapon
		{
			return "(" . GetEsoItemWeaponTypeText($this->itemRecord['weaponType']) . ")";
		}
		
		return "";
	}
	
	
	private function HasItemBar()
	{
		$type = $this->itemRecord['type'];
		$equipType = $this->itemRecord['equipType'];
		
		if ($type <= 0 || $equipType == 12 || $equipType == 2) return false;
		
			/* Weapons with no enchantments */
		if ($type == 1 && $this->itemRecord['weaponType'] != 14)
		{
			$hasEnchant = false;
			if ($this->itemRecord['enchantName'] != "") $hasEnchant = true;
			if ($this->itemRecord['enchantDesc'] != "") $hasEnchant = true;
			if ($this->enchantRecord1 != null) $hasEnchant = true;
			if ($this->enchantRecord2 != null)  $hasEnchant = true;
			if (!$hasEnchant) return false;
		}
		
		return true;
	}
	
	
	private function MakeItemBarClass()
	{
		if (!$this->HasItemBar()) return "esoilHidden";
		return "";
	}
	
	
	private function MakeItemBarLink()
	{
		if (!$this->HasItemBar()) return "";
		
		$type = $this->itemRecord['type'];
		$maxCharges = $this->itemRecord['maxCharges'];
		
		if ($maxCharges <= 0 && ($this->enchantRecord1 != null || $this->enchantRecord2 != null))
		{
					// TODO: This is a rough Estimate
					// MaxCharges = 6.242428345 * Level + 112.8789751
					// MaxCharges = -108.8146179 + 0.6707490449 * WD - 6.371375423E-005 * WD*WD - 7.121772545E-008 * WD*WD*WD
					// $maxCharges = $this->itemRecord['weaponPower'] / 2;
			$wp = intval($this->itemRecord['weaponPower']);
			$maxCharges = -108.8146179 + 0.6707490449 * $wp - 6.371375423E-005 * $wp * $wp - 7.121772545E-008 * $wp * $wp * $wp;
			if ($this->itemRecord['trait'] == 2) $maxCharges *= $this->itemRecord['quality']*0.25 + 2;
			
			$this->itemRecord['estimatedMaxCharges'] = $maxCharges;
			$this->itemAllData[0]['estimatedMaxCharges'] = $maxCharges;
		}
		
		if ($type == 1 && $maxCharges > 0 && $this->itemRecord['weaponType'] != 14)
		{
			$charges = $this->itemCharges;
			if ($charges < 0) $charges = $maxCharges;
		
			$coverImageSize = ($maxCharges - $charges) * 112 / $maxCharges;
			if ($coverImageSize < 0) $coverImageSize = 0;
			if ($coverImageSize > 112) $coverImageSize = 112;
			
			return "<img src='http://esoitem.uesp.net/resources/eso_item_chargebar.png' id='esoil_chargebar' /><img src='http://esoitem.uesp.net/resources/eso_item_barblack.png' id='esoil_chargebar_coverleft' style='width: {$coverImageSize}px;' /><img src='http://esoitem.uesp.net/resources/eso_item_barblack.png' id='esoil_chargebar_coverright' style='width: {$coverImageSize}px;' />";
		}
		
		if ($type == 1 || $type == 2)
		{
			$condition = $this->itemCharges/100;
			if ($condition < 0) $condition = 100;
			
			$coverImageSize = (100 - $condition) * 112 / 100;
			if ($coverImageSize < 0) $coverImageSize = 0;
			if ($coverImageSize > 112) $coverImageSize = 112;
			
			return "<img src='http://esoitem.uesp.net/resources/eso_item_conditionbar.png' id='esoil_conditionbar' /><img src='http://esoitem.uesp.net/resources/eso_item_barblack.png' id='esoil_conditionbar_coverleft' style='width: {$coverImageSize}px;' /><img src='http://esoitem.uesp.net/resources/eso_item_barblack.png' id='esoil_conditionbar_coverright' style='width: {$coverImageSize}px;' />";
		}
		
		return "";
	}
	
	
	private function MakeItemEnchantBlock()
	{
		$output = "";
		
			/* TODO: Temp fix for potions showing enchantments/sets */
		if ($this->itemRecord['type'] == 7) return "";
		
		if ($this->enchantRecord1 != null)
		{
			$enchantName = strtoupper($this->enchantRecord1['enchantName']);
			$enchantDesc = $this->FormatDescriptionText($this->enchantRecord1['enchantDesc']);
			if ($enchantName != "") $output .= "<div class='esoil_white esoil_small'>$enchantName</div><br />";
			if ($enchantDesc != "") $output .= "$enchantDesc";
		}
		
		if ($this->enchantRecord2 != null)
		{
			$enchantName = strtoupper($this->enchantRecord2['enchantName']);
			$enchantDesc = $this->FormatDescriptionText($this->enchantRecord2['enchantDesc']);
			
			if ($enchantDesc != "")
			{
				if ($output != "") $output .= "<p style='margin-top: 0.7em; margin-bottom: 0.7em;'/>";
				if ($enchantName != "") $output .= "<div class='esoil_white esoil_small'>$enchantName</div><br />";
				$output .= "$enchantDesc";
			}
		}
		
		if ($this->enchantRecord1 == null && $this->enchantRecord2 == null)
		{
			$enchantName = strtoupper($this->itemRecord['enchantName']);
			$enchantDesc = $this->FormatDescriptionText($this->itemRecord['enchantDesc']);
			if ($enchantName != "") $output .= "<div class='esoil_white esoil_small'>$enchantName</div><br />";
			if ($enchantDesc != "") $output .= "$enchantDesc";
		}
		
		return $output;
	}
	
	
	private function MakeItemTraitBlock()
	{
		$trait = $this->itemRecord['trait'];
		$traitDesc = $this->FormatDescriptionText($this->itemRecord['traitDesc']);
		$traitName = strtoupper(GetEsoItemTraitText($trait, $this->version));
		
		if ($trait <= 0) return "";
		return "<div class='esoil_white esoil_small'>$traitName</div><br />$traitDesc";
	}
	
	
	private function FormatDescriptionText($desc)
	{
		return FormatEsoItemDescriptionText($desc);
	}
	
	
	private function FormatSetDescriptionText($desc, $setCount)
	{
		if (self::ESOIL_USEPRECENT_CRITICALVALUE)
			$output = FormatEsoCriticalDescriptionText($desc, $this->itemRecord['level']);
		else
			$output = $desc;
		
		if ($this->itemSetCount >= 0 && $setCount > $this->itemSetCount)
		{
			$output = preg_replace("#\|c([0-9a-fA-F]{6})([a-zA-Z \-0-9\.%]+)\|r#s", "$2", $output);
			$output = str_replace("\n", "<br />", $output);
			
			$output = "<div class='esoil_itemsetdisabled'>" . $output . "</div>";
		}
		else
		{
			$output = FormatEsoItemDescriptionText($desc);
		}
				
		return $output;
	}
	
	
	private function MakeItemSetBlock()
	{
			/* TODO: Temp fix for potions showing enchantments/sets */
		if ($this->itemRecord['type'] == 7) return "";
		
		$setName = strtoupper($this->itemRecord['setName']);
		if ($setName == "") return "";
		
		$setMaxEquipCount = $this->itemRecord['setMaxEquipCount'];
		$setBonusCount = (int) $this->itemRecord['setBonusCount'];
		$output = "<div class='esoil_white esoil_small'>PART OF THE $setName SET ($setMaxEquipCount/$setMaxEquipCount ITEMS)</div>";
		
		for ($i = 1; $i <= $setBonusCount && $i <= 5; $i += 1)
		{
			$setCount = $this->itemRecord['setBonusCount' . $i];
			$setDesc = $this->FormatSetDescriptionText($this->itemRecord['setBonusDesc' . $i], $setCount);
			$output .= "<br />$setDesc";
		}
		
		return $output;
	}
	
	
	private function MakeItemAbilityBlock()
	{
		$ability = strtoupper($this->itemRecord['abilityName']);
		$abilityDesc = $this->FormatDescriptionText($this->itemRecord['abilityDesc']);
		$cooldown = ((int) $this->itemRecord['abilityCooldown']) / 1000;
		
		if ($abilityDesc == "") return "";
		return "<div class='esoil_white esoil_small'>$ability</div> $abilityDesc ($cooldown second cooldown)";
	}
	
	
	private function MakeItemTraitAbilityBlock()
	{
		if ($this->itemRecord['traitAbilityDescArray'] == null)
		{
			$abilityDesc = strtoupper($this->itemRecord['traitAbilityDesc']);
			if ($abilityDesc == "") return "";
			$cooldown = round($this->itemRecord['traitCooldown'] / 1000);
			return "$abilityDesc ($cooldown second cooldown)";
		}
		
		$abilityDesc = array();
		$cooldownDesc = "";
		
		foreach ($this->itemRecord['traitAbilityDescArray'] as $index => $desc)
		{
			//$desc = strtoupper($desc);
			$cooldown = round($this->itemRecord['traitCooldownArray'][$index] / 1000);
			$abilityDesc[] = $desc;
			$cooldownDesc = " ($cooldown second cooldown)";
		}
		
		if (count($abilityDesc) == 0) return "";
		
		$output = implode("\n\n", $abilityDesc) . "\n" . $cooldownDesc;
		$output = $this->FormatDescriptionText($output);
		return $output;		
	}
	
	
	private function GetItemLeftBlockDisplay()
	{
		
		switch ($this->itemRecord['type'])
		{
			case 2:
				$equipType = $this->itemRecord['equipType'];
				if ($equipType == 2 || $equipType == 12) return "none";
				return "inline-block";
			case 1:
				return "inline-block";
		}
		
		return "none";
	}
	
	
	private function GetItemLevelBlockDisplay()
	{
		$level = $this->itemRecord['level'];
		if ($level <= 0) return "none";
		
		switch ($this->itemRecord['type'])
		{
			case 2:
			case 1:
				return "inline-block";
		}
		
		return "inline-block";
	}
	
	
	private function GetItemValueBlockDisplay()
	{
		$value = $this->itemRecord['value'];
		
		if ($value <= 0) return "none";
		return "inline-block";
	}
	
	
	private function GetItemRightBlockDisplay()
	{
		if (!$this->useUpdate10Display) return $this->GetItemValueBlockDisplay();
		
		$level = $this->itemRecord['level'];
		
		if ($level > 50) return "inline-block";
		if (!is_numeric($level)) return $level;
		
		return "none";
	}
	
	
	private function GetItemNewValueBlockDisplay()
	{
		if (!$this->useUpdate10Display) return "none";
	
		if ($this->itemRecord['value'] > 0) return "inline-block";
		return "none";
	}
	
	
	private function GetItemDataJson()
	{
		$output = json_encode($this->itemAllData);
		return $output;
	}
	
	
	private function MakeItemLink()
	{
		$d1 = $this->itemRecord['itemId'];
		$d2 = $this->itemRecord['internalSubtype'];
		$d3 = $this->itemRecord['internalLevel'];
		$d4 = $this->enchantId1;
		$d5 = $this->enchantIntType1;
		$d6 = $this->enchantIntLevel1;
		$d7 = $this->enchantId2;
		$d8 = $this->enchantIntType2;
		$d9 = $this->enchantIntLevel2;
		$d10 = 0;
		$d11 = 0;
		$d12 = 0;
		$d13 = 0;
		$d14 = 0;
		$d15 = 0;
		$d16 = $this->itemRecord['style']; //Style
		$d17 = $this->itemCrafted < 0 ? 0 : $this->itemCrafted; //Crafted
		$d18 = $this->itemBound < 0 ? 0 : $this->itemBound; //Bound
		$d19 = $this->itemCharges < 0 ? 0 : $this->itemCharges; //Charges
		$d20 = $this->itemPotionData;
		$itemName = $this->itemRecord['name'];
		
		$link = "|H0:item:$d1:$d2:$d3:$d4:$d5:$d6:$d7:$d8:$d9:$d10:$d11:$d12:$d13:$d14:$d15:$d16:$d17:$d18:$d19:$d20|h[$itemName]|h";
		
		return $link;
	}
	
	
	private function MakeItemCraftedBlock()
	{
		if ($this->itemCrafted <= 0) return "";
		return "Created by: Someone";
	}
	
	
	private function MakeItemTagsBlock()
	{
		if ($this->itemRecord['tags'] == "") return "";
		
		$output  = "<div id='esoil_itemtags_title'>Treasure Type:</div>";
		$output .= $this->itemRecord['tags'];
		return $output;
	}
	
	
	private function GetItemLinkURL()
	{
		$itemLinkURL = '';
		
		if ($this->version != '' || $this->showSummary || $this->enchantId1 > 0 || $this->enchantId2 > 0 ||
			$this->itemPotionData > 0 || $this->itemCharges >= 0 || $this->itemStolen > 0)
		{
			$showSummary = $this->showSummary ? 'summary' : '';
			$itemLinkURL = 	"itemLinkImage.php?itemid={$this->itemRecord['itemId']}&level={$this->itemRecord['level']}&" .
							"quality={$this->itemRecord['quality']}&enchantid={$this->enchantId1}&enchantintlevel={$this->enchantIntLevel1}&" .
							"enchantinttype={$this->enchantIntType1}&v={$this->version}&{$showSummary}&potiondata={$this->itemPotionData}&stolen={$this->itemStolen}&" .
							"itemlink={$this->itemLink}";			
		}
		else 
		{
			$itemLinkURL = "http://esoitem.uesp.net/item-{$this->itemRecord['itemId']}-{$this->itemRecord['level']}-{$this->itemRecord['quality']}.png";
		}
		
		return $itemLinkURL;
	}
	
	
	private function MakeItemStyle()
	{
		$type = $this->itemRecord['type'];
		if ($type != 1 && $type != 2) return "";
		
		if ($this->itemStyle > 0) return GetEsoItemStyleText($this->itemStyle);
		if ($this->itemRecord['style'] > 0) return GetEsoItemStyleText($this->itemRecord['style']);
		return "";
	}
	
	
	private function CheckVersionLessThan($version)
	{
		if ($this->version == "") return false;
		return $this->version < $version;
	}
	
	
	private function MakePotencyItemDescription()
	{
		if (!$this->useUpdate10Display) return $this->MakeOldPotencyItemDescription();
		
		$glyphMinLevel = $this->itemRecord['glyphMinLevel'];
		if ($glyphMinLevel == 0) return $this->itemRecord['description'];
		
		$minDesc = "";
		
		if ($glyphMinLevel < 50)
		{
			$minDesc = "level $glyphMinLevel";
		}
		else
		{
			$cp = ($glyphMinLevel - 50) * 10;
			$minDesc = "level 50 <img src='http://esoitem.uesp.net/resources/champion_icon.png' class='esoil_cpimgsmall'>CP $cp";
		}
		
		$desc = "Used to create glyphs of $minDesc and higher.";
		return $desc;		
	}
	
	
	private function MakeOldPotencyItemDescription()
	{
		$glyphMinLevel = $this->itemRecord['glyphMinLevel'];
		$glyphMaxLevel = $this->itemRecord['glyphMaxLevel'];
		if ($glyphMinLevel == 0 && $glyphMaxLevel == 0) return $this->itemRecord['description'];
	
		$minDesc = "";
		$maxDesc = "";
	
		if ($glyphMinLevel < 50)
		{
			$minDesc = "level $glyphMinLevel";
		}
		else
		{
			$glyphMinLevel = $glyphMinLevel - 50;
			if ($this->CheckVersionLessThan(9)) $glyphMinLevel += 1;
			$minDesc = "|t32:32:EsoUI/Art/UnitFrames/target_veteranRank_icon.dds|trank $glyphMinLevel";
		}
	
		if ($glyphMaxLevel < 50)
		{
			$maxDesc = "level $glyphMaxLevel";
		}
		else
		{
			$glyphMaxLevel = $glyphMaxLevel - 50;
			if ($this->CheckVersionLessThan(9)) $glyphMaxLevel += 1;
			$maxDesc = "|t32:32:EsoUI/Art/UnitFrames/target_veteranRank_icon.dds|trank $glyphMaxLevel";
		}
	
		if ($minDesc == $maxDesc)
			$desc = "Used to create glyphs of $minDesc.";
		else
			$desc = "Used to create glyphs of $minDesc to $maxDesc.";
	
		return $desc;
	}
	
	
	private function MakeItemDescription()
	{
		$desc = $this->itemRecord['description'];
		$matDesc = $this->itemRecord['materialLevelDesc'];
		if ($this->itemRecord['type'] == 51) $desc = $this->MakePotencyItemDescription();
		if ($matDesc != "") $desc = $matDesc;
		
		return FormatEsoItemDescriptionText($desc);
	}
	
	
	private function MakeQuestItemDescription()
	{
		$desc = $this->questItemData['description'];
		return FormatEsoItemDescriptionText($desc);
	}
	
	
	private function MakeCollectibleItemDescription()
	{
		$desc = $this->collectibleItemData['description'] . "\n\n" . $this->collectibleItemData['hint'];
		$desc = preg_replace("#\<\<player{his/her}\>\>#", "his", $desc);
		return FormatEsoItemDescriptionText($desc);
	}
	
	
	private function GetItemRawVersion()
	{
		if ($this->version == "")
			$suffix = GetEsoUpdateVersion();
		else
			$suffix = intval(GetEsoItemTableSuffix($this->version));
		
		return $suffix;
	}
	
	
	private function OutputHtml()
	{
		$replacePairs = array(
				'{itemName}' => $this->itemRecord['name'],
				'{itemNameUpper}' => strtoupper($this->itemRecord['name']),
				'{itemDesc}' => $this->MakeItemDescription(),
				'{itemLink}' => $this->MakeItemLink(),
				'{itemStyle}' => $this->MakeItemStyle(),
				'{itemId}' => $this->itemRecord['itemId'],
				'{itemType1}' => $this->MakeItemTypeText(),
				'{itemType2}' => $this->MakeItemSubTypeText(),
				'{itemStolen}' => $this->MakeItemStolenText(),
				'{itemBindType}' => $this->MakeItemBindTypeText(),
				'{itemValue}' => $this->itemRecord['value'],
				'{itemLevel}' => $this->MakeItemLevelSimpleString(),
				'{itemLevelRaw}' => $this->itemRecord['level'],
				'{itemQualityRaw}' => $this->itemRecord['quality'],
				'{itemLevelBlock}' => $this->MakeItemLevelString(),
				'{itemQuality}' => GetEsoItemQualityText($this->itemRecord['quality']),
				'{iconLink}' => $this->MakeItemIconImageLink(),
				'{itemLeftBlock}' => $this->MakeItemLeftBlock(),
				'{itemRightBlock}' => $this->MakeItemRightBlock(),
				'{itemNewValueBlock}' => $this->MakeItemNewValueBlock(), 
				'{itemBar}' => $this->MakeItemBarLink(),
				'{itemBarClass}' =>  $this->MakeItemBarClass(),
				'{itemEnchantBlock}' => $this->MakeItemEnchantBlock(),
				'{itemTraitBlock}' => $this->MakeItemTraitBlock(),
				'{itemSetBlock}' => $this->MakeItemSetBlock(),
				'{itemAbilityBlock}' => $this->MakeItemAbilityBlock(),
				'{itemTraitAbilityBlock}' => $this->MakeItemTraitAbilityBlock(),
				'{itemLeftBlockDisplay}' => $this->GetItemLeftBlockDisplay(),
				'{itemLevelBlockDisplay}' => $this->GetItemLevelBlockDisplay(),
				'{itemRightBlockDisplay}' => $this->GetItemRightBlockDisplay(),
				'{itemNewValueBlockDisplay}' => $this->GetItemNewValueBlockDisplay(),
				'{itemCraftedBlock}' => $this->MakeItemCraftedBlock(),
				'{itemTags}' => $this->MakeItemTagsBlock(),
				'{itemDataJson}' => $this->GetItemDataJson(),
				'{itemSimilarBlock}' => $this->MakeSimilarItemBlock(),
				'{itemEnchantId1}' => $this->itemRecord['enchantId1'],
				'{itemEnchantIntLevel1}' => $this->itemRecord['enchantIntLevel1'],
				'{itemEnchantIntType1}' => $this->itemRecord['enchantIntType1'],
				'{showSummary}' => $this->showSummary ? 'summary' : '',
				'{version}' => $this->version,
				'{versionTitle}' => $this->GetVersionTitle(),
				'{itemLinkURL}' => $this->GetItemLinkURL(),
				'{viewSumDataExtraQuery}' => $this->GetSummaryDataExtraQuery(),
				'{itemRawDataList}' => $this->MakeItemRawDataList(),
				'{rawItemVersion}' => $this->GetItemRawVersion(),
				'{extraDataLinkDisplay}' => "block",
				'{controlBlockDisplay}' => "block",
				'{similarItemBlockDisplay}' => "none",
				'{itemTypeTitle}' => "",
				'{itemDescClass}' => "",
			);
		
		$output = strtr($this->htmlTemplate, $replacePairs);
		
		print ($output);
	}
	
	
	private function OutputQuestItemHtml()
	{
		$replacePairs = array(
				'{itemName}' => $this->questItemData['name'],
				'{itemNameUpper}' => $this->MakeQuestItemName(),
				'{itemDesc}' => strtoupper($this->questItemData['name']),
				'{itemLink}' => $this->questItemData['itemLink'],
				'{itemStyle}' => "",
				'{itemId}' => $this->questItemId,
				'{itemType1}' => $this->questItemData['header'],
				'{itemType2}' => "",
				'{itemStolen}' => "",
				'{itemBindType}' => "",
				'{itemValue}' => "",
				'{itemLevel}' => "",
				'{itemLevelRaw}' => "",
				'{itemQualityRaw}' => "",
				'{itemLevelBlock}' => "",
				'{itemQuality}' => "",
				'{iconLink}' => $this->MakeQuestItemIconImageLink(),
				'{itemLeftBlock}' => "",
				'{itemRightBlock}' => "",
				'{itemNewValueBlock}' => "",
				'{itemBar}' => "",
				'{itemBarClass}' => "esoilHidden",
				'{itemEnchantBlock}' => "",
				'{itemTraitBlock}' => "",
				'{itemSetBlock}' => "",
				'{itemAbilityBlock}' => "",
				'{itemTraitAbilityBlock}' => "",
				'{itemLeftBlockDisplay}' => "none",
				'{itemLevelBlockDisplay}' => "none",
				'{itemRightBlockDisplay}' => "none",
				'{itemNewValueBlockDisplay}' => "none",
				'{itemCraftedBlock}' => "",
				'{itemTags}' => "",
				'{itemDataJson}' => "{}",
				'{itemSimilarBlock}' => "",
				'{itemEnchantId1}' => "",
				'{itemEnchantIntLevel1}' => "",
				'{itemEnchantIntType1}' => "",
				'{showSummary}' => "",
				'{version}' => $this->version,
				'{versionTitle}' => $this->GetVersionTitle(),
				'{itemLinkURL}' => "",
				'{viewSumDataExtraQuery}' => "",
				'{itemRawDataList}' => $this->MakeQuestItemRawDataList(),
				'{rawItemVersion}' => $this->GetItemRawVersion(),
				'{extraDataLinkDisplay}' => "none",
				'{controlBlockDisplay}' => "none",
				'{similarItemBlockDisplay}' => "none",
				'{itemTypeTitle}' => "Quest ",
				'{itemDescClass}' => "esoil_itemdescQuest",
		);
	
		$output = strtr($this->htmlTemplate, $replacePairs);
	
		print ($output);
	}
	

	private function MakeCollectibleItemName()
	{
		$name = strtoupper($this->collectibleItemData['name']);
		$nickname = strtoupper($this->collectibleItemData['nickname']);
		
		if ($nickname != "") $name .= "<div class='esoil_nickname'>\"".$nickname."\"</div>";
		
		return $name;
	}		
	
	
	private function OutputCollectibleItemHtml()
	{
		$replacePairs = array(
				'{itemName}' => $this->collectibleItemData['name'],
				'{itemNameUpper}' => $this->MakeCollectibleItemName(),
				'{itemDesc}' => $this->MakeCollectibleItemDescription(),
				'{itemLink}' => $this->collectibleItemData['itemLink'],
				'{itemStyle}' => "",
				'{itemId}' => $this->collectibleItemId,
				'{itemType1}' => $this->collectibleItemData['categoryName'],
				'{itemType2}' => "",
				'{itemStolen}' => "",
				'{itemBindType}' => "",
				'{itemValue}' => "",
				'{itemLevel}' => "",
				'{itemLevelRaw}' => "",
				'{itemQualityRaw}' => "",
				'{itemLevelBlock}' => "",
				'{itemQuality}' => "",
				'{iconLink}' => $this->MakeCollectibleItemIconImageLink(),
				'{itemLeftBlock}' => "",
				'{itemRightBlock}' => "",
				'{itemNewValueBlock}' => "",
				'{itemBar}' => "",
				'{itemBarClass}' => "esoilHidden",
				'{itemEnchantBlock}' => "",
				'{itemTraitBlock}' => "",
				'{itemSetBlock}' => "",
				'{itemAbilityBlock}' => "",
				'{itemTraitAbilityBlock}' => "",
				'{itemLeftBlockDisplay}' => "none",
				'{itemLevelBlockDisplay}' => "none",
				'{itemRightBlockDisplay}' => "none",
				'{itemNewValueBlockDisplay}' => "none",
				'{itemCraftedBlock}' => "",
				'{itemTags}' => "",
				'{itemDataJson}' => "{}",
				'{itemSimilarBlock}' => "",
				'{itemEnchantId1}' => "",
				'{itemEnchantIntLevel1}' => "",
				'{itemEnchantIntType1}' => "",
				'{showSummary}' => "",
				'{version}' => $this->version,
				'{versionTitle}' => $this->GetVersionTitle(),
				'{itemLinkURL}' => "",
				'{viewSumDataExtraQuery}' => "",
				'{itemRawDataList}' => $this->MakeCollectibleItemRawDataList(),
				'{rawItemVersion}' => $this->GetItemRawVersion(),
				'{extraDataLinkDisplay}' => "none",
				'{controlBlockDisplay}' => "none",
				'{similarItemBlockDisplay}' => "none",
				'{itemTypeTitle}' => "Collectible ",
				'{itemDescClass}' => "esoil_itemdescQuest",
		);
	
		$output = strtr($this->htmlTemplate, $replacePairs);
	
		print ($output);
	}
	
	
	public function GetSummaryDataExtraQuery()
	{
		$output = "";
		
		if ($this->version != "")
		{
			$output = "version=". urlencode($this->version) ."&";
		}
		
		return $output;
	}
	
	
	public function OutputRawData()
	{
		$this->CreateRawItemDataKeys();
		$this->CreateRawItemDataSortedIndexes();
		
		if ($this->outputType == "html") return $this->OutputRawDataHtml();
		
		print($this->GetRawItemDataCsv());
	}
	
	
	public function OutputQuestItemRawData()
	{
		$replacePairs = array(
				'{itemName}' => $this->questItemData['name'],
				'{itemNameUpper}' => strtoupper($this->questItemData['name']),
				'{itemId}' => $this->questItemId,
				'{iconLink}' => $this->MakeQuestItemIconImageLink(),
				'{showSummary}' => "",
				'{version}' => $this->version,
				'{versionTitle}' => $this->GetVersionTitle(),
				'{itemLinkURL}' => $this->GetQuestItemLinkURL(),
				'{rawItemData}' => $this->GetRawQuestItemDataHtml(),
		);
		
		$rawDataTemplate = file_get_contents(self::ESOIL_RAWDATA_HTML_TEMPLATE);
		
		$output = strtr($rawDataTemplate, $replacePairs);
		print ($output);
	}
	
	
	public function OutputCollectibleItemRawData()
	{
		$replacePairs = array(
				'{itemName}' => $this->collectibleItemData['name'],
				'{itemNameUpper}' => strtoupper($this->collectibleItemData['name']),
				'{itemId}' => $this->collectibleItemId,
				'{iconLink}' => $this->MakeCollectibleItemIconImageLink(),
				'{showSummary}' => "",
				'{version}' => $this->version,
				'{versionTitle}' => $this->GetVersionTitle(),
				'{itemLinkURL}' => $this->GetCollectibleItemLinkURL(),
				'{rawItemData}' => $this->GetRawCollectibleItemDataHtml(),
		);
		
		$rawDataTemplate = file_get_contents(self::ESOIL_RAWDATA_HTML_TEMPLATE);
		
		$output = strtr($rawDataTemplate, $replacePairs);
		print ($output);
	}
	
	
	public function OutputRawDataHtml()
	{
		$replacePairs = array(
				'{itemName}' => $this->itemRecord['name'],
				'{itemNameUpper}' => strtoupper($this->itemRecord['name']),
				'{itemId}' => $this->itemRecord['itemId'],
				'{iconLink}' => $this->MakeItemIconImageLink(),
				'{showSummary}' => $this->showSummary ? 'summary' : '',
				'{version}' => $this->version,
				'{versionTitle}' => $this->GetVersionTitle(),
				'{itemLinkURL}' => $this->GetItemLinkURL(),
				'{rawItemData}' => $this->GetRawItemDataHtml(),
		);		
		
		$rawDataTemplate = file_get_contents(self::ESOIL_RAWDATA_HTML_TEMPLATE);
		
		$output = strtr($rawDataTemplate, $replacePairs);
		print ($output);
	}
	
	
	public function GetRawItemDataHtml()
	{
		$output = "<table class='esoil_rawitemdata_table'>\n";
		$output .= $this->GetRawItemDataHeaderHtml();
		
		foreach ($this->rawDataSortedIndexes as $sortedIndex)
		{
			$index = $sortedIndex[2];
			$item = $this->itemAllData[$index];
			$output .= $this->CreateRawItemDataHtml($item);
		}
		
		$output .= "</table>\n";
		return $output;
	}
	
	
	public function GetRawQuestItemDataHtml()
	{
		$output  = "<table class='esoil_rawitemdata_table'>\n";
		$output .= "<tr>";
		
		foreach ($this->questItemData as $key => $value)
		{
			$output .= "<th>$key</th>\n";
		}
		
		$output .= "</tr>\n";
		
		foreach ($this->questItemData as $key => $value)
		{
			$output .= "<tr>";
			$output .= "<td>$value</td>\n";
			$output .= "</tr>\n";
		}
	
		$output .= "</table>\n";
		return $output;
	}
	
	
	public function GetRawCollectibleItemDataHtml()
	{
		$output  = "<table class='esoil_rawitemdata_table'>\n";
		$output .= "<tr>";
		
		foreach ($this->collectibleItemData as $key => $value)
		{
			$output .= "<th>$key</th>\n";
		}
		
		$output .= "</tr>\n";
	
		foreach ($this->collectibleItemData as $key => $value)
		{
			$output .= "<tr>";
			$output .= "<td>$value</td>\n";
			$output .= "</tr>\n";
		}
	
		$output .= "</table>\n";
		return $output;
	}
	
	
	public function GetRawItemDataCsv()
	{
		$output = "";
		$output .= $this->GetRawItemDataHeaderCsv();
	
		foreach ($this->rawDataSortedIndexes as $sortedIndex)
		{
			$index = $sortedIndex[2];
			$item = $this->itemAllData[$index];
			$output .= $this->CreateRawItemDataCsv($item);
		}
	
		return $output;
	}
	
		
	public function CreateRawItemDataSortedIndexes()
	{
		$this->rawDataSortedIndexes = array();
	
		foreach ($this->itemAllData as $key => $item)
		{
			$level = $item['internalLevel'];
			$type = $item['internalSubtype'];
			
			if ($level == null) $level = $this->itemAllData[0]['internalLevel'];
			if ($type == null) $type = $this->itemAllData[0]['internalSubtype'];
			
			$this->rawDataSortedIndexes[] = array(
				0 => $level,
				1 => $type,
				2 => $key,
			);
		}
		
		usort($this->rawDataSortedIndexes, "compareRawDataSortedIndex");
	}
	
	
	public function CreateRawItemDataKeys()
	{
		$this->rawDataKeys = array();
		
		foreach ($this->itemAllData[0] as $key => $value)
		{
			if ($key == "id" || $key == "logId" || $key == "itemId" || $key == "link") continue;
			$this->rawDataKeys[] = $key;
		}
	}
	
	
	public function GetRawItemDataHeaderHtml()
	{
		$output = "<tr>";
		
		foreach ($this->rawDataKeys as $key)
		{
			$output .= "<th>$key</th>\n";
		}
		
		$output .= "</tr>\n";
		
		return $output;
	}
	
	
	public function GetRawItemDataHeaderCsv()
	{
		$output = "";
	
		foreach ($this->rawDataKeys as $key)
		{
			if ($output != "") $output .= ",";
			$output .= "\"$key\"";
		}
	
		$output .= "\n";
	
		return $output;
	}
	
	
	public function CreateRawItemDataHtml($item)
	{
		$output = "<tr>";
		
		foreach ($this->rawDataKeys as $key)
		{
			$value = $item[$key];
			if ($value == null) $value = $this->itemAllData[0][$key];
			$output .= "<td>$value</td>\n";
		}
		
		$output .= "</tr>\n";
		
		return $output;
	}
	
	
	public function CreateRawItemDataCsv($item)
	{
		$output = "";
	
		foreach ($this->rawDataKeys as $key)
		{
			$value = $item[$key];
			if ($value == null) $value = $this->itemAllData[0][$key];
			$value = addslashes($value);
			$value = str_replace("\n", "\\n", $value);
			
			if ($output != "") $output .= ",";
			$output .= "\"$value\"";
		}
	
		$output .= "\n";
	
		return $output;
	}
	
	
	public function GetVersionTitle()
	{
		if ($this->GetTableSuffix() == "") return "";
		return " v" . $this->version . "";
	}
	
	
	public function DumpItem()
	{
		foreach ($this->itemRecord as $key => $value)
		{
			print("$key = $value\n");
		}
	}
	
	
	public function DumpQuestItem()
	{
		foreach ($this->questItemData as $key => $value)
		{
			print("$key = $value\n");
		}
	}
	
	
	public function DumpCollectibleItem()
	{
		foreach ($this->collectibleItemData as $key => $value)
		{
			print("$key = $value\n");
		}
	}
	
	
	public function ShowQuestItem()
	{
		if (!$this->LoadQuestItemRecord()) $this->LoadQuestItemErrorData();
		
		if ($this->outputRaw)
			$this->OutputQuestItemRawData();
		else if ($this->outputType == "html")
			$this->OutputQuestItemHtml();
		elseif ($this->outputType == "text")
			$this->DumpQuestItem();
		else
			return $this->ReportError("Error: Unknown output type '{$this->outputType}' specified!");
		
		return true;
	}
	
	
	public function ShowCollectibleItem()
	{
		if (!$this->LoadCollectibleItemRecord()) $this->LoadCollectibleItemErrorData();
		
		if ($this->outputRaw)
			$this->OutputCollectibleItemRawData();
		else if ($this->outputType == "html")
			$this->OutputCollectibleItemHtml();
		elseif ($this->outputType == "text")
			$this->DumpCollectibleItem();
		else
			return $this->ReportError("Error: Unknown output type '{$this->outputType}' specified!");
		
		return true;
	}
	
	
	public function ShowItem()
	{
		$this->OutputHtmlHeader();
		
		if ($this->questItemId > 0) return $this->ShowQuestItem();
		if ($this->collectibleItemId > 0) return $this->ShowCollectibleItem();
		
		if (!$this->LoadItemRecord()) $this->LoadItemErrorData();
		$this->LoadEnchantRecords();
		
		if ($this->showSummary)
		{
			$this->LoadItemSummaryData();
			$this->MergeItemSummary();
		}
		
		if (!$this->embedLink)
		{
			$this->LoadAllItemData();
			$this->LoadSimilarItemRecords();
		}
		
		if ($this->outputRaw)
			$this->OutputRawData();
		else if ($this->outputType == "html")
			$this->OutputHtml();
		elseif ($this->outputType == "text")
			$this->DumpItem();
		else
			$this->ReportError("Error: Unknown output type '{$this->outputType}' specified!");
		
		return true;
	}
	
};


$g_EsoItemLink = new CEsoItemLink();
$g_EsoItemLink->ShowItem();


?>



