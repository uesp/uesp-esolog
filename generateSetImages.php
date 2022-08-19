<?php


$TABLE_SUFFIX = "27pts";
$OUTPUT_PATH = "/tmp/sets/";

require("itemLinkImage.class.php");

if ($TABLE_SUFFIX != "") $OUTPUT_PATH .= "$TABLE_SUFFIX/";

print("Generating all set image tooltips for update $TABLE_SUFFIX to '$OUTPUT_PATH'...\n");

if (!file_exists($OUTPUT_PATH) && !mkdir($OUTPUT_PATH, 0775, true)) exit("Error: Failed to create directory '$OUTPUT_PATH'!");

$itemLinkImage = new CEsoItemLinkImage();
$itemLinkImage->version = $TABLE_SUFFIX;

$db = new mysqli($uespEsoLogReadDBHost, $uespEsoLogReadUser, $uespEsoLogReadPW, $uespEsoLogDatabase);
if ($db->connect_error) exit("Could not connect to mysql database!");

$query = "SELECT * FROM setSummary$TABLE_SUFFIX ORDER BY setName;";
$result = $db->query($query);
if ($result === false) die("Failed to load set records!\n");

$sets = [];

while ($row = $result->fetch_assoc())
{
	$sets[] = $row;
}

$count = count($sets);
print("Loaded $count sets for update $TABLE_SUFFIX!\n");

$sumTime = 0;
$successCount = 0;

foreach ($sets as $set)
{
	$setName = $set['setName'];
	
	$startTime = microtime(true);
	
	$image = $itemLinkImage->CreateSetImage($setName);
	
	$sumTime += microtime(true) - $startTime;
	
	if ($image)
	{
		$filename = $OUTPUT_PATH . $setName . ".png";
		imagepng($image, $filename);
		print("\tSaved $setName to '$filename'\n");
		++$successCount;
	}
	
}

if ($count > 0)
{
	$avgTime = ($sumTime / $count * 1000);
	print("Saved $successCount set image tooltips with an average generation time of $avgTime ms.\n");
}
