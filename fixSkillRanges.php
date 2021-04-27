<?php


$SOURCE_SUFFIX = "";
$TARGET_SUFFIX = "30pts";

if (php_sapi_name() != "cli") die("Can only be run from command line!");

require("/home/uesp/secrets/esolog.secrets");

print("Copying skill range values from '$SOURCE_SUFFIX' to '$TARGET_SUFFIX'...\n");

$db = new mysqli($uespEsoLogWriteDBHost, $uespEsoLogWriteUser, $uespEsoLogWritePW, $uespEsoLogDatabase);
if ($db->connect_error) exit("Could not connect to mysql database!");

$query = "SELECT id, name, minRange, maxRange FROM minedSkills$SOURCE_SUFFIX;";
$result = $db->query($query);
if ($result === false) die("Failed to load skill records!\n");

$updateCount = 0;

while (($row = $result->fetch_assoc()))
{
	$id = $row['id'];
	
	$minRange = intval($row['minRange']);
	$maxRange = intval($row['maxRange']);
	if ($minRange == 0 && $maxRange == 0) continue;
	
	$writeResult = $db->query("UPDATE minedSkills$TARGET_SUFFIX SET minRange='$minRange', maxRange='$maxRange' WHERE id='$id';");
	if ($writeResult === false) print("Error: Failed to update ranges for skill $id!\n");
	
	++$updateCount;
}

print("Updated $updateCount skill records!\n");