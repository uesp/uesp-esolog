<?php

if (php_sapi_name() != "cli") die("Can only be run from command line!");

require("/home/uesp/secrets/esolog.secrets");
require("esoCommon.php");

class CEsoUpdateLocTimes 
{
	const SHOW_PARSE_LINENUMBERS = true;
	const ELP_POSITION_FACTOR = 1000;
	const UPDATE_LINE_COUNT = 10000;
	const ERROR_LINE_COUNT = 50000;
	
	//public $LOG_PATH = "/home/uesp/esolog/";
	public $LOG_PATH = "/home/uesp/oldesolog/";
	public $START_LOG_INDEX = 44126;
	public $END_LOG_INDEX = 10000000;
	
	public $db = null;
	public $lastQuery = "";
	public $files = [];
	
	public $currentParseLine = 0;
	public $currentLine = "";
	public $errorCounts = [];
	public $totalEntryCount = 0;
	public $totalErrorCount = 0;
	
	
	public function __construct()
	{
		global $uespEsoLogWriteDBHost;
		global $uespEsoLogWriteUser;
		global $uespEsoLogWritePW;
		global $uespEsoLogDatabase;
		
		$this->db = new mysqli($uespEsoLogWriteDBHost, $uespEsoLogWriteUser, $uespEsoLogWritePW, $uespEsoLogDatabase);
		if ($this->db === null || $this->db->connect_error) return die("ERROR: Could not connect to mysql database!");
	}
	
	
	public function DumpErrorSummary()
	{
		foreach ($this->errorCounts as $type => $count)
		{
			print("\t\t\t$type = $count\n");
		}
	}
	
	
	public function ReportParseError ($type)
	{
		$this->errorCounts[$type]++;
		return false;
	}
	
	
	public function ReportError ($errorMsg)
	{
		print($errorMsg . "\n");
		
		if ($this->db != null && $this->db->error)
		{
			print("\tDB Error:" . $this->db->error);
			print("\tLast Query:" . $this->lastQuery);
		}
		return false;
	}
	
	
	public function MakeNiceName($name)
	{
		if ($name == null) return "";
		
		$name = preg_replace('/\|c[0-9a-fA-F]{6}/', '', $name);
		$name = str_replace('|r', '', $name);
		$name = preg_replace('/\^.*$/', '', $name);
		
		return $name;
	}
	
	
	public function LoadLocation($logEntry, $locType, $idField = null, $id = null)
	{
		if ($logEntry['x'] == null || $logEntry['y'] == null) return $this->ReportParseError("null location");
		
		$zone = $this->MakeNiceName($logEntry['zone']);
		if ($zone == null || $zone == "") return $this->ReportParseError("null zone");
		
		$rawX = floatval($logEntry['x']);
		$rawY = floatval($logEntry['y']);
		if ($rawX == 0 && $rawY == 0) return $this->ReportParseError("zero location");;
		
		$safeZone = $this->db->real_escape_string($zone);
		$safeId = $this->db->real_escape_string($id);
		$safeType = $this->db->real_escape_string($locType);
		$x = (int) ($rawX * self::ELP_POSITION_FACTOR);
		$y = (int) ($rawY * self::ELP_POSITION_FACTOR);
		
		if ($idField == null || $id == null)
			$this->lastQuery = "SELECT * FROM location WHERE  type='$safeType' AND zone='$safeZone' AND x='$x' AND y='$y' ;";
		else
			$this->lastQuery = "SELECT * FROM location WHERE `$idField`='$safeId' AND type='$safeType' AND zone='$safeZone' AND x='$x' AND y='$y' ;";
		//print("\t\t\t{$this->lastQuery}\n");
		
		$result = $this->db->query($this->lastQuery);
		if (!$result) return $this->ReportParseError("location query error");
		
		$row = $result->fetch_assoc();
		if (!$row) return $this->ReportParseError("no locations found");
		
		return $row;
	}
	
	
	public function LoadRecord($table, $idField, $id)
	{
			//Note: Assumes safe input for table/idField
		$safeId = $this->db->real_escape_string($id);
		$this->lastQuery = "SELECT * FROM $table WHERE `$idField`='$safeId';";
		
		$result = $this->db->query($this->lastQuery);
		if ($result === false) return $this->ReportParseError("$table query error");
		
		$row = $result->fetch_assoc();
		if (!$row) return $this->ReportParseError("$table no record");
		
		return $row;
	}
	
	
	public function LoadRecord2($table, $idField, $id, $idField2, $id2)
	{
			//Note: Assumes safe input for table/idField
		$safeId = $this->db->real_escape_string($id);
		$safeId2 = $this->db->real_escape_string($id2);
		$this->lastQuery = "SELECT * FROM $table WHERE `$idField`='$safeId' AND `$idField2`='$safeId2';";
		
		$result = $this->db->query($this->lastQuery);
		if ($result === false) return $this->ReportParseError("$table query error");
		
		$row = $result->fetch_assoc();
		if (!$row) return $this->ReportParseError("$table no record");
		
		return $row;
	}
	
	
	public function LoadRecord3($table, $idField, $id, $idField2, $id2, $idField3, $id3)
	{
			//Note: Assumes safe input for table/idField
		$safeId = $this->db->real_escape_string($id);
		$safeId2 = $this->db->real_escape_string($id2);
		$safeId3 = $this->db->real_escape_string($id3);
		$this->lastQuery = "SELECT * FROM $table WHERE `$idField`='$safeId' AND `$idField2`='$safeId2' AND `$idField3`='$safeId3';";
		
		$result = $this->db->query($this->lastQuery);
		if ($result === false) return $this->ReportParseError("$table query error");
		
		$row = $result->fetch_assoc();
		if (!$row) return $this->ReportParseError("$table no record");
		
		return $row;
	}
	
	
	public function GetLogTime ($logEntry)
	{
			//timeStamp1{1609374464}
			//logTime{1609374599}
		if ($logEntry['timeStamp1']) return (int) $logEntry['timeStamp1'];
		if ($logEntry['logTime']) return (int) $logEntry['logTime'];
		return 0;
	}
	
	
	public function UpdateLocation($location, $logTime)
	{
		$id = $location['id'];
		$firstTime = (int) $location['firstTime'];
		$lastTime = (int) $location['lastTime'];
		$doUpdate = false;
		
		if ($firstTime <= 0 || $logTime < $firstTime)
		{
			$firstTime = $logTime;
			$doUpdate = true;
		}
		
		if ($lastTime <= 0 || $logTime > $lastTime)
		{
			$lastTime = $logTime;
			$doUpdate = true;
		}
		
		if ($doUpdate)
		{
			$this->lastQuery = "UPDATE location SET firstTime='$firstTime', lastTime='$lastTime' WHERE id='$id';";
			//print($this->lastQuery . "\n");
			
			$result = $this->db->query($this->lastQuery);
			
			if (!$result)
			{
				//$this->ReportError("Update Error");
				return $this->ReportParseError("location update query error");
			}
		}
		
		return true;
	}
	
	
	public function OnShowBook($logEntry)
	{
		$logTime = $this->GetLogTime($logEntry);
		if ($logTime <= 0) return $this->ReportParseError("no logtime");
		
		$bookTitle = $this->MakeNiceName($logEntry['bookTitle']);
		$bookId = $logEntry['bookId'];
		$bookRecord = null;
		
		if ($bookId != null) 
			$bookRecord = $this->LoadRecord("book", "bookId", $bookId);
		else if ($bookTitle != null)
			$bookRecord = $this->LoadRecord("book", "title", $bookTitle);
		else
			return $this->ReportParseError("no book id");
		
		if (!$bookRecord) return false;
		
		$loc = $this->LoadLocation($logEntry, "book", "bookId", $bookRecord['id']);
		if (!$loc) return false;
		
		return $this->UpdateLocation($loc, $logTime);
	}
	
	
	public function OnTargetChange($logEntry)
	{
		//print("\t\t\tTargetChange: {$name}\n");
		
		$logTime = $this->GetLogTime($logEntry);
		if ($logTime <= 0) return $this->ReportParseError("no logtime");
		
		$name = $this->MakeNiceName($logEntry['name']);
		$npc = $this->LoadRecord("npc", "name", $name);
		if (!$npc) return false;
		
		//print("\t\t\t\tFound NPC!\n");
		
		$loc = $this->LoadLocation($logEntry, "npc", "npcId", $npc['id']);
		if (!$loc) return false;
		
		//print("\t\t\t\tFound NPC Location!\n");
		
		return $this->UpdateLocation($loc, $logTime);
	}
	
	public function OnSkyshard($logEntry)
	{
		$logTime = $this->GetLogTime($logEntry);
		if ($logTime <= 0) return $this->ReportParseError("no logtime");
		
		$loc = $this->LoadLocation($logEntry, "skyshard");
		if (!$loc) return false;
		
		return $this->UpdateLocation($loc, $logTime);
	}
	
	
	public function OnFoundTreasure ($logEntry)
	{
		$logTime = $this->GetLogTime($logEntry);
		if ($logTime <= 0) return $this->ReportParseError("no logtime");
		
		$loc = $this->LoadLocation($logEntry, "treasure");
		if (!$loc) return false;
		
		return $this->UpdateLocation($loc, $logTime);
	}
	
	
	public function OnFish ($logEntry)
	{
		$logTime = $this->GetLogTime($logEntry);
		if ($logTime <= 0) return $this->ReportParseError("no logtime");
		
		$loc = $this->LoadLocation($logEntry, "fish");
		if (!$loc) return false;
		
		return $this->UpdateLocation($loc, $logTime);
	}
	
	
	public function startsWith($haystack, $needle)
	{
		return $needle === "" || strpos($haystack, $needle) === 0;
	}
	
	
	public function OnLootGainedEntry ($logEntry)
	{
		$name = $logEntry['itemName'];
		if ($name == null) return $this->ReportParseError("no item name");
		$name = MakeEsoTitleCaseName($name);
		
		$itemLink = $logEntry['itemLink'];
		
		if (!$this->startsWith($itemLink, "|H")) 
			$itemRecord = $this->LoadRecord("item", "name", $name);
		else
			$itemRecord = $this->LoadRecord("item", "link", $itemLink);
		
		if (!$itemRecord) return false;
		
		$logTime = $this->GetLogTime($logEntry);
		if ($logTime <= 0) return $this->ReportParseError("no logtime");
		
		$loc = $this->LoadLocation($logEntry, "item", "itemId", $itemRecord['id']);
		if (!$loc) return false;
		
		return $this->UpdateLocation($loc, $logTime);
	}
	
	
	public function OnQuestStart ($logEntry)
	{
		$questRecord = $this->LoadRecord2("quest", "name", $logEntry['quest'], "uniqueId", $logEntry['uniqueId']);
		if (!$questRecord) return false;
		
		$loc = $this->LoadLocation2($logEntry, "quest", "questId", $questRecord['id'], 'questStageId', -1);
		if (!$loc) return false;
		
		return $this->UpdateLocation($loc, $logTime);
	}
	
	
	public function OnQuestStep ($logEntry)
	{
		$questRecord = $this->LoadRecord2("quest", "name", $logEntry['quest'], "uniqueId", $logEntry['uniqueId']);
		if (!$questRecord) return false;
		
		$questStageRecord = $this->LoadRecord3("questStep", "questId", $questRecord['id'], "stageIndex", $logEntry['stageIndex'], "stepIndex", $logEntry['step']);
		if (!$questStageRecord) return false;
		
		$loc = $this->LoadLocation2($logEntry, "quest", "questId", $questRecord['id'], 'questStageId', $questStageRecord['id']);
		if (!$loc) return false;
		
		return $this->UpdateLocation($loc, $logTime);
	}
	
	
	public function HandleLogEntry ($logEntry)
	{
		if (!$this->CheckLanguage($logEntry)) return $this->ReportParseError("bad language");
		
		switch ($logEntry['event'])
		{
			case "TargetChange": 	return $this->OnTargetChange($logEntry);
			case "ShowBook":		return $this->OnShowBook($logEntry);
			case "Skyshard":		return $this->OnSkyshard($logEntry);
			case "FoundTreasure":	return $this->OnFoundTreasure($logEntry);
			case "Fish":			return $this->OnFish($logEntry);
			case "LootGained":		return $this->OnLootGainedEntry($logEntry);
			case "QuestStart":		return $this->OnQuestStart($logEntry);
			case "QuestStep":		return $this->OnQuestStep($logEntry);
		}
		
		return $this->ReportParseError("unknown log type");
	}
	
	
	public function CheckLanguage ($logEntry)
	{
		$language = $logEntry['lang'];
		
		//print($this->currentLine . "\n");
		
		if ($language == null) return true;
		if ($language != "en") return false;
		
		return true;
	}
	
	
	public function ParseLogEntry ($logString)
	{
		$matchData = array();
		$resultData = array();
		
		$result = preg_match_all("|([a-zA-Z0-9_]+){(.*?)}  |s", $logString, $matchData);
		
		if ($result === 0)
		{
			$this->ReportError("\t\t{$this->$currentLine}: Failed to find any matches for log entry: " . $logString);
			return null;
		}
		
		foreach ($matchData[1] as $key => $value)
		{
			$resultData[$value] = $matchData[2][$key];
		}
		
		//$this->prepareLogEntry($resultData, $logString);
		
		return $resultData;
	}
	
	
	public function ParseLogFile($logFilename, $fileContent = null)
	{
		print("\tParsing log file $logFilename...\n");
		
		if ($fileContent == null)
		{
			$fileData = file_get_contents($logFilename);
			if ($fileData == null) return $this->ReportError("\tError: Failed to load the log file '$logFilename'!");
		}
		else
		{
			$fileData = $fileContent;
		}
		
		$logEntries = array();
		$entryCount = 0;
		$errorCount = 0;
		$totalLineCount = 0;
		$nextLineUpdate = self::UPDATE_LINE_COUNT;
		$nextErrrorUpdate = self::ERROR_LINE_COUNT;
		
		$result = preg_match_all('|(event{.*?end{}  )|s', $fileData, $logEntries);
		if ($result === 0) return $this->reportError("\tError: Failed to find any log entries in file '{$logFilename}'!");
		
		$parseStartTime = microtime(true);
		$parseEndTime = 0;
		$parseLogCount = 0;
		
		foreach ($logEntries[1] as $key => $logText)
		{
			$lineCount = substr_count($logText, "\n") + 1;
			$totalLineCount += $lineCount;
			$this->currentParseLine = $totalLineCount;
			$this->currentLine = $logText;
			++$parseLogCount;
			
			$entryLog = $this->ParseLogEntry($logText);
			++$entryCount;
			++$this->totalEntryCount;
			
			if (!$this->HandleLogEntry($entryLog))
			{
				//print("\t\t$totalLineCount: Failed to handle log entry!\n");
				++$errorCount;
				++$this->totalErrorCount;
			}
			
			if ($totalLineCount >= $nextLineUpdate && self::SHOW_PARSE_LINENUMBERS)
			{
				$parseEndTime = microtime(true);
				$parseRate = 0;
				$parseRateText = '';
				
				if ($parseLogCount > 0 && $parseEndTime > $parseStartTime)
				{
					$parseRate = ($parseLogCount / ($parseEndTime - $parseStartTime));
					$parseRateText = "" . number_format($parseRate, 1) . " lines/sec";
				}
				
				$parseStartTime = $parseEndTime;
				$parseLogCount = 0;
				
				print("\t\tParsing line $totalLineCount ($parseRateText)...\n");
				$nextLineUpdate += self::UPDATE_LINE_COUNT;
			}
			
			if ($totalLineCount >= $nextErrrorUpdate && self::SHOW_PARSE_LINENUMBERS)
			{
				$updates = $this->totalEntryCount - $this->totalErrorCount;
				print("\t\tError Summary ($updates location updates, {$this->totalErrorCount} errors out of {$this->totalEntryCount} entries):\n");
				$this->DumpErrorSummary();
				$nextErrrorUpdate += self::ERROR_LINE_COUNT;
			}
		}
		
		return true;
	}
	
	
	public function ParseCompressedLogFile($logFilename)
	{
		//print("\tParsing compressed log file $logFilename...\n");
		$uncompressedFile = gzdecode(file_get_contents($logFilename));
		
		return $this->ParseLogFile($logFilename, $uncompressedFile);
	}
	
	
	public function DoLogParse()
	{
		print("Looking for ESO log files in '{$this->LOG_PATH}'...\n");
		
		$this->files = glob($this->LOG_PATH . "eso*.log*");
		$count = count($this->files);
		
		natsort($this->files);
		print("\tFound $count log files!\n");
		
		foreach ($this->files as $file)
		{
			$result = preg_match("#{$this->LOG_PATH}eso(\d+)\.log#", $file, $matches);
			
			if (!$result)
			{
				$this->ReportError("\tWarning: No match for log file $file!");
				continue;
			}
			
			$logIndex = (int) $matches[1];
			
			if ($logIndex <= 0 || $logIndex < $this->START_LOG_INDEX || $logIndex > $this->END_LOG_INDEX)
			{
				//$this->ReportError("\tSkipping log file $file!");
				continue;
			}
			
			if (preg_match("#{$this->LOG_PATH}eso\d+\.log.gz#", $file))
			{
				$this->ParseCompressedLogFile($file);
			}
			else if (preg_match("#{$this->LOG_PATH}eso\d+\.log#", $file))
			{
				$this->ParseLogFile($file);
			}
			else
			{
				$this->ReportError("\tWarning: No match for log file $file!");
			}
		}
		
		$updates = $this->totalEntryCount - $this->totalErrorCount;
		print("\t\tError Summary ($updates location updates, {$this->totalErrorCount} errors out of {$this->totalEntryCount} entries):\n");
		$this->DumpErrorSummary();
		$nextErrrorUpdate += self::ERROR_LINE_COUNT;
	}
	
};


$parse = new CEsoUpdateLocTimes();
$parse->DoLogParse();


