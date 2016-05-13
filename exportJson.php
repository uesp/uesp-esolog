<?php

$TABLES_TO_EXPORT = array(
	"cpDisciplines",
	"cpSkills",
	"cpSkillDescriptions",
);

if (php_sapi_name() != "cli") die("Can only be run from command line!");

require("/home/uesp/secrets/esolog.secrets");

$db = new mysqli($uespEsoLogReadDBHost, $uespEsoLogReadUser, $uespEsoLogReadPW, $uespEsoLogDatabase);
if ($db->connect_error) exit("Could not connect to mysql database!");

$records = array();

foreach ($TABLES_TO_EXPORT as $table)
{
	$records[$table] = array();
	$query = "SELECT * FROM $table;";
	$result = $db->query($query);
	if (!$result) print("Database query error!");
	
	while (($row = $result->fetch_assoc()))
	{
		$id = $row["id"];
		$records[$table][$id] = $row;
	}
}


$jsonData = json_encode($records);
print($jsonData);