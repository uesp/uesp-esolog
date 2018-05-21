<?php 

$TABLE_SUFFIX1 = "";
$TABLE_SUFFIX2 = "18pts";

if (php_sapi_name() != "cli") die("Can only be run from command line!");
print("Finding updated player skills...\n");

require("/home/uesp/secrets/esolog.secrets");

$db = new mysqli($uespEsoLogReadDBHost, $uespEsoLogReadUser, $uespEsoLogReadPW, $uespEsoLogDatabase);
if ($db->connect_error) exit("Could not connect to mysql database!");

$query = "SELECT * FROM minedSkills$TABLE_SUFFIX1 WHERE isPlayer=1;";
$result = $db->query($query);

if ($result === false) exit("Failed to load skills1 data!");

$skills1 = array();

while (($row = $result->fetch_assoc()))
{
	$id = $row['id'];
	$skills1[$id] = $row;
}

$count1 = count($skills1);
print("\tLoading $count1 player skills from minedSkills$TABLE_SUFFIX1...\n");

$query = "SELECT * FROM minedSkills$TABLE_SUFFIX2 WHERE isPlayer=1;";
$result = $db->query($query);

if ($result === false) exit("Failed to load skills2 data!");

$skills2 = array();

while (($row = $result->fetch_assoc()))
{
	$id = $row['id'];
	$skills2[$id] = $row;
}

$count2 = count($skills2);
print("\tLoading $count2 player skills from minedSkills$TABLE_SUFFIX2...\n");

$newCount = 0;
$modifiedCount = 0;
$output = array();

foreach ($skills2 as $id => $skill2)
{
	$name = $skill2['name'];
	$rank = $skill2['rank'];
	
	$skill1 = $skills1[$id];
	
	if ($skill1 == null)
	{
		$newCount++;
		$output[] = "\t$name $rank ($id): New skill";
		continue;
	}
	
	$desc1 = preg_replace("#[0-9]+#", "", $skill1['description']);
	$desc2 = preg_replace("#[0-9]+#", "", $skill2['description']);
	
	if ($desc1 != $desc2)
	{
		$modifiedCount++;
		$output[] = "\t$name $rank ($id): Modified skill";
	}
}

sort($output);

print(implode($output, "\n"));
print("\n");

print("Found $newCount new skills.\n");
print("Found $modifiedCount modified skills.\n");