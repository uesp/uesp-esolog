<?php

$OUTPUT_PATH = "/home/dave/esocolsprite/";
$ICON_BASEPATH = "/home/uesp/www/eso/gameicons";

if (php_sapi_name() != "cli") die("Can only be run from command line!");
print("Creating collectible CSS sprite...\n");

require("/home/uesp/secrets/esolog.secrets");

$db = new mysqli($uespEsoLogReadDBHost, $uespEsoLogReadUser, $uespEsoLogReadPW, $uespEsoLogDatabase);
if ($db->connect_error) exit("Could not connect to mysql database!\n");

mkdir($OUTPUT_PATH);

$query = "SELECT * FROM collectibles;";
$result = $db->query($query);
if ($result === false) exit("Failed to load collectible data! \n" . $db->error . "\n");

$bookImages = array();
$rowCount = 0;

while (($row = $result->fetch_assoc())) 
{
	if ($row['icon']) $bookImages[] = $row['icon'];
	++$rowCount;
}

print("\tLoaded $rowCount collectibles...\n");

$bookImages = array_unique($bookImages);

$imageCount = count($bookImages);
print("\tFound $imageCount unique icons...\n");
print("\tCopying images...\n");

foreach ($bookImages as $image)
{
	$image = $ICON_BASEPATH . str_replace(".dds", ".png", $image);
	$baseFilename = basename($image);
	$dest = $OUTPUT_PATH . $baseFilename;
	
	copy($image, $dest);	
}