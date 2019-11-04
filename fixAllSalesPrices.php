<?php 

if (php_sapi_name() != "cli") die("Can only be run from command line!");

require_once("parseSalesData.php");

$parser = new EsoSalesDataParser();

$query = "SELECT * FROM items;";
$result = $parser->db->query($query);
if (!$result) die("Failed to load items!");

print("Updating good prices for all {$result->num_rows} items...\n");
$itemCount = 0;

while (($item = $result->fetch_assoc()))
{
	++$itemCount;
	
	$priceResult = $parser->UpdateItemGoodPrice($item, false, 30);
	
	if ($priceResult)
	{
		print("{$item['id']}: {$item['goodPrice']}, {$item['goodSoldPrice']}, {$item['goodListPrice']}\n");
	}
	else
	{
		print("{$item['id']}: Not updated!\n");
		continue;
	}
	
	$saveResult = $parser->SaveItemStats($item);
	if (!$saveResult) print("\tError saving item data!\n");
}