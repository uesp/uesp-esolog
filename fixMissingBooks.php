<?php 

if (php_sapi_name() != "cli") die("Can only be run from command line!");

require("/home/uesp/secrets/esolog.secrets");
require("../BookTitles.php");

$db = new mysqli($uespEsoLogWriteDBHost, $uespEsoLogWriteUser, $uespEsoLogWritePW, $uespEsoLogDatabase);
if ($db->connect_error) exit("Could not connect to mysql database!");

foreach ($BOOK_TITLES as $bookId => $bookTitle)
{
	$safeTitle = $db->real_escape_string($bookTitle);
	$result = $db->query("SELECT * FROM book WHERE bookId='$bookId';");
	if ($result === false) exit("Failed to find bookId $bookId!");
	
	if ($result->num_rows == 0)
	{
		print("Adding missing book $bookTitle!\n");
		
		$writeResult = $db->query("INSERT INTO book SET bookId='$bookId', title='$safeTitle';\n");
		if ($result === false) print("Error creating bookId $bookId!\n");
	}
}
