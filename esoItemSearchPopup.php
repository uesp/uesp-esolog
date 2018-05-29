<?php 


require_once("/home/uesp/secrets/esolog.secrets");
require_once("esoCommon.php");


class CEsoItemSearchPopup 
{
	
	public $inputParams = array();
	
	public $inputText = "";
	public $inputItemSet = "";
	public $inputItemType = "";
	public $inputEquipType = "";
	public $inputWeaponType = "";
	public $inputArmorType = -1;
	public $inputItemTrait = -1;
	public $inputItemLevel = -1;
	public $inputItemQuality = -1;
	public $inputItemIntLevel = -1;
	public $inputItemIntType = -1;
	
	public $inputItemTransmuteTrait = -1;
	public $inputLimit = 100;
	
	public $resultItems = array();
	public $resultError = array("error" => true);
	
	public $itemRows = array("itemId", "name", "icon", "type", "equipType", "weaponType", "armorType", "trait", "style", "quality", "level");
	
	
	public function __construct()
	{
		$this->inputParams = $_REQUEST;
		$this->ParseInputParams();
		$this->InitDatabase();
	}
	
	
	public function ReportError($errorMsg)
	{
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
		$origin = $_SERVER['HTTP_ORIGIN'];
		
		if (substr($origin, -8) == "uesp.net")
		{
			header("Access-Control-Allow-Origin: $origin");
		}
	}
	
	
	public function ParseInputParams ()
	{
		if (array_key_exists('text', $this->inputParams)) $this->inputText = urldecode($this->inputParams['text']);
		if (array_key_exists('set', $this->inputParams)) $this->inputItemSet = urldecode($this->inputParams['set']);
		if (array_key_exists('type', $this->inputParams)) $this->inputItemType = urldecode($this->inputParams['type']);
		if (array_key_exists('equiptype', $this->inputParams) && $this->inputParams['equiptype'] !== "") $this->inputEquipType =  urldecode($this->inputParams['equiptype']);
		if (array_key_exists('weapontype', $this->inputParams) && $this->inputParams['weapontype'] !== "") $this->inputWeaponType = (int) $this->inputParams['weapontype'];
		if (array_key_exists('trait', $this->inputParams) && $this->inputParams['trait'] !== "") $this->inputItemTrait = (int) $this->inputParams['trait'];
		if (array_key_exists('armortype', $this->inputParams) && $this->inputParams['armortype'] !== "") $this->inputArmorType = (int) $this->inputParams['armortype'];
		if (array_key_exists('level', $this->inputParams) && $this->inputParams['level'] !== "") $this->inputItemLevel = (int) $this->inputParams['level'];
		if (array_key_exists('quality', $this->inputParams) && $this->inputParams['quality'] !== "") $this->inputItemQuality = (int) $this->inputParams['quality'];
		if (array_key_exists('intlevel', $this->inputParams) && $this->inputParams['intlevel'] !== "") $this->inputItemIntLevel = (int) $this->inputParams['intlevel'];
		if (array_key_exists('intype', $this->inputParams) && $this->inputParams['inttype'] !== "") $this->inputItemIntType = (int) $this->inputParams['intype'];
		if (array_key_exists('inttype', $this->inputParams) && $this->inputParams['inttype'] !== "") $this->inputItemIntType = (int) $this->inputParams['inttype'];
		if (array_key_exists('transmutetrait', $this->inputParams) && $this->inputParams['transmutetrait'] !== "") $this->inputItemTransmuteTrait = (int) $this->inputParams['transmutetrait'];
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
		$rows = implode(",", $this->itemRows);
		$query = "SELECT SQL_CALC_FOUND_ROWS $rows FROM minedItemSummary ";
		$whereQuery = array();
				
		if ($this->inputItemType != "")
		{
			$itemTypes = explode(",", $this->inputItemType);
			$tmpQuery = array();
				
			foreach ($itemTypes as $itemType)
			{
				$tmpQuery[] = "type=".((int)$itemType);
			}
				
			$whereQuery[] = "(" . implode(" OR ", $tmpQuery) . ")";
		}
		
		if ($this->inputItemType == "4,12" || $this->inputItemType == "4" || $this->inputItemType == "12")
		{
			$level = (int) $this->inputItemLevel;
			$where = "";
			
			if ($level <= 0)
			{
				// Do nothing
			}
			else if ($level < 2)
				$where = "((level = 1)";
			else if ($level < 5)
				$where = "((level >= 1 AND level < 5)";
			else if ($level < 10)
				$where = "((level >= 5 AND level <= 9)";
			else if ($level < 15)
				$where = "((level >= '10' AND level < '15')";
			else if ($level < 20)
				$where = "((level >= '15' AND level < '20')";
			else if ($level < 25)
				$where = "((level >= '20' AND level < '25')";
			else if ($level < 30)
				$where = "((level >= '25' AND level < '30')";
			else if ($level < 35)
				$where = "((level >= '30' AND level < '35')";
			else if ($level < 40)
				$where = "((level >= '35' AND level < '40')";
			else if ($level < 45)
				$where = "((level >= '40' AND level < '45')";
			else if ($level < 50)
				$where = "((level >= '45' AND level < '50')";
			else if ($level < 55)
				$where = "((level >= 'CP10' AND level < 'CP50')";
			else if ($level < 60)
				$where = "((level >= 'CP50' AND level <= 'CP90')";
			else if ($level < 65)
				$where = "((level >= 'CP100' AND level < 'CP150')";
			else if ($level <= 66)
				$where = "((level >= 'CP150' AND level <= 'CP160')";

			if ($where != "") 
			{
				$whereQuery[] = $where . " OR specialType=8 OR name=\"Orzorga's Red Frothgar\" OR name=\"Spring-Loaded Infusion\" OR name=\"Artaeum Picked Fish Bowl\"  OR name=\"Artaeum Takeaway Broth\" OR abilityDesc LIKE \"%These effects are scaled based on your level%\")";
			}
		}
		
		if ($this->inputEquipType != "")
		{
			$equipTypes = explode(",", $this->inputEquipType);
			$tmpQuery = array();
			
			foreach ($equipTypes as $equipType)
			{
				$tmpQuery[] = "equipType=".((int)$equipType);
			}
			
			$whereQuery[] = "(" . implode(" OR ", $tmpQuery) . ")";
		}
		
		if ($this->inputWeaponType != "") $whereQuery[] = "weaponType=".$this->inputWeaponType;
		if ($this->inputItemTrait >= 0)	$whereQuery[] = "trait=".$this->inputItemTrait;
		if ($this->inputArmorType >= 0)	$whereQuery[] = "armorType=".$this->inputArmorType;
		
		if ($this->inputText != "")
		{
			$safeText = $this->db->real_escape_string($this->inputText);
			$whereQuery[] = "(name LIKE '%$safeText%' OR description LIKE '%$safeText%' OR setName LIKE '%$safeText%')";
		}
		
		if ($this->inputItemSet != "")
		{
			$safeText = $this->db->real_escape_string($this->inputItemSet);
			$whereQuery[] = "(setName LIKE '%$safeText%')";
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
		
		$result = $this->db->query("SELECT FOUND_ROWS() as rowCount;");
		
		if ($result) 
		{
			$row = $result->fetch_assoc();
			$row['type'] = -1;
			$row['name'] = "zzzzzzzzz_RowCount";
			$this->resultItems[] = $row; 
		}
		
		$this->TransformItems();
		return true;
	}
	
	
	public function TransformItems()
	{
		foreach ($this->resultItems as $key => &$item)
		{
			$itemType = $item['type'];
			
			if ($itemType == 21 || $itemType == 20 || $itemType == 26)
			{
				$item['name'] = preg_replace("/^trifling /i", "", $item['name']);
			}
			
		}
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