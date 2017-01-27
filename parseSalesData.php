<?php 


// Database users, passwords and other secrets
require_once("/home/uesp/secrets/esosalesdata.secrets");


class EsoSalesDataParser
{
	const SKIP_CREATE_TABLES = false;
	const ESD_OUTPUTLOG_FILENAME = "esosalesdata.log";
		
	public $server = "NA";
	
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
		global $uespEsoSalesDataDatabase;
		
		$database = $uespEsoSalesDataDatabase;
	
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
	
	
	public function GetGuildSaleServer($server)
	{
		$upperServer = strtoupper($server);
	
		if (strncmp($upperServer, "NA", 2) == 0) return "NA";
		if (strncmp($upperServer, "EU", 2) == 0) return "EU";
		if (strncmp($upperServer, "PTS", 3) == 0) return "PTS";
	
		return $server;
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
						server TINYTEXT NOT NULL,
						name TINYTEXT NOT NULL,
						storeLocation TINYTEXT NOT NULL,
						lastStoreLocTime INT UNSIGNED NOT NULL,
						description TEXT NOT NULL,
						numMembers INTEGER NOT NULL,
						foundedDate INT UNSIGNED NOT NULL,
						leader TINYTEXT NOT NULL,
						totalSales INT UNSIGNED NOT NULL,
						totalPurchases INT UNSIGNED NOT NULL, 
						PRIMARY KEY (id),
						INDEX name_index(server(3), name(24))
					);";
	
		$this->lastQuery = $query;
		$result = $this->db->query($query);
		if ($result === FALSE) return $this->reportError("Failed to create guilds table!");
		
		$query = "CREATE TABLE IF NOT EXISTS items (
						id INTEGER NOT NULL AUTO_INCREMENT,
						server TINYTEXT NOT NULL,
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
						sumPurchases FLOAT NOT NULL,
						countPurchases INT UNSIGNED NOT NULL,
						countItemPurchases BIGINT UNSIGNED NOT NULL,
						sumSales FLOAT NOT NULL,
						countSales INT UNSIGNED NOT NULL,
						countItemSales BIGINT UNSIGNED NOT NULL,
						PRIMARY KEY (id),
						INDEX unique_index1(server(3), itemId, level, quality, trait, potionData),
						INDEX unique_index2(server(3), itemId, internalLevel, internalSubType, potionData)
					);";
		
		$this->lastQuery = $query;
		$result = $this->db->query($query);
		if ($result === FALSE) return $this->reportError("Failed to create items table!");
		
		$query = "CREATE TABLE IF NOT EXISTS sales (
						id INTEGER NOT NULL AUTO_INCREMENT,
						server TINYTEXT NOT NULL,
						itemId INTEGER NOT NULL,
						guildId SMALLINT NOT NULL,
						sellerName TINYTEXT NOT NULL,
						listTimestamp INT UNSIGNED NOT NULL,
						buyerName TINYTEXT NOT NULL,
						buyTimestamp INT UNSIGNED NOT NULL,
						eventId BIGINT NOT NULL,
						price INTEGER NOT NULL,
						qnt INTEGER NOT NULL,
						itemLink TINYTEXT NOT NULL,
						PRIMARY KEY (id),
						INDEX unique_entry1(server(3), itemId, guildId, listTimestamp, sellerName(24)),
						INDEX unique_entry2(server(3), itemId, guildId, eventId)
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
	
	
	public function LoadGuild($server, $name)
	{
		$safeName = $this->db->real_escape_string($name);
		$safeServer = $this->db->real_escape_string($server);
		
		$this->lastQuery = "SELECT * FROM guilds WHERE server=\"$safeServer\" AND name=\"$safeName\" LIMIT 1;";
		$result = $this->db->query($this->lastQuery);
		if ($result === FALSE) return $this->reportError("Failed to load guilds record!");
		if ($result->num_rows == 0) return false;
		
		$rowData = $result->fetch_assoc();
		$rowData['__dirty'] = false;
		
		$rowData['totalPurchases'] = intval($rowData['totalPurchases']);
		$rowData['totalSales'] = intval($rowData['totalSales']);
		
		return $rowData;
	}
	
	
	public function CreateNewGuild($server, $name)
	{
		$safeName = $this->db->real_escape_string($name);
		$safeServer = $this->db->real_escape_string($server);
		
		$this->lastQuery = "INSERT INTO guilds(server, name) VALUES(\"$server\", \"$safeName\");";
		$result = $this->db->query($this->lastQuery);
		if ($result === FALSE) return $this->reportError("Failed to create guilds record!");
		
		$guildData = array();
		
		$guildData['name'] = $name;
		$guildData['server'] = $server;
		$guildData['totalPurchases'] = 0;
		$guildData['totalSales'] = 0;
		$guildData['id'] = $this->db->insert_id;
		$guildData['__new'] = true;
		$guildData['__dirty'] = true;
			
		return $guildData;
	}
	
	
	public function &GetGuildData($server, $name)
	{
		if ($this->guildData[$server] != null)
		{
			if ($this->guildData[$server][$name] != null) return $this->guildData[$server][$name];
		}
		
		$guildData = $this->LoadGuild($server, $name);
		if ($guildData === false) $guildData = $this->CreateNewGuild($server, $name);
		
		if ($this->guildData[$server] == null) $this->guildData[$server] = array(); 
 		
		if ($guildData === false)
		{
			$this->guildData[$server][$name] = array();
			$this->guildData[$server][$name]['name'] = $name;
			$this->guildData[$server][$name]['server'] = $server;
			$this->guildData[$server][$name]['totalPurchases'] = 0;
			$this->guildData[$server][$name]['totalSales'] = 0;
			$this->guildData[$server][$name]['id'] = -1;
			$this->guildData[$server][$name]['__dirty'] = true;
			$this->guildData[$server][$name]['__error'] = true;
		}
		else
		{
			$this->guildData[$server][$name] = $guildData;
			$this->guildData[$server][$name]['__dirty'] = false;
		}
		
		return $this->guildData[$server][$name];
	}
	
	
	public function SaveGuild(&$guildData)
	{
		$id = intval($guildData['id']);
		$safeName = $this->db->real_escape_string($guildData['name']);
		$safeServer = $this->db->real_escape_string($guildData['server']);
		$desc = $this->db->real_escape_string($guildData['description']);
		$numMembers = intval($guildData['numMembers']);
		$storeLocation = $this->db->real_escape_string($guildData['storeLocation']);
		$lastStoreLocTime = intval($guildData['lastStoreLocTime']);
		$foundedDate = intval($guildData['foundedDate']);
		$leader = $this->db->real_escape_string($guildData['leader']);
		
		$totalPurchases = $guildData['totalPurchases'];
		$totalSales = $guildData['totalSales'];
		
		$this->lastQuery  = "UPDATE guilds SET name=\"$safeName\", server=\"$safeServer\", storeLocation=\"$storeLocation\", leader=\"$leader\",";
		$this->lastQuery .= "numMembers=$numMembers, lastStoreLocTime=$lastStoreLocTime, foundedDate=$foundedDate, description=\"$desc\", ";
		$this->lastQuery .= "totalPurchases=$totalPurchases, totalSales=$totalSales ";
		$this->lastQuery .= "WHERE id=$id;";
		
		$result = $this->db->query($this->lastQuery);
		if ($result === FALSE) return $this->reportError("Failed to save guild record!");
	
		$guildData['__dirty'] = false;
	
		return true;
	}
	
	
	public function SaveItemStats(&$itemData)
	{
		$id = intval($itemData['id']);
				
		$countPurchases = $itemData['countPurchases'];
		$countSales = $itemData['countSales'];
		$countItemPurchases = $itemData['countItemPurchases'];
		$countItemSales = $itemData['countItemSales'];
		$sumPurchases = $itemData['sumPurchases'];
		$sumSales = $itemData['sumSales'];
		$icon = $this->db->real_escape_string($itemData['icon']);
		
		$this->lastQuery = "UPDATE items SET ";
		$this->lastQuery .= "countPurchases=$countPurchases, countSales=$countSales, countItemPurchases=$countItemPurchases, ";
		$this->lastQuery .= "countItemSales=$countItemSales, sumPurchases=$sumPurchases, sumSales=$sumSales, icon='$icon' ";
		$this->lastQuery .= "WHERE id=$id;";
		
		$result = $this->db->query($this->lastQuery);
		if ($result === FALSE) return $this->reportError("Failed to save item stats data!");
		
		$itemData['__dirty'] = false;
		
		return true;
	}
	
	
	public function SaveUpdatedGuilds()
	{
		$guildCount = 0;
		
		//print_r($this->guildData);

		foreach ($this->guildData as $server => &$serverGuildData)
		{
			foreach ($serverGuildData as $id => &$guildData)
			{
				if ($guildData['__dirty'] === true) 
				{
					//print("Saving guild {$guildData['name']}...\n");
					$this->SaveGuild($guildData);
					++$guildCount;
				}
			}
		}
		
		print ("Saved $guildCount updated guild data...\n");
		return true;
	}
	
	
	public function SaveUpdatedItems()
	{
		$itemCount = 0;
		
		//print_r($this->guildData);
		
		foreach ($this->itemData as $cacheId => &$itemData)
		{
			if ($itemData['__dirty'] === true)
			{
				$this->SaveItemStats($itemData);
				++$itemCount;
			}
		}
		
		print ("Saved $itemCount updated item data...\n");
		return true;
	}
	
	
	public function LoadItemByKey($server, $itemId, $level, $quality, $trait, $potionData)
	{
		$this->lastQuery = "SELECT * FROM items WHERE server='$server' AND itemId='$itemId' AND level='$level' AND quality='$quality' AND trait='$trait' AND potionData='$potionData' LIMIT 1;";
		$result = $this->db->query($this->lastQuery);
		if ($result === FALSE) return $this->reportError("Failed to load items record matching $server:$itemId:$level:$quality:$trait:$potionData!");
		
		if ($result->num_rows == 0) return false;
			
		$rowData = $result->fetch_assoc();
		$rowData['__dirty'] = false;
		
		$rowData['countPurchases'] = intval($rowData['countPurchases']);
		$rowData['countSales'] = intval($rowData['countSales']);
		$rowData['countItemPurchases'] = intval($rowData['countItemPurchases']);
		$rowData['countItemSales'] = intval($rowData['countItemSales']);
		$rowData['sumSales'] = floatval($rowData['sumSales']);
		$rowData['sumPurchases'] = floatval($rowData['sumPurchases']);
		
		return $rowData;
	}
	
	
	public function LoadItem($server, $itemId, $itemIntLevel, $itemIntType, $itemPotionData)
	{
		$this->lastQuery = "SELECT * FROM items WHERE server='$server' AND itemId='$itemId' AND internalLevel='$itemIntLevel' AND internalSubType='$itemIntType' and potionData='$itemPotionData' LIMIT 1;";
		$result = $this->db->query($this->lastQuery);
		if ($result === FALSE) return $this->reportError("Failed to load items record matching $server:$itemId:$itemIntLevel:$itemIntType!");
		
		if ($result->num_rows == 0) return false;
			
		$rowData = $result->fetch_assoc();
		$rowData['__dirty'] = false;
		
		$rowData['countPurchases'] = intval($rowData['countPurchases']);
		$rowData['countSales'] = intval($rowData['countSales']);
		$rowData['countItemPurchases'] = intval($rowData['countItemPurchases']);
		$rowData['countItemSales'] = intval($rowData['countItemSales']);
		$rowData['sumSales'] = floatval($rowData['sumSales']);
		$rowData['sumPurchases'] = floatval($rowData['sumPurchases']);
		
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
	
	
	public function CreateNewItemByKey($server, $itemId, $level, $quality, $trait, $potionData, $itemLink, $itemRawData)
	{
		$minedItemData = $this->LoadMinedItem($itemId, $itemRawData['internalLevel'], $itemRawData['internalSubType'], $potionData);
		
		if ($minedItemData === false)
		{
			$icon = "";
			$name = $itemName;
			$setName = "";
			$equipType = "0";
			$weaponType = "0";
			$itemType = "0";
			$armorType = "0";
			//$trait = "0";
			//$quality = "0";
			//$level = "0";
		}
		else
		{
			$icon = $minedItemData['icon'];
			$name = $minedItemData['name'];
			$setName = $minedItemData['setName'];
			$equipType = $minedItemData['equipType'];
			$weaponType = $minedItemData['weaponType'];
			$itemType = $minedItemData['type'];
			$armorType = $minedItemData['armorType'];
			//$trait = $minedItemData['trait'];
			//$quality = $minedItemData['quality'];
			//$level = $minedItemData['level'];
		}
		
		if ($itemRawData['name'] != null) $name = $itemRawData['name']; 
		if ($itemRawData['icon'] != null) $icon = $itemRawData['icon'];
		
		$safeIcon = $this->db->real_escape_string($icon);
		$safeName = $this->db->real_escape_string($this->MakeNiceItemName($name));
		$safeSetName = $this->db->real_escape_string($setName);
		$server = $this->db->real_escape_string($this->server);
		$internalLevel = $itemRawData['internalLevel'];
		$internalSubType = $itemRawData['internalSubType'];
		
		$this->lastQuery  = "INSERT INTO items(server, itemId, potionData, level, quality, trait, itemType, equipType, weaponType, armorType, icon, name, setName, internalLevel, internalSubType) ";
		$this->lastQuery .= "VALUES('$server', '$itemId', '$potionData', '$level', '$quality', '$trait', '$itemType', '$equipType', '$weaponType', '$armorType', \"$safeIcon\", \"$safeName\", \"$safeSetName\", \"$internalLevel\", \"$internalSubType\");";
		$result = $this->db->query($this->lastQuery);
		if ($result === FALSE) return $this->reportError("Failed to create items record!");
		
		$itemData = array();
		$itemData['__dirty'] = false;
		$itemData['__new'] = true;
		$itemData['id'] = $this->db->insert_id;
		$itemData['itemId'] = $itemId;
		$itemData['icon'] = $icon;
		$itemData['level'] = $level;
		$itemData['quality'] = $quality;
		$itemData['itemType'] = $itemType;
		$itemData['trait'] = $trait;
		$itemData['weaponType'] = @weaponType;
		$itemData['armorType'] = $armorType;
		$itemData['equipType'] = $equipType;
		$itemData['name'] = $name;
		$itemData['setName'] = $setName;
		$itemData['potionData'] = $potionData;
		$itemData['internalLevel'] = $internalLevel;
		$itemData['internalSubType'] = $internalSubType;
		
		$itemData['sumPurchases'] = 0;
		$itemData['sumSales'] = 0;
		$itemData['countPurchases'] = 0;
		$itemData['countSales'] = 0;
		$itemData['countItemPurchases'] = 0;
		$itemData['countItemSales'] = 0;
		
		++$this->localNewItemCount;
		
		return $itemData;
	}
	
	
	public function CreateNewItem($server, $itemId, $itemIntLevel, $itemIntType, $itemPotionData, $subItemData = null, $itemKey = null)
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
			
			if ($subItemData != null && $subItemData != "")
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
		$server = $this->db->real_escape_string($this->server);
		
		$this->lastQuery  = "INSERT INTO items(server, itemId, potionData, level, quality, trait, itemType, equipType, weaponType, armorType, icon, name, setName) ";
		$this->lastQuery .= "VALUES('$server', '$itemId', '$itemPotionData', '$level', '$quality', '$trait', '$itemType', '$equipType', '$weaponType', '$armorType', \"$safeIcon\", \"$safeName\", \"$safeSetName\");";
		$result = $this->db->query($this->lastQuery);
		if ($result === FALSE) return $this->reportError("Failed to create items record!");
		
		$itemData = array();
		$itemData['__dirty'] = false;
		$itemData['__new'] = true;
		$itemData['id'] = $this->db->insert_id;
		$itemData['itemId'] = $itemId;
		$itemData['icon'] = $icon;
		$itemData['level'] = $level;
		$itemData['quality'] = $quality;
		$itemData['itemType'] = $itemType;
		$itemData['trait'] = $trait;
		$itemData['weaponType'] = @weaponType;
		$itemData['armorType'] = $armorType;
		$itemData['equipType'] = $equipType;
		$itemData['name'] = $name;
		$itemData['setName'] = $setName;
		$itemData['potionData'] = $itemPotionData;
		
		$itemData['sumPurchases'] = 0;
		$itemData['sumSales'] = 0;
		$itemData['countPurchases'] = 0;
		$itemData['countSales'] = 0;
		$itemData['countItemPurchases'] = 0;
		$itemData['countItemSales'] = 0;
				
		++$this->localNewItemCount;
		
		return $itemData;
	}
	
	
	public function &GetItemDataByKey($server, $itemLink, $itemRawData)
	{
		$itemId = $itemRawData['itemId'];
		$level = $itemRawData['level'];
		$quality = $itemRawData['quality'];
		$trait = $itemRawData['trait'];
		$potionData = $itemRawData['potionData'];
				
		$itemLinkData = $this->ParseItemLink($itemLink);
		if ($itemLinkData === false) return false;
	
		if ($itemId == null) 
		{
			$itemId = intval($itemLinkData['itemId']);
			$itemRawData['itemId'] = $itemId;
		}
		
		if ($potionData == null) 
		{
			$potionData = intval($itemLinkData['potionData']);
			$itemRawData['potionData'] = $potionData;
		}
		
		$itemRawData['internalLevel'] = $itemLinkData['level'];
		$itemRawData['internalSubType'] = $itemLinkData['subtype'];
		
			/* Check for missing or "old" sales data that may have incorrect data */
		if ($level == null || $trait == null || $quality == null || $itemRawData['timeStamp1'] < 1485400000)
		{
			$minedItemData = $this->LoadMinedItem($itemId, $itemLinkData['level'], $itemLinkData['subtype'], $itemPotionData);
				
			if ($minedItemData !== false)
			{
				$quality = $minedItemData['quality'];
				$trait = $minedItemData['trait'];
				$level = $minedItemData['level'];
			}
			else
			{
				if ($quality == null) $quality = 1;
				if ($trait == null) $trait = 0;
				if ($level == null) $level = 1;
			}
		}
		
		if ($level <= 0) $level = 1;
		
		$cacheId = $server . ":" . $itemId . ":" . $level . ":" .$quality . ":" . $trait . ":" . $potionData;
		if ($this->itemData[$cacheId] != null) return $this->itemData[$cacheId];
		
		$itemData = $this->LoadItemByKey($server, $itemId, $level, $quality, $trait, $potionData);
		
		if ($itemData === false)
		{
			$itemData = $this->CreateNewItemByKey($server, $itemId, $level, $quality, $trait, $potionData, $itemLink, $itemRawData);
		}
		
		if ($itemData !== false)
		{
			$this->itemData[$cacheId] = $itemData;
			$this->itemData[$cacheId]['__dirty'] = false;
		}
		else
		{
			print("\tError: Couldn't load or create new item $itemId!\n");
			
			$this->itemData[$cacheId] = array();
			$this->itemData[$cacheId] = $name;
			$this->itemData[$cacheId]['id'] = -1;
			$this->itemData[$cacheId]['server'] = $this->server;
			$this->itemData[$cacheId]['itemId'] = $itemId;
			$this->itemData[$cacheId]['level'] = $level;
			$this->itemData[$cacheId]['quality'] = $quality;
			$this->itemData[$cacheId]['trait'] = $trait;
			$this->itemData[$cacheId]['potionData'] = $potionData;
			$this->itemData[$cacheId]['sumPurchases'] = 0;
			$this->itemData[$cacheId]['sumSales'] = 0;
			$this->itemData[$cacheId]['countPurchases'] = 0;
			$this->itemData[$cacheId]['countSales'] = 0;
			$this->itemData[$cacheId]['countItemPurchases'] = 0;
			$this->itemData[$cacheId]['countItemSales'] = 0;
				
			$this->itemData[$cacheId]['__dirty'] = true;
			$this->itemData[$cacheId]['__error'] = true;
		}
		
		return $this->itemData[$cacheId];
	}
		
	
	public function LoadSaleSearchEntry($itemMyId, $guildId, $listTime, $sellerName)
	{
		$safeTime = intval($listTime);
		$safeName = $this->db->real_escape_string($sellerName);
		$server = $this->db->real_escape_string($this->server);
		
		$this->lastQuery = "SELECT * FROM sales WHERE server='$server' AND itemId='$itemMyId' AND guildId='$guildId' AND listTimestamp=$safeTime AND sellerName=\"$safeName\" LIMIT 1;";
		$result = $this->db->query($this->lastQuery);
		if ($result === FALSE) return $this->reportError("Failed to load sales record matching $itemMyId:$guildId:$safeTime:$safeName!");
		
		if ($result->num_rows == 0) return false;
		
		$rowData = $result->fetch_assoc();
		$rowData['__dirty'] = false;
	
		return $rowData;
	}
	
	
	public function LoadSale($itemMyId, $guildId, $eventId)
	{
		$safeEventId = $this->db->real_escape_string($eventId);
		$server = $this->db->real_escape_string($this->server);
		
		$this->lastQuery = "SELECT * FROM sales WHERE server='$server' AND itemId='$itemMyId' AND guildId='$guildId' AND eventId='$safeEventId' LIMIT 1;";
		$result = $this->db->query($this->lastQuery);
		if ($result === FALSE) return $this->reportError("Failed to load sales record matching $itemMyId:$guildId:$eventId!");
	
		if ($result->num_rows == 0) return false;
			
		$rowData = $result->fetch_assoc();
		$rowData['__dirty'] = false;
	
		return $rowData;
	}
	
	
	public function CreateNewSaleMM(&$itemData, &$guildData, &$subItemData, &$saleData)
	{
		if ($saleData['quant'] == 0) return false;
		
		$itemId = $itemData['id'];
		$guildId = $guildData['id'];
		$eventId = $this->db->real_escape_string($saleData['id']);
		$sellerName = $this->db->real_escape_string($saleData['seller']);
		$buyerName = $this->db->real_escape_string($saleData['buyer']);
		$buyTimestamp = $this->db->real_escape_string($saleData['timestamp']);
		$price = $this->db->real_escape_string($saleData['price']);
		$qnt = $this->db->real_escape_string($saleData['quant']);
		$server = $this->db->real_escape_string($this->server);
		$itemLink = $this->db->real_escape_string($saleData['itemLink']);
		
		$this->lastQuery  = "INSERT INTO sales(server, itemId, guildId, sellerName, buyerName, buyTimestamp, eventId, price, qnt, itemLink) ";
		$this->lastQuery .= "VALUES('$server', '$itemId', '$guildId', '$sellerName', '$buyerName', '$buyTimestamp', '$eventId', '$price', '$qnt', '$itemLink');";
		$result = $this->db->query($this->lastQuery);
		if ($result === FALSE) return $this->reportError("Failed to create new sales record!");

		++$guildData['totalPurchases'];
		$guildData['__dirty'] = true;
		
		$itemData['countPurchases'] += 1;
		$itemData['sumPurchases'] += floatval($saleData['price']);
		$itemData['countItemPurchases'] += intval($saleData['quant']);
		$itemData['__dirty'] = true;
		
		++$this->localNewSalesCount;
		
		return true;
	}
	
	
	public function CreateNewSale(&$itemData, &$guildData, &$saleData)
	{
		if ($saleData['qnt'] == 0) return false;
		
		$itemId = $itemData['id'];
		$guildId = $guildData['id'];
		$eventId = $this->db->real_escape_string($saleData['eventId']);
		$sellerName = $this->db->real_escape_string($saleData['seller']);
		$buyerName = $this->db->real_escape_string($saleData['buyer']);
		$buyTimestamp = $this->db->real_escape_string($saleData['saleTimestamp']);
		$price = $this->db->real_escape_string($saleData['gold']);
		$qnt = $this->db->real_escape_string($saleData['qnt']);
		$server = $this->db->real_escape_string($this->server);
		$itemLink = $this->db->real_escape_string($saleData['itemLink']);
	
		$this->lastQuery  = "INSERT INTO sales(server, itemId, guildId, sellerName, buyerName, buyTimestamp, eventId, price, qnt, itemLink) ";
		$this->lastQuery .= "VALUES('$server', '$itemId', '$guildId', '$sellerName', '$buyerName', '$buyTimestamp', '$eventId', '$price', '$qnt', '$itemLink');";
		$result = $this->db->query($this->lastQuery);
		if ($result === FALSE) return $this->reportError("Failed to create new sales record!");
	
		$guildData['totalPurchases'] += 1;
		$guildData['__dirty'] = true;
				
		$itemData['countPurchases'] += 1;
		$itemData['sumPurchases'] += floatval($saleData['gold']);
		$itemData['countItemPurchases'] += intval($saleData['qnt']);
		$itemData['__dirty'] = true;
		
		++$this->localNewSalesCount;
	
		return true;
	}
	
	
	public function CreateNewSaleSearchEntry(&$itemData, &$guildData, &$saleData)
	{
		if ($saleData['qnt'] == 0) return false;
		
		$itemId = $itemData['id'];
		$guildId = $guildData['id'];
		$eventId = 0;
		$sellerName = $this->db->real_escape_string($saleData['seller']);
		$buyerName = "";
		$buyTimestamp = 0;
		$listTimestamp = intval($saleData['listTimestamp']);
		$price = $this->db->real_escape_string($saleData['price']);
		$qnt = $this->db->real_escape_string($saleData['qnt']);
		$server = $this->db->real_escape_string($this->server);
		$itemLink = $this->db->real_escape_string($saleData['itemLink']);
	
		$this->lastQuery  = "INSERT INTO sales(server, itemId, guildId, sellerName, buyerName, listTimestamp, eventId, price, qnt, itemLink) ";
		$this->lastQuery .= "VALUES('$server', '$itemId', '$guildId', '$sellerName', '$buyerName', '$listTimestamp', '$eventId', '$price', '$qnt', '$itemLink');";
		$result = $this->db->query($this->lastQuery);
		if ($result === FALSE) return $this->reportError("Failed to create new sales record from search entry!");
	
		$guildData['totalSales'] += 1;
		$guildData['__dirty'] = true;
		
		$itemData['countSales'] += 1;
		$itemData['sumSales'] += floatval($saleData['price']);
		$itemData['countItemSales'] += intval($saleData['qnt']);
		$itemData['__dirty'] = true;
			
		++$this->localNewSalesCount;
	
		return true;
	}
	
	
	public function ClearMMData()
	{
		$this->Lua = new Lua();
	}
	
	
	public function LoadMMFile($filename)
	{
		$result = $this->Lua->include($filename);
		$this->log("Received $result result from Lua file '$filename'!");
		
		return $result;
	}
	
	
	public function ParseAllMMData()
	{
		$returnValue = true;
		
		$returnValue &= $this->ParseMMData($this->Lua->MM00DataSavedVariables, 'MM00DataSavedVariables');
		$returnValue &= $this->ParseMMData($this->Lua->MM01DataSavedVariables, 'MM01DataSavedVariables');
		$returnValue &= $this->ParseMMData($this->Lua->MM02DataSavedVariables, 'MM02DataSavedVariables');
		$returnValue &= $this->ParseMMData($this->Lua->MM03DataSavedVariables, 'MM03DataSavedVariables');
		$returnValue &= $this->ParseMMData($this->Lua->MM04DataSavedVariables, 'MM04DataSavedVariables');
		$returnValue &= $this->ParseMMData($this->Lua->MM05DataSavedVariables, 'MM05DataSavedVariables');
		$returnValue &= $this->ParseMMData($this->Lua->MM06DataSavedVariables, 'MM06DataSavedVariables');
		$returnValue &= $this->ParseMMData($this->Lua->MM07DataSavedVariables, 'MM07DataSavedVariables');
		$returnValue &= $this->ParseMMData($this->Lua->MM08DataSavedVariables, 'MM08DataSavedVariables');
		$returnValue &= $this->ParseMMData($this->Lua->MM09DataSavedVariables, 'MM09DataSavedVariables');
		$returnValue &= $this->ParseMMData($this->Lua->MM10DataSavedVariables, 'MM10DataSavedVariables');
		$returnValue &= $this->ParseMMData($this->Lua->MM11DataSavedVariables, 'MM11DataSavedVariables');
		$returnValue &= $this->ParseMMData($this->Lua->MM12DataSavedVariables, 'MM12DataSavedVariables');
		$returnValue &= $this->ParseMMData($this->Lua->MM13DataSavedVariables, 'MM13DataSavedVariables');
		$returnValue &= $this->ParseMMData($this->Lua->MM14DataSavedVariables, 'MM14DataSavedVariables');
		$returnValue &= $this->ParseMMData($this->Lua->MM15DataSavedVariables, 'MM15DataSavedVariables');
		
		return $returnValue;
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
		
		$itemParsedData = array();
		$itemParsedData['name'] = $subItemData['itemDesc'];
		$itemParsedData['icon'] = $subItemData['itemIcon'];
		
		$keyData = explode(":", $itemKey);
		$level1 = intval($keyData[0]) + intval($keyData[1]);
		if ($level1 != 0) $itemParsedData['level'] = $level1;
		$itemParsedData['quality'] = intval($keyData[2]);
		$itemParsedData['trait'] = intval($keyData[3]);
		
		$guildData = &$this->GetGuildData($this->server, $saleData['guild']);
		$itemData = &$this->GetItemDataByKey($this->server, $itemLink, $itemParsedData);
		
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
			$this->CreateNewSaleMM($itemData, $guildData, $subItemData, $saleData);
		}
		else
		{
			//print("Found duplicate sale: {$itemData['id']}:{$guildData['id']}:{$saleData['id']}\n");
		}
		
		return true;
	}
	
	
	public function ShowParseSummary()
	{
		print("Found {$this->localNewSalesCount} new sales and {$this->localNewItemCount} new items!\n");
	}
	
};


function EsoNameMatchUpper($matches)
{
	return strtoupper($matches[0]);
}
