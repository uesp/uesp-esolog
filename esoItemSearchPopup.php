<?php 


require_once("/home/uesp/secrets/esolog.secrets");
require_once("esoCommon.php");


class CEsoItemSearchPopup 
{
	
	public $inputParams = array();
	
	public $inputText = "";
	public $inputItemType = "";
	public $inputEquipType = "";
	public $inputWeaponType = "";
	public $inputArmorType = -1;
	public $inputItemTrait = -1;
	public $inputItemLevel = -1;
	public $inputItemQuality = -1;
	public $inputItemIntLevel = -1;
	public $inputItemIntType = -1;
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
		if (array_key_exists('type', $this->inputParams)) $this->inputItemType = urldecode($this->inputParams['type']);
		if (array_key_exists('equiptype', $this->inputParams)) $this->inputEquipType =  urldecode($this->inputParams['equiptype']);
		if (array_key_exists('weapontype', $this->inputParams)) $this->inputWeaponType = (int) $this->inputParams['weapontype'];
		if (array_key_exists('trait', $this->inputParams)) $this->inputItemTrait = (int) $this->inputParams['trait'];
		if (array_key_exists('armortype', $this->inputParams)) $this->inputArmorType = (int) $this->inputParams['armortype'];
		if (array_key_exists('level', $this->inputParams)) $this->inputItemLevel = (int) $this->inputParams['level'];
		if (array_key_exists('quality', $this->inputParams)) $this->inputItemQuality = (int) $this->inputParams['quality'];
		if (array_key_exists('intlevel', $this->inputParams)) $this->inputIntLevel = (int) $this->inputParams['intlevel'];
		if (array_key_exists('intype', $this->inputParams)) $this->inputIntType = (int) $this->inputParams['intype'];
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
			
			if ($level <= 0)
			{
				// Do nothing
			}
			else if ($level < 2)
				$whereQuery[] = "((level = 1) OR name LIKE \"Orzorga%\" OR abilityDesc LIKE \"%These effects are scaled based on your level%\")";
			else if ($level < 5)
				$whereQuery[] = "((level >= 1 AND level < 5) OR name LIKE \"Orzorga%\" OR abilityDesc LIKE \"%These effects are scaled based on your level%\")";
			else if ($level < 10)
				$whereQuery[] = "((level >= 5 AND level <= 9) OR name LIKE \"Orzorga%\" OR abilityDesc LIKE \"%These effects are scaled based on your level%\")";
			else if ($level < 15)
				$whereQuery[] = "((level >= '10' AND level < '15') OR name LIKE \"Orzorga%\" OR abilityDesc LIKE \"%These effects are scaled based on your level%\")";
			else if ($level < 20)
				$whereQuery[] = "((level >= '15' AND level < '20') OR name LIKE \"Orzorga%\" OR abilityDesc LIKE \"%These effects are scaled based on your level%\")";
			else if ($level < 25)
				$whereQuery[] = "((level >= '20' AND level < '25') OR name LIKE \"Orzorga%\" OR abilityDesc LIKE \"%These effects are scaled based on your level%\")";
			else if ($level < 30)
				$whereQuery[] = "((level >= '25' AND level < '30') OR name LIKE \"Orzorga%\" OR abilityDesc LIKE \"%These effects are scaled based on your level%\")";
			else if ($level < 35)
				$whereQuery[] = "((level >= '30' AND level < '35') OR name LIKE \"Orzorga%\" OR abilityDesc LIKE \"%These effects are scaled based on your level%\")";
			else if ($level < 40)
				$whereQuery[] = "((level >= '35' AND level < '40') OR name LIKE \"Orzorga%\" OR abilityDesc LIKE \"%These effects are scaled based on your level%\")";
			else if ($level < 45)
				$whereQuery[] = "((level >= '40' AND level < '45') OR name LIKE \"Orzorga%\" OR abilityDesc LIKE \"%These effects are scaled based on your level%\")";
			else if ($level < 50)
				$whereQuery[] = "((level >= '45' AND level < '50') OR name LIKE \"Orzorga%\" OR abilityDesc LIKE \"%These effects are scaled based on your level%\")";
			else if ($level < 55)
				$whereQuery[] = "((level >= 'CP10' AND level < 'CP50') OR name LIKE \"Orzorga%\" OR abilityDesc LIKE \"%These effects are scaled based on your level%\")";
			else if ($level < 60)
				$whereQuery[] = "((level >= 'CP50' AND level <= 'CP90') OR name LIKE \"Orzorga%\" OR abilityDesc LIKE \"%These effects are scaled based on your level%\")";
			else if ($level < 65)
				$whereQuery[] = "((level >= 'CP100' AND level < 'CP150') OR name LIKE \"Orzorga%\" OR abilityDesc LIKE \"%These effects are scaled based on your level%\")";
			else if ($level <= 66)
				$whereQuery[] = "((level >= 'CP150' AND level <= 'CP160') OR name LIKE \"Orzorga%\" OR abilityDesc LIKE \"%These effects are scaled based on your level%\")";

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