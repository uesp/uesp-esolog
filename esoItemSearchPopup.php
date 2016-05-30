<?php 


require_once("/home/uesp/secrets/esolog.secrets");
require_once("esoCommon.php");


class CEsoItemSearchPopup 
{
	
	public $inputParams = array();
	
	public $inputText = "";
	public $inputLimit = 100;
	
	public $resultItems = array();
	public $resultError = array("error" => true);
	
	
	public function __construct()
	{
		$this->inputParams = $_REQUEST;
		$this->ParseInputParams();
		$this->InitDatabase();
	}
	
	
	public function ReportError($errorMsg)
	{
		print($errorMsg);
		error_log($errorMsg);
		
		$this->resultError[] = $errorMsg;
		
		return false;
	}
	
	
	public function OutputHeader()
	{
		ob_start("ob_gzhandler");
	
		header("Expires: 0");
		header("Pragma: no-cache");
		header("Cache-Control: no-cache, no-store, must-revalidate");
		header("Pragma: no-cache");
		header("content-type: application/json");
	}
	
	
	public function ParseInputParams ()
	{
		if (array_key_exists('text', $this->inputParams)) $this->inputText = urldecode($this->inputParams['text']);
	}
	
	
	public function InitDatabase()
	{
		global $uespEsoLogReadDBHost, $uespEsoLogReadUser, $uespEsoLogReadPW, $uespEsoLogDatabase;
	
		$this->db = new mysqli($uespEsoLogReadDBHost, $uespEsoLogReadUser, $uespEsoLogReadPW, $uespEsoLogDatabase);
		if ($this->db->connect_error) return $this->ReportError("ERROR: Could not connect to mysql database!");
	
		return true;
	}
	
	
	public function CreateQuery()
	{
		$query = "SELECT itemId, name, icon FROM minedItemSummary ";
		$whereQuery = array();
		
		if ($this->inputText != "")
		{
			$safeText = $this->db->real_escape_string($this->inputText);
			$whereQuery[] = "(name LIKE '%$safeText%' OR description LIKE '%$safeText%')";
		}
		
		if (count($whereQuery) > 0)
		{
			$query .= "WHERE " . implode(" AND ", $whereQuery) . " ";
		}
		
		$query .= "LIMIT ". $this->inputLimit . " ";
		$query .= ";";
		
		return $query;
	}
	
	
	public function LoadItems()
	{
		$query = $this->CreateQuery();
		$result = $this->db->query($query);
		if (!$result) return $this->ReportError("Error: Database query error loading items! " . $this->db->error . "\n" . $query);
		
		while (($row = $result->fetch_assoc()))
		{
			$this->resultItems[] = $row;	
		}
		
		return true;
	}
	
	
	public function OutputJson($object)
	{
		print(json_encode($object));
	}
	
	
	public function Run()
	{
		$this->OutputHeader();
		
		if ($this->LoadItems())
			$this->OutputJson($this->resultItems);
		else
			$this->OutputJson($this->resultError);
			
		return true;		
	}
	
};


$g_EsoItemSearchPopup = new CEsoItemSearchPopup();
$g_EsoItemSearchPopup->Run();