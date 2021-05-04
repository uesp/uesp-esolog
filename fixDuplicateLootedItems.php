<?php

if (php_sapi_name() != "cli") die("Can only be run from command line!");


require("/home/uesp/secrets/esolog.secrets");
require("esoCommon.php");


class CEsoFixDuplicateLootedItems
{
	public $UPDATE_CLEAN_LINKS = false;
	public $UPDATE_ITEM_DATA = false;
	public $DELETE_EMPTY_LOCATION_ITEMS = true;
	
	public $CLEAN_LINKS = true;
	public $CHECK_SHORT_LINKS = false;		// Shouldn't be needed unless the item link format is changed again
	public $CASE_SENSITIVE = false;
	public $INCLUDE_VALUEQUALITYLEVEL = true;
	public $PERMIT_BLANK_NAMES = true;
	public $INCLUDE_ICON = true;
	public $DB_BATCH_SIZE = 2000;
	public $WAIT_FOR_SLAVE = true;
	public $MAX_SLAVE_LAG_CHECKS = 100;
	public $DB_SLAVELAG_SLEEP = 1;
	
	public $items = array();
	public $itemIndex = array();
	
	public $linkCleanupCount = 0;
	public $duplicateItemCount = 0;
	public $numDeletedItems = 0;
	public $numUpdatedRecords = 0;
	
	public $db = null;
	public $dbSlave = null;
	public $lastQuery = "";
	public $isDbWriteInitialized = false;
	public $isDbReadInitialized = false;
	public $isDbSlaveInitialized = false;
	
	public $missingItemTypes = array();
	
	
	public function __construct()
	{
		$this->InitDatabaseSlave();
		$this->InitDatabaseWrite();
	}
	
	
	protected function ReportError($msg)
	{
		if ($this->db && $this->db->error) $msg .= "\n" . $this->db->error . "\n" . $this->lastQuery;
		print("$msg\n");
		return false;
	}
	
	
	protected function InitDatabaseWrite()
	{
		global $uespEsoLogWriteDBHost, $uespEsoLogWriteUser, $uespEsoLogWritePW, $uespEsoLogDatabase;
		
		if ($this->isDbWriteInitialized) return true;
		
		$this->db = new mysqli($uespEsoLogWriteDBHost, $uespEsoLogWriteUser, $uespEsoLogWritePW, $uespEsoLogDatabase);
		if ($this->db->connect_error) return $this->ReportError("ERROR: Could not connect to esolog database for writing!");
		
		$this->isDbWriteInitialized = true;
		$this->isDbReadInitialized = false;
		return true;
	}
	
	
	protected function InitDatabaseSlave()
	{
		global $uespEsoLogReadDBHost, $uespEsoLogReadUser, $uespEsoLogReadPW, $uespEsoLogDatabase;
		
		if ($this->isDbSlaveInitialized) return true;
		
		$this->dbSlave = new mysqli($uespEsoLogReadDBHost, $uespEsoLogReadUser, $uespEsoLogReadPW, $uespEsoLogDatabase);
		if ($this->dbSlave->connect_error) return $this->reportError("Could not connect to mysql slave database!");
		
		$this->isDbSlaveInitialized = true;
		return true;
	}
	
	
	protected function WaitForSlaveDb()
	{
		if (!$this->WAIT_FOR_SLAVE) return false;
		
		$query = "SHOW MASTER STATUS;";
		$result = $this->db->query($query);
		if ($result === false) return $this->ReportError("Error: Failed to query database master for status!" . $this->db->error);
		
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
	
	
	protected function LoadItems()
	{
		$this->lastQuery = "SELECT * FROM item;";
		$result = $this->db->query($this->lastQuery);
		if ($result === false) return $this->ReportError("Error: Failed to load items!");
		
		while ($item = $result->fetch_assoc())
		{
			$this->items[] = $item;
		}
		
		$count = count($this->items);
		$this->ReportError("Loaded $count items.");
		return true;
	}
	
	
	protected function CleanupLink($link)
	{
		$link = str_replace("|HFFFFFF:", "|H0:", $link);
		$link = preg_replace("/\|h[^\|]+\|h$/", "|h|h", $link);
		
		if ($this->CHECK_SHORT_LINKS)
		{
				// |H0:item:23130:30:1:0:0:0:0:0:0:0:0:0:0:0:0:0:0:0:0:0:0|h|h -- 22 integer fields
			if (preg_match("/\|H0:item:[0-9]+:[0-9]+:[0-9]+:[0-9]+:[0-9]+:[0-9]+:[0-9]+:[0-9]+:[0-9]+:[0-9]+:[0-9]+:[0-9]+:[0-9]+:[0-9]+:[0-9]+:[0-9]+:[0-9]+:[0-9]+:[0-9]+:[0-9]+\|h\|h/", $link))
			{
				$link = str_replace("|h|h", ":0:0|h|h", $link);
			}
			else if (preg_match("/\|H0:item:[0-9]+:[0-9]+:[0-9]+:[0-9]+:[0-9]+:[0-9]+:[0-9]+:[0-9]+:[0-9]+:[0-9]+:[0-9]+:[0-9]+:[0-9]+:[0-9]+:[0-9]+:[0-9]+:[0-9]+:[0-9]+:[0-9]+:[0-9]+:[0-9]+\|h\|h/", $link))
			{
				$link = str_replace("|h|h", ":0|h|h", $link);
			}
		}
		
		return $link;
	}
	
	
	protected function LoadMinedItem($itemId, $intLevel, $intType)
	{
		$safeId = intval($itemId);
		$safeLevel = intval($intLevel);
		$safeType = intval($intType);
		
		if ($intLevel == 0 && $intType > 1) $intType = 0;
		
		$this->lastQuery = "SELECT * FROM minedItem WHERE itemId='$safeId' AND internalLevel='$safeLevel' AND internalSubType='$safeType';";
		$result = $this->db->query($this->lastQuery);
		if ($result === false) return $this->ReportError("\t\tError: Failed to load mined item $itemId:$intLevel:$intType!");
		
		if ($result->num_rows == 0) 
		{
			if ($this->missingItemTypes[$safeLevel] == null) $this->missingItemTypes[$safeLevel] = array();
			if ($this->missingItemTypes[$safeLevel][$safeType] == null) $this->missingItemTypes[$safeLevel][$safeType] = 0;
			++$this->missingItemTypes[$safeLevel][$safeType];
			
			$this->lastQuery = "SELECT * FROM minedItem WHERE itemId='$safeId' AND internalLevel='1' AND internalSubType='1';";
			$result = $this->db->query($this->lastQuery);
			if ($result === false) return $this->ReportError("\t\tError: Failed to load mined item $itemId:$intLevel:$intType!");
			if ($result->num_rows == 0) return $this->ReportError("\t\tWarning: No mined item found matching $itemId:$intLevel:$intType!");
		}
		
		return $result->fetch_assoc();
	}
	
	
	protected function LoadMinedItemSummaries()
	{
		$minedItems = array();
		
		$this->lastQuery = "SELECT * FROM minedItemSummary;";
		$result = $this->db->query($this->lastQuery);
		if ($result === false) return $this->ReportError("Error: Failed to load mined item summaries!");
		
		while ($item = $result->fetch_assoc())
		{
			$id = intval($item['itemId']);
			$minedItems[$id] = $item;
		}
		
		$count = count($minedItems);
		$this->ReportError("Loaded $count mined item summaries.");
		return $minedItems;
	}
	
	
	protected function UpdateItemData()
	{
		$this->ReportError("Updating all item data that having missing fields...");
		$totalCount = count($this->items);
		$updateCount = 0;
		
		$minedItems = $this->LoadMinedItemSummaries();
		if ($minedItems === false) return false;
		
		foreach ($this->items as $i => $item)
		{
			if ($i % 1000 == 0) $this->ReportError("\t$i / $totalCount ($updateCount updated): Updating item data....");
			
			$id = $item['id'];
			$link = $item['link'];
			$name = $item['name'];
			$newName = preg_replace('/\^.+$/', '', $name);
			$needsUpdating = false;
			
			if ($link == "") 
			{
				if ($name != $newName)
				{
					$safeName = $this->db->real_escape_string($newName);
					$this->lastQuery = "UPDATE item SET name='$safeName' WHERE id='$id';";
					$result = $this->db->query($this->lastQuery);
					if ($result === false) $this->ReportError("\t\tError: Failed to update name for item $id!");
					++$updateCount;
				}
				
				continue;
			}
			
			$parsedLink = ParseEsoItemLink($link);
			
			if ($parsedLink === false)
			{
				$this->ReportError("\t\t$i: Failed to parse item link '$link'!");
				continue;
			}
			
			$itemId = intval($parsedLink['itemId']);
			
			if ($itemId <= 0)
			{
				$this->ReportError("\t\t$i: Bad itemId $itemId found in item link '$link'!");
				continue;
			}
			
			$minedItem = $minedItems[$itemId];
			
			if ($minedItem == null)
			{
				$this->ReportError("\t\t$i: Missing item $itemId in item link '$link'!");
				continue;
			}
			
			$style = $item['style'];
			$trait = $item['trait'];
			$quality = $item['quality'];
			$type = $item['type'];
			$equipType = $item['equipType'];
			$craftType = $item['craftType'];
			$value = $item['value'];
			$level = $item['level'];
			$icon = $item['icon'];
			
			$newName = $name;
			if ($newName == "") $newName = $minedItem['name'];
			$newName = preg_replace('/\^.+$/', '', $newName);
			if ($name != $newName) $needsUpdating = true;
			
			if ($icon == "")
			{
				$icon = $minedItem['icon'];
				$needsUpdating = true;
			}
			
			if ($style < 0)
			{
				$style = intval($minedItem['style']);
				$needsUpdating = true;
			}
			
			if ($trait < 0)
			{
				$trait = intval($minedItem['trait']);
				$needsUpdating = true;
			}
			
			if ($quality < 0)
			{
				$quality = GetEsoQualityFromIntType($parsedLink['subtype']);
				$needsUpdating = true;
			}
			
			if ($type < 0)
			{
				$type = intval($minedItem['type']);
				$needsUpdating = true;
			}
			
			if ($equipType < 0)
			{
				$equipType = intval($minedItem['equipType']);
				$needsUpdating = true;
			}
			
			if ($craftType < 0)
			{
				$craftType = intval($minedItem['craftType']);
				$needsUpdating = true;
			}
			
			if ($value < 0)
			{
				$item = $this->LoadMinedItem($itemId, $parsedLink['level'], $parsedLink['subtype']);
				
				if ($item)
				{
					$value = intval($item['value']);
					$needsUpdating = true;
				}
			}
			
			if ($level < 0 || $level == 50)
			{
				$newLevel = GetEsoLevelFromIntType($parsedLink['subtype'], $parsedLink['level']);
				
				if ($newLevel != $level)
				{
					$level = $newLevel;
					$needsUpdating = true;
				}
			}
			
			if (!$needsUpdating) continue;
			
			$safeIcon = $this->db->real_escape_string($icon);
			$safeName = $this->db->real_escape_string($newName);
			$this->lastQuery = "UPDATE item SET name='$safeName', icon='$safeIcon', style='$style', trait='$trait', quality='$quality', type='$type', equipType='$equipType', craftType='$craftType', value='$value', level='$level' WHERE id='$id';";
			$result = $this->db->query($this->lastQuery);
			if ($result === false) $this->ReportError("\t\tError: Failed to update item $id!");
			
			//print("Query: {$this->lastQuery}\n");
			++$updateCount;
		}
		
		$this->ReportError("Updated $updateCount items. Missing item levels/subtypes found are:");
		ksort($this->missingItemTypes);
		$uniqueSubTypes = array();
		
		foreach ($this->missingItemTypes as $level => $levels)
		{
			ksort($levels);
			
			foreach ($levels as $subtype => $count)
			{
				if ($uniqueSubTypes[$subtype] == null) $uniqueSubTypes[$subtype] = 0;
				$uniqueSubTypes[$subtype] += $count;
				
				print("\t$level:$subtype = $count\n");
			}
		}
		
		$this->ReportError("Unique missing subtypes found are:");
		ksort($uniqueSubTypes);
		
		foreach ($uniqueSubTypes as $subtype => $count)
		{
			print("\t$subtype = $count\n");
		}
		
		return true;
	}
	
	
	protected function DeleteEmptyLocationItemsById()
	{
		$this->ReportError("Finding all empty item locations (this might take a while)...");
		$this->lastQuery = "SELECT id FROM location WHERE name='' and itemId=-1 AND type='item';";
		$result = $this->db->query($this->lastQuery);
		if ($result === false) return $this->ReportError("Error: Failed to find empty items from location table!");
		
		$this->ReportError("Found {$result->num_rows} empty item locations!");
		$allRows = array();
		
		while ($row = $result->fetch_assoc())
		{
			$allRows[] = $row['id'];
		}
		
		$rowBatches = array_chunk($allRows, $this->DB_BATCH_SIZE);
		$rowsDeleted = 0;
		
		foreach ($rowBatches as $rowBatch)
		{
			$count = count($rowBatch);
			$this->ReportError("\t$rowsDeleted: Deleting $count empty item locations...");
			
			$ids = implode(",", $rowBatch);
			$this->lastQuery = "DELETE FROM location WHERE id IN ($ids);";
			$result = $this->db->query($this->lastQuery);
			if ($result === false) return $this->ReportError("Error: Failed to delete empty items from location table by ID!");
			
			$rowsDeleted += $count;
			usleep(100000);
			$this->WaitForSlaveDb();
		}
		
		return true;
	}
	
	
	protected function DeleteEmptyLocationItems()
	{
		$this->lastQuery = "DELETE FROM location WHERE name='' and itemId=-1 AND type='item' LIMIT {$this->DB_BATCH_SIZE};";
		$rowsDeleted = 0;
		
		$this->ReportError("Deleting all empty item locations...");
		
		while (true)
		{
			$this->ReportError("\t$rowsDeleted: Deleting {$this->DB_BATCH_SIZE} empty item locations...");
			
			$result = $this->db->query($this->lastQuery);
			if ($result === false) return $this->ReportError("Error: Failed to delete empty items from location table!");
			if ($this->db->affected_rows == 0) break;
			
			$rowsDeleted += $this->db->affected_rows;
			
			usleep(100000);
			$this->WaitForSlaveDb();
		}
		
		$this->ReportError("Deleted $rowsDeleted empty item locations.");
		
		return true;
	}
	
	
	protected function UpdateCleanLinks()
	{
		$this->ReportError("Updating all links that need cleaing...");
		$totalCount = count($this->items);
		
		foreach ($this->items as $i => $item)
		{
			$id = $item['id'];
			$link = $item['link'];
			
			$link = $this->CleanupLink($link);
			if ($link == $item['link']) continue;
			
			$this->items[$i]['link'] = $link;
			if ($this->linkCleanupCount % 1000 == 0) $this->ReportError("\t{$this->linkCleanupCount} ($i/$totalCount): Cleaning links...");
			
			$safeLink = $this->db->real_escape_string($link);
			$this->lastQuery = "UPDATE item SET link='$safeLink' WHERE id='$id';";
			$result = $this->db->query($this->lastQuery);
			
			if ($result === false)
			{
				$this->ReportError("Error: Failed to update item $id!");
				continue;
			}
			
			++$this->linkCleanupCount;
		}
		
		$this->ReportError("Updated {$this->linkCleanupCount} links that needed cleaning.");
		return true;
	}
	
	
	protected function IndexItems()
	{
		$this->ReportError("Indexing items...");
		
		foreach ($this->items as $i => $item)
		{
			$link = $item['link'];
			$name = $item['name'];
			$icon = $item['icon'];
			$quality = $item['quality'];
			$value = $item['value'];
			$level = $item['level'];
			
			if ($this->CLEAN_LINKS)
			{
				$link = $this->CleanupLink($link);
				if ($link != $item['link']) ++$this->linkCleanupCount;
			}
			
			if (!$this->CASE_SENSITIVE)
			{
				$name = strtolower($name);
				$icon = strtolower($icon);
			}
			
			if ($this->PERMIT_BLANK_NAMES && $link != "")
			{
				$name = "";
			}
			
			if (!$this->INCLUDE_ICON)
			{
				$icon = "";
			}
			
			if (!$this->INCLUDE_VALUEQUALITYLEVEL)
			{
				$quality = -1;
				$value = -1;
				$level = -1;
			}
			
			$index = "$link:$name:$icon:$quality:$value:$level";
			
			if ($this->itemIndex[$index] == null) $this->itemIndex[$index] = array();
			$this->itemIndex[$index][] = $i;
		}
		
		$count = count($this->itemIndex);
		$this->ReportError("Found $count unique items with {$this->linkCleanupCount} links needing updates.");
	}
	
	
	protected function MergeDuplicateItem($itemIndexes, $percentDone)
	{
		$numRecipeItems = 0;
		$numIngredientItems = 0;
		$numLocationItems = 0;
		$numTotalItems = 0;
		$numDeletedItems = 0;
		
		$numItemIndexes = count($itemIndexes);
		if ($numItemIndexes <= 1) return true;
		
		$rootIndex = $itemIndexes[0];
		$rootItem = $this->items[$rootIndex];
		if ($rootItem == null) return false;
		$rootId = $rootItem['id'];
		
		$itemIdsToMerge = array();
		
		for ($i = 1; $i < $numItemIndexes; ++$i)
		{
			$itemIndex = $itemIndexes[$i];
			$item = $this->items[$itemIndex];
			if ($item == null) continue;
			
			$itemIdsToMerge[] = $item['id'];
			unset($this->items[$itemIndex]);
		}
		
		$batchItemIds = array_chunk($itemIdsToMerge, $this->DB_BATCH_SIZE);
		
		foreach ($batchItemIds as $itemIds)
		{
			$ids = implode(",", $itemIds);
			$count = count($itemIds);
			
			$this->ReportError("\t\t$rootId ($percentDone): Updating $count item ids...");
			//print("updating ids to $rootId:\n\t");
			//print_r($ids);
			//print("\n");
			
			$this->lastQuery = "UPDATE recipe SET resultItemId='$rootId' WHERE resultItemId IN($ids);";
			$result = $this->db->query($this->lastQuery);
			if ($result) $numRecipeItems += intval($this->db->affected_rows);
			
			$this->lastQuery = "UPDATE ingredient SET itemId='$rootId' WHERE itemId IN($ids);";
			$result = $this->db->query($this->lastQuery);
			if ($result) $numIngredientItems += intval($this->db->affected_rows);
			
			$this->lastQuery = "UPDATE location SET itemId='$rootId' WHERE itemId IN($ids);";
			$result = $this->db->query($this->lastQuery);
			if ($result) $numLocationItems += intval($this->db->affected_rows);
			
			$this->lastQuery = "DELETE FROM item WHERE id IN($ids);";
			$result = $this->db->query($this->lastQuery);
			if ($result) $numDeletedItems += $count;
			
			usleep(100000);
			$this->WaitForSlaveDb();
		}
		
		$this->numDeletedItems += $numDeletedItems;
		$numTotalItems = $numLocationItems + $numRecipeItems + $numIngredientItems;
		$this->numUpdatedRecords += $numTotalItems;
		
		$this->ReportError("\t$rootId: Deleted $numDeletedItems and updated $numTotalItems total uses ($numLocationItems locations, $numRecipeItems recipes, $numIngredientItems ingredients)");
		
		return true;
	}
	
	
	protected function MergeDuplicateItems()
	{
		$this->duplicateItemCount = 0;
		$totalItems = count($this->itemIndex);
		$indexCount = 0;
		
		foreach ($this->itemIndex as $index => $itemIndexes)
		{
			++$indexCount;
			
			if ($indexCount % 1000 == 0)
			{
				$this->ReportError("Checking $indexCount of $totalItems...");
				sleep(4);
			}
			
			$count = count($itemIndexes);
			if ($count <= 1) continue;
			
			++$this->duplicateItemCount;
			
			$this->MergeDuplicateItem($itemIndexes, $indexCount/$totalItems);
			
			//if ($indexCount == 10) break;
		}
		
		$this->ReportError("Found {$this->duplicateItemCount} items that have duplicates.");
	}
	
	
	public function Run()
	{
		if ($this->DELETE_EMPTY_LOCATION_ITEMS) return $this->DeleteEmptyLocationItemsById();
		
		$this->LoadItems();
		
		if ($this->UPDATE_CLEAN_LINKS) return $this->UpdateCleanLinks();
		if ($this->UPDATE_ITEM_DATA) return $this->UpdateItemData();
		
		$this->IndexItems();
		$this->MergeDuplicateItems();
		
		return true;
	}
	
};


$fixItems = new CEsoFixDuplicateLootedItems();
$fixItems->Run();