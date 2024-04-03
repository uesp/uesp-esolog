<?php

if (php_sapi_name() != "cli") die("Can only be run from command line!");

require("/home/uesp/secrets/esolog.secrets");
require("esoCommon.php");


class CEsoTrimOldMinedItems 
{
	public $db = null;
	public $version = "41";
	public $itemsLoaded = 0;
	public $itemsTrimmed = 0;
	public $itemsCopied = 0;
	
		// Keep all potions at itemId 1/2
	public $START_ITEMID = 10;
	public $END_ITEMID = 220000;
	public $TEMP_TABLE_NAME = "minedItemTrimTmp";
	
		// InternalLevel:InternalSubtype for mined items to keep
	public $VALID_ITEMS = [
			"1:1" => true,
			"1:2" => true,
			"50:364" => true,
			"50:366" => true,
			"50:367" => true,
			"50:368" => true,
			"50:369" => true,
			"50:370" => true,
	];
	
	
	public function __construct()
	{
		print("Removes all items from a minedItem table except specific levels (1:1, 1:2, 50:364, and 50:366-370).\n\n");
		$this->ParseCommandLineArgs();
		$this->InitDatabase();
	}
	
	
	protected function InitDatabase()
	{
		global $uespEsoLogWriteDBHost, $uespEsoLogWriteUser, $uespEsoLogWritePW, $uespEsoLogDatabase;
		
		$this->db = new mysqli($uespEsoLogWriteDBHost, $uespEsoLogWriteUser, $uespEsoLogWritePW, $uespEsoLogDatabase);
		if ($this->db->connect_error) exit("Error: Could not connect to mysql database!");
		
		return true;
	}
	
	
	protected function ParseCommandLineArgs()
	{
		global $argv;
		
		if (count($argv) <= 1) die("Error: No version specified on command line!\n");
		
		for ($i = 1; $i < count($argv); $i++)
		{
			$arg = $argv[$i];
			
			$this->version = strtolower(trim($arg));
		}
		
		if (!preg_match('/^\d+(?:pts(?:\d+)?)?$/', $this->version)) die("Error: Version does not match the expected format (38, 38pts, 38pts1)!\n");
	}
	
	
	protected function TrimItems()
	{
		$table = "minedItem" . GetEsoItemTableSuffix($this->version);
		$tmpTable = $this->TEMP_TABLE_NAME;
		
		$query = "CREATE TABLE `$tmpTable` LIKE `$table`;";
		$result = $this->db->query($query);
		if (!$result) die("Error: Failed to create temporary table '$tmpTable'!\n");
		
		for ($itemId = $this->START_ITEMID; $itemId <= $this->END_ITEMID; ++$itemId)
		{
			if ($itemId % 10000 == 0) print("\t$itemId) Trimming...(loaded {$this->itemsLoaded} records, trimmed {$this->itemsTrimmed})\n");
			
			$query = "SELECT id, internalLevel, internalSubtype FROM `$table` WHERE itemId='$itemId';";
			$result = $this->db->query($query);
			
			if (!$result)
			{
				print("Error: Failed to load items for ID #$itemId from database!\n");
				continue;
			}
			
			$idsToRemove = [];
			$idsToCopy = [];
			
			while ($item = $result->fetch_assoc())
			{
				$intLevel = intval($item['internalLevel']);
				$intSubtype = intval($item['internalSubtype']);
				$levelId = "$intLevel:$intSubtype";
				
				++$this->itemsLoaded;
				
				if ($this->VALID_ITEMS[$levelId] === true) 
				{
					++$this->itemsCopied;
					$idsToCopy[] = intval($item['id']);
					continue;
				}
				
				$idsToRemove[] = intval($item['id']);
				
				++$this->itemsTrimmed;
			}
			
			if (count($idsToCopy) == 0) continue;
			
			$ids = "'" . implode("','", $idsToCopy) . "'";
			$query = "INSERT INTO `$tmpTable` SELECT * FROM `$table` WHERE id IN($ids);";
			
			$result = $this->db->query($query);
			if (!$result) print("Error: Failed to copy itemId #$itemId to temporary database!\n\t" . $this->db->error . "\n");
			
			/* 
			 * Deleting records is slower than copying and in order to reclaim table space you must run a lengthy "OPTIMIZE TABLE..." query afterwards
			
			if (count($idsToRemove) == 0) continue;
			
			$ids = "'" . implode("','", $idsToRemove) . "'";
			$query = "DELETE FROM `$table` WHERE id IN($ids);";
			
			$result = $this->db->query($query);
			if (!$result) print("Error: Failed to trim itemId #$itemId from database!\n");
			*/
		}
		
		print("Finished copying {$this->itemsCopied} items to temporary table $tmpTable!\n");
		
		$query = "DROP TABLE `$table`;";
		$result = $this->db->query($query);
		if (!$result) die("Error: Failed to delete old '$table'!\n\t" . $this->db->error . "\n");
		
		$query = "RENAME TABLE `$tmpTable` to `$table`;";
		$result = $this->db->query($query);
		if (!$result) die("Error: Failed to rename temporary table '$tmpTable' to '$table'!\n\t" . $this->db->error . "\n");
		
		return true;
	}
	
	
	public function Run()
	{
		$suffix = GetEsoItemTableSuffix($this->version);
		if ($suffix == "") die("Error: Unknown table suffix for version {$this->version}!\n");
		
		print("WARNING: Trimming table minedItem$suffix for version {$this->version}. This is not reversible!\n");
		print("Are you sure you wish to continue (Y/N)? ");
		$input = strtolower(fgetc(STDIN));
		
		if ($input !== 'y') die("\tAborting...\n");
		
		$this->TrimItems();
		print("\n");
	}
	
	
};


$trim = new CEsoTrimOldMinedItems();
$trim->Run();
