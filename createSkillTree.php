<?php

$TABLE_SUFFIX = "";

if (php_sapi_name() != "cli") die("Can only be run from command line!");
print("Creating skill tree from mined skill data...\n");

require("/home/uesp/secrets/esolog.secrets");


function TransformBonusDesc($desc)
{
	$newDesc = preg_replace('/\|c[0-9a-fA-F]{6}([0-9a-zA-Z\_\.\-\%\s]+)\|r/', '$1', $desc);
	return preg_replace('/\n/', ' ', $newDesc);
}


$db = new mysqli($uespEsoLogWriteDBHost, $uespEsoLogWriteUser, $uespEsoLogWritePW, $uespEsoLogDatabase);
if ($db->connect_error) exit("Could not connect to mysql database!");

$query = "CREATE TABLE IF NOT EXISTS skillTree".$TABLE_SUFFIX."(
			id BIGINT NOT NULL AUTO_INCREMENT PRIMARY KEY,
			abilityId BIGINT NOT NULL,
			skillTypeName TINYTEXT NOT NULL,
			rank INTEGER NOT NULL DEFAULT -1
		);";

$result = $db->query($query);
if (!$result) exit("ERROR: Database query error creating table!\n" . $db->error);

$query = "DELETE * FROM skillTree".$TABLE_SUFFIX.";";
$result = $db->query($query);
if (!$result) exit("ERROR: Database query error (clearing tree)!\n" . $db->error);

$query = "SELECT * FROM minedSkillLines".$TABLE_SUFFIX." ORDER BY skillType, classType, raceType;";
$skillLineResult = $db->query($query);
if (!$skillLineResult) exit("ERROR: Database query error (finding skill lines)!\n" . $db->error);
$skillLineResult->data_seek(0);

while (($skillLine = $skillLineResult->fetch_assoc()))
{
	$query = "SELECT * FROM minedSkills".$TABLE_SUFFIX." WHERE skillType=".$skillLine['skillType']." AND skillLine='".$skillLine['name']."';";
	$skillResult = $db->query($query);
	if (!$skillResult) exit("ERROR: Database query error (finding skills)!\n" . $db->error);
}
	++$itemCount;
	$setName = $row['setName'];
	$setBonusDesc1 = TransformBonusDesc($row['setBonusDesc1']);
	$setBonusDesc2 = TransformBonusDesc($row['setBonusDesc2']);
	$setBonusDesc3 = TransformBonusDesc($row['setBonusDesc3']);
	$setBonusDesc4 = TransformBonusDesc($row['setBonusDesc4']);
	$setBonusDesc5 = TransformBonusDesc($row['setBonusDesc5']);
	$setBonusCount = 0;
	$setMaxEquipCount = $row['setMaxEquipCount'];
	if ($setMaxEquipCount == null || $setMaxEquipCount == "") $setMaxEquipCount = 1; 
	
	$lastBonusDesc = $setBonusDesc5;
	if ($lastBonusDesc == "") $lastBonusDesc = $setBonusDesc4;
	if ($lastBonusDesc == "") $lastBonusDesc = $setBonusDesc3;
	if ($lastBonusDesc == "") $lastBonusDesc = $setBonusDesc2;
	if ($lastBonusDesc == "") $lastBonusDesc = $setBonusDesc1;
	
	if ($setBonusDesc1 != "") $setBonusCount = 1;
	if ($setBonusDesc2 != "") $setBonusCount = 2;
	if ($setBonusDesc3 != "") $setBonusCount = 3;
	if ($setBonusDesc4 != "") $setBonusCount = 4;
	if ($setBonusDesc5 != "") $setBonusCount = 5;
	
	if (!array_key_exists($setName, $setItemSlots)) $setItemSlots[$setName] = array();
	UpdateItemSlotArray($setItemSlots[$setName], $row);
	
	$matches = array();
	$regResult = preg_match('/\(([0-9]+) items\)/', $lastBonusDesc, $matches);
	if ($regResult) $setMaxEquipCount = $matches[1];
	
	print("\tUpdating set $setName with $setMaxEquipCount items...\n");
	//print("\t\t$setBonusDesc1 == " . $row['setBonusDesc1'] . "\n");
	
	$query = "SELECT * FROM setSummary".$TABLE_SUFFIX." WHERE setName=\"$setName\";";
	$result = $db->query($query);
	if (!$result) exit("ERROR: Database query error finding set!\n" . $db->error);
	
	$createNewSet = true;
	$updateId = -1;
	
	while ( ($newRow = $result->fetch_assoc()) )
	{
		$matches = true;
		
		$newBonusDesc1 = preg_replace('/\|c[0-9a-fA-F]{6}([0-9\.\-\%\s]+)\|r/', '$1', $newRow['setBonusDesc1']);
		$newBonusDesc2 = preg_replace('/\|c[0-9a-fA-F]{6}([0-9\.\-\%\s]+)\|r/', '$1', $newRow['setBonusDesc2']);
		$newBonusDesc3 = preg_replace('/\|c[0-9a-fA-F]{6}([0-9\.\-\%\s]+)\|r/', '$1', $newRow['setBonusDesc3']);
		$newBonusDesc4 = preg_replace('/\|c[0-9a-fA-F]{6}([0-9\.\-\%\s]+)\|r/', '$1', $newRow['setBonusDesc4']);
		$newBonusDesc5 = preg_replace('/\|c[0-9a-fA-F]{6}([0-9\.\-\%\s]+)\|r/', '$1', $newRow['setBonusDesc5']);
		
		if ($newBonusDesc1 != $setBonusDesc1) { $matches = true; print("\t\tSet bonus #1 doesn't match!\n"); }
		if ($newBonusDesc2 != $setBonusDesc2) { $matches = true; print("\t\tSet bonus #2 doesn't match!\n"); }
		if ($newBonusDesc3 != $setBonusDesc3) { $matches = true; print("\t\tSet bonus #3 doesn't match!\n"); }
		if ($newBonusDesc4 != $setBonusDesc4) { $matches = true; print("\t\tSet bonus #4 doesn't match!\n"); }
		if ($newBonusDesc5 != $setBonusDesc5) { $matches = true; print("\t\tSet bonus #5 doesn't match!\n"); }
		if ($newRow['setMaxEquipCount'] != $setMaxEquipCount) { $matches = false; print("\t\tSet max equip count doesn't match!\n"); }
		
		if ($matches) 
		{
			$updateId = $newRow['id'];
			$createNewSet = false;
			break;
		}
	}
	
	if ($createNewSet)
	{
		//print("\t\tCreating new set...\n");
		++$newCount;
		
		$setBonusDesc = "";
		if ($setBonusDesc1 != "") $setBonusDesc .= $setBonusDesc1;
		if ($setBonusDesc2 != "") $setBonusDesc .= "\n".$setBonusDesc2;
		if ($setBonusDesc3 != "") $setBonusDesc .= "\n".$setBonusDesc3;
		if ($setBonusDesc4 != "") $setBonusDesc .= "\n".$setBonusDesc4;
		if ($setBonusDesc5 != "") $setBonusDesc .= "\n".$setBonusDesc5;
		
		$query  = "INSERT INTO setSummary".$TABLE_SUFFIX."(setName, setMaxEquipCount, setBonusCount, itemCount, setBonusDesc1, setBonusDesc2, setBonusDesc3, setBonusDesc4, setBonusDesc5, setBonusDesc) ";
		$query .= "VALUES(\"$setName\", $setMaxEquipCount, $setBonusCount, 1, \"$setBonusDesc1\", \"$setBonusDesc2\", \"$setBonusDesc3\", \"$setBonusDesc4\", \"$setBonusDesc5\", \"$setBonusDesc\");";
		
		$result = $db->query($query);
		if (!$result) exit("ERROR: Database query error inserting into table!\n" . $db->error . "\n" . $query);
	}
	else if ($updateId > 0)
	{
		//print("\t\tUpdating set $updateId...\n");
		++$updateCount;
		$query = "UPDATE setSummary".$TABLE_SUFFIX." SET itemCount=itemCount+1 WHERE id=$updateId;";
		$result = $db->query($query);
		if (!$result) exit("ERROR: Database query error updating table!\n" . $db->error . "\n" . $query);
	}
	else
	{
		print("\t\tError: Unknown set record to update!\n");
	}
	
}

print("\tUpdating set item slots...\n");

foreach ($setItemSlots as $setName => $setSlots)
{
	$slotString = CreateItemSlotString($setSlots);
	$query = "UPDATE setSummary".$TABLE_SUFFIX." SET itemSlots='".$slotString."' WHERE setName=\"".$setName."\";";
	$result = $db->query($query);
	if (!$result) exit("ERROR: Database query error updating table!\n" . $db->error . "\n" . $query);
	//print("$setName: $slotString\n");
}

print("Found $itemCount item sets, $newCount new, $updateCount duplicate!\n");

?>