<?php 

if (php_sapi_name() != "cli") die("Can only be run from command line!");

require_once("parseSalesData.php");

$parser = new EsoSalesDataParser();

$query = "SELECT * FROM items;";
$result = $parser->db->query($query);
if (!$result) die("Failed to load items!");

print("Updating good prices for all items...\n");
$itemCount = 0;

while (($item = $result->fetch_assoc()))
{
	++$itemCount;
	
	$parser->UpdateItemGoodPrice($item, false);
	
	$saleCount = 0;
	$listCount = 0;
	$saleSum = 0;
	$listSum = 0;
	$saleItems = 0;
	$listItems = 0;
	$lastSaleTimestamp = 0;
	$lastListTimestamp = 0;
	
	foreach ($parser->lastLoadedSalesData as $sale)
	{
		if ($sale['buyTimestamp'] > 0)
		{
			++$saleCount;
			$saleSum += $sale['price'];
			$saleItems += $sale['qnt'];
			if ($lastSaleTimestamp < $sale['buyTimestamp']) $lastSaleTimestamp = $sale['buyTimestamp']; 
		}
		else if ($sale['listTimestamp'] > 0)
		{
			++$listCount;
			$listSum += $sale['price'];
			$listItems += $sale['qnt'];
			if ($lastListTimestamp < $sale['listTimestamp']) $lastListTimestamp = $sale['listTimestamp'];
		}
	}
	
	$item['sumPurchases'] = $saleSum;
	$item['countPurchases'] = $saleCount;
	$item['countItemPurchases'] = $saleItems;
	$item['sumSales'] = $listSum;
	$item['countSales'] = $listCount;
	$item['countItemSales'] = $listItems;
	$item['lastPurchaseTimestamp'] = $lastSaleTimestamp;
	$item['lastSaleTimestamp'] = $lastListTimestamp;
	$item['lastSeen'] = max($item['lastSeen'], $lastSaleTimestamp, $lastListTimestamp);
	
	print("\t{$item['id']}: {$item['goodPrice']}, {$item['goodSoldPrice']}, {$item['goodListPrice']}\n");
	
	$saveResult = $parser->SaveItemStats($item);
	if (!$saveResult) print("\tError saving item data!\n");
}