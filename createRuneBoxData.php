<?php 
if (php_sapi_name() != "cli") die("Can only be run from command line!");

$TABLE_SUFFIX = "20";

$REPLACE_PAIRS = array(
		"Storm Lord" => "Stormlord", 
		"Fire Drake" => "Firedrake", 
		"Dwemer Theodolite Pet" => "Dwarven Theodolite",
		"Sixth House Robe Costume" => "Sixth House Robe",
		"Clockwork Reliquary" => "Clockwork Curator",
		"Soul-Shriven Skin" => "Soul-Shriven",
		"Big-Eared Ginger Kitten Pet" => "Big-Eared Ginger Kitten",
		"Psijic Glowglobe Emote" => "Psijic Glowglobe",
		"Arena Gladiator Costume" => "Arena Gladiator",	
);

require("/home/uesp/secrets/esolog.secrets");
require("esoCommon.php");

$db = new mysqli($uespEsoLogReadDBHost, $uespEsoLogReadUser, $uespEsoLogReadPW, $uespEsoLogDatabase);
if ($db->connect_error) exit("Could not connect to mysql database!");

print("Looking for all container data...\n");

$query = "SELECT * FROM minedItemSummary$TABLE_SUFFIX WHERE type=18;";
$result = $db->query($query);
if (!$result) exit("DB query error!");

while (($row = $result->fetch_assoc()))
{
	$name = $row['name'];
	$itemId = $row['itemId'];
	
	if (preg_match("/(Runebox|Style Page)\: (.*)/i", $name, $matches))
	{
		$suffix = $matches[2];
		//print ("\tFound $name ($suffix)!\n");
		
		$safeName = $db->real_escape_string($suffix);
		$query = "SELECT * FROM collectibles WHERE name='$safeName' LIMIT 1;";
		$result2 = $db->query($query);
		
		if ($result2->num_rows == 0)
		{
			$suffix = strtr($suffix, $REPLACE_PAIRS);
			$safeName = $db->real_escape_string($suffix);
			$query = "SELECT * FROM collectibles WHERE name='$safeName' LIMIT 1;";
			$result2 = $db->query($query);
		}
		
		if ($result2->num_rows == 0)
		{
			//print("\tWarning: No collectible matching '$suffix' found!\n");
			print("\t-- [$itemId] = ?, \t-- $suffix\n");
		}
		else
		{
			$row2 = $result2->fetch_assoc();
			$collectId = $row2['id'];
			print("\t[$itemId] = $collectId, \t-- $suffix\n");
		}
			
	}
}