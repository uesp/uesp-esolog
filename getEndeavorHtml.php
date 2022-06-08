<?php
/*
 * getEndeavorHtml.php -- by Dave Humphrey (dave@uesp.net), June 2022
 * 
 * Returns HTML fragments of Endeavor quests.
 * 
 * TODO:
 *
 */

	// Database users, passwords and other secrets
require("/home/uesp/secrets/esolog.secrets");
require("esoCommon.php");


class CEsoEndeavorHtml
{
	public $showAll = false;
	
	public $db = null;
	
	public $endeavors = [];
	public $allEndeavors = [];
	public $dailyStartTimestamp = -1;
	public $weeklyStartTimestamp = -1;
	
	public $errorMessages = [];
	
	
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
		
		if (array_key_exists('showall', $this->inputParams)) $this->showAll = intval($this->inputParams['showall']);
	}
	
	
	private function InitDatabase()
	{
		global $uespEsoLogReadDBHost, $uespEsoLogReadUser, $uespEsoLogReadPW, $uespEsoLogDatabase;
		
		$this->db = new mysqli($uespEsoLogReadDBHost, $uespEsoLogReadUser, $uespEsoLogReadPW, $uespEsoLogDatabase);
		if ($this->db->connect_error) return $this->ReportError("ERROR: Could not connect to mysql database!");
		
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
	
	
	private function MakeId($name)
	{
		$id = preg_replace('/ /', '_', trim($name));
		$id = preg_replace('/[^A-Za-z0-9_-]+/', '', $id);
		return strtolower($id);
	}
	
	
	private function MakeIconUrl($icon)
	{
		$icon = str_replace(".dds", ".png", $icon);
		$icon = $this->EscapeHtml($icon);
		return "https://esoicons.uesp.net" . $icon;
	}
	
	
	private function EscapeHtml($string)
	{
		return htmlspecialchars($string);
	}
	
	
	private function LoadLatestEndeavors()
	{
		$this->lastQuery = "select max(startTimestamp) as m from endeavors where type=0;";
		$result = $this->db->query($this->lastQuery);
		if ($result === false) return $this->ReportError("Error: Failed to find latest daily endeavor timestamps!");
		
		$maxDailyTimestamp = $result->fetch_assoc()['m'];
		if ($maxDailyTimestamp == null || $maxDailyTimestamp <= 0) return $this->ReportError("Error: Failed to find latest daily endeavor timestamp!");
		$maxDailyTimestamp = intval($maxDailyTimestamp);
		$this->dailyStartTimestamp = $maxDailyTimestamp;
		
		$this->lastQuery = "select max(startTimestamp) as m from endeavors where type=1;";
		$result = $this->db->query($this->lastQuery);
		if ($result === false) return $this->ReportError("Error: Failed to find latest weekly endeavor timestamps!");
		
		$maxWeeklyTimestamp = $result->fetch_assoc()['m'];
		if ($maxWeeklyTimestamp == null || $maxWeeklyTimestamp <= 0) return $this->ReportError("Error: Failed to find latest weekly endeavor timestamp!");
		$maxWeeklyTimestamp = intval($maxWeeklyTimestamp);
		$this->weeklyStartTimestamp = $maxWeeklyTimestamp;
		
		$this->lastQuery = "SELECT * FROM endeavors WHERE startTimestamp=$maxDailyTimestamp OR startTimestamp=$maxWeeklyTimestamp;";
		
		$result = $this->db->query($this->lastQuery);
		if ($result === false) return $this->ReportError("Error: Failed to load endeavor data!");
		if ($result->num_rows == 0) return $this->ReportError("No matching endeavor data found!");
		
		$this->endeavors = [];
		
		while ($row = $result->fetch_assoc())
		{
			$this->endeavors[] = $row;
		}
		
		return true;
	}
	
	
	private function LoadAllEndeavors()
	{
		$this->itemTimestamp = -1;
		$this->lastQuery = "SELECT * FROM endeavors;";
		
		$result = $this->db->query($this->lastQuery);
		if ($result === false) return $this->ReportError("Error: Failed to load all endeavors data!");
		if ($result->num_rows == 0) return $this->ReportError("No endeavor data found!");
		
		$this->allEndeavors = [];
		
		while ($row = $result->fetch_assoc())
		{
			$ts = intval($row['startTimestamp']);
			$this->allEndeavors[$ts][] = $row;
		}
		
		krsort($this->allEndeavors);
		return true;
	}
	
	
	private function OutputHtmlError($msg)
	{
		print("Error: $msg\n");
		print(implode("\n", $this->errorMessages));
		return false;
	}
	
	
	private function OutputGroupedEndeavorsHtml($titlePrefix, $endeavors, $timestamp)
	{
		$output = "";
		
		if ($groupedItems == null) $groupedItems = $this->groupedItems;
		if ($timestamp == null) $timestamp = $this->itemTimestamp;
		
		$formatDate = "";
		$formatDateId = "";
		
		if ($timestamp > 0) 
		{
			$formatDateId = gmdate('Y-m-d', $timestamp + 1);
			$formatDate = gmdate('j F Y', $timestamp + 1);
		}
		
		$output .= "<a name='uespesoGoldenItems_$formatDateId'></a>\n";
		$output .= "<h4>$titlePrefix$formatDate</h4>\n";
		$output .= "<ul class='uespesoGoldenItemList'>\n";
		
		foreach ($groupedItems as $nameId => $groupedItem)
		{
			$traits = $groupedItem['traits'];
			$prices = $groupedItem['prices'];
			$name = $groupedItem['name'];
			$bindType = $groupedItem['bindType'];
			$quality = $groupedItem['quality'];
			$className = "uespesoGoldenItemQuality" . $quality;
			
			$safePrices = $this->FormatPricesHtml($prices);
			$safeName = $this->EscapeHtml($name);
			$safeTraits = $this->GetTraitTexts($traits);
			
			$safeBind = "";
			if ($bindType == 2) $safeBind = " (Bind on Equip)";
			
			$itemLinkAtag = $this->GetItemLinkATag($groupedItem['items'][0]);
			$itemLinkAtagEnd = "";
			if ($itemLinkAtag) $itemLinkAtagEnd = "</a>";
			
			$output .= "<li>";
			$output .= "$itemLinkAtag<span class='uespesoGoldenItem $className'>$safeName ($safeTraits)</span>$itemLinkAtagEnd -- $safePrices $safeBind";
			$output .= "</li>\n";
		}
		
		$output .= "</ul>\n";
		return $output;
	}
	
	
	private function OutputLatestEndeavorsHtml($groupedItems = null, $timestamp = null)
	{
		return $this->OutputGroupedEndeavorsHtml("Latest Endeavors for ", $this->endeavors, $this->itemTimestamp);
	}
	
	
	private function OutputAllEndeavorsHtml()
	{
		$output = "<div class='uespesoEndeavorHistory'>";
		
		foreach ($this->allEndeavors as $timestamp => $endeavors)
		{
			$output .= $this->OutputGroupedEndeavorsHtml("Endeavors for ", $endeavors, $timestamp);
		}
		
		$output .= "</div>";
		return $output;
	}
	
	
	private function OutputHtml()
	{
		$output = "<div class='uespesoGoldenVendor'>\n";
		
		if ($this->showAll)
			$output .= $this->OutputAllItemsHtml();
		else
			$output .= $this->OutputLatestItemsHtml();
		
		$output .= "</div>\n";
		
		print($output);
		
		return true;
	}
	
	
	public function Render()
	{
		$this->OutputHtmlHeader();
		
		if ($this->showAll)
		{
			if (!$this->LoadAllEndeavors()) return $this->OutputHtmlError();
		}
		else
		{
			if (!$this->LoadLatestEndeavors()) return $this->OutputHtmlError();
		}
		
		$this->OutputHtml();
		
		return true;
	}
};


$end = new CEsoEndeavorHtml();
$end->Render();
