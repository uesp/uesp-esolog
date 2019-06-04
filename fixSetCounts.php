<?php 

if (php_sapi_name() != "cli") die("Can only be run from command line!");

require("/home/uesp/secrets/esolog.secrets");

$db = new mysqli($uespEsoLogWriteDBHost, $uespEsoLogWriteUser, $uespEsoLogWritePW, $uespEsoLogDatabase);
if ($db->connect_error) exit("Could not connect to mysql database!");

$MAX_ITEM_ID = 130000;
$count = 0;

for ($itemId = 1; $itemId <= $MAX_ITEM_ID; ++$itemId)
{
	$query = "SELECT id, setName, setMaxEquipCount, setBonusDesc1, setBonusDesc2, setBonusDesc3, setBonusDesc4, setBonusDesc5, setBonusCount FROM minedItem WHERE itemId=$itemId AND internalLevel=1 AND internalSubtype=1;";
	$result = $db->query($query);
	if (!$result) exit("Query failed!");
	if ($result->num_rows === 0) continue;
	
	$result->data_seek(0);
	$itemData = $result->fetch_assoc();
	
	//++$count;
	//if ($count % 1000 == 0) 
	
	if ($itemData['setName'] == "") continue;
	
	$highestSetDesc = "";
		
	if ($itemData['setBonusDesc1'] != "") $highestSetDesc = $itemData['setBonusDesc1'];
	if ($itemData['setBonusDesc2'] != "") $highestSetDesc = $itemData['setBonusDesc2'];
	if ($itemData['setBonusDesc3'] != "") $highestSetDesc = $itemData['setBonusDesc3'];
	if ($itemData['setBonusDesc4'] != "") $highestSetDesc = $itemData['setBonusDesc4'];
	if ($itemData['setBonusDesc5'] != "") $highestSetDesc = $itemData['setBonusDesc5'];
		
	if ($highestSetDesc != "")
	{
		$matches = array();
		$matchResult = preg_match("/\(([0-9]+) items\)/", $highestSetDesc, $matches);
		
		if ($matchResult) 
		{
			$setMaxEquipCount = (int) $matches[1];
			print("$itemId: Updating set coun to '$setMaxEquipCount'...\n");
			
			$writeQuery = "UPDATE minedItem set setMaxEquipCount=$setMaxEquipCount WHERE itemId=$itemId;";
			$writeResult = $db->query($writeQuery);
			if (!$writeResult) print("Write query error!\n");
		}
	}
	
}


?>
