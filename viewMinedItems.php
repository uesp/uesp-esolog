<?php

/*
 * viewMinedItems.php -- by Dave Humphrey (dave@uesp.net), December 2014
 * 
 * Very basic browser for the ESO mined items data.
 * 
 * TODO:
 * 		- Breadcrumb trail
 * 		- Advanced search?
 * 			- item type
 * 			- equip type
 * 			- armor/weapon type
 * 			- bind type
 * 			- text
 * 			- trait
 * 			- flags
 * 			- value
 * 			- weapon/armor rating
 * 			- style 
 */


	// Database users, passwords and other secrets
require("/home/uesp/secrets/esolog.secrets");
require("esoCommon.php");


class CEsoViewMinedItems
{
	
	const ESOVMI_HTML_TEMPLATE = "templates/esovmi_template.txt";
	
	public $inputParams = array();
	public $htmlTemplate = "";
	public $viewType = -1;
	public $viewEquipType = -1;
	public $viewArmorType = -1;
	public $viewWeaponType = -1;
	public $viewSearch = '';
	public $version = '';
	public $tableName = 'minedItemSummary';
	public $tableSuffix = '';
	public $fullTableName = 'minedItemSummary';
	public $extraQueryString = '';
	
	public $typeRecords = array();
	public $equipTypeRecords = array();
	public $weaponTypeRecords = array();
	public $armorTypeRecords = array();
	public $itemRecords = array();
	
	
	public function __construct()
	{
		$this->SetInputParams();
		$this->ParseInputParams();
		$this->InitDatabase();
	
		$this->htmlTemplate = file_get_contents(self::ESOVMI_HTML_TEMPLATE);
	}
	
	
	public function ReportError($errorMsg)
	{
		print($errorMsg);
		error_log($errorMsg);
		return false;
	}
	
	
	private function ParseInputParams ()
	{
		if (array_key_exists('type', $this->inputParams)) $this->viewType = intval($this->inputParams['type']);
		if (array_key_exists('equiptype', $this->inputParams)) $this->viewEquipType = intval($this->inputParams['equiptype']);
		if (array_key_exists('armortype', $this->inputParams)) $this->viewArmorType = intval($this->inputParams['armortype']);
		if (array_key_exists('weapontype', $this->inputParams)) $this->viewWeaponType = intval($this->inputParams['weapontype']);
		if (array_key_exists('search', $this->inputParams)) $this->viewSearch = $this->inputParams['search'];
		if (array_key_exists('version', $this->inputParams)) $this->version = $this->inputParams['version'];
		
		$this->tableSuffix = GetEsoItemTableSuffix($this->version);
		$this->fullTableName = $this->tableName . $this->tableSuffix;
		
		if ($this->version != '') 
		{
			$this->extraQueryString = '&version='.$this->version;
		}
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
	
	
	private function OutputHtmlHeader()
	{
		header("Expires: 0");
		header("Pragma: no-cache");
		header("Cache-Control: no-cache, no-store, must-revalidate");
		header("Pragma: no-cache");
		header("content-type: text/html");
	}
	
	
	public function LoadTypeRecords()
	{
		$query = "SELECT COUNT(*) AS count, type FROM {$this->fullTableName} GROUP BY type;";
		$result = $this->db->query($query);
		if (!$result) return $this->ReportError("ERROR: Database query error! " . $this->db->error);
		
		$this->typeRecords = array();
		
		while (($row = $result->fetch_assoc()))
		{
			$row['typeName'] = GetEsoItemTypeText($row['type']);
			$this->typeRecords[] = $row;
		}
		
		usort($this->typeRecords, function($a, $b) {
			return strcmp($a['typeName'], $b['typeName']);
		});
		
		return true;
	}
	
	
	public function LoadEquipTypeRecords()
	{
		$query = "SELECT COUNT(*) AS count, equipType FROM {$this->fullTableName} WHERE type={$this->viewType} GROUP BY equipType;";
		$result = $this->db->query($query);
		if (!$result) return $this->ReportError("ERROR: Database query error! " . $this->db->error);
		
		$this->equipTypeRecords = array();
		
		while (($row = $result->fetch_assoc()))
		{
			$row['equipTypeName'] = GetEsoItemEquipTypeText($row['equipType']);
			$this->equipTypeRecords[] = $row;
		}
		
		usort($this->equipTypeRecords, function($a, $b) {
			return strcmp($a['equipTypeName'], $b['equipTypeName']);
		});
		
			return true;
	}
	
	
	public function LoadWeaponTypeRecords()
	{
		$query = "SELECT COUNT(*) AS count, weaponType FROM {$this->fullTableName} WHERE type={$this->viewType} AND equipType={$this->viewEquipType} GROUP BY weaponType;";
		$result = $this->db->query($query);
		if (!$result) return $this->ReportError("ERROR: Database query error! " . $this->db->error);
		
		$this->weaponTypeRecords = array();
		
		while (($row = $result->fetch_assoc()))
		{
			$row['weaponTypeName'] = GetEsoItemWeaponTypeText($row['weaponType']);
			$this->weaponTypeRecords[] = $row;
		}
		
		usort($this->weaponTypeRecords, function($a, $b) {
			return strcmp($a['weaponTypeName'], $b['weaponTypeName']);
		});
		
			return true;
	}
	
	
	public function LoadArmorTypeRecords()
	{
		$query = "SELECT COUNT(*) AS count, armorType FROM {$this->fullTableName} WHERE type={$this->viewType} AND equipType={$this->viewEquipType} GROUP BY armorType;";
		$result = $this->db->query($query);
		if (!$result) return $this->ReportError("ERROR: Database query error! " . $this->db->error);
		
		$this->armorTypeRecords = array();
		
		while (($row = $result->fetch_assoc()))
		{
			$row['armorTypeName'] = GetEsoItemArmorTypeText($row['armorType']);
			$this->armorTypeRecords[] = $row;
		}
		
		usort($this->armorTypeRecords, function($a, $b) {
			return strcmp($a['armorTypeName'], $b['armorTypeName']);
		});
		
			return true;
	}
	
	
	public function LoadSearchRecords()
	{
		$safeSearch = $this->db->escape_string($this->viewSearch);
		$query = "SELECT itemId,name,trait FROM {$this->fullTableName} WHERE MATCH(name, description, abilityName, abilityDesc, enchantName, enchantDesc, traitDesc, setName, setBonusDesc1, setBonusDesc2, setBonusDesc3, setBonusDesc4, setBonusDesc5) AGAINST('$safeSearch' IN BOOLEAN MODE) ORDER BY name LIMIT 1000;";
		$result = $this->db->query($query);
		if (!$result) return $this->ReportError("ERROR: Database query error!" . $this->db->error);
		
		$this->itemRecords = array();
		
		while (($row = $result->fetch_assoc()))
		{
			$this->itemRecords[] = $row;
		}
		
		return true;
	}
		
	
	public function LoadItemRecords()
	{
		$where = array();
		
		if ($this->viewType >= 0) $where[] = " type={$this->viewType} ";
		if ($this->viewEquipType >= 0) $where[] = " equipType={$this->viewEquipType} ";
		if ($this->viewWeaponType >= 0) $where[] = " weaponType={$this->viewWeaponType} ";
		if ($this->viewArmorType >= 0) $where[] = " armorType={$this->viewArmorType} ";
		
		$whereQuery = "";
		if (count($where) > 0) $whereQuery = " WHERE ". implode(" AND ", $where) . " ";
		
		$query = "SELECT itemId,name,trait FROM {$this->fullTableName} $whereQuery ORDER BY name;";
		$result = $this->db->query($query);
		if (!$result) return $this->ReportError("ERROR: Database query error! " . $this->db->error);
		
		$this->itemRecords = array();
		
		while (($row = $result->fetch_assoc()))
		{
			$this->itemRecords[] = $row;
		}
		
		return true;
	}
	
	
	public function MakeTitleString()
	{
		if ($this->viewSearch != "")
		{
			return "Search Results For: " . $this->viewSearch;
		}
		elseif ($this->viewType >= 0 && $this->viewEquipType >= 0 && $this->viewWeaponType >= 0)
		{
			$typeName = GetEsoItemTypeText($this->viewType);
			$equipTypeName = GetEsoItemEquipTypeText($this->viewEquipType);
			$weaponType = GetEsoItemWeaponTypeText($this->viewWeaponType);
			return "$weaponType $equipTypeName $typeName Items";
		}
		elseif ($this->viewType >= 0 && $this->viewEquipType >= 0 && $this->viewArmorType >= 0)
		{
			$typeName = GetEsoItemTypeText($this->viewType);
			$equipTypeName = GetEsoItemEquipTypeText($this->viewEquipType);
			$armorType = GetEsoItemArmorTypeText($this->viewArmorType);
			return "$armorType $equipTypeName $typeName Items";
		}
		elseif ($this->viewType >= 0 && $this->viewEquipType >= 0)
		{
			$typeName = GetEsoItemTypeText($this->viewType);
			$equipTypeName = GetEsoItemEquipTypeText($this->viewEquipType);
			return "$equipTypeName $typeName Items";
		}
		else if ($this->viewType >= 0)
		{
			$typeName = GetEsoItemTypeText($this->viewType);
			return "$typeName Items";
		}
		else
			return "All Items";
	}
	
	
	public function MakeSearchBlock()
	{
		if (!$this->LoadSearchRecords()) return "";
		return $this->MakeItemBlock();
	}	
	
	
	public function MakeContentBlock()
	{
		if ($this->viewSearch != "")
			return $this->MakeSearchBlock();
		if ($this->viewType >= 0 && $this->viewEquipType >= 0 && $this->viewWeaponType >= 0)
			return $this->MakeWeaponBlock();
		elseif ($this->viewType >= 0 && $this->viewEquipType >= 0 && $this->viewArmorType >= 0)
			return $this->MakeArmorBlock();
		elseif ($this->viewType >= 0 && $this->viewEquipType >= 0)
			return $this->MakeLevel3Block();
		elseif ($this->viewType >= 0)
			return $this->MakeLevel2Block();
		else
			return $this->MakeTypeBlock();
	}
	
	
	public function MakeItemBlock()
	{
		$output = "<ol id='esovmi_itemlist'>\n";
		
		foreach ($this->itemRecords as $item)
		{
			$itemId = $item['itemId'];
			$name = $item['name'];
			if ($name == "") $name = "[blank]";
			
			if ($item['trait'] > 0)
			{
				$traitName = GetEsoItemTraitText($item['trait']);
				$output .= "<li><a href='itemLink.php?itemid=$itemId{$this->extraQueryString}'>$name ($traitName)</a></li>";
			}
			else
			{
				$output .= "<li><a href='itemLink.php?itemid=$itemId{$this->extraQueryString}'>$name</a></li>";
			}
		}
		
		if (count($this->itemRecords) == 0) $output .= "No items found!";
		
		$output .= "</ol>\n";
		return $output;
	}
	
	
	public function MakeOtherBlock()
	{
		if (!$this->LoadItemRecords()) return "";
		return $this->MakeItemBlock();
	}
	
	
	public function MakeWeaponBlock()
	{
		if (!$this->LoadItemRecords()) return "";
		return $this->MakeItemBlock();
	}
	
	
	public function MakeArmorBlock()
	{
		if (!$this->LoadItemRecords()) return "";
		return $this->MakeItemBlock();
	}
	
	
	public function MakeLevel3Block()
	{
		if ($this->viewType == 1) return $this->MakeWeaponTypeBlock();
		if ($this->viewType == 2) return $this->MakeArmorTypeBlock();
		return "";
	}
	
	
	public function MakeLevel2Block()
	{
		if ($this->viewType == 1 || $this->viewType == 2) return $this->MakeEquipTypeBlock();
		return $this->MakeOtherBlock();
	}
	
	
	public function MakeArmorTypeBlock()
	{
		if (!$this->LoadArmorTypeRecords()) return "";
		
		$output = "<ol id='esovmi_list'>\n";
		$totalItems = 0;
		$type = $this->viewType;
		$equipType = $this->viewEquipType;
		$typeName = GetEsoItemTypeText($this->viewType);
		$equipTypeName = GetEsoItemEquipTypeText($this->viewEquipType);
		
		foreach ($this->armorTypeRecords as $record)
		{
			$armorType = $record['armorType'];
			$armorTypeName = $record['armorTypeName'];
			$count = $record['count'];
			$totalItems += $count;
			
			$output .= "<li><a href='?type=$type&equiptype=$equipType&armortype=$armorType{$this->extraQueryString}'>$armorTypeName ($count items)</a></li>";
		}
	
		$output .= "</ol>\n";
		$output .= "Total of $totalItems $equipTypeName $typeName items";
		return $output;
	}
	
	
	public function MakeWeaponTypeBlock()
	{
		if (!$this->LoadWeaponTypeRecords()) return "";
		
		$output = "<ol id='esovmi_list'>\n";
		$totalItems = 0;
		$type = $this->viewType;
		$equipType = $this->viewEquipType;
		$typeName = GetEsoItemTypeText($this->viewType);
		$equipTypeName = GetEsoItemEquipTypeText($this->viewEquipType);
		
		foreach ($this->weaponTypeRecords as $record)
		{
			$weaponType = $record['weaponType'];
			$weaponTypeName = $record['weaponTypeName'];
			$count = $record['count'];
			$totalItems += $count;
				
			$output .= "<li><a href='?type=$type&equiptype=$equipType&weapontype=$weaponType{$this->extraQueryString}'>$weaponTypeName ($count items)</a></li>";
		}
		
		$output .= "</ol>\n";
		$output .= "Total of $totalItems $equipTypeName $typeName items";
		return $output;
	}
	
	
	public function MakeEquipTypeBlock()
	{
		if (!$this->LoadEquipTypeRecords()) return "";
		
		$output = "<ol id='esovmi_list'>\n";
		$totalItems = 0;
		$type = $this->viewType;
		$typeName = GetEsoItemTypeText($this->viewType);
		
		foreach ($this->equipTypeRecords as $record)
		{
			$equipType = $record['equipType'];
			$equipTypeName = $record['equipTypeName'];
			$count = $record['count'];
			$totalItems += $count;
			
			$output .= "<li><a href='?type=$type&equiptype=$equipType{$this->extraQueryString}'>$equipTypeName ($count items)</a></li>";
		}
		
		$output .= "</ol>\n";
		$output .= "Total of $totalItems $typeName items";
		return $output;
	}
	
	
	public function MakeTypeBlock()
	{
		if (!$this->LoadTypeRecords()) return "";
		
		$output = "<ol id='esovmi_list'>\n";
		$totalItems = 0;
		
		foreach ($this->typeRecords as $record)
		{
			$type = $record['type'];
			$typeName = $record['typeName'];
			$count = $record['count'];
			$totalItems += $count;
			
			$output .= "<li><a href='?type=$type{$this->extraQueryString}'>$typeName ($count items)</a></li>";
		}
		
		$output .= "</ol>\n";
		$output .= "Total of $totalItems items in database.";
		return $output;
	}
	
	
	public function MakeBreadCrumbBlock()
	{
		$output .= "&lt; <a href='?{$this->extraQueryString}'>All Items</a>";
		
		if ($this->viewSearch != "") return $output;
		if ($this->viewType < 0 && $this->viewEquipType < 0) return "";
		
		if ($this->viewType >= 0 && $this->viewEquipType >= 0)
		{
			$type = $this->viewType;
			$typeName = GetEsoItemTypeText($this->viewType);
			$output .= ": <a href='?type=$type{$this->extraQueryString}'>$typeName</a>";
		}
		
		if ($this->viewType >= 0 && $this->viewEquipType >= 0 && $this->viewWeaponType >= 0 ||
		    $this->viewType >= 0 && $this->viewEquipType >= 0 && $this->viewArmorType >= 0)
		{
			$type = $this->viewType;
			$equipType = $this->viewEquipType;
			$equipTypeName = GetEsoItemEquipTypeText($this->viewEquipType);
			$output .= ": <a href='?type=$type&equiptype=$equipType{$this->extraQueryString}'>$equipTypeName</a>";
		}
		
		return $output;
	}
	
	
	public function ViewItems()
	{
		$this->OutputHtmlHeader();
		
		$replacePairs = array(
				'{title}' => $this->MakeTitleString(),
				'{content}' => $this->MakeContentBlock(),
				'{search}' => $this->viewSearch,
				'{breadCrumb}' => $this->MakeBreadCrumbBlock(),
		);
		
		$output = strtr($this->htmlTemplate, $replacePairs);
		print($output);
	}
	
	
};

$g_EsoViewMinedItems = new CEsoViewMinedItems();
$g_EsoViewMinedItems->ViewItems();

?>