<?php

$TABLE_SUFFIX = "16";

$ESO_SLOTTED_SKILLS = array(
		35803 => -58,	//FG: Slayer
		45595 => -58,
		45596 => -58,
		
		29460 => -59,	//DK: Elder Dragon
		44951 => -59,
		
		36636 => -57,	//NB: Pressure Points
		45053 => -57,
		
		36532 => -60,	//NB: Dark Vigor
		45084 => -60,
		
		36603 => -61,	//NB: Soul Siphoner
		45155 => -61,
		
		31425 => -62,	//MG: Expert Mage
		45195 => -62,
		
		40438 => -63,	//MG: Magicka Controller
		45603 => -63,
		
		39255 => -64,	//Support: Magicka Aid
		45622 => -64,
		
		30893 => -54,	//DW: Twin Blade and Blunt
		45482 => -54,
		
		86068 => -65,	//Warden: Advanced Species
		86069 => -65,
		
		85876 => -66,	//Warden: Emerald Moss
		85877 => -66,
		
		86189 => -67,	//Warden: Frozen Armor
		86190 => -67,
);

$ESO_COEF_INDEX = array(
		30893 => 2,		//DW: Twin Blade and Blunt
		45482 => 2,
);

$ESO_COEF_VALUE = array(
		30893 => 547,		//DW: Twin Blade and Blunt
		45482 => 1095,
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
	
	$index = 1;
	if ($ESO_COEF_INDEX[$skillId] != null) $index = $ESO_COEF_INDEX[$skillId];
	
	$skillsData[$skillId] = $result->fetch_assoc();
	$skillsData[$skillId]['type' + $index] = $powerType;
}


foreach ($skillsData as $skillId => &$skill)
{
	$index = 1;
	if ($ESO_COEF_INDEX[$skillId] != null) $index = $ESO_COEF_INDEX[$skillId];
	
	$result = preg_match("#([0-9]+)#", $skill['description'], $matches);
	
	if (!$result) 
	{
		print("\t{$skill['name']} ($skillId): Description does not match expected format!\n");
		continue;	
	}
	
	$value = $matches[1];
	
	if ($ESO_COEF_VALUE[$skillId] != null) $value = $ESO_COEF_VALUE[$skillId];
	print("\t{$skill['name']} ($skillId): Found value $value\n");
	
	$skill['a' . $index] = $value;
	$skill['b' . $index] = 0;
	$skill['c' . $index] = 0;
	$skill['avg' . $index] = 0;
	$skill['R' . $index] = 1;
	$skill['numCoefVars'] = $index;
	$skill['coefDescription'] = preg_replace("#Current bonus: \|cffffff[0-9]+\|r#i", 'Current bonus: |cffffff\$'. $index . '|r', $skill['description']);
	$type = $skill['type' + $index];
	
	print("\tCoef Description: {$skill['coefDescription']}\n");
	
	$coef = $db->real_escape_string($skill['coefDescription']);
	
	$query = "UPDATE minedSkills$TABLE_SUFFIX SET type$index=$type, a$index={$skill['a'.$index]}, b$index=0, c$index=0, avg$index=0, R$index=1, numCoefVars=$index, coefDescription='$coef' WHERE id=$skillId;";
	$result = $db->query($query);
	if (!$result) print("\tError saving skill $skillId!\n");
}



