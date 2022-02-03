<?php 

if (php_sapi_name() != "cli") die("Can only be run from command line!");

require("/home/uesp/secrets/esolog.secrets");

$db = new mysqli($uespEsoLogReadDBHost, $uespEsoLogReadUser, $uespEsoLogReadPW, $uespEsoLogDatabase);
if ($db->connect_error) exit("Could not connect to mysql database!");

$result = $db->query("SELECT * FROM setSummary ORDER BY setName;");
if (!$result) die("Failed to load sets!");

while (($set = $result->fetch_assoc()))
{
	$matchSetCount = 0;
	
	//print ($set['setBonusDesc4'] . "\n");
	
	for ($i = 1; $i <= 7; ++$i)
	{
		$match = preg_match("# ([A-Za-z]+) ([0-9\-]+) ((?:[a-zA-Z]+)|)( |)Damage#i", $set["setBonusDesc$i"], $matches);
		
		if ($match) 
		{
			if ($matches[1] == "absorbs") continue;
			if ($matches[3] == "Spell") continue;
			if ($matches[3] == "Weapon") continue;
			$matchSetCount = $i;
		}
	}

	if ($matchSetCount == 0) continue;
	
	$setName = strtolower($set['setName']);
	$desc = $set["setBonusDesc$matchSetCount"];
	$desc = explode(". ", $desc);
	$desc = implode("\n\t// ", $desc);
	$items = 0;
	
	$match = preg_match("#\(([0-9]+) items\)#", $desc, $matches);
	if ($match) $items = $matches[1];
			
	print("\"$setName\" : {\n");
	print("\t// $desc\n");
	print("\tisAoE : false,\n");
	print("\tisDoT : false,\n");
	print("\tindex : $matchSetCount,\n");
	print("\titems : $items,\n");
	
	print("},\n");
	
}
