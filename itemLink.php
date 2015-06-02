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


class CEsoItemLink
{
	const ESOIL_HTML_TEMPLATE = "templates/esoitemlink_template.txt";
	const ESOIL_HTML_EMBED_TEMPLATE = "templates/esoitemlink_embed_template.txt";
	const ESOIL_ICON_PATH = "/home/uesp/www/eso/gameicons/";
	const ESOIL_ICON_URL = "http://content3.uesp.net/eso/gameicons/";
	const ESOIL_ICON_UNKNOWN = "unknown.png";
	
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
			"style" => -1,
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
	public $itemLevel = -1;		// 1-65
	public $itemQuality = -1;	// 0-5
	public $itemIntLevel = 1;	// 1-50
	public $itemIntType = 1;	// 1-400
	public $itemBound = -1;
	public $itemStyle = -1;
	public $itemCrafted = -1;
	public $itemCharges = -1;
	public $itemPotionData = -1;
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
	public $itemSimilarRecords = array();
	public $itemSummary = array();
	public $outputType = "html";
	public $showAll = false;
	public $itemErrorDesc = "";
	public $db = null;
	public $htmlTemplate = "";
	public $embedLink = false;
	public $showSummary = false;
	
	
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
		$result = preg_match('/\|H(?P<color>[A-Za-z0-9]*)\:item\:(?P<itemId>[0-9]*)\:(?P<subtype>[0-9]*)\:(?P<level>[0-9]*)\:(?P<enchantId1>[0-9]*)\:(?P<enchantSubtype1>[0-9]*)\:(?P<enchantLevel1>[0-9]*)\:(?P<enchantId2>[0-9]*)\:(?P<enchantSubtype2>[0-9]*)\:(?P<enchantLevel2>[0-9]*)\:(.*?)\:(?P<style>[0-9]*)\:(?P<crafted>[0-9]*)\:(?P<bound>[0-9]*)\:(?P<charges>[0-9]*)\:(?P<potionData>[0-9]*)\|h\[?(?P<name>[a-zA-Z0-9 %_\(\)\'\-]*)(?P<nameCode>.*?)\]?\|h/', $itemLink, $matches);
		if (!$result) return $this->ReportError("Failed to parse item link: $itemLink");
		
		$this->itemId = $matches['itemId'];
		$this->itemIntLevel = $matches['level'];
		$this->itemIntType = $matches['subtype'];
		
		$this->itemStyle = $matches['style'];
		$this->itemBound = $matches['bound'];
		$this->itemCrafted = $matches['crafted'];
		$this->itemCharges = $matches['charges'];
		$this->itemPotionData = $matches['potionData'];
		
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
				$this->itemLevel = (int) ltrim($level, 'v') + 50;
			else
				$this->itemLevel = (int) $level;
			
			$this->itemQuality = 1;
		}
		
		if (array_key_exists('quality', $this->inputParams))
		{
			$this->itemQuality = (int) $this->inputParams['quality'];
			if ($this->itemLevel < 0) $this->itemLevel = 1;
		}
		
		if (array_key_exists('show', $this->inputParams) || array_key_exists('showall', $this->inputParams)) $this->showAll = true;
		if (array_key_exists('summary', $this->inputParams)) $this->showSummary = true;
		if (array_key_exists('intlevel', $this->inputParams)) $this->itemIntLevel = (int) $this->inputParams['intlevel'];
		if (array_key_exists('inttype', $this->inputParams)) $this->itemIntType = (int) $this->inputParams['inttype'];
		
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
		
		$query = "SELECT * FROM minedItem WHERE itemId={$this->itemId} ORDER BY level, quality;";
		
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
	
	
	public function MergeItemSummary()
	{
		if ($this->itemSummary == null || count($this->itemSummary) == 0) return false;
		
		foreach (self::$ESOIL_ITEM_SUMMARY_FIELDS as $field)
		{
			$value = $this->itemSummary[$field];
			
			if ($field == "level" && $value == "")
				$this->itemRecord[$field] = '1-V15';
			else
				$this->itemRecord[$field] = $value;
		}
		
		return true;
	}
	
	
	private function LoadItemSummaryData()
	{
		if ($this->itemId <= 0) return $this->ReportError("ERROR: Missing or invalid item ID specified (1-65000)!");
		$query = "SELECT * FROM minedItemSummary WHERE itemId={$this->itemId};";
		
		$result = $this->db->query($query);
		if (!$result) return $this->ReportError("ERROR: Database query error! " . $this->db->error);
		
		$this->itemSummary = $result->fetch_assoc();
		if (!$this->itemSummary) $this->ReportError("ERROR: No item summary found matching ID {$this->itemId}!");
		
		return true;
	}
	
	
	private function LoadItemRecord()
	{
		if ($this->itemId <= 0) return $this->ReportError("ERROR: Missing or invalid item ID specified (1-65000)!");
		$query = "";
		
		if ($this->itemLevel >= 1)
		{
			if ($this->itemLevel <= 0) return $this->ReportError("ERROR: Missing or invalid item Level specified (1-64)!");
			if ($this->itemQuality < 0) return $this->ReportError("ERROR: Missing or invalid item Quality specified (1-5)!");
			$query = "SELECT * FROM minedItem WHERE itemId={$this->itemId} AND level={$this->itemLevel} AND quality={$this->itemQuality} LIMIT 1;";
			$this->itemErrorDesc = "id={$this->itemId}, Level={$this->itemLevel}, Quality={$this->itemQuality}";
		}
		else
		{
			if ($this->itemIntType < 0) return $this->ReportError("ERROR: Missing or invalid item internal type specified (1-400)!");
			$query = "SELECT * FROM minedItem WHERE itemId={$this->itemId} AND internalLevel={$this->itemIntLevel} AND internalSubtype={$this->itemIntType} LIMIT 1;";
			$this->itemErrorDesc = "id={$this->itemId}, Internal Level={$this->itemIntLevel}, Internal Type={$this->itemIntType}";
		}
		
		$result = $this->db->query($query);
		if (!$result) return $this->ReportError("ERROR: Database query error! " . $this->db->error);
		
		if ($result->num_rows === 0)
		{
			if ($this->itemLevel <= 0 && $this->itemIntType == 1)
			{
				$this->itemIntType = 2;
				$query = "SELECT * FROM minedItem WHERE itemId={$this->itemId} AND internalLevel={$this->itemIntLevel} AND internalSubtype={$this->itemIntType} LIMIT 1;";
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
		
		$this->itemRecord = $row;
		return true;
	}
	
	
	private function LoadEnchantRecords()
	{
		if ($this->enchantId1 > 0 && $this->enchantIntLevel1 > 0 && $this->enchantIntType1 > 0)
		{
			$query = "SELECT * FROM minedItem WHERE itemId={$this->enchantId1} AND internalLevel={$this->enchantIntLevel1} AND internalSubtype={$this->enchantIntType1} LIMIT 1;";
			$result = $this->db->query($query);
			if (!$result) return $this->ReportError("ERROR: Database query error! " . $this->db->error);
			
			$result->data_seek(0);
			$row = $result->fetch_assoc();
			if ($row) $this->enchantRecord1 = $row;
		}
		
		if ($this->enchantId2 > 0 && $this->enchantIntLevel2 > 0 && $this->enchantIntType2 > 0)
		{
			$query = "SELECT * FROM minedItem WHERE itemId={$this->enchantId2} AND internalLevel={$this->enchantIntLevel2} AND internalSubtype={$this->enchantIntType2} LIMIT 1;";
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
		$query = "SELECT id, internalLevel, internalSubtype FROM minedItem WHERE itemId={$this->itemId} AND level={$this->itemLevel} AND quality={$this->itemQuality};";
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
		header("Expires: 0");
		header("Pragma: no-cache");
		header("Cache-Control: no-cache, no-store, must-revalidate");
		header("Pragma: no-cache");
		header("Access-Control-Allow-Origin: " . $_SERVER['HTTP_ORIGIN'] . "");
		
		if ($this->outputType == "html")
			header("content-type: text/html");
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
		
		if ($this->itemStyle > 0 && $this->itemStyle != $this->itemRecord['style'])
		{
			$this->itemRecord['origStyle'] = $this->itemRecord['style'];
			$this->itemRecord['style'] = $this->itemStyle;
		}
		
		foreach ($this->itemRecord as $key => $value)
		{
			if (!$this->showAll && ($key == 'id' || $key == 'logId' || $value == "" || $value == '-1' || $value == '0')) continue;
			$id = "esoil_rawdata_" . $key;
			
			if ($key == "link") $value = $this->MakeItemLink();
			
			if ($key == "icon")
				$output .= "\t<tr><td>$key</td><td id='$id'><img id='esoil_rawdata_iconimage' src='{$this->MakeItemIconImageLink()}' /> $value</td></tr>\n";
			else
				$output .= "\t<tr><td>$key</td><td id='$id'>$value</td></tr>\n";
			
		}
		
		return $output;
	}
	
	
	private function MakeItemIconImageLink()
	{
		$icon = $this->itemRecord['icon'];
		if ($icon == null || $icon == "") $icon = self::ESOIL_ICON_UNKNOWN;
		
		$icon = preg_replace('/dds$/', 'png', $icon);
		$icon = preg_replace('/^\//', '', $icon);
		
		$iconLink = self::ESOIL_ICON_URL . $icon;
		return $iconLink;
	}
	
	
	private function MakeItemLevelSimpleString()
	{
		$level = $this->itemRecord['level'];
		if ($level <= 0) return "Level ?";
		
		if ($level > 50)
		{
			$level -= 50;
			return "Rank V$level";
		}
		
		return "Level $level";
	}
	
	
	private function MakeItemLevelString()
	{
		$level = $this->itemRecord['level'];
		if ($level <= 0) return "?";
		
		if ($level > 50) 
		{
			$level -= 50;
			return "<img src='http://esoitem.uesp.net/resources/eso_item_veteranicon.png' /> RANK <div id='esoil_itemlevel'>$level</div>";
		}
		
		return "LEVEL <div id='esoil_itemlevel'>$level</div>";
	}
	
	
	private function MakeItemLeftBlock()
	{
		$type = $this->itemRecord['type'];
		
		if ($type == 2) //armor 
		{
			return "ARMOR <div id='esoil_itemleft'>{$this->itemRecord['armorRating']}</div>";
		}
		elseif ($type == 1) //weapon 
		{
			return "DAMAGE <div id='esoil_itemleft'>{$this->itemRecord['weaponPower']}</div>";
		}
		
		return "";
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
	
	
	private function MakeItemBarLink()
	{
		$type = $this->itemRecord['type'];
		if ($type <= 0) return "";
		$maxCharges = $this->itemRecord['maxCharges'];
		
		if ($type == 1 && $maxCharges > 0)
		{
			$charges = $this->itemCharges;
			if ($charges < 0) $charges = $maxCharges;
			$coverImageSize = ($maxCharges - $charges) / $maxCharges * 112;
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
		
		if ($this->enchantRecord1 != null)
		{
			$enchantName = strtoupper($this->enchantRecord1['enchantName']);
			$enchantDesc = $this->FormatDescriptionText($this->enchantRecord1['enchantDesc']);
			if ($enchantDesc != "") $output .= "<div class='esoil_white esoil_small'>$enchantName</div><br/>$enchantDesc";
		}
		
		if ($this->enchantRecord2 != null)
		{
			$enchantName = strtoupper($this->enchantRecord2['enchantName']);
			$enchantDesc = $this->FormatDescriptionText($this->enchantRecord2['enchantDesc']);
			
			if ($enchantDesc != "")
			{
				if ($output != "") $output .= "<p style='margin-top: 0.7em; margin-bottom: 0.7em;'/>";
				$output .= "<div class='esoil_white esoil_small'>$enchantName</div><br/>$enchantDesc";
			}
		}
		
		if ($this->enchantRecord1 == null && $this->enchantRecord2 == null)
		{
			$enchantName = strtoupper($this->itemRecord['enchantName']);
			$enchantDesc = $this->FormatDescriptionText($this->itemRecord['enchantDesc']);
			if ($enchantDesc != "") $output .= "<div class='esoil_white esoil_small'>$enchantName</div><br/>$enchantDesc";
		}
		
		return $output;
	}
	
	
	private function MakeItemTraitBlock()
	{
		$trait = $this->itemRecord['trait'];
		$traitDesc = $this->FormatDescriptionText($this->itemRecord['traitDesc']);
		$traitName = strtoupper(GetEsoItemTraitText($trait));
		
		if ($trait <= 0) return "";
		return "<div class='esoil_white esoil_small'>$traitName</div><br />$traitDesc";
	}
	
	
	private function FormatDescriptionText($desc)
	{
		$output = preg_replace("| by ([0-9\-\.]+)|s", " by <div class='esoil_white'>$1</div>", $desc);
		$output = preg_replace("|Adds ([0-9\-\.]+)|s", "Adds <div class='esoil_white'>$1</div>", $output);
		$output = preg_replace("|for ([0-9\-\.]+)%|s", "for <div class='esoil_white'>$1</div>%", $output);
		$output = preg_replace("#\|c([0-9a-fA-F]{6})([a-zA-Z \-0-9\.]+)\|r#s", "<div style='color:#$1;display:inline;'>$2</div>", $output);
		$output = str_replace("\n", "<br />", $output);
		return $output;
	}
	
	
	private function MakeItemSetBlock()
	{
		$setName = strtoupper($this->itemRecord['setName']);
		if ($setName == "") return "";
		
		$setMaxEquipCount = $this->itemRecord['setMaxEquipCount'];
		$setBonusCount = (int) $this->itemRecord['setBonusCount'];
		$output = "<div class='esoil_white esoil_small'>PART OF THE $setName SET ($setMaxEquipCount/$setMaxEquipCount ITEMS)</div>";
		
		for ($i = 1; $i <= $setBonusCount && $i <= 5; $i += 1)
		{
			$setCount = $this->itemRecord['setBonusCount' . $i];
			$setDesc = $this->FormatDescriptionText($this->itemRecord['setBonusDesc' . $i]);
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
		$abilityDesc = strtoupper($this->itemRecord['traitAbilityDesc']);
		$cooldown = ((int) $this->itemRecord['traitCooldown']) / 1000;
		
		if ($abilityDesc == "") return "";
		return "$abilityDesc ($cooldown second cooldown)";
	}
	
	
	private function GetItemLeftBlockDisplay()
	{
		
		switch ($this->itemRecord['type'])
		{
			case 2:
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
		$d20 = 0; //PotionData
		$itemName = $this->itemRecord['name'];
		
		$link = "|H0:item:$d1:$d2:$d3:$d4:$d5:$d6:$d7:$d8:$d9:$d10:$d11:$d12:$d13:$d14:$d15:$d16:$d17:$d18:$d19:$d20|h[$itemName]|h";
		
		return $link;
	}
	
	
	private function MakeItemCraftedBlock()
	{
		if ($this->itemCrafted <= 0) return "";
		return "Crafted by: Someone";
	}
	
	
	private function MakeItemStyle()
	{
		$type = $this->itemRecord['type'];
		if ($type != 1 && $type != 2) return "";
		
		if ($this->itemStyle > 0) return GetEsoItemStyleText($this->itemStyle);
		if ($this->itemRecord['style'] > 0) return GetEsoItemStyleText($this->itemRecord['style']);
		return "";
	}
	
	
	private function OutputHtml()
	{
		$replacePairs = array(
				'{itemName}' => $this->itemRecord['name'],
				'{itemNameUpper}' => strtoupper($this->itemRecord['name']),
				'{itemDesc}' => $this->itemRecord['description'],
				'{itemLink}' => $this->MakeItemLink(),
				'{itemStyle}' => $this->MakeItemStyle(),
				'{itemId}' => $this->itemRecord['itemId'],
				'{itemType1}' => $this->MakeItemTypeText(),
				'{itemType2}' => $this->MakeItemSubTypeText(),
				'{itemBindType}' => $this->MakeItemBindTypeText(),
				'{itemValue}' => $this->itemRecord['value'],
				'{itemLevel}' => $this->MakeItemLevelSimpleString(),
				'{itemLevelRaw}' => $this->itemRecord['level'],
				'{itemQualityRaw}' => $this->itemRecord['quality'],
				'{itemLevelBlock}' => $this->MakeItemLevelString(),
				'{itemQuality}' => GetEsoItemQualityText($this->itemRecord['quality']),
				'{itemRawDataList}' => $this->MakeItemRawDataList(),
				'{iconLink}' => $this->MakeItemIconImageLink(),
				'{itemLeftBlock}' => $this->MakeItemLeftBlock(),
				'{itemBar}' => $this->MakeItemBarLink(),
				'{itemEnchantBlock}' => $this->MakeItemEnchantBlock(),
				'{itemTraitBlock}' => $this->MakeItemTraitBlock(),
				'{itemSetBlock}' => $this->MakeItemSetBlock(),
				'{itemAbilityBlock}' => $this->MakeItemAbilityBlock(),
				'{itemTraitAbilityBlock}' => $this->MakeItemTraitAbilityBlock(),
				'{itemLeftBlockDisplay}' => $this->GetItemLeftBlockDisplay(),
				'{itemLevelBlockDisplay}' => $this->GetItemLevelBlockDisplay(),
				'{itemValueBlockDisplay}' => $this->GetItemValueBlockDisplay(),
				'{itemCraftedBlock}' => $this->MakeItemCraftedBlock(),
				'{itemDataJson}' => $this->GetItemDataJson(),
				'{itemSimilarBlock}' => $this->MakeSimilarItemBlock(),
				'{itemEnchantId1}' => $this->itemRecord['enchantId1'],
				'{itemEnchantIntLevel1}' => $this->itemRecord['enchantIntLevel1'],
				'{itemEnchantIntType1}' => $this->itemRecord['enchantIntType1'],
				'{showSummary}' => $this->showSummary ? 'summary' : '',
			);
		
		$output = strtr($this->htmlTemplate, $replacePairs);
		
		print ($output);
	}
	
	
	public function DumpItem()
	{
		foreach ($this->itemRecord as $key => $value)
		{
			print("$key = $value\n");
		}
	}
	
	
	public function ShowItem()
	{
		$this->OutputHtmlHeader();
		
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
		
		if ($this->outputType == "html")
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



