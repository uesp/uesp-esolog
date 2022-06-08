<?php


require_once("/home/uesp/secrets/esolog.secrets");
require_once("esoCommon.php");


class CEsoServerStatus
{
	const STATUS_UPDATE_TIME_SECS = 60;
	const STATUS_URL = "https://live-services.elderscrollsonline.com/status/realms";
	const USE_SHORT_UPDATEMESSAGE = true;
	
	public $inputParams = [];
	public $format = "HTML";
	
	public $db = null;
	public $dbWrite = null;
	public $lastQuery = "";
	
	public $serverResponseData = [];
	public $serverResponseStatus = [
			"PC - NA" => "?",
			"PC - EU" => "?",
			"PS4 - EU" => "?",
			"PS4 - NA" => "?",
			"PTS" => "?",
			"XBOX - EU" => "?",
			"XBOX - US" => "?",
	];
	
	public $serverResponseCode = -1;
	public $serverResponseMessage = "";
	public $statusLastUpdated = "";
	public $statusLastUpdatedTimestamp = 0;
	
	public $outputData = [];
	
	public function __construct()
	{
		$this->ParseInputParams();
	}
	
	
	public function ReportError($errorMsg, $statusCode = 0)
	{
		error_log($errorMsg);
		
		if ($this->outputData['error'] == null) $this->outputData['error'] = array();
		$this->outputData['error'][] = $errorMsg;
		
		if ($statusCode > 0) header("X-PHP-Response-Code: " . $statusCode, true, $statusCode);
		
		return false;
	}
	
	
	protected function ParseInputParams()
	{
		$this->inputParams = $_REQUEST;
		
		if (array_key_exists('format', $this->inputParams))
		{
			$format = strtoupper($this->inputParams['format']);
			
			if ($format == "JSON")
				$this->format = "JSON";
			elseif ($format == "HTML")
				$this->format = "HTML";
		}
	}
	
	
	protected function InitDatabase()
	{
		global $uespEsoLogReadDBHost, $uespEsoLogReadUser, $uespEsoLogReadPW, $uespEsoLogDatabase;
		
		$this->db = new mysqli($uespEsoLogReadDBHost, $uespEsoLogReadUser, $uespEsoLogReadPW, $uespEsoLogDatabase);
		if ($this->db->connect_error) return $this->ReportError("ERROR: Could not connect to mysql database!", 500);
		
		return true;
	}
	
	
	protected function InitWriteDatabase()
	{
		global $uespEsoLogWriteDBHost, $uespEsoLogWriteUser, $uespEsoLogWritePW, $uespEsoLogDatabase;
	
		$this->dbWrite = new mysqli($uespEsoLogWriteDBHost, $uespEsoLogWriteUser, $uespEsoLogWritePW, $uespEsoLogDatabase);
		if ($this->dbWrite->connect_error) return $this->ReportError("ERROR: Could not connect to mysql database!", 500);
	
		return true;
	}
	
	
	protected function ParseResponseData($data)
	{
		$this->serverResponseData = $data;
		
		$response = $data['response'];
		if ($response == null) return $this->ReportError("ERROR: Failed to retrieve server status!", 500);
		
		$this->serverResponseCode = $data['result_code'];
		if ($this->serverResponseCode == null) $this->serverResponseCode = -2;
		
		$this->serverResponseMessage = $data['result_message'];
		if ($this->serverResponseMessage == null) $this->serverResponseMessage = "N/A";
		
		foreach ($response as $key => $value)
		{
			$key = strtoupper($key);
			$value = strtoupper($value);
			$key = str_replace("THE ELDER SCROLLS ONLINE (", "", $key);
			$key = str_replace(")", "", $key);
			$key = str_replace("US", "NA", $key);
			
			if ($key == "EU") $key = "PC - EU";
			elseif ($key == "NA") $key = "PC - NA";
			
			$this->serverResponseStatus[$key] = $value;
		}
		
		return true;
	}
	
	
	protected function SaveStatus()
	{
		if (!$this->InitWriteDatabase()) return false;
		
		$data = [];
		$data['response'] = $this->serverResponseStatus;
		$data['result_code'] = $this->serverResponseCode;
		$data['result_message'] = $this->serverResponseMessage;
		$data['last_updated'] = $this->statusLastUpdated;
		$data['last_updated_timestamp'] = $this->statusLastUpdatedTimestamp;
		
		$json = json_encode($data);
		$safeJson = $this->dbWrite->real_escape_string($json);
		$safeUpdate = intval($this->statusLastUpdatedTimestamp);
		
		$this->lastQuery = "UPDATE logInfo SET value='$safeJson' WHERE id='esoServerStatus';";
		$result = $this->dbWrite->query($this->lastQuery);
		if ($result == false) return $this->ReportError("ERROR: Failed to update server status!");
		
		$this->lastQuery = "UPDATE logInfo SET value='$safeUpdate' WHERE id='lastEsoServerStatusUpdate';";
		$result = $this->dbWrite->query($this->lastQuery);
		if ($result == false) return $this->ReportError("ERROR: Failed to update server status!");
		
		return true;
	}
	
	
	protected function UpdateStatus()
	{
		$json = file_get_contents(self::STATUS_URL);
		$statusData = json_decode($json, true);
		
		if ($statusData == null) return $this->ReportError("ERROR: Failed to retrieve server status!", 500);
		
		$zosResponse = $statusData['zos_platform_response'];
		if ($zosResponse == null) return $this->ReportError("ERROR: Failed to retrieve server status!", 500);
		
		if (!$this->ParseResponseData($zosResponse)) return false;
		
		$this->statusLastUpdatedTimestamp = time();
		$this->statusLastUpdated = date('Y-m-d H:i:s', $this->statusLastUpdatedTimestamp);
		
		$this->SaveStatus();
		
		return true;
	}
	
	
	protected function LoadServerStatus()
	{
		$this->lastQuery = "SELECT * FROM logInfo WHERE id='esoServerStatus' or id='lastEsoServerStatusUpdate';";
		$result = $this->db->query($this->lastQuery);
		if ($result === false) return $this->ReportError("ERROR: Failed to retrieve server status!", 500);
		
		$lastStatus = "";
		$lastEsoServerStatusUpdate = 0;
		
		while ($row = $result->fetch_assoc())
		{
			$id = $row['id'];
			
			if ($id == "esoServerStatus")
			{
				$lastStatus = $row['value'];
			}
			elseif ($id == "lastEsoServerStatusUpdate")
			{
				$lastEsoServerStatusUpdate = intval($row['value']);
			}
		}
		
		$currentTime = time();
		$diffTime = $currentTime - $lastEsoServerStatusUpdate;
		
		if ($diffTime >= self::STATUS_UPDATE_TIME_SECS || $lastStatus == null || $lastStatus == "") return $this->UpdateStatus();
		
		$this->statusLastUpdatedTimestamp = $lastEsoServerStatusUpdate;
		$this->statusLastUpdated = date('Y-m-d H:i:s', $lastEsoServerStatusUpdate);
		
		if (!$this->ParseResponseData(json_decode($lastStatus, true))) return false;
		return true;
	}
	
	
	protected function OutputHtmlHeader()
	{
		ob_start("ob_gzhandler");
		
		header("Expires: 0");
		header("Pragma: no-cache");
		header("Cache-Control: no-cache, no-store, must-revalidate");
		header("Access-Control-Allow-Origin: " . $_SERVER['HTTP_ORIGIN'] . "");
		header("content-type: text/html");
	}
	
	
	protected function OutputJsonHeader()
	{
		ob_start("ob_gzhandler");
		
		header("Expires: 0");
		header("Pragma: no-cache");
		header("Cache-Control: no-cache, no-store, must-revalidate");
		header("Access-Control-Allow-Origin: " . $_SERVER['HTTP_ORIGIN'] . "");
		header("content-type: application/json");
	}
	
	
	protected function OutputJson()
	{
		$this->OutputJsonHeader();
		
		$data = [];
		$data['response'] = $this->serverResponseStatus;
		$data['result_code'] = $this->serverResponseCode;
		$data['result_message'] = $this->serverResponseMessage;
		$data['last_updated'] = $this->statusLastUpdated;
		$data['last_updated_timestamp'] = $this->statusLastUpdatedTimestamp;
		
		$json = json_encode($data);
		
		print($json);
		
		return true;
	}
	
	
	protected function escape($html)
	{
		return htmlspecialchars($html);
	}
	
	
	protected function FormatStatus($status)
	{
		$statusType = "Unknown";
		if ($status == "UP") $statusType = "Up";
		if ($status == "DOWN") $statusType = "Down";
		
		$output = "<div class='uespEsoStatus$statusType'>$status</div>";
		return $output;
	}
	
	
	protected function OutputHtml()
	{
		$this->OutputHtmlHeader();
		
		$response = $this->serverResponseStatus;
		$pcna = $this->FormatStatus($response['PC - NA']);
		$pceu = $this->FormatStatus($response['PC - EU']);
		$ps4eu = $this->FormatStatus($response['PS4 - EU']);
		$ps4na = $this->FormatStatus($response['PS4 - NA']);
		$xboxeu = $this->FormatStatus($response['XBOX - EU']);
		$xboxna = $this->FormatStatus($response['XBOX - NA']);
		$pts = $this->FormatStatus($response['PTS']);
		
		$diffSeconds = (time() - $this->statusLastUpdatedTimestamp);
		$diffMinutes = floor($diffSeconds / 60);
		
		$lastUpdateMsg = $this->statusLastUpdated . ", ";
		if (self::USE_SHORT_UPDATEMESSAGE) $lastUpdateMsg = ""; 
		
		if ($diffSeconds <= 2)
			$lastUpdate = $this->escape($lastUpdateMsg . "just now");
		else if ($diffMinutes == 0)
			$lastUpdate = $this->escape($lastUpdateMsg . "$diffSeconds seconds ago");
		elseif ($diffMinutes == 1)
			$lastUpdate = $this->escape($lastUpdateMsg . "$diffMinutes minute ago");
		elseif ($diffMinutes < 1000)
			$lastUpdate = $this->escape($lastUpdateMsg . "$diffMinutes minutes ago");
		else
			$lastUpdate = $this->escape($lastUpdateMsg . "a while ago");
		
		$output = "<div id='uespEsoServerStatus'>";
		$output .= "<div class='uespEsoServer'><div class='uespEsoServerTitle'>PC - NA :</div> $pcna</div>";
		$output .= "<div class='uespEsoServer'><div class='uespEsoServerTitle'>PC - EU :</div> $pceu</div>";
		$output .= "<div class='uespEsoServer'><div class='uespEsoServerTitle'>PlayStation - NA :</div> $ps4na</div>";
		$output .= "<div class='uespEsoServer'><div class='uespEsoServerTitle'>PlayStation - EU :</div> $ps4eu</div>";
		$output .= "<div class='uespEsoServer'><div class='uespEsoServerTitle'>Xbox - NA :</div> $xboxna</div>";
		$output .= "<div class='uespEsoServer'><div class='uespEsoServerTitle'>Xbox - EU :</div> $xboxeu</div>";
		$output .= "<div class='uespEsoServer'><div class='uespEsoServerTitle'>PTS :</div> $pts</div>";
		$output .= "<div class='uespEsoServerUpdated'>Last Updated : $lastUpdate</div>";
		$output .= "</div>";
		
		print($output);
		
		return true;
	}
	
	
	protected function Output()
	{
		if ($this->format == "JSON") return $this->OutputJson();
		return $this->OutputHtml();
	}
	
	
	public function Render()
	{
		if (!$this->InitDatabase()) return false;
		
		if (!$this->LoadServerStatus()) return false;
		
		return $this->Output();
	}
	
};

