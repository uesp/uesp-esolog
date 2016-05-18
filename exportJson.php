<?php

require_once("/home/uesp/secrets/esolog.secrets");
require_once("esoCommon.php");


class CEsoLogJsonExport 
{
	
	public $db = null;
	
	public $version = "";
	public $inputId = "";
	public $inputLevel = "";
	public $inputQuality = "";
	public $inputItemType = "";
	public $inputEquipType = "";
	public $inputWeaponType = "";
	public $inputArmorType = "";
	public $inputIntLevel = "";
	public $inputIntType = "";
	public $exportTables = array();
	public $outputData = array();
	public $outputJson = "";
	
	
	public $VALID_TABLES = array(
			"cpDisciplines",
			"cpSkills",
			"cpSkillDescriptions",
			"minedItem",
			"minedItemSummary",
			"minedSkills",
			"skillCoef",
			"playerSkills",
			"setSummary",
			"achievementCategories",
			"achievementCriteria",
			"achievements",
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
	);
	
	
	public function __construct()
	{
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
	
		return true;
	}
	
	
	private function IsValidTable($table)
	{
		if (!in_array($table, $this->VALID_TABLES)) return false;
		
		return true;
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
		$query = "SELECT * FROM $table";
		
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
			
				// Currently far too slow (2-3 minutes for a typical query with 10-100k records)
			//if ($this->inputLevel != "" && $this->inputQuality != "") $isValid = true;
				
			if (!$isValid) return $this->ReportError("Error: Missing required item id!", 400);
			
			if ($this->inputId != "")
			{
				$itemId = (int) $this->inputId;
				if ($itemId <= 0) return $this->ReportError("Error: Invalid item id '{$this->inputId} received!", 400);
				$where[] = "itemId=$itemId";
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
		}
		else if ($table == "minedItemSummary")
		{
			
			if ($this->inputId != "")
			{
				$itemId = (int) $this->inputId;
				if ($itemId <= 0) return $this->ReportError("Error: Invalid item id '{$this->inputId} received!", 400);
				$where[] = "itemId=$itemId";
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
		
		$query = "SELECT * FROM $table";
		if (count($where) > 0) $query .= " WHERE " . implode(" AND ", $where);
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
		$this->ExportTables();
		
		$this->outputJson = json_encode($this->outputData);
		print($this->outputJson);
	}
	
};


$g_ExportJson = new CEsoLogJsonExport();
$g_ExportJson->Export();
