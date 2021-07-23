<?php 
if (php_sapi_name() != "cli") die("Can only be run from command line!");

$TABLE_SUFFIX = "31pts";

$REPLACE_PAIRS = array(
		"Storm Lord" => "Stormlord", 
		"Fire Drake" => "Firedrake", 
		"Dwemer Theodolite Pet" => "Dwarven Theodolite",
		"Dwarven Theodolite Pet" => "Dwarven Theodolite",
		"Sixth House Robe Costume" => "Sixth House Robe",
		"Clockwork Reliquary" => "Clockwork Curator",
		"Soul-Shriven Skin" => "Soul-Shriven",
		"Big-Eared Ginger Kitten Pet" => "Big-Eared Ginger Kitten",
		"Psijic Glowglobe Emote" => "Psijic Glowglobe",
		"Arena Gladiator Costume" => "Arena Gladiator",
		"Arena Gladiator Emote" => "Gladiator Taunt",
		"Blood Spawn's Shoulder" => "Bloodspawn's Shoulder",
		"Blood Spawn's Mask" => "Bloodspawn's Mask",
		"Sellistrix' Shoulder" => "Sellistrix's Shoulder",
		"Sellistrix' Mask" => "Sellistrix Mask",
		"Engine Guardian Shoulder" => "Engine Guardian's Shoulder",
		"Engine Guardian Mask" => "Engine Guardian's Mask",
		"Abner Tharn's Jerkin" => "Abnur Tharn's Jerkin",
		"Abner Tharn's Hat" => "Abnur Tharn's Hat",
		"Abner Tharn's Breeches" => "Abnur Tharn's Breeches",
		"Abner Tharn's Epaulets" => "Abnur Tharn's Epaulets",
		"Abner Tharn's Shoes" => "Abnur Tharn's Shoes",
		"Abner Tharn's Gloves" => "Abnur Tharn's Gloves",
		"Abner Tharn's Sash" => "Abnur Tharn's Sash",
		"Abner Tharn's Dagger" => "Abnur Tharn's Dagger",
		"Abner Tharn's Staff" => "Abnur Tharn's Staff",
		"Guar Stomp Emote" => "Guar Stomp",
		"Grothdar " => "Grothdarr ",
		"Mummified Alfiq Pet" => "Grisly Mummy Tabby",
		"Banner Emote" => "Banner",
		"Siegemaster's" => "Siegemaster",
		"Opal Ilambris' " => "Opal Ilambris ",
		"Snowhawk Mage " => "Snowhawk Mage's ",
		"Balorgh's " => "Balorgh ",
		"Iceheart's Mask" => "Iceheart Mask",
		"Troll King's Mask" => "Troll King Mask",
		"Bloodspawn's Mask" => "Bloodspawn Mask",
		"Sellistrix's Mask" => "Sellistrix Mask",
		"Swarm Mother's Mask" => "Swarm Mother Mask",
		"Valkyn Skoria's Mask" => "Valkyn Skoria Mask",
		"Nightflame's Mask" => "Nightflame Mask",
		"Lord Warden's Mask" => "Lord Warden Mask",
		"Shadowrend's Mask" => "Shadowrend Mask",
		"Ilambris' Mask" => "Ilambris Mask",
		"Molag Kena's Mask" => "Molag Kena Mask",
		"Grothdar's Mask" => "Grothdarr Mask",
		"Grothdar's Shoulder" => "Grothdarr's Shoulder",
		"Grothdarr's Mask" => "Grothdarr Mask",
		"Mighty Chudan's Mask" => "Mighty Chudan Mask",
		"Velidreth's Mask" => "Velidreth Mask",
		"Pirate Skeleton's Mask" => "Pirate Skeleton Mask",
		"Chokethorn's Mask" => "Chokethorn Mask",
		"Spawn of Mephala's Mask" => "Spawn of Mephala Mask",
		"Slimecraw's Mask" => "Slimecraw Mask",
		"Maw of the Infernal's Mask" => "Maw of the Infernal Mask",
		"Nerien'eth's Mask" => "Nerien'eth Mask",
		" Chitinous Jack" => "Chitinous Jack",
		"Earthgore's Mask" => "Earthgore Mask",
		"Tremorscale's Mask" => "Tremorscale Mask",
		"Timbercrow Wanderer Costume" => "Timbercrow Wanderer",
		"Doctrine Ordinator Cuirass" => "Doctrine Ordinator Jack",
		"Doctrine Ordinator Greaves" => "Doctrine Ordinator Guards",
		"Opal Troll King's Mask" => "Opal Troll King Mask",
		"Opal Bloodspawn's Mask" => "Opal Bloodspawn Mask",
		"Opal Nightflame's Mask" => "Opal Nightflame Mask",
		"Opal Iceheart's Mask" => "Opal Iceheart Mask",
		"Opal Iceheart Shoulder" => "Opal Iceheart's Shoulder",
		"Opal Lord Warden's Mask" => "Opal Lord Warden Mask",
		"Opal Nightflame's Mask" => "Opal Nightflame Mask",
		"Opal Nightflame Shoulder" => "Opal Nightflame's Shoulder",
		"Opal Swarm Mother's Mask" => "Opal Swarm Mother Mask",
		"Opal Swarm Mother Shoulder" => "Opal Swarm Mother's Shoulder",
		"Aldmeri Breton Terrier Pet" => "Dominion Breton Terrier",
		"Daggerfall Breton Terrier Pet" => "Covenant Breton Terrier", 
		"Ebonheart Breton Terrier Pet" => "Pact Breton Terrier",
		"Rage of the Reach Emote" => "Rage of the Reach",
		"Nibenese Court Wizard Robe" => "Nibenese Court Wizard Jerkin",
);

require("/home/uesp/secrets/esolog.secrets");
require("esoCommon.php");

$db = new mysqli($uespEsoLogReadDBHost, $uespEsoLogReadUser, $uespEsoLogReadPW, $uespEsoLogDatabase);
if ($db->connect_error) exit("Could not connect to mysql database!");

print("Looking for all container data...\n");

$query = "SELECT * FROM minedItemSummary$TABLE_SUFFIX WHERE type=18 or type=34;";
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
			$oldSuffix = $suffix;
			$suffix = strtr($suffix, $REPLACE_PAIRS);
			//print("\tNew Suffix: '$oldSuffix' => '$suffix'\n");
			
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