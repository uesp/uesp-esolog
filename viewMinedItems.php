<?php

/*
 * viewMinedItems.php -- by Dave Humphrey (dave@uesp.net), December 2014
 * 
 * Very basic browser for the ESO mined items data.
 * 
 * TODO:
 * 
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
	
	public $typeRecords = array();
	public $equipTypeRecords = array();
	
	
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
		$query = "SELECT COUNT(*) AS count, type FROM minedItemSummary GROUP BY type;";
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
		$query = "SELECT COUNT(*) AS count, equipType FROM minedItemSummary WHERE type={$this->viewType} GROUP BY equipType;";
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
	
	
	public function MakeTitleString()
	{
		if ($this->viewType >= 0)
		{
			$typeName = GetEsoItemTypeText($this->viewType);
			return "$typeName Items";
		}
		else
			return "All Items";
	}
	
	
	public function MakeContentBlock()
	{
		if ($this->viewType >= 0)
			return $this->MakeLevel2Block();
		else
			return $this->MakeTypeBlock();
	}
	
	
	public function MakeLevel2Block()
	{
		if ($this->viewType == 1 || $this->viewType == 2) return $this->MakeEquipTypeBlock();
		
		$output = "";
		return $output;
	}
	
	
	public function MakeEquipTypeBlock()
	{
		if (!$this->LoadEquipTypeRecords()) return "";
		
		$output = "<ul>\n";
		$totalItems = 0;
		$type = $this->viewType;
		$typeName = GetEsoItemTypeText($this->viewType);
		
		foreach ($this->equipTypeRecords as $record)
		{
			$equipType = $record['equipType'];
			$equipTypeName = $record['equipTypeName'];
			$count = $record['count'];
			$totalItems += $count;
			
			$output .= "<li><a href='?type=$type&equiptype=$equipType'>$equipTypeName ($count items)</a></li>";
		}
		
		$output .= "</ul>\n";
		$output .= "Total of $totalItems $typeName items";
		return $output;
	}
	
	
	public function MakeTypeBlock()
	{
		if (!$this->LoadTypeRecords()) return "";
		
		$output = "<ul>\n";
		$totalItems = 0;
		
		foreach ($this->typeRecords as $record)
		{
			$type = $record['type'];
			$typeName = $record['typeName'];
			$count = $record['count'];
			$totalItems += $count;
			
			$output .= "<li><a href='?type=$type'>$typeName ($count items)</a></li>";
		}
		
		$output .= "</ul>\n";
		$output .= "Total of $totalItems items in database.";
		return $output;
	}
	
	
	public function ViewItems()
	{
		$this->OutputHtmlHeader();
		
		$replacePairs = array(
				'{title}' => $this->MakeTitleString(),
				'{content}' => $this->MakeContentBlock(),
		);
		
		$output = strtr($this->htmlTemplate, $replacePairs);
		print($output);
	}
	
	
};

$g_EsoViewMinedItems = new CEsoViewMinedItems();
$g_EsoViewMinedItems->ViewItems();

?>