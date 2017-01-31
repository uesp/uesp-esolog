<?php 

if (php_sapi_name() != "cli") die("Can only be run from command line!");

require_once("/home/uesp/secrets/esosalesdata.secrets");

$db = new mysqli($uespEsoSalesDataWriteDBHost, $uespEsoSalesDataWriteUser, $uespEsoSalesDataWritePW, $uespEsoSalesDataDatabase);
if ($db->connect_error) die("Could not connect to mysql database!");


$query = "SELECT id from items;";
$result = $db->query($query);
$itemIds = array();

while (($row = $result->fetch_assoc()))
{
	$itemIds[] = $row['id'];
}

print ("Found ".count($itemIds)." items in sales database.\n");

foreach ($itemIds as $i => $itemId)
{
	if (($i % 100) == 0) print("Updating item $itemId...\n");
	
	$query = "SELECT max(buyTimestamp) as buyTimestamp, max(listTimestamp) as listTimestamp from sales WHERE itemId=$itemId;";
	$result = $db->query($query);
	if ($result === false) print("\tError: Loading sale data for item $itemId!\n");
	
	$row = $result->fetch_assoc();
	$maxListTime = $row['listTimestamp'];
	$maxBuyTime = $row['buyTimestamp'];
	
	if ($maxListTime == null) $maxListTime = 0;
	if ($maxBuyTime  == null) $maxBuyTime  = 0;
	
	$query = "UPDATE items SET lastPurchaseTimestamp=$maxBuyTime, lastSaleTimestamp=$maxListTime WHERE id=$itemId;";
	$result = $db->query($query);
	if ($result === false) print("\tError: Updating item $itemId!\n");
}