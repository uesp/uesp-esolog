<?php
/*
 * setReference.php -- by Dave Humphrey (dave@uesp.net), June 2022
 * 
 * Basic table of set data.
 * 
 * TODO:
 *
 */

	// Database users, passwords and other secrets
require("/home/uesp/secrets/esolog.secrets");
require("esoCommon.php");


class CUespEsoSetReference
{
	public $version = "";
	
	public $db = null;
	public $lastQuery = "";
	public $sets = [];
	
	public function __construct()
	{
		$this->ParseInputParams();
		$this->InitDatabase();
	}
	
	
	public function ReportError($errorMsg)
	{
		error_log($errorMsg);
		$this->errorMessages[] = $errorMsg;
		return false;
	}
	
	
	private function ParseInputParams ()
	{
		$this->inputParams = $_REQUEST;
	}
	
	
	private function InitDatabase()
	{
		global $uespEsoLogReadDBHost, $uespEsoLogReadUser, $uespEsoLogReadPW, $uespEsoLogDatabase;
		
		$this->db = new mysqli($uespEsoLogReadDBHost, $uespEsoLogReadUser, $uespEsoLogReadPW, $uespEsoLogDatabase);
		if ($this->db->connect_error) return $this->ReportError("ERROR: Could not connect to mysql database!");
		
		return true;
	}
	
	
	private function LoadSets()
	{
		$tableSuffix = GetEsoItemTableSuffix($this->version);
		$this->lastQuery = "SELECT * FROM setSummary$tableSuffix ORDER BY setName;";
		$result = $this->db->query($this->lastQuery);
		if ($result === false) return $this->ReportError("Error: Failed to load set data!");
		
		while ($row = $result->fetch_assoc())
		{
			$this->sets[] = $row;
		}
		
		return true;
	}
	
	
	private function EscapeHtml($string)
	{
		return htmlspecialchars($string);
	}
	
	
	public function WritePageHeader()
	{
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
<html>
<head>
	<title>UESP:ESO Log Data -- Set Reference</title>
	<meta http-equiv="Content-Type" content="text/html;charset=utf-8" />
	<meta charset="utf-8" />
	<link rel="stylesheet" href="//esolog-static.uesp.net/viewlog.css" />
	<script type="text/javascript" src="//esolog-static.uesp.net/resources/jquery-1.10.2.js"></script>
	<script type="text/javascript" src="//esolog-static.uesp.net/viewlog.js"></script>
</head>
<body>
<?php
		return true;
	}
	
	
	public function WritePageFooter()
	{
?>
<hr>
<div class='elvLicense'>Most content here is available under the same Attribute-ShareAlike 2.5 License as the UESP wiki. See <a href='https://en.uesp.net/wiki/UESPWiki:Copyright_and_Ownership'>Copyright and Ownership</a>
for more information. Some data is extracted directly from the ESO game data files and copyright is owned by Zenimax Online Studios.</div>
</body>
</html>
<?php
			return true;
	}
	
	
	private function OutputHtmlHeader()
	{
		ob_start("ob_gzhandler");
		
		header("Expires: 0");
		header("Pragma: no-cache");
		header("Cache-Control: no-cache, no-store, must-revalidate");
		header("Pragma: no-cache");
		header("content-type: text/html");
		
		header("Access-Control-Allow-Origin: *");
	}
	
	
	private function OutputHtml()
	{
		$output = "<table border='1' cellspacing='0' cellpadding='2'>\n";
		$output .= "<tr>\n";
		$output .= "<th>Set Name</th>";
		$output .= "<th>Bonuses</th>";
		$output .= "<th>Item Slots</th>";
		$output .= "<th>Image</th>";
		$output .= "</tr>\n";
		
		foreach ($this->sets as $set)
		{
			$nameUrl = urlencode($set['setName']);
			$name = $this->EscapeHtml($set['setName']);
			$desc = $this->EscapeHtml($set['setBonusDesc']);
			$itemSlots = $this->EscapeHtml($set['itemSlots']);
			$imageLink = "https://esolog.uesp.net/itemLinkImage.php?set=$nameUrl";
			
			$output .= "<tr>\n";
			$output .= "<td><b>$name</b></td>\n";
			$output .= "<td style='white-space: pre-wrap; word-wrap: break-word;'>$desc</td>\n";
			$output .= "<td>$itemSlots</td>\n";
			$output .= "<td style='min-width: 320px'><a href=\"$imageLink\">$imageLink</a><br/><img src=\"$imageLink\"></td>\n";
			$output .= "</tr>\n";
		}
		
		$output .= "</table>";
		print($output);
		
		return true;
	}
	
	
	public function Render()
	{
		$this->OutputHtmlHeader();
		$this->WritePageHeader();
		$this->LoadSets();
		$this->OutputHtml();
		$this->WritePageFooter();
		
		return true;
	}
	
};


$set = new CUespEsoSetReference();
$set->Render();