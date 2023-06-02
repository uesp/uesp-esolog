<?php
if (php_sapi_name() != "cli") die("Can only be run from command line!");

require("/home/uesp/secrets/esolog.secrets");

$renameType = "";
$version = "";
$destVersion = "";
$archiveVersion = 0;
$DRYRUN = false;

for ($i = 1; $i < $argc; ++$i) {
	$cmd = strtolower($argv[$i]);
	
	if ($renameType == "")
	{
		if ($cmd == "item" || $cmd == "items") {
			$renameType = "item";
		}
		else if ($cmd == "skill" || $cmd == "skills") {
			$renameType = "skill";
		}
		else {
			die("Unknown rename type '$cmd' found!");
		}
	}
	else if ($version == "") {
		$version = $cmd;
		if (!preg_match('/^[0-9]+(?:pts[0-9]?)?$/', $version)) die("Version '$cmd' doesn't match expected format of a table version suffix!");
	}
	else if ($destVersion == "")
	{
		$destVersion = $cmd;
		if (!preg_match('/^[0-9]+(?:pts[0-9]?)?$/', $destVersion)) die("Version '$cmd' doesn't match expected format of a table version suffix!");
	}
	else
	{
		die("Unknown command line parameter '$cmd' found!");
	}
	
}

$archiveVersion = intval($version) - 1;
if ($destVersion != "") $archiveVersion = $destVersion;

if ($renameType == "" || $version == "") die("Missing rename type or version on the command line: [item|skill] [sourceversion] [destversion]");
if ($destVersion == "" && $archiveVersion <= 0) die("Version is not a valid number!]");

$db = new mysqli($uespEsoLogWriteDBHost, $uespEsoLogWriteUser, $uespEsoLogWritePW, $uespEsoLogDatabase);
if ($db->connect_error) die("Could not connect to mysql database!");


function renameItems() {
	global $db;
	global $version;
	global $archiveVersion;
	global $DRYRUN;
	
	$tables = array("minedItem", "minedItemSummary", "setSummary");
	
	foreach ($tables as $i => $table)
	{
		$result = $db->query("SELECT count(*) FROM $table$archiveVersion;");
		if (!$result || $result->num_rows == 0) continue;
		
		$rowCount = $result->fetch_row()[0];
		if ($rowCount > 0) die("Error: Archive table $table$archiveVersion already exists and has $rowCount rows!");
		
		print("\tDeleting empty archive table $table$archiveVersion...\n");
		
		if ($DRYRUN) continue;
		
		$result = $db->query("DROP TABLE $table$archiveVersion;");
	}
	
	foreach ($tables as $i => $table)
	{
		print("\tMoving table $table to $table$archiveVersion...\n");
		print("\tMoving table $table$version to $table...\n");
		
		if ($DRYRUN) continue;
		
		$query = "RENAME TABLE $table TO $table$archiveVersion;";
		$result = $db->query($query);
		
		if (!$result) 
		{
			print("\tERROR: Failed to move table $table!\n$query\n{$db->error}\n");
			continue;
		}
		
		$query = "RENAME TABLE $table$version TO $table;";
		$result = $db->query($query);
		
		if (!$result) 
		{
			print("\tERROR: Failed to move table $table!\n$query\n{$db->error}\n");
			$result = $db->query("RENAME TABLE $table$archiveVersion TO $table;");
			continue;
		}
	}
	
}


function renameSkills() {
	global $db;
	global $version;
	global $archiveVersion;
	global $DRYRUN;
	
	//$tables = array("cpSkills", "cpSkillDescriptions", "cpDisciplines", "minedSkills", "skillTree", "minedSkillLines");	// Pre-update 29 skill tables
	$tables = array("cp2Skills", "cp2SkillDescriptions", "cp2Disciplines", "cp2ClusterRoots", "cp2SkillLinks", "minedSkills", "skillTree", "minedSkillLines", "skillTooltips");
	
	foreach ($tables as $i => $table)
	{
		$result = $db->query("SELECT count(*) FROM $table$archiveVersion;");
		if (!$result || $result->num_rows == 0) continue;
		
		$rowCount = $result->fetch_row()[0];
		if ($rowCount > 0) die("Error: Archive table $table$archiveVersion already exists and has $rowCount rows!");
		
		print("\tDeleting empty archive table $table$archiveVersion...\n");
		
		if ($DRYRUN) continue;
		
		$result = $db->query("DROP TABLE $table$archiveVersion;");
	}
	
	foreach ($tables as $i => $table)
	{
		print("\tMoving table $table to $table$archiveVersion...\n");
		print("\tMoving table $table$version to $table...\n");
		
		if ($DRYRUN) continue;
		
		$query = "RENAME TABLE $table TO $table$archiveVersion;";
		$result = $db->query($query);
		
		if (!$result) 
		{
			print("\tERROR: Failed to move table $table!\n$query\n{$db->error}\n");
			continue;
		}
		
		$query = "RENAME TABLE $table$version TO $table;";
		$result = $db->query($query);
		
		if (!$result) 
		{
			print("\tERROR: Failed to move table $table!\n$query\n{$db->error}\n");
			$result = $db->query("RENAME TABLE $table$archiveVersion TO $table;");
			continue;
		}
	}
	
}


function renameItemsDest($destVersion) {
	global $db;
	global $version;
	global $archiveVersion;
	global $DRYRUN;
	
	$tables = array("minedItem", "minedItemSummary", "setSummary");
	
	foreach ($tables as $i => $table)
	{
		$result = $db->query("SELECT count(*) FROM $table$destVersion;");
		if (!$result || $result->num_rows == 0) continue;
		
		$rowCount = $result->fetch_row()[0];
		if ($rowCount > 0) die("Error: Archive table $table$destVersion already exists and has $rowCount rows!");
		
		print("\tDeleting empty archive table $table$destVersion...\n");
		
		if ($DRYRUN) continue;
		
		$result = $db->query("DROP TABLE $table$destVersion;");
	}
	
	foreach ($tables as $i => $table)
	{
		print("\tMoving table $table$version to $table$destVersion...\n");
		
		if ($DRYRUN) continue;
		
		$query = "RENAME TABLE $table$version TO $table$destVersion;";
		$result = $db->query($query);
		
		if (!$result) 
		{
			print("\tERROR: Failed to move table $table$version!\n$query\n{$db->error}\n");
			continue;
		}
	}
	
}


function renameSkillsDest($destVersion) {
	global $db;
	global $version;
	global $archiveVersion;
	global $DRYRUN;
	
	//$tables = array("cpSkills", "cpSkillDescriptions", "cpDisciplines", "minedSkills", "skillTree", "minedSkillLines");	// Pre-update 29 skill tables
	$tables = array("cp2Skills", "cp2SkillDescriptions", "cp2Disciplines", "cp2ClusterRoots", "cp2SkillLinks", "minedSkills", "skillTree", "minedSkillLines", "skillTooltips");
	
	foreach ($tables as $i => $table)
	{
		$result = $db->query("SELECT count(*) FROM $table$destVersion;");
		if (!$result || $result->num_rows == 0) continue;
		
		$rowCount = $result->fetch_row()[0];
		if ($rowCount > 0) die("Error: Archive table $table$destVersion already exists and has $rowCount rows!");
		
		print("\tDeleting empty archive table $table$destVersion...\n");
		
		if ($DRYRUN) continue;
		
		$result = $db->query("DROP TABLE $table$destVersion;");
	}
	
	foreach ($tables as $i => $table)
	{
		print("\tMoving table $table$version to $table$destVersion...\n");
		
		if ($DRYRUN) continue;
		
		$query = "RENAME TABLE $table$version TO $table$destVersion;";
		$result = $db->query($query);
		
		if (!$result) 
		{
			print("\tERROR: Failed to move table $table$version!\n$query\n{$db->error}\n");
			continue;
		}
	}
	
}

if ($renameType == "item") {
	if ($destVersion)
		renameItemsDest($destVersion);
	else
		renameItems();
}
else if ($renameType == "skill") {
	if ($destVersion)
		renameSkillsDest($destVersion);
	else
		renameSkills();
}
	