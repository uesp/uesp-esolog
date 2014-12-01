<?php
/*
 * dumpMinedItems.php - by Dave Humphrey (dave@uesp.net), 27 Nov 2014
 * 
 * Very basic web script to dump the minedItem data from the database
 * to a text/CSV format.
 */

	// Database users, passwords and other secrets
require("/home/uesp/secrets/esolog.secrets");


	// Global objects
$db = null;
$inputParams = array();
$SELECT_LIMIT = 1000;


function ReportError($errorMsg)
{
	print($errorMsg);
	error_log($errorMsg);
	return false;
}


function InitDatabase()
{
	global $uespEsoLogReadDBHost, $uespEsoLogReadUser, $uespEsoLogReadPW, $uespEsoLogDatabase;
	global $db;
	
	$db = new mysqli($uespEsoLogReadDBHost, $uespEsoLogReadUser, $uespEsoLogReadPW, $uespEsoLogDatabase);
	if ($db->connect_error) return ReportError("ERROR: Could not connect to mysql database!");
	
	return true;
}


function ParseInputPrameters()
{
	global $inputParams;
	global $argv;
	
	$inputParams['itemid'] = 0;
	$inputParams['sort'] = "";
	
		// Add command line arguments to input parameters for testing
	if ($argv !== null)
	{
		foreach ($argv as $arg)
		{
			$e = explode("=", $arg);
	
			if(count($e) == 2)
				$inputParams[$e[0]] = $e[1];
			else
				$inputParams[$e[0]] = 0;
		}
	}
	
	if (array_key_exists('itemid', $_REQUEST))
	{
		$inputParams['itemid'] = intval($_REQUEST['itemid']);
	}
	
	if (array_key_exists('sort', $_REQUEST))
	{
		$matches = array();
		$result = preg_match("|^([a-zA-Z0-9]+)|s", $_REQUEST['sort'], $matches);
		if ($result) $inputParams['sort'] = $matches[1];
	}
	
	return true;
}


function OutputHtmlHeader()
{
	header("Expires: 0");
	header("Pragma: no-cache");
	header("Cache-Control: no-cache, no-store, must-revalidate");
	header("Pragma: no-cache");
	header("content-type: text/plain");
}


function InitProgram()
{
	error_reporting(E_ALL);
	OutputHtmlHeader();
	
	if (!InitDatabase()) return false;
	if (!ParseInputPrameters()) return false;
	
	return true;
}


function LoadRecords()
{
	global $db;
	global $inputParams;
	global $SELECT_LIMIT;
	
	$itemId = $inputParams['itemid'];
	$sort = $inputParams['sort'];

	if ($itemId <= 0) return ReportError("ERROR: No itemid specified!");
	
	$query = "SELECT * FROM minedItem WHERE itemId=$itemId";
	if ($sort != "") $query .= " ORDER BY $sort";
	$query .= " LIMIT $SELECT_LIMIT";
	
	$result = $db->query($query);
	if (!$result) return false;
	
	$records = array();
	if ($result->num_rows === 0) return $records;
	
	$result->data_seek(0);
	
	while (($row = $result->fetch_assoc()))
	{
		$records[] = $row;
	}
	
	return $records;
}



function OutputRecords($records)
{
	print("id, itemid, level, quality, value, intlevel, intsubtype, weaponPower, armorRating\n");
	
	foreach ($records as $key => $value)
	{
		print("${value['id']}, ${value['itemId']}, ${value['level']}, ${value['quality']}, ${value['value']}, ${value['internalLevel']}, ${value['internalSubtype']}, ${value['weaponPower']}, ${value['armorRating']},\n");
	}
	
}


// 	Begin main program
function DoMain()
{
	if (!InitProgram()) return false;
	$records = LoadRecords();
	if (!$records) return false;
	
	OutputRecords($records);
}


DoMain();

?>
