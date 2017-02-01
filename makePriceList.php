<?php

$SERVER = "NA";

if (php_sapi_name() != "cli") die("Can only be run from command line!");

require_once("/home/uesp/secrets/esosalesdata.secrets");

$db = new mysqli($uespEsoSalesDataReadDBHost, $uespEsoSalesDataReadUser, $uespEsoSalesDataReadPW, $uespEsoSalesDataDatabase);
if ($db->connect_error) die("Could not connect to mysql database!");

$query = "SELECT * FROM items WHERE server='$SERVER';";
$result = $db->query($query);
if ($result === false) die("Failed to load items!");

//print("Loaded ".$result->num_rows." items...\n");

$itemData = array();

while (($row = $result->fetch_assoc()))
{
	$itemId = $row['itemId'];
	$level = $row['level'];
	$quality = $row['quality'];
	$trait = $row['trait'];
	$potionData = $row['potionData'];
		
	if (!isset($itemData[$itemId])) $itemData[$itemId] = array();
	if (!isset($itemData[$itemId][$level])) $itemData[$itemId][$level] = array();
	if (!isset($itemData[$itemId][$level][$quality])) $itemData[$itemId][$level][$quality] = array();
	if (!isset($itemData[$itemId][$level][$quality][$trait])) $itemData[$itemId][$level][$quality][$trait] = array();
	if (!isset($itemData[$itemId][$level][$quality][$trait][$potionData])) $itemData[$itemId][$level][$quality][$trait][$potionData] = array();
		
	$saleData = &$itemData[$itemId][$level][$quality][$trait][$potionData];
	
	$count = intval($row['countPurchases']) + intval($row['countSales']);
	$itemCount = intval($row['countItemPurchases']) + intval($row['countItemSales']);
	$sumPrice = floatval($row['sumSales']) + floatval($row['sumPurchases']);
	$avgPrice = $sumPrice / $itemCount;
		
	if ($avgPrice >= 1000)
		$avgPrice = round($avgPrice, 0);
	else if ($avgPrice >= 100)
		$avgPrice = round($avgPrice, 1);
	else
		$avgPrice = round($avgPrice, 2);
	
	$avgPurchasePrice = 0;
	$avgSalePrice = 0;
	
	if ($row['countPurchases'] > 0)$avgPurchasePrice = floatval($row['sumPurchases']) / intval($row['countItemPurchases']);
	if ($row['countSales'] > 0) $avgSalePrice = floatval($row['sumSales']) / intval($row['countItemSales']);
	
	if ($avgPurchasePrice >= 1000)
		$avgPurchasePrice = round($avgPurchasePrice, 0);
	else if ($avgPurchasePrice >= 100)
		$avgPurchasePrice = round($avgPurchasePrice, 1);
	else
		$avgPurchasePrice = round($avgPurchasePrice, 2);

	if ($avgSalePrice >= 1000)
		$avgSalePrice = round($avgSalePrice, 0);
	else if ($avgPrice >= 100)
		$avgSalePrice = round($avgSalePrice, 1);
	else
		$avgSalePrice = round($avgSalePrice, 2);
	
	$saleData[0] = $avgPrice;
	$saleData[1] = $row['countPurchases'];
	$saleData[2] = $row['countSales'];
	$saleData[3] = $count;
	//$saleData[2] = $itemCount;
	//$saleData[2] = $row['countItemPurchases'];
	//$saleData[3] = $avgSalePrice;
	//$saleData[5] = $row['countItemSales'];
}

print("uespLog.SalesPrice = {\n");

foreach ($itemData as $itemId => $levelData)
{
	print("[$itemId]={\n");
	
	foreach ($levelData as $level => $qualityData)
	{
		print("[$level]={");
		
		foreach ($qualityData as $quality => $traitData)
		{
			print("[$quality]={");
			
			foreach ($traitData as $trait => $potionData)
			{
				print("[$trait]={");
				
				foreach ($potionData as $potion => $salesData)
				{
					//print("[$potion]={{$salesData[0]},{$salesData[1]},{$salesData[2]}},");
					//print("[$potion]={{$salesData[0]},{$salesData[1]},{$salesData[2]},{$salesData[3]},{$salesData[4]},{$salesData[5]}},");
					print("[$potion]={{$salesData[0]},{$salesData[1]},{$salesData[2]},{$salesData[3]}},");
				}
				
				print("},");
			}
			
			print("},");
		}
		
		print("},");
	}
	
	print("},\n");
}

print("}\n");