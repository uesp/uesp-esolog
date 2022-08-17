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
	public $showImage = true;
	
	public $db = null;
	public $lastQuery = "";
	public $sets = [];
	
	public $WIKI_ARTICLE_FIXUP = [
			"Agility" => "Agility (set)",
			"Alessian Order" => "Alessian Order (set)",
			"Balorgh" => "Balorgh (set)",
			"Baron Thirsk" => "Baron Thirsk (set)",
			"Baron Zaudrus" => "Baron Zaudrus (set)",
			"Bloodspawn" => "Bloodspawn (set)",
 			"Chokethorn" => "Chokethorn (set)",
			"Giant Spider" => "Giant Spider (set)",
			"Glorgoloch the Destroyer" => "Glorgoloch the Destroyer (set)",
			"Grave Guardian" => "Grave Guardian (set)",
			"Grothdarr" => "Grothdarr (set)",
			"Grundwulf" => "Grundwulf (set)",
			"Iceheart" => "Iceheart (set)",
			"Immolator Charr" => "Immolator Charr (set)",
			"Infernal Guardian" => "Infernal Guardian (set)",
			"Kargaeda" => "Kargaeda (set)",
			"Lady Malygda" => "Lady Malygda (set)",
			"Lady Thorn" => "Lady Thorn (set)",
			"Maarselok" => "Maarselok (set)",
			"Magma Incarnate" => "Magma Incarnate (set)",
			"Maw of the Infernal" => "Maw of the Infernal (set)",
			"Might Chudan" => "Might Chudan (set)",
			"Molag Kena" => "Molag Kena (set)",
			"Mother Ciannait" => "Mother Ciannait (set)",
			"Nazaray" => "Nazaray (set)",
			"Nerien'eth" => "Nerien'eth (set)",
			"Night Terror" => "Night Terror (set)",
			"Nunatak" => "Nunatak (set)",
			"Selene" => "Selene (set)",
			"Sentinel of Rkugamz" => "Sentinel of Rkugamz (set)",
			"Sentry" => "Sentry (set)",
			"Shadow Walker" => "Shadow Walker (set)",
			"Shadowrend" => "Shadowrend (set)",
			"Slimecraw" => "Slimecraw (set)",
			"Spawn of Mephala" => "Spawn of Mephala (set)",
			"Stone Husk" => "Stone Husk (set)",
			"Stormfist" => "Stormfist (set)",
			"Swarm Mother" => "Swarm Mother (set)",
			"Symphony of Blades" => "Symphony of Blades (set)",
			"Thurvokun" => "Thurvokun (set)",
			"Tremorscale" => "Tremorscale (set)",
			"The Troll King" => "The Troll King (set)",
			"Valkyn Skoria" => "Valkyn Skoria (set)",
			"Vampire Lord" => "Vampire Lord (set)",
			"Velidreth" => "Velidreth (set)",
			"Winterborn" => "Winterborn (set)",
			"Zoal the Ever-Wakeful" => "Zoal the Ever-Wakeful (set)",
	];
	
	
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
		
		if (array_key_exists('version', $this->inputParams)) 
		{
			$this->version = trim($this->inputParams['version']);
			if ($this->version === strval(GetEsoUpdateVersion())) $this->version = "";
		}
		
		if (array_key_exists('showimage', $this->inputParams)) 
		{
			$this->showImage = intval($this->inputParams['showImage']);
		}
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
	
	
	public function GetVersionList($currentVersion) 
	{
		$output = "";
		if ($currentVersion == "") $currentVersion = GetEsoUpdateVersion();
		
		$query = "SHOW TABLES LIKE 'setSummary%';";
		$result = $this->db->query($query);
		if ($result === false) return $this->ReportError("Failed to list all setSummary table versions!");
		
		$tables = array();
		$output .= "<form action='?' method='get' style='display: inline-block;'>";
		if (!$this->showImage) $output .= "<input type='hidden' name='showimage' value='0'>";
		$output .= "<select name='version'>";
		
		$tables = array();
		
		while (($row = $result->fetch_row())) 
		{
			$table = $row[0];
			$version = substr($table, 10);
			if ($version == "") $version = GetEsoUpdateVersion();
			
			$tables[$version] = $version;
		}
		
		natsort($tables);
		
		foreach ($tables as $version)
		{
			$select = "";
			if (strcasecmp($version, $currentVersion) == 0) $select = "selected";
			$output .= "<option $select>$version</option>";
		}
		
		$output .= "</select>";
		$output .= "<input type='submit' value='Go'>";
		$output .= "</form>";
		
		return $output;
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
<body>
<a href='viewlog.php'>Back to Home</a><br />
<h1>ESO: Set Reference</h1>
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
		$versionList = $this->GetVersionList($this->version);
		$count = count($this->sets);
		
		$output = "Showing $count sets for game update: $versionList";
		
		$output .= "<table border='1' cellspacing='0' cellpadding='2'>\n";
		$output .= "<tr>\n";
		$output .= "<th>Set Name</th>";
		$output .= "<th>Bonuses</th>";
		$output .= "<th>Type</th>";
		$output .= "<th>Sources</th>";
		$output .= "<th>Item Slots</th>";
		$output .= "<th>Image</th>";
		$output .= "</tr>\n";
		
		foreach ($this->sets as $set)
		{
			$nameUrl = urlencode($set['setName']);
			$name = $this->EscapeHtml($set['setName']);
			$desc = $this->EscapeHtml($set['setBonusDesc']);
			$itemSlots = $this->EscapeHtml($set['itemSlots']);
			$type = $this->EscapeHtml($set['type']);
			$sources = $this->EscapeHtml($set['sources']);
			
			$imageLink = "https://esolog.uesp.net/itemLinkImage.php?set=$nameUrl";
			if ($this->version != "") $imageLink .= "&version=" . $this->version;
			
			$articleName = str_replace(' ', '_', $set['setName']);
			$fixupName = $this->WIKI_ARTICLE_FIXUP[$set['setName']];
			if ($fixupName != null) $articleName = str_replace(' ', '_', $fixupName);
			$wikiLink = "https://en.uesp.net/wiki/Online:$articleName?set";
			
			$output .= "<tr>\n";
			$output .= "<td><b>$name</b></td>\n";
			$output .= "<td style='white-space: pre-wrap; word-wrap: break-word;'>$desc</td>\n";
			$output .= "<td>$type</td>\n";
			$output .= "<td>$sources</td>\n";
			$output .= "<td>$itemSlots</td>\n";
			
			if ($this->showImage) 
				$output .= "<td style='min-width: 320px'><a href=\"$wikiLink\" setname=\"$name\">$name</a><br/><img src=\"$imageLink\"><br/><a href=\"$imageLink\">$imageLink</a></td>\n";
			else
				$output .= "<td><a href=\"$wikiLink\" setname=\"$name\">$name</a><p/><a href=\"$imageLink\">$imageLink</a></td>\n";
			
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