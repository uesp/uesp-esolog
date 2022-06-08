<?php

require_once("/home/uesp/secrets/esolog.secrets");
require_once("esoCommon.php");


class CEsoLogJsonExport 
{
	
	public $db = null;
	
	public $version = "";
	public $inputId = "";
	public $inputIds = null;
	public $inputLevel = "";
	public $inputQuality = "";
	public $inputItemType = "";
	public $inputEquipType = "";
	public $inputWeaponType = "";
	public $inputArmorType = "";
	public $inputIntLevel = "";
	public $inputIntType = "";
	public $inputFields = "";
	public $inputTransmuteTrait = "";
	public $inputLimit = -1;
	public $exportTables = array();
	public $outputData = array();
	public $tableFields = array();
	public $outputJson = "";
	
	
	public $VALID_TABLES = array(
			#"cpDisciplines",		// Old CP tables
			#"cpSkills",
			#"cpSkillDescriptions",
			"cp2Disciplines",
			"cp2Skills",
			"cp2SkillLinks",
			"cp2ClusterRoots",
			"cp2SkillDescriptions",
			"minedItem",
			//"minedItem30pts",
			//"minedItem31pts",
			//"minedItem32pts",
			//"minedItem33pts",
			"minedItemSummary",
			//"minedItemSummary30pts",
			//"minedItemSummary31pts",
			//"minedItemSummary32pts",
			//"minedItemSummary33pts",
			//"minedItemSummary34pts",
			"minedSkills",
			//"minedSkills30pts",
			//"minedSkills31pts",
			//"minedSkills32pts",
			//"minedSkills33pts",
			"skillCoef",
			"playerSkills",
			"setSummary",
			//"setSummary30pts",
			//"setSummary31pts",
			//"setSummary32pts",
			//"setSummary33pts",
			//"setSummary34pts",
			"achievementCategories",
			"achievementCriteria",
			"achievements",
			"book",
			"quest",
			"questCondition",
			"questItem",
			"questReward",
			"questStep",
			"uniqueQuest",
			"skillTree",
			//"skillTree30pts",
			//"skillTree31pts",
			//"skillTree32pts",
			//"skillTree33pts",
			"skillTooltips",
			//"skillTooltips32pts",
			//"skillTooltips33pts",
			//"skillTooltips34pts",
	);
	
	
	public $TABLE_IDS = array(
			"cpDisciplines" => "name",
			"cpSkills" => "abilityId",
			"cpSkillDescriptions" => "abilityId",
			"minedItem" => "itemId",
			"minedItemSummary" => "itemId",
			"minedSkills" => "id",
			"skillCoef" => "id",
			"playerSkills" => "id",
			"setSummary" => "setName",
			"achievementCategories" => "name",
			"achievementCriteria" => "achievementId",
			"achievements" => "id",
			"book" => "bookId",
			"quest" => "internalId",
			"questCondition" => "id",
			"questItem" => "id",
			"questReward" => "id",
			"questStep" => "id",
			"uniqueQuest" => "internalId",
			"skillTree" => "abilityId",
			"skillTooltips" => "id",
	);
	
	
	public function __construct()
	{
		//SetupUespSession();
		
		$this->SetInputParams();
		$this->ParseInputParams();
		$this->InitDatabase();
	}
	
	
	public function ReportError($errorMsg, $statusCode = 0)
	{
		error_log($errorMsg);
		
		if ($this->outputData['error'] == null) $this->outputData['error'] = array();
		$this->outputData['error'][] = $errorMsg;
		
		if ($statusCode > 0) header("X-PHP-Response-Code: " . $statusCode, true, $statusCode);
		
		return false;
	}
	
	
	private function InitDatabase()
	{
		global $uespEsoLogReadDBHost, $uespEsoLogReadUser, $uespEsoLogReadPW, $uespEsoLogDatabase;
		
		$this->db = new mysqli($uespEsoLogReadDBHost, $uespEsoLogReadUser, $uespEsoLogReadPW, $uespEsoLogDatabase);
		if ($this->db->connect_error) return $this->ReportError("ERROR: Could not connect to mysql database!", 500);
		
		$this->db->set_charset("utf8");
		
		UpdateEsoPageViews("exportJsonViews");
		
		return true;
	}
	
	
	private function GetTableSuffix()
	{
		return GetEsoItemTableSuffix($this->version);
	}
	
	
	private function ParseInputParams ()
	{
		if (array_key_exists('version', $this->inputParams)) $this->version = urldecode($this->inputParams['version']);
		
		if (array_key_exists('table', $this->inputParams))
		{
			$table = $this->inputParams['table'];
			
			if (is_array($table))
				$this->exportTables = array_merge($this->exportTables, $table);
			else
				$this->exportTables[] = $table;
		}
		
		if (array_key_exists('id', $this->inputParams)) $this->inputId = urldecode($this->inputParams['id']);
		if (array_key_exists('intlevel', $this->inputParams)) $this->inputIntLevel = (int) $this->inputParams['intlevel'];
		if (array_key_exists('inttype', $this->inputParams)) $this->inputIntType = (int) $this->inputParams['inttype'];
		if (array_key_exists('level', $this->inputParams)) $this->inputLevel = (int) $this->inputParams['level'];
		if (array_key_exists('quality', $this->inputParams)) $this->inputQuality = (int) $this->inputParams['quality'];
		if (array_key_exists('type', $this->inputParams)) $this->inputItemType = (int) $this->inputParams['type'];
		if (array_key_exists('equiptype', $this->inputParams)) $this->inputEquipType = (int) $this->inputParams['equiptype'];
		if (array_key_exists('weapontype', $this->inputParams)) $this->inputWeaponType = (int) $this->inputParams['weapontype'];
		if (array_key_exists('armortype', $this->inputParams)) $this->inputArmorType = (int) $this->inputParams['armortype'];
		if (array_key_exists('limit', $this->inputParams)) $this->inputLimit = (int) $this->inputParams['limit'];
		if (array_key_exists('transmutetrait', $this->inputParams)) $this->inputTransmuteTrait = (int) $this->inputParams['transmutetrait'];
		
		if (array_key_exists('fields', $this->inputParams)) 
		{
			$this->inputFields = $this->inputParams['fields'];
			$tableFields = explode(",", $this->inputFields);
			
			$this->tableFields = array();
			
			foreach ($tableFields as $field)
			{
				$result = preg_match("|^([a-zA-Z0-9_]+)|s", trim($field), $matches);
				if ($result && $matches[1] != "") $this->tableFields[] = $matches[1];
			}
		}
		
		if (array_key_exists('ids', $this->inputParams)) 
		{
			$ids = explode(",", $this->inputParams['ids']);
			
			foreach ($ids as $id)
			{
				$safeId = intval($id);
				if ($safeId > 0) $this->inputIds[] = $safeId;
			}
		}
		
		return true;
	}
	
	
	private function IsValidTable($table)
	{
		if (in_array($table, $this->VALID_TABLES)) return true;
		
			// Include versioned tables like 'XXX30' or 'XXX31pts'
		$table = preg_replace('#[0-9]+(pts)?$#', '', $table);
		if (in_array($table, $this->VALID_TABLES)) return true;
		
		return false;
	}
	
	
	private function SetInputParams ()
	{
		global $argv;
		global $_REQUEST;
		
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
	
	
	private function OutputHeader()
	{
		ob_start("ob_gzhandler");
		
		header("Expires: 0");
		header("Pragma: no-cache");
		header("Cache-Control: no-cache, no-store, must-revalidate");
		header("Access-Control-Allow-Origin: " . $_SERVER['HTTP_ORIGIN'] . "");
		header("content-type: application/json");
	}
	
	
	public function GetQuery($table)
	{
		$where = array();
		$query = "";
		
		if ($table == "playerSkills")
		{
			$table = "minedSkills";
			$where[] = "isPlayer=1";
		}
		else if ($table == "skillCoef")
		{
			$table = "minedSkills";
			$where[] = "numCoefVars>0";
		}
		
		if ($table == "minedItem")
		{
			$isValid = false;
			if ($this->inputId != "") $isValid = true;
			if ($this->inputIds) $isValid = true;
			
				// Currently far too slow (2-3 minutes for a typical query with 10-100k records)
			//if ($this->inputLevel != "" && $this->inputQuality != "") $isValid = true;
			
			if (!$isValid) return $this->ReportError("Error: Missing required item id!", 400);
			
			$minedTable = "minedItem{$this->GetTableSuffix()}";
			$summaryTable = "minedItemSummary{$this->GetTableSuffix()}";
			
			if ($this->inputTransmuteTrait != "")
			{
				$itemId = GetEsoTransmuteTraitItemId($this->inputTransmuteTrait, $this->inputEquipType);
				if ($itemId == null) $this->ReportError("Error: Unknown trait {$this->inputTransmuteTrait} found!");
				
				$query = "SELECT $minedTable.traitDesc, $summaryTable.trait, $minedTable.itemId, $minedTable.internalLevel, $minedTable.internalSubtype FROM $minedTable LEFT JOIN $summaryTable ON $minedTable.itemId=$summaryTable.itemId WHERE $minedTable.itemId='$itemId' AND $minedTable.internalLevel='{$this->inputIntLevel}' AND $minedTable.internalSubtype='{$this->inputIntType}' LIMIT 1;";
				return $query;
			}
			
			if ($this->inputId != "")
			{
				$itemId = (int) $this->inputId;
				if ($itemId <= 0) return $this->ReportError("Error: Invalid item id '{$this->inputId} received!", 400);
				$where[] = "itemId=$itemId";
			}
			elseif ($this->inputIds)
			{
				$idField = $this->TABLE_IDS[$table];
				$ids = implode(",", $this->inputIds);
				$where[] = "$idField IN ($ids)";
			}
			
			if ($this->inputIntLevel != "" && $this->inputIntType != "")
			{
				$where[] = "internalLevel=".(int)$this->inputIntLevel;
				$where[] = "internalSubtype=".(int)$this->inputIntType;
			}
			else if ($this->inputLevel != "" && $this->inputQuality != "")
			{
				$where[] = "level=".(int)$this->inputLevel;
				$where[] = "quality=".(int)$this->inputQuality;
			}
			
			$query = "SELECT minedItemSummary{$this->GetTableSuffix()}.*, minedItem{$this->GetTableSuffix()}.* FROM $table{$this->GetTableSuffix()} LEFT JOIN minedItemSummary{$this->GetTableSuffix()} ON minedItem{$this->GetTableSuffix()}.itemId = minedItemSummary{$this->GetTableSuffix()}.itemId ";
			if (count($where) > 0) $query .= " WHERE minedItem{$this->GetTableSuffix()}." . implode(" AND minedItem{$this->GetTableSuffix()}.", $where);
			if ($this->inputLimit > 0) $query .= " LIMIT ".$this->inputLimit." ";
			$query .= ";";
			
			return $query;
		}
		else if ($table == "minedItemSummary")
		{
			
			if ($this->inputId != "")
			{
				$itemId = (int) $this->inputId;
				if ($itemId <= 0) return $this->ReportError("Error: Invalid item id '{$this->inputId} received!", 400);
				$where[] = "itemId=$itemId";
			}
			elseif ($this->inputIds)
			{
				$idField = $this->TABLE_IDS[$table];
				$ids = implode(",", $this->inputIds);
				$where[] = "$idField IN ($ids)";
			}
			
			if ($this->inputItemType != "") $where[] = "type=".(int)$this->inputItemType;
			if ($this->inputEquipType != "") $where[] = "equipType=".(int)$this->inputEquipType;
			if ($this->inputWeaponType != "") $where[] = "weaponType=".(int)$this->inputWeaponType;
			if ($this->inputArmorType != "") $where[] = "armorType=".(int)$this->inputArmorType;
		}
		else if ($this->inputId != "")
		{
			$idField = $this->TABLE_IDS[$table];
			$id = $this->db->real_escape_string($this->inputId);
			
			if ($idField != "") $where[] = "$idField='$id'";
		}
		else if ($this->inputIds != null)
		{
			$idField = $this->TABLE_IDS[$table];
			$ids = implode(",", $this->inputIds);
			$where[] = "$idField IN ($ids)";
		}
		
		$fields = "*";
		
		if (count($this->tableFields) > 0)
		{
			$fields = implode(",", $this->tableFields);
		}
		
		$query = "SELECT $fields FROM $table{$this->GetTableSuffix()}";
		if (count($where) > 0) $query .= " WHERE " . implode(" AND ", $where);
		if ($this->inputLimit > 0) $query .= " LIMIT ".$this->inputLimit." ";
		$query .= ";";
		
		return $query;
	}
	
	
	public function LoadTable($table)
	{
		$query = $this->GetQuery($table);
		if ($query == "") return false;
		
		$result = $this->db->query($query);
		if (!$result) return $this->ReportError("Error: Failed to load records from '$table'!", 500);
		
		$this->outputData[$table] = array();
		$numRecords = 0;
		
		while (($row = $result->fetch_assoc()))
		{
			if ($table == "minedItem" && $row['link'] == null)
			{
				$itemId = $row['itemId'];
				$internalLevel = $row['internalLevel'];
				$internalSubtype = $row['internalSubtype'];
				$row['link'] =  "|H0:item:$itemId:$internalSubtype:$internalLevel:0:0:0:0:0:0:0:0:0:0:0:0:0:0:0:0:0:0|h|h";
			}
			
			$this->outputData[$table][] = $row;
			++$numRecords;
		}
		
		$this->outputData['numRecords'] += $numRecords;
		
		return true;
	}
	
	
	public function ExportTable($table)
	{
		if (!$this->IsValidTable($table))
		{
			$this->ReportError("Error: '$table' is not a valid table for JSON export!", 400);
			return false;
		}
		
		return $this->LoadTable($table);
	}
	
	
	public function ExportTables()
	{
		$this->outputData['numRecords'] = 0;
		if (count($this->exportTables) == 0) return $this->ReportError("Error: No tables specified for export!", 400);
		
		foreach ($this->exportTables as $table)
		{
			if ($table == "") continue;
			$this->ExportTable($table);
		}
	}
	
	
	public function Export()
	{
		$this->OutputHeader();
		
		if (!CanViewEsoLogVersion($this->version))
		{
			return " 'Permission Denied!' ";
		}
		
		$this->ExportTables();
		
		$this->outputJson = json_encode($this->outputData);
		print($this->outputJson);
	}
	
};


$g_ExportJson = new CEsoLogJsonExport();
$g_ExportJson->Export();
