<?php

if (php_sapi_name() != "cli") die("Can only be run from command line!");
print("Looking for and merging duplicate sales items...\n");

require_once("parseSalesData.php");

$parser = new EsoSalesDataParser(true);
$duplicateItems = array();

$startTime = microtime(true);
$result = $parser->dbRead->query("SELECT *, COUNT(*) FROM items GROUP BY server, itemId, level, quality, trait, potionData, extraData HAVING COUNT(*) > 1;");
//SELECT id, server, itemId, level, quality, trait, potionData, extraData, COUNT(*) FROM items GROUP BY server, itemId, level, quality, trait, potionData, extraData HAVING COUNT(*) > 1;

while ($row = $result->fetch_assoc()) {
	$id = $row['id'];
	$duplicateItems[$id] = $row;
}

$diffTime = round(microtime(true) - $startTime, 2);
$count = count($duplicateItems);
print("\tFound $count duplicate items in $diffTime sec!\n");

foreach ($duplicateItems as $dupId => $dupItem) {
	
	$server = $parser->dbRead->real_escape_string($dupItem['server']);
	$itemId= $parser->dbRead->real_escape_string($dupItem['itemId']);
	$level = $parser->dbRead->real_escape_string($dupItem['level']);
	$quality = $parser->dbRead->real_escape_string($dupItem['quality']);
	$trait = $parser->dbRead->real_escape_string($dupItem['trait']);
	$potionData = $parser->dbRead->real_escape_string($dupItem['potionData']);
	$extraData = $parser->dbRead->real_escape_string($dupItem['extraData']);
	
	$result = $parser->dbRead->query("SELECT * FROM items WHERE server='$server' AND itemId='$itemId' AND level='$level' AND quality='$quality' AND trait='$trait' AND potionData='$potionData' AND extraData='$extraData';");
	$items = array();
	
	while ($row = $result->fetch_assoc()) {
		$id = $row['id'];
		
		$row['sumPurchases'] = (float) $row['sumPurchases'];
		$row['countPurchases'] = (int) $row['countPurchases'];
		$row['sumSales'] = (float) $row['sumSales'];
		$row['countSales'] = (int) $row['countSales'];
		$row['lastPurchaseTimestamp'] = (int) $row['lastPurchaseTimestamp'];
		$row['lastSaleTimestamp'] = (int) $row['lastSaleTimestamp'];
		$row['lastSeen'] = (int) $row['lastSeen'];
		
		$items[$id] = $row;
	}
	
	$count = count($items);
	print("\tFound $count items matching: $server:$itemId:$level:$quality:$trait:$potionData:$extraData\n");
	
	$result = $parser->db->query("CREATE TABLE IF NOT EXISTS duplicateItems LIKE items;");
	if ($result === false) die("ERROR: Failed to create duplicateItems table!");
	
	$sumPurchases = 0;
	$countPurchases = 0;
	$sumSales = 0;
	$countSales = 0;
	$countItemSales = 0;
	$lastPurchaseTimestamp = 0;
	$lastSaleTimestamp = 0;
	$lastSeen = 0;
	$maxCountId = -1;
	$maxCount = -1;
	
	foreach ($items as $id => $item) {
		$count = $item['countPurchases'] + $item['countSales'];
		
		if ($count > $maxCount) {
			$maxCount = $count;
			$maxCountId = $id;
		}
		
		$sumPurchases += $item['sumPurchases'];
		$countPurchases += $item['countPurchases'];
		$sumSales += $item['sumSales'];
		$countSales += $item['countSales'];
		$countItemSales += $item['countItemSales'];
		if ($lastPurchaseTimestamp < $item['lastPurchaseTimestamp']) $lastPurchaseTimestamp = $item['lastPurchaseTimestamp'];
		if ($lastSaleTimestamp < $item['lastSaleTimestamp']) $lastSaleTimestamp = $item['lastSaleTimestamp'];
		if ($lastSeen < $item['lastSeen']) $lastSeen = $item['lastSeen'];
	}
	
	print("\t\tRoot Item #$maxCountId with $maxCount sales/purchases: $sumPurchases:$countPurchases:$sumSales:$countSales:$countItemSales:$lastPurchaseTimestamp:$lastSaleTimestamp:$lastSeen\n");
	
	$destItemId = $maxCountId;
	if ($destItemId <= 0) continue;
	$destItem = $items[$destItemId];
	
	foreach ($items as $id => $item) 
	{
		$query = "INSERT INTO duplicateItems SELECT * FROM items WHERE id='$id';";
		//print("\t\t$query\n");
		$result = $parser->db->query($query);
		if ($result === false) die("\tERROR: Failed to move item #$id to duplicateItems table!\n$query\n");
		
		if ($id == $destItemId) continue;
		
		$query = "DELETE FROM items WHERE id='$id';";
		//print("\t\t$query\n");
		$result = $parser->db->query($query);
		if ($result === false) die("\tERROR: Failed to delete item #$id from items table!\n$query\n");
		
		$query = "UPDATE sales SET itemId='$destItemId' where itemId='$id';";
		//print("\t\t$query\n");
		$result = $parser->db->query($query);
		if ($result === false) die("\tERROR: Failed to update sales for item #$id!\n$query\n");
	}
	
	$parser->UpdateItemGoodPrice($destItem);
	
	$goodPrice = $destItem['goodPrice'];
	$goodSoldPrice = $destItem['goodSoldPrice'];
	$goodListPrice = $destItem['goodListPrice'];
	
	$query = "UPDATE items SET sumPurchases='$sumPurchases', countPurchases='$countPurchases', sumSales='$sumSales', countSales='$countSales', countItemSales='$countItemSales', lastPurchaseTimestamp='$lastPurchaseTimestamp', lastSaleTimestamp='$lastSaleTimestamp', lastSeen='$lastSeen', goodPrice='$goodPrice', goodSoldPrice='$goodSoldPrice', goodListPrice='$goodListPrice' WHERE id='$destItemId';";
	//print("\t\t$query\n");
	$result = $parser->db->query($query);
	if ($result === false) die("\tERROR: Failed to update data for item #$destItemId!\n$query\n");
}


 