<?php 

if (php_sapi_name() != "cli") die("Can only be run from command line!");

require("/home/uesp/secrets/esolog.secrets");

$db = new mysqli($uespEsoLogWriteDBHost, $uespEsoLogWriteUser, $uespEsoLogWritePW, $uespEsoLogDatabase);
if ($db->connect_error) exit("Could not connect to mysql database!");

$FIRST_VERSION = "7";
$SECOND_VERSION = "8pts";

$query = "SELECT * FROM itemIdCheck WHERE version='$FIRST_VERSION';";
$result = $db->query($query);
if (!$result) exit("ERROR: Database query error finding item version '$FIRST_VERSION'!\n" . $db->error);
$items1 = array();

while (($item = $result->fetch_assoc()))
{
	$items1[$item['itemId']] = $item;
}

$query = "SELECT * FROM itemIdCheck WHERE version='$SECOND_VERSION';";
$result = $db->query($query);
if (!$result) exit("ERROR: Database query error finding item version '$SECOND_VERSION'!\n" . $db->error);
$items2 = array();

while (($item = $result->fetch_assoc()))
{
	$items2[$item['itemId']] = $item;
}

$itemsNew = array();
$itemsRemoved = array();

foreach ($items1 as $itemId => $item)
{
	if (!array_key_exists($itemId, $items2)) $itemsRemoved[$itemId] = $item;
}

foreach ($items2 as $itemId => $item)
{
	if (!array_key_exists($itemId, $items1)) $itemsNew[$itemId] = $item;
}

ksort($itemsNew);
ksort($itemsRemoved);

$count1 = count($items1);
$count2 = count($items2);
$countNew = count($itemsNew);
$countRemoved = count($itemsRemoved);

$newItemsText = "";
$removedItemsText = "";
$lastId = -1;
$startId = -1;

foreach ($itemsNew as $itemId => $item)
{
	if ($itemId == $lastId + 1)
	{
		//Do nothing
	}
	elseif ($startId > 0)
	{
		if ($lastId > $startId)
			$newItemsText .= "$startId - $lastId\n";
		else
			$newItemsText .= "$startId\n";
		
		$startId = $itemId;
	}
	else
	{
		$startId = $itemId;
	}
	
	$lastId = $itemId;
}

if ($startId <= 0)
{}	// Do Nothing
elseif ($lastId > $startId)
	$newItemsText .= "$startId - $lastId\n";
else
	$newItemsText .= "$startId\n";

$lastId = -1;
$startId = -1;

foreach ($itemsRemoved as $itemId => $item)
{
		
	if ($itemId == $lastId + 1)
	{
		//Do nothing
	}
	elseif ($startId > 0)
	{
		if ($lastId > $startId)
			$removedItemsText .= "$startId - $lastId\n";
		else
			$removedItemsText .= "$startId\n";
		
		$startId = $itemId;
	}
	else
	{
		$startId = $itemId;
	}
	
	$lastId = $itemId;
}

if ($startId <= 0)
{}	// Do Nothing
elseif ($lastId > $startId)
	$removedItemsText .= "$startId - $lastId\n";
else
	$removedItemsText .= "$startId\n";

print("Found $count1 items with version '$FIRST_VERSION'.\n");
print("Found $count2 items with version '$SECOND_VERSION'.\n");
print("Found $countNew new items.\n");
print("Found $countRemoved removed items.\n");

file_put_contents("/tmp/newItems-$FIRST_VERSION-$SECOND_VERSION.txt", $newItemsText);
file_put_contents("/tmp/removedItems-$FIRST_VERSION-$SECOND_VERSION.txt", $removedItemsText);