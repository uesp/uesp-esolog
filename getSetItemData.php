<?php

require_once("/home/uesp/secrets/esolog.secrets");
require_once("esoCommon.php");


class CEsoLogGetSetItemData
{

	public $db = null;

	public $version = "";
	public $inputLevel = 66;
	public $inputQuality = 5;
	public $inputSetName = "";
	public $inputEquipType = 0;
	public $itemId = -1;
	
	public $outputData = array();
	public $outputJson = "";
	
	
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
	
		if (array_key_exists('level', $this->inputParams)) $this->inputLevel = (int) $this->inputParams['level'];
		if (array_key_exists('quality', $this->inputParams)) $this->inputQuality = (int) $this->inputParams['quality'];
		if (array_key_exists('equiptype', $this->inputParams)) $this->inputEquipType = (int) $this->inputParams['equiptype'];
		if (array_key_exists('setname', $this->inputParams)) $this->inputSetName = urldecode($this->inputParams['setname']);
	
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
	
		
	private function FindItemId()
	{
		$tableSuffix = $this->GetTableSuffix();
		$setName = $this->db->real_escape_string($this->inputSetName);
		$equipType = $this->inputEquipType;
		
		$query = "SELECT itemId from minedItemSummary$tableSuffix WHERE setName='$setName' AND equipType='$equipType' LIMIT 1;";
		$result = $this->db->query($query);
		if (!$result) return $this->ReportError("Database query error trying to find set item data!");
		if ($result->num_rows <= 0) return $this->ReportError("No item with specified set found!");
		
		$row = $result->fetch_assoc();
		$this->itemId = intval($row['itemId']);
		
		return true;		
	}	
	
	
	private function LoadItemData()
	{
		if ($this->inputSetName == "") return $this->ReportError("No set name specified!");
		if ($this->inputEquipType <= 0) return $this->ReportError("No equip type specified!");
		
		if (!$this->FindItemId()) return false;
		
		$itemId = $this->itemId;
		$tableSuffix = $this->GetTableSuffix();
		$level = $this->inputLevel;
		$quality = $this->inputQuality;
		
		$query = "SELECT * from minedItem$tableSuffix WHERE itemId=$itemId AND level=$level AND quality=$quality LIMIT 1;";
		$result = $this->db->query($query);
		if (!$result) return $this->ReportError("Database query error trying to load item data!");
		if ($result->num_rows <= 0) return $this->ReportError("No item with ID $itemId found with level $level and quality $quality!");
		
		$this->outputData['minedItem'] = array();
		$numRecords = 0;
		
		while (($row = $result->fetch_assoc()))
		{
			$this->outputData['minedItem'][] = $row;
			++$numRecords;
		}
		
		$this->outputData['numRecords'] += $numRecords;
				
		return true;
	}
	
	
	public function Export()
	{
		$this->OutputHeader();
		$this->LoadItemData();
	
		$this->outputJson = json_encode($this->outputData);
		print($this->outputJson);
	}
	
};



$g_ExportData = new CEsoLogGetSetItemData();
$g_ExportData->Export();
