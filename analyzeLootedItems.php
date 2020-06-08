<?php
if (php_sapi_name() != "cli") die("Can only be run from command line!");

require("/home/uesp/secrets/esolog.secrets");

$db = new mysqli($uespEsoLogReadDBHost, $uespEsoLogReadUser, $uespEsoLogReadPW, $uespEsoLogDatabase);
if ($db->connect_error) exit("Could not connect to mysql database!");

print("Analyzing stats of looted items...\n");

$result = $db->query("select * from item WHERE link!='';");
if (!$result) die("Failed to run query!");

$itemCounts = array();
$subtypeCounts = array();
$levelCounts = array();
$numItems = 0;

while ($item = $result->fetch_assoc()) {
	$itemLink = $item['link'];
	
	$matchResult = preg_match("#\|H([A-Fa-f0-9]+):item:([0-9]+):([0-9]+):([0-9]+):#", $itemLink, $matches);
	
	if (!$matchResult) continue;
	if ($matches[1] == "FFFFFF" || $matches[1] == "ffffff") continue;
	
	$itemSubtype = $matches[3];
	$itemLevel = $matches[4];
	
	$key = $itemSubtype . ":" . $itemLevel;
	
	if ($itemCounts[$key] == null) $itemCounts[$key] = 0;
	$itemCounts[$key]++;
	
	if ($subtypeCounts[$itemSubtype] == null) $subtypeCounts[$itemSubtype] = 0;
	$subtypeCounts[$itemSubtype]++;
	
	if ($levelCounts[$itemLevel] == null) $levelCounts[$itemLevel] = 0;
	$levelCounts[$itemLevel]++;
	
	++$numItems;
}

$count = count($itemCounts);
$count1 = count($subtypeCounts);
$count2 = count($levelCounts);
print("Loaded $numItems with $count unique subtype:level combinations, $count1 unique subtypes, and $count2 unique levels!\n");

ksort($itemCounts);
ksort($subtypeCounts);
ksort($levelCounts);

foreach ($itemCounts as $key => $count) {
	print("\t$key = $count\n");
}

print("Unique subtypes:\n");

foreach ($subtypeCounts as $key => $count) {
	print("\t$key = $count\n");
}

print("Unique levels:\n");

foreach ($levelCounts as $key => $count) {
	print("\t$key = $count\n");
}
