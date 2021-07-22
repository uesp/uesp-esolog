<?php


if (php_sapi_name() != "cli") die("Can only be run from command line!");

require("/home/uesp/secrets/esolog.secrets");
require("esoCommon.php");


class CEsoFixZeroSetValues 
{
	public $TABLE_SUFFIX = "";
	
	public $OUTPUT_SQL_FILENAME = "fixzerosets.sql";

	public $FIXED_SET_VALUES = array(
			"Perfected Spectral Cloak" => [ 2 ],
			"Spectral Cloak" => [ 2 ],
	);
	
	public $db = null;
	
	public $setNames = [];
	
	
	public function __construct()
	{
		$this->InitializeDbWrite();
		file_put_contents($this->OUTPUT_SQL_FILENAME, "");
	}
	
	
	public function ReportError($msg)
	{
		print($msg . "\n");
		return false;
	}
	
	
	public function OutputSql($sql)
	{
		file_put_contents($this->OUTPUT_SQL_FILENAME, $sql . "\n", FILE_APPEND);
	}
	
	
	public function InitializeDbWrite()
	{
		global $uespEsoLogWriteDBHost;
		global $uespEsoLogWriteUser;
		global $uespEsoLogWritePW;
		global $uespEsoLogDatabase;
		global $uespEsoLogReadDBHost;
		global $uespEsoLogReadUser;
		global $uespEsoLogReadPW;
		
		$this->db = new mysqli($uespEsoLogReadDBHost, $uespEsoLogReadUser, $uespEsoLogReadPW, $uespEsoLogDatabase);
		if ($this->db->connect_error) exit("Error: Could not connect to mysql database!");
	}
	
	
	public function LoadSetNames()
	{
		$this->ReportError("Finding sets with 0 values...");
		
		$result = $this->db->query("SELECT setName FROM setSummary{$this->TABLE_SUFFIX} WHERE setBonusDesc LIKE '% 0 %' OR setBonusDesc LIKE ' |cffffff0|r ';");
		if ($result === false) exit("Error: Failed to query setSummary!");
		
		$foundSets = [];
		
		while ($row = $result->fetch_assoc())
		{
			$setName = $row['setName'];
			
			$this->ReportError("\t$setName");
			$this->setNames[] = $setName;
			$foundSets[$setName] = 1;
		}
		
		foreach ($this->FIXED_SET_VALUES as $setName => $value)
		{
			if ($foundSets[$setName] == null) $this->setNames[] = $setName; 
		}
		
		return true;
	}
	
	
	public function LoadItemSummaries($setName)
	{
		$safeName = $this->db->real_escape_string($setName);
		$result = $this->db->query("SELECT * FROM minedItemSummary{$this->TABLE_SUFFIX} WHERE setName='$safeName';");
		if ($result === false) exit("Error: Failed to query minedItemSummary for $setName!");
		
		$itemSummaries = [];
		
		while ($row = $result->fetch_assoc())
		{
			$itemSummaries[] = $row;
		}
		
		return $itemSummaries;
	}
	
	
	public function LoadMatchingItemSummary($setName, $itemSummary)
	{
		$safeName = $this->db->real_escape_string($setName);
		
		$equipType = $itemSummary['equipType'];
		$weaponType = $itemSummary['weaponType'];
		$armorType = $itemSummary['armorType'];
		$itemType = $itemSummary['itemType'];
		$traitType = $itemSummary['trait'];
		
		$query = "SELECT * FROM minedItemSummary{$this->TABLE_SUFFIX} WHERE setName='$safeName' AND equipType='$equipType' AND weaponType='$weaponType' AND armorType='$armorType' AND trait='$traitType' LIMIT 1;";
		
		$result = $this->db->query($query);
		if ($result === false) exit("Error: Failed to query minedItemSummary for matching item for set $setName!");
		
		if ($result->num_rows == 0)
		{
			if ($traitType == 26)
			{
				$traitType = 8;
				$query = "SELECT * FROM minedItemSummary{$this->TABLE_SUFFIX} WHERE setName='$safeName' AND equipType='$equipType' AND weaponType='$weaponType' AND armorType='$armorType' AND trait='$traitType' LIMIT 1;";
				$result = $this->db->query($query);
				if ($result === false) exit("Error: Failed to query minedItemSummary for matching item for set $setName!");
			}
			else if ($traitType == 25)
			{
				$traitType = 18;
				$query = "SELECT * FROM minedItemSummary{$this->TABLE_SUFFIX} WHERE setName='$safeName' AND equipType='$equipType' AND weaponType='$weaponType' AND armorType='$armorType' AND trait='$traitType' LIMIT 1;";
				$result = $this->db->query($query);
				if ($result === false) exit("Error: Failed to query minedItemSummary for matching item for set $setName!");
			}
			
			if ($result->num_rows == 0)
			{
				$this->ReportError("\tError: No matching item found for $setName:{$itemSummary['name']}");
				$this->ReportError("\t\t$query");
				return false;
			}
		}
		
		return $result->fetch_assoc();
	}
	
	
	public function LoadItems($itemId)
	{
		$result = $this->db->query("SELECT * FROM minedItem{$this->TABLE_SUFFIX} WHERE itemId='$itemId';");
		if ($result === false) exit("Error: Failed to query minedItem for matching item $itemId!");
		
		$items = [];
		
		while ($item = $result->fetch_assoc())
		{
			$id = $item['internalLevel'] . ":" . $item['internalSubtype'];
			$items[$id] = $item;
		}
		
		return $items;
	}
	
	
	public function IsManualSetName($setName)
	{
		if ($this->FIXED_SET_VALUES[$setName] != null) return true;
		return false;
	}
	
	
	public function IsFixableSetName($setName)
	{
		if (stripos($setName, "Perfected ") !== false) return true;
		if ($this->IsManualSetName($setName)) return true;
		
		return false;
	}
	
	
	public function ConvertSetDescriptionToText($setDesc, $keepNumbers = true)
	{
		$setText = FormatRemoveEsoItemDescriptionText($setDesc);
		if (!$keepNumbers) $setText = preg_replace('/[0-9\.]+/', '#', $setText);
		$setText = str_replace("  ", " ", $setText);
		$setText = str_replace("  ", " ", $setText);
		
		return $setText;
	}
	
	
	public function FixManualItemSetIndex($setName, $item, $setIndex)
	{
		$manualValues = $this->FIXED_SET_VALUES[$setName];
		if ($manualValues == null) return $this->ReportError("\t\tError: Missing manual values for $setName!");
		
		$itemId = $item['itemId'];
		$id = $item['internalLevel'] . ":" . $item['internalSubtype'];
		
		$setDesc = $item["setBonusDesc$setIndex"];
		$setText = $this->ConvertSetDescriptionToText($setDesc, false);
		if (strpos($setDesc, " 0 ") == false && strpos($setDesc, " |cffffff0|r ") == false) return false;
		
		$setText = $this->ConvertSetDescriptionToText($setDesc);
		$newSetDesc = $setDesc;
		
		$k = 0;
		$replaceCount = 0;
		$expectedCount = count($manualValues);
		
		$newSetDesc = preg_replace_callback('/(?: 0 )|(?: \|cffffff0\|r )/', function($thisMatches) use (&$k, &$replaceCount, $manualValues) {
				$match = $manualValues[$k];
				++$k;
				if ($match == null) return $thisMatches[0];
				++$replaceCount;
				return " |cffffff" . $match . "|r ";
		}, $newSetDesc);
		
		if ($replaceCount != $expectedCount)
		{
			$this->ReportError("\t\tError: $itemId1:$id: Only manually replaced $replaceCount of $expectedCount!");
			$this->ReportError("\t\tset: $setDesc");
			$this->ReportError("\t\tfix: $newSetDesc");
			return false;
		}
		
		$safeDesc = $this->db->real_escape_string($newSetDesc);
		$internalLevel = $item['internalLevel'];
		$internalSubtype = $item['internalSubtype'];
		
		$this->OutputSql("UPDATE minedItem{$this->TABLE_SUFFIX} SET setBonusDesc$setIndex='$safeDesc' WHERE itemId='$itemId' AND internalLevel='$internalLevel' AND internalSubtype='$internalSubtype';");
		
		return true;
	}
	
	
	public function FixManualSet($setName)
	{
		$itemSummaries = $this->LoadItemSummaries($setName);
		
		$this->ReportError("\tManually fixing $setName...");
		
		foreach ($itemSummaries as $itemSummary)
		{
			$itemId = $itemSummary['itemId'];
			
			$this->ReportError("\tManually fixing {$itemSummary['name']} ($itemId)");
			
			$items = $this->LoadItems($itemId);
			$count = count($items);
			$fixedItemCount = 0;
			
			foreach ($items as $item)
			{
				$fixedDescCount = 0;
				
				for ($setIndex = 1; $setIndex <= 7; ++$setIndex)
				{
					if ($this->FixManualItemSetIndex($setName, $item, $setIndex)) ++$fixedDescCount;
				}
				
				if ($fixedDescCount > 0) ++$fixedItemCount;
			}
			
			$this->ReportError("\t\tManually fixed $fixedItemCount of $count items!");
		}
		
		return true;
	}
	
	
	public function FixItemSetIndex($setName, $item1, $item2, $setIndex)
	{
		$itemId1 = $item1['itemId'];
		$itemId2 = $item2['itemId'];
		$id = $item1['internalLevel'] . ":" . $item1['internalSubtype'];
		
		$setDesc1 = $item1["setBonusDesc$setIndex"];
		$setText1 = $this->ConvertSetDescriptionToText($setDesc1, false);
		if (strpos($setDesc1, " 0 ") == false && strpos($setDesc1, " |cffffff0|r ") == false) return false;
		
		$setDesc2 = $item2["setBonusDesc$setIndex"];
		$setText2 = $this->ConvertSetDescriptionToText($setDesc2, false);
		
		if (strcasecmp($setText1, $setText2) != 0 && $setIndex > 1)
		{
			$j = $setIndex - 1;
			$setDesc2 = $item2["setBonusDesc$j"];
			$setText2 = $this->ConvertSetDescriptionToText($setDesc2, false);
			
			if (strcasecmp($setText1, $setText2) != 0 && $setIndex > 2)
			{
				$j = $setIndex - 2;
				$setDesc2 = $item2["setBonusDesc$j"];
				$setText2 = $this->ConvertSetDescriptionToText($setDesc2, false);
			}
		}
		
		if (strcasecmp($setText1, $setText2) != 0)
		{
			$this->ReportError("\t\tError: $itemId1:$id:  Mismatched set descriptions found!");
			$this->ReportError("\t\tset1: $setDesc1");
			$this->ReportError("\t\tset2: $setDesc2");
			return false;
		}
		
		$setText1 = $this->ConvertSetDescriptionToText($setDesc1);
		$setText2 = $this->ConvertSetDescriptionToText($setDesc2);
		
		$matchDesc1 = FormatRemoveEsoItemDescriptionText($setDesc1);
		$matchDesc1 = str_replace("  ", " ", $matchDesc1);
		$matchDesc1 = str_replace("  ", " ", $matchDesc1);
		$matchDesc1 = str_replace("(", "\(", $matchDesc1);
		$matchDesc1 = str_replace(")", "\)", $matchDesc1);
		$matchDesc1 = preg_replace('/ 0 /', ' (.*) ', $matchDesc1);
		
		$hasMatch = preg_match('/' . $matchDesc1 . '/i', $setText2, $matches);
		
		if (!$hasMatch)
		{
			$this->ReportError("\t\tError: $itemId1:$id:  No match found!");
			$this->ReportError("\t\tset1: $setText1");
			$this->ReportError("\t\tset2: $setText2");
			$this->ReportError("\t\tmatch: $matchDesc1");
			return false;
		}
		
		$newSetDesc = $setDesc1;
		
		$k = 1;
		$replaceCount = 0;
		$expectedCount = count($matches) - 1;
		
		$newSetDesc = preg_replace_callback('/(?: 0 )|(?: \|cffffff0\|r )/', function($thisMatches) use ($matches, &$k, &$replaceCount) {
				//print("\t\t$k : {$thisMatches[0]}\n");
				//print("\t\t     {$matches[$k]}\n");
				$match = $matches[$k];
				++$k;
				if ($match == null) return $thisMatches[0];
				++$replaceCount;
				return " |cffffff" . $match . "|r ";
		}, $newSetDesc);
		
		if ($replaceCount != $expectedCount)
		{
			$this->ReportError("\t\tError: $itemId1:$id: Only replaced $replaceCount of $expectedCount!");
			$this->ReportError("\t\tset1: $setDesc1");
			$this->ReportError("\t\tset2: $setDesc2");
			$this->ReportError("\t\tfix1: $newSetDesc");
			return false;
		}
		
		if ($id == "50:370")
		{
			//$this->ReportError("\t\tset1: $setDesc1");
			//$this->ReportError("\t\tset2: $setDesc2");
			//$this->ReportError("\t\tfix1: $newSetDesc");
		}
		
		$safeDesc = $this->db->real_escape_string($newSetDesc);
		$internalLevel = $item1['internalLevel'];
		$internalSubtype = $item1['internalSubtype'];
		
		$this->OutputSql("UPDATE minedItem{$this->TABLE_SUFFIX} SET setBonusDesc$setIndex='$safeDesc' WHERE itemId='$itemId1' AND internalLevel='$internalLevel' AND internalSubtype='$internalSubtype';");
		return true;
	}
	
	
	public function FixItem($setName, $item1, $item2)
	{
		$fixedDescCount = 0;
		
		for ($setIndex = 1; $setIndex <= 7; ++$setIndex)
		{
			if ($this->FixItemSetIndex($setName, $item1, $item2, $setIndex)) ++$fixedDescCount;
		}
		
		return ($fixedDescCount > 0);
	}
	
	
	public function FixItems($setName, $itemId1, $itemId2, $items1, $items2)
	{
		$fixedItemCount = 0;
		
		foreach ($items1 as $id => $item1)
		{
			$item2 = $items2[$id];
			
			if ($item2 == null) 
			{
				$this->ReportError("\tError: Missing $id for item $itemId2!");
				continue;
			}
			
			if ($this->FixItem($setName, $item1, $item2)) ++$fixedItemCount;
		}
		
		$count = count($items1);
		$this->ReportError("\t\tFixed $fixedItemCount of $count items!");
		return true;
	}
	
	
	public function FixSetItemSummary($setName, $itemSummary)
	{
		$matchingSetName = str_ireplace("Perfected ", "", $setName);
		
		$matchingSummary = $this->LoadMatchingItemSummary($matchingSetName, $itemSummary);
		if ($matchingSummary === false) return false;
		
		$itemId1 = $itemSummary['itemId'];
		$itemId2 = $matchingSummary['itemId'];
		
		$this->ReportError("\tFound matching item for {$itemSummary['name']} ($itemId1) : {$matchingSummary['name']} ($itemId2)");
		
		$items1 = $this->LoadItems($itemId1);
		$items2 = $this->LoadItems($itemId2);
		
		$count1 = count($items1);
		$count2 = count($items2);
		//$this->ReportError("\t\tItem Counts: $count1 : $count2");
		
		$this->FixItems($setName, $itemId1, $itemId2, $items1, $items2);
		
		return true;
	}
	
	
	public function FixSetItemSummaries($setName, $itemSummaries)
	{
		foreach ($itemSummaries as $itemSummary)
		{
			$this->FixSetItemSummary($setName, $itemSummary);
		}
		
		return true;
	}
	
	
	public function FixSet($setName)
	{
		if (!$this->IsFixableSetName($setName))
		{
			$this->ReportError("\tSkipping $setName");
			return false;
		}
		
		if ($this->IsManualSetName($setName)) return $this->FixManualSet($setName);
		
		$itemSummaries = $this->LoadItemSummaries($setName);
		$this->FixSetItemSummaries($setName, $itemSummaries);
		
		return true;
	}
	
	
	public function FixAllSets()
	{
		foreach ($this->setNames as $setName)
		{
			$this->FixSet($setName);
		}
	}
	
	
	public function DoFix()
	{
		$this->ReportError("Fixing sets with 0 values...");
		
		$this->LoadSetNames();
		$this->FixAllSets();
	}
};

$fixSets = new CEsoFixZeroSetValues();
$fixSets->DoFix();


