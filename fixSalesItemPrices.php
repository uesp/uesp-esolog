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
	
	print("\t{$item['id']}: {$item['goodPrice']}, {$item['goodSoldPrice']}, {$item['goodListPrice']}\n");
		
	$goodPrice = $item['goodPrice'];
	$goodListPrice = $item['goodListPrice'];
	$goodSoldPrice = $item['goodSoldPrice'];
	
	$query = "UPDATE items SET ";
	$query .= "goodPrice=$goodPrice, goodListPrice=$goodListPrice, goodSoldPrice=$goodSoldPrice ";
	$query .= "WHERE id={$item['id']};";
	
	$saveResult = $parser->db->query($query);
	if (!$saveResult) print("\tError saving item data!\n");
}