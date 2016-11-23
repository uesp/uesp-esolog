<?php 


// Database users, passwords and other secrets
require_once("/home/uesp/secrets/esosalesdata.secrets");


class EsoSalesDataParser
{
	const SKIP_CREATE_TABLES = false;
	const ESD_OUTPUTLOG_FILENAME = "esosalesdata.log";
	
	
	public $server = "na";
	
	public $db = null;
	private $dbReadInitialized  = false;
	private $dbWriteInitialized = false;
	public $lastQuery = "";
	
	public $guildData = array();
	public $itemData = array();
	
	private $Lua = null;
	
	public $localSalesCount = 0;
	public $localItemCount = 0;
	public $localNewSalesCount = 0;
	public $localNewItemCount = 0;
	
	
	public function __construct ()
	{
		$this->Lua = new Lua();
				
		$this->initDatabaseWrite();
		
		$this->setInputParams();
		$this->parseInputParams();
	}
	
	
	public function log ($msg)
	{
		print($msg . "\n");
		$result = file_put_contents($this->logFilePath . self::ESD_OUTPUTLOG_FILENAME, $msg . "\n", FILE_APPEND | LOCK_EX);
		return TRUE;
	}
	
	
	public function reportError ($errorMsg)
	{
		$this->log($errorMsg);
	
		if ($this->db != null && $this->db->error)
		{
			$this->log("\tDB Error:" . $this->db->error);
			$this->log("\tLast Query:" . $this->lastQuery);
		}
		return false;
	}
	
	
	private function initDatabaseWrite ()
	{
		global $uespEsoSalesDataWriteDBHost, $uespEsoSalesDataWriteUser, $uespEsoSalesDataWritePW, $uespEsoSalesDataDatabase;
		global $uespEsoSalesDataDatabaseNA, $uespEsoSalesDataDatabaseEU;
		
		$database = $uespEsoSalesDataDatabaseNA;
		if ($this->server == "eu" || $this->server == "EU") $database = $uespEsoSalesDataDatabaseEU; 
	
		if ($this->dbWriteInitialized) return true;
	
		if ($this->dbReadInitialized)
		{
			$this->db->close();
			unset($this->db);
			$this->db = null;
			$this->dbReadInitialized = false;
		}
	
		$this->db = new mysqli($uespEsoSalesDataWriteDBHost, $uespEsoSalesDataWriteUser, $uespEsoSalesDataWritePW, $database);
		if ($db->connect_error) return $this->reportError("Could not connect to mysql database!");
	
		$this->dbReadInitialized = true;
		$this->dbWriteInitialized = true;
	
		if (self::SKIP_CREATE_TABLES) return true;
		return $this->createTables();
	}
	

	private function parseInputParams ()
	{
		if (array_key_exists('start', $this->inputParams))
		{
		}
	
		return true;
	}
	
	
	private function setInputParams ()
	{
		global $argv;
		$this->inputParams = $_REQUEST;
	
			// Add command line arguments to input parameters for testing
		if ($argv !== null)
		{
			$foundPath = false;
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
				elseif (!$foundPath)
				{
					$this->logFilePath = rtrim($e[0], '/') . '/';
					$foundPath = true;
				}
				else
				{
					$this->inputParams[$e[0]] = 1;
				}
			}
		}
	}
	
	
	public function writeHeaders ()
	{
		header("Expires: 0");
		header("Pragma: no-cache");
		header("Cache-Control: no-cache, no-store, must-revalidate");
		header("Pragma: no-cache");
		header("content-type: text/html");
	}
	
		
	public function createTables()
	{
		$result = $this->initDatabaseWrite();
		if (!$result) return false;
	
		$query = "CREATE TABLE IF NOT EXISTS guilds (
						id SMALLINT NOT NULL AUTO_INCREMENT,
						name TINYTEXT NOT NULL,
						storeLocation TINYTEXT NOT NULL,
						description TEXT NOT NULL,
						numMembers INTEGER NOT NULL,
						PRIMARY KEY (id),
						INDEX name_index(name(24))
					);";
	
		$this->lastQuery = $query;
		$result = $this->db->query($query);
		if ($result === FALSE) return $this->reportError("Failed to create guilds table!");
		
		$query = "CREATE TABLE IF NOT EXISTS items (
						id INTEGER NOT NULL AUTO_INCREMENT,
						name TINYTEXT NOT NULL,
						itemId INTEGER NOT NULL,
						internalLevel TINYINT NOT NULL,
						internalSubType SMALLINT NOT NULL,
						level TINYINT NOT NULL,
						quality TINYINT NOT NULL,
						trait TINYINT NOT NULL,
						itemType TINYINT NOT NULL,
						equipType TINYINT NOT NULL,
						weaponType TINYINT NOT NULL,
						armorType TINYINT NOT NULL,
						icon TINYTEXT NOT NULL,
						setName TINYTEXT NOT NULL,
						potionData INT UNSIGNED NOT NULL,
						PRIMARY KEY (id),
						INDEX unique_index(itemId, internalLevel, internalSubType, potionData)
					);";
		
		$this->lastQuery = $query;
		$result = $this->db->query($query);
		if ($result === FALSE) return $this->reportError("Failed to create items table!");
		
		$query = "CREATE TABLE IF NOT EXISTS sales (
						id INTEGER NOT NULL AUTO_INCREMENT,
						itemId INTEGER NOT NULL,
						itemLink TINYTEXT NOT NULL,
						guildId SMALLINT NOT NULL,
						sellerName TINYTEXT NOT NULL,
						listTimestamp INT UNSIGNED NOT NULL,
						buyerName TINYTEXT NOT NULL,
						buyTimestamp INT UNSIGNED NOT NULL,
						eventId BIGINT NOT NULL,
						price INTEGER NOT NULL,
						qnt INTEGER NOT NULL,
						PRIMARY KEY (id),
						INDEX unique_entry1(itemId, guildId, listTimestamp, sellerName(24)),
						INDEX unique_entry2(itemId, guildId, eventId)
					);";
		
		$this->lastQuery = $query;
		$result = $this->db->query($query);
		if ($result === FALSE) return $this->reportError("Failed to create guilds table!");
				
		return true;
	}
	
	
	public function ParseItemLink ($itemLink)
	{
		$matches = array();
	
		$result = preg_match('/\|H(?P<linkType>[A-Za-z0-9]*)\:item\:(?P<itemId>[0-9]*)\:(?P<subtype>[0-9]*)\:(?P<level>[0-9]*)\:(?P<enchantId>[0-9]*)\:(?P<enchantSubtype>[0-9]*)\:(?P<enchantLevel>[0-9]*)\:(.*?)\:(?P<style>[0-9]*)\:(?P<crafted>[0-9]*)\:(?P<bound>[0-9]*)\:(?P<stolen>[0-9]*)\:(?P<charges>[0-9]*)\:(?P<potionData>[0-9]*)\|h(?P<name>[^|\^]*)(?P<nameCode>.*?)\|h/', $itemLink, $matches);
		if ($result == 0) return false;

		return $matches;
	}
	
	
	public function LoadGuild($name)
	{
		$safeName = $this->db->real_escape_string($name);
		$this->lastQuery = "SELECT * FROM guilds WHERE name=\"$safeName\" LIMIT 1;";
		$result = $this->db->query($this->lastQuery);
		if ($result === FALSE) return $this->reportError("Failed to load guilds record!");
		if ($result->num_rows == 0) return false;
		
		$rowData = $result->fetch_assoc();
		$rowData['__dirty'] = false;
		
		return $rowData;
	}
	
	
	public function CreateNewGuild($name)
	{
		$safeName = $this->db->real_escape_string($name);
		$this->lastQuery = "INSERT INTO guilds(name) VALUES(\"$safeName\");";
		$result = $this->db->query($this->lastQuery);
		if ($result === FALSE) return $this->reportError("Failed to create guilds record!");
		
		$guildData = array();
		
		$guildData['name'] = $name;
		$guildData['id'] = $this->db->insert_id;
		$guildData['__new'] = true;
		$guildData['__dirty'] = false;
			
		return $guildData;
	}
	
	
	public function &GetGuildData($name)
	{
		if ($this->guildData[$name] != null) return $this->guildData[$name];
		
		$guildData = $this->LoadGuild($name);
		if ($guildData === false) $guildData = $this->CreateNewGuild($name);
		
		if ($guildData === false)
		{
			$this->guildData[$name] = array();
			$this->guildData[$name] = $name;
			$this->guildData[$name]['id'] = -1;
			$this->guildData[$name]['__dirty'] = true;
			$this->guildData[$name]['__error'] = true;
		}
		else
		{
			$this->guildData[$name] = $guildData;
			$this->guildData[$name]['__dirty'] = false;
		}
		
		return $this->guildData[$name];
	}
	
	
	public function LoadItem($itemId, $itemIntLevel, $itemIntType, $itemPotionData)
	{
		$this->lastQuery = "SELECT * FROM items WHERE itemId='$itemId' AND internalLevel='$itemIntLevel' AND internalSubType='$itemIntType' and potionData='$itemPotionData' LIMIT 1;";
		$result = $this->db->query($this->lastQuery);
		if ($result === FALSE) return $this->reportError("Failed to load items record matching $itemId:$itemIntLevel:$itemIntType!");
		
		if ($result->num_rows == 0) return false;
			
		$rowData = $result->fetch_assoc();
		$rowData['__dirty'] = false;
		
		return $rowData;
	}
	
	
	public function LoadMinedItem($itemId, $itemIntLevel, $itemIntType, $itemPotionData)
	{
		$this->lastQuery = "SELECT * FROM uesp_esolog.minedItem WHERE itemId='$itemId' AND internalLevel='$itemIntLevel' AND internalSubType='$itemIntType' AND potionData='$itemPotionData' LIMIT 1;";
		$result = $this->db->query($this->lastQuery);
		if ($result === FALSE) return $this->reportError("Failed to load mined item data record matching $itemId:$itemIntLevel:$itemIntType:$itemPotionData!");
		
		if ($result->num_rows == 0) 
		{
			$this->lastQuery = "SELECT * FROM uesp_esolog.minedItem WHERE itemId='$itemId' AND internalLevel='1' AND internalSubType='1' LIMIT 1;";
			$result = $this->db->query($this->lastQuery);
			if ($result === FALSE) return $this->reportError("Failed to load mined item data record matching $itemId:1:1:$itemPotionData!");
		}
		
		if ($result->num_rows == 0) return $this->reportError("Failed to find mined item data record matching $itemId:$itemIntLevel:$itemIntType:$itemPotionData!");;
		
		return $result->fetch_assoc();
	}
	
	
	public function MakeNiceItemName($name)
	{
		$newName = preg_replace("#\^.*#", "", $name);
		
		$newName = ucwords($newName);
		
		$newName = preg_replace("/ In /", " in ", $newName);
		$newName = preg_replace("/ Of /", " of ", $newName);
		$newName = preg_replace("/ The /", " the ", $newName);
		$newName = preg_replace("/ And /", " and ", $newName);
		$newName = preg_replace_callback("/\-[a-z]/", 'EsoNameMatchUpper', $newName);
		$newName = preg_replace_callback("/\[vix]+$/", 'EsoNameMatchUpper', $newName);
		
		return $newName;
	}
	
	
	public function CreateNewItem($itemId, $itemIntLevel, $itemIntType, $itemPotionData, &$subItemData, $itemKey)
	{
		$minedItemData = $this->LoadMinedItem($itemId, $itemIntLevel, $itemIntType, $itemPotionData);
		
		if ($minedItemData === false)
		{
			$icon = "";
			$name = $itemName;
			$setName = "";
			$equipType = "0";
			$trait = "0";
			$weaponType = "0";
			$itemType = "0";
			$armorType = "0";
			$quality = "0";
			$level = "0";
		}
		else
		{
			$icon = $minedItemData['icon'];
			$name = $minedItemData['name'];
			$setName = $minedItemData['setName'];
			$equipType = $minedItemData['equipType'];
			$trait = $minedItemData['trait'];
			$weaponType = $minedItemData['weaponType'];
			$itemType = $minedItemData['type'];
			$armorType = $minedItemData['armorType'];
			$quality = $minedItemData['quality'];
			$level = $minedItemData['level'];
			
			if ($subItemData != "")
			{
				$name = $subItemData['itemDesc'];
			}
				
			if ($itemPotionData > 0 && $itemKey != null)
			{
				$keyData = explode(":", $itemKey);
				$level1 = intval($keyData[0]) + intval($keyData[1]);
				if ($level1 != 0) $level = $level1;
			}
		}

		$safeIcon = $this->db->real_escape_string($icon);
		$safeName = $this->db->real_escape_string($this->MakeNiceItemName($name));
		$safeSetName = $this->db->real_escape_string($setName);
		
		$this->lastQuery  = "INSERT INTO items(itemId, internalLevel, internalSubType, potionData, level, quality, trait, itemType, equipType, weaponType, armorType, icon, name, setName) ";
		$this->lastQuery .= "VALUES('$itemId', '$itemIntLevel', '$itemIntType', '$itemPotionData', '$level', '$quality', '$trait', '$itemType', '$equipType', '$weaponType', '$armorType', \"$safeIcon\", \"$safeName\", \"$safeSetName\");";
		$result = $this->db->query($this->lastQuery);
		if ($result === FALSE) return $this->reportError("Failed to create items record!");
		
		$itemData = array();
		$itemData['__dirty'] = false;
		$itemData['__new'] = true;
		$itemData['id'] = $this->db->insert_id;
		$itemData['itemId'] = $itemId;
		$itemData['internalLevel'] = $itemIntLevel;
		$itemData['internalSubType'] = $itemIntType;
		$itemData['icon'] = $icon;
		$itemData['level'] = $icon;
		$itemData['quality'] = $icon;
		$itemData['itemType'] = $icon;
		$itemData['trait'] = $icon;
		$itemData['weaponType'] = $icon;
		$itemData['armorType'] = $icon;
		$itemData['equipType'] = $icon;
		$itemData['name'] = $name;
		$itemData['setName'] = $setName;
		$itemData['potionData'] = $itemPotionData;
		
		++$this->localNewItemCount;
		
		return $itemData;
	}
	
	
	public function &GetItemData($itemLink, &$subItemData, $itemKey)
	{
		$itemLinkData = $this->ParseItemLink($itemLink);
		if ($itemLinkData === false) return false;
		
		$itemId = intval($itemLinkData['itemId']);
		$itemIntLevel = intval($itemLinkData['level']);
		$itemIntType = intval($itemLinkData['subtype']);
		$itemPotionData = intval($itemLinkData['potionData']);
		$cacheId = $itemId . ":" . $itemIntLevel . ":" .$itemIntType . ":" . $itemPotionData;
		
		if ($this->itemData[$cacheId] != null) return $this->itemData[$cacheId];
	
		$itemData = $this->LoadItem($itemId, $itemIntLevel, $itemIntType, $itemPotionData);
		
		if ($itemData === false) 
		{
			$itemData = $this->CreateNewItem($itemId, $itemIntLevel, $itemIntType, $itemPotionData, $subItemData, $itemKey);
		}
	
		if ($itemData === false)
		{
			$this->itemData[$cacheId] = array();
			$this->itemData[$cacheId] = $name;
			$this->itemData[$cacheId]['id'] = -1;
			$this->itemData[$cacheId]['itemId'] = $itemId;
			$this->itemData[$cacheId]['internalLevel'] = $itemIntLevel;
			$this->itemData[$cacheId]['internalSubType'] = $itemIntType;
			$this->itemData[$cacheId]['potionData'] = $itemPotionData;
			$this->itemData[$cacheId]['__dirty'] = true;
			$this->itemData[$cacheId]['__error'] = true;
		}
		else
		{
			$this->itemData[$cacheId] = $itemData;
			$this->itemData[$cacheId]['__dirty'] = false;
		}
	
		return $this->itemData[$cacheId];
	
	}
	
	
	public function LoadSale($itemMyId, $guildId, $eventId)
	{
		$safeEventId = $this->db->real_escape_string($eventId);
		
		$this->lastQuery = "SELECT * FROM sales WHERE itemId='$itemMyId' AND guildId='$guildId' AND eventId='$safeEventId' LIMIT 1;";
		$result = $this->db->query($this->lastQuery);
		if ($result === FALSE) return $this->reportError("Failed to load sales record matching $itemMyId:$guildId:$eventId!");
	
		if ($result->num_rows == 0) return false;
			
		$rowData = $result->fetch_assoc();
		$rowData['__dirty'] = false;
	
		return $rowData;
	}
	
	
	public function CreateNewSale(&$itemData, &$guildData, &$subItemData, &$saleData)
	{
		$itemId = $itemData['id'];
		$guildId = $guildData['id'];
		$eventId = $this->db->real_escape_string($saleData['id']);
		$sellerName = $this->db->real_escape_string($saleData['seller']);
		$buyerName = $this->db->real_escape_string($saleData['buyer']);
		$buyTimestamp = $this->db->real_escape_string($saleData['timestamp']);
		$price = $this->db->real_escape_string($saleData['price']);
		$qnt = $this->db->real_escape_string($saleData['quant']);
		$itemLink = $this->db->real_escape_string($saleData['itemLink']);
				
		$this->lastQuery  = "INSERT INTO sales(itemId, guildId, sellerName, buyerName, buyTimestamp, eventId, price, qnt, itemLink) ";
		$this->lastQuery .= "VALUES('$itemId', '$guildId', '$sellerName', '$buyerName', '$buyTimestamp', '$eventId', '$price', '$qnt', '$itemLink');";
		$result = $this->db->query($this->lastQuery);
		if ($result === FALSE) return $this->reportError("Failed to create new sales record!");
		
		++$this->localNewSalesCount;
		
		return true;
	}	
	
	
	public function LoadMMFile($filename)
	{
		$result = $this->Lua->include($filename);
		$this->log("Received $result result from Lua file '$filename'!");
		
		return $result;
	}
	
	
	public function ParseAllMMData()
	{
		$this->ParseMMData($this->Lua->MM00DataSavedVariables, 'MM00DataSavedVariables');
		$this->ParseMMData($this->Lua->MM01DataSavedVariables, 'MM01DataSavedVariables');
		$this->ParseMMData($this->Lua->MM02DataSavedVariables, 'MM02DataSavedVariables');
		$this->ParseMMData($this->Lua->MM03DataSavedVariables, 'MM03DataSavedVariables');
		$this->ParseMMData($this->Lua->MM04DataSavedVariables, 'MM04DataSavedVariables');
		$this->ParseMMData($this->Lua->MM05DataSavedVariables, 'MM05DataSavedVariables');
		$this->ParseMMData($this->Lua->MM06DataSavedVariables, 'MM06DataSavedVariables');
		$this->ParseMMData($this->Lua->MM07DataSavedVariables, 'MM07DataSavedVariables');
		$this->ParseMMData($this->Lua->MM08DataSavedVariables, 'MM08DataSavedVariables');
		$this->ParseMMData($this->Lua->MM09DataSavedVariables, 'MM09DataSavedVariables');
		$this->ParseMMData($this->Lua->MM10DataSavedVariables, 'MM10DataSavedVariables');
		$this->ParseMMData($this->Lua->MM11DataSavedVariables, 'MM11DataSavedVariables');
		$this->ParseMMData($this->Lua->MM12DataSavedVariables, 'MM12DataSavedVariables');
		$this->ParseMMData($this->Lua->MM13DataSavedVariables, 'MM13DataSavedVariables');
		$this->ParseMMData($this->Lua->MM14DataSavedVariables, 'MM14DataSavedVariables');
		$this->ParseMMData($this->Lua->MM15DataSavedVariables, 'MM15DataSavedVariables');
	}
	
	
	public function ParseMMData($root, $name)
	{
		$this->localSalesCount = 0;
		$this->localItemCount = 0;
		$this->localNewSalesCount = 0;
		$this->localNewItemCount = 0;
		
		if ($root == null) return $this->reportError("Missing '$name' section in MM data!");
		if ($root['Default'] == null) return $this->reportError("Missing 'Default' section in MM data '$name' variable!");
		
		$defaultData = &$root['Default'];
		if ($defaultData['MasterMerchant'] == null) return $this->reportError("Missing 'MasterMerchant' section in MM data 'Default' variable!");
		
		$mmData = &$defaultData['MasterMerchant'];
		if ($mmData['$AccountWide'] == null) return $this->reportError("Missing '$AccountWide' section in MM data 'MasterMerchant' variable!");
		
		$accountWideData = &$mmData['$AccountWide'];
		if ($accountWideData['SalesData'] == null) return $this->reportError("Missing 'SalesData' section in MM data '$AccountWide' variable!");
		
		$salesData = &$accountWideData['SalesData'];
		
		foreach ($salesData as $itemId => &$itemData)
		{
			$this->ParseMMItemData($itemId, $itemData);
		}
		
		print ("$name: Found {$this->localItemCount} items ({$this->localNewItemCount} new) and {$this->localSalesCount} sales ({$this->localNewSalesCount} new) in MM data!\n");
		return true;
	}
	
	public function ParseMMItemData($itemId, &$itemData)
	{
		//print ("Parsing item ID #$itemId...\n");
		
		foreach ($itemData as $key => &$subItemData)
		{
			$this->ParseMMSubItemData($itemId, $key, $subItemData);
		}
		
		return true;
	}
	
	
	public function ParseMMSubItemData($itemId, $itemKey, &$subItemData)
	{
		++$this->localItemCount;
		
		foreach ($subItemData['sales'] as $index => &$saleData)
		{
			$this->ParseMMSaleData($itemId, $itemKey, $subItemData, $saleData);
		}
		
		return true;
	}
	
	
	public function ParseMMSaleData($itemId, $itemKey, &$subItemData, &$saleData)
	{
		++$this->localSalesCount;
		
		$itemLink = $saleData['itemLink'];
		//print("\tFound sale for item $itemLink\n");
		
		$guildData = $this->GetGuildData($saleData['guild']);
		$itemData = $this->GetItemData($itemLink, $subItemData, $itemKey);
		
		if ($itemData['icon'] != $subItemData['itemIcon'])
		{
			$itemData['icon'] = $subItemData['itemIcon'];
			$itemData['__dirty'] = true;
		}
		
		if ($guildData['__new'] === true || $itemData['__new'] === true)
		{
			$saleRecord = false;
		}
		else
		{
			$saleRecord = $this->LoadSale($itemData['id'], $guildData['id'], $saleData['id']);
		}
		
		if ($saleRecord === false)
		{
			$this->CreateNewSale($itemData, $guildData, $subItemData, $saleData);
		}
		
		return true;
	}
	
	
};


function EsoNameMatchUpper($matches)
{
	return strtoupper($matches[0]);
}
