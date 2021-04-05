<?php
if (php_sapi_name() != "cli") die("Can only be run from command line!");

require("/home/uesp/secrets/esolog.secrets");

$renameType = "";
$version = "";
$archiveVersion = 0;

for ($i = 1; $i < $argc; ++$i) {
	$cmd = strtolower($argv[$i]);
	
	if ($cmd == "item" || $cmd == "items") {
		$renameType = "item";
	}
	else if ($cmd == "skill" || $cmd == "skills") {
		$renameType = "skill";
	}
	else {
		$version = $cmd;
	}
}

$archiveVersion = intval ($version) - 1;

if ($renameType == "" || $version == "") die("Missing rename type or version on the command line: [item|skill] [version]");
if ($archiveVersion <= 0) die("Version is not a valid number!]");

$db = new mysqli($uespEsoLogWriteDBHost, $uespEsoLogWriteUser, $uespEsoLogWritePW, $uespEsoLogDatabase);
if ($db->connect_error) die("Could not connect to mysql database!");


function renameItems() {
	global $db;
	global $version;
	global $archiveVersion;
	
	$tables = array("minedItem", "minedItemSummary", "setSummary");
	
	foreach ($tables as $i => $table)
	{
		$result = $db->query("SELECT count(*) FROM $table$archiveVersion;");
		if (!$result || $result->num_rows == 0) continue;
		
		$rowCount = $result->fetch_row()[0];
		if ($rowCount > 0) die("Error: Archive table $table already exists and has $rowCount rows!");
		
		print("\tDeleting empty archive table $table$archiveVersion...\n");
		$result = $db->query("DROP TABLE $table$archiveVersion;");
	}
	
	foreach ($tables as $i => $table)
	{
		print("\tMoving table $table...\n");
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
	
	//$tables = array("cpSkills", "cpSkillDescriptions", "cpDisciplines", "minedSkills", "skillTree", "minedSkillLines");
	$tables = array("cp2Skills", "cp2SkillDescriptions", "cp2Disciplines", "cp2ClusterRoots", "cp2Skills", "minedSkills", "skillTree", "minedSkillLines", "skillTooltips");
	
	foreach ($tables as $i => $table)
	{
		$result = $db->query("SELECT count(*) FROM $table$archiveVersion;");
		if (!$result || $result->num_rows == 0) continue;
		
		$rowCount = $result->fetch_row()[0];
		if ($rowCount > 0) die("Error: Archive table $table already exists and has $rowCount rows!");
		
		print("\tDeleting empty archive table $table$archiveVersion...\n");
		$result = $db->query("DROP TABLE $table$archiveVersion;");
	}
	
	foreach ($tables as $i => $table)
	{
		print("\tMoving table $table...\n");
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

if ($renameType == "item")  renameItems();
if ($renameType == "skill") renameSkills();
	