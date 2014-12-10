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
	public $itemRecords = array();
	public $fieldRecords = array();
	
	private $tableFields = array("all");
	private $tableStartText = "";
	private $tableEndText = "";
	private $colStartText = "";
	private $colEndText = "";
	private $rowStartText = "";
	private $rowEndText = "";
	private $rowSepText = "";
	private $colSepText = "";
	
	
	public function __construct()
	{
		error_reporting(E_ALL);
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
			$result = preg_match("|^([a-zA-Z0-9_]+)|s", $this->inputParams['sort'], $matches);
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
		
		return true;
	}
	
	
	public function SetCsvStrings()
	{
		$this->tableStartText = "";
		$this->tableEndText = "";
		$this->colStartText = "";
		$this->colEndText = "";
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
		$this->rowStartText = "<tr>";
		$this->rowEndText = "</tr>\n";
		$this->rowSepText = "";
		$this->colSepText = "";
	}
	
	
	public function SetWikiStrings()
	{
		$this->tableStartText = "{| class='wikitable'\n";
		$this->tableEndText = "|}\n";
		$this->colStartText = "|| ";
		$this->colEndText = "";
		$this->rowStartText = "|-\n";
		$this->rowEndText = "\n";
		$this->rowSepText = "";
		$this->colSepText = "";
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
		
		$this->itemRecords = array();
		if ($result->num_rows === 0) return $records;
		$result->data_seek(0);
		
		while (($row = $result->fetch_assoc()))
		{
			$this->itemRecords[] = $row;
		}
		
		return true;
	}
	
	
	public function LoadFields()
	{
		$query = "DESCRIBE minedItem;";
		$result = $this->db->query($query);
		if (!$result) return $this->ReportError("ERROR: Database query error!");
		
		$this->fieldRecords = array();
		if ($result->num_rows === 0) return false;
		
		while (($row = $result->fetch_assoc()))
		{
			$this->fieldRecords[] = $row;
		}
		
		return true;
	}
	
	
	public function SetTableFields()
	{
		$doAllFields = false;
		$newFields = array();
		
		foreach ($this->tableFields as $key => $field)
		{
			if ($field == "all") 
				$doAllFields = true;
			else
				$newFields[] = $field;
		}
		
		if ($doAllFields)
		{
			$newFields[] = "id";
			$newFields[] = "itemId";
			$newFields[] = "level";
			$newFields[] = "quality";
			$newFields[] = "value";
			$newFields[] = "internalLevel";
			$newFields[] = "internalSubtype";
			$newFields[] = "weaponPower";
			$newFields[] = "armorRating";
		}
		
		$this->tableFields = $newFields;
	}
	
	
	public function OutputRecords()
	{
		$numRows = count($this->itemRecords);
		$numCols = count($this->tableFields);
		
		print($this->tableStartText);
		print($this->rowStartText);
		$col = 0;
		
		foreach ($this->tableFields as $key => $field)
		{
			print($this->colStartText);
			print($field);
			print($this->colEndText);
			if ($col < $numCols-1) print($this->colSepText);
			$col++;
		}
		
		print($this->rowEndText);
		print($this->rowSepText);
		$row = 0;
		
		foreach ($this->itemRecords as $record)
		{
			print($this->rowStartText);
			$col = 0;
			
			foreach ($this->tableFields as $field)
			{
				
				if (array_key_exists($field, $record))
					$value = $record[$field];
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
		if (!$this->LoadFields()) return false;
		$this->SetTableFields();
		
		if (!$this->LoadRecords()) return false;
		$this->OutputRecords();
	}
	
	
};


$g_EsoDumpMinedItems = new CEsoDumpMinedItems();
$g_EsoDumpMinedItems->Output();


?>
