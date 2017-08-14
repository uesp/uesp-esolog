<?php

$TABLE_SUFFIX = "15";

$ESO_SLOTTED_SKILLS = array(
		35803 => -58,
		45595 => -58,
		45596 => -58,
		
		29460 => -59,
		44951 => -59,
		
		36636 => -57,
		45053 => -57,
		
		36532 => -60,
		45084 => -60,
		
		36603 => -61,
		45155 => -61,
		
		31425 => -62,
		45195 => -62,
		
		40438 => -63,
		45603 => -63,
		
		39255 => -64,
		45622 => -64,	
);

if (php_sapi_name() != "cli") die("Can only be run from command line!");
print("Fixing slotted skills data...\n");

require("/home/uesp/secrets/esolog.secrets");

$db = new mysqli($uespEsoLogWriteDBHost, $uespEsoLogWriteUser, $uespEsoLogWritePW, $uespEsoLogDatabase);
if ($db->connect_error) exit("Could not connect to mysql database!");

$skillsData = array();

foreach ($ESO_SLOTTED_SKILLS as $skillId => $powerType)
{
	$query = "SELECT * FROM minedSkills$TABLE_SUFFIX WHERE id=$skillId;";
	$result = $db->query($query);
	
	if (!$result) 
	{
		print("Failed to load skill $skillId!\n");
		continue;
	}
	
	$skillsData[$skillId] = $result->fetch_assoc();
	$skillsData[$skillId]['type1'] = $powerType;
}


foreach ($skillsData as $skillId => &$skill)
{
	$result = preg_match("#([0-9]+)#", $skill['description'], $matches);
	
	if (!$result) 
	{
		print("\t{$skill['name']} ($skillId): Description does not match expected format!\n");
		continue;	
	}
	
	$value = $matches[1];
	print("\t{$skill['name']} ($skillId): Found value $value\n");
	
	$skill['a1'] = $value;
	$skill['b1'] = 0;
	$skill['c1'] = 0;
	$skill['avg1'] = 0;
	$skill['R1'] = 1;
	$skill['numCoefVars'] = 1;
	$skill['coefDescription'] = preg_replace("#Current bonus: \|cffffff[0-9]+\|r#i", 'Current bonus: |cffffff\$1|r', $skill['description']);
	
	print("\tCoef Description: {$skill['coefDescription']}\n");
	
	$coef = $db->real_escape_string($skill['coefDescription']);
	
	$query = "UPDATE minedSkills$TABLE_SUFFIX SET a1={$skill['a1']}, b1=0, c1=0, avg1=0, R1=1, numCoefVars=1, coefDescription='$coef' WHERE id=$skillId;";
	$result = $db->query($query);
	if (!$result) print("\tError saving skill $skillId!\n");
}



