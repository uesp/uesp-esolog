<?php


require_once('/home/uesp/www/esobuilddata/parseBuildData.class.php');
require_once('/home/uesp/www/esobuilddata/parseCharData.class.php');


class EsoLogSubmitter
{
	
	const ESOLOG_UPLOAD_PATH = "/home/uesp/esolog/upload";
	const ESOLOG_OUTPUT_PATH = "/home/uesp/esolog/";
	const ELC_INDEX_FILENAME = "/home/uesp/esolog/esolog.index";
	
	public $parseBuildData = null;
	public $parseCharData = null;
	public $fileData = "";
	public $fileError = 0;
	public $fileErrorMsg = "";
	public $fileSize = 0;
	public $fileTmpName = "";
	public $fileMoveName = "";
	public $fileName = "";
	public $hasFileData = false;
	public $parsedRecords = 0;
	public $accountName = "Anonymous";
	public $uploadedBuilds = 0;
	public $parsedBuilds = 0;
	public $uploadedCharacters = 0;
	public $parsedCharacters = 0;
	public $wikiUserName = '';
	public $hasCharData = false;
	
	public $currentLogIndex = 1;
	
	
	public function __construct ()
	{
		$this->parseBuildData = new EsoBuildDataParser();
		$this->parseCharData  = new EsoCharDataParser();
	}
	
	
	public function parseFormInput()
	{
		$this->writeHeaders();
		
		$this->inputParams = $_REQUEST;
		
		if (array_key_exists("wikiUserName", $this->inputParams))
		{
			$this->wikiUserName = $this->inputParams['wikiUserName'];
		}
		
		if (array_key_exists("logfile", $_FILES))
		{
			$this->fileName = $_FILES["logfile"]['name'];
			$this->fileSize = $_FILES["logfile"]['size'];
			$this->fileError = $_FILES["logfile"]['error'];
			$this->fileTmpName = $_FILES["logfile"]['tmp_name'];
			$this->hasFileData = true;
			
			switch($this->fileError)
			{
				case UPLOAD_ERR_OK:
					$this->fileErrorMsg = ""; 
					break;
				case UPLOAD_ERR_INI_SIZE:
					$this->fileErrorMsg = "The uploaded file exceeds the upload_max_filesize directive in php.ini.";
					break;
				case UPLOAD_ERR_FORM_SIZE:
					$this->fileErrorMsg = "The uploaded file exceeds the MAX_FILE_SIZE directive that was specified in the HTML form.";
					break;
				case UPLOAD_ERR_PARTIAL:
					$this->fileErrorMsg = "The uploaded file was only partially uploaded.";
					break;
				case UPLOAD_ERR_NO_FILE:
					$this->fileErrorMsg = "No file was uploaded.";
					break;
				case UPLOAD_ERR_NO_TMP_DIR:
					$this->fileErrorMsg = "Missing a temporary folder. Introduced in PHP 4.3.10 and PHP 5.0.3.";
					break;
				case UPLOAD_ERR_CANT_WRITE:
					$this->fileErrorMsg = "Failed to write file to disk. Introduced in PHP 5.1.0.";
					break;
				case UPLOAD_ERR_EXTENSION:
					$this->fileErrorMsg = "Unknown PHP extension error.";
					break;
				default:
					$this->fileErrorMsg = "Unknown error " . $this->fileError . ".";
					break;
			}
		}
		
		return true;
	}
	
	
	public function reportError ($errorMsg)
	{
		//print("Error: " . $errorMsg . "\n");
		
		$this->fileErrorMsg .= $errorMsg . "<br/>";
		$this->fileError = 10;
		
		return false;
	}
	
	
	public function reportWarning ($errorMsg)
	{
		$this->fileErrorMsg .= $errorMsg . "<br/>";
	
		return false;
	}
	
	
	public function outputUpload ()
	{
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
<html>
<head>
	<title>uespLog -- Submit ESO Data</title>
	<meta http-equiv="Content-Type" content="text/html;charset=utf-8" />
	<link rel="stylesheet" href="submit.css" />
</head>
<body>

<table border="0" cellpadding="2" cellspacing="0" id="maintable">
<tr>
	<td>
		<h1>uespLog -- Submitted ESO Data...</h1>
		<br /> &nbsp;
	</td>
</tr><tr>
	<td>
<?php
	$output = "";
	
	if ($this->fileError != 0)
	{
		$output = "<b>Error: Failed to upload file!</b><br />";
		$output .= $this->fileErrorMsg . "<br />";
		$output .= "Error Code: " . $this->fileError . "<br />";
	}
	else
	{
		$output  = "<b>Successfully uploaded file!</b><br />";
		$output .= "Filename: {$this->fileName}<br />";
		$output .= "File Size: {$this->fileSize} bytes<br />";
		$output .= "Local Filename: {$this->fileMoveName}<br />";
		$output .= "Lua Result: {$this->fileLuaResult}<br />";
		$output .= "Parsed Records: {$this->parsedRecords}<br />";
		$output .= "Uploaded Builds: {$this->uploadedBuilds}<br />";
		$output .= "Saved Builds: {$this->parsedBuilds}<br />";
		$output .= "Uploaded Characters: {$this->uploadedCharacters}<br />";
		$output .= "Saved Characters: {$this->parsedCharacters}<br />";
		
	}
	
	print($output);
?>
	</td>
</tr><tr>
	<td>
		<a href="submit.php" style="float: right;">Upload another log file...</a>
	</td>
	
</table>

</body>
</html>
<?php
	}
	
	public function outputForm ()
	{
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
<html>
<head>
	<title>uespLog -- Submit ESO Data</title>
	<meta http-equiv="Content-Type" content="text/html;charset=utf-8" />
	<link rel="stylesheet" href="submit.css" />
</head>
<body>

<form id="submitform" enctype="multipart/form-data" action="submit.php" method="post">

<table border="0" cellpadding="2" cellspacing="0" id="maintable">
<tr>
	<td>
		<h1>uespLog -- Submit ESO Data</h1>
		Use this page to manually submit a log file created by uespLog filled with data from ESO. Note that if you are playing on the
		Windows PC version you can use uespLogMonitor (included with the addon) to automatically upload all logged data.
		<p />
		<ul>
		<li>Choose your uespLog saved variable file. This is usually in your <em>"Documents"</em> folder as one of the following:<br />
			<em style="margin-left: 52px;">Documents\Elder Scrolls Online\live\SavedVariables\uespLog.lua</em><br /> 
			<em style="margin-left: 52px;">Documents\Elder Scrolls Online\liveeu\SavedVariables\uespLog.lua</em> 
		</li>
		<li>Submit file.</li>
		<li>After submitting you can run the command <em>"/uespreset log"</em> in ESO to clear the log data and similarly <em>"/uespreset builddata"</em> for any build data.</li>
		<li>It is safe to submit duplicate files or log entries...the log parser can detect and ignore duplicate entries.</li>
		<li>Any character and build data in the file is also parsed and saved.</li>
		</ul>
		<p />
		Note: Maximum file upload size is 50MB.
		<br /> &nbsp;
	</td>
</tr><tr>
	<td>
		UESP Wiki Username: <input type="text" name="wikiUserName" value="<?= $_COOKIE['uesp_net_wiki5UserName']?>" size="24" /> (optional)
		<br />
		<br />
		<br />
		<input type="hidden" name="MAX_FILE_SIZE" value="51000000" />
		<input type="file" name="logfile" value="Choose File..." />
		<br /> &nbsp;
	</td>
</tr><tr>
	<td>
		<input type="submit" id="submitbutton" />
	</td>
	
</table>

</form>

</body>
</html>
<?php
		return true;
	}
	
	public function output ()
	{
		if ($this->hasFileData)
		{
			$this->processUpload();
			$this->outputUpload();
		}
		else
		{
			$this->outputForm();
		}
	}
	
	
	public function processUpload ()
	{
		if ($this->fileError != 0)
		{
			error_log("upload error:" . $this->fileError);
			return false;
		}
		
		$destFilename = tempnam(self::ESOLOG_UPLOAD_PATH, "uespLog");
		$this->fileMoveName = $destFilename;
		
		$result = move_uploaded_file ($this->fileTmpName, $destFilename);
		if (!$result) return $this->reportError("Failed to move temporary upload file!");

		chmod($destFilename, 0644);
		
		$this->Lua = new Lua();
		
		$result = $this->Lua->include($destFilename);
		$this->fileLuaResult = $result;
		
		$this->readIndexFile();
		$this->parseVarRootLevel($this->Lua->uespLogSavedVars);
	}
	
	
	public function parseVarRootLevel ($object)
	{
		if ($object == null) return $this->reportError("Could not find the root object in the saved LUA variable file!");
		
		foreach ($object as $key => $value)
		{
			$this->parseVarAccountLevel($key, $value);
		}
		
		if ($this->hasCharData)
		{
			$this->parseCharData->saveParsedCharacters();
			$this->uploadedCharacters = $this->parseCharData->characterCount;
			$this->parsedCharacters = $this->parseCharData->savedCharacters;
		}
		
		return TRUE;
	}
	
	
	public function parseVarAccountLevel ($parentName, $object)
	{
		if ($object == null) return $this->reportError("Missing object in the {$parentName} section (account level) of the saved variable file!");
		
		foreach ($object as $key => $value)
		{
			$this->parseVarAccountWideLevel($key, $value);
		}
		
		return TRUE;
	}
	
	
	public function parseVarAccountWideLevel ($parentName, $object)
	{
		if ($object == null) return $this->reportError("Missing object in the {$parentName} section (account wide leve) of the saved variable file!");
		$this->accountName = ltrim($parentName, '@');
		
		foreach ($object as $key => $value)
		{
			$this->parseVarSectionLevel($key, $value);
		}
		
		return TRUE;
	}
	
	
	public function parseVarSectionLevel ($parentName, $object)
	{
		if ($parentName == '$AccountWide')
		{
			if ($object == null) return $this->reportError("Missing object in the {$parentName} section (section level) of the saved variable file!");
		
			$this->parseVarSectionData("all", $object["all"]);
			$this->parseVarSectionData("achievements", $object["achievements"]);
			$this->parseVarSectionData("globals", $object["globals"]);
		
			$this->parseCharacterBuildData($object["buildData"]);
			$this->parseCharacterData($object["bankData"]);
			$this->parseCharacterData($object["craftBagData"]);
		}
		else
		{
			$this->parseCharacterData($object["charData"]);
		}
	
		return TRUE;
	}
	
	
	public function parseCharacterBuildData ($buildData)
	{
			// Older log files won't have a buildData section so its not an error
		if ($buildData == null) return TRUE;
		
			// Empty data sections are removed by the Lua/PHP parser	
		$data = $buildData['data'];
		if ($data == null) return True;
		
		$data['IPAddress'] = $_SERVER["REMOTE_ADDR"];
		$data['UploadTimestamp'] = time();
		$data['WikiUser'] = $this->wikiUserName;
		
		if (!$this->parseBuildData->doParse($data)) 
		{
			return $this->reportError("Failed to parse and save the build data!");
		}
		
		$this->uploadedBuilds = $this->parseBuildData->characterCount;
		$this->parsedBuilds = $this->parseBuildData->newCharacterCount;
		return TRUE;
	}
	
	
	public function parseCharacterData ($charData)
	{
			// Older log files won't have a charData section so its not an error
		if ($charData == null) return TRUE;
		
				// Empty data sections are removed by the Lua/PHP parser
		$data = $charData['data'];
		if ($data == null) return True;
		
		$this->hasCharData = true;
		
		$data['IPAddress'] = $_SERVER["REMOTE_ADDR"];
		$data['UploadTimestamp'] = time();
		$data['WikiUser'] = $this->wikiUserName;
	
		if (!$this->parseCharData->doParse($data))
		{
			return $this->reportError("Failed to parse and save the character data!");
		}
	
		return TRUE;
	}
	
	
	public function parseVarSectionData ($parentName, $object)
	{
			// Empty object arrays will be removed by the Lua/PHP parser
		if ($object == null) return True; 
		
		$data = $object['data'];
		
		if ($data == null) 
		{
			$this->reportWarning("Missing 'data' section in the {$parentName} section (section data) of the saved variable file!");
			return TRUE;
		}
		
		ksort($data);
		$logData = "";
		
		foreach ($data as $key => $value)
		{
			$extraData  = "userName{" . $this->accountName . "}  ";
			$extraData .= "ipAddress{" . $_SERVER["REMOTE_ADDR"] . "}  ";
			$extraData .= "logTime{" . time() . "}  ";
			$extraData .= "end{}  ";
			
			if (substr_compare($value, "#STR#", strlen($str)-5, 5) !== 0)
			{
				$logData .= $value . $extraData . "\n";
			}
			
			++$this->parsedRecords;
		}
		
		$this->outputQueuedData($logData);
		
		return TRUE;
	}
	
	
	public function outputQueuedData ($logData)
	{
		if ($logData === "") return TRUE;
		
		$this->currentLogFilename = self::ESOLOG_OUTPUT_PATH . sprintf( 'eso%05d.log', $this->currentLogIndex);
		$result = file_put_contents($this->currentLogFilename, $logData, FILE_APPEND | LOCK_EX);
	
		if ($result == FALSE) return $this->reportError("Failed to append log data to file!");
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
	
	
	public function writeIndexFile()
	{
		if (file_put_contents(self::ELC_INDEX_FILENAME, (string)$this->currentLogIndex) === FALSE)
		{
			error_log("Failed to write the log index file: " . self::ELC_INDEX_FILENAME);
			return FALSE;
		}
	
		return TRUE;
	}
	
	
	public function writeHeaders ()
	{
		header("Expires: 0");
		header("Pragma: no-cache");
		header("Cache-Control: no-cache, no-store, must-revalidate");
		header("Pragma: no-cache");
		header("content-type: text/html");
	}
	
};


$g_EsoLogSubmitter = new EsoLogSubmitter();
$g_EsoLogSubmitter->parseFormInput();
$g_EsoLogSubmitter->output();


