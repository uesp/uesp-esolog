<?php

/*
 * itemLinkImage.php -- by Dave Humphrey (dave@uesp.net), December 2014
 *
 * Outputs an image containing an ESO item and its data in the same/similar format
 * as the in-game item tooltips.
 *
 * TODO:
 *	- Center level/value properly when no left block data exists.
 *	- Better error image.
 *	- Fix text extents.
 *	- Fix text AA.
 *	- JPEG format (tested but has no alpha).
 *
 *
 * 
 */

// Database users, passwords and other secrets
require("/home/uesp/secrets/esolog.secrets");
require("esoCommon.php");
require("esoPotionData.php");


class CEsoItemLinkImage
{
	const ESOIL_ICON_PATH = "/home/uesp/www/eso/gameicons/";
	const ESOIL_IMAGE_CACHEPATH = "/home/uesp/esoItemImages/";
	const ESOIL_ICON_UNKNOWN = "unknown.png";
	const ESOIL_IMAGE_WIDTH = 400;
	const ESOIL_IMAGE_MAXHEIGHT = 1000;
	const ESOIL_REGULARFONT_FILE = "./resources/esofontregular-webfont.ttf";
	const ESOIL_BOLDFONT_FILE = "./resources/esofontbold-webfont.ttf";
	const ESOIL_LINEHEIGHT_FACTOR = 1.75;
	const ESOIL_LEVELBLOCK_CENTERXAMT = 70;
	
	const ESOIL_POTION_MAGICITEMID = 1;
	const ESOIL_POISON_MAGICITEMID = 2;
	const ESOIL_ENCHANT_ITEMID = 23662;
	
	const MAXSETINDEX = 12;
	
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
	
	static public $ESOIL_ERROR_SETITEM_DATA = array(
			"name" => "Unknown",
			"set" => "Unknown",
			"itemId" => 0,
			"internalSubtype" => 0,
			"internalLevel" => 0,
			"quality" => 0,
			"level" => "?",
			"value" => "?",
			"type" => 0,
			"bind" => 0,
			"description" => "",
			"icon" => "/esoui/art/icons/icon_missing.dds",
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
			"icon",
	);
	
	public $isError = false;
	public $inputParams = array();
	public $itemId = 0;
	public $itemLink = "";
	public $itemLevel = -66;		// 1-64
	public $itemQuality = 5;	// 0-5
	public $itemIntLevel = 50;	// 1-50
	public $itemIntType = 370;	// 1-40
	public $itemBound = -1;
	public $itemStyle = -1;
	public $itemCrafted = -1;
	public $itemCharges = -1;
	public $itemSetCount = -1;
	public $itemTrait = 0;
	public $itemPotionData = -1;
	public $itemStolen = -1;
	public $itemSet = "";
	public $transmuteTrait = 0;
	public $enchantId1 = -1;
	public $enchantIntLevel1 = -1;
	public $enchantIntType1 = -1;
	public $writData1 = 0;
	public $writData2 = 0;
	public $writData3 = 0;
	public $writData4 = 0;
	public $writData5 = 0;
	public $writData6 = 0;
	public $inputIntType = -1;
	public $inputIntLevel = -1;
	public $inputLevel = -1;
	public $inputQuality = -1;
	public $enchantFactor = 0;
	public $weaponTraitFactor = 0;
	public $version = "";
	public $useUpdate10Display = true;
	public $noCache = false;
	public $showSummary = false;
	public $itemRecord = array();
	public $itemSummary = array();
	public $db = null;
	public $enchantRecord1 = null;
	public $enchantRecord2 = null;
	
	public $image = null;
	public $background;
	public $black;
	public $invis;
	public $white;
	public $textColor;
	public $nameColor;
	public $qualityColors = array();
	public $printOptionsLargeWhite;
	public $printOptionsMedBeige;
	public $printOptionsMedWhite;
	public $printOptionsSmallWhite;
	public $printOptionsSmallBeige;
	public $printOptionsTinyBeige;
	public $printOptionsSmallInvis;
	
	public $bigFontSize = 18;
	public $medFontSize = 12;
	public $smallFontSize = 11;
	public $tinyFontSize = 10;
	
	public $topMargin = 32;
	public $borderMargin = 5;
	public $bigFontLineHeight = 22;
	public $medFontLineHeight = 18;
	public $smallFontLineHeight = 15;
	public $tinyFontLineHeight = 10;
	public $dataBlockMargin = 32;
	public $blockMargin = 14;
	public $borderWidth = 7;
	public $levelBlockXOffset = 0;
	
	
	public function __construct ()
	{
		//SetupUespSession();
		
		$this->SetInputParams();
		$this->ParseInputParams();
		
		/*
		$this->GetTextExtents(12, self::ESOIL_REGULARFONT_FILE, "e");
		$this->GetTextExtents(12, self::ESOIL_REGULARFONT_FILE, "p");
		$this->GetTextExtents(12, self::ESOIL_REGULARFONT_FILE, "y");
		$this->GetTextExtents(12, self::ESOIL_REGULARFONT_FILE, "g");
		$this->GetTextExtents(12, self::ESOIL_REGULARFONT_FILE, "j");
		$this->GetTextExtents(12, self::ESOIL_REGULARFONT_FILE, "q");
		$this->GetTextExtents(12, self::ESOIL_REGULARFONT_FILE, "1");
		$this->GetTextExtents(12, self::ESOIL_REGULARFONT_FILE, ".");
		$this->GetTextExtents(12, self::ESOIL_REGULARFONT_FILE, "b");
		
		$this->GetTextExtents(12, self::ESOIL_REGULARFONT_FILE, "ee");
		$this->GetTextExtents(12, self::ESOIL_REGULARFONT_FILE, "pp");
		$this->GetTextExtents(12, self::ESOIL_REGULARFONT_FILE, "yy");
		$this->GetTextExtents(12, self::ESOIL_REGULARFONT_FILE, "gg");
		$this->GetTextExtents(12, self::ESOIL_REGULARFONT_FILE, "jj");
		$this->GetTextExtents(12, self::ESOIL_REGULARFONT_FILE, "qq");
		$this->GetTextExtents(12, self::ESOIL_REGULARFONT_FILE, "11");
		$this->GetTextExtents(12, self::ESOIL_REGULARFONT_FILE, "..");
		$this->GetTextExtents(12, self::ESOIL_REGULARFONT_FILE, "bb");
		
		$this->GetTextExtents(12, self::ESOIL_REGULARFONT_FILE, "eee");
		$this->GetTextExtents(12, self::ESOIL_REGULARFONT_FILE, "ppp");
		$this->GetTextExtents(12, self::ESOIL_REGULARFONT_FILE, "yyy");
		$this->GetTextExtents(12, self::ESOIL_REGULARFONT_FILE, "ggg");
		$this->GetTextExtents(12, self::ESOIL_REGULARFONT_FILE, "jjj");
		$this->GetTextExtents(12, self::ESOIL_REGULARFONT_FILE, "qqq");
		$this->GetTextExtents(12, self::ESOIL_REGULARFONT_FILE, "111");
		$this->GetTextExtents(12, self::ESOIL_REGULARFONT_FILE, "...");
		$this->GetTextExtents(12, self::ESOIL_REGULARFONT_FILE, "bbb"); */
	}
	
	
	public function ReportError($errorMsg)
	{
		error_log($errorMsg);
		return false;
	}
	
	
	public function ParseItemLink($itemLink)
	{	
		//$result = preg_match('/\|H(?P<color>[A-Za-z0-9]*)\:item\:(?P<itemId>[0-9]*)\:(?P<subtype>[0-9]*)\:(?P<level>[0-9]*)\:(?P<enchantId1>[0-9]*)\:(?P<enchantSubtype1>[0-9]*)\:(?P<enchantLevel1>[0-9]*)\:(?P<enchantId2>[0-9]*)\:(?P<enchantSubtype2>[0-9]*)\:(?P<enchantLevel2>[0-9]*)\:(.*?)\:(?P<style>[0-9]*)\:(?P<crafted>[0-9]*)\:(?P<bound>[0-9]*)\:(?P<stolen>[0-9]*)\\:(?P<charges>[0-9]*)\:(?P<potionData>[0-9]*)\|h\[?(?P<name>[a-zA-Z0-9 %_\(\)\'\-]*)(?P<nameCode>.*?)\]?\|h/', $itemLink, $matches);
		//if (!$result) return false;
		
		$matches = ParseEsoItemLink($itemLink);
		if (!$matches) return false;
		
		$this->itemId = (int) $matches['itemId'];
		$this->itemIntLevel = (int) $matches['level'];
		$this->itemIntType = (int) $matches['subtype'];
		
		$this->itemStyle = (int) $matches['style'];
		$this->itemBound = (int) $matches['bound'];
		$this->itemCrafted = (int) $matches['crafted'];
		$this->itemCharges = (int) $matches['charges'];
		$this->itemPotionData = (int) $matches['potionData'];
		$this->itemStolen = (int) $matches['stolen'];
		
		$this->enchantId1 = (int) $matches['enchantId1'];
		$this->enchantIntLevel1 = (int) $matches['enchantLevel1'];
		$this->enchantIntType1 = (int) $matches['enchantSubtype1'];
		
		$this->writData1 = (int) $matches['writ1'];
		$this->writData2 = (int) $matches['writ2'];
		$this->writData3 = (int) $matches['writ3'];
		$this->writData4 = (int) $matches['writ4'];
		$this->writData5 = (int) $matches['writ5'];
		$this->writData6 = (int) $matches['writ6'];
		
		$this->transmuteTrait = $this->writData1;
		
		return true;
	}
	
	
	private function ParseInputParams ()
	{
		if (array_key_exists('summary', $this->inputParams)) $this->showSummary = true;
		
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
		
		if (array_key_exists('id', $this->inputParams)) $this->itemId = (int) $this->inputParams['id'];
		if (array_key_exists('itemid', $this->inputParams)) $this->itemId = (int) $this->inputParams['itemid'];
		
		if (array_key_exists('intlevel', $this->inputParams))
		{
			$this->itemLevel = -1;
			$this->itemQuality = -1;
			$this->itemIntLevel = (int) $this->inputParams['intlevel'];
			$this->itemIntType = 1;
		}
		
		if (array_key_exists('inttype', $this->inputParams))
		{
			$this->itemLevel = -1;
			$this->itemQuality = -1;
			$this->itemIntType = (int) $this->inputParams['inttype'];
			if ($this->itemIntLevel < 0) $this->itemIntLevel = 1;
		}
		
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
			else if ($this->showSummary)
			{
				$this->itemLevel = strtoupper($level);
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
		
		if (array_key_exists('setcount', $this->inputParams)) $this->itemSetCount = (int) $this->inputParams['setcount'];
		if (array_key_exists('nocache', $this->inputParams)) $this->noCache = true;
		if (array_key_exists('potiondata', $this->inputParams)) $this->itemPotionData = (int) $this->inputParams['potiondata'];;
		if (array_key_exists('stolen', $this->inputParams)) $this->itemStolen = (int) $this->inputParams['stolen'];;
		if (array_key_exists('style', $this->inputParams)) $this->itemStyle = (int) $this->inputParams['style'];
		if (array_key_exists('enchantfactor', $this->inputParams)) $this->enchantFactor = (int) $this->inputParams['enchantfactor'];
		if (array_key_exists('weapontraitfactor', $this->inputParams)) $this->weaponTraitFactor = (int) $this->inputParams['weapontraitfactor'];
		if (array_key_exists('trait', $this->inputParams)) $this->transmuteTrait = (int) $this->inputParams['trait'];
		
		if (array_key_exists('version', $this->inputParams)) $this->version = urldecode($this->inputParams['version']);
		if (array_key_exists('v', $this->inputParams)) $this->version = urldecode($this->inputParams['v']);
		
		if (array_key_exists('extradata', $this->inputParams))
		{
			$extraData = explode(":", $this->inputParams['extradata']);
			$this->writData1 = intval($extraData[0]);
			$this->writData2 = intval($extraData[1]);
			$this->writData3 = intval($extraData[2]);
			$this->writData4 = intval($extraData[3]);
			$this->writData5 = intval($extraData[4]);
			$this->writData6 = intval($extraData[5]);
		}
		
		if (array_key_exists('writ1', $this->inputParams)) $this->writData1 = (int) $this->inputParams['writ1'];
		if (array_key_exists('writ2', $this->inputParams)) $this->writData2 = (int) $this->inputParams['writ2'];
		if (array_key_exists('writ3', $this->inputParams)) $this->writData3 = (int) $this->inputParams['writ3'];
		if (array_key_exists('writ4', $this->inputParams)) $this->writData4 = (int) $this->inputParams['writ4'];
		if (array_key_exists('writ5', $this->inputParams)) $this->writData5 = (int) $this->inputParams['writ5'];
		if (array_key_exists('writ6', $this->inputParams)) $this->writData6 = (int) $this->inputParams['writ6'];
		if (array_key_exists('vouchers', $this->inputParams)) $this->itemPotionData = (int) $this->inputParams['vouchers'];
		
		if (IsEsoVersionAtLeast($this->version, 10)) $this->useUpdate10Display = true;
		
		if ($this->itemLevel < 0 && $this->itemQuality < 0)
		{
			if ($this->itemIntLevel < 0) $this->itemIntLevel = 1;
			if ($this->itemIntType  < 0) $this->itemIntType  = 1;
		}
		
		if (array_key_exists('set', $this->inputParams)) $this->itemSet = trim($this->inputParams['set']);
		
		$this->inputIntType = $this->itemIntType;
		$this->inputIntLevel = $this->itemIntLevel;
		$this->inputLevel = $this->itemLevel;
		$this->inputQuality = $this->itemQuality;
		
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
		
		UpdateEsoPageViews("itemLinkImageViews");
		
		return true;
	}
	
	
	private function LoadItemTransmuteTraitData()
	{
		$this->itemRecord['origTraitDesc'] = $this->itemRecord['traitDesc'];
		
		$intLevel = $this->itemRecord['internalLevel'];
		$intSubtype = $this->itemRecord['internalSubtype'];
		
			/* Special case for mythic items */
		if ($this->itemRecord['quality'] == 6) {
			$intLevel = 50;
			$intSubtype = 370;
		}
		
		$this->itemRecord['traitDesc'] = LoadEsoTraitDescription($this->itemTrait, $intLevel, $intSubtype, $this->itemRecord['equipType'], $this->db, $this->version);
	}
	
	
	private function LoadSetItemRecord()
	{
		$setTable = "setSummary" . $this->GetTableSuffix();
		$summaryTable = "minedItemSummary" . $this->GetTableSuffix();
		
		$safeSet = $this->db->real_escape_string($this->itemSet);
		
		$query = "SELECT * FROM $setTable WHERE setName='$safeSet' LIMIT 1;";
		$this->itemErrorDesc = "set='{$this->itemSet}'";
		$result = $this->db->query($query);
		if (!$result) return $this->ReportError("ERROR: Database query error! " . $this->db->error);
		
		$this->setItemData = $result->fetch_assoc();
		
		if (!$this->setItemData)
		{
			$safeSet = strtolower($this->itemSet);
			$safeSet = str_replace("'", "", $safeSet);
			$safeSet = str_replace(",", "", $safeSet);
			$safeSet = str_replace(" ", "-", $safeSet);
			$safeSet = $this->db->real_escape_string($safeSet);
			
			$query = "SELECT * FROM setSummary". $this->GetTableSuffix() ." WHERE indexName='$safeSet' LIMIT 1;";
			
			$result = $this->db->query($query);
			if (!$result) return $this->ReportError("ERROR: Database query error! " . $this->db->error);
			
			$this->setItemData = $result->fetch_assoc();
			
			if (!$this->setItemData)
			{
				$this->ReportError("ERROR: No set found matching '{$this->itemSet}'!");
				$this->setItemData = array();
				return false;
			}
		}
		
		$this->setItemData['name'] = $this->setItemData['setName'];
		$this->setItemData['quality'] = 5;
		
		$safeSet = $this->db->real_escape_string($this->setItemData['setName']);
		
		$query = "SELECT icon FROM $summaryTable WHERE setName='$safeSet' AND type=2 AND equipType=1 LIMIT 1;";
		$result = $this->db->query($query);
		if (!$result) return $this->ReportError("ERROR: Database query error! " . $this->db->error);
		
		if ($result->num_rows == 0)
		{
			$query = "SELECT icon FROM $summaryTable WHERE setName='$safeSet' AND type=1 AND (equipType=5 OR equipType=6) LIMIT 1;";
			$result = $this->db->query($query);
			if (!$result) return $this->ReportError("ERROR: Database query error! " . $this->db->error);
			
			if ($result->num_rows == 0)
			{
				$query = "SELECT icon FROM $summaryTable WHERE setName='$safeSet' AND (type=1 or type=2) LIMIT 1;";
				$result = $this->db->query($query);
				if (!$result) return $this->ReportError("ERROR: Database query error! " . $this->db->error);
			}
		}
		
		$iconRow = $result->fetch_assoc();
		
		if ($iconRow)
		{
			$this->setItemData['icon'] = $iconRow['icon'];
		}
		
		$this->setItemData['description'] = $this->setItemData['itemSlots'];
		$this->setItemData['type'] = $this->setItemData['gameId'];
		
		
		$this->itemRecord = $this->setItemData;
		
		return true;
	}
	
	
	private function LoadItemRecord()
	{
		if ($this->itemSet != "") return $this->LoadSetItemRecord();
		
		if ($this->itemId <= 0) return $this->ReportError("ERROR: Missing or invalid item ID specified (1-65000)!");
		$query = "";
		
		$minedTable = "minedItem" . $this->GetTableSuffix();
		$summaryTable = "minedItemSummary" . $this->GetTableSuffix();
		
		if ($this->itemLevel >= 1)
		{
			if ($this->itemLevel <= 0) return $this->ReportError("ERROR: Missing or invalid item Level specified (1-64)!");
			if ($this->itemQuality < 0) return $this->ReportError("ERROR: Missing or invalid item Quality specified (1-5)!");
			//$query = "SELECT * FROM minedItem".$this->GetTableSuffix()." WHERE itemId={$this->itemId} AND level={$this->itemLevel} AND quality={$this->itemQuality} LIMIT 1;";
				$query = "SELECT $summaryTable.*, $minedTable.* FROM $minedTable LEFT JOIN $summaryTable ON $summaryTable.itemId=$minedTable.itemId WHERE $minedTable.itemId='{$this->itemId}' AND $minedTable.level='{$this->itemLevel}' AND $minedTable.quality='{$this->itemQuality}' LIMIT 1;";
			$this->itemErrorDesc = "id={$this->itemId}, Level={$this->itemLevel}, Quality={$this->itemQuality}";
		}
		else
		{
			if ($this->itemIntType < 0) return $this->ReportError("ERROR: Missing or invalid item internal type specified (1-400)!");
			//$query = "SELECT * FROM minedItem".$this->GetTableSuffix()." WHERE itemId={$this->itemId} AND internalLevel={$this->itemIntLevel} AND internalSubtype={$this->itemIntType} LIMIT 1;";
			$query = "SELECT $summaryTable.*, $minedTable.* FROM $minedTable LEFT JOIN $summaryTable ON $summaryTable.itemId=$minedTable.itemId WHERE $minedTable.itemId='{$this->itemId}' AND $minedTable.internalLevel='{$this->itemIntLevel}' AND $minedTable.internalSubtype='{$this->itemIntType}' LIMIT 1;";
			$this->itemErrorDesc = "id={$this->itemId}, Internal Level={$this->itemIntLevel}, Internal Type={$this->itemIntType}";
		}
		
		$result = $this->db->query($query);
		if (!$result) return $this->ReportError("ERROR: Database query error! " . $this->db->error);
		
		if ($result->num_rows === 0)
		{
			if ($this->itemLevel <= 0 && $this->itemIntType == 1)
			{
				$this->itemIntType = 2;
				//$query = "SELECT * FROM minedItem".$this->GetTableSuffix()." WHERE itemId={$this->itemId} AND internalLevel={$this->itemIntLevel} AND internalSubtype={$this->itemIntType} LIMIT 1;";
				$query = "SELECT $summaryTable.*, $minedTable.* FROM $minedTable LEFT JOIN $summaryTable ON $summaryTable.itemId=$minedTable.itemId WHERE $minedTable.itemId='{$this->itemId}' AND $minedTable.internalLevel='{$this->itemIntLevel}' AND $minedTable.internalSubtype='{$this->itemIntType}' LIMIT 1;";
				$this->itemErrorDesc = "id={$this->itemId}, Internal Level={$this->itemIntLevel}, Internal Type={$this->itemIntType}";
				
				$result = $this->db->query($query);
				if (!$result) return $this->ReportError("ERROR: Database query error! " . $this->db->error);
			}
			else
			{
				$this->itemIntType = 1;
				$this->itemIntLevel = 1;
			
				//$query = "SELECT * FROM minedItem". $this->GetTableSuffix() ." WHERE itemId={$this->itemId} AND internalLevel={$this->itemIntLevel} AND internalSubtype={$this->itemIntType} LIMIT 1;";
				$query = "SELECT $summaryTable.*, $minedTable.* FROM $minedTable LEFT JOIN $summaryTable ON $summaryTable.itemId=$minedTable.itemId WHERE $minedTable.itemId='{$this->itemId}' AND $minedTable.internalLevel='{$this->itemIntLevel}' AND $minedTable.internalSubtype='{$this->itemIntType}' LIMIT 1;";
				$this->itemErrorDesc = "id={$this->itemId}, Internal Level={$this->itemIntLevel}, Internal Type={$this->itemIntType}";
			
				$result = $this->db->query($query);
				if (!$result) return $this->ReportError("ERROR: Database query error! " . $this->db->error);
			}
			
			if ($result->num_rows === 0) return $this->ReportError("ERROR: No item found matching {$this->itemErrorDesc}!");
		}
		
		$result->data_seek(0);
		$row = $result->fetch_assoc();
		if (!$row) $this->ReportError("ERROR: No item found matching {$this->itemErrorDesc}!");
		
		if ($this->itemLevel <= 0) $this->itemLevel = (int) $row['level'];
		if ($this->itemQuality < 0) $this->itemQuality = (int) $row['quality'];
		
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
		
		$this->itemIntLevel =  $row['internalLevel'];
		$this->itemIntType = $row['internalSubtype'];
		$this->itemLevel = $row['level'];
		$this->itemId = $row['itemId'];
		$this->itemQuality = $row['quality'];
		$this->itemLink = $row['link'];
		
		$row['name'] = preg_replace("#\|.*#", "", $row['name']);
		
		if ($row['weaponType'] == 14) $row['armorRating'] += $this->extraArmor;
		
		$row['traitAbilityDescArray'] = array();
		$row['traitCooldownArray'] = array();
		
		if ($row['traitAbilityDesc'] != "")
		{
			$row['traitAbilityDescArray'][] = $row['traitAbilityDesc'];
			$row['traitCooldownArray'][] = $row['traitCooldown'];
		}
		
		$this->itemRecord = $row;
		
		if ($this->itemRecord['type'] == 7)  $this->LoadItemPotionData();
		if ($this->itemRecord['type'] == 30) $this->LoadItemPoisonData();
		
		if ($this->itemRecord['type'] == 60)
		{
			$this->transmuteTrait = 0;
			$this->CreateMasterWritData();
		}
		
		$this->itemTrait = $this->itemRecord['trait'];
		
		if ($this->itemRecord['type'] == 2 || $this->itemRecord['type'] == 1)
		{
			if ($this->transmuteTrait > 0) 
			{
				$this->itemTrait = $this->transmuteTrait;
				
				$this->itemRecord['origTrait'] = $this->itemRecord['trait'];
				$this->itemRecord['origTraitDesc'] = $this->itemRecord['traitDesc'];
				$this->itemRecord['origArmorRating'] = $this->itemRecord['armorRating'];
				$this->itemRecord['origWeaponPower'] = $this->itemRecord['armorWeaponPower'];
				$this->itemRecord['origEnchantDesc'] = $this->itemRecord['enchantDesc'];
					
				if ($this->itemRecord['trait'] == 13)		/* Original Reinforced */
				{
					$factor = 1;
					$result = preg_match("#by (?:\|c[0-9a-fA-F]{6})?([0-9.]+)(?:\|r)?%#", $this->itemRecord['origTraitDesc'], $matches);
					
					if ($result)
					{
						$factor = 1 + floatval($matches[1])/100;
						$this->itemRecord['armorRating'] = round($this->itemRecord['armorRating'] / $factor);
					}
				}
				else if ($this->itemRecord['trait'] == 25)	/* Original armor nirnhoned */
				{
					$amount = 0;
					$result = preg_match("#by ([0-9.]+)#", $this->itemRecord['origTraitDesc'], $matches);
					
					if ($result)
					{
						$amount = intval($matches[1]);
						$this->itemRecord['armorRating'] -= $amount;
					}
				}
				else if ($this->itemRecord['trait'] == 26)	/* Original weapon nirnhoned */
				{
					$factor = 0;
					$result = preg_match("#by (?:\|c[0-9a-fA-F]{6})?([0-9.]+)(?:\|r)?%#", $this->itemRecord['origTraitDesc'], $matches);
				
					if ($result)
					{
						$factor = 1 + floatval($matches[1])/100;
						$this->itemRecord['weaponPower'] = round($this->itemRecord['weaponPower'] / $factor);
					}
				}
				
				$this->LoadItemTransmuteTraitData();
				
				if ($this->transmuteTrait == 13)		/* Transmuted Reinforced */
				{
					$factor = 1;
					$result = preg_match("#by ([0-9.]+)\%#", $this->itemRecord['traitDesc'], $matches);
					
					if ($result)
					{
						$factor = 1 + floatval($matches[1])/100;
						$this->itemRecord['armorRating'] = floor($this->itemRecord['armorRating'] * $factor);
					}
				}
				else if ($this->transmuteTrait == 25)	/* Transmuted armor nirnhoned */
				{
					$amount = 0;
					$result = preg_match("#by ([0-9.]+)#", $this->itemRecord['traitDesc'], $matches);
					
					if ($result)
					{
						$amount = intval($matches[1]);
						$this->itemRecord['armorRating'] += $amount;
					}
				}
				else if ($this->transmuteTrait == 26)	/* Transmuted weapon nirnhoned */
				{
					$factor = 0;
					$result = preg_match("#by (?:\|c[0-9a-fA-F]{6})?([0-9.]+)(?:\|r)?%#", $this->itemRecord['traitDesc'], $matches);
					
					if ($result)
					{
						$factor = 1 + floatval($matches[1])/100;
						$this->itemRecord['weaponPower'] = round($this->itemRecord['weaponPower'] * $factor);
					}
				}
			}
			
			if ($this->weaponTraitFactor > 0)
			{
				
				if ($this->itemRecord['trait'] == 26 && $this->transmuteTrait <= 0)
				{
					$factor = 0;
					$result = preg_match("#by (?:\|c[0-9a-fA-F]{6})?([0-9.]+)(?:\|r)?%#", $this->itemRecord['traitDesc'], $matches);
					
					if ($result)
					{
						$factor = floatval($matches[1])/100;
						$newFactor = (1 + $factor * (1 + $this->weaponTraitFactor));
						$oldWeaponPower = round($this->itemRecord['weaponPower'] / (1 + $factor));
						$this->itemRecord['weaponPower'] = round($this->itemRecord['weaponPower'] * $newFactor);
					}
				}
				else if ($this->transmuteTrait == 26)
				{
					$factor = 0;
					$result = preg_match("#by (?:\|c[0-9a-fA-F]{6})?([0-9.]+)(?:\|r)?%#", $this->itemRecord['traitDesc'], $matches);
					
					if ($result)
					{
						$factor = floatval($matches[1])/100;
						$newFactor = (1 + $factor * (1 + $this->weaponTraitFactor));
						$oldWeaponPower = round($this->itemRecord['weaponPower'] / (1 + $factor));
						$this->itemRecord['weaponPower'] = round($this->itemRecord['weaponPower'] * $newFactor);
					}
				}
			}
		}
		else
		{
			$this->transmuteTrait = 0;
		}
		
		$this->LoadEnchantMaxCharges();
		
		return true;
	}
	
	
	public function CreateMasterWritData()
	{
		$text = CreateEsoMasterWritText($this->db, $this->itemRecord['name'], $this->writData1, $this->writData2, $this->writData3,
				$this->writData4, $this->writData5, $this->writData6, $this->itemPotionData);
		
		$this->itemRecord['abilityName'] = '';
		$this->itemRecord['abilityDesc'] = $text;
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
		if ($this->itemTrait == 2) $maxCharges *= $this->itemRecord['quality']*0.25 + 2;
		
		$this->itemRecord['maxCharges'] = $maxCharges;
		
		$this->itemRecord['maxCharges'] = $row['maxCharges'];
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
	
	
	private function LoadItemPoisonData()
	{
		if ($this->itemPotionData <= 0) return true;
		
		$potionData = intval($this->itemPotionData);
		$potionEffect1 = $potionData & 255;
		$potionEffect2 = ($potionData >> 8) & 255;
		$potionEffect3 = ($potionData >> 16) & 127;
		
		$this->LoadItemPotionDataEffect($potionEffect1, false);
		$this->LoadItemPotionDataEffect($potionEffect2, false);
		$this->LoadItemPotionDataEffect($potionEffect3, false);
		
		ksort($this->itemRecord['traitAbilityDescArray']);
		ksort($this->itemRecord['traitCooldownArray']);
		return true;
	}
	
	
	private function LoadItemPotionDataEffect($effectIndex, $loadPotion = true)
	{
		$effectIndex = intval($effectIndex);
		if ($effectIndex <= 0 || $effectIndex > 127) return true;
		
		if ($loadPotion)
			$itemId = self::ESOIL_POTION_MAGICITEMID;
		else
			$itemId = self::ESOIL_POISON_MAGICITEMID;
		
		if ($this->inputIntLevel >= 0 && $this->inputIntType >= 0)
		{
			$intlevel = $this->inputIntLevel;
			$subtype = $this->inputIntType;
			$query = "SELECT traitAbilityDesc FROM minedItem{$this->GetTableSuffix()} WHERE itemId=$itemId AND internalLevel=$intlevel AND internalSubtype=$subtype AND potionData=$effectIndex LIMIT 1;";
		}
		else if ($this->itemIntLevel >= 0 && $this->itemIntType >= 0)
		{
			$intlevel = $this->itemIntLevel;
			$subtype = $this->itemIntType;
			$query = "SELECT traitAbilityDesc FROM minedItem{$this->GetTableSuffix()} WHERE itemId=$itemId AND internalLevel=$intlevel AND internalSubtype=$subtype AND potionData=$effectIndex LIMIT 1;";
		}
		else if ($this->itemLevel >= 1)
		{
			$level = $this->itemLevel;
			$quality = $this->itemQuality;
			$query = "SELECT traitAbilityDesc FROM minedItem{$this->GetTableSuffix()} WHERE itemId=$itemId AND level=$level AND quality=$quality AND potionData=$effectIndex LIMIT 1;";
		}
		else
		{
			$intlevel = 1;
			$subtype = 1;
			$query = "SELECT traitAbilityDesc FROM minedItem{$this->GetTableSuffix()} WHERE itemId=$itemId AND internalLevel=$intlevel AND internalSubtype=$subtype AND potionData=$effectIndex LIMIT 1;";
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
	
	
	private function GetTableSuffix()
	{
		return GetEsoItemTableSuffix($this->version);
	}
	
	
	private function LoadItemErrorData()
	{
		$this->isError = true;
		
		if ($this->itemSet != "")
		{
			$this->itemRecord = self::$ESOIL_ERROR_SETITEM_DATA;
			
			$this->itemRecord['name'] = "Unknown Set";
			$this->itemRecord['setName'] = $this->itemSet;
			$this->itemRecord['setMaxEquipCount'] = '0';
			$this->itemRecord['setBonusCount'] = '0';
			//$this->itemRecord['description'] = "No set found matching '{$this->itemSet}'!";
			
			return;
		}
		
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
				$this->itemRecord[$field] = '1-V16';
			else
				$this->itemRecord[$field] = $value;
		}
	
		return true;
	}
	
	
	public function MergeItemSummaryAll()
	{
		if ($this->itemSummary == null || count($this->itemSummary) == 0) return false;
	
		foreach ($this->itemSummary as $field => $value)
		{
			if ($field == "level" && $value == "")
				$this->itemRecord[$field] = '1-CP160';
			else
				$this->itemRecord[$field] = $value;
		}
	
		$this->itemRecord['setBonusCount'] = 0;
		$this->itemRecord['setMaxEquipCount'] = 0;
		$numSetItems = 0;
	
		$result = preg_match("#\(([0-9]+) items\)#", $this->itemRecord['setBonusDesc1'], $matches);
		if ($result) $numSetItems = max($numSetItems, $matches[1]);
	
		$result = preg_match("#\(([0-9]+) items\)#", $this->itemRecord['setBonusDesc2'], $matches);
		if ($result) $numSetItems = max($numSetItems, $matches[1]);
	
		$result = preg_match("#\(([0-9]+) items\)#", $this->itemRecord['setBonusDesc3'], $matches);
		if ($result) $numSetItems = max($numSetItems, $matches[1]);
	
		$result = preg_match("#\(([0-9]+) items\)#", $this->itemRecord['setBonusDesc4'], $matches);
		if ($result) $numSetItems = max($numSetItems, $matches[1]);
	
		$result = preg_match("#\(([0-9]+) items\)#", $this->itemRecord['setBonusDesc5'], $matches);
		if ($result) $numSetItems = max($numSetItems, $matches[1]);
	
		$this->itemRecord['setMaxEquipCount'] = $numSetItems;
	
		if ($this->itemRecord['setBonusDesc1'] != "") $this->itemRecord['setBonusCount'] = 1;
		if ($this->itemRecord['setBonusDesc2'] != "") $this->itemRecord['setBonusCount'] = 2;
		if ($this->itemRecord['setBonusDesc3'] != "") $this->itemRecord['setBonusCount'] = 3;
		if ($this->itemRecord['setBonusDesc4'] != "") $this->itemRecord['setBonusCount'] = 4;
		if ($this->itemRecord['setBonusDesc5'] != "") $this->itemRecord['setBonusCount'] = 5;
		
		if ($this->itemQuality > 0) $this->itemRecord['quality'] = $this->itemQuality;
		if ($this->itemLevel > 0) $this->itemRecord['level'] = $this->itemLevel;
		
		return true;
	}
	
	
	private function LoadItemSummaryTransmuteTraitData()
	{
		$this->itemSummary['origTraitDesc'] = $this->itemSummary['traitDesc'];
		$this->itemSummary['traitDesc'] = LoadEsoTraitSummaryDescription($this->itemTrait, $this->itemSummary['equipType'], $this->db, $this->version);
	}
	
	
	private function LoadItemSummaryData()
	{
		if ($this->itemId <= 0) return $this->ReportError("ERROR: Missing or invalid item ID specified (1-65000)!");
		$query = "SELECT * FROM minedItemSummary".$this->GetTableSuffix()." WHERE itemId={$this->itemId};";
	
		$result = $this->db->query($query);
		if (!$result) return $this->ReportError("ERROR: Database query error! " . $this->db->error);
	
		$this->itemSummary = $result->fetch_assoc();
		if (!$this->itemSummary) $this->ReportError("ERROR: No item summary found matching ID {$this->itemId}!");
		
		$this->itemTrait = $this->itemSummary['trait'];
		
		if ($this->itemSummary['type'] == 2 || $this->itemSummary['type'] == 1)
		{
			if ($this->transmuteTrait > 0) 
			{
				$this->itemTrait = $this->transmuteTrait;
				
				/* TODO: Transmute trait modification? */
				
				$this->LoadItemSummaryTransmuteTraitData();
			}
		}
		else
		{
			$this->transmuteTrait = 0;
		}
		
		if ($this->itemSummary['type'] == 7)  $this->LoadItemPotionData();
		if ($this->itemSummary['type'] == 30) $this->LoadItemPoisonData();
		
		if ($this->itemSummary['type'] == 60) $this->CreateMasterWritData();
			
		return true;
	}
	
	
	private function LoadEnchantRecords()
	{
		if ($this->enchantId1 > 0 && $this->enchantIntLevel1 > 0 && $this->enchantIntType1 > 0)
		{
			$item = LoadEsoMinedItemExact($this->db, $this->enchantId1, $this->enchantIntLevel1, $this->enchantIntType1, $this->GetTableSuffix());
			if ($item) $this->enchantRecord1 = $item;
		}
		
		return true;
	}
	
	
	private function OutputHtmlHeader()
	{
		ob_start("ob_gzhandler");
		header("Expires: 0");
		header("Pragma: no-cache");
		header("Cache-Control: no-cache, no-store, must-revalidate");
		header("Pragma: no-cache");
		header("content-type: image/png");
		header("Access-Control-Allow-Origin: *");
	}
	
	
	public function FormatPrintData(&$printData, $lineData)
	{
		$lineBreak = array_key_exists('br', $lineData);
		
		$newText = preg_replace("#\|t([0-9%]*):([0-9%]*):([^\|]*)\|trank #s", "VR ", $lineData['text']);
		$newText = preg_replace("#\|t([0-9%]*):([0-9%]*):champion_icon_[0-9]+\.dds\|t#s", "CP", $newText);
		                         //"|t24:24:champion_icon_24.dds|t"
		$newText = preg_replace("#\|t([0-9%]*):([0-9%]*):([^\|]*)\|t#s", "", $newText);
		
		$formats = preg_split("#(\|c[0-9a-fA-F]{6}[^\|]+\|r)|(Adds [0-9\-\.]+)|(by [0-9\-\.]+)|(for [0-9\-\.]+)#s", $newText, -1, PREG_SPLIT_DELIM_CAPTURE);
		$numFmts = count($formats);
		
		foreach ($formats as $key => $value)
		{
			$newData = $lineData;
			
			if ($value[0] == '|' && preg_match("#\|c(?<color>[0-9a-fA-F]{6})(?<value>[^\|]+)\|r#s", $value, $matches))
			{
				$newData['text'] = " " .$matches['value'];
				$newData['color'] = hexdec($matches['color']);
			}
			elseif ($value[0] == 'A' && preg_match("|Adds ([0-9\-\.]+)|s", $value, $matches))
			{
				unset($newData['br']);
				$newData['text'] = " Adds ";
				$extents = $this->GetTextExtents($newData['size'], $newData['font'], $newData['text']);
				$newData['width']  = $extents[0];
				$newData['height'] = $extents[1];
				$printData[] = $newData;
				
				$newData = $lineData;
				$newData['text'] = $matches[1];
				$newData['color'] = 0xffffff;
			}
			elseif ($value[0] == 'b' && preg_match("|by ([0-9\-\.]+)|s", $value, $matches))
			{
				unset($newData['br']);
				$newData['text'] = "by ";
				$extents = $this->GetTextExtents($newData['size'], $newData['font'], $newData['text']);
				$newData['width']  = $extents[0];
				$newData['height'] = $extents[1];
				$printData[] = $newData;
				
				$newData = $lineData;
				$newData['text'] = " " . $matches[1];
				$newData['color'] = 0xffffff;
			}
			elseif ($value[0] == 'f' && preg_match("|for ([0-9\-\.]+)|s", $value, $matches))
			{
				unset($newData['br']);
				$newData['text'] = "for ";
				$extents = $this->GetTextExtents($newData['size'], $newData['font'], $newData['text']);
				$newData['width']  = $extents[0];
				$newData['height'] = $extents[1];
				$printData[] = $newData;
					
				$newData = $lineData;
				$newData['text'] = $matches[1] . " ";
				$newData['color'] = 0xffffff;
			}
			else
			{
				$newData['text'] = $value;
			}
	
			if ($newData['text'] == "")
			{
				$extents = $this->GetTextExtents($newData['size'], $newData['font'], "Aj");
				$newData['width']  = 1;
				$newData['height'] = $extents[1];
			}
			else 
			{
				$extents = $this->GetTextExtents($newData['size'], $newData['font'], $newData['text']);
				$newData['width']  = $extents[0];
				$newData['height'] = $extents[1];
			}
			
			unset($newData['br']);
			
			$printData[] = $newData;
		}
		
		if ($lineBreak) $printData[count($printData) - 1]['br'] = true;
	}
	
	
	public function ClearFormatPrintData(&$printData, $lineData)
	{
		$newData = $lineData;
		
		$newText = preg_replace("#\|c([0-9a-fA-F]{6})([a-zA-Z \-0-9\.\t\n]+)\|r#s", "$2", $lineData['text']);
		$newText = preg_replace("#\|t([0-9%]*):([0-9%]*):([^\|]*)\|trank #s", "VR ", $newText);
		$newText = preg_replace("#\|t([0-9%]*):([0-9%]*):([^\|]*)\|t#s", "", $newText);
		$newData['text'] = $newText;
		
		$extents = $this->GetTextExtents($lineData['size'], $newData['font'], $newData['text']);
		$newData['width']  = $extents[0];
		$newData['height'] = $extents[1];
				
		$printData[] = $newData;
	}
	
	
	public function AddPrintDataEx (&$printData, $text, $baseOptions, $options = array())
	{
		$optionsData = array_merge($baseOptions, $options);
		$lines = preg_split("/\\n/", $text);
		//$lines = str_split("\n", $text);
		$lineCount = 0;
		$dataStartIndex = count($printData);
		
			// Split by existing line breaks
		foreach ($lines as $key => $line)
		{
			$lineCount += 1;
			$newData = $optionsData;
			$newData['br'] = true;
			$newData['text'] = $line;
			
			if ($line == "")
			{
				$extents = $this->GetTextExtents($newData['size'], $newData['font'], "Aj");
				$newData['width'] = 1;
			}
			else
			{
				$extents = $this->GetTextExtents($newData['size'], $newData['font'], $line);
				$newData['width']  = $extents[0];
			}
			
			$newData['height'] = $extents[1];
			
			if (array_key_exists('format', $options) && $options['format'] === true)
				$this->FormatPrintData($printData, $newData);
			else if (array_key_exists('clearformat', $options) && $options['clearformat'] === true)
				$this->ClearFormatPrintData($printData, $newData);
			else
				$printData[] = $newData;
		}
		
			// Break long lines
		$lineWidth = 0;
		$maxWidth = self::ESOIL_IMAGE_WIDTH - 20;
		$lineStartIndex = $dataStartIndex;
		
		for ($i = $dataStartIndex; $i < count($printData); $i += 1)
		{
			$data = $printData[$i];
			$lineBreak = $data['br'];
			$origLineWidth = $lineWidth;
			$lineWidth += $data['width'];
			
			if ($lineWidth > $maxWidth)
			{
				$words = explode(' ', $data['text']);
				$width = $origLineWidth;
				$breakIndex = 0;
				$leftWords = "";
				
				$wordCount = count($words);
				
				foreach ($words as $key => $word)
				{
					$extents = $this->GetTextExtents($data['size'], $data['font'], $word . " ");
					$boxWidth = $extents[0];
					if ($width + $boxWidth > $maxWidth) break;
					$width += $boxWidth;
					
					$leftWords .= $word . " ";
					$breakIndex += 1;
				}
				
				if ($breakIndex < count($words))
				{
					$rightWords = substr($printData[$i]['text'], strlen($leftWords));
					$extents = $this->GetTextExtents($data['size'], $data['font'], $leftWords);
					
					$printData[$i]['width']  = $extents[0];
					$printData[$i]['height'] = $extents[1];
					$printData[$i]['text'] = $leftWords;
					$printData[$i]['br'] = true;
					
					$newData = $printData[$i];
					$extents = $this->GetTextExtents($data['size'], $data['font'], $rightWords);
					$newData['width']  = $extents[0];
					$newData['height'] = $extents[1];
					$newData['text'] = $rightWords;
					
					if ($lineBreak)
						$newData['br'] = $lineBreak;
					else
						unset($newData['br']);
					
					array_splice($printData, $i + 1, 0, array($newData));
					//$printData[$i + 1] = $newData;
					
					$lineWidth = 0;
					$lineStartIndex = $i + 1;
				}
			}
			
			if (array_key_exists('br', $data))
			{
				$lineWidth = 0;
				$lineStartIndex = $i + 1; 
			}
		}		
		
		return $lineCount;
	}
	
	
	public function AddPrintData (&$printData, $text, $baseOptions, $options = array())
	{
		if (array_key_exists('lineBreak', $options)) return $this->AddPrintDataEx($printData, $text, $baseOptions, $options);
		if (array_key_exists('format', $options)) return $this->AddPrintDataEx($printData, $text, $baseOptions, $options);
		
		$newData = array_merge($baseOptions, $options);
		$newData['text'] = $text;
		
		$extents = $this->GetTextExtents($newData['size'], $newData['font'], $text);
		$newData['width']  = $extents[0];
		$newData['height'] = $extents[1];
		if ($newData['height'] == 0) $newData['height'] = $newData['size'];
		
		$printData[] = $newData;
		return 1;
	}
	
	
	public function PrintDataTextElement ($image, $printData, $x, $y)
	{
		$font = $printData['font'];
		$size = $printData['size'];
		$color = $printData['color'];
		$text = $printData['text'];
		$width = $printData['width'];
		$height = $printData['height'];
		
		$this->PrintTextAA($image, $size, $x, $y + $printData['lineHeight'], $color, $font, $text);
		
		return array($printData['width'], $printData['height']);
	}
	
	
	public function PrintDataTextComputeSizes (&$printData)
	{
		$i = 0;
		
		while ($i < count($printData))
		{
			$lineWidth = 0;
			$lineHeight = 0;
			$lineStartIndex = $i;
				
			while ($i < count($printData))
			{
				$data = $printData[$i];
				$lineWidth += $data['width'];
				if ($data['height'] > $lineHeight) $lineHeight = $data['height'];
				$i += 1;
				if (array_key_exists('br', $data)) break;
			}
				
			for ($j = $lineStartIndex; $j < $i; $j += 1)
			{
				$printData[$j]['lineWidth'] = $lineWidth;
				$printData[$j]['lineHeight'] = $lineHeight;
			}
		}
		
	}
	
	/*
	 * imagettfbbox() seems to return incorrect widths for characters with tails below the baseline
	 * (jpgqy).
	 * 
	 * Character  Width  RealWidth (pixels)
	 *    e			7		7
	 *    b			7		7
	 *    g			3		7		4
	 *    j			0		5		5
	 *    p			3		7		4
	 *    q			3		7		4
	 *    y			3		7		4
	 *    1			4		4
	 *    .			3		3
	 *    ee		14		14
	 *    bb		14		14
	 *    gg		10		14		4
	 *    jj		3		14		9
	 *    pp		10		14		4
	 *    qq		10		14		4
	 *    yy		10		14		4
	 *    eee		21		21
	 *    bbb		21		21
	 *    ggg		17		21		4
	 *    jjj		6		21		15
	 *    ppp		17		21		4
	 *    qqq		17		21		4
	 *    yyy		17		21		4
	 */
	
	public function GetTextExtents($size, $font, $text)
	{
		$box = imagettfbbox($size, 0, $font, $text);
		
		$width = $box[4] - $box[1];
		$height = $box[0] - $box[5];
		$widthAdj1 = 0;
		$widthAdj2 = 0;
		$widthAdj3 = 0;
		$widthAdj4 = 0;
		
		for ($i = 0; $i < strlen($text); $i += 1)
		{
			switch ($text[$i])
			{
				case 'p':
					$widthAdj1 = intval($size/4);
					break;
				case 'g':
					$widthAdj2 = intval($size/4);
					break;
				case 'q':
					$widthAdj3 = intval($size/4);
					break;
				case 'y':
					$widthAdj4 = intval($size/4);
					break;
				case 'j':
					$width += intval($size/3);
					break;
				case '1':
					//$width += intval($size/8);
					break;
			}
		}
		
		$width += $widthAdj1 + $widthAdj2 + $widthAdj3 + $widthAdj4;
		//error_log("GetTextExtents($size, '$font', '$text') = $width, $height");
		return array($width, $height);
	}
	
	
	public function PrintDataText ($image, $printData, $x, $y, $alignment)
	{
		$this->PrintDataTextComputeSizes($printData);
		$i = 0;
		$deltaY = 0;
		
		//error_log("PrintDataText(image, " . count($printData) . ", $x, $y, $alignment)");
		
		while ($i < count($printData))
		{
			$data = $printData[$i];
			
			if ($alignment == 'right')
				$startX = $x - $data['lineWidth'];
			elseif ($alignment == 'center')
				$startX = $x - $data['lineWidth']/2;
			else
				$startX = $x;
			
			while ($i < count($printData))
			{
				$data = $printData[$i];
				
				//error_log("     Data text = \"" . $data['text'] . '"');
				//error_log("          Size = " . $data['width'] . "," . $data['height']);
				//error_log("          Line = " . $data['lineWidth'] . "," . $data['lineHeight']);
				
				$extents = $this->PrintDataTextElement($image, $data, $startX, $y + $deltaY);
				$startX += $extents[0];
				$i += 1;
				if (array_key_exists('br', $data)) break;
			}
			
			$deltaY += $data['lineHeight'] * self::ESOIL_LINEHEIGHT_FACTOR;
		}
		
		return $deltaY;
	}
	
	
	public function PrintTextAA($image, $fontSize, $x, $y, $color, $font, $text)
	{
		$colorAA = imagecolorallocatealpha($image, ($color >> 16) & 0xff, ($color >> 8) & 0xff,  $color & 0xff, 80);
		$delta = 1;
		
		imagettftext($image, $fontSize, 0, $x+$delta, $y, $colorAA, $font, $text);
		imagettftext($image, $fontSize, 0, $x-$delta, $y, $colorAA, $font, $text);
		
		imagettftext($image, $fontSize, 0, $x, $y, $color, $font, $text);
	}
	
	
	public function PrintText($image, $fontSize, $x, $y, $color, $font, $text)
	{
		imagettftext($image, $fontSize, 0, $x, $y, $color, $font, $text);
	}
	
	
	public function PrintCenterText($image, $fontSize, $y, $color, $font, $text)
	{
		$extents = $this->GetTextExtents($fontSize, $font, $text);
		$x = (self::ESOIL_IMAGE_WIDTH - $extents[0]) / 2;
		$this->PrintTextAA($image, $fontSize, $x, $y, $color, $font, $text);
	}
	
	
	public function PrintRightText($image, $fontSize, $x, $y, $color, $font, $text)
	{
		$extents = $this->GetTextExtents($fontSize, $font, $text);
		$newX = $x - $extents[0];
		$this->PrintTextAA($image, $fontSize, $newX, $y, $color, $font, $text);
	}
	
	
	public function OutputCenterImage($image, $filename, $y)
	{
		$hrImage = imagecreatefrompng($filename);
		if ($hrImage == null) return false;
		imagealphablending($hrImage, true);
		imagesavealpha($hrImage, true);
		
		$imageWidth = imagesx($hrImage);
		$imageHeight = imagesy($hrImage);
		$x = (self::ESOIL_IMAGE_WIDTH - $imageWidth) / 2;
		
		imagecopy($image, $hrImage, $x, $y, 0, 0, $imageWidth, $imageHeight);
		return array($imageWidth, $imageHeight);
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
		if ($this->itemSet != "") 
		{
			$type = intval($this->itemRecord['type']);
			if ($type > 0) return $type;
			return "";
		}
		
		if ($this->itemRecord['specialType'] > 0) return GetEsoItemSpecialTypeText($this->itemRecord['specialType']);
		
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
		$craftType = $this->itemRecord['craftType'];
		$specialType = $this->itemRecord['specialType'];
		
		if ($type <= 0) 
		{
			return "";
		}
		else if ($type == 2) //armor
		{
			if ($this->itemRecord['armorType'] > 0) return "" . GetEsoItemArmorTypeText($this->itemRecord['armorType']) . "";
			return "";
		}
		elseif ($type == 1) //weapon
		{
			return "" . GetEsoItemWeaponTypeText($this->itemRecord['weaponType']) . "";
		}
		elseif ($type == 29) // Recipe
		{
			if ($craftType == null || $craftType == "" || $craftType <= 0) return "";
			return "" . GetEsoItemCraftTypeText($craftType) . "";
		}
		elseif ($type == 61) // Furniture
		{
			$furnCate = $this->itemRecord['furnCategory'];
			$splitCats = explode(":", explode("(", $furnCate)[0]);
			$type1 = trim($splitCats[0]);
			$type2 = trim($splitCats[1]);
			
			//$type1 = $this->itemRecord['setBonusDesc4'];
			//$type2 = $this->itemRecord['setBonusDesc5'];
			
			if ($type1 != "" && $type != "") return "" . $type1 . " / " . $type2 . "";
			return "" . $type1 . $type2 . "";
		}
		else if ($craftType > 0)
		{
			return "" . GetEsoItemCraftTypeText($craftType) . "";
		}
	
		return "";
	}
	
	
	private function MakeItemIconImageFilename()
	{
		$icon = $this->itemRecord['icon'];
		if ($icon == null || $icon == "") $icon = self::ESOIL_ICON_UNKNOWN;
	
		$icon = preg_replace('/dds$/', 'png', $icon);
		$icon = preg_replace('/^\//', '', $icon);
	
		$iconLink = self::ESOIL_ICON_PATH . $icon;
		return $iconLink;
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
	
	
	private function OutputItemStolenBlock($image)
	{
		if ($this->itemStolen <= 0) return 0;
		$stolenImage = imagecreatefrompng("./resources/stolenitem.png");
		
		if ($stolenImage)
		{
			$x = imagesx($image) - $this->borderWidth - 20;
			$y = $this->topMargin + $this->borderWidth + 2;
			$size = 20;
			imagecopyresized($image, $stolenImage, $x, $y, 0, 0, $size, $size, imagesx($stolenImage),imagesy($stolenImage));
		}
		
		return $size;
	}
	
	
	public function OutputItemLevelBlock($image, $y)
	{
		if ($this->useUpdate10Display) return $this->OutputItemNewLevelBlock($image, $y);
		
		$level = $this->itemRecord['level'];
		$levelImageWidth = 0;
		$levelImage = null;
		
		if (!$this->showSummary && $level > 50)
		{
			$imageFile = "./resources/eso_item_veteranicon.png";
			$label = "RANK ";
			$levelText = $level - 50;
			
			$levelImage = imagecreatefrompng($imageFile);
			
			if ($levelImage != null)
			{
				imageantialias($levelImage, true);
				imagealphablending($levelImage, true);
				imagesavealpha($levelImage, true);
				$levelImageWidth = imagesx($levelImage) + 2;
			}
		}
		else
		{
			$imageFile = "";
			$label = "LEVEL ";
			$levelText = $level;
		}
		
		$extents1 = $this->GetTextExtents($this->medFontSize, self::ESOIL_BOLDFONT_FILE, $label);
		$extents2 = $this->GetTextExtents($this->bigFontSize, self::ESOIL_BOLDFONT_FILE, $levelText);
		$totalWidth = $levelImageWidth + $extents1[0] + $extents2[0];
		$x = (self::ESOIL_IMAGE_WIDTH - $totalWidth ) / 2 + $this->levelBlockXOffset;
		
		if (!$this->GetItemLeftBlockDisplay()) $x -= self::ESOIL_LEVELBLOCK_CENTERXAMT;
		
		if ($levelImage)
		{
			imagecopy($image, $levelImage, $x, $y, 0, 0, imagesx($levelImage), imagesy($levelImage));
			$x += $levelImageWidth;
		}
		
		$this->PrintTextAA($image, $this->medFontSize, $x, $y + $extents2[1] + 4, $this->textColor, self::ESOIL_BOLDFONT_FILE, $label);
		$x += $extents1[0];
		return $this->PrintTextAA($image, $this->bigFontSize, $x, $y + $extents2[1] + 4, $this->white, self::ESOIL_BOLDFONT_FILE, $levelText);
	}
	
	
	public function OutputItemNewLevelBlock($image, $y)
	{
		if ($this->showSummary)
		{
				// Is output in OutputItemRightBlock()?
			return 0;
			
			$level = $this->itemRecord['level'];
			
			$printData = array();
			$this->AddPrintData($printData, "LEVEL ", $this->printOptionsMedBeige);
			$this->AddPrintData($printData, $level, $this->printOptionsLargeWhite);
			
			$x = self::ESOIL_IMAGE_WIDTH/2;
			//if (!$this->GetItemLeftBlockDisplay()) $x -= self::ESOIL_LEVELBLOCK_CENTERXAMT;
			
			return $this->PrintDataText($image, $printData, $x, $y + 4, 'center');
		}
		
		$level = intval($this->itemRecord['level']);
		if ($level <= 0) return 0;
		if ($level > 50) return 0;
		if ($level > 50) $level = 50;
		
		$printData = array();
		$this->AddPrintData($printData, "LEVEL ", $this->printOptionsMedBeige);
		$this->AddPrintData($printData, $level, $this->printOptionsLargeWhite);
		
		$x = self::ESOIL_IMAGE_WIDTH/2;
		if (!$this->GetItemLeftBlockDisplay()) $x -= self::ESOIL_LEVELBLOCK_CENTERXAMT;
		
		return $this->PrintDataText($image, $printData, $x, $y + 4, 'center');
	}
	
	
	private function GetHasItemBlockDisplay()
	{
	
		switch ($this->itemRecord['type'])
		{
			case 61:	// Furnishing
			case 29:	// Recipe
			case 59:	// Dye Stamp
			case 60:	// Master Writ
			case 44:	// Style Material
			case 31:	// Reagent
			case 10:	// Ingredient
			case 52:	// Rune
			case 53:
			case 41:	// Blacksmith Temper
			case 43:
			case 34:
			case 39:
			case 18:
			case 55:
			case 57:
			case 47: 	// PVP Repair
			case 36:	// BS Material
			case 35:	// BS Raw Material
			case 40:
			case 54:
			case 16:
			case 8:
			case 17:
			case 6:
			case 19:
			case 48:
			case 5:
			case 46:
			case 42:
			case 38:
			case 37:
			case 0:
			case -1:
			case 58:	// Poison Base
			case 33:	// Potion Base
			case 45:
			case 51:
				return false;
				
			case 2:
				$display = true;
				break;
				
			case 1:
				$display = true;
				break;
				
			case 12: 	// Drink
			case 4: 	// Food
			case 26:	// Glyph
			case 3:
			case 20:
			case 21:
			case 30:	// Poison
			case 51:
			case 7:		// Potion
			case 9:		// Repair Kit
				return true;
		}
		
		if ($display) return true;

		$level = $this->itemRecord['level'];
		if ($level <= 0) return false;
		
		return true;
	}
	
	
	private function GetItemLeftBlockDisplay()
	{
	
		switch ($this->itemRecord['type'])
		{
			case 2:
				$equipType = $this->itemRecord['equipType'];
				if ($equipType == 2 || $equipType == 12) return false;
				return true;
			case 1:
				return true;
		}
	
		return false;
	}
	
	
	public function OutputItemRightBlock($image, $y)
	{
		if (!$this->useUpdate10Display) return $this->OutputItemOldValueBlock($image, $y);
		
		$x = self::ESOIL_IMAGE_WIDTH - $this->dataBlockMargin;
		$x = $x + $this->levelBlockXOffset;
		if (!$this->GetItemLeftBlockDisplay()) $x -= self::ESOIL_LEVELBLOCK_CENTERXAMT;
				
		$level = $this->itemRecord['level'];
		if ($level == "CP160") $level = 66;
		
		if (!is_numeric($level))
		{
			if ($level == "1-CP160") $level = "1 - CP160";
			
			$printData = array();
			$this->AddPrintData($printData, "LEVEL ", $this->printOptionsMedBeige);
			$this->AddPrintData($printData, $level, $this->printOptionsLargeWhite);
			
			$x = self::ESOIL_IMAGE_WIDTH/2;
			return $this->PrintDataText($image, $printData, $x, $y + 4, 'center');
		}
		
		if ($level <= 50) return 0;
		$cp = ($level - 50) * 10;
		
		$imageFile = "./resources/champion_icon.png";
		$cpImage = imagecreatefrompng($imageFile);
		
		$printData = array();
		$this->AddPrintData($printData, "CP ", $this->printOptionsMedBeige);
		$this->AddPrintData($printData, $cp, $this->printOptionsLargeWhite);
		
		$extents1 = $this->GetTextExtents($this->medFontSize, self::ESOIL_BOLDFONT_FILE, "CP ");
		$extents2 = $this->GetTextExtents($this->bigFontSize, self::ESOIL_BOLDFONT_FILE, $cp);
		$totalWidth = $extents1[0] + $extents2[0];
		
		if ($cpImage != null)
		{
			imageantialias($cpImage, true);
			imagealphablending($cpImage, true);
			imagesavealpha($cpImage, true);
			imagecopyresampled($image, $cpImage, $x - $totalWidth - 26, $y + 2, 0, 0, 24, 24, imagesx($cpImage), imagesy($cpImage));
		}
		
		return $this->PrintDataText($image, $printData, $x, $y + 4, 'right');
	}
	
	
	public function OutputItemOldValueBlock($image, $y)
	{
		$value = $this->itemRecord['value'];
		if ($value <= 0) return 0;
		
		$printData = array();
		$this->AddPrintData($printData, "VALUE ", $this->printOptionsMedBeige);
		$this->AddPrintData($printData, $value, $this->printOptionsLargeWhite);
		
		if ($this->showSummary)
			$x = self::ESOIL_IMAGE_WIDTH - 10;
		else
			$x = self::ESOIL_IMAGE_WIDTH - $this->dataBlockMargin;
		
		$x = $x + $this->levelBlockXOffset;
		
		if (!$this->GetItemLeftBlockDisplay()) $x -= self::ESOIL_LEVELBLOCK_CENTERXAMT;
		
		return $this->PrintDataText($image, $printData, $x, $y + 4, 'right');
	}
	
	
	private function OutputItemNewValueBlock($image, $y)
	{
		$value = $this->itemRecord['value'];
		if ($value <= 0) return 0;
		
		$label = "$value";
		
		$imageFile = "./resources/currency_gold_32.png";
		$goldImage = imagecreatefrompng($imageFile);
			
		if ($goldImage != null)
		{
			imageantialias($goldImage, true);
			imagealphablending($goldImage, true);
			imagesavealpha($goldImage, true);
			imagecopyresampled($image, $goldImage, self::ESOIL_IMAGE_WIDTH/2 + 4, $y - 2, 0, 0, 16, 16, imagesx($goldImage), imagesy($goldImage));
		}
		else
		{
			$label = "$value gp";
		}
		
		$printData = array();
		$this->AddPrintData($printData, $label, $this->printOptionsSmallWhite, array('br' => true));
		
		return $this->PrintDataText($image, $printData, self::ESOIL_IMAGE_WIDTH/2, $y, 'right') + $this->blockMargin;
	}
	
	
	public function OutputItemLeftBlock($image, $y)
	{
		$equipType = $this->itemRecord['equipType'];
		
		switch ($this->itemRecord['type'])
		{
			case 1:
				if ($this->itemRecord['equipType'] == 7) // shield
				{
					$label = "ARMOR ";
					$valueText = $this->itemRecord['armorRating'];
				}
				else {	//weapon
					$label = "DAMAGE ";
					$valueText = $this->itemRecord['weaponPower'];
				}
				break;
			case 2:
						// ring/neck
				if ($equipType == 2 || $equipType == 12) 
				{
					$this->levelBlockXOffset = -64;
					return;
				}
				
				$label = "ARMOR ";
				$valueText = $this->itemRecord['armorRating'];
				break;
			default:
				return;
		}
		
		if ($this->showSummary)
			$x = 10;
		else
			$x = $this->dataBlockMargin;
		
		$printData = array();
		$this->AddPrintData($printData, $label, $this->printOptionsMedBeige);
		$this->AddPrintData($printData, $valueText, $this->printOptionsLargeWhite);
		$this->PrintDataText($image, $printData, $x, $y + 4, 'left');
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
			return true;
		}
		else if ($type == 2)
		{
			return true;
		}
	
		return false;
	}
	
	
	private function OutputItemBar($image, $y)
	{
		if (!$this->HasItemBar()) return 0;
		
		$type = $this->itemRecord['type'];
		$coverImageSize = 0;
		$coverImageHeight = 0;
		
		$maxCharges = $this->itemRecord['maxCharges'];
		
		if ($maxCharges <= 0 && ($this->enchantRecord1 != null || $this->enchantRecord2 != null))
		{
				// TODO: This is a rough Estimate
			$maxCharges = $this->itemRecord['weaponPower'] / 2;
			if ($this->itemTrait == 2) $maxCharges *= $this->itemRecord['quality']*0.25 + 2;
		}
		
		if ($type == 1 && $maxCharges > 0 && $this->itemRecord['weaponType'] != 14)
		{
			$coverImageHeight = 5;
			$charges = $this->itemCharges;
			if ($charges < 0) $charges = $maxCharges;
			$coverImageSize = ($maxCharges - $charges) / $maxCharges * 112;
			if ($coverImageSize < 0) $coverImageSize = 0;
			if ($coverImageSize > 112) $coverImageSize = 112;
			
			$itemBarFile = "resources/eso_item_chargebar.png";
		}
		elseif ($type == 1 || $type == 2)
		{
			$coverImageHeight = 4;
			$condition = $this->itemCharges/100;
			if ($condition < 0) $condition = 100;
			$coverImageSize = (100 - $condition) * 112 / 100;
			if ($coverImageSize < 0) $coverImageSize = 0;
			if ($coverImageSize > 112) $coverImageSize = 112;
			
			$itemBarFile = "resources/eso_item_conditionbar.png";
		}
		else
		{
			return 0;
		}
		
		$result = $this->OutputCenterImage($image, $itemBarFile, $y);
		if (!$result) return 0;
		
		if ($coverImageSize > 0)
		{
			$x = (self::ESOIL_IMAGE_WIDTH - $result[0])/2 + 3;
			imagefilledrectangle($image, $x, $y+1, $x+$coverImageSize, $y+1+$coverImageHeight, 0);
			$x = (self::ESOIL_IMAGE_WIDTH + $result[0])/2 - 4;
			imagefilledrectangle($image, $x-$coverImageSize, $y+1, $x, $y+1+$coverImageHeight, 0);
		}
		
		return 7 + $this->blockMargin;
	}
	
	
	private function ModifyEnchantDesc($desc, $isDefaultEnchant)
	{
		static $WEAPON_MATCHES = array
		(
				"#(Deals \|c[0-9a-fA-F]{6})([0-9]+)(\|r)#i",
				"#(restores \|c[0-9a-fA-F]{6})([0-9]+)(\|r)i#",
				"#(by \|c[0-9a-fA-F]{6})([0-9]+)(\|r)#i",
				"#(Grants a \|c[0-9a-fA-F]{6})([0-9]+)(\|r)#i",
		);
		
		$newDesc = $desc;
		$trait = $this->itemTrait;
		$traitDesc = FormatRemoveEsoItemDescriptionText($this->itemRecord['traitDesc']);
		
		$armorFactor = 1 + $this->enchantFactor;
		$weaponFactor = 1 + $this->enchantFactor;
		
			/* Infused */
		if (!$isDefaultEnchant && ($trait == 16 || $trait == 4 || $trait == 33))
		{
			$result = preg_match("#effect(?:iveness|) by ([0-9]\.?[0-9]*)#", $traitDesc, $matches);
			$traitValue = 0;
			
			if ($result && $matches[1])
			{
				$armorTraitValue = 1 + ((float) $matches[1]) / 100;
				$weaponTraitValue = 1 + ((float) $matches[1]) / 100 * (1 + $this->weaponTraitFactor);
				
				if ($trait == 16) $armorFactor *= $armorTraitValue;
				if ($trait == 33) $armorFactor *= $armorTraitValue;
				if ($trait ==  4) $weaponFactor *= $weaponTraitValue;
			}
		}
		else if ($isDefaultEnchant && $this->transmuteTrait > 0)
		{
			$origTraitDesc = FormatRemoveEsoItemDescriptionText($this->itemRecord['origTraitDesc']);
			
			if ($this->transmuteTrait != 16 && $this->itemRecord['origTrait'] == 16)
			{
				$result = preg_match("#effect by ([0-9]\.?[0-9]*)#", $origTraitDesc, $matches);
				
				if ($result && $matches[1])
				{
					$traitValue = 1 + ((float) $matches[1]) / 100;
					$armorFactor /= $traitValue;
				}
			}
			elseif ($this->transmuteTrait != 33 && $this->itemRecord['origTrait'] == 33)
			{
				$result = preg_match("#effectiveness by ([0-9]\.?[0-9]*)#", $origTraitDesc, $matches);
				
				if ($result && $matches[1])
				{
					$traitValue = 1 + ((float) $matches[1]) / 100;
					$armorFactor /= $traitValue;
				}
			}
			elseif ($this->transmuteTrait != 4 && $this->itemRecord['origTrait'] == 4)
			{
				$result = preg_match("#effect by ([0-9]\.?[0-9]*)#", $origTraitDesc, $matches);
				
				if ($result && $matches[1])
				{
					$traitValue = 1 + ((float) $matches[1]) / 100;
					$weaponFactor /= $traitValue;
				}
			}
			
			if ($this->transmuteTrait == 16 && $this->itemRecord['origTrait'] != 16)
			{
				$result = preg_match("#effect by ([0-9]\.?[0-9]*)#", $traitDesc, $matches);
				
				if ($result && $matches[1])
				{
					$traitValue = 1 + ((float) $matches[1]) / 100;
					$armorFactor *= $traitValue;
				}
			}
			elseif ($this->transmuteTrait == 33 && $this->itemRecord['origTrait'] != 33)
			{
				$result = preg_match("#effectiveness by ([0-9]\.?[0-9]*)#", $traitDesc, $matches);
				
				if ($result && $matches[1])
				{
					$traitValue = 1 + ((float) $matches[1]) / 100;
					$armorFactor *= $traitValue;
				}
			}
			elseif ($this->transmuteTrait == 4 && $this->itemRecord['origTrait'] != 4)
			{
				$result = preg_match("#effect by ([0-9]\.?[0-9]*)#", $traitDesc, $matches);
				
				if ($result && $matches[1])
				{
					$traitValue = 1 + ((float) $matches[1]) / 100 * (1 + $this->weaponTraitFactor);
					$weaponFactor *= $traitValue;
				}
			}
				
			//error_log("Transmute Infused $armorFactor, $newDesc");
			//error_log("Transmute Infused $weaponFactor, $newDesc");
		}
	
		$armorType = $this->itemRecord['armorType'];
		$equipType = $this->itemRecord['equipType'];
		$weaponType = $this->itemRecord['weaponType'];
		$itemType = $this->itemRecord['type'];
		
			/* Half-enchants on 1H weapons, update 21 */
		if (!$isDefaultEnchant && ($weaponType == 1 || $weaponType == 2 || $weaponType == 3 || $weaponType == 11))
		{
			$weaponFactor *= 0.5;
		}
		
			/* Modify enchants of small armor pieces */
		if (!$isDefaultEnchant && $armorType > 0 && ($equipType == 4 || $equipType == 8 || $equipType == 10 || $equipType == 13))
		{
			$armorFactor *= 0.405;
		}
	
		if (($itemType == 2 || $weaponType == 14) && $armorFactor != 1)
		{
			$newDesc = preg_replace_callback("#((?:Adds \|c[0-9a-fA-F]{6})|(?: by \|c[0-9a-fA-F]{6}))([0-9\.]+)((?:\|r))#i",
						
					function ($matches) use ($armorFactor) {
						$result = floor($matches[2] * $armorFactor);
						return $matches[1] . $result . $matches[3];
					},
	
					$newDesc);
	
		}
		else if (( $weaponType > 0 && $weaponType != 14) && $weaponFactor != 1)
		{
			foreach ($WEAPON_MATCHES as $match)
			{
				$newDesc = preg_replace_callback($match,
							
						function ($matches) use ($weaponFactor) {
							$result = floor($matches[2] * $weaponFactor);
							return $matches[1] . $result . $matches[3];
						},
	
						$newDesc);
			}
		}
	
		//$newDesc = $this->FormatDescriptionText($newDesc);
		return $newDesc;
	}
	
	
	private function OutputItemEnchantBlock($image, $y)
	{
		$printData = array();
		
			/* TODO: Temp fix for potions showing enchantments/sets */
		if ($this->itemRecord['type'] == 7) return 0;
		
		if ($this->enchantRecord1 != null)
		{
			$enchantName = strtoupper($this->enchantRecord1['enchantName']);
			$enchantDesc = $this->ModifyEnchantDesc($this->enchantRecord1['enchantDesc'], false);
				
			if ($enchantDesc != "")
			{
				if ($enchantName != "") $this->AddPrintData($printData, $enchantName, $this->printOptionsSmallWhite, array('br' => true));
				$this->AddPrintData($printData, $enchantDesc, $this->printOptionsSmallBeige, array('format' => true, 'lineBreak' => true));
			}
		}
		
		if ($this->enchantRecord2 != null)
		{
			$enchantName = strtoupper($this->enchantRecord2['enchantName']);
			$enchantDesc = $this->ModifyEnchantDesc($this->enchantRecord2['enchantDesc'], false);
		
			if ($enchantDesc != "")
			{
				$this->AddPrintData($printData, " ", $this->printOptionsTinyBeige, array('br' => true));
				if ($enchantName != "") $this->AddPrintData($printData, $enchantName, $this->printOptionsSmallWhite, array('br' => true));
				$this->AddPrintData($printData, $enchantDesc, $this->printOptionsSmallBeige, array('format' => true, 'lineBreak' => true));
			}
		}
		
		if ($this->enchantRecord1 == null && $this->enchantRecord2 == null)
		{
			$enchantName = strtoupper($this->itemRecord['enchantName']);
			$enchantDesc = $this->ModifyEnchantDesc($this->itemRecord['enchantDesc'], true);
			
			if ($enchantDesc != "")
			{
				if ($enchantName != "") $this->AddPrintData($printData, $enchantName, $this->printOptionsSmallWhite, array('br' => true));
				$this->AddPrintData($printData, $enchantDesc, $this->printOptionsSmallBeige, array('format' => true, 'lineBreak' => true));
			}
		}
		
		return $this->PrintDataText($image, $printData, self::ESOIL_IMAGE_WIDTH/2, $y, 'center') + $this->blockMargin;
	}
	
	
	private function OutputItemAbilityBlock($image, $y)
	{
		if ($this->itemRecord['type'] == 60)	// Master Writs
		{
			$ability = "";
			$abilityDesc = $this->itemRecord['abilityDesc'];
			if ($abilityDesc == "") return 0;
		}
		else if ($this->itemRecord['type'] == 29)	// Recipes
		{
			$ability = strtoupper($this->itemRecord['abilityName']);
			$abilityDesc = $this->itemRecord['abilityDesc'];
			if ($abilityDesc == "") return 0;
		}
		else if ($this->itemRecord['type'] == 33)	//Potion Base
		{
			$level = $this->MakeLevelTooltipText($this->itemRecord['level']);
			$craft = $this->itemRecord['craftType'];
			$skillRank = $this->itemRecord['craftSkillRank'];
			$abilityDesc = "Makes a $level potion.";
				
			if ($craft > 0 && $skillRank > 0)
			{
				$craft = GetEsoItemCraftRequireText($craft);
				$skillRank = intval($skillRank);
				$abilityDesc .= "\n\n|c00ff00Requires $craft $skillRank.|r";
			}
		}
		else if ($this->itemRecord['type'] == 58)	//Poison Base
		{
			$level = $this->MakeLevelTooltipText($this->itemRecord['level']);
			$craft = $this->itemRecord['craftType'];
			$skillRank = $this->itemRecord['craftSkillRank'];
			$abilityDesc = "Makes a $level poison.";
				
			if ($craft > 0 && $skillRank > 0)
			{
				$craft = GetEsoItemCraftRequireText($craft);
				$skillRank = intval($skillRank);
				$abilityDesc .= "\n\n|c00ff00Requires $craft $skillRank.|r";
			}
		}
		else if (($this->itemRecord['type'] == 30 || $this->itemRecord['type'] == 7) && $this->itemPotionData > 0)
		{
			return 0;
		}
		else
		{
			$ability = strtoupper($this->itemRecord['abilityName']);
			$abilityDesc = $this->itemRecord['abilityDesc'];
			if ($abilityDesc == "") return 0;
			
			$cooldown = ((int) $this->itemRecord['abilityCooldown']) / 1000;
			if ($cooldown > 0) $abilityDesc .= " (" . $cooldown . " second cooldown)";
		}
		
		$printData = array();
		if ($ability != "") $this->AddPrintData($printData, $ability, $this->printOptionsSmallWhite, array('br' => true, 'format' => true));
		$this->AddPrintData($printData, $abilityDesc, $this->printOptionsSmallBeige, array('format' => true, 'lineBreak' => true, 'br' => true));
		
		return $this->PrintDataText($image, $printData, self::ESOIL_IMAGE_WIDTH/2, $y, 'center') + $this->blockMargin;
	}
	
	
	private function OutputItemTraitBlock($image, $y)
	{
		$trait = $this->itemTrait;
		if ($trait <= 0) return 0;
		
		$printData = array();
		$this->AddPrintData($printData, strtoupper(GetEsoItemTraitText($trait, $this->version)), $this->printOptionsSmallWhite, array('br' => true));
		
		$traitDesc = $this->itemRecord['traitDesc'];
		
		if ($this->weaponTraitFactor > 0 && $this->itemRecord['type'] == 1 && $this->itemRecord['weaponType'] != 14 && $this->itemRecord['trait'] != 9 && $this->itemRecord['trait'] != 10)
		{
			$traitDesc = preg_replace_callback('/by \|cffffff([0-9.]+)\|r/', function($matches) {
				$traitValue = floatval($matches[1]);
				$newTraitValue = $traitValue * (1 + $this->weaponTraitFactor);
				return "by |cffffff$newTraitValue|r";
			}, $traitDesc, 1);
			
			$traitDesc = preg_replace_callback('/a \|cffffff([0-9.]+)\|r% chance/', function($matches) {
				$traitValue = floatval($matches[1]);
				$newTraitValue = $traitValue * (1 + $this->weaponTraitFactor);
				return "a |cffffff$newTraitValue|r% chance";
			}, $traitDesc, 1);
		}
		
		$this->AddPrintData($printData, $traitDesc, $this->printOptionsSmallBeige, array('format' => true, 'lineBreak' => true));
		$deltaY = $this->PrintDataText($image, $printData, self::ESOIL_IMAGE_WIDTH/2, $y, 'center') + $this->blockMargin;
		
		if ($this->transmuteTrait > 0)
		{
			$lineWidth = 100;
			if ($printData[0] != null && $printData[0]['width'] > 0) $lineWidth = $printData[0]['lineWidth'];
		
			$imageX = self::ESOIL_IMAGE_WIDTH/2 - $lineWidth/2 - 36 - 22;
			
			$transmuteImage = imagecreatefrompng("./resources/transmute_icon.png");
			
			if ($transmuteImage)
			{
				$size = 32;
				imagecopyresized($image, $transmuteImage, $imageX, $y - 12, 0, 0, $size, $size, imagesx($transmuteImage), imagesy($transmuteImage));
			}
		}
				
		return $deltaY;
	}
	
	
	private function OutputItemTraitAbilityBlock($image, $y)
	{
		if ($this->itemRecord['traitAbilityDescArray'] == null)
		{
			$abilityDesc = strtoupper($this->itemRecord['traitAbilityDesc']);
			if ($abilityDesc == "") return 0;
			//$cooldown = ((int) $this->itemRecord['traitCooldown']) / 1000;
			//$abilityDesc .= " ($cooldown second cooldown)";
			
			$printData = array();
			$this->AddPrintData($printData, $abilityDesc, $this->printOptionsSmallBeige, array('format' => true, 'lineBreak' => true));
			return $this->PrintDataText($image, $printData, self::ESOIL_IMAGE_WIDTH/2, $y, 'center') + $this->blockMargin;
		}
		
		$abilityDesc = array();
		$printData = array();
		$cooldownDesc = "";
		$isFirst = true;
				
		foreach ($this->itemRecord['traitAbilityDescArray'] as $index => $desc)
		{
			$abilityDesc[] = $desc;
			//$cooldown = round($this->itemRecord['traitCooldownArray'][$index] / 1000);
			//$cooldownDesc = " ($cooldown second cooldown)";

			if (!$isFirst) $this->AddPrintData($printData, "=", $this->printOptionsSmallInvis, array('format' => false, 'lineBreak' => true));
			$this->AddPrintData($printData, $desc, $this->printOptionsSmallBeige, array('format' => true, 'lineBreak' => true));
			
			$isFirst = false;
		}
		
		if (count($abilityDesc) == 0) return 0;
		//$this->AddPrintData($printData, $cooldownDesc, $this->printOptionsSmallBeige, array('format' => true, 'lineBreak' => true));
	
		return $this->PrintDataText($image, $printData, self::ESOIL_IMAGE_WIDTH/2, $y, 'center') + $this->blockMargin;
	}
	
	
	private function OutputItemSetBlock($image, $y)
	{
			/* TODO: Temp fix for potions showing enchantments/sets */
		if ($this->itemRecord['type'] == 7) return 0;
		
		$setName = strtoupper($this->itemRecord['setName']);
		if ($setName == "") return 0;
		$printData = array();
		
		$setMaxEquipCount = $this->itemRecord['setMaxEquipCount'];
		$setBonusCount = (int) $this->itemRecord['setBonusCount'];
		$setLabel = "PART OF THE $setName SET ($setMaxEquipCount/$setMaxEquipCount ITEMS)";
		$this->AddPrintData($printData, $setLabel, $this->printOptionsSmallWhite, array('br' => true));
		
		for ($i = 1; $i <= self::MAXSETINDEX; $i += 1)
		{
			$setCount = $this->itemRecord['setBonusCount' . $i];
			$setDesc = $this->itemRecord['setBonusDesc' . $i];
			if ($setDesc == null || $setDesc == "") continue;
			
			if ($this->itemSetCount >= 0 && $setCount > $this->itemSetCount)
			{
				$this->AddPrintData($printData, $setDesc, $this->printOptionsSmallBeige, array('br' => true, 'clearformat' => true, 'lineBreak' => true, 'color' => $this->darkGray));
			}
			else
			{
				$this->AddPrintData($printData, $setDesc, $this->printOptionsSmallBeige, array('br' => true, 'format' => true, 'lineBreak' => true));
			}
		}
		
		return $this->PrintDataText($image, $printData, self::ESOIL_IMAGE_WIDTH/2, $y, 'center') + $this->blockMargin;
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
		if ($glyphMinLevel <= 0) $glyphMinLevel = $this->itemRecord['level'];
		
		$minDesc = $this->MakeLevelTooltipText($glyphMinLevel);
		$desc = "Used to create glyphs of $minDesc and higher.";
		return $desc;
	}
	
	
	public function MakeLevelTooltipText($level)
	{
		$desc = "";
	
		if ($level <= 50)
		{
			$minDesc = "level $level";
		}
		else
		{
			$cp = ($level - 50) * 10;
			$minDesc = "CP$cp";
		}
	
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
			$minDesc = "VR$glyphMinLevel";
		}
		
		if ($glyphMaxLevel < 50)
		{
			$maxDesc = "level $glyphMaxLevel";
		}
		else
		{
			$glyphMaxLevel = $glyphMaxLevel - 50;
			if ($this->CheckVersionLessThan(9)) $glyphMaxLevel += 1;
			$maxDesc = "VR$glyphMaxLevel";
		}
	
		if ($minDesc == $maxDesc)
			$desc = "Used to create glyphs of $minDesc.";
		else
			$desc = "Used to create glyphs of $minDesc to $maxDesc.";
		
		return $desc;
	}
	
	
	private function OutputItemDescription($image, $y)
	{
		$desc = $this->itemRecord['description'];
		$matDesc = $this->itemRecord['materialLevelDesc'];
		
		if ($this->itemRecord['type'] == 51)
		{
			$desc = $this->MakePotencyItemDescription();
		}
		else if ($this->itemRecord['type'] == 44)
		{
			$style = GetEsoItemStyleText($this->itemRecord['style']);
			$desc = "An ingredient for crafting in the |cffffff$style|r style.";
		}
		else if ($this->itemRecord['type'] == 58)
		{
			$desc = "";
		}
		else if ($this->itemRecord['type'] == 33)
		{
			$desc = "";
		}
		else if ($matDesc != "") 
		{
			$desc = $matDesc;
		}
		
		if ($desc == "") return 0;
		
		$printData = array();
		$this->AddPrintData($printData, $desc, $this->printOptionsTinyBeige, array('br' => true, 'format' => true, 'lineBreak' => true));
		return $this->PrintDataText($image, $printData, $this->borderMargin + 10, $y, 'left') + $this->blockMargin;
	}
	
	
	private function MakeItemStyle()
	{
		if ($this->itemStyle > 0) return GetEsoItemStyleText($this->itemStyle);
		if ($this->itemRecord['style'] > 0) return GetEsoItemStyleText($this->itemRecord['style']);
		return "";
	}
	
	
	private function OutputItemStyle($image, $y)
	{
		$style = $this->MakeItemStyle();
		if ($style == "") return 0;
		
		$type = $this->itemRecord['type'];
		if ($type != 1 && $type != 2) return;
		
		$this->PrintText($image, $this->smallFontSize, $this->borderMargin + 10, $y, $this->textColor, self::ESOIL_BOLDFONT_FILE, $style);
	}
	
	
	private function OutputItemCraftedBlock($image, $y)
	{
		if ($this->itemCrafted <= 0) return 0;
		
		$printData = array();
		$this->AddPrintData($printData, "Created by: Someone", $this->printOptionsSmallWhite, array('br' => true));
		return $this->PrintDataText($image, $printData, self::ESOIL_IMAGE_WIDTH/2, $y, 'center') + $this->blockMargin;
	}
	
		
	private function OutputItemTagsBlock($image, $y)
	{
		$result = 0;
		
		if ($this->itemRecord['tags'] != "") 
		{
			$printData = array();
			
			if ($this->itemRecord['type'] == 61)
				$this->AddPrintData($printData, "Furnishing Behavior", $this->printOptionsSmallBeige, array('br' => true));
			else
				$this->AddPrintData($printData, "Treasure Type:", $this->printOptionsSmallBeige, array('br' => true));
			
			$this->AddPrintData($printData, $this->itemRecord['tags'], $this->printOptionsMedWhite, array('br' => true));
			
			$result = $this->PrintDataText($image, $printData, self::ESOIL_IMAGE_WIDTH/2, $y, 'center') + $this->blockMargin;
		}
		
		if ($this->itemRecord['type'] == 61 && $this->itemRecord['furnLimitType'] >= 0)
		{
			$printData = array();
			
			$this->AddPrintData($printData, "Furnishing Limit Type", $this->printOptionsSmallBeige, array('br' => true));
			$furnType = GetEsoFurnLimitTypeText($this->itemRecord['furnLimitType']);
			$this->AddPrintData($printData, $furnType, $this->printOptionsMedWhite, array('br' => true));
			
			$result += $this->PrintDataText($image, $printData, self::ESOIL_IMAGE_WIDTH/2, $y + $result, 'center') + $this->blockMargin;
		}
		
		return $result;
	}
	
	
	private function OutputItemDyeStampBlock($image, $y)
	{
		$origy = $y;
		
		$dyeData = $this->itemRecord['dyeData'];
		if ($this->itemRecord['type'] != 59 || $dyeData == null || $dyeData == "") return 0;
	
		$parsedDyeData = explode(",", $dyeData);
		$dyeStampId = $parsedDyeData[0];
		$dye1 = $parsedDyeData[1];
		$dye2 = $parsedDyeData[2];
		$dye3 = $parsedDyeData[3];
	
		$printData = array();
		$text = "Dyes all the channels of your currently equipped\n|cffffffCostume|r and |cffffffHat|r.";
		$this->AddPrintData($printData, $text, $this->printOptionsSmallBeige, array('br' => true, 'format' => true, 'lineBreak' => true));
		
		$y += $this->PrintDataText($image, $printData, self::ESOIL_IMAGE_WIDTH/2, $y, 'center') + $this->blockMargin;
		$y += 2;
	
		$y += $this->OutputItemDyeStampSubBlock($dye1, $image, $y);
		$y += $this->OutputItemDyeStampSubBlock($dye2, $image, $y);
		$y += $this->OutputItemDyeStampSubBlock($dye3, $image, $y);
		
		$y += 5;
	
		return $y - $origy;
	}
	
	
	private function OutputItemDyeStampSubBlock($rawDyeData, $image, $y)
	{
		$origy = $y;
		if ($rawDyeData == null) return 0;
	
		$result = preg_match("/(?P<dyeId>[0-9]+){(?P<dyeName>[^}]*)}{(?P<dyeColor>[0-9a-zA-Z]*)}/", $rawDyeData, $matches);
		if (!$result) return 0;
	
		$dyeId = $matches['dyeId'];
		$dyeName = $matches['dyeName'];
		$dyeColor = $matches['dyeColor'];
	
		if ($dyeName  == null || $dyeName  == "") return "";
		if ($dyeColor == null || $dyeColor == "") return "";
		
		$printData = array();
		$this->AddPrintData($printData, $dyeName, $this->printOptionsSmallWhite, array('br' => true, 'format' => false, 'lineBreak' => false));
		$this->PrintDataText($image, $printData, $this->borderMargin + 145, $y + 4, 'left');
		
		$color = imagecolorallocate($image, hexdec(substr($dyeColor, 0, 2)), hexdec(substr($dyeColor, 2, 2)), hexdec(substr($dyeColor, 4, 2)));
		imagefilledrectangle($image, 120, $y, 120 + 20, $y + 20, $color);
		
		$dyeStampImage = imagecreatefrompng("resources/dyeStampBox.png");
		
		if ($dyeStampImage != null)
		{
			imagecopy($image, $dyeStampImage, 120 - 2, $y - 2, 0, 0, 24, 24);
		}
		
		return 30;
	}
	
	
	public function OutputBorder ($image)
	{
		$borderImage = imagecreatefrompng("resources/eso_item_border.png");
		if ($borderImage == null) return false;
		
		$topBorderImage    = imagecreatetruecolor(imagesx($borderImage) - $this->borderWidth*2, $this->borderWidth);
		$bottomBorderImage = imagecreatetruecolor(imagesx($borderImage) - $this->borderWidth*2, $this->borderWidth);
		$leftBorderImage   = imagecreatetruecolor($this->borderWidth, imagesy($borderImage) - $this->borderWidth*2);
		$rightBorderImage  = imagecreatetruecolor($this->borderWidth, imagesy($borderImage) - $this->borderWidth*2);
		$cornerImageNE = imagecreatetruecolor($this->borderWidth, $this->borderWidth);
		$cornerImageNW = imagecreatetruecolor($this->borderWidth, $this->borderWidth);
		$cornerImageSE = imagecreatetruecolor($this->borderWidth, $this->borderWidth);
		$cornerImageSW = imagecreatetruecolor($this->borderWidth, $this->borderWidth);
		
		imagecopy($topBorderImage, $borderImage, 0, 0, $this->borderWidth, 0, imagesx($borderImage) - $this->borderWidth*2, $this->borderWidth);
		imagecopy($bottomBorderImage, $borderImage, 0, 0, $this->borderWidth, imagesy($borderImage) - $this->borderWidth, imagesx($borderImage) - $this->borderWidth*2, $this->borderWidth);
		imagecopy($leftBorderImage, $borderImage, 0, 0, 0, $this->borderWidth, $this->borderWidth, imagesy($borderImage) - $this->borderWidth*2);
		imagecopy($rightBorderImage, $borderImage, 0, 0, imagesx($borderImage) - $this->borderWidth, $this->borderWidth, $this->borderWidth, imagesy($borderImage) - $this->borderWidth*2);
		
		imagecopy($cornerImageNE, $borderImage, 0, 0, imagesx($borderImage) - $this->borderWidth, 0, $this->borderWidth, $this->borderWidth);
		imagecopy($cornerImageNW, $borderImage, 0, 0, 0, 0, $this->borderWidth, $this->borderWidth);
		imagecopy($cornerImageSE, $borderImage, 0, 0, imagesx($borderImage) - $this->borderWidth, imagesy($borderImage) - $this->borderWidth, $this->borderWidth, $this->borderWidth);
		imagecopy($cornerImageSW, $borderImage, 0, 0, 0, imagesy($borderImage) - $this->borderWidth, $this->borderWidth, $this->borderWidth);
		
		imagecopyresized($image, $topBorderImage,    $this->borderWidth, $this->topMargin, 0, 0, imagesx($image) - $this->borderWidth*2, $this->borderWidth, imagesx($topBorderImage), $this->borderWidth);
		imagecopyresized($image, $bottomBorderImage, $this->borderWidth, imagesy($image) - $this->borderWidth, 0, 0, imagesx($image) - $this->borderWidth*2, $this->borderWidth, imagesx($bottomBorderImage), $this->borderWidth);
		imagecopyresized($image, $leftBorderImage,   0, $this->topMargin + $this->borderWidth, 0, 0, $this->borderWidth, imagesy($image) - $this->borderWidth*2 - $this->topMargin, $this->borderWidth, imagesy($leftBorderImage));
		imagecopyresized($image, $rightBorderImage,  imagesx($image) - $this->borderWidth, $this->borderWidth + $this->topMargin, 0, 0, $this->borderWidth, imagesy($image) - $this->borderWidth*2 - $this->topMargin, $this->borderWidth, imagesy($rightBorderImage));
		
		imagecopy($image, $cornerImageNW, 0, $this->topMargin, 0, 0, $this->borderWidth, $this->borderWidth);
		imagecopy($image, $cornerImageNE, imagesx($image) - $this->borderWidth, $this->topMargin, 0, 0, $this->borderWidth, $this->borderWidth);
		imagecopy($image, $cornerImageSW, 0, imagesy($image) - $this->borderWidth, 0, 0, $this->borderWidth, $this->borderWidth);
		imagecopy($image, $cornerImageSE, imagesx($image) - $this->borderWidth, imagesy($image) - $this->borderWidth, 0, 0, $this->borderWidth, $this->borderWidth);
		
		return true;
	}
	
	
	public function OutputImage()
	{
		$image = imagecreatetruecolor(self::ESOIL_IMAGE_WIDTH, self::ESOIL_IMAGE_MAXHEIGHT);
		if ($image == null) return false;
		$this->image = $image;
		
		imageantialias($image, true);
		imagealphablending($image, true);
		imagesavealpha($image, true);
		
		$itemData = $this->itemRecord;
		
		$this->qualityColors = array(
				imagecolorallocate($image, 0xff, 0xff, 0xff),
				imagecolorallocate($image, 0xff, 0xff, 0xff),
				imagecolorallocate($image, 0x2d, 0xc5, 0x0e),
				imagecolorallocate($image, 0x3a, 0x92, 0xff),
				imagecolorallocate($image, 0xa0, 0x2e, 0xf7),
				imagecolorallocate($image, 0xee, 0xca, 0x2a),
		);
		
		$this->background = imagecolorallocatealpha($image, 0, 0, 0, 127);
		$this->invis = imagecolorallocatealpha($image, 0, 0, 0, 0);
		$this->black =  imagecolorallocate($image, 0, 0, 0);
		$this->white =  imagecolorallocate($image, 255, 255, 255);
		$this->textColor =  imagecolorallocate($image, 0xC5, 0xC2, 0x9E);
		$this->darkGray =  imagecolorallocate($image, 0x55, 0x55, 0x55);
		
		$this->printOptionsLargeWhite = array(
				"font" => self::ESOIL_BOLDFONT_FILE,
				"color" => $this->white,
				"size" => $this->bigFontSize,
		);
		
		$this->printOptionsMedBeige = array(
				"font" => self::ESOIL_BOLDFONT_FILE,
				"color" => $this->textColor,
				"size" => $this->medFontSize,
		);
		
		$this->printOptionsMedWhite = array(
				"font" => self::ESOIL_REGULARFONT_FILE,
				"color" => $this->white,
				"size" => $this->medFontSize,
		);
		
		$this->printOptionsSmallWhite = array(
				"font" => self::ESOIL_BOLDFONT_FILE,
				"color" => $this->white,
				"size" => $this->smallFontSize,
		);
		
		$this->printOptionsSmallBeige = array(
				"font" => self::ESOIL_REGULARFONT_FILE,
				"color" => $this->textColor,
				"size" => $this->medFontSize,
		);
		
		$this->printOptionsTinyBeige = array(
				"font" => self::ESOIL_REGULARFONT_FILE,
				"color" => $this->textColor,
				"size" => $this->smallFontSize,
		);
		
		$this->printOptionsSmallInvis = array(
				"font" => self::ESOIL_REGULARFONT_FILE,
				"color" => $this->invis,
				"size" => $this->medFontSize,
		);
		
		imagefill($image, 0, 0, $this->background);
		imagefilledrectangle ($image, $this->borderWidth, $this->topMargin + $this->borderWidth, self::ESOIL_IMAGE_WIDTH - $this->borderWidth, self::ESOIL_IMAGE_MAXHEIGHT - $this->borderWidth, $this->black);
		
		$itemName = strtoupper($itemData['name']);
		$quality = $itemData['quality'];
		$this->nameColor = $this->qualityColors[$quality];
		if ($this->nameColor == null) $this->nameColor = $this->qualityColors[5];
		
		if ($this->showSummary)
		{
			if ($quality == '1-5' || $quality == '0-5') $this->nameColor = $this->qualityColors[5];
		}
		
		if ($this->nameColor == null) $this->nameColor = $this->white;
		
		$namePrintOptions = array(
				"font" => self::ESOIL_BOLDFONT_FILE,
				"color" => $this->nameColor,
				"size" => $this->bigFontSize,
		);
		
		$y = $this->topMargin + $this->borderMargin + $this->medFontLineHeight;
		$this->PrintTextAA($image, $this->smallFontSize, 10, $y, $this->textColor, self::ESOIL_BOLDFONT_FILE, $this->MakeItemTypeText());
		
		$y += $this->medFontLineHeight;
		$this->PrintRightText($image, $this->smallFontSize, 390, $y, $this->textColor, self::ESOIL_BOLDFONT_FILE, $this->MakeItemBindTypeText());
		$this->PrintTextAA($image, $this->smallFontSize, 10, $y, $this->textColor, self::ESOIL_BOLDFONT_FILE, $this->MakeItemSubTypeText());
		
		$printData = array();
		$this->AddPrintData($printData, $itemName, $namePrintOptions, array('br' => true, 'lineBreak' => true));
		$y += $this->PrintDataText($image, $printData, self::ESOIL_IMAGE_WIDTH/2, $y + $this->medFontLineHeight, 'center') + 10;
		
		$this->OutputItemStolenBlock($image);
		
		$y += 6;
		$this->OutputCenterImage($image, "./resources/eso_item_hr.png", $y);
		$y += 6;
		
		if ($this->GetHasItemBlockDisplay())
		{
			$this->OutputItemLeftBlock($image, $y);
			$this->OutputItemLevelBlock($image, $y);
			$this->OutputItemRightBlock($image, $y);
			$y += 40;
			$y += $this->OutputItemBar($image, $y);
		}
		else
		{
			$y += 5;
		}
		
		$y += $this->OutputItemAbilityBlock($image, $y);
		$y += $this->OutputItemEnchantBlock($image, $y);
		$y += $this->OutputItemTraitBlock($image, $y);
		$y += $this->OutputItemTraitAbilityBlock($image, $y);
		$y += $this->OutputItemSetBlock($image, $y);
		$y += $this->OutputItemDyeStampBlock($image, $y);
		
		$y += $this->OutputItemDescription($image, $y) + 4;
		$y += $this->OutputItemTagsBlock($image, $y);
		$y += $this->OutputItemCraftedBlock($image, $y);
		
		if ($this->useUpdate10Display)
		{
			$y += $this->OutputItemNewValueBlock($image, $y);
		}
		
		$this->OutputItemStyle($image, $y);
		$this->PrintRightText($image, $this->tinyFontSize, 390, $y, $this->darkGray, self::ESOIL_REGULARFONT_FILE, "uesp.net");
		$y += 10;
		
		$imageHeight = $y + 1;
		
		$croppedImage = imagecreatetruecolor(self::ESOIL_IMAGE_WIDTH, $imageHeight);
		if ($image == null) return false;
		imageantialias($croppedImage, true);
		imagealphablending($croppedImage, true);
		imagesavealpha($croppedImage, true);
		imagefill($croppedImage, 0, 0, $this->background);
		$this->OutputBorder($croppedImage);
		imagecopy($croppedImage, $image, 0, 0, 0, 0, imagesx($image), $imageHeight - $this->borderWidth);
		$this->OutputCenterImage($croppedImage, $this->MakeItemIconImageFilename(), 1);
		
		imagepng($croppedImage);
		$this->SaveImage($croppedImage);
	}
	
	
	public function GetImageFilename()
	{
		$path    = self::ESOIL_IMAGE_CACHEPATH . $this->itemId;
		$filename = "";
		
		if ($this->itemSet != "")
		{
			$filename = str_replace("'", '', $this->itemSet);
		}
		elseif ($this->showSummary)
		{
			if ($this->enchantId1 > 0 && $this->enchantIntType1 >= 0 && $this->enchantIntLevel1 > 0)
			{
				$filename   = $this->itemId . "-summary-" . $this->enchantId1 . "-summary";
			}
			else
			{
				$filename   = $this->itemId . "-summary";
			}
		}
		elseif ($this->enchantId1 > 0 && $this->enchantIntType1 >= 0 && $this->enchantIntLevel1 > 0)
		{
			$filename   = $this->itemId . "-" .$this->itemLevel . "-" . $this->itemQuality . "-" . $this->enchantId1 . "-" . $this->enchantIntLevel1 . "-" . $this->enchantIntType1 . "";
		}
		else
		{
			$filename   = $this->itemId . "-" .$this->itemLevel . "-" . $this->itemQuality . "";
		}
		
		return $filename;
	}
	
	
	public function GetImageIntFilename()
	{
		$intPath = self::ESOIL_IMAGE_CACHEPATH . $this->itemId . "/int";
		$intFilename = "/";
		
		if ($this->showSummary)
		{
			if ($this->enchantId1 > 0 && $this->enchantIntType1 >= 0 && $this->enchantIntLevel1 > 0)
			{
				$intFilename = $this->itemId . "-summary-" . $this->enchantId1 . "-summary";
			}
			else
			{
				$intFilename = $this->itemId . "-summary";
			}
		}
		else if ($this->enchantId1 > 0 && $this->enchantIntType1 >= 0 && $this->enchantIntLevel1 > 0)
		{
			$intFilename = $this->itemId . "-" .$this->itemIntLevel . "-" . $this->itemIntType . "-" . $this->enchantId1 . "-" . $this->enchantIntLevel1 . "-" . $this->enchantIntType1 . "";
		}
		else
		{
			$intFilename = $this->itemId . "-" .$this->itemIntLevel . "-" . $this->itemIntType . "";
		}
		
		return $intFilename;
	}
	
	
	public function SaveImage($image)
	{
		if ($this->version != "") return false;
		if ($this->isError) return false;
		if ($this->noCache) return false;
		
		if ($this->itemSet != "")
		{
			$path    = self::ESOIL_IMAGE_CACHEPATH . "sets/";
			$filename = $this->GetImageFilename() . ".png";
			$fullFilename = $path . $filename;
			
			if (!file_exists($path) && !mkdir($path, 0775, true)) return false;
			imagepng($image, $fullFilename);
			
			return true;
		}
		
		if ($this->itemId <= 0) return false;
		if ($this->itemIntLevel <= 0) return false;
		if ($this->itemIntType <= 0) return false;
		
		$path    = self::ESOIL_IMAGE_CACHEPATH . $this->itemId;
		$intPath = self::ESOIL_IMAGE_CACHEPATH . $this->itemId . "/int";
		
		$pngFilename = $path . "/" . $this->GetImageFilename() . ".png";
		$pngIntFilename = $intPath . "/" . $this->GetImageIntFilename() . ".png";
		//$pngFilename    = $path .    "/" . $this->itemId . "-" . $this->itemLevel    . "-" . $this->itemQuality . ".png";
		//$pngIntFilename = $intPath . "/" . $this->itemId . "-" . $this->itemIntLevel . "-" . $this->itemIntType . ".png";
		//$jpgFilename = $path . "/" . $this->itemId . "-" .$this->itemIntLevel . "-" . $this->itemIntType . ".jpg";
		
		if (!file_exists($path)    && !mkdir($path, 0775, true))    return false;
		if (!file_exists($intPath) && !mkdir($intPath, 0775, true)) return false;
		
		//imagejpeg($image, $jpgFilename, 75);
		imagepng($image, $pngFilename);
		imagepng($image, $pngIntFilename);
		
		return true;
	}
	
	
	public function ServeCachedImage($useRedirect)
	{
		if ($this->isError) return false;
		if ($this->noCache) return false;
		
		if ($this->itemSet != "")
		{
			$path    = self::ESOIL_IMAGE_CACHEPATH . "sets/";
			$filename = $this->GetImageFilename() . ".png";
			$fullFilename = $path . $filename;
			
			if (file_exists($fullFilename))
			{
				readfile($fullFilename);
				return true;
			}
			
			return false;
		}
		
		if ($this->itemId <= 0) return false;
		if ($this->itemBound > 0) return false;
		if ($this->itemStyle > 0) return false;
		if ($this->itemCrafted > 0) return false;
		if ($this->itemCharges > 0) return false;
		if ($this->itemPotionData > 0) return false;
		if ($this->writData1 > 0) return false;
		if ($this->writData2 > 0) return false;
		if ($this->writData3 > 0) return false;
		if ($this->writData4 > 0) return false;
		if ($this->writData5 > 0) return false;
		if ($this->writData6 > 0) return false;
		if ($this->version != "") return false;
		if ($this->itemSetCount >= 0) return false;
		if ($this->itemStolen > 0) return false;
		if ($this->transmuteTrait > 0) return false;
		
		$path    = self::ESOIL_IMAGE_CACHEPATH . $this->itemId . "/";
		$intPath = self::ESOIL_IMAGE_CACHEPATH . $this->itemId . "/int/";
		
		$filename = $this->GetImageFilename() . ".png";
		$intFilename = $this->GetImageIntFilename() . ".png";
		
		$fullFilename = $path . $filename; 
		$fullIntFilename = $intPath . $intFilename;
		
		if ($this->itemLevel > 0 && $this->itemQuality >= 0 && file_exists($fullFilename))
		{
			
			if ($useRedirect)
			{
				$url = "/itemcache/" . $filename;
				header("Location: $url");
				return true;
			}
			
			readfile($fullFilename);
			return true;
		}
		
		if ($this->itemIntLevel > 0 && $this->itemIntType >= 0 && file_exists($fullIntFilename))
		{
			
			if ($useRedirect)
			{
				$url = "/itemcache/" . $intFilename;
				header("Location: $url");
				return true;
			}
			
			readfile($fullIntFilename);
			return true;
		}
		
		return false;
	}
	
	
	public function MakeImage()
	{
		$this->OutputHtmlHeader();
		
		if (!CanViewEsoLogVersion($this->version))
		{
			$this->LoadItemErrorData();
			$this->itemRecord['name'] = "Permission Denied!";
			$this->OutputImage();
			return true;
		}
		
		if ($this->ServeCachedImage(false))
		{
			UpdateEsoPageViews("itemLinkImageViews");
			return true;
		}
		
		if (!$this->InitDatabase()) return false;
		
		if ($this->version != "" && $this->version < GetEsoUpdateVersion()) $this->showSummary = true;
		
		if ($this->showSummary)
		{
			if ($this->version >= GetEsoUpdateVersion())
			{
				if (!$this->LoadItemRecord()) $this->LoadItemErrorData();
			}
			
			$this->LoadEnchantRecords();
			if (!$this->LoadItemSummaryData()) $this->LoadItemErrorData();
			
			if ($this->version >= GetEsoUpdateVersion())
				$this->MergeItemSummary();
			else
				$this->MergeItemSummaryAll();
		}
		else
		{
			if (!$this->LoadItemRecord()) $this->LoadItemErrorData();
			$this->LoadEnchantRecords();
		}
		
		if ($this->ServeCachedImage(false)) return true;
		
		$this->OutputImage();
		return true;
	}
	
	
};

$g_EsoItemLinkImage = new CEsoItemLinkImage();
$g_EsoItemLinkImage->MakeImage();

