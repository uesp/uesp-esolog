<?php 

if (php_sapi_name() != "cli") die("Can only be run from command line!");

require("/home/uesp/secrets/esolog.secrets");

$db = new mysqli($uespEsoLogWriteDBHost, $uespEsoLogWriteUser, $uespEsoLogWritePW, $uespEsoLogDatabase);
if ($db->connect_error) exit("Could not connect to mysql database!");

while (true) {

	$query = "SELECT id, setMaxEquipCount, setBonusDesc1, setBonusDesc2, setBonusDesc3, setBonusDesc4, setBonusDesc5, setBonusCount FROM minedItem WHERE setBonusDesc1 != '' AND setMaxEquipCount=-1 LIMIT 10000;";
	print ($query. "\n");
	$result = $db->query($query);
	if (!$result) exit("Query failed!");
	if ($result->num_rows === 0) exit("Nothing to do!");
	print ("\tQuery done...\n");
	
	$result->data_seek(0);
	$count = 0;
	
	while (($row = $result->fetch_assoc()))
	{
		$count += 1;
		if ($count % 100 == 0) print("$count...\n");
		
		if (array_key_exists('setMaxEquipCount', $row) && $row['setMaxEquipCount'] == -1)
		{
			$highestSetDesc = "";
				
			if (array_key_exists('setBonusDesc1', $row) && $row['setBonusDesc1'] != "") $highestSetDesc = $row['setBonusDesc1'];
			if (array_key_exists('setBonusDesc2', $row) && $row['setBonusDesc2'] != "") $highestSetDesc = $row['setBonusDesc2'];
			if (array_key_exists('setBonusDesc3', $row) && $row['setBonusDesc3'] != "") $highestSetDesc = $row['setBonusDesc3'];
			if (array_key_exists('setBonusDesc4', $row) && $row['setBonusDesc4'] != "") $highestSetDesc = $row['setBonusDesc4'];
			if (array_key_exists('setBonusDesc5', $row) && $row['setBonusDesc5'] != "") $highestSetDesc = $row['setBonusDesc5'];
				
			if ($highestSetDesc != "")
			{
				$matches = array();
				$matchResult = preg_match("/\(([0-9]+) items\)/", $highestSetDesc, $matches);
				
				if ($matchResult) 
				{
					$row['setMaxEquipCount'] = (int) $matches[1];
					$setMaxEquipCount = $row['setMaxEquipCount'];
					$id = $row['id'];
					
					$writeQuery = "UPDATE minedItem set setMaxEquipCount=$setMaxEquipCount WHERE id=$id;";
					if ($count % 100 == 0) print "\t\t$writeQuery \n";
					$writeResult = $db->query($writeQuery);
					if (!$writeResult) print("Write query error!\n");
				}
			}
		}
		
	}
}


?>
