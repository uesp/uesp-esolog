<?php 


// Database users, passwords and other secrets
require_once("/home/uesp/secrets/esosalesdata.secrets");
require_once("/home/uesp/secrets/esolog.secrets");
require_once("esoCommon.php");
require_once("esoPotionData.php");


class EsoSalesDataParser
{
	const ALT_MINEDITEM_TABLE = "minedItem30pts";	// Alternate table to look for mined item data in (leave blank to ignore) 
	
	const SKIP_CREATE_TABLES = false;
	const ESD_OUTPUTLOG_FILENAME = "/home/uesp/esolog/esosalesdata.log";
	const ESD_LISTTIME_RANGE = 10;
	
	const MAX_ZSCORE = 2.0;
	
	const MIN_WEIGHTED_AVERAGE_INTERVAL = 11;
	const WEIGHTED_AVERAGE_BUCKETS = 20;
	
	const IGNORE_NEWSALES_TIMESECONDS = 86400 * 7 * 9;		// Incoming sales data older than this will be ignored and not parsed or added to database
	const TRENDS_START_TIMESECONDS = 86400 * 7 * 9;			// Sales older than this time are converted to trends
	const TRENDS_PERIODSECONDS = 86400 * 7;					// Create trends data over this period of time
	const TRENDS_DO_AVERAGE = true;
	const TRENDS_MIN_SALES_NUMBER = 500;					// Number of sales before trends are calculated
	const TRENDS_MOVE_BATCHSIZE = 100;						// Do database moves to oldSales in this quantity at a time
	const TRENDS_INCLUDE_OLDSALES = true;					// If true does a full relcalculation of all trends
	const TRENDS_START_ITEMID = 2912;						// Set to -1 for all items
	const TRENDS_END_ITEMID = -1;
	
	public $server = "NA";
	
	public $db = null;
	public $dbRead = null;
	public $dbSlave = null;
	public $dbLog = null;
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
	
	public $lastLoadedSalesData = array();
	
	public $startMicroTime = 0;
	
	public $waitForSlave = true;
	public $dbWriteCount = 0;
	public $dbWriteCountPeriod = 400;
	public $dbWriteNextSleepCount = 400;
	public $dbWriteCountSleep = 5;		// Period in seconds for sleep()
	public $maxAllowedSlaveLag = 5;		// Maximum database slave lag in seconds before write delays are enforced
	public $maxSlaveLagChecks = 10;
	public $trendCreateCount = 0;
	public $trendMoveCount = 0;
	public $trendItemCount = 0;
	public $trendItemsParsed = array();
	
	
	public function __construct ($quiet = false)
	{
		$this->Lua = new Lua();
		
		$this->startMicroTime = microtime(true);
		
		$this->initDatabaseRead();
		$this->initDatabaseWrite();
		$this->InitLogDatabaseRead();
		
		if ($quiet)
			$this->logQuiet("Current date is " . date('Y-m-d H:i:s'));
		else
			$this->log("Current date is " . date('Y-m-d H:i:s'));
		
		$this->setInputParams();
		$this->parseInputParams();
	}
	
	
	public function Ping()
	{
		if ($this->db) $this->db->ping();
		if ($this->dbRead) $this->dbRead->ping();
		if ($this->dbSlave) $this->dbSlave->ping();
		if ($this->dbLog) $this->dbLog->ping();
	}
	
	
	public function log ($msg)
	{
		$currentMicroTime = microtime(true);
		$diffTime = floor(($currentMicroTime - $this->startMicroTime)*1000)/1000;
		$diffTime = str_pad(number_format($diffTime, 3), 10, ' ', STR_PAD_LEFT);
		
		print("\t$diffTime: $msg\n");
		
		$result = file_put_contents(self::ESD_OUTPUTLOG_FILENAME, "$diffTime: $msg\n", FILE_APPEND | LOCK_EX);
		return TRUE;
	}
	
	
	public function logQuiet ($msg)
	{
		$currentMicroTime = microtime(true);
		$diffTime = floor(($currentMicroTime - $this->startMicroTime)*1000)/1000;
		
		$result = file_put_contents(self::ESD_OUTPUTLOG_FILENAME, "$diffTime: $msg\n", FILE_APPEND | LOCK_EX);
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
		
		if ($this->dbRead != null && $this->dbRead->error)
		{
			$this->log("\tDB Read Error:" . $this->dbRead->error);
			$this->log("\tLast Query:" . $this->lastQuery);
		}
		
		if ($this->dbLog != null && $this->dbLog->error)
		{
			$this->log("\tDB Log Error:" . $this->dbLog->error);
			$this->log("\tLast Query:" . $this->lastQuery);
		}
		
		return false;
	}
	
	
	private function initDatabaseWrite ()
	{
		global $uespEsoSalesDataWriteDBHost, $uespEsoSalesDataWriteUser, $uespEsoSalesDataWritePW, $uespEsoSalesDataDatabase;
		
		if ($this->dbWriteInitialized) return true;
		
		$this->db = new mysqli($uespEsoSalesDataWriteDBHost, $uespEsoSalesDataWriteUser, $uespEsoSalesDataWritePW, $uespEsoSalesDataDatabase);
		if ($db->connect_error) return $this->reportError("Could not connect to mysql database!");
		
		$this->dbWriteInitialized = true;
		
		if (self::SKIP_CREATE_TABLES) return true;
		return $this->createTables();
	}
	
	
	private function initDatabaseRead ()
	{
		global $uespEsoSalesDataReadDBHost, $uespEsoSalesDataReadUser, $uespEsoSalesDataReadPW, $uespEsoSalesDataDatabase;
		
		if ($this->dbReadInitialized) return true;
		
		$this->dbRead = new mysqli($uespEsoSalesDataReadDBHost, $uespEsoSalesDataReadUser, $uespEsoSalesDataReadPW, $uespEsoSalesDataDatabase);
		if ($db->connect_error) return $this->reportError("Could not connect to mysql read database!");
		
		$this->dbReadInitialized = true;
		
		return true;
	}
	
	
	private function InitLogDatabaseRead ()
	{
		global $uespEsoLogReadDBHost, $uespEsoLogReadUser, $uespEsoLogReadPW, $uespEsoLogDatabase;
	
		if ($this->dbLog != null) return true;
	
		$this->dbLog = new mysqli($uespEsoLogReadDBHost, $uespEsoLogReadUser, $uespEsoLogReadPW, $uespEsoLogDatabase);
		if ($this->dbLog->connect_error) return $this->ReportError("Could not connect to esolog mysql database!");

		return true;
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
					) ENGINE=MYISAM;";
	
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
						extraData TINYTEXT NOT NULL,
						sumPurchases FLOAT NOT NULL,
						countPurchases INT UNSIGNED NOT NULL,
						countItemPurchases BIGINT UNSIGNED NOT NULL,
						sumSales FLOAT NOT NULL,
						countSales INT UNSIGNED NOT NULL,
						countItemSales BIGINT UNSIGNED NOT NULL,
						lastPurchaseTimestamp INT UNSIGNED NOT NULL,
						lastSaleTimestamp INT UNSIGNED NOT NULL,
						lastSeen INT UNSIGNED NOT NULL,
						goodPrice FLOAT NOT NULL,
						goodSoldPrice FLOAT NOT NULL,
						goodListPrice FLOAT NOT NULL,
						PRIMARY KEY (id),
						INDEX unique_index1(server(3), itemId, level, quality, trait, potionData),
						INDEX unique_index2(server(3), itemId, internalLevel, internalSubType, potionData)
					) ENGINE=MYISAM;";
		
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
						timestamp INT UNSIGNED NOT NULL,
						eventId BIGINT NOT NULL,
						uniqueId BIGINT NOT NULL DEFAULT 0,
						price INTEGER NOT NULL,
						qnt INTEGER NOT NULL,
						itemLink TINYTEXT NOT NULL,
						lastSeen INT UNSIGNED NOT NULL,
						PRIMARY KEY (id),
						INDEX unique_entry1(server(3), itemId, guildId, listTimestamp, sellerName(24)),
						INDEX unique_entry2(server(3), itemId, guildId, eventId),
						INDEX unique_entry3(uniqueId),
						INDEX unique_itemid(itemId),
						INDEX timestamp(itemId, timestamp)
					) ENGINE=MYISAM;";
		
		$this->lastQuery = $query;
		$result = $this->db->query($query);
		if ($result === FALSE) return $this->reportError("Failed to create guilds table!");
		
		return true;
	}
	
	
	public function ParseItemLink ($itemLink)
	{
		return ParseEsoItemLink($itemLink);
	}
	
	
	public function LoadGuild($server, $name)
	{
		$safeName = $this->db->real_escape_string($name);
		$safeServer = $this->db->real_escape_string($server);
		
		$this->lastQuery = "SELECT * FROM guilds WHERE server=\"$safeServer\" AND name=\"$safeName\" LIMIT 1;";
		$result = $this->dbRead->query($this->lastQuery);
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
		
		$this->dbWriteCount++;
		
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
		
		$this->dbWriteCount++;
	
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
		$lastPurchase = $itemData['lastPurchaseTimestamp'];
		$lastSale = $itemData['lastSaleTimestamp'];
		$lastSeen = $itemData['lastSeen'];
		$icon = $this->db->real_escape_string($itemData['icon']);
		$goodPrice = $itemData['goodPrice'];
		$goodListPrice = $itemData['goodListPrice'];
		$goodSoldPrice = $itemData['goodSoldPrice'];
		
		$this->lastQuery = "UPDATE items SET ";
		$this->lastQuery .= "countPurchases=$countPurchases, countSales=$countSales, countItemPurchases=$countItemPurchases, ";
		$this->lastQuery .= "countItemSales=$countItemSales, sumPurchases=$sumPurchases, sumSales=$sumSales, icon='$icon', ";
		$this->lastQuery .= "lastPurchaseTimestamp=$lastPurchase, lastSaleTimestamp=$lastSale, lastSeen=$lastSeen, ";
		$this->lastQuery .= "goodPrice=$goodPrice, goodListPrice=$goodListPrice, goodSoldPrice=$goodSoldPrice ";
		$this->lastQuery .= "WHERE id=$id;";
		
		$result = $this->db->query($this->lastQuery);
		if ($result === FALSE) return $this->reportError("Failed to save item stats data!");
		
		$this->dbWriteCount++;
		
		$itemData['__dirty'] = false;
		
		return true;
	}
	
	
	public function SaveUpdatedGuilds()
	{
		$guildCount = 0;

		foreach ($this->guildData as $server => &$serverGuildData)
		{
			foreach ($serverGuildData as $id => &$guildData)
			{
				if ($guildData['__dirty'] === true) 
				{
					$this->SaveGuild($guildData);
					++$guildCount;
				}
			}
		}
		
		$this->log("Saved $guildCount updated guild data...");
		return true;
	}
	
	
	public function LoadAllOldSalesForItem($itemId)
	{
		$query = "SELECT * FROM oldSales WHERE itemId='$itemId';";
		$result = $this->dbRead->query($query);
		if ($result === false) return $this->reportError("Failed to load old sales for item $itemId!");
		
		if ($result->num_rows == 0) array();
		
		$salesData = array();
		
		while (($row = $result->fetch_assoc()))
		{
			$row['unitPrice'] = $row['price'] / $row['qnt'];
			$row['__isold'] = true;
			$salesData[] = $row;
		}
		
		return $salesData;
	}
	
	
	public function LoadAllSalesForItem($server, $itemId, $allColumns = false)
	{
		//$query = "SELECT price, qnt, listTimestamp, buyTimestamp, eventId FROM sales WHERE server='$server' AND itemId='$itemId';";
		
		if ($allColumns)
		{
			$query = "SELECT * FROM sales WHERE itemId='$itemId';";
		}
		else
		{
			$query = "SELECT price, qnt, timestamp, listTimestamp, buyTimestamp, eventId FROM sales WHERE itemId='$itemId';";
		}
		
		$result = $this->dbRead->query($query);
		if ($result === false) return $this->reportError("Failed to load sales for item $server:$itemId!");
		
		if ($result->num_rows == 0)
		{
			$this->reportError("No sales found for item $server:$itemId!");
			return false;
		}
		
		$salesData = array();
		
		while (($row = $result->fetch_assoc()))
		{
			$row['unitPrice'] = $row['price'] / $row['qnt'];
			//if ($row['buyTimestamp']  > 0) $row['timestamp'] = $row['buyTimestamp'];
			//if ($row['listTimestamp'] > 0) $row['timestamp'] = $row['listTimestamp'];
			
			$salesData[] = $row;
		}
		
		return $salesData;
	}
	
	
	public function Load30DaysSalesForItem($server, $itemId)
	{
		$timestamp = time() - 30*86400;
		$query = "SELECT price, qnt, timestamp, listTimestamp, buyTimestamp, eventId FROM sales WHERE itemId='$itemId' AND timestamp>'$timestamp';";
		
		$result = $this->dbRead->query($query);
		if ($result === false) return $this->reportError("Failed to load 30 days of sales for item $server:$itemId!");
		
		if ($result->num_rows == 0) 
		{
			$this->reportError("No sales found for item $server:$itemId!");
			return false;
		}
		
		$salesData = array();
		
		while (($row = $result->fetch_assoc()))
		{
			$row['unitPrice'] = $row['price'] / $row['qnt'];
			$salesData[] = $row;
		}
		
		return $salesData;
	}
	
	
	public function LoadDaysSalesForItem($server, $itemId, $days)
	{
		if ($days <= 0) $days = 1;
		$timestamp = time() - $days*86400;
		$query = "SELECT price, qnt, timestamp, listTimestamp, buyTimestamp, eventId FROM sales WHERE itemId='$itemId' AND timestamp>'$timestamp';";
		
		$result = $this->dbRead->query($query);
		if ($result === false) return $this->reportError("Failed to load $days days of sales for item $server:$itemId!");
		
		if ($result->num_rows == 0) 
		{
			$this->reportError("No sales found for item $server:$itemId!");
			return false;
		}
		
		$salesData = array();
		
		while (($row = $result->fetch_assoc()))
		{
			$row['unitPrice'] = $row['price'] / $row['qnt'];
			$salesData[] = $row;
		}
		
		return $salesData;
	}
	
	
	public function LoadCountSalesForItem($server, $itemId, $count = 100)
	{
		$salesData = array();
		if ($count <= 0) $count = 1;
		
		$query = "SELECT price, qnt, timestamp, listTimestamp, buyTimestamp, eventId FROM sales WHERE itemId='$itemId' AND listTimestamp>0 ORDER BY timestamp DESC LIMIT $count;";
		$result = $this->dbRead->query($query);
		if ($result === false) return $this->reportError("Failed to load latest #$count sales for item $server:$itemId!");
		
		while (($row = $result->fetch_assoc()))
		{
			$row['unitPrice'] = $row['price'] / $row['qnt'];
			$salesData[] = $row;
		}
		
		$query = "SELECT price, qnt, timestamp, listTimestamp, buyTimestamp, eventId FROM sales WHERE itemId='$itemId' AND buyTimestamp>0 ORDER BY timestamp DESC LIMIT $count;";
		$result = $this->dbRead->query($query);
		if ($result === false) return $this->reportError("Failed to load latest #$count sales for item $server:$itemId!");
		
		while (($row = $result->fetch_assoc()))
		{
			$row['unitPrice'] = $row['price'] / $row['qnt'];
			$salesData[] = $row;
		}
		
		if (count($salesData) == 0)
		{
			$this->reportError("No sales found for item $server:$itemId!");
			return false;
		}
		
		return $salesData;
	}
	
	
	public function SalesDataSortTimestamp($a, $b)
	{
		return $b['timestamp'] - $a['timestamp'];
	}
	
	
	public function SalesDataSortListTimestamp($a, $b)
	{
		return $b['listTimestamp'] - $a['listTimestamp'];
	}
	
	
	public function SalesDataSortSoldTimestamp($a, $b)
	{
		return $b['buyTimestamp'] - $a['buyTimestamp'];
	}
	
	
	public function UpdateItemGoodPrice(&$item, $output = false, $days = 30)
	{
		//$salesData = $this->LoadAllSalesForItem($item['server'], $item['id']);
		//$salesData = $this->Load30DaysSalesForItem($item['server'], $item['id']);
		//$salesData = $this->LoadCountSalesForItem($item['server'], $item['id'], $days);
		
			/* Only load 7 days of prices at first. If there's not enough data then try to load the full 30 days.
			 * This is an attempt to speed updates for items with 1000s of data points where loading all 30 days is quite slow (10-100 seconds). */
		if ($days == 30)
		{
			$salesData = $this->LoadDaysSalesForItem($item['server'], $item['id'], 7);
			if ($salesData === false || count($salesData) < 100) $salesData = $this->LoadDaysSalesForItem($item['server'], $item['id'], $days);
		}
		else
		{
			$salesData = $this->LoadDaysSalesForItem($item['server'], $item['id'], $days);
		}
		
		if ($salesData === false)
		{
			$salesData = $this->LoadCountSalesForItem($item['server'], $item['id'], 100);
			if ($salesData === false) return false;
		}
		
		$this->lastLoadedSalesData = $salesData;
		
		if ($output) $this->log("{$item['id']}: Loaded " . count($salesData) . " sales for item.");
		
		$stats = $this->ComputeBasicSalesStats($salesData);
		$this->ComputeAdvancedSalesStats($salesData, $stats);
		$this->RecalculatePriceLimits($salesData, $stats);
		
		$soldData = array();
		$listData = array();
		$validSalesData = array();
		
		foreach ($salesData as $sale)
		{
			if ($sale['outlier'] === true) continue;
			
			$validSalesData[] = $sale;
			if ($sale['listTimestamp'] > 0) $listData[] = $sale;
			if ($sale['buyTimestamp']  > 0) $soldData[] = $sale;
		}
		
		if (count($validSalesData) == 0)
		{
			foreach ($salesData as $sale)
			{
				$validSalesData[] = $sale;
				if ($sale['listTimestamp'] > 0) $listData[] = $sale;
				if ($sale['buyTimestamp']  > 0) $soldData[] = $sale;
			}	
		}
		
		usort($validSalesData, array('EsoSalesDataParser', 'SalesDataSortTimestamp'));
		$price = $this->ComputeWeightedAverage($validSalesData);
		if ($price > 0) $item['goodPrice'] = $price;
		
		usort($soldData, array('EsoSalesDataParser', 'SalesDataSortSoldTimestamp'));
		$price = $this->ComputeWeightedAverage($soldData);
		if ($price > 0) $item['goodSoldPrice'] = $price;
		
		usort($listData, array('EsoSalesDataParser', 'SalesDataSortListTimestamp'));
		$price = $this->ComputeWeightedAverage($listData);
		if ($price > 0) $item['goodListPrice'] = $price;
		
		if ($output) $this->log("\t\tGood Prices: {$item['goodPrice']}, {$item['goodSoldPrice']}, {$item['goodListPrice']} ");
		
		return true;
	}
	
	
	public function ComputeWeightedAverage($salesData)
	{
		if (count($salesData) <= 0) return -1;
		
		$numPoints = intval(count($salesData) / self::WEIGHTED_AVERAGE_BUCKETS); 
		if ($numPoints < self::MIN_WEIGHTED_AVERAGE_INTERVAL) $numPoints = self::MIN_WEIGHTED_AVERAGE_INTERVAL;
		
		$sum = 0;
		$count = 0;
		$i = 0;
		
		while ($i < count($salesData) && $count < $numPoints)
		{
			$data = $salesData[$i];
			++$i;
			
			$sum += $data['unitPrice'];
			++$count;
		}
		
		if ($count == 0) return 0;
		return $sum / $count;
	}
	
	
	public function ComputeBasicSalesStats($salesData)
	{
		if (count($salesData) < 1) return false;
		
		$result = array();
		
		$listSum = 0;
		$soldSum = 0;
		$listCount = 0;
		$soldCount = 0;
		$minPrice = 1000000000;
		$maxPrice = -1;
		$minTime = time();
		$maxTime = 0;
		
		foreach ($salesData as $sale)
		{
			$price = intval($sale['price']);
			$qnt = intval($sale['qnt']);
			$unitPrice = $sale['unitPrice'];
			$soldTime = intval($sale['buyTimestamp']);
			$listTime = intval($sale['listTimestamp']);
			$eventId = $sale['eventId'];
			
			if ($soldTime > 0 || $eventId > 0)
			{
				$soldSum += $price;
				$soldCount += $qnt;
				
				if ($minPrice > $unitPrice) $minPrice = $unitPrice;
				if ($maxPrice < $unitPrice) $maxPrice = $unitPrice;
				if ($minTime > $soldTime) $minTime = $soldTime;
				if ($maxTime < $soldTime) $maxTime = $soldTime;
			}
			
			if ($listTime > 0)
			{
				$listSum += $price;
				$listCount += $qnt;
				
				if ($minPrice > $unitPrice) $minPrice = $unitPrice;
				if ($maxPrice < $unitPrice) $maxPrice = $unitPrice;
				if ($minTime > $listTime) $minTime = $listTime;
				if ($maxTime < $listTime) $maxTime = $listTime;
			}
		}
		
		$result['minTime'] = $minTime;
		$result['maxTime'] = time();
		$result['maxTimeAction'] = $maxTime;
		$result['minPrice'] = $minPrice;
		$result['maxPrice'] = $maxPrice;
		
		$result['soldAvgPrice'] = 0;
		$result['listAvgPrice'] = 0;
		$result['totalAvgPrice'] = 0;
		
		if ($soldCount > 0) $result['soldAvgPrice'] = $soldSum / $soldCount;
		$result['soldItemCount'] = $soldCount;
		if ($listCount > 0) $result['listAvgPrice'] = $listSum / $listCount;
		$result['listItemCount'] = $listCount;
		
		$result['totalItemCount'] = $soldCount + $listCount;
		$result['totalAvgPrice'] = ($soldSum + $listSum) / $result['totalItemCount'];
		
		$result['minPriceLimit'] = $result['minPrice'];
		$result['maxPriceLimit'] = $result['maxPrice'];
		
		return $result;
	}
	
	
	public function ComputeAdvancedSalesStats($salesData, &$stats)
	{
		$sumSquareAll = 0;
		$sumSquareListed = 0;
		$sumSquareSold = 0;
		
		foreach ($salesData as $sale)
		{
			$price = intval($sale['price']);
			$qnt = intval($sale['qnt']);
			$unitPrice = $sale['unitPrice'];
		
			$sumSquareAll += pow($unitPrice - $stats['totalAvgPrice'], 2);
		
			if ($sale['buyTimestamp']  > 0)
			{
				$sumSquareSold += pow($unitPrice - $stats['soldAvgPrice'], 2);
			}
			
			if ($sale['listTimestamp'] > 0)
			{
				$sumSquareListed += pow($unitPrice - $stats['listAvgPrice'], 2);
			}
		}
		
		$stats['totalPriceStdDev'] = 0;
		$stats['soldPriceStdDev'] = 0;
		$stats['listedPriceStdDev'] = 0;
		
		if ($stats['totalItemCount'] > 0)
		{
			$stats['totalPriceStdDev'] = sqrt($sumSquareAll / floatval($stats['totalItemCount']));
		}
		
		if ($stats['soldItemCount'] > 0)
		{
			$stats['soldPriceStdDev'] = sqrt($sumSquareSold / floatval($stats['soldItemCount']));
		}
	
		if ($stats['listItemCount'] > 0)
		{
			$stats['listedPriceStdDev'] = sqrt($sumSquareListed / floatval($stats['listItemCount']));
		}
		
		return true;
	}
	
	
	public function RecalculatePriceLimits(&$salesData, &$stats)
	{
		if (count($salesData) <= 0) return false;
		if ($stats['totalPriceStdDev'] == 0) return false;
	
		$minPrice = 1000000000;
		$maxPrice = -1;
		$numValidPoints = 0;
			
		foreach ($salesData as $i => $sale)
		{
			$unitPrice = $sale['unitPrice'];
				
			$zScoreAll = abs(($unitPrice - $stats['totalAvgPrice']) / $stats['totalPriceStdDev']);
			$zScoreSold = 1;
			$zScoreListed = 1;
			$isOK = true;
				
			if ($zScoreAll > self::MAX_ZSCORE) $isOK = false;
			
			if ($sale['buyTimestamp'] > 0 && $stats['soldPriceStdDev'] != 0)
			{
				$zScoreSold = abs(($unitPrice - $stats['soldAvgPrice']) / $stats['soldPriceStdDev']);
				if ($zScoreSold > self::MAX_ZSCORE) $isOK = false;
			}
				
			if ($sale['listTimestamp'] > 0 && $stats['listedPriceStdDev'] != 0)
			{
				$zScoreListed = abs(($unitPrice - $stats['listAvgPrice']) / $stats['listedPriceStdDev']);
				if ($zScoreListed > self::MAX_ZSCORE) $isOK = false;
			}
				
			if ($isOK)
			{
				++$numValidPoints;
				if ($minPrice > $unitPrice) $minPrice = $unitPrice;
				if ($maxPrice < $unitPrice) $maxPrice = $unitPrice;
			}
			else
			{
				$salesData[$i]['outlier'] = true;
			}
		}
	
		$stats['minPriceLimit'] = $minPrice;
		$stats['maxPriceLimit'] = $maxPrice;
		$stats['numValidPoints'] = $numValidPoints;
			
		return true;
	}
	
	
	public function SaveUpdatedItems()
	{
		$itemCount = 0;
		$totalCount = count($this->itemData);
		
		$this->log("Updating all modified items...");
		
		foreach ($this->itemData as $cacheId => &$itemData)
		{
			if ($itemData['__dirty'] !== true) continue;
			++$itemCount;
			
			$this->log("\t$itemCount/$totalCount) Updating item {$itemData['id']}...");
			
			$this->UpdateItemGoodPrice($itemData, false);
			
			$this->SaveItemStats($itemData);
			
			$this->CheckDbWriteSleep();
		}
		
		$this->log("Saved $itemCount updated item data...");
		return true;
	}
	
	
	public function LoadItemByKey($server, $itemId, $level, $quality, $trait, $potionData, $extraData = "")
	{
		$server = $this->db->real_escape_string($server);
		$itemId = $this->db->real_escape_string($itemId);
		$level = $this->db->real_escape_string($level);
		$quality = $this->db->real_escape_string($quality);
		$trait = $this->db->real_escape_string($trait);
		$potionData = $this->db->real_escape_string($potionData);
		$extraData = $this->db->real_escape_string($extraData);
		
		$this->lastQuery = "SELECT * FROM items WHERE server='$server' AND itemId='$itemId' AND level='$level' AND quality='$quality' AND trait='$trait' AND potionData='$potionData' AND extraData='$extraData' LIMIT 1;";
		$result = $this->dbRead->query($this->lastQuery);
		if ($result === FALSE) return $this->reportError("Failed to load items record matching $server:$itemId:$level:$quality:$trait:$potionData:$extraData!");
		
			/* Check for recipe with a forced level of 1 */
		if ($result->num_rows == 0) 
		{
			$this->lastQuery = "SELECT * FROM items WHERE server='$server' AND itemId='$itemId' AND level='1' AND quality='$quality' AND trait='$trait' AND potionData='$potionData' AND extraData='$extraData' AND itemType=29 LIMIT 1;";
			$result = $this->dbRead->query($this->lastQuery);
			if ($result === FALSE) return $this->reportError("Failed to load recipe record matching $server:$itemId:$level:$quality:$trait:$potionData:$extraData!");
			
				/* Check for potion/poison with a forced quality of 1 */
			if ($result->num_rows == 0)
			{
				$this->lastQuery = "SELECT * FROM items WHERE server='$server' AND itemId='$itemId' AND level='$level' AND quality='1' AND trait='$trait' AND potionData='$potionData' AND extraData='$extraData' AND (itemType=7 OR itemType=30) LIMIT 1;";
				$result = $this->dbRead->query($this->lastQuery);
				if ($result === FALSE) return $this->reportError("Failed to load items record matching $server:$itemId:$level:$quality:$trait:$potionData:$extraData!");
				if ($result->num_rows == 0) return false;
			}
		}
			
		$rowData = $result->fetch_assoc();
		$rowData['__dirty'] = false;
		
		$rowData['countPurchases'] = intval($rowData['countPurchases']);
		$rowData['countSales'] = intval($rowData['countSales']);
		$rowData['countItemPurchases'] = intval($rowData['countItemPurchases']);
		$rowData['countItemSales'] = intval($rowData['countItemSales']);
		$rowData['sumSales'] = floatval($rowData['sumSales']);
		$rowData['sumPurchases'] = floatval($rowData['sumPurchases']);
		
		$rowData['extraData'] = $extraData;
		
		return $rowData;
	}
	
	
	public function LoadItem($server, $itemId, $itemIntLevel, $itemIntType, $itemPotionData, $extraData = "")
	{
		$server = $this->db->real_escape_string($server);
		$itemId = $this->db->real_escape_string($itemId);
		$itemIntLevel = $this->db->real_escape_string($itemIntLevel);
		$itemIntType = $this->db->real_escape_string($itemIntType);
		$itemPotionData = $this->db->real_escape_string($itemPotionData);
		$extraData = $this->db->real_escape_string($extraData);
		
		$this->lastQuery = "SELECT * FROM items WHERE server='$server' AND itemId='$itemId' AND internalLevel='$itemIntLevel' AND internalSubType='$itemIntType' and potionData='$itemPotionData' AND extraData='$extraData' LIMIT 1;";
		$result = $this->dbRead->query($this->lastQuery);
		if ($result === FALSE) return $this->reportError("Failed to load items record matching $server:$itemId:$itemIntLevel:$itemIntType:$itemPotionData:$extraData!");
		
		if ($result->num_rows == 0) return false;
			
		$rowData = $result->fetch_assoc();
		$rowData['__dirty'] = false;
		
		$rowData['countPurchases'] = intval($rowData['countPurchases']);
		$rowData['countSales'] = intval($rowData['countSales']);
		$rowData['countItemPurchases'] = intval($rowData['countItemPurchases']);
		$rowData['countItemSales'] = intval($rowData['countItemSales']);
		$rowData['sumSales'] = floatval($rowData['sumSales']);
		$rowData['sumPurchases'] = floatval($rowData['sumPurchases']);
		
		$rowData['extraData'] = $extraData;
		
		return $rowData;
	}
	
	
	public function LoadItemById($itemId, $extraData = "")
	{
		$itemId = $this->db->real_escape_string($itemId);
		
		$this->lastQuery = "SELECT * FROM items WHERE id='$itemId';";
		$result = $this->dbRead->query($this->lastQuery);
		if ($result === FALSE) return $this->reportError("Failed to load items record with id #$itemId!");
		
		if ($result->num_rows == 0) return false;
		
		$rowData = $result->fetch_assoc();
		$rowData['__dirty'] = false;
		
		$rowData['countPurchases'] = intval($rowData['countPurchases']);
		$rowData['countSales'] = intval($rowData['countSales']);
		$rowData['countItemPurchases'] = intval($rowData['countItemPurchases']);
		$rowData['countItemSales'] = intval($rowData['countItemSales']);
		$rowData['sumSales'] = floatval($rowData['sumSales']);
		$rowData['sumPurchases'] = floatval($rowData['sumPurchases']);
		
		$rowData['extraData'] = $extraData;
		
		return $rowData;
	}
	
	
	public function LoadMinedItem($itemId, $itemIntLevel, $itemIntType, $itemPotionData)
	{
		$itemId = $this->dbLog->real_escape_string($itemId);
		$itemIntLevel = $this->dbLog->real_escape_string($itemIntLevel);
		$itemIntType = $this->dbLog->real_escape_string($itemIntType);
		$itemPotionData = $this->dbLog->real_escape_string($itemPotionData);
		
		$this->lastQuery = "SELECT * FROM uesp_esolog.minedItem WHERE itemId='$itemId' AND internalLevel='$itemIntLevel' AND internalSubType='$itemIntType' AND potionData='$itemPotionData' LIMIT 1;";
		$result = $this->dbLog->query($this->lastQuery);
		if ($result === FALSE) return $this->reportError("Failed to load mined item data record matching $itemId:$itemIntLevel:$itemIntType:$itemPotionData!");
		
		if ($result->num_rows == 0)
		{
			$this->lastQuery = "SELECT * FROM uesp_esolog.minedItem WHERE itemId='$itemId' AND internalLevel='1' AND internalSubType='1' LIMIT 1;";
			$result = $this->dbLog->query($this->lastQuery);
			if ($result === FALSE) return $this->reportError("Failed to load mined item data record matching $itemId:1:1:$itemPotionData!");
			
			if ($result->num_rows == 0 && self::ALT_MINEDITEM_TABLE != "")
			{
				$table = self::ALT_MINEDITEM_TABLE;
				$this->lastQuery = "SELECT * FROM uesp_esolog.$table WHERE itemId='$itemId' AND internalLevel='$itemIntLevel' AND internalSubType='$itemIntType' AND potionData='$itemPotionData' LIMIT 1;";
				$result = $this->dbLog->query($this->lastQuery);
				if ($result === FALSE) return $this->reportError("Failed to load mined item data record from $table matching $itemId:$itemIntLevel:$itemIntType:$itemPotionData!");
				
				if ($result->num_rows == 0)
				{
					$this->lastQuery = "SELECT * FROM uesp_esolog.$table WHERE itemId='$itemId' AND internalLevel='1' AND internalSubType='1' LIMIT 1;";
					$result = $this->dbLog->query($this->lastQuery);
					if ($result === FALSE) return $this->reportError("Failed to load mined item data record from $table matching $itemId:1:1:$itemPotionData!");
				}
			}
		}
		
		if ($result->num_rows == 0) return $this->reportError("Failed to find mined item data record matching $itemId:$itemIntLevel:$itemIntType:$itemPotionData!");
		
		return $result->fetch_assoc();
	}
	
	
	public function MakeNiceItemName($name)
	{
		$name = explode('||', $name)[0];
		return MakeEsoTitleCaseName($name);
	}
	
	
	public function CreateNewItemByKey($server, $itemId, $level, $quality, $trait, $potionData, $extraData, $itemLink, $itemRawData)
	{
		$minedItemData = $this->LoadMinedItem($itemId, $itemRawData['internalLevel'], $itemRawData['internalSubType'], $potionData);
		
		if ($minedItemData === false)
		{
			$icon = "";
			$name = "";
			$setName = "";
			$equipType = "0";
			$weaponType = "0";
			$itemType = "0";
			$armorType = "0";
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
		}
		
			/* Some potions have incorrect qualities */
		if ($itemType == 30 || $itemType == 7)
		{
			$quality = 1;
		}
		
			/* Ignore level for recipes */
		if ($itemType == 29)
		{
			$level = 1;
		}
						
		//if ($itemRawData['name'] != null) $name = $itemRawData['name']; 
		if ($itemRawData['icon'] != null) $icon = $itemRawData['icon'];
		
		$safeIcon = $this->db->real_escape_string($icon);
		$safeName = $this->db->real_escape_string($this->MakeNiceItemName($name));
		$safeSetName = $this->db->real_escape_string($setName);
		$server = $this->db->real_escape_string($this->server);
		$internalLevel = $this->db->real_escape_string($itemRawData['internalLevel']);
		$internalSubType = $this->db->real_escape_string($itemRawData['internalSubType']);
		$itemId = $this->db->real_escape_string($itemId);
		$potionData = $this->db->real_escape_string($potionData);
		$extraData = $this->db->real_escape_string($extraData);
		
		$this->lastQuery  = "INSERT INTO items(server, itemId, potionData, level, quality, trait, itemType, equipType, weaponType, armorType, icon, name, setName, internalLevel, internalSubType, extraData) ";
		$this->lastQuery .= "VALUES('$server', '$itemId', '$potionData', '$level', '$quality', '$trait', '$itemType', '$equipType', '$weaponType', '$armorType', \"$safeIcon\", \"$safeName\", \"$safeSetName\", \"$internalLevel\", \"$internalSubType\", \"$extraData\");";
		
		$result = $this->db->query($this->lastQuery);
		if ($result === FALSE) return $this->reportError("Failed to create items record!");
				
		$this->dbWriteCount++;
		
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
		$itemData['weaponType'] = $weaponType;
		$itemData['armorType'] = $armorType;
		$itemData['equipType'] = $equipType;
		$itemData['name'] = $name;
		$itemData['server'] = $server;
		$itemData['setName'] = $setName;
		$itemData['potionData'] = $potionData;
		$itemData['extraData'] = $extraData;
		$itemData['internalLevel'] = $internalLevel;
		$itemData['internalSubType'] = $internalSubType;
		
		$itemData['sumPurchases'] = 0;
		$itemData['sumSales'] = 0;
		$itemData['countPurchases'] = 0;
		$itemData['countSales'] = 0;
		$itemData['countItemPurchases'] = 0;
		$itemData['countItemSales'] = 0;
		
		$itemData['lastPurchaseTimestamp'] = 0;
		$itemData['lastSaleTimestamp'] = 0;
		$itemData['lastSeen'] = 0;
		
		$itemData['goodPrice'] = 0;
		$itemData['goodSoldPrice'] = 0;
		$itemData['goodListPrice'] = 0;
		
		++$this->localNewItemCount;
		
		return $itemData;
	}
	
	
	public function CreateNewItem($server, $itemId, $itemIntLevel, $itemIntType, $itemPotionData, $extraData, $subItemData = null, $itemKey = null)
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
				//$name = $subItemData['itemDesc'];
			}
				
			if ($itemPotionData > 0 && $itemKey != null)
			{
				$keyData = explode(":", $itemKey);
				$level1 = intval($keyData[0]) + intval($keyData[1]);
				if ($level1 != 0) $level = $level1;
			}
		}
		
			/* Some potions have incorrect qualities */
		if ($itemType == 30 || $itemType == 7)
		{
			$quality = 1;
		}
		
			/* Ignore level for recipes */
		if ($itemType == 29)
		{
			$level = 1;
		}

		$safeIcon = $this->db->real_escape_string($icon);
		$safeName = $this->db->real_escape_string($this->MakeNiceItemName($name));
		$safeSetName = $this->db->real_escape_string($setName);
		$server = $this->db->real_escape_string($this->server);
		$itemId = $this->db->real_escape_string($itemId);
		$itemPotionData = $this->db->real_escape_string($itemPotionData);
		$extraData = $this->db->real_escape_string($extraData);
		
		$this->lastQuery  = "INSERT INTO items(server, itemId, potionData, level, quality, trait, itemType, equipType, weaponType, armorType, icon, name, setName, extraData,) ";
		$this->lastQuery .= "VALUES('$server', '$itemId', '$itemPotionData', '$level', '$quality', '$trait', '$itemType', '$equipType', '$weaponType', '$armorType', \"$safeIcon\", \"$safeName\", \"$safeSetName\", \"$extraData\");";
		
		$result = $this->db->query($this->lastQuery);
		if ($result === FALSE) return $this->reportError("Failed to create items record!");
				
		$this->dbWriteCount++;
		
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
		$itemData['weaponType'] = $weaponType;
		$itemData['armorType'] = $armorType;
		$itemData['equipType'] = $equipType;
		$itemData['server'] = $server;
		$itemData['name'] = $name;
		$itemData['setName'] = $setName;
		$itemData['potionData'] = $itemPotionData;
		$itemData['extraData'] = $extraData;
		
		$itemData['sumPurchases'] = 0;
		$itemData['sumSales'] = 0;
		$itemData['countPurchases'] = 0;
		$itemData['countSales'] = 0;
		$itemData['countItemPurchases'] = 0;
		$itemData['countItemSales'] = 0;
		
		$itemData['lastPurchaseTimestamp'] = 0;
		$itemData['lastSaleTimestamp'] = 0;
		$itemData['lastSeen'] = 0;
		
		$itemData['goodPrice'] = 0;
		$itemData['goodSoldPrice'] = 0;
		$itemData['goodListPrice'] = 0;
		
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
		$extraData = "";
		$itemType = 0;
		
		$itemLinkData = $this->ParseItemLink($itemLink);
		if ($itemLinkData === false) return false;
		
		if ($itemLinkData['writ1'] > 0)
		{
			$extraData = "{$itemLinkData['writ1']}:{$itemLinkData['writ2']}:{$itemLinkData['writ3']}:{$itemLinkData['writ4']}:{$itemLinkData['writ5']}:{$itemLinkData['writ6']}";
		}
		
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
				$itemType = $minedItemData['type'];
				$name = $minedItemData['name'];
			}
			else
			{
				if ($quality == null) $quality = 1;
				if ($trait == null) $trait = 0;
				if ($level == null) $level = 1;
			}
		}
		
		if ($level <= 0) $level = 1;
		
		$cacheId = $server . ":" . $itemId . ":" . $level . ":" .$quality . ":" . $trait . ":" . $potionData . ":" . $extraData;
		if ($this->itemData[$cacheId] != null) return $this->itemData[$cacheId];
		
		$itemData = $this->LoadItemByKey($server, $itemId, $level, $quality, $trait, $potionData, $extraData);
		
		if ($itemData === false)
		{
			$itemData = $this->CreateNewItemByKey($server, $itemId, $level, $quality, $trait, $potionData, $extraData, $itemLink, $itemRawData);
		}
		
		if ($itemData !== false)
		{
			$this->itemData[$cacheId] = $itemData;
			$this->itemData[$cacheId]['__dirty'] = false;
		}
		else
		{
			$this->log("\tError: Couldn't load or create new item $itemId!");
			
			$this->itemData[$cacheId] = array();
			$this->itemData[$cacheId] = $name;
			$this->itemData[$cacheId]['id'] = -1;
			$this->itemData[$cacheId]['server'] = $server;
			$this->itemData[$cacheId]['itemId'] = $itemId;
			$this->itemData[$cacheId]['level'] = $level;
			$this->itemData[$cacheId]['quality'] = $quality;
			$this->itemData[$cacheId]['trait'] = $trait;
			$this->itemData[$cacheId]['potionData'] = $potionData;
			$this->itemData[$cacheId]['extraData'] = $extraData;
			$this->itemData[$cacheId]['sumPurchases'] = 0;
			$this->itemData[$cacheId]['sumSales'] = 0;
			$this->itemData[$cacheId]['countPurchases'] = 0;
			$this->itemData[$cacheId]['countSales'] = 0;
			$this->itemData[$cacheId]['countItemPurchases'] = 0;
			$this->itemData[$cacheId]['countItemSales'] = 0;
			$this->itemData[$cacheId]['lastSeen'] = 0;
				
			$this->itemData[$cacheId]['__dirty'] = true;
			$this->itemData[$cacheId]['__error'] = true;
		}
		
		return $this->itemData[$cacheId];
	}
		
	
	public function LoadSaleSearchEntry($itemMyId, $guildId, $listTime, $sellerName)
	{
		$safeTime = intval($listTime);
		$itemMyId = $this->db->real_escape_string($itemMyId);
		$guildId = $this->db->real_escape_string($guildId);
		$safeName = $this->db->real_escape_string($sellerName);
		$server = $this->db->real_escape_string($this->server);
		
		if (self::ESD_LISTTIME_RANGE > 0) 
		{
			$minTime = $safeTime - self::ESD_LISTTIME_RANGE;
			$maxTime = $safeTime + self::ESD_LISTTIME_RANGE;
			
			$this->lastQuery = "SELECT * FROM sales WHERE server='$server' AND itemId='$itemMyId' AND guildId='$guildId' AND listTimestamp>='$minTime' AND listTimestamp<='$maxTime' AND sellerName=\"$safeName\" LIMIT 1;";
		}
		else
		{
			$this->lastQuery = "SELECT * FROM sales WHERE server='$server' AND itemId='$itemMyId' AND guildId='$guildId' AND listTimestamp='$safeTime' AND sellerName=\"$safeName\" LIMIT 1;";
		}
		
		$result = $this->dbRead->query($this->lastQuery);
		if ($result === FALSE) return $this->reportError("Failed to load sales record matching $itemMyId:$guildId:$safeTime:$safeName!");
		
		if ($result->num_rows == 0) return false;
		
		$rowData = $result->fetch_assoc();
		$rowData['__dirty'] = false;
	
		return $rowData;
	}
	
	
	
	public function UpdateSearchEntryId($searchEntry)
	{
		$safeTime = intval($searchEntry['listTimestamp']);
		$itemMyId = $this->db->real_escape_string($searchEntry['itemId']);
		$guildId = $this->db->real_escape_string($searchEntry['guildId']);
		$uniqueId = $this->db->real_escape_string($searchEntry['uniqueId']);
		$safeName = $this->db->real_escape_string($searchEntry['sellerName']);
		$server = $this->db->real_escape_string($this->server);
		
		if (self::ESD_LISTTIME_RANGE > 0) 
		{
			$minTime = $safeTime - self::ESD_LISTTIME_RANGE;
			$maxTime = $safeTime + self::ESD_LISTTIME_RANGE;
			
			$this->lastQuery = "UPDATE sales SET uniqueId='$uniqueId' WHERE server='$server' AND itemId='$itemMyId' AND guildId='$guildId' AND listTimestamp>='$minTime' AND listTimestamp<='$maxTime' AND sellerName=\"$safeName\";";
		}
		else
		{
			$this->lastQuery = "UPDATE sales SET uniqueId='$uniqueId' WHERE server='$server' AND itemId='$itemMyId' AND guildId='$guildId' AND listTimestamp='$safeTime' AND sellerName=\"$safeName\";";
		}
		
		$result = $this->db->query($this->lastQuery);
		if ($result === FALSE) return $this->reportError("Failed to update uniqueId '$uniqueId' sales entry matching $itemMyId:$guildId:$safeTime:$safeName!");
				
		return true;
	}
		
	
	public function LoadSaleSearchEntryById($itemMyId, $guildId, $uniqueId)
	{
		$itemMyId = $this->db->real_escape_string($itemMyId);
		$guildId = $this->db->real_escape_string($guildId);
		$uniqueId = $this->db->real_escape_string($uniqueId);
		$server = $this->db->real_escape_string($this->server);
		
		$this->lastQuery = "SELECT * FROM sales WHERE server='$server' AND itemId='$itemMyId' AND guildId='$guildId' AND sellerName=\"$uniqueId\" LIMIT 1;";
		//$this->lastQuery = "SELECT * FROM sales WHERE uniqueId=\"$uniqueId\" LIMIT 1;";
		
		$result = $this->dbRead->query($this->lastQuery);
		if ($result === FALSE) return $this->reportError("Failed to load sales record matching $itemMyId:$guildId:$uniqueId!");
		
		if ($result->num_rows == 0) return false;
		
		$rowData = $result->fetch_assoc();
		$rowData['__dirty'] = false;
	
		return $rowData;
	}
	
	
	public function LoadSale($itemMyId, $guildId, $eventId)
	{
		$safeEventId = $this->dbRead->real_escape_string($eventId);
		$server = $this->dbRead->real_escape_string($this->server);
		$itemMyId = $this->dbRead->real_escape_string($itemMyId);
		$guildId = $this->dbRead->real_escape_string($guildId);
				
		$this->lastQuery = "SELECT * FROM sales WHERE server='$server' AND itemId='$itemMyId' AND guildId='$guildId' AND eventId='$safeEventId' LIMIT 1;";
		$result = $this->dbRead->query($this->lastQuery);
		if ($result === FALSE) return $this->reportError("Failed to load sales record matching $itemMyId:$guildId:$eventId!");
	
		if ($result->num_rows == 0) return false;
			
		$rowData = $result->fetch_assoc();
		$rowData['__dirty'] = false;
	
		return $rowData;
	}
	
	
	public function CreateNewSaleMM(&$itemData, &$guildData, &$subItemData, &$saleData)
	{
		if ($saleData['quant'] == 0) return false;
		
		$itemId = $this->db->real_escape_string($itemData['id']);
		$guildId = $this->db->real_escape_string($guildData['id']);
		$eventId = $this->db->real_escape_string($saleData['id']);
		$sellerName = $this->db->real_escape_string($saleData['seller']);
		$buyerName = $this->db->real_escape_string($saleData['buyer']);
		$buyTimestamp = $this->db->real_escape_string($saleData['timestamp']);
		$price = $this->db->real_escape_string($saleData['price']);
		$qnt = $this->db->real_escape_string($saleData['quant']);
		$server = $this->db->real_escape_string($this->server);
		$itemLink = $this->db->real_escape_string($saleData['itemLink']);
		
		$this->lastQuery  = "INSERT INTO sales(server, itemId, guildId, sellerName, buyerName, buyTimestamp, timestamp, eventId, price, qnt, itemLink, lastSeen) ";
		$this->lastQuery .= "VALUES('$server', '$itemId', '$guildId', '$sellerName', '$buyerName', '$buyTimestamp', '$buyTimestamp', '$eventId', '$price', '$qnt', '$itemLink', '$buyTimestamp');";
		
		$result = $this->db->query($this->lastQuery);
		if ($result === FALSE) return $this->reportError("Failed to create new sales record!");
				
		$this->dbWriteCount++;

		++$guildData['totalPurchases'];
		$guildData['__dirty'] = true;
		
		$itemData['countPurchases'] += 1;
		$itemData['sumPurchases'] += floatval($saleData['price']);
		$itemData['countItemPurchases'] += intval($saleData['quant']);
		$itemData['__dirty'] = true;
		
		if ($buyTimestamp > 0)
		{
			if ($buyTimestamp > $itemData['lastPurchaseTimestamp']) $itemData['lastPurchaseTimestamp'] = $buyTimestamp;
			if ($buyTimestamp > $itemData['lastSeen']) $itemData['lastSeen'] = $buyTimestamp;
		}		
				
		++$this->localNewSalesCount;
		
		return true;
	}
	
	
	public function CreateNewSale(&$itemData, &$guildData, &$saleData)
	{
		if ($saleData['qnt'] == 0) return false;
		
		$itemId = $this->db->real_escape_string($itemData['id']);
		$guildId = $this->db->real_escape_string($guildData['id']);
		$timestamp = intval($saleData['timeStamp1']);
		$eventId = $this->db->real_escape_string($saleData['eventId']);
		$sellerName = $this->db->real_escape_string($saleData['seller']);
		$buyerName = $this->db->real_escape_string($saleData['buyer']);
		$buyTimestamp = $this->db->real_escape_string($saleData['saleTimestamp']);
		$price = $this->db->real_escape_string($saleData['gold']);
		$qnt = $this->db->real_escape_string($saleData['qnt']);
		$server = $this->db->real_escape_string($this->server);
		$itemLink = $this->db->real_escape_string($saleData['itemLink']);
	
		$this->lastQuery  = "INSERT INTO sales(server, itemId, guildId, sellerName, buyerName, buyTimestamp, timestamp, eventId, price, qnt, itemLink, lastSeen) ";
		$this->lastQuery .= "VALUES('$server', '$itemId', '$guildId', '$sellerName', '$buyerName', '$buyTimestamp', '$buyTimestamp', '$eventId', '$price', '$qnt', '$itemLink', '$timestamp');";
		
		$result = $this->db->query($this->lastQuery);
		if ($result === FALSE) return $this->reportError("Failed to create new sales record!");
				
		$this->dbWriteCount++;
	
		$guildData['totalPurchases'] += 1;
		$guildData['__dirty'] = true;
				
		$itemData['countPurchases'] += 1;
		$itemData['sumPurchases'] += floatval($saleData['gold']);
		$itemData['countItemPurchases'] += intval($saleData['qnt']);
		$itemData['__dirty'] = true;
		
		if ($buyTimestamp > 0 && $buyTimestamp > $itemData['lastPurchaseTimestamp']) $itemData['lastPurchaseTimestamp'] = $buyTimestamp;
		if ($timestamp > 0 && $timestamp > $itemData['lastSeen']) $itemData['lastSeen'] = $timestamp;
				
		++$this->localNewSalesCount;
	
		return true;
	}
	
	
	public function CreateNewSaleSearchEntry(&$itemData, &$guildData, &$saleData)
	{
		if ($saleData['qnt'] == 0) return false;
		
		$itemId = $this->db->real_escape_string($itemData['id']);
		$guildId = $this->db->real_escape_string($guildData['id']);
		$eventId = 0;
		$timestamp = intval($saleData['timeStamp1']);
		$sellerName = $this->db->real_escape_string($saleData['seller']);
		$buyerName = "";
		$buyTimestamp = 0;
		$listTimestamp = intval($saleData['listTimestamp']);
		$price = $this->db->real_escape_string($saleData['price']);
		$qnt = $this->db->real_escape_string($saleData['qnt']);
		$server = $this->db->real_escape_string($this->server);
		$itemLink = $this->db->real_escape_string($saleData['itemLink']);
		
		$uniqueId = 0;
		if ($saleData['uniqueId']) $uniqueId = $this->db->real_escape_string($saleData['uniqueId']);
		
		$this->lastQuery  = "INSERT INTO sales(server, itemId, guildId, sellerName, buyerName, listTimestamp, timestamp, eventId, price, qnt, itemLink, lastSeen, uniqueId) ";
		$this->lastQuery .= "VALUES('$server', '$itemId', '$guildId', '$sellerName', '$buyerName', '$listTimestamp', '$listTimestamp', '$eventId', '$price', '$qnt', '$itemLink', $timestamp, '$uniqueId');";
		
		$result = $this->db->query($this->lastQuery);
		if ($result === FALSE) return $this->reportError("Failed to create new sales record from search entry!");
				
		$this->dbWriteCount++;
	
		$guildData['totalSales'] += 1;
		$guildData['__dirty'] = true;
		
		$itemData['countSales'] += 1;
		$itemData['sumSales'] += floatval($saleData['price']);
		$itemData['countItemSales'] += intval($saleData['qnt']);
		$itemData['__dirty'] = true;
		
		if ($listTimestamp > 0 && $listTimestamp > $itemData['lastSaleTimestamp']) $itemData['lastSaleTimestamp'] = $listTimestamp;
		if ($timestamp > 0 && $timestamp > $itemData['lastSeen']) $itemData['lastSeen'] = $timestamp;
			
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
		
		$this->log("$name: Found {$this->localItemCount} items ({$this->localNewItemCount} new) and {$this->localSalesCount} sales ({$this->localNewSalesCount} new) in MM data!");
		return true;
	}
	
	
	public function ParseMMItemData($itemId, &$itemData)
	{
		//$this->log("Parsing item ID #$itemId...");
		
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
		//$this->log("\tFound sale for item $itemLink");
		
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
			//$this->log("Found duplicate sale: {$itemData['id']}:{$guildData['id']}:{$saleData['id']}");
		}
		
		return true;
	}
	
	
	public function CheckDbWriteSleep()
	{
		
		if ($this->dbWriteCount >= $this->dbWriteNextSleepCount)
		{
			$this->WaitForSlaveDatabase();
		}
		
	}
	
	
	public function ShowParseSummary()
	{
		$this->log("Found {$this->localNewSalesCount} new sales and {$this->localNewItemCount} new items!");
	}
	
	
	public function WaitForSlaveMasterPos()
	{
		if (!$this->waitForSlave) return true;
		
		$query = "SHOW MASTER STATUS;";
		$result = $this->db->query($query);
		if ($result === false) return $this->reportError("Failed to query database master for status!" . $this->db->error);
		
		$masterData = $result->fetch_assoc();
		$masterPos = intval($masterData['Position']);
		$checkCount = 0;
		
		do {
			$query = "SHOW SLAVE STATUS;";
			$result = $this->dbSlave->query($query);
			if ($result === false) return $this->reportError("Failed to query database slave for status!" . $this->dbSlave->error);
			
			$slaveData = $result->fetch_assoc();
			$slavePos = intval($slaveData['Exec_Master_Log_Pos']);
			$slaveLag = $slaveData['Seconds_Behind_Master'];
			
			if ($slavePos >= $masterPos) return true;
			
			++$checkCount;
			
			$this->log("Slave lag is $slaveLag. Master position is $masterPos. Slave position is $slavePos.");
			//$this->log("Waiting for slave position to be reach original master position!");
			sleep(1);
			
		} while ($checkCount < $this->maxSlaveLagChecks);
		
		$this->log("Exceeded {$this->maxSlaveLagChecks} slave database lag checks...resuming writes!");
		return true;
	}
	
	
	public function WaitForSlaveDatabase()
	{
		
		if (!$this->waitForSlave)
		{
			$this->log("Exceeded {$this->dbWriteNextSleepCount} DB writes...sleeping for {$this->dbWriteCountSleep} sec...");
			$this->dbWriteNextSleepCount = $this->dbWriteCount + $this->dbWriteCountPeriod;
			sleep($this->dbWriteCountSleep);
			return;
		}
		
		$checkCount = 0;
		//$this->log("Exceeded {$this->dbWriteNextSleepCount} DB writes...checking slave lag...");
		$this->dbWriteNextSleepCount = $this->dbWriteCount + $this->dbWriteCountPeriod;
		sleep($this->dbWriteCountSleep);
		
		do {
			$query = "SHOW SLAVE STATUS;";
			$result = $this->dbSlave->query($query);
			if ($result === false) return $this->reportError("Failed to query database slave for status!" . $this->dbSlave->error);
			
			$slaveData = $result->fetch_assoc();
			
			$query = "SHOW MASTER STATUS;";
			$result = $this->db->query($query);
			if ($result === false) return $this->reportError("Failed to query database master for status!" . $this->db->error);
			
			$masterData = $result->fetch_assoc();
			
			$masterPos = $masterData['Position'];
			$slavePos = $slaveData['Exec_Master_Log_Pos'];
			$slaveLag = $slaveData['Seconds_Behind_Master'];
			
			if ($slaveLag < $this->maxAllowedSlaveLag) 
			{
				$this->log("Slave database lag is $slaveLag sec...resuming writes!");
				return true;
			}
			
			$this->log("Slave lag is $slaveLag. Master position is $masterPos. Slave position is $slavePos.");
			$this->log("Waiting for slave database lag to be under {$this->maxAllowedSlaveLag} sec!");
			sleep($this->dbWriteCountSleep);
		} while ($checkCount < $this->maxSlaveLagChecks);
		
		$this->log("Exceeded {$this->maxSlaveLagChecks} slave database lag checks...resuming writes!");
		return true;
	}
	
	
	public function IsValidSalesTimestamp ($timestamp)
	{
		$diff = time() - intval($timestamp);
		if ($diff > self::IGNORE_NEWSALES_TIMESECONDS) return false;
		return true;
	}
	
	
	public function AreTrendsNeededForSalesData ($salesData)
	{
		$now = time();
		
		if (count($salesData) < self::TRENDS_MIN_SALES_NUMBER) return false;
		
		$thisWeekIndex = floor($now / self::TRENDS_PERIODSECONDS);
		$trendStartWeek = $thisWeekIndex - floor(self::TRENDS_START_TIMESECONDS / self::TRENDS_PERIODSECONDS);
		
		foreach ($salesData as $sale)
		{
			$thatWeekIndex = floor(intval($sale['timestamp']) / self::TRENDS_PERIODSECONDS);
			$weekIndex = $trendStartWeek - $thatWeekIndex;
			if ($weekIndex >= 0) return true;
		}
		
		return false;
	}
	
	
	public function ComputeTrendAverages(&$newTrend, $trendData)
	{
		foreach ($trendData as &$sale)
		{
			if ($sale['__isold'] !== true) $newTrend['ids'][] = $sale['id'];
			
			++$newTrend['both']['count'];
			$newTrend['both']['itemCount'] += intval($sale['qnt']);
			$newTrend['both']['sum'] += intval($sale['unitPrice']);
			
			if ($sale['listTimestamp'] > 0)
			{
				++$newTrend['sell']['count'];
				$newTrend['sell']['itemCount'] += intval($sale['qnt']);
				$newTrend['sell']['sum'] += intval($sale['unitPrice']);
			}
			else if ($sale['buyTimestamp'] > 0)
			{
				++$newTrend['buy']['count'];
				$newTrend['buy']['itemCount'] += intval($sale['qnt']);
				$newTrend['buy']['sum'] += intval($sale['unitPrice']);
			}
		}
		
		if ($newTrend['both']['count'] > 0) $newTrend['both']['avg'] = $newTrend['both']['sum'] / $newTrend['both']['count'];
		if ($newTrend['buy']['count']  > 0) $newTrend['buy']['avg']  = $newTrend['buy']['sum']  / $newTrend['buy']['count'];
		if ($newTrend['sell']['count'] > 0) $newTrend['sell']['avg'] = $newTrend['sell']['sum'] / $newTrend['sell']['count'];
	}
	
	
	public function ComputeTrendStdDev(&$newTrend, $trendData)
	{
		foreach ($trendData as &$sale)
		{
			$newTrend['both']['diffsum'] += pow(abs(intval($sale['unitPrice']) - $newTrend['both']['avg']), 2);
			
			if ($sale['listTimestamp'] > 0)
			{
				$newTrend['sell']['diffsum'] += pow(abs(intval($sale['unitPrice']) - $newTrend['sell']['avg']), 2);
			}
			else if ($sale['buyTimestamp'] > 0)
			{
				$newTrend['buy']['diffsum'] += pow(abs(intval($sale['unitPrice']) - $newTrend['buy']['avg']), 2);
			}
		}
		
		if ($newTrend['both']['count'] > 0)
		{
			$newTrend['both']['stddev'] = sqrt($newTrend['both']['diffsum'] / $newTrend['both']['count']);
			$newTrend['both']['low']  = $newTrend['both']['avg'] - $newTrend['both']['stddev'];
			$newTrend['both']['high'] = $newTrend['both']['avg'] + $newTrend['both']['stddev'];
			if ($newTrend['both']['low'] < 0) $newTrend['both']['low'] = 0;
		}
		
		if ($newTrend['buy']['count'] > 0)
		{
			$newTrend['buy']['stddev'] = sqrt($newTrend['buy']['diffsum'] / $newTrend['buy']['count']);
			$newTrend['buy']['low']  = $newTrend['buy']['avg'] - $newTrend['buy']['stddev'];
			$newTrend['buy']['high'] = $newTrend['buy']['avg'] + $newTrend['buy']['stddev'];
			if ($newTrend['buy']['low'] < 0) $newTrend['buy']['low'] = 0;
		}
		
		if ($newTrend['sell']['count'] > 0)
		{
			$newTrend['sell']['stddev'] = sqrt($newTrend['sell']['diffsum'] / $newTrend['sell']['count']);
			$newTrend['sell']['low']  = $newTrend['sell']['avg'] - $newTrend['sell']['stddev'];
			$newTrend['sell']['high'] = $newTrend['sell']['avg'] + $newTrend['sell']['stddev'];
			if ($newTrend['sell']['low'] < 0) $newTrend['sell']['low'] = 0;
		}
	}
	
	
	public function ComputeMedian ($values, $isSorted = false)
	{
		$sum = 0;
		$count = count($values);
		if ($count == 0) return 0;
		
		if (!$isSorted) sort($values);
		
		foreach ($values as $value)
		{
			$sum += $value;
		}
		
		$avg = $sum / $count;
		$middleIndex = floor(($count - 1)/2);
		if ($count % 2) return $values[$middleIndex];
		
		return ($values[$middleIndex] + $values[$middleIndex+1])/2;
	}
	
	
	public function ComputeQuartiles ($values)
	{
		$result = array( 0 => 0, 1 => 0, 2 => 0);
		
		$count = count($values);
		if ($count == 0) return $result;
		
		if ($count == 1)
		{
			$result[0] = $values[0];
			$result[1] = $values[0];
			$result[2] = $values[0];
			return $result;
		}
		
		sort($values);
		
		if ($count == 2)
		{
			$result[0] = $values[0];
			$result[1] = ($values[0] + $values[1])/2;
			$result[2] = $values[1];
			return $result;
		}
		
		$median = $this->ComputeMedian($values, true);
		
		if ($count == 3)
		{
			$result[0] = $values[0];
			$result[1] = $median;
			$result[2] = $values[2];
			return $result;
		}
		
		$result[1] = $median;
		
		$middleIndex = floor(($count - 1)/2);
		
		if ($count % 2)
		{
			$lowerHalf = array_slice($values, 0, $middleIndex + 1);
			$upperHalf = array_slice($values, $middleIndex);
		}
		else
		{
			$lowerHalf = array_slice($values, 0, $middleIndex);
			$upperHalf = array_slice($values, $middleIndex);
		}
		
		$result[0] = $this->ComputeMedian($lowerHalf, true);
		$result[2] = $this->ComputeMedian($upperHalf, true);
		
		return $result;
	}
	
	
	public function ComputeTrendQuartiles(&$newTrend, $trendData)
	{
		$bothSales = array();
		$buySales = array();
		$sellSales = array();
		
		foreach ($trendData as &$sale)
		{
			$price = intval($sale['unitPrice']);
			$bothSales[] = $price;
			
			if ($sale['listTimestamp'] > 0)
				$sellSales[] = $price;
			else if ($sale['buyTimestamp'] > 0)
				$buySales[] = $price;
		}
		
		$quartiles = $this->ComputeQuartiles($bothSales);
		$newTrend['both']['low']  = $quartiles[0];
		$newTrend['both']['avg']  = $quartiles[1];
		$newTrend['both']['high'] = $quartiles[2];
		
		if (count($buySales) > 0)
		{
			$quartiles = $this->ComputeQuartiles($buySales);
			$newTrend['buy']['low']  = $quartiles[0];
			$newTrend['buy']['avg']  = $quartiles[1];
			$newTrend['buy']['high'] = $quartiles[2];
		}
		
		if (count($sellSales) > 0)
		{
			$quartiles = $this->ComputeQuartiles($sellSales);
			$newTrend['sell']['low']  = $quartiles[0];
			$newTrend['sell']['avg']  = $quartiles[1];
			$newTrend['sell']['high'] = $quartiles[2];
		}
	}
	
	
	public function AverageTrendData($prevTrend, $currentTrend, $nextTrend)
	{
		if ($currentTrend == null) return $currentTrend;
		
		$trends = array();
		if ($prevTrend) $trends[] = $prevTrend;
		$trends[] = $currentTrend;
		if ($nextTrend) $trends[] = $nextTrend;
		
		$newTrend = $currentTrend;
		$newTrend['sell']['low'] = 0;
		$newTrend['sell']['avg'] = 0;
		$newTrend['sell']['high'] = 0;
		$newTrend['buy']['low'] = 0;
		$newTrend['buy']['avg'] = 0;
		$newTrend['buy']['high'] = 0;
		$newTrend['both']['low'] = 0;
		$newTrend['both']['avg'] = 0;
		$newTrend['both']['high'] = 0;
		
		$count = 0;
		$buyCount = 0;
		$sellCount = 0;
		
		foreach ($trends as $weekIndex => $trend)
		{
			if ($trend == null) continue;
			
			++$count;
			$newTrend['both']['low'] += $trend['both']['low'];
			$newTrend['both']['avg'] += $trend['both']['avg'];
			$newTrend['both']['high'] += $trend['both']['high'];
			
			if ($trend['sell']['count'] > 0)
			{
				++$sellCount;
				$newTrend['sell']['low'] += $trend['sell']['low'];
				$newTrend['sell']['avg'] += $trend['sell']['avg'];
				$newTrend['sell']['high'] += $trend['sell']['high'];
			}
			
			if ($trend['buy']['count'] > 0)
			{
				++$buyCount;
				$newTrend['buy']['low'] += $trend['buy']['low'];
				$newTrend['buy']['avg'] += $trend['buy']['avg'];
				$newTrend['buy']['high'] += $trend['buy']['high'];
			}
		}
		
		if ($count > 1)
		{
			$newTrend['both']['low']  = $newTrend['both']['low']  / $count;
			$newTrend['both']['avg']  = $newTrend['both']['avg']  / $count;
			$newTrend['both']['high'] = $newTrend['both']['high'] / $count;
		}
		
		if ($sellCount > 1)
		{
			$newTrend['sell']['low']  = $newTrend['sell']['low']  / $count;
			$newTrend['sell']['avg']  = $newTrend['sell']['avg']  / $count;
			$newTrend['sell']['high'] = $newTrend['sell']['high'] / $count;
		}
		
		if ($buyCount > 1)
		{
			$newTrend['buy']['low']  = $newTrend['buy']['low']  / $count;
			$newTrend['buy']['avg']  = $newTrend['buy']['avg']  / $count;
			$newTrend['buy']['high'] = $newTrend['buy']['high'] / $count;
		}
		
		return $newTrend;
	}
	
	
	public function AverageTrends($trendsData)
	{
		$newTrends = array();
		
		ksort($trendsData);
		reset($trendsData);
		
		$count = count($trendsData);
		$current = current($trendsData);
		$next = next($trendsData);
		reset($trendsData);
		
		while (current($trendsData) !== false)
		{
			$currentTrend = current($trendsData);
			$key = key($trendsData);
			$prevTrend = prev($trendsData);
			
			if ($prevTrend === false) 
				reset($trendsData);
			else
				next($trendsData);
			
			$nextTrend = next($trendsData);
			
			$newTrends[$key] = $this->AverageTrendData($prevTrend, $currentTrend, $nextTrend);
		}
		
		return $newTrends;
	}
	
	
	public function ComputeTrendsForSalesData(&$salesData)
	{
		$trends = array();
		$trends['ids'] = array();
		$trends['weeks'] = array();
		
		$trendsData = array();
		$now = time();
		$thisWeekIndex = floor($now / self::TRENDS_PERIODSECONDS);
		$trendStartWeek = $thisWeekIndex - floor(self::TRENDS_START_TIMESECONDS / self::TRENDS_PERIODSECONDS);
		$count = 0;
		
		foreach ($salesData as &$sale)
		{
			$thatWeekIndex = floor(intval($sale['timestamp']) / self::TRENDS_PERIODSECONDS);
			$weekIndex = $trendStartWeek - $thatWeekIndex;
			if ($weekIndex < 0) continue;
			
			++$count;
			
			if ($trendsData[$weekIndex] == null) $trendsData[$weekIndex] = array();
			$trendsData[$weekIndex][] = &$sale;
			
				// Only move original sales to oldSales table
			if ($sale['__isold'] !== true) $trends['ids'][] = $sale['id'];
		}
		
		$totalCount = count($salesData);
		if ($totalCount <= 0) return false;
		$count1 = count($trendsData);
		
		$this->log("\tUsing $count ($count1 weeks) of $totalCount sales for trends calculation...");
		
		foreach ($trendsData as $weekIndex => &$trendData)
		{
			$newTrend = array();
			$newTrend['ids'] = array();
			$newTrend['sell'] = array('count' => 0, 'itemCount' => 0, 'sum' => 0, 'avg' => 0, 'diffsum' => 0, 'stddev' => 0, 'low' => 0, 'high' => 0);
			$newTrend['buy'] = array('count' => 0, 'itemCount' => 0, 'sum' => 0, 'avg' => 0, 'diffsum' => 0, 'stddev' => 0, 'low' => 0, 'high' => 0);
			$newTrend['both'] = array('count' => 0, 'itemCount' => 0, 'sum' => 0, 'avg' => 0, 'diffsum' => 0, 'stddev' => 0, 'low' => 0, 'high' => 0);
			
			$newTrend['timestamp'] = ($trendStartWeek - $weekIndex) * self::TRENDS_PERIODSECONDS;
			
			$this->ComputeTrendAverages($newTrend, $trendData);
			//$this->ComputeTrendStdDev($newTrend, $trendData);
			$this->ComputeTrendQuartiles($newTrend, $trendData);
			
			$trends['weeks'][$weekIndex] = $newTrend;
		}
		
		if (self::TRENDS_DO_AVERAGE)
		{
			$trends['weeks'] = $this->AverageTrends($trends['weeks']);
		}
		
		if (count($trends['weeks']) <= 0) return $this->log("\tSkipping due to no trends data points!");
		return $trends;
	}
	
	
	public function MoveTrendData($salesIds)
	{
		$count = count($salesIds);
		if ($count <= 0) return true;
		
		$chunkedIds = array_chunk($salesIds, self::TRENDS_MOVE_BATCHSIZE);
		
		foreach ($chunkedIds as $tempIds)
		{
			$count = count($tempIds);
			$ids = implode(",", $tempIds);
			
			$startTime = microtime(true);
			
			$this->lastQuery = "INSERT INTO oldSales SELECT * FROM sales WHERE id IN ($ids);";
			$result = $this->db->query($this->lastQuery);
			if ($result === false) return $this->reportError("Failed to move $count old sales data!");
			
			$diff = ((microtime(true) - $startTime)*1000);
			$this->log("\t\tInserted $count old sales for item in $diff ms");
			$startTime = microtime(true);
			
			$this->lastQuery = "DELETE FROM sales WHERE id IN ($ids);";
			$result = $this->db->query($this->lastQuery);
			if ($result === false) return $this->reportError("Failed to delete $count sales data!");
			
			$diff = ((microtime(true) - $startTime)*1000);
			$this->log("\t\tDeleted $count old sales for item in $diff ms");
			$startTime = microtime(true);
			
			$this->trendMoveCount += $count;
			
			usleep(200000);
			$this->WaitForSlaveMasterPos();
		}
		
		return true;
	}
	
	
	public function SaveTrends($trends)
	{
		$itemId = $trends['itemId'];
		$server = $trends['server'];
		$allSalesIds = array();
		
		$startTime = microtime(true);
		
		foreach ($trends['weeks'] as $trend)
		{
			$timestamp = $trend['timestamp'];
			
			$cols = array(
					'server',
					'itemId',
					'timestamp',
					'sellLow',
					'sellMid',
					'sellHigh',
					'sellCount',
					'sellItemCount',
					'buyLow',
					'buyMid',
					'buyHigh',
					'buyCount',
					'buyItemCount',
					'bothLow',
					'bothMid',
					'bothHigh',
			);
			
			$values = array(
					"'" . $this->db->real_escape_string($server) . "'",
					$itemId,
					$timestamp,
					$trend['sell']['low'],
					$trend['sell']['avg'],
					$trend['sell']['high'],
					$trend['sell']['count'],
					$trend['sell']['itemCount'],
					$trend['buy']['low'],
					$trend['buy']['avg'],
					$trend['buy']['high'],
					$trend['buy']['count'],
					$trend['buy']['itemCount'],
					$trend['both']['low'],
					$trend['both']['avg'],
					$trend['both']['high'],
			);
			
			$cols = implode(",", $cols);
			$values = implode(",", $values);
			
			$this->lastQuery = "INSERT INTO trends($cols) VALUES($values);";
			$result = $this->db->query($this->lastQuery);
			
			if ($result === false)
			{
				$this->reportError("Failed to update trends data for $itemId:$server:$timestamp!");
				continue;
			}
			
			++$this->trendCreateCount;
			
			$allSalesIds = array_merge($allSalesIds, $trend['ids']);
		}
		
		$count = count($trends['weeks']);
		$diff = ((microtime(true) - $startTime)*1000);
		$this->log("\t\tSaved $count trends for item in $diff ms");
		
		return $this->MoveTrendData($allSalesIds);
	}
	
	
	public function UpdateTrendsForItem($item)
	{
		$uniqueId = $item['server'] . ":" . $item['id'];
		
		if ($this->trendItemsParsed[$uniqueId] != null)
		{
			$this->log("\tSkipping duplicate item {$item['server']}:{$item['id']}:{$item['name']}");
			return true;
		}
		
		$this->trendItemsParsed[$uniqueId] = 1;
		++$this->trendItemCount;
		$this->log("{$this->trendItemCount}: Updating trends for item {$item['server']}:{$item['id']}:{$item['name']}");
		
		$startTime = microtime(true);
		
		$salesData = $this->LoadAllSalesForItem($item['server'], $item['id'], true);
		if ($salesData === false) return false;
		
		if (self::TRENDS_INCLUDE_OLDSALES)
		{
			$oldSalesData = $this->LoadAllOldSalesForItem($item['id']);
			if ($oldSalesData === false) return false;
			$salesData = array_merge($salesData, $oldSalesData);
		}
		
		$count = count($salesData);
		$diff = ((microtime(true) - $startTime)*1000);
		$this->log("\t\tLoaded $count sales for item in $diff ms");
		$startTime = microtime(true);
		
		if (!$this->AreTrendsNeededForSalesData($salesData))
		{
			$this->log("\tTrends are not needed for this item.");
			return true;
		}
		
		$trends = $this->ComputeTrendsForSalesData($salesData);
		if ($trends === false) return true;
		
		$trends['server'] = $item['server'];
		$trends['itemId'] = $item['id'];
		
		$diff = ((microtime(true) - $startTime)*1000);
		$this->log("\t\tComputed trends for item in $diff ms");
		$startTime = microtime(true);
		
		if (!$this->SaveTrends($trends)) return false;
		
		$diff = ((microtime(true) - $startTime)*1000);
		$this->log("\t\tSaved trends for item in $diff ms");
		$startTime = microtime(true);
		
		sleep(2);
		
		return true;
	}
	
	
	public function UpdateTrendsForAllItems()
	{
		$this->dbSlave = $this->dbRead;
		
		if (self::TRENDS_START_ITEMID > 0)
		{
			$firstId = self::TRENDS_START_ITEMID;
			$lastId = self::TRENDS_END_ITEMID;
			
			if ($lastId <= 0)
			{
				$this->log("Updating trends for items starting at $firstId...");
				$this->lastQuery = "SELECT * FROM items WHERE id>='$firstId';";
			}
			else
			{
				$this->log("Updating trends for items starting at $firstId to $lastId...");
				$this->lastQuery = "SELECT * FROM items WHERE id>='$firstId' AND id<='$lastId';";
			}
		}
		else
		{
			$this->log("Updating trends for all items...");
			$this->lastQuery = "SELECT * FROM items;";
		}
		
		$result = $this->db->query($this->lastQuery);
		if ($result === false) return $this->reportError("Failed to load all item data!");
		
		while ($item = $result->fetch_assoc())
		{
			$this->UpdateTrendsForItem($item);
		}
		
		$this->log("Checked {$this->trendItemCount} items, created {$this->trendCreateCount} trends and moved {$this->trendMoveCount}!");
		
		return true;
	}
	
};


