<?php

$TABLE_SUFFIX = "42pts";

require_once("esoCommon.php");

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
		
		29791 => -53,	//Heavy Armor: Rapid Mending
		45529 => -53,
		
		39197 => -53,	//Heavy Armor: Immovable
		41097 => -53,
		41100 => -53,
		41103 => -53,
		
		39197 => -53,	//Heavy Armor: Resolve
		41097 => -53,
		41100 => -53,
		
		39192 => -52,	//Medium Armor: Elude
		41133 => -52,
		41135 => -52,
		41137 => -52,
		/*
		28418 => -68,	//Sorcerer: Conjured Ward
		30457 => -68,
		30460 => -68,
		30463 => -68,
		
		29489 => -68,	//Sorcerer: Hardened Ward
		30466 => -68,
		30470 => -68,
		30474 => -68,
		
		29482 => -68,	//Sorcerer: Empowered Ward
		30478 => -68,
		30482 => -68,
		30486 => -68,
		
		29338 => -68,	//Light Armor: Annulment
		41106 => -68,
		41107 => -68,
		41108 => -68,
		
		39186 => -68,	//Light Armor: Dampen Magic
		41109 => -68,
		41111 => -68,
		41113 => -68,
				
		39182 => -68,	//Light Armor: Harness Magicka
		41115 => -68,
		41118 => -68,
		41121 => -68, */
		
		116269 => -69,	//Necromancer: Health Avarice
		116270 => -69,
		
		116197 => -70,	//Necromancer: Death Knell
		116198 => -70,
		
		18866 => -53,	//Nightblade: Shadow Barrier
		45071 => -53,
		
		29665 => -51,	//Light Armor: Evocation
		45557 => -51,
		
		29639 => -51,	//Light Armor: Grace
		45548 => -51,
		45549 => -51,
		
		29742 => -52,	//Medium Armor: Athletics
		45574 => -52,
		
		20930 => -73,
		34042 => -73,
		34045 => -73,
		34048 => -73,
);

$ESO_COEF_INDEX = array(
		30893 => 2,		//DW: Twin Blade and Blunt
		45482 => 2,
		
		18866 => array(1, 2),		//Nightblade: Shadow Barrier
		45071 => array(1, 2),
		
		39182 => 1,		//Light Armor: Harness Magicka
		41115 => 1,
		41118 => 1,
		41121 => 1,
		
		29665 => array(1, 2),	//Light Armor: Evocation
		45557 => array(1, 2),
		
		29639 => array(1, 2),	//Light Armor: Grace
		45548 => array(1, 2),
		45549 => array(1, 2),
		
		29742 => array(1, 2),	//Medium Armor: Athletics
		45574 => array(1, 2),
		
		39192 => array(1, 2),	// Medium Armor: Elude
		41133 => array(1, 2),
		41135 => array(1, 2),
		41137 => array(1, 2),
		
		20930 => 3,
		34042 => 3,
		34045 => 3,
		34048 => 3,
);

$ESO_COEF_VALUE = array(
		30893 => 547,		//DW: Twin Blade and Blunt
		45482 => 1095,
		
		18866 => array(		//Nightblade: Shadow Barrier
					array(25, 0, 0),
					array(0.75, 0, 3),
				),
		45071 => array (
					array(25, 0, 0),
					array(1.5, 0, 6),
				),
		
		//45533 => array(362.4, 0, 0),	// Heavy Armor: Resolve
		
		29791 => array(0.5, 0, 0),	//Heavy Armor: Rapid Mending
		45529 => array(1, 0, 0),
		
		39197 => array(5, 0, 65),		//Heavy Armor: Unstoppable
		41097 => array(5, 0, 65),
		41100 => array(5, 0, 65),
		41103 => array(5, 0, 65),
		
		39192 => array(					//Medium Armor: Elude
					array(2, 0, 16),
					array(1, 0, 0),
				),
		41133 => array(
					array(2, 0, 16),
					array(1, 0, 0),
				),
		41135 => array(
					array(2, 0, 16),
					array(1, 0, 0),
				),
		41137 => array(
					array(2, 0, 16),
					array(1, 0, 0),
				),
		
		20930 => array(1/6300, 1/600, 6),	// Engulfing Flames, updated 27 Feb 2020, PTS v5.3.3
		34042 => array(1/6300, 1/600, 6),   // Technically Ranks 1-3 are 1 less than Rank 4 for both Magicka and Spell Damage
		34045 => array(1/6300, 1/600, 6),	// Changed to 6% cap in update 36?
		34048 => array(1/6300, 1/600, 6),
		
	/* Old Values
		29338 => array(0.3300, 0.4, 0),		//Light Armor: Annulment
		41106 => array(0.3336, 0.4, 0),
		41107 => array(0.3372, 0.4, 0),
		41108 => array(0.3410, 0.4, 0),
		
		39186 => array(0.3410, 0.5, 0),		//Light Armor: Dampen Magic
		41109 => array(0.3410, 0.5, 0),
		41111 => array(0.3410, 0.5, 0),
		41113 => array(0.3410, 0.5, 0),
		
		39182 => array(0.3410, 0.4, 0),		//Light Armor: Harness Magicka
		41115 => array(0.3410, 0.4, 0),
		41118 => array(0.3410, 0.4, 0),
		41121 => array(0.3410, 0.4, 0), */
		
		29665 => array(						//Light Armor: Evocation
					array(2, 0, 0),
					array(1, 0, 0) 
				),
		45557 => array(
					array(4, 0, 0),
					array(2, 0, 0) 
				),
		
		29639 => array(						//Light Armor: Grace
					array(1, 0, 0),
					array(1, 0, 0) 
				),
		45548 => array(
					array(2, 0, 0),
					array(2, 0, 0)
				),
		45549 => array(
					array(4, 0, 0),
					array(3, 0, 0)
				),
		
		29742 => array(						//Medium Armor: Athletics
					array(2, 0, 0),
					array(2, 0, 0) 
				),
		45574 => array(
					array(3, 0, 0),
					array(4, 0, 0) 
				),
		
		116197 => 4,	//Necromancer: Death Knell
		116198 => 8,
		
/* Old Values
		28418 => array(0.3655, 0.4, 0),		//Sorcerer: Conjured Ward
		30457 => array(0.3694, 0.4, 0),
		30460 => array(0.3735, 0.4, 0),
		30463 => array(0.3775, 0.4, 0),
		
		29489 => array(0.3775, 0.5, 0),		//Sorcerer: Hardened Ward
		30466 => array(0.3817, 0.5, 0),
		30470 => array(0.3858, 0.5, 0),
		30474 => array(0.3900, 0.5, 0),
		
		29482 => array(0.3775, 0.4, 0),		//Sorcerer: Empowered Ward
		30478 => array(0.3817, 0.4, 0),
		30482 => array(0.3858, 0.4, 0),
		30486 => array(0.3900, 0.4, 0), */
);


	// 1-based counting (1 == first number in tooltip)
$ESO_COEF_NUMBER_INDEX = array(
		
		30893 => 6,		//DW: Twin Blade and Blunt
		45482 => 6,
		
		18866 => array(4, 5),		//Nightblade: Shadow Barrier
		45071 => array(4, 5),
		
		39197 => 4,		//Heavy Armor: Unstoppable
		41097 => 4,
		41100 => 4,
		41103 => 4,
		
		39192 => array(2, 3),		//Medium Armor: Elude
		41133 => array(2, 3),
		41135 => array(2, 3),
		41137 => array(2, 3),
		
		29665 => array(2, 4),	//Light Armor: Evocation
		45557 => array(2, 4),
		
		29639 => array(2, 4),	//Light Armor: Grace
		45548 => array(2, 4),
		45549 => array(2, 4),
		
		29742 => array(2, 4),	//Medium Armor: Athletics
		45574 => array(2, 4),
);


/*
 * abilityId => Coef Index (1 = first coef)
 */
$ESO_EXACT_SKILL_VALUES = array(
		
		29825 => 1,		// Heavy Armor: Resolve
		45531 => 1,
		45533 => 1,
		
		29769 => 2,		// Heavy Armor: Constitution
		45526 => 2,
		
		29743 => 1,		// Medium Armor: Dexterity
		45563 => 1,
		45564 => 1,
		
		29663 => 1,		// Light Armor: Spell Warding
		45559 => 1,
		
		29668 => 1,		// Light Armor: Prodigy
		45561 => 1,
		
		29667 => 1,		// Light Armor: Concentration
		45562 => 1,
	
);


$ESO_EXACT_SKILL_VALUES_OVERWRITE = array(
		
		//45533 => 362.4,		// Heavy Armor: Resolve
		
		29769 => 108,		// Heavy Armor: Constitution
		45526 => 108,
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
	
	$index = 1;
	if ($ESO_COEF_INDEX[$skillId] != null) $index = $ESO_COEF_INDEX[$skillId];
	if (!is_array($index)) $index = array($index);
	
	foreach($index as $i)
	{
		$skillsData[$skillId]['type' + $i] = $powerType;
	}

}

	// Special case for parsing cap of Engulfing Flames
if ($skillsData[20930])
{
	$desc = FormatRemoveEsoItemDescriptionText($skillsData[20930]['description']);
	
	if (preg_match('/a maximum of ([0-9]+)% bonus damage/', $desc, $matches))
	{
		$damageCap = floatval($matches[1]);
		
		if ($damageCap > 0)
		{
			$ESO_COEF_VALUE[20930][2] = $damageCap;
			$ESO_COEF_VALUE[34042][2] = $damageCap;
			$ESO_COEF_VALUE[34045][2] = $damageCap;
			$ESO_COEF_VALUE[34048][2] = $damageCap;
			print("\tFound damage cap of $damageCap% for Engulfing Flames!\n");
		}
		else
		{
			print("\tWarning: Failed to find damage cap in Engulfing Flames (zero or non-numeric value found)!\n");
			print("\tDescription: $desc\n");
		}
	}
	else
	{
		print("\tWarning: Failed to find damage cap in Engulfing Flames (description didn't match)!\n");
		print("\tDescription: $desc\n");
	}
}
else
{
	print("\tWarning: Failed to find damage cap in Engulfing Flames (skill data not loaded)!\n");
}


foreach ($skillsData as $skillId => &$skill)
{
	$index = 1;
	if ($ESO_COEF_INDEX[$skillId] != null) $index = $ESO_COEF_INDEX[$skillId];
	if (!is_array($index)) $index = array($index);
	
	$coefDescription = $skill['coefDescription'];
	$fixupCoefDesc = false;
	
	if ($coefDescription == "")
	{
		$coefDescription = $skill['description'];
		$fixupCoefDesc = true;
	}
	
	$numberIndex = $ESO_COEF_NUMBER_INDEX[$skillId];
	
	if (!is_array($numberIndex))
	{
		$numberIndex = array($numberIndex);
	}
	else
	{
		$coefDescription = $skill['description'];
		$fixupCoefDesc = true;
	}	
	
	foreach ($numberIndex as $i => $niValue)
	{
		$indexValue = $index[$i];
		
		if ($indexValue == null)
		{
			print("\tError: {$skill['name']} ($skillId): Null coefficent index value at array index $i!\n");
			continue;
		}
		
		if ($fixupCoefDesc || $niValue != null)
		{
			if (!is_array( $ESO_COEF_NUMBER_INDEX[$skillId])) 
			{
				$coefDescription = $skill['description'];
				if ($coefDescription == "")	$coefDescription = $skill['description'];
			}
			
			$foundCount = 0;
			
			$coefDescription = preg_replace_callback('|(\$?[0-9]+(?:\.\d+)?)|', function($matches) {
				global $foundCount, $niValue, $indexValue;
				++$foundCount;
				if ($foundCount == $niValue) return '$' . $indexValue;
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
			
			if (is_array($coefValue[0]))
			{
				$coefValue = $coefValue[$i];
				
				if ($coefValue == null)
				{
					print("\t{$skill['name']} ($skillId): Null coefficient data at index $i!\n");
					continue;
				}
			}
			
			$valueA = $coefValue[0];
			$valueB = $coefValue[1];
			$valueC = $coefValue[2];
		}
		
		print("\t{$skill['name']} ($skillId): Found value $valueA:$valueB:$valueC\n");
		
		$skill['a' . $indexValue] = $valueA;
		$skill['b' . $indexValue] = $valueB;
		$skill['c' . $indexValue] = $valueC;
		$skill['avg' . $indexValue] = 0;
		$skill['R' . $indexValue] = 1;
		$skill['numCoefVars'] = $indexValue;
		
		if (!is_array($ESO_COEF_NUMBER_INDEX[$skillId])) 
			$skill['coefDescription'] = preg_replace("#Current bonus: \|cffffff[0-9]+\|r#i", 'Current bonus: |cffffff\$'. $indexValue . '|r', $coefDescription);
		else
			$skill['coefDescription'] = $coefDescription;
		
		$type = $skill['type' + $indexValue];
		
		//print("\tCoef Description: {$skill['coefDescription']}\n");
		
		$coef = $db->real_escape_string($skill['coefDescription']);
		
		$query = "UPDATE minedSkills$TABLE_SUFFIX SET type$indexValue=$type, a$indexValue={$skill['a'.$indexValue]}, b$indexValue={$skill['b'.$indexValue]}, c$indexValue={$skill['c'.$indexValue]}, avg$indexValue=0, R$indexValue=1, numCoefVars=$indexValue, coefDescription='$coef' WHERE id=$skillId;";
		//print("\t$query\n");
		$result = $db->query($query);
		if (!$result) print("\tError saving skill $skillId!\n");
	}
}


foreach ($ESO_EXACT_SKILL_VALUES as $skillId => $indexValue)
{
	$query = "SELECT * FROM minedSkills$TABLE_SUFFIX WHERE id=$skillId;";
	$result = $db->query($query);
	
	if (!$result)
	{
		print("Failed to load skill $skillId!\n");
		continue;
	}
	
	$skill = $result->fetch_assoc();
	$coefDescription = $skill['description'];
	
	$result = preg_match("#by \|cffffff([0-9]+)\|r#", $coefDescription, $matches);
	
	if (!$result)
	{
		print("Failed to find exact skill value match for $skillId!\n");
		continue;
	}
	
	$exactValue = $ESO_EXACT_SKILL_VALUES_OVERWRITE[$skillId];
	if ($exactValue == null) $exactValue = $matches[1];
	
	$a = $db->real_escape_string($exactValue);
	$b = 0;
	$c = 0;
	$R = 1;
	
	$query = "UPDATE minedSkills$TABLE_SUFFIX SET a$indexValue='$a', b$indexValue=0, c$indexValue=0, R$indexValue=1 WHERE id=$skillId;";
	
	$result = $db->query($query);
	if (!$result) print("\tError saving skill $skillId!\n{$db->error}\n");
}

