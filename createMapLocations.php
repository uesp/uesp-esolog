<?php

if (php_sapi_name() != "cli") die("Can only be run from command line!");

require("/home/uesp/secrets/esolog.secrets");
require("/home/uesp/secrets/gamemap.secrets");
require("esoCommon.php");


class CEsoCreateMapLocations
{
	public $DO_SINGLE_MAP = "stormhaven";
	
	public $MAP_DB_SUFFIX = "";
	public $WAIT_FOR_SLAVE = true;
	public $MAX_SLAVE_LAG_CHECKS = 100;
	public $DB_SLAVELAG_SLEEP = 1;
	public $UNIQUE_RAWX_DISTANCE = 0.001;
	public $UNIQUE_RAWY_DISTANCE = 0.001;
	
	public $db = null;
	public $dbSlave = null;
	public $dbMap = null;
	public $lastQuery = "";
	public $isDbMapInitialized = false;
	public $isDbReadInitialized = false;
	public $isDbSlaveInitialized = false;
	
	
	public function __construct()
	{
		$this->InitDatabaseRead();
		$this->InitDatabaseMap();
		$this->InitDatabaseSlave();
	}
	
	
	protected function ReportError($msg)
	{
		if ($this->db && $this->db->error) $msg .= "\n" . $this->db->error . "\n" . $this->lastQuery;
		if ($this->dbMap && $this->dbMap->error) $msg .= "\n" . $this->dbMap->error . "\n" . $this->lastQuery;
		print("$msg\n");
		return false;
	}
	
	
	protected function InitDatabaseRead()
	{
		global $uespEsoLogReadDBHost, $uespEsoLogReadUser, $uespEsoLogReadPW, $uespEsoLogDatabase;
		
		if ($this->isDbReadInitialized) return true;
		
		$this->db = new mysqli($uespEsoLogReadDBHost, $uespEsoLogReadUser, $uespEsoLogReadPW, $uespEsoLogDatabase);
		if ($this->db->connect_error) return $this->reportError("Could not connect to mysql esolog database!");
		
		$this->isDbReadInitialized = true;
		return true;
	}
	
	
	protected function InitDatabaseSlave()
	{
		$this->dbSlave = $this->db;
		$this->isDbSlaveInitialized = true;
		return true;
	}
	
	
	protected function InitDatabaseMap()
	{
		global $uespGameMapWriteDBHost, $uespGameMapWriteUser, $uespGameMapWritePW, $uespGameMapDatabase;
		
		if ($this->isDbMapInitialized) return true;
		
		$dbName = $uespGameMapDatabase . $this->MAP_DB_SUFFIX;
		
		$this->dbMap = new mysqli($uespGameMapWriteDBHost, $uespGameMapWriteUser, $uespGameMapWritePW, $dbName);
		if ($this->db->connect_error) return $this->reportError("Could not connect to mysql map database '$dbName'!");
		
		$this->isDbMapInitialized = true;
		return true;
	}
	
	
	protected function WaitForSlaveDb()
	{
		if (!$this->WAIT_FOR_SLAVE) return false;
		
		$query = "SHOW MASTER STATUS;";
		$result = $this->dbMap->query($query);
		if ($result === false) return $this->ReportError("Error: Failed to query database master for status!" . $this->dbMap->error);
		
		$masterData = $result->fetch_assoc();
		$masterPos = intval($masterData['Position']);
		$checkCount = 0;
		
		do {
			$query = "SHOW SLAVE STATUS;";
			$result = $this->dbSlave->query($query);
			if ($result === false) return $this->ReportError("Error: Failed to query database slave for status!" . $this->dbSlave->error);
			
			$slaveData = $result->fetch_assoc();
			$slavePos = intval($slaveData['Exec_Master_Log_Pos']);
			$slaveLag = $slaveData['Seconds_Behind_Master'];
			
			if ($slavePos >= $masterPos) return true;
			
			++$checkCount;
			
			$this->ReportError("\t\tSlave lag is $slaveLag. Master position is $masterPos. Slave position is $slavePos.");
			//$this->ReportError("Waiting for slave position to be reach original master position!");
			sleep($this->DB_SLAVELAG_SLEEP);
			
		} while ($checkCount < $this->MAX_SLAVE_LAG_CHECKS);
		
		$this->ReportError("\t\tExceeded {$this->MAX_SLAVE_LAG_CHECKS} slave database lag checks...resuming writes!");
		return true;
	}
	
	
	protected function LoadLocations ($zone)
	{
		$this->ReportError("Loading all locations for zone '$zone'...");
		
		$safeZone = $this->db->real_escape_string($zone);
		$this->lastQuery = "SELECT * FROM location WHERE zone='$safeZone';";
		$result = $this->db->query($this->lastQuery);
		if ($result === false) return $this->ReportError("Error: Failed to load locations for zone '$zone'!");
		
		$locations = array();
		
		while ($loc = $result->fetch_assoc())
		{
			$locations[] = $loc;
		}
		
		$count = count($locations);
		$this->ReportError("Loaded $count locations for zone '$zone'.");
		
		return $locations;
	}
	
	
	protected function IndexLocations($locations)
	{
		
/*		| id           | bigint(20) | NO   | PRI | NULL    | auto_increment |
		| logId        | bigint(20) | NO   |     | NULL    |                |
		| npcId        | bigint(20) | NO   | MUL | NULL    |                |
		| questId      | bigint(20) | NO   | MUL | NULL    |                |
		| questStageId | bigint(20) | NO   | MUL | NULL    |                |
		| itemId       | bigint(20) | NO   | MUL | NULL    |                |
		| bookId       | bigint(20) | NO   | MUL | NULL    |                |
		| type         | tinytext   | NO   | MUL | NULL    |                |
		| name         | tinytext   | NO   | MUL | NULL    |                |
		| count        | int(11)    | NO   |     | NULL    |                |
		| zone         | tinytext   | NO   | MUL | NULL    |                |
		| x            | int(11)    | NO   |     | NULL    |                |
		| y            | int(11)    | NO   |     | NULL    |                |
		| rawX         | float      | NO   |     | NULL    |                |
		| rawY         | float      | NO   |     | NULL    |                | 

		type:
			book
			fish
				Fishing Hole (no type info)
			item
				Includes Harvested nodes
			npc
			oldquest
			quest
			skyshard
			treasure
				Chest (no lock info in location)
				Heavy Sack
				Safebox
				Thieves Trove
		*/
		$locIndexes = array();
		$uniqueKeys = 0;
		
		$this->ReportError("Creating location indexes...");
		
		foreach ($locations as $loc)
		{
			$type = $loc['type'];
			
			if ($type == "oldquest") continue;
			if ($type == "skyshard") continue;
			
			$npcId = $loc['npcId'];
			$questId = $loc['questId'];
			$questStageId = $loc['questStageId'];
			$itemId = $loc['itemId'];
			$bookId = $loc['bookId'];
			$name = strtolower(trim($loc['name']));
			$x = $loc['x'];
			$y = $loc['y'];
			$rawx = $loc['rawX'];
			$rawy = $loc['rawY'];
			
			if ($npcId <= 0) $npcId = "";
			if ($questId <= 0) $questId = "";
			if ($questStageId <= 0) $questStageId = "";
			if ($itemId <= 0) $itemId = "";
			if ($bookId <= 0) $bookId = "";
			
			//if ($type == "item" && $itemId == "" && $name == "") continue;
			
				// Only index quests by name?
			if ($questId > 0)
			{
				$questId = "";
				$questStageId = "";
			}
			
				// Only index books by name?
			if ($bookId > 0) $bookId = "";
			
				// Only index npcs by name?
			if ($npcId > 0) $npcId = "";
			
			$key = "$npcId:$questId:$questStageId:$itemId:$bookId";
			
			if ($locIndexes[$type] == null) $locIndexes[$type] = array();
			if ($locIndexes[$type][$name] == null) $locIndexes[$type][$name] = array();
			if ($locIndexes[$type][$name][$key] == null) { $locIndexes[$type][$name][$key] = array(); ++$uniqueKeys; }
			
			$locIndexes[$type][$name][$key][] = $loc;
		}
		
		$this->ReportError("Finished creating location indexes with $uniqueKeys unique keys!");
		
		return $locIndexes;
	}
	
	
	protected function CheckDuplicateLocations(&$locIndexes)
	{
		$noDuplicateCount = 0;
		$duplicateLocCount = 0;
		$totalCount = 0;
		$totalLocCount = 0;
		
		foreach ($locIndexes as $type => $locNames)
		{
			foreach ($locNames as $name => $locKeys)
			{
				foreach ($locKeys as $key => $locs)
				{
					$posIndex = array();
					$origPosIndex = -1;
					
					foreach ($locs as $i => $loc)
					{
						//$x = intval($loc['x']);
						//$y = intval($loc['y']);
						$rawx = floatval($loc['rawX']);
						$rawy = floatval($loc['rawY']);
						
						$rawxu = round($rawx / $this->UNIQUE_RAWX_DISTANCE);
						$rawyu = round($rawy / $this->UNIQUE_RAWY_DISTANCE);
						
						$poskey = "$rawxu:$rawyu";
						
						if ($posIndex[$poskey] == null) 
						{
							$posIndex[$poskey] = array();
							$locIndexes[$type][$name][$key][$i]['__origpos'] = -1;
							$origPosIndex = $i;
						}
						else
						{
							$locIndexes[$type][$name][$key][$i]['__origpos'] = $origPosIndex;
						}
						
						$posIndex[$poskey][] = $loc;
						//if ($name == "chest") print("\t\t\t$rawx:$rawy => $rawxu:$rawyu\n");
					}
					
					//if ($name == "chest") print_r($posIndex);
					
					$count1 = count($locs);
					$count2 = count($posIndex);
					++$totalCount;
					$totalLocCount += $count1;
					
					if ($count1 != $count2)
					{
						$dupCount = $count1 - $count2;
						$duplicateLocCount += $dupCount;
						$this->ReportError("\t$type:$name:$key has $dupCount duplicate locations out of $count1.");
					}
					else
					{
						++$noDuplicateCount;
					}
				}
			}
		}
		
		$this->ReportError("Found $totalCount unique locations with $duplicateLocCount duplicate locations out of $totalLocCount total.");
		$this->ReportError("$noDuplicateCount out of $totalCount unique locations had no duplicates.");
	}
	
	
	protected function LoadMapWorld($name)
	{
		$safeName = $this->dbMap->real_escape_string($name);
		$this->lastQuery = "SELECT * FROM world WHERE name='$safeName';";
		$result = $this->dbMap->query($this->lastQuery);
		if ($result === false) return $this->ReportError("Error: Failed to load map matching '$name'!");
		
		if ($result->num_rows > 1) $this->ReportError("Warning: Found more than 1 map matching '$name' ({$result->num_rows})!");
		
		$world = $result->fetch_assoc();
		return $world;
	}
	
	
	protected function ExportMapLocations($map, $locIndexes, $exportMapName)
	{
		if ($exportMapName)
		{
			$exportMap = $this->LoadMapWorld($exportMapName);
			if ($exportMap === false) return $this->ReportError("Error: Failed to find export map '$exportMapName'!");
			$exportMapId = $exportMap['id'];
		}
		else
		{
			$exportMap = $map;
			$exportMapId = $map['id'];
		}
		
		$numTilesX = intval($exportMap['tilesX']);
		$numTilesY = intval($exportMap['tilesY']);
		$posTop = intval($exportMap['posTop']);
		$posBottom = intval($exportMap['posBottom']);
		$posLeft = intval($exportMap['posLeft']);
		$posRight = intval($exportMap['posRight']);
		$posWidth = $posRight - $posLeft;
		$posHeight = $posTop - $posBottom;
		
		$xExtentRight = floor($posWidth * $numTilesX/($numTilesX + 1)) + $posLeft;
		$xExtentLeft = $posLeft;
		$yExtentTop = $posTop;
		$yExtentBottom = floor($posHeight * 1/($numTilesY + 1)) + $posBottom;
		$yExtent = abs($yExtentTop - $yExtentBottom);
		$xExtent = abs($xExtentLeft - $xExtentRight);
		
		$addedLocCount = 0;
		$this->ReportError("Adding locations to $exportMapId...");
		
		//print_r($exportMap);
		//break;
		
		foreach ($locIndexes as $type => $locNames)
		{
			if ($type == "item") continue;
			if ($type == "npc") continue;
			if ($type == "quest") continue;
			if ($type == "book") continue;
			if ($type == "fish") continue;
			
			foreach ($locNames as $name => $locKeys)
			{
				$displayName = ucwords($name);
				if ($displayName == "") $displayName = ucwords($type);
				
				foreach ($locKeys as $key => $locs)
				{
					$iconType = 0;
					
					if ($type == "book")
						$iconType = 191;
					elseif ($type == "fish")
						$iconType = 36;
					elseif ($type == "item")
						$iconType = 90;
					elseif ($type == "npc")
						$iconType = 81;
					elseif ($type == "quest")
						$iconType = 77;
					elseif ($type == "skyshard")
						$iconType = 75;
					elseif ($type == "treasure")
					{
						if ($name == "chest")
							$iconType = 83;
						elseif ($name == "heavy sack")
							$iconType = 89;
						elseif ($name == "safebox")
							$iconType = 143;
						elseif ($name == "thieves trove")
							$iconType = 167;
						else
							$iconType = 132;
					}
					else
						$iconType = 132;
					
					foreach ($locs as $i => $loc)
					{
						$origPosIndex = $loc['__origpos'];
						if ($origPosIndex >= 0) continue;
						
						$rawx = floatval($loc['rawX']);
						$rawy = floatval($loc['rawY']);
						
						if ($rawx < 0 || $rawx > 1) continue;
						if ($rawy < 0 || $rawy > 1) continue;
						
						$newx = floor($rawx * $xExtent) + $xExtentLeft;
						$newy = floor((1 - $rawy) * $yExtent) + $yExtentBottom;
						
						//print("\t\t\t$i: $rawx,$rawy => $newx,$newy\n");
						//continue;
						
						$displayData = "{\"labelPos\":0,\"points\":[$newx, $newy]}";
						
						$cols = array(
								'worldId',
								'revisionId',
								'destinationId',
								'locType',
								'x',
								'y',
								'width',
								'height',
								'name',
								'description',
								'iconType',
								'displayData',
								'wikiPage',
								'displayLevel',
								'visible',
						);
						
						$values = array(
								$exportMapId,
								'-123',
								'0',
								'1',
								$newx,
								$newy,
								'0',
								'0',
								"'".$this->dbMap->real_escape_string($displayName)."'",
								"''",
								$iconType,
								"'".$this->dbMap->real_escape_string($displayData)."'",
								"''",
								'11',
								'1',
						);
						
						$cols = implode(",", $cols);
						$values = implode(",", $values);
						
						$this->lastQuery = "INSERT INTO location($cols) VALUES($values);";
						$result = $this->dbMap->query($this->lastQuery);
						if ($result === false) $this->ReportError("Error: Failed to insert new location into map database ($type:$name:$key:$i)!");
						
						++$addedLocCount;
					}
				}
			}
		}
		
		$this->ReportError("Added $addedLocCount locations to $exportMapId!");
		return true;
	}
	
	
	public function Run()
	{
		$map = $this->LoadMapWorld("stormhaven");
		if ($map === false) return $this->ReportError("Error: Failed to load source map!");
		
		$locations = $this->LoadLocations("stormhaven");
		$locIndexes = $this->IndexLocations($locations);
		$this->CheckDuplicateLocations($locIndexes);
		
		$this->ExportMapLocations($map, $locIndexes, "teststormhaven");
		
		return true;
	}
};


$createMapLocs = new CEsoCreateMapLocations();
$createMapLocs->Run();