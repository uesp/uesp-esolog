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
	public $itemId = 0;
	public $sortField = "";
	public $db = null;
	public $outputType = "csv";
	public $inputParams = array();
	
	
	public function __construct()
	{
		error_reporting(E_ALL);
		
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
	
	
	public function InitDatabase()
	{
		global $uespEsoLogReadDBHost, $uespEsoLogReadUser, $uespEsoLogReadPW, $uespEsoLogDatabase;
		
		$this->db = new mysqli($uespEsoLogReadDBHost, $uespEsoLogReadUser, $uespEsoLogReadPW, $uespEsoLogDatabase);
		if ($this->db->connect_error) return $this->ReportError("ERROR: Could not connect to mysql database!");
	
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
		if (array_key_exists('itemid', $this->inputParams)) $this->itemId = intval($this->inputParams['itemid']);
		
		if (array_key_exists('sort', $this->inputParams))
		{
			$matches = array();
			$result = preg_match("|^([a-zA-Z0-9]+)|s", $this->inputParams['sort'], $matches);
			if ($result) $this->sortField = $matches[1];
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
		
		return true;
	}
	
	
	public function OutputHtmlHeader()
	{
		header("Expires: 0");
		header("Pragma: no-cache");
		header("Cache-Control: no-cache, no-store, must-revalidate");
		header("Pragma: no-cache");
		header("content-type: text/plain");
	}
	
	
	public function LoadRecords()
	{
		$itemId = $this->itemId;
		$sort = $this->sortField;
		
		if ($itemId <= 0) return $this->ReportError("ERROR: No itemid specified!");
		
		$query = "SELECT * FROM minedItem WHERE itemId=$itemId";
		if ($sort != "") $query .= " ORDER BY $sort";
		$query .= " LIMIT " . self::SELECT_LIMIT;
		
		$result = $this->db->query($query);
		if (!$result) return $this->ReportError("ERROR: Database query error!");
		
		$records = array();
		if ($result->num_rows === 0) return $records;
		$result->data_seek(0);
		
		while (($row = $result->fetch_assoc()))
		{
			$records[] = $row;
		}
		
		return $records;
	}
	
	
	public function OutputRecords($records)
	{
		print("id, itemid, level, quality, value, intlevel, intsubtype, weaponPower, armorRating\n");
		
		foreach ($records as $key => $value)
		{
			print("${value['id']}, ${value['itemId']}, ${value['level']}, ${value['quality']}, ${value['value']}, ${value['internalLevel']}, ${value['internalSubtype']}, ${value['weaponPower']}, ${value['armorRating']},\n");
		}
		
	}
	
	
	public function Output()
	{
		$records = $this->LoadRecords();
		if (!$records) return false;
		
		$this->OutputRecords($records);
	}
	
	
};


$g_EsoDumpMinedItems = new CEsoDumpMinedItems();
$g_EsoDumpMinedItems->Output();


?>
