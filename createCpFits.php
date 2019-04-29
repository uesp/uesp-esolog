<?php

$TABLE_SUFFIX = "22pts";
$USE_CUBIC_FIT = true;

if (php_sapi_name() != "cli") die("Can only be run from command line!");
print("Creating CP data fits...\n");

require("/home/uesp/secrets/esolog.secrets");

$db = new mysqli($uespEsoLogWriteDBHost, $uespEsoLogWriteUser, $uespEsoLogWritePW, $uespEsoLogDatabase);
if ($db->connect_error) exit("Could not connect to mysql database!");

$skillData = array();

$query = "SELECT * FROM cpSkills$TABLE_SUFFIX;";
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

$query = "SELECT * FROM cpSkillDescriptions$TABLE_SUFFIX;";
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


function ComputePowerFit($skillPoints, $skillData)
{
	global $db;
	
	print("Computing power fits and saving data...\n");

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

		$query = "UPDATE cpSkills$TABLE_SUFFIX SET a=$a, b=$b, c=-1, d=-1, r2=$R2, fitDescription=\"$fitDesc\" WHERE abilityId=$id";
		$result = $db->query($query);
		if (!$result) print("\tError saving fit to cpSkills table!\n");
		
	}
}


function ComputeCubicFit($skillPoints, $skillData)
{
	global $db;
	
	print("Computing cubic fits and saving data...\n");
	
	foreach ($skillPoints as $id => $skill)
	{
		$skillFits[$id] = array();
		$name = $skillData[$id]['name'];
		
		$count = 0;
		$sumX = 0;
		$sumX2 = 0;
		$sumX3 = 0;
		$sumX4 = 0;
		$sumX5 = 0;
		$sumX6 = 0;
		$sumY = 0;
		$sumXY = 0;
		$sumX2Y = 0;
		$sumX3Y = 0;
		$avgY = 0;
		
		if (count($skill) <= 0) continue;
		
		foreach ($skill as $points => $value)
		{
			//print ("$points, $value\n");
			
			$sumX += $points;
			$sumX2 += pow($points, 2);
			$sumX3 += pow($points, 3);
			$sumX4 += pow($points, 4);
			$sumX5 += pow($points, 5);
			$sumX6 += pow($points, 6);
			
			$sumY += $value;
			$sumXY += $value*$points;
			$sumX2Y += $value*pow($points, 2);
			$sumX3Y += $value*pow($points, 3);
			
			$avgY += $value;
			$count++;
		}
		
		//print("sumY = $sumY\n");
		//print("sumXY = $sumXY\n");
		//print("sumX2Y = $sumX2Y\n");
		//print("sumX3Y = $sumX3Y\n");
		
		$a11 = $count;
		$a12 = $sumX;
		$a13 = $sumX2;
		$a14 = $sumX3;
		$a21 = $sumX;
		$a22 = $sumX2;
		$a23 = $sumX3;
		$a24 = $sumX4;
		$a31 = $sumX2;
		$a32 = $sumX3;
		$a33 = $sumX4;
		$a34 = $sumX5;
		$a41 = $sumX3;
		$a42 = $sumX4;
		$a43 = $sumX5;
		$a44 = $sumX6;

		//print("$a11  $a12  $a13  $a14\n");
		//print("$a21  $a22  $a23  $a24\n");
		//print("$a31  $a32  $a33  $a34\n");
		//print("$a41  $a42  $a43  $a44\n");
		
		$detX  = $a11*$a22*$a33*$a44;
		$detX += $a11*$a23*$a34*$a42;
		$detX += $a11*$a24*$a32*$a43;
		$detX += $a12*$a21*$a34*$a43;
		$detX += $a12*$a23*$a31*$a44;
		$detX += $a12*$a24*$a33*$a41;
		$detX += $a13*$a21*$a32*$a44;
		$detX += $a13*$a22*$a34*$a41;
		$detX += $a13*$a24*$a31*$a42;
		$detX += $a14*$a21*$a33*$a42;
		$detX += $a14*$a22*$a31*$a43;
		$detX += $a14*$a23*$a32*$a41;
		$detX -= $a11*$a22*$a34*$a43;
		$detX -= $a11*$a23*$a32*$a44;
		$detX -= $a11*$a24*$a33*$a42;
		$detX -= $a12*$a21*$a33*$a44;
		$detX -= $a12*$a23*$a34*$a41;
		$detX -= $a12*$a24*$a31*$a43;
		$detX -= $a13*$a21*$a34*$a42;
		$detX -= $a13*$a22*$a31*$a44;
		$detX -= $a13*$a24*$a32*$a41;
		$detX -= $a14*$a21*$a32*$a43;
		$detX -= $a14*$a22*$a33*$a41;
		$detX -= $a14*$a23*$a31*$a42;
		
		if ($detX == 0)
		{
			print ("\t$name: 0 determinant!\n");
			continue;
		}
		
		//print("\tdetX = $detX\n");
		
		$b11 = ($a22*$a33*$a44 + $a23*$a34*$a42 + $a24*$a32*$a43 - $a22*$a34*$a43 - $a23*$a32*$a44 - $a24*$a33*$a42) / $detX;
		$b12 = ($a12*$a34*$a43 + $a13*$a32*$a44 + $a14*$a33*$a42 - $a12*$a33*$a44 - $a13*$a34*$a42 - $a14*$a32*$a43) / $detX;
		$b13 = ($a12*$a23*$a44 + $a13*$a24*$a42 + $a14*$a22*$a43 - $a12*$a24*$a43 - $a13*$a22*$a44 - $a14*$a23*$a42) / $detX;
		$b14 = ($a12*$a24*$a33 + $a13*$a22*$a34 + $a14*$a23*$a32 - $a12*$a23*$a34 - $a13*$a24*$a32 - $a14*$a22*$a33) / $detX;
		$b21 = ($a21*$a34*$a43 + $a23*$a31*$a44 + $a24*$a33*$a41 - $a21*$a33*$a44 - $a23*$a34*$a41 - $a24*$a31*$a43) / $detX;
		$b22 = ($a11*$a33*$a44 + $a13*$a34*$a41 + $a14*$a31*$a43 - $a11*$a34*$a43 - $a13*$a31*$a44 - $a14*$a33*$a41) / $detX;
		$b23 = ($a11*$a24*$a43 + $a13*$a21*$a44 + $a14*$a23*$a41 - $a11*$a23*$a44 - $a13*$a24*$a41 - $a14*$a21*$a43) / $detX;
		$b24 = ($a11*$a23*$a34 + $a13*$a24*$a31 + $a14*$a21*$a33 - $a11*$a24*$a33 - $a13*$a21*$a34 - $a14*$a23*$a31) / $detX;
		$b31 = ($a21*$a32*$a44 + $a22*$a34*$a41 + $a24*$a31*$a42 - $a21*$a34*$a42 - $a22*$a31*$a44 - $a24*$a32*$a41) / $detX;
		$b32 = ($a11*$a34*$a42 + $a12*$a31*$a44 + $a14*$a32*$a41 - $a11*$a32*$a44 - $a12*$a34*$a41 - $a14*$a31*$a42) / $detX;
		$b33 = ($a11*$a22*$a44 + $a12*$a24*$a41 + $a14*$a21*$a42 - $a11*$a24*$a42 - $a12*$a21*$a44 - $a14*$a22*$a41) / $detX;
		$b34 = ($a11*$a24*$a32 + $a12*$a21*$a34 + $a14*$a22*$a31 - $a11*$a22*$a34 - $a12*$a24*$a31 - $a14*$a21*$a32) / $detX;
		$b41 = ($a21*$a33*$a42 + $a22*$a31*$a43 + $a23*$a32*$a41 - $a21*$a32*$a43 - $a22*$a33*$a41 - $a23*$a31*$a42) / $detX;
		$b42 = ($a11*$a32*$a43 + $a12*$a33*$a41 + $a13*$a31*$a42 - $a11*$a33*$a42 - $a12*$a31*$a43 - $a13*$a32*$a41) / $detX;
		$b43 = ($a11*$a23*$a42 + $a12*$a21*$a43 + $a13*$a22*$a41 - $a11*$a22*$a43 - $a12*$a23*$a41 - $a13*$a21*$a42) / $detX;
		$b44 = ($a11*$a22*$a33 + $a12*$a23*$a31 + $a13*$a21*$a32 - $a11*$a23*$a32 - $a12*$a21*$a33 - $a13*$a22*$a31) / $detX;
		
		$a0 = $b11*$sumY + $b12*$sumXY + $b13*$sumX2Y + $b14*$sumX3Y;
		$a1 = $b21*$sumY + $b22*$sumXY + $b23*$sumX2Y + $b24*$sumX3Y;
		$a2 = $b31*$sumY + $b32*$sumXY + $b33*$sumX2Y + $b34*$sumX3Y;
		$a3 = $b41*$sumY + $b42*$sumXY + $b43*$sumX2Y + $b44*$sumX3Y;
		
		$R2 = 0;
		$ssRes = 0;
		$ssTot = 0;
		
		foreach ($skill as $points => $value)
		{
			$diff = $value - ($a0 + $a1*points + $a2*pow($points,2) + $a3*pow($points,3));
			$ssRes += $diff * $diff;
				
			$diff = $value - $avgY;
			$ssTot += $diff * $diff;
		}
		
		$R2 = 1 - $ssRes / $ssTot;
		
		$r2Text = sprintf("%0.6f", $R2);
		$a0Text = sprintf("%0.6f", $a0);
		$a1Text = sprintf("%0.6f", $a1);
		$a2Text = sprintf("%0.6f", $a2);
		$a3Text = sprintf("%0.6f", $a3);
		$fitDesc = "Value = $a0Text + $a1Text X + $a2Text X^2 + $a3Text X^3 (R2 = $r2Text)";
		print ("\t$name: $fitDesc\n");
		
		$query = "UPDATE cpSkills$TABLE_SUFFIX SET a=$a0, b=$a1, c=$a2, d=$a3, r2=$R2, fitDescription=\"$fitDesc\" WHERE abilityId=$id";
		$result = $db->query($query);
		if (!$result) print("\tError saving fit to cpSkills table!\n");
	}
}


if ($USE_CUBIC_FIT)
	ComputeCubicFit($skillPoints, $skillData);
else
	ComputePowerFit($skillPoints, $skillData);
	

print ("Finished!\n");

