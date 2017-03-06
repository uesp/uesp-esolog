<?php 

if (php_sapi_name() != "cli") die("Can only be run from command line!");

require_once("parseSalesData.php");

$parser = new EsoSalesDataParser();

$query = "SELECT * FROM items WHERE itemType=29 AND level > 1;";
$itemResult = $parser->db->query($query);
if (!$itemResult) die("Failed to load items!");

$count = $itemResult->num_rows;
print("Fixing $count recipes with bad level...\n");

while (($item = $itemResult->fetch_assoc()))
{
	$id = $item['id'];
	$itemId = $item['itemId'];
	$server = $item['server'];
	$itemType = $item['itemType'];
	
	$query = "SELECT * FROM items WHERE server='$server' AND itemId=$itemId AND itemType=$itemType AND level=1;";
	$origItemResult = $parser->db->query($query);
	if (!$origItemResult) { print("\t$itemId: Failed to find matching item!\n"); continue; }
	
	print("$id: Fixing item ({$origItemResult->num_rows})...\n");
	if ($origItemResult->num_rows > 1) print("\t$id: WARNING: Found $count matching items for recipe!\n");
	
	if ($origItemResult->num_rows == 0)
	{
		$query = "UPDATE items SET level=1 WHERE id=$id;";
		$writeResult = $parser->db->query($query);
		if (!$writeResult) { print("\t$id: Failed update level for item!\n"); continue; }
	}
	else
	{
		$origItem = $origItemResult->fetch_assoc();
		$origId = $origItem['id'];
		
		$origItem['sumPurchases'] += $item['sumPurchases'];
		$origItem['countPurchases'] += $item['countPurchases'];
		$origItem['countItemPurchases'] += $item['countItemPurchases'];
		$origItem['sumSales'] += $item['sumSales'];
		$origItem['countItemSales'] += $item['countItemSales'];
		
		$origItem['lastPurchaseTimestamp'] = max($origItem['lastPurchaseTimestamp'], $item['lastPurchaseTimestamp']);
		$origItem['lastSaleTimestamp'] = max($origItem['lastSaleTimestamp'], $item['lastSaleTimestamp']);
		$origItem['lastSeen'] = max($origItem['lastSeen'], $item['lastSeen']);
		
		$query = "UPDATE sales SET itemId=$origId WHERE itemId=$id;";
		$writeResult = $parser->db->query($query);
		if (!$writeResult) { print("\t$id: Failed move sales for item $origId!\n"); continue; }
	
		$parser->UpdateItemGoodPrice($origItem, false);
		$parser->SaveItemStats($origItem);
		
		$query = "DELETE FROM items where id=$id;";
		$writeResult = $parser->db->query($query);
		if (!$writeResult) { print("\t$id: Failed to delete item!\n"); continue; }
	}

}