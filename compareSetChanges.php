<?php

if (php_sapi_name() != "cli") die("Can only be run from command line!");

require("/home/uesp/secrets/esolog.secrets");

$db = new mysqli($uespEsoLogReadDBHost, $uespEsoLogReadUser, $uespEsoLogReadPW, $uespEsoLogDatabase);
if ($db->connect_error) exit("Could not connect to mysql database!");

$TABLE_SUFFIX1 = "40test";
$TABLE_SUFFIX2 = "";

$table1 = "setSummary".$TABLE_SUFFIX1;
$table2 = "setSummary".$TABLE_SUFFIX2;

print("Finding differences in sets from $table1 and $table2...\n");

$query = "select $table1.setName, $table1.setBonusDesc as desc1,$table2.setBonusDesc as desc2 from $table1 LEFT JOIN $table2 on $table2.setName=$table1.setName WHERE $table1.setBonusDesc!=$table2.setBonusDesc;";
$result = $db->query($query);
if (!$result) exit("ERROR: Database query error (finding items)!\n" . $db->error);

$count = $result->num_rows;
print("\tFound $count differences!\n");

while ($item = $result->fetch_assoc())
{
	$setName = $item['setName'];
	$desc1 = $item['desc1'];
	$desc2 = $item['desc2'];
	
	$rawDesc1 = preg_replace('/[0-9]+/', '', $desc1);
	$rawDesc2 = preg_replace('/[0-9]+/', '', $desc2);
	
	$numDesc1 = preg_replace('/[^0-9]+/', ' ', $desc1);
	$numDesc2 = preg_replace('/[^0-9]+/', ' ', $desc2);
	
	$match1 = preg_match_all('/([0-9-]+)/', $desc1, $numMatches1);
	$match2 = preg_match_all('/([0-9-]+)/', $desc2, $numMatches2);
	
	if ($numDesc1 == $numDesc2)
	{
		print("\t$setName: Differs only in text\n");
		continue;
	}
	
	if ($rawDesc1 == $rawDesc2)
	{
		$count = 0;
		
		for ($i = 1; $i < count($numMatches1[1]); $i++)
		{
			//print("\t\t{$numMatches1[1][$i]}\n");
			if ($numMatches1[1][$i] != $numMatches2[1][$i]) $count++;
		}
		
		print("\t$setName: Differs only in numbers ($count)\n");
		
		if ($count > 1)
		{
			print("\t\t$numDesc1\n");
			print("\t\t$numDesc2\n");
			print("\t\t$desc1\n");
		}
		continue;
	}
	
	print("\t$setName: Differs both in text and numbers\n");
	print("\t\t$desc1\n");
	print("\t\t$desc2\n");
}

