<?php

if (php_sapi_name() != "cli") die("Can only be run from command line!");

/*
 * itemTypeMap.php -- by Dave Humphrey (dave@uesp.net), December 2014
 * 
 * Outputs a static HTML file that shows the type map of ESO's item IDs.
 * 
 */


	// Database users, passwords and other secrets
require("/home/uesp/secrets/esolog.secrets");
require("esoCommon.php");


class CEsoItemTypeMap
{
	
	const ESOITM_OUTPUT_FILE = "EsoItemTypeMap.html";
	const ESOITM_HTML_TEMPLATE = "templates/esoitemtypemap_template.txt";
	const ESOITM_MAX_ITEMID = 66000;
	
	public $typeMap = array();
	public $db = null;
	
	public function __construct()
	{
		$this->InitDatabase();
		$this->htmlTemplate = file_get_contents(self::ESOITM_HTML_TEMPLATE);
	}
	
	
	private function InitDatabase()
	{
		global $uespEsoLogReadDBHost, $uespEsoLogReadUser, $uespEsoLogReadPW, $uespEsoLogDatabase;
	
		$this->db = new mysqli($uespEsoLogReadDBHost, $uespEsoLogReadUser, $uespEsoLogReadPW, $uespEsoLogDatabase);
		if ($this->db->connect_error) return $this->ReportError("ERROR: Could not connect to mysql database!");
	
		return true;
	}
	
	
	public function LoadMapData()
	{
		print("Loading data for item type map...\n");
		
		for($id = 1; $id <= self::ESOITM_MAX_ITEMID; $id++)
		{
			if ($id % 1000 == 0) print("\tLoading itemId $id\n");
			
			$query ="SELECT type from minedItem WHERE itemId=$id LIMIT 1;";
			$result = $this->db->query($query);
			if (!$result) return false;
			
			if ($result->num_rows === 0)
			{
				$this->typeMap[$id] = -1;
				continue;
			}
			
			$result->data_seek(0);
			$row = $result->fetch_assoc();
			
			if ($row == null)
			{
				$this->typeMap[$id] = -1;
			}
			else
			{
				$this->typeMap[$id] = $row['type'];
			}
		}
		
		print("Finish loading data.\n");
		return true;
	}
	
	
	public function MakeMapContent()
	{
		print("Making map content string...\n");
		$output = "";
		
		for($id = 1; $id <= self::ESOITM_MAX_ITEMID; )
		{
			$type = $this->typeMap[$id];
			$firstId = $id;
			
			while ($this->typeMap[$id] == $type && $id <= self::ESOITM_MAX_ITEMID)
			{
				$id++;
			}
			
			$lastId = $id - 1;
			$count = $lastId - $firstId + 1;
			$typeString = GetEsoItemTypeText($type);
			$height = $count * 2;
			
			if ($type < 0)
				$output .= "<div id='esoitm_id_none' style='height: {$height}px; line-height: {$height}px;'></div>\n";
			elseif ($firstId == $lastId)
				$output .= "<div id='esoitm_id_$type' style='height: {$height}px; line-height: {$height}px;'>$typeString ($firstId)</div>\n";
			else
				$output .= "<div id='esoitm_id_$type' style='height: {$height}px; line-height: {$height}px;'>$typeString ($firstId to $lastId)</div>\n";
		}
		
		return $output;
	}
	
	
	public function MakeMap()
	{
		$this->LoadMapData();
		
		$replacePairs = array(
				'{itemStartId}' => 1,
				'{itemEndId}' => self::ESOITM_MAX_ITEMID,
				'{itemTypeMapContent}' => $this->MakeMapContent(),
		);
		
		$output = strtr($this->htmlTemplate, $replacePairs);
		file_put_contents(self::ESOITM_OUTPUT_FILE, $output);
	}
	
};


$g_EsoItemTypeMap = new CEsoItemTypeMap();
$g_EsoItemTypeMap->MakeMap();

?>
