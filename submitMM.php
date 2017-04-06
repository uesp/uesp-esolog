<?php



class CEsoMMLogSubmitter
{
	const MM_OUTPUT_PATH = "/home/uesp/esolog/mm/";
	const MM_INDEX_FILENAME = "/home/uesp/esolog/mm/mm.index";
	
	public $fileData = array();
	public $fileErrorMsgs = array();
	public $hasFileData = false;
	public $server = "NA";
	public $wikiUserName = '';
	public $currentLogIndex = 1;
	public $numFilesUploaded = 0;
	public $totalFileSize = 0;
	
	
	public function __construct ()
	{
	}
	
	
	public function parseFormInput()
	{
		$this->writeHeaders();
		
		$this->inputParams = $_REQUEST;
		
		if (array_key_exists("server", $this->inputParams))
		{
			$tmpServer = strtoupper($this->inputParams['server']);
			
			if ($tmpServer == "NA" || $tmpServer == "PC-NA")
				$this->server = "NA";
			else if ($tmpServer == "EU" || $tmpServer == "PC-EU")
				$this->server = "EU";
			else if ($tmpServer == "PTS")
				$this->server = "PTS";
			else
				$this->server = "Other";
		}
		
		if (array_key_exists("wikiUserName", $this->inputParams))
		{
			$this->wikiUserName = $this->inputParams['wikiUserName'];
		}
		
		if (array_key_exists("mmfile", $_FILES))
		{
			$fileData = $_FILES["mmfile"];
			$totalFiles = count($fileData['name']);
			$this->reportWarning("Found file data with ".count($totalFiles)." files!");
			
			for ($i = 0; $i < $totalFiles; ++$i)
			{
				if ($fileData['name'][$i] == "") continue;
				$this->reportWarning("Processing file '".$fileData['name'][$i]."'...");
				
				$this->hasFileData = true;
				
				$fileErrorMsg = $this->GetFileErrorMsg($fileData['error'][$i], $fileData['name'][$i]);
				if ($fileErrorMsg) $this->fileErrorMsgs[] = $fileErrorMsg;
				
				$newFileData = array();
				$newFileData['name'] = $fileData['name'][$i];
				$newFileData['tmp_name'] = $fileData['tmp_name'][$i];
				$newFileData['size'] = $fileData['size'][$i];
				$newFileData['type'] = $fileData['type'][$i];
				$newFileData['error'] = $fileData['error'][$i];
				$newFileData['errorMsg'] = $fileErrorMsg;
				
				$this->fileData[] = $newFileData;
			}
		}
		
		return true;
	}
	
	
	public function GetFileErrorMsg ($fileError, $filename)
	{
		switch($fileError)
		{
			case UPLOAD_ERR_OK:
				$fileErrorMsg = "";
				break;
			case UPLOAD_ERR_INI_SIZE:
				$fileErrorMsg = "The uploaded file '$filename' exceeds the upload_max_filesize directive in php.ini.";
				break;
			case UPLOAD_ERR_FORM_SIZE:
				$fileErrorMsg = "The uploaded file '$filename' exceeds the MAX_FILE_SIZE directive that was specified in the HTML form.";
				break;
			case UPLOAD_ERR_PARTIAL:
				$fileErrorMsg = "The uploaded file '$filename' was only partially uploaded.";
				break;
			case UPLOAD_ERR_NO_FILE:
				$fileErrorMsg = "No file was uploaded.";
				break;
			case UPLOAD_ERR_NO_TMP_DIR:
				$fileErrorMsg = "Missing a temporary folder to write file '$filename'.";
				break;
			case UPLOAD_ERR_CANT_WRITE:
				$fileErrorMsg = "Failed to write file '$filename' to disk.";
				break;
			case UPLOAD_ERR_EXTENSION:
				$fileErrorMsg = "Unknown PHP extension error.";
				break;
			default:
				$fileErrorMsg = "Unknown error $fileError.";
				break;
		}
		
		return $fileErrorMsg;
	}
	
	
	public function reportError ($errorMsg)
	{
		//print("Error: " . $errorMsg . "\n");
		error_log("Error: " . $errorMsg);
		$this->fileErrorMsgs[] = $errorMsg;
		
		return false;
	}
	
	
	public function reportWarning ($errorMsg)
	{
		error_log("Warning: " . $errorMsg);
		$this->fileErrorMsgs[] = $errorMsg;
	
		return false;
	}
	
	
	public function outputUploads ()
	{
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
<html>
<head>
	<title>uespLog -- Submit MasterMerchant Data</title>
	<meta http-equiv="Content-Type" content="text/html;charset=utf-8" />
	<link rel="stylesheet" href="submit.css" />
</head>
<body>

<table border="0" cellpadding="2" cellspacing="0" id="maintable">
<tr>
	<td>
		<h1>uespLog -- Submitted MasterMerchant Data...</h1>
		<br /> &nbsp;
	</td>
</tr><tr>
	<td>
<?php
	$output = "";
	
	if (count($this->fileErrorMsgs) > 0)
	{
		$output = "<b>Error: Failed to upload one or more files!</b><br />";
		$output .= implode("<br />", $this->fileErrorMsgs). "<p /><br />";
	}
	
	$sizeMB = round($this->totalFileSize/1024.0/1024.0, 1);
	$output  = "<b>Successfully uploaded $this->numFilesUploaded files!</b><br />";
	$output .= "Total Size: {$sizeMB} MB<br />";		
	
	print($output);
?>
	</td>
</tr><tr>
	<td>
		<p><br/>
		Successfully uploaded files will be parsed within 24 hours and can be viewed at <a href='//esosales.uesp.net/'>esosales.uesp.net</a>.
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
	<title>uespLog -- Submit MasterMerchant Data</title>
	<meta http-equiv="Content-Type" content="text/html;charset=utf-8" />
	<link rel="stylesheet" href="submit.css" />
	<script type="text/javascript" src="resources/jquery-1.10.2.js"></script>
	<script type="text/javascript" src="submit.js"></script>
</head>
<body>

<form id="submitform" enctype="multipart/form-data" action="submitMM.php" method="post">

<table border="0" cellpadding="2" cellspacing="0" id="maintable">
<tr>
	<td>
		<h1>uespLog -- Submit MasterMerchant Data</h1>
		
		Submit your MasterMerchant add-on data to the UESP and view the <a href='//esosales.uesp.net/'>collected sales data</a>.
		<p />
		Upload Steps:
		<ul>
			<li>Choose your MasterMerchant saved variable files. These are usually found in your <em>"Documents"</em> folder at:<br />
				<em style="margin-left: 52px;">..\Documents\Elder Scrolls Online\live\SavedVariables\MMXXData.lua</em></li>
			<li>Select all 16 files from <em>MM00Data.lua</em> to <em>MM15Data.lua</em>.</li>
			<li>Select the ESO server where the MM data comes from.</li>
			<li>Submit files.</li>
			<li>You can upload the same MM files multiple times and only new sales will be added to our database.</li>
			<li>You can also use the <a href='//www.esoui.com/downloads/info1257-uespLog.html'>uespLog add-on</a> to automatically download all
					guild trader searches, listed and purchased items.</li>
		</ul>
		<p />
		Note: Maximum file upload size is 50MB.
		<br /> &nbsp;
	</td>
</tr><tr>
	<td>
		<br />
		Select Server:
		<select name="server" />
			<option value="NA" selected">PC-NA</option>
			<option value="EU" selected">PC-EU</option>
			<option value="PTS" selected">PTS</option>
			<option value="Other" selected">Other</option>
		</select>
		<p><br/>
		<input type="hidden" name="wikiUserName" value="<?= $_COOKIE['uesp_net_wiki5UserName']?>"
		<input type="hidden" name="MAX_FILE_SIZE" value="61000000" />
		<input type="file" name="mmfile[]" value="Choose MM Files..." multiple="multiple"/>
		<br /> &nbsp;
	</td>
</tr><tr>
	<td>
		<input type="submit" id="submitbutton" onclick="return OnSubmitEsoMMData();"/>
	</td>
	
</table>

</form>

<div id='esosubmituploadscreen' style='display: none;'>
Uploading Data...
</div>

</body>
</html>
<?php
		return true;
	}
	
	public function output ()
	{
		if ($this->hasFileData)
		{
			$this->processUploads();
			$this->outputUploads();
		}
		else
		{
			$this->outputForm();
		}
	}
	
	
	public function processUploads ()
	{
		$returnValue = true;
		
		$this->readIndexFile();
		
		$logIndex = sprintf("%06d", $this->currentLogIndex);
		$outputPath = self::MM_OUTPUT_PATH . strtolower($this->server) . "/" . $logIndex . "/";
		
		if (!mkdir($outputPath)) return $this->reportError("Failed to create path '$outputPath'!");
		chmod($outputPath, 0755);
		
		$extraFileData  = "uespMM = {}\n";
		$extraFileData .= "uespMM.wikiUserName = '" . $this->wikiUserName . "'\n";
		$extraFileData .= "uespMM.ipAddress = '" . $_SERVER["REMOTE_ADDR"] . "'\n";
		$extraFileData .= "uespMM.uploadTime = " . time() . "\n";
		$extraFileData .= "uespMM.server = '" . $this->server. "'\n";
		
		foreach ($this->fileData as $file)
		{
			$origName = basename($file['name']);
			$destFilename = $outputPath . $origName;
			
			$result = move_uploaded_file($file['tmp_name'], $destFilename);
			if (!$result) $returnValue = $this->reportError("Failed to move temporary upload file!");
			
			chmod($destFilename, 0644);
			
			file_put_contents($destFilename, $extraFileData, FILE_APPEND | LOCK_EX);
			
			++$this->numFilesUploaded;
			$this->totalFileSize += intval($file['size']);
		}		
				
		++$this->currentLogIndex;
		$this->writeIndexFile();
		
		return $returnValue;
	}

	
	public function readIndexFile()
	{
		$index = file_get_contents(self::MM_INDEX_FILENAME);
	
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
		if (file_put_contents(self::MM_INDEX_FILENAME, (string)$this->currentLogIndex) === FALSE)
		{
			error_log("Failed to write the log index file: " . self::MM_INDEX_FILENAME);
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


$g_EsoMMLogSubmitter = new CEsoMMLogSubmitter();
$g_EsoMMLogSubmitter->parseFormInput();
$g_EsoMMLogSubmitter->output();


