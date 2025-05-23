<?php 

$TABLE_SUFFIX = "46pts";

if (php_sapi_name() != "cli") die("Can only be run from command line!");

require("/home/uesp/secrets/esolog.secrets");
require("esoCommon.php");

print("Updating the gameIds for all sets for version $TABLE_SUFFIX...\n");

$db = new mysqli($uespEsoLogWriteDBHost, $uespEsoLogWriteUser, $uespEsoLogWritePW, $uespEsoLogDatabase);
if ($db->connect_error) exit("Could not connect to mysql database!");

$query = "SELECT * FROM setSummary$TABLE_SUFFIX;";
$result = $db->query($query);
if ($result === false) exit("Failed to load set summaries!");

$sets = array();

while ($row = $result->fetch_assoc())
{
	$sets[] = $row;
}

$ESO_SETINDEX_MAP = array();

foreach ($ESO_SET_INDEXES as $setIndex => $setName)
{
	$setName = strtolower($setName);
	if ($ESO_SETINDEX_MAP[$setName] != null) print ("\tWarning: Duplicate set index $setIndex for '$setName'!\n");
	$ESO_SETINDEX_MAP[$setName] = $setIndex;
}

$setsFound = count($sets);
$setsUpdated = 0;

foreach ($sets as $set)
{
	$id = $set['id'];
	$name = $set['setName'];
	$gameIndex = $ESO_SETINDEX_MAP[strtolower($name)];
	
	if ($gameIndex == null)
	{
		print("\tError: Missing gameId for set '$name'!\n");
		continue;
	}
	
	$safeName = $db->real_escape_string($name);
	$query = "UPDATE setSummary$TABLE_SUFFIX SET gameId='$gameIndex' WHERE id='$id';";
	$result = $db->query($query);
	if ($result === false) print("\tError: Failed to update set $name!\n");
	
	++$setsUpdated;
}


print("Found $setsFound sets and updated $setsUpdated game IDs!\n");