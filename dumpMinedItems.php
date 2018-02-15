<?php
/*
 * dumpMinedItems.php - by Dave Humphrey (dave@uesp.net), 27 Nov 2014
 * 
 * Very basic web script to dump the minedItem data from the database
 * to a text/CSV format.
 */

	// Database users, passwords and other secrets
require("/home/uesp/secrets/esolog.secrets");
require("esoCommon.php");


class CEsoDumpMinedItems {
	
	const SELECT_LIMIT = 2000;
	
	public static $DEFAULT_FIELDS = array(
			"level", 
			"quality",
			"value",
			"weaponPower",
			"armorRating",
			"abilityName",
			"abilityDesc",
			"enchantName",
			"enchantDesc",
			"maxCharges",
			"trait",
			"traitDesc",
			"setBonusDesc1",
			"setBonusDesc2",
			"setBonusDesc3",
			"setBonusDesc4",
			"setBonusDesc5",
			"internalLevel",
			"internalSubtype",
			"tags",
			"comment",
	);
	
	public static $ITEMTYPE_FIELDS = array(
			"itemId",
			"name",
			"equipType",
			"weaponType",
			"value",
			"weaponPower",
			"armorRating",
			"abilityName",
			"abilityDesc",
			"enchantName",
			"enchantDesc",
			"trait",
			"traitDesc",
			"traitAbilityDesc",
			"setName",
			"setBonusDesc1",
			"setBonusDesc2",
			"setBonusDesc3",
			"setBonusDesc4",
			"setBonusDesc5",
			"icon",
	);
	
	public $TRANSFORM_FIELDS = array();
	
	public $itemId = 0;
	public $sortFields = array("level", "quality", "value");
	public $db = null;
	public $outputType = "csv";
	public $noTransform = false;
	public $keepBlankFields = false;
	public $keepInvariantFields = false;
	public $showLimits = false;
	public $inputParams = array();
	public $itemRecords = array();
	public $fieldRecords = array();
	public $allFields = array();
	public $validFields = array();
	public $isItemTable = true;
	public $useExTraitName = false;
	public $version = "";
	
	public $weaponType = -1;
	public $armorType = -1;
	public $itemType = -1;
	public $equipType = -1;
		
	private $tableFields = array("default");
	private $tableStartText = "";
	private $tableEndText = "";
	private $colStartText = "";
	private $colEndText = "";
	private $colHeaderStartText = "";
	private $colHeaderEndText = "";
	private $colHeaderSepText = "";
	private $rowStartText = "";
	private $rowEndText = "";
	private $rowSepText = "";
	private $colSepText = "";
	
	
	public function __construct()
	{
		//SetupUespSession();
		
		$this->TRANSFORM_FIELDS = array(
				"trait" => array($this, MakeItemTraitText),
				"weaponType" => GetEsoItemWeaponTypeText,
				"armorType" => GetEsoItemArmorTypeText,
				"equipType" => GetEsoItemEquipTypeText,
				"itemType" => GetEsoItemTypeText,
				"bindType" => GetEsoItemBindTypeText,
				"quality" => GetEsoItemQualityText,
				"style" => GetEsoItemStyleText,
				"level" => GetEsoItemLevelText,
				"enchantDesc" => array($this, FormatDescriptionString),
				"abilityDesc" => array($this, FormatDescriptionString),
				"traitDesc" => array($this, FormatDescriptionString),
				"traitAbilityDesc" => array($this, FormatDescriptionString),
				"setBonusDesc1" => array($this, FormatDescriptionString),
				"setBonusDesc2" => array($this, FormatDescriptionString),
				"setBonusDesc3" => array($this, FormatDescriptionString),
				"setBonusDesc4" => array($this, FormatDescriptionString),
				"setBonusDesc5" => array($this, FormatDescriptionString),
				"enchantDescRange" => array($this, FormatDescriptionString),
				"abilityDescRange" => array($this, FormatDescriptionString),
				"traitDescRange" => array($this, FormatDescriptionString),
				"traitAbilityDescRange" => array($this, FormatDescriptionString),
				"setBonusDesc1Range" => array($this, FormatDescriptionString),
				"setBonusDesc2Range" => array($this, FormatDescriptionString),
				"setBonusDesc3Range" => array($this, FormatDescriptionString),
				"setBonusDesc4Range" => array($this, FormatDescriptionString),
				"setBonusDesc5Range" => array($this, FormatDescriptionString),
				"description" => array($this, EscapeQuotes),
				"icon" => array($this, MakeIconLink),
				"itemId" => array($this, MakeItemIdLink),
				"name" => array($this, MakeItemNameLink),
				"title" => array($this, EscapeQuotes),
		);
		
		//error_reporting(E_ALL);
		
		$this->SetCsvStrings();
		
		$this->SetInputParams();
		$this->ParseInputParameters();
		$this->InitDatabase();
		
		$this->OutputHtmlHeader();
	}
	
	
	public function ReportError($errorMsg)
	{
		print($errorMsg);
		error_log($errorMsg);
		return false;
	}
	
	
	private function GetTableSuffix()
	{
		return GetEsoItemTableSuffix($this->version);
	}
	
	
	public function InitDatabase()
	{
		global $uespEsoLogReadDBHost, $uespEsoLogReadUser, $uespEsoLogReadPW, $uespEsoLogDatabase;
		
		$this->db = new mysqli($uespEsoLogReadDBHost, $uespEsoLogReadUser, $uespEsoLogReadPW, $uespEsoLogDatabase);
		if ($this->db->connect_error) return $this->ReportError("ERROR: Could not connect to mysql database!");
		
		UpdateEsoPageViews("dumpDataViews");
	
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
	
	
	public function ParseInputParameters()
	{
		if (array_key_exists('version', $this->inputParams)) $this->version = $this->inputParams['version'];
		if (array_key_exists('v', $this->inputParams)) $this->version = $this->inputParams['v'];
		
		if (array_key_exists('itemid', $this->inputParams)) $this->itemId = intval($this->inputParams['itemid']);
		
		if (array_key_exists('itemtype', $this->inputParams))
		{
			$this->itemType = intval($this->inputParams['itemtype']);
			$this->isItemTable = false;
			$this->tableFields = self::$ITEMTYPE_FIELDS;
		}
		
		if (array_key_exists('type', $this->inputParams))
		{
			$this->itemType = intval($this->inputParams['type']);
			$this->isItemTable = false;
			$this->tableFields = self::$ITEMTYPE_FIELDS;
		}
		
		if (array_key_exists('equiptype', $this->inputParams))
		{
			$this->equipType = intval($this->inputParams['equiptype']);
			$this->isItemTable = false;
			$this->tableFields = self::$ITEMTYPE_FIELDS;
		}
		
		if (array_key_exists('weapontype', $this->inputParams))
		{
			$this->weaponType = intval($this->inputParams['weapontype']);
			$this->isItemTable = false;
			$this->tableFields = self::$ITEMTYPE_FIELDS;
		}
		
		if (array_key_exists('armortype', $this->inputParams))
		{
			$this->armorType = intval($this->inputParams['armortype']);
			$this->isItemTable = false;
			$this->tableFields = self::$ITEMTYPE_FIELDS;
		}
		
		if (array_key_exists('notransform', $this->inputParams)) $this->noTransform = true;
		if (array_key_exists('keepblank', $this->inputParams)) $this->keepBlankFields = true;
		if (array_key_exists('keepinvariant', $this->inputParams)) $this->keepInvariantFields = true;
		if (array_key_exists('showlimit', $this->inputParams)) $this->showLimits = true;
		if (array_key_exists('extrait', $this->inputParams)) $this->useExTraitName = true;
		
		if (array_key_exists('sort', $this->inputParams))
		{
			$this->sortFields = array();
			$sortFields = preg_split("/,/", $this->inputParams['sort']);
			
			foreach ($sortFields as $field)
			{
				$result = preg_match("|^([a-zA-Z0-9_]+)|s", trim($field), $matches);
				if ($result) $this->sortFields[] = $matches[1];
			}
		}
		
		if (array_key_exists('output', $this->inputParams))
		{
			$this->inputParams['output'] = strtolower($this->inputParams['output']);
			
			if ($this->inputParams['output'] == "csv") 
				$this->outputType = "csv";
			elseif ($this->inputParams['output'] == "html")
				$this->outputType = "html";
			elseif ($this->inputParams['output'] == "wiki")
				$this->outputType = "wiki";
			
		}
		
		if (array_key_exists('fields', $this->inputParams))
		{
			$fields = preg_split("/,/", $this->inputParams['fields']);
			$this->tableFields = array();
			
			foreach ($fields as $field)
			{
				$result = preg_match("|^([a-zA-Z0-9_]+)|s", trim($field), $matches);
				if ($result) $this->tableFields[] = $matches[1];
			}
		}
		
		if ($this->outputType == "csv")
			$this->SetCsvStrings();
		else if ($this->outputType == "html")
			$this->SetHtmlStrings();
		elseif ($this->outputType == "wiki")
			$this->SetWikiStrings();
		
		return true;
	}
	
	
	public function SetCsvStrings()
	{
		$this->tableStartText = "";
		$this->tableEndText = "";
		$this->colStartText = "\"";
		$this->colEndText = "\"";
		$this->colHeaderStartText = "\"";
		$this->colHeaderEndText = "\"";
		$this->colHeaderSepText = ",";
		$this->rowStartText = "";
		$this->rowEndText = "\n";
		$this->rowSepText = "";
		$this->colSepText = ",";
	}
	
	
	public function SetHtmlStrings()
	{
		$this->tableStartText = "<table border='1' cellpadding='0' cellspacing='0' class='esodmi_table'>\n";
		$this->tableEndText = "</table>\n";
		$this->colStartText = "<td>";
		$this->colEndText = "</td>";
		$this->colHeaderStartText = "<th>";
		$this->colHeaderEndText = "</th>";
		$this->colHeaderSepText = "";
		$this->rowStartText = "<tr>";
		$this->rowEndText = "</tr>\n";
		$this->rowSepText = "";
		$this->colSepText = "";
	}
	
	
	public function SetWikiStrings()
	{
		$this->tableStartText = "{| class=wikitable\n";
		$this->tableEndText = "|}\n";
		$this->colStartText = "| ";
		$this->colEndText = "\n";
		$this->colHeaderStartText = "! ";
		$this->colHeaderEndText = "\n";
		$this->colHeaderSepText = "";
		$this->rowStartText = "|-\n";
		$this->rowEndText = "";
		$this->rowSepText = "";
		$this->colSepText = "";
	}
	
	
	public function FormatDescriptionString($desc)
	{
		if ($this->outputType == "html")
			$output = preg_replace("#\|c([0-9a-fA-F]{6})([a-zA-Z \-0-9\.]+)\|r#s", "<div class='esodmi_desc_$2' style='display:inline;'>$2</div>", $desc);
		else
			$output = preg_replace("#\|c([0-9a-fA-F]{6})([a-zA-Z \-0-9\.]+)\|r#s", "$2", $desc);
		
		$output = $this->EscapeQuotes($output);
		return $output;
	}
	
	
	public function MakeItemIdLink($itemId)
	{
		if ($this->outputType == "html")
		{
			return "<a href='//esoitem.uesp.net/itemLink.php?itemid=$itemId'>$itemId</a>";
		}
		elseif ($this->outputType == "wiki")
		{
			return "[//esoitem.uesp.net/itemLink.php?itemid=$itemId $itemId]";
		}
		
		return $itemId;
	}
	
	
	public function MakeItemNameLink($itemName, $record)
	{
		$itemId = $record['itemId'];
		
		if ($this->outputType == "html")
		{
			return "<a href='//esoitem.uesp.net/itemLink.php?itemid=$itemId'>$itemName</a>";
		}
		elseif ($this->outputType == "wiki")
		{
			return "[//esoitem.uesp.net/itemLink.php?itemid=$itemId $itemName]";
		}
		
		$itemName = $this->EscapeQuotes($itemName);
		return $itemName;
	}
	
	
	public function EscapeQuotes($inputString)
	{
		return str_replace('"', '""', $inputString);;
	}
	
	
	public function MakeIconLink($icon)
	{
		if ($this->outputType == "html")
		{
			$link = MakeEsoIconLink($icon);
			return "<img src='$link' />";
		}
		elseif ($this->outputType == "wiki")
		{
			$icon = preg_replace("/\.dds$/", ".png", $icon);
			return MakeEsoIconLink($icon);
		}
		
		return $icon;
	}
	
	
	public function MakeItemTraitText($trait, $record)
	{
		$traitText = GetEsoItemTraitText($trait, $this->version);
		if (!$this->useExTraitName) return $traitText;
		
		$matches = array();
		$result = preg_match("/([0-9\-\.\%]+)/s", $record['traitDesc'], $matches);
		if (!$result) return $traitText;
		
		return "$traitText ({$matches[1]})";
	}
	
	
	public function OutputHtmlHeader()
	{
		ob_start("ob_gzhandler");
		header("Expires: 0");
		header("Pragma: no-cache");
		header("Cache-Control: no-cache, no-store, must-revalidate");
		header("Pragma: no-cache");
		
		if ($this->outputType == "html")
			header("content-type: text/html");
		else
			header("content-type: text/plain");
	}
	
	
	public function LoadRecords()
	{
		$itemId = $this->itemId;
		if ($itemId <= 0) return $this->ReportError("ERROR: No itemid specified!");
		
		$query = "SELECT * FROM minedItem". $this->GetTableSuffix() ." WHERE itemId=$itemId";
		if (count($this->sortFields) > 0) $query .= " ORDER BY " . implode(",", $this->sortFields);
		$query .= " LIMIT " . self::SELECT_LIMIT;
		
		$result = $this->db->query($query);
		if (!$result) return $this->ReportError("ERROR: Database query error!");
		
		$this->itemRecords = array();
		if ($result->num_rows === 0) return false;
		$result->data_seek(0);
		
		while (($row = $result->fetch_assoc()))
		{
			$this->itemRecords[] = $row;
		}
		
		$this->CheckFieldData();
		return true;
	}
	
	
	public function LoadTypeRecords()
	{
		$whereTypes = array();
		
		if ($this->itemType > 0) $whereTypes[] = "type={$this->itemType}";
		if ($this->equipType > 0) $whereTypes[] = "equiptype={$this->equipType}";
		if ($this->weaponType > 0) $whereTypes[] = "weapontype={$this->weaponType}";
		if ($this->armorType > 0) $whereTypes[] = "armortype={$this->armorType}";
		
		if (count($whereTypes) == 0)  return $this->ReportError("ERROR: No item types specified!");
		$where = implode(" AND ", $whereTypes);
		
		$query = "SELECT * FROM minedItemSummary". $this->GetTableSuffix() ." WHERE $where ORDER BY name;";
		$result = $this->db->query($query);
		if (!$result) return $this->ReportError("ERROR: Database query error! " . $this->db->error);
	
		$this->itemRecords = array();
		$result->data_seek(0);
	
		while (($row = $result->fetch_assoc()))
		{
			$row['link'] = $this->MakeItemLink($row);
			$this->itemRecords[] = $row;
		}
	
		$this->CheckFieldData();
		return true;
	}
	
	
	public function MakeItemLink($item)
	{
		$itemId = $item['itemId'];
		
		$style = $item['style'];
		$intType = $item['internalSubtype'];
		$intLevel = $item['internalLevel'];
		
		$enchantId = $item['enchantId'];
		$enchantLevel = $item['enchantLevel'];
		$enchantType = $item['enchantSubtype'];
		$enchantCharges = $item['maxCharges'];
		$condition = $item['condition'];
		
		if ($enchantId == null) $enchantId = 0;
		if ($enchantLevel == null) $enchantLevel = 0;
		if ($enchantType == null) $enchantType = 0;
		if ($enchantCharges == null) $enchantCharges = 0;
		if ($condition == null) $condition = $enchantCharges;
		
		if ($style == null) $style = 0;
		if ($intType == null) $intType = 1;
		if ($intLevel == null) $intLevel = 1;
		
		$itemLink = "|H0:item:$itemId:$intType:$intLevel:$enchantId:$enchantType:$enchantLevel:0:0:0:0:0:0:0:0:0:$style:0:0:0:$condition:0|h|h";
		
		return $itemLink;
	}
	
	
	
	public function LoadFields()
	{
		$this->validFields = array();
		
		if ($this->weaponType > 0 || $this->armorType > 0 || $this->equipType > 0 || $this->itemType > 0)
		{
			$query = "DESCRIBE minedItemSummary". $this->GetTableSuffix() .";";
			$this->validFields['link'] = true;
			$this->allFields[] = 'link';
			$this->fieldRecords[] = 'link';
		}
		else 
		{
			$query = "DESCRIBE minedItem". $this->GetTableSuffix() .";";
		}
		
		$result = $this->db->query($query);
		if (!$result) return $this->ReportError("ERROR: Database query error!");
		
		$this->fieldRecords = array();
		if ($result->num_rows === 0) return false;
		
		while (($row = $result->fetch_assoc()))
		{
			$this->fieldRecords[] = $row;
			$this->allFields[] = $row['Field'];
			$this->validFields[$row['Field']] = true;
		}
		
		return true;
	}
	
	
	public function IsValidField($field)
	{
		return array_key_exists($field, $this->validFields);
	}
	
	
	public function CheckFieldData()
	{
		if ($this->keepBlankFields && $this->keepInvariantFields) return true;
		$newFields = array();
		$rowCount = count($this->itemRecords);
		
		foreach ($this->tableFields as $field)
		{
			$firstValue = $this->itemRecords[0][$field];
			$isBlank = true;
			$isInvariant = true;
			
			foreach ($this->itemRecords as $record)
			{
				if ($field == 'trait' && $this->useExTraitName)
				{
					if ($record[$field] !== "") $isBlank = false;
					if ($record[$field] !== $firstValue) $isInvariant = false;
					if ($record['traitDesc'] !== $this->itemRecords[0]['traitDesc']) $isInvariant = false;
				}
				else
				{
					if ($record[$field] !== "") $isBlank = false;
					if ($record[$field] !== $firstValue) $isInvariant = false;
				}
				
				if (!$isBlank && !$isInvariant) break;
			}
			
			if ($isBlank) $isInvariant = false;
			
			if ((!$isBlank || ($isBlank && $this->keepBlankFields)) && (!$isInvariant || $rowCount <= 1 || ($isInvariant && $this->keepInvariantFields)))
			{
				$newFields[] = $field;
			}
		}
		
		$this->tableFields = $newFields;
		return true;
	}
	
	
	public function SetTableFields()
	{
		$newFields = array();
		$newFieldMap = array();
		
			// Parse out special fields like "all" or "default"
		foreach ($this->tableFields as $field)
		{
			if ($field == "all") 
				$newFields += $this->allFields;
			elseif ($field == "default")
				$newFields += self::$DEFAULT_FIELDS;
			else
				$newFields[] = $field;
		}
		
		$this->tableFields = array();
		
			// Remove duplicates and invalid field names
		foreach ($newFields as $field)
		{
			if ($this->IsValidField($field) && !array_key_exists($field, $newFieldMap))
			{
				$this->tableFields[] = $field;
				$newFieldMap[$field] = true;
			}
		}
		
		return true;
	}
	
	
	public function CheckSortFields()
	{
		$newSortFields = array();
		
		foreach ($this->sortFields as $field)
		{
			if ($field == "none")
				$newSortFields = array();
			elseif ($this->IsValidField($field))
				$newSortFields[] = $field;
		}
		
		$this->sortFields = $newSortFields;
	}
	
	
	public function TransformFieldValue($field, $value, $record)
	{
		if ($this->noTransform || !array_key_exists($field, $this->TRANSFORM_FIELDS)) return $value;
		return call_user_func($this->TRANSFORM_FIELDS[$field], $value, $record);
	}
	
	
	public function OutputRecords()
	{
		$numRows = count($this->itemRecords);
		$numCols = count($this->tableFields);
		
		if ($numCols == 0)
		{
			print("No fields to output! Try with the 'keepinvariant' option.");
			return true;
		}
		
		if ($numRows == 0)
		{
			print("No rows to output!");
			return true;
		}
		
		print($this->tableStartText);
		print($this->rowStartText);
		$col = 0;
		
		foreach ($this->tableFields as $field)
		{
			print($this->colHeaderStartText);
			print($field);
			print($this->colHeaderEndText);
			if ($col < $numCols-1) print($this->colHeaderSepText);
			$col++;
		}
		
		print($this->rowEndText);
		print($this->rowSepText);
		$row = 0;
		
		foreach ($this->itemRecords as $record)
		{
			if ($this->showLimits && !($row == 0 || $row == $numRows-1)) {
				$row++;
				continue;
			}
					
			print($this->rowStartText);
			$col = 0;
			
			foreach ($this->tableFields as $field)
			{
				if (array_key_exists($field, $record))
					$value = $this->TransformFieldValue($field, $record[$field], $record);
				else
					$value = "";
				
				print($this->colStartText);
				print($value);
				print($this->colEndText);
				if ($col < $numCols-1) print($this->colSepText);
				$col++;
			}
			
			print($this->rowEndText);
			if ($row < $numRows-1) print($this->rowSepText);
			$row++;
		}
		
		print($this->tableEndText);
	}
	
	
	public function Output()
	{
		if (!CanViewEsoLogVersion($this->version))
		{
			print("Permission Denied!");
			return;
		}
		
		if (!$this->LoadFields()) return false;
		$this->SetTableFields();
		$this->CheckSortFields();
		
		if ($this->itemType > 0 || $this->equipType > 0 || $this->weaponType > 0 || $this->armorType > 0)
		{
			if (!$this->LoadTypeRecords()) return false;
		}
		elseif (!$this->LoadRecords())
		{
			return false;
		}
		
		$this->OutputRecords();
	}
	
	
};


$g_EsoDumpMinedItems = new CEsoDumpMinedItems();
$g_EsoDumpMinedItems->Output();

