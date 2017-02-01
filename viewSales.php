<?php 


// Database users, passwords and other secrets
require_once("/home/uesp/secrets/esosalesdata.secrets");
require_once("/home/uesp/secrets/esolog.secrets");
require_once(__DIR__."/esoCommon.php");


$g_EsoSalesDataSortOrder = 1;
$g_EsoItemData = null;


class EsoViewSalesData
{
	const ESOVSD_ICON_URL = UESP_ESO_ICON_URL;
	const ESOVSD_ICON_UNKNOWN = "unknown.png";
	
	public $OMIT_BUYER_INFO = true;
	public $OMIT_SELLER_INFO = true;
		
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
	public $hasSearchData = false;
	
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
	
	public $salePriceAverageAll = 0;
	public $salePriceAverageCountAll = 0;
	public $salePriceAverageItemsAll = 0;
	
	public $salePriceAverageSold = 0;
	public $salePriceAverageCountSold = 0;
	public $salePriceAverageItemsSold = 0;
	
	public $salePriceAverageListed = 0;
	public $salePriceAverageCountListed = 0;
	public $salePriceAverageItemsListed = 0;
	
	public $salePriceAverageLastTimestampListed = 0;
	public $salePriceAverageLastTimestampSold = 0;
	public $salePriceAverageLastTimestampAll = 0;
	
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
			0 => "All Time",
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
			"all"    => "All",
			"sold"   => "Only Sold Items",
			"listed" => "Only Listed Items",
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

	
	public function Escape ($html)
	{
		return htmlspecialchars($html);	
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

		if ($this->dbReadInitialized) return true;

		$this->db = new mysqli($uespEsoSalesDataReadDBHost, $uespEsoSalesDataReadUser, $uespEsoSalesDataReadPW, $uespEsoSalesDataDatabase);
		if ($this->db->connect_error) return $this->ReportError("Could not connect to mysql database!");

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
		$this->ParseFormParam('saletype');
		
		if ($this->formValues['saletype'] != null)
		{
			$this->formValues['saletype'] = strtolower($this->formValues['saletype']);
		}
		
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
			$this->sortField = "lastseen";
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
		
		$this->hasSearchData = false;
		if (count($this->inputParams) > 0) $this->hasSearchData = true;
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
		return $this->Escape($this->formValues[$id]);
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
		if (!$this->hasSearchData) return "";
		
		$output  = "<table id='esovsd_searchresults_table' class='esovsd_itemresults'>";
		$output .= "<tr>";
		$output .= "<th>Item</th>";
		$output .= "<th>Level</th>";
		$output .= "<th>Trait</th>";
		$output .= "<th>Type</th>";
		$output .= "<th>Equip</th>";
		$output .= "<th>Armor</th>";
		$output .= "<th>Weapon</th>";
		$output .= "<th>Set Name</th>";
		$output .= "<th>#</th>";
		$output .= "<th>Avg Price</th>";
		$output .= "<th></th>";
		$output .= "</tr>";
				
		foreach ($this->itemSortedKeys as $itemId)
		{
			$item = $this->itemResults[$itemId];
			
			$iconURL = $this->Escape($this->GetIconUrl($item['icon']));
				
			$trait = $item['trait'];
			$itemName = $this->Escape($item['name']);
			$levelText = GetEsoItemLevelText($item['level']);
			$itemType = GetEsoItemTypeText($item['itemType']);
			$weaponType = GetEsoItemWeaponTypeText($item['weaponType']);
			$armorType = GetEsoItemArmorTypeText($item['armorType']);
			$equipType = GetEsoItemEquipTypeText($item['equipType']);
			$setName = $this->Escape($item['setName']);
			$totalSales = floatval($item['countSales']);
			$totalPurchases = floatval($item['countPurchases']);
			$sumSales = floatval($item['sumSales']);
			$sumPurchases = floatval($item['sumPurchases']);
			$totalCount = $totalSales + $totalPurchases;
			$totalItems = floatval($item['countItemPurchases']) + floatval($item['countItemSales']);
			$avgPrice = ($sumSales + $sumPurchases) / $totalItems;
			$extraQuery = "";
			
			if ($this->formValues['saletype'] == "sold")
			{
				$totalCount = $totalPurchases;
				$totalItems = floatval($item['countItemPurchases']);
				$avgPrice = $sumPurchases / $totalItems;
				$extraQuery .= "&saletype=sold";
			}
			else if ($this->formValues['saletype'] == "listed")
			{
				$totalCount = $totalSales;
				$totalItems = floatval($item['countItemSales']);
				$avgPrice = $sumSales / $totalItems;
				$extraQuery .= "&saletype=listed";
			}
			
			if ($this->formValues['timeperiod'] > 0)
			{
				$extraQuery .= "&timeperiod=".intval($this->formValues['timeperiod']);
			}
						
			$totalCountText = $totalCount;
			if ($totalItems > $totalCount) $totalCountText = "$totalCount ($totalItems)"; 
			
			if ($avgPrice >= 1000)
				$avgPrice = round($avgPrice, 0);
			else if ($avgPrice >= 100)
				$avgPrice = round($avgPrice, 1);
			else
				$avgPrice = round($avgPrice, 2);
				
			$traitText = "";
			if ($trait > 0) $traitText = GetEsoItemTraitText($trait);
			
			$copyPriceTooltip = $this->CreateItemCopyPriceTooltip($avgPrice, $totalSales, $totalPurchases, 0, $totalItems, $item);
			$copyPriceHtml = "<div class='esovsd_copyprice esovsd_copypricesmall' copydata='$copyPriceTooltip'>Copy</div>";
			
			$output .= "<tr>";
			$output .= "<td><div class='esovsd_itemlink eso_item_link eso_item_link_q{$item['quality']}' itemid='{$item['itemId']}' intlevel='{$item['internalLevel']}' inttype='{$item['internalSubType']}' potiondata='{$item['potionData']}'>";
			$output .= "<img src='$iconURL' class='esovsd_itemicon'>$itemName</div></td>";
			$output .= "<td>$levelText</td>";
			$output .= "<td>$traitText</td>";
			$output .= "<td>$itemType</td>";
			$output .= "<td>$equipType</td>";
			$output .= "<td>$armorType</td>";
			$output .= "<td>$weaponType</td>";
			$output .= "<td>$setName</td>";
			$output .= "<td>$totalCountText</td>";
			$output .= "<td>$avgPrice gp$copyPriceHtml</td>";
			$output .= "<td><a href='?viewsales=$itemId$extraQuery'>View Item Sales</a></td>";
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
		$output  = "<table id='esovsd_searchresults_table' class='esovsd_salesresults'>";
		$output .= "<tr>";
		$output .= "<th>Guild</th>";
		$output .= "<th>Kiosk Location</th>";
		if (!$this->OMIT_SELLER_INFO) $output .= "<th>Seller</th>";
		if (!$this->OMIT_BUYER_INFO) $output .= "<th>Buyer</th>";
		$output .= "<th>Listed / Sold</th>";
		$output .= "<th>Last Seen</th>";
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
			$lastSeen = $this->FormatTimeStamp($row['lastSeen']);
			$iconURL = $this->GetIconUrl($item['icon']);
			$unitPrice = number_format(floatval($row['price']) / floatval($row['qnt']), 2, ".", '');
			
			$kiosk = $guild['storeLocation'];
			if ($kiosk == "") $kiosk = "None";
	
			$lastStoreLocTime = $this->FormatTimeStamp($guild['lastStoreLocTime']);
			if ($lastStoreLocTime != "") $kiosk .= "<br/><small>updated " . $lastStoreLocTime . "</small>";
			
			$output .= "<tr>";
			$output .= "<td>{$guild['name']}</td>";
			$output .= "<td>$kiosk</td>";
			if (!$this->OMIT_SELLER_INFO) $output .= "<td>{$row['sellerName']}</td>";
			if (!$this->OMIT_BUYER_INFO) $output .= "<td>{$row['buyerName']}</td>";
			
			if ($listDate != "")
				$output .= "<td>Listed {$listDate}</td>";
			else
				$output .= "<td>Sold {$buyDate}</td>";

			$output .= "<td>$lastSeen</td>";
			$output .= "<td>{$row['price']} gp</td>";
			$output .= "<td>{$row['qnt']}</td>";
			$output .= "<td>{$unitPrice} gp</td>";
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
	
	
	public function GetFindItemLinkQuery($itemLinkData)
	{
		$itemId = intval($itemLinkData['itemId']);
		$intSubtype = intval($itemLinkData['subtype']);
		$intLevel = intval($itemLinkData['level']);
		$potionData = intval($itemLinkData['potionData']);
		
		$query = "SELECT SQL_CALC_FOUND_ROWS * FROM items ";
		$where = array();
		
		$where[] = "itemId=$itemId";
		$where[] = "internalLevel=$intLevel";
		$where[] = "internalSubtype=$intSubtype";		
		$where[] = "potionData=$potionData";		
				
		if (count($where) > 0) $query .= "WHERE " . implode(" AND ", $where);
		$query .= " LIMIT " . $this->searchItemIdsLimit . ";";
		
		$this->itemQuery = $query;
		return $query;
	}
	
	
	public function GetSecondFindItemLinkQuery($itemLinkData)
	{
		global $uespEsoLogReadDBHost, $uespEsoLogReadUser, $uespEsoLogReadPW, $uespEsoLogDatabase;
		
		$itemId = intval($itemLinkData['itemId']);
		$intSubtype = intval($itemLinkData['subtype']);
		$intLevel = intval($itemLinkData['level']);
		$potionData = intval($itemLinkData['potionData']);
		
		$query = "SELECT level, quality FROM $uespEsoLogDatabase.minedItemSummary WHERE itemId=$itemId;";
		$result = $this->db->query($query);
		
		$level = null;
		$quality = null;
		
		if ($result !== false && $result->num_rows > 0)
		{
			$row = $result->fetch_assoc();
			$level = $row['level'];
			$quality = $row['quality'];
			
			if (strpos($level, "-") !== false || strpos($quality, "-") !== false) 
			{
				$level = null;
				$quality = null;
			}
		}
		
		if ($level === null || $quality === null)
		{
			$quality = GetEsoQualityFromIntType($intSubtype);
			$level = GetEsoLevelFromIntType($intSubtype);
			if ($level == 1) $level = $intLevel;
		}
		
		$query = "SELECT SQL_CALC_FOUND_ROWS * FROM items ";
		$where = array();
	
		$where[] = "itemId=$itemId";
		$where[] = "level=$level";
		$where[] = "quality=$quality";
		$where[] = "potionData=$potionData";
	
		if (count($where) > 0) $query .= "WHERE " . implode(" AND ", $where);
		$query .= " LIMIT " . $this->searchItemIdsLimit . ";";
		
		$this->itemQuery = $query;
		return $query;
	}
	
	
	public function GetSecondFindItemQuery()
	{
		$itemLinkData = $this->ParseItemLink($this->formValues['text']);
		if ($itemLinkData) return $this->GetSecondFindItemLinkQuery($itemLinkData);
		
		return "";
	}
	
	
	public function GetFindItemQuery()
	{
		$itemLinkData = $this->ParseItemLink($this->formValues['text']);
		if ($itemLinkData) return $this->GetFindItemLinkQuery($itemLinkData);
		
		$query = "SELECT SQL_CALC_FOUND_ROWS * FROM items ";
		$where = array();

		$where[] = "server='".$this->server."'";
		if ($this->finalItemLevel   >= 1) $where[] = "level=".$this->finalItemLevel;
		if ($this->finalItemQuality >= 0) $where[] = "quality=".$this->finalItemQuality;
		
		$traitValue = $this->GetItemTraitValue($this->formValues['trait']);
		
		if ($traitValue > 0) 
		{
			if ($traitValue == 20 || $traitValue == 9)
				$where[] = "(trait=9 OR trait=20)";
			else if ($traitValue == 19 || $traitValue == 24 || $traitValue == 10)
				$where[] = "(trait=10 OR trait=19 or trait=24)";
			else if ($traitValue == 6 || $traitValue == 15)
				$where[] = "(trait=6 OR trait=15)";
			else if ($traitValue == 25 || $traitValue == 26)
				$where[] = "(trait=25 OR trait=26)";
			else
				$where[] = "trait=".$traitValue;
		}
		
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
		
		$timePeriod = intval($this->formValues['timeperiod']);
		$timestamp = time() - $timePeriod;
				
		if ($this->formValues['saletype'] == "sold")
		{
			$where[] = "countPurchases > 0";
			if ($timePeriod > 0) $where[] = "lastPurchaseTimestamp >= $timestamp";
		}
		else if ($this->formValues['saletype'] == "listed")
		{
			$where[] = "countSales > 0";
			if ($timePeriod > 0) $where[] = "(lastSaleTimestamp > 0 AND lastSeen >= $timestamp)";
		}
		elseif ($timePeriod > 0)
		{
			$where[] = "(lastPurchaseTimestamp >= $timestamp OR (lastSaleTimestamp > 0 AND lastSeen >= $timestamp))";
		}
		
		if (count($where) > 0) $query .= "WHERE " . implode(" AND ", $where);
		$query .= " LIMIT " . $this->searchItemIdsLimit . ";";
		
		$this->itemQuery = $query;
		return $query;
	}
	
	
	public function LoadItemData()
	{		
		$this->totalItemCount = 0;
		if (!$this->hasSearchData) return true;
		
		$this->lastQuery = $this->GetFindItemQuery();
		
		$result = $this->db->query($this->lastQuery);
		if ($result === false) return false;
		
		if ($result->num_rows == 0)
		{
			$secondQuery = $this->GetSecondFindItemQuery();
			
			if ($secondQuery != "")
			{
				$this->lastQuery = $secondQuery;
				
				$result = $this->db->query($this->lastQuery);
				if ($result === false) return false;
				
				if ($result->num_rows == 0)
				{
					$this->errorMessages[] = "No items found matching input search!";
					return false;
				}
			}
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
			$where[] = "(buyTimestamp >= $timestamp OR (listTimestamp > 0 AND lastSeen >= $timestamp))";
		}
				
		if ($this->formValues['saletype'] == "sold")
		{
			$where[] = "buyTimestamp > 0";
		}
		else if ($this->formValues['saletype'] == "listed")
		{
			$where[] = "listTimestamp > 0";
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
			$where[] = "(buyTimestamp >= $timestamp or (listTimestamp > 0 AND lastSeen >= $timestamp))";
		}
		
		if ($this->formValues['saletype'] == "sold")
		{
			$where[] = "buyTimestamp > 0";
		}
		else if ($this->formValues['saletype'] == "listed")
		{
			$where[] = "listTimestamp > 0";
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
		$countSold = 0;
		$countListed = 0;
		$countItemsAll = 0;
		$countItemsSold = 0;
		$countItemsListed = 0;
		$priceSumSold = 0;
		$priceSumListed = 0;
		$lastListTimestamp = time();
		$lastSoldTimestamp = $lastListTimestamp;
		
		foreach ($this->searchResults as $result)
		{
			$price = intval($result['price']);
			$qnt = intval($result['qnt']);
			
			if ($result['buyTimestamp'] > 0)
			{
				$priceSumSold += $price;
				$countItemsSold += $qnt;
				++$countSold;
				
				if ($result['buyTimestamp'] < $lastSoldTimestamp) $lastSoldTimestamp = intval($result['buyTimestamp']);
			}
			
			if ($result['listTimestamp'] > 0)
			{
				$priceSumListed += $price;
				$countItemsListed += $qnt;
				++$countListed;
				
				if ($result['listTimestamp'] < $lastListTimestamp) $lastListTimestamp = intval($result['listTimestamp']);
			}
			
			$priceSumAll += $price;
			$countItemsAll += $qnt;
			++$countAll;
			
		}
		
		$this->salePriceAverageAll = 0;
		$this->salePriceAverageCountAll = 0;
		$this->salePriceAverageItemsAll = 0;
		
		$this->salePriceAverageSold = 0;
		$this->salePriceAverageCountSold = 0;
		$this->salePriceAverageItemsSold = 0;
		
		$this->salePriceAverageListed = 0;
		$this->salePriceAverageCountListed = 0;
		$this->salePriceAverageItemsListed = 0;
		
		if ($countAll > 0)
		{
			$this->salePriceAverageAll = floatval($priceSumAll) / $countAll;
			$this->salePriceAverageCountAll = $countAll;
			$this->salePriceAverageItemsAll = $countItemsAll;
		}
		
		if ($countSold > 0)
		{
			$this->salePriceAverageSold = floatval($priceSumSold) / $countSold;
			$this->salePriceAverageCountSold = $countSold;
			$this->salePriceAverageItemsSold = $countItemsSold;
		}
		
		if ($countListed > 0)
		{
			$this->salePriceAverageListed = floatval($priceSumListed) / $countListed;
			$this->salePriceAverageCountListed = $countListed;
			$this->salePriceAverageItemsListed = $countItemsListed;
		}
		
		$this->salePriceAverageLastTimestampListed = $lastListTimestamp;
		$this->salePriceAverageLastTimestampSold = $lastSoldTimestamp;
		$this->salePriceAverageLastTimestampAll = min($lastSoldTimestamp, $lastListTimestamp);
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
		else if ($this->sortField == "lastseen")
			usort($this->searchResults, "EsoSalesDataCompareLastSeen");
		
		return true;
	}
		
	
	public function GetErrorMessagesHtml()
	{
		if (count($this->errorMessages) <= 0) return "";
		$output = "<div id='esovsd_errormessages'>";
		
		foreach ($this->errorMessages as $errorMsg)
		{
			$errorMsg = $this->Escape($errorMsg);
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
		
		$quality = intval($item['quality']);
		$itemId = intval($item['itemId']);
		$internalLevel = intval($item['internalLevel']);	
		$internalSubType = intval($item['internalSubType']);
		$potionData = intval($item['potionData']);
		$itemName = $this->Escape($item['name']);
		
		$output .= "<div class='esovsd_itemlink eso_item_link eso_item_link_q{$quality}' itemid='{$itemId}' intlevel='{$internalLevel}' inttype='{$internalSubType}' potiondata='{$potionData}'>";
		$output .= "<img src='$iconURL' class='esovsd_itemicon'>{$itemName}</div>";
		
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
		$output .= "<th>Listed / Sold</th>";
		$output .= "</tr>";
	
		foreach ($this->guildData as $key => $guild)
		{
			$name = $this->Escape($guild['name']);
			$server = $this->Escape($guild['server']);
			$numMembers = $this->Escape($guild['numMembers']);
			$founder = $this->Escape($guild['leader']);
			$numSales = $this->Escape($guild['totalSales']);
			$numPurchases = $this->Escape($guild['totalPurchases']);
			
			if ($guild['foundedDate'] <= 0) 
				$foundedDate = "";
			else
				$foundedDate = date('Y-m-d', $guild['foundedDate']);
			
			$storeLoc = $this->Escape($guild['storeLocation']);
			if ($storeLoc == "") $storeLoc = "None";
			
			$lastStoreLocTime = $this->FormatTimeStamp($guild['lastStoreLocTime']);
			if ($lastStoreLocTime != "") $lastStoreLocTime = "<small>updated " . $lastStoreLocTime . "</small>";
						
			$output .= "<tr>";
			
			$output .= "<td>$name</td>";
			$output .= "<td>$server</td>";
			$output .= "<td>$numMembers</td>";
			$output .= "<td>$founder</td>";
			$output .= "<td>$foundedDate</td>";
			$output .= "<td>$storeLoc<br/>$lastStoreLocTime</td>";
			$output .= "<td>$numSales / $numPurchases</td>";
			
			$output .= "</tr>\n";
		}
		
		$output .= "</table>";
				
		return $output;
	}
	
	
	public function CreateItemCopyPriceTooltip($price, $saleCount, $purchaseCount, $lastTimestamp, $totalItems, $itemData)
	{
		$copyData = "UESP price (";
		
		if ($price >= 1000)
			$price = round($price, 0);
		else if ($price >= 100)
			$price = round($price, 1);
		else
			$price = round($price, 2);
		
		$itemId = $itemData['itemId'];
		$itemLevel = $itemData['internalLevel'];
		$itemSubType = $itemData['internalSubType'];
		
		if ($saleCount > 0 && $purchaseCount > 0)
			$copyData .= "$saleCount listed, $purchaseCount sold";
		else if ($purchaseCount > 0)
			$copyData .= "$purchaseCount sold";
		else
			$copyData .= "$saleCount listed";
	
		if ($totalItems > $saleCount + $purchaseCount)
		{
			$copyData .= ", $totalItems items";
		}
			
		if ($lastTimestamp > 0)
		{
			$days = intval((time() - $lastTimestamp) / 86400) + 1;
			$copyData .= ", $days days";
		}
		
		$copyData .= "): $price gp for |H1:item:$itemId:$itemSubType:$itemLevel:0:0:0:0:0:0:0:0:0:0:0:0:0:0:0:0:0:0|h|h";
		return $copyData;
	}
	
	
	public function CreateItemCopyPriceHtml($price, $saleCount, $purchaseCount, $lastTimestamp, $totalItems)
	{
		if ($this->singleItemData == null) return "";
		
		$copyData = $this->CreateItemCopyPriceTooltip($price, $saleCount, $purchaseCount, $lastTimestamp, $totalItems, $this->singleItemData);
		$output = "<div class='esovsd_copyprice' copydata='$copyData'>Copy to Clipboard</div>";
		
		return $output;
	}
	
	
	public function FormatPrice ($price)
	{
		if ($avgPrice >= 1000)
			$fmtPrice = round($price, 0);
		else if ($avgPrice >= 100)
			$fmtPrice = round($price, 1);
		else
			$fmtPrice = round($price, 2);
		
		return $fmtPrice;
	}
	
	
	public function GetPriceStatSaleTypeHtml($listCount, $soldCount, $itemCount, $avgPrice, $lastTimestamp, $label)
	{
		$output = "";
		$totalCount = $listCount + $soldCount;
		$fmtPrice = $this->FormatPrice($avgPrice);
		
		if ($totalCount <= 0)
		{
			$output .= "Not enough data to compute average price for $label!";
		}
		else
		{
			$days = intval((time() - $lastTimestamp) / 86400) + 1;
			$output .= "Average price for $label (";
			if ($listCount > 0) $output .= "$listCount listed, ";
			if ($soldCount > 0) $output .= "$soldCount sold, ";
			if ($itemCount > $totalCount) $output .= "$itemCount items, ";
				
			$output .= "$days days): <b>" . $fmtPrice . " gp</b>";
			$output .= $this->CreateItemCopyPriceHtml($avgPrice, $listCount, $soldCount, $lastTimestamp, $itemCount);
		}
		
		return $output;
	}
	
	
	public function GetPriceStatHtml()
	{
		$output = "";
		
		if ($this->formValues['saletype'] != "listed" && $this->formValues['saletype'] != "sold")
		{
			$output .= $this->GetPriceStatSaleTypeHtml($this->salePriceAverageCountListed, $this->salePriceAverageCountSold, $this->salePriceAverageItemsCountAll, $this->salePriceAverageAll, $this->salePriceAverageLastTimestampAll, "all data");
			$output .= "<br /><br />";
		}
		
		if ($this->formValues['saletype'] == "listed")
		{
			$output .= $this->GetPriceStatSaleTypeHtml($this->salePriceAverageCountListed, 0, $this->salePriceAverageItemsCountListed, $this->salePriceAverageListed, $this->salePriceAverageLastTimestampListed, "items listed");
		}
		else
		{
			$output .= $this->GetPriceStatSaleTypeHtml(0, $this->salePriceAverageCountSold, $this->salePriceAverageItemsCountSold, $this->salePriceAverageSold, $this->salePriceAverageLastTimestampSold, "items sold");
		}
		 
		$output .= "";
		return $output;		
	}
	
	
	public function GetItemDetailsHtml()
	{
		if ($this->singleItemData == null) return "";
		
		$traitText = "";
		if ($trait > 0) $traitText = GetEsoItemTraitText($this->singleItemData['trait']) . ",";
		
		$details = array();
		
		$details[] = GetEsoItemQualityText($this->singleItemData['quality']);
		$details[] = "Level ".GetEsoItemLevelText($this->singleItemData['level']);
		$details[] = GetEsoItemTypeText($this->singleItemData['itemType']);
		$details[] = GetEsoItemWeaponTypeText($this->singleItemData['weaponType']);
		$details[] = GetEsoItemArmorTypeText($this->singleItemData['armorType']);
		$details[] = GetEsoItemEquipTypeText($this->singleItemData['equipType']);
		$details[] = $this->Escape($item['setName']);
		
		$details = array_filter($details);
		
		$output = "Item Details: ";
		$output .= implode(", ", $details);
		$output .= "";
		
		return $output;				
	}
	
		
	public function GetSalesTypeHtml()
	{
		$output = "";
		$saleType = $this->formValues['saletype'];
		$timePeriod = intval($this->formValues['timeperiod']);
		$itemId = $this->viewSalesItemId;
		
		$output .= "View: ";
		
		if ($saleType == "all" || $saleType == "")
			$output .= " <b>All Items</b> ";
		else
			$output .= " <a href='?viewsales=$itemId&saletype=all&timeperiod=$timePeriod'>All Items</a>";
		
		$output .= " : ";
		
		if ($saleType == "sold")
			$output .= " <b>Only Sold Items</b> ";
		else
			$output .= " <a href='?viewsales=$itemId&saletype=sold&timeperiod=$timePeriod'>Only Sold Items</a>";
		
		$output .= " : ";
		
		if ($saleType == "listed")
			$output .= " <b>Only Listed Items</b> ";
		else
			$output .= " <a href='?viewsales=$itemId&saletype=listed&timeperiod=$timePeriod'>Only Listed Items</a>";
		
		$output .= " from ";
			
		if ($timePeriod <= 0)
			$output .= " <b>All Time</b> ";
		else
			$output .= " <a href='?viewsales=$itemId&saletype=$saleType&timeperiod=0'>All Time</a>";
		
		$output .= " : ";
			
		if ($timePeriod == 86400)
			$output .= " <b>Last Day</b> ";
		else
			$output .= " <a href='?viewsales=$itemId&saletype=$saleType&timeperiod=86400'>Last Day</a>";
		
		$output .= " : ";
			
		if ($timePeriod == 604800)
			$output .= " <b>Last Week</b> ";
		else
			$output .= " <a href='?viewsales=$itemId&saletype=$saleType&timeperiod=604800'>Last Week</a>";
		
		$output .= " : ";
				
		if ($timePeriod == 2678400)
			$output .= " <b>Last Month</b> ";
		else
			$output .= " <a href='?viewsales=$itemId&saletype=$saleType&timeperiod=2678400'>Last Month</a>";
		
		$output .= " : ";
		
		if ($timePeriod == 31558150)
			$output .= " <b>Last Year</b> ";
		else
			$output .= " <a href='?viewsales=$itemId&saletype=$saleType&timeperiod=31558150'>Last Year</a>";
		
		$output .= "<br/>";
		return $output;
	}
	
	
	public function GetTimePeriodMessageHtml()
	{
		if ($this->formValues['timeperiod'] <= 0) return "";
		return "Note: The displayed sale # and average price are for all time. View the item sale details for more accurate values.<br/><br/>";
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
				'{formSaleType}' => $this->GetFormValue('saletype'),
				'{formLevel}' => $this->GetOutputFormLevel(),
				
				'{listTrait}' => $this->GetGeneralListHtml(self::$ESOVSD_TRAITS, 'trait'),
				'{listQuality}' => $this->GetGeneralListHtml(self::$ESOVSD_QUALITIES, 'quality'),
				'{listItemType}' => $this->GetGeneralListHtml(self::$ESOVSD_ITEMTYPES, 'itemtype'),
				'{listEquipType}' => $this->GetGeneralListHtml(self::$ESOVSD_EQUIPTYPES, 'equiptype'),
				'{listArmorType}' => $this->GetGeneralListHtml(self::$ESOVSD_ARMORTYPES, 'armortype'),
				'{listWeaponType}' => $this->GetGeneralListHtml(self::$ESOVSD_WEAPONTYPES, 'weapontype'),
				'{listTimePeriod}' => $this->GetGeneralListHtml(self::$ESOVSD_TIMEPERIODS, 'timeperiod', true),
				'{listServer}' => $this->GetGeneralListHtml(self::$ESOVSD_SERVERS, 'server', true),
				'{listSaleType}' => $this->GetGeneralListHtml(self::$ESOVSD_SALETYPES, 'saletype', true),
				
				'{searchResults}' => $this->GetSearchResultsHtml(),
				'{errorMessages}' => $this->GetErrorMessagesHtml(),
				'{timePeriodMessage}' => $this->GetTimePeriodMessageHtml(),
				'{itemQuery}' => $this->itemQuery,
				'{salesQuery}' => $this->salesQuery,
				'{salesItemLink}' => $this->GetSalesItemLinkHtml(),
				'{server}' => $this->GetServerHtml(),
				'{guildsResults}' => $this->GetGuildResultsHtml(),
				
				'{priceStats}' => $this->GetPriceStatHtml(),
				'{itemDetails}' => $this->GetItemDetailsHtml(),
				'{showSalesType}' => $this->GetSalesTypeHtml(),
				
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


function EsoSalesDataCompareLastSeen($a, $b)
{
	global $g_EsoSalesDataSortOrder;
	
	$t1 = $a['lastSeen'];
	$t2 = $b['lastSeen'];
	
	$result = $t1 - $t2;
	
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



