<?php

$TABLE_SUFFIX = "19pts";

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
		
		39197 => -53,	//Heavy Armor: Unstoppable
		41097 => -53,
		41100 => -53,
		41103 => -53,
		
		39192 => -52,	//Medium Armor: Elude
		41133 => -52,
		41135 => -52,
		41137 => -52,
);

$ESO_COEF_INDEX = array(
		30893 => 2,		//DW: Twin Blade and Blunt
		45482 => 2,
);

$ESO_COEF_VALUE = array(
		30893 => 547,		//DW: Twin Blade and Blunt
		45482 => 1095,
		
		39197 => array(0.25, 0, 5),		//Heavy Armor: Unstoppable
		41097 => array(0.25, 0, 5),
		41100 => array(0.25, 0, 5),
		41103 => array(0.25, 0, 5),
		
		39192 => array(0.9, 0, 18),		//Medium Armor: Elude
		41133 => array(0.9, 0, 18),
		41135 => array(0.9, 0, 18),
		41137 => array(0.9, 0, 18),
);


	// 1-based counting (1 == first number in tooltip)
$ESO_COEF_NUMBER_INDEX = array(
		39197 => 4,		//Heavy Armor: Unstoppable
		41097 => 4,
		41100 => 4,
		41103 => 4,
		
		39192 => 3,		//Medium Armor: Elude
		41133 => 3,
		41135 => 3,
		41137 => 3,
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
	
	$coefDescription = $skill['coefDescription'];
	$fixupCoefDesc = false;
	
	if ($coefDescription == "")
	{
		$coefDescription = $skill['description'];
		$fixupCoefDesc = true;
	}
	
	$numberIndex = $ESO_COEF_NUMBER_INDEX[$skillId];
	
	if ($fixupCoefDesc || $numberIndex != null)
	{
		$coefDescription = $skill['description'];
		$foundCount = 0;
		
		$coefDescription = preg_replace_callback('|([0-9]+(?:\.\d+)?)|', function($matches) {
			global $foundCount, $numberIndex, $index;
			++$foundCount;
			if ($foundCount == $numberIndex) return '$' . $index;
			return $matches[0];					
		}, $coefDescription);
	}
	elseif ($fixupCoefDesc)
	{
		print("\t{$skill['name']} ($skillId): Skill has no coefficient description!\n");
	}
	
	$result = preg_match("#([0-9]+)#", $coefDescription, $matches);
	
	if (!$result) 
	{
		print("\t{$skill['name']} ($skillId): Description does not match expected format!\n");
		continue;	
	}
	
	$valueA = $matches[1];
	$valueB = 0;
	$valueC = 0;
	$coefValue = $ESO_COEF_VALUE[$skillId];
	
	if (is_numeric($coefValue)) 
	{
		$valueA = $coefValue;
	}
	elseif (is_array($coefValue)) 
	{
		$valueA = $coefValue[0];
		$valueB = $coefValue[1];
		$valueC = $coefValue[2];
	}
	
	print("\t{$skill['name']} ($skillId): Found value $valueA:$valueB:$valueC\n");
	
	$skill['a' . $index] = $valueA;
	$skill['b' . $index] = $valueB;
	$skill['c' . $index] = $valueC;
	$skill['avg' . $index] = 0;
	$skill['R' . $index] = 1;
	$skill['numCoefVars'] = $index;
	$skill['coefDescription'] = preg_replace("#Current bonus: \|cffffff[0-9]+\|r#i", 'Current bonus: |cffffff\$'. $index . '|r', $coefDescription);
	$type = $skill['type' + $index];
	
	print("\tCoef Description: {$skill['coefDescription']}\n");
	
	$coef = $db->real_escape_string($skill['coefDescription']);
	
	$query = "UPDATE minedSkills$TABLE_SUFFIX SET type$index=$type, a$index={$skill['a'.$index]}, b$index={$skill['b'.$index]}, c$index={$skill['c'.$index]}, avg$index=0, R$index=1, numCoefVars=$index, coefDescription='$coef' WHERE id=$skillId;";
	$result = $db->query($query);
	if (!$result) print("\tError saving skill $skillId!\n");
}



