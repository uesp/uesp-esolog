<?php

$TABLE_SUFFIX = "13pts";

if (php_sapi_name() != "cli") die("Can only be run from command line!");
print("Creating CP data fits...\n");

require("/home/uesp/secrets/esolog.secrets");

$db = new mysqli($uespEsoLogWriteDBHost, $uespEsoLogWriteUser, $uespEsoLogWritePW, $uespEsoLogDatabase);
if ($db->connect_error) exit("Could not connect to mysql database!");

$skillData = array();

$query = "SELECT * FROM cpSkills;";
$result = $db->query($query);
if (!$result) exit("Error loading cpSkills from database!");

while (($row = $result->fetch_assoc() ))
{
	$id = $row['abilityId'];
	$skillData[$id] = $row;
	$skillData[$id]['data'] = array();
}

$count = count($skillData);
print("Loaded $count skills...\n");

$query = "SELECT * FROM cpSkillDescriptions;";
$result = $db->query($query);
if (!$result) exit("Error loading cpSkillDescriptions from database!");
$count = 0;

while (($row = $result->fetch_assoc() ))
{
	$points = $row['points'];
	$id = $row['abilityId'];
	$skillData[$id]['data'][$points] = $row;
	$count++;
}

print("Loaded $count skill descriptions...\n");
$skillPoints = array();
$count = 0;

foreach ($skillData as $id => $skill)
{
	$skillPoints[$id] = array();
	
	foreach ($skill['data'] as $points => $pointsData)
	{
		$unlockLevel = $pointsData['unlockLevel'];
		if ($unlockLevel > 0) continue;
			
		$desc = $pointsData['description'];
		$desc = preg_replace("#\|c[0-9a-fA-F]{6}([0-9]+[.]?[0-9]*)\|r#", "$1", $desc);
		$result = preg_match("#by ([0-9]+[.]?[0-9]*)#", $desc, $matches);
		
		if ($result)
		{
			$skillPoints[$id][$points] = $matches[1];
			$count++;
		}
		else
		{
			print("\t$points: Match failed for '$desc'!\n");
		}
	}
}

print("Parsed $count skill descriptions.\n");
$skillFits = array();
print("Computing fits and saving data...\n");

foreach ($skillPoints as $id => $skill)
{
	$skillFits[$id] = array();
	$count = 0;
	$sumLnX = 0;
	$sumLnY = 0;
	$sumLnXLnY = 0;
	$sumLnX2 = 0;
	$sumLnY2 = 0;
	$avgY = 0;
	
	foreach ($skill as $points => $value)
	{
		if ($points == 0) continue;
		
		$sumLnX += log($points);
		$sumLnY += log($value);
		$sumLnX2 += log($points) * log($points);
		$sumLnY2 += log($value) * log($value);
		$sumLnXLnY += log($points) * log($value);
		$avgY += $value;
		$count++;
	}
	
	if ($count <= 0)
	{
		$skillFits[$id]['a'] = -1;
		$skillFits[$id]['b'] = -1;
		$skillFits[$id]['R2'] = -1;
		continue;
	}
	
	$avgY = $avgY / $count;
	$b = ($count * $sumLnXLnY - $sumLnX * $sumLnY) / ($count * $sumLnX2 - $sumLnX * $sumLnX);
	$a = exp(($sumLnY - $b * $sumLnX)/$count);
	$R2 = 0;
	$ssRes = 0;
	$ssTot = 0;
	
	foreach ($skill as $points => $value)
	{
		$diff = $value - $a * pow($points, $b); 
		$ssRes += $diff * $diff;
		
		$diff = $value - $avgY;
		$ssTot += $diff * $diff;
	}
	
	$R2 = 1 - $ssRes / $ssTot; 
	
	$skillFits[$id]['a'] = $a;
	$skillFits[$id]['b'] = $b;
	$skillFits[$id]['R2'] = $R2;
	
	$name = $skillData[$id]['name'];
	$aText = sprintf("%0.6f", $a);
	$bText = sprintf("%0.6f", $b);
	$r2Text = sprintf("%0.6f", $R2);
	$fitDesc = "Value = $aText * pow(Points, $bText)    (R2 = $r2Text)";
	print ("\t$name: $fitDesc\n");
	
	$query = "UPDATE cpSkills SET a=$a, b=$b, r2=$R2, fitDescription=\"$fitDesc\" WHERE abilityId=$id";
	$result = $db->query($query);
	if (!$result) print("\tError saving fit to cpSkills table!\n");
}

print ("Finished!\n");

