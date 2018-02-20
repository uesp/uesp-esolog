<?php 

$TABLE_SUFFIX1 = "16";
$TABLE_SUFFIX2 = "";

if (php_sapi_name() != "cli") die("Can only be run from command line!");
print("Finding updated set descriptions...\n");

require("/home/uesp/secrets/esolog.secrets");

$db = new mysqli($uespEsoLogReadDBHost, $uespEsoLogReadUser, $uespEsoLogReadPW, $uespEsoLogDatabase);
if ($db->connect_error) exit("Could not connect to mysql database!");

$query = "SELECT * FROM setSummary$TABLE_SUFFIX1;";
$result = $db->query($query);

if ($result === false) exit("Failed to load set1 data!");

$sets1 = array();

while (($row = $result->fetch_assoc()))
{
	$id = $row['setName'];
	$sets1[$id] = $row;
}

$count1 = count($sets1);
print("\tLoading $count1 sets from setSummary$TABLE_SUFFIX1...\n");

$query = "SELECT * FROM setSummary$TABLE_SUFFIX2;";
$result = $db->query($query);

if ($result === false) exit("Failed to load set2 data!");

$sets2 = array();

while (($row = $result->fetch_assoc()))
{
	$id = $row['setName'];
	$sets2[$id] = $row;
}

$count2 = count($sets2);
print("\tLoading $count2 sets from setSummary$TABLE_SUFFIX2...\n");

$newCount = 0;
$modifiedCount = 0;
$output = array();

foreach ($sets2 as $name => $set2)
{
	$set1 = $sets1[$name];

	if ($set1 == null)
	{
		$newCount++;
		$output[] = "\t$name: New set";
		//$output[] = "\t\t" . $set2['setBonusDesc'] . "";
		continue;
	}

	$desc1 = preg_replace("#[0-9]+#", "", $set1['setBonusDesc']);
	$desc2 = preg_replace("#[0-9]+#", "", $set2['setBonusDesc']);

	if ($desc1 != $desc2)
	{
		$modifiedCount++;
		$output[] = "\t$name: Modified set description";
		//$output[] = "\t\t" . $set2['setBonusDesc'] . "";
	}
}

sort($output);

print(implode($output, "\n"));
print("\n");

print("Found $newCount new sets.\n");
print("Found $modifiedCount modified sets.\n");