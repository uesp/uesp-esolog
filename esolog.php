<?php


function endsWith($haystack, $needle)
{
	return $needle === "" || substr($haystack, -strlen($needle)) === $needle;
}


class EsoLogCollector
{
	const ELC_OUTPUT_LOG_PATH = "/home/uesp/esolog/";
	const ELC_INDEX_FILENAME = "/home/uesp/esolog/esolog.index";
	const ELC_MAX_LOG_FILESIZE = 25000000; 	# Maximum desired size of a log file in bytes

	public $currentLogFilename = "";
	public $currentLogIndex = 1;
	public $rawLogData = array();
	public $formResponseErrorMsg = ""; 
	public $contentEncoding = "";
	
	
	public function __construct ()
	{
		$this->readIndexFile();
		$this->currentLogFilename = $this->generateLogFilename($this->currentLogIndex);
	}
	
	
	public function generateLogFilename($index)
	{
		$logFilename = self::ELC_OUTPUT_LOG_PATH . sprintf( 'eso%05d.log', $index);
		return $logFilename;
	}
	
	
	public function findNewLogFilename()
	{
		$pattern = self::ELC_OUTPUT_LOG_PATH . "eso*.log";
		$logFiles = glob($pattern);
		
		$lastIndex = 0;
		
		foreach ($logFiles as $key => $value)
		{
			
			if (preg_match('/eso(.*)\.log/', $value, $matches) === 1)
			{
				$index = (int)$matches[1];
				if ($index > $lastIndex) $lastIndex = $index;
			}
		}
		
		return $lastIndex + 1;
	}
	
	
	public function writeIndexFile()
	{
		if (file_put_contents(self::ELC_INDEX_FILENAME, (string)$this->currentLogIndex) === FALSE)
		{
			error_log("Failed to write the log index file: " . self::ELC_INDEX_FILENAME);
			return FALSE;
		}
		
		return TRUE;
	}
	
	
	public function readIndexFile()
	{
		$index = file_get_contents(self::ELC_INDEX_FILENAME);
		
		if ($index === FALSE)
		{
			$this->currentLogIndex = 1;
			$this->writeIndexFile();
			return FALSE;
		}
		
		$this->currentLogIndex = (int) $index;
		if ($this->currentLogIndex < 0) $this->currentLogIndex = 1;
		
		return TRUE;
	}
	
	
	public function reportError ($errorMsg)
	{
		print("Error: " . $errorMsg . "\n");
		error_log("Error: " . $errorMsg);
		
		return false;
	}
	
	
	public function outputQueuedData ($logData)
	{
		$result = file_put_contents($this->currentLogFilename, $logData . "\n", FILE_APPEND | LOCK_EX);
		
		if ($result == FALSE)
		{
			$this->formResponseErrorMsg = "Failed to append data to the log file!";
			$this->reportError("Failed to append log data to file!");
			return false;
		}
		
		return true;
	}
	
	
	public function getExtraData()
	{
		$extraData  = "ipAddress{" . $_SERVER["REMOTE_ADDR"] . "}  ";
		$extraData .= "logTime{" . time() . "}  ";
		$extraData .= "end{}  ";
		return $extraData;
	}
	
	
	public function parseLogDataItem($logData)
	{
		$newLogData = str_replace(' ', '+', $logData);
		$decodedData = base64_decode($newLogData);
		
		if (endsWith($decodedData, "end{}  "))
		{
			$decodedData = substr($decodedData, 0, -7);
		}
		
		return $this->outputQueuedData($decodedData . $this->getExtraData());
	}
	
	
	public function parseLogData()
	{
		$result = true;
		
		foreach ($this->rawLogData as $key => $value)
		{
			$result &= $this->parseLogDataItem($value) and $result;
		}
		
		return $result;
	}
	
	
	public function createNewLogFile()
	{
		$newIndex = $this->currentLogIndex;
		$numAttempts = 0;
		
		while ($numAttempts < 1000)
		{
			++$newIndex;
			++$numAttempts;
			
			$filename = $this->generateLogFilename($newIndex);
			
			if (!file_exists($filename)) break;
		}
		
		if ($numAttempts >= 1000)
		{
			$this->reportError("ERROR: Failed to generate new log filename!");
			return FALSE;
		}
		
		$this->currentLogIndex = $newIndex;
		$this->currentLogFilename = $this->generateLogFilename($this->currentLogIndex);
		$this->writeIndexFile();
		
		return TRUE;
	}
	
	
	public function checkLogSize()
	{
		$filesize = filesize($this->currentLogFilename);
		if ($filesize === FALSE) return FALSE;
		
		if ($filesize > self::ELC_MAX_LOG_FILESIZE) $this->createNewLogFile();
	}
	
	
	public function parseFormInput()
	{
		$this->writeHeaders();
		
		$this->contentEncoding = $_SERVER["HTTP_CONTENT_ENCODING"];
		$this->inputParams = $_REQUEST;
		
		if (strtolower($this->contentEncoding) == "gzip")
		{
			$raw_post = file_get_contents("php://input");
			$size = strlen($raw_post);
			
			$postData = gzuncompress($raw_post);
			
			if ($postData)
			{  
				parse_str($postData, $this->inputParams);
				//urldecode();
			}			
		}
		
		if (!array_key_exists('log', $this->inputParams)) 
		{
			$this->formResponseErrorMsg = "Failed to find any form input to parse!";
			header('X-PHP-Response-Code: 500', true, 500);
			header('X-Uesp-Error: ' . $this->formResponseErrorMsg, false);
			return $this->reportError($this->formResponseErrorMsg);
		}
		
		$this->rawLogData = $this->inputParams['log'];
		
		print("Found log form data with " . count($this->rawLogData) . " elements to parse.");
		
		$result = $this->parseLogData();
		
		if (!$result) 
		{
			header('X-PHP-Response-Code: 500', true, 500);
			header('X-Uesp-Error: ' . $this->formResponseErrorMsg, false);
		}
		
		$result &= $this->checkLogSize();
		
		return $result;
	}
	
	
	public function writeHeaders ()
	{
		header("Expires: 0");
		header("Pragma: no-cache");
		header("Cache-Control: no-cache, no-store, must-revalidate");
		header("content-type: text/html");
	}
	
};


$g_EsoLogCollector = new EsoLogCollector();
$g_EsoLogCollector->parseFormInput();


?>