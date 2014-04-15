<?php

$outputOutputFile = null;
$OUTPUTPATH = "/home/uesp/www/esolog/log/fix/";

if (php_sapi_name() != "cli") die("Can only be run from command line!");


function ParseEntireLog ($logFilename)
{
	//log("Parsing entire log file $logFilename...");
	//$currentParseFile = $logFilename;
	//$currentParseLine = 0;
	
	$logEntries = array();
	$entryCount = 0;
	$errorCount = 0;

	$fileData = file_get_contents($logFilename);
	if ($fileData == null) return false;

	if (strlen($fileData) === 0) return true;

	$result = preg_match_all('|(event{.*?end{}  )|s', $fileData, $logEntries);
	if ($result === 0) return false;

	$totalLineCount = 0;

	foreach ($logEntries[1] as $key => $value)
	{
		$lineCount = substr_count($value, "\n") + 1;
		$totalLineCount += $lineCount;
		//$currentParseLine = $totalLineCount;
			
		$entryLog = ParseLogEntry($value);
			
		//if (!HandleLogEntry($entryLog))
		//{
			//++$errorCount;
		//}
			
		++$entryCount;
	}

	//log("Parsed {$entryCount} log entries from file '{$logFilename}'.");
	//log("Found {$errorCount} entries with errors.");
	//log("Skipped {$duplicateCount} duplicate log entries.");
	return TRUE;
}


function IsBadChar($char)
{
	$ch = ord($char);
	return ($ch > 128 && $ch != 0x94 && $ch != 0xE2 && $ch != 0xA6) || ($ch < 32 && $ch != 0x0D && $ch != 0x0A && $ch != 7 && $ch != 9);
}


function HasBadCharsAtEnd($string)
{
	$stringLen = strlen($string);
	
	for ($i = $stringLen - 1; $i >= 0; --$i)
	{
		if (IsBadChar($string[$i])) return true;
	}
	
	return false;
}


function FindFirstBadChar($string)
{
	$logStringLen = strlen($string);
	
	for ($i = 0; $i < $logStringLen; ++$i)
	{
		if (IsBadChar($string[$i])) return $i;
	}
	
	return $logStringLen;
}


function FixEntryPlain($logString)
{
	$log64 = base64_encode($logString);
	$logStringLen = strlen($logString);
	$firstBadChar = FindFirstBadChar($logString);
	
	if ($firstBadChar >= $logStringLen)
	{
		print("\tNo bad characters found in string..skipping fix!\n");
		return $logString;
	}
	
	$estPos = (int) ($firstBadChar * 4 / 3);
	
	if ($log64[$estPos] == 'K')
		$new64 = substr_replace($log64, '+', $estPos, 0);
	elseif ($log64[$estPos-1] == 'I' && $log64[$estPos] == 'C' && $log64[$estPos+1] == 'A')
		$new64 = substr_replace($log64, '+', $estPos-1, 0);
	else
		$new64 = substr_replace($log64, '+', $estPos + 1, 0);
	
	$newLogEntry= base64_decode($new64);
	
	//print("\tIndex: $firstBadChar / $estPos / $posMod / $lengthMod\n");
	print("\tOld64: $log64\n");
	print("\tNew64: $new64\n");
	
	return $newLogEntry;
}


function endsWith($haystack, $needle)
{
	return $needle === "" || substr($haystack, -strlen($needle)) === $needle;
}


function FixEntry($logString)
{
	global $currentOutputFile;
	
	$matches = array();
	preg_match('|(ipAddress.*)|', $logString, $matches);
	$newLogString = preg_replace('|(ipAddress.*)|', '', $logString);
	$counter = 0;
	
	do {
		$newLogEntry = FixEntryPlain($newLogString);
		$newLogString = $newLogEntry;
		++$counter;
	} while ($counter < 10 && HasBadCharsAtEnd($newLogEntry));
	
	//$posMod = $estPos % 4;
	//$lengthMod = strlen($log64) % 4;
	//print("\tIndex: $firstBadChar / $estPos / $posMod / $lengthMod\n");
	//print("\tOld64: $log64\n");
	//print("\tNew64: $new64\n");
	
	if (endsWith($newLogEntry, "end{}  "))
	{
		$newLogEntry = substr($newLogEntry, 0, -7);
	}
	
	if (!endsWith($newLogEntry, "  "))
	{
		if (endsWith($newLogEntry, " "))
			$newLogEntry .= " ";
		else
			$newLogEntry .= "  ";
	}
	
	$newLogEntry .= $matches[0];
	print("\tFix Count: $counter\n");
	print("\tNewLog: $newLogEntry\n");
	
	fwrite($currentOutputFile, $newLogEntry . "\n");
	return true;
}


function ParseLogEntry ($logString)
{
	global $currentOutputFile;
	
	$matchData = array();
	$resultData = array();

	$result = preg_match_all("|([a-zA-Z]+){(.*?)}  |s", $logString, $matchData);
	
	if ($result === 0)
	{
		fwrite($currentOutputFile, $logString . "\n");
		return null;
	}
	
	foreach ($matchData[1] as $key => $value)
	{
		$resultData[$value] = $matchData[2][$key];
	}
	
	if (!array_key_exists('ipAddress', $resultData))
	{
		print("Found broken entry for event{{$resultData['event']}}\n");
		FixEntry($logString);
		return null;
	}
	
	if ($resultData['event'] == 'ShowBook' && $resultData['bookTitle'] == null)
	{
		print("Found broken entry for event{{$resultData['event']}}\n");
		FixEntry($logString);
		return null;
	} 
	
	fwrite($currentOutputFile, $logString . "\n");
	//prepareLogEntry($resultData, $logString);
	return $resultData;
}



function ParseAllLogs($path)
{
	global $OUTPUTPATH;
	global $currentOutputFile;
	
	$files = glob($path . "eso*.log");
	
	foreach ($files as $key => $value)
	{
		$path_parts = pathinfo($value);
		$currentOutputFilename = $OUTPUTPATH . $path_parts['basename'];
		print("Parsing log $value...\n");
		print("Saving to $currentOutputFilename...\n");
		
		$currentOutputFile = fopen( $currentOutputFilename, "wb");
		
		ParseEntireLog($value);
		
		fclose($currentOutputFile);
	}
	
	return true;
}

//print(HasBadCharsAtEnd("asdsadasdsad\n"));
ParseAllLogs("/home/uesp/www/esolog/log/");
?>