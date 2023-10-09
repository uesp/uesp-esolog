<?php 

if (php_sapi_name() != "cli") die("Can only be run from command line!");

$NUMTESTS = 100;
$MAXITEMID = 1800000;
$TEST_DB = "newdb1";
srand(552);

print("Quick ESO Sales Database benchmark for $TEST_DB...\n");

require_once("/home/uesp/secrets/esosalesdata.secrets");
require_once("esoCommon.php");

if ($TEST_DB == "db1")
{
	$db = new mysqli($uespEsoSalesDataWriteDBHost, $uespEsoSalesDataWriteUser, $uespEsoSalesDataWritePW, $uespEsoSalesDataDatabase);
	if ($db->connect_error) die("Could not connect to mysql database $TEST_DB!");
}
else if ($TEST_DB == "newdb1")
{
	$db = new mysqli("10.12.222.33", $uespEsoSalesDataWriteUser, $uespEsoSalesDataWritePW, $uespEsoSalesDataDatabase);
	if ($db->connect_error) die("Could not connect to mysql database $TEST_DB!");
}
else
{
	$db = new mysqli($uespEsoSalesDataReadDBHost, $uespEsoSalesDataReadUser, $uespEsoSalesDataReadPW, $uespEsoSalesDataDatabase);
	if ($db->connect_error) die("Could not connect to mysql database $TEST_DB!");
}

$totalCount = 0;
$startTime = microtime(true);
$totalQueryTime = 0;
$totalFetchTime = 0;

for ($i = 0; $i < $NUMTESTS; $i++)
{
	$itemId = rand(1, 1800000);
	print("\t$i) Loading item $itemId...\n");
	
	$startQueryTime = microtime(true);
	
	$result = $db->query("SELECT * FROM sales WHERE itemId=$itemId;");
	
	$startFetchTime = microtime(true);
	$totalQueryTime += $startFetchTime - $startQueryTime;
	
	$items = [];
	
	while ($row = $result->fetch_assoc())
	{
		$items[] = $row;
	}
	
	$totalFetchTime += microtime(true) - $startFetchTime;
	$totalCount += count($items);
}


$totalTime = microtime(true) - $startTime;
$avgTime = $totalTime / $NUMTESTS;

print("Ran $NUMTESTS tests loading random ESO sales items.\n");
print("\tTotal Time = $totalTime secs\n");
print("\tAverage Time = $avgTime secs\n");
print("\tTotal Query Time = $totalQueryTime secs\n");
print("\tTotal Fetch Time = $totalFetchTime secs\n");