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


class CEsoItemLinkImage
{
	const ESOIL_ICON_PATH = "/home/uesp/www/eso/gameicons/";
	const ESOIL_IMAGE_CACHEPATH = "/home/uesp/esoItemImages/";
	const ESOIL_ICON_UNKNOWN = "unknown.png";
	const ESOIL_IMAGE_WIDTH = 400;
	const ESOIL_IMAGE_MAXHEIGHT = 600;
	const ESOIL_REGULARFONT_FILE = "./resources/esofontregular-webfont.ttf";
	const ESOIL_BOLDFONT_FILE = "./resources/esofontbold-webfont.ttf";
	const ESOIL_LINEHEIGHT_FACTOR = 1.75;
	const ESOIL_LEVELBLOCK_CENTERXAMT = 70;
	
	const ESOIL_POTION_MAGICITEMID = 1;
	const ESOIL_POISON_MAGICITEMID = 2;
	const ESOIL_ENCHANT_ITEMID = 23662;
	
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
			"icon",
	);
	
	public $inputParams = array();
	public $itemId = 0;
	public $itemLink = "";
	public $itemLevel = -1;		// 1-64
	public $itemQuality = -1;	// 0-5
	public $itemIntLevel = -1;	// 1-50
	public $itemIntType = -1;	// 1-40
	public $itemBound = -1;
	public $itemStyle = -1;
	public $itemCrafted = -1;
	public $itemCharges = -1;
	public $itemSetCount = -1;
	public $itemPotionData = -1;
	public $itemStolen = -1;
	public $enchantId1 = -1;
	public $enchantIntLevel1 = -1;
	public $enchantIntType1 = -1;
	public $enchantId2 = -1;
	public $enchantIntLevel2 = -1;
	public $enchantIntType2 = -1;
	public $inputIntType = -1;
	public $inputIntLevel = -1;
	public $inputLevel = -1;
	public $inputQuality = -1;
	public $enchantFactor = 0;
	public $version = "";
	public $useUpdate10Display = true;
	public $noCache = false;
	public $showSummary = false;
	public $itemRecord = array();
	public $itemSummary = array();
	public $db = null;
	
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
		print($errorMsg);
		error_log($errorMsg);
		return false;
	}
	
	
	public function ParseItemLink($itemLink)
	{	
		$result = preg_match('/\|H(?P<color>[A-Za-z0-9]*)\:item\:(?P<itemId>[0-9]*)\:(?P<subtype>[0-9]*)\:(?P<level>[0-9]*)\:(?P<enchantId1>[0-9]*)\:(?P<enchantSubtype1>[0-9]*)\:(?P<enchantLevel1>[0-9]*)\:(?P<enchantId2>[0-9]*)\:(?P<enchantSubtype2>[0-9]*)\:(?P<enchantLevel2>[0-9]*)\:(.*?)\:(?P<style>[0-9]*)\:(?P<crafted>[0-9]*)\:(?P<bound>[0-9]*)\:(?P<stolen>[0-9]*)\\:(?P<charges>[0-9]*)\:(?P<potionData>[0-9]*)\|h\[?(?P<name>[a-zA-Z0-9 %_\(\)\'\-]*)(?P<nameCode>.*?)\]?\|h/', $itemLink, $matches);
		if (!$result) return false;
		
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
		
		$this->enchantId2 = (int) $matches['enchantId2'];
		$this->enchantIntLevel2 = (int) $matches['enchantLevel2'];
		$this->enchantIntType2 = (int) $matches['enchantSubtype2'];
		
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
				error_log("item level = ".$this->itemLevel);
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
		
		if (array_key_exists('intlevel', $this->inputParams))
		{
			$this->itemIntLevel = (int) $this->inputParams['intlevel'];
			$this->itemIntType = 1;
		}
		
		if (array_key_exists('inttype', $this->inputParams))
		{
			$this->itemIntType = (int) $this->inputParams['inttype'];
			if ($this->itemIntLevel < 0) $this->itemIntLevel = 1;
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
		if (array_key_exists('summary', $this->inputParams)) $this->showSummary = true;
		if (array_key_exists('potiondata', $this->inputParams)) $this->itemPotionData = (int) $this->inputParams['potiondata'];;
		if (array_key_exists('stolen', $this->inputParams)) $this->itemStolen = (int) $this->inputParams['stolen'];;
		if (array_key_exists('style', $this->inputParams)) $this->itemStyle = (int) $this->inputParams['style'];
		if (array_key_exists('enchantfactor', $this->inputParams)) $this->enchantFactor = (int) $this->inputParams['enchantfactor'];
		
		if (array_key_exists('version', $this->inputParams)) $this->version = urldecode($this->inputParams['version']);
		if (array_key_exists('v', $this->inputParams)) $this->version = urldecode($this->inputParams['v']);
				
		if (IsEsoVersionAtLeast($this->version, 10)) $this->useUpdate10Display = true;
		
		if ($this->itemLevel < 0 && $this->itemQuality < 0)
		{
			if ($this->itemIntLevel < 0) $this->itemIntLevel = 1;
			if ($this->itemIntType  < 0) $this->itemIntType  = 1;
		}
		
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
			$query = "SELECT * FROM minedItem".$this->GetTableSuffix()." WHERE itemId={$this->itemId} AND level={$this->itemLevel} AND quality={$this->itemQuality} LIMIT 1;";
			$this->itemErrorDesc = "id={$this->itemId}, Level={$this->itemLevel}, Quality={$this->itemQuality}";
		}
		else
		{
			if ($this->itemIntType < 0) return $this->ReportError("ERROR: Missing or invalid item internal type specified (1-400)!");
			$query = "SELECT * FROM minedItem".$this->GetTableSuffix()." WHERE itemId={$this->itemId} AND internalLevel={$this->itemIntLevel} AND internalSubtype={$this->itemIntType} LIMIT 1;";
			$this->itemErrorDesc = "id={$this->itemId}, Internal Level={$this->itemIntLevel}, Internal Type={$this->itemIntType}";
		}
		
		$result = $this->db->query($query);
		if (!$result) return $this->ReportError("ERROR: Database query error! " . $this->db->error);
		
		if ($result->num_rows === 0)
		{
			if ($this->itemLevel <= 0 && $this->itemIntType == 1)
			{
				$this->itemIntType = 2;
				$query = "SELECT * FROM minedItem".$this->GetTableSuffix()." WHERE itemId={$this->itemId} AND internalLevel={$this->itemIntLevel} AND internalSubtype={$this->itemIntType} LIMIT 1;";
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
		
		$this->itemRecord = $row;
		
		if ($this->itemRecord['type'] == 7)  $this->LoadItemPotionData();
		if ($this->itemRecord['type'] == 30) $this->LoadItemPoisonData();
		
		$this->LoadEnchantMaxCharges();
		
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
			$query = "SELECT traitAbilityDesc, traitCooldown FROM minedItem{$this->GetTableSuffix()} WHERE itemId=$itemId AND internalLevel=$intlevel AND internalSubtype=$subtype AND potionData=$effectIndex LIMIT 1;";
		}
		else if ($this->itemIntLevel >= 0 && $this->itemIntType >= 0)
		{
			$intlevel = $this->itemIntLevel;
			$subtype = $this->itemIntType;
			$query = "SELECT traitAbilityDesc, traitCooldown FROM minedItem{$this->GetTableSuffix()} WHERE itemId=$itemId AND internalLevel=$intlevel AND internalSubtype=$subtype AND potionData=$effectIndex LIMIT 1;";
		}
		else if ($this->itemLevel >= 1)
		{
			$level = $this->itemLevel;
			$quality = $this->itemQuality;
			$query = "SELECT traitAbilityDesc, traitCooldown FROM minedItem{$this->GetTableSuffix()} WHERE itemId=$itemId AND level=$level AND quality=$quality AND potionData=$effectIndex LIMIT 1;";
		}
		else
		{
			$intlevel = 1;
			$subtype = 1;
			$query = "SELECT traitAbilityDesc, traitCooldown FROM minedItem{$this->GetTableSuffix()} WHERE itemId=$itemId AND internalLevel=$intlevel AND internalSubtype=$subtype AND potionData=$effectIndex LIMIT 1;";
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
	
	
	private function LoadItemSummaryData()
	{
		if ($this->itemId <= 0) return $this->ReportError("ERROR: Missing or invalid item ID specified (1-65000)!");
		$query = "SELECT * FROM minedItemSummary".$this->GetTableSuffix()." WHERE itemId={$this->itemId};";
	
		$result = $this->db->query($query);
		if (!$result) return $this->ReportError("ERROR: Database query error! " . $this->db->error);
	
		$this->itemSummary = $result->fetch_assoc();
		if (!$this->itemSummary) $this->ReportError("ERROR: No item summary found matching ID {$this->itemId}!");
	
		return true;
	}
	
	
	private function LoadEnchantRecords()
	{
		if ($this->enchantId1 > 0 && $this->enchantIntLevel1 > 0 && $this->enchantIntType1 > 0)
		{
			$query = "SELECT * FROM minedItem".$this->GetTableSuffix()." WHERE itemId={$this->enchantId1} AND internalLevel={$this->enchantIntLevel1} AND internalSubtype={$this->enchantIntType1} LIMIT 1;";
			$result = $this->db->query($query);
			if (!$result) return $this->ReportError("ERROR: Database query error! " . $this->db->error);
				
			$result->data_seek(0);
			$row = $result->fetch_assoc();
			if ($row) $this->enchantRecord1 = $row;
		}
	
		if ($this->enchantId2 > 0 && $this->enchantIntLevel2 > 0 && $this->enchantIntType2 > 0)
		{
			$query = "SELECT * FROM minedItem".$this->GetTableSuffix()." WHERE itemId={$this->enchantId2} AND internalLevel={$this->enchantIntLevel2} AND internalSubtype={$this->enchantIntType2} LIMIT 1;";
			$result = $this->db->query($query);
			if (!$result) return $this->ReportError("ERROR: Database query error! " . $this->db->error);
	
			$result->data_seek(0);
			$row = $result->fetch_assoc();
			if ($row) $this->enchantRecord2 = $row;
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
		
		$origin = $_SERVER['HTTP_ORIGIN'];
		
		if (substr($origin, -8) == "uesp.net")
		{
			header("Access-Control-Allow-Origin: $origin");
		}
	}
	
	
	public function FormatPrintData(&$printData, $lineData)
	{
		$lineBreak = array_key_exists('br', $lineData);
		
		$newText = preg_replace("#\|t([0-9]*):([0-9]*):([^\|]*)\|trank #s", "VR ", $lineData['text']);
		$newText = preg_replace("#\|t([0-9]*):([0-9]*):champion_icon_[0-9]+\.dds\|t#s", "CP", $newText);
		                         //"|t24:24:champion_icon_24.dds|t"
		$newText = preg_replace("#\|t([0-9]*):([0-9]*):([^\|]*)\|t#s", "", $newText);
		
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
		$newText = preg_replace("#\|t([0-9]*):([0-9]*):([^\|]*)\|trank #s", "VR ", $newText);
		$newText = preg_replace("#\|t([0-9]*):([0-9]*):([^\|]*)\|t#s", "", $newText);
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
		elseif ($type == 29) // Recipe
		{
			$specialType = $this->itemRecord['specialType'];
			if ($specialType == null || $specialType == "" || $specialType <= 0) return "(Provisioning)";
			return "(" . GetEsoItemSpecialTypeText($specialType) . ")";
		}
		elseif ($type == 61) // Furniture
		{
			$type1 = $this->itemRecord['setBonusDesc4'];
			$type2 = $this->itemRecord['setBonusDesc5'];
			
			if ($type1 != "" && $type != "") return "(" . $type1 . " / " . $type2 . ")";
			return "(" . $type1 . $type2 . ")";
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
		if ($this->showSummary) return 0;
		
		$level = intval($this->itemRecord['level']);
		if ($level <= 0) return 0;
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
				return false;
				
			case 2:
				$equipType = $this->itemRecord['equipType'];
				
				if ($equipType == 2 || $equipType == 12) 
					$display = false;
				else
					$display = true;
				
				break;
				
			case 1:
				$display = true;
				break;
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
			
			return $this->PrintDataText($image, $printData, $x, $y + 4, 'right');
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
		}
	
		return true;
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
			if ($this->itemRecord['trait'] == 2) $maxCharges *= $this->itemRecord['quality']*0.25 + 2;
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
				"#(Deals \|c[0-9a-fA-F]{6})([0-9]+)(\|r)#",
				"#(restores \|c[0-9a-fA-F]{6})([0-9]+)(\|r)#",
				"#(by \|c[0-9a-fA-F]{6})([0-9]+)(\|r)#",
				"#(Grants a \|c[0-9a-fA-F]{6})([0-9]+)(\|r)#",
		);
	
		$newDesc = $desc;
		$trait = $this->itemRecord['trait'];
		$traitDesc = FormatRemoveEsoItemDescriptionText($this->itemRecord['traitDesc']);
	
		$armorFactor = 1 + $this->enchantFactor;
		$weaponFactor = 1 + $this->enchantFactor;
	
			/* Infused */
		if ($trait == 16 || $trait == 4)
		{
			$result = preg_match("#effect by ([0-9]\.?[0-9]*)#", $traitDesc, $matches);
			$traitValue = 0;
				
			if ($result && $matches[1])
			{
				$traitValue = 1 + ((float) $matches[1]) / 100;
				if ($trait == 16) $armorFactor *= $traitValue;
				if ($trait ==  4) $weaponFactor *= $traitValue;
			}
		}
	
		$armorType = $this->itemRecord['armorType'];
		$equipType = $this->itemRecord['equipType'];
	
			/* Modify enchants of small armor pieces */
		if ($armorType > 0 && ($equipType == 4 || $equipType == 8 || $equipType == 10 || $equipType == 13))
		{
			$armorFactor *= 0.405;
		}
	
		if (($armorType > 0 || $weaponType == 14) && !$isDefaultEnchant && $armorFactor != 1)
		{
			$newDesc = preg_replace_callback("#(Adds \|c[0-9a-fA-F]{6})([0-9]+)(\|r Maximum)|(Adds )([0-9]+)( Maximum)#",
						
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
		if ($this->itemRecord['type'] == 29)	// Recipes
		{
			$ability = strtoupper($this->itemRecord['abilityName']);
			$abilityDesc = $this->itemRecord['abilityDesc'];
			if ($abilityDesc == "") return "";
		}
		else
		{
			$ability = strtoupper($this->itemRecord['abilityName']);
			$abilityDesc = $this->itemRecord['abilityDesc'];
			if ($abilityDesc == "") return 0;
			
			$cooldown = ((int) $this->itemRecord['abilityCooldown']) / 1000;
			$abilityDesc .= " (" . $cooldown . " second cooldown)";
		}
		
		$printData = array();
		if ($ability != "") $this->AddPrintData($printData, $ability, $this->printOptionsSmallWhite, array('br' => true, 'format' => true));
		$this->AddPrintData($printData, $abilityDesc, $this->printOptionsSmallBeige, array('format' => true, 'lineBreak' => true, 'br' => true));
		
		return $this->PrintDataText($image, $printData, self::ESOIL_IMAGE_WIDTH/2, $y, 'center') + $this->blockMargin;
	}
	
	
	private function OutputItemTraitBlock($image, $y)
	{
		$trait = $this->itemRecord['trait'];
		if ($trait <= 0) return 0;
		
		$printData = array();
		$this->AddPrintData($printData,strtoupper(GetEsoItemTraitText($trait, $this->version)), $this->printOptionsSmallWhite, array('br' => true));
		$this->AddPrintData($printData, $this->itemRecord['traitDesc'], $this->printOptionsSmallBeige, array('format' => true, 'lineBreak' => true));
		
		return $this->PrintDataText($image, $printData, self::ESOIL_IMAGE_WIDTH/2, $y, 'center') + $this->blockMargin;
	}
	
	
	private function OutputItemTraitAbilityBlock($image, $y)
	{
		if ($this->itemRecord['traitAbilityDescArray'] == null)
		{
			$abilityDesc = strtoupper($this->itemRecord['traitAbilityDesc']);
			if ($abilityDesc == "") return 0;
			$cooldown = ((int) $this->itemRecord['traitCooldown']) / 1000;
			$abilityDesc .= " ($cooldown second cooldown)";
			
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
			$cooldown = round($this->itemRecord['traitCooldownArray'][$index] / 1000);
			$abilityDesc[] = $desc;
			$cooldownDesc = " ($cooldown second cooldown)";

			if (!$isFirst) $this->AddPrintData($printData, "=", $this->printOptionsSmallInvis, array('format' => false, 'lineBreak' => true));
			$this->AddPrintData($printData, $desc, $this->printOptionsSmallBeige, array('format' => true, 'lineBreak' => true));
			
			$isFirst = false;
		}
		
		if (count($abilityDesc) == 0) return 0;
		$this->AddPrintData($printData, $cooldownDesc, $this->printOptionsSmallBeige, array('format' => true, 'lineBreak' => true));
	
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
		
		for ($i = 1; $i <= $setBonusCount && $i <= 5; $i += 1)
		{
			$setCount = $this->itemRecord['setBonusCount' . $i];
			$setDesc = $this->itemRecord['setBonusDesc' . $i];
			
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
		if ($glyphMinLevel == 0) return $this->itemRecord['description'];
		
		$minDesc = "";
		
		if ($glyphMinLevel <= 50)
		{
			$minDesc = "level $glyphMinLevel";
		}
		else
		{
			$cp = ($glyphMinLevel - 50) * 10;
			$minDesc = "level 50 CP$cp";
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
		if ($matDesc != "") $desc = $matDesc;
		if ($this->itemRecord['type'] == 51) $desc = $this->MakePotencyItemDescription();
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
		if ($this->itemRecord['tags'] == "") return 0;
	
		$printData = array();
		$this->AddPrintData($printData, "Treasure Type:", $this->printOptionsSmallBeige, array('br' => true));
		$this->AddPrintData($printData, $this->itemRecord['tags'], $this->printOptionsMedWhite, array('br' => true));
		
		return $this->PrintDataText($image, $printData, self::ESOIL_IMAGE_WIDTH/2, $y, 'center') + $this->blockMargin;
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
		$this->image = image;
		
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
		if ($this->nameColor == null) $this->nameColor = $white;
		
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
		
		$printData = array();
		$this->AddPrintData($printData, $label, $this->printOptionsMedBeige);
		$this->AddPrintData($printData, $valueText, $this->printOptionsLargeWhite);
		$this->PrintDataText($image, $printData, $this->dataBlockMargin, $y + 4, 'left');
		
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
		$this->PrintRightText($image, $this->tinyFontSize, 390, $y, $this->darkGray, self::ESOIL_REGULARFONT_FILE, "www.uesp.net");
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
		
		if ($this->showSummary)
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
		else if ($this->enchantId1 > 0 && $this->enchantIntType1 >= 0 && $this->enchantIntLevel1 > 0)
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
		if ($this->itemId <= 0) return false;
		if ($this->itemIntLevel <= 0) return false;
		if ($this->itemIntType <= 0) return false;
		if ($this->version != "") return false;
		
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
		if ($this->noCache) return false;
		if ($this->enchantId2 > 0) return false;
		if ($this->itemId <= 0) return false;
		if ($this->itemBound > 0) return false;
		if ($this->itemStyle > 0) return false;
		if ($this->itemCrafted > 0) return false;
		if ($this->itemCharges > 0) return false;
		if ($this->itemPotionData > 0) return false;
		if ($this->version != "") return false;
		if ($this->itemSetCount >= 0) return false;
		if ($this->itemStolen > 0) return false;
		
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
		if ($this->ServeCachedImage(false)) return true;
		
		if (!$this->InitDatabase()) return false;
		
		if (!$this->LoadItemRecord()) $this->LoadItemErrorData();
		
		if ($this->showSummary)
		{
			$this->LoadItemSummaryData();
			$this->MergeItemSummary();
		}
		
		$this->LoadEnchantRecords();
		
		if ($this->ServeCachedImage(false)) return true;
		
		$this->OutputImage();
		return true;
	}
	
};

$g_EsoItemLinkImage = new CEsoItemLinkImage();
$g_EsoItemLinkImage->MakeImage();


?>


