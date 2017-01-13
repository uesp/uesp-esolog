<?php 


// Database users, passwords and other secrets
require_once("/home/uesp/secrets/esosalesdata.secrets");
require_once(__DIR__."/esoCommon.php");


$g_EsoSalesDataSortOrder = 1;
$g_EsoItemData = null;


class EsoViewSalesData
{
	const ESOVSD_ICON_URL = UESP_ESO_ICON_URL;
	const ESOVSD_ICON_UNKNOWN = "unknown.png";
		
	public $ESOVSD_HTML_TEMPLATE = "";
	public $ESOVSD_HTML_TEMPLATE_EMBED = "";
	public $ESOVSD_HTML_SALES_TEMPLATE = "";
	public $ESOVSD_HTML_SALES_TEMPLATE_EMBED = "";
	public $ESOVSD_HTML_GUILDS_TEMPLATE = "";
	public $ESOVSD_HTML_GUILDS_TEMPLATE_EMBED = "";
	
	public $server = "NA";
	public $isEmbedded = false;
	
	public $db = null;
	private $dbReadInitialized  = false;
	private $dbWriteInitialized = false;
	public $lastQuery = "";
	
	public $htmlTemplate = "";
	
	public $viewSalesItemId = -1;
	public $formValues = array();
	public $finalItemLevel = -1;
	public $finalItemQuality = -1;
	public $sortField = "itemname";
	public $sortOrder = 1;
	public $displayServer = "??";
	public $rawShowForm = "";
	public $showForm = "ItemSearch";
	public $viewRawData = false;
	
	public $guildData = array();
	public $searchCount = 0;
	public $itemCount = 0;
	public $itemResults = array();
	public $itemIds = array();
	public $singleItemData = null;
	public $itemSortedKeys = array();
	public $searchResults = array();
	public $searchLimitCount = 500;
	public $searchItemIdsLimit = 1000;
	public $totalItemCount = 0;
	public $totalSalesCount = 0;
	
	public $itemQuery = "";
	public $salesQuery = "";
	
	public $errorMessages = array();
	
	static public $ESOVSD_TRAITS = array();
	static public $ESOVSD_QUALITIES = array();
	static public $ESOVSD_ITEMTYPES = array();
	static public $ESOVSD_EQUIPTYPES = array();
	static public $ESOVSD_ARMORTYPES = array();
	static public $ESOVSD_WEAPONTYPES = array();
	
	static public $ESOVSD_TIMEPERIODS = array(
			-1 => "",
			86400 => "Last Day",
			604800 => "Last Week",
			2678400 => "Last Month",
			31558150 => "Last Year",
	);
		
	static public $ESOVSD_SERVERS = array(
			"NA" => "PC-NA",
			"EU" => "PC-EU",
			"PTS" => "PTS",			
	);
	
	static public $ESOVSD_SALETYPES = array(
			"All" => "All",
			"Purchases" => "Only Purchases",
			"ForSale" => "Only For Sale",
	);
	

	public function __construct ()
	{
		global $ESO_ITEMTRAIT10_TEXTS, $ESO_ITEMTYPE_TEXTS, $ESO_ITEMEQUIPTYPE_TEXTS;
		global $ESO_ITEMARMORTYPE_TEXTS, $ESO_ITEMWEAPONTYPE_TEXTS, $ESO_ITEMQUALITY_TEXTS;
		
		$this->ESOVSD_HTML_TEMPLATE = __DIR__."/templates/esosales_template.txt";
		$this->ESOVSD_HTML_TEMPLATE_EMBED = __DIR__."/templates/esosales_embed_template.txt";
		
		$this->ESOVSD_HTML_SALES_TEMPLATE = __DIR__."/templates/esosales_sales_template.txt";
		$this->ESOVSD_HTML_SALES_TEMPLATE_EMBED = __DIR__."/templates/esosales_sales_embed_template.txt";
		
		$this->ESOVSD_HTML_GUILDS_TEMPLATE = __DIR__."/templates/esosales_guilds_template.txt";
		$this->ESOVSD_HTML_GUILDS_TEMPLATE_EMBED = __DIR__."/templates/esosales_guilds_embed_template.txt";
		
		self::$ESOVSD_TRAITS = self::MakeUniqueArray($ESO_ITEMTRAIT10_TEXTS);
		self::$ESOVSD_QUALITIES = self::MakeUniqueArray($ESO_ITEMQUALITY_TEXTS, true);
		self::$ESOVSD_ITEMTYPES = self::MakeUniqueArray($ESO_ITEMTYPE_TEXTS);
		self::$ESOVSD_EQUIPTYPES = self::MakeUniqueArray($ESO_ITEMEQUIPTYPE_TEXTS);
		self::$ESOVSD_ARMORTYPES = self::MakeUniqueArray($ESO_ITEMARMORTYPE_TEXTS);
		self::$ESOVSD_WEAPONTYPES = self::MakeUniqueArray($ESO_ITEMWEAPONTYPE_TEXTS);
		
		array_unshift(self::$ESOVSD_TRAITS, "(none)");
	
		$this->InitDatabaseRead();

		$this->SetInputParams();
		$this->ParseInputParams();
	}
	
	
	private static function MakeUniqueArray($src, $noSort = false)
	{
		$newArray = array_unique($src);
		if (!$noSort) sort($newArray);
		return $newArray;
	}


	public function ReportError ($errorMsg)
	{
		error_log($errorMsg);

		if ($this->db != null && $this->db->error)
		{
			error_log("\tDB Error:" . $this->db->error);
			error_log("\tLast Query:" . $this->lastQuery);
		}
		return false;
	}


	private function InitDatabaseRead ()
	{
		global $uespEsoSalesDataReadDBHost, $uespEsoSalesDataReadUser, $uespEsoSalesDataReadPW, $uespEsoSalesDataDatabase;
		global $uespEsoSalesDataDatabase;

		$database = $uespEsoSalesDataDatabase;

		if ($this->dbReadInitialized) return true;

		$this->db = new mysqli($uespEsoSalesDataReadDBHost, $uespEsoSalesDataReadUser, $uespEsoSalesDataReadPW, $database);
		if ($db->connect_error) return $this->ReportError("Could not connect to mysql database!");

		$this->dbReadInitialized = true;
		$this->dbWriteInitialized = false;
		
		return true;
	}


	private function ParseInputParams ()
	{
		$this->ParseFormParam('text');
		$this->ParseFormParam('trait');
		$this->ParseFormParam('quality');
		$this->ParseFormParam('itemtype');
		$this->ParseFormParam('equiptype');
		$this->ParseFormParam('armortype');
		$this->ParseFormParam('weapontype');
		$this->ParseFormParam('level');
		$this->ParseFormParam('timeperiod');
		$this->ParseFormParam('server');
		
		if ($this->formValues['server'] != null)
		{
			if ($this->formValues['server'] == "NA")
				$this->server = "NA";
			else if ($this->formValues['server'] == "EU")
				$this->server = "EU";
			else if ($this->formValues['server'] == "PTS")
				$this->server = "PTS";
			
			$this->displayServer = $this->server;
			$this->salesData->server = $this->server; 
		}
		
		$this->finalItemQuality = $this->GetItemQualityValue($this->formValues['quality']);
		$this->finalItemLevel   = $this->GetItemLevelValue($this->formValues['level']);
		if ($this->finalItemLevel == 0) $this->finalItemLevel = -1;
		
		if (array_key_exists("viewsales", $this->inputParams))
		{
			$this->viewSalesItemId = intval($this->inputParams['viewsales']);
			$this->showForm = "ViewSales";
			$this->sortField = "buydate";
			$this->sortOrder = 0;
		}
		
		if (array_key_exists("view", $this->inputParams))
		{
			$this->rawShowForm = strtolower($this->inputParams['view']);
			
			if ($this->rawShowForm == "guild" || $this->rawShowForm == "guilds")
			{
				$this->showForm = "ViewGuilds";
			}
		}
		
		if (array_key_exists("raw", $this->inputParams) || array_key_exists("rawdata", $this->inputParams))
		{
			$this->viewRawData = true;
		}
				
		if (array_key_exists("sort", $this->inputParams)) $this->sortField = $this->inputParams['sort'];
		if (array_key_exists("order", $this->inputParams)) $this->sortOrder = intval($this->inputParams['order']);
		
		return true;
	}
	
	
	private function ParseFormParam($name)
	{
		$id = $name;
	
		if (array_key_exists($id, $this->inputParams))
		{
			$this->formValues[$name] = $this->inputParams[$id];
			return true;
		}
	
		return false;
	}


	private function SetInputParams ()
	{
		global $argv;
		$this->inputParams = $_REQUEST;
	}


	private function OutputHtmlHeader()
	{
		ob_start("ob_gzhandler");
		header("Expires: 0");
		header("Pragma: no-cache");
		header("Cache-Control: no-cache, no-store, must-revalidate");
		header("Pragma: no-cache");
		
		$origin = $_SERVER['HTTP_ORIGIN'];
		
		if (substr($origin, -8) == "uesp.net")
		{
			header("Access-Control-Allow-Origin: $origin");
		}
	}


	public function LoadTemplate()
	{
		$templateFile = "";
		
		if ($this->showForm == "ViewSales")
		{
			if ($this->isEmbedded)
				$templateFile .= $this->ESOVSD_HTML_SALES_TEMPLATE_EMBED;
			else
				$templateFile .= $this->ESOVSD_HTML_SALES_TEMPLATE;
		}
		else if ($this->showForm == "ViewGuilds")
		{
			if ($this->isEmbedded)
				$templateFile .= $this->ESOVSD_HTML_GUILDS_TEMPLATE_EMBED;
			else
				$templateFile .= $this->ESOVSD_HTML_GUILDS_TEMPLATE;
		}
		else
		{
			if ($this->isEmbedded)
				$templateFile .= $this->ESOVSD_HTML_TEMPLATE_EMBED;
			else
				$templateFile .= $this->ESOVSD_HTML_TEMPLATE;
		}
			
		$this->htmlTemplate = file_get_contents($templateFile);
	}
	
	
	public function GetFormValue($id, $default = "")
	{
		if (!array_key_exists($id, $this->formValues)) return $default;
		return htmlspecialchars($this->formValues[$id]);
	}
	
	
	public function GetGeneralListHtml($listArray, $formName, $useKeyAsValue = false)
	{
		$output = "";
		$selectedValue = $this->GetFormValue($formName);
	
		foreach ($listArray as $key => $value)
		{
			if ($value == $selectedValue)
				$selected = "selected";
			else if ($useKeyAsValue && $key == $selectedValue)
				$selected = "selected";
			else
				$selected = "";
	
			if ($useKeyAsValue)
				$output .= "<option value='$key' $selected>$value</option>";
			else
				$output .= "<option value='$value' $selected>$value</option>";
		}
	
		return $output;
	}
	
	
	public function GetItemTraitValue($text)
	{
		global $ESO_ITEMTRAIT_TEXTS, $ESO_ITEMTRAIT10_TEXTS;
	
		$value = array_search($text, $ESO_ITEMTRAIT10_TEXTS);
	
		if ($value === FALSE) return -1;
		return $value;
	}
	
	
	public function GetItemQualityValue($text)
	{
		global $ESO_ITEMQUALITY_TEXTS;
	
		$value = array_search($text, $ESO_ITEMQUALITY_TEXTS);
		if ($value === FALSE) return -1;
	
		return $value;
	}
	
	
	public function GetItemLevelValue($text)
	{
		$result = preg_match("/v[0-9]+/i", $text);
		$result1 = preg_match("/cp[0-9]+/i", $text);
		$level = 0;
	
		if ($result)
		{
			$level = intval(substr($text, 1)) + 50;
		}
		else if ($result1)
		{
			$level = floor(intval(substr($text, 2))/10) + 50;
		}
		else
		{
			$level = intval($text);
		}
	
		if ($level < 0)
			$level = 0;
		else if ($level > 66)
			$level = 66;
					
		return $level;
	}
	
		
	public function GetItemTypeValue($text)
	{
		global $ESO_ITEMTYPE_TEXTS;
	
		$value = array_search($text, $ESO_ITEMTYPE_TEXTS);
		if ($value === FALSE) return -1;
	
		return $value;
	}
	
	
	public function GetEquipTypeValue($text)
	{
		global $ESO_ITEMEQUIPTYPE_TEXTS;
	
		$value = array_search($text, $ESO_ITEMEQUIPTYPE_TEXTS);
		if ($value === FALSE) return -1;
	
		return $value;
	}
	
	
	public function GetWeaponTypeValue($text)
	{
		global $ESO_ITEMWEAPONTYPE_TEXTS;
	
		$value = array_search($text, $ESO_ITEMWEAPONTYPE_TEXTS);
		if ($value === FALSE) return -1;
	
		return $value;
	}
	
	
	public function GetArmorTypeValue($text)
	{
		global $ESO_ITEMARMORTYPE_TEXTS;
	
		$value = array_search($text, $ESO_ITEMARMORTYPE_TEXTS);
		if ($value === FALSE) return -1;
	
		return $value;
	}
	
	
	public function GetOutputFormLevel()
	{
		$level = $this->GetItemLevelValue($this->formValues['level']);
		if ($level <= 0) return "";
	
		if ($level > 50)
		{
			if (UESP_SHOWCPLEVEL) return "CP" . (($level - 50)*10);
			return "v" . ($level - 50);
		}
	
		return (string) $level;
	}
	
	
	public function GetIconUrl($icon)
	{
		if ($icon == null || $icon == "") $icon = self::ESOVSD_ICON_UNKNOWN;
	
		$icon = preg_replace('/dds$/', 'png', $icon);
		$icon = preg_replace('/^\//', '', $icon);
	
		$iconLink = self::ESOVSD_ICON_URL . '/' . $icon;
		return $iconLink;
	}
	
	
	public function GetItemSearchResultsHtml()
	{
		$output  = "<table id='esovsd_searchresults_table'>";
		$output .= "<tr>";
		$output .= "<th>Item</th>";
		$output .= "<th>Trait</th>";
		$output .= "<th>Type</th>";
		$output .= "<th>EquipType</th>";
		$output .= "<th>WeaponType</th>";
		$output .= "<th>ArmorType</th>";
		$output .= "<th>Set Name</th>";
		$output .= "<th>Sales</th>";
		$output .= "<th></th>";
		$output .= "</tr>";
		
		
		foreach ($this->itemSortedKeys as $itemId)
		{
			$item = $this->itemResults[$itemId];
			
			$iconURL = $this->GetIconUrl($item['icon']);
				
			$trait = $item['trait'];
			$itemType = GetEsoItemTypeText($item['itemType']);
			$weaponType = GetEsoItemWeaponTypeText($item['weaponType']);
			$armorType = GetEsoItemArmorTypeText($item['armorType']);
			$equipType = GetEsoItemEquipTypeText($item['equipType']);
			$setName = $item['setName'];
			$totalSales = $item['totalSales'];
				
			$traitText = "";
			if ($trait > 0) $traitText = GetEsoItemTraitText($trait);
			
			$output .= "<tr>";
			$output .= "<td><div class='esovsd_itemlink eso_item_link eso_item_link_q{$item['quality']}' itemid='{$item['itemId']}' intlevel='{$item['internalLevel']}' inttype='{$item['internalSubType']}' potiondata='{$item['potionData']}'>";
			$output .= "<img src='$iconURL' class='esovsd_itemicon'>{$item['name']}</div></td>";
			$output .= "<td>$traitText</td>";
			$output .= "<td>$itemType</td>";
			$output .= "<td>$equipType</td>";
			$output .= "<td>$weaponType</td>";
			$output .= "<td>$armorType</td>";
			$output .= "<td>$setName</td>";
			$output .= "<td>$totalSales</td>";
			$output .= "<td><a href='?viewsales=$itemId'>View Item Sales</a></td>";
			$output .= "</tr>\n";
		}
		
		$output .= "</table>";
		return $output;
	}
	
	
	
	public function FormatTimeStamp ($timestamp)
	{
		if ($timestamp == null || $timestamp == "") return "";
		
		$tsValue = intval($timestamp);
		if ($tsValue <= 0) return "";
		
		if ($this->viewRawData) return "" . $timestamp;
		
		$now = time();
		$diff = $now - $tsValue;
		
		$days = floor($diff / 86400);
		$hourSeconds = $diff % 86400;
		$hours = floor($hourSeconds / 3600);
		$minuteSeconds = $hourSeconds % 3600;
		$minutes = floor ($hourSeconds / 60);
		$seconds = $minuteSeconds % 60;
		
		if ($days > 1)
			return "$days days ago";
		else if ($days > 0)
			return "1 day ago";
		else if ($hours > 1)
			return "$hours hours ago";
		else if ($hours > 0)
			return "1 hour ago";
		else if ($minutes > 1)
			return "$minutes mins ago";
		else
			return "1 minute ago";
		
	}
	
	
	public function GetSalesSearchResultsHtml()
	{
		$output  = "<table id='esovsd_searchresults_table'>";
		$output .= "<tr>";
		$output .= "<th>Guild</th>";
		$output .= "<th>Seller</th>";
		$output .= "<th>Buyer</th>";
		$output .= "<th>Listed</th>";
		$output .= "<th>Purchased</th>";
		$output .= "<th>Price</th>";
		$output .= "<th>Qnt</th>";
		$output .= "<th>Unit Price</th>";
		$output .= "</tr>";
		
		foreach ($this->searchResults as $row)
		{
			$guild = $this->guildData[$row['guildId']];
			$item = $this->itemResults[$row['itemId']];
			$buyDate = $this->FormatTimeStamp($row['buyTimestamp']);
			$listDate = $this->FormatTimeStamp($row['listTimestamp']);
			$iconURL = $this->GetIconUrl($item['icon']);
			$unitPrice = number_format(floatval($row['price']) / floatval($row['qnt']), 2, ".", '');
			
			//if ($row['listTimestamp'] <= 0) $listDate = "";
			//if ($row['buyTimestamp'] <= 0) $buyDate = "";
			
			//$trait = $item['trait'];
			//$itemType = $item['itemType'];
			//$weaponType = $item['weaponType'];
			//$armorType = $item['armorType'];
			//$equipType = $item['equipType'];
			//$setName = $item['setName'];
			
			//$summary = array();
			//$traitText = "";
			//if ($trait > 0) $traitText = GetEsoItemTraitText($trait);
			
			//if ($itemType > 0) $summary[] = GetEsoItemTypeText($itemType);
			//if ($weaponType > 0) $summary[] = GetEsoItemWeaponTypeText($weaponType);
			//if ($armorType > 0) $summary[] = GetEsoItemArmorTypeText($armorType);
			//if ($equipType > 0) $summary[] = GetEsoItemEquipTypeText($equipType);
			//if ($setName != "") $summary[] = $setName;
			//$summary = implode(", ", $summary);
			
			$output .= "<tr>";
			//$output .= "<td><div class='esovsd_itemlink eso_item_link eso_item_link_q{$item['quality']}' itemid='{$item['itemId']}' intlevel='{$item['internalLevel']}' inttype='{$item['internalSubType']}' potiondata='{$item['potionData']}'>";
			//$output .= "<td><div class='esovsd_itemlink eso_item_link eso_item_link_q{$item['quality']}' itemlink='{$row['itemLink']}'>";
			//$output .= "<img src='$iconURL' class='esovsd_itemicon'>{$item['name']}</div></td>";
			//$output .= "<td>$traitText</td>";
			$output .= "<td>{$guild['name']}</td>";
			$output .= "<td>{$row['sellerName']}</td>";
			$output .= "<td>{$row['buyerName']}</td>";
			$output .= "<td>{$listDate}</td>";
			$output .= "<td>{$buyDate}</td>";
			$output .= "<td>{$row['price']}</td>";
			$output .= "<td>{$row['qnt']}</td>";
			$output .= "<td>{$unitPrice}</td>";
			$output .= "</tr>\n";
		}
	
		$output .= "</table>";
		return $output;
	}
	
	
	public function GetSearchResultsHtml()
	{
		if ($this->viewSalesItemId > 0) return $this->GetSalesSearchResultsHtml();
		return $this->GetItemSearchResultsHtml();
	}
	
	
	public function GetFindItemQuery()
	{
		$query = "SELECT SQL_CALC_FOUND_ROWS * FROM items ";
		$where = array();

		$where[] = "server='".$this->salesData->server."'";
		if ($this->finalItemLevel   >= 1) $where[] = "level=".$this->finalItemLevel;
		if ($this->finalItemQuality >= 0) $where[] = "quality=".$this->finalItemQuality;
		
		$traitValue = $this->GetItemTraitValue($this->formValues['trait']);
		if ($traitValue > 0) $where[] = "trait=".$traitValue;
		
		$equipTypeValue = $this->GetEquipTypeValue($this->formValues['equiptype']);
		if ($equipTypeValue > 0) $where[] = "equipType=".$equipTypeValue;
		
		$itemTypeValue = $this->GetItemTypeValue($this->formValues['itemtype']);
		if ($itemTypeValue > 0) $where[] = "itemType=".$itemTypeValue;
		
		$weaponTypeValue = $this->GetWeaponTypeValue($this->formValues['weapontype']);
		if ($weaponTypeValue > 0) $where[] = "weaponType=".$weaponTypeValue;
		
		$armorTypeValue = $this->GetArmorTypeValue($this->formValues['armortype']);
		if ($armorTypeValue > 0) $where[] = "armorType=".$armorTypeValue;
		
		if ($this->formValues['text'] != "")
		{
			$safeText = $this->db->real_escape_string($this->formValues['text']);
			//$where[] = "MATCH(name, setName) AGAINST ('$safeText' in BOOLEAN MODE)";
			$where[] = "(name LIKE '%$safeText%' or setName LIKE '%$safeText%')";
		}
		
		if (count($where) > 0) $query .= "WHERE " . implode(" AND ", $where);
		$query .= " LIMIT " . $this->searchItemIdsLimit . ";";
		
		$this->itemQuery = $query;
		return $query;
	}
	
	
	public function LoadItemData()
	{		
		$this->totalItemCount = 0;
		
		$this->lastQuery = $this->GetFindItemQuery();
		
		$result = $this->db->query($this->lastQuery);
		if ($result === false) return false;
		
		if ($result->num_rows == 0)
		{
			$this->errorMessages[] = "No items found matching input search!";
			return false;
		}
		
		$this->totalItemCount = $result->num_rows;
		$this->itemCount = $result->num_rows;
		
		while (($row = $result->fetch_assoc()))
		{
			$this->itemResults[$row['id']] = $row;
			$this->itemIds[] = $row['id'];
		}
		
		if ($result->num_rows >= $this->searchItemIdsLimit)
		{
			$result = $this->db->query("SELECT FOUND_ROWS() as rowCount;");
			
			if ($result)
			{
				$row = $result->fetch_assoc();
				$totalItems = $row['rowCount'];
				$this->totalItemCount = $totalItems;
				$this->errorMessages[] = "Found $totalItems matching items which exceeds the maximum of {$this->searchItemIdsLimit}.";
			}
		}
		
		$this->SortItemSearchResults();
		return true;
	}
	
	
	public function LoadGuilds()
	{
		$this->lastQuery = "SELECT * FROM guilds;";
		$result = $this->db->query($this->lastQuery);
		if ($result === false) return $this->ReportError("Failed to load guild data!");

		while (($row = $result->fetch_assoc()))
		{
			$this->guildData[$row['id']] = $row;
		}
		
		return true;
	}
	
	
	public function GetSearchQuery()
	{
		$query = "SELECT SQL_CALC_FOUND_ROWS * FROM sales ";
		$where = array();
		
		$where[] = "server='".$this->server."'";
		
		if (count($this->itemIds) <= 0) 
			$where[] = "0"; 
		else
			$where[] = "itemId IN (" . implode(",", $this->itemIds) . ")";
		
		$timePeriod = intval($this->formValues['timeperiod']);
		
		if ($timePeriod > 0) 
		{
			$timestamp = time() - $timePeriod;
			$where[] = "buyTimestamp >= $timestamp";
		}
		
		if (count($where) > 0)
		{
			$query .= " WHERE " . implode(" AND ", $where);
		}
		
		$query .= " LIMIT {$this->searchLimitCount};";
		
		$this->salesQuery = $query;
		return $query;
	}
	
	
	public function GetSingleSearchQuery()
	{
		$query = "SELECT SQL_CALC_FOUND_ROWS * FROM sales ";
		$where = array();
	
		$where[] = "itemId=" . $this->viewSalesItemId;
		
		$timePeriod = intval($this->formValues['timeperiod']);

		if ($timePeriod > 0)
		{
			$timestamp = time() - $timePeriod;
			$where[] = "buyTimestamp >= $timestamp";
		}

		if (count($where) > 0)
		{
			$query .= " WHERE " . implode(" AND ", $where);
		}

		//$query .= " LIMIT {$this->searchLimitCount};";
		//$query .= " ORDER BY buyTimestamp DESC;";
		$query .= ";";

		$this->salesQuery = $query;
		return $query;
	}
		
	
	public function LoadSalesSearchResults($loadSingle = false)
	{
		if ($loadSingle)
			$this->lastQuery = $this->GetSingleSearchQuery();
		else
			$this->lastQuery = $this->GetSearchQuery();
		
		$result = $this->db->query($this->lastQuery);
		if ($result === false) return $this->ReportError("Failed to search for sales data!");
		
		$this->searchCount = $result->num_rows;
		$this->totalSalesCount = $this->searchCount;
		
		if ($this->searchCount == 0)
		{
			$this->errorMessages[] = "No matching sales found!";			
			return true;
		}
		
		while (($row = $result->fetch_assoc()))
		{
			$row['unitPrice'] = floatval($row['price']) / floatval($row['qnt']);
			$row['itemName'] = $this->itemResults[$row['itemId']]['name'];
			$this->searchResults[] = $row;
			
			$this->displayServer = $row['server'];
		}
		
		if ($result->num_rows >= $this->searchLimitCount)
		{
			$result = $this->db->query("SELECT FOUND_ROWS() as rowCount;");
				
			if ($result)
			{
				$row = $result->fetch_assoc();
				$totalSales = $row['rowCount'];
				$this->totalSalesCount = $totalSales;
				$this->errorMessages[] = "Displaying {$this->searchLimitCount} out of $totalSales matching sales for {$this->itemCount} items.";
			}
		}
		else
		{
			$this->errorMessages[] = "Found {$this->totalSalesCount} matching sales for {$this->itemCount} items.";
		}
				
		$this->SortSalesSearchResults();
		return true;
	}
	
		
	public function LoadSingleItemData()
	{
		$this->lastQuery = "SELECT * FROM items WHERE id={$this->viewSalesItemId};";
		$result = $this->db->query($this->lastQuery);
		if ($result === false) return $this->ReportError("Failed to load single item data!");
		
		if ($result->num_rows == 0) return $this->ReportError("Failed to load single item data (ID = {$this->viewSalesItemId})!");
		$row = $result->fetch_assoc();
		
		$this->itemResults[$row['id']] = $row;
		$this->itemIds[] = $row['id'];
		$this->singleItemData = $row;
		
		$this->itemCount = 1;
		
		return true;
	}
	
	
	public function ComputeSaleStatistics()
	{
		$priceSumAll = 0;
		$countAll = 0;
		$countSales = 0;
		$priceSumBuy = 0;
		$countBuy = 0;
		$countSalesBuy = 0;
		$lastListTimestamp = time();
		$lastBuyTimestamp = $lastListTimestamp;
		
		foreach ($this->searchResults as $result)
		{
			$price = intval($result['price']);
			$qnt = intval($result['qnt']);
			
			if ($result['buyTimestamp'] > 0)
			{
				$priceSumBuy += $price;
				$countBuy += $qnt;
				++$countSalesBuy;
				
				if ($result['buyTimestamp'] < $lastBuyTimestamp) $lastBuyTimestamp = intval($result['buyTimestamp']);
			}
			
			$priceSumAll += $price;
			$countAll += $qnt;
			++$countSales;
			if ($result['listTimestamp'] > 0 && $result['listTimestamp'] < $lastListTimestamp) $lastListTimestamp = intval($result['listTimestamp']);
		}
		
		if ($countAll > 0)
		{
			$this->salePriceAverage = floatval($priceSumAll) / $countAll;
			$this->salePriceAverageCount = $countSales;
			$this->salePriceAverageQnt = $countAll;
		}
		else
		{
			$this->salePriceAverage = -1;
			$this->salePriceAverageQnt = -1;
			$this->salePriceAverageCount = -1;			
		}
		
		if ($countBuy > 0)
		{
			$this->salePriceAverageBuy = floatval($priceSumBuy) / $countBuy;;
			$this->salePriceAverageBuyQnt = $countBuy;
			$this->salePriceAverageBuyCount = $countSalesBuy;
		}
		else
		{
			$this->salePriceAverageBuy = -1;
			$this->salePriceAverageBuyQnt = -1;
			$this->salePriceAverageBuyCount = -1;			
		}
		
		$this->salePriceAverageListLastTimestamp = $lastListTimestamp;
		$this->salePriceAverageBuyLastTimestamp = $lastBuyTimestamp;
		$this->salePriceAverageLastTimestamp = min($lastBuyTimestamp, $lastListTimestamp);
	}
	
	
	public function LoadSearchResults()
	{
		
		if ($this->viewSalesItemId > 0)
		{
			$this->LoadSingleItemData();
			$this->LoadSalesSearchResults(true);
			$this->ComputeSaleStatistics();
		}
		else if ($this->showForm == "ViewGuilds")
		{
			
		}
		else if ($this->showForm == "ItemSearch")
		{
			$this->LoadItemData();
		}
		
	}
	
	
	public function SortItemSearchResults()
	{
		global $g_EsoSalesDataSortOrder, $g_EsoItemData;
		
		$g_EsoItemData = $this->itemResults;
		$g_EsoSalesDataSortOrder = $this->sortOrder;
		
		$this->itemSortedKeys = array_keys($this->itemResults);
	
		if ($this->sortField == "") return true;
	
		if ($this->sortField == "itemname")
		{
			usort($this->itemSortedKeys, "EsoItemDataCompareItemName");
		}
		
		return true;
	}
	
	
	public function SortSalesSearchResults()
	{
		global $g_EsoSalesDataSortOrder;
		
		$g_EsoSalesDataSortOrder = $this->sortOrder;
		if ($this->sortField == "") return true;
		
		if ($this->sortField == "itemname")
			usort($this->searchResults, "EsoSalesDataCompareItemName");
		else if ($this->sortField == "buydate")
			usort($this->searchResults, "EsoSalesDataCompareBuyDate");
		else if ($this->sortField == "listdate")
			usort($this->searchResults, "EsoSalesDataCompareListDate");
		else if ($this->sortField == "price")
			usort($this->searchResults, "EsoSalesDataComparePrice");
		else if ($this->sortField == "qnt")
			usort($this->searchResults, "EsoSalesDataCompareQnt");
		else if ($this->sortField == "unitprice")
			usort($this->searchResults, "EsoSalesDataCompareUnitPrice");
		
		return true;
	}
		
	
	public function GetErrorMessagesHtml()
	{
		if (count($this->errorMessages) <= 0) return "";
		$output = "<div id='esovsd_errormessages'>";
		
		foreach ($this->errorMessages as $errorMsg)
		{
			$output .= "<div class='esovsd_errormsg'>$errorMsg</div>";	
		}		
		
		$output .= "</div>";
		return $output;
	}
	
	
	public function ParseItemLink ($itemLink)
	{
		$matches = array();
	
		$result = preg_match('/\|H(?P<linkType>[A-Za-z0-9]*)\:item\:(?P<itemId>[0-9]*)\:(?P<subtype>[0-9]*)\:(?P<level>[0-9]*)\:(?P<enchantId>[0-9]*)\:(?P<enchantSubtype>[0-9]*)\:(?P<enchantLevel>[0-9]*)\:(.*?)\:(?P<style>[0-9]*)\:(?P<crafted>[0-9]*)\:(?P<bound>[0-9]*)\:(?P<stolen>[0-9]*)\:(?P<charges>[0-9]*)\:(?P<potionData>[0-9]*)\|h(?P<name>[^|\^]*)(?P<nameCode>.*?)\|h/', $itemLink, $matches);
		if ($result == 0) return false;
	
		return $matches;
	}
	
	
	public function GetSalesItemLinkHtml()
	{
		$output = "";
		if ($this->singleItemData == null) return "";
		
		$item = $this->singleItemData;
		$iconURL = $this->GetIconUrl($item['icon']);
		
		$internalLevel = $item['internalLevel'];	
		$internalSubType = $item['internalSubType'];
		
		$output .= "<div class='esovsd_itemlink eso_item_link eso_item_link_q{$item['quality']}' itemid='{$item['itemId']}' intlevel='{$internalLevel}' inttype='{$internalSubType}' potiondata='{$item['potionData']}'>";
		$output .= "<img src='$iconURL' class='esovsd_itemicon'>{$item['name']}</div>";
		
		return $output;
	}
	
	
	public function GetServerHtml()
	{
		return $this->displayServer;
	}
	
	
	public function GetGuildResultsHtml()
	{
		$output  = "<table id='esovsd_guildresults_table'>";
		$output .= "<tr>";
		$output .= "<th>Guild</th>";
		$output .= "<th>Server</th>";
		$output .= "<th>Members</th>";
		$output .= "<th>Founder</th>";
		$output .= "<th>Founded Date</th>";
		$output .= "<th>Kiosk Location</th>";
		$output .= "<th>Sales</th>";
		$output .= "</tr>";
	
		foreach ($this->guildData as $key => $guild)
		{
			$name = $guild['name'];
			$server = $guild['server'];
			$numMembers = $guild['numMembers'];
			$founder = $guild['leader'];
			$numSales = $guild['totalSales'];
			
			if ($guild['foundedDate'] <= 0) 
				$foundedDate = "";
			else
				$foundedDate = date('Y-m-d', $guild['foundedDate']);
			
			$storeLoc = $guild['storeLocation'];
			
			$lastStoreLocTime = $this->FormatTimeStamp($guild['lastStoreLocTime']);
			if ($lastStoreLocTime != "") $lastStoreLocTime = "<small>updated " . $lastStoreLocTime . "</small>";
						
			$output .= "<tr>";
			
			$output .= "<td>$name</td>";
			$output .= "<td>$server</td>";
			$output .= "<td>$numMembers</td>";
			$output .= "<td>$founder</td>";
			$output .= "<td>$foundedDate</td>";
			$output .= "<td>$storeLoc<br/>$lastStoreLocTime</td>";
			$output .= "<td>$numSales</td>";
			
			$output .= "</tr>\n";
		}
		
		$output .= "</table>";
				
		return $output;
	}
	
	
	public function CreateItemCopyPriceHtml($price, $saleCount, $purchaseCount, $lastTimestamp, $totalItems)
	{
		if ($this->singleItemData == null) return "";
		
		if ($price >= 1000)
			$price = round($price, 0);
		else if ($price >= 100)
			$price = round($price, 1);
		else
			$price = round($price, 2);
		
		$itemId = $this->singleItemData['itemId'];
		$itemLevel = $this->singleItemData['internalLevel'];
		$itemSubType = $this->singleItemData['internalSubType'];
		$days = intval((time() - $lastTimestamp) / 86400) + 1;
		
		$copyData = "UESP price (";
		
		if ($saleCount > 0 && $purchaseCount > 0)
			$copyData .= "$saleCount for sale, $purchaseCount purchases";
		else if ($purchaseCount > 0)
			$copyData .= "$purchaseCount purchases";
		else 
			$copyData .= "$saleCount for sale";
		
		if ($totalItems > $saleCount + $purchaseCount)
		{
			$copyData .= ", $totalItems items";
		}
			
		$copyData .= ", $days days): $price gp for |H1:item:$itemId:$itemSubType:$itemLevel:0:0:0:0:0:0:0:0:0:0:0:0:0:0:0:0:0:0|h|h";
		
		$output = "<div class='esovsd_copyprice' copydata='$copyData'>Copy to Clipboard</div>";
		
		return $output;
	}
	
	
	public function GetPriceStatHtml()
	{
		$output = "";
		
		if ($this->salePriceAverageCount <= 0)
		{
			$output .= "Not enough data to compute average price!";	
		}
		else
		{
			if ($this->salePriceAverage >= 1000)
				$fmt = round($this->salePriceAverage, 0);
			else if ($this->salePriceAverage >= 100)
				$fmt = round($this->salePriceAverage, 1);
			else
				$fmt = round($this->salePriceAverage, 2);
			
			$days = intval((time() - $this->salePriceAverageLastTimestamp) / 86400) + 1;
			$count = $this->salePriceAverageCount - $this->salePriceAverageBuyCount;
			$output .= "Average price for all data ($count for sale, {$this->salePriceAverageBuyCount} purchases,";
			
			if ($this->salePriceAverageQnt > $this->salePriceAverageCount)	$output .= " {$this->salePriceAverageQnt} items,";
			
			$output .= " $days days): <b>" . $fmt . " gp</b>";
			$output .= $this->CreateItemCopyPriceHtml($this->salePriceAverage, $this->salePriceAverageCount-$this->salePriceAverageBuyCount, $this->salePriceAverageBuyCount, $this->salePriceAverageLastTimestamp, $this->salePriceAverageQnt);
		}
		
		$output .= "<br /><br />";
			
		if ($this->salePriceAverageBuyCount <= 0)
		{
			$output .= "Not enough item purchases to compute average price!";
		}
		else
		{
			if ($this->salePriceAverageBuy >= 1000)
				$fmt = round($this->salePriceAverageBuy, 0);
			else if ($this->salePriceAverageBuy >= 100)
				$fmt = round($this->salePriceAverageBuy, 1);
			else
				$fmt = round($this->salePriceAverageBuy, 2);
			
			$days = intval((time() - $this->salePriceAverageBuyLastTimestamp) / 86400) + 1;
			$output .= "Average price for only purchases ({$this->salePriceAverageBuyCount} purchases,";
			
			if ($this->salePriceAverageBuyQnt != $this->salePriceAverageBuyCount)	$output .= " {$this->salePriceAverageBuyQnt} items,";
				
			$output .= " $days days): <b>" . $fmt . " gp</b>";
			$output .= $this->CreateItemCopyPriceHtml($this->salePriceAverageBuy, 0, $this->salePriceAverageBuyCount, $this->salePriceAverageBuyLastTimestamp, $this->salePriceAverageBuyQnt);
		}
		 
		$output .= "";
		return $output;		
	}
	
	
	public function CreateOutputHtml()
	{
		$replacePairs = array(
				'{formText}' => $this->GetFormValue('text'),
				'{formTrait}' => $this->GetFormValue('trait'),
				'{formQuality}' => $this->GetFormValue('quality'),
				'{formItemType}' => $this->GetFormValue('itemtype'),
				'{formEquipType}' => $this->GetFormValue('equiptype'),
				'{formArmorType}' => $this->GetFormValue('armortype'),
				'{formWeaponType}' => $this->GetFormValue('weapontype'),
				'{formTimePeriod}' => $this->GetFormValue('timeperiod'),
				'{formServer}' => $this->GetFormValue('server'),
				'{formLevel}' => $this->GetOutputFormLevel(),
				
				'{listTrait}' => $this->GetGeneralListHtml(self::$ESOVSD_TRAITS, 'trait'),
				'{listQuality}' => $this->GetGeneralListHtml(self::$ESOVSD_QUALITIES, 'quality'),
				'{listItemType}' => $this->GetGeneralListHtml(self::$ESOVSD_ITEMTYPES, 'itemtype'),
				'{listEquipType}' => $this->GetGeneralListHtml(self::$ESOVSD_EQUIPTYPES, 'equiptype'),
				'{listArmorType}' => $this->GetGeneralListHtml(self::$ESOVSD_ARMORTYPES, 'armortype'),
				'{listWeaponType}' => $this->GetGeneralListHtml(self::$ESOVSD_WEAPONTYPES, 'weapontype'),
				'{listTimePeriod}' => $this->GetGeneralListHtml(self::$ESOVSD_TIMEPERIODS, 'timeperiod', true),
				'{listServer}' => $this->GetGeneralListHtml(self::$ESOVSD_SERVERS, 'server', true),
				
				'{searchResults}' => $this->GetSearchResultsHtml(),
				'{errorMessages}' => $this->GetErrorMessagesHtml(),
				'{itemQuery}' => $this->itemQuery,
				'{salesQuery}' => $this->salesQuery,
				'{salesItemLink}' => $this->GetSalesItemLinkHtml(),
				'{server}' => $this->GetServerHtml(),
				'{guildsResults}' => $this->GetGuildResultsHtml(),
				
				'{priceStats}' => $this->GetPriceStatHtml(),
				
		);
		
		$output = strtr($this->htmlTemplate, $replacePairs);
		return $output;
	}
	
	
	public function GetOutputHtml()
	{
		$this->LoadTemplate();
		$this->LoadGuilds();
		
		$this->LoadSearchResults();

		return $this->CreateOutputHtml();
	}
	
	
	public function Render()
	{
		$output = $this->GetOutputHtml();
		print ($output);
	}
	
};


function EsoItemDataCompareItemName($a, $b)
{
	global $g_EsoSalesDataSortOrder, $g_EsoItemData;
	
	$result = strcmp($g_EsoItemData[$a]['name'], $g_EsoItemData[$b]['name']);

	if ($g_EsoSalesDataSortOrder == 0) return -$result;
	return $result;
}


function EsoSalesDataCompareItemName($a, $b)
{
	global $g_EsoSalesDataSortOrder;
	
	$result = strcmp($a['itemName'], $b['itemName']);
	
	if ($g_EsoSalesDataSortOrder == 0) return -$result;
	return $result;
}


function EsoSalesDataCompareBuyDate($a, $b)
{
	global $g_EsoSalesDataSortOrder;
	
	$t1 = $a['buyTimestamp'];
	$t2 = $b['buyTimestamp'];
	
	if ($t1 <= 0) $t1 = $a['listTimestamp'];
	if ($t2 <= 0) $t2 = $b['listTimestamp'];
	
	$result = $t1 - $t2;
	
	//if ($result == 0) $result = $a['listTimestamp'] - $b['listTimestamp']; 
	
	if ($g_EsoSalesDataSortOrder == 0) return -$result;
	return $result;
}


function EsoSalesDataComparePrice($a, $b)
{
	global $g_EsoSalesDataSortOrder;
	
	$result = $a['price'] - $b['price'];
	
	if ($g_EsoSalesDataSortOrder == 0) return -$result;
	return $result;
}


function EsoSalesDataCompareQnt($a, $b)
{
	global $g_EsoSalesDataSortOrder;
	
	$result = $a['qnt'] - $b['qnt'];
	
	if ($g_EsoSalesDataSortOrder == 0) return -$result;
	return $result;
}


function EsoSalesDataCompareUnitPrice($a, $b)
{
	global $g_EsoSalesDataSortOrder;
	
	$result = $a['unitPrice'] - $b['unitPrice'];
	
	if ($g_EsoSalesDataSortOrder == 0) return -$result;
	return $result;
}


$viewSales = new EsoViewSalesData();
$viewSales->Render();


