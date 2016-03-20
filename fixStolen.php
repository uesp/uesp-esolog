<?php 

if (php_sapi_name() != "cli") die("Can only be run from command line!");

require("/home/uesp/secrets/esolog.secrets");

$LOG_PATH = "/home/uesp/esolog";

$userData = array();

$START_LOG_INDEX = 1486;
$END_LOG_INDEX = 2361;
$END_LOG_INDEX = 1536;

$START_LOG_INDEX = 1491;
$END_LOG_INDEX = 2361;

$ELP_THIEVESTROVE_LASTFIXTIMESTAMP = 4743900596690485248;
$TREASURE_DELTA_TIME = 4000;

for ($i = $START_LOG_INDEX; $i <= $END_LOG_INDEX; ++$i)
{
	$fileIndex = sprintf("%05d", $i);
	$logFilename = "$LOG_PATH/eso$fileIndex.log";
	
	printf("Loading log file $logFilename...\n");
	
	$fileData = file_get_contents($logFilename);
	
	if ($fileData == null) 
	{
		print("\tError loading file!\n");
		continue;
	}
	
	if (strlen($fileData) === 0) continue;
	
	$logEntries = array();
	$result = preg_match_all('|(event{.*?end{}  )|s', $fileData, $logEntries);
	
	foreach ($logEntries[1] as $key => $value)
	{
		$matchData = array();
		$result = preg_match_all("|([a-zA-Z0-9_]+){(.*?)}  |s", $value, $matchData);
		
		if ($result === 0)
		{
			printf("\t$index: Error parsing line!\n");
			printf("$line");
			continue;
		}
		
		$resultData = array();
		
		foreach ($matchData[1] as $key => $value)
		{
			$resultData[$value] = $matchData[2][$key];
		}
		
		$userName = $resultData['userName'];
				
		if ($userData[$userName] == null)
		{
			$userData[$userName] = array();
			$userData[$userName]['itemsStolen'] = 0;
			$userData[$userName]['trovesFound'] = 0;
			$userData[$userName]['__lastTroveFoundGameTime'] = 0;
		}
		
		$thisUser = &$userData[$userName];
		
		if ($resultData['event'] == 'LootGained' && $resultData['rvcType'] == "stolen")
		{
			++$thisUser['itemsStolen'];
		}
		else if ($resultData['event'] == 'Stolen')
		{
			++$thisUser['itemsStolen'];
		}
		else if ($resultData['lastTarget'] == 'Thieves Trove' && $resultData['timeStamp'] < $ELP_THIEVESTROVE_LASTFIXTIMESTAMP)
		{
			$diff = $resultData['gameTime'] - $thisUser['__lastTroveFoundGameTime'];
			
			if ($diff >= $TREASURE_DELTA_TIME || $diff < 0)
			{
				//printf("\tFound Trove...\n");
				$thisUser['__lastTroveFoundGameTime'] = $resultData['gameTime'];
				++$thisUser['trovesFound'];
			}
		}
	}
}

$db = new mysqli($uespEsoLogWriteDBHost, $uespEsoLogWriteUser, $uespEsoLogWritePW, $uespEsoLogDatabase);
if ($db->connect_error) exit("Could not connect to mysql database!");

foreach ($userData as $name => $user)
{
	if ($user['itemsStolen'] == 0 && $user['trovesFound'] == 0) continue;
	
	$itemsStolen = $user['itemsStolen'];
	$trovesFound = $user['trovesFound'];
	
	printf("\t$name: $itemsStolen stolen, $trovesFound troves\n");
	
	$query = "UPDATE user SET trovesFound=trovesFound+$trovesFound, itemsStolen=itemsStolen+$itemsStolen WHERE name='$name';";
	$result = $db->query($query);
	if (!$result) printf("\tError updating user $name!\n\t".$db->error."\n");
}