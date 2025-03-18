<?php

if (php_sapi_name() != "cli") die("Error: Can only be run from command line!");

require("/home/uesp/secrets/esolog.secrets");
require_once("esoCommon.php");

$TABLE_SUFFIX = "45";

print("Finding all crafted skill abilityIds and updating the craftedSkill$TABLE_SUFFIX table...\n");

$db = new mysqli($uespEsoLogWriteDBHost, $uespEsoLogWriteUser, $uespEsoLogWritePW, $uespEsoLogDatabase);
if ($db->connect_error) exit("Error: Could not connect to mysql database!");

$result = $db->query("SELECT id, craftedId FROM minedSkills$TABLE_SUFFIX where craftedId>0;");
if (!$result) die("Error: Failed to query minedSkills$TABLE_SUFFIX table!");

$count = $result->num_rows;
print("\tFound $count abilities with a craftedId set!\n");

$craftedData = [];

while ($row = $result->fetch_assoc())
{
	$id = $row['id'];
	$craftedId = $row['craftedId'];
	$craftedData[$craftedId][] = $id;
}

$count = count($craftedData);
print("\tFound $count unique crafted skills!\n");

foreach ($craftedData as $craftedId => $ids)
{
	$text = implode(",", $ids);
	$safeIds = $db->real_escape_string($text);
	$query = "UPDATE craftedSkills$TABLE_SUFFIX SET abilityIds='$safeIds' WHERE id='$craftedId';";
	$result = $db->query($query);
	if (!$result) print("Error: Failed to update craftedSkills$TABLE_SUFFIX table for crafted skill $craftedId! ($query)\n");
}

print("Done!\n");